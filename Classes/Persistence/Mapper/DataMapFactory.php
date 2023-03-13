<?php

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

namespace YolfTypo3\SavLibraryMvc\Persistence\Mapper;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMap;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;

/**
 * Extends the generic DataMapFactory.
 */
class DataMapFactory extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory
{

    /**
     * Controller
     *
     * @var DefaultController
     */
    protected $controller = null;

    /**
     * The data map
     *
     * @var DataMap
     */
    protected $dataMap;

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
     * @var int
     */
    protected $viewIdentifier = null;

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
     * Initializes the data map factory
     *
     * @param string $domainObjectName
     * @return void
     */
    public function initialize($domainObjectName)
    {
        $this->dataMap = $this->buildDataMap($domainObjectName);
        $this->controlSection = $this->getControlSection($this->dataMap->getTableName());
        $this->columnsDefinition = $this->getColumnsDefinition($this->dataMap->getTableName());
        $this->setSavLibraryMvcConfiguration();
    }

    /**
     * Sets the savLibraryMvcColumns.
     *
     * @return void
     */
    protected function setSavLibraryMvcConfiguration()
    {
        $extensionKey = $this->controller->getControllerExtensionKey();
        $this->savLibraryMvcConfiguration = is_array($this->controlSection['EXT'][$extensionKey]) ? $this->controlSection['EXT'][$extensionKey] : [];
        $this->savLibraryMvcColumns = is_array($this->savLibraryMvcConfiguration['columns']) ? $this->savLibraryMvcConfiguration['columns'] : [];
        $this->savLibraryMvcCtrl = is_array($this->savLibraryMvcConfiguration['ctrl']) ? $this->savLibraryMvcConfiguration['ctrl'] : [];
    }

    /**
     * Gets the SAV Library Mvc Columns.
     *
     * @return array
     */
    public function getSavLibraryMvcColumns(): array
    {
        return $this->savLibraryMvcColumns;
    }

    /**
     * Gets the SAV Library Mvc Ctrl.
     *
     * @return array
     */
    public function getSavLibraryMvcCtrl(): array
    {
        return $this->savLibraryMvcCtrl;
    }

    /**
     * Gets the SAV Library Mvc Ctrl field.
     *
     * @param string $fieldName
     *
     * @return mixed|null
     */
    public function getSavLibraryMvcCtrlField($fieldName)
    {
        return $this->savLibraryMvcCtrl[$fieldName] ?? null;
    }

    /**
     * Gets the TCAFieldConfiguration.
     *
     * @param string $fieldName
     * @return array
     */
    public function getTCAFieldConfiguration($fieldName): array
    {
        return $this->columnsDefinition[$fieldName]['config'] ?? [];
    }

    /**
     * Gets the TCAFieldLabel.
     *
     * @param string $fieldName
     * @return string
     */
    public function getTCAFieldLabel($fieldName)
    {
        $propertyName = GeneralUtility::underscoredToLowerCamelCase($fieldName);
        $columnMap = $this->dataMap->getColumnMap($propertyName);
        if ($columnMap === null) {
            throw new \Exception(sprintf(
                'Unknown columnMap for property "%s".',
                $propertyName
                )
            );
        }
        $columnName = $columnMap->getColumnName();

        return $GLOBALS['TSFE']->sl($this->columnsDefinition[$columnName]['label']);
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
    public function getFieldType($fieldName): string
    {
        return $this->savLibraryMvcColumns[$fieldName]['fieldType'] ?? '';
    }

    /**
     * Gets the foreign model.
     *
     * @param string $fieldName
     * @return string
     */
    public function getForeignModel($fieldName): string
    {
        return $this->savLibraryMvcColumns[$fieldName]['foreignModel'] ?? '';
    }

    /**
     * Gets the render type of the field.
     *
     * @param string $fieldName
     * @return string
     */
    public function getRenderType($fieldName)
    {
        return $this->savLibraryMvcColumns[$fieldName]['renderType'];
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
