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

namespace YolfTypo3\SavLibraryMvc\ViewConfiguration;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * List view configuration for the SAV Library MVC
 *
 * @package SavLibraryMvc
 * @author Laurent Foulloy <yolf.typo3@orange.fr>
 *
 */
class ListViewConfiguration extends AbstractViewConfiguration
{

    /**
     * Gets the view configuration
     *
     * @param array $arguments
     *            Arguments from the action
     * @return array The view configuration
     */
    public function getConfiguration(array $arguments): array
    {
        // Gets the special parameters from arguments, uncompresses it and modifies it if needed
        $special = $arguments['special'];
        $uncompressedParameters = AbstractController::uncompressParameters($special);

        // Gets the main repository
        $mainRepository = $this->controller->getMainRepository();

        // Gets the number of items to display
        $count = $mainRepository->countAllForListView();

        // Defines the last available page in the list
        $maxItems = (int) $this->controller->getSetting('maxItems');
        $lastPage = ($maxItems ? floor(($count - 1) / $maxItems) : 0);

        // Defines the pages
        $page = (int) $uncompressedParameters['page'];
        $pages = [];
        for ($i = min($page, max(0, $lastPage - $maxItems)); $i <= min($lastPage, $page + $maxItems); $i ++) {
            $pages[$i] = $i + 1;
        }

        // Sets the general configuration for the view
        $this->addGeneralViewConfiguration('extensionKey', $this->controller->getControllerExtensionKey());
        $this->addGeneralViewConfiguration('controllerName', $this->controller->getControllerName());
        $this->addGeneralViewConfiguration('special', $special);
        $this->addGeneralViewConfiguration('contentUid', $this->controller->getContentObjectRenderer()->data['uid']);
        $this->addGeneralViewConfiguration('orderLink', $uncompressedParameters['orderLink']);
        $this->addGeneralViewConfiguration('currentMode', $uncompressedParameters['mode']);
        $this->addGeneralViewConfiguration('page', $page);
        $this->addGeneralViewConfiguration('pages', $pages);
        $this->addGeneralViewConfiguration('lastPage', $lastPage);
        $this->addGeneralViewConfiguration('userIsAllowedToInputData', $this->controller->getFrontendUserManager()
            ->userIsAllowedToInputData());
        $this->addGeneralViewConfiguration('userIsAllowedToExportData', $this->controller->getFrontendUserManager()
            ->userIsAllowedToExportData());
        $this->addGeneralViewConfiguration('hideIconLeft', ! ($uncompressedParameters['mode'] == AbstractController::EDIT_MODE) || ($this->controller->getSetting('noEditButton') && AbstractController::getSetting('noDeleteButton')));
        $this->addGeneralViewConfiguration('newButtonIsAllowed', ($uncompressedParameters['mode'] == AbstractController::EDIT_MODE) && $this->controller->getFrontendUserManager()
            ->userIsAllowedToInputData() && ! $this->controller->getSetting('noNewButton'));

        // Processes the case where the count is equal to zero
        if ($count == 0) {
            switch ($this->controller->getSetting('showNoAvailableInformation')) {
                case self::SHOW_MESSAGE:
                    $this->addGeneralViewConfiguration('message', LocalizationUtility::translate('message.noAvailableInformation', 'sav_library_mvc'));
                    break;
                case self::DO_NOT_SHOW_MESSAGE:
                    break;
                case self::DO_NOT_SHOW_EXTENSION:
                    $this->addGeneralViewConfiguration('hideExtension', true);
                    break;
            }
            $viewConfiguration = [
                'general' => $this->getGeneralViewConfiguration()
            ];
            return $viewConfiguration;
        }

        // Generates the fluid template
        $fluidItemTemplate = $this->generateFluidItemTemplate();

        // Gets the field configuration manager
        $fieldConfigurationManager = $this->controller->getFieldConfigurationManager();

        // Gets the fields configuration
        $fieldConfigurationManager->setStaticFieldsConfiguration($this->getViewIdentifier(), $mainRepository);

        // Gets the query result from the main repository
        $itemsConfiguration = [];
        $objects = $mainRepository->findAllForListView();

        foreach ($objects as $this->object) {
            // Gets the item configuration
            $itemConfiguration = $this->getItemConfiguration();
            $fieldConfigurationManager->setGeneralConfiguration($itemConfiguration);
            $fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);

            // Attribute-based post-processing
            $fieldsConfiguration = $fieldConfigurationManager->getFieldsConfiguration();
            $classItem = 'item';
            foreach($fieldsConfiguration as $fieldConfiguration) {
                if ($fieldConfiguration['classItem'] != 'item') {
                    $classItem = $fieldConfiguration['classItem'];
                }
            }

            // Parses the fluid template
            $template = $this->templateParser->parse($fluidItemTemplate, [
                'field' => $fieldConfigurationManager->getFieldsConfiguration(),
                'general' => $itemConfiguration
            ]);

            $itemsConfiguration[] = [
                'classItem' => $classItem,
                'template' => $template,
                'general' => $itemConfiguration
            ];
        }

