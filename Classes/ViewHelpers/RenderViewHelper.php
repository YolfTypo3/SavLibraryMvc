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
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace YolfTypo3\SavLibraryMvc\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\TypoScript\TypoScriptStringFactory;
use TYPO3\CMS\Core\TypoScript\AST\AstBuilder;

/**
 * Class RenderViewHelper
 * @inheritdoc
 */
class RenderViewHelper extends \TYPO3Fluid\Fluid\ViewHelpers\RenderViewHelper
{

    /**
     * @var array
     */
    protected array $attributes = [
        'addLeftIfNotNull',
        'addLeftIfNull',
        'addRightIfNotNull',
        'addRightIfNull',
        'stdWrapValue',
    ];

    /**
     * Renders the view helper
     *
     * @return string
     */
    public function render(): string
    {
        // Gets the content
        $content = parent::render() ?? '';

        // Special processing
        $fiedConfiguration = $this->arguments['arguments']['field'];
        $specialAttributes = array_intersect(array_keys($fiedConfiguration), $this->attributes);
        if (! empty($specialAttributes)) {
            foreach ($specialAttributes as $specialAttribute) {
                $addAttributeBasedMethod = 'postProcessorFor' . ucfirst($specialAttribute);
                if (method_exists($this, $addAttributeBasedMethod)) {
                    $content = $this->$addAttributeBasedMethod($content, $fiedConfiguration);
                }
            }
        }
        return $content;
    }

    /**
     * Post-processor for the attribute addLeftIfNotNull.
     *
     * @param string $fieldName
     * @param array $fieldConfiguration
     * 
     * @return string
     */
    protected function postProcessorForAddLeftIfNotNull(?string $content, array $fieldConfiguration): string
    {
        if (!empty($fieldConfiguration['value'])) {
            return $fieldConfiguration['addLeftIfNotNull'] .  $content;
        }
            return $content;
    }

    /**
     * Post-processor for the attribute addLeftIfNull.
     *
     * @param string $fieldName
     * @param array $fieldConfiguration
     * 
     * @return string
     */
    protected function postProcessorForAddLeftIfNull(?string $content, array $fieldConfiguration): string
    {
        if (empty($fieldConfiguration['value'])) {
            return $fieldConfiguration['addLeftIfNull'] .  $content;
        }
        return $content;
    }

    /**
     * Post-processor for the attribute addRightIfNotNull.
     *
     * @param string $fieldName
     * @param array $fieldConfiguration
     * 
     * @return string
     */
    protected function postProcessorForAddRightIfNotNull(?string $content, array $fieldConfiguration): string
    {
        if (! empty($fieldConfiguration['value'])) {
            return $content . $fieldConfiguration['addRightIfNotNull'];
        }
        return $content;
    }

    /**
     * Post-processor for the attribute addRightIfNull.
     *
     * @param string $fieldName
     * @param array $fieldConfiguration
     * 
     * @return string
     */
    protected function postProcessorForAddRightIfNull(?string $content, array $fieldConfiguration): string
    {
        if (! empty($fieldConfiguration['value'])) {
            return $content . $fieldConfiguration['addRightIfNull'];
        }
        return $content;
    }

    /**
     * Post-processor for the attribute addRightIfNull.
     *
     * @param string $fieldName
     * @param array $fieldConfiguration
     * 
     * @return string
     */
    protected function postProcessorForStdWrapValue(?string $content, array $fieldConfiguration): string
    {
        if (! empty($fieldConfiguration['value'])) {
           // The value is wrapped using the stdWrap TypoScript
           $configuration = $fieldConfiguration['stdWrapValue'];

           /** @var TypoScriptStringFactory $typoScriptStringFactory */
           $typoScriptStringFactory = GeneralUtility::makeInstance(TypoScriptStringFactory::class);
           $parsedTypoScript = $typoScriptStringFactory->parseFromString($configuration, new AstBuilder(new NoopEventDispatcher()));
           
           $controller = $this->getRequest()->getAttribute('controller');
           $contentObjectRenderer = $controller->getContentObjectRenderer();
           $content = $contentObjectRenderer->stdWrap($content, $parsedTypoScript->toArray());
        }
        return $content;
    }

}
