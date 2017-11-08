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
 * Ведомость оценок по подписке персоны. Базовые функции сабинтерфейса.
 * 
 * @package    im
 * @subpackage journal
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// ОБЩИЕ GET-ПАРАМЕТРЫ ИНТЕРФЕЙСА
// Подписка пользователя
$personbc = optional_param('personbcid', NULL, PARAM_INT);
// Академическая группа
$agroupid = optional_param('agroupid', NULL, PARAM_INT);

// Добавление таблицы стилей
$DOF->modlib('nvg')->add_css('im', 'journal', '/personsbc_gradeslist/style.css');

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('pbcgl_programmbc_header', 'journal'),
    $DOF->url_im('journal','/personsbc_gradeslist/index.php', $addvars)
);

// ОБЩИЕ ОБРАБОТЧИКИ ИНТЕРФЕЙСА

// Проверка на доступность подписки
if ( is_int($personbc) )
{// Подписка указана
    if ( $personbc < 1 )
    {// Неверный ID подписки
        // Добавить сообщение
        $DOF->messages->add($DOF->get_string('pbcgl_error_programmbc_not_set', 'journal'), 'error');
        // Сброс идентификатора подписки
        $personbc = NULL;
    } else
    {// Подписка указана
        // Получить подписку
        $programbc = $DOF->storage('programmsbcs')->get($personbc);
        if ( empty($programbc) )
        {// Подписка не найдена
            // Добавить сообщение
            $DOF->messages->add($DOF->get_string('pbcgl_error_programmbc_not_found', 'journal'), 'error');
            // Сброс идентификатора подписки
            $personbc = NULL;
        } else
        {// Подписка найдена
            // Проверка доступа на использование подписки
            $has_access = $DOF->storage('programmsbcs')->is_access('use', $personbc);
            if ( empty($has_access) )
            {// Нет доступа
                $DOF->messages->add($DOF->get_string('pbcgl_error_programmbc_access', 'journal'), 'error');
                // Сброс идентификатора подписки
                $personbc = NULL;
            } else
            {// Доступ на работу с подпиской есть
                // Добавление в общие GET параметры
                $addvars['personbcid'] = $personbc;
            }
        }
    }
}
// Проверка на доступность академической группы
if ( is_int($agroupid) )
{// Группа указана
    if ( $agroupid < 1 )
    {// Неверный ID группы
        // Добавить сообщение
        $DOF->messages->add($DOF->get_string('pbcgl_error_agroup_not_set', 'journal'), 'error');
        // Сброс идентификатора группы
        $agroupid = NULL;
    } else
    {// Группа указана
        // Получить группу
        $agroup = $DOF->storage('agroups')->get($agroupid);
        if ( empty($agroup) )
        {// Группа не найдена
            // Добавить сообщение
            $DOF->messages->add($DOF->get_string('pbcgl_error_agroup_not_found', 'journal'), 'error');
            // Сброс идентификатора группы
            $agroupid = NULL;
        } else
        {// Группа найдена
            // Проверка доступа на использование группы
            $has_access = $DOF->storage('agroups')->is_access('use', $agroupid);
            if ( empty($has_access) )
            {// Нет доступа
                $DOF->messages->add($DOF->get_string('pbcgl_error_agroup_access', 'journal'), 'error');
                // Сброс идентификатора подписки
                $agroupid = NULL;
            } else
            {// Доступ на работу с подпиской есть
                // Добавление в общие GET параметры
                $addvars['agroupid'] = $agroupid;
            }
        }
    }
}

if ( $DOF->messages->errors_exists() && is_null($personbc) && is_null($agroupid) )
{// Найдены ошибки, при этом не возможно продолжить работу ни по одному из вариантов
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Отображение сообщений
    $DOF->messages->display();
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

?>