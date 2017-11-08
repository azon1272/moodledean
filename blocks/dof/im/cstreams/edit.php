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
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
$cstreamid = optional_param('cstreamid', 0, PARAM_INT);
$default = new stdClass;
$default->ageid = optional_param('ageid', 0, PARAM_INT);
$default->departmentid = optional_param('departmentid', 0, PARAM_INT);
$default->programmid = optional_param('programmid', 0, PARAM_INT);
$default->programmitemid = optional_param('programmitemid', 0, PARAM_INT);
$default->appointmentid = optional_param('appointmentid', 0, PARAM_INT);
$default->pitemteacher = array($default->programmid, $default->programmitemid, $default->appointmentid);
//проверяем доступ
if ( $cstreamid )
{//проверка права редактировать поток
    if ( ! $DOF->storage('cstreams')->is_access('edit/plan', $cstreamid) ) 
    {// нельзя редактировать черновик - проверим, можно ли вообще редактировать
        $DOF->storage('cstreams')->require_access('edit', $cstreamid);
    }
}else
{//проверка права создавать поток
    $DOF->storage('cstreams')->require_access('create');
}

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
if ( $DOF->storage('cstreams')->is_exists($cstreamid) OR ($cstreamid === 0) )
{// если период есть или id периода не передано
    // загружаем форму
    $form = $DOF->im('cstreams')->form($cstreamid);
    if ( $cstreamid === 0 )
    {
        $form->set_data($default);
    }

    if ( $cstreamid == 0 )
    {//добавляем уровень навигации для создания потока 
        $DOF->modlib('nvg')->add_level($DOF->get_string('newcstream', 'cstreams'), 
                             $DOF->url_im('cstreams','/edit.php?cstreamid='.$cstreamid),$addvars);
    }else
    {//добавляем уровень навигации для редактирования потока
        $programmitemid = $DOF->storage('cstreams')->get_field($cstreamid, 'programmitemid');
        $progitem = $DOF->storage('programmitems')->get($programmitemid);
        $DOF->modlib('nvg')->add_level($progitem->name.'['.$progitem->code.']', 
                             $DOF->url_im('programmitems','/view.php?pitemid='.$progitem->id),$addvars);
        $DOF->modlib('nvg')->add_level($DOF->storage('cstreams')->get_field($cstreamid, 'name'), 
                             $DOF->url_im('cstreams','/view.php?cstreamid='.$cstreamid),$addvars);
        $DOF->modlib('nvg')->add_level($DOF->get_string('editcstream', 'cstreams'), 
                             $DOF->url_im('cstreams','/edit.php?cstreamid='.$cstreamid),$addvars);
    }
}else
{// если поток не найден, выведем ошибку
    print_error($DOF->get_string('notfoundcstream','cstreams'));
}

$error = '';

//подключаем обработчик формы
include($DOF->plugin_path('im','cstreams','/process_form.php'));

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//вывод сообщений об ошибках из обработчика
echo $error;

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>