<?php

declare(strict_types=1);

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

namespace YolfTypo3\SavLibraryMvc\Utility;

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
     * @return int Integer representing the array of booleans
     */
    static public function booleanArrayToInteger(array $booleanArray): int
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
     * @param int $integer
     *            Integer to convert
     * @return array Array of booleans
     */
    static public function integerToBooleanArray(int $integer): array
    {
        $result = [];
        while ($integer) {
            $result[] = (bool) ($integer % 2);
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
    static public function stringArrayToCommaSeparatedString(array $stringArray): string
    {
        return implode(',', $stringArray);
    }

    /**
     * Transforms a comma-separated string into an array of string
     *
     * @param string $commaSeparatedString
     * @return array Array of string
     */
    static public function commaSeparatedStringToStringArray(string $commaSeparatedString): array
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
    static public function upperCamel(string $string): string
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
    static public function lowerCamel(string $string): string
    {
        $output = self::upperCamel($string);
        return lcfirst($output);
    }
}
