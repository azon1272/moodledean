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
 * Удаление компетенции
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
// Подтверждение удаления
$confirm = optional_param('confirm', 0, PARAM_INT);
// Права доступа
$DOF->workflow('skills')->require_access('changestatus', $addvars['id']);

$linkno = $DOF->url_im('skills', '/list.php', $addvars);

if ( $confirm )
{// Удалим компетенцию
    $DOF->workflow('skills')->change($addvars['id'], 'deleted');
    redirect($linkno);
}
// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('list_title', 'skills'),
        $DOF->url_im('skills', '/list.php'), $addvars);
$DOF->modlib('nvg')->add_level(
        $DOF->get_string('delete_skill', 'skills'),
        $DOF->url_im('skills','/delete.php'), $addvars
);

// Печать шапки
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Подготовим данные для подтверждения
$confirmation = $DOF->get_string('confirmation_delete_skill', 'skills');

$addvars['confirm'] = 1;
$linkyes = $DOF->url_im('skills', '/delete.php', $addvars);
// Отобразим форму подтверждения
$DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $linkno);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>