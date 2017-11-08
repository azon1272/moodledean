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

$type = required_param('type', PARAM_TEXT);
$typeid = 0;
// Определим, какой тип учебного плана нужно загружать
if ( $type == 'programmsbc' )
{
    $typeid = required_param('programmsbcid', PARAM_INT);
} else if ( $type == 'agroup' )
{
    $typeid = required_param('agroupid', PARAM_INT);
} else
{// Выведем ошибку - некорректный тип учебного плана
    $errorlink = $DOF->url_im('programmsbcs','/list.php', $addvars);
    $DOF->print_error('error_type', $errorlink, $id, 'im', 'learningplan');
}
// GET-параметры для ссылок
$params = array('type' => $type, "{$type}id" => $typeid);

// Проверим, существует ли план уже и выведем ошибку, если он пустой
if ( ! $DOF->storage('learningplan')->is_exists(array('type' => $type, "{$type}id" => $typeid)) )
{
    $errorlink = $DOF->url_im('learningplan','/index.php',$addvars + $params);
    $DOF->print_error('error_empty', $errorlink, false, 'im', 'learningplan');
    exit;
}
// Может ли пользователь создавать учебный план? Потоки? Подписки?
$DOF->storage('learningplan')->require_access('create');
$DOF->storage('cstreams')->require_access('create');
$DOF->storage('cpassed')->require_access('create');
// Проверим, существует ли в плане хоть одна запланированная дисциплина на текущий период
// Достанем текущий семестр
if ( ($agenum = $DOF->storage('learningplan')->get_current_agenum_type_typeid($type, $typeid)) === false )
{
    $errorlink = $DOF->url_im('learningplan','/index.php',$addvars + $params);
    $DOF->print_error('error_emptyagenum', $errorlink, false, 'im', 'learningplan');
    exit;
}
// Cеместр не может быть нулевым
if ( $agenum == 0 )
{
    $errorlink = $DOF->url_im('learningplan','/index.php', $addvars + $params);
    $DOF->print_error('error_zeroagenum', $errorlink, false, 'im', 'learningplan');
    exit;
}
// Проверим, есть ли запланированные дисциплины
$planned = $DOF->storage('learningplan')->get_planned_pitems($type, $typeid, $agenum);
if ( empty($planned) )
{
    $errorlink = $DOF->url_im('learningplan','/index.php',$addvars + $params);
    $DOF->print_error('error_emptyplanned', $errorlink, false, 'im', 'learningplan');
    exit;
}
// Проверим, есть ли запланированные и неизученные дисциплины
// Должна быть хотя бы одна такая, чтобы можно было подписать её
$unsigned = $DOF->storage('learningplan')->get_unsigned_pitems($type, $typeid, $agenum);
if ( empty($unsigned) )
{
    $errorlink = $DOF->url_im('learningplan','/index.php',$addvars + $params);
    $DOF->print_error('error_emptyunsigned', $errorlink, false, 'im', 'learningplan');
    exit;
}

$confirm = optional_param('confirm', 0, PARAM_INT);
//добавление уровней навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title','learningplan'), $DOF->url_im('learningplan','/index.php', $addvars + $params));
$DOF->modlib('nvg')->add_level($DOF->get_string('subscribedisciplines','learningplan'), $DOF->url_im('learningplan','/create.php', $addvars + $params));

// Шапка страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Отображаем основную информацию и дисциплины с потоками, а так же обрабатываем форму там же
$DOF->im('learningplan')->print_process_unsigned_info($type, $typeid);
// Подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>