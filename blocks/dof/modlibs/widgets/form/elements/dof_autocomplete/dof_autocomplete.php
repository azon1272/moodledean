<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
//                                                                        //
// Copyright (C) 2008-2999  Alex Djachenko (Алексей Дьяченко)             //
// alex-pub@my-site.ru                                                    //
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   // 
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           // 
//                                                                        //
// This program is free software: you can redistribute it and/or modify   //
// it under the terms of the GNU General Public License as published by   //
// the Free Software Foundation, either version 3 of the Licensen.        //
//                                                                        //
// This program is distributed in the hope that it will be useful,        //
// but WITHOUT ANY WARRANTY; without even the implied warranty of         //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          //
// GNU General Public License for more details.                           //
//                                                                        //
// You should have received a copy of the GNU General Public License      //
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  //
//                                                                        //
////////////////////////////////////////////////////////////////////////////

/**
 * Класс поля автозаполнения.
 * 
 * Производит AJAX запрос к плагину и возвращает список подходящих элементов.
 * 
 * @package    modlib
 * @subpackage widgets
 * @author     dido86
 * @copyright  2011
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/form/text.php');

class MoodleQuickForm_dof_autocomplete extends MoodleQuickForm_group
{
    /*
     * Опции формирования AJAX запроса
     * 
     * @var array
     */
    var $_options = [];
    
    /*
     * Имя поля
     * 
     * @var string
     */    
    var $_elementName = '';
    
    /*
     * Дополнительный код для поддержки работы поля
     * 
     * @var string
     */     
    var $_js = '';
    
    /*
     * Значение скрытого поля - ID выбранного элемента для автодополнения
     * 
     * @var int
     */     
    var $_id_for_hidden = 0;
    
    /*
     * Значение текстового автозаполняемого поля 
     */     
    var $_text = '';
    
    /**
     * Список дополнительных полей, из которых необходимо получить данные для
     * передачи в AJAX запросе
     * 
     * Формат массива ['key'] => fieldname
     * 
     * @var array
     */
    var $_additional_data = [];
   
    /**
     * Параметры работы js поля
     * 
     * @var array
     */
    var $_js_options = [];
    
   /**
    * Конструктор класса
    *
    * @param string $elementName - Имя поля
    * @param string $elementLabel - Заголовок поля
    * @param array $attributes - Массив атрибутов поля автозаполнения
    * @param array $options - Параметры AJAX-запроса
    */
    function __construct($elementName = null, $elementLabel = null, $attributes = null, $options=null)
    {
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_autocomplete';
        $this->_elementName = $elementName;
    }
    
   /**
    * Конструктор класса
    *
    * @param string $elementName - Имя поля
    * @param string $elementLabel - Заголовок поля
    * @param array $attributes - Массив атрибутов поля автозаполнения
    * @param array $options - Параметры AJAX-запроса
    */
    public function MoodleQuickForm_dof_autocomplete($elementName = null, $elementLabel = null, $attributes = null, $options=null)
    {
        GLOBAL $DOF;
        
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_appendName       = true;
        $this->_type             = 'dof_autocomplete';
        $this->_elementName      = $elementName;
        
        // Подключение JS-поддержки поля
        $DOF->modlib('widgets')->js_init('autocomplete');
        
        // Установка значений по-умолчанию
        $this->_do_for_hidden = '**#empty';
        
        // @TODO переименовать во всех обращениях к autocomplete 'option' в 'default'
        // удалить этот фрагмент кода после того как все вызовы будут переделаны
        if ( isset($options['option']) )
        {
            $this->_id_for_hidden = key($options['option']);
            $this->_do_for_hidden = '**#choose';
            $this->_text          = current($options['option']);
            unset($options['options']);
        }
        // Вставка значений по умолчанию (новая, старую удалить)
        if ( isset($options['default']) )
        {
            $this->_id_for_hidden = key($options['default']);
            $this->_do_for_hidden = '**#choose';
            $this->_text          = current($options['default']);
            unset($options['default']);
        }
        
        // @TODO избавиться от использования optional_param(), после того как все обращения к
        // dof_autocomplete станут передавать подразделение. После этого удалить это условие
        if ( ! isset($options['departmentid']) )
        {
            $options['departmentid'] = optional_param('departmentid',0,PARAM_INT);
        }
        
        // Настройка для расширенного автокомплита с возможностью создать, 
        // переименовать и удалить значение прямо в нем
        if ( empty($options['extoptions']) )
        {
            $options['extoptions'] = new stdClass();
        }
        $this->extoptions = $options['extoptions'];
        unset($options['extoptions']);
        
        // Дополнительные данные для передачи по AJAX
        if ( isset($options['additional_data']) )
        {
            $this->_additional_data = $options['additional_data'];
            unset($options['additional_data']);
        }
        
        // Параметры работы поля
        if ( isset($options['js_options']) )
        {
            $this->_js_options = $options['js_options'];
            unset($options['js_options']);
        }
        
        // Обязательные параметры для формирования AJAX запроса
        if ( isset($options['plugintype']) &&
             isset($options['plugincode']) &&
             isset($options['sesskey'])    &&
             isset($options['querytype'])  &&
             isset($options['departmentid']) 
           ) 
        {
            if ( ! isset($options['type']) )
            {// Установка типа запроса по-умолчанию
                $options['type'] = 'autocomplete';
            }
            $this->_options = json_encode($options);
        } else 
        {// Нет обязательных параметров
            dof_debugging('dof_autocomplete required options is missing', DEBUG_DEVELOPER);
            print_error('dof_autocomplete required options is missing');
        }
        
    }

    /** 
     * Создание элементов поля
     * 
     * text  : элемент текс с значениями по умолчанию(если есть)
     * hidden: хранит в себе id выбранного элемента
     * html  : вспомогат элемент, для вставки js-кода
     * 
     **/
    function _createElements() 
    {
        // Получение атрибутов текстового поля         
        $attributes = $this->getAttributes();
        
        // Дополнение атрибутов
        $attributes['id'] = "id_".$this->_elementName;
        $attributes['value'] = $this->_text;
        
        // Формирование полей
        $this->_elements = [];
        $this->_elements[] = @MoodleQuickForm::createElement('text', $this->_elementName, null, $attributes);
        // @TODO удалить старый элемент '_old_hidden_id' после переработки всех форм, использующих dof_autocomplete
        // оставить только один hidden, который называется 'id'
        $this->_elements[] = @MoodleQuickForm::createElement('hidden', 'id_autocomplete', $this->_id_for_hidden,
                    ['id'=> $this->_elementName.'_old_hidden_id']);
        $this->_elements[] = @MoodleQuickForm::createElement('hidden', 'id', $this->_id_for_hidden,
                    ['id'=> $this->_elementName.'_hidden_id']);
        $this->_elements[] = @MoodleQuickForm::createElement('hidden', 'do', $this->_do_for_hidden,
                    ['id'=> $this->_elementName.'_hidden_do']);
        $this->_elements[] = @MoodleQuickForm::createElement('html', $this->get_js());

        foreach ( $this->_elements as $element )
        {
            if ( method_exists($element, 'setHiddenLabel') )
            {
                $element->setHiddenLabel(true);
            }
        }
    }   
    
    
    /**
	 * Рендеринг элементов поля
     */
    function toHtml() 
    {
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);
        return $renderer->toHtml();
    }
    
    function accept(&$renderer, $required = false, $error = null) 
    {
        $renderer->renderElement($this, $required, $error);
    }
    
    /**
     * Сформировать скрипт JS-поддержки поля автозаполнения 
     */
    function get_js()
    {
        global $DOF;
        
        $additional_fields = json_encode($this->_additional_data);
        $additionaljs = $this->get_ext_values($this->extoptions);
        $ajaxurl = $DOF->url_modlib('widgets', '/json.php');
        // Переопределение параметров работы
        $delay = 400;
        if ( isset($this->_js_options['delay']) )
        {// Изменить задержку перед выполнением запроса (мс)
            $delay = (int)$this->_js_options['delay'];
        }
        $stringminlength = 3; 
        if ( isset($this->_js_options['string_minlength']) )
        {// Изменить минимальный лимит исполнения запроса
            $stringminlength = (int)$this->_js_options['string_minlength'];
        }
        
        $js = '
            <script type="text/javascript">
                $(document).ready(function() {
             	
                    // Формирование списка автозаполнения
                    function process(data) {
            		    var json = $.parseJSON(data);
            		    var variants = [];
                        '.$additionaljs.'
        			    for( var key in json ) {
                            
                    	   variants.push( 
                    	   {
                               value: json[key]["name"],
                    		   label: json[key]["name"],
                    		   id: json[key]["id"],
                    		   do: \'**#choose\'
    					   });
                        } 
    				    return variants;
				    }
                            
                    // Формирование дополнительных данных для передачи
                    function get_additional()
                 	{
                		var additional = {}; 
                        var fields = '.$additional_fields.';
                        for ( key in fields ) {
                            var selector = "input[name=\'" + fields[key] + "\']";
                            additional[key] = $(selector).val();
                        }
            			return additional;
    				}
                    
                    // Установка значений по умолчанию для полей
                    function set_default()
                 	{
                		$("#'.$this->_elementName.'_hidden_id").val("'.$this->_id_for_hidden.'");
                        $("#'.$this->_elementName.'_hidden_do").val("'.$this->_do_for_hidden.'");
                        $("#'.$this->_elementName.'_old_hidden_id").val("'.$this->_id_for_hidden.'");
    				}
                            
                    // Поле для формирования автозаполнения       
                    var input = $("#id_'.$this->_elementName.'");
                        
				    // Инициализация скрипта поддержки поля автозаполнения
                    input.autocomplete(
                    {
                        source: function(request, response) {
                          	var jsondat = '.$this->_options.';
                          	jsondat.additional = get_additional();
                          	if ( request ) {
                          	    jsondat.data = request.term;
                          	} else {
                          	    jsondat.data = "";
                          	}
                          	if ( ! jsondat.data ) {
                          	    set_default();
                          	}
                          	    
                         	var id = parseInt(jsondat.data);
                          	
                         	if ( jsondat.data.length >= '.$stringminlength.' || ( ! isNaN(id) && id > 0 ) )
                         	{
                         		$.post("'.$ajaxurl.'", jsondat, function(data) {
                             		var a = process(data);
                             		response(a);
                         		    return;
                             	});
                            } 	 
                         	response("");
                            return;
                     	},
                 	    delay : '.$delay.',
                 	    minLength : 0,
                        select : function(event, ui) { 
                            $("#'.$this->_elementName.'_hidden_id").val(ui.item.id);
                            $("#'.$this->_elementName.'_hidden_do").val(ui.item.do);
                            $("#'.$this->_elementName.'_old_hidden_id").val(ui.item.id);
					    }
                    });
                    input.click(function() {
                        $(this).autocomplete("search");
                    });
                    input.change(function() {
                        if ( $(this).val() == "" ) {
                            set_default();
                        }
                    });
        	   }
           )
        </script>';
        
        return $js;
    }

    protected function get_ext_values($options)
    {
        global $DOF;
        $values = '';  
        $el_value = '$("#id_'.$this->_elementName.'").val()';
        if ( isset($options->empty) )
        {
            $values .= ' variants.push( 
                     { 
                         value: \'\',
                         label: \''.$DOF->get_string('autocomplete_empty', 'widgets', null, 'modlib').'\',
                         id: \'\',
                         do: \'**#empty\'
                     });';
        }
        if ( isset($options->create) )
        {
            $values .= ' variants.push( 
                     { 
                         value: '.$el_value.',
        
                         label: \''.$DOF->get_string('autocomplete_create', 'widgets', null, 'modlib').'\'+\' \'+ '.$el_value.',
                         id: \'\', 
                         do: \'**#create\'
                     });';
        }
        if ( isset($options->rename) )
        {
            $values .= ' variants.push( 
                     { 
                         value: '.$el_value.',
                         label: \''.$DOF->get_string('autocomplete_rename', 'widgets', null, 'modlib').'\'+\' \'+ '.$el_value.',
                         id: \''.$options->rename.'\', 
                         do: \'**#rename\',
                     });';
        }
        return $values;
    }
    
}
 