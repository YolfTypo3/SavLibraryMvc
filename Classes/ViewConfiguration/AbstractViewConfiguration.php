<?php
namespace SAV\SavLibraryMvc\ViewConfiguration;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use SAV\SavLibraryMvc\Controller\AbstractController;
use SAV\SavLibraryMvc\Controller\FlashMessages;
use SAV\SavLibraryMvc\Managers\FieldConfigurationManager;

/**
 * Abstract view configuration for the SAV Library MVC
 */
abstract class AbstractViewConfiguration
{

    /**
     * Constants associated with the flag showNoAvailableInformation
     */
    const SHOW_MESSAGE = 0;

    const DO_NOT_SHOW_MESSAGE = 1;

    const DO_NOT_SHOW_EXTENSION = 2;

    /**
     * Pattern for the cutter
     */
    const CUT_IF_PATTERN = '/    (?:      (?:        \s+        (?P<connector>[\|&]|or|and|OR|AND)        \s+      )?      (?P<expression>        (?:        	FALSE | TRUE |	        (?:\#{3})?		        (?P<lhs>(?:(?:\w+\.)+)?\w+)		        \s*(?P<operator>=|!=|>=|<=|>|<)\s*		        (?P<rhs>[-\w]+|\#{3}[^\#]+\#{3})	        (?:\#{3})?				)      )    )  /x';

    /**
     *
     * @var \SAV\SavLibraryMvc\Controller\DefaultController
     */
    protected $controller = NULL;

    /**
     *
     * @var \SAV\SavLibraryMvc\Parser\TemplateParser
     */
    protected $templateParser = NULL;

    /**
     *
     * @var array
     */
    protected $generalViewConfiguration = array();

    /**
     *
     * @var integer
     */
    protected $viewIdentifier = NULL;

    /**
     *
     * @var \SAV\SavLibraryMvc\Managers\FieldConfigurationManager
     */
    protected $fieldConfigurationManager;

    /**
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     */
    protected $object;

    /**
     * Constructor
     *
     * @param \SAV\SavLibraryMvc\Controller\DefaultController $controller
     * @return void
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Injects the template parser
     *
     * @param \SAV\SavLibraryMvc\Parser\TemplateParser $templateParser
     * @return void
     */
    public function injectTemplateParser(\SAV\SavLibraryMvc\Parser\TemplateParser $templateParser)
    {
        $this->templateParser = $templateParser;
        $this->templateParser->setController($this->controller);
    }

    /**
     * Injects the field configuration manager
     *
     * @param
     *            \SAV\SavLibraryMvc\Managers\FieldConfigurationManager
     * @return void
     */
    public function injectFieldConfigurationManager(FieldConfigurationManager $fieldConfigurationManager)
    {
        $this->fieldConfigurationManager = $fieldConfigurationManager;
    }

    /**
     * Gets the view type.
     *
     * return string
     */
    protected function getViewType()
    {
        $viewType = lcfirst(preg_replace('/^.+?\\\\(\w+)Configuration$/', '$1', get_class($this)));
        return $viewType;
    }

