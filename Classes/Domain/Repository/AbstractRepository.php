<?php
namespace SAV\SavLibraryMvc\Domain\Repository;

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
 * Abstract Repository for the SAV Library MVC
 */
abstract class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     *
     * @var \SAV\SavLibraryMvc\Controller\DefaultController
     */
    protected $controller = NULL;

    /**
     *
     * @var \SAV\SavLibraryMvc\Persistence\Mapper\DataMapFactory
     */
    protected $dataMapFactory = NULL;

    /**
     * Sets the controller
     *
     * @param \SAV\SavLibraryMvc\Controller\DefaultController $controller
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Gets the controller
     *
     * @return \SAV\SavLibraryMvc\Controller\DefaultController $controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Resolves the model class name
     *
     * @var string $repositoryClassName
     * @return string
     */
    public function resolveModelClassName($repositoryClassName = '')
    {
        if ($repositoryClassName == '') {
            $repositoryClassName = $this->getRepositoryClassName();
        }
        $modelClassName = preg_replace('/\\\\Repository\\\\(\w+)Repository$/', '\\\\Model\\\\$1', $repositoryClassName);

        return $modelClassName;
    }

    /**
     * Resolves the repository class name from the table name
     *
     * @var string $tableName
     * @return string
     */
    public static function resolveRepositoryClassNameFromTableName($tableName)
    {
        // Gets the repository name
        $repositoryName = preg_replace('/^tx_[^_]+_domain_model_(.+)$/', '$1', $tableName);
        $repositoryName = GeneralUtility::underscoredToUpperCamelCase($repositoryName);

        // Gets the repository class name
        $repositoryClassName = preg_replace('/^(.+?)\\\\Controller\\\\.+$/', '$1\\\\Domain\\\\Repository\\\\' . $repositoryName . 'Repository', AbstractController::getControllerObjectName());

        return $repositoryClassName;
    }

    /**
     * Gets the Data map factory.
     *
     * return \SAV\SavLibraryMvc\Persistence\Mapper\DataMapFactory
     */
    public function getDataMapFactory()
    {
        if ($this->dataMapFactory === NULL) {
            $this->dataMapFactory = $this->objectManager->get('SAV\\SavLibraryMvc\\Persistence\\Mapper\\DataMapFactory');
            $this->dataMapFactory->initialize($this->resolveModelClassName());
        }
        return $this->dataMapFactory;
    }

    /**
     * Creates a model object.
     *
     * return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
     */
    public function createModelObject()
    {
        $object = $this->objectManager->get($this->resolveModelClassName());

        // TODO Check before return
        return $object;
    }
}
?>
