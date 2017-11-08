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
require_once($DOF->plugin_path('im','departments','/lib.php'));
//проверяем доступ
$DOF->storage('appointments')->require_access('view');

// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id учебного подразделения в таблице departmrnts
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// id сотрудника
$addvars['personid'] = $conds->personid = optional_param('personid', null, PARAM_INT);
// ставка сотрудника
$addvars['worktime'] = $conds->worktime = optional_param('worktime', null, PARAM_INT);
// id вакансии
$addvars['schpositionid'] = $conds->schpositionid = optional_param('schpositionid', null, PARAM_INT);
// id вакансии
$addvars['combination'] = $conds->combination = optional_param('combination', null, PARAM_INT);
// статус договора
$addvars['status'] = $conds->status = optional_param('status', '', PARAM_ALPHA);
// табельный номер
$addvars['enumber'] = $conds->enumber = optional_param('enumber', '', PARAM_TEXT);
// сортировка
$conds->orderby = optional_param('orderby', 'ASC', PARAM_TEXT);
$conds->sort =  optional_param('sort', 'sortname', PARAM_TEXT);

// ловим номер страницы, если его передали
// какое количество договоров выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);
// создаем объект формы
$customdata = new stdClass;
$customdata->dof = $DOF;
$searchform = new dof_im_appointments_search_form($DOF->url_im('employees','/list_appointeagreements.php',$addvars),$customdata);
$searchform->set_data($addvars);
//вывод на экран
// добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'), 
    $DOF->url_im('employees','/list.php', $addvars));

$DOF->modlib('nvg')->add_level($DOF->get_string('list_appointeagreement', 'employees'),
    $DOF->url_im('employees','/list_appointeagreements.php',$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатеам кладки
echo $DOF->im('employees')->print_tab($addvars,'appoints'); 

// СМЕНА ПОДРАЗДЕЛНИЙ
$message = '';
// объевляем класс формы смены подразделения
$options = array();
$change_department = new dof_im_departments_change_department($DOF,'appointments',$options);
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
// Получаем результат удаления персоны
$person_delete = optional_param('deletesucsess', NULL, PARAM_INT);
if ( ! is_null($person_delete) )
{
    if ( $person_delete === 1 )
    {// Персона удалилась успешно
        $message .= '<p style=" color:green; "><b>'.$DOF->get_string('person_delete_success', 'employees').'</b></p>';
    }
    if ( $person_delete === 0 )
    {// Ошибка при удалении персоны
        $message .= '<p style=" color:red; "><b>'.$DOF->get_string('person_delete_error', 'employees').'</b></p>';
    }
}
// ВЫведем уведомления
echo $message;





// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('employees',null,$limitnum, $limitfrom);
//получаем список договоров
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
$list = $DOF->storage('appointments')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum(), false, true);
                                                                                        
// получаем html-код таблицы с периодами
$eagreements = $DOF->im('employees')->show_list_appointeagreements($list, $addvars,$change_department->options);

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
if ( $DOF->storage('appointments')->is_access('create') )
{// ссылка на создание назначения
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('appointments',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_appointment.php?id=0',$addvars).'>'.
        $DOF->get_string('new_appointment', 'employees').'</a>';
        echo '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_appointment', 'employees').
        	' ('.$DOF->get_string('limit_message','employees').')</span>';
        echo '<br>'.$link.'<br>'; 
    } 
}

if ( ! $eagreements )
{// не найдено ни одного договора
    print('<p align="center">(<i>'.$DOF->get_string('no_eagreements_found', 'employees').'</i>)</p>');
}else
{
    // помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
    $vars = array('limitnum'     => $pages->get_current_limitnum(),
                  'limitfrom'    => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    
    // начело формы
    echo '<form action="'.$DOF->url_im('employees','/list_appointeagreements.php', $vars).'" method=POST name="change_department">';
        
    // Выводим таблицу с должостными назначениями
    echo '<br>'.$eagreements;
    
    // конец формы
    echo $change_department->get_form();
    echo '</form>';
       
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('appointments')->get_listing($conds,$pages->get_current_limitfrom(), 
                        $pages->get_current_limitnum(),true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list_appointeagreements.php', $vars);
    echo $pagesstring;
}
// показываем форму поиска
$searchform->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>