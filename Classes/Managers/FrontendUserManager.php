<?php

declare(strict_types=1);

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

namespace YolfTypo3\SavLibraryMvc\Managers;

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

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
     * @var AbstractController
     */
    protected ?AbstractController $controller = null;

    /**
     * Sets the controller
     *
     * @param AbstractController $controller
     * 
     * @return void
     */
    public function setController(AbstractController $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * Gets the Frontend user
     *
     * @return FrontendUserAuthentication
     */
    public function getFrontendUser(): FrontendUserAuthentication
    {
        return $this->controller->getRequest()->getAttribute('frontend.user');
    }

    /**
     * Gets the Frontend user id
     *
     * @return int
     */
    public function getUserId(): ?int
    {
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        if ($typo3Version->getMajorVersion() < 13) {
            // @extensionScannerIgnoreLine
            return $this->getFrontendUser()->user['uid'] ?? null;
        } else {
            return $this->getFrontendUser()->getUserId();
        }
    }
    
    /**
     * Checks if the a user is authenticated in FE.
     *
     * @return boolean
     */
    public function userIsAuthenticated(): bool
    {
    return (is_null($this->getUserId()) ? false : true);
    }

    /**
     * Checks if the user is allowed to input data in the form
     *
     * @return bool
     */
    public function userIsAllowedToInputData(): bool
    {
        // Checks if the user is authenticated
        if ($this->userIsAuthenticated() === false) {
            return false;
        }

        // Condition on date
        $time = time();
        $conditionOnInputDate = ($this->controller->getSetting('inputStartDate') &&
            ($time >= (int) $this->controller->getSetting('inputStartDate')) &&
            $this->controller->getSetting('inputEndDate') &&
            ($time <= (int) $this->controller->getSetting('inputEndDate')));

        switch ($this->controller->getSetting('dateUserRestriction')) {
            case '':
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
        $result = (count(array_intersect(explode(',', $this->controller->getSetting('allowedGroups')), array_keys($this->getFrontEndUser()->groupData['uid']))) > 0 ? true : false);
        $conditionOnAllowedGroups = ($this->controller->getSetting('allowedGroups') ? $result : true);

        return $this->controller->getSetting('inputIsAllowed') && $conditionOnAllowedGroups && $conditionOnInputDate;
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

        // Gets the admin configuration from the user configuration
        $inputAdminConfiguration = $this->getUserConfiguration();

        // Condition on the Input Admin Field
        $conditionOnInputAdminField = true;
        $inputAdminField = $this->controller->getSetting('inputAdminField');

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
                    if ($fieldValue != $this->controller->getFrontEndUser()->user['uid']) {
                        $conditionOnInputAdminField = false;
                    }
                    break;
                default:
                    $extensionKey = $this->controller->getControllerExtensionKey();
                    $conditionOnInputAdminField = (strpos($inputAdminConfiguration[$extensionKey . '_Admin'], $fieldValue) === false ? false : true);
                    break;
            }
        }
        return $conditionOnInputAdminField;
    }

    /**
     * Checks if the user is a super admin for the extension
     *
     * @return bool
     */
    public function userIsSuperAdmin(): bool
    {
        // Gets the extension key
        $extensionKey = $this->controller->getControllerExtensionKey();

        // Gets the user configuration
        $userConfiguration = $this->getUserConfiguration();

        // Sets the condition
        $condition = (($userConfiguration[$extensionKey . '_Admin'] ?? null) == '*');

        return $condition;
    }

    /**
     * Checks if the user is allowed to export data
     *
     * @return bool
     */
    public function userIsAllowedToExportData(): bool
    {
        // Gets the extension key
        $extensionKey = $this->controller->getControllerExtensionKey();

        // Gets the user configuration
        $userConfiguration = $this->getUserConfiguration();

        // Sets the condition
        $condition = (($userConfiguration[$extensionKey . '_Export'] ?? null) == '*' || ($userConfiguration[$extensionKey . '_ExportWithQuery'] ?? null) == '*');

        return $condition;
    }

    /**
     * Checks if the user is allowed to use query when exporting data
     *
     * @return bool
     */
    public function userIsAllowedToExportDataWithQuery(): bool
    {
        // Checks if the user is allowad to export data
        if ($this->userIsAllowedToExportData() === false) {
            return false;
        }

        // Gets the extension key
        $extensionKey = $this->controller->getControllerExtensionKey();

        // Gets the user configuration
        $userConfiguration = $this->getUserConfiguration();

        // Sets the condition
        $condition = ($userConfiguration[$extensionKey . '_ExportWithQuery'] == '*');

        return $condition;
    }

    /**
     * Gets the Frontend user configuration
     *
     * @return array
     */
    public function getUserConfiguration(): array
    {
        $configurationArray = explode(chr(10), $this->getFrontendUser()->user['tx_savlibrarymvc_config'] ?? '');
        $result = [];
        
        foreach ($configurationArray as $configurationString) {
            $position = strpos($configurationString, '=');
            if ($position !== false) {
                $parts = explode('=', $configurationString);
                $result[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $result;
    }
    
}
