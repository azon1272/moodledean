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
 * Интерфейс управления подписками учебного процесса. Панель управления подписками.
 *
 * @package    im
 * @subpackage cpassed
 * @author     Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

///////////////////////////
// ПОДКЛЮЧЕНИЕ БИБЛИОТЕК //
///////////////////////////

require_once('lib.php');

/////////////////////////////////
// ПОЛУЧЕНИЕ ПЕРЕДАННЫХ ДАННЫХ //
/////////////////////////////////

// Добавление уровня навигации плагина
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'cstreams'),
    $DOF->url_im('cstreams', '/list.php'),
    $addvars
);

// ID учебного процесса
$cstreamid = optional_param('cstreamid', null, PARAM_INT);
$addvars['cstreamid'] = $cstreamid;

// Получение сортировки
$sort  = optional_param('sort', '', PARAM_TEXT);
$dir  = optional_param('dir', 'ASC', PARAM_TEXT);
$sortvars = [];
if ( $sort )
{// Добавление данных сортировки
    $sortvars['sort'] = $sort;
    $sortvars['dir'] = $dir;
    if ( $sortvars['dir'] != 'ASC' )
    {
        $sortvars['dir'] = 'DESC';
    }
}
//////////////////////////////
// ПРОВЕРКА ИСХОДНЫХ ДАННЫХ //
//////////////////////////////

// Проверка учебного процесса
if ( is_null($cstreamid) )
{// Параметр не был передан
    $DOF->messages->add($DOF->get_string('error_cstream_required', 'cpassed'), 'error');
} elseif ( ! $cstream = $DOF->storage('cstreams')->get($cstreamid) )
{// Учебный процесс не существует
    $DOF->messages->add($DOF->get_string('error_cstream_not_exist', 'cpassed'), 'error');
}

/////////////////////////
// ФОРМИРОВАНИЕ ВЫВОДА //
/////////////////////////

$html = $DOF->im('cpassed')->render_cstream_cpasseds_tabs('listeditor', $addvars);

if ( ! $DOF->messages->errors_exists() )
{// Предварительная проверка пройдена без ошибок
    
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('cstream', 'cstreams') . ' [' . $cstream->name . ']',
        $DOF->url_im('cstreams', '/view.php'),
        $addvars
    );
    
    // Генерация формы редактирования
    $form = $DOF->im('cpassed')->form_listeditor($addvars + $sortvars);

    // Пагинация
    $pages = $DOF->modlib('widgets')->pages_navigation('cpassed',
        $form->get_cpasseds_count(), $addvars['limitnum'], $addvars['limitfrom']);
    
    // Добавление уровня навигации плагина
    $DOF->modlib('nvg')->add_level(
        $DOF->get_string('listeditor_cpasseds', 'cpassed'),
        $DOF->url_im('cpassed', '/listeditor.php'),
        $addvars + $sortvars
    );
    
    if ( $form )
    {// Форма получена
        // Обработчик формы
        $form->process();
        // Рендеринг формы
        $html .= $form->render();
        // Пагинация
        $html .= $pages->get_navpages_list('/listeditor.php', $addvars);
        
    } else
    {// Во время генерации формы произошла ошибка
        $DOF->messages->add($DOF->get_string('error_form_generation', 'orders'), 'error');
    }
}


////////////////////
// ВЫВОД СТРАНИЦЫ //
////////////////////

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Вывод контента
echo $html;

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
