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

// Подключаем библиотеки
require_once(dirname(realpath(__FILE__))."/../../lib.php");

/** Краткий отчет о деятельности учителя
 *
 */
class dof_sync_mreports_report_teachershort extends dof_sync_mreports_teacher
{
     // Параметры для работы с шаблоном
    protected $templatertype = 'sync';
    protected $templatercode = 'mreports';
    protected $templatertemplatename = 'short_report';

    /* Код плагина, объявившего тип приказа
     */
    function code()
    {
        return 'teachershort';
    }
    
    /* Имя плагина, объявившего тип приказа
    */ 
    function name()
    {
        return $this->dof->get_string('short_teachers', 'employees');
    }    
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        $a = new stdClass();
        $a->begindate = $this->dof->storage('persons')->get_userdate($report->begindate,'%d.%m.%Y');
        $a->enddate = $this->dof->storage('persons')->get_userdate($report->enddate,'%d.%m.%Y');
        $report->name = $this->dof->get_string('short_teachers_of', 'employees', $a);
        return $report;
    }

    /**
     * Дописываем в объект недостоющие поля
     */     
    protected function get_add_data_report($template,$opt)
    {
        // кол активных cstreams
        $template->activecpasscstream = $this->dof->storage('cstreams')->count_list(array('teacherid'=>$opt['personid'],'status'=>'active'));
        // проверенные  работы
        $template->countelements = $this->count_graded_items($opt['personid'], null, $opt['begindate'], $opt['enddate']);

        
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
        $begindate = $report->begindate;
        $enddate   = $report->enddate;
                
        $teachers = array();
        $conditions = array('status' => 'active');
        if ( $report->objectid )
        {// список рабочих контрактов по подразделению
            $conditions['departmentid'] = $report->objectid;
        }
        $eagreements = $this->dof->storage('eagreements')->get_listing($conditions);
        if ( $eagreements )
        {
            // для того чтобы отобразить сколько контрактов осталось обработать - посчитаем их количество
            $totalcount   = count($eagreements);
            $currentcount = 0;
            foreach ( $eagreements as $obj)
            {
                // Выводим сообщение о том какой контракт проверяется сейчас, и сколько контрактов осталось
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing eagreementid: '.$obj->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->dof->mtrace(2, $mtracestring);
                // собираем данные по учителю
                $teacher = $this->get_data_short_report($obj->personid,$begindate,$enddate,$opt = array() );
                $teachers[] = $teacher;
            }
        }
        // дозапишем наш объект
        $report->data->persons = $teachers;
        // допол инфа
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
        $report->data->column_fio = $this->dof->get_string('fio', 'employees');
        $report->data->column_lastaccess = $this->dof->get_string('lastaccess', 'employees');
        $report->data->column_countaccess = $this->dof->get_string('countaccess', 'employees');
        $report->data->column_countaccessage = $this->dof->get_string('countaccessage', 'employees');
        $report->data->column_messageforum = $this->dof->get_string('messageforum', 'employees');
        $report->data->column_countcourse = $this->dof->get_string('countcourse', 'employees');
        $report->data->column_logage = $this->dof->get_string('logage', 'employees');
        $report->data->column_countelements = $this->dof->get_string('countelements', 'employees');
        $report->data->column_activecpassed = $this->dof->get_string('activecstreams', 'employees');        
        
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