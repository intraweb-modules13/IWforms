{include file="IWforms_user_menu.tpl" func="sended"}
<div class="usercontainer">
    <div class="userpageicon">{img modname='core' src='windowlist.png' set='icons/large'}</div>
    <h2>{gt text="Notes sent"}</h2>
    <div>{gt text="Form name"}: {$form.formName}</div>
    <div>{gt text="Title of the annotations "}: {$form.title}</div>
    {$content}
    {if $content eq ''}
    <div style="font-weight: bold;"><br />{gt text="Has not been found sent notes"}</div>
    {/if}
</div>
