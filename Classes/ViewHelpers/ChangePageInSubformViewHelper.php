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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Changes a compressed parameter a string
 *
 * @package SavLibraryMvc
 */
class ChangePageInSubformViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('arguments', 'array', 'Arguments', true);
    }

    /**
     * Renders the viewhelper
     *
     * @return string The modified compressed parameters
     */
    public function render()
    {
        // Gets the arguments
        $arguments = $this->arguments['arguments'];

        // Gets the special parameter
        $special = $this->templateVariableContainer->get('general')['special'];

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
            $compressedSubformActivePages .= AbstractController::compressParameters([
                'subformKey' => $subformKey,
                'subformPage' => $subformPage
            ]);
        }

        // Modifies the special parameter with the new value
        $special = AbstractController::changeCompressedParameters($special, [
            'subformActivePages' => $compressedSubformActivePages
        ]);

        return $special;
    }
}
