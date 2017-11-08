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
 * Класс стандартных функций интерфейса
 * 
 */
class dof_workflow_ages implements dof_workflow
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
        return 2011082200;
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
        return 'ages';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('ages'=>2009050600,
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
        return array(array('plugintype'=>'storage','plugincode'=>'ages','eventcode'=>'insert'));
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
        if ( $gentype==='storage' AND $gencode === 'ages' AND $eventcode === 'insert' )
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
        return 'ages';
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
        return array('plan'=>$this->dof->get_string('status:plan','ages',NULL,'workflow'),
                     'createstreams'=>$this->dof->get_string('status:createstreams','ages',NULL,'workflow'),
                     'createsbc'=>$this->dof->get_string('status:createsbc','ages',NULL,'workflow'),
                     'createschedule'=>$this->dof->get_string('status:createschedule','ages',NULL,'workflow'),
                     'active'=>$this->dof->get_string('status:active','ages',NULL,'workflow'),
                     'completed'=>$this->dof->get_string('status:completed','ages',NULL,'workflow'),
                     'canceled'=>$this->dof->get_string('status:canceled','ages',NULL,'workflow'));
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
                             'createstreams'=>$this->dof->get_string('status:createstreams',$this->code(),NULL,'workflow'),
                             'createsbc'=>$this->dof->get_string('status:createsbc',$this->code(),NULL,'workflow'),
                             'createschedule'=>$this->dof->get_string('status:createschedule',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'));
            case 'real':
                return array('plan'=>$this->dof->get_string('status:plan',$this->code(),NULL,'workflow'),
                             'createstreams'=>$this->dof->get_string('status:createstreams',$this->code(),NULL,'workflow'),
                             'createsbc'=>$this->dof->get_string('status:createsbc',$this->code(),NULL,'workflow'),
                             'createschedule'=>$this->dof->get_string('status:createschedule',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'completed'=>$this->dof->get_string('status:completed',$this->code(),NULL,'workflow'));  
            case 'junk':                
                return array('canceled'=>$this->dof->get_string('status:canceled',$this->code(),NULL,'workflow'));
            default:
                dof_debugging('workflow/'.$this->code().' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }
    
    /** 
     * Возвращает имя статуса
     * @param string status - название состояния
     * @return string
     * @access public
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
     * Возвращает массив состояний,
     * в которые может переходить объект 
     * из текущего состояния  
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
        // Получаем объект из ages
        if ( ! $obj = $this->dof->storage('ages')->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            case 'plan':       // переход из статуса "запланирован"
                $statuses = array('createstreams'=>$this->get_name('createstreams'), 'canceled'=>$this->get_name('canceled'));
            break;
            case 'createstreams':     // переход из статуса "сформированы потоки"
                $statuses = array('createsbc'=>$this->get_name('createsbc'), 'canceled'=>$this->get_name('canceled'));
            break;
            case 'createsbc':    // переход из статуса "сформированы подписки"
                $statuses = array('createschedule'=>$this->get_name('createschedule'), 'canceled'=>$this->get_name('canceled'));
            break;
            case 'createschedule':   // переход из статуса "сформировано рассписание"
                $statuses = array('active'=>$this->get_name('active'), 'canceled'=>$this->get_name('canceled'));
            break;
            case 'active':   // переход из статуса "идет"
                $statuses = array('completed'=>$this->get_name('completed'), 'canceled'=>$this->get_name('canceled'));
            break;
            case 'completed':  // переход из статуса "завершен"
                $statuses = array();
            break;
            case 'canceled':  // переход из статуса "отменен"
                $statuses = array();
            break;
            default: $statuses = array('plan'=>$this->get_name('plan'));
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
        // снимем временное ограничение чтобы период перевел все в нужные статусы
        dof_hugeprocess();
        if ( ! $age = $this->dof->storage('ages')->get($id) )
        {// Период не найден
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
        $rez = true;
        switch ( $status )
        {
            case 'active':   // переход в статус "идет"
                // сначала меняем статус периоду
                $obj = new stdClass();
                $obj->id = intval($id);
                $obj->status = $status;
                if ( $this->dof->storage('ages')->update($obj) )
                {// если статус сменился
                    if ( $cstreams = $this->dof->storage('cstreams')->get_records(array('ageid'=>$id,'status'=>'plan')) )
                    {// если у периода были запланированные потоки
                        foreach ( $cstreams as $cstream)
                        {// запустим процесс для каждого
                            $rez = $rez & $this->dof->workflow('cstreams')->change($cstream->id, 'active');
                        }
                    }
                }else
                {// если нет, то плохо
                    return false;
                }
                if ( ! $rez )
                {// если что-то пошло не так, откатим изменения назад
                    $obj = new stdClass();
                    $obj->id = intval($id);
                    $obj->status = $age->status;
                    $this->dof->storage('ages')->update($obj);
                }
                return $rez;
            break;
            case 'completed':  // переход в статус "завершен"
                if ( $cstreams1 = $this->dof->storage('cstreams')->get_records(array('ageid'=>$id,'status'=>'plan')) )
                {// если у периода были запланированные потоки
                    foreach ( $cstreams1 as $cstream1)
                    {// отменим их
                        $rez = $rez & $this->dof->workflow('cstreams')->change($cstream1->id, 'canceled');
                    }
                }
                if ( $cstreams2 = $this->dof->storage('cstreams')->
                                   get_records(array('ageid'=>$id,'status'=>array('active','suspend'))) )
                {// и если есть активные и приостановленные потоки
                    foreach ( $cstreams2 as $cstream2)
                    {// завершим их
                       $rez = $rez & $this->dof->workflow('cstreams')->change($cstream2->id, 'completed');
                    }
                }
            break;
            case 'canceled':  // переход в статус "отменен"
                if ( $cstreams = $this->dof->storage('cstreams')->
                                   get_records(array('ageid'=>$id,'status'=>array('plan','active','suspend'))) )
                {// если у периода были запланированные, активные и приостановленные потоки
                    foreach ( $cstreams as $cstream)
                    {// отменим все
                        $rez = $rez & $this->dof->workflow('cstreams')->change($cstream->id, 'canceled');
                    }
                }
            break;
        }
        
        if ( ! $rez )
        {// если что-то не получилось, статус периода не меняем
            return false;
        }
        $this->dof->storage('statushistory')->change_status($this->get_storage(),intval($id), $status,$age->status,$opt);
        // Меняем статус';
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = $status;
        //$obj->statusdate = time();
        return $this->dof->storage('ages')->update($obj);
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
        if (!$obj = $this->dof->storage($this->get_storage())->get($id))
        {// Объект не найден
            return false;
        }
        // Меняем статус
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'plan';
        return $this->dof->storage($this->get_storage())->update($obj);
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
}
?>