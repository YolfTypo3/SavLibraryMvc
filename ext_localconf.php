<?php
defined('TYPO3_MODE') or die();

// Loads the rte_ckeditor configuration
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rte_ckeditor')) {
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['sav_library_mvc'] = 'EXT:sav_library_mvc/Configuration/RTE/SavLibraryMvc.yaml';
}

// Adds a hook to change the upload folder
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauthgroup.php']['getDefaultUploadFolder'][] = \YolfTypo3\SavLibraryMvc\Hooks\DefaultUploadFolder::class . '->getDefaultUploadFolder';

// Registers type converters
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\IntegerConverter::class);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\StringConverter::class);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\UploadedFileReferenceConverter::class);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\ObjectStorageConverter::class);

// Registers the help node
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1565023070] = [
    'nodeName' => 'help',
    'priority' => 40,
    'class' => \YolfTypo3\SavLibraryMvc\Form\Element\Help::class
];
?>
