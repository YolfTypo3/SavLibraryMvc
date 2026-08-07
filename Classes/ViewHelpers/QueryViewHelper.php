<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;

/**
 * Class QueryViewHelper
 *
 * This view helper can be used in templates
 * for exporting data
 */
final class QueryViewHelper extends AbstractViewHelper
{
    
    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('name', 'string', 'Name of variable to create', true);
        $this->registerArgument('statement', 'string', 'The statement', false);
        $this->registerArgument('debug', 'bool', 'Debug flag', false, false);
    }

    /**
     * Renders the view helper
     * 
     * @return void
     */
    public function render(): void
    {
        $name = $this->arguments['name'];
        $statement = $this->arguments['statement'];
        $debug = $this->arguments['debug'];

        if ($statement === null) {
            $statement = $this->renderChildren();
        }

        // Gets the controller information
        $request = $this->renderingContext->getRequest();
        $controller = $request->getAttribute('controller');

        $userIsAllowedToExportData = $controller->getFrontendUserManager()
            ->userIsAllowedToExportData();

        // Checks if the user is allowed to export data
        if ($userIsAllowedToExportData) {

            // Gets the main repository
            $mainRepository = $controller->getMainRepository();

            $query = $mainRepository->createQuery();
            $result = $query->statement($statement)->execute(true);

            if ($debug) {
                debug($result);
            }

            $this->renderingContext->getVariableProvider()->add($name, $result);
        } else {
            $this->renderingContext->getVariableProvider()->add($name, []);
            FlashMessages::addMessageOnce('error.notAllowedToUseQueryViewHelper');
        }
    }

}
