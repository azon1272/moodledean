<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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
 * Журнал предмето-класса. Базовые функции сабинтерфейса.
 * 
 * @package    im
 * @subpackage journal
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

$csid = optional_param('csid', 0, PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('group_journal', 'journal'), $DOF->url_im('journal', '/group_journal/index.php?csid=' . $csid, $addvars));

/** 
 * Класс отображения таблицы оценок журнала учебного процесса
 */
class dof_im_journal_tablegrades extends dof_im_journal_rawdata
{
    /**
     * Массив GET параметров для формирования ссылок
     * 
     * @var array
     */
    public $addvars = [];
    
    /**
     * Конструктор - определяет с каким учебным потоком будет вестись работа
     *
     * @param dof_control - глобальный объект Деканата $DOF
     * @param int $csid - ID учебного процесса(предмето-класса)
     * @param array $addvars - Массив GET параметров
     */
    
    function __construct(dof_control $dof, $csid, $addvars = [])
    {
        $this->dof  = $dof;
        $this->csid = (int)$csid;
        $this->addvars = (array)$addvars;
    }
    
    /** 
     * Отобразить таблицу оценок журнала учебного процесса для всех учащихся
     * 
     * @param int $planid - ID контрольной точки в учебном плане данного предмета-класса
     * @param int $eventid - ID учебного события данного предмета-класса
     * @param bool $returnhtml - Вернуть HTML вместо печати таблицы
     * 
     * @return string|void - Печать таблицы оценок или возврат HTML-кода
     */
    public function print_texttable($planid = NULL, $eventid = 0, $returnhtml = FALSE)
    {
        // Получение данных для шаблонизатора
        $docdata = $this->get_all_form($planid, $eventid);
        
        // Загрузка данных в шаблонизатор
        $templater_package = $this->dof->modlib('templater')->template('im', 'journal', $docdata, 'group_journal');
        
        // Добавить JS поддержки группового журнала
        $this->dof->modlib('nvg')->add_js('im', 'journal', '/group_journal/groupjournal.js', false);
        
        // Формирование HTML-шаблона
        $html = $templater_package->get_file('html');
        
        if ( $returnhtml )
        {// Вернуть HTML
            return $html;
        } else 
        {// Печать таблицы
            print($html);
        }
    }

    /**
     * Отобразить таблицу оценок журнала учебного процесса для одной подписки на программу
     * 
     * 
     * @param int $programmsbcid - ID подписки на программу
     * @param bool $returnhtml - Вернуть HTML вместо печати таблицы
     * 
     * @return string|void - Печать таблицы оценок или возврат HTML-кода
     */
    public function get_grades_programmsbcid($programmsbcid, $returnhtml = FALSE)
    {
        global $addvars;
        
        $html = '';
        
        // Получение всех подписок на текущий учебный процесс по подписке на программу
        $cpasseds = $this->get_cpassed_programmsbc($programmsbcid, true);
        if ( ! empty($cpasseds) )
        {// Найдены подписки на учебные процессы
            
            // Получить контрольные точки тематического плана текущего учебного процесса
            $plans = $this->get_checkpoints(false);
            if ( $plans )
            {// В результирующем массиве формируем строку месяцев и дат
                $datesstring = $this->create_datesstring($plans);
                if ( !empty($datesstring) )
                {
                    // Сделаем из timestamp дни, чтобы отобразить их под месяцами
                    foreach ( $datesstring->monthdate as $id => $mdate )
                    {
                        $datesstring->monthdate[$id]->date = dof_userdate($mdate->date, '%d');
                    }
                }
            }
            
            $i = 1;
            $grades = [];
            // Формирование данных по каждой подписке на учебный процесс
            foreach ( $cpasseds as $cpassedid => $cpassed )
            {
                $grades[$cpassedid]       = $this->get_line_for_student($i++, $cpassed, $plans, null, 0, 'grades');
                $begindate                = dof_userdate($cpassed->begindate, '%d.%m.%Y');
                $enddate                  = dof_userdate($cpassed->enddate, '%d.%m.%Y');
                $grades[$cpassedid]->cpdate = $begindate . ' - ' . $enddate;
                $cpstatus = $this->dof->workflow('cpassed')->get_name($cpassed->status);
                $cpassedlink = $this->dof->im('obj')->get_object_url_current('cpassed', $cpassedid, 'view', $addvars, $cpstatus);
                $grades[$cpassedid]->cpassedlink = $cpassedlink;
                if ( ! empty($datesstring) )
                {
                    $grades[$cpassedid]->monthdesc  = $this->dof->get_string('month', 'journal');
                    $grades[$cpassedid]->daydesc    = $this->dof->get_string('date_day', 'journal');
                    $grades[$cpassedid]->monthdate  = $datesstring->monthdate;
                    $grades[$cpassedid]->monthtitle = $datesstring->monthtitle;
                }
            }
            $grobject = new stdClass();
            $grobject->cpasseds = $grades;
            
            // Загрузка данных в шаблонизатор
            $templater_package = $this->dof->modlib('templater')->template('im', 'journal', $grobject, 'cphistory');

            // Формирование HTML-шаблона
            $html = $templater_package->get_file('html');
        }
        
        if ( $returnhtml )
        {// Вернуть HTML
            return $html;
        } else
        {// Печать таблицы
            print($html);
        }
    }

    /** 
     * Получить список статусов с которыми будут извлекаться события из таблицы schevents
     * 
     * @return array|NULL - Массив статусов или NULL, если не требуется фильтрация по статусам
     */
    protected function get_eventstatuses()
    {
        return array('plan', 'completed', 'postponed');
    }

    /** 
     * Возвращает объект формы для вставки в templater
     * 
     * @param int $editid - ID контрольной точки в учебном плане данного предмета-класса
     * @param int $eventid - ID учебного события данного предмета-класса
     * 
     * @return object - Объект нужной структуры для построения шаблона
     */
    private function get_all_form($editid, $eventid)
    {
        $result              = new stdClass();
        $result->monthdesc   = $this->dof->get_string('month', 'journal');
        $result->datedesc    = $this->dof->get_string('date_day', 'journal');
        $result->npp         = $this->dof->get_string('npp', 'journal');
        $result->listtitle   = $this->dof->get_string('students_list2', 'journal');
        
        // СБОРКА ИСХОДНЫХ ДАННЫХ
        // Получить все запланированные и активные контрольные точки учебного процесса
        $plans = $this->get_checkpoints(false);
        
        // Массив для названий месяцев
        $result->monthtitle  = [];
        // Массив для дат
        $result->monthdate   = [];
        // Добавление информации по ученикам
        $result->studentinfo = $this->get_lines_for_students($plans, $editid, $eventid, $info = 'info');
        // Добавление ФИО учеников отдельным полем
        $result->student = $this->get_lines_for_students($plans, $editid, $eventid, $info = 'grades');

        if ( $plans )
        {// В результирующем массиве формируются строки месяцев и дат
            $datesstring = $this->create_datesstring($plans);
            $result->upper_anchor = $datesstring->upper_anchor;
            $result->monthdate    = $datesstring->monthdate;
            $result->monthtitle   = $datesstring->monthtitle;
        }
        
        if ( $editid )
        {// Требуется создание формы для редактирования оценок контрольной точки
            $anchor = $this->get_anchor($plans, $editid, $eventid);
            if ( $this->dof->im('journal')->is_access('give_grade', $editid) ||
                 $this->dof->im('journal')->is_access('give_grade/in_own_journal', $editid) )
            {// Есть права на выставление оценок по контрольной точке
                $result->formbegin = $this->get_begin_form($editid, $eventid, $anchor);
                $result->formend   = $this->get_end_form($eventid);
            }
        }
        return $result;
    }

    
    /** 
     * Возвращает редактируемую ячейку контрольной точки для ученика
     * 
     * @param int $studentid - ID ученика, которому принадлежит ячейка
     * @param int $cpassedid - ID подписки ученика
     * @param int $gradeid - ID текущей оценки
     * @param string $oldgrade - Текущая оценка
     * @param int $eventid - ID редактируемого события
     * @param string $scale - Шкала оценок
     * 
     * @return string - HTML-код ячейки
     */
    private function get_cell_form($studentid, $cpassedid, $oldgrade = NULL, $gradeid = 0, $eventid, $scale = null)
    {
        // Базовые переменные
        $cellhtml = '';
        
        // Получить подписку на учебный процесс
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
        
        // Время начала подписки
        $begindate = $cpassed->begindate;
        if ( empty($cpassed->begindate) )
        {// Время начала не указано
            // Установка времени начала подписки на время начала учебного процесса
            if ( ! $begindate = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'begindate') )
            {// Время начала учебного процесса не установлено
                // Установка времени начала подписки на время начала учебного периода
                if ( ! $begindate = $this->dof->storage('ages')->get_field($cpassed->ageid, 'begindate') )
                {// Время начала периода не установлено
                    // Текущее время
                    $begindate = time();
                }
            }
        }
        
        // Нормализация времени до начала дня @todo - Учет часового пояса подразделения
        $time = dof_usergetdate($begindate); 
        $begindate = mktime(0, 0, 0, $time['mon'], $time['mday'], $time['year']);
        
