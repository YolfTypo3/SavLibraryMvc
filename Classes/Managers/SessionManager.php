<?php
namespace SAV\SavLibraryMvc\Managers;

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
use SAV\SavLibraryMvc\Controller\AbstractController;

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
     * @param
     *            none
     *            
     * @return void
     */
    public static function loadSession()
    {
        // Loads the library, filters data and the selected filter key
        self::loadLibraryData();
        self::loadFiltersData();
        self::loadSelectedFilterKey();
        // TODO Remove
        // \TYPO3\CMS\Core\Utility\DebugUtility::debug(self::$libraryData, 'load self::$libraryData');
        // Cleans the filters data
        self::cleanFiltersData();
    }

    /**
     * Loads the library data
     *
     * @param
     *            none
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
     * @param
     *            none
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
     * @param
     *            none
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
     * @param
     *            none
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
                if ($filterKey != self::$selectedFilterKey && $filter['pageID'] == $GLOBALS['TSFE']->id && $filter['contentID'] != self::$filtersData[self::$selectedFilterKey]['contentID']) {
                    unset(self::$filtersData[$filterKey]);
                }
            }
            
            // Removes the selectedFilterKey if there no filter associated with it
            if (is_array(self::$filtersData[self::$selectedFilterKey]) === FALSE) {
                self::$selectedFilterKey = NULL;
            }
        }
    }

    /**
     * Saves the session
     *
     * @param
     *            none
     *            
     * @return void
     */
    public static function saveSession()
    {
        // Saves the library information
        // $compressed = GeneralUtility::_GET(\SAV\SavLibraryPlus\Controller\AbstractController::LIBRARY_NAME);
        // self::setFieldFromSession('compressedParameters', \SAV\SavLibraryPlus\Managers\UriManager::getCompressedParameters());
        // \TYPO3\CMS\Core\Utility\DebugUtility::debug(AbstractController::getControllerObjectName(), 'AbstractController::getControllerObjectName()');
        $GLOBALS['TSFE']->fe_user->setKey('ses', AbstractController::getControllerObjectName(), self::$libraryData);
        // \TYPO3\CMS\Core\Utility\DebugUtility::debug(self::$libraryData, 'save self::$libraryData');
        // Saves the filter information
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'filters', self::$filtersData);
        
        // Cleans the selected filter key
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'selectedFilterKey', NULL);
        
        $GLOBALS['TSFE']->storeSessionData();
    }

    /**
     * Gets a field in the session
     *
     * @param $fieldKey string
     *            The field key
     *            
     * @return mixed
     */
    public static function getFieldFromSession($fieldKey)
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
     * @return mixed
     */
    public static function setFieldFromSession($fieldKey, $value)
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
    public static function getSessionSubformField($subfromKey, $field)
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
    public static function setSessionSubformField($subfromKey, $field, $value)
    {
        self::$libraryData['subform'][$subfromKey][$field] = $value;
    }

    /**
     * Clears the subform fields
     *
     * @param
     *            none
     *            
     * @return void
     */
    public static function clearSessionSubformm()
    {
        unset(self::$libraryData['subform']);
    }

    /**
     * Gets the selected filter key
     *
     * @param
     *            none
     *            
     * @return string
     */
    public static function getSelectedFilterKey()
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
    public static function getFilterField($filterKey, $fieldName)
    {
        return self::$filtersData[$filterKey][$fieldName];
    }
}

?>