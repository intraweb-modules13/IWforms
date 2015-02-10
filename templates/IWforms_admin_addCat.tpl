{include file="IWforms_admin_menu.tpl"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='configure.png' set='icons/large' __alt=''}</div>
    <h2>{gt text="Creates a new category"}</h2>
    <form id="addCat" class="z-form" action="{modurl modname='IWforms' type='admin' func='submitCat'}" method="post" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <div class="z-formrow">
            <label for="catName">{gt text="Name"}</label>
            <input type="text" name="catName" size="32" maxlength="55" />
        </div>
        <div class="z-formrow">
            <label for="description">{gt text="Description"}</label>
            <input type="text" name="description" size="50" maxlength="200" />
        </div>

        <div class="z-center">
            <span class="z-buttons">
                <a onclick="javascript: forms['addCat'].submit();">
                    {img modname='core' src='button_ok.png' set='icons/small'   __alt="Add" __title="Add"} {gt text="Add"}
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
