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

//проверяем полномочия на просмотр информации
$reportid = required_param('reportid',PARAM_INT);
$type = required_param('type',PARAM_TEXT);

$DOF->storage('reports')->require_access('export_report_im_journal_loadteachers',$reportid);
$format = optional_param('format','csv',PARAM_TEXT);
if ($type != 'loadteachers' OR $DOF->storage('reports')->get_field($reportid, 'code') != 'loadteachers')
{// неверный тип
    return false;
}
// загрузим данные приказа
$report = $DOF->im('journal')->report('loadteachers',$reportid);
$reportobj = $DOF->storage('reports')->get($reportid);
$template = $report->load_file();
//подразделение
if ( $reportobj->departmentid )
{// отчет по подразделению
    $dep = $DOF->storage('departments')->get($reportobj->departmentid);
    $department = $dep->code;    
}else 
{// все отчеты
    $department = $DOF->get_string('all_departs','journal');
}  
// сохраним его в вормате CSV
$filename = 'report'.$reportid.'('.dof_userdate($template->_begindate,'%d.%m.%y').'-'.
                                   dof_userdate($template->_enddate,'%d.%m.%y')
                .')['.dof_userdate($reportobj->crondate,'%d.%m.%y_%H:%d').']['.$department.'].csv';
// путь хранения отчета
$path = $DOF->plugin_path('im', 'journal', '/dat/'.$filename);

// приведем к виду массив массивов
$mas = array();
// имена колонок, убираем тут тег <br>
$head = array(
        $template->column_teacher, 
        $template->column_eagreement, 
        $template->column_appoint, 
        strip_tags($template->column_tabelload), 
        strip_tags($template->column_fixload), 
        strip_tags($template->column_planload), 
        strip_tags($template->column_executeload), 
        strip_tags($template->column_replace), 
        strip_tags($template->column_cancel),
        strip_tags($template->column_salarypoints), 
        );
if ( isset($template->forecast) AND $template->forecast )
{// корректировка
    $head[] = strip_tags($DOF->get_string('correction_for_previous_month', 'journal'));
}
$head[] = 'url';
$head[] = 'events';
$mas[] = $head;
// сами данные
foreach ( $template->column_persons as $key=>$obj )
{
    $val   = array();
    $val[] = $obj->teacher;
    $val[] = $obj->eagreement;
    // добавляем пробел, а то exel воспринимает как int и передние нули убирает
    // например 000262=262
    $val[] = (string)$obj->appoint;
    $val[] = $obj->tabelload;
    $val[] = $obj->fixload;
    $val[] = $obj->planload;
    $val[] = $obj->executeload;
    $val[] = $obj->replace;
    $val[] = $obj->cancel;
    $val[] = strip_tags($obj->salarypoints);
    if ( isset($template->forecast) AND $template->forecast )
    {// корректировка
        $val[] = strip_tags($obj->prevtotalrhours - $obj->prevforecast);
    }
    $val[] = $obj->url;
    $events = array();
    if ( ! empty($obj->events) )
    {
        foreach ( $obj->events as $event )
        {
            if ( $event->complete )
            {
                $events[] = $event->date.';'.$event->time.';'.$event->ahours.';'.
                $event->countstudents_salfactor.';'.$event->salfactor_programmitem.';'.
                $event->salfactor_programmsbcs.';'.$event->salfactor_agroups.';'.
                $event->salfactor_cstreams.';'.$event->salfactor_schtemplates.';'.
                $event->payment_combination.';'.$event->type.';'.$event->rhours;
            }
        }
    }
    $val[] = implode('%',$events);
    $mas[] = $val;
}

if ( $format == 'xls' )
{// импорт в xml
    $ready = array();
    $ready[] = $mas;
    $filename = 'report'.$reportid.'('.dof_userdate($template->_begindate,'%d.%m.%y').'-'.
                                       dof_userdate($template->_enddate,'%d.%m.%y').')['
                    .dof_userdate($reportobj->crondate,'%d.%m.%y_%H:%d').']['.$department.'].xls';
    // используем класс мудле для создания отчета
    $wb = otech_doffice_xls_table($ready);
	$wb->send($filename);
	$wb->close();    
}else 
{// импорт в csv
    // создаём файл 
    $handle = fopen($path, 'w');
    // создадим CSV файл
    foreach ( $mas as $val )
    {
         fputcsv($handle, $val, ';');
    }
    fclose($handle);
    
    // файл создан, теперь дадим возможность пользователю скачать его
    // посылаем заголовки на запрос сохранения файла
    header("Content-Type: application/octet-stream");
    header('Content-disposition: extension-token; filename=' . $filename);
    readfile($path);
}

?>