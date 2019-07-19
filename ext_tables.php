<?php
defined('TYPO3_MODE') or die();

// Adds user function for help in flexforms for extension depending on the SAV Library Mvc
if (! function_exists('user_savlibraryMvcHelp')) {

    function user_savlibraryMvcHelp($PA, $fobj)
    {
        if (is_array($PA['fieldConf']['config']['userFuncParameters']) && ! empty($PA['fieldConf']['config']['userFuncParameters']['extensionKey'])) {
            $extensionKey = $PA['fieldConf']['config']['userFuncParameters']['extensionKey'];
        } else {
            $extensionKey = 'sav_library_mvc';
        }
        $cshTag = $PA['fieldConf']['config']['userFuncParameters']['cshTag'];

        $languageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Lang\LanguageService::class);
        $message = '<b>' . $languageService->sL('LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:extensionFlexform.help') . '</b>';

        return \TYPO3\CMS\Backend\Utility\BackendUtility::cshItem('xEXT_' . $extensionKey . '_' . $cshTag, '', '', $message . '|');
    }
}

// Context sensitive tags
$contextSensitiveHelpFiles = [
    'helpGeneral' => 'locallang_csh_flexform_helpGeneral',
    'helpInputControls' => 'locallang_csh_flexform_helpInputControls',
    'helpAdvanced' => 'locallang_csh_flexform_helpAdvanced',
];

// Sets the Context Sensitive Help
foreach ($contextSensitiveHelpFiles as $contextSensitiveHelpFileKey => $contextSensitiveHelpFile) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'xEXT_sav_library_mvc_' . $contextSensitiveHelpFileKey,
        'EXT:sav_library_mvc/Resources/Private/Language/ContextSensitiveHelp/' . $contextSensitiveHelpFile . '.xlf'
    );

}
?>
