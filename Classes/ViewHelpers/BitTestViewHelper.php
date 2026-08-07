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
 * Test the bit of an integer
 *
 * @package SavLibraryMvc
 */
final class BitTestViewHelper extends AbstractViewHelper
{
    
    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'int', 'The value to test', false);
        $this->registerArgument('bit', 'int', 'Bit to test', true);
    }

    /**
     * Renders the view helper
     *
     * @return int
     */
    public function render(): int
    {
        // Gets the arguments
        $value = $this->arguments['value'];
        $bit = $this->arguments['bit'];

        if ($value === null) {
            $value = $this->renderChildren();
        }

        return $value & (1 << $bit);
    }
}
