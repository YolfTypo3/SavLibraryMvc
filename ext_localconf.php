<?php
if (! defined('TYPO3_MODE'))
    die('Access denied.');

// Loads the rte_ckeditor configuration
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rte_ckeditor')) {
    $GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets'][$_EXTKEY] = 'EXT:'. $_EXTKEY . '/Configuration/RTE/SavLibraryMvc.yaml';
}

// Makes the extension version number available to the extension scripts
require (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'ext_emconf.php');
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['version'] = $EM_CONF[$_EXTKEY]['version'];

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\IntegerConverter::class);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\StringConverter::class);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\UploadedFileReferenceConverter::class);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(\YolfTypo3\SavLibraryMvc\Property\TypeConverter\ObjectStorageConverter::class);
?>
