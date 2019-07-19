<?php
namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Removes empty lines
 *
 * @package SavLibraryMvc
 */
class RemoveEmptyLinesViewHelper extends AbstractViewHelper
{
    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'String', false, null);
    }

    /**
     * Remove empty lines
     *
     * @return string The altered string.
     */
    public function render()
    {
        // Gets the arguments
        $value = $this->arguments['value'];

        if ($value === null) {
            $value = $this->renderChildren();
        }

        $value = preg_replace('/([ \t]*[\r\n]){2,}/', chr(10), $value);
        return $value;
    }




}
?>
