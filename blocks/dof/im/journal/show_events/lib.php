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
//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__)) . "/../lib.php");

//$DOF->modlib('nvg')->add_level($DOF->get_string('show_events', 'journal'), '');

class dof_im_journal_show_events
{

    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * массив структуры:
     * = array(
     *   [departmentid] => obj  -> departmentname = 'department_name'
     *                     obj  -> programms = array(
     *   [programmid]   => obj1 -> programmname = 'programm_name'
     *                     obj1 -> ages = array(
     *   [agenum]       => obj2 -> agename = 'age_name'
     *                     obj2 -> items = array(
     *   [itemid]       => obj3 -> itemname = 'item_name'
     *                     obj3 -> cstreams = array(
     *   [cstreamid]    => obj4 -> cstreamname = 'cstream_name'
     *                                              )
     *                                            )
     *                                           )
     *                                               )
     *         )
     * содержит данные для вставку в темплатер после добавления еще одного уровня:
     * $fortemplater->departments = $this->departments;
     * @var array
     */
    private $personid;
    private $date;
    private $datatime;
    private $depid;

    public function __construct(dof_control $dof, $depid = 0)
    {
        $this->dof = $dof;
        $this->depid = $depid;
    }

    /**
     * Заполняет $this->datatime начальной информацией
     * 
     * @param int $departmentid - id подразделения
     * @return bool true, если все нормально или 
     * false в ином случае
     */
    public function set_data($date = 0, $teacherid = 0, $studentid = 0)
    {
        if ( !$date )
        {//получаем журналы одного подразделения
            $date              = array();
            $date['date_from'] = time(); 
            $date['date_to']   = time();
        }
        // запомним остальные параметры
        $datatime = new stdClass();
        if ( $teacherid != 0 )
        {
            $datatime->teacherid = $teacherid;
            $this->personid      = $teacherid;
        }
        // передали студента
        if ( $studentid != 0 )
        {
            $datatime->studentid = $studentid;
            $this->personid      = $studentid;
        }

        $datatime->date_from = $date['date_from'];
        $datatime->date_to   = $date['date_to'];
        $this->datatime      = $datatime;
        //раз сюда дошли - значит все в порядке
        return true;
    }

