<?php

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
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class RenderViewHelper
 * @inheritdoc
 */
class RenderViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\RenderViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var array
     */
    protected static $attributes = [
        'addLeftIfNotNull',
        'addLeftIfNull',
        'addRightIfNotNull',
        'addRightIfNull',
        'stdWrapValue',
    ];

    /**
     * Renders the viewhelper
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        // Gets the content
        $content = parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);

        // Special processing
        $fiedConfiguration = $arguments['arguments']['field'];
        $specialAttributes = array_intersect(array_keys($fiedConfiguration), self::$attributes);
        if (! empty($specialAttributes)) {
            foreach ($specialAttributes as $specialAttribute) {
                $addAttributeBasedMethod = 'postProcessorFor' . ucfirst($specialAttribute);
                if (method_exists(static::class, $addAttributeBasedMethod)) {
                    $content = self::$addAttributeBasedMethod($content, $fiedConfiguration);
                }
            }
        }
        return $content;
    }

    /**
     * Post-processor for the attribute addLeftIfNotNull.
     *
     * @param string $fieldName
     * @return mixed
     */
    protected static function postProcessorForAddLeftIfNotNull(?string $content, $fieldConfiguration)
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
     * @return mixed
     */
    protected static function postProcessorForAddLeftIfNull(?string $content, $fieldConfiguration)
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
     * @return mixed
     */
    protected static function postProcessorForAddRightIfNotNull(?string $content, $fieldConfiguration)
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
     * @return mixed
     */
    protected static function postProcessorForAddRightIfNull(?string $content, $fieldConfiguration)
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
     * @return mixed
     */
    protected static function postProcessorForStdWrapValue(?string $content, $fieldConfiguration)
    {
        if (! empty($fieldConfiguration['value'])) {
           // The value is wrapped using the stdWrap TypoScript
           $configuration = $fieldConfiguration['stdWrapValue'];

           $TSparser = GeneralUtility::makeInstance(TypoScriptParser::class);
           $TSparser->parse($configuration);

           $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
           $content = $contentObjectRenderer->stdWrap($content, $TSparser->setup);

        }
        return $content;
    }

}
