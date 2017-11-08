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

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//проверяем полномочия на просмотр информации
$DOF->storage('reports')->require_access('view_report',NULL,NULL,$addvars['departmentid']);
// настройки
$config = $DOF->storage('config')->get_config('report_teachers', 'storage', 'reports', $addvars['departmentid']);
$default = new stdClass();
$default->dof = $DOF;
//выводим форму выбора даты
$depchoose = new dof_im_journal_reportteacher_short($DOF->url_im('employees','/reportteachers/index.php',$addvars), $default);
if ( $DOF->storage('reports')->is_access('request_mreports_teacher',NULL,NULL,$addvars['departmentid']) 
        AND ( ! empty($config->value) OR $DOF->is_access('datamanage')) )
{//проверяем полномочия на заказ отчета
    $depchoose->display();
}    
// загружаем метод работы с отчетом
$reportcl = $DOF->im('employees')->report('teachershort');
if ( $depchoose->is_submitted() AND confirm_sesskey() AND $formdata = $depchoose->get_data() )
{// формируем данные для отчета
    $reportdata = new stdClass();
    $reportdata->begindate = $formdata->begindate;
    $reportdata->enddate   = $formdata->enddate;
    $reportdata->crondate  = $formdata->crondate;
    $reportdata->personid  = $DOF->storage('persons')->get_by_moodleid_id();
    $reportdata->departmentid = $addvars['departmentid'];
    $reportdata->objectid = $addvars['departmentid'];
    if ( isset($formdata->buttonshort) )
    {
        $reportcl = $DOF->im('employees')->report('teachershort');
    }
    if ( isset($formdata->buttonfull) )
    {// созраняем заявку
        $reportcl = $DOF->im('employees')->report('teacherfull');
    }
    $reportcl->save($reportdata);
}

foreach(array('teachershort', 'teacherfull') as $reporttype)
{
    $reportcl = $DOF->im('programmsbcs')->report($reporttype);
    $options = new stdClass();
    $options->departmentid = $addvars['departmentid'];
    $options->plugintype = $reportcl->plugintype();
    $options->plugincode = $reportcl->plugincode();
    $options->code = $reportcl->code();
    // показываем если есть настройки для просмотра
    if ( $reports = $DOF->storage('reports')->get_report_listing($options,'requestdate DESC') AND 
            ( ! empty($config->value) OR $DOF->is_access('datamanage')) )
    {// найдены заказанные и сформированные отчеты
        foreach ( $reports as $report )
        {
            //уточним подразделение
            if ( $report->departmentid )
            {
                $dep = $DOF->storage('departments')->get_field($report->departmentid, 'code');
            }else 
            {// все подразделения
                $dep = $DOF->get_string('all_depart','employees');
            }
            // у старых отчетов этого поля ещё нет и чтобы не было notice
            if ( ! isset($report->crondate) OR ! $report->crondate )
            {
                $report->crondate = $report->requestdate;
            }
            if ( $report->status == 'requested' )
            {//если отчет заказан - выведем что он заказан
                $text =  '<br>['.dof_userdate($report->requestdate,'%d.%m.%Y %H:%M').
                '] '.$report->name.' ('.$DOF->get_string('status_request', 'employees').') 
                ['.$DOF->get_string('do_after','employees',
                dof_userdate($report->crondate,'%d.%m.%Y %H:%M')).']['.$dep.']';
            }elseif( $report->status == 'completed' AND $DOF->storage('reports')->is_access('view_mreports_teacher',$report->id) )
            {// отчет сгенерирован - выведем с сылкой на просмотр
                $text = '<br><a href="'.$DOF->url_im('employees','/reportteachers/view.php?id='.$report->id.'&type='.$report->code,$addvars).'" >'.
                '['.dof_userdate($report->requestdate,'%d.%m.%Y %H:%M').
                '] '.$report->name.' ('.$DOF->get_string('status_completed', 'employees').') 
                ['.$DOF->get_string('report_ready','employees',
                dof_userdate($report->completedate,'%d.%m.%Y %H:%M')).']['.$dep.'] </a>';
            }elseif ( $report->status == 'error' )
            {//ошибка генерации
                $text = '<br><font style=" color:red; text-align:center; ">['.
                dof_userdate($report->requestdate,'%d.%m.%Y %H:%M').'] '
                .$report->name.' ('.$DOF->get_string('status_error', 'employees').') 
                ['.$DOF->get_string('do_after','employees',
                dof_userdate($report->crondate,'%d.%m.%Y %H:%M')).']['.$dep.'] </font>';
            }
            // добавим ссылку на удаление
            if ( $DOF->storage('reports')->is_access('delete',$report->id) OR $report->personid == $DOF->storage('persons')->get_by_moodleid_id() )
            {
                
                $path = $DOF->url_im('employees','/reportteachers/delete.php?id='.$report->id,$addvars);
                $title = array('title'=>$DOF->modlib('ig')->igs('delete'));
                
                $text .=  $DOF->modlib('ig')->icon('delete',$path,$title);
            }
            print $text;
        }
    }
}    
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>