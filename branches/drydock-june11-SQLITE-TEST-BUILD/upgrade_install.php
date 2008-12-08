<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:           		upgrade_install.php
		Description:	Handles upgrading of a MySQL-based drydock install from 0.3.0 to 0.3.1

		todo:
		figure out what's going on with THdbprefix
		rewrite config.php in step 2
		
		Unless otherwise stated, this code is copyright 2008
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once("config.php");
	require_once("dbi/MySQL-dbi.php");
	
	@header('Content-type: text/html; charset=utf-8');
	if (function_exists("mb_internal_encoding")) {
		//Unicode support :]
		mb_internal_encoding("UTF-8");
		mb_language("uni");
		mb_http_output("UTF-8");
	}

	// Add post passwords. (Type 1)
	function add_post_passwords()
	{
		$dbi = new ThornDBI();
		
		// First verify if the table is already upgraded.
		$result = $dbi->myresult("SHOW COLUMNS FROM `".THposts_table."` LIKE 'password'");
		if( mysql_num_rows($result) > 0 )
		{
			die("Posts table has already been modified!");
		}
				
		$query = "ALTER TABLE `".THthreads_table."` ADD `password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";
		$result = $dbi->myquery($query);
		
		// Catch and display errors
		if($result === null)
		{
			echo "Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
		}
		else
		{
			// Since that worked, alter the posts table	
			$query = "ALTER TABLE `".THposts_table."` ADD `password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";
			$result = $dbi->myquery($query);
			
			if($result === null)
			{
				echo "Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
			}
			else
			{
				echo "Success!";
			}
		}
	}
	
	// Add new tables.  (Type 2)
	function add_new_tables()
	{
		$dbi = new ThornDBI();
	
		// Add new tables (use THdbprefix)
		$query = "CREATE TABLE IF NOT EXISTS `".THdbprefix."banhistory` 
		( 
		`id` int unsigned NOT NULL auto_increment, 
		`ip_octet1` int NOT NULL, 
		`ip_octet2` int NOT NULL, 
		`ip_octet3` int NOT NULL, 
		`ip_octet4` int NOT NULL, 
		`publicreason` text  NOT NULL, 
		`privatereason` text  NOT NULL, 
		`adminreason` text  NOT NULL, 
		`postdata` longtext  NOT NULL, 
		`duration` int(11) NOT NULL default '-1', 
		`bantime` int(11) unsigned NOT NULL, 
		`bannedby` varchar(100)  NOT NULL, 
		`bannedby` varchar(100)  NOT NULL, 
		`unbaninfo` text NOT NULL 
		PRIMARY KEY  (`id`) 
		) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci;";
		
		$result = $dbi->myquery($query);
		
		if($result === null)
		{
			die "CREATE Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
		}

		// Add new tables (use THdbprefix)
		$query = "CREATE TABLE IF NOT EXISTS `".THdbprefix."reports` 
		( 
		`id` int unsigned NOT NULL auto_increment, 
		`ip` int(11) NOT NULL default '0',
		`time` int(11) unsigned NOT NULL,
		`postid` int unsigned NOT NULL default '0',
		`board` smallint(5) unsigned NOT NULL default '0',
		`status` tinyint(1) unsigned NOT NULL default '0'
		PRIMARY KEY  (`id`) 
		) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci;";
		
		$result = $dbi->myquery($query);
		
		if($result === null)
		{
			die "CREATE Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
		}
		
		// Rewrite config.php
	}
	
	// Upgrade the bans table. (Type 3)
	function upgrade_bans_table()
	{
		$dbi = new ThornDBI();
		
		// First verify if the table is already upgraded.
		$result = $dbi->myresult("SHOW COLUMNS FROM `".THbans_table."` LIKE 'ip_octet3'");
		if( mysql_num_rows($result) > 0 )
		{
			die("Bans table has already been modified!");
		}
				
		// Get all of the old bans from the DB
		$bans = $dbi->mymultiarray("SELECT * FROM `".THbans_table."` WHERE 1"); // This could take a while.
		
		// Store them in a temp file
		file_put_contents("upgrade_install.php.temp", var_export($bans, true), FILE_TEXT)
			or die("Could not open upgrade_install.php.temp for writing.");
				
		// Convert to a new type
		$bans_new = array(); // This will hold the converted ones.
		$octets = array(); // Used to hold IP octets
		$single_ban = array(); // Used to hold a converted ban
		
		foreach( $bans as $old_ban )
		{
			// Convert the IP (in long integer format) from the old ban,
			// and segment it into the new octet fields
			$octets = explode(".", long2ip($old_ban['ip']), 4);
			$single_ban['ip_octet1'] = intval($octets[0]);
			$single_ban['ip_octet2'] = intval($octets[1]);
			$single_ban['ip_octet3'] = intval($octets[2]);
			
			// If subnet in the old ban is true, set the 
			// new ban's 4th octet to be the wildcard value of -1,
			// otherwise proceed as normal
			if( $old_ban['subnet'] != 0 )
			{
				$single_ban['ip_octet4'] = -1;
			}
			else
			{
				$single_ban['ip_octet4'] = intval($octets[3]);
			}
			
			// Everything else is a straight copyover
			$single_ban['publicreason'] = $old_ban['publicreason'];
			$single_ban['privatereason'] = $old_ban['privatereason'];
			$single_ban['adminreason'] = $old_ban['adminreason'];
			$single_ban['postdata'] = $old_ban['postdata'];
			$single_ban['duration'] = $old_ban['duration'];
			$single_ban['bantime'] = $old_ban['bantime'];
			$single_ban['bannedby'] = $old_ban['bannedby'];
			
			$bans_new[] = $single_ban; // Add it into the array
		}
		
		$bans = null; // Clean up
			
		// Drop old bans table
		$result = $dbi->myquery("DROP TABLE `".THbans_table."`");
		
		if($result === null)
		{
			die "DROP Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
		}
		
		// Insert new bans table
		$query = "CREATE TABLE `".THbans_table."` 
		( 
		`id` int unsigned NOT NULL auto_increment, 
		`ip_octet1` int NOT NULL, 
		`ip_octet2` int NOT NULL, 
		`ip_octet3` int NOT NULL, 
		`ip_octet4` int NOT NULL, 
		`publicreason` text  NOT NULL, 
		`privatereason` text  NOT NULL, 
		`adminreason` text  NOT NULL, 
		`postdata` longtext  NOT NULL, 
		`duration` int(11) NOT NULL default '-1', 
		`bantime` int(11) unsigned NOT NULL, 
		`bannedby` varchar(100)  NOT NULL, 
		PRIMARY KEY  (`id`) 
		) ENGINE=MyISAM character set utf8 collate utf8_unicode_ci;";
		
		$result = $dbi->myquery($query);
		
		if($result === null)
		{
			die "CREATE Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
		}
		
		// Insert converted bans
		$successful = 1; // set to 0 when one of these insert queries fails
			
		foreach( $bans_new as $insert )
		{
			$banquery = "insert into `".THbans_table."` 
			set ip_octet1=" . $single_ban['ip_octet1'] . ",
			ip_octet2=" . $single_ban['ip_octet2'] . ",
			ip_octet3=" . $single_ban['ip_octet3'] . ",
			ip_octet4=" . $single_ban['ip_octet4'] . ",
			privatereason='" . $dbi->clean($insert['privatereason']) . "', 
			publicreason='" . $dbi->clean($insert['publicreason']) . "', 
			adminreason='" . $dbi->clean($insert['adminreason']) . "', 
			postdata='" . $dbi->clean($insert['postdata']) . "', 
			duration='" . $insert['duration'] . "', 
			bantime=" . $insert['bantime'] . ", 
			bannedby='" . $insert['bannedby'] . "'";
		
			$result = $dbi->myquery($banquery);
			
			if($result === null)
			{
				printf("Insert Error for %d.%d.%d.%s: #%d: %s<br>\n",
				$single_ban['ip_octet1'],
				$single_ban['ip_octet2'],
				$single_ban['ip_octet3'],
				(($single_ban['ip_octet4'] == -1) ? "*" : $single_ban['ip_octet4']),
				mysql_errno($dbi->cxn),
				mysql_error($dbi->cxn));

				$successful = 0; // One bad insert ruins the lot.
			}	
		}
		
		// Did it work?
		if( $successful == 1 )
		{
			echo "Success!";
		}
	}
	
