<?php
function smarty_function_iwformsusermenulinks($params, &$smarty)
{
	$dom = ZLanguage::getModuleDomain('IWforms');
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
		$params['class'] = 'pn-menuitem-title';
	}
	
	$fid = $params['fid'];
	$func = $params['func'];

	if(!UserUtil::isLoggedIn() && is_numeric($fid)){
		$form = ModUtil::apiFunc('IWforms', 'user', 'getFormDefinition', array('fid' => $fid));
		$notexport = $form['unregisterednotexport'];
	}else{
		$notexport = 0;
	}

	//get user permissions for this form
	if(isset($fid) && is_numeric($fid)){
		$access = ModUtil::func('IWforms', 'user', 'access', array('fid' => $fid));
	}
	
	$formsusermenulinks = "<span class=\"" . $params['class'] . "\">" . $params['start'] . " ";

	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ)) {
		$formsusermenulinks .= "<a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'main')) . "\">" . __('Show the forms',$dom) . "</a> ";
	}

	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) && $access['level'] > 2) {
		$formsusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'new', array('fid' => $fid))) . "\">" . __('Send an annotation',$dom) . "</a> ";
	}

	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) && UserUtil::isLoggedIn() && isset($fid)) {
		$formsusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'sended', array('fid' => $fid))) . "\">" . __('Show the notes I sent',$dom) . "</a> ";
	}

	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_READ) && isset($fid) && $notexport == 0 && $func != 'sended') {
		$formsusermenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(ModUtil::url('IWforms', 'user', 'export', array('fid' => $fid))) . "\">" . __('Export to CSV',$dom) . "</a> ";
	}
	
	$formsusermenulinks .= $params['end'] . "</span>\n";

	return $formsusermenulinks;
}
