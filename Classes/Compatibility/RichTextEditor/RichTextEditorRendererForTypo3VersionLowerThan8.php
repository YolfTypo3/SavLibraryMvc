<?php
namespace YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor;

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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Form\NodeFactory;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * Rich text editor renderer for TYPO3 version < 8
 *
 * @extensionScannerIgnoreFile
 * @todo Will be removed in TYPO3 v10
 */
class RichTextEditorRendererForTypo3VersionLowerThan8 extends AbstractRichTextEditorRenderer
{
    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render() : string
    {
        // Renders the Rich Text Element
        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);

        $formData = [
            'renderType' => 'text',
            'inlineStructure' => [],
            'databaseRow' => [
                'pid' => $GLOBALS['TSFE']->id
            ],
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'cols' => $this->arguments['cols'],
                        'rows' => $this->arguments['rows']
                    ],
                    'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'
                ],
                'itemFormElName' => $this->name,
                'itemFormElValue' => html_entity_decode($this->valueAttribute, ENT_QUOTES)
            ]
        ];

        // Renders the Rich Text Element
        $formResult = $nodeFactory->create($formData)->render();

        // Adds the style sheets
        foreach ($formResult['stylesheetFiles'] as $stylesheetFile) {
            AdditionalHeaderManager::addCascadingStyleSheet('typo3/' . $stylesheetFile);
        }

        // Defines the TYPO3 variable
        AdditionalHeaderManager::addJavaScriptInlineCode('variable', 'var TYPO3 = TYPO3 || {}; TYPO3.jQuery = jQuery.noConflict(true);');

        // Adds the require javascript modules
        foreach ($formResult['requireJsModules'] as $requireJsModule) {
            self::loadRequireJsModule($requireJsModule);
        }

        // Loads the jquery javascript file
        $extensionWebPath = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath('core'));
        AdditionalHeaderManager::addJavaScriptFile($extensionWebPath . 'Resources/Public/JavaScript/Contrib/jquery/jquery-' . PageRenderer::JQUERY_VERSION_LATEST . '.js');

        // Loads the ext Js
        self::loadExtJS();

        // Loads other javascript files
        $extensionWebPath = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath('backend'));
        AdditionalHeaderManager::addJavaScriptFile($extensionWebPath . 'Resources/Public/JavaScript/notifications.js');
        $extensionWebPath = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath('rtehtmlarea'));
        AdditionalHeaderManager::addJavaScriptFile($extensionWebPath . 'Resources/Public/JavaScript/HTMLArea/NameSpace/NameSpace.js');

        // Adds information for the settings
        AdditionalHeaderManager::addInlineSettingArray(
            'FormEngine',
            [
                'formName' => 'data',
                'backPath' => ''
            ]
        );

        // Cleans the html form result
        $htmlFormResult = $formResult['html'];
        $htmlFormResult = preg_replace('/<input [^>]+>/', '', $htmlFormResult);

        // Renders the view helper
        $htmlArray = [];
        $htmlArray[] = $htmlFormResult;

        // Adds the javaScript after the textarea tag
        $htmlArray[] = '<script type="text/javascript">';
        $htmlArray[] = '/*<![CDATA[*/';
        foreach ($formResult['additionalJavaScriptPost'] as $additionalJavaScriptPost) {
            $htmlArray[] = $additionalJavaScriptPost;
        }
        $htmlArray[] = '/*]]>*/';
        $htmlArray[] = '</script>';

        return implode(chr(10), $htmlArray);
    }

    /**
     * Loads a required Js module
     *
     * @param string $mainModuleName
     * @return void
     */
    public static function loadRequireJsModule(string $mainModuleName)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        if(is_array($mainModuleName)) {
            $pageRenderer->loadRequireJsModule(key($mainModuleName), current($mainModuleName));
        } else {
            $pageRenderer->loadRequireJsModule($mainModuleName);
        }
    }

    /**
     * Loads the extJS library
     *
     * @return void
     */
    public static function loadExtJS()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadExtJS();
    }
}
?>
