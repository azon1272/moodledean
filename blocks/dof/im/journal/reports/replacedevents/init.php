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

/** Отчет о замененных уроках
 *
 */
class dof_im_journal_report_replacedevents extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'journal';
    protected $templatertemplatename = 'replacedevents';
    protected $departmentid;
    /* Код плагина, объявившего тип приказа
     * 
     */
    public function code()
    {
        return 'replacedevents';
    }
    
    /* Название отчета
     * 
     */ 
    public function name()
    {
        return $this->dof->get_string('replacedevents', 'journal');
    }
    
    /*
     * Тип плагина
     */
    public function plugintype()
    {
        return 'im';
    }
    
    /*
     * Код плагина
     */
    public function plugincode()
    {
        return 'journal';
    }    
    
    
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        $a = new stdClass();
        $a->begindate = dof_userdate($report->begindate,'%d.%m.%Y');
        $a->enddate   = dof_userdate($report->enddate,'%d.%m.%Y');
        $report->name = $this->dof->get_string('replacedevents_time', 'journal', $a);
        return $report;
    }     
    
 

    /** Метод записывает в отчет все данные по замененным урокам и
     * возвращает уже полный отчет
     * @param object $report - отчет, по который формируем
     * @return object $report - сформированный отчет
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// не того типа передали даные
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        // высчитываем время с учетом часового пояса пользователя
        $beginday   = dof_usergetdate($report->begindate);   
        $endday     = dof_usergetdate($report->enddate); 
        $begindate  = mktime(0,0,0,$beginday['mon'],$beginday['mday'],$beginday['year']);
        $enddate    = mktime(24,0,0,$endday['mon'],$endday['mday'],$endday['year']);
        $this->departmentid = $report->departmentid;
        // Получаем все проведенные уроки, являющиеся заменами
        $finalevents = $this->get_final_events($begindate, $enddate);
        
        if ( $finalevents )
        {
            $events = array();
            // для того чтобы отобразить сколько уроков осталось обработать - посчитаем их количество
            $totalcount   = count($finalevents);
            $currentcount = 0;
            
            foreach ( $finalevents as $event )
            {
                // Выводим сообщение о том какой что проверяется сейчас, и сколько осталось
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing eventid: '.$event->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->log_string($mtracestring, true);
                $this->log_string("\n\n");
                // Получаем все данные для отображения в шаблоне
                if ( $eventdata = $this->get_string_event($event) )
                {
                    $events[] = $eventdata;
                }
            }
            
            // допол информация
            $report->data->info   = $this->dof->get_string('info','journal');
            $report->data->depart = $this->dof->get_string('department','journal');
            if ( $report->departmentid )
            {// отчет по подразделению
                $report->data->depart_name = $this->dof->im('departments')->get_html_link($id, true);  
            }else 
            {// все отчеты
                $report->data->depart_name = $this->dof->get_string('all_departs','journal');
            }
            // Данные о времени сбора отчета  
            $report->data->data_complete            = $this->dof->get_string('data_complete','journal');
            $report->data->data_begin_name          = $this->dof->get_string('data_begin','journal');
            $report->data->data_begin               = dof_userdate($report->crondate,'%d.%m.%Y %H:%M');
            $report->data->request_name             = $this->dof->get_string('request_name','journal');
            $report->data->requestdate              = dof_userdate($report->requestdate,'%d.%m.%Y %H:%M');
            // задаем название столбцов таблицы 
            $report->data->column_date              = $this->dof->modlib('ig')->igs('date');
            $report->data->column_oldteacher        = $this->dof->get_string('old_event_teacher','journal');
            $report->data->column_oldpitem          = $this->dof->get_string('old_event_pitem','journal');
            $report->data->column_eventrhours       = $this->dof->get_string('event_rhours','journal');
            $report->data->column_student           = $this->dof->get_string('student_or_group','journal');
            $report->data->column_newteacher        = $this->dof->get_string('new_event_teacher','journal');
            $report->data->column_newpitem          = $this->dof->get_string('new_event_pitem','journal');
            // список замененных уроков
            $report->data->column_events            = $events;
        }
        
        return $report;
    }
    
    /** Строка для вывода одного замененного события
     * @param object $finalevent - событие из таблицы schevents. Проведенные уроки, являющиеся заменами
     * 
     * @return object $templater - объект с данныим для строчки события
     */
    public function get_string_event($finalevent)
    {
        $templater = new stdClass()($finalevent);
        // Получаем событие с которого все началось
        // ФИО заменяющего учителя
        $templater->newteacher = $this->get_event_teacher($finalevent);
        // Проведенный предмет
        $templater->newpitem = '';
        $templater->newdate = dof_userdate($finalevent->date,'%d.%m.%Y %H:%M');
        if ( $cstream = $this->dof->storage('cstreams')->get($finalevent->cstreamid) )
        {
            $templater->newpitem = $this->dof->im('programmitems')->get_html_link($cstream->programmitemid, 
                                   false, array('departmentid'=>$this->departmentid));
        }
        
        //зарплатные часы
        $templater->eventrhours = $finalevent->rhours;
            
        if ( ! $event = $this->dof->storage('schevents')->get($finalevent->replaceid) )
        {// не нашли заменяемый урок
            return false;
        }
        // Запланированная дата проведения урока
        $templater->date = dof_userdate($event->date,'%d.%m.%Y %H:%M');
        
        // ФИО пропустившего учителя 
        $templater->oldteacher = $this->get_event_teacher($event);
        
        // Запланированный предмет
        $templater->oldpitem = '';
        // Класс или ученик
        $templater->student  = '';
        if ( $cstream = $this->dof->storage('cstreams')->get($event->cstreamid) )
        {
            $templater->oldpitem = $this->dof->im('programmitems')->get_html_link($cstream->programmitemid, 
                                   false, array('departmentid'=>$this->departmentid));
            $templater->student  = html_writer::div( 
                $this->dof->im('cstreams')->get_html_link($cstream->id,
                    $this->dof->get_string('cstream_members','journal').":", 
                    [
                        'departmentid' => $this->departmentid
                    ]));
            
            // Определяем, кто должен был учавствовать в событии: класс или ученик
            $agroups  = array();
            $students = array();
            if ( $cslinks = $this->dof->storage('cstreamlinks')->get_records(array('cstreamid'=>$cstream->id)) )
            {// это группа
                foreach ( $cslinks as $cslink )
                {
                    $agroups[] = $this->dof->im('agroups')->get_html_link($cslink->agroupid, 
                                 false, array('departmentid'=>$this->departmentid));
                }
                $templater->student .= implode(',<br>', $agroups);
            }elseif ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$cstream->id)) )
            {// Это отдельные ученики
                foreach ( $cpassed as $cpobj )
                {
                    $students[] = $this->dof->im('persons')->get_fullname($cpobj->studentid,true,null,$this->departmentid); 
                }
                $templater->student .= implode(',<br>', $students);
            }
        }
        return $templater;
    }
    
    /** Получить все проведенные уроки за указанный период (в одном подразделении или во всех)
     *  которые являлись конечными заменами (последним звеном в цепочке замен)
     * @param int $begindate - Дата начала периода, за который собираются замененные уроки (unixtime)
     * @param int $endate - окончание периода, когда собираются замененные уроки (unixtime)
     * @param int $departmentid[optional] - id подразделения для которого собираются уроки
     * @param string $sort[optional] - порядок сортировки уроков 
     *                                    (по умолчанию - по запланированному времени, по возрастанию)
     * 
     * @return array - массив записей из таблицы schevents
     */
    protected function get_final_events($begindate, $enddate, $departmentid=null, $sort='date ASC')
    {
        // составляем условия выборки
        $params = array();
        $params['begindate']    = $begindate;
        $params['enddate']      = $enddate;
        $select = 'date >= ? AND (date+duration) <= ? AND 
                    status IN ("plan", "completed", "postponed")  AND replaceid IS NOT NULL';
        
        return $this->dof->storage('schevents')->get_records_select($select, $params, $sort, 
            'id, planid, cstreamid, date, duration, place, replaceid, form, appointmentid, rhours');
    }
    
    /** Получить ссылку на сотрудника на которого запланировано событие (вместе с договором)
     * @param object $event - событие из таблицы schevents
     * 
     * @return string - html-ссылка на сотрудника и его тоговор или пустая строка
     */
    protected function get_event_teacher($event)
    {
        if ( $person = $this->dof->storage('appointments')->get_person_by_appointment($event->appointmentid) )
        {
            $appointment = $this->dof->storage('appointments')->get($event->appointmentid);
            $eagreementlink = '';
            if ( $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
            {// @todo сейчас при переходе будет сбиваться подразделение - а у нас нет
                // стандартных способов отслеживания глобального состояния
                // когда их придумаете - исправьте ссылку
                $eagreementlink = '<a href="'.$this->dof->url_im('employees', 
                    '/view_eagreement.php?id='.$eagreement->id.'&dapartmentid='.$this->departmentid).'">['.$eagreement->num.']</a>';
            }
            // Показываем ссылку на учителя и на договор
            return $this->dof->im('persons')->get_fullname($person->id,true,null,$this->departmentid).' '.$eagreementlink;
        }
        
        return '';
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
            if ( isset($template->column_events) )
            {
                if ( ! $templater = $this->template() )
                {//не смогли
                    $error = $this->dof->get_string('report_no_get_template','journal');
                }elseif ( ! $table = $templater->get_file('html') )
                {// не смогли загрузить html-таблицу
                    $error = $this->dof->get_string('report_no_get_table','journal');
                }
            }else 
            {
                $error = $this->dof->get_string('no_data','journal','<br>');
            }
        }
        
        // вывод ошибок
        print '<p style=" color:red; text-align:center; "><b>'.$error.'</b></p>';
        echo $table;
    }
    
    /**
     * Метод, предусмотренный для расширения логики отображения данных отчета
     */
    protected function template_data($template)
    {
        // ловим данный из формы template
        $formdata = $_POST;
        // все фильтры пока активны
        $optiondate = 'checked';
        $optionteacher = 'checked';
        $optioncstream = 'checked';
        if ( !empty($formdata) AND empty($formdata['date']) )
        {// отменили фильтр замены по дате - снимем галку
            $optiondate = '';
        }
        if ( !empty($formdata) AND empty($formdata['teacher']) )
        {// отменили фильтр замены по учителю - снимем галку
            $optionteacher = '';
        }
        // формируем отображение типа замены
        $form = '<form id="form_select_type" method="post" action="">'.
                '<input type="checkbox" name="date" '.$optiondate.'/>'.
                $this->dof->get_string('replace_event_date','journal').'<br>'.
                '<input type="checkbox" name="teacher" '.$optionteacher.'/>'.
                $this->dof->get_string('replace_event_teacher','journal').'<br>'.
                '<input name="remove" id="remove" type="submit" value="'.
                $this->dof->modlib('ig')->igs('show').'" title="'.
                $this->dof->modlib('ig')->igs('show').'" />';
        $template->form_title = $this->dof->get_string('filter_type','journal');
        $template->form = $form;
        $template->column_type = $this->dof->get_string('type_replace_event','journal');
        if ( isset($template->column_events) )
        {// если уроки есть
            // переформируем массив отображаемых уроков заново
            $events = array();
            foreach ( $template->column_events as $num=>$event )
            {
                $type = array();
                if ( isset($event->newdate) AND $event->date != $event->newdate )
                {// замена другим временем
                    if ( empty($formdata) OR !empty($formdata['date']) )
                    {// выбран фильтр - добавим урок в массив
                        $events[$num] = $event;
                    }
                    $type[] = $this->dof->get_string('replace_event_date','journal');
                }
                if ( $event->oldteacher != $event->newteacher )
                {// замена другим учителем
                    if ( empty($formdata) OR !empty($formdata['teacher']) )
                    {// выбран фильтр - добавим урок в массив
                        $events[$num] = $event;
                    }
                    $type[] = $this->dof->get_string('replace_event_teacher','journal');
                }
                if ( $event->oldpitem != $event->newpitem )
                {// замена другим потоком
                    if ( empty($formdata) OR !empty($formdata['cstream']) )
                    {// выбран фильтр - добавим урок в массив
                        $events[$num] = $event;
                    }
                    $type[] = $this->dof->get_string('replace_event_cstreams','journal');
                }
                // объединяем все типы в одну строчку
                if ( isset($events[$num]) )
                {// урок попал в переформированный массив
                    $events[$num]->type = implode('<br>',$type);
                }
            }
            // сохраняем переформированный массив в данные отчета
            $template->column_events = $events;
        }
        return $template;
    }  
}
