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



global $DOF;
$sections = array(); //хранит секции, отображаемые по центру страницы
/** Любой плагин может возвращать несколько Секций. Они различаются по именам (название_блока). 
 *  Каждый блок может возвращать разные параметры. id_блока - это ключ, который указывает, 
 *  какой параметр возвращает блок. 
 *  Поэтому структура массива такова:
 *  $left_blocks[] = array('im'=>'код_плагина','name'=>'название_блока','id'=>id_блока,'title'=>'заголовок секции');
 */  
if ( $DOF->im('university')->is_access('student') )
{//секция с информацией для студента
    $sections[] = array('im'=>'university','name'=>'student','id'=>1, 'title'=>$DOF->get_string('forstudent','university'));
}
if ( $DOF->im('university')->is_access('teacher') )
{//Секция с информацией для учителей
    $sections[] = array('im'=>'university','name'=>'teacher','id'=>1, 'title'=>$DOF->get_string('forteacher','university'));
}
if ( $DOF->im('university')->is_access('manager') )
{//Секция с информацией для администрации
    $sections[] = array('im'=>'university','name'=>'manager','id'=>1, 'title'=>$DOF->get_string('formanager','university'));
}

?>