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


/** 
 * Плагин рабочих процессов событий
 * 
 */
class dof_workflow_schevents implements dof_workflow
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
        return 2016042600;
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
        return 'schevents';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('schevents' => 2009060800,
                                      'acl'       => 2011082200));
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
        return array(array('plugintype'=>'storage','plugincode'=>'schevents','eventcode'=>'insert'));
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
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);  
        switch ( $do )
        {
                
        } 
        if ( $this->dof->is_access('manage') )
        {// манагеру можно, кроме отмечать статус отмененным
            return true;
        
        }
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
        if ( $gentype==='storage' AND $gencode === 'schevents' AND $eventcode === 'insert' )
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
    
    /** Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * 
     * @return string
     * @access public
     */
    public function get_storage()
    {
        return 'schevents';
    }
    
    /** 
     * Возвращает массив всех состояний, в которых может находиться событие
     * 
     * @return array - Массив статусов
     * @access public
     */
    public function get_list()
    {
        return array(
                'plan'      => $this->dof->get_string('status:plan','schevents',NULL,'workflow'),
                'completed' => $this->dof->get_string('status:completed','schevents',NULL,'workflow'),
                'replaced'  => $this->dof->get_string('status:replaced','schevents',NULL,'workflow'),
                'canceled'  => $this->dof->get_string('status:canceled','schevents',NULL,'workflow'),
                'postponed' => $this->dof->get_string('status:postponed','schevents',NULL,'workflow'),
                'implied'   => $this->dof->get_string('status:implied','schevents',NULL,'workflow'),
                'deleted'   => $this->dof->get_string('status:deleted','schevents',NULL,'workflow')
        );
    }
    
    /**
     * Возвращает массив метастатусов
     *
     * @param string $type - тип списка метастатусов
     *
     * @return array - Массив статусов
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':
                return [
                ];
            case 'actual':
                return [
                    'plan'      => $this->dof->get_string('status:plan','schevents',NULL,'workflow')
                ];
            case 'real':
                return [
                    'plan'      => $this->dof->get_string('status:plan','schevents',NULL,'workflow'),
                    'completed' => $this->dof->get_string('status:completed','schevents',NULL,'workflow'),
                    'replaced'  => $this->dof->get_string('status:replaced','schevents',NULL,'workflow'),
                    'postponed' => $this->dof->get_string('status:postponed','schevents',NULL,'workflow'),
                    'implied'   => $this->dof->get_string('status:implied','schevents',NULL,'workflow')
                ];
            case 'junk':
                return [
                    'canceled'  => $this->dof->get_string('status:canceled','schevents',NULL,'workflow'),
                    'deleted'   => $this->dof->get_string('status:deleted','schevents',NULL,'workflow')
                ];
            default:
                dof_debugging('workflow/'.$this->code().' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }
    
    /** 
     * Возвращает локализованное имя статуса
     * 
     * @param string status - Состояние события
     * 
     * @return string - Имя с учетом языка
     */
    public function get_name($status)
    {
        $list = $this->get_list();
        if ( isset($list[$status]) )
        {
            return $list[$status];
        }
        return '';
    }
    
    /** 
     * Возвращает массив состояний, в которые может переходить событие 
     * из текущего состояния  
     * 
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
        // Получаем объект из ages
        if ( ! $obj = $this->dof->storage('schevents')->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Определяем возможные состояния в зависимости от текущего статуса
        $statuses = array();
        switch ( $obj->status )
        {
            case 'plan':      // переход из статуса "запланирован"
                $statuses['completed'] = $this->get_name('completed');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['replaced'] = $this->get_name('replaced');
                $statuses['postponed'] = $this->get_name('postponed');
                $statuses['implied'] = $this->get_name('implied');
                $statuses['plan'] = $this->get_name('plan');
                $statuses['deleted'] = $this->get_name('deleted');
            break;
            case 'postponed': // переход из статуса "отложено"
                $statuses['replaced'] = $this->get_name('replaced');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['postponed'] = $this->get_name('postponed');
                $statuses['deleted'] = $this->get_name('deleted');
            break;
            case 'implied': // переход из статуса "Подразумевается"
                $statuses['replaced'] = $this->get_name('replaced');
                $statuses['canceled'] = $this->get_name('canceled');
                $statuses['implied'] = $this->get_name('implied');
                $statuses['deleted'] = $this->get_name('deleted');
            break;
            case 'canceled':  // переход из статуса "отменен"
                $statuses['canceled'] = $this->get_name('canceled');
            break;
            case 'completed': // переход из статуса "завершено"
                $statuses['completed'] = $this->get_name('completed');
            break;
            case 'replaced':  // переход из статуса "заменено"
                $statuses['deleted'] = $this->get_name('deleted');
            break;
            default:
                $statuses['plan'] = $this->get_name('plan');
                break;
        }
        
        return $statuses;
        
    }
    /** 
     * Переводит экземпляр объекта с указанным id в переданное состояние
     * @param int id - id экземпляра объекта
     * @param string status - название состояния
     * @return boolean true - удалось перевести в указанное состояние, 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function change($id, $status,$opt=null)
    {
        if ( ! $event = $this->dof->storage('schevents')->get($id) )
        {// Период не найден
            return false;
        }
        
        $astatus = $this->dof->storage('appointments')->get_field($event->appointmentid,'status');
        $personid = $this->dof->storage('persons')->get_by_moodleid()->id;
        $aperson = $this->dof->storage('appointments')->get_person_by_appointment($event->appointmentid);
        if ( !empty($aperson) AND $aperson->id == $personid AND 
                      ($astatus == 'patient' OR $astatus == 'vacation') )
        {// персона на больничном или в отпуске не может менять статусы
            return false;
        }
        if ( $this->dof->is_access('datamanage') )
        {// датаманагеру можно переводить в любой статус
            // под личную ответственность датаманагера
            $list = $this->get_list();
        }else
        {// только список доступных
            $list = $this->get_available($id);
        }
        if ( ! $list )
        {// Ошибка получения статуса для объекта';
            return false;
        }
        if ( ! isset($list[$status]) )
        {// Переход в данный статус из текущего невозможен';
            return false;
        }    
        switch ($status)
        {
            // отмечаем проведение урока
            case 'completed':
                if ( ! $this->limit_time($event->date) OR ! isset($event->teacherid) OR ! $event->teacherid )
                {// если есть ограничения или нет учителя - провести занятие нельзя
                    return false;
                }
            break;        
        }
        $this->dof->storage('statushistory')->change_status('schevents',intval($id), $status,$event->status,$opt);
        // Меняем статус';
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = $status;
        $this->dof->storage('schevents')->update($obj);
        //перерасчитываем коэффициенты
        $obj->salfactor      = $this->calculation_salfactor($id);
        $obj->salfactorparts = serialize($this->calculation_salfactor($id, true, true));
        $obj->rhours         = $this->calculation_salfactor($id,true);
        //$obj->statusdate = time();
        return $this->dof->storage('schevents')->update($obj);
    }
    /** 
     * Инициализируем состояние объекта
     * @param int id - id экземпляра
     * @return boolean true - удалось инициализировать состояние объекта 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function init($id)
    {
        // Получаем объект из справочника
        if (!$obj = $this->dof->storage('schevents')->get($id))
        {// Объект не найден
            return false;
        }
        // Меняем статус
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'plan';
        $this->dof->storage('schevents')->update($obj);
        //перерасчитываем коэффициенты
        $obj->salfactor      = $this->calculation_salfactor($id);
        $obj->salfactorparts = serialize($this->calculation_salfactor($id, true, true));
        $obj->rhours         = $this->calculation_salfactor($id,true);
        return $this->dof->storage('schevents')->update($obj);
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
        $result->objectid     = $objectid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
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
        
        $a['changestatus'] = array('roles'=>array('manager','teacher','methodist')); 
        $a['changestatus:to:canceled'] = array('roles'=>array('manager')); 
        
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
    /**
     * Переводит объект с любым статусом в отмененный
     * @param int $id - id объекта, который надо отменить
     * @return bool - true, если статус сменили, 
     * false - в остальных случаях
     */
    public function cancel_any($id)
    {

        if ( ! $obj = $this->dof->storage('schevents')->get($id) )
        {// Объект не найден
            return false;
        }
        $oldstatus = $obj->status;
        //меняем статус на отмененный
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'canceled';
        $this->dof->storage('schevents')->update($obj);
        // Добавление логирования 
        $this->dof->storage('statushistory')->change_status('schevents', intval($id), $obj->status, $oldstatus, NULL, 'cancel_any method');
        //перерасчитываем коэффициенты
        $obj->salfactor      = $this->calculation_salfactor($id);
        $obj->salfactorparts = serialize($this->calculation_salfactor($id, true, true));
        $obj->rhours         = $this->calculation_salfactor($id,true);
        return $this->dof->storage('schevents')->update($obj);
    } 
    
    /** 
     * Возможность установить отметку о проведении урока
     * 
     * @param int $date - timestamp начала урока
     * @return bool true - ограничений нет или false 
     */
    public function limit_time($date)
    {
        if ( $this->is_access('manage') )
        {// Возможность поставить отметку независимо от времени
            return true;
        }   
        // Проверим по времени
        if ( $date >= time() )
        {// Начало урока еще не произошло
            return false;
        }
        // Блокировка по 26 день месяца, если настройка включена в системе
        if ( $this->dof->storage('config')->get_config_value('time_limit', 
                'storage', 'schevents', optional_param('departmentid', 0, PARAM_INT)) )
        {// Активирована настройка - проверим, укладывается ли отмена в отведенный лимит
            // Значения по-умолчанию
            $edate = dof_gmgetdate($date);
            $fixday = 26;
            if ( $day = $this->dof->storage('schdays')->get_records(array('status'=>'fixed'),'date desc','*',0,1) )
            {// Если есть фиксированный день, используем его
                $day = current($day);
                $edate = dof_gmgetdate($day->date);
                // Получили день для блокировки
                $fixday = $edate['mday']+1;
            }
            // Подводим timestamp к началу дня
            $fixdate = mktime(0,0,0,$edate['mon'],$fixday,$edate['year']);
            if ( time() > $fixdate AND $date < $fixdate )
            {// Текущая дата позже зафиксированого дня, а урок был начат до фиксированого дня
                return false;
            }
        }
        // Проверки пройдены
        return true;
    }

    /** 
     * Вычислить расчетный коэффициент для события
     * 
     * @param int|object $schevents - ID или Объект события
     * @param bool $ahours - Произвести рассчет для одного часа события
     * @param bool $return_calcdata - Вернуть только данные для рассчета
     * 
     * @return int|object|bool
     */
    public function calculation_salfactor($schevents, $ahours = false, $return_calcdata = false)
    {
        $options = [];
        if ( ! $ahours )
        {// Указано переопределение часов
            $options['ahours'] = 1;
        }
        
        // Получение данных для рассчета
        $calcdata = $this->dof->storage('schevents')->prepare_calculation_data($schevents, $options);
        if ( $return_calcdata )
        {// Вернуть данные для рассчета
            return $calcdata;
        }
        // Рассчет по формуле
        return $this->dof->modlib('calcformula')->calc_formula($calcdata->formula, $calcdata->vars);      
    }
    
    /**
     * Перевод события в ранее установленное состояние
     *
     * @param int $id - ID события, требующей преевода
     * @param string $metastatus - Метастатус, в который требуется вернуть элемент
     * @param array $options - Дополнительные данные обработки
     *          ['return'] => 'newstatus' - Вернуть вместо true новый статус, в который был переведен элемент
     *                        'oldstatus' - Вернуть вместо true старый статус, из которого был переведен элемент
     *          
     * @return bool - Результат работы
     */
    public function restore_status($id, $metastatus = NULL, $options = [] )
    {
        // Проверка наличия
        $element = $this->dof->storage($this->get_storage())->get($id);
        if ( ! $element )
        {// Контрольная точка не найдена
            return FALSE;
        }
    
        // Нормализация
        if ( ! isset($options['return']) )
        {
            $options['return'] = NULL;
        }
        
        // Получение маршрута смены статусов
        $trace = $this->dof->storage('statushistory')->get_records([
            'plugintype' => 'storage',
            'plugincode' => $this->get_storage(),
            'objectid' => $id], 'statusdate DESC'
        );
    
        // Получение метастатуса для возврата в предыдущее состояние
        $meta = $this->get_meta_list($metastatus);
        if ( empty($meta) )
        {// Мета статус не определен
            return FALSE;
        }
        $retstatus = 'plan';
    
        if ( ! empty($trace) )
        {// Маршрут найден
            do
            {
                // Получение последней смены статуса
                $track = array_pop($trace);
                // Определение статуса перехода
                if ( ! empty($track->status) && isset($meta[$track->status]) )
                {// Статус принадлежит указанному метастатусу
                    $retstatus = $track->status;
                    // Выход из цикла поиска статуса
                    break;
                }
                if ( ! empty($track->prevstatus) && isset($meta[$track->prevstatus]) )
                {// Статус принадлежит указанному метастатусу
                    $retstatus = $track->prevstatus;
                    // Выход из цикла поиска статуса
                    break;
                }
                // Текущий трек не позволяет получить предыдущий статус КТ, переход к следующему треку
            } while ( count($trace) > 0 );
        }
    
        // Смена статуса
        $object = new stdClass();
        $object->id = intval($id);
        $object->status = $retstatus;
        if ( $update = $this->dof->storage($this->get_storage())->update($object) )
        {// Успешная смена статуса
            // Добавление в лог смены статусов
            $this->dof->storage('statushistory')->change_status(
                $this->get_storage(),
                $object->id,
                $object->status,
                $element->status,
                NULL
            );
            switch ( $options['return'] )
            {
                case 'newstatus' :
                    return $object->status;
                case 'oldstatus' :
                    return $element->status;
                default : 
                    return TRUE;
            }
        } else
        {// Смена статуса не удалась
            return FALSE;
        }
    }
}
?>