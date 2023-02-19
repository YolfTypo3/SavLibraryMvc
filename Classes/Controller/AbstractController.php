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

namespace YolfTypo3\SavLibraryMvc\Controller;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3Fluid\Fluid\View\ViewInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use YolfTypo3\SavLibraryMvc\Domain\Repository\DefaultRepository;
use YolfTypo3\SavLibraryMvc\Domain\Repository\ExportRepository;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;
use YolfTypo3\SavLibraryMvc\Managers\FieldConfigurationManager;
use YolfTypo3\SavLibraryMvc\Managers\FrontendUserManager;
use YolfTypo3\SavLibraryMvc\ViewConfiguration\AbstractViewConfiguration;

/**
 * Abstract controller for the SAV Library MVC
 *
 * @package SavLibraryMvc
 */
abstract class AbstractController extends ActionController
{

    // Constants
    const LIBRARY_NAME = 'sav_library_mvc';

    // Constants for the mode
    const DEFAULT_MODE = 0;
    const EDIT_MODE = 1;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * Css root path
     *
     * @var string
     */
    public static $cssRootPath = 'Resources/Public/Css';

    /**
     * JavaScript root path
     *
     * @var string
     */
    public static $javaScriptRootPath = 'Resources/Public/JavaScript';

    /**
     * Allowed icon file name extensions
     *
     * @var string
     */
    protected static $allowedIconFileNameExtensions = '.gif,.png,.jpg,.jpeg,.svg';

    // Variable to encode/decode the special parameters
    protected static $specialParameters = [
        'page', // 0
        'formKey', // 1
        'mode', // 2
        'folder', // 3
        'orderLink', // 4
        'uid', // 5
        'subformKey', // 6
        'subformUidForeign', // 7
        'subformUidLocal', // 8
        'subformPage', // 9
        'subformActivePages', // 10
        'fileUid', // 11
        'exportUid' //12
    ];

    // Variable to encode/decode the special parameters
    protected static $specialParametersToRemoveIfNotSet = [
        'subformUidForeign'
    ];

    /**
     * Extension settings
     *
     * @var array
     */
    protected $extensionSettings = null;

    /**
     * Front end user manager
     *
     * @var FrontendUserManager
     */
    protected $frontendUserManager;

    /**
     * Field configuration manager
     *
     * @var FieldConfigurationManager
     */
    protected $fieldConfigurationManager;

    /**
     * Viewer configuration
     *
     * @var AbstractViewConfiguration
     */
    protected $viewerConfiguration = null;

    /**
     * ExportRepository
     *
     * @var ExportRepository
     */
    protected $exportRepository = null;

