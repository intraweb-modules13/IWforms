<?php
/**
 * PostNuke Application Framework
 *
 * @copyright (c) 2002, PostNuke Development Team
 * @link http://www.postnuke.com
 * @version $Id: pninit.php 22139 2007-06-01 10:57:16Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_Value_Addons
 * @subpackage Forms
 */

/**
 * Initialise the IWforms module creating module tables and module vars
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function IWforms_init()
{
	$dom = ZLanguage::getModuleDomain('IWforms');
	// Checks if module IWmain is installed. If not returns error
	$modid = pnModGetIDFromName('IWmain');
	$modinfo = pnModGetInfo($modid);
	
	if ($modinfo['state'] != 3) {
		return LogUtil::registerError (__('Module IWmain is needed. You have to install the IWmain module before installing it.', $dom));
	}
	
	// Check if the version needed is correct
	$versionNeeded = '2.0';
	if (!pnModFunc('IWmain', 'admin', 'checkVersion', array('version' => $versionNeeded))) {
		return false;
	}

	// Create module tables
	if (!DBUtil::createTable('IWforms_def')) return false;
	if (!DBUtil::createTable('IWforms_cat')) return false;
	if (!DBUtil::createTable('IWforms')) return false;
	if (!DBUtil::createTable('IWforms_note')) return false;
	if (!DBUtil::createTable('IWforms_note_def')) return false;
	if (!DBUtil::createTable('IWforms_validator')) return false;
	if (!DBUtil::createTable('IWforms_group')) return false;

	//Create indexes
	$pntable = pnDBGetTables();
	$c = $pntable['IWforms_def_column'];
	if (!DBUtil::createIndex($c['active'],'IWforms_def', 'active')) return false;
	
	$c = $pntable['IWforms_column'];
	if (!DBUtil::createIndex($c['fid'],'IWforms', 'fid')) return false;

	$c = $pntable['IWforms_group_column'];
	if (!DBUtil::createIndex($c['fid'],'IWforms_group', 'fid')) return false;	

	$c = $pntable['IWforms_note_column'];
	if (!DBUtil::createIndex($c['fmid'],'IWforms_note', 'fmid')) return false;
	if (!DBUtil::createIndex($c['fndid'],'IWforms_note', 'fndid')) return false;	

	$c = $pntable['IWforms_note_def_column'];
	if (!DBUtil::createIndex($c['fid'],'IWforms_note_def', 'fid')) return false;

	$c = $pntable['IWforms_validator_column'];
	if (!DBUtil::createIndex($c['fid'],'IWforms_validator', 'fid')) return false;
	
	//Set module vars
	pnModSetVar('IWforms','characters','15');
	pnModSetVar('IWforms','resumeview','0');
	pnModSetVar('IWforms','newsColor','#90EE90');
	pnModSetVar('IWforms','viewedColor','#FFFFFF');
	pnModSetVar('IWforms','completedColor','#D3D3D3');
	pnModSetVar('IWforms','validatedColor','#CC9999');
	pnModSetVar('IWforms','fieldsColor','#ADD8E6');
	pnModSetVar('IWforms','contentColor','#FFFFE0');
	pnModSetVar('IWforms','attached','forms');
	pnModSetVar('IWforms','publicFolder','documents');

	//Successfull
	return true;
}

/**
 * Delete the IWforms module
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function IWforms_delete()
{
	// Delete module table
	DBUtil::dropTable('IWforms_def');
	DBUtil::dropTable('IWforms_cat');
	DBUtil::dropTable('IWforms');
	DBUtil::dropTable('IWforms_note');
	DBUtil::dropTable('IWforms_note_def');
	DBUtil::dropTable('IWforms_validator');
	DBUtil::dropTable('IWforms_group');

	//Delete module vars
	pnModDelVar('IWforms','characters');
	pnModDelVar('IWforms','resumeview');
	pnModDelVar('IWforms','colornoves');
	pnModDelVar('IWforms','colorvistes');
	pnModDelVar('IWforms','colorcompletades');
	pnModDelVar('IWforms','colornovalidades');
	pnModDelVar('IWforms','colorfonscamps');
	pnModDelVar('IWforms','colorfonscontingut');
	pnModDelVar('IWforms','attached');
	pnModDelVar('IWforms','publicFolder','documents');
	
	//Deletion successfull
	return true;
}

/**
 * Update the IWforms module
 * @author Albert Pérez Monfort (aperezm@xtec.cat)
 * @return bool true if successful, false otherwise
 */
function IWforms_upgrade($oldversion)
{
	if (!DBUtil::changeTable('IWforms_def')) return false;
	if (!DBUtil::changeTable('IWforms_cat')) return false;
	if (!DBUtil::changeTable('IWforms')) return false;
	if (!DBUtil::changeTable('IWforms_note')) return false;
	if (!DBUtil::changeTable('IWforms_note_def')) return false;
	if (!DBUtil::changeTable('IWforms_validator')) return false;
	if (!DBUtil::changeTable('IWforms_group')) return false;
	if($oldversion < '1.2'){
		//Create indexes
		$pntable = pnDBGetTables();
		$c = $pntable['IWforms_def_column'];
		!DBUtil::createIndex($c['active'],'IWforms_def', 'active');
		$c = $pntable['IWforms_column'];
		!DBUtil::createIndex($c['fid'],'IWforms', 'fid');
		$c = $pntable['IWforms_group_column'];
		!DBUtil::createIndex($c['fid'],'IWforms_group', 'fid');	
		$c = $pntable['IWforms_note_column'];
		!DBUtil::createIndex($c['fmid'],'IWforms_note', 'fmid');
		!DBUtil::createIndex($c['fndid'],'IWforms_note', 'fndid');	
		$c = $pntable['IWforms_note_def_column'];
		!DBUtil::createIndex($c['fid'],'IWforms_note_def', 'fid');
		$c = $pntable['IWforms_validator_column'];
		!DBUtil::createIndex($c['fid'],'IWforms_validator', 'fid');
	}
	return true;
}
