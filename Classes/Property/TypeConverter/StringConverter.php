<?php

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

namespace YolfTypo3\SavLibraryMvc\Property\TypeConverter;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use YolfTypo3\SavLibraryMvc\Utility\Conversion;

/**
 * Converter which transforms simple types to a string.
 *
 * @api
 */
class StringConverter extends AbstractTypeConverter implements SingletonInterface
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
     * @var int
     */
    protected $priority = 2;

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param string $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     * @return string | Error @api
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        if (! is_array($source)) {
            return new Error('"%s" is not an array.', 1332933658, [
                $source
            ]);
        } else {
            return Conversion::stringArrayToCommaSeparatedString($source);
        }
    }
}
