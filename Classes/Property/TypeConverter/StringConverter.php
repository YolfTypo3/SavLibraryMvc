<?php
namespace YolfTypo3\SavLibraryMvc\Property\TypeConverter;

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

/**
 * Converter which transforms simple types to a string.
 *
 * @api
 */
class StringConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     *
     * @var array<string>
     */
    protected $sourceTypes = [
        'array'
    ];

    /**
     *
     * @var string
     */
    protected $targetType = 'string';

    /**
     *
     * @var integer
     */
    protected $priority = 2;

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param string $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     * @return string @api
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = null)
    {
        if (! is_array($source)) {
            return new \TYPO3\CMS\Extbase\Error\Error(
                '"%s" is not an array.',
                1332933658,
                [
                    $source
                ]
            );
        } else {
            return \YolfTypo3\SavLibraryMvc\Utility\Conversion::stringArrayToCommaSeparatedString($source);
        }
    }
}
