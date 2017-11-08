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
 * Файл, заменяющий плагин storage - "типы итогового контроля"
 */
$values = array(
     1 => $this->dof->get_string('total_other',    'refbook', null, 'modlib'),
     2 => $this->dof->get_string('total_diplomawork',   'refbook', null, 'modlib'),
     3 => $this->dof->get_string('total_coursework', 'refbook', null, 'modlib'),
     4 => $this->dof->get_string('total_abstract', 'refbook', null, 'modlib'),
     5 => $this->dof->get_string('total_gradexamination',   'refbook', null, 'modlib'),
     6 => $this->dof->get_string('total_oralexamination', 'refbook', null, 'modlib'),
     7 => $this->dof->get_string('total_writeexamination', 'refbook', null, 'modlib'),
     8 => $this->dof->get_string('total_finaltest',   'refbook', null, 'modlib'),
     9 => $this->dof->get_string('total_oralquiz', 'refbook', null, 'modlib'),
    10 => $this->dof->get_string('total_writequiz', 'refbook', null, 'modlib'),
    11 => $this->dof->get_string('total_writetest',    'refbook', null, 'modlib'),
    12 => $this->dof->get_string('total_combotest',   'refbook', null, 'modlib'),
    13 => $this->dof->get_string('total_discussion', 'refbook', null, 'modlib'),
    14 => $this->dof->get_string('total_questionnaire',    'refbook', null, 'modlib'),
    15 => $this->dof->get_string('total_testing',   'refbook', null, 'modlib'),
    16 => $this->dof->get_string('total_project', 'refbook', null, 'modlib'),
    17 => $this->dof->get_string('total_examination', 'refbook', null, 'modlib'));

?>