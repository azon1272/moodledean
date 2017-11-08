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
 * Панель управления отчетами
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

if ( $DOF->im('partners')->is_access('report') )
{// Регистрация включена
    // Добавление ссылки на страницу регистрации
    $addvars['type'] = 'admins';
    $link = dof_html_writer::link(
            $DOF->url_im('partners', '/reports/index.php', $addvars),
            $DOF->get_string('report_panel_admins_title', 'partners')
    );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('partners')->is_access('report') )
{// Доступ к панели управления партнерской сетью
    // Добавление ссылки к панели управления партнерской сетью
    $addvars['type'] = 'teachers';
    $link = dof_html_writer::link(
            $DOF->url_im('partners', '/reports/index.php', $addvars),
            $DOF->get_string('report_panel_teachers_title', 'partners')
    );
    echo dof_html_writer::tag('h4', $link);
}

if ( $DOF->im('partners')->is_access('report') )
{// Доступ к панели просмотра отчетов
    // Добавление ссылки на панель просмотра отчетов
    $addvars['type'] = 'students';
    $link = dof_html_writer::link(
            $DOF->url_im('partners', '/reports/index.php', $addvars),
            $DOF->get_string('report_panel_students_title', 'partners')
    );
    echo dof_html_writer::tag('h4', $link);
}

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>