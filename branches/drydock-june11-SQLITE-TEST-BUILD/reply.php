<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			reply.php
		Description:	Script that receives form input for replies.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	/*
		THINGS THAT WE EXPECT TO COME IN:
	
		$_POST['thread']  (int for the thread ID)
		$_POST['body'] (string for the post body)
		$_POST['link'] (string for the link field)
		$_POST['nombre'] (string for the post name if forced_anon is off)
		$_POST['board'] (string for the board folder)
		$_POST['password'] (string for post deletion)
	
		THINGS THAT MIGHT ALSO COME IN:
		$_POST['vc'] (captcha string)
		$_POST['email'] (spambot string)
		$_POST['todo'] (after-posting string)
		$_FILES (for images)
	
	*/
	
	require_once("common.php");
	require_once("post-common.php");
	$mod=($_SESSION['moderator'] || $_SESSION['admin']);  //quick fix

	var_dump($_POST);
	$db=new ThornPostDBI();
	if ($db->checkban())
	{
		THdie("PObanned");
	}
	
	// Get thread and board info
	$thread=$db->gettinfo($_POST['thread']);
	$binfo=$db->getbinfo($db->getboardnumber($_POST['board']));
	
	// Die if the board doesn't exist.
	if( $binfo == null )
		die("Specified board does not exist.");
	else if( $thread == null )
		die("Specified thread does not exist.");
	
	//check for banned keywords
	if ($mod==false)
	{
		// This should have the cached version of banned keywords in an array named $spamblacklist.
		@include(THpath.'/cache/blacklist.php');
		//You could use any website, or even CENSORED or some other text.  We picked GameFAQs.
		if(count($spamblacklist) > 0)
		{
			$_POST['body'] = str_replace($spamblacklist, "xxxxx", $_POST['body']);
			$_POST['link'] = str_replace($spamblacklist, "xxxxx", $_POST['link']);
			$_POST['name'] = str_replace($spamblacklist, "xxxxx", $_POST['name']);
		}
		
		// The email field will have a big "IF YOU ARE HUMAN DO NOT FILL THIS IN" next to it.
		if(isset($_POST['email']) && $_POST['email'] != "")
		{
			// get out spambot >:[
			$redhammer = new ThornModDBI();
			$redhammer->banip(ip2long($_SERVER['REMOTE_ADDR']),0,"Suspected bot.","","Suspected bot.",$_POST['body'], -1, "autoban");
			THdie("Abnormal reply"); // heh heh
		}
		
		// Prevent people from posting new replies if it's the mod board
		if( $binfo['id'] == THmodboard)
		{
			THdie("POboardreplocked");
		}
		
		// Prevent people from posting to boards that require registration
		// when they're not logged in
		if( $binfo['requireregistration'] == true && $_SESSION['username'] == false)
		{
			THdie("POboardreplocked");
		}
	}

	if ($thread['lawk']==1 && $mod==false)
	{
		THdie("POthrlocked");
	}
	if ($binfo['rlock']==1 && $mod==false)
	{
		THdie("POboardreplocked");
	}
	if ($mod==false && THvc==true) 
	{
		checkvc();
	}
	//File checking and processing here, I suppose.
	$goodfiles=checkfiles($binfo);

	if ($binfo['rpix']==0 && count($goodfiles)>0 && $mod==false) 
	{
		THdie("POrepnopix");
	}
	if ($binfo['rpix']==2 && count($goodfiles)==0 && $mod==false) 
	{
		THdie("POrepmustpix");
	}
	if (count($goodfiles)==0 && !$_POST['body'] && $mod==false)
	{
		THdie("You must post images or leave a comment.");
	}

	//prin_tr($_POST);

	//Don't post if there's no files or body (mod stuff only)
	if (strlen($_POST['body'])>1 || count($goodfiles)>0) 
	{
		$usethese=preptrip($_POST['nombre'],$_POST['tpass']);
		$pnum=$db->putpost($usethese['nombre'],$usethese['trip'],$_POST['link'],
			$binfo['id'],(int)$_POST['thread'],$_POST['body'],ip2long($_SERVER['REMOTE_ADDR']),
			$mod, $_POST['password']);
		movefiles($goodfiles, $pnum, false, $binfo, $db);
	}

	if ($_POST['mem']=="on") 
	{
		if ($_POST['nombre']!==null) 
		{
			setcookie(THcookieid."-name",$_POST['nombre'],time()+THprofile_cookietime, THprofile_cookiepath);
		}
		if ($_POST['tpass']!==null) 
		{
			setcookie(THcookieid."-tpass",$_POST['tpass'],time()+THprofile_cookietime, THprofile_cookiepath);
		}
		if ($_POST['link']!=null) 
		{
			setcookie(THcookieid."-link",$_POST['link'],time()+THprofile_cookietime, THprofile_cookiepath);
		} 
		setcookie(THcookieid."-re-goto", $_POST['todo'],time()+THprofile_cookietime, THprofile_cookiepath);
		setcookie(THcookieid."-password", $_POST['password'],time()+THprofile_cookietime, THprofile_cookiepath);
	}

	//hopefully this doesn't break it! -tyam
	if ($_POST['todo']=="board")
	{
		if (THuserewrite) { $location = THurl.$binfo['folder']; } 
		else { $location = THurl."drydock.php?b=".$binfo['folder']; }
		header("Location: ".$location);
	}
	elseif ($_POST['todo']=="post") 
	{
		// Retrieve the global IDs for both the thread and post number
		$loc_arr = $db->getpostlocation($thread['id'], $pnum);

		if (THuserewrite) 
		{ 
			$location = THurl.$binfo['folder']."/thread/".$loc_arr['thread_loc']."#".$loc_arr['post_loc']; 
		} 
		else 
		{ 
			$location = THurl."drydock.php?b=".$binfo['folder']."&i=".$loc_arr['thread_loc']."#".$loc_arr['post_loc']; 
		}
		
		header("Location: ".$location);
	} 
	else 
	{
		header("Location: drydock.php");
	}

?>
