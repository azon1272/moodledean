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


/** Справочник учебных программ
 * 
 */
class dof_storage_schdays extends dof_storage
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
        // после установки плагина устанавливаем права
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
        global $CFG, $DB;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        $result = true;
        if ($oldversion < 2014061000)
        {// добавим поле birthadressid
            $field = new xmldb_field('type', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'departmentid');
            if ( ! $dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            $index = new xmldb_index('itype', XMLDB_INDEX_NOTUNIQUE,array('type'));
            // добавляем индекс для поля
            if ( ! $dbman->index_exists($table, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2014061000;
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
        return 'schdays';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('departments' => 2011060201,
                                      'acl'         => 2011041800));
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
        return array('storage'=>array('acl'=>2011040504));
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
        return 'block_dof_s_schdays';
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
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage($this->code())->get_field($objectid, 'departmentid');
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
    
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']     = array('roles'=>array('manager'));
        $a['edit']     = array('roles'=>array('manager'));
        $a['create']   = array('roles'=>array('manager'));
        $a['delete']   = array('roles'=>array());
        $a['use']      = array('roles'=>array('manager'));
        
        // @todo пока не создан плагин workflow/schdays - это право будет находиться здесь
        $a['changestatus:to:deleted'] = array('roles'=>array('manager'));
        
        return $a;
    }         

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Получить фрагмент списка дней 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds - список параметров для выборки периодов 
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( is_null($conds) )
        {// если список периодов не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object) $conds;
        if ( $limitnum <= 0 AND ! is_null($limitnum) )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault(); 
        }
        if ( $limitfrom < 0 AND ! is_null($limitfrom) )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        // посчитаем общее количество записей, которые нужно извлечь
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($select);
        }
        // добавим сортировку
        // сортировка из других таблиц - пока не имеется
        $outsort = '';
        $sort = $this->get_orderby_listing($sort);
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->get_records_select($select,null,$sort,$fields,$limitfrom,$limitnum);
    }
    
    /**Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @param string $prefix - префикс к полям, если запрос составляется для нескольких таблиц
     * @return string
     */
    public function get_select_listing($inputconds,$prefix='')
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        $conds = (object) $conds;
        if ( isset($conds->date_from) )
        {
            // формируем дату на 12:00
            $dateday = getdate($conds->date_from);
            $time_from = $this->dof->storage('persons')->get_make_timestamp(12,0,0,$dateday['mon'],$dateday['mday'],$dateday['year']);
            $selects[] = 'date >= '.$time_from;
            unset($conds->date_from);
        }
        if ( isset($conds->date_to) )
        {
            // формируем дату на 12:00
            $dateday = getdate($conds->date_to);
            $time_to = $this->dof->storage('persons')->get_make_timestamp(12,0,0,$dateday['mon'],$dateday['mday'],$dateday['year']);
            $selects[] = 'date <= '.$time_to;
            unset($conds->date_to);
        }
        if ( ! empty($conds->noid) )
        {// исключаем из выборки самого себя
            $selects[] = " id != ".$conds->noid;
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->noid);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->query_part_select($prefix.$name,$field);
            }
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }
    
    /** Возвращает фрагмент sql-запроса c ORDER BY
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_orderby_listing($sort,$prefix='')
    {
        // по-умолчанию имя
        $sqlsort = $prefix.'date';
        if ( ! is_array($sort) )
        {// сортировки не переданы - вернем умолчание
            return $sqlsort;
        }
        $dir = 'asc';
        if ( isset($sort['dir']) )
        {// вид сортировки
            $dir = $sort['dir'];
            unset($sort['dir']);
        }
        if ( empty($sort) )
        {// сортировок нет - вернем умолчание с видом
            return $sqlsort.' '.$dir;
        }
        // формируем сортировку
        $selects = array();
        foreach ( $sort as $field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $prefix.$field.' '.$dir;
            }
        } 
        // добавим умолчание в конец
        $selects[] = $prefix.'date '.$dir;
        // возвращаем сортировку
        return implode($selects,',');
    }

    /** Проверяет наличия дня в системе
     * @param int $ageid - id периода из таблица ages
     * @param int $date - дата дня
     * @param int $depid - id подразделения из таблицы departments
     * @return bool true - есть, false - нет
     */
    public function is_exists_day($ageid,$date,$depid)
    {
        // преобразуем дату на 12:00, если передали не так
        $mdate = getdate($date);
        $date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
        return (bool) $this->count_records_select
               ("ageid={$ageid} AND date={$date} AND departmentid={$depid} AND 
                 status IN ('plan','active','holiday','fixed') ");
    }
    
    /** Получает день по параметрам 
     * @param int $ageid - id периода из таблица ages
     * @param int $date - дата дня
     * @param int $depid - id подразделения из таблицы departments
     * @return object|false
     */
    public function get_day($ageid,$date,$depid)
    {
        // преобразуем дату на 12:00, если передали не так
        $mdate = getdate($date);
        $date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
        $depsql = '';
        if ( ! empty($depid) )
        {
            $depsql = "AND departmentid={$depid}";
        }
        if ( ! $days = $this->get_records_select
               ("ageid={$ageid} AND date={$date} {$depsql} AND 
                 status IN ('plan','active','holiday','fixed', 'draft') ") )
        {// дня нет
            return false;
        }
        // если день есть, то только один
        return current($days);
    }
    
    /** Получает дни по параметрам 
     * @param int $ageid - id периода из таблицы ages
     * @param int $depid - id подразделения из таблицы departments
     * @param int $begindate - начало периода
     * @param int $enddate - конец периода
     * @return array|false
     */
    public function get_days($ageid, $depid = null, $begindate = null, $enddate = null)
    {
        $depsql = '';
        if ( ! empty($depid) )
        {
            $depsql = "AND departmentid={$depid}";
        }
        $begindsql = '';
        if ( ! empty($begindate) )
        {
            $begindsql = "AND date >= {$begindate}";
        }
        $enddsql = '';
        if ( ! empty($enddate) )
        {
            $enddsql = "AND date <= {$enddate}";
        }
        if ( ! $days = $this->get_records_select
               ("ageid={$ageid} {$begindsql} {$enddsql} {$depsql} AND 
                 status IN ('plan','active','holiday','fixed') ") )
        {// дней нет
            return false;
        }
        return $days;
    }
    
    /** Сохраняет день
     * @param int $ageid - id периода из таблица ages
     * @param int $date - дата дня
     * @param int $daynum - номер дня
     * @param int $dayvar - вариант недели
     * @param int $depid - id подразделения из таблицы departments
     * @return int|false 
     */
    public function save_day($obj)
    {
        if ( $this->is_exists_day($obj->ageid,$obj->date,$obj->departmentid) )
        {// если день уже создан - второй создавать нельзя
            return false;
        }
        
        // сохраняем день
        $dbobj = clone $obj;
        // преобразуем дату на 12:00, если передали не так
        $mdate = getdate($obj->date);
        $dbobj->date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
        return $this->insert($dbobj);
        
    }
    
    /** 
     * Обновляет день в праздничный
     * @param int $dayid - id учебного дня в таблице schdays
     * @return int|false 
     */
    public function update_holiday($dayid)
    {
        $obj = new stdClass();
        $obj->type = 'holiday';
        return $this->update($obj,$dayid);
        
    }
    /** Удаляет день
     * 
     * @param int|object $day - id дня в таблице schdays или объект из той же таблицы
     * 
     * @return bool 
     */
    public function delete_day($day)
    {
        // найдем день 
        if ( ! is_object($day) )
        {// если переменная - не объект, значит нам передали id
            if ( ! $day = $this->get($day) )
            {// неправильный формат данных или такой записи не существует
                return false;
            }
        }
        return $this->dof->workflow('schdays')->change($day->id,'deleted');
        
    }
    
    /** Удаляет день и все события этого дня
     * @param int $dayid - id учебного дня в таблице schdays
     * 
     * @return bool
     */
    public function delete_entire_day($dayid)
    {
        $delevents = true;
        $conds = new stdClass();
        $conds->dayid = $dayid;
        $sql = $this->dof->storage('schevents')->get_select_listing($conds);
        if ( $events = $this->dof->storage('schevents')->get_records_select($sql) )
        {
            foreach($events as $event)
            {// для каждой КТ удалим ее вместе с событием
                if ( ! $this->dof->storage('schevents')->cancel_event($event->id, true) ) 
                {// не удалось удалить событие
                    // нельзя будет удалить и ДЕНЬ
                    $delevents = false;
                }
            }
        }
        
        if ( ! $delevents )
        {// не смогли удалить ВСЕ дня с этой датой
            return false;
        }
        // если все прошло успешно - удалить сам день
        if ( ! $this->dof->storage('schdays')->delete_day($dayid) )
        {// попытались удалить день, но не получилось
            return false;
        }
        return true;
    }
    
    
    /** 
     * Получить список возможных типов дней
     *
     * @return array
     */
    public function get_types()
    {
        return array(
                'working'  => $this->dof->get_string('working', 'schdays', null, 'storage'),
                'holiday'  => $this->dof->get_string('holiday', 'schdays', null, 'storage'),
                'vacation' => $this->dof->get_string('vacation', 'schdays', null, 'storage'),
                'dayoff'   => $this->dof->get_string('dayoff', 'schdays', null, 'storage')
        );
    }
} 
?>