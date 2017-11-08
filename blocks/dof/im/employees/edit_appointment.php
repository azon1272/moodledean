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
// получаем id должности, которую будем редактировать
$id = required_param('id', PARAM_INT);
$eaid = optional_param('eaid',0, PARAM_INT);
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_appointeagreement', 'employees'),
    $DOF->url_im('employees','/list_appointeagreements.php',$addvars));
if ( ! $id )
{// создаем новую должность
    // права доступа
    $DOF->storage('appointments')->require_access('create');
    $appointment = new stdClass();
    $appointment->eagreementid = $eaid;
    $appointment->departmentid = $addvars['departmentid'];
    // добавляем уровень навигации - заголовок "создание должности"
    $DOF->modlib('nvg')->add_level($DOF->get_string('new_appointment', 'employees'),
        $DOF->url_im('employees','/edit_appointment.php?id='.$id,$addvars));
}else
{// редактируем существующую должность
    // права доступа
    $DOF->storage('appointments')->require_access('edit',$id);
    // добавляем уровень навигации - заголовок "редактирование должности"
    $DOF->modlib('nvg')->add_level($DOF->get_string('edit_appointment', 'employees'),
        $DOF->url_im('employees','/edit_appointment.php?id='.$id,$addvars));
    if( ! $appointment = $DOF->storage('appointments')->get($id) )
    {// в базе нет такой записи
        $DOF->print_error($DOF->get_string('appointment_not_found', 'employees', $id));
    }
    $appointment->worktime = round($appointment->worktime, 2);
}
$error = '';
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->eagreementid = $appointment->eagreementid;
unset($appointment->eagreementid);

if ( $id AND $appointment->status == 'canceled' )
{// назначение на должность удалено - его нельзя редактировать
    $form = new dof_im_employees_appointment_edit_form(
        $DOF->url_im('employees','/edit_appointment.php?id='.$id.'eaid='.$eaid,$addvars), $customdata, 'post', null, null, false);
}else
{// назначение на должность можно редактировать
    // создаем объект формы
    $form = new dof_im_employees_appointment_edit_form(
        $DOF->url_im('employees','/edit_appointment.php?id='.$id.'eaid='.$eaid,$addvars), $customdata);
}

// устанавливаем данные по умолчанию
$form->set_data($appointment);
// подключаем обработчик
require_once($DOF->plugin_path('im', 'employees', '/process_appointment.php'));

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print '<br>'.$error.'<br>';
// отображаем форму
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>