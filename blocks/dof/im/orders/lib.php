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
 * Базовые функции плагина
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once (dirname(realpath(__FILE__)) . "/../lib.php");

// Добавление таблицы стилей
$DOF->modlib('nvg')->add_css('im', 'orders', '/styles.css');

// Проверка доступа
if ( ! $DOF->storage('orders')->is_access('view') )
{// Доступа нет
    $DOF->messages->add(
        $DOF->get_string('error_access_denied', 'orders'), 'error');
}

// ПОЛУЧЕНИЕ GET-ПАРАМЕТРОВ
// Получение числа записей на странице по-умолчанию
$limitnumdefault = (int) $DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $limitnumdefault, PARAM_INT);
// Получение смещения
$limitfrom = optional_param('limitfrom', '1', PARAM_INT);

// Тип плагина
$ptype = optional_param('ptype', null, PARAM_ALPHANUM);
// Код плагина
$pcode = optional_param('pcode', null, PARAM_ALPHANUMEXT);
if ( $plugininfo = explode("_", $pcode) and count($plugininfo) > 1 )
{
    $pcode = $plugininfo[1];
}
// Код приказа
$code = optional_param('code', null, PARAM_ALPHANUMEXT);

// Установка статуса отображаемых приказов по-умолчанию
$defaultstatus = implode(',',array_keys($DOF->workflow('orders')->get_meta_list('actual')));
// Статусы приказа
$status = optional_param('o_status', $defaultstatus, PARAM_TAGLIST);
// Значения статусов, прошедшие проверку
$statusintersect = array_intersect(explode(',',$status), 
    array_keys($DOF->workflow('orders')->get_list()));

// Проверка общих для плагина параметров
$notfound = false;
if ( ! is_null($ptype) and (string) $ptype != '0' and
     ! in_array($ptype, array_keys($DOF->storage('orders')->get_list_ptypes())) )
{// Тип плагина передан, но не прошел проверку на доступность
    $notfound = true;
}
if ( ! is_null($pcode) and (string) $pcode != '0' and
     ! in_array($pcode, array_keys($DOF->storage('orders')->get_list_pcodes($ptype))) )
{// Код плагина передан, но не прошел проверку на доступность
    $notfound = true;
}
if ( ! is_null($code) and (string) $code != '0' and
     ! in_array($code, array_keys($DOF->storage('orders')->get_list_codes($ptype, $pcode))) )
{// Код приказа передан, но не прошел проверку на доступность
    $notfound = true;
}
if ( $notfound )
{// Некоторые данные не прошли проверку на доступность
    $DOF->messages->add(
        $DOF->get_string('error_list_orders_empty_list', 'orders'),
        'message'
    );
}

if ( ! $DOF->messages->errors_exists() )
{// Ошибок не обнаружено
    // Формирование массива GET-параметров для ссылок
    $addvars = array_merge($addvars, [
            'ptype' => $ptype,
            'pcode' => $pcode,
            'code' => $code,
            'o_status' => $status,
            'limitnum' => $limitnum,
            'limitfrom' => $limitfrom
        ]
    );
} else 
{// Есть ошибки в ходе получения данных
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Инициализация генератора HTML
$DOF->modlib('widgets')->html_writer();

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('nvg_prefilter_orders', 'orders'), 
    $DOF->url_im('orders', '/index.php', $addvars)
);