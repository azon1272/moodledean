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
 * Класс поля для ввода телефона. 
 * 
 * @package    modlib
 * @subpackage widgets
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once($CFG->libdir . '/formslib.php');

class MoodleQuickForm_dof_telephone extends MoodleQuickForm_text
{
    /*
     * строка, имя элемента(в js используется для идентификации id элемента)
     */    
    var $_elementName = '';
    /**
     * @var string строка с js-кодом элемента
     */
    var $_js = '';
   
   /**
    * Class constructor (for PHP 4)
    *
    * @access public
    * @param  string $elementName Element's name
    * @param  mixed  $elementLabel Label(s) for an element
    * @param  mixed  $attributes Either a typical HTML attribute string or an associative array
    */
    function MoodleQuickForm_dof_telephone($elementName = null, $elementLabel = null, $attributes = null, $options=null)
    {
        global $DOF;
        $this->HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        // Поддержка заморозки элемента
        $this->_persistantFreeze = true;
        $this->_type = 'dof_telephone';
        $this->_elementName = $elementName;
        $this->_attributes['type'] = 'tel';
        
        // Подключение js
        $DOF->modlib('widgets')->js_init('dof_telephone');
    }
    

    function toHtml()
    {
        if ($this->_flagFrozen) 
        {
            return $this->getFrozenHtml();
        } else {
            
            return $this->_getTabs() . '<input' . $this->_getAttrString($this->_attributes) . ' />'.$this->get_js();
        }
    } 
    
    /**
     * Формирует js-скрипт и возвращает его
     **/
    function get_js()
    {
        $js = '<script type="text/javascript">'."\n";
        $js .= 'document.addEventListener("DOMContentLoaded", dof_telephone_init('.$this->_attributes['id'].'));';
        $js .= '</script>';     
        return $js;
    }
}    
?>