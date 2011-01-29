<?php

	//THIS IS, IN ALL LIKELYHOOD, A TEMPORARY FILE.  I'm putting it in the SVN, along with calling it, in order to make testing easier
	//tl;dr: Expect this file to disappear soon.


	require_once("config.php");
	require_once("common.php");
	require_once("rebuilds.php");
	
	checkadmin(); //make sure the person trying to access this file is allowed to

/*
	If debug mode is on, odds are we're in a test environment.. admins can reset to "factory" settings.
	Mostly this is for me when I make SVN commits.  Kind of an annoying feature, but I'm tacking it on for now
	Badly programmed.  I will turn this into something better if I decide it needs to stick around.

	Things we do to restore:
		"Remove" all images and image directories (in reality, we move them to unlinked)
		Remove all Smarty files other than the plugins that we included with drydock intially
		Purge all caches
		Restore placeholder.txt

	What this will NOT do:
		Restore install.php	or upgrade.php
		Wipe databases (SQLite is stored in unlinked and will be preserved)
		Purge admin/mod logs (to prevent abuse)
		Remove the config file (again, to prevent abuse, this is simply moved to unlinked)

*/
function testing()
{
	global $ddpath;
	plugins($ddpath."_Smarty/plugins");
	$nuke = nuke();
	$folders = array ( "cache", "compd", "images/profiles", "unlinked" );
	foreach ($folders as $folder)
	{
		restore_placeholder($folder);
	}
}


function plugins($str)
{
	global $ddpath;
	$plugins = array (
		"modifier.THtrunc.php",
		"modifier.quotereply.php",
		"modifier.capcode.php",
		"modifier.tags.php",
		"modifier.filetrunc.php",
		"modifier.vids.php",
		"modifier.filters_new.php",
		"modifier.wordwrap_new.php",
		"modifier.kses.php",
		"modifier.wrapper.php",
		"modifier.markdown.php"
	);
	if(is_file($str))
	{
		$modstr = explode("/", $str);
		foreach ($modstr as $file)
		{
			if (strpos($file,".php"))
			{
				if(!in_array($file, $plugins))
				{
					return unlink($str);
					//echo "unlink $str<br>";
				} else {
					echo $str." is safe<br>";
				}
			}
		}
	} elseif(is_dir($str)) {
		$scan = glob(rtrim($str,'/').'/*');
		foreach($scan as $index=>$path)
		{
			plugins($path);
		}
	}
}



function recursiveDelete($str)
{
	if(is_file($str))
	{
		return unlink($str);
	} elseif(is_dir($str)) {
		$scan = glob(rtrim($str,'/').'/*');
		foreach($scan as $index=>$path)
		{
			recursiveDelete($path);
		}
		return @rmdir($str);
	}
}


function nuke()
{
	global $ddpath;

	//Take them all away
	recursiveDelete($ddpath."compd");
	recursiveDelete($ddpath."cache");
	recursiveDelete($ddpath."images");
	recursiveDelete($ddpath."_Smarty/internals");

	unlink($ddpath."_Smarty/debug.tpl");
	//Put them all back
	mkdir($ddpath."compd");
	mkdir($ddpath."cache");
	mkdir($ddpath."images");
	mkdir($ddpath."images/profiles");
	plugins($ddpath."_Smarty/plugins");
	return 1;
}
function restore_placeholder($folder)
{
	global $ddpath;
	$text = "Sometimes archiving programs and indexing programs don't like empty directories. :[\n";
	$place = fopen($ddpath.$folder."/placeholder.txt", "w");
	$bytes = fwrite($place, $text);
	fclose($place);
	echo $ddpath.$folder.": ".$bytes." bytes written<br>";
	unset($place);
}

?>