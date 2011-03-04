<?php
class IWforms_Controller_User extends Zikula_Controller {

    /**
     * show the forms where the user can access
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The list of forms available
     */
    public function main() {
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //get all the active forms
        $forms = ModUtil::apiFunc('IWforms', 'user', 'getAllForms',
                        array('user' => 1));
        $uid = UserUtil::getVar('uid');
        //get all the groups of the user
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $userGroups = ModUtil::func('IWmain', 'user', 'getAllUserGroups',
                        array('uid' => $uid,
                            'sv' => $sv));
        foreach ($userGroups as $group) {
            $userGroupsArray[] = $group['id'];
        }
        $flagged = ModUtil::apiFunc('IWforms', 'user', 'getWhereFlagged');
        $validation = ModUtil::apiFunc('IWforms', 'user', 'getWhereNeedValidation');
        //Filter the forms where the user can access
        foreach ($forms as $form) {
            $access = ModUtil::func('IWforms', 'user', 'access',
                            array('fid' => $form['fid'],
                                'userGroups' => $userGroupsArray));
            if ($access['level'] != 0) {
                $isFlagged = (array_key_exists($form['fid'], $flagged)) ? 1 : 0;
                $needValidation = (array_key_exists($form['fid'], $validation)) ? 1 : 0;
                $new = ModUtil::func('IWforms', 'user', 'makeTimeForm', $form['new']);
                $new = mktime(23, 59, 00, substr($new, 3, 2), substr($new, 0, 2), substr($new, 6, 2));
                $newLabel = ($new > time()) ? true : false;
                //Whit all the information create the array to output
                $forms_array[] = array('formName' => $form['formName'],
                    'title' => $form['title'],
                    'accessLevel' => $access['level'],
                    'closeableInsert' => $form['closeableInsert'],
                    'isFlagged' => $isFlagged,
                    'needValidation' => $needValidation,
                    'closeInsert' => $form['closeInsert'],
                    'newLabel' => $newLabel,
                    'defaultValidation' => $access['defaultValidation'],
                    'fid' => $form['fid']);
            }
        }
        // Create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $view->assign('forms', $forms_array);
        $view->assign('func', '');
        $view->assign('fid', '');
        return $view->fetch('IWforms_user_main.htm');
    }

