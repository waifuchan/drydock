{* tyam - rules page, using per-board code *}
{it->binfo assign=binfo}
{$binfo.rules}<br />
{if $binfo.tlock}Only moderators and administrators are allowed to create new threads.<br />{/if}
{if $binfo.tpix==2}New threads <b>must</b> include at least one uploaded image.<br />{/if}
{if $binfo.allowvids}<br>Video tags are enabled for this board. {/if}
