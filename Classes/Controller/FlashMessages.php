<?php
namespace SAV\SavLibraryMvc\Controller;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Flash messages.
 */
class FlashMessages
{

    /**
     *
     * @var array
     */
    protected static $errorRegisteredKeys = array();

    /**
     *
     * @var array
     */
    protected static $messageRegisteredKeys = array();

    /**
     * Adds a message either to the BE_USER session (if the $message has the storeInSession flag set)
     * or it adds the message to self::$messages.
     *
     * @param object $message
     *            Message
     * @return void
     */
    protected static function addMessageToQueue($flashMessage)
    {
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageService->getMessageQueueByIdentifier()->enqueue($flashMessage);
    }

    /**
     * Returns all messages from the current PHP session and from the current request.
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            Arguments associated with the translation of the message key
     * @param const $severity
     *            The message severity
     *
     * @return array object
     */
    protected static function createFlashMessage($key, $arguments, $severity)
    {
        return GeneralUtility::makeInstance(FlashMessage::class, self::translate($key, $arguments), '', $severity, TRUE);
    }

    /**
     * Translates a message
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return void
     */
    public static function translate($key, $arguments = NULL)
    {
        return LocalizationUtility::translate($key, 'sav_library_mvc', $arguments);
    }

    /**
     * Adds a message to the messages array
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return void
     */
    public static function addMessage($key, $arguments = NULL)
    {
        self::$messageRegisteredKeys[] = $key;
        $flashMessage = self::createFlashMessage($key, $arguments, FlashMessage::OK);
        self::addMessageToQueue($flashMessage);
    }

    /**
     * Adds a message to the messages array only once
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return (none)
     */
    public static function addMessageOnce($key, $arguments = NULL)
    {
        // If the message already exists, just return
        foreach (self::$messageRegisteredKeys as $messageRegisteredKey) {
            if ($messageRegisteredKey == $key) {
                return;
            }
        }
        // If we are here, the key was not found
        self::addMessage($key, $arguments);
    }

    /**
     * Adds an error to the errors array
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return boolean Returns always FALSE so that it can be used in return statements
     */
    public static function addError($key, $arguments = NULL)
    {
        self::$errorRegisteredKeys[] = $key;
        $flashMessage = self::createFlashMessage($key, $arguments, FlashMessage::ERROR);
        self::addMessageToQueue($flashMessage);
        return FALSE;
    }

    /**
     * Adds an error to the errors array only once
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return boolean Returns always FALSE so that it can be used in return statements
     */
    public static function addErrorOnce($key, $arguments = NULL)
    {
        // If the message already exists, just return
        foreach (self::$errorRegisteredKeys as $errorRegisteredKey) {
            if ($errorRegisteredKey == $key) {
                return;
            }
        }
        // If we are here, the key was not found
        self::addError($key, $arguments);
        return FALSE;
    }
}
?>