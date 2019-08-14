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
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * Changes a compressed parameter a string
 *
 * @package SavLibraryMvc
 */
class ChangePageViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('arguments', 'array', 'Arguments', false, null);
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

        // Gets the special parameter from the controller arguments
        $controllerArguments = $this->renderingContext->getControllerContext()
            ->getRequest()
            ->getArguments();
        $special = $controllerArguments['special'];

        // Gets the uncompressed subform active pages
        $uncompressedParameters = AbstractController::uncompressParameters($special);

        // Processes the different cases
        switch ($arguments['action']) {
            case 'firstPage':
                $page = 0;
                break;
            case 'lastPage':
                $page = $arguments['lastPage'];
                break;
            case 'previousPage':
                $page = $uncompressedParameters['page'] - 1;
                break;
            case 'nextPage':
                $page = $uncompressedParameters['page'] + 1;
                break;
            case 'changePage':
                $page = $arguments['page'];
                break;
        }

        // Modifies the special parameter with the new value
        $special = AbstractController::changeCompressedParameters($special, [
            'page' => $page
        ]);

        return $special;
    }
}
?>
