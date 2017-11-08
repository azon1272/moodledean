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

// примем тип отчета
$type = optional_param('type','',PARAM_TEXT);

// объект для формы по заказу отчетов
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->type = $type;
$customdata->depid = $addvars['departmentid'];
$customdata->categoryid = $addvars['invcategoryid'];
//выводим форму заказа отчета
$form = new dof_im_inventory_report_sets_and_items($DOF->url_im('inventory','/reports/index.php',$addvars), $customdata);
// обработчик формы
$form->process($addvars);

// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//проверяем полномочия на просмотр информации
$DOF->storage('reports')->require_access('view_report',NULL,NULL,$addvars['departmentid']);

// доп навигация по категориям
echo $DOF->im('inventory')->additional_nvg('/reports/index.php', $addvars);

if ( $DOF->storage('reports')->is_access('request_inventory',NULL,NULL,$addvars['departmentid']) AND
        (! empty($config->value) OR $DOF->is_access('datamanage')) )
{//проверяем полномочия на заказ отчета
    $form->display();
} 


// отчет по персонам у которых имеется оборудование
$reportitems = $DOF->im('inventory')->report('loadpersons');  
$options = new stdClass();
$options->departmentid = $addvars['departmentid'];
$options->plugintype = $reportitems->plugintype();
$options->plugincode = $reportitems->plugincode();
$options->code = $reportitems->code();
if ( ! $reportsitems = $DOF->storage('reports')->get_report_listing($options,'requestdate DESC') )
{
    $reportsitems = array();
} 

// отчет по персонам у которых имеется оборудование
$reportsets = $DOF->im('inventory')->report('loaditems');  
$options = new stdClass();
$options->departmentid = $addvars['departmentid'];
$options->plugintype = $reportsets->plugintype();
$options->plugincode = $reportsets->plugincode();
if ( ! empty($addvars['invcategoryid']) )
{
    $options->objectid = $addvars['invcategoryid'];
}    
$options->code = $reportsets->code();
if ( ! $reportsets = $DOF->storage('reports')->get_report_listing($options,'requestdate DESC') )
{
    $reportsets = array();
}

// объединим массивы
$reports = array();
// ключи массивов пересекаться не будут, т.к. это из 1 таблицы данные
$reports = $reportsitems+$reportsets;



if ( $reports AND (! empty($config->value) OR $DOF->is_access('datamanage')) )
{// найдены заказанные и сформированные отчеты
    foreach ( $reports as $report )
    {
        //уточним подразделение
        if ( $report->departmentid )
        {
            $dep = $DOF->storage('departments')->get_field($report->departmentid, 'code');
        }else 
        {// все подразделения
            $dep = $DOF->get_string('all_depart','inventory');
        }
        // у старых отчетов этого поля ещё нет и чтобы не было notice
        if ( ! isset($report->crondate) OR ! $report->crondate )
        {
            $report->crondate = $report->requestdate;
        }
        if ( $report->status == 'requested' )
        {//если отчет заказан - выведем что он заказан
            $text =  '<br>['.dof_userdate($report->requestdate,'%d.%m.%Y %H:%M').
            '] '.$report->name.' ('.$DOF->get_string('status_request', 'inventory').') 
            ['.$DOF->get_string('do_after','employees',
            dof_userdate($report->crondate,'%d.%m.%Y %H:%M')).']['.$dep.'] ';
        }elseif( $report->status == 'completed' AND $DOF->storage('reports')->is_access('view_inventory',$report->id) )
        {// отчет сгенерирован - выведем с сылкой на просмотр
            $text =  '<br><a id="reportid_'.$report->id.'" href="'.$DOF->url_im('inventory','/reports/view.php?id='.$report->id.'&type='.$report->code,$addvars).'" >'.
            '['.dof_userdate($report->requestdate,'%d.%m.%Y %H:%M').
            '] '.$report->name.' ('.$DOF->get_string('status_completed', 'employees').') 
            ['.$DOF->get_string('report_ready','employees',
            dof_userdate($report->completedate,'%d.%m.%Y %H:%M')).']['.$dep.'] </a>';
        }
        // добавим ссылку на удаление
        if ( $DOF->storage('reports')->is_access('delete',$report->id) OR $report->personid == $DOF->storage('persons')->get_by_moodleid_id() )
        {
            
            $path = $DOF->url_im('inventory','/reports/delete.php?id='.$report->id,$addvars);
            $title = array('title'=>$DOF->modlib('ig')->igs('delete'));
            
            $text .=  $DOF->modlib('ig')->icon('delete',$path,$title);
        }
        print $text;          
        
    }
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>