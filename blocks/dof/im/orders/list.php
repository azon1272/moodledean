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
 * Страница фильтрации и вывода списка приказов.
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

if ( is_null($ptype) or (string) $ptype == '0' or is_null($pcode) or (string) $pcode == '0' or
     is_null($code) or (string) $code == '0' )
{ //ряд параметров, обязательных к заполнению не передан
    $DOF->messages->add(
        $DOF->get_string('error_fill_required_on_page', 'orders', 
            '<a href="' . $DOF->url_im('orders', '/index.php', $addvars) . '">' .
             $DOF->get_string('nvg_prefilter_orders', 'orders') . '</a>'), 'error');
}

// Строка для сбора представления
$html = '';

/* ОТОБРАЖЕНИЕ СТРАНИЦЫ */

// Отображазить страницу если нет ошибок (параметры переданы)
if ( ! $DOF->messages->errors_exists() )
{
    // Добавление уровня навигации
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('nvg_filter_orders', 'orders'), 
        $DOF->url_im('orders', '/list.php', $addvars)
    );

    // Генерация формы фильтрации для текущего класса приказа
    $formfilter = $DOF->im('orders')->form_filter($addvars);
    
    
    if ( $formfilter )
    {// Форма получена
        // Обработка формы
        if ( $formfilter->process() )
        { //все прошло успешно
            // Добавление результата обработки
            $html .= $formfilter->get_process_result();
            // Пагинация
            $pages = $DOF->modlib('widgets')->pages_navigation('orders', 
                $formfilter->get_pages_count(), $limitnum, $limitfrom);
            $html .= $pages->get_navpages_list('/list.php', $addvars);
        } else
        {// Обработка завершилась с ошибкой
            $DOF->messages->add($DOF->get_string('error_form_processing', 'orders'), 'error');
        }
    } else
    {// Генерация формы завершилась с ошибкой
        $DOF->messages->add($DOF->get_string('error_form_generation', 'orders'), 'error');
    }
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( isset($formfilter) && $formfilter )
{// Форма получена
    // Отобразить форму
    $formfilter->display();
}
// Отобразить контент
echo $html;
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
