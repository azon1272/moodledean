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
// подключаем библиотеки верхнего уровня
require_once('lib.php');

//проверяем доступ
$DOF->storage('positions')->require_access('view');

// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// имя дожности
$conds->name         = optional_param('name', '', PARAM_TEXT);
// код должности
$conds->code         = optional_param('code', '', PARAM_TEXT);
// id учебного подразделения в таблице departmrnts
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// статус договора
$conds->status       = optional_param('status', '', PARAM_ALPHA);
// ловим номер страницы, если его передали
// какое количество должностей выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '0', PARAM_INT);
//вывод на экран
// добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));

$DOF->modlib('nvg')->add_level($DOF->get_string('list_positions', 'employees'),
    $DOF->url_im('employees','/list_positions.php',$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатеам кладки
echo $DOF->im('employees')->print_tab($addvars,'positions'); 

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('employees',null,$limitnum, $limitfrom);
//получаем список должностей
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
$list = $DOF->storage('positions')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());                                                                               
// получаем html-код таблицы с должностей
$positions = $DOF->im('employees')->show_list_positions($list,$addvars);
// ссылка на создание Должности
// @todo пока только manager
if ( $DOF->storage('positions')->is_access('create') )
{// если есть право создавать должность
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('positions',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_position.php?id=0',$addvars).'>'.
        $DOF->get_string('new_position', 'employees').'</a>';
        echo '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_position', 'employees').
        	' <br>('.$DOF->get_string('limit_message','employees').')</span>';
        echo '<br>'.$link.'<br>';
    } 



}
if ( ! $positions )
{// не найдено ни одной должности
    print('<p align="center">(<i>'.$DOF->get_string('no_positions_found', 'employees').'</i>)</p>');
}else
{
    // выводим таблицу с должностями
    echo '<br>'.$positions;
    
    // помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
    $vars = array('limitnum'     => $pages->get_current_limitnum(),
                  'limitfrom'    => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count  = $DOF->storage('positions')->get_listing($conds,$limitfrom, $limitnum,'','*', true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list_positions.php', $vars);
    echo $pagesstring;
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>