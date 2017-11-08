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
require_once($DOF->plugin_path('storage', 'config', '/config_default.php'));

/** Справочник подписок на учебные программы
 * 
 */
class dof_storage_programmsbcs extends dof_storage implements dof_storage_config_interface
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
        if ( !parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
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
        // Модификация базы данных через XMLDB
        $result = true;

        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ( $oldversion < 2013062700 )
        {// добавим поле salfactor
            $field = new xmldb_field('salfactor', XMLDB_TYPE_FLOAT, '6', XMLDB_UNSIGNED, true, null, '1', 'dateend');
            // количество знаков после запятой
            $field->setDecimals('2');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE, array('salfactor'));
            if ( !$dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        if ( $oldversion < 2013082800 )
        {// добавим поле salfactor
            dof_hugeprocess();
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE, array('salfactor'));
            if ( $dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('salfactor', XMLDB_TYPE_FLOAT, '6, 2', null, XMLDB_NOTNULL, null, '0', 'dateend');
            $dbman->change_field_default($table, $field);
            if ( !$dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            while ( $list = $this->get_records_select('salfactor = 1', null, '', '*', 0, 100) )
            {
                foreach ( $list as $schevent )
                {// ищем уроки где appointmentid не совпадает с teacherid
                    $obj            = new stdClass;
                    $obj->salfactor = 0;
                    $this->update($obj, $schevent->id);
                }
            }
        }
        if ( $oldversion < 2013102500 )
        {// добавим поле salfactor
            dof_hugeprocess();
            $index = new xmldb_index('iagestartid', XMLDB_INDEX_NOTUNIQUE, array('agestartid'));
            if ( $dbman->index_exists($table, $index) )
            {// если индекс еще не установлен
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('agestartid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'datestart');
            $dbman->drop_field($table, $field);
        }
        // Применяем обновления
        return $result && $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
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
        return 'programmsbcs';
    }

    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return [
		                'storage' => [
		                                'contracts' => 2015110900,
                                        'acl'       => 2011041800,
                                        'config'    => 2011080900
		                ],
		                'workflow' => [
		                                'contracts' => 2015020200
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
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion = 0)
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
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
		                'storage' => [
		                                'contracts' => 2015110900,
                                        'acl'       => 2011041800,
                                        'config'    => 2011080900
		                ],
		                'workflow' => [
		                                'contracts' => 2015020200
		                ]
		];
    }

    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(
            // обрабатываем создание, изменение, или удаление новой подписки
            array('plugintype' => 'storage', 'plugincode' => 'programmsbcs', 'eventcode' => 'insert'),
            array('plugintype' => 'storage', 'plugincode' => 'programmsbcs', 'eventcode' => 'update'),
            array('plugintype' => 'storage', 'plugincode' => 'programmsbcs', 'eventcode' => 'delete')
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
        if ( $this->dof->is_access('datamanage') || 
             $this->dof->is_access('admin') ||
             $this->dof->is_access('manage') 
           )
        {// Имеются глобальные права управления Деканатом
            return true;
        }

        // Получение ID персоны
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        
        // Получение полного списка параметров для проверки прав
        $acldata  = $this->get_access_parametrs($do, $objid, $personid, $depid);
        
        // Выполнение действий в зависимости от права
        switch ($do)
        {
            // Использовать свою подписку на программу
            case 'use/my':
                // Договор по подписке
                $contractid = $this->get_field($objid, 'contractid');
                if ( empty($contractid) )
                {// Договор не указан
                    return false;
                }
                $contract = $this->dof->storage('contracts')->get($contractid);
                if ( empty($contract) )
                {// Договор не найден
                    return false;
                }
                if ( $personid == $contract->clientid || $personid == $contract->studentid )
                {// Персона является владельцем подписки
                    return true;
                }
                break;
            default:
                break;
        }
        // Проверка прав
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
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
        if ( !$this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ( $objid )
            {
                $notice.=" id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
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
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        if ( $gentype == 'storage' AND $gencode == 'programmsbcs' )
        {//есть событие от этого справочника';
            switch ( $eventcode )
            {//обработаем его
                case 'insert': return $this->send_addto_agroup($intvar, $mixedvar['new']);
                    break;
                case 'update': return $this->send_change_agroup($intvar, $mixedvar['old'], $mixedvar['new']);
                    break;
                case 'delete': return $this->send_from_agroup($intvar, $mixedvar['old']);
                    break;
            }
        }
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
        return 'block_dof_s_programmsbcs';
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
        $result               = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid = $objectid;
        if ( !$objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else
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
      
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = [];

        // Право просмотра подписки
        $a['view']          = ['roles' => ['manager', 'methodist']];
        // Право редактирования подписки
        $a['edit']          = ['roles' => ['manager']];
        // Право смены принадлежности к академической группе у подписки 
        $a['edit:agroupid'] = ['roles' => ['manager']];
        // Право создания новой подписки
        $a['create']        = ['roles' => ['manager']];
        // Право удаления подписки
        $a['delete']        = ['roles' => []];
        // Право использования подписки
        $a['use']           = ['roles' => ['manager', 'methodist', 'teacher']];
        // Право использования своей подписки
        $a['use/my']        = ['roles' => []];
        
        return $a;
    }

    /**
     * Получить настройки для плагина
     * 
     * @param unknown $code
     * 
     * @return array - Массив настроек плагина
     */
    public function config_default($code = null)
    {
        $config = [];
        
        // Плагин включен и используется
        $obj                = new stdClass();
        $obj->type          = 'checkbox';
        $obj->code          = 'enabled';
        $obj->value         = '1';
        $config[$obj->code] = $obj;
        // Максимально разрешенное количество объектов этого типа в базе(для каждого подразделения)
        $obj                = new stdClass();
        $obj->type          = 'text';
        $obj->code          = 'objectlimit';
        $obj->value         = '-1';
        $config[$obj->code] = $obj;
        // Форма обучения по-умолчанию
        $obj                = new stdClass();
        $obj->type          = 'text';
        $obj->code          = 'default_eduform';
        $obj->value         = 'internal';
        $config[$obj->code] = $obj;
        
        return $config;
    }
    // **********************************************
    //              Собственные методы
    // **********************************************

    /**
     * Сохранить подписку на обучение
     *
     * @param stdClass|array $programmsbcdata - Данные подписки на обучение
     *              Обязательные поля для создания подписки:
     *                  int contractid - ID договора на обучение
     *                  int programmid - ID программы
     *                  
     * @param array $options - Массив дополнительных параметров
     *
     * @return bool|int - ID подписки или false в случае ошибки
     *
     * @throws dof_exception_dml - В случае ошибки
     */
    public function save($programmsbcdata = null, $options = [])
    {
        // Нормализация данных
        try {
            $normalized_data = $this->normalize($programmsbcdata, $options);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception_dml('error_save_'.$e->errorcode);
        }
        
        // Сохранение данных
        if ( isset($normalized_data->id) )
        {// Обновление записи
            if ( ! $this->is_exists($normalized_data->id) )
            {// Запись не найдена
                throw new dof_exception_dml('error_save_programmbc_not_found');
            }
            
            if ( $this->is_programmsbc($normalized_data->contractid, $normalized_data->programmid, $normalized_data->agroupid, null, $normalized_data->id) )
            {// Аналогичная подписка найдена в системе, сохранение запрещено
                throw new dof_exception_dml('error_save_programmbc_duplicate');
            }
            
            if ( ! $this->update($normalized_data) )
            {// Обновление не удалось
                throw new dof_exception_dml('error_save_programmbc');
            } else
            {// Обновление удалось
                // Выбрасывание события сохранения подписки на программу
                $this->dof->send_event('storage', 'programmsbcs', 'item_saved', (int)$normalized_data->id);
                return $normalized_data->id;
            }
        } else
        {// Создание записи
            
            if ( $this->is_programmsbc($normalized_data->contractid, $normalized_data->programmid, $normalized_data->agroupid) )
            {// Аналогичная подписка найдена в системе, сохранение запрещено
                throw new dof_exception_dml('error_save_programmbc_duplicate');
            }
            
            // Добавление записи
            $id = $this->insert($normalized_data);

            if ( ! $id )
            {// Создание не удалось
                throw new dof_exception_dml('error_save_programmbc');
            } else
            {// Создание удалось
                $this->dof->send_event('storage', 'programmsbcs', 'item_saved', (int)$id);
                return $id;
            }
        }
        return false;
    }
    
    /**
     * Нормализация данных подписки на программу
     *
     * Формирует объект подписки на основе переданных данных. В случае критической ошибки
     * или же если данных недостаточно, выбрасывает исключение.
     *
     * @param array|stdClass $contractdata - Данные подписки
     * @param array $options - Опции работы
     *      'ignore_validation_contract' bool - Игнорировать валидацию дговора
     *
     * @return stdClass - Нормализовализованный Объект подписки
     * @throws dof_exception_dml - Исключение в случае критической ошибки или же недостаточности данных
     */
    public function normalize($programmsbcdata, $options = [])
    {
        // Нормализация входных данных
        if ( is_object($programmsbcdata) || is_array($programmsbcdata) )
        {// Комплексные данные
            $programmsbcdata = (object)$programmsbcdata;
        } else
        {// Неопределенные данные
            throw new dof_exception_dml('invalid_data');
        }
    
        // Проверка входных данных
        if ( empty($programmsbcdata) )
        {// Данные не переданы
            throw new dof_exception_dml('empty_data');
        }
        if ( isset($programmsbcdata->id) )
        {// Проверка на существование
            if ( ! $this->get($programmsbcdata->id) )
            {// Договор не найден
                throw new dof_exception_dml('programmsbc_not_found');
            }
        }

        // Получение данных о текущей персоне
        $currentperson = $this->dof->storage('persons')->get_bu();
        if ( isset($currentperson->id) )
        {
            $currentpersonid = (int)$currentperson->id;
        } else
        {
            $currentpersonid = 0;
        }
        
        // Создание объекта для сохранения
        $saveobj = clone $programmsbcdata;
    
        // Проверки валидности входных данных
        if ( isset($saveobj->id) )
        {// Процесс обновления
            // Удаление автоматически генерируемых полей
            unset($saveobj->status);
            unset($saveobj->dateadd);
        } else
        {// Cоздание новой подписки
            $currenttime = time();

            // АВТОЗАПОЛНЕНИЕ ПОЛЕЙ
            // Установка значений по-умолчанию
            if ( ! isset($saveobj->departmentid) || empty($saveobj->departmentid) )
            {// Установка подразделения по-умолчанию
                $saveobj->departmentid = $this->dof->storage('departments')->get_default_id();
            }
            if ( ! isset($saveobj->contractid) || empty($saveobj->contractid) )
            {// Установка договора по-умолчанию
                $saveobj->contractid = 0;
            }
            if ( ! isset($saveobj->programmid) || empty($saveobj->programmid) )
            {// Установка программы по-умолчанию
                $saveobj->programmid = 0;
            }
            if ( ! isset($saveobj->agroupid) || empty($saveobj->agroupid) )
            {// Группа не передана
                $saveobj->agroupid = 0;
            }
            if ( ! isset($saveobj->edutype) || empty($saveobj->edutype) )
            {// Установка типа обучения в зависимости от наличия группы
                if ( (int)$saveobj->agroupid )
                {// Подписка в группу, групповой тип подписки
                    $saveobj->edutype = 'group';
                } else
                {// Индивидуальная подписка
                    $saveobj->edutype = 'individual';
                }
            }
            if ( ! isset($saveobj->eduform) || empty($saveobj->eduform) )
            {// Установка формы обучения
                // Форма обучения по-умолчанию
                $defaultvalue = (string)$this->dof->storage('config')->get_config_value(
                    'default_eduform',
                    'storage',
                    'programmsbcs',
                    $saveobj->departmentid,
                    $currentpersonid
                );
                $saveobj->eduform = $defaultvalue;
            }
            if ( ! isset($saveobj->freeattendance) )
            {// Установка флага свободного посещения по-умолчанию
                $saveobj->freeattendance = 0;
            }
            if ( ! isset($saveobj->agenum) )
            {// Установка номера параллели
                $saveobj->agenum = 0;
            }
            if ( ! isset($saveobj->datestart) || empty($saveobj->datestart) )
            {// Установка даты старта подписки
                $saveobj->datestart = $currenttime;
            }
            if ( ! isset($saveobj->certificatenum) || empty($saveobj->certificatenum) )
            {// Установка номера сертификата
                $saveobj->certificatenum = null;
            }
            if ( ! isset($saveobj->certificateform) || empty($saveobj->certificateform) )
            {// Установка кода формы сертификата
                $saveobj->certificateform = null;
            }
            if ( ! isset($saveobj->certificatedate) || empty($saveobj->certificatedate) )
            {// Установка даты выдачи сертификата
                $saveobj->certificatedate = null;
            }
            if ( ! isset($saveobj->certificateorderid) || empty($saveobj->certificateorderid) )
            {// Установка id приказа о выдаче сертификата
                $saveobj->certificateorderid = null;
            }
            if ( ! isset($saveobj->dateend) || empty($saveobj->dateend) )
            {// Установка даты окончания подписки
                $saveobj->dateend = null;
            }
            if ( ! isset($saveobj->salfactor) || empty($saveobj->salfactor) )
            {// Установка зарплатного коэфициента по подписке
                $saveobj->salfactor = 0;
            }
            
            // Установка автоматически генерируемых полей
            if ( ! $this->dof->plugin_exists('workflow', 'programmsbcs') )
            {// Плагин статусов не активен, установка статуса по-умолчанию
                $saveobj->status = 'application';
            } else
            {// Статус назначается в плагине статусов
                unset($saveobj->status);
            }
            $saveobj->dateadd = $currenttime;
        }
        
        // НОРМАЛИЗАЦИЯ ПОЛЕЙ
        if ( isset($saveobj->datestart) && $saveobj->datestart != null )
        {
            if ( ! is_int_string($saveobj->datestart) )
            {// Время представлено в строковом формате
                $saveobj->datestart = strtotime($saveobj->datestart);
            }
            $saveobj->datestart = (int)$saveobj->datestart;
        }
        if ( isset($saveobj->dateend) && $saveobj->dateend != null )
        {
            if ( ! is_int_string($saveobj->dateend) )
            {// Время представлено в строковом формате
                $saveobj->dateend = strtotime($saveobj->dateend);
            }
            $saveobj->dateend = (int)$saveobj->dateend;
        }
        if ( isset($saveobj->edutype) )
        {
            $edutypes = $this->get_edutypes_list();
            if ( ! isset($edutypes[$saveobj->edutype]) )
            {
                if ( isset($saveobj->agroupid) && (int)$saveobj->agroupid )
                {// Подписка в группу, групповой тип подписки
                    $saveobj->edutype = 'group';
                } else
                {// Индивидуальная подписка
                    $saveobj->edutype = 'individual';
                }
            }
        }
        if ( isset($saveobj->eduform) )
        {
            $eduforms = $this->get_eduforms_list();
            if ( ! isset($eduforms[$saveobj->eduform]) )
            {
                $saveobj->eduform = 'internal';
            }
        }
        
        // ВАЛИДАЦИЯ ДАННЫХ
        if ( ! isset($options['ignore_validation_contract']) || empty($options['ignore_validation_contract']) )
        {// Валидация не запрещена
            // Проверки обязательных полей
            if ( isset($saveobj->contractid) && empty($saveobj->contractid) )
            {// Договор не передан
                throw new dof_exception_dml('contract_not_set');
            }
        }
        if ( isset($saveobj->programmid) && empty($saveobj->programmid) )
        {// Программа не передана
            throw new dof_exception_dml('programm_not_set');
        }
        // Проверка валидности параллели
        if ( isset($saveobj->agenum) )
        {// Указан номер параллели
            $programmsbc = null;
            if ( isset($saveobj->id) )
            {// Получение текущей подписки
                $programmsbc = $this->dof->get_record(['id' => $saveobj->id]);
            }
            
            $agroupid = 0;
            if ( isset($saveobj->agroupid) )
            {// Указана группа
                $agroupid = $saveobj->agroupid;
            } elseif ( isset($programmsbc->agroupid) ) 
            {// Получение группы
                $agroupid = $programmsbc->agroupid;
            }
            
            if ( $this->dof->plugin_exists('storage', 'agroup') && $agroup = $this->dof->storage('agroups')->get($agroupid) )
            {// Проверка параллели по группе
                if ( $saveobj->agenum != $agroup->agenum )
                {// Параллель не является параллелью группы
                    throw new dof_exception_dml('agenum_overagroup');
                    
                }
            }
            $programmid = 0;
            if ( isset($saveobj->programmid) )
            {// Указана программа
                $programmid = $saveobj->programmid;
            } elseif ( isset($programmsbc->programmid) )
            {// Получение группы
                $programmid = $programmsbc->programmid;
            }
            if ( $programm = $this->dof->storage('programms')->get($programmid) )
            {// Проверка параллели по программе
                if ( $saveobj->agenum > $programm->agenums )
                {// Параллель не является параллелью группы
                    throw new dof_exception_dml('agenum_overlimit');
                }
            }
            
            if ( $saveobj->agenum < 0 )
            {// Параллель не валидна
                throw new dof_exception_dml('agenum_not_valid');
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
     *              int - ID подписки на программу
     *              string - ID подписки на программу
     *              array|stdClass - Комплексные данные
     *          При передачи комплексных данных можно также указать данные по
     *          зависимым элементам, что приведет к их обработке.
     *              Пример: $data->contractid->num = number
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
            if ( is_int($data) || is_string($data) )
            {// ID объекта
                $data = ['id' => $data];
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
            $options['reportcode'] = 'programmsbc';
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
            
            // ПОИСК ОБЪЕКТОВ ПО ПЕРЕДАННЫМ ДАННЫМ
            $params = [];
            $objects = [];
            // Формирование статусов для фильтрации
            if ( $this->dof->plugin_exists('workflow', 'programmsbcs') )
            {
                $statuses = (array)$this->dof->workflow('programmsbcs')->get_meta_list('actual');
                $statuses = array_keys($statuses);
            } else
            {
                $statuses = ['plan', 'application', 'active', 'condactive', 'suspend'];
            }
            $params['status'] = $statuses;
            // Поиск программы по ID
            if ( isset($data['id']) && ! empty($data['id']) )
            {
                $params['id'] = $data['id'];
                $programmsbcs = $this->get_records($params);
                $objects = $programmsbcs + $objects;
                unset($params['id']);
            }
            
            // Поиск подписки по договору программе и группе
            $params['contractid'] = 0;
            $params['programmid'] = 0;
            // Определение договора подписки
            if ( isset($data['contractid']) )
            {
                $contractoptions = [
                    'notexist_action' => 'error',
                    'multiple_action' => 'error',
                    'simulation' => true
                ];
                $nullreport = null;
                $contract = $this->dof->storage('contracts')->import(
                    $data['contractid'],
                    $nullreport,
                    $contractoptions
                );
                if ( isset($contract->id) )
                {// Договор определен
                    $params['contractid'] = $contract->id;
                }
                unset($contract);
            }
            
            // Определение программы подписки
            if ( isset($data['programmid']) )
            {
                $programmoptions = [
                    'notexist_action' => 'error',
                    'multiple_action' => 'error',
                    'simulation' => true
                ];
                $nullreport = null;
                $programm = $this->dof->storage('programms')->import(
                    $data['programmid'],
                    $nullreport,
                    $programmoptions
                );
                if ( isset($programm->id) )
                {// Программа определена
                    $params['programmid'] = $programm->id;
                }
                unset($programm);
            }
            
            $programmsbcs = $this->get_records($params);
            $objects = $programmsbcs + $objects;
            unset($params['contractid']);
            unset($params['programmid']);
    
            // ОБРАБОТКА НАЙДЕННЫХ ОБЪЕКТОВ
            if ( empty($objects) )
            {// Объекты не найдены
                
                // Исполнение действия в зависимости от настроек
                switch ( $options['notexist_action'] )
                {
                    // Попытка создать подписку на основе переданных данных
                    case 'create' :
                        // Обработка элементов подписки
                        if ( isset($data['contractid']) )
                        {// Обработка договора
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'contractid';
                            $importoptions['notexist_action'] = 'create';
                            $contract = $this->dof->storage('contracts')->import(
                                $data['contractid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['contractid'] = 0;
                            if ( ! empty($subreport['subreports']['contractid']['error']) )
                            {// Ошибки во время импорта договора
                                $subreport['error'] = 'error_import_contract_import';
                                $subreport['errortype'] = 'create';
                            } else
                            {// Импорт договора прошел успешно
                                if ( isset($contract->id) )
                                {
                                    $data['contractid'] = (int)$contract->id;
                                }
                            }
                        } else 
                        {// Договор подписки не определен
                            $subreport['error'] = 'error_import_contract_not_set';
                        }

                        if ( isset($data['programmid']) )
                        {// Обработка программы
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'programmid';
                            $programm = $this->dof->storage('programms')->import(
                                $data['programmid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['programmid'] = 0;
                            if ( ! empty($subreport['subreports']['programmid']['error']) )
                            {// Ошибки во время импорта программы
                                $subreport['error'] = 'error_import_programm_import';
                                $subreport['errortype'] = 'create';
                            } else
                            {// Импорт программы прошел успешно
                                if ( isset($programm->id) )
                                {
                                    $data['programmid'] = (int)$programm->id;
                                }
                            }
                        } else
                        {// Программа подписки не определена
                            $subreport['error'] = 'error_import_programm_not_set';
                        }
                        
                        if ( isset($data['agroupid']) )
                        {// Обработка академической группы
                            $importoptions = $options;
                            $importoptions['reportcode'] = 'agroupid';
                            $agroup = $this->dof->storage('agroups')->import(
                                $data['agroupid'],
                                $subreport['subreports'],
                                $importoptions
                            );
                            $data['agroupid'] = 0;
                            if ( ! empty($subreport['subreports']['agroupid']['error']) )
                            {// Ошибки во время импорта группы
                                $subreport['error'] = 'error_import_agroup_import';
                                $subreport['errortype'] = 'create';
                            } else
                            {// Импорт группы прошел успешно
                                if ( isset($agroup->id) )
                                {
                                    $data['agroupid'] = (int)$agroup->id;
                                }
                            }
                        }
                        
                        $subreport['action'] = 'save';
                        // Нормализация подразделения
                        if ( ! isset($data['departmentid']) )
                        {
                            $data['departmentid'] = $options['departmentid'];
                        }
                        if ( $options['simulation'] )
                        {// Симуляция процесса сохранения подписки на программу
                            try
                            {
                                $importoptions = $options;
                                $importoptions['ignore_validation_contract'] = true;
                                $importobject = $this->normalize($data, $importoptions);
                            } catch ( dof_exception_dml $e )
                            {// Ошибка проверки подписки на программу
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
                        $subreport['error'] = 'error_import_programmsbc_not_found';
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
                        $subreport['error'] = 'error_import_programmsbc_multiple_found';
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
    
    /** Возвращает список контрактов учеников данной группы, у которых нет подписок на предмето-поток
     * @param int $agroupid - id группы
     * @param int $cstreamid - id потока
     * @return array список контрактов
     */
    public function get_contracts_without_cpassed($agroupid, $cstreamid)
    {
        // находим все подписки данного потока
        if ( $cpassed = $this->dof->storage('cpassed')->get_cstream_students($cstreamid) )
        {// подписки есть
            $str = array();
            // переберем их
            foreach ( $cpassed as $cpass )
            {// для каждого запомним id подписки на программу
                if ( $this->is_exists($cpass->programmsbcid) )
                {
                    $str[] = $cpass->programmsbcid;
                }
            }
        }
        // формируем запрос
        if ( !empty($str) )
        {// подписки на потоки были - исключим из поиска найденные подписки на программы
            $select = 'agroupid = ' . $agroupid . ' AND id NOT IN (' . implode($str, ',') . ')';
        } else
        {// не было подисок на поток - значит нужны все подписки на программы 
            $select = 'agroupid = ' . $agroupid;
        }
        if ( !$sbcs = $this->get_records_select($select) )
        {// если подписок найдено не было - значит и контрактов нет
            return array();
        }
        // вернем все найденные контракты
        return $this->dof->storage('contracts')->get_list_by_list($sbcs, 'contractid');
    }

    /** Возвращает список подписок по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_listing($conds = null, $limitfrom = null, $limitnum = null, $sort = '', $fields = '*', $countonly = false)
    {
        if ( !$conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object) $conds;
        if ( !is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( !is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        $countselect = $this->get_select_listing($conds);
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($countselect);
        }
        //формируем строку запроса
        $select          = $this->get_select_listing($conds);
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tblpersons      = $this->dof->storage('persons')->prefix()   . $this->dof->storage('persons')->tablename();
        $tblprogramms    = $this->dof->storage('programms')->prefix() . $this->dof->storage('programms')->tablename();
        $tblcontracts    = $this->dof->storage('contracts')->prefix() . $this->dof->storage('contracts')->tablename();
        $tblagroups      = $this->dof->storage('agroups')->prefix()   . $this->dof->storage('agroups')->tablename();
        $tblprogrammsbcs = $this->prefix() . $this->tablename();
        if ( strlen($select) > 0 )
        {// сделаем необходимые замены в запросе
            $select = 'WHERE p.' . preg_replace('/ AND /', ' AND p.', $select . ' ');
            $select = preg_replace('/ OR /', ' OR p.', $select);
            $select = str_replace('p. (', '(p.', $select);
            $select = str_replace('p.(', '(p.', $select);
        }
        $sql = "SELECT p.*, pr.sortname as sortname, pr.id as studentid, pg.name as programm
                FROM {$tblprogrammsbcs} as p
                LEFT JOIN {$tblagroups} as ag ON  p.agroupid=ag.id
                LEFT JOIN {$tblcontracts} as ct ON p.contractid=ct.id
                LEFT JOIN {$tblpersons} as pr ON ct.studentid=pr.id
                LEFT JOIN {$tblprogramms} as pg ON  p.programmid=pg.id
                $select ";

        // Добавим сортировку
        $sql .= $this->get_orderby_listing($sort);
        //print $sql;
        return $this->get_records_sql($sql, null, $limitfrom, $limitnum);
    }

    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        //$conds = fullclone($inputconds);
        $conds   = (array) $inputconds;
        // теперь создадим все остальные условия
        foreach ( $conds as $name => $field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->query_part_select($name, $field);
            }
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        } elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        } else
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
        $order_by = " ORDER BY ";
        switch ( $sort )
        {
            case 'sortname':
                break;
            case 'programm':
                $order_by .= "pg.name, ";
                break;
            case 'agroup':
                $order_by .= "ag.name, ";
                break;
            case 'agenumprogramm':
                $order_by .= "pg.name, p.agenum, ";
                break;
            default:
                $order_by .= "p.{$sort}, ";
                break;
        }
        return $order_by . "pr.sortname";
    }

    /** 
     * Получить список возможных форм обучения
     * 
     * @return array - Массив вида "форма обучения" => "название"
     */
    public function get_eduforms_list()
    {
        return [
            'internal'                  => $this->dof->get_string('eduform:internal', 'programmsbcs', null, 'storage'),
            'correspondence'            => $this->dof->get_string('eduform:correspondence', 'programmsbcs', null, 'storage'),
            'internally-correspondence' => $this->dof->get_string('eduform:internally-correspondence', 'programmsbcs', null, 'storage'),
            'external-studies'          => $this->dof->get_string('eduform:external-studies', 'programmsbcs', null, 'storage')
        ];
    }

    /** Получить обозначение формы обучения по ее коду
     * 
     * @return mixed string|bool - название формы обучения или пробел, 
     * если такая форма обучения не найдена
     * @param string $code - код формы обучения 
     */
    public function get_eduform_name($code)
    {
        //получим список всех возможных форм обучения
        $eduforms = $this->get_eduforms_list();
        if ( array_key_exists($code, $eduforms) )
        {// если такая форма обучения зарегестрирована - вернем ее название
            return $eduforms[$code];
        }
        // в остальных случаях false
        return '&nbsp;';
    }

    /** Получить список всех возможных типов обучения
     * 
     * @return array массив вида "тип обучения"=>"название"
     */
    public function get_edutypes_list()
    {
        return array('individual' => $this->dof->get_string('edutype:individual', 'programmsbcs', null, 'storage'),
                     'group'      => $this->dof->get_string('edutype:group', 'programmsbcs', null, 'storage'));
    }

    /** Получить обозначение формы обучения по ее коду
     * 
     * @return mixed string|bool - название формы обучения или пробел, 
     * если такая форма обучения не найдена
     * @param string $code - код формы обучения 
     */
    public function get_edutype_name($code)
    {
        //получим список всех возможных форм обучения
        $edutypes = $this->get_edutypes_list();
        if ( array_key_exists($code, $edutypes) )
        {// если такая форма обучения зарегестрирована - вернем ее название
            return $edutypes[$code];
        }
        // в остальных случаях false
        return '&nbsp;';
    }

    /** Проверяет существование подписка с параметрами
     * @param int $contractid - id контракта
     * @param int $programmid - id программы
     * @param int $agroupid - id группы, по умолчанию - нет
     * @param int $id - id подписки, которую не следует учитывать, по умолчанию - нет
     * @return bool true если подписки найдены и false если таковых нет
     */
    public function is_programmsbc($contractid, $programmid, $agroupid = null, $agestartid = null, $id = null)
    {
        // получим контракт ученика для того чтобы узнать id ученика
        $contract = $this->dof->storage('contracts')->get($contractid);
        if ( !isset($contract) AND ! isset($contract->studentid) )
        {// нет контракта и студента - проверять нечего
            return false;
        }
        // получим все контракты ученика
        if ( !$contracts = $this->dof->storage('contracts')->get_list_by_student($contract->studentid) )
        {
            // У ученика нет контрактов
            return false;
        }
        $contractids = array();
        $select      = '';
        foreach ( $contracts as $contract )
        {// собираем все id контрактов
            $contractids[] = $contract->id;
        }
        // склеиваем их в строку
        $contractidsstring = implode(', ', $contractids);
        // составим условие
        $select .= ' contractid IN (' . $contractidsstring . ')';
        if ( !is_null($agroupid) )
        {// id группы указан - дополним условие';
            if ( ($agroupid == 0 ) )
            {// если id группы не найдено - ученик учится индивидуально
                $select .= ' AND agroupid IS NULL';
            } else
            {// id группы найдено - в группе
                $select .= ' AND agroupid = \'' . $agroupid . '\'';
            }
        }
        // включим в условие поиска программу и статус
        // ищем только подписки со статусом заявка, подтвержденная, действующая, приостановленная
        $select .= " AND programmid = '" . $programmid . "' AND 
                             status IN ('application', 'plan', 'active', 'suspend')";
        if ( !is_null($id) AND ( $id <> 0) )
        {// если указано id, которое следует исключить
            // исключаем его
            $select .= ' AND id != ' . $id;
        }
        // метода проверяющего существование записи по SQL-запросу нет,
        // придется использовать метод подсчитывающий кол-во записей
        if ( $sbc = $this->get_records_select($select) )
        {// запись найдена - вернем ее id
            return current($sbc)->id;
        }
        return false;
    }

    /** Изменяет параметры подписки
     * @param int $id - id подписки, которая обновляется
     * @param string $edutype - тип обучения
     * @param string $eduform - форма обучения
     * @param int $freeattendance - свободное посещение
     * @param int $agroupid - id группы, по умолчанию - нет
     * @param int $departmentid - id подразделения в таблице departments
     * @param int $agenum - номер  параллели, в которой находится учебная подписка
     * @return bool true если запись успешно обнавлена и false в остальных случаях
     * 
     * @todo оптимизировать эту функцию, переместив однотипные данные в один объект
     */
    public function change_sbc_parametres($id, $edutype, $eduform, $freeattendance, $datestart, 
            $agestartid = null, $agroupid = null, $departmentid = null, $agenum = null)
    {
        // создадим объект для вставки
        $sbc                 = new stdClass();
        $sbc->edutype        = $edutype;
        $sbc->eduform        = $eduform;
        $sbc->freeattendance = $freeattendance;
        $sbc->datestart      = $datestart;
        if ( $agroupid == 0 )
        {// если группа указана равной 0, переопределим ее в значение null
            $agroupid = null;
        }
        if ( is_numeric($departmentid) AND $departmentid )
        {// если изменился id подразделения - обновим его
            $sbc->departmentid = $departmentid;
        }
        if ( is_numeric($agenum) AND $agenum )
        {// если изменился период - обновим ео
            $sbc->agenum = $agenum;
        }
        $sbc->agroupid = $agroupid;
        // обновим запись в БД
        return $this->update($sbc, $id);
    }

    /** Подписывает ученика на программу
     * @param obj $sbc - объект вставки записи в БД
     * @return mixed int id вставленой записи или bool false в остальных случаях
     */
    public function sign($sbc)
    {
        if ( !isset($sbc->contractid) AND ! isset($sbc->programmid)
                AND ! isset($sbc->agroupid) )
        {// нету id контракта, программы и группы - вставлять нельзя
            return false;
        }
        if ( $this->is_programmsbc($sbc->contractid, $sbc->programmid, $sbc->agroupid) )
        {// подписка с такими параметрами уже существует - вставлять нельзя
            return false;
        }
        // вставляем запись в БД
        return $this->insert($sbc);
    }

    /**
     * Посылает событие "changeagroup", 
     * в случае зачисления студента в группу 
     * @param int $id - id подписки на программу 
     * @param stdClass $object - объект, вставляемый в таблицу
     * @return bool - true, если вызван обработчик события из 
     * соответствующего плагина или false
     */
    public function send_addto_agroup($id, $object)
    {
        $object->id = $id;
        if ( isset($object->agroupid) AND $object->agroupid )
        {//зачислили в группу';
            return $this->dof->send_event($this->type(), $this->code(), 'changegroup', $id,
                    array('oldagroup' => null, 'newagroup' => $object->agroupid, 'programmsbc' => $object));
        }
        return false;
    }

    /**
     * Посылает событие "changeagroup", 
     * в случае перевода студента из одной группы в другую 
     * @param int $id - id подписки на программу 
     * @param stdClass $oldobject - объект, который был в таблице
     * @param stdClass $newobject - объект, вставляемый в таблицу
     * @return bool - true, если вызван обработчик события из 
     * соответствующего плагина или false
     */
    public function send_change_agroup($id, $oldobject, $newobject)
    {
        if ( isset($oldobject->agroupid) AND $oldobject->agroupid )
        {//старая группа есть';
            $oldagroupid = $oldobject->agroupid;
        } else
        {//раньше студент не был в группе';
            $oldagroupid = null;
        }
        if ( isset($newobject->agroupid) AND $newobject->agroupid )
        {//новая группа есть';
            $newagroupid = $newobject->agroupid;
        } else
        {//новой группы нет';
            $newagroupid = null;
        }
        if ( is_null($oldagroupid) AND is_null($newagroupid) )
        {//студент как был вне группы, так и остался';
            return true;
        }
        if ( $oldagroupid == $newagroupid )
        {//студент остался в прежней группе';
            return true;
        }
        //группа изменилась - генерим событие';
        return $this->dof->send_event($this->type(), $this->code(), 'changegroup', $id,
                    array('oldagroup' => $oldagroupid, 'newagroup' => $newagroupid, 'programmsbc' => $newobject));
    }

    /** Посылает событие при исключении ученика из группы 
     * 
     * @param int $id - id удаленной (на момент обработки события) записи из таблицы programmsbcs 
     * @param stdClass $object - объект, удаленный из таблицы programmsbcs
     * @return bool - true если событие послано успешно, или false в случае ошибки
     */
    public function send_from_agroup($id, $object)
    {
        if ( !$object->agroupid )
        {//студент вне группы - событие посылать не надо';
            return false;
        }
        //посылаем событие';
        return $this->dof->send_event($this->type(),$this->code(),'changegroup',$id,
                array('oldagroup' => $object->agroupid, 'newagroup' => null, 'programmsbc' => $object) );
    }

    /** Получить id ученика в таблице persons на которого зарегестрирована эта подписка
     * 
     * @return bool|int
     * @param int $programmsbcid - id подписки на программу в таблице programmsbcs
     */
    public function get_studentid_by_programmsbc($programmsbcid)
    {
        // получаем подписку
        if ( !$programmsbc = $this->get($programmsbcid) )
        {// не найдена запись - это ошибка
            //@todo сообщить об этом через исключение, когда будет возможность
            return false;
        }
        // получаем контракт
        if ( !$contract = $this->dof->storage('contracts')->get($programmsbc->contractid) )
        {// не нашли контракт
            //@todo сообщить об этом через исключение, когда будет возможность
            return false;
        }
        return $contract->studentid;
    }

    /** Метод будет удален: используйте get_programmsbcs_by_contractid($id)
     */
    public function get_programmsbcs_on_contractid($id)
    {
        return $this->get_programmsbcs_by_contractid($id);
    }

    /** Получает информацию о подписках на программу по id контракта
     * @param int $id - id контракта
     * @return array - массив с информацией о подписках на программу
     */
    public function get_programmsbcs_by_contractid($id)
    {
        if ( !is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        $rez  = array();
        //найдем подписки на программу по id контракта
        if ( !$sbcs = $this->get_records(array('contractid' => $id, 'status' => array('application', 'plan', 'active', 'suspend'))) )
        {// ничего не нашли
            return $rez;
        }
        foreach ( $sbcs as $sbc )
        {// для каждой из подписок соберем информацию
            $sbcinfo               = new stdClass();
            if ( !$sbcinfo->programmname = $this->dof->storage('programms')->
                    get_field($sbc->programmid, 'name') )
            {
                $sbcinfo->programmname = '';
            }
            $sbcinfo->agenum     = $sbc->agenum;
            $sbcinfo->status     = $sbc->status;
            $sbcinfo->programmid = $sbc->programmid;
            $rez[$sbc->id]       = $sbcinfo;
        }
        return $rez;
    }

    /** Получает id подписки на программу по id контракта
     * @param int $id - id контракта
     * @return array - массив id подписок
     */
    public function get_programmsbcs_by_contractid_ids($id)
    {
        if ( !is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        $rez  = array();
        //найдем подписки на программу по id контракта
        if ( !$sbcs = $this->get_records(array('contractid' => $id)) )
        {// ничего не нашли
            return $rez;
        }
        foreach ( $sbcs as $sbc )
        {// для каждой из подписок соберем информацию
            $rez[] = $sbc->id;
        }
        return $rez;
    }

    /** Получает максимальный используемый семестр (параллель) среди ВСЕХ подписок
     * @return int - номер параллели
     */
    public function get_max_agenum()
    {
        //найдем подписки на программу по id контракта
        if ( !$sbc = $this->get_record(array(), "MAX(agenum) as ma") )
        {// ничего не нашли
            return 0;
        }
        return $sbc->ma;
    }

    /** Получает информацию о подписках на программу по id персоны ученика
     * @param int $id - id персоны
     * @return array - массив с информацией о подписках на программу
     */
    public function get_programmsbcs_by_personid($id)
    {
        if ( !is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        // Получаем список контрактов
        if ( !$contracts = $this->dof->storage('contracts')->get_list_by_student($id) )
        {
            return array();
        }
        // Собираем подписки по всем контрактам
        $sbcs = array();
        foreach ( $contracts as $contract )
        {
            // Сливаем массивы с наложением (наложения быть не может - при одинаковых id объекты одинаковы)
            $sbcs = $this->get_programmsbcs_by_contractid($contract->id) + $sbcs;
        }
        return $sbcs;
    }

    /** Проверяем, подписана ли персона на указанную программу
     * @param int $id - id персоны
     * @return array - массив с информацией о подписках на программу
     */
    public function is_sbc_to_programm($personid, $programmid)
    {
        // Получаем все подписки пользователя (знаю что криво, но писать sql-запрос на проверку без выборки - некогда)
        $sbcs = $this->get_programmsbcs_by_personid($personid);
        foreach ( $sbcs as $sbc )
        {
            //print_object($sbc);
            if ( $sbc->programmid == $programmid )
            {
                // Нашли
                return true;
            }
        }
        // Ничего не нашли
        return false;
    }
    
    /**
     * Автозаполнение подписки на программу для персоны
     *
     * @param int $programmid - 
     * @param int $personid - ID персоны
     * @param int $options - Массив дополнительных опций
     *      ['switch_to_active'] (bool) Привести к активному статусу
     *      ['available_status'] (array) Допустимые статусы подписки для поиска аналогов
     *      ['agroupid'] (int) - ID группы, с которой должна быть связана подписка
     *      ['agenum'] (int) - Номер потока
     *      
     * @return stdClass - Результат добавления
     *          ->status - Статус работы
     *          ->message - Cообщение
     *          ->contract - Объект договора
     *          ->programmsbc - Подписка на программу
     *          ->programm - Программа;
     *          ->person - Объект персоны
     *          ->options - Массив опций работы
     */
    public function autocomplete_add_person_programmsbcs($programmid, $personid, $options = [])
    {
        // Получение договора персоны
        $contractoptions = $options;
        $contractoptions['available_status'] = $this->dof->workflow('contracts')->get_meta_list('actual');
        
        $return = $this->dof->storage('contracts')->autocomplete_add_person_contract($personid, $contractoptions);
        
        if ( $return->status == 'error' )
        {// Ошибка при автозаполнении
            $return->message = $this->dof->get_string('error_getting_person_contract', 'programmsbcs', NULL, 'storage').'<br/>'.$return->message;
            return $return;
        }

        // Результат работы по-умолчанию
        $return->programmsbc = NULL;
        $return->programm = NULL;
    
        // Получение программы
        $programm = $this->dof->storage('programms')->get($programmid);
        if ( empty($programm) )
        {// Программа не найдена
            $return->status = 'error';
            $return->message = $this->dof->get_string('error_programm_not_found', 'programms', $programmid, 'storage');
            return $return;
        }
        $return->programm = $programm;
      
        // Поиск аналогичной подписки
        $params = [];
        $params['contractid'] = $return->contract->id;
        $params['programmid'] = $return->programm->id;
        $params['departmentid'] = $return->contract->departmentid;
        if ( isset($options['available_status']) && ! empty($options['available_status']) )
        {// Доступные статусы указаны
            $params['status'] = array_keys($options['available_status']);
        }
        if ( isset($options['agroupid']) && ! empty($options['agroupid']) )
        {// Указана группа
            $params['agroupid'] = $options['agroupid'];
        }
        if ( isset($options['agenum']) && ! empty($options['agenum']) )
        {// Указан поток
            $params['agenum'] = $options['agenum'];
        }
        // Получение подписок на программу
        $programmsbcs = $this->get_records($params);

        $now = time();
        if ( ! empty($programmsbcs) )
        {// Фильтрация найденных подписок по дате
            foreach ( $programmsbcs as $programmsbc )
            {
                if ( ! empty($programmsbc->dateend) && $programmsbc->dateend < $now )
                {// Подписка закончилась
                    continue;
                }
                if ( ! empty($programmsbc->datestart) && $programmsbc->datestart > $now )
                {// Подписка не началась
                    continue;
                }
                $return->programmsbc = $programmsbc;
            }
        }
        
        if ( empty($return->programmsbc) )
        {// Подходящая подписка не найдена
            // Автоматическое добавление подписки на программу
            $programmsbc = new stdClass();
            $programmsbc->contractid = $return->contract->id;
            $programmsbc->programmid = $return->programm->id;
            $programmsbc->departmentid = $return->contract->departmentid;
            $programmsbc->eduform = 'internal';
            $programmsbc->datestart = $now;
            $programmsbc->dateadd = $now;
            if ( isset($options['agroupid']) && ! empty($options['agroupid']) )
            {// Указана группа
                $programmsbc->agroupid = $options['agroupid'];
                $programmsbc->edutype = 'group';
            } else 
            {// Индивидуальная подписка
                $programmsbc->edutype = 'individual';
            }
            if ( isset($options['agenum']) && ! empty($options['agenum']) )
            {// Указан поток
                $programmsbc->agenum = $options['agenum'];
            } else 
            {
                $programmsbc->agenum = 0;
            }
            $programmsbc->status = 'application';
            $programmsbcid = $this->insert($programmsbc);
            if ( empty($programmsbcid) )
            {// Подписка не создана
                $return->status = 'error';
                $return->message = $this->dof->get_string('error_programmsbc_not_created', 'programmsbcs', NULL, 'storage');
                return $return;
            } else
            {// Подписка создана
                $return->programmsbc = $this->get($programmsbcid);
            }
        }
        
        if ( empty($return->programmsbc) )
        {// Подписка не найдена
            $return->programmbcs = NULL;
            $return->status = 'error';
            $return->message = $this->dof->get_string('error_programmsbcs_not_found', 'programmsbcs', $programmsbcid, 'storage');
            return $return;
        } else 
        {// Подписка найдена
            $this->send_addto_agroup($return->programmsbc->id, $return->programmsbc);
        }
        
        if ( isset($options['switch_to_active']) && ! empty($options['switch_to_active']) && $return->programmsbc->status != 'active' )
        {// Требуется перевести в активный статус
            $pluginexist = $this->dof->plugin_exists('workflow', 'programmsbcs');
            if ( $pluginexist )
            {// Плагин статусов найден
                $this->dof->workflow('programmsbcs')->change($return->programmsbc->id, 'plan');
                $changesuccess = $this->dof->workflow('programmsbcs')->change($return->programmsbc->id, 'active');
                if ( empty($changesuccess) )
                {// Невозможно перевести подписку в активный статус
                    $return->status = 'error';
                    $return->message = $this->dof->get_string('error_programmsbc_changestatus', 'programmsbcs', $return->programmsbc->id, 'storage');
                    return $return;
                }
            } else
            {// Плагин статусов не включен
                $update = new stdClass();
                $update->status = 'active';
                $update->id = $return->programmsbc->id;
                $changesuccess = $this->update($update);
                if ( empty($changesuccess) )
                {// Невозможно перевести подписку в активный статус
                    $return->status = 'error';
                    $return->message = $this->dof->get_string('error_programmsbc_changestatus', 'programmsbcs', $return->programmsbc->id, 'storage');
                    return $return;
                }
            }
            // Обновить данные подписки
            $return->programmsbc = $this->get($return->programmsbc->id);
        }
        
        // Реультат с подпиской персоны
        return $return;
    }
    
    /**
     * Обработка AJAX-запросов из форм
     *
     * @param string $querytype - Тип запроса
     * @param int $depid - ID подразделения, для которого формируются действия
     * @param mixed $data - Дополнительные данные
     * @param int $objectid - Дополнительный ID
     *
     * @return array
     */
    public function widgets_field_variants_list($querytype = '', $depid = 0, $data = '', $objectid = 0)
    {
        switch ( $querytype )
        {
            // Список групп
            case 'programmbcs_list' :
                $select = [];
    
                // Получение подразделений, доступных данному пользователю
                $options = [];
                if ( $this->dof->plugin_exists('workflow', 'departments') )
                {// Плагин статусов определен
                    $statuses = $this->dof->workflow('departments')->get_meta_list('real');
                    $statuses = array_keys($statuses);
                    $options['status'] = $statuses;
                }
                $departments = $this->dof->storage('departments')->get_departments($depid, $options);
                $availabledeps = [];
                // Проверка текущего подразделения
                if ( $this->dof->storage('departments')->is_access('use', $depid, NULL, $depid) ||
                     $this->dof->storage('departments')->is_access('view/mydep', $depid, NULL, $depid) )
                {// Доступ есть
                    $availabledeps[] = $depid;
                }
                // Проверка дочерних подразделений
                if ( ! empty($departments) )
                {
                    foreach ( $departments as $department )
                    {
                        if ( $this->dof->storage('departments')->is_access('use', $depid, NULL, $depid) ||
                             $this->dof->storage('departments')->is_access('view/mydep', $depid, NULL, $depid) )
                        {// Доступ к подразделению есть
                            $availabledeps[] = $department->id;
                        }
                    }
                }
                $availablepersons = [];
                // Получение персон, отфильтрованных с учетом данных
                if ( is_string($data) || is_int($data) )
                {// Передано значение поля
                    $params = [];
                    // Поиск по имени, коду и ID академической группы
                    $params['id'] = (int)$data;
                    $params['firstname'] = '%'.(string)$data.'%';
                    $params['lastname'] = '%'.(string)$data.'%';
                    $params['middlename'] = '%'.(string)$data.'%';
                    $sql = ' ( 
                        id = :id OR 
                        firstname LIKE :firstname OR 
                        middlename LIKE :middlename OR 
                        lastname LIKE :lastname 
                        ) ';
                    // Фильтрация по статусу
                    if ( $this->dof->plugin_exists('workflow', 'persons') )
                    {// Фильтрация по статусам
                        $statuses = $this->dof->workflow('persons')->get_meta_list('real');
                        $statuses = '"'.implode('","', array_keys($statuses)).'"';
                        $sql .= ' AND status IN ('.$statuses.') ';
                    }
                    
                    $list = $this->dof->storage('persons')->get_records_select($sql, $params);
                    if ( ! empty($list) )
                    {
                        foreach ( $list as $item )
                        {
                            $availablepersons[] = $item->id;
                        }
                    }
                }
                
                $availablecontracts = [];
                // Получение доступных договоров, отфильтрованных с учетом данных
                if ( is_string($data) || is_int($data) )
                {// Передано значение поля
                    
                    $params = [];
                    // Поиск по имени, коду и ID академической группы
                    $params['id'] = (int)$data;
                    $params['num'] = '%'.(string)$data.'%';
                    $availablepersonsstr = '"'.implode('","', $availablepersons).'"';
                    $sql = ' (
                            id = :id OR
                            num LIKE :num OR
                            sellerid IN ('.$availablepersonsstr.') OR
                            clientid IN ('.$availablepersonsstr.') OR
                            studentid IN ('.$availablepersonsstr.')
                          ) ';
                    
                    // Фильтрация по статусу
                    if ( $this->dof->plugin_exists('workflow', 'contracts') )
                    {// Фильтрация по статусам
                        $statuses = $this->dof->workflow('contracts')->get_meta_list('real');
                        $statuses = '"'.implode('","', array_keys($statuses)).'"';
                        $sql .= ' AND status IN ('.$statuses.') ';
                    }
                    
                    $list = $this->dof->storage('contracts')->get_records_select($sql, $params);
                    if ( ! empty($list) )
                    {
                        foreach ( $list as $item )
                        {
                            $availablecontracts[] = $item->id;
                        }
                    }
                }
                
                // Получение подписок на программы
                $availablebcs = [];
                // Получение доступных подписок на программы, отфильтрованных с учетом данных
                if ( is_string($data) || is_int($data) )
                {// Передано значение поля
                
                    $params = [];
                    // Поиск по имени, коду и ID академической группы
                    $params['id'] = (int)$data;
                    $availablecontractsstr = '"'.implode('","', $availablecontracts).'"';
                    $sql = ' (
                                id = :id OR
                                contractid IN ('.$availablecontractsstr.')
                              ) ';
                    
                    // Фильтрация по статусу
                    if ( $this->dof->plugin_exists('workflow', 'programmsbcs') )
                    {// Фильтрация по статусам
                        $statuses = $this->dof->workflow('programmsbcs')->get_meta_list('real');
                        $statuses = '"'.implode('","', array_keys($statuses)).'"';
                        $sql .= ' AND status IN ('.$statuses.') ';
                    }
                    // Фильтрация с учетом подразделения
                    $availabledepsstr = '"'.implode('","', $availabledeps).'"';
                    $sql .= ' AND departmentid IN ('.$availabledepsstr.') ';
                    
                    $list = $this->dof->storage('programmsbcs')->get_records_select($sql, $params);
                    
                    if ( ! empty($list) )
                    {
                        foreach ( $list as $item )
                        {
                            if ( $this->dof->storage('programmsbcs')->is_access('use', $item->id, NULL, $depid) ||
                                 $this->dof->storage('programmsbcs')->is_access('use/my', $item->id, NULL, $depid) )
                            {// Добавление элемента в список
                             
                                $personname = ' - ';
                                $contract = $this->dof->storage('contracts')->get($item->contractid);
                                if ( ! empty($contract) )
                                {
                                    $personname = $this->dof->storage('persons')->get_fullname($contract->studentid);
                                }
                                // Программа
                                $programmname = ' - ';
                                $programm = $this->dof->storage('programms')->get($item->programmid);
                                if ( ! empty($programm) )
                                {
                                   $programmname = '('.$programm->name.')';
                                }
                                
                                $select[$item->id] = [
                                    'id' => $item->id,
                                    'name' => $personname.' '.$programmname.' ['.$item->id.']'
                                ];
                            }
                        }
                    }
                }

                return $select;
                break;
            default :
                return [ 0 => $this->dof->modlib('ig')->igs('choose')];
                break;
        }
    }
    
    /**
     * Получить подписки на программы с учетом параметров
     * 
     * @param array $options - Массив параметров для получения подписок
     *             string|int|array ['ids'] - Массив идентификаторов подписок или единичный идентификатор
     *             string|int|array ['agroupids'] - Массив идентификаторов групп или единичный идентификатор
     *             array ['statuses'] - Массив статусов для фильтрации. По-умолчанию 
     *                                  производится фильтрация по реальным мета-статусам
     *
     * @param array - Массив подписок на программы 
     */
    public function get_programmsbcs_by_options($options)
    {
        $where = '';
        $filter = '';
        $params = [];
        
        // Добавление по идентификаторам
        if ( isset($options['ids']) && ! empty($options['ids']) )
        {// Указаны идентификаторы
            if ( is_string($options['ids']) || is_int($options['ids']) )
            {// Нормализация до массива
                $singleid = (int)$options['ids'];
                $options['ids'] = [$singleid];
            }
            if ( is_array($options['ids']) )
            {// Получен верный тип данных
                if ( ! empty($where) )
                {// Разделитель
                    $where .= ' OR ';
                }
                $ids = '"'.implode('","', $options['ids']).'"';
                $where .= ' id IN ('.$ids.') ';
            }
        }
        
        // Добавление по идентификаторам групп
        if ( isset($options['agroupids']) && ! empty($options['agroupids']) )
        {// Указаны идентификаторы
            if ( is_string($options['agroupids']) || is_int($options['agroupids']) )
            {// Нормализация до массива
                $singleid = (int)$options['agroupids'];
                $options['agroupids'] = [$singleid];
            }
            if ( is_array($options['agroupids']) )
            {// Получен верный тип данных
                if ( ! empty($where) )
                {// Разделитель
                    $where .= ' OR ';
                }
                $agids = '"'.implode('","', $options['agroupids']).'"';
                $where .= ' agroupid IN ('.$agids.') ';
            }
        }
        
        // Фильтрация по статусам
        if ( isset($options['statuses']) && is_array($options['statuses']) )
        {// Указаны требуемые статусы подписок
            $statuses = '"'.implode('","', $options['statuses']).'"';
            $filter .= ' status IN ('.$statuses.') ';
        } else 
        {// Фильтрация по-умолчанию
            if ( $this->dof->plugin_exists('workflow', 'programmsbcs') )
            {// Плагин статусов активен
                $statuses = $this->dof->workflow('programmsbcs')->get_meta_list('real');
                $statuses = '"'.implode('","', array_keys($statuses)).'"';
                $filter .= ' status IN ('.$statuses.') ';
            }
        }
        
        if ( ! empty($where) )
        {// Указаны параметры получения подписок
            if ( ! empty($filter) )
            {// Указан фильтр
                $where = ' ( ' .$where. ' ) AND '. $filter;
            }
        } else 
        {// Не указаны параметры получаемых подписок
            return [];
        }
        
        // Получение подписок с учетом всех параметров
        $programmbcs = $this->get_records_select($where, $params);
        
        return $programmbcs;
    }
    
    /**
     * Отсортировать записи по полю персоны, которой принадлежит данная подписка
     * 
     * @param array $ids - Массив идентификаторов подписок
     * @param string $sortfield - Поле, по которому будет происходить сортировка
     * @param string $direction - Направление сортировки
     * @param string $returnfields - Список полей, которые требуется получить
     * @param number $limitfrom - Начало среза
     * @param number $limitnum - Число записей
     * 
     * @return array - Массив подписок, отсортированных по полю
     */
    public function get_records_sort_person($ids, $sortfield = 'id', $direction = 'ASC', $returnfields = '*', $limitfrom = 1, $limitnum = 0)
    {
        // Определение таблиц
        $tblpersons = $this->prefix().$this->dof->storage('persons')->tablename();
        $tblcontracts = $this->prefix().$this->dof->storage('contracts')->tablename();
        $tblsbc = $this->prefix().$this->tablename();
        
        // Формирование выборки полей
        $select = (array)explode(',', $returnfields);
        foreach ( $select as &$field )
        {
            $field = 'sbc.'.trim($field);
        }
        $select = implode(', ', $select);
        $ids = implode(',', $ids);
        
        $sql = "SELECT $select
                FROM {$tblsbc} as sbc 
                    INNER JOIN {$tblcontracts} as c ON sbc.contractid = c.id
                    INNER JOIN {$tblpersons} as p ON c.studentid = p.id
                WHERE sbc.id IN ($ids)
                ORDER BY p.$sortfield $direction
                LIMIT $limitnum OFFSET $limitfrom ";
        return $this->get_records_sql($sql);
    }
    
    /**
     * Отсортировать записи по полю программы, которой принадлежит данная подписка
     *
     * @param array $ids - Массив идентификаторов подписок
     * @param string $sortfield - Поле, по которому будет происходить сортировка
     * @param string $direction - Направление сортировки
     * @param string $returnfields - Список полей, которые требуется получить
     * @param number $limitfrom - Начало среза
     * @param number $limitnum - Число записей
     *
     * @return array - Массив подписок, отсортированных по полю
     */
    public function get_records_sort_programm($ids, $sortfield = 'id', $direction = 'ASC', $returnfields = '*', $limitfrom = 1, $limitnum = 0)
    {
        // Определение таблиц
        $tblprogramms = $this->prefix().$this->dof->storage('programms')->tablename();
        $tblsbc = $this->prefix().$this->tablename();
    
        // Формирование выборки полей
        $select = (array)explode(',', $returnfields);
        foreach ( $select as &$field )
        {
            $field = 'sbc.'.trim($field);
        }
        $select = implode(', ', $select);
        $ids = implode(',', $ids);
        
        $sql = "SELECT $select
                FROM {$tblsbc} as sbc
                    INNER JOIN {$tblprogramms} as p ON sbc.programmid = p.id
                WHERE sbc.id IN ($ids)
                ORDER BY p.$sortfield $direction
                LIMIT $limitnum OFFSET $limitfrom ";
        
        return $this->get_records_sql($sql);
    }
    
    /**
     * Отсортировать записи по полю группы, которой принадлежит данная подписка
     *
     * @param array $ids - Массив идентификаторов подписок
     * @param string $sortfield - Поле, по которому будет происходить сортировка
     * @param string $direction - Направление сортировки
     * @param string $returnfields - Список полей, которые требуется получить
     * @param number $limitfrom - Начало среза
     * @param number $limitnum - Число записей
     *
     * @return array - Массив подписок, отсортированных по полю
     */
    public function get_records_sort_agroup($ids, $sortfield = 'id', $direction = 'ASC', $returnfields = '*', $limitfrom = 1, $limitnum = 0)
    {
        if ( $this->dof->plugin_exists('storage', 'agroups') )
        {// Хранилище групп определено
            // Определение таблиц
            $tblagroup = $this->prefix().$this->dof->storage('agroups')->tablename();
            $tblsbc = $this->prefix().$this->tablename();
            
            // Формирование выборки полей
            $select = (array)explode(',', $returnfields);
            foreach ( $select as &$field )
            {
                $field = 'sbc.'.trim($field);
            }
            $select = implode(', ', $select);
            $ids = implode(',', $ids);
            
            $sql = "SELECT $select
                    FROM {$tblsbc} as sbc
                    LEFT JOIN {$tblagroup} as a ON sbc.agroupid = a.id
                    WHERE sbc.id IN ($ids)
                    ORDER BY a.$sortfield $direction
                    LIMIT $limitnum OFFSET $limitfrom ";
            
            return $this->get_records_sql($sql);
        } else 
        {// Хранилище не найдено
            return $this->get_records(['id' => $ids], '', $returnfields, $limitfrom, $limitnum);
        }
    }
}
?>