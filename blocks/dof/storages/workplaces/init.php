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

/** Справочник мест работы (связывает persons и organizations)
 * 
 */
class dof_storage_workplaces extends dof_storage implements dof_storage_config_interface
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
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2016071500;
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
        return 'workplaces';
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
        return 'block_dof_s_workplaces';
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
    
    /** Обработка AJAX-запросов из форм
     * @param string $querytype - тип запроса
     * @param int $objectid - id объекта с которым производятся действия
     * @param array $data - дополнительные данные пришедшие из json-запроса
     *
     * @return array
     */
    public function widgets_field_variants_list($querytype, $depid, $data)
    {
       // echo '!!!!!!!!!!!!!!';
        switch ( $querytype )
        {
            // список организаций для autocomplete-элемента          
            // результат содержит список организаций из базы в формате id => shortname
            case 'workplaces_list':    
                return $this->widgets_workplaces_list($depid, $data);
         
            default: return array(0 => $this->dof->modlib('ig')->igs('choose'));
        }
    }
    
    
    /** Получить список должностей по первым буквам названия должности
     * @todo добавить проверку прав: проверять что получатель списка имеет право "use" в storage/persons
     * @param int $departmenid - подразделение, в котором ищутся должности
     *                           если передан 0 - то должности ищутся во всех подразделениях
     * @param string $post - первые несколько букв названия должности
     *
     * @return array массив объектов для AJAX-элемента dof_autocomplete
     */
    protected function widgets_workplaces_list($departmentid, $post)
    {
        global $USER;
        
        $params = null;
        $post = clean_param($post, PARAM_TEXT);
        //получим имя таблицы
        $fulltablename = $this->prefix().
                       $this->tablename();
        //запрос(будем добавлять через get_records_sql, чтобы получить только уникальные должности)               
        $sql = 'SELECT DISTINCT ( post) AS post FROM '.$fulltablename.' 
                WHERE statuswork="active" AND post LIKE"'.$post.'%" ORDER BY post';
        $params = array();
        
        if ( ! $workplaces = $this->get_records_sql($sql,null,0,15))
        {// Нет организаций с такими данными
            return array();
        }

        // Формируем массив объектов нужной структуры для dof_autocomplete
        $result = array();
        foreach ( $workplaces as $workplace )
        {
            //если есть право
            if ($this->is_access('use', $workplace->id, $USER->id, $departmentid))
            {
                $obj = new stdClass;
                //$obj->id   = $workplace->id;
                $obj->name = $workplace->post;           
                $result[] = $obj;
            }
        }
        
        return $result;
    }
    
    /**
     * Обработчик добавления должности в справочник "workplaces"
     * @param string $type - имя параметра autocomplete-элемента
     * @param array $workplace - id организации от элемента autocomplete
     * @param int $personid - id персоны
     * @param int $orgid - id организации
     * @return null|bool - результат добавления должности в базу, null в особом случае
     */
    public function handle_workplace($type, $workplace, $personid, $orgid=0)
    {
        $value = $this->dof->modlib('widgets')->get_extvalues_autocomplete($type,$workplace);
        $obj = new stdClass;
        switch ( $value['do'] )
        {
            // переименовать
            case "rename":
                if ( !$this->is_access('use',$value['id']) OR 
                     !$this->is_exists($value['id']) )
                {// прав нет - пичалька
                    return false;
                }
                $obj->post = mb_convert_case($obj->post, MB_CASE_TITLE, "UTF-8");
                if ( $workplace->organizationid != $orgid )
                {//старую должность переводим в архивный статус
                    $obj = new stdClass();
                    $obj->statuswork = 'archive';
                    $obj->dismissaldate = time();
                }
                $this->update($obj,$value['id']);
                if ( empty($orgid) )
                {
                    return $value['id'];
                }
            // нужно создать запись
            case "create":
                if ( $this->is_exists(array('personid'=>$personid,'statuswork'=>'active')) )
                {// создавать с одинаковыми номерами пока нельзя
                    return false;
                }
                $obj = new stdClass();
                $obj->personid = $personid;
                $obj->receptiondate = time();
                $obj->statuswork = 'active';
                $obj->organizationid = $orgid;
                $obj->post = mb_convert_case($obj->post, MB_CASE_TITLE, "UTF-8");
                return $this->insert($obj);
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
        
        if ( !is_int_string($personid) OR !is_int_string($orgid) )
        {//проверка входных параметров
            return false;
        }
        //если есть активная должность для этой персоны
        if ( $workplace = $this->get_record(array('personid' => $personid, 'statuswork' => 'active'), 
                        'id,organizationid') )
        {
            //если изменили организацию
            if ( $workplace->organizationid != $orgid )
            {//старую должность переводим в архивный статус
                $obj = new stdClass();
                $obj->statuswork = 'archive';
                $obj->dismissaldate = time();
                if ( !$this->update($obj, $workplace->id) )
                {// не смогли архивировать - нельзя создать новую запись
                    return false;
                }
            }else
            {// просто обновим запись
                $obj = new stdClass();
                $obj->post = mb_convert_case($obj->post, MB_CASE_TITLE, "UTF-8");
                return $this->update($obj, $workplace->id);
            }
        }
        if ( empty($orgid) )
        {// создавать ничего не надо - все хорошо
            return true;
        }
        //создаем новую должность в активном статусе
        $obj = new stdClass();
        $obj->personid = $personid;
        $obj->receptiondate = time();
        $obj->statuswork = 'active';
        $obj->organizationid = $orgid;
        $obj->post = mb_convert_case($obj->post, MB_CASE_TITLE, "UTF-8");
        
        return $this->insert($obj);
    }
}

?>