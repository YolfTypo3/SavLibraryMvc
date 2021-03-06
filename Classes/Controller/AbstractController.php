<?php
namespace YolfTypo3\SavLibraryMvc\Controller;

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
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use YolfTypo3\SavLibraryMvc\Domain\Repository\DefaultRepository;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;
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
     * Icons root path
     *
     * @var string
     */
    public static $iconRootPath = 'Resources/Public/Icons';

    /**
     * Images root path
     *
     * @var string
     */
    public static $imageRootPath = 'Resources/Public/Images';

    /**
     * Css root path
     *
     * @var string
     */
    public static $cssRootPath = 'Resources/Public/Css';

    /**
     * Styles root path (for compatibility with previously generated extensions)
     *
     * @var string
     */
    public static $stylesRootPath = 'Resources/Public/Styles';

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
        'fileUid' // 11
    ];

    // Variable to encode/decode the special parameters
    protected static $specialParametersToRemoveIfNotSet = [
        'subformUidForeign'
    ];

    /**
     * Controller object name
     *
     * @var string
     */
    protected static $controllerObjectName;

    /**
     * Controller extension key
     *
     * @var string
     */
    protected static $controllerExtensionKey;

    /**
     * Controller name
     *
     * @var string
     */
    protected static $controllerName;

    /**
     * Original arguments
     *
     * @var array
     */
    protected static $originalArguments;

    /**
     * Extension settings
     *
     * @var array
     */
    protected static $extensionSettings;

    /**
     * Extbase framework configuration
     *
     * @var array
     */
    protected static $extbaseFrameworkConfiguration;

    /**
     * Front end user manager
     *
     * @var FrontendUserManager
     */
    protected $frontendUserManager;

    /**
     * Viewer configuration
     *
     * @var AbstractViewConfiguration
     */
    protected $viewerConfiguration = null;

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
     * Gets the configuration manager.
     *
     * @return ConfigurationManagerInterface
     */
    public function getConfigurationManager()
    {
        return $this->configurationManager;
    }

    /**
     * Gets the request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Gets the object manager
     *
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Gets the frontend user manager
     *
     * @return FrontendUserManager
     */
    public function getFrontendUserManager()
    {
        return $this->frontendUserManager;
    }

    /**
     * Gets the viewer configuration
     *
     * @return AbstractViewConfiguration
     */
    public function getViewerConfiguration($actionMethodName = null)
    {
        if ($actionMethodName === null) {
            $actionMethodName = $this->actionMethodName;
        }

        if ($this->viewerConfiguration === null) {

            $action = str_replace('Action', '', ucfirst($actionMethodName));
            $viewerConfigurationClass = 'YolfTypo3\\SavLibraryMvc\\ViewConfiguration\\' . $action . 'ViewConfiguration';
            if (! $this->objectManager->isRegistered($viewerConfigurationClass)) {
                // TODO Adds an error message
                return null;
            }
            // Gets the viewer configuration object
            $this->viewerConfiguration = $this->objectManager->get($viewerConfigurationClass, $this);
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
     * Gets the view identifiers
     *
     * @return array
     */
    public function getViewIdentifiers()
    {
        $dataMapFactory = $this->mainRepository->getDataMapFactory();
        return $dataMapFactory->getSavLibraryMvcControllerViewIdentifiers(self::$controllerName);
    }

    /**
     * Gets the view title bar
     *
     * @param string $viewType
     *            The view type
     * @return integer
     */
    public function getViewTitleBar($viewType)
    {
        $dataMapFactory = $this->mainRepository->getDataMapFactory();
        return $dataMapFactory->getSavLibraryMvcControllerViewTitleBar(self::$controllerName, $viewType);
    }

    /**
     * Gets the view item template
     *
     * @param string $viewType
     *            The view type
     * @return integer
     */
    public function getViewItemTemplate($viewType)
    {
        $dataMapFactory = $this->mainRepository->getDataMapFactory();
        return $dataMapFactory->getSavLibraryMvcControllerViewItemTemplate(self::$controllerName, $viewType);
    }

    /**
     * Gets the folder
     *
     * @param string $viewType
     *            The view type
     * @return integer
     */
    public function getFolders($viewType)
    {
        $dataMapFactory = $this->mainRepository->getDataMapFactory();
        return $dataMapFactory->getSavLibraryMvcControllerFolders(self::$controllerName, $viewType);
    }

    /**
     * Gets the subform information from its key
     *
     * @param integer $subformKey
     *            The subform key
     * @return array
     */
    public function getSubform($subformKey)
    {
        $subform = $this->subforms[$subformKey];
        return $subform;
    }

    /**
     * Initializes the controller before invoking an action method.
     *
     * @return void
     */
    protected function initializeAction()
    {
        // Gets the extension settings
        self::$extensionSettings = $this->settings;

        // Keeps the controller information
        self::$controllerObjectName = $this->request->getControllerObjectName();
        self::$controllerExtensionKey = $this->request->getControllerExtensionKey();
        self::$originalArguments = $this->request->getArguments();

        // Checks if the static extension template is included
        /** @var FrontendConfigurationManager $frontendConfigurationManager */
        $frontendConfigurationManager = GeneralUtility::makeInstance(FrontendConfigurationManager::class);
        $typoScriptSetup = $frontendConfigurationManager->getTypoScriptSetup();
        $pluginSetupName = 'tx_' . strtolower($this->request->getControllerExtensionName()) . '.';
        if (! @is_array($typoScriptSetup['plugin.'][$pluginSetupName]['view.'])) {
            die('Fatal error: You have to include the static template of the extension ' . $this->request->getControllerExtensionKey() . '.');
        }

        // Sets the controller index from the settings
        $controllerIndex = self::getSetting('formId');
        $dataMapFactory = $this->mainRepository->getDataMapFactory();
        self::$controllerName = $dataMapFactory->getControllerNameFromIndex($controllerIndex);
        $this->request->setControllerName(self::$controllerName);

        // Gets the extension framework configuration
        self::$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        // Sets the controller where required
        $this->frontendUserManager->setController($this);
        $this->mainRepository->setController($this);

        // Adds the style sheets
        AdditionalHeaderManager::addCascadingStyleSheets();
    }

    /**
     * Gets the controller object name
     *
     * @return string The controller object name
     */
    public static function getControllerObjectName()
    {
        return self::$controllerObjectName;
    }

    /**
     * Gets the extension key
     *
     * @return string The extension key
     */
    public static function getControllerExtensionKey()
    {
        return self::$controllerExtensionKey;
    }

    /**
     * Gets the controller name.
     *
     * @return string The controller name
     */
    public static function getControllerName()
    {
        return self::$controllerName;
    }

    /**
     * Gets the arguments.
     *
     * @return string The arguments
     */
    public static function getOriginalArguments()
    {
        return self::$originalArguments;
    }

    /**
     * Gets the plugin name space.
     *
     * @return string The plugin spacename
     */
    public static function getPluginNameSpace()
    {
        return 'tx_' . str_replace('_', '', self::getControllerExtensionKey()) . '_pi1';
    }

    /**
     * Gets the template root paths.
     *
     * @return string The template root paths
     */
    public static function getTemplateRootPaths()
    {
        $templateRootPaths = self::$extbaseFrameworkConfiguration['view']['templateRootPaths'];
        return $templateRootPaths;
    }

    /**
     * Gets the partial root paths.
     *
     * @return string The partial root paths
     */
    public static function getPartialRootPaths()
    {
        $partialRootPaths = self::$extbaseFrameworkConfiguration['view']['partialRootPaths'];
        return $partialRootPaths;
    }

    /**
     * Gets a setting.
     *
     * @param string $settingName
     *            The setting name
     * @return mixed
     */
    public static function getSetting($settingName)
    {
        return self::$extensionSettings[$settingName];
    }

    /**
     * Gets the content object
     *
     * @return ContentObjectRenderer
     */
    public function getContentObjectRenderer()
    {
        return $this->configurationManager->getContentObject();
    }

    /**
     * Gets the default date format from the library TypoScript configuration if any.
     *
     * @param string $extensionKey
     *
     * @return string
     */
    public static function getTypoScriptConfiguration($extensionKey)
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
     * Gets the icon path
     *
     * @param string $fileName
     *            The file name without extension
     *
     * @return string
     */
    public static function getIconPath($fileName)
    {
        // The icon directory is taken from the configuration in TS if set,
        // else from the Resources/Icons folder in the extension if it exists,
        // else from the default Resources/Icons in the SAV Library Plus extension if it exists
        // File name extension is added from allowed files name extensions.
        $libraryTypoScriptConfiguration = self::getTypoScriptConfiguration(self::LIBRARY_NAME);
        $extensionTypoScriptConfiguration = self::getTypoScriptConfiguration(self::getControllerExtensionKey());
        $formTypoScriptConfiguration = $extensionTypoScriptConfiguration[self::getControllerName() . '.'];

        // Checks if the file name is in the iconRootPath defined by the form configuration in TS
        $fileNameWithExtension = self::getFileNameWithExtension($formTypoScriptConfiguration['iconRootPath'] . '/', $fileName);
        if (! empty($fileNameWithExtension)) {
            return substr(GeneralUtility::getFileAbsFileName($formTypoScriptConfiguration['iconRootPath']), strlen(self::getSitePath())) . '/' . $fileNameWithExtension;
        }

        // If not found, checks if the file name is in the iconRootPath defined by the extension configuration in TS
        $fileNameWithExtension = self::getFileNameWithExtension($extensionTypoScriptConfiguration['iconRootPath'] . '/', $fileName);
        if (! empty($fileNameWithExtension)) {
            return substr(GeneralUtility::getFileAbsFileName($extensionTypoScriptConfiguration['iconRootPath']), strlen(self::getSitePath())) . '/' . $fileNameWithExtension;
        }

        // If not found, checks if the file name is in the iconRootPath defined by the library configuration in TS
        $fileNameWithExtension = self::getFileNameWithExtension($libraryTypoScriptConfiguration['iconRootPath'] . '/', $fileName);
        if (! empty($fileNameWithExtension)) {
            return substr(GeneralUtility::getFileAbsFileName($libraryTypoScriptConfiguration['iconRootPath']), strlen(self::getSitePath())) . '/' . $fileNameWithExtension;
        }

        // If not found, checks if the file name is in Resources/Icons folder of the extension
        $extensionWebPath = self::getExtensionWebPath(self::getControllerExtensionKey());
        $fileNameWithExtension = self::getFileNameWithExtension($extensionWebPath . self::$iconRootPath . '/', $fileName);
        if (! empty($fileNameWithExtension)) {
            return $extensionWebPath . self::$iconRootPath . '/' . $fileNameWithExtension;
        }

        // If not found, checks if the file name is in Resources/Icons folder of the SAV Library Mvc extension
        $extensionWebPath = self::getExtensionWebPath(self::LIBRARY_NAME);
        $fileNameWithExtension = self::getFileNameWithExtension($extensionWebPath . self::$iconRootPath . '/', $fileName);
        if (! empty($fileNameWithExtension)) {
            return $extensionWebPath . self::$iconRootPath . '/' . $fileNameWithExtension;
        }

        return '';
    }

    /**
     * Gets the images directory
     *
     * @return boolean
     */
    public static function getImageRootPath($fileName)
    {
        // The images directory is taken from the configuration in TS if set,
        // else from the Resources/Images folder in the extension if it exists,
        // else from the default Resources/Images in the library.
        $libraryTypoScriptConfiguration = self::getTypoScriptConfiguration(self::LIBRARY_NAME);
        $extensionTypoScriptConfiguration = self::getTypoScriptConfiguration(self::getControllerExtensionKey());
        $formTypoScriptConfiguration = $extensionTypoScriptConfiguration[self::getControllerName() . '.'];
        $extensionWebPath = self::getExtensionWebPath(self::getControllerExtensionKey());

        if (is_file(GeneralUtility::getFileAbsFileName($formTypoScriptConfiguration['imageRootPath'] . '/' . $fileName))) {
            return substr(GeneralUtility::getFileAbsFileName($formTypoScriptConfiguration['imageRootPath']), strlen(self::getSitePath())) . '/';
        } elseif (is_file(GeneralUtility::getFileAbsFileName($extensionTypoScriptConfiguration['imageRootPath'] . '/' . $fileName))) {
            return substr(GeneralUtility::getFileAbsFileName($extensionTypoScriptConfiguration['imageRootPath']), strlen(self::getSitePath())) . '/';
        } elseif (is_file(GeneralUtility::getFileAbsFileName($libraryTypoScriptConfiguration['imageRootPath'] . '/' . $fileName))) {
            return substr(GeneralUtility::getFileAbsFileName($libraryTypoScriptConfiguration['imageRootPath']), strlen(self::getSitePath())) . '/';
        } elseif (is_file($extensionWebPath . self::$imageRootPath . '/' . $fileName)) {
            return $extensionWebPath . self::$imageRootPath . '/';
        } else {
            $extensionWebPath = self::getExtensionWebPath(self::LIBRARY_NAME);
            return $extensionWebPath . self::$imageRootPath . '/';
        }
    }

    /**
     * Gets the relative web path of a give extension.
     *
     * @param string $extension
     *            The extension
     *
     * @return string The relative web path
     */
    public static function getExtensionWebPath($extension)
    {
        $extensionWebPath = PathUtility::getAbsoluteWebPath(ExtensionManagementUtility::extPath($extension));
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
    protected static function getFileNameWithExtension($path, $fileName)
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
    protected function setViewConfiguration(ViewInterface $view)
    {
        parent::setViewConfiguration($view);
        // Sets the template path and file name
        $viewFunctionName = 'setTemplatePathAndFilename';
        if (method_exists($view, $viewFunctionName)) {
            $templateRootPaths = self::getTemplateRootPaths();
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
    public function getViewConfiguration($arguments = [])
    {
        $viewConfiguration = $this->getViewerConfiguration()->getConfiguration($arguments);

        return $viewConfiguration;
    }

    /**
     * Uncompresses a parameter string into an array
     *
     * @param string $compressedParameters
     *            The compressed parameter
     * @return array The uncompressed parameter array
     */
    public static function uncompressParameters($compressedParameters)
    {
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
    public static function compressParameters($parameters)
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
     * @param string $compressedParameters
     *            The compressed parameter
     * @return array The uncompressed parameter array
     */
    public static function uncompressSubformActivePages($compressedParameters)
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
     * @param string $compressedParameters
     *            The compressed parameters string
     * @param array $newParameters
     *            Array of (key => value) to change
     *
     * @return string The modified compressed parameter string
     */
    public static function changeCompressedParameters($compressedParameters, $newParameters)
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
     * Gets the versioning workspace id
     *
     * @return integer The versioning workspace id
     */
    public function getVersioningWorkspaceId()
    {
        return (isset($GLOBALS['TSFE']->sys_page->versioningWorkspaceId) ? $GLOBALS['TSFE']->sys_page->versioningWorkspaceId : null);
    }

    /**
     * Gets the table name
     *
     * @return string
     */
    protected function getTableName($modelName)
    {
        $tableName = 'tx_' . str_replace('_', '', self::getExtensionKey()) . '_domain_model_' . GeneralUtility::camelCaseToLowerCaseUnderscored($modelName);
        return $tableName;
    }

    /**
     * Gets the action method name
     *
     * @return string
     */
    public function getActionMethodName()
    {
        return $this->actionMethodName;
    }

    /**
     * Generates the edit view configuration
     *
     * @param string $viewType
     *            The view type
     * @return array The folder configuration
     */
    protected function getViewFolders($viewType)
    {
        $viewFolders = $this->getFolders($viewType);

        // Sets the folder key
        $special = $this->generalManager->getGeneralConfigurationValue('special');
        $uncompressedParameters = $this->generalManager->uncompressParameters($special);
        if ($uncompressedParameters['folder']) {
            $activeFolder = (empty($viewFolders) ? 0 : $uncompressedParameters['folder']);
        } else {
            $activeFolder = (empty($viewFolders) ? 0 : key($viewFolders));
        }
        $this->generalManager->setGeneralConfigurationValue('activeFolder', $activeFolder);
        return $viewFolders;
    }

    /**
     * Gets the site path
     *
     * @return string
     */
    public static function getSitePath()
    {
        return Environment::getPublicPath() . '/';
    }
}
?>