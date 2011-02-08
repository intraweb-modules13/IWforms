<?php
/**
 * Change the users in select list
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Redirect to the user main page
*/
function IWforms_ajax_chgUsers($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$gid = FormUtil::getPassedValue('gid', -1, 'GET');
	if ($gid == -1) {
		AjaxUtil::error('no group id');
	}
   	// get group members
	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	$groupMembers = ModUtil::func('IWmain', 'user', 'getMembersGroup',
                               array('sv' => $sv,
									 'gid' => $gid));
	asort($groupMembers);
	if (empty($groupMembers)) {
        AjaxUtil::error('unable to get group members or group is empty for gid=' . DataUtil::formatForDisplay($gid));
	}
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign ('groupMembers',$groupMembers);
	$view->assign ('action','chgUsers');
	$content = $view->fetch('IWforms_admin_ajax.htm');
	AjaxUtil::output(array('content' => $content));
}

/**
 * Change the characteristics of a field definition
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the field and the value to change
 * @return:	the field row new value in database
*/
function IWforms_ajax_modifyField($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fndid = FormUtil::getPassedValue('fndid', -1, 'GET');
	if ($fndid == -1) {
		AjaxUtil::error('no field id');
	}
	$char = FormUtil::getPassedValue('char', -1, 'GET');
	if ($char == -1) {
		AjaxUtil::error('no char defined');
	}
	//Get field information
	$itemField = ModUtil::apiFunc('IWforms', 'user', 'getFormField',
                               array('fndid' => $fndid));
	if ($itemField == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Could not find form', $dom)));
	}
	if($char == "accessType"){
		$value = $itemField[$char] + 1;
		if($value >= 3){$value = 0;}
	}else{
		$value = ($itemField[$char]) ? 0 : 1;
	}
	//change value in database
	$items = array($char => $value);
	if(!ModUtil::apiFunc('IWforms', 'admin', 'editFormField',
                      array('fndid' => $fndid,
							'items' => $items))){
		AjaxUtil::error('Error');
	}
	AjaxUtil::output(array('fndid' => $fndid));
}

/**
 * Change the characteristics of a field definition
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the field and the value to change
 * @return:	the field row new value in database
*/
function IWforms_ajax_changeContent($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fndid = FormUtil::getPassedValue('fndid', -1, 'GET');
	if ($fndid == -1) {
		AjaxUtil::error('no field id');
	}
	//Get field information
	$field = ModUtil::apiFunc('IWforms', 'user', 'getFormField',
                           array('fndid' => $fndid));
	if ($field == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Could not find form', $dom)));
	}
	if($field['gid'] > 0){
		$group = ModUtil::apiFunc('Groups','user','get',array('gid' => $field['gid']));
		$groupName = $group['name'];
	}
	$fieldTypeTextArray = ModUtil::func('IWforms', 'admin', 'getFileTypeText');
	$fields_array = array('fndid' => $field['fndid'],
						  'fieldName' => $field['fieldName'],
						  'feedback' => $field['feedback'],
						  'fieldTypePlus' => '-'.$field['fieldType'].'-',
						  'active' => $field['active'],
						  'description' => $field['description'],
						  'required' => $field['required'],
						  'order' => $field['order'],
						  'validationNeeded' => $field['validationNeeded'],
						  'notify' => $field['notify'],
						  'dependance' => str_replace('$$',',',substr($field['dependance'],2,-1)),
						  'accessType' => $field['accessType'],
						  'editable' => $field['editable'],
						  'size' => $field['size'],
						  'cols' => $field['cols'],
						  'rows' => $field['rows'],
						  'editor' => $field['editor'],
						  'publicFile' => $field['publicFile'],
						  'checked' => $field['checked'],
						  'options' => $field['options'],
						  'calendar' => $field['calendar'],
						  'height' => $field['height'],
						  'color' => $field['color'],
						  'colorf' => $field['colorf'],
						  'collapse' => $field['collapse'],
						  'searchable' => $field['searchable'],
						  'group' => $groupName,
                          'extensions' => $field['extensions'],
                          'imgWidth' => $field['imgWidth'],
                          'imgHeight' => $field['imgHeight']);
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('field', $fields_array);
	$content = $view->fetch('IWforms_admin_form_fieldCharContent.htm');
	AjaxUtil::output(array('content' => $content,
						   'fndid' => $fndid));
}

