<?php
namespace SAV\SavLibraryMvc\ViewHelpers\Form;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Rtehtmlarea\Controller\FrontendRteController;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use SAV\SavLibraryMvc\Managers\AdditionalHeaderManager;

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
class RteForTypo3VersionLowerThan7ViewHelper extends \SAV\SavLibraryMvc\ViewHelpers\Form\AbstractFormFieldViewHelper
{

    /**
     * Counter for RTE
     *
     * @var int
     */
    public $RTEcounter = 1;

    /**
     * Type of the document
     *
     * @var boolean
     */
    public $docLarge = true;

    /**
     * Initial JavaScript to be printed before the form (should be in head, but cannot due to IE6 timing bug)
     *
     * @var string
     */
    public $additionalJS_initial = '';

    /**
     * Additional JavaScript to be printed before the form
     *
     * @var array
     */
    public $additionalJS_pre = array();

    /**
     * Additional JavaScript to be printed after the form
     *
     * @var array
     */
    public $additionalJS_post = array();

    /**
     * Additional JavaScript to be executed on submit
     *
     * @var array
     */
    public $additionalJS_submit = array();

    /**
     *
     * @var string
     */
    protected $tagName = 'textarea';

    /**
     * Initialize the arguments.
     *
     * @return void
     * @author Sebastian Kurfürst <sebastian@typo3.org>
     *         @api
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('rows', 'int', 'The number of rows of a text area', TRUE);
        $this->registerTagAttribute('cols', 'int', 'The number of columns of a text area', TRUE);
        $this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
        $this->registerUniversalTagAttributes();
    }

    /**
     * Renders the rte htmlarea.
     *
     * @return string
     * @author Laurent Foulloy <yolf.typo3@orange.fr>
     *         @api
     */
    public function render()
    {

        // Gets the name and registers it
        $name = $this->getName();
        $this->registerFieldNameForFormTokenGeneration($name);

        // Gets the value
        $value = $this->getValueAttribute();

        // Adds the attributes
        $this->tag->forceClosingTag(TRUE);
        $this->tag->addAttribute('name', $name);
        $this->tag->setContent($value);
        $this->setErrorClassAttribute();

        // Creates the RTE
        $htmlArray = array();
        $richTextEditor = GeneralUtility::makeInstance(FrontendRteController::class);

        // Sets the page typoScript configuration
        $pageTypoScriptConfiguration = BackendUtility::getPagesTSconfig($GLOBALS['TSFE']->id);
        $typoScriptConfiguration = array_merge($pageTypoScriptConfiguration['RTE.']['default.']['FE.'], array(
            'rteResize' => 1,
            'showStatusBar' => 0
        ));

        // Sets the configuration
        $configuration = array(
            'richtext' => 1,
            'rte_transform' => array(
                'parameters' => array(
                    'flag=rte_enabled',
                    'mode=ts_css'
                )
            )
        );

        // Sets the properties
        $properties = array(
            'itemFormElName' => $this->getName(),
            'itemFormElValue' => html_entity_decode($this->getValueAttribute(), ENT_QUOTES, $GLOBALS['TSFE']->renderCharset)
        );

        // Gets the ritch text editor
        $content = $richTextEditor->drawRTE($this, '', '', $row = array(), $properties, $configuration, $typoScriptConfiguration, 'text', '', $GLOBALS['TSFE']->id);

        // Removes the hidden field
        $content = preg_replace('/<input type="hidden"[^>]*>/', '', $content);

        // Adds onchange
        $content = preg_replace('/<textarea ([^>]*)>/', '<textarea $1' . ' onchange="document.changed=1;">', $content);

        // Replaces the height
        $height = $field['height'];
        if (! empty($height)) {
            $content = preg_replace('/height:[^p]*/', 'height:' . $height, $content);

            // Adds 2px to the first div
            $content = preg_replace('/height:([^p]*)/', 'height:$1+2', $content, 1);
        }

        // Replaces the width
        $width = $field['width'];
        if (! empty($width)) {
            $content = preg_replace('/width:[^p]*/', 'width:' . $width, $content);
            // Adds 2px to the first div
            $content = preg_replace('/width:([^p]*)/', 'width:$1+2', $content, 1);
        }

        $htmlArray[] = $content;

        // Adds the javaScript after the textarea tag
        $htmlArray[] = '<script type="text/javascript">';
        $htmlArray[] = $this->additionalJS_post[0];
        $htmlArray[] = '</script>';

        // Adds the javaScript for the rich text editor update
        $editorNumber = preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $properties['itemFormElName']) . '_' . $this->RTEcounter;
        AdditionalHeaderManager::addJavaScript('checkIfRteChanged', 'checkIfRteChanged(\'' . $editorNumber . '\');');
        AdditionalHeaderManager::addJavaScript('rteUpdate', $this->additionalJS_submit[0]);

        return implode(chr(10), $htmlArray);
    }
}

?>
