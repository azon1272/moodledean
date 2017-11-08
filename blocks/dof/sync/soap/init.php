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
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         //
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
/** Класс стандартных функций интерфейса
 *
 */
class dof_sync_soap implements dof_sync
{

    /**
     * @var dof_control
     */
    protected $dof;

    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return true;
    }

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        return true;
    }

    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2015020900;
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
        return 'ancistrus';
    }

    /** Возвращает тип плагина
     * @return string
     * @access public
     */
    public function type()
    {
        return 'sync';
    }

    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'soap';
    }

    /** Возвращает список плагинов,
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'  => array('ama' => 2009101500),
                     'storage' => array('persons' => 2014041000,
                                        'config'  => 2012042500));

    }

    /** Список обрабатываемых плагином событий
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array();
    }

    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
    }

    /**
     * Определить, включен ли плагин
     * 
     * @return bool
     * @todo: придумать что-нибудь с настройками (глобальные? по подразделениям?)
     */
    public function is_enabled()
    {
        // Возвращаем настройку, пользуясь стандартной логикой get_config_value.
        $params = new stdClass();
        $params->code = 'enabled';
        $params->plugintype = 'sync';
        $params->plugincode = 'soap';
        $params->value = 1;
        $isempty = $this->dof->storage('config')->get_listing($params);
        if ( ! empty($isempty) )
        {
            return true;
        }
        return false;
        // Чтобы включить SOAP-сервер, необходимо в departmentid=0 установить галочку 'enabled' = 1
        // 
        // return $this->dof->storage('config')->get_config_value('enabled', 'sync', 'soap', 1);
    }
    
    /** Обработать событие
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $id - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype, $gencode, $eventcode, $id, $mixedvar)
    {
        return true;
    }

    /** Запустить обработку периодических процессов
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan, $messages)
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
    public function todo($code, $intvar, $mixedvar)
    {
        return true;
    }

    // **********************************************
    // Собственные методы
    // **********************************************
    /** Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }

    /** Список настроек плагина
     * @param array $options  - дополнительные параметры, указаны для совместимости
     * 
     * return array
     */
    public function config_default($options = null)
    {
        // Используется ли плагин по-умолчанию
        $config = array();
        $obj = new stdClass();
        $obj->type  = 'checkbox';
        $obj->code  = 'enabled';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        // Страна по-умолчанию (set_person)
        $obj = new stdClass();
        $obj->type  = 'text';
        $obj->code  = 'defaultcountry';
        $obj->value = 'RU';
        $config[$obj->code] = $obj;
        $obj = new stdClass();
        // Регион по-умолчанию (set_person)
        $obj->type  = 'text';
        $obj->code  = 'defaultregion';
        $obj->value = 'RU-MOW';
        $config[$obj->code] = $obj;
        // Маршрут статусов персоны по-умолчанию (set_person)
        $obj = new stdClass();
        $obj->type  = 'text';
        $obj->code  = 'setpersonstatusroute';
        $obj->value = 'normal'; // названия статусов
        $config[$obj->code] = $obj;
        // Маршрут статусов контракта по-умолчанию (set_contract)
        $obj = new stdClass();
        $obj->type  = 'text';
        $obj->code  = 'setcontractstatusroute';
        $obj->value = 'new,clientsign,wesign,work'; // названия статусов по порядку
        $config[$obj->code] = $obj;
        // Менеджер по-умолчанию (set_contract)
        $obj = new stdClass();
        $obj->type  = 'text';
        $obj->code  = 'setcontractdefaultsellerid';
        $obj->value = '1'; // id из таблицы persons
        $config[$obj->code] = $obj;
        return $config;
    }

    /**
     * Проверить поля адреса
     * 
     * @param object $input - объект с полями справочника addresses
     * @return bool|string true, если проверка успешная, иначе - код ошибки
     */
    public function check_address($input)
    {
        $result = $this->check_column_types_null($input, 'addresses');
        if ( is_string($result) )
        {// Ошибка в типах
            return $result;
        }
        list($countries, $regions) = $this->dof->modlib('refbook')->get_country_regions();
        if ( !empty($input->country) )
        {
            if ( !array_key_exists($input->country, $countries) )
            {
                return 'SP21';
            }
            if ( !empty($input->region) )
            {
                if ( !empty($regions[$input->country]) )
                {// При условии что есть регионы у этой страны
                    if ( !array_key_exists($input->region, $regions[$input->country]) )
                    {
                        return 'SP22';
                    }
                }
            }
        } else
        {
            if ( !empty($input->region) )
            {
                return 'SP23';
            }
        }
        if ( !empty($input->streetname) AND empty($input->streettype) )
        {// Укажите тип улицы
            return 'SP19';
        }
        return true;
    }
    
    /**
     * Проверить поля запроса на предмет ненулевых значений и корректности типов справочника
     * 
     * @param object $input - объект SOAP-запроса
     * @param string $table - название справочника
     * @return bool|string - true в случае успешной проверки, или код ошибки: [SC6-SC7], [PR5-PR6]
     */
    public function check_column_types_null($input, $table)
    {
        if ( !$storage = $this->dof->storage($table) )
        {
            return 'SC6';
        }
        // Отфильтруем только нужные поля
        $columns = $this->get_fields($input, $table, true);
        $dbcolumnsall = $storage->get_columns();
        foreach ( $dbcolumnsall as $name => $columninfo )
        {
            if ( !isset($columns[$name]) )
            {
                unset($dbcolumnsall[$name]);
            }
        }
        // Пройдёмся по всем отфильтрованным полям и проверим на типы и null
        foreach ( $dbcolumnsall as $name => $columninfo )
        {
            // Если поле в справочнике не может быть пустым,
            // отсутствует авто-инкремент
            // с пустым значением по-умолчанию (но при этом '0' считается таким значением)
            // и во входящем запросе нет этого поля (но при этом '0' считается таким значением)
            if ( $columninfo->not_null == 1 
                    AND $columninfo->auto_increment != 1
                    AND empty($columninfo->default_value)
                    AND ($columninfo->default_value != 0 OR $columninfo->default_value != '0')
                    AND empty($input->$name)
                    AND ($input->$name !== 0 OR $input->$name !== '0') )
            {// Отсутствует значение в ненулевом поле
                return 'PR5';
            }
            if ( !is_null($input->$name) )
            {// Если есть значение
                switch ( $columninfo->type )
                {
                    case 'bigint':
                    case 'smallint':
                    case 'tinyint':
                    case 'integer':
                        if ( !is_int_string($input->$name) )
                        {// Некорректный тип переданного поля
                            return 'PR6';
                        }
                        break;
                    case 'char': 
                    case 'blob':
                    case 'text':
                    case 'varchar':
                        if ( !is_string($input->$name) )
                        {// Некорректный тип переданного поля
                            return 'PR6';
                        }
                        break;
                    case 'double':
                    case 'float':
                        if ( !is_float($input->$name) )
                        {// Некорректный тип переданного поля
                            return 'PR6';
                        }
                        break;
                    default:
                        // Неизвестный тип переменной
                        return 'SC7';
                }                
            }
        }
        return true;
    }
    
    /** Проверить дополнительный массив на предмет соответствия формату
     * 
     * @param array $cov - дополнительный вложенный массив
     * @param object $fields - поля объекта для проверки дублирующихся полей
     * @return bool|string - true, если ошибок не обнаружено 
     * или код ошибки: [PI6-PI8], [SC3]
     */
    public function check_cov($cov, $fields = null)
    {
        if ( !is_object($cov) )
        {// Тип переменной cov должен быть массивом
            return 'PI6';
        }
        if ( !is_null($fields) AND (!is_object($fields) OR empty($fields)) )
        {// Ошибка кодирования: Не верно передан формат проверки полей вложенного массива cov
            return 'SC3';
        }
        $keys = array();
        if ( !is_null($fields) )
        {
            $keys = get_object_vars($fields);
        }

        // Проверим каждое поле
        foreach ( $cov as $key => $value )
        {
            if ( is_object($value) OR is_array($value) )
            {// Поля внутри переменной cov не должны быть массивами или объектами
                return 'PI7';
            }
            if ( !empty($keys) )
            {
                if ( array_key_exists($key, $keys) )
                {// Поля внутри переменной cov не должны совпадать с полями справочника
                    return 'PI8';
                }
            }
        }
        return true;
    }

    /** Проверить, существует ли такое подразделение и актуальный ли у него статус
     * 
     * @param string $code - код подразделения
     * @return bool|string - true, если ошибок не обнаружено 
     * или код ошибки: [SP5-SP7]
     */
    public function check_department($code)
    {
        if ( !is_string($code) )
        {
            return 'SP7';
        }
        $actualstatuses = $this->dof->workflow('departments')->get_meta_list('actual');
        $conds = array('code' => $code);
        $status = $this->dof->storage('departments')->get_field($conds, 'status');
        if ( !$this->dof->storage('departments')->is_code_notunique($code) )
        {
            return 'SP5';
        } else if ( !array_key_exists($status, $actualstatuses) )
        {
            return 'SP6';
        }
        return true;
    }

    /** Проверяет, можно ли использовать переданный e-mail в системе
     * @param $email
     * @return bool|string true если ошибок не нашли, или код ошибки: [SP2], [PI4]
     */
    public function check_email($email)
    {
        // Проверим на допустимые символы
        if ( !$this->dof->modlib('ama')->user(false)->validate_email($email) )
        {
            return 'PI4';
        }
        // Проверим, есть ли почта в чёрном списке
        if ( $error = $this->dof->modlib('ama')->user(false)->email_is_not_allowed($email) )
        {
            return 'SP2';
        }
        return true;
    }

    /** Проверяет входящий запрос по sha1-хешу, сверяя наличие требуемых полей и
     * выдаёт код ошибки при её наличии
     * 
     * @param object $input - содержит поля запроса, а так же обязательные поля 
     * id, requesttime, requestlogin, requesthash
     * @return bool|string - true в случае успеха, иначе - код ошибки: [PR0-PR4], [PI3]
     */
    public function check_hash($input)
    {
        // Клонируем, чтобы не возникло проблем с "порчей" объекта запроса
        $inputc = clone $input;
        if ( !is_object($inputc) )
        {
            return 'PR0';
        }
        // Проверяем обязательные поля
        $requiredfields = array('PR4' => 'id',
                                'PR1' => 'requestlogin',
                                'PR2' => 'requesttime',
                                'PR3' => 'requesthash');
        foreach ( $requiredfields as $errorcode => $field )
        {
            if ( !isset($inputc->$field) OR empty($inputc->$field) )
            {
                return $errorcode;
            }
        }
        // Проверяем контрольную сумму
        $requesthash = $inputc->requesthash;
        $requestpassword = $this->get_key($inputc->requestlogin);
        if ( $requesthash != $this->hash_object($inputc, $requestpassword) )
        {
            return 'PI3';
        }
        return true;
    }

    /** Проверяет объект SOAP-запроса на предмет некорректных значений полей
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->id - Внешний id метаконтракта
     *      ->num - Номер метаконтракта
     *      ->departmentcode - Код подразделения
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return bool|string - true в случае успеха, иначе - код ошибки:
     *  [SP4-SP7], [PI5-PI8], [SC3]
     */
    public function check_fields_set_meta_contract($input)
    {
        if ( !is_string($input->num) OR is_null($input->num) )
        { // Название метаконтракта не может быть пустым
            return 'SP4';
        }
        if ( !is_null($input->departmentcode) )
        { // Код передали, он должен быть строкового типа
            if ( !is_string($input->departmentcode) )
            {
                return 'PI5';
            }
            // Проверяем, существует ли такое подразделение и его статус
            $errorcode = $this->check_department($input->departmentcode);
            if ( is_string($errorcode) )
            {
                return $errorcode;
            }
        }

        // Проверим cov
        if ( !empty($input->cov) )
        {
            $errorcode = $this->check_cov($input->cov);
            if ( is_string($errorcode) )
            {
                return $errorcode;
            }
        }
        return true;
    }

    /** Проверяет объект SOAP-запроса на предмет некорректных значений полей
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->id - Внешний id контракта
     *      ->date - Дата заключения в UTS
     *      ->sellerid - Менеджер по работе с клиентами (приемная комиссия, партнер) - добавляет договор, меняет статус до "подписан клиентом", отслеживает статус договора и ход обучения (id по таблице persons)
     *      ->clientid - Клиент, оплачивающий обучение (законный представитель, сам совершеннолетний ученик или куратор от организации, может принимать значение 0 или null, если клиент создается, а контракт имеет черновой вариант) (по таблице persons)
     *      ->studentid - Ученик (может принимать значение 0, если ученик создается, а контракт имеет черновой вариант) (по таблице persons)
     *      ->notes - Заметки
     *      ->departmentcode - Подразделение в таблице departments, к которому приписан контракт на обучение (например, принявшее ученика)
     *      ->curatorid - Куратор или классный руководитель данного ученика (по таблице persons или не указан), отслеживает учебный процесс, держит связь с учеником, является посредником между учеником и системой, может быть внешней персоной.
     *      ->metacontractid - id метаконтракта, к которому привязан договор, в таблице metacontracts
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return bool|string - true в случае успеха, иначе - код ошибки: []
     */
    public function check_fields_set_contract($input)
    {
        // Проверим все поля на соответствие с базой данных
        $result = $this->check_column_types_null($input, 'contracts');
        if ( is_string($result) )
        {// Возникла ошибка
            return $result;
        }
        // date, clientid, studentid не могут быть пустыми
        $notempty = array('studentid' => 'SP24',
                          'clientid'  => 'SP25',
        // sellerid может быть пока пустой
//                          'sellerid'  => 'SP26',
                          'date'      => 'SP27');
        foreach ( $notempty as $field => $errorcode )
        {
            if ( empty($input->$field) )
            {
                return $errorcode;
            }
        }
        $departmentid = null;
        if ( !empty($input->departmentcode) )
        { // Код передали, он должен быть строкового типа
            // Проверяем, существует ли такое подразделение и его статус
            $errorcode = $this->check_department($input->departmentcode);
            if ( is_string($errorcode) )
            {
                return $errorcode;
            }
            $result = $this->get_departmentid($input->departmentcode);
            if ( is_int_string($result) )
            {// Получили номер подразделения
                $departmentid = $result;
            }
        }
        // Проверим, верные ли передали id персон
        $persons = array('studentid' => 'SP28',
                         'clientid'  => 'SP29',
                         'sellerid'  => 'SP30',
                         'curatorid' => 'SP31');
        foreach ( $persons as $personid => $errorcode )
        {
            if ( empty($input->$personid) )
            {
                if ( $personid == 'sellerid' )
                {// Если не передали менеджера попробуем взять по-умолчанию
                    $defaultsellerid = $this->dof->storage('config')
                        ->get_config_value('setcontractdefaultsellerid', 'sync', 'soap', $departmentid);
                    if ( empty($defaultsellerid) OR !$this->dof->storage('persons')->is_exists($defaultsellerid) )
                    {// Менеджер по-умолчанию не найден
                        return 'SP32';
                    }
                }
            } else
            {
                $result = $this->is_sync_object_exists('persons', $input->requestlogin, $input->$personid);
                if ( is_string($result) )
                {
                    return $result;
                } else if ( $result === false )
                {
                    return $errorcode;
                }
            }
        }
        // Проверим, есть ли такой метаконтракт
        if ( !empty($input->metacontractid) )
        {
            $result = $this->is_sync_object_exists('metacontracts', $input->requestlogin, $input->metacontractid);
            if ( is_string($result) )
            {// Произошла ошибка
                return $result;
            } else if ( $result === false )
            {// Метаконтракт не найден
                return 'SP33';
            }
        }
        // Проверим дату
        if ( !is_int_string($input->date) )
        {// Дата заключения договора не в корректном формате
            return 'SP34';
        }
        // Проверим, пытается ли пользователь сменить персону в действующем контракте
        $result = $this->is_sync_object_exists('contracts', $input->requestlogin, $input->id);
        if ( is_string($result) )
        {// Произошла ошибка
            return $result;
        } else if ( $result === true )
        {// Контракт найден, достанем его
            $contract = $this->get_sync_object('contracts', $input->requestlogin, $input->id);
            if ( !$inputstudent = $this->get_sync_object('persons', $input->requestlogin, $input->studentid) )
            {// Не найден студент, на которого заключён договор
                return 'SP35';
            }
            if ( $contract->studentid != $inputstudent->id )
            {// Нельзя изменить студента, на которого заключён договор
                return 'SP36';
            }
            // Номер контракта изменяем
            if ( !empty($input->num) AND ($contract->num != $input->num) )         
            {// Номер договора должен быть уникальным.
                if ( $this->dof->storage('contracts')->get_records(array('num'=>$input->num)) )
                {
                    return 'SP37';
                }
            }
        } else
        {// Контракт не создан
            // Номер контракта.
            if ( !empty($input->num) AND $this->dof->storage('contracts')->
             get_records(array('num'=>$input->num)) )         
            {// Номер договора должен быть уникальным.
                return 'SP37';
            }
        }
        
        return true;
    }

    /** Проверяет объект SOAP-запроса на предмет некорректных значений полей
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->id - Внешний id метаконтракта
     *      ->firstname - Имя
     *      ->middlename - Отчество
     *      ->lastname - Фамилия
     *      ->preferredname - Префикс для имения (Mr. Dr. Г-н, Г-а)
     *      ->dateofbirth - Дата рождения в UTS
     *      ->gender - Пол (male, female, unknown)
     *      ->email - Основной адрес электронной почты
     *      ->phonehome - Домашний телефон
     *      ->phonework - Рабочий телефон
     *      ->phonecell - Сотовый телефон
     *      ->passtypeid - Тип удостоверения личности (1 - свидетельство о рождении, 2 - паспорт гражданина РФ, 3 - загранпасспорт, 4 - разрешение на временное проживание лица без гражданства, 5 - вид на жительство, 6 - военный билет, 7 - водительсткое удостоверение пластиковое, 8 - вод. удостоверение форма 1, 9 - вод. удостоверение международное)
     *      ->passportserial - Серия удостоверения личности (если предусмотрена типом документа)
     *      ->passportnum - Номер удостоверения личности
     *      ->passportdate - Дата выдачи удостоверения личности в UTS
     *      ->passportem - Название организации, выдавшей удостоверение личности
     *      ->citizenship - Гражданство
     *      ->departmentcode - Основной отдел, к которому приписан человек (может редактировать его данные в persons)
     *      ->about - Характеристика личности
     *      ->skype - Уникальный идентификатор в Skype
     *      ->phoneadd1 - Дополнительный телефон 1
     *      ->phoneadd2 - Дополнительный телефон 2
     *      ->phoneadd3 - Дополнительный телефон 3
     *      ->emailadd1 - Дополнительная электронная почта 1
     *      ->emailadd2 - Дополнительная электронная почта 2
     *      ->emailadd3 - Дополнительная электронная почта 3
     *      ->passportaddr - Адрес прописки по паспорту (для генерации документов)
     *      ->address - Текущий адрес (почтовый адрес)
     *      ->birthaddress - Адрес рождения персоны
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return bool|string - true в случае успеха, иначе - код ошибки: []
     */
    public function check_fields_set_person($input)
    {
        // Проверим все поля на соответствие с базой данных
        $result = $this->check_column_types_null($input, 'persons');
        if ( is_string($result) )
        {// Возникла ошибка
            return $result;
        }
        // Имя, фамилия, email, дата рождения, пол не могут быть пустыми
        $notempty = array('firstname'   => 'SP9',
                          'lastname'    => 'SP10',
                          'email'       => 'SP11',
                          'dateofbirth' => 'SP12',
                          'gender'      => 'SP13');
        foreach ( $notempty as $field => $errorcode )
        {
            if ( empty($input->$field) )
            {
                return $errorcode;
            }
        }
        if ( !empty($input->departmentcode) )
        { // Код передали, он должен быть строкового типа
            if ( !is_string($input->departmentcode) )
            {
                return 'PI5';
            }
            // Проверяем, существует ли такое подразделение и его статус
            $errorcode = $this->check_department($input->departmentcode);
            if ( is_string($errorcode) )
            {
                return $errorcode;
            }
        }
        if ( $input->gender != 'male' AND $input->gender != 'female' )
        {// Укажите корректный пол: Мужской (male) или Женский" (female)
            return 'SP14';
        }
        $result = $this->check_email($input->email);
        if ( is_string($result) )
        {// Ошибка с почтой (в чёрном списке или это не почта)
            return $result;
        }
        // Проверим, существует ли такая персона в базе
        $syncperson = $this->get_sync_object('persons', $input->requestlogin, $input->id);
        if ( is_string($syncperson) )
        {// Возникла ошибка.
            return $syncperson;
        } else if ( $syncperson !== false )
        {
            // Пользователь уже зарегистрирован, проверим, пытаемся ли мы изменить email?
            if ( $syncperson->email != $input->email )
            {
                if ( !$this->dof->storage('persons')->is_email_unique($input->email) )
                {// Данный e-mail уже зарегистрирован в системе
                    return 'SP3';
                }
            }
        } else
        {// Пользователь не зарегистрирован.
            if ( !$this->dof->storage('persons')->is_email_unique($input->email) )
            {// Данный e-mail уже зарегистрирован в системе.
                return 'SP3';
            }
        }
        
        if ( !empty($input->passtypeid) )
        {
            if ( !$this->dof->modlib('refbook')->pasport_type($input->passtypeid) )
            {// Укажите правильный тип удостоверения личности
                return 'SP15';
            }
            if ( empty($input->passportserial) )
            {// Укажите серию паспорта
                return 'SP16';
            }
            if ( empty($input->passportnum) )
            {// Укажите номер паспорта
                return 'SP17';
            }
            if ( empty($input->passportem) )
            {// Укажите место выдачи паспорта
                return 'SP18';
            }
        }
        if ( !empty($input->skype) AND !preg_match('/^[a-z][a-z0-9\.,\-_]{5,31}$/i', $input->skype) )
        {// Логин Skype содержит недопустимые символы
            return 'SP20';
        }
        // Дополнительные почтовые адреса
        $emails = array('emailadd1', 'emailadd2', 'emailadd3');
        foreach ( $emails as $email )
        {
            if ( !empty($input->$email) )
            {
                $result = $this->check_email($input->$email);
                if ( is_string($result) )
                {// Ошибка с почтой
                    return $result;
                }
            }
        }
        // Проверка адресов
        $addresses = array('passportaddr', 'address', 'birthaddress');
        foreach ( $addresses as $address )
        {
            if ( !empty($input->$address) )
            {
                $result = $this->check_address($input->$address);
                if ( is_string($result) )
                {// Ошибка с адресом
                    return $result;
                }
            }
        }
        // @todo: проверка массива cov для измененных ФИО
        // Проверки, если предыдущее поле не заполнено, а текущее заполнено, ошибка!
        if ( !empty($input->cov) )
        {
            $fields = new stdClass();
            $fields->firstname  = $input->firstname;
            $fields->lastname   = $input->lastname;
            if ( !empty($input->middlename) )
            {
                $fields->middlename = $input->middlename;
            }

            $postfixesf = array('','2');
            $postfixesn = array('2', '1');
            $prefixesf = array('','old');
            $prefixesn = array('old', 'old');
            $namefields = array('firstname', 'lastname', 'middlename');
            $index = 0; // для организации двух индексов
            foreach ( $postfixesn as $postfix )
            {
                foreach ( $namefields as $field )
                {
                    $prevfieldname = $prefixesf[$index] . $field . $postfixesf[$index];
                    $nextfieldname = $prefixesn[$index] . $field . $postfix;
                    if ( !empty($input->cov->$nextfieldname) )
                    {
                        $fields->$nextfieldname = $input->cov->$nextfieldname;
                    }
                    if ( empty($fields->{$prevfieldname}) AND !empty($fields->{$nextfieldname}) )
                    {
                        return 'SP23';
                    }
                }
                $index++;
            }
        }

        return true;
    }

    /** Создать соединение с объектом синхронизации по названию справочника
     * и поставщику данных
     *
     * @param string $pcode - название плагина для синхронизации (metacontracts, persons, ...)
     * @param string $provider - название поставщика синхронизации
     * @param string $substorage - код внутреннего субсправочника
     * @return bool|object - false в случае ошибки, или объект подключения к синхронизации dof_storage_sync_connect
     */
    private function connect_sync($pcode, $provider, $substorage = null)
    {
        if ( !is_string($pcode) OR ! is_string($provider)
                OR ( !is_string($substorage) AND ! is_null($substorage)) )
        {
            return false;
        }
        return $this->dof->storage('sync')->createConnect('storage', $pcode,
            $substorage, $this->type(), $this->code(), $provider);
    }

    /** Исполняет SOAP-запрос set_meta_contract()
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->id - Внешний id метаконтракта
     *      ->num - Номер метаконтракта
     *      ->departmentcode - Код подразделения
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return object|string - object в случае успеха, иначе - код ошибки: []
     */
    public function execute_set_meta_contract($input)
    {
        return $this->execute_set_base($input, 'set_meta_contract');
    }

    /** Исполняет SOAP-запрос set_contract()
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->id - Внешний id контракта
     *      ->date - Дата заключения в UTS
     *      ->sellerid - Менеджер по работе с клиентами (приемная комиссия, партнер) - добавляет договор, меняет статус до "подписан клиентом", отслеживает статус договора и ход обучения (id по таблице persons)
     *      ->clientid - Клиент, оплачивающий обучение (законный представитель, сам совершеннолетний ученик или куратор от организации, может принимать значение 0 или null, если клиент создается, а контракт имеет черновой вариант) (по таблице persons)
     *      ->studentid - Ученик (может принимать значение 0, если ученик создается, а контракт имеет черновой вариант) (по таблице persons)
     *      ->notes - Заметки
     *      ->departmentcode - Подразделение в таблице departments , к которому приписан контракт на обучение (например, принявшее ученика)
     *      ->curatorid - Куратор или классный руководитель данного ученика (по таблице persons или не указан), отслеживает учебный процесс, держит связь с учеником, является посредником между учеником и системой, может быть внешней персоной.
     *      ->metacontractid - id метаконтракта, к которому привязан договор, в таблице metacontracts
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return bool|string - true в случае успеха, иначе - код ошибки: []
     */
    public function execute_set_contract($input)
    {
        return $this->execute_set_base($input, 'set_contract');
    }

    /** Исполняет SOAP-запрос set_person()
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->id - Внешний id метаконтракта
     *      ->firstname - Имя
     *      ->middlename - Отчество
     *      ->lastname - Фамилия
     *      ->preferredname - Префикс для имения (Mr. Dr. Г-н, Г-а)
     *      ->dateofbirth - Дата рождения в UTS
     *      ->gender - Пол (male, female, unknown)
     *      ->email - Основной адрес электронной почты
     *      ->phonehome - Домашний телефон
     *      ->phonework - Рабочий телефон
     *      ->phonecell - Сотовый телефон
     *      ->passtypeid - Тип удостоверения личности (1 - свидетельство о рождении, 2 - паспорт гражданина РФ, 3 - загранпасспорт, 4 - разрешение на временное проживание лица без гражданства, 5 - вид на жительство, 6 - военный билет, 7 - водительсткое удостоверение пластиковое, 8 - вод. удостоверение форма 1, 9 - вод. удостоверение международное)
     *      ->passportserial - Серия удостоверения личности (если предусмотрена типом документа)
     *      ->passportnum - Номер удостоверения личности
     *      ->passportdate - Дата выдачи удостоверения личности в UTS
     *      ->passportem - Название организации, выдавшей удостоверение личности
     *      ->citizenship - Гражданство
     *      ->departmentcode - Основной отдел, к которому приписан человек (может редактировать его данные в persons)
     *      ->about - Характеристика личности
     *      ->skype - Уникальный идентификатор в Skype
     *      ->phoneadd1 - Дополнительный телефон 1
     *      ->phoneadd2 - Дополнительный телефон 2
     *      ->phoneadd3 - Дополнительный телефон 3
     *      ->emailadd1 - Дополнительная электронная почта 1
     *      ->emailadd2 - Дополнительная электронная почта 2
     *      ->emailadd3 - Дополнительная электронная почта 3
     *      ->passportaddr - Адрес прописки по паспорту (для генерации документов)
     *      ->address - Текущий адрес (почтовый адрес)
     *      ->birthaddress - Адрес рождения персоны
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @return bool|string - true в случае успеха, иначе - код ошибки: []
     */
    public function execute_set_person($input)
    {
        return $this->execute_set_base($input, 'set_person');
    }

    /** Получить sha1-хеш пришедшего SOAP-запроса по алгоритму:
     * $requesthash = sha1($requestpassword + $requesttime + $requestlogin + json($input) + json($input->cov)), где
     * $input формируется следующим образом:
     *   1. Из полей объекта для SOAP-запроса исключаются служебные данные — requesthash,
     *  requestlogin, requesttime и дополнительный массив cov
     *   2. Поля сортируются в алфавитном порядке;
     * $input->cov — вложенный массив cov, поля которого так же сортируются в алфавитном порядке;
     * 
     * @param object $input - SOAP-запрос с обязательными полями
     *  id, requesttime, requestlogin, requesthash, массивом cov
     * @param string $requestpassword - ключ идентификатора системы
     * @return bool|string - false в случае ошибки или sha1-хеш
     */
    public function hash_object($input, $requestpassword)
    {
        $inputc = clone $input;
        if ( !is_object($inputc) OR ! is_string($requestpassword) )
        {
            return false;
        }
        // Рассчитаем sha1-хеш от входных параметров
        $hashstring = '';
        // Ключ, время, идентификатор системы
        $hashstring .= $requestpassword;
        $hashstring .= $inputc->requesttime;
        $hashstring .= $inputc->requestlogin;
        // Поля убираем - для сортировки и json_encode
        unset($inputc->requesttime);
        unset($inputc->requestlogin);
        unset($inputc->requesthash);
        $cov = new stdClass();
        // Если cov не передали, то json_encode($cov) возвратит '{}'
        if ( isset($inputc->cov) AND is_object($inputc->cov) )
        {
            $cov = $inputc->cov;
            unset($inputc->cov);
        }
        // Сортируем объект и массив cov
        $sorted = dof_sort_object_fields($inputc);
        $sortedcov = dof_sort_object_fields($cov);
        // Создаём json-строки из них и формируем sha1-hash
        $hashstring .= json_encode($sorted);
        $hashstring .= json_encode($sortedcov);
        return sha1($hashstring);
    }

    /** Выполнить хеширование объекта для сравнения с uphash в storage/sync
     * 
     * @param object $input
     * @return bool|string
     */
    public function hash_uphash($input)
    {
        if ( !is_object($input) )
        {
            return false;
        }
        $inputc = clone $input;
        // Рассчитаем sha1-хеш от входных параметров
        $hashstring = '';
        $cov = new stdClass();
        // Если cov не передали, то json_encode($cov) возвратит '{}'
        if ( isset($inputc->cov) AND is_object($inputc->cov) )
        {
            $cov = $inputc->cov;
            unset($inputc->cov);
        }
        // Служебные поля убираем - для сортировки и json_encode
        unset($inputc->requesttime);
        unset($inputc->requestlogin);
        unset($inputc->requesthash);
        // Сортируем объект и массив cov
        $sorted = dof_sort_object_fields($inputc);
        $sortedcov = dof_sort_object_fields($cov);
        // Создаём json-строки из них и формируем sha1-hash
        $hashstring .= json_encode($sorted);
        $hashstring .= json_encode($sortedcov);
        return sha1($hashstring);
    }

    /** Получить каркас объекта ответа для разных типов методов
     * 
     * @param string $type - тип запроса ('set', 'get', ...)
     * @param string $errorcode - код ошибки
     * @return bool|object - объект для передачи ответа в SOAP-сообщениях или false в случае ошибки
     */
    public function get_response($type, $errorcode = 'OK')
    {
        $response = new stdClass();
        switch ( $type )
        {
            case 'set':
                $response->id = null;
                $response->dofid = null;
                $response->modified = null;
                $response->hash = null;
                $response->errorcode = $errorcode;
                break;

            default:
                return false;
        }
        $this->errorlog($response);
        return $response;
    }

    /** Получить ключ идентификатора системы, используя логин
     * 
     * @param string $requestlogin - идентификатор системы (логин)
     * @return string|bool ключ или false
     */
    public function get_key($requestlogin)
    {
        // Путь до файла с ключами
        //(сначала подключается файл $CFG->moodledata."/dof/cfg/sync/soap/clients.php",
        // потом файл sync/soap/cfg/clients.php, если первый не найден)
        $path = $this->dof->plugin_path($this->type(), $this->code(), '/cfg/clients.php');
        if ( file_exists($path) )
        {
            include_once($path);
            // Структура описана в cfg/clients.php
            if ( isset($clients) AND is_array($clients) )
            {
                if ( isset($clients[$requestlogin]) )
                {
                    if ( isset($clients[$requestlogin]['password']) )
                    {
                        return $clients[$requestlogin]['password'];
                    }
                }
            }
        }
        return false;
    }

    /** Выполняет базовую проверку переданных параметров (контрольная сумма), проверяет,
     * реализованы ли функции для проверки входных параметров и исполнения запроса,
     * вызывает 'execute_' метод для переданного в качестве аргумента объекта запроса и метода
     * В случае возникновения ошибок составляет ответный запрос и выполняет логирование
     * Вызывает следующие методы:
     *  ->check_hash()
     *  ->check_fields_$methodname()
     *  ->execute_$methodname()
     * 
     * @param object $input - объект SOAP-запроса
     * @param string $methodname - название функции, которую необходимо исполнить
     * @return object - объект содерщаший поля
     *       ->id - переданный в запросе id объекта
     *       ->dofid - внутренний id объекта
     *       ->modified - время изменения объекта
     *       ->hash - контрольная сумма из storages/sync
     *       ->errorcode - код ошибки
     */
    public function set_method($input, $methodname)
    {
        $this->errorlog($methodname);
        $this->errorlog($input);
        // Исправим входящий объект или массив на объект (для правильного хеширования)
        if ( isset($input->cov) )
        {
            if ( is_array($input->cov) )
            {
                $input->cov = json_decode(json_encode($input));
            } else if ( isset($input->cov->item) )
            {
                $input->cov = $this->normalize_array($input->cov);
            }
        }
        $errorcode = $this->check_hash($input);
        if ( is_string($errorcode) )
        {
            $response = $this->get_response('set', $errorcode);
            return $response;
        }
        // Проверим, есть ли функции проверки и выполнения запроса
        $checkfnc = 'check_fields_' . $methodname;
        if ( !method_exists($this, $checkfnc) )
        {
            $response = $this->get_response('set', 'SC1');
            return $response;
        }
        $executefnc = 'execute_' . $methodname;
        if ( !method_exists($this, $executefnc) )
        {
            $response = $this->get_response('set', 'SC2');
            return $response;
        }

        // Проверим, какие поля некорректны и можно ли выполнить запрос
        // Здесь только проверяется корректность полей, никаких запросов к базе!
        $errorcode = $this->$checkfnc($input);
        if ( is_string($errorcode) )
        {
            $response = $this->get_response('set', $errorcode);
            return $response;
        }
        // Обращаемся к storages, проверяем hash объекта
        // В зависимости от того, есть ли id в базе, делать update или insert
        $result = $this->$executefnc($input);
        if ( is_string($result) )
        {
            $response = $this->get_response('set', $result);
            return $response;
        } else if ( !is_object($result) )
        {// Что-то пошло не так. Логируем и возвращаем результат
            return $this->get_response('set', 'unknown');
        }
        // Все действия выполнены, возвратим ответ
        return $result;
    }

    /** Обновить вложенный массив cov для внутренних объектов 
     * 
     * @param int $dofid - внутренний id объекта в таблице $plugincode
     * @param mixed $inputcov - массив или объект, по которому происходит перебор
     * @param string $plugincode - код плагина storage
     * @return bool|string - true или код ошибки 
     */
    private function update_cov($dofid, $inputcov, $plugincode)
    {
        $cov = $this->dof->storage('cov');
        $storage = $this->dof->storage($plugincode);
        $columns = array_keys($storage->get_columns());
        if ( !empty($inputcov) )
        {
            foreach ( $inputcov as $code => $value )
            {
                // Нельзя сохранять в cov объекты, массивы и существующие поля справочника
                if ( !is_object($value) AND !is_array($value) AND !array_key_exists($value, $columns) )
                {
                    $cov->save_option('storage', $plugincode, $dofid, $code, $value);
                }
            }
        }
    }

    /** Обновить объект в базе
     * 1) Получить из запроса поля для вставки в таблицу
     * 2) Выполнить дополнительные действия, в случае ошибок сгенерировать ответ
     * 3) Обновить объект в базе, обработать ошибки
     * 4) Обработать массив cov, добавить поля в базу, обработать ошибки
     * 
     * @param object $input - объект, по которому происходит перебор
     * @param int $dofid - внутренний id объекта в таблице $plugincode
     * @param string $method - метод API, который необходимо выполнить
     * @param string $table - название справочника: 'contracts', 'persons', ...
     * @return bool|string - true или код ошибки 
     */
    private function update_object($input, $dofid, $method, $table)
    {
        $storage = $this->dof->storage($table);
        $update = $this->get_fields($input, $table);
        if ( empty($update) )
        {// Не удалось получить поля для добавления/обновления объекта в базе
            return 'SC8';
        }
        $update->id = $dofid;
        $executeadditional = 'execute_additional_' . $method;
        if ( method_exists($this, $executeadditional) )
        {
            $result = $this->$executeadditional($input, 'update', $dofid);
            if ( is_string($result) )
            {// Возникла ошибка
                return $result;
            } else if ( is_object($result) )
            {// Если возвратил объект, значит это дополнительные поля к $insert
                foreach ( $result as $key => $value )
                {
                    $update->$key = $value;
                }
            }
        }

        if ( $storage->update($update) )
        {// Успешно вставили: добавим cov
            $this->update_cov($dofid, $input->cov, $table);
            // Статусы здесь не обрабатываем, только в insert()
        } else
        {// Сгенерируем ошибку и отправим
            return $this->get_storage_error('update', $table);
        }
        return true;
    }
    
    /** Добавить объект в базу
     * 1) Получить из запроса поля для вставки в таблицу: get_fields()
     * 2) Выполнить дополнительные действия: execute_additional_(), в случае ошибок сгенерировать ответ
     * 3) Вставить объект в базу, обработать ошибки
     * 4) Обработать массив cov: update_cov(), добавить поля в базу, обработать ошибки
     * 5) В соответствии с настройками выставить статус через set_status_route(), ошибки только логировать
     * 
     * @param object $input - объект с полями справочника и другими дополнительными полями
     * @param string $method - метод API, который необходимо выполнить
     * @param string $table - название справочника: 'contracts', 'persons', ...
     * @return int|string - id добавленного объекта или код ошибки
     */
    private function insert_object($input, $method, $table)
    {
        $storage = $this->dof->storage($table);
        $insert = $this->get_fields($input, $table);
        if ( empty($insert) )
        {// Не удалось получить поля для добавления/обновления объекта в базе
            return 'SC8';
        }
        $executeadditional = 'execute_additional_' . $method;
        if ( method_exists($this, $executeadditional) )
        {
            $result = $this->$executeadditional($input, 'insert');
            if ( is_string($result) )
            {// Возникла ошибка
                return $result;
            } else if ( is_object($result) )
            {// Если возвратил объект, значит это дополнительные поля к $insert
                foreach ( $result as $key => $value )
                {
                    $insert->$key = $value;
                }
            }
        }

        if ( $dofid = $storage->insert($insert) )
        {// Успешно вставили: добавим cov
            $this->update_cov($dofid, $input->cov, $table);
        } else
        {// Сгенерируем ошибку и отправим
            return $this->get_storage_error('insert', $table);
        }
        return $dofid;
    }
    
    /** Исполняет дополнительные действия в SOAP-запросе set_person()
     * 
     * @param object $input - запрос, должен содержать следующие поля:
     *      ->passportaddr - Адрес прописки по паспорту (для генерации документов)
     *      ->address - Текущий адрес (почтовый адрес)
     *      ->birthaddress - Адрес рождения персоны
     *      ->cov [optional] - Дополнительный массив cov, содержащий дополнительные поля к объекту
     * @param string $operation - название операции: 'insert', 'update', ...
     * @param int $dofid - id из таблицы persons (если не null, то операция 'update', иначе - 'insert')
     * @return bool|object|string - true если обновление успешно, false, если нет
     *                              object в случае успеха при $operation = 'insert',
     *                              иначе - код ошибки: [SP8], [SI8], [SI10], [SC5]
     */
    public function execute_additional_set_person($input, $operation, $dofid = null)
    {
        // Вспомогательный массив: какие из полей запроса относятся к полям в справочнике
        $addresstypes = array('passportaddr' => 'passportaddrid',
                              'address'      => 'addressid',
                              'birthaddress' => 'birthaddressid');
        // Вспомогательный массив: какие типы адресов относятся к полям из запроса
        // ->type: Классификация по SIF (modlib/refbook)
        //'1' - постоянный домашний адрес по паспорту
        //'2' - другой домашний адрес
        //'9' - другой
        $addresssif = array('passportaddr' => 1,
                            'address'      => 2,
                            'birthaddress' => 9);
        if ( !is_object($input) )
        {
            return 'SP8';
        }
        if ( $operation == 'update' )
        {
            if ( empty($dofid) )
            {// Нельзя обновить запись без указания $dofid
                return 'SI8';
            }
            if ( !$person = $this->dof->storage('persons')->get($dofid) )
            {
                return 'SI10';
            }
        } else if ( $operation != 'insert' )
        {// Неверно указано название операции
            return 'SC5';
        }
        $addresses = new stdClass();
                
        $result = false;
        foreach ( $addresstypes as $addresstype => $fieldname )
        {
            if ( isset($input->{$addresstype}) )
            {
                // Если это обновление
                if ( $operation == 'update' )
                {// достанем из персоны id из таблицы addresses:
                    $addressid = $person->{$fieldname};
                } else
                {
                    $addressid = null;
                }
                $input->{$addresstype}->type = $addresssif[$addresstype];
                $result = $this->{$operation . '_address'}($input->{$addresstype}, $addressid);
                if ( is_string($result) )
                {// Возвращаем ошибку
                    return $result;
                }
                if ( $operation == 'update' )
                {
                    $addressid = $person->{$fieldname};
                } else
                {
                    $addressid = $result;
                }
                $addresses->{$fieldname} = $addressid;
            } else
            {
                if ( $operation == 'insert' )
                {// Надо вставить записи со значениями по-умолчанию
                    $input->{$addresstype}->type = $addresssif[$addresstype];
                    $result = $this->{$operation . '_address'}($input->{$addresstype});
                    if ( is_string($result) )
                    {// Возвращаем ошибку
                        return $result;
                    }
                    $addresses->{$fieldname} = $result;
                } else
                {
                    $addresses->{$fieldname} = $person->{$fieldname};
                }
            }
        }
        // Видимо, произошла ошибка
        if ( is_bool($result) AND $result === false )
        {
            return false;
        }
        return $addresses;
    }

    /** Исполняет дополнительные действия в SOAP-запросе set_contract()
     * 
     * @param object $input - запрос, должен содержать следующие поля: (см. check_fields_set_contract())
     * @param string $operation - название операции: 'insert', 'update', ...
     * @param int $dofid - id из таблицы contracts (если не null, то операция 'update', иначе - 'insert')
     * @return object - объект с преобразованными полями id
     */
    public function execute_additional_set_contract($input, $operation, $dofid = null)
    {
        // Вспомогательный массив: персоны
        $persons = array('studentid',
                         'sellerid',
                         'curatorid',
                         'clientid');
        // Нужно перевести id внешней системы (upid) во внутренние id (downid)
        $replacefields = new stdClass();
        foreach ( $persons as $personfield )
        {
            if ( !empty($input->$personfield) )
            {
                $record = $this->get_sync_object('persons', $input->requestlogin, $input->$personfield);
                $replacefields->$personfield = $record->id;
            }
        }
        // Если менеджера не передали
        if ( empty($input->sellerid) )
        {
            $departmentid = $this->get_departmentid($input->departmentcode);
            $defaultsellerid = $this->dof->storage('config')
                ->get_config_value('setcontractdefaultsellerid', 'sync', 'soap', $departmentid);
            $replacefields->sellerid = $defaultsellerid;
        }
        // Передали метаконтракт
        if ( !empty($input->metacontractid) )
        {
            $record = $this->get_sync_object('metacontracts', $input->requestlogin, $input->metacontractid);
            $replacefields->metacontractid = $record->id;
        }
        return $replacefields;
    }
    
    /** Выполнить базовые действия метода set_:
     * 1) Получить объект синхронизации
     * 2) Получить объект для ответа
     * 3) Просчитать хеш объекта (без служебных полей)
     * 4) Есть ли объект синхронизации с таким id в базе?
     * 4.1) Объект найден. Проверить, нужно ли производить синхронизацию
     * 4.1.1) Синхронизация нужна
     * 4.1.1.1) Обновить объект в базе, объект синхронизации и сгенерировать ответ 'OK'
     * 4.1.1.2) Логирование
     * 4.1.1.3) Cгенерировать ответ 'OK', синхронизация успешна
     * 4.1.2) Синхронизация не нужна, сгенерировать ответ 'ОК'
     * 4.2) Объект не найден.
     * 4.2.1) Добавить объект в базу, обрабатывая ошибки, объект синхронизации
     * 4.2.2) Логирование
     * 4.2.3) Cгенерировать ответ 'OK', синхронизация успешна
     * 5) Вернуть ответ
     * 
     * @see dof_sync_soap::insert_object()
     * @see dof_sync_soap::update_object()
     * @param object $input - объект SOAP-запроса
     * @param string $method - метод API, который необходимо выполнить
     * @return string|object - код ошибки или объект $response
     */
    private function execute_set_base($input, $method)
    {
        if ( !$table = $this->get_table($method) )
        {// Для этого метода не задана таблица, с которой он работает
            return 'SC4';
        }
        // Проверяем, есть ли в справочнике storage/sync переданный id
        // Если есть, достаём его из базы и вычисляем hash переданного объекта
        // Если hash совпал, то возвращаем ответ со статусом 'OK'
        $connect = $this->connect_sync($table, $input->requestlogin);
        $response = $this->get_response('set');
        $uphash = $this->hash_uphash($input);
        $response->id = $input->id;
        $response->hash = $uphash;
        // А есть ли такой объект в системе?
        if ( $syncrecord = $connect->getSync(array('upid' => $input->id)) )
        {// Посчитаем hash от пришедшего объекта и вызовем функцию проверки
            if ( $connect->is_updated($syncrecord->upid, $uphash, 'down') )
            {// Генерируем response
                $response->dofid    = $syncrecord->downid;
                $response->modified = $syncrecord->lasttime;
                $response->hash     = $syncrecord->uphash;
            } else
            {// Обновим объект в базе, обновим объект синхронизации, сгенерируем response
                $result = $this->update_object($input, $syncrecord->downid, $method, $table);
                $error = false;
                if ( $result !== true )
                {// Нам вернули строку с кодом ошибки
                    $response->errorcode = $result;
                    $error = true;
                }
                $connect->updateDown($input->id, 'update', $uphash, $syncrecord->downid, $method, $input, $error);
                $response->dofid    = $syncrecord->downid;
                $response->modified = $syncrecord->lasttime;
            }
        } else
        {// Добавим объект в базу, добавим объект синхронизации, сгенерируем response
            $result = $this->insert_object($input, $method, $table);
            $error = false;
            $statusfail = false;
            if ( is_string($result) )
            {// Возвратил код ошибки
                $response->errorcode = $result;
                $error = true;
            } else if ( is_int_string($result) )
            {
                $response->dofid = $result;
                $dofid = $result;
                // Маршрутизация статусов
                $departmentid = null;
                if ( !empty($input->departmentcode) )
                {// Ранее уже должны были проверить код подразделения, просто берём его
                    $departmentid = $this->get_departmentid($input->departmentcode);
                }
                $result = $this->set_status_route($table, $dofid, $departmentid);
                if ( is_array($result) )
                {
                    $statusfail = true;
                    // Код ошибки
                    $errorcode = $result[0];
                    // Описание
                    $errordesc = $result[1];
                }
            }
            $connect->updateDown($input->id, 'create', $uphash, $dofid, $method, $input, $error);
            if ( $statusfail )
            {// Запишем в лог, что обновление статуса произошло с ошибкой
                $connect->updateDown($input->id, 'update', $uphash, $dofid,
                        $errorcode . ': ' . $errordesc, $input, $statusfail);
            }
            // Время синхронизации
            if ( $syncrecord = $connect->getSync(array('upid' => $input->id)) )
            {
                $response->modified = $syncrecord->lasttime;
            }
        }
        return $response;
    }
    
    /** Получить название таблицы (справочника), с которым работает переданный метод
     * 
     * @param string $method - название метода
     * @return bool|string - false, если такого метода не найдено или название справочника
     */
    private function get_table($method)
    {
        $tables = array(
            'set_contract' => 'contracts',
            'set_meta_contract' => 'metacontracts',
            'set_person' => 'persons',
        );
        if ( isset($tables[$method]) )
        {
            return $tables[$method];
        }
        return false;
    }
    
    /** Получить поля для вставки или обновления таблицы
     * 
     * @param object $input - объект с определёнными полями одного из справочника
     * @param string $storage - название справочника: 'contracts', 'persons', ...
     * @param string $listonly - возвратить только названия полей
     * @return bool|object false в случае ошибки или объект с полями 
     * для вставки/обновления записи в таблице
     * @todo вместо null должны быть значения по-умолчанию
     */
    private function get_fields($input, $storage, $listonly = false)
    {
        if ( !is_object($input) OR !is_string($storage) )
        {
            return false;
        }
        $fields = array(
            'addresses' => array(
                // в формате 'поле' => 'значение по-умолчанию'
                'type'       => null,
                'postalcode' => null,
                'country'    => null,
                'region'     => null,
                'county'     => null,
                'city'       => null,
                'streetname' => null,
                'streettype' => null,
                'number'     => null,
                'gate'       => null,
                'floor'      => null,
                'apartment'  => null,
                'latitude'   => 0.0,
                'longitude'  => 0.0,
            ),
            'contracts' => array(
                'date'           => null,
                'sellerid'       => null,
                'clientid'       => null,
                'studentid'      => null,
                'notes'          => null,
                'departmentid'   => null,
                'curatorid'      => null,
                'metacontractid' => null,
            ),
            'persons' => array(
                'firstname'      => null,
                'middlename'     => null,
                'lastname'       => null,
                'preferredname'  => null,
                'dateofbirth'    => null,
                'gender'         => null,
                'email'          => null,
                'phonehome'      => null,
                'phonework'      => null,
                'phonecell'      => null,
                'passtypeid'     => null,
                'passportserial' => null,
                'passportnum'    => null,
                'passportdate'   => null,
                'passportem'     => null,
                'citizenship'    => null,
                'departmentid'   => null,
                'about'          => null,
                'skype'          => null,
                'phoneadd1'      => null,
                'phoneadd2'      => null,
                'phoneadd3'      => null,
                'emailadd1'      => null,
                'emailadd2'      => null,
                'emailadd3'      => null,
                'passportaddrid' => null,
                'addressid'      => null,
                'birthaddressid' => null,
            ),
            'metacontracts' => array(
                'num'            => null,
                'departmentid'   => null,
            ),
        );
        // Создадим объект из массива
        $insertobject = false;
        if ( isset($fields[$storage]) )
        {
            if ( $listonly )
            {// Возвратить только названия полей
                return $fields[$storage];
            }
            $insertobject = json_decode(json_encode($fields[$storage]));
        } else
        {
            return false;
        }
        // Определим departmentid
        if ( !empty($input->departmentcode) )
        {// Ранее уже должны были проверить код подразделения, просто берём его
            $depid = $this->get_departmentid($input->departmentcode);
        } else
        {// Код не передали, возьмём по-умолчанию
            $depid = $this->get_departmentid(null);
        }
        if ( array_key_exists('departmentid', $fields[$storage]) )
        {// В таблице есть подразделение, укажем его
            $insertobject->departmentid = $depid;
        }
        // Скопируем поля из $input, если таковые обнаружены
        foreach ( $insertobject as $key => $value )
        {
            if ( isset($input->$key) AND !empty($input->$key) )
            {
                $insertobject->$key = $input->$key;
            }
        }
        return $insertobject;
    }
    
    /** Установить статус согласно маршруту, заданному в конфиге
     * 
     * @param string $table - название справочника: 'contracts', 'persons', ...
     * @param int $dofid - внутренний id объекта из таблицы $storage
     * @param int $departmentid - id из таблицы departments
     * @return bool|array - true в случае успеха или
     *                      array('код_ошибки', 'описание ошибки')
     */
    private function set_status_route($table, $dofid, $departmentid)
    {
        $statusroutes = array(
            'persons'       => 'setpersonstatusroute',
            'contracts'     => 'setcontractstatusroute',
        );
        $availablestatuses = array(
            'persons' => array(
                'normal', 'archived', 'deleted',
            ),
        );
        if ( isset($statusroutes[$table]) )
        {
            // Сменим статусы согласно маршруту:
            $statuses = $this->dof->storage('config')
                    ->get_config_value($statusroutes[$table], 'sync', 'soap', $departmentid);
            $statuses = explode(',', $statuses);
            if ( $this->dof->plugin_exists('workflow', $table) )
            {
                $workflow = $this->dof->workflow($table);
                // Инициализируем состояние объекта
                $workflow->init($dofid);
                $workflowstatuses = $workflow->get_list();
                // Перебираем статусы
                foreach ( $statuses as $status )
                {
                    // Проверяем, есть ли такой статус
                    if ( array_key_exists($status, $workflowstatuses) )
                    {
                        if ( !$workflow->change($dofid, $status) )
                        {// Не удалось изменить статус на $a
                            return array('SW1', "[$table] " . $this->dof->get_string('SW1', 'soap', $status, 'sync'));
                        }
                    } else
                    {// Статуса с таким именем [$a] не обнаружено
                        return array('SW2', "[$table] " . $this->dof->get_string('SW2', 'soap', $status, 'sync'));
                    }
                }
            } else
            {// Просто вручную ставим статус
                $storage = $this->dof->storage($table);
                // Перебираем статусы
                $available = array();
                if (isset($availablestatuses[$table]))
                {
                    $available = $availablestatuses[$table];
                }
                foreach ( $statuses as $status )
                {
                    // Проверяем, есть ли такой статус
                    if ( array_search($status, $available) )
                    {
                        $update = new stdClass();
                        $update->status = $status;
                        if ( !$storage->update($update, $dofid) )
                        {
                            return array('SW1', "[$table] " . $this->dof->get_string('SW1', 'soap', $status, 'sync'));
                        }
                    } else
                    {
                        return array('SW2', "[$table] " . $this->dof->get_string('SW2', 'soap', $status, 'sync'));
                    }
                }
            }
        } else
        {
            // Ничего делать не надо
        }
        return true;
    }
    
    /** Получить код ошибки справочника по названию метода и таблицы
     * 
     * @param string $method - название операции со справочником: 'insert', 'update', ...
     * @param string $storage - название справочника: 'contracts', 'persons', ...
     * @return bool|string - код ошибки или false, если такого сочетания "метод-справочник" не найдено
     */
    private function get_storage_error($method, $storage)
    {
        $inserterrors = array(
            'metacontracts' => 'SI1',
            'persons'       => 'SI3',
            'contracts'     => 'SI5',
        );
        $updateerrors = array(
            'metacontracts' => 'SI2',
            'persons'       => 'SI4',
            'contracts'     => 'SI6',
            'cov'           => 'SI7',
        );
        // Метод
        switch ( $method )
        {
            case 'insert':
                if ( isset($inserterrors[$storage]) )
                {
                    return $inserterrors[$storage];
                }
                break;

            case 'update':
                if ( isset($updateerrors[$storage]) )
                {
                    return $updateerrors[$storage];
                }
                break;

            default:
                break;
        }
        return false;
    }
    
    /** Обновить адрес
     * 
     * @param object $input - объект с необходимыми полями
     * @param int $dofid - id из таблицы adresses
     * @return bool|string результат обновления или код ошибки
     */
    private function update_address($input, $dofid)
    {
        $addresses = $this->dof->storage('addresses');
        if ( !$addresses->is_exists(array('id'=>$dofid)) )
        {
            return 'SI9';
        }
        // Возьмём из запроса только те поля, которые определены и существуют в справочнике
        $update = $this->get_fields($input, 'addresses');
        if ( empty($update) )
        {// Не удалось получить поля для добавления/обновления объекта в базе
            return 'SC8';
        }
        
        // Если нам не передали country и region, возьмём по-умолчанию
        if ( empty($update->country) )
        {
            $update->country = $this->dof->storage('config')
                    ->get_config_value('defaultcountry', 'sync', 'soap', $update->departmentid);
        }
        if ( empty($update->region) )
        {
            $update->region = $this->dof->storage('config')
                    ->get_config_value('defaultregion', 'sync', 'soap', $update->departmentid);
        }
        $update->id = $dofid;
        return $addresses->update($update);
    }
    
    /** Добавить новый адрес
     * 
     * @param object $input - объект с необходимыми полями
     * @return int|bool - false в случае ошибки или id вставленной записи
     */
    private function insert_address($input)
    {
        // Возьмём из запроса только те поля, которые определены и существуют в справочнике
        $insert = $this->get_fields($input, 'addresses');
        if ( empty($insert) )
        {// Не удалось получить поля для добавления/обновления объекта в базе
            return 'SC8';
        }
        // Если нам не передали country и region, возьмём по-умолчанию
        if ( empty($insert->country) )
        {
            $insert->country = $this->dof->storage('config')
                    ->get_config_value('defaultcountry', 'sync', 'soap', $insert->departmentid);
            if ( empty($insert->region) )
            {
                $insert->region = $this->dof->storage('config')
                        ->get_config_value('defaultregion', 'sync', 'soap', $insert->departmentid);
            }
        }
        $addresses = $this->dof->storage('addresses');
        return $addresses->insert($insert);
    }
    
    /** Нормализует ошибочно переданный ассоциативный массив в объект
     * 
     * @param object $item - объект с вложенным массивом item[], в котором находятся объекты:
     * item[0] = stdClass(),
     * item[0]->key, 
     * item[0]->value, 
     * @return object - объект, в котором поля {item[0]->key}, а значения: {item[0]->value}
     */
    private function normalize_array($item)
    {
        $normalized = new stdClass();
        if ( isset($item->item) AND is_array($item->item) )
        {
            $first = reset($item->item);
            if ( isset($first->key) AND isset($first->value) )
            {
                foreach ( $item->item as $object )
                {
                    if ( isset($object->key) AND isset($object->value) )
                    {
                        $key = $object->key;
                        $value = $object->value;
                        $normalized->$key = $value;
                    }
                }
            }
        }
        return $normalized;
    }

    /** Записывает переданный объект в файл ('/dat/errorlog.txt') для последующей отладки
     * @param mixed $input - переменная для вывода в файл
     * @param string $queryname - название запроса (какя именно операция выполняется)
     * @return void
     */
    public function errorlog($input, $queryname=null)
    {
        // открываем файл
        $resultfile = fopen($this->dof->plugin_path('sync', 'soap', '/dat/errorlog.txt'), 'a');
        // формируем данные для вставки в файл
        if ( $queryname )
        {
            fputs($resultfile, $queryname.":\n");
        }
        //дата запроса
        fputs($resultfile, date('d.m.Y H:i:s',time())."\n");
        ob_start();
        print_object($input);
        $out = html_entity_decode(ob_get_clean());
        fputs($resultfile,"input - ".$out."\n\n");

        // завершаем работу с файлом
        fclose($resultfile);
    }

    /**
     * Проверить, существует ли объект синхронизации в справочнике storage/sync
     *
     * @param $table - название справочника
     * @param $provider - название клиента, с которым выполняется синхронизация
     * @param $id - id объекта, в зависимости от направления синхронизации
     * @param string $direct - направление синхронизации ('up', 'down')
     * @return bool|string - результат операции или код ошибки
     */
    public function is_sync_object_exists($table, $provider, $id, $direct = 'down')
    {
        if ( !is_string($table) OR !is_string($provider)
            OR !is_string($direct) OR !is_int_string($id) )
        {
            return 'SC9';
        }
        $connect = $this->connect_sync($table, $provider);
        $directid = '';
        if ( $direct == 'up' OR empty($direct) )
        {
            $directid = 'downid';
        } else if ( $direct == 'down')
        {
            $directid = 'upid';
        }
        // А есть ли такой объект в системе?
        if ( !$syncrecord = $connect->getSync(array($directid => $id)) )
        {// Объект не найден
            return false;
        }
        return true;
    }
    
    /**
     * Получить внутренний объект синхронизации
     * 
     * @param string $table - справочник, по которому необходимо получить объект
     * @param string $provider - внешний провайдер
     * @param int $id - id из таблицы storage/sync
     * @param string $direct - направление синхронизации
     * @return bool|string|object - false, если объект не найден
     *                            - код ошибки
     *                            - объект с записью
     */
    public function get_sync_object($table, $provider, $id, $direct = 'down')
    {
        if ( !is_string($table) OR !is_string($provider)
            OR !is_string($direct) OR !is_int_string($id) )
        {
            return 'SC9';
        }
        $connect = $this->connect_sync($table, $provider);
        $directid = '';
        if ( $direct == 'up' OR empty($direct) )
        {
            $directid = 'downid';
        } else if ( $direct == 'down')
        {
            $directid = 'upid';
        }
        // А есть ли такой объект в системе?
        if ( $syncrecord = $connect->getSync(array($directid => $id)) )
        {// Объект найден
            $storage = $this->dof->storage($table);
            if ( !$this->dof->storage($table)->is_exists($syncrecord->downid) )
            {
                return false;
            }
            $record = $storage->get($syncrecord->downid);
            return $record;
        }
        return false;
    }

    /**
     * Получить id подразделения по коду (если не передано, то получить подразделение по-умолчанию)
     *
     * @param $code - код подразделения
     * @return int|string - id подразделения, если указан код или
     *                      id подразделения по-умолчанию, если не указан
     *                      код ошибки, если подразделений с таким кодом или по-умолчанию не найдено
     */
    public function get_departmentid($code = null)
    {
        if ( !empty($code) )
        {// Код указан
            if ( ! $departmentid = $this->dof->storage('departments')->get_field(
                array('code' => $code), 'id') )
            {// Подразделения с таким кодом не существует
                return 'SP5';
            }
            return (int)$departmentid;
        } else
        {// Подразделение по-умолчанию
            if ( $dep = $this->dof->storage('departments')->get_default() )
            {
                $departmentid = (int)$dep->id;
                return $departmentid;
            }
        }
        // Код подразделения не передан, и значения по-умолчанию не найдено
        return 'SP9';
    }
}
?>