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
// подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Справочник учебных периодов
 * 
 */
class dof_storage_ages extends dof_storage implements dof_storage_config_interface
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
    
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        
        $result = true;
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2014050500)
        {
            // добавляем поле метаконтрактов
            $field = new xmldb_field('schdays',XMLDB_TYPE_INTEGER, '5', 
                    null, XMLDB_NOTNULL, null, '7', 'status'); 
            if ( !$dbman->field_exists($table, $field) )
            {// если поле еще не установлено
                $dbman->add_field($table, $field);
                               
            }
            // добавляем индекс к полю
            $index = new xmldb_index('ischdays', XMLDB_INDEX_NOTUNIQUE, 
                    array('schdays'));
            if (!$dbman->index_exists($table, $index)) 
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            // добавляем поле метаконтрактов
            $field = new xmldb_field('schedudays',XMLDB_TYPE_CHAR, '255', 
                    null, XMLDB_NOTNULL, null, '1,2,3,4,5', 'schdays'); 
            if ( !$dbman->field_exists($table, $field) )
            {// если поле еще не установлено
                $dbman->add_field($table, $field);
                               
            }
            // добавляем индекс к полю
            $index = new xmldb_index('ischedudays', XMLDB_INDEX_NOTUNIQUE, 
                    array('schedudays'));
            if (!$dbman->index_exists($table, $index)) 
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
            // добавляем поле метаконтрактов
            $field = new xmldb_field('schstartdaynum',XMLDB_TYPE_INTEGER, '5', 
                    null, XMLDB_NOTNULL, null, 1, 'schedudays'); 
            if ( !$dbman->field_exists($table, $field) )
            {// если поле еще не установлено
                $dbman->add_field($table, $field);
                               
            }
            // добавляем индекс к полю
            $index = new xmldb_index('ischstartdaynum', XMLDB_INDEX_NOTUNIQUE, 
                    array('schstartdaynum'));
            if (!$dbman->index_exists($table, $index)) 
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }   
            $num = 0;
            while ( $list = $this->get_records(null,'','*',$num,100) )
            {// выбираем все периоды
                foreach ($list as $age)
                {
                    $obj = new stdClass;
                    $daynumber = dof_userdate($age->begindate,'%w');
                    if ( $daynumber == '0' )
                    {// %w - возвращает 0 для воскресенья, переправим на 7
                        $daynumber = 7;
                    }
                    $obj->schstartdaynum = $daynumber;
                    $this->update($obj,$age->id);
                } 
                $num++;              
            }
        }
        // обновляем полномочия, если они изменились
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2016070502;
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
        return 'ages';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('departments' => 2009040800,
                                      'acl'         => 2011041800,
                                      'cov'         => 2014032000,
                                      'config'      => 2011080900));
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
                                      'cov'   => 2014032000,
                                      'config'=> 2011092700));
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
        // Просим запускать крон не чаще раза в 15 минут
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
        return 'block_dof_s_ages';
    }

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************   
    
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
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->objectid     = $objectid;
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
        
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']   = array('roles'=>array('manager','methodist'));
        $a['edit']   = array('roles'=>array('manager'));
        $a['create'] = array('roles'=>array('manager'));
        $a['delete'] = array('roles'=>array());
        $a['use']    = array('roles'=>array('manager','methodist'));
        
        return $a;
    }
    
    /** Функция получения настроек для плагина
     * @todo добавить обработку настроек schdaysdefault, schedudaysdefault,
     *  schstartdaynumdefault для подразделений
     */
    public function config_default($code=null)
    {
        // плагин включен и используется
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Максимально разрешенное количество объектов этого типа в базе
        // (указывается индивидуально для каждого подразделения)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'objectlimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;
        // Количество дней в учебной неделе
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'schdaysdefault';
        $obj->value = '7';
        $config[$obj->code] = $obj;
        // Список учебных дней в учебной неделе
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'schedudaysdefault';
        $obj->value = '1,2,3,4,5';
        $config[$obj->code] = $obj;
        // Номер первого дня в периоде (по умолчанию - пустое; если оставить
        //  пустым, при сохранении туда подставится номер дня недели на дату
        //  начала периода, по календарю)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'schstartdaynumdefault';
        $obj->value = '';
        $config[$obj->code] = $obj;
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************

    /** Получить фрагмент списка учебных периодов для вывода таблицы 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds - список параметров для выборки периодов 
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( is_null($conds) )
        {// если список периодов не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object) $conds;
        if ( $limitnum <= 0 AND ! is_null($limitnum) )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault(); 
        }
        if ( $limitfrom < 0 AND ! is_null($limitfrom) )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        // посчитаем общее количество записей, которые нужно извлечь
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($select);
        }
        // добавим сортировку
        // сортировка из других таблиц - пока не имеется
        $outsort = '';
        $sort = $this->get_orderby_listing($sort);
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->get_records_select($select,null,$sort,$fields,$limitfrom,$limitnum);
    }
    
    /** 
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @param string $prefix - префикс к полям, если запрос составляется для нескольких таблиц
     * @return string
     */
    public function get_select_listing($inputconds,$prefix='')
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        $conds = (object) $conds;
        if ( ! empty($conds->name) )
        {// для имени используем шаблон LIKE
            $selects[] = " name LIKE '%".$conds->name."%' ";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->name);
        }
        if ( ! empty($conds->noid) )
        {// для имени используем шаблон LIKE
            $selects[] = " id != ".$conds->noid;
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->noid);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->query_part_select($prefix.$name,$field);
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
    
    /** Возвращает фрагмент sql-запроса c ORDER BY
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_orderby_listing($sort,$prefix='')
    {
        // по-умолчанию имя
        $sqlsort = $prefix.'name';
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
        $selects[] = $prefix.'name '.$dir;
        // возвращаем сортировку
        return implode($selects,',');
    }
    
    
    /**
     * Возвращает массив текущих периодов
     * 
     * @param $depid - id подразделения, для которого возвращаем массив текущих периодов
     * @return array - массив периодов
     */
    public function get_current_ages($depid = null)
    {
        // Получаем текущую дату
        $date = date('U');
        
        $params = array($date, $date);
        $select = 'begindate < ? AND enddate > ?';
        if ( ! empty($depid) )
        {
            $select .= ' AND departmentid = ? ';
            $params[] = $depid;
        }
        
        if ( ! $ages = $this->get_records_select($select, $params) )
        {//не нашли периоды;
            return false;
        }
        
        return $ages;
    }
    
    /** Возвращает один из дочерних учебных периодов
     * 
     * @return int - id периода или false, если установить id не удалось
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $agenum - сколько периодов вперед отсчитать 
     * относительно переданного ageid.
     * При этом переданный id считается первым.
     */
    public function get_next_ageid($ageid, $agenum)
    {
        if ( $ageid == 0)
        {// переданный id не может быть равен 0
            return false;
        }
        $agenum = (int)$agenum;
        $age = new stdClass();
        $age->id = (int)$ageid;
        for ($i=2; $i<=$agenum; $i++)
        {//последовательно перебираем периоды до нужного
            if ( ! $age = $this->get_record(array('previousid'=>$age->id)) )
            {//не нашли дочерний период';
                return false;
            }
        }
        return $age->id;
    }
    
    /** Возвращает предшествующий указанному в ageid учебный период 
     * 
     * @return int|bool - id периода или false, если установить id не удалось
     * @param int $ageid - id учебного периода в таблице ages
     * @param int $agenum - сколько периодов назад отсчитать 
     * относительно переданного ageid.
     * При этом переданный ageid считается последним.
     */
    public function get_previous_ageid($ageid, $agenum)
    {
        $agenum = (int)$agenum;
        $age = new stdClass();
        $age->previousid = (int)$ageid;
        
        for ( $i=2; $i<=$agenum; $i++ )
        {// последовательно ищем предыдущий период
            if ( ! $age = $this->get($age->previousid) )
            {// указанный период не найден
                return false;
            }
        }
        // возвращаем id нужного периода
        return $age->previousid;
    }
    
    /** Находит порядковый номер параллели, считая от заданного периода
     * @param int $startageid - id периода на котором нужно остановить поиск.
     * @param int $currentageid - id периода, порядковый номер которого нам надо узнать
     * @param int $maxagenum - максимально возможный номер agenum 
     * @return mixed int - agenum или bool false
     */
    public function get_agenum_byageid($startageid, $currentageid, $maxagenum)
    {
        //подстрахуемся от бесконечного цикла
        $maxagenum = (int)$maxagenum;
        //будет хранить номер текущего периода
        $agenum = 0;
        //имитируем, будто текущий период мы получили
        $age = new stdClass();
        $age->previousid = $currentageid;
        while ($agenum < $maxagenum )
        {
            //получаем текущий период
            if ( ! $age = $this->get($age->previousid) )
            {//не получили период';
                return false;
            }
            //увеличиваем порядковый номер периода
            $agenum++;
            if ( $startageid == $age->id )
            {//найден первый период';
                return $agenum;
            }
        }
        //перебрали все периоды, а самый первый не нашли';
        return false;
    }
    
    /**
     * Возвращает массив дней в учебной неделе
     * 
     * @param object|int $age - Период или его ID
     * @param bool $alias - Использовать алиасы дней, если они установлены для периода
     * 
     * @return array - масив дней 
     */
    public function get_list_daynums($age, $alias = false)
    {
        if ( ! is_object($age) )
        {//если передан не период, а его id
            if ( ! $age = $this->get($age) )
            { // Не получили период - дней нет
                return array();
            }
        }
        
        // Формируем массив дней учебной недели 
        $daynums = array();
        
        // Первая часть массива
        for ( $i = 1; $i <= $age->schdays; $i++ )
        {
            $daynums[$i] = "$i";
        }
        
        // Замена номеров дней на алиасы
        if ( $alias )
        {
            $aliases = $this->get_daynums_aliases($age->id);
            foreach ( $daynums as $key => &$day )
            {
                if ( isset($aliases[$key]) )
                {
                    $daynums[$key] = $aliases[$key];
                }
            }
            // Всегда очищаем за собой переменные со ссылками
            unset ($day); 
        }
        return $daynums;
    }
    
    /**
     * Возвращает массив алиасов
     *
     * @param int $ageid - ID учебного периода
     *
     * @return array - масив алиасов [номер дня] => алиас
     */
    public function get_daynums_aliases($ageid)
    {
        // Получим опцию использования стандартных названий дней недели
        $useweekdaynames = $this->get_custom_option('useweekdaynames', $ageid);
    
        // Получим период
        $age = $this->get($ageid);
        if ( empty($age) )
        {// Нет такого учебного периода
            return false;
        }
        
        // Сформируем алиасы
        $aliases = array();
        for ($day = 1; $day <= $age->schdays; $day++ )
        {
            $aliases[$day] = $day;
        }
        if ( $useweekdaynames )
        {// У периода установлена настройка использования стандартных алиасов
            $aliases['1'] = $this->dof->modlib('ig')->igs('monday');
            $aliases['2'] = $this->dof->modlib('ig')->igs('tuesday');
            $aliases['3'] = $this->dof->modlib('ig')->igs('wednesday');
            $aliases['4'] = $this->dof->modlib('ig')->igs('thursday');;
            $aliases['5'] = $this->dof->modlib('ig')->igs('friday');
            $aliases['6'] = $this->dof->modlib('ig')->igs('satuday');
            $aliases['7'] = $this->dof->modlib('ig')->igs('sunday');
        }
        
        return $aliases;
    }
    
    /**
     * Получить дополнительную настройку для учебного периода из справочника cov
     * 
     * @param string $name - опция, значение которой надо получить
     * @param int $id - ID учебного периода
     * @param $options - дополнительные свойства, список предоставлен в описании
     *                   метода get_option плагина cov
     * 
     * @return mixed - значение запрашиваемой настройки, значение при ошибке устанавливается в опциях
     */
    public function get_custom_option($name, $id, $options = array() )
    {
        // Получим опцию
        return $this->dof->storage('cov')->get_option('storage', 'ages', $id, $name, NULL, $options);
    }
    
    /**
     * Сохранить дополнительную настройку для учебного периода из справочника cov
     *
     * @param string $name - имя опции
     * @param int $id - ID учебного периода
     * @param string $value - значение опции
     *
     * @return mixed - значение запрашиваемой настройки, значение при ошибке устанавливается в опциях
     */
    public function save_custom_option($name, $id, $value = NULL)
    {
        // Получим опцию
        return $this->dof->storage('cov')->save_option('storage', 'ages', $id, $name, $value);
    }
    
    // **********************************************
    //              Устаревшие методы
    // **********************************************
    
    /** Создать период для структурного подразделения
     * 
     * @return int id созданного периода или false
     * @param int $deptid - id учебного подразделения
     * @param int $datebegin - время начала периода в формате unixtime
     * @param int $dateend - время окончания периода в формате unixtime
     * @param int $numweeks - количество недель в учебном периоде
     * @param string $name - название учебного периода
     * @param int $previosid[optional] - id предыдущего учебного периода
     */
    public function create_period_for_department($deptid, $datebegin, $dateend, $numweeks, $name, $previousid=null)
    {
        dof_debugging('storage/create_period_for_department. Метод нигде не использовался и был признан устаревшим', DEBUG_DEVELOPER);
    	$age = new stdClass();
    	$age->name = $name;
    	$age->begindate = $datebegin;
    	$age->enddate = $dateend;
    	$age->eduweeks = $numweeks;
    	$age->departmentid = $deptid;
    	if ( isset($previousid) AND $this->get_next_ageid($previousid,2) )
    	{// если указан предыдущий период и у него уже есть последующие
    		// период создавать нельзя
    		return false;
    	}
    	// добавляем предыдущий период в БД
    	$age->previousid = $previousid;
    	// сохраняем запись в БД
    	return $this->insert($age);
    }

    
} 
?>