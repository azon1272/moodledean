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
 * Библиотека, для класса приказа и отображения его
 */ 
//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../../lib.php");

/**
 * Класс для формирования приказа смены преподавателя
 */
class dof_im_cstreams_teacher 
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var dof_storage_cstreams_order_change_teacher
     */
    public $order;
    
    /** Конструктор, создающий новый приказ, либо берущий старый по $orderid
     * 
     * @param object $dof - объект ЭД
     * @param int $orderid - номер приказа, который должен существовать в системе
     * @return object - объект приказа
     */
    public function __construct($dof, $orderid = null)
    {
        global $addvars;
        $this->dof = $dof;
        if ( is_null($orderid) )
        {
            $order = $this->dof->im('cstreams')->order('change_teacher');
            // Сохраняем новый приказ
            $orderobj = new stdClass();
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// Если id персоны не найден
                $this->order = false;
                $errorlink = $DOF->url_im('cstreams','/orderctload/index.php',$addvars);
                $this->dof->print_error('error_person', $errorlink, null, 'im', 'cstreams');
                return;
            }
            // Сохраняем автора приказа
            $orderobj->ownerid = $personid;
            
            if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {// Установим выбранное на странице id подразделения 
                $orderobj->departmentid = $addvars['departmentid'];
            }else
            {// Установим id подразделения из сведений о том кто формирует приказ
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }
            
            // Дата создания приказа
            $orderobj->date = time();
            // Добавляем данные, о которых приказ
            $orderobj->data = new stdClass();
            if( isset($addvars['departmentid']) AND $addvars['departmentid'] )
            {
                $orderobj->data->departments = array($addvars['departmentid']);
            }
            // Сохраняем приказ в БД и привязываем экземпляр приказа к id
            $order->save($orderobj);
        }else
        {
            $order = $this->dof->im('cstreams')->order('change_teacher',$orderid);
        }
        $this->order = $order;
    }
    
    /** Возвращает id приказа, над которым производятся действия
     * 
     * @return int - id приказа из таблицы orders
     */
    public function get_id()
    {
        return $this->order->get_id();
    }
    
    /** Возвращает appointmentid учителя, имеющего минимальную нагрузку из всех
     * учителей, способных взять эту дисциплину ($pitid), либо false в случае ошибки
     * 
     * @param int $pitid - id дисциплины
     * @return mixed bool|int - appointmentid из таблицы appointments или false в случае ошибки
     */
    public function get_minimal_load_teacher($pitid)
    {
        $teachers = $this->get_possible_teachers($pitid);
        if ( empty($teachers) )
        {
            return false;
        }
        $minload  = 0;
        $minappid = 0;
        $first = true; // Первый проход
        foreach ( $teachers as $teacher )
        {// Перебираем нагрузку всех учителей
            $load = $this->dof->storage('appointments')->get_appointment_load($teacher->appointmentid);
            if ( !is_bool($load) AND $load < $minload OR $first )
            {// Если нагрузка имеется и она меньше текущей, либо это первое присваивание
                $minload  = $load;
                $minappid = $teacher->appointmentid;
                $first = false;
            }
        }
        if ( empty($minappid) )
        {// Так и не нашли минимальную нагрузку
            return false;
        }
        return $minappid;
    }
    
    /** Извлекает данные текущего приказа, либо по переданному $orderid
     * 
     * @param int $orderid - id из таблицы orders
     * @return mixed bool|object - объект приказа или false в случае неудачи
     */
    public function get_order_data($orderid = null)
    {
        if ( $this->order === false )
        {// Объект приказа не создан
            return false;
        }
        if ( is_numeric($orderid) )
        {// Загружаем приказ по переданному $orderid
            return $this->order->load($orderid);
        }
        if ( ! $thisid = $this->order->get_id() )
        {
            dof_debugging('something goes wrong. $thisid:'. $thisid, DEBUG_DEVELOPER);
        }
        return $this->order->load($thisid);
    }
    
    /** Метод извлекает данные существующего приказа и исправляет его поля для
     * того, чтобы исполнить приказ обратного действия
     * 
     * @param object $formdata - данные из "первой" формы
     * @return object - объект приказа
     */
    private function get_order_returnhours($formdata)
    {
        // Запоминаем id
        $thisid = $this->get_id();
        $orderdataold = $this->get_order_data($formdata->order['id']);
        $orderdata = $this->get_order_data($thisid);
        // Скопируем данные из старого приказа
        foreach ( $orderdataold->data as $name => $value )
        {
            if ( stripos($name, '_') === 0 )
            {
                unset($orderdataold->data->$name);
            } else
            {
                $orderdata->data->$name = $orderdataold->data->$name;
            }
        }
        // Исправляем направление существующего приказа, чтобы отменить действие
        // Поскольку приказ отменяет своё действие, нужно вернуть все часы преподавателю
        
        // Текущий статус
        $orderdata->data->status = $this->dof->storage('appointments')->
                                    get_field(array('id'=>$orderdata->data->appointmentid), 'status');
        $orderdata->data->direction = $formdata->direction;
        $orderdata->data->reason = $formdata->reason;
        if ( isset($formdata->changestatus) )
        {
            $orderdata->data->changestatus = $formdata->changestatus;
        } else
        {
            $orderdata->data->changestatus = 0;
        }
        return $orderdata;
    }

    /** Возвращает список потоков на передачу по выбранному преподавателю (группировка по программам)
     * 
     * @param int $appid - id в таблице appointmentis
     * @return mixed bool|array - массив объектов (программ), в которых находятся потоки:
                                  $groups[$programmid] = new stdClass();
                                  $groups[$programmid]->name = $programmname;
                                  $groups[$programmid]->cstreams = array();
     *                            либо false в случае, если потоков нет или произошла ошибка
     */
    private function get_cstreams_group_program_transfer($appid)
    {
        if ( !is_int_string($appid) )
        {
            return false;
        }
        // Все поля нам не нужны, только те, которые могут пригодиться при генерации шаблона
        $fields = 'id,ageid,programmitemid,teacherid,departmentid,appointmentid,status,hours,name';
        $cstreams = $this->dof->storage('cstreams')->get_appointment_cstreams($appid, 'active', $fields);
        if ( empty($cstreams) )
        {
            return false;
        }
        $groups = array();
        foreach ( $cstreams as $cstream )
        { // Сформируем по группам
            // Узнаем, к какой программе принадлежит дисциплина, и семестр:
            $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'programmid');
            $pitemname = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'name');
            $agenum = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'agenum');
            if ( !isset($groups[$programmid]) )
            { // Группируем по программам
                $programmname = $this->dof->storage('programms')->get_field($programmid, 'name');
                $groups[$programmid] = new stdClass();
                $groups[$programmid]->name = $programmname;
                $groups[$programmid]->cstreams = array();
            }
            // Пока не знаем, какому учителю будет передавать нагрузку, поэтому по нулям
            $cstream->teacherid = 0;
            $cstream->fullname = '';
            $cstream->enumber = null;
            $cstream->appointmentid = 0;
            $cstream->agenum = $agenum;
            $cstream->pitemname = $pitemname;
            $groups[$programmid]->cstreams[$cstream->id] = $cstream;
        }
        return $groups;
    }
    
    /** Возвращает список потоков, сгруппированных по учителям, нагрузку которых можно
     * передать учителю
     * 
     * @param int $appid - id в таблице appointmentis
     * @return mixed bool|array - массив объектов (должностных назначений), в которых находятся потоки:
                                  $grouped[$cstream->appointmentid] = new stdClass();
                                  $grouped[$cstream->appointmentid]->name = $fullname;
                                  $grouped[$cstream->appointmentid]->teacherid = $person->id;
                                  $grouped[$cstream->appointmentid]->enumber = $person->enumber;
                                  $grouped[$cstream->appointmentid]->cstreams = array();
     *                            либо false в случае, если потоков нет или произошла ошибка
     */
    private function get_cstreams_group_program_take($appid)
    {
        // Все поля нам не нужны, только те, которые могут пригодиться при генерации шаблона
        $fields = 'id,ageid,programmitemid,teacherid,departmentid,appointmentid,status,hours,name';
        $cstreams = $this->dof->storage('cstreams')->get_appointment_take_cstreams($appid, 'active', $fields);
        // Сгруппируем по преподавателям все потоки, которые может взять наш преподаватель
        $grouped = array();
        foreach ( $cstreams as $id => $cstream )
        {// проходим по всем потокам, группируем их и добавляем дополнительную информацию
            if ( !isset($grouped[$cstream->appointmentid]) )
            { // Группируем по табельным номерам
                $person = $this->dof->storage('appointments')->get_person_by_appointment($cstream->appointmentid, true);
                $fullname = $this->dof->storage('persons')->get_fullname($person);
                $grouped[$cstream->appointmentid] = new stdClass();
                $grouped[$cstream->appointmentid]->name = $fullname;
                $grouped[$cstream->appointmentid]->teacherid = $person->id;
                $grouped[$cstream->appointmentid]->enumber = $person->enumber;
                $grouped[$cstream->appointmentid]->cstreams = array();
            }
            // Записываем дополнительную информацию в потоки для последующей фильтрации
            $agenum = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'agenum');
            $programmid = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'programmid');
            $programmname = $this->dof->storage('programms')->get_field($programmid, 'name');
            $pitemname = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'name');
            $cstream->programmid = $programmid;
            $cstream->programmname = $programmname;
            $cstream->pitemname = $pitemname;
            $cstream->agenum = $agenum;
            $cstream->checked = 0; // Показывает, отметили ли мы галочкой поток
            $grouped[$cstream->appointmentid]->cstreams[$id] = $cstream;
        }
        return $grouped;
    }
    
    /** Выводит список доступных учителей для дисциплины в формате:
     *  array (appointmentid => "teachername [enumber]", ... )
     * 
     * @param int $pitemid - programmitemid из таблицы cstreams
     * @return array - список доступных учителей
     */
    public function get_possible_teachers($pitemid)
    {
        // Получаем всех учителей
        // с проверкой доступа к чтению дисциплины, активных табельных номеров
        $teachers = $this->dof->storage('teachers')->get_teachers_for_pitem($pitemid);
        $appointmentids = array();
        foreach ( $teachers as $teacher )
        {
            $appointmentids[] = $teacher->appointmentid;
        }
        $persons = array();
        if ( !empty($appointmentids) )
        {// получаем список пользователей по списку учителей
            foreach ($appointmentids as $appid)
            {
                $persons[] = $this->dof->storage('appointments')->get_person_by_appointment($appid, true);
            }
            
            // Смотрим, есть ли у них активные табельные номера
            $statuses = $this->dof->workflow('appointments')->get_meta_list('active');
            foreach ($persons as $id => $person)
            { // Перебираем статусы табельных номеров
                $appstatus = $this->dof->storage('appointments')->get_field(
                        array('enumber'=>$person->enumber),'status');
                if ( !array_key_exists($appstatus, $statuses) )
                { // Если табельный номер не активен
                    unset($persons[$id]);
                }
            }
        }
        return $persons;
    }
    
    public function is_signed()
    {
        return $this->order->is_signed();
    }
       
    public function is_executed()
    {
        return $this->order->is_executed();
    }
       
    /** Вывести шаблон change_teacher на экран
     * 
     */
    public function print_texttable()
    {// Получаем массив со структурой документов
        global $addvars;
        $orderdata = $this->get_order_data();
        $change_teacher = new stdClass;
        $change_teacher->orderid        = $orderdata->id;
        $change_teacher->orderdesc      = $this->dof->get_string('order_desc', 'cstreams');
        $change_teacher->hdirection     = $this->dof->get_string('direction', 'cstreams');
        $change_teacher->direction      = $this->dof->get_string($orderdata->data->direction, 'cstreams');
        $change_teacher->hchangestatus  = $this->dof->get_string('change_status', 'cstreams');
        $change_teacher->hcurrentstatus = $this->dof->get_string('currentstatus', 'cstreams');
        if ( $orderdata->data->changestatus )
        {
            $status = $this->dof->modlib('ig')->igs('yes');
        } else
        {
            $status = $this->dof->modlib('ig')->igs('no');
        }
        $change_teacher->changestatus  = $status;
        $change_teacher->currentstatus = $this->dof->workflow('appointments')->get_name($orderdata->data->status);
        $change_teacher->hreason       = $this->dof->get_string('reason', 'cstreams');
        $change_teacher->reason        = $this->dof->get_string($orderdata->data->reason, 'cstreams');
        $change_teacher->hfullname     = $this->dof->get_string('fullname', 'cstreams');
        
        $fullnamelink = '<a href="'.$this->dof->url_im('persons','/view.php?id='.$orderdata->data->teacherid, $addvars).'">'
                                   .$orderdata->data->fullname . " [{$orderdata->data->enumber}]</a>";
        $appointmenticonlink = ' <a href="'.$this->dof->url_im('employees','/view_appointment.php?id='.$orderdata->data->appointmentid, $addvars)
            .'" title="'. $this->dof->get_string('appointment','cstreams')
            .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" ></a>';
        $change_teacher->tfullname     = $fullnamelink . $appointmenticonlink;
        if ( $orderdata->data->direction != 'toteacher')
        {
            $change_teacher->byorder = $this->dof->get_string('by_programms', 'cstreams');
        } else
        {
            $change_teacher->byorder = $this->dof->get_string('by_appointments', 'cstreams');
        }
        
        if ( $orderdata->data->direction != 'toteacher' )
        {
            $programms =& $orderdata->data->cstreams;
            $change_teacher->cstreams = array();
            foreach ( $programms as $programmid => $object )
            { // Формат такой:
                // $programms[$programmid] = new stdClass();
                // $programms[$programmid]->name = $programmname;
                // $programms[$programmid]->cstreams = array();
                $change_teacher->cstreams[$programmid] = new stdClass();
                $change_teacher->cstreams[$programmid]->name = $object->name;
                $pitemsgroup = array(); // Сформируем группу по 
                foreach ( $object->cstreams as $cstream )
                { // Сгруппируем потоки по дисциплинам
                    if ( $cstream->appointmentid == 0)
                    {
                        continue;
                    }
                    if ( !isset($pitemsgroup[$cstream->programmitemid]) )
                    {
                        $pitemsgroup[$cstream->programmitemid] = array();
                    }
                    $pitemsgroup[$cstream->programmitemid][] = $cstream;
                }
                $change_teacher->cstreams[$programmid]->disciplines = array();
                foreach ( $pitemsgroup as $pitemid => $cstreams )
                {
                    $change_teacher->cstreams[$programmid]->disciplines[$pitemid] = new stdClass();
                    $change_teacher->cstreams[$programmid]->disciplines[$pitemid]->dcstreams = array();
                    foreach ( $cstreams as $cstream )
                    {
                        $change_teacher->cstreams[$programmid]->disciplines[$pitemid]->discipline = $cstream->pitemname;
                        $change_teacher->cstreams[$programmid]->disciplines[$pitemid]->dcstreams[$cstream->id] = new stdClass();
                        // Ссылка на поток
                        $depid = $orderdata->departmentid;
                        $link = $this->dof->url_im('cstreams', '/view.php', array('cstreamid'=>$cstream->id, 'departmentid'=>$depid));
                        $cstreamlink = '<a href="'.$link.'">'.$cstream->name.'</a>';
                        $change_teacher->cstreams[$programmid]->disciplines[$pitemid]->dcstreams[$cstream->id]->cstream = $cstreamlink;
                        // Ссылка на преподавателя и его табельный номер
                        $fullnamelink = '<a href="'.$this->dof->url_im('persons','/view.php?id='.$cstream->teacherid, $addvars).'">'
                                                   .$cstream->fullname . " [{$cstream->enumber}]</a>";
                        $appointmenticonlink = '<a href="'.$this->dof->url_im('employees','/view_appointment.php?id='.$cstream->appointmentid, $addvars)
                            .'" title="'. $this->dof->get_string('appointment','cstreams')
                            .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" ></a>';
                        $change_teacher->cstreams[$programmid]->disciplines[$pitemid]->dcstreams[$cstream->id]->fullname = $fullnamelink;
                        $change_teacher->cstreams[$programmid]->disciplines[$pitemid]->dcstreams[$cstream->id]->appointment = $appointmenticonlink;
                    }
                }

            }
        } else if ( $orderdata->data->direction == 'toteacher' )
        {
            $appoinments =& $orderdata->data->cstreams;
            $change_teacher->cstreams = array();
            foreach ( $appoinments as $appid => $object )
            { // Формат такой:
                // $programms[$appid] = new stdClass();
                // $programms[$appid]->name = $programmname;
                // $programms[$appid]->cstreams = array();
                $change_teacher->cstreams[$appid] = new stdClass();
                $fullnamelink = '<a href="'.$this->dof->url_im('persons','/view.php?id='.$object->teacherid, $addvars).'">'
                                           .$object->name . " [{$object->enumber}]</a>";
                $appointmenticonlink = '<a href="'.$this->dof->url_im('employees','/view_appointment.php?id='.$appid, $addvars)
                    .'" title="'. $this->dof->get_string('appointment','cstreams')
                    .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" ></a>';
                $change_teacher->cstreams[$appid]->name = $fullnamelink . $appointmenticonlink;
                $pitemsgroup = array(); // Сформируем группу по 
                foreach ( $object->cstreams as $cstream )
                { // Сгруппируем потоки по дисциплинам
                    if ( $cstream->checked == 0 )
                    {
                        continue;
                    }
                    if ( !isset($pitemsgroup[$cstream->programmitemid]) )
                    {
                        $pitemsgroup[$cstream->programmitemid] = array();
                    }
                    $pitemsgroup[$cstream->programmitemid][] = $cstream;
                }
                $change_teacher->cstreams[$appid]->disciplines = array();
                foreach ( $pitemsgroup as $pitemid => $cstreams )
                {
                    $change_teacher->cstreams[$appid]->disciplines[$pitemid] = new stdClass();
                    $change_teacher->cstreams[$appid]->disciplines[$pitemid]->dcstreams = array();
                    foreach ( $cstreams as $cstream )
                    {
                        $change_teacher->cstreams[$appid]->disciplines[$pitemid]->discipline = $cstream->pitemname;
                        $change_teacher->cstreams[$appid]->disciplines[$pitemid]->dcstreams[$cstream->id] = new stdClass();
                        // Ссылка на поток
                        $depid = $orderdata->departmentid;
                        $link = $this->dof->url_im('cstreams', '/view.php', array('cstreamid'=>$cstream->id, 'departmentid'=>$depid));
                        $cstreamlink = '<a href="'.$link.'">'.$cstream->name.'</a>';
                        $change_teacher->cstreams[$appid]->disciplines[$pitemid]->dcstreams[$cstream->id]->cstream = $cstreamlink;
                        // Ссылка на преподавателя и его табельный номер
//                        $fullnamelink = '<a href="'.$this->dof->url_im('persons','/view.php?id='.$cstream->teacherid, $addvars).'">'
//                                                   .$cstream->fullname . " [{$cstream->enumber}]</a>";
//                        $appointmenticonlink = '<a href="'.$this->dof->url_im('employees','/view_appointment.php?id='.$cstream->appointmentid, $addvars)
//                            .'" title="'. $this->dof->get_string('appointment','cstreams')
//                            .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" ></a>';
//                        $change_teacher->cstreams[$appid]->disciplines[$pitemid]->dcstreams[$cstream->id]->fullname = $fullnamelink;
//                        $change_teacher->cstreams[$appid]->disciplines[$pitemid]->dcstreams[$cstream->id]->appointment = $appointmenticonlink;
                    }
                }
                if ( empty($change_teacher->cstreams[$appid]->disciplines) )
                {
                    unset($change_teacher->cstreams[$appid]);
                }

            }
        } // if
        
        $templater_package = $this->dof->modlib('templater')->template('im', 'cstreams', $change_teacher, 'change_teacher');
        
        return print($templater_package->get_file('html'));
    }

    /** Обрабатывает пришедшие из второй формы данные: фильтрует потоки по выбранным критериям и
     * в результате на третьем экране показываются преподаватели, которые уже выбраны
     * 
     * @param object $formdata - данные из "второй" формы
     * @return bool - результат операции
     */
    public function process_form_second($formdata)
    {
        if ( !is_object($formdata) )
        {
            return false;
        }
        // Период и семестр
        $ageid     = $formdata->ageid;
        $agenum    = $formdata->agenum;
        if ( !empty($formdata->programmitems) )
        {
            $programmitems = $formdata->programmitems;
        } else 
        {
            $programmitems = null;
        }
        // Фильтруем потоки по периоду, семестру и отмеченным дисциплинам с выбранным учителем
        if ( $this->filter_cstreams_ageid_agenum_pitems_appids($ageid, $agenum, $programmitems) )
        {
            $orderdata = $this->get_order_data();
            $orderdata->data->state = 2;
            return $this->save_order_data($orderdata);
        }
        return false;
        
    }

    /** Обрабатывает пришедшие из третьей формы данные: загружает приказ, сохраняет данные, обновляет его и т.д.
     * 
     * @param object $formdata - данные из "третьей" формы
     * @return bool - результат операции
     */
    public function process_form_third($formdata)
    {
        if ( !is_object($formdata) )
        {
            return false;
        }
        
        $orderdata = $this->get_order_data();
        $orderdata->data->state = 3; // обработали третий экран
        // Привяжем к переменной
        $currentprogramms =& $orderdata->data->cstreams;
        if ( $formdata->direction == 'fromteacher' )
        {// Обрабатываем приказ на передачу нагрузки от преподавателя
            $updateprogramms = $formdata->cstreams;
            foreach ( $updateprogramms as $pid => $cstreams )
            {// Для всех программ просмотрем изменённую информацию по потокам и обновим их
                foreach ( $cstreams as $cid => $appointmentid )
                {
                    if ( $appointmentid != 0 )
                    {
                        $person = $this->dof->storage('appointments')->get_person_by_appointment($appointmentid, true);
                        $pname = $this->dof->storage('persons')->get_fullname($person);
                    } else
                    {
                        $person = new stdClass();
                        $person->id = 0;
                        $person->enumber = 0;
                        $pname = '';
                    }
                    $currentprogramms[$pid]->cstreams[$cid]->teacherid = $person->id;
                    $currentprogramms[$pid]->cstreams[$cid]->enumber = $person->enumber;
                    $currentprogramms[$pid]->cstreams[$cid]->fullname = $pname;
                    $currentprogramms[$pid]->cstreams[$cid]->appointmentid = $appointmentid;
                }
            }
        } else if ( $formdata->direction == 'toteacher' )
        {// Обрабатываем приказ на передачу нагрузки преподавателю
            $currentappointments = $currentprogramms; // Группировка по людям, а не по программам
            $updateappointments = $formdata->cstreams;

            foreach ( $updateappointments as $appid => $cstreams )
            {// Для всех преподавателей просмотрем изменённую информацию по потокам и обновим их
                foreach ( $cstreams as $cid => $checked )
                { // $checked это значение checkbox'а
                    $currentappointments[$appid]->cstreams[$cid]->checked = $checked;
                }
            }
        } else if ( $formdata->direction == 'returnhours' )
        {// Обрабатываем приказ на возврат нагрузки
            $orderdata->data->direction = 'returnhours';
        }
        return $this->save_order_data($orderdata);
    }
    
    /** Обрабатывает пришедшие из формы данные: загружает приказ, сохраняет первичные данные,
     *  получает списки потоков или загружает информацию из старого приказа (для возврата часов)
     * 
     * @param object $formdata - данные из формы
     * @return bool - результат операции
     */
    public function process_form($formdata)
    {
        if ( !is_object($formdata) )
        {
            return false;
        }
        
        $orderdata = $this->get_order_data();
        switch ( $formdata->direction )
        { // Направление
            case 'fromteacher':
                $teacher = $formdata->fullname;
                // Получим список потоков и запишем сюда
                $orderdata->data->cstreams = $this->get_cstreams_group_program_transfer($teacher['id']);
                break;
            case 'toteacher':
                $teacher = $formdata->fullname;
                // Получим список потоков и запишем сюда
                $orderdata->data->cstreams = $this->get_cstreams_group_program_take($teacher['id']);
                break;
            case 'returnhours':
                // Получим список потоков из старого приказа и запишем сюда
                $orderdata = $this->get_order_returnhours($formdata);
                $orderdata->data->state = 3;
                return $this->order->save($orderdata);
            default:
                return false;
        }
        // Сохраним состояние приказа
        $orderdata->data->state = 1;
        $orderdata->data->direction = $formdata->direction;
        if ( isset($teacher) )
        {// Данные по преподавателю
            $person = $this->dof->storage('appointments')->get_person_by_appointment($teacher['id']);
            $orderdata->data->fullname      = $this->dof->storage('persons')->get_fullname($person);
            $orderdata->data->appointmentid = $teacher['id'];
            $orderdata->data->teacherid     = $person->id;
            $orderdata->data->status        = $this->dof->storage('appointments')->
                                        get_field(array('id'=>$teacher['id']), 'status');
            $orderdata->data->enumber       = $this->dof->storage('appointments')->
                                        get_field(array('id'=>$teacher['id']), 'enumber');
        }
        // Причина и смена статуса
        $orderdata->data->reason = $formdata->reason;
        if ( isset($formdata->changestatus) )
        {
            $orderdata->data->changestatus = $formdata->changestatus;
        } else
        {
            $orderdata->data->changestatus = 0;
        }
        return $this->order->save($orderdata);
    }
    
    /** Сохраняет данные приказа
     * 
     * @param object $orderdata - данные приказа
     * @return bool - результат операции
     */
    public function save_order_data($orderdata)
    {
        $this->order->save($orderdata);
    }
    
    /** Проверяет, можно ли подписать приказ и подписывает его при возможности
     * если уже подписан, возвращает true
     * 
     * @param int $personid - id из таблицы persons
     * @return bool - статус подписи: успешно/уже подписан или не удалось подписать
     */
    public function sign($personid)
    {
        if ( !is_int_string($personid) )
        {
            return false;
        }
        if ( $this->order->is_signed() )
        { // Уже подписан
            return true;
        }
        if ( $this->check_order_data() AND $this->delete_empty_cstreams() )
        { 
            return $this->order->sign($personid);
        }
        return false;
    }
    
    /** Выполняет проверку, чтобы при подписке или исполнении приказа не оказалось,
     * что приказ выполняется по старым данным, которые уже не актуальны.
     * 
     * @return bool - результат операции
     */    
    public function check_order_data()
    {
        $orderdata = $this->get_order_data();
        $direction = $orderdata->data->direction;
        if ( empty($orderdata->data) OR empty($orderdata->data->cstreams))
        {// Данных или потоков нет
            return false;
        }
        
        // Списки активных статусов
        $appstatuses = $this->dof->workflow('appointments')->get_meta_list('active');
        $cststatuses = $this->dof->workflow('cstreams')->get_meta_list('active');
        $tchstatuses = $this->dof->workflow('teachers')->get_meta_list('active');
        switch ($direction)
        {
            case 'fromteacher':
                // Группировка по программам
                $programms =& $orderdata->data->cstreams;
                foreach ( $programms as $obj ) // в obj хранится name программы и cstreams
                {// По каждой программе просматриваем информацию о потоках
                    foreach ( $obj->cstreams as $id => $cstream )
                    {
                        if ( empty($cstream->appointmentid) )
                        { // Пропустим потоки, для которых ничего не выставлено
                            continue;
                        }
                        // Проверить, что все указанные преподаватели активны
                        $appstatus = $this->dof->storage('appointments')->get_field($cstream->appointmentid, 'status');
                        if ( empty($appstatus) OR !array_key_exists($appstatus, $appstatuses) )
                        {
                            return false;
                        }
                        // Потом проверить, что все потоки активны
                        $cststatus = $this->dof->storage('cstreams')->get_field($id, 'status');
                        if ( empty($cststatus) OR !array_key_exists($cststatus, $cststatuses) )
                        { 
                            return false;
                        }
                        // Проверить, что указанные преподаватели могут взять потоки
                        $conds = array('appointmentid'=>$cstream->appointmentid, 'programmitemid'=>$cstream->programmitemid);
                        $tchstatus = $this->dof->storage('teachers')->get_field($conds, 'status');
                        if ( empty($tchstatus) OR !array_key_exists($tchstatus, $tchstatuses) )
                        {
                            return false;
                        }
                        
                    }
                }
                
                break;

            case 'toteacher':
                // Группировка по должностным назначениям
                $appids =& $orderdata->data->cstreams;
                // Проверить, что указанный преподаватель будет активен после смены статуса (если есть)
                $appid = $orderdata->data->appointmentid;
                if ( $orderdata->data->changestatus )
                {// Если смена статуса есть
                    $statuses = array(
                        'sickleave' => 'patient',
                        'vacation'  => 'vacation',
                        'recovery'  => 'active',
                        'vacationreturn' => 'active',
                    );
                    if ( array_key_exists($orderdata->data->reason, $statuses) )
                    {
                        $appstatus = $statuses[$orderdata->data->reason];
                    } else
                    {// Передали неверную причину
                        return false;
                    }
                } else 
                {// Смены статуса не производится, тогда возьмём текущий статус
                    $appstatus = $this->dof->storage('appointments')->get_field($appid, 'status');
                }
                if ( empty($appstatus) OR !array_key_exists($appstatus, $appstatuses) )
                {// Статуса нет, либо он неактивен
                    return false;
                }
                
                foreach ( $appids as $object )
                {// По всем преподавателям проверим потоки
                    foreach ( $object->cstreams as $id => $cstream )
                    {
                        // Потом проверить, что все потоки активны
                        $cststatus = $this->dof->storage('cstreams')->get_field($id, 'status');
                        if ( empty($cststatus) OR !array_key_exists($cststatus, $cststatuses) )
                        { 
                            return false;
                        }
                        // Проверить, что указанный преподаватель может взять потоки
                        $conds = array('appointmentid'=>$appid, 'programmitemid'=>$cstream->programmitemid);
                        $tchstatus = $this->dof->storage('teachers')->get_field($conds, 'status');
                        if ( empty($tchstatus) OR !array_key_exists($tchstatus, $tchstatuses) )
                        { 
                            return false;
                        }
                    }
                }
                break;

            case 'returnhours':
                $programms =& $orderdata->data->cstreams; // группировка по программам
                // Проверить, что указанный преподаватель будет активен после смены статуса (если есть)
                $appid = $orderdata->data->appointmentid;
                if ( $orderdata->data->changestatus )
                {// Если смена статуса есть
                    $statuses = array(
                        'sickleave' => 'patient',
                        'vacation'  => 'vacation',
                        'recovery'  => 'active',
                        'vacationreturn' => 'active',
                    );
                    if ( array_key_exists($orderdata->data->reason, $statuses) )
                    {
                        $appstatus = $statuses[$orderdata->data->reason];
                    } else
                    {// Передали неверную причину
                        return false;
                    }
                } else 
                {// Смены статуса не производится, тогда возьмём текущий статус
                    $appstatus = $this->dof->storage('appointments')->get_field($appid, 'status');
                }
                if ( empty($appstatus) OR !array_key_exists($appstatus, $appstatuses) )
                {// Статуса нет, либо он неактивен
                    return false;
                }
                foreach ( $programms as $object )
                {// По всем программам просмотрим потоки
                    foreach ( $object->cstreams as $id => $cstream )
                    {
                        // Потом проверить, что все потоки активны
                        $cststatus = $this->dof->storage('cstreams')->get_field($id, 'status');
                        if ( empty($cststatus) OR !array_key_exists($cststatus, $cststatuses) )
                        {
                            return false;
                        }
                        // Проверить, что указанный преподаватель может взять потоки
                        $conds = array('appointmentid'=>$appid, 'programmitemid'=>$cstream->programmitemid);
                        $tchstatus = $this->dof->storage('teachers')->get_field($conds, 'status');
                        if ( empty($tchstatus) OR !array_key_exists($tchstatus, $tchstatuses) )
                        { 
                            return false;
                        }
                    }
                }
                break;
            default:
                return false;
        }
        // Проверим, что переход в указанный статус возможен
        if ( ! empty($orderdata->data->changestatus) )
        {
            if ( ! $list = $this->dof->workflow('appointments')->get_available($orderdata->data->appointmentid) )
            {// Ошибка получения статуса для объекта;
                return false;
            }
            switch ( $orderdata->data->reason )
            {
                case 'sickleave':
                    $status = 'patient';
                    break;

                case 'vacation':
                    $status = 'vacation';
                    break;

                case 'recovery':
                case 'vacationreturn':
                    $status = 'active';
                    break;
                default:
                    // Нет такой причины
                    return false;
            }
            if ( ! isset($list[$status]) )
            {// Переход в данный статус из текущего невозможен';
                return false;
            }
        }
        return true;
    }
    
    /** Удаляет из объекта приказа все потоки, для которых не отмечены учителя на передачу
     * 
     * @return bool - результат операции
     */
    public function delete_empty_cstreams()
    {
        // Подготовим данные, уберём лишние
        $orderdata = $this->get_order_data();
        $direction = $orderdata->data->direction;
        $cstreams =& $orderdata->data->cstreams;
        // unset($orderdata->data->state); // Если удалить из orderdata поле, тогда save перезапишет всё поверх
        $orderdata->data->state = 0; // Значит, конечное состояние
        // Списки активных статусов
        switch ($direction)
        {
            case 'fromteacher':
                // Группировка по программам
                $programms = $cstreams;
                foreach ( $programms as $pid => $object )
                {
                    foreach ( $object->cstreams as $id => $cstream )
                    {
                        if ( empty($cstream->appointmentid) )
                        { // Удалим потоки, для которых ничего не выставлено
                            unset($orderdata->data->cstreams[$pid]->cstreams[$id]);
                        }
                    }
                    if ( empty($object->cstreams) )
                    { // Удалим пустую ветку
                        unset($orderdata->data->cstreams[$pid]);
                    }
                }
                break;

            case 'toteacher':
                // Группировка по должностным назначениям
                $appids = $cstreams;
                foreach ( $appids as $appid => $object )
                {
                    foreach ( $object->cstreams as $id => $cstream )
                    {
                        if ( empty($cstream->checked) )
                        { // Удалим потоки, для которых ничего не выставлено
                            unset($orderdata->data->cstreams[$appid]->cstreams[$id]);
                        }
                    }
                    if ( empty($object->cstreams) )
                    { // Удалим пустую ветку
                        unset($orderdata->data->cstreams[$appid]);
                    }
                }
                break;

            case 'returnhours':
                // Удалять уже ничего не нужно, приказ исполнялся, а теперь
                //  просто в обратную сторону
                return true;

            default:
                return false;
        }
        return $this->order->save($orderdata);
    }
    
    /** Обёртка для исполнения приказа 
     * (возможно, необходимо выполнять какие-то действия перед ним?)
     * 
     * @return bool - результат операции
     */
    public function execute()
    {
        return $this->order->execute();
    }
    
    /** Фильтрует потоки (выставляет значения по-умолчанию) по переданным параметрам: id периода, номер семестра,
     *  массив дисциплин (programmitemid) с номерами appointmentid (0 = ничего не делать)
     * 
     * @param int $ageid - номер периода
     * @param int $agenum - номер семестра
     * @param array $programmitemids - массив в формате: $programmitemids[$pitid] = $appointmentid
     * @return bool - результат операции фильтрации потоков
     */
    public function filter_cstreams_ageid_agenum_pitems_appids($ageid = null, $agenum = null, $programmitemids = null)
    {
        if ( !is_null($ageid) AND !is_int_string($ageid) OR
             !is_null($agenum) AND !is_int_string($agenum) OR
             !is_null($programmitemids) AND !is_array($programmitemids) )
        {
            return false;
        }
        if ( empty($programmitemids) )
        {// Не фильтруем, так не фильтруем
            return true;
        }
        $orderdata = $this->get_order_data();
        switch ( $orderdata->data->direction )
        {// Направление передачи нагрузки
            case 'fromteacher':
                // Группировка по программам
                $programms =& $orderdata->data->cstreams;
                foreach ( $programms as $pid => $object )
                {// По каждой программе
                    foreach ( $object->cstreams as $id => $cstream )
                    {// По каждому потоку
                        $pitid = $cstream->programmitemid;
                        if ( ( $ageid  == $cstream->ageid  OR empty($ageid)  ) AND
                             ( $agenum == $cstream->agenum OR empty($agenum) ) AND
                               isset($programmitemids[$pitid]) )
                        {// Если выбранный фильтр совпал
                            if ( $programmitemids[$pitid] != 0 )
                            { // Если указан преподаватель
                                $teacher = $this->dof->storage('appointments')->get_person_by_appointment($programmitemids[$pitid], true);
                                $name = $this->dof->storage('persons')->get_fullname($teacher);
                                $orderdata->data->cstreams[$pid]->cstreams[$id]->appointmentid = $programmitemids[$pitid];
                                $orderdata->data->cstreams[$pid]->cstreams[$id]->teacherid = $teacher->id;
                                $orderdata->data->cstreams[$pid]->cstreams[$id]->fullname = $name;
                                $orderdata->data->cstreams[$pid]->cstreams[$id]->enumber = $teacher->enumber;
                            } else 
                            { // Выбрано "по-умолчанию": вычисляем учителя по минимальной нагрузке
                                $appid = $this->get_minimal_load_teacher($pitid);
                                if ( !empty($appid) )
                                {
                                    $teacher = $this->dof->storage('appointments')->get_person_by_appointment($appid, true);
                                    $name = $this->dof->storage('persons')->get_fullname($teacher);
                                    $orderdata->data->cstreams[$pid]->cstreams[$id]->appointmentid = $appid;
                                    $orderdata->data->cstreams[$pid]->cstreams[$id]->teacherid = $teacher->id;
                                    $orderdata->data->cstreams[$pid]->cstreams[$id]->fullname = $name;
                                    $orderdata->data->cstreams[$pid]->cstreams[$id]->enumber = $teacher->enumber;
                                }
                            }
                            
                        } else 
                        { // Если фильтр не совпал.. удалять ли потоки остальные?
                            
                        }
                    }
                }
                break;

            case 'toteacher':
                // Группировка по преподавателям
                $appids =& $orderdata->data->cstreams;
                foreach ( $appids as $appid => $object )
                {// По каждому табельному номеру (appointment)
                    foreach ( $object->cstreams as $id => $cstream )
                    {// По каждому потоку
                        $pitid = $cstream->programmitemid;
                        if ( ( $ageid  == $cstream->ageid  OR empty($ageid)  ) AND
                             ( $agenum == $cstream->agenum OR empty($agenum) ) AND
                               isset($programmitemids[$pitid]) )
                        {// Если выбранный фильтр совпал
                            if ( $programmitemids[$pitid] != 0 AND
                                 $programmitemids[$pitid] == $appid)
                            {// Отмечаем галочкой поток
                                $orderdata->data->cstreams[$appid]->cstreams[$id]->checked = 1;
                            } else 
                            {
//                                $orderdata->data->cstreams[$appid]->cstreams[$id]->checked = 0;
                            }
                        } else 
                        { // Если фильтр не совпал.. удалять ли потоки остальные?
//                            $orderdata->data->cstreams[$appid]->cstreams[$id]->checked = 0;
                        }
                    }
                }
                break;

            default:
                break;
        }
        return $this->save_order_data($orderdata);
        
    }

}

