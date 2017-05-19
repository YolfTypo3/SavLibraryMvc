<?php
namespace SAV\SavLibraryMvc\Managers;

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
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use SAV\SavLibraryMvc\Controller\AbstractController;
use SAV\SavLibraryMvc\Controller\FlashMessages;

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
        (?:
        	FALSE | TRUE |
	        (?:\#{3})?
		        (?P<lhs>(?:(?:\w+\.)+)?\w+)
		        \s*(?P<operator>=|!=|>=|<=|>|<)\s*
		        (?P<rhs>[-\w]+|\#{3}[^\#]+\#{3})
	        (?:\#{3})?
				)
      )
    )
  /x';

    /**
     * @var array
     */
    protected $savLibraryMvcColumns = array();

    /**
     * @var array
     */
    protected $fieldsConfiguration = array();

    /**
     * @var array
     */
    protected $fieldConfiguration = array();

    /**
     * @var boolean
     */
    protected $cutFlag;

    /**
     * @var integer
     */
    protected $viewIdentifier;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     */
    protected $object = NULL;

    /**
     * @var \SAV\SavLibraryMvc\Domain\Repository\DefaultRepository $repository
     */
    protected $repository;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     *
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Gets the view identifer.
     *
     * @return integer
     */
    protected function getViewIdentifier()
    {
        return $this->viewIdentifier;
    }

    /**
     * Gets the fields configuration.
     *
     * @return array
     */
    public function getFieldsConfiguration()
    {
        return $this->fieldsConfiguration;
    }

    /**
     * sets a field configuration.
     *
     * @param array $fieldConfiguration
     *
     * @return void
     */
    public function setFieldConfiguration($fieldConfiguration)
    {
        $this->fieldConfiguration = $fieldConfiguration;
    }

    /**
     * Sets the static configuration for all the fields selected in a view.
     *
     * @param integer $viewIdentifier
     * @param \SAV\SavLibraryMvc\Domain\Repository\DefaultRepository $repository
     * @return void
     */
    public function setStaticFieldsConfiguration($viewIdentifier, $repository)
    {
        $this->viewIdentifier = $viewIdentifier;
        $this->repository = $repository;

        // Gets the selected fields in the right order
        $temporaryArray = array();
        $this->savLibraryMvcColumns = $repository->getDataMapFactory()->getSavLibraryMvcColumns();
        foreach ($this->savLibraryMvcColumns as $fieldKey => $field) {
            if ($this->isSelected($fieldKey)) {
                $temporaryArray[$fieldKey] = $field['order'][$this->getViewIdentifier()];
            }
        }
        asort($temporaryArray);

        // Builds the static fields configuration
        $this->fieldsConfiguration = array();
        foreach ($temporaryArray as $fieldName => $field) {

            // Merges the TCA and the configuration frim the kickstarter
            $this->fieldConfiguration = array_merge($repository->getDataMapFactory()->getTCAFieldConfiguration($fieldName), $this->getSavLibraryMvcFieldConfigurationByView($fieldName));

            // Adds the label
            if (empty($this->fieldConfiguration['label'])) {
                $this->fieldConfiguration['label'] = $repository->getDataMapFactory()->getTCAFieldLabel($fieldName);
            }
            // Adds the field name
            $this->fieldConfiguration['fieldName'] = $fieldName;
            // Adds the field type
            $this->fieldConfiguration['fieldType'] = $repository->getDataMapFactory()->getFieldType($fieldName);
            // Adds the folder
            $this->fieldConfiguration['folder'] = $this->getFolder($fieldName);
            // Checks if the field should be displayed
            $this->fieldConfiguration['display'] = ($this->fieldConfiguration['doNotDisplay'] ? 0 : 1);
            // Adds the required attribute
            $this->fieldConfiguration['required'] = $this->fieldConfiguration['required'] || preg_match('/required/', $this->fieldConfiguration['eval']) > 0;
            // Adds the default class label
            $this->fieldConfiguration['classLabel'] = $this->getClassLabel();
            // Adds the default class value
            $this->fieldConfiguration['classValue'] = $this->getClassValue();
            // Adds the default class Field
            $this->fieldConfiguration['classField'] = $this->getClassField();
            // Adds the default class Item
            $this->fieldConfiguration['classItem'] = $this->getClassItem();
            // Adds the label cutter
            $this->fieldConfiguration['cutLabel'] = $this->getCutLabel();

            $this->fieldsConfiguration[$fieldName] = $this->fieldConfiguration;
        }
    }

    /**
     * Adds dynamic configuration to fields.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $object
     * @return void
     */
    public function addDynamicFieldsConfiguration($object)
    {
        $this->object = $object;

        foreach ($this->fieldsConfiguration as $fieldKey => $this->fieldConfiguration) {
            // Adds the value
            $this->fieldConfiguration['value'] = $this->getValue();

            // Adds the cutters (fusion and field)
            $this->setCutFlag();
            $this->fieldConfiguration['cutDivItemBegin'] = $this->getCutDivItemBegin();
            $this->fieldConfiguration['cutDivItemInner'] = $this->getCutDivItemInner();
            $this->fieldConfiguration['cutDivItemEnd'] = $this->getCutDivItemEnd();

            // Adds the property name
            $this->fieldConfiguration['propertyName'] = GeneralUtility::underscoredToLowerCamelCase($this->fieldConfiguration['fieldName']);

            // Adds specific configuration depending on the type
            $addTypeBasedMethod = 'addFieldsConfigurationFor' . ucfirst($this->fieldConfiguration['fieldType']);
            if (method_exists($this, $addTypeBasedMethod)) {
                $this->$addTypeBasedMethod($fieldName);
            }

            // Adds the field configuration to the fields configuration
            $this->fieldsConfiguration[$fieldKey] = $this->fieldConfiguration;
        }

        // Post processing
        foreach ($this->fieldsConfiguration as $fieldKey => $this->fieldConfiguration) {
            // Adds specific configuration depending on the type
            $addTypeBasedMethod = 'addFieldConfigurationFor' . ucfirst($this->fieldConfiguration['fieldType']);
            if (method_exists($this, $addTypeBasedMethod)) {
                $this->fieldsConfiguration[$fieldKey] = array_merge($this->fieldsConfiguration[$fieldKey], $this->$addTypeBasedMethod($fieldKey));
            }
        }

    }

    /**
     * Adds the static configuration for the type Files.
     *
     * @param string $fieldName
     * @return void
     */
    protected function addFieldConfigurationForFiles($fieldName)
    {
        $addedFieldConfiguration = array();

        if ($this->fieldConfiguration['value'] instanceof ObjectStorage) {
            $files = array();
            foreach ($this->fieldConfiguration['value'] as $object) {
                $fileConfiguration = array();

                $originalResource = $object->getOriginalResource();
                $fileConfiguration['fileName'] = $originalResource->getPublicUrl();
                $fileConfiguration['shortFileName'] = $originalResource->getName();

                // Checks if the file exists
                if (! is_file(PATH_site . $fileConfiguration['fileName'])) {
                    $fileConfiguration['fileUnknown'] = 1;
                    FlashMessages::addError('error.fileDoesNotExist', array(
                        $fileConfiguration['fileName']
                    ));
                }
                $type = $originalResource->getType();

                switch ($type) {
                    case AbstractFile::FILETYPE_IMAGE:
                        $fileConfiguration['value'] = $originalResource;
                        $fileConfiguration['isImage'] = 1;
                        break;
                    case AbstractFile::FILETYPE_TEXT:
                    case AbstractFile::FILETYPE_AUDIO:
                    case AbstractFile::FILETYPE_VIDEO:
                    case AbstractFile::FILETYPE_APPLICATION:

                        // Gets the value
                        $fileConfiguration['value'] = $originalResource->getPublicUrl();
                        ;

                        // Gets the message attribute
                        $fieldMessage = $this->fieldConfiguration['fieldMessage'];
                        if ($fieldMessage) {
                            $fileConfiguration['message'] = $this->fieldsConfiguration[$fieldMessage]['value'];
                        }
                        if (empty($this->fieldConfiguration['message']) && empty($fieldMessage)) {
                            $fileConfiguration['message'] = $originalResource->getName();
                        }

                        // Processes the addIcon attribute
                        if ($this->fieldConfiguration['addIcon']) {
                            $iconFactory = $this->objectManager->get(IconFactory::class);
                            $pathParts = pathinfo($originalResource->getName());
                            $fileConfiguration['icon'] = $iconFactory->getIconForFileExtension($pathParts['extension'], \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->getMarkup();
                        }
                        break;
                }

                // Adds file information
                $files[] = $fileConfiguration;
            }
        }
        // Adds the files information
        $addedFieldConfiguration['files'] = $files;

        // Adds the alt attribute
        $fieldAlt = $this->fieldConfiguration['fieldAlt'];
        if ($fieldAlt) {
            $addedFieldConfiguration['alt'] = $this->fieldsConfiguration[$fieldAlt]['value'];
        }
        if (empty($this->fieldConfiguration['alt']) && empty($fieldAlt)) {
            $addedFieldConfiguration['alt'] = $this->fieldConfigurationc;
        }

        return $addedFieldConfiguration;
    }

    /**
     * Adds the static configuration for the type Link.
     *
     * @param string $fieldName
     * @return void
     */
    protected function addFieldConfigurationForLink($fieldName)
    {
        $addedFieldConfiguration = array();
        // message attribute
        $fieldMessage = $this->fieldConfiguration['fieldMessage'];
        if ($fieldMessage) {
            $addedFieldConfiguration['message'] = $this->fieldsConfiguration[$fieldMessage]['value'];
        }
        if (empty($this->fieldConfiguration['message']) && empty($fieldMessage)) {
            $addedFieldConfiguration['message'] = $this->fieldConfiguration['value'];
        }
        // alt attribute
        $fieldLink = $this->fieldConfiguration['fieldLink'];
        if ($fieldLink) {
            $addedFieldConfiguration['link'] = $this->fieldsConfiguration[$fieldLink]['value'];
        }
        if (empty($this->fieldConfiguration['link']) && empty($fieldLink)) {
            $addedFieldConfiguration['link'] = $this->fieldConfiguration['value'];
        }

        return $addedFieldConfiguration;
    }

    /**
     * Adds the static configuration for the type Radiobuttons.
     *
     * @param string $fieldName
     * @return void
     */
    protected function addFieldConfigurationForRadiobuttons($fieldName)
    {
        $addedFieldConfiguration = array();
        if ($this->fieldConfiguration['horizontalLayout']) {
            $this->fieldConfiguration['cols'] = count($this->fieldConfiguration['items']);
        }
        return $addedFieldConfiguration;
    }

    /**
     * Adds the static configuration for the type RelationManyToManyAsDoubleSelectorbox.
     *
     * @param string $fieldName
     * @return void
     */
    protected function addFieldConfigurationForRelationManyToManyAsSubform($fieldName)
    {
        $addedFieldConfiguration = array();
        // Gets the controller
        $controller = $this->repository->getController();

        // Sets the flag to show first and last buttons
        $addedFieldConfiguration['general']['showFirstLastButtons'] = $this->fieldConfiguration['noFirstLast'] ? 0 : 1;
        unset($this->fieldsConfiguration[$fieldName]['noFirstLast']);

        // Computes the last page id in a subform
        $maximumItemsInSubform = $this->fieldConfiguration['maxSubformItems'];
        $lastPageInSubform = (empty($maximumItemsInSubform) ? 0 : floor(($this->fieldConfiguration['value']->count() - 1) / $maximumItemsInSubform));
        $addedFieldConfiguration['general']['lastPageInSubform'] = $lastPageInSubform;

        // Page information for the page browser
        $maxPagesInSubform = AbstractController::getSetting('maxItems');

        // Get the page for the subform
        $arguments = AbstractController::getOriginalArguments();
        $uncompressedParameters = AbstractController::uncompressParameters($arguments['special']);
        $subformActivePages = $uncompressedParameters['subformActivePages'];
        $uncompressedSubformActivePages = AbstractController::uncompressSubformActivePages($subformActivePages);
        $subformKey = $this->fieldConfiguration['subformKey'];
        $pageInSubform = (int) $uncompressedSubformActivePages[$subformKey];
        $addedFieldConfiguration['general']['pageInSubform'] = $pageInSubform;

        $pagesInSubform = array();
        for ($i = min($pageInSubform, max(0, $lastPageInSubform - $maxPagesInSubform)); $i <= min($lastPageInSubform, $pageInSubform + $maxPagesInSubform); $i ++) {
            $pagesInSubform[$i] = $i + 1;
        }
        $addedFieldConfiguration['general']['pagesInSubform'] = $pagesInSubform;
        $addedFieldConfiguration['general']['subformUidLocal'] = $this->object->getUid();
        return $addedFieldConfiguration;
    }

    /**
     * Checks if a field is selected for the view.
     *
     * @param string $fieldName
     * @return array
     */
    protected function isSelected($fieldName)
    {
        $fieldConfiguration = $this->savLibraryMvcColumns[$fieldName]['config'];
        $condition = is_array($fieldConfiguration[$this->getViewIdentifier()]) && $fieldConfiguration[$this->getViewIdentifier()]['selected'];
        return $condition;
    }

    /**
     * Checks if a file is an image
     *
     * @param string $fieldName
     * @return boolean
     */
    protected function isImage($fileName)
    {
        // The attribute disallowed is empty for images
        $disallowed = $this->fieldConfiguration['disallowed'];
        if (! empty($disallowed)) {
            return FALSE;
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
    protected function getSavLibraryMvcFieldConfigurationByView($fieldName)
    {
        $savLibraryMvcFieldConfiguration = $this->savLibraryMvcColumns[$fieldName]['config'][$this->getViewIdentifier()];
        if (is_array($savLibraryMvcFieldConfiguration)) {
            return $savLibraryMvcFieldConfiguration;
        } else {
            return array();
        }
    }

    /**
     * Gets a SavLibraryMvc field attribute by view.
     *
     * @param string $fieldName
     * @param $string $attributeName
     * @return array
     */
    protected function getSavLibraryMvcFieldAttributeByView($fieldName, $attributeName)
    {
        $savLibraryMvcFieldAttribute = $this->savLibraryMvcColumns[$fieldName]['config'][$this->getViewIdentifier()][$attributeName];
        return $savLibraryMvcFieldAttribute;
    }

    /**
     * Gets the folder for the view.
     *
     * @param string $fieldName
     * @param integer $viewIdentifier
     * @return integer
     */
    protected function getFolder($fieldName)
    {
        $folder = $this->savLibraryMvcColumns[$fieldName]['folders'][$this->getViewIdentifier()];
        return ($folder ? $folder : 0);
    }

    /**
     * Builds the value content.
     *
     * @return string
     */
    protected function getValue()
    {
        // Gets the value directly from the kickstarter (specific and rare case)
        $value = $this->getSavLibraryMvcFieldAttributeByView($this->fieldConfiguration['fieldName'], 'value');
        if (! empty($value)) {
            // Parse localization tags
            $value = $this->parseLocalizationTags($value);
            return $value;

            // TODO Parse field tags
            // $value = $querier->parseFieldTags($value);

        } elseif (! empty($this->fieldConfiguration['reqValue'])) {
            $value = $this->getValueFromRequest();
        } else {
            // If none of the above conditions is true, the value is obtained through one of the object getters
            if (! empty($this->fieldConfiguration['alias'])) {
                $fieldName = $this->fieldConfiguration['alias'];
            } else {
                $fieldName = $this->fieldConfiguration['fieldName'];
            }

            $getterName = 'get' . GeneralUtility::underscoredToUpperCamelCase($fieldName);
            $value = $this->object->$getterName();
            // TODO Modify with default value
            if ($value === NULL) {
                $value = '';
            }
        }

        return $value;
    }

    /**
     * Builds the value content.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function getValueFromTypoScriptStdwrap($value)
    {

        // The value is wrapped using the stdWrap TypoScript
        // TODO Parse localization and field tags
        // $configuration = $querier->parseLocalizationTags($this->kickstarterFieldConfiguration['stdwrapvalue']);
        // $configuration = $querier->parseFieldTags($configuration);
        $configuration = $this->fieldConfiguration['stdwrapvalue'];

        $TSparser = $this->getGeneralManager()
            ->getObjectManager()
            ->get(TypoScriptParser::class);
        $TSparser->parse($configuration);
        $contentObject = $this->getGeneralManager()
            ->getController()
            ->getConfigurationManager()
            ->getContentObject();
        $value = $contentObject->stdWrap($value, $TSparser->setup);

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
        if (empty($this->fieldConfiguration['tsproperties'])) {
            FlashMessages::addError('error.noAttributeInField', array(
                'tsProperties',
                $this->fieldConfiguration['fieldName']
            ));
            return '';
        }

        // The value is generated from TypoScript
        // TODO Parse localization and field tags
        // $configuration = $querier->parseLocalizationTags($this->kickstarterFieldConfiguration['tsproperties']);
        // $configuration = $querier->parseFieldTags($configuration);

        $configuration = $this->fieldConfiguration['tsproperties'];

        $TSparser = $this->getGeneralManager()
            ->getObjectManager()
            ->get(TypoScriptParser::class);
        $TSparser->parse($configuration);

        $contentObject = $this->getGeneralManager()
            ->getController()
            ->getConfigurationManager()
            ->getContentObject();
        $value = $contentObject->cObjGetSingle($this->fieldConfiguration['tsobject'], $TSparser->setup);

        return $value;
    }

    /**
     * Builds the value content from a request.
     *
     * @return string
     */
    protected function getValueFromRequest()
    {
        // TODO to be checked
        // Gets the querier
        $querier = $this->getQuerier();

        // Gets the query
        $query = $this->kickstarterFieldConfiguration['reqvalue'];

        // Processes localization tags
        $query = $querier->parseLocalizationTags($query);
        $query = $querier->parseFieldTags($query);

        // Checks if the query is a select query
        if (! $querier->isSelectQuery($query)) {
            FlashMessages::addError('error.onlySelectQueryAllowed', array(
                $this->kickstarterFieldConfiguration['fieldName']
            ));
            return '';
        }
        // Executes the query
        $resource = $GLOBALS['TYPO3_DB']->sql_query($query);
        if ($resource === FALSE) {
            FlashMessages::addError('error.incorrectQueryInReqValue', array(
                $this->kickstarterFieldConfiguration['fieldName']
            ));
        }

        // Sets the separator
        $separator = $this->kickstarterFieldConfiguration['separator'];
        if (empty($separator)) {
            $separator = '<br />';
        }

        // Creates an item viewer for the processing of the func attribute
        // $itemViewer = GeneralUtility::makeInstance('SAV\\SavLibraryPlus\\ItemViewers\\General\\StringItemViewer');
        // $itemViewer->injectController($this->getController());
        // $itemViewer->injectItemConfiguration($this->kickstarterFieldConfiguration);

        // Processes the rows
        $value = '';
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resource)) {

            // Checks if the field value is in the row
            if (array_key_exists('value', $row)) {
                $valueFromRow = $row['value'];
                unset($row['value']);
                $itemViewer->injectItemConfigurationAttribute($row);
                // Injects each field as additional markers
                foreach ($row as $fieldKey => $field) {
                    $querier->injectAdditionalMarkers(array(
                        '###' . $fieldKey . '###' => $field
                    ));
                }
                $valueFromRow = $itemViewer->processFuncAttribute($valueFromRow);

                $value .= ($value ? $separator : '') . $valueFromRow;
            } else {
                FlashMessages::addError('error.aliasValueMissingInReqValue', array(
                    $this->kickstarterFieldConfiguration['fieldName']
                ));
                return '';
            }
        }
        return $value;
    }

    /**
     * Builds the class for the label.
     *
     * @return string
     */
    protected function getClassLabel()
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
    protected function getClassValue()
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
    protected function getClassField()
    {
        // Adds subform if the type is a RelationManyToManyAsSubform
        if ($this->fieldConfiguration['fieldType'] == 'RelationManyToManyAsSubform') {
            $class = 'subform ';
        } else {
            $class = 'field ';
        }

        if (! empty($this->fieldConfiguration['classfield'])) {
            $class = $class . $this->fieldConfiguration['classfield'];
        }

        return $class;
    }

    /**
     * Builds the class for the item.
     *
     * @return string
     */
    protected function getClassItem()
    {
        if (empty($this->fieldConfiguration['classItem'])) {
            $class = 'item';
        } else {
            $class = 'item ' . $this->fieldConfiguration['classItem'];
        }

        return $class;
    }

    /**
     * Builds the error flag if any during the update.
     *
     * @return boolean
     */
    protected function getErrorFlag()
    {
        // TODO to be checked
        $querier = $this->getQuerier();
        if (empty($querier)) {
            return FALSE;
        } elseif ($querier->errorDuringUpdate() === TRUE) {
            $fieldName = $this->getFullFieldName();
            $errorCode = $querier->getFieldErrorCodeFromProcessedPostVariables($fieldName);
            return $errorCode != \SAV\SavLibraryPlus\Queriers\UpdateQuerier::ERROR_NONE;
        } else {
            return FALSE;
        }
    }

    /**
     * <DIV class="label"> cutter: checks if the label must be cut
     * Returns TRUE if the <DIV> must be cut.
     *
     * @return boolean
     */
    protected function getCutLabel()
    {
        // Cuts the label if the type is a RelationManyToManyAsSubform or cutLabel is not equal to zero
        if ($this->fieldConfiguration['fieldType'] == 'RelationManyToManyAsSubform') {
            $cut = TRUE;
        } elseif ($this->fieldConfiguration['cutLabel']) {
            $cut = TRUE;
        } else {
            $cut = FALSE;
        }

        return $cut;
    }

    /**
     * <DIV class="item"> cutter: checks if the beginning of the <DIV> must be cut
     * Returns TRUE if the <DIV> must be cut.
     *
     * @return boolean
     */
    protected function getCutDivItemBegin()
    {
        $fusionBegin = ($this->fieldConfiguration['fusion'] == 'begin');

        if ($fusionBegin) {
            $this->fusionBeginPending = TRUE;
        }

        $cut = (($this->fusionInProgress && ! $fusionBegin) || ($this->getCutFlag() && ! $this->fusionInProgress));

        if ($this->fusionBeginPending && ! $cut) {
            $this->fusionInProgress = TRUE;
            $this->fusionBeginPending = FALSE;
        }

        return $cut;
    }

    /**
     * <DIV class="item"> cutter: checks if the endt of the <DIV> must be cut
     * Returns TRUE if the <DIV> must be cut.
     *
     * @return boolean
     */
    protected function getCutDivItemEnd()
    {
        $fusionEnd = ($this->fieldConfiguration['fusion'] == 'end');

        $cut = (($this->fusionInProgress && ! $fusionEnd) || ($this->getCutFlag() && ! $this->fusionInProgress));
        if ($fusionEnd) {
            $this->fusionInProgress = FALSE;
            $this->fusionBeginPending = FALSE;
        }
        return $cut;
    }

    /**
     * <DIV class="item"> cutter: checks if the inner content of the <DIV> must be cut
     * Returns TRUE if the <DIV> must be cut.
     *
     * @return boolean
     */
    protected function getCutDivItemInner()
    {
        $cut = ($this->getCutFlag());
        return $cut;
    }

    /**
     * Gets the cut flag.
     * If TRUE the content must be cut.
     *
     * @return boolean
     */
    protected function getCutFlag()
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
        $this->cutFlag = $this->cutIfEmpty() | $this->cutIf();
    }

    /**
     * Content cutter: checks if the content is empty
     * Returns TRUE if the content must be cut.
     *
     * @return boolean
     */
    protected function cutIfEmpty()
    {
        if ($this->fieldConfiguration['cutIfNull'] || $this->fieldConfiguration['cutIfEmpty']) {
            $value = $this->getValue();
            return empty($value);
        } else {
            return FALSE;
        }
    }

    /**
     * Content cutter: checks if the content is empty
     * Returns TRUE if the content must be cut.
     *
     * @return boolean
     */
    public function cutIf()
    {
        if ($this->fieldConfiguration['cutIf']) {
            return $this->processFieldCondition($this->fieldConfiguration['cutIf']);
        } elseif ($this->fieldConfiguration['showIf']) {
            return ! $this->processFieldCondition($this->fieldConfiguration['showIf']);
        } else {
            return FALSE;
        }
    }

    /**
     * Processes a field condition
     *
     * @param string $fieldCondition
     *
     * @return boolean True if the field condition is satisfied
     */
    public function processFieldCondition($fieldCondition)
    {
        // TODO Must be checked
        // Initializes the result
        $result = NULL;

        // Matchs the pattern
        preg_match_all(self::CUT_IF_PATTERN, $fieldCondition, $matches);

        // Processes the expressions
        foreach ($matches['expression'] as $matchKey => $match) {
            // Processes the left hand side
            $lhs = $matches['lhs'][$matchKey];

            switch ($lhs) {
                case 'group':
                    $isGroupCondition = TRUE;
                    if (empty($querier) === FALSE && $querier->rowsNotEmpty()) {
                        $fullFieldName = $querier->buildFullFieldName('usergroup');
                        if ($querier->fieldExistsInCurrentRow($fullFieldName) === TRUE) {
                            $lhsValue = $querier->getFieldValueFromCurrentRow($fullFieldName);
                        } else {
                            return FlashMessages::addError('error.unknownFieldName', array(
                                $fullFieldName
                            ));
                        }
                    } else {
                        return FALSE;
                    }
                    break;
                case 'usergroup':
                    $isGroupCondition = TRUE;
                    $lhsValue = $GLOBALS['TSFE']->fe_user->user['usergroup'];
                    break;
                case '':
                    break;
                default:
                    // Gets the value
                    if ($this->object !== NULL) {
                        $lhsValue = $this->object->getFieldValueFromFieldName($lhs);
                    } else {
                        return FALSE;
                    }
            }

            // Processes the right hand side
            $rhs = $matches['rhs'][$matchKey];
            switch ($rhs) {
                case 'EMPTY':
                    $condition = empty($lhsValue);
                    break;
                case '###user###':
                    $condition = ($lhsValue == $GLOBALS['TSFE']->fe_user->user['uid']);
                    break;
                case '###cruser###':
                    $viewer = $this->getController()->getViewer();
                    // Skips the condition if it is a new view since cruser_id will be set when saved
                    if (empty($viewer) === FALSE && $viewer->isNewView() === TRUE) {
                        continue;
                    } else {
                        $condition = ($lhsValue == $GLOBALS['TSFE']->fe_user->user['uid']);
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
                    if ($isGroupCondition !== TRUE) {
                        $rhsValue = $rhs;
                    } else {
                        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
                    /* SELECT */	'uid',
        			/* FROM   */	'fe_groups',
        	 		/* WHERE  */	'title="' . $rhs . '"');
                        $rhsValue = $rows[0]['uid'];
                    }
                    break;
            }

            // Processes the condition
            $operator = $matches['operator'][$matchKey];
            switch ($operator) {
                case '=':
                    if ($isGroupCondition !== TRUE) {
                        $condition = ($lhsValue == $rhsValue);
                    } else {
                        $condition = (in_array($rhsValue, explode(',', $lhsValue)) === TRUE);
                    }
                    break;
                case '!=':
                    if ($isGroupCondition !== TRUE) {
                        $condition = ($lhsValue != $rhsValue);
                    } else {
                        $condition = (in_array($rhsValue, explode(',', $lhsValue)) === FALSE);
                    }
                    break;
                case '>=':
                    if ($isGroupCondition !== TRUE) {
                        $condition = $lhsValue >= $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', array(
                            $operator
                        ));
                    }
                    break;
                case '<=':
                    if ($isGroupCondition !== TRUE) {
                        $condition = $lhsValue <= $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', array(
                            $operator
                        ));
                    }
                    break;
                case '>':
                    if ($isGroupCondition !== TRUE) {
                        $condition = $lhsValue > $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', array(
                            $operator
                        ));
                    }
                    break;
                case '<':
                    if ($isGroupCondition !== TRUE) {
                        $condition = $lhsValue < $rhsValue;
                    } else {
                        return FlashMessages::addError('error.operatorNotAllowed', array(
                            $operator
                        ));
                    }
                    break;
            }

            // Processes the connector
            $connector = $matches['connector'][$matchKey];
            switch ($connector) {
                case '|':
                case 'or':
                case 'OR':
                    $result = ($result === NULL ? $condition : $result || $condition);
                    break;
                case '&':
                case 'and':
                case 'AND':
                    $result = ($result === NULL ? $condition : $result && $condition);
                    break;
                case '':
                    $result = $condition;
                    break;
            }
        }

        return $result;
    }

    /**
     * Processes localization tags
     *
     * @param $input string
     *            String to process
     * @return string
     */
    public function parseLocalizationTags($input)
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
            $label = LocalizationUtility::translate($match, AbstractController::getControllerExtensionKey());
            $input = str_replace($matches[0][$matchKey], $label, $input);
        }

        return $input;
    }
}
