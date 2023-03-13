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

use YolfTypo3\SavLibraryMvc\Adders\CheckboxAdder;
use YolfTypo3\SavLibraryMvc\Utility\Conversion;

/**
 * Field configuration adder for Checkboxes type.
 */
final class CheckboxesAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];
        $addedFieldConfiguration['checkboxes'] = Conversion::integerToBooleanArray((int) $this->fieldConfiguration['value']);

        $edit = $this->fieldConfiguration['edit'] ?? false;
        if ($edit) {
            return $addedFieldConfiguration;
        }

        $values = $addedFieldConfiguration['checkboxes'];
        $renderedValues = [];
        foreach ($this->fieldConfiguration['items'] as $itemKey => $item) {
            $value = ($values[$itemKey] ? true : false);
            $renderedValues[] = CheckboxAdder::renderValueInDefaultMode($value, $this->fieldConfiguration);
        }
        $addedFieldConfiguration['renderedValues'] = $renderedValues;

        return $addedFieldConfiguration;
    }
}