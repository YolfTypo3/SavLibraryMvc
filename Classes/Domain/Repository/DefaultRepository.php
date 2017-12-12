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
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Default Repository for the SAV Library MVC
 */
class DefaultRepository extends AbstractRepository
{

    /**
     * Returns the number objects of this repository
     *
     * @return int The object count
     */
    public function countAllForListView()
    {
        // Creates the query
        $query = $this->createQuery();

        // Adds the cosntraints
        $query = $this->addConstraints($query);

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
        $maxItems = (integer) AbstractController::getSetting('maxItems');
        $limit = ($maxItems ? $maxItems : $this->countAllForListView());
        $offset = (int) $this->controller->getViewerConfiguration()->getGeneralViewConfiguration('page') * $limit;

        // Creates the query
        $query = $this->createQuery();

        // Adds the cosntraints
        $query = $this->addConstraints($query);

        // Applies the order by clause
        $arguments = AbstractController::getOriginalArguments();
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

        // Gets the result
        $result = $query->setOffset($offset)
            ->setLimit($limit ? $limit : 1)
            ->execute();

        return $result;
    }



    /**
     * Returns all objects of this repository
     *
     * @return array An array of objects, empty if no objects found
     */
    public function persistAll()
    {
        // Creates the query
        $this->persistenceManager->persistAll();
    }

    /**
     * Returns the (internal) identifier for the object, if it is known to the
     * backend.
     * Otherwise NULL is returned.
     *
     * Note: this returns an identifier even if the object has not been
     * persisted in case of AOP-managed entities. Use isNewObject() if you need
     * to distinguish those cases.
     *
     * @param object $object
     * @return mixed The identifier for the object if it is known, or NULL
     *         @api
     */
    public function getIdentifierByObject($object)
    {
        return $this->persistenceManager->getIdentifierByObject($object);
    }

    /**
     * Returns true if the record with the given uid is in the draft workspace
     *
     * @param integer $uid
     *            The uid of the record
     * @return boolean
     */
    public function isInDraftWorkspace($uid)
    {
        return FALSE;
    }
}
?>
