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
 * Здесь происходит объявление класса формы
 * для добавления нового TODO
 * на основе класса формы из плагина modlib/widgets. 
 * Подключается из init.php. 
 */

// Подключаем библиотеки
require_once('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_admin_edit extends dof_modlib_widgets_form
{

    protected $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми
        global $load;
        $this->dof = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
       
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);

        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('newtodo','admin'));
        
        // добавляем новый элемент в форму
        $myselect =& $mform->addElement('hierselect', 'testname', $this->dof->get_string('plugin_type','admin').
        										'<br>'.$this->dof->get_string('plugin_code','admin') ,null,'<br>');
        // устанавливаем для него варианты ответа
        $select1 = array( 'im' => 'im', 
        				  'storage' => 'storage',
                          'workflow' => 'workflow', 
                          'sync' => 'sync', 
                          'modlib' => 'modlib', 
                          'core' => 'core');
        $select2 = $this->get_list_previous($select1);
        $myselect->setOptions(array($select1, $select2));
        
        // код задания
        $mform->addElement('text', 'todocode', $this->dof->get_string('todocode','admin'));
        $mform->setType('todocode', PARAM_TEXT);        
        
        // дополнительный параметр
        $mform->addElement('text', 'dopparam', $this->dof->get_string('dopparam','admin',' '));
        $mform->setType('dopparam', PARAM_INT);      
        
        // Добавляем элемент формы
        $mform->addElement('select', 'readysys', $this->dof->get_string('loadsys','admin'), $load);        
        $mform->Setdefault('readysys',2);

        // дата
        $mform->addElement('date_time_selector', 'time', $this->dof->get_string('time','admin'),$this->get_year(time()));  

        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','ages'));
    }
    
    
    /** Возвращает двумерный массив типов и
     * соответствующих им кодов плагинов
     * @param array $types - список подразделений
     * @return array список кодов плагина, соответствующих данному типу
     */
    private function get_list_previous($types)
    {
        $previous = array();
        if ( ! is_array($types) )
        {//получили не массив - это значит что в базен нет ни одного подразделения
            return $previous;
        }
        foreach ($types as $type)
        {// забиваем массив данными    
            $previous[$type] = $this->get_list_value($type);
            
        }
        return $previous;
    }
    
    /** Возвращает список кодов плагина по типу плагина
     * @param int $type - id подразделения
     * @return array список кодов
     */
    private function get_list_value($type)
    {
        $code = array();
        if ( $type == 'core' )
        {// для core одоно значение 'coer'
            $codes = array('core' => 'core');
        }else
        {
            $codes = $this->dof->plugin_list_dir($type);
        }    
        foreach ( $codes as $key=>$obj )
        {
            $code[$key] = $key; 
        }
        return $code;
    }
    
    
    /**
     * Возвращает год для
     * @param $date
     * @param $begin
     * @param $new
     * @return integer
     */
    private function get_year($date, $new=true)
    {
        $dateform = array();
        $dateform['startyear'] = dof_userdate($date-1*365*24*3600,'%Y');
        $dateform['stopyear']  = dof_userdate($date+5*365*24*3600,'%Y');
        $dateform['optional']  = false;
        return $dateform;
    }
    
}
