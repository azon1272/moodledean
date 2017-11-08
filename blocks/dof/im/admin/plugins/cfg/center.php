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

$sections = array(); //хранит блоки, отображаемые на левой стороне страницы
/** Любой плагин может возвращать несколько блоков. Они различаются по именам (название_блока). 
 *  Каждый блок может возвращать разные параметры. id_блока - это ключ, который указывает, 
 *  какой параметр возвращает блок. 
 *  Поэтому структура массива такова:
 *  $left_blocks[] = array('im'=>'код_плагина','name'=>'название_блока','id'=>id_блока);
 */  
$sections[] = array('im'=>'admin','name'=>'plugins','id'=>1,'title'=>$this->dof->get_string('storages', 'admin', null));
$sections[] = array('im'=>'admin','name'=>'plugins','id'=>2, 'title'=>$this->dof->get_string('ims', 'admin', null));
$sections[] = array('im'=>'admin','name'=>'plugins','id'=>3, 'title'=>$this->dof->get_string('workflows', 'admin', null));
$sections[] = array('im'=>'admin','name'=>'plugins','id'=>4, 'title'=>$this->dof->get_string('syncs', 'admin', null));
$sections[] = array('im'=>'admin','name'=>'plugins','id'=>5, 'title'=>$this->dof->get_string('modlibs', 'admin', null));

 

?>