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
require_once($CFG->libdir . '/form/group.php');
require_once($CFG->libdir . '/formslib.php');

/**
 * Класс КАЛЕНДАРЯ, который отрисовывает календарь 
 *  
 * @package formslib
 */
class MoodleQuickForm_dof_calendar extends MoodleQuickForm_group
{
        
    /*
     * строка, содержащая вспомогат код html(js в том числе)
     * вставляемый в браузер
     */     
    var $_js = '';
    /*
     * время в unix-time(начало/конец дня соотвественно)
     */
    var $time_unix_from;
    var $time_unix_to;
    /*
     *  Время в формате дд.мм.гггг (для использования в js-коде календаря)
     */     
    var $text_from;
    var $text_to;
    var $text_today;
    var $dates;
    /*
     * Имя элемента
     */    
    var  $_elementName ='';
    /*
     * Label календаря(метка)
     */    
    var  $_elementLabel ='';
    /*
     * Массив имен, которые надо удаоить
     */     
    var $_delete = 0;
    
    /*
     * Временная зона
     */
    var $timezone = 99;
    
    /** Конструктор класса для совместимости с PHP 5.3
     * 
     * @access public
     * @param  string $elementName Element's name
     * @param  mixed  $elementLabel Label(s) for an element
     * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
     */
    function __construct($elementName = null, $elementLabel = null, $options=null)
    {
        
        $this->HTML_QuickForm_element($elementName);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_calendar';
        $this->_elementName = $elementName;
        
    }
   
   /**
    * Class constructor
    *
    * @access public
    * @param  string $elementName Element's name
    * @param  mixed  $elementLabel Label(s) for an element
    * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
    */
    function MoodleQuickForm_dof_calendar($elementName = null, $elementLabel = null, $options=null)
    {
        
        GLOBAL $DOF;
        $this->HTML_QuickForm_element($elementName);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'dof_calendar';
        $this->_elementName = $elementName;
        $this->_elementLabel = $elementLabel;
        $this->calendartype = 'one_calendar';
        $this->dates = array('00.00.00');// добавляем 0-й день, чтобы не было ошибок
        $this->hightlight_dates = [];
        $DOF->modlib('widgets')->js_init('calendar');
        
        // Установки параметров
        if ( ! empty($options) AND is_array($options) )
        {
            if ( isset($options['timezone']) AND is_float($options['timezone']))
            {
                $this->timezone = $options['timezone'];
            } else
            {
                $this->timezone = (float)99;
            }
            if ( ! empty($options['calendartype']) )
            {// тип календаря
                $this->calendartype = $options['calendartype'];
            }
            if ( !empty($options['date_from']) AND ! is_array($options['date_from']))
            {
                $this->time_unix_from = $this->get_server_timestamp($options['date_from'],true, $this->timezone);
                $this->text_from = date('d.m.Y',$this->time_unix_from);
            }else 
            {
                $this->time_unix_from = $this->get_server_timestamp(time(), true, $this->timezone);
                $this->text_from = date('d.m.Y',$this->time_unix_from);
            }
            if ( !empty($options['date_to']) AND ! is_array($options['date_to'] ) )
            {
                $this->time_unix_to = $this->get_server_timestamp($options['date_to'],false, $this->timezone);
                $this->text_to = date('d.m.Y',$this->time_unix_to);
            }else 
            {
                $this->time_unix_to = $this->get_server_timestamp(time(),false, $this->timezone);
                $this->text_to = date('d.m.Y',$this->time_unix_from);
            }
            // даты которые следует выделить
            if ( isset($options['dates']) AND is_array($options['dates']))
            {
                $this->dates = $options['dates'];
            }
            
            if ( isset($options['hightlight_dates']) AND is_array($options['hightlight_dates']))
            {
                foreach ( $options['hightlight_dates'] as $date )
                {
                    $this->dates[] = dof_userdate($date, '%d.%m.%Y', $this->timezone, true);
                }
            }
            
            // @todo - Разобраться с этой переменной и понять, зачем она
            if ( isset($options) AND is_array($options) )
            {// запомним их
            	$data = [];
            	foreach ( $options as $option )
            	{
            		if ( ! is_array($option) && ! is_object($option) )
            		{
            			$data[] = $option;
            		}
            	}
            	$this->_delete = '["'.implode('","', $data).'"]';
            }
        } else 
        {// установки по умолчанию
             $this->time_unix_from = $this->get_server_timestamp(time(),true, 99);
             $this->time_unix_to   = $this->get_server_timestamp(time(),false, 99);
             $this->text_from      = date('d.m.Y',$this->time_unix_from);
             $this->text_to        = date('d.m.Y',$this->time_unix_to);
             $this->timezone = (float)99;
        } 
        
    }
    

