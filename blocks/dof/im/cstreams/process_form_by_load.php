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
 * обработчик формы страницы для отображения подробной информации о потоках
 */

class dof_im_cstreams_process_form_by_load
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $eadepid;
    private $apdepid;
    private $cstreamdepid;
    private $personid;
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof, $eadepid, $apdepid, $cstreamdepid, $personid)
    {
        $this->dof = $dof;
        $this->eadepid = $eadepid;
        $this->apdepid = $apdepid;
        $this->cstreamdepid = $cstreamdepid;
        $this->personid = $personid;
    }
    
    /** Возвращает таблицы с нагрузками учитьелей
     * @return string html-код таблиц
     */
    public function get_teachers_load()
    {
        // найдем табельные номера отсортированные по персонам
        if ( ! is_null($this->personid) )
        {// если персона не пустая - отобразим всю информацию
            $persons = $this->get_array_appointments();
            if ( empty($persons) )
            {// табельные номера не найдены - таблиц нет
                return '';
            }   
        }else
        {// персона пустая - сообщим, что надо что-то выбрать
            return '<br><p align="center"><b>'.$this->dof->get_string('select_person','cstreams').'</b></p>';
        }
        $rez = '';
        foreach ( $persons as $id=>$person )
        {// для каждого табеля формируем строчку
            $rez .= $this->get_table_person($person, $id).'<br>';
        }
        return '<br>'.$rez;
        
    }
    
    /** Возвращает список табельных номеров отсортированных по персонам
     * @return array массив персон с табельными номерами
     */
    private function get_array_appointments()
    {
        $conds = new stdClass();
        $conds->eagreementdepartmentid = $this->eadepid;
        $conds->departmentid = $this->apdepid;
        if ( $this->personid )
        {// есть персона - добавим ее к поиску
            $conds->personid = $this->personid;
        }
        $conds->status = array('plan','active');
        $mas = array();
        if ( $appointments = $this->dof->storage('appointments')->get_teacher_list($conds) )
        {
            foreach ( $appointments as $appointment )
            {// для каждого табеля
                $mas[$appointment->personid][$appointment->id] = $appointment;
            }
        }
        return $mas;
        
        
    }
    /** Возвращает строку для отображения данных о потоке
     * @param object $cstream - объект записи из таблицы cstreams БД
     * @return array - массив для вставки в таблицу
     */
    private function get_string_info_cstream($cstream)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $cstreamname = '<a href="'.$this->dof->url_im('cstreams', '/view.php?cstreamid='.$cstream->id,$addvars).'">'.
               $this->dof->storage('cstreams')->change_name_cstream($cstream).'</a>';
        // имя программы
        $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'programmid');
        $programname = '<a href="'.$this->dof->url_im('programms', '/view.php?programmid='.
                    $programmid,$addvars).'">'.
                    $this->dof->storage('programms')->get_field($programmid,'name').' <br>['.
                    $this->dof->storage('programms')->get_field($programmid,'code').']';
        // имя предмета
        $itemname = '<a href="'.$this->dof->url_im('programmitems', '/view.php?pitemid='.
                    $cstream->programmitemid,$addvars).'">'.
                    $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'code').']';
        
        // ссылки
        $link = '';
        if ( $this->dof->storage('cstreams')->is_access('edit', $cstream->id) OR 
             $this->dof->storage('cstreams')->is_access('edit/plan', $cstream->id) )
        {
            $link .= '<a href="'.$this->dof->url_im('cstreams', '/edit.php?cstreamid='.$cstream->id,$addvars).
                     '"><img src="'.$this->dof->url_im('cstreams', '/icons/edit.png').'"</a>' ;
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может просматривать шаблоны
            $link .= ' <a href='.$this->dof->url_im('schedule','/view_week.php?ageid='.
                    $cstream->ageid.'&cstreamid='.$cstream->id.'&departmentid='.$depid).'>'.
                    '<img src="'.$this->dof->url_im('cstreams', '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').'">'.'</a>';
        }
        $calculatedsalfactor = $this->dof->storage('cstreams')->calculation_salfactor($cstream);
        $salaryhours = $cstream->hoursweek*$calculatedsalfactor;
        if ( $cstream->status == 'active' )
        {// если статус активный - выведем обычную надпись
            return array($programname, $itemname, $cstreamname, $cstream->hoursweek, 
                    (int) $cstream->hoursweekinternally, (int) $cstream->hoursweekdistance, 
                    $cstream->salfactor.'/'.$cstream->substsalfactor, $calculatedsalfactor,
                    $salaryhours, $link);
        }
        //выводим все серым цвеиом
        return array('<span class=gray_link>'.$programname.'</span>','<span class=gray_link>'.$itemname.'</span>',
                     '<span class=gray_link>'.$cstreamname.'</span>','<span class=gray>'.$cstream->hoursweek.'</span>',
                     '<span class=gray>'.(int) $cstream->hoursweekinternally.'</span>',
                     '<span class=gray>'.(int) $cstream->hoursweekdistance.'</span>',
                     '<span class=gray>'.$cstream->salfactor.'/'.$cstream->substsalfactor.'</span>', 
                     '<span class=gray>'.$calculatedsalfactor.'</span>',
                     '<span class=gray>'.$salaryhours.'</span>',
                     $link);
        
    }
    
    /** Возвращает таблицу с потоками
     * @param object $appointid - id табельного номера
     * @param int $hours - назначенные часы
     * @return string html-код таблиц
     */
    public function get_table_cstream($appointid,&$hours,&$salaryhours)
    {
        // ищем все потоки
        if ( $this->cstreamdepid )
        {// указано подразделение - выведем только для него
            $cstreams = $this->dof->storage('cstreams')->get_records(array('departmentid'=>$this->cstreamdepid,
                            'appointmentid'=>$appointid,'status'=>array('plan','active','suspend')), 'status ASC, name ASC');
        }else
        {// для всех подразделений
            $cstreams = $this->dof->storage('cstreams')->get_records(array('appointmentid'=>$appointid,
                            'status'=>array('plan','active','suspend')), 'status ASC, name ASC');
        }
        if ( ! $cstreams )
        {// потоков нет - возвращаем пустую строчку
            return '';
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "left";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
		//$table->size = array('200px','200px',null,'100px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head[] = $this->dof->get_string('programm', 'cstreams');
        $table->head[] = $this->dof->get_string('programmitem', 'cstreams');
        $table->head[] = $this->dof->get_string('name_cstream', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('hoursweek', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('hoursweekinternally', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('hoursweekdistance', 'cstreams', '<br>');
        $table->head[] = $this->dof->get_string('salcalcfactor', 'cstreams','<br>');
        $table->head[] = $this->dof->get_string('calcfactor', 'cstreams','<br>');
        $table->head[] = $this->dof->get_string('salaryhours', 'cstreams','<br>');
        $table->head[] = $this->dof->modlib('ig')->igs('actions');
        // заносим данные в таблицу     
        foreach ( $cstreams as $cstream )
        {// для каждого предмета формируем строчку и запоминаем кол-во часов
            $table->data[] = $this->get_string_info_cstream($cstream);
            
            if ( $cstream->status == 'active' )
            {// если статус активный, считаем нагрузку
                $hours += $cstream->hoursweek;

                $calculatedsalfactor = $this->dof->storage('cstreams')->calculation_salfactor($cstream);
                $salaryhours += $cstream->hoursweek * $calculatedsalfactor;
            }
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
        
    }
    
    /** Возвращает таблицы табельных номеров для персоны
     * @param array $person - массив персоны с табельными номерами
     * @param int $id - id персоны
     * @return string html-код таблиц
     */
    public function get_table_person($person,$id)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "left";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '100%';
        $table->size = array ('100%');
        $table->align = array ("center");
        // заносим данные в таблицу  
        $rez = '';   
        $fixhours = 0;
        $teachersalaryhours = 0;
        $tabelhours = 0;
        foreach ( $person as $appoint )
        {// для каждого табеля формируем строчку
            // рисуем таблицу
            $table1 = new stdClass();
            $table1->tablealign = "left";
            $table1->cellpadding = 5;
            $table1->cellspacing = 5;
            $table1->width = '100%';
            $table1->size = array ('100%');
            $table1->align = array ("center");
            $hours = 0;
            $appointmentsalaryhours = 0;
            if ( $cstreams_table = $this->get_table_cstream($appoint->id,$hours,$appointmentsalaryhours) )
            {// если потоки на тебельные номер есть - выведем тыблицу
                // шапка таблицы
                $name = $this->dof->get_string('eagreement', 'cstreams').':'.
                        $this->dof->storage('eagreements')->get_field($appoint->eagreementid,'num').
                        ' - '.$this->dof->get_string('appointment', 'cstreams').':'.$appoint->enumber.
                        ' - '.$this->dof->get_string('load', 'cstreams').': '.
                        $this->dof->get_string('tabel', 'cstreams').':'.round($appoint->worktime, 2).' / '.
                        $this->dof->get_string('fix', 'cstreams').':'.$hours.' - '.
                        $this->dof->get_string('salaryhours', 'cstreams').':'.$appointmentsalaryhours;
                $table1->head[] = $name;
                $fixhours += $hours;
                $teachersalaryhours += $appointmentsalaryhours;
                $rez .= $this->dof->modlib('widgets')->print_table($table1,true).$cstreams_table; 
            }else
            {// выведем сообщение что их нет
                // шапка таблицы
                $name = $this->dof->get_string('eagreement', 'cstreams').':'.
                        $this->dof->storage('eagreements')->get_field($appoint->eagreementid,'num').
                        ' - '.$this->dof->get_string('appointment', 'cstreams').':'.$appoint->enumber.
                        ' - '.$this->dof->get_string('load', 'cstreams').': '.
                        $this->dof->get_string('tabel', 'cstreams').':'.round($appoint->worktime, 2).' / '.
                        $this->dof->get_string('fix', 'cstreams').':0'.' - '.
                        $this->dof->get_string('salaryhours', 'cstreams').':0';
                $table1->head[] = $name;
                $table1->data[] = array($this->dof->get_string('no_cstream_for_appointment', 'cstreams'));
                $rez .= $this->dof->modlib('widgets')->print_table($table1,true); 
            }
            $tabelhours += $appoint->worktime;
           
        }
        // шапка таблицы
        $table->head[] = '<br>'.$this->dof->storage('persons')->get_fullname($id).' - '.
                         $this->dof->get_string('total_load', 'cstreams').': '.
                         $this->dof->get_string('tabel', 'cstreams').':'.round($tabelhours, 2).' / '.
                         $this->dof->get_string('fix', 'cstreams').':'.$fixhours.' - '.
                         $this->dof->get_string('salaryhours', 'cstreams').':'.$teachersalaryhours.'<br><br>';
        return $this->dof->modlib('widgets')->print_table($table,true).$rez;  
    }
    
    
    /** Возвращает количество шаблонов потока
     * @param $cstreamid - id потока
     * @return bool false|int
     */
    private function get_count_templates($cstreamid)
    {
        if ( ! $this->dof->plugin_exists('im', 'otech') )
        {// нет плагина, смысла искать нет
            return false;
        }
        $csobj = new otech_doffice_templesson_cstreams();
        // выводим количество записей в шаблоне
        if ( ! $templates = $csobj->get_filter_lessons_cstream(null,null,null,null,$cstreamid,null,'on') )
        {// если записей не нашли, то возвращаем 0
            return 0;
        }
        return count($templates);
    }

    /** Возвращает html-код справки
     * @return string 
     */
    public function get_help()
    {
        return '<b>'.$this->dof->get_string('help', 'cstreams').':</b><br>
               - '.$this->dof->get_string('help_choise', 'cstreams').'.<br>';
    }
}

?>