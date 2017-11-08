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

/** Полный отчет о деятельности учинека
 *
 */
class dof_im_journal_report_loadteachers extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'journal';
    protected $templatertemplatename = 'load_teachers';
    /* Код плагина, объявившего тип приказа
    */
    public function code()
    {
        return 'loadteachers';
    }
    
    /* Имя плагина, объявившего тип приказа
    */ 
    public function name()
    {
        return $this->dof->get_string('loadteachers', 'journal');
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
        $timezone = $this->dof->storage('departments')->get_timezone($report->departmentid);
        $a->begindate = dof_userdate($report->begindate,'%d.%m.%Y', $timezone );
        $a->enddate = dof_userdate($report->enddate,'%d.%m.%Y', $timezone);
        $report->name = $this->dof->get_string('loadteachers_time', 'journal', $a);
        
        return $report;
    }     
    
 

    /** Метод записывает в отчет все данные по студентам и
     * возвращает уже полный отчет
     * @param object $report - отчет, по который доформировываем )
     * @return object $report - объект 
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// Ошибочный тип данных
            return false;
        }
        // Логируем процесс
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        // Получаем временную зону, относительно которой формировались даты
        $timezone = $this->dof->storage('departments')->get_timezone($report->departmentid);
        
        // Начинаем формировать данные по учителям
        $teachers   = array();
        
        // Пусть останется так
        // учтем подразделения. тут ОБЪЕКТ имеет ту же величину, что и подразделение
        // он изначально так сохраняется
        if ( $report->objectid )
        {// Получаем назначения только одного подразделения
            // @todo получать также назначения дочерних подразделений
            $appoits = $this->dof->storage('appointments')->get_records(array('status'=>'active','departmentid'=>$report->objectid));
        } else 
        {// Получаем все назначения
            $appoits = $this->dof->storage('appointments')->get_records(array('status'=>'active'));
        }
        
        if ( $appoits )
        {// Назначения есть - начинаем рассчет
            
            // Для того чтобы отобразить сколько контрактов осталось обработать - посчитаем их количество
            $totalcount   = count($appoits);
            $currentcount = 0;
            
            foreach ( $appoits as $appoit )
            {// отчет о нагрузке учителя
                // Выводим сообщение о том какой контракт проверяется сейчас, и сколько контрактов осталось (Крон)
                ++$currentcount;
                $mtracestring = 'Prosessing appointid: '.$appoit->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->log_string($mtracestring, true);
                $this->log_string("\n\n");
                
                // Собираем данные по отдельному учителю
                $teacher = $this->get_string_load(
                        $appoit, // ID назначения на должность
                        $report->begindate, // Начальная дата
                        $report->enddate, // Конечная дата
                        $report->departmentid, // Подразделение
                        (bool) $report->data->forecast, // Делать прогноз часов
                        $report->data->reportid, // Отчет
                        $timezone // Временная зона
                );
                // Добавляем данные по назначению в общий массив
                $teachers[$appoit->id] = $teacher;
            }
            // Сортировка массива по имени
            uasort($teachers, array('dof_im_journal_report_loadteachers', 'sortapp_by_sortname2'));  
            
            // Дополнительная информация
            $report->data->info = $this->dof->get_string('info','journal');
            $report->data->depart = $this->dof->get_string('department','journal');
            if ( $report->departmentid )
            {// Отчет по подразделению
                $dep = $this->dof->storage('departments')->get($report->departmentid);
                $report->data->depart_name = $dep->name.'['.$dep->code.']';    
            }else 
            {// Общий отчет
                $report->data->depart_name = $this->dof->get_string('all_departs','journal');
            }
            $report->data->data_complete = $this->dof->get_string('data_complete','journal');
            $report->data->data_begin_name = $this->dof->get_string('data_begin','journal');
            $report->data->data_begin = dof_userdate($report->crondate,'%d.%m.%Y %H:%M');
            $report->data->request_name = $this->dof->get_string('request_name','journal');
            $report->data->requestdate = dof_userdate($report->requestdate,'%d.%m.%Y %H:%M');
            $report->data->column_teacher = $this->dof->get_string('teacher_fio','journal');
            $report->data->column_cstream = $this->dof->get_string('cstream','journal');
            $report->data->column_eagreement = $this->dof->get_string('eagreement','journal');
            $report->data->column_appoint = $this->dof->get_string('appointment','journal');
            $report->data->column_tabelload = $this->dof->get_string('week_tabel_load','journal');
            $report->data->column_fixload = $this->dof->get_string('week_fix_load','journal');
            $report->data->column_planload = $this->dof->get_string('plan_load','journal');
            $report->data->column_executeload = $this->dof->get_string('execute_load','journal');
            $report->data->column_replace = $this->dof->get_string('replace_postpone_events','journal');
            $report->data->column_cancel = $this->dof->get_string('cancel_events','journal');
            $report->data->column_salarypoints = $this->dof->get_string('loadteacher_rhours','journal');
            // Добавим данные по учителям
            $report->data->column_persons = $teachers;
        }
        
        return $report;
    }
    
    /**
     * Сбор данных по одному назначению на должность
     * 
     * @param int $appoit - ID назначения на должность
     * @param int $begindate - начальная дата временного интервала
     * @param int $enddate - конечная дата временного интервала
     * @param int $departmentid - ID подразделения
     * @param bool $forecast - рассчитывать поправку на конец месяца
     * @param int $reportid - ID отчета, использующегося для подсчета поправки
     * @param float $timezone - временная зона, относительно которой происходит сбор
     * 
     * @return object - объект с данными по назначению
     */
    public function get_string_load($appoit, $begindate, $enddate, $departmentid, $forecast, $reportid, $timezone)
    {
        $templater = new stdClass();
        
        // Формируем строку таблицы по нагрузке учителей за период
        if ( $person = $this->dof->storage('appointments')->get_person_by_appointment($appoit->id) )
        {// Если есть есть персона - выведем имя
            $templater->teacher = $this->dof->storage('persons')->get_fullname($person->id);
        } else 
        {// Персоны нет
            $templater->teacher = '';
        }
        // Номер договора
        $templater->eagreement = $this->dof->storage('eagreements')->get_field($appoit->eagreementid,'num');
        // Табельный номер
        $templater->appoint = $appoit->enumber;
        // Табельная нагрузка
        $templater->tabelload = round($appoit->worktime, 2);
        // Найдем назначенную нагрузку
        $templater->fixload = 0;
        if ( $cstreams = $this->dof->storage('cstreams')->get_records(array('appointmentid'=>$appoit->id,'status'=>'active')) )
        {// Потоки есть
            foreach ( $cstreams as $cstream )
            {// Суммируем часы
                $templater->fixload += $cstream->hoursweek;
            }
        }
        // Произведем рассчет нагрузки
        $templater->planload = 0; // Плановая
        $templater->executeload = 0; // Исполненная
        $templater->prevexecuteload = 0; // Исполненная нагрузка предыдущего отчета
        $templater->replace = 0; // Перенесенных часов
        $templater->cancel = 0; // Отмененные
        $salarypoints = 0; // Зарплатные часы
        $prevsalarypoints = 0; // Зарплатные часы за предыдущий месяц
        $totalrhours = 0; // Итого часов
        $templater->prevtotalrhours = 0; // Итого часов за предыдущий месяц
        $templater->events = array(); // Массив событий
        $templater->prevevents = array(); // Масив событий за предыдущий месяц
        $templater->prevforecast = 0; // Поправка за предыдущий месяц
        // Собираем данные для выборки событий
        $counds = new stdClass;
        $counds->appointmentid = $appoit->id; 
        $counds->date_from = $begindate;
        $counds->date_to = $enddate;
        $select = $this->dof->storage('schevents')->get_select_listing($counds);
        // Получаем события
        $events = $this->dof->storage('schevents')->get_records_select($select, null, 'date');
        if ( ! empty($events) )
        {// События есть
            foreach ( $events as $event )
            {// Обсчет каждого события
                if ( $event->status == 'canceled' )
                {// Отмененные уроки
                    $templater->cancel++;
                }
                if ( $event->status == 'completed' OR $event->status == 'implied')
                {// Исполненная нагрузка
                    $templater->executeload++;
                    $salarypoints += $event->rhours;
                }
                if ( $event->status != 'canceled' AND empty($event->replaceid) )
                {// Плановая нагрузка
                    $templater->planload++;
                }
                if ( $event->status == 'replaced' OR $event->status == 'postponed' )
                {// Замены
                    $templater->replace++;
                }
                if ( ! empty(unserialize($event->salfactorparts)->vars) AND $event->status != 'canceled' AND $event->status != 'replaced' )                
                {// Добавляем объект события
                    $templater->events[] = $this->get_string_event($event);
                }
            }
        }
        
        // Сумма баллов
        $templater->totalrhours = $salarypoints;
        
        // Поправка на текущий месяц
        if ( $forecast )
        { // Необходимо сделать поправку часов до конца месяца
            // Получаем число дней, прошедших в отчете между начальной и конечной датой
            $begindaydate = dof_usergetdate($begindate, $timezone);
            $enddaydate = dof_usergetdate($enddate, $timezone);
            $beginday = $begindaydate['mday'];
            $endday = $enddaydate['mday'];
            $intervaldays = $endday - $beginday + 1;
            // Получаем число дней в месяце
            $dateday = dof_usergetdate(mktime(12,0,0,$enddaydate['mon']+1,0,$enddaydate['year']));
            $allday = $dateday['mday'];
            
            // Получаем прогноз часов, наработанных до конца месяца 
            $templater->forecast = round( ( $salarypoints * $allday ) / $endday, '2') - $salarypoints;
            // Добавим к зарплатным часам поправку
            $salarypoints += $templater->forecast;
        }
        
        // Учет предыдущего отчета
        if ( $reportid )
        {// Указан предыдущий отчет, от которого надо вести расчет часов
            // Получаем отчет
            $report = $this->dof->storage('reports')->get($reportid);
            $reportobj = $this->dof->storage('reports')->report(
                        $report->plugintype,
                        $report->plugincode,
                        $report->code,
                        $report->id
                 );
            // Получаем данные отчета
            $reportdata = $reportobj->load_file();
            
            // Получаем временную зону, относительно которой формировались даты предыдущего отчета
            $timezone = $this->dof->storage('departments')->get_timezone($report->departmentid);
            // Получаем дату окончания расчета для предыдущего отчета
            $prevendday = dof_usergetdate($report->enddate, $timezone);

            if ( isset($reportdata->column_persons[$appoit->id]) )
            {// В предыдущем отчете есть данные по текущему должостному назначению
                // Получаем данные по персоне
                $person = $reportdata->column_persons[$appoit->id];
                
                if ( isset($person->forecast) )
                {// Поправка за предыдущий месяц по отчету
                    $templater->prevforecast = $person->forecast;
                }
        
                // Получим конец месяца по временной зоне отчета
                $prevalldaydate = dof_make_timestamp($prevendday['year'], $prevendday['mon'] + 1, 0, 23, 55, 0, $timezone );
                
                // Сформруем данные для получения событий от конца корректировочного отчета 
                // до начала текущего
                $selectstr = '';
                $selectstr .= ' appointmentid = '.$appoit->id;
                $selectstr .= ' AND date > '.$report->enddate;
                $selectstr .= ' AND date < '.$prevalldaydate;
                // Получаем события
                $events = $this->dof->storage('schevents')->get_records_select($selectstr, null, 'date');
                // Подсчитаем реальное количество отработаных часов от конца корректировочного отчета 
                // до начала текущего
                foreach ( $events as $event )
                {
                    if ( $event->status == 'completed' OR $event->status == 'implied')
                    {// Исполненная нагрузка за неучтенную в предыдущем отчете часть месяца 
                        $templater->prevexecuteload++;
                        $templater->prevtotalrhours += $event->rhours;
                    } 
                    $templater->prevevents[] = $this->get_string_event($event);
                }
            }
        }
        
        // Зарплатные баллы
        $templater->salarypoints = $salarypoints;
        $templater->url = '';
        if ( $salarypoints > 0 )
        {
            $url_params = array('id' => $this->id,
                    'appointid' => $appoit->id,
                    'begindate' => $begindate,
                    'enddate' => $enddate,
                    'departmentid' => $departmentid);
            $templater->url = $this->dof->url_im('journal',
                    '/reports/loadteachers/loadteacher.php', $url_params);
            $templater->salarypoints = '<a href='.$templater->url.'>'.$templater->salarypoints.'</a>';     
        }
        
        return $templater;
    }

    /**
     * Строка для вывода одного события
     * @return object $templater - объект с данныим для строчки события
     */
    public function get_string_event($event)
    {
        // добаваем параметры
        $salfactorparts = unserialize($event->salfactorparts);
        $params = $salfactorparts->vars;
        $obj = new stdClass();
        // время
        $obj->formula = $salfactorparts->formula;
        
        // число
        $obj->date = dof_userdate($event->date, "%d/%m/%Y");
        
        // время
        $obj->time = dof_userdate($event->date, "%H:%M");
        
        // прдолжительность урока
        $obj->duration = ($event->duration/60).' ' .$this->dof->modlib('ig')->igs('min').'. ';
        $obj->individual = $this->dof->get_string('no', 'journal');
        if ($params['schevent_individual'])
        {// индивидуальный урок
            $obj->individual = $this->dof->get_string('yes', 'journal');
        }
        
        // количество студентов
        $obj->countstudents = $params['count_active_cpassed'];
        $obj->countstudents_salfactor = $params['config_salfactor_countstudents'];
        
        // поправочный зарплатный коэффициент предмета
        $obj->salfactor_programmitem = $params['programmitem_salfactor'];
        
        // поправочный зарплатный коэффициент подписок
        $obj->salfactor_programmsbcs = $params['programmsbcs_salfactors']['all'];
        unset($params['programmsbcs_salfactors']['all']);
        $obj->programmsbcs = array();
        foreach ( $params['programmsbcs_salfactors'] as $id=>$salfactor )
        {
            $programmsbc_salfactor = new stdClass;
            $departmentid = $this->dof->storage('programmsbcs')->get_field($id,'departmentid');
            $url_params = array('programmsbcid' => $id,
                'departmentid' => $departmentid);
            $programmsbc_salfactor->salfactor = '<a href='.$this->dof->url_im('programmsbcs',
        '/view.php', $url_params).'>'.$salfactor.'</a>';
            $obj->programmsbcs[] = $programmsbc_salfactor;
        }
        
        // поправочный зарплатный коэффициент групп
        $obj->salfactor_agroups = $params['agroups_salfactors']['all'];
        unset($params['agroups_salfactors']['all']);
        $obj->agroups = array();
        foreach ( $params['agroups_salfactors'] as $id=>$salfactor )
        {
            $agroup_salfactor = new stdClass;
            $departmentid = $this->dof->storage('agroups')->get_field($id,'departmentid');
            $url_params = array('agroupid' => $id,
                'departmentid' => $departmentid);
            $agroup_salfactor->salfactor = '<a href='.$this->dof->url_im('agroups',
        '/view.php', $url_params).'>'.$salfactor.'</a>';
            $obj->agroups[] = $agroup_salfactor;
        }
        $obj->color = 'black';
        if ( $event->status == 'implied')
        {// исполненная нагрузка
            $obj->color = '#A52A2A';
        }
        // поправочный зарплатный коэффициент потока
        $obj->salfactor_cstreams = $params['cstreams_salfactor'];
        
        // замещающий зарплатный коэффициент потока
        $obj->substsalfactor_cstreams = $params['cstreams_substsalfactor'];
        
        // поправочный зарплатный коэффициент шаблона
        $obj->salfactor_schtemplates = $params['schtemplates_salfactor'];
        
        // выплата совместителям
        if ( ! isset($params['payment_combination']) )
        {// если не было - вставим по-умолчанию
            $params['payment_combination'] = 1;
        }
        $obj->payment_combination = $params['payment_combination'];

        // тип урока
        if ( ! isset($params['schevent_type']) )
        {// если не было - вставим по-умолчанию
            $params['schevent_type'] = 1;
        }
        $obj->type = $params['schevent_type'];
        
        // академические часы
        $obj->ahours = $params['ahours'];
        
        // оооведение урока по факту
        $obj->complete = $params['schevents_completed'];
        
        // суммарный балл
        $obj->rhours = $event->rhours;
        
        // Получаем учебный процесс 
        if ( isset($event->cstreamid) )
        {
            $obj->cstreamid = $event->cstreamid;
        } else 
        {// ID не передан
            $obj->cstreamid = null;
        }
        
        // добавляем строку в таблицу
        return $obj;
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
            if ( isset($template->column_persons) )
            {
                $templater = $this->template();                
                if ( ! $templater )
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
        
        if ( ! $error )
        {// вывод легенды
            print '<b>'.$this->dof->get_string('legend', 'journal').':</b><br>
               - '.$this->dof->get_string('legend_week_tabel_load', 'journal').';<br>
               - '.$this->dof->get_string('legend_week_fix_load', 'journal').';<br>
               - '.$this->dof->get_string('legend_plan_load', 'journal').';<br>
               - '.$this->dof->get_string('legend_execute_load', 'journal').';<br>
               - '.$this->dof->get_string('legend_replace_postpone_events', 'journal').';<br>
               - '.$this->dof->get_string('legend_cancel_events', 'journal').';<br>
               - '.$this->dof->get_string('legend_salfactors', 'journal').'.<br><br>';
            // скачать в формате csv
            print '<a href="'.$this->dof->url_im('journal',
                '/reports/import.php?reportid='.$this->id.'&type=loadteachers',$addvars).'">'.
                $this->dof->get_string('download_excel','journal','csv').'</a>';
            echo '<br>';
            // скачать в формате xml
            print '<a href="'.$this->dof->url_im('journal',
                '/reports/import.php?reportid='.$this->id.'&type=loadteachers&format=xls',$addvars).'">'.
                $this->dof->get_string('download_excel','journal','xls').'</a>';
        }
    } 

    protected function template_data($template)
    {
        if ( $template AND ! isset($template->column_salarypoints) )
        {// установлена старая структура отчета - добавим зарплатные баллы
            $template->column_salarypoints = $this->dof->get_string('salary_points', 'journal');
            foreach ($template->column_persons as $key=>$person)
            {
                $person->salarypoints = 0;
                $template->column_persons[$key] = $person;
            }
        }
        if ( isset($template->forecast) AND $template->forecast )
        {
            $template->column_correction = '<th class="header c0">'.
                $this->dof->get_string('correction_for_previous_month', 'journal').'</th>';
            foreach ($template->column_persons as $key=>$person)
            {
                $person->correction = '<td style="text-align:center;" class="cell c0">'.
                    ($person->prevtotalrhours - $person->prevforecast).'</td>';
                $template->column_persons[$key] = $person;
            }
        }
        return $template;
    }  
    
    /**
     * Функция сравнения двух объектов 
     * из таблицы persons по полю sortname
     * @param object $person1 - запись из таблицы persons
     * @param object $person2 - другая запись из таблицы persons
     * @return -1, 0, 1 в зависимости от результата сравнения
     */
    private function sortapp_by_sortname2($person1,$person2)
    {
        return strnatcmp($person1->teacher, $person2->teacher);
    }  
    
    public function dof_im_journal_get_loadteacher($appointmentid, $begindate, $enddate)
    {
        $template = $this->load_file();
        $teacher = new stdClass();
        //print_object($template);

        $teacher->title = $template->_name;
        if ( isset($template->forecast) AND $template->forecast )
        {
            $teacher->name_onetable = $this->dof->get_string('correction_for_previous_month', 'journal');
            $teacher->name_twotable = $this->dof->get_string('salhours_for_1_25_days', 'journal');
            $teacher->name_treetable = $this->dof->get_string('total_to_pay', 'journal');
            $teacher->prevloaddata = $template->column_persons[$appointmentid]->prevevents;
            if ( ! empty($teacher->prevloaddata) )
            {// уроки не пустые
                foreach ( $teacher->prevloaddata as $id=>$key )
                {
                    $teacher->prevloaddata[$id]->prevprogrammsbcs = 
                          $template->column_persons[$appointmentid]->prevevents[$id]->programmsbcs;
                    foreach ( $teacher->prevloaddata[$id]->prevprogrammsbcs as $key=>$value )
                    {
                        $teacher->prevloaddata[$id]->prevprogrammsbcs[$key]->prevsalfactor =
                        $teacher->prevloaddata[$id]->prevprogrammsbcs[$key]->salfactor;
                        unset($teacher->prevloaddata[$id]->prevprogrammsbcs[$key]->salfactor);
                    }
                    $teacher->prevloaddata[$id]->prevagroups = 
                          $template->column_persons[$appointmentid]->prevevents[$id]->agroups;
                }
            }
            $teacher->score = $this->dof->get_string('loadteacher_score', 'journal');
            // фактические часы
            $teacher->prevexecuteload = $template->column_persons[$appointmentid]->prevexecuteload;
            // сумма баллов
            $teacher->prevtotalrhours = $template->column_persons[$appointmentid]->prevtotalrhours;
            $teacher->prevforecast = $this->dof->get_string('paid_for_previous_month', 'journal').
                      ' = <b>'.$template->column_persons[$appointmentid]->prevforecast.'</b>';
            $teacher->prevrhours = $this->dof->get_string('execute_for_previous_month', 'journal').
                      ' = <b>'.$template->column_persons[$appointmentid]->prevtotalrhours.'</b>';
            $teacher->correction = $this->dof->get_string('correction_for_previous_month', 'journal').
                      ' = <b>'.round($template->column_persons[$appointmentid]->prevtotalrhours-
                                $template->column_persons[$appointmentid]->prevforecast, 2).'</b>';
            $teacher->allrhours = $this->dof->get_string('salhours_for_1_25_days', 'journal').
                      ' = <b>'.$template->column_persons[$appointmentid]->totalrhours.'</b>';
            $teacher->forecast = $this->dof->get_string('forecast_on_end_month', 'journal').
                      ' = <b>'.$template->column_persons[$appointmentid]->forecast.'</b>';
            $teacher->totalpay = $this->dof->get_string('total_to_pay', 'journal').
                      ' = <b>'.($template->column_persons[$appointmentid]->prevtotalrhours-
                                $template->column_persons[$appointmentid]->prevforecast+
                                $template->column_persons[$appointmentid]->totalrhours+
                                $template->column_persons[$appointmentid]->forecast).'</b>';
        }
        if ( $person = $this->dof->storage('appointments')->get_person_by_appointment($appointmentid) )
        {// если есть есть персона - выведем имя
            $teacher->fullname = $this->dof->storage('persons')->get_fullname($person->id);
        }else
        {
            $teacher->fullname = '';
        }

        $teacher->column_date = $this->dof->get_string('loadteacher_date', 'journal');
        $teacher->column_cstream = $this->dof->get_string('cstream', 'journal');
        $teacher->column_time = $this->dof->get_string('loadteacher_time', 'journal');
        $teacher->column_duration = $this->dof->get_string('loadteacher_duration', 'journal');
        $teacher->column_individual = $this->dof->get_string('loadteacher_individual', 'journal');
        $teacher->column_countstudents = $this->dof->get_string('loadteacher_countstudents', 'journal');
        $teacher->column_salfactor_programmitem = $this->dof->get_string('loadteacher_programmitem', 'journal');
        $teacher->column_salfactor_students = $this->dof->get_string('loadteacher_students', 'journal');
        $teacher->column_salfactor_cstream  = $this->dof->get_string('loadteacher_cstream', 'journal');
        $teacher->column_substsalfactor_cstream  = $this->dof->get_string('loadteacher_cstreamsub', 'journal');
        $teacher->column_salfactor_schtemplate = $this->dof->get_string('loadteacher_schtemplate', 'journal');
        $teacher->column_salfactor_agroup = $this->dof->get_string('loadteacher_agroup', 'journal');
        $teacher->column_complete = $this->dof->get_string('loadteacher_complete', 'journal');
        $teacher->column_payment_combination = $this->dof->get_string('payment_combination', 'journal');
        $teacher->column_type = $this->dof->get_string('schevent_type', 'journal');
        $teacher->column_ahours = $this->dof->get_string('loadteacher_ahours', 'journal');
        
        // суммарный коэффициент и формула расчета
        $teacher->column_rhours = $this->dof->get_string('loadteacher_rhours', 'journal');
        $teacher->loaddata = $template->column_persons[$appointmentid]->events;
        $teacher->score = $this->dof->get_string('loadteacher_score', 'journal');
        // фактические часы
        $teacher->executeload = $template->column_persons[$appointmentid]->executeload;
        // сумма баллов
        $teacher->totalrhours = $template->column_persons[$appointmentid]->totalrhours;
        // выводим нагрузку учителя
        // Если данные есть
        if ( ! empty($teacher->loaddata) )
        {
            // Получим текущее подразделение
            $depid = optional_param('departmentid', 0, PARAM_INT);
            // Сформируем массив GET параметров
            $addvars = array('departmentid' => $depid);
            // Создаем ссылку на процесс для каждой строки
            foreach ( $teacher->loaddata as $event )
            {
                // Если передан cstream
                if ( ! empty($event->cstreamid) )
                {
                    // Добавляем id процеса
                    $addvars['csid'] =  $event->cstreamid;
                    // Формируем сылку
                    $cstreamhref = $this->dof->url_im('journal','/group_journal/index.php',$addvars);
                    // Получаем имя процесса
                    $cstreamname = $this->dof->storage('cstreams')->get_field($event->cstreamid, 'name');
                    if ( empty($cstreamname) )
                    {
                        // Добавляем ссылку на журнал
                        $event->cstream = '<a href="'.$cstreamhref.'" target="_blank" >[]</a>';
                    } else {
                        // Добавляем ссылку на журнал
                        $event->cstream = '<a href="'.$cstreamhref.'" target="_blank" >'.$cstreamname.'</a>';
                    }
                }
            }
        }
        if ( isset($template->forecast) AND $template->forecast )
        {
           $templater_package = $this->dof->modlib('templater')->template('im', 'journal', $teacher, 'load_forecast');
           print($templater_package->get_file('html'));
        }else
        {
            $templater_package = $this->dof->modlib('templater')->template('im', 'journal', $teacher, 'load_salfactors');
            print($templater_package->get_file('html'));
        }
        
    }  
    
}

?>