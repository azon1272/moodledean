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
 * Журнал предмето-класса. Точка входа в сабинтерфейс.
 *
 * @package    im
 * @subpackage journal
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотек
require_once('lib.php');

// Получение GET-параметров
// ID предмето-класса
$csid = required_param('csid', PARAM_INT);
// ID учебного события или плана, оценки которго редактируются
$dateid  = optional_param('planid', 0, PARAM_INT);
$eventid = optional_param('eventid', 0, PARAM_INT);

// Подключение библиотек
$DOF->modlib('widgets')->js_init('show_hide');
// Добавление таблицы стилей
$DOF->modlib('nvg')->add_css('im', 'journal', '/styles.css');

// Проверка параметров
if ( ! $cstream = $DOF->storage('cstreams')->get($csid) )
{// Предмето-класс не найден
	$DOF->messages->add(
	    $DOF->get_string('error_grpjournal_cstream_not_found', 'journal', $csid), 
	    'error'
	);
}

// Проверка прав доступа
if ( ! $DOF->im('journal')->is_access('view_journal/own', $csid) &&
       $DOF->im('journal')->require_access('view_journal', $csid) )
{// Нет прав для просмотра журнала
    $DOF->messages->add(
        $DOF->get_string('error_grpjournal_view_access_denied', 'journal'),
        'error'
    );
}

if ( $DOF->messages->errors_exists() )
{// Имеются ошибки
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Печать подвала страницы
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    die;
}

// Получение класса формирования таблицы оценок
$grades_table = new dof_im_journal_tablegrades($DOF, $csid, $addvars);
// Получение класса формирования таблицы тематических планов
$themeplans_table = new dof_im_journal_templans_table($DOF, $csid);

// ДЕЙСТВИЯ В ЖУРНАЛЕ
$links = '';

// Ссылка на создание нового урока
if ( ($DOF->storage('schevents')->is_access('create')) || 
     ($DOF->storage('schevents')->is_access('create/in_own_journal', $csid)) ) 
{// Прав достаточно
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_newlesson');
    $somevars = $addvars + ['planid' => 0, 'eventid' => 0, 'csid' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('journal','/group_journal/topic.php', $somevars), 
        $DOF->get_string('new_lesson', 'journal'),
        ['class' => 'btn button dof_button']
    );
    $links .= dof_html_writer::end_div();
}

// Ссылка на получение итоговой ведомости
if (  //  право завершать cstream до истечения срока cstream
      (($DOF->im('journal')->is_access('complete_cstream_before_enddate',$csid) AND $cstream->enddate > time()) OR
      // право завершать cstream после истечения срока cstream (пересдача)
      ($DOF->im('journal')->is_access('complete_cstream_after_enddate', $csid) AND $cstream->enddate < time()) OR
      // право  Закрывать итоговую ведомость до завершения cstream 
      // (под завершением имеется в виду cstream в конечном статусе)
      ($DOF->im('journal')->is_access('close_journal_before_closing_cstream', $csid) AND $cstream->status != 'completed' ) OR
      // право Закрывать итоговую ведомость до истечения даты cstream
      ($DOF->im('journal')->is_access('close_journal_before_cstream_enddate', $csid) AND $cstream->enddate > time() ) OR
      //  право Закрывать итоговую ведомость после истечения даты cstream, но до завершения cstream
      ($DOF->im('journal')->is_access('close_journal_after_active_cstream_enddate', $csid) 
          AND $cstream->status != 'completed' AND time() > $cstream->enddate )) 
      AND 
      ( $DOF->storage('cpassed')->is_access('edit:grade/own',$csid) OR 
              $DOF->storage('cpassed')->is_access('edit:grade/auto',$csid) OR 
        $DOF->storage('cpassed')->is_access('edit:grade',$csid) )
   )
{// Прав достаточно
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_finalgrades');
    $somevars = $addvars + ['id' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('journal','/itog_grades/edit.php', $somevars),
        $DOF->get_string('itog_grades', 'journal'),
        ['class' => 'btn button dof_button']
    );
    $links .= dof_html_writer::end_div();
}

// Ссылки на работу с тематическим планированием занятий
if ( $DOF->im('plans')->is_access('viewthemeplan', $csid) || 
     $DOF->im('plans')->is_access('viewthemeplan/my', $csid) )
{// Прав достаточно 
    // Ссылка на просмотр фактического планирования
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_plancstream');
    $somevars = $addvars + ['linktype' => 'cstreams', 'linkid' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('plans','/themeplan/viewthemeplan.php', $somevars),
        $DOF->get_string('view_plancstream', 'journal'),
        ['class' => 'btn button dof_button']
    );
    $links .= dof_html_writer::end_div();
    // Ссылка на просмотр учебного тематического планирования
    $links .= dof_html_writer::start_div('dof_group_journal_action dof_group_journal_action_iutp');
    $somevars = $addvars + ['linktype' => 'plan', 'linkid' => $csid];
    $links .= dof_html_writer::link(
        $DOF->url_im('plans','/themeplan/viewthemeplan.php', $somevars),
        $DOF->get_string('view_iutp', 'journal'),
        ['class' => 'btn button dof_button']
    );
    $links .= dof_html_writer::end_div();
}

// Формирование базового блока информации о журнале потока
$table = new stdClass();
$table->cellpadding = 0;
$table->cellspacing = 0;
$table->size = ['50%', '50%'];
$table->align = ['center', 'left'];
$table->class = 'dof_group_journal_baseinfo';
// Шапка
$table->head = [];
// Данные таблицы
$table->data = [[
    $DOF->im('journal')->get_cstream_info($csid),
    $links
]];

// Формирование блока разворота журнала
$stable = new stdClass();
$stable->cellpadding = 0;
$stable->cellspacing = 0;
$stable->size = ['50%', '50%'];
$stable->align = ['left', 'left'];
$stable->class = 'dof_group_journal_journallist';
// Таблица оценок учеников
$gradeslist = dof_html_writer::start_div('dof_group_journal_journallist_gradelist');
$gradeslist .= $grades_table->print_texttable($dateid, $eventid, true);
$gradeslist .= dof_html_writer::end_div();
// Таблица тематического плана
$themesplan = dof_html_writer::start_div('dof_group_journal_journallist_themesplan');
$themesplan .= $themeplans_table->print_table();
$themesplan .= dof_html_writer::end_div();
// Шапка
$stable->head = [];
// Данные таблицы
$stable->data = [[$gradeslist, $themesplan]];


// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Вывод таблицы
$DOF->modlib('widgets')->print_table($table);
// Вывод таблицы
$DOF->modlib('widgets')->print_table($stable);
// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>