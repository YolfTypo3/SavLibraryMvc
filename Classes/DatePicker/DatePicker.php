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

namespace YolfTypo3\SavLibraryMvc\DatePicker;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Exception;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * Date picker.
 */
final class DatePicker
{

    /**
     * The date picker path
     *
     * @var string
     */
    protected string $datePickerPath = 'Resources/Public/DatePicker/';

    /**
     * The date picker CSS file
     *
     * @var string
     */
    protected string $datePickerCssFile = 'calendar-win2k-2.css';

    /**
     * The javaScript file
     *
     * @var string
     */
    protected string $datePickerJsFile = 'calendar.js';

    protected string $datePickerJsSetupFile = 'calendar-setup.js';

    protected string $datePickerLanguageFile;

    /**
     * The controller
     *
     * @var AbstractController
     */
    protected AbstractController $controller;
    
    /**
     * Extension key
     *
     * @var string $extensionKey
     */
    protected string $extensionKey;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * 
     * @return void
     */
    public function __construct(RequestInterface $request)
    {
        // Gets the extension key
        $this->extensionKey = $request->getControllerExtensionKey();
        
        // Gets the langauege code
        $languageCode = $request->getAttribute('language')->getLocale()->getLanguageCode();
                
        $this->datePickerLanguageFile = 'calendar-' . $languageCode . '.js';
        $extensionWebPath = ExtensionManagementUtility::extPath(AbstractController::LIBRARY_NAME);
        $datePickerLanguagePath = $extensionWebPath . $this->datePickerPath . 'lang/';
        if (file_exists($datePickerLanguagePath . $this->datePickerLanguageFile) === false) {
            $this->datePickerLanguageFile = 'calendar-en.js';
        }

        $this->addCascadingStyleSheet();
        $this->addJavaScript();
    }

    /**
     * Adds the date picker css file
     * - from the datePicker.stylesheet TypoScript configuration if any
     * - else from the default css file
     *
     * @return void
     */
    protected function addCascadingStyleSheet(): void
    {
        $libraryName = AbstractController::LIBRARY_NAME;
        $key = 'datePicker.';
        $extensionTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($this->extensionKey);
        $datePickerTypoScriptConfiguration = $extensionTypoScriptConfiguration[$key] ?? null;
        // @extensionScannerIgnoreLine
        $stylesheet = $datePickerTypoScriptConfiguration['stylesheet'] ?? null;
        if (! empty($stylesheet)) {
            // The style sheet is given by the extension TypoScript
            $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($stylesheet);
            if (is_file($cascadingStyleSheetAbsoluteFileName)) {
                $cascadingStyleSheet = substr($cascadingStyleSheetAbsoluteFileName, strlen(AbstractController::getSitePath()));
                AdditionalHeaderManager::addCascadingStyleSheet($cascadingStyleSheet);
            } else {
                throw new Exception(FlashMessages::translate('error.fileDoesNotExist', [
                    htmlspecialchars($cascadingStyleSheetAbsoluteFileName)
                ]));
            }
        } else {
            $libraryTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($libraryName);
            $datePickerTypoScriptConfiguration = $libraryTypoScriptConfiguration[$key] ?? null;
            // @extensionScannerIgnoreLine
            $stylesheet = $datePickerTypoScriptConfiguration['stylesheet'] ?? null;
            if (empty($stylesheet) === false) {
                // The style sheet is given by the library TypoScript
                $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($stylesheet);
                if (is_file($cascadingStyleSheetAbsoluteFileName)) {
                    $cascadingStyleSheet = substr($cascadingStyleSheetAbsoluteFileName, strlen(AbstractController::getSitePath()));
                    AdditionalHeaderManager::addCascadingStyleSheet($cascadingStyleSheet);
                } else {
                    throw new Exception(FlashMessages::translate('error.fileDoesNotExist', [
                        htmlspecialchars($cascadingStyleSheetAbsoluteFileName)
                    ]));
                }
            } else {
                // The style sheet is the default one
                $cascadingStyleSheet = 'EXT:' . $libraryName . '/' . $this->datePickerPath . 'css/' . $this->datePickerCssFile;
                AdditionalHeaderManager::addCascadingStyleSheet($cascadingStyleSheet);
            }
        }
    }

