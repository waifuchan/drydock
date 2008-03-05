{include file=head.tpl comingfrom=$comingfrom}
<body>
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{* include_php file="linkbar.php" *} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
<br clear="all" />
		<center>{include_php file="banners.php"}
		<div class="pgtitle">
			{$binfo.name}<br \>
			<font color="red" size="-2">Last post: {if $binfo.lasttime>0}{$binfo.lasttime|date_format:$THdatetimestring}{else}unavailable{/if}</font><br />
		</div>
		</center><br />
{if $binfo.about}{$binfo.about}<br />{/if}
{include file=rules.tpl}
{if $binfo.tlock}Only moderators and administrators are allowed to create new threads.<br />{/if}</br>
<a name="tlist"></a>
<hr />
    <div class="medtitle">
{it->getallthreads assign="bthreads"}
{counter name="upto" assign="upto" start="0"}
{foreach from=$bthreads item=th}
{counter name="upto"}
{*<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$th.globalid}">*}
<a href="#{$th.globalid}">{$th.globalid}: {if $th.title}{$th.title|escape:'html':'UTF-8'}{else}No Subject{/if} ({$thread.rcount+1})</a> &nbsp;&nbsp;
{foreachelse}
(no threads)
{/foreach}
	</div><br />
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
</div>
{it->getsthreads assign="sthreads"}
{foreach from=$sthreads item=thread}
</div><div class="box">
{include file="viewblock.tpl" comingfrom=$comingfrom}
{foreachelse}
<div class="medtitle">(No threads on this board)</div></div>
{/foreach}{*For each thread*}
</div>
<div class="box">
{include file=postblock.tpl comingfrom=$comingfrom}
 </div>
</div>
