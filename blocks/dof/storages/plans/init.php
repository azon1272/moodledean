<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999  Alex Djachenko (Алексей Дьяченко)             //
// alex-pub@my-site.ru                                                    //
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

require_once $DOF->plugin_path('storage','config','/config_default.php');

/** Справочник учебных программ
 * 
 */
class dof_storage_plans extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
        
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG;
        $result = true;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2012042500;
    }
    /** Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'plans';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array('storage'=>array('ages'=>2009050600,
		                              'programms'=>2009040800,
		                              'programmitems'=>2009060800,
		                              'cstreams'=>2009060800,
		                              'config'=> 2011080900,
		                              'acl'     => 2011041800));
    }
    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion=0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'=>2011040504,
        			 'config'=> 2011080900));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
    }
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return false;
    }
    
    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid); 
        switch ( $do )
        {// определяем дополнительные параметры в зависимости от запрашиваемого права
            //право создать темы в своем журнале
            case 'create/in_own_journal':
                //$objid = $cstreamid
                if ( $this->dof->storage('cstreams')->get_field($objid,'status') != 'active' )
                {// создавать можно только в активном потоке
                    return false;
                }
                if ( $this->dof->storage('cstreams')->get_field($objid,'teacherid') != $personid )
                {// персона не учитель потока
                    return false;
                }
                
            break;
            // право на редактирование темы          
            case 'edit':
                if ( ! $plan = $this->dof->storage('plans')->get($objid) )
                {// не нашли тему, проверять нечего
                    return false;
                }
                if ( $plan->status == 'fixed' OR $plan->status == 'completed' )
                {// фиксированную или пройденную тему редактировать нельзя
                    return false;
                }
            break;
            // право на редактирование темы в своем журнале             
            case 'edit/in_own_journal':
                if ( ! $plan = $this->dof->storage('plans')->get($objid) )
                {// не нашли тему, проверять нечего
                    return false;
                }
                if ( $plan->linktype != 'cstreams' )
                {// тема должна быть из фактического планирования
                    return false;
                }
                if ( $plan->status == 'fixed' OR $plan->status == 'completed' )
                {// фиксированную или пройденную тему редактировать нельзя
                    return false;
                }
                $cstream = $this->dof->storage('cstreams')->get($plan->linkid);
                if ( $event = current($this->dof->storage('schevents')->get_records(array
                       ('planid'=>$plan->id,'status'=>array('plan','active','completed')))) )
                {// персона должна быть учителем урока
                    if ( $personid != $event->teacherid OR ($cstream->status != 'active' AND $cstream->status != 'suspend') ) 
                    {// только учителю и только для активного потока
                        return false;
                    }
                }else
                {// персона - учитель потока
                    if ( $personid != $cstream->teacherid OR ($cstream->status != 'active' AND $cstream->status != 'suspend') ) 
                    {// только учителю и только для активного потока
                        return false;
                    }
                }
            break;
        }   
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        } 
        return false;
    }
    
	/** Требует наличия полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    
    /** Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
        return true;
    }
    /** Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan,$messages)
    {
        return true;
    }
    /** Обработать задание, отложенное ранее в связи с его длительностью
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_plans';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** Получить список параметров для фунции has_hight()
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     * 
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code, 
                              $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }    
      
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles'=>array('manager','teacher','methodist'));
        $a['edit']   = array('roles'=>array('manager'));
        // редактировать тему в своем журнале
        $a['edit/in_own_journal']   = array('roles'=>array('teacher'));
        $a['use']    = array('roles'=>array('manager','teacher','methodist'));
        $a['create'] = array('roles'=>array('manager'));
        // создавать тему в своем журнале
        $a['create/in_own_journal'] = array('roles'=>array('teacher'));
        $a['delete'] = array('roles'=>array());

        return $a;
    }

    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        // отчет по учителям
        $config = array();
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'scale';
        $obj->value = '1-5';
        $config[$obj->code] = $obj;
        return $config;
    }       
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    /**
     * Возвращает список контрольных точек по статусу, типу привязки и id привязки,
     * (данный метод возвращает только контрольные точки, напрямую привязанные к объекту,
     * его не следует использовать для отображения полного учебно-тематического плана!)
     * @param $id - id элемента, к которому привязана
     * контрольная точка.
     * @param $type - тип элемента к которому привязана 
     * контрольная точка. Это название таблицы - cstreams, 
     * programms, programmitems, ages.
     * @param $status - статус контрольной точки, 
     * если null - вернет событя с любым статусом
     * @return mixed - array - массив объектов из таблицы, 
     * или bool false 
     */
    public function get_checkpoints($id, $type, $status = 'active')
    {
        //формируем условие выборки
        $select = "linkid='{$id}' AND linktype='{$type}'";
        if ( ! is_null($status) )
        {//добавляем выборку по статусу
            $select .= " AND status='{$status}'";
        }
        //выбираем записи
        return $this->dof->storage('plans')->get_records_select($select, null,'reldate ASC');
    }
    /** Получить список контрольных точек, со всеми статусами, кроме указанного
     * 
     * @return array|bool - массив записей из таблицы plans или false если ничего не найдено
     * @param string $status[optional] - статус уоторый надо исключить
     */
    public function get_list_no_status($status = 'active')
    {
        $select = "";
        if ( ! is_null($status) )
        {//добавляем выборку по статусу
            $select .= "status != '{$status}'";
        }
        //выбираем записи
        return $this->dof->storage('plans')->get_records_select($select);
    }
    /**
     * Возвращает массив объединенных КТ и событий отсортированных 
     * по дате события или КТ
     * @param $id - id учебного процесса в таблице cstreams
     * @param array $planstatuses  - список статусов с которыми получаются контрольные точки из таблицы plans
     *                              (по умолчанию - с любым статусом)
     * @param array $eventstatuses - список статусов с которыми нужно получить события из таблицы schevents
     *                              (по умолчанию - с любым статусом)
     * @return array массив объектов - контрольных точек из таблицы plans. Если для КТ есть событие - то
     *               оно записывается в поле "event"
     */
    public function sort_checkpoints_and_events($id, $planstatuses=null, $eventstatuses=null, $directmap=null)
    {
        //получаем все нужные контрольные точки
        $points = $this->get_checkpoints_for_cstreams($id, $planstatuses, $directmap);
        //получаем массив состыкованных КТ и событий
        $all = $this->dof->storage('schevents')->get_points_and_events($points,$id,$eventstatuses);
        //создаем новый массив состыкованных КТ и событий, 
        //в котором индексы заменены на метки времени
        $datepe = array();
        foreach ( $all as $pe )
        {
            //print_object($pe);
            // получим будущий ключ массива - дату события
            $key = $this->get_date($pe);
            if ( ! $key )
            {// дата события неизвестна - переходим к обработке следующего элемента
                continue;
            }
            while ( array_key_exists($key, $datepe) )
            {// если полученный ключ в массиве уже существует - 
                //увеличим его на секунду
                $key++;
            }
            // запишем получившийся уникальный ключ в массив
            $datepe[$key] = $pe;
        }
        //сортируем массив по ключам от меньшего к большему сохраняя отношение ключ => значение
        ksort($datepe);
        // возвращаем отсортированный массив
        return $datepe;
    }
    
    /**
     * Возвращает метку времени КT или соответствующего события
     * @param object $pe - объект контрольной точки 
     * @return int - время контрольной точки
     */
    private function get_date($pe)
    {
        if ( isset($pe->event->date) )
        {// если есть событие - то вернем дату начала события
            return $pe->event->date;
        }elseif ( isset($pe->date) AND $pe->date != 0 )
        {// если нет события- то  вернем указанную дату начала контрольной точки
            return $pe->date;
        }elseif ( isset($pe->reldate) )
        {// если и ее нет - то вернем дату как начало учебного потока + смещение
            $cstream = $this->dof->storage($pe->linktype)->get($pe->linkid);
            if ( $cstream AND isset($cstream->begindate) )
            {
                return ($cstream->begindate + $pe->reldate);
            }else
            {// непонятно, откуда брать дату
                return false;
            }
        }else
        {// в остальных случаях - ошибка
            return false;
        }
    }
    
    /** Возвращает список контрольных точек актуальных для данного учебного процесса 
     * (включая контрольные точки дисциплины и периода, с которыми связан данный учебный процесс)
     * 
     * @param int $csid - id учебного процесса в таблице cstreams
     * @param $statuses - статусы контрольных точек в таблице plans
     * @return array - список контрольных точек
     */
    public function get_checkpoints_for_cstreams($csid, $statuses=null, $directmap=null)
    {
    	// находим данный учебный поток
    	$cstream = $this->dof->storage('cstreams')->get($csid);
    	if ( ! $cstream )
    	{ //не нашли потока работать не с чем
    		return false;
    	}
        
    	// сформируем условие для выборки
    	$select = "((linkid='{$csid}' AND linktype='cstreams')";
    	$select .= " OR (linkid='{$cstream->programmitemid}' AND linktype='programmitems')";
    	// найдем связанный с потоком период
    	$age = $this->dof->storage('ages')->get($cstream->ageid);
    	if ( $age )
    	{   // есть период - добавим его в условие 
    		$select .= " OR (linkid='{$cstream->ageid}' AND linktype='ages'))"; 
    		// и сформируем вычисляемую колонку, по которой будем производить сортировку
    	    $fields = ' *, IF(linktype = \'ages\','.$age->begindate.' + reldate,'.$cstream->begindate.' + reldate) AS absdate';  
    	} else
    	{ // нет периода - формируем колонку без него 
    	    $select .=")"; 
    	    $fields = ' *, ('.$cstream->begindate.' + reldate) AS absdate';	    	    
    	}
    	if ( is_array($statuses) AND ! empty($statuses) )
    	{// нужно вернуть только записи с указанными статусами (статусы заключаем в кавычки)
    	    $select .= " AND status IN ('".implode("', '", $statuses)."')";
    	}
        if ( $directmap )
        {
            $select .= " AND directmap='".$directmap."'";
        }
    	return $this->get_records_select($select,null,'absdate ASC',$fields);
    }
    /** Возвращает количество КТ, удовлетворяющих 
     * указанным критериям
     * 
     * @param string $select - критерии отбора записей
     * @return int количество найденных записей
     */
    public function get_numberof_points($select)
    {
        dof_debugging('storage/plans get_numberof_points.Этот метод не имеет смысла', DEBUG_DEVELOPER);
        return $this->count_select($select);
    }
    
    /** Отменяет КТ с событием
     * @param int $id - id КТ
     * @param bool $all - удалять ли все события: true - да, false - нет
     * @return bool true - КТ отменено, false - возникли ошибки
     */
    public function cancel_checkpoint($id, $all = false)
    {
        $cp = $this->get((int)$id);
        if ( ! $cp )
        {//нет такой КТ
            return false;
        }
        //получаем события КТ';
        $schevents = $this->dof->storage('schevents')->
            get_pointevents($cp);
        if ( $schevents )
        {//события есть - отменяем их';
            $rez = true;
            foreach ( $schevents as $ev )
            {
                //print_object($ev);
                $rez = $rez AND $this->dof->storage('schevents')->cancel_event($ev->event->id, $all);
            }
            if ( ! $rez )
            {//не все события отменили';
                return false;
            }
            //проверим - не осталось ли событий
            unset($schevents);
            $schevents = $this->dof->storage('schevents')->
                get_pointevents($cp, null, array('plan', 'postponed','completed','replaced'));
            if ( ! $schevents )
            {//отменяем КТ';
                $cp->status = 'canceled';
                return $this->update($cp);
            }
            //есть активные события';
            return false;
        }else
        {//можно отменять КТ';
            $cp->status = 'canceled';
            return $this->update($cp);
        }
        return false;
    }
    
    /** Разбивает шкалу оценок на массив оценок
     * @param string $scale - шкала оценок
     * @return array - массив оценок
     * TODO удалить этот метод после 02.2012
     */
    public function get_grades_scale($scale)
    {
        $grades = array();
        if ( ! is_string($scale) OR $scale == '' )
        {// не шкала - зададим универсальную
            for ($i=1; $i<=5; $i++)
            {
                $grades[$i] = trim($i);
            }
        }else
        {// разберем шкалу по кусочкам
            $grades = $this->get_grades_scale_str($scale);
        }
        return $grades;
    }
    /** Возвращает список тематических разделов
     * @param string $linktype - тип связи
     * @param int $linkid - id связи
     * @param array $statuses - список статусов
     * @param bool $viewplan - показать плановое планирование
     * @param int $directmap - отображение в журнале
     * @param bool $noremoveitself - не показывать план на самого себя
     * @return array|false - список тематических разделов
     */
    public function get_theme_plan($linktype, $linkid, $statuses=null, $viewplan = false, 
                                   $directmap = null, $noremoveitself = false)
    {
    	// находим данный учебный поток
    	if ( $linktype === 'cstreams' OR  $linktype === 'plan' )
    	{// выберем КТ также для периода и предмета
        	$cstream = $this->dof->storage('cstreams')->get($linkid);
        	if ( ! $cstream )
        	{ //не нашли потока работать не с чем
        		return false;
        	}
        	$select = "(";
        	// сформируем условие для выборки
        	if ( ! $noremoveitself )
        	{// самого себя не покажем
        	    $select .= "(linkid='{$linkid}' AND linktype='{$linktype}')";
        	    $select .= " OR ";
        	}
        	if ( $linktype === 'cstreams' AND $viewplan )
        	{// для предмето-класса можно брать темы из планового планирования
        	    // если это нужно
        	    $select .= " (linkid='{$linkid}' AND linktype='plan')"; 
        	    $select .= " OR ";
        	}
            if ( $linktype === 'plan' AND $viewplan )
            {// для предмето-класса можно брать темы из планового планирования
                // если это нужно
                $select .= " (linkid='{$cstream->programmitemid}' AND linktype='programmitems')";
                $select .= " OR ";
            }
        	// @todo - не отображаем планирование на предмет
        	//$select .= " (linkid='{$cstream->programmitemid}' AND linktype='programmitems')";
        	$select .= " (linkid='{$cstream->ageid}' AND linktype='ages'))"; 
    	}elseif ( ! $noremoveitself )
    	{// только для указанного типа
    	    $select = $this->query_part_select('linktype',$linktype);
    	    $select .=' AND ';
    	    $select .= $this->query_part_select('linkid',$linkid);
    	}else
    	{// создаем запрос, который точно вернет пустое значение
    	    $select = $this->query_part_select('linktype',0);
    	    $select .=' AND ';
    	    $select .= $this->query_part_select('linkid',0);
    	}
    	
    	if ( ! empty($statuses) )
    	{// нужно вернуть только записи с указанными статусами
    	    $select .=' AND ';
    	    $select .= $this->query_part_select('status',$statuses);
    	}
    	if ( isset($directmap) AND ! empty($directmap) )
    	{
    	    $select .=" AND directmap='{$directmap}'";
    	}
    	return $this->get_records_select($select,null,'reldate ASC');
    }
    
    /** Разбивает шкалу оценок на массив оценок
     * @param string $scale - шкала оценок
     * @return array - массив оценок - ассоциативный
     */
    public function get_grades_scale_str($scale)
    {// объявим массив    
        $grades = array();
        $scl = explode(',', $scale);
        foreach ($scl as $element)
        {// перебираем все оценки шкалы
        	if ( preg_match('/-/', $element) )
        	    {
        		    $boundaries = explode('-', $element);
        		    // определим границы максимальных и минимальных значений
        		    $min = $boundaries[0]; 
        		    $max = $boundaries[1]; 
        			if ( $min != '' AND $max != '')
        			{// диапозон то записываем
        			    if ( $min > $max )
        			    {// если обратная шкала
        			        $mini = $min;
        			        $min = $max;
        			        $max = $mini;
        			    }
        			    for ($i=$min; $i<=$max; $i++)
                        {
                            $grades[$i] = "$i"; 
                        }
        				continue;
                    }
                }
                $grades[$element] = $element; 
        }    
        return $grades;
    }
    
    /** Наследует учебный темплан из планирования по предмету
     * @param object|int $cstream - объект|id из таблицы cstreams
     * @return bool - true|false
     */
    public function succession_pitem_plan($cstream)
    {
        // найдем поток 
        if ( ! is_object($cstream) )
        {// если переменная - не объект, значит нам передали id
            if ( ! $cstream = $this->dof->storage('cstreams')->get($cstream) )
            {// неправильный формат данных или такой записи не существует
                return false;
            }
        }
        // @todo - какие по статусу темы наследуются?
        // найдем наследуемый темплан
        $pitemplans = $this->get_records(array('linktype'=>'programmitems',
                  'linkid'=>$cstream->programmitemid,'status'=>'active'));
        $rez = true;
        foreach ( $pitemplans as $pitemplan )
        {
            // клонируем наследника
            $successor = clone $pitemplan;
            // @todo - какие еще поля удалять при наследовании?
            // удаляем ненужные поля
            unset($successor->id);
            unset($successor->status);
            // переопределяем поля
            $successor->linktype = 'plan';
            $successor->linkid = $cstream->id;
            $successor->parentid = $pitemplan->id;
            if ( $id = $this->insert($successor) )
            {
                $rez = $rez AND $this->dof->storage('planinh')->create_point_links($id, array($pitemplan->id));
            }else
            {
                $rez = false;
            }
        }
        return $rez;
    }

    /** Сохранить данные одного поля при ajax-радактировании
     * @param string $querytype - уникальное имя запроса сохранения внутри плагина. Как правило имя 
     *                            сохраняемого поля
     * @param int    $objectid - id объекта, данные которого редактируются
     * @param object $data -  данные для сохранения (обычно - новое значение поля)
     * 
     * @return string - новое значение элемента или строка с html-кодом ошибки
     */
    public function widgets_save_field($querytype, $objectid, $data)
    {
        switch ($querytype) 
        {
            case 'name':
                $obj = new stdClass();
                $obj->id   = $objectid;
                $obj->name = $data;
                $this->update($obj);
            break;
        }
        
        return $data;
    }
    
    
    /** Метод, который возаращает список для автозаполнения
     * 
     * @param string $querytype - тип завпроса(поу молчанию стандарт)
     * @param object $obj - объект с параметрами для выборки
     * 
     * @return array or false - запись, если есть или false, если нет
     */
    public function widgets_field_variants_list($querytype='standart', $obj)
    {
        if ( ! is_object($obj) OR ! $obj )
        {// пустые даные
            return false;
        }
        // таблица выборки
        $tbl = $this->prefix().$this->code();
        $sql = '';
        // от типа запроса - своя выборка
        switch ($querytype)
        {
            // стандартный тип - выборка по полю NAME значений через LIKE
            case 'standart' :
                if ( empty($obj->name) )
                {// неверные данные передали
                    return false;
                }
                // селект запрос после слова where, одинаковые поля не выводим
                $sql = " SELECT DISTINCT name FROM {$tbl} WHERE name LIKE '{$obj->name}%' ORDER BY name ";
                return $this->get_records_sql($sql,null,0 ,10);
        }
        // нет ни одного из типа
        return false;
    }    
    
    /** Подгрузить значение поля перед inline-редактированием
     * @param string $fieldname - название поля объекта, которое будет подгружено
     * @param int    $objectid  - id редактируемого объекта
     * @param mixed  $data[optional] - дополнительные данные для запроса
     * 
     * @return string
     */
    public function widgets_load_field($fieldname, $objectid, $data=null)
    {
        return $this->get_field($objectid, $fieldname);
    }
    
}
    
?>