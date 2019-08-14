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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Domain\Repository\AbstractRepository;

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
 *
 * @package SavLibraryMvc
 */
class RenderRelationOneToManySelectorboxViewHelper extends AbstractViewHelper
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

        // Returns an empty string if the field is null
        if ($field === null) {
            return '';
        }

        // Creates the object manager
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Gets the controller
        $controller = $objectManager->get(AbstractController::getControllerObjectName());

        // Gets the repository
        $repositoryClassName = AbstractRepository::resolveRepositoryClassNameFromTableName($field['foreign_table']);
        $repository = $objectManager->get($repositoryClassName);
        $repository->setController($controller);

        // Defines the label field getter
        $labelFieldGetter = 'get' . GeneralUtility::underscoredToUpperCamelCase($repository->getDataMapFactory()->getLabelField());

        // Processes the action
        switch ($action) {
            case 'options':
                if (is_array($field['items'][0])) {
                    $extensionKey = AbstractController::getControllerExtensionKey();
                    $options = [
                        '0' => LocalizationUtility::translate($field['items'][0][0], $extensionKey)
                    ];
                } else {
                    $options = [];
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

