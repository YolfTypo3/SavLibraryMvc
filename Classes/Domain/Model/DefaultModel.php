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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;

/**
 * Standard Model for the SAV Library MVC
 */
class DefaultModel extends AbstractEntity
{

    /**
     * The cruserIdFrontend variable
     *
     * @var int
     */
    protected $cruserIdFrontend;

    /**
     * Getter for cruserId
     *
     * @return int
     */
    public function getCruserId(): int
    {
        return $this->cruserId;
    }

    /**
     * Getter for crdate
     *
     * @return int
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }

    /**
     * Setter for setCruserIdFrontend
     *
     * @param int $cruserIdFrontend
     * 
     * @return void
     */
    public function setCruserIdFrontend(int $cruserIdFrontend): void
    {
        $this->cruserIdFrontend = $cruserIdFrontend;
    }

    /**
     * Getter for cruserIdFrontend
     *
     * @return int
     */
    public function getCruserIdFrontend(): int
    {
        return $this->cruserIdFrontend;
    }

    /**
     * Setter for uid.
     *
     * @param int $uid
     * 
     * @return void
     */
    public function setUid(?int $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * Resolves the table name from an object
     * 
     * @return string
     */
    public function resolveTableNameFromObject(): string
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
     * @return string
     */
    public function resolveRepositoryClassName(): string
    {
        $objectClassName = get_class($this);
        $repositoryClassName = preg_replace('/\\\\Model\\\\(\w+)$/', '\\\\Repository\\\\$1Repository', $objectClassName);

        return $repositoryClassName;
    }

    /**
     * Gets the field value from teh field name
     *
     * @var string $fieldName
     * 
     * @return string
     */
    public function getFieldValueFromFieldName(string $fieldName): string
    {
        // Splits the fieldName
        $field = explode('.', $fieldName);

        if (empty($field[1])) {
            // A short field name is used
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
            FlashMessages::addError('error.unknownFieldName', [
                $fieldName
            ]);
            return '';
        } else {
            return $this->$getterName();
        }
    }
}
