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



// Подключаем библиотеки
require_once('lib.php');
//id записи о теме занятия
$eventid = required_param('id', PARAM_INT);
// получаем id персоны
$addvars['personid'] = optional_param('personid',0,PARAM_INT);
$addvars['date_to'] = optional_param('date_to',0,PARAM_INT);
$addvars['date_from'] = optional_param('date_from',0,PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
// проверки
// не найден элемент учебного плана
if ( ! $event  = $DOF->storage('schevents')->get($eventid) )
{// вывод сообщения и ничего не делаем
    print_error($DOF->get_string('template_not_exists','schedule',$eventid));
}
//проверка прав доступа
$DOF->workflow('schevents')->is_access('changestatus:to:canceled',$eventid);  

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes ='/group_journal/cancel_event.php?id='.$eventid.'&delete=1';
$linkno ='/show_events/show_events.php';
if ( $delete )
{// если сказали удалить - сменим статус
    $DOF->storage('schevents')->cancel_event($eventid,true,true);
    redirect($DOF->url_im('journal',$linkno,$addvars));
}else
{   
    $DOF->modlib('nvg')->add_level($DOF->get_string('lesson_cancel_title', 'journal'),
          $DOF->url_im('journal','/group_journal/cancel_event.php?id='.$eventid,$addvars));
    //печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // спросим об удалении
    $date = dof_userdate($event->date,'%d-%m-%Y').' '.dof_userdate($event->date,'%H:%M');
    $teacher = $DOF->storage('persons')->get_fullname($event->teacherid);
    $DOF->modlib('widgets')->notice_yesno($date.', '.$teacher.'.<br>'.
            $DOF->get_string('сonfirmation_cancel_lesson','journal').'?', 
            $DOF->url_im('journal',$linkyes,$addvars),
            $DOF->url_im('journal',$linkno,$addvars));
    
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>
