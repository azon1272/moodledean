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
require_once('form.php');

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_resource_delete', 'inventory'), $DOF->url_im('inventory','/invorders/resource_delete.php',$addvars));

// id просматриваемого приказа
$orderid = optional_param('orderid',0 , PARAM_INT);

// формируем данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->depid = $addvars['departmentid'];
if ( $orderid )
{// приказ уже был создан и мы вернулись сюда
    // для переоформления приказа/редактирования
    $customdata->orderid = $orderid;    
}
$path = $DOF->url_im('inventory','/invorders/resource_delete.php',$addvars);
$form = new dof_im_inventory_order_resource_delete($path,$customdata);
// подключаем обработчик
$form->process($addvars);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав
$DOF->storage('orders')->require_access('create', NULL, NULL, $addvars['departmentid']);

$form->display();


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>