{if $mod_thisboard =="1" or $mod_global =="1" or $mod_admin =="1"}
{literal}
<script type="text/javascript">
	// oh god I hate you javascript this took way too long to debug
	var modtags=document.getElementsByName("jsmod");
	var i=0;
	for (i=0;i<modtags.length;i++)
	{
		var x = String(modtags[i].innerHTML);
		var splitter = x.lastIndexOf(","); // where is the last comma?
		var board = x.substr(0,splitter); // everything before the comma is the board ID
		var post = x.substr(splitter+1); // everything after the last comma is the post ID
		
		modtags[i].innerHTML = "[Edit]";
		modtags[i].href = "{/literal}{$THurl}{literal}editpost.php?post=" + post + "&board=" + board;
		modtags[i].style.display = "inline";
	}
</script>
{/literal}
{/if}