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

/**
 * Validator for DateTime objects
 */
class Tx_Extbase_Validation_Validator_EmptyValidator extends Tx_Extbase_Validation_Validator_AbstractValidator
{
    /**
     * Checks if the given value is null.
     *
     *
     * @param mixed $value
     *            The value that should be validated
     * @param array $validationOptions
     *            Not used
     * @return boolean true if the value is null, false otherwise
     */
    public function isEmpty($value)
    {
        $result = empty($value);
        return $result;
    }
}

?>
