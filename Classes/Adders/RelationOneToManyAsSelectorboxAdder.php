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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Field configuration adder for RelationOneToManyAsSelectorbox type.
 */
final class RelationOneToManyAsSelectorboxAdder extends AbstractAdder
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

        // Gets the repository and its query
        $repository = $this->getRepository();
        $query = $this->getQuery($repository);

        // Processes the options
        if (is_array($this->fieldConfiguration['items'][0])) {
            $extensionKey = $this->fieldConfigurationManager->getController()->getControllerExtensionKey();
            $options = [
                '' => LocalizationUtility::translate($this->fieldConfiguration['items'][0][0], $extensionKey)
            ];
        } else {
            $options = [];
        }
        $objects = $query->execute();

        foreach ($objects as $object) {
            if (empty($this->fieldConfiguration['labelSelect'])) {
                $labelGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());
                $value = $object->$labelGetter();
            } else {
                $value = self::parseLabel($object, $this->fieldConfiguration['labelSelect']);
            }
            $options[$object->getUid()] = $value;
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
        $value = $this->fieldConfiguration['value'];
        if (is_object($value)) {
            // Defines the label field getter
            if (empty($this->fieldConfiguration['labelSelect'])) {
                $repositoryClassName = $value->resolveRepositoryClassName();
                $repository = GeneralUtility::makeInstance($repositoryClassName);
                $repository->setController($this->fieldConfigurationManager->getController());
                $labelGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());
                $addedFieldConfiguration['value'] = $value->$labelGetter();
            } else {
                $addedFieldConfiguration['value'] = $this->parseLabel($value, $this->fieldConfiguration['labelSelect']);
            }
        } else {
            if (!empty($this->fieldConfiguration['items'][0][0])) {
                $extensionKey = $this->fieldConfigurationManager->getController()->getControllerExtensionKey();
                $addedFieldConfiguration['value'] = LocalizationUtility::translate($this->fieldConfiguration['items'][0][0], $extensionKey);
            } else {
                throw new \Exception(sprintf(
                    'No label item defined for "%s".',
                    $this->fieldConfiguration['fieldName']
                    )
                );
            }
        }

        return $addedFieldConfiguration;
    }

}