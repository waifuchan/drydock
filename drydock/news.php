<?php
	require_once("config.php");
	require_once("common.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="rss.xml" />
<title><?php echo THname;?> &#8212; News Page</title></head>
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />
<body>	
<div id="main">
	<div class="box">
		<div class="pgtitle">
			News Page <a href="<?php echo THurl; ?>rss.xml"><img src="<?php echo THurl; ?>static/rss.png" border="0"></a>
		</div>
		<div>
<?php
	if ((THnewsboard!=0)&&(file_exists("rss2html.php")))  //uh i think this is okay?  incompatible licenses :[
	{ 
		include("rss2html.php");
		 $archivelink = '<a href="'.THurl.getboardname(THnewsboard).'">Full News Archive</a>';  //make our link
		echo '<div align="center" style="font-family:verdana,century;font-size:10px">- '.$archivelink." -<br></div>\n";
	} else {
		echo "<br />HARDER BETTER FASTER STRONGER KANYE KANYE KANYE KANYE<br /><br /><br />i need you right now :[";
	}
?>
		</div>
	</div>
</div>
<?php include("menu.php"); ?>
</div>
<div align="center">- <a href="http://thorn.pichan.org/" target="blank">Thorn</a> +
<a href="http://wakaba.c3.cx/s/web/wakaba_kareha.html" target="_blank">Wakaba</a> +
<a href="http://code.573chan.org/" target="_blank">drydock <?php echo THversion ?></a> -</div>
