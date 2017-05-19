<?php
namespace SAV\SavLibraryMvc\Persistence\Mapper;

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

/**
 * Extends the generic DataMapFactory.
 */
class DataMapFactory extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
{

    /**
     *
     * @var array
     */
    protected $controlSection;

    /**
     *
     * @var array
     */
    protected $columnsDefinition;

    /**
     *
     * @var integer
     */
    protected $viewIdentifier = NULL;

    /**
     *
     * @var array
     */
    protected $savLibraryMvcConfiguration;

    /**
     *
     * @var array
     */
    protected $savLibraryMvcColumns;

    /**
     *
     * @var array
     */
    protected $savLibraryMvcCtrl;

    /**
     * Controllers configuration
     *
     * @var array
     */
    protected $savLibraryMvcControllers;

    /**
     * Initializes the data map factory
     *
     * @param string $domainObjectName
     * @return void
     */
    public function initialize($domainObjectName)
    {
        $dataMap = $this->buildDataMap($domainObjectName);
        $this->controlSection = $this->getControlSection($dataMap->getTableName());
        $this->columnsDefinition = $this->getColumnsDefinition($dataMap->getTableName());
        $this->viewIdentifier = $viewIdentifier;
        $this->setSavLibraryMvcConfiguration();
    }

    /**
     * Sets the savLibraryMvcColumns.
     *
     * @return void
     */
    protected function setSavLibraryMvcConfiguration()
    {
        $this->savLibraryMvcConfiguration = is_array($this->controlSection['EXT']['sav_library_mvc']) ? $this->controlSection['EXT']['sav_library_mvc'] : array();
        $this->savLibraryMvcColumns = is_array($this->savLibraryMvcConfiguration['columns']) ? $this->savLibraryMvcConfiguration['columns'] : array();
        $this->savLibraryMvcCtrl = is_array($this->savLibraryMvcConfiguration['ctrl']) ? $this->savLibraryMvcConfiguration['ctrl'] : array();
        $this->savLibraryMvcControllers = is_array($this->savLibraryMvcConfiguration['controllers']) ? $this->savLibraryMvcConfiguration['controllers'] : array();
    }

    /**
     * Gets the SAV Library Mvc Columns.
     *
     * @return array
     */
    public function getSavLibraryMvcColumns()
    {
        return $this->savLibraryMvcColumns;
    }

    /**
     * Gets the SAV Library Mvc Ctrl.
     *
     * @return array
     */
    public function getSavLibraryMvcCtrl()
    {
        return $this->savLibraryMvcCtrl;
    }

    /**
     * Gets the SAV Library Mvc Ctrl field.
     *
     * @param string $fieldName
     *
     * @return array
     */
    public function getSavLibraryMvcCtrlField($fieldName)
    {
        return $this->savLibraryMvcCtrl[$fieldName];
    }

    /**
     * Gets the controller name from an index.
     *
     * @param integer $index
     *
     * @return string
     */
    public function getControllerNameFromIndex($index)
    {
        $controllerNames = array_keys($this->savLibraryMvcControllers);
        $controllerName = $controllerNames[$index-1];
        return $controllerName;
    }


    /**
     * Gets the SAV Library Mvc Controller view identifiers.
     *
     * @param string $controllerName
     *
     * @return array
     */
    public function getSavLibraryMvcControllerViewIdentifiers($controllerName)
    {
        if (is_array($this->savLibraryMvcControllers[$controllerName]) && is_array($this->savLibraryMvcControllers[$controllerName]['viewIdentifiers'])) {
            return $this->savLibraryMvcControllers[$controllerName]['viewIdentifiers'];
        } else {
            return array();
        }
    }

    /**
     * Gets the SAV Library Mvc Controller view title bar.
     *
     * @param string $controllerName
     * @param string $viewType
     *
     * @return array
     */
    public function getSavLibraryMvcControllerViewTitleBar($controllerName, $viewType)
    {
        if (is_array($this->savLibraryMvcControllers[$controllerName]) && is_array($this->savLibraryMvcControllers[$controllerName]['viewTileBars'])) {
            return $this->savLibraryMvcControllers[$controllerName]['viewTileBars'][$viewType];
        } else {
            return '';
        }
    }

    /**
     * Gets the SAV Library Mvc Controller view item template.
     *
     * @param string $controllerName
     * @param string $viewType
     *
     * @return array
     */
    public function getSavLibraryMvcControllerViewItemTemplate($controllerName, $viewType)
    {
        if (is_array($this->savLibraryMvcControllers[$controllerName]) && is_array($this->savLibraryMvcControllers[$controllerName]['viewItemTemplates'])) {
            return $this->savLibraryMvcControllers[$controllerName]['viewItemTemplates'][$viewType];
        } else {
            return '';
        }
    }

    /**
     * Gets the SAV Library Mvc Controller folders.
     *
     * @param string $controllerName
     * @param string $viewType
     *
     * @return array
     */
    public function getSavLibraryMvcControllerFolders($controllerName, $viewType)
    {
        if (is_array($this->savLibraryMvcControllers[$controllerName]) && is_array($this->savLibraryMvcControllers[$controllerName]['folders'])) {
            return $this->savLibraryMvcControllers[$controllerName]['folders'][$viewType];
        } else {
            return array();
        }
    }

    /**
     * Gets the SAV Library Mvc Controller query identifier.
     *
     * @param string $controllerName
     * @param string $viewType
     *
     * @return array
     */
    public function getSavLibraryMvcControllerQueryIdentifier($controllerName)
    {
        if (is_array($this->savLibraryMvcControllers[$controllerName]) && isset($this->savLibraryMvcControllers[$controllerName]['queryIdentifier'])) {
            return $this->savLibraryMvcControllers[$controllerName]['queryIdentifier'];
        } else {
            return null;
        }
    }

    /**
     * Gets the TCAFieldConfiguration.
     *
     * @param string $fieldName
     * @return array
     */
    public function getTCAFieldConfiguration($fieldName)
    {
        return is_array($this->columnsDefinition[$fieldName]['config']) ? $this->columnsDefinition[$fieldName]['config'] : array();
    }

    /**
     * Gets the TCAFieldLabel.
     *
     * @param string $fieldName
     * @return string
     */
    public function getTCAFieldLabel($fieldName)
    {
        return $GLOBALS['TSFE']->sl($this->columnsDefinition[$fieldName]['label']);
    }

    /**
     * Gets the LabelField.
     *
     * @return string
     */
    public function getLabelField()
    {
        return $this->controlSection['label'];
    }

    /**
     * Gets the type of the field.
     *
     * @param string $fieldName
     * @return string
     */
    public function getFieldType($fieldName)
    {
        return $this->savLibraryMvcColumns[$fieldName]['fieldType'];
    }

    /**
     * Gets the SavLibraryMvc field configuration.
     *
     * @param string $fieldName
     * @return array
     */
    public function getSavLibraryMvcFieldConfiguration($fieldName)
    {
        return $this->savLibraryMvcColumns[$fieldName];
    }
}
