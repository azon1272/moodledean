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
 * Экспорт отчетов
 *
 * @package    im
 * @subpackage reports
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получение параметров запроса
$reportid   = required_param('id', PARAM_INT);
$plugintype = required_param('plugintype', PARAM_TEXT);
$plugincode = required_param('plugincode', PARAM_TEXT);
$code       = required_param('code', PARAM_TEXT);
$exporttype = required_param('export', PARAM_TEXT);

// Проверка доступа
switch ($plugintype)
{
    case 'im' :
    case 'storage' :
    case 'workflow' :
    case 'sync' :
        // Проверка доступа
        $DOF->$plugintype($plugincode)->require_access('export_report_'.$code, $reportid);
        break;
    default:
        // Ошибочный тип плагина
        $notice = "error plugin type: $plugintype";
        $DOF->print_error('nopermissions','',$notice);
        break;
}

// Получение отчета
$reportobj = $DOF->storage('reports')->get($reportid);
if ( empty($reportobj) )
{
    $DOF->print_error('reportnotfound');
}

$report = $DOF->storage('reports')->report($plugintype, $plugincode, $code, $reportid);
$template = $report->load_file();
$templater_package = $DOF->modlib('templater')->template( $plugintype, $plugincode, $template, $code);

// Выбираем формат экспорта
switch ($exporttype)
{
    case 'odf' : 
        $templater_package->send_file('odf');
        break;
    case 'dbg' :
        $templater_package->send_file('dbg');
        break;
    default :
    case 'csv' :
        $templater_package->send_file('csv');
        break;
}

?>