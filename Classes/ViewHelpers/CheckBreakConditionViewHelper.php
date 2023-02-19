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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
    use CompileWithRenderStatic;

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
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return array the options array
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        // Gets the arguments
        $counter = $arguments['counter'];
        $breakCount = $arguments['breakCount'];

        if ($counter === null) {
            $counter = $renderChildrenClosure();
        }
        if ($breakCount != 0) {
            return (($counter % $breakCount) == 0);
        } else {
            return true;
        }
    }
}
