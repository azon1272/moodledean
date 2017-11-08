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
require_once(dirname(realpath(__FILE__)).'/lib.php');

// права
$DOF->im('cstreams')->require_access('order');
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
// Выводим шапку
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

echo '<br><ul>';
echo '<li><a href="'.$DOF->url_im('cstreams','/orderctload/list.php',$addvars).'">'. $DOF->get_string('list_orders','learningorders') .'</a></li>';
echo '<li><a href="'.$DOF->url_im('cstreams','/orderctload/form_first.php?',$addvars).'">'. $DOF->get_string('order_change_teacher','cstreams') .'</a></li>';
echo '</ul>';

// подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');

?>