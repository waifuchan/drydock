{if $thread.visible == "1"} {* deleting threads sets this to 0 to hide for review *}
{if $comingfrom=="board"}
<tr><td>
<a name="{$thread.globalid}" href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">
{		if $binfo.forced_anon != "1"} {* begin forced_anon *}
{			if !$thread.title}
		<span class="filetitle">No subject</span>
{			else}
		<span class="filetitle">{$thread.title}</span>
{			/if} {* if no title *}
</a>
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

{*<a href="{$THurl}profiles.php?action=viewprofile&user={$thread.name|escape:'html':'UTF-8'}">*}{*this should only be used on forced-reg boards, so it's untested*}
{				if !$thread.trip}
{					if !$thread.name}
		<span class="postername">{$THdefaultname}</span>
{					else}
		<span class="postername">{$thread.name|escape:'html':'UTF-8'}</span>
{					/if} {* name used? *}
{				else}
		<span class="postername">{$thread.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$thread.trip}</span>
{				/if} {* trip used? *}
{		/if} {* end forced_anon *}
		{*</a>*}</td><td style="text-align: center;"><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}"><span class="timedate">{$thread.time|date_format:$THdatetimestring}</span></a></td>
<td style="text-align: center;"><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">
{	assign value=$thread.rcount-$thread.scount var="count"}
		<span class="omittedposts">{$count+1}</span></a></td>
</tr>
{else}
<div class="damnopera">
	<a name="{$thread.globalid}" />
	<label>
{		if $binfo.forced_anon != "1"} {* begin forced_anon *}
{			if !$thread.title}
		&nbsp;&nbsp;
{			else}
		<span class="filetitle">{$thread.title}</span>
{			/if} {* if no title *}

<input type="checkbox" name="chkpost{$thread.globalid}" value="1"> {* Deletion/reporting checkbox *}

