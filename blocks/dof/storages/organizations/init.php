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


/** Справочник учебных программ
 * 
 */
class dof_storage_organizations extends dof_storage
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
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        $result = true;
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
 
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2013011600;
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
        return 'organizations';
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
        return array('storage'=>array('acl'=>2012042500));
    }
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
        return 'block_dof_s_organizations';
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
        }
        
        return $result;
    }    
    
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        //$a['view']     = array('roles'=>array('manager','methodist'));
        //$a['edit']     = array('roles'=>array('manager'));
        //$a['create']   = array('roles'=>array('manager'));
        //$a['delete']   = array('roles'=>array('manager'));
        $a['use']      = array('roles'=>array('manager','methodist'));
        
        return $a;
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



    // **********************************************
    //              Собственные методы
    // **********************************************
    
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
            case 'organizations_list':    
                return $this->widgets_organizations_list($depid, $data);
            
            default: return array(0 => $this->dof->modlib('ig')->igs('choose'));
        }
    }
    
    
    /** Получить список организация по первым буквам полного имени, короткого имени,
     * ИНН,ОГРН,телефонного номера и айди
     * 
     * @param int $departmenid - подразделение, в котором ищутся организации
     *                           если передан 0 - то организации ищутся во всех подразделениях
     * @param string $fullname - первые несколько букв какого либо поля
     *
     * @return array массив объектов для AJAX-элемента dof_autocomplete
     */
    protected function widgets_organizations_list($departmentid, $fullname)
    {
        global $USER;
        
        $fullname = clean_param($fullname, PARAM_TEXT);
        
         //формируем запрос  
        $select = " fullname LIKE '%".$fullname."%'
                        OR shortname LIKE '%".$fullname."%'
                        OR id LIKE '%".$fullname."%'
                        OR inn LIKE '%".$fullname."%'
                        OR ogrn LIKE '%".$fullname."%'
                        OR phone LIKE '%".$fullname."%' ";
        
        $params = array();
        if ( ! $organizations = $this->get_records_select($select, $params, ' fullname ASC', 'id, shortname, fullname', 0, 15) )
        {// Нет организаций с такими данными
            return array();
        }
         
        // Формируем массив объектов нужной структуры для dof_autocomplete
        $result = array();
        foreach ( $organizations as $organization )
        {
            //если есть право
            if ($this->is_access('use', $organization->id, $USER->id, $departmentid))
            {
                $obj = new stdClass;
                $obj->id   = $organization->id;
                
                if (!empty($organization->fullname))
                {// Если полное имя есть, возвратим его
                    $obj->name = $organization->fullname;
                } else
                {
                    $obj->name = $organization->shortname;
                }
                $result[$organization->id] = $obj;
            }
        }
        return $result;
    }
    
    /** Обработчик добавления организации в справочник "organizations"
     * 
     * @param int $orgid - id организации
     * @param string $organization - название организации
     * @return int - id организации
     */
    public function handle_organization($type,$organization)
    {
        $value = $this->dof->modlib('widgets')->get_extvalues_autocomplete($type,$organization);
        $obj = new stdClass;
        switch ($value['do'])
        {
            // нужно создать запись
            case "create":
                $obj->shortname = $value['name'];
                if ( $this->is_exists(array('shortname'=>$value['name'])) )
                {// создавать с одинаковыми номерами пока нельзя
                    return false;
                }
                return $this->insert($obj);
            // переименовать
            case "rename":
                if ( !$this->is_access('use',$value['id']) OR 
                     !$this->is_exists($value['id']) )
                {// прав нет - пичалька
                    return false;
                }
                if ( $this->is_exists(array('shortname'=>$value['name'])) )
                {// переименовать в уже существующий с таким названием тоже нельзя
                    return false;
                }
                $obj->shortname = $value['name'];
                $this->update($obj,$value['id']);
                return $value['id'];
            // запись просто выбрана
            case "choose":
                if ( !$this->is_access('use',$value['id']) OR 
                     !$this->is_exists($value['id']) )
                {// прав нет - пичалька
                    return false;
                }
                return $value['id'];
            // выбрано пустое значение
            case "empty":
                return null;
            // не распознано - ошибка
            default: 
                dof_debugging('autocomplete returned error', DEBUG_DEVELOPER);
                return null;
        }
        
        //проверка входных параметров
        if (!is_int_string($orgid) OR !is_string($organization))
        {
            return false;
        }
        
        if ( preg_match("/^[0-9]+$/", $organization))
        {// передана страка как id
            $id = $organization;
        }elseif ( $row = $this->dof->storage('organizations')->get_record(array('shortname' => $organization),'id') )
        {// нашли такую запись в БД - работаем с ее id 
            $id = $row->id;
        }elseif ( !empty($orgid) )
        {// передан id - работаем с ним
            $id = $orgid;
        }
        
        if ( !empty($id) )
        {//обрабатываем id
            if ( !$this->dof->storage('organizations')->is_access('use',$id) )
            {// прав нет - пичалька
                return false;
            }
            if ( !empty($orgid) AND !empty($organization) AND $orgid==$id )
            {//нужно обновить организацию
                $obj = new stdClass;
                $obj->shortname = $organization;
                //создаем новую организацию, возвращаем id
                $this->dof->storage('organizations')->update($obj, $orgid);
            }
            return $id;
        }
        // обьект нужно создать
        $obj = new stdClass;
        $obj->shortname = $organization;
        //создаем новую организацию, возвращаем id
        return $this->dof->storage('organizations')->insert($obj);

    }
} 