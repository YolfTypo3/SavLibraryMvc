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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;

/**
 * A view helper for rendering a selectorbox.
 *
 * = Examples =
 *
 * <code title="RenderSelectorbox">
 * <sav:RenderSelectorbox />
 * </code>
 *
 * Output:
 * the options
 *
 * @package SavLibraryMvc
 */
class RenderSelectorboxViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('fields', 'array', 'Fields', false, null);
    }

    /**
     * Renders the viewhelper
     *
     * @return array the options array
     */
    public function render()
    {
        // Gets the arguments
        $fields = $this->arguments['fields'];

        if ($fields === null) {
            $fields = $this->renderChildren();
        }

        $options = [];

        $extensionKey = AbstractController::getControllerExtensionKey();
        foreach ($fields as $field) {
            $options[$field[1]] = LocalizationUtility::translate($field[0], $extensionKey);
        }

        return $options;
    }
}
?>