    /**
     * Injects the cache service
     *
     * @param CacheService $cacheService
     * @return void
     */
    public function injectCacheService(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Injects the frontend user manager
     *
     * @param FrontendUserManager $frontendUserManager
     * @return void
     */
    public function injectFrontendUserManager(FrontendUserManager $frontendUserManager)
    {
        $this->frontendUserManager = $frontendUserManager;
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
     * @param ExportRepository $exportRepository
     */
    public function injectExportRepository(ExportRepository $exportRepository)
    {
        $this->exportRepository = $exportRepository;
    }

    /**
     * Initializes the controller for the save action method.
     *
     * @return void
     */
    protected function initializeSaveAction()
    {
        $propertyMappingConfiguration = $this->arguments['data']->getPropertyMappingConfiguration();
        $fields = $this->request->getArgument('data');
        foreach($fields as $propertyName => $field) {
            $dataMapFactory = $this->mainRepository->getDataMapFactory();
            $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
            $tcaFieldConfiguration = $dataMapFactory->getTCAFieldConfiguration($fieldName);
            $fieldType = $dataMapFactory->getFieldType($fieldName);
            if ($fieldType == 'RelationManyToManyAsSubform') {
                $propertyMapping = $propertyMappingConfiguration->forProperty($propertyName);
                $this->processPropertyMapping($tcaFieldConfiguration, $field, $propertyMapping);
            } elseif (($fieldType == 'RelationManyToManyAsDoubleSelectorbox' &&
                !empty($tcaFieldConfiguration['MM'])) || $tcaFieldConfiguration['renderType'] == 'selectMultipleSideBySide') {
                if (is_array($field)) {
                    foreach ($field as $itemKey => $item) {
                        $propertyMappingConfiguration->forProperty($propertyName)->allowProperties($itemKey);
                    }
                }
            }
        }
    }

    /**
     * Processes the property mapping for RelationManyToManyAsDoubleSelectorbox.
     *
     * @return void
     */
    protected function processPropertyMapping($tcaFieldConfigurationFields, $fields, $propertyMapping)
     {
         foreach($fields as $itemsKey => $items) {
             $repositoryClassName = $this->resolveRepositoryClassNameFromTableName($tcaFieldConfigurationFields['foreign_table']);
             $repository = GeneralUtility::makeInstance($repositoryClassName);
             $repository->setController($this);
             $dataMapFactory = $repository->getDataMapFactory();
             foreach($items as $propertyName => $field) {
                 $fieldName = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyName);
                 $tcaFieldConfiguration = $dataMapFactory->getTCAFieldConfiguration($fieldName);
                 $fieldType = $dataMapFactory->getFieldType($fieldName);
                 if ($fieldType == 'RelationManyToManyAsSubform') {
                     $this->processPropertyMapping($tcaFieldConfiguration, $field, $propertyMapping->forProperty($itemsKey)->forProperty($propertyName));
                 } elseif (($fieldType == 'RelationManyToManyAsDoubleSelectorbox' &&
                     !empty($tcaFieldConfiguration['MM'])) || $tcaFieldConfiguration['renderType'] == 'selectMultipleSideBySide') {
                     if (is_array($field)) {
                         foreach ($field as $itemKey => $item) {
                             $propertyMapping->forProperty($itemsKey)->forProperty($propertyName)->allowProperties($itemKey);
                         }
                     }
                 }
             }
         }
     }

    /**
     * Gets the configuration manager.
     *
     * @return ConfigurationManagerInterface
     */
    public function getConfigurationManager(): ConfigurationManagerInterface
    {
        return $this->configurationManager;
    }

    /**
     * Gets the field configuration manager.
     *
     * @return FieldConfigurationManager
     */
    public function getFieldConfigurationManager(): FieldConfigurationManager
    {
        return $this->fieldConfigurationManager;
    }

    /**
     * Gets the request.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Gets the frontend user manager
     *
     * @return FrontendUserManager
     */
    public function getFrontendUserManager(): FrontendUserManager
    {
        return $this->frontendUserManager;
    }

    /**
     * Gets the cache service
     *
     * @return CacheService
     */
    public function getCacheService(): CacheService
    {
        return $this->cacheService;
    }

    /**
     * Gets the controller object name
     *
     * @return string The controller object name
     */
    public function getControllerObjectName(): string
    {
        return $this->request->getControllerObjectName();
    }

    /**
     * Gets the plugin name
     *
     * @return string The plugin name
     */
    public function getPluginName(): string
    {
        return $this->request->getPluginName();
    }

    /**
     * Gets the extension name
     *
     * @return string The extension key
     */
    public function getControllerExtensionName(): string
    {
        return $this->request->getControllerExtensionName();
    }

    /**
     * Gets the extension key
     *
     * @return string The extension key
     */
    public function getControllerExtensionKey(): string
    {
        return $this->request->getControllerExtensionKey();
    }

    /**
     * Gets the controller action name.
     *
     * @return string The controller action name
     */
    public function getControllerActionName(): string
    {
        return  $this->request->getControllerActionName();
    }


    /**
     * Gets the controller name.
     *
     * @return string The controller name
     */
    public function getControllerName(): string
    {
        return  $this->request->getControllerName();
    }

    /**
     * Gets the controller arguments.
     *
     * @return array The controller arguments
     */
    public function getArguments(): array
    {
        return  $this->request->getArguments();
    }

    /**
     * Gets the viewer configuration
     *
     * @param string|null $actionMethodName
     * @return AbstractViewConfiguration
     * @throws \Exception
     */
    public function getViewerConfiguration(?string $actionMethodName = null)
    {
        if ($actionMethodName === null) {
            $actionMethodName = $this->actionMethodName;
        }

        if ($this->viewerConfiguration === null) {

            $action = str_replace('Action', '', ucfirst($actionMethodName));
            $viewerConfigurationClass = 'YolfTypo3\\SavLibraryMvc\\ViewConfiguration\\' . $action . 'ViewConfiguration';

            // Gets the viewer configuration object
            if (! class_exists($viewerConfigurationClass)) {
                throw new \Exception(sprintf(
                    'The viewer configuration class "%s" does not exist.',
                    $viewerConfigurationClass
                    )
                );
            }
            $this->viewerConfiguration = GeneralUtility::makeInstance($viewerConfigurationClass);
            $this->viewerConfiguration->setController($this);
        }

        return $this->viewerConfiguration;
    }

    /**
     * Gets the main repository
     *
     * @return DefaultRepository
     */
    public function getMainRepository()
    {
        return $this->mainRepository;
    }

    /**
     * Gets the export repository
     *
     * @return ExportRepository
     */
    public function getExportRepository(): ExportRepository
    {
        return $this->exportRepository;
    }

    /**
     * Gets the view identifiers
     *
     * @return array
     */
    public function getViewIdentifiers(): array
    {
        if (is_array($this->controllerConfiguration['viewIdentifiers'])) {
            return $this->controllerConfiguration['viewIdentifiers'];
        } else {
            return [];
        }
    }

    /**
     * Gets the view title bar
     *
     * @param int $viewIdentifier
     *            The view identifier
     * @return string
     */
    public function getViewTitleBar(int $viewIdentifier): string
    {
        if (is_array($this->controllerConfiguration['viewTitleBars'])) {
            return $this->controllerConfiguration['viewTitleBars'][$viewIdentifier];
        } else {
            return '';
        }
    }

    /**
     * Gets the view item template
     *
     * @param int $viewIdentifier
     *            The view identifier
     * @return string
     */
    public function getViewItemTemplate(int $viewIdentifier): string
    {
        if (is_array($this->controllerConfiguration['viewItemTemplates'])) {
            return $this->controllerConfiguration['viewItemTemplates'][$viewIdentifier];
        } else {
            return '';
        }
    }

    /**
     * Gets the view active folder
     *
     * @param int $viewIdentifier
     * @param string|null $folderIdentifier
     * @return string
     */
    public function getActiveFolder(int $viewIdentifier, ?string $folderIdentifier)
    {
        if (is_array($this->controllerConfiguration['folders'])) {
            $folders = $this->controllerConfiguration['folders'][$viewIdentifier];

            if (is_array($folders)) {
                // Sorts the folder by the order field
                uasort (
                    $folders ,
                    function ($a, $b) {
                        return $a['order'] < $b['order'] ? -1 : 1;
                    }
                );

                // Checks if the folder exists otherwise return the first folder
                if ($folderIdentifier > 0) {
                    if (empty($folders[$folderIdentifier])) {
                        return key($folders);
                    } else {
                        return (int) $folderIdentifier;
                    }
                } else {
                    return key($folders);
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Gets the folder
     *
     * @param string $viewIdentifier
     *            The view identifier
     * @return array
     */
    public function getFolders(string $viewIdentifier): array
    {
        if (is_array($this->controllerConfiguration['folders'])) {
            $folders = $this->controllerConfiguration['folders'][$viewIdentifier];

            // Processes the folder configuration
            foreach ($folders as $folderKey => $folder) {
                if (! empty($folder['configuration'])) {
                    if (!empty ($folder['configuration']['cutIf'])) {
                        $cutFolder = $this->fieldConfigurationManager->processFieldCondition($folder['configuration']['cutIf']);
                        if ($cutFolder === true) {
                            unset ($folders[$folderKey]);
                        }
                    }
                }
            }
            uasort (
                $folders ,
                function ($a, $b) {
                    return $a['order'] < $b['order'] ? -1 : 1;
                }
            );
            return $folders;
        } else {
            return [];
        }
    }

    /**
     * Gets the query identifier.
     *
     * @return int|null
     */
    public function getQueryIdentifier(): ?int
    {
        if (is_array($this->controllerConfiguration)) {
            return $this->controllerConfiguration['queryIdentifier'];
        } else {
            return null;
        }
    }

    /**
     * Gets the subform information from its key
     *
     * @param int $subformKey
     *            The subform key
     * @return array
     */
    public function getSubform(int $subformKey): array
    {
        $subform = $this->subforms[$subformKey];
        return $subform;
    }

    /**
     * Gets the subform information from the field name
     *
     * @param string $fieldName
     *
     * @return array
     */
    public function getSubformFromFieldName(string $fieldName): array
    {
        $subformKey = array_search($fieldName, array_column($this->subforms, 'fieldName'));
        return $this->getSubform($subformKey);
    }

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    protected function initializeAction()
    {
        // Gets the controller identifier
        $controllerIdentifier = $this->getSetting('formId');
        $configuration = $GLOBALS['TCA']['tx_savlibrarymvc_domain_model_configuration']['ctrl']['EXT'][$this->getControllerExtensionKey()];
        $this->controllerConfiguration = $configuration['controllers'][$controllerIdentifier];

        // Redirects to the required controller if not the defaut one
        $controllerName = $this->controllerConfiguration['name'];
        if ($this->getControllerName() != $controllerName) {
            $this->redirect($this->getControllerActionName(),
                $controllerName,
                $this->getControllerExtensionName(),
                $this->getArguments());
        }

        // Checks if the static extension template is included
        /** @var FrontendConfigurationManager $frontendConfigurationManager */
        $frontendConfigurationManager = GeneralUtility::makeInstance(FrontendConfigurationManager::class);
        $typoScriptSetup = $frontendConfigurationManager->getTypoScriptSetup();
        $pluginSetupName = 'tx_' . strtolower($this->getControllerExtensionName()) . '.';
        if (! @is_array($typoScriptSetup['plugin.'][$pluginSetupName]['view.'])) {
            die('Fatal error: You have to include the static template of the extension ' . $this->getControllerExtensionKey() . '.');
        }

        // Sets the controller where required
        $this->frontendUserManager->setController($this);
        $this->mainRepository->setController($this);
        $this->fieldConfigurationManager->setController($this);
        AdditionalHeaderManager::setController($this);

        // Adds the style sheets
        AdditionalHeaderManager::addCascadingStyleSheets();
    }

    /**
     * Gets the controller identifier.
     *
     * @return int The controller identifier
     */
    public function getControllerIdentifier(): int
    {
        return $this->getSetting('formId');
    }

    /**
     * Gets the plugin name space.
     *
     * @return string The plugin spacename
     */
    public function getPluginNameSpace(): string
    {
        return 'tx_' . str_replace('_', '', $this->getControllerExtensionKey()) . '_' . strtolower($this->getPluginName());
    }

    /**
     * Gets the template root paths.
     *
     * @return array The template root paths
     */
    public function getTemplateRootPaths(): array
    {
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $templateRootPaths = $extbaseFrameworkConfiguration['view']['templateRootPaths'];

        return $templateRootPaths;
    }

    /**
     * Gets the partial root paths.
     *
     * @return array The partial root paths
     */
    public function getPartialRootPaths(): array
    {
        $extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $partialRootPaths = $extbaseFrameworkConfiguration['view']['partialRootPaths'];

        return $partialRootPaths;
    }

    /**
     * Gets a setting.
     *
     * @param string $settingName
     *            The setting name
     * @return mixed
     */
    public function getSetting(string $settingName)
    {
        if ($this->extensionSettings === null) {
            $this->extensionSettings = $this->settings;
        }
        return $this->extensionSettings[$settingName];
    }

    /**
     * Resolves the repository class name from the table name
     *
     * @var string $tableName
     * @return string
     */
    public function resolveRepositoryClassNameFromTableName(string $tableName): string
    {
        if ($tableName == 'fe_groups') {
            return \YolfTypo3\SavLibraryMvc\Domain\Repository\FrontendUserGroupRepository::class;
        }
        if ($tableName == 'fe_users') {
            return \YolfTypo3\SavLibraryMvc\Domain\Repository\FrontendUserRepository::class;
        }

        // Gets the repository name
        $repositoryName = preg_replace('/^tx_[^_]+_domain_model_(.+)$/', '$1', $tableName);
        $repositoryName = GeneralUtility::underscoredToUpperCamelCase($repositoryName);

        // Gets the repository class name
        $repositoryClassName = preg_replace('/^(.+?)\\\\Controller\\\\.+$/', '$1\\\\Domain\\\\Repository\\\\' . $repositoryName . 'Repository', $this->getControllerObjectName());

        return $repositoryClassName;
    }


    /**
     * Gets the content object
     *
     * @return ContentObjectRenderer
     */
    public function getContentObjectRenderer(): ContentObjectRenderer
    {
        return $this->configurationManager->getContentObject();
    }

    /**
     * Gets the default date format from the library TypoScript configuration if any.
     *
     * @param string $extensionKey
     *
     * @return array|null
     */
    public static function getTypoScriptConfiguration(string $extensionKey): ?array
    {
        $prefixId = 'tx_' . str_replace('_', '', $extensionKey) . '.';
        $typoScriptConfiguration = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$prefixId];
        if (is_array($typoScriptConfiguration)) {
            return $typoScriptConfiguration;
        } else {
            return null;
        }
    }

    /**
     * Gets the relative web path of a given extension.
     *
     * @param string $extensionKey
     *            The extension key
     *
     * @return string The relative web path
     */
    public static function getExtensionWebPath(string $extensionKey): string
    {
        $extensionWebPath = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath($extensionKey));
        if ($extensionWebPath[0] === '/') {
            // Makes the path relative
            $extensionWebPath = substr($extensionWebPath, 1);
        }
        return $extensionWebPath;
    }

    /**
     * Gets the icon file name with its extension by checking if it exists in the given path.
     *
     * @param string $path
     *            The file path
     * @param string $fileName
     *            The file name without extension
     *
     * @return string The file name with extension
     */
    protected static function getFileNameWithExtension(string $path, string $fileName): string
    {
        if (preg_match('/^[^\.]+\.\w+$/', $fileName) > 0) {
            // The file name has an extension
            if (is_file(GeneralUtility::getFileAbsFileName($path . $fileName))) {
                return $fileName;
            }
        } else {
            // The file has no extension. All possible extensions are checked
            $iconFileNameExtensions = explode(',', self::$allowedIconFileNameExtensions);
            foreach ($iconFileNameExtensions as $iconFileNameExtension) {
                $fileNameWithExtension = $fileName . $iconFileNameExtension;
                if (is_file(GeneralUtility::getFileAbsFileName($path . $fileNameWithExtension))) {
                    return $fileNameWithExtension;
                }
            }
        }
        return '';
    }

    /**
     * Sets the view configuration
     *
     * @param ViewInterface $view
     *
     * @return void
     */
    protected function setViewConfiguration($view)
    {
        // Calls the parent function.
        // @TODO in V12, type ViewInterface could be added in th eparameter declaration.
        parent::setViewConfiguration($view);
        // Sets the template path and file name
        $viewFunctionName = 'setTemplatePathAndFilename';
        if (method_exists($view, $viewFunctionName)) {
            $templateRootPaths = $this->getTemplateRootPaths();
            foreach ($templateRootPaths as $templateRootPath) {
                $parameter = GeneralUtility::getFileAbsFileName($templateRootPath) . '/Default/' . ucfirst(str_replace('Action', '', $this->actionMethodName)) . '.html';
                // no need to bother if there is nothing to set
                if ($parameter) {
                    $view->$viewFunctionName($parameter);
                    return;
                }
            }
        }
    }

    /**
     * Gets the view configuration
     *
     * @param array $arguments
     *            Arguments from the action
     * @return array
     */
    public function getViewConfiguration(array $arguments = []): array
    {
        $viewConfiguration = $this->getViewerConfiguration()->getConfiguration($arguments);

        return $viewConfiguration;
    }

    /**
     * Uncompresses a parameter string into an array
     *
     * @param string|null $compressedParameters
     *            The compressed parameter
     * @return array The uncompressed parameter array
     */
    public static function uncompressParameters(?string $compressedParameters): array
    {
        if ($compressedParameters === null) {
            return [
                'mode' => self::DEFAULT_MODE
            ];
        }
        $parameters = [];
        while (! empty($compressedParameters)) {
            // Reads the index
            list ($parameterIndex) = sscanf($compressedParameters, '%1x');
            $parameterKey = self::$specialParameters[$parameterIndex];
            if (! isset($parameterKey)) {
                return $parameters;
            }
            $compressedParameters = substr($compressedParameters, 1);

            // Reads the length
            list ($length) = sscanf($compressedParameters, '%1x');
            $compressedParameters = substr($compressedParameters, 1);

            // Reads the value
            list ($parameterValue) = sscanf($compressedParameters, '%' . $length . 's');
            $compressedParameters = substr($compressedParameters, $length);

            // Sets the parameter
            $parameters[$parameterKey] = $parameterValue;
        }
        return $parameters;
    }

    /**
     * Compresses an array of parameters
     *
     * @param array $parameters
     *            The parameter array to compress
     * @return string The compressed parameter string
     */
    public static function compressParameters(array $parameters): string
    {
        $compressedParameters = '';

        foreach ($parameters as $parameterKey => $parameter) {
            $parameterIndex = array_search($parameterKey, self::$specialParameters);
            if ($parameterIndex === false) {
                return '';
            } else {
                $compressedParameters .= dechex($parameterIndex);
            }
            $compressedParameters .= sprintf('%01x%s', strlen($parameter), $parameter);
        }
        return $compressedParameters;
    }

    /**
     * Uncompresses a parameter string into an array
     *
     * @param string|null $compressedParameters
     *            The compressed parameter
     * @return array The uncompressed parameter array
     */
    public static function uncompressSubformActivePages(?string $compressedParameters): array
    {
        $parameters = [];
        while (! empty($compressedParameters)) {
            // Reads the index
            list ($parameterIndex) = sscanf($compressedParameters, '%1x');
            $parameterKey = self::$specialParameters[$parameterIndex];
            if ($parameterKey != 'subformKey') {
                return $parameters;
            }
            $compressedParameters = substr($compressedParameters, 1);

            // Reads the length
            list ($length) = sscanf($compressedParameters, '%1x');
            $compressedParameters = substr($compressedParameters, 1);

            // Reads the value
            list ($parameterValue) = sscanf($compressedParameters, '%' . $length . 's');
            $compressedParameters = substr($compressedParameters, $length);

            // Sets the parameter
            $subformKey = $parameterValue;

            // Reads the index
            list ($parameterIndex) = sscanf($compressedParameters, '%1x');
            $parameterKey = self::$specialParameters[$parameterIndex];
            if ($parameterKey != 'subformPage') {
                return $parameters;
            }
            $compressedParameters = substr($compressedParameters, 1);

            // Reads the length
            list ($length) = sscanf($compressedParameters, '%1x');
            $compressedParameters = substr($compressedParameters, 1);

            // Reads the value
            list ($parameterValue) = sscanf($compressedParameters, '%' . $length . 's');
            $compressedParameters = substr($compressedParameters, $length);

            // Sets the parameter
            $subformPage = $parameterValue;

            $parameters[$subformKey] = $subformPage;
        }
        return $parameters;
    }

    /**
     * Changes a parameter in the compressed parameters string
     *
     * @param string|null $compressedParameters
     *            The compressed parameters string
     * @param array $newParameters
     *            Array of (key => value) to change
     *
     * @return string The modified compressed parameter string
     */
    public static function changeCompressedParameters(?string $compressedParameters, array $newParameters): string
    {
        $uncompressParameters = self::uncompressParameters($compressedParameters);

        // Removes the parameters which have to be removed if not set
        if (empty(array_intersect(self::$specialParametersToRemoveIfNotSet, array_keys($newParameters)))) {
            foreach (self::$specialParametersToRemoveIfNotSet as $parameterToRemove) {
                unset($uncompressParameters[$parameterToRemove]);
            }
        }
        // Modifies the parameters
        foreach ($newParameters as $key => $value) {
            $uncompressParameters[$key] = $value;
        }

        return self::compressParameters($uncompressParameters);
    }

    /**
     * Changes a page in compressed subform pages
     *
     * @param string $compressedSubformActivePages
     *            The compressed subform active pages
     * @param string $subformKey
     *            The subform key
     * @param mixed $subformPage
     *            The new page
     *
     * @return string The modified compressed parameter string
     */
    public static function changeCompressedSubformActivePages($compressedSubformActivePages, $subformKey, $subformPage)
    {
        $compressedParameters = '';
        $uncompressedSubformActivePages = self::uncompressSubformActivePages($compressedSubformActivePages);
        $uncompressedSubformActivePages[$subformKey] = $subformPage;
        // Compresses the subform active pages
        foreach ($uncompressedSubformActivePages as $subformKey => $subformPage) {
            $compressedParameters .= self::compressParameters([
                'subformKey' => $subformKey,
                'subformPage' => $subformPage
            ]);
        }

        return $compressedParameters;
    }

    /**
     * Gets the action method name
     *
     * @return string
     */
    public function getActionMethodName(): string
    {
        return $this->actionMethodName;
    }

    /**
     * Gets the site path
     *
     * @return string
     */
    public static function getSitePath(): string
    {
        return Environment::getPublicPath() . '/';
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

}