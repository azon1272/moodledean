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
 * отображает одну запись по ее id 
 */

// Подключаем библиотеки
require_once('lib.php');
// Подключаем формы
require_once($DOF->plugin_path('im', 'agroups', '/form.php'));
// id подразделения, внутри которого совершаются все действия
$departmentid = optional_param('departmentid', 0, PARAM_INT);
// id группы, которую надо синхронизировать с потоком
$agroupsyncid = optional_param('agroupsyncid', 0, PARAM_INT);
// тип экспорта - если нужно экспортировать данные
$export       = optional_param('export', '', PARAM_ALPHANUM);

// подписка и отписка учеников из группы
// Оба списка передаются через POST массивом, поэтому автоматическая проверка данных не используется (PARAM_RAW)
// Списки учеников проверяются позже

// список тех, кого надо добавить в группу
$addstudentlist    = optional_param_array('addselect',    null, PARAM_RAW);
// список тех, кого надо исключить из группы
$removestudentlist = optional_param_array('removeselect', null, PARAM_RAW);

if ( $agroupsyncid )
{// если передан id синхронизируемой группы - значит ее и надо показывать
    $agroupid = $agroupsyncid;
}else
{// если группу синхронизировать не надо - значит обязательно надо узнать какую группу показывать
    $agroupid = required_param('agroupid', PARAM_INT);
}

if ( $export )
{// если нужно произвести экспорт
    $DOF->im('agroups')->require_access('makeexport');
    // @todo вынести обработку в отдельный файл
    // подключаем библиотеку с классом сбора данных
    require_once($DOF->plugin_path('im', 'cstreams', '/lib.php'));
    // создаем объект с созданными данными
    $obj = new dof_im_cstreams_students_grades_odf($DOF, false, $agroupid);
    // создаем оъект шаблонизатора, и загружаем в него данные
    $exporter = $DOF->modlib('templater')->template('im', 'agroups', $obj->get_agroup_data(), 'examlist');
    // устанавливаем собственное имя для файла экспорта
    $options = new stdClass();
    $options->filename = 'Vedomost_'.dof_userdate(time(),'%Y-%m-%d');
    // В зависимости от формата производим экспорт
    switch ( $export )
    {// пока экспорт только для формата odf
        case 'odf': $exporter->send_file('odf', $options);die;
    }
}

// обработка отчисления/зачисления ученика в группу
$addremoveresult = '';
if ( is_array($addstudentlist) AND ! empty($addstudentlist) )
{// есть ученики которых нужно записать в группу
    // записываем ученика в группу
    $addremoveresult = $DOF->im('agroups')->process_addremove_students('add', $addstudentlist, $agroupid);
    // в зависимости от результата выводим сообщение
    $addremoveresult = $DOF->im('agroups')->get_addremove_students_result_message('add', $addremoveresult);
}
if ( is_array($removestudentlist) AND ! empty($removestudentlist) )
{// есть ученики которых нужно исключить из группы
    // выписываем ученика из группы
    $addremoveresult = $DOF->im('agroups')->process_addremove_students('remove', $removestudentlist, $agroupid);
    // в зависимости от результата выводим сообщение
    $addremoveresult = $DOF->im('agroups')->get_addremove_students_result_message('remove', $addremoveresult);
}

// строка для вывода сообщений
$message       = '';
//проверяем доступ
$DOF->storage('agroups')->require_access('view', $agroupid);

// создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id  = $agroupid;
// объявляем форму
$statusform = new dof_im_agroups_changestatus_form($DOF->url_im('agroups', '/view.php?agroupid='.$agroupid,$addvars), $customdata);
// обрабатываем данные
$statusform->process();
//вывод на экран
//добавление уровня навигации
$agroup = $DOF->storage('agroups')->get($agroupid);
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'agroups'), 
                               $DOF->url_im('agroups','/list.php'),$addvars);
