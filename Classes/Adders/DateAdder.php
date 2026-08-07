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

use TYPO3\CMS\Core\Localization\DateFormatter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

        // Sets the format if any
        $dateFormat = $this->fieldConfiguration['dateFormat'] ?? null;
        if (empty($dateFormat)) {
            $dateFormat = 'd/m/Y';
        }

        $value = $this->fieldConfiguration['value'];
        if (strpos($dateFormat, '%') !== false) {
            /** @var DateFormatter $dateFormatter */
            $dateFormatter = GeneralUtility::makeInstance(DateFormatter::class);
            $addedFieldConfiguration['value'] = $dateFormatter->strftime($dateFormat, (int)$value->format('U'));
        } else  {
            $addedFieldConfiguration['value'] = $value->format($dateFormat);
        }

        return $addedFieldConfiguration;
    }

}