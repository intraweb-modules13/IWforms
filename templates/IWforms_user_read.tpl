<script language="javascript">
    function send(){
        document.order.submit();
    }
</script>
{include file="IWforms_user_menu.tpl" fid=$form.fid}
<div class="usercontainer">
    <div class="userpageicon">{img modname='core' src='windowlist.png' set='icons/large'}</div>
    <h2>{gt text="List of entries submitted"}</h2>
    {if $form.showFormName eq 1}
    <div>{gt text="Form name"}: {$form.formName}</div>
    {/if}
    {if $form.showNotesTitle eq 1}
    <div>{gt text="Title of the annotations "}: {$form.title}</div>
    {/if}
    {if !$oneRecord}
    <div class='options' style='margin-top: 20px;'>
        <form name="order" id="order" action="{modurl modname='IWforms' type='user' func='read'}" method="post" enctype="application/x-www-form-urlencoded">
            <input type="hidden" name="fidReload" value="{$fid}">
            <div style="float: left;">
                {gt text="N. Entries"}:
                <select name="ipp" onchange="javascript:send()">
                    <option {if $ipp eq 10}selected{/if} value="10">10</option>
                    <option {if $ipp eq 20}selected{/if} value="20">20</option>
                    <option {if $ipp eq 30}selected{/if} value="30">30</option>
                    <option {if $ipp eq 50}selected{/if} value="50">50</option>
                    <option {if $ipp eq 100}selected{/if} value="100">100</option>
                </select>
            </div>
            {if !$hideUsers}
            <div style="float: left; margin-left: 50px;">
                {gt text="Filter by user"}:
                <select name="u" onchange="javascript:send()">
                    <option value="0">{gt text="All"}</option>
                    {foreach key=key item=user from=$users}
                    <option {if $u eq $key}selected="selected"{/if} value="{$key}">{$user}</option>
                    {/foreach}
                </select>
            </div>
            {/if}
        </form>
    </div>
    {/if}
    <div style="float: left; margin-left: 50px;">{$pager}</div>
    <div style="height:15px; clear:both;">&nbsp;</div>
    {foreach item="note" from=$notes}
    {if $note.contentBySkin eq ''}
    {include file="IWforms_user_readNoteContent.tpl"}
    <div>&nbsp;</div>
    {else}
    {$form.skincssurl}
    {$note.contentBySkin|safehtml}
    {/if}
    {foreachelse}
    <div>{gt text="Not annotations found"}</div>
    {/foreach}
    <div style="float: left; margin-left: 20px;">{$pager}</div>
</div>
