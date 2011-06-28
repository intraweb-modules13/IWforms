function chgUsers(gid){
    var pars = "module=IWforms&func=chgUsers&gid=" + gid;
    showinfo('chgInfo');
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: chgUsers_response,
        onFailure: chgUsers_failure
    });
}

function chgUsers_failure(){
    showinfo('chgInfo');
    Element.update('validator', '').innerHTML;
}

function chgUsers_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    showinfo('chgInfo');
    Element.update('validator', json.content).innerHTML;
}

/**
 * Show a bus while ajax process
 * called twice: 
 * #1: Show the icon
 * #2: restore normal display
 *
 *@return none;
 *@author Albert Pérez Monfort
 */
function showinfo(info)
{
    if(!Element.hasClassName(info, 'z-hide')) {
        Element.update(info, '&nbsp;');
        Element.addClassName(info, 'z-hide');
    } else {
        Element.update(info, '<img src="images/ajax/circle-ball-dark-antialiased.gif">');
        Element.removeClassName(info, 'z-hide');
    }
}  


function modifyField(fieldId,char){
    if(char == 'collapse'){
        showfieldinfo(fieldId, expandcollapse);
    }else{
        showfieldinfo(fieldId, modifyingfield);
    }
    var pars = "module=IWforms&func=modifyField&fndid=" + fieldId + "&char=" + char;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: modifyField_response,
        onFailure: modifyField_failure
    });
}

function modifyField_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    changeContent(json.fndid);
}

function modifyField_failure(){

}

function changeContent(fndid){
    var pars = "module=IWforms&func=changeContent&fndid=" + fndid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: changeContent_response,
        onFailure: changeContent_failure
    });
}

function changeContent_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('field_'+json.fndid, json.content).innerHTML;
}

function changeContent_failure(){
}

function showfieldinfo(fndid, infotext){
    if(fndid) {
        var info = 'fieldinfo_' + fndid;
        if(!Element.hasClassName(info, 'z-hide')) {
            Element.update(info, '&nbsp;');
            Element.addClassName(info, 'z-hide');
        } else {
            Element.update(info, infotext);
            Element.removeClassName(info, 'z-hide');
        }
    } else {
        $A(document.getElementsByClassName('fieldinfo')).each(function(info){
            Element.update(info, '&nbsp;');
            Element.addClassName(info, 'z-hide');
        });
    }
}

function send(){
    document.conf.submit();
}

function closeForm(fid){
    var pars = "module=IWforms&func=closeForm&fid=" + fid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: closeForm_response,
        onFailure: closeForm_failure
    });
}

function closeForm_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    alert(json.text);
    Element.update('option_'+json.fid, json.content).innerHTML;
}

function closeForm_failure(){
}

function IWforms_deleteNote(fmid){
    resposta=confirm(deleteUserNote);
    if(resposta){
        var pars = "module=IWforms&func=deleteNote&fmid=" + fmid;
        var myAjax = new Ajax.Request("ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: IWforms_deleteNote_response,
            onFailure: IWforms_deleteNote_failure
        });
    }
}

function IWforms_deleteNote_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('note_' + json.fmid).toggle()
    reloadFlaggedBlock();
}

function IWforms_deleteNote_failure(){
}

function markNote(fmid){
    var pars = "module=IWforms&func=markNote&fmid=" + fmid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: markNote_response,
        onFailure: markNote_failure
    });
}

function markNote_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    if(json.mark == 'marked'){
        $(json.fmid).src="images/icons/small/flag.png";
    }else{
        $(json.fmid).src="modules/IWforms/images/none.gif";
    }
    Element.update('note_options_'+json.fmid, json.contentOptions).innerHTML;
    reloadFlaggedBlock();
}

function markNote_failure(){
}

function setCompleted(fmid){
    var pars = "module=IWforms&func=setCompleted&fmid=" + fmid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: setCompleted_response,
        onFailure: setCompleted_failure
    });
}

function setCompleted_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('note_'+json.fmid).style.backgroundColor=json.color;
    Element.update('note_options_'+json.fmid, json.contentOptions).innerHTML;
}

