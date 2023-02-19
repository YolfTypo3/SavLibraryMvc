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

namespace YolfTypo3\SavLibraryMvc\Adders;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;
use YolfTypo3\SavLibraryMvc\Managers\FieldConfigurationManager;
use YolfTypo3\SavLibraryMvc\Parser\OrderByClauseParser;
use YolfTypo3\SavLibraryMvc\Parser\WhereClauseParser;

abstract class AbstractAdder
{

    /**
     * Field configuration manager
     *
     * @var FieldConfigurationManager
     */
    protected $fieldConfigurationManager;

    /**
     * Field configuration
     *
     * @var array
     */
    protected $fieldConfiguration;

    abstract public function render(): array;

    /**
     * Constructor
     *
     * @param FieldConfigurationManager $fieldConfigurationManager
     * @return void
     */
    public function __construct(FieldConfigurationManager $fieldConfigurationManager)
    {
        $this->fieldConfigurationManager = $fieldConfigurationManager;
        $this->fieldConfiguration = $fieldConfigurationManager->getFieldConfiguration();
    }

    /**
     * Gets the repository
     *
     * @return RepositoryInterface
     */
    protected function getRepository(): RepositoryInterface
    {
        $foreignModel = $this->fieldConfiguration['foreignModel'];
        $repositoryClassName = ClassNamingUtility::translateModelNameToRepositoryName($foreignModel);
        $repository = GeneralUtility::makeInstance(ltrim($repositoryClassName, '\\'));
        if (method_exists($repository, 'setController')) {
            $controller = $this->fieldConfigurationManager->getController();
            $repository->setController($controller);
        }

        return $repository;
    }

    /**
     * Gets the query for a given repositoy
     *
     * @param RepositoryInterface $repository
     * @return QueryInterface
     */
    protected function getQuery($repository): QueryInterface
    {
        // Sets the ordering if any
        if (! empty($this->fieldConfiguration['orderSelect'])) {
            $orderByClauseParser = GeneralUtility::makeInstance(OrderByClauseParser::class);
            $orderByClause = $orderByClauseParser->processOrderByClause($this->fieldConfiguration['orderSelect']);
            $repository->setDefaultOrderings($orderByClause);
        }

        // Creates the query
        $query = $repository->createQuery();

        // Adds restrictions if any
        if (! empty($this->fieldConfiguration['whereSelect'])) {
            $whereClauseParser = GeneralUtility::makeInstance(WhereClauseParser::class);
            $whereClauseParser->injectRepository($repository);
            $query = $query->matching($whereClauseParser->processWhereClause($query, $this->fieldConfiguration['whereSelect']));
        }

        return $query;
    }

    /**
     * Parses label ###field### tags.
     *
     * @param object $object
     * @param string $input
     *            The string to process     *
     * @return string
     */
    protected function parseLabel($object, string $label): string
    {
        // Checks if the value must be parsed
        $matches = [];
        if (! preg_match_all('/###([^#]+)###/', $label, $matches)) {
            $getter = 'get' . GeneralUtility::underscoredToUpperCamelCase($label);
            $label = $object->$getter();
            return $label;
        } else {
            foreach ($matches[1] as $matchKey => $match) {
                // Gets the value
                $getter = 'get' . GeneralUtility::underscoredToUpperCamelCase($match);
                $result = $object->$getter();
                $label = str_replace($matches[0][$matchKey], $result, $label);
            }
        }

        return $label;
    }

    /**
     * Gets the label from a request.
     *
     * @param string $query
     * @return array
     * @throws \Exception
     */
    protected function getLabelFromRequest(string $query): array
    {
        // Processes localization and field tags
        $query = $this->fieldConfigurationManager->parseLocalizationTags($query);
        $query = $this->fieldConfigurationManager->parseFieldTags($query);

        // Checks if the query is a select query and finds the first table in the FROM clause
        $match = [];
        if (preg_match('/^(?is:SELECT.*?FROM\s+(\w+))/', $query, $match) > 0) {
            $tableForConnection = $match[1];
        } else {
            throw new \Exception(sprintf(
                'Only SELECT query is allowed in property "reqLabel" of field "%s".',
                $this->fieldConfiguration['fieldName']
                )
            );
        }

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $rows = $connectionPool->getConnectionForTable($tableForConnection)
            ->executeQuery($query)
            ->fetchAll();
        if ($rows === null) {
            throw new \Exception(sprintf(
                'Incorrect query in property "reqLabel" of field "%s".',
                 $this->fieldConfiguration['fieldName']
                )
            );
        }

        // Processes the rows.
        $options = [];
        foreach ($rows as $row) {
            // The aliases "Label" and "Uid" must exist.
            if (array_key_exists('Label', $row) && array_key_exists('Uid', $row)) {
                $uid = $row['Uid'];
                $options[$uid] = $row['Label'];
            } else {
                throw new \Exception(sprintf(
                    'The SELECT clause of the request in property "reqLabel" must contain the aliases "Label" and "Uid" in field "%s"',
                    $this->fieldConfiguration['fieldName']
                    )
                );
            }
        }

        return $options;
    }

}