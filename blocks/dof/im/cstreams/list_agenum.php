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
 * Список ученбных потоков для группы
 */
// Подключаем библиотеки
require_once('lib.php');
// получаем id группы
$agroupid = required_param('agroupid', PARAM_INT);
// получаем статус
$status   = optional_param('status', '', PARAM_ALPHA);
//проверяем доступ
$DOF->storage('cstreams')->require_access('view');

// добавляем уровень навигации
$agroup = $DOF->storage('agroups')->get($agroupid);
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($agroup->name.'['.$agroup->code.']',
                     $DOF->url_im('agroups','/view.php?agroupid='.$agroupid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('agroup_cstream_list', 'cstreams'),
                     $DOF->url_im('programmitems','/list_agenum.php?agroupid='.$agroupid,$addvars));


//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Опции печати таблицы предмето-классов
$options = [];
$options['status'] = $status;
// выводим потоки по всем параллелям
print($DOF->im('cstreams')->get_table_list_agenums($agroupid, $options));

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>