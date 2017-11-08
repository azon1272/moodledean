<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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

// Подключаем библиотеки
require_once('lib.php');
require_once($DOF->plugin_path('im','journal','/group_journal/libform.php'));

// id учебного подразделения в таблице departments
$departmentid = optional_param('departmentid', 0, PARAM_INT);
//проверяем доступ
$DOF->storage('schevents')->require_access('create');

$dep = '';
if ( $departmentid )
{
    $dep = ' AND departmentid='.$departmentid;
}
//получаем список потоков
$list = $DOF->storage('cstreams')->get_records_select(' status="active" '.$dep );

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $list )
{// не найдено ни одной потока
    print('<p align="center">(<i>'.$DOF->
            get_string('no_cstreams_found', 'cstreams').'</i>)</p>');
}else
{//есть потоки
    $cstreams = array();
    foreach($list as $el)
    {
        $cstream = new stdClass();
        $cstream->name = $el->name;
        $cstream->id = $el->id;
        $cstreams[] = $cstream;
    }
    // объект для формы
    $customdata = new stdClass();
    $customdata->dof       = $DOF;
    $customdata->cstreams  = $cstreams;
    $customdata->cstreamid = $cstream->id;
    $customdata->eventid   = 0;
    $customdata->planid    = 0;
    $customdata->departmentid = $departmentid;
    //инициализируем форму
    $formtopic = new dof_im_journal_formtopic_teacher(
    $DOF->url_im('journal','/mass_events/index.php?departmentid='.$departmentid
            ), $customdata);
    //подключаем обработчик формы
    $formtopic->process_save_events();
    $formtopic->display();
}

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>