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
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
// id дня который сейчас редактируется
$id = optional_param('id', 0, PARAM_INT);

$day = new stdClass;
if ( $id AND ! $day = $DOF->storage('schdays')->get($id) )
{// редактируемый день отсутствует в базе
    $errorlink = $DOF->url_im('schdays','/calendar.php',$addvars);
    $DOF->print_error('day_not_found', $errorlink, NULL, 'im', 'schdays');
}
if ( $addvars['departmentid'] == 0 )
{// нельзя создавать расписание не указав подразделение
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    $errorlink = $DOF->url_im('schdays','/calendar.php',$addvars);
    $DOF->print_error('error:department_not_set', $errorlink, NULL, 'im', 'schdays');
}
// создаем дополнительные данные для формы
$customdata = new stdClass();
// id подразделения (из lib.php)
$customdata->departmentid = $addvars['departmentid'];
$customdata->dof          = $DOF;
$customdata->ageid        = $addvars['ageid'];
$customdata->edit_date    = false;

// Создаем объект формы
$form = new dof_im_schdays_edit_schday_form($DOF->url_im('schdays','/edit.php',$addvars), $customdata);
// обрабатываем пришедшие данные (если нужно)
$message = $form->process();
// Устанавливаем данные по умолчанию
$form->set_data($day);

if ( $id == 0 )
{//проверяем доступ
    $DOF->storage('schdays')->require_access('create');
    $pagetitle = $DOF->get_string('new_day', 'schdays');
}else
{//проверяем доступ
    $DOF->storage('schdays')->require_access('edit', $id);
    $pagetitle = $DOF->get_string('edit_day', 'schdays');
}

// добавляем уровни навигации 
$DOF->modlib('nvg')->add_level($pagetitle, $DOF->url_im('schdays','/edit.php'), $addvars);
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// печать формы
$form->display();

echo $message;
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>