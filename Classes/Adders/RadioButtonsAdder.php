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

namespace YolfTypo3\SavLibraryMvc\Adders;

/**
 * Field configuration adder for RadioButtons type.
 */
final class RadioButtonsAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];

        $horizontalLayout = $this->fieldConfiguration['horizontalLayout'] ?? false;
        if ($horizontalLayout) {
            $addedFieldConfiguration['cols'] = count($this->fieldConfiguration['items']);
        }

        return $addedFieldConfiguration;
    }
}