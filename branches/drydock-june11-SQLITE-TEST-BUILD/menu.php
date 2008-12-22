<div id="idxmenu">
<div id="idxmenuitem">
<div class="idxmenutitle">
<?php if($_SESSION["admin"]){ echo "Administration Menu<br />"; } elseif($_SESSION["moderator"]) { echo "Moderator Menu<br />"; } ?>
<?php if($_SESSION["admin"]){ echo "
<a href=".THurl."admin.php?a=g>Global Settings</a><br />
<a href=".THurl."admin.php?a=b>Board Setup</a><br />
<a href=".THurl."admin.php?a=bl>Blotter Posts</a><br />
<a href=".THurl."admin.php?a=x>Bans</a><br />
<a href=".THurl."admin.php?a=c>Capcodes</a><br />
<a href=".THurl."admin.php?a=w>Filters</a><br />
<a href=".THurl."admin.php?a=p>Profile Admin</a><br />
<a href=".THurl."admin.php?a=hk>Housekeeping</a><br />
<a href=".THurl."admin.php?a=t>Recent Pics</a><br />
<a href=".THurl."admin.php?a=q>Recent Posts</a><br />
<a href=".THurl."admin.php?a=r>Reports</a><br /><br />";
} elseif($_SESSION["moderator"]){
echo "
<a href=".THurl."recentpics.php>Recent Pics</a><br />
<a href=".THurl."recentposts.php>Recent Posts</a><br />
<a href=".THurl."reports.php>Reports</a><br /><br />";
} ?>
<a href="http://localhost/drydock/branches/drydock/">Site Index</a><br />
<?php if($_SESSION["username"]) {
echo "<a href=".THurl."profiles.php?action=logout>Log Out</a> / <a href=".THurl."profiles.php>Profiles</a>";
 } else {
echo "<a href=".THurl."profiles.php?action=login>Login</a>
 / 
<a href=".THurl."profiles.php?action=register>Register</a>";
}?>
</div></div>
