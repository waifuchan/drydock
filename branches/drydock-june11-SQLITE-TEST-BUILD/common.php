<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			common.php
		Description:	Contains many functions for general use.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	require_once("version.php");
	session_start();
	checklogin(); // Update session variables if needed (needed meaning: HAY WE HAVE A COOKIE)
	@header('Content-type: text/html; charset=utf-8');
	if (function_exists("mb_internal_encoding"))
	{
		//This server will support multi-byte strings. Unicodey goodness!
		mb_internal_encoding("UTF-8");
		mb_language("uni");
		mb_http_output("UTF-8");
	}
	if (file_exists("config.php"))
	{
		require_once("config.php");
	} 
	//error_reporting(E_ALL);

	//Find DB code
	if (THdbtype!=null)
	{
		$findpath=THpath."dbi/".THdbtype."-dbi.php";
		if (file_exists($findpath))
		{
			require_once($findpath);
		} 
		else 
		{
			THdie("DBcode");
		}
	}
	require_once("rebuilds.php");  //frown
	
	define("THbcw_blotter", 1);
	define("THbcw_capcode", 2);
	define("THbcw_filter", 3);
	
	function THdie($err)
	{
		//die($err);
		if($err=="ADbanned" || $err =="PObanned") // THIS USED TO READ $err="ADbanned", which meant that whenever THdie was called it would tell someone they're banned, gg
		{
			$db=new ThornDBI();

			// Get bans associated with an IP (there could be multiple bans)
			$bans = $db->getban();
			$unbanned = 1; // boolean to indicate whether they've been unbanned or not, gets changed in the foreach loop if appropriate
			
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<meta http-equiv="Content-Style-Type" content="text/css" />';
			echo '<link rel="stylesheet" type="text/css" href="href="'.THurl.'tpl/'.THtplset.'" title="Stylesheet" />';
			echo '<title>b&</title>';
			echo '</head>';
			echo '<body>';
			echo '<div align="center">You have been banned.<br /></div>';

			foreach( $bans as $singleban )
			{
				// Display wildcards as appropriate.
				printf("Associated IP: %d.%d.%s.%s<br>\n",
				$singleban['ip_octet1'],
				$singleban['ip_octet2'],
				(($singleban['ip_octet3'] == -1) ? "*" : $singleban['ip_octet3']),
				(($singleban['ip_octet4'] == -1) ? "*" : $singleban['ip_octet4']));
			
				if( $singleban['postdata'] )
				{
					echo 'Associated post:<br />'.$singleban['postdata'].'<br /><br />';
				}
				
				$reason = "";
				if(!$singleban['privatereason'])
				{
					$reason=$singleban['publicreason'];
				} 
				else 
				{
					$reason=$singleban['privatereason'];
				}
				
				if(!$reason)
				{
					$reason='No reason given';
				}
				else
				{
					echo 'Reason given: '.$reason.'<br /><br />';
				}
				
				if( $singleban['duration'] == 0 )
				{
					echo 'This is only a warning and will be removed from the active bans list. Keep in mind however that if you are warned multiple times you may be permabanned.';
				}
				else if( $singleban['duration'] == -1 )
				{
					echo 'This ban will not expire.<br /><br />';
					$unbanned = 0; // still banned
				}
				else // Neither permanent nor a warning
				{
					//we'll need to know the difference between the ban time and the duration for actually expiring the bans
					$offset = THtimeoffset*60;
					$now = time()+$offset;
					$banoffset = $singleban['duration']*3600; // convert to hours
					$expiremath = $banoffset+$singleban['bantime'];
				
					if($now>$expiremath)
					{
						echo 'This ban has expired.  Keep in mind that you may be rebanned at any time.<br /><br />';
					} 
					else 
					{
						echo 'This ban duration was set to '.$bantime['duration'].' hours.  The ban will expire on '.
							date(THdatetimestring,$expiremath).'<br /><br />';
						$unbanned = 0; // still banned
					}
				}
				
			}
	
			if($unbanned == 1)
			{
				echo '<a href="'.THurl.'">Continue to the main index</a>';
			} 
			else 
			{
				echo "If you feel this ban is in error, please email an administrator.";
			}
			
			echo '</body></html>';
		} 
		else 
		{
			$sm=sminit("error",$err);
			$sm->assign_by_ref("error",$err);
			$sm->display("error.tpl",$err);
			die();
		}
	}//THdie
	//Below are functions that are used in various places throughout Thorn.
	
	function ipsub($ip)
	{
		$sub=explode(".",long2ip($ip));
		return(ip2long(implode(".",array($sub[0],$sub[1],$sub[2],0))));
	}
	
	function smsimple()
	{
		require_once("_Smarty/Smarty.class.php");
		$sm=new Smarty;
		//$sm->debugging=true;  //uncomment to enable debugging window for smarty
		return($sm);
	}
	
	function sminit($tpl,$id=null,$template=THtplset,$admin=false)
	{
		$smarty=smsimple();
		$sm->cache_dir=THpath."cache/";
		if ($admin)
		{
			//echo("ADMIM MODE ZOMG");
			$smarty->caching=0;
			$smarty->template_dir=THpath."tpl/_admin/";
			$smarty->cache_lifetime=0;
			$smarty->assign("THtplurl",THurl."tpl/_admin/");

		}
		elseif (THtpltest || $tpl=="error.tpl" || $tpl=="preview.tpl")
		{
			//We don't want to cache error pages or post previews
			$smarty->caching=0;
			$smarty->force_compile=true;
			$smarty->template_dir=THpath."tpl/".$template."/";
			$smarty->cache_lifetime=-1;
			$smarty->assign("THtplurl",THurl."tpl/".$template."/");
		} 
		else 
		{
			$smarty->caching=2;
			$smarty->compile_check=false;
			$smarty->template_dir=THpath."tpl/".$template."/";
			$smarty->cache_lifetime=-1;
			$smarty->assign("THtplurl",THurl."tpl/".$template."/");
		}
		$smarty->compile_dir=THpath."compd/";
		if ($id!=null && $admin==false && $smarty->is_cached($tpl,$id))
		{
			//$smarty->display($tpl,$id);
			echo $smarty->display($tpl,$id);
			if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $smarty->display("modscript.tpl",$id); }
			echo $smarty->display("bottombar.tpl",$id);
			die("<!-- Loaded from cache /-->");
		}
		$smarty->assign_by_ref("THcname",$id);
		$smarty->assign("THname",THname);
		$smarty->assign("THurl",THurl);
		$smarty->assign("THtpltest",THtpltest);
		$smarty->assign("THversion",THversion);
		$smarty->assign("THcodename",THcodename);  //we're trendy now right?
		$smarty->assign("THvc",THvc);
		$smarty->assign("THnewsboard",THnewsboard);
		$smarty->assign("THmodboard",THmodboard);
		$smarty->assign("THmaxfilesize",THmaxfilesize);
		$smarty->assign("THdefaulttext",THdefaulttext);
		$smarty->assign("THdefaultname",THdefaultname);
		$smarty->assign("THdatetimestring",THdatetimestring);
		$smarty->assign("THuserewrite",THuserewrite);
		$smarty->assign("GET",$_GET);
		$smarty->assign("THcookieid", THcookieid);
		$smarty->register_function("smcount","smcount");
		return($smarty);
	}
	
	function smcount($p,&$sm)
	{
		//Sweet mother, why didn't I think of this sooner?!
		//Smarty doesn't have an array counting function built in, so here's a wrapper for PHP's.
		if (isset($p['assign'])==true)
		{
			$sm->assign($p['assign'],count($p['array']));
		} else {
			return(count($p['array']));
		}
	}
	
	function smclearcache($board, $page=-1, $thread=-1, $delete_everything=false)
	{
		// Oh, we're actually clearing the cache for a thread
		if($thread != -1)
		{
			$files_to_delete = glob(THpath."cache/t".$board."-".$thread."*");
		}
		else if($page != -1) // clearing the cache for a page
		{
			$files_to_delete = glob(THpath."cache/b".$board."-".$page."-*");
		}
		else if($delete_everything == true) // delete board cache AND ALL thread caches (meant for use ONLY in fragboard)
		{
			$files_to_delete = glob(THpath."cache/b".$board."-*");
			$files_to_delete2 = glob(THpath."cache/t".$board."-*");
		}
		else // we're clearing the cache for a whole board.
		{
			$files_to_delete = glob(THpath."cache/b".$board."-*");
		}
		
		foreach($files_to_delete as $deletion)
		{
			unlink($deletion);
		}
	}

	function delimgs($badimgs)
	{
		//Delete these images
		foreach ($badimgs as $bad)
		{
			$bad=(int)$bad;
			if($bad!=0)
			{
				$pyath=THpath."images/".$bad."/";
				$it=opendir($pyath);
				while (($img=readdir($it))!==false)
				{
					if ($img{0}!=".")
					{
						unlink($pyath.$img);
					}
				}
				rmdir($pyath);
			}
		}
	}

	function bitlookup($ext)
	{
		// OH YEAH BITFLAGS
		if($ext == "jpg" || $ext == "jpeg"){
			return 1;
		}
		else if($ext == "gif"){
			return 2;
		}
		else if($ext == "png"){
			return 4;
		}
		else if($ext == "svg"){
			return 8;
		}
		else if($ext == "swf"){
			return 16;
		}
		else if($ext == "pdf"){
			return 32;
		}
	
		//not found!  should this error?
		return 0;
	}
	
	function is_in_csl($item, $csl)
	// CSL stands for comma separated list.  It's not
	// very complicated.
	{
		$items = explode(",",$csl);
		return in_array($item, $items);
	}
	//minor annoyance
	function replacewedge($input)
	{
		$output = str_replace("<", "&lt;", $input);
		$output = str_replace(">", "&gt;", $output);
		return $output;
	}
	
	//profile related functions follow
	function generateRandID()
	
	{
		return md5(generateRandStr(16));
	}
	
	/*
		generateRandStr - Generates a string made up of randomized
		letters (lower and upper case) and digits, the length
		is a specified parameter.
	*/
	function generateRandStr($length)
	{
		$randstr = "";
		for($i=0; $i<$length; $i++)
		{
			$randnum = mt_rand(0,61);
			if($randnum < 10)
			{
				$randstr .= chr($randnum+48);
			}
			else if($randnum < 36)
			{
				$randstr .= chr($randnum+55);
			} else {
				$randstr .= chr($randnum+61);
			}
		}
		return $randstr;
	}

	function sendWelcome($user, $email, $pass)
	{
		$from = "From: ".THprofile_emailname." <".THprofile_emailaddr.">";
		$subject = THname." welcomes you!";
		$body = $user.",\n\n"
			."Welcome! You've just registered at ".THname." "
			."with the following information:\n\n"
			."Username: ".$user."\n"
			."Password: ".$pass."\n\n"
			."If you ever lose or forget your password, a new "
			."password will be generated for you and sent to this "
			."email address, if you would like to change your "
			."email address you can do so by going to the "
			."My Account page after signing in.\n\n"
			."- ".THname." Automailer";
		return mail($email,$subject,$body,$from);
	}
	function sendNewPass($user, $email, $pass, $ip)
	{
		$from = "From: ".THprofile_emailname." <".THprofile_emailaddr.">";
		$subject = THname." - Your new password";
		$body = $user.",\n\n"
			."We recieved a request to reset your password from "
			."the IP ".$ip.".\n\n"
			."We've generated a new password for you at your "
			."request, you can use this new password with your "
			."username to log in to ".THname.".\n\n"
			."Username: ".$user."\n"
			."New Password: ".$pass."\n\n"
			."It is recommended that you change your password "
			."to something that is easier to remember, which "
			."can be done by going to the My Account page "
			."after signing in.\n\n"
			."- ".THname." Automailer";
		return mail($email,$subject,$body,$from);
	}
	function sendFuckOff($user, $email)
	{
		$from = "From: ".THprofile_emailname." <".THprofile_emailaddr.">";
		$subject = THname." : pending registration";
		$body = $user.",\n\n"
			."We regret to inform you that your request for an "
			."account at ".THname." with the username of: \"".$user
			."\" has been denied. This could have been done for a "
			."variety of reasons, and if you have any specific"
			."questions simply contact the administrators.\n\n"
			."- ".THname." Automailer";
		return mail($email,$subject,$body,$from);
	}

	function replacequote($filter)
	//fuck html!
	{
		// ~simple and clean is the way that you're making me feel tonight~
		// ^-- imagine tyam singing this while wearing a dress...
		return str_replace("'", "&#039;", $filter);
		// ...or a tank top

		//oh hello i found this comment thanks diff ~tyam
	}
	function replacedouble($filter)
	{
		return str_replace('"', "&#034;", $filter);
	}
	
	function writelog($actionstring,$type)
	{
		$logfile = fopen("unlinked/".$type.".log", "a") or error_log("Could not write ".$type." log: ".$actionstring."\n",3,"error.log");
		
		fwrite($logfile, strftime("%m/%d/%y %H:%M:%S",time()+(THtimeoffset*60)).": ".$_SESSION['username']."\t".$_SERVER['REMOTE_ADDR']."\t");
		fwrite($logfile, $actionstring."\n");
		fclose($logfile);
	}
	
	function checkadmin()  //quick, simple, to the point, this is how I like it
	{
		if($_SESSION['admin']!=true)
		{
			THdie("You are not logged in as an administrator!");
		}
	}
	
	function checklogin() //look for cookies, set session variables if needed
	{
		if(isset($_COOKIE[THcookieid."-uname"]) && isset($_COOKIE[THcookieid."-id"]))
		{
			// verify login information
			$db=new ThornProfileDBI();
			$userdata  = $db->getuserdata_cookielogin($_COOKIE[THcookieid."-uname"], $_COOKIE[THcookieid."-id"]);
			
			if($userdata == null)
			{
				// No dice.
				setcookie(THcookieid."-uname", "", time()-THprofile_cookietime, THprofile_cookiepath);
				setcookie(THcookieid."-id",   "", time()-THprofile_cookietime, THprofile_cookiepath);

				/* Unset PHP session variables */
				unset($_SESSION['username']);
				unset($_SESSION['userid']);
				unset($_SESSION['userlevel']);
				unset($_SESSION['admin']);
				unset($_SESSION['moderator']);
				unset($_SESSION['mod_array']);
				
			}
			elseif( !isset($_SESSION['username']) )
			{
				// Okay, they have a valid ID for a login, but no session data.  Let's rectify that.
				$_SESSION['username'] 	= $userdata['username'];
				$_SESSION['userlevel'] 	= $userdata['userlevel'];
				$_SESSION['admin'] 		= $userdata['mod_admin'];
				$_SESSION['mod_array'] 	= $userdata['mod_array'];
				$_SESSION['mod_global'] = $userdata['mod_global'];
					
				if ($userdata['mod_global'] || $userdata['mod_array'])
				{
					$_SESSION['moderator']=true;
				}
			}			
		}
//		elseif (isset($_SESSION['username']))
//		{
//			/* Unset PHP session variables */
//			unset($_SESSION['username']);
//			unset($_SESSION['userid']);
//			unset($_SESSION['userlevel']);
//			unset($_SESSION['admin']);
//			unset($_SESSION['moderator']);
//			unset($_SESSION['mod_array']);
//		}
	}
?>
