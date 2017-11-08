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
 * Хранилище ссылок на объекты для тегов
 */
class dof_storage_taglinks extends dof_storage implements dof_storage_config_interface
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
        return 2014120100;
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
        return 'taglinks';
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
                                      'acl'   => 2011041800));
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
                                      'config' => 2011080900 )
        );
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
     * @return bool|int - false для запуска раз в 15 минут
     *                    int для периода запуска
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
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
        
        switch ( $do )
        {
            case 'view' :
                // Инициализируем результат проверок
                $return = true;
                // Если линковка не передана, возвращаем false
                if ( empty($objid) )
                {
                    return false;
                }
                // Получаем линковку
                $taglink = $this->get($objid); 
                // Проверяем доступ. Может ли вообще пользователь смотреть информацию по тегу?
                if ( ! $this->dof->storage('tags')->is_access('view/owner', $taglink->tagid) )
                {// Пользователь не имеет доступа к тегу - владельцу линковки
                    $return = false;
                }
                // Подразделение линковки и текущее подразделение совпадают?
                if ( $taglink->departmentid > 0 && $taglink->departmentid <> $depid )
                {
                    $return = false;
                }
                break;
            default:
                break;
        }
        
        // Производим проверку в соответствии с доверенностями
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid); 
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return ( true || $return );
        }
        return ( false || $return );
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
        return 'block_dof_s_taglinks';
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
      
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles'=>array('manager','teacher','methodist'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['create'] = array('roles'=>array('manager'));
        $a['delete'] = array('roles'=>array('manager'));

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
     * Метод для линковки объекта к тегу
     * 
     * Вызывает map функцию объекта класса и в зависимости от результата
     * работы этой функции производит действия над линком, 
     * удаляя, обновляя или создавая его.
     *  
     * @param object $tagid - ID тега
     * @param string $plugintype - тип плагина, которому принадлежит объект
     * @param string $plugincode - код плагина, которому принадлежит объект
     * @param int $objectid - id объекта для линка
     * @param int $departmenid - id подразделения
     * @param object $manualoptions - опции для ручного линка
     * @param int $updatemark - время начала процедуры линковки
     * 
     * @return string $action - сообщение о выполненных действиях
     */
    public function link_tag($tagid, $plugintype, $plugincode, $objectid, $departmenid=0, $manualoptions=null,$updatemark=null)
    {
        // Получаем объект тега
        $tagobjectdb = $this->dof->storage('tags')->get($tagid);
        if ( empty($tagobjectdb) )
        {// Не нашли тег
            return false;
        }
        
        // Объект класса тега
        $tagobject = $this->dof->storage('tags')->tag($tagid);
        
        // Маппинг
        $result = $tagobject->map($tagobjectdb, $plugintype, $plugincode, $objectid, $departmenid);
        
        // Если функция вернула NULL, ничего не делаем, при проверке метки обновления
        // статус этой записи будет автоматически сменен на deleted
        if ( is_null($result) )
        {
           return 'not_actual_link';
        }
        
        // Если объект удовлетворяет параметрам тега
        // производим либо добавление, либо обновление
        if ( is_object($result) )
        {
            // Добавляем updatemark
            $result->updatemark = $updatemark;
            
            // Получаем фильтры
            $filters = new stdClass();
            $filters->tagid = $tagid;
            $filters->plugintype = $plugintype;
            $filters->plugincode = $plugincode;
            $filters->objectid = $objectid;
            
            if ( $this->dof->plugin_exists('workflow', 'taglinks') )
            {// Плагин статусов есть
                // Получаем реальные статусы
                $statuses = $this->dof->workflow('taglinks')->get_meta_list('real');
            } else
            {// Плагина нет - фильтруем только по активным
                $statuses = 'active';
            }
            // Добавляем  статусы
            $filters->status = $statuses;
            
            // Проверяем, есть ли аналогичная запись в БД, отсортируем по updatemark
            $taglinkobjects = $this->get_list_taglinks($filters, 1, 0, 0, 'updatemark');
            
            // Если в базе нет добавляемой записи
            if ( empty($taglinkobjects) )
            {
                // Добавляем линк
                $this->insert($result);
                return 'insert';
            } else 
            {
                // Если вернуло несколько линковок(нештатная ситуация) - возьмем последнюю по updatemark
                $taglinkobject = array_shift($taglinkobjects);
                // Запись в базе есть, надо сравнить поля
                if ( $result->sortname == $taglinkobject->sortname &&
                     $result->departmentid == $taglinkobject->departmentid &&
                     $result->infotext == $taglinkobject->infotext &&
                     $result->infoserial == $taglinkobject->infoserial
                   )
                {
                    //Полное обновление не требуется, добавим лишь updatemark
                    $update = new stdClass();
                    $update->updatemark = $updatemark;
                    $this->update($update, $taglinkobject->id);
                    return 'actual_link';
                    
                } else 
                {
                    // Записи отличаются - обновляем
                    $this->update($result, $taglinkobject->id);
                    return 'update';
                }
            }
        }
    }
    
    /**
     * Принудительная отлинковка объекта от тега
     * 
     * @param int $taglinkid - ID линка
     * 
     * @return boolean - false в случае ошибки
     */
    public function unlink_tag_manual($taglinkid)
    {
        // Получили линковку
        $taglink = $this->dof->storage('tagkinks')->get($taglinkid);
        if ( empty($taglink) )
        {// Не нашли линковку
            return false;
        }
        
        // Получаем объект тега
        $tagobjectdb = $this->dof->storage('tags')->get($tagid);
        if ( empty($tagobjectdb) )
        {// Не нашли тег
            return false;
        }
        
        // Получаем класс тега
        $tagclass = $this->dof->storage('tags')->tagclass($tagobjectdb->class);
        if ( empty($tagclass) )
        {// Не нашли класс
            return false;
        }
        
        // Проверяем возможность отлинковки
        if ( $tagclass::is_manual_unlink() )
        {// Отлинковка возможна - переводим статус линковки на 'Удалено' 
            if ( $this->dof->plugin_exists('workflow', 'taglinks') )
            {// Плагин статусов есть
                // Сменяем статус
                if ( $this->dof->workflow('taglinks')->change($taglinkid, 'deleted') )
                {
                    return true;
                }
            } else 
            {// Плагина нет - делаем апдейт вручную
                // Формируем объект
                $update = new stdClass();
                $update->status = 'deleted';
                // Обновляем
                if ( $this->update($update, $taglinkid) )
                {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Самостоятельное сканирование справочников для тега и обновление выборки
     * 
     * @param id $tagid - ID тега, для которого будет проведено обновление
     * @param id $depid - ID подразделения
     * @param int $updatemark - время начала обновления для отбора устаревших
     *                          линков
     * @param int $limit - лимит записей для сканирования
     * @param object $continue - объект с данными для продолжения 
     *                          прерваного сканирования
     *                          
     * @return bool - false в случае ошибки
     *              - true в случае успешного завершения     
     */
    public function rescan_taglinks($tagid, $depid = 0, $updatemark=null, $limit=0, $continue=null)
    {     
        // Получаем объект тега
        $tagobjectdb = $this->dof->storage('tags')->get($tagid);
        if ( empty($tagobjectdb) )
        {// Не нашли тег
            return false;
        }
        
        // Объект класса тега
        $tagobject = $this->dof->storage('tags')->tag($tagid);
        
        // Получаем объекты для формирования выборки из подходящих по параметрам
        $list = $tagobject->get_rescanobjects_list($tagobjectdb, $depid);
        
        // Если время генерации не указано, то берем текущее
        if ( is_null($updatemark) )
        {
            $updatemark = date('U');
        }
     
        /* Линк объектов */
        
        // Перебор типов плагинов
        foreach ( $list->list as $ptype => $pcodes )
        {
            // Перебор Кодов плагинов
            foreach ( $pcodes as $pcode => $objects )
            {
                // Перебор объектов в конкретном плагине
                foreach ( $objects as $object )
                {
                    // Пробуем прилинковать каждый объект
                    $this->link_tag($tagid, $ptype, $pcode, $object->id, $depid, null, $updatemark);
                }
            }
        }
        
        /* Провели процесс перелинковки , теперь нужно сменить статус на удален
         у неактуальных записей */
    
        // Создаем объект для апдейта
        $update = new stdClass();
        $update->status = 'deleted';
        
        // Получаем id записей, которые необходимо удалить
        $params = array('status' => 'deleted');
        $params['updatemark'] = $updatemark;
        $params['tagid'] = $tagid;
        
        $linksfordelete = $this->get_records_select(
                'status <> :status AND updatemark < :updatemark AND tagid = :tagid',
                $params,
                '',
                'id');
        
        foreach ($linksfordelete as $object)
        {
            // Обновляем статус у неактуальных записей
            $this->update($update,$object->id, false, true);
        }
        return true;
    }
    
    /**
     *  Получить отфильтрованный список линковок
     *
     *  Метод предназначен для получения массива линковок. 
     *  Значения фильтров берутся из ключей массива
     *
     *  @param object $filters - объект с параметрами, по которым идет отбор линковок
     *         -> tagid(int|array) - маcсив ID тегов (или один ID),
     *                               к которым принадлежат возвращаемые линковки
     *         -> departmentid(int|array) - маcсив подразделений(или одно подразделение),
     *                               к которым принадлежат возвращаемые линковки
     *         -> status(str|array) - маcсив статусов(или один статус),
     *                                которые могут иметь возвращаемые линковки
     *
     *  @param int $order - сортировка, 0 - прямая, 1 - обратная
     *  @param int $from - смещение выборки
     *  @param int $limit - чосло строк (0 - все строки)
     *  @param str $sortnamefield - имя поля, по которому происходит сортировка
     *
     *  @return array массив линковок, хранящихся в БД, отфильтрованный и отсортированный
     */
    public function get_list_taglinks($filters, $order = 0, $limitfrom = 0, $limitnum = 0, $sortnamefield = 'sortname')
    {
        // Готовим начальные данные
        $params = array();
        $select = '';
    
        // Фильтрация по тегу
        if ( isset($filters->tagid) )
        {
            $this->get_filtered_params_sql($filters->tagid, $select, $params, 'tagid');
        }
        // Фильтрация по plugintype
        if ( isset($filters->plugintype) )
        {
            $this->get_filtered_params_sql($filters->plugintype, $select, $params, 'plugintype');
        }
        // Фильтрация по plugincode
        if ( isset($filters->plugincode) )
        {
            $this->get_filtered_params_sql($filters->plugincode, $select, $params, 'plugincode');
        }
        // Фильтрация по objectid
        if ( isset($filters->objectid) )
        {
            $this->get_filtered_params_sql($filters->objectid, $select, $params, 'objectid');
        }
        // Фильтрация по подразделению
        if ( isset($filters->departmentid) )
        {
            $this->get_filtered_params_sql($filters->departmentid, $select, $params, 'departmentid');
        }
        // Фильтрация по статусу
        if ( isset($filters->status) )
        {
            $this->get_filtered_params_sql($filters->status, $select, $params, 'status');
        }
    
        // Добавляем сортировку
        if ( ! $order )
        {
            $ordering = 'sortname ASC';
        } else
        {
            $ordering = 'sortname DESC';
        }
    
        // Получаем выборку
        return $this->get_records_select($select, $params, $ordering, '*', $limitfrom, $limitnum);
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
}    
?>