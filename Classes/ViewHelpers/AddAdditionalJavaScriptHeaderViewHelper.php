<?php
namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

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

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * A view helper for rendering a the additional JavaScript header.
 *
 * = Examples =
 *
 * <code title="addAdditionalJavaScriptHeader">
 * <sav:addAdditionalJavaScriptHeader />
 * </code>
 *
 * Output:
 * none
 *
 * @package SavLibraryMvc
 */
class AddAdditionalJavaScriptHeaderViewHelper extends AbstractViewHelper
{
    /**
     * Renders the viewhelper
     *
     * @return array the options array
     */
    public function render()
    {
        AdditionalHeaderManager::addAdditionalJavaScriptHeader();
    }
}
?>

