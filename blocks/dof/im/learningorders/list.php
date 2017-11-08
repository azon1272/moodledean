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

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('page_main_name', 'learningorders'), $DOF->url_im('learningorders','/index.php'),$addvars);

// класс ордера
//require($DOF->plugin_path('im','learningorders','/orders/transfer/init.php'));
// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'learningorders'), $DOF->url_im('learningorders','/list.php',$addvars));
// права
$DOF->im('learningorders')->require_access('order');
// Выводим шапку
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = (int)optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom = (int)optional_param('limitfrom', '0', PARAM_INT);

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('learningorders',null,$limitnum, $limitfrom);

// доп. параметры url 
$vars = array('limitnum' => $pages->get_current_limitnum(),
        'limitfrom' => $pages->get_current_limitfrom());

// список всех приказов
$orders = $DOF->storage('orders')->get_list_by_code('im','learningorders','transfer',$addvars['departmentid'],
        null,null,null,$limitfrom,$limitnum,null,'id');

//$transfer = new dof_im_journal_order_transfer($DOF);

$transfer = $DOF->storage('orders')->order('im','learningorders','transfer');

// создаем таблицу приказов
$orderstable = new dof_im_learningorders_orders_table($DOF, $orders, $transfer, $addvars);

// выводим таблицу
print $orderstable->show_table();

// общее кол-во записей
$pages->count = $DOF->storage('orders')->get_list_by_code('im','learningorders','transfer',$addvars['departmentid'],
        null,null,null,$limitfrom,$limitnum,null,'id',true);

// выводим строку со списком страниц
$pagesstring = $pages->get_navpages_list('/list.php', array_merge($vars,$addvars));
if ( $pagesstring )
{// если выводим строку со списком страниц, то выводим и надпись "страницы"
    print '<p align="center"><b>'.$DOF->modlib('ig')->igs('pages').':</b><br>'.
            $pagesstring.'</p>';
}

// подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');

?>