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

$reportid = required_param('id', PARAM_INT);
$addvars['id'] = $reportid;
$type = required_param('type', PARAM_TEXT);

$DOF->modlib('nvg')->add_level($DOF->storage('reports')->get_field($reportid, 'name'), 
                                $DOF->url_im('programmsbcs','/reportstudents/view.php',$addvars));
$table = '';
$error = '';
// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);
// загружаем метод работы с отчетом
$report = $DOF->im('programmsbcs')->report($type,$reportid);
//$report->generate();
if ( ! $report->is_generate($report->load()) )
{//  отчет еще не сгенерирован
    $error = $DOF->get_string('report_no_generate','programmsbcs');
}else
{// загружаем шаблон
    // достаем данные из файла
    $template = $report->load_file();
    // подгружаем методы работы с шаблоном
    if ( ! $templater = $report->template() )
    {//не смогли
        $error = $DOF->get_string('report_no_get_template','programmsbcs');
    }elseif ( ! $table = $templater->get_file('html') )
    {// не смогли загрузить html-таблицу
        $error = $DOF->get_string('report_no_get_table','programmsbcs');
    }
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//права
$DOF->storage('reports')->require_access('view_mreports_person',$reportid);

if ( ! $age = $DOF->storage('reports')->get($reportid) )
{// если период не найден, выведем ошибку
	print_error($DOF->get_string('notfoundage','programmsbcs'));
}

print '<p style=" color:red; text-align:center; "><b>'.$error.'</b></p>';
if ( ! empty($config->value) OR $DOF->is_access('datamanage') )
{
    echo $table;
}    
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>