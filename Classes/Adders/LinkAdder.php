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
 * Field configuration adder for Link type.
 */
final class LinkAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];

        // message attribute
        $fieldMessage = $this->fieldConfiguration['fieldMessage'];
        if ($fieldMessage) {
            $addedFieldConfiguration['message'] = $this->fieldConfigurationManager->getFieldConfiguration($fieldMessage)['value'];
        }
        if (empty($this->fieldConfiguration['message']) && empty($fieldMessage)) {
            $addedFieldConfiguration['message'] = $this->fieldConfiguration['value'];
        }
        // alt attribute
        $fieldLink = $this->fieldConfiguration['fieldLink'];
        if ($fieldLink) {
            $addedFieldConfiguration['link'] = $this->fieldConfigurationManager->getFieldConfiguration($fieldLink)['value'];
        }
        if (empty($this->fieldConfiguration['link']) && empty($fieldLink)) {
            $addedFieldConfiguration['link'] = $this->fieldConfiguration['value'];
        }

        return $addedFieldConfiguration;
    }
}