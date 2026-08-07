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

namespace YolfTypo3\SavLibraryMvc\Controller;

/**
 * Flash messages.
 */
class File extends \TYPO3\CMS\Core\Resource\File
{
    protected  \TYPO3\CMS\Core\Resource\File $originalResource;
    
    /**
     * Constructor method
     *
     * @param \TYPO3\CMS\Core\Resource\File $originalResource
     *
     */
    public function __construct(\TYPO3\CMS\Core\Resource\File $originalResource)
    {
        $this->originalResource = $originalResource;
        $this->identifier = $originalResource->identifier;
        $this->name = $originalResource->name;
        $this->properties = $originalResource->properties;
        $this->storage = $originalResource->storage;


    }
    
    /**
     * @return \TYPO3\CMS\Core\Resource\File
     */
    public function getOriginalFile(): \TYPO3\CMS\Core\Resource\File
    {
        return $this->originalResource;
    }
}
