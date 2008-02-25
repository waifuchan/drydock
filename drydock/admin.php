<?php
	require_once("config.php");
	require_once("common.php");
	checkadmin(); //make sure the person trying to access this file is allowed to
	$db=new ThornModDBI();
	include("rebuilds.php");  //frown
	if(isset($_GET['rebuild']))
	{
		//all of these could have just changed
		rebuild_hovermenu();
		rebuild_linkbars();
		rebuild_rss();
		rebuild_htaccess();
		header("Location: ".THurl."admin.php?a=g");
		die();
	}
	if(isset($_GET['profilepic']))
	{
		if(isset($_GET['filename']))
		{
			if(!file_exists('./unlinked/'.$_GET['filename']))
			{
				die();
			}
			$pinfo=pathinfo('./unlinked/'.$_GET['filename']);
			$filetype=strtolower($pinfo['extension']);
			if($filetype == "jpg" || $filetype == "jpeg") { header("Content-type: image/jpg"); }
			else if ($filetype == "png") { header("Content-type: image/png"); }
			else if($filetype == "gif") { header("Content-type: image/gif"); }
			else { die(); }
			readfile('./unlinked/'.$_GET['filename']);
		}
	}
	$sm=sminit(null,null,null,true);
	//Admin Smarty setup; no caching (we probably broke this from Thorn :[ ~tyam)
	if (isset($_GET['a']))
	{
		if ($_GET['a']=="b")  //Board stuff
		{
			// We have to assign these to be able to set disabled attributes for the checkboxes for file formats
			// (i.e., greying out the SVG file type box when THenableSVG is 0)
			$sm->assign("THenableSVG",THenableSVG);
			$sm->assign("THenableSWFmeta",THenableSWFmeta);
			$sm->assign("THSVGthumbnailer", THSVGthumbnailer);
			
			//Assign other template sets
			$sets=array();
			//Read template sets
			$it=opendir(THpath."tpl/");
			while (($set=readdir($it))!==false)
			{
				//echo($set);
				if (in_array($set,array(".","..","_admin","_compd"))==false && is_dir(THpath."tpl/".$set)) //Should mebbe do a better test here... versions, etc
				{
					$sets[]=$set;
				}
			}
			$sm->assign_by_ref("tplsets",$sets);

			if ($_GET['boardselect'])
			{
				//Configure options for a specific board
				$sm->assign("boardselect",$_GET['boardselect']);
				$sm->assign("board",mysql_fetch_array($db->myquery("select * from ".THboards_table." where id=".intval($_GET['boardselect']))),$sm);
			} else {
				//Board list
				if (THdbtype==null)
				{
					//Can't access this unless the database is set up.
					THdie("ADdbfirst");
				}
				$sm->assign("boards",$db->getindex(array('full'=>true),$sm));
			}
			$sm->display("adminboard.tpl");
		}
		elseif ($_GET['a']=="x")
		{
			//Ban config		
			if ($_GET['banselect'])
			{
				//Edit a specific ban
				$sm->assign("banselect",$_GET['banselect']);
				$sm->assign("ban",mysql_fetch_array($db->myquery("select * from ".THbans_table." where ip=".intval($_GET['banselect']))),$sm);
				$ippull = $_GET['banselect'];
				$ipdata = explode(".",long2ip($ippull));
				$subnet = mysql_fetch_row($db->myquery("select subnet from ".THbans_table." where ip=".$_GET['banselect']));
				//this should be commented out right now because this code is a big hack job and i dont know what i am doing because it is 4:15am but it isnt so okay ~tyam
				if ($subnet[0])
				{
					$ipdata[3]="*";
				}
				$ipdata[]=array(
					"ip1"=>$ipdata[0],
					"ip2"=>$ipdata[1],
					"ip3"=>$ipdata[2],
					"ip4"=>$ipdata[3],
					"longip"=>$ippull
					);
				//ugh why isnt this working.  okay let's be retarded about it
				$sm->assign("ip1",$ipdata[0],$sm);
				$sm->assign("ip2",$ipdata[1],$sm);
				$sm->assign("ip3",$ipdata[2],$sm);
				$sm->assign("ip4",$ipdata[3],$sm);
				$sm->assign("iplong",$ippull,$sm);
			} else {
				if (THdbtype==null)
				{
					//Can't access this unless the database is set up.
					THdie("ADdbfirst");
				}
				$rawbans=$db->getallbans();
				if ($rawbans!=null)
				{
					$bans=array();
					foreach ($rawbans as $ban)
					{
						$ip=explode(".",long2ip($ban['ip']));
						if ($ban['subnet'])
						{
							$ip[3]="*";
						}
						$bans[]=array(
							"ip1"=>$ip[0],
							"ip2"=>$ip[1],
							"ip3"=>$ip[2],
							"ip4"=>$ip[3],
							"longip"=>$ban['ip'],
							"subnet"=>$ban['subnet'],
							"publicreason"=>$ban['publicreason'],
							"privatereason"=>$ban['privatereason'],
							"adminreason"=>$ban['adminreason'],
							"postdata"=>$ban['postdata'],
							"duration"=>$ban['duration'],
							"bantime"=>$ban['bantime'],
							"bannedby"=>$ban['bannedby'],
							);
					}
				} else {
					$bans=null;
				}
				$sm->assign("bans",$bans);
			}
			$sm->display("adminban.tpl");
		}

		//are tbese next two really needed?
		elseif ($_GET['a']=="t") //let's put thornlight here
		{
			include("recentpics.php");
		}
		elseif ($_GET['a']=="q") //let's put thornquasilight here
		{
			include("recentposts.php");
		}
		elseif ($_GET['a']=="c") //Capcodes
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			$queryresult = $db->myquery("SELECT * FROM ".THcapcodes_table);
			if ($queryresult!=null)
			{
				$capcodes=array();
				while ($capcode=mysql_fetch_assoc($queryresult))
				{
					$capcodes[]=$capcode;
				}
			} else {
				$capcodes=null;
			}
			//print_r($capcodes);
			rebuild_capcodes();
			$sm->assign("capcodes",$capcodes);
			$sm->display("admincapcodes.tpl");
		}
	
		//this block is a copy pasta from ordog's capcodes block with no changes other than a=?? ~tyam
		elseif ($_GET['a']=="w") //wordfilters
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			$queryresult = $db->myquery("SELECT * FROM ".THfilters_table);
			if ($queryresult!=null)
			{
				$filters=array();
				while ($filter=mysql_fetch_assoc($queryresult))
				{
					$filters[]=replacequote($filter);
				}
			} else {
				$filters=null;
			}
			//print_r($filters);
			rebuild_filters();
			$sm->assign("filters",$filters);
			$sm->display("adminfilters.tpl");
		}

		elseif ($_GET['a']=="p") //Profiles
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			
			//move this over to t=p eventually - tyam
			if((isset($_GET['action']) && $_GET['action']=="regyes") && isset($_GET['username'])){
			
				$db->myquery("UPDATE ".THusers_table.
				" SET approved=1 WHERE username='".mysql_real_escape_string($_GET['username'])."'");
			
				if(THprofile_emailwelcome){
				$email = $db->myresult("SELECT email FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				sendWelcome($username, $email);
				}
			}

			if((isset($_GET['action']) && $_GET['action']=="regno") && isset($_GET['username'])){
				$query = "UPDATE ".THusers_table.
				" SET approved='-1' WHERE username='".mysql_real_escape_string($_GET['username'])."'";
				$db->myquery($query);
				if(THprofile_emailwelcome){
				$email = $db->myresult("SELECT email FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				sendFuckOff($username, $email);
				}
			}

			if((isset($_GET['action']) && $_GET['action']=="capyes") && isset($_GET['username'])){
				//update or insert capcode
				/*
					check capcode table for match of existing capcode
					if match found, use update query, else, insert query
				*/
				$new_capcode = $db->myresult("SELECT proposed_capcode FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				$user_hash = $db->myresult("SELECT capcode FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				$already_there = $db->myresult("SELECT capcode_to FROM ".THcapcodes_table.
				" WHERE capcode_from='".mysql_real_escape_string($user_hash)."'");
				
				if($already_there != null){
				$db->myquery("UPDATE ".THcapcodes_table.
				" SET proposed_capcode='".mysql_real_escape_string($new_capcode).
				"' WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				}
				else{
				$db->myquery("INSERT INTO ".THcapcodes_table.
				" (capcode_from, capcode_to) VALUES('".mysql_real_escape_string($user_hash)."','".mysql_real_escape_string($new_capcode)."')");
				}
				
				// We don't need this anymore since it's no longer proposed
				$db->myquery("UPDATE ".THusers_table.
				" SET proposed_capcode=\"\" WHERE username='".mysql_real_escape_string($_GET['username'])."'");
			}

			if((isset($_GET['action']) && $_GET['action']=="capno") && isset($_GET['username'])){
				//this capcode isn't going to work for whatever reason, deny it
				
				$db->myquery("UPDATE ".THusers_table.
				" SET proposed_capcode='' WHERE username='".mysql_real_escape_string($_GET['username'])."'");
			}
			
			if((isset($_GET['action']) && $_GET['action']=="picyes") && isset($_GET['username'])){
				// Get the file extension of the wanted picture
				$desired_picture = $db->myresult("SELECT pic_pending FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				// Get the file extension of the current picture (if any)
				$old_picture = $db->myresult("SELECT has_picture FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				// Delete the old picture, if there is one
				if($old_picture)
					unlink(THpath.'images/profiles/'.$_GET['username'].'.'.$old_picture);
				
				// Move the new picture into the profiles directory
				rename(THpath.'unlinked/'.$_GET['username'].'.'.$desired_picture, THpath.'images/profiles/'.$_GET['username'].'.'.$desired_picture);
				
				// Update the db to reflect this
				$db->myquery("UPDATE ".THusers_table.
				" SET pic_pending='', has_picture='".mysql_real_escape_string($desired_picture).
				"' WHERE username='".mysql_real_escape_string($_GET['username'])."'");
			}
			
			if((isset($_GET['action']) && $_GET['action']=="picno") && isset($_GET['username'])){
				// Get the file extension
				$desired_picture = $db->myresult("SELECT pic_pending FROM ".
				THusers_table." WHERE username='".mysql_real_escape_string($_GET['username'])."'");
				
				// Delete the file
				unlink(THpath.'unlinked/'.$_GET['username'].'.'.$desired_picture);
				
				// Clear the db record
				$db->myquery("UPDATE ".THusers_table.
				" SET pic_pending='' WHERE username='".mysql_real_escape_string($_GET['username'])."'");
			}
						
			$queryresult = $db->myquery("SELECT * FROM ".THusers_table.
				" WHERE pic_pending IS NOT NULL OR proposed_capcode IS NOT NULL OR approved=0");
			if ($queryresult!=null)
			{
				$pend_regs=array();
				$pend_caps=array();
				$pend_pics=array();
				while ($user=mysql_fetch_assoc($queryresult))
				{
					if($user['approved'] == 0)
					{
					$pend_regs[]=array("username" => $user['username'],
									   "email" => $user['email']);
					}
					
					if($user['proposed_capcode'])
					{
					$pend_caps[]=array("username" => $user['username'],
									   "proposed_capcode" => $user['proposed_capcode']);
					}
					
					if($user['pic_pending'])
					{
					$pend_pics[]=array("username" => $user['username'],
									   "pic_pending" => $user['pic_pending']);
					}
					
				}
			} else {
				
				$pend_caps=null;
				$pend_pics=null;
				$pend_regs=null;
			}
			$sm->display("adminprofile.tpl");
		}
		elseif ($_GET['a']=="mp") // Manager post function
		{
			if ($_GET['board'])
			{
				$boardarray = mysql_fetch_array($db->myquery("select * from ".THboards_table." where id=".intval($_GET['board'])));
				if($boardarray)
				{
					$sm->assign("binfo",$boardarray,$sm);
					$sm->display("adminpost.tpl");
				} else {
					THdie("Invalid board ID provided.");
				}
			} else {
				THdie("No board ID provided!");
			}
		}
		elseif ($_GET['a']=="hk")  //housekeeping~
		{
			$sm->display("adminhouse.tpl");
		}
		elseif ($_GET['a']=="hkc") //housekeeping functions are actually called here
		{
			if($_POST['rs']) { rebuild_rss(); }
			if($_POST['ht']) { rebuild_htaccess(); }
			if($_POST['sl']) { rebuild_hovermenu(); }
			if($_POST['lb']) { rebuild_linkbars(); }
			if($_POST['sp']) { rebuild_spamlist(); }
			if($_POST['fl']) {  rebuild_filters(); }
			if($_POST['cp']) { rebuild_capcodes(); }
			if($_POST['all'])
					{
						rebuild_rss();
						rebuild_htaccess();
						rebuild_hovermenu();
						rebuild_linkbars();
						rebuild_spamlist();
						rebuild_filters();
						rebuild_capcodes();
					}
			header("Location: ".THurl."admin.php?a=hk");
		}

		elseif ($_GET['a']=="bl") //blotter
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			$queryresult = $db->myquery("SELECT * FROM ".THblotter_table);
			if ($queryresult!=null)
			{
				$blotter=array();
				while ($blot=mysql_fetch_assoc($queryresult))
				{
					$blotter[]=replacequote($blot);
				}
			} else {
				$blotter=null;
			}
			//print_r($filters);
			$sm->assign("blots",$blotter);
			
			$sm->assign("boards",$db->getindex(array('full'=>true),$sm));
			$sm->display("adminblotter.tpl");
		}
		elseif ($_GET['a']=="g") //general admin - boring old TH code, can still use Smarty since it's only reading
		{
			//Assume general options
			//Assign settings
			$sm->assign("THname",THname);
			$sm->assign("THthumbwidth",THthumbwidth);
			$sm->assign("THthumbheight",THthumbheight);
			$sm->assign("THjpegqual",THjpegqual);
			$sm->assign("THpixperpost",THpixperpost);
			$sm->assign("THtplset",THtplset);
			$sm->assign("THtpltest",THtpltest);
			$sm->assign("THdupecheck",THdupecheck);
			$sm->assign("THtimeoffset",THtimeoffset);
			$sm->assign("THvc",THvc);	
			$sm->assign("THnewsboard",THnewsboard);
			$sm->assign("THmodboard",THmodboard);
			$sm->assign("THdefaulttext",THdefaulttext);
			$sm->assign("THdefaultname",THdefaultname);
			$sm->assign("THdatetimestring",THdatetimestring);
			$sm->assign("THuserewrite",THuserewrite);
			$sm->assign("THpearpath", THpearpath);
			$sm->assign("THuseSWFmeta", THuseSWFmeta);
			$sm->assign("THSVGthumbnailer", THSVGthumbnailer);
			$sm->assign("THuseSVG", THuseSVG);
			$sm->assign("THusePDF", THusePDF);
			$sm->assign("THusecURL", THusecURL);
			$sm->assign("THprofile_adminlevel", THprofile_adminlevel);
			$sm->assign("THprofile_userlevel", THprofile_userlevel);
			$sm->assign("THprofile_emailname", THprofile_emailname);
			$sm->assign("THprofile_emailaddr", THprofile_emailaddr);
			$sm->assign("THprofile_emailwelcome", THprofile_emailwelcome);
			$sm->assign("THprofile_cookietime", THprofile_cookietime);
			$sm->assign("THprofile_cookiepath", THprofile_cookiepath);
			$sm->assign("THprofile_lcnames", THprofile_lcnames);
			$sm->assign("THprofile_maxpicsize", THprofile_maxpicsize);
			$sm->assign("THprofile_regpolicy", THprofile_regpolicy);
			$sm->assign("THprofile_viewuserpolicy", THprofile_viewuserpolicy);
			$sm->assign("pend_regs",$pend_regs);
			$sm->assign("pend_caps",$pend_caps);
			$sm->assign("pend_pics",$pend_pics);
			//Assign other template sets
			$sets=array();
			//Read template sets
			$it=opendir(THpath."tpl/");
			while (($set=readdir($it))!==false)
			{
				//echo($set);
				if (in_array($set,array(".","..","_admin","_compd"))==false && is_dir(THpath."tpl/".$set)) //Should mebbe do a better test here... versions, etc
				{
					$sets[]=$set;
				}
			}
			$sm->assign_by_ref("tplsets",$sets);
			$sm->assign("boards",$db->getindex(array('full'=>true),$sm));
			$sm->display("admingen.tpl");
		} else {
			THdie("Where are you going?");
		}
		die();
	} //end GET=a

	//If still alive here, assume $_GET['t'] is set.
	if ($_GET['t']=="g")
	{
		//General settings.
		if ($_POST['fragcache']=="on")
		{
			//Frag the cache. Don't do anything else.
			$sm->clear_all_cache();
			$sm->clear_compiled_tpl();
		} else {
			rebuild_config($_POST); //hope
			header("Location: ".THurl."admin.php?rebuild");  //fucking hack >:[
			die();
		}
		header("Location: ".THurl."admin.php?a=g");
		die();
	}
	elseif($_GET['t']=="p") // get it?  change profile settings ~tyam
	{
		$config = fopen(THpath."config-features.php", 'w');
		fwrite($config, '<?php'."\n");

		fwrite($config, '?>');  //some editors break colors here so <?
		//print_r($_POST);
		header("Location: ".THurl."admin.php?a=p");
		die();
	}
	elseif ($_GET['t']=="bl") //Update blotter
	{
		$entry = mysql_real_escape_string($_POST['post']);
		$board = $_POST['postto'];
		$time = time();
		$query = 'INSERT INTO '.THblotter_table.' SET entry="'.$entry.'",board="'.$board.'",time="'.$time.'"';
		$db->myquery($query);
		header("Location: ".THurl."admin.php?a=bl");
	}
	elseif ($_GET['t']=="ble") //Edit blotter
	{
		$queryresult = $db->myquery("SELECT * FROM ".THblotter_table);
		if ($queryresult!=null)
		{
			$blotter=array();
			while ($blot=mysql_fetch_assoc($queryresult))
			{
				$blotter[]=$blot;
			}
			foreach ($blotter as $blot)
			{
				if ($_POST['del'.$blot['id']])
				{
					$db->myquery("delete from ".THblotter_table." where id=".$blot['id']);
				} 
				else {
					$blotter_entry=array(
						'id'=>(int)$_POST['id'.$blot['id']],
						'text'=>mysql_real_escape_string($_POST['post'.$blot['id']]),
						'board'=>mysql_real_escape_string($_POST['postto'.$blot['id']])
					);
					//print_r($filter);
					$query='update '.THblotter_table.' set entry="'.$blotter_entry['text'].'", board="'.$blotter_entry['board'].
						'" where id='.$blotter_entry['id'];
					$db->myquery($query);
				}
			}
		}
		header("Location: ".THurl."admin.php?a=bl");
	}
	elseif ($_GET['t']=="b")  //edit boards
	{
		if($_POST['boardselect'])
		{
			if ($_POST['delete'.$_POST['boardselect']]==true) //Delete images on that board; nuke it from db
			{
				delimgs($db->fragboard($_POST['boardselect']));
				$db->myquery("DELETE from ".THboards_table." WHERE id=".intval($_POST['boardselect']));
			} else {
				$oldid=intval($_POST['boardselect']);
				$id=intval($_POST['id'.$oldid]);
				$globalid=intval($_POST['globalid'.$oldid]);
				$name=$_POST['name'.$oldid];
				$folder=$_POST['folder'.$oldid];
				$about=strip_tags($_POST['about'.$oldid], '<i><b><u><strike><p><br><font><a><ul><ol><li><marquee>');
				$rules=$_POST['rules'.$oldid];
				$perpg=intval($_POST['perpg'.$oldid]);
				$perth=intval($_POST['perth'.$oldid]);
				$hidden=($_POST['hidden'.$oldid]=="on");
				$allowedformats=intval($_POST['allowedformats'.$oldid]);
				$forced_anon=($_POST['forced_anon'.$oldid]=="on");
				$maxfilesize=intval($_POST['maxfilesize'.$oldid]);
				$allowvids=($_POST['allowvids'.$oldid]=="on");
				$customcss=($_POST['customcss'.$oldid]=="on");
				$filter=($_POST['filter'.$oldid]=="on");
				$boardlayout=$_POST['boardlayout'.$oldid];
				$requireregistration=($_POST['requireregistration'.$oldid]=="on");
				$tlock=($_POST['tlock'.$oldid]=="on");
				$rlock=($_POST['rlock'.$oldid]=="on");
				$tpix=intval($_POST['tpix'.$oldid]);
				$rpix=intval($_POST['rpix'.$oldid]);
				$tmax=intval($_POST['tmax'.$oldid]);
				$updatequery = "UPDATE ".THboards_table." set id=".$db->clean($id).",globalid=".$db->clean($globalid).",name='".$db->clean($name)."',folder='".$db->clean($folder)."',about='".$about."',rules='".$db->clean($rules)."',perpg='".$perpg."',perth='".$perth."',hidden='".$hidden."',allowedformats='".$db->clean($allowedformats)."',forced_anon='".$forced_anon."',maxfilesize='".$db->clean($maxfilesize)."',allowvids='".$allowvids."',customcss='".$customcss."',boardlayout='".$boardlayout."',requireregistration='".$requireregistration."',filter='".$filter."',rlock='".$rlock."',tlock='".$tlock."',tpix='".$tpix."',rpix='".$rpix."',tmax='".$tmax."' WHERE id=".$oldid;
				//print_r($updatequery);
				$db->myquery($updatequery);
			}
		} else {
			if ($_POST['namenew']!=null)  //Adding a new board
			{
				$id=(int)$_POST['idnew'];
				$globalid=0;
				$name=$_POST['namenew'];
				$folder=$_POST['foldernew'];
				$about=$_POST['aboutnew'];
				$rules=$_POST['rulesnew'];
				$perpg=20;
				$perth=4;
				$hidden=1;
				$allowedformats=7;
				$forced_anon=0;
				$filter=1;
				$maxfilesize=2097152;
				$allowvids=0;
				$customcss=0;
				$requireregistration=0;
				$boardlayout="drydock-image";
				$tlock=1;
				$rlock=1;
				$tpix=1;
				$rpix=1;
				$tmax=100;
				$query="insert into ".THboards_table." set id=".$db->clean($id).",globalid=".$globalid.",name='".$db->clean($name)."',folder='".$db->clean($folder)."',about='".$db->clean($about)."',rules='".$db->clean($rules)."',perpg='".$perpg."',perth='".$perth."',hidden='".$hidden."',allowedformats='".$allowedformats."',forced_anon='".$forced_anon."',maxfilesize='".$maxfilesize."',allowvids='".$allowvids."',customcss='".$customcss."',filter='".$filter."',boardlayout='".$boardlayout."',requireregistration='".$requireregistration."',rlock='".$rlock."',tlock='".$tlock."',tpix='".$tpix."',rpix='".$rpix."',tmax='".$tmax."'";
				$db->myquery($query);
				//print_r($query);
			}
			//CHECK FOR DUPE IDs
			$max=count($boards);
			for ($x=0;$x<$max;$x++)
			{
				$boreds[]=$boards[$x]['id'];
			}
		}
		//print_r($boards);
		$sm->clear_all_cache();
		$sm->clear_compiled_tpl();
		rebuild_htaccess();
		rebuild_linkbars();
		rebuild_hovermenu();
		header("Location: ".THurl."admin.php?a=b");
		die();
	}
	elseif ($_GET['t']=="ax") //Add ban
	{
		if ($_POST['ip4']=="")
		{
			$ip4="0";
		} else {
			if ($_POST['ipsub'])
			{
				$ip4="0";
			} else {
				$ip4=$_POST['ip4'];
			}
		}
		$ip=ip2long($_POST['ip1'].".".$_POST['ip2'].".".$_POST['ip3'].".".$ip4);
		if ($ip==-1 || $ip==false)
		{
			THdie("ADbanbadip");
		}
		$db->banip($ip,($_POST['ipsub']=="on"),$_POST['adminreason'],'admin ban',$_POST['adminreason'],'This is an admin ban, you were not banned for any one post.',$_POST['duration'],'admin');
		header("Location: ".THurl."admin.php?a=x");
	}
	elseif ($_GET['t']=="ux") //Remove ban
	{
		$ips=$db->getallbans();
		foreach ($ips as $ip)
		{
			if ($_POST['del'.$ip['ip']])
			{
				$db->delban($ip['ip']);
			}
		}
		header("Location: ".THurl."admin.php?a=x");
	}
	elseif ($_GET['t']=="ac") //Add capcode
	{
		if($_POST['capcodefrom'] == null || $_POST['capcodeto'] == null)
		{
			THdie('Invalid field provided.'); // don't know if this is right
		}
		$query = 'INSERT INTO '.THcapcodes_table.' SET capcodefrom="'.mysql_real_escape_string($_POST['capcodefrom']).'",capcodeto="'.mysql_real_escape_string($_POST['capcodeto']).'",notes="'.mysql_real_escape_string($_POST['notes']).'"';
		$db->myquery($query);
		header("Location: ".THurl."admin.php?a=c");
	}
	elseif ($_GET['t']=="rc") //Edit capcode
	{
		$queryresult = $db->myquery("SELECT * FROM ".THcapcodes_table);
		if ($queryresult!=null)
		{
			$capcodes=array();
			while ($capcode=mysql_fetch_assoc($queryresult))
			{
				$capcodes[]=$capcode;
			}
			foreach ($capcodes as $cap)
			{
				if ($_POST['del'.$cap['id']])
				{
					$db->myquery("delete from ".THcapcodes_table." where id=".$cap['id']);
				} else {
					$capcode=array(
						'id'=>(int)$_POST['id'.$cap['id']],
						'from'=>mysql_real_escape_string($_POST['from'.$cap['id']]),
						'to'=>mysql_real_escape_string($_POST['to'.$cap['id']]),
						'notes'=>mysql_real_escape_string($_POST['notes'.$cap['id']])
					);
					//print_r($capcode);
					$query='update '.THcapcodes_table.' set capcodefrom="'.$capcode['from'].'", capcodeto="'.$capcode['to'].'", notes="'.$capcode['notes'].'" where id='.$capcode['id'];
					$db->myquery($query);
				}
			}
		}
		header("Location: ".THurl."admin.php?a=c");
	}
	elseif ($_GET['t']=="aw") //Add wordfilter
	{
		if($_POST['filterfrom'] == null || $_POST['filterto'] == null)
		{
			THdie('Invalid field provided.'); // don't know if this is right
		}
		$query = 'INSERT INTO '.THfilters_table.' SET filterfrom="'.mysql_real_escape_string($_POST['filterfrom']).'",filterto="'.mysql_real_escape_string($_POST['filterto']).'",notes="'.mysql_real_escape_string($_POST['notes']).'"';
		$db->myquery($query);
		header("Location: ".THurl."admin.php?a=w");
	}
	elseif ($_GET['t']=="ew") //Edit filter
	{
		$queryresult = $db->myquery("SELECT * FROM ".THfilters_table);
		if ($queryresult!=null)
		{
			$filters=array();
			while ($filter=mysql_fetch_assoc($queryresult))
			{
				$filters[]=$filter;
			}
			foreach ($filters as $filt)
			{
				if ($_POST['del'.$filt['id']])
				{
					$db->myquery("delete from ".THfilters_table." where id=".$filt['id']);
				} else {
					$filter=array(
						'id'=>(int)$_POST['id'.$filt['id']],
						'from'=>mysql_real_escape_string($_POST['from'.$filt['id']]),
						'to'=>mysql_real_escape_string($_POST['to'.$filt['id']]),
						'notes'=>mysql_real_escape_string($_POST['notes'.$filt['id']])
					);
					//print_r($filter);
					$query='update '.THfilters_table.' set filterfrom="'.$filter['from'].'", filterto="'.$filter['to'].'", notes="'.$filter['notes'].'" where id='.$filter['id'];
					$db->myquery($query);
				}
			}
		}
		header("Location: ".THurl."admin.php?a=w");
	}
	elseif ($_GET['t']=="au") // Manually add user
	{
		$errorstring = "";
		if(isset($_POST['user']))
		{	
			$username = trim($_POST['user']);
			$password = trim($_POST['password']);
			$email = trim($_POST['email']);
				
			$nameexists = $db->myresult(
			"SELECT COUNT(*) FROM ".THusers_table." WHERE username='".mysql_real_escape_string($username)."'");
			
			if($nameexists)
			{
			$errorstring .= "Sorry, an account with this name already exists.<br>\n";
			}
			
			if(!eregi("^([0-9a-z\.-_])+$", $username))
			{
	        $errorstring .= "Sorry, your name must be alphanumeric and contain no spaces.<br>\n";
	        }
			
			
			if($password)
			{
				$passlength = strlen($password);
				if($passlength < 4)
				{
					$errorstring .= "Sorry, your password must be at least 4 characters.<br>\n";
				}
			}
			else
			{
				$errorstring .= "You must provide a password!<br>\n";
			}
			
			if(isset($_POST['email']) && strlen($email))
			{
		         /* Check if valid email address */
				if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email))
				{
					$errorstring .= "You must provide a valid email address!<br>\n";
				}
				
				if($db->myresult("SELECT COUNT(*) FROM ".THusers_table." WHERE email='".mysql_real_escape_string($email)."'"))
				{
					$errorstring .= "That email has already been used to register an account!<br>\n";
				}
			}
			else
			{
				$errorstring .= "You must provide an email address!<br>\n";
			}
			
			if($errorstring == "") 
			{ // No errors encountered so far, attempt to register
				$pass_md5 = md5(THsecret_salt.$password);
			
				$insertquery = "INSERT INTO ".THusers_table.
				" (username, password, userlevel, email, approved) VALUES ('".
				mysql_real_escape_string($username)."','".mysql_real_escape_string($pass_md5)."',".THprofile_userlevel.
				",'".mysql_real_escape_string($email)."',1)";
				
				$db->myresult($insertquery);
				header("Location: ".THurl."admin.php?a=p");
			}
	
			// <chopperdave> UHHHHHH OOOOOOHHHHHH </chopperdave>
			THdie($errorstring);
		}
	}
?>
