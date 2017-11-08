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

$dayid = required_param('id', PARAM_INT);
//проверяем полномочия на просмотр информации
//$DOF->storage('reports')->require_access('view_report',$ageid);

if ( ! $day = $DOF->storage('schdays')->get($dayid) )
{// редактируемый день отсутствует в базе
    $errorlink = $DOF->url_im('schdays','/calendar.php',$addvars);
    $DOF->print_error('day_not_found', $errorlink, NULL, 'im', 'schdays');
}
$DOF->modlib('nvg')->add_level(dof_userdate($day->date,"%d-%m-%Y"),
        $DOF->url_im('schdays','/view.php?id='.$dayid,$addvars));

// загружаем метод работы с отчетом
$dispay = new dof_im_schdays_display($DOF,$addvars);    

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//  ссылка на создание расписания по шаблону
if ( $DOF->storage('schtemplates')->is_access('view') )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('schedule','/index.php?daynum='.$day->daynum.
    '&dayvar='.$day->dayvar.'&display=time&form=all&intervalid=0',$addvars).'>'.
    $DOF->get_string('view_templates', 'schdays').'</a>';
    echo $link.'<br>';
}
if ( $DOF->im('journal')->is_access('view_schevents') )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('journal','/show_events/show_events.php?'.
    'date_to='.$day->date.'&date_from='.$day->date.'&personid=0&display=time&viewform=1',$addvars).'>'.
    $DOF->get_string('view_events', 'schdays').'</a>';
    echo $link.'<br><br>';
}

// отображаем отчет
echo $dispay->get_table_one($dayid);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>