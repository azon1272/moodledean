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

class dof_im_cstreams_process_form_by_groups
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $programmid;
    private $ageid;
    private $agenum;
    private $groupdepid;
    private $cstreamdepid;
    private $programmitemslist;
    private $sbcstatus;
    
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof, $programmid, $ageid, $agenum, $groupdepid, $sbcstatus)
    {
        $this->dof = $dof;
        $this->programmid = $programmid;
        if ( $ageid )
        {// если период указан - возьмем его
            $this->ageid = $ageid;
        }else
        {// если нет
            //возьмем массивом все незавершенные и неотмененные
            if ( ! $ages = $this->dof->storage('ages')->get_records(array('status'=>
                           array('plan','createstreams','createsbc','createschedule','active'))) )
            {// если и их нет, то укажем как 0
                $this->ageid = 0;
            }else
            {// соберем массив
                $this->ageid = array();
                foreach ( $ages as $age )
                {
                    $this->ageid[] = $age->id;
                }
            }
        }
        $this->agenum = $agenum;
        $this->groupdepid = $groupdepid;
        $this->cstreamdepid = 0;
        $this->programmitemslist = $this->dof->storage('programmitems')->get_pitems_select_list($programmid, $agenum);
        $this->sbcstatus = $sbcstatus;
    }
    /** Возвращает таблицу с предметами для отображения на странице
     * @return string - html-код таблицы
     */
    public function get_programitems()
    {
        $table = '';
        // найдем предметы указанной параллели
        if ( $items = $this->get_items() )
        {// если они есть - выведем на экран
            $table .= $this->get_head_agenum($this->agenum);
            $table .= $this->get_table_programmitems($items);
        }
        // найдем предметы доступные во всех параллелях
        if ( $items = $this->dof->storage('programmitems')->get_pitems_list($this->programmid,0,array('active','suspend')) )
        {// если они есть - выведем на экран
            $table .= $this->get_head_agenum(0);
            $table .= $this->get_table_programmitems($items);
        }
        if ( empty($table) AND $this->programmid )
        {//если была выбрана программа, но предметов в ней не оказалось
            // сообщим обэтом
            $table = '<div align=\'center\'><b>'.$this->dof->get_string('no_item_for_agenum', 'cstreams').
                     '</b></div>';
        }
        return $table;
    }
    /** Возвращает большой заголовок таблицы
     * @param string $agenum - номер параллели
     * @return string - html-код таблицы
     */
    private function get_head_agenum($agenum)
    {
        // шапка таблицы
        if ( ! $agenum )
        {// если параллель 0-я
            // значит доступно во всех параллелях
            $heading = $this->dof->get_string('optional_pitems', 'cstreams');
        }else
        {// выведем название параллели
            $heading = $this->dof->get_string('agenum', 'cstreams').' '.$agenum;
        }
        return $this->dof->modlib('widgets')->print_heading($heading, '', 2, 'main', true);
    }
    /** Возвращает таблицу с предметами параллели
     * @param array $items - массив объектов-предметов
     * @return string - html-код таблицы
     */
    private function get_table_programmitems($items)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = array (null,'150px','150px');
        $table->align = array ("center","center","center");
        // шапка таблицы
        $table->head[] = $this->dof->get_string('name_item', 'cstreams');
        $table->head[] = $this->dof->get_string('type_item', 'cstreams');
        $table->head[] = $this->dof->get_string('actions', 'cstreams');  
        // заносим данные в таблицу     
        foreach ( $items as $item )
        {// для каждого предмета формируем строчку
            $table->data[] = $this->get_string_programmitems($item);
        }
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    /** Возвращает строку для отображения данных о предмете
     * @param object $item - объект записи из таблицы programmitems БД
     * @return array - массив для вставки в таблицу
     */
    private function get_string_programmitems($item)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        // имя предмета
        $itemname = '<a href="'.$this->dof->url_im('programmitems', '/view.php?pitemid='.$item->id,$addvars).'">'.
                    $item->name.' ['.$item->code.']</a>';
        // тип предмета
	    $type = $this->dof->storage('programmitems')->get_type_name($item->type);
	    // ссылка на создание предмето-потоков
        // создание предмето-потоков
        $actions = '<a href="'.$this->dof->url_im('cstreams', '/edit.php?programmitemid='.$item->id
                            .'&programmid='.$item->programmid,$addvars).'">';
        $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/create_cstreams.png').'"
        alt=  "'.$this->dof->get_string('create_cstream_for_programmiteam', 'cstreams').'" 
        title="'.$this->dof->get_string('create_cstream_for_programmiteam', 'cstreams').'" /></a>&nbsp;';
        // вернем полученные данные
	    return array($itemname,$type,$actions);
    }
    /** Возвращает таблицу с группами для отображения на странице
     * @return string - html-код таблицы
     */
    public function get_agroups()
    {
        $params = array();
        $params['programmid'] = $this->programmid;
        $params['agenum'] = $this->agenum;
        if ( ! empty($this->groupdepid) )
        {// если указано подразделение группы - выведем группы для указанного подразделения
            $params['departmentid'] = $this->groupdepid;
        }
        
        if ( ! $agroups = $this->dof->storage('agroups')->get_records($params) )
        {// нет таких групп
            return '';
        }
        
        $addvars['programmid'] = $this->programmid;
        $addvars['agenum'] = $this->agenum;
        $addvars['ageid'] = $this->ageid;
        $addvars['departmentid'] = $this->groupdepid;
        $table = '';
        foreach ( $agroups as $agroup )
        {// для каждой группы выведем таблицу
            $cstreamstable = $this->get_table_cstream($agroup->id,$this->ageid,'group');
            if ( $cstreamstable == '' )
            {// если таблица с потоками не найдена - выведем заголовок с сообщением
                $table .= $this->get_head_name($agroup->name.' ['.$agroup->code.']',$agroup->id,'group',true);
            }else
            {// иначе просто выведем заголовок
                $table .= $this->get_head_name($agroup->name.' ['.$agroup->code.']',$agroup->id,'group');
            }
            // выведем таблицу с потоками
            $table .= '<a name="ag'.$agroup->id.'"></a>';
            $table .= $cstreamstable;
            $table .= '<br>';
            $addvars['agroupid'] = $agroup->id;

            if ( ! in_array($agroup->status, array('plan','active','formed')) )
            {// группа не активна - привязывать объекты нельзя
                $table .= '';
            }elseif (  $this->ageid AND ! is_array($this->ageid) AND in_array($this->dof->storage('ages')->get_field($this->ageid, 'status'), array('plan','active')) 
                  AND $this->get_items() )
            {// стаус периода plan или active покажем кнопку привязать все обязательные предметы
                $table .= $this->get_gen_bind_for_html($addvars, true);
            }else 
            {
                $table .= $this->get_gen_bind_for_html($addvars, false);
            }
            
            $table .= '<br>'; 
        }
        return $table;
    }
    /** Возвращает таблицу с подписками для отображения на странице
     * @return string - html-код таблицы
     */
    public function get_programmsbcs()
    {
        $select  = array();
        $select['programmid'] = $this->programmid;
        $select['agenum'] = $this->agenum;
        $select['edutype'] = 'individual';
        if ( $this->sbcstatus == 'complete' )
        {
            $select['status'] = array('completed','failed');
        }else
        {
            $select['status'] = array_keys($this->dof->workflow('programmsbcs')->get_meta_list($this->sbcstatus));
        }
        if ( ! $this->groupdepid )
        {// если не указано подразделение потока
            // выведем подписки для всех подразделений
            $sbcs = $this->dof->storage('programmsbcs')->get_records_select
                    ($this->dof->storage('programmsbcs')->get_select_listing($select));
        }else
        {   // иначе только для указанного
            $select['departmentid'] = $this->groupdepid;
            $sbcs = $this->dof->storage('programmsbcs')->get_records_select
                    ($this->dof->storage('programmsbcs')->get_select_listing($select));
        }

        if ( ! $sbcs )
        {// нет таких подписок на программу
           return '';
        }
        $addvars['programmid'] = $this->programmid;
        $addvars['agenum'] = $this->agenum;
        $addvars['ageid'] = $this->ageid;
        $addvars['departmentid'] = $this->groupdepid;
        $sorttable = array();
        foreach ( $sbcs as $sbc )
        {// для каждой подписки выведем таблицу
            $cstreamstable = $this->get_table_cstream($sbc->id,$this->ageid);
            // сформируем имя
            $name = array();
            $studentid = $this->dof->storage('contracts')->get_field($sbc->contractid, 'studentid');
            if ( $personname = $this->dof->storage('persons')->get_fullname($studentid) )
            {// есть имя ученика, запомним
               $name[] = $personname;
            }
            if ( $contractnum = $this->dof->storage('contracts')->get_field($sbc->contractid, 'num') )
            {// есть номер контракта - запомним
               $name[] = $contractnum;
            }
            $name[] = $sbc->id;
            // формируем из них имя
            $name = implode('-',$name);
            if ( $cstreamstable == '' )
            {// если таблица с потоками не найдена - выведем заголовок с сообщением
                $table = $this->get_head_name($name,$sbc->id,null,true);
            }else
            {// иначе просто выведем заголовок
                $table = $this->get_head_name($name,$sbc->id);
            }
            // выведем таблицу с потоками
            $table .= '<a name="ps'.$sbc->id.'"></a>';
            $table .= $cstreamstable;
            $table .= '<br>';
            $addvars['sbcid'] = $sbc->id;
            if ( ! in_array($sbc->status, array('plan','active','application','condactive')) )
            {// подписка не активна - привязывать объекты нельзя
                $table .= '';
            }elseif ( $this->ageid AND ! is_array($this->ageid) AND in_array($this->dof->storage('ages')->get_field($this->ageid, 'status'), array('plan','active')) 
                AND $this->get_items() )
            {// стаус периода plan или active покажем кнопку привязать все обязательные предметы
                $table .= $this->get_gen_bind_for_html($addvars, true);
            }else
            {
                $table .= $this->get_gen_bind_for_html($addvars, false);
            }            
            $table .= '<br>'; 
            // занесем имя и таблицу в массив для сортировки
            $sorttable[$name] = $table; 
        }
        // сортируем поименно
        ksort($sorttable);
        // вернем все таблицы
        return implode('',$sorttable);
    }
    /**
     * Возвращает сформированную форму со всеми ссылками
     * @param array $addvars - массив передаваемых переменных
     * @param int $programmid - id программы по которому ищем предметы
     * @param int $agenum - параллель для которой ищем предметы
     * @param boolean $flag - указывает на все обязательные пронграммы 
     * @return string - html-строка
     */
    private function get_gen_bind_for_html($addvars, $flag=false)
    {
        if ( $flag )
        {
            return $this->dof->im('cstreams')->get_bind_form_html($this->dof->url_im('cstreams','/assign_cstream_student.php',$addvars),
                $this->programmitemslist, true);    
        }
        return $this->dof->im('cstreams')->get_bind_form_html($this->dof->url_im('cstreams','/assign_cstream_student.php',$addvars),
            $this->programmitemslist);
    }
    
    /** Возвращает список предмето-потоков для студента
     * @param int $id - группы или подписки на программу
     * @param int $ageid - id периода
     * @param string $type - тип строки
     * student - ученик
     * group - группа 
     * @return array - массив предмето-потоков
     */
    public function get_table_cstream($id, $ageid, $type='student')
    {
        switch ( $type )
        {
            // найдем все потоки для данной группы
            case 'group': $cstreamids = $this->get_cstream_for_agroup($id); break;
            // найдем все потоки для данного студента в данном периоде
            case 'student': $cstreamids = $this->get_cstream_for_student($id,$ageid); break;
            default: return '';
        }
        if ( ! $cstreamids )
        {// потоков нет - вернем пустую строчку
            return '';
        }
        $values = new stdClass();
        $values->id = $cstreamids;
        $values->ageid = $ageid;
        $values->departmentid = 0;//optional_param('departmentid', 0, PARAM_INT);
        $values->status = array('plan','active','suspend','completed');
        if ( $this->cstreamdepid )
        {// если департмент указан - выведем потоки только к указанному
            $values->departmentid = $this->cstreamdepid;
        }
        if ( ! $cstreams = $this->dof->storage('cstreams')->get_listing($values,null,null,'c.status ASC, pi.name ASC') )
        {// потоков нет - вернем пустую строчку
            return '';
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '90%';
        $table->size = array ('300px','150px','150px','200px','100px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head[] = $this->dof->get_string('name_cstream', 'cstreams');
        $table->head[] = $this->dof->get_string('teacher', 'cstreams');
        $table->head[] = $this->dof->get_string('programmitem', 'cstreams');
        $table->head[] = $this->dof->get_string('hoursweek', 'cstreams');
        $table->head[] = $this->dof->get_string('hoursweekinternally', 'cstreams');
        $table->head[] = $this->dof->get_string('hoursweekdistance', 'cstreams');                
        if ( $type == 'group' )
        {// для группы укажем тип синхронизации
            $table->head[] = $this->dof->get_string('typesync', 'cstreams');
        }
        $table->head[] = $this->dof->get_string('salcalcfactor', 'cstreams','<br>');
        $table->head[] = $this->dof->get_string('calcfactor', 'cstreams','<br>');
        $table->head[] = $this->dof->get_string('status', 'cstreams');
        $table->head[] = $this->dof->get_string('actions', 'cstreams'); 
        $hours = new stdClass;
        $hours->weeks = 0;
        $hours->internally = 0;
        $hours->distance = 0;
        // заносим данные в таблицу   
        foreach ( $cstreams as $cstream )
        {// для каждого предмета формируем строчку
            if ( $cstream->status == 'canceled' )
            {// отмененные пока не показываем
                continue;
            }
            $table->data[] = $this->get_string_info_cstream($id, $cstream, $type);
            $hours->weeks += $cstream->hoursweek;
            $hours->internally += $cstream->hoursweekinternally;
            $hours->distance +=$cstream->hoursweekdistance;
        }
        // выведем дополнительную строку
        $table->data[] = $this->get_string_all_hoursweek($hours,$type);
        return $this->dof->modlib('widgets')->print_table($table,true);
        
    }
    /** Возвращает большой заголовок таблицы для группы или ученика
     * @param string $name - имя заголовка
     * @param int $id - группы или подписки на программу
     * @param string $type - тип строки
     * student - ученик
     * group - группа 
     * @param bool $message - выводить ли сообщение 
     * @return string - html-код таблицы
     */
    private function get_head_name($name,$id,$type = 'student', $message = false)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        
        // шапка таблицы
        if ( $type == 'group' )
        {// указанно выводить для группы
            $heading = '<a href="'.$this->dof->url_im('agroups', '/view.php?agroupid='.$id,$addvars).'">'.
                             $this->dof->get_string('agroup', 'cstreams').' '.$name.'</a>';
        }elseif ( $type == 'cstream' )
        {// выводим для студента
            $heading = $this->dof->get_string('empty_cstreams', 'cstreams');
        }else
        {// выводим для студента
            $heading = '<a href="'.$this->dof->url_im('programmsbcs', 
                             '/view.php?programmsbcid='.$id,$addvars).'">'.$name.'</a>';
        }
        if ( $message )
        {//выводим сообщение если нужно
            if ( $type == 'group' )
            {// для группы
                $message = '<p align="center">'.$this->dof->get_string('not_found_cstreams_for_agroups', 'cstreams').'</p>';
            }else
            {// для ученика
                $message = '<p align="center">'.$this->dof->get_string('not_found_cstreams_for_student', 'cstreams').'</p>';
            }
        }
        return $this->dof->modlib('widgets')->print_heading($heading, '', 2, 'main', true).$message;
    }
    /** Возвращает строку для отображения данных о группе или учащимся
     * @param int $id - группы или подписки на программу
     * @param object $cstream - объект записи из таблицы cstreams БД
     * @param string $type - тип строки
     * student - ученик
     * group - группа 
     * @return array - массив для вставки в таблицу
     */
    private function get_string_info_cstream($id, $cstream, $type = 'student')
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $cstreamname = '<a href="'.$this->dof->url_im('cstreams', '/view.php?cstreamid='.$cstream->id,$addvars).'">'.
                       $this->dof->storage('cstreams')->change_name_cstream($cstream).'</a>';
        // имя преподавателя
        $teachername = '<a href="'.$this->dof->url_im('persons', '/view.php?id='.$cstream->teacherid,$addvars).'">'.
                    $this->dof->storage('persons')->get_fullname($cstream->teacherid).'</a>';
        // имя предмета
        $itemname = '<a href="'.$this->dof->url_im('programmitems', '/view.php?pitemid='.
                    $cstream->programmitemid,$addvars).'">'.
                    $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'name').' <br>['.
                    $this->dof->storage('programmitems')->get_field($cstream->programmitemid,'code').']';
        // синхронизация группы
        if ( $type == 'group' )
        {// только для группы разумеется
            if ( ! $agroupsync = $this->dof->storage('cstreamlinks')->get_type_cstreamlink($id,$cstream->id) )
            {// если не нашли тип связи - значит не синхронизирован
                $agroupsync = $this->dof->get_string('no_agroup_sync', 'cstreams');
            }
        }
        // статус
        if ( ! $statusname = $this->dof->workflow('cstreams')->get_name($cstream->status) )
        {//статуса нет - выведем пустую строчку
            $statusname = '';
        }
        
        // иконки
        $actions = '';
        // для группы
        $actions .= '<a href="'.$this->dof->url_im('cstreams', '/linkagroup.php?cstreamid='.$cstream->id,$addvars).'">';
        $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/add_link.png').'"
        alt=  "'.$this->dof->get_string('list_group', 'cstreams').'" 
        title="'.$this->dof->get_string('list_group', 'cstreams').'" /></a>&nbsp;';
        // для студента
        $actions .= '<a href="'.$this->dof->url_im('cpassed', '/list.php?cstreamid='.$cstream->id,$addvars).'">';
        $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/students.png').'"
        alt=  "'.$this->dof->get_string('cpassed', 'cstreams').'" 
        title="'.$this->dof->get_string('cpassed', 'cstreams').'" /></a>&nbsp;';
        // на школьный журнал
        $actions .= '<a href="'.$this->dof->url_im('journal','/group_journal/index.php?csid='.$cstream->id,$addvars).'">';
        $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/journal.png').'"
        alt=  "'.$this->dof->get_string('journal', 'cstreams').'" 
        title="'.$this->dof->get_string('journal', 'cstreams').'" /></a>&nbsp;';
        if ( $this->dof->im('plans')->is_access('viewthemeplan',$cstream->id) OR 
             $this->dof->im('plans')->is_access('viewthemeplan/my',$cstream->id) )
        {// если есть право на просмотр планирования
            $actions .= '<a id="view_planning_for_cstream_'.$cstream->id.'" href="'.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=cstreams&linkid='.$cstream->id,$addvars).'">';
            $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/plancstream.png').'"
                alt=  "'.$this->dof->get_string('view_plancstream', 'cstreams').'" 
                title="'.$this->dof->get_string('view_plancstream', 'cstreams').'" /></a>&nbsp;';
            $actions .= '<a id="view_iutp_for_cstream_'.$cstream->id.'" href="'.$this->dof->url_im('plans','/themeplan/viewthemeplan.php?linktype=plan&linkid='.$cstream->id,$addvars).'">';
            $actions .= '<img src="'.$this->dof->url_im('cstreams', '/icons/iutp.png').'"
                alt=  "'.$this->dof->get_string('view_iutp', 'cstreams').'" 
                title="'.$this->dof->get_string('view_iutp', 'cstreams').'" /></a>&nbsp;';
        }
        if ( $this->dof->storage('schtemplates')->is_access('view') )
        {// пользователь может просматривать шаблоны
            $actions .= ' <a id="view_schedule_for_cstream_'.$cstream->id.'" href='.$this->dof->url_im('schedule','/view_week.php?ageid='.
                    $cstream->ageid.'&cstreamid='.$cstream->id,$addvars).'>'.
                    '<img src="'.$this->dof->url_im('cstreams', '/icons/view_schedule.png').
                    '"alt="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').
                    '" title="'.$this->dof->get_string('view_week_template_on_cstream', 'cstreams').'">'.'</a>';
        }
        if ( $this->dof->storage('schtemplates')->is_access('create') )
        {// пользователь может редактировать шаблон
            $actions .= ' <a id="create_schedule_for_cstream_'.$cstream->id.'" href='.$this->dof->url_im('schedule','/edit.php?ageid='.
                    $cstream->ageid.'&cstreamid='.$cstream->id,$addvars).'>'.
                    '<img src="'.$this->dof->url_im('cstreams', '/icons/create_schedule.png').
                    '"alt="'.$this->dof->get_string('create_template_on_cstream', 'cstreams').
                    '" title="'.$this->dof->get_string('create_template_on_cstream', 'cstreams').'">'.'</a>';
        }
        // вернем полученные данные
        if ( $type == 'group' )
        {// для группы вернем с типом связи
	        return array($cstreamname, $teachername, $itemname, $cstream->hoursweek, $this->dof->storage('cstreams')->hours_int($cstream->hoursweekinternally), 
	                $this->dof->storage('cstreams')->hours_int($cstream->hoursweekdistance),$agroupsync,
	                $cstream->salfactor.'/'.$cstream->substsalfactor,
	                $this->dof->storage('cstreams')->calculation_salfactor($cstream),
	                $statusname,$actions);
        }else
        {// для студента - без
            return array($cstreamname, $teachername, $itemname, $cstream->hoursweek, $this->dof->storage('cstreams')->hours_int($cstream->hoursweekinternally), 
            $this->dof->storage('cstreams')->hours_int($cstream->hoursweekdistance), 
            $cstream->salfactor.'/'.$cstream->substsalfactor, 
            $this->dof->storage('cstreams')->calculation_salfactor($cstream),
            $statusname, $actions);
        }
    }
    
    /** Возвращает список предмето-потоков для студента
     * @param int $programmsbcid - id подписки на программу студента
     * @param int $ageid - id периода
     * @return array - список id предмето-потоков или bool false
     */
    private function get_cstream_for_student($programmsbcid, $ageid)
    {
        // найдем все подписки на дисциплину для данного студента в данном периоде
        $conds = new stdClass();
        $conds->programmsbcid = $programmsbcid;
        $conds->ageid = $ageid;
        //$conds->noagroupid = (int) true;
        // показываем все подписки
        $conds->departmentid = 0;//optional_param('departmentid', 0, PARAM_INT);
        $conds->status = array('plan','active','suspend','completed','failed');
        if ( ! $cpassed = $this->dof->storage('cpassed')->
                      get_listing($conds) )
        {// нету таких - потоков у студента тоже нет
            return false;
        }
        // запомним id всех потоков
        $cstreamids = array();
        foreach ( $cpassed as $cpass )
        {
            $cstreamids[] = $cpass->cstreamid;
        }
        return $cstreamids;
    }
    /** Возвращает список id предмето-потоков
     * @param int $agroupid - id группы
     * @return array - список id предмето-потоков или bool false
     */
    private function get_cstream_for_agroup($agroupid)
    {
        // найдем все связи этой группы с потоками
        if ( ! $links = $this->dof->storage('cstreamlinks')->
                      get_records(array('agroupid'=>$agroupid)) )
        {// нету таких - потоков у групп тоже нет
            return false;
        }
        // запомним id всех потоков
        $cstreamids = array();
        foreach ( $links as $link )
        {
            $cstreamids[] = $link->cstreamid;
        }
        return $cstreamids;
    }
    /** Возвращает строку отображения всего кол-ва часов в неделю
     * @param int $num - кол-во часов в неделю
     * @param string $type - тип строки
     * @return unknown_type
     */
    private function get_string_all_hoursweek($num,$type='student')
    {
        if ( $type == 'group' )
        {// если тип группа, выведем на одну колонку больше
            return array('<b>'.$this->dof->get_string('all_hoursweek', 'cstreams').'</b>',
                                   '','','<b>'.$num->weeks.'</b>','<b>'.$num->internally.'</b>'
                                   ,'<b>'.$num->distance.'</b>','','','');
        }
        // вернем строку
        return array('<b>'.$this->dof->get_string('all_hoursweek', 'cstreams').'</b>',
                                   '','','<b>'.$num->weeks.'</b>','<b>'.$num->internally.'</b>'
                                   ,'<b>'.$num->distance.'</b>','','');
    }
    /** Возвращает таблицу для пустых потоков
     * @return unknown_type
     */
    public function get_cstreams()
    {
        if ( ! $cstreams = $this->get_empty_cstreams() )
        {// пустых потоков нет
            return '';
        }
        // выведем большой заголовок
        $table1 = $this->get_head_name(null,null,'cstream');

        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->width = '90%';
        $table->size = array ('300px','150px','150px','200px','100px','100px');
        $table->align = array ("center","center","center","center","center","center");
        // шапка таблицы
        $table->head[] = $this->dof->get_string('name_cstream', 'cstreams');
        $table->head[] = $this->dof->get_string('teacher', 'cstreams');
        $table->head[] = $this->dof->get_string('programmitem', 'cstreams');
        $table->head[] = $this->dof->get_string('hoursweek', 'cstreams');
        $table->head[] = $this->dof->get_string('hoursweekinternally', 'cstreams');
        $table->head[] = $this->dof->get_string('hoursweekdistance', 'cstreams');         
        $table->head[] = $this->dof->get_string('status', 'cstreams');
        $table->head[] = $this->dof->get_string('actions', 'cstreams'); 
        // заносим данные в таблицу   
        foreach ( $cstreams as $cstream )
        {// для каждого потока формируем строчку
            if ( $cstream->status == 'canceled' )
            {// отмененные пока не показываем
                continue;
            }
            $table->data[] = $this->get_string_info_cstream(null, $cstream);
        }
        return $table1.$this->dof->modlib('widgets')->print_table($table,true);
    }
    /** Получает список пустых потоков
     * @return unknown_type
     */
    private function get_empty_cstreams()
    {
        // сам SQL- запрос в справочнике
        $cstreams = $this->dof->storage('cstreams')->get_empty_cstreams_full($this->programmid,$this->agenum,$this->cstreamdepid,$this->ageid);     
        return $cstreams;
    }
    /** Получить список обязательных предметов в виде массива id
     * @return миссив объектов items или false
     */
    public function get_items()
    {
        $items = $this->dof->storage('programmitems')->get_pitems_list($this->programmid,$this->agenum,array('active','suspend'));
        return $items;    
    }    
     
    
}

?>