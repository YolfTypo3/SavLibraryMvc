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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use SAV\SavLibraryMvc\Controller\AbstractController;
use SAV\SavLibraryMvc\Controller\FlashMessages;

/**
 * Additional header manager.
 */
class AdditionalHeaderManager
{

    /**
     * Array of javaScript code used for the view
     *
     * @var array
     */
    protected static $javaScript = array();

    /**
     * Adds the css files
     *
     * @return void
     */
    public static function addCascadingStyleSheets()
    {
        // Adds the library cascading style sheet
        self::addLibraryCascadingStyleSheet();

        // Adds the extension cascading style sheet
        self::addExtensionCascadingStyleSheet();
    }

    /**
     * Adds the library css file
     * - from the stylesheet TypoScript configuration if any
     * - else from the default css file which is in the "Styles" directory of the SAV Library Mvc
     *
     * @return void
     */
    protected static function addLibraryCascadingStyleSheet()
    {
        $extensionKey = AbstractController::LIBRARY_NAME;
        $typoScriptConfiguration = AbstractController::getTypoScriptConfiguration($extensionKey);
        if (empty($typoScriptConfiguration['stylesheet'])) {
            $cascadingStyleSheet = ExtensionManagementUtility::siteRelPath($extensionKey) . AbstractController::$stylesRootPath . '/' . $extensionKey . '.css';
            self::addCascadingStyleSheet($cascadingStyleSheet);
        } else {
            $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($typoScriptConfiguration['stylesheet']);
            if (is_file($cascadingStyleSheetAbsoluteFileName)) {
                $cascadingStyleSheet = substr($cascadingStyleSheetAbsoluteFileName, strlen(PATH_site));
                self::addCascadingStyleSheet($cascadingStyleSheet);
            } else {
                throw new \SAV\SavLibraryMvc\Exception(FlashMessages::translate('error.fileDoesNotExist', array(
                    htmlspecialchars($cascadingStyleSheetAbsoluteFileName)
                )));
            }
        }
    }

    /**
     * Adds the extension css file if any
     * The css file should be extension.css in the "Styles" directory
     * where "extension" is the extension key
     *
     * @return void
     */
    protected static function addExtensionCascadingStyleSheet()
    {
        $extensionKey = AbstractController::getControllerExtensionKey();
        $typoScriptConfiguration = AbstractController::getTypoScriptConfiguration($extensionKey);
        if (empty($typoScriptConfiguration['stylesheet']) === FALSE) {
            $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($typoScriptConfiguration['stylesheet']);
            if (is_file($cascadingStyleSheetAbsoluteFileName)) {
                $cascadingStyleSheet = substr($cascadingStyleSheetAbsoluteFileName, strlen(PATH_site));
                self::addCascadingStyleSheet($cascadingStyleSheet);
            } else {
                throw new \SAV\SavLibraryMvc\Exception(FlashMessages::translate('error.fileDoesNotExist', array(
                    htmlspecialchars($cascadingStyleSheetAbsoluteFileName)
                )));
            }
        } elseif (is_file(ExtensionManagementUtility::extPath($extensionKey) . AbstractController::$stylesRootPath . '/' . $extensionKey . '.css')) {
            $cascadingStyleSheet = ExtensionManagementUtility::siteRelPath($extensionKey) . AbstractController::$stylesRootPath . '/' . $extensionKey . '.css';
            self::addCascadingStyleSheet($cascadingStyleSheet);
        }
    }

