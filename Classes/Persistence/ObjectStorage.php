<?php

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

namespace YolfTypo3\SavLibraryMvc\Persistence;

class ObjectStorage extends \TYPO3\CMS\Extbase\Persistence\ObjectStorage
{

    /**
     * Moves the object one position up.
     * If it is the first one, it becomes the last one.
     *
     * @param mixed $object
     * @return void | null
     */
    public function moveDown($object)
    {
        if (! isset($this->addedObjectsPositions[spl_object_hash($object)])) {
            return null;
        }
        // Gets the next position
        $position = $this->addedObjectsPositions[spl_object_hash($object)];
        $count = count($this->addedObjectsPositions);
        $nextPosition = ($position % $count) + 1;
        $nextObjectKey = array_search($nextPosition, $this->addedObjectsPositions);

        // Exchanges the position and reorders the array
        $tempo = $this->addedObjectsPositions[spl_object_hash($object)];
        $this->addedObjectsPositions[spl_object_hash($object)] = $this->addedObjectsPositions[$nextObjectKey];
        $this->addedObjectsPositions[$nextObjectKey] = $tempo;
        asort($this->addedObjectsPositions);

        // Exchanges the storage
        $tempo = $this->storage[spl_object_hash($object)];
        $this->storage[spl_object_hash($object)] = $this->storage[$nextObjectKey];
        $this->storage[$nextObjectKey] = $tempo;

        $this->isModified = true;
    }

    /**
     * Moves the object one position down.
     * If it is the last one, it becomes the first one.
     *
     * @param mixed $object
     * @return void | null
     */
    public function moveUp($object)
    {
        if (! isset($this->addedObjectsPositions[spl_object_hash($object)])) {
            return null;
        }
        // Gets the next position
        $position = $this->addedObjectsPositions[spl_object_hash($object)];
        $count = count($this->addedObjectsPositions);
        $nextPosition = (($position + $count - 2) % $count) + 1;
        $nextObjectKey = array_search($nextPosition, $this->addedObjectsPositions);

        // Exchanges the position and reorders the array
        $tempo = $this->addedObjectsPositions[spl_object_hash($object)];
        $this->addedObjectsPositions[spl_object_hash($object)] = $this->addedObjectsPositions[$nextObjectKey];
        $this->addedObjectsPositions[$nextObjectKey] = $tempo;
        asort($this->addedObjectsPositions);

        // Exchanges the storage
        $tempo = $this->storage[spl_object_hash($object)];
        $this->storage[spl_object_hash($object)] = $this->storage[$nextObjectKey];
        $this->storage[$nextObjectKey] = $tempo;

        $this->isModified = true;
    }
}
