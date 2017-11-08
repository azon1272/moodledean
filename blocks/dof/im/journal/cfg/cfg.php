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


$im_journal = array();
// Право на редактирование даты урока
//$im_journal['teacher_can_change_lessondate'] = true;  //включена 
$im_journal['teacher_can_change_lessondate'] = false; //выключена
// Разрешить или запретить создание КТ из журнала
//$im_journal['teacher_can_create_lesson'] = true;  //включена 
$im_journal['teacher_can_create_lesson'] = false; //выключена

// Разрешить или запретить создание событий из журнала
// по умолчанию события через журнал создавать нельзя
//$im_journal['allow_events_create_from_journal'] = true;
$im_journal['allow_events_create_from_journal'] = false;

// Запретить задавать домашние задания, не указывая планируемое время их выполнения
// по умолнанию - выключено
//$im_journal['deny_homework_without_hours'] = true;
$im_journal['deny_homework_without_hours'] = false;
?>