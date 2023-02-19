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
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * A view helper for rendering a the additional JavaScript header.
 *
 * = Examples =
 *
 * <code title="addAdditionalJavaScriptHeader">
 * <sav:addAdditionalJavaScriptHeader />
 * </code>
 *
 * Output:
 * none
 *
 * @package SavLibraryMvc
 */
class AddAdditionalJavaScriptHeaderViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

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
        AdditionalHeaderManager::addAdditionalJavaScriptHeader();
    }
}
