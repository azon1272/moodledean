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

// Добавление уровней навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'cstreams'), $DOF->url_im('cstreams','/orderctload/list.php',$addvars));
// Права
$DOF->im('cstreams')->require_access('order');
// Выводим шапку
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

// Ловим номер страницы, если его передали
// Какое количество строк таблицы выводить на экран
$limitnumdefault = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = (int)optional_param('limitnum', $limitnumdefault, PARAM_INT);
// Начиная с какого номера записи показывать ее
$limitfrom = (int)optional_param('limitfrom', 0, PARAM_INT);

// Подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('cstreams',null,$limitnum, $limitfrom);

// Доп. параметры url 
$vars = array('limitnum' => $pages->get_current_limitnum(),
        'limitfrom' => $pages->get_current_limitfrom());

// Список всех приказов
$params = array(
    'plugintype' => 'storage',
    'plugincode' => 'cstreams',
    'code' => 'change_teacher',
    'departmentid' => $addvars['departmentid'],
    'ownerid' => null,
    'status' => array_keys($DOF->workflow('orders')->get_meta_list('real')),
);
$orders = $DOF->storage('orders')->get_listing($params,$limitfrom,$limitnum, array('dir'=>'DESC', 'id'), '*', false);

// Класс создания приказов
$changeteacher = $DOF->im('cstreams')->order('change_teacher');

// Создаем таблицу приказов
$orderstable = new dof_im_cstreams_orders_table($DOF, $orders, $changeteacher, $addvars);

// Ссылка на создание приказа
echo '<br><ul>';
echo '<li><a href="'.$DOF->url_im('cstreams','/orderctload/form_first.php?',$addvars).'">'. $DOF->get_string('order_change_teacher','cstreams') .'</a></li>';
echo '</ul>';

// Выводим таблицу
print $orderstable->show_table();

// Общее кол-во записей

$pages->count = $orders = $DOF->storage('orders')->get_listing($params, $limitfrom, $limitnum, array('dir'=>'DESC', 'id'), '*', true);

// Выводим строку со списком страниц
$pagesstring = $pages->get_navpages_list('/list.php', array_merge($vars,$addvars));
echo $pagesstring;

// Подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');

?>