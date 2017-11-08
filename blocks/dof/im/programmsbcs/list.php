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
 * Отображение списка объектов. 
 * Если соответствующие поля есть в справочнике. 
 * страница принимает параметры departmentid, status, 
 * по необходимости, могут быть другие, 
 * специфичные для справочника. Если они переданы, 
 * то выводится не весь список, а только его часть, 
 * удовлетворяющая условию. 
 * Параметры limitfrom и limitnum предназначены для ограничения количества 
 * выводимых записей. Если limitnum задан, то внизу выводится указатель страниц. 
 * Сам указатель страниц должен быть реализован в виде метода в плагине 
 * modlib widgets, которому передаются значения $code (код плагина im для ссылки), 
 * $adds, $vars, $limitfrom, $limitnum и $count, 
 * на основании которых возвращается html-код указателя. 
 * Ссылки генерируются с помощью $DOF->url_im(). 
 * Если для этого типа записей предусмотрен поиск, 
 * то форма поиска отображается над списком. 
 * Для результатов поиска действуют те же фильтры, что и для вывода списка. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once($DOF->plugin_path('im','programmsbcs','/form.php'));
require_once($DOF->plugin_path('im','departments','/lib.php'));
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
//id подразделения
$conds->departmentid   = optional_param('departmentid', null, PARAM_INT);
//id программы
$conds->programmid     = optional_param('programmid', null, PARAM_INT);
//форма обучения 
$conds->eduform        = optional_param('eduform', null, PARAM_TEXT);
//номер периода
$conds->agenum         = optional_param('agenum', null, PARAM_INT);
// id группы 
$conds->agroupid       = optional_param('agroupid', null, PARAM_INT);
// id контракта 
$conds->contractid     = optional_param('contractid', null, PARAM_INT);
// статус подписки
$conds->status         = optional_param('status', '', PARAM_ALPHA);

if ($conds->contractid)
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('programmsbcscontract', 'programmsbcs'), 
                                   $DOF->url_im('programmsbcs','/list.php?contractid='.$conds->contractid),$addvars);
}

$message = '';
// объевляем класс смены подразделения
$options = array();
$change_department = new dof_im_departments_change_department($DOF,'programmsbcs',$options);
// @todo включить обработчик
//print_object($_POST);
$errors = $change_department->execute_form();
if ( $errors != 1 )
{// сработал обработчик
    if ( empty($errors) )
    {// выводим сообщение, что все хорошо
        $message = '<p style=" color:green; "><b>'.$DOF->get_string('departments_change_success', 'programmsbcs').'</b></p>';
    }else
    {// все плохо...
        $message = '<p style=" color:red; "><b>'.implode('<br>',$errors).'</b></p>';
    }
}

// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = optional_param('limitnum', $DOF->modlib('widgets')->get_limitnum_bydefault(), PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom = optional_param('limitfrom', '1', PARAM_INT);

$addvars['sort'] = optional_param('sort','', PARAM_ALPHA);

//проверяем доступ
$DOF->storage('programmsbcs')->require_access('view');


// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('programmsbcs',null,$limitnum, $limitfrom);

// получаем html-код таблицы с подписками
$list = $DOF->storage('programmsbcs')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                        $pages->get_current_limitnum(),$addvars['sort']);
//print_object($list);                        
// форма с галочками
$programmsbcs = $DOF->im('programmsbcs')->showlist($list, $conds, $change_department->options);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//покажем ссылку на создание новой подписки
if ( $DOF->storage('programmsbcs')->is_access('create') )
{// если есть право на создание подписки
    // лимит
    if ( $DOF->storage('config')->get_limitobject('programmsbcs',$conds->departmentid) )
    {
        $link = '<a href='.$DOF->url_im('programmsbcs','/edit.php',$conds).'>'.
            $DOF->get_string('newprogrammsbcs', 'programmsbcs').'</a>';
    }else 
    {    
        $link =  '<span style="color:silver;">'.$DOF->get_string('newprogrammsbcs', 'programmsbcs').
        	' <br>('.$DOF->get_string('limit_message','programmsbcs').')</span>';        
    }
     echo '<br>'.$link.'<br>';
}

// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);
//покажем ссылку на создание новой подписки
if ( $DOF->storage('reports')->is_access('view_mreports_person') AND
     ( ! empty($config->value) OR $DOF->is_access('datamanage')) )
{// если есть право на просмотр отчетов
    $link = '<a href='.$DOF->url_im('reports','/list.php',
            array('plugintype'=>'sync','plugincode'=>'mreports','code'=>'studentshort', 
                    'departmentid' => $addvars['departmentid'])).'>'.
            $DOF->get_string('shorts_students', 'programmsbcs').'</a>';
    $link .= '<br><a href='.$DOF->url_im('reports','/list.php',
        array('plugintype'=>'sync','plugincode'=>'mreports','code'=>'studentfull',
                'departmentid' => $addvars['departmentid'])).'>'.
        $DOF->get_string('fulls_students', 'programmsbcs').'</a>';
    echo $link.'<br>';
}

// помещаем в массив все параметры страницы, 
//чтобы навигация по списку проходила корректно
$vars = array('limitnum'  => $pages->get_current_limitnum(),
              'limitfrom' => $pages->get_current_limitfrom(),
              'sort'      => $addvars['sort']);

if ( ! $programmsbcs )
{// не найдено ни одной подписки
    print('<p align="center">(<i>'.$DOF->
            get_string('no_programmsbcs_found', 'programmsbcs').'</i>)</p>');
}else
{//есть подписки
    echo $message;
    // выводим таблицу с подписками

    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    //начело формы
    echo '<form action="'.$DOF->url_im('programmsbcs',"/list.php", $vars).'" method=POST name="change_department">';

    echo '<br>'.$programmsbcs;
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('programmsbcs')->get_listing($conds,$pages->get_current_limitfrom(), 
                        $pages->get_current_limitnum(), $addvars['sort'],'*',true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    
    // конец формы
    echo $change_department->get_form();
    echo '</form>';
    echo $pagesstring;
}

//создаем форму поиска
//объект данных для передачи в форму
$cdata = new stdClass();
$cdata->dof = $DOF;
//инициализация формы
$search = new dof_im_programmsbcs_search_form($DOF->url_im('programmsbcs', '/list.php', $vars), 
                $cdata, 'post');
//установка значений формы
$search->set_data($conds);
//вывод формы на экран
$search->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>