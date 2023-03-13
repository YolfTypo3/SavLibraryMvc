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
 * Returns an item in an array
 *
 * @package SavLibraryMvc
 */
class GetItemViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'array', 'Value of the parameter to change', false, null);
        $this->registerArgument('key', 'string', 'Key of the parameter to change', false, null);
        $this->registerArgument('offset', 'integer', 'Offset for the key', false, 0);
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
        $value = $arguments['value'];
        $key = $arguments['key'];
        $offset = $arguments['offset'];

        if ($value === null) {
            $value = $renderChildrenClosure();
        }

        return $value[$key + $offset] ?? null;
    }
}