        // Время конца подписки
        $enddate = $cpassed->enddate;
        if ( empty($cpassed->enddate) )
        {// Время начала не указано
            // Установка времени конца подписки на время конца учебного процесса
            if ( ! $enddate = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'enddate') )
            {// Время конца учебного процесса не установлено
                // Установка времени конца подписки на время конца учебного периода
                if ( !$enddatee = $this->dof->storage('ages')->get_field($cpassed->ageid, 'enddate') )
                {// Время конца периода не установлено
                    // Текущее время
                    $enddate = time();
                }
            }
        }
        
        // Нормализация времени до конца дня @todo - Учет часового пояса подразделения
        $time = dof_usergetdate($enddate);
        $enddate  = mktime(23, 59, 59, $time['mon'], $time['mday'], $time['year']);
        
        // Возможность редактирования ячейки
        $disabled = FALSE;
        if ( $schevent = $this->dof->storage('schevents')->get($eventid) )
        {// Событие контрольной точки найдено
            if ( ( $schevent->date < $begindate || $schevent->date > $enddate ) AND
                 ! $this->dof->im('journal')->is_access('remove_not_studied') )
            {// Запрет редактирования ячейки
                $disabled = TRUE;
                $cellhtml .= '<input type="hidden" name="noaway[' . $cpassedid . ']" value="' . $studentid . '">';
                $cellhtml .= '<input type="hidden" name="editgrades[' . $cpassedid . ']" value="' . $studentid . '">';
                $cellhtml .= '<input type="hidden" name="away[' . $cpassedid . ']" value="' . $studentid . '">';
            }
        }
        // Форирование строки для отключения элементов формы
        $disabledstring = '';
        if ( $disabled )
        {
            $disabledstring = ' disabled ';
        }
        
        // Формирование таблицы для формы
        $cellhtml .= '<table callpadding="0" celspacing="0" border="0" class="dof_cpassedgradeform">';
        $cellhtml .= '<tr><td rowspan="2">';
        $cellhtml .= '<input type="hidden" name="gradeid[' . $cpassedid . ']" value="' . $gradeid . '">';
        
        // Получение элементов выпадающего списка оценок
        $variants = $this->get_grade_variants($scale);
        // Создание выпадающего списка оценок
        $cellhtml .= '<select name="editgrades[' . $cpassedid . ']"' . $disabledstring . '>';
        foreach ( $variants as $variant )
        {
            if ( $oldgrade == $variant->value )
            {
                $variant->selected = 'selected';
            } else
            {
                $variant->selected = '';
            }
            $cellhtml .= '<option value="' . $variant->value . '" ' . $variant->selected . '>' . $variant->name . '</option>' . "\n";
        }
        $cellhtml .= '</select>';
        $cellhtml .= '</td>';
        
        // Посещаемость
        if ( ! empty($schevent) )
        {// Событие найдено
            // Получение посещаемости по событию
            $presence = $this->dof->storage('schpresences')->get_present_status($studentid, $eventid);
        } else
        {// Контрольная точка без посещаемости
            $presence = 'noaway';
        }
        
        if ( $eventid )
        {// Установка посещаемости только для контрольной точки с событием
            
            $cellhtml .= '<td align="left">';
            
            // Проверка на отсутствие на уроке (Н)
            if ( $presence === '0' )
            {// Ученик отсутствовал на уроке
                $check = 'checked';
            } else
            {
                $check = '';
            }
            // Формирование чекбокса с указанием пропуска
            $cellhtml .= '<div class="dof_cpassedgradeform_checkbox">
                <input type="checkbox" name="away[' . $cpassedid . ']" id="checkbox_away[' . $cpassedid . ']" value="' . $studentid . '" ' . $check . ' ' . $disabledstring . '>';
            $cellhtml .= '<label for="checkbox_away[' . $cpassedid . ']">'.$this->dof->get_string('away_n', 'journal')."</label>";
            $cellhtml .= '</div>';

            // Проверка на отсутствие посещаемости (Н/О)
            if ( $presence === false || $presence == 'noaway' )
            {// Посещаемость не найдено, либо явно указано, что ученик не обучался
                $cellhtml .= '<div>';
                if ( $schevent->date < $begindate || $schevent->date > $enddate || $cpassed->status != 'active' )
                {// Событие произошло вне интервала подписки, или подписка не активна
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '" ' . $disabledstring . ' checked>';
                } elseif ( $schevent->status == 'completed' )
                {// Событие завершено
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '" checked>';
                } else
                {
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '">';
                }
                $cellhtml .= $this->dof->get_string('away_no', 'journal');
                $cellhtml .= '</div>';
            } elseif ( $schevent->date < $begindate OR $schevent->date > $enddate )
            {// Есть запись о посещаемости
                if ( $this->dof->im('journal')->is_access('remove_not_studied') )
                {// Дать возможность изменять посещаемость
                    $cellhtml .= '<div>';
                    $cellhtml .= '<input type="checkbox" name="noaway[' . $cpassedid . ']" value="' . $studentid . '">';
                    $cellhtml .= $this->dof->get_string('away_no', 'journal');
                    $cellhtml .= '</div>';
                }
            }
            $cellhtml .= '</td>';
            
            // Блок с редактированием замечания
            if ( $eventid && ! $disabled )
            {// Для контрольной точки имеется событие
                // Поверка наличия посещаемости
                $params = [];
                $params['personid'] = $studentid;
                $params['eventid'] = $eventid;
                $presence = $this->dof->storage('schpresences')->get_records($params);
                if ( ! empty($presence) && $this->dof->plugin_exists('storage', 'comments') )
                {// Комментарий к посещаемости ученика на событии
                    $presence = end($presence);
                    // Получение списка комментариев
                    $comments = $this->dof->storage('comments')->get_comments_by_object('storage', 'schpresences', $presence->id, 'public');
                    $label_class = 'btn button dof_button grpjournal_comment_modal_label';
                    if ( empty($comments) )
                    {// Комментариев по посещаемости нет
                        // Создание пустого поля 
                        $content = '<div>';
                        $content .= '<textarea name="comment[0_'.$presence->id.'_'.$cpassedid.']"></textarea>';
                        $content .= '</div>';
                    } else 
                    {// Комментарии есть
                        $label_class .= ' grpjournal_has_comments';
                        $content = '<div>';
                        foreach ( $comments as $comment )
                        {// Добавление полей комментариев
                            $content .= '<textarea name="comment['.$comment->id.'_'.$presence->id.'_'.$cpassedid.']">'.$comment->text.'</textarea>';
                        }
                        $content .= '</div>';
                    }
                    $title = $this->dof->get_string('grpjournal_comment_modal_title', 'journal');
                    $label = '<span class="'.$label_class.'" title="'.$title.'">'.
                                $this->dof->modlib('ig')->icon('feedback', NULL, ['title' => $title]).
                             '</div>';
                    if ( ! empty($content) )
                    {
                        $cellhtml .= '<td>'.$this->dof->modlib('widgets')->modal($label, $content, $title).'</td>';
                    }
                }
            }
            $cellhtml .= '</tr>';
        }
        $cellhtml .= '<input type="hidden" name="cpassedid[' . $cpassedid . ']" value="' . $studentid . '">';
        $cellhtml .= '</table>';
        
        return $cellhtml;
    }

    /** 
     * Получить содержимое ячейки
     * 
     * @param int $studentid - ID персоны
     * @param object $plan - Контрольная точка в учебном плане данного учебного процесса
     * @param int $cpassedid - ID подписки на учебный процесс
     * @param object $gradedata - Данные об оценке
     * 
     * @return string html-код оценки и отметки об отсутствии
     */
    private function get_cell_string($studentid, $plan, $cpassedid, $gradedata = NULL)
    {
        $html = '';
        
        $prdate = '';
        $presence = NULL;
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        // Получение события контрольной точки
        if ( isset($plan->event) )
        {// Событие найдено
            // Получение посещаемости персоны
            $params             = [];
            $params['personid'] = $studentid;
            $params['eventid']  = $plan->event->id;
            $presences = $this->dof->storage('schpresences')->get_records($params);
            if ( ! empty($presences) && is_array($presences) )
            {// Посещаемость найдена
                foreach ( $presences as $item )
                {
                    if ( ! empty($item->orderid) )
                    {// Посещаемость подкреплена приказом
                        $presence = $item;
                        $date = $this->dof->storage('orders')->get_field($presence->orderid, 'exdate');
                        $prdate = dof_userdate($date, '%d.%m.%Y %H:%M', $usertimezone, false);
                        break;
                    }
                }
                
            }
        }
        
        // Отображение отметки пользователя по контрольной точке
        if ( ! empty($gradedata) )
        {// Имеются данные по оценке
            // Отображение оценки
            $date = $this->dof->storage('orders')->get_field($gradedata->orderid, 'exdate');
            $date = dof_userdate($date, '%d.%m.%Y %H:%M', $usertimezone, false);
            $html = '<span class="has-tooltip" title="' . $date . '">' . $gradedata->grade . '</span>';
        }
        
        // Добавление данных о посещаемости
        if ( isset($plan->event) AND isset($presence->present) AND $presence->present === '0' )
        {// Ученик отсутствовал на занятии - "Н"
            $html .= '<span class="has-tooltip" title="' . $prdate . '">(' . $this->dof->get_string('away_n', 'journal') . ')</span>';
        } else
        {// Посещаемость не найдена
            $html .= '<span title="' . $prdate . '">&nbsp;</span>';
        }
        
        if ( isset($presence->id) )
        {// Отобразить список комментариев по посещаемости
            $options = [];
            $options['display'] = 'icon';
            $html .= $this->presence_comment_block($presence->id, $options);
        }
        
        // Вернуть данные по ячейке
        return $html;
    }

    /** Возвращает данные в одной клетке 
     * @param int $studentid - id студента 
     * @param object $plan - контрольная точка с событием  из тем. планирования
     * @param object $gradedata - данные об оценке, либо null
     * @param int $cpassedid - id  подписки
     * @param int $editid - id редактируемого плана
     * @param int $eventid - id редактируемого события
     * @param string $scale - шкала оценок
     * @return string
     */
    private function get_one_cell($studentid, $plan, $gradedata, $cpassedid, $editid, $eventid, $scale = null)
    {
        $grades = '';
        // если id КТ из ем. планирования и редактируемой КТ совпадают
        // ячейка редактируется 

        if ( $plan->plan->id == $editid AND ( $this->dof->im('journal')->is_access('give_grade', $editid)
                OR $this->dof->im('journal')->is_access('give_grade/in_own_journal', $editid)) )
        {
            if ( $gradedata )
            {// есть оценка
                $grades = $this->get_cell_form($studentid, $cpassedid, $gradedata->grade, $gradedata->id, $eventid, $scale);
            } else
            {// нет оценки
                $grades = $this->get_cell_form($studentid, $cpassedid, 0, 0, $eventid, $scale);
            }
        } else
        {// это обычная ячейка. Просто покажем оценку
            $grades = $this->get_cell_string($studentid, $plan, $cpassedid, $gradedata);
        }
        // возвращаем код формы
        return $grades;
    }

    /** 
     * Возвращает стилизацию ячейки для контрольной точки и пользователя
     * 
     * @param int $studentid - ID персоны
     * @param object $plan - Контрольная точка в учебном плане данного учебного процесса
     * @param int $cpassedid - ID подписки на учебный процесс
     * @param int $editid - ID редактируемой контрольной точки
     * @param int $eventid - ID редактируемого события
     * 
     * @return string - style-параметр для HTML-тега
     */
    private function get_color_cell($studentid, $plan, $cpassedid, $editid, $eventid)
    {
        $style = '';

        if ( $plan->plan->id != $editid AND isset($plan->event) AND 
           (
               ( $this->dof->storage('schpresences')->get_present_status($studentid, $plan->event->id) === false ) OR 
               ( 
                   isset($plan->date) && $plan->date > 0 && 
                   (
                       $this->dof->storage('cpassed')->get_field($cpassedid, 'begindate') > $plan->date ||
                       $this->dof->storage('cpassed')->get_field($cpassedid, 'enddate') < $plan->date )
                   )
               )
           )
        {
            $style = 'style="background: #aaa;"';
        }
        return $style;
    }
    
    /** 
     * Сформировать начало формы редактирования оценок
     * 
     * @param int $planid - ID контрольной точки в учебном плане данного предмета-класса
     * @param int $eventid - ID учебного события данного предмета-класса
     * 
     * @return string HTML-код формы
     */
    private function get_begin_form($editid, $eventid, $anchor)
    {
        global $USER;
        
        //запомним идентификатор сессии
        $sesskey = '';
        if ( isset($USER->sesskey) AND $USER->sesskey )
        {//запомним идентификатор сессии
            $sesskey = $USER->sesskey;
        }
        $addvars                 = [];
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        $addvars['csid'] = $this->csid;
        
        // Объявление формы
        $actonurl = $this->dof->url_im('journal', '/group_journal/process_grades.php', $addvars);
        $result = '<form name="gradeeditform" method="post" action="'.$actonurl.'">';
        
        // Идентификатор контрольной точки
        $result .= '<input type="hidden" name="planid" value="' . $editid . '"/>';
        // Идентификатор учителя
        $result .= '<input type="hidden" name="teacherid" value="' .
                $this->dof->storage('cstreams')->get_field($this->csid, 'teacherid') . '"/>';
        // Идентификатор учителя
        $result .= '<input type="hidden" name="sesskey" value="' . $sesskey . '">';
        // Идентификатор собыия
        $result .= '<input type="hidden" name="eventid" value="' . $eventid . '">';
        // Идентификатор предмето-класса
        $result .= '<input type="hidden" name="csid" value="' . $this->csid . '">';
        // Идентификатор подразделения
        $result .= '<input type="hidden" name="departmentid" value="' . $addvars['departmentid'] . '">';
        // Якорь
        $result .= '<input type="hidden" name="anchor" value="' . $anchor . '">';
        
        return $result;
    }

    /** 
     * Сформировать конец формы редактирования оценок
     * 
     * @param int $eventid - ID учебного события данного предмета-класса
     * 
     * @return string HTML-код формы
     */
    private function get_end_form($eventid)
    {
        $result = '';
        $result .= '<br/><b>'.$this->dof->get_string('jornal_edit_warning', 'journal').'</b><br/>';
        
        // ДОБАВЛЕНИЕ ЧЕКБОКСА ДЛЯ ОТМЕТКИ УРОКА
        // Получить статус события
        $status = $this->dof->storage('schevents')->get_field($eventid, 'status');
        
        if ( $this->dof->im('journal')->is_access('can_complete_lesson', $eventid) OR
             $this->dof->im('journal')->is_access('can_complete_lesson/own', $eventid) )
        {// Есть права на завершение занятия
            $result .= '<b>'.$this->dof->get_string('jornal_edit_warningtwo', 'journal') . '</b><br/>';
            
            if ( $this->dof->storage('config')->get_config_value('time_limit', 'storage', 'schevents', optional_param('departmentid', 0, PARAM_INT)) )
            {// Установлена настройка
                $result .= '<br/><b>'.$this->dof->get_string('jornal_edit_warning_limit_time', 'journal').'</b><br/>';
            }
            // Время начала занятия
            $evdate = $this->dof->storage('schevents')->get_field($eventid, 'date');
            
            if ( $this->dof->workflow('schevents')->limit_time($evdate) )
            {// Временные лимиты позволяют установить отметку
                // Чекбокс с подтверждением смены статуса урока
                $result .= '<br/><span><b>'.$this->dof->get_string('lesson_complete_title', 'journal').'</b></span>';
                $result .= '<input type="checkbox" name="box"></p>';
            }
        }
        
        // Действия над оценками
        $result .= '<br/><input type="submit" name="save_and_continue" value="' .
                $this->dof->get_string('save_and_continue', 'journal') . '"/>';
        $result .= '<input type="submit" name="save" value="' .
                $this->dof->get_string('to_save', 'journal') . '"/>';
        $result .= '<input type="submit" name="restore" value="' .
                $this->dof->get_string('restore', 'journal') . '"/>';
        $result .= '</form>';
        
        return $result;
    }

    /** 
     * Возвращает данные для одного студента
     * 
     * @param int $i - порядковый номер
     * @param object $student - студент
     * @param array $cpasseds - его подписки
     * @param array $plans - контрольные точки
     * @param int $editid - id редактируемого плана
     * @param int $eventid - id редактируемого события
     * @param string  $info - показывает иформацию, что нужно вывести, если пусто,
     * 							то выводить всю информацию
     * @return object информация о студенте
     */
    private function get_line_for_student($i, $cpassed, $plans, $editid, $eventid, $info = '')
    {
        global $CFG;
        
        $depid                     = optional_param('departmentid', 0, PARAM_INT);
        $cstreamid                 = optional_param('csid', 0, PARAM_INT);
        $addvars                   = array();
        $addvars['departmentid']   = $depid;
        $curstudent                = new stdClass();
        // устанавливаем порядковый номер
        $curstudent->studentnumber = $i;
        $links                     = '';
        $name                      = $this->dof->storage('persons')->get_fullname($cpassed->studentid);
        // перечеркнем имя
        if ( $cpassed->status == 'failed' OR $cpassed->status == 'canceled' )
        {
            $name = "<span style='text-decoration:line-through;color:gray;'> {$name} </span>";
        }
        // серый цвет
        if ( $cpassed->status == 'completed' )
        {
            $name = "<span style='color:gray;'> {$name} </span>";
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// можно просматривать шаблон - добавим ссылку на просмотр шаблона на неделю
            $ageid = $this->dof->storage('cstreams')->get_field($cstreamid, 'ageid');
            
            $options = array(
                'alt' => $this->dof->get_string('view_week_template_on_student', 'journal'),
                'title' => $this->dof->get_string('view_week_template_on_student', 'journal'),
                );
            $url = $this->dof->url_im('schedule', '/view_week.php?studentid=' . $cpassed->studentid . '&ageid=' . $ageid, $addvars);
            $img = $this->dof->modlib('ig')->icon_plugin('show_schedule_week','im','journal', $url, $options);
            $links = $img;
        }
        $mdlcourse = $this->dof->storage('programmitems')->get_field($cpassed->programmitemid, 'mdlcourse');
        if ( isset($mdlcourse) AND $this->dof->modlib('ama')->course(false)->is_course($mdlcourse) )
        {
            $mdluser = $this->dof->storage('persons')->get_field($cpassed->studentid, 'mdluser');
            $links .= $this->dof->modlib('ig')->icon('moodle', $CFG->wwwroot . "/course/user.php?id=" . $mdlcourse . "&user=" . $mdluser .
                    "&mode=outline");
        }
        // Показать занятия учащегося
        $options = array(
            'alt' => $this->dof->get_string('view_events_student', 'journal'),
            'title' => $this->dof->get_string('view_events_student', 'journal'),
            );
        $url = $this->dof->url_im('journal', '/show_events/show_events.php?personid=' . $cpassed->studentid, $addvars) . '&date_to=' . time() . '&date_from=' . time();
        $img = $this->dof->modlib('ig')->icon_plugin('events_student','im','journal', $url, $options);
        // История обучения по дисциплине
        $options = array(
            'programmsbcid' => $cpassed->programmsbcid,
            'programmitemid' => $cpassed->programmitemid,
        );
        $url = $this->dof->url_im('journal', '/cphistory.php', $addvars + $options);
        $options = array(
            'alt' => $this->dof->get_string('cphistory', 'journal'),
            'title' => $this->dof->get_string('cphistory', 'journal'),
        );
        $cphistorylink = $this->dof->modlib('ig')->icon_plugin('history', 'im', 'journal', $url, $options);
        $curstudent->fio = '<a href="' . $this->dof->url_im('journal', '/person.php?personid=' . $cpassed->studentid, $addvars) . '">' .
                $name .
                '</a>' . $img . $links . $cphistorylink;
        ;

        $curstudent->cpassedid = $cpassed->id;
        // вывод информации
        if ( $info == 'info' )
        {
            return $curstudent;
        }
        // объявляем массив для будущих оценок студента
        $curstudent->studentgrades = array();
        // собираем ключи массива - id учебных событий
        if ( is_array($plans) )
        {
            foreach ( $plans as $plan )
            {// для всех дат проставляем оценки
                // создаем объект оценки для обработки шаблоном
                $grade = new stdClass();
                // получаем оценку за указанную дату
                // нулевая шкала - возьмем из предмета
                if ( IS_NULL($plan->plan->scale) )
                {
                    $csid    = $plan->plan->linkid;
                    $pitemid = $this->dof->storage('cstreams')->get_field($csid, 'programmitemid');
                    $scale   = $this->dof->storage('programmitems')->get_field($pitemid, 'scale');
                    // и в программе не указана - возбмем из настроек по умолчанию
                    if ( empty($scale) )
                    {
                        $scale = $this->dof->storage('config')->get_config_value('scale', 'storage', 'plans', $depid);
                    }
                } else
                {
                    $scale = $plan->plan->scale;
                }

                // @todo в будущем передалать для вывода нескольких оценок за одну дату
                $gradedata                   = $this->dof->storage('cpgrades')->
                        get_grade_student_cpassed($cpassed->id, $plan->plan->id);
                // получем оценку студента
                $grade->grades               = $this->get_one_cell($cpassed->studentid, $plan, $gradedata, $curstudent->cpassedid, $editid, $eventid, $scale);
                // получем оценку студента
                $grade->color                = $this->get_color_cell($cpassed->studentid, $plan, $curstudent->cpassedid, $editid, $eventid);
                // добавляем оценку в массив оценок ученика
                $curstudent->studentgrades[] = $grade;
            }
        }
        if ( $info == 'grades' )
        {
            unset($curstudent->fio);
            unset($curstudent->studentnumber);
        }
        // вернем информацию о студенте
        return $curstudent;
    }

    /** 
     * Возвращает данные для всех студентов
     * 
     * @param array $plans - контрольные точки тематического плана учебного процесса
     * @param int $editid - ID редактируемой контрольной точки
     * @param int $eventid - ID редактируемого события
     * 
     * @param string $info - показывает иформацию, что нужно вывести, если пусто,
     * 							то выводить всю информацию
     * 
     * @return array информация о всех студентах данного потока
     */
    private function get_lines_for_students($plans, $editid, $eventid, $info = '')
    {
        // Настройка "Отображать отписанных учеников в журнале группы"
        $showjunk = $this->dof->storage('config')->get_config_value('showjunkstudents',
                'im', 'journal', optional_param('departmentid', 0, PARAM_INT));
        
        // Получение всех подписок на предмето-класс
        $cpasseds = $this->get_cpassed($showjunk);
        
        $result = [];
        if ( $cpasseds )
        {// Подписки найдены
            $i = 0;
            foreach ( $cpasseds as $cpassed )
            {
                // Текущий порядковый номер
                ++$i;
                // Добавление информации о студенте
                $result[$cpassed->id] = $this->get_line_for_student($i, $cpassed, $plans, $editid, $eventid, $info);
            }
        }
        return $result;
    }

    /** Создает строку дат для вывода журнала 
     * 
     * @return object объект, содержащий массив с данными
     * @param object $plans - массив контрольных  точек учебного потока или false в случае неудачи
     */
    private function create_datesstring($plans)
    {
        $result     = new stdClass();
        // создаем счетчик месяцев
        $monthcount = 0;
        $oldmname   = '';
        if ( !$plans )
        {// не переданно ни одной темы планирования - построить строку дат не удастся
            return false;
        }
        // получаем строку дат
        $dates = $this->generate_all_dates($plans);
        foreach ( $dates as $date )
        {// перебираем все события и собираем массивы дат и названий месяцев
            // создаем якорь
            $anchor                 = new stdClass();
            $anchor->anchornum      = $date->date;
            $result->upper_anchor[] = $anchor;
            // вычисляем название текущего месяца
            $mname                  = dof_im_journal_format_date($date->date, 'm');

            // если про просматриваемая дата не находится в том же месяце, что и предыдущая,
            // то дополняем список месяцев
            if ( $oldmname != $mname )
            {
                $monthcount++;
                // создаем объект месяца
                $result->monthtitle[$monthcount]         = new stdClass();
                // заполняем название месяца
                $result->monthtitle[$monthcount]->mtitle = $mname;
                $oldmname                                = $mname;
            }
            // прибавляем счетчик дат в месяце
            $result->monthtitle[$monthcount]->mcolspan++;
            // записываем новую дату в журнал
            $result->monthdate[] = $date;
        }
        return $result;
    }

    /** Вызывается из generate_all_dates Создает один объект даты для журнала.
     * 
     * @return object дата в нужном для templater'a формате
     * @param object $plan
     * @param object $event[optional]
     */
    private function generate_single_date($plan, $date, $event = null)
    {
        $depid                   = optional_param('departmentid', 0, PARAM_INT);
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        // устанавливаем путь к теме в планировании
        $dayurl                  = '#' . $date;
        if ( $event AND is_object($event) )
        {
            $eventid = $event->id;
        } else
        {
            $eventid = 0;
        }
        $editurl    = $this->dof->url_im('journal', '/group_journal/index.php', array_merge(array('csid' => $this->csid, 'planid' => $plan->id, 'eventid' => $eventid), $addvars))
                . '#jm' . $date; // добавляем ссылку на якорь, чтобы страница проматывалась 
        // горизонтально до нужного места
        // переходим к составлению ссылки на редактирование
        $dateobject = new stdClass();
        if ( !$event )
        {// если это четвертная или годовая оценка - выведем только ее название
            $dateobject->datecode = $plan->name;
            if ( $this->dof->im('journal')->is_access('give_grade', $plan->id) OR
                    $this->dof->im('journal')->is_access('give_grade/in_own_journal', $plan->id) )
            {
                $dateobject->datecode .= dof_im_journal_date_edit(null, 'd', $dayurl, $editurl);
            } else
            {
                $dateobject->datecode .= dof_im_journal_date_edit(null, 'd', $dayurl);
            }
        } else
        {// если это обычная дата - выведем ее
            if ( $this->dof->im('journal')->is_access('give_grade', $plan->id) OR
                    $this->dof->im('journal')->is_access('give_grade/in_own_journal', $plan->id) )
            {// если статус неактивный выведем просто даты
                $dateobject->datecode = dof_im_journal_date_edit($date, 'd', $dayurl, $editurl);
            } else
            {// если активный, то выведем значек редактирования
                $dateobject->datecode = dof_im_journal_date_edit($date, 'd', $dayurl);
            }
            // сделаем дату жирной на текущее время
            $evdate = dof_usergetdate($date);
            $tmdate = dof_usergetdate(time());
            if ( $evdate['mon'] == $tmdate['mon']
                    AND $evdate['mday'] == $tmdate['mday']
                    AND $evdate['year'] == $tmdate['year'] )
            {
                $dateobject->datecode = '<b>' . $dateobject->datecode . '</b>';
                if ( ($date < time()) AND ( ($date + $event->duration) > time()) )
                {
                    $dateobject->datecode = '<div id="menu">' . $dateobject->datecode . '</div>';
                }
            }
        }

        // устанавливаем якорь как метку времени независимо от 

        $dateobject->date = $date;
        return $dateobject;
    }

    /** Вызывается из generate_datesstring. Получить строку со всеми датами для журнала
     * 
     * @return array - даты для вывода в журнал
     * @param array $plans - массив контрольных точек учебного потока
     */
    private function generate_all_dates($plans)
    {
        $result = array();
        // собираем даты
        foreach ( $plans as $plan )
        {// получим событие, которое относится к данной теме тематического планирования
            if ( isset($plan->event) AND is_object($plan->event) )
            {// если событие есть - то покажем дату
                $result[] = $this->generate_single_date($plan->plan, $plan->date, $plan->event);
            } else
            {// если события нет - только название
                $result[] = $this->generate_single_date($plan->plan, $plan->date);
            }
        }
        return $result;
    }

    /** Возвращает масив оценок нужной структуры, для использования в форме.
     * @return array массив объектов вида
     *         value->'значение оценки'
     *         name->'отображаемое в форме имя оценки'
     *         selected->'selected', если вы хотите видеть этот пункт выбранным по умолчанию или null,
     *         в противном случае  
     * @param string $scale - тип используемой шкалы
     */
    public function get_grade_variants($scale = null)
    {

        $fromplan = $this->dof->storage('plans')->get_grades_scale_str($scale);
        $variants = array();
        foreach ( $fromplan as $gradevariant )
        {
            $variant        = new stdClass();
            $variant->name  = $gradevariant;
            $variant->value = $gradevariant;
            $variants[]     = $variant;
        }
        // по умолчанию к любой шкале добавляем "нулевую оценку" - для того, чтобы ее можно было удалить
        $variant        = new stdClass();
        $variant->name  = ' ';
        $variant->value = '';
        $variants[]     = $variant;
        // возвращаем шкалу оценок в указанном виде
        return $variants;
    }

    /** Получить id html-якоря для редактированияячейки
     * 
     * @return 
     * @param object $plans
     */
    private function get_anchor($plans, $planid, $eventid)
    {
        if ( !is_array($plans) )
        {// неверный формат исходных данных
            return 0;
        }
        //print_object($plans);
        foreach ( $plans as $anchor => $plan )
        {// ищем нужный id в массиве
            if ( isset($plan->event) )
            {//если есть событие
                if ( $plan->plan->id == $planid AND $plan->event->id == $eventid )
                {//проверяем и КТ и событие
                    return $anchor;
                }
            } else
            {//события нет
                if ( $plan->plan->id == $planid )
                {//проверяем только КТ
                    return $anchor;
                }
            }
        }
        // если ничего не нашли
        return 0;
    }
    
    /** 
     * Получить блок комментариев по посещаемости
     * 
     * @param $presenceid - ID элемента учета посещаемости
     * @param $options - ID элемента учета посещаемости
     * 
     * @return string - HTML-блок с комментариями по элементу
     */
    private function presence_comment_block($presenceid, $options = [])
    {
        $html = '';
        
        // Добавление комментария по ячейке
        if ( $this->dof->plugin_exists('im', 'comments') )
        {// Получить форму комментариев
            if ( $presenceid > 0 )
            {// Есть посещаемость
                // Получение списка комментариев
                $content = $this->dof->im('comments')->show_comments_list(
                    'storage', 
                    'schpresences', 
                    $presenceid, 
                    NULL, 
                    ['return_html' => true, 'disable_actions' => true]
                );
                if ( ! empty($content) )
                {  
                    $title = $this->dof->get_string('grpjournal_comment_modal_title', 'journal');
                    $label = dof_html_writer::span(
                        $this->dof->modlib('ig')->icon('feedback', NULL),
                        'btn button dof_button grpjournal_comment_modal_label grpjournal_has_comments'
                    );
                    $html .= $this->dof->modlib('widgets')->modal($label, $content, $title);
                }
            }
        }
        return $html;
    }
}