    /**
     * Получаем строку для вывода одного события
     * 
     * @param object $event - объект события
     * @param bool $show_all - вывод подробной таблицы
     * @return array - массив для строчки события
     */
    public function get_string_event($event, $show_all = false)
    {
        // деламе ХУК, если есть у потока подразделение - укажем строго его
        if ( !$depid = optional_param('departmentid', 0, PARAM_INT) )
        {
            $depid = $this->dof->storage('cstreams')->get_field($event->cstreamid, 'departmentid');
        }
        $addvars                 = array();
        $addvars['departmentid'] = $depid;
        $date  = dof_userdate($event->date, '%d-%m-%Y') . '<br>';
        $date .= dof_userdate($event->date, '%H:%M');
        $student  = array();
        $presents = array();
        $grades   = array();
        if ( $cpassed = $this->dof->storage('cpassed')->get_records(array(
            'cstreamid' => $event->cstreamid, 'status' => array('active', 'completed', 'suspend', 'failed'))) )
        {// если есть на урок ученики - покажем их
            foreach ( $cpassed as $cpass )
            {// каждого
                $link = '';
                if ( $this->dof->storage('schtemplates')->is_access('view') )
                {// можно просматривать шаблон - добавим ссылку на просмотр шаблона на неделю
                    $ageid = $this->dof->storage('cstreams')->get_field($event->cstreamid, 'ageid');
                    $link = '<a href="' . $this->dof->url_im('schedule', '/view_week.php?studentid=' . $cpass->studentid . '&ageid=' . $ageid, $addvars) .
                            '"><img src="' . $this->dof->url_im('journal', '/icons/show_schedule_week.png') . '"
                             alt=  "' . $this->dof->get_string('view_week_template_on_student', 'journal') . '" 
                             title="' . $this->dof->get_string('view_week_template_on_student', 'journal') . '" /></a>';
                }
                //$student[] = '<a href="'.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$cpass->studentid).'&date='.$event->date.'">'.
                //             $this->dof->storage('persons')->get_fullname($cpass->studentid).'</a>';
                $student[] = '<a href="' . $this->dof->url_im('journal', '/person.php?personid=' . $cpass->studentid, $addvars) . '">' .
                        $this->dof->storage('persons')->get_fullname_initials($cpass->studentid) .
                        '</a><br><a href="' . $this->dof->url_im('journal', '/show_events/show_events.php?personid=' . $cpass->studentid, $addvars) .
                        '&date_to=' . $this->datatime->date_to . '&date_from=' . $this->datatime->date_from . '">
                             <img src="' . $this->dof->url_im('journal', '/icons/events_student.png') . '"
                             alt=  "' . $this->dof->get_string('view_events_student', 'journal') . '" 
                             title="' . $this->dof->get_string('view_events_student', 'journal') . '" /></a>' . $link;
                $presresult = $this->dof->storage('schpresences')->get_present_status($cpass->studentid, $event->id);
                if ( $presresult === '1' )
                {// ученик присутствовал
                    $presents[] = $this->dof->get_string('yes_present', 'journal') . '<br>';
                } elseif ( $presresult === '0' )
                {// ученик отсутствовал
                    $presents[] = $this->dof->get_string('no_present', 'journal') . '<br>';
                } else
                {// нет данных о посещаемости
                    $presents[] = $this->dof->get_string('no_mark', 'journal') . '<br>';
                }
                
                // Получение оценок по контрольной точке
                $controlpoint_grades = $this->dof->storage('cpgrades')->get_records(['planid' => $event->planid, 'cpassedid' => $cpass->id]);
                if ( ! empty($controlpoint_grades) )
                {// Оценки получены
                    $string = [];
                    // Формирование строки с оценками по контрольной точке для подписки
                    foreach ( $controlpoint_grades as $grade )
                    {
                        $string[] = $grade->grade;
                    }
                    $string = implode('/', $string);
                    // Добавление данных об оценках
                    $grades[] = $string . '<br>';
                } else
                {
                    $grades[] = '<br>';
                }
                
            }
        }
        // формируем строку таблицы
        $teacher = $this->dof->storage('persons')->get_fullname($event->teacherid) . '</a>' .
                ' <a href="' . $this->dof->url_im('journal', '/show_events/show_events.php?personid=' . $event->teacherid .
                        '&date_to=' . $this->datatime->date_to . '&date_from=' . $this->datatime->date_from, $addvars) . '">
                   <br><img src="' . $this->dof->url_im('journal', '/icons/events_student.png') . '"
                   alt=  "' . $this->dof->get_string('view_events_teacher', 'journal') . '" 
                   title="' . $this->dof->get_string('view_events_teacher', 'journal') . '" /></a>';
        $link = '';
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// можно просматривать шаблон - добавим ссылку на просмотр шаблона на неделю
            $ageid = $this->dof->storage('cstreams')->get_field($event->cstreamid, 'ageid');
            $teacher .= '<a href="' . $this->dof->url_im('schedule', '/view_week.php?teacherid=' . $event->teacherid . '&ageid=' . $ageid, $addvars) .
                    '"><img src="' . $this->dof->url_im('journal', '/icons/show_schedule_week.png') . '"
                     alt=  "' . $this->dof->get_string('view_week_template_on_teacher', 'journal') . '" 
                     title="' . $this->dof->get_string('view_week_template_on_teacher', 'journal') . '" /></a>';
        }
        //$teacher = '<a href="'.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$event->teacherid).'&date='.$event->date.'">'.
        //           $this->dof->storage('persons')->get_fullname($event->teacherid).'</a>';
        $student  = implode('<br>',$student);
        $presents = implode('<br>',$presents);
        $grades   = implode('<br>',$grades);
        
