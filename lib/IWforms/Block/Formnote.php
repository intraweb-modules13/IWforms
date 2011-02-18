<?php
class IWforms_Block_formnote extends Zikula_Block
{
    public function init() {
        SecurityUtil::registerPermissionSchema("IWforms:formnoteblock:", "Note identity::");
    }

    public function info() {
        return array('text_type' => 'FormNote',
                     'func_edit' => 'formnote_edit',
                     'func_update' => 'formnote_update',
                     'module' => 'IWforms',
                     'text_type_long' => $this->__('Display the content of a note in a block'),
                     'allow_multiple' => true,
                     'form_content' => false,
                     'form_refresh' => false,
                     'show_preview' => true);
    }

    /**
     * Show the list of forms for choosed categories
     * @autor:	Albert Pérez Monfort
     * return:	The list of forms
     */
    public function display($blockinfo) {
        // Security check
        if (!SecurityUtil::checkPermission("IWforms:formnoteblock:", $blockinfo['url'] . "::", ACCESS_READ)) {
            return;
        }
        // Check if the module is available
        if (!ModUtil::available('IWforms')) {
            return;
        }
        $content = explode('$$$$$$$[parameter]$$$$$$$', $blockinfo['content']);
        if ($content[1] == 1)
            $blockinfo['title'] = '';
        $uid = (UserUtil::isLoggedIn()) ? UserUtil::getVar('uid') : '-1';
        //get the headlines saved in the user vars. It is renovate every 10 minutes
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $exists = ModUtil::apiFunc('IWmain', 'user', 'userVarExists',
                                    array('name' => 'formNoteBlock' . $blockinfo['bid'],
                                          'module' => 'IWforms',
                                          'uid' => $uid,
                                          'sv' => $sv));
        if ($exists) {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $s = ModUtil::func('IWmain', 'user', 'userGetVar',
                                array('uid' => $uid,
                                      'name' => 'formNoteBlock' . $blockinfo['bid'],
                                      'module' => 'IWforms',
                                      'sv' => $sv,
                                      'nult' => true));
            // get note
            $note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                                      array('fmid' => $blockinfo['url']));
            //check user access to this form
            $access = ModUtil::func('IWforms', 'user', 'access',
                                     array('fid' => $note['fid']));
            if ($access['level'] < 2) {
                return false;
            }
            //Get item
            $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                                      array('fid' => $note['fid']));
            if ($form == false) {
                return false;
            }
            if ($content[0] == '') {
                return false;
            }
            if ($form['skincss'] != '') {
                $skincssurl = '<link rel="stylesheet" href="' . $form['skincss'] . '" type="text/css" />';
            }
            // Create output object
            $view = Zikula_View::getInstance('IWforms', false);
            $view->assign('contentBySkin', DataUtil::formatForDisplayHTML(stripslashes($s)));
            $view->assign('skincssurl', $skincssurl);
            $s = $view->fetch('IWforms_block_formNote.htm');
            // Populate block info and pass to theme
            $blockinfo['content'] = $s;
            return BlockUtil::themesideblock($blockinfo);
        }
        // get note
        $note = ModUtil::apiFunc('IWforms', 'user', 'getNote',
                                  array('fmid' => $blockinfo['url']));
        //check user access to this form
        $access = ModUtil::func('IWforms', 'user', 'access',
                                 array('fid' => $note['fid']));
        if ($access['level'] < 2) {
            return false;
        }
        //Get item
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                                  array('fid' => $note['fid']));
        if ($form == false) {
            return false;
        }
        if ($content[0] == '') {
            return false;
        }
        $noteContent = ModUtil::apiFunc('IWforms', 'user', 'getAllNoteContents',
                                         array('fid' => $note['fid'],
                                               'fmid' => $note['fmid']));
        if ($note['annonimous'] == 0 && ($uid != '-1' || ($uid == '-1' && $form['unregisterednotusersview'] == 0))) {
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
        $contentBySkin = str_replace('[$user$]', $userName, $content[0]);
        $contentBySkin = str_replace('[$time$]', date('H.i', $note['time']), $contentBySkin);
        $contentBySkin = str_replace('[$noteId$]', $note['fmid'], $contentBySkin);
        $contentBySkin = str_replace('[$formId$]', $note['fid'], $contentBySkin);
        $contentBySkin = str_replace('[$date$]', date('d/m/Y', $note['time']), $contentBySkin);
        if ($photo != '') {
            $photo = '<img src="' . System::getBaseUrl() . 'index.php?module=IWforms&func=getFile&fileName=' . $photo . '" />';
        }
        $contentBySkin = str_replace('[$avatar$]', $photo, $contentBySkin);
        foreach ($noteContent as $key => $value) {
            $contentBySkin = str_replace('[$' . $key . '$]', nl2br($value['content']), $contentBySkin);
            $contentBySkin = str_replace('[%' . $key . '%]', $value['fieldName'], $contentBySkin);
        }
        $contentBySkin = ($note['publicResponse']) ? str_replace('[$reply$]', $note['renote'], $contentBySkin) : str_replace('[$reply$]', '', $contentBySkin);
        // this set of changes are required in case the field contents was edited because using Javascript some special chars are modified
        $contentBySkin = str_replace('|per|', '%', $contentBySkin);
        $contentBySkin = str_replace('|par|', '#', $contentBySkin);
        $contentBySkin = str_replace('|int|', '?', $contentBySkin);
        $contentBySkin = str_replace('|amp|', '&', $contentBySkin);
        // load the template defined in the form definition
        if ($form['skincss'] != '') {
            $skincssurl = '<link rel="stylesheet" href="' . $form['skincss'] . '" type="text/css" />';
        }
        $isValidator = ($access['level'] == 7) ? 1 : 0;
        $isAdministrator = (SecurityUtil::checkPermission('blocks::', "::", ACCESS_ADMIN)) ? 1 : 0;
        // create output object
        $view = Zikula_View::getInstance('IWforms', false);
        $view->assign('contentBySkin', DataUtil::formatForDisplayHTML(stripslashes($contentBySkin)));
        $view->assign('skincssurl', $skincssurl);
        $view->assign('isValidator', $isValidator);
        $view->assign('isAdministrator', $isAdministrator);
        $view->assign('fmid', $blockinfo['url']);
        $view->assign('bid', $blockinfo['bid']);
        $view->assign('fid', $note['fid']);
        $s = $view->fetch('IWforms_block_formNote.htm');
        // copy the block information into user vars
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        ModUtil::func('IWmain', 'user', 'userSetVar',
                       array('uid' => $uid,
                             'name' => 'formNoteBlock' . $blockinfo['bid'],
                             'module' => 'IWforms',
                             'sv' => $sv,
                             'value' => $s,
                             'lifetime' => '300'));
        // Populate block info and pass to theme
        $blockinfo['content'] = $s;
        return BlockUtil::themesideblock($blockinfo);
    }

    function formnote_update($blockinfo) {
        // Security check
        if (!SecurityUtil::checkPermission("IWforms:formnoteblock:", $blockinfo['url'] . "::", ACCESS_ADMIN)) {
            return;
        }
        $fmid = $blockinfo['fmid'];
        $blockContent = $blockinfo['blockContent'];
        $blockHideTitle = (isset($blockinfo['blockHideTitle']) && $blockinfo['blockHideTitle'] == 1) ? 1 : 0;
        $blockinfo['content'] = "$blockContent" . '$$$$$$$[parameter]$$$$$$$' . $blockHideTitle;
        $blockinfo['url'] = "$fmid";
        return $blockinfo;
    }

    function formnote_edit($blockinfo) {
        // Security check
        if (!SecurityUtil::checkPermission("IWforms:formnoteblock:", $blockinfo['url'] . "::", ACCESS_ADMIN)) {
            return;
        }
        $fmid = $blockinfo['url'];
        $content = explode('$$$$$$$[parameter]$$$$$$$', $blockinfo['content']);
        $checked = ($content[1] == 1) ? "checked" : "";
        $blockContent = stripslashes($content[0]);
        $sortida = '<tr><td valign="top">' . $this->__('Identity of the note that must be schown') . '</td><td>' . "<input type=\"text\" name=\"fmid\" size=\"5\" maxlength=\"5\" value=\"$fmid\" />" . "</td></tr>\n";
        $sortida .= '<tr><td valign="top">' . $this->__('Hide block title') . '</td><td>' . "<input type=\"checkbox\" name=\"blockHideTitle\"" . $checked . " value=\"1\" />" . "</td></tr>\n";
        $sortida .= '<tr><td valign="top">' . $this->__('Block content') . '</td><td>' . "<textarea name=\"blockContent\" rows=\"5\" cols=\"70\">" . $blockContent . "</textarea>" . "</td></tr>\n";
        $sortida .= '<tr><td colspan=\"2\" valign="top"><div class="z-informationmsg">' . $this->__("[\$formId\$] =>Identity of the form, [\$noteId\$] =>Identity of the note, [%id%] => Title of the field, [\$id\$] => Content of the field, [\$user\$] => Username, [\$date\$] => Note creation date, [\$time\$] => Note creation time, [\$avatar\$] => User avatar, [\$reply\$] => Reply to the user if the reply is public") . "</div></td><tr>\n";
        return $sortida;
    }
}