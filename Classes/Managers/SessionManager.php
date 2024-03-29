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

use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Session Manager.
 */
class SessionManager
{

    /**
     * The library Data
     *
     * @var array
     */
    protected static $libraryData;

    /**
     * The filters session
     *
     * @var array
     */
    protected static $filtersData;

    /**
     * The selected filter Key
     *
     * @var string
     */
    protected static $selectedFilterKey;

    /**
     * Loads the session
     *
     * @return void
     */
    public static function loadSession()
    {
        // Loads the library, filters data and the selected filter key
        self::loadLibraryData();
        self::loadFiltersData();
        self::loadSelectedFilterKey();

        // Cleans the filters data
        self::cleanFiltersData();
    }

    /**
     * Loads the library data
     *
     * @return void
     */
    protected static function loadLibraryData()
    {
        self::$libraryData = $GLOBALS['TSFE']->fe_user->getKey('ses', AbstractController::getControllerObjectName());
    }

    /**
     * Loads the filters data
     *
     * @return void
     */
    protected static function loadFiltersData()
    {
        self::$filtersData = (array) $GLOBALS['TSFE']->fe_user->getKey('ses', 'filters');
    }

    /**
     * Loads the filter selected data
     *
     * @return void
     */
    protected static function loadSelectedFilterKey()
    {
        self::$selectedFilterKey = $GLOBALS['TSFE']->fe_user->getKey('ses', 'selectedFilterKey');
    }

    /**
     * Cleans the filter data
     *
     * @return void
     */
    protected static function cleanFiltersData()
    {

        // Gets the arguments
        $arguments = AbstractController::getOriginalArguments();

        if ($arguments['special']) {
            // Removes filters in the same page which are not active,
            // that is not selected or with the same contentID
            foreach (self::$filtersData as $filterKey => $filter) {
                if ($filterKey != self::$selectedFilterKey && $filter['pageId'] == $this->getPageId() && $filter['contentUid'] != self::$filtersData[self::$selectedFilterKey]['contentUid']) {
                    unset(self::$filtersData[$filterKey]);
                }
            }

            // Removes the selectedFilterKey if there no filter associated with it
            if (is_array(self::$filtersData[self::$selectedFilterKey]) === false) {
                self::$selectedFilterKey = null;
            }
        }
    }

    /**
     * Saves the session
     *
     * @return void
     */
    public static function saveSession()
    {
        // Saves the library information
        $GLOBALS['TSFE']->fe_user->setKey('ses', AbstractController::getControllerObjectName(), self::$libraryData);

        // Saves the filter information
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'filters', self::$filtersData);

        // Cleans the selected filter key
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'selectedFilterKey', null);

        // @extensionScannerIgnoreLine
        $GLOBALS['TSFE']->fe_user->storeSessionData();
    }

    /**
     * Gets a field in the session
     *
     * @param $fieldKey string
     *            The field key
     *
     * @return mixed
     */
    public static function getFieldFromSession(string $fieldKey)
    {
        return self::$libraryData[$fieldKey];
    }

    /**
     * Sets a field in the session
     *
     * @param $fieldKey string
     *            The field key
     * @param $value mixed
     *            The value
     *
     * @return void
     */
    public static function setFieldFromSession(string $fieldKey, $value)
    {
        self::$libraryData[$fieldKey] = $value;
    }

    /**
     * Gets a field in a subform
     *
     * @param string $subfromKey
     *            The subform field key
     * @param string $field
     *            The field
     *
     * @return mixed
     */
    public static function getSessionSubformField(string $subfromKey, string $field)
    {
        return self::$libraryData['subform'][$subfromKey][$field];
    }

    /**
     * Sets the value of a field in a subform
     *
     * @param string $subfromKey
     *            The subform field key
     * @param string $field
     *            The field
     * @param mixed $value
     *            The value
     *
     * @return void
     */
    public static function setSessionSubformField(string $subfromKey, string $field, $value)
    {
        self::$libraryData['subform'][$subfromKey][$field] = $value;
    }


    /**
     * Gets the selected filter key
     *
     * @return string
     */
    public static function getSelectedFilterKey(): string
    {
        return self::$selectedFilterKey;
    }

    /**
     * Gets a field in a filter
     *
     * @param string $filterKey
     *            The filter key
     * @param string $fieldName
     *            The field name
     *
     * @return mixed
     */
    public static function getFilterField(string $filterKey, string $fieldName)
    {
        return self::$filtersData[$filterKey][$fieldName];
    }

    /**
     * Gets the page id
     *
     * @return int
     */
    protected function getPageId():int
    {
        // @extensionScannerIgnoreLine
        return (int) $GLOBALS['TSFE']->id;
    }
}
