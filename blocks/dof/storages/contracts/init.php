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

/** Договоры с учениками
 * 
 */
class dof_storage_contracts extends dof_storage implements dof_storage_config_interface
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
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        
        $result = true;
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2012110300)
        {
            // добавляем поле метаконтракта
            $field = new xmldb_field('metacontractid',XMLDB_TYPE_INTEGER, '7', 
                     null, null, null, null, 'enddate'); 
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
                               
            }
            $index = new xmldb_index('imetacontractid', XMLDB_INDEX_NOTUNIQUE, 
                     array('metacontractid'));
            // добавляем индекс для поля
            if ( !$dbman->index_exists($table, $index) ) 
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
        return 2016070400;
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
        return 'contracts';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('departments'   => 2009040800,
                                      'persons'       => 2016052700,
                                      'config'        => 2011080900,
                                      'acl'           => 2011040504,
                                      'metacontracts' => 2012101500));
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
         
        // Проверка специальных условий
        switch ( $do )
        {// определяем дополнительные параметры в зависимости от запрашиваемого права
            
            // право на просмотр информации по своему контракту
            case 'view:billing/my':
                
                // Проверяем, является ли пользователь владельцем договора
                $select = 'id = '.$acldata->objectid.' AND 
                           ( sellerid = '.$acldata->personid.' OR 
                           clientid = '.$acldata->personid.' OR 
                           studentid = '.$acldata->personid.' 
                           ) ';
                // Проверяем, является ли пользователь владельцем договора

                if ( $this->count_records_select($select) )
                {
                    return true;
                }
                // Регресс к родительскому праву
                $do = 'view:billing';
                break;
                
            default:
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
        return 'block_dof_s_contracts';
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
        
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']     = array('roles'=>array('manager','methodist'));
        $a['edit']     = array('roles'=>array('manager'));
        $a['create']   = array('roles'=>array('manager'));
        $a['delete']   = array('roles'=>array('manager'));
        $a['use']      = array('roles'=>array('manager','methodist'));
        // Права для биллинга
        $a['view:billing'] = array('roles'=>array('manager','methodist'));
        $a['view:billing/my'] = array('roles'=>array('manager','methodist','parent','student'));
        $a['create:billinrefill']   = array('roles'=>array('manager'));
        $a['create:billinwriteof']   = array('roles'=>array('manager'));
        
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
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function insert($dataobject,$quiet=NULL)
    {
        if ( ! isset($dataobject->num) || empty($dataobject->num) )
        {
            $dataobject->num = '';
        }
        
        // Добавляем текущее время
        $dataobject->adddate = time();
        // Исходный статус
        $dataobject->status = 'tmp';
        
        // Вызываем метод из родительского класса
        if ( $id = parent::insert($dataobject, $quiet) )
        {// Устанавливаем номер контракта по номеру записи в БД
            $obj = new stdClass();
            $obj->id = intval($id);
            $obj->status = 'tmp';
            $obj->num = $this->get_default_contractnum($id);
            $this->update($obj);
            return $id; 
        } else
        {
            return false;
        }
    }
    
    /**
     * Сохранить договор в системе
     *
     * @param string|stdClass|array $contractdata - Данные договора(номер или комплексные данные)
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - false в случае ошибки или ID договора в случае успеха
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($contractdata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($contractdata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
    
        // Сохранение данных
        if ( isset($normalized_data->id) && $this->is_exists($normalized_data->id) )
        {// Обновление записи
            $contract = $this->update($normalized_data);
            if ( empty($contract) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_contract');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'contracts', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            $res = $this->insert($normalized_data);
            if ( ! $res )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_contract');
            } else
            {// Обновление удалось
                $this->dof->send_event('storage', 'contracts', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных договора
     *
     * Формирует объект договора на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param string|stdClass|array $contractdata - Данные договора(номер или комплексные данные)
     * @param array $options - Опции работы
     *
     * @return stdClass - Нормализовализованный Объект договора
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($contractdata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($contractdata) || is_array($contractdata) )
        {// Комплексные данные
            $contractdata = (object)$contractdata;
        } elseif ( is_string($contractdata) )
        {// Передан номер договора
            $contractdata = new stdClass();
            $contractdata->num = $contractdata;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
    
        // Проверка входных данных
        if ( empty($contractdata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        if ( isset($contractdata->id) )
        {// Проверка на существование
            if ( ! $this->get($contractdata->id) )
            {// Договор не найден
                throw new dof_exception_dml('contract_not_found');
            }
        }
    
        // Создание объекта для сохранения
        $saveobj = clone $contractdata;
        
        // Обработка входящих данных и построение объекта договора
        if ( isset($saveobj->id) )
        {// Обновление данных о договоре
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
            unset($saveobj->adddate);
        } else
        {// Cоздание нового договора
            $currenttime = time();
        
            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            // Установка значений по-умолчанию для полей
            if ( ! isset($saveobj->typeid) || empty($saveobj->typeid) )
            {// Установка типа договора по-умолчанию
                $saveobj->typeid = null;
            }
            if ( ! isset($saveobj->num) || empty($saveobj->num) )
            {// Установка номера договора по-умолчанию
                $saveobj->num = $this->get_default_contractnum();
            }
            if ( ! isset($saveobj->numpass) || empty($saveobj->numpass) )
            {// Установка номера студенческого билета/пропуска по-умолчанию
                $saveobj->numpass = null;
            }
            if ( ! isset($saveobj->date) || empty($saveobj->date) )
            {// Установка даты заключения договора по-умолчанию
                $saveobj->date = $currenttime;
            }
            if ( ! isset($saveobj->sellerid) || empty($saveobj->sellerid) )
            {// Установка агента по-умолчанию
                $saveobj->sellerid = null;
            }
            if ( ! isset($saveobj->studentid) || empty($saveobj->studentid) )
            {// Установка студента по-умолчанию
                // Студент по договору должен быть создан
                $saveobj->studentid = 0;
            }
            if ( ! isset($saveobj->clientid) )
            {// Установка клиента по-умолчанию
                // Клиент по договору будет учеником
                $saveobj->clientid = null;
            }
            if ( ! isset($saveobj->notes) || empty($saveobj->notes) )
            {// Установка заметок о договоре по-умолчанию
                $saveobj->notes = null;
            }
            if ( ! isset($saveobj->departmentid) || empty($saveobj->departmentid) )
            {// Установка подразделения по-умолчанию
                $saveobj->departmentid = $this->dof->storage('departments')->get_default_id();
            }
            if ( ! isset($saveobj->contractform) || empty($saveobj->contractform) )
            {// Установка типа формы договора по-умолчанию
                $saveobj->contractform = null;
            }
            if ( ! isset($saveobj->organizationid) || empty($saveobj->organizationid) )
            {// Установка привязки к организации по-умолчанию
                $saveobj->organizationid = null;
            }
            if ( ! isset($saveobj->curatorid) || empty($saveobj->curatorid) )
            {// Установка куратора по-умолчанию
                $saveobj->curatorid = null;
            }
            if ( ! isset($saveobj->enddate) || empty($saveobj->enddate) )
            {// Установка даты окончания договора по-умолчанию
                $saveobj->enddate = null;
            }
            if ( ! isset($saveobj->metacontractid) || empty($saveobj->metacontractid) )
            {// Установка привязки к метаконтракту по-умолчанию
                $saveobj->metacontractid = null;
            }
        
            // Установка автоматически генерируемых полей
            if ( ! $this->dof->plugin_exists('workflow', 'contracts') )
            {// Плагин статусов договоров не активен, установка статуса по-умолчанию
                $saveobj->status = 'tmp';
            } else
            {// Статус назначается в плагине статусов
                unset($saveobj->status);
            }
            $saveobj->adddate = $currenttime;
        
            // Нормализация полей
            $saveobj->sellerid = (int)$saveobj->sellerid;
            $saveobj->studentid = (int)$saveobj->studentid;
            $saveobj->adddate = (int)$saveobj->adddate;
            $saveobj->departmentid = (int)$saveobj->departmentid;
            $saveobj->organizationid = (int)$saveobj->organizationid;
            if ( $saveobj->metacontractid !== null )
            {
                $saveobj->metacontractid = (int)$saveobj->metacontractid;
            }
            
        }
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->date) && $saveobj->date != null )
        {
            if ( ! is_int_string($saveobj->date) )
            {// Время представлено в строковом формате
                $saveobj->date = strtotime($saveobj->date);
            }
            $saveobj->date = (int)$saveobj->date;
        }
        if ( isset($saveobj->enddate) && $saveobj->enddate != null )
        {
            if ( ! is_int_string($saveobj->enddate) )
            {// Время представлено в строковом формате
                $saveobj->enddate = strtotime($saveobj->enddate);
            }
            $saveobj->enddate = (int)$saveobj->enddate;
        }

        // ВАЛИДАЦИЯ ДАННЫХ
        // Проверки на возможность сохранения договора
        if ( isset($saveobj->sellerid) && $saveobj->sellerid && ! $this->dof->storage('persons')->is_exists($saveobj->sellerid) )
        {// Указанный агент не найден
            throw new dof_exception_dml('seller_not_found');
            return false;
        }
        if ( isset($saveobj->clientid) && $saveobj->clientid && ! $this->dof->storage('persons')->is_exists($saveobj->clientid) )
        {// Указанный клиент не найден
            throw new dof_exception_dml('client_not_found');
            return false;
        }
        if ( isset($saveobj->studentid) && $saveobj->studentid && ! $this->dof->storage('persons')->is_exists($saveobj->studentid) )
        {// Указанный студент не найден
            throw new dof_exception_dml('student_not_found');
            return false;
        }
        if ( isset($saveobj->curatorid) && $saveobj->curatorid && ! $this->dof->storage('persons')->is_exists($saveobj->curatorid) )
        {// Указанный куратор не найден
            throw new dof_exception_dml('curator_not_found');
            return false;
        }
        if ( isset($saveobj->num) )
        {// Проверка уникальности договора
            $contractid = 0;
            if ( isset($saveobj->id) )
            {
                $contractid = (int)$saveobj->id;
            }
            if ( ! $this->is_unique($saveobj->num, $contractid) )
            {// Договор не уникален
                throw new dof_exception_dml('contract_notunique_num');
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
     *              int - ID договора
     *              string - Номер договора
     *              array|stdClass - Комплексные данные
     *          При передачи комплексных данных можно также указать данные по
     *          зависимым элементам, что приведет к их обработке.
     *              Пример: $data->studentid->email = useremail
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
            {// Номер объекта
                $data = ['num' => $data];
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
            $options['reportcode'] = 'contract';
        }
    
        // ОПРЕДЕЛЕНИЕ БАЗОВЫХ ДАННЫХ ДЛЯ ПРОЦЕССА ИМПОРТА
        $importobject = null;
        $currentuser = $this->dof->storage('persons')->get_bu();
        $currentuserid = 0;
        if ( isset($currentuser->id) )
        {
            $currentuserid = (int)$currentuser->id;
        }
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
            if ( $this->dof->plugin_exists('workflow', 'contracts') )
            {
                $statuses = (array)$this->dof->workflow('contracts')->get_meta_list('real');
                $statuses = array_keys($statuses);
            } else
            {
                $statuses = ['new', 'clientsign', 'studreg', 'wesign', 'work', 'frozen', 'archives'];
            }
            $params['status'] = $statuses;
            // Поиск договора по ID
            if ( isset($data['id']) && ! empty($data['id']) )
            {
                $params['id'] = $data['id'];
                $contracts = $this->get_records($params);
                $objects = $contracts + $objects;
                unset($params['id']);
            }
            // Поиск договора по номеру
            if ( isset($data['num']) && ! empty($data['num']) )
            {
                $params['num'] = $data['num'];
                $contracts = $this->get_records($params);
                $objects = $contracts + $objects;
                unset($params['num']);
            }
    
            // ОБРАБОТКА НАЙДЕННЫХ ОБЪЕКТОВ
            if ( empty($objects) )
            {// Объекты не найдены
                
                // Исполнение действия в зависимости от настроек
                switch ( $options['notexist_action'] )
                {
                    // Попытка создать договор на основе переданных данных
                    case 'create' :

                        // Обработка персон договора
                        if ( isset($data['studentid']) )
                        {// Обработка студента по договору
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'studentid';
                            $importoptions['notexist_action'] = 'create';
                            $person = $this->dof->storage('persons')->import(
                                $data['studentid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['studentid'] = 0;
                            if ( ! empty($subreport['subreports']['studentid']['error']) )
                            {// Ошибки во время импорта студента
                                $subreport['error'] = 'error_import_student_import';
                                $subreport['errortype'] = 'create';
                            } else 
                            {// Импорт студента прошел успешно
                                if ( isset($person->id) )
                                {
                                    $data['studentid'] = (int)$person->id;
                                }
                            }
                        } else 
                        {// Студент по договору не определен
                            $subreport['error'] = 'error_import_student_not_set';
                        }
                        if ( isset($data['sellerid']) )
                        {// Обработка менеджера по договору
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'sellerid';
                            $importoptions['notexist_action'] = 'create';
                            $person = $this->dof->storage('persons')->import(
                                $data['sellerid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['sellerid'] = null;
                            if ( ! empty($subreport['subreports']['sellerid']['error']) )
                            {// Ошибки во время импорта менеджера
                                $subreport['error'] = 'error_import_seller_import';
                                $subreport['errortype'] = 'create';
                            } else
                            {// Импорт менеджера прошел успешно
                                if ( isset($person->id) )
                                {
                                    $data['sellerid'] = (int)$person->id;
                                }
                            }
                        }
                        if ( isset($data['clientid']) )
                        {// Обработка Законного представителя по договору
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'clientid';
                            $importoptions['notexist_action'] = 'create';
                            $person = $this->dof->storage('persons')->import(
                                $data['clientid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['clientid'] = null;
                            if ( ! empty($subreport['subreports']['clientid']['error']) )
                            {// Ошибки во время импорта законного представителя
                                $subreport['error'] = 'error_import_client_import';
                                $subreport['errortype'] = 'create';
                            } else
                            {// Импорт законного представителя прошел успешно
                                if ( isset($person->id) )
                                {
                                    $data['clientid'] = (int)$person->id;
                                }
                            }
                        }
                        if ( isset($data['curatorid']) )
                        {// Обработка Куратора по договору
                            
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'curatorid';
                            $importoptions['notexist_action'] = 'create';
                            $person = $this->dof->storage('persons')->import(
                                $data['curatorid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['curatorid'] = null;
                            if ( ! empty($subreport['subreports']['curatorid']['error']) )
                            {// Ошибки во время импорта куратора
                                $subreport['error'] = 'error_import_curator_import';
                                $subreport['errortype'] = 'create';
                            } else
                            {// Импорт куратора прошел успешно
                                if ( isset($person->id) )
                                {
                                    $data['curatorid'] = (int)$person->id;
                                }
                            }
                        }
                        
                        $subreport['action'] = 'save';
                        // Нормализация подразделения
                        if ( ! isset($data['departmentid']) )
                        {
                            $data['departmentid'] = $options['departmentid'];
                        }
                        // Нормализация персон договора
                        if ( ! isset($data['sellerid']) || empty($data['sellerid']) )
                        {
                            $data['sellerid'] = $currentuserid;
                        }
                        if ( isset($data['studentid']) && ( isset($data['clientid']) || empty($data['clientid']) ) )
                        {
                            $data['clientid'] = (int)$data['studentid'];
                        }
                        
                        if ( $options['simulation'] )
                        {// Симуляция процесса сохранения договора
                            try
                            {
                                $importobject = $this->normalize($data, $options);
                            } catch ( dof_exception_dml $e )
                            {// Ошибка проверки договора
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
                            {// Ошибка сохранения договора
                                $subreport['error'] = $e->errorcode;
                                $subreport['errortype'] = 'create';
                            }
                        }
                        break;
                    case 'error' :
                    default :
                        $subreport['error'] = 'error_import_contract_not_found';
                        $subreport['errortype'] = 'notexist';
                        break;
                }
            }
            if ( count($objects) > 1 )
            {// Найдено несколько договоров
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
                        $subreport['error'] = 'error_import_contract_multiple_found';
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
    
    /** Получить список контрактов, заключенных продавцом
     * @param int $pid - id персоны-продавца
     * @return array - список контрактов
     * @access public
     */
    public function get_list_by_seller($pid = NULL)
    {
        // Только для кураторов: если не указан, берем текущую персону
        if (is_null($pid))
        {   // Берем id текущего пользователя
            if ( ! $seller = $this->dof->storage('persons')->get_bu() )
            {
                return array();
            }
            $pid = $seller->id;
        }
        return $this->get_records(array('sellerid'=>$pid), 'id ASC');
    }
    /** Получить список контрактов, заключенных данным клиентом
     * @param int $pid - id персоны-продавца
     * @return array - список контрактов
     * @access public
     */
    public function get_list_by_client($pid = NULL)
    {
        if (is_null($pid))
        {   // Берем id текущего пользователя
            if ( ! $client = $this->dof->storage('persons')->get_bu() )
            {
                return array();
            }
            $pid = $client->id;
        }
        return $this->get_records(array('clientid'=>$pid), 'id ASC');
    }
    /** Получить список актуальных контрактов, в которых участвует этот студент
     * @param int $pid - id персоны-продавца
     * @return array - список контрактов
     * @access public
     */
    public function get_list_by_student($pid = NULL)
    {
        if (is_null($pid))
        {   // Берем id текущего пользователя
            if ( ! $student = $this->dof->storage('persons')->get_bu() )
            {
                return array();
            }
            $pid = $student->id;
        }
        // Ищем по personid
        $pid = (int) $pid;
        $sql = "studentid = {$pid} AND ";
        
        // Ищем по статусу
        // Получаем фрагмент sql, содержащий поиск по списку актуальных статусов
        $sql .= $this->query_part_select('status', array_flip($this->dof->workflow('contracts')->get_list_actual()));
        
        
        return $this->get_records_select($sql, null, 'id ASC', '*');
        //return $this->get_list('studentid', (int) $pid,'id ASC' );
    }
    
    /** Получить список актуальных контрактов, в которых участвует этот студент
     * @param int $pid - id персоны-студента
     * @return array - список контрактов
     * @access public
     */
    public function get_list_by_student_age($pid = NULL)
    {
        if (is_null($pid))
        {    // Берем id текущего пользователя
            if ( ! $student = $this->dof->storage('persons')->get_bu() )
            {
                return array();
            }
            $pid = $student->id;
        }
        // Ищем по personid
        $pid = (int) $pid;
        $sql = "studentid = {$pid} AND ";
        // Ищем по статусу
        // Получаем фрагмент sql, содержащий поиск по списку актуальных статусов
        $sql .= $this->query_part_select('status', array_flip($this->dof->workflow('contracts')->get_list_actual_age()));
        return $this->get_records_select($sql, null, 'id ASC', 'id');
    }
    
    /** Получить список контрактов по статусу
     * @param string $status - статус
     * @return array - список контрактов
     * @access public
     */
    public function get_list_by_status($status,$depid = false)
    {
        if ( $depid )
        {// только для переданного подразделения
            return $this->get_records(array('status'=>(string)$status, 'departmentid'=>$depid,'id ASC'));
        }
        return $this->get_records(array('status'=>$status), 'id ASC');
    }

    /** Получить список id кураторов
     * @return array - список id кураторов
     * @access public
     */
    public function get_curator_ids()
    {
        $curatorids=array();
        $curators=$this->dof->storage("contracts")->get_records_select("curatorid>0",null,"","DISTINCT(curatorid) as idcurator");
        foreach($curators as $curator)
            $curatorids[]=$curator->idcurator;
        return $curatorids;
    }

    /** Есть ли другие активные договора, где используется учетная запись
     * @param int $pid - id пользователя 
     * @param int $except - id контракта, который надо исключить из поиска
     * @return bool
     * @access public
     */
    public function is_person_used($pid,$except=null)
    {
        $pid = (int) $pid;
        $select = " (clientid={$pid} OR studentid={$pid}) AND
         (status<>'cancel' AND status<>'archives') ";
        if ($except)
        {   // Задан контракт, который нужно исключить
            $except = (int) $except;
            $select .= " AND id<>{$except}";
        }
        //print $select;
        return (bool) $this->count_records_select($select);
        
    }
    /** Является ли данная персона куратором по данному контракту
     * @param int $contractid - id контракта в таблице contracts
     * @param int $personid - id проверяемого пользователя 
     * @return bool
     * @access public
     */
    public function is_seller($contractid = null,$personid = null)
    {
        if ( is_null($personid) )
        {//получаем id пользователя
            $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        }
        if ( ! $personid )
        {//что-то с id пользователя не чисто
            return false;
        }
        if ( is_null($contractid) )
        {//контракт не указан возвращаем да, 
            //если пользователь числится в базе как куратор 
            return $this->is_exists(array('sellerid'=>$personid));
        }
        //контракт указан, возвращаем да, 
        //если пользователь числится в базе как куратор этого контракта
        return $this->is_exists(array('sellerid'=>$personid, 'id'=>$contractid));
    }
    
    /**
     * Отвечает на вопрос - является ли данный пользователь студентом
     * Возвращает true, если пользователь числится студентом в контрактах со статусом
     * "подписан нами" или "действует" 
     * @param int $contractid - id контракта в таблице contracts
     * @param int $personid - id проверяемого пользователя по таблице persons  
     * @return bool true - если является студентом, иначе false
     */
    public function is_student($contractid = null,$personid = null)
    {
        if ( is_null($personid) )
        {//получаем id пользователя
            $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        }
        if ( ! $personid )
        {//что-то с id пользователя не чисто
            return false;
        }
        if ( is_null($contractid) )
        {//контракт не указан возвращаем да, 
            //если пользователь числится в базе как клиент
            return $this->is_exists(array('studentid'=>$personid));
        }
        //контракт указан, возвращаем да, 
        //если пользователь числится в базе как студента этого контракта
        return $this->is_exists(array('studentid'=>$personid, 'id'=>$contractid));
    }
    
   /**
     * Отвечает на вопрос - является ли данный пользователь клиентом
     * Возвращает true, если пользователь числится клиентом в контрактах со статусом
     * "подписан нами" или "действует" 
     * @param int $contractid - id контракта в таблице contracts
     * @param int $personid - id проверяемого пользователя по таблице persons  
     * @return bool true - если является студентом, иначе false
     */
    public function is_client($contractid = null, $personid = null)
    {
        if ( is_null($personid) )
        {//получаем id пользователя
            $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        }
        if ( ! $personid )
        {//что-то с id пользователя не чисто
            return false;
        }
        if ( is_null($contractid) )
        {//контракт не указан возвращаем да, 
            //если пользователь числится в базе как клиент
            return $this->is_exists(array('clientid'=>$personid)); 

        }
        //контракт указан, возвращаем да, 
        //если пользователь числится в базе как клиент этого контракта
        return $this->is_exists(array('clientid'=>$personid, 'id'=>$contractid));

    }
    

    /**
     * Если пользователь упомянут в контракте или он учитель либо админ - вернем true
     * @param int $userid - id пользователя (в moodle, fdo)
     * @param bool $except - id контракта, который надо исключить из поиска
     * @param string $where - идентификатор происхождения id пользователя 
     * mоodle - id из таблицы mdl_user, fdo - из таблицы persons 
     * @return bool
     */
    public function is_personel($userid, $except=null, $where = 'moodle' )
    {
        
        if ( 'moodle' == $where )
        {//передан id пользователя в moodle
            $mdluser = $userid;
            // найдем пользователя деканата
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id($mdluser) )
            {// пользователь не найден - укажем невозможный id
                $personid = -1;
            }
        }elseif ( 'fdo' == $where )
        {//передан id пользователя в деканате
            $personid = $userid;
            // найдем пользователя Moodle
            if ( ! $mdluser = $this->dof->storage('persons')->get_field($userid, 'mdluser') )
            {//пользователь не найден - укажем невозможный id 
                $mdluser = 0;
            }
        }else
        {
            return false;
        }
        //если пользователь упомянут в контракте или он учитель либо админ - вернем true
        return $this->dof->modlib('ama')->user(false)->is_teacher($mdluser) OR 
               $this->is_person_used($personid, $except) OR 
               $this->dof->storage('eagreements')->is_person_used($personid);
    }
    
    /** Получает ФИО продавца и его id по id контракта
     * @param int $id - id контракта
     * @return object - ФИО продавца и его id
     * или false
     */
    public function get_seller($id)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        if ( ! $contract = $this->get($id) )
        {// контракт не существует
            return false;
        }
        if ( empty($contract->sellerid)  )
        {// продавец не указан
            return false;
        }
        $seller = new stdClass();
        $seller->id = $contract->sellerid;
        $seller->name = $this->dof->storage('persons')->get_fullname($contract->sellerid);
        if ( ! $seller->name )
        {//не получили имя
            return false;
        }
        return $seller;
    }
    /** Получить список договоров для конкретной персоны
     * @param int $pid - id персоны
     * @return array - список контрактов
     * @access public
     */
    public function get_contracts_for_person($pid = null, $depid = false)
    {
        if (is_null($pid))
        {   // Берем id текущего пользователя
            if ( ! $student = $this->dof->storage('persons')->get_bu() )
            {
                return array();
            }
            $pid = $student->id;
        }
        // Ищем по personid
        $pid = (int) $pid;
        // для персоны как студента
        $sql = "studentid = {$pid} OR ";
        // для персоны как клиента
        $sql .= "clientid = {$pid} ";
        if ( $depid )
        {
            $sql = '('.$sql.') AND departmentid='.$depid;
        }
        return $this->get_records_select($sql, null,'id ASC', '*');
    }
    
    /** Возвращает списокконтрактов по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_listing($conds = null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
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
        // @todo - обработку можно было сделать пооптимизированней, не было на это времени
        $addwhere = '';
        if ( ! empty($conds->state) )
        {
            $addwhere = 'AND pr.passportaddrid=adr.id AND adr.region='.$conds->state;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds,'c.');
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tblpersons = $this->dof->storage('persons')->prefix().$this->dof->storage('persons')->tablename();
        $tblcontracts = $this->prefix().$this->tablename();
        if (strlen($select)>0)
        {// сделаем необходимые замены в запросе
            $select .= ' AND ';
        }
        $sql = "FROM {$tblcontracts} as c, {$tblpersons} as pr
                WHERE {$select} c.studentid=pr.id {$addwhere}";
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_sql("SELECT COUNT(*) {$sql}");
        }
        // Добавим сортировку
        $sql .= $this->get_orderby_listing($sort);

        return $this->get_records_sql("SELECT c.*, pr.sortname as sortname {$sql}",null, $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @param string $prefix - префикс к полям, если запрос составляется для нескольких таблиц
     * @return string
     */
    public function get_select_listing($inputconds,$prefix='')
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( isset($conds->personid) AND intval($conds->personid) )
        {
            $selects[] = " (".$prefix."clientid={$conds->personid} OR ".$prefix."studentid={$conds->personid} OR ".$prefix."sellerid={$conds->personid})";
            unset($conds->personid);
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
     * @param string $sort - поле для сортировки контрактов
     * @return string
     */
    public function get_orderby_listing($sort)
    {
        if ( empty($sort) OR $sort == 'sortname' )
        {
            return " ORDER BY pr.sortname"; 
        }
        return " ORDER BY c.{$sort}, pr.sortname";
    }
    
    /**
     * Обработка AJAX-запросов
     *
     * @param string $querytype - Тип запроса
     * @param int $depid - ID подразделения, для которого формируются действия
     * @param mixed $data - Дополнительные данные
     * @param int $objectid - Дополнительный ID
     *
     * @return array
     */
    public function widgets_field_variants_list($querytype = '', $depid = 0, $data = '', $objectid = 0, $additionaldata = null)
    {
        switch ( $querytype )
        {
            // Список договоров
            case 'contracts_list' :

                $sql = '';
                $params = [];
                $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();

                // Поиск по идентификатору
                $sql .= ' id = :id ';
                $params['id'] = (int)$data;
                
                // Получение персон, подходящих под запрос
                $availablepersons = (array)$this->dof->storage('persons')->get_list_search($data, $depid, true);
                if ( $availablepersons )
                {
                    $persons = array_keys($availablepersons);
                    $persons = implode(',', $persons);
                    $sql .= ' OR sellerid IN ('.$persons.')';
                    $sql .= ' OR clientid IN ('.$persons.')';
                    $sql .= ' OR studentid IN ('.$persons.')';
                    $sql .= ' OR curatorid IN ('.$persons.')';
                }
                // Условие для повышения скорости работы
                $num = str_replace('0', '', $data);
                if ( ! empty($num) && strlen($data) > 2 )
                {
                    $sql .= ' OR ('.$this->get_sql_like('num', ':num').')';
                    $params['num'] = (string)'%'.$data.'%';
                }
                
                if ( $depid )
                {// Фильтрация по подразделению
                    $filter = (array)$this->dof->storage('departments')->get_departments($depid);
                    $filter[$depid] = $depid;
                    $filter = array_keys($filter);
                    $departments = implode(',', $filter);
                    $sql = '( '.$sql.' ) AND departmentid IN ('.$departments.')';
                }
                
                if ( $this->dof->plugin_exists('workflow', 'contracts') )
                {// Фильтрация по статусам
                    $statuses = $this->dof->workflow('contracts')->get_meta_list('real');
                    $statuses = '"'.implode('","', array_keys($statuses)).'"';
                    $sql = ' ('.$sql.') AND status IN ('.$statuses.') ';
                }

                // Получение списка договоров
                $contracts = (array)$this->get_records_select($sql, $params, 'num ASC', 'id, num, studentid, date, departmentid');

                // Формирование итогового списка
                $result = [];
                foreach ( $contracts as $id => $contract )
                {
                    if ( $this->is_access('use', $id, null, $contract->departmentid) )
                    {
                        $stringvars = new stdClass();
                        $stringvars->date = dof_userdate($contract->date, '%d.%m.%Y', $usertimezone, false);
                        $stringvars->num = $contract->num;
                        $stringvars->personfullname = $this->dof->storage('persons')->get_fullname($contract->studentid);
                        
                        $obj = new stdClass;
                        $obj->id   = $id;
                        $obj->name = $this->dof->get_string('contract_fullname', 'contracts', $stringvars, 'storage');
                        $result[$id] = $obj;
                    }
                }

                return $result;
            default :
                return [ 0 => $this->dof->modlib('ig')->igs('choose')];
        }
    }
    
    /**
     * Автозаполнение договора на обучение персоны
     *
     * @param int $personid - ID персоны
     * @param int $options - Массив дополнительных опций
     *      ['switch_to_active'] (bool) Привести к активному статусу
     *      ['available_status'] (array) Допустимые статусы договоров для поиска аналогов
     *
     * @return stdClass - Результат добавления
     *          ->status - Статус работы
     *          ->message - Cообщение
     *          ->contract - Объект договора
     *          ->person - Объект персоны
     *          ->options - Массив опций работы
     */
    public function autocomplete_add_person_contract($personid, $options = [])
    {
        
        // Результат работы по-умолчанию
        $return = new stdClass();
        $return->status = 'ok';
        $return->message = '';
        $return->person = NULL;
        $return->contract = NULL;
        $return->options = $options;
    
        // Получение персоны
        $person = $this->dof->storage('persons')->get($personid);
        if ( empty($person) )
        {// Персона не найдена
            $return->status = 'error';
            $return->message = $this->dof->get_string('error_person_not_found', 'persons', $personid, 'storage');
            return $return;
        }
        // Добавление к результату
        $return->person = $person;
        
        // Поиск аналогичного договора
        $params = [];
        $params['studentid'] = $personid;
        $params['departmentid'] = $person->departmentid;
        if ( isset($options['available_status']) && ! empty($options['available_status']) )
        {// Доступные статусы указаны
             $params['status'] = array_keys($options['available_status']);
        }
        // Получение договоров
        $contracts = $this->get_records($params);
        
        $now = time();
        $return->contract = NULL;
        if ( ! empty($contracts) )
        {// Фильтрация найденных договоров по дате
            foreach ( $contracts as $contract )
            {
                if ( ! empty($contract->enddate) && $contract->enddate < $now )
                {// Договор закончился
                    continue;
                }
                if ( ! empty($contract->date) && $contract->date > $now )
                {// Договор не начался
                    continue;
                }
                $return->contract = $contract;
            }
        }
        
        if ( empty($return->contract) )
        {// Подходящий контракт на персону не найден
            // Автоматическое добавление договора на обучение
            $contract = new stdClass();
            $contract->studentid = $personid;
            $contract->clientid = $personid;
            $contract->departmentid = $person->departmentid;
            $contract->date = $now;
            $contract->status = 'tmp';
            $contractid = $this->insert($contract);
            if ( empty($contractid) )
            {// Договор не создан
                $return->status = 'error';
                $return->message = $this->dof->get_string('error_contract_not_created', 'contracts', NULL, 'storage');
                return $return;
            } else 
            {// Договор создан
                $return->contract = $this->get($contractid);
            }
        }
        
        if ( empty($return->contract) )
        {// Договор не найден
            $return->contract = NULL;
            $return->status = 'error';
            $return->message = $this->dof->get_string('error_contract_not_found', 'contracts', $contractid, 'storage');
            return $return;
        }
        
        if ( isset($options['switch_to_active']) && ! empty($options['switch_to_active']) && $return->contract->status != 'work' )
        {// Требуется перевести в активный статус
            $pluginexist = $this->dof->plugin_exists('workflow', 'contracts');
            if ( $pluginexist )
            {// Плагин статусов найден
                $this->dof->workflow('contracts')->change($return->contract->id, 'new');
                $this->dof->workflow('contracts')->change($return->contract->id, 'clientsign');
                $this->dof->workflow('contracts')->change($return->contract->id, 'wesign');
                $changesuccess = $this->dof->workflow('contracts')->change($return->contract->id, 'work');
                if ( empty($changesuccess) )
                {// Договор не найден
                    $return->status = 'error';
                    $return->message = $this->dof->get_string('error_contract_changestatus', 'contracts', $return->contract->id, 'storage');
                    return $return;
                }
            } else
            {// Плагин статусов не ключен
                $update = new stdClass();
                $update->status = 'work';
                $update->id = $return->contract->id;
                $changesuccess = $this->update($update);
                if ( empty($changesuccess) )
                {// Договор не найден
                    $return->status = 'error';
                    $return->message = $this->dof->get_string('error_contract_changestatus', 'contracts', $return->contract->id, 'storage');
                    return $return;
                }
            }
            // Обновить данные договора
            $return->contract = $this->get($return->contract->id);
        }
        
        // Реультат с договором персоны
        return $return;
    }
    
    
    
    /**
     * Получение номера нового договора по-умолчанию
     * 
     * @param int $id - ID договора, для которого требуется получить номер 
     * 
     * @return string - Номер договора
     */
    public function get_default_contractnum($id = 0)
    {
        $num = '';
        $time = time();
        if ( ! empty($id) )
        {// Получение текущего номера договора
            $contract = $this->get($id);
            if ( $contract )
            {
                $num = $contract->num;
                $time = $contract->date;
            }
        }
        
        if ( ! empty($num) )
        {// Номер определен
            return $num;
        }
        
        // Генерация номера по-умолчанию
        if ( empty($id) )
        {// Установка идентификатора по-умолчанию
            $lastid = (array)$this->get_records([], 'id DESC', 'id', 0, 1);
            $lastid = key($lastid);
            $id = (int)$lastid + 1;
        }
        
        $lap = 0;
        while ( empty($num) || $this->is_exists(['num' => $num]))
        {// Код не уникален
            // Генерация кода
            $num = sprintf('%06d', $id).'/'.date('y', $time).'/'.rand(10,99);
            if ( $lap++ > 100 )
            {
                return '';
            }
        }
        
        return $num;
    }
    
    /**
     * Проверка уникальности договора
     * 
     * @param string $num - Номер договора
     * @param number $currentid - ID текущего договора
     * @param array $options - Дополнительные опции работы
     * 
     * @return bool - Результат проверки уникальности
     * @throws dof_exception_dml - При ошибках определения уникальности
     */
    public function is_unique($num, $currentid = 0, $options = [])
    {
        // Нормализация входящих данных
        $num = trim((string)$num);
        $currentid = (int)$currentid;
        $options = (array)$options;
        
        if ( empty($num) )
        {// Номер не передан
            throw new dof_exception_dml('unique_checking_num_not_set');
        }
        
        // Получение договора по номеру без учета статусов
        $contracts = $this->get_records(['num' => $num], '', 'id');

        // Фильтрация текущего договора
        unset($contracts[$currentid]);
        
        if ( ! empty($contracts) )
        {// Договор не уникален
            return false;
        } else 
        {
            return true;
        }
    }
}

?>