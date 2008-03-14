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
	
	require_once("common.php");
	require_once("post-common.php");
	$mod=($_SESSION['moderator'] || $_SESSION['admin']);  //quick fix

	$db=new ThornPostDBI();
	if ($db->checkban())
	{
		THdie("PObanned");
	}
	$thread=$db->gettinfo((int)$_POST['thread']);
	$binfo=$db->getbinfo($thread['board']);

	//check for banned keywords
	if ($mod==false)
	{
		// This should have the cached version of banned keywords in an array named $spamblacklist.
		@include(THpath.'/cache/blacklist.php');
		//You could use any website, or even CENSORED or some other text.  We picked GameFAQs.
		if(count($spamblacklist) > 0)
		{
			$_POST['body'] = str_replace($spamblacklist, "gamefaqs.com", $_POST['body']);
			$_POST['link'] = str_replace($spamblacklist, "gamefaqs.com", $_POST['link']);
			$_POST['name'] = str_replace($spamblacklist, "gamefaqs.com", $_POST['name']);
		}
		
		// The email field will have a big "IF YOU ARE HUMAN DO NOT FILL THIS IN" next to it.
		if(isset($_POST['email']) && $_POST['email'] != "")
		{
			// get out spambot >:[
			$redhammer = new ThornModDBI();
			$redhammer->banip(ip2long($_SERVER['REMOTE_ADDR']),0,"Suspected bot.","","Suspected bot.",$_POST['body'], -1, "autoban");
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
		$pnum=$db->putpost($usethese['nombre'],$usethese['trip'],$_POST['link'],$thread['board'],(int)$_POST['thread'],$_POST['body'],ip2long($_SERVER['REMOTE_ADDR']),$mod,$_POST['bump']=="on");
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
	}

	//hopefully this doesn't break it! -tyam
	$boardz = getboardname($thread['board']);

	if ($_POST['todo']=="board")
	{
		if (THuserewrite) { $location = THurl.$boardz; } 
		else { $location = THurl."drydock.php?b=$boardz"; }
		header("Location: ".$location);
	}
	elseif ($_POST['todo']=="post") 
	{
		$postglobalid=mysql_query("select globalid from ".THreplies_table." where id=".$pnum);
		$postglobalid=mysql_result($postglobalid,0,"globalid");
		$threadglobalid=mysql_query("select globalid from ".THthreads_table." where id=".$thread['id']);
		$threadglobalid=mysql_result($threadglobalid,0,"globalid");
		if (THuserewrite) { $location = THurl.$boardz."/thread/".$threadglobalid."#".$postglobalid; } 
		else { $location = THurl."drydock.php?b=$boardz&i=$threadglobalid#$postglobalid"; }
		header("Location: ".$location);
	} else {
		header("Location: drydock.php");
	}

?>
