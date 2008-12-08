<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:           		upgrade_install.php
		Description:	Handles upgrading of a MySQL-based drydock install from 0.3.0 to 0.3.1

		todo:
		fill in skeletons for add_new_tables, upgrade_bans_table()
		figure out what's going on with THdbprefix
		
		Unless otherwise stated, this code is copyright 2008
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once("config.php");
	require_once("dbi/MySQL-dbi.php");

	// Add post passwords. (Type 1)
	function add_post_passwords()
	{
		$dbi = new ThornDBI();
		$query = "ALTER TABLE `".THthreads_table."` ADD `password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";
		$result = $dbi->myquery($query);
		
		// Catch and display errors
		if($result == null)
		{
			echo "Error ".mysql_errno($dbi->cxn) . ": " . mysql_error($dbi->cxn) . "\n";
		}
		else
		{
			// Since that worked, alter the posts table	
			$query = "ALTER TABLE `".THposts_table."` ADD `password` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";
			$result = $dbi->myquery($query);
			
			if($result == null)
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
		// Add new tables (use THdbprefix)
		
		// Rewrite config.php
	}
	
	// Upgrade the bans table. (Type 3)
	function upgrade_bans_table()
	{
		// Get all of the old bans from the DB
		
		// Store them in a temp file?
		
		// Convert to a new type
		
		// Drop old bans table
		
		// Insert new bans table
		
		// Insert converted bans
	}
	
	@header('Content-type: text/html; charset=utf-8');
	if (function_exists("mb_internal_encoding")) {
		//Unicode support :]
		mb_internal_encoding("UTF-8");
		mb_language("uni");
		mb_http_output("UTF-8");
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
This upgrade will attempt to upgrade the bans table to a more flexible version.
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