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
require_once($DOF->plugin_path('im','journal','/group_journal/libform.php'));
//id записи о теме занятия
$planid = required_param('planid', PARAM_INT);
if ( $DOF->storage('plans')->get_field($planid,'status') == 'canceled' )
{
    $planid = 0;
}
//id группо-потока
$csid = required_param('csid', PARAM_INT);
//id события
$eventid = required_param('eventid', PARAM_INT);
// сообщение, об успехе или крахе
$message = optional_param('message', 0, PARAM_INT);

// подключаем библеиотеки и стили
$DOF->modlib('widgets')->js_init('show_hide');
$DOF->modlib('nvg')->add_css('im', 'journal', '/styles.css');

//строка результатов работы
$rez = '';

//инициализируем форму
$customdata = new stdClass();
$status = $DOF->storage('schevents')->get_field($eventid,'status');
$customdata->editform     = true;
$customdata->cstreamid    = $csid;
$customdata->planid       = $planid;
$customdata->eventid      = $eventid;
$customdata->departmentid = $addvars['departmentid'];
$customdata->dof          = $DOF;
//print_object($customdata);

// права
if ( empty($planid) AND empty($eventid) )
{// новая КТ создаем
    if ( ! $DOF->storage('plans')->is_access('create/in_own_journal', $csid) ) 
    {//если нет права создавать тему в своем журнале, проверим, можно ли создавать тему вообще
        $DOF->storage('plans')->require_access('create');
    }
}elseif ( ! empty($eventid) )
{//событие есть 
    if ( ! empty($planid) )
    {// КТ есть
        if ( ! $DOF->storage('plans')->is_access('edit/in_own_journal', $planid) )
        {//если нет права редактировать тему в своем журнале, проверим, можно ли редактировать тему вообще
            $DOF->storage('plans')->require_access('edit', $planid);
        }
    }else
    {// событие есть, КТ - нет
        if ( ! $DOF->im('journal')->is_access('give_theme_event/own_event', $eventid) )
        {//если нет права создавать тему для события в своем журнале, 
            //проверим, можно ли создавать тему вообще
            $DOF->im('journal')->require_access('give_theme_event', $eventid);           
        }         
    }
}elseif ( ! empty($planid) AND empty($eventid))
{// события нет, но есть КТ
    if ( ! $DOF->storage('plans')->is_access('edit/in_own_journal', $planid) )
    {//если нет права редактировать тему в своем журнале, проверим, можно ли редактировать тему вообще
        $DOF->storage('plans')->require_access('edit', $planid);
    }
}

$flag = true;
if ( $status == 'replaced' OR $status == 'canceled' OR $status == 'implied' )
{
    $flag = false;
}
// проверим права: может пользователь менять дату урока
//передаем флаг разрешения изменения даты
$customdata->editdate = dof_im_journal_is_editdate($planid, $customdata->cstreamid);

//подключаем методы редактирования формы
$edittopic = new dof_im_journal_edittopic($DOF, $planid, $csid, $eventid);

//подключаем методы вывода формы редактирования элемента темплана
// $flag - определяет, будет блокирована форма или нет


//подключаем методы вывода формы отмены темплана
$cancellesson = new dof_im_journal_form_cancel_lesson(
      $DOF->url_im('journal','/group_journal/topic.php?planid='.$planid.
                             '&csid='.$csid.'&eventid='.$eventid,$addvars), $customdata);
include($DOF->plugin_path('im','journal','/group_journal/process_cancel_lesson.php'));


$formtopic = new dof_im_journal_formtopic_teacher(
      $DOF->url_im('journal','/group_journal/topic.php?planid='.$planid.
                             '&csid='.$csid.'&eventid='.$eventid,$addvars), $customdata,'post','',null,$flag);

//подключаем методы вывода формы завершения темплана
$completelesson = new dof_im_journal_form_complete_lesson(
      $DOF->url_im('journal','/group_journal/topic.php?planid='.$planid.
                             '&csid='.$csid.'&eventid='.$eventid,$addvars), $customdata);
//подключаем методы вывода формы завершения темплана
$transferlesson = new dof_im_journal_form_transfer_lesson(
      $DOF->url_im('journal','/group_journal/topic.php?planid='.$planid.
                             '&csid='.$csid.'&eventid='.$eventid,$addvars), $customdata);

