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
// Copyright (C) 2008-2999  Ilia Smirnov (Илья Смирнов)                   // 
// Copyright (C) 2008-2999  Mariya Rojayskaya (Мария Рожайская)           // 
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


/** Класс функций биллинга
 * 
 */
class dof_modlib_billing implements dof_plugin_modlib
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
       // Получаем имя главного счета
       $name =  $this->dof->get_string('main_account', 'billing', null, 'modlib');
       // Добавляем счет задолженности слушателей
       if ( ! $this->dof->storage('accounts')->add_account(
               'modlib',     // Тип плагина - владельца счета
               'billing',    // Плагин - владелец счета
               0,            // ID договора - владельца счета
               'contract',   // Принадлежность счета
               'asset',      // Тип счета (активный, пассивный)
               $name,        // Имя счета
               '0000'        // Параметры счета для хэша
               )
          )
       {
           // Сообщаем об ошибке 
           dof_debugging('Ошибка при добавлении главного счета.', DEBUG_ALL);
           return false;
       }
       
       // Получаем реальные статусы
       $realstatuses = $this->dof->workflow('contracts')->get_meta_list('real');
       
       // Если есть реальные статусы - добавляем в sql - фрагмент
       if ( ! empty($realstatuses) )
       {
           $start = false;
           $sql = '';
           $params = array();
           
           // Добавляем каждый статус в фрагмент sql запроса
           foreach ( $realstatuses as $status => $name )
           {
               if ( $start )
               {
                   $sql .= ' OR ';
               }
       
               // Добавляем статус счета
               $params['status'.$status] = $status;
               $sql .= 'status = :status'.$status.'';
       
               $start = true;
           }
           
           // Получить все реальные договоры
           $contracts = $this->dof->storage('contracts')->get_records_select($sql, $params);
           
           // Если нет договоров, для которых надо создать счета
           if ( empty($contracts) )
           {// Закончили установку
               return true;
           }
           
           // Для каждого договора создаем счет
           foreach ( $contracts as $item )
           {
               // Получаем имя счета
               $name = $this->dof->get_string('name_account', 'billing', $item->num, 'modlib');
               
               // Добавляем счет для каждого договора
               if ( ! $this->dof->storage('accounts')->add_account(
                       'modlib',
                       'billing',
                       $item->id,
                       'contract',
                       'passive',
                       $name,
                       $item->num
                       )
                  )
               {
                   // Cообщаем , что один из счетов не добавился
                   dof_debugging('Ошибка при добавлении счета. Номер договора: '.$item->num , DEBUG_ALL);
               }
           }
       }
       
       return true;
    }
    
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
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
        return 2016031400;
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
        return 'neon';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'billing';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
         return array('storage'=>array('contracts' => 2014110000,
                                       'accounts'  => 2014110000
                                      ),
                      'workflow'=>array('accounts' => 2014110000,
                                        'contracts' => 2011082100
                                       )    
                     );
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
     * если требуется - возвращает количество секунд между запусками
     * если нет - возвращает false
     * @return mixed int или bool false
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
        // Делаем регресс к Справочнику договоров
        
        return $this->dof->storage('contracts')->is_access($do, $objid, $userid);
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        return $this->dof->storage('contracts')->require_access($do, $objid, $userid);
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
     * и без которых начать установку невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
         return array('storage'=>array('contracts' => 2014110000,
                                       'accounts'  => 2014110000
                                      ),
                      'workflow'=>array('accounts' => 2014110000,
                                        'contracts' => 20011082100
                                       )    
                     );
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
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin_modlib
    // **********************************************

    // **********************************************
    // Собственные методы
    // **********************************************

    /**
     * Возвращает номер аккаунта клиентского счета по contractid.
     * 
     * Если счет не найден - создается новый счет на основе данных договора
     * 
     * @param int $contractid - id контракта, к которому привязан счет.
     * @return int|bool - id счета или false в случае ошибки.
     */
    public function get_contract_account($contractid)
    {
        // Получаем счет
    	$account = $this->dof->storage('accounts')->get_record(array(
    	        'plugintype' => $this->type(), 
    	        'plugincode' => $this->code(), 
    	        'objectid' => $contractid
    	));
    	
    	if ( ! $account )
    	{// Счет не найлен - создаем новый счет для этого договора
    	    
    	    // Получаем договор
    	    $contract = $this->dof->storage('contracts')->get_record(array('id' => $contractid));
    	    
    	    if ( ! $contract )
    	    {// Договор не найден
    	        return false;
    	    }
    	    
    	    // Получаем имя счета
    	    $name = $this->dof->get_string('name_account', 'billing', $contract->num, 'modlib');
    	    
    	    // Создаем счет для договора с $contractid
    	    $accountid = $this->dof->storage('accounts')->add_account(
    	               $this->type(), 
    	               $this->code(), 
    	               $contractid, 
    	               'contract', 
    	               'passive', 
    	               $name, 
    	               $contract->num                              
    	            );
    	    
    	    // Возвращаем id добавленного счета
    	    if ( ! $accountid )
    	    {// Если добавления не произошло
    	        return false;
    	    } else 
    	    {// Если вернул $accountid
    	        return intval($accountid);
    	    }
    	    
    	}
    	return intval($account->id);
    }
    
    /**
     * Баланс по контракту на дату $date
     * Учитывается дата выполнения операции, а не ее создания
     * 
     * @param int $contractid - id контракта
     * @param int $date - дата в формате Unixtime
     * @return int - баланс
     */
    public function get_contract_balance($contractid,$date)
    {
        // Получаем id счета
        $accountid = $this->get_contract_account($contractid);
        
    	// Получаем баланс по счету
        $return = $this->dof->storage('accentryes')->get_account_balance($accountid, $date);
        
        if ($return === false)
        {
            return false;
        }
        return $return;
    }
    
    /**
     * Получить id Главного счета
     * 
     * @return bool|int - false если не нашли счет,
     *                    или id счета
     */
    public function get_main_account_id()
    {
        // Готовим массив с параметрами
        $params = array(
              'plugintype' => 'modlib',
              'plugincode' => 'billing', 
              'objectid' => 0,   
              'code' =>'contract',
              'type' => 'asset'
        );
        // Получаем объект главного счета
        $account = $this->dof->storage('accounts')->get_record($params);
        
        // Если не нашли главный счет
        if ( empty($account) )
        {
            return false;
        }
        
        // Возвращаем id счета
        return intval($account->id);
    }
    
    /**
     * Получить историю контракта
     * 
     * Если не передана дата - берется вся история по аккаунту
     * 
     * @param int $contractid - id контракта
     * @param int $date - дата в формате Unixtime
     * @return object - объект с историей по контракту
     * Формат вывода:
     * $obj->contract - объект контракта
     * $obj->account - объект счета
     * $obj->history - массив объектов accentry
     * $obj->firstentry - объект первой операции (включая вычисленное поле balance)
     * $obj->lastentry - объект последней операции (включая вычисленное поле balance)
     * $obj->dateentry - объект последней операции, до наступления date (включая вычисленное поле balance)
     * $obj->nowentry - объект последней перед текущей датой (включая вычисленное поле balance)
     * $obj->date - дата, переданная в $date (или текущее время)
     * $obj->datebalance - баланс на дату $date
     * $obj->nowbalance - баланс на текущее время
     * $obj->lastdate - дата последней операции
     * $obj->lastdatebalance - баланс после последней операции
     */
    public function get_contract_history($contractid, $date=null) 
    {
        // Получаем id счета по контракту
        $accountid = $this->get_contract_account($contractid);
        
        // Получаем всю историю счета 
        $history = $this->dof->storage('accentryes')->get_history($accountid);
        
        //Формируем историю операций по контракту
        $result = new stdClass();
        $result->contract = $this->dof->storage('contracts')->get_record(array('id' => $contractid));
        $result->account = $this->dof->storage('accounts')->get_record(array('id' => $accountid));
        $result->history = $history;
        
        $result->firstentry = null;
        $result->lastentry = null;
        $result->dateentry = null;
        $result->nowentry = null;
        $result->date = null;
        $result->datebalance = null;
        $result->nowbalance = null;
        $result->lastdate = null;
        $result->lastdatebalance = null;
        
        // Если не передано время, получаем текущее
        if ( is_null($date) )
        {
            $result->date = date('U');
        } else {
            $result->date = $date;
        }
        
        // Получаем остальные поля       
        $first = true;
        foreach ( $history as $item )
        {
            // Объект первой операции
            if ( $first )
            {
                $result->firstentry = $item;
                $first = false;
            }
            
            // Объект последней операции
            $result->lastentry = $item;
            
            // Объект последней операции, до наступления date
            if ( $item->date < $result->date )
            {
                $result->dateentry = $item;
            }
            
            // Объект последней перед текущей датой операции
            if ( $item->date < date('U') )
            {
                $result->nowentry = $item;
            }
            
            // Баланс на дату $date
            if ( $item->date <= $result->date )
            {
                $result->datebalance = $item->balance;
            }
            
            // Баланс на текущее время
            if ( $item->date <= date('U') )
            {
                $result->nowbalance = $item->balance;
            }
            
            // Дата последней операции
            $result->lastdate = $item->date;
            
            // баланс после последней операции
            $result->lastdatebalance = $item->balance;
        }  
        
        return $result;  
    }
    
    /**
     * Пополнение баланса
     * 
     * @param array $data - массив операций для приказа
     * @param int $actpersonid - id пользователя, от чъего лица будет создан приказ
     * Формат массива:
     * $data[]->contractid - id контракта
	 * $data[]->amount - сумма пополнения
	 * $data[]->date - дата исполнения операции
	 * $data[]->extentryopts[] - массив строк с внешними ключами операции для проверки уникальности регистрации внешней операции.
     * $data[]->extentryoptshash - md5 от json $data[]->extentryopts[]
     */
    public function refill_contract_balance($data,$actpersonid = 0)
    {
        // Проверка пользователя
        if ( $actpersonid < 1 || ! is_int($actpersonid) )
        {
            // Получаем id текущего пользователя
            $actperson = $this->dof->storage('persons')->get_bu();
            $actpersonid = $actperson->id;
        }
        
        $time = date('U');
        // Проверка доступа, для проверки необходимо передавать moodleid персоны
        // Получаем объект пользователя
        $user = $this->dof->storage('persons')->get_record(array('id' => $actpersonid));
        // Производим проверку
        $this->require_access('create:billinrefill', null, $user->mdluser);

        // Создаем приказ
        $order = $this->order('refill');
        // Создаем объект параметров приказа
        $orderobj = new stdClass();
        $orderobj->code = 'refill';
        $orderobj->ownerid = $actpersonid;
        $orderobj->signerid = $actpersonid;
        $orderobj->departmentid = $user->departmentid;
        $orderobj->date = $time;
        $orderobj->signdate = $time;
        $orderobj->exdate = $time;
        $orderobj->data = $data;

        // Сохраняем приказ
        $order->save($orderobj);
        $order->sign($actpersonid);
        $order->execute();
        
        // Проверяем корректность приказа
        if ( $order->is_executed() )
        {
            // Возвращаем id приказа
            return $order->get_id();
        } else 
        {
            // В случае ошибки
            return false;
        }
    }
    
    /**
     * Списание баланса
     * 
     * @param array $data - массив операций для приказа
     * @param int $actpersonid - id пользователя, от чъего лица будет создан приказ
     * Формат массива:
     * $data[]->contractid - id контракта
     * $data[]->amount - сумма списания
     * $data[]->date - дата исполнения операции 
     * $data[]->programsbcsid - id подписки на программу, за которую начисляется оплата, обязательный параметр.
     * $data[]->ageid - id периода, за который списана оплата. 
     * $data[]->agenum - номер параллели, за изучение которой происходит оплата
     * $data[]->learninghistoryid - id записи в learninghistory, на которую начисляется оплата
     * $data[]->extentryopts[] - массив строк с внешними ключами операции для проверки уникальности регистрации внешней операции
     * $data[]->extentryoptshash - md5 от json $data[]->extentryopts[]
     */
    public function writeof_contract_balance($data,$actpersonid = 0)
    {
        // Проверка пользователя
        if ( $actpersonid < 1 || ! is_int($actpersonid) )
        {
            // Получаем id текущего пользователя
            $actperson = $this->dof->storage('persons')->get_bu();
            $actpersonid = $actperson->id;
        }
        
        $time = date('U');
        // Проверка доступа, для проверки необходимо передавать moodleid персоны
        // Получаем объект пользователя
        $user = $this->dof->storage('persons')->get_record(array('id' => $actpersonid));
        // Производим проверку
        $this->require_access('create:billinwriteof', null, $user->mdluser);
        
        // Создаем приказ
        $order = $this->order('writeof');
        
        // Создаем объект параметров приказа
        $orderobj = new stdClass();
        $orderobj->code = 'writeof';
        $orderobj->ownerid = $actpersonid;
        $orderobj->signerid = $actpersonid;
        $orderobj->departmentid = $user->departmentid;
        $orderobj->date = $time;
        $orderobj->signdate = $time;
        $orderobj->exdate = $time;
        $orderobj->data = $data;
        
        // Сохраняем приказ
        $order->save($orderobj);
        $order->sign($actpersonid);
        $order->execute();
        
        // Проверяем корректность приказа
        if ( $order->is_executed() )
        {
            // Возвращаем id приказа
            return $order->get_id();
        } else
        {
            // В случае ошибки
            return false;
        }
    }
    
    /**
     * Отмена операции по контракту
     * 
     * @param unknown $contractid
     * @param unknown $accentryid
     * @param unknown $date
     * @param unknown $personid
     */
    public function cancell_contract_balance($contractid, $accentryid, $date=null, $personid = null)
    {
        // Проверка пользователя
        if ( empty($personid) || ! is_int($personid) )
        {
            // Получаем id текущего пользователя
            $person = $this->dof->storage('persons')->get_bu();
        } else 
        {
            // Получаем объект пользователя
            $person = $this->dof->storage('persons')->get_record(array('id' => $personid));
        }
        
        // Получим счет
        $accountid = $this->get_contract_account($contractid);
        if ( empty($accountid) )
    	{// Ошибка при получении номера счета
    	    return false;
    	}
    	
    	// Получим счет
    	$account = $this->dof->storage('accounts')->get_record(array('id' => $accountid));
    	if ( empty($account) )
    	{// Ошибка при получении счета
    	return false;
    	}
    	
    	// Получим операцию
        $accentry = $this->dof->storage('accentryes')->get_record(array('id' => $accentryid));
        if ( empty($accentry) )
        {// Ошибка при получении операции
            return false;
        }
        
        // Проверим на принадлежность счету
        if ( ( $accentry->fromid <> $account->id ) && ( $accentry->toid <> $account->id ) )
        {// Операция не принадлежит счету
            return false;
        }
        
        // Проверим права
        if ( ( $accentry->fromid == 1 ) )
        { // Пополнение
            $access = $this->is_access('create:billinrefill', null, $person->mdluser);
        } else 
        {// Списание
            $access = $this->is_access('create:billinwriteof', null, $person->mdluser);
        }
        if ( ! $access )
        {// Доступ запрещен
            return false;
        }
        
        // Если не передана дата отменяемой операции
        if ( empty($date) )
        {
            $date = $accentry->date + 1;
        }
        
        $data = array($accentry);
        
        // Создаем приказ
        $order = $this->order('cancel');
        // Создаем объект параметров приказа
        $orderobj = new stdClass();
        $orderobj->code = 'cancel';
        $orderobj->ownerid = $person->id;
        $orderobj->signerid = $person->id;
        $orderobj->departmentid = $person->departmentid;
        $orderobj->date = $date;
        $orderobj->signdate = $date;
        $orderobj->exdate = $date;
        $orderobj->data = $data;
        
        // Сохраняем приказ
        $order->save($orderobj);
        $order->sign($person->id);
        $order->execute();
        
        // Проверяем корректность приказа
        if ( $order->is_executed() )
        {
            // Возвращаем id приказа
            return $order->get_id();
        } else
        {
            // В случае ошибки
            return false;
        }
    }
	
    /**
     * Формирование приказов
     * 
     * Абстрактная фабрика, которая формирует приказы на пополнение, списание и отмену
     * 
     * @param string $code
     * @param int $id - id приказа в таблице orders
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        switch ($code)
        {
            case 'refill':
                require_once($this->dof->plugin_path('modlib',$this->code(),'/orders/refill/init.php'));
                
                $order = new dof_modlib_billing_order_refill($this->dof);
                
               if ( ! empty($id) )
                {// нам передали id, загрузим приказ
                    $orderdata = $order->load($id);
                    if ( ! $orderdata )
                    {// Не найден
                        
                        return false;
                    }
                    return $orderdata;
                }
                // Возвращаем объект
                return $order;
                
            case 'writeof':
                require_once($this->dof->plugin_path('modlib',$this->code(),'/orders/writeof/init.php'));
            
                $order = new dof_modlib_billing_order_writeof($this->dof);
            
                if ( ! empty($id) )
                {// нам передали id, загрузим приказ
                    $orderdata = $order->load($id);
                    if ( ! $orderdata )
                    {// Не найден
                        
                        return false;
                    }
                    return $orderdata;
                }
                // Возвращаем объект
                return $order;
            case 'cancel':
                require_once($this->dof->plugin_path('modlib',$this->code(),'/orders/cancel/init.php'));
                
                $order = new dof_modlib_billing_order_cancel($this->dof);
                
                if ( ! empty($id) )
                {// нам передали id, загрузим приказ
                    $orderdata = $order->load($id);
                    if ( ! $orderdata )
                    {// Не найден
                
                       return false;
                    }
                    return $orderdata;
                }
                // Возвращаем объект
                return $order;
        }
    }
}

?>