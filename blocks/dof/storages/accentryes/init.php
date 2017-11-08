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
 * Справочник Операций по счетам
 * 
 */
class dof_storage_accentryes extends dof_storage implements dof_storage_config_interface
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
        return 'accentryes';
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
                                      'accounts'=> 2014111000,
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
                                      'config'=> 2011080900)
        );
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Cобытий не обрабатываем
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
        return 'block_dof_s_accentryes';
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
        
        $a['view']   = array('roles'=> array('manager','methodist'));
        $a['edit']   = array('roles'=> array('manager'));
        $a['create'] = array('roles'=> array('manager','methodist'));
        $a['delete'] = array('roles'=> array('manager'));

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
     * Генерируем объект операции
     * 
     * @param int $fromid - счет, с которого списываем сумму
     * @param int $toid - счет, на который переводим сумму
     * @param float $amount - сумма операции
     * @param int $date - дата, когда необходимо провести операцию
     * @param array $extentryopts - массив опций внешнего ключа
     * @param int $orderid - id приказа в соответствии с которым выполняется операция
     * @param string $about - описание операции
     * @return object|bool - объект операции по счету или false в случае ошибки
     */
    public function generate_accentry_record($fromid, $toid, $amount, $date, $extentryopts, $orderid = null, $about = '' )
    {
        // Проверяем наличие счетов
        if ( ! $this->dof->storage('accounts')->get_record(array('id' => $fromid)) )
        {// Счет не найден
            return false;
        }
        if ( ! $this->dof->storage('accounts')->get_record(array('id' => $toid)) )
        {// Счет не найден
            return false;
        }
        
        // Создаем объект операции
        $accentryobj = new stdClass();
        $accentryobj->fromid = $fromid;
        $accentryobj->toid = $toid;
        $accentryobj->amount = $amount;
        $accentryobj->orderid = $orderid;
        $accentryobj->createdate = date('U');
        $accentryobj->date = $date;
        
        if ( ! empty($extentryopts) && is_array($extentryopts) )
        {// Если нам передан массив опций
            // Сортируем опции
            krsort($extentryopts);
            // Сериализуем массив
            $accentryobj->extentryopts = serialize($extentryopts);
        } else 
        {// Если массив не передан
            $accentryobj->extentryopts = null;
        }
        $accentryobj->extentryoptshash = $this->get_extentryoptshash($extentryopts);
        $accentryobj->about = $about;
        
        // Возвращаем объект операции
        return $accentryobj;
    }
    
    /**
     * Добавить проводку
     * 
     * Добавить проводку на основе передаваемого объекта
     * В объекте должны быть указаны все необходимые поля, а именно
     * >fromid
     * >toid
     * >amount
     * >orderid
     * >createdate
     * >date
     * >extentryopts
     * >extentryoptshash
     * >about
     * >status
     * 
     * @param object $accentryobj - Объект операции
     * @return object|bool - объект операции, либо false в случае ошибки
     */
    public function add_accentry($accentryobj)
    {
        // Проверяем, есть ли среди реальных проводок транзакция с идентичных хэшем
        if ( ! empty($accentryobj->extentryoptshash) )
        {
            
            // Формируем фрагмент SQL
            $select = 'extentryoptshash = :opts AND
                       amount >= 0';
            
            // Формируем массив плейсхолдеров
            $params = array( 'opts' => $accentryobj->extentryoptshash );
            
            // Получаем все активные статусы
            $activestatuses = $this->dof->workflow('accentryes')->get_meta_list('active');
            
            // Если нет ни одного активного статуса - проверка на идентичность не работает,
            // выводим ошибку
            if ( empty($activestatuses) )
            {
                dof_debugging('Нет ни одного активного статуса!', DEBUG_DEVELOPER);
                return false;
            }

            $select .= ' AND ( ';
            $start = false;
            foreach ( $activestatuses as $status => $name )
            {
                if ( $start )
                {
                    $select .= ' OR '; 
                }       
                // Добавляем статус
                $params['status'.$status] = $status;
                $select .= 'status = :status'.$status.'';
            
                $start = true;
            }
            $select .= ' ) ';

            // Проверяем, есть ли в системе такая операция
            if ( $this->get_records_select($select, $params) )
            {// Операция уже занесена в систему
                return false;
            }
        }
        
        // Добавляем в Справочник
        return $this->insert($accentryobj);
    }
    
    /**
     * Сменить статус проводки
     *
     * Сменяет статус у объекта с переданным id
     * 
     * @param int $accentryid - id объекта операции
     * @param string $status - статус
     * @return bool - false в случае ошибки, true в случае успеха
     */
    public function change_status($accentryid, $status )
    {
        // Создаем объект операции
        $cancelobj = new stdClass();
        $cancelobj->status = $status;
        // Обновляем статус
        return $this->update($cancelobj, $accentryid);
    }
    
    /**
     * Получить историю операций по счету , находящихся в интервале времени
     * 
     * Возвращает массив операций с балансом по счету после каждой операции.
     *  
     * @param int $accountid - id счета
     * @param string $begindate - начальная дата, если null - не учитывается
     * @param string $enddate - конечная дата, если null - не учитывается
     * @return array|bool - массив объектов операций, или false в случае ошибки
     */
    public function get_history($accountid, $begindate=null, $enddate=null) 
    {
        // Проверка входных данных
        if ( ! is_int($accountid) || $accountid < 1 )
        {// Если неверный тип id счета, вернем false
            return false;
        }
        
        // Готовим массив плейсхолдеров
        $params = array();
        
        // Формируем участок кода для фильтрации операций
        $params['fromid'] = $accountid;
        $params['toid'] = $accountid;
        $select = ' ( fromid = :fromid OR toid = :toid ) ';
        
        // Получаем все реальные статусы
        $realstatuses = $this->dof->workflow('accentryes')->get_meta_list('real');
        
        // Если есть реальные статусы
        if ( ! empty($realstatuses) )
        {
            $select .= ' AND ( ';
            $start = false;
            foreach ( $realstatuses as $status => $name )
            {
                if ( $start )
                {
                    $select .= ' OR ';
                }
                // Добавляем статус
                $params['status'.$status] = $status;
                $select .= 'status = :status'.$status.'';
            
                $start = true;
            }
            $select .= ' ) ';
        }
        
        // Если передана верная конечная дата
        if ( is_int($enddate) && $enddate > 0 )
        {
            $params['enddate'] = $enddate;
            $select .= ' AND date <= :enddate ';
        }
        
        // Получаем историю операций по счету с использованием сформированного фрагмента
        $history = $this->get_records_select($select, $params, 'date ASC');
        
        // Начинаем просчет баланса по каджому элементу
        $balance = 0;
        
        // Получаем все актуальные статусы
        $actualstatuses = $this->dof->workflow('accentryes')->get_meta_list('actual');
        
        foreach ( $history as $item )
        {
            // Если элемент имеет актуальный статус - учитываем в подсчете баланса
            if ( array_key_exists($item->status, $actualstatuses) )
            {
                // Просчет и добавление баланса по каждой операции.
                // Если происходило списание
                if ($item->fromid == $accountid)
                {
                    $balance = $balance - $item->amount;
                }
                // Если происходило пополнение
                if ($item->toid == $accountid)
                {
                    $balance = $balance + $item->amount;
                }
                // Если передана начальная дата и операция была проведена раньше этой даты,
                // то после подсчета баланса по результату этой операции затираем ее
                if ( is_int($begindate) && $begindate > 0 && $item->date < $begindate )
                {// Если передан верный $begindate
                unset ($history[$item->id]);
                }
            }
            $item->balance = round( $balance, 2 );
        }
        
        return $history;
    }
    
    /**
     * Получить баланс по счету на дату 
     * 
     * @param int $accountid - id счета
     * @param int $date - Дата, на которую получаем баланс
     * @return float|bool - баланс по счету на дату $date или false в случае ошибки    
     */
    public function get_account_balance($accountid, $date)
    {
        // Получить историю по аккаунту
        $history = $this->get_history($accountid, null, $date);
        
        // Если get_history возвращает ошибку
        if ( $history === false )
        {
            return false;
        }
        
        if ( ! empty($history) )
        {// Если операции проводились
            $balance = array_pop($history)->balance;
        } else 
        {// Если операций не проводилось
            $balance = 0;
        }
        return round( $balance, 2 );
    }
    
    /**
     * Возвращает md5-хэш от массива опций
     * 
     * @param array $extentryopts - массив опций
     * @return string|null - хэш 
     */
    private function get_extentryoptshash($extentryopts = null)
    {
        if ( empty($extentryopts) )
        {
            return null;
        }
        // Конвертируем в строку
        $result = json_encode($extentryopts);
        // Возвращаем md5
        return md5($result);
    }
}  
?>