    /**
     * get the access level form an user in a forms
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The form identity and the user groups
     * @return:	An array with the access level for the user into the form and validation conditions
     * 		0 - no access
     * 		1 - write
     * 		2 - read
     * 		3 - read and write
     * 		4 - fiels validator. Can read and write too
     * 		7 - Hight validator
     * 		Example: array('level' => 7, 'defaultValidation' => 1)
     */
    public function access($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'POST');
        $userGroups = FormUtil::getPassedValue('userGroups', isset($args['userGroups']) ? $args['userGroups'] : null, 'POST');
        $uid = FormUtil::getPassedValue('uid', isset($args['uid']) ? $args['uid'] : UserUtil::getVar('uid'), 'POST');
        $sv = FormUtil::getPassedValue('sv', isset($args['sv']) ? $args['sv'] : null, 'POST');
        if (!ModUtil::func('IWmain', 'user', 'checkSecurityValue',
                        array('sv' => $sv))) {
            // Security check
            if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
                return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
            }
        } else {
            $requestByCron = true;
        }
        //check if the form exists
        //Get item
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $item = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid,
                            'sv' => $sv));
        if ($item == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        //if form is not actived there is not access
        if ($item['active'] != 1) {
            return array('level' => 0,
                'defaultValidation' => 0);
        }
        $formGroupsArray = array();
        $uid = (!UserUtil::isLoggedIn() && !$requestByCron) ? '-1' : $uid;
        if ($uid != '-1') {
            if ($uid != UserUtil::getVar('uid') && !$requestByCron) {
                return 0;
            }
        }
        //in case the array of groups of the users is not isset
        if ($userGroups == null) {
            //get all the groups of the user
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $userGroups = ModUtil::func('IWmain', 'user', 'getAllUserGroups',
                            array('uid' => $uid,
                                'sv' => $sv));
            foreach ($userGroups as $group) {
                $userGroups[] = $group['id'];
            }
        }
        if ($uid > 0) {
            //check if the user is form validator
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $validator = ModUtil::apiFunc('IWforms', 'user', 'isValidator',
                            array('fid' => $fid,
                                'uid' => $uid,
                                'sv' => $sv));
            if ($validator != 0) {
                $return = ($validator == 2) ? 7 : 4;
                return array('level' => $return,
                    'defaultValidation' => 1);
            }
        } else {
            $userGroups[] = 0;
        }
        //get all the form groups
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $formGroups = ModUtil::apiFunc('IWforms', 'user', 'getAllGroups',
                        array('fid' => $fid,
                            'sv' => $sv));
        foreach ($formGroups as $group) {
            $formGroupsArray[] = $group['group'];
        }
        $accessAsGroup = array_intersect($userGroups, $formGroupsArray);
        $accessGroupsString = "$";
        foreach ($accessAsGroup as $accessGroup) {
            $accessGroupsString .= "$" . $accessGroup . "$";
        }
        if (empty($accessAsGroup)) {
            return array('level' => 0,
                'defaultValidation' => 0);
        } else {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $userAccess = ModUtil::apiFunc('IWforms', 'user', 'getGroupsUserAccess',
                            array('fid' => $fid,
                                'accessGroupsString' => $accessGroupsString,
                                'sv' => $sv));
        }
        $defaultValidation = ($userAccess['validationNeeded'] == 1) ? 0 : 1;
        return array('level' => $userAccess['accessType'],
            'defaultValidation' => $defaultValidation);
    }

    /**
     * access to see the notes sended in a form
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	id of the form to show
     * @return:	The list of notes of the form
     */
    public function read($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'GET');
        $ipp = FormUtil::getPassedValue('ipp', isset($args['ipp']) ? $args['ipp'] : 10, 'REQUEST');
        $init = FormUtil::getPassedValue('init', isset($args['init']) ? $args['init'] : 0, 'REQUEST');
        $fidReload = FormUtil::getPassedValue('fidReload', isset($args['fidReload']) ? $args['fidReload'] : null, 'POST');
        $fmid = FormUtil::getPassedValue('fmid', isset($args['fmid']) ? $args['fmid'] : null, 'GET');
        if ($fidReload != null) {
            $fid = $fidReload;
        }
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] < 2) {
            LogUtil::registerError($this->__('You can not access this form to view the annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        //Get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        // get user identity
        $uid = UserUtil::getVar('uid');
        if ($uid == '') {
            $uid = '-1';
        }
        $usersList = '$$'; //I have written it in case the form is annonimous. In this case the var would be empty and I want to avoid it
        $validate = ($access['level'] == 7) ? 0 : 1;
        if ($fmid != null) {
            $fmidArray[] = $fmid;
            $oneRecord = true;
            // get all form notes written in the array $fmidArray
            $notes = ModUtil::apiFunc('IWforms', 'user', 'getAllNotes',
                            array('fid' => $fid,
                                'ipp' => 1,
                                'init' => 0,
                                'fmid' => $fmidArray,
                                'validate' => $validate));
        } else {
            //get all form notes
            $notes = ModUtil::apiFunc('IWforms', 'user', 'getAllNotes',
                            array('fid' => $fid,
                                'ipp' => $ipp,
                                'init' => $init,
                                'validate' => $validate));
        }
        // set the default template
        $template = 'IWforms_user_read.htm';
        if ($form['expertMode'] && $form['skinByTemplate'] == 1) {
            if ($form['skinTemplate'] != '' && $fmid == null)
                $template = $form['skinTemplate'];
            if ($form['skinNoteTemplate'] != '' && $fmid != null)
                $template = $form['skinNoteTemplate'];
        }
        foreach ($notes as $note) {
            $noteContent = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                            array('fid' => $fid,
                                'fmid' => $note['fmid']));
            if ($note['annonimous'] == 0 && ($uid != '-1' || ($uid == '-1' && $form['unregisterednotusersview'] == 0))) {
                $userName = UserUtil::getVar('uname', $note['user']);
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $photo = ModUtil::func('IWmain', 'user', 'getUserPicture',
                                array('uname' => $userName,
                                    'sv' => $sv));
                $usersList .= $note['user'] . '$$';
                $user = ($note['user'] != '') ? $note['user'] : '-1';
            } else {
                $user = '';
                $userName = '';
                $photo = '';
            }
            if (strpos($note['viewed'], '$' . $uid . '|') == 0) {
                // set the note as viewed by user
                ModUtil::apiFunc('IWforms', 'user', 'setAsViewed',
                                array('fid' => $fid,
                                    'fmid' => $note['fmid'],
                                    'value' => $note['viewed']));
            }
            $color = (strpos($note['viewed'], '$' . UserUtil::getVar('uid') . '|') == 0) ? ModUtil::getVar('IWforms', 'newsColor') : ModUtil::getVar('IWforms', 'viewedColor');
            if ($form['expertMode'] && $form['skinByTemplate'] == 0) {
                $contentBySkin = '';
                if ($form['skin'] != '' && $fmid == null)
                    $contentBySkin = $form['skin'];
                if ($form['skinNote'] != '' && $fmid != null)
                    $contentBySkin = $form['skinNote'];
                if ($contentBySkin != '') {
                    $contentBySkin = str_replace('[$user$]', $userName, $contentBySkin);
                    $contentBySkin = str_replace('[$time$]', date('H.i', $note['time']), $contentBySkin);
                    $contentBySkin = str_replace('[$date$]', date('d/m/Y', $note['time']), $contentBySkin);
                    $contentBySkin = str_replace('[$noteId$]', $note['fmid'], $contentBySkin);
                    $contentBySkin = str_replace('[$formId$]', $fid, $contentBySkin);
                    if ($photo != '') {
                        $photo = '<img src="' . System::getBaseUrl() . 'index.php?module=IWforms&func=getFile&fileName=' . $photo . '" />';
                    }
                    $contentBySkin = str_replace('[$avatar$]', $photo, $contentBySkin);
                    foreach ($noteContent as $key => $value) {
                        $contentBySkin = str_replace('[$' . $key . '$]', nl2br($value['content']), $contentBySkin);
                        $contentBySkin = str_replace('[%' . $key . '%]', $value['fieldName'], $contentBySkin);
                    }
                    $contentBySkin = ($note['publicResponse']) ? str_replace('[$reply$]', $note['renote'], $contentBySkin) : str_replace('[$reply$]', '', $contentBySkin);
                    $contentBySkin = str_replace('|per|', '%', $contentBySkin);
                    $contentBySkin = str_replace('|par|', '#', $contentBySkin);
                    $contentBySkin = str_replace('|int|', '?', $contentBySkin);
                    $contentBySkin = str_replace('|amp|', '&', $contentBySkin);
                }
            }
            $notesArray[] = array('user' => $user,
                'userName' => $userName,
                'fmid' => $note['fmid'],
                'day' => date('d/m/Y', $note['time']),
                'time' => date('H.i', $note['time']),
                'renote' => $note['renote'],
                'publicResponse' => $note['publicResponse'],
                'photo' => $photo,
                'color' => $color,
                'content' => $noteContent,
                'contentBySkin' => $contentBySkin);
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $users = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'ccn',
                            'sv' => $sv,
                            'list' => $usersList));
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        ModUtil::func('IWmain', 'user', 'userSetVar',
                        array('module' => 'IWmain_block_news',
                            'name' => 'have_news',
                            'value' => 'fu',
                            'sv' => $sv));
        //get all notes
        $total = ModUtil::apiFunc('IWforms', 'user', 'getTotalNotes',
                        array('fid' => $fid,
                            'validate' => 1));
        $pager = ModUtil::func('IWforms', 'user', 'pager',
                        array('init' => $init,
                            'ipp' => $ipp,
                            'total' => $total,
                            'urltemplate' => 'index.php?module=IWforms&func=read&init=%%&ipp=' . $ipp . '&fid=' . $fid));
        if ($form['skincss'] != '' &&
                ($form['skin'] != '' || $form['skinNote'] != '') &&
                $form['expertMode'] == 1 &&
                $form['skinByTemplate'] == 0) {
            $form['skincssurl'] = '<link rel="stylesheet" href="' . $form['skincss'] . '" type="text/css" />';
        }
        // Create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $view->assign('notes', $notesArray);
        $view->assign('pager', $pager);
        $view->assign('ipp', $ipp);
        $view->assign('order', $order);
        $view->assign('users', $users);
        $view->assign('form', $form);
        $view->assign('oneRecord', $oneRecord);
        $view->assign('fid', $fid);
        $view->assign('fieldsColor', ModUtil::getVar('IWforms', 'fieldsColor'));
        $view->assign('contentColor', ModUtil::getVar('IWforms', 'contentColor'));
        $view->assign('newsColor', ModUtil::getVar('IWforms', 'newsColor'));
        $view->assign('viewedColor', ModUtil::getVar('IWforms', 'viewedColor'));
        $view->assign('completedColor', ModUtil::getVar('IWforms', 'completedColor'));
        $view->assign('validatedColor', ModUtil::getVar('IWforms', 'validatedColor'));
        return $view->fetch($template);
    }

    /**
     * access to manage the notes sended in a form
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	id of the form to manage
     * @return:	The list of notes of the form with the managment options
     */
    public function manage($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'REQUEST');
        $order = FormUtil::getPassedValue('order', isset($args['order']) ? $args['order'] : null, 'REQUEST');
        $ipp = FormUtil::getPassedValue('ipp', isset($args['ipp']) ? $args['ipp'] : 10, 'REQUEST');
        $init = FormUtil::getPassedValue('init', isset($args['init']) ? $args['init'] : 0, 'REQUEST');
        $fmid = FormUtil::getPassedValue('fmid', isset($args['fmid']) ? $args['fmid'] : null, 'REQUEST');
        $filterValue = FormUtil::getPassedValue('filterValue', isset($args['filterValue']) ? $args['filterValue'] : null, 'REQUEST');
        $filter = FormUtil::getPassedValue('filter', isset($args['filter']) ? $args['filter'] : 0, 'REQUEST');
        $fidReload = FormUtil::getPassedValue('fidReload', isset($args['fidReload']) ? $args['fidReload'] : null, 'POST');
        if ($fidReload != null) {
            $fid = $fidReload;
        }

        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] < 7) {
            LogUtil::registerError($this->__('You can not access this form to view the annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        if ($order == null) {
            $order = "";
        }
        // get user identity
        $uid = UserUtil::getVar('uid');
        if ($uid == '') {
            $uid = '-1';
        }
        //Get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        //get all people who have sended a note
        $AllNotes = ModUtil::apiFunc('IWforms', 'user', 'getAllUsersNotes',
                        array('fid' => $fid,
                            'ipp' => 1000000,
                            'init' => 0));
        $usersList = '';
        $users= array();
        foreach ($AllNotes as $note) {
            $usersList .= $note['user'] . '$$';
        }
        $fmidArray = array();
        $fmidNotes = array();
        $filters = array();
        $notesArray = array();
        $items = array();
        $notes = array();
        if ($fmid != null) {
            $fmidArray[] = $fmid;
            $records = 1;
        }
        if ($filter != 0 && $filterValue == '')
            $filter = 0;

        if ($filter != 0) {
            //get the field properties
            $field = ModUtil::apiFunc('IWforms', 'user', 'getFormField',
                            array('fndid' => $filter));
            $fmidNotes = ModUtil::apiFunc('IWforms', 'user', 'getAllNotesFilter',
                            array('fid' => $fid,
                                'order' => $order,
                                'filter' => $filter,
                                'fieldType' => $field['fieldType'],
                                'filterValue' => $filterValue));
        }
        foreach ($fmidNotes as $f) {
            $fmidArray[] = $f['fmid'];
        }
        if ($filter == 0 || ($filter != 0 && $filterValue != '' && $filterValue != '0' && count($fmidArray) > 0)) {
            //get all form notes
            $notes = ModUtil::apiFunc('IWforms', 'user', 'getAllNotes',
                            array('fid' => $fid,
                                'order' => $order,
                                'ipp' => $ipp,
                                'init' => $init,
                                'fmid' => $fmidArray,
                                'filter' => $filter,
                                'filterValue' => $filterValue));
        }
        //get form fields
        $fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                        array('fid' => $fid,
                            'whereArray' => 'active|1'));
        foreach ($fields as $field) {
            if ($field['fieldType'] < 10) {
                $fieldsIdsArray[] = $field['fndid'];
            }
        }
        foreach ($notes as $note) {
            $noteContent = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                            array('fid' => $fid,
                                'fmid' => $note['fmid']));
            if ($note['annonimous'] == 0) {
                $userName = UserUtil::getVar('uname', $note['user']);
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $photo = ModUtil::func('IWmain', 'user', 'getUserPicture',
                                array('uname' => $userName,
                                    'sv' => $sv));
                $user = ($note['user'] != '') ? $note['user'] : '-1';
            } else {
                $user = '';
                $userName = '';
                $photo = '';
            }
            if (strpos($note['viewed'], '$' . $uid . '|') == 0) {
                // set the note as viewed by user
                ModUtil::apiFunc('IWforms', 'user', 'setAsViewed',
                                array('fid' => $fid,
                                    'fmid' => $note['fmid'],
                                    'value' => $note['viewed']));
            }
            $fieldsIdsNoteArray = array();
            foreach ($noteContent as $noteContentId) {
                $fieldsIdsNoteArray[] = $noteContentId['fndid'];
            }
            $synchronize = (count(array_intersect($fieldsIdsNoteArray, $fieldsIdsArray)) == count($fieldsIdsArray) && count(array_intersect($fieldsIdsNoteArray, $fieldsIdsArray)) == count($fieldsIdsNoteArray)) ? false : true;
            $marked = (strpos($note['mark'], '$' . $uid . '$') != 0) ? 1 : 0;
            $notesArray[] = array('user' => $user,
                'userName' => $userName,
                'fmid' => $note['fmid'],
                'validate' => $note['validate'],
                'state' => $note['state'],
                'observations' => $note['observations'],
                'renote' => $note['renote'],
                'publicResponse' => $note['publicResponse'],
                'day' => date('d/m/Y', $note['time']),
                'time' => date('H.i', $note['time']),
                'photo' => $photo,
                'marked' => $marked,
                'synchronize' => $synchronize,
                'color' => ModUtil::func('IWforms', 'user', 'calcColor',
                        array('validate' => $note['validate'],
                            'state' => $note['state'],
                            'viewed' => $note['viewed'])),
                'content' => $noteContent);
        }
        if ($usersList != '') {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $users = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                            array('info' => 'ccn',
                                'sv' => $sv,
                                'list' => $usersList));
            asort($users);
        }
        $filterType = 0;
        if (count($users) > 1) {
            $filters[] = array('id' => 0,
                'name' => $this->__('user'));
            if ($filter == 0) {
                $items = $users;
            }
        }
        //get form fields
        $fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                        array('fid' => $fid,
                            'whereArray' => 'active|1$$searchable|1'));
        foreach ($fields as $field) {
            $name = substr($field['fieldName'], 0, 15);
            if (strlen($field['fieldName']) > 15) {
                $name .= '...';
            }
            $filters[] = array('id' => $field['fndid'],
                'name' => $name);
            if ($filter == $field['fndid'] || count($filters) == 1) {
                switch ($field['fieldType']) {
                    case 6:
                        $options = explode('-', $field['options']);
                        $optionsArray = array();
                        foreach ($options as $option) {
                            $optionsArray[$option] = $option;
                        }
                        if ($field['gid'] > 0) {
                            $members = array();
                            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                            $members = ModUtil::func('IWmain', 'user', 'getMembersGroup',
                                            array('sv' => $sv,
                                                'gid' => $field['gid'],
                                                'onlyId' => 1));
                            if (count($members) > 0) {
                                $usersList = '$$';
                                foreach ($members as $member) {
                                    $usersList .= $member['id'] . '$$';
                                }
                                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                                $users1 = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                                                array('info' => 'ccn',
                                                    'sv' => $sv,
                                                    'list' => $usersList));
                                asort($users1);
                                foreach ($users1 as $user) {
                                    $optionsArray[$user] = $user;
                                }
                            }
                        }
                        $items = $optionsArray;
                        break;
                    case 8:
                        $optionsArray[$this->__('No')] = $this->__('No');
                        $optionsArray[$this->__('Yes')] = $this->__('Yes');
                        $items = $optionsArray;
                        break;
                    default:
                        $filterType = 1;
                        $filterValue = $filterValue;
                }
            }
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        ModUtil::func('IWmain', 'user', 'userSetVar',
                        array('module' => 'IWmain_block_news',
                            'name' => 'have_news',
                            'value' => 'fu',
                            'sv' => $sv));
        //get all notes
        $total = ModUtil::apiFunc('IWforms', 'user', 'getTotalNotes',
                        array('fid' => $fid,
                            'filter' => $filter,
                            'fmid' => $fmidArray,
                            'filterValue' => $filterValue));
        $records = 0;
        if ($total > $ipp) {
            $records = $ipp;
        }
        $ippPreArray = array(10, 20, 30, 50, 100);
        $ippArray = array();
        $j = 0;
        for ($i = 0; $i < 5; $i++) {
            if ($ippPreArray[$i] < $total) {
                $ippArray[] = $ippPreArray[$i];
                $j = $i;
            }
        }
        $ippArray[] = $ippPreArray[$j + 1];
        $pager = ModUtil::func('IWforms', 'user', 'pager',
                        array('init' => $init,
                            'ipp' => $ipp,
                            'total' => $total,
                            'urltemplate' => 'index.php?module=IWforms&func=manage&init=%%&ipp=' . $ipp . '&fid=' . $fid . '&order=' . $order . '&filterValue=' . $filterValue . '&filter=' . $filter));
        // Create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $view->assign('filters', $filters);
        $view->assign('pager', $pager);
        $view->assign('notes', $notesArray);
        $view->assign('ipp', $ipp);
        $view->assign('init', $init);
        $view->assign('ippArray', $ippArray);
        $view->assign('order', $order);
        $view->assign('filterType', $filterType);
        $view->assign('items', $items);
        $view->assign('users', $users);
        $view->assign('filterValue', $filterValue);
        $view->assign('filter', $filter);
        $view->assign('form', $form);
        $view->assign('records', $records);
        $view->assign('func', '');
        $view->assign('fid', $fid);
        $view->assign('total', $total);
        $view->assign('fieldsColor', ModUtil::getVar('IWforms', 'fieldsColor'));
        $view->assign('contentColor', ModUtil::getVar('IWforms', 'contentColor'));
        $view->assign('newsColor', ModUtil::getVar('IWforms', 'newsColor'));
        $view->assign('viewedColor', ModUtil::getVar('IWforms', 'viewedColor'));
        $view->assign('completedColor', ModUtil::getVar('IWforms', 'completedColor'));
        $view->assign('validatedColor', ModUtil::getVar('IWforms', 'validatedColor'));
        return $view->fetch('IWforms_user_manage.htm');
    }

    /**
     * synchronize the fields of a note creating the fields that do not exists and
     * 	deleting the fields that have been removed from the form definition
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	id of the form to manage
     * @return: True if success and false otherwise
     */
    public function synchro($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'REQUEST');
        $order = FormUtil::getPassedValue('order', isset($args['order']) ? $args['order'] : null, 'REQUEST');
        $ipp = FormUtil::getPassedValue('ipp', isset($args['ipp']) ? $args['ipp'] : 10, 'REQUEST');
        $init = FormUtil::getPassedValue('init', isset($args['init']) ? $args['init'] : 0, 'REQUEST');
        $fmid = FormUtil::getPassedValue('fmid', isset($args['fmid']) ? $args['fmid'] : null, 'REQUEST');
        $filterValue = FormUtil::getPassedValue('filterValue', isset($args['filterValue']) ? $args['filterValue'] : null, 'REQUEST');
        $filter = FormUtil::getPassedValue('filter', isset($args['filter']) ? $args['filter'] : 0, 'REQUEST');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] < 7) {
            LogUtil::registerError($this->__('You can not access this form to view the annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        $syncronized = false;
        // delete the fields that aren't necessary
        if (ModUtil::apiFunc('IWforms', 'user', 'fieldsToDelete',
                        array('fid' => $fid,
                            'fmid' => $fmid)))
            $syncronized = true;
        // create the needed fields
        if (ModUtil::apiFunc('IWforms', 'user', 'fieldsToCreate',
                        array('fid' => $fid,
                            'fmid' => $fmid)))
            $syncronized = true;

        if ($syncronized) {
            LogUtil::registerStatus($this->__('Field synchronized successfully.'));
        }
        //Successfull
        return System::redirect(ModUtil::url('IWforms', 'user', 'manage',
                        array('fid' => $fid,
                            'order' => $order,
                            'ipp' => $ipp,
                            'init' => $init,
                            'filterValue' => $filterValue,
                            'filter' => $filter)));
    }

    /**
     * access to manage the notes sended in a form
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	id of the form to manage
     * @return:	The list of notes of the form with the managment options
     */
    public function load($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'REQUEST');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] == 7) {
            return System::redirect(ModUtil::url('IWforms', 'user', 'manage',
                            array('fid' => $fid)));
        }
        if ($access['level'] == 1 || $access['level'] > 2) {
            return System::redirect(ModUtil::url('IWforms', 'user', 'newitem',
                            array('fid' => $fid)));
        }
        LogUtil::registerError($this->__('You can not access this form to view the annotations'));
        return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
    }

    /**
     * access to messages sended by user
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @return:	The list of notes of the form with the managment options
     */
    public function sended($args) {
        $fmid = FormUtil::getPassedValue('fmid', isset($args['fmid']) ? $args['fmid'] : 0, 'GETPOST');
        $fid = FormUtil::getPassedValue('form_id', isset($args['form_id']) ? $args['form_id'] : 0, 'GETPOST');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) || !UserUtil::isLoggedIn()) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        if ($fmid != 0) {
            $note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                            array('fmid' => $fmid));
            $fid = $note['fid'];
        }
        $content = '';
        // Create output object
        $view = Zikula_View::getInstance('IWforms', false);
        if ($fid == 0) {
            //get all the active forms
            $forms = ModUtil::apiFunc('IWforms', 'user', 'getAllForms',
                            array('user' => 1));
            foreach ($forms as $form) {
                $notesArray = array();
                //check user access to this form
                $access = ModUtil::func('IWforms', 'user', 'access',
                                array('fid' => $form['fid']));
                if ($access['level'] > 0) {
                    $formsArray[] = array('formName' => $form['formName'],
                        'fid' => $form['fid']);
                }
            }
            $view->assign('formsArray', $formsArray);
            $view->assign('fid', '');
            return $view->fetch('IWforms_user_sendedWhatForm.htm');
        }
        //Get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        $notesArray = array();
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] > 0) {
            //get all form notes for the user
            $notes = ModUtil::apiFunc('IWforms', 'user', 'getAllNotes',
                            array('fid' => $fid,
                                'userNotes' => 1));
            $validators = ModUtil::apiFunc('IWforms', 'user', 'getAllValidators',
                            array('fid' => $fid));
            $usersList = '$';
            foreach ($notes as $note) {
                $noteContent = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                                array('fid' => $fid,
                                    'fmid' => $note['fmid']));
                $viewedArray = array();
                foreach ($validators as $validator) {
                    if ($validator['validatorType'] == 0 && strpos($note['viewed'], $validator['validator'] . '|') !== false) {
                        $time = substr($note['viewed'], strpos($note['viewed'], $validator['validator'] . '|') + strlen($validator['validator']) + 1, 18);
                        $viewedArray[] = array('user' => $validator['validator'],
                            'time' => $time);
                        $usersList .= $validator['validator'] . '$$';
                    }
                }
                $notesArray[] = array('validate' => $note['validate'],
                    'fmid' => $note['fmid'],
                    'renote' => $note['renote'],
                    'viewed' => $viewedArray,
                    'day' => date('d/m/Y', $note['time']),
                    'time' => date('H.i', $note['time']),
                    'content' => $noteContent);
            }
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $users = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                            array('info' => 'ccn',
                                'sv' => $sv,
                                'list' => $usersList));
            $view->assign('users', $users);
            $view->assign('fid', $fid);
            $view->assign('notes', $notesArray);
            $view->assign('form', $form);
            $view->assign('fieldsColor', ModUtil::getVar('IWforms', 'fieldsColor'));
            $view->assign('contentColor', ModUtil::getVar('IWforms', 'contentColor'));
            $view->assign('color', ModUtil::getVar('IWforms', 'viewedColor'));
            $content .= $view->fetch('IWforms_user_sended.htm');
        }
        $view->assign('content', $content);
        return $view->fetch('IWforms_user_sendedView.htm');
    }

    /**
     * Get a file from a server folder even it is out of the public html directory
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	name of the file that have to be gotten
     * @return:	The file information
     */
    public function getFile($args) {
        // File name with the path
        $fileName = FormUtil::getPassedValue('fileName', isset($args['fileName']) ? $args['fileName'] : 0, 'GET');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        return ModUtil::func('IWmain', 'user', 'getFile',
                array('fileName' => $fileName,
                    'sv' => $sv));
    }

    /**
     * Calc the necessari colour for a note
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The note information
     * @return:	The color for the note
     */
    public function calcColor($args) {
        $validate = FormUtil::getPassedValue('validate', isset($args['validate']) ? $args['validate'] : null, 'POST');
        $state = FormUtil::getPassedValue('state', isset($args['state']) ? $args['state'] : null, 'POST');
        $viewed = FormUtil::getPassedValue('viewed', isset($args['viewed']) ? $args['viewed'] : null, 'POST');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        $color = ModUtil::getVar('IWforms', 'viewedColor');
        if (strpos($viewed, '$' . UserUtil::getVar('uid') . '|') == 0) {
            $color = ModUtil::getVar('IWforms', 'newsColor');
        }
        if ($validate == 0) {
            $color = ModUtil::getVar('IWforms', 'validatedColor');
        }
        if ($state == 1) {
            $color = ModUtil::getVar('IWforms', 'completedColor');
        }
        return $color;
    }

    /**
     * allow to add a new note
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	id of the form where to add a new note
     * @return:	The fields of the form
     */
    public function newitem($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'GET');
        $fmid = FormUtil::getPassedValue('fmid', isset($args['fmid']) ? $args['fmid'] : null, 'POST');
        $adminView = FormUtil::getPassedValue('adminView', isset($args['adminView']) ? $args['adminView'] : null, 'GET');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
