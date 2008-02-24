<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>{$THname}</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<style type="text/css">{literal}
body { margin: 0; padding: 8px; margin-bottom: auto; }
blockquote blockquote { margin-left: 0em }
form { margin-bottom: 0px }
.postarea { text-align: center }
.postarea table { margin: 0px auto; text-align: left }
.thumb { border: none; float: left; margin: 2px 20px }
.nothumb { float: left; background: #eee; border: 2px dashed #aaa; text-align: center; margin: 2px 20px; padding: 1em 0.5em 1em 0.5em; }
.reply blockquote, blockquote :last-child { margin-bottom: 0em }
.reflink a { color: inherit; text-decoration: none }
.reply .filesize { margin-left: 20px }
.userdelete { float: right; text-align: center; white-space: nowrap }
.replypage .replylink { display: none }{/literal}
</style>

<link rel="stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />

<script type="text/javascript">var style_cookie="Futaba-ish Stylesheet";</script>
<script type="text/javascript" src="{$THtplurl}js.js"></script>
</head>
{if $comingfrom=="thread"}
<body class="replypage">
{else}
<body>
{/if}
<b>Board Navigation</b><br/>
<br/>
{it->getindex full=true assign="idx"}
{foreach from=$idx item=board}
        <a href="{$THurl}{$board.folder}" target="main" title="{$board.about}">{$board.name}</a>
        <blockquote>{$board.about|markdown|default:""}</blockquote>
{/foreach}
</body></html>
