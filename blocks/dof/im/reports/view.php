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

$reportid   = required_param('id', PARAM_INT);
$plugintype = required_param('plugintype', PARAM_TEXT);
$plugincode = required_param('plugincode', PARAM_TEXT);
$code       = required_param('code', PARAM_TEXT);
//проверяем полномочия на просмотр информации
if ( ! $DOF->storage('reports')->is_access('view_report_'.$plugintype.'_'.$plugincode.'_'.$code,$reportid) )
{
    $DOF->storage('reports')->require_access('view_report',$reportid);
}

$addvars['id'] = $reportid;

$DOF->modlib('nvg')->add_level($DOF->storage('reports')->get_field($reportid, 'name'),
        $DOF->url_im('reports','/view.php',$addvars));

// проверка на правильность данных
$report = $DOF->storage('reports')->get($reportid);

// загружаем метод работы с отчетом
$dispay = new dof_im_reports_display($DOF,$addvars['departmentid'],$addvars);    
$report = $dispay->report($plugintype,$plugincode,$code,$reportid);


//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// отображаем отчет
$report->show_report_html($addvars);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>