    /**
     * Adds javascript
     *
     * @return void
     */
    public function addJavaScript(): void
    {
        $datePickerSiteRelativePath = 'EXT:' . AbstractController::LIBRARY_NAME . '/' . $this->datePickerPath;
        AdditionalHeaderManager::addJavaScriptFile($datePickerSiteRelativePath . 'js/' . $this->datePickerJsFile);
        AdditionalHeaderManager::addJavaScriptFile($datePickerSiteRelativePath . 'lang/' . $this->datePickerLanguageFile);
        AdditionalHeaderManager::addJavaScriptFile($datePickerSiteRelativePath . 'js/' . $this->datePickerJsSetupFile);
    }

    /**
     * Gets the date picker format
     *
     * @return array|null
     */
    protected function getDatePickerFormat(): ?array
    {
        $libraryName = AbstractController::LIBRARY_NAME;
        $key = 'datePicker.';
        $extensionTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($this->extensionKey);
        $datePickerTypoScriptConfiguration = $extensionTypoScriptConfiguration[$key] ?? null;
        if (is_array($datePickerTypoScriptConfiguration['dateFormat.'] ?? null)) {
            return $datePickerTypoScriptConfiguration['dateFormat.'];
        } else {
            $libraryTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($libraryName);
            $datePickerTypoScriptConfiguration = $libraryTypoScriptConfiguration[$key] ?? null;
            if (is_array($datePickerTypoScriptConfiguration['dateFormat.'] ?? null)) {
                return $datePickerTypoScriptConfiguration['dateFormat.'];
            }
        }
        return null;
    }

    /**
     * Renders the date picker
     *
     * @param array $datePickerConfiguration
     * @return string
     */
    public function render(array $datePickerConfiguration): string
    {
        $datePickerSetup = [];
        $datePickerSetup[] = '<a href="#">';
        $datePickerSetup[] = '<div id="button_' . $datePickerConfiguration['id'] . '">';
        $datePickerSetup[] = $datePickerConfiguration['icon'];
        $datePickerSetup[] = '</div>';
        $datePickerSetup[] = '</a>';
        $datePickerSetup[] = '<script type="text/javascript">';
        $datePickerSetup[] = '/*<![CDATA[*/';
        $datePickerSetup[] = '  Calendar.setup({';
        $datePickerSetup[] = '    inputField     :    "input_' . $datePickerConfiguration['id'] . '",';
        $datePickerSetup[] = '    ifFormat       :    "' . $datePickerConfiguration['dateFormat'] . '",';

        // Gets the date picker format
        $datePickerFormat = $this->getDatePickerFormat();
        if (empty($datePickerFormat['toolTipDate']) === false) {
            $datePickerSetup[] = '    ttFormat       :    "' . $datePickerFormat['toolTipDate'] . '",';
        }
        if (empty($datePickerFormat['titleBarDate']) === false) {
            $datePickerSetup[] = '    tbFormat       :    "' . $datePickerFormat['titleBarDate'] . '",';
        }
        $datePickerSetup[] = '    button         :    "button_' . $datePickerConfiguration['id'] . '",';
        $datePickerSetup[] = '    showsTime      :    ' . ($datePickerConfiguration['showsTime'] ? 'true' : 'false') . ',';
        $datePickerSetup[] = '    singleClick    :    true';
        $datePickerSetup[] = '  });';
        $datePickerSetup[] = '/*]]>*/';
        $datePickerSetup[] = '</script>';

        return implode(chr(10), $datePickerSetup);
    }
}
