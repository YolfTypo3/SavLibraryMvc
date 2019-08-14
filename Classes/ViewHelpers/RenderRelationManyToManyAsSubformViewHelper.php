<?php
namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Domain\Repository\AbstractRepository;
use YolfTypo3\SavLibraryMvc\Managers\FieldConfigurationManager;

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
 *
 * @package SavLibraryMvc
 */
class RenderRelationManyToManyAsSubformViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('field', 'array', 'Fields', false, null);
    }

    /**
     *
     * Renders the viewhelper
     *
     * @return mixed
     */
    public function render()
    {
        // Gets the arguments
        $field = $this->arguments['field'];

        if ($field === null) {
            $field = $this->renderChildren();
        }

        // Creates the object manager
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Gets the controller
        $controller = $objectManager->get(AbstractController::getControllerObjectName());

        // Gets the repository
        $repositoryClassName = AbstractRepository::resolveRepositoryClassNameFromTableName($field['foreign_table']);
        $repository = $objectManager->get($repositoryClassName);
        $repository->setController($controller);

        // Gets the view type
        $controllerActionName = $this->renderingContext->getControllerContext()
            ->getRequest()
            ->getControllerActionName();
        $viewType = lcfirst($controllerActionName) . 'View';

        // Gets the view identifier
        $viewIdentifier = $controller->getViewerConfiguration($controllerActionName)->getViewIdentifier($viewType);

        // Gets the field configuration manager
        $fieldConfigurationManager = $objectManager->get(FieldConfigurationManager::class);
        $fieldConfigurationManager::storeFieldsConfiguration();

        // Sets the general parameters
        $general = $field['general'];

        // Adds the special arguments to the general configuration
        $arguments = $this->renderingContext->getControllerContext()
            ->getRequest()
            ->getArguments();
        $general['special'] = $arguments['special'];

        // Adds the subformKey to the general configuration
        $general['subformKey'] = $field['subformKey'];

        // Checks if the maximum number of relations is reached
        if (($field['value'] instanceof \TYPO3\CMS\Extbase\Persistence\ObjectStorage) && $field['value']->count() < $field['maxitems']) {
            $newButtonIsAllowed = true;
        } else {
            $newButtonIsAllowed = false;
        }

        $general['newButtonIsAllowed'] = $newButtonIsAllowed;
        $general['upDownButtonIsAllowed'] = $field['addUpDown'];
        $general['deleteButtonIsAllowed'] = $field['addDelete'];

        // TODO check this
        // 'saveButtonIsAllowed' => ($isNewInSubform === false) && $saveButtonIsAllowed,

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
            $subformForeignRepository = $objectManager->get($subform['foreignRepository']);
            $object = $subformForeignRepository->createModelObject();

            // Gets the field configuration
            $fieldConfigurationManager->setStaticFieldsConfiguration($viewIdentifier, $repository);
            $fieldConfigurationManager->addDynamicFieldsConfiguration($object);
            $fieldsConfiguration = $fieldConfigurationManager::getFieldsConfiguration();

            // Adds the property name to the field configuration
            $uid = - 1;
            $items = [];
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
                    $fieldsConfiguration = $fieldConfigurationManager::getFieldsConfiguration();

                    // Checks if it is the first field
                    reset($fieldsConfiguration);
                    $firstFieldKey = key($fieldsConfiguration);
                    $fieldsConfiguration[$firstFieldKey]['isFirstField'] = true;

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
            // @TODO Parse field tags
        }
        $general['title'] = $subformTitle;

        // Restores the field configuration
        $fieldConfigurationManager::restoreFieldsConfiguration();

        return [
            'items' => $items,
            'general' => $general
        ];
    }
}
?>

