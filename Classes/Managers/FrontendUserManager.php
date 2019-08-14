<?php
namespace YolfTypo3\SavLibraryMvc\Managers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
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
     * Controller
     *
     * @var DefaultController
     */
    protected $controller = null;

    /**
     * Sets the controller
     *
     * @param DefaultController $controller
     * @return void
     */
    public function setController(DefaultController $controller)
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
        return (is_null($GLOBALS['TSFE']->fe_user->user['uid']) ? false : true);
    }

    /**
     * Checks if the user is allowed to input data in the form
     *
     * @return boolean
     */
    public function userIsAllowedToInputData()
    {
        // Checks if the user is authenticated
        if ($this->userIsAuthenticated() === false) {
            return false;
        }

        // Condition on date
        $time = time();
        $conditionOnInputDate = (AbstractController::getSetting('inputStartDate') && ($time >= AbstractController::getSetting('inputStartDate')) && AbstractController::getSetting('inputEndDate') && ($time <= AbstractController::getSetting('inputEndDate')));
        switch (AbstractController::getSetting('dateUserRestriction')) {
            case self::NOBODY:
                $conditionOnInputDate = true;
            case self::ALL:
                // The condition is applied to all users including super Admin
                break;
            case self::ADMIN_PLUS_USER:
                // The condition will be checked in userIsAdmin and applied to admin Plus users
                $conditionOnInputDate = true;
                break;
            case self::ALL_EXCLUDING_SUPER_ADMIN:
                // Checks if the user is super Admin.
                $conditionOnInputDate = ($this->userIsSuperAdmin() ? true : $conditionOnInputDate);
                break;
        }

        // Condition on allowedGroups
        $result = (count(array_intersect(explode(',', AbstractController::getSetting('allowedGroups')), array_keys($GLOBALS['TSFE']->fe_user->groupData['uid']))) > 0 ? true : false);
        $conditionOnAllowedGroups = (AbstractController::getSetting('allowedGroups') ? $result : true);

        return AbstractController::getSetting('inputIsAllowed') && $conditionOnAllowedGroups && $conditionOnInputDate;
    }

    /**
     * Checks if the user is allowed to change data in the form
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     * @param string $additionalString
     *            (default '') String which will be added to the field value
     *
     * @return boolean
     */
    public function userIsAllowedToChangeData($object, $additionalString = '')
    {
        if ($this->userIsSuperAdmin()) {
            return true;
        }

        // Gets the admin configuration fronm the user TS Config
        $inputAdminConfiguration = $GLOBALS['TSFE']->fe_user->getUserTSconf();

        // Condition on the Input Admin Field
        $conditionOnInputAdminField = true;
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
                        $conditionOnInputAdminField = false;
                    }
                    break;
                default:
                    $extensionKey = DefaultController::getControllerExtensionKey();
                    $conditionOnInputAdminField = (strpos($inputAdminConfiguration[$extensionKey . '_Admin'], $fieldValue) === false ? false : true);
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
        if ($this->userIsAllowedToExportData() === false) {
            return false;
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
