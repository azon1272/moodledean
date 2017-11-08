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
// id шаблона который сейчас редактируется
$templateid = optional_param('id', 0, PARAM_INT);
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
// очно/дистанционно - пераметр
$formlesson = optional_param('formlesson','internal',PARAM_TEXT);

$template = new stdClass();
$template->daynum = $addvars['daynum'];
$template->dayvar = $addvars['dayvar'];
if ( $templateid AND ! $template = $DOF->storage('schtemplates')->get($templateid) )
{// редактируемый шаблон отсутствует в базе
    $errorlink = $DOF->url_im('schedule');
    $DOF->print_error('template_not_found', $errorlink, NULL, 'im', 'schedule');
}
// создаем дополнительные данные для формы
$customdata = new stdClass();
// id подразделения (из lib.php)
$customdata->departmentid = $addvars['departmentid'];
$customdata->dof          = $DOF;
$customdata->cstreamid    = $cstreamid;
$customdata->begintime    = $begintime;
$customdata->ageid        = $addvars['ageid'];
$customdata->studentid    = $studentid;
$customdata->teacherid    = $teacherid;
$customdata->agroupid     = $agroupid;
$customdata->formlesson   = $formlesson;
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
// добавляем дополнительные параметры в навигацию
$addvars['id'] = $templateid;
$addvars['cstreamid'] = $cstreamid;
$addvars['begintime'] = $begintime;

// Создаем объект формы
$form = new dof_im_schedule_edit_schetemplate_form($DOF->url_im('schedule','/edit.php',$addvars), $customdata);
// обрабатываем пришедшие данные (если нужно)
$form->process();
// Устанавливаем данные по умолчанию
$form->set_data($template);

if ( $templateid == 0 )
{//проверяем доступ
    $DOF->storage('schtemplates')->require_access('create');
    $pagetitle = $DOF->get_string('new_template', 'schedule');
    $hours    = 0;
    $minutes  = 0;
}else
{//проверяем доступ
    $DOF->storage('schtemplates')->require_access('edit', $templateid);
    $pagetitle = $DOF->get_string('edit_template', 'schedule');
    $hours    = floor($template->begin / 3600);
    $minutes  = floor(($template->begin - $hours * 3600) / 60);
}

// добавляем уровни навигации 
$DOF->modlib('nvg')->add_level($pagetitle, $DOF->url_im('schedule','/edit.php'), $addvars);
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone',dof_usertimezone()));

$begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
$midnight = intval(date("H",dof_make_timestamp(0, 0, 0))); // полночь в часовом поясе
$zonedate = dof_usergetdate($hours);
$date = getdate($hours);
if ( ($zonedate['wday']*24+$zonedate['hours']) > ($date['wday']*24+$date['hours']) )
{// положительная зона
    if ( ($hours > $midnight) OR ($hours <= ($midnight - 24)) )
    {
         echo '<div style="color: #FF0033; text-align: center;"><b>'.$DOF->get_string('warning_timezone', 'schedule').'</b></div>';
    }
}elseif ( ($zonedate['wday']*24+$zonedate['hours']) < ($date['wday']*24+$date['hours']) )
{// отрицательная зона
    if ( ($hours <= $midnight) OR ($hours > ($midnight + 24)) )
    {// выделим другим цветом те, которые из другого подразделения
         echo '<div style="color: #FF0033; text-align: center;"><b>'.$DOF->get_string('warning_timezone', 'schedule').'</b></div>';
    }
}
// печать формы
$form->display();


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>