/**
 * Возвращает отформатированную дату
 * @param int $date - метка времени которую надо вывести
 * @param $format - тип форматирования даты:
 * dmy: выводит дд.мм.гг
 *  dm: выводит дд.мм
 *  my: выводит ммм гг, ммм - название месяца из трех букв
 *   m: выводит полное название месяца
 *   d: выводит дд
 * @param string $url - путь, по которому надо перейти, если дату надо сделать ссылкой
 * @return string
 */
function dof_im_journal_format_date($date, $format = 'dmy', $url = NULL)
{
    global $DOF;
    //получаем путь с нужной функцией
    $amapath = $DOF->plugin_path('modlib', 'ama', '/amalib/utils.php');
    //подключаем путь с нужной функцией
    require_once($amapath);
    if ( ama_utils_is_intstring($date) )
    {//получена дата - форматируем
        switch ( $format )
        {
            case 'dmy': $rez = dof_userdate($date, '%d.%m.%y');
                break;
            case 'dm': $rez = dof_userdate($date, '%d.%m');
                break;
            case 'my': $rez = dof_userdate($date, '%b %y');
                break;
            case 'm': $rez = dof_userdate($date, '%B');
                break;
            case 'd': $rez = dof_userdate($date, '%d');
                break;
            default: $rez = $date;
        }
        // strftime в win32 возвращает результат в cp1251 - исправим это
        if ( stristr(PHP_OS, 'win') AND ! stristr(PHP_OS, 'darwin') )
        {//это виндовый сервер
            //if ( $localewincharset = get_string('localewincharset') )
            //{//изменим кодировку символов из виндовой в utf-8
            //    $textlib = textlib_get_instance();
            //    $rez = $textlib->convert($rez, $localewincharset, 'utf-8');
            //}
        }
    } else
    {//получена строка - вставим ее
        $rez = trim($date);
    }
    if ( !is_null($url) AND is_string($url) )
    {//делаем дату ссылкой
        $rez = "<a href=\"{$url}\">" . $rez . '</a>';
    }
    return $rez;
}

