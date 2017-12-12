<?php
namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

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
 */
class RenderRelationManyToManyAsDoubleSelectorboxViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     *
     * @param array $field
     *            The fields
     * @param string $action
     *            The action to execute
     *
     * @return string the options array
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     */
    public function render($field = NULL, $action = NULL)
    {
        if ($field === NULL) {
            $field = $this->renderChildren();
        }

        // Gets the repository class name
        $repositoryClassName = AbstractRepository::resolveRepositoryClassNameFromTableName($field['foreign_table']);

        // Gets the repository
        $repository = $this->objectManager->get($repositoryClassName);

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
                $options = array();
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
                $items = array();
                foreach ($field['value'] as $item) {
                    $object = $repository->findByUid($item);
                    if (! is_null($object)) {
                        $items[$item] = $object->$labelFieldGetter();
                    }
                }
                return $items;

            case 'valuesMM':
                $items = array();
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

