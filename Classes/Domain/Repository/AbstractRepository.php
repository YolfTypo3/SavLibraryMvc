<?php
namespace YolfTypo3\SavLibraryMvc\Domain\Repository;

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
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Persistence\Mapper\DataMapFactory;

/**
 * Abstract Repository for the SAV Library MVC
 */
abstract class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     *
     * @var \YolfTypo3\SavLibraryMvc\Controller\DefaultController
     */
    protected $controller = NULL;

    /**
     *
     * @var \YolfTypo3\SavLibraryMvc\Persistence\Mapper\DataMapFactory
     */
    protected $dataMapFactory = NULL;

    /**
     * Sets the controller
     *
     * @param \YolfTypo3\SavLibraryMvc\Controller\DefaultController $controller
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Gets the controller
     *
     * @return \YolfTypo3\SavLibraryMvc\Controller\DefaultController $controller
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
     * return \YolfTypo3\SavLibraryMvc\Persistence\Mapper\DataMapFactory
     */
    public function getDataMapFactory()
    {
        if ($this->dataMapFactory === NULL) {
            $this->dataMapFactory = $this->objectManager->get(DataMapFactory::class);
        }
        $this->dataMapFactory->initialize($this->resolveModelClassName());

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

        // TODO Add an error message
        return $object;
    }

    /**
     * Gets the filter constraints if any.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    protected function getFilterConstraints($query) {
        // Gets the session variables
        $sessionFilters = $GLOBALS['TSFE']->fe_user->getKey('ses', 'filters');
        $sessionSelectedFilter = $GLOBALS['TSFE']->fe_user->getKey('ses', 'selectedFilter');
        if (!empty($sessionFilters) && !empty($sessionSelectedFilter) && !empty($sessionFilters[$sessionSelectedFilter]) && $sessionFilters[$sessionSelectedFilter]['pageId'] == $this->getPageId()) {
            return $sessionSelectedFilter::getFilterWhereClause($query);
        } else {
            return null;
        }
    }

    /**
     * Gets the filter constraints if any.
     *
     * return boolean
     */
    protected function keepWhereClause() {
        // Gets the session variables
        $sessionFilters = $GLOBALS['TSFE']->fe_user->getKey('ses', 'filters');
        $sessionSelectedFilter = $GLOBALS['TSFE']->fe_user->getKey('ses', 'selectedFilter');
        if (!empty($sessionFilters) && !empty($sessionSelectedFilter) && !empty($sessionFilters[$sessionSelectedFilter]) && $sessionFilters[$sessionSelectedFilter]['pageId'] == $this->getPageId()) {
            return $sessionSelectedFilter::keepWhereClause();
        } else {
            return null;
        }
    }

    /**
     * Adds constraints to the query
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    protected function addConstraints($query)
    {
        // Gets filter constraints if any
        $filterConstraints = $this->getFilterConstraints($query);

        // Gets the where clause constraints
        $whereClauseConstraints = $this->whereClause($query);

        $finalConstraints = array();
        if ($filterConstraints === null) {
            // Applies the where clause
            if($whereClauseConstraints !== null) {
                $finalConstraints[] = $whereClauseConstraints;
            }
        } else {
            // Applies the filter constraints
            $finalConstraints[] = $filterConstraints;
            // Applies the where clause constraints if required
            if ($this->keepWhereClause() && $whereClauseConstraints !== null) {
                $finalConstraints[] = $whereClauseConstraints;
            }
        }

        if (!empty($finalConstraints)) {
            $query = $query->matching($query->logicalAnd($finalConstraints));
        }

        return $query;
    }

    /**
     * Defines the order by clause
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @return void
     */
    protected function orderByClause($query)
    {
        $controllerName = $this->getController()->getControllerName();
        $queryIdentifier = $this->dataMapFactory->getSavLibraryMvcControllerQueryIdentifier($controllerName);
        if ($queryIdentifier !== null) {
            $orderByClauseMethod = 'orderByClause' . $queryIdentifier;
            if(method_exists($this, $orderByClauseMethod)) {
                return $this->$orderByClauseMethod($query);
            }
        }

        return null;
    }

    /**
     * Defines the where clause
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @return void
     */
    protected function whereClause($query)
    {
        $controllerName = $this->getController()->getControllerName();
        $queryIdentifier = $this->dataMapFactory->getSavLibraryMvcControllerQueryIdentifier($controllerName);
        if ($queryIdentifier !== NULL) {
            $whereClauseMethod = 'whereClause' . $queryIdentifier;
            if(method_exists($this, $whereClauseMethod)) {
                return $this->$whereClauseMethod($query);
            }
        }
        return null;
    }

    /**
     * Gets the page id
     *
     * @return integer
     */
    protected function getPageId() {
        return $GLOBALS['TSFE']->id;
    }

}
?>
