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
/*
 * просмотр одной записи из базы
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programms'), 
      $DOF->url_im('programms','/list.php'),$addvars);
// создадим массив для условий поиска
$conds = new stdClass();
// id подразделения в таблице departments
// выводятся учебные программы с любым departmentid, если ничего не передано
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// статус учебной программы. Выводятся программы 
// с любым статусом, если ничего не передано
$conds->status       = optional_param('status', null, PARAM_ALPHA);
// название учебной программы
$conds->name         = trim(optional_param('name', null, PARAM_TEXT));
// код учебной программы
$conds->code         = trim(optional_param('code', null, PARAM_TEXT));
// ловим номер страницы, если его передали
// какое количество учебных программ выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// записываем в форму объект $DOF
$customdata = new stdClass();
$customdata->dof = $DOF;
// создаем объект формы
$searchform = new dof_im_programms_search_form(null, $customdata, 'POST');
//проверяем доступ
$DOF->storage('programms')->require_access('view');
// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('programms',null,$limitnum, $limitfrom);
// получаем список учебных программ
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
$list = $DOF->storage('programms')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());

// получаем html-код таблицы с учебными программами
$programms = $DOF->im('programms')->showlist($list,$conds);

if ( $DOF->storage('programms')->is_access('create') )
{// выводим ссылку на создание учебной программы
    if ( $DOF->storage('config')->get_limitobject('programms',$conds->departmentid) )
    {
        $link = '<a href='.$DOF->url_im('programms','/edit.php',$conds).'>'.
            $DOF->get_string('newprogramm', 'programms').'</a>';
    }else 
    {    
        $link =  '<span style="color:silver;">'.$DOF->get_string('newprogramm', 'programms').
        	' <br>('.$DOF->get_string('limit_message','programms').')</span>';        
    }


    echo '<p align="left">'.$link.'</p>';
}
if ( ! $programms )
{// не найдено ни одной учебной программы
    print('<p align="center">(<i>'.$DOF->get_string('no_programms_found', 'programms').'</i>)</p>');
    // показываем форму поиска
    $searchform->display();
}else
{
    // выводим таблицу с учебными программами
    echo $programms;
    
    // помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
    $vars = array('limitnum'     => $pages->get_current_limitnum(),
                  'limitfrom'    => $pages->get_current_limitfrom());
    // добавляем в массив в массив параметры страницы
    $vars = array_merge($vars, (array)$conds);              
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('programms')->count_records_select($DOF->storage('programms')->get_select_listing($conds));
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    echo $pagesstring;
    
    // показываем форму поиска
    $searchform->display();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>