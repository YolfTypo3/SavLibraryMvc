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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use SAV\SavLibraryMvc\Domain\Repository\AbstractRepository;
use SAV\SavLibraryMvc\Controller\AbstractController;

/**
 * A view helper for building the options for the field selector.
 *
 * = Examples =
 *
 * <code title="RenderRelationOneToManySelectorbor">
 * <sav:RenderRelationOneToManySelectorbox field="myField" action="options|value"/>
 * {myField->sav:RenderRelationOneToManySelectorbox(action:'options|value')}
 * </code>
 *
 * Output:
 * the options or the value
 */
class RenderRelationOneToManySelectorboxViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
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

        // Returns an empty string if the field is null
        if ($field === NULL) {
            return '';
        }

        // Gets the controller
        $controller = $this->objectManager->get(AbstractController::getControllerObjectName());

        // Gets the repository
        $repositoryClassName = AbstractRepository::resolveRepositoryClassNameFromTableName($field['foreign_table']);
        $repository = $this->objectManager->get($repositoryClassName);
        $repository->setController($controller);

        // Defines the label field getter
        $labelFieldGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());

        // Processes the action
        switch ($action) {
            case 'options':
                if (is_array($field['items'][0])) {
                    $extensionKey = AbstractController::getControllerExtensionKey();
                    $options = array(
                        '0' => LocalizationUtility::translate($field['items'][0][0], $extensionKey)
                    );
                } else {
                    $options = array();
                }
                $objects = $repository->findAll();

                foreach ($objects as $object) {
                    $options[$object->getUid()] = $object->$labelFieldGetter();
                }
                return $options;
            case 'value':
                return is_object($field['value']) ? $field['value']->$labelFieldGetter() : LocalizationUtility::translate($field['items'][0][0], $extensionKey);
        }
    }
}
?>

