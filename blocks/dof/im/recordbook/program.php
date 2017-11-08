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
 * Экран учебной программы
 */
 // Подключаем библиотеки
require_once('lib.php');
$programsbcid = required_param('programmsbcid', PARAM_INT);
// эти параметры только для истории оценки используются
$cpassed        = optional_param('cpassed',0, PARAM_INT);
// Создаем объект, который отвечает за отрисовку экрана учебной программы
$programeditor = new dof_im_recordbook_learning_program($DOF, $programsbcid);
$contractid = $DOF->storage('programmsbcs')->get_field($programsbcid, 'contractid');
$studentid  = $DOF->storage('contracts')->get_field($contractid, 'studentid');

//вывод на экран
//добавление уровня навигации, ведущего на страницу дневника
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'recordbook'), 
    $DOF->url_im('recordbook','/index.php?clientid='.$studentid,$addvars));
// добавление ссылки на учебную программу
$DOF->modlib('nvg')->add_level($DOF->get_string('learning_program', 'recordbook'), 
    $DOF->url_im('recordbook', '/program.php?programmsbcid='.$programsbcid,$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//проверка прав доступа';
$DOF->im('recordbook')->require_access('view_recordbook', $programsbcid);

// выводим таблицу с данными по подразделению и по программе
print $programeditor->print_info_table($programsbcid);
// выводим заголовок для таблицы
$DOF->modlib('widgets')->print_heading($DOF->get_string('disciplines_list', 'recordbook'));
// выводим таблицу с данными по элементам учебной программы
print $programeditor->print_full_progitemstable($programsbcid);
// покажем историю по оценке, если есть
if ( $cpassed AND $studentid == $DOF->storage('cpassed')->get_field($cpassed, 'studentid'))
{
    echo '<br><br>'.$programeditor->show_history_cpass($cpassed, $addvars['departmentid']);    
}
$DOF->modlib('widgets')->print_heading($DOF->get_string('reoffset_disciplines', 'recordbook'));
// выводим таблицу перезачётов
print $programeditor->print_reoffset_disciplines($programsbcid);
$DOF->modlib('widgets')->print_heading($DOF->get_string('academicdebts', 'recordbook'));
// выводим таблицу с академической разницей
print $programeditor->print_academicdebts($programsbcid);
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    
?>