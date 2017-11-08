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
// Copyright (C) 2011-2999  Evgeniy Yaroslavtsev (Евгений Ярославцев)     //
// Copyright (C) 2011-2999  Evgeniy Gorelov (Евгений Горелов)             //  
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
 * Конфигурационный файл плагина courseenrolment (типа sync) для блока Moodle dof
 * 
 * @package    block
 * @subpackage dof
 * @copyright  2011 Evgeniy Yaroslavtsev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$cenrolcfg = array();

// Включить ли синхронизацию оценок?
// Если false, то cron() будет просто возвращать true, ничего не делая
$cenrolcfg['sync_enabled'] = false;
// TODO Это значение подставляется в методе is_cron, пока отключено т.е.
$cenrolcfg['sync_interval'] = false; // 3600;
// Сколько cstream синхронизировать за раз (за один запуск cron)
$cenrolcfg['sync_cstream_at_time'] = 100;
$cenrolcfg['debug'] = true;

//*****************************************************************************
// Для логов

// срок хранения логов в днях
$cenrolcfg['shelflife_logs'] = 7;
$cenrolcfg['log'] = true;
// Какая пауза (в секундах) допустима при записи логов, чтобы считать что
// конкретный файл логов сейчас используется для записи. Используется при поиске
// файла логов, в который в данный момент происходит запись
$cenrolcfg['just_writed_delay'] = 20;

//*****************************************************************************

// Подправить при установке клиенту - это id преподавателя (того же, что указан
// в im/av/cfg/cfg.php как сотрудник)
// $cenrolcfg['teacher_personid'] = 1731; // сейчас, к счастью, не используется
// id главного подразделения
$cenrolcfg['main_department_id'] = 1;
// Период запуска синхронизации оценок

?>
