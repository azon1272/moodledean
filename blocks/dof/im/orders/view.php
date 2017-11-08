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
 * Страница просмотра приказа
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once ('lib.php');

// Получение идентификатора приказа для отображения
$orderid = optional_param('id', NULL, PARAM_INT);

if ( is_null($orderid) )
{// Приказ для отображения не указан
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
        
        // Проверка доступа
        if ( ! $DOF->storage('orders')->is_access('view', $orderid) )
        {
            $DOF->messages->add(
                $DOF->get_string('error_order_view_access_denied', 'orders'),
                'error'
            );
        }
        
        // Добавление GET-параметров
        $addvars['id'] = $orderid;
    } else
    {// Приказ не получен
        $DOF->messages->add(
            $DOF->get_string('error_element_id', 'orders').': '.
            $DOF->get_string('error_field_value', 'orders'), 
            'error'
        );
    }
}

/* ОТОБРАЖЕНИЕ СТРАНИЦЫ */
$html = '';

if ( ! $DOF->messages->errors_exists() )
{// Ошибок нет
    if ( isset($addvars['ptype']) && 
         isset($addvars['pcode']) &&
         isset($addvars['code']) 
       )
    {// Необходимые параметры, требуемые для перехода на страницу поиска, получены
        $DOF->modlib('nvg')->add_level(
            $DOF->get_string('nvg_filter_orders', 'orders'), 
            $DOF->url_im('orders', '/list.php', $addvars)
        );
    }
    // Добавление уровня навигации
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('nvg_view_order', 'orders'), 
        $DOF->url_im('orders', '/view.php', $addvars)
    );
    
    $backurl = $DOF->url_im('orders','/view.php', $addvars);
    // Получить табицу с данными по приказу
    $view = $DOF->im('orders')->view_order(array_merge($addvars, ['backurl' => $backurl]));
    if ( $view != false )
    {// Таблица сформирована
        $html .= $view;
    } else
    {// Отображение не сформировано
        // Во время генерации формы произошла ошибка
        $DOF->messages->add($DOF->get_string('error_view_generation', 'orders'), 'error');
    }
    
    // Генерация формы смены статуса
    $formchangestatus = $DOF->im('orders')->form_change_status($addvars);
    if ( $formchangestatus )
    {// Форма получена
        $html .= $formchangestatus->render();
    } else
    {// Во время генерации формы произошла ошибка
        $DOF->messages->add($DOF->get_string('error_form_generation', 'orders'), 'error');
    }
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Вывод контента
print($html);
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
