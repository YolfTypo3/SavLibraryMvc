<?php
namespace SAV\SavLibraryMvc\Domain\Model;

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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use SAV\SavLibraryMvc\Controller\FlashMessages;

/**
 * Standard Model for the SAV Library MVC
 */
class DefaultModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Setter for uid.
     *
     * @param integer $uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Updates the file storage with the uploaded files
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $fileStorage
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $uploadedFileStorage
     * @return void
     */
    protected function updateFileStorage($fileStorage, $uploadedFileStorage)
    {
        $existingFiles = $fileStorage->toArray();
        $files = array();
        foreach ($uploadedFileStorage->toArray() as $uploadedFileKey => $uploadedFile) {
            if ($uploadedFile->_getProperty(originalResource) !== NULL) {
                $files[$uploadedFileKey] = $uploadedFile;
            } else {
                if (count($uploadedFileStorage->toArray()) === 1) {
                    $files = $existingFiles;
                } elseif (isset($existingFiles[$uploadedFileKey])) {
                    if ($existingFiles[$uploadedFileKey] !== NULL) {
                        $files[$uploadedFileKey] = $existingFiles[$uploadedFileKey];
                    }
                } else {
                    $files[$uploadedFileKey] = $uploadedFile;
                }
            }
        }
        $storage = new ObjectStorage();
        foreach ($files as $file) {
            $storage->attach($file);
        }

        return $storage;
    }

    /**
     * Resolves the table name from an object
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     * @return string
     */
    public function resolveTableNameFromObject()
    {
        // Gets the repository name
        $objectClassName = get_class($this);
        if (preg_match('/^[^\\\\]+\\\\([^\\\\]+)\\\\Domain\\\\Model\\\\(.*)$/', $objectClassName, $match)) {
            $tableName = 'tx_' . strtolower($match[1]) . '_' . GeneralUtility::camelCaseToLowerCaseUnderscored($match[2]);
        } else {
            $tableName = '';
        }
        return $tableName;
    }

    /**
     * Resolves the table name from an object
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     * @return string
     */
    public function getFieldValueFromFieldName($fieldName)
    {

        // Splits the fieldName
        $field = explode('.', $fieldName);

        if (empty($field[1])) {
            // A short field nane is used
            $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($field[0]);
        } else {
            if ($this->resolveTableNameFromObject() != $field[0]) {
                return FlashMessages::addError('error.unknownFieldName', array(
                    $fieldName
                ));
            } else {
                $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($field[1]);
            }
        }

        if (! method_exists($this, $getterName)) {
            return FlashMessages::addError('error.unknownFieldName', array(
                $fieldName
            ));
        } else {
            return $this->$getterName();
        }
    }
}
?>
