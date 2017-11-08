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

// получаем id вакансии, которую будем редактировать
$id = required_param('id', PARAM_INT);
// получаем подраздеоение, если его нужно установить по умолчанию
$departmentid = optional_param('departmentid', 0, PARAM_INT);
// получаем должность, если ее нужно установить по умолчанию
$positiontid  = optional_param('positionid', 0, PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
// добавляем уровень навигации - заголовок "список вакансий"
$DOF->modlib('nvg')->add_level($DOF->get_string('list_schpositions', 'employees'),
    $DOF->url_im('employees','/list_schpositions.php',$addvars));
if ( ! $id )
{// создаем новую вакансию
    //проверяем доступ
    $DOF->storage('schpositions')->require_access('create');
    $schposition = new stdClass();
    if ( $departmentid OR $positiontid )
    {// если заданы значения по умолчанию - установим их в форму
        $schposition->dept_and_position = array($departmentid, $positiontid);
    }
    $schposition->departmentid = $addvars['departmentid'];
    // добавляем уровень навигации - заголовок "создание вакансии"
    $DOF->modlib('nvg')->add_level($DOF->get_string('new_schposition', 'employees'),
        $DOF->url_im('employees','/edit_schposition.php?id='.$id,$addvars));
}else
{// редактируем существующую вакансию
    //проверяем доступ
    $DOF->storage('schpositions')->require_access('edit',$id);
    // добавляем уровень навигации - заголовок "редактирование вакансии"
    $DOF->modlib('nvg')->add_level($DOF->get_string('edit_schposition', 'employees'),
        $DOF->url_im('employees','/edit_schposition.php?id='.$id,$addvars));
    if( ! $schposition = $DOF->storage('schpositions')->get($id) )
    {// в базе нет такой записи
        $DOF->print_error($DOF->get_string('schposition_not_found', 'employees', $id));
    }
    $schposition->worktime = round($schposition->worktime, 2);
}

$customdata = new stdClass();
$customdata->dof = $DOF;

if ( $id AND $schposition->status == 'canceled' )
{// удаленную вакансию нельзя редактировать
    $form = new dof_im_employees_schposition_edit_form(null, $customdata, 'post', null, null, false);
}else
{// остальные редактировать можно
    // создаем объект формы
    $form = new dof_im_employees_schposition_edit_form(null, $customdata);
}

// подключаем обработчик
require_once($DOF->plugin_path('im', 'employees', '/process_schposition.php'));
// устанавливаем данные по умолчанию

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$form->set_data($schposition);
// отображаем форму
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>