/**
 * Возвращает отформатироанную дату и 
 * значок редактирования как ссылку
 * @param int $date метка времени
 * @param string $format - см. описание к dof_im_journal_format_date 
 * @param string $durl - путь ссылки для даты,
 * если не указана - дата выводится как просто строка
 * @param string $eurl - путь ссылки для значка, 
 * если не указана значок не показывается
 * @param bool $imgsubdate - вывести значок под датой или рядом 
 * по умолчанию выводит значок под датой
 * @return string 
 */
function dof_im_journal_date_edit($date, $format = 'dmy', $durl = null, $eurl = null, $imgsubdate = true)
{
    global $DOF;
    //получаем форматированную дату
    $rez = dof_im_journal_format_date($date, $format, $durl);
    //добавляем значок форматирования
    if ( !is_null($eurl) AND is_string($eurl) )
    {//передана строка - делаем ссылку
        //рисуем картинку
        $imgedit = '<img src="' . $DOF->url_im('journal', '/icons/edit.png') . '">';
        //делаем ее ссылкой        
        $imglink = "<a href=\"{$eurl}\">" . $imgedit . '</a>';
    } else
    {//ссылка не передана - не показываем значок
        $imglink = '';
    }
    if ( is_bool($imgsubdate) AND $imgsubdate )
    {
        return $rez . '<br />' . $imglink;
    }
    return $rez . '&nbsp;' . $imglink;
}

/**
 * Показывает можно редактировать дату 
 * элемента темплана или нельзя.
 * @param int $planid - id элемента темплана
 * @param int $csid - id потока
 * @return bool true - можно изменять дату события, 
 * false - нельзя изменять дату события
 */
function dof_im_journal_is_editdate($planid, $csid)
{
    global $DOF;
    if ( !$DOF->im('journal')->get_cfg('teacher_can_change_lessondate') )
    {// если прав у учителя нет - значит редактировать дату нельзя
        return false;
    }
    require_once($DOF->plugin_path('modlib', 'ama', '/amalib/utils.php'));
    if ( !ama_utils_is_intstring($planid) OR ! ama_utils_is_intstring($csid) )
    {//передано непонятно что - нельзя дату менять
        return false;
    }
    if ( !$planid )
    {//для нового элемента темплана 
        //дату можно редактировать
        return true;
    }
    if ( !$DOF->storage('schevents')->get_records(array(
                'cstreamid' => $csid, 'planid'    => $planid,
                'status'    => array('plan', 'completed', 'postponed', 'replaced'))) )
    {//для данного элемента темплана события нет';
        //дату редактировать нельзя
        return false;
    }
    return true;
}

/**
 * Класс для отрисовки 
 * таблицы тематического планирования
 * в классном журнале
 */
class dof_im_journal_templans_table extends dof_im_journal_rawdata
{

    public function __construct($dof, $csid)
    {
        parent::__construct($dof, $csid);
    }

    /** Определяем, что для страницы списка уроков в журнале нам нужны только уроки 
     * со статусами: 'plan', 'completed', 'postponed', 'replaced'
     * 
     * @return array
     */
    protected function get_eventstatuses()
    {
        return array('plan', 'completed', 'postponed', 'replaced');
    }

    /**
     * Возвращает массив объектов c необходимыми свойствами
     * для вставки в таблицу тем уроков
     * @return mixed array или bool false
     */
    private function get_topics()
    {
        if ( !$checkpoints = $this->get_checkpoints() )
        {//не получили события потока
            return false;
        }
        //print_object($checkpoints);
        //формируем объект с нужными полями
        $topics = array();
        foreach ( $checkpoints as $point )
        {
            $topic = new stdClass();
            if ( !empty($point->plan) AND ( $point->plan->status != 'canceled') )
            {// КТ указана - выведем информацию о ней
                $topic->planid        = $point->plan->id;
                $topic->name          = $point->plan->name;
                $topic->homework      = $point->plan->homework;
                $topic->homeworkhours = $point->plan->homeworkhours;
                $topic->replace       = ' ';
                $topic->note          = ' ';
            } else
            {// КТ нет
                $topic->planid        = 0;
                $topic->name          = '';
                $topic->homework      = '';
                $topic->homeworkhours = '';
                $topic->replace       = ' ';
                $topic->note          = ' ';
            }
            if ( !empty($point->event) )
            {//событие есть - покажем его дату и id
                $topic->eventid = $point->event->id;
                $topic->date    = $point->event->date;
            } else
            {//события нет - покажем пустую строку';
                $topic->eventid = 0;
                $topic->date    = ' ';
            }
            $topics[] = $topic;
        }
        return $topics;
    }

    /**
     * Возвращает "пустой" объект отчета об уроке
     * @return object
     */
    private function get_empty_topic()
    {
        $topic           = new stdClass();
        $topic->planid   = 0;
        $topic->date     = time();
        $topic->name     = ' ';
        $topic->homework = ' ';
        $topic->replace  = ' ';
        $topic->note     = ' ';
        return $topic;
    }

    /**
     * Возвращает массив строки заголовка таблицы
     * @return array
     */
    protected function table_head()
    {
        return array($this->dof->get_string('N', 'journal'),
            $this->dof->get_string('date', 'journal'),
            $this->dof->get_string('what_passed_on_lesson', 'journal'),
            // @todo нету ни замены, ни заметок
            $this->dof->get_string('homework', 'journal'),
            $this->dof->get_string('hwhours', 'journal'),
            //$this->dof->get_string('replacement', 'journal'),
            //$this->dof->get_string('notes', 'journal')
            $this->dof->get_string('status', 'journal')
        );
    }

    /**
     * Возвращает массив строк данных
     * отформатированных для вывода 
     * с помощью moodle-функции print_table()
     * @return array
     */
    protected function table_data()
    {
        $rez    = array();
        if ( !$topics = $this->get_checkpoints() )
        {//тематического планирования нет
            $topics = array();
        }
        $depid                   = optional_param('departmentid', 0, PARAM_INT);
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        $previosdates            = array();
        $i                       = 1;
        foreach ( $topics as $one )
        {//формируем массив строк таблицы
            $linkdate = $one->date;
            while ( in_array($linkdate, $previosdates) )
            {// устанавливаем соответствие между датой здесь и на странице оценок
                $linkdate = $linkdate + 1;
            }
            // установим начальные значения
            $eurl       = null;
            $statusname = '';
            // если нет КТ, то одно право, иначе другое    
            if ( !empty($one->plan) )
            {
                $rezylt = ($this->dof->storage('plans')->is_access('edit', $one->plan->id) OR
                        $this->dof->storage('plans')->is_access('edit/in_own_journal', $one->plan->id));
            } else
            {
                $rezylt = ($this->dof->im('journal')->is_access('give_theme_event', $one->event->id) OR
                        $this->dof->im('journal')->is_access('give_theme_event/own_event', $one->event->id));
            }
            if ( !empty($one->event) )
            {//событие есть - покажем его дату и id
                if ( $rezylt )
                {// нет прав на редактирование - не показываем иконку редактирования
                    $eurl = $this->dof->url_im('journal', '/group_journal/topic.php?planid=' . $one->event->planid . '&csid=' . $this->csid . '&eventid=' . $one->event->id, $addvars);
                }
                // дата смены статуса
                $datechangestatus   = '';
                // найдем записи о смене статуса
                $status             = new stdClass();
                $status->plugintype = 'storage';
                $status->plugincode = 'schevents';
                $status->objectid   = $one->event->id;
                $status->prevstatus = 'plan';
                $sqlstatus          = $this->dof->storage('statushistory')->get_select_listing($status);
                if ( $statuses           = $this->dof->storage('statushistory')->get_records_select($sqlstatus, null, 'statusdate DESC') )
                {
                    $statusdate       = current($statuses)->statusdate;
                    $datechangestatus = dof_userdate($statusdate, '%d.%m.%Y');
                }

                $status     = $this->dof->storage('schevents')->get_field($one->event->id, 'status');
                $statusname = $this->dof->workflow('schevents')->get_name($status);
                if ( $status == 'replaced' )
                {// если урок заменен - то покажем, какой именно учитель заменил
                    $replaceid   = $this->dof->storage('schevents')->get_field($one->event->id, 'replaceid');
                    if ( $newschevent = $this->dof->storage('schevents')->get($replaceid) )
                    {
                        $statusname = '<span title=' . $datechangestatus . '>' . $statusname .
                                '<br>[' . trim($this->dof->storage('persons')->get_fullname($newschevent->teacherid)) . ']</span>';
                    }
                } else
                {// в остальных случаях
                    // покажем кто провел, или должен был провести занятие
                    $teacherid  = $this->dof->storage('schevents')->get_field($one->event->id, 'teacherid');
                    $statusname = '<span title=' . $datechangestatus . '>' . $statusname .
                            '<br>[' . trim($this->dof->storage('persons')->get_fullname($teacherid)) . ']</span>';
                }
            } elseif ( $this->dof->storage('plans')->get($one->plan->id)->linktype == 'cstreams' )
            {// события нет, но есть КТ в с linktype=cstreams
                if ( $this->dof->storage('plans')->is_access('edit', $one->plan->id) OR
                        $this->dof->storage('plans')->is_access('edit/in_own_journal', $one->plan->id) )
                {// нет прав на редактирование - не показываем иконку редактирования
                    $eurl = $this->dof->url_im('journal', '/group_journal/topic.php?planid=' . $one->plan->id . '&csid=' . $this->csid . '&eventid=0', $addvars);
                }
                $statusname = '';
            }

            //вставляем якорь и форматируем дату в нужный вид
            $one->date = '<a name = ' . $linkdate . '></a>' .
                    dof_im_journal_date_edit($one->date, 'dmy', NULL, $eurl);
            //добавляем в результирующий массив
            if ( empty($one->plan) AND ! empty($one->event) )
            {
                $one->planid = $one->event->planid;
                $one->plan   = $this->dof->storage('plans')->get($one->planid);
            }
            if ( !empty($one->plan) AND $one->plan->status != 'canceled' )
            {// КТ указана - выведем информацию о ней
                if ( !empty($one->plan->homeworkhours) )
                {// есть время  - покажем его
                    $homeworkhours = ($one->plan->homeworkhours / 60) . ' ' . $this->dof->modlib('ig')->igs('min') . '. ';
                } else
                {// нет времени - значит нет
                    $homeworkhours = '';
                }
                // спрячем задание, которое больше 100 символов
                $lengstr = mb_strlen($one->plan->homework, 'utf-8');
                if ( $lengstr > 100 )
                {
                    // видимая часть
                    $text1               = mb_substr($one->plan->homework, 0, 100, 'utf-8');
                    // скрытая часть
                    $text2               = '<span class="red ' . $one->plan->id . '_Btn"><a href="" onClick="return dof_modlib_widgets_js_hide_show(\'' . $one->plan->id . '_homework\',\'' . $one->plan->id . '_Btn\');">...</a></span>';
                    // ссылка для нажатия
                    $text3               = '<span id="hideCont" class="' . $one->plan->id . '_homework">' . mb_substr($one->plan->homework, 100, $lengstr - 100, 'utf-8') . '</span>';
                    $one->plan->homework = $text1 . $text3 . $text2;
                }
                $rez[] = $this->get_table_row($i, $one->date, $one->plan->name, $one->plan->homework, $homeworkhours, $statusname);
            } else
            {// КТ нет
                $rez[] = $this->get_table_row($i, $one->date, '', '', '', $statusname);
            }
            $i++;
            $previosdates[] = $linkdate;
        }//print_object($previosdates);
        return $rez;
    }

    /** Создать массив ячеек в строке таблицы, используя переданные данные
     * Эта функция создана для того чтобы было проще переопределить класс
     * отрисовки таблицы, и добавлять или удалять туда нужны е столбцы
     * 
     * @return array
     * @param string $strnum - номер строки
     * @param string $date - дата события
     * @param string $name - что пройдено на уроке
     * @param string $homework - домашнее задание
     * @param string $statusname - название статуса русскими буквами
     */
    protected function get_table_row($strnum, $date, $name, $homework, $homeworkhours, $statusname)
    {
        return array($strnum, $date, $name, $homework, $homeworkhours, $statusname);
    }

    /**
     * Возвращает массив выравнивания полей таблицы
     * @return array
     */
    protected function table_align()
    {
        return array('center', 'center', 'center', 'center', 'center');
    }

    /**
     * Возвращает таблицу как строку html-кода
     * @return string
     */
    public function print_table()
    {
        $table             = new stdClass();
        $table->tablealign = 'center';
        $table->head       = $this->table_head();
        //$table->wrap = array('wrap', 'wrap', 'wrap', 'wrap', 'wrap', 'wrap');
        $table->width      = '50%';
        $table->data       = $this->table_data();
        $table->align      = $this->table_align();
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
}

/** Класс для создания таблицы планирования на странице создания/редактирования темы
 * 
 */
class dof_im_journal_topic_page_table extends dof_im_journal_templans_table
{

    protected function table_head()
    {
        return array(//$this->dof->get_string('N', 'journal'),
            $this->dof->get_string('date', 'journal'),
            $this->dof->get_string('what_passed_on_lesson', 'journal'),
            $this->dof->get_string('homework', 'journal'),
                //$this->dof->get_string('replacement', 'journal'),
                //$this->dof->get_string('notes', 'journal')
                //$this->dof->get_string('hwhours', 'journal')
        );
    }

    /** Определяем, что для страницы списка уроков в журнале нам нужны только уроки 
     * со статусами: 'plan', 'completed', 'postponed', 'replaced'
     * 
     * @return array
     */
    protected function get_eventstatuses()
    {
        return array('plan', 'completed', 'postponed', 'replaced');
    }

