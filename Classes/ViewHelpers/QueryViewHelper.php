<?php

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;

/**
 * Class QueryViewHelper
 *
 * This view helper can be used in templates
 * for exporting data
 */
class QueryViewHelper extends AbstractViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;
    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name of variable to create', true);
        $this->registerArgument('statement', 'string', 'The statement', false);
        $this->registerArgument('debug', 'bool', 'Debug flag', false, false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
            $name = $arguments['name'];
            $statement = $arguments['statement'];
            $debug = $arguments['debug'];

            if ($statement === null) {
                $statement = $renderChildrenClosure();
            }

            // Gets the controller
            if (method_exists(__CLASS__, 'getRequest')) {
                $request = $renderingContext->getRequest();
            } else {
                // For TYPO3 v10
                // @extensionScannerIgnoreLine
                $request =  $renderingContext
                ->getControllerContext()
                ->getRequest();
            }
            $controllerObjectName = $request->getOriginalRequest()
                ->getControllerObjectName();
            $controller = GeneralUtility::makeInstance($controllerObjectName);

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

                $renderingContext->getVariableProvider()->add($name, $result);
            } else {
                $renderingContext->getVariableProvider()->add($name, []);
                FlashMessages::addMessageOnce('error.notAllowedToUseQueryViewHelper');
            }
        }

}
