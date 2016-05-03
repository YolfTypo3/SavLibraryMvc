<?php
namespace SAV\SavLibraryMvc\ViewHelpers\Form;

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
 * A view helper to add the required javascript for the form .
 *
 *
 *
 *
 * = Examples =
 *
 * <code title="AdditionalJavaScriptHeader">
 * <sav:AdditionalJavaScriptHeader />
 * </code>
 *
 * Output:
 * None
 *
 * @package SavLibraryMvc
 * @version $Id:
 */
class AdditionalJavaScriptHeaderViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     *
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     *         @api
     */
    public function render()
    {
        \SAV\SavLibraryMvc\Managers\AdditionalHeaderManager::addAdditionalJavaScriptHeader();
    }
}
?>

