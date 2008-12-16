{* Due to Smarty annoyance, must include entire header here, without the menu part. *}
{* This is just a thing for small little popups and thus with heavily stripped-down HTML
   which can have three things set - $title, $text, and $timeout.  It will close after
   $timeout milliseconds if $timeout is > 0.  Caching is also disabled for it. *}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
{if $title == ""}
<title>{$THname}</title>
{else}
<title>$title</title>
{/if}
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

{if $timeout > 0}
<script language="javascript">
<!--
setTimeout("self.close();",{$timeout})
//-->
</script> 
{/if}

<style type="text/css">{literal}
body { margin: 0; padding: 8px; margin-bottom: auto; }
{/literal}
</style>

<link rel="stylesheet" type="text/css" href="{$THtplurl}Futaba.css" title="Futaba-ish Stylesheet" />
</head>

{$text}

{if $timeout > 0}
<br>This window will close in {$timeout/1000} seconds.
{/if}

</body>
</html>