    /** Создать массив ячеек в строке таблицы, используя переданные данные
     * Эта функция создана для того чтобы было проще переопределить класс
     * отрисовки таблицы, и добавлять или удалять туда нужные столбцы
     * 
     * @return array
     * @param string $strnum - номер строки
     * @param string $date - дата события
     * @param string $name - что пройдено на уроке
     * @param string $homework - домашнее задание
     * @param string $statusname - название статуса русскими буквами
     */
    protected function get_table_row($strnum, $date, $name, $homework, $homeworkhours = null, $statusname = null)
    {
        return array(strip_tags($date), $name, $homework);
    }
}

/**
 * Класс для создания или редактирования
 * одной темы и события на странице планирования уроков
 * @todo Удалить неиспользуемые методы
 */
class dof_im_journal_edittopic extends dof_im_journal_rawdata
{

    private $planid;
    private $eventid;
    public $rez;

    public function __construct(dof_control $dof, $planid, $csid, $eventid)
    {
        $depid                   = optional_param('departmentid', 0, PARAM_INT);
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        $this->planid            = $planid;
        $this->eventid           = $eventid;
        $path                    = $dof->url_im('journal', '/group_journal/index.php?csid=' . $csid . '&planid=' . $planid . '&eventid=' . $eventid, $addvars);
        if ( $eventid )
        {
            $path = $dof->url_im('journal', '/group_journal/index.php?csid=' . $csid . '&planid=' . $planid .
                    '&eventid=' . $eventid . '&departmentid=' . $addvars['departmentid'] . '#jm' . $dof->storage('schevents')->get_field($eventid, 'date'));
        } elseif ( $planid )
        {
            $plan = $dof->storage('plans')->get($planid);
            $time = $plan->datetheme + $plan->reldate;
            $path = $dof->url_im('journal', '/group_journal/index.php?csid=' . $csid . '&planid=' . $planid .
                    '&eventid=' . $eventid . '&departmentid=' . $addvars['departmentid'] . '#jm' . $time);
        }
        $link    = '<a href="' . $path . '">' . $dof->get_string('backward', 'journal') . '</a>';
        $strlink = '<br />' . $link;

        //печатаем результат работы
        $this->rez = '<p style="text-align:center; color:green;"><b>' .
                $dof->get_string('save_true', 'journal') . '</b>' . $strlink . '</p>';
        parent::__construct($dof, $csid);
    }

    /** Определяем, что для страницы списка уроков в журнале нам нужны только уроки 
     * со статусами: 'plan', 'completed', 'postponed', 'replaced'
     * 
     * @return array
     */
    protected function get_eventstatuses()
    {
        return array('plan', 'completed', 'postponed', 'replaced');
    }

    /** Возвращает объект с полями, необходимыми для заполнения формы
     * 
     * отчета об уроке
     * @return mixed object - объект с полями для вставки в форму
     * или bool false
     */
    public function get_topic()
    {
        if ( !$this->planid )
        {
            return $this->get_empty_topic();
        }
        $topic    = new stdClass();
        if ( !$rectopic = $this->dof->storage('plans')->get($this->planid) )
        {//не получили запись
            return false;
        }//возвращам заполненный отчет об уроке
        $topic->csid    = $this->csid;
        $topic->planid  = $rectopic->id;
        $topic->eventid = $this->eventid;
        if ( $sh             = $this->dof->storage('schevents')->get($this->eventid) )
        {
            $topic->reldate = $sh->date;
            $topic->form    = $sh->form;
        } else
        {
            $topic->reldate = $rectopic->reldate;
        }
        $topic->name          = $rectopic->name;
        $topic->homework      = $rectopic->homework;
        $topic->homeworkhours = $rectopic->homeworkhours;
        $topic->replacement   = $this->get_teacherid();
        $topic->note          = $rectopic->note;
        return $topic;
    }

    /**
     * Возвращает "пустой" объект отчета об уроке
     * @return object
     */
    private function get_empty_topic()
    {
        $topic              = new stdClass();
        $topic->csid        = $this->csid;
        $topic->planid      = 0;
        $topic->eventid     = 0;
        $topic->reldate     = time();
        $topic->topic       = '';
        $topic->homework    = '';
        $topic->replacement = $this->get_teacherid();
        $topic->note        = '';
        return $topic;
    }
    /*     * @deprecated
     * Обновляет существующий или создает новый 
     * элемент тематического планирования
     * @param object $checkpoint - объект для обновления 
     * или создания записи в таблице plans 
     * @param object $event - объект для обновления 
     * или создания записи в таблице schevents 
     * @return mixed - bool true - если записи обновлены
     * или int id новой записи из таблицы schevents,
     * bool false в иных случаях
     */

    public function save_topic($checkpoint, $event)
    {
        global $DOF;
        if ( !is_object($event) )
        {//нет данных для создания или обновления события
            return false;
        }
        if ( isset($event->planid) AND $event->planid )
        {//надо создать новое событие для существующей КТ';
            return $this->create_event($event);
        } elseif ( !isset($checkpoint->id) OR ! $checkpoint->id )
        {//надо создать новые КТ и событие';
            return $this->create_topic($checkpoint, $event);
        } else
        {//надо обновить КТ и событие';
            $plan                = $DOF->storage('plans')->get($checkpoint->id);
            // вычисляем новую относительную дату
            $checkpoint->reldate = $plan->reldate;
            // обновляем записи
            $rez1                = $DOF->storage('plans')->update($checkpoint);
            $rez2                = $DOF->storage('schevents')->update($event);
            return $rez1 AND $rez2;
        }
        //если для существующей КТ выбрана новая тема?
        //если для существующей КТ выбрана другая КТ?
        return false;
    }
    /*     * @deprecated
     * Создает новый элемент тематического планирования
     * @param object $checkpoint - объект для 
     * создания записи в таблице plans 
     * @param object $event - объект для 
     * создания записи в таблице schevents 
     * @return mixed - int id новой записи из таблицы schevents,
     * bool false в иных случаях
     */

    public function create_topic($checkpoint, $event)
    {
        if ( !$checkpointid = $this->create_checkpoint($checkpoint) )
        {//не удалось создать элемент тематического планирования
            return false;
        }
        //добавляем в событие id контрольной точки
        $event->planid = $checkpointid;
        //создаем событие
        if ( !$scheventid    = $this->create_event($event) )
        {//событие не создано - удаляем элемент темплана
            $this->dof->storage('plans')->delete($event->planid);
            return false;
        }
        //возвращаем id события
        return $scheventid;
    }

    /**
     * Создает и сохраняет запись в таблице plans
     * @param object $point - данные для сохранения
     * @return mixed int id новой записи или bool false
     */
    private function create_checkpoint($point)
    {
        global $DOF;
        if ( !is_object($point) OR ! isset($point->name) OR ! isset($point->reldate) )
        {//нет необходимых входных данных
            return false;
        }
        if ( !$this->csid )
        {//не получили id потока
            return false;
        }
        if ( !$begindate = $DOF->storage('cstreams')->get_field($this->csid, 'begindate') )
        {//не получили дату начала учебы
            return false;
        }
        //создаем заготовку КТ
        $checkpoint                = new stdClass();
        $checkpoint->linkid        = $this->csid;
        $checkpoint->linktype      = 'cstreams';
        $checkpoint->name          = $point->name;
        $checkpoint->reldate       = $point->reldate;
        $checkpoint->homework      = $point->homework;
        $checkpoint->homeworkhours = $point->homeworkhours;
        //вычисляем относительную дату контрольной точки
        $checkpoint->reldate       = $point->reldate;
        $checkpoint->status        = 'active';
        $checkpoint->directmap     = $point->directmap;
        //заносим в БД
        return $this->dof->storage('plans')->insert($checkpoint);
    }

    /**
     * Создает объект события и сохраняет его в schevents
     * @param object $event - данные для сохранения
     * @return mixed int id новой записи или bool false
     */
    private function create_event($event)
    {
        if ( !is_object($event) OR ! isset($event->date) OR ! isset($event->planid) )
        {//нет необходимых данных
            return false;
        }
        if ( !$this->csid )
        {//не получили id потока
            return false;
        }
        if ( !$teacherid = $this->get_teacherid($this->csid) )
        {//не нашли преподавателя
            return false;
        }
        //создаем заготовку события
        $schevent            = new stdClass();
        $schevent->planid    = $event->planid;
        $schevent->type      = 'normal';
        $schevent->join      = 0;
        $schevent->cstreamid = $this->csid;
        $schevent->teacherid = $teacherid;
        $schevent->date      = $event->date;
        $schevent->form      = $event->form;
        // @todo - в будущем брать из шаблона, а пока 45мин
        $schevent->duration  = 2700;
        $schevent->status    = 'plan';
        //заносим в БД
        return $this->dof->storage('schevents')->insert($schevent);
    }

    /** Формирует табличку тем в правой части экрана
     * @return string
     */
    public function print_table()
    {
        // распечатываем таблицу уроков из журнала, слегка ее модифицировав
        // $this->dof передается по ссылке для экономии ресурсов
        $table = new dof_im_journal_topic_page_table($this->dof, $this->csid);
        return $table->print_table();
    }

    /** Возвращает метку времени, которая будет создана для нового урока, 
     * чтобы осуществить корректное перенаправление на страницу журнала
     * 
     * @return int метка времени
     * @param  int время создания нового события
     */
    public function get_anchor_id($time)
    {
        $checkpoints = $this->get_checkpoints();
        if ( !is_array($checkpoints) )
        {// проверим, получи ли мы нужный тип данных 
            return 0;
        }
        while ( array_key_exists($time, $checkpoints) )
        {// получим уникальное значение
            $time = $time + 1;
        }
        // возвращаем уникальное значение ключа
        return $time - 1;
    }

