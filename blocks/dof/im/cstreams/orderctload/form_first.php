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
 * Интерфейс для приказа "Передача нагрузки преподавателя" (первая страница)
 * 
 * Формируется в три экрана: на первом -
 *  Радиокнопка "Направление передачи нагрузки"
 *  Поле с автопоиском ФИО (автопоиск по должностным назначениям).
 *  Поле с автопоиском "Приказ о передаче часов"
 *  Радиокнопка "Причина"
 *  Галочка "Изменить статус должностного назначения"
 * 
 * Если пользователь вводит все данные и нажимает "Сохранить", то объект приказа создаётся в бд, и его можно увидеть в списке приказов.
 *  После этого происходит редирект на вторую страницу.
 *  Если мы переходим со страницы списка приказов на просмотр приказа, или редактирование, вызывается вторая страница (не эта).
 *
 */
// Подключаем библиотеки
require_once(dirname(realpath(__FILE__)).'/lib.php');
$departmentid  = optional_param('departmentid', 0, PARAM_INT);

// Права
$DOF->im('cstreams')->require_access('order');
if ( ! $personid = $DOF->storage('persons')->get_by_moodleid_id() )
{// Если id персоны не найден
    $errorlink = $DOF->url_im('cstreams','/orderctload/index.php',$addvars);
    $DOF->print_error('error_person', $errorlink, null, 'im', 'cstreams');
}

// Подключаем формы
require_once($DOF->plugin_path('im', 'cstreams', '/orderctload/form.php'));
// Создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof     = $DOF;
// Объявляем форму
$orderform = new dof_im_cstreams_order_change_teacher_page_one($DOF->url_im('cstreams', 
                '/orderctload/form_first.php?&departmentid='.$departmentid,$addvars), $customdata);
// Обработаем и сделаем redirect на вторую страницу
$orderform->process();

// Вывод на экран
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), 
                     $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('order_form', 'cstreams'), 
                     $DOF->url_im('cstreams','/orderctload/form_first.php'),$addvars);
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $DOF->storage('cstreams')->is_access('view') )
{// Если есть право просматривать подписки
    $orderform->display();
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>