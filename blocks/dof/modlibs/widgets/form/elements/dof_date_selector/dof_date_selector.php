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
 * Класс поля выбора текущей даты
 * 
 * Возвращает массив, содержащий в себе Timestamp, часовой пояс, 
 * а также выбранную дату и время. 
 * Список поддерживаемых опций:
 *      int startyear - начальный год интервала
 *      int stopyear - конечный год интервала
 *      int hours - время(часы), устанавливаемое в timestamp
 *      int minutes - время(минуты), устанавливаемое в timestamp
 *      int seconds - время(секунды), устанавливаемое в timestamp
 *      int defaulttime - ?
 *      float timezone - часовой пояс, в котором выбираем дату
 *      int step - ?
 *      bool optional - true/false, возможность заблокировать выбор даты
 *      bool onlytimestamp - true/false, вывод только timestamp
 * 
 * @package formslib
 * @author polikarpov
 */
class MoodleQuickForm_dof_date_selector extends MoodleQuickForm_group {

    /**
     * Опции поля
     * 
     * @var array
     */
    protected $_options = array();

    /**
     * @var array These complement separators, they are appended to the resultant HTML.
     */
    protected $_wrap = array('', '');

    /**
     * @var null|bool Keeps track of whether the date selector was initialised using createElement
     *                or addElement. If true, createElement was used signifying the element has been
     *                added to a group - see MDL-39187.
     */
    protected $_usedcreateelement = true;

