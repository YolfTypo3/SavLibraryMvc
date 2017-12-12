<?php
namespace YolfTypo3\SavLibraryMvc\ViewConfiguration;

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
        $title = $this->parseTitle($viewIdentifier, array(
            'general' => $this->getGeneralViewConfiguration(),
            'fields' => $this->fieldConfigurationManager->getFieldsConfiguration()
        ));
        $this->addGeneralViewConfiguration('title', $title);

        // Gets the folders
        $viewFolders = $this->getViewFolders($viewIdentifier);

        // Sets the view configuration
        $viewConfiguration = array(
            'general' => $this->getGeneralViewConfiguration(),
            'fields' => $this->fieldConfigurationManager->getFieldsConfiguration(),
            'folders' => $viewFolders
        );

        return $viewConfiguration;
    }
}
?>