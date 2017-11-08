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
 * библиотека, для вызова из веб-страниц, подключает DOF.
 */ 

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Добавление общих GET параметров плагина
$addvars['ageid'] = optional_param('ageid', 0, PARAM_INT);
// режим отображения
$addvars['display']    = optional_param('display', null, PARAM_ALPHANUM);
// id интервала времени
$addvars['intervalid'] = optional_param('intervalid', null, PARAM_INT);
// день недели
$addvars['daynum']     = optional_param('daynum', null, PARAM_INT);
// тип недели
$addvars['dayvar']     = optional_param('dayvar', null, PARAM_INT);
// форма урока
$addvars['form']     = optional_param('form', 'all', PARAM_ALPHANUM);

/** Класс для отображения данных расписания
 * 
 */
class dof_im_schedule_display
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $data; // данные для построения таблицы шаблона
    private $rowclasses; // css-класс для отрисовки одной строки таблицы
    private $departmentid; // подразделение
    private $ageid; // период
    private $addvars; // набор параметров, которые мы приплюсовываем к сылкам
    
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @param int $departmentid - id подразделения в таблице departments
     * @param array $addvars - массив get-параметров для ссылки
     * @access public
     */
    public function __construct($dof,$departmentid,$addvars)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        $this->departmentid = $departmentid;
        $this->ageid = $addvars['ageid'];
        $this->addvars       = $addvars;
    }
   	/** Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * @return string
     * @access private
	 */
	private function get_im()
	{
		return 'schedule';
	}

    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * @todo не использует print_table, т.к выводит вертикальную таблицу
     * @param int $id - id шаблона из таблицы schtemplates
     * @return string
     */
    public function get_table_one($id)
    {
        $table = new stdClass();
        if ( ! $template = $this->dof->storage('schtemplates')->get($id))
        {// не нашли шаблон - плохо
            return '';
        }
        // получаем подписи с пояснениями
        $descriptions = $this->get_header('view');
        $data = $this->get_string_full($template);
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.current(each($descriptions)).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получает строку таблицы для отображения списка шаблонов
     * @param object $$obj - объект шаблона из таблицы schtemplates
     * @return array
     */
    private function get_string_full($obj)
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->departmentid;
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;
        $string   = array();
        // имя предмето-класса
        $cstream = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'name');
        if ( $this->dof->storage('cstreams')->is_access('view',$obj->cstreamid) )
        {// ссылка на просмотр предмета
            $cstream = '<a href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.$obj->cstreamid,$outadd).'>'.
                        $cstream.'</a>';
        }
        $string[] = $cstream;   
        $hours    = floor($obj->begin / 3600);
        $minutes  = floor(($obj->begin - $hours * 3600) / 60);
        $string[] = $this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M")
                    .' ('.dof_usertimezone().')'; //время 
        $string[] = $obj->duration/60; // продолжительность - выводим в мин 
        // предмет
        $pitemid  = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'programmitemid');
        $pitem = $this->dof->storage('programmitems')->get_field($pitemid,'name').' ['.
                    $this->dof->storage('programmitems')->get_field($pitemid,'code').']';
        if ( $this->dof->storage('programmitems')->is_access('view',$pitemid) )
        {// ссылка на просмотр предмета
            $pitem = '<a href='.$this->dof->url_im('programmitems','/view.php?pitemid='.$pitemid,$outadd).'>'.
                        $pitem.'</a>';
        }
        $string[] = $pitem;   
        $appointid = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'appointmentid');
        if ( $appointid )
        {// учителя вычисляем из сотрудника
            $person = $this->dof->storage('appointments')->get_person_by_appointment($appointid);
            $link = '';
            
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {// пользователь может просматривать шаблоны
                $link .= ' <a id="view_schtemplate_teacher_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid='.$person->id,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            if ( $this->dof->im('journal')->is_access('view_schevents'))
            {// пользователь может просматривать шаблоны
                $link .= '<a id="view_schtemplate_person_week_'.$obj->id.'" href='.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$person->id.
                        '&date_to='.time().'&date_from='.time(),$outadd).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_events.png').
                        '"alt="'.$this->dof->get_string('view_events_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_events_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->storage('persons')->get_fullname($person->id).$link; // учитель 
        }else
        {// вакансия
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {// пользователь может просматривать шаблоны
                $link .= ' <a id="view_schtemplate_vacancy_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid=0',$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->get_string('cstreams_no_teacher', 'schedule').$link; 
        }
        // ученики - выводим всех 
        $string[] = $this->get_list_students($obj->cstreamid); 
        $daynum  = $this->get_template_week_days();
        $string[] = $daynum[$obj->daynum]; // день недели 
        $dayvars  = $this->dof->modlib('refbook')->get_day_vars();
        $string[] = $dayvars[$obj->dayvar]; // вариант недели 
        $types    = $this->dof->modlib('refbook')->get_event_types();
        $string[] = $types[$obj->type]; // тип урока 
        $forms    = $this->dof->modlib('refbook')->get_event_form();
        $string[] = $forms[$obj->form]; // форма урока 
        $string[] = $obj->place; // место - просто место
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']'; 
                    // подразделение шаблона 
        $string[] = $this->dof->workflow('schtemplates')->get_name($obj->status); // статус 
        $link = '';
        if ( $this->dof->storage('schtemplates')->is_access('edit',$obj->id) )
        {// пользователь может редактировать шаблон
            $link = '<a id="edit_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/edit.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/edit.png').
                    '"alt="'.$this->dof->get_string('edit_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('edit_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может редактировать шаблон
            $link .= ' <a id="view_schtemplate_cstream_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->cstreamid,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).'">'.'</a>';
        }
        array_unshift($string, $link); // действия - законные, незаконные караются законом 
        return $string;
    }
    
    /** Распечатать таблицу для отображения шаблонов по потоку
     * @param int $id - id потока из таблицы $cstreams
     * @param int $daynum[optional]  - день недели, для которого отображаются шаблоны
     * @param int $dayvar[optional] - вариант недели, для которого отображаются шаблоны
     * @return string
     */
    public function get_table_cstream($id, $daynum = null, $dayvar = null)
    {
        $conds = new stdClass();
        $conds->ageid = $this->ageid;
        $conds->cstreamid = $id;
        $conds->status = array('active','suspend');
        if ( ! is_null($daynum) )
        {// указан день недели
            $conds->daynum = $daynum;
        }
        if ( ! is_null($dayvar) )
        {// указан день недели
            $conds->dayvar = $dayvar;
        }
        if ( ! $templates = $this->dof->storage('schtemplates')->
               get_objects_list($conds,'daynum ASC, begin ASC'))
        {// не нашли шаблон - плохо
            return '';
        }
        // формируем данные
        $this->data = array();
        $this->rowclasses = array();
        foreach ( $templates as $template )
        {//для каждого шаблона формируем строку
            $flag = false; 
            $color = false;
            $hours    = floor($template->begin/3600);
            $minutes  = floor(($template->begin - $hours * 3600) / 60);
            
            $begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
            //$midnight = date("H",dof_make_timestamp(0, 0)); // полночь в часовом поясе
            $midnight = intval(date("H", dof_make_timestamp(0, 0, 0)));
            $zonedate = dof_usergetdate($hours);
            $date = getdate($hours);
            if ( ($zonedate['wday']*24+$zonedate['hours']) > ($date['wday']*24+$date['hours']) )
            {// положительная зона
                if ( ($hours > $midnight) OR ($hours <= ($midnight - 24)) )
                {
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }elseif ( ($zonedate['wday']*24+$zonedate['hours']) < ($date['wday']*24+$date['hours']) )
            {// отрицательная зона
                if ( ($hours <= $midnight) OR ($hours > ($midnight + 24)) )
                {// выделим другим цветом те, которые из другого подразделения
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }
            if ( ! $color AND $template->ageid != $this->ageid AND $template->departmentid != $this->departmentid
                 AND $this->ageid != 0 AND $this->departmentid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age_department';
            }elseif ( ! $color AND $template->ageid != $this->ageid AND $this->ageid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age';
            }elseif ( ! $color AND $template->departmentid != $this->departmentid AND $this->departmentid != 0 )
            {// чтоб выделить - объявим класс
                $this->rowclasses[] = 'mismatch_department';
            }elseif ( ! $color )
            {    
                $this->rowclasses[] = '';
                $flag = true;
            }
            $this->data[] = $this->get_string_cstream($template,$flag);

        }
        return $this->print_table('cstream');
    }
    
    /** Получает строку для отображения по потоку
     * @param int $obj - объект шаблона из таблицы $templates
     * @param bool $flag - отображать ссылки для редактирования или нет
     * @return array
     */
    private function get_string_cstream($obj, $flag=true)
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->departmentid; 
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;
        $addweek = $add;    
        // удалим лишнее
        unset($addweek['cstreamid']);
        unset($addweek['teacherid']);
        unset($addweek['studentid']);
        unset($addweek['agroupid']);   
        $string   = array();
        $hours    = floor($obj->begin / 3600);
        $minutes  = floor(($obj->begin - $hours * 3600) / 60);
        $begin    = $this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M"); //время начала
        $hours    = floor(($obj->begin + $obj->duration) / 3600);
        $minutes  = floor(($obj->begin + $obj->duration - $hours * 3600) / 60);
        $string[] = $begin.' - '.$this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M"); //время конца
        // предмет
        $pitemid  = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'programmitemid');
        $pitem = $this->dof->storage('programmitems')->get_field($pitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($pitemid,'code').']';
        if ( $this->dof->storage('programmitems')->is_access('view',$pitemid) )
        {// ссылка на просмотр предмета
            $pitem = '<a href='.$this->dof->url_im('programmitems','/view.php?pitemid='.$pitemid,$outadd).'>'.
                        $pitem.'</a>';
        }
        $string[] = $pitem;   
        $appointid = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'appointmentid');
        if ( $appointid )
        {// учителя вычисляем из сотрудника
            $person = $this->dof->storage('appointments')->get_person_by_appointment($appointid);
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view'))
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a id="view_schtemplate_teacher_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid='.$person->id,$addweek).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            if ( $this->dof->im('journal')->is_access('view_schevents'))
            {// пользователь может просматривать шаблоны
                $link .= '<a id="view_schtemplate_person_week_'.$obj->id.'" href='.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$person->id.
                        '&date_to='.time().'&date_from='.time(),$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_events.png').
                        '"alt="'.$this->dof->get_string('view_events_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_events_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->storage('persons')->get_fullname($person->id).$link; // учитель 
        }else
        {// вакансия
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view'))
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a id="view_schtemplate_vacancy_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid=0',$addweek).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->get_string('cstreams_no_teacher', 'schedule').$link; 
        }
        // ученики - выводим всех 
        $string[] = $this->get_list_students($obj->cstreamid); 
        $forms    = $this->dof->modlib('refbook')->get_event_form();
        $string[] = $forms[$obj->form]; // форма урока 
        // тип недели
        $type    = $this->dof->modlib('refbook')->get_day_vars();
        $string[] = $type[$obj->dayvar];  
        
        $string[] = $obj->place; // место - просто место
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']'; 
                    // подразделение шаблона  
        $string[] = $this->dof->workflow('schtemplates')->get_name($obj->status); // статус 
        $link = ''; 
        if ( $this->dof->storage('schtemplates')->is_access('edit',$obj->id) AND $flag )
        {// пользователь может редактировать шаблон
            $link .= ' <a id="edit_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/edit.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/edit.png').
                    '"alt="'.$this->dof->get_string('edit_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('edit_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view',$obj->id) AND $flag )
        {// пользователь может просматривать шаблон
            $link .= ' <a id="view_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view.png').
                    '"alt="'.$this->dof->get_string('view_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->workflow('schtemplates')->is_access('changestatus') 
             AND $obj->status == 'suspend' AND $flag )
        {
            $link .= ' <a id="activate_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/active.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/state.png').
                    '"alt="'.$this->dof->get_string('active_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('active_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->workflow('schtemplates')->is_access('changestatus') 
             AND $obj->status == 'active' AND $flag )
        {
            $link .= ' <a id="suspend_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/suspend.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/suspend.png').
                    '"alt="'.$this->dof->get_string('suspend_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('suspend_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->workflow('schtemplates')->is_access('changestatus') AND $flag )
        {
            $link .= ' <a id="delete_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/delete.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/delete.png').
                    '"alt="'.$this->dof->get_string('delete_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('delete_template', $this->get_im()).'">'.'</a>';
        }
        
        // ссылка на предмето-класс
        if ( $this->dof->storage('cstreams')->is_access('view', $obj->cstreamid) )
        {// пользователь может просматривать предмето-класс
            $link .= ' <a id="view_cstream_'.$obj->cstreamid.'_for_schtemplate_'.$obj->id.'" href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.$obj->cstreamid,$outadd).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/cstreams.png').
                    '"alt="'.$this->dof->get_string('view_cstream', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_cstream', $this->get_im()).'">'.'</a>';
        }       
        
        array_unshift($string, $link); // действия - законные, незаконные караются законом 
        return $string;
    }
    
    /** Распечатать таблицу для отображения шаблонов по учителю
     * @param int $id - id учителя из таблицы persons
     * @param int $daynum[optional]  - день недели, для которого отображаются шаблоны
     * @param int $dayvar[optional] - вариант недели, для которого отображаются шаблоны
     * @return string
     */
    public function get_table_teacher($id, $daynum = null, $dayvar = null)
    {
        $conds = new stdClass();
       // $conds->departmentid = $this->departmentid;
        $conds->status = array('active','suspend');
        $conds->teacherid = $id;
        if ( ! $id )
        {// если учителя нет, то предмето-классы выбираем на период
            $conds->ageid = $this->ageid;
        }
        if ( ! is_null($daynum) )
        {// указан день недели
            $conds->daynum = $daynum;
        }
        if ( ! is_null($dayvar) )
        {// указан день недели
            $conds->dayvar = $dayvar;
        }
        if ( ! $templates = $this->dof->storage('schtemplates')->
               get_objects_list($conds,'daynum ASC, begin ASC'))
        {// не нашли шаблон - плохо
            return '';
        }
        // формируем данные
        $this->data = array();
        $this->rowclasses = array();
        foreach ( $templates as $template )
        {//для каждого шаблона формируем строку
            $flag = false; 
            $color = false;
            $hours    = floor($template->begin/3600);
            $minutes  = floor(($template->begin - $hours * 3600) / 60);
            
            $begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
            //$midnight = date("H",dof_make_timestamp(0, 0)); // полночь в часовом поясе
            $midnight = intval(date("H",dof_make_timestamp(0, 0, 0)));
            $zonedate = dof_usergetdate($hours);
            $date = getdate($hours);
            if ( ($zonedate['wday']*24+$zonedate['hours']) > ($date['wday']*24+$date['hours']) )
            {// положительная зона
                if ( ($hours > $midnight) OR ($hours <= ($midnight - 24)) )
                {
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }elseif ( ($zonedate['wday']*24+$zonedate['hours']) < ($date['wday']*24+$date['hours']) )
            {// отрицательная зона
                if ( ($hours <= $midnight) OR ($hours > ($midnight + 24)) )
                {// выделим другим цветом те, которые из другого подразделения
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }
            if ( ! $color AND $template->ageid != $this->ageid AND $template->departmentid != $this->departmentid
                 AND $this->ageid != 0 AND $this->departmentid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age_department';
            }elseif ( ! $color AND $template->ageid != $this->ageid AND $this->ageid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age';
            }elseif ( ! $color AND $template->departmentid != $this->departmentid AND $this->departmentid != 0 )
            {// чтоб выделить - объявим класс
                $this->rowclasses[] = 'mismatch_department';
            }elseif ( ! $color )
            {    
                $this->rowclasses[] = '';
                $flag = true;
            }
            $this->data[] = $this->get_string_teacher($template,$flag);
        }
        return $this->print_table('teacher');
    }
    
    /** Получает строку для отображения по учителю
     * @param object $obj - объект шаблона из таблицы schtemplates
     * @param bool $flag - отображать иконки редактирования или нет(по умолчанию ДА)
     * @return array
     */
    private function get_string_teacher($obj, $flag=true)
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->departmentid;   
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;     
        $string   = array();
        $hours    = floor($obj->begin / 3600);
        $minutes  = floor(($obj->begin - $hours * 3600) / 60);
        $begin    = $this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M"); //время начала
        $hours    = floor(($obj->begin + $obj->duration) / 3600);
        $minutes  = floor(($obj->begin + $obj->duration - $hours * 3600) / 60);
        $string[] = $begin.' - '.$this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M"); //время конца
        // предмет
        $pitemid  = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'programmitemid');
        $pitem = $this->dof->storage('programmitems')->get_field($pitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($pitemid,'code').']';
        if ( $this->dof->storage('programmitems')->is_access('view',$pitemid) )
        {// ссылка на просмотр предмета
            $pitem = '<a href='.$this->dof->url_im('programmitems','/view.php?pitemid='.$pitemid,$outadd).'>'.
                        $pitem.'</a>';
        }
        $string[] = $pitem;   
        $appointid = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'appointmentid');
        // ученики - выводим всех 
        $string[] = $this->get_list_students($obj->cstreamid); 
        $forms    = $this->dof->modlib('refbook')->get_event_form();
        $string[] = $forms[$obj->form]; // форма урока 
        // тип недели
        $type    = $this->dof->modlib('refbook')->get_day_vars();
        $string[] = $type[$obj->dayvar]; 
        
        $string[] = $obj->place; // место - просто место
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']'; 
                    // подразделение шаблона
                         
        $string[] = $this->dof->workflow('schtemplates')->get_name($obj->status); // статус 
        $link = ''; 
        if ( $this->dof->storage('schtemplates')->is_access('edit',$obj->id) AND $flag )
        {// пользователь может редактировать шаблон
            $link .= ' <a id="edit_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/edit.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/edit.png').
                    '"alt="'.$this->dof->get_string('edit_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('edit_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view',$obj->id) AND $flag )
        {// пользователь может просматривать шаблон
            $link .= ' <a id="view_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view.png').
                    '"alt="'.$this->dof->get_string('view_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view'))
        {// пользователь может просматривать шаблоны
            $link .= ' <a id="view_schtemplate_cstream_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->cstreamid,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).'">'.'</a>';
        }
            if ( $this->dof->workflow('schtemplates')->is_access('changestatus') 
             AND $obj->status == 'suspend' AND $flag )
        {
            $link .= ' <a id="activate_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/active.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/state.png').
                    '"alt="'.$this->dof->get_string('active_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('active_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->workflow('schtemplates')->is_access('changestatus') 
             AND $obj->status == 'active' AND $flag )
        {
            $link .= ' <a id="suspend_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/suspend.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/suspend.png').
                    '"alt="'.$this->dof->get_string('suspend_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('suspend_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->workflow('schtemplates')->is_access('changestatus') AND $flag )
        {
            $link .= ' <a id="delete_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/delete.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/delete.png').
                    '"alt="'.$this->dof->get_string('delete_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('delete_template', $this->get_im()).'">'.'</a>';
        }
        // ссылка на предмето-класс
        if ( $this->dof->storage('cstreams')->is_access('view', $obj->cstreamid) )
        {// пользователь может просматривать предмето-класс
            $link .= ' <a id="view_cstream_'.$obj->cstreamid.'_for_schtemplate_'.$obj->id.'" href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.$obj->cstreamid,$outadd).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/cstreams.png').
                    '"alt="'.$this->dof->get_string('view_cstream', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_cstream', $this->get_im()).'">'.'</a>';
        }
        array_unshift($string, $link);  // действия - законные, незаконные караются законом 
        return $string;
    }
    
    /** Распечатать таблицу для отображения шаблонов группы
     * @param int $id - id группы из таблицы agroups
     * @param int $daynum[optional]  - день недели, для которого отображаются шаблоны
     * @param int $dayvar[optional] - вариант недели, для которого отображаются шаблоны
     * @return string
     */
    public function get_table_agroup($id, $daynum = null, $dayvar = null)
    {
        $conds = new stdClass();
        //$conds->departmentid = $this->departmentid;
        $conds->agroupid = $id;
        $conds->status = array('active','suspend');
        if ( ! is_null($daynum) )
        {// указан день недели
            $conds->daynum = $daynum;
        }
        if ( ! is_null($dayvar) )
        {// указан день недели
            $conds->dayvar = $dayvar;
        }
        if ( ! $templates = $this->dof->storage('schtemplates')->
               get_objects_list($conds,'daynum ASC, begin ASC'))
        {// не нашли шаблон - плохо
            return '';
        }
        // формируем данные
        $this->data = array();
        $this->rowclasses = array();
        // @todo отображение берем для потока,т.к таблицы индетичны
        // если что-то изменится, потом напишем отдельный метод
        foreach ( $templates as $template )
        {//для каждого шаблона формируем строку
            $flag = false; 
            $color = false;
            $hours    = floor($template->begin/3600);
            $minutes  = floor(($template->begin - $hours * 3600) / 60);
            
            $begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
            //$midnight = date("H",dof_make_timestamp(0, 0)); // полночь в часовом поясе
            $midnight = intval(date("H",dof_make_timestamp(0, 0, 0)));
            $zonedate = dof_usergetdate($hours);
            $date = getdate($hours);
            if ( ($zonedate['wday']*24+$zonedate['hours']) > ($date['wday']*24+$date['hours']) ) 
            {// положительная зона
                if ( ($hours > $midnight) OR ($hours <= ($midnight - 24)) )
                {
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }elseif ( ($zonedate['wday']*24+$zonedate['hours']) < ($date['wday']*24+$date['hours']) )
            {// отрицательная зона
                if ( ($hours <= $midnight) OR ($hours > ($midnight + 24)) )
                {// выделим другим цветом те, которые из другого подразделения
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }
            if ( ! $color AND $template->ageid != $this->ageid AND $template->departmentid != $this->departmentid
                 AND $this->ageid != 0 AND $this->departmentid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age_department';
            }elseif ( ! $color AND $template->ageid != $this->ageid AND $this->ageid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age';
            }elseif ( ! $color AND $template->departmentid != $this->departmentid AND $this->departmentid != 0 )
            {// чтоб выделить - объявим класс
                $this->rowclasses[] = 'mismatch_department';
            }elseif ( ! $color )
            {    
                $this->rowclasses[] = '';
                $flag = true;
            }
            $this->data[] = $this->get_string_cstream($template,$flag);         
        }
        return $this->print_table('agroup');
    }
    
    /** Распечатать таблицу для отображения шаблонов студента
     * @param int $id - id ученика в таблице persons
     * @param int $daynum[optional]  - день недели, для которого отображаются шаблоны
     * @param int $dayvar[optional] - вариант недели, для которого отображаются шаблоны
     * @return string
     */
    public function get_table_student($id, $daynum , $dayvar = null)
    {
        $conds = new stdClass();
        // $conds->departmentid = $this->departmentid;
        $conds->studentid = $id;
        $conds->status = array('active','suspend');
        $conds->cstreamsstatus = array('plan','active','suspend');
        $conds->cpassedstatus = array('plan','active','suspend');
        if ( ! is_null($daynum) )
        {// указан день недели
            $conds->daynum = $daynum;
        }
        if ( ! is_null($dayvar) )
        {// указан день недели
            $conds->dayvar = $dayvar;
        }
        if ( ! $templates = $this->dof->storage('schtemplates')->
               get_templaters_on_day($conds,'daynum ASC, begin ASC'))
        {// не нашли шаблон - плохо
            return '';
        }
        // формируем данные
        $this->data = array();
        // @todo отображение берем для потока,т.к таблицы индетичны
        // если что-то изменится, потом напишем отдельный метод
        $this->rowclasses = array();
        foreach ( $templates as $template )
        {//для каждого шаблона формируем строку
            $flag = false; 
            $color = false;
            $hours    = floor($template->begin/3600);
            $minutes  = floor(($template->begin - $hours * 3600) / 60);
            
            $begin = dof_userdate(mktime($hours, $minutes),"%H"); //время начала
            //$midnight = date("H",dof_make_timestamp(0, 0)); // полночь в часовом поясе
            $midnight = intval(date("H",dof_make_timestamp(0, 0, 0)));
            $zonedate = dof_usergetdate($hours);
            $date = getdate($hours);
            if ( ($zonedate['wday']*24+$zonedate['hours']) > ($date['wday']*24+$date['hours']) )
            {// положительная зона
                if ( ($hours > $midnight) OR ($hours <= ($midnight - 24)) )
                {
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }elseif ( ($zonedate['wday']*24+$zonedate['hours']) < ($date['wday']*24+$date['hours']) )
            {// отрицательная зона
                if ( ($hours <= $midnight) OR ($hours > ($midnight + 24)) )
                {// выделим другим цветом те, которые из другого подразделения
                     $this->rowclasses[] = 'mismatch_timezone';
                     $color = true;
                     $flag = true;
                }
            }
            if ( ! $color AND $template->ageid != $this->ageid AND $template->departmentid != $this->departmentid
                 AND $this->ageid != 0 AND $this->departmentid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age_department';
            }elseif ( ! $color AND $template->ageid != $this->ageid AND $this->ageid != 0 )
            {//чтоб выделить - объявим класс';
                $this->rowclasses[] = 'mismatch_age';
            }elseif ( ! $color AND $template->departmentid != $this->departmentid AND $this->departmentid != 0 )
            {// чтоб выделить - объявим класс
                $this->rowclasses[] = 'mismatch_department';
            }elseif ( ! $color )
            {    
                $this->rowclasses[] = '';
                $flag = true;
            }
            $this->data[] = $this->get_string_cstream($template,$flag);  
        }
        return $this->print_table('student');
    }
    
    /** Получает строку для отображения по времени
     * @param object $obj - объект шаблона из таблицы schtemplates
     * @return array
     */
    private function get_string_time($obj, $dayvar)
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->departmentid;
        $outadd = array();
        $outadd['departmentid'] = $this->departmentid;   
        $string   = array();
        $pitemid  = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'programmitemid');
        $pitem = $this->dof->storage('programmitems')->get_field($pitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($pitemid,'code').']';
        if ( $this->dof->storage('programmitems')->is_access('view',$pitemid) )
        {// ссылка на просмотр предмета
            $pitem = '<a href='.$this->dof->url_im('programmitems','/view.php?pitemid='.$pitemid,$outadd).'>'.
                        $pitem.'</a>';
        }
        $string[] = $pitem; 
                    // предмет
        $appointid = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'appointmentid');
        if ( $appointid )
        {// учителя вычисляем из сотрудника
            $person = $this->dof->storage('appointments')->get_person_by_appointment($appointid);
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a id="view_schtemplate_teacher_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid='.$person->id,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            if ( $this->dof->im('journal')->is_access('view_schevents'))
            {// пользователь может просматривать шаблоны
                $link .= '<a id="view_schtemplate_person_week_'.$obj->id.'" href='.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$person->id.
                        '&date_to='.time().'&date_from='.time(),$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_events.png').
                        '"alt="'.$this->dof->get_string('view_events_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_events_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->storage('persons')->get_fullname($person->id).$link; // учитель 
        }else
        {// вакансия
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a id="view_schtemplate_vacancy_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid=0',$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->get_string('cstreams_no_teacher', 'schedule').$link; 
        }
        $appointid = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'appointmentid');
        // ученики - выводим всех 
        $string[] = $this->get_list_students($obj->cstreamid); 
        $forms    = $this->dof->modlib('refbook')->get_event_form();
        $string[] = $forms[$obj->form]; // форма урока 
        // тип недели
        $type    = $this->dof->modlib('refbook')->get_day_vars();
        $string[] = $type[$obj->dayvar]; 
        
        $string[] = $obj->place; // место - просто место
        $string[] = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                    $this->dof->storage('departments')->get_field($obj->departmentid,'code').']'; 
                    // подразделение шаблона
        $string[] = $this->dof->workflow('schtemplates')->get_name($obj->status); // статус
        $link = ''; 
        if ( $dayvar == $obj->dayvar )
        {
            if ( $this->dof->storage('schtemplates')->is_access('edit',$obj->id) )
            {// пользователь может редактировать шаблон
                $link .= ' <a id="edit_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/edit.php?id='.$obj->id,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/edit.png').
                        '"alt="'.$this->dof->get_string('edit_template', $this->get_im()).
                        '" title="'.$this->dof->get_string('edit_template', $this->get_im()).'">'.'</a>';
            }
            if ( $this->dof->storage('schtemplates')->is_access('view',$obj->id) )
            {// пользователь может просматривать шаблон
                $link .= ' <a id="view_schtemplate_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view.php?id='.$obj->id,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view.png').
                        '"alt="'.$this->dof->get_string('view_template', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_template', $this->get_im()).'">'.'</a>';
            }
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {// пользователь может просматривать шаблоны
                $link .= ' <a id="view_schtemplate_cstream_week_'.$obj->id.'" href='.$this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->cstreamid,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).'">'.'</a><br>';
            }
            // просмотр темплана
            if ( $this->dof->im('plans')->is_access('viewthemeplan', $obj->cstreamid) OR
                 $this->dof->im('plans')->is_access('viewthemeplan/my', $obj->cstreamid) )
            {// пользователь может просматривать шаблоны
                $link .= ' <a id="view_schtemplate_planning_'.$obj->id.'" href='.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=cstreams&linkid='.$obj->cstreamid,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/plancstream.png').
                        '"alt="'.$this->dof->get_string('view_plancstream', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_plancstream', $this->get_im()).'">'.'</a>';
            } 
               
            // просмотр предмето-класса
            if ( $this->dof->storage('cstreams')->is_access('view', $obj->cstreamid) )
            {// пользователь может просматривать шаблоны
                $link .= ' <a id="view_cstream_'.$obj->cstreamid.'_for_schtemplate_'.$obj->id.'" href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.$obj->cstreamid,$outadd).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/cstreams.png').
                        '"alt="'.$this->dof->get_string('view_cstream', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_cstream', $this->get_im()).'">'.'</a>';
            }                     
            
            
        }    
        array_unshift($string,$link);// действия - законные, незаконные караются законом 
        return $string;
    }
    // @todo - переделать метод    
    /** Получить список учеников, (краткий - при наведении мыши - полный), относящихся к шаблону
     * 
     * @param int $cstreamid - id потока, для которого получаются ученики
     * 
     * @return string - строка со списком учеников
     */
    protected function get_list_students($cstreamid)
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->departmentid;  
        // удалим лишнее
        unset($add['cstreamid']);
        unset($add['teacherid']);
        unset($add['studentid']);
        unset($add['agroupid']);  
        $student = array();
        if ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$cstreamid,'status'=>array('active','plan','suspend'))) )
        {// если есть ученики - покажем их
            foreach ( $cpassed as $cpass )
            {// каждого
                $link = '';
                if ( $this->dof->im('journal')->is_access('view_schevents'))
                {// пользователь может просматривать шаблоны
                    $link .= '<a href='.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$cpass->studentid.
                            '&date_to='.time().'&date_from='.time(),$add).'>'.
                            '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_events.png').
                            '"alt="'.$this->dof->get_string('view_events_student', $this->get_im()).
                            '" title="'.$this->dof->get_string('view_events_student', $this->get_im()).'">'.'</a>';
                }
                $add['studentid'] = $cpass->studentid;
                $student[] = $this->dof->storage('persons')->get_fullname($cpass->studentid).
                         '<br><a href="'.$this->dof->url_im($this->get_im(), '/view_week.php',$add).'">
                         <img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').'"
                         alt=  "'.$this->dof->get_string('view_week_template_on_student', $this->get_im()).'" 
                         title="'.$this->dof->get_string('view_week_template_on_student', $this->get_im()).'" /></a>'.$link;

            }
        }
        return implode('<br>',$student);
    }
    
    
   /** Возвращает html-код таблицы
     * @param string $type - тип отображения данных
     *                           view - один шаблон 
     *                           full - полные данные по шаблону 
     *                           cstream - отображение по потоку
     *                           student - отображение по ученику
     *                           teacher - по учителю
     *                           time - по времени
     *                           person - по персоне (неизвестно ученик или учитель)
     *                           group- по группе
     *                           
     * @return string - html-код или пустая строка
     */
    protected function print_table($type)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
        $table->rowclasses = $this->rowclasses;
        switch ( $type )
		{
		    case 'view': // для одного шаблона
			case 'full': // полная версия
                //$table->size = array ('50px','150px','150px','200px','150px','100px');
                $table->align = array("center","center","center","center","center",
                                      "center","center","center","center","center",
                                      "center","center","center");
            break;
            case 'cstream': // для потока
            case 'student': // для ученика
                //$table->size = array ('50px','150px','150px','200px','150px','100px');
                $table->wrap = array (true);
                $table->align = array("center","center","center","center","center",
                                      "center","center","center","center","center");
            break; 
            case 'teacher': // для учителя
                $table->wrap = array (true);
                $table->align = array("center","center","center","center","center",
                                      "center","center","center","center");
            break; 
            case 'agroup': // для группы
                $table->wrap = array (true);
                $table->align = array("center","center","center","center","center",
                                      "center","center","center","center","center");            
            case 'time': // для времени
                //$table->wrap = array (true);
                $table->align = array("center","center","center","center","center",
                                      "center","center","center","center");
            break; 
            case 'person': // для персоны
                $table->width = '60%';
                //$table->wrap = array (true);
                $table->align = array("left","left","left","center");
            break; 
            case 'group': // для персоны
                $table->width = '60%';
                //$table->wrap = array (true);
                $table->align = array("left","left","center");
            break; 
		}
        
        // шапка таблицы
        $table->head = $this->get_header($type);
        // заносим данные в таблицу     
        $table->data = $this->data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * @param string $type - тип отображения данных
     *                           view - один шаблон 
     *                           full - полные данные по шаблону 
     *                           cstream - отображение по потоку
     *                           student - отображение по ученику
     *                           teacher - по учителю
     *                           time - по времени
     *                           person - по персоне (неизвестно ученик или учитель)
     *                           group- по группе
     * @return array
     */
    private function get_header($type)
    {
        switch ( $type )
		{
		    // для одного шаблона
		    case 'view':
		    // полная версия
			case 'full':
            return array($this->dof->modlib('ig')->igs('actions'),
                         $this->dof->get_string('cstream_name', $this->get_im()),
                         $this->dof->get_string('begin_time', $this->get_im()),
                         $this->dof->get_string('duration', $this->get_im()),
                         $this->dof->get_string('item', $this->get_im()),
                         $this->dof->get_string('teacher', $this->get_im()),
                         $this->dof->get_string('students', $this->get_im()),
                         $this->dof->get_string('daynum', $this->get_im()),
                         $this->dof->get_string('dayvar', $this->get_im()),
                         $this->dof->get_string('lesson_type', $this->get_im()),
                         $this->dof->get_string('lesson_form', $this->get_im()),
                         $this->dof->get_string('place', $this->get_im()),
                         $this->dof->get_string('department', $this->get_im()),
                         $this->dof->modlib('ig')->igs('status'));
            break;
            // для потока
            case 'cstream':
            // для ученика
            case 'student':
            // для группы
            case 'agroup':
            return array($this->dof->modlib('ig')->igs('actions'),
                         $this->dof->get_string('time_lesson', $this->get_im(),'<br>'),
                         $this->dof->get_string('item', $this->get_im()),
                         $this->dof->get_string('teacher', $this->get_im()),
                         $this->dof->get_string('students', $this->get_im()),
                         //$this->dof->get_string('daynum', $this->get_im()),
                         //$this->dof->get_string('lesson_type', $this->get_im()),
                         $this->dof->get_string('lesson_form', $this->get_im(),'<br>'),
                         $this->dof->get_string('dayvar', $this->get_im(),'<br>'),
                         $this->dof->get_string('place', $this->get_im()),
                         $this->dof->get_string('department', $this->get_im()),
                         $this->dof->modlib('ig')->igs('status'));
            break; 
            // для учителя
            case 'teacher':
            return array($this->dof->modlib('ig')->igs('actions'),
                         $this->dof->get_string('time_lesson', $this->get_im(),'<br>'),
                         $this->dof->get_string('item', $this->get_im()),
                         //$this->dof->get_string('teacher', $this->get_im()),
                         $this->dof->get_string('students', $this->get_im()),
                         //$this->dof->get_string('daynum', $this->get_im()),
                         //$this->dof->get_string('dayvar', $this->get_im()),
                         //$this->dof->get_string('lesson_type', $this->get_im()),
                         $this->dof->get_string('lesson_form', $this->get_im(),'<br>'),
                         $this->dof->get_string('dayvar', $this->get_im(),'<br>'),
                         $this->dof->get_string('place', $this->get_im()),
                         $this->dof->get_string('department', $this->get_im()),
                         $this->dof->modlib('ig')->igs('status'));
            break; 
            // для времени
            case 'time':
            return array($this->dof->modlib('ig')->igs('actions'),
                         //$this->dof->get_string('begin_time', $this->get_im()),
                         //$this->dof->get_string('end_time', $this->get_im()),
                         $this->dof->get_string('item', $this->get_im()),
                         $this->dof->get_string('teacher', $this->get_im()),
                         $this->dof->get_string('students', $this->get_im()),
                         //$this->dof->get_string('daynum', $this->get_im()),
                         //$this->dof->get_string('dayvar', $this->get_im()),
                         //$this->dof->get_string('lesson_type', $this->get_im()),
                         $this->dof->get_string('lesson_form', $this->get_im(),'<br>'),
                         $this->dof->get_string('dayvar', $this->get_im(),'<br>'),
                         $this->dof->get_string('place', $this->get_im()),
                         $this->dof->get_string('department', $this->get_im()),
                         $this->dof->modlib('ig')->igs('status'));
            break; 
            // под персону
            case 'person':
            return array($this->dof->modlib('ig')->igs('actions'),
                         $this->dof->get_string('lastname', $this->get_im()),//$this->get_im()),
                         $this->dof->get_string('firstname', $this->get_im()),//$this->get_im()),
                         $this->dof->get_string('middlename', $this->get_im()),/*$this->get_im()),*/);
            break; 
            // под группу
            case 'group':
            return array($this->dof->modlib('ig')->igs('actions'),
                         $this->dof->get_string('name', $this->get_im()),//$this->get_im()),
                         $this->dof->get_string('code', $this->get_im())/*$this->get_im()),*/);
            break; 
		}
    }
    
    
    /***************************************/
    /**** Методы отображения информации ****/
    /****      на главной странице      ****/
    /***************************************/
    
    /** Составить заголовок главной страницы просмотра шаблонов расписания. Метод составляет
     * html-код заголовка из вкладок, в зависимости от переданных параметров. 
     * Новые уровни вкладок добавляются к старым по мере выбора пользователем параметров отображения.
     * Каждый раз передается полный html-код заголовка со всеми вкладками.
     * Аргументы в функции перечислены в порядке выбора их пользователем.
     * 
     * @todo я не успел разобраться с тем как Moodle выводит расширенное дерево вкладок (в несколько уровней)
     *       Поэтому сейчас просто выводится несколько одноуровневых списков. Выглядит не так круто как
     *       хотелось бы, но работает. Внешний вид улучшим тогда когда будет время.
     * 
     * @param int $ageid - id учебного периодадля которого отображается расписание
     * @param string $displaytype - тип отображения. 3 возможных варианта:
     *                 time - отображение расписания по интервалам времени
     *                 students - отображение по ученикам
     *                 teachers - отображение по учителям
     * @param int $daynum[optional] - номер дня недели от понедельника (1) до воскресенья (7)
     * @param int $dayvar[optional] - тип учебной недели: ежедневно(0), четная(1) или нечетная(2).
     *                                Если не передана - то берется из настроек
     * @param int $time[optional] - временной интервал, за который просматривается расписание.
     *                              Передается только если выбрано отображение по времени.
     *                              Возможные значения:
     *                              0 - за все время
     *                              1 - с 9 до 12
     *                              2 - с 12 до 15
     *                              3 - с 15 до 18
     *                              4 - после 18
     * 
     * @return string - html-код заголовка с вкладками
     */
    public function get_main_page_tabs($ageid, $displaytype, $daynum=null, $dayvar=null, $time=null, $form='all')
    {
        if ( ! $ageid OR ! $displaytype )
        {// не показываем ничего, пока не будет определен период и вид отображения
            return '';
        }
        // переменная, содержащая html-код всех элементов
        $result = '';
        // массив для параметров url для вкладок
        $urloptions = array();
        $urloptions['departmentid'] = $this->departmentid;
        $urloptions['ageid']        = $ageid;
        $urloptions['display']      = $displaytype;
        $urloptions['form']         = $form;
        if ( $daynum )
        {// день недели выбран - добавим в навигацию
            $urloptions['daynum'] = $daynum;
        }
        if ( ! is_null($dayvar) )
        {//  добавим выбранный тип недели в навигацию
            $urloptions['dayvar'] = $dayvar;
        }else 
        {// если тип учебной недели не передан - используем значение по умолчанию
            $urloptions['dayvar'] = $this->get_default_dayvar();
        }
        if ( ! is_null($time) )
        {//  добавим выбранный интервал времени в навигацию
            $urloptions['intervalid'] = $time;
        }
        // создаем массив, содержащий уровни вкладок
        $tablevels = array();
        
        // Создаем строку с днями недели
        // нам уже передан период и тип отображения - покажем список дней недели
        $weektabs  = $this->get_week_tabs($urloptions);
        $tablevels = $weektabs;
        
        if ( ! $daynum )
        {// если день недели еще не выбран - то не показываем пока нижние вкладки, а возвращаем то что есть
            return $this->dof->modlib('widgets')->print_tabs($tablevels, NULL, NULL, NULL, true);
        }else
        {// день недели выбран - укажем его
            $result .= $this->dof->modlib('widgets')->print_tabs($tablevels, $daynum, NULL, NULL, true);
        }
        
        // Создаем строку с вариантами учебной недели
        $dayvartabs  = $this->get_dayvar_tabs($urloptions);
        $tablevels = $dayvartabs;

        if ( $daynum )
        {
            $result .= '<div>'.
                $this->dof->modlib('widgets')->print_tabs($tablevels, $dayvar, NULL, NULL, true).'</div>';
        }
        
        
        // Создаем строку с вариантами времени
        if ( $displaytype == 'time' AND $daynum AND ! is_null($dayvar) )
        {// если выбрано отображение по времени - добавим дополнительный уровень вкладок с интервалами времени
            $timetabs = $this->get_time_tabs($urloptions);
            $tablevels = $timetabs;
            if ( is_null($time) )
            {// если временной интервал не указан - просто выведем строку вкладок
                $result .= '<div>'.
                    $this->dof->modlib('widgets')->print_tabs($tablevels, NULL, NULL, NULL, true).'</div>';
            }else
            {// если указан - выделим нужную вкладку
                $result .= '<div>'.
                    $this->dof->modlib('widgets')->print_tabs($tablevels, $time, NULL, NULL, true).'</div>';
            }
        }
        
        return $result;
    }
    
    /** Получить список закладок для интервалов времени
     * @param array $urloptions - массив с дополнительными get-параметрами для ссылок
     * 
     * @return array - массив вкладок, объектов dof_modlib_widgets_tabobject
     */
    protected function get_time_tabs($urloptions)
    {
        $tabs = array();
        $intervals = $this->schedule_intervals_list();
        
        foreach ( $intervals as $id=>$interval )
        {// перебираем все доступные интервалы времени и из каждого делаем вкладку
            // добавляем в ссылку больше параметров
            //$urloptions['begin']      = $interval['begin'];
            //$urloptions['end']        = $interval['end'];
            $urloptions['intervalid'] = $id;
            // создаем ссылку для вкладки
            $link   = $this->dof->url_im($this->get_im(), '/index.php', $urloptions);
            // создаем саму вкладку
            $tabs[] = $this->dof->modlib('widgets')->create_tab($id, $link, $interval['label']);
        }
        
        return $tabs;
    }
    
    /** Получить список закладок для дней недели
     * @param array $urloptions - массив с дополнительными get-параметрами для ссылок
     * 
     * @return array - массив вкладок, объектов dof_modlib_widgets_tabobject
     */
    protected function get_week_tabs($urloptions)
    {
        $tabs = array();
        // получаем список дней недели
        $days = $this->get_template_week_days();
        foreach ( $days as $daynum=>$dayname )
        {// из каждого дня недели делаем вкладку
            $urloptions['daynum'] = $daynum;
            // создаем ссылку для вкладки
            $link = $this->dof->url_im($this->get_im(), '/index.php', $urloptions);
            // создаем саму вкладку
            $tabs[] = $this->dof->modlib('widgets')->create_tab($daynum, $link, $dayname);
        }
        
        return $tabs;
    }
    
    /** Получить массив закладок с вариантами учебной недели (четная/нечетная/ежедневно)
     * 
     * @param array $urloptions - массив с дополнительными get-параметрами для ссылок
     * 
     * @return array - массив вкладок, объектов dof_modlib_widgets_tabobject
     */
    protected function get_dayvar_tabs($urloptions)
    {
        $tabs = array();
        // получаем варианты недели
        $dayvars = $this->dof->modlib('refbook')->get_day_vars();
        foreach ( $dayvars as $id=>$dayvar )
        {// из каждого варианта делаем вкладку
            $urloptions['dayvar'] = $id;
            // создаем ссылку для вкладки
            $link = $this->dof->url_im($this->get_im(), '/index.php', $urloptions);
            // создаем саму вкладку
            $tabs[] = $this->dof->modlib('widgets')->create_tab($id, $link, $dayvar);
        }
        
        return $tabs;
    }
    
    /** Получить список возможных временных интервалов для отображения уроков.
     * @todo брать список из настроек, добавить возможность создавать свои интервалы,
     *       а также добавлять и удалять интервалы
     * 
     * @return array массив по которому будут формироваться вкладки с интервалами времени.
     *               каждый элемент содержит время начала, время окончания (в секундах)
     *               и подпись, которая будет располагаться на вкладке
     */
    protected function schedule_intervals_list()
    {
        return array(
            // интервал который называется "все".
            // Если он выбран - то показываются шаблоны за все время
            0 => array('begin' => 0,
                       'end'   => 24 * 3600,
                       'label' => $this->dof->modlib('ig')->igs('all')),
                        // 9-12
            1 => array('begin' => 0 * 3600,
                       'end'   => 9 * 3600,
                       'label' => $this->dof->modlib('ig')->igs('until').' 9'),
            // 9-12
            2 => array('begin' => 9  * 3600,
                       'end'   => 12 * 3600,
                       'label' => $this->dof->modlib('ig')->igs('from').' 9 '.
                                  $this->dof->modlib('ig')->igs('until_sm').' 12'),
            // 12-15
            3 => array('begin' => 12 * 3600,
                       'end'   => 15 * 3600,
                       'label' => $this->dof->modlib('ig')->igs('from').' 12 '.
                                  $this->dof->modlib('ig')->igs('until_sm').' 15'),
            // 15-18
            4 => array('begin' => 15 * 3600,
                       'end'   => 18 * 3600,
                       'label' => $this->dof->modlib('ig')->igs('from').' 15 '.
                                  $this->dof->modlib('ig')->igs('until_sm').' 18'),
            // После 18
            5 => array('begin' => 18 * 3600,
                       'end'   => 24 * 3600,
                       'label' => $this->dof->modlib('ig')->igs('after').' 18')
            );
    }
    
    /** Получить тип учебной недели, отображаемый по умолчанию
     * 
     * @return int
     */
    protected function get_default_dayvar()
    {
        $dayvar = $this->dof->storage('config')->get_config
                    ('dayvar', 'storage', 'schtemplates', optional_param('departmentid', 0, PARAM_INT));
        if ( isset($dayvar->value) )
        {
            return $dayvar->value;
        }
        // настройки нет
        return 0;
    }
    
    /*************************************************/
    /****** Функции для отображения расписания *******/
    /*************************************************/
    
    /** Отобразить большую таблицу шаблонов за весь день (показывается только после того как выбран период,
     * тип и день недели)
     * Собирает данные (по ученикам/учителям/времени) и в цикле распечатывает много маленьких таблиц для каждого
     * временного интервала, ученика или учителя.
     * 
     * 
     * @param int $ageid - id учебного периодадля которого отображается расписание
     * @param string $displaytype - тип отображения. 3 возможных варианта:
     *                 time - отображение расписания по интервалам времени
     *                 students - отображение по ученикам
     *                 teachers - отображение по учителям
     * @param int $daynum - номер дня недели от понедельника (1) до воскресенья (7)
     * @param int $dayvar - тип учебной недели: ежедневно (0), четная (1) или нечетная (2).
     *                                Если не передана - то берется из настроек
     * @param int $time[optional] - временной интервал, за который просматривается расписание.
     *                              Передается только если выбрано отображение по времени.
     *                              Возможные значения:
     *                              0 - за все время
     *                              1 - до 9
     *                              2 - с 9 до 12
     *                              3 - с 12 до 15
     *                              4 - с 15 до 18
     *                              5 - после 18
     * @return null Эта функция не возвращает значений, просто выводит данные на экран
     */
    public function print_full_schedule($ageid, $displaytype, $daynum, $dayvar, $time=null, $form = 'all')
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->departmentid;
        $string = '';
        // находим данные в зависимости от отображения
        switch ( $displaytype )
        {
		    case 'time': // по времени';
		        $this->data = array();
		        $datas = $this->compose_schedule_by_time($ageid, $daynum, $dayvar, $time, $form);
                if ( ! empty($datas) ) 
                {// нашли данные
                    foreach ( $datas as $time=>$objects)
                    {// формируем из них таблицы
                        $a = new stdClass();
                        $a->label = $time;
                        $a->records = $objects;
                        $string .= $this->print_schedule_part($displaytype, $a, $dayvar);
                    }
                }else
                {// ничего не нашли - выведем сообщение
                    return '<div align="center"><b>'.$this->dof->get_string
                       ('no_list_templates_for_daynum_daynum_interval', $this->get_im()).'</b></div>';
                }
		    break;
		    case 'students':// по ученику
		        // рисуем таблицу
                $table1 = new stdClass();
                $table1->tablealign = "center";
                $table1->cellpadding = 5;
                $table1->cellspacing = 5;
                $table1->rowclasses = $this->rowclasses;
                $table1->width = '60%';
                $table1->align = array("center");
                // шапка таблицы
                $table1->head = array($this->dof->get_string('groups', $this->get_im()));
                $table = '';
		        $this->data = array();
                $datas = $this->compose_schedule_by_agroups($ageid, $daynum, $dayvar);
                if ( ! empty($datas) ) 
                {// нашли данные
                    foreach ( $datas as $group )
                    {//для каждого шаблона формируем строку
                        $link = '';
                        if ( $this->dof->storage('schtemplates')->is_access('view') )
                        {// пользователь может просматривать шаблоны
                            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view_week.php?agroupid='.$group->id,$add).'>'.
                                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                                    '"alt="'.$this->dof->get_string('view_week_template_on_group', $this->get_im()).
                                    '" title="'.$this->dof->get_string('view_week_template_on_group', $this->get_im()).'">'.'</a>';
                        }
                        $this->data[] = array($group->name,$group->code,$link);
                    }
                    $table .= $this->print_table('group');
                }else
                {// ничего не нашли - выведем сообщение
                    $table1->data = array(array($this->dof->get_string
                           ('no_list_groups', $this->get_im())));
                }
                $string .= $this->dof->modlib('widgets')->print_table($table1,true).$table; 
                // шапка таблицы
                $table1->head = array($this->dof->get_string('students', $this->get_im()));
                $table1->data = array();
                $table = '';
                $this->data = array();
                $datas = $this->compose_schedule_by_students($ageid, $daynum, $dayvar);
                if ( ! empty($datas) ) 
                {// нашли данные
                    foreach ( $datas as $person )
                    {//для каждого шаблона формируем строку
                        $link = '';
                        if ( $this->dof->storage('schtemplates')->is_access('view') )
                        {// пользователь может просматривать шаблоны
                            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view_week.php?studentid='.$person->id,$add).'>'.
                                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                                    '"alt="'.$this->dof->get_string('view_week_template_on_student', $this->get_im()).
                                    '" title="'.$this->dof->get_string('view_week_template_on_student', $this->get_im()).'">'.'</a>';
                        }
                        if ( !isset($person->middlename) )
                        {
                            $person->middlename = '';
                        }
                        $this->data[] = array($person->lastname,$person->firstname,$person->middlename,$link);
                    }
                    $table .= $this->print_table('person');
                }else
                {// ничего не нашли - выведем сообщение
                    $table1->data = array(array($this->dof->get_string
                           ('no_list_students', $this->get_im())));
                }
                $string .= $this->dof->modlib('widgets')->print_table($table1,true).$table; 
		    break;
		    case 'teachers': // по учителю';
		        // рисуем таблицу
                $table1 = new stdClass();
                $table1->tablealign = "center";
                $table1->cellpadding = 5;
                $table1->cellspacing = 5;
                $table1->rowclasses = $this->rowclasses;
                $table1->width = '60%';
                $table1->align = array("center");
                // шапка таблицы
                $table1->head = array($this->dof->get_string('teachers', $this->get_im()));
                $table1->data = array();
                $table = '';
		        $this->data = array();
		        $link = '';
                if ( $this->dof->storage('schtemplates')->is_access('view') )
                {// пользователь может просматривать шаблоны
                    $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid=0',$add).'>'.
                            '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                            '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                            '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
                }
		        $table1->data[] = array($this->dof->get_string('cstreams_no_teacher', 'schedule').$link);
                $datas = $this->compose_schedule_by_teachers($ageid, $daynum, $dayvar);
                if ( ! empty($datas) ) 
                {// нашли данные
                    foreach ( $datas as $person )
                    {//для каждого шаблона формируем строку
                        $link = '';
                        if ( $this->dof->storage('schtemplates')->is_access('view') )
                        {// пользователь может просматривать шаблоны
                            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid='.$person->id,$add).'>'.
                                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                                    '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                                    '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
                        }
                        if ( !isset($person->middlename) )
                        {
                            $person->middlename = '';
                        }
                        $this->data[] = array($person->lastname,$person->firstname,$person->middlename,$link);
                    }
                    $table = $this->print_table('person');
                }else
                {// ничего не нашли - выведем сообщение
                    $table1->data[] = array('<div align="center">'.$this->dof->get_string
                       ('no_list_teachers', $this->get_im()).'</div>');
                }
                $string .= $this->dof->modlib('widgets')->print_table($table1,true).$table; 
		    break;
        
        }
		//  распечатать таблицы
		return $string;

    }
    
    /** Собрать расписание для отображения по времени.
     * Функция извлекает все шаблоны по заданным параметрам, упорядочивает их по интервалам времени
     * (с утра до вечера) и сохраняет в массив объектов (пример структуры см. ниже)
     * 
     * @param int $ageid - id учебного периода для которого отображается расписание
     * @param string $displaytype - тип отображения. 3 возможных варианта:
     *                 time - отображение расписания по интервалам времени
     *                 students - отображение по ученикам
     *                 teachers - отображение по учителям
     * @param int $daynum - номер дня недели от понедельника (1) до воскресенья (7)
     * @param int $dayvar - тип учебной недели: ежедневно (0), четная (1) или нечетная (2).
     *                                Если не передана - то берется из настроек
     * @param int $time[optional] - временной интервал, за который просматривается расписание.
     *                              Передается только если выбрано отображение по времени.
     *                              Возможные значения:
     *                              0 - за все время
     *                              1 - до 9
     *                              2 - с 9 до 12
     *                              3 - с 12 до 15
     *                              4 - с 15 до 18
     *                              5 - после 18
     * 
     * @return array массив объектов, разбитый по интервалам времени, и
     * Пример объекта: Уроки с 08:00-08:30
     *      $a = new stdClass();
     *      $a->label = '08:00-08:30';
     *      $a->records = array({список шаблонов});
     */
    protected function compose_schedule_by_time($ageid, $daynum, $dayvar, $time=null, $form = 'all' )
    {
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->daynum = $daynum;
        $conds->dayvar = $dayvar;
        if ( $form != 'all')
        {
            $conds->form = $form;
        }
        $conds->ageid  = $ageid;
        $conds->status = array('active','suspend');
        
        $intervals = $this->schedule_intervals_list();
        if ( $time )
        {// передано время - ищем шаблоны на промежутке
            
            // @todo поскольку шаблоны привязаны к дням а не ко времени, то сейчас мы просто
            // не показываем все "вышедшие за границы суток" уроки других часовых поясов
            // Поскольку просмотр шаблонов из другого часового пояса - редкая задача 
            // то текущее временное решение оставляем.
            // В будущем нужно будет извлекать шаблоны со смещением суток, и показывать
            // в другом дне недели те шаблоны которые действительно "уехали" в другой день
            // Например если мы просматриваем из другого часового пояса шаблоны на понедельник,
            // а они из-за смены времени сползли на вторник - то показывать их не как шаблоны понедельника
            // а как шаблоны вторника
            $conds->begintime = $this->dof->storage('schtemplates')->usertime_to_lessontime($intervals[$time]['begin']);
            $conds->endtime   = $this->dof->storage('schtemplates')->usertime_to_lessontime($intervals[$time]['end']);
        }
        if ( ! $templates = $this->dof->storage('schtemplates')->
               get_objects_list($conds,'begin ASC, duration ASC'))
        {// не нашли шаблон - плохо';
            return '';
        }
        // формируем двумерный массив данных
        $times = array();
        foreach ( $templates as $template )
        {
            // формируем имя времени
            $begintime = 
            $hours    = floor(($template->begin) / 3600);
            $minutes  = floor(($template->begin  - $hours * 3600) / 60);
            $begin    = $this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M")
                        .' ('.dof_usertimezone().')'; //время начала
            $hours    = floor(($template->begin + $template->duration) / 3600);
            $minutes  = floor(($template->begin + $template->duration - $hours * 3600) / 60);
            $nametime = $begin.' - '.$this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M")
                        .' ('.dof_usertimezone().')'; //время конца
            $times[$nametime][$template->id] = $template;
        }
        return $times;
    }
    
	/** Собрать расписание для отображения по Учителям.
     * Функция извлекает все шаблоны по заданным параметрам, упорядочивает их по ФИО учителя
     * и сохраняет в массив объектов (пример структуры см. ниже)
     * 
     * @param int $ageid - id учебного периода для которого отображается расписание

     * 
     * @return array массив объектов
     * Пример объекта: Уроки учителя "Иванов Иван Иванович"
     *      $a = new stdClass();
     *      $a->label = 'Иванов Иван Иванович';
     *      $a->records = array({список шаблонов});
     */
    protected function compose_schedule_by_teachers($ageid)
    {
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->ageid  = $ageid;
        $conds->cstreamsstatus = array('plan','active','suspend');
        $conds->appointstatus = array('plan','active');
        $conds->status = array('active','suspend');
        // для архива свой список статусов
        if ( ! $teachers = $this->dof->storage('schtemplates')->get_teachers_list($conds))
        {// не нашли шаблон - плохо';
            return '';
        }
        return $teachers;
    }
    
    /** Собрать расписание для отображения по ученикам.
     * Функция извлекает все шаблоны по заданным параметрам, упорядочивает их по ФИО ученика
     * и сохраняет в массив объектов (пример структуры см. ниже)
     * 
     * @param int $ageid - id учебного периодадля которого отображается расписание
     * @param string $displaytype - тип отображения. 3 возможных варианта:
     *                 time - отображение расписания по интервалам времени
     *                 students - отображение по ученикам
     *                 teachers - отображение по учителям
     * @param int $daynum - номер дня недели от понедельника (1) до воскресенья (7)
     * @param int $dayvar - тип учебной недели: ежедневно (0), четная (1) или нечетная (2).
     *                                Если не передана - то берется из настроек
     * 
     * @return array массив объектов, разбитый по интервалам времени, и
     * Пример объекта: Уроки учителя "Петров Петр Петрович"
     *      $a = new stdClass();
     *      $a->label = 'Петров Петр Петрович';
     *      $a->records = array({список шаблонов});
     */
    protected function compose_schedule_by_agroups($ageid, $daynum, $dayvar)
    {
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->ageid  = $ageid;
        $conds->cstreamsstatus = array('plan','active','suspend');
        $conds->status = array('active','suspend');
        // для архива свой список статусов
        if ( ! $agroups = $this->dof->storage('schtemplates')->get_groups_list($conds) )
        {// не нашли шаблон - плохо';
            return '';
        }
        return $agroups;
    }
    
    /** Собрать расписание для отображения по ученикам.
     * Функция извлекает все шаблоны по заданным параметрам, упорядочивает их по ФИО ученика
     * и сохраняет в массив объектов (пример структуры см. ниже)
     * 
     * @param int $ageid - id учебного периода для которого отображается расписание
     * @param string $displaytype - тип отображения. 3 возможных варианта:
     *                 time - отображение расписания по интервалам времени
     *                 students - отображение по ученикам
     *                 teachers - отображение по учителям
     * @param int $daynum - номер дня недели от понедельника (1) до воскресенья (7)
     * @param int $dayvar - тип учебной недели: ежедневно (0), четная (1) или нечетная (2).
     *                                Если не передана - то берется из настроек
     * 
     * @return array массив объектов, разбитый по интервалам времени, и
     * Пример объекта: Уроки учителя "Петров Петр Петрович"
     *      $a = new stdClass();
     *      $a->label = 'Петров Петр Петрович';
     *      $a->records = array({список шаблонов});
     */
    protected function compose_schedule_by_students($ageid, $daynum, $dayvar)
    {
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->ageid  = $ageid;
        $conds->cstreamsstatus = array('plan','active','suspend');
        $conds->cpassedstatus = array('plan','active','suspend');
        $conds->status = array('active','suspend');
        // для архива свой список статусов
        if ( ! $students = $this->dof->storage('schtemplates')->get_individual_students_list($conds) )
        {// не нашли шаблон - плохо';
            return '';
        }
        return $students;
    }    
    /** Распечатать фрагмен расписания (например, для одного временного промежутка, одного ученика или одного учиителя)
     * 
     * @param string $displaytype - тип отображения. 3 возможных варианта:
     *                 time - отображение расписания по интервалам времени
     *                 students - отображение по ученикам
     *                 teachers - отображение по учителям
     * @param integer $daynum - на какой день недели отображаем(по умолчанию - ежедневно)
     * @param object $part - объект, полученный из функции 
     * 						 compose_schedule_by_students,
     * 						 compose_schedule_by_teachers или
     * 						 compose_schedule_by_time
     *  					 Пример:
     * 						 $a->label = 'Подпись к таблице';
     *  					 $a->records = array({массив записей из таблицы schtemplates})
     * @return null Эта функция не возвращает значений, просто выводит данные на экран
     */
    protected function print_schedule_part($displaytype, $part, $dayvar=0)
    {
        $header = '<br><div align="left"><b>'.$part->label.'</b></div><br>';
        switch ( $displaytype )
        {
		    case 'time':// по времени
		        $this->data = array();
		        foreach ( $part->records as $template )
                {//для каждого шаблона формируем строку
                    // статус УДАЛЕН - не отображаем
                    if ( $template->status != 'deleted'  )
                    {
                        $this->data[] = $this->get_string_time($template, $dayvar);
                        if ( $dayvar != $template->dayvar )
                        {
                            $this->rowclasses[] = 'mismatch_department';
                        }else
                        {
                            $this->rowclasses[] = '';
                        }
                    }
                }
                return $header.$this->print_table('time');
		        
		    break;
		    case 'students':// по ученику
		        $this->data = array();
		        // @todo отображение берем для потока,т.к таблицы индетичны
                // если что-то изменится, потом напишем отдельный метод
		        foreach ( $part->records as $template )
                {//для каждого шаблона формируем строку
                    $this->data[] = $this->get_string_cstream($template);
                }
                return $header.$this->print_table('student');
		    break;
		    case 'teachers':// по учителю
		        $this->data = array();
		        foreach ( $part->records as $template )
                {//для каждого шаблона формируем строку
                    $this->data[] = $this->get_string_teacher($template);
                }
                return $header.$this->print_table('teacher');
		    break;
        
		}
		// никак не отображать
		return '';
    }
    
    public function get_help_value_color()
    {
        return '<b>'.$this->dof->get_string('value_color', $this->get_im()).':</b>'.
               '<div id=mismatch_department>- '.$this->dof->get_string('color_department', $this->get_im()).'.</div>'.
               '<div id=mismatch_age>- '.$this->dof->get_string('color_age', $this->get_im()).'.</div>'.
               '<div id=mismatch_age_department>- '.$this->dof->get_string('color_age_department', $this->get_im()).'.</div>';
               '<div id=mismatch_timezone>- '.$this->dof->get_string('color_timezone', $this->get_im()).'.</div>';
    }
    
    public function get_template_week_days()
    {
        return $this->dof->storage('ages')->get_list_daynums($this->ageid);
    }
    
}



