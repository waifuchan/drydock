<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			news.php
		Description:	Holds the front page - needs to have something done with it at some point because it isnt very good



				We're currently suggesting users install the FREE rss2html script available from http://www.feedforall.com

			After putting the rss2html.php and the XML parser file, edit rss2html and change these variables
				$XMLfilename = "rss.xml";
				$TEMPLATEfilename = "news-template.php";
			These two are optional but suggested.
				$ShortDateFormat = "Y.m.d";
				$ShortTimeFormat = "H:i";

			If you have specified a news board in drydock config, rebuilding the RSS feed from housekeeping should make this work
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	require_once("config.php");
	require_once("common.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<?php if(THnewsboard != 0) echo '<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.xml" />'; ?>
<title><?php echo THname;?> &#8212; News Page</title></head>
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />
<body>	
<div id="main">
	<div class="box">
		<div class="pgtitle">
			News Page <?php if(THnewsboard != 0) echo '<a href="'.THurl.'rss.xml"><img src="'.THurl.'static/rss.png" border="0"></a>'; ?>
		</div>
		<div>
<?php
	if ((THnewsboard!=0)&&(file_exists("rss2html.php")))
	{ 
		include("rss2html.php");
			if (THuserewrite)  //compatibility~~~
			{
				$archivelink = '<a class="info" href="'.THurl;
			} else {
				$archivelink = '<a class="info" href="'.THurl.'drydock.php?b=';
			}
		$db = new ThornDBI();
		 $archivelink .= $db->getboardname(THnewsboard).'">Full News Archive</a>';  //make our link
	} else {
		echo "<br />This site is powered by the drydock image board script.";
	}
?>
		</div>
	</div>
</div>
<?php include("menu.php"); ?>
</div></div>
<?php if($archivelink) {
		echo '<div align="center" style="font-family:verdana,century;font-size:10px;padding-bottom: 10px;">- '.$archivelink." -</div>\n";
}
?>

<div align="center">- <a href="http://thorn.pichan.org/" target="blank">Thorn</a> +
<a href="http://wakaba.c3.cx/s/web/wakaba_kareha.html" target="_blank">Wakaba</a> +
<a href="http://code.573chan.org/" target="_blank">drydock</a> -</div>
