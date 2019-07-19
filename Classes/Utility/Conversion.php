<?php
namespace YolfTypo3\SavLibraryMvc\Utility;

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
 * Utilities for converting variables
 */
class Conversion
{
    /**
     * Transforms an array of booleans (0 or 1) into an integer
     *
     * @param array $booleanArray
     *            Array of booleans
     * @return integer Integer representing the array of booleans
     */
    static public function booleanArrayToInteger($booleanArray)
    {
        $result = 0;
        foreach ($booleanArray as $key => $value) {
            if ($value) {
                $result = $result + (1 << $key);
            }
        }
        return $result;
    }

    /**
     * Transforms an integer to an array of booleans
     *
     * @param integer $integer
     *            Integer to convert
     * @return array Array of booleans
     */
    static public function integerToBooleanArray($integer)
    {
        $result = [];
        while ($integer) {
            $result[] = $integer % 2;
            $integer = (int) ($integer / 2);
        }
        return $result;
    }

    /**
     * Transforms an array of strings into a comma-separated string
     *
     * @param array $stringArray
     *            Array of string
     * @return string The comma_separaated string
     */
    static public function stringArrayToCommaSeparatedString($stringArray)
    {
        return implode(',', $stringArray);
    }

    /**
     * Transforms a comma-separated string into an array of string
     *
     * @param array $integer
     *            Integer to convert
     * @return array Array of booleans
     */
    static public function commaSeparatedStringToStringArray($commaSeparatedString)
    {
        return explode(',', $commaSeparatedString);
    }

    /**
     * Converts a string to upperCamel
     *
     * @param string $string
     *            The string to convert
     * @return string The string in upper Camel case
     */
    static public function upperCamel($string)
    {
        $string = str_replace(' ', '_', $string);
        $parts = explode('_', $string);
        foreach ($parts as $part) {
            $output .= ucfirst($part);
        }
        return $output;
    }

    /**
     * Converts a string to lowerCamel
     *
     * @param string $string
     *            The string to convert
     * @return string The string in lower Camel case
     */
    static public function lowerCamel($string)
    {
        $output = self::upperCamel($string);
        if (function_exists('lcfirst')) {
            return lcfirst($output);
        } else {
            $output[0] = strtolower($output[0]);
            return $output;
        }
    }
}

?>