// Only output the full HTML page if we're not trying to do something with AJAX.
if($_GET['type']==NULL) 
{ 
?>
<html><head>
<title>drydock <?php echo THversion; ?> upgrader</title>

<style type="text/css">
body {
background-image:url('static/watermark.png');
background-repeat: no-repeat;
background-attachment: fixed;
background-position: bottom right; 
}
p.centertext {
    margin-left: auto;
    margin-right: auto;
    width: 40em
}
.logo { clear:both; text-align:center; font-size:2em; font-weight: bold; color:#FF6600; }
#main { margin-right:154px; }
.box { padding-left:10px; margin-bottom:10px; border-style:none; border-color:black; border-width:1px; }
.pgtitle { text-decoration:none; color:#2266AA; font-family:sans-serif; font-size:x-large; border-width:0px 0px 2px 0px; border-color:#FF6600; border-style:solid; margin-right:10px; margin-top:5px; }
</style>
	<script type="text/javascript">
		function RequestUpgrade(type) 
		{ 
			var oXMLHttpRequest = new XMLHttpRequest; 
			oXMLHttpRequest.open("GET", "upgrade_install.php?type="$type, true); 
			oXMLHttpRequest.onreadystatechange = function() 
			{ 
				if (this.readyState == XMLHttpRequest.DONE) 
				{
					// Change the pane corresponding with this message.
					document.getElementById("action_"+type).style.display = "block"; // unhide
					document.getElementById("action_"+type).innerHTML = "Server response:<br>"+this.responseText;
					
					// Change the link for this request either to be hidden or back to a valid link
					if( this.responseText == "Success!" )
					{
						document.getElementById("link_"+type).style.display = "hidden";
					}
					else
					{
						document.getElementById("link_"+type).innerHTML 
						= "<a href=\"#\" onclick=\"javascript:RequestUpgrade('"+type+"');\">Perform upgrade</a>";
					}
				} 
			} 
			
			document.getElementById("link_"+type).innerHTML = "Upgrading...";
			document.getElementById("action_"+type).style.display = "hidden"; // hide
			document.getElementById("action_"+type).innerHTML = ""; // clear
			oXMLHttpRequest.send(null); 
		}

	</script>
</head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Drydock Upgrade Script
        </div>
	<br>
This script will attempt to perform the necessary database changes
<hr>
<!-- ACTION 1: Add password field to replies/threads tables -->
This upgrade will attempt to add a password field to the replies and threads tables for user-initiated
post deletion.
<div id="link_1"><a href="#" onclick="javascript:RequestUpgrade('1');">Perform upgrade</a></div>
<div id="action_1" style="display: hidden;"></div>
<hr>
<!-- ACTION 2: Add new tables - ban history and report tables -->
This upgrade will attempt to add the ban history and report tables to the database.  Config.php will
be edited as a result.
<div id="link_2"><a href="#" onclick="javascript:RequestUpgrade('2');">Perform upgrade</a></div>
<div id="action_2" style="display: hidden;"></div>
<hr>
<!-- ACTION 3: Upgrade bans table -->
This upgrade will attempt to upgrade the bans table to a more flexible version.   In the process,
the file "upgrade_install.php.temp" will be created as part of the intermediate steps.  Please
ensure that the script will be able to do this, or this step will fail.
<div id="link_3"><a href="#" onclick="javascript:RequestUpgrade('3');">Perform upgrade</a></div>
<div id="action_3" style="display: hidden;"></div>
<hr>

	</div>
</div>
<?php 
} // end of if($_GET['type']==NULL) 

// Now handle the AJAX request types
// Type 1 - Add password field to replies/threads tables
else if ( intval($_GET['type']) == 1 )
{
	add_post_passwords();
}
// Type 2 - Add ban history and report tables.
else if ( intval($_GET['type']) == 2 )
{
	add_new_tables();
}
// Type 3 - Upgrade bans table
else if ( intval($_GET['type']) == 3 )
{
	upgrade_bans_table();
}
?>