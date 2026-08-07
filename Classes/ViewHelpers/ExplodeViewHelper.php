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
 * Explodes a string
 *
 * @package SavLibraryMvc
 */
final class ExplodeViewHelper extends AbstractViewHelper
{
 
    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('delimiter', 'string', 'Delimiter', true);
        $this->registerArgument('string', 'string', 'String to explode', false, null);
    }

    /**
     * Renders the view helper
     *
     * @return array the options array
     */
    public function render(): array
    {
        // Gets the arguments
        $delimiter = $this->arguments['delimiter'];
        $string = $this->arguments['string'];

        if ($string === null) {
            $string = $this->renderChildren();
        }

        return explode($delimiter, $string);
    }
}
