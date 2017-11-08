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
// Copyright (C) 2008-2999  Dmitrii Shtolin (Дмитрий Штолин)              //
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
require_once(dirname(realpath(__FILE__)).'/lib.php');
require_once('form.php');

// Защищаем списки пользователей от случайного доступа
$DOF->storage('persons')->require_access('view');
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('persons', 'persons'), $DOF->url_im('persons', '/list.php'), $addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('extendedsearch', 'persons'), $DOF->url_im('persons', '/extendedsearch.php', $addvars));

$limitfrom = $addvars['limitfrom'] = optional_param('limitfrom', 1, PARAM_INT);
$limitnum  = $addvars['limitnum']  = optional_param('limitnum', $DOF->modlib('widgets')->get_limitnum_bydefault(), PARAM_INT);
$customdata = new stdClass();
$customdata->dof = $DOF;
$searchform = new dof_im_person_extendedsearch_form($DOF->url_im('persons', '/extendedsearch.php', $addvars), $customdata);
$searchform->process();

$conds = new stdClass();
//$conds->departmentid=(int)optional_param('departmentid', 0, PARAM_INT);
$conds->lastname       = optional_param('lastname', "", PARAM_TEXT);
$conds->firstname      = optional_param('firstname', "", PARAM_TEXT);
$conds->middlename     = optional_param('middlename', "", PARAM_TEXT);
$conds->contractnum    = optional_param('contractnum', "", PARAM_TEXT);
$conds->phone          = optional_param('phone', "", PARAM_TEXT);
$conds->email          = optional_param('email', "", PARAM_TEXT);
$conds->programmid     = optional_param('programmid', 0, PARAM_INT);
$conds->currentageid   = optional_param('currentageid', 0, PARAM_INT);
$conds->startageid     = optional_param('startageid', 0, PARAM_INT);
$conds->currentagenum  = optional_param('currentagenum', 0, PARAM_INT);
$conds->curatorid      = optional_param('curatorid', 0, PARAM_INT);
//$pbcsstatus = !isset($_GET['limitfrom']) ? array("active" => 1, "condactive" => 1) : array();
$conds->pbcsstatus     = optional_param('pbcsstatus', 'active,condactive', PARAM_TEXT);
$conds->contractstatus = optional_param('contractstatus', 'work', PARAM_TEXT);
$conds->children       = optional_param('children', 0, PARAM_INT);

$setdata = clone $conds;
// Сделаем из строк массивы вида: [ 'status' => 1, 'status2' => 1, ...]
$keys                    = explode(',', $setdata->pbcsstatus);
$setdata->pbcsstatus     = array_fill_keys($keys, 1);
$keys                    = explode(',', $setdata->contractstatus);
$setdata->contractstatus = array_fill_keys($keys, 1);

$searchform->set_data($setdata);

// Выводим шапку в режиме "портала
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

// Форма запроса
$searchform->display();

// Формируем список, состоящий из id подходящих под фильтры пользователей
$list = $DOF->storage('persons')->get_list_extendedsearch($setdata, $addvars['departmentid'], ($limitfrom - 1), $limitnum);
$countrows = $DOF->storage('persons')->get_list_extendedsearch($setdata, $addvars['departmentid'], ($limitfrom - 1), $limitnum, true);
// Выводим пользователей на экран
$DOF->im('persons')->show_list_as_cards($list, $addvars);

// Формируем и выводим навигацию
// $countrows содержит число всех записей, полученных без учёта limitfrom, limitnum
$pages = $DOF->modlib('widgets')->pages_navigation('persons', $countrows, $limitnum, $limitfrom);
$foundusers = $DOF->get_string('usersfound', 'persons');
echo "<p align=\"center\">{$foundusers}: " . $pages->count . "</p>";
$pagesstring = $pages->get_navpages_list('/extendedsearch.php', (array)$conds +
        array_merge($addvars, array("limitnum" => $limitnum, "limitfrom" => $limitfrom)));
echo $pagesstring;

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL, 'right');
?>
