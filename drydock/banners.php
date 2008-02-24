<?php
	/*
		Random banner script (based on work by Matt Mullenweg)

		This basically will pull a single random banner file from the folder defined
		("banderole" by default as per konamichan dev team sense of humor) and "push"
		the image to the user, rather than just "pulling" it to the script.
	*/
	require_once("config.php");
	$folder = 'banderole/';
	$exts = 'jpg jpeg png gif';
	$files = array(); $i = -1;
	if ('' == $folder) $folder = './';

	$handle = opendir($folder);
	$exts = explode(' ', $exts);
	while (false !== ($file = readdir($handle)))
	{
		foreach($exts as $ext) // for each extension check the extension
		{
			if (preg_match('/\.'.$ext.'$/i', $file, $test)) // faster than ereg, case insensitive
			{
				$files[] = $file; // it's good
				++$i;
			}
		}
	}
	closedir($handle); // We're not using it anymore
	mt_srand((double)microtime()*1000000); // seed for PHP < 4.2
	$rand = mt_rand(0, $i); // $i was incremented as we went along
	echo "<center><img src=\"".THurl."$folder$files[$rand]\"></center>"; // Voila!
?>
