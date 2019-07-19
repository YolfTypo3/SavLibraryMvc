<?php
namespace YolfTypo3\SavLibraryMvc\Domain\Model;

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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;

/**
 * Standard Model for the SAV Library MVC
 */
class DefaultModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * The crdate variable
     *
     * @var int
     */
    protected $crdate;

    /**
     * The cruserId variable
     *
     * @var int
     */
    protected $cruserId;

    /**
     * The cruserIdFrontend variable
     *
     * @var int
     */
    protected $cruserIdFrontend;

    /**
     * Last modified time
     *
     * @var int
     */
    protected $tstamp;

    /**
     * Getter for crdate
     *
     * @return int
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Getter for cruserId
     *
     * @return int
     */
    public function getCruserId()
    {
        return $this->cruserId;
    }

    /**
     * Setter for setCruserIdFrontend
     *
     * @param int $cruserIdFrontend
     */
    public function setCruserIdFrontend($cruserIdFrontend)
    {
        $this->cruserIdFrontend = $cruserIdFrontend;
    }

    /**
     * Getter for cruserIdFrontend
     *
     * @return int
     */
    public function getCruserIdFrontend()
    {
        return $this->cruserIdFrontend;
    }

    /**
     * Getter for tstamp
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

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
        $files = [];
        foreach ($uploadedFileStorage->toArray() as $uploadedFileKey => $uploadedFile) {
            if ($uploadedFile !== null) {
                if ($uploadedFile->_getProperty('originalResource') !== null) {
                    $files[$uploadedFileKey] = $uploadedFile;
                } else {
                    $existingFiles = $fileStorage->toArray();
                    if (count($uploadedFileStorage->toArray()) === 1) {
                        $files = $existingFiles;
                    } elseif (isset($existingFiles[$uploadedFileKey])) {
                        if ($existingFiles[$uploadedFileKey] !== null) {
                            $files[$uploadedFileKey] = $existingFiles[$uploadedFileKey];
                        }
                    } else {
                        $files[$uploadedFileKey] = $uploadedFile;
                    }
                }
            }
        }

        if (count($files) > 0) {
            $storage = new ObjectStorage();
            // Duplicates existing files
            if ($fileStorage !== null) {
                foreach ($fileStorage as $file) {
                    $storage->attach($file);
                }
            }
            // Adds the uploaded files
            foreach ($files as $file) {
                $storage->attach($file);
            }
        } else {
            $storage = null;
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
                return FlashMessages::addError(
                    'error.unknownFieldName',
                    [
                        $fieldName
                    ]
                );
            } else {
                $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($field[1]);
            }
        }

        if (! method_exists($this, $getterName)) {
            return FlashMessages::addError(
                'error.unknownFieldName',
                [
                    $fieldName
                ]
            );
        } else {
            return $this->$getterName();
        }
    }
}
?>
