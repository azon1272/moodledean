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
class dof_storage_schpositions extends dof_storage implements dof_storage_config_interface
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
        return 'schpositions';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array('storage'=>array('positions'   => 2010040200,
                                      'departments' => 2010022700,
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
        return 'block_dof_s_schpositions';
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
    
    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     * 				 'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']     = array('roles'=>array('manager'));
        $a['edit']     = array('roles'=>array('manager'));
        $a['create']   = array('roles'=>array('manager'));
        $a['delete']   = array('roles'=>array());
        $a['use']      = array('roles'=>array('manager'));
        
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

    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
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
        // формируем строку запроса
        $select = $this->get_select_listing($conds);
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_select($select);
        }
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->get_records_select($select, null,'departmentid ASC', '*', $limitfrom, $limitnum);
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
    /** Создать несколько вакансий (используется в обработчике формы создания должности)
     * 
     * @return bool
     * @param int $number - количество вакансий, которые нужно создать
     * @param object $position - объект из таблицы positions с информацией о
     *                           должности, для которой создается вакансия
     * @param object $schposition - объект с данными о вакансии, со структурой соответствующей таблице schpositions.
     *                              поля из этого объекта записываются последними, поверх уже созданных
     */
    public function create_schpositions($number, $position, $schposition)
    {
        if ( ! is_numeric($number) OR $number < 0 )
        {// получены некорректные данные
            return false;
        }
        if ( ! is_object($position) )
        {// получены некорректные данные
            return false;
        }
        if ( ! $this->dof->storage('departments')->is_exists($position->departmentid) )
        {// получены некорректные данные
            return false;
        }
        if ( ! $this->dof->storage('positions')->is_exists($position->id) )
        {// получены некорректные данные
            return false;
        }
        if ( $position->status == 'canceled' )
        {// нельзя создавать вакансии на отмененные должности
            return false;
        }
        $result = true;
        for ( $i=0; $i<$number; $i++ )
        {// создаем нужное количество вакансий
            $obj = new stdClass();
            $obj->departmentid = $position->departmentid;
            $obj->positionid   = $position->id;
            if ( is_object($schposition) )
            {// если указаны дополнительные данные о вакансии - запишем их
                $obj = dof_object_merge($obj, $schposition);
            }
            
            $result = $result & (bool)$this->insert($obj);
        }
        return $result;
    }
}
?>