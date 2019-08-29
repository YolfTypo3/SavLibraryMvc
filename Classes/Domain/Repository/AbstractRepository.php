<?php
namespace YolfTypo3\SavLibraryMvc\Domain\Repository;

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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Persistence\Mapper\DataMapFactory;

/**
 * Abstract Repository for the SAV Library MVC
 */
abstract class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     *
     * @var DefaultController
     */
    protected $controller = null;

    /**
     *
     * @var DataMapFactory
     */
    protected $dataMapFactory = null;

    /**
     * Sets the controller
     *
     * @param DefaultController $controller
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Gets the controller
     *
     * @return DefaultController $controller
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
     * @return DataMapFactory
     */
    public function getDataMapFactory()
    {
        if ($this->dataMapFactory === null) {
            $this->dataMapFactory = $this->objectManager->get(DataMapFactory::class);
        }
        $this->dataMapFactory->initialize($this->resolveModelClassName());

        return $this->dataMapFactory;
    }

    /**
     * Creates a model object.
     *
     * @return AbstractEntity
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
     * @param QueryInterface $query
     * @return QueryInterface
     */
    protected function getFilterConstraints($query)
    {
        // Gets the session variables
        $sessionFilters = $GLOBALS['TSFE']->fe_user->getKey('ses', 'filters');
        $selectedFilterKey = $GLOBALS['TSFE']->fe_user->getKey('ses', 'selectedFilterKey');
        if (! empty($sessionFilters) && ! empty($selectedFilterKey) && ! empty($sessionFilters[$selectedFilterKey]) && $sessionFilters[$selectedFilterKey]['Mvc']['pageId'] == $this->getPageId()) {
            $filterClassName = $sessionFilters[$selectedFilterKey]['Mvc']['filterClassName'];
            return $filterClassName::getFilterWhereClause($query);
        } else {
            return null;
        }
    }

    /**
     * Gets the filter constraints if any.
     *
     * @return boolean
     */
    protected function keepWhereClause()
    {
        // Gets the session variables
        $sessionFilters = $GLOBALS['TSFE']->fe_user->getKey('ses', 'filters');
        $sessionSelectedFilter = $GLOBALS['TSFE']->fe_user->getKey('ses', 'selectedFilter');
        if (! empty($sessionFilters) && ! empty($sessionSelectedFilter) && ! empty($sessionFilters[$sessionSelectedFilter]) && $sessionFilters[$sessionSelectedFilter]['pageId'] == $this->getPageId()) {
            return $sessionSelectedFilter::keepWhereClause();
        } else {
            return null;
        }
    }

    /**
     * Adds constraints to the query
     *
     * @param QueryInterface $query
     * @return QueryInterface
     */
    protected function addConstraints($query)
    {
        // Gets filter constraints if any
        $filterConstraints = $this->getFilterConstraints($query);

        // Gets the where clause constraints
        $whereClauseConstraints = $this->whereClause($query);

        $finalConstraints = [];
        if ($filterConstraints === null) {
            // Applies the where clause
            if ($whereClauseConstraints !== null) {
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

        if (! empty($finalConstraints)) {
            $query = $query->matching($query->logicalAnd($finalConstraints));
        }

        return $query;
    }

    /**
     * Defines the order by clause
     *
     * @param QueryInterface $query
     * @return void
     */
    protected function orderByClause($query)
    {
        $controllerName = $this->getController()->getControllerName();
        $queryIdentifier = $this->dataMapFactory->getSavLibraryMvcControllerQueryIdentifier($controllerName);
        if ($queryIdentifier !== null) {
            $orderByClauseMethod = 'orderByClause' . $queryIdentifier;
            if (method_exists($this, $orderByClauseMethod)) {
                return $this->$orderByClauseMethod($query);
            }
        }

        return null;
    }

    /**
     * Defines the where clause
     *
     * @param QueryInterface $query
     * @return void
     */
    protected function whereClause($query)
    {
        $controllerName = $this->getController()->getControllerName();
        $queryIdentifier = $this->dataMapFactory->getSavLibraryMvcControllerQueryIdentifier($controllerName);
        if ($queryIdentifier !== null) {
            $whereClauseMethod = 'whereClause' . $queryIdentifier;
            if (method_exists($this, $whereClauseMethod)) {
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
    protected function getPageId()
    {
        return $GLOBALS['TSFE']->id;
    }
}
?>
