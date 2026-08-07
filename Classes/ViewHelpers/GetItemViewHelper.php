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
 * Returns an item in an array
 *
 * @package SavLibraryMvc
 */
final class GetItemViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'array', 'Value of the parameter to change', false, null);
        $this->registerArgument('key', 'string', 'Key of the parameter to change', false, null);
        $this->registerArgument('offset', 'integer', 'Offset for the key', false, 0);
    }

    /**
     * Renders the view helper
     *
     * @return mixed
     */
    public function render(): mixed
    {
        // Gets the arguments
        $value = $this->arguments['value'];
        $key = $this->arguments['key'];
        $offset = $this->arguments['offset'];

        if ($value === null) {
            $value = $this->renderChildren();
        }

        return $value[$key + $offset] ?? null;
    }
}
