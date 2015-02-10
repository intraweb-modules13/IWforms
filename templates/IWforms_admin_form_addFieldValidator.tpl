<div class="formContent">
    <div class="z-adminpageicon">{img modname='core' src='editdelete.png' set='icons/medium'}</div>
    <h2>{gt text="Add a validator"}</h2>
    <form id="addFieldValidator" class="z-form" action="{modurl modname='IWforms' type='admin' func='addFieldValidator'}" method="post" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="fid" value="{$item.fid}" />
        <input type="hidden" name="confirm" value="1" />
        <input type="hidden" name="fndid" value="{$itemField.fndid}" />
        {gt text="Choose a new validator for the field"} <strong>{$itemField.fieldName}</strong>
        <div class="z-formrow">
            <label for="validator">{gt text="Choose the group that should have access to the form"}</label>
            {if @count|$validators gt 0}
            <select name="validator">
                {foreach item=validator from=$validators}
                <option value="{$validator.validatorUserId}">{$users[$validator.validatorUserId]}</option>
                {/foreach}
            </select>
            {else}
            <strong>{gt text="There is no validators available for this form"}</strong>
            {/if}
        </div>
        <div class="z-center">
            <span class="z-buttons">
                <a onClick="javascript:forms['addFieldValidator'].submit()">
                    {img modname='core' src='button_ok.png' set='icons/small' __alt="Add" __title="Add"} {gt text="Add"}
                </a>
            </span>
            <span class="z-buttons">
                <a href="{modurl modname='IWforms' type='admin' func='form' action='field' fid=$item.fid}">
                    {img modname='core' src='button_cancel.png' set='icons/small' __alt="Cancel" __title="Cancel"} {gt text="Cancel"}
                </a>
            </span>
        </div>
    </form>
</div>