final class dof_im_cstreams_orders_table 
{
    private $dof;
    private $orders;
    private $change_teacher;
    private $addvars; 
    
    /** Конструктор
     * 
     */
    public function __construct($dof,$orders,$change_teacher,$addvars=array())
    {
        $this->dof             = $dof;
        $this->orders          = $orders;
        $this->change_teacher  = $change_teacher;
        $this->addvars         = $addvars;        
    }
    
    /** Возвращает таблицу со списком приказов
     * 
     * @return string - таблица со списком приказов
     */
    public function show_table()
    {
        $rez = '<h2 align="center">'.$this->dof->get_string('list_orders', 'cstreams').'</h2>';
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->head = $this->get_head_description();
        $table->align = array('center','center','center','center','center','center','center');
        
        if ( ! empty($this->orders) )
        {// есть приказы - строим таблицу
            foreach ( $this->orders as $order )
            {
                $table->data[] = $this->get_string_order($order);
            }
            
            $rez .= $this->dof->modlib('widgets')->print_table($table,true);
        }else 
        {// список пустой - скажем об этом
            $rez .= ' <br><h3 align="center">'.$this->dof->get_string('empty_order', 'cstreams').'</h3>';   
        }
          
        return $rez;
    }

    /** Возвращает строку таблицы для одного приказа
     * 
     * @param object $order - объект приказа
     * @return array - массив со столбцами, содержащих информацию о приказе
     *                 array($actions, $order->id, $date, $owner, $signdate, $signer, $exdate)
     */
    protected function get_string_order($order)
    {
        $actions = '';
        if ( ! empty($order->signdate) )
        {// подписан - смотреть и исполнить
            // TODO сделать ссылку для подписан
            $actions .= '
            <a href="'.$this->dof->url_im('cstreams','/orderctload/view.php?id='.$order->id,$this->addvars)
            .'" title="'. $this->dof->get_string('order_see','cstreams')
            .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/view.png').'" ></a>';
            if (  empty($order->exdate) )
            {// приказ не исполнен - покажем ссылку
                $actions .= '
                <a href="'.$this->dof->url_im('cstreams', '/orderctload/execute.php?id='.$order->id,$this->addvars)
                .'" title="'. $this->dof->get_string('order_ready','cstreams')
                .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/ready.png').'"></a>';
            }
        }else
        {// не подписан - удалить, продолжить работу, подписать
            // TODO сделать ссылку для готово
            $actions .= '
            <a href="'.$this->dof->url_im('cstreams', '/orderctload/delete.php?id='.$order->id,$this->addvars)
            .'" title="'. $this->dof->get_string('order_delete','cstreams')
            .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/delete.png').'" > </a>';
           
            $a = $this->change_teacher->load($order->id);
            
            if ( ! empty($a->data->cstreams) )
            {// на случай чтоб не подписать и не просматривать ПУСТОЙ приказ
                $actions .= '
                <a href="'.$this->dof->url_im('cstreams', '/orderctload/edit.php?id='.$order->id,$this->addvars)
                .'" title="'. $this->dof->get_string('order_edit','cstreams')
                .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/edit.png').'"> </a>
                <a href="'.$this->dof->url_im('cstreams', '/orderctload/sign.php?id='.$order->id,$this->addvars)
                .'" title="'. $this->dof->get_string('order_write','cstreams')
                .'"> <img src="'.$this->dof->url_im('cstreams', '/icons/write.png').'">  </a>';
            }
        }
        
        $date = dof_userdate($order->date, '%d/%m/%Y');
        $owner = $this->dof->get_string('not_defined', 'cstreams');
        if ( $order->ownerid )
        {// ссылка на персону, создавшую приказ
            $owner = '<a href="'.$this->dof->url_im('persons','/view.php',array(
                    'id' => $order->ownerid, 'departmentid' => $this->addvars['departmentid']))
                    .'">'.$this->dof->storage('persons')->get_fullname($order->ownerid).'</a>';
        }
        $signer = $this->dof->get_string('not_defined', 'cstreams');
        if ( $order->signerid )
        {// ссылка на персону, подписавшую приказ
            $signer = '<a href="'.$this->dof->url_im('persons','/view.php',array(
                    'id' => $order->signerid, 'departmentid' => $this->addvars['departmentid']))
                    .'">'.$this->dof->storage('persons')->get_fullname($order->signerid).'</a>';
        }
        $signdate = $this->dof->get_string('not_defined', 'cstreams');
        if ( $order->signdate )
        {// дата подписания известна
            $signdate = dof_userdate($order->signdate, '%d/%m/%Y');
        }
        $exdate = $this->dof->get_string('not_defined', 'cstreams');
        if ( $order->exdate )
        {// дата исполнения известна
            $exdate = dof_userdate($order->exdate, '%d/%m/%Y');
        }
        return array($actions, $order->id, $date, $owner, $signdate, $signer, $exdate);
    }
    
    /** Получение заголовков таблицы
     * 
     * @return array - массив заголовков
     */
    protected function get_head_description()
    {
        return array($this->dof->get_string('action', 'cstreams'),
                     $this->dof->get_string('tbl_id', 'cstreams'),
                     $this->dof->get_string('tbl_createdate', 'cstreams'),
                     $this->dof->get_string('tbl_owner', 'cstreams'),
                     $this->dof->get_string('tbl_signdate', 'cstreams'),
                     $this->dof->get_string('tbl_signer', 'cstreams'),
                     $this->dof->get_string('tbl_exdate', 'cstreams'));
    }
}

?>