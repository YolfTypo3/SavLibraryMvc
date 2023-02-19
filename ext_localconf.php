<?php
defined('TYPO3') or die();

(function () {
    // Loads the rte_ckeditor configuration
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rte_ckeditor')) {
        $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['sav_library_mvc'] = 'EXT:sav_library_mvc/Configuration/RTE/SavLibraryMvc.yaml';
    }

    // Registers type converters
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\IntegerConverter::class);
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\StringConverter::class);
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\FileReferenceConverter::class);

    // Registers the help node
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1565023070] = [
        'nodeName' => 'help',
        'priority' => 40,
        'class' => \YolfTypo3\SavLibraryMvc\Form\Element\Help::class
    ];

    $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version;
    if (version_compare($typo3Version->getVersion(), '11.5', '<')) {
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            'radio-button-on',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:sav_library_mvc/Resources/Public/Icons/radio-button-on.svg']
        );
        $iconRegistry->registerIcon(
            'radio-button-off',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:sav_library_mvc/Resources/Public/Icons/radio-button-off.svg']
        );
        $iconRegistry->registerIcon(
            'checkbox-checked',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:sav_library_mvc/Resources/Public/Icons/checkbox-checked.svg']
        );
        $iconRegistry->registerIcon(
            'checkbox-empty',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:sav_library_mvc/Resources/Public/Icons/checkbox-empty.svg']
        );
    }

})();