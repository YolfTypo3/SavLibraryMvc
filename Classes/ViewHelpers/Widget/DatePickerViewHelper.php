<?php
namespace SAV\SavLibraryMvc\ViewHelpers\Widget;

/**
 * Copyright notice
 *
 * (c) 2015 Laurent Foulloy <yolf.typo3@orange.fr>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use SAV\SavLibraryMvc\Controller\AbstractController;
use SAV\SavLibraryMvc\DatePicker\DatePicker;
use SAV\SavLibraryMvc\Controller\FlashMessages;

/**
 * A date picker view helper.
 *
 * = Examples =
 *
 * <code title="DatePicker">
 * <sav:wiget.datePicker aarguments="" />
 * </code>
 *
 * Output:
 * the date
 */
class DatePickerViewHelper extends \SAV\SavLibraryMvc\ViewHelpers\Form\AbstractFormFieldViewHelper
{

    /**
     *
     * @var string
     */
    protected $tagName = 'input';

    /**
     * Initialize the arguments.
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
    }

    /**
     *
     * @param array $arguments
     *            The arguments
     * @param array $default
     *            The default configurataion values
     * @param string $action
     *            The action to execute
     *
     * @return string the options array
     */
    public function render($arguments, $default = array())
    {

        // Sets the date format
        if (! isset($default['format'])) {
            $default['format'] = '%d/%m/%Y %H:%M';
        }

        // Merges the default values with the field configuration
        $fieldConfiguration = array_merge($default, $arguments['field']);

        // Instanciates the calendar
        $datePicker = GeneralUtility::makeInstance(DatePicker::class);

        // Registers the field name
        $this->registerFieldNameForFormTokenGeneration($name);

        // Adds the attribute for the tag
        $this->tag->addAttribute('type', 'text');
        $this->tag->addAttribute('name', $name);

        // Creates a unique id which will be used by the calendar and adds it
        $datePickerConfiguration['id'] = $fieldConfiguration['fieldName'] . '_' . uniqid();
        $this->tag->addAttribute('id', 'input_' . $datePickerConfiguration['id']);

        // Adds items to the configuration
        $datePickerConfiguration['format'] = $fieldConfiguration['format'];
        $datePickerConfiguration['showsTime'] = $fieldConfiguration['showsTime'];
        $datePickerConfiguration['iconPath'] = AbstractController::getIconPath('calendar.gif');

        // Sets the value
        if ($this->getValueAttribute() === NULL || empty($this->getValueAttribute())) {
            $value = $fieldConfiguration['noDefault'] ? '' : date();
        } elseif ($fieldConfiguration['noDefault'] && $configuration['newRecord']) {
            $value = '';
        } else {
            $value = strftime($datePickerConfiguration['format'], $this->getValueAttribute()->format('U'));
        }
        $this->tag->addAttribute('value', $value);
        $this->tag->addAttribute('onchange', 'document.changed=1;');

        $this->setErrorClassAttribute();

        $dateTimeFormat = $this->convertToDateTimeFormat($datePickerConfiguration['format']);
        $content = '<input type="hidden" name="' . str_replace('[date]', '[dateFormat]', $name) . '" value="' . $dateTimeFormat . '" />';
        $content .= $this->tag->render() . $datePicker->render($datePickerConfiguration);

        return $content;
    }

    /**
     * Converts a strftime format to the DateTime format
     *
     * @param string $format
     *
     * @return string Datetime format
     */
    public function convertToDateTimeFormat($format)
    {
        $conversionArray = array(
            // Day
            'a' => 'D', // Sun through Sat
            'A' => 'l', // Sunday through Saturday
            'd' => 'd', // 01 to 31
            'e' => 'j', // 1 to 31
            'j' => 'z', // 001 to 366
                        // Month
            'b' => 'M', // Jan through Dec
            'B' => 'F', // January through December
            'h' => 'M', // Jan through Dec
            'm' => 'm', // 01 (for January) through 12 (for December)
                        // Year
            'y' => 'y', // Example: 09 for 2009, 79 for 1979
            'Y' => 'Y', // Example: 2038
                        // Time
            'H' => 'H', // hour: 00 through 23
            'k' => 'G', // hour: 1 through 23 (space preceeding single digits)
            'I' => 'h', // hour: 01 through 12
            'l' => 'g', // hour: 1 through 12 (space preceeding single digits)
            'M' => 'i', // minute: 00 through 59
            'P' => 'a', // Example: am for 00:31, pm for 22:23
            'p' => 'A', // Example: AM for 00:31, PM for 22:23
            'S' => 's', // second: 00 through 59
            'r' => 'h:i:s A', // Same as "%I:%M:%S %p"
            'R' => 'H:i', // Same as "%H:%M"
            'T' => 'H:i:s', // Same as "%H:%M:%S"
            '%' => '%'
        ); // %

        if (preg_match_all('/\%([a-zA-Z\%])/', $format, $matches) > 0) {
            foreach ($matches[1] as $matchKey => $match) {
                if (array_key_exists($match, $conversionArray)) {
                    $format = str_replace('%' . $match, $conversionArray[$match], $format);
                } else {
                    FlashMessages::addError('error.incorectDateFormat', array(
                        $match
                    ));
                }
            }
            return $format;
        }
        FlashMessages::addError('error.incorectDateFormat', array(
            $format
        ));
        return $format;
    }
}
?>
