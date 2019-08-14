<?php
namespace YolfTypo3\SavLibraryMvc\ViewHelpers\Typoscript;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Typoscript wrapper view helper.
 *
 *
 * @package SavLibraryMvc
 * @subpackage ViewHelpers
 */
class WrapViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('data', 'mixed', 'Data to be used for rendering the cObject. Can be an object, array or string', false, null);
        $this->registerArgument('configuration', 'string', 'Configuration', false, null);
    }

    /**
     * Renders the viewhelper
     *
     * @return string Rendered The wrapped content
     */
    public function render()
    {
        // Gets the arguments
        $data = $this->arguments['data'];
        $configuration = $this->arguments['configuration'];

        if ($data === null) {
            $data = html_entity_decode($this->renderChildren());
        }

        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        return $contentObject->dataWrap($data, $configuration);
    }
}

?>
