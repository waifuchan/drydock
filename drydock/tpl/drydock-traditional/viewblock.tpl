{if $thread.visible == "1"} {* deleting threads sets this to 0 to hide for review *}
<a name="{$thread.globalid}"></a>
<tr><td>
<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}">
{		if $binfo.forced_anon == "0"} {* begin forced_anon *}
{			if !$thread.title}
		No subject
{			else}
		<span class="filetitle">{$thread.title|escape}</span>
{			/if} {* if no title *}
</a>
{if $mod_admin =="1" or $mod_global =="1"}[<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/edit/{else}editpost.php?board={$binfo.folder}&post={/if}{$thread.globalid}">Edit</a>]{/if}
{if $thread.pin}
		<img src="{$THurl}static/sticky.png" alt="HOLY CRAP STICKY">
{/if}
{if $thread.lawk}
		<img src="{$THurl}static/locked.png" alt="LOCKED">
{/if}
{if $thread.permasage}
		<img src="{$THurl}static/permasage.png" alt="THIS THREAD SUCKS">
{/if}
	
		</td><td>
<a href="{$THurl}profiles.php?action=viewprofile&user={$thread.name|escape}">
{				if !$thread.trip}
{					if !$thread.name}
		<span class="postername">{$THdefaultname}</span>
{					else}
		<span class="postername">{$thread.name|escape}</span>
{					/if} {* name used? *}
{				else}
		<span class="postername">{$thread.name|escape|default:""}</span><span class="postertrip">!{$thread.trip}</span>
{				/if} {* trip used? *}
{		/if} {* end forced_anon *}
		</a></td><td align=center><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}"><span class="timedate">{$thread.time|date_format:$THdatetimestring}</span></a></td>
{if $comingfrom=="board"}
<td align=center><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}">
{	assign value=$thread.rcount-$thread.scount var="count"}
		<span class="omittedposts">{$count+1}</span></a></td>
</tr>
{else}
</td>&nbsp;</td></tr>
{/if}

{if $comingfrom=="thread"}</table><blockquote>{/if}
{if not $thread.body}
{	$THdefaulttext}
{else}
{	if $comingfrom=="thread"}
{	assign value=$thread.body var=bodey}
{		if $binfo.allowvids == 1} 
{			$bodey|vids|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
{		else}
{			$bodey|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
{		/if}
{	/if}
</blockquote>
{/if}
{if $comingfrom=="board"}
{	if $thread.rcount > 0}
{		assign value=$thread.reps var="location"}
{	/if}
{else}
{	if $posts}
{		assign value=$posts var="location"}
{	/if}
{/if}
{foreach from=$location item=post}
{if $post.visible == 1} {* deleting sets to 0 *}
<table>
	<tbody>
		<tr>
			<td class="doubledash">&gt;&gt;</td>
			<td class="reply" id="{$post.globalid}">
				<a name="{$post.globalid}"></a>
				<label>&nbsp;&nbsp;
<a href="{$THurl}profiles.php?action=viewprofile&user={$post.name|escape}">
{		if !$post.trip}
{			if !$post.name}
				<span class="postername">{$THdefaultname}</span>
{			else}
				<span class="postername">{$post.name|escape}</span>
{			/if}
{		else}
				<span class="postername">{$post.name|escape|default:""}</span><span class="postertrip">!{$post.trip}</span>
{		/if}
</a>
				<span class="timedate">{$post.time|date_format:$THdatetimestring}</label></span>
{if $mod_admin =="1" or $mod_global =="1"}[<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/edit/{else}editpost.php?board={$binfo.folder}&post={/if}{$thread.globalid}">Edit</a>]{/if}
{* replies *}
				{if $comingfrom=="thread"}<blockquote>{/if}
{if not $post.body}  {* no text,admin configable, THdefaulttext *}
{	$THdefaulttext}
{else}
{if $comingfrom=="board"}{assign value=$post.body|THtrunc:1000 var=bodeycheck}
{assign value=$bodeycheck.text var=bodey}{else}{assign value=$post.body var=bodey}{/if}
{	if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter=="0"}
{		if $binfo.allowvids == 1} 
{			$bodey|vids|nl2br|wrapper|quotereply:"$binfo":"$post":"$thread"}
{		else}
{			$bodey|nl2br|wrapper|quotereply:"$binfo":"$post":"$thread"}
{		/if}
{	else}
{		if $binfo.allowvids == 1} 
{			$bodey|vids|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
{		else}
{			$bodey|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
{		/if}
{	/if}
{if $comingfrom=="board" and $bodeycheck.wastruncated} <em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$thread.globalid}" title="Read the rest of this post" class="ssmed">[more...]</a></em>{/if}
{/if}
				{if $comingfrom=="thread"}</td></tr>{/if}
			</td>
		</tr>
	</tbody>
</table>
{/if} {* if visible *}
{/foreach}{* reply *}
{/if} {* if deleted (visible==1) *}
