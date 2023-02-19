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

namespace YolfTypo3\SavLibraryMvc\Domain\Model;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;

/**
 * Standard Model for the SAV Library MVC
 */
class DefaultModel extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * The cruserIdFrontend variable
     *
     * @var int
     */
    protected $cruserIdFrontend;

    /**
     * The cruserIdFrontend variable
     *
     * @var int
     */

    /**
     * Getter for cruserId
     *
     * @return int
     */
    public function getCruserId()
    {
        return $this->cruserId;
    }

    public function getCrdate()
    {
        return $this->crdate;
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
     * Setter for uid.
     *
     * @param int $uid
     * @return void
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Updates the file storage with the uploaded file
     *
     * @param ObjectStorage $fileStorage
     * @param ObjectStorage $uploadedFileStorage
     * @param array $fieldConfiguration
     * @return void
     */
    protected function updateFileStorage($fileStorage, $uploadedFileStorage, $fieldConfiguration)
    {
        // Gets the viewId
        $viewId = $this->repository->getController()->getArguments()['viewId'];

        // Gets the configuration for the view
        $configuration = $fieldConfiguration['config'][$viewId];
        $uploadFolder = $configuration['uploadFolder'] ?? null;

        // Gets the uploaded file
        $uploadedFile = $uploadedFileStorage->current();
        if ($uploadedFile === null) {
            return $fileStorage;
        }

        $properties = $uploadedFile->_getProperties();
        if ($properties['uidLocal'] === null) {
            return $fileStorage;
        }

        // Moves the file if an upload folder is set
        if ($uploadFolder !== null) {
            // Gets the original file and identifier
            $originalFile = $uploadedFile->getOriginalResource()->getOriginalFile();
            $originalFileIdentifier = $originalFile->getPublicUrl();

            // Gets the resource storage
            $storage = $originalFile->getStorage();

            // Creates the new folder and moves the file
            if (! $storage->hasFolder($uploadFolder)) {
                $folder = $storage->createFolder($uploadFolder);
            } else {
                $folder = $storage->getFolder($uploadFolder);
            }
            $originalFile->moveTo($folder, null, DuplicationBehavior::REPLACE);

            // Deletes the upload folder
            $originalFileIdentifierPathInfo = pathinfo($originalFileIdentifier);
            GeneralUtility::rmdir(Environment::getPublicPath() . '/'. $originalFileIdentifierPathInfo['dirname']);
        }

        if ($uploadedFileStorage->count() > 0 && $uploadedFile->_getProperty('originalResource') !== null) {
            $storage = new ObjectStorage();

            // Duplicates existing files
            if ($fileStorage !== null) {
                foreach ($fileStorage as $file) {
                    $storage->attach($file);
                }
            }
            $storage->attach($uploadedFile);
        } else {
            $storage = null;
        }

        return $storage;
    }

    /**
     * Resolves the table name from an object
     *
     * @var ObjectStorage $object
     * @return string
     */
    public function resolveTableNameFromObject()
    {
        // Gets the model class name
        $objectClassName = get_class($this);
        if (preg_match('/^[^\\\\]+\\\\([^\\\\]+)\\\\Domain\\\\Model\\\\(.*)$/', $objectClassName, $match)) {
            $tableName = 'tx_' . strtolower($match[1]) . '_' . GeneralUtility::camelCaseToLowerCaseUnderscored($match[2]);
        } else {
            $tableName = '';
        }
        return $tableName;
    }

    /**
     * Resolves the repository class name
     *
     * @var string $repositoryClassName
     * @return string
     */
    public function resolveRepositoryClassName()
    {
        $objectClassName = get_class($this);
        $repositoryClassName = preg_replace('/\\\\Model\\\\(\w+)$/', '\\\\Repository\\\\$1Repository', $objectClassName);

        return $repositoryClassName;
    }

    /**
     * Gets the field value from teh field name
     *
     * @var ObjectStorage $object
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
                return FlashMessages::addError('error.unknownFieldName', [
                    $fieldName
                ]);
            } else {
                $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($field[1]);
            }
        }

        if (! method_exists($this, $getterName)) {
            return FlashMessages::addError('error.unknownFieldName', [
                $fieldName
            ]);
        } else {
            return $this->$getterName();
        }
    }
}
