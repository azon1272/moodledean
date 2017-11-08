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
 * Интерфейс управления подразделениями. Страница просмотра списка подразделений.
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

// Получение GET-параметров
// Получение числа записей по-умолчанию
$limitnumdefault = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $limitnumdefault, PARAM_INT);
// Получение смещения
$limitfrom  = optional_param('limitfrom', '1', PARAM_INT);

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'departments'), 
    $DOF->url_im('departments','/index.php',$addvars)
);

// Формирование GET-параметров
$addvars['limitnum'] = $limitnum;
$addvars['limitfrom'] = $limitfrom;

// Определение фильтра по подразделениям
$conds = new stdClass();
// Статус подразделения
$conds->status = NULL;
// Родительское подразделение
$conds->leaddepid = $addvars['departmentid'];
$leaddep = $DOF->storage('departments')->get($conds->leaddepid);
if ( ! empty($leaddep) )
{// Подразделение указано
    // Формирование массива подчиненных подразделений
    $leaddeparray = array_keys($DOF->storage('departments')->departments_list_subordinated($conds->leaddepid));
    // Добавление родителя
    $leaddeparray[] = $conds->leaddepid;
    $conds->leaddepid = $leaddeparray;
}

// Формирование пути до текущего подразделения
$departments = $DOF->storage('departments')->get_departmentstrace($addvars['departmentid']);
if ( ! empty($departments) )
{// Путь до подразделения определен
    $display = false;
    $navvars = $addvars;
    foreach( $departments as $department )
    {// Обработка каждого подразделения
        if ( $DOF->storage('departments')->is_access('view', NULL, NULL, $department->id) )
        {// Есть право на просмотр
            $display = true;
            $navvars['departmentid'] = $department->id;
            $DOF->modlib('nvg')->add_level(
                $department->name,
                $DOF->url_im('departments','/list.php', $navvars)
            );
        } else 
        {// Право на просмотр не дано
            if ( $display )
            {// Требуется отображение для показа актуального пути
                $DOF->modlib('nvg')->add_level(
                    $department->name,
                    NULL
                );
            }
        }
    }
}

// Подгрузка сообщений формы
dof_im_edit::get_form_messages($DOF);

/* ОТОБРАЖЕНИЕ СТРАНИЦЫ */
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$html = '';

// Проверка доступа
$DOF->storage('departments')->require_access('view', NULL, NULL, $addvars['departmentid']);

// Пагинация
$pages = $DOF->modlib('widgets')->pages_navigation('departments', null, $limitnum, $limitfrom);
$list = $DOF->im('departments')->get_listing($conds, $pages->get_current_limitfrom()-1, $pages->get_current_limitnum());
                                                                           
// Получение html-кода таблицы с подразделениями
$departments = $DOF->im('departments')->showlist($list, $addvars);

// Ссылка на создание подразделения
if ( $DOF->storage('departments')->is_access('create') )
{// Право на создание есть
    if ( $DOF->storage('config')->get_limitobject('departments', $addvars['departmentid']) )
    {// Лимит по числу подразделений не превышен
        $link = dof_html_writer::link(
            $DOF->url_im('departments','/edit.php', $addvars), 
            $DOF->get_string('newdepartment', 'departments'),
            ['class' => 'btn btn-primary']
        );
        // Ссылка на создание подраделения
        $html .= html_writer::div($link, 'dof_departments create_department ');
    } else 
    {// Превышение лимита
        $span = dof_html_writer::span(
            $DOF->get_string('newdepartment', 'departments'),
            'btn btn-primary disabled'
        );
        // Добавление уведомления о превышении лимита
        $DOF->messages->add($DOF->get_string('limit_message', 'departments'));
        // Заблокированная ссылка на добавление подразделения
        $html .= html_writer::div($span, 'dof_departments create_department create_department_disabled');
    }  
}
if ( empty($list) )
{// Дочерние подразделения не найдены
    if ( ! empty($leaddepid) )
    {// Дочерние подразделения не найдены
        $DOF->messages->add($DOF->get_string('nonesubordinated', 'departments'));
    } else
    {// Подразделение не найдено
        $DOF->messages->add($DOF->get_string('no_department_found', 'departments'), 'error');
    }
} else
{// Дочерние подразделения найдены  
    if ( ! empty($leaddep) )
    {
        $html .= html_writer::tag('h3', $leaddep->name);
    }
    // Таблица подразделений
    $html .= $departments;
    
    // Пагинация
    $selectlisting = $DOF->im('departments')->get_select_listing($conds);
    $pages->count = $DOF->storage('departments')->count_records_select($selectlisting);
    $html .= $pages->get_navpages_list('/list.php', $addvars);
}
    
// Вывод страницы
// Отображение сообщений
//$DOF->messages->display();
print($html);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>