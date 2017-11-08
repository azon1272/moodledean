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

/* 
 * Страница просмотра одного приказа
 */


// Подключаем библиотеки
require_once('lib.php');

// id просматриваемого приказа
$id = optional_param('id', 0, PARAM_INT);

if ( ! $order = $DOF->storage('orders')->get($id) )
{// указаный приказ не найден
    $DOF->print_error('order_notfound', $DOF->url_im('inventory', '', 
        array('departmentid' => $addvars['departmentid'])), $id, 'im','inventory');
}
//добавление уровня навигации (список приказов)
$DOF->modlib('nvg')->add_level($DOF->get_string('orders', 'inventory'), 
        $DOF->url_im('inventory','/invorders/list_orders.php',$addvars));
//добавление уровня навигации (отдельный приказ)
$DOF->modlib('nvg')->add_level($DOF->get_string('order_num', 'inventory', $id), 
        $DOF->url_im('inventory','/invorders/view.php',$addvars));

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав
$DOF->storage('orders')->require_access('view', $order->id, NULL, $order->departmentid);

// отображение информации о приказе
$DOF->im('inventory')->display_inventory_order($id, $addvars);


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>