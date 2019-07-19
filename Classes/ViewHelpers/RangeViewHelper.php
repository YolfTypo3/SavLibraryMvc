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
 * Returns an array of numbers
 *
 * @package SavLibraryMvc
 */
class RangeViewHelper extends AbstractViewHelper
{
    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('low', 'integer', 'Low value', true);
        $this->registerArgument('high', 'integer', 'High value', true);
        $this->registerArgument('step', 'integer', 'Step value', false, 1);
    }

    /**
     * Range viewhelper.
     *
     * @return array The range array
     */
    public function render()
    {
        // Gets the arguments
        $low = $this->arguments['low'];
        $high = $this->arguments['high'];
        $step = $this->arguments['step'];

        return range($low, $high, $step);
    }
}
?>
