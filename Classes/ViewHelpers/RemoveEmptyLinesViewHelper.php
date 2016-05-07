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
class RemoveEmptyLinesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Remove empty lines
     *
     * @param string $value string to format
     * @return string The altered string.
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     */
    public function render($value = NULL)
    {

        if ($value === NULL) {
            $value = $this->renderChildren();
        }

        $value = preg_replace('/([ \t]*[\r\n]){2,}/', chr(10), $value);
        return $value;
    }




}
?>