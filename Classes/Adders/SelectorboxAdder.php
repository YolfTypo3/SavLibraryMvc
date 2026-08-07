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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Field configuration adder for Selectorbox type.
 */
final class SelectorboxAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $edit = $this->fieldConfiguration['edit'] ?? false;
        if ($edit) {
            return $this->renderInEditMode();
        } else {
            return $this->renderInDefaultMode();
        }
    }

    /**
     * Renders the adder in edit mode
     *
     * @return array
     */
    protected function renderInEditMode(): array
    {
        $addedFieldConfiguration = [];

        $extensionKey = $this->controller->getControllerExtensionKey();
        $items = $this->fieldConfiguration['items'];
        $options = [];
        foreach ($items as $item) {
            $itemValue = $item['value'] ??  $item[1];
            $itemLabel = $item['label'] ??  $item[0];
            $options[$itemValue] = LocalizationUtility::translate($itemLabel, $extensionKey);
        }

        $addedFieldConfiguration['options'] = $options;

        return $addedFieldConfiguration;
    }


    /**
     * Renders the adder in default mode
     *
     * @return array
     */
    protected function renderInDefaultMode(): array
    {
        $addedFieldConfiguration = [];

        $extensionKey = $this->controller->getControllerExtensionKey();
        $items = $this->fieldConfiguration['items'];
        $value = $this->fieldConfiguration['value'];
        $selectedOption = null;
        foreach ($items as $item) {
            $itemValue = $item['value'] ??  $item[1];
            if ($itemValue == $value) {
                $itemLabel = $item['label'] ??  $item[0];
                $selectedOption = LocalizationUtility::translate($itemLabel, $extensionKey);
                break;
            }
        }
        $addedFieldConfiguration['value'] = $selectedOption;

        return $addedFieldConfiguration;
    }
}