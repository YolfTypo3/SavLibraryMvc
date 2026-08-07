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
final class InViewHelper extends AbstractViewHelper
{
    
    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('list', 'string', 'The comma-separated list', true);
        $this->registerArgument('key', 'string', 'The key', false);
    }

    /**
     * Renders the view helper
     *
     * @return bool
     */
    public function render(): bool
    {
        // Gets the arguments
        $list = $this->arguments['list'];
        $key = $this->arguments['key'];

        if ($key === null) {
            $key = $this->renderChildren();
        }

        return in_array($key, explode(',', $list));
    }
}
