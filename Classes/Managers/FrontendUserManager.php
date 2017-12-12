<?php
namespace YolfTypo3\SavLibraryMvc\Managers;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;

/**
 * Frontend user manager.
 */
class FrontendUserManager
{

    // Constants used in admin methods
    const NOBODY = 0;

    const ALL = 1;

    const ADMIN_PLUS_USER = 2;

    const ALL_EXCLUDING_SUPER_ADMIN = 3;

    /**
     *
     * @var \YolfTypo3\SavLibraryMvc\Controller\DefaultController
     */
    protected $controller = NULL;

    /**
     * Sets the controller
     *
     * @param \YolfTypo3\SavLibraryMvc\Controller\DefaultController $controller
     * @return void
     */
    public function setController(\YolfTypo3\SavLibraryMvc\Controller\DefaultController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Checks if the a user is authenticated in FE.
     *
     * @return boolean
     */
    public function userIsAuthenticated()
    {
        return (is_null($GLOBALS['TSFE']->fe_user->user['uid']) ? FALSE : TRUE);
    }

    /**
     * Checks if the user is allowed to input data in the form
     *
     * @return boolean
     */
    public function userIsAllowedToInputData()
    {
        // Checks if the user is authenticated
        if ($this->userIsAuthenticated() === FALSE) {
            return FALSE;
        }

        // Condition on date
        $time = time();
        $conditionOnInputDate = (AbstractController::getSetting('inputStartDate') && ($time >= AbstractController::getSetting('inputStartDate')) && AbstractController::getSetting('inputEndDate') && ($time <= AbstractController::getSetting('inputEndDate')));
        switch (AbstractController::getSetting('dateUserRestriction')) {
            case self::NOBODY:
                $conditionOnInputDate = TRUE;
            case self::ALL:
                // The condition is applied to all users including super Admin
                break;
            case self::ADMIN_PLUS_USER:
                // The condition will be checked in userIsAdmin and applied to admin Plus users
                $conditionOnInputDate = TRUE;
                break;
            case self::ALL_EXCLUDING_SUPER_ADMIN:
                // Checks if the user is super Admin.
                $conditionOnInputDate = ($this->userIsSuperAdmin() ? TRUE : $conditionOnInputDate);
                break;
        }

        // Condition on allowedGroups
        $result = (count(array_intersect(explode(',', AbstractController::getSetting('allowedGroups')), array_keys($GLOBALS['TSFE']->fe_user->groupData['uid']))) > 0 ? TRUE : FALSE);
        $conditionOnAllowedGroups = (AbstractController::getSetting('allowedGroups') ? $result : TRUE);

        return AbstractController::getSetting('inputIsAllowed') && $conditionOnAllowedGroups && $conditionOnInputDate;
    }

    /**
     * Checks if the user is allowed to change data in the form
     *
     * @param  \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     * @param string $additionalString
     *            (default '') String which will be added to the field value
     *
     * @return boolean
     */
    public function userIsAllowedToChangeData($object, $additionalString = '')
    {
        if ($this->userIsSuperAdmin()) {
            return TRUE;
        }

        // Gets the admin configuration fronm the user TS Config
        $inputAdminConfiguration = $GLOBALS['TSFE']->fe_user->getUserTSconf();

        // Condition on the Input Admin Field
        $conditionOnInputAdminField = TRUE;
        $inputAdminField = AbstractController::getSetting('inputAdminField');

        if (! empty($inputAdminField)) {
            // Gets the value
            $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($inputAdminField);
            $fieldValue = $object->$getterName();
            $fieldValue = html_entity_decode($fieldValue . $additionalString, ENT_QUOTES);

            switch ($inputAdminField) {
                // Currently Extbase sets cruser_id to 0 when data are input in front end.
                // The field cruser_id_frontend is in the default model and is created for all generated extensions.
                // It was introduced to recover the id of the frontend user who created the record.
                case 'cruser_id_frontend':
                case 'cruser_id':
                    // Checks if the user created the record
                    if ($fieldValue != $GLOBALS['TSFE']->fe_user->user['uid']) {
                        $conditionOnInputAdminField = FALSE;
                    }
                    break;
                default:
                    $extensionKey = DefaultController::getControllerExtensionKey();
                    $conditionOnInputAdminField = (strpos($inputAdminConfiguration[$extensionKey . '_Admin'], $fieldValue) === FALSE ? FALSE : TRUE);
                    break;
            }
        }
        return $conditionOnInputAdminField;
    }

    /**
     * Checks if the user is a super admin for the extension
     *
     * @return boolean
     */
    public function userIsSuperAdmin()
    {
        // Gets the extension key
        $extensionKey = DefaultController::getControllerExtensionKey();

        // Gets the user TypoScript configuration
        $userTypoScriptConfiguration = $GLOBALS['TSFE']->fe_user->getUserTSconf();

        // Sets the condition
        $condition = ($userTypoScriptConfiguration[$extensionKey . '_Admin'] == '*');

        return $condition;
    }

    /**
     * Checks if the user is allowed to export data
     *
     * @return boolean
     */
    public function userIsAllowedToExportData()
    {
        // Gets the extension key
        $extensionKey = DefaultController::getExtensionKey();

        // Gets the user TypoScript configuration
        $userTypoScriptConfiguration = $GLOBALS['TSFE']->fe_user->getUserTSconf();

        // Sets the condition
        $condition = ($userTypoScriptConfiguration[$extensionKey . '_Export'] == '*' || $userTypoScriptConfiguration[$extensionKey . '_ExportWithQuery'] == '*');

        return $condition;
    }

    /**
     * Checks if the user is allowed to use query when exporting data
     *
     * @return boolean
     */
    public function userIsAllowedToExportDataWithQuery()
    {
        // Checks if the user is allowad to export data
        if ($this->userIsAllowedToExportData() === FALSE) {
            return FALSE;
        }

        // Gets the extension key
        $extensionKey = DefaultController::getExtensionKey();

        // Gets the user TypoScript configuration
        $userTypoScriptConfiguration = $GLOBALS['TSFE']->fe_user->getUserTSconf();

        // Sets the condition
        $condition = ($userTypoScriptConfiguration[$extensionKey . '_ExportWithQuery'] == '*');

        return $condition;
    }

    /**
     * Gets a setting.
     *
     * @param string $settingName
     *            The setting name
     * @return mixed
     */
    protected function getSetting($settingName)
    {
        return $this->controller->settings[$settingName];
    }
}
?>
