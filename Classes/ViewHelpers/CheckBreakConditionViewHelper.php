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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * A view helper for building the options for the field selector.
 *
 * = Examples =
 *
 * <code title="CheckBreakCondition">
 * <sav:CheckBreakCondition />
 * </code>
 *
 * Output:
 */
class CheckBreakConditionViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('counter', 'integer', 'Counter', false, null);
        $this->registerArgument('breakCount', 'integer', 'Break count', false, null);
    }

    /**
     * Renders the viewhelper
     *
     * @return boolean the break condition
     */
    public function render()
    {
        // Gets the arguments
        $counter = $this->arguments['counter'];
        $breakCount = $this->arguments['breakCount'];

        if ($counter === null) {
            $counter = $this->renderChildren();
        }
        if ($breakCount != 0) {
            return (($counter % $breakCount) == 0);
        } else {
            return true;
        }
    }
}
?>

