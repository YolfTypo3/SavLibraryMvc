<?php
namespace YolfTypo3\SavLibraryMvc\ViewConfiguration;

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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * List view configuration for the SAV Library MVC
 *
 * @package SavLibraryMvc
 * @author Laurent Foulloy <yolf.typo3@orange.fr>
 * @version $ID:$
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
    public function getConfiguration($arguments)
    {
        // Gets the special parameters from arguments, uncompresses it and modifies it if needed
        $special = $arguments['special'];
        $uncompressedParameters = AbstractController::uncompressParameters($special);

        // Gets the main repository
        $mainRepository = $this->controller->getMainRepository();

        // Defines the last available page in the list
        $maxItems = (integer) AbstractController::getSetting('maxItems');
        $lastPage = ($maxItems ? floor(($mainRepository->countAllForListView() - 1) / $maxItems) : 0);

        // Defines the pages
        $page = (int) $uncompressedParameters['page'];
        for ($i = min($page, max(0, $lastPage - $maxItems)); $i <= min($lastPage, $page + $maxItems); $i ++) {
            $pages[$i] = $i + 1;
        }

        // Sets the general configuration for the view
        $this->addGeneralViewConfiguration('extensionKey', AbstractController::getControllerExtensionKey());
        $this->addGeneralViewConfiguration('controllerName', AbstractController::getControllerName());
        $this->addGeneralViewConfiguration('special', $special);
        $this->addGeneralViewConfiguration('contentUid', $this->controller->getContentObjectRenderer()->data['uid']);
        $this->addGeneralViewConfiguration('orderLink', $uncompressedParameters['orderLink']);
        $this->addGeneralViewConfiguration('currentMode', $uncompressedParameters['mode']);
        $this->addGeneralViewConfiguration('page', $page);
        $this->addGeneralViewConfiguration('pages', $pages);
        $this->addGeneralViewConfiguration('lastPage', $lastPage);
        $this->addGeneralViewConfiguration('userIsAllowedToInputData', $this->controller->getFrontendUserManager()
            ->userIsAllowedToInputData());
        $this->addGeneralViewConfiguration('hideIconLeft', ! ($uncompressedParameters['mode'] == AbstractController::EDIT_MODE) || (AbstractController::getSetting('noEditButton') && AbstractController::getSetting('noDeleteButton')));
        $this->addGeneralViewConfiguration('newButtonIsAllowed', ($uncompressedParameters['mode'] == AbstractController::EDIT_MODE) && $this->controller->getFrontendUserManager()
            ->userIsAllowedToInputData() && ! AbstractController::getSetting('noNewButton'));

        // Gets the number of items to display
        $count = $mainRepository->countAllForListView();

        // Processes the case where the count is equal to zero
        if ($count == 0) {
            switch (AbstractController::getSetting('showNoAvailableInformation')) {
                case self::SHOW_MESSAGE:
                    $this->addGeneralViewConfiguration('message', LocalizationUtility::translate('message.noAvailableInformation', 'sav_library_mvc'));
                    break;
                case self::DO_NOT_SHOW_MESSAGE:
                    break;
                case self::HIDE_EXTENSION:
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

        // Gets the fields configuration
        $this->fieldConfigurationManager->setStaticFieldsConfiguration($this->getViewIdentifier(), $mainRepository);

        // Gets the query result from the main repository
        $itemsConfiguration = [];
        $objects = $mainRepository->findAllForListView();

        foreach ($objects as $this->object) {

            // Gets the item configuration
            $itemConfiguration = $this->getItemConfiguration();
            $this->fieldConfigurationManager->addGeneralConfiguration($itemConfiguration);
            $this->fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);

            // Parses the fluid template
            $template = $this->templateParser->parse(
                $fluidItemTemplate,
                [
                    'fields' => $this->fieldConfigurationManager::getFieldsConfiguration(),
                    'general' => $itemConfiguration
                ]
            );

            $itemsConfiguration[] = [
                'template' => $template,
                'general' => $itemConfiguration
            ];
        }

        // Gets the view identifier
        $viewIdentifier = $this->getViewIdentifier();

        // Adds the title
        $title = $this->parseTitle(
            $viewIdentifier,
            [
                'general' => $this->getGeneralViewConfiguration(),
                'fields' => $this->fieldConfigurationManager::getFieldsConfiguration()
            ]
        );
        $this->addGeneralViewConfiguration('title', $title);

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
    protected function getItemConfiguration()
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
        $editButtonIsAllowed = $generalCondition && ! AbstractController::getSetting('noEditButton');
        $deleteButtonIsAllowed = $generalCondition && ! AbstractController::getSetting('noDeleteButton');

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
     */
    public function generateFluidItemTemplate()
    {
        // Gets the item templates
        $viewType = $this->getViewType();
        $itemTemplate = $this->controller->getViewItemTemplate($viewType);

        // Searches the tags in the template
        preg_match_all('/###([^\.#]+)\.?([^#]*)###/', $itemTemplate, $matches);

        foreach ($matches[0] as $keyMatch => $match) {
            if ($matches[2][$keyMatch]) {
                // TODO Tag with atable name
            } else {
                // Main model is assumed
                $repository = $this->controller->getMainRepository();
                $fieldName = $matches[1][$keyMatch];
                $itemTemplate = str_replace($match, '<f:if condition="{field.cutDivItemInner}!=1">            <f:render partial="Types/Default/' . $repository->getDataMapFactory()->getFieldType($fieldName) . '.html' . '" arguments="{general:general, field:fields.' . $fieldName . '}" />          </f:if>', $itemTemplate);
            }
        }

        return $itemTemplate;
    }
}
?>