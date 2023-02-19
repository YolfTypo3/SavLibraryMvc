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

namespace YolfTypo3\SavLibraryMvc\ViewHelpers\Widget;

use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use YolfTypo3\SavLibraryMvc\Controller\FlashMessages;
use YolfTypo3\SavLibraryMvc\Controller\AbstractController;
use YolfTypo3\SavLibraryMvc\DatePicker\DatePicker;

/**
 * A date picker view helper.
 *
 * = Examples =
 *
 * <code title="DatePicker">
 * <sav:wiget.datePicker arguments="" />
 * </code>
 *
 * Output:
 * the date
 */
class DatePickerViewHelper extends AbstractFormFieldViewHelper
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
        $this->registerArgument('arguments', 'array', 'Arguments', true);
        $this->registerArgument('default', 'array', 'Default configuration', false, []);
    }

    /**
     * Renders the widget
     *
     * @return string the options array
     */
    public function render()
    {
        // Gets the arguments
        $arguments = $this->arguments['arguments'];
        $default = $this->arguments['default'];

        // Gets the name
        $name = $this->getName();

        // Sets the date format
        if (! isset($default['format'])) {
            $default['format'] = '%d/%m/%Y %H:%M';
        }

        // Merges the default values with the field configuration
        $fieldConfiguration = array_merge($default, $arguments['field']);

        // Gets the extension key
        $extensionKey = $this->getRequest()->getControllerExtensionKey();

        // Instanciates the calendar
        $datePicker = new DatePicker($extensionKey);

        // Registers the field name
        $this->registerFieldNameForFormTokenGeneration($name);

        // Adds the attribute for the tag
        $this->tag->addAttribute('type', 'text');
        $this->tag->addAttribute('name', $name);

        // Creates a unique id which will be used by the calendar and adds it
        $datePickerConfiguration = [];
        $datePickerConfiguration['id'] = $fieldConfiguration['fieldName'] . '_' . uniqid();
        $this->tag->addAttribute('id', 'input_' . $datePickerConfiguration['id']);

        // Adds items to the configuration
        $datePickerConfiguration['format'] = $fieldConfiguration['format'];
        $datePickerConfiguration['showsTime'] = $fieldConfiguration['showsTime'];

        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $datePickerConfiguration['icon'] = $iconFactory->getIcon('actions-calendar', Icon::SIZE_SMALL);

        // Sets the value
        $dateTimeFormat = $this->convertToDateTimeFormat($datePickerConfiguration['format']);
        if ($this->getValueAttribute() === null || empty($this->getValueAttribute())) {
            $value = $fieldConfiguration['noDefault'] ? '' : date($dateTimeFormat);
        } elseif ($fieldConfiguration['noDefault'] && $fieldConfiguration['newRecord']) {
            $value = '';
        } else {
            $value = strftime($datePickerConfiguration['format'], $this->getValueAttribute()->format('U'));
        }
        $this->tag->addAttribute('value', $value);
        $this->tag->addAttribute('onchange', 'document.changed=1;');

        $this->setErrorClassAttribute();

        $content = '<input type="hidden" name="' . preg_replace('/\[date\]$/', '[dateFormat]', $name) . '" value="' . $dateTimeFormat . '" />';
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
        $conversionArray = [
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
        ]; // %

        $matches = [];
        if (preg_match_all('/\%([a-zA-Z\%])/', $format, $matches) > 0) {
            foreach ($matches[1] as $match) {
                if (array_key_exists($match, $conversionArray)) {
                    $format = str_replace('%' . $match, $conversionArray[$match], $format);
                } else {
                    FlashMessages::addError('error.incorectDateFormat', [
                        $match
                    ]);
                }
            }
            return $format;
        }
        FlashMessages::addError('error.incorectDateFormat', [
            $format
        ]);
        return $format;
    }
}
