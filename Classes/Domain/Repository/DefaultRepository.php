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

namespace YolfTypo3\SavLibraryMvc\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Parser\WhereClauseParser;
use YolfTypo3\SavLibraryMvc\Persistence\Mapper\DataMapFactory;

/**
 * Default Repository for the SAV Library MVC
 */
class DefaultRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
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
     *
     * @var array
     */
    protected $sessionFilter = [];

    /**
     * Injects the data map factory
     *
     * @param DataMapFactory $dataMapFactory
     * @return void
     */
    public function injectDataMapFactory(DataMapFactory $dataMapFactory)
    {
        $this->dataMapFactory = $dataMapFactory;
    }

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
     * Gets the controller
     *
     * @return DefaultController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Gets the object type
     *
     * @return string
     */
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * Gets the persistence manager
     *
     * @return PersistenceManagerInterface
     */
    public function getPersistenceManager(): PersistenceManagerInterface
    {
        return $this->persistenceManager;
    }

    /**
     * Resolves the model class name
     *
     * @var string $repositoryClassName
     * @return string
     */
    public function resolveModelClassName(string $repositoryClassName = ''): string
    {
        if ($repositoryClassName == '') {
            $repositoryClassName = $this->getRepositoryClassName();
        }
        $modelClassName = preg_replace('/\\\\Repository\\\\(\w+)Repository$/', '\\\\Model\\\\$1', $repositoryClassName);

        return $modelClassName;
    }

    /**
     * Gets the Data map factory.
     *
     * @return DataMapFactory
     */
    public function getDataMapFactory(): DataMapFactory
    {
        $this->dataMapFactory->setController($this->controller);
        $this->dataMapFactory->initialize($this->resolveModelClassName());

        return $this->dataMapFactory;
    }

    /**
     * Creates a model object.
     *
     * @return AbstractEntity
     */
    public function createModelObject(): AbstractEntity
    {
        $object = GeneralUtility::makeInstance($this->resolveModelClassName());

        // TODO Add an error message
        return $object;
    }

    /**
     * Returns the number objects of this repository
     *
     * @return int The object count
     */
    public function countAllForListView(): int
    {
        // Creates the query
        $query = $this->createQuery();

        // Adds the constraints
        $query = $this->addConstraints($query);

        //         $this->debugQuery($query);

        return $query->execute()->count();
    }

    /**
     * Returns all objects of this repository
     *
     * @return array An array of objects, empty if no objects found
     */
    public function findAllForListView()
    {
        // Sets the limit
        $maxItems = (int) $this->controller->getSetting('maxItems');
        $limit = ($maxItems ? $maxItems : $this->countAllForListView());
        $offset = (int) $this->controller->getViewerConfiguration()->getGeneralViewConfiguration('page') * $limit;

        // Creates the query
        $query = $this->createQuery();

        // Adds the constraints
        $query = $this->addConstraints($query);

        // Applies the order by clause
        $arguments = $this->controller->getArguments();
        $uncompressedParameters = AbstractController::uncompressParameters($arguments['special']);
        // Checks if there is an order link
        if (isset($uncompressedParameters['orderLink'])) {
            $orderByMethod = 'orderByClauseForWhereTag' . $uncompressedParameters['orderLink'];
            if (method_exists($this, $orderByMethod)) {
                // Applies the where tag ordering
                $this->$orderByMethod($query);
            } else {
                // Applies the default ordering
                $query = $this->orderByClause($query);
            }
        } else {
            // Applies the default ordering
            $this->orderByClause($query);
        }

        // Adds the limit the result
        $query = $query->setOffset($offset)->setLimit($limit ? $limit : 1);

        //          $this->debugQuery($query);
        return $query->execute();
    }

    /**
     * Commits new objects and changes
     *
     * @return void
     */
    public function persistAll()
    {
        $this->persistenceManager->persistAll();
    }

    /**
     * Returns the (internal) identifier for the object, if it is known to the
     * backend.
     * Otherwise null is returned.
     *
     * Note: this returns an identifier even if the object has not been
     * persisted in case of AOP-managed entities. Use isNewObject() if you need
     * to distinguish those cases.
     *
     * @param object $object
     * @return mixed The identifier for the object if it is known, or null
     */
    public function getIdentifierByObject($object)
    {
        return $this->persistenceManager->getIdentifierByObject($object);
    }

    /**
     * Returns true if the record with the given uid is in the draft workspace
     *
     * @param int $uid
     *            The uid of the record
     * @return boolean
     */
    public function isInDraftWorkspace(int $uid): bool
    {
        return false;
    }

    /**
     * Gets the filter constraints if any.
     *
     * @param QueryInterface $query
     * @return QueryInterface|null
     */
    protected function getFilterConstraints(QueryInterface $query): ?ConstraintInterface
    {
        // Gets the session variables
        $sessionFilters = $GLOBALS['TSFE']->fe_user->getKey('ses', 'filters');
        $selectedFilterKey = $GLOBALS['TSFE']->fe_user->getKey('ses', 'selectedFilterKey');

        if (! empty($sessionFilters) && ! empty($selectedFilterKey) && ! empty($sessionFilters[$selectedFilterKey]) && $sessionFilters[$selectedFilterKey]['pageId'] == $this->getPageId()) {
            //Sets the selected session filter
            $this->selectedSessionFilter = $sessionFilters[$selectedFilterKey];

            // Gets the constraints for the selected filter
            $clause = $this->selectedSessionFilter['addWhere'];

            /** @var WhereClauseParser $whereClauseParser */
            $whereClauseParser = GeneralUtility::makeInstance(WhereClauseParser::class);
            $whereClauseParser->injectRepository($this);
            $constraints = $whereClauseParser->processWhereClause($query, $clause);

            return $constraints;
        } elseif (! $this->controller->getSetting('noFilterShowAll')) {
            return $query->equals('uid', 0);
        }

        return null;
    }

    /**
     * Gets the filter constraints if any.
     *
     * @return boolean
     */
    protected function keepWhereClause(): bool
    {
        if (!isset($this->selectedSessionFilter['keepWhereClause'])) {
            return false;
        }
        return $this->selectedSessionFilter['keepWhereClause'];
    }

    /**
     * Adds constraints to the query
     *
     * @param QueryInterface $query
     * @return QueryInterface
     */
    protected function addConstraints(QueryInterface $query): QueryInterface
    {
        // Gest the permanent filter constraints if any
        $permanentFilterClause = $this->controller->getSetting('permanentFilter');
        if (!empty($permanentFilterClause)) {
            $whereClauseParser = GeneralUtility::makeInstance(WhereClauseParser::class);
            $whereClauseParser->injectRepository($this);
            $permanentFilterConstraints = $whereClauseParser->processWhereClause($query, $permanentFilterClause);
            if ($permanentFilterConstraints !== null) {
                $finalConstraints[] = $permanentFilterConstraints;
            }
        }

        // Gets the where clause constraints
        $whereClauseConstraints = $this->whereClause($query);

        // Gets filter constraints if any
        $filterConstraints = $this->getFilterConstraints($query);

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
     * @return QueryInterface|null
     */
    protected function orderByClause(QueryInterface $query): ?QueryInterface
    {
        $queryIdentifier = $this->controller->getQueryIdentifier();
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
     * @return QueryInterface|null
     */
    protected function whereClause(QueryInterface $query): ?ConstraintInterface
    {
        $queryIdentifier = $this->controller->getQueryIdentifier();
        if ($queryIdentifier !== null) {
            $whereClauseMethod = 'whereClause' . $queryIdentifier;
            if (method_exists($this, $whereClauseMethod)) {
                return $this->$whereClauseMethod($query);
            }
        }
        return null;
    }

    /**
     * Checks if the user is member of a group
     *
     * @param string $groupName
     *
     * @return bool (true if the current user is a member of the group)
     */
    protected function isGroupMember(string $groupName): bool
    {
        if (empty($groupName)) {
            return false;
        }

        return is_array($GLOBALS['TSFE']->fe_user->groupData['title']) && in_array($groupName, $GLOBALS['TSFE']->fe_user->groupData['title']);
    }

    /**
     * Checks if the user is member of a group
     *
     * @param string $groupName
     *
     * @return bool (true if the current user is not a member of the group)
     */
    protected static function isNotGroupMember(string $groupName): bool
    {
        if (empty($groupName)) {
            return true;
        }

        return is_array($GLOBALS['TSFE']->fe_user->groupData['title']) && ! in_array($groupName, $GLOBALS['TSFE']->fe_user->groupData['title']);
    }


    /**
     * Gets the page id
     *
     * @return int
     */
    protected function getPageId(): int
    {
        // @extensionScannerIgnoreLine
        return (int) $GLOBALS['TSFE']->id;
    }

    /**
     * Gets the userd id
     *
     * @return int
     */
    protected function getUserId(): int
    {
        // @extensionScannerIgnoreLine
        return (int) $GLOBALS['TSFE']->fe_user->user['uid'];
    }

    /**
     * Debug a query
     *
     * @param QueryInterface $query
     * @return void
     */
    public function debugQuery($query)
    {
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $dbParser = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);
        $environmentService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\EnvironmentService::class);
        $dbParser->injectObjectManager($objectManager);
        $dbParser->injectEnvironmentService($environmentService);
        $dbParser->initializeObject();
        $builder = $dbParser->convertQueryToDoctrineQueryBuilder($query);
        debug([$builder->getSQL(), $builder->getParameters()]);

    }

}
