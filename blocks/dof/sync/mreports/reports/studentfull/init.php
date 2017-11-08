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
// подключаем библиотеки верхнего уровня
require_once(dirname(realpath(__FILE__))."/../../lib.php");

/** Полный отчет о деятельности учинека
 *
 */
class dof_sync_mreports_report_studentfull extends dof_sync_mreports_student
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'sync';
    protected $templatercode = 'mreports';
    protected $templatertemplatename = 'full_report_student';
    /* Код плагина, объявившего тип приказа
    */
    function code()
    {
        return 'studentfull';
    }
    
    /* Имя плагина, объявившего тип приказа
    */ 
    function name()
    {
        return $this->dof->get_string('full_students', 'programmsbcs');
    }
    
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        $a = new stdClass();
        $a->begindate = $this->dof->storage('persons')->get_userdate($report->begindate,'%d.%m.%Y');
        $a->enddate = $this->dof->storage('persons')->get_userdate($report->enddate,'%d.%m.%Y');
        $report->name = $this->dof->get_string('full_teachers_of', 'programmsbcs', $a);
        return $report;
    }     
    
    /** 
     * Возвращает данные для шаблона отчета
     * Структура объекта:
     * * fio - ФИО
     * * contracts - Договоры
     * * * num - Номер договора
     * * * date - дата создания
     * * * programmsbcs - подписки по данному договору
     * * * * programm - программа
     * * * * begindate - дата начала действия подписки
     * * * * cpasseds - курсы
     * * * * * course - курс эд
     * * * * * mdlcourse - Курс moodle
     * * * * * lastaccess - последний заход на курс
     * * * * * countactions - всего действий в курсе
     * * * * * countactionsage - всего действий в курсе за период
     * * * * * messageforum - ответов в форуме
     * * * * * countworks - сданных работ
     * @param int $personid - id персоны деканата
     * @param int $begindate - начоло заданного периода
     * @param int $enddate - конец заданного периода
     * @param int $enddate - конец заданного периода
     * @return object
     */
    protected function get_data_full_report($con, $personid, $mdluser = 0, $begindate = null, $enddate = null, $depid = 0 )
    {
        $contract = new stdClass();
        $contract->column_contract = $this->dof->get_string('contract', 'programmsbcs');
        //echo $contract->column_contract;die;
        $contract->column_date =     $this->dof->get_string('contructdate', 'programmsbcs');
        $contract->num = $con->num;
        if ( $con->date )
        {// преобразуем unixtime в дату только в том случае если данные есть
            $contract->date = $this->dof->storage('persons')->get_userdate($con->date,'%d.%m.%Y');
        }else
        {
            $contract->date = $this->dof->modlib('ig')->igs('no_specify');
        }
        
        $contract->programmsbcs = array();
        if( $programmsbcs = $this->dof->storage('programmsbcs')->get_records(array('contractid'=>$con->id,'status'=>'active')) )
        {// получили список подписок по данному договору
            foreach( $programmsbcs as $progsbc )
            {
                if( $programm = $this->dof->storage('programms')->get($progsbc->programmid) )
                {
                    $programmsbc = new stdClass();
                    $programmsbc->column_programm = $this->dof->get_string('programm', 'programmsbcs');
                    $programmsbc->column_begindate = $this->dof->get_string('begindate', 'programmsbcs');
                    $programmsbc->programm = $programm->name.'['.$programm->code.']'.'('.$progsbc->agenum.')';
                    if ( $progsbc->datestart )
                    {
                        $programmsbc->begindate = $this->dof->storage('persons')->get_userdate($progsbc->datestart,'%d.%m.%Y');
                    }else
                    {
                        $programmsbc->begindate = $this->dof->modlib('ig')->igs('no_specify');
                    }
                
                    //$programmsbc->id = $progsbc->id;
                    $programmsbc->cpasseds = array();
                    if ( $cpasseds = $this->dof->storage('cpassed')->get_records(array('programmsbcid'=>$progsbc->id,'status'=>'active')) )
                    {
                        $programmsbc->column_course = $this->dof->get_string('course', 'programmsbcs');
                        $programmsbc->column_mdlcourse = $this->dof->get_string('mdlcourse', 'programmsbcs');;
                        $programmsbc->column_lastaccess = $this->dof->get_string('lastaccess_course', 'programmsbcs');
                        $programmsbc->column_countaccessage = $this->dof->get_string('countaccess_course', 'programmsbcs');
                        $programmsbc->column_countactions = $this->dof->get_string('countactions_course', 'programmsbcs');
                        $programmsbc->column_countactionsage = $this->dof->get_string('countactionsage_course', 'programmsbcs');
                        $programmsbc->column_messageforum = $this->dof->get_string('messageforum', 'programmsbcs');
                        $programmsbc->column_countworks = $this->dof->get_string('countworks', 'programmsbcs'); 
                        foreach($cpasseds as $cpas)
                        {
                            if ( $programmitem = $this->dof->storage('programmitems')->get($cpas->programmitemid) )
                            {// синхронизирован с moodle
                                if( isset($programmitem->mdlcourse) AND $programmitem->mdlcourse AND $mdluser )
                                {
                                    $cpass = $this->get_cpassed_info($programmitem, $personid, $begindate , $enddate);    
                                }else
                                {
                                    $cpass = new stdClass();
                                    // название курса
                                    $cpass->namecourse = $programmitem->name.'['.$programmitem->code.']';
                                    $cpass->mdlcourse     = ' - ';
                                    // последний вход в курс
                                    $cpass->lastaccess    = ' - ';
                                    // всего входов в курс
                                    $cpass->allaccess     = ' - ';
                                    // количество записей в логах, связанных с этим курсом за период
                                    $cpass->allrecordtime = ' - ';
                                    // количество записей в логах, связанных с этим курсом за всё время 
                                    $cpass->allrecord     = ' - ';
                                    // ответов в форуме этого курса
                                    $cpass->forumaswer    = ' - ';
                                    // сданных работ в этом курсе
                                    $cpass->readywork     = ' - ';
                                }
                                $programmsbc->cpasseds[] = $cpass;
                            }
                        }
                    }
                    $contract->programmsbcs[] = $programmsbc;
                }
            }
        }
        return $contract;
    }    
    
    /**
     * Сpassed - развернуто
     * @param integer $cpassedid - id cpasseda
     * @param 
     * @return 
     */
    public function get_cpassed_info($pitem, $personid, $begindate = null, $enddate = null)    
    {
        global $DB;
        // пользователь из мудла
        $userid = $this->get_userid($personid);
        $cpass = new stdClass();
        // имя курса
        $cpass->namecourse = $pitem->name;
        // имя курса в мудле
        if ( $name = $DB->get_record('course', array('id' => $pitem->mdlcourse), 'fullname')  )
        {
            $cpass->mdlcourse = $name->fullname;
        }else 
        {
            $cpass->mdlcourse = '';
        }    
        // последний вход в курс
        if ( $lastaccess = $this->get_user_last_access($personid, $pitem->id) )
        {// преобразуем дату из unixtime
            $lastaccess = $this->dof->storage('persons')->get_userdate($lastaccess,'%d.%m.%Y %H:%M');
        }else
        {// если дата не указана - то не производим преобразование
            $lastaccess = $this->dof->modlib('ig')->igs('no');
        }
        $cpass->lastaccess = $lastaccess;
        // всего входов в курс
        $cpass->allaccess = $this->dof->modlib('ama')->course(false)->get_log_course_num($userid, $pitem->mdlcourse, $begindate, $enddate);
        // количество записей в логах, связанных с этим курсом за период
        $cpass->allrecordtime = $this->dof->modlib('ama')->course(false)->get_logs($userid, $pitem->mdlcourse, $begindate, $enddate);
        // количество записей в логах, связанных с этим курсом за всё время 
        $cpass->allrecord = $this->dof->modlib('ama')->course(false)->get_logs($userid, $pitem->mdlcourse);
        // ответов в форуме этого курса
        $cpass->forumanswer = $this->get_log_forum_answer($personid, $pitem->id, $begindate, $enddate);
        // сданных работ в этом курсе
        $cpass->readywork = $this->dof->modlib('ama')->course($pitem->mdlcourse)->count_submitted_elements($userid,$begindate,$enddate);
        
        return $cpass;
        
    } 

   /** Метод записывает в отчет все данные по студентам и
     * возвращает уже полный отчет
     * @param object $report - отчет, по который доформировываем )
     * @return object $report - объект 
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// неправильный тип данных
            return false;
        }
        // переопределяем данные
        $begindate  = $report->begindate;
        $enddate    = $report->enddate;
        $students   = array();
        $conditions = array('status' => 'work');
        if ( $report->objectid )
        {// отчет только по ученикам подразделения
            $conditions['departmentid'] = $report->objectid;
        }
        // список рабочих контрактов, отсортированных по ФИО ученика
        $contracts = $this->dof->storage('contracts')->
                        get_listing($conditions,null,null,"sortname");
        $students = array();
        if ( $contracts )
        {
            // для того чтобы отобразить сколько контрактов осталось обработать - посчитаем их количество
            $totalcount   = count($contracts);
            $currentcount = 0;
            
            foreach ( $contracts as $obj)
            {// отчет по студентам
                // Выводим сообщение о том какой контракт проверяется сейчас, и сколько контрактов осталось
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing contractid: '.$obj->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->dof->mtrace(2, $mtracestring);
                
                if ( ! $person = $this->dof->storage('persons')->get($obj->studentid) )
                {// пользователя нет в БД - пропустим его
                    $this->dof->mtrace(2, 'ERROR:No person for contractid='.$obj->id.'!');
                    continue;
                }
                $template = new stdClass();
                $template->fio = $person->sortname; //фио
             
                $template->contracts = array();
                $contract = $this->get_data_full_report($obj, $person->id, $person->mdluser, $begindate, $enddate);
                $template->contracts[] = $contract;
                
                $students[] = $template;
            }  
            // дозапишем наш объект 
            $report->data->students = $students;
            // допол информация
            $report->data->info = $this->dof->get_string('info', 'mreports','','sync');
            $report->data->depart = $this->dof->get_string('department', 'mreports','','sync');
            if ( $report->departmentid )
            {// отчет по подразделению
                $dep = $this->dof->storage('departments')->get($report->departmentid);
                $report->data->depart_name = $dep->name.'['.$dep->code.']';    
            }else 
            {// все отчеты
                $report->data->depart_name = $this->dof->get_string('all_departs', 'mreports','','sync');
            }     
            $report->data->data_complete = $this->dof->get_string('data_complete', 'mreports','','sync');
            $report->data->data_begin_name = $this->dof->get_string('data_begin', 'mreports','','sync');
            $report->data->data_begin = $this->dof->storage('persons')->get_userdate($report->crondate,'%d.%m.%Y %H:%M');
            $report->data->request_name = $this->dof->get_string('request_name', 'mreports','','sync');
            $report->data->requestdate = $this->dof->storage('persons')->get_userdate($report->requestdate,'%d.%m.%Y %H:%M');
        }
        return $report;
    }
    
    /** Отобразить отчет в формате HTML
     * 
     */
    public function show_report_html($addvars=null)
    {
        $error = '';
        $table = '';
        if ( ! $this->is_generate($this->load()) )
        {//  отчет еще не сгенерирован
            $error = $this->dof->get_string('report_no_generate','journal');
        }else
        {// загружаем шаблон
            // достаем данные из файла
            $template = $this->load_file();
            // подгружаем методы работы с шаблоном
            if ( ! $templater = $this->template() )
            {//не смогли
                $error = $DOF->get_string('report_no_get_template','employees');
            }elseif ( ! $table = $templater->get_file('html') )
            {// не смогли загрузить html-таблицу
                $table = '';
                $error = $DOF->get_string('report_no_get_table','employees');
            }
        }
        
        // вывод ошибок
        print '<p style=" color:red; text-align:center; "><b>'.$error.'</b></p>';
        echo $table;
       
    }  
}
?>