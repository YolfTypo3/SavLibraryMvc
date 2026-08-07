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
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace YolfTypo3\SavLibraryMvc\ViewHelpers\Typoscript;

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
final class WrapViewHelper extends AbstractViewHelper
{
    
    /**
     * Initializes arguments.
     * 
     * @return void
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('data', 'mixed', 'Data to be used for rendering the cObject. Can be an object, array or string', false, null);
        $this->registerArgument('configuration', 'string', 'Configuration', false, null);
    }

    /**
     * Renders the view helper
     *
     * @return string
     */
    public function render(): string
    {
        // Gets the arguments
        $data = $this->arguments['data'];
        $configuration = $this->arguments['configuration'];

        if ($data === null) {
            $data = html_entity_decode($this->renderChildren());
        }
        if (!empty($configuration)) {
            /** @var ContentObjectRenderer $contentObject */
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            return $contentObject->dataWrap($data, $configuration);
        } else {
            return $data;
        }
    }
}
