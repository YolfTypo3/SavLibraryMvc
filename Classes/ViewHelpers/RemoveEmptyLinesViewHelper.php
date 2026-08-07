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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Removes empty lines
 *
 * @package SavLibraryMvc
 */
final class RemoveEmptyLinesViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'String', false, null);
        $this->registerArgument('convertAmpersand', 'bool', '', false, false);
    }

    /**
     * Renders the view helper
     *
     * @return string
     */
    public function render(): string
    {
        // Gets the arguments
        $value = $this->arguments['value'];
        $convertAmpersand = $this->arguments['convertAmpersand'];

        if ($value === null) {
            $value = $this->renderChildren();
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
