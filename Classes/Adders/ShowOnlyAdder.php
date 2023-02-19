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
 * Field configuration adder for ShowOnly type.
 */
final class ShowOnlyAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];

        $defautRepository = $this->fieldConfigurationManager->getDefaultRepository();
        $fieldName = $this->fieldConfiguration['fieldName'];
        $renderType = $defautRepository->getDataMapFactory()->getRenderType($fieldName);
        if (empty($renderType)) {
            $renderType = 'String';
        }

        // Checks if renderType is defined in the field configuration
        if(! empty($this->fieldConfiguration['renderAs'])) {
            $renderType = $this->fieldConfiguration['renderAs'];
        }

        $adderClassName = '\\YolfTypo3\\SavLibraryMvc\\Adders\\' . $renderType . 'Adder';
        if (method_exists($adderClassName, 'render')) {
            $adder = new $adderClassName($this->fieldConfigurationManager);
            $addedFieldConfiguration = $adder->render();
        }

        $addedFieldConfiguration['renderType'] = $renderType;

        return $addedFieldConfiguration;
    }
}