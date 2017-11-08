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

// категория
$catid = optional_param('invcategoryid',null,PARAM_INT);


// создадим массив, который хранит кол-во элементов в той или иной вкладке
$count_tab = array();
// тип отображения
$display = array('all','free','in_set','n_a');
foreach ( $display as $value )
{
    $conds = $addvars;
    $conds['displaytype'] = $value; 
    $count_tab[$value] = $DOF->storage('invitems')->get_listing($conds,null,null,'','*',true);    
}

// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// доп навигация по категориям
echo $DOF->im('inventory')->additional_nvg('/items/index.php', $addvars);

// распечатеам кладки
echo $DOF->im('inventory')->print_tab($addvars,'items');
// Второй уровень вкладок - фильтр оборудования по статусу
echo $DOF->im('inventory')->print_item_tabs($addvars, 'operation_items', $count_tab); 


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
// отчеты
if ( $DOF->storage('reports')->is_access('view_inventory',NULL,NULL,$addvars['departmentid']) AND
    (! empty($config->value) OR $DOF->is_access('datamanage')) )
{
    echo '<a id="im_inventory_order_persons" href='.$DOF->url_im('inventory','/reports/index.php?type=persons',$addvars).'>'
   	 .$DOF->get_string('report_persons','inventory').'</a><br>';
}   	 

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>