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
 * Интерфейс управления подразделениями. Страница редактирования
 * 
 * @package    im
 * @subpackage departments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'departments'),
    $DOF->url_im('departments','/index.php', $addvars)
);

// ID выбранного подразделения
$id = optional_param('id', 0, PARAM_INT);

// Добавление GET-параметров
$addvars['id'] = $id;
if ( empty($id) )
{// Проверка на возможность создания подразделения
    $DOF->storage('departments')->require_access('create');
    // Добавление уровня навигации
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('newdepartment', 'departments'),
        $DOF->url_im('departments','/edit.php', $addvars)
    );
} else 
{// Проверка на возможность редактирования подразделения
    $DOF->storage('departments')->require_access('edit', NULL, NULL, $id);
    // Добавление уровня навигации
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('editdepartment', 'departments'),
        $DOF->url_im('departments','/edit.php', $addvars)
    );
}    

if ( ! empty($id) && ! $DOF->storage('departments')->is_exists($id) )
{// Подразделение указано, но не найдено
    $DOF->messages->add($DOF->get_string('notfound', 'departments'));
}

// Сформируем url формы
$url = $DOF->url_im('departments', '/edit.php', $addvars);
    
// Сформируем дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->departmentid = $addvars['departmentid'];
$customdata->addvars = $addvars;
$customdata->returnurl = $DOF->url_im('departments', '/list.php', $addvars);

// Форма сохранения подразделения
$form = new dof_im_edit($url, $customdata);

// Обработчик формы
$form->process();

// Подгрузка сообщений формы
dof_im_edit::get_form_messages($DOF);

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Печать сообщений
$DOF->messages->display();
// Печать формы
$form->display();

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>