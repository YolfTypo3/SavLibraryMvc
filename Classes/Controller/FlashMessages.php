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

namespace YolfTypo3\SavLibraryMvc\Controller;

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
    protected static $errorRegisteredKeys = [];

    /**
     *
     * @var array
     */
    protected static $messageRegisteredKeys = [];

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
     * @param int $severity
     *            The message severity
     *
     * @return FlashMessage
     */
    protected static function createFlashMessage($key, $arguments, $severity): FlashMessage
    {
        return GeneralUtility::makeInstance(FlashMessage::class, self::translate($key, $arguments), '', $severity, true);
    }

    /**
     * Translates a message
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return string|null
     */
    public static function translate($key, $arguments = null): ?string
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
    public static function addMessage($key, $arguments = null)
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
    public static function addMessageOnce($key, $arguments = null)
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
     * @return boolean Returns always false so that it can be used in return statements
     */
    public static function addError($key, $arguments = null): bool
    {
        self::$errorRegisteredKeys[] = $key;
        $flashMessage = self::createFlashMessage($key, $arguments, FlashMessage::ERROR);
        self::addMessageToQueue($flashMessage);
        return false;
    }

    /**
     * Adds an error to the errors array only once
     *
     * @param string $key
     *            The message key
     * @param array $arguments
     *            The argument array
     *
     * @return boolean Returns always false so that it can be used in return statements
     */
    public static function addErrorOnce($key, $arguments = null): bool
    {
        // If the message already exists, just return
        foreach (self::$errorRegisteredKeys as $errorRegisteredKey) {
            if ($errorRegisteredKey == $key) {
                return false;
            }
        }
        // If we are here, the key was not found
        self::addError($key, $arguments);
        return false;
    }
}