    /**
     * Adds a cascading style Sheet
     *
     * @param string $key
     * @param string $cascadingStyleSheet
     *
     * @return void
     */
    public static function addCascadingStyleSheet($cascadingStyleSheet)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssFile($cascadingStyleSheet);
    }

    /**
     * gets the cascading style Sheet link
     *
     * @param string $cascadingStyleSheet
     *
     * @return string
     */
    protected static function getCascadingStyleSheetLink($cascadingStyleSheet)
    {
        $cascadingStyleSheetLink = '<link rel="stylesheet" type="text/css" href="' . $cascadingStyleSheet . '" />' . chr(10);
        return $cascadingStyleSheetLink;
    }

    /**
     * Loads a required Js module
     *
     * @param string $mainModuleName
     *
     * @return void
     */
    public static function loadRequireJsModule($mainModuleName)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule($mainModuleName);
    }

    /**
     * Loads the extJS library
     *
     * @return void
     */
    public static function loadExtJS()
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadExtJS();
    }

    /**
     * Adds a javaScript file
     *
     * @param string $javaScriptFileName
     *
     * @return void
     */
    public static function addJavaScriptFile($javaScriptFileName)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile($javaScriptFileName);
    }

    /**
     * Adds a javaScript inline code
     *
     * @param string $javaScriptFileName
     *
     * @return void
     */
    public static function addJavaScriptInlineCode($key, $javaScriptInlineCode)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsInlineCode($key, $javaScriptInlineCode);
    }

    /**
     * Adds Javascript Inline Setting.
     *
     * @param string $namespace
     * @param array $array
     * @return void
     */
    public static function addInlineSettingArray($namespace, array $array)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addInlineSettingArray($namespace, $array);
    }

    /**
     * Adds the javaScript header
     *
     *
     * @return void
     */
    public static function addAdditionalJavaScriptHeader()
    {
        if (count(self::$javaScript) > 0) {
            if (count(self::$javaScript['selectAll']) > 0) {
                $javaScriptFileName = ExtensionManagementUtility::siteRelPath(AbstractController::LIBRARY_NAME) .
                    AbstractController::$javaScriptRootPath . '/' . AbstractController::LIBRARY_NAME . '.js';
                self::addJavaScriptFile($javaScriptFileName);
            }
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addJsInlineCode(AbstractController::LIBRARY_NAME, self::getJavaScriptHeader());
        }
    }

    /**
     * Adds javaScript to a given key
     *
     * @param $key string
     *            The key
     * @param $javaScript array
     *            The javaScript
     *
     * @return void
     */
    public static function addJavaScript($key, $javaScript = NULL)
    {
        if (! is_array(self::$javaScript[$key])) {
            self::$javaScript[$key] = array();
        }
        self::$javaScript[$key][] = $javaScript;
    }

    /**
     * Gets the javaScript for a given key
     *
     * @param $key string
     *            The key
     *
     * @return string the javaScript
     */
    protected static function getJavaScript($key)
    {
        if (! empty(self::$javaScript[$key]) && is_array(self::$javaScript[$key])) {
            return implode(chr(10) . '    ', self::$javaScript[$key]);
        } else {
            return '';
        }
    }

    /**
     * Returns the javaScript Header
     *
     * @return string The javaScript Header
     */
    protected static function getJavaScriptHeader()
    {
        $javaScript = array();

        $javaScript[] = '';
        $javaScript[] = '  document.addEventListener(\'DOMContentLoaded\', init, false);';
        $javaScript[] = '  ' . self::getJavaScript('documentChanged');
        $javaScript[] = '  function checkIfRteChanged(x) {';
        $javaScript[] = '    if (RTEarea[x].editor.plugins.UndoRedo.instance.undoPosition>0) {';
        $javaScript[] = '      document.changed = TRUE;';
        $javaScript[] = '    }';
        $javaScript[] = '  }';
        $javaScript[] = '  function submitIfChanged(x) {';
        $javaScript[] = '    ' . self::getJavaScript('checkIfRteChanged');
        $javaScript[] = '    if (document.changed) {';
        $javaScript[] = '      if (confirm("' . FlashMessages::translate('warning.save') . '"))	{';
        $javaScript[] = '        update(x);';
        $javaScript[] = '        return false;';
        $javaScript[] = '      }';
        $javaScript[] = '      return true;';
        $javaScript[] = '    }';
        $javaScript[] = '    return true;';
        $javaScript[] = '  }';
        $javaScript[] = '  function update(x) {';
        $javaScript[] = '    ' . self::getJavaScript('rteUpdate');
        $javaScript[] = '    ' . self::getJavaScript('selectAll');
        $javaScript[] = '    return true;';
        $javaScript[] = '  }';
        $javaScript[] = '  function init() {';
        $javaScript[] = '    ' . self::getJavaScript('loadDoubleSelector');
        $javaScript[] = '  }';
        return implode(chr(10), $javaScript);
    }
}
?>