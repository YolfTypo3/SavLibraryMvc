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

namespace YolfTypo3\SavLibraryMvc\Managers;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Exception;

/**
 * Additional header manager.
 */
class AdditionalHeaderManager
{

    /**
     * Controller
     *
     * @var DefaultController
     */
    protected static $controller = null;

    /**
     * Array of javaScript code used for the view
     *
     * @var array
     */
    protected static $javaScript = [];

    /**
     * Sets the controller
     *
     * @param DefaultController $controller
     * @return void
     */
    public static function setController(DefaultController $controller)
    {
        self::$controller = $controller;
    }

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
     * - else from the default css file which is in the "Css" directory of the SAV Library Mvc
     *
     * @return void
     */
    protected static function addLibraryCascadingStyleSheet()
    {
        $extensionKey = AbstractController::LIBRARY_NAME;
        $typoScriptConfiguration = AbstractController::getTypoScriptConfiguration($extensionKey);
        if (empty($typoScriptConfiguration['stylesheet'])) {
            $extensionWebPath = AbstractController::getExtensionWebPath($extensionKey);
            $cascadingStyleSheet = $extensionWebPath . AbstractController::$cssRootPath . '/' . $extensionKey . '.css';
            self::addCascadingStyleSheet($cascadingStyleSheet);
        } else {
            $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($typoScriptConfiguration['stylesheet']);
            if (is_file($cascadingStyleSheetAbsoluteFileName)) {
                $cascadingStyleSheet = substr($cascadingStyleSheetAbsoluteFileName, strlen(AbstractController::getSitePath()));
                self::addCascadingStyleSheet($cascadingStyleSheet);
            } else {
                throw new Exception(FlashMessages::translate('error.fileDoesNotExist', [
                    htmlspecialchars($cascadingStyleSheetAbsoluteFileName)
                ]));
            }
        }
    }

    /**
     * Adds the extension css file if any
     * The css file should be extension.css in the "Css" directory
     * where "extension" is the extension key
     *
     * @return void
     */
    protected static function addExtensionCascadingStyleSheet()
    {
        $extensionKey = self::$controller->getControllerExtensionKey();
        $typoScriptConfiguration = AbstractController::getTypoScriptConfiguration($extensionKey);
        if (empty($typoScriptConfiguration['stylesheet']) === false) {
            $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($typoScriptConfiguration['stylesheet']);
            if (is_file($cascadingStyleSheetAbsoluteFileName)) {
                $cascadingStyleSheet = substr($cascadingStyleSheetAbsoluteFileName, strlen(AbstractController::getSitePath()));
                self::addCascadingStyleSheet($cascadingStyleSheet);
            } else {
                throw new Exception(FlashMessages::translate('error.fileDoesNotExist', [
                    htmlspecialchars($cascadingStyleSheetAbsoluteFileName)
                ]));
            }
        } elseif (is_file(ExtensionManagementUtility::extPath($extensionKey) . AbstractController::$cssRootPath . '/' . $extensionKey . '.css')) {
            $extensionWebPath = AbstractController::getExtensionWebPath($extensionKey);
            $cascadingStyleSheet = $extensionWebPath . AbstractController::$cssRootPath . '/' . $extensionKey . '.css';
            self::addCascadingStyleSheet($cascadingStyleSheet);
        }
    }

