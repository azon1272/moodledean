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

/** Учебные периоды
 * 
 */
class dof_im_schdays implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
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
        
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault()); 
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016032200;
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
        return 'angelfish';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'schdays';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'     => 2008060300,
                                     'widgets' => 2009050800) );
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
     * и без которых начать установку невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'=>2011040504));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
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
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $this->dof->modlib('nvg')->print_header(NVG_MODE_PORTAL);
            $notice = "schdays/{$do} (block/dof/im/schdays: {$do})";
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
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {

        return false;
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
        switch ($code)
        {
            case 'auto_create_days':
                return $this->auto_create_days($mixedvar->ageid,$mixedvar->departmentid,
                        $mixedvar->date, $mixedvar->daynum, $mixedvar->dayvar, $mixedvar->type); 
                break;
            case 'auto_create_events':     
                return $this->auto_create_events($mixedvar->ageid,$mixedvar->departmentid); 
                break;
            case 'auto_delete_events': 
                return $this->auto_delete_events($mixedvar->ageid,$mixedvar->departmentid) ; 
                break;
            case 'auto_update_events': 
                return $this->auto_update_events($mixedvar->ageid,$mixedvar->departmentid); 
                break;
            case 'auto_create_events_week':
                return $this->auto_create_events($mixedvar->ageid, $mixedvar->departmentid, $mixedvar->datestart, $mixedvar->dateend); 
                break;
            
        }
        return true;
    }
    /** Конструктор
     * @param dof_control $dof - идентификатор действия, которое должно быть совершено
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        $rez = '';

        return $rez;
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {

        }
        return $rez;
    }
     /** Возвращает текст, отображаемый в блоке на странице курса MOODLE 
      * @return string  - html-код для отображения
      */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im($this->code(),'/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
    }

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************   

    /** Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts 
     * и при редактировании контракта или персоны обращаться к ним
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
        return $a;
    }    
    

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Получить URL к собственным файлам плагина
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    
    /** Автоматическое создание дней на выбранный период
     * 
     * @param int $fageid - id из таблицы ages
     * @param int $fdepartmentid - id из таблицы departments
     * @param int $fdate - дата начала
     * @param int $fdaynum - день недели
     * @param int $fdayvar - тип недели (мигалка)
     * @param string $ftype - тип урока (working, holiday, vacation, dayoff)
     * @param int $fdateend [optional] - дата конца периода (null - берётся из учебного периода)
     * @return bool - результат операции (false - произошла ошибка в процессе)
     */
    public function auto_create_days($fageid, $fdepartmentid, $fdate, $fdaynum, $fdayvar, $ftype, $fdateend = null)
    {
        if ( !is_int_string($fageid)  OR !is_int_string($fdepartmentid) OR
             !is_int_string($fdate)   OR !is_int_string($fdaynum) OR
             !is_int_string($fdayvar) OR !is_string($ftype) OR
             (!is_null($fdateend) AND !is_int_string($fdateend)) )
        {
            return false;
        }
        $age = $this->dof->storage('ages')->get($fageid);
        if ( empty($age) )
        {
            return false;
        }
        $timezone = $this->dof->storage('departments')->get_timezone($fdepartmentid);
        $dbobj = new stdClass();
        $mdate = dof_usergetdate($fdate, $timezone); // время начала дня 
        $dbobj->date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
        $dbobj->daynum       = $fdaynum; // день недели
        $dbobj->dayvar       = $fdayvar; // тип недели
        $dbobj->type         = $ftype; // тип урока
        $dbobj->ageid        = $fageid; // id периода
        $dbobj->departmentid = $fdepartmentid; // id подразделения 
        // создаем начальный день
        $id = $this->dof->storage('schdays')->save_day($dbobj);
        $result = (bool)$id;
        $daynum = $fdaynum;
        $dayvar = $fdayvar;
        $date = $dbobj->date + DAYSECS;
        // Конечный период не передали
        if ( is_null($fdateend) )
        {
            $fdateend = $age->enddate;
        } else
        { // Передали, но некорректно, или дата конца меньше даты начала
            if ( !is_int_string($fdateend) OR $fdateend <= $fdate )
            {
                $fdateend = $age->enddate;
            }
        }
        for ( $i = $date; $i <= $fdateend; $i += DAYSECS )
        {
            $daynum++;

            if ( $daynum > $age->schdays )
            {
                $daynum = 1;
                $dayvar++;
            }
            if ( $dayvar > 2 )
            {
                $dayvar = 1;
            }
            $type = 'working';
            if ( ! preg_match("/{$daynum}/", $age->schedudays) )
            {
                $type = 'dayoff';
            }
            $dbobj->date         = $i; // время начала урока (уже в секундах)
            $dbobj->daynum       = $daynum; // день недели
            $dbobj->dayvar       = $dayvar; // тип недели
            $dbobj->type         = $type; // тип урока
            $dbobj->ageid        = $fageid; // id периода
            $dbobj->departmentid = $fdepartmentid; // id подразделения 

            // создаем начальный день
            $id = $this->dof->storage('schdays')->save_day($dbobj);
            $result = ($result AND (bool)$id);
        }
        return $result;
    }
    
    /** Автоматически создать расписание для указанного периода и подразделения
     * (опционально - создать в указанном промежутке времени)
     * 
     * @param int $ageid - id из таблицы ages
     * @param int $departmentid - id из таблицы departments
     * @param int $datestart [optional] - начало промежутка
     * @param int $dateend [optional] - конец промежутка
     * @param bool $returnerrors [optional] - возвратить ошибки в шаблонах в виде массива:
     *  ($schtemplateid => $template, ...)
     * @return bool|array - результат выполнения операции или массив шаблонов с ошибками
     */
    public function auto_create_events($ageid, $departmentid, $datestart = null, $dateend = null, $returnerrors = false)
    {
        // Создаём расписание только для указанных дат?
        $filterdate = false;
        if ( !is_null($datestart) OR !is_null($dateend) )
        {
            if ( !is_int_string($datestart) OR !is_int_string($dateend) )
            {
                return false;
            }
            $filterdate = true;
        }
        global $addvars;
        $_POST['ageid'] = $ageid;
        $_POST['departmentid'] = $departmentid;
        
        $conds = new stdClass;
        $conds->ageid = $ageid;
        $conds->departmentid = $departmentid;
        $conds->status = 'plan';
        
        if ( ! $days = $this->dof->storage('schdays')->get_listing($conds) )
        {
            return true;
        }

        $templateids = array();
        foreach ( $days as $day )
        {
            if ( $filterdate )
            { // Если день не попадает в выбранный период
                if ( $day->date < $datestart OR $day->date > $dateend )
                { // Пропускаем его
                    continue;
                }
            }
            $errors = $this->dof->storage('schevents')->create_from_templates($day->id);
            if ( empty($errors) )
            {// Ошибок нет -- изменим статус дня на активный.
                $this->dof->workflow('schdays')->change($day->id, 'active');
            } else
            {// Ошибки есть -- оставляем статус таким же
                $this->dof->workflow('schdays')->change($day->id, 'draft');
                $templateids[$day->id] = $errors;
            }
        }
        if ( $returnerrors )
        {
            return $templateids;
        }
        return true;
    }
    
    public function auto_delete_events($ageid,$departmentid)
    {
        global $addvars;
        $_POST['ageid'] = $ageid;
        $_POST['departmentid'] = $departmentid;
        require_once($this->dof->plugin_path('im','schdays','/lib.php'));
        $manager = new dof_im_schdays_schedule_manager($this->dof,$addvars);
        $conds = new stdClass;
        $conds->ageid = $ageid;
        $conds->departmentid = $departmentid;
        $conds->status = array('active','completed');
        
        if ( ! $days = $this->dof->storage('schdays')->get_listing($conds) )
        {
            return true;
        }
        
        foreach ( $days as $day )
        {
            $manager->delete_events_day($day->daynum,$day->dayvar,$day->id);
            $this->dof->workflow('schdays')->change($day->id, 'plan');
        }
        return true;
    }
    
    public function auto_update_events($ageid,$departmentid)
    {
        global $addvars;
        $_POST['ageid'] = $ageid;
        $_POST['departmentid'] = $departmentid;
        require_once($this->dof->plugin_path('im','schdays','/lib.php'));
        $manager = new dof_im_schdays_schedule_manager($this->dof,$addvars);
        $conds = new stdClass;
        $conds->ageid = $ageid;
        $conds->departmentid = $departmentid;
        $conds->status = array('plan','active','completed');
        
        if ( ! $days = $this->dof->storage('schdays')->get_listing($conds) )
        {
            return true;
        }

        foreach ( $days as $day )
        {
            if ( $day->status == 'plan' )
            {
                $manager->delete_events_day($day->daynum,$day->dayvar,$day->id);
                $this->dof->workflow('schdays')->change($day->id, 'plan');
            }
            $this->dof->storage('schevents')->create_from_templates($day->id);
            $this->dof->workflow('schdays')->change($day->id, 'active');
        }
        return true;
    }


}
?>