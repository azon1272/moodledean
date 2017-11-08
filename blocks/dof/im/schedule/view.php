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
 * отображает одну запись по ее id 
 */

// Подключаем библиотеки
require_once('lib.php');
// Подключаем формы
require_once('form.php');
// получаем id просматриваемого периода
$tmid = required_param('id', PARAM_INT);
//проверяем доступ
//@todo проверить право, визу, все документы и разрешение на въезд...
$DOF->storage('schtemplates')->require_access('view',$tmid);

if ( ! $template = $DOF->storage('schtemplates')->get($tmid) )
{// если период не найден, выведем ошибку
    // @todo на место $link можно прописать ссылку, если надо будет
	$DOF->print_error('template_not_exists', '', $tmid, 'im', 'schedule');
}

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
$DOF->modlib('nvg')->add_level($DOF->get_string('view_template', 'schedule'),$DOF->url_im('schedule','/view.php?id='.$tmid,$addvars));

// переменная для текстовых сообщений, выводимых на экран
$message = '';
// создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $tmid;
// объявляем форму
$statusform = new dof_im_schedule_changestatus_schetemplate_form(
              $DOF->url_im('schedule', '/view.php?id='.$tmid,$addvars), $customdata);
// обрабатываем смену статуса
$statusform->process();

//вывод на экран

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone',dof_usertimezone()));

$show = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);
//ссылка на возвращение к рассписанию
if ( $DOF->storage('schtemplates')->is_access('view') )
{
    $link = '<a href='.$DOF->url_im('schedule','/index.php',$addvars).'>'.
    $DOF->get_string('return_on_schedule', 'schedule').'</a>';
    echo '<br>'.$link;
    // ссылка на возвращение в учебный план
    // ищем программу, в которой создан шаблон
    $tmcstreamid = $DOF->storage('schtemplates')->get_field($tmid, 'cstreamid');
    $tmprogrammitemid = $DOF->storage('cstreams')->get_field($tmcstreamid, 'programmitemid');
    $tmprogrammid = $DOF->storage('programmitems')->get_field($tmcstreamid, 'programmid');
    $link = '<a href='.$DOF->url_im('cstreams','/by_groups.php',
            array('departmentid' => $addvars['departmentid'], 'programmid' => $tmprogrammid)).'>'.
            $DOF->get_string('return_on_teachplan', 'schedule').'</a>';
    echo '<br>'.$link;
}


// ссылка на создание шаблона
if ( $DOF->storage('schtemplates')->is_access('create') )
{// если есть право создавать шаблон
    //добываем (золото, минералы, древесину) id периода
    $ageid = $DOF->storage('cstreams')->get_field($template->cstreamid,'ageid');
    $link = '<a href='.$DOF->url_im('schedule','/edit.php?ageid='.$ageid,$addvars).'>'.
    $DOF->get_string('new_template', 'schedule').'</a>';
    echo '<br>'.$link;
    // ссылка на создание шаблона для cstream
    $link = '<a href='.$DOF->url_im('schedule','/edit.php?cstreamid='.$template->cstreamid,$addvars).'>'.
    $DOF->get_string('new_template_on_cstream', 'schedule').'</a>';
    echo '<br>'.$link.'<br><br>';
}

$hours    = floor($template->begin/3600);
$minutes  = floor(($template->begin - $hours * 3600) / 60);

$begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
//$midnight = date("H",dof_make_timestamp(0, 0)); // полночь в часовом поясе
$midnight = intval(date("H",dof_make_timestamp(0, 0, 0)));
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

$load = new dof_im_schedule_master_make($DOF, $addvars['departmentid'], null, $addvars);
// отобразим недогруз
echo '<br><br>'.$load->get_underload_cstream($template->cstreamid,true);
// вывод информации о периоде
echo '<br>'.$show->get_table_one($tmid);

if ( $DOF->workflow('schtemplates')->is_access('changestatus') )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // показываем форму
    $statusform->display();
}

// ПЕРЕСЕЧЕНИЯ
if ( ! $load->show_cross_templates($tmid) )
{// нет пересечений - скажем об этом
    echo '<br><div align="middle" style="color:green"><b>'.$DOF->get_string('no_cross_template', 'schedule').'</b></div>';
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>