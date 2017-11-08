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
class dof_storage_learningplan extends dof_storage implements dof_storage_config_interface
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
        global $CFG, $DB;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        $result = true;
        if ( $oldversion < 2014081200 )
        {// Добавим поле learninghistoryid
            $field = new xmldb_field('appointmentid', XMLDB_TYPE_INTEGER, '10',
                    null, null, null, 0, 'programmitemid');
            if ( !$dbman->field_exists($table, $field) )
            {// Поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // Добавляем индекс к полю
            $index = new xmldb_index('appointmentid', XMLDB_INDEX_NOTUNIQUE,
                    array('appointmentid'));
            if ( !$dbman->index_exists($table, $index) )
            {// Если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        
        // обновляем полномочия, если они изменились
        return $result && $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2014090500;
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
        return 'learningplan';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('departments'  => 2009040800,
                                      'acl'          => 2011041800,
                                      'config'       => 2011080900,
                                      'cpassed'      => 2011082200,
                                      'agroups'      => 2013082800,
                                      'programmitems'=> 2013082800,
                                      'programmsbcs' => 2013102500));
    }
    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
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
                                      'config'=> 2011092700));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'eventcode'=>'update'),
                     array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'eventcode'=>'delete'),
        );
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
        if ( $gentype == 'storage' AND $gencode == 'programmitems' )
        {//есть событие от этого справочника';
            switch ($eventcode)
            {//обработаем его
                case 'update': return $this->check_programmitems_update($intvar, $mixedvar['new']);
                case 'delete': return $this->check_programmitems_delete($intvar);
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
        return 'block_dof_s_learningplan';
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
        // максимально разрешенное количество объектов этого типа в базе
        // (указывается индивидуально для каждого подразделения)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'objectlimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;
        // Выбирать преподавателя автоматически
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'autochooseteacher';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Использовать поле "рекомендованный преподаватель"
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'recommendedteacher';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Отображать все планируемые дисциплины в меню, если в программе
        // задана опция "Плавающие учебные планы" - flowagenums
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'showallpitems';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************

    /** Добавить к учебному плану запланированную дисциплину $pitemid для параллели $agenum
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели)
     * @param int $pitemid - id из таблицы programmitems
     * @return bool|int - результат операции (false - ошибка, true - запись уже существует) или
     *                    id добавленной записи
     */
    public function add_to_planned($type, $typeid, $agenum, $pitemid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR !is_int_string($agenum)
                          OR !is_int_string($pitemid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        // Проверим, что планируемый семестр в пределах количества семестров программы
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        if ( $agenums = $this->dof->storage('programms')->get_field($programmid, 'agenums') )
        {
            if ( $agenum < 0 OR $agenum > $agenums )
            {
                return false;
            }
        } else
        { // Не нашли программу?
            return false;
        }
        
        $conds = array('type' => $type,
                       "{$type}id" => $typeid,
                       'programmitemid' => $pitemid,
                       'agenum' => $agenum);
        // Проверим, не запланирована ли уже дисциплина
        if ( $this->is_exists($conds) )
        {
            return true;
        }
        // Проверим, что дисциплина активна
        if ( $status = $this->dof->storage('programmitems')->get_field($pitemid,'status') )
        {
            if ( $status != 'active' )
            {
                return false;
            }
        } else
        {
            return false;
        }
        $insert = new stdClass();
        $insert->type           = $type;
        $insert->{$type.'id'}   = $typeid;
        $insert->programmitemid = $pitemid;
        $insert->agenum         = $agenum;
        return $this->insert($insert);
    }
    
    
    /** Убрать из учебного плана запланированную дисциплину
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $pitemid - id из таблицы programmitems
     * @return bool - результат операции (false - ошибка, true - запись удалена)
     */
    public function remove_from_planned($type, $typeid, $pitemid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR !is_int_string($pitemid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        // Проверим, что программа есть
        if ( ! $programmid = $this->get_programmid_type_typeid($type, $typeid) )
        { // Не нашли программу?
            return false;
        }
        
        $conds = array('type' => $type,
                       "{$type}id" => $typeid,
                       'programmitemid' => $pitemid);
        // Проверим, есть ли такая запланированная дисциплина
        if ( ! $this->is_exists($conds) )
        { // Дисциплина уже удалена
            return true;
        }
        if ( $planned = $this->get_record($conds) )
        { // Запись получили, удалим её
            return $this->delete($planned->id);
        }
        return false;
    }
    
    /** Изменить в учебном плане запланированной дисциплине $pitemid параллель на $agenum
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели)
     * @param int $pitemid - id из таблицы programmitems
     * @return bool - результат операции
     */
    public function change_planned_agenum($type, $typeid, $agenum, $pitemid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR !is_int_string($agenum)
                          OR !is_int_string($pitemid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        // Проверим, что программа есть
        if ( ! $programmid = $this->get_programmid_type_typeid($type, $typeid) )
        { // Не нашли программу?
            return false;
        } else
        { // Программу нашли, проверим семестр
            if ( $agenums = $this->dof->storage('programms')->get_field($programmid, 'agenums') )
            {
                if ( $agenum < 0 OR $agenum > $agenums )
                {
                    return false;
                }
            }
        }
        
        $conds = array('type' => $type,
                       "{$type}id" => $typeid,
                       'programmitemid' => $pitemid);
        // Проверим, есть ли такая запланированная дисциплина
        if ( ! $this->is_exists($conds) )
        {
            return false;
        }
        if ( $planned = $this->get_record($conds) )
        { // Запись получили, обновим
            $planned->agenum = $agenum;
            return $this->update($planned);
        }
    }
    
    /** Проверяет целостность таблицы, исключая из неё те дисциплины, которые были удалены
     * 
     * @param int $intvar - номер дисциплины из таблицы programmitems
     */
    public function check_programmitems_delete($intvar)
    {
        // Проверим, есть ли такая дисциплина в нашей таблице
        $conds = array('programmitemid' => $intvar);
        if ( ! $this->is_exists($conds) )
        { // Дисциплины нет, ничего не делаем
            return true;
        }
        // Дисциплина перестала быть активной, удалим записи в таблице
        $listing = $this->get_listing($conds);
        foreach ( $listing as $id => $row )
        {
            $this->delete($id);
        }
        return true;
    }

    /** Проверяет целостность таблицы, исключая из неё те дисциплины, которые стали неактивными
     * 
     * @param int $intvar - номер дисциплины из таблицы programmitems
     * @param object $new - объект дисциплины после обновления
     * @return bool - результат операции
     */
    public function check_programmitems_update($intvar, $new)
    {
        // Проверим, есть ли такая дисциплина в нашей таблице
        $conds = array('programmitemid' => $intvar);
        if ( ! $this->is_exists($conds) )
        { // Дисциплины нет, ничего не делаем
            return true;
        }
        if ( $new->status != 'active' )
        { // Дисциплина перестала быть активной, удалим записи в таблице
            $listing = $this->get_listing($conds);
            foreach ( $listing as $id => $row )
            {
                $this->delete($id);
            }
        }
        return true;
    }
        
    /** Получить все дисциплины программы, кроме запланированных в виде академической
     *  разницы (agenum=0), для всех параллелей, или только указанной
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @param string|array $status - статус дисциплин, можно передать массив или false для всех
     * @return bool|array - дисциплины программы, исключая запланированные, или false в случае ошибки
     */
    public function get_all_pitems($type, $typeid, $agenum = false, $status = null)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR (!is_int_string($agenum)
                          AND !is_bool($agenum)) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        if ( $programmid == false )
        {
            return false;
        }
        // Получим все дисциплины параллели
        $pitemsall = $this->dof->storage('programmitems')->get_pitems_list($programmid, $agenum, $status);
        // Добавить получение запланированных и изученных дисциплин
//        $planned = $this->get_planned_pitems($type, $typeid);
//        $learned = $this->get_learned_pitems($type, $typeid);
        $pitems = array();
        if ( $pitemsall == false )
        {
            return array();
        } else
        {
            foreach ( $pitemsall as $pitem )
            {
                $pitems[$pitem->id] = new stdClass();
                $pitems[$pitem->id]->id                 = $pitem->id;
                $pitems[$pitem->id]->name               = $pitem->name;
                $pitems[$pitem->id]->metaprogrammitemid = $pitem->metaprogrammitemid;
                // Определим статус (в программе, запланирована, изучена)
                $pitems[$pitem->id]->status   = $this->get_pitem_status($type, $typeid, $pitem->id);
                $pitems[$pitem->id]->code     = $pitem->code;
                $pitems[$pitem->id]->agenum   = $pitem->agenum;
                $pitems[$pitem->id]->required = $pitem->required;
                // Найдём оценку по предмету (перезачёты имеют больший приоритет)
                if ( $pitems[$pitem->id]->status == 'learned' )
                {
                    $pitems[$pitem->id]->grade  = $this->get_pitem_grade($type, $typeid, $pitem->id);
                } else
                {
                    $pitems[$pitem->id]->grade  = null;
                }
            }
        }
        // Получим все дисциплины, для которых agenum == 0 (академическая разница)
        $conds = array('type' => $type, "{$type}id" => $typeid, 'agenum' => 0);
        $planned = $this->get_listing($conds, null, null, '', 'id,programmitemid');
        foreach ( $planned as $plan )
        { // Исключим из дисциплин те, которые уже запланированы как академическая разница
            if ( isset($pitems[$plan->programmitemid]) )
            {
                unset($pitems[$plan->programmitemid]);
            }
        }
        return $pitems;
    }
    
    /** Получить список изученных, активных и перезачтённых дисциплин учебного плана для указанной параллели (или для всех)
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @param string|array $status - статус дисциплин, можно передать массив или null для всех
     * @return bool|array - изученные, активные и перезачтённые дисциплины.
     */
    public function get_learned_pitems($type, $typeid, $agenum = false, $status = null)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR (!is_int_string($agenum)
                          AND !is_bool($agenum)) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        } else
        {
            if ( $type == 'agroup' )
            {
                // Если группа уже обучается, то нужно перебирать вручную:
                // $programmsbcids = storage('programmsbcs')->get_records('agroupid'=>$typeid)
                // foreach ( $programmsbcids as $programmsbcid ) {
                //     get_learned_pitems('programmsbc', $programmsbcid, $agenum);
                // }
                return array();
            } else
            {
                $programmsbcid = $typeid;
            }
        }
        
        // Получим все дисциплины...
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        if ( $programmid == false )
        {
            return false;
        }
        // Выберем все cpasseds, относящиеся к нашей подписке, отсортированные по begindate
        $params = new stdClass();
        $params->programmsbcid  = $programmsbcid;
        $params->status         = array_keys($this->dof->workflow('cpassed')->get_meta_list('real'));
        $cpasseds = $this->dof->storage('cpassed')->get_records((array)$params, 'begindate DESC',
                'id,begindate,programmitemid,status,grade,learninghistoryid');
        // Сразу же включим последние (по begindate) записи в pitems
        $pitems = array();
        // Для сортировки по семестру и названию
        $sort = array();
        // Нам нужны только те записи, где learninghistory.agenum == $agenum,
        // и заодно создадим массив $pitems, $sort
        foreach ( $cpasseds as $id => $cpassed )
        {
            $lh = $this->dof->storage('learninghistory')->get($cpassed->learninghistoryid);
            if ( (!empty($lh) AND $lh->agenum == $agenum) OR empty($agenum) )
            { // Если нашли learninghistory, или это нулевая параллель
                if ( $pitem = $this->dof->storage('programmitems')->get($cpassed->programmitemid, $this->get_programmitem_fields(true)) )
                {
                    if ( !is_bool($agenum) AND $agenum == 0 )
                    { // В "доступных для всех" отображаем только дисциплины с нулевой параллелью
                        if ( $pitem->agenum != 0 )
                        {
                            continue;
                        } else if ( empty($lh) OR $lh->agenum != 0 )
                        { // если они не изучаются/изучены в другой параллели
                            continue;
                        }
                        // А для $agenum === false забираем все дисциплины
                    }
                    // Если есть оценка
                    if ( !empty($cpassed->grade) )
                    {
                        $pitem->grade = $cpassed->grade;
                    } else
                    {
                        $pitem->grade = null;
                    }
                    $pitem->cpassedid     = $cpassed->id;
                    $pitem->cpassedstatus = $cpassed->status;
                    // Добавим "0" к номеру семестра, чтобы отсортировать дисциплины сначала по номеру семестра,
                    // а затем по названию:
                    //  01 Тестовая дисциплина 1
                    //  01 Тестовая дисциплина 2
                    //  02 Тестовая дисциплина 3
                    if ( $pitem->agenum < 10 )
                    {
                        $anumsort = '0' . $pitem->agenum;
                    } else
                    {
                        $anumsort = $pitem->agenum;
                    }
                    if ( !isset($sort[$pitem->id]) )
                    {
                        $sort[$pitem->id] = "{$anumsort} " . $pitem->name;
                        $pitems[$pitem->id] = $pitem;
                    }
                }
            } else if ( empty($lh) AND !empty($agenum) )
            { // Не нашли learninghistory для этого cpassed, тогда смотрим
                if ( $pitem = $this->dof->storage('programmitems')->get($cpassed->programmitemid, $this->get_programmitem_fields(true)) )
                {
                    if ( $pitem->agenum != $agenum )
                    {
                        continue;
                    }
                    // Если есть оценка
                    if ( !empty($cpassed->grade) )
                    {
                        $pitem->grade = $cpassed->grade;
                    } else
                    {
                        $pitem->grade = null;
                    }
                    $pitem->cpassedid     = $cpassed->id;
                    $pitem->cpassedstatus = $cpassed->status;
                    // Добавим "0" к номеру семестра, чтобы отсортировать дисциплины сначала по номеру семестра,
                    // а затем по названию:
                    //  01 Тестовая дисциплина 1
                    //  01 Тестовая дисциплина 2
                    //  02 Тестовая дисциплина 3
                    if ( $pitem->agenum < 10 )
                    {
                        $anumsort = '0' . $pitem->agenum;
                    } else
                    {
                        $anumsort = $pitem->agenum;
                    }
                    if ( !isset($sort[$pitem->id]) )
                    {
                        $sort[$pitem->id] = "{$anumsort} " . $pitem->name;
                        $pitems[$pitem->id] = $pitem;
                    }
                }
            }
        }
        // Отсортируем по семестру и названию
        if ( !empty($sort) AND count($sort) > 1 )
        {
            $arrkeys = array_keys($sort);
            array_multisort(array_values($sort), SORT_ASC, SORT_REGULAR, $arrkeys, $pitems);
            $pitems = array_combine($arrkeys, $pitems);
        }
        
        return $pitems;
    }
    
    /** Получить информацию об учебном плане: тип, код, имя, программа,
     *  текущий семестр, период, количество семестров
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @return object|bool - возвращаемый объект с полями
     * ->type     - тип объекта
     * ->typeid   - номер объекта
     * ->code     - код объекта
     * ->name     - имя объекта
     * ->programm - учебная программа, на которую планируются дисциплины
     * ->agenum   - текущий семестр
     * ->age      - текущий период
     * ->agenums  - количество семестров в программе
     * или false в случае ошибки
     */
    public function get_learningplan_info($type, $typeid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        // Возвращаемый объект
        $learninginfo = new stdClass();
        $learninginfo->type = $type;
        $learninginfo->typeid = $typeid;
        // Определим тип учебного плана
        if ( $type == 'programmsbc' )
        { // Тип: подписка на программу
            if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($typeid) )
            { // Не нашли подписку
                return false;
            }
            if ( ! $contract = $this->dof->storage('contracts')->get($programmsbc->contractid) )
            { // Не нашли контракт
                return false;
            }
            //$contract->studentid
            $fullname = $this->dof->storage('persons')->get_fullname($contract->studentid);
            $learninginfo->name = "$fullname [{$contract->num}]";
            $programmid = $programmsbc->programmid;
        } else if ( $type == 'agroup' )
        { // Тип: академическая группа
            if ( ! $agroup = $this->dof->storage('agroups')->get($typeid) )
            { // Не нашли академическую группу
                return false;
            }
            // Достанем одну подписку, чтобы определить программу
            $psbcs = $this->dof->storage('programmsbcs')->get_records(array('agroupid'=>$typeid), '', '*', 0, 1);
            if ( !is_array($psbcs) OR ! $programmsbc = current($psbcs) )
            { // Не нашли подписку
                return false;
            }
            $learninginfo->name = $agroup->name;
            $programmid = $programmsbc->programmid;
        } else
        {// Неверный тип
            return false;
        }
        
        if ( ! $programm = $this->dof->storage('programms')->get($programmid, 'id, code, name, agenums, flowagenums, edulevel') )
        { // Не нашли такой программы
            return false;
        }
        // Поля для программы
        $learninginfo->programm = new stdClass();
        $learninginfo->programm->id = $programm->id;
        $learninginfo->programm->code = $programm->code;
        $learninginfo->programm->name = $programm->name;
        $learninginfo->programm->flowagenums = $programm->flowagenums;
        $learninginfo->programm->edulevel = $programm->edulevel;
        // Текущий семестр (0 == обучение не начато)
        $learninginfo->agenum = $programmsbc->agenum;
        // Текущая запись в истории обучения
        if ( $type == 'agroup' )
        {
            $actualdata = $this->dof->storage('agrouphistory')->get_actual_learning_data($typeid);
        } else
        {
            $actualdata = $this->dof->storage('learninghistory')->get_actual_learning_data($programmsbc->id);
        }
        if ( ! $actualdata )
        { // Текущей записи нет (вроде такого не должно быть)
            $learninginfo->age = null;
        } else
        { // 
            $age = $this->dof->storage('ages')->get($actualdata->ageid);
            // Проверим, есть ли учебный период
            if ( $age = $this->dof->storage('ages')->get($actualdata->ageid) )
            {
                $learninginfo->age = new stdClass();
                $learninginfo->age->id        = $age->id;
                $learninginfo->age->name      = $age->name;
                $learninginfo->age->begindate = $age->begindate;
                $learninginfo->age->eduweeks  = $age->eduweeks;
            } else
            { // Такого учебного периода нет
                $learninginfo->age = null;
            }
        }
        // Количество семестров
        $learninginfo->agenums = $programm->agenums;
        return $learninginfo;
    }
    
    /** Получить объект, содержащий в себе начальную конфигурацию учебного плана: добавление всех
     *  обязательных дисциплин в соответствии с указанными семестрами
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @return object|bool - false в случае ошибки или объект с полями:
     * ->planning - дисциплины, предусмотренные программной на эту параллель, которые возможно запланировать
     * ->planned  - запланированные дисциплины
     * ->learned  - изученные дисциплины
     * ->all      - все дисциплины, предусмотренные программой для этой параллели
     *  (включая уже запланированные и изученные, исключая дисциплины из нулевой параллели)
     */
    public function get_learningplan_pitems($type, $typeid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        
        // Определим тип учебного плана
        if ( $type == 'programmsbc' )
        {
            
        } else if ( $type == 'agroup' )
        {
            
        } else
        {// Неверный тип
            return false;
        }
        $learningplan = array();
        // Нам нужны agenums от программы
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        $agenums = $this->dof->storage('programms')->get_field($programmid, 'agenums');
        for ( $agen = 0; $agen <= $agenums; $agen++ )
        {
            // Объект, содержащий дисциплины
            $pitems = new stdClass();
            $pitems->planning = $this->get_planning_pitems($type, $typeid, $agen);
            $pitems->planned  = $this->get_planned_pitems($type, $typeid, $agen);
            $pitems->learned  = $this->get_learned_pitems($type, $typeid, $agen, array('active'));
            $pitems->all      = $this->get_all_pitems($type, $typeid, $agen, array('active'));
            
            $learningplan[$agen] = $pitems;
        }
        return $learningplan;
    }

    /** Получить запланированные для указанной параллели дисцплины ($agenum = 0 - академическая разница)
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @return bool|array - изученные, активные и перезачтённые дисциплины или false в случае ошибки
     */
    public function get_planned_pitems($type, $typeid, $agenum = false)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR (!is_int_string($agenum)
                          AND !is_bool($agenum)) )
        {
            return false;
        }
        // Определим тип учебного плана
        if ( $type == 'programmsbc' )
        {
            
        } else if ( $type == 'agroup' )
        {
            
        } else
        {// Неверный тип
            return false;
        }
        $conds = new stdClass();
        $conds->type = $type;
        $conds->{$type.'id'} = $typeid;
        if ( $agenum !== false AND is_int_string($agenum) )
        {
            $conds->agenum = $agenum;
        }
        
        // Достанем все запланированные дисциплины из учебного плана
        $planned = $this->get_listing($conds);
        // Отфильтруем те, которые уже изучены
        $learned = $this->get_learned_pitems($type, $typeid, false);
        foreach ( $planned as $id => $plan )
        {
            if ( array_key_exists($plan->programmitemid, $learned) )
            {
                unset($planned[$id]);
            }
        }
        $pitems = array();
        
        // Для сортировки по семестру и названию дисциплины
        $sort = array();
        // Сформируем массив дисциплин для возврата
        foreach ( $planned as $plan )
        {
            if ( $pitem = $this->dof->storage('programmitems')->get($plan->programmitemid, $this->get_programmitem_fields(true)) )
            {
                if ( $pitem->status == 'deleted' )
                { // Удалённые дисциплины не нужны
                    continue;
                }
                $pitem->status  = 'planned';
                // У запланированных дисциплин нет оценок
                $pitem->grade   = null;
                // Добавим "0" к номеру семестра (если меньше 10), чтобы
                //  отсортировать дисциплины сначала по номеру семестра,
                //  а затем по названию:
                //  01 Тестовая дисциплина 1
                //  01 Тестовая дисциплина 2
                //  02 Тестовая дисциплина 3
                if ( $pitem->agenum < 10 )
                {
                    $anumsort = '0' . $pitem->agenum;
                } else
                {
                    $anumsort = $pitem->agenum;
                }
                $sort[$pitem->id] = "{$anumsort} " . $pitem->name;
                // Добавим рекомендованного преподавателя
                $pitem->appointmentid = $plan->appointmentid;
                $pitems[$pitem->id] = $pitem;
            }
        }
        // Отсортируем по семестру и названию
        if ( !empty($sort) AND count($sort) > 1 )
        {
            $arrkeys = array_keys($sort);
            array_multisort(array_values($sort), SORT_ASC, SORT_REGULAR, $arrkeys, $pitems);
            $pitems = array_combine($arrkeys, $pitems);
        }
        
        return $pitems;
    }
    
    /** Получить запланированные и изученные (поиск во всех семестрах get_learned_pitems)
     *  для указанной параллели дисцплины ($agenum = 0 - академическая разница)
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @return bool|array - запланированные и изученные дисциплины или false в случае ошибки
     */
    public function get_signed_pitems($type, $typeid, $agenum = false)
    {
        $planned = $this->get_planned_pitems($type, $typeid, $agenum);
        $learned = $this->get_learned_pitems($type, $typeid, false);
        if ( empty($learned) )
        {
            return array();
        }
        $signed = array();
        // Найдём все запланированные дисциплины, которые "изучены" или изучаются
        foreach ( $planned as $pid => $pitem )
        {
            if ( array_key_exists($pid, $learned) )
            {
                $signed[$pid] = $pitem;
            }
        }
        return $signed;
    }
    
    /** Получить запланированные, но не изученные (поиск во всех семестрах get_learned_pitems)
     *  для указанной параллели дисцплины ($agenum = 0 - академическая разница)
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @return bool|array - запланированные, но не изученные дисциплины или false в случае ошибки
     */
    public function get_unsigned_pitems($type, $typeid, $agenum = false)
    {
        $planned = $this->get_planned_pitems($type, $typeid, $agenum);
        $learned = $this->get_learned_pitems($type, $typeid, false);
        $unsigned = array();
        // Найдём все запланированные дисциплины, которые "изучены" или изучаются
        foreach ( $planned as $pid => $pitem )
        {
            if ( ! array_key_exists($pid, $learned) )
            {
                $unsigned[$pid] = $pitem;
            }
        }
        return $unsigned;
    }
    
    /** Получить запланированные на текущую параллель дисциплины с учётом дополнительных настроек
     * (используется для передачи в функцию автоподписки)
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @return bool|array - дисциплины для подписки или false в случае ошибки
     */
    public function get_subscribe_current_agenum_pitems($type, $typeid, $options = array())
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        $info     = $this->get_learningplan_info($type, $typeid);
        $unsigned = $this->get_unsigned_pitems($type, $typeid, $info->agenum);
        // Достаём ageid из актуальной истории обучения
        if ( $type == 'programmsbc' )
        {
            $actual = $this->dof->storage('learninghistory')->get_actual_learning_data($typeid);
        } else
        {
            $actual = $this->dof->storage('agrouphistory')->get_actual_learning_data($typeid);
        }
        $ageid = 0;
        if ( $actual->ageid )
        {
            $ageid = $actual->ageid;
        } else
        {
            // Если период не найден, то подписать мы не можем
            return false;
        }
        // Получим настройки для выбора преподавателей
        $autochooseteacher = $this->dof->storage('config')->get_config_value('autochooseteacher',
                'storage', 'learningplan', optional_param('departmentid', 0, PARAM_INT));
        $recommendedteacher = $this->dof->storage('config')->get_config_value('recommendedteacher',
                'storage', 'learningplan', optional_param('departmentid', 0, PARAM_INT));
        
        // Названия дополнительных настроек
        $optcolumns = array('appointmentid', 'cstreamid');
        // Инициализируем дополнительные настройки и добавим ageid
        foreach ( $unsigned as $pid => $pitem )
        {
            foreach ( $optcolumns as $column )
            {
                $unsigned[$pid]->$column = 0;
                $unsigned[$pid]->ageid = $ageid;
            }
        }
        
        if ( $autochooseteacher )
        { // Выбираем преподавателя по-умолчанию, наименее загруженного
            foreach ( $unsigned as $pid => $pitem )
            {
                $teachers = $this->dof->storage('teachers')->get_appointments_active_for_pitem($pid);
                $minload = 10;
                $appointmentid = 0;
                // Просматриваем табельные номера в поисках минимально загруженного
                foreach ( $teachers as $appid => $teacher )
                {
                    $load = $this->dof->storage('appointments')->get_appointment_load($appid);
                    if ( $minload > $load AND $load !== false )
                    {
                        $minload = $load;
                        $appointmentid = $appid;
                    }
                }
                // Табельный номер с наименьшей нагрузкой
                $unsigned[$pid]->appointmentid = $appointmentid;
            }
        }
        // Если рекомендованного учителя используем, и он указан, возьмём оттуда
        if ( $recommendedteacher )
        {
            $conds = array('type'=>$type,"{$type}id"=>$typeid);
            $plan = $this->get_records($conds);
            foreach ( $plan as $record )
            {
                $appid = $record->appointmentid;
                $pid   = $record->programmitemid;
                $teachers = $this->dof->storage('teachers')->get_appointments_active_for_pitem($pid);
                // Проверим, активен ли табельный номер
                if ( $appid != 0 AND array_key_exists($appid, $teachers) )
                {
                    $unsigned[$pid]->appointmentid = $appid;
                }
            }
        }
        
        // По каждому предмету добавим опции из дополнительного массива
        if ( !empty($options) )
        {
            foreach ( $unsigned as $pid => $pitem )
            {
                if ( isset($options[$pid]) )
                {
                    foreach ( $optcolumns as $column )
                    {
                        if ( isset($options[$pid]->$column) )
                        {
                            $unsigned[$pid]->$column = $options[$pid]->$column;
                        }
                    }
                }
            }
        }
        return $unsigned;
    }
    
    /** Получить дисциплины, предусмотренные программной на эту параллель, которые возможно запланировать
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @return bool|array - планируемые дисциплины (которые можно добавить в план) или false в случае ошибки
     */
    public function get_planning_pitems($type, $typeid, $agenum = false)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR (!is_int_string($agenum)
                          AND !is_bool($agenum)) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        // Получим текущие дисциплины и из нулевой паралели
        $flowagenums = $this->dof->storage('programms')->get_field($programmid, 'flowagenums');
        $all = array();
        // Отображать все планируемые дисциплины в меню, если в программе
        // задана опция "Плавающие учебные планы" - flowagenums
        $showallpitems = $this->dof->storage('config')->get_config_value('showallpitems',
                'storage', 'learningplan', optional_param('departmentid', 0, PARAM_INT));
        if ( $showallpitems AND $flowagenums AND $agenum != 0)
        {
            // Общее число семестров
            $agenums = $this->dof->storage('programms')->get_field($programmid, 'agenums');
            // Текущий семестр (если 0, то прибавляем 1)
            $curagenum = $this->get_current_agenum_type_typeid($type, $typeid);
            if ( $curagenum == 0 )
            { // Академическую разницу не добавляем
                $curagenum++;
            }
            // Получим семестры, которые необходимо добавить в список
            $planningagenums = array();
            for ( $agen = 1; $agen <= $agenums; $agen++ )
            { // Создаём список со всеми семестрами
                if ( $agen >= $curagenum )
                { // В пройденные семестры переносить дисциплины нельзя
                    $planningagenums[$agen] = $agen;
                }
            }
            foreach ( $planningagenums as $agen )
            {
                if ( $pitems = $this->get_all_pitems($type, $typeid, $agen, array('active')) )
                {
                    $all += $pitems;
                }
            }
        } else
        {
            $all = $this->get_all_pitems($type, $typeid, $agenum, array('active'));
        }
        $pitems = array();
        $planned = $this->get_planned_pitems($type, $typeid, false);
        $learned = $this->get_learned_pitems($type, $typeid, false, array('active'));
        $exclude = $planned + $learned;

        // Отфильтруем планируемые дисциплины: все дисциплины программы кроме запланированных и изученных

        // Для сортировки по семестру и названию дисциплины
        $sort = array();
        foreach ( $all as $pitemid => $pitem )
        { // Если исключать не надо
            if ( !isset($exclude[$pitemid]) )
            {
                // Добавим "0" к номеру семестра, чтобы отсортировать дисциплины сначала по номеру семестра,
                // а затем по названию:
                //  01 Тестовая дисциплина 1
                //  01 Тестовая дисциплина 2
                //  02 Тестовая дисциплина 3
                if ( $pitem->agenum < 10 )
                {
                    $anumsort = '0' . $pitem->agenum;
                } else
                {
                    $anumsort = $pitem->agenum;
                }
                $sort[$pitem->id] = "{$anumsort} " . $pitem->name;
                $pitems[$pitemid] = $pitem;
            }
        }
        // Отсортируем по семестру и названию
        if ( !empty($sort) AND count($sort) > 1 )
        {
            $arrkeys = array_keys($sort);
            array_multisort(array_values($sort), SORT_ASC, SORT_REGULAR, $arrkeys, $pitems);
            $pitems = array_combine($arrkeys, $pitems);
        }
            
        return $pitems;
    }
    
    /** Получить номер программы по типу и номеру индивидуального плана
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @return bool|int - номер программы или false в случае ошибки
     */
    public function get_programmid_type_typeid($type, $typeid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        $programmid = false;
        // Определим тип учебного плана
        if ( $type == 'programmsbc' )
        { // Тип: подписка на программу
            if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($typeid) )
            { // Не нашли подписку
                return false;
            }
            $programmid = $programmsbc->programmid;
            
        } else if ( $type == 'agroup' )
        { // Тип: академическая группа
            if ( ! $agroup = $this->dof->storage('agroups')->get($typeid) )
            { // Не нашли академическую группу
                return false;
            }
            // Достанем одну подписку, чтобы определить программу
            $psbcs = $this->dof->storage('programmsbcs')->get_records(array('agroupid'=>$typeid), '', '*', 0, 1);
            if ( !is_array($psbcs) OR ! $programmsbc = current($psbcs) )
            { // Не нашли подписку
                return false;
            }
            $programmid = $programmsbc->programmid;
        } else
        {// Неверный тип
            return false;
        }
        return $programmid;
    }

    /** Получить номер текущего семестра по типу и номеру индивидуального плана
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @return bool|int - номер текущего семестра или false в случае ошибки
     */
    public function get_current_agenum_type_typeid($type, $typeid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        $agenum = false;
        // Определим тип учебного плана
        if ( $type == 'programmsbc' )
        { // Тип: подписка на программу
            if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($typeid) )
            { // Не нашли подписку
                return false;
            }
            $agenum = $programmsbc->agenum;
            
        } else if ( $type == 'agroup' )
        { // Тип: академическая группа
            if ( ! $agroup = $this->dof->storage('agroups')->get($typeid) )
            { // Не нашли академическую группу
                return false;
            }
            // Достанем одну подписку, чтобы определить программу
            $psbcs = $this->dof->storage('programmsbcs')->get_records(array('agroupid'=>$typeid), '', '*', 0, 1);
            if ( !is_array($psbcs) OR ! $programmsbc = current($psbcs) )
            { // Не нашли подписку
                return false;
            }
            $agenum = $programmsbc->agenum;
        } else
        {// Неверный тип
            return false;
        }
        return $agenum;
    }
    
    /** Получить список всех перезачтённых дисциплин учебного плана или только для указанной параллели
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели), если false - все семестры
     * @param string|array $status - статус дисциплин, можно передать массив или null для всех
     * @return bool|array - перезачтённые дисциплины.
     */
    public function get_reoffset_pitems($type, $typeid, $agenum = false, $status = null)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR (!is_int_string($agenum)
                          AND !is_bool($agenum)) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        } else
        {
            if ( $type == 'agroup' )
            {
                // Если у группы были перезачёты, то нужно перебирать вручную:
                // $programmsbcids = storage('programmsbcs')->get_records('agroupid'=>$typeid)
                // foreach ( $programmsbcids as $programmsbcid ) {
                //     get_reoffset_pitems('programmsbc', $programmsbcid, $agenum);
                // }
                return array();
            } else
            {
                $programmsbcid = $typeid;
            }
        }
        
        // Получим все дисциплины...
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        if ( $programmid == false )
        {
            return false;
        }
        // ...Которые относятся к семестру $agenum
        $pitems = $this->dof->storage('programmitems')->get_pitems_list($programmid, $agenum, $status);
        if ( $pitems == false )
        {
            return array();
        }
        // Составим параметры для запроса изученных дисциплин:
        $params = new stdClass();
        $params->programmitemid = array_keys($pitems);
        unset($pitems);
        $params->programmsbcid  = $programmsbcid;
        $this->dof->workflow('cpassed')->get_list();
        $params->status = array('reoffset');
        if ( $agenum )
        {
            // Получим ageid по номеру семестра и добавим условие
            if ( $ageid = $this->dof->storage('learninghistory')->get_ageid_agenum($typeid, $agenum) )
            {
                $params->ageid = $ageid;
            }
        }
        $cpasseds = $this->dof->storage('cpassed')->get_cpasseds_reoffset($params, null, null, '', 'id,begindate,programmitemid,status,grade,required');

        // Статусы дисциплин (перезачёты, завершённые, текущие)
        $reoffsets = array();
        foreach ( $cpasseds as $cpassed )
        { // Распределим дисциплины по статусам
            switch ( $cpassed->status )
            {
                case 'reoffset':
                    if ( isset($reoffsets[$cpassed->programmitemid]) )
                    {
                        $reoffsets[$cpassed->programmitemid] = array();
                    }
                    $reoffsets[$cpassed->programmitemid][] = $cpassed;
                    break;

                default:
                    // Такого не может быть
                    dof_debugging('status: '. $cpassed->status, DEBUG_DEVELOPER);
                    return false;
            }
        }
        // Пройдёмся по перезачётам и убедимся, что был только один перезачёт
        foreach ( $reoffsets as $pitemid => $reoffset )
        {
            if ( $cpassed = $this->dof->storage('cpassed')->get_actual_reoffset_cpassed($reoffset) )
            { // Исключаем "лишние" перезачёты
                $reoffsets[$pitemid] = $cpassed;
            }
        }
        // Соберём все оценки за дисциплины
        $allcpasseds = $reoffsets;
        $pitems = array();
        // Для сортировки по семестру и названию дисциплины
        $sort = array();
        // Из подписок достанем сами дисциплины и прикрепим к ним оценки, если они есть
        foreach ( $allcpasseds as $cpassed )
        {
            if ( $pitem = $this->dof->storage('programmitems')->get($cpassed->programmitemid, $this->get_programmitem_fields(true)) )
            {
                if ( $cpassed->status != 'active' )
                { // У завершённых и пересдач есть оценка
                    $pitem->grade = $cpassed->grade;
                } else
                { // У активных её нет
                    $pitem->grade = null;
                }
                // Добавим "0" к номеру семестра, чтобы отсортировать дисциплины сначала по номеру семестра,
                // а затем по названию:
                //  01 Тестовая дисциплина 1
                //  01 Тестовая дисциплина 2
                //  02 Тестовая дисциплина 3
                if ( $pitem->agenum < 10 )
                {
                    $anumsort = '0' . $pitem->agenum;
                } else
                {
                    $anumsort = $pitem->agenum;
                }
                $sort[$pitem->id] = "{$anumsort} " . $pitem->name;
                $pitems[$pitem->id] = $pitem;
            }
        }
        // Отсортируем по семестру и названию
        if ( !empty($sort) AND count($sort) > 1 )
        {
            $arrkeys = array_keys($sort);
            array_multisort(array_values($sort), SORT_ASC, SORT_REGULAR, $arrkeys, $pitems);
            $pitems = array_combine($arrkeys, $pitems);
        }
        return $pitems;
    }
    
    /** Создать начальную конфигурацию учебного плана: добавление всех
     *  обязательных дисциплин в соответствии с указанными семестрами
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @return bool|object - объект с учебным планом или false в случае ошибки
     */
    public function create_learningplan($type, $typeid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        
        // Определим тип учебного плана
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        {// Неверный тип
            return false;
        }
        // Проверим, доступна ли программа
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        $pstatus = $this->dof->storage('programms')->get_field($programmid, 'status');
        if ( $pstatus == 'archive' )
        {
            return false;
        }
        // Собираем "заголовок" объекта
        $learningplan = $this->get_learningplan_info($type, $typeid);
        if ( $learningplan == false )
        {
            return false;
        }
        // Собираем дисциплины
        $learningplan->learningplan = $this->get_learningplan_pitems($type, $typeid);
        if ( $learningplan->learningplan == false )
        {
            return false;
        }
        return $learningplan;
    }
    
    /** Метод для планирования всех обязательных дисциплин для одной указанной параллели $agenum
     * или всех обязательных дисциплин программы
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $agenum - номер семестра (параллели)
     * @return bool - результат операции
     */
    public function add_to_planned_required_agenum($type, $typeid, $agenum = false)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR (!is_int_string($agenum)
                          AND !is_bool($agenum)) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        // Проверим, что планируемый семестр в пределах количества семестров программы
        $programmid = $this->get_programmid_type_typeid($type, $typeid);
        if ( $agenums = $this->dof->storage('programms')->get_field($programmid, 'agenums') )
        {
            if ( $agenum < 0 OR $agenum > $agenums )
            {
                return false;
            }
        } else
        { // Не нашли программу?
            return false;
        }
        if ( $agenum === false )
        {
//            $currentagenum = $this->get_current_agenum_type_typeid($type, $typeid);
            // По каждому семестру выберем планируемые дисциплины:
//            for ( $agen = $currentagenum; $agen <= $agenums; $agen++ )
//            {
//                $planning = $this->get_planning_pitems($type, $typeid, $agen);
                $planning = $this->get_planning_pitems($type, $typeid, false);
                foreach ( $planning as $pitemid => $pitem )
                {
                    // Проверяем, что дисциплина обязательна и принадлежит нужному семестру
                    // (потому что при flowagenums == 1 в списке будут дисциплины из других семестров)
                    if ( !empty($pitem->required) )
                    {
                        $this->add_to_planned($type, $typeid, $pitem->agenum, $pitemid);
                    }
                }
//            }
        } else
        {
            $planning = $this->get_planning_pitems($type, $typeid, $agenum);
            foreach ( $planning as $pitemid => $pitem )
            {
                // Проверяем, что дисциплина обязательна и принадлежит нужному семестру
                // (потому что при flowagenums == 1 в списке будут дисциплины из других семестров)
                if ( !empty($pitem->required) AND $pitem->agenum == $agenum )
                {
                    $this->add_to_planned($type, $typeid, $agenum, $pitemid);
                }
            }
        }
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
        if ( ! empty($conds->name) )
        {// для имени используем шаблон LIKE
            $selects[] = " name LIKE '%".$conds->name."%' ";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->name);
        }
        if ( ! empty($conds->noid) )
        {
            $selects[] = " id != ".$conds->noid;
            // убираем условие из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->noid);
        }
        if ( isset($conds->agenum) AND $conds->agenum !== false )
        {
            $selects[] = " agenum = ".$conds->agenum;
            // убираем условие из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->agenum);
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
    
    /** Получить оценку дисциплины учебного плана по подписке
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $pitemid - id из таблицы programmitems
     * @return bool|string|int|null - оценка (int|string), null или false в случае ошибки
     */
    public function get_pitem_grade($type, $typeid, $pitemid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR !is_int_string($pitemid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        } else
        {
            if ( $type == 'agroup' )
            {
                // Если группа получала оценки, то нужно перебирать вручную:
                // $programmsbcids = storage('programmsbcs')->get_records('agroupid'=>$typeid)
                // foreach ( $programmsbcids as $programmsbcid ) {
                //     get_pitem_grade('programmsbc', $programmsbcid, $pitemid);
                // }
                return false;
            } else
            {
                $programmsbcid = $typeid;
            }
        }
        $params = new stdClass();
        $params->programmitemid = $pitemid;
        $params->programmsbcid  = $programmsbcid;
        // Изученные - завершённые, перезачтённые и активные.
        $params->status = array('completed','reoffset','active');
        $agenum = $this->get_field(array('type'=>$type,"{$type}id"=>$typeid,'programmitemid'=>$pitemid), 'agenum');
        if ( $agenum )
        {
            // Получим ageid по номеру семестра и добавим условие
            if ( $ageid = $this->dof->storage('learninghistory')->get_ageid_agenum($typeid, $agenum) )
            {
                $params->ageid = $ageid;
            }
        }
        $cpasseds = $this->dof->storage('cpassed')->get_listing($params, null, null, '', 'id,begindate,programmitemid,status,grade');
        // Статусы дисциплин (перезачёты, завершённые, текущие)
        $reoffsets = array();
        $completes = array();
        $actives   = array();
        foreach ( $cpasseds as $cpassed )
        { // Распределим дисциплины по статусам
            switch ( $cpassed->status )
            {
                case 'completed':
                    $completes[$cpassed->programmitemid] = $cpassed;
                    break;

                case 'active':
                    $actives[$cpassed->programmitemid] = $cpassed;
                    break;

                case 'reoffset':
                    if ( isset($reoffsets[$cpassed->programmitemid]) )
                    {
                        $reoffsets[$cpassed->programmitemid] = array();
                    }
                    $reoffsets[$cpassed->programmitemid][] = $cpassed;
                    break;

                default:
                    // Такого не может быть
                    dof_debugging('status: '. $cpassed->status, DEBUG_DEVELOPER);
                    return false;
            }
        }
        // Пройдёмся по перезачётам и убедимся, что был только один перезачёт
        foreach ( $reoffsets as $pitemid => $reoffset )
        {
            if ( $cpassed = $this->dof->storage('cpassed')->get_actual_reoffset_cpassed($reoffset) )
            { // Исключаем "лишние" перезачёты
                $reoffsets[$pitemid] = $cpassed;
                if ( array_key_exists($pitemid, $completes) )
                { // Завершённые подписки с пересдачей убираются
                    unset($completes[$pitemid]);
                }
            }
        }
        // Соберём все подписки на дисциплины
        $allcpasseds = $completes + $actives + $reoffsets;
        $pitems = array();
        // Из подписок достанем сами дисциплины и прикрепим к ним оценки, если они есть
        foreach ( $allcpasseds as $cpassed )
        {
            if ( $pitem = $this->dof->storage('programmitems')->get($cpassed->programmitemid, $this->get_programmitem_fields(true)) )
            {
                if ( $cpassed->status != 'active' )
                { // У завершённых и пересдач есть оценка
                    $pitem->grade = $cpassed->grade;
                } else
                { // У активных её нет
                    $pitem->grade = null;
                }
                $pitems[$pitem->id] = $pitem;
            }
        }
        // Возвратим оценку
        if ( isset($pitems[$pitemid]) )
        {
            return $pitems[$pitemid]->grade;
        }
        return false;
    }
    /** Получить статус дисциплины учебного плана (запланирована, в программе, изучена)
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param int $pitemid - id из таблицы programmitems
     * @return bool|string - одно из трёх возможных значений: 'planned', 'programm', 'learned' или false в случае ошибки
     */
    public function get_pitem_status($type, $typeid, $pitemid)
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid)
                          OR !is_int_string($pitemid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        } else
        {
            if ( $type == 'agroup' )
            {
                // Если группа, то нужно перебирать вручную:
                // $programmsbcids = storage('programmsbcs')->get_records('agroupid'=>$typeid)
                // foreach ( $programmsbcids as $programmsbcid ) {
                //     get_pitem_status('programmsbc', $programmsbcid, $pitemid);
                // }
                return false;
            } else
            {
                $programmsbcid = $typeid;
            }
        }
        // Проверим, запланирована ли уже дисциплина
        if ( $this->is_exists(array('type'=>$type,"{$type}id"=>$typeid,'programmitemid'=>$pitemid)) )
        {
            return 'planned';
        }
        // Определим, изучена ли дисциплина
        $params = new stdClass();
        $params->programmitemid = $pitemid;
        $params->programmsbcid  = $programmsbcid;
        // Изученные - завершённые, перезачтённые и активные.
        $params->status = array('completed','reoffset','active');
        $agenum = $this->get_field(array('type'=>$type,"{$type}id"=>$typeid,'programmitemid'=>$pitemid), 'agenum');
        if ( $agenum )
        {
            // Получим ageid по номеру семестра и добавим условие
            if ( $ageid = $this->dof->storage('learninghistory')->get_ageid_agenum($typeid, $agenum) )
            {
                $params->ageid = $ageid;
            }
        }
        $cpasseds = $this->dof->storage('cpassed')->get_listing($params, null, null, '', 'id,begindate,programmitemid,status,grade');
        // Статусы дисциплин (перезачёты, завершённые, текущие)
        $reoffsets = array();
        $completes = array();
        $actives   = array();
        foreach ( $cpasseds as $cpassed )
        { // Распределим дисциплины по статусам
            switch ( $cpassed->status )
            {
                case 'completed':
                    $completes[$cpassed->programmitemid] = $cpassed;
                    break;

                case 'active':
                    $actives[$cpassed->programmitemid] = $cpassed;
                    break;

                case 'reoffset':
                    if ( isset($reoffsets[$cpassed->programmitemid]) )
                    {
                        $reoffsets[$cpassed->programmitemid] = array();
                    }
                    $reoffsets[$cpassed->programmitemid][] = $cpassed;
                    break;

                default:
                    // Такого не может быть
                    dof_debugging('status: '. $cpassed->status, DEBUG_DEVELOPER);
                    return false;
            }
        }
        // Пройдёмся по перезачётам и убедимся, что был только один перезачёт
        foreach ( $reoffsets as $pitemid => $reoffset )
        {
            if ( $cpassed = $this->dof->storage('cpassed')->get_actual_reoffset_cpassed($reoffset) )
            { // Исключаем "лишние" перезачёты
                $reoffsets[$pitemid] = $cpassed;
                if ( array_key_exists($pitemid, $completes) )
                { // Завершённые подписки с пересдачей убираются
                    unset($completes[$pitemid]);
                }
            }
        }
        // Соберём все подписки на дисциплины
        $allcpasseds = $completes + $actives + $reoffsets;
        // Если хотя бы одна есть, то значит обучение по дисциплине ведётся / окончено
        if ( !empty($allcpasseds) )
        {
            return 'learned';
        }
        // Значит, дисциплина находится в программе
        return 'programm';
    }
    
    /** Выполнить подписку на запланированные дисциплины для текущей параллели указанного объекта
     * 
     * @param string $type - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup'
     * @param int $typeid - id из таблицы programmsbcs или agroups
     * @param array $options - дополнительные опции: array($pitemid => $options, ...), где 
     *              $options - объект с дополнительными параметрами
     * @return bool - результат операции
     */
    public function sign_planned_pitems_current_agenum($type, $typeid, $options = array())
    {
        if ( empty($type) OR empty($typeid)
                          OR !is_string($type)
                          OR !is_int_string($typeid) )
        {
            return false;
        }
        if ( $type != 'programmsbc' AND $type != 'agroup' )
        { // Не верный тип плана
            return false;
        }
        $info     = $this->get_learningplan_info($type, $typeid);
        $unsigned = $this->get_unsigned_pitems($type, $typeid, $info->agenum);
        // Достаём ageid из актуальной истории обучения
        if ( $type == 'programmsbc' )
        {
            $actual = $this->dof->storage('learninghistory')->get_actual_learning_data($typeid);
        } else
        {
            $actual = $this->dof->storage('agrouphistory')->get_actual_learning_data($typeid);
        }
        $ageid = 0;
        if ( $actual->ageid )
        {
            $ageid = $actual->ageid;
        } else
        {
            return false;
        }
        // Названия дополнительных настроек
        $optcolumns = array('appointmentid', 'cstreamid');
        // По каждому предмету создадим потоки и подпишем студента/группу
        foreach ( $unsigned as $pid => $pitem )
        {
            $optvalues = new stdClass();
            foreach ( $optcolumns as $column )
            {
                $optvalues->$column = 0;
            }
            if ( !empty($options) )
            {
                if ( isset($options[$pid]) )
                {
                    foreach ( $optcolumns as $column )
                    {
                        if ( isset($options[$pid]->$column) )
                        {
                            $optvalues->$column = $options[$pid]->$column;
                        }
                    }
                }
            }
            // Создаём потоки
            if ( $optvalues->cstreamid == 0 )
            {
                // Создаём поток
                $cstream = new stdClass();
                $age    = $this->dof->storage('ages')->get($ageid);
                $pitemb = $this->dof->storage('programmitems')->get($pid);
                $cstream->ageid          = $age->id;
                $cstream->programmitemid = $pid;
                $cstream->appointmentid  = $optvalues->appointmentid;
                // Количество недель сказано брать из периода
                $cstream->eduweeks = $age->eduweeks;
                if ( $pitemb->eduweeks )
                {// ..или из предмета, если указано там
                    $cstream->eduweeks = $pitemb->eduweeks;
                }
                // Количество часов возьмем из предмета
                $cstream->hours = $pitemb->hours;  
                // Количество часов в неделю возьмем из предмета
                $cstream->hoursweek = $pitemb->hoursweek;  
                // Подразделение возьмем из предмета
                $cstream->departmentid = $pitemb->departmentid;

                $cstream->teacherid = 0;
                if ( $cstream->appointmentid )
                {// Если есть назначение - найдем учителя
                    $cstream->teacherid = $this->dof->storage('appointments')->
                                           get_person_by_appointment($cstream->appointmentid)->id;
                }
                // Данные из периода
                $cstream->begindate  = $age->begindate;
                $cstream->enddate    = $age->enddate;
                // Часов в неделю дистанционно
                $cstream->hoursweekdistance = 0;
                // Часов в неделю очно
                $cstream->hoursweekinternally = 0;
                // Зарплатные коэффициенты
                $cstream->substsalfactor = 0; 
                $cstream->salfactor = 0;
                // Добавляем поток
                if ( $optvalues->cstreamid = $this->dof->storage('cstreams')->insert($cstream) )
                {// Всё в порядке - сохраняем статус и возвращаем на страниу просмотра класса
                    $this->dof->workflow('cstreams')->init($optvalues->cstreamid);
                } else
                {// Не получилось: сообщаем об ошибке
                    $this->dof->debugging('cstream not inserted', DEBUG_DEVELOPER);
                }
            } else
            {
                if ( ! $cstream = $this->dof->storage('cstreams')->get($optvalues->cstreamid) )
                {// Поток не найден
                    $this->dof->debugging('cstream id=' . $optvalues->cstreamid . ' not found', DEBUG_DEVELOPER);
                }
                // Если указано -- сменим учителя.
                if ( $optvalues->appointmentid )
                {// Если есть назначение - найдем учителя
                    $cstream->teacherid = $this->dof->storage('appointments')->
                                           get_person_by_appointment($optvalues->appointmentid)->id;
                    $cstream->appointmentid = $optvalues->appointmentid;
                    $this->dof->storage('cstreams')->update($cstream);
                }
            }
            // Поток получен и существует
            if ( $optvalues->cstreamid )
            { // Создаём подписки на указанные/созданные потоки
                if ( $type == 'agroup' )
                {
                    $this->dof->storage('cpassed')->sign_agroup_on_cstream($typeid, $optvalues->cstreamid);
                } else
                {
                    $this->dof->storage('cpassed')->sign_student_on_cstream($optvalues->cstreamid, $typeid);
                }
            }
        }
        return true;
    }
    
    /** Возвращает фрагмент sql-запроса c ORDER BY
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_orderby_listing($sort,$prefix='')
    {
        // по-умолчанию имя
        $sqlsort = $prefix.'programmitemid';
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
        $selects[] = $prefix.'programmitemid '.$dir;
        // возвращаем сортировку
        return implode($selects,',');
    }
    
    /**
     * Получить поля для дисциплины, необходимые для объекта learningplan
     * 
     * @param bool $implode - возвратить склеенную из полей строку?
     * @return array|string - массив из параметров или строка
     */
    private function get_programmitem_fields($implode = false)
    {
        // Основные поля
        $result = array('id', 'name', 'status', 'code', 'agenum', 'required', 'metaprogrammitemid');
        // Поля для расчёта часов
        $fields = array('maxcredit', 'hours', 'hourstheory', 'hourspractice',
                        'hoursweek', 'hourslab', 'hoursind', 'hourscontrol',
                        'hoursclassroom');
        if ( $implode )
        {
            return implode(',', $result + $fields);
        }
        return $result + $fields;
    }

}
?>