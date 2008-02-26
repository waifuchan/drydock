{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212;Housekeeping</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
Housekeeping Functions
        </div>
	<br>
        <div class="sslarge">
			The following buttons can be used to force updates to certain script built pages.  They can be called manually if there is some error in the build process or if changes are made outisde of the scope of the update scripts (such as editing a post in your news board).
        <div class="pgtitle">
	  Rebuild Functions
	</div>
	<br />			
            <form method="post" enctype="multipart/form-data" action="admin.php?a=hkc">
				{if THnewsboard!=0}<input type="submit" name="rs" style="width:20em;" value="Rebuild news page RSS feed"><br />{/if}
				<input type="submit" name="ht" style="width:20em;" value="Rebuild .htaccess"><br />
				<input type="submit" name="sl" style="width:20em;" value="Rebuild side links"><br />
				<input type="submit" name="lb" style="width:20em;" value="Rebuild top/bottom link bars"><br />
				<input type="submit" name="sp" style="width:20em;" value="Rebuild spam list"><br />
				<input type="submit" name="fl" style="width:20em;" value="Rebuild word filters"><br />
				<input type="submit" name="cp" style="width:20em;" value="Rebuild capcodes"><br />
				<input type="submit" name="all" style="width:20em;" value="REBUILD ALL"><br />
            </form>

{php}
if(file_exists("dump.php"))
{
echo '
        <div class="pgtitle">
	  SQL Dumps
	</div>
	<br />
			SQL dumps can be generated with the following links.  Please be careful with these since they coould be used to gain access to your administration area or other registered profiles if posted to a public forum.  If you are using these dumps for support on the drydock discussion board, please edit out any important information (password hashes, contact info, etc) before posting publicly.<br/>
			<b>To disable this function, delete the file dump.php</b>  These may take a while to process, depending on how active your site is.<br />
			<a href="dump.php?table=bans">Bans table</a><br />
			<a href="dump.php?table=blotter">Blotter table</a><br />
			<a href="dump.php?table=boards">Boards table</a><br />
			<a href="dump.php?table=capcodes">Capcodes table</a><br />
			<a href="dump.php?table=extra">Extra info table (metadata)</a><br />
			<a href="dump.php?table=filters">Wordfilters table</a><br />
			<a href="dump.php?table=images">Images table</a><br />
			<a href="dump.php?table=replies">Replies table</a><br />
			<a href="dump.php?table=threads">Threads table</a><br />
			<a href="dump.php?table=users">Users table</a><br />
			<a href="dump.php?table=all">All tables</a><br />
';
}
{/php}
        </div>
    </div>
{include file=admin-foot.tpl}
