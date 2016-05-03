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
 * Returns an array of numbers
 *
 * @package SavLibraryMvc
 * @version $Id:
 */
class RangeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Range viewhelper.
     *
     * @param integer $low
     *            The low value
     * @param integer $high
     *            The high value
     * @param integer $step
     *            The step value
     * @return array The range array
     * @author Laurent Foulloy <yolf.typo3@oranage.fr>
     *         @api
     */
    public function render($low, $high, $step = 1)
    {
        return range($low, $high, $step);
    }
}
?>
