<?php
namespace SAV\SavLibraryMvc\Compatibility;

/*
 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid". *
 * *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU Lesser General Public License, either version 3 *
 * of the License, or (at your option) any later version. *
 * *
 * The TYPO3 project - inspiring people to share! *
 */

/**
 * Utility class for the compatibility
 *
 * @api
 */
class CompatibilityUtility
{

    /**
     * Sets class aliases according to the TYPO3 version
     *
     * @return void
     */
    public static function setClassAliases()
    {
        if (version_compare(TYPO3_version, '7.0', '<')) {
            class_alias('SAV\\SavLibraryMvc\\ViewHelpers\\Link\\TypolinkViewHelper', 'TYPO3\CMS\Fluid\ViewHelpers\Link\TypolinkViewHelper');
            class_alias('SAV\\SavLibraryMvc\\ViewHelpers\\Form\\RteForTypo3VersionLowerThan7ViewHelper', 'SAV\\SavLibraryMvc\\ViewHelpers\\Form\\RteViewHelper');
        }
    }
}
