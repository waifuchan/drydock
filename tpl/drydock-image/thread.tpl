{* 
	DRYDOCK THREAD TEMPLATE					last update: 2007.08.28
	
	Provides the view for individual threads and allows replies to threads.
	
	Last updated by:		ordog

*}
{include file=heady.tpl comingfrom=$comingfrom}
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{* include_php file="linkbar.php" *} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
{* no pages for thread view *}
<br clear="all" />
{it->head assign="thread"}{* Workaround *}
{it->getreplies assign="posts"}
{include file=whereami.tpl comingfrom=$comingfrom}
[<a href="{$THurl}{if !$THuserewrite}drydock.php?b={/if}{$binfo.folder}">Return</a>]
<div class="theader">{if ($thread.lawk or $binfo.rlock) and $mod_thisboard !="1" and $mod_global !="1" and $mod_admin !="1"}(Thread is locked, no more posts allowed){else}Posting mode: Reply{/if}</div>
{if $boardmode ==0}{include file=postblock.tpl comingfrom=$comingfrom}
{else}{include file=postblock-img.tpl comingfrom=$comingfrom}
{/if}
<hr />
{literal}
<script type="text/javascript">
	<!--
		var n=readCookie("{/literal}{$THcookieid}{literal}-name");
		var t=readCookie("{/literal}{$THcookieid}{literal}-tpass");
		var d=readCookie("{/literal}{$THcookieid}{literal}-re-goto");
		var l=readCookie("{/literal}{$THcookieid}{literal}-link");
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
{*		as of yet, i don't think we need to split the viewblock for threads,
		but since we only have 2 templates right now, who knows about the future *}
{include file=viewblock.tpl comingfrom=$comingfrom}
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
{* include_php file="linkbar.php" *} {* tyam - gives us quicklinks - take the asterisks out if you want them*}
{include file=bottombar.tpl}
</body>
</html>
