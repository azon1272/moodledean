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

require_once('lib.php');
require_once('libform.php');

//id события
$eventid = required_param('eventid', PARAM_INT);

//проверяем полномочия на просмотр информации
$DOF->im('journal')->require_access('replace_schevent',$eventid);

//инициализируем форму
$customdata = new stdClass();
$customdata->eventid      = $eventid;
$customdata->departmentid = $addvars['departmentid'];
$customdata->dof          = $DOF;
$transferlesson = new dof_im_journal_form_replace_event(
      $DOF->url_im('journal','/group_journal/replace.php?eventid='.$eventid,$addvars), $customdata);


$status = $DOF->storage('schevents')->get_field($eventid,'status');
print $transferlesson->process();
// вывод на экран
$DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
$transferlesson->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
?>