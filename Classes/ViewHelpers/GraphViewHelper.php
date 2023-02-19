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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;
use YolfTypo3\SavCharts\XmlParser\XmlParser;

/**
 * View helper which builds the src attribute for an icon
 *
 * @package SavLibraryMvc
 */
class GraphViewHelper extends AbstractViewHelper
{

    /**
     * The xml parser
     *
     * @var XmlParser
     */
    protected $xmlParser;

    /**
     * If true the template is not processed
     *
     * @var bool
     */
    protected $doNotProcessTemplate = false;

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('field', 'array', 'Field', true);
        $this->registerArgument('contentUid', 'int', 'Content uid', true);
    }

    /**
     * Renders the content.
     *
     * @return string Rendered string
     */
    public function render(): string
    {
        $content = '';
        // Checks that sav_charts is loaded
        if (ExtensionManagementUtility::isLoaded('sav_charts')) {

            // Creates the xml parser
            $this->xmlParser = GeneralUtility::makeInstance(XmlParser::class);
            $this->xmlParser->clearXmlTagResults();

            // Processes the tags
            $this->processTags();

            // Defines the file name for the resulting image
            if ($this->doNotProcessTemplate === false) {
                $content = $this->processTemplate();
            }
        } else {
            FlashMessages::addError('error.graphExtensionNotLoaded');
        }

        return $content;
    }

    /**
     * Processes the tags.
     *
     * @return void
     */
    protected function processTags()
    {
        // Gets the arguments
        $field = $this->arguments['field'];
        $tags = $field['tags'];

        // Sets the markers if any
        if (! empty($tags)) {
            $tags = explode(',', $tags);

            // Gets the special parameter
            $special = $this->templateVariableContainer->get('general')['special'];

            // Gets the uid
            $uncompressedParameters = AbstractController::uncompressParameters($special);
            $uid = $uncompressedParameters['uid'];

            // Gets the controller
            $controllerObjectName = $this->getRequest()->getControllerObjectName();
            $controller = GeneralUtility::makeInstance($controllerObjectName);

            // Gets the main repository
            $mainRepository = $controller->getMainRepository();

            // Gets the object from the uid
            $object = $mainRepository->findByUid($uid);

            // Builds a field configuration manager
            $fieldConfigurationManager = $controller->getFieldConfigurationManager();
            $fieldConfigurationManager->storeFieldsConfiguration();
            $fieldConfigurationManager->addDynamicFieldsConfiguration($object);
            $fieldConfigurationManager->restoreFieldsConfiguration();

            // Processes the tags
            foreach ($tags as $tag) {
                $match = [];
                if (preg_match('/^([0-9A-Za-z_]+)#([0-9A-Za-z_]+)\s*=\s*(.*)$/', trim($tag), $match)) {

                    $name = $match[1];
                    $id = $match[2];
                    $value = $match[3];
                    $value = $fieldConfigurationManager->parseFieldTags($value);

                    // Checks if the not empty condition is satisfied
                    if (strtolower($value) == 'notempty[]') {
                        $this->doNotProcessTemplate = true;
                        continue;
                    } else {
                        $value = preg_replace('/(?i)notempty\[([^\]]+)\]/', '$1', $value);
                    }

                    // Processes the tag if it has been replaced.
                    if (preg_match('/^###[0-9A-Za-z_]+###$/', $value) == 0) {
                        $xml = '<' . $name . ' id ="' . $id . '">' . $value . '</' . $name . '>';

                        $this->xmlParser->loadXmlString($xml);
                        $this->xmlParser->parseXml();
                    }
                }
            }
        }
    }

    /**
     * Processes the template.
     *
     * @return string The image element or empty string
     */
    protected function processTemplate(): string
    {
        $content = '';

        // Gets the arguments
        $field = $this->arguments['field'];
        $contentUid = $this->arguments['contentUid'];

        // Processes the template
        $graphTemplate = $field['graphTemplate'];

        if (empty($graphTemplate)) {
            FlashMessages::addError('error.graphTemplateNotSet');
        } else {
            if (file_exists(AbstractController::getSitePath() . $graphTemplate)) {
                $this->xmlParser->loadXmlFile($graphTemplate);
                $this->xmlParser->parseXml();
                // Post-processing to get the javascript
                $result = $this->xmlParser->postProcessing();

                // Adds the latest javascript file
                $javaScriptRootDirectory = ExtensionManagementUtility::extPath('sav_charts') . 'Resources/Public/JavaScript';
                $javaScriptFiles = scandir($javaScriptRootDirectory, SCANDIR_SORT_DESCENDING);
                $extensionWebPath = AbstractController::getExtensionWebPath('sav_charts');
                $javaScriptFooterFile = $extensionWebPath . 'Resources/Public/JavaScript/' . $javaScriptFiles[0];
                AdditionalHeaderManager::addJavaScriptFooterFile($javaScriptFooterFile);

                // Prepares the content
                $canvases = $result['canvases'];
                if (! empty($canvases)) {
                    foreach ($canvases as $canvas) {
                        $chartId = str_replace('###contentObjectUid###', $contentUid, $canvas['chartId']);
                        $javaScriptFooterInlineCode = str_replace('###contentObjectUid###', $contentUid, $result['javaScriptFooterInlineCode']);

                        $content .= '<div class="charts chart' . $chartId . '">' . '<canvas id="canvas' . $chartId . '" width="' . $canvas['width'] . '" height="' . $canvas['height'] . '"></canvas>' . '</div>';

                        // Adds the javacript
                        AdditionalHeaderManager::addJavaScriptFooterInlineCode($chartId, $javaScriptFooterInlineCode);
                    }
                }
            } else {
                FlashMessages::addError('error.graphTemplateUnknown', [
                    $graphTemplate
                ]);
            }
        }

        return $content;
    }

    /**
     * Shortcut for retrieving the request from the controller context
     *
     * @return Request
     */
    protected function getRequest(): Request
    {
        if (method_exists($this->renderingContext, 'getRequest')) {
            return $this->renderingContext->getRequest();
        } else {
            // For TYPO3 v10
            // @extensionScannerIgnoreLine
            return $this->renderingContext
                ->getControllerContext()
                ->getRequest();
        }
    }

}