{			if $thread.link}<a href="{$thread.link}">{/if}
{			if $thread.name == "CAPCODE"}
		<span class="postername">{$thread.trip|capcode}</span>
{			/if}{* name = capcode? *}
{			if $thread.name != "CAPCODE"}
{				if !$thread.trip}
{					if !$thread.name}
		<span class="postername">{$THdefaultname}</span>
{					else}
		<span class="postername">{$thread.name|escape:'html':'UTF-8'}</span>
{					/if} {* name used? *}
{				else}
		<span class="postername">{$thread.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$thread.trip}</span>
{				/if} {* trip used? *}
{			/if} {* name not capcode? *}
{		/if} {* end forced_anon *}
		<span class="timedate">{$thread.time|date_format:$THdatetimestring}</span>
{			if $thread.link}</a>{/if}
		<span class="reflink"><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">No.{$thread.globalid}</a></span>
{if $comingfrom=="board"}&nbsp;[<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">Reply</a>]{/if}
<a class="jsmod" style="display:none;">{$binfo.folder},{$thread.globalid}</a>

{if $thread.pin}
		<img src="{$THurl}static/sticky.png" alt="HOLY CRAP STICKY">
{/if}
{if $thread.lawk}
		<img src="{$THurl}static/locked.png" alt="LOCKED">
{/if}
{if $thread.permasage}
		<img src="{$THurl}static/permasage.png" alt="THIS THREAD SUCKS">
{/if}
	</label>
</div>
{if $thread.images}
<table>
	<tr>
{counter name="imgcount" assign="imgcount" start="0"} {* tyam - let's avoid more template breaking *}
{	foreach from=$thread.images item=it}
		<td style="text-align: center;">
			<div class="filesize">File: <a href="{$THurl}images/{$thread.imgidx}/{$it.name}" target="_blank">{$it.name|filetrunc}</a></div>
			<a class="info" href="{$THurl}images/{$thread.imgidx}/{$it.name}" target="_blank">
				<img src="{$THurl}images/{$thread.imgidx}/{$it.tname}" width="{$it.twidth}" height="{$it.theight}" alt="{$it.name}" class="thumb" />
				<span>{$it.fsize} K, {$it.width}x{$it.height}{if $it.anim}, animated{/if}<br />{$it.exif_text}</span>
			</a>
		</td>
{		if ($imgcount mod 4 == 3)}</tr><tr>{/if}{* tyam - let's avoid more template breaking *}
{counter name="imgcount"}
{	/foreach}
	</tr>
</table>
{/if}
<blockquote>
{if not $thread.body}
{	$THdefaulttext}
{else}
{if $comingfrom=="board"}{assign value=$thread.body|THtrunc:2000 var=bodeycheck}
<blockquote>
{assign value=$bodeycheck.text var=bodey}{else}{assign value=$thread.body var=bodey}{/if}
{	if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter!="1"}
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
{if $comingfrom=="board" and $bodeycheck.wastruncated}<em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}#{$post.globalid}" class="ssmed">[more...]</a></em>{/if}
{/if}
</blockquote>
{/if}{*board preview*}
{if $comingfrom=="board"}
{	assign value=$thread.rcount-$thread.scount var="count"}
{	if $count>0}<span class="omittedposts">{$count} {if $count>1}posts{else}post{/if} omitted. Click <a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">full thread</a> to view.</span>{/if}
{	if $thread.rcount > 0}
{		assign value=$thread.reps var="location"}
{	/if}
{else}
{	if $posts}
{		assign value=$posts var="location"}
{	/if}
{/if}
{if $comingfrom=="thread"}
{foreach from=$location item=post}
{if $post.visible == 1} {* deleting sets to 0 *}
<table>
	<tbody>
		<tr>
			<td class="doubledash">&gt;&gt;</td>
			<td class="reply" id="{$post.globalid}">
				<a name="{$post.globalid}" />
				<label>&nbsp;&nbsp;
	
<input type="checkbox" name="chkpost{$post.globalid}" value="1"> {* Deletion/reporting checkbox *}
				
{	if $post.link}<a href="{$post.link}">{/if}
{	if $post.name == "CAPCODE"}
				<span class="postername">{$post.trip|capcode}</span>
{	/if}
{	if $post.name != "CAPCODE"}
{		if !$post.trip}
{			if !$post.name}
				<span class="postername">{$THdefaultname}</span>
{			else}
				<span class="postername">{$post.name|escape:'html':'UTF-8'}</span>
{			/if}
{		else}
				<span class="postername">{$post.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$post.trip}</span>
{		/if}
{	/if}
				<span class="timedate">{$post.time|date_format:$THdatetimestring}</label></span>
{	if $post.link}</a>{/if}
				<span class="reflink">No.{$post.globalid}</span>
<a class="jsmod" style="display:none;">{$binfo.folder},{$post.globalid}</a><br />
{if $post.images}
				<table>
					<tr>
{counter name="imgcount" assign="imgcount" start="0"}
{foreach from=$post.images item=it} {* each image *}
						<td style="text-align: center;">
							<div class="filesize">File: <a href="{$THurl}images/{$post.imgidx}/{$it.name}" target="_blank">{$it.name|filetrunc}</a><br /></div>
							<a class="info" href="{$THurl}images/{$post.imgidx}/{$it.name}" target="_blank">
								<img src="{$THurl}images/{$post.imgidx}/{$it.tname}" width="{$it.twidth}" height="{$it.theight}" alt="{$it.name}" class="thumb" />
								<span>{$it.fsize} K, {$it.width}x{$it.height}{if $it.anim}, animated{/if}<br />{$it.exif_text}</span>
							</a>
						</td>
{if ($imgcount == 3)}</tr><tr>{/if} {* tyam - let's avoid more template breaking *}
{counter name="imgcount"}
{/foreach}
					</tr>
				</table>
{/if}
{* replies *}
				<blockquote>
{if not $post.body}  {* no text,admin configable, THdefaulttext *}
{	$THdefaulttext}
{else}
{if $comingfrom=="board"}{assign value=$post.body|THtrunc:1000 var=bodeycheck}
{assign value=$bodeycheck.text var=bodey}{else}{assign value=$post.body var=bodey}{/if}
{	if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter!="1"}
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
{if $comingfrom=="board" and $bodeycheck.wastruncated} <em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}#{$post.globalid}" title="Read the rest of this post" class="ssmed">[more...]</a></em>{/if}
{/if}
				</blockquote>
			</td>
		</tr>
	</tbody>
</table>
{/if} {* if visible *}
{/foreach}{* reply *}
<br clear="left" />
{/if}{*replies only show on thread view*}
{/if} {* if deleted (visible==1) *}
