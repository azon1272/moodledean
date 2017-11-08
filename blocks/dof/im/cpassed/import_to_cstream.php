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
 * Страница редактирования приказа.
 *
 * @package im
 * @subpackage cpassed
 * @author Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright 2016
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

///////////////////////////
// ПОДКЛЮЧЕНИЕ БИБЛИОТЕК //
///////////////////////////

require_once('lib.php');


/////////////////////////////////
// ПОЛУЧЕНИЕ ПЕРЕДАННЫХ ДАННЫХ //
/////////////////////////////////

// id процесса
$cstreamid = optional_param('cstreamid', null, PARAM_INT);
$addvars['cstreamid'] = $cstreamid;

//////////////////////////////
// ПРОВЕРКА ИСХОДНЫХ ДАННЫХ //
//////////////////////////////

if ( is_null($cstreamid) )
{//параметр не был передан
    $DOF->messages->add($DOF->get_string('error_cstream_required', 'orders'), 'error');
}
if ( ! $cstream = $DOF->storage('cstreams')->get($cstreamid) )
{//процесс не существует
    $DOF->messages->add($DOF->get_string('error_cstream_not_exist', 'orders'), 'error');
}


/////////////////////////
// ФОРМИРОВАНИЕ ВЫВОДА //
/////////////////////////

$html = $DOF->im('cpassed')->render_cstream_cpasseds_tabs('import_to_cstream', $addvars);

if ( ! $DOF->messages->errors_exists() )
{// Предварительная проверка пройдена без ошибок - можно наполнять контентом
    
    // Генерация формы
    $form = $DOF->im('cpassed')->form_import_to_cstream($addvars);

    // Пагинация
//     $pages = $DOF->modlib('widgets')->pages_navigation('cpassed',
//         $form->get_cpasseds_count(), $addvars['limitnum'], $addvars['limitfrom']);
    
    if ( $form )
    {// Форма получена
        // Обработчик формы
        $form->process();
        //отрисовка формы в переменную
        $html .= $form->render();
        //постраничная навигация
//         $html .= $pages->get_navpages_list('/listeditor.php', $addvars);
        
    } else
    {// Во время генерации формы произошла ошибка
        $DOF->messages->add($DOF->get_string('error_form_generation', 'orders'), 'error');
    }
}


////////////////////
// ВЫВОД СТРАНИЦЫ //
////////////////////

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'),
    $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('cstream', 'cstreams') . ' [' . $cstream->name . ']',
    $DOF->url_im('cstreams','/view.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('import_cpasseds_to_cstream', 'cpassed'),
    $DOF->url_im('cpassed','/import_to_cstream.php'),$addvars);
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Вывод контента
echo $html;
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
