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
<br/>{if $binfo.tlock}Only moderators and administrators are allowed to create new threads.<br />{/if}</br>
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
		var p=readCookie("{/literal}{$THcookieid}{literal}-password");
		
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
		
		if (p!= null)
		{
			document.forms['postform'].elements['password'].value=unescape(p).replace(/\+/g," ");
			document.forms['delform'].elements['password'].value=unescape(p).replace(/\+/g," ");
		}
		else
		{
			var chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			var pass='';

			for(var i=0;i<8;i++)
			{
				var rnd=Math.floor(Math.random()*chars.length);
				pass+=chars.substring(rnd,rnd+1);
			}

			document.forms['postform'].elements['password'].value=pass;
			document.forms['delform'].elements['password'].value=pass;			
		}
	//-->
</script>
{/literal}
</div>

{* Beginning of form for post deletion/reporting/whatever else we might want in the future *}
<form target="_blank" action="misc.php" method="POST" id="delform">
<input type="hidden" name="board" value="{$binfo.folder}" />

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

{* End of form for post deletion/reporting/whatever else *}
<div style="text-align:right">
Password: <input type="password" name="password" value=""><br>
<input type="submit" name="report" value="Report"><input type="submit" name="delete" value="Delete">
</div>
</form>