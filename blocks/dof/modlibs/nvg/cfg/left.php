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
$blocks = array(); //хранит блоки, отображаемые на левой стороне страницы
/** Любой плагин может возвращать несколько блоков. Они различаются по именам (название_блока). 
 *  Каждый блок может возвращать разные параметры. id_блока - это ключ, который указывает, 
 *  какой параметр возвращает блок. 
 *  Поэтому структура массива такова:
 *  $blocks[] = array('im'=>'код_плагина','name'=>'название_блока','id'=>id_блока);
 */  


if ( $DOF->modlib('nvg')->is_access('admin'))
{
    $blocks[] = array('im'=>'admin','name'=>'menu','id'=>1, 'title' => $DOF->get_string('title', 'admin'));
}
//$blocks[] = array('im'=>'my','name'=>'main','id'=>1, 'title' => $DOF->get_string('navigation'));
//$blocks[] = array('im'=>'departments','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'departments'));
//$blocks[] = array('im'=>'sel','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'sel'));
//$blocks[] = array('im'=>'persons','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'persons'));
//$blocks[] = array('im'=>'employees','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'employees'));

//$blocks[] = array('im'=>'exampleim','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'exampleim'));
//$blocks[] = array('im'=>'ages','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'ages'));
//$blocks[] = array('im'=>'programms','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'programms'));
//$blocks[] = array('im'=>'programmitems','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'programmitems'));
//$blocks[] = array('im'=>'agroups','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'agroups'));
//$blocks[] = array('im'=>'departments','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'departments'));
//$blocks[] = array('im'=>'plans','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'plans'));
//$blocks[] = array('im'=>'cstreams','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'cstreams'));
//$blocks[] = array('im'=>'cpassed','name'=>'main','id'=>1, 'title' => $DOF->get_string('title', 'cpassed'));


?>