if ( $agroup )
{// нет группы - не показываем название группы в навигаци
    $DOF->modlib('nvg')->add_level($agroup->name.'['.$agroup->code.']',
                         $DOF->url_im('agroups','/view.php?agroupid='.$agroupid,$addvars));
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'), 
                         $DOF->url_im('agroups','/list.php'),$addvars);
}
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $agroup = $DOF->im('agroups')->show_id($agroupid,$addvars) )
{// если период не найден, выведем ошибку
	print_error($DOF->get_string('notfound','agroups', $agroupid));
}
if ( $programmid = $DOF->storage('agroups')->get_field($agroupid,'programmid') )
{//если id программы указано - покажем ссылки
    //покажем ссылку на создание новой группы
    if ( $DOF->storage('agroups')->is_access('create') )
    {// если есть право создавать группу
        if ( $DOF->storage('config')->get_limitobject('agroups',$addvars['departmentid']) )
        {
            $link = '<a href='.$DOF->url_im('agroups','/edit.php',$addvars).'>'.$DOF->get_string('newagroup', 'agroups').'</a>';
            echo '<br>'.$link;
        }else 
        {
            $link =  '<span style="color:silver;">'.$DOF->get_string('newagroup', 'agroups').
            	' ('.$DOF->get_string('limit_message','agroups').')</span>';
            echo '<br>'.$link; 
        }  
    }
    // на программу
    if ( $DOF->storage('programms')->is_access('view', $programmid) )
    {// если есть право просматривать программу
       $link_programm = '<a href='.$DOF->url_im('programms','/view.php?programmid='.$programmid,$addvars).'>'
             .$DOF->get_string('view_programm', 'programmsbcs').' '
             .$DOF->storage('programms')->get_field($programmid, 'name')
             .'['.$DOF->storage('programms')->get_field($programmid, 'code').']</a>';
        echo '<br>'.$link_programm;
    }
    // на все предметы
    if ( $DOF->storage('programmitems')->is_access('view') )
    {// если есть право просматривать все предметы одной программы
        $link_programmitems = '<a href='.$DOF->url_im('programmitems','/list_agenum.php?programmid='.$programmid,$addvars).'>'
            .$DOF->get_string('view_all_programmitems', 'programmsbcs').'</a>';
        echo '<br>'.$link_programmitems;
    }
    if ( $DOF->im('agroups')->is_access('makeexport') )
    {// если нужно получить список группы для ведомости
       $link_export = '<a href='.$DOF->url_im('agroups','/view.php?agroupid='.$agroupid,$addvars).'&export=odf>'
             .$DOF->get_string('get_studentlist_for_export', 'agroups').'</a>';
        echo '<br>'.$link_export.'<br>';
    }
}
// обрабатываем событие синхронизации
// @todo вывести его в отдельный файл
if ( $agroupsyncid )
{// если нужно синхронизировать события
    if ( $DOF->storage('agroups')->is_access('edit', $agroupsyncid) )
    {// если есть права на синхронизацию - то приступаем к ней
        // @todo проставить более продуманные права доступа, либо завести собственную категорию
        // прав для синхронизации
        if ( $DOF->storage('cpassed')->syncronize_agroup_with_cstreams($agroupsyncid) )
        {// если удалось произвести синхронизацию всех академических групп с потоками
            // то выведем сообщение о том, что это удалось
            $message .= '<br/><b style=" color:green; ">'.
                        $DOF->get_string('sync_agroup_successful', 'agroups').'</b>';
        }else
        {// если не удалось произвести синхронизацию - то скажем об этом
            $message .= '<br/><b style=" color:red; ">'.
                        $DOF->get_string('sync_agroup_failed', 'agroups').'</b>';
        }
    }
}
// выводим сообщения, если они есть
if ( $message )
{
    print('<p align="center">'.$message.'</p>');
}
// выводим информацию о группе
echo '<br>'.$agroup;

if ( $DOF->workflow('agroups')->is_access('changestatus',$agroupid) )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // добавляем данные группы
    $dataobj = new stdClass();
    $dataobj->id = $agroupid;
    // устанавливаем значения по умолчанию
    $statusform->set_data($dataobj);
    // показываем форму
    $statusform->display();
}

// Форма записи учеников в группу в виде двустороннего списка
// получаем объект формы 
$addremove = $DOF->modlib('widgets')->addremove();
// Устанавливаем надписи в форме
$addremovestrings = new stdClass();
$addremovestrings->addlabel    = $DOF->get_string('can_be_added_to_a_group', 'agroups');
$addremovestrings->removelabel = $DOF->get_string('group_list', 'agroups');
$addremovestrings->addarrow    = $DOF->modlib('ig')->igs('add');
$addremovestrings->removearrow = $DOF->modlib('ig')->igs('remove');
$addremove->set_default_strings($addremovestrings);
// список учеников уже входящих в группу
$addremove->set_remove_list($DOF->im('agroups')->get_students_to_remove($agroupid));
// список учеников доступных для записи в группу
$addremove->set_complex_add_list($DOF->im('agroups')->get_students_to_add($agroupid));
// отображаем сообщение об успешном/неуспешном зачислении или удалении из группы
echo $addremoveresult;
// Отображаем форму
$addremove->print_html();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>