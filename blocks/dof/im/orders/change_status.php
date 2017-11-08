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
 * Страница изменения статуса приказа
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once ('lib.php');

// Получение данных о приказе
$confirmed = optional_param('confirmed', 0, PARAM_INT);
$orderid = optional_param('id', NULL, PARAM_INT);
$newstatus = optional_param('newstatus', NULL, PARAM_ALPHANUM);

// Права доступа
// $DOF->im('orders')->require_access('admnistration');

if ( is_null($orderid) )
{// Приказ для смены статуса не указан
    $DOF->messages->add(
        $DOF->get_string('error_element_id', 'orders').': '.
        $DOF->get_string('error_required_field', 'orders'), 
        'error'
    );
} else
{// Указан приказ
    // Получить приказ
    $order = $DOF->storage('orders')->get_record(['id' => $orderid]);
    if ( ! empty($order) )
    {// Приказ получен
        // Добавление GET-параметров
         $addvars['id'] = $orderid;
            
        // Проверка доступа
        if ( ! $DOF->storage('orders')->is_access('edit', $orderid) )
        {
            $DOF->messages->add(
                $DOF->get_string('error_order_edit_access_denied', 'orders'),
                'error'
            );
        }
    } else
    {// Приказ не получен
        $DOF->messages->add(
            $DOF->get_string('error_element_id', 'orders').': '.
            $DOF->get_string('error_field_value', 'orders'),
            'error'
        );
    }
}
if ( ! is_null($newstatus) )
{// Указан новый статус
    $addvars['newstatus'] = $newstatus;
}

/* ОТОБРАЖЕНИЕ СТРАНИЦЫ */
$html = '';

if ( ! $DOF->messages->errors_exists() )
{// Ошибок нет
    // Получение формы смены статуса
    $formchangestatus = $DOF->im('orders')->form_change_status(array_merge($addvars, ['confirmed' => $confirmed]));
    if ( $formchangestatus->process() )
    {// Обработка прошла успешно
        $html .= $formchangestatus->get_process_result();
    }
}

// Печать шапки
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Вывод контента
echo $html;
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);