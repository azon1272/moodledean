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
 * Интерфейс для приказа "Передача нагрузки преподавателя" (вторая страница)
 * 
 * Формируется в три экрана: на этом можно выбирать фильтры по периодам, семестрам
 * и преподавателям с определёнными дисциплинами
 */
// Подключаем библиотеки
require_once(dirname(realpath(__FILE__)).'/lib.php');
$departmentid  = optional_param('departmentid', 0, PARAM_INT);
$orderid       = required_param('edit', PARAM_INT);

// Права
$DOF->im('cstreams')->require_access('order');
if ( ! $personid = $DOF->storage('persons')->get_by_moodleid_id() )
{// Если id персоны не найден
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('error_person', $errorlink, null, 'im', 'cstreams');
}

// Подключаем формы
require_once($DOF->plugin_path('im', 'cstreams', '/orderctload/form.php'));
// Создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof     = $DOF;

if ( !empty($orderid) )
{
    $order = new dof_im_cstreams_teacher($DOF, $orderid);

    if ( $order->order == false )
    {// Приказ не существует
        $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
        $DOF->print_error('order_notfound', $errorlink, $orderid, 'im', 'cstreams');
    }
    if ( $order->is_signed() )
    {// Приказ уже подписан
        $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
        $DOF->print_error('order_wrote', $errorlink, $orderid, 'im', 'cstreams');
    }
    $customdata->orderid = $orderid;
    $orderform = new dof_im_cstreams_order_change_teacher_page_two($DOF->url_im('cstreams', 
                    '/orderctload/form_second.php?edit='.$orderid.'&departmentid='.$departmentid,$addvars), $customdata);
} else
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                         $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

    // Показываем ошибку
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('emptyorderid', $errorlink, null, 'im', 'cstreams');

    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    exit;
}

// Обработаем и сделаем redirect на вторую страницу
$orderform->process();

// Вывод на экран
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), 
                     $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('order_form', 'cstreams'), 
                     $DOF->url_im('cstreams','/orderctload/form_second.php?edit='.$orderid),$addvars);
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $DOF->storage('cstreams')->is_access('view') )
{// Если есть право просматривать подписки
    $orderform->display();
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>