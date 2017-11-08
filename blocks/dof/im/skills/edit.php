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
 * Редактирование компетенции
 *
 * @package    im
 * @subpackage skills
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Сформируем массив GET параметров
$addvars['id'] = optional_param('id', 0, PARAM_INT);

// Права доступа
$DOF->im('skills')->require_access('edit', $addvars['id']);

// Сформируем url формы
$url = $DOF->url_im('skills', '/edit.php', $addvars);
// Сформируем дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $addvars;

// Сформируем форму
$form = new dof_im_skills_edit_form($url, $customdata);

// Обработчик формы
$form->process();


// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('list_title', 'skills'),
        $DOF->url_im('skills', '/list.php'), $addvars);
if ( empty($addvars['id']) )
{
    $DOF->modlib('nvg')->add_level(
            $DOF->get_string('create_skill', 'skills'),
            $DOF->url_im('skills', '/edit.php'), $addvars);
} else
{
    $DOF->modlib('nvg')->add_level(
            $DOF->get_string('edit_skill', 'skills'),
            $DOF->url_im('skills', '/edit.php'), $addvars);
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Сообщение о результате работы
$message = optional_param('success', NULL, PARAM_INT);
if ( $message === 1 )
{
    echo $DOF->modlib('widgets')->success_message($DOF->get_string('message_form_skills_edit_save_sussess', 'skills'));
}
if ( $message === 0 )
{
    echo $DOF->modlib('widgets')->error_message($DOF->get_string('message_form_skills_edit_save_error', 'skills'));
}

// Отобразим форму
$form->display();

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>