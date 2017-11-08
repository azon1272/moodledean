<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean's Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://deansoffice.ru/>                                               //
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

// Подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/**
 * Справочник персоналий Деканата
 * 
 * @package    storage
 * @subpackage persons
 * @author     Alexey Djachenko
 * @copyright  2008
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_persons extends dof_storage implements dof_storage_config_interface
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /** 
     * Процесс установки плагина
     * 
     * @return bool - Результат установки
     */
    public function install()
    {
        // Базовая установка
        parent::install();
        
        // Создание персоны для текущего пользователя
        global $USER;
        if ($USER->id)
        {
            $person = new stdClass();
            $person->mdluser = $USER->id;
            $person->email = $USER->email;
            $person->lastname = $USER->lastname;
            $person->sync2moodle = 1;
            $person->departmentid = $this->dof->storage('departments')->get_default_id();
            $this->insert($person, true);
        }
        // Обновление прав доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /** 
     * Процесс обновлния плагина
     * 
     * @param string $oldversion - Версия установленного в системе плагина
     * 
     * @return boolean - Результат обновления
     */
    public function upgrade($oldversion)
    {
        global $CFG, $DB;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2012030700) 
        {//удалим enum поля
            // для поля noextend
            if ( $this->dof->moodle_version() <= 2011120511 )
            {
                $field = new xmldb_field('sync2moodle', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null, 'mdluser');
                $dbman->drop_enum_from_field($table, $field);
                // для поля gender
                $field = new xmldb_field('gender', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, 'unknown', 'dateofbirth');
                $dbman->drop_enum_from_field($table, $field);
            }
        }
        if ($oldversion < 2013040900)
        {// добавим поле birthadressid
            $field = new xmldb_field('birthaddressid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'adddate');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            $index = new xmldb_index('ibirthaddressid', XMLDB_INDEX_NOTUNIQUE, 
                     array('birthaddressid'));
            // добавляем индекс для поля
            if ( !$dbman->index_exists($table, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            $index = new xmldb_index('idepartmentid', XMLDB_INDEX_NOTUNIQUE, 
                     array('departmentid'));
            // добавляем индекс для поля
            if ( !$dbman->index_exists($table, $index) ) 
            {// индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        if ($oldversion < 2014041000)
        {// Новые поля: about, skype, phoneadd1/2/3, emailadd1/2/3
            $fields = array();
            $fields[] = new xmldb_field('about',     XMLDB_TYPE_TEXT, null,  null, null, null, null, 'departmentid');
            $fields[] = new xmldb_field('skype',     XMLDB_TYPE_CHAR, '32',  null, null, null, null, 'about');
            $fields[] = new xmldb_field('phoneadd1', XMLDB_TYPE_CHAR, '20',  null, null, null, null, 'skype');
            $fields[] = new xmldb_field('phoneadd2', XMLDB_TYPE_CHAR, '20',  null, null, null, null, 'phoneadd1');
            $fields[] = new xmldb_field('phoneadd3', XMLDB_TYPE_CHAR, '20',  null, null, null, null, 'phoneadd2');
            $fields[] = new xmldb_field('emailadd1', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'phoneadd3');
            $fields[] = new xmldb_field('emailadd2', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'emailadd1');
            $fields[] = new xmldb_field('emailadd3', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'emailadd2');
            foreach ($fields as $field)
            {
                if ( !$dbman->field_exists($table, $field) )
                {// поле еще не установлено
                    $dbman->add_field($table, $field);
                }
            }
            
            // Добавляем индексы для полей
            $indexes = array();
            $indexes[] = new xmldb_index('iemailadd1', XMLDB_INDEX_NOTUNIQUE, array('emailadd1'));
            $indexes[] = new xmldb_index('iemailadd2', XMLDB_INDEX_NOTUNIQUE, array('emailadd2'));
            $indexes[] = new xmldb_index('iemailadd3', XMLDB_INDEX_NOTUNIQUE, array('emailadd3'));
            $indexes[] = new xmldb_index('iskype', XMLDB_INDEX_NOTUNIQUE, array('skype'));
            foreach ($indexes as $index)
            {
                if ( !$dbman->index_exists($table, $index) ) 
                {// индекс еще не установлен
                    $dbman->add_index($table, $index);
                }
            }
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault()); 
    }
    
    /** 
     * Текущая версия плагина
     * 
     * @return string
     */
    public function version()
    {
        return 2016080500;
    }
    
    /**
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     *
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium_bcd';
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
        return 'persons';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
            'modlib' => [
                'ig'          => 2016031400,
                'widgets'     => 2016050500,
                'ama'         => 2016041900
            ],
            'storage' => [
                'config'      => 2012042500,
                'departments' => 2016012100,
                'addresses'   => 2015120700,
                'acl'         => 2012042500,
                'cov'         => 2014032000
            ]
        ];
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
     *         true - если плагин можно устанавливать
     *         false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion=0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return [
            'modlib' => [
                'ig'          => 2016031400,
                'widgets'     => 2016050500,
                'ama'         => 2016041900
            ],
            'storage' => [
                'config'      => 2012042500,
                'departments' => 2016012100,
                'addresses'   => 2015120700,
                'acl'         => 2012042500,
                'cov'         => 2014032000
            ]
        ];
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(
                // Запросы на удаление информации о персоне
                array('plugintype'=>'im', 'plugincode'=>'employees', 'eventcode'=>'delete_person_info'),
                array('plugintype'=>'im', 'plugincode'=>'employees', 'eventcode'=>'delete_person')
        );
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
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
    
    /** 
     * Обработать событие
     * 
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
        // Ловим запрос c интерфейса сотрудников
        if ( $gentype === 'im' AND $gencode === 'employees' )
        {
            switch($eventcode)
            {
                // Запрос на формирование таблицы затрагиваемых записей при удалении персоны
                case 'delete_person_info' :
                    // Возвращаем строку c информацией
                    return $this->get_delete_person_info($intvar);
                // Запрос на проведение действий, сопутствующих удалению персоны
                case 'delete_person' :
                    // Переводим записи в пассивный статус
                    return $this->delete_person($intvar);
            }
        }
        return true;
    }
    
    /** 
     * Запустить обработку периодических процессов
     * 
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
    
    /** 
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function todo($code,$intvar,$mixedvar)
    {
        if ($code === 'recalcsortnameall')
        {
            // Нас попросили провести "очистку"
            return $this->remake_all_sortname();
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
        return 'block_dof_s_persons';
    }
    
    /** Получить название объекта из хранилища для отображения или составления ссылки
     * Этот метод переопределяется для тех хранилищ, объекты в которых не имеют поля name
     * @todo дописать алгоритм работы с дополнительными полями
     * 
     * @param int|object - id объекта или сам объект
     * @param array $fields[optional] - список дополнительных полей, которые будут выведены после названия
     * 
     * @return string название объекта
     */
    public function get_object_name($id, array $fields=array())
    {
        if ( is_object($id) )
        {
            $obj = $id;
        }elseif ( is_int_string($id) )
        {
            if ( ! $obj = $this->get($id) )
            {
                dof_debugging(get_class($this).'::get_object_name() object not found!', DEBUG_DEVELOPER);
                return '[[object_not_found!]]';
            }
        }else
        {
            dof_debugging(get_class($this).'::get_object_name() wrong parameter type!', DEBUG_DEVELOPER);
            return '';
        }
        
        return $this->get_fullname($obj);
    }
    

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************  
    
    /** 
     * Получить список параметров для проверки прав
     * 
     * @param string $action - Выполняемое действие
     * @param int $objectid - ID объекта, над которым совершается действие
     * @param int $personid - ID персоны, которое совершает действие
     * @param int $depid - ID подразделения
     * 
     * @return stdClass - Список параметров
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = (int)$objectid;
        
        if ( is_null($depid) )
        {// Установка текущего подразделения
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( $objectid )
        {// Указан объект для проверки прав
            // Переопределение подразделения
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
     *  a[] = array( 'код полномочия' => array('список ролей'),
     *               'roles'          => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        $a['view']          = array('roles'=>array('manager','methodist'));
        $a['view/parent']   = array('roles'=>array('manager', 'parent'));
        $a['view/sellerid'] = array('roles'=>array('manager', 'parent'));        
        $a['viewpersonal']  = array('roles'=>array('manager','methodist'));
        $a['viewabout']     = array('roles'=>array('manager','methodist'));
        $a['edit']          = array('roles'=>array('manager'));
        // право изменять информацию в договоре для законного представителя (если понадобится)
        $a['edit/parent']   = array('roles'=>array('manager', 'parent'));
        $a['use']           = array('roles'=>array('manager','methodist'));
        $a['create']        = array('roles'=>array('manager'));
        $a['delete']        = array('roles'=>array());
        // Это право будет находится здесь если только мы не решим создать workflow
        // для плагина persons 
        $a['changestatus'] = array('roles'=>array(''));
        // право персоне назначать комплект(выдать комплект)
        $a['give_set'] = array('roles'=>array('manager'));
        // право редактировать time_zone
        $a['edit_timezone'] = array('roles'=>array('manager'));
        //право вручную синхронизировать персону с пользователем Moodle
        $a['edit:sync2moodle'] = array('roles'=>array(''));
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
        // максимально разрешенное количество объектов этого типа в базе
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
    
    /** 
     * Вставляет запись в таблицу(ы) плагина 
     * 
     * @param object dataobject 
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function insert($dataobject, $quiet=NULL)
    {
        // Добавление времени создания персоны
        $dataobject->adddate = time();
        if ( ! isset($dataobject->status) )
        {// Установка статуса по-умолчанию
            $dataobject->status = 'normal';            
        }
        if ( ! isset($dataobject->sortname) )
        {// Установка поля сортировки по-умолчанию
            $dataobject->sortname = $this->make_sortname($dataobject);
            if ( ! $dataobject->sortname )
            {// Поле для сортировки не сформировано
                unset($dataobject->sortname);
            }
        }
        return parent::insert($dataobject, $quiet);
    }
    
    /**
     * Сохранить персону в системе
     *
     * @param string|stdClass|array $persondata - Данные персоны(email или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID персоны в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($persondata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($persondata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
        
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $person = $this->update($normalized_data);
            if ( empty($person) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_person');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'persons', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $personid = $this->insert($normalized_data);
            if ( ! $personid )
            {// Добавление не удалось
                throw new dof_exception_dml('error_save_person');
            } else
            {// Добавление удалось
                $this->dof->send_event('storage', 'persons', 'item_saved', (int)$personid);
                return $personid;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных персоны
     * 
     * Формирует объект персоны на основе переданных данных. В случае критической ошибки 
     * или же если данных недостаточно, выбрасывает исключение.
     * 
     * @param string|stdClass|array $persondata - Данные персоны(email или комплексные данные)
     * @param array $options - Опции работы
     * 
     * @return stdClass - Нормализовализованный Объект персоны
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($persondata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($persondata) || is_array($persondata) )
        {// Комплексные данные
            $persondata = (object)$persondata;
        } elseif ( is_string($persondata) )
        {// Передан email
            $persondata = new stdClass();
            $persondata->email = $persondata;
        } else 
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
        
        // Нормализация идентификатора
        if ( isset($persondata->id) && $persondata->id < 1)
        {
            unset($persondata->id);
        }
        // Проверка входных данных
        if ( empty($persondata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        if ( ( ! isset($persondata->id) || $persondata->id < 1 ) &&
             ( ! isset($persondata->email) || empty($persondata->email) )
           )
        {// Невозможно определить персону
            throw new dof_exception_dml('create_without_email');
        }
        if ( isset($persondata->id) )
        {// Проверка на существование
            if ( ! $this->get($persondata->id) )
            {// Персона не найдена
                throw new dof_exception_dml('person_not_found');
            }
        }
        
        // Создание объекта для сохранения
        $saveobj = clone $persondata;
        
        // Обработка входящих данных и построение объекта персоны
        if ( isset($saveobj->id) && $this->is_exists($saveobj->id) )
        {// Персона уже содержится в системе
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
            unset($saveobj->adddate);
            unset($saveobj->sortname);
        } else
        {// Новая персона
            
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            if ( ! isset($saveobj->firstname) || empty($saveobj->firstname) )
            {// Установка имени по-умолчанию
                $saveobj->firstname = '';
            }
            if ( ! isset($saveobj->lastname) || empty($saveobj->lastname) )
            {// Установка фамилии по-умолчанию
                $saveobj->lastname = '';
            }
            if ( ! isset($saveobj->middlename) || empty($saveobj->middlename) )
            {// Установка отчества по-умолчанию
                $saveobj->middlename = '';
            }
            if ( ! isset($saveobj->preferredname) || empty($saveobj->preferredname) )
            {// Установка префикса имени по-умолчанию
                $saveobj->preferredname = null;
            }
            if ( ! isset($saveobj->email) || empty($saveobj->email) )
            {// Установка email по-умолчанию
                $saveobj->email = '';
            }
            if ( ! isset($saveobj->gender) || empty($saveobj->gender) )
            {// Установка пола по-умолчанию
                $saveobj->gender = 'unknown';
            }
            if ( ! isset($saveobj->dateofbirth) || empty($saveobj->dateofbirth) )
            {
                $saveobj->dateofbirth = null;
            }
            if ( ! isset($saveobj->departentid) || empty($saveobj->departentid) )
            {// Подразделение по-умолчанию
                $saveobj->departentid = $this->dof->storage('departments')->get_default_id();
            }
            if ( ! isset($saveobj->sync2moodle) )
            {// Синхронизация по-умолчанию
                $saveobj->sync2moodle = 1;
            }
            if ( ! isset($saveobj->mdluser) )
            {// Синхронизируемый пользователь по-умолчанию
                $saveobj->mdluser = null;
            }
            if ( ! isset($saveobj->addressid) )
            {// Адрес пользователя по-умолчанию
                $saveobj->addressid = 0;
            }
            if ( ! isset($saveobj->passportaddrid) )
            {// Адрес по удостоверению личности по-умолчанию
                $saveobj->passportaddrid = 0;
            }
            if ( ! isset($saveobj->birthaddressid) )
            {// Адрес рождения по-умолчанию
                $saveobj->birthaddressid = 0;
            }
        
            // АВТОМАТИЧЕСКИ ГЕНЕРИРУЕМЫЕ ПОЛЯ
            if ( ! $this->dof->plugin_exists('workflow', 'persons') )
            {// Плагин статусов персон не активен, установка статуса по-умолчанию
                $saveobj->status = 'normal';
            } else
            {// Статус назначается в плагине статусов
                unset($saveobj->status);
            }
            // Установка значения для сортировки
            $saveobj->sortname = $this->make_sortname($saveobj);
            // Установка даты создания
            $saveobj->adddate = time();
        }
            
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->gender) )
        {// Указан пол сохраняемой персоны
            $saveobj->gender = strtolower((string)$saveobj->gender);
            switch ( $saveobj->gender )
            {
                case 'male' :
                case 'm' :
                case 'м' :
                case 'М' :
                case '1' :
                    $saveobj->gender = 'male';
                    break;
                case 'female' :
                case 'f' :
                case 'ж' :
                case 'Ж' :
                case '2' :
                    $saveobj->gender = 'female';
                    break;
                default :
                    $saveobj->gender = 'unknown';
                    break;
            }
        }
        // Нормализация даты рождения
        if ( isset($saveobj->dateofbirth) && $saveobj->dateofbirth != null )
        {
            if ( ! is_int_string($saveobj->dateofbirth) )
            {// Время представлено в строковом формате
                $saveobj->dateofbirth = strtotime($saveobj->dateofbirth);
            }
        }
        // Нормализация данных о синхронизации
        if ( isset($saveobj->sync2moodle) )
        {
            $saveobj->sync2moodle = (int)$saveobj->sync2moodle;
        }
        if ( isset($saveobj->mdluser) )
        {
            if ( $saveobj->mdluser !== null )
            {
                $saveobj->mdluser = (int)$saveobj->mdluser;
            }
        }
        // Нормализация адресов
        if ( isset($saveobj->addressid) )
        {
            $saveobj->addressid = (int)$saveobj->addressid;
        }
        if ( isset($saveobj->passportaddrid) )
        {
            $saveobj->passportaddrid = (int)$saveobj->passportaddrid;
        }
        if ( isset($saveobj->birthaddressid) )
        {
            $saveobj->birthaddressid = (int)$saveobj->birthaddressid;
        }
        
        // ВАЛИДАЦИЯ ДАННЫХ
        // Проверки email
        if ( isset($saveobj->email) )
        {
            // Валидация email
            if ( ! $this->dof->modlib('ama')->user(false)->validate_email($saveobj->email) )
            {
                throw new dof_exception_dml('notvalid_email');
            }
            // Уникальность email
            $currentid = 0;
            if ( isset($saveobj->id) )
            {
                $currentid = (int)$saveobj->id;
            }
            if ( ! $this->is_email_unique($saveobj->email, $currentid) )
            {
                throw new dof_exception_dml('notunique_email');
            }
        }
        
        return $saveobj;
    }
    
    /**
     * Реализация механизма импорта данных с поддержкой формирования отчета.
     * 
     * Производит попытку поиска объекта на основе переданных данных и если находит - 
     * возвращает этот объект.
     * Отдельно обрабатывает случаи, когда объект не был найден или 
     * когда было найдено несколько объектов. 
     *
     * @param mixed $data - Набор данных, на основе которых производится поиск
     *          В зависимости от типа данные интерпретируются следующим образом:
     *              int - ID персоны
     *              string - email персоны
     *              array|stdClass - Комплексные данные
     *          При передачи комплексных данных можно также указать данные по 
     *          зависимым элементам, что приведет к их обработке.
     *              Пример: $data->departmentid->code = Depcode
     * @param array|null $report - Ссылка для формирования отчета по объекту
     *          Стандарт отчета:
     *          'action'         (string) - Действие, которое произведено с объектом (get|save)
     *          'error'          (string) - Ошибка для отображения пользователю
     *          'notice'         (string) - Уведомление для отображения пользователю
     *          'additional' (array|null) - Дополнительные данные по отчету
     *          'errortype'      (string) - Код ошибки
     *          'object'  (stdClass|null) - Объект хранилища
     *          'subreports'      (array) - Массив отчетов по работе над зависимыми элементами
     * @param int $options - Массив дополнительных опций обработки
     *      ['simulation'] (bool) Процесс симуляции, в этом режиме не происходит добавление элементов в БД,
     *                            но формируются все имеющиеся ошибки процесса. Полезно для предварительной
     *                            валидации данных перед добавлением в систему
     *      ['departmentid'] (int) - Значению по-умолчанию для объекта, если в нем не указано подразделение.
     *      ['notexist_action] (string) - Действие , если запись на основе переданных данных
     *                            не найдена в системе. Варианты действий: error|create
     *                            По-умолчанию вариант error
     *      ['multiple_action] (string) - Действие , если найдено несколько записей
     *                                    на основе переданных данных
     *                            Варианты действий: error|first|last
     *                            По-умолчанию вариант error
     *      ['reportcode'] (string) - Код отчета. По-умолчанию person.
     *
     * @return stdClass - Объект, полученный на основе переданных данных
     */
    public function import($data, &$report = null, $options = [])
    {
        // НОРМАЛИЗАЦИЯ ВХОДЯЩИХ ДАННЫХ
        if ( ! empty($data) )
        {// Данные переданы
            // Нормализация данных по объекту
            if ( is_int($data) )
            {// ID объекта
                $data = ['id' => $data];
            } elseif ( is_string($data) )
            {// Email объекта
                $data = ['email' => $data];
            } else 
            {// Комплексные данные
                $data = (array)$data;
            }
        }
        // НОРМАЛИЗАЦИЯ ОПЦИЙ ИМПОРТА
        if ( ! isset($options['simulation']) )
        {// Процесс симуляции отключен
            $options['simulation'] = false;
        }
        if ( ! isset($options['departmentid']) )
        {// Установка подразделения по-умолчанию
            $options['departmentid'] = $this->dof->storage('departments')->get_default_id();
        }
        if ( ! isset($options['notexist_action']) )
        {// Действие по-умолчанию, если объект не найден
            $options['notexist_action'] = 'error';
        }
        if ( ! isset($options['multiple_action']) )
        {// Действие по-умолчанию, если найдено несколько объектов
            $options['multiple_action'] = 'error';
        }
        if ( ! isset($options['reportcode']) || empty($options['reportcode']) )
        {// Код отчета по-умолчанию
            $options['reportcode'] = 'person';
        }
        
        // ОПРЕДЕЛЕНИЕ БАЗОВЫХ ДАННЫХ ДЛЯ ПРОЦЕССА ИМПОРТА
        $importobject = null;
        // Текущий отчет
        $subreport = [
            'action'     => 'get',
            'error'      => null,
            'notice'     => null,
            'additional' => null,
            'errortype'  => null,
            'object'     => null,
            'subreports' => []
        ];
        
        if ( empty($data) )
        {// Данные не переданы
            $subreport['error'] = 'error_import_empty_data';
            $subreport['errortype'] = 'data_validation';
        } else 
        {// Данные определены
            // ПОИСК ОБЪЕКТОВ ПО УНИКАЛЬНЫМ ПОЛЯМ ПЕРЕДАННЫХ ДАННЫХ
            $params = [];
            $objects = [];
            // Формирование статусов для фильтрации
            if ( $this->dof->plugin_exists('workflow', 'persons') )
            {
                $statuses = (array)$this->dof->workflow('persons')->get_meta_list('real');
                $statuses = array_keys($statuses);
            } else
            {
                $statuses = ['normal', 'archive'];
            }
            $params['status'] = $statuses;
            // Поиск персон по ID
            if ( isset($data['id']) && ! empty($data['id']) )
            {
                $params['id'] = $data['id'];
                $persons = $this->get_records($params);
                $objects = $persons + $objects;
                unset($params['id']);
            }
            // Поиск персон по email
            if ( isset($data['email']) && ! empty($data['email']) )
            {
                $params['email'] = $data['email'];
                $persons = $this->get_records($params);
                $objects = $persons + $objects;
                unset($params['email']);
            }
            
            // ОБРАБОТКА НАЙДЕННЫХ ОБЪЕКТОВ
            if ( empty($objects) )
            {// Объекты не найдены
                
                // Исполнение действия в зависимости от настроек
                switch ( $options['notexist_action'] )
                {
                    // Попытка создать персону на основе переданных данных
                    case 'create' :
                        
                        $subreport['action'] = 'save';
                        // Нормализация подразделения
                        if ( ! isset($data['departmentid']) )
                        {
                            $data['departmentid'] = $options['departmentid'];
                        }
                        if ( $options['simulation'] )
                        {// Симуляция процесса сохранения персоны
                            try
                            {
                                $importobject = $this->normalize($data, $options);
                            } catch ( dof_exception_dml $e )
                            {// Ошибка проверки персоны
                                $subreport['error'] = 'error_save_'.$e->errorcode;
                                $subreport['errortype'] = 'create';
                            }
                        } else
                        {// Сохранение элемента
                            try
                            {
                                $id = $this->save($data, $options);
                                $importobject = $this->get((int)$id);
                            } catch ( dof_exception_dml $e )
                            {// Ошибка сохранения персоны
                                $subreport['error'] = $e->errorcode;
                                $subreport['errortype'] = 'create';
                            }
                        }
                        break;
                    case 'error' :
                    default :
                        $subreport['error'] = 'error_import_person_not_found';
                        $subreport['errortype'] = 'notexist';
                    break;
                }
            } 
            if ( count($objects) > 1 )
            {// Найдено несколько персон
                // Исполнение действий в соответствии с настройками
                switch ( $options['multiple_action'] )
                {
                    // Вернуть первый элемент массива
                    case 'first' :
                        $importobject = reset($objects);
                        break;
                    // Вернуть последний элемент массива
                    case 'last' :
                        $importobject = end($objects);
                        break;
                    // Ошибка
                    case 'error' :
                    default :
                        $subreport['error'] = 'error_import_person_multiple_found';
                        $subreport['errortype'] = 'multiple';
                        $subreport['additional'] = array_keys($objects);
                        break;
                }
            } 
            if ( count($objects) == 1 )
            {// Объект найден
                $importobject = current($objects);
            }
            
            // ЗАВЕРШЕНИЕ ОБРАБОТКИ
            $subreport['object'] = $importobject;
        }
        
        if ( $report !== null )
        {// Требуется отчет о работе
            $report[$options['reportcode']] = $subreport;
        }
        unset($subreport);

        return $importobject;
    }
    
    /** Обновляет запись данными из объекта.
     * Отсутствующие в объекте записи не изменяются.
     * Если id передан, то обновляется запись с переданным id.
     * Если id не передан обновляется запись с id, который передан в объекте
     * @param object dataobject - данные, которыми надо заменить запись в таблице 
     * @param int id - id обновляемой записи
     * @param bool quiet - не генерировать событий
     * @return boolean true если обновление прошло успешно и false во всех остальных случаях
     * @access public
     * @todo сделать проверку на mdluser
     */
    public function update($dataobject,$id = NULL,$quiet=false)
    {
        if (!isset($dataobject->sortname))
        {
            // Имя для сортировки формируем сами
            if (!$dataobject->sortname = $this->make_sortname($dataobject))
            {
                // Ничего нет
                unset($dataobject->sortname);
            }
        }
        // Вызываем исходный метод обновления
        return parent::update($dataobject,$id,$quiet);
    }
    
    /** Получить объект по moodleid
     * @param int $userid - id пользователя в moodle
     * @return object - данные персоны
     * @access public
     */
    public function get_bu($userid = NULL,$create=false)
    {
        global $USER;
        if ( is_null($userid) )
        {    // Берем id текущего пользователя
            $userid = $USER->id;
        }
        if ( ! $userid )
        {// юзера не пепредали
            return false;
        }
        if ( $person = $this->get_record(array('mdluser'=>intval($userid))))
        {
            return $person;
        }elseif ( $create )
        {   
            //var_dump($USER);
            // Нас попросили создать персону по текущему пользователю
            if ( $userid == $USER->id )
            {// но только если она соответствует текущему пользователю
                if ( $id = $this->reg_moodleuser($USER) )
                {
                    // Возвращаем объект
                    return $this->get($id);
                }
            }
        }
        return false;
    }
    
    /** Получить объект по moodleid
     * @param int $muserid - id пользователя в moodle
     * @return object - данные персоны
     * @access public
     */
    public function get_by_moodleid($muserid = NULL,$create=false)
    {
        return $this->get_bu($muserid,$create);
    }
    
    /** Получить объект по moodleid
     * @param int $muserid - id пользователя в moodle
     * если не указан берется $USER->id
     * @return mixed int id персоны или bool false
     * @access public
     */
    public function get_by_moodleid_id($muserid = NULL,$create=false)
    {
        if (is_object($person = $this->get_by_moodleid($muserid,$create)))
        {
            return $person->id;
        }
        
        return false;
    }
    
    /** Получить список синхронизируемых персон
     * @return array - список персон, требующих синхронизации
     * @access public
     */
    public function get_list_synced()
    {
        return $this->get_records(array('sync2moodle' => 1),'sortname ASC');
    }
    
    /** Получить список неудаленных
     * @return array - список персон
     * @access public
     */
    public function get_list_normal($depid = false,$limitfrom='0',$limitnum='0')
    {
        if ( $depid )
        {// только для переданного подразделения
            return $this->get_records(array('status'=>'normal','departmentid'=>$depid),'sortname ASC','*',$limitfrom,$limitnum);
        }
        return $this->get_records(array('status'=>'normal'),'sortname ASC','*',$limitfrom,$limitnum);
    }
    
   /** Получить список персон по запрашиваемой фамилии
     * @param $query - фамилия, которую ищем
     * @param $depid - id записи из таблицы departments
     * @param $children - сообщает, использовать ли дочерние подразделения
     * @return array - список персон
     * @access public
     */
    public function get_list_search_lastname($query, $depid = false, $children = false, $limitfrom=0, $limitnum=0)
    {
        if ( $depid )
        {// только для переданного подразделения
            if( $children )
            {
                if ( $department = $this->dof->storage('departments')->get($depid) AND $childids = 
                        $this->dof->storage('departments')->get_records_select("path LIKE '".$department->path."/%'") )
                {
                    $depidstr = $depid;
                    foreach($childids as $key=>$dep)
                    {
                        $depidstr .= ','.$key;
                    }
                    return $this->get_records_select("lastname LIKE '{$query}%' AND departmentid IN (".$depidstr.")", null,'sortname ASC','*', $limitfrom, $limitnum);
                }
            }
            return $this->get_records_select("lastname LIKE '{$query}%' AND departmentid=".$depid, null,'sortname ASC','*', $limitfrom, $limitnum);
        }
        return $this->get_records_select("lastname LIKE '{$query}%'",null, 'sortname ASC','*', $limitfrom, $limitnum);
    }
    
    /** 
     * Получить список персон на основе переданной строки
     * 
     * Ведет поиск, интерпретируя переданные данные как ФИО, Email, 
     * или же идентификаторы пользователя
     * 
     * @param string $query - Абстрактные данные персоны
     * @param int $depid - ID подразделения, в котором производится поиск персон
     * @param bool $children - Включение поиска в дочерних подразделениях
     * @param $limitfrom - Смещение
     * @param $limitnum - Лимит записей
     * 
     * @return array - Список найденных персон
     */
    public function get_list_search($query, $depid = 0, $children = false, $limitfrom = 0, $limitnum = 0)
    {
        $sql = '';
        $params = [];
        
        $sql .= ' ( '.$this->get_sql_like('lastname', ':lastname').' ) OR';
        $params['lastname'] = (string)'%'.$query.'%';
        $sql .= ' ( '.$this->get_sql_like('firstname', ':firstname').' ) OR';
        $params['firstname'] = (string)'%'.$query.'%';
        $sql .= ' ( '.$this->get_sql_like('middlename', ':middlename').' ) OR';
        $params['middlename'] = (string)'%'.$query.'%';
        $sql .= ' ( '.$this->get_sql_like('email', ':email').' ) OR';
        $params['email'] = (string)'%'.$query.'%';
        $sql .= ' mdluser = :mdluser OR id = :id ';
        $params['mdluser'] = (int)$query;
        $params['id'] = (int)$query;
                    
        if ( $depid )
        {// Фильтрация по подразделению
            $filter = [];
            if( $children )
            {// Фильтрация с учетом дочерних подразделений
                $filter = (array)$this->dof->storage('departments')->get_departments($depid);
            }
            $filter[$depid] = $depid;
            
            $filter = array_keys($filter);
            $filter = implode(',', $filter);
            $sql = '( '.$sql.' ) AND departmentid IN ('.$filter.')';
        }
        return $this->get_records_select($sql, $params, 'sortname ASC', '*', $limitfrom, $limitnum);
    }

    /** 
     * Получить список идентификаторов персон по списку условий
     * 
     * @param stdClass $conditions - Параметры поиска пользователей
     * @param int $depid - ID подразделения
     * @param int $limitfrom - Смещение
     * @param int $limitnum - Требуемое число записей
     * @param bool $onlycount - Вернуть число найденных записей вместо идентификаторов персон
     * 
     * @return array|int - Cписок идентифкаторов персон или количество найденных пользователей
     */
    public function get_list_extendedsearch($conditions, $depid = 0, $limitfrom = 0, $limitnum = 0, $onlycount = false)
    {
        $conds = array("1=1");
        $tbllhistory = $this->prefix() . $this->dof->storage('learninghistory')->tablename();
        if ( isset($conditions->lastname) && $conditions->lastname != "" )
        {
            $conds[] = sprintf("per.lastname LIKE '%s%%'", ($conditions->lastname));
        }
        if ( isset($conditions->firstname) && $conditions->firstname != "" )
        {
            $conds[] = sprintf("per.firstname LIKE '%s%%'", ($conditions->firstname));
        }
        if ( isset($conditions->middlename) && $conditions->middlename != "" )
        {
            $conds[] = sprintf("per.middlename LIKE '%s%%'", ($conditions->middlename));
        }
        if ( isset($conditions->contractnum) && $conditions->contractnum != "" )
        {
            $conds[] = sprintf("c.num='%s'", ($conditions->contractnum));
        }
        if ( isset($conditions->phone) && $conditions->phone != "" )
        {
            $conds[] = sprintf("(per.phonehome LIKE '%%%1\$s%%'"
                        . " OR per.phonework LIKE '%%%1\$s%%'"
                        . " OR per.phonecell LIKE '%%%1\$s%%')", $conditions->phone);
        }
        if ( isset($conditions->email) && $conditions->email != "" )
        {
            $conds[] = sprintf("(per.email='%1\$s')", $conditions->email);
        }
        if ( isset($conditions->programmid) && $conditions->programmid != 0 )
        {
            $conds[] = sprintf("pbcs.programmid=%d", $conditions->programmid);
        }
        if ( isset($conditions->currentageid) && $conditions->currentageid != 0 )
        {
            $conds[] = sprintf("(SELECT ageid"
                            . " FROM {$tbllhistory}"
                            . " WHERE programmsbcid=pbcs.id"
                            . " ORDER BY changedate DESC LIMIT 1)=%d", $conditions->currentageid);
        }
        if ( isset($conditions->startageid) && $conditions->startageid != 0 )
        {
            $conds[] = sprintf("(SELECT ageid"
                            . " FROM {$tbllhistory}"
                            . " WHERE programmsbcid=pbcs.id"
                            . " ORDER BY changedate ASC LIMIT 1)=%d", $conditions->startageid);
        }
        if ( isset($conditions->currentagenum) && $conditions->currentagenum != 0 )
        {
            $conds[] = sprintf("pbcs.agenum=%d", $conditions->currentagenum);
        }
        if ( isset($conditions->curatorid) && $conditions->curatorid > 0 )
        {
            $conds[] = sprintf("c.curatorid = %d", $conditions->curatorid);
        }
        if ( isset($conditions->pbcsstatus) && count($conditions->pbcsstatus) > 0 )
        {
            // Собираем массив из значений статусов
            $pbcsstatuses = array_keys($conditions->pbcsstatus);

            if ( in_array("0", $pbcsstatuses) )
            {// Если надо найти человека без подписки
                if ( count($pbcsstatuses) > 1 )
                { // В совокупности с другими статусами
                    unset($pbcsstatuses[array_search(0, $pbcsstatuses)]);
                    $conds[] = sprintf("(pbcs.status IN (%s) OR pbcs.status is null)", "'" . implode("','", $pbcsstatuses) . "'");
                } else
                { // Учитываем только статусы без подписки
                    $conds[] = "pbcs.status is null";
                }
            } elseif ( count($pbcsstatuses) > 0 )
            {
                $conds[] = sprintf("pbcs.status IN (%s)", "'" . implode("','", $pbcsstatuses) . "'");
            }
        }
        if ( isset($conditions->contractstatus) && count($conditions->contractstatus) > 0 )
        {
            // Собираем массив из значений статусов
            $contractstatuses = array_keys($conditions->contractstatus);

            if ( in_array("0", $contractstatuses) )
            {// Если надо найти человека без договора
                if ( count($contractstatuses) > 1 )
                { // В совокупности с другими статусами
                    unset($contractstatuses[array_search(0, $contractstatuses)]);
                    $conds[] = sprintf("(c.status IN (%s) OR c.status is null)", "'" . implode("','", $contractstatuses) . "'");
                } else
                { // Учитываем только статусы без договора
                    $conds[] = "c.status is null";
                }
            } elseif ( count($contractstatuses) > 0 )
            {
                $conds[] = sprintf("c.status IN (%s)", "'" . implode("','", $contractstatuses) . "'");
            }
        }

        if ( $depid )
        {// Подразделение указано
            if ( isset($conditions->children) && $conditions->children )
            {// Требуется получть персон из дочерних подразделений
                if ( $department = $this->dof->storage('departments')->get($depid) &&
                     $childids = $this->dof->storage('departments')->get_records_select("path LIKE '" . $department->path . "/%'") )
                {
                    $depidstr = $depid;
                    foreach ( $childids as $key => $dep )
                    {
                        $depidstr .= ',' . $key;
                    }
                    $conds[] = "per.departmentid IN (" . $depidstr . ")";
                }
            }
            $conds[] = "per.departmentid=" . $depid;
        }
        // Расширенный поиск выполняем с помощью одного сложного sql-запроса
        // Который учитывает все условия сразу и считает сколько найдено без учета LIMIT
        $tblpersons = $this->prefix() . $this->tablename();
        $tblcontracts = $this->prefix() . $this->dof->storage('contracts')->tablename();
        $tblpsbcs = $this->prefix() . $this->dof->storage('programmsbcs')->tablename();
        $sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT(per.id)
                FROM {$tblpersons} as per
                LEFT JOIN {$tblcontracts} as c 
                    ON c.studentid = per.id
                LEFT JOIN {$tblpsbcs} as pbcs 
                    ON pbcs.contractid = c.id
                WHERE " . implode(" AND ", $conds) . "
                ORDER BY per.sortname ASC";
        
        $records = $this->get_records_sql($sql, null, $limitfrom, $limitnum);
        
        // Сразу же запрашиваем сколько было найдено строк до выполнения LIMIT
        $foundrows = $this->get_records_sql("SELECT FOUND_ROWS() as 'count'");
        $countrows = 0;
        if ( is_array($foundrows) )
        {
            $values = array_values($foundrows);
            $result = reset($values);
            $countrows = $result->count;
        }
        if ( $onlycount )
        {
            return $countrows;
        }
        return $records;
    }
    
    /**
     * Получить расширенный список персон с дополнительными данными [договорами, программами
     * обучения, подписками и т. д.]
     * 
     * @param array $personids - массив id из таблицы persons
     * @param array $params - дополнительные параметры к SQL-запросу
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @return bool|array - false в случае ошибки или массив с результатами запроса
     */
    public function get_list_extended($personids, $params = null, $limitfrom = 0, $limitnum = 0)
    {
        if ( !is_array($personids) OR (!is_null($params) AND !is_array($params))
            OR !is_int_string($limitfrom) OR !is_int_string($limitnum) )
        {
            return false;
        }
        // Выборку делаем с помощью одного сложного sql-запроса
        // Который собирает все данные сразу по отобранному списку персон
        $tblpersons = $this->prefix() . $this->tablename();
        $tblages = $this->prefix() . $this->dof->storage('ages')->tablename();
        $tbllhistory = $this->prefix() . $this->dof->storage('learninghistory')->tablename();
        $tblcontracts = $this->prefix() . $this->dof->storage('contracts')->tablename();
        $tblpsbcs = $this->prefix() . $this->dof->storage('programmsbcs')->tablename();
        $tblprogs = $this->prefix() . $this->dof->storage('programms')->tablename();
        
        $sql = sprintf("SELECT
                  CONCAT(per.id,'_',IFNULL(c.id,'NA'),'_',IFNULL(pbcs.id,'NA'),'_',IFNULL(p.id,'NA')) as 'unique_id',
                  per.id as 'perid', per.sortname as 'persortname', c.id as 'cid', c.num as 'cnum', c.date as 'cdate',
                  c.status as 'cstatus', pbcs.id as 'pbcsid', p.name as 'pname', p.id as 'pid', pbcs.status as 'pbcsstatus',
                  (SELECT a.name FROM
                    $tbllhistory as lh
                    LEFT JOIN $tblages as a ON a.id=lh.ageid
                    WHERE lh.programmsbcid=pbcs.id
                    ORDER BY lh.changedate ASC
                    LIMIT 1) as startage,
                  (SELECT a.name FROM
                    $tbllhistory as lh
                    LEFT JOIN $tblages as a ON a.id=lh.ageid
                    WHERE lh.programmsbcid=pbcs.id
                    ORDER BY lh.changedate DESC
                    LIMIT 1) as currentage
                FROM
                  $tblpersons as per
                  LEFT JOIN $tblcontracts as c ON c.studentid=per.id
                  LEFT JOIN $tblpsbcs as pbcs ON pbcs.contractid=c.id
                  LEFT JOIN $tblprogs as p ON p.id=pbcs.programmid
                WHERE
                  per.id IN (%s)
                ORDER BY
                  per.sortname ASC, per.id, c.date, c.id, pbcs.dateadd, pbcs.id",implode(",",$personids));
        
        return $this->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    /** Сообщает, используется ли в системе этот е-mail
     * @param string $email - адрес email
     * @return bool
     * @access public
     */
    public function is_email_unique($email, $id = 0)
    {
        $persons = $this->get_records([
            'email' => $email,
            'status' => 'normal'
        ]);
        unset($persons[$id]);
        if ( empty($persons) )
        {
            return true;
        }
        return false;
    }
    
    /** Отправляет письмо персоне
     * @param $toid - id персоны получателя
     * @param $subject
     * @param $messagetext
     * @param $fromid - id персоны отправителя
     * @param $messagehtml
     * @param $attachment
     * @param $attachname
     * @return unknown_type
     */
    public function send_email($toid,$subject, $messagetext,$fromid='',$messagehtml='', $attachment='', $attachname='')
    {
        // Получаем персону-получателя
        if ( ! $personto = $this->get($toid) )
        {
            return false;
        }
        // Если указан id в fromid
        if ( ! empty($fromid) AND ctype_digit($fromid) )
        {
            // Удалось найти персону отправителя 
            if ($personfrom = $this->get($fromid))
            {
                // Синхронизирована ли персона-отправитель с Moodle?
                if ( ! empty($personfrom->sync2moodle) AND !empty($personfrom->mdluser) 
                                AND $this->dof->plugin_exists('modlib', 'ama'))
                {
                    // Да!
                    // Извлекаем пользователя Moodle в качестве отправителя
                    $from = $this->dof->modlib('ama')->user($personfrom->mdluser)->get();
                }else
                {
                    // Нет!
                    // Подставляем только имя отправителя
                    $from = $this->get_fullname($personfrom->id);
                }
            }
        }else
        {
            // Приравниваем вместо fromid отправится письмо от noreply с $fromid в качестве имени отправителя
            $from = $fromid;
        }
        
        // Если персона синхронизирована с Moodle и есть плагин ama - посылаем сообщение через ama
        if ( ! empty($personto->sync2moodle) AND ! empty($personto->mdluser) AND $this->dof->plugin_exists('modlib', 'ama'))
        {
            return $this->dof->modlib('ama')->user($personto->mdluser)->send_email($subject, $messagetext, 
                                                            $from, $messagehtml, $attachment, $attachname);
        }
        // Отправку напрямую пока не поддерживаем - нужно добавить метод в dof
        return false;
    }
    
    /** 
     * Зарегистрировать персону для переданного пользователя Moodle
     * @param object $USER - объект с пользователем Moodle
     * @return int - id новой записи в таблице persons
     * 
     * @todo вынести проверку на то, что пользователь с таким id в moodle уже существует
     * в таблице persons в функцию безопасной вставки (safe_insert()) 
     * @todo добавить возможность задавать параметры для нового пользователя в таблицы persons
     * (например подразделение, и т. п.)
     */
    public function reg_moodleuser($USER)
    {
        if (
                !is_object($USER)
                OR !isset($USER->id)
                OR !$USER->id
                OR !isset($USER->email)
                OR !$USER->email
                OR !isset($USER->firstname)
                OR !isset($USER->lastname)
            )
        {
            // Нам передали плохой объект без данных пользователя
            return false;
        }
        // Регистрируем персону
        $obj = new stdClass();
        $obj->mdluser = $USER->id;
        $obj->email = $USER->email;
        $obj->firstname = $USER->firstname;
        $obj->lastname = $USER->lastname;
        $obj->sync2moodle = 1;
        $obj->addressid = null;
        $departmentid = $this->get_cfg('departmentid');
        if ( isset($departmentid) AND $departmentid
            AND $this->dof->storage('departments')->is_exists($departmentid) )
        {
            $obj->departmentid = $departmentid;
        }else
        {
            $obj->departmentid = $this->dof->storage('departments')->get_default_id();
        }
        // проверим, есть ли в базе уже пользователь с таким id в Moodle
        if ( $person = $this->get_record(array('mdluser' => $USER->id)) )
        {// персона деканата для такого пользователя уже существует - все нормально
            return $person->id;
        }
        // в остальных случаях - регистрируем пользователя moodle в таблице persons
        return $this->insert($obj);
    }
    
    /**
     * Пересчитать sortname для всех пользователей
     * @return unknown_type
     */
    protected function remake_all_sortname()
    {
        $persons = $this->get_list_normal();
        foreach ($persons as $person)
        {
           $dataobject = new stdClass();
           $dataobject->sortname = $this->make_sortname($person); 
           $this->update($dataobject,$person->id); 
        }
        return true;
    }
    
    protected function make_sortname($person)
    {
        $str = '';
        if (isset($person->lastname))
        {
            if ($str)
            {
                // Вставляем разделитель
                $str .= ' ';
            }
            // Дополняем имя для поиска
            $str .= $person->lastname;
        }
        if (isset($person->firstname))
        {
            if ($str)
            {
                // Вставляем разделитель
                $str .= ' ';
            }
            // Дополняем имя для поиска
            $str .= $person->firstname;
        }
        if (isset($person->middlename))
        {
            if ($str)
            {
                // Вставляем разделитель
                $str .= ' ';
            }
            // Дополняем имя для поиска
            $str .= $person->middlename;
        }
        return $str;
    }
    
    /**
     * Возвращает полное имя пользователя в формате ФИО 
     * 
     * В качестве параметра может принимать ID персоны, 
     * объект персоны(совместимость с предыдущими версиями), 
     * или NULL, тогда берется текущий пользователь     
     * 
     * @param mixed $id - ID персоны, либо объект персоны, либо NULL
     * 
     * @return string - ФИО пользователя или пустая строка, если пользователь не найден
     */
    public function get_fullname($id = null)
    {
        // Получим персону
        switch ( gettype($id) )
        {// Выполним действия в зависимости от типа
            // Если ничего не передано
            case 'NULL' :
                global $USER;
                // Получим персону
                $person = $this->get_by_moodleid($USER->id);
                
                if ( empty($person) )
                {// Не получили запись пользователя
                    return '';
                }
                break;
            // Если передан объект ( совместимость с пред. версией )
            case 'object' :
                if ( isset($id->firstname) AND isset($id->lastname) )
                {// В объекте есть необъодимые поля
                    $person = $id;
                    break;
                }
                if ( isset($id->id) )
                {// В объекте есть id, попробуем получить персону
                    $person = $this->get($id->id);
                    if ( empty($person) )
                    {// Не получили запись пользователя
                        return '';
                    }
                    break;
                }
                // В объекте нет полей, достаточных для отображения fullname
                return '';
            // Если передан ID персоны
            case 'integer' :
            case 'string' :
                $person = $this->get(intval($id));
                if ( empty($person) )
                {// Не получили запись пользователя
                    return '';
                }
                break;
            // Если передано что-то другое
            default : 
                return '';
        }
        
        // Cформируем строку ФИО
        $fullname = array();
        if ( !empty($person->lastname) )
        {// Фамилия указана
            $fullname[] = $person->lastname;
        }
        if ( !empty($person->firstname) )
        {// Имя указано
            $fullname[] = $person->firstname;
        }
        if ( !empty($person->middlename) )
        {// Отчество указано
            $fullname[] = $person->middlename;
        }
        // Возвращаем результат
        return implode(' ', $fullname);
    }
    
    /**
     * Возвращает полное имя пользователя в формате Фамилия И.О. 
     * 
     * @param mixed $id - id записи пользователя, 
     *                    чье имя необходимо, любой тип данных
     * 
     * @return string - полное имя пользователя или 
     *                  пустая строка, если пользователь не найден
     */
    public function get_fullname_initials($id)
    {
        if ( is_object($id)        AND 
             isset($id->firstname) AND 
             isset($id->lastname)  AND 
             isset($id->middlename)
           )
        {
             $user = $id;   
        } else 
        {
            if ( is_string($id) )
            {// Преобразуем строку в число
                $id = intval($id);
            }
            if ( is_object($id) && isset($id->id) ) 
            {// Получаем из объекта ID
                $id = intval($id->id);
            }
            if ( is_array($id) && isset($id['id']) )
            {// Получаем из массива ID
                $id = intval($id['id']);
            }
            
            // Получим персону
            $user = $this->dof->storage('persons')->get($id);
            if ( empty($user) )
            {// Не получили запись
                return '';
            }
        }    
            
        // Формируем строку с ФИО персоны
        $str = $user->lastname.' '.mb_substr($user->firstname,0,1,'utf-8').'. ';
        if ( ! empty($user->middlename) )
        {// Добавляем отчество
            $str .= mb_substr($user->middlename,0,1,'utf-8').'.';
        }
        
        return $str;
    }
    
    /**
     * Вернуть массив с настройками или одну переменную
     * @param $key - переменная
     * @return mixed
     */
    protected function get_cfg($key=null)
    {
        // Возвращает параметры конфигурации
        include_once ($this->dof->plugin_path($this->type(),$this->code(),'/cfg/personcfg.php'));
        if (empty($key))
        {
            return $storage_persons;
        }else
        {
            return @$storage_persons[$key];
        }
    } 
    
    /** Возвращает список персон по заданным критериям 
     * 
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     * @return array массив записей из базы, или false в случае ошибки
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object) $conds;
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tblpersons = $this->prefix().$this->tablename();
        // @todo - пока выборка происходит без дополнительных таблиц - переопределение select не нужно
        //if (strlen($select)>0)
        //{// сделаем необходимые замены в запросе
        //    $select = 'c.'.ereg_replace(' AND ',' AND c.',$select.' ').' AND ';
        //    $select = ereg_replace(' OR ',' OR c.',$select);
        //    $select = str_replace('c. (','(c.',$select);
        //}
        $fields = "*";
        $paramslike = null;
        if ( $select )
        {
            if (!isset($conds->oldnamesearch) OR !$conds->oldnamesearch)
            { // Для поиска по старым фио нужен LEFT JOIN..
                $select = "WHERE {$select}";
            } else
            {
                $fields = 'p.*';
                $paramslike = array();
                $i = 10;
                while ( $i --> 1 )
                { // "$i стремится к 1": 9 8 7 6 5 4 3 2 1
                    $paramslike['searchname'.$i] = '%'.$conds->oldnamesearch.'%';
                }
            }
        }
        $sql = " FROM {$tblpersons}
                $select ";
        
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            if (!isset($conds->oldnamesearch) OR !$conds->oldnamesearch)
            { 
                return $this->count_records_sql("SELECT COUNT({$fields}) {$sql}", $paramslike);
            } else
            { // Отдельный запрос для поиска по ФИО и изменённым ($fields = 'p.*')
                global $DB;
                $sqlcountlike = "SELECT SUM(cnt) FROM (SELECT COUNT(DISTINCT(p.id)) as cnt {$sql}) sumcnt";
                if ( !$DB->get_field_sql($sqlcountlike, $paramslike) )
                {// Проблема: если внутренний COUNT выдал empty set, то внешний
                // выдаёт NULL и count_records_sql валится с ошибкой
                    return 0;
                }
                return $this->count_records_sql($sqlcountlike, $paramslike);
            }
        }
        // Добавим сортировку
        $sql .= $this->get_orderby_listing($sort);
        $temp = $this->get_records_sql("SELECT {$fields} {$sql}", $paramslike, $limitfrom, $limitnum);
        return $temp;
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
        if ( isset($conds->oldnamesearch) AND $conds->oldnamesearch)
        {
            return $this->get_select_oldnames();
        }
        if ( isset($conds->fioemailmdluser) AND $conds->fioemailmdluser )
        {
            $selects[] = "({$prefix}lastname LIKE '{$conds->fioemailmdluser}%' "
                        ."OR {$prefix}firstname LIKE '{$conds->fioemailmdluser}%'  "
                        ."OR {$prefix}middlename LIKE '{$conds->fioemailmdluser}%' "
                        ."OR {$prefix}email LIKE '{$conds->fioemailmdluser}%' "
                        ."OR ({$prefix}mdluser='{$conds->fioemailmdluser}' AND {$prefix}mdluser<>'0') "
                        ."OR {$prefix}id='{$conds->fioemailmdluser}')";
            unset($conds->fioemailmdluser);
        }
        if ( isset($conds->childrendepid) AND intval($conds->childrendepid) )
        {
            $childids = array();
            if ( $childs = $this->dof->storage('departments')->get_records_select("path LIKE '"
                    .$this->dof->storage('departments')->get_field($conds->childrendepid,'path')."/%'") )
            {// есть дочки - добавим их к запросу
                foreach($childs as $dep)
                {
                    $childids[] = $dep->id;
                }
            }
            if ( isset($conds->departmentid) )
            {// есть подразделение - добавим его к поиску
                $childids[] = $conds->departmentid;
            }
            $selects[] = "{$prefix}departmentid IN (".implode(',',$childids).")";
            unset($conds->departmentid);
            unset($conds->childrendepid);
        }
        if ( isset($conds->lastname) AND $conds->lastname )
        {
            $selects[] = "{$prefix}lastname LIKE '{$conds->lastname}%' ";
            unset($conds->lastname);
        }
        if ( ! empty($conds) )
        {// теперь создадим все остальные условия
            foreach ( $conds as $name=>$field )
            {
                if ( $field )
                {// если условие не пустое, то для каждого поля получим фрагмент запроса
                    $selects[] = $this->query_part_select($prefix.$name,$field);
                }
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
    public function get_orderby_listing($sort)
    {
        if ( is_null($sort) OR empty($sort) )
        {
            return ''; 
        }
        return " ORDER BY {$sort} ASC";
    }
    
    /**
     * Поск персон по имени(выбирает персон, которые имеют cstream в этом подразделениии(ученики/учителя))
     * Отображает уроки за промежуток времени
     * @param string $name - имя для поиска персоны( не полное(like) )
     * @param integer $depid - текущее подразделение(по умолчанию-все подразделения) 
     * @return array
     */
    public function get_person_lastname($name, $depid=0)
    {
        // таблицы
        $tbl = $this->prefix().$this->tablename();
        $tblcstreams = $this->prefix().$this->dof->storage('cstreams')->tablename();
        $tblcpassed   = $this->prefix().$this->dof->storage('cpassed') ->tablename();
        
        $csdep = '';
        if ( $depid )
        {// учитываем подразделение
            $csdep = "AND cs.departmentid={$depid}";
        }
        // сам запрос
        $sql = "SELECT DISTINCT per.id as id, per.sortname as sortname from {$tbl} as per, {$tblcpassed} as cpas, {$tblcstreams} as cs 
                 WHERE cs.status<>'canceled' {$csdep} AND ( (cs.teacherid=per.id AND per.lastname LIKE '{$name}%') OR
                  (cpas.cstreamid=cs.id AND cpas.status<>'canceled' AND cpas.studentid=per.id AND per.lastname LIKE '{$name}%') ) 
                  ORDER BY per.sortname";
        return $this->get_records_sql($sql);
    }
    
    /** Обработка AJAX-запросов из форм
     * @param string $querytype - тип запроса
     * @param int $objectid - id объекта с которым производятся действия
     * @param array $data - дополнительные данные пришедшие из json-запроса
     * 
     * @return array
     */
    public function widgets_field_variants_list($querytype, $depid, $data)
    {
        switch ( $querytype )
        {
            // Автозаполнение имени
            case 'autocomplete_firstname' :
                return $this->autocomplete_field('firstname', $data, $depid);
            // Автозаполнение фамилии
            case 'autocomplete_lastname' :
                return $this->autocomplete_field('lastname', $data, $depid);
            // Автозаполнение отчества
            case 'autocomplete_middlename' :
                return $this->autocomplete_field('middlename', $data, $depid);
            // Автозаполнение email
            case 'autocomplete_email' :
                return $this->autocomplete_field('email', $data, $depid);
            // Поиск персоны по фрагменту данных
            case 'persons_list' :    
                return $this->widgets_persons_list($depid, $data);
            // Поиск менеджера подразделения по фрагменту данных
            case 'persons_list_departmentmanager' :
                return $this->widgets_persons_list_departmentmanager($depid, $data);
            // Поиск персоны по данным пользователя Moodle
            case 'mdluser_list':    
                return $this->widgets_mdluser_list($data);
            
                
            // @todo определить для чего нужен этот тип запроса
            case 'person_name':     return $this->result_of_autocomplete('person_name', $depid, $data);
            // список персон для выдачи комплекта оборудования
            case 'person_give_set': return $this->result_of_autocomplete('person_give_set', $depid, $data);
            default: 
                return [0 => $this->dof->modlib('ig')->igs('choose')];
        }
    }
    
    /** 
     * Получить список персон по фрагменту данных
     * 
     * @param int $departmenid - Ограничение по подразделению
     * @param string $data - Фрагмент ФИО персоны или ID
     * 
     * @return array массив персон в формате ID => ФИО
     */
    protected function widgets_persons_list($departmentid, $data)
    {
        // Результат поиска
        $result = [];
        
        // Нормализация данных
        $data = clean_param($data, PARAM_TEXT);
        
        // Поиск персон по фрагменту данных
        $persons = (array)$this->get_list_search($data, $departmentid, true);
        if ( ! empty($persons) )
        {// Пользователи найдены
            foreach ( $persons as $person )
            {
                // Проверка доступа
                if ( $this->is_access('use', $person->id) )
                {
                    $obj = new stdClass;
                    $obj->id   = $person->id;
                    $obj->name = $this->get_fullname($person).' ['.$person->id.']';
                    $result[$person->id] = $obj;
                }
            }
        }
        return $result;
    }
    
    /**
     * Получить список доступных менеждеров подразделения по фрагменту данных
     *
     * @param int $departmenid - Ограничение по подразделению
     * @param string $data - Фрагмент ФИО персоны или ID
     *
     * @return array - Массив персон в формате ID => ФИО
     */
    protected function widgets_persons_list_departmentmanager($departmentid, $data)
    {
        // Результат поиска
        $result = [];
        // Найденные персоны
        $persons = [];
        
        // Нормализация данных
        $data = clean_param($data, PARAM_TEXT);
    
        // Получение списка родительских подразделений
        $deptrace = (array)$this->dof->storage('departments')->get_departmentstrace($departmentid);
        
        // Добавление доступных персон из родительских подразделений
        foreach ( $deptrace as $department )
        {
            // Поиск персон по фрагменту данных в указанном подразделении
            $persons = $this->get_list_search($data, $department->id, false);
            
            if ( ! empty($persons) )
            {// Пользователи найдены
                foreach ( $persons as $person )
                {
                    // Проверка доступа
                    if ( $this->is_access('use', $person->id) )
                    {
                        $obj = new stdClass;
                        $obj->id   = $person->id;
                        $obj->name = $this->get_fullname($person).' ['.$person->id.']';
                        $result[$person->id] = $obj;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /** Получить список пользователей Moodle по первым буквам фамилии
     * @param string $lastname - первые несколько букв фамилии пользователя
     *
     * @return array массив объектов для AJAX-элемента dof_autocomplete
     */
    protected function widgets_mdluser_list($lastname)
    {
        $lastname = clean_param($lastname, PARAM_TEXT);
        
        if ( is_int_string($lastname) )
        {// пользователя Moodle ищут по id
            $id = $lastname;
            if ( ! $this->dof->modlib('ama')->user(false)->is_exists($id) )
            {// нет пользователя с таким id
                return array();
            }
            $users = array($id => $this->dof->modlib('ama')->user($id)->get() );
        }else
        {// пользователя Moodle ищут по ФИО
            if ( mb_strlen($lastname) < 3 )
            {// Слишком короткий фрагмент фамилии - не начинаем поиск
                return array();
            }
            $conditions = new stdClass;
            $conditions->lastname = $lastname;
            // Ищем пользователя по фамилии
            if ( ! $users = $this->dof->modlib('ama')->user(false)->search($conditions, 'lastname ASC', 0, 15) )
            {// не нашли
                return array();
            }
        }
        
        // Формируем массив объектов нужной структуры для dof_autocomplete
        $result = array();
        foreach ( $users as $user )
        {
            $obj = new stdClass;
            $obj->id   = $user->id;
            $obj->name = $user->lastname.' '.$user->firstname.' ['.$user->id.']';
            $result[$user->id] = $obj;
        }
        
        return $result;
    }
    
    /** Метод, который возаращает список для автозаполнения
     * @todo следует более внятно назвать типы запросов и проставить комментарии 
     *       для типа запроса "person_name" - сейчас невозможно понять где он используется и зачем нужен
     * @todo разбить эту функцию на 2 (по 1 функции на каждый тип запроса). Сейчас она слишком длинная
     * 
     * @param string $querytype - тип завпроса(поу молчанию стандарт)
     * @param string $data - строка
     * @param integer $depid - id подразделения  
     * 
     * @return array or false - запись, если есть или false, если нет
     */
    public function result_of_autocomplete($querytype, $depid, $data)
    {
        if ( ! $data )
        {// пустые даные
            return false;
        }
        // таблица выборки
        $tbl = $this->prefix().$this->tablename();
        $tblcstreams = $this->prefix().$this->dof->storage('cstreams')->tablename();
        $tblcpassed   = $this->prefix().$this->dof->storage('cpassed') ->tablename();
        // от типа запроса - своя выборка
        switch ($querytype)
        {
            case 'person_name' :
        
                $data = $this->get_sql_fio($data, 'per');
                $csdep = '';
                // выбираем учителей
                $sqlteacher = "SELECT DISTINCT per.id as id, per.sortname as sortname from {$tbl} as per INNER JOIN {$tblcstreams} as cs
                                ON per.id=cs.teacherid 
                                WHERE cs.status<>'canceled' {$csdep} {$data}";
                if ( ! $selectteacher =  $this->get_records_sql($sqlteacher, null, 0, 10) )
                {// создадим пустой, дабы не было ошибок при объединении массивов
                    $selectteacher = array();
                }
                // выбираем учиников
                $sqlstudent = "SELECT DISTINCT per.id as id, per.sortname as sortname from {$tbl} as per INNER JOIN {$tblcpassed} as cpas 
                                ON per.id=cpas.studentid INNER JOIN {$tblcstreams} as cs ON  cpas.cstreamid=cs.id
                                WHERE cpas.status<>'canceled' {$data} AND cs.status<>'canceled' 
                                  {$csdep} ";
                if ( ! $selectstudent =  $this->get_records_sql($sqlstudent, null, 0, 10) )
                {// создадим пустой, дабы не было ошибок при объединении массивов
                    $selectstudent = array();
                }                
                // объединим результаты
                $select = $selectstudent + $selectteacher;
                $mas = array();
                // сделаем в порядке ключ->значение
                if ( $select )
                {
                    foreach ( $select as $key=>$obj )
                    {// создаем массив объектов для json
                        $a = new stdClass();
                        $a->id = $obj->id;
                        $a->name = $obj->sortname;
                        $mas[$obj->id] = $a;
                    }
                    // отсортируем по фамилии
                    asort($mas);
                }    
                return $mas;
           // выдача комплекта персоне
           // ищем всех персон в системе, критерии - имя и фильтр на права
           case 'person_give_set' : 
               // кол найденных персон
               $mas = array();
               // ветвь отслеживания по id персоны из системы
               $id = (int)$data;
               
               // передали id
               // выведеи по id персону
               if ( $id )
               {
                   if ( $person = $this->get_record(array('id' => $id)) AND $this->is_access('give_set',$person->id) )
                   {// нашли персону
                       $per = new stdClass();
                       $per->name = '';
                       $per->id = $person->id;
                       if ( empty($person->sortname) )
                       {
                           $per->name = $this->get_fullname($person);
                       }else 
                       {
                           $per->name = $person->sortname;    
                       }                       
                       $mas[$person->id] = $per;
                       return $mas;
                   }
                   return $mas; 
                      
               }
               
               $data = $this->get_sql_fio($data);
               // счетчик
               $num = 0;

               while ( count($mas) < 10 )
               {
                   // берем по 100 персон
                   if ( $persons = $this->get_records_select(" status='normal' {$data}", null,'sortname', '*', $num, 100) )
                   {   
                       // проверяем на права
                       foreach ( $persons as $person )
                       {
                           if ( $this->is_access('give_set',$person->id) )
                           {
                               $per = new stdClass();
                               $per->name = '';
                               $per->id = $person->id;
                               if ( empty($person->sortname) )
                               {
                                   $per->name = $this->get_fullname($person);
                               }else 
                               {
                                   $per->name = $person->sortname;    
                               } 
                               $mas[$person->id] = $per;
                               // нашли 10 - выходим, больше не надо
                               if ( count($mas) == 10 )
                               {
                                   return $mas;
                               }
                           }
                       }
                   }else 
                   {
                       return $mas;    
                   }    
                   // следующие 100
                   $num += 100;
               }
               
                return $mas;
            default:
        }
        // нет ни одного из типа
        return false;
    }    
    
    /** Метод возвращает строку для sql-запроса
     * с поиском по персонам ФИО
     * 
     * @param string $$tbl - имя таблицы персон(по умолчанию mdl_block_dof_s_persons )
     * @param string $data - строка с данными для выборки(Ф И О)(Иванов Максим Петрович)
     * 
     * @return string - строку с готовым sql-кодом, начинающ на слово ' AND'
     **/    
    public function get_sql_fio($data='', $tbl='')
    {
        if (empty($data))
        {// пусто - и вернем пустую строку
            return '';
        }
        if ( ! empty($tbl) )
        {// таблица персоны
            $tbl .= "." ;
        }
        // уберем пробелы по краям
        $data = trim($data);
        // разобьём массив на пробелы
        $mas = explode(" ", $data);
        switch (count($mas))
        {
            case 1: return " AND {$tbl}lastname LIKE '".$mas[0]."%'"; break; 
            case 2: return " AND {$tbl}lastname='".$mas[0]."' AND {$tbl}firstname LIKE '".$mas[1]."%'"; break; 
            case 3: return " AND {$tbl}lastname='".$mas[0]."' AND {$tbl}firstname='".$mas[1]."' 
                    AND {$tbl}middlename LIKE '".$mas[2]."%'"; break;   
            default: return '';
        }
    }
    
    /** Получить часовой пояс персоны по ее id
     * 
     * @return float - часовой пояс в UTC (как положительное или отричательное дробное число) или false
     * @param int $personid[optional] - id пользователя в таблице persons. 
     *                        Если не передано - то берется текущий пользователь
     */
    public function get_usertimezone_as_number($personid = null)
    {
        if ( ! $personid )
        {
            $person = $this->get_by_moodleid();
        }
        
        if ( ! isset($person->mdluser) OR ! $person->mdluser )
        {// пользователя Moodle нет а у нас в таблице временные 
            // зоны не хранятся - не знаем что делать
            return false;
        }
        
        if ( ! $this->dof->modlib('ama')->user(false)->is_exists($person->mdluser) )
        {// пользователя нет в Moodle - не можем получит временную зону
            return false;
        }
        // пользователь есть в moodle - получаем его вместе с таймзоной
        $user = $this->dof->modlib('ama')->user($person->mdluser)->get();
        
        return (float) $user->timezone;
    }
    
    /** Получить дату и время с учетом часового пояса
     * 
     * @return string - время с учетом часового пояса или пустая строка
     * @param int $date - время в unixtime
     * @param string $format - формат даты с учетом символов используемых в strftime
     * @param int $mdluserid - id пользователя в moolde
     * @param boolean $fixday - true стирает нуль перед %d
     *                          false - не стирает
     */
    public function get_userdate($date, $format = '', $personid = null, $fixday = false)
    {
        $mdluserid = null;
        if ( ! is_null($personid) )
        {   // Берем id текущего пользователя
            $mdluserid = $this->get_field($personid,'mdluser');
        }
        return $this->dof->sync('personstom')->get_userdate($date,$format,$mdluserid,$fixday);
    }
    
    /** Получить дату и время с учетом часового пояса
     * 
     * @return array - время с учетом часового пояса или пустая строка
     * @param int $date - время в unixtime
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_usergetdate($date, $personid = null)
    {
        $mdluserid = null;
        if ( ! is_null($personid) )
        {   // Берем id текущего пользователя
            $mdluserid = $this->get_field($personid,'mdluser');
        }
        return $this->dof->sync('personstom')->get_usergetdate($date,$mdluserid);
    }
    
    /** Получить дату и время с учетом часового пояса
     * 
     * @return array - время с учетом часового пояса или пустая строка
     * @param int $date - время в unixtime
     * @param int $mdluserid - id пользователя в moolde
     */
    public function get_make_timestamp($hour=0, $minute=0, $second=0, $month=1, $day=1, $year=0, $personid = null, $applydst=true)
    {
        $mdluserid = null;
        if ( ! is_null($personid) )
        {   // Берем id текущего пользователя
            $mdluserid = $this->get_field($personid,'mdluser');
        }
        return $this->dof->sync('personstom')->get_make_timestamp($hour, $minute, $second, $month, $day, $year, $mdluserid, $applydst);
    }
    
    /** Подсчитывает, сколько раз изменяли имя пользователя
     * 
     * @param int $userid
     * @return int количество изменений имени
     */
    public function count_name_changes($userid)
    {
        // Проверим, менялась ли фамилия ранее
        $select = " code LIKE 'oldfirstname%' AND plugintype = '" . $this->type() .
                                           "' AND plugincode = '". $this->code() .
                                           "' AND objectid = "    . $userid;
        $count = $this->dof->storage('cov')->count_records_select($select);
        return $count;
    }

    /** Вставляет три записи в таблицу cov, содержащие существующие части имени персоны $userid (если $existing=true)
     * 
     * @param int $userid
     * @param bool $existing создаёт запись на основе существующей (копирует поля из него)
     */
    public function create_old_name($userid, $existing=false)
    {
        if ( !$this->is_exists($userid) )
        {
            return false;
        }
        if ( $existing )
        {
            $user = $this->get($userid, 'firstname,lastname,middlename');
        } else
        {
            $user = new stdClass();
            $user->firstname = '';
            $user->lastname = '';
            $user->middlename = '';
        }
        if ( !isset($user->middlename) )
        { // Отчества может и не быть
            $user->middlename = '';
        }
        $count = $this->count_name_changes($userid);
        $count++; // Если было 0, станет 1, и т. д.
        $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                          $userid, 'oldfirstname'.$count, $user->firstname);
        $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                          $userid, 'oldlastname'.$count, $user->lastname);
        $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                          $userid, 'oldmiddlename'.$count, $user->middlename);
        return true;
    }
    
    /** Редактирует старое имя пользователя
     * 
     * @param int $userid
     * @param int $number номер редактируемого поля
     * @param string $firstname
     * @param string $lastname
     * @param string $middlename
     * @return boolean false в случае ошибки, true если успешно
     */
    public function edit_old_name($userid, $number=1, $firstname = '', $lastname = '', $middlename = '')
    {
        if ( !$this->is_exists($userid) )
        {
            return false;
        }
        $count = $this->count_name_changes($userid);
        if ( $count < 1 )
        { // Редактировать нечего
            return false;
        }
        $fields = array('firstname' => $firstname,
                        'lastname'  => $lastname,
                        'middlename'=> $middlename);
        // Достаём предыдущую запись
        if ( $number > 1 )
        {
            $prevuser = current($this->get_person_namechanges($userid, 1, $number - 1));
        } else
        {
            $prevuser = $this->get($userid, implode(',', array_keys($fields)));
        }
        if ( empty($prevuser) )
        { // Ошибка, предыдущий пользователь не найден
            return false;
        }
        // Если поле текущей записи не заполнено, нужно посмотреть, есть ли оно в предыдущей
        $namechanges = $this->get_person_namechanges($userid, 1, $number);
        if ( $namechanges !== false )
        {
            $edituser = current($namechanges);
        } else
        {
            $edituser = new stdClass();
        }
        foreach ( $fields as $name => $value )
        { // Сначала записываем переданные параметры
            if ( !is_null($value) )
            {
                $edituser->$name = $value;
            } else // Если поля не передали, берём предыдущее (если оно есть и если в текущем пусто)
            {
                if ( empty($edituser->$name) )
                { // Если в поле ничего нет
                    if ( !empty($prevuser->$name) )
                    { // Если в предыдущем что-то есть
                        $edituser->$name = $prevuser->$name;
                    }
                }
            }
        }
        // Теперь сохраняем
        $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                          $userid, 'oldfirstname'.$number, $edituser->firstname);
        $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                          $userid, 'oldlastname' .$number, $edituser->lastname);
        if ( !isset($prevuser->middlename) )
        { // Отчества может и не быть
            $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                              $userid, 'oldmiddlename'.$number, '');
        } else
        {
            $this->dof->storage('cov')->save_option($this->type(), $this->code(),
                                              $userid, 'oldmiddlename'.$number, $edituser->middlename);
        }
        return true;
    }
    
    /** Возвращает массив изменённых имён персоны из справочника `cov` в следующем формате:
     * array ( id => object->(firstname,lastname,middlename), ... )
     * 
     * @param int $userid из таблицы persons
     * @param int $limitnum ограничение получаемых записей (0 - все записи, которые есть)
     * @param int $limitfrom с какой из записи начинать (от 1 до N)
     * @param bool $asc в каком порядке забирать записи (true - по порядку, false - наоборот)
     * @return bool|array false в случае ошибки или массив со значениями
     */
    public function get_person_namechanges($userid, $limitnum = 2, $limitfrom = 1, $asc = true)
    {
        $namechanges = array(); // array ( [id] => object, [id] => object, ...)
        if ( !$this->is_exists($userid) )
        {
            return false;
        }
        $count = $this->count_name_changes($userid);
        if ( $count < 1 )
        {
            return false;
        }
        
        if ( $limitnum > $count )
        {// Ограничиваем лимит числом записей
            $limitnum = $count;
        }
        $limitto = $limitfrom + $limitnum;
        for ( $index = $limitfrom; $index < $limitto; $index++ )
        {
            $user = new stdClass();
            $user->firstname     = $this->dof->storage('cov')->get_option($this->type(),
                    $this->code(), $userid, 'oldfirstname' . $index);
            $user->lastname      = $this->dof->storage('cov')->get_option($this->type(),
                    $this->code(), $userid, 'oldlastname' . $index);
            $user->middlename    = $this->dof->storage('cov')->get_option($this->type(),
                    $this->code(), $userid, 'oldmiddlename' . $index);
            $namechanges[$index] = $user;
        }
        if ( $asc )
        {// забираем $limit записей, начиная с $limitfrom
            return $namechanges;
        }
        return array_reverse($namechanges);
    }
    
    /** Возвращает sql-запрос для выборки по старым именам
     * 
     * Используется sql_like со строкой :searchname,
     *  нужно передавать массив $params = array ('searchname' => '...')
     * @return string sql-запрос для поиска
     */
    protected function get_select_oldnames()
    {
        global $DB;
        // Поиск по фио, включая изменённые:
        // Для этого нужно искать по двум базам
        $covtable = $this->prefix().$this->dof->storage('cov')->tablename();
        // Нельзя сделать один именованный statement несколько раз:
        // http://www.php.net/manual/en/pdo.prepare.php#69291
        return ' p LEFT JOIN '. $covtable . ' c ON p.id=c.objectid'
             . ' WHERE ' . $DB->sql_like('p.firstname',  ':searchname1', false, true, false)
             . ' OR '    . $DB->sql_like('p.lastname',   ':searchname2', false, true, false)
             . ' OR '    . $DB->sql_like('p.middlename', ':searchname3', false, true, false)
             . ' OR c.code = "oldfirstname1" AND '  . $DB->sql_like('c.value', ':searchname4', false, true, false)
             . ' OR c.code = "oldfirstname2" AND '  . $DB->sql_like('c.value', ':searchname5', false, true, false)
             . ' OR c.code = "oldlastname1" AND '   . $DB->sql_like('c.value', ':searchname6', false, true, false)
             . ' OR c.code = "oldlastname2" AND '   . $DB->sql_like('c.value', ':searchname7', false, true, false)
             . ' OR c.code = "oldmiddlename1" AND ' . $DB->sql_like('c.value', ':searchname8', false, true, false)
             . ' OR c.code = "oldmiddlename2" AND ' . $DB->sql_like('c.value', ':searchname9', false, true, false)
             . ' GROUP BY c.objectid';
    }
    
    /**
     * Сформировать информацию о записях, затрагиваемых при удалении персоны из деканата
     *
     * Отображает число записей, которые доступны для перевода в пассивный режим
     * при удалении персоны.
     * Также отображает количество заблокированных записей для пользователя
     *
     * @param int $personid - ID персоны, которую собираются удалить
     * @return string - информация по записи персоны
     */
    private function get_delete_person_info($personid)
    {
        // Выполняем сбор информации в зависимости от доступности плагина статусов
        if ( $this->dof->plugin_exists('workflow', 'persons') )
        {// Плагин статусов есть, значит можем получить реальные статусы
            // Получаем все реальные статусы для персон
            $statuses = $this->dof->workflow('persons')->get_meta_list('real');
            // Конвертируем в массив для фильтрации записей
            $statuses = array_keys($statuses);
            // Получаем персону
            $person = $this->get_record(
                    array(
                            'id' => $personid,
                            'status' => $statuses
                    )
            );
            if ( empty($person) )
            {// Персона не найдена
                return '';
            }
            
            // Начинаем подсчет записей
            $canedit = 0;
            $cantedit = 0;
            if ( $this->dof->workflow('persons')->
                    is_access('changestatus', $person->id) )
            {// Смена статуса персон разрешена для данного пользователя
                $canedit++;
            } else
            {// Доступ запрещен
                $cantedit++;
            }
        } else
        {// Плагина статусов нет, значит берем запись без учета статуса
            $person = $this->get($personid);
            if ( empty($person) )
            {// Персона не найдена
                return '';
            }
            
            // Начинаем подсчет
            $canedit = 0;
            $cantedit = 0;
            if ( $this->is_access('edit', $person->id) )
            {// Редактирование записи разрешено для данного пользователя
                $canedit++;
            } else
            {// Доступ запрещен
                $cantedit++;
            }
        }
    
        // Формируем информацию о затрагиваемых записях
        $result = '';
        if ( $canedit )
        {// Если есть доступные для редактирования записи
            $result .= $this->dof->get_string(
                    'delete_person_can_edit',
                    $this->code(),
                    NULL,
                    $this->type()
            ).$canedit.'</br>';
        }
        if ( $cantedit )
        {// Если есть недоступные для редактирования данной персоной записи
            $result .= $this->dof->get_string(
                    'delete_person_cant_edit',
                    $this->code(),
                    NULL,
                    $this->type()
            ).$cantedit.'</br>';
        }
        if ( ! empty($result) )
        {// Есть информация для отображения - добавим название плагина
            return '<b>'.$this->dof->get_string(
                    'title',
                    $this->code(),
                    NULL,
                    $this->type()
            ).'</b></br>'.$result;
        }
    
        // Возвращаем строку
        return $result;
    }
    
    /**
     * Перевести персону в статус Удалена
     *
     * В зависимости от того, доступен ли плагин статусов, метод меняет статус у
     * записи персоны либо через этот плагин, либо вручную
     *
     * @param int $personid - ID персоны, которую собираются удалить
     * @return bool - false в случае ошибок при удалении и
     *                true в случае успешного завершения
     */
    private function delete_person($personid)
    {
        // Выполняем сбор информации в зависимости от доступности плагина статусов
        if ( $this->dof->plugin_exists('workflow', 'persons') )
        {// Плагин статусов есть, значит можем получить реальные статусы
            // Получаем все реальные статусы для персон
            $statuses = $this->dof->workflow('persons')->get_meta_list('real');
            // Конвертируем в массив для фильтрации записей
            $statuses = array_keys($statuses);
            // Получаем персону
            $person = $this->get_record(
                    array(
                            'id' => $personid,
                            'status' => $statuses
                    )
            );
            if ( empty($person) )
            {// Персона не найдена, завершим выполнение
                return true;
            }
            
            // Сменяем статус
            if ( $this->dof->workflow('persons')->
                    is_access('changestatus', $person->id) )
            {// Смена статуса разрешена для данного пользователя
                return $this->dof->workflow('persons')->
                                change($person->id, 'deleted');
            }
        } else
        {// Плагина статусов нет, значит получаем пользователя без учета его статуса
            $person = $this->get($personid);
            if ( empty($person) )
            {// Персона не найдена
                return true;
            }
    
            // Выполняем перевод в неактивный статус
            $update = new stdClass();
            $update->status = 'deleted';
            if ( $this->is_access('edit', $person->id) )
            {// Редактирование записи разрешено для данного пользователя
                return $this->update($update, $person->id);
            }
        }
        return true;
    }
    
    /**
     * Получение списка пользовательских полей
     */
    public function get_person_fieldnames()
    {
        global $DB;
        $fields = $DB->get_columns($this->tablename());
        // Добавление названий
        foreach ( $fields as $fieldname => &$fielddata )
        {
            $fielddata = $this->dof->get_string($fieldname, 'persons', NULL, 'storage');
        }
        return $fields;
    }
    
    /**
     * Получение персон по ID пользователелей moodle
     * 
     * @param array $userids - Массив пользователей Moodle
     * 
     * @return array - Массив персон
     */
    public function get_persons_by_userids($userids)
    {
        if ( empty($userids) )
        {
            return [];
        }
        // Нормализация значений
        $users = [];
        foreach ( $userids as $user )
        {
            if ( is_int($user) || is_string($user) )
            {
                $value = intval($user);
            }
            if ( isset($user->id) )
            {
                $value = intval($user->id);
            }
            if ( isset($user['id']) )
            {
                $value = intval($user['id']);
            }
            $users[$value] = $value;
        }
        // Сбор информации в зависимости от доступности плагина статусов
        if ( $this->dof->plugin_exists('workflow', 'persons') )
        {// Плагин статусов найден
            // Получение реальных статусов для персон
            $statuses = $this->dof->workflow('persons')->get_meta_list('real');
            // Конвертация в массив для фильтрации записей
            $statuses = array_keys($statuses);
            // Получение персон
            $persons = $this->get_records(
                    [
                            'mdluser' => $users,
                            'status' => $statuses
                    ]
            );
        } else
        {// Плагина статусов нет, значит получаем пользователей без учета статуса
            $persons = $this->get_records(
                    [
                            'mdluser' => $users
                    ]
            );
        }
        return $persons;
    }
    
    
    
    /**
     * Получить массив значений для автозаполнения
     * 
     * @param string $fieldname - Название пользовательского поля
     * @param string $part - Часть значения
     * @param unknown $departmentid - Область поиска. Если = 0 - то по всей системе
     */
    private function autocomplete_field($fieldname, $part, $departmentid = 0)
    {
        $data = [];
        $select = '';
        $params = [];
        
        // Формирование массива имен
        $select .= $fieldname.' LIKE :part ';
        $params['part'] = $part.'%';
        
        if ( $this->dof->plugin_exists('workflow', 'persons') )
        {// Найден плагин статусов
            $statuses = $this->dof->workflow('persons')->get_meta_list('real');
            $statuses = '"'.implode('","', array_keys($statuses)).'"';
            $select .= ' AND status IN ('.$statuses.') ';
        }
        if ( $departmentid > 0 )
        {// Учет подразделения
            $select .= ' AND departmentid = :departmentid ';
            $params['departmentid'] = $departmentid;
        }
        $result = $this->get_records_select($select, $params, $fieldname.' ASC ', 'id, '.$fieldname);
        foreach ( (array)$result as $element )
        {
            $value = new stdClass();
            $value->id = 0;
            $value->name = $element->$fieldname;
            // Формирование массива по автозаполнению
            $data[$element->$fieldname] = $value;
        }
        return $data;
    }
}
?>