<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.org                                            //
//                                                                       //
// Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com     //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

global $CFG;
require_once($CFG->libdir . '/formslib.php');


/** SLELECT-элемент с динамической подгрузкой вариантов выбора
 * @todo переделать конструкторы: из обоих вызывать одну функцию, которая срабатывает только один раз
 *  
 * @package formslib
 */
class MoodleQuickForm_dof_ajaxselect extends MoodleQuickForm_select
{
    /**
     * @var array массив с параметрами ajax-запроса 
     */
    var $_options = array();
    /*
     * строка, имя элемента(в js используется для идентификации id элемента)
     */    
    var $_elementName = '';
    /**
     * @var string строка с js-кодом элемента
     */
    var $_js = '';
    /**
     * @var string строка с адресом для запроса, с установленными обязательными параметрами
     */
    var $ajaxurl= '';
        
    /** Конструктор класса для совместимости с PHP 5.3
     * 
     * @access public
     * @param  string $elementName Element's name
     * @param  mixed  $elementLabel Label(s) for an element
     * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
     */
    function __construct($elementName = null, $elementLabel = null, $attributes = null, $options=null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_ajaxselect';
        $this->_elementName = $elementName;
    }
   
   /**
    * Class constructor (for PHP 4)
    *
    * @access public
    * @param  string $elementName Element's name
    * @param  mixed  $elementLabel Label(s) for an element
    * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
    */
    function MoodleQuickForm_dof_ajaxselect($elementName = null, $elementLabel = null, $attributes = null, $options=null)
    {
        GLOBAL $DOF;
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_ajaxselect';
        $this->_elementName = $elementName;
        
        // подключаем скрипты для работы 
        $DOF->modlib('widgets')->js_init('ajaxselect');
        
        // устанавливаем обязательные параметры для ajax-запроса
        $this->setOptions($options);
    }
    

    /** Создаем элементы 
     * -text  : элемент текс с значениями по умолчанию(если есть)
     * -hidden: хранит в себе id выбранного элемента
     * -html  : вспомогат элемент, для вставки js-кода
     * 
     **
    function _createElements() {
        // получим атрибуты            
        $attributes = $this->getAttributes();
        // дополним их СВОИИ обязательными
        $attributes['id'] = "id_".$this->_elementName;
        $this->_elements = array();
        $this->_elements[] = MoodleQuickForm::createElement('select',$this->_elementName,null,$attributes);
        $this->_elements[] = MoodleQuickForm::createElement('hidden','id_autocomplete',$this->_id_for_hidden,array('id'=>'id_hidden_auto'));
        $this->_elements[] = MoodleQuickForm::createElement('html', $this->get_js());

        foreach ($this->_elements as $element){
            if (method_exists($element, 'setHiddenLabel')){
                $element->setHiddenLabel(true);
            }
        }
    }  
    
    
    /**
     * Отрисовка элемента
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $tabs    = $this->_getTabs();
            $strHtml = '';

            $attrString = $this->_getAttrString($this->_attributes);
            $strHtml .= $tabs . '<select' . $attrString . ">\n";

            $strHtml .= '<option/>';
            
            return $strHtml . $tabs . '</select>'.$this->get_js();
        }
    }
    
    /**
     * Формирует js-скрипт и возвращает его
     **/
    function get_js()
    {
        global $DOF;
        // проверяем правильность данных прежде чем рисовать JavaScript
        $this->checkOptions($this->_options);
        $js = "<script type=\"text/javascript\">\n//<![CDATA[\n";
        // все функции уже написаны в modlib/widgets, 
        // нам остается только обратиться к ним и инициализировать элемент
        $js .= '$(document).ready(
            function () {
                dof_ajaxselect_init(\''.
                $this->_options['parentid'].'\',
                \''.$this->_options['childselectid'].'\',
                \''.$this->_options['url'].'\',
                '.$this->_options['customdata'].',
                \''.$this->_values[0].'\');
        });';
        $js .= "\n //]]>\n</script>";        
        
        return $js;
    }
    
