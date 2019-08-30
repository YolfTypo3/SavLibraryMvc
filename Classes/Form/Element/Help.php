<?php
namespace YolfTypo3\SavLibraryMvc\Form\Element;

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
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Help rendering type
 */
class Help extends AbstractFormElement
{

    /**
     * The extension key
     *
     * @var string
     */
    protected $extensionKey = 'sav_library_mvc';

    /**
     * The TYPO3 documentaion root URL
     *
     * @var string
     */
    protected $documentationRootUrl = 'https://docs.typo3.org/typo3cms/extensions/';

    public function render()
    {
        $parameters = $this->data['parameterArray']['fieldConf']['config']['parameters'];
        $tag = $parameters['tag'];
        $section = $parameters['section'];

        $documentationUrl = $this->documentationRootUrl . $this->extensionKey . '/stable/' . $section . '/Index.html#' . $tag;

        $languageService = GeneralUtility::makeInstance(LanguageService::class);
        $message = '<b>' . $languageService->sL('LLL:EXT:' . $this->extensionKey . '/Resources/Private/Language/locallang.xlf:extensionFlexform.help') . '</b>';

        $result = $this->initializeResultArray();
        $result['html'] = '<a target="_blank" href="' . $documentationUrl . '">' . $message . '</a>';
        return $result;
    }
}

?>