<?php
 /*
        drydock imageboard script (http://code.573chan.org/)
        File:           configure.php
        Description:    Handles installation of the script.

			Probably some more work could be done to reduce the size of this file.

        
        Unless otherwise stated, this code is copyright 2008 r
        by the drydock developers and is released under the
        Artistic License 2.0:
        http://www.opensource.org/licenses/artistic-license-2.0.php
    */
	include("version.php");
	function promptsetup()
	{
		$path=str_replace("/configure.php", "", $_SERVER['SCRIPT_FILENAME']);
		$url=str_replace("/configure.php", "", $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		echo "
<html><head>
<title>drydock ".THversion." installer<style type=\"text/css\">
body {
background-image:url('static/watermark.png');
background-repeat: no-repeat;
background-attachment: fixed;
background-position: bottom right; 
}
#main { margin-right:154px; }
.box { padding-left:10px; margin-bottom:10px; border-style:none; border-color:black; border-width:1px; }
.pgtitle { text-decoration:none; color:#2266AA; font-family:sans-serif; font-size:x-large; border-width:0px 0px 2px 0px; border-color:#FF6600; border-style:solid; margin-right:10px; margin-top:5px; }
</style></head><body>
<div id=\"main\">
    <div class=\"box\">
        <div class=\"pgtitle\">
            Drydock Installation Script
        </div>
	<br>
Welcome to the drydock configuration script.  First, thank you for your choice to set sail with us.  If you have not already done so, please read as much of the documentation as you can.  While we will not help you set up drydock on your own server (as in, we will not do it for you), we will be more than happy to answer any questions you might have.<br /><br />
Fill out the following boxes and checkmarks.  If you did it wrong, you'll get some errors and possible solutions on the next page.  If you don't know what something means, either look it up in the documentation, or leave it at the default value (usually blank).  All of these settings may be changed later.<br />
        <div class=\"pgtitle\">
            Location Settings
        </div>
	<br>
The script will attempt to guess these values, but often times you will need to correct them yourself.<br />
<b>IF THESE VALUES ARE NOT RIGHT, THIS SCRIPT WILL NOT WORK.</b>  If you are installing on a Windows system, forward slashes may not work (but you should try them first).  Be sure to double check your path before assuming the script is correct.<br />
<form method=\"post\" enctype=\"multipart/form-data\" action=\"configure.php?initialsetup\">
Your image board file path (<b>with trailing slash</b>):<br />
<input type=\"text\" name=\"path\" size=\"40\" value=\"".$path."/\" /><br />
Your image board URL:<br />
<input type=\"text\" name=\"url\" size=\"40\" value=\"http://".$url."/\" /><br />
        <div class=\"pgtitle\">
            Admin Settings
        </div>
	<br>
In order to access the admin panel and do various moderation related tasks, you must set up a super user account.  This will be your main administrative account.<br/>
	Admin name: <input type=\"text\" name=\"adminname\" size=\"12\" /><br />
	Admin pass: <input type=\"password\" name=\"adminpass\" size=\"12\" /><br />
	Verify pass: <input type=\"password\" name=\"adminpassver\" size=\"12\" /><br />
        <div class=\"pgtitle\">
            Database Settings
        </div>
	<br>
Now it gets tricky.  The only thing that is supported (at this time) is MySQL databases.  
Hopefully you have access to this and know your information.  Otherwise, ask your hosting provider for the details.<br />
If you have a database set up, there is a change you could encounter some errors.  
If possible, install drydock to its own database.  You must do this manually due to different server configurations.<br />
<br />
This script will delete ALL existing data from the database tables the script uses.<br />
It is therefore suggested that you install the script on its own database.  However if this is not possible for you due to hosting constraints, you should define a prefix
for all database tables.  If you seet the prefix as blank and your database is wiped, this is not our fault.  By default, tables are prefixed with drydock_.
This should prevent any issues you may have, except when installing multiple copies of drydock, and a few other rare issues we've encountered.<br />
<br /><b>This is your warning.  If you are reinstalling drydock and these tables exist, they will be deleted.</b><br /><br />
	Database table prefix: <input type=\"text\" name=\"THdbprefix\" size=\"12\" value=\"drydock_\"/><br />
	Database server: <input type=\"text\" name=\"THdbserver\" size=\"12\" /><br />
	Database username: <input type=\"text\" name=\"THdbuser\" size=\"12\" /><br />
	Database password: <input type=\"password\" name=\"THdbpass\" size=\"12\" /><br />
	Database name: <input type=\"text\" name=\"THdbbase\" size=\"12\" /><br />

        <div class=\"pgtitle\">
            Extra Settings
        </div>
	<br>
Everything should work just fine if you leave these defaults in place, but in certain situations it may be
better for you to change it.  	<br /><br />
The following features require external libraries that we do not distribute or control. SVG support is
currently limited to ImageMagick.  The next release should allow you to select different programs (such as rsvg).<br />
Please see the documentation for more information about these settings.  If you're not sure, leave them blank.<br />
	Path to PEAR <input type=\"text\" name=\"THpearpath\" size=\"12\" /><br />
	Enable SWF metatag support (requires PEAR libraries) <input type=\"checkbox\" name=\"THuseSWFmeta\" ><br />
	Enable SVG support (requires PEAR libraries and rsvg, ImageMagick, or other external library) <input type=\"checkbox\" name=\"THuseSVG\" ><br />
<input type=\"submit\" value=\"Submit\" />
</div></div>
</form></body></html>
		";
	}
	function smsimple()
	{
		require_once($_POST['path']."_Smarty/Smarty.class.php");
		$sm=new Smarty;
		//$sm->debugging=true;
		return($sm);
	}

	function veryfirstinit() //here goes - this is mostly albright's code with our own stuff tacked on to it ~tyam
	{
		//We're just now setting up the board.
		$path=$_POST['path'];
		if ($path{strlen($path)-1}!="/") { $path.="/"; }
		$url=$_POST['url'];
		if ($url{strlen($url)-1}!="/") { $url.="/"; }
		//Attempt to touch a file in the directories that need to be chmodded.
		$chmod=array();
		//List of places that must be writable at least by the server
		$paths=array($path,$path."compd/",$path."cache/",$path."captchas/",$path."images/",$path."unlinked/",$path."menu.php",$path."linkbar.php",$path."rss.xml"$path.".htaccess",$path."unlinked/.htaccess");
		foreach ($paths as $pith)
		{
			if (touch($pith."test")==false)
			{
				$chmod[]=$pith;
			} else {
				unlink($pith."test");
			}
		}
		if (count($chmod)>0)
		{
			die("<h2>Oh no!</h2>Please change the permissions mode on the following directories/folders to 777.<br />
				See the documentation for more information.<br /><br />".implode("<br />",$chmod)."<br />
				You can try the following command to do it all in one go:<br />chmod 0777 ".implode(" ",$chmod)."<br />
				Basically the server (specifically the <b>user</b> that the http server runs as) needs to be able to read and write these.");
				//please let them not screw this up, I don't want to deal with it
		}
		if(!empty($_POST['THdbprefix'])) { $prefix = $_POST['THdbprefix']; } else { $prefix = ""; }
		//keep drydock cookies from being useful on each drydock site
		$seed = mt_rand(0,100000); // I like the Mersenne Twister random number generation more.
		// lol.
		// It's 4:30 AM and I can't think of a better way to generate a random character string (generated via the Mersenne Twister algorithm), to be used for
		// salting passwords before they're defined in the
		sprintf($secret_salt, "%c%c%c%c%c%c%c%c", mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), 
												  mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), mt_rand(40,126) );
		$cookieid = "dd".$seed;
		//Let's make the initial config file
		$sm=smsimple();
		$sm->caching=0;
		$sm->compile_dir=$path."compd/";
		$sm->template_dir=$path."tpl/_admin/";
		$sm->cache_lifetime=0;
		$sm->assign("THtplurl",$url."tpl/_admin/");
		//let's write to config
		if(touch($_POST['path']."config.php")==false)
		{
			die($_POST['path']."config.php cannot be written");
		} else {
			//write a quick config, this format isn't good, but hopefully they'll change something in the regular config and trigger a rewrite
			$config = fopen($_POST['path']."config.php", 'w');
			fwrite($config, '<?php'."\n");
			fwrite($config, '$configver=4;'."\n");
			fwrite($config, 'define("THpath","'.$path.'");'."\n");
			fwrite($config, 'define("THurl","'.$url.'");'."\n");
			fwrite($config, 'define("THdbtype","MySQL");'."\n");  //only thing supported right now
			fwrite($config, 'define("THdbserver","'.$_POST['THdbserver'].'");'."\n");
			fwrite($config, 'define("THdbuser","'.$_POST['THdbuser'].'");'."\n");
			fwrite($config, 'define("THdbpass","'.$_POST['THdbpass'].'");'."\n");
			fwrite($config, 'define("THdbbase","'.$_POST['THdbbase'].'");'."\n");
			fwrite($config, 'define("THbans_table","'.$prefix."bans".'");'."\n");
			fwrite($config, 'define("THblotter_table","'.$prefix."blotter".'");'."\n");
			fwrite($config, 'define("THboards_table","'.$prefix."boards".'");'."\n");
			fwrite($config, 'define("THcapcodes_table","'.$prefix."capcodes".'");'."\n");
			fwrite($config, 'define("THextrainfo_table","'.$prefix."extra_info".'");'."\n");
			fwrite($config, 'define("THfilters_table","'.$prefix."filters".'");'."\n");
			fwrite($config, 'define("THimages_table","'.$prefix."imgs".'");'."\n");
			fwrite($config, 'define("THreplies_table","'.$prefix."posts".'");'."\n");
			fwrite($config, 'define("THthreads_table","'.$prefix."threads".'");'."\n");
			fwrite($config, 'define("THusers_table","'.$prefix."users".'");'."\n");
			fwrite($config, 'define("THsecret_salt","'.$secret_salt.'");'."\n");  //salt for passwords
			fwrite($config, 'define("THname","drydock image board");'."\n");
			fwrite($config, 'define("THtplset","drydock-image");'."\n");
			fwrite($config, 'define("THcookieid","'.$cookieid.'");'."\n");  //cookie seed
			fwrite($config, 'define("THcaptest", 0);'."\n");
			fwrite($config, 'define("THtpltest",1);'."\n");  //cannot turn off until we fix cache
			fwrite($config, 'define("THthumbheight",100);'."\n");
			fwrite($config, 'define("THthumbwidth",150);'."\n");
			fwrite($config, 'define("THjpegqual",65);'."\n");
			fwrite($config, 'define("THdupecheck", 1);'."\n");
			fwrite($config, 'define("THtimeoffset",0);'."\n");
			fwrite($config, 'define("THvc", 0);'."\n");
			fwrite($config, 'define("THnewsboard",0);'."\n");
			fwrite($config, 'define("THmodboard",0);'."\n");
			fwrite($config, 'define("THdefaulttext","No text entered");'."\n");
			fwrite($config, 'define("THdefaultname","Anonymous");'."\n");
			fwrite($config, 'define("THdatetimestring","%m/%d/%y(%a)%H:%M:%S");'."\n");
			fwrite($config, 'define("THSVGthumbnailer",0);'."\n");
			//this is a mess, i won't fix it later so i won't promise to   ~tyam :]
			fwrite($config, "define(\"THuserewrite\", 0);\ndefine(\"THpearpath\",");
			fwrite($config, "\"".$_POST['THpearpath']."\"");
			fwrite($config, ");\ndefine(\"THuseSWFmeta\",");
			if($_POST['THuseSWFmeta']=="on"){fwrite($config, "1");}else{fwrite($config, "0");}
			fwrite($config, ");\ndefine(\"THuseSVG\",");
			if($_POST['THuseSVG']=="on"){fwrite($config, "1");}else{fwrite($config, "0");}
			fwrite($config, ");\ndefine(\"THusePDF\",0);");
			fwrite($config, "\ndefine(\"THusecURL\",");
			if($_POST['THusecURL']=="on"){fwrite($config, "1");}else{fwrite($config, "0");}
			fwrite($config, ");\ndefine(\"THSVGthumbnailer\",0);");
			fwrite($config, 'define("THprofile_adminlevel",9);'."\n");
			fwrite($config, 'define("THprofile_userlevel",1);'."\n");
			fwrite($config, 'define("THprofile_emailname","CHANGE THIS");'."\n");
			fwrite($config, 'define("THprofile_emailaddr","THIS IS NOT AN EMAIL");'."\n");
			fwrite($config, 'define("THprofile_emailwelcome",0);'."\n");
			fwrite($config, 'define("THprofile_cookietime",8640000);'."\n");
			fwrite($config, 'define("THprofile_cookiepath","/");'."\n");
			fwrite($config, 'define("THprofile_lcnames",0);'."\n");
			fwrite($config, 'define("THprofile_maxpicsize",512000);'."\n");
			fwrite($config, 'define("THprofile_regpolicy",1);'."\n");
			fwrite($config, 'define("THprofile_viewuserpolicy",1);'."\n");
			fwrite($config, '?>');
			fclose($config);  //file's closed, fwrites, etc
		}
		//one more ugly hack
		$admin = fopen($path."admintemp.php", "w");
		fwrite($admin, "<?php\n");
		fwrite($admin, 'define("TEMPadminpass", "'.md5($secret_salt.$_POST['adminpass']).'");'."\n");
		fwrite($admin, 'define("TEMPadminname","'.$_POST['adminname'].'");'."\n");
		fwrite($admin, '?>'); //<? breaks colors sometimes here
		fclose($admin);
		//dump them out to the next part
		parttwo($_POST['path']);
	}//veryfirstint()
	
	function parttwo($path)
	{
		require_once($path."config.php");
		require_once($path."admintemp.php");  //hack hack hack ~tyam
		//Initial DB setup - oh god
		$link = mysql_connect(THdbserver,THdbuser,THdbpass);
		@mysql_select_db(THdbbase) or diestring();
		writedb();
		makeuser($path);
		mysql_close($link);
		partthree();
	}
	function diestring()
	{
		die("Database settings not correct. :[");  //this doesn't always do what we need
	}
	function writedb ()
	{
		//well, this is a bad idea!  let's just nuke everything!  we warned them!  hope this wasn't important~  ~tyam
		mysql_query('DROP TABLE IF EXISTS `'.THbans_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THblotter_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THboards_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THcapcodes_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THextrainfo_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THfilters_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THimages_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THreplies_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THthreads_table.'`') or diestring();
		mysql_query('DROP TABLE IF EXISTS `'.THusers_table.'`') or diestring();
	
		//set up the schema from scratch
	mysql_query("CREATE TABLE `".THbans_table."` (
	`ip` int(11) NOT NULL default '0',
	`subnet` tinyint(3) unsigned NOT NULL default '0',
	`publicreason` longtext  NOT NULL,
	`privatereason` longtext  NOT NULL,
	`adminreason` longtext  NOT NULL,
	`postdata` longtext  NOT NULL,
	`duration` int(11) NOT NULL default '-1',
	`bantime` int(11) unsigned NOT NULL,
	`bannedby` varchar(255)  NOT NULL,
	PRIMARY KEY  (`ip`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THblotter_table."` (
	`id` mediumint(8) unsigned NOT NULL auto_increment,
	`time` int(10) NOT NULL,
	`entry` text collate utf8_unicode_ci NOT NULL,
	`board` text collate utf8_unicode_ci NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THboards_table."` (
	`id` smallint(5) unsigned NOT NULL auto_increment,
	`globalid` mediumint(9) NOT NULL default '0',
	`name` text  NOT NULL,
	`folder` varchar(5)  NOT NULL,
	`about` text  NOT NULL,
	`rules` text  NOT NULL,
	`perpg` tinyint(3) unsigned NOT NULL default '20',
	`perth` tinyint(3) unsigned NOT NULL default '4',
	`hidden` tinyint(3) unsigned NOT NULL default '0',
	`allowedformats` tinyint(3) unsigned NOT NULL default '7',
	`forced_anon` tinyint(1) NOT NULL default '0',
	`maxfilesize` int(11) NOT NULL default '2097152',
	`maxres` int(5) NOT NULL default '3000',
	`thumbres` int(5) NOT NULL default '150',
	`pixperpost` int(2) NOT NULL default '8',
	`allowvids` tinyint(1) NOT NULL default '0',
	`customcss` tinyint(1) NOT NULL default '0',
	`filter` tinyint(1) NOT NULL default '1',
	`boardlayout` char(255) NOT NULL default 'drydock-image',
	`requireregistration` tinyint(1) NOT NULL default '0',
	`tlock` tinyint(3) unsigned NOT NULL default '0',
	`rlock` tinyint(3) unsigned NOT NULL default '0',
	`tpix` tinyint(3) unsigned NOT NULL default '0',
	`rpix` tinyint(3) unsigned NOT NULL default '0',
	`tmax` smallint(5) unsigned NOT NULL default '100',
	`lasttime` int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THcapcodes_table."` (
	`id` int(11) NOT NULL auto_increment,
	`capcodefrom` varchar(11)  NOT NULL,
	`capcodeto` text  NOT NULL,
	`notes` text  NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THextrainfo_table."` (
	`id` int(11) NOT NULL auto_increment,
	`extra_info` longtext  NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THfilters_table."` (
	`id` int(11) NOT NULL auto_increment,
	`filterfrom` text collate utf8_unicode_ci NOT NULL,
	`filterto` text collate utf8_unicode_ci NOT NULL,
	`notes` text collate utf8_unicode_ci NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THimages_table."` (
	`id` mediumint(8) unsigned NOT NULL,
	`hash` varchar(40) NOT NULL default '',
	`name` tinyblob NOT NULL,
	`width` smallint(5) unsigned NOT NULL default '0',
	`height` smallint(5) unsigned NOT NULL default '0',
	`tname` tinyblob NOT NULL,
	`twidth` smallint(5) unsigned NOT NULL default '0',
	`theight` smallint(5) unsigned NOT NULL default '0',
	`fsize` smallint(5) unsigned NOT NULL default '0',
	`anim` tinyint(4) default '0',
	`extra_info` int(11) unsigned NOT NULL default '0',
	KEY `id` (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THusers_table."` (
	`username` varchar(30) NOT NULL default '',
	`password` varchar(32) default NULL,
	`userid` varchar(32) default NULL,
	`userlevel` tinyint(1) unsigned NOT NULL default '1',
	`email` varchar(50) default NULL,
	`mod_array` varchar(100) NOT NULL default '0',
	`mod_global` tinyint(1) NOT NULL default '0',
	`mod_admin` tinyint(1) NOT NULL default '0',
	`timestamp` int(11) unsigned NOT NULL default '0',
	`age` varchar(3)  default NULL,
	`gender` varchar(1)  default NULL,
	`location` text ,
	`contact` longtext ,
	`description` longtext ,
	`capcode` varchar(11)  default NULL,
	`has_picture` varchar(4)  default NULL,
	`approved` tinyint(1) NOT NULL default '0',
	`pic_pending` varchar(4)  default NULL,
	`proposed_capcode` text  default NULL,
	PRIMARY KEY  (`username`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THreplies_table."` (
	`id` mediumint(8) unsigned NOT NULL auto_increment,
	`globalid` mediumint(9) NOT NULL default '0',
	`board` tinyint(3) unsigned NOT NULL default '0',
	`thread` mediumint(8) unsigned NOT NULL default '0',
	`title` text,
	`name` text  NOT NULL,
	`trip` varchar(11) NOT NULL default '',
	`body` longtext  NOT NULL,
	`time` int(10) unsigned NOT NULL default '0',
	`ip` int(11) NOT NULL default '0',
	`pin` tinyint(3) NOT NULL,
	`lawk` tinyint(3) NOT NULL,
	`bump` tinyint(3) unsigned NOT NULL default '1',
	`imgidx` mediumint(8) unsigned NOT NULL default '0',
	`visible` tinyint(1) NOT NULL default '1',
	`unvisibletime` int(10) NOT NULL default '0',
	`permasage` tinyint(1) unsigned NOT NULL default '0',
	`link` text  NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	mysql_query("CREATE TABLE `".THthreads_table."` (
	`id` mediumint(8) unsigned NOT NULL auto_increment,
	`globalid` mediumint(9) NOT NULL default '0',
	`board` tinyint(3) unsigned NOT NULL default '0',
	`thread` mediumint(8) default NULL,
	`title` text  NOT NULL,
	`name` text  NOT NULL,
	`trip` varchar(11) NOT NULL default '',
	`body` longtext  NOT NULL,
	`time` int(10) unsigned NOT NULL default '0',
	`ip` int(11) NOT NULL default '0',
	`pin` tinyint(3) unsigned NOT NULL default '0',
	`lawk` tinyint(3) unsigned NOT NULL default '0',
	`bump` int(10) unsigned NOT NULL default '0',
	`imgidx` mediumint(8) unsigned NOT NULL default '0',
	`visible` tinyint(1) NOT NULL default '1',
	`unvisibletime` int(10) NOT NULL default '0',
	`permasage` tinyint(1) unsigned NOT NULL default '0',
	`link` text  NOT NULL,
	PRIMARY KEY  (`id`)
	) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci") or diestring();
	return;
	}//db written?

	function makeuser ($path)
	{
		//shove in some defaults for our new database
		//set up our admin user
		$query = "INSERT INTO `".THusers_table."` ( `username` , `password` , `userid` , `userlevel` , `email` , 	`mod_array` , `mod_global` , `mod_admin` , `timestamp` , `age` , `gender` , `location` , `contact` , 	`description` , `capcode` , `has_picture` , `approved` , `pic_pending` , `proposed_capcode` ) VALUES 
			('".TEMPadminname."', '".TEMPadminpass."', '".TEMPadminpass."', '9', NULL , 	'0', '0', '1', '0', NULL , NULL , NULL , NULL , NULL , NULL , NULL , '1', NULL , NULL)";
		mysql_query($query) or diestring();
		unlink($path."admintemp.php");  //hack hack hack ~tyam
	}

function partthree()
{
echo "
<html><head><style type=\"text/css\">
body {
background-image:url('static/watermark.png');
background-repeat: no-repeat;
background-attachment: fixed;
background-position: bottom right; 
#main { margin-right:154px; }
.box { padding-left:10px; margin-bottom:10px; border-style:none; border-color:black; border-width:1px; }
.pgtitle { text-decoration:none; color:#2266AA; font-family:sans-serif; font-size:x-large; border-width:0px 0px 2px 0px; border-color:#FF6600; border-style:solid; margin-right:10px; margin-top:5px; }
}
</style></head><body>
If you're reading this and there are no crazy errors anywhere around here, it looks like everything went through okay.  But you might want to check out everything for yourself.<br>
<br>
There's still some setup you'll need to do on your own. Here is a tasklist for you to follow:<br>
<li>Delete the configuration.php script (or move it) for security reasons.  The script will not allow visitors while this area exists.
<li><a href=\"".THurl."profiles.php?action=login\">Log in</a> with the username/password you created.<br/>
<li>Under housekeeping functions, rebuild all items.
<li>Configure the rest of the settings in the general administration area.
<li>Set up boards.
<li>Run \"REBUILD ALL\" under <a href=\"".THurl."admin.php?a=hk\">housekeeping</a> in the admin area.
<li>?????
<li>PROFIT!
</ol>
Happy posting!  And don't forget, if you make any neat changes to the code, we'd love to see it.  You can post about it on the <a href=\"http://573chan.org/dry/\">drydock discussion</a> on 573chan.org.
</body></html>
";
initial_builds();
return;
}
	// Build menu, empty wordfilters cache, empty capcodes cache, empty spam blacklist cache
	function initial_builds()
	{
		$sidelinks = fopen($_POST['path']."menu.php", "w") or die("Could not open menu.php for writing.");
		fwrite($sidelinks, '<div id="idxmenu">'."\n".
				'<div id="idxmenuitem">'."\n".
				'<div class="idxmenutitle">'."\n");
		fwrite($sidelinks, '<?php if($_SESSION["admin"]){ echo "'."\n");
		fwrite($sidelinks, "<a href=".THurl."admin.php?a=hk>Housekeeping Functions</a><br />\";\n");
		fwrite($sidelinks, '} ?>'."\n");
		fwrite($sidelinks, '<a href="'.THurl.'">Site Index</a><br />'."\n");
		fwrite($sidelinks, '<?php if($_SESSION["username"]) {'."\n".'echo "<a href=".THurl."profiles.php?action=logout>Log Out</a> / <a href=".THurl."profiles.php>Profiles</a>";'."\n".' } else {'."\n");
		fwrite($sidelinks, 'echo "<a href=".THurl."profiles.php?action=login>Login</a>'."\n".' / '."\n".'<a href=".THurl."profiles.php?action=register>Register</a>";'."\n".'}?>'."\n");
		fwrite($sidelinks, '</div>');
		fwrite($sidelinks, '</div>'."\n");
		fclose($sidelinks);
		
		$fp_cache = fopen($_POST['path']."cache/filters.php", "w") or die("Could not open cache/filters.php for writing.");
		fwrite($fp_cache, "<?php\n");
		fwrite($fp_cache, '$to'."=();\n");
		fwrite($fp_cache, '$from'."=();\n");
		fwrite($fp_cache, "?>");
		fclose($fp_cache);		
		
		$fp_cache = fopen($_POST['path']."cache/capcodes.php", "w") or die("Could not open cache/capcodes.php for writing.");
		fwrite($fp_cache, "<?php\n");
		fwrite($fp_cache, '$capcodes'."=();\n");
		fwrite($fp_cache, "?>");
		fclose($fp_cache);		

		$fp_cache = fopen($_POST['path']."cache/blacklist.php", "w") or die("Could not open cache/blacklist.php for writing.");
		fwrite($fp_cache, "<?php\n");
		fwrite($fp_cache, '$spamblacklist'."=();\n");
		fwrite($fp_cache, "?>");
		fclose($fp_cache);	

		$htaccess = fopen($_POST['path']."unlinked/.htaccess", "w") or die("Could not open unlinked/htaccess for writing.");
		fwrite($htaccess, "#drydock htaccess module\n");
		fwrite($htaccess, "Deny from all\n");
		fclose($htaccess);

		return;
	}

	@header('Content-type: text/html; charset=utf-8');
	if (function_exists("mb_internal_encoding"))
	{
		//Unicode support :]
		mb_internal_encoding("UTF-8");
		mb_language("uni");
		mb_http_output("UTF-8");
	}
	if (file_exists("../config.php"))
	{
		//uh oh, looks like we've already tried to set up
		die("The configuration file already exists.  If you are trying to reinstall, please delete config.php");
	}
	elseif (isset($_GET['initialsetup']))
	{
		if ($_POST['adminpassver'] == $_POST['adminpass'])
		{
			if(isset($_POST['adminpassver']))
			{
				//we started our installer
				veryfirstinit();
			} else {
				die("Admin password is not set.");
			}
		} else {
			die("Passwords do not match.");
		}
	} else {
		//nothing has been started hopefully
		promptsetup();
		die();
	}
?>
