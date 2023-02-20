<?php

return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $configurator, \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder): void {

    $configurationDirectory =  \TYPO3\CMS\Core\Core\Environment::getExtensionsPath() . '/sav_library_mvc/Configuration/';
    if (! file_exists($configurationDirectory)) {
        throw new \Exception('Configuration directory not found in sav_library_mvc.');
    } else {
        $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version;
        $yamlFileLoader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader ($containerBuilder, new \Symfony\Component\Config\FileLocator($configurationDirectory));
        if (version_compare($typo3Version->getVersion(), '11.0', '>')) {
            $yamlFileLoader->load('ServicesV11.yaml');
        } else {
            $yamlFileLoader->load('ServicesV10.yaml');
        }
    }
};
