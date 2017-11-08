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


global $DOF;
// Подключаем библиотеки
require_once(dirname(realpath(__FILE__))."/../../lib.php");
require_once($DOF->plugin_path('storage','reports','/basereport.php'));
/**
 * Класс для сбора информации из Moodle для отчетов
 */
abstract class dof_sync_mreports_base extends dof_storage_reports_basereport
{
    public function plugintype()
    {
        return 'sync';
    }
    
    public function plugincode()
    {
        return 'mreports';
    }
    
    /** 
     * Возвращает данные для шаблона отчета 
     * @param int $personid - id персоны деканата
     * @param int $begindate - начоло заданного периода
     * @param int $enddate - конец заданного периода
     * @return object
     */
    public function get_data_short_report($personid, $begindate = null, $enddate = null, $opt = array() )
    {
        if ( ! $person = $this->dof->storage('persons')->get($personid) )
        {// пользователя нет в БД - ошибка
            return false;
        }
        $template = new stdClass();
        $template->fio = $person->sortname; //фио
        $template->lastaccess = $this->get_user_last_access($personid); //последний вход
        $template->countaccess = $this->get_count_access($personid); //всего вход
        $template->countaccessage = $this->get_count_access($personid,0,$begindate,$enddate); //всего вход за период
        $template->messageforum = $this->get_log_forum_answer($personid,0,$begindate,$enddate); //кол-во сообщений в форумах
        $template->countcourse = $this->get_courses($personid);  //кол-во курсов Moodle  
        // активность пользователя за период в кратком отчете отключена из-за проблем
        // с производительностью.
        //$template->logage = $this->get_logs($personid,0,$begindate,$enddate); //активность пользователя
        $opt['personid'] = $personid;
        $opt['enddate'] = $enddate;
        $opt['begindate'] = $begindate;
        // добавим индивидуальные поля для шаблона
        $template = $this->get_add_data_report($template,$opt);
        // вернем данные для шаблона
        return $template;
    }
    
    /** 
     * Возвращает данные для шаблона отчета
	 *  учителя или учеников
     * @param int $personid - id персоны деканата
     * @param int $begindate - начоло заданного периода
     * @param int $enddate - конец заданного периода
     * @param int $enddate - конец заданного периода
     * @return object
     */
    protected function get_data_full_report($obj, $personid, $mdluser = 0, $begindate = null, $enddate = null, $depid = 0 )
    {
        return new stdClass();
    }
    
    /** Метод для переопределения данных шаблона
     * @param object $template - данные шаблона
     * @return unknown_type
     */
    protected function get_add_data_report($template,$opt)
    {
        return $template;
    }
    /**
     * Возвращает userid по personid 
     * @param int $personid - id персоны деканата
     * @return int
     */
    protected function get_userid($personid)
    {
        return (int) $this->dof->storage('persons')->get_field($personid,'mdluser');
    }
    
    /**
     * Возвращает courseid по programmitemid 
     * @param int $programmitemid - id дисциплины деканата
     * @return int
     */
    protected function get_courseid($programmitemid)
    {
        if ( ! $programmitemid )
        {// нету дисциплины - нету и Moodle-курса
            return 0;
        }
        return (int) $this->dof->storage('programmitems')->get_field($programmitemid,'mdlcourse');
    }
    
    /**
     * Получает последний вход юзера на курс
     * @param int $personid - id персоны деканата
     * @param int $pitemid - id дисциплины деканата
     * @return int
     */
    protected function get_user_last_access($personid, $pitemid = 0)
    {
        if ( ! $pitemid )
        {// предмет не указан - ищем последний вход пользователя на сайт
            return $this->dof->modlib('ama')->user(false)->get_lastaccess($personid);
        }else
        {// ищем последний вход пользователя на курс
            $userid = $this->get_userid($personid);
            $courseid = $this->get_courseid($pitemid);
            return $this->dof->modlib('ama')->course(false)->user_last_access($userid, $courseid);
        }
    }
    
