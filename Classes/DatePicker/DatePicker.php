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
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;
use YolfTypo3\SavLibraryMvc\Controller\DefaultController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Exception;

/**
 * Date picker.
 */
final class DatePicker
{

    // Constants
    const KEY = 'datePicker';

    /**
     * The date picker path
     *
     * @var string
     */
    protected $datePickerPath = 'Classes/DatePicker/';

    /**
     * The date picker CSS file
     *
     * @var string
     */
    protected $datePickerCssFile = 'calendar-win2k-2.css';

    /**
     * The javaScript file
     *
     * @var string
     */
    protected $datePickerJsFile = 'calendar.js';

    protected $datePickerJsSetupFile = 'calendar-setup.js';

    protected $datePickerLanguageFile;

    /**
     * Extension key
     *
     * @var string $extensionKey
     */
    protected $extensionKey;

    /**
     * Constructor
     *
     * @param string $extensionKey
     * @return void
     */
    public function __construct(string $extensionKey)
    {
        $this->extensionKey = $extensionKey;
        $this->datePickerLanguageFile = 'calendar-' . $GLOBALS['TSFE']->config['config']['language'] . '.js';
        $extensionWebPath = AbstractController::getExtensionWebPath(AbstractController::LIBRARY_NAME);
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
    protected function addCascadingStyleSheet()
    {
        $libraryName = AbstractController::LIBRARY_NAME;
        $key = self::KEY . '.';
        $extensionTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($this->extensionKey);
        $datePickerTypoScriptConfiguration = $extensionTypoScriptConfiguration[$key];
        if (! empty($datePickerTypoScriptConfiguration['stylesheet'])) {
            // The style sheet is given by the extension TypoScript
            $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($datePickerTypoScriptConfiguration['stylesheet']);
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
            $datePickerTypoScriptConfiguration = $libraryTypoScriptConfiguration[$key];
            if (empty($datePickerTypoScriptConfiguration['stylesheet']) === false) {
                // The style sheet is given by the library TypoScript
                $cascadingStyleSheetAbsoluteFileName = GeneralUtility::getFileAbsFileName($datePickerTypoScriptConfiguration['stylesheet']);
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
                $extensionWebPath = AbstractController::getExtensionWebPath($libraryName);
                $cascadingStyleSheet = $extensionWebPath . $this->datePickerPath . 'css/' . $this->datePickerCssFile;
                AdditionalHeaderManager::addCascadingStyleSheet($cascadingStyleSheet);
            }
        }
    }

    /**
     * Adds javascript
     *
     * @return void
     */
    public function addJavaScript()
    {
        $extensionWebPath = AbstractController::getExtensionWebPath(AbstractController::LIBRARY_NAME);
        $datePickerSiteRelativePath = $extensionWebPath . $this->datePickerPath;
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
        $key = self::KEY . '.';
        $extensionTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($this->extensionKey);
        $datePickerTypoScriptConfiguration = $extensionTypoScriptConfiguration[$key];
        if (is_array($datePickerTypoScriptConfiguration['format.'])) {
            return $datePickerTypoScriptConfiguration['format.'];
        } else {
            $libraryTypoScriptConfiguration = AbstractController::getTypoScriptConfiguration($libraryName);
            $datePickerTypoScriptConfiguration = $libraryTypoScriptConfiguration[$key];
            if (is_array($datePickerTypoScriptConfiguration['format.'])) {
                return $datePickerTypoScriptConfiguration['format.'];
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
        $datePickerSetup[] = '    ifFormat       :    "' . $datePickerConfiguration['format'] . '",';

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
