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

// подключаем интерфейс настроек, чтобы в плагине работали настройки
require_once($DOF->plugin_path('storage','config','/config_default.php'));
//implements dof_plugin_modlib, dof_storage_config_interface
/** Шаблоны расписания
 * 
 */
class dof_storage_schtemplates extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /**
     * 
     * @see parent::install()
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
    
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $result = true;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        
        if ($oldversion < 2013062700)
        {// добавим поле salfactor
            $field = new xmldb_field('salfactor', XMLDB_TYPE_FLOAT, '6', XMLDB_UNSIGNED, 
                    true, null, '1', 'status');
            // количество знаков после запятой
            $field->setDecimals('2');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('isalfactor', XMLDB_INDEX_NOTUNIQUE,
                    array('salfactor'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2013062700;
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
        return 'paradusefish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'schtemplates';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array('storage'=>array('cstreams'    => 2011062103,
		                              'departments' => 2011060201,
		                              'persons'     => 2009060400,
									  'config'      => 2011040500,
		                              'acl'         => 2011041800));
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
        return array('storage'=>array('acl'=>2011040504,
                                      'config'=> 2011080900));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
    }
    /** Требуется ли запуск cron в плагине
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
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "schtemplates/{$do} (block/dof/storage/schtemplates: {$do})";
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
        return 'block_dof_s_schtemplates';
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
    protected function get_access_parametrs($action, $objectid, $personid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
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
     * 				 'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']     = array('roles'=>array('manager','methodist'));
        $a['edit']     = array('roles'=>array('manager'));
        $a['create']   = array('roles'=>array('manager'));
        $a['delete']   = array('roles'=>array('manager'));
        $a['use']      = array('roles'=>array('manager','methodist'));
        
        return $a;
    } 
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Возвращает список учебных потоков по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param object $countonly[optional] - только вернуть количество записей по указанным условиям
     */
    public function get_objects_list($conds = null, $sort='', $fields='*', $limitfrom = 0, $limitnum = 0)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tbl = $this->prefix().$this->tablename();
        if ( $fields )
        {// переданы поля, которые следует отобразить
            $fields = 'sch.'.$fields;
            $fields = str_replace(',',', sch.',$fields);
            // необходимые поля из потока
            $fields .= ',cs.ageid';
        }
        $tblcstream = $this->dof->storage('cstreams')->prefix().$this->dof->storage('cstreams')->tablename();
        $sql = "SELECT {$fields} FROM {$tbl} as sch, {$tblcstream} as cs";
        $sql .= " WHERE sch.cstreamid=cs.id";
        if ( isset($conds->ageid) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cs.ageid',$conds->ageid));
             // удалим из полей шаблона
             unset($conds->ageid);
        }
        if ( isset($conds->cstreamsstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cs.status',$conds->cstreamsstatus));
             // удалим из полей шаблона
             unset($conds->cstreamsstatus);
        }
        if ( isset($conds->teacherid) AND $conds->teacherid == 0 )
        {// ищем вакансию - глубоко копать не надо ищем прям так
            $sql .=' AND cs.appointmentid=0';
             // удалим из полей шаблона
             unset($conds->teacherid);
        }
        if ( $select = $this->get_select_for_sql($conds) )
        {// выборка не пустая
            $select = ' AND sch.'.preg_replace('/ AND /',' AND sch.',$select.' ');
            $select = preg_replace('/ OR /',' OR sch.',$select);
            $select = str_replace('sch. (','(sch.',$select);
            $select = str_replace('sch.(','(sch.',$select);
            $sql .= " {$select}";
        }
        if ( ! empty($sort) )
        {// сортировка не пустая
            $sort = 'sch.'.str_replace(',',', sch.',$sort);
            $sql .= " ORDER BY {$sort}";
        }

        return $this->get_records_sql($sql, null, $limitfrom, $limitnum);
    }
    
    /** Возвращает список учебных потоков по заданным критериям 
     * слитые с параметрами из таблицы cstreamlinks и cpassed
     * @return array массив записей из базы, или false в случае ошибки
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     */
    public function get_groups_list($conds = null)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        // возвращаем ту часть массива записей таблицы, которую нужно
        //return $this->get_list_select($select, '', '*', $limitfrom, $limitnum);
        $tblcslinks = $this->dof->storage('cstreamlinks')->prefix().$this->dof->storage('cstreamlinks')->tablename();
        $tblagroup = $this->dof->storage('agroups')->prefix().$this->dof->storage('agroups')->tablename();
        $tblcstreams = $this->dof->storage('cstreams')->prefix().$this->dof->storage('cstreams')->tablename();
        $tbl = $this->prefix().$this->tablename();
        $sql = "SELECT ag.*
                FROM {$tbl} as sch, {$tblcstreams} as c, {$tblcslinks} as cl, {$tblagroup} as ag
                WHERE sch.cstreamid=c.id AND c.id=cl.cstreamid AND cl.agroupid=ag.id";
        if ( isset($conds->ageid) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('c.ageid',$conds->ageid));
             // удалим из полей шаблона
             unset($conds->ageid);
        }
        if ( isset($conds->cstreamsstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('c.status',$conds->cstreamsstatus));
             // удалим из полей шаблона
             unset($conds->cstreamsstatus);
        }
        if ( $select = $this->get_select_for_sql($conds) )
        {
            $select = ' AND sch.'.preg_replace('/ AND /',' AND sch.',$select.' ');
            $select = preg_replace('/ OR /',' OR sch.',$select);
            $select = str_replace('sch. (','(sch.',$select);
            $select = str_replace('sch.(','(sch.',$select);
            $sql .= " {$select}";

        }
        $sql .= " ORDER BY ag.name";
        return $this->get_records_sql($sql);
    }    
    
    /** Возвращает список учебных потоков по заданным критериям 
     * слитые с параметрами из таблицы cstreamlinks и cpassed
     * @return array массив записей из базы, или false в случае ошибки
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     */
    public function get_individual_students_list($conds = null)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }

        // возвращаем ту часть массива записей таблицы, которую нужно
        //return $this->get_list_select($select, '', '*', $limitfrom, $limitnum);
        $tblperson = $this->dof->storage('persons')->prefix().$this->dof->storage('persons')->tablename();
        $tbcpassed = $this->dof->storage('cpassed')->prefix().$this->dof->storage('cpassed')->tablename();
        $tblcstreams = $this->dof->storage('cstreams')->prefix().$this->dof->storage('cstreams')->tablename();
        $tbl = $this->prefix().$this->tablename();
        $sql = "SELECT sch.id as schid, pr.*
                FROM {$tbl} as sch, {$tblcstreams} as c, {$tbcpassed} as cp, {$tblperson} as pr
                WHERE sch.cstreamid=c.id AND c.id=cp.cstreamid AND cp.studentid=pr.id 
                AND (cp.agroupid=0 OR cp.agroupid IS NULL)";
        if ( isset($conds->ageid) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('c.ageid',$conds->ageid));
             // удалим из полей шаблона
             unset($conds->ageid);
        }
        if ( isset($conds->cstreamsstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('c.status',$conds->cstreamsstatus));
             // удалим из полей шаблона
             unset($conds->cstreamsstatus);
        }
        if ( isset($conds->cpassedstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cp.status',$conds->cpassedstatus));
             // удалим из полей шаблона
             unset($conds->cpassedstatus);
        }
        if ( $select = $this->get_select_for_sql($conds) )
        {
            $select = ' AND sch.'.preg_replace('/ AND /',' AND sch.',$select.' ');
            $select = preg_replace('/ OR /',' OR sch.',$select);
            $select = str_replace('sch. (','(sch.',$select);
            $select = str_replace('sch.(','(sch.',$select);
            $sql .= " {$select}";

        }
        $sql .= " ORDER BY pr.sortname";
        return $this->get_records_sql($sql);
    }    
    
    /** Возвращает список учебных потоков по заданным критериям 
     * слитые с параметрами из таблицы cstreamlinks и cpassed
     * @return array массив записей из базы, или false в случае ошибки
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     */
    public function get_teachers_list($conds = null)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        // возвращаем ту часть массива записей таблицы, которую нужно
        //return $this->get_list_select($select, '', '*', $limitfrom, $limitnum);
        $tblperson = $this->dof->storage('persons')->prefix().$this->dof->storage('persons')->tablename();
        $tblappoint = $this->dof->storage('appointments')->prefix().$this->dof->storage('appointments')->tablename();
        $tbleagreement = $this->dof->storage('eagreements')->prefix().$this->dof->storage('eagreements')->tablename();
        $tblcstreams = $this->dof->storage('cstreams')->prefix().$this->dof->storage('cstreams')->tablename();
        $tbl = $this->prefix().$this->tablename();
        $sql = "SELECT pr.*
                FROM {$tbl} as sch, {$tblcstreams} as c, {$tblappoint} as ap, 
                {$tbleagreement} as ea, {$tblperson} as pr
                WHERE sch.cstreamid=c.id AND c.appointmentid=ap.id AND 
                ap.eagreementid=ea.id AND ea.personid=pr.id";
        if ( isset($conds->ageid) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('c.ageid',$conds->ageid));
             // удалим из полей шаблона
             unset($conds->ageid);
        }
        if ( isset($conds->cstreamsstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('c.status',$conds->cstreamsstatus));
             // удалим из полей шаблона
             unset($conds->cstreamsstatus);
        }
        if ( isset($conds->appointstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('ap.status',$conds->appointstatus));
             // удалим из полей шаблона
             unset($conds->appointstatus);
        }
        if ( $select = $this->get_select_for_sql($conds) )
        {
            $select = ' AND sch.'.preg_replace('/ AND /',' AND sch.',$select.' ');
            $select = preg_replace('/ OR /',' OR sch.',$select);
            $select = str_replace('sch. (','(sch.',$select);
            $select = str_replace('sch.(','(sch.',$select);
            $sql .= " {$select}";

        }
        $sql .= " ORDER BY pr.sortname";
        return $this->get_records_sql($sql);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_for_sql($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        $cstreamids = array();
        if ( ! isset($conds->cstreamid) )
        {// не был передан id потока - поищем потоки по другим параметрам';
            if ( isset($conds->agroupid) )
            {// ищем записи по академической группе
                if ( $cstreams = $this->dof->storage('cstreamlinks')->get_records(array('agroupid'=>$conds->agroupid), null, 'cstreamid') )
                {// есть записи принадлежащие такой академической группе
                    foreach ( $cstreams as $cstream )
                    {// собираем все cstreamids
                        $cstreamids[] = $cstream->cstreamid;
                    }
                }
            }
            if ( isset($conds->teacherid) )
            {// ищем записи по учителю';
                $cs = new stdClass();
                $cs->teacherid = $conds->teacherid;
                $cs->status = array('plan','active','suspend','completed');
                if ( $cstreams = $this->dof->storage('cstreams')->get_listing($cs) )
                {// есть записи принадлежащие такму учителю
                    foreach ( $cstreams as $cstream )
                    {// собираем все cstreamids';
                        $cstreamids[] = $cstream->id;
                    }
                }
            }
            if ( isset($conds->studentid) )
            {// ищем записи по студенту
                $cs = new stdClass();
                $cs->personid = $conds->studentid;
                $cs->status = array('plan','active','suspend','completed');
                if ( $cstreams = $this->dof->storage('cstreams')->get_listing($cs) )
                {// есть записи принадлежащие такому студенту
                    foreach ( $cstreams as $cstream )
                    {// собираем все cstreamids
                        $cstreamids[] = $cstream->id;
                    }
                }
            }
            if ( (isset($conds->agroupid) OR isset($conds->teacherid) 
                  OR isset($conds->studentid) ) AND empty($cstreamids)  )
            {// передан хоть кто-то из id, но потоки не найдены';
                // составим запрос, который гарантированно вернет false
                return ' cstreamid = -1 ';
            }
            // убираем agroupid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->agroupid);
            // убираем teacherid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->teacherid);
            // убираем studentid из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->studentid);
            $conds->cstreamid = $cstreamids;
        }
        if ( isset($conds->begintime) )
        {
            $selects[] = 'begin >='.$conds->begintime;
            unset($conds->begintime);
        }
        if ( isset($conds->endtime) )
        {
            $selects[] = 'begin <'.$conds->endtime;
            unset($conds->endtime);
        }
        if ( isset($conds->dayvar) AND $conds->dayvar == 0 )
        {// передан выбор на еженедельную неделю
            // обрабатываем отдельнот - query_part_select на такое не настроен, у него настроение портится
            $selects[] = 'dayvar = 0';
            unset($conds->dayvar);
        }
        //четная или нечетная (отобразим ещё и ЕЖЕДНЕВНО)
        if(isset($conds->dayvar)) 
        {
            $selects[] = "(dayvar = 0 OR dayvar = $conds->dayvar)";
            unset($conds->dayvar);
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
    
    
    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        // включен ли плагин
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // размер академического часа (в секундах) - 45 мин
        $config = array();
        $obj = new stdClass();
        $obj->type  = 'text';
        $obj->code  = 'ahourduration';
        $obj->value = '2700';
        $config[$obj->code] = $obj;
        // продолжительность урока по умолчанию (в секундах)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'duration';
        $obj->value = '2700';
        $config[$obj->code] = $obj;
        // тип недели при создании урока (по умолчанию - ежедневно)
        $obj = new stdClass();
        $obj->type = 'select';
        $obj->code = 'dayvar';
        $obj->value = '0';
        $config[$obj->code] = $obj;        
        return $config;
    }    
    
    /**
     * Возвращает массив запрос для пересечения шаблонов по ученикам
     * @param object $obj - запись из таблицы schtemplates 
     * @return array/
     */
    public function get_select_templater_students($obj)
    {   
        // студенты
        if ( ! $students = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$obj->cstreamid,'status'=>array('plan','active'))) )
        {// нет студентов на всякий случай
            return '';
        }
        
        // найдем учителя и проверим его на УЧЕНИКА в др шаблонах в это же время
        if ( $apid = $this->dof->storage('cstreams')->get_field($obj->cstreamid, 'appointmentid') )
        {
            if ( $eaid = $this->dof->storage('appointments')->get_field($apid, 'eagreementid') )
            {
                $prid = $this->dof->storage('eagreements')->get_field($eaid, 'personid');
            }            
        }

        $people = array();
        foreach ( $students as $student )
        {
            $people[] = $student->studentid;
        }
        // дозапишем учителя
        if ( isset($prid) AND $prid )
        {
            $people[] = $prid;
        }
        // преобразуем в строку
        $people = implode(',', $people);
        //Запишем названия таблиц, из которых будем доставать данные
        $templates = $this->prefix().$this->tablename();
        $cpassed = $this->prefix().$this->dof->storage('cpassed')->tablename();  
        $sql = '';
        $end = (int)($obj->begin + $obj->duration);
        // составляем запрос
        $sql .= "SELECT DISTINCT t.* FROM $templates as t, $cpassed as c WHERE t.cstreamid=c.cstreamid ";
        // sql после слова WHERE
        // не показываем шаблон, по которрому уже ищем
        $sql .= " AND t.id<>$obj->id ";
        // день недели        
        $sql .= " AND t.daynum = $obj->daynum ";   
        // учет времени
        $sql .= " AND ((t.begin >= $obj->begin AND t.begin <= $end) OR ((t.begin+t.duration) >= $obj->begin AND (t.begin+t.duration) <=$end))";
        if ( $obj->dayvar )
        {// четно или нечетно
            // а если ежеднево, то туда попадают ВСЕ шаблоны
            $sql .= " AND (t.dayvar = 0 OR t.dayvar = $obj->dayvar)";
        }
        // статусы 
        $sql .= " AND t.status = 'active' AND c.status IN ('plan','active')";
        // 
        $sql .= " AND c.studentid IN ( $people )";
        // учебный год - период
        $ageid = $this->dof->storage('cstreams')->get_field($obj->cstreamid, 'ageid');
        $sql .= " AND c.ageid = $ageid";
        // группировка
        $sql .= " GROUP BY t.id";  
        // выведем записи
        return $this->get_records_sql($sql);
        
    }

    /**
     * Возвращает массив запрос для пересечения шаблонов по учителю
     * @param object $obj - запись из таблицы schtemplates 
     * @return array/
     */
    public function get_select_templater_teachers($obj)
    {   
        // найдем учителя
        if ( ! $apid = $this->dof->storage('cstreams')->get_field($obj->cstreamid, 'appointmentid') )
        {// не знаю как так может быть 
            return '';
        }
        if ( ! $eaid = $this->dof->storage('appointments')->get_field($apid, 'eagreementid') )
        {// не знаю как так может быть 
            return '';
        }
        if ( ! $prid = $this->dof->storage('eagreements')->get_field($eaid, 'personid') )
        {// не знаю как так может быть 
            return '';
        }  
        // студенты
        // тут делаем проверку на то, что ЭТИ студенты не являются учителями в ЭТО же время в др. шаблонах
        $students = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$obj->cstreamid,'status'=>array('plan','active')));
        $people = array();
        $people[] = $prid;
        if ( $students )
        {// есть ученики - учтем и их
            foreach ( $students as $student )
            {
                $people[] = $student->studentid;
            }
        }    
        // преобразуем в строку
        $people = implode(',', $people);        
        
        //Запишем названия таблиц, из которых будем доставать данные
        $template = $this->prefix().$this->tablename();
        $cstream = $this->prefix().$this->dof->storage('cstreams')->tablename();
        $appointment = $this->prefix().$this->dof->storage('appointments')->tablename();
        $eagreement = $this->prefix().$this->dof->storage('eagreements')->tablename();
          
        $sql = '';
        $end = (int)($obj->begin + $obj->duration);
        // составляем запрос
        $sql = "SELECT DISTINCT t.*
                FROM $template as t, $cstream as c, $appointment as ap, $eagreement as ea WHERE";
        // sql после слова WHERE
        // не показываем шаблон, по которрому уже ищем
        $sql .= " t.id<>$obj->id ";
        $sql .= " AND t.cstreamid=c.id AND c.appointmentid=ap.id AND ap.eagreementid=ea.id AND ea.personid IN ( $people ) ";
        // день недели        
        $sql .= " AND t.daynum = $obj->daynum ";   
        // учет времени
        $sql .= " AND ((t.begin >= $obj->begin AND t.begin <= $end) OR ((t.begin+t.duration) >= $obj->begin AND (t.begin+t.duration) <=$end))";
        if ( $obj->dayvar )
        {// четно или нечетно
            // а если ежеднево, то туда попадают ВСЕ шаблоны
            $sql .= " AND (t.dayvar = 0 OR t.dayvar = $obj->dayvar)";
        }
        // статусы, чителей в пересечении пока не нужно учитывать - если он прописан там, 
        // то не важно, что там у него за статус, урок-то нужно вести
        $sql .= " AND t.status = 'active' AND c.status IN ('plan','active') ";
        // учебный год - период
        $ageid = $this->dof->storage('cstreams')->get_field($obj->cstreamid, 'ageid');
        $sql .= " AND c.ageid = $ageid";
        // группировка
        $sql .= " GROUP BY t.id";

        // выведем записи
        return $this->get_records_sql($sql);
        
    }    
    
    /** Функция получения шаблонов для ученика 
     * для определенного(пон, вт,ср..) дня определенной недели(четная/нечетная или ВСЕ)
     *  @param integer $studentid - id студента из табл persons
     *  @param integer $daynum - день недели(1-пон,2-вт,3-ср...)
     *  @param integer $dayvar - тип недели(0-ежедневно,1-четная,2-нечетная) по умолчанию null 
     */
    public function get_templaters_on_day($conds = null, $sort='', $fields='*', $limitfrom = 0, $limitnum = 0)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        // возвращаем ту часть массива записей таблицы, которую нужно
        $tbl = $this->prefix().$this->tablename();
        if ( $fields )
        {// переданы поля, которые следует отобразить
            $fields = 'sch.'.$fields;
            $fields = str_replace(',',', sch.',$fields);
            // необходимые поля из потока
            $fields .= ',cs.ageid';
        }
        $tblcstream = $this->dof->storage('cstreams')->prefix().$this->dof->storage('cstreams')->tablename();
        $tblcpassed  = $this->prefix().$this->dof->storage('cpassed') ->tablename();
        $sql = "SELECT {$fields} FROM {$tbl} as sch, {$tblcstream} as cs, {$tblcpassed} as cpas";
        $sql .= " WHERE sch.cstreamid=cs.id AND cs.id=cpas.cstreamid AND cpas.studentid={$conds->studentid}";
        unset($conds->studentid);
        if ( isset($conds->ageid) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cs.ageid',$conds->ageid));
             // удалим из полей шаблона
             unset($conds->ageid);
        }
        if ( isset($conds->cstreamsstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cs.status',$conds->cstreamsstatus));
             // удалим из полей шаблона
             unset($conds->cstreamsstatus);
        }
        if ( isset($conds->cpassedstatus) )
        {// поле потока - добавим в выборку
             $sql .=' AND '.trim($this->query_part_select('cpas.status',$conds->cpassedstatus));
             // удалим из полей шаблона
             unset($conds->cpassedstatus);
        }
        if ( isset($conds->teacherid) AND $conds->teacherid == 0 )
        {// ищем вакансию - глубоко копать не надо ищем прям так
            $sql .=' AND cs.appointmentid=0';
             // удалим из полей шаблона
             unset($conds->teacherid);
        }
        if ( $select = $this->get_select_for_sql($conds) )
        {// выборка не пустая
            $select = ' AND sch.'.preg_replace('/ AND /',' AND sch.',$select.' ');
            $select = preg_replace('/ OR /',' OR sch.',$select);
            $select = str_replace('sch. (','(sch.',$select);
            $select = str_replace('sch.(','(sch.',$select);
            $sql .= " {$select}";
        }
        if ( ! empty($sort) )
        {// сортировка не пустая
            $sort = 'sch.'.str_replace(',',', sch.',$sort);
            $sql .= " ORDER BY {$sort}";
        }

        return $this->get_records_sql($sql, null, $limitfrom, $limitnum);
    } 
    
    /** Перевести время урока во время пользователя
     * @param int $lessontime
     * 
     * @return int
     */
    public function lessontime_to_usertime($lessontime)
    {
        
    }
    
    /** Перевести время пользователя во время урока
     * @param int $usertime - время урока (в секундах) в часовом поясе пользователя (от 0 до 24*3600)
     * 
     * @return int - время начала или окончания урока (вместе со смещением относительно пользователя)
     *               (в секундах от 0 до 24*3600)
     */
    public function usertime_to_lessontime($usertime, $timezone=99)
    {
        
        $userhours    = floor(($usertime) / 3600);
        $userminutes  = floor(($usertime  - $userhours * 3600) / 60);
        //echo $userhours.':'.$userminutes;
        $hours = intval($userhours) * 2 -intval($this->dof->storage('persons')->
            get_userdate(mktime($userhours, $userminutes),"%H"));
        
        if ($hours < 0 )
        {
            $hours = 24 + $hours;
        }     
        if ($hours > 24 )
        {
            $hours = $hours - 24;
        }         
        $lessontime = ($hours) * 3600 + 
                      (intval($userminutes) * 2 -
                      intval($this->dof->storage('persons')->get_userdate(mktime($userhours,
                      $userminutes),"%M"))) * 60;
        if ($lessontime < 0 )
        {
            $lessontime = 24 * 3600 - $lessontime;
        }
        if ($lessontime > 24 * 3600 )
        {
            $lessontime = $lessontime - 24 * 3600;
        }
        return $lessontime;
    }
} 
?>