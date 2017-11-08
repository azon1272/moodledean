<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                  
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


/**
 * Класс стандартных функций интерфейса
 *
 */
class dof_workflow_cpassed implements dof_workflow
{
    /**
     * Хранит методы ядра деканата
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /**
    * Метод, реализующий инсталяцию плагина в систему
    * Создает или модифицирует существующие таблицы в БД
    * и заполняет их начальными значениями
    * @return boolean
    * Может надо возвращать массив с названиями таблиц и результатами их создания?
    * чтобы потом можно было распечатать сообщения о результатах обновления
    * @access public
    */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /**
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /**
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016061700;
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
        return 'guppy_a';
    }

    /**
     * Возвращает тип плагина
     * @return string
     * @access public
     */
    public function type()
    {
        return 'workflow';
    }
    /**
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'cpassed';
    }
    /**
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('cpassed'=>2009101900,
                                          'acl'=>2011040504) );
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
    /**
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype'=>'storage','plugincode'=>'cpassed','eventcode'=>'insert'));
    }
    /**
     * Требуется ли запуск cron в плагине
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
        if ( $gentype==='storage' AND $gencode === 'cpassed' AND $eventcode === 'insert' )
        {
            // Отлавливаем добавление нового объекта
            // Инициализируем плагин
            return $this->init($intvar);
        }
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
    // **********************************************
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
    /**
     * Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * @return string
     * @access public
     */
    public function get_storage()
    {
        return 'cpassed';
    }
    /**
     * Возвращает массив всех состояний,
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * @return array
     * @access public
     */
    public function get_list()
    {
        return array('plan'         => $this->dof->get_string('status:plan','cpassed',NULL,'workflow'),
                     'active'       => $this->dof->get_string('status:active','cpassed',NULL,'workflow'),
                     'suspend'      => $this->dof->get_string('status:suspend','cpassed',NULL,'workflow'),
                     'canceled'     => $this->dof->get_string('status:canceled','cpassed',NULL,'workflow'),
                     'completed'    => $this->dof->get_string('status:completed','cpassed',NULL,'workflow'),
                     'reoffset'     => $this->dof->get_string('status:reoffset','cpassed',NULL,'workflow'),
                     'failed'       => $this->dof->get_string('status:failed','cpassed',NULL,'workflow'),
                     'academicdebt' => $this->dof->get_string('status:academicdebt','cpassed',NULL,'workflow'));
    }
    
