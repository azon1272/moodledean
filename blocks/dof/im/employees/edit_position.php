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
require_once($DOF->plugin_path('im', 'employees', '/form.php'));

// получаем id должности, которую будем редактировать
$id = required_param('id', PARAM_INT);
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
// добавляем уровень навигации - заголовок "список должностей"
$DOF->modlib('nvg')->add_level($DOF->get_string('list_positions', 'employees'),
    $DOF->url_im('employees','/list_positions.php',$addvars));
if ( ! $id )
{// создаем новую должность
    //проверяем доступ
    $DOF->storage('positions')->require_access('create');
    $position = new stdClass();
    $position->departmentid = $addvars['departmentid'];
    // добавляем уровень навигации - заголовок "создание должности"
    $DOF->modlib('nvg')->add_level($DOF->get_string('new_position', 'employees'),
        $DOF->url_im('employees','/edit_position.php?id='.$id,$addvars));
}else
{// редактируем существующую должность
    //проверяем доступ
    $DOF->storage('positions')->require_access('edit',$id);
    // добавляем уровень навигации - заголовок "редактирование должности"
    $DOF->modlib('nvg')->add_level($DOF->get_string('edit_position', 'employees'),
        $DOF->url_im('employees','/edit_position.php?id='.$id,$addvars));
    if( ! $position = $DOF->storage('positions')->get($id) )
    {// в базе нет такой записи
        $DOF->print_error($DOF->get_string('position_not_found', 'employees', $id));
    }
}

$customdata = new stdClass();
$customdata->dof = $DOF;

// создаем объект формы
if ( $id AND $position->status == 'canceled' )
{// отмененную должность нельзя редактировать
    $form = new dof_im_employees_position_edit_form(null, $customdata, 'post', null, null, false);
}else
{// все остальные - можно
    $form = new dof_im_employees_position_edit_form(null, $customdata);
}

// подключаем обработчик
require_once($DOF->plugin_path('im', 'employees', '/process_position.php'));
// устанавливаем данные по умолчанию
$form->set_data($position);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// отображаем форму
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>