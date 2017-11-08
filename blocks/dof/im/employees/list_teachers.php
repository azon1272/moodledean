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
// подключаем библиотеки верхнего уровня
require_once('lib.php');

// права доступа
$DOF->storage('teachers')->require_access('view');
// для какого предмета будем выводить учителей
$programmitemid = required_param('id', PARAM_INT);

if ( ! $programmitem = $DOF->storage('programmitems')->get($programmitemid) )
{// не найден предмет
    $DOF->print_error($DOF->get_string('err_programmitem_not_found', 'employees'));
}
// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
        $DOF->url_im('employees','/list.php', $addvars));

$DOF->modlib('nvg')->add_level($DOF->get_string('discipline_teachers', 'employees'),
        $DOF->url_im('employees','/teachers_list.php?id='.$programmitemid,$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// выводим заголовок для информационной таблице по дисциплине
$pitemlink = '<a href="'.$DOF->url_im('programmitems', '/view.php?pitemid='.$programmitemid,$addvars).'">'.
    $programmitem->name.'</a>';
$DOF->modlib('widgets')->print_heading($DOF->get_string('programmitem', 'employees').' &quot;'.$pitemlink.'&quot;');
// выводим таблицу с краткой информацией по предметам
$DOF->im('programmitems')->print_short_info_table($programmitemid);
// выводим заголовок для таблицы со списком учителей
$DOF->modlib('widgets')->print_heading($DOF->get_string('teachers', 'employees'));
// печатаем таблицу см преполавателями
$DOF->im('employees')->get_teachers_table_for_pitem($programmitemid,$addvars);
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>