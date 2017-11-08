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

/**
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

/*
// Время для которого создается шаблон (если есть)
$begintime  = optional_param('begintime', 0, PARAM_INT);
// Поток, для которого создается шаблон (если есть)
$cstreamid  = optional_param('cstreamid', 0, PARAM_INT);
// получаем id'шники, по которым будем дополнительно отсеевать потоки
// получаем id студента
$studentid = optional_param('studentid',0,PARAM_INT);
// получаем id учителя
$teacherid = optional_param('teacherid',null,PARAM_INT);
// получаем id группы
$agroupid  = optional_param('agroupid',0,PARAM_INT);

*/
$DOF->im('schedule')->require_access('create_schedule');
// создаем дополнительные данные для формы
$customdata = new stdClass();
// id подразделения (из lib.php)
$customdata->departmentid = $addvars['departmentid'];
$customdata->dof          = $DOF;
$customdata->ageid        = $addvars['ageid'];
/*
$customdata->cstreamid    = $cstreamid;
$customdata->begintime    = $begintime;

$customdata->studentid    = $studentid;
$customdata->teacherid    = $teacherid;
$customdata->agroupid     = $agroupid;
*/
//добавление уровня навигации
if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
{// на конкретный периож
    $DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name), 
    $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                               'ageid'=>$addvars['ageid'])) ); 
}else
{// без периода
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'schedule'), 
    $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                               'ageid'=>$addvars['ageid'])) ); 
} 
/*
// добавляем дополнительные параметры в навигацию
$addvars['id'] = $templateid;
$addvars['cstreamid'] = $cstreamid;
$addvars['begintime'] = $begintime;
*/
$message = '';
// Создаем объект формы
$form = new dof_im_schedule_create_event_form($DOF->url_im('schedule','/create_events.php',$addvars), $customdata);
// обрабатываем пришедшие данные (если нужно)
$message = $form->process();




// добавляем уровни навигации 
$DOF->modlib('nvg')->add_level($DOF->get_string('create_event', 'schedule'), 
      $DOF->url_im('schedule','/create_events.php'), $addvars);
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//ссылка на возвращение к рассписанию
if ( $DOF->storage('schtemplates')->is_access('view') )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('schedule','/index.php',$addvars).'>'.
    $DOF->get_string('return_on_schedule', 'schedule').'</a>';
    echo '<br>'.$link;
}

// печать формы
$form->display();
// вывод сообщения об обработке
echo $message;

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>