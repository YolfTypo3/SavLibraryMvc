<?php

return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $configurator, \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder): void {

    if (file_exists('typo3conf/ext/sav_library_mvc/Configuration/')) {
        $configurationDirectory =  'typo3conf/ext/sav_library_mvc/Configuration/';
    } elseif (file_exists('../typo3conf/ext/sav_library_mvc/Configuration/')) {
        $configurationDirectory =  '../typo3conf/ext/sav_library_mvc/Configuration/';
    } else {
        die('configuration directory not found in sav_library_mvc');
    }
    $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version;
    $yamlFileLoader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader ($containerBuilder, new \Symfony\Component\Config\FileLocator($configurationDirectory));
    if (version_compare($typo3Version->getVersion(), '11.0', '>')) {
        $yamlFileLoader->load('ServicesV11.yaml');
    } else {
        $yamlFileLoader->load('ServicesV10.yaml');
    }
};
