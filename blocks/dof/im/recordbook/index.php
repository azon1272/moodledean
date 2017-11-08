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
if ( $DOF->is_access('view') )
{// если у нас есть право просматривать личную информацию - то возьмем id извне
    $clientid = optional_param('clientid', null, PARAM_INT);
    if ( ! $clientid )
    {// не указан внешний id - возьмем свой
        $clientid = $DOF->storage('persons')->get_by_moodleid_id();
    }
}else
{// если нет - то можно смотреть только свой дневник
    $clientid = $DOF->storage('persons')->get_by_moodleid_id();
}

//добавление уровня навигации, ведущего на страницу дневника
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'recordbook'), 
    $DOF->url_im('recordbook', '/index.php?clientid='.$clientid,$addvars));

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//создаем объект для сбора и подготовки всех необходимых данных
$c = new dof_im_recordbook_studentslist($DOF);
$c->set_data($clientid);
$c->add_data();
// получаем данные для маркированного списка
$listdata = $c->get_output($clientid);
$info = $listdata->students['0'];

//print_object($listdata);
//выводим заголовок
$DOF->modlib('widgets')->print_heading($DOF->get_string('recordbook_common_data','recordbook') . ': ' . $info->fullname);
// обращаемся к шаблонизатору для вывода таблицы
if ( ! is_object($listdata) OR empty($listdata->students) )
{// если нет данных об ученике, то скажем об этом
    print('<p align="center">(<i>'.$DOF->get_string('no_data', 'recordbook').'</i>)</p>');
}else
{// если есть информация - выводим ее
    $templater_package = $DOF->modlib('templater')->template('im', 'recordbook', $listdata, 'studentslist');
    print($templater_package->get_file('html'));
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>