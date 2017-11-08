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

/** Полный отчет о деятельности учителя
 *
 */
class dof_sync_mreports_report_teacherfull extends dof_sync_mreports_teacher
{
     // Параметры для работы с шаблоном
    protected $templatertype = 'sync';
    protected $templatercode = 'mreports';
    protected $templatertemplatename = 'full_report_teacher';

    /* Код плагина, объявившего тип приказа
     */
    function code()
    {
        return 'teacherfull';
    }
    
    /* Имя плагина, объявившего тип приказа
    */ 
    function name()
    {
        return $this->dof->get_string('full_teachers', 'employees');
    }    
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        $a = new stdClass();
        $a->begindate = $this->dof->storage('persons')->get_userdate($report->begindate,'%d.%m.%Y');
        $a->enddate = $this->dof->storage('persons')->get_userdate($report->enddate,'%d.%m.%Y');
        $report->name = $this->dof->get_string('full_teachers_of', 'employees', $a);
        return $report;
    }
    
    /**
     * Сstream - развернуто
     * @param int $cstreamid - id cpasseda
     * @param 
     * @return 
     */
    protected function get_cstream_info($pitem, $personid, $begindate = null, $enddate = null)    
    {
        global $DB;
        // пользователь из мудла
        $userid = $this->get_userid($personid);
        $cstream = new stdClass();
        // имя курса
        $cstream->namecourse = $pitem->name;
        
        // получаем имя курса в Moodle (если сможем найти)
        // после получения id курса на всякий случай проверим его существование чтобы 
        // МОДУЛЬ AMA ВДРУГ НЕ УПАЛ С КРИТИЧЕСКОЙ ОШИБКОЙ ПОСЕРЕДИНЕ СБОРА ОТЧЕТОВ 
        if ( isset($pitem->mdlcourse) AND 
             $this->dof->modlib('ama')->course(false)->is_exists($pitem->mdlcourse) )
        {// курс точно есть - вытаскиваем его название
            $course = $DB->get_record('course', array('id' => $pitem->mdlcourse), 'fullname');
            $cstream->mdlcourse = $course->fullname;
        }else
        {// нет такого курса в Moodle
            $cstream->mdlcourse = '';
        }
         
        // последний вход в курс
        if ( $lastaccess = $this->get_user_last_access($personid, $pitem->id) )
        {
            $cstream->lastaccsess = $this->dof->storage('persons')->get_userdate($lastaccess,'%d.%m.%Y %H:%M');
        }else
        {
            $cstream->lastaccsess = $this->dof->modlib('ig')->igs('no_specify');
        }
        
        // всего входов в курс за период
        $cstream->allaccsess = $this->dof->modlib('ama')->course(false)->get_log_course_num($userid, $pitem->mdlcourse, $begindate, $enddate);
        // количество записей в логах, связанных с этим курсом за период
        $cstream->allrecordtime = $this->dof->modlib('ama')->course(false)->get_logs($userid, $pitem->mdlcourse, $begindate, $enddate);
        // количество записей в логах, связанных с этим курсом за всё время 
        $cstream->allrecord = $this->dof->modlib('ama')->course(false)->get_logs($userid, $pitem->mdlcourse);
        // ответов в форуме этого курса
        $cstream->forumaswer = $this->get_log_forum_answer($personid, $pitem->id, $begindate, $enddate);
        // сданных работ в этом курсе
        $cstream->checkwork = '';
        if ( $cstream->mdlcourse )
        {// проверяем количество сданных работ в курсе только в случае наличия самого курса
            // иначе модуль ama падает с ошибкой
            $cstream->checkwork = $this->dof->modlib('ama')->course($pitem->mdlcourse)->
                                    count_graded_elements($userid,$begindate,$enddate);
        }
        
        return $cstream;
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
    protected function get_data_full_report($eagr, $personid, $mdluser = 0, $begindate = null, $enddate = null, $depid = 0 )
    {
        $eagreement = new stdClass();
        $eagreement->column_eagreement = $this->dof->get_string('eagreement', 'employees');
        $eagreement->column_begindate = $this->dof->get_string('begindate', 'employees');
        $eagreement->num = $eagr->num;
        if ( $eagr->begindate )
        {// если дата заключения контракта указана - то преобразуем ее из unixtime
            $eagreement->begindate = $this->dof->storage('persons')->get_userdate($eagr->begindate,'%d.%m.%Y');
        }else
        {// если не указана - то так и напишем, чтобы не писать про 1970 год
            $eagreement->begindate = $this->dof->modlib('ig')->igs('no_specify');
        }
        
        $eagreement->appointments = array();
        if( $appointments = $this->dof->storage('appointments')->get_records(array('eagreementid'=>$eagr->id,'status'=>'active')) )
        {// получили список подписок по данному договору
            foreach( $appointments as $app )
            {
                $appointment = new stdClass();
                $appointment->column_appointment = $this->dof->get_string('appointment', 'employees');
                $appointment->column_appointment_begin = $this->dof->get_string('appointment_begin', 'employees');
                $appointment->id = $app->id;
                $appointment->enumber = $app->enumber;
                if ( $app->begindate )
                {// если дата начала обучения указана - то преобразуем ее из unixtime
                    $appointment->apbegindate = $this->dof->storage('persons')->get_userdate($app->begindate,'5d.%m.%Y');
                }else
                {// если не указана - то так и напишем, чтобы не писать про 1970 год
                    $eagreement->begindate = $this->dof->modlib('ig')->igs('no_specify');
                }
                
                $appointment->cstreams = array();
                // создаем массив с условиями для извлечения учителей
                $teacherconditions = array('appointmentid' => $app->id,
                                           'status'        => 'active');
                if( $teachers = $this->dof->storage('teachers')->get_objects_sorted_by_pitem($teacherconditions) )
                {// получили список преподаваемых предметов в этой должности
                    foreach( $teachers as $teach )
                    {
                        if ( $programmitem = $this->dof->storage('programmitems')->get($teach->programmitemid) )
                        {// синхронизирован с moodle
                            $appointment->column_course = $this->dof->get_string('course', 'employees');
                            $appointment->column_mdlcourse = $this->dof->get_string('mdlcourse', 'employees');;
                            $appointment->column_lastaccess = $this->dof->get_string('lastaccess_course', 'employees');
                            
                            //$report->data->column_countaccess = $this->dof->get_string('countaccess_course', 'programmsbcs');
                            $appointment->column_countaccessage = $this->dof->get_string('countaccessage_course', 'employees');
                            $appointment->column_countactions = $this->dof->get_string('countactions_course', 'employees');
                            $appointment->column_countactionsage = $this->dof->get_string('countactionsage_course', 'employees');
                            $appointment->column_messageforum = $this->dof->get_string('messageforum', 'employees');
                            $appointment->column_countgradedworks = $this->dof->get_string('countelements', 'employees');   
                            
                            if( $programmitem->mdlcourse AND $mdluser )
                            {
                                $cstream = $this->get_cstream_info($programmitem, $personid, $begindate, $enddate);    
                            }else
                            {
                                $cstream = new stdClass();
                                $cstream->namecourse = $programmitem->name.'['.$programmitem->code.']';
                                $cstream->mdlcourse     = ' - ';
                                // последний вход в курс
                                $cstream->lastaccsess   = ' - ';
                                // всего входов в курс
                                $cstream->allaccsess    = ' - ';
                                // количество записей в логах, связанных с этим курсом за период
                                $cstream->allrecordtime = ' - ';
                                // количество записей в логах, связанных с этим курсом за всё время 
                                $cstream->allrecord     = ' - ';
                                // ответов в форуме этого курса
                                $cstream->forumaswer    = ' - ';
                                // сданных работ в этом курсе
                                $cstream->readywork     = ' - ';
                            }
                           $appointment->cstreams[] = $cstream;
                        }
                    }
                    
                }
                $eagreement->appointments[] = $appointment;
            }
            
        }
        return $eagreement;
    }
 

    /** Метод записывает в отчет все данные по студентам и
     * возвращает уже полный отчет
     * @param object $report - отчет, по который доформировываем )
     * @return object $report - объект 
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// не того типа передали даные
            return false;
        }
        // переопределяем данные
        $begindate  = $report->begindate;
        $enddate    = $report->enddate;
        $teachers   = array();
        $conditions = array('status' => 'active');
        if ( $report->objectid )
        {// список рабочих контрактов по подразделению
            $conditions['departmentid'] = $report->objectid;
        }
        $eagreements = $this->dof->storage('eagreements')->get_listing($conditions);
        
        $teachers = array();
        if ( $eagreements )
        {
            // для того чтобы отобразить сколько контрактов осталось обработать - посчитаем их количество
            $totalcount   = count($eagreements);
            $currentcount = 0;
            foreach ( $eagreements as $obj)
            {// отчет по учителю
                // Выводим сообщение о том какой контракт проверяется сейчас, и сколько контрактов осталось
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing eagreementid: '.$obj->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->dof->mtrace(2, $mtracestring);
                
                // собираем данные по учителю
                if ( ! $person = $this->dof->storage('persons')->get($obj->personid) )
                {// пользователя нет в БД - ошибка
                    $this->dof->mtrace(2, 'ERROR:No person for eagreementid='.$obj->id.'!');
                    continue;
                }
                $template = new stdClass();
                $template->fio = $person->sortname; //фио
                
                $template->contracts = array();
                $contract = $this->get_data_full_report($obj, $person->id, $person->mdluser, $begindate, $enddate, $report->objectid);
                $template->contracts[] = $contract;
                
                $teachers[] = $template;
            }  
            // дозапишем наш объект 
            $report->data->teachers = $teachers;
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