function setCompleted_failure(){
}

function validateNote(fmid){
    var pars = "module=IWforms&func=validateNote&fmid=" + fmid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: validateNote_response,
        onFailure: validateNote_failure
    });
}

function validateNote_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('note_'+json.fmid).style.backgroundColor=json.color;
    Element.update('note_options_'+json.fmid, json.contentOptions).innerHTML;
}

function validateNote_failure(){
}

function editNoteManageContent(fmid,toDo){
    var pars = "module=IWforms&func=editNoteManageContent&fmid=" + fmid + "&do=" + toDo;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: editNoteManageContent_response,
        onFailure: editNoteManageContent_failure
    });
}

function editNoteManageContent_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    if(json.toDo == 'observations'){
        Element.update('note_observations_'+json.fmid, json.content).innerHTML;
    }
    if(json.toDo == 'renote'){
        Element.update('note_renote_'+json.fmid, json.content).innerHTML;
    }
    if(json.toDo == 'content'){
        Element.update('note_content_'+json.fmid, json.content).innerHTML;
    }
}

function editNoteManageContent_failure(){
}

function submitValue(toDo,fmid,checked){
    if(toDo == 'observations'){
        var value = $('submitValueFormO_' + fmid).observations.value;
    }
    if(toDo == 'renote'){
        var value = $('submitValueFormR_' + fmid).renote.value;
    }
    if(toDo == 'content'){
        var value = $('submitValueFormC_' + fmid).content.value;
    }
    value = replaceChars('?', "|int|", value);
    value = replaceChars('&', "|amp|", value);
    value = replaceChars('#', "|par|", value);
    value = replaceChars('%', "|per|", value);
    var pars = "module=IWforms&func=submitValue&fmid=" + fmid + "&value=" + value + "&do=" + toDo + "&checked=" + checked;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: submitValue_response,
        onFailure: submitValue_failure
    });
}


function replaceChars(pattern, newstring, entry) {
    temp = "" + entry; // temporary holder
    while (temp.indexOf(pattern)>-1) {
        pos= temp.indexOf(pattern);
        temp = "" + (temp.substring(0, pos) + newstring +
            temp.substring((pos + pattern.length), temp.length));
    }
    return temp;
}

function submitValue_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    if(json.toDo == 'observations'){
        Element.update('note_observations_'+json.fmid, json.content).innerHTML;
    }
    if(json.toDo == 'renote'){
        Element.update('note_renote_'+json.fmid, json.content).innerHTML;
    }
    if(json.toDo == 'content'){
        Element.update('note_content_'+json.fmid, json.content).innerHTML;
    }
}

function submitValue_failure(){
}

function modifyForm(formId,char){
    showFormInfo(formId, modifyingform);
    var pars = "module=IWforms&func=modifyForm&fid=" + formId + "&char=" + char;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: modifyForm_response,
        onFailure: modifyForm_failure
    });
}

function modifyForm_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    changeFormContent(json.fid);
}

function modifyForm_failure(){
}

function changeFormContent(fid){
    var pars = "module=IWforms&func=changeFormContent&fid=" + fid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: changeFormContent_response,
        onFailure: changeFormContent_failure
    });
}

function changeFormContent_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('form_'+json.fid, json.content).innerHTML;
}

function changeFormContent_failure(){
}

function showFormInfo(fid, infotext){
    if(fid) {
        var info = 'forminfo_' + fid;
        if(!Element.hasClassName(info, 'z-hide')) {
            Element.update(info, '&nbsp;');
            Element.addClassName(info, 'z-hide');
        } else {
            Element.update(info, infotext);
            Element.removeClassName(info, 'z-hide');
        }
    } else {
        $A(document.getElementsByClassName('forminfo')).each(function(info){
            Element.update(info, '&nbsp;');
            Element.addClassName(info, 'z-hide');
        });
    }
}

