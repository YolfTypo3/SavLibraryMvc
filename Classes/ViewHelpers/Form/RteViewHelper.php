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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers\Form;

use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Richtext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;


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
final class RteViewHelper extends AbstractFormFieldViewHelper
{

    /**
     * @var string
     */
    protected $tagName = 'div';
    
    /**
     * Initializes arguments.
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('property', 'string', 'the property');
        $this->registerArgument('rows', 'int', 'The number of rows of a text area');
        $this->registerArgument('cols', 'int', 'The number of columns of a text area');
        $this->registerArgument('value', 'string', 'the value');
    }

    /**
     * Renders the rte.
     *
     * @return string
     */
    public function render(): string
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->setRespectSubmittedDataValue(true);
        $controller = $this->getRequest()->getAttribute('controller');
        $pageId = $controller->getPageId();
        
        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $GLOBALS['BE_USER']->uc['edit_RTE'] = true;
        $GLOBALS['BE_USER']->user['lang'] = null;
        $GLOBALS['LANG'] = $controller->getLanguageService();

        $richtextConfigurationProvider = GeneralUtility::makeInstance(Richtext::class);
        $richtextConfiguration = $richtextConfigurationProvider->getConfiguration('', '', $pageId, '', [
            'richtext' => true,
            'richtextConfiguration' => 'sav_library_mvc'
        ]);

        // Renders the Rich Text Element
        $nodeFactory = GeneralUtility::makeInstance(NodeFactory::class);
        $formData = [
            'renderType' => 'text',
            'fieldName' => $name,
            'processedTca' => [
                'columns' => [
                    $name => [
                        'config' => [
                            'type' => 'text',
                        ]
                    ]
                ]
            ],
            'databaseRow' => [
                'uid' => ''
            ],
            'tableName' => '',
            'defaultLanguageDiffRow' => [
            ],
            'recordTypeValue' => null,
            'effectivePid' => null,
            'inlineStructure' => [],
            'row' => [
                'pid' => $pageId,
            ],
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'cols' => $this->arguments['cols'],
                        'rows' => $this->arguments['rows'],
                        'enableRichtext' => true,
                        'richtextConfiguration' => $richtextConfiguration,
                        'richtextConfigurationName' => null,
                    ],
                    'defaultExtras' => 'richtext[]:rte_transform[mode=ts_css]'
                ],
                'itemFormElID' => null,
                'itemFormElName' => $name,
                'itemFormElValue' => html_entity_decode($this->arguments['value'], ENT_QUOTES)
            ]
        ];

        $formResult = $nodeFactory->create($formData)->render();

        // Adds javaScript and cascading style sheet
        AdditionalHeaderManager::loadJavaScriptModules($formResult['javaScriptModules']);
        AdditionalHeaderManager::addCascadingStyleSheet($formResult['stylesheetFiles'][0]);

        $htmlArray = [];
        $htmlArray[] = htmlspecialchars($formResult['html']);

        return implode(chr(10), $htmlArray);
    }
}
