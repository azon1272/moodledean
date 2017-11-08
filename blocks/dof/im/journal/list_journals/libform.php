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


// Подключаем библиотеки
require_once('lib.php');

// содключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_department_choose extends dof_modlib_widgets_form
{
    function definition()
    {    
        // делаем глобальные переменные видимыми
        global $DOF;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $DOF->get_string('form_departmentchoose_title','journal'));
        $mform->addElement('select', 'depid', 
            $DOF->get_string('view_department','journal').':', 
            $this->get_list_departments() );
        $mform->setType('depid', PARAM_INT);
        $mform->addElement('checkbox', 'complete_cstrems',null, 
                           $DOF->get_string('display_complete_cstream', 'journal') );
        $mform->addElement('checkbox', 'my_cstrems',null, $DOF->get_string('display_my_cstream', 'journal') );
        $mform->addElement('submit', 'buttonview', $DOF->get_string('button_view','journal'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * возвращает массив подразделений для формы
     * @return array
     */
    function get_list_departments()
    {
        // делаем глобальные переменные видимыми
        global $DOF;
        $list = array();
        //добавляем элемент "показать все подразделения"
        $list[0] = $DOF->get_string('all', 'journal');
        //получаем все подразделения
        $deps = $DOF->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $deps = $this->dof_get_acl_filtered_list($deps, $permissions);
        
        //сливаем в один массив
        return $list + $deps;  
    }
    
}

?>