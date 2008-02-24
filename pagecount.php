<?php
  	//Hit counter for each page that include()s this file, for every page load
	//Originally for meilk.com, but now we're using it here, so that's cool?
	$self = str_replace(".php", "", $_SERVER['PHP_SELF']);  //buggy, but who cares, it works for index.php!
	$fname = THpath."unlinked/counters_".$self.".txt";
	//open for reading
	$fp = fopen($fname,"r+");
	flock($fp,LOCK_EX);
	$pagecount = fgets($fp,6);
	flock($fp,LOCK_UN);
	//reopen for writing
	$fp = fopen($fname,"w+");
	flock($fp,LOCK_EX);
	$newcount = $pagecount + 1;
	rewind($fp);
	fputs($fp,$newcount);
	flock($fp,LOCK_UN);
	fclose($fp);
?> 