function deleteUserNote(fmid){
    resposta=confirm(deleteUserNoteText);
    if(resposta){
        var pars = "module=IWforms&func=deleteUserNote&fmid=" + fmid;
        var myAjax = new Ajax.Request("ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: deleteUserNote_response,
            onFailure: deleteUserNote_failure
        });
    }
}

function deleteUserNote_response(req){
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('note_' + json.fmid).toggle()
}

function deleteUserNote_failure(){
}

function changeFilter(fid,filter){
    if(filter != 0){
        Element.update('filterValues', '<img src="images/ajax/circle-ball-dark-antialiased.gif">');
        var pars = "module=IWforms&func=changeFilter&fid=" + fid + "&filter=" + filter;
        var myAjax = new Ajax.Request("ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: changeFilter_response,
            onFailure: changeFilter_failure
        });
    }else{
        sendChange(1);
    }
}

function changeFilter_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('allNotesContent', json.content).innerHTML;
    Element.update('filterValues', json.filterContent).innerHTML;
    Element.update('itemsPerPage', '<div style="clear:both;"></div>').innerHTML;
}

function changeFilter_failure(){
}

function deleteForm(fid){
    response = confirm(deleteFormText);
    if (response) {
        var pars = "module=IWforms&func=deleteForm&fid=" + fid;
        var myAjax = new Ajax.Request("ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: deleteForm_response,
            onFailure: deleteForm_failure
        });
    }
}

function deleteForm_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('formRow_' + json.fid).toggle();
}

function deleteForm_failure(){
}

function createField(fieldType,fid){
    if(fieldType == 0){
        alert(chooseFiledType);
        exit;
    }
    Element.update('newFormField', '<img src="images/ajax/circle-ball-dark-antialiased.gif">');
    var pars = "module=IWforms&func=createField&fid=" + fid + "&fieldType=" + fieldType;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: createField_response,
        onFailure: createField_failure
    });
}

function createField_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('newFormField', json.content).innerHTML;
}

function createField_failure(req){
}

function deleteFormField(fndid,fid){
    response = confirm(deleteFormFieldText);
    if(response){
        var pars = "module=IWforms&func=deleteFormField&fid=" + fid + "&fndid=" + fndid;
        var myAjax = new Ajax.Request("ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: deleteFormField_response,
            onFailure: deleteFormField_failure
        });
    }
}

function deleteFormField_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    $('fieldrow_' + json.fndid).toggle();
}

function deleteFormField_failure(){

}

function newField(fid){
    var pars = "module=IWforms&func=newField&fid=" + fid;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: newField_response,
        onFailure: newField_failure
    });
}

function newField_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('fieldsList', json.content).innerHTML;
}

function newField_failure(){
}

function actionToDo(fid,action){
    var pars = "module=IWforms&func=actionToDo&fid=" + fid + "&action=" + action ;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: actionToDo_response,
        onFailure: actionToDo_failure
    });
}

function actionToDo_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('dContent', json.content).innerHTML;
    Element.update('form_minitabs', json.tabContent).innerHTML;
}

function actionToDo_failure(){
}

function allowCommentsControl(control) {
    var f=document.forms['formDefinition'];
    if (!f.allowComments.checked && control == 0) {
        f.allowCommentsModerated.checked = false;
    }
    if (f.allowCommentsModerated.checked && control == 1) {
        f.allowComments.checked = true;
    }
}

function expertModeActivation(fid, type) {
    var f=document.forms['formDefinition'];
    var expertMode = 0;
    var skinByTemplate = 0;
    if (f.expertMode.checked) {
        expertMode = 1;
    }
    if (type == 1) {
        if (f.skinByTemplate.checked) {
            skinByTemplate = 1;
        }
    }
    var pars = "module=IWforms&func=expertModeActivation&fid=" + fid + "&expertMode=" + expertMode + "&skinByTemplate=" + skinByTemplate;
    var myAjax = new Ajax.Request("ajax.php",
    {
        method: 'get',
        parameters: pars,
        onComplete: expertModeActivation_response,
        onFailure: expertModeActivation_failure
    });
}

function expertModeActivation_response(req){
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('expertModeContent', json.content).innerHTML;
}

function expertModeActivation_failure(){
}
