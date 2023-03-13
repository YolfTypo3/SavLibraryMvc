<?php

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

namespace YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor;

/**
 * Abstract Rich text editor renderer
 */
abstract class AbstractRichTextEditorRenderer
{
    /**
     * @var array $arguments
     */
    protected $arguments;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $valueAttribute
     */
    protected $valueAttribute;

    /**
     * Setter for the arguments
     *
     * @param array $arguments
     */
    public function setArguments(array $arguments) {
        $this->arguments = $arguments;
    }

    /**
     * Setter for the name
     *
     * @param string $name
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * Setter for the value attribute
     *
     * @param string $valueAttribute
     */
    public function setValueAttribute(string $valueAttribute) {
        $this->valueAttribute = $valueAttribute;
    }

    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render(): string
    {
    }

    /**
     * Gets the page id
     *
     * @return int
     */
    protected function getPageId(): int
    {
        // @extensionScannerIgnoreLine
        return (int) $GLOBALS['TSFE']->id;
    }
}
