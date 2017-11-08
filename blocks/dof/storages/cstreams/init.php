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
// подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Справочник учебных процессов (предмето-классы)
 * 
 */
class dof_storage_cstreams extends dof_storage implements dof_storage_config_interface
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
        global $DB;
        $result = true;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        
        if ($oldversion < 2013062700)
        {// добавим поле salfactor
            $field = new xmldb_field('salfactor', XMLDB_TYPE_FLOAT, '6', XMLDB_UNSIGNED, 
                    true, null, '1', 'lastgradesync');
            // количество знаков после запятой
            $field->setDecimals('2');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE,
                    array('salfactor'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            
            // добавим поле substsalfactor
            $field = new xmldb_field('substsalfactor', XMLDB_TYPE_FLOAT, '6', XMLDB_UNSIGNED, 
                    true, null, '0', 'salfactor');
            // количество знаков после запятой
            $field->setDecimals('2');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('isubstsalfactor', XMLDB_INDEX_NOTUNIQUE,
                    array('substsalfactor'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }

        if ($oldversion < 2013082800)
        {// добавим поле salfactor
            dof_hugeprocess();
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE,
                    array('salfactor'));
            if ($dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('salfactor', XMLDB_TYPE_FLOAT, '6, 2', null, 
                    XMLDB_NOTNULL, null, '0', 'lastgradesync');
            $dbman->change_field_default($table, $field);
                        if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            while ( $list = $this->get_records_select('salfactor = 1',null,'','*',0,100) )
            {
                foreach ($list as $cstream)
                {// ищем уроки где appointmentid не совпадает с teacherid
                    $obj = new stdClass;
                    $obj->salfactor = 0;
                    $this->update($obj,$cstream->id);
                }               
            }
            
        }
        
        // установлена самая свежая версия
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2016071500;
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
        return 'cstreams';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('cstreamlinks'  => 2009060900,
                                      'agroups'       => 2009011600,
                                      'programmitems' => 2009060800,
                                      'acl'           => 2011040504,
                                      'config'        => 2011080900
                                      ) );
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
        return array(array('plugintype'=>'storage', 'plugincode'=>'cstreams', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'cstreams', 'eventcode'=>'update'),
                     array('plugintype'=>'storage', 'plugincode'=>'cstreamlinks', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'cstreamlinks', 'eventcode'=>'update'),
                     array('plugintype'=>'storage', 'plugincode'=>'cstreamlinks', 'eventcode'=>'delete'),
                     array('plugintype'=>'storage', 'plugincode'=>'cpassed', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'cpassed', 'eventcode'=>'update'),
                     );
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
            case 'edit/plan':
                if ( 'plan' != $this->get_field($objid,'status') )
                {//редактировать можно только запланированные потоки
                    return false;
                }
            break;              
                
        }  
        // проверка
        return $this->acl_check_access_paramenrs($acldata);
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
        if ( $gentype === 'storage' AND ($gencode === 'cstreamlinks' 
                    OR $gencode === 'cpassed') )
        {//обрабатываем события от справочника cstreamlink, cpassed
            switch($eventcode)
            {
                case 'insert': return $this->get_cstreamname($eventcode,$mixedvar);
                case 'update': return $this->get_cstreamname($eventcode,$mixedvar);
                case 'delete': return $this->get_cstreamname($eventcode,$mixedvar);
            }
        }
        if ( $gentype === 'storage' OR $gencode === 'cstreams' )
        {//обрабатываем события от своего собственного справочника
            switch($eventcode)
            {
                case 'insert': return $this->get_cstreamname($eventcode,$mixedvar, true);
                case 'update': return $this->get_cstreamname($eventcode,$mixedvar, true);
            }
        }
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
        switch ( $code )
        {
            // пересинхронизация всех потоков дисциплины
            case 'resync_programmitem_cstreams': 
                $this->todo_resync_programmitem_cstreams($intvar,$mixedvar->personid); 
                break;
            // пересинхронизация всех потоков подразделений
            case 'resync_department_cstreams': 
                $this->todo_resync_department_cstreams($intvar,$mixedvar->personid); 
                break;
            // остановка всех активных cpassed
            case 'programmitem_cpass_to_suspend': 
                $this->todo_itemid_active_to_suspend($intvar,$mixedvar->personid); 
                break;
            // запусе всех приостановленных cpassed
            case 'programmitem_cpass_to_active': 
                $this->todo_itemid_suspend_to_active($intvar,$mixedvar->personid); 
                break;
            // Активация учебных процессов
            case 'activate_cstreams':
                return $this->activate_cstreams($intvar, $mixedvar->id);
                break;
        }
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
        return 'block_dof_s_cstreams';
    }

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************    
    
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
      
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles'=>array('manager', 'methodist'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['use']    = array('roles'=>array('manager', 'methodist'));
        $a['create'] = array('roles'=>array('manager', 'methodist'));
        $a['delete'] = array('roles'=>array());
        // право менять предмет
        $a['edit:programmitemid']   = array('roles'=>array(''));
        $a['edit/plan']   = array('roles'=>array('manager','methodist'));

        return $a;
    }
    
    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        // плагин включен и используется
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Максимально разрешенное количество объектов этого типа в базе
        // (указывается индивидуально для каждого подразделения)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'objectlimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;        

        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Вставляет запись в таблицу(ы) плагина 
     * @param object dataobject 
     * @param bool quiet - не генерировать событий
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function insert($dataobject, $quiet=false)
    {
        $mode = 'programmitems';
        if ( ! $dataobject = $this->default_departmentid($dataobject,$mode) )
        {// не смогли выставить подразделение по умолчанию
            return false;
        }
        return parent::insert($dataobject, $quiet);
    }
        
    /** Получить список учебных процессов у данного преподавателя 
     * @param int $id - id преподавателя
     * @param string $status - статус потока (по умолчанию "идет")
     * @deprecated
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_teacher_cstream($id, $status = 'active')
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        $select = 'teacherid = '.$id;
 
        if ( is_string($status) )
        {// получить события с указанным статусом
            $select .= ' AND status = \''.$status.'\'';
        }
        
        return $this->get_records_select($select);
    }
    
    /** Получить список учебных процессов у данного должностного назначения
     * 
     * @param int $appid - id должностного назначения
     * @param string $status - статус потока (по умолчанию 'active') - через метастатусы
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_appointment_cstreams($appid, $status = null, $fields = '*')
    {
        if ( ! is_int_string($appid) )
        {//входные данные неверного формата 
            return false;
        }
        if ( is_null($status) )
        {
            $status = $this->dof->workflow('cstreams')->get_meta_list('active');
        } else 
        {
            $status = $this->dof->workflow('cstreams')->get_meta_list($status);
        }
        $in = implode("','", array_keys($status));
        $select = 'appointmentid = '.$appid;
 
        if ( is_string($in) )
        {// получить события с указанным статусом
            $select .= ' AND status IN (\''.$in.'\')';
        }
        return $this->get_records_select($select, null, '', $fields);
    }
    
    /** Получить список учебных процессов у должностного назначения, которые он может взять
     * Функция создана после отказа от поля teacherid
     * 
     * @param int $appid - id должностного назначения
     * @param string $status - статус потока (по умолчанию 'active') - через метастатусы
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_appointment_take_cstreams($appid, $status = null, $fields = '*')
    {
        if ( ! is_int_string($appid) )
        {//входные данные неверного формата 
            return false;
        }
        // Список дисциплин учителя, которые он может взять по своему должностному назначению
        $pitemids = $this->dof->storage('teachers')->get_appointment_pitems($appid);
        if ( is_null($status) )
        {
            $status = $this->dof->workflow('cstreams')->get_meta_list('active');
        } else 
        {
            $status = $this->dof->workflow('cstreams')->get_meta_list($status);
        }
        $status = implode("','", array_keys($status));
        $pitems = implode(",", array_keys($pitemids));
        $select = 'appointmentid != '.$appid;
 
        if ( is_string($status) )
        {// получить события с указанным статусом
            $select .= ' AND status IN (\''.$status.'\')';
        }

        if ( is_string($pitems) )
        {// получить события с указанным статусом
            $select .= ' AND programmitemid IN ('.$pitems.')';
        }

        return $this->get_records_select($select, null, '', $fields);
    }
    
    /** Получить список учебных процессов у данного подразделения
     * @param int $id - id преподавателя
     * @param string $status - статус потока (по умолчанию "идет")
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_department_cstream($id, $status = 'active')
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        $select = 'departmentid = '.$id;
 
        if ( is_string($status) )
        {// получить события с указанным статусом
            $select .= ' AND status = \''.$status.'\'';
        }
        
        return $this->get_records_select($select);
    }
   /** Получить список учебных процессов для данного учебного периода
     * @param int $id - id учебного периода
     * @param string $status - статус потока (по умолчанию "идет")
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_age_cstream($id, $status = 'active')
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        
        $select = 'ageid = '.$id;
 
        if ( is_string($status) )
        {// получить события с указанным статусом
            $select .= ' AND status = \''.$status.'\'';
        }
        
        return $this->get_records_select($select);
    }
    /** Получить список учебных процессов по данной дисциплине
     * @param int $id - id дисциплины
     * @param string $status - статус потока (по умолчанию "идет")
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_programmitem_cstream($id, $status = 'active')
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        
        $select = 'programmitemid = '.$id;
 
        if ( is_string($status) )
        {// получить события с указанным статусом
            $select .= ' AND status = \''.$status.'\'';
        }
        
        return $this->get_records_select($select);
    }
    /** Получить список учебных процессов для академической группы
     * @param int $id - id академической группы в таблице agroups
     * @return mixed array массив процессов или bool false если процессы не найдены
     */
    public function get_agroup_cstream($id)
    {
        // находим все связи процессов с группой
        $params = array();
        $params['agroupid'] = $id;
        $cstream = $this->dof->storage('cstreamlinks')->get_records($params);
        if ( ! $cstream )
        {
            return false;
        }
        return $this->get_list_by_list($cstream, 'cstreamid');
    }
    /** Получить Список программ по академической группе, и периоду
     * 
     * @return array|false - массив записей из таблицы cstreams если они есть, 
     *     или false, если ничего не нашлось
     * @param int $agroupid - id академической группы в таблице agroups
     * @param int $ageid - id учебного периода в таблице ages
     */
    public function get_agroup_agenum_cstreams($agroupid, $ageid)
    {
        // сначала получаем список потоков по переданной группе
        $agcstreams = $this->get_agroup_cstream($agroupid);
        if ( ! $agcstreams OR ! is_array($agcstreams) )
        {// если его нет - то нет смысла искать дальше
            return false;
        }
        $result = array();
        foreach ( $agcstreams as $id=>$agcstream )
        {// перебираем все учебные потоки этьой группы, и оставляем только те
            if ( $agcstream->ageid == $ageid )
            {// если поток относится к нужному периоду - запишем его в результат
                $result[$id] = $agcstream;
            }
        }
        if ( empty($result) )
        {// если ничего не найдено - вернем false
            return false;
        }
        return $result;
    }
    /** Получить Список программ по академической группе, и статусу
     * 
     * @return array|false - массив записей из таблицы cstreams если они есть, 
     *     или false, если ничегг не нашлось
     * @param int $agroupid - id академической группы в таблице agroups
     * @param string $status - статус потока 
     */
    public function get_agroup_status_cstreams($agroupid, $status)
    {// сначала получаем список потоков по переданной группе
        $agcstreams = $this->get_agroup_cstream($agroupid);
        if ( ! $agcstreams OR ! is_array($agcstreams) )
        {// если его нет - то нет смысла искать дальше
            return false;
        }
        $result = array();
        
        foreach ( $agcstreams as $id=>$agcstream )
        {// перебираем все учебные потоки этьой группы, и оставляем только те
            if ( $agcstream->status === $status )
            {// если поток относится к нужному периоду - запишем его в результат
                $result[$id] = $agcstream;
            }
        }
        if ( empty($result) )
        {// если ничего не найдено - вернем false
            return false;
        }
        return $result;
    }
    /** Возвращает количество потоков
     * 
     * @param string $select - критерии отбора записей
     * @return int количество найденных записей
     */
    public function get_numberof_cstreams($select)
    {
        dof_debugging('storage/apointments get_numberof_cstreams.Этот метод не имеет смысла', DEBUG_DEVELOPER);
        return $this->count_select($select);
    }
    
    /** Получить список учебных потоков, допустимых учебной программой и текущим периодом
     * 
     * @return array|bool - массив записей из базы, или false
     * @param object $programmid - id учебной программы в таблице programms
     * @param object $ageid - id периода в таблице ages
     * @param[optional] string $status - статус учебного потока
     */
    public function get_prog_age_cstreams($pitemid, $ageid, $status=null)
    {
        if ( ! intval($pitemid) OR ! intval($ageid) )
        {// не переданы необходимые параметры
            return false;
        }
        $select = ' programmitemid = '.$pitemid.' AND ageid = '.$ageid;
        if ( $status )
        {// если указан статус - добавим его в запрос
            $select .= ' AND status = "'.$status.'"';
        }
        return $this->get_records_select($select);
    }
    
    /** Получает все учебные потоки программы
     * @param int $programmid - id программы
     * @param int $ageid - id периода, по умолчанию нет
     * @return mixed array массив потоков или bool false если потоки не найдены
     */
    public function get_programm_age_cstreams($programmid, $ageid = null, $agenum = null, $dpid = null)
    {
        if ( ! is_int_string($programmid) OR ! ( is_int_string($ageid) OR is_null($ageid)) 
               OR ! ( is_int_string($agenum) OR is_null($agenum)) 
                     OR ! ( is_int_string($dpid) OR is_null($dpid)) )
        {//входные данные неверного формата
            return false;
        }
        //найдем предметы программы
        if ( is_null($agenum) )
        {// если параллели нет - выведем на все
            $items = $this->dof->storage('programmitems')->get_records(array('programmid'=>$programmid));
        }else
        {// только на указанную параллель
            $items = $this->dof->storage('programmitems')->
                     get_records(array('programmid'=>$programmid,'agenum'=>$agenum));
        }
        if ( ! $items )
        {// предметов нет
            return false;
        }
        foreach ( $items as $item )
        {// выберем id каждого предмета
            $itemid[] = $item->id;
        }
        // составляем условие
        $select = ' programmitemid IN ('.implode(', ', $itemid).')';
        if ( ! is_null($ageid) )
        {// если указан период выведем в текущем периоде
            $select .= ' AND ageid = '.$ageid;
        }
        if ( ! is_null($dpid) AND $dpid )
        {// если указан период выведем в текущем периоде
            $select .= ' AND departmentid = '.$dpid;
        }
        // возвращаем найденные потоки
        return $this->dof->storage('cstreams')->get_records_select($select);
    }
    
    /** Возвращает список учебных потоков по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_listing($conds=null, $limitfrom=null, $limitnum=null, $sort='', $fields='c.*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object) $conds;
        if ( $limitnum <= 0 AND ! is_null($limitnum) )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault(); 
        }
        if ( $limitfrom < 0 AND ! is_null($limitnum) )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds,'c.');
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tblprogramms = $this->dof->storage('programms')->prefix().$this->dof->storage('programms')->tablename();
        $tblprogrammitems = $this->dof->storage('programmitems')->prefix().$this->dof->storage('programmitems')->tablename();
        $tblcstreams = $this->prefix().$this->tablename();
        if (strlen($select)>0)
        {
            $select .= ' AND ';
        }
        $sql = "FROM {$tblcstreams} as c, {$tblprogrammitems} as pi, {$tblprogramms} as p 
                WHERE $select c.programmitemid=pi.id AND 
                      pi.programmid=p.id ";
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_sql("SELECT COUNT(*) {$sql}");
        }
        $sql = "SELECT {$fields}, pi.name as pitemname, pi.code as pitemcode, p.id as programmid, 
                       p.name as progname, p.code as progcode {$sql}";
        // Добавим сортировку
        $sql .= $this->get_orderby_listing($sort);
        //print $sql;
        return $this->get_records_sql($sql, null,$limitfrom, $limitnum);
        
    }
    
    /** Возвращает фрагмент sql-запроса после слова WHERE
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
        if ( ! empty($conds->noid) )
        {// надо исключить id из условия
            $selects[] = " id != ".$conds->noid;
            unset($conds->noid);
        }
        if ( isset($conds->teacherid) AND intval($conds->teacherid) )
        {// ищем записи по академической группе
            if ( $appoints = $this->dof->storage('appointments')->get_appointment_by_persons($conds->teacherid) )
            {// есть записи принадлежащие такой академической группе
                $appointids = array();
                foreach ( $appoints as $appoint )
                {// собираем все cstreamids
                    $appointids[] = $appoint->id;
                }
                // составляем условие
                $selects[] = $prefix.'appointmentid IN ('.implode(', ', $appointids).')';
            }elseif ( $conds->teacherid == 0)
            {
                $selects[] = $prefix."appointmentid = 0";
            }else
            {// нет записей принадлежащих такой академической группе
                // составим запрос, который гарантированно вернет false
                return $prefix.'id = -1 ';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->teacherid);
        }
        if ( isset($conds->appointmentid) AND $conds->appointmentid == 0 )
        {// ищем записи по академической группе
            $selects[] = $prefix."appointmentid = 0 ";
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->appointmentid);
        }
        if ( isset($conds->subsalfactor) )
        {// ищем записи по академической группе
            if ( $conds->subsalfactor == '1' )
            {
                $selects[] = $prefix."substsalfactor != 0 ";
            }elseif ( $conds->subsalfactor == '0' )
            {
                $selects[] = $prefix."substsalfactor = 0 ";
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->subsalfactor);
        }
        if ( isset($conds->agroupid) AND intval($conds->agroupid) )
        {// ищем записи по академической группе
            $cstreams   = $this->dof->storage('cstreamlinks')->get_agroup_cstreamlink($conds->agroupid);
            if ( $cstreams )
            {// есть записи принадлежащие такой академической группе
                $cstreamids = array();
                foreach ( $cstreams as $cstream )
                {// собираем все cstreamids
                    $cstreamids[] = $cstream->cstreamid;
                }
                // склеиваем их в строку
                $cstreamidsstring = implode(', ', $cstreamids);
                // составляем условие
                $selects[] = $prefix.'id IN ('.$cstreamidsstring.')';
            }else
            {// нет записей принадлежащих такой академической группе
                // составим запрос, который гарантированно вернет false
                return $prefix.'id = -1 ';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->agroupid);
        }
        if ( isset($conds->personid) AND intval($conds->personid) )
        {// ищем записи по академической группе
            // учитываем и статусы
            $cpassed = $this->dof->storage('cpassed')->get_records(array('studentid'=>$conds->personid));
            if ( $cpassed )
            {// есть записи принадлежащие такой академической группе
                $cstreamids = array();
                foreach ( $cpassed as $cpass )
                {// собираем все cstreamids
                    $cstreamids[] = $cpass->cstreamid;
                }
                // склеиваем их в строку
                $cstreamidsstring = implode(', ', $cstreamids);
                // составляем условие
                $selects[] = $prefix.'id IN ('.$cstreamidsstring.')';
            }else
            {// нет записей принадлежащих такой академической группе
                // составим запрос, который гарантированно вернет false
                return $prefix.'id = -1 ';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->personid);
        }
        if ( isset($conds->programmid) AND intval($conds->programmid) )
        {// ищем записи по академической группе
            $pitems   = $this->dof->storage('programmitems')->get_records(array('programmid'=>$conds->programmid));
            if ( $pitems )
            {// есть записи принадлежащие такой академической группе
                $pitemids = array();
                foreach ( $pitems as $pitem )
                {// собираем все cstreamids
                    $pitemids[] = $pitem->id;
                }
                // склеиваем их в строку
                $pitemsstring = implode(', ', $pitemids);
                // составляем условие
                $selects[] = $prefix.'programmitemid IN ('.$pitemsstring.')';
            }else
            {// нет записей принадлежащих такой академической группе
                // составим запрос, который гарантированно вернет false
                return $prefix.'id = -1 ';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->programmid);
        }
        if ( isset($conds->noagroupid) AND intval($conds->noagroupid) )
        {// ищем записи по академической группе
            $cstreams = $this->dof->storage('cstreamlinks')->get_agroup_cstreamlink($conds->noagroupid);
            if ( $cstreams )
            {// есть записи принадлежащие такой академической группе
                $cstreamids = array();
                foreach ( $cstreams as $cstream )
                {// собираем все cstreamids
                    if ( $cstream->cstreamid )
                    {
                        $cstreamids[] = $cstream->cstreamid;
                    }
                }
                // склеиваем их в строку
                $cstreamidsstring = implode(', ', $cstreamids);
                // составляем условие
                $selects[] = $prefix.'id NOT IN ('.$cstreamidsstring.')';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->noagroupid);
        }
        if ( isset($conds->nosbcid) AND intval($conds->nosbcid) )
        {// ищем записи по академической группе
            $cpassed = $this->dof->storage('cpassed')->get_records(array
                    ('programmsbcid'=>$conds->nosbcid,'status'=>array('plan','active','suspend')));
            if ( $cpassed )
            {// есть записи принадлежащие такой академической группе
                $cstreamids = array();
                foreach ( $cpassed as $cpass )
                {// собираем все cstreamids
                    if ( $cpass->cstreamid )
                    {
                        $cstreamids[] = $cpass->cstreamid;
                    }
                }
                // склеиваем их в строку
                $cstreamidsstring = implode(', ', $cstreamids);
                // составляем условие
                $selects[] = $prefix.'id NOT IN ('.$cstreamidsstring.')';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->nosbcid);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {//для каждого поля получим фрагмент запроса
            if ( $field )
            {
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
    /**
     * Возвращает фрагмент sql-запроса c ORDER BY
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_orderby_listing($sort=null)
    {
        if ( is_null($sort) OR empty($sort) )
        {
            return "ORDER BY p.name ASC, pi.name ASC, c.begindate ASC";   
        }
        // послана своя сортировка
        return " ORDER BY ".$sort;
    }
    
    /** Возвращает список учебных потоков по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_cstreams_group($conds = null, $sort='', $fields='*', $limitfrom = 0, $limitnum = 0)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tbl = $this->prefix().$this->tablename();
        if ( $fields )
        {// переданы поля, которые следует отобразить
            $fields = 'cs.'.$fields;
            $fields = str_replace(',',', cs.',$fields);
            // необходимые поля из потока
            $fields .= ',cl.agroupid';
        }
        $tblcstream = $this->dof->storage('cstreamlinks')->prefix().$this->dof->storage('cstreamlinks')->tablename();
        $sql = "SELECT {$fields} FROM {$tbl} as cs, {$tblcstream} as cl";
        $sql .= " WHERE cl.cstreamid=cs.id";
        if ( isset($conds->agroupid) )
        {// поле связки - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cl.agroupid',$conds->agroupid));
             // удалим из полей шаблона
             unset($conds->agroupid);
        }
        if ( $select = $this->get_select_listing($conds) )
        {// выборка не пустая
            $select = ' AND cs.'.preg_replace('/ AND /',' AND cs.',$select.' ');
            $select = preg_replace('/ OR /',' OR cs.',$select);
            $select = str_replace('cs. (','(cs.',$select);
            $select = str_replace('cs.(','(cs.',$select);
            $sql .= " {$select}";
        }
        if ( ! empty($sort) )
        {// сортировка не пустая
            $sort = 'cs.'.str_replace(',',', cs.',$sort);
            $sql .= " ORDER BY {$sort}";
        }
        //print $sql;
        return $this->get_records_sql($sql, null,$limitfrom, $limitnum);
    }
    
    /**
     * Проверяет числится ли указанный пользователь преподавателем. 
     * Любого или конкретного предмета.
     * @param int $personid - id пользователя
     * @param int $programmitemid - id предмета
     * @return bool
     */
    public function is_teacher($personid, $programmitemid = null)
    {
        if ( ! $personid )
        {// id персоны нет - это не учитель
            return false;
        }
        if ( is_null($programmitemid) )
        {//поток не передан
            return $this->is_exists(array('teacherid', $personid));
        }
        //поток передан
        return $this->is_exists(array('teacherid'=>$personid, 'programmitemid'=>$programmitemid));
    }
    
    /** Получить id всех периодов, в течение которых проходит обучение выбранной группы
     * 
     * @return array|bool - индексированный массив с уникальными значениями id периодов всех потоков или false
     * если ничего не найдено
     * @param int $agroupid - id академической группы в таблице agroups
     */
    public function get_agroup_ageids($agroupid)
    {
        $result = array();
        // получаем массив всех потоков академической группы
        $agcstreams = $this->get_agroup_cstream($agroupid);
        // получаем все id периодов 
        if ( ! $agcstreams )
        {// не найдено ни одного потока
            return false;
        }
        foreach ( $agcstreams as $agcstream )
        {// перебираем все элементы массива, и вытаскиваем только id
            $result[] = $agcstream->ageid;
        }
        // оставляем только уникальные значения
        $result = array_unique($result);
        // сортируем массив по возрастанию
        sort($result);
        return $result;
    }
    
    /** Создать учебные потоки для группы
     * 
     * @return 
     * @param int $agroupid - id акадкмическуой группы (класса) в таблице agroups
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $departmentid - id учебного подразделения в таблице departments
     * @param int $datebegin - дата начала обучения в формате unixtime
     * 
     */
    public function create_cstreams_for_agroup($agroupid, $ageid, $departmentid, $datebegin, $enddate=null)
    {
        if ( ! $agroup = $this->dof->storage('agroups')->get($agroupid) )
        {// не удалось получить академичеескую группу
            return false;
        }
        if ( ! $programm = $this->dof->storage('programms')->get($agroup->programmid) )
        {// не удалось получить учебную программу
            return false;
        }
        if ( ! $programmitems = $this->dof->storage('programmitems')->get_pitems_list
                               ($programm->id, $agroup->agenum, 'deleted') )
        {// нет потоков, некого подписывать - но считаем, что мы свою работы все равно сделали
            return true;
        }
        $result = true;
        // если в программе есть предметы на этот период - создадим для них подписки
        foreach ( $programmitems as $pitem )
        {
            $cslink=false;
            if ( $cstreams = $this->get_records(array('ageid'=>$ageid,'programmitemid'=>$pitem->id, 
                    'status'=>array('plan', 'active', 'suspend'))) )
            {// если уже есть такой поток
                foreach ( $cstreams as $cstream )
                {// и на него подписана группа
                    $params = array();
                    $params['cstreamid'] = $cstream->id;
                    $params['agroupid'] = $agroupid;
                    if ( $this->dof->storage('cstreamlinks')->get_record($params) ) 
                    {// поток не создаем
                        $cslink=true;
                    }
                }
            } 
            if ( ! $cslink AND $pitem->required == '1' )  
            {// нету связи создаем поток и привязываем
                $cstream = new stdClass();
                $cstream->ageid          = $ageid;
                $cstream->programmitemid = $pitem->id;
                // откуда брать id учителя?
                $cstream->teacherid      = 0;
                $cstream->departmentid   = $departmentid;
                $cstream->mdlgroup       = null;
                $cstream->eduweeks = $this->dof->storage('ages')->get_field($ageid,'eduweeks');
                if ( $pitem->eduweeks )
                {// или из предмета, если указано там
                    $cstream->eduweeks = $pitem->eduweeks;
                }
                $cstream->begindate      = $datebegin;
                $cstream->enddate        = $datebegin + $pitem->maxduration;
                if ( $enddate )
                {// дата окончания указана принудительно
                    $cstream->enddate = $enddate;
                }
                $cstream->status         = 'plan';
                // создаем подписку предмета на программу в текущем периоде
                if ( $id = $this->insert($cstream) )
                {// удалось вставить запись в базу
                    if ( $this->dof->storage('cstreamlinks')->
                               is_exists(array('cstreamid'=>$id, 'agroupid'=>$agroupid)) )
                    {// если запись для такого потока и такой группы существует - не создаем такую запись еще раз 
                        continue;
                    }
                    // запомним, если что-то пошло не так
                    $result = $result AND (bool)$this->dof->storage('cstreamlinks')->
                              enrol_agroup_on_cstream($agroupid, $id);
                }else
                {// во время вставки произошла ошибка
                    $result = $result AND false;
                }
            }
        }
        // возвращаем результат
        return $result;
        
    }
    
    /** Создать подписку на программу в учебном периоде для выбранной параллели
     * 
     * @return bool 
     * @param int $programmid - id учебной программы в таблице programms
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $agenum - номер параллели, для которой создается подписка
     * @param int $departmentid - id учебного подразделения в таблице departments
     * @param int $datebegin - дата начала обучения в формате unixtime
     */
    public function create_cstreams_for_programm($programmid, $ageid, $agenum, $departmentid, $datebegin, $enddate=null)
    {
        $result = true;
        if ( ! $programm = $this->dof->storage('programms')->get($programmid) )
        {// не удалось получить учебную программу
            return false;
        }
        if ( ! $programmitems = $this->dof->storage('programmitems')->get_pitems_list($programmid, $agenum, 'deleted') )
        {// нет потоков, некого подписывать - но считаем, что мы свою работы все равно сделали
            return true;
        }
        // если в программе есть предметы на этот период - создадим для них подписки
        foreach ( $programmitems as $pitem )
        {
            
            $cstream = new stdClass();
            $cstream->ageid          = $ageid;
            $cstream->programmitemid = $pitem->id;
            // откуда брать id учителя?
            $cstream->teacherid      = 0;
            $cstream->departmentid   = $departmentid;
            $cstream->mdlgroup       = null;
            $cstream->eduweeks = $this->dof->storage('ages')->get_field($ageid,'eduweeks');
            if ( $pitem->eduweeks )
            {// или из предмета, если указано там
                $cstream->eduweeks = $pitem->eduweeks;
            }
            $cstream->begindate      = $datebegin;
            $cstream->enddate        = $datebegin + $pitem->maxduration;
            if ( $enddate )
            {// дата окончания указана принудительно
                $cstream->enddate = $enddate;
            }
            $cstream->status         = 'plan';
            // создаем подписку предмета на программу в текущем периоде
            $result = $result AND (bool)$this->insert($cstream);
            
        }
        return $result;
    }
    
    /** Подписать группу на список потоков
     * 
     * @return bool - результат операции
     * @param int $agroupid - id группы в таблице agroups
     * @param int $ageid - id учебного периода в таблице ages
     * 
     * @todo выяснить, нужно ли реализовать возможность подписки группы не по определененому периоду, 
     * а для всех периодов?
     */
    public function enrol_agroup_on_cstreams($agroupid, $ageid)
    {
        $return = true;
        if ( ! $agroup = $this->dof->storage('agroups')->get($agroupid) )
        {// не удалось получить академичеескую группу
            return false;
        }
        // @todo нужно ли указывать agenum?
        if ( ! $programmitems = $this->dof->storage('programmitems')->get_pitems_list($agroup->programmid, $agroup->agenum) )
        {// в программе группы нет предметов для текущего периода
            return true;
        }
        foreach ( $programmitems as $pitem )
        {// подписываем группу на все потоки
            $cstreams = $this->get_prog_age_cstreams($pitem->id, $ageid);
            foreach ($cstreams as $cstream )
            {// создаем подписку группы на учебный поток
                if ( $this->dof->storage('cstreamlinks')->
                        is_exists(array('cstreamid'=>$cstream->id, 'agroupid'=>$agroupid)) )
                {// если запись для такого потока и такой группы существует - не создаем такую запись еще раз 
                    continue;
                }
                // запомним, если что-то пошло не так
                $return = $return AND (bool)$this->dof->storage('cstreamlinks')->
                          enrol_agroup_on_cstream($agroupid, $cstream->id);
            }
        }
        return $return;
    }
    
    /** Переводит поток в статус "завершен"
     * @param int $id - id потока
     * @return bool true - если поток удачно завершен и 
     * false в остальных случаях
     */
    public function set_status_complete($id)
    {
        if ( ! is_int_string($id) )
        {// входные данные неверного формата
            return false;
        }
        if ( ! $obj = $this->get($id) )
        {// объект не найден
            return false;
        }
        if ( $obj->status == 'completed' )
        {// поток уже завершен
            return true;
        }
        if ( $obj->status == 'plan' OR $obj->status == 'canceled' OR $obj->status == 'suspend')
        {// поток запланирован, приостановлен или отменен - его нельзя завершить
            return false;
        }
        $rez = true;
        // дата окончания действия подписки
        $obj->enddate = time();
        if ( ! $this->update($obj,$id) )
        {// не удалось обновить запись БД
            return false;
        }
        // переместить в статс "неудачно завершены" 
        if ( $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$id,
                                            'status'=>array('plan','active','suspend'))) )
        {// если есть незавершенные подписки на дисциплину сменим им статус
            foreach($cpassed as $cpass)
            {// переведем каждую в статус неуспешно завершена
                $rez = $this->dof->storage('cpassed')->set_final_grade($cpass->id) && $rez;
            }
        }
        if ( $rez )
        {// если все в порядке - меняем статус потока
            return $this->dof->workflow('cstreams')->change($id,'completed');
        }
        return $rez;
    }

    /** Возвращает список потоков по параметрам
     * @param int $programmitemid - id дисциплины
     * @param int $teacherid - id учителя
     * @param bool $mycstrems - показать ли потоки текущего пользователя
     * @param bool $completecstrems - показать ли завершенные потоки
     * @return array
     */
    public function get_cstreams_on_parametres($programmitemid, $teacherid = 0, $mycstrems = false, $completecstrems = false)
    {
        // составляем условие
        // предмет обязателен
        $select = ' programmitemid = '.$programmitemid;
        if ( $teacherid )
        {// если указан учитель выведем только для него
            $select .= ' AND teacherid = '.$teacherid;
        }elseif ( $mycstrems ) 
        {// если учителя нет, но надо показать потоки текущего пользователя
            if ( $teacherid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// если только он есть в БД
                $select .= ' AND teacherid = '.$teacherid;
            }
        }
        if ( $completecstrems )
        {// скахзано что надо вывести завершенные потоки вместе с активными
            $select .= ' AND status IN (\'active\',\'completed\')';
        }else
        {// выведем только активные
            $select .= ' AND status = \'active\'';
        }
        // возвращаем найденные потоки
        return $this->dof->storage('cstreams')->get_records_select($select);
    }
    /** Возвращает короткое имя потока
     * @return string
     */
    public function get_short_name($cstreamid)
    {
        if ( ! $cstream = $this->get($cstreamid) )
        {
            return false;
        }
        $pitem       = $this->dof->storage('programmitems')->get_field($cstream->programmitemid, 'name');
        $teacher     = $this->dof->storage('persons')->get_fullname($cstream->teacherid);
            
        $cstreamname = $pitem;
        if ( $teacher )
        {// если есть учитель - добавим его
            $cstreamname .= ', '.$teacher;
        }
        $cstreamname .= ' ['.$cstream->id.']';
        return $cstreamname;
    }
    
    /** Подписать учеников на поток
     * 
     * @return bool
     * @param object $cstream - объект из таблицы cstreams
     * @param object $programmsbcids - массив, состоящий из id подписок на программы в таблице programmsbcs
     */
    public function enrol_students_on_cstream($cstream, $programmsbcids)
    {
        if ( ! is_object($cstream) OR ! is_array($programmsbcids) )
        {// неправильный формат данных
            return false;
        }
        $result = true;
        foreach ( $programmsbcids as $programmsbcid )
        {// перебираем все подписки на программу и подписываем каждого ученика
            $result = $this->enrol_student_on_cstream($cstream, $programmsbcid) && $result;
        }
        return $result;
    }
    
    /** Исключить учеников из потока
     * 
     * @return bool
     * @param object $cstream - объект из таблицы cstreams
     * @param array $programmsbcids - массив, состоящий из id подписок на программы в таблице programmsbcs
     */
    public function unenrol_students_from_cstream($cstream, $programmsbcids)
    {
        if ( ! is_object($cstream) OR ! is_array($programmsbcids) )
        {// неправильный формат данных
            return false;
        }
        $result = true;
        foreach ( $programmsbcids as $programmsbcid )
        {// перебираем все подписки на программу и отписываем каждого ученика
            $result = $this->unenrol_student_from_cstream($cstream, $programmsbcid) && $result;
        }
        return $result;
    }
    
    /** 
     * Подписать одного ученика на поток
     * 
     * @todo проверить, не подписан ли уже ученик на этот поток
     * @todo добавить полную проверку объекта $cpassed, если к тому времени не введем функции безопасной вставки
     * 
     * @param object $cstream - объект из таблицы cstreams
     * @param int $programmsbcid - id подписки ученика на программу в таблице programmsbcs
     * 
     * @return int|bool - идентификатор созданной подписки на дисциплину или false в случае ошибки
     */
    public function enrol_student_on_cstream($cstream, $programmsbcid)
    {
        $programmsbcid = intval($programmsbcid);
        if ( ! is_object($cstream) OR ! $programmsbc = $this->dof->storage('programmsbcs')->get($programmsbcid) )
        {// неправильный формат данных
            return false;
        }
        if ( ! $studentid = $this->dof->storage('programmsbcs')->get_studentid_by_programmsbc($programmsbcid) )
        {// не нашли id ученика - это ошибка
            // @todo поймать здесь исключение которое будет генерироваться функцией get_studentid_by_programmitem
            return false;
        }
        if ( ! $programmitem = $this->dof->storage('programmitems')->get($cstream->programmitemid) )
        {// предмет потока на который подписывается ученик не найден
            // @todo сгенерировать исключение и записать это событие в лог, когда станет возможно
            return false;
        }
        // создаем объект для будущей подписки на предмет
        $cpassed = new stdClass();
        $cpassed->cstreamid      = $cstream->id;
        $cpassed->programmsbcid  = $programmsbcid;
        $cpassed->programmitemid = $cstream->programmitemid;
        $cpassed->studentid      = $studentid;
        $cpassed->agroupid       = $programmsbc->agroupid;
        $cpassed->gradelevel     = $programmitem->gradelevel;
        $cpassed->ageid          = $cstream->ageid;
        // @todo с типом синхронизации разобраться когда станет окончательно ясно как обавлять обычные cpassed
        //$cpassed->typesync       = 0;
        // @todo добавить  сюда сведения о часах из дисциплины, когда эти поля появятся в таблице cpassed
         
        // Устанавливаем статус прошлой подписки в положение "неуспешно завершен"
        // @todo в будущем проверять результат выполнения этой функции и записывать его в лог
        // когда это станет возможно
        if ( $repeatid = $this->set_previos_cpassed_to_failed($cstream, $programmsbcid) )
        {// если ученик пересдавал предмет в этом потоке - то запомним это
            $cpassed->repeatid = $repeatid;
        }
                
        // вставляем новую запись в таблицу cpassed, тем самым подписывая ученика на поток
        return $this->dof->storage('cpassed')->insert($cpassed);
    }
    
    /** Устанавливает предыдущие подписки в статус "неуспешно завершено" если они были
     * 
     * @return bool
     * @param object $cstream - учебный поток, объект из таблицы cstreams
     * @param object $programmsbcid - id подписки на программу в таблице programmsbcs
     * 
     * @todo различать случаи ошибок и случаи когда просто нет предыдущей записи в cpassed
     */
    private function set_previos_cpassed_to_failed($cstream, $programmsbcid)
    {
        $select = 'programmsbcid = '.$programmsbcid.
                      ' AND cstreamid = '.$cstream->id.
                      " AND repeatid IS NULL AND status != 'canceled' ";
        $cpass = $this->dof->storage('cpassed')->get_records_select($select);
        if ( $cpass AND is_array($cpass) )
        {// если нашли запись - то она единственная
            $cpass = current($cpass);
        }else
        {// подписка не найдена - все нормально, ничего не надо делать
            return false;
        }
        
        // найдем наследника
        $successorid = $this->dof->storage('cpassed')->get_last_successor($cpass->id);
        if ( ! $successorid )
        {// нет наследника - все нормально
            return false;
        }
        // устанавливаем предыдущие подписки в статус "отменен" или "неуспешно завершен", если они есть,
        // используя для этого функцию выставления итоговых оценок
        // @todo проверить результат работы этой функции и записать в лог возможные ошибки, если они возникнут
        $this->dof->storage('cpassed')->set_final_grade($successorid);
        if (  $this->dof->storage('cpassed')->get_field($cpass->id,'status') == 'canceled' )
        {// родитель сменил статус на отменен - наследовать такого нельзя
            return false;
        }
        return $successorid;
    }
    
    /** установить статус новой созданной подписки в зависимости от статуса потока неа который она создается
     * 
     * @return bool
     * @param int $id - id подписки на поток в таблице cpassed
     * @param object $cstream - объект из таблицы cstreams. Поток на который была произведена запись
     */
    private function set_new_status_to_cpassed($id, $cstream)
    {
        switch ( $cstream->status )
        {// в зависимости от статуса потока меняем статус подписки
            case 'active':  return $this->dof->workflow('cpassed')->change($id, 'active');  break;
            case 'suspend': return $this->dof->workflow('cpassed')->change($id, 'suspend'); break;
            // подписка уже в нужном статусе
            case 'plan':    return true; break;
            // неизвестный или недопустимый статус потока
            default: return false;
        }
    }
    
    /** Подписать одного ученика на поток. 
     * 
     * @return bool
     * @param object $cstream - объект из таблицы cstreams
     * @param int $programmsbcid - id подписки ученика на программу в таблице programmsbcs
     * 
     * @todo перенести эту функцию в storage/cstreams
     */
    public function unenrol_student_from_cstream($cstream, $programmsbcid)
    {
        $programmsbcid = intval($programmsbcid);
        if ( ! is_object($cstream) OR ! $programmsbcid )
        {// неправильный формат данных
            return false;
        }
        if ( ! $cpassed = $this->dof->storage('cpassed')->
                get_records(array('cstreamid'=>$cstream->id, 'programmsbcid'=>$programmsbcid, 
                'status'=>array('plan', 'active', 'suspend'))) )
        {// не нашли ни одной подписки, значит ученик уже отписан
            return true;
        }
        
        $result = true;
        foreach ( $cpassed as $cpitem )
        {// отписываем всех учеников от потока, устанавливая подпискам статус "отменен"
            // @todo выяснить какой статус устанавливать: "отменен" или "успешно завершен"
            $result = $this->dof->workflow('cpassed')->change($cpitem->id, 'canceled') && $result;
        }
        return $result;
    }
    
    /** Получить id программы, к которой привязан указанный поток
     * 
     * @return int|bool - id программы, которой принадлежит поток или false в случае ошибки
     * @param int $cstreamid - id потока в таблице cstreams
     */
    private function get_cstream_programmid($cstreamid)
    {
        if ( ! $this->get($cstreamid) )
        {
            return false;
        }
        // получаем id программы из предмета, по которому проходит этот поток
        return $this->dof->storage('programmitems')->get_field($cstreamid->programmitemid, 'programmid');
    }
    
    /** Сохраняет имя предмето-потока в БД
     * @param int $cstreamid - id предмето-поток
     * @return bool true - если запись прошла успешно или false
     */
    public function get_cstreamname($eventcode, $mixedvar, $cstream = false)
    {
        //узнаем с объектами из каких таблиц мы имеем дело';
        //и найдем cstreamid
        if ( $cstream )
        {//пришли данные из таблицы cstream
            if ( $eventcode == 'delete' AND isset($mixedvar['old']->id) )
            {//это удаление- старый объект обязательно должен быть
                $oldid = $mixedvar['old']->id;
            }elseif ( $eventcode == 'insert' AND isset($mixedvar['new']->id) )
            {//это вставка - новая запись всегда должна быть
                $newid = $mixedvar['new']->id;
            }elseif ( $eventcode == 'update' AND isset($mixedvar['old']->id) 
                 AND isset($mixedvar['new']->id) )
            {//это обновление - оба объекта должны быть
                $newid = $mixedvar['new']->id;
                $oldid = $mixedvar['old']->id;
            }else
            {//но это не так
               return false; 
            }
        }else
        {//пришли данные из других таблиц';
            if ( $eventcode == 'delete' AND isset($mixedvar['old']->cstreamid) )
            {//это удаление - старый объект обязательно должен быть
                $oldid = $mixedvar['old']->cstreamid;
            }elseif ( $eventcode == 'insert' AND isset($mixedvar['new']->cstreamid) )
            {//это вставка - новая запись всегда должна быть
                $newid = $mixedvar['new']->cstreamid;
            }elseif ( $eventcode == 'update' AND isset($mixedvar['old']->cstreamid) 
                 AND isset($mixedvar['new']->id) )
            {//это обновление - оба объекта должны быть
                $newid = $mixedvar['new']->cstreamid;
                $oldid = $mixedvar['old']->cstreamid;
            }else
            {//но это не так
               return false; 
            }
        }
        //путь к файлу с методами формирования имени файла
        $path = $this->dof->plugin_path('storage','cstreams','/cfg/namestream.php');
        if ( ! file_exists($path) )
        {//если файла нет - сообщим об этом
            return false;
        }
        //файл есть - подключаем файл
        include_once($path);
        //создаем объект для генерации имени
        $csname = new block_dof_storage_cstreams_namecstream;
        switch ( $eventcode )
        {
            case 'insert':
            {
                return $csname->save_cstream_name($newid);
            }
            case 'update':
            {
                $old = $csname->save_cstream_name($oldid);
                $new = $csname->save_cstream_name($newid);
                return ($old AND $new);
            }
            case 'delete':
            {
                return $csname->save_cstream_name($oldid);
            }
        }
        return false;
    }
    
    /** Подставляет подразделение по умолчанию
     
     */
    private function default_departmentid($cstream, $mode = 'programmitems')
    {
        if ( ! is_object($cstream) )
        {// не объект - ошибка
            return false;
        }
        if ( empty($cstream->departmentid) )
        {// если подразделение у потока не указано
            // возьмем подразделение из предмета
            if ( $mode == 'programmitems' )
            {// только если сказано брать из предмета
                $cstream->departmentid = $this->dof->storage($mode)->
                          get_field($cstream->programmitemid,'departmentid');
            }
        }
        return $cstream;
    }
    
    /**
     * Возвращает id указанного количества активных самых давно-синхронизированных cstream`ов
     * 
     * @param int $limit Количество выбираемых записей
     * @return array of object Массив записей
     * @author Evgeniy Yaroslavtsev
     */
    public function get_old_sync_cstreams($limit)
    {
        return $this->get_records_select("status='active'", null,'lastgradesync ASC', 'id', 0, $limit);
    }
    
    /** Возвращает "путь" через запятые
     * @param int $id - id подразделения, которого возвращаем
     * @return string $path - путь подразделения через запятую
     * @access public
     */
    public function change_name_cstream($id)
    {   
        if ( is_object($id) )
        {
            $cstream = $id;   
        }elseif ( ! $cstream = $this->get($id) )
        {//не получили запись пользователя
            return '';
        } 
        // заменяем ',' на ', '
        return str_replace(',',', ', $cstream->name);
    }

    /** Получает список пустых потоков
     * @param integer $ageid - id периода, если не передан, то выбераем со всех периодов(null)
     * @param integer $programmid - id программы
     * @param integer $agenum - паралель(класс)
     * @param integer $cstreamdepid - id подразделения из потока(проедмето-класса)
     * @return unknown_type
     */
    public function get_empty_cstreams_full($programmid,$agenum,$cstreamdepid,$ageid=null)
    {
        // найдем все потоки программы для указанной параллели
        if ( empty($ageid) OR is_array($ageid) )
        {// если период не указан - выведем для всех
            $cstreams = $this->get_programm_age_cstreams($programmid,null,$agenum,$cstreamdepid);
        }else
        {// если указан - то для конкретного
            $cstreams = $this->get_programm_age_cstreams($programmid,$ageid,$agenum,$cstreamdepid);
        }
        // нет - не очень то и хотелось 
        if ( ! $cstreams )
        {
            return false;
        }

        // запишем все id  в 1 масси
        foreach ( $cstreams as $cstream )
        {// выберем id каждого предмета
            $ids[] = $cstream->id;
        }
        $ids = implode(',', $ids);
        // готовим запрос
        // таблицы
        $cs_st = $this->prefix().$this->tablename();
        $cpas_st= $this->prefix().$this->dof->storage('cpassed')->tablename();         
        $cslinks_st= $this->prefix().$this->dof->storage('cstreamlinks')->tablename();
        // САМ ЗАПРОС
        $select = "SELECT DISTINCT cs.* FROM ".$cs_st." as cs LEFT JOIN ".$cpas_st." as cpass ON cs.id=cpass.cstreamid 
                                        LEFT JOIN ".$cslinks_st." as link ON cs.id=link.cstreamid 
                                        WHERE (cpass.cstreamid IS NULL AND link.cstreamid IS NULL) AND cs.id IN (".$ids.")";

        $cstreams= $this->dof->storage('cstreams')->get_records_sql($select);

        // вернем пустые потоки
        return $cstreams;
        
    }

    /** Возвращете целую часть, если дробной нет
     * 
     * @param float $number - вещественное число
     */
    public function hours_int($number)
    {
        if ( ($number - floor($number)) > 0 )
        {// есть остаток - вернем с оттатком
            if ( ($number - floor($number)) == 0.25  )
            {// вывод если 0,25
                return $number;    
            }
            // вывод если 0,5
            return round($number,1);
        }
        // вернем целое число
        return floor($number);
        
    }
    
    /** Получить id учителя, который ведет поток
     * Функция создана после отказа от поля teacherid
     * @param int $cstreamid - id учебного потока в таблице cstreams
     * 
     * @return int_bool - id учителя в таблице persons или false
     * 
     */
    public function get_cstream_teacherid($cstreamid)
    {
        if ( ! $appointmentid = $this->get_field($cstreamid, 'appointmentid') )
        {// нет потока или назначения на должность
            return false;
        }
        if ( ! $eagreementid = $this->dof->storage('appointments')->
            get_field($appointmentid, 'eagreementid') )
        {// договор не существует или назначение не существует
            return false;
        }
        // возвращаем id персоны или false если ее не нашли
        return $this->dof->storage('eagreements')->get_field($eagreementid, 'personid');
    }
    
    /** 
     * Вычислить предварительный расчетный коэффициент для потока
     * 
     * @param object|id $cstream - Учебный процесс или его идентификатор
     * @param bool part - вернуть только значения без расчета
     * 
     * @return int|object|bool
     */
    public function calculation_salfactor($cstream, $part = false)
    {
        // Параметры
        $params = [];
        
        // Получение объекта учебного процесса
        if ( ! is_object($cstream) )
        {// Требуется получить учебный процесс
            $cstream = $this->get($cstream);
            if ( ! $cstream )
            {// Учебный процесс не получен
                return false;
            }
        }
        
        // Получение формулы рассчета часов
        $formula = $this->dof->storage('config')->get_config_value(
            'salfactors_calculation_formula', 
            'storage', 
            'schevents', 
            $cstream->departmentid
        );

        // Для подразделения из конфига
        $params['config_salfactor_department'] = $this->dof->storage('config')->get_config_value(
            'salfactor_department',
            'storage',
            'schevents',
            $cstream->departmentid
        );
        
        // Получение числа подписок на предмето-класс
        $num = $this->dof->storage('cpassed')->count_list(
            [
               'cstreamid' => $cstream->id, 
               'status'=>['plan','active','suspend']
            ]
        );
        
        // Замещающий зарплатный коэффициент потока
        $params['cstreams_substsalfactor'] = $cstream->substsalfactor;
        
        // Поправочный зарплатный коэффициент потока
        $params['cstreams_salfactor'] = $cstream->salfactor;
        
        // Замещающий зарплатный коэффициент потока
        $subsalfactor = round($cstream->substsalfactor, 2);
        $params['absence_substsalfactor'] = 1;
        if ( ! empty($subsalfactor) )
        {// Замещающий зарплатный коэффициент потока
            $params['absence_substsalfactor'] = 0;
        }
        
        // Поправочный зарплатный коэффициент предмета
        $params['programmitem_salfactor'] = $this->dof->storage('programmitems')->
            get_field($cstream->programmitemid, 'salfactor');
        
        // Поправочный зарплатный коэффициент подписок
        $params['programmsbcs_salfactor'] = $this->dof->storage('cpassed')->get_salfactor_programmsbcs($cstream->id);
            
        // Поправочный зарплатный коэффициент групп
        $params['agroups_salfactor'] = $this->dof->storage('cstreamlinks')->get_salfactor_agroups($cstream->id);
        
        // Число академических часов
        $params['ahours'] = 1;
        
        // Поправочный зарплатный коэффициент шаблона
        $params['schtemplates_salfactor'] = 0;
        
        // Фактор проведения события
        $params['schevents_completed'] = 1;
        
        // Фактор оплаты совместителям
        $params['payment_combination'] = 1;
        
        // Тип события
        $params['schevent_type'] = 1;
        
        // фактор отметки урока вовремя 
        // @todo доработать через настройки
        $params['schevents_completed_on_time'] = 1;
        
        // Число активных учеников
        $cpassedstatuses = $this->dof->workflow('cpassed')->get_meta_list('active');
        $params['count_active_cpassed'] = $this->dof->storage('cpassed')->count_list([
            'cstreamid' => $cstream->id,
            'status' => array_keys($cpassedstatuses)
        ]);
        
        // Число приостановленных учеников
        $params['count_suspend_cpassed'] = $this->dof->storage('cpassed')->count_list([
            'cstreamid' => $cstream->id,
            'status' => 'suspend'
        ]);
        
        // Общее число учеников
        $params['count_all_cpassed'] = $num;
        
        // Поправочный зарплатный коэффициент для общего числа студентов
        $params['config_salfactor_countstudents'] = $this->dof->storage('cpassed')->
            get_salfactor_count_students($params['count_all_cpassed'], $cstream->departmentid);
                
        // Поправочный зарплатный коэффициент для активных студентов
        $params['config_salfactor_countstudents_active'] = $this->dof->storage('cpassed')->
            get_salfactor_count_students($params['count_active_cpassed'], $cstream->departmentid);
        
        // Число присутствовавших учеников
        $params['count_presented_cpassed'] = 0;
        
        // Число отсутствовавших учеников
        $params['count_absented_cpassed'] = 0;
        
        // Событие является заменой
        $params['schevent_replaced'] = 0;
        
        // @todo Урок имеет статус "ученики временно отсутствуют"? статуса нет, пока только запомним, что это тоже нужно.
        
        // Групповое или индивидуальное событие
        $params['schevent_group'] = 0;
        $params['schevent_individual'] = 1;
        if ( $num > 1 )
        {
            $params['schevent_group'] = 1;
            $params['schevent_individual'] = 0;
        }
        
        if ( $part )
        {// Требуется вернуть данные для расчета
            return $params;
        }
        // Произвести расчет часов
        return $this->dof->modlib('calcformula')->calc_formula($formula, $params);   
    }
    
    /************************************************/
    /****** Функции для обработки заданий todo ******/
    /************************************************/
    
    /** Приостановить, а затем снова запустить все потоки подразделения
     * 
     * @param int $departmentid - id подразделения, потоки которого нужно пересинхронизировать
     * 
     * @return bool
     */
    protected function todo_resync_department_cstreams($departmentid, $personid)
    {
        // может потребоваться много времени
        dof_hugeprocess();
        
        $num = 0;
        
        // сообщаем о том, что начинаем todo
        $this->dof->mtrace(2, '(storage/cstreams:todo)Resyncronizing cstreams for departmentid='.$departmentid);
        $this->dof->mtrace(2, 'Collected. Starting resync.');
        $opt = array();
        $opt['personid'] = $personid;
        while ( $cstreams = $this->get_records_select(" status = 'active' AND departmentid = ".$departmentid, 
                         null,'', 'id', $num, 100) )
        {// сначала ищем все записи предмето-классов
            $num += 100;
            foreach ( $cstreams as $id=>$cstream )
            {// все активные предмето-классы собираем в один большой массив
                $this->dof->mtrace(2, 'Resyncing cstreamid='.$id); 
                // чтобы не скапливалось большое количество приостановленных потоков
                if ( ! $this->dof->workflow($this->code())->change($id, 'suspend', $opt) )
                {
                    $this->dof->mtrace(2, 'ERROR: cstreamid='.$id.' is not suspended');
                }
                if ( ! $this->dof->workflow($this->code())->change($id, 'active', $opt) )
                {
                    $this->dof->mtrace(2, 'ERROR: cstreamid='.$id.' is not activated');
                }
            }
        }
        
        $this->dof->mtrace(2, '(storage/cstreams:todo) DONE.');
        return true;
    }
    
    /** Приостановить, а затем снова запустить все потоки дисциплины
     * 
     * @param int $programmitemid - id дисциплины, потоки которой нужно пересинхронизировать
     * 
     * @return bool
     */
    protected function todo_resync_programmitem_cstreams($programmitemid,$personid)
    {
        // может потребоваться много времени
        dof_hugeprocess();
        
        $num = 0;
        
        // сообщаем о том, что начинаем todo
        $this->dof->mtrace(2, '(storage/cstreams:todo)Resyncronizing cstreams for programmitemid='.$programmitemid);
        $this->dof->mtrace(2, 'Collected. Starting resync.');
        $opt = array();
        $opt['personid'] = $personid;
        while ( $cstreams = $this->get_records_select(" status = 'active' AND programmitemid = ".$programmitemid, 
                         null,'', 'id', $num, 100) )
        {// сначала ищем все записи предмето-классов
            $num += 100;
            foreach ( $cstreams as $id=>$cstream )
            {// все активные предмето-классы собираем в один большой массив
                $this->dof->mtrace(2, 'Resyncing cstreamid='.$id); 
                // чтобы не скапливалось большое количество приостановленных потоков
                if ( ! $this->dof->workflow($this->code())->change($id, 'suspend', $opt) )
                {
                    $this->dof->mtrace(2, 'ERROR: cstreamid='.$id.' is not suspended');
                }
                if ( ! $this->dof->workflow($this->code())->change($id, 'active', $opt) )
                {
                    $this->dof->mtrace(2, 'ERROR: cstreamid='.$id.' is not activated');
                }
            }
        }
        
        $this->dof->mtrace(2, '(storage/cstreams:todo) DONE.');
        return true;
    }
    
    /* Останавливает все активные cpassed
     *  @param integer $itemid - id дисциплины
     */
    public function todo_itemid_active_to_suspend($itemid,$personid)
    {
        // времени понадобится много
        dof_hugeprocess();
        
        $cstreamids = array();
        $num = 0;
        // сообщаем о том, что начинаем todo
        $this->dof->mtrace(2, '(storage/cstreams:todo)Cstreams all suspend for programmitemid='.$itemid);
        $this->dof->mtrace(2, 'Collecting ids...');
        $opt = array();
        $opt['personid'] = $personid;
        while ( $cstreams = $this->get_records_select(' programmitemid='.$itemid.' AND status="active" ', null,'', 'id', $num, 100) )
        {// собираем все записи об изучаемых или пройденных курсах, которые надо перезапустить
            $num += 100;
            foreach ( $cstreams as $id=>$cstream )
            {
                $cstreamids[] = (int)$id;
            }
        }
        $this->dof->mtrace(2, 'Collected. Starting suspend cstreams.');
        
        // собрали все id cpassed которые нужно приостановить, а потом запустить
        foreach ( $cstreamids as $id )
        {
            $this->dof->mtrace(2, 'Suspend cstreamid='.$id); 
            // приостанавливаем и запускаем каждый cpassed по очереди
            // чтобы не скапливалось большое количество приостановленных cpassed
            if ( ! $this->dof->workflow($this->code())->change($id, 'suspend', $opt) )
            {
                $this->dof->mtrace(2, 'ERROR: cstreamid='.$id.' is not suspended');
            }
        }
        
        $this->dof->mtrace(2, '(storage/cstreams:todo) DONE.');
        
        return true;
    }
    
    /* Запускает все приостановленные cpassed
     *  @param integer $itemid - id дисциплины
     */
    public function todo_itemid_suspend_to_active($itemid,$personid)
    {
        
        // времени понадобится много
        dof_hugeprocess();
        
        $cstreamsids = array();
        $num = 0;
        // сообщаем о том, что начинаем todo
        $this->dof->mtrace(2, '(storage/cstreams:todo) Cstreams all active for programmitemid='.$itemid);
        $this->dof->mtrace(2, 'Collecting ids...');
        $opt = array();
        $opt['personid'] = $personid;
        while ( $cstreams = $this->get_records_select(' programmitemid='.$itemid.' AND status="suspend" ',null, '', 'id', $num, 100) )
        {// собираем все записи об изучаемых или пройденных курсах, которые надо перезапустить
            $num += 100;
            foreach ( $cstreams as $id=>$cstream )
            {
                $cstreamsids[] = (int)$id;
            }
        }
        $this->dof->mtrace(2, 'Collected. Starting active cstreams.');
        
        // собрали все id cpassed которые нужно приостановить, а потом запустить
        foreach ( $cstreamsids as $id )
        {
            $this->dof->mtrace(2, 'Active cstreamid='.$id); 
            // приостанавливаем и запускаем каждый cpassed по очереди
            // чтобы не скапливалось большое количество приостановленных cpassed
            if ( ! $this->dof->workflow($this->code())->change($id, 'active', $opt) )
            {
                $this->dof->mtrace(2, 'ERROR: cstreamid='.$id.' is not activated');
            }
        }
        
        $this->dof->mtrace(2, '(storage/cstreams:todo) DONE.');
        
        return true;
    }
    
    /**
     * Получение учебных процессов по ID учебного периода 
     * в указанном статусе с данными о программе и дисциплине
     * 
     * @param int $ageid ID учебного периода
     * @param string $status - Статус запрашиваемых учебных процессов
     * 
     * @return array - Массив учебных процессов
     */
    public function get_join_cstreams($ageid, $status)
    {
        $sql = 'SELECT cs.id, cs.ageid, cs.programmitemid, cs.status, cs.name,
                       pi.programmid, pi.name piname, p.name pname
                FROM {block_dof_s_cstreams} cs
                    JOIN {block_dof_s_programmitems} pi
                    ON cs.programmitemid=pi.id
                    JOIN {block_dof_s_programms} p
                    ON pi.programmid=p.id
                WHERE cs.ageid=? AND cs.status=?';
        return $this->dof->storage($this->code())->get_records_sql($sql, [$ageid, $status]);
    }
    
    /**
     * Получение уже активированных cstream'ов по id учебного периода, которые находятся в todo на активацию
     * @param int $ageid id учебного периода
     * @param array $cstreamsid массив id cstream'ов, которые находятся в todo
     */
    public function get_join_cstreams_by_id($ageid, $cstreamsid)
    {
        $cstreamsid = (array)$cstreamsid;
        $sql = 'SELECT cs.id, cs.ageid, cs.programmitemid, cs.status, cs.name,
                       pi.programmid, pi.name piname, p.name pname
                FROM {block_dof_s_cstreams} cs
                    JOIN {block_dof_s_programmitems} pi
                    ON cs.programmitemid=pi.id
                    JOIN {block_dof_s_programms} p
                    ON pi.programmid=p.id
                WHERE cs.ageid=? AND cs.status=?';
        $where = str_repeat('cs.id=? OR ', count($cstreamsid) - 1) . 'cs.id=?';
        $sql .= ' AND (' . $where . ')';
        $condition = [$ageid, 'active'];
        foreach($cstreamsid as $id)
        {
            $condition[] = $id;
        }
        return $this->dof->storage($this->code())->get_records_sql($sql, $condition);
    }
        
    /**************************************************/
    /********* Функции обработки todo-заданий *********/
    /**************************************************/
    
    /**
     * Активация учебных процессов
     * 
     * @param int $ageid идентификатор периода
     * @param stdClass $cstreams массив идентификаторов учебных процессов на активацию
     * @return boolean если активация прошла успешно true, если хотя бы один процесс не активировался false
     */
    public function activate_cstreams($ageid, $cstreamsid)
    {
        $res = true;
        $cstreamsid = (array)$cstreamsid;
        if( $cstreamsid )
        {
            // получим по переданным id запланированные cstream'ы (проверка на существование)
            $ids = $this->dof->storage($this->code())->get_records(['id' => $cstreamsid, 'status' => 'plan'], '', 'id');
            foreach($ids as $val)
            {// и активируем то, что реально есть
                $res = $this->dof->workflow($this->code())->change($val->id, 'active') && $res;
            }
        } else
        {
            return false;
        }
        return $res;
    }
    
    /**
     * Процесс работы с todo на активацю учебных процессов, 
     * если todo есть - обновляет ее, если нет - создает
     * 
     * @param array $cstreamids массив идентификаторов учебных процессов, $cstreamids['add'] - на добавление в todo, $cstreamids['del'] - на удаление из todo
     */
    public function add_todo_activate_cstreams($cstreamids)
    {
        global $DB, $USER;
        $cstreams = $ages = $lastids = $todoids = $clear_todoids = [];
        $add_cstreamids = $del_cstreamids = null;
        if( !empty($cstreamids) )
        {// если id cstream'ов переданы, разобьем их на те, которые надо активировать и те, активацию которых надо отменить
            foreach($cstreamids as $id => $v)
            {
                if( intval($v) == 1 )
                {
                    $add_cstreamids[] = $id;
                } else 
                {
                    $del_cstreamids[] = $id;
                }
            }
        }
        
        if( $add_cstreamids )
        {// если есть cstream'ы на активацию, разбираем их по периодам, проверяем что они запланированы
            $cstreams = $this->dof->storage($this->code())->get_records(['id' => $add_cstreamids, 'status' => 'plan'], '', 'id, ageid');
            foreach ( $cstreams as $cstream )
            {
                $ages[$cstream->ageid][] = $cstream->id;
            }
            foreach ( $ages as $ageid => $csids )
            {// у каждого периода ищем todo на активацию
                if ( $todo = $this->get_todo('activate_cstreams', $ageid) )
                {// если есть - заменяем в ней данные
                    $todoids = unserialize($todo->mixedvar)->id;
                    $dataobject = new stdClass();
                    $dataobject->id = $todo->id;
                    $dataobject->mixedvar = new stdClass();
                    if( $del_cstreamids )
                    {// если есть cstream'ы на удаление, уберем их из массива в todo, затем добавим недостающие cstream'ы из переданных на активацию
                        // поймем какие cstream'ы в todo уже активированы и выкинем их
                        $clear_todoids = array_diff($todoids, array_diff($todoids, $csids, $del_cstreamids));
                        // выкинем из того, что осталось cstream'ы на отмену активации
                        $clear_todoids = array_diff($clear_todoids, $del_cstreamids);
                        // добавим новые cstream'ы на активацию
                        $dataobject->mixedvar->id = array_merge($clear_todoids, array_diff($csids, $clear_todoids));
                    } else 
                    {
                        $clear_todoids = array_diff($todoids, array_diff($todoids, $csids));
                        $dataobject->mixedvar->id = array_merge($clear_todoids, array_diff($csids, $clear_todoids));
                    }
                    $dataobject->mixedvar = serialize($dataobject->mixedvar);
                    $dataobject->tododate = time();
                    $dataobject->personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                    $DB->update_record('block_dof_todo', $dataobject);
                } else
                {// если нет - создадим todo
                    $dataobject = new stdClass();
                    $dataobject->id = $csids;
                    $this->dof->add_todo('storage', $this->code(), 'activate_cstreams', $ageid, $dataobject, 2, time());
                }
            }
        } else
        {// если нет cstream'ов на активацию
            if( $del_cstreamids )
            {// и есть запланированные cstream'ы на удаление
                $cstreams = $this->dof->storage($this->code())->get_records(['id' => $del_cstreamids, 'status' => 'plan'], '', 'id, ageid');
                foreach( $cstreams as $cstream )
                {// разобьем их по периодам
                    $ages[$cstream->ageid][] = $cstream->id;
                }
                foreach( $ages as $ageid => $csids )
                {// для каждого периода получим todo
                    if( $todo = $this->get_todo('activate_cstreams', $ageid) )
                    {// если todo есть
                        $lastids = array_diff(unserialize($todo->mixedvar)->id, $del_cstreamids);
                        if( empty($lastids) )
                        {// и cstream'ы в todo совпадают с переданными, то удаляем todo
                            $DB->delete_records('block_dof_todo', ['id' => $todo->id]);
                        } else 
                        {// если не совпадают, удалим из todo только переданные cstream'ы
                            $dataobject = new stdClass();
                            $dataobject->id = $todo->id;
                            $dataobject->mixedvar = new stdClass();
                            $dataobject->mixedvar->id = $lastids;
                            $dataobject->mixedvar = serialize($dataobject->mixedvar);
                            $dataobject->tododate = time();
                            $dataobject->personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                            $DB->update_record('block_dof_todo', $dataobject);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Получение todo
     * @param string $plugintype тип плагина
     * @param string $plugincode код плагина
     * @param string $todocode код задания
     * @param int $intvar дополнительный параметр для выполнения задания
     * @param int $exdate дата выполнения задания
     * @return mixed|stdClass|false
     */
    public function get_todo($todocode, $intvar, $exdate = 0)
    {
        global $DB;
        return $DB->get_record('block_dof_todo', [
            'plugintype' => 'storage',
            'plugincode' => $this->code(),
            'todocode' => $todocode,
            'intvar' => $intvar,
            'exdate' => $exdate
        ]);
    }
}
?>