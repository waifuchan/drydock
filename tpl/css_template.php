<?php
if(isset($_POST['css_submit']))
{
$textcolorlight = $_POST['textcolorlight'];
$textcolordark = $_POST['textcolordark'];	
$backcolor1 = $_POST['backcolor1'];
$backcolor2 = $_POST['backcolor2'];
$bordersandlines1 = $_POST['bordersandlines1'];
$bordersandlines2 = $_POST['bordersandlines2'];							
}
else{
$textcolorlight = "#FFFFFF";
$textcolordark = "#000000";	
$backcolor1 = "#FFFFFF";	
$backcolor2 = "#777777";
$bordersandlines1 = "#FFFFFF";
$bordersandlines2 = "#000000";		
}

$cssfile = '@import "futaba.css";
a{ color: '.$textcolorlight.'; }
a.info{ color:'.$textcolorlight.'; }
a.info:hover{ background-color:'.$backcolor1.'; }
a.info:hover span{
	border:1px solid '.$bordersandlines1.';
	background-color:'.$backcolor2.'; 
	color:'.$textcolorlight.';
}
html, body{ 
	background-color: '.$backcolor1.'; 
	color: '.$textcolordark.'; 
}
hr { 
border-color: '.$bordersandlines1.'; 
background-color: '.$bordersandlines1.';
color: '.$bordersandlines1.';
border: 0;
}
input { background-color: '.$backcolor2.'; }
.pgtitle { border-color: '.$bordersandlines1.'; }
.postername{ color:'.$textcolorlight.'; }
.reflink{ color:'.$textcolordark.'; }
.reply{
	background:'.$backcolor2.';
	border: solid 1px '.$bordersandlines2.';
}
textarea { background-color: '.$backcolor2.'; }
.theader{
	background:'.$backcolor2.';
	border: solid 1px '.$bordersandlines2.';
}
.timedate{
	color:'.$textcolordark.';
	font-size: .8em;
	font-weight: bold;
}
.omittedposts { color: '.$bordersandlines2.'; }

.bottomAdTitle {
	background-color: '.$backcolor1.'; 
	color: '.$textcolordark.'; 
}
#bottomAdOuter { border: 1px solid '.$backcolor1.'; }
}';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>CSS Template Generator</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<style type="text/css">
<?php echo $cssfile;?>
</style>
</head>
<body>
This is a preview of what the currently selected colors will look like.
<br /><br />
Borders and lines color 1:<br>
<hr style="height: 5px; color:<?php echo $bordersandlines1;?>; background-color:<?php echo $bordersandlines1;?>;">
Borders and lines color 2:<br>
<hr style="height: 5px; color:<?php echo $bordersandlines2;?>; background-color:<?php echo $bordersandlines2;?>;">
<table><tbody>
<tr bgcolor="<?php echo $backcolor1;?>"><td>
<font color="<?php echo $textcolordark;?>">Dark text color on background color 1</font>
</td></tr>
<tr bgcolor="<?php echo $backcolor2;?>"><td>
<font color="<?php echo $textcolorlight;?>">Light text color on background color 2</font>
</td><tr>
</tbody></table>
<br/>
<form action="css_template.php" method="post">
<table><tbody>
<tr><td>Light text color:</td>
<td><input type="text" name="textcolorlight" value="<?php echo $textcolorlight;?>" size="7" /></td></tr>
<tr><td>Dark text color:</td>
<td><input type="text" name="textcolordark" value="<?php echo $textcolordark;?>" size="7" /></td></tr>
<tr><td>Background color 1:</td>
<td><input type="text" name="backcolor1" value="<?php echo $backcolor1?>" size="7" /></td></tr>
<tr><td>Background color 2:</td>
<td><input type="text" name="backcolor2" value="<?php echo $backcolor2;?>" size="7" /></td></tr>
<tr><td>Borders and lines color 1:</td> 
<td><input type="text" name="bordersandlines1" value="<?php echo $bordersandlines1;?>" size="7" /></td></tr>
<tr><td>Borders and lines color 2:</td>
<td><input type="text" name="bordersandlines2" value="<?php echo $bordersandlines2;?>" size="7" /></td></tr>
</tbody></table>
<INPUT type="submit" value="Submit"> <INPUT type="reset"><br/><br/>
<!-- I'm not giving it a name so it will never submit (i.e. be "successful"). -->
Resulting CSS file:<br/>
<textarea readonly="on" cols="64" rows="12">
<?php echo $cssfile; ?>
</textarea>
<input type="hidden" name="css_submit" value="1">
</form>
</body>
</html>