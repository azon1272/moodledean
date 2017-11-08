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
 * Класс модального окна
 *
 * @package    modlib
 * @subpackage widgets
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_modlib_widgets_modal
{
    /**
     * Экземпляр Деканата
     * 
     * @var dof_control
     */
    var $dof;
    
    /**
     * HTML-код текста в кнопке для открытия модального окна
     * 
     * @var string
     */
    protected $label = '';
    
    /**
     * HTML-код содержимого модального окна
     *
     * @var string
     */
    protected $text = '';
    
    /**
     * HTML-код заголовка модального окна
     *
     * @var string
     */
    protected $title = '';
    
    /**
     * Дополнительные опции отображения
     *
     * @var array
     */
    protected $options = [];
    
    /** 
     * Конструктор класса
     * 
     * @param dof_control - глобальный объект $DOF 
     * @param array $options - Дополнительные опции отображения модального окна
     *      ['uniqueid'] - Имя модального окна
     *      ['show'] - Отобразить модельное окно
     */
    function __construct(dof_control $dof, $options = [])
    {
        $this->dof     = $dof;
        $this->options = $options;
        if ( ! isset($this->dof->modlib('widgets')->modalids) )
        {// Первый вызов построения модального окна
            $this->dof->modlib('widgets')->modalids = ['0'];
            $this->uniqueid = '0';
        } else 
        {// Определить текущий уникальный идентификатор модального окна
            $lastid = end($this->dof->modlib('widgets')->modalids);
            $lastid++;
            $this->uniqueid = $lastid;
        }
        $this->dof->modlib('widgets')->modalids[] = $this->uniqueid;
        
        // Добавить CSS
        $this->dof->modlib('nvg')->add_css('modlib', 'widgets', '/css/dof_modal.css', false);
    }
    
    /**
     * Установить HTML-код текста в кнопке для открытия модального окна
     * 
     * @param string $html - HTML-код текста в кнопке для открытия модального окна
     * 
     * @return bool - Результат установки
     */
    public function set_label($html = '')
    {
        if ( ! empty($html) )
        {
            $this->label = $html;
            return true;
        }
    }
    
    /**
     * Установить HTML-код содержимого модального окна
     *
     * @param string $html - HTML-код содержимого модального окна
     *
     * @return bool - Результат установки
     */
    public function set_text($html = '')
    {
        if ( ! empty($html) )
        {
            $this->text = $html;
            return true;
        }
    }
    
    /**
     * Установить HTML-код заголовка модального окна
     *
     * @param string $html - HTML-код заголовка модального окна
     *
     * @return bool - Результат установки
     */
    public function set_title($html = '')
    {
        if ( ! empty($html) )
        {
            $this->title = $html;
            return true;
        }
    }
    
    public function render()
    {
        // Инициализация генератора HTML
        $this->dof->modlib('widgets')->html_writer();
        
        // Уникальное имя модального окна
        $name = 'dof_modal_'.$this->uniqueid;
        $state = false;
        if ( isset($this->options['show']) )
        {// Установлено переопределение состояния модального окна
            $state = (bool)$this->options['show'];
        }
        
        // Формирование блока модального окна
        $html = dof_html_writer::start_span('dof_modal_wrapper');
        // Кнопка открытия
        $html .= dof_html_writer::label($this->label, $name);
        // Модальное окно
        $html .= dof_html_writer::start_div('dof_modal');
        $html .= dof_html_writer::checkbox($name, null, $state, null, ['id' => $name, 'class' => 'dof_modal_open']);
        $html .= dof_html_writer::start_div('dof_modal_modalwrap', ['aria-hidden' => 'true', 'role' => 'dialog' ]);
        $html .= dof_html_writer::label('', $name, false, ['class' => 'dof_modal_overlay']);
        $html .= dof_html_writer::start_div('dof_modal_dialog');
        $html .= dof_html_writer::start_div('dof_modal_header');
        $html .= dof_html_writer::start_tag('h2');
        $html .= $this->title;
        $html .= dof_html_writer::end_tag('h2');
        $html .= dof_html_writer::label('×', $name, false, ['aria-hidden' => 'true', 'class' => 'dof_modal_button_close']);
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::start_div('dof_modal_body');
        $html .= $this->text;
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::start_div('dof_modal_footer');
        $html .= '';
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_span();
        
        return $html;
    }
}
?>