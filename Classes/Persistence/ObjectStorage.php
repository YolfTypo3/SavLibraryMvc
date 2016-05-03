<?php
namespace SAV\SavLibraryMvc\Persistence;

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
class ObjectStorage extends \TYPO3\CMS\Extbase\Persistence\ObjectStorage
{

    /**
     * Moves the object one position up.
     * If it is the first one, it becomes the last one.
     *
     * @param mixed $object            
     * @return void | NULL
     */
    public function moveDown($object)
    {
        if (! isset($this->addedObjectsPositions[spl_object_hash($object)])) {
            return NULL;
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
        
        $this->isModified = TRUE;
    }

    /**
     * Moves the object one position down.
     * If it is the last one, it becomes the first one.
     *
     * @param mixed $object            
     * @return void | NULL
     */
    public function moveUp($object)
    {
        if (! isset($this->addedObjectsPositions[spl_object_hash($object)])) {
            return NULL;
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
        
        $this->isModified = TRUE;
    }
}
