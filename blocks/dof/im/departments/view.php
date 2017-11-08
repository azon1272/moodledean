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
 * отображает одну запись по ее id 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
// навигация
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'departments'), $DOF->url_im('departments'.'/index.php'),$addvars);
$departmentid = optional_param('departmentid','0' ,PARAM_INT);
$id = optional_param('id',$departmentid, PARAM_INT);
//проверяем доступ
if ( !$DOF->storage('departments')->is_access('view/mydep',$id) )
{
    $DOF->storage('departments')->require_access('view',$id);    
}     

//добавление уровня навигации
if ( ! $department = $DOF->storage('departments')->get($id) )
{
     $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'), $DOF->url_im('departments'));
}else 
{
    $DOF->modlib('nvg')->add_level($department->name.'['.$department->code.']',$DOF->url_im('departments','/view.php?id='.$id,$addvars));
    $department = $DOF->im('departments')->show_id($id);
}

// вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( ! $id )
{// вывод ошибки
    $errorlink = $DOF->url_im('departments','',$addvars);
    $DOF->print_error('no_department_found',$errorlink,$id,'im','departments');
}

// выводим карточку подразделения
$department->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>