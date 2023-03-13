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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * Field configuration adder for RelationManyToManyAsDoubleSelectorbox type.
 */
final class RelationManyToManyAsDoubleSelectorboxAdder extends AbstractAdder
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

        // Adds the javaScript for the selectorboxes
        $fieldName = $this->fieldConfiguration['fieldName'];
        $propertyName = $this->fieldConfiguration['propertyName'];
        $pluginNameSpace = $this->fieldConfigurationManager->getController()->getPluginNameSpace();
        if ($this->fieldConfigurationManager->isSelected($fieldName, true)) {
            AdditionalHeaderManager::addJavaScript('selectAll', 'if (x == \'' . 'data' . '\')	selectAll(x, \'' . $pluginNameSpace . '[data][' . str_replace('.', '][', $propertyName) . '][]\');');
        }

        $addedFieldConfiguration['moveToSource'] = 'move(\'data\', \'' . $pluginNameSpace . '[data][' . str_replace('.', '][', $propertyName) . '][]\', \'' . $pluginNameSpace . '[source][' . str_replace('.', '][', $propertyName) . '][]\', 0);';
        $addedFieldConfiguration['moveFromSource'] = 'move(\'data\', \'' . $pluginNameSpace . '[source][' . str_replace('.', '][', $propertyName) . '][]\', \'' . $pluginNameSpace . '[data][' . str_replace('.', '][', $propertyName) . '][]\', 0);';
        $addedFieldConfiguration['sourceName'] = 'source[' . str_replace('.', '][', $propertyName) . ']';
        $addedFieldConfiguration['destinationName'] = 'data[' . str_replace('.', '][', $propertyName) . ']';

        // Cheks if options are provided by a query
        $reqLabel = $this->fieldConfiguration['reqLabel'] ?? '';
        if (! empty($reqLabel)) {
            $options = $this->getLabelFromRequest($reqLabel);
        }

        // Gets the repository and its query
        $repository = $this->getRepository();
        $query = $this->getQuery($repository);
        $MM = $this->fieldConfiguration['MM'] ?? '';
        $value = $this->fieldConfiguration['value'];
        if (! empty($MM) || $value instanceof ObjectStorage) {
            $selectedObjects = $value;
        } else {
            $items = explode(',', $value);
            $selectedObjects = $query->matching($query->in('uid', $items))->execute();
        }

        // Gets the list of uid.
        $uidSelectedObjects = [];
        $selectedOptions = [];
        $labelSelect = $this->fieldConfiguration['labelSelect'] ?? '';

        foreach ($selectedObjects as $object) {
            $uid = $object->getUid();
            $uidSelectedObjects[] = $uid;
            if (! empty($reqLabel)) {
                if (isset($options[$uid])) {
                    $selectedOptions[$uid] = $options[$uid];
                }
            } elseif (! empty($labelSelect)) {
                $selectedOptions[$uid] = $this->parseLabel($object, $labelSelect);
            } else {
                $labelGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());
                $selectedOptions[$uid] = $object->$labelGetter();
            }
        }

        // Gets the unselected objects
        if (empty($uidSelectedObjects)) {
            $unselectedObjects = $this->getQuery($repository)->execute();
        } else {
            $unselectedObjects = $query->matching(
                $query->logicalNot(
                    $query->in('uid', $uidSelectedObjects)
                )
            )->execute();
        }
        $unselectedOptions = [];
        foreach ($unselectedObjects as $object) {
            $uid = $object->getUid();
            if (! empty($reqLabel)) {
                if (isset($options[$uid])) {
                    $unselectedOptions[$uid] = $options[$uid];
                }
            } elseif (! empty($labelSelect)) {
                $unselectedOptions[$uid] = $this->parseLabel($object, $labelSelect);
            } else {
                $labelGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());
                $unselectedOptions[$uid] = $object->$labelGetter();
            }
        }

        $addedFieldConfiguration['selectedOptions'] = $selectedOptions;
        $addedFieldConfiguration['unselectedOptions'] = $unselectedOptions;

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

        // Cheks if options are provided by a query
        $reqLabel = $this->fieldConfiguration['reqLabel'] ?? '';
        if (! empty($reqLabel)) {
            $options = $this->getLabelFromRequest($reqLabel);
        }

        // Gets the repository and its query
        $repository = $this->getRepository();
        $query = $this->getQuery($repository);
        $MM = $this->fieldConfiguration['MM'] ?? '';
        $value = $this->fieldConfiguration['value'];
        if (! empty($MM) || $value instanceof ObjectStorage) {
            $selectedObjects = $value;
        } else {
            if (!empty($value)) {
                $items = explode(',', $value);
                $selectedObjects = $query->matching($query->in('uid', $items))->execute();
            } else {
                $selectedObjects = null;
            }
        }


        $selectedItems = [];
        if ($selectedObjects !== null) {
            $labelSelect = $this->fieldConfiguration['labelSelect'] ?? '';
            foreach ($selectedObjects as $object) {
                $uid = $object->getUid();
                if (! empty($reqLabel)) {
                    if (isset($options[$uid])) {
                        $selectedItems[] = $options[$uid];
                    }
                } elseif (! empty($labelSelect)) {
                    $selectedItems[] = $this->parseLabel($object, $labelSelect);
                } else {
                    $labelGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());
                    $selectedItems[] = $object->$labelGetter();
                }
            }
        }
        $addedFieldConfiguration['selectedItems'] = $selectedItems;

        return $addedFieldConfiguration;
    }

}