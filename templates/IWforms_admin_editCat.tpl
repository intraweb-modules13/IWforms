{include file="IWforms_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='configure.png' set='icons/large'}</div>
    <h2>{gt text="Change to the category"}</h2>
    <form id="editCat" class="z-form" action="{modurl modname='IWforms' type='admin' func='updateCat'}" method="post" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="cid" value="{$item.cid}" />
        <div class="z-formrow">
            <label for="catName">{gt text="Name"}</label>
            <input type="text" name="catName" size="32" maxlength="55" value="{$item.catName}" />
        </div>
        <div class="z-formrow">
            <label for="description">{gt text="Description"}</label>
            <input type="text" name="description" size="50" maxlength="200" value="{$item.description}" />
        </div>
        <div class="z-center">
            <span class="z-buttons">
                <a onClick="javascript:forms['editCat'].submit()">
                    {img modname='core' src='button_ok.png' set='icons/small' __alt="Modify" __title="Modify"} {gt text="Modify"}
                </a>
            </span>
            <span class="z-buttons">
                <a href="{modurl modname='IWforms' type='admin' func='conf'}">
                    {img modname='core' src='button_cancel.png' set='icons/small'   __alt="Cancel" __title="Cancel"} {gt text="Cancel"}
                </a>
            </span>
        </div>
    </form>
</div>
