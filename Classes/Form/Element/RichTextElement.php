<?php
namespace SAV\SavLibraryMvc\Form\Element;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Lang\LanguageService;


/**
 * Render rich text editor in FormEngine
 */
class RichTextElement extends \TYPO3\CMS\Rtehtmlarea\Form\Element\RichTextElement
{

    /**
     * Checks if frontend editing is active.
     *
     * @return bool TRUE if frontend editing is active
     */
    protected function isFrontendEditActive()
    {
        return is_object($GLOBALS['TSFE']) && $GLOBALS['TSFE']->fe_user->user['uid'] > 0;
    }

    /**
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        $GLOBALS['LANG']->init($GLOBALS['BE_USER']->uc['lang']);
        return $GLOBALS['LANG'];
    }

    /**
     *
     * @return BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return GeneralUtility::makeInstance(BackendUserAuthentication::class);
    }
}