    /**
     * Adds a cascading style Sheet
     *
     * @param string $cascadingStyleSheet
     * @return void
     */
    public static function addCascadingStyleSheet(string $cascadingStyleSheet)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addCssFile($cascadingStyleSheet);
    }

    /**
     * gets the cascading style Sheet link
     *
     * @param string $cascadingStyleSheet
     * @return string
     */
    protected static function getCascadingStyleSheetLink(string $cascadingStyleSheet): string
    {
        $cascadingStyleSheetLink = '<link rel="stylesheet" type="text/css" href="' . $cascadingStyleSheet . '" />' . chr(10);
        return $cascadingStyleSheetLink;
    }

    /**
     * Adds a javaScript file
     *
     * @param string $javaScriptFileName
     * @return void
     */
    public static function addJavaScriptFile(string $javaScriptFileName)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFile($javaScriptFileName);
    }

    /**
     * Adds a javaScript inline code
     *
     * @param string $key
     * @param string $javaScriptFileName
     *
     * @return void
     */
    public static function addJavaScriptInlineCode(string $key, string $javaScriptInlineCode)
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
    public static function addInlineSettingArray(string $namespace, array $array)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addInlineSettingArray($namespace, $array);
    }

    /**
     * Adds a javaScript footer file
     *
     * @param string $javaScriptFileName
     *
     * @return void
     */
    public static function addJavaScriptFooterFile(string $javaScriptFileName)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFooterFile($javaScriptFileName);
    }

    /**
     * Adds a javaScript footer inline code
     *
     * @param string $key
     * @param string $javaScriptFileName
     *
     * @return void
     */
    public static function addJavaScriptFooterInlineCode(string $key, string $javaScriptInlineCode)
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addJsFooterInlineCode($key, $javaScriptInlineCode);
    }

    /**
     * Adds the javaScript header
     *
     * @return void
     */
    public static function addAdditionalJavaScriptHeader()
    {
        if (count(self::$javaScript) > 0) {
            if (count(self::$javaScript['selectAll']) > 0) {
                $extensionWebPath = AbstractController::getExtensionWebPath(AbstractController::LIBRARY_NAME);
                $javaScriptFileName = $extensionWebPath . AbstractController::$javaScriptRootPath . '/' . AbstractController::LIBRARY_NAME . '.js';
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
     * @param $javaScript string
     *            The javaScript
     * @return void
     */
    public static function addJavaScript(string $key, string $javaScript = null)
    {
        if (! is_array(self::$javaScript[$key])) {
            self::$javaScript[$key] = [];
        }
        self::$javaScript[$key][] = $javaScript;
    }

    /**
     * Gets the javaScript for a given key
     *
     * @param $key string
     *            The key
     * @return string the javaScript
     */
    protected static function getJavaScript(string $key): string
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
    protected static function getJavaScriptHeader(): string
    {
        $javaScript = [];

        $javaScript[] = '';
        $javaScript[] = '  document.addEventListener(\'DOMContentLoaded\', init, false);';
        $javaScript[] = '  ' . self::getJavaScript('documentChanged');
        $javaScript[] = '  function checkIfRteChanged(x) {';
        $javaScript[] = '    if (RTEarea[x].editor.plugins.UndoRedo.instance.undoPosition>0) {';
        $javaScript[] = '      document.changed = true;';
        $javaScript[] = '    }';
        $javaScript[] = '  }';
        $javaScript[] = '  function submitIfChanged(x) {';
        $javaScript[] = '    ' . self::getJavaScript('checkIfRteChanged');
        $javaScript[] = '    if (document.changed) {';
        $javaScript[] = '      if (confirm("' . FlashMessages::translate('warning.save') . '"))	{';
        $javaScript[] = '        update(x);';
        $javaScript[] = '        document.getElementById(\'id_\' + x).submit();';
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

    /**
     * Adds the javaScript to confirm delete action
     *
     * @param string $className
     *
     * @return void
     */
    public static function addConfirmDeleteJavaScript($className)
    {
        $javaScript = [];

        $javaScript[] = '  function confirmDelete() {';
        $javaScript[] = '    document.activeElement.closest(".' . $className . '").classList.add("deleteWarning");';
        $javaScript[] = '    if (confirm("' . FlashMessages::translate('warning.delete') . '"))	{';
        $javaScript[] = '      return true;';
        $javaScript[] = '    }';
        $javaScript[] = '    document.activeElement.closest(".' . $className . '").classList.remove("deleteWarning");';
        $javaScript[] = '    return false;';
        $javaScript[] = '  }';

        self::addJavaScriptFooterInlineCode('confirmDelete',implode(chr(10), $javaScript));
    }

}
