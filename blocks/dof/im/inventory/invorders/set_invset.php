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
$DOF->modlib('nvg')->add_level($DOF->get_string('set_invset_doing', 'inventory'), $DOF->url_im('inventory','/invorders/set_invset.php',$addvars));

$count = optional_param('count',0,PARAM_INTEGER); 
$items = optional_param('items',0,PARAM_INTEGER); 

// формируем данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->depid = $addvars['departmentid'];
$customdata->count = $count;
$customdata->items = $items;
$customdata->catid = $addvars['invcategoryid'];
$path = $DOF->url_im('inventory','/invorders/set_invset.php?&count='.$count.'&items='.$items,$addvars);
$form = new dof_im_inventory_order_set_invset($path,$customdata);

// подключаем обработчик
if ( $obj = $form->process($addvars) )
{// перeопределяем для коректного отображения 
    $customdata->count = $obj->quantity;
    $customdata->items = $obj->count_item_in_invset;
    $path = $DOF->url_im('inventory','/invorders/set_invset.php?&count='.$obj->quantity.'&items='.$obj->count_item_in_invset,$addvars);
    $form = new dof_im_inventory_order_set_invset($path,$customdata);
}
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав - право создавать комплекты
$DOF->storage('invsets')->require_access('create', NULL, NULL, $addvars['departmentid']);


$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>