/**
 * Close/open a form
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the form that must be closed
 * @return:	Form new state
*/
function IWforms_ajax_closeForm($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	//Get item
	$form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                          array('fid' => $fid));
	if ($form == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Could not find form', $dom)));
	}
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
                         array('fid' => $fid));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	$close = ModUtil::apiFunc('IWforms', 'user', 'closeInsert',
                           array('fid' => $fid));
	//check user access to this form
	if($close == false){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error in the modified form', $dom)));
	}
	$closeInserValue = ($form['closeInsert'] == 0) ? 1 : 0;
	$form_array = array('formName' => $form['formName'],
						'title' => $form['title'],
						'accessLevel' => $access['level'],
						'closeableInsert' => $form['closeableInsert'],
						'closeInsert' => $closeInserValue,
						'defaultValidation' => $access['defaultValidation'],
						'fid' => $form['fid']);
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('form', $form_array);
	$content = $view->fetch('IWforms_user_mainOptions.htm');
	$text = ($form['closeInsert'] == 0) ? __('Has closed input data on the form', $dom) : __('Has opened input data on the form', $dom);
	AjaxUtil::output(array('text' => $text,
							'content' => $content,
							'fid' => $fid));
}

/**
 * Close/open a form
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the form that must be closed
 * @return:	Form new state
*/
function IWforms_ajax_deleteNote($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	//get the note information
	$note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                          array('fmid' => $fmid));
	//Get form fields
	$fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                            array('fid' => $note['fid']));
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
                         array('fid' => $note['fid']));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	if(!ModUtil::apiFunc('IWforms', 'user', 'deleteNote',
                      array('fmid' => $fmid))){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error to remove the annotation', $dom)));
	}
	foreach($fields as $field){
		if($field['fieldType'] == '7'){
			$noteContent = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                                         array('fid' => $note['fid'],
											   'fmid' => $note['fmid']));
			if($noteContent[$field['fndid']]['content'] != ''){
				$file = ModUtil::getVar('IWmain','documentRoot') . '/' . ModUtil::getVar('IWforms','attached') . '/' . $noteContent[$field['fndid']]['content'];
				if(file_exists($file)){
					unlink($file);	
				}
			}
		}
	}
	AjaxUtil::output(array('fmid' => $fmid));
}

/**
 * Define a note as marked or unmarked
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Redirect to the user main page
*/
function IWforms_ajax_markNote($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	//get the note information
	$note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                          array('fmid' => $fmid));
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
                         array('fid' => $note['fid']));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	//Change the flagged atributes for the user
	$mark = ModUtil::apiFunc('IWforms', 'user', 'markNote',
                          array('fmid' => $fmid));
	//check user access to this form
	if($mark == false){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error in the modified form', $dom)));
	}
	$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
	ModUtil::func('IWmain', 'user', 'userSetVar',
               array('module' => 'IWmain_block_flagged',
					 'name' => 'have_flags',
					 'value' => 'fr',
					 'sv' => $sv));
	$userName = ($note['annonimous'] == 0) ? UserUtil::getVar('uname',$note['user']) : '';
	$marked = ($mark == 'marked') ? 1 : 0;
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('note', array('fmid' => $fmid,
									'marked' => $marked,
									'state' => $note['state'],
									'userName' => $userName,
									'validate' => $note['validate']));
	$contentOptions = $view->fetch('IWforms_user_manageNoteContentOptions.htm');
	AjaxUtil::output(array('fmid' => $fmid,
				'mark' => $mark,
				'contentOptions' => $contentOptions));
}

/**
 * Set a note as completed or uncompleted
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Redirect to the user main page
*/
function IWforms_ajax_setCompleted($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	//get the note information
	$note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                          array('fmid' => $fmid));
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
                         array('fid' => $note['fid']));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	//Change the flagged atributes for the user
	$state = ModUtil::apiFunc('IWforms', 'user', 'changeState',
                           array('fmid' => $fmid));
	//check user access to this form
	if($state == false){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error in the modified form', $dom)));
	}
	$stateValue = ($state == 'completed') ? 1 : 0;
	$note['state'] = $stateValue;
	$userName = ($note['annonimous'] == 0) ? UserUtil::getVar('uname',$note['user']) : '';
	$marked = (strpos($note['mark'],'$'.UserUtil::getVar('uid').'$') !== false) ? 1 : 0;
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('note', array('fmid' => $fmid,
									'marked' => $marked,
									'state' => $stateValue,
									'userName' => $userName,
									'validate' => $note['validate']));
	$contentOptions = $view->fetch('IWforms_user_manageNoteContentOptions.htm');
	AjaxUtil::output(array('fmid' => $fmid,
							'color' => ModUtil::func('IWforms', 'user', 'calcColor',
                                                  array('validate' => $note['validate'],
														'state' => $note['state'],
														'viewed' => $note['viewed'])),
														'contentOptions' => $contentOptions));
}

