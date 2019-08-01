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

use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Configuration\Richtext;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Defalt Rich Text Editor renderer
 */
class RichTextEditorRenderer extends AbstractRichTextEditorRenderer
{
    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render() : string
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
        $formData = [
            'renderType' => 'text',
            'inlineStructure' => [],
            'row' => [
                'pid' => $GLOBALS['TSFE']->id
            ],
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'cols' => $this->arguments['cols'],
                        'rows' => $this->arguments['rows'],
                        'enableRichtext' => true,
                        'richtextConfiguration' => $richtextConfiguration,
                    ],
                    'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'

                ],
                'itemFormElName' => $this->name,
                'itemFormElValue' => html_entity_decode($this->valueAttribute, ENT_QUOTES)
            ]
        ];
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
