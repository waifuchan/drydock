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
        <div id="showit" class="sslarge">
			<form method="post" enctype="multipart/form-data" action="{$THurl}{if $comingfrom=="thread"}reply{else if $comingfrom == "board"}thread{/if}.php" id="postform">
                <div>
                    Name: <input type="text" name="nombre" size="20" /> Link: <input type="text" name="link" size="20" /><br />
                    {if $comingfrom == "board"}Subject: <input type="text" name="subj" size="45" /><br />{/if}
                    <textarea name="body" cols="51" rows="8" id="cont"></textarea><br />
                    After submission, go to the:
                    <select name="todo">
                        <option value="board">Return to board</option>
                        <option value="thread">Go to the new thread</option>
                    </select>
                    {if $THvc==1}
                    <br />Verification Code: <img src="{$THurl}captcha.php" alt="Verification Code" /> <input type="text" name="vc" size="6" id="vc" />
                    <script type="text/javascript"><!--
                    document.write('<input type="button" value="Post" id="subbtn" onclick="vctest()" />');
                    // /--></script>
                    {elseif $THvc==2}
					<br />LEAVE BLANK IF HUMAN: <input type=text" name="email" />
                    <input type="submit" value="Post" />
					{else}
                    <input type="submit" value="Post" />
                    {/if}
                    <noscript>
                    <input type="submit" value="Post" />
                    </noscript> 
{if $comingfrom == "board"}<input type="hidden" name="board" value="{$binfo.id}" />
{else if $comingfrom == "thread"}<input type="hidden" name="thread" value="{$thread.id}" />{/if}
                </div>
            </form>
        </div>
    <div class="ssmed">
        <span class="name">
<a href="{$THurl}{if $THuserewrite}{$binfo.folder}{else}drydock.php?b={$binfo.folder}{/if}#tlist">Thread List</a>
