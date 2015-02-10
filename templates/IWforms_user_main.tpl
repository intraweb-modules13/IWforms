{include file="IWforms_user_menu.tpl"}
<div class="usercontainer">
    <div class="userpageicon">{img modname='core' src='windowlist.png' set='icons/large'}</div>
    <h2>{gt text="Forms list"}</h2>
    <div style="height:15px;">&nbsp;</div>

    <table class="usertable">
        {foreach item="form" from=$forms}
        <tr bgcolor="{cycle values="#ffffff, #eeeeee"}">
            <td valign="top">
                {if $form.accessLevel eq 7}
                    {if $form.isFlagged eq 1}
                        <div>
                            {img modname='core' src='flag.png' set='icons/extrasmall' __alt="Open the entry of annotations and editing" __title="Open the entry of annotations and editing"}
                        </div>
                    {/if}
                    {if $form.needValidation eq 1}
                        <div>
                            {img modname='core' src='14_layer_visible.png' set='icons/extrasmall' __alt="Open the entry of annotations and editing" __title="Open the entry of annotations and editing"}
                        </div>
                    {/if}
                {/if}
                {if $form.newLabel}
                    <div style="color: red; background-color: #ffffaf;">{gt text="New"}</div>
                {/if}
            </td>
            <td valign="top">
                {$form.formName}
            </td>
            <td valign="top">
                {$form.title}
            </td>
            <td valign="top">
                {if $form.accessLevel eq 1}
                    {gt text="Only writing"}
                {elseif $form.accessLevel eq 2}
                    {gt text="Only reading"}
                {elseif $form.accessLevel eq 3}
                    {gt text="Reading and writing"}
                {elseif $form.accessLevel eq 4}
                    {gt text="Validation of fields"}
                {elseif $form.accessLevel eq 7}
                    {gt text="Validation"}
                {/if}
                {if $form.accessLevel < 7 AND $form.defaultValidation eq 0}
                <div>{gt text="Validation is required"}</div>
                {/if}
            </td>
            <td valign="top">
                {include file="IWforms_user_mainOptions.tpl" form=$form}
            </td>
        </tr>
		{foreachelse}
        <tr>
            <td colspan="10">
                {gt text="You do not have any form available"}
            </td>
        </tr>
        {/foreach}
    </table>
</div>
