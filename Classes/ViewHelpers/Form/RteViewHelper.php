<?php
namespace YolfTypo3\SavLibraryMvc\ViewHelpers\Form;

/*
 * This script belongs to the FLOW3 package "Fluid". *
 * *
 * It is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version. *
 * *
 * This script is distributed in the hope that it will be useful, but *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN- *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser *
 * General Public License for more details. *
 * *
 * You should have received a copy of the GNU Lesser General Public *
 * License along with the script. *
 * If not, see http://www.gnu.org/licenses/lgpl.html *
 * *
 * The TYPO3 project - inspiring people to share! *
 */

use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use YolfTypo3\SavLibraryMvc\Managers\AdditionalHeaderManager;
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
class RteViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\TextareaViewHelper
{

    /**
     * Renders the rte htmlarea.
     *
     * @return string
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     *         @api
     */
    public function render()
    {

        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->setRespectSubmittedDataValue(true);

        $GLOBALS['TSFE']->beUserLogin = true;
        $GLOBALS['BE_USER'] = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $GLOBALS['BE_USER']->frontendEdit = 1;
        $GLOBALS['BE_USER']->uc['edit_RTE'] = true;

        $richTextEditorRenderer = RichTextEditorCompatibility::getRichTextEditorRenderer();
        $richTextEditorRenderer->setArguments($this->arguments);
        $richTextEditorRenderer->setName($this->getName());
        $richTextEditorRenderer->setValueAttribute($this->getValueAttribute());

        return  $richTextEditorRenderer->render();

    }
}

?>
