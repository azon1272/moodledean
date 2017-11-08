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
 * Интерфейс управления участниками учебного процесса. Панель управления слушателями.
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

// Получение GET-параметров
// Получение смещения
$limitfrom  = optional_param('limitfrom', '1', PARAM_INT);
// Отобразить результаты фильтрации
$showresult = optional_param('filter', false, PARAM_BOOL);
// Получение сортировки
$sort  = optional_param('sort', '', PARAM_TEXT);
$dir  = optional_param('dir', 'ASC', PARAM_TEXT);

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('page_students_name', 'participants'),
    $DOF->url_im('participants', '/students.php'),
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

// Проверка на наличие ошибок
if ( $DOF->messages->errors_exists() )
{
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Формирование GET-параметров, которые не входят в навигацию верхнего уровня
$addvars['limitfrom'] = $limitfrom;

// Сформировать URL формы фильтрации подписок
$url = $DOF->url_im('participants', '/students.php', $addvars);
// Сформировать URL возврата после обработки формы
$returnurl = $DOF->url_im('participants', '/students.php', $addvars + ['filter' => 1]);
// Сформировать дополнительные данные
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $addvars;
$customdata->returnurl = $returnurl;
// Форма фильтрации пользователей
$form = new dof_im_participants_students_filter($url, $customdata, 'post', '', ['class' => 'dof_im_participants_students_filter']);
// Обработчик формы и получение фильтра
$filter = $form->process();

// Печать вкладок
$html .= $DOF->im('participants')->render_tabs('students', $addvars);

$cancreate = $DOF->storage('programmsbcs')->is_access('create');
// Отображение интерфейса импорта подписок
if ( $cancreate )
{
    $somevars = [];
    $somevars['departmentid'] = $addvars['departmentid'];
    $url = $DOF->url_im('participants', '/students_import.php', $somevars);
    $html .= dof_html_writer::link(
        $url,
        $DOF->get_string('students_import_button', 'participants'),
        ['class' => 'btn btn-primary button dof_button dof_button_right']
    );
}
/*
$canview = $DOF->storage('programmsbcs')->is_access('view');
// Отображение интерфейса экспорта подписок
if ( $canview )
{
    $somevars = [];
    $somevars['departmentid'] = $addvars['departmentid'];
    $url = $DOF->url_im('participants', '/students_export.php', $somevars);
    $html .= dof_html_writer::link(
        $url,
        $DOF->get_string('students_export_button', 'participants'),
        ['class' => 'btn btn-primary button dof_button dof_button_right']
    );
}*/

if ( $showresult )
{// Отображение результатов поиска подписок
    
    // Получение GET-параметров фильтра для формирования ссылок
    $filteraddvars = $form->get_addvars();
    
    // Добавление сортировки
    $addvars['sort'] = $sort;
    $addvars['dir'] = $dir;
    if ( $addvars['dir'] != 'ASC' )
    {
        $addvars['dir'] = 'DESC';
    }
    
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('page_filter_result_name', 'participants'),
        $DOF->url_im('participants', '/students.php'),
        $addvars + $filteraddvars + ['filter' => $showresult]
    );

    // Получить ID подписок с учетом фильтрации по текущему подразделению
    $programmsbcs = $DOF->im('participants')->
        get_programmsbcs_id_by_filter($filter, $addvars['departmentid'], ['sort' => $addvars['sort'], 'dir' => $addvars['dir']]);
    $count = count($programmsbcs);
    
    // БЛОК СОЗДАНИЯ ПОДПИСКИ
    // Получение условий для отображения блока создания подписок
    $has_userfilter = (
        (isset($filter['user']->lastname) && isset($filter['user']->firstname)) ||
         isset($filter['user']->email)
    );
    
    if ( $has_userfilter && $cancreate )
    {// Требуется отобразить блок быстрого создания подписки

        // Быстрое создание подписки
        $url = $DOF->url_im('participants', '/students.php', $addvars + $filteraddvars + ['filter' => $showresult]);
        $customdata = new stdClass;
        $customdata->dof = $DOF;
        $customdata->addvars = $addvars + $filteraddvars + ['filter' => $showresult];
        $customdata->createdata = $filter;
        $fcreateform = new dof_im_participants_students_fastcreate($url, $customdata);
        if ( $fcreateform->process() === true )
        {// Подписка создана
            // Обновление страницы с выводом сообщения
            $successurl = $DOF->url_im('participants', '/students.php', $addvars + $filteraddvars + ['filter' => $showresult, 'bccreatesussess' => '1']);
            redirect($successurl);
        }
        if ( $fcreateform->canconfirm )
        {// Форма имеет достаточно данных для создания подписки
            
            // Отобразить форму содания подписки в модальном окне
            $label = dof_html_writer::span(
                $DOF->get_string('students_create_bc_fast_button', 'participants'),
                'btn btn-primary button dof_button'
            );
            $content = $fcreateform->render();
            $title = $DOF->get_string('students_create_bc_fast_title', 'participants');
            $html .= $DOF->modlib('widgets')->modal($label, $content, $title, ['show' => $fcreateform->hasmessages]);
        }
    }
    
    if ( $cancreate )
    {// Требуется отобразить блок детального создания подписки
        // Детальное создание подписки
        $somevars = [];
        $somevars['departmentid'] = $addvars['departmentid'];
        $somevars['returnurl'] = $DOF->url_im('participants', '/students.php', $addvars + $filteraddvars + ['filter' => $showresult]);
        $url = $DOF->url_im('participants', '/create_programmsbc.php', $somevars);
        $html .= dof_html_writer::link(
            $url,
            $DOF->get_string('students_create_bc_button', 'participants'),
            ['class' => 'btn btn-primary button dof_button']
        );
    }
    
    // Рендер формы
    $html .= $form->render();
    
    // ТАБЛИЦА КОЛЛИЗИЙ
    // Получение условий для отображения коллизий
    $has_userfilter = ( 
        (isset($filter['user']->lastname) && isset($filter['user']->firstname)) || 
        isset($filter['user']->email) || 
        isset($filter['user']->phone) 
    );
    $has_programmfilter = isset($filter['programm']->name) || isset($filter['agroup']->name);
    if ( $has_userfilter && $has_programmfilter )
    {// Требуется найти подписки в остальных подразделениях
        // Получить подписки, созданные не в текущем подразделении
        $allprogrammsbcs = $DOF->im('participants')->
            get_programmsbcs_id_by_filter($filter, 0, ['sort' => $addvars['sort'], 'dir' => $addvars['dir']]);
        $collisions = array_diff($allprogrammsbcs, $programmsbcs);
        if ( ! empty($collisions) )
        {// Подписки не в текущем подразделении найдены
            
            // Уведомление пользователя
            $DOF->messages->add(
                $DOF->get_string('notice_page_filter_has_collisions', 'participants'), 
                'notice'
            );
            
            // Получить таблицу подписок
            $options = [
                'limitfrom' => 0,
                'limitnum' => 0,
                'addvars' => $addvars,
                'disablesort' => true
            ];
            $html .= dof_html_writer::tag('h4', $DOF->get_string('table_programmsbcs_collisions_title', 'participants'));
            $html .= $DOF->im('participants')->get_programmsbcs_table($collisions, $options);
        }
    }
    
    // ТАБЛИЦА ПОДПИСОК
    if ( ! empty($programmsbcs) )
    {// Подписки найдены
        
        // Пагинация
        $pagination = $DOF->modlib('widgets')->pages_navigation('participants', $count, $addvars['limitnum'], $addvars['limitfrom']);
        
        // Получить таблицу подписок
        $html .= dof_html_writer::tag('h4', $DOF->get_string('table_programmsbcs_title', 'participants'));
        $html .= $pagination->get_navpages_list('/students.php', $addvars + $filteraddvars + ['filter' => $showresult]);
        $options = [
            'limitfrom' => $addvars['limitfrom'], 
            'limitnum' => $addvars['limitnum'],
            'addvars' => $addvars
        ];
        $html .= $DOF->im('participants')->get_programmsbcs_table($programmsbcs, $options);
        $html .= $pagination->get_navpages_list('/students.php', $addvars + $filteraddvars + ['filter' => $showresult]);
    } else 
    {// Подписки не найдены
        // Уведомление пустом результате
        $DOF->messages->add(
            $DOF->get_string('notice_page_filter_empty_result', 'participants'), 
            'notice'
        );
    }
} else 
{// Базовый вид страницы

    if ( $cancreate )
    {// Требуется отобразить блок детального создания подписки
        // Детальное создание подписки
        $somevars = [];
        $somevars['departmentid'] = $addvars['departmentid'];
        $somevars['returnurl'] = $DOF->url_im('participants', '/students.php', $addvars);
        $url = $DOF->url_im('participants', '/create_programmsbc.php', $somevars);
        $html .= dof_html_writer::link(
            $url,
            $DOF->get_string('students_create_bc_button', 'participants'),
            ['class' => 'btn btn-primary button dof_button']
        );
    }
    // Рендер формы
    $html .= $form->render();
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print($html);

// Печать подвала страницы
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>