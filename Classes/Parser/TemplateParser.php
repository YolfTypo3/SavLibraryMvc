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

namespace YolfTypo3\SavLibraryMvc\Parser;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
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
    public function setController(DefaultController $controller)
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
    public function parse(string $content, array $arguments = [], string $nameSpace = '{namespace sav=YolfTypo3\\SavLibraryMvc\\ViewHelpers}'): string
    {
        // Do not parse if the content is empty
        if (empty($content)) {
            return '';
        }

        // Gets a standalone view
        /** @var StandaloneView $standaloneView */
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->getRequest()->setOriginalRequest($this->controller->getRequest());
        $standaloneView->getRequest()->setControllerExtensionName($this->controller->getControllerExtensionName());
        $standaloneView->getRequest()->setControllerName($this->controller->getControllerName());
        $standaloneView->getRequest()->setControllerActionName($this->controller->getControllerActionName());

        // Sets the template source
        $standaloneView->setTemplateSource($nameSpace . '<f:format.raw>' . $content . '</f:format.raw>');

        // Sets the partial root paths
        $partialRootPaths = $this->controller->getPartialRootPaths();
        $convertedPartialRootPaths = [];
        foreach ($partialRootPaths as $partialRootPathKey => $partialRootPath) {
            $convertedPartialRootPaths[$partialRootPathKey] = GeneralUtility::getFileAbsFileName($partialRootPath);
        }
        $standaloneView->setPartialRootPaths($convertedPartialRootPaths);

        // Assigns the arguments
        foreach ($arguments as $argumentKey => $argument) {
            $standaloneView->assign($argumentKey, $argument);
        }

        // Renders the view
        return $standaloneView->render();
    }
}
