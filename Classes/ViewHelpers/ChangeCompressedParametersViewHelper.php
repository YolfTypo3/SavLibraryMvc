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
class ChangeCompressedParametersViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('arguments', 'array', 'Arguments', false, null);
    }

    /**
     * Renders the viewhelper.
     *
     * @return string The modified compressed parameters
     */
    public function render()
    {
        // Gets the arguments
        $arguments = $this->arguments['arguments'];

        if ($arguments === null) {
            $arguments = $this->renderChildren();
        }

        // Gets and changes the special parameter
        $special = $this->templateVariableContainer->get('general')['special'];
        $special = AbstractController::changeCompressedParameters($special, $arguments);

        return $special;
    }
}
