{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Database Settings</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Database Settings
        </div>
        <div class="sslarge"><a href="{$THurl}admin.php?a=g">General Settings</a> &#8212; <strong>Database Settings</strong> &#8212; <a href="{$THurl}admin.php?a=b">Board Settings</a> &#8212; <a href="{$THurl}admin.php?a=x">Ban Settings</a></div>
        <div class="sslarge">
            <form method="post" enctype="multipart/form-data" action="admin.php?t=d">
                <div>
                    Database interface:
                    <select name="THdbtype">
    {foreach from=$dbis item=dbi}
                        <option value="{$dbi}"{if $dbi==$THdbtype} selected="selected"{/if}>{$dbi}</option>
    {/foreach}
                    </select>
                    <div class="ssmed">(Depending on which database interface you select, some of the below settings may be unnecessary. Consult the documentation.)</div><br />
                    <br />
                    Database server: <input type="text" name="THdbserver" size="12" value="{$THdbserver}" /><br />
                    Database username: <input type="password" name="THdbuser" size="12" value="{$THdbuser}" /><br />
                    Database password: <input type="password" name="THdbpass" size="12" value="{$THdbpass}" /><br />
                    Database name: <input type="text" name="THdbbase" size="12" value="{$THdbbase}" /><br />
                    <input type="checkbox" name="dbinit" />Initialize database &#8212; Check this to attempt to set up a new Thorn database using these settings. Be warned that this will <em>delete</em> a previous Thorn database if one is already configured with these settings. You want to check this box if this is your first installation of Thorn.
                    <div class="ssmed">Make sure you know what you're doing before you submit any changes!</div>
                    <input type="submit" value="Submit" /><br />
                    {* WARNING: Thorn logs all submissions of this form, successful or otherwise, including IP addresses. h4xx0rz wannabes, beware. (Not really. At least, not yet.) *}
                </div>
            </form>
        </div>
    </div>
{include file=admin-foot.tpl}