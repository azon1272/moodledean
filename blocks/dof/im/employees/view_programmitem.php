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
require_once('form.php');
// получаем id назначения на должность, которое будем отображать
$id      = required_param('id', PARAM_INT);
// если мы добавили предмет в список преподаваемых - то запомним его id
//$pitemid = optional_param('pitemid', 0, PARAM_INT);
// если предмет добавлялся в список
$actionadd    = optional_param('add', false, PARAM_BOOL);
// если предмет удалялся из списка
$actionremove = optional_param('remove', false, PARAM_BOOL);
// ловим значение галочки "сразу же активировать"
$activate     = optional_param('activate', false, PARAM_BOOL);
//проверяем доступ
$DOF->storage('programmitems')->require_access('view',$id);

if ( ! $pitem = $DOF->storage('programmitems')->get($id) )
{// в базе нет такой записи
    $DOF->print_error('programmitem_not_found', null, $id, 'im' ,'employees');
}
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
                               $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('assign_teachers', 'employees'),
                               $DOF->url_im('employees','/view_programmitem.php',array_merge(array('id'=>$id),$addvars)));

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// выводим информацию по предмету
print '<br>';
print $DOF->im('employees')->show_programmitem($id, true);
if ( $pitem->status != 'deleted')
{// если назначение еще не отменено
    // подключаем обработчик формы добавления/удаления предметов
    include($DOF->plugin_path('im', 'employees','/process_teachers_assign.php'));
    
    // подключаем файл с формой добавления/удаления предметов';
    include($DOF->plugin_path('im', 'employees','/teachers_assign.php'));
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>