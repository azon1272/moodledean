<?php

// Подключаем библиотеки
require_once(dirname(realpath(__FILE__))."/../../lib.php");


class dof_sync_mreports_report_studentshort extends dof_sync_mreports_student
{
     // Параметры для работы с шаблоном
     protected $templatertype = 'sync';
     protected $templatercode = 'mreports';
     protected $templatertemplatename = 'short_report';
     /* Код плагина, объявившего тип приказа
     */
     function code()
     {
         return 'studentshort';
     }
     
     /* Имя плагина, объявившего тип приказа
     */ 
     function name()
     {
         return $this->dof->get_string('short_students', 'programmsbcs');
     }    

    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        $a = new stdClass();
        $a->begindate = $this->dof->storage('persons')->get_userdate($report->begindate,'%d.%m.%Y');
        $a->enddate = $this->dof->storage('persons')->get_userdate($report->enddate,'%d.%m.%Y');
        $report->name = $this->dof->get_string('short_teachers_of', 'programmsbcs', $a);
        return $report;
    }    
    
    /**
     * Дописываем в объект недостоющие поля
     */     
    protected function get_add_data_report($template,$opt)
    {
        // кол сданных работ
        $template->countelements = $this->get_send_task($opt['personid'],0, $opt['begindate'], $opt['enddate']);
        // кол активных cpassed
        $template->activecpasscstream = $this->dof->storage('cpassed')->count_list(array('studentid'=>$opt['personid'],'status'=>'active'));  
  
        return $template;
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
        $students   = array();
        $conditions = array('status' => 'work');
        if ( $report->objectid )
        {// отчет только по ученикам подразделения
            $conditions['departmentid'] = $report->objectid;
        }
        // список рабочих контрактов, отсортированных по ФИО ученика
        $contracts = $this->dof->storage('contracts')->
                        get_listing($conditions, null,null, "sortname");
        
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
                
                // собираем данные по ученику
                $student = $this->get_data_short_report($obj->studentid,$begindate,$enddate, array() );
                $students[] = $student;
            }  
            // дозапишем наш объект 
            $report->data->persons = $students;
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
            // данные для основной таблицы            
            $report->data->column_fio = $this->dof->get_string('fio', 'programmsbcs');
            $report->data->column_lastaccess = $this->dof->get_string('lastaccess', 'programmsbcs');
            $report->data->column_countaccess = $this->dof->get_string('countaccess', 'programmsbcs');
            $report->data->column_countaccessage = $this->dof->get_string('countaccessage', 'programmsbcs');
            $report->data->column_messageforum = $this->dof->get_string('messageforum', 'programmsbcs');
            $report->data->column_countcourse = $this->dof->get_string('countcourse', 'programmsbcs');
            $report->data->column_logage = $this->dof->get_string('logage', 'programmsbcs');
            $report->data->column_countelements = $this->dof->get_string('countelements', 'programmsbcs');
            $report->data->column_activecpassed = $this->dof->get_string('activecpassed', 'programmsbcs');
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