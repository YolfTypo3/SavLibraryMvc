<?php
namespace YolfTypo3\SavLibraryMvc\ViewConfiguration;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Managers\FieldConfigurationManager;
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
     * @var integer
     */
    protected $viewIdentifier = null;

    /**
     *
     * @var FieldConfigurationManager
     */
    protected $fieldConfigurationManager;

    /**
     *
     * @var ObjectStorage $object
     */
    protected $object;

    /**
     * Constructor
     *
     * @param DefaultController $controller
     * @return void
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Injects the template parser
     *
     * @param \YolfTypo3\SavLibraryMvc\Parser\TemplateParser $templateParser
     * @return void
     */
    public function injectTemplateParser(\YolfTypo3\SavLibraryMvc\Parser\TemplateParser $templateParser)
    {
        $this->templateParser = $templateParser;
        $this->templateParser->setController($this->controller);
    }

    /**
     * Injects the field configuration manager
     *
     * @param FieldConfigurationManager $fieldConfigurationManager
     *
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
     * return int
     */
    public function getViewIdentifier(): int
    {
        if ($this->viewIdentifier !== null) {
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
                if (! empty($viewWithConditionConfiguration['cutIf']) || ! empty($viewWithConditionConfiguration['showIf'])) {
                    // Builds a field configuration manager
                    $fieldConfigurationManager = GeneralUtility::makeInstance(FieldConfigurationManager::class);
                    $fieldConfigurationManager::storeFieldsConfiguration();
                    $fieldConfigurationManager->setFieldConfiguration($viewWithConditionConfiguration);
                    $fieldConfigurationManager->addDynamicFieldsConfiguration($this->object);
                    $fieldConfigurationManager::restoreFieldsConfiguration();

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
    public function getGeneralViewConfiguration($key = null)
    {
        if ($key === null) {
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
            if ($activeFolder > 0 && empty($viewFolders[$activeFolder])) {
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

        // Processes the marker
        $matches = [];
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
        $title = $this->templateParser->parse($title, [
            'general' => $configuration['general'],
            'fields' => $configuration['fields']
        ]);

        return $title;
    }
}
?>