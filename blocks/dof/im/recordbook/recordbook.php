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
 * Интерфейс зачетной книжки студента. Страница просмотра учебной недели.
 *
 * @package    im
 * @subpackage recordbook
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('libform.php');

// ПОЛУЧЕНИЕ GET-ПАРАМЕТРОВ
$programmsbcid = optional_param('programmsbcid', 0, PARAM_INT);
$date = optional_param('date', time(), PARAM_INT);

if ( empty($programmsbcid) )
{// Подписка не указана
    // Добавление ошибки
    $DOF->messages->add(
        $DOF->get_string('error_programmbc_not_set', 'recordbook'),
        'error'
    );
} else {
    // Получение подписки на программу
    $params = [
        'id' => $programmsbcid,
        'status' => array_keys($DOF->workflow('programmsbcs')->get_meta_list('real'))
    ];
    $programmbc = $DOF->storage('programmsbcs')->get_record($params);
    $addvars['departmentid'] = $programmbc->departmentid;
    if ( empty($programmbc) )
    {// Подписка не найдена
        // Добавление ошибки
        $DOF->messages->add(
            $DOF->get_string('error_programmbc_not_found', 'recordbook'),
            'error'
        );
    } else 
    {// Подписка найдена
        // Проверка прав доступа
        $access = $DOF->im('recordbook')->is_access('view_recordbook', $programmsbcid);
        if ( ! $access )
        {// Доступ запрещен
            $DOF->messages->add(
                $DOF->get_string('error_view_recordbook_access_denied', 'recordbook'),
                'error'
            );
        } else 
        {// Доступ разрешен
            // Получение договора
            $params = [
                'id' => $programmbc->contractid,
                'status' => array_keys($DOF->workflow('contracts')->get_meta_list('real'))
            ];
            $contract = $DOF->storage('contracts')->get_record($params);
            if ( empty($contract) )
            {// Договор не найден
                // Добавление ошибки
                $DOF->messages->add(
                    $DOF->get_string('error_contract_not_found', 'recordbook'),
                    'error'
                );
            }
        }
    }
}

if ( $DOF->messages->errors_exists() )
{// Критические ошибки
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'recordbook'),
    $DOF->url_im('departments','/index.php?clientid='.$contract->studentid, $addvars)
);
// Формирование GET-параметров
$addvars['programmsbcid'] = $programmsbcid;
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('lesson_schedule', 'recordbook'),
    $DOF->url_im('departments', '/index.php', $addvars)
);

// Временная зона пользователя
$usertimezone = $DOF->storage('persons')->get_usertimezone_as_number();
// Нормализация
if ( $usertimezone == 99 )
{// Часовая зона сервера
    $usertimezone = date('Z')/HOURSECS;
}
// Временная зона подразделения
$timezone = $DOF->storage('departments')->get_field($programmbc->departmentid, 'zone');
// Нормализация
if ( $timezone == 99 )
{// Часовая зона сервера
    $timezone = date('Z')/HOURSECS;
}
if ( $usertimezone != $timezone )
{// Временные зоны пользователя и подразделения подписки различаются

    $usertimezonestr = 'GMT ';
    if ( $usertimezone > 0 )
    {// Добавить +
        $usertimezonestr .= '+';
    }
    $usertimezonestr .= $usertimezone;
    
    // Добавить напоминание о несоответствии часовой зоны
    $DOF->messages->add(
        $DOF->modlib('ig')->igs('you_from_timezone', $usertimezonestr),
        'notice'
    );
}

// Формирование URL формы выбора недели для отображения занятий
$url = $DOF->url_im('recordbook','/recordbook.php', $addvars);

// Формирование дополнительных данных
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $addvars;
$customdata->date = $date;
// Форма выбора недели
$form = new dof_im_recordbook_weekselect_form($url, $customdata);
// Обработчик формы
$form->process();

// Отображение календаря
$recordbook = new dof_im_recordbook_recordbook($DOF);
$html = $recordbook->display($programmsbcid, $date);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Отображение календаря
$form->display();

// Отображение дневника
print($html);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>