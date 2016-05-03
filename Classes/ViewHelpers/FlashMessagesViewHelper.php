<?php
namespace SAV\SavLibraryMvc\ViewHelpers;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * View helper compatible with TYPO3 6 and 7 which renders the flash messages
 *
 * @package SavLibraryMvc
 * @version $Id:
 */
class FlashMessagesViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FlashMessagesViewHelper
{
    const RENDER_MODE_UL = 'ul';

    /**
     * Render the flashmessages.
     *
     * @param string $renderMode
     *            one of the RENDER_MODE_* constants
     * @param string $as
     *            The name of the current flashMessage variable for rendering inside
     * @return string rendered Flash Messages, if there are any.
     */
    public function render($renderMode = self::RENDER_MODE_UL, $as = null)
    {
        $this->arguments['queueIdentifier'] = 'core.template.flashMessages';
        return parent::render($renderMode);
    }
}
?>
