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
 * Ведомость оценок по подписке персоны. Точка входа в сабинтерфейс.
 * 
 * @package    im
 * @subpackage journal
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');
require_once('form.php');

$html = '';

// Получение GET-параметров
// Временной интервал
$timestart = optional_param('timestart', NULL, PARAM_INT);
$timeend = optional_param('timeend', NULL, PARAM_INT);
$view_type = optional_param('viewtype', '00', PARAM_ALPHANUM);
// Получение числа записей по-умолчанию
$limitnumdefault = (int)$DOF->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
$limitnum = optional_param('limitnum', $limitnumdefault, PARAM_INT);
// Получение смещения
$limitfrom  = optional_param('limitfrom', '1', PARAM_INT);
// Нормализация
if ( $limitnum < 1 )
{
    $limitnum = $limitnumdefault;
}
if ( $limitfrom < 1 )
{
    $limitfrom = 1;
}
if ( ! isset($view_type{0}) )
{
    $view_type{0} = '0';
}
if ( ! isset($view_type{1}) )
{
    $view_type{1} = '0';
}


// Формирование GET-параметров
if ( is_int($timestart) )
{// Указан начальный интервал
    $addvars['timestart'] = $timestart;
}
if ( is_int($timeend) )
{// Указан конечный интервал
    $addvars['timeend'] = $timeend;
}
// Тип отображения
$addvars['viewtype'] = $view_type;

// Формирование URL формы
$url = $DOF->url_im('journal','/personsbc_gradeslist/index.php', $addvars);

// Формирование дополнительных данных
$customdata = new stdClass;
$customdata->dof = $DOF;
$customdata->addvars = $addvars;

// Форма сохранения подразделения
$form = new dof_im_journal_pbcgl_sourceselect($url, $customdata);

// Обработчик формы
$form->process();

if ( ! is_null($personbc) || ! is_null($agroupid) )
{// Указаны данные для формирования таблицы

    // Получение доступных подписок на программы
    $programmbcs = $DOF->im('journal')->get_available_programmbcs($addvars);
    
    // Получение массива доступных подписок на предмето-классы
    $cpasseds = $DOF->im('journal')->get_cpasseds_by_programmbcs($programmbcs, $addvars);

    // Формирование ведомости
    $options = [];
    $options['addvars'] = $addvars;
    $options['timestart'] = $timestart;
    $options['timeend'] = $timeend;
    $options['view_type'] = $view_type;
    $options['export'] = 'CSV';
    
    $exportdata = $DOF->im('journal')->personbc_gradelist_table($cpasseds, $options);

    $filename = 'gradeslist('.dof_userdate($timestart,'%Y-%m-%d').'-'
        .dof_userdate($timeend,'%Y-%m-%d').')('
            .dof_userdate(time(),'%Y-%m-%d').')('.$addvars['departmentid'].').csv';
    
    // Прописываем заголовки для скачивания файла
    header('Content-Description: File Transfer');
    header("Content-Type: application/octet-stream");
    header('Content-disposition: extension-token; filename=' . $filename);
    print($exportdata);
} else 
{// Данные не получены
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Отображение сообщений
    $DOF->messages->display();
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}
?>