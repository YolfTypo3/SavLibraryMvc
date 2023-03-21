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

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Field configuration adder for Date type.
 */
final class DateAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];

        $edit = $this->fieldConfiguration['edit'] ?? false;
        if ($edit) {
            return $addedFieldConfiguration;
        }

        // Sets the format if any
        $format = $this->fieldConfiguration['format'] ?? null;
        if (empty($format)) {
            $format = 'd/m/Y';
        }

        $value = $this->fieldConfiguration['value'];
        if (strpos($format, '%') !== false) {
            $addedFieldConfiguration['value'] =  strftime($format, (int)$value->format('U'));
        } else  {
            $addedFieldConfiguration['value'] = $value->format($format);
        }

        return $addedFieldConfiguration;
    }

}