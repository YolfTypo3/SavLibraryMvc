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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers\Form;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\TextareaViewHelper;
use YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor\RichTextEditorCompatibility;

/**
 * RTE view helper.
 *
 * = Examples =
 *
 * <code title="Example">
 * <f:form.rte name="rteName" rows="5" cols="50" value="The text to edit" />
 * <f:form.rte property="rteName" rows="5" cols="50" value="The text to edit" />
 * </code>
 *
 * Output:
 * The rich text editor
 *
 * @package SavLibraryMvc
 */
class RteViewHelper extends TextareaViewHelper
{
    /**
     * Renders the rte.
     *
     * @return string
     */
    public function render()
    {
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->setRespectSubmittedDataValue(true);

        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $GLOBALS['BE_USER']->uc['edit_RTE'] = true;

        $richTextEditorRenderer = RichTextEditorCompatibility::getRichTextEditorRenderer();
        $richTextEditorRenderer->setArguments($this->arguments);
        $richTextEditorRenderer->setName($this->getName());
        $richTextEditorRenderer->setValueAttribute($this->getValueAttribute());

        return  $richTextEditorRenderer->render();
    }
}
