<?php

defined('TYPO3') or die();

(function () {
    // Loads the rte_ckeditor configuration
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rte_ckeditor')) {
        $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['sav_library_mvc'] = 'EXT:sav_library_mvc/Configuration/RTE/SavLibraryMvc.yaml';
    }

    // Registers the help node
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1565023070] = [
        'nodeName' => 'help',
        'priority' => 40,
        'class' => \YolfTypo3\SavLibraryMvc\Form\Element\Help::class
    ];

})();