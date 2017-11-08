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

// Подключаем дополнительную библиотеку
require_once $DOF->plugin_path('storage','config','/config_default.php');
require_once $DOF->plugin_path('storage', 'reports','/basereport.php');

/*
 * Хранилище для описания истории подписок в учебных периодах
 */

class dof_storage_reports extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** 
     * Инсталяция плагина
     */
    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        // после установки плагина устанавливаем права
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }  
    
    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        
        global $CFG;
        //методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');
        
        $result = true;
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** 
     * Возвращает версию установленного плагина
     * 
     * @return string
     * @access public
     */
    public function version()
    {
        return 2013021100;
    }
    /** 
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * 
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
     * 
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'paradusefish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
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
     * 
     * @return string
     * @access public
     */
    public function code()
    {
        return 'reports';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * 
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('acl' => 2011082200));
    }
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * 
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
                                      'config'=> 2011080900));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array();
    }
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     * @access public
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
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin') 
             OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);   
        // проверка
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        } 
        return false;
    }

    /** 
     * Требует наличия полномочия на совершение действий
     * 
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
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "reports/{$do} ";
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
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {

        return true;
    }
    /** 
     * Запустить обработку периодических процессов
     * 
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
     * 
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
     * 
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
        return 'block_dof_s_reports';
    }

    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************  
    
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
      
    /** 
     * Возвращает стандартные полномочия доступа в плагине
     * 
     * @return array
     *  a[] = array( 'код полномочия'  => array('список ролей'),
     * 				 'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        $a['view_report'] = array('roles'=>array(''));
        $a['view_report_sync_mreport_teachershort'] = array('roles'=>array('manager'));
        $a['view_report_sync_mreport_teacherfull']  = array('roles'=>array('manager'));
        $a['view_report_sync_mreport_studentshort'] = array('roles'=>array('manager'));
        $a['view_report_sync_mreport_studentfull']  = array('roles'=>array('manager'));
        $a['view_report_im_journal_loadteachers']   = array('roles'=>array('manager'));
        $a['view_report_im_journal_replacedevents'] = array('roles'=>array('manager'));
        $a['view_report_im_inventory_loaditems']    = array('roles'=>array('manager'));
        $a['view_report_im_inventory_loadpersons']  = array('roles'=>array('manager'));
        $a['request_report'] = array('roles'=>array(''));
        $a['request_report_sync_mreport_teachershort'] = array('roles'=>array('manager'));
        $a['request_report_sync_mreport_teacherfull']  = array('roles'=>array('manager'));
        $a['request_report_sync_mreport_studentshort'] = array('roles'=>array('manager'));
        $a['request_report_sync_mreport_studentfull']  = array('roles'=>array('manager'));
        $a['request_report_im_journal_loadteachers']   = array('roles'=>array('manager'));
        $a['request_report_im_journal_replacedevents'] = array('roles'=>array('manager'));
        $a['request_report_im_inventory_loaditems']    = array('roles'=>array('manager'));
        $a['request_report_im_inventory_loadpersons']  = array('roles'=>array('manager'));
        $a['export_report'] = array('roles'=>array(''));
        //$a['export_report_sync_mreport_teachershort'] = array('roles'=>array('manager'));
        //$a['export_report_sync_mreport_teacherfull']  = array('roles'=>array('manager'));
        //$a['export_report_sync_mreport_studentshort'] = array('roles'=>array('manager'));
        //$a['export_report_sync_mreport_studentfull']  = array('roles'=>array('manager'));
        $a['export_report_im_journal_loadteachers']   = array('roles'=>array('manager'));
        //$a['export_report_im_journal_replacedevents'] = array('roles'=>array('manager'));
        //$a['export_report_im_inventory_loaditems']    = array('roles'=>array('manager'));
        //$a['export_report_im_inventory_loadpersons']  = array('roles'=>array('manager'));
        // удалени отчетов - физическое
        $a['delete']  = array('roles'=>array());          
        return $a;
    }    
    
    
    /** 
     * Функция получения настроек для плагина 
     */
    public function config_default($code=null)
    {
        // отчет по учителям
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'report_teachers';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // отчет по ученикам)
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'report_persons';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // отчет по оборудованию
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'report_items';
        $obj->value = '1';
        $config[$obj->code] = $obj;         
        return $config;
    }    

    
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** 
     * Получить список записей по любому количеству полей
     * 
     * @todo удалить этот метод, когда в базовом классе storage появится 
     * нормальная функция для извлечения данных
     * 
     * @return array 
     * @param array $options[optional] - список условий в формате "название поля" => "значение"
     */
    public function get_report_listing($options = array(), $sort=" name ASC ", $fields='*', $limitfrom='', $limitnum='')
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
        return $this->get_records_select($sql, null,$sort, $fields, $limitfrom, $limitnum);
    }
    
    /** 
     * Возвращает список отчетов по заданным критериям 
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
        $select = $this->get_select_listing($conds);
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tblpersons = $this->dof->storage('persons')->prefix().$this->dof->storage('persons')->tablename();
        $tblorders = $this->prefix().$this->tablename();
        if (strlen($select)>0)
        {// сделаем необходимые замены в запросе
            $select = 'o.'.preg_replace('/ AND /',' AND o.',$select.' ').' AND ';
            $select = preg_replace('/ OR /',' OR o.',$select);
            $select = str_replace('o. (','(o.',$select);

        }
        $sql = "from {$tblorders} as o, {$tblpersons} as pr
                where $select o.personid=pr.id ";
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_sql("select count(*) {$sql}");
        }
        // добавим сортировку
        // сортировка из других таблиц
        $outsort = '';
        if ( isset($sort['sortname']) )
        {// сортировка из другой таблицы';
            $dir = 'asc';
            if ( isset($sort['dir']) )
            {// вид сортировки
                $dir = $sort['dir'];
            }    
            $outsort = 'pr.sortname '.$dir.',';
            unset($sort['sortname']);
        }
        $sql .= " ORDER BY ".$outsort.' '.$this->get_orderby_listing($sort,'o');
        return $this->get_records_sql("select o.*, pr.sortname as sortname {$sql}", null, $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * 
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( isset($conds->begindate) )
        {
            // формируем дату начала дня
            $dateday = getdate($conds->begindate);
            $time = $this->dof->storage('persons')->get_make_timestamp(0,0,0,$dateday['mon'],$dateday['mday'],$dateday['year']);
            // создадим условие при котором выборка происходит 
            // не раньше начала дня и не позже его конца
            $selects[] = 'begindate >= '.$time;
            unset($conds->begindate);
        } 
        if ( isset($conds->enddate) )
        {
            // формируем дату начала дня
            $dateday = getdate($conds->enddate);
            $time = $this->dof->storage('persons')->get_make_timestamp(0,0,0,$dateday['mon'],$dateday['mday'],$dateday['year']);
            // создадим условие при котором выборка происходит 
            // не раньше начала дня и не позже его конца
            $selects[] = 'enddate <= '.$time;
            unset($conds->enddate);
        } 
        
        if ( ! empty($conds) )
        {// теперь создадим все остальные условия
            foreach ( $conds as $name=>$field )
            {
                if ( $field )
                {// если условие не пустое, то для каждого поля получим фрагмент запроса
                    $selects[] = $this->query_part_select($name,$field);
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
     * Возвращает фрагмент sql-запроса c ORDER BY
     * 
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_orderby_listing($sort,$prefix='')
    {
        // по-умолчанию дата завершения
        $sqlsort = $prefix.'.completedate';
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
                $selects[] = $prefix.'.'.$field.' '.$dir;
            }
        } 
        // добавим умолчание в конец
        $selects[] = $prefix.'.completedate '.$dir;
        // возвращаем сортировку
        return implode($selects,',');
    }
    
    /** 
     * Сгенерировать все запрошенные отчеты
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
    public function generate_reports($plugintype=null, $plugincode=null, $codes=null, $departmentid=null, $personid=null, $limit=null)
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
        $options['status'] = 'requested';
        
        if ( ! $reports = $this->get_report_listing($options, " name ASC ", '*', '', $limit) )
        {// отчетов, ожидающих генерации нет - все нормально.
            // var_dump($reports);
            return true;
        }
       
        foreach ( $reports as $report )
        {// собираем все отчеты, которые были запрошены, но еще не сформированы
            // перебираем их и формируем каждый по очереди
            
            // учитываем поле crondate
            if ( isset($report->crondate) AND $report->crondate > time() )
            {
                continue;
            }
            
            // подключаем класс, который будет генерировать отчет
            if ( ! $reportobj = $this->report($report->plugintype,$report->plugincode,$report->code,$report->id) )
            {// в плагине нет отчета с таким кодом - это ошибка 
                $this->set_report_error($report->id);
                // отметили что при генерации отчета произошла ошибка, и переходим к следующему
                continue;
            }
            // если все нормально подключилось - генерируем отчет
            $reportobj->generate();
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
    public function report($plugintype, $plugincode, $code, $id = NULL)
    {
        if ( ! $plugintype OR ! $plugincode OR ! $code )
        {
            return false;
        }
        
        // получаем имя файла
        $filepath = $this->dof->plugin_path($plugintype, $plugincode, '/reports/'.$code.'/init.php');
        
        // получаем имя класса
        $classname = 'dof_'.$plugintype.'_'.$plugincode.'_report_'.$code;
        
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
    
    /** 
     * Отметить, что при генерации отчета произошла ошибка
     * 
     * @return bool
     * @param int $id - id отчета
     */
    protected function set_report_error($id)
    {
        $error         = new stdClass();
        $error->id     = $id;
        $error->status = 'error';
        return $this->update($error);
    }

    /* 
     * Удаляет физически отчет из бд
     * а также и фал отчета с сервера
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - rjl плагина
     * @param object $report - запись из табл reports
     * 
     * return boolean
     */
    public function delete_report($report)
    {
        // проверим права на удаление
        $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        // удаляет тот у кого есть права или кто создал этот отчет
        if ( $this->is_access('delete',$report->id) OR $personid == $report->personid )
        {
            $path = $this->dof->plugin_path($report->plugintype,$report->plugincode,'/dat/'.$report->id.'.dat');
          
            if ( file_exists($path) )
            {
                unlink($path);
            }
            return $this->delete($report->id);
        }
        return false;
    }
    
    /* 
     * Получает последний отчет с коррекцией 
     * а также и фал отчета с сервера
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - rjl плагина
     * @param object $report - запись из табл reports
     * 
     * return boolean
     */
    public function get_last_correct_journal_report($time)
    {
        $params = new stdClass;
    	$params->departmentid = optional_param('departmentid', null, PARAM_INT);
        $params->plugintype = 'im';
        $params->plugincode = 'journal';
        $params->code       = 'loadteachers';
        $params->status     = 'completed';
        $dateday = getdate($time);
        $params->begindate  = mktime(0,0,0,$dateday['mon']-1,1,$dateday['year']);
        $params->enddate    = mktime(0,0,0,$dateday['mon'],0,$dateday['year']);
        // получаем список доступных учебных периодов
    	$select = $this->dof->storage('reports')->get_select_listing($params);
    	if ( ! $reports = $this->dof->storage('reports')->get_records_select($select,null,'requestdate desc') )
        {//не найдены отчеты';
            return false;
        }
        foreach ( $reports as $report )
        {
            $reportdata = unserialize($report->requestdata);
            if ( isset($reportdata->forecast) AND $reportdata->forecast )
            {// нашли отчет - вернем его
                return $report;
            }
        }
        //нет отчетов с корректировкой';
        return false;
    }
    
}    

