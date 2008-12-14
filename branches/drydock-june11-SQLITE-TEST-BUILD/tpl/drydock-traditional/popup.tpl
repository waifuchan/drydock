{* Due to Smarty annoyance, must include entire header here, without the menu part. *}
{* This is just a thing for small little popups and thus with heavily stripped-down HTML
   which can have two things set - $title and $text.  Caching is also disabled for it. *}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
{if $title == ""}
<title>{$THname}</title>
{else}
<title>$title</title>
{/if}
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

<style type="text/css">{literal}
body { margin: 0; padding: 8px; margin-bottom: auto; }
{/literal}
</style>

<link rel="stylesheet" type="text/css" href="{$THtplurl}Futaba.css" title="Futaba-ish Stylesheet" />
</head>

{$text}

</body>
</html>