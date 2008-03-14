{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Ban Settings</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Ban Settings
        </div>
	<br />
        <div class="sslarge">
{if !$banselect}
    {if $bans==null}
            There are currently no banned IPs.
    {else}
            <form method="post" enctype="multipart/form-data" action="admin.php?t=ux">
                <div>
                    <table>
                        <tr>
                            <td>
                                Unban?
                            </td>
                            <td>
                                Banned IP
                            </td>
                            <td>
                                Private<br>Reason
                            </td>
							<td>
                                Duration
                            </td>
                            <td>
                                Ban set
                            </td>
                            <td>
                                Banned by
                            </td>
                       </tr>
    {foreach from=$bans item=ban}
                        <tr>
                            <td>
                                <input type="checkbox" name="del{$ban.longip}" />
                            </td>
                            <td>
                                <a href="{$THurl}admin.php?a=x&banselect={$ban.longip}">{$ban.ip1}.{$ban.ip2}.{$ban.ip3}.{$ban.ip4}</a><!-- ({$ban.longip}) /-->
                            </td>
                            <td>
                                {$ban.privatereason}
                            </td>
                            <td>
								{if $ban.duration=="-1"}Permanent{elseif $ban.duration=="0"}Warning{else}{$ban.duration}{/if}
                            </td>
                            <td>
                                {$ban.bantime|date_format:$THdatetimestring}
                            </td>
                            <td>
                                {$ban.bannedby}
                            </td>
                        </tr>
			
    {/foreach}
                         </table>
                    <input type="submit" value="Unban checked IPs" />
                </div>
            </form>

    {/if}
{else}
	<a href="{$THurl}admin.php?a=x">back to ban list</a>
	            <form method="post" enctype="multipart/form-data" action="admin.php?t=ux">
                <div>
                    <table>
                        <tr>
                            <tr><td>
                                Unban?</td><td><input type="checkbox" name="del{$longip}" />
                            </td></tr>
                            <tr><td>
                                Banned IP</td><td>{$ip1}.{$ip2}.{$ip3}.{$ip4}<!-- ({$longip}) /-->
                            </td></tr>
                            <tr><td>
                                Public Reason</td><td>{$ban.publicreason}
                            </td></tr>
                            <tr><td>
                                Private Reason</td><td>{$ban.privatereason}
                            </td></tr>
                            <tr><td>
                                Admin Reason</td><td>{$ban.adminreason}
                            </td></tr>
                            <tr><td>
                                Post data</td><td>{$ban.postdata|escape:'html':'UTF-8'}
                            </td></tr>
							<tr><td>
                                Duration</td><td>{if $ban.duration=="-1"}Permanent{else}{$ban.duration}{/if}
                            </td></tr>
                            <tr><td>
                                Ban set</td><td> {$ban.bantime|date_format:$THdatetimestring}
                            </td></tr>
                            <tr><td>
                                Banned by</td><td>{$ban.bannedby}
                            </td></tr>
			                         </table>
                    <input type="submit" value="Unban checked IPs" />
                </div>
            </form>

{/if}
        <div class="pgtitle">
	  Add New Ban
	</div>
	<br />
            <form method="post" enctype="multipart/form-data" action="admin.php?t=ax">
                <div>
                    IP address: <input type="text" name="ip1" size="3" />.<input type="text" name="ip2" size="3" />.<input type="text" name="ip3" size="3" />.<input type="text" name="ip4" size="3" /> <input type="checkbox" name="ipsub" />Ban subnet<br />
                    Reason: <input type="text" name="adminreason" size="20" /> Duration: <input type="text" name="duration" size="3" />hrs<input type="submit" value="Ban" />
                </div>
            </form>
        </div>
    </div>
{include file=admin-foot.tpl}