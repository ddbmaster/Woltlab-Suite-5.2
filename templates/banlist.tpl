{include file='header'}

<section class="section">
    <header class="sectionHeader">
        <h1>{lang}wcf.cs2bannliste.title{/lang}</h1>

        {if $canAdd}
            <p>
                <a href="{link application='wcf' controller='BanAdd'}{/link}" class="button buttonPrimary">
                    {lang}wcf.cs2bannliste.add{/lang}
                </a>
            </p>
        {/if}
    </header>

    {if $error}
        <div class="error">{$error}</div>
    {elseif !$bans|count}
        <p>{lang}wcf.cs2bannliste.empty{/lang}</p>
    {else}
        <table class="table">
            <thead>
                <tr>
                    <th>{lang}wcf.cs2bannliste.name{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.steamid{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.reason{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.admin{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.created{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.duration{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.status{/lang}</th>
                    <th>{lang}wcf.cs2bannliste.actions{/lang}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$bans item=ban}
                <tr>
                    <td>{$ban.player_name}</td>
                    <td><code>{$ban.player_steamid}</code></td>
                    <td>{$ban.reason}</td>
                    <td>{$ban.admin_name}</td>
                    <td>{$ban.created}</td>
                    <td>{$ban.duration}</td>

                   
         {assign var=_status value=$ban.status|trim}
            {if $_status == 'UNBANNED' || $_status == 'Unbanned' || $_status == 'unbanned'}
                <td style="color:#3a3;">{$ban.status}</td>
        {elseif $_status == 'EXPIRED' || $_status == 'Expired' || $_status == 'expired'}
                <td style="color:#ff9900;">{$ban.status}</td>
        {else}
                <td style="color:#c00;">{$ban.status}</td>
            {/if}



                <td>
                        {if $canEdit}
                            <a href="{link application='wcf' controller='BanEdit' id=$ban.id}{/link}" class="button small">
                        {lang}wcf.cs2bannliste.edit{/lang}
                    </a>
                        {/if}

                        {if $canUnban && $ban.status != 'UNBANNED'}
                            <a href="{link application='wcf' controller='BanUnban' id=$ban.id}{/link}" class="button small">
                        {lang}wcf.cs2bannliste.unban{/lang}
                    </a>
                    {/if}

                    {if $canDelete}
                            <a href="{link application='wcf' controller='BanDelete' id=$ban.id}{/link}"
                        class="button small"
                            onclick="return confirm('Diesen Bann wirklich löschen?');">
                        🗑️ {lang}wcf.cs2bannliste.delete{/lang}
                    </a>
                    {/if}
                </td>

                </tr>
            {/foreach}
            </tbody>
        </table>
                {if $bans|count}
                    <div style="text-align:center; margin-top:20px; font-size:0.9em; color:#999;">
                        &copy; {TIME_NOW|date:'Y'} | Designer &amp; Entwickler: <strong>ddbmaster</strong>
                    </div>
                {/if}
            {/if}
</section>

{include file='footer'}
