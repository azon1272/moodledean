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

/**
 Тут будет распологаться список всего того, 
 что мы можем сделать в данном плагине
 */

// Подключаем библиотеки
//
require_once('lib.php');
require_once($DOF->plugin_path('im','departments','/lib.php'));
//проверяем доступ
$DOF->storage('eagreements')->require_access('view');

// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// Условие выборки по id персоны
if ($personid = optional_param('personid', false, PARAM_INT))
{
	$conds->personid = $personid;
}
$conds->departmentid = $addvars['departmentid'];
$conds->orderby = optional_param('orderby', 'ASC', PARAM_TEXT);

// @todo добавить условия выборки - пока неясно какие нам нужны

// ловим номер страницы, если его передали
// какое количество записей выводить на экран
$limitnum  = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum  = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom = optional_param('limitfrom', '0', PARAM_INT);
// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'), 
        $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_eagreements', 'employees'),
    $DOF->url_im('employees','/list.php',$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатеам кладки
echo $DOF->im('employees')->print_tab($addvars,'eagreements'); 

$message = '';
// объевляем класс смены подразделения
$options = array();
$change_department = new dof_im_departments_change_department($DOF,'eagreements',$options);
// print_object($_POST);
$errors = $change_department->execute_form();
if ( $errors != 1 )
{// сработал обработчик
    if ( empty($errors) )
    {// выводим сообщение, что все хорошо
        $message = '<p style=" color:green; "><b>'.$DOF->get_string('departments_change_success', 'employees').'</b></p>';
    }else
    {// все плохо...
        $message = '<p style=" color:red; "><b>'.implode('<br>',$errors).'</b></p>';
    }
}

echo $message;
// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('employees',null,$limitnum, $limitfrom);
//получаем список сотрудников
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
// поэтому от стартового значения отнимем единицу.
$list = $DOF->storage('eagreements')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());
$employees = $DOF->im('employees')->show_list_employees($list,$addvars,$change_department->options);
if ( ! $employees )
{// не найдено ни одной должности
    print('<p align="center">(<i>'.$DOF->get_string('no_employees_found', 'employees').'</i>)</p>');
}

if ( $DOF->storage('eagreements')->is_access('create') )
{// ссылка на создание договора
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('eagreements',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_eagreement_one.php?id=0',$addvars).'>'.
        $DOF->get_string('new_eagreement', 'employees').'</a>';
        echo '<br>'.$link;
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_eagreement', 'employees').
        	' ('.$DOF->get_string('limit_message','employees').')</span>';
        echo '<br>'.$link; 
    } 
    
}
echo '<br>';

$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);
if ( $DOF->storage('reports')->is_access('view_report') 
    AND ( ! empty($config->value) OR $DOF->is_access('datamanage')) ) 
{// заказ отчетов
    echo '<a href='.$DOF->url_im('reports','/list.php',
         $addvars+array('plugintype'=>'sync','plugincode'=>'mreports','code'=>'teachershort')).'>'.
         $DOF->get_string('short_teachers', 'employees').'</a><br>';
    echo '<a href='.$DOF->url_im('reports','/list.php',
         $addvars+array('plugintype'=>'sync','plugincode'=>'mreports','code'=>'teacherfull')).'>'.
         $DOF->get_string('full_teachers', 'employees').'</a><br>';
    echo '<br>';
}


if ( $employees )
{
    // помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
    $vars = array('limitnum'     => $pages->get_current_limitnum(),
                  'limitfrom'    => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    //начело формы
    echo '<form action="'.$DOF->url_im('employees','/list.php', $vars).'" method=POST name="change_department">';
    // выводим таблицу с должностями
    echo '<br>'.$employees;
    
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count  = $DOF->storage('eagreements')->get_listing($conds,$limitfrom, $limitnum,'','*',true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    
    // конец формы
    echo $change_department->get_form('contract_employees');
    echo '</form>';
    
    echo $pagesstring;
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>