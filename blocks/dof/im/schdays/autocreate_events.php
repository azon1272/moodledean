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

$type = required_param('type', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$DOF->im('schedule')->require_access('create_schedule');

$add = array();
if ( $type == 'createweek' )
{
    $datestart    = required_param('begindate', PARAM_INT);
    $dateend      = required_param('enddate', PARAM_INT);
    $ageid        = required_param('ageid', PARAM_INT);
    $departmentid = required_param('departmentid', PARAM_INT);
    $add = array('begindate' => $datestart,
                 'enddate'   => $dateend);
}

// Добавляем уровни навигации
$DOF->modlib('nvg')->add_level($DOF->get_string($type.'_events', 'schdays'),
$DOF->url_im('schdays','/autocreate_events.php?confirm=1&type='.$type), $add + $addvars);
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $confirm )
{
    switch ( $type )
    {
        case 'createweek':
            /*$additional = new stdClass();
            $additional->datestart = $datestart;
            $additional->dateend = $dateend;
            $additional->departmentid = $addvars['departmentid'];
            $additional->ageid = $addvars['ageid'];
            $DOF->add_todo('im', 'schdays', 'auto_create_events_week', null,$additional, 1, time());
            $message = $DOF->get_string('auto_create_success', 'schdays');*/
            $create = new dof_im_schdays_schedule_manager($DOF,$addvars);
            $message = $create->create_days($ageid, $departmentid, $datestart, $dateend);
            break;
        case 'create':  // Автоматическое создание расписания на существующие дни
            $DOF->add_todo('im', 'schdays', 'auto_create_events', null,(object) $addvars, 2, time());
            $message = $DOF->get_string('auto_create_success', 'schdays');
            break;
        case 'delete':  // Автоматическое удаление расписания из существующих дней
            $DOF->add_todo('im', 'schdays', 'auto_delete_events', null,(object) $addvars, 2, time());
            $message = $DOF->get_string('auto_delete_success', 'schdays');
            break;
        case 'update':  // Автоматическое обновление расписания на существующие дни
            $DOF->add_todo('im', 'schdays', 'auto_update_events', null,(object) $addvars, 2, time());
            $message = $DOF->get_string('auto_update_success', 'schdays');
            break;

        // Некорректный тип действия
        default: $message = $DOF->get_string('error:nonexistent_type', 'schdays');
    }
    $link = $DOF->url_im('schdays','/calendar.php',$addvars);
} else
{
    $message = $DOF->get_string('autocreate_sure_'.$type, 'schdays');
    $link = $DOF->url_im('schdays','/autocreate_events.php?confirm=1&type='.$type, $add + $addvars);
}

echo $DOF->modlib('widgets')->button($message, $link);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>