<?php
namespace YolfTypo3\SavLibraryMvc\Parser;

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
use TYPO3\CMS\Fluid\View\StandaloneView;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Template Parser
 */
class TemplateParser
{

    /**
     *
     * @var \YolfTypo3\SavLibraryMvc\Controller\DefaultController
     */
    protected $controller = NULL;

    /**
     * Sets the controller
     *
     * @param \YolfTypo3\SavLibraryMvc\Controller\DefaultController $controller
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Parses a content
     *
     * @param string $content
     *            The content to parse.
     * @param array $arguments
     *            The arguments for the parser.
     * @param string $nameSpace
     *            The name space.
     * @return string The parsed content
     */
    public function parse($content, $arguments = array(), $nameSpace = '{namespace sav=YolfTypo3\\SavLibraryMvc\\ViewHelpers}')
    {
        // Gets a standalone view
        $standaloneView = $this->controller->getObjectManager()->get(StandaloneView::class);

        // Sets the template source
        $standaloneView->setTemplateSource($nameSpace . '<f:format.raw>' . $content . '</f:format.raw>');

        // Sets the controller extension name
        $standaloneView->getRequest()->setControllerExtensionName($this->controller->getRequest()
            ->getControllerExtensionName());

        // Sets the controller name
        $standaloneView->getRequest()->setControllerName($this->controller->getRequest()
            ->getControllerName());

        // Sets the controller name
        $standaloneView->getRequest()->setPluginName($this->controller->getRequest()
            ->getPluginName());

        // Transfers the special argument to the controller argument
        $standaloneView->getRequest()->setArgument('special', $arguments['general']['special']);

        //
        $partialRootPaths = AbstractController::getPartialRootPaths();
        $convertedPartialRootPaths = array();
        foreach($partialRootPaths as $partialRootPathKey => $partialRootPath) {
            $convertedPartialRootPaths[$partialRootPathKey] = GeneralUtility::getFileAbsFileName($partialRootPath);
        }

        // Sets the partial root paths
        $standaloneView->setPartialRootPaths($convertedPartialRootPaths);

        // Assigns the arguments
        foreach ($arguments as $argumentKey => $argument) {
            $standaloneView->assign($argumentKey, $argument);
        }

        // Renders the view
        return $standaloneView->render();
    }
}
?>