//        ModUtil::callHooks('item', 'display');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_ADMIN)) {
            $adminView = null;
        }
        $uid = UserUtil::getVar('uid');
        if ($uid == '') {
            $uid = -1;
        }
        $content = '';
        $requiredJS = '';
        $checkJS = '';
        //Get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] != 1 && $access['level'] < 3 && $adminView == null) {
            if (!UserUtil::login()) {
                return System::redirect('user.php');
            } else {
                LogUtil::registerError($this->__('You can not access this form to send annotations'));
                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
            }
        }
        //If form is closed it is not possible to add new notes
        if ($form['closeInsert'] == 1 && $adminView == null) {
            LogUtil::registerError($this->__('You can not access this form to send annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        //if form is for only one reply users can send more that a note
        if ($form['unique'] == 1 && $adminView == null) {
            //check if user has sended a note in this form
            if (ModUtil::apiFunc('IWforms', 'user', 'sended',
                            array('fid' => $fid))) {
                LogUtil::registerError($this->__('The form is only one answer and know that you\'ve already sent an annotation.'));
                // Redirect to the main site for the user
                return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
            }
        }
        //get form fields
        $fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                        array('fid' => $fid,
                            'whereArray' => 'active|1'));
        if ($fields == false) {
            LogUtil::registerError($this->__('Has not been found fields in the form'));
            if ($adminView == null) {
                return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
            } else {
                return System::redirect(ModUtil::url('IWforms', 'admin', 'main'));
            }
        }
        $noteContentIdArray = array();
        $noteValidationArray = array();
        if ($fmid != null && $fmid != 0) {
            // get all note contents
            $noteContents = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                            array('fid' => $fid,
                                'fmid' => $fmid));
            $note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                            array('fid' => $fid,
                                'fmid' => $fmid));
            // create an array with the note contents Id
            // and another array with $noteId => validation state
            foreach ($noteContents as $noteContent) {
                array_push($noteContentIdArray, $noteContent['fndid']);
                $noteValidationArray[] = array($noteContent['fndid'] => $noteContent['validate']);
            }
        }
        $template = 'IWforms_user_new.htm';
        if ($form['skinForm'] != '' && $form['skinByTemplate'] == 0 && $form['expertMode'] == 1) {
            $content = DataUtil::formatForDisplayHTML($form['skinForm']);
        }
        // create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $requiredText = false;
        foreach ($fields as $field) {
            if (($form['skinForm'] == '' && $form['skinByTemplate'] == 0) ||
                    ($form['expertMode'] == '1' && $form['skinByTemplate'] == 1) ||
                    $form['expertMode'] == 0) {
                $contentArray = ModUtil::func('IWforms', 'user', 'newFieldContent',
                                array('fndid' => $field['fndid'],
                                    'fid' => $field['fid'],
                                    'adminView' => $adminView));
                $content .= $contentArray['content'];
                $requiredJS .= $contentArray['requiredJS'];
                $checkJS .= $contentArray['checkJS'];
                if ($form['skinByTemplate'] == 1 && $form['skinFormTemplate'] != '')
                    $template = $form['skinFormTemplate'];
                if ($contentArray['requiredText'])
                    $requiredText = true;
            } else {
                $contentArray = ModUtil::func('IWforms', 'user', 'newFieldContent',
                                array('fndid' => $field['fndid'],
                                    'fid' => $field['fid'],
                                    'adminView' => $adminView));
                $content = str_replace("[$" . $field['fndid'] . "$]", $contentArray['content'], $content);
                $requiredJS .= $contentArray['requiredJS'];
                $checkJS .= $contentArray['checkJS'];
            }
        }
        $captcha = false;
        if ($uid == '-1') {
            $captcha = true;
        }
        if ($form['skincss'] != '' && $form['skinForm'] != '' && $form['expertMode'] == 1 && $form['skinByTemplate'] == 0) {
            $form['skincssurl'] = '<link rel="stylesheet" href="' . $form['skincss'] . '" type="text/css" />';
        }
        $view->assign('requiredText', $requiredText);
        $view->assign('adminView', $adminView);
        $view->assign('form', $form);
        $view->assign('checkJS', $checkJS);
        $view->assign('requiredJS', $requiredJS);
        $view->assign('content', $content);
        $view->assign('func', '');
        $view->assign('fid', $fid);
        $view->assign('captcha', $captcha);
        return $view->fetch($template);
    }

    /**
     * get the content of a note for the form
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	id of the form where to add a new note and id of the note
     * @return:	The field contents
     */
    public function newFieldContent($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'POST');
        $fndid = FormUtil::getPassedValue('fndid', isset($args['fndid']) ? $args['fndid'] : null, 'POST');
        $adminView = FormUtil::getPassedValue('adminView', isset($args['adminView']) ? $args['adminView'] : null, 'POST');
        // security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        $uid = UserUtil::getVar('uid');
        if ($uid == '') {
            $uid = -1;
        }
        $noteContentIdArray = array();
        $fieldArray = array();
        $requiredText = false;
        $fieldContent = '';
        $checkJS = '';
        $requiredJS = '';
        // get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        // check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] != 1 && $access['level'] < 3 && $adminView == null) {
            LogUtil::registerError($this->__('You can not access this form to send annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        // get form field
        $field = ModUtil::apiFunc('IWforms', 'user', 'getFormField',
                        array('fndid' => $fndid));
        // create output object
        $view = Zikula_View::getInstance('IWforms', false);
        if ($field['fieldType'] == 1) {
            $view->assign('size', $field['size']);
        }
        if ($field['fieldType'] == 2) {
            $view->assign('cols', $field['cols']);
            $view->assign('rows', $field['rows']);
        }
        if ($field['fieldType'] == 4 && $field['calendar'] == 1) {
            $view->assign('calendar', $field['calendar']);
        }
        if ($field['fieldType'] == 4 && $field['calendar'] == 0) {
            //Create days array
            $days = array();
            for ($i = 1; $i < 32; $i++) {
                $value = $i;
                if (strlen($value) < 2) {
                    $value = '0' . $i;
                }
                $days[] = $value;
            }
            //Create months array
            $months = array();
            for ($i = 1; $i < 13; $i++) {
                $value = $i;
                if (strlen($value) < 2) {
                    $value = '0' . $i;
                }
                $months[] = $value;
            }
            //Create years array
            $years = array();
            $year = date('y', time());
            for ($i = $year; $i < $year + 5; $i++) {
                $iText = (strlen($i) == 1) ? '0' . $i : $i;
                $years[] = $iText;
            }
            $view->assign('days', $days);
            $view->assign('months', $months);
            $view->assign('years', $years);
            $view->assign('dayNow', date('d', time()));
            $view->assign('monthNow', date('m', time()));
            $view->assign('yearNow', date('y', time()));
        }
        if ($field['fieldType'] == 5) {
            //Create hours array
            $hours = array();
            for ($i = 0; $i < 24; $i++) {
                $h = $i;
                if ($i == 0) {
                    $h = '00';
                }
                if (strlen($i) == 1) {
                    $h = '0' . $i;
                }
                $hours[] = $h;
            }
            //Create minutes array
            $minutes = array();
            for ($i = 0; $i < 12; $i++) {
                $m = $i * 5;
                if ($i == 0) {
                    $m = '00';
                }
                if (strlen($m) == 1) {
                    $m = '0' . $m;
                }
                $minutes[] = $m;
            }
            $view->assign('hours', $hours);
            $view->assign('minutes', $minutes);
        }
        if ($field['fieldType'] == 6) {
            $options = explode('-', $field['options']);
            $optionsArray = array();
            foreach ($options as $option) {
                $optionsArray[] = $option;
            }
            if ($field['gid'] > 0) {
                $members = array();
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $members = ModUtil::func('IWmain', 'user', 'getMembersGroup',
                                array('sv' => $sv,
                                    'gid' => $field['gid'],
                                    'onlyId' => 1));
                if (count($members) > 0) {
                    $usersList = '$$';
                    foreach ($members as $member) {
                        $usersList .= $member['id'] . '$$';
                    }
                    $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                    $users = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                                    array('info' => 'ccn',
                                        'sv' => $sv,
                                        'list' => $usersList));
                    asort($users);
                    foreach ($users as $user) {
                        $optionsArray[] = $user;
                    }
                }
            }
            $view->assign('options', $optionsArray);
        }
        if ($field['fieldType'] == 8) {
            $view->assign('checked', $field['checked']);
        }
        if ($field['fieldType'] == 52) {
            $view->assign('height', $field['height']);
            $view->assign('color', $field['color']);
        }
        if ($field['fieldType'] == 53) {
            $view->assign('colorf', $field['colorf']);
        }
        //Check if field have the dependances solves. It means that the fields which the field is dependent
        //exists and trey are validated in case that the validation were needed
        $solvedDependences = true;
        if ($field['dependance'] != '$') {
            $dependances = explode('$$', substr($field['dependance'], 2, -1));
            foreach ($dependances as $dependance) {
                //Check if the field exists
                if (!in_array($dependance, $noteContentIdArray) ||
                        (isset($noteValidationArray[$dependance]) &&
                        $noteValidationArray[$dependance] == 0)) {
                    $solvedDependences = false;
                }
            }
        }
        //Check if the user can access as a validator
        $isValidator = ModUtil::apiFunc('IWforms', 'user', 'isValidator',
                        array('fid' => $fid));
        //if $isValidator == 2 can validate all the field otherwise it is necessari check if
        //the user can validate the field
        if ($isValidator == 2 || strpos($field['rfid'], '$' . $uid . '$') !== false) {
            //The user can validate the field
            if ($solvedDependences) {
                //Dependances solved
                //Check if field exists
                if (in_array($field['fndid'], $noteContentIdArray)) {
                    //Field exists
                    //Check if validation is needed and the field isn't validated yet
                    if ($field['validationNeeded'] && $noteValidationArray[$field['fndid']] == 1) {
                        //Validation is needed and field is not validated
                        $access = true;
                        $statusActive = true;
                    } else {
                        //Validation isn't needed or field is validated
                        //Check if the field is editable
                        if ($field['editable']) {
                            $access = true;
                            $statusActive = true;
                        } else {
                            $access = true;
                            $statusActive = false;
                        }
                    }
                } else {
                    //Don't exists
                    $access = true;
                    $statusActive = true;
                }
            } else {
                //Dependances not solved
                $access = true;
                $statusActive = false;
            }
        } else {
            //The user can't validate the field
            //Check if the field is only for validators
            if ($field['accessType'] == 2) {
                //The field is only for validators
                $access = false;
                $statusActive = false;
            } else {
                //The field is for all users
                //Check if the field is readtable only
                if ($field['accessType'] == 1) {
                    //users can only read
                    $access = true;
                    $statusActive = false;
                } else {
                    //Check if dependances are solved
                    if ($solvedDependences) {
                        //The dependances are solved
                        //Check if the form is new or the user is who have init the proces
                        if (!isset($note) || !empty($note) || $note['user'] == $uid) {
                            if (in_array($field['fndid'], $noteContentIdArray)) {
                                //Field exists
                                //Check if the field is editable
                                if ($field['editable']) {
                                    $access = true;
                                    $statusActive = true;
                                } else {
                                    $access = true;
                                    $statusActive = false;
                                }
                            } else {
                                //Don't exists
                                $access = true;
                                $statusActive = true;
                            }
                        } else {
                            $access = true;
                            $statusActive = false;
                        }
                    } else {
                        //Dependances aren't solved
                        $access = true;
                        $statusActive = false;
                    }
                }
            }
        }
        //in case the field is required and active create the JavaScript function needed
        $required = '';
        if ($field['required'] == 1 && $statusActive && $field['editor'] == 0) {
            $required = ' *';
            $requiredText = true;
            if ($field['calendar'] == 1) {
                $view->assign('calendar', true);
            }
            $view->assign('fieldType', $field['fieldType']);
            $view->assign('fndid', $field['fndid']);
            $requiredJS .= $view->fetch('IWforms_user_requiredJS.htm');
        }
        //Check some specific fields
        if ($statusActive && $fieldContent == 1) {
            $view->assign('fndid', $field['fndid']);
            $view->assign('fieldType', $field['fieldType']);
            $view->assign('extensions', ModUtil::getVar('IWmain', 'extensions'));
            $checkJS .= $view->fetch('IWforms_user_checkJS.htm');
        }
        $fieldsArray = array('fndid' => $field['fndid'],
            'fid' => $field['fid'],
            'required' => $required,
            'access' => $access,
            'statusActive' => $statusActive,
            'description' => $field['description'],
            'help' => $field['help'],
            'fieldName' => $field['fieldName'],
            'fieldNameShort' => (strlen($field['fieldName']) > 30) ? mb_strimwidth($field['fieldName'], 0, 30, '...') : $field['fieldName'],
            'feedback' => $field['feedback'],
            'publicFile' => $field['publicFile'],
            'editor' => $field['editor'],
            'fieldType' => $field['fieldType'],
            'extensions' => $field['extensions'],
            'imgWidth' => $field['imgWidth'],
            'imgHeight' => $field['imgHeight']);
        $view->assign('field', $fieldsArray);
        $publicFileURL = '<strong>' . System::getBaseUrl() . 'file.php?<br />file=' . ModUtil::getVar('IWforms', 'publicFolder') . '/<br />' . $this->__('Name_field') . '</strong>';
        $view->assign('publicFileURL', $publicFileURL);
        $content = $view->fetch('IWforms_user_fieldContent.htm');
        return array('content' => $content,
            'checkJS' => $checkJS,
            'requiredJS' => $requiredJS,
            'requiredText' => $requiredText);
    }

    /**
     * Submit a note in database
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	information of the note that have to be sumitted
     * @return:	Thue if success and false otherwise
     */
    public function submitNote($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'POST');
        $fmid = FormUtil::getPassedValue('fmid', isset($args['fmid']) ? $args['fmid'] : null, 'POST');
        $fields = FormUtil::getPassedValue('fields', isset($_POST) ? $_POST : null, 'POST');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWforms', 'user', 'main'));
        }
        if (!UserUtil::isLoggedIn() &&
                ModUtil::isHooked('dpCaptcha', 'IWforms') &&
                ModUtil::available('dpCaptcha')) {
            if (!ModUtil::callHooks('item', 'transform')) {
                LogUtil::registerError($this->__('Error with the capcha protect system'));
                return System::redirect(ModUtil::url('IWforms', 'user', 'newitem',
                                array('fid' => $fid)));
            }
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] != 1 &&
                $access['level'] < 3) {
            LogUtil::registerError($this->__('You can not access this form to send annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        if ($fmid == null ||
                $fmid == 0) {
            //It is a new note and it is created
            //create a new post
            $createNote = ModUtil::apiFunc('IWforms', 'user', 'createNote',
                            array('fid' => $fid,
                                'validate' => $access['defaultValidation']));
            if (!$createNote) {
                return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
            }
            $fmid = $createNote;
        }
        //get form fields
        $allFields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                        array('fid' => $fid,
                            'whereArray' => 'active|1'));
        //get all note contents
        $noteContents = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                        array('fid' => $fid,
                            'fmid' => $fmid));
        $noteContentIdArray = array();
        /*
          //Create an array with the note contents
          foreach ($noteContents as $noteContent) {
          array_push($noteContentIdArray, $noteContent['fndid']);
          }
          // IN THE FUTURE, FOR THE PROTOCOLS MODULE, TRY TO CHANGE THE ARRAY_PUSH FUNCTION
         */
        foreach ($fields as $key => $element) {
            if (substr($key, 0, 6) != 'fieldb' && substr($key, 0, 5) == 'field') {
                $fieldId = str_replace('field', '', $key);
                $fieldType = $allFields[$fieldId]['fieldType'];
                $fieldContent = stripslashes($element);
                switch ($fieldType) {
                    case '1':
                        //Not needed
                        break;
                    case '2':
                        //Not needed
                        break;
                    case '3':
                        $fieldContent = $fields['fieldbis' . $fieldId] . $fieldContent;
                        break;
                    case '4':
                        if ($allFields[$fieldId]['calendar'] == 1) {
                            $fieldContent = $fieldContent;
                        } else {
                            $fieldContent = ($fieldContent != '' &&
                                    $fields['fieldbis' . $fieldId] != '' &&
                                    $fields['fieldbisbis' . $fieldId] != '') ? $fieldContent . '/' . $fields['fieldbis' . $fieldId] . '/' . $fields['fieldbisbis' . $fieldId] : '';
                        }
                        break;
                    case '5':
                        $fieldContent = ($fieldContent != '' && $fields['fieldbis' . $fieldId] != '') ? $fieldContent . '.' . $fields['fieldbis' . $fieldId] : '';
                        break;
                    case '6':
                        //Not needed
                        break;
                    case '7':
                        //Not needed
                        break;
                    case '8':
                        //Not needed
                        break;
                }
                //If the field exists it is updated. Only if it is editable
                if (in_array($fieldId, $noteContentIdArray)) {
                    if ($allFields[$fieldId]['editable']) {
                        $items = array('content' => $fieldContent);
                        $updateNoteContent = ModUtil::apiFunc('IWforms', 'user', 'updateNoteContent',
                                        array('fmid' => $fmid,
                                            'fid' => $fid,
                                            'fndid' => $fieldId,
                                            'items' => $items));
                        if ($updateNoteContent) {
                            $msg = $this->__('The entry has been edited');
                        } else {
                            LogUtil::registerError($update['msg'] . ' ' . $this->__('There was an error in the edition of the annotation'));
                            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
                        }
                    }
                } else {
                    $validate = ($allFields[$fieldId]['validationNeeded'] == 0) ? 1 : 0;
                    //The field doesn't exists and it is created
                    $createNoteContent = ModUtil::apiFunc('IWforms', 'user', 'createNoteContent',
                                    array('fmid' => $fmid,
                                        'fid' => $fid,
                                        'fndid' => $fieldId,
                                        'content' => $fieldContent,
                                        'validate' => $validate));
                    if ($createNoteContent) {
                        $msg = $this->__('The entry has been created');
                    } else {
                        LogUtil::registerError($this->__('There was an error in the creation of the annotation'));
                        return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
                    }
                }
            }
        }
        //The fields with attached files are processed in a different way
        foreach ($_FILES as $key => $element) {
            $fieldId = str_replace('field', '', $key);
            $fieldType = $allFields[$fieldId]['fieldType'];
            $fieldContent = $element['name'];
            //Update the files to the server
            if ($fieldContent != '') {
                $folder = ($allFields[$fieldId]['publicFile'] != 1) ? ModUtil::getVar('IWforms', 'attached') : ModUtil::getVar('IWforms', 'publicFolder');
                if ($folder == '') {
                    LogUtil::registerError($update['msg'] . ' ' . $this->__('There was a problem in the attachment file. Probably the annotation was sent without the file or attachment.'));
                    return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
                }
                $allowOnly = ($allFields[$fieldId]['extensions'] != '') ? str_replace(',', '|', $allFields[$fieldId]['extensions']) : '';
                $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
                $update = ModUtil::func('IWmain', 'user', 'updateFile',
                                array('sv' => $sv,
                                    'folder' => $folder,
                                    'fileRealName' => $element['name'],
                                    'fileNameTemp' => $element['tmp_name'],
                                    'size' => $element['size'],
                                    'widthImg' => $allFields[$fieldId]['imgWidth'],
                                    'heighImg' => $allFields[$fieldId]['imgHeight'],
                                    'allowOnly' => $allowOnly));
                //the function returns the error string if the update fails and and empty string if success
                if ($update['msg'] != '') {
                    LogUtil::registerError($update['msg'] . ' ' . $this->__('There was a problem in the attachment file. Probably the annotation was sent without the file or attachment.'));
                    //return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
                }
                $fieldContent = $update['fileName'];
            }
            //Submit values to database
            //If the field exists it is updated. Only if it is editable
            if (in_array($fieldId, $noteContentIdArray)) {
                if ($allFields[$fieldId]['editable']) {
                    $items = array('content' => $fieldContent);
                    $updateNoteContent = ModUtil::apiFunc('IWforms', 'user', 'updateNoteContent',
                                    array('fmid' => $fmid,
                                        'fid' => $fid,
                                        'fndid' => $fieldId,
                                        'items' => $items));
                    if ($updateNoteContent) {
                        $msg = $this->__('The entry has been edited');
                    } else {
                        LogUtil::registerError($update['msg'] . ' ' . $this->__('There was an error in the edition of the annotation'));
                        return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
                    }
                }
            } else {
                $validate = ($allFields[$fieldId]['validationNeeded'] == 0) ? 1 : 0;
                //The field doesn't exists and it is created
                $createNoteContent = ModUtil::apiFunc('IWforms', 'user', 'createNoteContent',
                                array('fmid' => $fmid,
                                    'fid' => $fid,
                                    'fndid' => $fieldId,
                                    'content' => $fieldContent,
                                    'validate' => $validate));
                if ($createNoteContent) {
                    $msg = $this->__('The entry has been created');
                } else {
                    LogUtil::registerError($this->__('There was an error in the creation of the annotation'));
                    return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
                }
            }
        }
        //Successfull
        LogUtil::registerStatus($msg);
        //Get form definition
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form['returnURL'] != '') {
            return System::redirect($form['returnURL']);
        } else {
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
    }

    /**
     * Download a file
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	name of the file that have to be downloaded
     * @return:	The file required
     */
    public function download($args) {
        // Get the parameters
        $fndid = FormUtil::getPassedValue('fndid', isset($args['fndid']) ? $args['fndid'] : null, 'GET');
        $fileName = FormUtil::getPassedValue('fileName', isset($args['fileName']) ? $args['fileName'] : 0, 'GET');
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'GET');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        // Needed argument
        if (!isset($fileName) || !isset($fndid)) {
            return LogUtil::registerError($this->__('Error! Could not do what you wanted. Please check your input.'));
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] < 2) {
            LogUtil::registerError($this->__('You can not access this form to view the annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        $field = ModUtil::apiFunc('IWforms', 'user', 'getFormField',
                        array('fndid' => $fndid));
        // user can download the file
        if ($field['publicFile'] != 1) {
            $fileNameInServer = ModUtil::getVar('IWforms', 'attached') . '/' . $fileName;
        } else {
            $fileNameInServer = ModUtil::getVar('IWforms', 'publicFolder') . '/' . $fileName;
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        return ModUtil::func('IWmain', 'user', 'downloadFile',
                array('fileName' => $fileName,
                    'fileNameInServer' => $fileNameInServer,
                    'sv' => $sv));
    }

    /**
     * Export notes to CSV
     * @author	Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	form identity
     * @return	The module information
     */
    public function export($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'REQUEST');
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        //Get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return false;
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] < 2 || (!UserUtil::isLoggedIn() && $form['unregisterednotexport'] == 1)) {
            LogUtil::registerError($this->__('You do not have access to this form'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        //Checks if the exportatin is possible
        if (!file_exists(ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder')) || ModUtil::getVar('IWmain', 'tempFolder') == '') {
            LogUtil::registerError($this->__('The export is not possible because the directory for temporary files don\'t exist on the server. If the problem persists it communicates to the administrator at the portal.'));
            return System::redirect(ModUtil::url('IWforms', 'user', 'manage',
                            array('fid' => $fid)));
        }
        //get form fields
        $fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                        array('fid' => $fid,
                            'whereArray' => 'active|1'));
        $fieldsOrder = array(array('id' => -1,
                'name' => $this->__('State of the note')),
            array('id' => -2,
                'name' => $this->__('Date')),
            array('id' => -3,
                'name' => $this->__('Referring')));
        $fieldsTypes = array('1' => $this->__('Text'),
            '2' => $this->__('Text area'),
            '3' => $this->__('URL'),
            '4' => $this->__('Date'),
            '5' => $this->__('Time'),
            '6' => $this->__('List of options'),
            '8' => $this->__('Yes/No'));
        $i = 1;
        foreach ($fields as $field) {
            if ($field['fieldType'] != '7' && $field['fieldType'] != '51' && $field['fieldType'] != '52' && $field['fieldType'] != '53' && $field['fieldType'] != '100') {
                $name = (strlen($field['fieldName']) > 20) ? mb_strimwidth($field['fieldName'], 0, 30, '...') : $field['fieldName'];
                $fieldsOrder[] = array('id' => $field['fndid'],
                    'name' => $this->__('Field') . ' ' . $i . '=>' . $name);
                $i++;
                $fieldsArray[] = array('fieldName' => $field['fieldName'],
                    'fieldType' => $field['fieldType'],
                    'fndid' => $field['fndid']);
            }
        }
        // Create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $view->assign('form', $form);
        $view->assign('fieldsTypes', $fieldsTypes);
        $view->assign('fieldsOrder', $fieldsOrder);
        $view->assign('fields', $fieldsArray);
        return $view->fetch('IWforms_user_export.htm');
    }

    /**
     * Create the CSV file result of the exportation
     * @author	Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	The things to export
     * @return	The export file
     */
    public function generateCSV($args) {
        $fid = FormUtil::getPassedValue('fid', isset($args['fid']) ? $args['fid'] : null, 'POST');
        $user = FormUtil::getPassedValue('user', isset($args['user']) ? $args['user'] : null, 'POST');
        $date = FormUtil::getPassedValue('date', isset($args['date']) ? $args['date'] : null, 'POST');
        $observations = FormUtil::getPassedValue('observations', isset($args['observations']) ? $args['observations'] : null, 'POST');
        $renotes = FormUtil::getPassedValue('renotes', isset($args['renotes']) ? $args['renotes'] : null, 'POST');
        $state = FormUtil::getPassedValue('state', isset($args['state']) ? $args['state'] : null, 'POST');
        $fields = FormUtil::getPassedValue('fields', isset($args['fields']) ? $args['fields'] : null, 'POST');
        $url = FormUtil::getPassedValue('url', isset($args['url']) ? $args['url'] : null, 'POST');
        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('IWforms', 'user', 'main'));
        }
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                        array('fid' => $fid));
        if ($access['level'] < 2) {
            LogUtil::registerError($this->__('You can not access this form to send annotations'));
            // Redirect to the main site for the user
            return System::redirect(ModUtil::url('IWforms', 'user', 'main'));
        }
        //Get form
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                        array('fid' => $fid));
        if ($form == false) {
            LogUtil::registerError($this->__('Could not find form'));
            return System::redirect(ModUtil::url('IWforms', 'user', 'manage',
                            array('fid' => $fid)));
        }
        //get form fields
        $formFields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields',
                        array('fid' => $fid,
                            'whereArray' => 'active|1'));
        $fieldsOrder = array();
        $i = 1;
        foreach ($formFields as $field) {
            if ($field['fieldType'] != '7' &&
                    $field['fieldType'] != '51' &&
                    $field['fieldType'] != '52' &&
                    $field['fieldType'] != '53' &&
                    $field['fieldType'] != '100' &&
                    in_array($field['fndid'], $fields)) {
                $fieldsOrder[$i] = $field['fndid'];
                $i++;
            }
        }
        //get all form notes
        $notes = ModUtil::apiFunc('IWforms', 'user', 'getAllNotes',
                        array('fid' => $fid));
        $file = ModUtil::getVar('IWmain', 'documentRoot') . '/' . ModUtil::getVar('IWmain', 'tempFolder') . '/export' . date('dmY') . '.csv';
        $f = fopen($file, 'w');
        $row1 = '';
        if ($user == 'on')
            $row1 .= '"' . $this->__('user') . '",';
        if ($date == 'on')
            $row1 .= '"' . $this->__('Date') . '",';
        if ($observations == 'on')
            $row1 .= '"' . $this->__('Observations') . '",';
        if ($renotes == 'on')
            $row1 .= '"' . $this->__('Renotes') . '",';
        if ($state == 'on')
            $row1 .= '"' . $this->__('State') . '",';
        if ($url == 'on')
            $row1 .= '"' . $this->__('URL') . '",';
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $row1 .= '"' . $formFields[$field]['fieldName'] . '",';
            }
        }
        $row1 = substr($row1, 0, -1);
        //Posem la fila inicial del fitxer
        fwrite($f, $row1 . "\r\n");
        // get all the users who has sended a note
        $usersList = '$$';
        foreach ($notes as $note) {
            $usersList .= $note['user'] . '$$';
        }
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $users = ModUtil::func('IWmain', 'user', 'getAllUsersInfo',
                        array('info' => 'ccn',
                            'sv' => $sv,
                            'list' => $usersList));

        foreach ($notes as $note) {
            $i = 1;
            // get all note contents
            $noteContents = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                            array('fid' => $fid,
                                'fmid' => $note['fmid']));
            $row = '';
            if ($user == 'on') {
                if ($note['annonimous'] == 0) {
                    $row .= '"' . trim($users[$note['user']]) . '",';
                } else {
                    $row .= '"",';
                }
            }
            if ($date == 'on')
                $row .= '"' . date('d/m/Y H.i', $note['time']) . '",';
            if ($observations == 'on')
                $row .= '"' . $note['observations'] . '",';
            if ($renotes == 'on')
                $row .= '"' . $note['renote'] . '",';
            if ($state == 'on')
                $row .= '"' . $note['state'] . '",';
            if ($url == 'on')
                $row .= '"' . System::getBaseUrl() . 'index.php?module=IWforms&func=read&fid=' . $fid . '&fmid=' . $note['fmid'] . '",';
            foreach ($fieldsOrder as $field) {
                $row .= '"' . str_replace('"', '--', strip_tags($noteContents[$field]['content'])) . '",';
            }
            $row = substr($row, 0, -1);
            fwrite($f, $row . "\r\n");
        }
        fclose($f);
        // check file successful creation
        if (!is_file($file)) {
            LogUtil::registerError($this->__('There was an error in the creation of information. The export was not possible'));
            return System::redirect(ModUtil::url('IWforms', 'user', 'manage',
                            array('fid' => $fid)));
        }
        // gather relevent info about file
        $len = filesize($file);
        $filename = basename($file);
        $file_extension = strtolower(substr(strrchr($filename, "."), 1));
        $ctype = "CSV/CSV";
        // begin writing headers
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        // use the switch-generated Content-Type
        header("Content-Type: $ctype");
        // force the download
        $header = "Content-Disposition: attachment; filename=" . $filename . ";";
        header($header);
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . $len);
        @readfile($file);
        // delete temporal file from server
        unlink($file);
        exit;
    }

    /**
     * Show the module information
     * @author	Albert Pérez Monfort (aperezm@xtec.cat)
     * @return	The module information
     */
    public function module() {
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Sorry! No authorization to access this module.'), 403);
        }
        // Create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $module = ModUtil::func('IWmain', 'user', 'module_info',
                        array('module_name' => 'IWforms',
                            'type' => 'user'));
        $view->assign('module', $module);
        return $view->fetch('IWforms_user_module.htm');
    }

    /**
     * Prepare time to save it in database
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	time in format dd/mm/yy hh:ii::ss
     * @return:	time in format yy-mm-dd hh:ii:ss
     */
    public function makeTime($time) {
        $time = FormUtil::getPassedValue('time', isset($time) ? $time : null, 'POST');
        $time = substr($time, 6, 2) . '-' . //Year
                substr($time, 3, 2) . '-' . //month
                substr($time, 0, 2) . ' ' . //day
                substr($time, 8, 2) . ':' . //hour
                substr($time, 11, 2) . ':' . //minute
                substr($time, 14, 2); //second
        return $time;
    }

    /**
     * Prepare time to show it in a text field in a form
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	time in format yyyy-mm-dd hh:ii:ss
     * @return:	time in format dd/mm/yy
     */
    public function makeTimeForm($time) {
        $time = FormUtil::getPassedValue('time', isset($time) ? $time : null, 'POST');
        $time = substr($time, 8, 2) . '/' . //day
                substr($time, 5, 2) . '/' . //month
                substr($time, 2, 2); //second
        return $time;
    }

    /**
     * show a paginador if it is necessari
     * @author:     Albert Pérez Monfort (aperezm@xtec.cat)
     * @param:	ipp - items per page
     * @param:	total - number of items
     * @param:	total - template to load
     * @param:  init - First item to show
     * @return:	time in format dd/mm/yy
     */
    public function pager($args) {
        $ipp = FormUtil::getPassedValue('ipp', isset($args['ipp']) ? $args['ipp'] : null, 'POST');
        $init = FormUtil::getPassedValue('init', isset($args['init']) ? $args['init'] : null, 'POST');
        $total = FormUtil::getPassedValue('total', isset($args['total']) ? $args['total'] : null, 'POST');
        $urltemplate = FormUtil::getPassedValue('urltemplate', isset($args['urltemplate']) ? $args['urltemplate'] : null, 'POST');
        $view = Zikula_View::getInstance('IWforms', false);
        //Security check
        if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ))
            return LogUtil::registerPermissionError();
        // Quick check to ensure that we have work to do
        if ($total <= $ipp)
            return;
        if (!isset($init) || empty($init))
            $init = 1;
        if (!isset($ipp) || empty($ipp))
            $ipp = 10;
        // Show startnum link
        if ($init != 1) {
            $url = preg_replace('/%%/', 1, $urltemplate);
            $text = '<a href="' . $url . '"><<</a> | ';
        } else {
            $text = '<< | ';
        }
        $items[] = array('text' => $text);
        // Show following items
        $pagenum = 1;
        for ($curnum = 1; $curnum <= $total; $curnum += $ipp) {
            if (($init < $curnum) || ($init > ($curnum + $ipp - 1))) {
                //mod by marsu - use sliding window for pagelinks
                if ((($pagenum % 10) == 0) // link if page is multiple of 10
                        || ($pagenum == 1) // link first page
                        || (($curnum > ($init - 4 * $ipp)) //link -3 and +3 pages
                        && ($curnum < ($init + 4 * $ipp)))) {
                    // Not on this page - show link
                    $url = preg_replace('/%%/', $curnum, $urltemplate);
                    $text = '<a href="' . $url . '">' . $pagenum . '</a> | ';
                    $items[] = array('text' => $text);
                }
                //end mod by marsu
            } else {
                // On this page - show text
                $text = $pagenum . ' | ';
                $items[] = array('text' => $text);
            }
            $pagenum++;
        }
        if (($curnum >= $ipp + 1) && ($init < $curnum - $ipp)) {
            $url = preg_replace('/%%/', $curnum - $ipp, $urltemplate);
            $text = '<a href="' . $url . '">>></a>';
        } else {
            $text = '>>';
        }
        $items[] = array('text' => $text);
        $view->assign('items', $items);
        return $view->fetch('IWforms_user_pager.htm');
    }
}