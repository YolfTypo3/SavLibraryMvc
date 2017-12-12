<?php
namespace YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor;

/*
 * This script belongs to the FLOW3 package "Fluid". *
 * *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version. *
 * *
 * This script is distributed in the hope that it will be useful, but *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN- *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser *
 * General Public License for more details. *
 * *
 * You should have received a copy of the GNU Lesser General Public *
 * License along with the script. *
 * If not, see http://www.gnu.org/licenses/lgpl.html *
 * *
 * The TYPO3 project - inspiring people to share! *
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Form\NodeFactory;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * Rich text editor renderer for TYPO3 version < 8
 */
class RichTextEditorRendererForTypo3VersionLowerThan8 extends AbstractRichTextEditorRenderer
{

    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render()
    {

        // Renders the Rich Text Element
        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);

        $formData = array(
            'renderType' => 'text',
            'inlineStructure' => array(),
            'databaseRow' => array(
                'pid' => $GLOBALS['TSFE']->id
            ),
            'parameterArray' => array(
                'fieldConf' => array(
                    'config' => array(
                        'cols' => $this->arguments['cols'],
                        'rows' => $this->arguments['rows']
                    ),
                    'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'
                ),
                'itemFormElName' => $this->name,
                'itemFormElValue' => html_entity_decode($this->valueAttribute, ENT_QUOTES, $GLOBALS['TSFE']->renderCharset)
            )
        );

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
            AdditionalHeaderManager::loadRequireJsModule($requireJsModule);
        }

        // Loads the jquery javascript file
        AdditionalHeaderManager::addJavaScriptFile(ExtensionManagementUtility::siteRelPath('core') . 'Resources/Public/JavaScript/Contrib/jquery/jquery-' . PageRenderer::JQUERY_VERSION_LATEST . '.js');

        // Loads the ext Js
        AdditionalHeaderManager::loadExtJS();

        // Loads other javascript files
        AdditionalHeaderManager::addJavaScriptFile(ExtensionManagementUtility::siteRelPath('backend') . 'Resources/Public/JavaScript/notifications.js');
        AdditionalHeaderManager::addJavaScriptFile(ExtensionManagementUtility::siteRelPath('rtehtmlarea') . 'Resources/Public/JavaScript/HTMLArea/NameSpace/NameSpace.js');

        // Adds information for the settings
        AdditionalHeaderManager::addInlineSettingArray('FormEngine', array(
            'formName' => 'data',
            'backPath' => ''
        ));


        // Cleans the html form result
        $htmlFormResult = $formResult['html'];
        $htmlFormResult = preg_replace('/<input [^>]+>/', '', $htmlFormResult);

        // Renders the view helper
        $htmlArray = array();
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
}

?>