//подключаем обработчик формы
include($DOF->plugin_path('im','journal','/group_journal/topic_save.php'));
//include($DOF->plugin_path('im','journal','/group_journal/process_cancel_lesson.php'));
//include($DOF->plugin_path('im','journal','/group_journal/process_complete_lesson.php'));
include($DOF->plugin_path('im','journal','/group_journal/process_replace_lesson.php'));
//обновим методы вывода формы завершения темплана после обработчика
/*$formtopic = new dof_im_journal_formtopic_teacher(
      $DOF->url_im('journal','/group_journal/topic.php?planid='.$planid.
                             '&csid='.$csid.'&eventid='.$eventid,$addvars), $customdata,'post','',null,$flag);*/
$transferlesson = new dof_im_journal_form_transfer_lesson(
      $DOF->url_im('journal','/group_journal/topic.php?planid='.$planid.
                             '&csid='.$csid.'&eventid='.$eventid,$addvars), $customdata);
//вывод на экран
//печать шапки страницы
if ( $eventid )
{
        $DOF->modlib('nvg')->add_level($DOF->get_string('edit_lesson', 'journal'), 
          $DOF->url_im('journal', '/group_journal/index.php', 
          array_merge(array('csid'=>$csid,'eventid'=>$eventid, 'planid'=>$planid ),$addvars)));
}else
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('new_lesson', 'journal'), 
          $DOF->url_im('journal', '/group_journal/index.php', array_merge(array('csid'=>$csid),$addvars)));
}
$DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);

//print $DOF->im('journal')->get_cstreamlink_info($csid);
if ( $topic = $edittopic->get_topic() )
{//вставляем начальные данные и выводим форму';
    if ( isset($USER->sesskey) )
    {//сохраним идентификатор сессии
        $topic->sesskey = $USER->sesskey;
    }else
    {//идентификатор сессии не найден
        $topic->sesskey = 0;
    }
    if ( ! $customdata->editdate )
    {//дату темплана нельзя редактировать - 
        //покажем дату в понятном формате 
        $topic->date = dof_userdate(time(),'%d.%m.%Y');
        $topic->reldate = dof_userdate($topic->reldate,'%d-%m-%Y %H:%M');
    }
    //добавляем в форму номер элемента темплана
    $topic->topicselector = $topic->planid;
    //print_object($topic);
    //$formtopic->set_data($topic); 
    $topic->eventid = $eventid;  
    $completelesson->set_data($topic);
    $cancellesson->set_data($topic);
    
    //выводим форму и список тем в таблице
    echo '<table style="border-style:none;text-aligh:center;width:100%;">';
    echo '<tr>';
    print '<td style="width:55%;vertical-align:top;">';
    
    $formtopic->display();
    if ( $message )
    {
        print $edittopic->rez;
    }
    $status = $DOF->storage('schevents')->get_field($eventid,'status');

    if ( ($status == 'plan' OR $status == 'postponed' OR $status == 'replaced') AND $flag)
    {// отображаем форму

        if ( ! $replace = $DOF->storage('schevents')->get_records(array('replaceid'=>$eventid,'status'=>
	                    array('completed','postponed','replaced'))) )
	    {// у события нет состоявшихся замен - отобразим форму
	        if ( $DOF->im('journal')->is_access('replace_schevent:date_dis',$eventid) OR
	             $DOF->im('journal')->is_access('replace_schevent:date_int',$eventid) OR
                 $DOF->im('journal')->is_access('replace_schevent:teacher',$eventid) OR
                 $DOF->im('journal')->is_access('replace_schevent:date_dis/own',$eventid) )
	        {// если есть хоть одно право переносить урок - покажем форму        
    	        $topic->eventid = $eventid;
    	        $topic->planid = $planid;
    	        $replaceid = $DOF->storage('schevents')->get_field($eventid,'replaceid');
                if ( ! empty($replaceid) )
                {// если событие уже является заменой, то производим действия на замененный урок
                    $topic->eventid = $replaceid;
                }
                $topic->date = time();
                $transferlesson->set_data($topic);
                $transferlesson->display();
	        }
	           
	    }
        
    }
    if ( $DOF->workflow('schevents')->is_access('changestatus:to:canceled',$eventid) )
    {// если есть право отмечать урок как отмененный
        $cancellesson->display();
    }
    print '<p style="text-align:center;">'.$rez.'</p>';
    print '</td>';
    print '<td style="width:45%;vertical-align:top;">';
    print $edittopic->print_table();
    
    print '</td>';
    print '</tr>';
    print '</table>';
    
   
}else
{//начальные данные не получены - сообщаем об ошибке
    $DOF->print_error($DOF->get_string('no_data_in_form', 'journal'));
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);

?>