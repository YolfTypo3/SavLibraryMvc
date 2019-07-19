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
     * Otherwise null is returned.
     *
     * Note: this returns an identifier even if the object has not been
     * persisted in case of AOP-managed entities. Use isNewObject() if you need
     * to distinguish those cases.
     *
     * @param object $object
     * @return mixed The identifier for the object if it is known, or null
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
        return false;
    }
}
?>
