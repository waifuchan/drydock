{* 
	DRYDOCK BOARD TEMPLATE					last update: 2007.08.28
	
	Provides the view for a board and allows posting of new threads.
	
	Last updated by:		ordog
	
*}
{include file=heady.tpl comingfrom=$comingfrom}
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{* include_php file="linkbar.php" *} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
{if $comingfrom=="board"}{include file=pages.tpl}{/if}
<br clear="all" />
{if $comingfrom == "thread"}{include file=threadhead.tpl comingfrom=$comingfrom}{/if}
{include file=whereami.tpl comingfrom=$comingfrom}
{if $comingfrom=="thread"}[<a href="/{$binfo.folder}/">Return</a>]{/if}
<div class="theader">
{if $comingfrom=="thread"}
{	if ($thread.lawk or $binfo.rlock) and $mod_thisboard !="1" and $mod_global !="1" and $mod_admin !="1"}(Thread is locked, no more posts allowed){else}Posting mode: Reply{/if}</div>
{else}
{	if $binfo.tlock and $mod_thisboard !="1" and $mod_global !="1" and $mod_admin !="1"}(Board is locked, no more posts allowed){else}Posting mode: New thread{/if}</div>
{/if}
{include file=postblock.tpl comingfrom=$comingfrom}
<hr />
{$thread}
{literal}
<script type="text/javascript">
	<!--
		var n=readCookie("{/literal}{$THcookieid}{literal}-name");
		var t=readCookie("{/literal}{$THcookieid}{literal}-tpass");
{/literal}
{if $comingfrom=="thread"}
{	literal}var d=readCookie("{/literal}{$THcookieid}{literal}-re-goto");{/literal}
{else}
{	literal}var d=readCookie("{/literal}{$THcookieid}{literal}-th-goto");{/literal}
{/if}
{literal}

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
{if $comingfrom=="board"}
{include file=boardblock.tpl comingfrom=$comingfrom}
{elseif $comingfrom=="thread"}
{include file=viewblock.tpl comingfrom="thread" thread=$thread posts=$posts}
{/if}
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
