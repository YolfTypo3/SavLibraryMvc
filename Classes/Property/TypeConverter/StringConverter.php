<?php
namespace YolfTypo3\SavLibraryMvc\Property\TypeConverter;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
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
    protected $sourceTypes = array(
        'array'
    );

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
    public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL)
    {
        if (! is_array($source)) {
            return new \TYPO3\CMS\Extbase\Error\Error('"%s" is not an array.', 1332933658, array(
                $source
            ));
        } else {
            return \YolfTypo3\SavLibraryMvc\Utility\Conversion::stringArrayToCommaSeparatedString($source);
        }
    }
}
