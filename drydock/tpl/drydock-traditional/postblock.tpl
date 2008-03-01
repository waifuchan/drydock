<div class="theader">
{if $comingfrom=="board"}
	{if $binfo.tlock}
		(Board is locked, no more posts allowed)
	{else}
		Posting mode: New thread
	{/if}
{elseif $comingfrom=="thread"}
	{if $thread.lawk}
		(Thread is locked, no more posts allowed)
	{else}
		Posting mode: Reply
	{/if}
{else}
	huh
{/if}
</div>

<div class="postarea">
	<table>
		<tbody>

			<form id="postform" action="{$THurl}{if $comingfrom=="thread"}reply{else if $comingfrom == "board"}thread{/if}.php" method="post" enctype="multipart/form-data">
{				if $binfo.forced_anon == "0"} {* begin forced_anon *}
				<tr>
					<td class="postblock">Name</td>
					<td><input type="text" name="nombre" size="40" maxlength="40"/><span style="font-size:x-small;"><input type="checkbox" name="mem" style="font-size:x-small;" value="on" />Remember</span></td>
				</tr>
{				/if} {* end forced_anon *}
{				if $binfo.forced_anon=="0" and $comingfrom=="board"} {* begin forced_anon / boardorthread check*}
				<tr>
					<td class="postblock">Subject</td>
					<td><input type="text" name="subj" size="40" maxlength="40"/></td>
					</tr>
{				/if} {* end forced_anon / boardorthread check *}
				<tr>
					<td class="postblock">Link</td>
					<td><input type="text" name="link" size="40" maxlength="40"/>
		{if $THvc==1}
                    <script type="text/javascript"><!--
                    document.write('<input type="button" value="Post" id="subbtn" onclick="vctest()" />');
                    // /--></script></td>
				</tr>
				<tr>
					<td class="postblock">Verification Code</td>
					<td><img src="{$THurl}captcha.php" alt="Verification Code" /> <input type="text" name="vc" size="6" id="vc" /></td>

		{elseif $THvc==2}
						<input type="submit" value="Submit" id="subbtn" /></td>
				</tr>
				<tr>
					<td class="postblock">LEAVE BLANK IF HUMAN</td>
					<td><input type=text" name="email" />
				</tr>
		{else}
						<input type="submit" value="Submit" id="subbtn" /></td>
				</tr>
		{/if}
				<tr>
					<td class="postblock">Comment</td>
					<td><textarea name="body" cols="48" rows="4" id="cont"></textarea></td>
				</tr>
				<tr>
					<td class="postblock">Then</td>
					<td>
						<select name="todo">
{if $comingfrom=="thread"}<option value="post">Go to my post</option>{else if $comingfrom=="board"}<option value="thread">Go to the new thread</option>{/if}
							<option value="board" selected="selected">Return to board</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="postblock">Rules</td>
					<td><div class="rules">{include file=rules.tpl}</div></td>
				</tr>
{if $comingfrom == "board"}<input type="hidden" name="board" value="{$binfo.id}" />{else if $comingfrom == "thread"}<input type="hidden" name="thread" value="{$thread.id}" />{/if}
{if $blotter}
<tr>
<td class="postblock">Blotter</td>
<td><div class="rules">
{foreach from=$blotter item=blot}
<span class="timedate">{$blot.time|date_format:$THdatetimestring}</span> - {$blot.entry}<br>
{/foreach}
</div></td>
</tr>
{/if}
			</form>
		</tbody>
	</table>
</div>
