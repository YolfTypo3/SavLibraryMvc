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
use SAV\SavLibraryMvc\Controller\AbstractController;

/**
 * Changes a compressed parameter a string
 */
class ChangePageInSubformViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Renders the viewhelper
     *
     * @param array $arguments            
     * @return string The modified compressed parameters
     */
    public function render($arguments)
    {
        
        // Gets the special parameter from the controller arguments
        $controllerArguments = $this->controllerContext->getRequest()->getArguments();
        $special = $controllerArguments['special'];
        
        // Gets the uncompressed subform active pages
        $uncompressedParameters = AbstractController::uncompressParameters($special);
        $compressedSubformActivePages = $uncompressedParameters['subformActivePages'];
        $subformKey = $arguments['subformKey'];
        $uncompressedSubformActivePages = AbstractController::uncompressSubformActivePages($compressedSubformActivePages);
        
        // Processes the different cases
        switch ($arguments['action']) {
            case 'firstPage':
                $uncompressedSubformActivePages[$subformKey] = 0;
                break;
            case 'lastPage':
                $uncompressedSubformActivePages[$subformKey] = $arguments['lastPageInSubform'];
                break;
            case 'previousPage':
                $uncompressedSubformActivePages[$subformKey] = $uncompressedSubformActivePages[$subformKey] - 1;
                break;
            case 'nextPage':
                $uncompressedSubformActivePages[$subformKey] = $uncompressedSubformActivePages[$subformKey] + 1;
                break;
            case 'changePage':
                $uncompressedSubformActivePages[$subformKey] = $arguments['subformPage'];
                break;
        }
        
        // Compresses the subform active pages
        $compressedSubformActivePages = '';
        foreach ($uncompressedSubformActivePages as $subformKey => $subformPage) {
            $compressedSubformActivePages .= AbstractController::compressParameters(array(
                'subformKey' => $subformKey,
                'subformPage' => $subformPage
            ));
        }
        
        // Modifies the special parameter with the new value
        $special = AbstractController::changeCompressedParameters($special, array(
            'subformActivePages' => $compressedSubformActivePages
        ));
        
        return $special;
    }
}
?>
