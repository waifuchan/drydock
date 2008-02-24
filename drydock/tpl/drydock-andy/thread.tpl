{include file=head.tpl}
{it->binfo assign="binfo"}
{it->head assign="thread"}{* Workaround *}
{it->getreplies assign="posts"}
<div id="main">

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


{include file="viewblock.tpl"}


    </div>



    <div class="bline">
        Powered by Thorn {$THversion}<br />
        Andy template &#8212; {if $THcname}Cache file generated {$smarty.now|date_format:"%d %b %y %k:%M:%S"} as {$THcname}{else}This page is not cached{/if}{if $THtpltest}<br />
        TEMPLATE TESTING MODE ON
{/if}
    </div>
</div>
<div id="idxmenu">
    <a href="javascript:toggmenu()">{$THname} &darr;</a>
    <div id="idxmenuitem">
        <div class="idxmenutitle">
            <a href="{$THurl}index.php" title="Go to index page">Board Index</a>
        </div>
{it->getindex assign="idx"}
{foreach from=$idx item=bb}
        <a href="{$THurl}index.php?b={$bb.id}" title="{$bb.name} &#8212; {$bb.about|default:"(no description)"}">{$bb.name}</a><br />
{/foreach}
        <div class="idxmenutitle">
            Posts in this thread
        </div>
        <a href="#t{$thread.id}" title="by {if $thread.name || $thread.trip}{$thread.name} {if $thread.trip}({$thread.trip}){/if}{else}Anonymous{/if}">{$thread.time|date_format:"%d %b %y %k:%M"}</a><br />
{foreach from=$posts item="me"}
        <a href="#p{$me.id}" title="by {if $me.name || $me.trip}{$me.name} {if $me.trip}({$me.trip}){/if}{else}Anonymous{/if}">{$me.time|date_format:"%d %b %y %k:%M"}</a><br />
{/foreach}
    </div>
</div>
{include file=foot.tpl from="thread"}