/** Класс для отображения информации о нагрузке по шаблонам
 * 
 */
class dof_im_schedule_master_make
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $data; // данные для построения таблицы по нагрузке
    private $templatedepid; // id подразделения, к которому принадлежат шаблоны
    private $csdepartids; // массив подразделений потоков
    private $ageid; // период, для которого рисуется таблица
    private $addvars; // массив get-параметров, которые добавляются к ссылкам
    private  $aaa;
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @param int $departmentid - id подразделения в таблице departments
     * @param int $templatedepid - id шаблона, для которого отображается нагрузка
     * @param array $csdepartids - массив id из таблицы departments
     * @param array $addvars - массив get-параметров, которые добавляются к ссылкам
     * @access public
     */
    public function __construct($dof,$templatedepid,$csdepartids,$addvars)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        $this->templatedepid = $templatedepid;
        $this->csdepartids = $csdepartids;
        $this->ageid = $addvars['ageid'];
        $this->addvars       = $addvars;
    }
    
   	/** Возвращает код im'а, в котором хранятся отслеживаемые объекты
     * @return string
     * @access private
	 */
	private function get_im()
	{
		return 'schedule';
	}    
    
    
   /** Возвращает html-код таблицы
     * @param string $type - тип отображения данных:
     *                        underload - недогруженные
     *                        
     * @return string - html-код или пустая строка
     */
    protected function print_table($type)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
        switch ( $type )
		{
            case 'underload': // для недогруза
                //$table->wrap = array (true);
                //$table->size = array ('50px','150px','150px','200px','150px','100px');
                $table->align = array("center","center","center","center","center",
                                      "center","center");
            break; 
            case 'miniunderload': // для мининедогруза
                //$table->wrap = array (true);
                //$table->size = array ('50px','150px','150px','200px','150px','100px');
                $table->align = array("center","center","center");
            break; 
            case 'intersection': // для пересечения
                $table->align = array("center","center","center","center","center","center","center");
            break;
		}
        
        // шапка таблицы
        $table->head = $this->get_header($type);
        // заносим данные в таблицу     
        $table->data = $this->data;
        return $this->dof->modlib('widgets')->print_table($table,true);        
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * @param string $type - тип отображения данных:
     *                        underload - недогруженные
     * 
     * @return array
     */
    private function get_header($type)
    {
        switch ( $type )
		{
		    // недогруз
			case 'underload':
            return array($this->dof->get_string('cstream_name', $this->get_im()),
                         $this->dof->get_string('item', $this->get_im()),
                         $this->dof->get_string('teacher', $this->get_im()),
                         $this->dof->get_string('hours', $this->get_im(),'<br>'),
                         $this->dof->get_string('hoursinternally', $this->get_im(),'<br>'),
                         $this->dof->get_string('hoursdistance', $this->get_im(),'<br>'),
                         $this->dof->modlib('ig')->igs('actions'));
            break;
            //мининедогруз - для отображения на страницах просмотра шаблонов по потоку
            case 'miniunderload':
            return array($this->dof->get_string('hours', $this->get_im()),
                         $this->dof->get_string('hoursinternally', $this->get_im()),
                         $this->dof->get_string('hoursdistance', $this->get_im()));
            break;
            // пеерсечение
            case 'intersection':
            return array($this->dof->get_string('time_lesson', $this->get_im(),'<br>'),
                         $this->dof->get_string('item', $this->get_im()),
                         $this->dof->get_string('teacher', $this->get_im()),
                         $this->dof->get_string('students', $this->get_im()),
                         $this->dof->get_string('dayvar', $this->get_im()),
                         $this->dof->get_string('daynum', $this->get_im()),
                         $this->dof->modlib('ig')->igs('actions'));
            break;            
		}
    }
    
    /** Распечатать таблицу для отображения шаблонов по потоку
     * @param string $type - тип отображения данных:
     *                        underload - недогруженные
     *                        overload - перегруженные
     * @return string
     */
    public function get_table_load()
    {
        $table = '';
        // получаем все потоки из периода по статусу и подразделениям
        $cstreams = $this->dof->storage('cstreams')->get_records(array('ageid'=>$this->ageid,'status'=>array('plan','active'),'departmentid'=>$this->csdepartids),'name ASC');
        // рисуем таблицу
        $table1 = new stdClass();
        $table1->tablealign = "center";
        $table1->cellpadding = 5;
        $table1->cellspacing = 5;
        $table1->width = '100%';
        $table1->align = array("center");
        // шапка таблицы
        $table1->head = array($this->dof->get_string('cstreams_load_bad', $this->get_im()));
        // есть поток - работаем
        if ( $cstreams )  
        {
            
            $this->data = array();
            // переменная underload/overload
            $loaddata = array();
            foreach ( $cstreams as $cstream )
            {// перебираем все потоки
                // тут идет проверка есть ли права
                // на ЭТОТ поток. НЕТУ -  в лес, не покажем ему
                if ( ! $this->dof->storage('cstreams')->is_access('view', $cstream->id) )
                {
                    continue;
                }
                $hours = $this->get_hours_templates($cstream);
                // неверная нагрузка
                if ( ($cstream->hoursweekinternally != $hours->nor) OR 
                     ($cstream->hoursweekdistance != $hours->dis)
                     OR (int)$cstream->hoursweek != ($hours->nor+$hours->dis) )
                {
                    $loaddata[] = $this->get_string_underload($cstream,$hours->dis,$hours->nor);
                }
                 
            }
            $this->data = $loaddata;
            if ( $loaddata )
            {// есть ланные после проверок - показываем их
                $table .= $this->dof->modlib('widgets')->print_table($table1,true);
                $table .= $this->print_table('underload');
            }
        }
        return $table;
    }

    /** Получить список учеников, (краткий - при наведении мыши - полный), относящихся к шаблону
     * 
     * @param int $cstreamid - id потока, для которого получаются ученики
     * 
     * @return string - строка со списком учеников
     */
    protected function get_list_students($cstreamid)
    {
        $add = $this->addvars;
        $add['departmentid'] = $this->addvars['departmentid'];  
        // удалим лишнее
        unset($add['cstreamid']);
        unset($add['teacherid']);
        unset($add['studentid']);
        unset($add['agroupid']);  
        $student = array();
        if ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$cstreamid,'status'=>array('active','plan','suspend'))) )
        {// если есть ученики - покажем их
            foreach ( $cpassed as $cpass )
            {// каждого
                $add['studentid'] = $cpass->studentid;
                $student[] = $this->dof->storage('persons')->get_fullname($cpass->studentid).
                         '<br><a href="'.$this->dof->url_im($this->get_im(), '/view_week.php',$add).'">
                         <img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').'"
                         alt=  "'.$this->dof->get_string('view_week_template_on_student', $this->get_im()).'" 
                         title="'.$this->dof->get_string('view_week_template_on_student', $this->get_im()).'" /></a>';

            }
        }
        return implode('<br>',$student);
    }    
    
    /** Возвращает таблицу недогруза для одного потока
     * @param int $cstreamid - id потока из таблицы cstreams
     * @param bool $flag - отобрадать или нет сылку на удаление шаблона(для станицы week.php) 
     * @return string - html-код или пустая строка
     */
    public function get_underload_cstream($cstreamid, $flag=false)
    {
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
	    {// поток не найден - часов нет
	        return '';
	    }
        
        // получим кол-во часов
        $hours = $this->get_hours_templates($cstream);
        $this->data = array();
        if ( ($cstream->hoursweekinternally != $hours->nor) OR 
             ($cstream->hoursweekdistance != $hours->dis) 
             OR (int)$cstream->hoursweek != ($hours->nor+$hours->dis)  )
        {// недобор
            $this->data[] = $this->get_string_mini_underload($cstream,$hours->dis,$hours->nor,$flag);
        }else
        {//недогруза нет
            return '';
        }
        return $this->header_name_table('cstream_load').$this->print_table('miniunderload');
    }
    
    /** Получает строку для мини отображения по потоку
     * @param object $obj - объект шаблона из таблицы $templates
     * @param int $hour_dis - количество часов дистанционно
     * @param int $hour_nor - количество часов очно
     * @param bool $flag - отобрадать или нет сылку на удаление шаблона(для станицы week.php) 
     * @return string
     */
    private function get_string_mini_underload($obj,$hour_dis,$hour_nor,$flag=false)
    {
        $add = $this->addvars;
        // $add['departmentid'] = $this->departmentid;        
        // часов всего
        $hourcs_all = (int)$obj->hoursweek;
        $hourtem_all = $hour_nor+$hour_dis;
        if ( $hourcs_all != $hourtem_all )
        {// выдели цветом если полные часы не равны
            $hourtem_all = $this->get_color($hourtem_all);
        }
        $string[] =  $hourcs_all.'/'.$hourtem_all;
        // часов очно
        $hourcs_nor = $this->dof->storage('cstreams')->hours_int($obj->hoursweekinternally);
        if ( $hourcs_nor > $hour_nor )
        {
            $hour_nor =  $this->get_color($hour_nor).' <a href='.
                         $this->dof->url_im($this->get_im(), '/edit.php?cstreamid='.$obj->id,$add).
           				 '><img src="'.$this->dof->url_im($this->get_im(), '/icons/add.png').
                         '"alt="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).
                         '" title="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).'">'.'</a>';
        }elseif( $hourcs_nor < $hour_nor )
        {// перебор
            if ( $flag )
            {// покажем значек на удаление(для стр week.php)
                $hour_nor =  $this->get_color($hour_nor,'green').' <a href='.
                         $this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->id,$add).
           				 '><img src="'.$this->dof->url_im($this->get_im(), '/icons/remove.png').
                         '"alt="'.$this->dof->get_string('template_cansel', $this->get_im()).
                         '" title="'.$this->dof->get_string('template_cansel', $this->get_im()).'">'.'</a>';      
            }else
            {
                $hour_nor =  $this->get_color($hour_nor,'green');
            }    
        } 
        $string[] = $hourcs_nor.'/'.$hour_nor;
        // часов дистанционно
        $hourcs_dis = $this->dof->storage('cstreams')->hours_int($obj->hoursweekdistance);
        if ( $hourcs_dis > $hour_dis )
        {
            $hour_dis = $this->get_color($hour_dis).' <a href='.
                        $this->dof->url_im($this->get_im(), '/edit.php?formlesson=distantly&cstreamid='.$obj->id,$add). 
            			'><img src="'.$this->dof->url_im($this->get_im(), '/icons/add.png').
                        '"alt="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).
                        '" title="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).'">'.'</a>';
        }elseif( $hourcs_dis < $hour_dis )
        {// перебор 
            if ( $flag )
            {// покажем значек на удаление
                $hour_dis =  $this->get_color($hour_dis,'green').' <a href='.
                         $this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->id,$add).
           				 '><img src="'.$this->dof->url_im($this->get_im(), '/icons/remove.png').
                         '"alt="'.$this->dof->get_string('template_cansel', $this->get_im()).
                         '" title="'.$this->dof->get_string('template_cansel', $this->get_im()).'">'.'</a>';      
            }else
            {
                $hour_dis =  $this->get_color($hour_dis,'green');
            }             
        }
        $string[] = $hourcs_dis.'/'.$hour_dis;        
        return $string;
    }
    
    /** Получает строку для отображения по потоку
     * @param object $obj - объект из таблицы $cstreams
     * @param int $hour_dis - количество часов дистанционно
     * @param int $hour_nor - количество часов очно
     * @return string
     */
    private function get_string_underload($obj,$hour_dis,$hour_nor)
    {
        $add = $this->addvars;
       // $add['departmentid'] = $this->departmentid;        
        $string   = array();
        $editlink = '';
        if ( $this->dof->storage('cstreams')->is_access('edit', $obj->id) )
        {// ссылка на редактирование потока         
            $editlink .= '<br><a href='.$this->dof->url_im('cstreams','/edit.php?cstreamid='.
                $obj->id,$this->addvars).'>'.
                '<img src="'.$this->dof->url_im($this->get_im(), '/icons/edit.png').
                '"alt="'.$this->dof->get_string('edit_cstream', $this->get_im()).
                '" title="'.$this->dof->get_string('edit_cstream', $this->get_im()).'">'.'</a>';  
        }  
        if ( ! $this->dof->storage('cstreams')->is_access('view', $obj->id) )
        {
            $string[] = $this->dof->storage('cstreams')->change_name_cstream($obj).$editlink; 
        }else
        {
            $string[] = '<a href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.
                $obj->id,$this->addvars).' title="'.$this->dof->get_string('view_cstream', $this->get_im()).'">'.
                $this->dof->storage('cstreams')->change_name_cstream($obj).
                '</a>'.$editlink;//имя потока
        }

        $pitemid  = $this->dof->storage('cstreams')->get_field($obj->id,'programmitemid');
        $string[] = $this->dof->storage('programmitems')->get_field($pitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($pitemid,'code').']'; 
                    // предмет
        if ( $obj->appointmentid )
        {// учителя вычисляем из сотрудника
            $person = $this->dof->storage('appointments')->get_person_by_appointment($obj->appointmentid);
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view'))
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid='.$person->id,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->storage('persons')->get_fullname($person->id).$link; // учитель 
        }else
        {// вакансия
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view'))
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid=0',$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->get_string('cstreams_no_teacher', $this->get_im()).$link; 
        }
        // часов всего
        $hourcs_all = (int)$obj->hoursweek;
        $hourtem_all = $hour_nor+$hour_dis;
        if ( $hourcs_all != $hourtem_all )
        {// выдели цветом если полные часы не равны
            $hourtem_all = $this->get_color($hourtem_all);
        }
        $string[] =  $hourcs_all.'/'.$hourtem_all;
        // часов очно
        $hourcs_nor = $obj->hoursweekinternally;
        if ( $hourcs_nor > $hour_nor )
        {// добавление шаблона
            $hour_nor =  $this->get_color($hour_nor).' <a href='.
                         $this->dof->url_im($this->get_im(), '/edit.php?cstreamid='.$obj->id,$add).
           				 '><img src="'.$this->dof->url_im($this->get_im(), '/icons/add.png').
                         '"alt="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).
                         '" title="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).'">'.'</a>';
        }elseif( $hourcs_nor < $hour_nor )
        {// удаление шаблонов
             $hour_nor =  $this->get_color($hour_nor,'green').' <a href='.
                         $this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->id,$add).
           				 '><img src="'.$this->dof->url_im($this->get_im(), '/icons/remove.png').
                         '"alt="'.$this->dof->get_string('template_cansel', $this->get_im()).
                         '" title="'.$this->dof->get_string('template_cansel', $this->get_im()).'">'.'</a>';           
        }
        $string[] = $hourcs_nor.'/'.$hour_nor;
        // часов дистанционно
        $hourcs_dis = $obj->hoursweekdistance;
        if ( $hourcs_dis > $hour_dis )
        {// добавление шаблона
            $hour_dis = $this->get_color($hour_dis).' <a href='.
                        $this->dof->url_im($this->get_im(), '/edit.php?formlesson=distantly&cstreamid='.$obj->id,$add). 
            			'><img src="'.$this->dof->url_im($this->get_im(), '/icons/add.png').
                        '"alt="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).
                        '" title="'.$this->dof->get_string('new_template_on_cstream', $this->get_im()).'">'.'</a>';
        }elseif( $hourcs_dis < $hour_dis )
        {// удаление шаблонов
            $hour_dis = $this->get_color($hour_dis,'green').' <a href='.
                        $this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->id,$add). 
            			'><img src="'.$this->dof->url_im($this->get_im(), '/icons/remove.png').
                        '"alt="'.$this->dof->get_string('template_cansel', $this->get_im()).
                        '" title="'.$this->dof->get_string('template_cansel', $this->get_im()).'">'.'</a>';            
        }
        $string[] = $hourcs_dis.'/'.$hour_dis;        

        // действия
        $link = ''; 
        // @todo проверить право на просмотр cpassed
        $link .= '<a href="'.$this->dof->url_im('cpassed', '/list.php?cstreamid='.$obj->id,
                 array('departmentid'=>$add['departmentid'])).'">'.
                 '<img src="'.$this->dof->url_im($this->get_im(), '/icons/students.png').
                 '"alt=  "'.$this->dof->get_string('cpassed', $this->get_im()).
                 '"title="'.$this->dof->get_string('cpassed', $this->get_im()).'" /></a>&nbsp;';
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может просматривать шаблоны
            $link .= '<a href='.$this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).'">'.'</a>';
        }
        $string[] = $link; // действия - законные, незаконные караются законом 
        return $string;
    }
    
    /** задать цвет 
     * @param string $color - задающий цвет отображения(по умолчанию красный)
     * @param string $text - текст, который надо закрасить
     * @return string
     */
    private function get_color($text, $color='red')
    {
        $text = '<span style="color:'.$color.'"><b>'.$text.'</b></span>';
        return $text;
    }

    /** Возвращает кол-во часов потока по шаблонам
     * @param int|object $cstream - id|объект потока из таблицы cstreams
     * @return object         
     *  $hour->dis - часов дистанционно
     *  $hour->nor - часов очно
     */
    public function get_hours_templates($cstream)
    {
        // формируем объект
        $hours = new stdClass();
        $hours->dis = 0;
        $hours->nor = 0; 
        if ( ! is_object($cstream) AND ! $cstream = $this->dof->storage('cstreams')->get($cstream) )
	    {// поток не найден - часов нет
	        return $hours;
	    }
        $hour_dis = 0;
        $hour_nor = 0;  
        // выбираем все шаблоны для этого потока
        if ( ! $templates = $this->dof->storage('schtemplates')->get_records(array('cstreamid'=>$cstream->id,'status'=>'active')) )
        {// нет шаблонов - часов нет
            return $hours; 
        }
        // подсчитаем часы для этого потока
        foreach ( $templates as $template )
        {
            if ( $template->form == 'distantly' )
            {//дистанционно
                if ( $template->dayvar == '0' )
                {// ежедневно
                    $hour_dis += $template->duration;
                }else 
                {// четная/нечетная поому и делим на 2
                    $hour_dis += ($template->duration)/2;    
                }
            }else 
            {// очно
                if ( $template->dayvar == '0' )
                {// ежедневно
                    $hour_nor += $template->duration;
                }else 
                {// четная/нечетная потому и делим на 2
                    $hour_nor += ($template->duration)/2;    
                }
            }
        }    
        // получиил для 1 потока ВСЕ время по его шаблонам очна,дистанционно
        // теперь сравнивая время определим поток в перегруз или не догруз
        // для этого время ещё разделим на продолжительность академ часа , взятого из настроек "ahourduration"
        $hour_akadem = $this->dof->storage('config')->get_config
                ('ahourduration', 'storage', 'schtemplates', $cstream->departmentid);
        if ( isset($hour_akadem->value) )
        {// время из настройки
            $hour_akadem = $hour_akadem->value;
        }else
        {// настройки не оказалось запишем 45 минут
            $hour_akadem = 2700;
        }
        // перепишем наши времена на академ часы
        // округляем до десятых
        $hours->dis = round(($hour_dis / $hour_akadem), 1);
        $hours->nor = round(($hour_nor / $hour_akadem), 1); 
        return $hours;
    }
    
    /** Получает строку для отображения пересечения
     * @param object $obj - объект шаблона из таблицы $templates
     * @return string
     */
    private function get_string_intersection($obj)
    {
        $add = $this->addvars;
        $string   = array();
        //время 
        $hours    = floor($obj->begin / 3600);
        $minutes  = floor(($obj->begin - $hours * 3600) / 60);
        $begin    = $this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M"); //время начала
        $hours    = floor(($obj->begin + $obj->duration) / 3600);
        $minutes  = floor(($obj->begin + $obj->duration - $hours * 3600) / 60);
        $string[] = $begin.' - '.$this->dof->storage('persons')->get_userdate(mktime($hours, $minutes),"%H:%M"); //время конца
        // предмет 
        $pitemid  = $this->dof->storage('cstreams')->get_field($obj->cstreamid,'programmitemid');
        $string[] = $this->dof->storage('programmitems')->get_field($pitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($pitemid,'code').']'; 
        // учитель  
               
        if ( $appointment = $this->dof->storage('cstreams')->get_field($obj->cstreamid, 'appointmentid') )
        {// учителя вычисляем из сотрудника
            $person = $this->dof->storage('appointments')->get_person_by_appointment($appointment);

            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view'))
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid='.$person->id,$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->storage('persons')->get_fullname($person->id).$link; // учитель 
        }else
        {// вакансия
            $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view'))
            {// пользователь может просматривать шаблоны
                $link .= ' <br><a href='.$this->dof->url_im($this->get_im(),'/view_week.php?teacherid=0',$add).'>'.
                        '<img src="'.$this->dof->url_im($this->get_im(), '/icons/show_schedule_week.png').
                        '"alt="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).
                        '" title="'.$this->dof->get_string('view_week_template_on_teacher', $this->get_im()).'">'.'</a>';
            }
            $string[] = $this->dof->get_string('cstreams_no_teacher', $this->get_im()).$link; 
        }
        // ученики
        $string[] = $this->get_list_students($obj->cstreamid); 
        // тип недели(четная, нечетная...)        
        $days = $this->dof->modlib('refbook')->get_day_vars();
        $string[] = $days[$obj->dayvar];
        // день недели
        $week_days = $this->dof->storage('ages')->get_list_daynums($this->ageid);
        $string[] = $week_days[$obj->daynum];
        // действия
        $link = ''; 
        if ( $this->dof->storage('schtemplates')->is_access('edit',$obj->id) )
        {// пользователь может редактировать шаблон
            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/edit.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/edit.png').
                    '"alt="'.$this->dof->get_string('edit_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('edit_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view',$obj->id) )
        {// пользователь может просматривать шаблон
            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view.php?id='.$obj->id,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view.png').
                    '"alt="'.$this->dof->get_string('view_template', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_template', $this->get_im()).'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может просматривать шаблоны
            $link .= ' <a href='.$this->dof->url_im($this->get_im(),'/view_week.php?cstreamid='.$obj->cstreamid,$add).'>'.
                    '<img src="'.$this->dof->url_im($this->get_im(), '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', $this->get_im()).'">'.'</a>';
        }        
        
        $string[] = $link;
        return $string;
    }
    
    /** Получить информацию о пересечении с другими шаблонами(учителя)
     * @param int $templateid - id шаблона из таблицы schtemplates
     * @return string
     */
    public function cross_with_others_templates_teacher($templateid)
    {
        // проверка на существование шаблона
        if ( ! $template = $this->dof->storage('schtemplates')->get($templateid) )
        {
            return '';
        }

        return $this->dof->storage('schtemplates')->get_select_templater_teachers($template);
    }
    
    /** Получить информацию о пересечении с другими шаблонами(ученики)
     * @param int $templateid - id шаблона из таблицы schtemplates
     * @return string
     */
    public function cross_with_others_templates_student($templateid)
    {
        // проверка на существование шаблона
        if ( ! $template = $this->dof->storage('schtemplates')->get($templateid) )
        { 
            return '';
        }

        return $this->dof->storage('schtemplates')->get_select_templater_students($template);
    }    
    
    
    /** Выводит пересечение шаблонов по ученкам и учителям, если таковы есть
     * Если передан параметр, то ищет ТОЛЬКО для 1 шаблона пересечения
     * @param integer $id  - ud шаблона
     * return bool (true - есть перeсечения)
     */ 
    public function show_cross_templates($id=0)
    {
        $conds = new stdClass();
        $conds->departmentid = $this->templatedepid;
        $conds->ageid  = $this->ageid;
        $conds->status = array('active');
        $conds->cstreamsstatus = array('plan','active');
        if ( ! $id )
        {// не передали - ищем каждый с каждым
            if ( ! $templates = $this->dof->storage('schtemplates')->
                   get_objects_list($conds))
            {// не нашли шаблон - плохо';
                return false;
            }
        }else 
        {
            $templates = array();
            if ( ! $templates[] = $this->dof->storage('schtemplates')->get($id) )
            {
                return false;
            }
        }    
        // перебираем все шаблоны и если есть пересечение - покажем его
        $this->data = array();
        foreach ( $templates as $template )
        {
            $rez = '';
            // получеам пересечения для учеников и учителей
    		if ( $student = $this->cross_with_others_templates_student($template->id) )
    		{
    		    // заголовок для пересечения сдунентов
    		    $rez .= $this->header_name_table('intersection_student');
    		    $this->data = array();
    		    foreach ( $student as $st )
    		    {
    		        $this->data[] = $this->get_string_intersection($st);
    		    }
                // таблица пересечений    
    		    $rez .= $this->print_table('intersection').'<br>';

    		}	

    		if ( $teacher = $this->cross_with_others_templates_teacher($template->id) )
            {
                $rez .= $this->header_name_table('intersection_teacher');
                $this->data = array();
    		    foreach ( $teacher as $tc )
    		    {
    		        $this->data[] = $this->get_string_intersection($tc);
    		    }
    		    $rez .= $this->print_table('intersection');
    		}
    		
    		if ( $rez )
    		{// есть пересечение
    		    $this->data = array();
    	        $this->data[] = $this->get_string_intersection($template);
    	        // добавим рамку - блок
    	        $this->dof->modlib('widgets')->print_box_start();
    	        // добавим САМ шаблон, для которого искали пересечения и выведем
                echo $rez = $this->header_name_table('template_see').$this->print_table('intersection').'<br>'.$rez;
                // закроем рамку-блок
                $this->dof->modlib('widgets')->print_box_end();
                echo  '<br>';
            }
         	
        }
        if ( $this->data )
        {
            return true;
        }
        return false;
    }     
    
    
    /** Получить сообщении о состоянии учебного процесса 
     *  (неправильное количество часов или пересечение по времени)
     * @param string $name - заголовок таблицы
     * @return string заголовок для сообщения о количес
     */
    private function header_name_table($name)
    {            
        if ( $name == 'template_see' )
        {// выделяем просматривамый шаблон
            return $this->dof->modlib('widgets')->
                error_message($this->dof->get_string($name, $this->get_im()));
        }else 
        {
            return $this->dof->modlib('widgets')->
                notice_message($this->dof->get_string($name, $this->get_im()));
        }    
    }     
}
