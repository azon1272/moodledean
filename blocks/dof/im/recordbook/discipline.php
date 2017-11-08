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
 * Экран дисциплины
 */
  // Подключаем библиотеки
require_once('lib.php');
// получаем id подписки на изучаемую или пройденную дисциплину
$cpassedid = required_param('cpassedid', PARAM_INT);
// создаем объект для работы с данными
$pagewriter = new dof_im_recordbook_discipline($DOF);
// получаем все id необходимые для навигации по страницам
$programmsbcid = $pagewriter->get_programmsbcid($cpassedid);
// получаем подписку на программу
$cpassed = $DOF->storage('cpassed')->get($cpassedid);

//добавление уровня навигации, ведущего на страницу дневника
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'recordbook'), 
    $DOF->url_im('recordbook','/index.php?clientid='.$cpassed->studentid,$addvars));
//печатаем шапку
$DOF->modlib('nvg')->add_level($DOF->get_string('learning_program', 'recordbook'), 
    $DOF->url_im('recordbook', '/program.php?programmsbcid='.$programmsbcid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('discipline', 'recordbook'), 
     $DOF->url_im('recordbook', '/discipline.php?cpassedid='.$cpassedid, $addvars));
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);


//проверка прав доступа';
$DOF->im('recordbook')->require_access('view_recordbook', $programmsbcid);

// выводим таблицу с общей информацией';
print $pagewriter->print_info_table($cpassedid);
// выводим заголовок для таблицы
$DOF->modlib('widgets')->print_heading($DOF->get_string('presence_and_grades', 'recordbook'));
// выводим таблицу с информацией по оценкам и посещаемости
$gradestable = $pagewriter->print_lessons_table($cpassedid);
if ( $gradestable )
{// есть таблицы с предметами - выводим ее
    print($gradestable);
}else
{// таблицы с предметами нет - выведем сообщение "нет данных"
    print('<p align="center">(<i>'.$DOF->get_string('no_data', 'recordbook').'</i>)</p>');
}
 
//выводим заголовок
$DOF->modlib('widgets')->print_heading($DOF->get_string('classmates', 'recordbook'));
// выводим таблицу с одноклассниками
$classmatestable = $pagewriter->print_classmates_table($cpassedid);
if ( $classmatestable )
{// выведем список одноклассников, если он есть
    print($classmatestable);
}else
{// если его нет - покажем надпись "нет одноклассников"
    print('<p align="center">(<i>'.$DOF->get_string('no_classmates', 'recordbook').'</i>)</p>');
}

//выводим подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>