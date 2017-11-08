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
 * Интерфейс управления приказами. Точка входа. Выбор типа приказа(префильтр)
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once ('lib.php');
require_once ('form.php');

// Объявление формы
$formprefilter = $DOF->im('orders')->form_prefilter($addvars);
$formprefilter->process();

/* ОТОБРАЖЕНИЕ СТРАНИЦЫ */

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//Вывод сообщений только если форма была отправлена
//страница является точкой входа и ошибок при входе быть не должно
if ( $formprefilter->is_submitted() )
{
    $DOF->messages->display();
}

// Отображение формы
$formprefilter->display();

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
