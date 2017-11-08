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

/** Параметры фильтрации */
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id учебного подразделения в таблице departmrnts
//выводятся периоды с любым departmentid, если ничего не передано
$conds->departmentid = $addvars['departmentid'];
// статус учебного периода. Выводятся периоды с любым статусом, если ничего не передано
$addvars['status'] = $conds->status = optional_param('status', '', PARAM_TEXT);
// имя учебного периода
$addvars['name'] = $conds->name = optional_param('name', '', PARAM_TEXT);
// сортировка
$addvars['sort'] = optional_param('sort','name', PARAM_TEXT);
$addvars['dir'] = optional_param('dir','asc', PARAM_TEXT);
// ловим номер страницы, если его передали
// какое количество периодов выводить на экран
$limitnum     = optional_param('limitnum', $DOF->modlib('widgets')->get_limitnum_bydefault(), PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);

/** Навигация */
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'ages'), $DOF->url_im('ages','/list.php'), $addvars);

/** Доступ */
$DOF->storage('ages')->require_access('view');

/** Формы */
$customdata = new stdClass;
$customdata->dof = $DOF;
// создаем объект формы
$searchform = new dof_im_ages_search_form($DOF->url_im('ages','/list.php',$addvars),$customdata);
$searchform->set_data($addvars);

/** Отображение */
// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('ages',null,$limitnum, $limitfrom);
//получаем список периодов
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
$display = new dof_im_ages_display($DOF,$addvars);                                                                          
// получаем html-код таблицы с периодами
list($table,$count) = $display->get_table_all($conds,
                                      $pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());
//вывод на экран
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// ссылка на создание периода
if ( $DOF->storage('ages')->is_access('create') )
{// если есть право создавать период
    if ( $DOF->storage('config')->get_limitobject('ages',$conds->departmentid) )
    {// лимит еще есть - покажем ссылку
        $link = '<a href='.$DOF->url_im('ages','/edit.php',array('departmentid'=>$conds->departmentid)).'>'.
        $DOF->get_string('newages', 'ages').'</a>';
        echo $link.'<br>';
    }else 
    {// лимит исчерпан
        $link =  '<span style="color:silver;">'.$DOF->get_string('newages', 'ages').
        	' ('.$DOF->get_string('limit_message','ages').')</span>';
        echo $link.'<br>'; 
    }    
}
if ( $DOF->im('cstreams')->is_access('viewcurriculum') )
{// если есть право просмотра учебный план учащихся
    $link = '<a href='.$DOF->url_im('cstreams','/by_groups.php',array('departmentid'=>$conds->departmentid)).'>'.
    $DOF->get_string('participants_cstreams', 'ages').'</a>';
    echo $link.'<br><br>';    
}

// выводим таблицу с учебными периодами
echo $table;

// помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
$vars = array('limitnum'     => $pages->get_current_limitnum(),
              'limitfrom'    => $pages->get_current_limitfrom(),
              'sort'      => $addvars['sort'],
              'dir'       => $addvars['dir']);
// добавляем все необходимые условия фильтрации
$vars = array_merge($vars, (array)$conds);
$pages->count = $count;
// выводим строку со списком страниц
$pagesstring = $pages->get_navpages_list('/list.php', $vars);
echo $pagesstring;

// показываем форму поиска
$searchform->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>