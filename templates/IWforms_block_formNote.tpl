{if $noNote == 0}
{if $skincssurl neq ''}
{pageaddvar name='stylesheet' value=$skincssurl}
{/if}
{$contentBySkin}
{if $isValidator}
<div>
    <a href="{modurl modname='IWforms' type='user' func='manage' fid=$fid fmid=$fmid}">
        {gt text="Edit content"}
    </a>
</div>
{/if}
{if $isAdministrator}
<div>
    <a href="{modurl modname='blocks' type='admin' func='modify' bid=$bid}">
        {gt text="Edit estructure"}
    </a>
</div>
{/if}
{else}
<div>
    {gt text="Note do not fount. Please check the id of the note."}
</div>
{/if}