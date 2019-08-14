<?php
namespace YolfTypo3\SavLibraryMvc\Parser;

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
use TYPO3\CMS\Fluid\View\StandaloneView;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;

/**
 * Template Parser
 *
 * @package SavLibraryMvc
 */
class TemplateParser
{

    /**
     *
     * @var DefaultController
     */
    protected $controller = null;

    /**
     * Sets the controller
     *
     * @param DefaultController $controller
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
    public function parse($content, $arguments = [], $nameSpace = '{namespace sav=YolfTypo3\\SavLibraryMvc\\ViewHelpers}')
    {
        // Creates the object manager
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // Gets a standalone view
        $standaloneView = $objectManager->get(StandaloneView::class);

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
        $convertedPartialRootPaths = [];
        foreach ($partialRootPaths as $partialRootPathKey => $partialRootPath) {
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
