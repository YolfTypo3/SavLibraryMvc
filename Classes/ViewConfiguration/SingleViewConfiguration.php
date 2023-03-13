<?php

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

use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Single view configuration for the SAV Library MVC
 */
class SingleViewConfiguration extends AbstractViewConfiguration
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
        $uncompressedParameters['mode'] = AbstractController::DEFAULT_MODE;
        $special = AbstractController::compressParameters($uncompressedParameters);

        // Gets the uid
        $uid = $uncompressedParameters['uid'];

        // Gets the main repository
        $mainRepository = $this->controller->getMainRepository();

        // Gets the object from the uid
        $this->object = $mainRepository->findByUid($uid);

        // Gets the view identifier
        $viewIdentifier = $this->getViewIdentifier();

        // Gets the field configuration manager
        $fieldConfigurationManager = $this->controller->getFieldConfigurationManager();

        // Sets general configuration values
        $this->addGeneralViewConfiguration('extensionKey', $this->controller->getControllerExtensionKey());
        $this->addGeneralViewConfiguration('controllerName', $this->controller->getControllerName());
        $this->addGeneralViewConfiguration('special', $special);
        $this->addGeneralViewConfiguration('contentUid', $this->controller->getContentObjectRenderer()->data['uid']);
        $this->addGeneralViewConfiguration('currentMode', $uncompressedParameters['mode']);
        $userIsAllowedToInputData = $this->controller->getFrontendUserManager()->userIsAllowedToInputData() && ! $mainRepository->isInDraftWorkspace($uid);
        $this->addGeneralViewConfiguration('userIsAllowedToInputData', $userIsAllowedToInputData);
        $this->addGeneralViewConfiguration('isInDraftWorkspace', $mainRepository->isInDraftWorkspace($uid));
        $this->addGeneralViewConfiguration('activeFolder', $this->controller->getActiveFolder($viewIdentifier, $uncompressedParameters['folder'] ?? null));

        // Sets the fields configuration
        $fieldConfigurationManager->setStaticFieldsConfiguration($viewIdentifier, $mainRepository);

        // Adds the dynamic configuration
        $fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);

        // Gets the folders
        $viewFolders = $this->controller->getFolders($viewIdentifier);

        // Adds the title
        $title = $this->parseTitle(
            $viewIdentifier,
            [
                'general' => $this->getGeneralViewConfiguration(),
                'field' => $fieldConfigurationManager->getFieldsConfiguration()
            ]
        );
        $this->addGeneralViewConfiguration('title', $title);

        // Sets the view configuration
        $viewConfiguration = [
            'general' => $this->getGeneralViewConfiguration(),
            'fields' => $fieldConfigurationManager->getFieldsConfiguration(),
            'folders' => $viewFolders
        ];

        return $viewConfiguration;
    }
}
