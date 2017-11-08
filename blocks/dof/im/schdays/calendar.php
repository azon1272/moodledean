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
// Проверяем полномочия на просмотр информации
$DOF->storage('schdays')->require_access('view');
// Получаем параметры из запроса
$complete = optional_param('complete', 0, PARAM_INT);

// Сформируем данные формы
$formdata = new stdClass();
$formdata->dof = $DOF;
$formdata->age = $DOF->storage('ages')->get($addvars['ageid']);
$formdata->departmentid = $addvars['departmentid'];
$formdata->addvars = $addvars;
// Инициализируем форму календаря - массовая обработка дней
$calendar = new dof_im_schdays_calendar_form(
        $DOF->url_im('schdays', '/calendar.php', $addvars), 
        $formdata
);

// Обработчик формы
$errors = $calendar->process();

// Шапка страницы
$DOF->modlib('nvg')->add_css('im', 'schdays', '/styles.css');
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Массовые функции
if ( $DOF->storage('schdays')->is_access('create') )
{// Право на создание дня есть
    $link = html_writer::link(
            $DOF->url_im('schdays','/autocreate_days.php',$addvars), 
            $DOF->get_string('auto_create_days', 'schdays'),
            array('class' => 'calendar_autofunctions')
    );
    echo $link;
}
if ( $DOF->storage('schevents')->is_access('create') AND 
        ( ! $DB->get_records(
                'block_dof_todo',
                array( 
                        'exdate' => 0, 
                        'plugintype' => 'im',
                        'plugincode' => 'schdays',
                        'todocode' => 'auto_create_events'
                )
            ) 
        )
    )
{// Право на создание событий имеется, а также нет аналогичных задач на выполнение
    $link = html_writer::link(
            $DOF->url_im('schdays','/autocreate_events.php?type=create',$addvars),
            $DOF->get_string('auto_create_events', 'schdays'),
            array('class' => 'calendar_autofunctions')
    );
    echo $link;
}
if ( $DOF->storage('schevents')->is_access('delete') AND 
     $DOF->storage('schevents')->is_access('create') AND
        ( ! $DB->get_records(
                'block_dof_todo',
                array(
                        'exdate' => 0,
                        'plugintype' => 'im',
                        'plugincode' => 'schdays',
                        'todocode' => 'auto_update_events'
                )
            ) 
        )
   )
{// Право на обновление событий имеется, а также нет аналогичных задач на выполнение
    $link = html_writer::link(
        $DOF->url_im('schdays','/autocreate_events.php?type=update',$addvars),
        $DOF->get_string('auto_update_events', 'schdays'),
        array('class' => 'calendar_autofunctions')
    );
    echo $link;
}
if ( $DOF->storage('schevents')->is_access('delete') AND 
        ( ! $DB->get_records(
                'block_dof_todo',
                array(
                        'exdate' => 0,
                        'plugintype' => 'im',
                        'plugincode' => 'schdays',
                        'todocode' => 'auto_delete_events'
                )
            ) 
        )
    )
{// Право на удаление событий имеется, а также нет аналогичных задач на выполнение
    $link = html_writer::link(
        $DOF->url_im('schdays','/autocreate_events.php?type=delete',$addvars),
        $DOF->get_string('auto_delete_events', 'schdays'),
        array('class' => 'calendar_autofunctions')
    );
    echo $link;
}

// Результат массовых операций над днями
if ( ! empty($errors) )
{// Есть ошибки
    echo html_writer::div( 
            $DOF->get_string('error_day_errors_exist', 'schdays'), 
            'calendarmessage'
         );
    $table = new stdClass();   
    foreach ( $errors as $dayid => $error )
    {
        $table->data[] = array($dayid, $error);
    }
    echo $DOF->modlib('widgets')->print_table($table, true);
    echo html_writer::link($DOF->url_im(
            'schdays', '/calendar.php', $addvars), 
            $DOF->get_string('form_days_link_continue', 'schdays'),
            array('class' => 'continuelink')
    );
} else 
{// Завершено без ошибок
    if ( ! empty($complete) )
    {
        echo html_writer::div( $DOF->get_string('form_days_complete', 'schdays'), 'calendarmessage' );
    } 
    // Отобразить календарь
    $calendar->display();
}

// Подвал страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>