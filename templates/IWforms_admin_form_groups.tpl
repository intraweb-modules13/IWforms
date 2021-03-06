<div class="formAdd">
    {if not isset($adminView)}
    <a href="{modurl modname='IWforms' type='admin' func='form' action='group' do='add' fid=$item.fid}">
        {img modname='core' src='add_group.png' set='icons/extrasmall'   __alt="Add a new group" __title="Add a new group"}
        {gt text="Add a new group"}
    </a>
    {else}
    <a href="{modurl modname='IWforms' type='admin' func='form' action='group' do='add' aio=1 fid=$fid}">
        {img modname='core' src='add_group.png' set='icons/extrasmall'   __alt="Add a new group" __title="Add a new group"}
        {gt text="Add a new group"}
    </a>
    {/if}
</div>
<div class="formContent">
    {if not isset($adminView)}
    <div class="z-adminpageicon">{img modname='core' src='agt_family.png' set='icons/large'}</div>
    <h2>{gt text="List of groups that have access to the form"}</h2>
    {/if}

    <div style="height: 5px;">&nbsp;</div>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Name of the group"}</th>
                <th>{gt text="Type of acces"}</th>
                <th>{gt text="Validation"}</th>
                <th>{gt text="Options"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach item=group from=$groups}
            <tr class="{cycle values="z-odd,z-even"}" id="topic_{$group.gfid}">
                <td>
                     {$group.groupName}
                 </td>
                 <td>
                     {$group.accessType}
                 </td>
                 <td>
                     {$group.validationNeeded}
                 </td>
                 <td valign="top" width="80" align="center">
                     {if not isset($adminView)}
                     <a href="{modurl modname='IWforms' type='admin' func='form' action='group' do='delete' fid=$item.fid gfid=$group.gfid}">
                         {img modname='core' src='delete_group.png' set='icons/extrasmall' __alt="Delete the group" __title="Delete the group"}
                     </a>
                     {else}
                     <a href="{modurl modname='IWforms' type='admin' func='form' action='group' do='delete' fid=$fid aio=1 gfid=$group.gfid}">
                         {img modname='core' src='delete_group.png' set='icons/extrasmall' __alt="Delete the group" __title="Delete the group"}
                     </a>
                     {/if}
                 </td>
             </tr>
             {foreachelse}
             <tr>
                 <td colspan="10">
                     {gt text="No defined groups with access to the form"}
                 </td>
             </tr>
             {/foreach}
            </tbody>
        </table>
    </div>