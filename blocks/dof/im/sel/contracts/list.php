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

$conds = new stdClass();
$conds->personid = optional_param('personid', $DOF->storage('persons')->get_bu()->id, PARAM_INT);
$state = $conds->state = optional_param('state', '', PARAM_TEXT);
$conds->sellerid = 0;
$byseller = optional_param('byseller', 0, PARAM_INT);
if ( $byseller )
{//договоры для продовца
// найдем его в системе
    if ( ! $seller = $DOF->storage('persons')->get_bu() )
    {// нет - ошибка
    	$DOF->print_error("You account is not registered");
    }
    $conds->sellerid = $seller->id;
}
$conds->departmentid = optional_param('departmentid', 0, PARAM_INT);
$conds->status = optional_param('status','', PARAM_ALPHA);
// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT); 

$sort = optional_param('sort','', PARAM_ALPHA);
$cdata = new stdClass();
$cdata->dof = $DOF;
$cdata->search = $searchoption = optional_param('search', 'my_contracts', PARAM_TEXT);

// объявляем класс поиска по подразделениям
$search = new sel_contract_form_search_status($DOF->url_im('sel','/contracts/list.php',$conds), $cdata);

if( $search->is_submitted() AND ($formdata = $search->get_data()) )
{//Если была нажата кнопка "Поиск"
    //print_object($formdata->status);die;
    switch( $formdata->status )
    {
        case 'my_contracts':
            $conds->personid = $DOF->storage('persons')->get_bu()->id;
            $conds->status = ''; 
        break;
        case 'all_statuses':
        	$conds->personid = 0;
            $conds->status = ''; 
        break;
        default:
            $conds->personid = 0;
            $conds->status = $formdata->status;
    }
    $searchoption = $formdata->status;
    //print_object($conds->status);die;
}

// Доступно только менеджерам по продажам или кому можно видеть все
$DOF->im('sel')->require_access('viewcontract');

$message = '';
// объевляем класс смены подразделения
$options = array();
$change_department = new dof_im_departments_change_department($DOF,'contracts',$options);

$errors = $change_department->execute_form();
if ( $errors != 1 )
{// сработал обработчик
    if ( empty($errors) )
    {// выводим сообщение, что все хорошо
        $message = '<p style=" color:green; "><b>'.$DOF->get_string('departments_change_success', 'sel').'</b></p>';
    }else
    {// все плохо...
        $message = '<p style=" color:red; "><b>'.implode('<br>',$errors).'</b></p>';
    }
}

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('sel/contracts',null,$limitnum, $limitfrom);
$list = $DOF->storage('contracts')->get_listing($conds, $pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum(),$sort);   
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('contractlist', 'sel'), $DOF->url_im('sel','/contracts/list.php',$addvars));

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
echo "<ul>";
// лимит
if ( $DOF->storage('config')->get_limitobject('contracts',$conds->departmentid) )
{
    echo "<li><a href=\"{$DOF->url_im('sel','/contracts/edit_first.php',$addvars)}\">
        {$DOF->get_string('newcontract', 'sel')}</a></li>";
}else 
{
    echo '<li><span style="color:silver;">'.$DOF->get_string('newcontract', 'sel').
    	'<br> ('.$DOF->get_string('limit_message','sel').')</span></li>';
}

echo "</ul>";

//вывод формы поиска на экран
$search->display();

echo '<br /><br />';
echo $message;
  // подключаем класс для вывода страниц
// помещаем в массив все параметры страницы, 
//чтобы навигация по списку проходила корректно
$vars = array('limitnum'  => $pages->get_current_limitnum(),
              'limitfrom' => $pages->get_current_limitfrom(),
              'sort'      => $sort,
              'search'    => $searchoption,
              'state'     => $state);
// добавляем все необходимые условия фильтрации
$vars = array_merge($vars, (array)$conds);


if ( ! $list )
{// если указано id персоны - выведем только ее контракты
    echo '<p align="center">(<i>'.$DOF->get_string('no_contracts_found', 'sel').'</i>)</p>';
}else
{// выведем все контракты с учетом ЛИМИТА

    // Пагинация
    $pages = $DOF->modlib('widgets')->pages_navigation('sel', null, $limitnum, $limitfrom);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('contracts')->get_listing($conds, $pages->get_current_limitfrom(), $pages->get_current_limitnum(),$sort,'*',true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/contracts/list.php', $addvars);
    

    //начело формы
    echo '<form action="'.$DOF->url_im('sel','/contracts/list.php', $vars).'" method=POST name="change_department">';
    imseq_show_contracts($list,$vars,$change_department->options);
    // конец формы
    echo $change_department->get_form('contracr_person');
    echo '</form>';
    
    echo $pagesstring;
}

//print_object($searchoption);die;

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>