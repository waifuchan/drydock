<?php
//This script was more or less designed to work independently of the rest of Thorn, so that others who wanted to use it for their own projects could do so quite easily.
//
//A much earlier version of this code is used in Ochiba, an image board/gallery script created by h-cube. http://ochiba.x-maru.org

require_once("config.php");
session_cache_expire(10);
session_start();
if (isset($_SESSION['vc'])==false) {
    //Generate new code if the session for the last one is dead
    $_SESSION['vc']=mt_rand(0,99999);
    }
$code=(string)$_SESSION['vc'];
$codepath=THpath."captchas/".$code.".png";
if (THcaptest==false && file_exists($codepath)) {
    //If an image with this code number already exists...
    if (filemtime($codepath)<time()-300) {
        //If it's older than five minutes, delete it and we'll make another one.
        unlink($codepath);
        }
    else {
        //Otherwise, we'll use that one.
        header("Content-type: image/png");
        readfile($codepath);
        die();
        }
    }

//Righty-o, then. Let's get going.
$clen=strlen($code);

$right=mt_rand(-1,4);//Rightmost pixel of first char
for ($i=0; $i<$clen; $i++) {
    $pos[$i]=mt_rand($right, $right+4);//Set positions of other chars
    $fonts[$i]=mt_rand(4,5);//Random font size
    $right=$pos[$i]+imagefontwidth($fonts[$i])+2;//Next char can start here
    }
$theimg=imagecreatetruecolor($right, 16);

if (mt_rand(0,1)==0) {
    //Dark or light color scheme?
    for ($i=0;$i<3;$i++) {
        $back[]=mt_rand(0,63);
        }
    $dark=true;
    }
else {
    for ($i=0;$i<3;$i++) {
        $back[]=mt_rand(191,255);
        }
    $dark=false;
    }

$xback=imagecolorallocate($theimg,$back[0],$back[1],$back[2]);
imagefill($theimg,0,0,$xback);
for ($i=0; $i<$clen; $i++) {
    //Define colors for each char
    if ($dark==true) {
        for ($j=0;$j<3;$j++) {
            $k[$j]=mt_rand(128,255);
            }
        }
    else {
        for ($j=0;$j<3;$j++) {
            $k[$j]=mt_rand(0,127);
            }
        }
    $clr=imagecolorallocate($theimg,$k[0],$k[1],$k[2]);//Allocate color
    $horiz=mt_rand(-1,16-imagefontheight($fonts[$i]));
    imagestring($theimg, $fonts[$i], $pos[$i], $horiz, $code{$i}, $clr);
    }

for ($rot=mt_rand(-10,10);$rot>-2 && $rot<2;$rot=mt_rand(-10,10)) {
    //Random rotation angle
    }

$theimg=imagerotate($theimg,$rot,$xback);
//Rotating an image with transparency breaks GD or something, so we have to apply transparency after rotation.
imagecolortransparent($theimg,$xback);

////The text is more legible if it's larger, so let's scale it.
$scw=imagesx($theimg);
$sch=imagesy($theimg);
//
//$scw=$txx*2;
//$sch=$txy*2;
//
//$thenewimg=imagecreatetruecolor($scw,$sch);
//
//imagecopyresampled($thenewimg,$theimg,0,0,0,0,$scw,$sch,$txx,$txy);

//The text is more legible if it's larger, so let's scale it.

$thefinalimg=imagecreatetruecolor($scw,$sch);
$xback=imagecolorallocate($thefinalimg,$back[0],$back[1],$back[2]);
imagefill($thefinalimg,0,0,$xback);
//imagecolortransparent($thefinalimg,$xback);

//Some dupes to throw OCR scripts off (hopefully);
imagecopymerge($thefinalimg,$theimg,mt_rand(-6,6),mt_rand(-6,6),0,0,$scw,$sch,40);
imagecopymerge($thefinalimg,$theimg,mt_rand(-2,2),mt_rand(-2,2),0,0,$scw,$sch,60);
imagecopymerge($thefinalimg,$theimg,mt_rand(-4,4),mt_rand(-4,4),0,0,$scw,$sch,20);

$xshift=mt_rand(8,16);
if (mt_rand(0,1)==1) {
    $xshift=-$xshift;
    }

$yshift=mt_rand(16,20);
if (mt_rand(0,1)==1) {
    $yshift=-$yshift;
    }

imagecopymerge($thefinalimg,$theimg,$xshift,$yshift,0,0,$scw,$sch,100);

imagecopymerge($thefinalimg,$theimg,0,0,0,0,$scw,$sch,100);//The real thing!

if (THcaptest==false) {
    imagepng($thefinalimg,$codepath);
    }

// To stop caching...
// http://jp2.php.net/header

// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
 
// HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

// HTTP/1.0
header("Pragma: no-cache");


header("Content-type: image/png");
imagepng($thefinalimg);
//OCR THIS, PAL.
?>