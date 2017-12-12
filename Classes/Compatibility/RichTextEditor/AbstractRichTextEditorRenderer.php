<?php
namespace YolfTypo3\SavLibraryMvc\Compatibility\RichTextEditor;

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
    public function setArguments($arguments) {
        $this->arguments = $arguments;
    }

    /**
     * Setter for the name
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Setter for the value attribute
     *
     * @param string $valueAttribute
     */
    public function setValueAttribute($valueAttribute) {
        $this->valueAttribute = $valueAttribute;
    }

    /**
     * Renders the rich text editor
     *
     * @return string
     */
    public function render()
    {

    }
}

?>
