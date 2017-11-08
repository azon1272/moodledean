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
 * Отображение истории оучения группы
 */

// Подключаем библиотеки
require_once('lib.php');
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// выводятся классы с любым programmid, если ничего не передано
$agroupid = required_param('agroupid', PARAM_INT);

//проверяем доступ
$DOF->storage('agroups')->require_access('view');


//добавление уровня навигации
// TODO раньше тут чтояло $conds
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'agroups'), 
                               $DOF->url_im('agroups','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('history_group', 'agroups'), 
                               $DOF->url_im('agroups','/history.php?agroupid='.$agroupid,$addvars));
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $DOF->modlib('widgets')->print_heading(
            $DOF->get_string('history_group', 'agroups').' &quot;'.
            $DOF->storage('agroups')->get_field($agroupid,'name').
            ' ['.$DOF->storage('agroups')->get_field($agroupid,'code').
            ']&quot;','center', 2, 'main', true);

// получаем html-код таблицы с группами
$agroups = $DOF->im('agroups')->print_table_history($agroupid);
    

// выводим таблицу с учебными группами
echo '<br>'.$agroups;
    

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);


?>