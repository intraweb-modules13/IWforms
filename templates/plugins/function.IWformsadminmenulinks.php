<?php
function smarty_function_iwformsadminmenulinks($params, &$smarty)
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

	$formsadminmenulinks = "<span class=\"" . $params['class'] . "\">" . $params['start'] . " ";

	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_ADMIN)) {
		$formsadminmenulinks .= "<a href=\"" . DataUtil::formatForDisplayHTML(pnModURL('IWforms', 'admin', 'create')) . "\">" . __('Create a new form',$dom) . "</a> " . $params['seperator'];
	}

	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_ADMIN)) {
		$formsadminmenulinks .= " <a href=\"" . DataUtil::formatForDisplayHTML(pnModURL('IWforms', 'admin', 'main')) . "\">" . __('Show the forms',$dom) . "</a> " . $params['seperator'];
	}
	
	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_ADMIN)) {
		$formsadminmenulinks .= " <a href=\"" . DataUtil::formatForDisplayHTML(pnModURL('IWforms', 'admin', 'import')) . "\">" . __('Import a form',$dom) . "</a> ";
	}
	
	if (SecurityUtil::checkPermission('IWforms::', "::", ACCESS_ADMIN)) {
		$formsadminmenulinks .= $params['seperator'] . " <a href=\"" . DataUtil::formatForDisplayHTML(pnModURL('IWforms', 'admin', 'conf')) . "\">" . __('Configure the module',$dom) . "</a> ";
	}

	$formsadminmenulinks .= $params['end'] . "</span>\n";

	return $formsadminmenulinks;
}
