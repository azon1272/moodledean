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
 * Интерфейс учебных периодов. Задача по активации учебных процессов периода.
 *
 * @package    im
 * @subpackage ages
 * @author     Ivanov Dmitry <ivanov@opentechnology.ru>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// HTML-код старинцы
$html = '';

// Получение GET-параметров
// ID учебного периода
$ageid = required_param('ageid', PARAM_INT);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'ages'), 
    $DOF->url_im('ages', '/list.php'), 
    $addvars
);

// Проверка базового условия доступа
if ( ! $DOF->storage('ages')->is_exists($ageid) )
{// Учебный период не найден
    $DOF->messages->add(
        $DOF->get_string('err_age_not_exists', 'ages'), 
        'error'
    );
}

// Добавление уровня навигации плагина
$addvars['ageid'] = $ageid;
$DOF->modlib('nvg')->add_level(
    $DOF->storage('ages')->get_field($ageid, 'name'),
    $DOF->url_im('ages','/view.php'),
    $addvars
);

// Проверка базового права доступа к интерфейсу
if( ! $DOF->storage('ages')->is_access('view', $ageid, null, $addvars['departmentid']) )
{// Доступ запрещен
    $DOF->messages->add(
        $DOF->get_string('access_denied'),
        'error'
    );
}

// Проверка на наличие ошибок
if ( $DOF->messages->errors_exists() )
{
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Сформировать URL формы фильтрации подписок
$url = $DOF->url_im('ages', '/activate_cstreams.php', $addvars);
// Сформировать дополнительные данные
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $ageid;
$customdata->addvars = $addvars;
// Форма задачи по смене статусов учебных процессов
$taskform = new dof_im_ages_activate_cstreams($url, $customdata, 'post', '', ['class' => 'dof_im_ages_activate_cstreams']);

// Обработчик формы и получение фильтра
$taskform->process();

// Рендер формы
$html .= $taskform->render();

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>