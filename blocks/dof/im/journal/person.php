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
// страница отображания основной информации об ученике, информации для куратора или информации для учителя
$personid = required_param('personid', PARAM_INT);

// @todo разобраться с правами доступа
//добавление уровня навигации, ведущего на главную страницу дневника
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'recordbook'), 
    $DOF->url_im('recordbook', '/index.php?clientid='.$personid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('recordbook_common_data', 'recordbook'), 
                        $DOF->url_im('journal', '/person.php?personid='.$personid,$addvars));
//проверяем полномочия на просмотр информации
$DOF->im('journal')->require_access('view_person_info');
$fullname = '';
if ( $DOF->storage('persons')->is_exists($personid) )
{// Узнаем ФИО персоны 
    $fullname = $DOF->storage('persons')->get_fullname($personid);
}else
{// персоны нет - это ошибка
    $DOF->print_error('no_client_in_base', '', $DOF->url_im('recordbook','',$addvars), 'im', 'recordbook');
}
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Выводим ФИО персоны
if ( $DOF->storage('persons')->is_access('view',$personid) )
{
    $DOF->modlib('widgets')->print_heading('<a href="'.$DOF->url_im('persons', '/view.php?id='.$personid,$addvars).'">'.$fullname.'</a>');
}else
{
    $DOF->modlib('widgets')->print_heading($fullname);
}
// ссылка на расписание 
if ( $DOF->im('journal')->is_access('view_schevents') )
{//проверяем полномочия на просмотр информации
    echo '<a href="'.$DOF->url_im('journal', '/show_events/show_events.php?personid='.$personid,$addvars).'">'
        .$DOF->get_string('show_events', 'journal').'</a><br>';  
}
echo '<a href="'.$DOF->url_im('journal', '/personsbc_gradeslist/index.php',$addvars).'">'
    .$DOF->get_string('pbcgl_programmbc_header', 'journal').'</a><br>';
//проверяем доступ на шаблоны
/*if (  $DOF->storage('schtemplates')->is_access('view') )
{
    echo $DOF->get_string('view_week_template','journal').' 
        <a href="'.$DOF->url_im('schedule', '/view_week.php?studentid='.$personid,$addvars).'">'
                .$DOF->get_string('view_week_template_for_student', 'journal').'</a> / 
        <a href="'.$DOF->url_im('schedule', '/view_week.php?teacherid='.$personid,$addvars).'">'
        .$DOF->get_string('view_week_template_for_teacher', 'journal').'</a><br><br> ';
}*/

$persondata = new dof_im_journal_view_person_info($DOF, $personid);
echo $persondata->get_learning_info();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>