    /** Сохранить данные из формы проведения урока
     * @todo вставить проверку прав при создании и обновлении всех объектов
     *
     * @return bool
     * @param object $formdata - объект из формы класса dof_im_journal_formtopic_teacher
     * @param boolean $redirect - true/false(перенапрвлять пользователя или нет)(по умолчанию true-да)
     */
    public function save_complete_lesson_form($formdata, $redirect = true)
    {
        //print_object($formdata); die;
        $success = true;
        $eventid = $formdata->eventid;
        if ( isset($formdata->create_event) AND $formdata->create_event )
        {// нужно сохранить событие
            $eventid = $this->save_journal_form_event($formdata);
            $success = (bool) $eventid && $success;
        } elseif ( $formdata->eventid )
        {// обновляем событие
            //$success = $this->update_journal_form_event($formdata) && $success;
        }

        // нет имени темы - складываем ее из род.тем
        $name      = trim($formdata->name);
        // @todo когда появится возможность задавать неограниченное количество родительских тем -
        // изменить алгоритм сохранения
        $parentids = array();
        if ( isset($formdata->parentid3) )
        {// если форма с парент активна
            if ( $formdata->parentid1 OR $formdata->parentid2 OR $formdata->parentid3 )
            {// если указана одна или несколько родительских тем
                $pointnames = array();
                if ( $formdata->parentid1 )
                {
                    if ( !$name )
                    {// если название темы не было указано - то составим его из родительских тем
                        $pointnames[] = $this->dof->storage('plans')->get_field($formdata->parentid1, 'name');
                    }
                    $parentids[] = $formdata->parentid1;
                }
                if ( $formdata->parentid2 )
                {
                    if ( !$name )
                    {// если название темы не было указано - то составим его из родительских тем
                        $pointnames[] = $this->dof->storage('plans')->get_field($formdata->parentid2, 'name');
                    }
                    $parentids[] = $formdata->parentid2;
                }
                if ( $formdata->parentid3 )
                {
                    if ( !$name )
                    {// если название темы не было указано - то составим его из родительских тем
                        $pointnames[] = $this->dof->storage('plans')->get_field($formdata->parentid3, 'name');
                    }
                    $parentids[] = $formdata->parentid3;
                }
                if ( !$name )
                {
                    $formdata->name = implode($pointnames, '. ');
                }
            }
        }

        $planid = $formdata->planid;
        if ( !$formdata->planid )
        {// контрольной точки нет
            if ( $formdata->plan_creation_type == 'create' )
            {// нужно создать новую контрольную точку
                $planid = $this->save_journal_form_plan($formdata, $parentids);
                if ( $eventid )
                {// если событие есть или было создано привязываем событие к контрольной точке
                    $success = $success & (bool) $this->set_journal_form_event_link($formdata, $eventid, $planid);
                }
            } elseif ( $formdata->plan_creation_type == 'select' )
            {// нужно просто привязать событие к уже существующей контрольной точке
                if ( $eventid )
                {// если событие есть или было создано привязываем событие к контрольной точке
                    $success = $success & (bool) $this->set_journal_form_event_link($formdata, $eventid, $formdata->existing_point);
                }
            }
        } else
        {// контрольная точка есть - и она редактируется
            $success = $success & (bool) $this->update_journal_form_plan($formdata, $parentids);
        }
        //если редирект требуется
        if ( $redirect === true )
        {// перенаправляем пользователя обратно на страницу журнала
            $this->apply_redirect_after_topic_save($formdata, $eventid, $planid, $success);
        }
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и создать из них учебное событие
     *
     * @return int - id нового созданного события в таблице schevents
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function save_journal_form_event($formdata)
    {
        // получаем объект события из формы
        $event = $this->get_event_object_from_form($formdata);
        // сохраняем событие в базу
        return $this->dof->storage('schevents')->insert($event, $formdata->eventid);
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и обновить событие
     *
     * @return bool - статус обновления в таблице schevents
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function update_journal_form_event($formdata)
    {
        $event                 = new stdClass;
        $event->ahours         = $formdata->event_ahours;
        $event->salfactor      = $this->dof->workflow('schevents')->calculation_salfactor(
                $formdata->eventid, true);
        // применяемый итоговый коэффициент
        $event->salfactorparts = serialize($this->dof->workflow('schevents')->calculation_salfactor(
                        $formdata->eventid, true, true));
        // сериализованный объект
        $event->rhours         = $this->dof->workflow('schevents')->calculation_salfactor(
                $formdata->eventid);
        // продолжительность в условных часах
        // обновляем событие в базе
        return $this->dof->storage('schevents')->update($event, $formdata->eventid);
    }

    /** Привязать событие к контрольной точке
     * @todo больше комментариев в коде функции
     *
     * @return bool
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     * @param int $neweventid[optional] - если событие создавалось из формы - то
     *                                       id только что созданного события
     * @param int $newplanid[optional] - id новой контрольной точки, если она была создана
     */
    protected function set_journal_form_event_link($formdata, $neweventid = false, $newplanid = false)
    {
        $event = new stdClass();
        if ( $neweventid )
        {
            $event->id = $neweventid;
        } else
        {
            $event->id = $formdata->eventid;
        }
        if ( $newplanid )
        {
            $event->planid = $newplanid;
        } else
        {
            $event->planid = $formdata->planid;
        }

        return $this->dof->storage('schevents')->update($event);
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и создать из них точку тематического планирования на поток
     *
     * @return int - id новой точки тематического планирования в таблице plans
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function save_journal_form_plan($formdata, $parentids = NULL)
    {
        // получаем объект тематического планироания из формы
        $plan = $this->get_plan_object_from_form($formdata);
        // вставляем собранный объект в базу и возвращаем его id
        if ( $id   = $this->dof->storage('plans')->insert($plan) )
        {// обновим род темы
            if ( $this->dof->storage('planinh')->create_point_links($id, $parentids) )
            {
                return $id;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    /** Собрать данные из формы формы отчета об уроке в журнале
     * и обновить данные о точке тематического планирования
     *
     * @return bool
     * @param object $formdata - объект данных из формы отчета об уроке в журнале
     */
    protected function update_journal_form_plan($formdata, $parentids = NULL)
    {
        // получаем объект тематического планироания из формы
        $plan = $this->get_plan_object_from_form($formdata);
        // обновляем существующую запись и возвращаем результат
        if ( $this->dof->storage('plans')->update($plan) )
        {// обновим род темы
            if ( $this->dof->storage('planinh')->upgrade_point_links($plan->id, $parentids) )
            {
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    /** Создать объект точки тематического планирования из данных формы
     *
     * @return object
     * @param object $formdata
     */
    protected function get_plan_object_from_form($formdata)
    {
        $plan    = new stdClass();
        $cstream = $this->dof->storage('cstreams')->get($formdata->csid);

        $plan->id       = $formdata->planid;
        $plan->linkid   = $formdata->linkid;
        $plan->linktype = $formdata->linktype;
        $plan->name     = $formdata->name;
        // относительная дата начала
        // из даты начала потока вычитаем дату начала занятия
        if ( isset($formdata->event_date) AND $formdata->event_date AND ( ( isset($formdata->create_event) AND $formdata->create_event )
                OR $formdata->eventid ) )
        {
            $plan->reldate = $formdata->event_date - $formdata->begindate;
        } elseif ( isset($formdata->eventid) AND $formdata->eventid AND
                $event = $this->dof->storage('schevents')->get($formdata->eventid) )
        {
            $plan->reldate = $event->date - $formdata->begindate;
        } elseif ( isset($formdata->pinpoint_date) AND $formdata->pinpoint_date )
        {
            $plan->reldate = $formdata->pinpoint_date - $formdata->begindate;
        }
        $plan->type     = $formdata->type;
        $plan->homework = $formdata->homework;
        // время на домашнюю работу - переводим из часов и минут в секунды
        // переводим часы и минуты в секунды
        // @todo сейчас время на домашнее задание задается только в минутах.
        //       если такое решение приживется 
        $homeworkhours  = 0;
        $hoursname      = 'homeworkhoursgroup[hours]';
        $minutesname    = 'homeworkhoursgroup[minutes]';
        //if ( isset($formdata->$hoursname) )
        //{// собираем часы
        //    $homeworkhours += $formdata->$hoursname;
        //}
        if ( isset($formdata->$minutesname) )
        {// собираем минуты
            $homeworkhours += $formdata->$minutesname;
        }
        $plan->homeworkhours  = $homeworkhours;
        // темы созданные из журнала всегда имеют directmap=1
        $plan->directmap      = $formdata->directmap;
        // точная дата начала темы
        $plan->datetheme      = $formdata->begindate;
        $plan->plansectionsid = $formdata->plansectionsid;
        $plan->note           = $formdata->note;
        // шкала наследуется из предмета
        // @todo крайний срок сдачи в этой форме не указывается - возможно в будущем это следует изменить
        // @todo раскоментировать эту строку когда появится возможность указывать номер темы в плане
        // $plan->number
        // @todo когда появится возможность указывать в плане id moodle для синхронизации -
        // раскомментировать это поле
        // $plan->mdlinstance
        // $plan->typesync       =

        return $plan;
    }

    /** Создать объект учебного события из данных формы
     * @todo возможно следует передавать еще один параметр - $planid - если
     * событие одновременно создается и привязывается к контрольной точке. Узнать, возможно ли одновременно
     * @todo когда появится возможность задавать место события - создать переменную place
     *
     * @return object - нужной структуры для таблицы plans
     * @param object $formdata
     */
    protected function get_event_object_from_form($formdata)
    {
        $event = new stdClass();

        $event->form          = $formdata->event_form;
        $event->date          = $formdata->event_date;
        $event->type          = $formdata->event_type;
        $event->teacherid     = $formdata->event_teacherid;
        $event->duration      = $formdata->event_duration;
        $event->appointmentid = $formdata->event_appointmentid;

        if ( isset($formdata->event_appointmentid) AND $formdata->event_appointmentid )
        {// назначение существует
            $status = $this->dof->storage('appointments')->get_field($formdata->event_appointmentid, 'status');
            if ( $status == 'patient' OR $status == 'vacation' )
            {// учитель на больничном не может быть назначен событию
                $event->teacherid     = 0;
                $event->appointmentid = 0;
            }
        }

        $event->ahours    = $formdata->event_ahours;
        $event->cstreamid = $formdata->csid;
        if ( isset($formdata->planid) AND $formdata->planid )
        {//Если КТ уже существует, то привязываем событие к нему
            $event->planid = $formdata->planid;
        }
        // $event->place         = ???

        return $event;
    }

    /** Перенаправить пользователя обратно после сохранения данных из формы журнала
     *
     * @return
     * @param object $eventid
     * @param object $planid
     * @param object $error[optional]
     */
    protected function apply_redirect_after_topic_save($formdata, $eventid, $planid, $success = true)
    {
        $depid                   = optional_param('departmentid', 0, PARAM_INT);
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        //die('function apply_redirect_after_topic_save() IS NOT COMPLETED!');
        //создадим ссылку на разворот журнала
        if ( $success )
        {// успех -  будем выводить ЭТО
            $message = 1;
        }
        $path  = $this->dof->url_im('journal', '/group_journal/index.php#jm' .
                $this->dof->storage('schevents')->get_field($eventid, 'date'), array('csid'         => $formdata->csid,
            'planid'       => $planid,
            'eventid'      => $eventid,
            'departmentid' => $addvars['departmentid']));
        $path2 = $this->dof->url_im('journal', '/group_journal/topic.php', array_merge(array('csid'    => $formdata->csid,
            'planid'  => $planid,
            'eventid' => $eventid,
            'message' => $message), $addvars));


        $link    = '<a href="' . $path . '">' . $this->dof->get_string('backward', 'journal') . '</a>';
        $strlink = '<br />' . $link;

        //печатаем результат работы
        $this->rez = '<p style="text-align:center; color:green;"><b>' .
                $this->dof->get_string('save_true', 'journal') . '</b>' . $strlink . '</p>';

        redirect($path2);
    }
}

/** 
 * Базовый класс для отображения данных по журналу учебного процесса(предмето-класса)
 * 
 * Подготовка "сырых" данных для различных таблиц и форм журнала учебного процесса
 */
class dof_im_journal_rawdata
{
    /**
     * @var dof_control
     */
    var $dof;
    
    /**
     * Идентификатор учебного процесса
     * 
     * @var integer
     */
    var $csid;

    /** 
     * Конструктор - определяет с каким учебным потоком будет вестись работа
     * 
     * @param dof_control - глобальный объект Деканата $DOF 
     * @param int $csid - ID учебного процесса(предмето-класса)
     */

    function __construct(dof_control $dof, $csid)
    {
        $this->dof  = $dof;
        $this->csid = (int)$csid;
    }

    /** 
     * Получить ID учебного процесса(предмето-класса)
     * 
     * @return integer
     */
    protected function get_cstreamid()
    {
        return $this->csid;
    }

    /** 
     * Получить список доступных статусов планов
     * 
     * @return array  
     */
    protected function get_planstatuses()
    {
        // @TODO - Сформировать связь с workflow
        return ['active', 'fixed', 'checked', 'completed'];
    }

    /** 
     * Получить список статусов с которыми будут извлекаться события из таблицы schevents
     * 
     * @return array|NULL - Массив статусов или NULL, если не требуется фильтрация по статусам
     */
    protected function get_eventstatuses()
    {
        return NULL;
    }

    /** 
     * Получить контрольные точки учебного процесса
     * 
     * Получить массив объектов, содержащие связь между тематическим планом и событием
     * 
     * @param bool - 
     * @return array - Массив контрольных точек учебного процесса
     */
    protected function get_checkpoints($emevent = true)
    {
        if ( ! $this->csid )
        {// Идентификатор учебного процесса не получен
            return FALSE;
        }
        
        // Получить список статусов для фильтрации тематического плана
        $planstatuses  = $this->get_planstatuses();
        // Получить список статусов для фильтрации событий
        $eventstatuses = $this->get_eventstatuses();
        // Получение контрольных точек учебного процесса
        $checkpoints = $this->dof->storage('schevents')->
            get_mass_date($this->csid, $eventstatuses, $planstatuses, $emevent);
        
        // Вернуть контрольные точки
        return $checkpoints;
    }

    /** 
     * Получить все подписки на учебный процесс
     * 
     * @return array|bool - Массив подписок на учебный процесс или false
     */
    protected function get_cpassed($showjunk = false)
    {
        $list = $this->dof->storage('cpassed')->get_records([
            'cstreamid' => $this->csid, 
            'status' => array_keys($this->dof->workflow('cpassed')->get_register_statuses($showjunk))
        ]);
        if ( ! $list  )
        {// Подписки не найдены
            return FALSE;
        }
        
        // Сортировка по имени
        usort($list, array('dof_im_journal_rawdata', 'sortapp_by_sortname2'));
        
        return $list;
    }

    /**
     * Получить все подписки на учебный процесс для указанной подписки на программу
     * 
     * @return array массив записей из таблицы cpassed или false
     */
    protected function get_cpassed_programmsbc($programmsbcid, $showjunk = false)
    {
        $params = [
            'cstreamid' => $this->csid,
            'programmsbcid' => $programmsbcid,
            'status' => array_keys($this->dof->workflow('cpassed')->get_register_statuses($showjunk))
        ];
        $list = $this->dof->storage('cpassed')->get_records($params, 'begindate ASC');
        if ( ! $list )
        {// Подписки на учебный процесс не найдены
            return FALSE;
        }
        return $list;
    }

    /** 
     * Получить всех студентов указанного учебного потока
     * 
     * @return array массив записей из таблицы persons или false
     */
    protected function get_students()
    {
        $studentids  = [];
        
        // Получаем подписки на учебный процесс
        $listcpassed = $this->get_cpassed();
        if ( ! $listcpassed )
        {// Подписок не найдено
            return false;
        }
        // Перебор всех подписок и создание из них строки для запроса
        foreach ( $listcpassed as $cpassed )
        {
            $studentids[] = $cpassed->studentid;
        }
        
        return $this->dof->storage('persons')->get_records(['id' => $studentids], 'lastname');
    }

    /** 
     * Получить имя указанной контрольной точки
     * 
     * @param int $planid - ID записи в таблице plans или false
     * 
     * @return string, если имя есть, или false, если оно не указано
     */
    protected function get_checkpoint_name($planid)
    {
        return '';
    }

    /** 
     * Получить ID преподавателя учебного процесса
     * 
     * @return int - ID преподавателя или false
     */
    protected function get_teacherid()
    {
        $cstream = $this->dof->storage('cstreams')->get($this->csid);
        if ( ! $cstream )
        {// Учебный процесс не найден
            return FALSE;
        }
        
        return $cstream->teacherid;
    }

    /**
     * Функция сравнения двух объектов из таблицы persons по полю sortname
     * 
     * @param object $person1 - запись из таблицы persons
     * @param object $person2 - другая запись из таблицы persons
     * 
     * @return -1, 0, 1 в зависимости от результата сравнения
     */
    public function sortapp_by_sortname2($person1, $person2)
    {
        return strnatcmp($this->dof->storage('persons')->get_field($person1->studentid, 'sortname'),
                         $this->dof->storage('persons')->get_field($person2->studentid, 'sortname'));
    }
}

/*
 * Класс для проверки и обработки оценок из формы
 */
class dof_im_journal_process_gradesform extends dof_im_journal_rawdata
{

    /**
     * @var dof_control object
     */
    //var $dof;
    /**
     * @var stdClass object все оценки и сопутствующая им информация
     */
    protected $gradedata;
    //  protected $gradeactions;
    /**
     * @var array непроверенные данные пришедшие из массива $_POST.
     * Используются только для составления повторного запроса на сохранение данных.
     */
    private $mypost;

    /** Конструктор класса. Осуществляет все проверки и записывает данные во внутреннее поле.
     * 
     * @param dof_control $dof - объект $DOF
     * @param object $gradedata - массив $_POST из формы
     */
    function __construct($dof, $gradedata)
    {
        // Обьект для хранения отфильтрованных данных
        $result = new stdClass();
        
        // начинаем проверку скалярных данных
        $scalars                   = $this->check_scalar_data($gradedata);
        $result->teacherid         = $scalars->teacherid;
        $result->eventid           = $scalars->eventid;
        $result->planid            = $scalars->planid;
        $result->csid              = $scalars->csid;
        $result->anchor            = $scalars->anchor;
        $result->conflictsresolved = $scalars->conflictsresolved;
        
        // вызываем родительский конструктор
        parent::__construct($dof, $result->csid);
        // проверяем массивы
        // проверим данные об отсутствующих учениках
        $result->away      = $this->check_away_array($gradedata);
        // проверим массив оценок 
        $result->grades    = $this->check_grades_array($gradedata);
        // проверим массив идентификаторов cpassed
        $result->cpassedid = $this->check_cpassed_array($gradedata);
        // узнаем id оценок для изменения их статуса
        $result->gradeid   = $this->check_gradeid_array($gradedata);
        // Нормализация комментариев
        $result->comment = $this->check_comments($gradedata);
        // определяем тип действия, которое надо совершить
        if ( isset($gradedata['save']) )
        {// сохраняем данные
            $result->action = 'save';
        } elseif ( isset($gradedata['save_and_continue']) )
        {// сохранить и продолжить
            $result->action = 'save_and_continue';
        } elseif ( isset($gradedata['restore']) )
        {// восстановить исходные значения
            $result->action = 'restore';
        } else
        {// хз
            $result->action = 'ERROR';
        }
        // форма чексбоксa
        if ( isset($gradedata['box']) )
        {
            $result->box = 'true';
        }

        // запишем в итоговую переменную результат после проверок
        $this->gradedata = $result;

        // запишем исходный массив в поле объекта, если потом понадобится еще раз 
        // отправить данные после подтверждения
        $this->mypost = $gradedata;
    }

    /** Обработать все данные, пришедшие из формы: 
     * установить посещаемость, выставить оценки 
     * и сформировать приказы
     * @return false в случае неудачи. 
     * В случае успеха производит редирект на страницу журнала
     * @todo сделать обработку ошибок через exceptions
     */
    public function process_form()
    {
        if ( $this->gradedata->action == 'restore' )
        {// если нажата кнопка "восстановить", то не переходим к сохранению оценок
            $this->do_redirect();
        }
        if ( !$this->gradedata->teacherid )
        {// нет события - значит данные о посещаемости вообще не посылались
            return false;
        }
        // проверяем, установлено ли у кого-нибудь из учеников одновременно "н" и оценка
        $notices = $this->check_double_marks();

        if ( $notices )
        {// неточности есть - составим сообщение и ссылки
            $this->show_full_notice($notices);
            //типа все хорошо
            return true;
        } else
        {// выполняем остальные действия только если нет предупреждений 
            $this->save_comments();
            // формируем приказ';
            if ( !empty($this->gradedata->grades) )
            {
                if ( !$this->generate_order_grades() )
                {// ошибка  при создании приказа оценок';
                    return false;
                }
            }
            if ( !$this->generate_order_presences() )
            {// ошибка  при создании приказа присутствия';
                return false;
            }
            // производим редирект, если все успешно
            $this->do_redirect();
        }
    }

    /**
     * Сохранить комментарии по посещаемости
     * 
     * @return bool - Результат сохранения комментариев
     */
    protected function save_comments()
    {
        $comments = $this->gradedata->comment;
        $result = true;
        if ( ! empty($comments) )
        {// Требуется обновление комментариев
            foreach ( $comments as $complaxid => $text )
            {// Обработка каждого комментария
                // Получение идентификаторов
                $ids = explode('_', $complaxid);
                // Разбиение набора идентификаторов
                $commentid = $ids[0];
                $precenceid = $ids[1];
                
                // Сохранение комментария
                $comment = new stdClass();
                $comment->plugintype = 'storage';
                $comment->plugincode = 'schpresences';
                $comment->objectid = $precenceid;
                $comment->code = 'public';
                $comment->text = $text;
                $comment->personid = $this->gradedata->teacherid;
                if ( $commentid > 0 )
                {// Идентификатор указан
                    $comment->id = $commentid;
                }
                $result = ( $result & $this->dof->storage('comments')->save($comment) );
            }
        }
        return $result;
    }
    
    /** 
     * Проверить установленные комментарии
     *
     * @param array $gradedata - данные об оценках
     * 
     * @return array - Отфильтрованные данные
     */
    private function check_comments($gradedata)
    {
        $comments = [];
        if ( isset($gradedata['comment']) && is_array($gradedata['comment']) )
        {// Комментарии указаны
            foreach ( $gradedata['comment'] as $complaxid => $text )
            {// Обработка каждого комментария
                // Получение идентификаторов
                $ids = explode('_', $complaxid);
                if ( count($ids) != 3 )
                {// Невалидный набор идентификаторов
                    continue;
                }
                // Разбиение набора идентификаторов
                $commentid = $ids[0];
                $precenceid = $ids[1];
                $cpassedid = $ids[2];
                // Проверка на валидность подписки
                if ( ! isset($gradedata['cpassedid'][$cpassedid]) )
                {// Подписка не найдена
                    continue;
                }
                // Проверка посещаемости
                if ( ! ( $precenceid > 0 ) )
                {// Посещаемость не указана
                    continue;
                } else
                {// Проверка соответствия посещаемости
                    $presence = $this->dof->storage('schpresences')->get($precenceid);
                    if ( ! isset($presence->eventid) || $presence->eventid != $gradedata['eventid'] )
                    {// Посещаемость не принадлежит текущему событию
                        continue;
                    }
                }
                // Очистка от лишних пробелов
                $text = trim($text);
                // Проверка на наличие комментария
                if ( empty($text) )
                {// Комменатрий не указан
                    if ( $commentid > 0 )
                    {// Удалить комментарий
                        $this->dof->workflow('comments')->change($commentid, 'deleted');
                    }
                    continue;
                }
                // Проверка на изменение комментария
                if ( $commentid > 0 )
                {// Комментарий редактируется
                    // Получить содержимое комментария
                    $savedcomment = $this->dof->storage('comments')->get_field($commentid, 'text');
                    if ( $savedcomment == $text )
                    {// Комментарий не отличается от сохраненного - обновление не требуется
                        continue;
                    }
                }
                
                // Добавление комментария в итоговый массив
                $comments[$complaxid] = $text;
            }
        }
        return $comments;
    }
    
    /** Сформировать приказ об изменении состояния учебного потока
     * 
     * @return true or false
     */
    protected function generate_order_grades()
    {
        $result       = true;
        if ( !$departmentid = $this->dof->storage('cstreams')
                              ->get_field($this->csid, 'departmentid') )
        {//не получили id подразделения';
            return false;
        }

        //print_object($this->gradedata);
        $actions = array_unique($this->gradeactions);

        // определяем тип действия, которое нужно совершить с оценкой
        foreach ( $actions as $action )
        {
            switch ( $action )
            {// @todo предусмотреть возможность обновления оценки
                case 'set_grade':
                    if ( !$this->order_set_grade($departmentid) )
                    {// не удалось выполнить одну из операций  - запомним это
                        $result = false;
                    }
                    break;
                case 'delete_grade':
                    if ( !$this->order_delete_grade($departmentid) )
                    {// не удалось выполнить одну из опероаций  - запомним это
                        $result = false;
                    }
                    break;
            }
        }

        return $result;
    }

    /** Формирует приказ - установить оценку
     * 
     * @return bool true в случае успеха и false в случае неудачи
     * @param int $departmentid
     */
    private function order_set_grade($departmentid)
    {
        //подключаем методы работы с приказом
        $order                  = $this->dof->im('journal')->order('set_grade');
        //создаем объект для записи
        $orderobj               = new stdClass();
        //сохраняем автора приказа
        $orderobj->ownerid      = $this->gradedata->teacherid;
        //подразделение, к которому он относится        
        $orderobj->departmentid = $departmentid;
        //дата создания приказа
        $orderobj->date         = dof_im_journal_get_date(time());
        //добавляем данные, о которых приказ
        $orderobj->data         = $this->get_grades_fororder('set_grade');
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // подписываем приказ
        $order->sign($this->gradedata->teacherid);

        //проверяем подписан ли приказ
        if ( !$order->is_signed() )
        {//приказ не подписан
            return false;
        }
        //print 'исполняем приказ';
        if ( !$order->execute() )
        {//не удалось исполнить приказ
            return false;
        }
        return true;
    }

    /** Формирует приказ - удалить оценку
     * 
     * @return bool true в случае успеха и false в случае неудачи 
     * @param int $departmentid
     */
    private function order_delete_grade($departmentid)
    {
        //подключаем методы работы с приказом
        $order                  = $this->dof->im('journal')->order('delete_grade');
        //создаем объект для записи
        $orderobj               = new stdClass();
        //сохраняем автора приказа
        $orderobj->ownerid      = $this->gradedata->teacherid;
        //подразделение, к которому он относится        
        $orderobj->departmentid = $departmentid;
        //дата создания приказа
        $orderobj->date         = dof_im_journal_get_date(time());
        //добавляем данные, о которых приказ
        $orderobj->data         = $this->get_grades_fororder('delete_grade');
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // подписываем приказ
        $order->sign($this->gradedata->teacherid);
        //проверяем подписан ли приказ
        if ( !$order->is_signed() )
        {//приказ не подписан
            return false;
        }
        //исполняем приказ';
        if ( !$order->execute() )
        {//не удалось исполнить приказ
            return false;
        }
        return true;
    }

    /** Формирует приказ - изменить оценку
     * 
     * @return bool true в случае успеха и false в случае неудачи
     * @param int $departmentid
     */
    private function order_update_grade($departmentid)
    {
        
    }

    /** Сформировать приказ об изменении состояния учебного потока
     * 
     * @return true or false
     */
    protected function generate_order_presences()
    {
        if ( !$departmentid = $this->dof->storage('cstreams')->
                get_field($this->csid, 'departmentid') )
        {//не получили id подразделения
            return false;
        }
        //подключаем методы работы с приказом
        $order                  = $this->dof->im('journal')->order('presence');
        //создаем объект для записи
        $orderobj               = new stdClass();
        //сохраняем автора приказа
        $orderobj->ownerid      = $this->gradedata->teacherid;
        //подразделение, к которому он относится        
        $orderobj->departmentid = $departmentid;
        //дата создания приказа
        $orderobj->date         = dof_im_journal_get_date(time());
        //добавляем данные, о которых приказ
        $orderobj->data         = $this->get_presents_fororder();
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // подписываем приказ
        $order->sign($this->gradedata->teacherid);
        //проверяем подписан ли приказ
        if ( !$order->is_signed() )
        {//приказ не подписан
            return false;
        }
        //исполняем приказ
        if ( !$order->execute() )
        {//не удалось исполнить приказ
            return false;
        }
        return true;
    }

    /** Отметить посещаемость. Заносит в базу данных всю информацию о посещаемости
     * для текущего события. 
     * 
     * @return true or false
     */
    protected function store_attendance()
    {
        if ( !$this->gradedata->eventid )
        {// если обрабатываемая контрольная точка не привязана к конкретной дате, то 
            // нет необходимости в отметках посещаемости
            return true;
        }
        return $this->dof->storage('schpresences')->save_present_students($this->gradedata->eventid, null, $this->gradedata->away);
    }

    /** Обновить страницу после изменения оценок. Перенаправляет пользователя на страницу 
     * редактирования/просмотра журнала, в зависимости от нажатой кнопки
     * 
     * @return Эта функция не возвращает значений 
     */
    private function do_redirect()
    {
        $depid                   = optional_param('departmentid', 0, PARAM_INT);
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        // Чтобы не возникал 'You should really redirect before you start page output'
        ob_clean();
        switch ( $this->gradedata->action )
        {// в зависимости от переданного значения перенаправляем на разные страницы
            case 'save': // сохранить
                redirect($this->dof->url_im('journal', '/group_journal/index.php?csid=' . $this->gradedata->csid
                                . '&departmentid=' . $addvars['departmentid'] . '#jm' . $this->gradedata->anchor), '', 0);
                break;
            case 'save_and_continue': // сохранить и продолжить
                redirect($this->dof->url_im('journal', '/group_journal/index.php?csid=' . $this->gradedata->csid .
                                '&planid=' . $this->gradedata->planid . '&eventid=' . $this->gradedata->eventid
                                . '&departmentid=' . $addvars['departmentid'] . '#jm' . $this->gradedata->anchor), '', 0);
                break;
            case 'restore': // восстановить
                redirect($this->dof->url_im('journal', '/group_journal/index.php?csid=' . $this->gradedata->csid .
                                '&planid=' . $this->gradedata->planid . '&eventid=' . $this->gradedata->eventid
                                . '&departmentid=' . $addvars['departmentid'] . '#jm' . $this->gradedata->anchor), '', 0);
                break;
            default: // по умолчанию возвращаемся на страницу журнала
                redirect($this->dof->url_im('journal', '/group_journal/index.php?csid=' . $this->gradedata->csid
                                . '&departmentid=' . $addvars['departmentid'] . '#jm' . $this->gradedata->anchor), '', 0);
                break;
        }
    }
    
    /**
     * Функции проверки данных
     */

    /** Вызывается из конструктора. проверяет массив на соответствие стандарту передачи данных
     * 
     * @param array $gradedata - массив с данными из формы
     * @return array - проверенный массив или false а случае неучачи
     */
    private function check_away_array($gradedata)
    {
        $away = array();

        $cpasseds = $this->get_cpassed();
        if ( is_array($cpasseds) AND isset($gradedata['eventid']) AND $gradedata['eventid'] )
        {// если получили список учеников, и есть событие - начинаем его обработку
            $date = $this->dof->storage('schevents')->get_field($gradedata['eventid'], 'date');
            foreach ( $cpasseds as $cpassed )
            {// перебираем студентов
                if ( isset($gradedata['noaway']) AND array_key_exists($cpassed->id, $gradedata['noaway']) )
                {// если н/о и есть запись то удаляем её
                    $params             = array();
                    $params['eventid']  = $gradedata['eventid'];
                    $params['personid'] = $cpassed->studentid;
                    if ( $schpresent         = $this->dof->storage('schpresences')->get_record($params) )
                    {// нашли запись - удаляем 
                        $this->dof->storage('schpresences')->delete($schpresent->id);
                    }
                    continue;
                }
                if ( isset($gradedata['away']) AND array_key_exists($cpassed->id, $gradedata['away']) )
                //OR  (isset($gradedata['noaway']) AND array_key_exists($student->id, $gradedata['noaway'])) )
                {// если id ученика есть в массиве отстутствующих - запишем, что его не было
                    $away[$cpassed->id] = 0;
                } else
                {// в противном случае считаем, что он был
                    $away[$cpassed->id] = 1;
                }
            }
        }
        // возвращаем массив нужной для структуры (для таблицы с посещаемостью)
        return $away;
    }

    /** Проверяет переданный массив оценок
     * 
     * @return проверенный массив с оценками вида [id_ученика] => Оценка
     * @param array $gradedata - массив $_POST из формы
     */
    private function check_grades_array($gradedata)
    {
        $grades = array();

        $cpasseds = $this->get_cpassed();
        if ( is_array($cpasseds) )
        {// начинаем егог обработку
            foreach ( $cpasseds as $cpassed )
            {// записываем данные с проверкой
                if ( isset($gradedata['editgrades'][$cpassed->id]) )
                {// оценка есть
                    if ( isset($gradedata['noaway']) AND array_key_exists($cpassed->id, $gradedata['noaway']) )
                    {// есть "н/о" удаляем оценку тогда 
                        $grades[$cpassed->id]             = addslashes('0');
                        $this->gradeactions[$cpassed->id] = 'delete_grade';
                    } elseif ( ($gradedata['editgrades'][$cpassed->id] <> '')
                            AND ( (isset($gradedata['noaway']) AND ! array_key_exists($cpassed->id, $gradedata['noaway']))
                            OR ( !isset($gradedata['noaway']))) )
                    {// запомним, что оценку надо установить
                        $grades[$cpassed->id]             = addslashes($gradedata['editgrades'][$cpassed->id]);
                        // @todo предусмотреть вариант обновления оценки
                        $this->gradeactions[$cpassed->id] = 'set_grade';
                    } else
                    {// запомним, что оценку надо удалить
                        $grades[$cpassed->id]             = addslashes($gradedata['editgrades'][$cpassed->id]);
                        $this->gradeactions[$cpassed->id] = 'delete_grade';
                    }
                }
            }
        }
        return $grades;
    }

    /** Проверить переданный из формы массив идентификаторов cpassed
     * 
     * @return array проверенный массив идентификаторов
     * @param array $gradedata - массив $_POST
     */
    private function check_cpassed_array($gradedata)
    {
        $cp       = array();
        $cpasseds = $this->get_cpassed();
        if ( is_array($cpasseds) )
        {// собираем в массив ключи - id учеников
            foreach ( $cpasseds as $cpassed )
            {// перебираем всех учеников 
                if ( isset($gradedata['cpassedid'][$cpassed->id]) AND
                        is_numeric($gradedata['cpassedid'][$cpassed->id]) )
                {// проверяем тип ключа и значение
                    $cp[$cpassed->id] = $gradedata['cpassedid'][$cpassed->id];
                }
            }
        }
        // возвращаем проверенный массив идентификаторов,
        // или пустой массив в случае былинного отказа
        return $cp;
    }

    /** Проверить массив идентификаторов оценок
     * 
     * @return  array проверенный массив идентификаторов
     * @param array $gradedata - массив $_POST
     */
    private function check_gradeid_array($gradedata)
    {
        $gradeid  = array();
        $cpasseds = $this->get_cpassed();
        if ( is_array($cpasseds) )
        {// собираем в массив ключи - id учеников
            foreach ( $cpasseds as $cpassed )
            {// перебираем всех учеников 
                if ( isset($gradedata['noaway']) AND array_key_exists($cpassed->id, $gradedata['noaway']) )
                {// есть "н/о" удаляем оценку тогда 
                    $gradeid[$cpassed->id] = $gradedata['gradeid'][$cpassed->id];
                } elseif ( (isset($gradedata['gradeid'][$cpassed->id]) AND is_numeric($gradedata['gradeid'][$cpassed->id]) ) )
                {// проверяем тип ключа и значение
                    $gradeid[$cpassed->id] = $gradedata['gradeid'][$cpassed->id];
                } else
                {
                    $gradeid[$cpassed->id] = 0;
                }
            }
        }
        // возвращаем проверенный массив идентификаторов,
        // или пустой массив в случае ошибки
        return $gradeid;
    }

    /** Проверяет все идентификаторы, пришедшие из формы
     * @todo оптимизировать работу этой функции
     * @return object - Проверенные скалярные данные
     * @param array $gradedata - массив $_POST
     */
    private function check_scalar_data($gradedata)
    {
        // создаем объект для итоговых данных
        $result = new stdClass();
        // перечисляем все параметры, которые нужно проверить
        $vars = array('planid', 'eventid', 'teacherid', 'csid', 'conflictsresolved', 'anchor');
        // проверяем переменную
        foreach ( $vars as $var )
        {
            
            if ( isset($gradedata[$var]) AND is_numeric($gradedata[$var]) )
            {// не получили planid
                $result->$var = $gradedata[$var];
            } else
            {
                $result->$var = false;
            }
        }
        // возвращаем объект с данными
        return $result;
    }

    /** Получить массив оценок для формирования приказа
     * 
     * @return 
     * @param object $type
     */
    private function get_grades_fororder($type)
    {
        $obj            = new stdClass;
        $obj->date      = dof_im_journal_get_date(time());
        $obj->planid    = $this->gradedata->planid;
        $obj->teacherid = $this->gradedata->teacherid;

        foreach ( $this->gradedata->grades as $stid => $grade )
        {
            if ( $type == $this->gradeactions[$stid] )
            {// если тип действия над проверяемой оценкой совпадает с заявленной
                // то добавляем ее в массив
                switch ( $type )
                {// тип объекта в массиве зависит от действия, которое будет произведено над оценкой в приказе
                    case 'set_grade':  // выставить оценку
                        $mas[$stid] = array('grade' => $grade,
                            'cpassedid' => $stid,
                            'status'    => 'tmp');
                        break;
                    case 'delete_grade': // удалить оценку
                        $mas[$stid] = array('grade' => $grade,
                            'cpassedid' => $stid,
                            'id'        => $this->gradedata->gradeid[$stid]);
                        break;
                }
            }
        }
        ksort($mas);
        $obj->grades = $mas;
        return $obj;
    }

    private function get_presents_fororder()
    {
        $obj           = new stdClass;
        $obj->eventid  = $this->gradedata->eventid;
        $obj->presents = $this->gradedata->away;
        if ( isset($this->gradedata->box) )
        {
            $obj->box = $this->gradedata->box;
        }
        return $obj;
    }

    /** Проверяет наличие одновременно отмеченной буквы "н" и оценки для ученика
     * 
     * @return array массив id учеников, для которых одновременно выставлена оценка и статуст "отсутствовал"
     * или false, если таких учеников нет
     */
    private function check_double_marks()
    {
        $result = array();
        if ( !$this->gradedata->eventid )
        {// нет события - значит данные о посещаемости вообще не посылались
            return false;
        }
        if ( isset($this->gradedata->conflictsresolved) AND $this->gradedata->conflictsresolved == 1 )
        {// если мы просто второй раз получаем данные с уже разрешенными конфликтами
            return false;
        }
        // собираем все id в массив
        $cpasseds = $this->get_cpassed();
        if ( is_array($cpasseds) )
        {
            foreach ( $cpasseds as $cpassed )
            {// просматриваем все выставленные оценки
                if (       $this->gradedata->grades[$cpassed->id]      AND
                     (bool)$this->gradedata->grades[$cpassed->id]      AND
                     isset($this->gradedata->away[$cpassed->id])       AND 
                           $this->gradedata->away[$cpassed->id]   == 0    )
                {// если есть отметка о посещаемости и оценка то помещаем такого ученика в массив 
                    $result[] = $cpassed->id;
                }
            }
        }
        if ( empty($result) )
        {
            return false;
        } else
        {
            return $result;
        }
    }

    /** Создает сообщения для случая, когда одновременно проставлен статус "отсутствовал" и оценка
     * 
     * @param array $cpasseds
     * @return string html-код сообщения, или false в случае ошибки 
     */
    private function create_notice_message($cpasseds)
    {
        $result = '';
        if ( !is_array($cpasseds) )
        {// неверный формаи исходных данных
            return false;
        }
        $result .= $this->dof->get_string('doble_marks_notice', 'journal');
        $result .= "<ul>\n";
        foreach ( $cpasseds as $cpid )
        {// перебираем массив учеников для составления сообщения
            $stid    = $this->dof->storage('cpassed')->get_field($cpid, 'studentid');
            $student = $this->dof->storage('persons')->get($stid);
            if ( $student )
            {// выводим фамилию и имя ученика как элемент списка
                $result .= '<li><b>' . $student->lastname . ' ' . $student->firstname . "</b></li>\n";
            } else
            {// нарушена целостности БД - ошибка
                error($this->dof->get_string('no_student_in_base_with_id', 'journal') . '=' . $stid);
                break;
            }
        }
        $result .= "</ul>\n";
        $result .= $this->dof->get_string('save_data_question', 'journal');
        return $result;
    }

    /** Используется для составления массива $_POST в случае с подтверждением данных 
     * 
     * @return array массив для корректного выставления оценок после переадресации
     */
    private function create_post_again()
    {
        $result = array();
        // dirty hack для функции moodle, отображающей сообщения: она некорректно работает с многомерным $_POST
        // приводим значения элементов к нужному виду 
        foreach ( $this->mypost as $postkey => $postvalue )
        {
            if ( is_array($postvalue) )
            {
                foreach ( $postvalue as $elkey => $elvalue )
                {// делаем из двумерного массива одномерный, иначе не обработается
                    $result[$postkey . '[' . $elkey . ']'] = $elvalue;
                }
            } else
            {// скалярные данные записываем как есть
                $result[$postkey] = $postvalue;
            }
        }
        // делаем пометку о том, что учитель дал подтверждение своему выбору
        $result['conflictsresolved'] = 1;
        return $result;
    }

    /** Отображает предупреждение с заголовком и оформлением
     * 
     * @return null эта функция не возвращает значений
     * @param array $notices - id учеников, для которых нужно сформировать замечания
     */
    private function show_full_notice($notices)
    {
        $depid                   = optional_param('departmentid', 0, PARAM_INT);
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        $message                 = $this->create_notice_message($notices);
        // ссылка при нажатии на "да"
        $linkyes = $this->dof->url_im('journal', '/group_journal/process_grades.php', 
                array_merge(array('csid' => $this->csid, 
                      'planid' => $this->gradedata->planid, 
                      'eventid' => $this->gradedata->eventid),$addvars));
        // ссылка при нажатии на "нет" - возвращаем ся на страницу с оценками
        $linkno  = $this->dof->url_im('journal','/group_journal/index.php?
                   csid='.$this->gradedata->csid.
                   '&planid='.$this->gradedata->planid.
                   '&eventid='.$this->gradedata->eventid.
                   '#jm'.$this->gradedata->anchor,$addvars);
        // формируем массив для корректной передачи данных
        $optionsyes = $this->create_post_again();
        // распечатвем заголовок
//        $this->dof->modlib('nvg')->print_header(NVG_MODE_PAGE);
        // @todo предусмотреть возможность сохранения оценок после нажатия на кнопку "нет"
        $this->dof->modlib('widgets')->notice_yesno($message, $linkyes, $linkno, $optionsyes);
        // выводим нижнюю часть страницы
//        $this->dof->modlib('nvg')->print_footer(NVG_MODE_PAGE);
    }
}

/*
 * Класс для обработки посещаемости 
 */

class dof_im_journal_presence
{

    /**
     * @var dof_control
     */
    protected $dof;
    private $csid;
    protected $mas;

    function __construct($dof, $csid)
    {
        $this->dof  = $dof;
        $this->csid = $csid;
    }

    /** Получить cstreamid (id учебного потока) по cstreamlinkid из таблицы cstreamlinks
     * @return mixed int - id учебного потока или bool false если такого потока нет
     */
    protected function get_cstreamid()
    {
        // получаем id учебного потока
        //return $this->dof->storage('cstreamlinks')->get_field($this->cslid, 'cstreamid');
        return $this->csid;
    }

    /** Находит всех студентов учебного процесса
     * @return array - список студентов
     */
    private function get_students()
    {
        if ( !$csid = $this->get_cstreamid() )
        {
            return false;
        }
        $cstreams = $this->dof->storage('cpassed')->get_cstream_students($csid, 'active');
        //print_object($cstreams);die;
        return $this->dof->storage('persons')->get_list_by_list($cstreams, 'studentid');
    }

    /** Проверяет принадлежность студента к учебному процессу
     * @param int $stid - id студента
     * @return bool
     */
    private function check_student($stid)
    {
        $students = $this->get_students();
        $presence = false;
        foreach ( $students as $student )
        {
            if ( $student->id == $stid )
            {
                $presence = true;
            }
        }
        return $presence;
    }

    /** Формирует массив присутствия студентов
     * @return array
     */
    public function presence_students()
    {
        $students = $this->get_students();
        foreach ( $students as $student )
        {
            $this->mas[$student->id] = 1;
        }
        return $this->mas;
    }

    /** Формирует массив отсутствующих студентов
     * @param array $away - отсутствующие студенты
     * @return array
     */
    public function absence_students($away)
    {
        $away = array_keys($away);
        foreach ( $away as $stid )
        {
            if ( $this->check_student($stid) )
            {
                $this->mas[$stid] = 0;
            }
        }
        return $this->mas;
    }
}

?>