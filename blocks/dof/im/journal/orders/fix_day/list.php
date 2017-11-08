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

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id учебного подразделения в таблице departmrnts
//выводятся классы с любым departmentid, если ничего не передано
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);
$addvars['sort'] = optional_param('sort','date', PARAM_TEXT);
$addvars['dir'] = optional_param('dir','desc', PARAM_TEXT);

//добавление уровня навигации
// TODO раньше тут чтояло $conds
//$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'reports'), $DOF->url_im('reports','/list.php'),$addvars);

// Получим UTC для подразделения и пользователя
$dtimezone = $DOF->storage('departments')->get_timezone(optional_param('departmentid', 0, PARAM_INT));
$utimezone = $DOF->storage('persons')->get_usertimezone_as_number();

$customdata = new stdClass();
$customdata->dof  = $DOF;
$customdata->departmentid = $addvars['departmentid'];
$customdata->timezone = $dtimezone;
if ( ! $DOF->storage('orders')->is_access('') )
{
    $DOF->storage('orders')->require_access('');
}
//выводим форму выбора даты
$depchoose = new dof_im_journal_order_fix_day_form($DOF->url_im('journal','/orders/fix_day/list.php',
                    $addvars), $customdata);

$dispay = new dof_im_orders_fix_day_display($DOF,$conds->departmentid,$addvars);    
//$reportcl = $dispay->order();
$depchoose->process();
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// @todo нормальный обработчик + сообщение для 99 зоны
if ( $dtimezone != $utimezone )
{// Временные зоны персоны и подразделения не совпадают - сообщим об этом
    print("<p align='center'>".$DOF->get_string('message_usertimezone', 'journal', dof_usertimezone($utimezone)));
    print($DOF->get_string('message_timezone_not_equal', 'journal', dof_usertimezone($dtimezone))."</p>");
}

//получаем список групп
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
// подключаем класс для вывода страниц
$sort = array();
$sort[$addvars['sort']] = $addvars['sort'];
$sort['dir'] = $addvars['dir'];
$conds->plugintype   = 'im';
$conds->plugincode   = 'journal';
$conds->code         = 'fix_day';
$conds->status       = array('requested','completed');
$pages = $DOF->modlib('widgets')->pages_navigation('journal/orders/fix_day',null,$limitnum, $limitfrom);
$list = $DOF->storage('orders')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum(),$sort);
// посчитаем общее количество записей, которые нужно извлечь
$pages->count = count($DOF->storage('orders')->get_listing($conds,null,null,$sort,true));
unset($conds->plugintype);
unset($conds->plugincode);
unset($conds->code);
if ( $DOF->storage('reports')->is_access('request_report') OR
     $DOF->storage('reports')->is_access('request_report_'.$customdata->type) )
{//проверяем полномочия на заказ отчета
    $depchoose->display();
}

// получаем html-код таблицы с группами
$reports = $dispay->get_table_list($list);


if ( ! $reports )
{// не найдено ни одной группы
    print('<p align="center">(<i>'.$DOF->
            get_string('no_agroups_found', 'agroups').'</i>)</p>');
}else
{//есть группы
    // выводим таблицу с учебными группами
    echo '<br>'.$reports;
    
    // помещаем в массив все параметры страницы, 
    //чтобы навигация по списку проходила корректно
    $vars = array('limitnum'  => $pages->get_current_limitnum(),
                  'limitfrom' => $pages->get_current_limitfrom(),
                  'sort'      => $addvars['sort'],
                  'dir'       => $addvars['dir']);
                  
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // составим запрос для извлечения количества записей
    //$selectlisting = $DOF->storage('orders')->get_select_listing($conds);
    
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    echo $pagesstring;
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>