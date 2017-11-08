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
 * Журнал. История обучения по дисциплине.
 *
 * @package    im
 * @subpackage journal
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

// Получение GET-параметров
$programmsbcid  = required_param('programmsbcid', PARAM_INT);
$programmitemid = required_param('programmitemid', PARAM_INT);

// Проверяем полномочия на просмотр информации
$DOF->storage('programmsbcs')->require_access('view');
$DOF->storage('programmitems')->require_access('view');
$DOF->storage('cpassed')->require_access('view');

$params = array('programmsbcid' => $programmsbcid, 'programmitemid' => $programmitemid);
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('cphistory','journal'), $DOF->url_im('journal','/cphistory.php', $addvars + $params));

$html = $DOF->im('journal')->show_cphistory($programmsbcid, $programmitemid);
// Выводим шапку
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
print($html);
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');
?>