/**
 * validate a note
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Redirect to the user main page
*/
function IWforms_ajax_validateNote($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	//get the note information
	$note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                          array('fmid' => $fmid));
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
                         array('fid' => $note['fid']));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	//Change the flagged atributes for the user
	$state = ModUtil::apiFunc('IWforms', 'user', 'validateNote',
                           array('fmid' => $fmid));
	if($state == false){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error in the modified form', $dom)));
	}
    $validate = ($note['validate'] == 1) ? 0 : 1;
	$note['validate'] = $validate;
	$userName = ($note['annonimous'] == 0) ? UserUtil::getVar('uname',$note['user']) : '';
	$marked = (strpos($note['mark'], '$' . UserUtil::getVar('uid') . '$') !== false) ? 1 : 0;
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('note', array('fmid' => $fmid,
									'marked' => $marked,
									'state' => $note['state'],
									'userName' => $userName,
									'validate' => $note['validate']));
	$contentOptions = $view->fetch('IWforms_user_manageNoteContentOptions.htm');
	AjaxUtil::output(array('fmid' => $fmid,
							'color' => ModUtil::func('IWforms', 'user', 'calcColor',
                                                  array('validate' => $note['validate'],
														'state' => $note['state'])),
														'contentOptions' => $contentOptions));
}

/**
 * Edit the notes observations or the answares to writers
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Show the observations or renotes contents forms
*/
function IWforms_ajax_editNoteManageContent($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	// in the case of editing the content of a note the parameter fmid refers to fnid
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	$do = FormUtil::getPassedValue('do', -1, 'GET');
	if ($do == -1) {
		AjaxUtil::error('no action defined');
	}
	if ($do == 'content') {
		$fnid = $fmid;
		$noteContent = ModUtil::apiFunc('IWforms', 'user', 'getNoteContent',
									 array('fnid' => $fnid));
		if ($noteContent === false) {
			AjaxUtil::error(__('For some reason it is not possible to edit the field\'s content.', $dom));
		}
		if ($noteContent['editable'] != 1) {
			AjaxUtil::error(__('You can not edit this note.', $dom));
		}
		$fmid = $noteContent['fmid'];
	}
	// in the case of editing the content of a note the parameter fmid refers to fnid
	// get the note information
	$note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                          array('fmid' => $fmid));
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
                         array('fid' => $note['fid']));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('do', 'edit');
	if($do == 'observations'){
		$view->assign('note', $note);
		$content = $view->fetch('IWforms_user_manageNoteContentObs.htm');
	}
	if($do == 'renote'){
		$view->assign('note', $note);
		//get form definition
		$form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                              array('fid' => $note['fid']));
		if ($form == false) {
			LogUtil::registerError (__('Could not find form', $dom));
			return false;
		}
		$view->assign('form', $form);
		$content = $view->fetch('IWforms_user_manageNoteContentRenote.htm');
	}
	if ($do == 'content') {
		$fmid = $fnid;
		$view->assign('noteContent', $noteContent);
		$content = $view->fetch('IWforms_user_manageNoteContentEdit.htm');
	}
	AjaxUtil::output(array('fmid' => $fmid,
						   'content' => $content,
						   'toDo' => $do));
}

