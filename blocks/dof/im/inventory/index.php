<?PHP
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

// Подключаем библиотеки
require_once('lib.php');


//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);


// доп навигация по категориям
// ТУТ СТРОГО НЕЛЬЗЯ передавать в addvars категорию

echo $DOF->im('inventory')->additional_nvg('/index.php', $addvars);

// распечатеам кладки
echo $DOF->im('inventory')->print_tab($addvars,'operation'); 

// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);

// начало блока-приказ
$DOF->modlib('widgets')->print_box_start();
// ссылка для просмотра приказов
if ( $DOF->storage('orders')->is_access('view', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_resource_all" href='.$DOF->url_im('inventory','/invorders/list_orders.php',$addvars).'>'
        .$DOF->get_string('list_orders','inventory').'</a><br><br>';
}

// ссылка для составления приказа о приходе оборкдования
if ( $DOF->storage('orders')->is_access('create', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_resource_new" href='.$DOF->url_im('inventory','/invorders/resource_new.php',$addvars).'>'
        .$DOF->get_string('order_resource_new','inventory').'</a><br>';
        
}

// ссылка на приказ об удалении оборудования
if ( $DOF->storage('orders')->is_access('create', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_resource_delete" href='.$DOF->url_im('inventory','/invorders/resource_delete.php',$addvars).'>'
        .$DOF->get_string('order_resource_delete','inventory').'</a><br><br>';
}

// ссылка на приказ формирования комплекта
if ( $DOF->storage('orders')->is_access('create', NULL, NULL, $addvars['departmentid']) )
{// есть права - покажем
    echo '<a id="im_inventory_set_invset" href='.$DOF->url_im('inventory','/invorders/set_invset.php',$addvars).'>'
        .$DOF->get_string('order_set_invset','inventory').'</a><br><br>';
}
// закрываем бокс-приказы
$DOF->modlib('widgets')->print_box_end();

if ( ! empty($config->value) OR $DOF->is_access('datamanage') )
{
    // начало блока-отчеты
    $DOF->modlib('widgets')->print_box_start();
    
    // TODO сделать права, понять что к чему
    // ОТЧЕТЫ
    echo '<a id="im_inventory_order_persons" href='.$DOF->url_im('inventory','/reports/index.php?type=persons',$addvars).'>'
        .$DOF->get_string('report_persons','inventory').'</a><br>';
    
    echo '<a id="im_inventory_order_items" href='.$DOF->url_im('inventory','/reports/index.php?type=items',$addvars).'>'
        .$DOF->get_string('report_items','inventory').'</a><br>';        
        
    // закрываем бокс-отчеты
    $DOF->modlib('widgets')->print_box_end();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);


?>