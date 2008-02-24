<?php
	//Albright threw this together apparently for thorn2?
	//http://wakaba.c3.cx/soc/kareha.pl/1100499906/230
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>KChan Trip Tester (for tripfags)</title>
	</head>
	<body>
		<?php
			@header('Content-type: text/html; charset=utf-8');
			if (function_exists("mb_internal_encoding"))
			{
				mb_internal_encoding("UTF-8");
				mb_language("uni");
				mb_http_output("UTF-8");
			}
			error_reporting(E_ALL^E_NOTICE);
			if($_POST['nombre']!=NULL)
			{
				$name=explode("#",$_POST['nombre']);
				$trip=array_pop($name);
				$name=array_pop($name);
				$trip=mb_convert_encoding($trip,"SJIS");
				$salt=substr($trip."H.",1,2);
				$salt=ereg_replace("[^\.-z]",".",$salt);
				$salt=strtr($salt,":;<=>?@[\\]^_`","ABCDEFGabcdef"); 
				$trip=substr(crypt($trip,$salt),-10)."";
				$trip=mb_convert_encoding($trip,"UTF-8");
				echo("<strong>".$name."</strong>!".$trip."\n");
			}
		?>
		<form method="post" enctype="multipart/form-data" action="triptest.php">
			<input type="text" size="20" name="nombre" /><input type="submit" />
		</form>
	</body>
</html>
