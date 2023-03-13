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

namespace YolfTypo3\SavLibraryMvc\ViewConfiguration;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Parser\TemplateParser;

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
    const CUT_IF_PATTERN = '/    (?:      (?:        \s+        (?P<connector>[\|&]|or|and|OR|AND)        \s+      )?      (?P<expression>        (?:        	false | true |	        (?:\#{3})?		        (?P<lhs>(?:(?:\w+\.)+)?\w+)		        \s*(?P<operator>=|!=|>=|<=|>|<)\s*		        (?P<rhs>[-\w]+|\#{3}[^\#]+\#{3})	        (?:\#{3})?				)      )    )  /x';

    /**
     * Controller
     *
     * @var DefaultController
     */
    protected $controller = null;

    /**
     *
     * @var TemplateParser
     */
    protected $templateParser = null;

    /**
     *
     * @var array
     */
    protected $generalViewConfiguration = [];

    /**
     *
     * @var int
     */
    protected $viewIdentifier = null;

    /**
     * Storage object
     *
     * @var ObjectStorage $object
     */
    protected $object;

    /**
     * Sets the controller
     *
     * @param DefaultController $controller
     *
     * @return void
     */
    public function setController(DefaultController $controller)
    {
        $this->controller = $controller;
        $this->templateParser->setController($controller);
    }

    /**
     * Injects the template parser
     *
     * @param TemplateParser $templateParser
     *
     * @return void
     */
    public function injectTemplateParser(TemplateParser $templateParser)
    {
        $this->templateParser = $templateParser;
    }

    /**
     * Gets the view type.
     *
     * @return string
     */
    protected function getViewType():string
    {
        $viewType = lcfirst(preg_replace('/^.+?\\\\(\w+)Configuration$/', '$1', get_class($this)));
        return $viewType;
    }

    /**
     * Gets the view identifer.
     *
     * @param boolean $checkViewsWithCondition
     *
     * @return int
     */
    public function getViewIdentifier($checkViewsWithCondition = true): int
    {
        if ($this->viewIdentifier !== null) {
            return $this->viewIdentifier;
        }

        // Gets the view Identifiers from the controller
        $viewIdentifiers = $this->controller->getViewIdentifiers();

        // Gets the view type
        $viewType = $this->getViewType();

        // Gets the views with condition if any
        $viewsWithCondition = $viewIdentifiers['viewsWithCondition'][$viewType] ?? null;

        if ($checkViewsWithCondition === false || empty($viewsWithCondition)) {
            return $viewIdentifiers[$viewType];
        } else {
            foreach ($viewsWithCondition as $viewWithConditionKey => $viewWithCondition) {
                // Gets the configuration
                $viewWithConditionConfiguration = $viewWithCondition['config'];

                // Processes the condition if it exists
                if (! empty($viewWithConditionConfiguration['cutIf']) || ! empty($viewWithConditionConfiguration['showIf'])) {
                    // Builds a field configuration manager
                    $fieldConfigurationManager = $this->controller->getFieldConfigurationManager();
                    $fieldConfigurationManager->storeFieldsConfiguration();
                    $fieldConfigurationManager->setFieldConfiguration($viewWithConditionConfiguration);
                    $fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);
                    $fieldConfigurationManager->restoreFieldsConfiguration();

                    // Checks the cutif condition
                    if ($fieldConfigurationManager->cutIf() === false) {
                        return $viewWithConditionKey;
                    }
                }
            }

            // If no false condition was found, return the default view
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
     * @return void
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
     *
     * @return mixed
     */
    public function getGeneralViewConfiguration($key = null)
    {
        if ($key === null) {
            return $this->generalViewConfiguration;
        }
        return $this->generalViewConfiguration[$key];
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
        $title = $this->controller->getFieldConfigurationManager()->parseLocalizationTags($title);

        // Processes the marker
        $matches = [];
        preg_match_all('/###(\w+)###/', $title, $matches);

        // Gets the view type
        $viewType = $this->getViewType();

        foreach ($matches[0] as $keyMatch => $match) {
            $fieldName = $matches[1][$keyMatch];
            switch ($viewType) {
                case 'listView':
                    $orderLinkInTitle = $configuration['field'][$fieldName]['orderLinkInTitle'] ?? false;
                    if ($orderLinkInTitle) {
                        // Gets the associated whereTags
                        $configuration['field'][$fieldName]['orderAsc'] = $this->controller->getMainRepository()->getWhereTagByTitle($fieldName . '+');
                        $configuration['field'][$fieldName]['orderDesc'] = $this->controller->getMainRepository()->getWhereTagByTitle($fieldName . '-');
                        // Sets the default pattern for the display
                        if (! isset($configuration['field'][$fieldName]['orderLinkInTitleSetup'])) {
                            $configuration['field'][$fieldName]['orderLinkInTitleSetup'] = ':link:';
                        }
                        $replacementString = '<f:render partial="TitleBars/OrderLinks/renderField.html" arguments="{general:general, field:field.' . $fieldName . '}" />';
                    } else {
                        $replacementString = '{field.' . $fieldName . '.label}';
                    }
                    break;
                default:
                    $replacementString = '{field.' . $fieldName . '.value}';
            }
            $title = str_replace($match, $replacementString, $title);
        }

        // Parses the title template
        $title = $this->templateParser->parse(
            $title,
            [
                'general' => $configuration['general'],
                'field' => $configuration['field']
            ]
        );

        return $title;
    }
}