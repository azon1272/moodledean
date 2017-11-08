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

/**
 * Хранилище приказов. Класс плагина.
 *
 * @package     storage
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем дополнительную библиотеку
require_once $DOF->plugin_path('storage', 'orders','/baseorder.php');

class dof_storage_orders extends dof_storage
{
    /**
     * Объект деканата для доступа к общим методам
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
     * 
     * @return boolean
     */
    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $result = true;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2014011500)
        {// добавим поле salfactor
            dof_hugeprocess();

            $field = new xmldb_field('crondate',XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                     null, null, null, 'exdate');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            $index = new xmldb_index('icrondate', XMLDB_INDEX_NOTUNIQUE,
                    array('crondate'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        if ($oldversion < 2014013000)
        {
         while ( $list = $this->get_records_select('status IS NULL',null,'','*',0,100) )
            {
                foreach ($list as $order)
                {// ищем уроки где appointmentid не совпадает с teacherid
                    $obj = new stdClass;
                    $obj->status = 'requested';
                    if ( ! is_null($order->exdate) )
                    {
                        $obj->status = 'completed';
                    }
                    $this->update($obj,$order->id);
                }
            }
        }
        if ( $oldversion < 2016011800 )
        {
            $field = new xmldb_field('num', XMLDB_TYPE_CHAR, '40', NULL,
                false, NULL, NULL, 'code');
            if ( ! $dbman->field_exists($table, $field) )
            {// Поле не установлено
                // Добавление нового поля
                $dbman->add_field($table, $field);
            }
            
            $index = new xmldb_index('inum', XMLDB_INDEX_NOTUNIQUE,
                ['num']);
            if ( ! $dbman->index_exists($table, $index) )
            {// Индекс не установлен
                $dbman->add_index($table, $index);
            }
        }
        // Обновление полномочий, если они изменились
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return string
     */
    public function version()
    {
        return 2016042700;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'storage';
    }
    
    /**
     * Возвращает короткое имя плагина
     *
     * Оно должно быть уникально среди плагинов этого типа
     *
     * @return string
     */
    public function code()
    {
        return 'orders';
    }
    
    /**
     * Возвращает список плагинов, без которых этот плагин работать не может
     *
     * @return array
     */
    public function need_plugins()
    {
        return [
            'storage' => [
                'config'       => 2011080900,
                'departments'  => 2015110500,
                'acl'          => 2011040504
            ],
            'workflow' => [
            ]
        ];
    }
    
    /**
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin
     *
     * @see dof_modlib_base_plugin::is_setup_possible()
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
    
    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
            'storage' => [
                'config'       => 2011080900,
                'departments'  => 2015110500,
                'acl'          => 2011040504
            ],
            'workflow' => [
            ]
        ];
    }
    
    /**
     * Список обрабатываемых плагином событий
     *
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [];
    }
    
    /**
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        return false;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access( $do, $objid = NULL, $userid = NULL )
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для менеджеров
            return true;
        }
        
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);

        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        } 
        return false;
    }
    
    /**
     * Требует наличия полномочия на совершение действий
     *
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     *
     * @return bool
     *              true - можно выполнить указанное действие по
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
        }
    }
    
    /**
     * Обработать событие
     *
     * @param string $gentype - тип модуля, сгенерировавшего событие
     * @param string $gencode - код модуля, сгенерировавшего событие
     * @param string $eventcode - код задания
     * @param int $intvar - дополнительный параметр
     * @param mixed $mixedvar - дополнительные параметры
     *
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        return true;
    }
    
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (
     *              1 - только срочные, 
     *              2 - нормальный режим, 
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор, 
     *              3 - детальная диагностика
     *        )
     *        
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan, $messages)
    {
        return true;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        switch ( $code )
        {
            case 'orders_on_orderdata': $this->rewrite_orders(); break;
        }
        return true;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** 
     * Получить имя таблицы
     * 
     * @return string
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_orders';
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
        $result->objectid     = $objectid;
    
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else
        {
            $result->departmentid = $this->dof->storage($this->code())->get_field($objectid, 'departmentid');
        }
    
        return $result;
    }    

    /**
     * Проверить права через плагин acl.
     *
     * Функция вынесена сюда, чтобы постоянно не писать
     * длинный вызов и не перечислять все аргументы
     *
     * @param object $acldata - объект с данными для функции storage/acl->has_right()
     *
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
        has_right(
            $acldata->plugintype,
            $acldata->plugincode,
            $acldata->code,
            $acldata->personid,
            $acldata->departmentid,
            $acldata->objectid
        );
    }    
      
    /**
     * Задаем права доступа для объектов
     *
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles'=>array('manager'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['create'] = array('roles'=>array('manager'));
        $a['delete'] = array('roles'=>array());
        $a['use']    = array('roles'=>array('manager'));
        
        return $a;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************

    /*
     * Получить список приказов по параметрам
     */
    public function get_list_by_code($plugintype,$plogincode,$code,$departmentid=null,
                            $ownerid=null,$signerid=null,$status=null,$limitfrom='',$limitnum='',
                            $time=null, $sort='id ASC', $countonly=false)
    {
        $select = '';
        if (!empty($plugintype))
        {
            if (!empty($select)){$select .= " AND ";}
            $select .= "plugintype='{$plugintype}'";
        }
        if (!empty($plogincode))
        {
            if (!empty($select)){$select .= " AND ";}
            $select .= "plugincode='{$plogincode}'";
        }
        if (!empty($code))
        {
            if (!empty($select)){$select .= " AND ";}
            $select .= "code='{$code}'";
        }
        if (!empty($departmentid))
        {
            if (!empty($select)){$select .= " AND ";}
            $select .= "departmentid='{$departmentid}'";
        }
        if (!empty($ownerid))
        {
            if (!empty($select)){$select .= " AND ";}
            $select .= "ownerid='{$ownerid}'";
        }
        if (!empty($signerid))
        {
            if (!empty($select)){$select .= " AND ";}
            $select .= "signerid='{$signerid}'";
        }
        if (!empty($status))
        {
            if(is_array($status))
            {
                if(count($status)>0)
                {
                    if (!empty($select)){$select .= " AND ";}
                    $select .= "status IN ('".implode("','",$status)."')";
                }
            }
            else
            {
                if (!empty($select)){$select .= " AND ";}
                $select .= "status='{$status}'";
            }
        }
        if (!empty($time) AND is_array($time))
        {
            if (!empty($select))
            {    
                $select .= " AND ";
            }
            
            if ( ! empty($time['begindate']) )
            {
                $select .= "exdate>='{$time['begindate']}'";
            }
            
            if ( ! empty($time['enddate']) )
            {
                $select .= "AND exdate<='{$time['enddate']}'";
            }     
        }
        
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($select, array(),'COUNT(*)');
        }
        return $this->get_records_select($select, null,$sort, '*', $limitfrom, $limitnum);
    }


    /** Получить список записей по любому количеству полей
     * 
     * @todo удалить этот метод, когда в базовом классе storage появится 
     * нормальная функция для извлечения данных
     * 
     * @return array 
     * @param array $options[optional] - список условий в формате "название поля" => "значение"
     */
    public function get_orders($options = array(), $sort=" date ASC ", $fields='*', $limitfrom='', $limitnum='')
    {
        if ( $options AND  ! is_array($options) AND ! is_object($options) )
        {// неправильный формат входных данных
            return false;
        }
        
        $queries = array();
        foreach ( $options as $field => $value )
        {// перебираем все условия и составляем sql-запрос
            if ( ! empty($value) )
            {// если значение не пустое
                $queries[] = $this->query_part_select($field, $value);
            }
        }
        
        // объединием все фрагменты запроса через AND
        $sql = implode(' AND ', $queries);

        // возвращаем выборку
        return $this->get_records_select($sql.'AND exdate IS NULL', null,$sort, $fields, $limitfrom, $limitnum);
    }
    
    /** Сгенерировать все запрошенные отчеты
     * 
     * @return bool
     * @param string $plugintype[optional] - тип плагина, для которого нужно сгенерировать отчеты
     * @param string $plugincode[optional] - код плагина, для которого нужно сгененрировать отчеты
     * @param string|array $codes[optional] - код (или массив кодов) отчетов, которые нужно сгенерировать
     * @param int $departmentid[optional] - id подразделения в таблице departments, которому 
     *                                      принадлежат отчеты
     * @param int $personid[optional] - id пользователя в таблице persons, который запросил отчет
     * @param int $limit[optional] - сколько максимум отчетов создать
     */
    public function generate_orders($plugintype=null, $plugincode=null, $codes=null, $departmentid=null, $personid=null, $limit=null)
    {
        // для генераци нескольких отчетов может  понадобится очень много времени
        // @todo запустить здесь счетчик выполнения процесса, когда появится такая возможность
        dof_hugeprocess();
        // Собираем условия, по которым будем запрашивать отчеты:
        $options = array();
        if ( $plugintype )
        {
            $options['plugintype'] = $plugintype;
        }
        if ( $plugincode )
        {
            $options['plugincode'] = $plugincode;
        }
        if ( ! empty($codes) )
        {// нужно сформировать только отчеты с определененым кодом  
            if ( is_array($codes) OR is_string($codes) )
            {
                $options['code'] = $codes;
            }
        }
        // нужны только отчеты со статусом "запрошен"
        //$options['status'] = 'requested';
        
        if ( ! $orders = $this->get_orders($options, " date ASC ", '*', '', $limit) )
        {// отчетов, ожидающих генерации нет - все нормально.
            return true;
        }
       
        foreach ( $orders as $order )
        {// собираем все отчеты, которые были запрошены, но еще не сформированы
            // перебираем их и формируем каждый по очереди
            if ( empty($order->crondate) )
            {
               continue; 
            }
            // учитываем поле crondate
            if ( !empty($order->crondate) AND $order->crondate > time() )
            {
                continue;
            }
            if ( $order->exdate )
            {// приказ уже выполнен
                continue;
            }
            // подключаем класс, который будет генерировать отчет
            if ( ! $reportobj = $this->order($order->plugintype,$order->plugincode,$order->code,$order->id) )
            {// в плагине нет отчета с таким кодом - это ошибка 
                continue;
            }
            // если все нормально подключилось - генерируем отчет
            $reportobj->execute();
        }
        
        return true;
    }
    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function order($plugintype, $plugincode, $code, $id = NULL)
    {
        if ( ! $plugintype OR ! $plugincode OR ! $code )
        {
            return false;
        }
        
        // получаем имя файла
        $filepath = $this->dof->plugin_path($plugintype, $plugincode, '/orders/'.$code.'/init.php');
        
        
        // получаем имя класса
        $classname = 'dof_'.$plugintype.'_'.$plugincode.'_order_'.$code;
        
        if ( ! file_exists($filepath) )
        {// нет файла с указанным названием
            // @todo записать ошибку в лог
            echo $filepath;
            return false;
        }
        
        // подключаем файл с классом сбора данных для отчета
        require_once($filepath);
          
        
        if ( ! class_exists($classname) )
        {// в файле нет нужного класса
            // @todo записать ошибку в лог

            return false;
        }
        // создаем объект для сбора данных
        return new $classname($this->dof, $id);
    }
    
    /** Перезаписывает ордера на новую таблицу orderdata
     * 
     * @return array
     */
    public function rewrite_orders()
    {
        $result = true;
        // времени понадобится много
        dof_hugeprocess();
        // выводим сообщение о том что начинается очистка таблицы событий
        dof_mtrace(2, 'Starting orders rewrite on orderdata(storage/orderss)...');
        
        while ( $orders = $this->get_records_select(' sdata IS NOT NULL ',null, '', '*', 0, 100) )
        {// нас интересуют все запланированные или отложенные события, которые привязаны к 
            // несуществующим или удаленным дням
            foreach ( $orders as $order )
            {
                $this->todo_rewrite_data($order);
                dof_mtrace(2, 'orderid='.$order->id.' rewrite on orderdata');
            }
        }
        
        return $result;
    }

    /**
     * Метод, который записывает в таблицу orderdata присланный ордер
     * если запись прошла плохо - данные sdata не затираются
     * @param object $order - сам ордер
     * @param object/array $data - данные масива для сериалицации(они тут разбираются)
     */
    protected function todo_rewrite_data($order)
    {
        // переменная для подсчета НОМЕРА переменной(чтоб правильно собрать)
        $i = 0;
        $obj = new stdClass();
        // сохраняем ордер, на который ссылается
        $obj->orderid = $order->id;
        // переменная - удалять из ордера sdata или нет
        $delete = true;
        
        $data = unserialize($order->sdata);
        if ( is_array($data) OR is_object($data) )
        { 
            foreach ( $data as $key=>$value )
            {// перебираем сами данные
                $i++;
                // запоминаме имя
                $obj->firstlvlname = $key;
                // порядковый номер имени
                $obj->varnum = $i;
    
                if ( is_array($value) OR is_object($value) )
                {// если не расшипился до значений - сериализуеи и сохраняем
                    // не скаляр
                    $obj->scalar = 0;
                    $obj->data = serialize($value);
                    // запишем данные поле в индекс
                    // исключение проблемы экранирования кавычки
                    $obj->ind = substr($obj->data,0,254);
                }else 
                {// обычный скаляр
                    $obj->scalar = 1;
                    $obj->data = $value;
                    // запишем данные в поле индекс
                    $obj->ind = $value; 
                }
                // вставляем
                if ( ! $this->dof->storage('orderdata')->insert($obj) )
                {// что-то не установилось - удалять нельзя 
                    $delete = false;
                }
            }    
        }elseif( is_null($data) ) 
        {// как правило ЭТО при выставлении оценок и закрытии ведомости с пустой оценкой
            $delete = true;
        } 
        if ( $delete )
        {
            $order->sdata = NULL;
            $this->dof->storage($this->code())->update($order, $order->id);
        }

        return true;
    }  

    /** Возвращает список отчетов по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds,'o.');
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tblpersons = $this->dof->storage('persons')->prefix().$this->dof->storage('persons')->tablename();
        $tblorders = $this->prefix().$this->tablename();
        if (strlen($select)>0)
        {
            $select .= ' AND ';
        }
        $sql = "from {$tblorders} as o, {$tblpersons} as pr
                where $select (o.ownerid=pr.id OR o.signerid=pr.id)";
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_sql("select count(*) {$sql}");
        }
        // добавим сортировку
        // сортировка из других таблиц
        $outsort = '';
        if ( isset($sort['ownersortname']) )
        {// сортировка из другой таблицы';
            $dir = 'asc';
            if ( isset($sort['dir']) )
            {// вид сортировки
                $dir = $sort['dir'];
            }    
            $outsort = 'pr.sortname '.$dir.',';
            unset($sort['ownersortname']);
        }
        if ( isset($sort['signsortname']) )
        {// сортировка из другой таблицы';
            $dir = 'asc';
            if ( isset($sort['dir']) )
            {// вид сортировки
                $dir = $sort['dir'];
            }    
            $outsort = 'pr.sortname '.$dir.',';
            unset($sort['signsortname']);
        }
        $sql .= " ORDER BY ".$outsort.' '.$this->get_orderby_listing($sort,'o.');
        return $this->get_records_sql("select o.*, pr.sortname as sortname {$sql}", null, $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds,$prefix='')
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( ! empty($conds) )
        {// теперь создадим все остальные условия
            foreach ( $conds as $name=>$field )
            {
                if ( $field )
                {// если условие не пустое, то для каждого поля получим фрагмент запроса
                    $selects[] = $this->query_part_select($prefix.$name,$field);
                }
            } 
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }

    /**
     * Проверка номера приказа на уникальность
     *
     * @param string $num
     *            - номер приказа
     * @param int $departmentid
     *            - идентификатор подразделения
     *            
     * @return bool
     */
    public function is_unique_order_num( $num, $departmentid )
    {
        if ( ! empty($num) )
        { //уникальность проверяется только для непустого номера договора
            //получаем подразделение
            $currentdepartment = $this->dof->storage('departments')->get($departmentid);
            //обещает ли подразделение уникальность внутри себе (является нумератором)
            $iscounter = $this->dof->storage('config')->get_config_value(
                'set_unique_ordersnum_context', 'im', 'orders', $currentdepartment->id);
            if ( $iscounter || $currentdepartment->depth == 0 )
            { //найдено ближайшее подразделение, внутри которого должна проверяться уникальность
                //получение спика всех дочерних подразделений
                $subdepartments = $this->dof->storage('departments')->get_departments(
                    $currentdepartment->id);
                foreach ( $subdepartments as $sdid => $subdepartment )
                {
                    if ( $this->dof->storage('config')->get_config_value(
                        'set_unique_ordersnum_context', 'im', 'orders', $sdid) )
                    { //подразделение само является нумератором - исключаем его и потомков из проверяемых
                        //получаем потомков ненужного для проверки подразделения
                        $subcountersubdepartments = $this->dof->storage('departments')->get_departments(
                            $sdid);
                        foreach ( $subcountersubdepartments as $scsdid => $subcountersubdepartment )
                        {
                            //исключаем их из искомых значений
                            unset($subdepartments[$scsdid]);
                        }
                        //исключаем подразделение-нумератор из искомых значений
                        unset($subdepartments[$sdid]);
                    }
                }
                
                //список подразделений для поиска
                $conddepartments = array_keys($subdepartments);
                //внутри текущего подразделения уникальность тоже должна проверяться
                $conddepartments[] = $currentdepartment->id;
                
                //получим приказы с таким же номером внутри дочерних подразделений
                $samenumorders = $this->get_records(
                    [
                        'departmentid' => $conddepartments,
                        'num' => $num
                    ]);
                //вернем результат - есть ли приказы с подобным номером
                return empty($samenumorders);
            } else
            { //уникаольность должна проверяться не только по этому подразделению, но и выше
                return $this->is_unique_order_num($num, $currentdepartment->leaddepid);
            }
        }
    }
    
    /**
     * Возвращает фрагмент sql-запроса c ORDER BY
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_orderby_listing($sort,$prefix='')
    {
        // по-умолчанию дата завершения
        $sqlsort = $prefix.'exdate';
        if ( ! is_array($sort) )
        {// сортировки не переданы - вернем умолчание
            return $sqlsort;
        }
        $dir = 'asc';
        if ( isset($sort['dir']) )
        {// вид сортировки
            $dir = $sort['dir'];
            unset($sort['dir']);
        }
        if ( empty($sort) )
        {// сортировок нет - вернем умолчание с видом
            return $sqlsort.' '.$dir;
        }
        // формируем сортировку
        $selects = array();
        foreach ( $sort as $field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $prefix.$field.' '.$dir;
            }
        } 
        // добавим умолчание в конец
        $selects[] = $prefix.'exdate '.$dir;
        // возвращаем сортировку
        return implode($selects,',');
    }
    
    /** Обработка AJAX-запросов из форм
     * @param string $querytype - тип запроса
     * @param int $objectid - id объекта с которым производятся действия
     * @param array $data - дополнительные данные пришедшие из json-запроса
     *
     * @return array
     */
    public function widgets_field_variants_list($querytype, $depid, $data)
    {
        switch ( $querytype )
        {
            // список организаций для autocomplete-элемента          
            // результат содержит список организаций из базы в формате id => fullname (shortname)
            case 'orders_change_teacher':    
                return $this->widgets_orders_change_teacher($depid, $data);
            
            default: return array(0 => $this->dof->modlib('ig')->igs('choose'));
        }
    }
    
    
    /** Получить приказ на смену нагрузки преподавателя по номеру приказа, в
     *  подсказке отображаем дату, направление и ФИО, отображаем только приказы
     *  на передачу нагрузки. Находятся только приказы, передающие часы от преподавателя
     * 
     * @param int $departmenid - подразделение, в котором ищутся организации
     *                           если передан 0 - то организации ищутся во всех подразделениях
     * @param string $number - номер приказа
     *
     * @return array массив объектов для AJAX-элемента dof_autocomplete
     */
    protected function widgets_orders_change_teacher($departmentid=0, $number)
    {
        if ( !is_numeric($number) OR !is_numeric($departmentid))
        {
            return array();
        }
        //$this->dof->storage('agroups')->is_access('edit', $obj->id)
        //$this->is_access('use', $organization->id, $USER->id, $departmentid)
        $number = clean_param($number, PARAM_TEXT);
        $params = array('id'=>$number);
        if ( $departmentid > 0 )
        {
            $params['departmentid'] = $departmentid;
            
        }
        // Сначала проверим, есть ли приказ с таким номером с системе
        if ( !$this->is_exists($params) )
        {
            return array();
        }
        // Достанем приказ и проверим, тот ли это
        $order = $this->get_record($params);
        if ( $order->code != 'change_teacher' OR empty($order->exdate) )
        {
            return array();
        }
        // В orderdata находится всё остальное: направление, фио, потоки
        $orderdata = $this->dof->storage('orderdata')->get_records(array('orderid'=>$number));
        // Формируем массив объектов нужной структуры для dof_autocomplete
        $fullname = $direction = '';
        foreach ($orderdata as $data)
        {
            if ( $data->firstlvlname == 'direction' )
            { // Найдём из данных приказа направление
                $direction = $data->data;
                if ( $direction != 'fromteacher' )
                { // Ищем только приказы на передачу нагрузки от преподавателя
                    return array();
                }
            }
            if ( $data->firstlvlname == 'fullname' )
            { // Найдём из данных приказа имя
                $fullname = $data->data;
            }
        }
        $result = array();
        $obj = new stdClass();
        $obj->id         = $number;
        $obj->date       = $order->date;
        $obj->direction  = $direction;
        $obj->fullname   = $fullname;
        $date            = dof_userdate($obj->date,'%Y-%m-%d');
        $direction       = 'Направление: ' . $this->dof->get_string($direction,'cstreams');
        $obj->name       = "{$obj->fullname} [{$date}] [{$direction}]";
        $result[$number] = $obj;
        return $result;
    }    
    

    /**
     * Получение списка типов плагинов, по которым имеются приказы
     * 
     * @return array - Массив типов плагинов, для которых созданы приказы
     */
    public function get_list_ptypes()
    {
        $result = [];
        
        // Получение уникальных типов плагинов
        $orders = $this->get_records_select('', NULL, 'plugintype ASC', 'DISTINCT(plugintype)');
        
        if( ! empty($orders) )
        {// Типы приказов найдены
            foreach( $orders as $order )
            {// Добавление приказа в список
                $result[$order->plugintype] = $this->dof->get_string($order->plugintype.'s', 'admin');
            }
        }
        
        return $result;
    }
    
    /**
     * Получение списка кодов плагинов, соответствующих переданному типу плагина
     * 
     * @param string $ptype - Тип плагина
     * 
     * @return array - Массив кодов плагинов, для которых созданы приказы 
     */
    public function get_list_pcodes($ptype)
    {
        $result = [];
        
        // Получение кодов плагина
        $orders = $this->get_records(['plugintype' => $ptype], 'plugincode ASC', 'DISTINCT(plugincode)');
        
        if( ! empty($orders) )
        {// Коды найдены
            foreach($orders as $order)
            {// Добавление кода плагина
                $result[$order->plugincode] = $this->dof->get_string('title', $order->plugincode);
            }
        }
        return $result;
    }
    
    /**
     * Получение списка кодов приказов, соответствующих переданному типу и коду плагина
     *
     * @param string $ptype - Тип плагина
     * @param string $ptype - Код плагина
     * 
     * @return array - Массив кодов приказов, которые были созданы для указанных плагинов
     */
    public function get_list_codes($ptype, $pcode)
    {
        $result = [];
        
        // Поучение кодов приказов
        $orders = $this->get_records(['plugintype' => $ptype, 'plugincode' => $pcode], 'code ASC', 'DISTINCT(code)');
        
        if( ! empty($orders) )
        {// Приказы найдены
            foreach($orders as $order)
            {// Добавление приказов 
                $result[$order->code] = $order->code;
            }
        }
        
        return $result;
    }
}

?>