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
 * View helper which builds the src attribute for an icon
 *
 * @package SavLibraryMvc
 */
class GetIconSrcViewHelper extends AbstractViewHelper
{

    /**
     * Initializes arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('fileName', 'string', 'File name', true);
    }

    /**
     * Renders the content.
     *
     * @return string Rendered string
     */
    public function render()
    {
        // Gets the arguments
        $fileName = $this->arguments['fileName'];

        // Checks if the file Name exists in the SAV Library Mvc
        $filePath = AbstractController::getIconPath($fileName);

        if (file_exists($filePath)) {
            return $filePath;
        } else {
            return null;
        }
    }
}

?>
