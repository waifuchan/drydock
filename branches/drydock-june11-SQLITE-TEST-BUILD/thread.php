<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			thread.php
		Description:	Script that receives form input for new threads.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once("common.php");
	require_once("post-common.php");
	
	/*
		THINGS THAT WE EXPECT TO COME IN:
	
		$_POST['body'] (string for the post body)
		$_POST['subj'] (string for the post subject)
		$_POST['link'] (string for the link field)
		$_POST['nombre'] (string for the post name if forced_anon is off)
		$_POST['board'] (string for the board folder)
	
		THINGS THAT MIGHT ALSO COME IN:
		$_POST['vc'] (captcha string)
		$_POST['email'] (spambot string)
		$_POST['todo'] (after-posting string)
		$_FILES (for images)
		$_POST['pin']
		$_POST['lock']
		$_POST['permasage']
	
	*/

/*
	//You can see how this would be used (and even expand it to other fields if you wish), but we're not using it right now. - tyam
	if (strlen($_POST['subj'])<3)
	{
		THdie("Subject field not long enough");
	}
*/
	$mod=($_SESSION['moderator'] || $_SESSION['admin']);  //quick fix
	//var_dump($_POST);
	$db=new ThornPostDBI;
	if ($db->checkban()) 
	{
		THdie("PObanned");
	}

	$binfo=$db->getbinfo($db->getboardnumber($board));
	
	// Die if the board doesn't exist.
	if( $binfo == null )
		die("Specified board does not exist.");

	//check for banned keywords
	if ($mod==false)
	{
		// This should have the cached version of banned keywords in an array named $spamblacklist.
		@include(THpath.'/cache/blacklist.php');
		//You could use any website, or even CENSORED or some other text.  We picked GameFAQs.
		if(count($spamblacklist) > 0)
		{
			$_POST['subj'] = str_replace($spamblacklist, "xxxxx", $_POST['subj']);
			$_POST['body'] = str_replace($spamblacklist, "xxxxx", $_POST['body']);
			$_POST['link'] = str_replace($spamblacklist, "xxxxx", $_POST['link']);
			$_POST['name'] = str_replace($spamblacklist, "xxxxx", $_POST['name']);
		}
		
		// The email field will have a big "IF YOU ARE HUMAN DO NOT FILL THIS IN" next to it.
		if(isset($_POST['email']) && $_POST['email'] != "")
		{
			// get out spambot >:[
			$redhammer = new ThornModDBI();
			$redhammer->banip(ip2long($_SERVER['REMOTE_ADDR']),0,"Suspected bot.","","Suspected bot.",$_POST['body'], 4, "autoban");
		}
	}

	if ($binfo['tlock']==1 && $mod==false) 
	{
		THdie("POnonewth");
	}

	if ($mod==false && THvc==true) {
		checkvc();
	}

	//File checking and processing here, I suppose.
	$goodfiles=checkfiles($binfo);
	//echo(count($goodfiles));
	if ($binfo['tpix']==0 && count($goodfiles)>0 && $mod==false)
	{
		THdie("POthnopix");
	}
	if ($binfo['tpix']==2 && count($goodfiles)==0 && $mod==false)
	{
		THdie("POthmustpix");
	}
	if (count($goodfiles)==0 && !$_POST['body'] && $mod==false)  //oops, tyam moment
	{
		THdie("You must post images or leave a comment.");
	}
	$pin=(int)($_POST['pin']=="on" && $mod);
	$lock=(int)($_POST['lock']=="on" && $mod);
	$permasage=(int)($_POST['permasage']=="on" && $mod);

	$usethese=preptrip($_POST['nombre'],$_POST['tpass']);

	$tnum=$db->putthread(
	$usethese['nombre'],$usethese['trip'],$binfo['id'],$_POST['subj'],
	$_POST['body'],$_POST['link'],ip2long($_SERVER['REMOTE_ADDR']),$mod,$pin,$lock,$permasage
	);

	movefiles($goodfiles,$tnum,true,$binfo,$db);

	$sm=smsimple();
	$sm->clear_cache(null,$board);
	//$sm->clear_cache(null,"idx"); what
/* 	if (isset($_POST['tedit'])==true)
	{
		$sm->clear_cache(null,"t".$_POST['tedit']);
	} */

	if ($binfo['tmax']!=0 /*&& isset($_POST['tedit'])==false*/) //Don't purge if max threads is set to 0
	{
		delimgs($db->purge(intval(1)));
	}
	//Cookie setting stuff here
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
		setcookie(THcookieid."-th-goto", $_POST['todo'],time()+THprofile_cookietime, THprofile_cookiepath);
	}

	//hopefully this doesn't break it! -tyam
	if ($_POST['todo']=="board")
	{
		if (THuserewrite) { $location = THurl.$board; } else { $location = THurl."drydock.php?b=$board"; }
		header("Location: ".$location);
	}
	elseif ($_POST['todo']=="thread")
	{
		// Look up the global ID for this thread.
		$loc_arr = $db->getpostlocation($tnum);

		if (THuserewrite) 
		{ 
			$location = THurl.$board."/thread/".$loc_arr['thread_loc']; 
		} 
		else 
		{ 
			$location = THurl."drydock.php?b=$board&i=".$loc_arr['thread_loc']; 
		}
		
		header("Location: ".$location);
	} 
	else 
	{
		header("Location: drydock.php");
	}
?>
