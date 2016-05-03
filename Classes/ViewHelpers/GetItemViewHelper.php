<?php
namespace SAV\SavLibraryMvc\ViewHelpers;

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
 * Returns an item in an array
 */
class GetItemViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * viewhelper.
     *
     * @param array $value
     *            The value of the parameter to change
     * @param string $key
     *            The key of the parameter to change
     * @param int $offset
     *            An offset for the key
     * @return string The modified compressed parameters
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     *         @api
     */
    public function render($value = NULL, $key = NULL, $offset = 0)
    {
        if ($value === NULL) {
            $value = $this->renderChildren();
        }
        return $value[$key + $offset];
    }
}
?>
