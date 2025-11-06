{include file='header'}

<section class="section">
    <header class="sectionHeader">
        <h1>{lang}wcf.cs2bannliste.unban{/lang}</h1>
    </header>

    {if $error}
        <div class="error">{$error}</div>
    {elseif $success}
        <div class="success">{$success}</div>
    {else}
        <p>{lang}wcf.cs2bannliste.confirmUnban{/lang}</p>
        <form method="post" class="jsForm">
            <button type="submit" class="button buttonPrimary">{lang}wcf.cs2bannliste.unban{/lang}</button>
            <a href="{link application='wcf' controller='Banlist'}{/link}" class="button">{lang}wcf.cs2bannliste.back{/lang}</a>
        </form>
    {/if}
</section>

{include file='footer'}
