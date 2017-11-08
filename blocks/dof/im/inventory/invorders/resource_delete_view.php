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

/** Страница просмотра оборудования
 *  для удаления
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');


// id просматриваемого приказа
$id = required_param('id', PARAM_INT);

if ( ! $order = $DOF->storage('orders')->get($id) )
{// указаный приказ не найден
    $DOF->print_error('order_notfound', $DOF->url_im('inventory', '', 
        array('departmentid' => $addvars['departmentid'])), $id, 'im','inventory');
}
// оржер уже подписан - бардак, кто-то специально лезет сюда
if ( $order->exdate )
{
    $DOF->print_error('order_signdate', '', $id, 'im','inventory');    
}
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_num', 'inventory', $id), 
        $DOF->url_im('inventory','/invorders/resource_delete_view.php',$addvars));

        
// формируем данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->depid = $addvars['departmentid'];
$customdata->orderid = $id;
$path = $DOF->url_im('inventory','/invorders/resource_delete_view.php?id='.$id,$addvars);
$form = new dof_im_inventory_order_resource_delete_view($path,$customdata);        

// передадим и номер приказа
$addvars['orderid'] = $id;
$form->process($addvars);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав
$DOF->storage('orders')->require_access('view', $id, NULL, $order->departmentid);


// отобразим форму
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>