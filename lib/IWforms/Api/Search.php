<?php

/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * The search for items in the Users module.
 */
class IWforms_Api_Search extends Zikula_AbstractApi {

    /**
     * Return search plugin info.
     *
     * @return array An array containing information for the searc API.
     */
    public function info() {
        return array(
            'title' => 'IWforms',
            'functions' => array(
                'IWforms' => 'search'
            )
        );
    }

    /**
     * Render the search form component for Users.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * boolean 'active' Indicates that the Users module is an active part of the search(?).
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return string The rendered template for the Users search component.
     */
    public function options($args) {
        $options = '';

        if (SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
            // Create output object - this object will store all of our output so that
            // we can return it easily when required
            $renderer = Zikula_View::getInstance($this->name);
            $options = $renderer->assign('active', !isset($args['active']) || isset($args['active']['IWforms']))
                    ->fetch('IWforms_search_options.htm');
        }

        return $options;
    }

    /**
     * Perform a search.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * ? $args['q'] ?.
     * ? $args[?]   ?.
     * 
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success or null result, false on error.
     */
    public function search($args) {
        // Security check
        if (!SecurityUtil::checkPermission('IWforms::', '::', ACCESS_READ)) {
            return false;
        }

        if (!isset($args['q']) || empty($args['q'])) {
            return true;
        }

        // get forms where user have read access
        //get all the active forms
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $forms = ModUtil::apiFunc('IWforms', 'user', 'getAllForms', array('user' => 1,
                    'sv' => $sv));

        $uid = UserUtil::getVar('uid');

        //get all the groups of the user
        $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
        $userGroups = ModUtil::func('IWmain', 'user', 'getAllUserGroups', array('uid' => $uid,
                    'sv' => $sv));

        if ($userGroups) {
            foreach ($userGroups as $group) {
                $userGroupsArray[] = $group['id'];
            }
        }

        $userForms = array();

        foreach ($forms as $form) {
            $sv = ModUtil::func('IWmain', 'user', 'genSecurityValue');
            $access = ModUtil::func('IWforms', 'user', 'access', array('fid' => $form['fid'],
                        'userGroups' => $userGroupsArray,
                        'uid' => $uid,
                        'sv' => $sv));
            if ($access['level'] > 1) {
                $userForms = $form;
            }
        }

        $fieldsArray = array();

        // get searchable fields
        foreach ($userForms as $form) {
            // get searchable fields for each form
            $fields = ModUtil::apiFunc('IWforms', 'user', 'getAllFormFields', array('fid' => $form['fid'],
                        'whereArray' => 'active|1$$searchable|1'));
            if (is_array($fields)) {
                $fieldsArray[] = $fields;
            }
        }


        print_r($fieldsArray);
        die();




        // get the db and table info
        $dbtable = DBUtil::getTables();
        $userscolumn = $dbtable['users_column'];

        $q = DataUtil::formatForStore($args['q']);
        $q = str_replace('%', '\\%', $q);  // Don't allow user input % as wildcard
        // build the where clause
        $where = array();
        $where[] = "({$userscolumn['activated']} != " . Users_Constant::ACTIVATED_PENDING_REG . ')';
        $where[] = $unameClause;
        $where = implode(' AND ', $where);

        //$notes = DBUtil::selectObjectArray('users', $where, '', -1, -1, 'uid');

        if (!$notes) {
            return true;
        }

        $sessionId = session_id();

        foreach ($notes as $note) {
            /*
              if ($user['uid'] != 1 && SecurityUtil::checkPermission('Users::', "$user[uname]::$user[uid]", ACCESS_READ)) {
              if ($useProfileMod) {
              $qtext = $this->__("Click the user's name to view his/her complete profile.");
              } else {
              $qtext = '';
              }
              $items = array('title' => $user['uname'],
              'text' => $qtext,
              'extra' => $user['uid'],
              'module' => 'Users',
              'created' => null,
              'session' => $sessionId);
              $insertResult = DBUtil::insertObject($items, 'search_result');
              if (!$insertResult) {
              $this->registerError($this->__("Error! Could not load the results of the user's search."));
              return false;
              }
              }
             */
        }
        return true;
    }

    /**
     * Do last minute access checking and assign URL to items.
     *
     * Access checking is ignored since access check has
     * already been done. But we do add a URL to the found user.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * array $args['datarow'] ?.
     * 
     * @param array $args The search results.
     *
     * @return bool True.
     */
    public function search_check($args) {

        /*
          $profileModule = System::getVar('profilemodule', '');
          if (!empty($profileModule) && ModUtil::available($profileModule)) {
          $datarow = &$args['datarow'];
          $userId = $datarow['extra'];
          $datarow['url'] = ModUtil::url($profileModule, 'user', 'view', array('uid' => $userId));
          }
         */

        return true;
    }

}
