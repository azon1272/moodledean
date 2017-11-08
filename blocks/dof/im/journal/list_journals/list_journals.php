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
 * Страница списка журналов.
 * Если передан id подразделения, то выводим журналы 
 * только этого подразделения. Иначе - все журналы.
 * 1 получаем список подразделений.
 * 2 Получаем список программ, которые каждое реализует.
 * 3 получаем количество семестров, в течение которых 
 * реализуется каждая программа
 * 4 получаем потоки, которые в каждом семестре идут.
 * 5 получаем группы, которые эти потоки изучают
 * 6 получаем потоки вне групп
 * выводим все это.
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('libform.php');

//получаем id подразделения
$departmentid = optional_param('depid',0,PARAM_INT);
$teacherid = optional_param('teacherid',$DOF->storage('persons')->get_by_moodleid_id(),PARAM_INT);
$completecstrems = (bool) optional_param('complete_cstrems',0,PARAM_INT);
$mycstrems= (bool) optional_param('my_cstrems',0,PARAM_INT);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// добавим стили для отображения завершенных потоков
echo '<style type="text/css"> #menu A { color: gray; } </style>';
//выводим форму выбора подразделения
$depchoose = new dof_im_journal_department_choose;
$depchoose->set_data(array('depid'=>$departmentid, 
                           'complete_cstrems'=>(int) $completecstrems, 
                           'my_cstrems'=>(int) $mycstrems));
$depchoose->display();
//проверяем полномочия на просмотр информации
$DOF->im('journal')->require_access('view_schevents');
//подключаем методы получения списка журналов
$d = new dof_im_journal_listjournals($DOF);
//инициализируем начальную структуру
$d->set_data($departmentid, $teacherid, $mycstrems, $completecstrems);
//получаем список журналов
$d->get_journals();
//формируем структуру для templater
$all = new stdClass();
$all->departments = $d->get_data();
// обращаемся к шаблонизатору для вывода таблицы
$templater_package = $DOF->modlib('templater')->template('im', 'journal', $all, 'list_journals');
print($templater_package->get_file('html'));

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>