/**
 * update the notes observations or the answares to writers
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Change the observations or renotes contents
*/
function IWforms_ajax_submitValue($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	$do = FormUtil::getPassedValue('do', -1, 'GET');
	if ($do == -1) {
		AjaxUtil::error('no action defined');
	}
	$value = FormUtil::getPassedValue('value', -1, 'GET');
	if ($do == -1) {
		AjaxUtil::error('no value defined');
	}
	// in the case of editing the content of a note the parameter fmid refers to fnid
	if ($do == 'content') {
		$fnid = $fmid;
		$noteContent = ModUtil::apiFunc('IWforms', 'user', 'getNoteContent',
									 array('fnid' => $fnid));
		if ($noteContent === false) {
			AjaxUtil::error(__('For some reason it is not possible to edit the field\'s content.', $dom));
		}
		$fmid = $noteContent['fmid'];
	}
	//get the note information
	$note = ModUtil::apiFunc('IWforms','user','getNote',
						  array('fmid' => $fmid));
	//check user access to this form
	$access = ModUtil::func('IWforms', 'user', 'access',
						 array('fid' => $note['fid']));
	if($access['level'] < 7){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	if ($do == 'content') {
		$submited = ModUtil::apiFunc('IWforms', 'user', 'submitContentValue', 
								  array('value' => $value,
										'fmid' => $fmid,
								  		'fnid' => $fnid,
										'toDo' => $do));
	} else {
		//submit values
		$submited = ModUtil::apiFunc('IWforms', 'user', 'submitValue',
								  array('value' => $value,
										'fmid' => $fmid,
										'toDo' => $do));
	}
	if ($submited == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error in the modified form', $dom)));
	}

	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('do', 'print');
	if ($do == 'observations') {
		$note['observations'] = $value;
		$view->assign('note', $note);
		$content = $view->fetch('IWforms_user_manageNoteContentObs.htm');
	}
	if ($do == 'renote') {
		$checked = FormUtil::getPassedValue('checked', -1, 'GET');
		$note['renote'] = $value;
		$view->assign('note', $note);
		$content = $view->fetch('IWforms_user_manageNoteContentRenote.htm');
		$modid = ModUtil::getIdFromName('iw_messages');
		$modinfo = ModUtil::getInfo($modid);
		if($checked == 'true' && $modinfo['state'] == 3 && $note['annonimous'] == 0){
			$view->assign('fmid', $fmid);
			$noteOrigen = $view->fetch('IWforms_user_origenNote.htm');
			$note['renote'] = str_replace('|int|','?',$note['renote']);
			$note['renote'] = str_replace('|amp|','&',$note['renote']);
			$note['renote'] = str_replace('|par|','#',$note['renote']);
			$note['renote'] = str_replace('|per|','%',$note['renote']);
			// set copy whit a private message to user
			ModUtil::apiFunc('iw_messages', 'user', 'create',
                          array('image' => '',
								'subject' => __('Forms: automatic message', $dom),
								'to_userid' => $note['user'],
								'message' => nl2br($note['renote'].$noteOrigen),
								'reply' => '',
								'file1' =>'',
								'file2' => '',
								'file3' => ''));
		}
	}
	if ($do == 'content') {
		$view->assign('value', $value);
		$view->assign('fnid', $fnid);
		$fmid = $fnid;
		$content = $view->fetch('IWforms_user_manageNoteContentEdit.htm');	
	}
	AjaxUtil::output(array('fmid' => $fmid,
						   'content' => $content,
						   'toDo' => $do));
}

/**
 * Change the characteristics of a form definition
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the field and the value to change
 * @return:	the form new value in database
*/
function IWforms_ajax_modifyForm($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$char = FormUtil::getPassedValue('char', -1, 'GET');
	if ($char == -1) {
		AjaxUtil::error('no char defined');
	}
	//Get form information
	$itemForm = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                              array('fid' => $fid));
	if ($itemForm == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Could not find form', $dom)));
	}
	$value = ($itemForm[$char]) ? 0 : 1;
	//change value in database
	$items = array($char => $value);
	if(!ModUtil::apiFunc('IWforms', 'admin', 'editForm',
                      array('fid' => $fid,
							'items' => $items))){
		AjaxUtil::error('Error');
	}
	AjaxUtil::output(array('fid' => $fid));
}

/**
 * Change the characteristics of a field definition
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the field and the value to change
 * @return:	the field row new value in database
*/
function IWforms_ajax_changeFormContent($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	//Get field information
	$form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                          array('fid' => $fid));
	if ($form == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Could not find form', $dom)));
	}
	$view = Zikula_View::getInstance('IWforms',false);
	$form['new'] = ModUtil::func('IWforms', 'user', 'makeTimeForm', $form['new']);
	$form['caducity'] = ModUtil::func('IWforms', 'user', 'makeTimeForm', $form['caducity']);
	$view->assign('form', $form);
	$content = $view->fetch('IWforms_admin_formChars.htm');
	AjaxUtil::output(array('content' => $content,
							'fid' => $fid));
}

