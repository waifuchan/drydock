<?php $menustring = ""; ?>
<div id="idxmenu">
<div id="idxmenuitem">
<div class="idxmenutitle">
Board Navigation<br>
<a href="http://test.573chan.org/drydock-sqlite/drydock.php?b=test">test</a><br />
<?php if($_SESSION["admin"]){ $menustring.= "Administration Menu<br />"; } elseif($_SESSION["moderator"]) { $menustring.= "Moderator Menu<br />"; } ?>
<?php if($_SESSION["admin"]){ $menustring.= "
<a href=".THurl."admin.php?a=g>Global Settings</a><br />
<a href=".THurl."admin.php?a=b>Board Setup</a><br />
<a href=".THurl."admin.php?a=bl>Blotter Posts</a><br />
<a href=".THurl."admin.php?a=sp>Static Pages</a><br />
<a href=".THurl."admin.php?a=x>Bans</a><br />
<a href=".THurl."admin.php?a=c>Capcodes</a><br />
<a href=".THurl."admin.php?a=w>Filters</a><br />
<a href=".THurl."admin.php?a=p>Profile Admin</a><br />
<a href=".THurl."admin.php?a=hk>Housekeeping</a><br />
<a href=".THurl."admin.php?a=lv>Log Viewer</a><br />
<a href=".THurl."admin.php?a=t>Recent Pics</a><br />
<a href=".THurl."admin.php?a=q>Recent Posts</a><br />
<a href=".THurl."admin.php?a=r>Reports</a><br />
<a href=".THurl."admin.php?a=l>Lookup Tools</a><br /><br />";
} elseif($_SESSION["moderator"]){
$menustring.= "
<a href=".THurl."recentpics.php>Recent Pics</a><br />
<a href=".THurl."recentposts.php>Recent Posts</a><br />
<a href=".THurl."reports.php>Reports</a><br />
<a href=".THurl."lookups.php>Lookup Tools</a><br /><br />";
} 
$menustring.= "<a href=".THurl.">Site Index</a><br />";
 if($_SESSION["username"]) {
$menustring.= "<a href=".THurl."profiles.php?action=logout>Log Out</a> / <a href=".THurl."profiles.php>Profiles</a>";
 } else {
$menustring.= "<a href=".THurl."profiles.php?action=login>Login</a>
 / 
<a href=".THurl."profiles.php?action=register>Register</a>";
}?>
</div></div>
