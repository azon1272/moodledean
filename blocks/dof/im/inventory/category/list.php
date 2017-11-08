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
$sort = optional_param('sort', 'name', PARAM_ALPHA);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// права
$DOF->storage('invcategories')->require_access('view',NULL,NULL,$addvars['departmentid']);

// распечатеам кладки
echo $DOF->im('inventory')->print_tab($addvars,'category'); 
// ссылка на создание
 
// каждой ссылке даем свой id, для написание автомат тестов       
echo '<a id="im_inventory_category_create" href='.$DOF->url_im('inventory','/category/edit.php',$addvars).'>'
        .$DOF->get_string('cat_create','inventory').'</a><br><br>';

// выводим список категорий
$DOF->im('inventory')->print_category_table($addvars, $sort);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>