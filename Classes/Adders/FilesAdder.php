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

namespace YolfTypo3\SavLibraryMvc\Adders;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;

/**
 * Field configuration adder for Files type.
 */
final class FilesAdder extends AbstractAdder
{
    const RENDER_FILE_AS_IMAGE_OBJECT = 1;
    const RENDER_FILE_AS_IMAGE_RESOURCE = 2;
    const RENDER_FILE_AS_LINK = 3;

    /**
     * Renders the adder
     *
     * @return array
     */
    public function render(): array
    {
        $addedFieldConfiguration = [];
        $files = [];

        if ($this->fieldConfiguration['value'] instanceof ObjectStorage) {
            foreach ($this->fieldConfiguration['value'] as $object) {
                $fileConfiguration = [];
                $originalResource = $object->getOriginalResource();
                $fileConfiguration['fileName'] = $originalResource->getPublicUrl();
                $fileConfiguration['shortFileName'] = $originalResource->getName();
                $fileConfiguration['uid'] = $object->getUid();
                // Checks if the file exists
                if (! is_file(AbstractController::getSitePath() . $fileConfiguration['fileName'])) {
                    $fileConfiguration['fileUnknown'] = 1;
                    FlashMessages::addError('error.fileDoesNotExist', [
                        $fileConfiguration['fileName']
                    ]);
                }
                $type = $originalResource->getType();

                switch ($type) {
                    case AbstractFile::FILETYPE_IMAGE:
                        $fileConfiguration['value'] = $originalResource;
                        $fileConfiguration['renderAs'] = self::RENDER_FILE_AS_IMAGE_OBJECT;
                        break;
                    case AbstractFile::FILETYPE_TEXT:
                    case AbstractFile::FILETYPE_AUDIO:
                    case AbstractFile::FILETYPE_VIDEO:
                    case AbstractFile::FILETYPE_APPLICATION:
                        $fileConfiguration['renderAs'] = self::RENDER_FILE_AS_LINK;
                        // Gets the value
                        $fileConfiguration['value'] = $originalResource->getPublicUrl();

                        // Gets the message attribute
                        $fieldMessage = $this->fieldConfiguration['fieldMessage'];
                        if ($fieldMessage) {
                            $fileConfiguration['message'] = $this->fieldConfigurationManager->getFieldConfiguration($fieldMessage)['value'];
                        }
                        if (empty($this->fieldConfiguration['message']) && empty($fieldMessage)) {
                            $fileConfiguration['message'] = $originalResource->getName();
                        }
                        break;
                }
                // Processes the addIcon attribute
                if ($this->fieldConfiguration['addIcon']) {
                    $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
                    $pathParts = pathinfo($originalResource->getName());
                    $fileConfiguration['icon'] = $iconFactory->getIconForFileExtension($pathParts['extension'], Icon::SIZE_SMALL)->render();
                }
                // Adds file information
                $files[] = $fileConfiguration;
            }
        } else {
            $fileConfiguration = [];
            $fileConfiguration['renderAs'] = self::RENDER_FILE_AS_IMAGE_RESOURCE;
            $fileName = $this->fieldConfiguration['value'];
            // Adds the upload folder is required
            if (isset($this->fieldConfiguration['uploadFolder'])) {
                $fileName = $this->fieldConfiguration['uploadFolder'] . '/' . $fileName;
            }
            // Cheks if the file exists
            if (! file_exists(AbstractController::getSitePath() . $fileName) || empty($this->fieldConfiguration['value'])) {
                if (isset($this->fieldConfiguration['default'])) {
                    $fileName = $this->fieldConfiguration['default'];
                } else {
                    $fileName = 'EXT:sav_library_mvc/Resources/Public/Images/unknown.gif';
                }
            }
            $fileConfiguration['fileName'] = $fileName;


            $files[] = $fileConfiguration;
        }

        // Adds the files information
        $addedFieldConfiguration['files'] = $files;

        // Adds the alt attribute
        $fieldAlt = $this->fieldConfiguration['fieldAlt'];
        if ($fieldAlt) {
            $addedFieldConfiguration['alt'] = $this->fieldConfigurationManager->getFieldConfiguration($fieldAlt)['value'];
        }
        if (! empty($this->fieldConfiguration['alt']) && empty($fieldAlt)) {
            $addedFieldConfiguration['alt'] = $this->fieldConfiguration['alt'];
        }

        // Adds the javascript to confirm the delete action
        if ($this->fieldConfiguration['edit'] == AbstractController::EDIT_MODE) {
            AdditionalHeaderManager::addConfirmDeleteJavaScript('file');
        }

        return $addedFieldConfiguration;
    }
}