    /**
     * Получает кол-во сообщений пользователя в форуме
     * @param int $personid - id персоны деканата
     * @param int $pitemid - id дисциплины деканата
     * @param int $begindate - начоло заданного периода
     * @param int $enddate - конец заданного периода
     * @return int
     */
    protected function get_log_forum_answer($personid, $pitemid = 0, $begindate = null, $enddate = null)
    {
        $userid = $this->get_userid($personid);
        $courseid = $this->get_courseid($pitemid);
        return $this->dof->modlib('ama')->course(false)->get_log_forum_answer($userid, $courseid, $begindate, $enddate);
    }
    
    /**
     * Получает общее кол-во входов пользователя всего
     * @param int $personid - id персоны деканата
     * @param int $pitemid - id дисциплины деканата
     * @param int $begindate - начало заданного периода
     * @param int $enddate - конец заданного периода
     * @return int
     */
    protected function get_count_access($personid, $pitemid = 0, $begindate = null, $enddate = null)
    {
        if ( ! $pitemid )
        {// предмет не указан - ищем кол-во входов пользователя на сайт
            return $this->dof->modlib('ama')->user(false)->count_login($personid,$begindate,$enddate);
        }else
        {// ищем кол-во входов пользователя на курс
            $userid = $this->get_userid($personid);
            $courseid = $this->get_courseid($pitemid);
            return $this->dof->modlib('ama')->course(false)->user_last_access($userid, $courseid, $begindate, $enddate);
        }
    }
    
    /**
     * Получает активность персоны на курсе
     * @param int $personid - id персоны деканата
     * @param int $pitemid - id дисциплины деканата
     * @param int $begindate - начоло заданного периода
     * @param int $enddate - конец заданного периода
     * @return array
     */
    protected function get_logs($personid, $pitemid = 0, $begindate = null, $enddate = null)
    {
        $userid = $this->get_userid($personid);
        $courseid = $this->get_courseid($pitemid);
        $logs = $this->dof->modlib('ama')->course(false)->get_logs($userid, $courseid, $begindate, $enddate);

        return $logs;
    }
    
    /** Получает количество курсов на которые пользователь подписан в moodle
     * 
     * @param int $personid - id персоны деканата
     * @return int
     */
    protected function get_courses($personid)
    {
        $userid = $this->get_userid($personid);
        $course = $this->dof->modlib('ama')->user(false)->get_courses($userid);
        if ( ! $course )
        {// курсов нет - вернем 0;
            return 0;
        }
        return count($course);
    }

}

/** Отчеты о деятельности учеников
 *
 */
abstract class dof_sync_mreports_student extends dof_sync_mreports_base
{
	/**
     * Количество выполненых заданий по курсу
	 * @param integer $pitem - id programmitems
     * @param integer $persinid - персона из деканата
     * @return integer $count - кол заданий
     */
    public function get_send_task($personid, $pitem=0, $begindate=null, $enddate=null)    
    {
        // персона мудла
        $userid = $this->get_userid($personid);
        
        if ( $pitem )
        {
            if ( ! $courseid = $this->get_courseid($pitem) )
            {// из-за глюка амы приходится проверять
                return 0;
            }
            // кол выполненых заданий пок курсу            
            $count = $this->dof->modlib('ama')->course($courseid)->count_submitted_elements($userid,$begindate,$enddate);
        }else 
        {// код выполненых заданий по мудлу    
            $count = 0;
            $courses = $this->dof->modlib('ama')->user(false)->get_courses($userid);
            foreach ( $courses as $course )
            {
                $count  = $this->dof->modlib('ama')->course($course->id)->count_submitted_elements($userid,$begindate,$enddate);
                $count += $count;
            }        
        }
        return $count;
    }
    
}

/** Базовый класс сбора данных об учителе
 * 
 */
abstract class dof_sync_mreports_teacher extends dof_sync_mreports_base
{
    /**
     * Получить данные из договора (ФИО, № договора, дата заключения)
     * @param array $user  -массив с id пользователями
     * @param bool $fulldata - флаг, определяет полные или краткие данные запрашивать
     * @return object - объект с данными пользователя
     */
    public function get_contract_info($userid, $flag=false)    
    {
        if ( ! $person = $this->dof->storage('persons')->get($userid) )
        {// пользователя нет в БД - ошибка
            return false;
        }        
        $person = new stdClass();
        $person->fio = $person->sortname;
        if ( $flag )
        {// полный отчет
            $eagreements = $this->dof->storage('eagreements')->
                get_records(array("personid"=>$userid , 'status'=>'active'));
            $person->eagreements = array();
            foreach ( $eagreements as $eagreement )
            {
                $obj = new stdClass();
                $obj->data = $eagreement->date;
                $obj->num  = $eagreement->num;
                $obj->id   = $eagreement->id;
                $person->eagreements[] = $obj;
            }
        }    
        return $person;
    }
    
