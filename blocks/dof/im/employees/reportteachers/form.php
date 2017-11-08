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

// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_reportteacher_short extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {    
        // делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle');
        $options = array();
        $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+10*365*24*3600,'%Y');
        $options['optional']  = false;
        $mform->addElement('date_selector', 'begindate', 
            $this->dof->modlib('ig')->igs('from').':',$options);
        $mform->setType('begindate', PARAM_INT);
        $mform->addElement('date_selector', 'enddate', 
            $this->dof->modlib('ig')->igs('to').':',$options);
        $mform->setType('enddate', PARAM_INT);

        $mform->addElement('date_time_selector', 'crondate', 
            $this->dof->get_string('crondate','employees').':',$options);
        
        $mform->addElement('submit', 'buttonshort', $this->dof->get_string('order_report_short','employees'));
        $mform->addElement('submit', 'buttonfull', $this->dof->get_string('order_report_full','employees'));    
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');    
    }
    
    
    function definition_after_data()
    {
        $mform = $this->_form;
        // делаем значения по умолчанию
        $date = time();
        // формируем даты начала и конца месяца 
        $dateday   = dof_usergetdate($date);
        $begintime = dof_make_timestamp(12,0,0,$dateday['mon'],1,$dateday['year']);
        $endtime   = dof_make_timestamp(12,0,0,$dateday['mon'],30,$dateday['year']);
        $mform->setDefault('begindate', $begintime);
        $mform->setDefault('enddate', $endtime);
    }
    
}

