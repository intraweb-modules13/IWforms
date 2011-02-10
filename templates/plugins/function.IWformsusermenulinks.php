<?php
function smarty_function_iwformsusermenulinks($params, &$smarty) {
    // set some defaults
    if (!isset($params['start'])) {
        $params['start'] = '[';
    }
    if (!isset($params['end'])) {
        $params['end'] = ']';
    }
    if (!isset($params['seperator'])) {
        $params['seperator'] = '|';
    }
    if (!isset($params['class'])) {
        $params['class'] = 'z-menuitem-title';
    }

    //$fid = (isset($params['fid'])) ? $params['fid'] : 0;
    $func = $params['func'];
    $fid = $params['fid'];

    if (!UserUtil::isLoggedIn() && is_numeric($fid)) {
        $form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition',
                array('fid' => $fid));
        $notexport = $form['unregisterednotexport'];
    } else {
        $notexport = 0;
    }

    //get user permissions for this form
    if (isset($fid) && is_numeric($fid)) {
        $access = ModUtil::func('IWforms', 'user', 'access',
                array('fid' => $fid));
    } else {
        $access = 0;
    }

    $formsusermenulinks = "<span class=\"" . $params['class'] . "\">" . $params['start'] . " ";

    if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
        $formsusermenulinks .= "<a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'main')) . "\">" . __('Show the forms') . "</a> ";
    }

    if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) && $access['level'] > 2) {
        $formsusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'newitem',
                array('fid' => $fid))) . "\">" . __('Send an annotation') . "</a> ";
    }

    if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) && UserUtil::isLoggedIn() && isset($fid)) {
        $formsusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'sended',
                array('fid' => $fid))) . "\">" . __('Show the notes I sent') . "</a> ";
    }

    if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) && isset($fid) && $notexport == 0 && $func != 'sended') {
        $formsusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'export',
                array('fid' => $fid))) . "\">" . __('Export to CSV') . "</a> ";
    }

    $formsusermenulinks .= $params['end'] . "</span>\n";

    return $formsusermenulinks;
}