    /** Получить количество проверенных учителем заданий
     * 
     * @return int
     * @param int $teacherid - id учителя в таблице persons, для которого получается количество заданий
     * @param int $courseid[optional] - id курса moodle в котором находятся задания. 
     *                                  Если не указано - то задания соберутся из всех курсов
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    public function count_graded_items($teacherid, $courseid=null, $begindate=null, $enddate=null)
    {
        $result = 0;
        
        if ( ! $userid = $this->get_userid($teacherid) )
        {// не найдено пользователя в moodle с такими данными
            return 0;
        }
        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($userid) )
        {// указанный пользователь не существует
            return 0;
        }
        
        if ( ! $courseid )
        {// курс не указан - возьмем все курсы пользователя
            $courses = $this->get_teacher_courses($teacherid);
        }else
        {// передан единственный курс
            $courses = array($courseid);
        }
    
        foreach ( $courses as $course )
        {// ищем проверенные задания во всех курсах
            if ( empty($course) )
            {// id курса не существует
                // Тупая ama
                continue;
            }
            if ( ! $this->dof->modlib('ama')->course(false)->is_exists($course) )
            {// указанного курса не существует
                continue;
            }
            if ( ! $this->dof->modlib('ama')->user(false)->is_teacher($userid, $course) )
            {// пользователь не является учителем в курсе - значит он не может проверять там задания
                continue;
            }       
            $result += $this->dof->modlib('ama')->
                course($course)->count_graded_elements($userid, $begindate, $enddate);
        }
        
        return $result;
    }
    
    /** Получить список курсов moodle которые преподает учитель
     * @return array - массив id курсов Moodle
     * @todo для учителя извлекать только те потоки, которые попадают в рамки отчета по begindate и enddate
     * 
     * @param int $teacherid - id учителя в таблице persons
     */
    protected function get_teacher_courses($teacherid)
    {
        // получаем все потоки, которые ведет учитель
        if ( ! $cstreams = $this->dof->storage('cstreams')->
                get_records(array('teacherid'=>$teacherid)) )
        {// нет потоков для указанного учителя в выбранном периоде - он ничего в это время не вел
            // а значитт и задания в курсах проверять не надо
            return array();
        }
        // собираем список предметов, которые ведет учитель
        $pitems = array();
        foreach ( $cstreams as $cstream )
        {// из всех потоков извлекаем предметы
            if ( isset($cstream->programmitemid) AND $cstream->programmitemid )
            {
                $pitems[] = $cstream->programmitemid;
            }
        }
        $pitems = array_unique($pitems);
        // собираем список курсов moodle, которые ведет преподаватель
        $mdlcourses = array();
        foreach ( $pitems as $pitem )
        {
            if ( $mdlcourseid = $this->dof->storage('programmitems')->get_field($pitem, 'mdlcourse') )
            {
                $mdlcourses[] = $mdlcourseid;
            }
        }
        
        return array_unique($mdlcourses);
    }
    
    /** Получить количество непроверенных заданий в курсе
     * 
     * @return int
     * @param int $courseid - id курса в moodle, для которого собираются данные
     * @param int $groupid[optional] - id группы (если не указано - будет получено общее количество
     *                                 непроверенных заданий в курсе)
     * @param int $begindate[optional] - Начало периода, за который собираются данные
     * @param int $enddate[optional] - Конец периода, за который собираются данные
     */
    public function count_notgraded_items($courseid, $groupid=null, $begindate=null, $enddate=null)
    {
        if ( ! $this->dof->modlib('ama')->course(false)->is_exists($userid) )
        {// указанного курса не существует
            return 0;
        }
        if ( ! $courseid )
        {
            return 0;
        }
        
        return $this->dof->modlib('ama')->course($courseid)->
                             count_notgraded_elements($groupid, $begindate, $enddate);
    }
    
}

?>