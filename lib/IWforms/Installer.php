<?php
class IWforms_Installer extends Zikula_Installer {
    /**
     * Initialise the IWforms module creating module tables and module vars
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    public function Install() {
        // Checks if module IWmain is installed. If not returns error
        $modid = ModUtil::getIdFromName('IWmain');
        $modinfo = ModUtil::getInfo($modid);

        if ($modinfo['state'] != 3) {
            return LogUtil::registerError($this->__('Module IWmain is needed. You have to install the IWmain module before installing it.'));
        }

        // Check if the version needed is correct
        $versionNeeded = '2.0';
        if (!ModUtil::func('IWmain', 'admin', 'checkVersion', array('version' => $versionNeeded))) {
            return false;
        }

        // Create module tables
        if (!DBUtil::createTable('IWforms_definition')) return false;
        if (!DBUtil::createTable('IWforms_cat')) return false;
        if (!DBUtil::createTable('IWforms')) return false;
        if (!DBUtil::createTable('IWforms_note')) return false;
        if (!DBUtil::createTable('IWforms_note_definition')) return false;
        if (!DBUtil::createTable('IWforms_validator')) return false;
        if (!DBUtil::createTable('IWforms_group')) return false;

        //Create indexes
        $pntable = DBUtil::getTables();
        $c = $pntable['IWforms_definition_column'];
        if (!DBUtil::createIndex($c['active'], 'IWforms_definition', 'active')) return false;

        $c = $pntable['IWforms_column'];
        if (!DBUtil::createIndex($c['fid'], 'IWforms', 'fid')) return false;

        $c = $pntable['IWforms_group_column'];
        if (!DBUtil::createIndex($c['fid'], 'IWforms_group', 'fid')) return false;

        $c = $pntable['IWforms_note_column'];
        if (!DBUtil::createIndex($c['fmid'], 'IWforms_note', 'fmid')) return false;
        if (!DBUtil::createIndex($c['fndid'], 'IWforms_note', 'fndid')) return false;

        $c = $pntable['IWforms_note_definition_column'];
        if (!DBUtil::createIndex($c['fid'], 'IWforms_note_definition', 'fid')) return false;

        $c = $pntable['IWforms_validator_column'];
        if (!DBUtil::createIndex($c['fid'], 'IWforms_validator', 'fid')) return false;

        //Set module vars
        ModUtil::setVar('IWforms', 'characters', '15');
        ModUtil::setVar('IWforms', 'resumeview', '0');
        ModUtil::setVar('IWforms', 'newsColor', '#90EE90');
        ModUtil::setVar('IWforms', 'viewedColor', '#FFFFFF');
        ModUtil::setVar('IWforms', 'completedColor', '#D3D3D3');
        ModUtil::setVar('IWforms', 'validatedColor', '#CC9999');
        ModUtil::setVar('IWforms', 'fieldsColor', '#ADD8E6');
        ModUtil::setVar('IWforms', 'contentColor', '#FFFFE0');
        ModUtil::setVar('IWforms', 'attached', 'forms');
        ModUtil::setVar('IWforms', 'publicFolder', 'documents');

        //Successfull
        return true;
    }

    /**
     * Delete the IWforms module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    public function Uninstall() {
        // Delete module table
        DBUtil::dropTable('IWforms_definition');
        DBUtil::dropTable('IWforms_cat');
        DBUtil::dropTable('IWforms');
        DBUtil::dropTable('IWforms_note');
        DBUtil::dropTable('IWforms_note_definition');
        DBUtil::dropTable('IWforms_validator');
        DBUtil::dropTable('IWforms_group');

        //Delete module vars
        ModUtil::delVar('IWforms', 'characters');
        ModUtil::delVar('IWforms', 'resumeview');
        ModUtil::delVar('IWforms', 'colornoves');
        ModUtil::delVar('IWforms', 'colorvistes');
        ModUtil::delVar('IWforms', 'colorcompletades');
        ModUtil::delVar('IWforms', 'colornovalidades');
        ModUtil::delVar('IWforms', 'colorfonscamps');
        ModUtil::delVar('IWforms', 'colorfonscontingut');
        ModUtil::delVar('IWforms', 'attached');
        ModUtil::delVar('IWforms', 'publicFolder', 'documents');

        //Deletion successfull
        return true;
    }

    /**
     * Update the IWforms module
     * @author Albert Pérez Monfort (aperezm@xtec.cat)
     * @return bool true if successful, false otherwise
     */
    public function upgrade($oldversion) {
        if (!DBUtil::changeTable('IWforms_definition')) return false;
        if (!DBUtil::changeTable('IWforms_cat')) return false;
        if (!DBUtil::changeTable('IWforms')) return false;
        if (!DBUtil::changeTable('IWforms_note')) return false;
        if (!DBUtil::changeTable('IWforms_note_definition')) return false;
        if (!DBUtil::changeTable('IWforms_validator')) return false;
        if (!DBUtil::changeTable('IWforms_group')) return false;
        if ($oldversion < '1.2') {
            //Create indexes
            $pntable = DBUtil::getTables();
            $c = $pntable['IWforms_definition_column'];
            !DBUtil::createIndex($c['active'], 'IWforms_definition', 'active');
            $c = $pntable['IWforms_column'];
            !DBUtil::createIndex($c['fid'], 'IWforms', 'fid');
            $c = $pntable['IWforms_group_column'];
            !DBUtil::createIndex($c['fid'], 'IWforms_group', 'fid');
            $c = $pntable['IWforms_note_column'];
            !DBUtil::createIndex($c['fmid'], 'IWforms_note', 'fmid');
            !DBUtil::createIndex($c['fndid'], 'IWforms_note', 'fndid');
            $c = $pntable['IWforms_note_definition_column'];
            !DBUtil::createIndex($c['fid'], 'IWforms_note_definition', 'fid');
            $c = $pntable['IWforms_validator_column'];
            !DBUtil::createIndex($c['fid'], 'IWforms_validator', 'fid');
        }
        return true;
    }
}