<?php
namespace SAV\SavLibraryMvc\ViewHelpers;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use SAV\SavLibraryMvc\Controller\AbstractController;
use SAV\SavLibraryMvc\Domain\Repository\AbstractRepository;
use SAV\SavLibraryMvc\Managers\FieldConfigurationManager;

/**
 * A view helper for building the options for the field selector.
 *
 * = Examples =
 *
 * <code title="RenderRelationManyToManyAsSubformViewHelper">
 * <sav:RenderRelationManyToManyAsSubformViewHelper field="myField" />
 * myField->sav:RenderRelationManyToManyAsSubformViewHelper()
 * </code>
 *
 * Output:
 * the options
 */
class RenderRelationManyToManyAsSubformViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     *
     * @param array $field
     *            The fields
     *
     * @return string the options array
     */
    public function render($field = NULL)
    {
        if ($field === NULL) {
            $field = $this->renderChildren();
        }

        // Gets the controller
        $controller = $this->objectManager->get(AbstractController::getControllerObjectName());

        // Gets the repository
        $repositoryClassName = AbstractRepository::resolveRepositoryClassNameFromTableName($field['foreign_table']);
        $repository = $this->objectManager->get($repositoryClassName);
        $repository->setController($controller);

        // Gets the view type
        $controllerActionName = $this->controllerContext->getRequest()->getControllerActionName();
        $viewType = GeneralUtility::lcfirst($controllerActionName) . 'View';

        // Gets the view identifier
        $viewIdentifier = $controller->getViewerConfiguration($controllerActionName)->getViewIdentifier($viewType);
        // Gets the field configuration manager
        $fieldConfigurationManager = $this->objectManager->get(FieldConfigurationManager::class);

        // Sets the general parameters
        $general = $field['general'];

        // Adds the special arguments to the general configuration
        $arguments = $this->controllerContext->getRequest()->getArguments();
        $general['special'] = $arguments['special'];

        // Adds the subformKey to the general configuration
        $general['subformKey'] = $field['subformKey'];

        // Checks if the maximum number of relations is reached
        if (($field['value'] instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) && $field['value']->count() < $field['maxitems']) {
            $newButtonIsAllowed = TRUE;
        } else {
            $newButtonIsAllowed = FALSE;
        }

        $general['newButtonIsAllowed'] = $newButtonIsAllowed;
        $general['upDownButtonIsAllowed'] = $field['addUpDown'];
        $general['deleteButtonIsAllowed'] = $field['addDelete'];

        // TODO check this
        //'saveButtonIsAllowed' => ($isNewInSubform === FALSE) && $saveButtonIsAllowed,

        // Processes the items
        $start = min($general['pageInSubform'], $general['lastPageInSubform']) * $field['maxSubformItems'];
        $count = 0;
        $maxSubformItems = $field['maxSubformItems'] ? $field['maxSubformItems'] : $field['maxitems'];

        // Checks if a new item was requested
        $uncompressedParameters = AbstractController::uncompressParameters($general['special']);

        $isNewItemInSubform = isset($uncompressedParameters['subformKey']) && isset($uncompressedParameters['subformUidLocal']) && isset($uncompressedParameters['subformUidForeign']) && $uncompressedParameters['subformKey'] == $field['subformKey'] && $uncompressedParameters['subformUidLocal'] == $field['general']['subformUidLocal'] && $uncompressedParameters['subformUidForeign'] == - 1;

        if ($isNewItemInSubform) {
            // Creates a new object
            $subform = $controller->getSubform($uncompressedParameters['subformKey']);
            $subformForeignRepository = $this->objectManager->get($subform['foreignRepository']);
            $object = $subformForeignRepository->createModelObject();

            // Gets the field configuration
            $fieldConfigurationManager->setStaticFieldsConfiguration($viewIdentifier, $repository);
            $fieldConfigurationManager->addDynamicFieldsConfiguration($object);
            $fieldsConfiguration = $fieldConfigurationManager->getFieldsConfiguration();

            // Adds the property name to the field configuration
            $uid = - 1;
            if ($field['edit'] == 1) {
                foreach ($fieldsConfiguration as $fieldConfigurationKey => $fieldConfiguration) {
                    $fieldsConfiguration[$fieldConfigurationKey]['propertyName'] = $field['propertyName'] . '.' . $uid . '.' . $fieldsConfiguration[$fieldConfigurationKey]['propertyName'];
                }
            }
            $items[$uid] = $fieldsConfiguration;
            $general['object'] = $object;
        } else {
            // Processes existing objects
            foreach ($field['value'] as $object) {
                if ($count >= $start && $count < $start + $maxSubformItems) {
                    // Gets the field configuration
                    $fieldConfigurationManager->setStaticFieldsConfiguration($viewIdentifier, $repository);
                    $fieldConfigurationManager->addDynamicFieldsConfiguration($object);
                    $fieldsConfiguration = $fieldConfigurationManager->getFieldsConfiguration();

                    // Checks if it is the first field
                    reset($fieldsConfiguration);
                    $firstFieldKey = key($fieldsConfiguration);
                    $fieldsConfiguration[$firstFieldKey]['isFirstField'] = TRUE;

                    // Adds the property name to the field configuration
                    $uid = $object->getUid();
                    if ($field['edit'] == 1) {
                        foreach ($fieldsConfiguration as $fieldConfigurationKey => $fieldConfiguration) {
                            $fieldsConfiguration[$fieldConfigurationKey]['propertyName'] = $field['propertyName'] . '.' . $uid . '.' . $fieldsConfiguration[$fieldConfigurationKey]['propertyName'];
                        }
                    }
                    $items[$uid] = $fieldsConfiguration;
                }
                $count ++;
            }
        }

        // Gets the subform title
        $subformTitle = $field['subformTitle'];
        if (empty($subformTitle)) {
            // Gets the label cutter
            $cutLabel = $field['cutLabel'];
            if ($cutLabel) {
                $subformTitle = $field['label'];
            }
        } else {
            // Processes localization tags
            $subformTitle = $fieldConfigurationManager->parseLocalizationTags($subformTitle);
            // TODO Parse field tags
        }
        $general['title'] = $subformTitle;

        return array(
            'items' => $items,
            'general' => $general
        );
    }
}
?>

