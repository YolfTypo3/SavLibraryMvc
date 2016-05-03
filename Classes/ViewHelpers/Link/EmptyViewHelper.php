<?php

/*
 * This script is part of the TYPO3 project - inspiring people to share! *
 * *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by *
 * the Free Software Foundation. *
 * *
 * This script is distributed in the hope that it will be useful, but *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN- *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General *
 * Public License for more details. *
 */

/**
 * A view helper for creating anchors.
 *
 * = Examples =
 *
 * <code title="empty">
 * <f:link.empty key="test" />
 * </code>
 *
 * Output:
 *
 * @package SavLibraryMvc
 * @subpackage ViewHelpers
 */
class Tx_SavLibraryMvc_ViewHelpers_Link_EmptyViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper
{

    /**
     *
     * @param string $key
     *            target page. See TypoLink destination
     * @return string Rendered anchor
     * @author Bastian Waidelich <bastian@typo3.org>
     */
    public function render($key)
    {
        $output = '<a name="' . t3lib_div::md5int($key) . '"></a>';
        
        return $output;
    }
}

?>
