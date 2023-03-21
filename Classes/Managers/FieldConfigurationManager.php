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

namespace YolfTypo3\SavLibraryMvc\Managers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Domain\Repository\DefaultRepository;

/**
 * Field configurataion manager.
 */
class FieldConfigurationManager
{

    /**
     * Pattern for the cutter
     */
    const CUT_IF_PATTERN = '/
    (?:
      (?:
        \s+
        (?P<connector>[\|&]|or|and|OR|AND)
        \s+
      )?
      (?P<expression>
        (?P<lparenthesis>\s*?\(\s*?)?
        (?:
        	false | true |
	        (?:\#{3})?
		        (?P<lhs>(?:(?:\w+\.)+)?\w+)
		        \s*(?P<operator>=|!=|>=|<=|>|<|isnot|is)\s*
		        (?P<rhs>[-\w]+|\#{3}[^\#]+\#{3})
	        (?:\#{3})?
        )
        (?P<rparenthesis>\s*?\)\s*?)?
      )
    )
  /x';

    const RENDER_FILE_AS_IMAGE_OBJECT = 1;
    const RENDER_FILE_AS_IMAGE_RESOURCE = 2;
    const RENDER_FILE_AS_LINK = 3;

    /**
     *
     * @var array
     */
    protected $savLibraryMvcColumns = [];

    /**
     *
     * @var array
     */
    protected $fieldsConfiguration = [];

    /**
     *
     * @var array
     */
    protected $storedFieldsConfiguration = [];

    /**
     *
     * @var array
     */
    protected $generalConfiguration;

    /**
     *
     * @var int
     */
    protected $uidMainTable;

    /**
     *
     * @var array
     */
    protected $fieldConfiguration = [];

    /**
     *
     * @var boolean
     */
    protected $cutFlag;

    /**
     *
     * @var boolean
     */
    protected $fusionInProgress = false;

    /**
     *
     * @var boolean
     */
    protected $fusionBeginPending = false;

    /**
     * View identifier
     *
     * @var int
     */
    protected $viewIdentifier;

    /**
     * Storage object
     *
     * @var DomainObjectInterface $object
     */
    protected $object = null;

    /**
     * Previous value for a field if attribute cutIfSameAsPrevious is used.
     *
     * @var array $previousValue
     */
    protected $previousValue;

    /**
     * Controller
     *
     * @var DefaultController $controller
     */
    protected $controller;

    /**
     * Uri builder
     *
     * @var UriBuilder $uriBuilder
     */
    protected $uriBuilder = null;

    /**
     * Default repository
     *
     * @var DefaultRepository $repository
     */
    protected $repository;

    /**
     * Flag for subforms
     *
     * @var bool
     */
    protected $subformFlag = false;

    /**
     * Subform property name
     *
     * @var string
     */
    protected $subformPropertyName = '';

    /**
     * Injects the objet storage
     *
     * @param ObjectStorage $object
     * @return void
     */
    public function injectObjectStorage(ObjectStorage $object)
    {
        $this->object = $object;
    }

    /**
     * Injects the uri builder
     *
     * @param UriBuilder UriBuilder
     * @return void
     */
    public function injectUriBuilder(UriBuilder $uriBuilder)
    {
        $this->uriBuilder = $uriBuilder;
    }

    /**
     * Sets the controller
     *
     * @param DefaultController $controller
     * @return void
     */
    public function setController(DefaultController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Gets the default repository.
     *
     * @return DefaultRepository
     */
    public function getDefaultRepository(): DefaultRepository
    {
        return $this->repository;
    }

    /**
     * Gets the domain object.
     *
     * @return DomainObjectInterface
     */
    public function getDomainObject(): DomainObjectInterface
    {
        return $this->object;
    }

    /**
     * Sets the domain object.
     *
     * @param DomainObjectInterface $object
     * @return void
     */
    public function setDomainObject(DomainObjectInterface $object)
    {
        $this->object = $object;
    }

    /**
     * Gets the controller.
     *
     * @return DefaultController
     */
    public function getController(): DefaultController
    {
        return $this->controller;
    }

    /**
     * Gets the view identifer.
     *
     * @return int
     */
    protected function getViewIdentifier(): int
    {
        return $this->viewIdentifier;
    }

    /**
     * Gets the fields configuration.
     *
     * @return array
     */
    public function getFieldsConfiguration(): array
    {
        return $this->fieldsConfiguration;
    }

    /**
     * Sets uidMainTable.
     *
     * @param int $uidMainTable
     * @return void
     */
    public function setUidMainTable(int $uidMainTable)
    {
        $this->$uidMainTable = $uidMainTable;
    }

    /**
     * Sets the subform flag.
     *
     * @param bool subformFlag
     * @return void
     */
    public function setSubformFlag(bool $subformFlag)
    {
        $this->subformFlag = $subformFlag;
    }

    /**
     * Gets the subform flag.
     *
     * @return bool
     */
    public function getSubformFlag(): bool
    {
        return $this->subformFlag;
    }

    /**
     * Sets the subform property name.
     *
     * @param string $subformPropertyName
     * @return void
     */
    public function setSubformPropertyName(string $subformPropertyName)
    {
        $this->subformPropertyName = $subformPropertyName;
    }

    /**
     * Gets the subform property name.
     *
     * @return string
     */
    public function getSubformPropertyName(): string
    {
        return $this->subformPropertyName;
    }

    /**
     * Gets the field configuration.
     *
     * @return array
     */
    public function getFieldConfiguration(string $fieldName = ''): ?array
    {
        if (empty($fieldName)) {
            return $this->fieldConfiguration;
        } else {
            return $this->fieldsConfiguration[$fieldName];
        }
    }

    /**
     * sets a field configuration.
     *
     * @param array $fieldConfiguration
     * @return void
     */
    public function setFieldConfiguration(array $fieldConfiguration)
    {
        $this->fieldConfiguration = $fieldConfiguration;
    }

    /**
     * Sets the general configuration.
     *
     * @param array $configuration
     * @return void
     */
    public function setGeneralConfiguration(array $configuration)
    {
        $this->generalConfiguration = $configuration;
    }

    /**
     * Stores the field configuration.
     *
     * @return void
     */
    public function storeFieldsConfiguration()
    {
        array_push($this->storedFieldsConfiguration,
            [
                $this->fieldsConfiguration,
                $this->savLibraryMvcColumns,
                $this->repository
            ]
        );
        $this->fieldsConfiguration = [];
    }

    /**
     * Restores the field configuration.
     *
     * @return void
     */
    public function restoreFieldsConfiguration()
    {
        $storedFieldsConfiguration = array_pop($this->storedFieldsConfiguration);
        $this->fieldsConfiguration = $storedFieldsConfiguration[0];
        $this->savLibraryMvcColumns = $storedFieldsConfiguration[1];
        $this->repository = $storedFieldsConfiguration[2];
    }

    /**
     * Sets the static configuration for all the fields selected in a view.
     *
     * @param int $viewIdentifier
     * @param DefaultRepository $repository
     * @return void
     */
    public function setStaticFieldsConfiguration(int $viewIdentifier, $repository)
    {
        $this->viewIdentifier = $viewIdentifier;
        $this->repository = $repository;

        // Gets the selected fields in the right order
        $temporaryArray = [];
        $this->savLibraryMvcColumns = $repository->getDataMapFactory()->getSavLibraryMvcColumns();
        foreach ($this->savLibraryMvcColumns as $fieldKey => $field) {
            if ($this->isSelected($fieldKey)) {
                $temporaryArray[$fieldKey] = $field['order'][$viewIdentifier];
            }
        }

        // Checks if there is at least one selected field
        if (empty($temporaryArray)) {
            throw new \Exception('No field selected in the view.');
        }
        asort($temporaryArray);

        // Builds the static fields configuration
        $this->fieldsConfiguration = [];
        foreach ($temporaryArray as $fieldName => $field) {
            // Merges the TCA and the configuration from the kickstarter
            $this->fieldConfiguration = array_merge($repository->getDataMapFactory()->getTCAFieldConfiguration($fieldName), $this->getSavLibraryMvcFieldConfigurationByView($fieldName));
            // Adds the label
            if (empty($this->fieldConfiguration['label'])) {
                $this->fieldConfiguration['label'] = $repository->getDataMapFactory()->getTCAFieldLabel($fieldName);
            }
            // Adds the field name
            $this->fieldConfiguration['fieldName'] = $fieldName;
            // Adds the field type
            $this->fieldConfiguration['fieldType'] = $repository->getDataMapFactory()->getFieldType($fieldName);
            // Adds the foreign model
            $this->fieldConfiguration['foreignModel'] = $repository->getDataMapFactory()->getForeignModel($fieldName);
            // Adds the folder
            $this->fieldConfiguration['folder'] = $this->getFolder($fieldName);
            // Checks if the field should be displayed
            $this->fieldConfiguration['display'] = ($this->fieldConfiguration['doNotDisplay'] ?? false ? 0 : 1);
            // Adds the required attribute
            if ($this->fieldConfiguration['requiredIf'] ?? false) {
                $this->fieldConfiguration['required'] = $this->processFieldCondition($this->fieldConfiguration['requiredIf'] ?? '');
            } else {
                $this->fieldConfiguration['required'] = $this->fieldConfiguration['required'] ?? false || preg_match('/required/', $this->fieldConfiguration['eval'] ?? '') > 0;
            }
            // Adds the default class label
            $this->fieldConfiguration['classLabel'] = $this->getClassLabel();

            // Adds the default class value
            $this->fieldConfiguration['classValue'] = $this->getClassValue();
            // Adds the default class Field
            $this->fieldConfiguration['classField'] = $this->getClassField();
            // Adds the default class Item
            $this->fieldConfiguration['classItem_'] = $this->getClassItem();
            // Adds the label cutter
            $this->fieldConfiguration['cutLabel'] = $this->getCutLabel();

            $this->fieldsConfiguration[$fieldName] = $this->fieldConfiguration;
        }
    }

    /**
     * Adds dynamic configuration to fields.
     *
     * @param ObjectStorage $object
     * @return void
     */
    public function addDynamicFieldsConfiguration($object)
    {
        $this->object = $object;

        // Gets the uidMainTable
        if ($this->repository == $this->controller->getMainRepository()) {
            $this->uidMainTable = $this->object->getUid();
        }

        foreach ($this->fieldsConfiguration as $fieldKey => $this->fieldConfiguration) {

            // Sets the uidMainTable
            $this->fieldConfiguration['uidMainTable'] = $this->uidMainTable;

            // Adds the value
            $this->fieldConfiguration['value'] = $this->getValue();

            // Processes the attribute cutIfSameAsPrevious
            $this->fieldConfiguration['classItem'] = $this->fieldConfiguration['classItem_'];
            if ($this->fieldConfiguration['cutIfSameAsPrevious'] ?? false) {
                if (! isset($this->previousValue[$fieldKey])) {
                    $this->previousValue[$fieldKey] = $this->fieldConfiguration['value'];
                } elseif ($this->previousValue[$fieldKey] == $this->fieldConfiguration['value']) {
                    $this->fieldConfiguration['value'] = '';
                    $this->fieldConfiguration['classItem'] = 'item';
                } else {
                    $this->previousValue[$fieldKey] = $this->fieldConfiguration['value'];
                }
            }

            // Adds the cutters (fusion and field)
            $this->setCutFlag();
            $this->fieldConfiguration['cutDivItemBegin'] = $this->getCutDivItemBegin();
            $this->fieldConfiguration['cutDivItemInner'] = $this->getCutDivItemInner();
            $this->fieldConfiguration['cutDivItemEnd'] = $this->getCutDivItemEnd();

            // Adds property for subforms
            $fieldName = $this->fieldConfiguration['fieldName'];
            $propertyName = GeneralUtility::underscoredToLowerCamelCase($fieldName);

            if ($this->getSubformFlag()) {
                $uid = $object->getUid();
                if ($uid == null) {
                    $uid = -1;
                }
                $this->fieldConfiguration['propertyName'] = $this->subformPropertyName . '.' . $uid . '.' . $propertyName;
                $this->fieldConfiguration['uidLocal'] = $uid;
            } else {
                $this->fieldConfiguration['propertyName'] = $propertyName;
            }

            // Adds specific configuration depending on the type
            $adderClassName = '\\YolfTypo3\\SavLibraryMvc\\Adders\\' . ucfirst($this->fieldConfiguration['fieldType']) . 'Adder';
            if (method_exists($adderClassName, 'render')) {
                $adder = new $adderClassName($this);
                $this->fieldConfiguration = array_merge(
                    $this->fieldConfiguration,
                    $adder->render()
                );
            }

            // Processes the value from a TypoScript object, if any
            $tsObject = $this->fieldConfiguration['tsObject'] ?? false;
            if ($tsObject) {
                $this->fieldConfiguration['value'] = $this->getValueFromTypoScriptObject();
            }

            // Adds wrapItem if required
            $wrapItemIfNotCut = $this->fieldConfiguration['wrapItemIfNotCut'] ?? false;
            $cutDivItemInner = $this->fieldConfiguration['cutDivItemInner'] ?? false;
            if ($wrapItemIfNotCut && ! $cutDivItemInner) {
                $this->fieldConfiguration['wrapItem'] = $this->fieldConfiguration['wrapItemIfNotCut'];
            }

            // Adds the field configuration to the fields configuration
            $this->fieldsConfiguration[$fieldKey] = $this->fieldConfiguration;
        }

        // Attribute-based post-processing
        foreach ($this->fieldsConfiguration as $fieldKey => $this->fieldConfiguration) {
            // Post-processes for the func attribute
            if (! empty($this->fieldConfiguration['func'] ?? null)) {
                $addAttributeBasedMethod = 'postProcessFieldConfigurationForFunc' . ucfirst($this->fieldConfiguration['func']);
                if (method_exists($this, $addAttributeBasedMethod)) {
                    $this->fieldsConfiguration[$fieldKey] = array_merge($this->fieldsConfiguration[$fieldKey], $this->$addAttributeBasedMethod($fieldKey));
                }
            }
        }
    }

    /**
     * Post-processor for the attribute func=makeItemLink.
     *
     * @param string $fieldName
     * @return array
     */
    protected function postProcessFieldConfigurationForFuncMakeItemLink(string $fieldName): array
    {
        $modifiedConfiguration = [];

        // Defines the action and the view
        $viewName = 'singleView';
        $action = 'single';
        $inputForm = $this->fieldConfiguration['inputForm'] ?? null;
        if ($inputForm == 1) {
            $viewName = 'editView';
            $action = 'edit';
        }

        // Adds parameters to the special argument
        $special = $this->generalConfiguration['special'];
        $uncompressedParameters = AbstractController::uncompressParameters($special);

        if (! empty($this->fieldConfiguration['folderTab'])) {
            // Gets the folders for the requested view
            $viewIdentifiers = $this->repository->getController()->getViewIdentifiers();
            $viewIdentifier = $viewIdentifiers[$viewName];
            $folders = $this->repository->getController()->getFolders($viewIdentifier);

            // Gets the folder identifier
            $folderIdentifier = 0;
            foreach ($folders as $folderKey => $folder) {
                if ($folder['label'] == $this->fieldConfiguration['folderTab']) {
                    $folderIdentifier = $folderKey;
                    break;
                }
            }
            $uncompressedParameters['folder'] = $folderIdentifier;
        }
        $compressedParameters = AbstractController::compressParameters($uncompressedParameters);

        // Defines the page uid
        $pageUid = (empty($this->fieldConfiguration['setUid']) ? $this->getPageId() : $this->fieldConfiguration['setUid']);

        // Builds the uri
        $pluginNameSpace = $this->controller->getPluginNameSpace();
        $uri = $this->uriBuilder->reset()
            ->setTargetPageUid($pageUid)
            ->setArguments([
            $pluginNameSpace . '[special]' => $compressedParameters,
            $pluginNameSpace . '[action]' => $action,
                $pluginNameSpace . '[controller]' => $this->controller->getControllerName()
            ])
            ->build();

        // Modifies the value with the link
        $modifiedConfiguration['value'] = '<a href="' . $uri . '">' . $this->fieldConfiguration['value'] . '</a>';

        return $modifiedConfiguration;
    }

    /**
     * Post-processor for the attribute func=makeEmailLink.
     *
     * @param string $fieldName
     * @return array
     */
    protected function postProcessFieldConfigurationForFuncMakeEmailLink(string $fieldName): array
    {
        $modifiedConfiguration = [];

        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        // Gets the message for the link
        $message = $this->fieldConfiguration['value'];
        if (! empty($this->fieldConfiguration['message'] ?? null)) {
            $message = $this->parseFieldTags($this->fieldConfiguration['message']);
        }

        if (! empty($this->fieldConfiguration['fieldMessage'] ?? null)) {
            $fieldMessage = $this->fieldConfiguration['fieldMessage'];
            $message = $this->parseFieldTags($this->fieldsConfiguration[$fieldMessage]['value']);
        }

        // Gets the mailTo information.
        $mailTo = $contentObjectRenderer->getMailTo($this->fieldConfiguration['value'], $message);

        // Modifies the value if the email is valid
        if (GeneralUtility::validEmail($this->fieldConfiguration['value'])) {
            $modifiedConfiguration['value'] = '<a href="' . $mailTo[0] . '">' . $mailTo[1] . '</a>';
        }

        return $modifiedConfiguration;
    }

    /**
     * Post-processor for the attribute func=makeUrlLink.
     *
     * @param string $fieldName
     * @return array
     */
    protected function postProcessFieldConfigurationForFuncMakeUrlLink(string $fieldName): array
    {
        $modifiedConfiguration = [];

        // Gets the message and processes it
        $message = $this->fieldConfiguration['value'];
        if (! empty($this->fieldConfiguration['message'] ?? null)) {
            $message = $this->parseFieldTags($this->fieldConfiguration['message']);
        }

        // Gets the parameter
        $link = $this->fieldConfiguration['value'];
        if (! empty($this->fieldConfiguration['link'] ?? null)) {
            $link = $this->fieldConfiguration['link'];
        }

        // Gets the target
        $target = '_blank';
        if (! empty($this->fieldConfiguration['exttarget'] ?? null)) {
            $target = $this->fieldConfiguration['exttarget'];
        }

        $typoScriptConfiguration = [
            'parameter' => $link,
            'extTarget' => $target
        ];

        // Gets the content object
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        $modifiedConfiguration['value'] = $contentObjectRenderer->typolink($message, $typoScriptConfiguration);

        return $modifiedConfiguration;
    }



    /**
     * Checks if a field is selected for the view.
     *
     * @param string $fieldName
     * @param bool $checkFolder
     * @return bool
     */
    public function isSelected(string $fieldName, bool $checkFolder = false): bool
    {
        $fieldConfiguration = $this->savLibraryMvcColumns[$fieldName]['config'];
        $viewIdentifier = $this->getViewIdentifier();
        $condition = is_array($fieldConfiguration[$viewIdentifier]) && $fieldConfiguration[$viewIdentifier]['selected'];

        if ($checkFolder && isset($this->savLibraryMvcColumns[$fieldName]['folders'])) {
            if (isset($this->savLibraryMvcColumns[$fieldName]['folders'][$viewIdentifier])) {
                $activeFolder = $this->controller->getViewerConfiguration()->getGeneralViewConfiguration('activeFolder');
                if ($this->savLibraryMvcColumns[$fieldName]['folders'][$viewIdentifier] == $activeFolder) {
                    return $condition;
                }
                $condition = false;
            }
        }

        return $condition;
    }

    /**
     * Checks if a file is an image
     *
     * @param string $fieldName
     * @return bool
     */
    protected function isImage(string $fileName): bool
    {
        // The attribute disallowed is empty for images
        $disallowed = $this->fieldConfiguration['disallowed'];
        if (! empty($disallowed)) {
            return false;
        }

        // Gets the allowed extensions for images
        if ($this->fieldConfiguration['allowed'] == 'gif,png,jpeg,jpg') {
            $allowedExtensionsForImages = explode(',', 'gif,png,jpeg,jpg');
        } else {
            $allowedExtensionsForImages = explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']);
        }

        // Gets the extension
        $pathParts = pathinfo($fileName);
        $extension = $pathParts['extension'];

        return in_array($extension, $allowedExtensionsForImages);
    }

    /**
     * Gets the SavLibraryMvc field configuration by view.
     *
     * @param string $fieldName
     * @return array
     */
    protected function getSavLibraryMvcFieldConfigurationByView(string $fieldName): array
    {
        $savLibraryMvcFieldConfiguration = $this->savLibraryMvcColumns[$fieldName]['config'][$this->getViewIdentifier()];
        if (is_array($savLibraryMvcFieldConfiguration)) {
            return $savLibraryMvcFieldConfiguration;
        } else {
            return [];
        }
    }

    /**
     * Gets a SavLibraryMvc field attribute by view.
     *
     * @param string $fieldName
     * @param $string $attributeName
     * @return mixed
     */
    protected function getSavLibraryMvcFieldAttributeByView(string $fieldName, string $attributeName)
    {
        $savLibraryMvcFieldAttribute = $this->savLibraryMvcColumns[$fieldName]['config'][$this->getViewIdentifier()][$attributeName] ?? null;

        return $savLibraryMvcFieldAttribute;
    }

    /**
     * Gets the folder for the view.
     *
     * @param string $fieldName
     * @return int
     */
    protected function getFolder(string $fieldName): int
    {
        $folder = (int) ($this->savLibraryMvcColumns[$fieldName]['folders'][$this->getViewIdentifier()] ?? 0);
        return $folder;
    }

    /**
     * Builds the value content.
     *
     * @return mixed
     */
    protected function getValue()
    {
        // Gets the value directly from the kickstarter (specific and rare case)
        $value = $this->getSavLibraryMvcFieldAttributeByView($this->fieldConfiguration['fieldName'], 'value');

        if (! empty($value)) {
            if (empty($this->fieldConfiguration['valueIf']) || (! empty($this->fieldConfiguration['valueif']) && $this->processFieldCondition($this->fieldConfiguration['valueIf']))) {
                // Parse localization and field tags
                $value = $this->parseLocalizationTags($value);
                $value = $this->parseFieldTags($value);
                return $value;
            } else {
                return null;
            }
        } elseif (! empty($this->fieldConfiguration['reqValue'])) {
            if (empty($this->fieldConfiguration['reqValueIf']) || (! empty($this->fieldConfiguration['reqValueIf']) && $this->processFieldCondition($this->fieldConfiguration['reqValueIf']))) {
                // ReqValue is possible only if the record is not a new one
                if (! is_null($this->object->getUid())) {
                    $value = $this->getValueFromRequest();
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            // If none of the above conditions is true, the value is obtained through one of the object getters
            if (! empty($this->fieldConfiguration['alias'])) {
                $fieldName = $this->fieldConfiguration['alias'];
            } else {
                $fieldName = $this->fieldConfiguration['fieldName'];
            }

            // Gets the value
            $result = $this->getValueFromFieldName($fieldName);
            if ($result['error'] === false) {
                $value = $result['value'];
            } else {
                return null;
            }

            // @TODO to be checked
            if ($value === null) {
                $value = '';
            }
        }

        return $value;
    }

    /**
     * Builds the value content.
     *
     * @return string
     */
    protected function getValueFromTypoScriptObject()
    {
        // Checks if the typoscript properties exist
        if (empty($this->fieldConfiguration['tsProperties'] ?? null)) {
            FlashMessages::addError('error.noAttributeInField', [
                'tsProperties',
                $this->fieldConfiguration['fieldName']
            ]);
            return '';
        }

        // The value is generated from TypoScript
        $configuration = $this->fieldConfiguration['tsProperties'];
        $configuration = $this->parseLocalizationTags($configuration);
        $configuration = $this->parseFieldTags($configuration);

        $TSparser = GeneralUtility::makeInstance(TypoScriptParser::class);
        $TSparser->parse($configuration);

        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $value = $contentObjectRenderer->cObjGetSingle($this->fieldConfiguration['tsObject'], $TSparser->setup);

        return $value;
    }

    /**
     * Builds the value content from a request.
     *
     * @return string
     * @throws \Exception
     */
    protected function getValueFromRequest()
    {
        // Gets the query
        $query = $this->fieldConfiguration['reqValue'];

        // Processes localization and field tags
        $query = $this->parseLocalizationTags($query);
        $query = $this->parseFieldTags($query);

        // Checks if the query is a select query and finds the first table in the FROM clause
        $match = [];
        if (preg_match('/^(?is:SELECT.*?FROM\s+(\w+))/', $query, $match) > 0) {
            $tableForConnection = $match[1];
        } else {
            throw new \Exception(sprintf(
                'Only SELECT query is allowed in property "reqLabel" of field "%s".',
                $this->fieldConfiguration['fieldName']
                )
            );
        }
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $rows = $connectionPool->getConnectionForTable($tableForConnection)
            ->executeQuery($query)
            ->fetchAll();
        if ($rows === null) {
            FlashMessages::addError('error.incorrectQueryInReqValue', [
                $this->fieldConfiguration['fieldName']
            ]);
        }

        // Sets the separator
        $separator = $this->fieldConfiguration['separator'];
        if (empty($separator)) {
            $separator = '<br />';
        }

        // Processes the rows
        $value = '';
        foreach ($rows as $row) {
            // Checks if the field value is in the row
            if (array_key_exists('value', $row)) {
                $valueFromRow = $row['value'];
                $value .= ($value ? $separator : '') . $valueFromRow;
            } else {
                FlashMessages::addError('error.aliasValueMissingInReqValue', [
                    $this->fieldConfiguration['fieldName']
                ]);
                return null;
            }
        }
        return $value;
    }

    /**
     * Builds the class for the label.
     *
     * @return string
     */
    protected function getClassLabel(): string
    {
        if (empty($this->fieldConfiguration['classLabel'])) {
            return 'label';
        } else {
            return 'label ' . $this->fieldConfiguration['classLabel'];
        }
    }

    /**
     * Builds the class for the value.
     *
     * @return string
     */
    protected function getClassValue(): string
    {
        if (empty($this->fieldConfiguration['classValue'])) {
            $class = 'value';
        } else {
            $class = 'value ' . $this->fieldConfiguration['classValue'];
        }

        return $class;
    }

    /**
     * Builds the class for the field.
     *
     * @return string
     */
    protected function getClassField(): string
    {
        // Adds subform if the type is a RelationManyToManyAsSubform
        if ($this->fieldConfiguration['fieldType'] == 'RelationManyToManyAsSubform') {
            $class = 'subform ';
        } else {
            $class = 'field ';
        }

        if (! empty($this->fieldConfiguration['classField'])) {
            $class = $class . $this->fieldConfiguration['classField'];
        }

        return $class;
    }

    /**
     * Builds the class for the item.
     *
     * @return string
     */
    protected function getClassItem(): string
    {
        if (empty($this->fieldConfiguration['classItem'])) {
            $class = 'item';
        } else {
            $class = 'item ' . $this->fieldConfiguration['classItem'];
        }

        return $class;
    }

    /**
     * <DIV class="label"> cutter: checks if the label must be cut
     * Returns true if the <DIV> must be cut.
     *
     * @return bool
     */
    protected function getCutLabel(): bool
    {
        // Cuts the label if the type is a RelationManyToManyAsSubform or cutLabel is not equal to zero
        if ($this->fieldConfiguration['fieldType'] == 'RelationManyToManyAsSubform') {
            $cut = true;
        } elseif ($this->fieldConfiguration['cutLabel'] ?? false) {
            $cut = true;
        } else {
            $cut = false;
        }

        return $cut;
    }

    /**
     * <DIV class="item"> cutter: checks if the beginning of the <DIV> must be cut
     * Returns true if the <DIV> must be cut.
     *
     * @return bool
     */
    protected function getCutDivItemBegin(): bool
    {
        $fusion = $this->fieldConfiguration['fusion'] ?? null;
        $fusionBegin = ($fusion == 'begin');

        if ($fusionBegin) {
            $this->fusionBeginPending = true;
        }

        $cut = (($this->fusionInProgress && ! $fusionBegin) || ($this->getCutFlag() && ! $this->fusionInProgress));

        if ($this->fusionBeginPending && ! $cut) {
            $this->fusionInProgress = true;
            $this->fusionBeginPending = false;
        }

        return $cut;
    }

    /**
     * <DIV class="item"> cutter: checks if the endt of the <DIV> must be cut
     * Returns true if the <DIV> must be cut.
     *
     * @return bool
     */
    protected function getCutDivItemEnd(): bool
    {
        $fusion = $this->fieldConfiguration['fusion'] ?? null;
        $fusionEnd = ($fusion == 'end');

        $cut = (($this->fusionInProgress && ! $fusionEnd) || ($this->getCutFlag() && ! $this->fusionInProgress));
        if ($fusionEnd) {
            $this->fusionInProgress = false;
            $this->fusionBeginPending = false;
        }
        return $cut;
    }

    /**
     * <DIV class="item"> cutter: checks if the inner content of the <DIV> must be cut
     * Returns true if the <DIV> must be cut.
     *
     * @return boolean
     */
    protected function getCutDivItemInner(): bool
    {
        $cut = ($this->getCutFlag());
        return $cut;
    }

    /**
     * Gets the cut flag.
     * If true the content must be cut.
     *
     * @return bool
     */
    protected function getCutFlag(): bool
    {
        return $this->cutFlag;
    }

    /**
     * Sets the cut flag
     *
     * @return void
     */
    protected function setCutFlag()
    {
        $this->cutFlag = $this->cut() | $this->cutIfEmpty() | $this->cutIf();
    }

    /**
     * Content cutter: simple cutter which is used in special cases
     * when the configuration must be fetched, i.e. for the title bar,
     * but the field should not be displayed in the view.
     * Returns true if the content must be cut.
     *
     * @return bool
     */
    protected function cut(): bool
    {
        $cut = $this->fieldConfiguration['cut'] ?? false;
        if ($cut) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Content cutter: checks if the content is empty
     * Returns true if the content mus t be cut.
     *
     * @return bool
     */
    protected function cutIfEmpty(): bool
    {
        $cutIfNull = $this->fieldConfiguration['cutIfNull'] ?? false;
        $cutIfEmpty = $this->fieldConfiguration['cutIfEmpty'] ?? false;
        if ($cutIfNull || $cutIfEmpty) {
            $value = $this->getValue();
            return empty($value);
        } else {
            return false;
        }
    }

    /**
     * Content cutter: checks if the content is empty
     * Returns true if the content must be cut.
     *
     * @return bool
     */
    public function cutIf(): bool
    {
        if ($this->fieldConfiguration['cutIf'] ?? false) {
            return $this->processFieldCondition($this->fieldConfiguration['cutIf']);
        } elseif ($this->fieldConfiguration['showIf'] ?? false) {
            return ! $this->processFieldCondition($this->fieldConfiguration['showIf']);
        } else {
            return false;
        }
    }

    /**
     * Processes a field condition
     *
     * @param string $fieldCondition
     *
     * @return bool True if the field condition is satisfied
     */
    public function processFieldCondition(string $fieldCondition)
    {
        $result = null;

        // Matches the pattern
        $matches = [];
        preg_match_all(self::CUT_IF_PATTERN, $fieldCondition, $matches);

        // Processes the expressions
        foreach ($matches['expression'] as $matchKey => $match) {
            // Processes the left hand side
            $lhs = $matches['lhs'][$matchKey];
            $isGroupCondition = false;

            switch ($lhs) {
                case 'group':
                    $isGroupCondition = true;
                    $result = $this->getValueFromFieldName('usergroup');
                    if ($result['error'] === false) {
                        $lhsValue = $result['value'];
                    } else {
                        return false;
                    }
                    break;
                case 'usergroup':
                    $isGroupCondition = true;
                    $lhsValue = $this->getTypoScriptFrontendController()->fe_user->user['usergroup'];
                    break;
                case '0':
                    $lhsValue = 0;
                    break;
                case '':
                    break;
                default:
                    // Gets the value
                    $result = $this->getValueFromFieldName($lhs);
                    if ($result['error']) {
                        return false;
                    } else {
                        $lhsValue = $result['value'];

                    }
            }

            // Processes the right hand side
            $rhs = $matches['rhs'][$matchKey];
            switch ($rhs) {
                case 'EMPTY':
                    $condition = empty($lhsValue);
                    break;
                case 'NEW':
                    $condition = ($this->getController()
                        ->getViewer()
                        ->isNewView() && $lhsValue === null);
                    break;
                case '###user###':
                    $rhsValue = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
                    break;
                case '###cruser###':
                    $viewer = $this->getController()->getViewer();
                    // Skips the condition if it is a new view since cruser_id will be set when saved
                    if (empty($viewer) === false && $viewer->isNewView() === true) {
                        continue 2;
                    } else {
                        $rhsValue = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
                    }
                    break;
                case '###time()###':
                case '###now()###':
                    $rhsValue = time();
                    break;
                case '':
                    // Processes directly the expression
                    switch ($matches['expression'][$matchKey]) {
                        case 'FALSE':
                        case 'false':
                            $condition = 0;
                            break;
                        case 'TRUE':
                        case 'true':
                            $condition = 1;
                            break;
                        default:
                            $condition = 1;
                    }
                    break;
                default:
                    if ($isGroupCondition !== true) {
                        $rhsValue = $rhs;
                    } else {
                        $row = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_groups')
                            ->select(
                                [
                                    'uid'
                                ],
                                'fe_groups',
                                [
                                    'title' => $rhs
                                ])
                            ->fetch();
                        $rhsValue = $row['uid'];
                    }
                    break;
            }

            // Processes the condition
            $operator = $matches['operator'][$matchKey];
            switch ($operator) {
                case '=':
                    if ($isGroupCondition !== true) {
                        $condition = ($lhsValue == $rhsValue);
                    } else {
                        $condition = (in_array($rhsValue, explode(',', $lhsValue)) === true);
                    }
                    break;
                case '!=':
                    if ($isGroupCondition !== true) {
                        $condition = ($lhsValue != $rhsValue);
                    } else {
                        $condition = (in_array($rhsValue, explode(',', $lhsValue)) === false);
                    }
                    break;
                case '>=':
                    if ($isGroupCondition !== true) {
                        $condition = $lhsValue >= $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', [
                            $operator
                        ]);
                    }
                    break;
                case '<=':
                    if ($isGroupCondition !== true) {
                        $condition = $lhsValue <= $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', [
                            $operator
                        ]);
                    }
                    break;
                case '>':
                    if ($isGroupCondition !== true) {
                        $condition = $lhsValue > $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', [
                            $operator
                        ]);
                    }
                    break;
                case '<':
                    if ($isGroupCondition !== true) {
                        $condition = $lhsValue < $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', [
                            $operator
                        ]);
                    }
                    break;
                case 'isnot':
                    $condition = ! $condition;
                    break;
            }

            // Processes the connector
            $connector = $matches['connector'][$matchKey];

            // Pushes the operator and the result in case of a left parenthesis
            if ($matches['lparenthesis'][$matchKey]) {
                array_push($this->cutterStack, [
                    'connector' => $connector,
                    'result' => $result
                ]);
                $result = null;
                $connector = '';
            }

            switch ($connector) {
                case '|':
                case 'or':
                case 'OR':
                    $result = ($result === null ? $condition : $result || $condition);
                    break;
                case '&':
                case 'and':
                case 'AND':
                    $result = ($result === null ? $condition : $result && $condition);
                    break;
                case '':
                    $result = $condition;
                    break;
            }

//             debug([
//             'lhs' => $lhs,
//             'lhsValue' => $lhsValue,
//             'operator' => $operator,
//             'rhs' => $rhs,
//             'rhsValue' => $rhsValue,
//             'connector' => $connector,
//             'result' => $result
//             ]);

            // Pops the operator and the result in case of a right parenthesis
            if ($matches['rparenthesis'][$matchKey]) {
                $stackValue = array_pop($this->cutterStack);
                switch ($stackValue['connector']) {
                    case '|':
                    case 'or':
                    case 'OR':
                        $result = $result || $stackValue['result'];
                        break;
                    case '&':
                    case 'and':
                    case 'AND':
                        $result = $result && $stackValue['result'];
                        break;
                    case '':
                        $result = $stackValue['result'];
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Processes localization tags
     *
     * @param $input string
     *            String to parse
     * @return string
     */
    public function parseLocalizationTags(string $input = null): string
    {
        if ($input === null) {
            return '';
        }

        // Processes labels associated with fields
        $matches = [];
        if (preg_match_all('/\$\$\$label\[([^\]]+)\]\$\$\$/', $input, $matches)) {

            foreach ($matches[1] as $matchKey => $match) {
                // Checks if the label is in language files, no default table is assumed
                // In that case the full name must be used, i.e. tableName.fieldName
                $label = LocalizationUtility::translate($match, AbstractController::getControllerExtensionKey());
                if (! empty($label)) {
                    $input = str_replace($matches[0][$matchKey], $label, $input);
                } else {
                    // Checks if the label is associated with the current repository
                    $label = LocalizationUtility::translate($this->repository->resolveModelClassName() . '.' . $match, AbstractController::getControllerExtensionKey());
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
            $label = LocalizationUtility::translate($match, $this->controller->getControllerExtensionKey());
            $input = str_replace($matches[0][$matchKey], $label, $input);
        }

        return $input;
    }

    /**
     * Parses ###field### tags.
     *
     * @param string $input
     *            The string to parse
     *
     * @return string
     */
    public function parseFieldTags(string $input): string
    {
        // Checks if the value must be parsed
        $matches = [];
        if (! preg_match_all('/###([^#]+)###/', $input, $matches)) {
            return $input;
        } else {
            foreach ($matches[1] as $matchKey => $match) {
                // Gets the value
                $result = $this->getValueFromFieldName($match);
                if ($result['error'] === false) {
                    $input = str_replace($matches[0][$matchKey], $result['value'], $input);
                }
            }
        }

        return $input;
    }

    /**
     * Gets the value from a field name
     *
     * @param string $fieldName
     * @return array
     */
    protected function getValueFromFieldName(string $fieldName)
    {
        if ($fieldName == 'uidMainTable') {
            return [
                'value' => $this->fieldConfiguration['uidMainTable'],
                'error' => false
            ];
        }

        $fieldNameParts = explode('.', $fieldName);
        if (count($fieldNameParts) == 1) {
            $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($fieldNameParts[0]);
        } elseif (count($fieldNameParts) == 2) {
            if ($fieldNameParts[0] != $this->resolveTableName(get_class($this->object))) {
                return [
                    'error' => ! FlashMessages::addError('error.unknownGetMethod', [
                        $fieldName
                    ])
                ];
            } else {
                $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($fieldNameParts[1]);
            }
        } else {
            return [
                'error' => ! FlashMessages::addError('error.unknownGetMethod', [
                    $fieldName
                ])
            ];
        }

        if (method_exists($this->object, $getterName)) {
            $value = $this->object->$getterName();
        } else {
            return [
                'error' => ! FlashMessages::addError('error.unknownGetMethod', [
                    $fieldName
                ])
            ];
        }

        return [
            'value' => $value,
            'error' => false
        ];
    }

    /**
     * Resolves the table name for the given class name
     *
     * @param string $className
     * @return string The table name
     */
    protected function resolveTableName(string $className): string
    {
        $className = ltrim($className, '\\');
        $classNameParts = explode('\\', $className);
        // Skip vendor and product name for core classes
        if (strpos($className, 'TYPO3\\CMS\\') === 0) {
            $classPartsToSkip = 2;
        } else {
            $classPartsToSkip = 1;
        }
        $tableName = 'tx_' . strtolower(implode('_', array_slice($classNameParts, $classPartsToSkip)));

        return $tableName;
    }

    /**
     * Check if a quey is a SELECT query
     *
     * @param string $query The query to check
     * @return bool
     */
    protected function isSelectQuery(string $query): bool
    {
        return preg_match('/^[ \r\t\n]*(?i)select\s*/', $query) ? true : false;
    }

    /**
     * Gets the page id
     *
     * @return int
     */
    protected function getPageId(): int
    {
        // @extensionScannerIgnoreLine
        return (int) $GLOBALS['TSFE']->id;
    }

    /**
     * Gets the TypoScript Frontend Controller
     *
     * @return TypoScriptFrontendController
     */
    protected  function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

}
