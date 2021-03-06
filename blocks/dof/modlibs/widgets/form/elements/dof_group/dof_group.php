<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

global $CFG;
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Класс поля группировки элементов
 * 
 * Класс с расширенным набором настроек поля
 * 
 * @package    formslib
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_dof_group extends MoodleQuickForm_group {

    /** @var string html for help button, if empty then no help */
    var $_helpbutton='';

    /**
     * constructor
     *
     * @param string $elementName (optional) name of the group
     * @param string $elementLabel (optional) group label
     * @param array $elements (optional) array of HTML_QuickForm_element elements to group
     * @param string $separator (optional) string to seperate elements.
     * @param string $appendName (optional) string to appened to grouped elements.
     */
    function MoodleQuickForm_dof_group($elementName=null, $elementLabel=null, $elements=null, $separator=null, $appendName = true, $options = null) {
        parent::HTML_QuickForm_group($elementName, $elementLabel, $elements, $separator, $appendName);
    }

    /** @var string template type, would cause problems with client side validation so will leave for now */
    //var $_elementTemplateType='fieldset';

    /**
     * set html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }

    /**
     * Returns element template, nodisplay/static/fieldset
     *
     * @return string
     */
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            if ($this->getGroupType() == 'submit'){
                return 'nodisplay';
            } else {
                return 'static';
            }
        } else {
            if ($this->getGroupType() == 'submit') {
                return 'actionbuttons';
            }
            return 'fieldset';
        }
    }
}
