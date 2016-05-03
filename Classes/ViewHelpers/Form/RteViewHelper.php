<?php
namespace SAV\SavLibraryMvc\ViewHelpers\Form;

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
use SAV\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * RTE view helper.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.rte name="rteName" rows="5" cols="50" value="The text to edit" />
 * <f:form.rte property="rteName" rows="5" cols="50" value="The text to edit" />
 * </code>
 *
 * Output:
 * The rich text editor
 *
 * @package SavLibraryMvc
 */
class RteViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\TextareaViewHelper
{

    /**
     * Renders the rte htmlarea.
     *
     * @return string
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     *         @api
     */
    public function render()
    {
        // Renders the parent class to have everything correctly initialized.
        parent::render();

        // Creates the RTE
        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
        $formData = array(
            'renderType' => 'text',
            'inlineStructure' => array(),
            'row' => array(
                'pid' => $GLOBALS['TSFE']->id
            ),
            'parameterArray' => array(
                'fieldConf' => array(
                    'config' => array(
                        'cols' => $this->arguments['cols'],
                        'rows' => $this->arguments['rows'],
                    ),
                    'defaultExtras' => 'richtext[]'
                ),
                'itemFormElName' => $this->getName(),
                'itemFormElValue' => html_entity_decode($this->getValueAttribute(), ENT_QUOTES, $GLOBALS['TSFE']->renderCharset)
            )
        );

        // Renders the Rich Text Element
        $formResult = $nodeFactory->create($formData)->render();

        // Adds the style sheets
        foreach ($formResult['stylesheetFiles'] as $stylesheetFile) {
            AdditionalHeaderManager::addCascadingStyleSheet($stylesheetFile);
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

        // Adds the javascript for processing the field on save action
        AdditionalHeaderManager::addJavaScriptInlineCode('troc', $formResult['additionalJavaScriptSubmit'][0]);

        // Cleans the html form result
        // @TODO Adds what is necessary to keep the form result and removes the cleaning
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