    /** Возвращает массив метастатусов
     * @param string $type - тип списка метастатусов
     *               'active' - активный 
     *               'actual' - актуальный
     *               'real' - реальный
     *               'junk' - мусорный
     * @return array
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':   
                return array('active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'));
            case 'actual':
                return array('plan'=>$this->dof->get_string('status:plan',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'suspend'=>$this->dof->get_string('status:suspend',$this->code(),NULL,'workflow'));
            case 'real':
                return array('plan'=>$this->dof->get_string('status:plan',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'suspend'=>$this->dof->get_string('status:suspend',$this->code(),NULL,'workflow'),
                             'completed'=>$this->dof->get_string('status:completed',$this->code(),NULL,'workflow'),
                             'reoffset'=>$this->dof->get_string('status:reoffset',$this->code(),NULL,'workflow'),
                             'failed'=>$this->dof->get_string('status:failed',$this->code(),NULL,'workflow'),
                             'academicdebt'=>$this->dof->get_string('status:academicdebt',$this->code(),NULL,'workflow'));
            case 'junk':                
                return array('canceled'=>$this->dof->get_string('status:canceled',$this->code(),NULL,'workflow'));
            default:
                dof_debugging('workflow/'.$this->code().' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }

    /**
     * Возвращает имя статуса
     * @param string status - код состояния
     * @return string название статуса или пустую строку
     * @access public
     */
    public function get_name($status)
    {
        //получим список всех статусо
        $list = $this->get_list();
        if (array_key_exists($status, $list) )
        {//такого кода ест в массиве
            //вернем название статуса
            return $list[$status];
        }
        //такого кода нет в массиве
        return '';
    }
    /**
     * Возвращает массив состояний,
     * в которые может переходить объект 
     * из текущего состояния  
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
        // Получаем объект из cstreams
        if ( ! $obj = $this->dof->storage('cpassed')->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            case 'plan':       // переход из статуса "запланирован"
                $statuses = array();
                if ( isset($obj->cstreamid) AND isset($obj->programmsbcid)
                AND ($cstream = $this->dof->storage('cstreams')->get($obj->cstreamid))
                AND ($sbc = $this->dof->storage('programmsbcs')->get($obj->programmsbcid)) )
                {// если у подписки есть поток и подписка на программу
                    if ( isset($cstream->status) AND ($cstream->status == 'active')
                    AND isset($sbc->status) AND (($sbc->status == 'active') OR ($sbc->status == 'condactive')) )
                    {// и они активены, то подписку можно перевести в статус идет
                        $statuses['active']    = $this->get_name('active');
                    }
                    if ( isset($cstream->status) AND ($cstream->status == 'completed') )
                    {// если поток уже завершен
                        $statuses['active']    = $this->get_name('active');
                        $statuses['completed'] = $this->get_name('completed');
                        $statuses['failed'] = $this->get_name('failed');
                    }
                    
                }
                // добавим остальные статусы
                $statuses['reoffset'] = $this->get_name('reoffset');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['suspend']  = $this->get_name('suspend');
                return $statuses;
                break;
            case 'active':     // переход из статуса "Идет обучение"
                return array('completed'=>$this->get_name('completed'), 'failed'=>$this->get_name('failed'),
                             'suspend'=>$this->get_name('suspend'), 'canceled'=>$this->get_name('canceled'));
                break;
            case 'suspend':    // переход из статуса "Приостановлен"
                $statuses = array();
                if ( isset($obj->cstreamid) AND isset($obj->programmsbcid)
                AND ($cstream = $this->dof->storage('cstreams')->get($obj->cstreamid))
                AND ($sbc = $this->dof->storage('programmsbcs')->get($obj->programmsbcid)) )
                {// если у подписки есть поток и подписка на программу
                    if ( isset($cstream->status) AND ($cstream->status == 'active')
                    AND isset($sbc->status) AND (($sbc->status == 'active') OR ($sbc->status == 'condactive')) )
                    {// и они активены, то подписку можно перевести в статус идет
                        $statuses['active'] = $this->get_name('active');
                    }
                }
                $statuses['completed'] = $this->get_name('completed');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['failed'] = $this->get_name('failed');
                return $statuses;
                break;
            case 'canceled':   // переход из статуса "Отменен"
                return array();
                break;
            case 'reoffset':   // переход из статуса "перезачет"
                return array('failed'=>$this->get_name('failed'));
                break;
            case 'completed':  // переход из статуса "завершен"
                return array('failed'=>$this->get_name('failed'));
                break;
            case 'failed':  // переход из статуса "неудачно завершен"
                return array();
                break;
            case 'academicdebt':  // переход из статуса "неудачно завершен"
                return array();
                break;
            default: return false;
        }
        return false;
    }
    
    /**
     * Переводит экземпляр объекта с указанным id в переданное состояние
     * 
     * @param int $id - id экземпляра объекта
     * @param string $status - название состояния
     * @return boolean true - удалось перевести в указанное состояние, 
     *                 false - не удалось перевести в указанное состояние                
     * @access public
     */
    public function change($id, $status, $opt = null)
    {
        // Проверяем входные данные
        $id = intval($id);
        
        if ( ! $cpass = $this->dof->storage('cpassed')->get($id) )
        {// Запись не найдена
            return false;
        }
        if ( ! $list = $this->get_available($id) )
        {// Ошибка получения статуса для объекта';
            return false;
        }
        if ( ! isset($list[$status]) )
        {// Переход в данный статус из текущего невозможен';
            return false;
        }
        // Добавляем запись об изменении статуса в историю
        $this->dof->storage('statushistory')->change_status($this->get_storage(),intval($id), $status, $cpass->status, $opt);
        
        // Формируем объект нового cpassed'а
        $newcpass = new stdClass();
        
        // Выполняем действия в зависимости от статуса
        if ( $status == 'active' )
        {
            if( $cpass->status == 'plan' )
            {// Если подписка активируется первый раз( Запланирована->Активна )
                $newcpass->begindate = time();
            }
            if ( $cstream = $this->dof->storage('cstreams')->get($cpass->cstreamid) )
            {// Находим учебный поток на который ссылается подписка и продлеваем её до его окончания
                $newcpass->enddate = $cstream->enddate;
            }
            // Кидаем событие на переход записи в активное состояние
            $this->dof->send_event('workflow', 'cpassed', 'cpassed_active', $id);
        } elseif ( $cpass->status == 'active' )
        {// Если подписка на предмет переходит из активного статуса
            // Время завершения
            $newcpass->enddate = time();
            // Кидаем событие на переход записи из активного состояния
            $this->dof->send_event('workflow', 'cpassed', 'cpassed_not_active', $id);
        }
        // Обновляем подписку
        $newcpass->id = $id;
        $newcpass->status = $status;
        return $this->dof->storage('cpassed')->update($newcpass);
    }
    
    /**
     * Инициализация состояния объекта
     * 
     * @param int id - id экземпляра
     * 
     * @return boolean true - удалось инициализировать состояние объекта 
     *                 false - не удалось перевести в указанное состояние
     */
    public function init($id)
    {
        // Получение объекта
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($id) )
        {
            // Объект не найден
            return false;
        }
        
