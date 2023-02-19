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
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

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
        if ($this->fieldConfiguration['edit']) {
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

        $extensionKey = $this->fieldConfigurationManager->getController()->getControllerExtensionKey();
        $items = $this->fieldConfiguration['items'];
        $options = [];
        foreach ($items as $item) {
            $options[$item[1]] = LocalizationUtility::translate($item[0], $extensionKey);
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

        $items = $this->fieldConfiguration['items'];
        $value = $this->fieldConfiguration['value'];
        foreach ($items as $item) {
            if ($item[1] == $value) {
                $selectedOption = LocalizationUtility::translate($item[0], $extensionKey);
                break;
            }
        }
        $addedFieldConfiguration['value'] = $selectedOption;

        return $addedFieldConfiguration;
    }
}