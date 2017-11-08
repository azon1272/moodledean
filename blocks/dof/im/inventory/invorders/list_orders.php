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
// подключаем приказ
//require_once($DOF->plugin_path('storage','invitems','/order/new_order.php'));
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'inventory'), $DOF->url_im('inventory','/invorders/list_orders.php',$addvars));
// определим подразделение
$depid = $addvars['departmentid'];
// тип отображения
$type = optional_param('type','orders',PARAM_TEXT);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав
$DOF->storage('orders')->require_access('view', NULL, NULL, $addvars['departmentid']);

// доп навигация по категориям
echo $DOF->im('inventory')->additional_nvg('/invorders/list_orders.php', $addvars);

// распечатеам кладки
echo $DOF->im('inventory')->print_tab($addvars+array('invcategoryid' => $catid),'');
 

$code = array('new_items','delete_items');
if ( $a = $DOF->im('inventory')->print_inventory_orders_list($code, $depid,null,null,$addvars) )
{
    echo $a;
}else 
{// список пуст - скажем об этом
    $message = $DOF->get_string('list_empty','inventory');
    echo $DOF->modlib('widgets')->notice_message($message);
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>