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
use YolfTypo3\SavLibraryMvc\Domain\Repository\AbstractRepository;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * A view helper for building the relation many to many as double selectorbox.
 *
 * = Examples =
 *
 * <code title="RenderRelationManyToManyAsDoubleSelectorboxViewHelper">
 * <sav:RenderRelationManyToManyAsDoubleSelectorboxViewHelper field="myField" action="options|optionsSource|values|moveToSource[moveFromSource" />
 * myField->sav:RenderRelationManyToManyAsDoubleSelectorboxViewHelper(action:'options|optionsSource|values|moveToSource[moveFromSource')
 *
 * with actionKey =
 * </code>
 *
 * Output:
 * the options
 *
 * @package SavLibraryMvc
 */
class RenderRelationManyToManyAsDoubleSelectorboxViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('field', 'array', 'Fields', false, null);
        $this->registerArgument('action', 'string', 'Action to execute', false, null);
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
        $action = $this->arguments['action'];

        if ($field === null) {
            $field = $this->renderChildren();
        }

        // Creates the object manager
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Gets the repository class name
        $repositoryClassName = AbstractRepository::resolveRepositoryClassNameFromTableName($field['foreign_table']);

        // Gets the repository
        $repository = $objectManager->get($repositoryClassName);

        // Defines the label field getter
        $labelFieldGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());

        switch ($action) {

            case 'options':
                // Adds the javaScript for the selectorboxes
                $pluginNameSpace = AbstractController::getPluginNameSpace();
                AdditionalHeaderManager::addJavaScript('loadDoubleSelector', 'loadDoubleSelector(\'data\', \'' . $pluginNameSpace . '[source][' . $field['fieldName'] . '][]\', \'' . $pluginNameSpace . '[data][' . $field['fieldName'] . '][]\');');
                AdditionalHeaderManager::addJavaScript('selectAll', 'if (x == \'' . 'data' . '\')	selectAll(x, \'' . $pluginNameSpace . '[data][' . $field['fieldName'] . '][]\');');

            case 'optionsSource':
                // Defines the options
                $options = [];
                $objects = $repository->findAll();
                foreach ($objects as $object) {
                    $uid = $object->getUid();
                    $options[$uid] = $object->$labelFieldGetter();
                }
                return $options;

            case 'moveToSource':
                $pluginNameSpace = AbstractController::getPluginNameSpace();
                $out = 'move(\'data\', \'' . $pluginNameSpace . '[data][' . str_replace('.', '][', $field['propertyName']) . '][]\', \'' . $pluginNameSpace . '[source][' . str_replace('.', '][', $field['propertyName']) . '][]\', 0);';
                return $out;

            case 'moveFromSource':
                $pluginNameSpace = AbstractController::getPluginNameSpace();
                $out = 'move(\'data\', \'' . $pluginNameSpace . '[source][' . str_replace('.', '][', $field['propertyName']) . '][]\', \'' . $pluginNameSpace . '[data][' . str_replace('.', '][', $field['propertyName']) . '][]\', 0);';
                return $out;

            case 'values':
                $items = [];
                foreach ($field['value'] as $item) {
                    $object = $repository->findByUid($item);
                    if (! is_null($object)) {
                        $items[$item] = $object->$labelFieldGetter();
                    }
                }
                return $items;

            case 'valuesMM':
                $items = [];
                foreach ($field['value'] as $object) {
                    if (! is_null($object)) {
                        $items[$object->getUid()] = $object->$labelFieldGetter();
                    }
                }
                return $items;
        }
    }
}
?>

