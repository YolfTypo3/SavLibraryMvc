<?php

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

namespace YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor;

use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Richtext;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Default Rich Text Editor renderer
 */
class RichTextEditorRenderer extends AbstractRichTextEditorRenderer
{
    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render(): string
    {
        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $GLOBALS['BE_USER']->uc['edit_RTE'] = true;

        $richtextConfigurationProvider = GeneralUtility::makeInstance(Richtext::class);
        $richtextConfiguration = $richtextConfigurationProvider->getConfiguration('', '', $this->getPageId(), '', [
            'richtext' => true,
            'richtextConfiguration' => 'sav_library_mvc'
        ]);

        // Renders the Rich Text Element
        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
        $formData = [
            'renderType' => 'text',
            'inlineStructure' => [],
            'row' => [
                'pid' => $this->getPageId()
            ],
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'cols' => $this->arguments['cols'],
                        'rows' => $this->arguments['rows'],
                        'enableRichtext' => true,
                        'richtextConfiguration' => $richtextConfiguration
                    ],
                    'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'
                ],
                'itemFormElName' => $this->name,
                'itemFormElValue' => html_entity_decode($this->arguments['value'], ENT_QUOTES)
            ]
        ];
        $formResult = $nodeFactory->create($formData)->render();

        // Loads the ckeditor javascript file
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile('EXT:rte_ckeditor/Resources/Public/JavaScript/Contrib/ckeditor.js');

        // Gets the CKEDITOR.replace callback function and inserts it in the footer
        $sanitizedFieldId = $this->sanitizeFieldId($this->name);
        $requireJsModule = $formResult['requireJsModules'][0];
        if ($requireJsModule instanceof \TYPO3\CMS\Core\Page\JavaScriptModuleInstruction) {
            $configuration = $requireJsModule->getItems()[0]['args'][0]['configuration'];
            $javaScript = [];
            $javaScript[] = 'var editor_' . $sanitizedFieldId .
                ' = CKEDITOR.replace("' . $sanitizedFieldId . '",' .
                json_encode($configuration) . ');';
            $javaScript[] = 'editor_' . $sanitizedFieldId . '.on(\'change\', function(evt) {';
            $javaScript[] = '    document.changed = true;';
            $javaScript[] = '});';
            $pageRenderer->addJsFooterInlineCode($sanitizedFieldId, implode(chr(10), $javaScript));
        } else {
            $mainModuleName = key($requireJsModule);
            $callBackFunction = $requireJsModule[$mainModuleName];

            $match = [];
            if (preg_match('/CKEDITOR\.replace\(.+\);/', $callBackFunction, $match)) {
                $javaScript = [];
                $javaScript[] = 'var editor_' . $sanitizedFieldId . ' = ' . $match[0];
                $javaScript[] = 'editor_' . $sanitizedFieldId . '.on(\'change\', function(evt) {';
                $javaScript[] = '    document.changed = true;';
                $javaScript[] = '});';
                $pageRenderer->addJsFooterInlineCode($sanitizedFieldId, implode(chr(10), $javaScript));
            }
        }

        // Renders the view helper
        $htmlArray = [];
        $htmlArray[] = $formResult['html'];

        return implode(chr(10), $htmlArray);
    }

    /**
     * @param string $itemFormElementName
     * @return string
     */
    protected function sanitizeFieldId(string $itemFormElementName): string
    {
        $fieldId = (string)preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $itemFormElementName);
        return htmlspecialchars((string)preg_replace('/^[^a-zA-Z]/', 'x', $fieldId));
    }

}
