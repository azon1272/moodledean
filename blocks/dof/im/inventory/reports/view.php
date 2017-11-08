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

// id отчета
$reportid = required_param('id', PARAM_INT);
// тип точета
$type = required_param('type', PARAM_TEXT);
$addvars['id'] = $reportid;


$DOF->modlib('nvg')->add_level($DOF->storage('reports')->get_field($reportid, 'name'), 
                                $DOF->url_im('inventory','/reports/view.php',$addvars));
                                
$table = '';
$error = '';
// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);
// проверка на правильность данных
$report = $DOF->storage('reports')->get($reportid);

$typearray = array('loadpersons', 'loaditems');

if ( $report AND in_array($report->code,$typearray) AND in_array($type,$typearray) )
{
    // загружаем метод работы с отчетом
    $report = $DOF->im('inventory')->report($type,$reportid);


    if ( ! $report->is_generate($report->load()) )
    {//  отчет еще не сгенерирован
        $error = $DOF->get_string('report_no_generate','inventory');
    }else
    {// загружаем шаблон
        // достаем данные из файла
        $template = $report->load_file();

        // подгружаем методы работы с шаблоном
        if ( isset($template->column_persons) OR isset($template->column_items) )
        {
            if ( ! $templater = $report->template() )
            {//не смогли
                $error = $DOF->get_string('report_no_get_template','inventory');
            }elseif ( ! $table = $templater->get_file('html') )
            {// не смогли загрузить html-таблицу
                $error = $DOF->get_string('report_no_get_table','inventory');
            }
        }else 
        {
            $error = $DOF->get_string('no_data','inventory','<br>');
        }    
    }
}else 
{
    $error = $DOF->get_string('notfoundage','inventory');
}    

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//проверяем полномочия на просмотр информации
$DOF->storage('reports')->require_access('view_inventory',$reportid);

// вывод ошибок
print '<p style=" color:red; text-align:center; "><b>'.$error.'</b></p>';

// показываем отчет
if ( ! empty($config->value) OR $DOF->is_access('datamanage') )
{
    echo $table;
}    
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>