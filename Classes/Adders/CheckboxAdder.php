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
 * Field configuration adder for Checkbox type.
 */
final class CheckboxAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];

        if ($this->fieldConfiguration['edit']) {
            return $addedFieldConfiguration;
        }

        $value = $this->fieldConfiguration['value'];
        $addedFieldConfiguration['renderedValue'] = self::renderValueInDefaultMode($value, $this->fieldConfiguration);

        return $addedFieldConfiguration;
    }

    /**
     * Renders the value in default mode
     *
     * @param bool $value
     * @param array $fieldConfiguration
     * @return mixed
     */
    public static function renderValueInDefaultMode(bool $value, array $fieldConfiguration)
    {
        if ($value) {
            // The checkbox is checked
            if ($fieldConfiguration['displayAsImage']) {
                if ($fieldConfiguration['checkboxSelectedImage']) {
                    $iconIdentifier = $fieldConfiguration['checkboxSelectedImage'];
                } else {
                    $iconIdentifier = 'checkbox-checked';
                }
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $renderedValue = $iconFactory->getIcon(
                    $iconIdentifier,
                    Icon::SIZE_SMALL
                );
            } else {
                $renderedValue = LocalizationUtility::translate('itemviewer.yes', 'sav_library_mvc');
            }
        } else {
            // The checkbox is not checked and must not be displayed
            if ($fieldConfiguration['doNotDisplayIfNotChecked']) {
                return '';
            }

            // The checkbox is not checked and diplayed
            if ($fieldConfiguration['displayAsImage']) {
                if ($fieldConfiguration['checkboxNotSelectedImage']) {
                    $iconIdentifier = $fieldConfiguration['checkboxNotSelectedImage'];
                } else {
                    $iconIdentifier = 'checkbox-empty';
                }
                $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                $renderedValue = $iconFactory->getIcon(
                    $iconIdentifier,
                    Icon::SIZE_SMALL
                );
            } else {
                $renderedValue = LocalizationUtility::translate('itemviewer.no', 'sav_library_mvc');
            }
        }

        return $renderedValue;
    }
}