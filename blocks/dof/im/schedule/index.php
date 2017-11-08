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

/** Главная страница расписания
 * @todo добавить проверку прав
 */

// Подключаем библиотеки
require_once('lib.php');
require_once($DOF->plugin_path('im', 'schedule', '/form.php'));

// параметры отображения расписания
$displayvars = array();
// время начала уроков
$displayvars['begin']      = optional_param('begin',  null, PARAM_INT);
// время окончания уроков
$displayvars['end']        = optional_param('end',    null, PARAM_INT);
//проверяем доступ
$DOF->storage('schtemplates')->require_access('view');

foreach ( $displayvars as $name => $value )
{// Добавляем к ссылке все переданные параметры отображения расписания
    if ( ! is_null($value) )
    {
        $addvars[$name] = $value;
    }
}

//добавление уровня навигации
if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
{// на конкретный период
    $DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name), 
    $DOF->url_im('schedule','/index.php',$addvars) ); 
}else
{// без периода
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'schedule'), 
    $DOF->url_im('schedule','/index.php',$addvars) ); 
}    

// формируем данные для формы
$formaction = $DOF->url_im('schedule', '/index.php', $addvars);
$customdata  = new stdClass();
$customdata->dof = $DOF;

// создаем форму выбора режима просмотра
$displayform = new dof_im_schedule_display_mode_form($formaction, $customdata);
// Обрабатываем данные (если нужно)
$displayform->process($addvars);
// устанавливаем значения по умолчанию
$displayform->set_data($addvars);

// Создаем объект для отображения данных шаблона
$schedule = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone',dof_usertimezone()));

// TODO в будущем вынести ЭТО в стили
// тут мы выделяем другим цветом всю строку таблицы, отвечеющей нашим условиям
// класс, который при отображении по времени четных/нечетных ещё и показывает ЕЖЕНЕДЕЛЬНО дрю цветом
echo "
  <style type='text/css'>
    .mismatch_department .cell { color: #009900; }   
  </style> ";

// ссылка на создание шаблона
if ( $DOF->storage('schtemplates')->is_access('create') AND $addvars['ageid'] )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('schedule','/edit.php',$addvars).'>'.
    $DOF->get_string('new_template', 'schedule').'</a>';
    echo '<br>'.$link.'<br>';
}
// cсылка на просмотр отчета нагрузки по шаблонам
if ( $DOF->storage('schtemplates')->is_access('view') AND $addvars['ageid'] )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('schedule','/report_template.php',$addvars).'>'.
    $DOF->get_string('report', 'schedule').'</a>';
    echo $link.'<br>';
}

// Показываем форму с режимом отображения
$displayform->display();
if ( $addvars['display'] == 'time' )
{// если отображение сделано по времени
    // показываем ряды вкладок
    echo $schedule->get_main_page_tabs($addvars['ageid'], $addvars['display'], $addvars['daynum'], $addvars['dayvar'], $addvars['intervalid'],$addvars['form']);
}
if ( ! $addvars['ageid'] )
{// предупреждение на отсутствие периода
    echo '<div align="center"><b>'.$DOF->get_string('select_ageid_of_display', 'schedule').'</b></div>';
}elseif ( ! $addvars['daynum'] AND $addvars['display'] == 'time' )
{// предупреждение на отсутствие дня
    echo '<div align="center"><b>'.$DOF->get_string('select_daynum', 'schedule').'</b></div>';
}elseif ( ! isset($addvars['intervalid']) AND $addvars['display'] == 'time' )
{// предупреждение на дневного интервала
    echo '<div align="center"><b>'.$DOF->get_string('select_intervalid', 'schedule').'</b></div>';
}else
{// выводим таблицы
    echo $schedule->print_full_schedule($addvars['ageid'], $addvars['display'], $addvars['daynum'], $addvars['dayvar'], $addvars['intervalid'], $addvars['form']);
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>