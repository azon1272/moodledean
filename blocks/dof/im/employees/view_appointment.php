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
// подключаем библиотеки верхнего уровня
require_once('lib.php');
require_once('form.php');
// получаем id назначения на должность, которое будем отображать
$id      = required_param('id', PARAM_INT);
// если мы добавили предмет в список преподаваемых - то запомним его id
$pitemid = optional_param('pitemid', 0, PARAM_INT);
// если предмет добавлялся в список
$actionadd    = optional_param('add', false, PARAM_BOOL);
// если предмет удалялся из списка
$actionremove = optional_param('remove', false, PARAM_BOOL);
// ловим значение галочки "сразу же активировать"
$activate     = optional_param('activate', false, PARAM_BOOL);

// список тех, кого надо добавить в группу
$addpitemslist    = optional_param_array('addselect',    null, PARAM_RAW);
// список тех, кого надо исключить из группы
$removepitemslist = optional_param_array('removeselect', null, PARAM_RAW);
// количество часов отведенных для преподавания дисциплины
$worktime         = optional_param('worktime', 0, PARAM_INT);

//проверяем доступ
$DOF->storage('appointments')->require_access('view',$id);

if ( ! $appointment = $DOF->storage('appointments')->get($id) )
{// в базе нет такой записи
    $DOF->print_error('appointment_not_found', null, $id, 'im' ,'employees');
}
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_appointeagreement', 'employees'),
    $DOF->url_im('employees','/list_appointeagreements.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('view_appointment', 'employees'),
    $DOF->url_im('employees','/view_appointment.php?id='.$id,$addvars));
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id  = $id;
// создаем и показываем форму смены статуса
$statusform = new dof_im_employees_appointments_status_form($DOF->url_im('employees','/view_appointment.php?id='.$id,$addvars), $customdata);
$statusform->process();
// устанавливаем в форму значения по умолчанию
$statusform->set_data($appointment);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатываем вкладки 1-ого и 2-ого уровня
echo $DOF->im('employees')->print_tab( array_merge($addvars, array(
        'id' => $id) ),'appointments',true);

if ( $DOF->storage('eagreements')->is_access('create') )
{// создание договора
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('eagreements',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_eagreement_one.php?id=0',$addvars).'>'.
            $DOF->get_string('new_eagreement', 'employees').'</a>';
        echo '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_eagreement', 'employees').
        	' <br>&nbsp;&nbsp;&nbsp;&nbsp;('.$DOF->get_string('limit_message','employees').')</span>';
        echo '<br>'.$link.'<br>';
    }  
}
if ( $DOF->storage('appointments')->is_access('create') )
{// создание договора
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('appointments',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_appointment.php?id=0',$addvars).'>'.
            $DOF->get_string('new_appointment', 'employees').'</a>';
        echo '<br>'.$link.'<br><br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_appointment', 'employees').
        	' <br>&nbsp;&nbsp;&nbsp;&nbsp;('.$DOF->get_string('limit_message','employees').')</span>';
        echo '<br>'.$link.'<br><br>';
    }  
}

// выводим информацию по должности
$DOF->im('employees')->show_appointment($id,$addvars);

if ( $DOF->workflow('appointments')->is_access('changestatus') )
{// Форма смены статуса отображается только в случая наличия прав
    $statusform->display();
}

if ( $DOF->storage('appointments')->get_field($id, 'status') != 'canceled' AND 
     ($DOF->storage('teachers')->is_access('create') OR $DOF->workflow('teachers')->is_access('changestatus')) )
{// если назнначение еще не отменено и есть права создания и смены статуса назначений на предметы
    // подключаем класс для работы с назначениями на предметы
    $process = new dof_im_employees_programmitems_assigment($DOF);
    $addremoveresult = '';
    // подключаем класс двусторонних списков
    $addremove = $DOF->modlib('widgets')->addremove();
    if ( is_array($addpitemslist) AND ! empty($addpitemslist) )
    {// есть предметы которые нужно назначить учителю
        if ( $DOF->storage('teachers')->is_access('create') )
        {// есть право добавления - добавляем
            $addpitemslist = $addremove->check_add_remove_array($addpitemslist);
            $addremoveresult = $process->add_programmitem_from_appointment($appointment, $addpitemslist, $worktime);
        }else
        {// нельзя - сообщим об этом
            $addremoveresult = 'add_pitems_to_appointment_access';
        }
        // в зависимости от результата выводим сообщение
        $addremoveresult = $process->get_addremove_result_message('add', $addremoveresult);
        
    }
    if ( is_array($removepitemslist) AND ! empty($removepitemslist) )
    {// есть предметы которые нужно отписать от учителя
        if ( $DOF->workflow('teachers')->is_access('changestatus') )
        {// если есть право менять статус - отписываем предметы
            $removepitemslist = $addremove->check_add_remove_array($removepitemslist);
            $addremoveresult = $process->remove_programmitem_from_appointment($appointment, $removepitemslist);
        }else
        {// нельзя - сообщим об этом
            $addremoveresult = 'remove_pitems_from_appointment_access';
        }
        // в зависимости от результата выводим сообщение
        $addremoveresult = $process->get_addremove_result_message('remove', $addremoveresult);
    }

    // Устанавливаем надписи в форме
    $addremovestrings = new stdClass();
    $addremovestrings->addlabel    = $DOF->get_string('pitems_to_add', 'employees');
    $addremovestrings->removelabel = $DOF->get_string('pitems_available', 'employees');
    $addremovestrings->addarrow    = $DOF->modlib('ig')->igs('add');
    $addremovestrings->removearrow = $DOF->modlib('ig')->igs('delete');
    $addremove->set_default_strings($addremovestrings);
    // список предметов уже назначенных учителю
    $addremove->set_remove_list($process->get_appointment_pitems($id),$process->extradata);
    // список предметов, которые можно назначить учителю
    $addremove->set_complex_add_list($process->get_available_pitems_for_appointment($id));
    // отображаем сообщение об успешном/неуспешном назначении или отписании предметов
    echo $addremoveresult;
    // Отображаем форму
    $addremove->print_html($process->get_worktime_form());
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>