/**
 * set as deleted by user
 * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   Array with the id of the note
 * @return:	Redirect to the user main page
*/
function IWforms_ajax_deleteUserNote($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fmid = FormUtil::getPassedValue('fmid', -1, 'GET');
	if ($fmid == -1) {
		AjaxUtil::error('no note id');
	}
	//get the note information
	$note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                          array('fmid' => $fmid));
	//check user access to this note
	if($note['user'] != UserUtil::getVar('uid')){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('You do not have access to manage form', $dom)));
	}
	//Change the deleted atributes for the user
	$state = ModUtil::apiFunc('IWforms', 'user', 'deleteUserNote',
                           array('fmid' => $fmid));
	if($state == false){
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('There was an error in the modified form', $dom)));
	}
	AjaxUtil::output(array('fmid' => $fmid));
}

function IWforms_ajax_changeFilter($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$filter = FormUtil::getPassedValue('filter', -1, 'GET');
	if ($filter == -1) {
		AjaxUtil::error('no filter id');
	}
	//get form fields
	$fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                            array('fid' => $fid,
								  'whereArray' => 'active|1$$searchable|1'));
	$filterType = 0;
	switch($fields[$filter]['fieldType']){
		case 6:
			$options = explode('-', $fields[$filter]['options']);
			$optionsArray = array();
			foreach($options as $option){
				$optionsArray[$option] = $option;
			}
			if($fields[$filter]['gid'] > 0){
				$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
				$members = ModUtil::func('IWmain', 'user', 'getMembersGroup',
                                      array('sv' => $sv,
											'gid' => $fields[$filter]['gid'],
											'onlyId' => 1));
				if(count($members) > 0){
					$usersList = '$$';
					foreach($members as $member){
						$usersList .= $member['id'].'$$';
					}
					$sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
					$users1 = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                                         array('info' => 'ccn',
											   'sv' => $sv,
											   'list' => $usersList));
					asort($users1);
					foreach($users1 as $user){
						$optionsArray[$user] = $user;
					}
				}
			}
			$items = $optionsArray;
			break;
		case 8:
			$optionsArray[__('No', $dom)] = __('No', $dom);
			$optionsArray[__('Yes', $dom)] = __('Yes', $dom);
			$items = $optionsArray;
			break;
		default:
			$filterType = 1;
			$filterValue = $filterValue;
	}
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('items', $optionsArray);
	$view->assign('fid', $fid);
	$view->assign('filter', 1);
	$view->assign('filterType', $filterType);
	$filterContent = $view->fetch('IWforms_user_manageFilter.htm');
	$view->assign('total', 0);
	$content = $view->fetch('IWforms_user_manageAllNotesContent.htm');
	AjaxUtil::output(array('content' => $content,
							'filterContent' => $filterContent));
}

function IWforms_ajax_deleteForm($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	//delete the form fields
	if(!ModUtil::apiFunc('IWforms', 'admin', 'deleteFormFields',
                      array('fid' => $fid))){
		AjaxUtil::error(__('Has been removed the fields of the form', $dom));
	}
	//delete the form groups
	if(!ModUtil::apiFunc('IWforms', 'admin', 'deleteFormGroups',
                      array('fid' => $fid))){
		AjaxUtil::error(__('Has been removed the groups of the form', $dom));
	}
	//delete the form validators
	if(!ModUtil::apiFunc('IWforms', 'admin', 'deleteFormValidators',
                      array('fid' => $fid))){
		AjaxUtil::error(__('Has been removed the validators of the form', $dom));
	}
	//delete the form notes
	if(!ModUtil::apiFunc('IWforms', 'admin', 'deleteFormNotes', array('fid' => $fid))){
		AjaxUtil::error(__('Dropped the annotations of the form', $dom));
	}
	//delete the form
	if(!ModUtil::apiFunc('IWforms', 'admin', 'deleteForm',
                      array('fid' => $fid))){
		AjaxUtil::error(__('Has been removed form', $dom));
	}
	AjaxUtil::output(array('fid' => $fid));
}

