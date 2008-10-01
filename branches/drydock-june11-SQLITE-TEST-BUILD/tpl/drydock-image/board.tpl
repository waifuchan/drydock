<!-- board -->
{include file=heady.tpl comingfrom=$comingfrom}
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{* include_php file="linkbar.php" *} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
{include file=pages.tpl}
<br clear="all" />
{* no workaround *}
{* we don't get replies here *}
{include file=whereami.tpl comingfrom=$comingfrom}
{* we're at top, no return possible *}
{include file=postblock.tpl comingfrom=$comingfrom}
<hr />
{literal}
<script type="text/javascript">
	<!--
		var n=readCookie("{/literal}{$THcookieid}{literal}-name");
		var t=readCookie("{/literal}{$THcookieid}{literal}-tpass");
		var d=readCookie("{/literal}{$THcookieid}{literal}-th-goto");
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
<!-- threads -->
{it->getsthreads assign="sthreads"}
{foreach from=$sthreads item=thread}
{include file=viewblock.tpl comingfrom=$comingfrom}
{/foreach}{* multiple threads *}
{include file=pages.tpl}
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