    /**
     * Конструктор
     * 
     * @param string $elementName Element's name
     * @param mixed $elementLabel Label(s) for an element
     * @param array $options Options to control the element's display
     * @param mixed $attributes Either a typical HTML attribute string or an associative array
     */
    function MoodleQuickForm_dof_date_selector($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        // Get the calendar type used - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();
        // Формируем опции по-умолчанию
        $this->_options = array(
                'startyear' => $calendartype->get_min_year(), 
                'stopyear' => $calendartype->get_max_year(),
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'defaulttime' => 0, 
                'timezone' => 99, 
                'step' => 5, 
                'optional' => false,
                'onlytimestamp' => false
        );
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'date_selector';
        // Переопределяем опции
        if (is_array($options)) 
        {
            foreach ($options as $name => $value) 
            {
                if ( isset($this->_options[$name]) ) 
                {
                    if ( is_array($value) && is_array($this->_options[$name]) ) 
                    {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else 
                    {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }

        // Включаем js поддерку, только если выбран грегорианский календарь
        if ($calendartype->get_name() === 'gregorian') 
        {
            form_init_date_js();
        }
    }

    /**
     * Создает html код для поля выбора даты, состоящего из 3 выпадающих списков
     *
     * @access private
     */
    function _createElements() {
        global $OUTPUT;

        // Get the calendar type used - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        $this->_elements = array();

        $dateformat = $calendartype->get_date_order($this->_options['startyear'], $this->_options['stopyear']);
        foreach ($dateformat as $key => $value) {
            // E_STRICT creating elements without forms is nasty because it internally uses $this
            $this->_elements[] = @MoodleQuickForm::createElement('select', $key, get_string($key, 'form'), $value, $this->getAttributes(), true);
        }
        // The YUI2 calendar only supports the gregorian calendar type so only display the calendar image if this is being used.
        if ($calendartype->get_name() === 'gregorian') {
            $image = $OUTPUT->pix_icon('i/calendar', get_string('calendar', 'calendar'), 'moodle');
            $this->_elements[] = @MoodleQuickForm::createElement('link', 'calendar',
                    null, '#', $image,
                    array('class' => 'visibleifjs'));
        }
        // If optional we add a checkbox which the user can use to turn if on
        if ($this->_options['optional']) {
            $this->_elements[] = @MoodleQuickForm::createElement('checkbox', 'enabled', null, get_string('enable'), $this->getAttributes(), true);
        }
        foreach ($this->_elements as $element){
            if (method_exists($element, 'setHiddenLabel')){
                $element->setHiddenLabel(true);
            }
        }

    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param string $event Name of event
     * @param mixed $arg event arguments
     * @param object $caller calling object
     * @return bool
     */
    function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'updateValue':
                // Constant values override both default and submitted ones
                // default values are overriden by submitted.
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    // If no boxes were checked, then there is no value in the array
                    // yet we don't want to display default value in this case.
                    if ($caller->isSubmitted()) {
                        $value = $this->_findValue($caller->_submitValues);
                    } else {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                $requestvalue=$value;
                if ($value == 0) {
                    $value = time();
                }
                if (!is_array($value)) {
                    $calendartype = \core_calendar\type_factory::get_calendar_instance();
                    $currentdate = $calendartype->timestamp_to_date_array($value, $this->_options['timezone']);
                    $value = array(
                        'day' => $currentdate['mday'],
                        'month' => $currentdate['mon'],
                        'year' => $currentdate['year']);
                    // If optional, default to off, unless a date was provided.
                    if ($this->_options['optional']) {
                        $value['enabled'] = $requestvalue != 0;
                    }
                } else {
                    $value['enabled'] = isset($value['enabled']);
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            case 'createElement':
                // Optional is an optional param, if its set we need to add a disabledIf rule.
                // If its empty or not specified then its not an optional dateselector.
                if (!empty($arg[2]['optional']) && !empty($arg[0])) {
                    // When using the function addElement, rather than createElement, we still
                    // enter this case, making this check necessary.
                    if ($this->_usedcreateelement) {
                        $caller->disabledIf($arg[0] . '[day]', $arg[0] . '[enabled]');
                        $caller->disabledIf($arg[0] . '[month]', $arg[0] . '[enabled]');
                        $caller->disabledIf($arg[0] . '[year]', $arg[0] . '[enabled]');
                    } else {
                        $caller->disabledIf($arg[0], $arg[0] . '[enabled]');
                    }
                }
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            case 'addElement':
                $this->_usedcreateelement = false;
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            default:
                return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    /**
     * Returns HTML for advchecbox form element.
     *
     * @return string
     */
    function toHtml() {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);

        $html = $this->_wrap[0];
        if ($this->_usedcreateelement) {
            $html .= html_writer::tag('span', $renderer->toHtml(), array('class' => 'fdate_selector'));
        } else {
            $html .= $renderer->toHtml();
        }
        $html .= $this->_wrap[1];

        return $html;
    }

    /**
     * Accepts a renderer
     *
     * @param HTML_QuickForm_Renderer $renderer An HTML_QuickForm_Renderer object
     * @param bool $required Whether a group is required
     * @param string $error An error message associated with a group
     */
    function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderElement($this, $required, $error);
    }

    /**
     * Вывод значения поля
     *
     * @param array $submitValues values submitted.
     * @param bool $assoc specifies if returned array is associative
     * @return array
     */
    function exportValue(&$submitValues, $assoc = false) {
        
        $value = array();
        $valuearray = array();
        foreach ($this->_elements as $element){
            $thisexport = $element->exportValue($submitValues[$this->getName()], true);
            if ($thisexport!=null){
                $valuearray += $thisexport;
            }
        }
        
        if (count($valuearray)){
            if($this->_options['optional']) {
                // If checkbox is on, the value is zero, so go no further
                if(empty($valuearray['enabled'])) {
                    $result = 0;
                    $name = $this->getName();
                    $value = [];
                    $myIndex  = "['" . str_replace(array(']', '['), array('', "']['"), $name) . "']";
                    eval("\$value$myIndex = \$result;");
                    return $value;
                }
            }
            // Get the calendar type used - see MDL-18375.
            $calendartype = \core_calendar\type_factory::get_calendar_instance();
            $gregoriandate = $calendartype->convert_to_gregorian($valuearray['year'], $valuearray['month'], $valuearray['day']);

            // Формируем результат
            if ( $this->_options['onlytimestamp'] )
            {// Вернуть только timestamp
                $result = make_timestamp(
                        $gregoriandate['year'],
                        $gregoriandate['month'],
                        $gregoriandate['day'],
                        $this->_options['hours'],
                        $this->_options['minutes'],
                        $this->_options['seconds'],
                        $this->_options['timezone'],
                        true
                );
            } else
            {
                $result = array();
                // Timestamp
                $result['timestamp'] = make_timestamp(
                    $gregoriandate['year'],
                    $gregoriandate['month'],
                    $gregoriandate['day'],
                    $this->_options['hours'],
                    $this->_options['minutes'],
                    $this->_options['seconds'],
                    $this->_options['timezone'],
                    true
                );
                // Получаем дополнительную информацию о дате
                $additionalinfo = dof_usergetdate($result['timestamp'], $this->_options['timezone']);
            
                // Год
                $result['year'] = $gregoriandate['year'];
                // Месяц
                $result['month'] = $gregoriandate['month'];
                // День
                $result['day'] = $gregoriandate['day'];
                // Номер дня в году
                $result['yday'] = $additionalinfo['yday'];
                // Номер дня в месяце
                $result['wday'] = $additionalinfo['wday'];
                // Час
                $result['hours'] = $this->_options['hours'];
                // Минуты
                $result['minutes'] = $this->_options['minutes'];
                // Секунды
                $result['seconds'] = $this->_options['seconds'];
                // Часовой пояс
                $result['timezone'] = $this->_options['timezone'];
            }
            
            // Добавляем данные
            $name = $this->getName();
            $value = [];
            $myIndex  = "['" . str_replace(array(']', '['), array('', "']['"), $name) . "']";
            eval("\$value$myIndex = \$result;");
            return $value;
        } else {
            return null;
        }
    }
}
