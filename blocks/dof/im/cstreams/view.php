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
require_once($DOF->plugin_path('im', 'cstreams', '/form.php'));
// id потока, группы которого надо синхронизировать с группами
$cstreamsyncid  = optional_param('cstreamsyncid', 0, PARAM_INT);
// тип экспортируемого файла - если нужно произвести экспорт
$export         = optional_param('export', '', PARAM_ALPHANUM);
// маркер совершенного действия - если новые ученики подписываются на поток
$addstudents    = optional_param('add', false, PARAM_BOOL);
// маркер совершенного действия - если новые ученики отдписываются с потока
$removestudents = optional_param('remove', false, PARAM_BOOL);
$conds = new stdClass;
// id подразделения
$conds->departmentid   = optional_param('departmentid', null, PARAM_INT);
//id программы 
$conds->programmid     = optional_param('programmid', null, PARAM_INT);
//id предмета 
$conds->programmitemid = optional_param('programmitemid', null, PARAM_INT);
//id предмета 
$conds->appointmentid  = optional_param('appointmentid', null, PARAM_INT);
//id академической группы 
$conds->agroupid       = optional_param('agroupid', null, PARAM_INT);
//id ученика 
$conds->personid       = optional_param('personid', null, PARAM_INT);
// статус предмето-потока
$conds->status         = optional_param('status', '', PARAM_ALPHA);
// учебный период
$conds->ageid          = optional_param('ageid', null, PARAM_INT);
if ( $cstreamsyncid )
{// если передан id синхронизируемого потока, значит его же надо и отобразить
    $cstreamid = $cstreamsyncid;
}else
{// если поток синхронизировать не надо - значит обязательно надо узнать какой поток показывать
    $cstreamid = required_param('cstreamid', PARAM_INT);
}
if ( $export )
{// если нужно произвести экспорт
    $DOF->im('cstreams')->require_access('export');
    // @todo вынести обработку в отдельный файл
    // создаем объект с созданными данными
    $obj = new dof_im_cstreams_students_grades_odf($DOF, $cstreamid);
    // создаем оъект шаблонизатора, и загружаем в него данные
    $exporter = $DOF->modlib('templater')->template('im', 'cstreams', $obj->get_data(), 'examlist');
    // устанавливаем собственное имя для файла экспорта
    $options = new stdClass();
    $options->filename = 'Vedomost_'.dof_userdate(time(),'%Y-%m-%d');
    // В зависимости от формата производим экспорт
    switch ( $export )
    {// пока экспорт только для формата odf
        case 'odf': $exporter->send_file('odf', $options);die;
    }
}
// получаем из базы объект объект потока, чтобы името доступ к его полям
if ( ! $cstreamobj = $DOF->storage('cstreams')->get($cstreamid) )
{// если поток не найден, выведем ошибку
	print_error($DOF->get_string('no_cstreams_found','cstreams', $cstreamid));
}

// строка для вывода сообщений
$message = '';
//проверяем доступ
$DOF->storage('cstreams')->require_access('view', $cstreamid);
// создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
// объявляем форму смены статуса
$statusform = new dof_im_cstreams_changestatus_form($DOF->url_im('cstreams', '/view.php?cstreamid='.$cstreamid,$addvars), $customdata);
$statusform->process();

// объявляем форму режима просмотра списка подписываемых учеников
$viewmodeform = new dof_im_cstreams_viewmode_form($DOF->url_im('cstreams', '/view.php?cstreamid='.$cstreamid,$addvars), $customdata);
//вывод на экран
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
if ( $progitem = $DOF->storage('programmitems')->get($cstreamobj->programmitemid) )
{
    $DOF->modlib('nvg')->add_level($progitem->name.'['.$progitem->code.']', 
                         $DOF->url_im('programmitems','/view.php?pitemid='.$progitem->id,$addvars));
}
$DOF->modlib('nvg')->add_level($cstreamobj->name,
                     $DOF->url_im('cstreams','/view.php?cstreamid='.$cstreamid,$addvars));

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $cstream = $DOF->im('cstreams')->show_id($cstreamid,$conds) )
{// если поток не найден, выведем ошибку
	print_error($DOF->get_string('notfound','cstreams', $cstreamid));
}
if ( $cstreamsyncid AND $cstreamid != $cstreamsyncid )
{// неверно указаны параметры синхронизации
    print_error($DOF->get_string('notfound','cstreams', $cstreamid));
}
// получаем из базы 

// обрабатываем событие синхронизации
// @todo вывести его в отдельный файл
if ( $cstreamsyncid )
{// если нужно синхронизировать события
    if ( $DOF->storage('cstreams')->is_access('edit', $cstreamsyncid) )
    {// если есть права на синхронизацию - то приступаем к ней
        // @todo проставить более продуманные права доступа, либо завести собственную категорию
        // прав для синхронизации
        if ( $DOF->storage('cpassed')->syncronize_agroups_with_cstream($cstreamid) )
        {// если удалось произвести синхронизацию всех академических групп с потоками
            // то выведем сообщение о том, что это удалось
            $message .= '<br/><b style=" color:green; ">'.
                        $DOF->get_string('sync_cstream_agroups_successful', 'cstreams').'</b>';
        }else
        {// если не удалось произвести синхронизацию - то скажем об этом
            $message .= '<br/><b style=" color:red; ">'.
                        $DOF->get_string('sync_cstream_agroups_failed', 'cstreams').'</b>';
        }
    }
}

//покажем ссылку на создание нового потока
if ( $DOF->storage('cstreams')->is_access('create') )
{// если есть право на создание потока 
    if ( $DOF->storage('config')->get_limitobject('cstreams',$conds->departmentid) )
    {
        $link = '<a href='.$DOF->url_im('cstreams','/edit.php',$conds).'>'.
                $DOF->get_string('newcstream', 'cstreams').'</a>';
        echo '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('newcstream', 'cstreams').
        	' ('.$DOF->get_string('limit_message','cstreams').')</span>';
        echo '<br>'.$link.'<br>'; 
    } 
}
if ( $DOF->im('cstreams')->is_access('export') )
{// выводим ссылку на экспорт файла в формат odf
    print('<p><a href="'.$DOF->url_im('cstreams', '/view.php?cstreamid='.$cstreamid.'&export=odf').'">'.
    $DOF->get_string('get_exam_sheet_in_odf', 'cstreams').'</a></p>');
}


// выводим сообщения, если они есть
if ( $message )
{
    print('<p align="left">'.$message.'</p>');
}
// выводим поток
echo '<br>'.$cstream;

if ( $DOF->workflow('cstreams')->is_access('changestatus') )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // добавляем данные периода
    $dataobj     = new stdClass();
    $dataobj->id = $cstreamid;
    // устанавливаем значения по умолчанию
    $statusform->set_data($dataobj);
    // показываем форму
    $statusform->display();
}

if ( $DOF->storage('cpassed')->is_access('view') AND 
        $cstreamobj->status != 'completed' AND
        $cstreamobj->status != 'canceled' )
{// если у пользователя есть право создавать и редактировать подписки на предметы
    // и если подписка находится в нужном статусе - то покажем форму записи учеников на поток
    // подключаем файл, в котором находится код и стандартные значения для элемента "добавить/удалить"
    require_once($DOF->plugin_path('im', 'cstreams', '/view_addremove.php'));
}

echo $DOF->im('cstreams')->get_table_statushistory($cstreamid);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>