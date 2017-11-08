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
 * Интерфейс управления участниками учебного процесса. Импорт студентов
 *
 * @package    im
 * @subpackage participants
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

// HTML-код старинцы
$html = '';

// Отображение сообщений на основе GET-параметров
$DOF->im('participants')->messages();

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_students_name', 'participants'),
    $DOF->url_im('participants', '/students.php'),
    $addvars
);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_students_import_name', 'participants'),
    $DOF->url_im('participants', '/students_import.php'),
    $addvars
);

// Проверка базового права доступа к интерфейсу
if ( ! $DOF->im('participants')->is_access('interface_students') )
{
    $DOF->messages->add(
        $DOF->get_string('error_interface_students_access_denied', 'participants'),
        'error'
    );
}

// Печать вкладок
$html .= $DOF->im('participants')->render_tabs('students', $addvars);

// Проверка доступа
$cancreate = $DOF->storage('programmsbcs')->is_access('create');
if ( ! $cancreate )
{// Возможность создавать подписки 
    $DOF->messages->add(
        $DOF->get_string('students_import_error_access_denied', 'participants'),
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

// Сформировать URL формы импорта
$url = $DOF->url_im('participants', '/students_import.php', $addvars);
// Сформировать URL возврата после обработки формы
$returnurl = $DOF->url_im('participants', '/students_import.php', $addvars);
// Сформировать дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $addvars;
$customdata->returnurl = $returnurl;
// Форма импорта студентов и подписок
$form = new dof_im_participants_students_import($url, $customdata, 'post', '', ['class' => 'dof_im_participants_students_import']);
// Обработчик формы импорта
$filter = $form->process();

// Рендер формы
$html .= $form->render();

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>