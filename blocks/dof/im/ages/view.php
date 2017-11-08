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
require_once('form.php');

/** Параметры фильтрации */
// получаем id просматриваемого периода
$ageid = required_param('ageid', PARAM_INT);

/** Навигация */
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'ages'), 
                               $DOF->url_im('ages','/list.php'),$addvars);
if ( ! $DOF->storage('ages')->is_exists($ageid) )
{// если период не найден, выведем ошибку
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'),
           $DOF->url_im('ages','/view.php?ageid='.$ageid,$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    $errorlink = $DOF->url_im('ages');
    $DOF->print_error('notfoundage', $errorlink, $ageid, 'im', 'ages');
}
$DOF->modlib('nvg')->add_level($DOF->storage('ages')->get_field($ageid, 'name'),
                                   $DOF->url_im('ages','/view.php?ageid='.$ageid,$addvars));

/** Доступ */
$DOF->storage('ages')->require_access('view',$ageid);

/** Формы */
// создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $ageid;
// объявляем форму смены статуса 
$statusform = new dof_im_ages_changestatus_form($DOF->url_im('ages', '/view.php?ageid='.$ageid,$addvars), $customdata);
// обрабатываем данные
$statusform->process();

// объявляем форму пересинхронизации потоков периода
$resyncform = new dof_im_ages_resync_form($DOF->url_im('ages', '/view.php?ageid='.$ageid,$addvars), $customdata);
$resyncform->process();

/** Отображение */
// подключаем класс отображения
$display = new dof_im_ages_display($DOF,$addvars);

//вывод на экран
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// ссылка на создание периода
if ( $DOF->storage('ages')->is_access('create') )
{// если есть право создавать период
    if ( $DOF->storage('config')->get_limitobject('ages',$addvars['departmentid']) )
    {// лимит еще есть - покажем ссылку
        $link = '<a href='.$DOF->url_im('ages','/edit.php',$addvars).'>'.
        $DOF->get_string('newages', 'ages').'</a>';
        echo $link.'<br><br>';
    }else 
    {// лимит исчерпан
        $link =  '<span style="color:silver;">'.$DOF->get_string('newages', 'ages').
        	' ('.$DOF->get_string('limit_message','ages').')</span>';
        echo $link.'<br><br>'; 
    } 
}

// вывод информации о периоде
echo $display->get_table_one($ageid);

if ( $DOF->workflow('ages')->is_access('changestatus',$ageid) )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    $statusform->display();
}
if ( $DOF->im('ages')->is_access('manage') )
{// показываем форму пересинхронизации только пользователям с правами manage
    $resyncform->display();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>