        $item = $this->dof->storage('programmitems')->get_field($event->programmitemid,'name').'<br>['.
                $this->dof->storage('programmitems')->get_field($event->programmitemid,'code').']';
        $theme = '';
        if ( $string = $this->dof->storage('plans')->get_field(array('id' => $event->planid), 'name') )
        {// обрежем название темы
            $theme = "<span title='$string'>";
            $theme .= mb_substr($string, 0, 27, 'UTF-8').'...</span>';    
        }
        // действия
        $action = '<a href="'.$this->dof->url_im('journal', '/group_journal/index.php?csid='.$event->cstreamid,$addvars).'">';
        $action .= '<img src="'.$this->dof->url_im('journal', '/icons/journal.png').'"
            alt=  "'.$this->dof->get_string('group_journal', 'journal').'" 
            title="'.$this->dof->get_string('group_journal', 'journal').'" /></a>&nbsp;';
        if ( $this->dof->im('plans')->is_access('viewthemeplan',$event->cstreamid) OR
             $this->dof->im('plans')->is_access('viewthemeplan/my',$event->cstreamid) )
        {
            $action .= '<a href="'.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=cstreams&linkid='.$event->cstreamid,$addvars).'">';
            $action .= '<img src="'.$this->dof->url_im('journal', '/icons/plancstream.png').'"
                alt=  "'.$this->dof->get_string('view_plancstream', 'journal').'" 
                title="'.$this->dof->get_string('view_plancstream', 'journal').'" /></a>&nbsp;';
            $action .= '<a href="'.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=plan&linkid='.$event->cstreamid,$addvars).'">';
            $action .= '<img src="'.$this->dof->url_im('journal', '/icons/iutp.png').'"
                alt=  "'.$this->dof->get_string('view_iutp', 'journal').'" 
                title="'.$this->dof->get_string('view_iutp', 'journal').'" /></a>&nbsp;';
        }
        if ( $this->dof->im('journal')->is_access('replace_schevent',$event->id) )
        {
            $action .= '<a href="'.$this->dof->url_im('journal', '/group_journal/replace.php?eventid='.$event->id,$addvars).'">';
            $action .= '<img src="'.$this->dof->url_im('journal', '/icons/replace.png').'"
                alt=  "'.$this->dof->get_string('replacement', 'journal').'" 
                title="'.$this->dof->get_string('replacement', 'journal').'" /></a>&nbsp;';
        }
        if ( $this->dof->workflow('schevents')->is_access('changestatus:to:canceled',$event->id) )
        {
            $action .= $this->dof->modlib('ig')->icon('delete',
                    $this->dof->url_im('journal', '/group_journal/cancel_event.php?id='.$event->id.'&personid='.$event->teacherid.
                    '&date_to='.$this->datatime->date_to.'&date_from='.$this->datatime->date_from,$addvars),
                    array('title'=>$this->dof->get_string('lesson_cancel', 'journal')));
        }
        $statusname = $this->dof->workflow('schevents')->get_name($event->status);
        $type = '';
        if ( $event->form == 'internal' ) 
        {
            $type  = $this->dof->get_string('internal', 'journal');
        }elseif ( $event->form == 'distantly' ) 
        {
            $type  = $this->dof->get_string('distantly', 'journal');
        }
        if ( $show_all )
        {
        	return array($date,$teacher,$item,$theme,$student,$type,$presents,$grades,
        	             $event->ahours,$event->salfactor,$statusname,$action);
        } 
        return array($date,$teacher,$item,$student,$type,$presents,$grades,$statusname,$action);
    }
    
    /** 
     * Возвращает события одного дня
     * 
     * @param string $display - ражим отображения(по времени, по учителям, по ученикам)
     * @param bool $show_all - вывод подробной таблицы
     * @param bool $implied - вывод мнимых событий
     * @return mixed array - набор журналов или bool - false
     */
    public function get_table_events($display='time', $show_all=false, $implied=false)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $add = array();
        $add['departmentid'] = $depid;
        $string = '';
        
        // отображение по времени
        if ( $display == 'time' )
        {
            $flag = false;
            //получаем все события по параметрам
            if ( ! $events = $this->compose_schedule_by_persons('time',$implied) )
            {// их нет, выводить нечего
                return '';
            }
            // рисуем таблицу
            $table              = new stdClass();
            $table->tablealign  = "center";
            $table->cellpadding = 2;
            $table->cellspacing = 2;
            //$table->size = array ('100px','150px','150px','200px','150px','100px', '50px', '100px', '100px');
            $table->align = array ("center","center","center","center","center","center","center","center","center",
                    "center","center","center","center");
            $table->wrap = array (true,false,false,false,true,true,true,true,true,false,true,true,true);
            
            // создаем массив с заголовками для таблицы
            $heads = array($this->dof->modlib('ig')->igs('time'),
            		$this->dof->get_string('teacher', 'journal'),
            		$this->dof->get_string('course', 'journal'),
            		$this->dof->get_string('student', 'journal'),
            		$this->dof->get_string('form', 'journal'),
            		$this->dof->get_string('present', 'journal'),
            		$this->dof->get_string('grade', 'journal'),
            		$this->dof->get_string('status', 'journal'),
            		$this->dof->modlib('ig')->igs('actions'));
            
            if ( $show_all )
            {// режим отображения не задан - уберем лишние колонки из таблицы
            	$heads = array($this->dof->modlib('ig')->igs('time'),
            			$this->dof->get_string('teacher', 'journal'),
            			$this->dof->get_string('course', 'journal'),
            			$this->dof->get_string('theme', 'journal'),
            			$this->dof->get_string('student', 'journal'),
            			$this->dof->get_string('form', 'journal'),
            			$this->dof->get_string('present', 'journal'),
            			$this->dof->get_string('grade', 'journal'),
            			$this->dof->get_string('ahours', 'journal'),
            			$this->dof->get_string('rhours', 'journal'),
            			$this->dof->get_string('status', 'journal'),
            			$this->dof->modlib('ig')->igs('actions'));
            }
            
            // шапка таблицы
            $table->head = $heads;
            // заносим данные в таблицу  
            $table->data = array();
            foreach ( $events as $event )
            {// формируем строку для каждого
                $departmentid = $this->dof->storage('cstreams')->get_field($event->cstreamid,'departmentid');
                if ( $departmentid != $depid AND $depid != 0 )
                {//чтоб выделить - объявим класс';
                    $table->rowclasses[] = 'mismatch_department';
                }else
                {    
                    $table->rowclasses[] = '';
                    $flag = true;
                }
                $table->data[] = $this->get_string_event($event,$show_all);
            }
            return $this->dof->modlib('widgets')->print_table($table,true);
        }
        // заголовок для таблицы(учителя/ученики)
        $string .= $this->get_header_for_table($display);
        // ученики
        if ( $display == 'students')
        {
            $table = '';
            $this->data = array();
            $datas = $this->compose_schedule_by_persons('students',$implied);
            if ( ! empty($datas) ) 
            {// нашли данные
                foreach ( $datas as $person )
                {//для каждого шаблона формируем строку
                    $link = ' <a href="'.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$person->id.
                                   '&date_to='.$this->datatime->date_to.'&date_from='.$this->datatime->date_from,$add).'">
                                   <br><img src="'.$this->dof->url_im('journal', '/icons/events_student.png').'"
                                   alt=  "'.$this->dof->get_string('view_events_student', 'journal').'" 
                                   title="'.$this->dof->get_string('view_events_student', 'journal').'" /></a>';
                    if ( !isset($person->middlename) )
                    {
                        $person->middlename = '';
                    }
                    $this->data[] = array($person->lastname, $person->firstname, $person->middlename, $link);
                }
                $string .= $this->print_table($display);
            } else
            {// ничего не нашли - выведем сообщение
                $string .= '<div align="center">'.$this->dof->get_string('no_list_students', 'journal').'</div>';
            }
            return  $string;             
        }
        // по учителям
        if ( $display == 'teachers' )
        {
            $type = null;
            $table = '';
            $this->data = array();
            // собираем данные
            $datas = $this->compose_schedule_by_persons('teachers',$implied);
            // нашлись - заносим в таблицу
            if ( ! empty($datas) ) 
            {// нашли данные
                foreach ( $datas as $person )
                {
                    //для каждого шаблона формируем строку
                    $link = ' <a href="'.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$person->id.
                                   '&date_to='.$this->datatime->date_to.'&date_from='.$this->datatime->date_from,$add).'">
                                   <img src="'.$this->dof->url_im('journal', '/icons/events_student.png').'"
                                   alt=  "'.$this->dof->get_string('view_events_teacher', 'journal').'" 
                                   title="'.$this->dof->get_string('view_events_teacher', 'journal').'" /></a>';
                    
                    $dataobject             = new stdClass();
                    $dataobject->lastname   = $person->lastname;
                    $dataobject->firstname  = $person->firstname;
                    if ( !isset($person->middlename) )
                    {
                        $person->middlename = '';
                    }
                    $dataobject->middlename = $person->middlename;
                    // Проверяем права доступа
                    if ( $this->dof->im('journal')->is_access('view:financial') )
                    {
                        $dataobject->salarypoints = $this->dof->storage('schevents')
                                ->get_salary_hours($person->appointmentid, $this->datatime->date_from, $this->datatime->date_to);
                        $type = $display;
                    }
                    $dataobject->link = $link;
                    
                    $this->data[] = (array) $dataobject;
                }
                $table  .= $this->print_table($type);
                $string .= $table; 
            } else
            {// ничего не нашли - выведем сообщение
                $string .= '<div align="center">'.$this->dof->get_string('no_list_teachers', 'journal').'</div>';
            }
            return $string;
        }
    }
    
    /** 
     * Возвращает события одного дня
     * 
     * @param string $display - ражим отображения(по времени, по учителям, по ученикам)
     * @return mixed array - набор журналов или bool - false
     */
    public function get_table_unmarked_events()
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $add                 = array();
        $add['departmentid'] = $depid;
        $string = '';
        //получаем все события по параметрам
        if ( ! $events = $this->compose_schedule_by_persons('unmarked') )
        {// их нет, выводить нечего
            return '';
        }
        // рисуем таблицу
        $table              = new stdClass();
        $table->tablealign  = "center";
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center");
        $table->wrap = array (true,false,false,true,true,true,true,true);
        // шапка таблицы
        $table->head = array($this->dof->modlib('ig')->igs('time'),
                             $this->dof->get_string('teacher', 'journal'),
                             $this->dof->get_string('course', 'journal'),
                             $this->dof->get_string('student', 'journal'),
                             $this->dof->get_string('form', 'journal'),
                             $this->dof->get_string('present', 'journal'),
                             $this->dof->get_string('grade', 'journal'),
                             $this->dof->get_string('status', 'journal'),
                             $this->dof->modlib('ig')->igs('actions'));
        // заносим данные в таблицу  
        $table->data = array();
        foreach ( $events as $event )
        {// формируем строку для каждого
            $table->data[] = $this->get_string_event($event);
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
            
    }
    /**
     * Получаем строку для вывода одного предмето-класса
     * 
     * @return array - массив для строчки события
     */
    public function get_string_appointment($cstream)
    {
        // деламе ХУК, если есть у потока подразделение - укажем строго его
        if ( ! $depid  = optional_param('departmentid', 0, PARAM_INT) )
        {
            $depid = $cstream->departmentid;    
        }
        $addvars = array();
        $addvars['departmentid'] = $depid;
        //формируем название программы
        if ( empty($cstream->progname) OR empty($cstream->progcode) )
        {//не получили - найдем из БД
            $progid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'programmid');
            $programmname  = $this->dof->storage('programms')->get_field($progid,'name').' <br>['.
                                 $this->dof->storage('programms')->get_field($progid,'code').']';
        }else
        {//получили - формируем имя
            $programmname = $cstream->progname.' <br>['.$cstream->progcode.']';
        }
        //формируем название предмета
        if ( empty($cstream->progname) OR empty($cstream->progcode) )
        {//не получили - найдем из БД
            $programmitemname  = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'name').' <br>['.
                                 $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'code').']';
        }else
        {//получили - формируем имя
            $programmitemname  = $cstream->pitemname.' <br>['.$cstream->pitemcode.']';
        }
        //получаем данные о подразделении
        if ( ! $department = $this->dof->storage('departments')->get($cstream->departmentid) )
        {//не получили - выведем пустую строку
            $departmentname = '';
        }else
        {//получили - формируем имя
            $departmentname = $department->name.'<br>['.$department->code.']';
        }
        $student = array();
        if ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$cstream->id,
                        'status'=>array('active','plan','suspend','failed'))) )
        {// если есть на урок ученики - покажем их
            foreach ( $cpassed as $cpass )
            {// каждого
                $student[] = $this->dof->storage('persons')->get_fullname($cpass->studentid);
            }
        }
        $student = implode('<br>',$student);
        $cstreamname = '<a href="'.$this->dof->url_im('journal', '/group_journal/index.php?csid='.$cstream->id,$addvars).'">'.$cstream->name.'</a>';
        if ( $cstream->status == 'active' )
        {// если статус активный - выведем обычнуу надпись
            return array($programmname,$programmitemname,$departmentname,$cstreamname,$student,$cstream->hoursweek);
        }
        //выводим
        return array('<span class=gray>'.$programmname.'</span>','<span class=gray>'.$programmitemname.'</span>',
                     '<span class=gray>'.$departmentname.'</span>','<span class=gray_link>'.$cstreamname.'</span>',
                     '<span class=gray>'.$student.'</span>','<span class=gray>'.$cstream->hoursweek.'</span>');
    }
    /**
     * Возвращает учебную нагрузку учителя
     * @return mixed array - набор журналов или bool - false
     */
    public function get_table_teaching_load()
    {
        $this->datatime->status = array('plan','completed');
        //получаем все события по параметрам
        if ( !isset($this->datatime->teacherid) )
        {// Учителя нет
            return '';
        }
        $appoits = $this->dof->storage('appointments')->get_appointment_by_persons($this->datatime->teacherid);
        if ( !$appoits )
        {// их нет, выводить нечего
            return '';
        }
        $tablecstream = array();
        foreach ( $appoits as $appoit )
        {
            $tableap = new stdClass();
            $tableap->tablealign = "center";
            $tableap->cellpadding = 2;
            $tableap->cellspacing = 2;
            $tableap->align = array ("center");
            // шапка таблицы
            if ( ! $department = $this->dof->storage('departments')->get($appoit->departmentid) )
            {//не получили - выведем пустую строку
                $departmentname = '';
            }else
            {//получили - формируем имя
                 $departmentname = $department->name.'['.$department->code.']';
            }
            if ( ! $cstreams = $this->dof->storage('cstreams')->get_records(array('appointmentid'=>$appoit->id,
                       'status'=>array('active','plan','suspend')),'status ASC, name ASC') )
            {// их нет, выводим пустышку
                $tableap->head = array($departmentname.' - '. $this->dof->get_string('appointment', 'journal').
                    ':' .$appoit->enumber.' - '.$this->dof->get_string('hours', 'journal').': '.
                     $this->dof->get_string('worktime', 'journal').':'.round($appoit->worktime, 2).' / '.
                     $this->dof->modlib('ig')->igs('in_all').':0');
                $tableap->data[] = array($this->dof->get_string('no_cstream_for_appointment', 'journal')); 
                $tablecstream[] = $this->dof->modlib('widgets')->print_table($tableap,true);
                continue;
            }
            
            // рисуем таблицу
            $table = new stdClass();
            $table->tablealign = "center";
            $table->cellpadding = 2;
            $table->cellspacing = 2;
            //$table->size = array ('100px','150px','150px','200px','150px','100px');
            $table->align = array ("center","center","center","center","center","center");
            // шапка таблицы
            $table->head = array($this->dof->get_string('programm', 'journal'),
                                 $this->dof->get_string('course', 'journal'),
                                 $this->dof->get_string('department', 'journal'),
                                 $this->dof->get_string('name', 'journal'),
                                 $this->dof->get_string('student', 'journal'),
                                 $this->dof->get_string('hoursweek', 'journal'));
            // заносим данные в таблицу  
            $table->data = array();
            $hours = 0;
            foreach ( $cstreams as $cstream )
            {// формируем строку для каждого
                if ( $cstream->status == 'active' )
                {// считаем нагрузку только для активных потоков
                    $hours += $cstream->hoursweek;
                }
                $table->data[] = $this->get_string_appointment($cstream);
            }
            $tableap->head = array($departmentname.' - '. $this->dof->get_string('appointment', 'journal').
                    ':' .$appoit->enumber.' - '.$this->dof->get_string('hours', 'journal').': '.
                     $this->dof->get_string('worktime', 'journal').':'.round($appoit->worktime, 2).' / '.
                     $this->dof->modlib('ig')->igs('in_all').':'.$hours);
            $tablecstream[] = $this->dof->modlib('widgets')->print_table($tableap,true).
                              $this->dof->modlib('widgets')->print_table($table,true);
        }
        return implode('<br>',$tablecstream);
    }

  
    /** Собрать расписание для отображения по перосне(ученик/учитель)
     * или же по времени.
     * Функция извлекает все события по заданным параметрам,
     * и возвращает учеников ЭТИХ событий 
     * @return array массив объектов, разбитый по интервалам времени, и
     */
    protected function compose_schedule_by_persons($person, $implied=false)
    {
        // параметры выборки
        $conds = new stdClass();
        $conds->cstreamsstatus = array('plan','active','suspend','completed');
        $statuses = array('plan','postponed','completed');
        if ( $implied )
        {// добавим в выборку мнимые события
            $statuses[] = 'implied';
        }
        $conds->status = $statuses;
        $conds->date_to = $this->datatime->date_to;
        $conds->date_from = $this->datatime->date_from;
        
        // добавим выборку для каждого свою
        switch ($person)
        {
            case 'students':
                $conds->departmentid = $this->depid;
                $conds->cpassedstatus = array('plan','active','suspend','completed','failed');
                break;
            case 'teachers':
                $conds->departmentid = $this->depid;
                $conds->appointstatus = array('plan','active');
                break;
            case 'time':
                $conds->cpassedstatus = array('active','suspend','completed','failed');
                // передали перосну - запомним её
                if ( isset($this->datatime->teacherid) )
                {// учитель
                    $conds->teacherid = $this->datatime->teacherid;
                }elseif ( isset($this->datatime->studentid) )
                {// ученик
                    $conds->studentid = $this->datatime->studentid;
                }else
                {// выводим уроки только для подразделения
                    $conds->departmentid = $this->depid;
                }
                
                return $this->dof->storage('schevents')->get_time_list($conds);
                break;
            case 'unmarked': 
                $conds->teacherid = -1;// чтоб не выводил все подряд
                // передали перосну - запомним её
                if ( isset($this->datatime->teacherid) )
                {// учитель
                    $conds->teacherid = $this->datatime->teacherid;
                }
                $conds->cpassedstatus = array('active','suspend');
                $conds->cstreamsstatus = array('plan','active','suspend');
                $conds->status = array('plan','postponed');
                unset($conds->date_from);
                unset($conds->date_to);
                $conds->to_end_lesson = true;
                return $this->dof->storage('schevents')->get_time_list($conds);
            break;
        }
        // для архива свой список статусов
        if ( ! $result = $this->dof->storage('schevents')->get_persons_list($conds, $person) )
        {// не нашли шаблон - плохо';
            return '';
        }
        return $result;
    }     

    /**
     * Возвращает html-код таблицы
     *
     * @param string $person - тип персоны (учитель или ученик)                          
     * @return string - html-код или пустая строка
     */
    protected function print_table($person = null)
    {
        // рисуем таблицу
        $table              = new stdClass();
        $table->tablealign  = "center";
        $table->cellspacing = 5;
        $table->width       = '60%';
        //$table->wrap = array (true);
        $table->align       = array("left", "left", "left", "center", "center");
        // шапка таблицы
        $table->head        = $this->get_header($person);
        // заносим данные в таблицу     
        $table->data        = $this->data;
        return $this->dof->modlib('widgets')->print_table($table, true);
    }

    /** 
     * Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта
     *  
     * @return array
     */
    private function get_header($person = null)
    {
        switch ($person) {
            case 'teachers': 
                return array($this->dof->get_string('lastname', 'journal'),
                                $this->dof->get_string('firstname', 'journal'),
                                $this->dof->get_string('middlename', 'journal'),
                                $this->dof->get_string('salaryhour', 'journal'),
                                $this->dof->modlib('ig')->igs('actions'));
            default:
                return array($this->dof->get_string('lastname', 'journal'),//$this->get_im()),
                                $this->dof->get_string('firstname', 'journal'),//$this->get_im()),
                                $this->dof->get_string('middlename', 'journal'),//$this->get_im()),
                                $this->dof->modlib('ig')->igs('actions'));
        }
    }
    
 	/** Рисует таблицу для отображения
	 * только одной шапки (преподаватели/учащиеся)
	 * 
	 * @param string $type - тип персоны(преподаватель/учащийся)
	 * return table
     */
    protected function get_header_for_table($type)
    {
        $table1 = new stdClass();
        $table1->tablealign = "center";
        $table1->cellpadding = 5;
        $table1->cellspacing = 5;
        $table1->width = '60%';
        $table1->align = array("center");
        if ( $type == 'teachers' )
        {// преподаветели
            $table1->head = array($this->dof->get_string('teachers', 'journal'));            
        }else 
        {// ученики
            $table1->head = array($this->dof->get_string('students', 'journal'));  
        }
        return $this->dof->modlib('widgets')->print_table($table1,true);
    }

    /** Сбор данных для экспорта в csv
     *
     * return array
     */
    public function get_data_for_export()
    {
        $export = array();
        $events = $this->compose_schedule_by_persons('time');
        
        foreach ($events as $event)
        {
            $id = $event->id;
            $date = dof_userdate($event->date,'%d-%m-%Y').' '.dof_userdate($event->date,'%H:%M');
            $item = $this->dof->storage('programmitems')->get_field($event->programmitemid,'name').'['.
                    $this->dof->storage('programmitems')->get_field($event->programmitemid,'code').']';
            if ( !$theme = $this->dof->storage('plans')->get_field(array('id' => $event->planid), 'name') )
            {
                $theme = '';
            }
            // формируем строку таблицы
            $teacher = $this->dof->storage('persons')->get_fullname($event->teacherid);
            $teacher_enum = $this->dof->storage('appointments')->get_field(array(
                    'id' => $event->appointmentid), 'enumber');
            
            $students = array();
            $students = $this->get_string_for_export($event); 
            
            $statusname = $this->dof->workflow('schevents')->get_name($event->status);
            
            $export[] = array('event_id'            => $id, 
                              'date'                => $date, 
                              'item'                => $item, 
                              'theme'               => $theme, 
                              'teacher_name'        => $teacher, 
                              'teacher_enumber'     => $teacher_enum,
                              'students'            => $students, 
                              'event_statusname'    => $statusname);
        }
        return $export;
    }
    
    /** Создание массива данных об учениках
     * @param object $event - объект урока
     * return array
     */
    public function get_string_for_export($event)
    {
        $students = array();
        
        if ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$event->cstreamid,
                        'status'=>array('plan','active','suspend','completed','failed'))) )
        {// если есть на урок ученики - покажем их
            foreach ( $cpassed as $cpass )
            {
                $fullname = $this->dof->storage('persons')->get_fullname_initials($cpass->studentid); 
                // номер контракта учащегося
                if ( $contractid = $this->dof->storage('programmsbcs')->get_field($cpass->programmsbcid, 'contractid') )
                {
                    $contractnum = $this->dof->storage('contracts')->get_field(array('id' => $contractid), 'num');   
                }
                $presresult = $this->dof->storage('schpresences')->get_present_status($cpass->studentid, $event->id);
                if ( $presresult === '1' )
                {// ученик присутствовал
                    $present = $this->dof->get_string('yes_present', 'journal');
                }elseif( $presresult === '0' )
                {// ученик отсутствовал
                    $present = $this->dof->get_string('no_present', 'journal');
                }else
                {// нет данных о посещаемости
                    $present = $this->dof->get_string('no_mark', 'journal');
                }
                // получаем оценку студентa
                if ( !$grade = $this->dof->storage('cpgrades')->get_field(array('teacherid' => $event->teacherid,
                        'planid' => $event->planid, 'cpassedid' => $cpass->id), 'grade'))
                {
                    $grade = " ";
                }
                $students[] = array('student_name' => $fullname, 
                        'student_contract' => $contractnum, 
                        'student_present' => $present, 
                        'student_grade' => $grade);
            }
        }
        return $students;
    }
}
?>