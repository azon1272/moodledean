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
require_once($DOF->plugin_path('im','cpassed','/form.php'));
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
//id подразделения
$conds->departmentid   = optional_param('departmentid', 0, PARAM_INT);
//id периода
$conds->ageid          = optional_param('ageid', null, PARAM_INT);
//id предмета 
$conds->programmitemid = optional_param('programmitemid', null, PARAM_INT);
// id подписки на предмет
$conds->programmsbcid  = optional_param('programmsbcid', null, PARAM_INT);
// id учителя
$conds->teacherid      = optional_param('teacherid', null, PARAM_INT);
// id ученика
$conds->studentid      = optional_param('studentid', null, PARAM_INT);
// id потока 
$conds->cstreamid      = optional_param('cstreamid', null, PARAM_INT);
// id группы 
$conds->agroupid       = optional_param('agroupid', null, PARAM_INT);
// статус предмето-потока
$conds->status         = optional_param('status', '', PARAM_ALPHA);
// сортировка
$sort = optional_param('sort','', PARAM_ALPHA);
// отловим вывод сообщений
$message               = optional_param('message', '', PARAM_TEXT);


// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);

//проверяем доступ
$DOF->storage('cpassed')->require_access('view');
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cpassed'), 
                     $DOF->url_im('cpassed','/list.php'),$addvars);

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('cpassed',null,$limitnum, $limitfrom);
//получаем список курсов
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.    

$list = $DOF->storage('cpassed')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum(), $sort);

$form = '';
if ( ! empty($conds->cstreamid) AND ! empty($list) )
{// есть id потока и список
    $customdata = new stdClass();
    if ( isset($conds->agroupid) )
    {// если указана группа - выведем форму добавления подписок
        $customdata->agroupid  = $conds->agroupid ;
        $customdata->cstreamid = $conds->cstreamid ;
        $customdata->dof       = $DOF;
        $pass = new dof_im_cpassed_addpass_form(null, $customdata);
    }
}
// подключим обработчик
include($DOF->plugin_path('im','cpassed','/pass_process_form.php'));                                                                            
// получаем html-код таблицы с курсами
$cpassed = $DOF->im('cpassed')->showlist($list,$conds, [
    'sortview' => true
], $addvars);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Добавление ссылки назад
$returnurl = optional_param('returnurl', null, PARAM_URL);
if ( $returnurl )
{
    $link = dof_html_writer::link(
        $returnurl,
        $DOF->get_string('back', 'cpassed'),
        ['class' => 'btn btn-primary']
    );
    // Ссылка на создание подраделения
    echo html_writer::div($link);
}
//покажем ссылку на создание подписки на курс
if ( $DOF->storage('cpassed')->is_access('create') )
{// если есть право создавать подписки
    // @todo к этой странице пока не обращаемся... запрящино
    //$link = '<a href='.$DOF->url_im('cpassed','/edit.php').'>'.$DOF->get_string('newcpassed', 'cpassed').'</a>';
    //echo '<br>'.$link.'<br>';
}


//чтобы навигация по списку проходила корректно
$vars = array('limitnum'       => $pages->get_current_limitnum(),
              'limitfrom'      => $pages->get_current_limitfrom(),
              'sort'           => $sort);


if ( ! $cpassed )
{// не найдено ни одной курса
    print('<p align="center">(<i>'.$DOF->
            get_string('no_cpassed_found', 'cpassed').'</i>)</p>');
}else
{//есть курсы
    echo '<br>'.str_replace(',', '<br>', $message);
    
    // выводим таблицу с учебными курсами
    //echo $form; 
    echo $cpassed;
    if ( ! empty($conds->cstreamid) AND ! empty($list) )
    {// есть id потока
        if ( isset($conds->agroupid) )
        {// и id группы
            // распечатаем форму добавления
            $pass->display();
        }
//         else
//         {// нет группы - форма удаления
//             if ( $DOF->workflow('cpassed')->is_access('changstatus') )
//             {// и есть право менять статус - форма массовых операций
//                 $selectoptions = [
//                     'choose option'=>'choose option',
//                     'change status'=>'change status'
//                 ];
//                 $selectoptions = [
//                     'active' => $DOF->get_string('change_status_to', 'cpassed', $DOF->get_string('status:active','cpassed',NULL,'workflow')),
//                     'suspend' => $DOF->get_string('change_status_to', 'cpassed', $DOF->get_string('status:suspend','cpassed',NULL,'workflow')),
//                     'completed' => $DOF->get_string('change_status_to', 'cpassed', $DOF->get_string('status:completed','cpassed',NULL,'workflow')),
//                     'failed' => $DOF->get_string('change_status_to', 'cpassed', $DOF->get_string('status:failed','cpassed',NULL,'workflow')),
//                     'canceled' => $DOF->get_string('change_status_to', 'cpassed', $DOF->get_string('status:canceled','cpassed',NULL,'workflow')),
//                     'move_to_other_cstream' => $DOF->get_string('move_to_other_cstream', 'cpassed')
//                 ];
//                 echo html_writer::select($selectoptions, 'operation_type');
//                 echo html_writer::empty_tag('submit',[
//                     'value' => $DOF->get_string('submit_mass_operation', 'cpassed'),
//                     'name' => 'massoperation'
//                 ]);
//                 echo html_writer::end_tag('form');
//             }
//         }
    } 
    echo $form;
    
    // помещаем в массив все параметры страницы, 

    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // посчитаем общее количество записей, которые нужно извлечь
    
    $pages->count = $DOF->storage('cpassed')->get_listing($conds,$pages->get_current_limitfrom(), 
                        $pages->get_current_limitnum(), $sort,'*',true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    echo $pagesstring;
}

//создаем форму поиска
//объект данных для передачи в форму
$cdata = new stdClass();
$cdata->dof = $DOF;
//инициализация формы
$search = new dof_im_cpassed_search_form($DOF->url_im('cpassed', '/list.php', $vars),$cdata, 'post');
//установка значений формы
$search->set_data($conds);
//вывод формы на экран
$search->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>