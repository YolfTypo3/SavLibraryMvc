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
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
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

        // Builds the rendering context
        $context = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $context->setControllerName($this->controller->getControllerName());
        $context->setControllerAction($this->controller->getControllerActionName());

        // Gets the view
        $view = $this->createView($nameSpace . '<f:format.raw>' . $content . '</f:format.raw>');

        // Assigns the arguments
        foreach ($arguments as $argumentKey => $argument) {
            $view->assign($argumentKey, $argument);
        }

        // Renders the view
        return $view->render() ?? '';
    }
    
    /**
     * Creates the view
     *
     * @param string $template
     * @param string $isTemplateFile
     *
     * @return mixed
     */
    public function createView(string $template): mixed
    {
        // Sets the partial root paths
        $partialRootPaths = $this->controller->getPartialRootPaths();
        $convertedPartialRootPaths = [];
        foreach ($partialRootPaths as $partialRootPathKey => $partialRootPath) {
            $convertedPartialRootPaths[$partialRootPathKey] = GeneralUtility::getFileAbsFileName($partialRootPath);
        }
        
        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $viewFactoryData = new (ViewFactoryData::class)(
            partialRootPaths: $convertedPartialRootPaths,
            request: $this->controller->getRequest(),
            );
        
        $view = $viewFactory->create($viewFactoryData);
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($template);

        return $view;
    }    
    
}
