{*}
{if $item.skincssurl neq ''}
{add_additional_header header=$item.skincssurl}
{/if}
{*}
<div class="formContent">
    {if not isset($adminView)}
    <div class="z-adminpageicon">{img modname='core' src='windowlist.png' set='icons/large'}</div>
    <h2>{gt text="Form definition"}</h2>
    {/if}
    <div style="height: 15px;">&nbsp;</div>
    {if $item.lang neq ''}
    <img src="images/flags/flag-{$item.lang}.png" />
    {/if}
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Form name"}</span>:
        {$item.formName}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Title of the annotations "}</span>:
        {$item.title}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Category"}</span>:
        {$catName}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Form description"}</span>:
        {$item.description}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Anonymous"}</span>:
        {if $item.annonimous}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Only answer"}</span>:
        {if $item.unique}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Validators can close the annotations"}</span>:
        {if $item.closeableNotes}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Validators can close the entries or the release of the book-entry form"}</span>:
        {if $item.closeableInsert}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="The form is initially closed"}</span>:
        {if $item.closeInsert}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Default number of notes for page in users read view"}</span>:
        {if $item.defaultNumberOfNotes eq 1}
        10
        {elseif $item.defaultNumberOfNotes eq 2}
        20
        {elseif $item.defaultNumberOfNotes eq 3}
        30
        {elseif $item.defaultNumberOfNotes eq 4}
        50
        {elseif $item.defaultNumberOfNotes eq 5}
        70
        {elseif $item.defaultNumberOfNotes eq 6}
        100
        {elseif $item.defaultNumberOfNotes eq 7}
        500
        {else}
        {/if}
    </div>

    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Default order for notes"}</span>:
        {if $item.defaultOrderForNotes eq 1}
        {gt text="Cronologicaly inverse"}
        {elseif $item.defaultOrderForNotes eq 2}
        {gt text="Cronologicaly"}
        {elseif $item.defaultOrderForNotes eq 3}
        {gt text="Alphabetical"}
        - {gt text="Ordenation form field"}: 
        {if $item.orderFormField eq 0}
        <span style="color: red;">
            {gt text="There are not defined fields. You should select this option editing this form after fields creation. The default option is Cronologicaly inverse"}
        </span>            
        {else}
        {$orderFormField}
        {/if}
        {elseif $item.defaultOrderForNotes eq 4}
        {gt text="Random order"}
        {else}
        {/if}
    </div>

    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Unregistered users can not see the data of senders of entries"}</span>:
        {if $item.unregisterednotusersview}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Unregistered users can not export the contents of the annotations"}</span>:
        {if $item.unregisterednotexport}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="The answer is visible to all users who have access to information"}</span>:
        {if $item.publicResponse}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="As a new until a..."}</span>
        {if $item.new neq '0000-00-00 00:00:00'}{$new}{/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Make it expires automatically a..."}</span>
        {if $item.caducity neq '0000-00-00 00:00:00'}{$caducity}{/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="The notes allow comments"}</span>:
        {if $item.allowComments}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="The validators can decide if a particular note allow comments"}</span>:
        {if $item.allowCommentsModerated}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Use expert mode"}</span>:
        {if $item.expertMode}
        <span class="active">{gt text="Yes"}</span>
        {else}
        <span class="inactive">{gt text="No"}</span>
        {/if}
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Return URL after sending a new note"}</span>: 
        <a href="{$item.returnURL}">{$item.returnURL}</a>
    </div>
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Form files folder"}</span>:
        {$item.filesFolder}
    </div>
    {if not $folderExists}
    <div class="z-errormsg">
        {gt text="Files folder not found."}
    </div>
    {else}
    {if not $folderIsWriteable}
    <div class="z-errormsg">
        {gt text="The files folder is not writeable."}
    </div>
    {/if}
    {/if}
    {if $item.expertMode}
    <fieldset style="margin: 15px 0px 15px 0px;">
        <legend>{gt text="Templates for the notes (expert mode)"}</legend>
        {if $item.skinByTemplate}
        <div class="formRowDefinition">
            <span class="formRowDefinitionDBField">{gt text="Template to aply for the form"}</span>:
            {$item.skinFormTemplate}
        </div>
        <div class="formRowDefinition">
            <span class="formRowDefinitionDBField">{gt text="Template to aply for the set of notes"}</span>:
            {$item.skinTemplate}
        </div>
        <div class="formRowDefinition">
            <span class="formRowDefinitionDBField">{gt text="Template to aply for an alone note"}</span>:
            {$item.skinNoteTemplate}
        </div>
        {else}
        <div class="formRowDefinition">
            <span class="formRowDefinitionDBField">{gt text="Show the form name"}</span>:
            {if $item.showFormName}
            <span class="active">{gt text="Yes"}</span>
            {else}
            <span class="inactive">{gt text="No"}</span>
            {/if}
        </div>
        <div class="formRowDefinition">
            <span class="formRowDefinitionDBField">{gt text="Show the notes title"}</span>:
            {if $item.showNotesTitle}
            <span class="active">{gt text="Yes"}</span>
            {else}
            <span class="inactive">{gt text="No"}</span>
            {/if}
        </div>
        <div class="formRowDefinition">
            <div>
                {gt text="Form aspect"}
            </div>
            <div class="formSkin">{$item.skinForm|safetext|nl2br}</div>
        </div>
        <div class="z-informationmsg">
            {gt text="[\$id\$] => Form field"}
        </div>
        <div class="formRowDefinition">
            <div>
                {gt text="Set of notes of the form"}
            </div>
            <div class="formSkin">{$item.skin|safetext|nl2br}</div>
        </div>
        <div class="formRowDefinition">
            <div>
                {gt text="An individual note"}
            </div>
            <div class="formSkin">{$item.skinNote|safetext|nl2br}</div>
        </div>
        <div class="z-informationmsg">
            {gt text="[\$formId\$] =>Identity of the form, [\$noteId\$] =>Identity of the note, [%id%] => Title of the field, [\$id\$] => Content of the field, [\$user\$] => Username, [\$date\$] => Note creation date, [\$time\$] => Note creation time, [\$avatar\$] => User avatar, [\$reply\$] => Reply to the user if the reply is public"}
        </div>
        <div class="formRowDefinition">
            <span class="formRowDefinitionDBField">{gt text="Styles sheet to aply"}</span>:
            {$item.skincss}
        </div>
        {/if}
    </fieldset>
    {/if}
    <div class="formRowDefinition">
        <span class="formRowDefinitionDBField">{gt text="Active / non-active"}</span>:
        {if $item.active}
        <span class="active">{gt text="Active"}</span>
        {else}
        <span class="inactive">{gt text="No active"}</span>
        {/if}
    </div>
    {if not isset($adminView)}
    <div class="z-buttons z-center">
        <a href="{modurl modname='IWforms' type='admin' func='form' do='edit' fid=$item.fid}">
            {img modname='core' src='edit.png' set='icons/small' __alt="Edit the main form" __title="Edit the main form"}
            {gt text="Edit the main form"}
        </a>
    </div>
    {/if}
</div>
