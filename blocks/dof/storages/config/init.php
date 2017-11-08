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

// подключим интерфейс
require_once 'config_default.php';

/** Настройки плагинов
 * 
 */
class dof_storage_config extends dof_storage
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2012030600) 
        {//удалим enum поля
            // для поля plugintype
            if ( $this->dof->moodle_version() <= 2011120511 )
            {
                $field = new xmldb_field('plugintype', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'value');
                $dbman->drop_enum_from_field($table, $field);
                // для поля noextend
                $field = new xmldb_field('noextend', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'plugincode');
                $dbman->drop_enum_from_field($table, $field);
            }
        }
        return true;// уже установлена самая свежая версия
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2012042500;
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
        return 'config';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array();
    }
    
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype' => 'core', 'plugincode' => 'core', 'eventcode' => 'plugin_install'),
                     array('plugintype' => 'core', 'plugincode' => 'core', 'eventcode' => 'plugin_upgrade'),
                     array('plugintype' => 'core', 'plugincode' => 'core', 'eventcode' => 'plugin_uninstall'));
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
    public function is_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
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
        
        if ( $gentype === 'core' AND $gencode === 'core' )
        {
            $inst_func = array('plugin_install' => 'install_plugin',
                               'plugin_upgrade' => 'upgrade_plugin');
            switch($eventcode)
            {
                case 'plugin_install':
                case 'plugin_upgrade':
                    // нужно записать/обновить/удалить настройки при установке/обновлении/удалении плагина
                    $plugin = $mixedvar['new'];
                    
                    if ( $this->plugin_has_config($plugin->type, $plugin->code) )
                    {// если плагин содержит в себе список настроек
                        $confdata = $this->dof->{$plugin->type}($plugin->code)->config_default();
                        return $this->{$inst_func[$eventcode].'_config'}($plugin->type, $plugin->code, $confdata);
                    }
                break;
                case 'plugin_delete':
                    $plugin = $mixedvar['old'];
                    if ( $this->plugin_has_config($plugin->type, $plugin->code) )
                    {// если плагин содержит в себе список настроек
                        return $this->delete_plugin_config($plugin->type, $plugin->code);
                    }
                break;
            }
        }
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
        return 'block_dof_s_config';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************

   
    /** Возвращает настройки заданного плагина
     * Если не задан плагин, пользователь или подразделение - вернёт настройки
     * по умолчанию для соответствующего поля
     * @param string $configcode - код настройки
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @param int $departmentid - id подразделения, по умолчанию 0
     * @param int $personid - id пользователя, по умолчанию 0
     * @return object - настройка, соответствующая запросу
     */
    public function get_config($configcode, $plugintype, $plugincode, $departmentid=0, $personid=0)
    {
        // Проверка данных
        if( ! $this->dof->plugin_exists($plugintype, $plugincode) )
        {
            return null;
        }
        // Составим SQL запрос
        $configstorage = $this->prefix().
                         $this->tablename();
        $departmentsstorage = $this->dof->storage('departments')->prefix().
                              $this->dof->storage('departments')->tablename(); 
        // Общие условия поиска
        $where = " WHERE ".$configstorage.".code = '".$configcode.
            "' AND ".$configstorage.".plugintype = '".$plugintype.
            "' AND ".$configstorage.".plugincode = '".$plugincode."'";
        if( $departmentid )
        {// Если требуется найти настройки для конкретного подразделения
            $deppath = $this->dof->storage('departments')->get_field($departmentid,'path');
            $departmentid = str_replace('/',',',$deppath);
            $from = " FROM ".$departmentsstorage.", ".$configstorage;
            $where .= " AND ".$configstorage.".departmentid=".$departmentsstorage.".id".
                  " AND ".$departmentsstorage.".id IN (".$departmentid.")";
            $order = " ORDER BY ".$departmentsstorage.".depth DESC";
        }else
        {// Если требуется найти общие настройки
            $from = " FROM ".$configstorage;
            $where .= " AND ".$configstorage.".departmentid = '0'";
            $order = '';
        }
        $where .= " AND ".$configstorage.".personid = '".$personid."' ";
        $select = "SELECT ".$configstorage.".*".$from.$where.$order;
        // Передали персону, но не нашли результат - ищем для ВСЕХ персон
        if( ! $result = $this->get_records_sql($select,null,0,1) AND $personid ) 
        {
            $result = $this->get_config($configcode, $plugintype, $plugincode, $departmentid);
        }
        // Передали подразделение, но не нашли результат - ищем для ВСЕХ подразделений(настройки по умолчанию)
        if( ! $result AND ! $result = $this->get_records_sql($select,null,0,1) AND $departmentid ) 
        {
            $result = $this->get_config($configcode, $plugintype, $plugincode);
        }
        
        // ПОКА мы не знаем, что делать с NOEXTAND и потому закомитили 
        if( ! is_array($result) )
        {
            if ( $result )
            {/*
                if ( $result->noextend == '1' AND $result->departmentid != $departmentid )
                {// запрет наследования - нам не подходит 
                    return null;
                }*/
                return $result;
            }    
            // на случай когда result = false
            return null;
        }
        foreach($result as $res)
        {/*
            if ( $res->noextend == '1' AND $res->departmentid != $departmentid )
            {// запрет наследования - нам не подходит 
                return null;
            }*/
            return $res;
        }
    }
    
    /** Получить только значение указанной настройки
     * Если не задан плагин, пользователь или подразделение - вернёт настройки
     * по умолчанию для соответствующего поля
     * @param string $configcode - код настройки
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @param int $departmentid - id подразделения, по умолчанию 0
     * @param int $personid - id пользователя, по умолчанию 0
     * @return string - значение настройки
     */
    public function get_config_value($configcode, $plugintype, $plugincode, $departmentid=0, $personid=0)
    {
        $config = $this->get_config($configcode, $plugintype, $plugincode, $departmentid, $personid);
        if ( ! is_object($config) )
        {
            return false;
        }
        
        return $config->value;
    }

    /** Возвращает настройки для заданного подразделения
     * @param int $departmentid - id подразделения
     * @param array $addvars - условия сортировки
     * @return array - список настроек
     */
    public function get_config_list_by_department($departmentid, $order_by='')
    {
        // Составим SQL запрос
        // Общие условия поиска
        $select = "departmentid";
        if( $departmentid )
        {// Если требуется найти настройки для конкретного подразделения
            $depstring = $this->dof->storage('departments')->change_path_department($departmentid);
            
            $select .= " IN (".$depstring.",0) ";
        }else
        {// Если требуется найти общие настройки
            $select .= "='0' ";
        }
        // это для того, чтоб вывести натсройки по глубине пути
        
        $configstorage = $this->prefix().
                         $this->tablename();
        $departmentsstorage = $this->dof->storage('departments')->prefix().
                              $this->dof->storage('departments')->tablename(); 
        // сортировка
        switch ($order_by)
        {
            case 'id' : 
                $order_by = "ORDER BY ".$configstorage.".id DESC";
                break;
            case 'code' : 
                $order_by = "ORDER BY ".$configstorage.".code DESC,".$configstorage.".plugintype DESC,".$configstorage.".plugincode DESC,".$departmentsstorage.".depth DESC";
                break;       
            case 'order_by':            
                $order_by = "ORDER BY ".$configstorage.".plugintype DESC,".$configstorage.".plugincode DESC,".$departmentsstorage.".depth DESC";
                break;
            default: $order_by = "ORDER BY ".$configstorage.'.plugintype DESC,'.$configstorage.'.plugincode DESC';   
        }     
        $select = "select ". $configstorage.".* from ".$departmentsstorage." RIGHT JOIN ".$configstorage." 
                    ON ".$configstorage.".departmentid=".$departmentsstorage.".id where  ".$configstorage.".".$select.$order_by;  
        $result = $this->get_records_sql($select);
        $result = array_reverse($result);
        // Обрабатываем запрос
        return $result;
    }

    /** Обновляет настройки заданного плагина
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @param array $configdata - список настроек (формат задаётся функцией config_default)
     * @return bool - true, если всё получилось, false, если что-то пошло не так
     */
    public function upgrade_plugin_config($plugintype, $plugincode, $configdata)
    {
        //Сюда будем записывать настройки, которые нужно обновить
        $update = array();
        //Сюда будем записывать настройки, которые нужно удалить
        $delete = array();
        if ( ( $list = $this->get_records(array('plugintype'=>$plugintype,
                                                'plugincode'=>$plugincode,
                                                'departmentid'=>0) )
                   AND is_array($list) ) )
        {// Нашли настройки, обработаем их
            foreach($list as $setting)
            {
                if ( isset( $configdata[$setting->code] ) )
                {
                    //Если настройка существует в списке новых настроек
                    $flag = true;
                    foreach( $configdata[$setting->code] as $field => $value )
                    {
                        $flag = ( $flag AND ($setting->$field == $value) );
                    }
                    if ( ! $flag )
                    {// Если были найдены различия между старой и новой настройками, запишем более новую 
                        $update[$setting->id] = $configdata[$setting->code];
                    }
                }else
                {
                    $delete[] = $setting->id;
                }
                unset($configdata[$setting->code]);
            }
        }
        $flag = true;
        if ( $configdata )
        {// Все настройки из списка новых настроек, которых не было среди старых, запишем в справочник
            foreach($configdata as $record)
            {// В зависимости от формата $configdata, возможно, прийдётся перезаписать пару полей
                $record->plugintype = $plugintype;
                $record->plugincode = $plugincode; 
                $record->departmentid = 0;
                $record->personid = 0;
                $flag = ( $flag AND (bool)$this->insert($record) );
            }
        }
        if ( $update )
        {// Обновим старые настройки
            foreach($update as $key => $record)
            {
                $flag = ( $flag AND $this->update($record, $key) );
            }
        }
        if ( $delete )
        {// Удалим устаревшие настройки
            foreach($delete as $record)
            {
                $flag = ( $flag AND $this->delete($record) );
            }
        }
        return $flag;
    }

    /** Функция проверяет, реализует ли плагин интерфейс настроек
     *    и если реализует - удаляет все настройки, связанные с этим плагином
     * @param string $plugintype - тип плагина 
     * @param string $plugincode - код плагтна
     * @return bool true - успех
     */
    public function delete_plugin_config($plugintype, $plugincode)
    {// ищем настройки
        if ( $configs = $this->get_records(array('plugintype'=>$plugintype, 'plugincode'=>$plugincode)) )
        {
            foreach ( $configs as $config=>$obj )
            {
                $this->delete($obj->id);    
            }
        }
        return true;
    }

    /** Функция добавляет новые настройки при установке нового плагина
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @param object $configdata - данные из функции config_default(массив объектов)
     * @return bool true - успех
     */
    protected function install_plugin_config($plugintype, $plugincode, $configdata)
    {
        foreach ( $configdata as $code=>$config )
        {// перебираем все настройки
            if ( ! isset($config->code) OR ! isset($config->type) )
            {// пропускаем настройки у которых не указаны обязательные поля
                continue;
            }
            $obj = new stdClass();
            $obj->plugintype   = $plugintype;
            $obj->plugincode   = $plugincode;
            $obj->code         = $config->code;
            $obj->type         = $config->type;
            $obj->value = null;
            if ( isset($config->value) )
            {
                $obj->value = $config->value;
            }
            // по умолчанию разрешаем наследовать все настройки
            $obj->noextend  = 0;
            if ( isset($config->noextend) )
            {
                $obj->noextend = $config->noextend;
            }
            // id привязки к конкретным объектам не могут быть заданны для стандартных настроек
            $obj->personid     = 0;
            $obj->departmentid = 0;
            // записываем
            $this->insert($obj);
        }      
        return true;
    }

    /** Определяет, предоставляет ли плагин список собственных настроек
     * 
     * @return bool
     * @param string $plugintype - тип плагина, для которого проверяется поддержка настроек
     * @param string $plugincode - код плагина, для которого проверяется поддержка настроек
     */
    public function plugin_has_config($plugintype, $plugincode)
    {
        // получаем все установленные расширения PHP
        $extensions = get_loaded_extensions();
        // определяем, подключена ли SPL-библиотека
        if ( in_array(array('SPL', 'spl'), $extensions) )
        {// расширение SPL подключено
            return $this->plugin_has_config_spl_enabled($plugintype, $plugincode);
        }else
        {// расширение не подключено - справляемся своими силами
            return $this->plugin_has_config_spl_disabled($plugintype, $plugincode);
        }
    }
    
    /** Определяет, предоставляет ли плагин список собственных полномочий
     * (используется если в PHP есть расширение spl. 
     * Без этого расширения не работает функция class_implements)
     * 
     * @return bool
     * @param string $plugintype - тип плагина, для которого проверяется поддержка настроек
     * @param string $plugincode - код плагина, для которого проверяется поддержка настроек
     */
    protected function plugin_has_config_spl_enabled($plugintype, $plugincode)
    {
        // если нет интерфейса - вернет НЕ МАССИВ
        $arrayimplements = class_implements('dof_'.$plugintype.'_'.$plugincode);
        // если массив - работаем
        if (  is_array($arrayimplements) )
        {
            if ( in_array('dof_storage_config_interface', 
                 class_implements('dof_'.$plugintype.'_'.$plugincode)) )
            {
                return true;
            }
        }    
        return false;
    }
    
    /** Определяет, предоставляет ли плагин список собственных полномочий
     * (используется если PHP собран без поддержки библиотеки spl)
     *
     * @return bool
     * @param string $plugintype - тип плагина, для которого проверяется поддержка настроек
     * @param string $plugincode - код плагина, для которого проверяется поддержка настроек
     */
    protected function plugin_has_config_spl_disabled($plugintype, $plugincode)
    {
        if ( method_exists('dof_'.$plugintype.'_'.$plugincode, 'config_default') )
        {// в классе есть функция получения настроек - значит настройки поддерживаются плагином
            return true;
        }
        // плагин не имеет настроек
        return false;
    }

    /** показывает, превышен лимит или нет
     * @todo переписать эту функцию когда появяться  
     * string $code - код плагина
     * integer $departmentid - id подразделения(по умолчанию 0)
     * @return text
     * @access public
     */
    public function get_limitobject($code, $departmentid=0)
    {
        if ( $objnum = $this->get_config_value('objectlimit', 'storage', $code, $departmentid) )
        {// получили лимит
            // создаем массив с интересующими нас статусами
            if ( $code == 'departments' )
            {
                if ( $departmentid )
                {
                    $path = $this->dof->storage('departments')->get_field($departmentid, 'path');
                    $actelements = $this->dof->storage($code)->count_records_select(" (status <> 'deleted' OR status IS NULL) AND path LIKE '$path%'"); 
                }else 
                {
                    $actelements = $this->dof->storage($code)->count_records_select(" status <> 'deleted' OR status IS NULL");    
                }
            }else 
            {
                // ВСе кроме удалённых, отменённых, архивных и черновиков
                $status = array('application','plan','active','suspend','createstreams','createsbc',
                    'createschedule','formed','normal','available','condactive',
                    'new','clientsign','wesign','work', 'frozen', 'onleave', 'notavailable');
                
                if ( $this->dof->plugin_exists('workflow', $code) )
                {// удалим из списка проверяемых те статусы, которых в плагине вообще нет,
                    // чтобы не грузить mysql лишними сравнениями
                    $available = array_keys($this->dof->workflow($code)->get_list());
                    $status    = array_intersect($available, $status);
                }
  
                //кол активных елементов
                $actelements = $this->dof->storage($code)->count_list(array('status'=>$status,'departmentid'=>$departmentid)); 
            }
            
            if ( $objnum > $actelements OR $objnum == '-1')
            {// создавать можем
                return true;
            }
            return false;
        }
        // настройки нет(не понятно почему)-значит без ограничения
        return true;
    }     
    
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
    
    /**Возвращает фрагмент sql-запроса после слова WHERE
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
        if ( ! empty($conds->noid) )
        {// для имени используем шаблон LIKE
            $selects[] = " id != ".$conds->noid;
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->noid);
        }
        if( $conds->departmentid )
        {// Если требуется найти настройки для конкретного подразделения
            $depstring = $this->dof->storage('departments')->change_path_department($conds->departmentid);
            
            $selects[] = " departmentid IN (".$depstring.",0) ";
            unset($conds->departmentid);
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
        $sqlsort = $prefix.'plugintype,plugincode';
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
        $selects[] = $prefix.'plugintype,plugincode '.$dir;
        // возвращаем сортировку
        return implode($selects,',');
    }


}
?>