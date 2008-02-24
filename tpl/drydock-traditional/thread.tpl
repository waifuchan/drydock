{* 
	KONAMICHAN THREAD TEMPLATE					last update: 2007.01.26
	
	Provides the view for individual threads and allows replies to threads.
	
	Last updated by:		tyam
	
	lol no license
*}
{include file=heady.tpl comingfrom=$comingfrom}
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{include_php file="linkbar.php"} {* tyam - this way we have a list of boards to quicklink to *}
{* no pages for thread view *}
<br clear="all" />
{it->head assign="thread"}{* Workaround *}
{it->getreplies assign="posts"}
{include file=whereami.tpl comingfrom=$comingfrom}
[<a href="{$THurl}{$binfo.folder}/">Return</a>]
<div class="theader">{if ($thread.lawk or $binfo.rlock) and $mod_thisboard !="1" and $mod_global !="1" and $mod_admin !="1"}(Thread is locked, no more posts allowed){else}Posting mode: Reply{/if}</div>
{include file=postblock.tpl comingfrom=$comingfrom}
<hr />
{literal}
<script type="text/javascript">
	<!--
		var n=readCookie("USIJC-name");
		var t=readCookie("USIJC-tpass");
		var d=readCookie("USIJC-re-goto");
		var l=readCookie("USIJC-link");
		if (n!=null)
		{
			document.forms['postform'].elements['nombre'].value=unescape(n).replace(/\+/g," ");
		}
		if (t!=null)
		{
			document.forms['postform'].elements['tpass'].value=unescape(t).replace(/\+/g," ");
		}
		if (d!=null)
		{
			document.forms['postform'].elements['todo'].value=d;
		}
		if (l!=null)
		{
			document.forms['postform'].elements['link'].value=unescape(l).replace(/\+/g," ");
		}
	//-->
</script>
{/literal}
<table width="100%">
<tr width="100%">
<th width=55% align=left>Subject</th><th with=20% align=left>Poster</th><th width=15% align=center>Timestamp</th><th width=10% align=center></th>
</tr>
{*		we don't need to get each thread here so we don't need a 
		for each for it here, but here is a place keeper for numbering *}
{include file=viewblock.tpl comingfrom=$comingfrom}
</table><br clear=all><hr>
{* we're out on this out, so let's clean it
	we don't need the loop for multithread	*}
{literal}
<script type="text/javascript" defer="defer">
	<!--
		function visfile(thisone)
		{
			if (document.getElementById("file"+(thisone+1)))
			{
				document.getElementById("file"+(thisone+1)).style.display="block";
			}
		}
	-->
</script>
{/literal}
{include_php file="linkbar.php"} {* tyam - gives us quicklinks *}
{include file=bottombar.tpl}
</body>
</html>
