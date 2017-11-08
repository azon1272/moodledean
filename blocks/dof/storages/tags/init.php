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

// Подключаем абстрактный класс
require_once $DOF->plugin_path('storage', 'tags','/tags.php');

/** 
 * Хранилище тегов
 */
class dof_storage_tags extends dof_storage implements dof_storage_config_interface
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
        return 2014122000;
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
        return 'tags';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
return array('storage'=>array('config' => 2011080900,
                              'acl'    => 2011041800));
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
        return array('storage'=>array('acl'    => 2011040504,
                                      'config' => 2011080900));
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
    
    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются ( moodleid )
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        { // Доступ для Администратора разрешен
            return true;
        }
        
        // Выполняем действия в зависимости от задачи
        switch ($do)
        {
            case 'view/owner' : // Просмотр публичных и своих тегов
                
                /* Проверка переданных данных */
                // Тег
                if ( empty($objid) )
                {// ID объекта не передан, значит проверять нечего - вернем false
                    dof_debugging(
                        $this->dof->get_string('error_no_tagid', 'tags', null, 'storage'),
                        DEBUG_DEVELOPER
                    );
                    return false;
                }
                // Проверяем ID тега
                $tag = $this->get($objid);
                if ( empty($tag) )
                {// Такого тега нет
                    dof_debugging(
                        $this->dof->get_string('error_tag_not_found', 'tags', null, 'storage'),
                        DEBUG_DEVELOPER
                    );
                    return false;
                }
                // Пользователь
                if ( empty($userid) )
                {// Пользователь не передана - получаем ее
                    $person = $this->dof->storage('persons')->get_bu();
                    $personid = $person->id;
                    
                } else 
                {// Пользователь передан
                    // Получаем id персоны по moodleid
                    $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
                    if ( empty($personid) )
                    {// Пользователь не найден
                        dof_debugging(
                            $this->dof->get_string('error_person_not_found', 'tags', null, 'storage'),
                            DEBUG_DEVELOPER
                        );
                        return false;
                    }
                }
                // Подразделение
                if ( empty($depid) )
                {// Подразделение не передано, возьмем подразделение пользователя
                    $person = $this->dof->storage('persons')->get($personid);
                    $depid = $person->departmentid;
                } 

                /* Проверка доступа к тегу */
                
                // Получаем все дочерние подразделения тега
                $deplist = $this->dof->storage('departments')->
                                departments_list_subordinated($depid);
                // Проверим доступ к тегу
                if ( 
                       ( $tag->ownerid == 0 || // Тег публичный
                         $tag->ownerid == $personid ) && // Приватный тег пользователя
                       ( $tag->departmentid == $depid || // Тег текущего подразделения
                         array_key_exists($tag->departmentid, $deplist) ) // Тег дочернего подразделения
                   )
                {
                    // Проверка по владельцу и подразделению пройдена
                    return true;
                }
                // Делаем регресс к проверке общего права для поддержки доверенностей
                $do = 'view';
                break;
        
            case 'edit/owner' : // Редактирование только своих тегов ( НЕ ПУБЛИЧНЫХ )
        
                /* Проверка переданных данных */
                // Тег
                if ( empty($objid) )
                {// ID объекта не передан, значит проверять нечего - вернем false
                    dof_debugging(
                        $this->dof->get_string('error_no_tagid', 'tags', null, 'storage'),
                        DEBUG_DEVELOPER
                    );
                    return false;
                }
                // Проверяем ID тега
                $tag = $this->get($objid);
                if ( empty($tag) )
                {// Такого тега нет
                    dof_debugging(
                        $this->dof->get_string('error_tag_not_found', 'tags', null, 'storage'),
                        DEBUG_DEVELOPER
                    );
                    return false;
                }
                // Пользователь
                if ( empty($userid) )
                {// Пользователь не передана - получаем ее
                    $person = $this->dof->storage('persons')->get_bu();
                    $personid = $person->id;
                    
                } else 
                {// Пользователь передан
                    // Получаем id персоны по moodleid
                    $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
                    if ( empty($personid) )
                    {// Пользователь не найден
                        dof_debugging(
                            $this->dof->get_string('error_person_not_found', 'tags', null, 'storage'),
                            DEBUG_DEVELOPER
                        );
                        return false;
                    }
                }
                // Подразделение
                if ( empty($depid) )
                {// Подразделение не передано, возьмем подразделение пользователя
                    $person = $this->dof->storage('persons')->get($personid);
                    $depid = $person->departmentid;
                } 
                
                /* Проверка доступа к тегу */
                
                // Получаем все дочерние подразделения тега
                $deplist = $this->dof->storage('departments')->
                                departments_list_subordinated($depid);
                // Проверим доступ к тегу
                if ( 
                       ( $tag->ownerid == $personid ) && // Приватный тег пользователя
                       ( $tag->departmentid == $depid || // Тег текущего подразделения
                         array_key_exists($tag->departmentid, $deplist) ) // Тег дочернего подразделения
                   )
                {
                    // Проверка по владельцу и подразделению пройдена
                    return true;
                }
                // Делаем регресс к проверке общего права для поддержки доверенностей
                $do = 'edit';
                break;
        
            default :
                break;
        }
        
        // Получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // Получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);   
        // Проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть - заканчиваем обработку
            return true;
        } 
        // Права нет
        return false;
    }
    
    /** 
     * Требует наличия полномочия на совершение действий
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
        return 'block_dof_s_tags';
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
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
        $a = array();
        
        $a['view']       = array('roles'=>array('manager')); // Общая проверка по доверенностям на просмотр
        $a['view/owner'] = array('roles'=>array()); // Проверка по владельцу тега
        $a['edit']       = array('roles'=>array('manager')); // Общая проверка по доверенностям на редактирование
        $a['edit/owner'] = array('roles'=>array()); // Проверка на редактирование приватных тегов
        $a['create']     = array('roles'=>array('manager', 'methodist', 'teacher')); // Проверка на создание новых тегов
        $a['delete']     = array('roles'=>array('manager')); // Проверка на удаление

        return $a;
    }

    /** Функция получения настроек для плагина
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
     *  Получить отфильтрованный список тегов
     *  
     *  Метод предназначен для получения массива тегов из БД для передачи 
     *  функции печати на странице показа всех тегов. Значения берутся из ключей массива
     *  
     *  @param object $filters - объект с параметрами, по которым идет отбор тегов
     *        
     *         -> departmentid(int|array) - маcсив подразделений(или одно подразделение), 
     *                               к которым принадлежат возвращаемые теги 
     *         -> class(str|array) - масcив классов(или один класс), 
     *                               которым могут принадлежать возвращаемые теги
     *         -> ownerid(int|array) - масcив владельцев(или один владелец), 
     *                                 которым могут принадлежать возвращаемые теги
     *         -> parentid(int|array) - маcсив id родительских тегов(или один id), 
     *                                  которым могут принадлежать возвращаемые теги
     *         -> status(str|array) - маcсив статусов(или один статус), 
     *                                которые могут иметь возвращаемые теги
     *           
     *  @param int $order - сортировка, 0 - прямая, 1 - обратная
     *  @param int $from - смещение выборки 
     *  @param int $limit - чосло строк (0 - все строки)  
     *  
     *  @return array массив тегов, хранящихся в БД, отфильтрованный и отсортированный   
     */
    public function get_list_tags($filters, $order = 0, $limitfrom = 0, $limitnum = 0)
    {
        // Готовим начальные данные
        $params = array();
        $select = '';

        // Фильтрация по подразделению
        if ( isset($filters->departmentid) )
        {
            $this->get_filtered_params_sql($filters->departmentid, $select, $params, 'departmentid');
        }
        // Фильтрация по классу
        if ( isset($filters->class) )
        {
            $this->get_filtered_params_sql($filters->class, $select, $params, 'class');
        }
        // Фильтрация по владельцу
        if ( isset($filters->ownerid) )
        {
            $this->get_filtered_params_sql($filters->ownerid, $select, $params, 'ownerid');
        }
        // Фильтрация по родителю
        if ( isset($filters->parentid) )
        {
            $this->get_filtered_params_sql($filters->parentid, $select, $params, 'parentid');
        }
        // Фильтрация по статусу
        if ( isset($filters->status) )
        {
            $this->get_filtered_params_sql($filters->status, $select, $params, 'status');
        }
        
        // Добавляем сортировку
        if ( ! $order )
        {
            $ordering = 'code ASC';
        } else
        {
            $ordering = 'code DESC';
        }

        // Получаем выборку
        return $this->get_records_select($select, $params, $ordering, '*', $limitfrom, $limitnum);
    }
    
    
    /**
     * Добавить тег в БД
     * 
     * На вход получает все необходимые данные для добавления тега.
     * 
     * Пример параметров для класса Select (все активные объекты из одного справочника)
     * $data->options->storage = 'persons';          
     *                  
     * @param string $class - класс тега
     * @param string $code - код тега
     * @param int $departmentid - ID подразделения
     * @param string $alias - алиас тега
     * @param string $about - описание тега
     * @param int $parentid - ID родителького тега
     * @param int $ownerid - владелец тега (0 - публичный)
     * @param int $cron - требуется ли запуск крона
     * @param int $cronrepeate - периодичность запуска крона
     * @param object $options - параметры тега
     * 
     * @return object - объект тега
     */
    public function add_tag($class, $code, $departmentid, $alias='', $about='', $parentid = 0, $ownerid = 0, $cron = -1, $cronrepeate = 0, $options = NULL )
    {
        /* Проверки */
        
        // Готовим объект для ошибок
        $error = new stdClass();
        
        //Перевод в нижний регистр класса тега ,транслитерация и очистка
        $class = $this->translit('ru', $class);
        // Проверка размера класса
        if ( strlen($class) > 20 || $class == '' )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_class', 'tags', null, 'storage');
            return $error;
        }
        
        //Перевод в нижний регистр кода тега ,транслитерация и очистка
        $code = $this->translit('ru', $code);
        // Проверка размера кода
        if ( strlen($code) > 20 || $code == '' )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_code', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка Подразделения
        if ( $departmentid < 1 )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_departementid', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка ID родителя
        if ( $parentid < 0 || ( ! $this->get($parentid) && $parentid > 0 ) )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_parentid', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка ID владельца
        if ( $ownerid < 0 || ( ! $this->dof->storage('persons')->get($ownerid) && $ownerid > 0 ) )
        {
            // Если владелец не найден
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_missed_ownerid', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка уникальности кода тега внутри родителя и подразделения
        if ( $this->get_record(array(
                'code' => $code, 
                'parentid' => $parentid, 
                'departmentid' => $departmentid
        )) ) 
        {// Такой тег уже найден, следовательна нарушается уникальность
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_not_unique_code', 'tags', null, 'storage');
            return $error;
        }
        
        /* Формирование тега */
        
        $newtag = new stdClass();
        // Получим пустой объект класса для проверки тега
        $tagobject = $this->tagclass($class, $newtag);
        // Проверка объекта класса
        if ( empty($tagobject) )
        {
            // Если нет такого класса
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_class_define', 'tags', null, 'storage');
            return $error;
        }

        // Подготовка объекта для встваки в БД, производим проверку через класс
        $result = $tagobject->check_tag($code, $departmentid, $alias, $about, $parentid, $ownerid, $cron, $cronrepeate, $options);
        
        // Если при проверке объекта произошла ошибка, не производим добавление
        if ( $result->errorstatus )
        {
            $result->errortext = $this->dof->get_string('error_required_options_undefined', 'tags', null, 'storage');
            return $result;
        }
        
        // Добавляем в БД
        $tagid = $this->insert($result);
        
        if ( empty($tagid) )
        {
            // Если добавление не удалось
            $result->errorstatus = true;
            $result->errortext = $this->dof->get_string('error_insert_tag', 'tags', null, 'storage');
            return $result;
        } 
        // Добавляем к результату ID
        $result->id = $tagid;
        
        // Возвращаем успешно добавленную запись
        return $result;
    }
    
    
    /**
     * Обновить тег в БД
     *
     * На вход получает все необходимые данные для обновления тега.
     *
     * @param int $id - ID тега
     * @param string $class - класс тега
     * @param string $code - код тега
     * @param int $department - код подразделения
     * @param string $alias - алиас тега
     * @param string $about - описание тега
     * @param int $parentid - id родителького тега
     * @param int $cron - требуется ли запуск крона
     * @param int $cronrepeate - периодичность запуска крона
     * @param object $options - параметры тега
     *
     * @return object - объект тега
     */
    public function update_tag($id, $class, $code, $departmentid, $alias = '', $about = '', $parentid = 0,  $ownerid = 0, $cron = -1, $cronrepeate = 0, $options = NULL )
    {
        /* Проверки */
        
        // Готовим объект для ошибок
        $error = new stdClass();
        
        //Перевод в нижний регистр класса тега ,транслитерация и очистка
        $class = $this->translit('ru', $class);
        // Проверка размера класса
        if ( strlen($class) > 20 || $class == '' )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_class', 'tags', null, 'storage');
            return $error;
        }
        
        //Перевод в нижний регистр кода тега ,транслитерация и очистка
        $code = $this->translit('ru', $code);
        // Проверка размера кода
        if ( strlen($code) > 20 || $code == '' )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_code', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка Подразделения
        if ( $departmentid < 1 )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_departementid', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка ID родителя
        if ( $parentid < 0 || ( ! $this->get($parentid) && $parentid > 0 ) )
        {
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_unvalid_parentid', 'tags', null, 'storage');
            return $error;
        }
        
        // Проверка ID владельца
        if ( $ownerid < 0 || ( ! $this->dof->storage('persons')->get($ownerid) && $ownerid > 0 ) )
        {
            // Если владелец не найден
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_missed_ownerid', 'tags', null, 'storage');
            return $error;
        }
        
        // Получаем тег
        $tag = $this->get($id);
        // Проверка существования тега
        if ( empty($tag) )
        {
            // Если тег не найден
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_tag_not_found', 'tags', null, 'storage');
            return $error;
        }
        // Если у нас поменялась одна из ключевых опций, проверим на уникальность
        if ( $tag->code <> $code || $tag->parentid <> $parentid || $tag->departmentid <> $departmentid )
        {
            // Проверка уникальности кода тега внутри родителя и подразделения
            if ( $this->get_record(array(
                    'code' => $code,
                    'parentid' => $parentid,
                    'departmentid' => $departmentid
            )) )
            {// Такой тег уже найден, следовательна нарушается уникальность
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_not_unique_code', 'tags', null, 'storage');
            return $error;
            }
        }
        
        /* Формирование тега */
        
        // Получим объект класса
        $tagobject = $this->tagclass($class, $tag);
        // Проверка объекта класса
        if ( empty($tagobject) )
        {
            // Если нет такого класса
            $error->errorstatus = true;
            $error->errortext = $this->dof->get_string('error_class_define', 'tags', null, 'storage');
            return $error;
        }
    
        // Подготовка объекта для обновления в БД, производим проверку через класс
        $result = $tagobject->check_tag($code, $departmentid, $alias, $about, $parentid, $ownerid, $cron, $cronrepeate, $options);
        
        // Если при проверке объекта произошла ошибка, не производим добавление
        if ( $result->errorstatus )
        {
            $result->errortext = $this->dof->get_string('error_required_options_undefined', 'tags', null, 'storage');
            return $result;
        }
        // Добавляем к результату ID тега
        $result->id = $id;
        
        // Обновлем в БД
        if ( ! $this->update($result) )
        {
            // Если обновление не удалось
            $result->errorstatus = true;
            $result->errortext = $this->dof->get_string('error_update_tag', 'tags', null, 'storage');
            return $result;
        }
        
        // Возвращаем обновленную запись
        return $result;
    }
    
    
    /**
     * Создание объекта тега на основе класса
     * 
     * В зависимости от $tagobjectdb
     * 
     * Если объект тега не передан - 
     *     Возвращаем имя класса для запуска статических методов класса
     * 
     * Если $tagobjectdb передан - 
     *     Создаем объект класса тега
     * 
     * @param string $class - класс тега
     * @param object $object - объект с дополнительными параметрами
     * 
     * @return object|bool|string 
     *      объект класса тега, либо false в случае ошибки, либо имя класса
     */
    public function tagclass($class, $options = NULL)
    {
        // Получаем имя файла
        $filepath = $this->dof->plugin_path('storage', 'tags', '/classes/'.$class.'/init.php');
        
        // Получаем имя класса
        $classname = 'dof_storage_tags_tags_'.$class;
        
        if ( ! file_exists($filepath) )
        {// Нет файла с указанным названием
            return false;
        }
        
        // подключаем файл с классом тега
        require_once($filepath);
        
        if ( ! class_exists($classname) )
        {// в файле нет нужного класса
            return false;
        }
        
        if ( ! is_object($options) )
        {
            // Возвращаем неинициализированный класс
            return $classname;
            
        } else 
        {
            // Возвращаем объект класса
            return $classname::getInstance($this->dof, $options);
        }
    }
    
    /**
     * Возвращает объект тега по его ID
     *
     * @param int $tagid - ID тега
     * @return object|bool объект класса тега и false в случае ошибки
     */
    public function tag($tagid)
    {
        // Получаем объект тега из БД
        $tagobjectdb = $this->get($tagid);
        
        // Если не нашли объект в БД
        if ( empty($tagobjectdb) )
        {
            return false;
        }
        
        // Получаем объект класса тега
        $tagobject = $this->tagclass($tagobjectdb->class, $tagobjectdb);

        // Возвтращаем объект класса тега
        return $tagobject;
    }
    
    /**
     * Метод добавления тегу информации по прошедшей перелинковке
     * 
     * @param bool $sucсess - true при успешной пролинковке и false при ошибке
     * @param int $tagid - ID тега
     * @param int $timestamp - Время начала линковки
     */
    public function set_croninfo($success, $tagid, $timestamp)
    {
        // Формируем объект для обновления
        $update = new stdClass();
       
        if ( $success )
        {// Если обновление прошло успешно - обновляем дату и статус
            $update->cronstatus = 'ok';
            $update->crondone = $timestamp;
        } else 
        {// Если обновление ошибочно - обновляем статус
            $update->cronstatus = 'error';
        }
        return $this->update($update, $tagid);
    }
    
    /**
     * Метод формирования и добавления параметров к фильтрации
     *
     * Метод добавляет элементы в строку sql-фрагмента и массив плейсхолдеров
     *
     * @param int|str|array $filter - элемент или массив элементов для добавления
     * @param str $select - ссылка на строку с фрагментом запроса
     * @param array $params - массив плейсхолдеров
     * @param string $name - название поля в БД, по которому добавится фильтрация
     * 
     * @return null
     */
    private function get_filtered_params_sql($filter, &$select, &$params, $name)
    {
        $list = '';
        if ( is_array($filter) )
        {// Передан массив
            
            if ( ! empty($select) )
            {// В строке уже есть условие, добавим AND
                $select .= ' AND ';
            }
               
            // Добавляем элементы
            foreach ( $filter as $key => $value )
            {
                if ( ! empty($list) )
                {// В строке уже есть условия, поэтому добавляем OR
                    $list .= ' OR ';
                }
                // Добавим элемент к фильтрам
                $params[$name.$key] = $key;
                $list .= $name.' = :'.$name.$key.'';
            }
            
            // Заканчиваем фрагмент и оборачиваем его в скобки
            $select .= ' ( '.$list.' ) ';
    
        } else
        { // Передан один элемент
            if ( ! empty($select) )
            {// В строке уже есть условие, добавим AND
                $select .= ' AND ';
            }
            // Добавим элемент к фильтрам
            $params[$name.$filter] = $filter;
            $select .= $name.' = :'.$name.$filter.'';
        }
    }
    
    /** 
     * Транслителировать строку в латиницу
     * 
     * @param string $lang - двухбуквенный код языка
     * @param string $string - строка
     * @param bool $small - перевод в нижний регистр
     * @param bool $clear - очистка от не-буквенных символов
     * @return string
     */
    private function translit($lang, $string, $small = true, $clear = true)
    {
        // Формируем массив транслитерации
        if ( $lang === 'ru' )
        {
            $alfabet = array(
                    'а' => 'a', 'А' => 'A', 'б' => 'b', 'Б' => 'B', 'в' => 'v', 'В' => 'V',
                    'г' => 'g', 'Г' => 'G', 'д' => 'd', 'Д' => 'D', 'е' => 'e', 'Е' => 'E',
                    'ё' => 'jo', 'Ё' => 'Jo', 'ж' => 'zh', 'Ж' => 'Zh', 'з' => 'z', 'З' => 'Z',
                    'и' => 'i', 'И' => 'I', 'й' => 'j', 'Й' => 'J', 'к' => 'k', 'К' => 'K',
                    'л' => 'l', 'Л' => 'L', 'м' => 'm', 'М' => 'M', 'н' => 'n', 'Н' => 'N',
                    'о' => 'o', 'О' => 'O', 'п' => 'p', 'П' => 'P', 'р' => 'r', 'Р' => 'R',
                    'с' => 's', 'С' => 'S', 'т' => 't', 'Т' => 'T', 'у' => 'u', 'У' => 'U',
                    'ф' => 'f', 'Ф' => 'F', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'C',
                    'ч' => 'ch', 'Ч' => 'Ch', 'ш' => 'sh', 'Ш' => 'Sh', 'щ' => 'shh', 'Щ' => 'Shh',
                    'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'Y', 'ь' => "", 'Ь' => "",
                    'э' => 'e', 'Э' => 'E', 'ю' => 'ju', 'Ю' => 'Ju', 'я' => 'ja', 'Я' => 'Ja');
        }
        
        // Переводим в транслит
        $string = strtr($string, $alfabet);
        
        // Если требуется перевести в нижний регистр
        if ( $small )
        {
            $string = textlib::strtolower($string);
        }
        
        // Если требуется очистить от не-буквенных символов 
        if ( $clear )
        {
            $string = preg_replace ("/[^a-zA-ZА-Яа-я\s]/","",$string);
        }
        
        // Чтоб не было конфликтов перед обработкой убираем экранирование
        return addslashes($string);
    }
}
    
?>