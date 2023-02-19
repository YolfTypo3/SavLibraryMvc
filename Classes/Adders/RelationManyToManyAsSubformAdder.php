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
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Domain\Repository\AbstractRepository;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * Field configuration adder for RelationManyToManyAsSubform type.
 */
final class RelationManyToManyAsSubformAdder extends AbstractAdder
{

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];

        // Sets the subform flag
        $subformFlag = $this->fieldConfigurationManager->getSubformFlag();
        $this->fieldConfigurationManager->setSubformFlag(true);

        // Sets the subform property name
        $propertyName = $this->fieldConfiguration['propertyName'];
        $subformPropertyName = $this->fieldConfigurationManager->getSubformPropertyName();
        $this->fieldConfigurationManager->setSubformPropertyName($propertyName);

        // Saves the domain object
        $savedObject = $this->fieldConfigurationManager->getDomainObject();

        //Intializes the general configuration
        $generalConfiguration = [];

        // Sets the flag to show first and last buttons
        $generalConfiguration['showFirstLastButtons'] = $this->fieldConfiguration['noFirstLast'] ? 0 : 1;

        // Computes the last page id in a subform
        $maximumItemsInSubform = $this->fieldConfiguration['maxSubformItems'];
        $lastPageInSubform = (empty($maximumItemsInSubform) ? 0 : floor(($this->fieldConfiguration['value']->count() - 1) / $maximumItemsInSubform));
        $generalConfiguration['lastPageInSubform'] = $lastPageInSubform;

        // Page information for the page browser
        $maxPagesInSubform = $this->fieldConfigurationManager->getController()->getSetting('maxItems');

        // Gets the page for the subform
        $arguments = $this->fieldConfigurationManager->getController()->getArguments();
        $uncompressedParameters = AbstractController::uncompressParameters($arguments['special']);
        $subformActivePages = $uncompressedParameters['subformActivePages'];
        $uncompressedSubformActivePages = AbstractController::uncompressSubformActivePages($subformActivePages);
        $subformKey = $this->fieldConfiguration['subformKey'];
        $pageInSubform = (int) $uncompressedSubformActivePages[$subformKey];
        $generalConfiguration['pageInSubform'] = $pageInSubform;

        $pagesInSubform = [];
        for ($i = min($pageInSubform, max(0, $lastPageInSubform - $maxPagesInSubform)); $i <= min($lastPageInSubform, $pageInSubform + $maxPagesInSubform); $i ++) {
            $pagesInSubform[$i] = $i + 1;
        }
        $generalConfiguration['pagesInSubform'] = $pagesInSubform;
        $generalConfiguration['subformUidLocal'] = $this->fieldConfigurationManager->getDomainObject()->getUid();

        // Adds the special arguments to the general configuration
        $generalConfiguration['special'] = $arguments['special'];

        // Adds the subformKey to the general configuration
        $generalConfiguration['subformKey'] = $this->fieldConfiguration['subformKey'];

        // Checks if the maximum number of relations is reached
        if (($this->fieldConfiguration['value'] instanceof ObjectStorage) && $this->fieldConfiguration['value']->count() < $this->fieldConfiguration['maxitems']) {
            $newButtonIsAllowed = true;
        } else {
            $newButtonIsAllowed = false;
        }

        $generalConfiguration['newButtonIsAllowed'] = $newButtonIsAllowed;
        $generalConfiguration['upDownButtonIsAllowed'] = $this->fieldConfiguration['addUpDown'];
        $generalConfiguration['deleteButtonIsAllowed'] = $this->fieldConfiguration['addDelete'];

        // Processes the items
        $start = min($generalConfiguration['pageInSubform'], $generalConfiguration['lastPageInSubform']) * $this->fieldConfiguration['maxSubformItems'];
        $count = 0;
        $maxSubformItems = $this->fieldConfiguration['maxSubformItems'] ? $this->fieldConfiguration['maxSubformItems'] : $this->fieldConfiguration['maxitems'];

        // Gets the controller
        $controller = $this->fieldConfigurationManager->getController();

        // Gets the repository
        $repository = $this->getRepository();

        // Gets the controller action name
        $controllerActionName = $controller->getControllerActionName();

        // Gets the view identifier
        $viewIdentifier = $controller->getViewerConfiguration($controllerActionName)->getViewIdentifier(false);

        // Checks if a new item was requested
        $isNewItemInSubform = isset($uncompressedParameters['subformKey']) &&
            isset($uncompressedParameters['subformUidLocal']) &&
            isset($uncompressedParameters['subformUidForeign']) &&
            $uncompressedParameters['subformKey'] == $this->fieldConfiguration['subformKey'] &&
            $uncompressedParameters['subformUidLocal'] == $generalConfiguration['subformUidLocal'] &&
            $uncompressedParameters['subformUidForeign'] == - 1;

        // Stores the fields configuration
        $this->fieldConfigurationManager->storeFieldsConfiguration();

        if ($isNewItemInSubform) {
            // Creates a new object
            $subform = $controller->getSubform((int) $uncompressedParameters['subformKey']);
            $subformForeignRepository = GeneralUtility::makeInstance($subform['foreignRepository']);
            $object = $subformForeignRepository->createModelObject();

            // Gets the field configuration
            $this->fieldConfigurationManager->setStaticFieldsConfiguration($viewIdentifier, $repository);
            $this->fieldConfigurationManager->addDynamicFieldsConfiguration($object);
            $fieldsConfiguration = $this->fieldConfigurationManager->getFieldsConfiguration();

            // Sets the items
            $uid = - 1;
            $items[$uid] = $fieldsConfiguration;
        } else {
            // Processes existing objects
            foreach ($this->fieldConfiguration['value'] as $object) {
                if ($count >= $start && $count < $start + $maxSubformItems) {
                    // Gets the field configuration
                    $this->fieldConfigurationManager->setStaticFieldsConfiguration($viewIdentifier, $repository);
                    $this->fieldConfigurationManager->addDynamicFieldsConfiguration($object);
                    $fieldsConfiguration = $this->fieldConfigurationManager->getFieldsConfiguration();

                    // Checks if it is the first field
                    reset($fieldsConfiguration);
                    $firstFieldKey = key($fieldsConfiguration);
                    $fieldsConfiguration[$firstFieldKey]['isFirstField'] = true;

                    // Sets the items
                     $uid = $object->getUid();
                    $items[$uid] = $fieldsConfiguration;
                }
                $count ++;
            }
        }

        // Gets the subform title
        $subformTitle = $this->fieldConfiguration['subformTitle'];
        if (empty($subformTitle)) {
            // Gets the label cutter
            $cutLabel = $this->fieldConfiguration['cutLabel'];
            if ($cutLabel) {
                $subformTitle = $this->fieldConfiguration['label'];
            }
        } else {
            // Processes localization tags
            $subformTitle = $this->fieldConfigurationManager->parseLocalizationTags($subformTitle);
            $subformTitle = $this->fieldConfigurationManager->parseFieldTags($subformTitle);
        }
        $generalConfiguration['title'] = $subformTitle;

        // Adds the javascript to confirm the delete action
        if ($this->fieldConfiguration['edit'] == 1) {
            AdditionalHeaderManager::addConfirmDeleteJavaScript('subformItem');
        }

        // Restores the field configuration
       $this->fieldConfigurationManager->restoreFieldsConfiguration();

        $addedFieldConfiguration['subformConfiguration'] = [
            'items' => $items,
            'general' => $generalConfiguration
        ];

        // Restores the subform flag and property name
        $this->fieldConfigurationManager->setSubformFlag($subformFlag);
        $this->fieldConfigurationManager->setSubformPropertyName($subformPropertyName);

        // Restores the domain object
        $this->fieldConfigurationManager->setDomainObject($savedObject);

        return $addedFieldConfiguration;
    }
}