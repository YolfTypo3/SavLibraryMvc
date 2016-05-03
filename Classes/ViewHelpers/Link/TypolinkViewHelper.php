<?php
namespace SAV\SavLibraryMvc\ViewHelpers\Link;

/*
 * This script is part of the TYPO3 project - inspiring people to share! *
 * *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by *
 * the Free Software Foundation. *
 * *
 * This script is distributed in the hope that it will be useful, but *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN- *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General *
 * Public License for more details. *
 */

/**
 * A view helper for creating a typolink.
 *
 *
 * @package SavLibraryMvc
 */
class TypolinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     *
     * @param mixed $data
     *            the data to be used for rendering the cObject. Can be an object, array or string. If this argument is not set, child nodes will be used
     * @param string $parameter            
     * @return string Rendered The link
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     */
    public function render($data = NULL, $parameter = '')
    {
        if ($data === NULL) {
            $data = $this->renderChildren();
        }
        
        $contentObject = $this->objectManager->get('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
        
        return $contentObject->typoLink($data, array(
            'parameter' => $parameter
        ));
    }
}

?>