        // Установка статуса по-умолчанию
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'plan';
        
        if ( $this->dof->storage('cpassed')->update($obj) )
        {// Инициализация прошла успешно
            // Изменение статуса на основе данных об подписке и предмето-классе
            return $this->postinit($id);
        }
        // Инициализация завершилась не успешно
        return false;
    }
    
    /**
     * Пост-инициализация состояния объекта
     * 
     * Коррекция статуса на основе статусов подписки на программу и предмето-класса
     * 
     * @param int id - id экземпляра
     * 
     * @return boolean true - удалось инициализировать состояние объекта 
     *                 false - не удалось перевести в указанное состояние
     */
    public function postinit($id)
    {
        // Получение объекта
        if ( ! $cpassed = $this->dof->storage('cpassed')->get($id) )
        {
            // Объект не найден
            return false;
        }
        
        // Новый статус
        $status = null;
        
        // Данные для обновления
        $update = new stdClass();
        $update->id = $id;
        
        // Определение статусов предмето-класса и подписки на программу
        $cstreamstatus = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid, 'status');
        $sbcstatus = $this->dof->storage('programmsbcs')->get_field($cpassed->programmsbcid, 'status');
        
        // Установка нового статуса в зависимости от статусов подписки и предмето-класса
        switch ( $cstreamstatus )
        {
            case 'plan':
                switch ( $sbcstatus )
                {
                    case 'application':
                    case 'plan':
                    case 'active':
                    case 'condactive':
                    case 'suspend':
                        $status = 'plan';
                        break;
                    default: $status = 'canceled';
                }
                break;
            case 'active':
                switch ( $sbcstatus )
                {
                    case 'application':
                    case 'plan':
                        $status = 'plan';
                        break;
                    case 'active':
                    case 'condactive':
                        $status = 'active';
                        // подписываем студента на курс
                        $this->dof->send_event('workflow', 'cpassed', 'cpassed_active', $cpassed->id);
                        $update->begindate = time();
                        // продлеваем подписку до окончания потока
                        $update->enddate = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid,'enddate');
                        break;
                    case 'suspend':
                        $update->begindate = time();
                        $update->enddate = time();
                        $status = 'suspend';
                        break;
                    default: $status = 'canceled';
                }
                break;
            case 'suspend':
                switch ( $sbcstatus )
                {
                    case 'application':
                    case 'plan':
                        $status = 'plan';
                        break;
                    case 'active':
                    case 'condactive':
                    case 'suspend':
                        $update->begindate = time();
                        $update->enddate = time();
                        $status = 'suspend';
                        break;
                    default: $status = 'canceled';
                }
                break;
            case 'completed':
                // создаем в плане, иначе происходит накладка с пересдачей
                $status = 'plan';
                break;
            case 'canceled':
            default:
                if ( $cpassed->status == 'academicdebt' )
                { // Это академическая разница
                    $status = 'academicdebt';
                } else if ( empty($cpassed->cstreamid) AND empty($cpassed->ageid) )
                { // Это перезачёт из "Ведомости перезачёта оценок"
                    $status = 'reoffset';
                } else
                {
                    $status = 'canceled';
                }
        }
        
        // @TODO - Сменить update на метод change, проверить, что при этом маршруты не будут блокировать смену статуса
        $update->status = $status;
        if ( $this->dof->storage('cpassed')->update($update) )
        {// Обновление прошло успешно
            // Добавление записи об изменении статуса в историю
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                intval($id), 
                $status, 
                $cpassed->status, 
                null
            );
            
            return true;
        }
        return false;
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
        
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['changestatus'] = array('roles'=>array('manager'));
        
        return $a;
    }

    // **********************************************
    // Собственные методы
    // **********************************************
    /**
    * Конструктор
    * @param dof_control $dof - это $DOF
    * объект с методами ядра деканата
    */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }

    /** Получить список статусов, которые могут попадать в итоговую ведомость 
     *
     * @param bool $showjunk - отображать мусорные статусы
     * @return array - ассоциативный массив - список статусов, ключ - код статуса (строка),
     *                                                         значение - описание (строка)
     */
    public function get_register_statuses($showjunk = false)
    {
        $statuses = array('active'    => $this->dof->get_string('status:active','cpassed',NULL,'workflow'),
                          'suspend'   => $this->dof->get_string('status:active','cpassed',NULL,'workflow'),
                          'completed' => $this->dof->get_string('status:completed','cpassed',NULL,'workflow'),
                          'reoffset'  => $this->dof->get_string('status:reoffset','cpassed',NULL,'workflow'),
                          'failed'    => $this->dof->get_string('status:failed','cpassed',NULL,'workflow'));
        if ( !empty($showjunk) )
        {// Отображать мусорные статусы
            $statuses += $this->get_meta_list('junk');
        }
        return $statuses;
    }
}
?>