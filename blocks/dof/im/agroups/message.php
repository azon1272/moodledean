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
 * Отобразим форму для групповой рассылки сообщений
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получаем данные из GET
$agroupid = optional_param('agroupid', 0, PARAM_INT);
$depid = optional_param('departmentid', 0, PARAM_INT);
$success = optional_param('success', 0, PARAM_INT);
$error = optional_param('error', 0, PARAM_INT);

// Добавление уровня навигации - список групп
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'agroups'), 
                               $DOF->url_im('agroups', '/list.php', $addvars));

// Проверяем доступ
$DOF->storage('agroups')->require_access('view', $agroupid);

if ( ! $DOF->storage('agroups')->is_exists($agroupid) )
{// Не нашли группу
    $DOF->print_error(
            'error_agroup_not_found',
            $DOF->url_im('agroups', '/list.php', $addvars),
            NULL,
            'im',
            'agroups'
     );
}

// Добавим значение в массив для передачи по ссылке
$addvars['agroupid'] = $agroupid;
// Получаем объект группы
$agroup = $DOF->storage('agroups')->get($agroupid);

// Добавление уровней навигации
$DOF->modlib('nvg')->add_level($agroup->name.'['.$agroup->code.']',
        $DOF->url_im('agroups','/view.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('group_message', 'agroups'),
        $DOF->url_im('agroups','/message.php', $addvars));

// Определяем форму
$customdata = new stdClass;
$customdata->agroupid = $agroupid;
$customdata->depid = $depid;
$customdata->dof    = $DOF;
$form = new dof_im_agroups_groupmessage_form(
        $DOF->url_im('agroups','/message.php', $addvars), 
        $customdata
);

// Обработчик формы
$form->process();

// Шапка
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $success )
{// Сообщение было успешно отправлено
    echo html_writer::span(
            $DOF->get_string('group_message_send_success', 'agroups'),
            'dof_sussess',
            array('style' => 'color:green;')
    );
}
if ( $error )
{// Ошибки при отправке сообщений
    echo html_writer::span(
            $DOF->get_string('error_group_message_send_error', 'agroups'),
            'dof_error',
            array('style' => 'color:red;')
         );
}
// Отображение формы
$form->display();

// Подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>