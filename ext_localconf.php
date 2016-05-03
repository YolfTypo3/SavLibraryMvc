<?php
if (! defined('TYPO3_MODE'))
    die('Access denied.');

// Makes the extension version number available to the extension scripts
require (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'ext_emconf.php');
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['version'] = $EM_CONF[$_EXTKEY]['version'];

// Register FormEngine node type resolver hook to render RTE in FormEngine if enabled
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1450181820] = array(
    'nodeName' => 'text',
    'priority' => 50,
    'class' => \SAV\SavLibraryMvc\Form\Resolver\RichTextNodeResolver::class,
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('SAV\\SavLibraryMvc\\Property\\TypeConverter\\IntegerConverter');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('SAV\\SavLibraryMvc\\Property\\TypeConverter\\StringConverter');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('SAV\\SavLibraryMvc\\Property\\TypeConverter\\UploadedFileReferenceConverter');
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('SAV\\SavLibraryMvc\\Property\\TypeConverter\\ObjectStorageConverter');
?>
