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

/** 
 * 
 * Справочник Счетов
 * 
 */
class dof_storage_accounts extends dof_storage implements dof_storage_config_interface
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
        
    /** 
     * Метод, реализующий обновление плагина в системе
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
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2014120000;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /** 
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'accounts';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('config'=> 2011080900,
                                      'acl'     => 2011041800));
    }
    /** 
     * Определить, возможна ли установка плагина в текущий момент
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
    /** 
     * Получить список плагинов, которые уже должны быть установлены в системе,
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
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
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
            return true;
    }
    
    /** 
     * Требует наличия полномочия на совершение действий
     * 
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
        return true;
    }
    
    /** 
     * Обработать событие
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
    /** 
     * Запустить обработку периодических процессов
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
    /** 
     * Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
   
    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_accounts';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
     * 
     * Получить список параметров для фунции has_hight()
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

    /** 
     * Проверить права через плагин acl.
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
      
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();

        return $a;
    }

    /** 
     * Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        $config = array();
        return $config;
    }       
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Добавить новый счет в Справочник
     * 
     * Производит добавление счета на основе переданных данных
     * 
     * @param string $ptype - Тип плагина-владельца
     * @param string $pcode - Код плагина-владельца
     * @param int $objectid - ID объекта - владельца
     * @param string $code - код счета (первичный, бонусный и тд.)
     * @param string $type - тип счета (активный, пассивный)
     * @param string $name - имя счета
     * @param string|array $params - параметры для определения уникальности счета
     * 
     * @return int|bool - id счета, либо false в случае ошибки
     */
    public function add_account($ptype, $pcode, $objectid, $code, $type, $name, $params )
    {   
        // Готовим объект для добавления
        $insert = new stdClass();
        
        // Заполняем объект
        $insert->plugintype = $ptype;
        $insert->plugincode = $pcode;
        $insert->code = $code;
        $insert->objectid = $objectid;
        $insert->type = $type;
        $insert->createdate = date('U');
        $insert->md5code = $this->get_hash($params);
        $insert->name = $name;
        
        // Проверка на уникальность кода счета добавляемой записи
        if ( $this->get_record(array('md5code' => $insert->md5code)) )
        {// Такой счет уже есть в Справочнике
            return false;
        }
        
        return $this->insert($insert);
    }
    
    /**
     * Возвращает счета, удовлетворяющие переданным параметрам
     * 
     * Если статус не указан, то фильтрует удаленные записи
     * 
     * @param int $begindate - начальная дата создания договора
     * @param int $enddate - конечная дата создания договора
     * @param string $ptype - тип плагина-владельца
     * @param string $pcode - код плагина-владельца
     * @param int $objectid - id объекта-владельца
     * @param string $code - код счета
     * @param string $type - тип счета
     * @param string $name - имя счета
     * @param string $params - уникальные параметры счета
     * @param string $status - статус счета
     * @return array|bool - массив объектов или false в случае ошибки
     */
    public function find_accounts($begindate = 0, $enddate = 0, $ptype = '', $pcode = '', $objectid = 0, $code = '', $type = '', $name = '', $params = '', $status = '')
    {
        // Готовим фрагмент SQL 
        $sql = '';
        // Для определения начала строки (добавление AND между условиями)
        $start = false;
        // Массив плейсхолдеров
        $params = array();
        
        // Начальная дата
        if ( ( ! empty($begindate) ) && is_int($begindate) )
        {
            // Добавляем начальную дату
            $params['begindate'] = $begindate;
            $sql .= 'createdate >= :begindate';
            
            // Первый параметр есть, Теперь нужны AND.
            $start = true;
        }
        
        // Конечная дата
        if ( ( ! empty($enddate) ) && is_int($enddate)  )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем конечную дату
            $params['enddate'] = $enddate;
            $sql .= 'createdate <= :enddate';
            
            $start = true;
        }
        
        // Тип плагина - владельца
        if ( ( ! empty($ptype) ) && is_string($ptype) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем тип плагина
            $params['plugintype'] = addslashes($ptype);
            $sql .= 'plugintype = :plugintype';
            
            $start = true;
        }
        
        // Код плагина - владельца
        if ( ( ! empty($pcode) ) && is_string($pcode) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем код плагина
            $params['plugincode'] = addslashes($pcode);
            $sql .= 'plugincode = :plugincode';
            
            $start = true;
        }
        
        // ID объекта - владельца
        if ( ( ! empty($objectid) ) && is_int($objectid) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем id объекта - владельца
            $params['objectid'] = $objectid;
            $sql .= 'objectid = :objectid';
            
            $start = true;
        }
        
        // Код счета
        if ( ( ! empty($code) ) && is_string($code) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем код счета
            $params['code'] = addslashes($code);
            $sql .= 'code = :code';
            
            $start = true;
        }
        
        // Тип счета
        if ( ( ! empty($type) )  && is_string($type) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем тип счета
            $params['type'] = addslashes($type);
            $sql .= 'type = :type';
            
            $start = true;
        }
        
        // Имя счета
        if ( ( !  empty($name) )  && is_string($name) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем тип счета
            $params['name'] = addslashes($name);
            $sql .= 'name LIKE :name';
            
            $start = true;
        }
        
        // Код счета
        if ( ( ! empty($params) )  && is_string($params) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем код счета
            $params['md5code'] = $this->get_hash($params);
            $sql .= 'md5code = :md5code';
            
            $start = true;
        }
        
        // Статус счета
        if ( ( !  empty($status) )  && is_string($status) )
        {
            if ( $start )
            {
                $sql .= ' AND ';
            }
            // Добавляем статус счета
            $params['status'] = addslashes($status);
            $sql .= 'status = :status';
        } else 
        {
            // Статус не указан - фильтруем все мусорные счета
            
            // Получаем мусорные статусы
            $junkstatuses = $this->dof->workflow('accounts')->get_meta_list('junk');
            // Если есть мусорные статусы - добавляем фильтрацию по ним
            if (! empty($junkstatuses) )
            {
                foreach ( $junkstatuses as $status => $name )
                {
                    if ( $start )
                    {
                        $sql .= ' AND ';
                    }
                    
                    // Добавляем статус счета
                    $params['status'.$status] = $status;
                    $sql .= 'status <> :status'.$status.'';
                    
                    $start = true;
                }
            }
            
        }

        // Получаем результат запроса
        $records = $this->get_records_select($sql, $params);
        
        return $records;
        
    }
    
    /**
     * Получить md5
     * 
     * @param string|array $data - данные для формирования хэша
     * @return string $result - md5-строка 
     */
    private function get_hash($data)
    {
        if ( is_array($data) )
        {
            // Сериализуем массив
            $data = serialize($data);
            // Формируем хэш
            $result = md5($str);
        } else 
        {
            // Конвертируем в строку
            $data = (string) $data;
            // Формируем хэш
            $result = md5($data);
        }
        
        // Возвращаем хэш
        return $result;
    }
}  
?>