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
    public function getConfiguration($arguments)
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

        // Sets general configuration values
        $this->addGeneralViewConfiguration('extensionKey', AbstractController::getControllerExtensionKey());
        $this->addGeneralViewConfiguration('controllerName', AbstractController::getControllerName());
        $this->addGeneralViewConfiguration('special', $special);
        $this->addGeneralViewConfiguration('contentUid', $this->controller->getContentObjectRenderer()->data['uid']);
        $this->addGeneralViewConfiguration('currentMode', $uncompressedParameters['mode']);
        $userIsAllowedToInputData = $this->controller->getFrontendUserManager()->userIsAllowedToInputData() && ! $mainRepository->isInDraftWorkspace($uid);
        $this->addGeneralViewConfiguration('userIsAllowedToInputData', $userIsAllowedToInputData);
        $this->addGeneralViewConfiguration('isInDraftWorkspace', $mainRepository->isInDraftWorkspace($uid));

        // Gets the fields configuration
        $this->fieldConfigurationManager->setStaticFieldsConfiguration($this->getViewIdentifier(), $mainRepository);

        // Adds the dynamic configuration
        $this->fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);

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

        // Gets the folders
        $viewFolders = $this->getViewFolders($viewIdentifier);

        // Sets the view configuration
        $viewConfiguration = [
            'general' => $this->getGeneralViewConfiguration(),
            'fields' => $this->fieldConfigurationManager::getFieldsConfiguration(),
            'folders' => $viewFolders
        ];

        return $viewConfiguration;
    }
}
?>