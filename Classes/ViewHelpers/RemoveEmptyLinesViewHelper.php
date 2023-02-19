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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Removes empty lines
 *
 * @package SavLibraryMvc
 */
class RemoveEmptyLinesViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'String', false, null);
        $this->registerArgument('convertAmpersand', 'bool', '', false, false);
    }

    /**
     * Renders the viewhelper
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return array The range array
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        // Gets the arguments
        $value = $arguments['value'];
        $convertAmpersand = $arguments['convertAmpersand'];

        if ($value === null) {
            $value = $renderChildrenClosure();
        }
        $parterns = [];
        $replace = [];

        $parterns[] = '/([ \t]*[\r\n]){2,}/';
        $replace[] = chr(10);
        if ($convertAmpersand) {
            $parterns[] = '/&(?!(?:amp;|quot;|gt;|lt;))/';
            $replace[] = '&amp;';
        }

        $value = preg_replace($parterns, $replace, $value);

        return trim($value);
    }
}
