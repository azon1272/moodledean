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

/** Справочник учебных программ
 * 
 */
class dof_storage_eagreements extends dof_storage implements dof_storage_config_interface
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
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG;
        $result = true;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
        
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2012052800;
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
        return 'eagreements';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('persons'     => 2009060400,
                                      'departments' => 2010022700,
                                      'acl'         => 2011041800,
                                      'config'      => 2011080900 ) );
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
        return 'block_dof_s_eagreements';
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
      
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']     = array('roles'=>array('manager','methodist'));
        $a['edit']     = array('roles'=>array('manager'));
        // право редактировать номер договора
        $a['edit:num'] = array('roles'=>array('manager'));
        
        $a['use']      = array('roles'=>array('manager','methodist'));
        $a['create']   = array('roles'=>array('manager'));
        $a['delete']   = array('roles'=>array());

        return $a;
    }
    
    /** Функция получения настроек для плагина
     *  
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
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    /** Вставляет запись в таблицу(ы) плагина 
     * @param object dataobject 
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     */
    public function insert($dataobject,$quiet=false)
    {
        // Проверняем код на уникальность
        if ( ! empty($dataobject->num) )
        {// код указывался вручную
            $num = $dataobject->num;
            $manualnum = true;
            $i = 1;
            
            while ( $this->is_exists(array('num'=>$dataobject->num)) )
            {   // Если код не уникален - расширяем
                $dataobject->num = $num.'_'.$i;
                ++$i;
            }
        } else
        {// код нужно сгенерировать автоматически
            $num = time();
            $manualnum = false;
        }
        

        
        // Добавляем текущее время
        $dataobject->adddate = time();
        // Вызываем метод из родительского класса
        if ( $id = parent::insert($dataobject,$quiet) )
        {
            // Устанавливаем номер контракта по номеру записи в БД
            $obj = new stdClass();
            $obj->id  = intval($id);
            if ( !$manualnum )
            {// установливаем только в том случае если он не был установлен вручную
                $obj->num = sprintf('%06d',$id).'/'.date('y',$dataobject->adddate);
            }
            $this->update($obj);
            return $id; 
        }else
        {
            return false;
        }
    }
    
    /** Получить список записей из таблицы по указанным парпметрам для отображения списка
     * 
     * @return array|int
     * @param int $limitfrom - номер записи с которой начинается выборка
     * @param int $limitnum - количество записей, которое нужно извлечь
     * @param object $conds[optional] - условия выборки
     * @param bool $countonly[optional] - только вернуть количество
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object) $conds;
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        if ( ! isset($conds->orderby) )
        {
            $sort = 'ASC';
        }else 
        {
            $sort = $conds->orderby;
            unset($conds->orderby);
        }
        // Готовим SQL-запрос
        $tblpersons = $this->dof->storage('persons')->prefix().$this->dof->storage('persons')->tablename();
        $tbleagreements = $this->prefix().$this->tablename();
        // формируем строку запроса
        $select = $this->get_select_listing($conds,$tbleagreements.'.');
        if (strlen($select)>0)
        {
            $select .= ' AND ';
        }
        $sql = " FROM {$tbleagreements},{$tblpersons}"
            ." WHERE {$select} {$tbleagreements}.personid = {$tblpersons}.id";
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_sql("SELECT COUNT(*) {$sql}");
        }
        $sql = "SELECT {$tblpersons}.*,{$tbleagreements}.*" 
            .$sql." ORDER BY {$tblpersons}.sortname ".$sort;
        // возвращаем ту часть массива записей таблицы, которую нужно
        // return $this->get_list_select($select, 'num ASC', '*', $limitfrom, $limitnum);
        return $this->get_records_sql($sql,null,$limitfrom, $limitnum);
    }
    
    /** Получить sql-запрос для поиска по перечисленным пареметрам
     * 
     * @return string - текст запроса
     * @param object $conds - условия для составления запроса
     * @param string $prefix - префикс к полям, если запрос составляется для нескольких таблиц
     */
    private function get_select_listing($inputconds,$prefix='')
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->query_part_select($prefix.$name, $field);
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
    
    
    
    /** Возвращает договора персоны отсортированных по наибольшиму значению adddate
     * @param int $personid - id персоны
     * @param string $sort - параметры сортировки
     * @param int $limitnum - количество возвращаемых договоров
     * @param int $limitfrom - с какого договора возвратить
     * @return mixed array - договора сотрудника или bool false
     * 
     * @deprecated
     */
    public function get_eagreements($personid, $sort='adddate DESC', $limitfrom=0, $limitnum=1)
    {
        dof_debugging('call to deprecated method get_eagreements()', DEBUG_DEVELOPER);
        if ( ! is_int_string($personid) )
        {//входные данные неверного формата 
            return false;
        }
        return $this->get_list('personid', $personid, 
                         $sort, '*', $limitfrom, $limitnum);

    } 
    
    /** Если пользователь упомянут в контракте или он учитель либо админ - вернем true
     * @param int $mdluser - id пользователя в moodle
     * @param bool $except - id контракта, который надо исключить из поиска
     * @param string $where - идентификатор происхождения id пользователя 
     * mоodle - id из таблицы mdl_user, fdo - из таблицы persons 
     * @return bool
     */
    public function is_personel($userid, $except=null, $where = 'moodle' )
    {
        
        if ( 'moodle' == $where )
        {//передан id пользователя в moodle
            $mdluser = $userid;
            // найдем пользователя деканата
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id($mdluser) )
            {// пользователь не найден - укажем невозможный id
                $personid = -1;
            }
        }elseif ( 'fdo' == $where )
        {//передан id пользователя в деканате
            $personid = $userid;
            // найдем пользователя Moodle
            if ( ! $mdluser = $this->dof->storage('persons')->get_field($userid, 'mdluser') )
            {//пользователь не найден - укажем невозможный id 
                $mdluser = 0;
            }
        }else
        {
            return false;
        }
        //если пользователь упомянут в контракте или он учитель либо админ - вернем true
        return $this->dof->modlib('ama')->user(false)->is_teacher($mdluser) OR 
               $this->is_person_used($personid, $except) OR 
               $this->dof->storage('contracts')->is_person_used($personid);
    }  

    /** Есть ли другие активные договора, где используется учетная запись
     * @param int $pid - id пользователя 
     * @param int $except - id контракта, который надо исключить из поиска
     * @return bool
     */
    public function is_person_used($pid,$except=null)
    {
        $pid = (int) $pid;
        $select = " personid={$pid} AND status<>'cancel' ";
        if ($except)
        {   // Задан контракт, который нужно исключить
            $except = (int) $except;
            $select .= " AND id<>{$except}";
        }
        
        return (bool) $this->count_records_select($select);
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
            case 'list_eagreements': return $this->widgets_list_form_eagreements($depid, $data);
            default: return array(0 => $this->dof->modlib('ig')->igs('choose'));
        }
    }
    /** Получить список договоров для выпадающего списка
     * @param int $depid - подразделение, из которого надо извлечь договора
     * @param string $lastname - первые несколько букв фамилии сотрудника
     *                       или первые несколько цифр договора с сотрудником
     */
    protected function widgets_list_form_eagreements($depid, $data)
    {
        global $USER;
        $data = clean_param($data, PARAM_TEXT);
        
        $result = array();
        $params = array();
        $where  = array();
        $eagreements = $this->prefix().$this->tablename();
        
        if ( is_numeric($data) )
        {// Поиск по договору
            $where[] = " num LIKE '%".$data."%' ";
        }else
        {// Поиск по ФИО
            $where[] = " lastname LIKE '".$data."%' ";
        }
        // ищем только активные договоры
        $where[] = " (".$eagreements.".status = :status ) ";
        $params['status'] = 'active';
        
        $where = implode(' AND ', $where);
        if ( ! $records = $this->get_eagreements_with_fio($where, $params, 'lastname ASC', 0, 15) )
        {// нет ни одного человека с такой фамилией
            return array();
        }
        
        foreach ( $records as $id => $record )
        {// ключами массива являются id договоров с сотрудниками
            if ( $this->is_access('use', $id, $USER->id, $depid) )
            {
                $obj = new stdClass;
                $obj->id   = $id;
                $fullname = $this->dof->storage('persons')->get_fullname($record);
                $obj->name = "$fullname [$record->num]";
            }
            
            $result[] = $obj;
        }
        
        return $result;
    }
    /** Получить список договоров с сотрудниками, отсортированный по ФИО сотрудников
     * @param string|array $conditions - Фрагмент SQL-запроса, после слова WHERE (не включая само слово)
     *                                   или массив в формате (поле=>значение), если нужно сделать выборку
     *                                   по нескольким критериям таблицы eagreements
     * @param array $params[optional] - массив параметров для sql-запроса, если вы передаете условия запроса строкой
     *                                  (Для макроподстановок. Подробнее - см. документацию класса storage_base)
     * @param string $sort[optional] - по какому полю сортировать? (по умолчанию сортируется по фамилии сотрудника)
     * @param int $limitfrom[optional]
     * @param int $limitfrom[optional]
     * 
     * @return array - массив записей из таблицы eagreements с дополнительными полями firstname, lastname и middlename 
     */
    public function get_eagreements_with_fio($conditions, $params=null, $sort='lastname ASC', $limitfrom=0, $limitnum=0)
    {
        $eagreements = $this->prefix().$this->tablename();
        $persons     = $this->prefix().$this->dof->storage('persons')->tablename();
        
        if ( is_array($conditions) )
        {// условия пришли массивом - сделаем из него SQL
            $where  = array();
            $params = array();
            foreach ( $conditions as $field=>$value )
            {// составляем запрос с использованием макроподстановок
                if ( $field == 'departmentid' OR $field == 'status' OR $field == 'adddate' )
                {// условие для предотвращения ошибки в запросе
                    // Если в запросе указывается подразделение 
                    // (или другое поле, присутствующее в двух таблицах одновременно) 
                    // то мы всегда считаем, что имеется в виду поле договора, а не сотрудника
                    $field = $eagreements.'.'.$field;
                }
                if ( is_array($value) )
                {// если производится поиск по списку параметров (например статусы)
                    $where[]  = ' ( '.$field.' IN (?) ) ';
                    $params[] = "'".implode("', '", $value)."'";
                }else
                {
                    $where[]  = ' ( '.$field.' = "?" ) ';
                    $params[] = $value;
                }
            }
            $where = implode(' AND ', $where);
        }else
        {// условия пришли строкой - подставляем ее в запрос как есть
            $where = $conditions;
        }
        
        // Делаем запрос по 2 таблицам persons и eagreements, пристыковываем ФИО пользователей к договорам
        $sql = 'SELECT '.$eagreements.'.*, '.
                   $persons.'.firstname, '.$persons.'.lastname, '.$persons.'.middlename FROM '.$persons.
                   ' RIGHT JOIN '.$eagreements.' ON '.$persons.'.id = '.$eagreements.'.personid ';
        
        if ( $where )
        {// а тут еще и условия
            $sql .= ' WHERE '.$where;
        }
        if ( $sort )
        {// и сортировка
            $sql .= ' ORDER BY '.$sort;
        }
        
        return $this->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }
}