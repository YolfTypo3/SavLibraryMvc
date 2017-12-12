<?php
if (! defined('TYPO3_MODE')) {
    die('Access denied.');
}

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


        $cshTag = lcfirst($cshTag);
        $languageService = $GLOBALS['LANG'];
        $message = $languageService->sL('LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:extensionFlexform.help');
        $moduleToken = \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get()->generateToken('moduleCall', 'help_CshmanualCshmanual');
        $helpUrl = 'index.php?M=help_CshmanualCshmanual&moduleToken=' . $moduleToken . '&tx_cshmanual_help_cshmanualcshmanual[controller]=Help&tx_cshmanual_help_cshmanualcshmanual[action]=detail&';

        $iconSrcAttribute = 'src="../typo3conf/ext/' . $extensionKey . '/Resources/Public/Icons/helpbubble.gif"';
        $icon = '<img ' . $iconSrcAttribute . ' class="typo3-csh-icon" alt="' . $cshTag . '" />';

        return '<a href="#" onclick="vHWin=window.open(\'' . $helpUrl . 'tx_cshmanual_help_cshmanualcshmanual[table]=xEXT_' . $extensionKey . ($cshTag ? '_' . $cshTag : '') . '\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return FALSE;">' . $icon . ' ' . $message . '</a>';
    }
}
?>
