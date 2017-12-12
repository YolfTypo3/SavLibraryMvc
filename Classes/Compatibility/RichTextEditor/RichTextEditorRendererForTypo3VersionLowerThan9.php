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
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Configuration\Richtext;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Rich text editor renderer for TYPO3 version < 9
 */
class RichTextEditorRendererForTypo3VersionLowerThan9 extends AbstractRichTextEditorRenderer
{

    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render()
    {

        $richtextConfigurationProvider = GeneralUtility::makeInstance(Richtext::class);
        $richtextConfiguration = $richtextConfigurationProvider->getConfiguration(
            '',
            '',
            $GLOBALS['TSFE']->id,
            '',
            ['richtext' => true,
                'richtextConfiguration' => 'sav_library_mvc',
            ]
            );

        // Renders the Rich Text Element
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
                        'enableRichtext' => true,
                        'richtextConfiguration' => $richtextConfiguration,
                    ),
                    'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'

                ),
                'itemFormElName' => $this->name,
                'itemFormElValue' => html_entity_decode($this->valueAttribute, ENT_QUOTES, $GLOBALS['TSFE']->renderCharset)
            )
        );
        $formResult = $nodeFactory->create($formData)->render();

        // Loads the ckeditor javascript file
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile('EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/ckeditor.js');

        // Gets the CKEDITOR.replace callback function and inserts it in the footer
        $requireJsModule = $formResult['requireJsModules'][0];
        $mainModuleName =  key($requireJsModule);
        $callBackFunction = $requireJsModule[$mainModuleName];
        if (preg_match('/CKEDITOR\.replace\("(.+__(\w+)_)".+\);/', $callBackFunction, $match)) {
            $javaScript = [];
            $javaScript[] = 'var editor' . $match[2] . ' = ' . $match[0];
            $javaScript[] = 'editor' . $match[2] . '.on(\'change\', function(evt) {';
            $javaScript[] = '    document.changed = true;';
            $javaScript[] = '});';
            $pageRenderer->addJsFooterInlineCode($match[1], implode(chr(10), $javaScript));
        }

        // Renders the view helper
        $htmlArray = [];
        $htmlArray[] = $formResult['html'];

        return implode(chr(10), $htmlArray);
    }
}

?>
