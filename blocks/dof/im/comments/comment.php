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
 * Интерфейс комментариев к объектам Деканата. Обработчик комментария.
 * 
 * @package    im
 * @subpackage comments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получение GET параметров
$id = optional_param('id', 0, PARAM_INT);
$text = optional_param('task', 0, PARAM_TEXT);
$ptype = optional_param('plugintype', '', PARAM_TEXT);
$pcode = optional_param('plugincode', '', PARAM_TEXT);
$objectid = optional_param('objectid', 0, PARAM_INT);
$code = optional_param('code', NULL, PARAM_TEXT);
$returnurl = optional_param('returnurl', NULL, PARAM_URL);

// Получим объект формы
$url = $DOF->url_im('comments','/comment.php', $addvars);
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->plugintype = $ptype;
$customdata->plugincode = $pcode;
$customdata->objectid = $objectid;
$customdata->code = $code;
$customdata->task = $text;
$customdata->returnurl = $returnurl;
$form = new dof_im_comments_form($url, $customdata);

$form->process();

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$form->display();

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>