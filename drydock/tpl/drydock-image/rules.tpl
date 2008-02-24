{* tyam - rules page, using per-board code *}
{it->binfo assign=binfo}
{$binfo.rules}<br />
{if $binfo.tlock}Only moderators and administrators are allowed to create new threads.<br />{/if}
{if $binfo.tpix!=0 || $binfo.rpix!=0}
	{if $binfo.tpix==2}New threads <b>must</b> include at least one uploaded image.<br />{/if}
	{if $binfo.allowedformats >  0}Max image size {$binfo.maxfilesize} bytes.<br />{/if}
	{if $binfo.allowedformats >  0}Allowed image formats:{/if}
	{if $binfo.allowedformats &  1}JPG {/if}
	{if $binfo.allowedformats &  2}GIF {/if}
	{if $binfo.allowedformats &  4}PNG {/if}
	{if $binfo.allowedformats &  8}SVG {/if}
	{if $binfo.allowedformats & 16}SWF {/if}
{else}Image posting is currently disabled.{/if} {* can you even post images? *}
{if $binfo.allowvids}<br>Video tags are enabled for this board. {/if}