    /**
     *
     */
    function accept(&$renderer, $required = false, $error = null) {
        $renderer->renderElement($this, $required, $error);
    }
    
    /** Получить данные для ajax-запроса
     * Записывет все параметры запроса во внутреннее поле объекта
     * Функция только записывает данные, но не проверяет их
     * 
     * @param array|object $options - массив с данными для ajax-запроса
     *                  plugintype - тип плагина, предоатвляющий данные для запроса
     *                  plugincode - тип плагина, предоатвляющий данные для запроса
     *                  querytype - тип ajax-запроса внутри плагина, предоставляющего данные
     *                  url - url для запроса, со всеми параметрами (необязательно)
     *                  customdata - данные, которые поедут в плагин вместе с запросом (необязательный параметр, массив)
     *                  type - тип запроса в modlib/widgets (необязательно, по умочанию - ajaxselect)
     *                  parentid - id элемента, на значение которого мы ореинтируемся
     * 
     * @todo сделать вывод ошибок в более приемлемом виде
     */
    public function setOptions($options) 
    {
        global $DOF;
        if ( ! is_array($options) AND ! is_object($options) )
        {// неправильный тип данных для запроса
            return;
        }
        if ( is_object($options) )
        {// преобразовываем данные к нужному типу
            $options = (array)$options;
        }
        
        // устанавливаем id родительского и зависимого элементов
        if ( isset($options['parentid']) )
        {
            $this->_options['parentid'] = '#'.$options['parentid'];
        }
        $this->_options['childselectid'] = '#id_'.$this->_elementName;
        // приводим данные к формату json
        if ( isset($options['customdata']) )
        {
            $this->_options['customdata'] = json_encode($options['customdata']);
        }else
        {
            $this->_options['customdata'] = '{}';
        }
        // адрес для запроса (со всеми параметрами)
        if ( isset($options['url']) )
        {// он передан - просто его используем
            $this->_options['url'] = $options['url'];
        }else
        {// не передан - конструируем
            if ( isset($options['plugintype']) AND 
                 isset($options['plugincode']) AND 
                 isset($options['querytype']) ) 
            {
                if ( ! isset($options['type']) )
                {
                    $options['type'] = 'ajaxselect';
                }
                
                $this->_options['url'] = $DOF->url_modlib('widgets', '/json.php',
                    array(
                        'plugincode' => $options['plugincode'],
                        'plugintype' => $options['plugintype'],
                        'querytype'  => $options['querytype'],
                        'type'       => $options['type'],
                        'sesskey'    => sesskey()
                    ));
            }
        }
    }

    /**
    * We check the options and return only the values that _could_ have been
    * selected. We also return a scalar value if select is not "multiple"
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        //if (empty($this->_options)) {
        //    return $this->_prepareValue(null, $assoc);
        //}
        
        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        }
        $value = (array)$value;
        
        /*
        $cleaned = array();
        foreach ($value as $v) {
            foreach ($this->_options as $option) {
                if ((string)$option['attr']['value'] === (string)$v) {
                    $cleaned[] = (string)$option['attr']['value'];
                    break;
                }
            }
        }

        if (empty($cleaned)) {
            return $this->_prepareValue(null, $assoc);
        }
        return $this->_prepareValue($cleaned[0], $assoc);
        */
        //return array(11=>'aa');
        return $this->_prepareValue($value[0], $assoc);
    }
    
    /** Проверить параметры AJAX-запроса
     * Прерывает процесс работы скрипты и выдает ошибку, 
     * если не все параметры заданы, или некоторые заданы неправильно
     * Функция вызывается после setOptions
     * 
     * @return bool
     */
    public function checkOptions($options)
    {
        if ( ! isset($this->_options['parentid']) or ! $this->_options['parentid'] )
        {
            print_error(get_class($this).': NO REQUIRED PARAMETER parentid');
        }
        
        if ( ! isset($this->_options['url']) or ! $this->_options['url'] )
        {
            print_error(get_class($this).': NO REQUIRED PARAMETER url OR WRONG URL PARAMS');
        }
    }
}    
?>