    /**
     * Gets the view identifer.
     *
     * return integer
     */
    public function getViewIdentifier()
    {
        if ($this->viewIdentifier !== NULL) {
            return $this->viewIdentifier;
        }

        // Gets the view Identifiers from the controller
        $viewIdentifiers = $this->controller->getViewIdentifiers();

        // Gets the view type
        $viewType = $this->getViewType();

        // Gets the views with condition if any
        $viewsWithCondition = $viewIdentifiers['viewsWithCondition'][$viewType];
        if (empty($viewsWithCondition)) {
            return $viewIdentifiers[$viewType];
        } else {
            foreach ($viewsWithCondition as $viewWithConditionKey => $viewWithCondition) {
                // Gets the configuratop,
                $viewWithConditionConfiguration = $viewWithCondition['config'];

                // Processes the condition if it exists
                if (!empty($viewWithConditionConfiguration['cutIf']) || !empty($viewWithConditionConfiguration['showIf'])) {
                    // Builds a field configuration manager
                    $fieldConfigurationManager = GeneralUtility::makeInstance(FieldConfigurationManager::class);
                    $fieldConfigurationManager->setFieldConfiguration($viewWithConditionConfiguration);
                    $fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);

                    // Checks the cutif condition
                    if ($fieldConfigurationManager->cutIf() === FALSE) {
                        return $viewWithConditionKey;
                    }
                }
            }

            // If no FALSE condition was found, return the default view
            return $viewIdentifiers[$viewType];
        }
    }

    /**
     * Adds a value to the general view configuration.
     *
     * @param string $key
     *            The key
     * @param mixed $value
     *            The value
     *            return none
     */
    public function addGeneralViewConfiguration($key, $value)
    {
        $this->generalViewConfiguration[$key] = $value;
    }

    /**
     * Gets the general view configuration.
     *
     * @param string $key
     *            The key
     *            return mixed
     */
    public function getGeneralViewConfiguration($key = NULL)
    {
        if ($key === NULL) {
            return $this->generalViewConfiguration;
        }
        return $this->generalViewConfiguration[$key];
    }

    /**
     * Gets the view folder
     *
     * @param string $viewIdentifier
     *            The view identifier
     * @return array The folder configuration
     */
    protected function getViewFolders($viewIdentifier)
    {
        $viewFolders = $this->controller->getFolders($viewIdentifier);

        // Sets the folder key
        $special = $this->getGeneralViewConfiguration('special');
        $uncompressedParameters = AbstractController::uncompressParameters($special);
        if ($uncompressedParameters['folder']) {
            $activeFolder = (empty($viewFolders) ? 0 : $uncompressedParameters['folder']);
            // Checks if the folder exists otherwise return the first folder
            if($activeFolder > 0 && empty($viewFolders[$activeFolder])) {
                $activeFolder = key($viewFolders);
            }
        } else {
            $activeFolder = (empty($viewFolders) ? 0 : key($viewFolders));
        }
        $this->addGeneralViewConfiguration('activeFolder', $activeFolder);
        return $viewFolders;
    }

    /**
     * Replaces the localisation markers and parses the template
     *
     * @param string $viewIdentifier
     *            The view identifier
     * @param array $configuration
     *            The configuration used for replacements
     *
     * @return string The parsed title
     */
    protected function parseTitle($viewIdentifier, $configuration)
    {

        // Gets and processes the title
        $title = $this->controller->getViewTitleBar($viewIdentifier);

        // Processes the localization markers
        $title = $this->fieldConfigurationManager->parseLocalizationTags($title);

        // Processes the markers
        preg_match_all('/###(\w+)###/', $title, $matches);

        // Gets the view type
        $viewType = $this->getViewType();

        foreach ($matches[0] as $keyMatch => $match) {
            $fieldName = $matches[1][$keyMatch];
            switch ($viewType) {
                case 'listView':
                    if ($configuration['fields'][$fieldName]['orderLinkInTitle']) {
                        // Gets the associated whereTags
                        $configuration['fields'][$fieldName]['orderAsc'] = $this->controller->getMainRepository()->getWhereTagByTitle($fieldName . '+');
                        $configuration['fields'][$fieldName]['orderDesc'] = $this->controller->getMainRepository()->getWhereTagByTitle($fieldName . '-');
                        // Sets the default pattern for the display
                        if (! isset($configuration['fields'][$fieldName]['orderLinkInTitleSetup'])) {
                            $configuration['fields'][$fieldName]['orderLinkInTitleSetup'] = ':link:';
                        }
                        $replacementString = '<f:render partial="TitleBars/OrderLinks/renderField.html" arguments="{general:general, field:fields.' . $fieldName . '}" />';
                    } else {
                        $replacementString = '{fields.' . $fieldName . '.label}';
                    }
                    break;
                default:
                    $replacementString = '{fields.' . $fieldName . '.value}';
            }
            $title = str_replace($match, $replacementString, $title);
        }

        // Parses the title template
        $title = $this->templateParser->parse($title, array(
            'general' => $configuration['general'],
            'fields' => $configuration['fields']
        ));

        return $title;
    }

    /**
     * Processes localization tags
     *
     * @param $input string
     *            String to process
     * @return string
     */
    public function processLocalizationTags($input)
    {
        // Processes labels associated with fields
        if (preg_match_all('/\$\$\$label\[([^\]]+)\]\$\$\$/', $input, $matches)) {

            foreach ($matches[1] as $matchKey => $match) {
                // Checks if the label is in language files, no default table is assumed
                // In that case the full name must be used, i.e. tableName.fieldName
                $label = LocalizationUtility::translate($match, AbstractController::getControllerExtensionKey());
                if (! empty($label)) {
                    $input = str_replace($matches[0][$matchKey], $label, $input);
                } else {
                    // Checks if the label is associated with the current table
                    $label = LocalizationUtility::translate($this->controller->getMainRepository()->resolveModelClassName() . '.' . $match, AbstractController::getControllerExtensionKey());
                    if (! empty($label)) {
                        $input = str_replace($matches[0][$matchKey], $label, $input);
                    } else {
                        FlashMessages::addError('error.missingLabel');
                    }
                }
            }
        }

        // Processes labels as $$$label$$$
        preg_match_all('/\$\$\$([^\$]+)\$\$\$/', $input, $matches);
        foreach ($matches[1] as $matchKey => $match) {
            $label = LocalizationUtility::translate($match, AbstractController::getControllerExtensionKey());
            $input = str_replace($matches[0][$matchKey], $label, $input);
        }

        return $input;
    }
}

?>