        // Gets the view identifier
        $viewIdentifier = $this->getViewIdentifier();

        // Adds the title
        $title = $this->parseTitle($viewIdentifier, [
            'general' => $this->getGeneralViewConfiguration(),
            'field' => $fieldConfigurationManager->getFieldsConfiguration()
        ]);
        $this->addGeneralViewConfiguration('title', $title);

        // Adds the javascript to confirm the delete action
        if ($uncompressedParameters['mode'] == AbstractController::EDIT_MODE) {
            AdditionalHeaderManager::addConfirmDeleteJavaScript('item');
        }

        // Returns the view configuration
        $viewConfiguration = [
            'general' => $this->getGeneralViewConfiguration(),
            'items' => $itemsConfiguration
        ];

        return $viewConfiguration;
    }

    /**
     * Gets the item configuration.
     *
     * @return array
     */
    protected function getItemConfiguration(): array
    {
        // Uncompresses the special parameter
        $special = $this->getGeneralViewConfiguration('special');
        $uncompressedParameters = AbstractController::uncompressParameters($special);

        // Sets additional configuration values
        $isInEditMode = $uncompressedParameters['mode'] == AbstractController::EDIT_MODE;
        $userIsAllowedToInputData = $this->controller->getFrontendUserManager()->userIsAllowedToInputData();
        $userIsAllowedToChangeData = $this->controller->getFrontendUserManager()->userIsAllowedToChangeData($this->object);
        $isInDraftWorkspace = $this->controller->getMainRepository()->isInDraftWorkspace($this->object->getUid());

        // Sets the general condition
        $generalCondition = $isInEditMode && $userIsAllowedToInputData && $userIsAllowedToChangeData && ! $isInDraftWorkspace;

        // Sets the button conditions
        $editButtonIsAllowed = $generalCondition && ! $this->controller->getSetting('noEditButton');
        $deleteButtonIsAllowed = $generalCondition && ! $this->controller->getSetting('noDeleteButton');

        // Sets the special parameters for the item
        $uncompressedParameters['uid'] = $this->object->getUid();
        $special = AbstractController::compressParameters($uncompressedParameters);

        $itemConfiguration = [
            'isInDraftWorkspace' => $isInDraftWorkspace,
            'editButtonIsAllowed' => $editButtonIsAllowed,
            'deleteButtonIsAllowed' => $deleteButtonIsAllowed,
            'special' => $special
        ];

        return $itemConfiguration;
    }

    /**
     * Generates fluid item template
     *
     * @return string The view configuration
     * @throws \Exception
     */
    public function generateFluidItemTemplate(): string
    {
        // Gets the item templates
        $viewIdentifier = $this->getViewIdentifier();
        $itemTemplate = $this->controller->getViewItemTemplate($viewIdentifier);

        // Searches the tags in the template
        $matches = [];
        preg_match_all('/###([^\.#]+)\.?([^#]*)###/', $itemTemplate, $matches);

        foreach ($matches[0] as $keyMatch => $match) {
            if ($matches[2][$keyMatch]) {
                // TODO Tag with atable name
            } else {
                // Main model is assumed
                $repository = $this->controller->getMainRepository();

                // Gets the type
                $fieldName = $matches[1][$keyMatch];
                $dataMapFactory = $repository->getDataMapFactory();
                $type = $dataMapFactory->getFieldType($fieldName);

                // If the type is empty, tries to use the table field name
                // Useful for compatiblity with SAV Library Plus
                if (empty($type)) {
                    $tableFieldNames = array_column($dataMapFactory->getSavLibraryMvcColumns(), 'tableFieldName');
                    foreach ($tableFieldNames as $tableFieldName) {
                        if (key($tableFieldName) == $fieldName) {
                            $fieldName = current($tableFieldName);
                            $type = $dataMapFactory->getFieldType($fieldName);
                            break;
                        }
                    }
                }

                if ($type === null) {
                    throw new \Exception(sprintf(
                        'Undefined type for field "%s".',
                        $fieldName
                        )
                    );
                }
                $itemTemplate = str_replace($match, '<f:if condition="{field.' . $fieldName . '.cutDivItemInner}!=1">            <sav:render partial="Types/Default/' . $type . '.html' . '" arguments="{general:general, field:field.' . $fieldName . '}" />          </f:if>', $itemTemplate);
            }
        }

        return $itemTemplate;
    }
}
