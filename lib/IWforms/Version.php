<?php
 /**
 * Load the module version information
 *
 * @author		Albert Pérez Monfort (aperezm@xtec.cat)
 * @return		The version information
 */
$dom = ZLanguage::getModuleDomain('IWforms');
$modversion['name'] = 'IWforms';
$modversion['version'] = '2.1';
$modversion['description'] = __('Description', $dom);
$modversion['displayname']    = __('IWForms', $dom);
$modversion['url'] = __('IWforms', $dom);
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/help.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 0;
$modversion['author'] = 'Albert Pérez Monfort';
$modversion['contact'] = 'aperezm@xtec.cats';
$modversion['admin'] = 1;
$modversion['securityschema'] = array('IWforms::' => '::');
$modversion['dependencies'] = array(array('modname' => 'IWmain',
											'minversion' => '2.0',
											'maxversion' => '',
											'status' => PNMODULE_DEPENDENCY_REQUIRED));
