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
class dof_workflow_programmsbcs implements dof_workflow
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
        return 'programmsbcs';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('programmsbcs'=>2009052900,
                                        'acl'=>2011041800));
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
        return array(array('plugintype' => 'storage',
                           'plugincode' => 'programmsbcs',
                           'eventcode'  => 'insert'),
                           
                     array('plugintype' => 'workflow',
                           'plugincode' => 'contracts',
                           'eventcode'  => 'changestatus')
                     );
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
        if ( $gentype==='storage' AND $gencode === 'programmsbcs' AND $eventcode === 'insert' )
        {
            // Отлавливаем добавление нового объекта
            // Инициализируем плагин
            return $this->init($intvar);
        }
        if ( $gentype==='workflow' AND $gencode === 'contracts' AND $eventcode === 'changestatus' )
        {// следим за изменением статуса контракта
            if ( $mixedvar->new == 'frozen' )
            {// При изменении статуса контракта на "frozen" (приостановлен) все подписки на программу должны
                // приостанавливаться
                $this->suspend_all_contract_programmsbcs($intvar);
            }
            
            if ( $mixedvar->old == 'frozen' AND $mixedvar->new == 'work' )
            {// если контракт переходит из статуса "frozen" (приостановлен) - то все подписки на программу 
                // должны снова запускаться 
                $this->awake_all_contract_programmsbcs($intvar);
            }
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
        return 'programmsbcs';
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
        return array('application' => $this->dof->get_string('status:application','programmsbcs',NULL,'workflow'),
                     'plan'        => $this->dof->get_string('status:plan','programmsbcs',NULL,'workflow'),
                     'active'      => $this->dof->get_string('status:active','programmsbcs',NULL,'workflow'),
                     'condactive'  => $this->dof->get_string('status:condactive','programmsbcs',NULL,'workflow'),
                     'suspend'     => $this->dof->get_string('status:suspend','programmsbcs',NULL,'workflow'),
                     'onleave'     => $this->dof->get_string('status:onleave','programmsbcs',NULL,'workflow'),
                     'canceled'    => $this->dof->get_string('status:canceled','programmsbcs',NULL,'workflow'),
                     'failed'      => $this->dof->get_string('status:failed','programmsbcs',NULL,'workflow'),
                     'rejected'    => $this->dof->get_string('status:rejected','programmsbcs',NULL,'workflow'),
                     'completed'   => $this->dof->get_string('status:completed','programmsbcs',NULL,'workflow'));
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
                return array('active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'condactive'=>$this->dof->get_string('status:condactive',$this->code(),NULL,'workflow'));
            case 'actual':
                return array('plan'=>$this->dof->get_string('status:plan',$this->code(),NULL,'workflow'),
                             'application'=>$this->dof->get_string('status:application',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'condactive'=>$this->dof->get_string('status:condactive',$this->code(),NULL,'workflow'),
                             'suspend'=>$this->dof->get_string('status:suspend',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'));
            case 'real':
                return array('plan'=>$this->dof->get_string('status:plan',$this->code(),NULL,'workflow'),
                             'application'=>$this->dof->get_string('status:application',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'condactive'=>$this->dof->get_string('status:condactive',$this->code(),NULL,'workflow'),
                             'suspend'=>$this->dof->get_string('status:suspend',$this->code(),NULL,'workflow'),
                             'active'=>$this->dof->get_string('status:active',$this->code(),NULL,'workflow'),
                             'failed'=>$this->dof->get_string('status:failed',$this->code(),NULL,'workflow'),
                             'completed'=>$this->dof->get_string('status:completed',$this->code(),NULL,'workflow'));  
            case 'junk':                
                return array('canceled'=>$this->dof->get_string('status:canceled',$this->code(),NULL,'workflow'),
                             'rejected'=>$this->dof->get_string('status:rejected',$this->code(),NULL,'workflow'));
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
        //получим список всех статусов
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
        if ( ! $obj = $this->dof->storage('programmsbcs')->get($id) )
        {
            // Объект не найден
            return false;
        }
        if ( ! $programm = $this->dof->storage('programms')->get($obj->programmid) )
        {// программа по которой создана подписка не найдена - не можем изменять статус
            return false;
        }
        if ( $programm->status == 'notavailable' )
        {// если программа в статусе "недоступна" - то запрещаем менять статус подписки
            return array();
        }
        
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            case 'application': // переход из статуса "Заявка"
                return array('plan'=>$this->get_name('plan'), 'rejected'=>$this->get_name('rejected'),
                             'canceled'=>$this->get_name('canceled')); 
            break;
            case 'plan': // переход из статуса "Запланированная подписка"
                $statuses = array();
                if ( isset($obj->contractid) AND isset($obj->programmid) 
                     AND ($contract = $this->dof->storage('contracts')->get($obj->contractid)) 
                     AND ($programm = $this->dof->storage('programms')->get($obj->programmid)) 
                     AND ($agroup   = $this->dof->storage('agroups')->get($obj->agroupid) OR empty($obj->agroupid)) )
                {// если у подписки есть контракт и программа
                    if ( isset($contract->status) AND ($contract->status == 'work')  
                         AND isset($programm->status) AND ($programm->status == 'available')
                         AND (( isset($agroup->agenum) AND ($agroup->agenum == $obj->agenum)) OR empty($obj->agroupid)) )
                    {// и они активны, то подписку можно перевести в статус идет
                        $statuses['active'] = $this->get_name('active');
                        $statuses['condactive'] = $this->get_name('condactive');
                    }
                }
                // добавим остальные статусы
                $statuses['suspend'] = $this->get_name('suspend');
                $statuses['rejected'] = $this->get_name('rejected');
                $statuses['canceled'] = $this->get_name('canceled');
                return $statuses;
            break;
            case 'active': // переход из статуса "Идет обучение"
                return array('condactive'=>$this->get_name('condactive'), 'suspend'=>$this->get_name('suspend'), 
                             'onleave'=>$this->get_name('onleave'), 'completed'=>$this->get_name('completed'), 
                             'failed'=>$this->get_name('failed'));
            break;
            case 'condactive': // переход из статуса "Идет обучение условно"
                return array('active'=>$this->get_name('active'), 'suspend'=>$this->get_name('suspend'), 
                             'onleave'=>$this->get_name('onleave'), 'completed'=>$this->get_name('completed'), 
                             'failed'=>$this->get_name('failed'));
            break;
            case 'suspend': // переход из статуса "Приостановлена "
                $statuses = array();
                if ( isset($obj->contractid) AND isset($obj->programmid) 
                     AND ($contract = $this->dof->storage('contracts')->get($obj->contractid)) 
                     AND ($programm = $this->dof->storage('programms')->get($obj->programmid))
                     AND ($agroup   = $this->dof->storage('agroups')->get($obj->agroupid) OR empty($obj->agroupid)) )
                {// если у подписки есть контракт и программа
                    if ( isset($contract->status) AND ($contract->status == 'work')  
                         AND isset($programm->status) AND ($programm->status == 'available')
                         AND (( isset($agroup->agenum) AND ($agroup->agenum == $obj->agenum)) OR empty($obj->agroupid)) )
                    {// и они активны, то подписку можно перевести в статус идет
                        $statuses['active'] = $this->get_name('active');
                        $statuses['condactive'] = $this->get_name('condactive');
                    }
                }
                // добавим остальные статусы
                $statuses['onleave'] = $this->get_name('onleave');
                $statuses['completed'] = $this->get_name('completed');
                $statuses['failed'] = $this->get_name('failed');
                return $statuses;
            break;
            case 'onleave': // переход из статуса "Академический отпуск"
                $statuses = array();
                if ( isset($obj->contractid) AND isset($obj->programmid) 
                     AND ($contract = $this->dof->storage('contracts')->get($obj->contractid)) 
                     AND ($programm = $this->dof->storage('programms')->get($obj->programmid))
                     AND ($agroup   = $this->dof->storage('agroups')->get($obj->agroupid) OR empty($obj->agroupid)) )
                {// если у подписки есть контракт и программа
                    if ( isset($contract->status) AND ($contract->status == 'work')  
                         AND isset($programm->status) AND ($programm->status == 'available')
                         AND (( isset($agroup->agenum) AND ($agroup->agenum == $obj->agenum)) OR empty($obj->agroupid)) )
                    {// и они активны, то подписку можно перевести в статус идет
                        $statuses['active'] = $this->get_name('active');
                    }
                }
                // добавим остальные статусы
                $statuses['completed'] = $this->get_name('completed');
                $statuses['failed'] = $this->get_name('failed');
                return $statuses;
            break;
            case 'rejected': // переход из статуса "Оклоненная"
                return array();
            break;
            case 'canceled': // переход из статуса "Отмененная"
                return array();
            break;
            case 'failed': // переход из статуса "Неуспешно завершенная "
                return array();
            break;
            case 'completed': // переход из статуса "Успешно завершенная"
                return array();
            break;
            default: return false;
        }
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
        if ( ! $list = $this->get_available($id) )
        {
            // Ошибка получения статуса для объекта';
            return false;
        }
        if ( ! isset($list[$status]) )
        {
            // Переход в данный статус из текущего невозможен';
            return false;
        }
        $oldsbc = $this->dof->storage('programmsbcs')->get($id);
        $rez = true;
        // Меняем статус';
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = $status;
        // установим время завершения подписки
        if ( in_array($status, array('onleave','completed','failed','rejected','canceled')) )
        {
            $obj->dateend = time();
        }    
        $rez = $rez AND $this->dof->storage('programmsbcs')->update($obj);
        
        if ( ! $rez )
        {// не получилось изменить статус - никаких дополнительных действий не производим
            return false;
        }
         // если статус изменить удалось - то получим новую подписку
        $newsbc = $this->dof->storage('programmsbcs')->get($id);
        
        switch ( $status )
        {// выполняем необходимые действия, связанные со сменой статуса
            case 'plan':
                break;
            case 'active':
            case 'condactive':
                // если подписка переходит в статус "активный" - то все ее подписки на предметы
                // должны перейти в статус "активные"
                
                // если поток был запланирован - то это должны быть только подписки со статусом "plan"
                // если поток был приостановлен - то это должны быть только подписки со статусом "suspend"
                if ( $oldsbc->status == 'plan' OR $oldsbc->status == 'suspend' )
                {
                    if ( $sbclist = $this->dof->storage('cpassed')->
                                        get_records(array('programmsbcid'=>$id, 'status'=>$oldsbc->status)) )
                    {// если мы получили записи - изменим их статус
                        foreach ( $sbclist as $cpassed )
                        {
                            $csstatus = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid,'status');
                            switch ( $csstatus )
                            {
                                case 'active':
                                    // если ее поток тоже активен, то переведем в статус "активна"
                                    $rez = $rez AND $this->dof->workflow('cpassed')->change($cpassed->id, 'active');
                                break;
                                case 'suspend':
                                    // если ее поток приостановлен 
                                    if ( $cpassed->status == 'plan' )
                                    {// запланированные подписки тоже приостановим
                                        $rez = $rez AND $this->dof->workflow('cpassed')->change($cpassed->id, 'suspend');
                                    }
                                break;
                            }
                        }
                    }
                }
                break;
            case 'suspend':
                // если подписка переходит в статус "приостановлен" - то все его подписки на предметы
                // должны перейти в статус "приостановленные"
                if ( $sbclist = $this->dof->storage('cpassed')->
                                        get_records(array('programmsbcid'=>$id, 'status'=>array('plan','active'))) )
                {// если мы получили записи - изменим их статус
                    foreach ( $sbclist as $cpassed )
                    {
                        // Получение предмето-классов по подписке 
                        $csstatus = $this->dof->storage('cstreams')->get_field($cpassed->cstreamid,'status');
                        switch ( $csstatus )
                        {
                            case 'active':
                                // если ее поток активен
                            case 'suspend':
                                // если ее поток приостановлен 
                                // подписки тоже приостановим
                                $rez = $rez & $this->dof->workflow('cpassed')->change($cpassed->id, 'suspend');
                            break;
                        }
                    }
                }
                break;
            case 'onleave':
            case 'completed':             
            case 'failed':
            case 'rejected':
            case 'canceled':
                // если подписка переходит в статус "активный" - то все его подписки на предметы
                // должны перейти в статус "активные"
                $sbclist = $this->dof->storage('cpassed')->
                    get_records(array('programmsbcid'=>$id, 'status'=>array('plan', 'active', 'suspend')));
                if ( $sbclist AND ! empty($sbclist) )
                {// если мы получили записи - изменим их статус
                    foreach ( $sbclist as $sbc )
                    {// пербираем все запланированные подписки на предметы, и перевозим каждую в статус "активна"
                        $rez = $rez AND $this->dof->storage('cpassed')->set_final_grade($sbc->id);
                    }
                }                
                break;
        }
        if ( ! $rez )
        {// какой-то подписке не удалось изменить статус - вернем подписке исходное состояние
            $this->dof->storage('programmsbcs')->update($oldsbc);
            // сообщим о неудачной операции
            return false;
        }
        // записываем историю изменения статуса в таблицу
        $this->dof->storage('statushistory')->
            change_status($this->get_storage(),intval($id), $status, $oldsbc->status, $opt);
        return $rez;
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
        // Получаем объект из examplest
        if ( ! $obj = $this->dof->storage('programmsbcs')->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Меняем статус
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'application';
        //$obj->statusdate = time();
        return $this->dof->storage('programmsbcs')->update($obj);
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
    
    /** Приостановить все подписки на программу при приостановке контракта
     * @todo объединить две эти функции в одну - syncronize_programmsbcs_with_contracts($contractid, $statusfrom, $statusto)
     * 
     * @return bool
     * @param object $contractid - id контракта в хранилище contracts
     */
    protected function suspend_all_contract_programmsbcs($contractid)
    {
        $result = true;
        if ( ! $programmsbcs = $this->dof->storage($this->code())->
                get_records(array('contractid'=>$contractid, 'status'=>array('active','condactive'))) )
        {// нет подписок на программу для этого контракта - ничего не делаем
            return true;
        }
        foreach ( $programmsbcs as $element )
        {
            $result = ($result AND $this->change($element->id, 'suspend'));
        }
        
        return $result;
    }
    
    /** Возобновить все подписки на программы при активации контракта
     * 
     * @return bool
     * @param object $contractid - id контракта в хранилище contracts
     */
    protected function awake_all_contract_programmsbcs($contractid)
    {
        $result = true;
        if ( ! $programmsbcs = $this->dof->storage($this->code())->
                get_records(array('contractid'=>$contractid, 'status'=>'suspend')) )
        {// нет подписок на программу для этого контракта - ничего не делаем
            return true;
        }
        foreach ( $programmsbcs as $element )
        {
            $result = $result AND $this->change($element->id, 'active');
        }
        
        return $result;
    }
}
?>