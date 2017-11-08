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
 * Точка входа в плагин
 *
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Настройка доступности регистрации в текущем подразделении
$registration_enabled = $DOF->storage('config')->
    get_config_value('registration_enabled', 'im', 'partners', $addvars['departmentid']);
if ( $registration_enabled )
{// Регистрация включена
    // Добавление ссылки на страницу регистрации
    $link = dof_html_writer::link(
            $DOF->url_im('partners', '/registration.php', $addvars),
            $DOF->get_string('registration_title', 'partners')
    );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('partners')->is_access('admnistration') )
{// Доступ к панели управления партнерской сетью
    // Добавление ссылки к панели управления партнерской сетью
    $link = dof_html_writer::link(
            $DOF->url_im('partners', '/admin_panel.php', $addvars),
            $DOF->get_string('admin_panel_title', 'partners')
    );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('partners')->is_access('report') )
{// Доступ к панели просмотра отчетов
    // Добавление ссылки на панель просмотра отчетов
    $link = dof_html_writer::link(
            $DOF->url_im('partners', '/report_panel.php', $addvars),
            $DOF->get_string('report_panel_title', 'partners')
    );
    echo dof_html_writer::tag('h4', $link);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>