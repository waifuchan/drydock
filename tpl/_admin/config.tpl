<?php
$configver=4;
define("THpath","{$THpath}");
define("THurl","{$THurl}");

define("THdbtype","{$THdbtype}");
define("THdbserver","{$THdbserver}");
define("THdbuser","{$THdbuser}");
define("THdbpass","{$THdbpass}");
define("THdbbase","{$THdbbase}");
define("THbans_table", "{$THbans_table}");
define("THblotter_table", "{$THblotter_table}");
define("THboards_table", "{$THboards_table}");
define("THcapcodes_table", "{$THcapcodes_table}");
define("THextrainfo_table", "{$THextrainfo_table}");
define("THfilters_table", "{$THfilters_table}");
define("THimages_table", "{$THimages_table}");
define("THreplies_table", "{$THreplies_table}");
define("THspamlist_table", "{$THspamlist_table}");
define("THthreads_table", "{$THthreads_table}");
define("THusers_table", "{$THusers_table}");

define("THname","{$THname}");
define("THtplset","{$THtplset}");
define("THcookieid","{$THcookieid}");
define("THcaptest",{if $THcaptest==true}true{else}false{/if});
//define("THtpltest",{if $THtpltest==true}true{else}false{/if});
define("THtpltest",true);

define("THpixperpost",{$THpixperpost});
define("THthumbheight",{$THthumbheight});
define("THthumbwidth",{$THthumbwidth});
define("THjpegqual",{$THjpegqual});
define("THdupecheck",{if $THdupecheck==true}true{else}false{/if});

define("THtimeoffset",{$THtimeoffset});
define("THvc",{if $THvc==true}true{else}false{/if});

define("THnewsboard",{$THnewsboard});
define("THmodboard",{$THmodboard});
define("THdefaulttext","{$THdefaulttext}");
define("THdefaultname","{$THdefaultname}");
define("THdatetimestring","{$THdatetimestring}");

//profile stuff
define("THprofile_adminlevel", {$THprofile_adminlevel});
define("THprofile_userlevel", {$THprofile_userlevel});
define("THprofile_emailname", "{$THprofile_emailname}");
define("THprofile_emailaddr", "{$THprofile_emailaddr}");
define("THprofile_emailwelcome",{if $THprofile_emailwelcome==true}true{else}false{/if});
define("THprofile_cookietime", {$THprofile_cookietime});
define("THprofile_cookiepath", "{$THprofile_cookiepath}");
define("THprofile_lcnames", {if $THprofile_lcnames==true}true{else}false{/if});
define("THprofile_maxpicsize", {$THprofile_maxpicsize});
define("THprofile_regpolicy", {$THprofile_regpolicy});
define("THprofile_viewuserpolicy", {$THprofile_viewuserpolicy});
?>
