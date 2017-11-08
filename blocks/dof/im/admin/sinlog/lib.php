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
require_once('../lib.php');

$addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);

/** Создание таблицы с ошибками синхронизаций
 *  @return html $table
 */
function print_error_table() 
{
    global $DOF;
    
    $table = new stdClass();

    $table->cellpadding = 5;
    $table->cellspacing = 5;
    
    $table->head = array('ID',
            $DOF->get_string('sinlog_syncid', 'admin'),
            $DOF->get_string('sinlog_exectime', 'admin'),
            $DOF->get_string('sinlog_operation', 'admin'),
            $DOF->get_string('sinlog_direct', 'admin'),
            $DOF->get_string('sinlog_prevoperation', 'admin'),
            $DOF->get_string('sinlog_textlog', 'admin'),
            $DOF->get_string('sinlog_optlog', 'admin'));
    $table->align = array('center','center','center','center','center','center','center','center');
    
    $data = get_log_data();
    
    if ( empty($data) )
    {// данных нет - выходим
        return '';
    }
    
    $table->data = array();
    
    foreach ( $data as $line )
    {
        unset($line[6]);
        $line[2] = date('Y-m-d H:i:s', $line[2]);
        $table->data[] = $line;
    }
    
    //выводим таблицу на экран
    return $DOF->modlib('widgets')->print_table($table, true);
}

/** Получение данных из лога
 * @return array $logdata
 * 
 */
function get_log_data()
{
    global $DOF;
    
    $logdata = array();
    
    $filename = $DOF->plugin_path('storage', 'synclogs', '/dat/synclogs_errors.log');
    if ( ! file_exists($filename) OR ! $file = file($filename) )
    {// файла не существует - вернем пустой массив
        return array();
    }
    
    foreach($file as $line)
    {
        $logdata[] = explode('|', $line);
    }
    return $logdata;
}

?>