function IWforms_ajax_deleteFormField($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$fndid = FormUtil::getPassedValue('fndid', -1, 'GET');
	if ($fndid == -1) {
		AjaxUtil::error('no field id');
	}
	//Check if there are other fields that depens on it. In this case the field can't be deleted
	$dependancesTo = ModUtil::apiFunc('IWforms', 'user', 'getFormFieldDependancesTo',
                                   array('fndid' => $fndid));
	if($dependancesTo){
		AjaxUtil::error('no possible');
	}
	if(!ModUtil::apiFunc('IWforms', 'admin', 'deleteFormField',
                      array('itemField' => $fndid))){
		LogUtil::registerStatus (_IWFORMSFORMFIELDDELETEDERROR);
	}
	// Reorder the items
	ModUtil::apiFunc('IWforms', 'admin', 'reorder',
                  array('fid' => $fid));
	AjaxUtil::output(array('fndid' => $fndid));
}

function IWforms_ajax_createField($args){
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$fieldType = FormUtil::getPassedValue('fieldType', -1, 'GET');
	if ($fieldType == -1) {
		AjaxUtil::error('no field type');
	}
	$createField = ModUtil::apiFunc('IWforms', 'admin', 'createFormField',
                                 array('fid' => $fid,
									   'fieldType' => $fieldType,
									   'fieldName' => __('Field name', $dom)));
	if(!$createField){
		AjaxUtil::error('creation error');
	}
	ModUtil::apiFunc('IWforms', 'admin', 'reorder', array('fid' => $fid));
	//If field type is fileset create a fieldset end field </fieldset> and edit it
	if($fieldType == 53){
		$createFieldSetEnd = ModUtil::apiFunc('IWforms', 'admin', 'createFormFieldSetEnd',
                                           array('fid' => $fid,
												 'dependance' => $createField,
												 'fieldName' => __('Final box', $dom)));
	}
	$content = ModUtil::func('IWforms', 'admin', 'editField',
                          array('fid' => $fid,
								'fndid' => $createField));
	AjaxUtil::output(array('fid' => $fid,
						   'content' => $content));
}

function IWforms_ajax_newField($args){
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$content = ModUtil::func('IWforms', 'admin', 'createField');
	AjaxUtil::output(array('fid' => $fid,
						   'content' => $content));
}

function IWforms_ajax_actionToDo($args){
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$action = FormUtil::getPassedValue('action', -1, 'GET');
	if ($actiom == -1) {
		AjaxUtil::error('no action defined id');
	}
	switch($action){
		case 'validators':
			$content = ModUtil::func('IWforms', 'admin', 'validators',
                                  array('fid' => $fid));
			$tabContent = ModUtil::func('IWforms', 'admin', 'minitab',
                                     array('tab' => 4));
			break;
		case 'group':
			$content = ModUtil::func('IWforms', 'admin', 'groups', array('fid' => $fid));
			$tabContent = ModUtil::func('IWforms', 'admin', 'minitab',
                                     array('tab' => 3));
			break;
		case 'field':
			$content = ModUtil::func('IWforms', 'admin', 'field',
                                  array('fid' => $fid));
			$tabContent = ModUtil::func('IWforms', 'admin', 'minitab',
                                     array('tab' => 2));
			break;
		case 'edit':
			$content = ModUtil::func('IWforms', 'admin', 'edit', array('fid' => $fid));
			$tabContent = ModUtil::func('IWforms', 'admin', 'minitab',
                                     array('tab' => 1));
			break;
	}
	AjaxUtil::output(array('content' => $content,
						   'tabContent' => $tabContent));
}

/**
 * Change the characteristics of a expert mode edition view
 * @author:  Albert Pérez Monfort (aperezm@xtec.cat)
 * @param:	args   identity of the form
 * @return:	 The expert mode content
*/
function IWforms_ajax_expertModeActivation($args)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_ADMIN)) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Sorry! No authorization to access this module.', $dom)));
	}
	$fid = FormUtil::getPassedValue('fid', -1, 'GET');
	if ($fid == -1) {
		AjaxUtil::error('no form id');
	}
	$expertMode = FormUtil::getPassedValue('expertMode', -1, 'GET');
	$skinByTemplate = FormUtil::getPassedValue('skinByTemplate', -1, 'GET');
	//Get field information
	$form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
						  array('fid' => $fid));
	if ($form == false) {
		AjaxUtil::error(DataUtil::formatForDisplayHTML(__('Could not find form', $dom)));
	}
	$form['expertMode'] = $expertMode;
	$form['skinByTemplate'] = $skinByTemplate;
	$view = Zikula_View::getInstance('IWforms',false);
	$view->assign('item', $form);
	$content = $view->fetch('IWforms_admin_form_definitionExpertMode.htm');
	
	//$content = 'tt';
	AjaxUtil::output(array('content' => $content));
}
