<?php
namespace YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor;

/**
 * Copyright notice
 *
 * (c) 2016 Laurent Foulloy (yolf.typo3@orange.fr)
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

/**
 * Compatibility for the rich text editor
 *
 * @package SavLibraryPlus
 */
class RichTextEditorCompatibility
{

    public static function getRichTextEditorRenderer()
    {
        if (version_compare(TYPO3_version, '8.0', '<')) {
            $richTextEditorRenderer = GeneralUtility::makeInstance(RichTextEditorRendererForTypo3VersionLowerThan8::class);
        }  elseif (version_compare(TYPO3_version, '9.0', '<')) {
            $richTextEditorRenderer = GeneralUtility::makeInstance(RichTextEditorRendererForTypo3VersionLowerThan9::class);
        }

        return $richTextEditorRenderer;
    }
}