    /** Создаем элементы 
     * -hidden: хранят в себе выбранную дату в формате unixtime
     * -html  : вспомогат элемент, для вставки js-кода
     * 
     **/
    function _createElements() 
    {
        
        $this->_elements = array();
        $this->_elements[] = @MoodleQuickForm::createElement('html', $this->get_js());
        $this->_elements[] = @MoodleQuickForm::createElement('hidden','date_from',0,array('id'=>'id_'.$this->_elementName.'_from'));
        $this->_elements[] = @MoodleQuickForm::createElement('hidden','date_to',0,array('id'=>'id_'.$this->_elementName.'_to'));

        foreach ($this->_elements as $element){
            if (method_exists($element, 'setHiddenLabel')){
                $element->setHiddenLabel(true);
            }
        }
    }  
    
    
    /**
	*	Отрисовка элементов(и черт там голову сломает)
     **/
    function toHtml() 
    {
        
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);
        return $renderer->toHtml();
        
    }
    
    /**
     * Формирует js-скрипт и возвращает его
     */
    function get_js()
    {
        
        $js = "
        <div id='info_".$this->_elementName."' class='fitem' align=center style='display:none'>
        	<table align='center' border=0>
       			<tr align='center'> 
       				<td colspan=3 style='vertical-align:top'>".$this->_elementLabel."</td>
       			<tr>
        		<tr align=center style='vertical-align:top'> 
        			<td> <div id='".$this->_elementName."_from'></div></td>
        			<td width='50px'> &nbsp; &nbsp; </td>";
        if ( $this->calendartype == 'two_calendar' )
        {
            $js .= "<td> <div id='".$this->_elementName."_to'></div></td>";
        }
        $js .= "</tr>
        		<tr align=center> 
        			<td> c  <span id='".$this->_elementName."_data_from'> $this->text_from</span>  </td>
        			<td></td>";
        if ( $this->calendartype == 'two_calendar' )
        {
            $js .= "<td> по <span id='".$this->_elementName."_data_to'>   $this->text_to  </span>  </td>";
        }
        $js .= "</tr>
			</table>
		</div></br>
		<style type='text/css'>
            .cls2 { background-color: black !important; }
        </style> 
        <script type=\"text/javascript\">\n//<![CDATA[\n";
        
        $js .= '
        var dates = '.json_encode($this->dates).';
        $(document).ready( 
        	function()
       		{
       			// получим данные, которые надо удалить(массив)
       			var a='.$this->_delete.';
       			if ( $.isArray(a)  )
       			{
       				for( var key in a )
       				{
       					var text = a[key];
       					// запоминаем объект
       					var b = document.getElementsByName(text+"[year]");
       					// прячем
       					$(b).parent().parent().remove();
       				}
       			}
       			
       			$("#info_'.$this->_elementName.'").show();
       			// устанавливаем значения в hidden поля по умаолчанию
       			$("#id_'.$this->_elementName.'_from").attr("value",'.$this->time_unix_from.');
       			// устанавливаем флаг того что в календаре используется ajax
       			$("#id_'.$this->_elementName.'_from").append("<input type=hidden name=\'_'.$this->_elementName.'_has_ajax\' value=1>");';
		if ( $this->calendartype == 'two_calendar' )
		{
		    $js .= '$("#id_'.$this->_elementName.'_to").attr("value",'.$this->time_unix_to.');';
		}
       	$js .= '// устанавливаем русский язык
				$.datepicker.setDefaults($.datepicker.regional[\'ru\']);
				// устанавливаем сегодняшнюю дату и по умолчанию выбранный диапазон
				show_calendar("'.$this->_elementName.'","'.$this->text_from.'","'.$this->text_to.
				'","'.$this->text_today.'")
				
		})';
        $js .= "\n //]]>\n</script>";        
        
        return $js;
    }
    
    function accept(&$renderer, $required = false, $error = null) 
    {
        $renderer->renderElement($this, $required, $error);
    }
    
    /** Преобразовать полученные из календаря данные
     *
     * @param array $submitValues
     * @param bool $assoc
     * @return array
     */
    function exportValue(&$submitValues, $assoc = false)
    {
        
        // Данные интервала приходят в формате начала дня (00:00) по временной зоне сервера
        
        if ( $this->calendartype == 'two_calendar' )
        {// Двойной календарь. Формирование интервала
            // Получение выбранных дат
            $serverdatestart = getdate($submitValues[$this->getName()]['date_from']);
            $serverdateend = getdate($submitValues[$this->getName()]['date_to']);
            
            // Формирование timestamp на основе временной зоны пользователя
            $userdatestart = dof_make_timestamp(
                $serverdatestart['year'],
                $serverdatestart['mon'],
                $serverdatestart['mday'],
                0,
                0,
                0,
                $this->timezone
            );
            $userdateend = dof_make_timestamp(
                $serverdateend['year'],
                $serverdateend['mon'],
                $serverdateend['mday'],
                23,
                59,
                59,
                $this->timezone
            );
        } else 
        {// Единичный календарь
            $serverdatestart = getdate($submitValues[$this->getName()]['date_from']);
            // Формирование timestamp на основе временной зоны пользователя
            $userdatestart = dof_make_timestamp(
                $serverdatestart['year'],
                $serverdatestart['mon'],
                $serverdatestart['mday'],
                12,
                0,
                0,
                $this->timezone
            );
            $userdateend = $userdatestart;
        }

        
        if ( isset($submitValues['_'.$this->getName().'_has_ajax']) )
        {// Ajax - обновление данных
            
        }

        return [
            $this->getName() => [
                'date_from' => $userdatestart,
                'date_to'   => $userdateend
            ]
        ];
        
    }
    
    /** 
     * Получить timestamp начала или конца дня по временной зоне сервера
     * 
     * @param int $time - Пользовательская метка времени
     * @param bool $begin - true, если требуется получить метку на начало дня или 
     *                      false, если требуется метка конца дня
     * @param float $timezone - Временная зона пользователя
     * 
     * return int $time - Метка времени начала или конца дня по серверу
     */
    protected function get_server_timestamp($time, $begin, $timezone = 99)
    {
        
        $userdateinfo = dof_usergetdate($time, $timezone);

        if ( $begin )
        {// Требуется метка начала дня
            $time = mktime(
                0,
                0,
                0,
                $userdateinfo['mon'], 
                $userdateinfo['mday'], 
                $userdateinfo['year']
            );
        }else 
        {// Требуется метка окончания дня
            $time = mktime(
                23,
                59,
                59,
                $userdateinfo['mon'], 
                $userdateinfo['mday'], 
                $userdateinfo['year']
            );
        }
        return $time;
        
    }
}
?>