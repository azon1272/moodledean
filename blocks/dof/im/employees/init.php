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

/** 
 * Сотрудники
 */
class dof_im_employees implements dof_plugin_im
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * 
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * 
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
    /** 
     * Возвращает версию установленного плагина
     * 
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016060900;
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
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'im';
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
        return 'employees';
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
        return array('im'      => array('persons'       => 2010061600,
                                        'programmitems' => 2010030500),
                     'modlib'  => array('nvg'           => 2008060300,
                                        'widgets'       => 2009050800),
                     'storage' => array('eagreements'   => 2010040200,
                                        'appointments'  => 2010040200,
                                        'positions'     => 2010040200,
                                        'schpositions'  => 2010040200,
                                        'persons'       => 2010061600,
                                        'departments'   => 2010022700,
                                        'programmitems' => 2010012100,
                                        'acl'           => 2011040504) 
        );
    }
    /** 
     * Определить, возможна ли установка плагина в текущий момент
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
        return array('storage'=>array('acl'=>2011040504));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array(
            array('plugintype' => 'im',
                  'plugincode' => 'persons',
                  'eventcode'  => 'persondata'),
                  
            array('plugintype' => 'im',
                  'plugincode' => 'obj',
                  'eventcode'  => 'get_object_url'),
                  
            array('plugintype' => 'im',
                  'plugincode' => 'my',
                  'eventcode'  => 'info')
                  );
    }
    /** 
     * Требуется ли запуск cron в плагине
     *
     * @return bool
     * @access public
     */
    public function is_cron()
    {
       // Запуск раз в сутки
       return 3600 * 24;
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
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('manage') 
             OR $this->dof->is_access('admin') )
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
            $notice = "employees/{$do} (block/dof/im/employees: {$do})";
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
        $result = '';
        $depid = optional_param('departmentid', 0, PARAM_INT);
        
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {// отобразить все подписки персоны
            if ( $table = $this->get_table_eagreements($intvar, $depid) )
            {// у нас есть хотя бы один договор - выводим заголовок
                $heading = $this->dof->get_string('cldheader', $this->code());
                $result .= $this->dof->modlib('widgets')->print_heading($heading, '', 2, 'main', true);
                $result .= $table;
            }
            
            return $result;
        }
        
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' AND
             isset($mixedvar['storage']) AND isset($mixedvar['action']) )
        {
            $action = $mixedvar['action'];
            $params = $mixedvar['urlparams'];
            $params['departmentid'] = $depid;
            switch ( $mixedvar['storage'] )
            {
                case 'appointments': return $this->appointments_action_url($intvar, $action, $params);
                case 'eagreements':  return $this->eagreements_action_url($intvar, $action, $params);
                case 'positions':    return $this->positions_action_url($intvar, $action, $params);
                case 'schpositions': return $this->schpositions_action_url($intvar, $action, $params);
            }
        }
        
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info' ) 
        {
            $sections = array();
            if ( $this->get_section('my_eagreements') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>$this->code(),'name'=>'my_eagreements','id'=>1, 'title'=>$this->dof->get_string('title', $this->code()));
            }
            if ( $this->get_section('my_appointments') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>$this->code(),'name'=>'my_appointments','id'=>1, 'title'=>$this->dof->get_string('eagreement', $this->code()));
            }
            return $sections;
        }
        
        
        return false;
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
        $result = true;
        if ( $loan == 3 )
        {// генерацию отчетов запускаем только в режиме
            // самых ресурсоемких операций
            // @todo уточнить plugintype и plugincode для отчетов
            $result = $result && $this->dof->storage('reports')->generate_reports('sync', 'mreports');
        }
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
    
    /** 
     * Возвращает текст для отображения в блоке на странице dof
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        $rez = '';
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        switch ($name)
        {
            case 'main':
            {
            	if ($this->dof->im('employees')->is_access('viewstuff'))
            	{
	                //список должностей
	                $url = $this->dof->url_im('employees','/list_positions.php',$addvars);
	                $phrase = $this->dof->get_string('list_positions','employees');
	                $rez .= "<a href=\"{$url}\">{$phrase}</a><br />";
	                //список вакансий
	                $url = $this->dof->url_im('employees','/list_schpositions.php',$addvars);
	                $phrase = $this->dof->get_string('list_schpositions','employees');
	                $rez .= "<a href=\"{$url}\">{$phrase}</a><br />";
	                //список назначений
	                $url = $this->dof->url_im('employees','/list_appointeagreements.php',$addvars);
	                $phrase = $this->dof->get_string('list_appointeagreement','employees');
	                $rez .= "<a href=\"{$url}\">{$phrase}</a><br />";
	                //список сотрудников
	                $url = $this->dof->url_im('employees','/list.php',$addvars);
	                $phrase = $this->dof->get_string('page_main_name','employees');
	                $rez .= "<a href=\"{$url}\">{$phrase}</a><br />";
            	}
                break;
            }
        }
        return $rez;
    }
    /** 
     * Возвращает html-код, который отображается внутри секции
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 1)
    {
        global $USER;
        $rez = '';
        switch ($name)
        {
            case "my_eagreements":
                $depid = optional_param('departmentid', 0, PARAM_INT);
                $personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                return $this->get_table_eagreements($personid,$depid);
            case "my_appointments":
                $depid = optional_param('departmentid', 0, PARAM_INT);
                $personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                return $this->get_table_appointments($personid,$depid);
        }
        return $rez;
    }
    /** 
     * Возвращает текст, отображаемый в блоке на странице курса MOODLE 
     * 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('employees','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
    }
    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************    
    
    /** 
     * Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts 
     * и при редактировании контракта или персоны обращаться к ним
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $userid
     */
    protected function get_access_parametrs($action, $objectid, $userid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('eagreements')->get_field($objectid, 'departmentid');
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
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
    } 
    
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        // Право на просмотр страницы удаления работнка 
        $a['deleteperson'] = array('roles' => array('manager'));
        
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
    /**
     * Показать информацию о должности
     * 
     * @param int $id - id должности в таблице positions
     * @return bool - false в случае ошибок, true в случае успешного выполнения 
     */
    public function show_position($id,$addvars)
    {
        if ( ! is_int_string($id) )
        {// Входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('positions')->get($id) )
    	{// Должность не найдена
    		return false;
    	}
    	
        // Создаем массив, в который мы занесем все значения объекта из базы
        $position   = array();
        // добавим в раздел "действия" список возможных действий с иконками
        $actions    = $this->get_position_actions($id,$addvars);
        // получим название подразделения
        if ( $department = $this->dof->storage('departments')->get($obj->departmentid) )
        {// подразделение получено - покажем его название и код
            if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
            {// ссылка на подразделение (если есть права)
                $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
            } else
            {
                $department = $this->dof->storage('departments')->
                      get_field($obj->departmentid, 'name').' ['.
                      $this->dof->storage('departments')->get_field($obj->departmentid, 'code').']';
            }
        } else
        {// подразделение не указано - сообщим об этом
            $department = $this->dof->modlib('ig')->igs('no_specify');
        }
        
        // Перед выводом в таблицу получаем название статуса
        $status = $this->dof->workflow('positions')->get_name($obj->status);
        // Добавляем в итоговый результат весь список свойств
        $position[] = $actions;
        $position[] = $obj->name;
        $position[] = $obj->code;
        $position[] = $department;
        $position[] = $status;
        
        // Создаем таблицу с описанием полей и выводим результат
        print($this->print_single_table($position, 'position'));
        // Выполнено успешно
        return true;
    }
    
    /** Получить список действий, которые доступны для данной должности
     * 
     * @return string - html-код списка действий с иконками
     * @param int $id - id вакансии в таблице positions
     */
    private function get_position_actions($id,$addvars)
    {
        // Объявляем переменную для вывода результата
        $actions = '';
        // создаем html-код для изображения редактирования
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'"
            alt="'.$this->dof->modlib('ig')->igs('edit').'" title="'.
            $this->dof->modlib('ig')->igs('edit').'">';
        // создаем html-код для изображения просмотра
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'"
            alt="'.$this->dof->modlib('ig')->igs('view').'" title="'.
            $this->dof->modlib('ig')->igs('view').'">';
        // составляем ссылку для редактирования
        $actions .= '<a id="edit_position_'.$id.'" href="'.$this->dof->url_im($this->code(), '/edit_position.php?id='.$id,$addvars).'">'.$imgedit.'</a>';
        // создаем ссылку просмотра
        $actions .= '<a id="view_position_'.$id.'" href="'.$this->dof->url_im($this->code(), '/view_position.php?id='.$id,$addvars).'">'.$imgview.'</a>';
        return $actions;
    }
    /** Показать информацию о договоре с сотрудником
     * 
     * @todo добавить проверки для тех случаем, когда мы не получили объекты из базы
     * @return null
     * @param int $id - id договора в таблице eagreements
     */
    public function show_eagreement($id,$addvars)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('eagreements')->get($id) )
    	{// договор не найдена
    		return false;
    	}
        // создаем массив, в который мы занесем все значения объекта из базы
        $eagreement   = array();
        // добавим в раздел "действия" список возможных действий с иконками
        $actions    = $this->get_eagreement_actions($id,$addvars);
        // имя персоны
        $person = $this->dof->storage('persons')->get_fullname($obj->personid);
        // получим название подразделения
        if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
        {// ссылка на подразделение (если есть права)
            $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
        }else
        {
            $department = $this->dof->storage('departments')->get_field($obj->departmentid, 'name').' ['.
                      $this->dof->storage('departments')->get_field($obj->departmentid, 'code').']';
        }
        
        // список вакансий
        $schpositions = ' ';
        if ( $appointments = $this->dof->storage('appointments')->get_records(array(
                'eagreementid'=>$id)) )
        {// назначения по договору существуют - создаем список вакансий
            foreach ($appointments as $appointment)
            {
                if ( $position = $this->dof->storage('positions')->get($this->dof->storage('schpositions')->get_field(
                        $appointment->schpositionid, 'positionid')) )
                {
                    $worktime = $this->dof->storage('schpositions')->get_field($appointment->schpositionid, 'worktime'); 
                    $sp       = $position->name.'['.$position->code.']('.$worktime.')';
                    
                    $schpositions .= "<a href='".$this->dof->url_im('employees', '/view_schposition.php', 
                            array_merge($addvars, array('id' => $appointment->schpositionid)))."'>".$sp."</a><br/>";
                } 
            }
        }
        
        // перед выводом в таблицу получаем отображение статуса русскими буквами
        $status = $this->dof->workflow('eagreements')->get_name($obj->status);
        // добавляем в итоговый результат весь список свойств
        $eagreement[] = $actions;
        $eagreement[] = '<a href="'.$this->dof->url_im('persons', '/view.php?id='.$obj->personid,$addvars).'">'.$person.'</a>';
        $eagreement[] = $obj->num;
        $eagreement[] = dof_userdate($obj->date,'%d.%m.%Y');
        if ( $obj->begindate )
        {// дата начала
             $eagreement[] = dof_userdate($obj->begindate,'%d.%m.%Y');
        }else
        {// не указана
            $eagreement[] = $this->dof->modlib('ig')->igs('no_specify');
        }
        if ( $obj->enddate )
        {// дата окончания
             $eagreement[] = dof_userdate($obj->enddate,'%d.%m.%Y');
        }else
        {// не указана
            $eagreement[] = $this->dof->modlib('ig')->igs('no_specify');
        }
        $eagreement[] = $schpositions;
        $eagreement[] = $department;
        $eagreement[] = $status;
        // создаем таблицу с описанием полей и выводим результат
        print($this->print_single_table($eagreement, 'eagreement'));
    }
    
    /** Получить список действий, которые доступны для даннго договора с сотрудником
     * 
     * @return string - html-код списка действий с иконками
     * @param int $id - id договора в таблице eagreement
     */
    private function get_eagreement_actions($id,$addvars)
    {
        // объявляем переменную для вывода результата
        $actions = '';
        //создаем html-код для изображения редактирования
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'"
            alt="'.$this->dof->modlib('ig')->igs('edit').'" title="'.
            $this->dof->modlib('ig')->igs('edit').'">';
        // составляем ссылку для редактирования
        if ( $this->dof->im('journal')->is_access('view:salfactors') OR 
             $this->dof->im('journal')->is_access('view:salfactors/own',$personid)) 
        {
            $date = dof_userdate(time(), '%Y_%m');   
            $personid = $this->dof->storage('eagreements')->get_field($id,'personid');
            $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/report_user.png').'" 
                alt="'.$this->dof->get_string('view_teacher_salfactors',$this->code()).'" title="'.
                $this->dof->get_string('view_teacher_salfactors',$this->code()).'">';
            // добавляем ссылку
            $actions .= ' <a href="'.$this->dof->url_im('journal','/load_personal/loadpersonal.php',
                    $addvars+array('personid'=>$personid,'date'=>$date)).'">'.
                    $imgview.'</a>';
        }
        $actions .= '<a id="edit_eagreement_'.$id.'" href="'.$this->dof->url_im($this->code(), '/edit_eagreement_one.php?id='.$id,$addvars).'">'.$imgedit.'</a>';
        return $actions;
    }
    
    /** Показать информацию о вакансии
     * 
     * @return null
     * @param int $id - id вакансии в таблице schpositions
     */
    public function show_schposition($id,$addvars)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('schpositions')->get($id) )
    	{// должность не найдена
    		return false;
    	}
        // создаем массив, в который мы занесем все значения объекта из базы
        $schposition = array();
        // добавим в раздел "действия" список возможных действий с иконками
        $actions     = $this->get_schposition_actions($id,$addvars);
        // получим название подразделения
        if ( $department = $this->dof->storage('departments')->get($obj->departmentid) )
        {// подразделение получено - покажем его название и код
            if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
            {// ссылка на подразделение (если есть права)
                $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
            }else
            {
                $department = $department->name.' ['.$department->code.']';
            }
        }else
        {// подразделение не указано - сообщим об этом
            $department = $this->dof->modlib('ig')->igs('no_specify');
        }
        // получим название подразделения
        if ( $position = $this->dof->storage('positions')->get($obj->positionid) )
        {
            if ( $this->dof->storage('positions')->is_access('view', $obj->positionid) )
            {// Ссылка на должность если есть права
                $position = $position = '<a href="'.$this->dof->url_im('employees', 
                    '/view_position.php?id='.$obj->positionid,$addvars).'">'.
                    $position->name.' ['.$position->code.']</a>';
            }else
            {// название должности если прав нет
                $position = $position->name.' ['.$position->code.']';
            }
        }else
        {// должность не указана
            $position = $this->dof->modlib('ig')->igs('no_specify');
        }
        // перед выводом в таблицу получаем отображение статуса русскими буквами
        $status      = $this->dof->workflow('schpositions')->get_name($obj->status);
        // добавляем в итоговый результат весь список свойств
        $schposition[] = $actions;
        $schposition[] = $position;
        $schposition[] = round($obj->worktime, 2);
        $schposition[] = $department;
        $schposition[] = $status;
        // создаем таблицу с описанием полей и выводим результат
        print($this->print_single_table($schposition, 'schposition'));
    }
    /** Получить список действий, которые доступны для данной выкансии
     * 
     * @return string - html-код списка действий с иконками
     * @param int $id - id вакансии в таблице positions
     */
    private function get_schposition_actions($id,$addvars)
    {
        // объявляем переменную для вывода результата
        $actions = '';
        //создаем html-код для изображения редактирования
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'"
            alt="'.$this->dof->modlib('ig')->igs('edit').'" title="'.
            $this->dof->modlib('ig')->igs('edit').'">';
        // создаем html-код для изображения просмотра
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'"
            alt="'.$this->dof->modlib('ig')->igs('view').'" title="'.
            $this->dof->modlib('ig')->igs('view').'">';
        // составляем ссылку для редактирования
        $actions .= '<a id="edit_schposition_'.$id.'" href="'.$this->dof->url_im($this->code(), '/edit_schposition.php?id='.$id,$addvars).'">'.$imgedit.'</a>';
        $actions .= '<a id="view_schposition_'.$id.'" href="'.$this->dof->url_im($this->code(), '/view_schposition.php?id='.$id,$addvars).'">'.$imgview.'</a>';
        return $actions;
    }
    /** Показать информацию о вакансии
     * 
     * @return null
     * @todo добавить проверки для тех случаем, когда мы не получили объекты из базы
     * @todo разбить эту функцияю на несколько более простых
     * @param int $id - id вакансии в таблице schpositions
     */
    public function show_appointment($id,$addvars)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('appointments')->get($id) )
    	{// должность не найдена
    		return false;
    	}
        // создаем массив, в который мы занесем все значения объекта из базы
        $appointment = array();
        // добавим в раздел "действия" список возможных действий с иконками
        $actions     = $this->get_appointment_actions($id,$addvars);
        
        if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
        {// ссылка на подразделение (если есть права)
            $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
        }else
        {
            $department = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                      $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
        }
        
        $personid = $this->dof->storage('eagreements')->get_field($obj->eagreementid, 'personid');
        if ( $this->dof->storage('persons')->is_access('view', $personid) )
        {// ссылка на имя сотрудника, если есть права
            $person = $this->dof->im('persons')->get_fullname($personid, true);
        }else
        {// Просто ФИО сотрудника
            $person = $this->dof->storage('persons')->get_fullname($personid);
        }
        
        // Договор
        if ( $this->dof->storage('eagreements')->is_access('view', $obj->eagreementid) )
        {// покажем ссылку на договор
            $eagreementnum = '<a href="'.$this->dof->url_im('employees', '/view_eagreement.php',
                             array('id' => $obj->eagreementid)+$addvars).'">'.
                             $this->dof->storage('eagreements')->get_field($obj->eagreementid, 'num').
                             '</a>';
        }else
        {// просто название договора
            $eagreementnum = $this->dof->storage('eagreements')->get_field($obj->eagreementid, 'num');
        }
        
        // должность
        $positionid = $this->dof->storage('schpositions')->
                                get_field($obj->schpositionid, 'positionid');        
        if ( $this->dof->storage('positions')->is_access('view', $positionid) )
        {// Ссылка на должность если есть права
            $position = '<a href="'.$this->dof->url_im('employees', '/view_position.php',
            array('id' => $positionid)+$addvars).'">'.
            $this->dof->storage('positions')->get_field($positionid, 'name').' ['.
            $this->dof->storage('positions')->get_field($positionid, 'code').']'.
            '</a>';
        }else
        {// название должности если прав нет
            $position = $this->dof->storage('positions')->get_field($positionid, 'name').' ['.
                        $this->dof->storage('positions')->get_field($positionid, 'code').']';
        }
        
        // перед выводом в таблицу получаем отображение статуса русскими буквами
        $status      = $this->dof->workflow('appointments')->get_name($obj->status);
        
        
        // добавляем иконку просмотра должности
        $viewimage = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'" 
            alt="'.$this->dof->modlib('ig')->igs('change').'" title="'.
            $this->dof->modlib('ig')->igs('change').'">';
        // делаем иконки для просмотра ссылками
        $pstatusimage  = '&nbsp;<a id="view_position_'.$positionid.'" href="'.$this->dof->url_im($this->code(), 
                '/view_position.php?id='.$positionid,$addvars).'">'.
                $viewimage.'</a>';
        $estatusimage  = '&nbsp;<a id="view_eagreement_'.$obj->eagreementid.'" href="'.$this->dof->url_im($this->code(), 
                '/view_eagreement.php?id='.$obj->eagreementid,$addvars).'">'.
                $viewimage.'</a>';
        $spstatusimage = '&nbsp;<a id="view_schposition_'.$obj->schpositionid.'" href="'.$this->dof->url_im($this->code(), 
                '/view_schposition.php?id='.$obj->schpositionid,$addvars).'">'.
                $viewimage.'</a>';
        // также дополнительно выводим статус должности 
        $pstatus     = $this->dof->workflow('positions')->
                            get_name($this->dof->storage('positions')->get_field($positionid, 'status')).
                            $pstatusimage;
        // статус договора
        $estatus     = $this->dof->workflow('eagreements')->
                            get_name($this->dof->storage('eagreements')->get_field($obj->eagreementid, 'status')).
                            $estatusimage;
        // статус вакансии
        $spstatus    = $this->dof->workflow('schpositions')->
                            get_name($this->dof->storage('schpositions')->get_field($obj->schpositionid, 'status')).
                            $spstatusimage;
        // вакансия
        $splink = '<a href='.$this->dof->url_im($this->code(), '/view_schposition.php', 
                array('id' => $obj->schpositionid, 'departmentid' => $addvars['departmentid'])).'>'
                .'<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'" 
                alt="'.$this->dof->modlib('ig')->igs('view').'" title="'.
                $this->dof->modlib('ig')->igs('view').'">'.'</a>';
        
        // количество часов, не занятых текущим табельным номером
        $sptimeleft = $this->dof->storage('appointments')->get_free_worktime($obj->schpositionid);
        
        // добавляем в итоговый результат весь список свойств
        $appointment[] = $actions;
        $appointment[] = $eagreementnum.' ['.$person.']'.'<br/>'
                . $this->dof->get_string('status', $this->code()) . '&nbsp;['.$estatus.']';
        $appointment[] = $position.'<br/>'. $this->dof->get_string('status', $this->code()) 
                . '&nbsp;['.$pstatus.']';
        $appointment[] = $obj->enumber;
        $combination = $this->dof->modlib('ig')->igs('no');
        if ( !empty($obj->combination) )
        {// совместитель
            $combination = $this->dof->modlib('ig')->igs('yes');
        }
        $appointment[] = $combination;
        $appointment[] = $splink . '<br/>' . $this->dof->get_string('worktime',$this->code()) . '&nbsp;['
                . round($obj->worktime, 2) . ']<br/>' . $this->dof->get_string('schposition_time_left',$this->code()) 
                . '&nbsp;['. round($sptimeleft) .']<br/>' . $this->dof->get_string('status', $this->code()) 
                . '&nbsp;['. $spstatus.']';
        $appointment[] = dof_userdate($obj->date,'%d.%m.%Y');
        if ( $obj->begindate )
        {// дата начала
             $appointment[] = dof_userdate($obj->begindate,'%d.%m.%Y');
        }else
        {// не указана
            $appointment[] = $this->dof->modlib('ig')->igs('no_specify');
        }
        if ( $obj->enddate )
        {// дата окончания
             $appointment[] = dof_userdate($obj->enddate,'%d.%m.%Y');
        }else
        {// не указана
            $appointment[] = $this->dof->modlib('ig')->igs('no_specify');
        }
        $appointment[] = $department;
        $appointment[] = $status;
        //$appointment[] = '&nbsp;';
        //$appointment[] = $estatus;
        //$appointment[] = $pstatus;
        //$appointment[] = $spstatus;
        
        // создаем таблицу с описанием полей и выводим результат
        print($this->print_single_table($appointment, 'appointment'));
    }
    /** Получить список действий, которые доступны для данной выкансии
     * 
     * @return string - html-код списка действий с иконками
     * @param int $id - id вакансии в таблице positions
     */
    private function get_appointment_actions($id,$addvars)
    {
        // объявляем переменную для вывода результата
        $actions = '';
        // @todo подобрать иконки
        //создаем html-код для изображения редактирования
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'"
            alt="'.$this->dof->get_string('edit_appointment',$this->code()).'" title="'.
            $this->dof->get_string('edit_appointment',$this->code()).'">';
        // составляем ссылку для редактирования
        $actions .= '<a id="edit_appointment_'.$id.'" href="'.$this->dof->url_im($this->code(), '/edit_appointment.php?id='.$id,$addvars).'">'.$imgedit.'</a>';
        $eagreementid = $this->dof->storage('appointments')->get_field($id, 'eagreementid');
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view-eagreement.png').'" 
            alt="'.$this->dof->get_string('view_eagreement',$this->code()).'" title="'.
            $this->dof->get_string('view_eagreement',$this->code()).'">';
        // добавляем ссылку
        $actions .= ' <a id="view_eagreement_'.$eagreementid.'" href="'.$this->dof->url_im($this->code(), '/view_eagreement.php?id='.$eagreementid,$addvars).'">'.
                $imgview.'</a>';
        return $actions;
    }
    /** Показать информацию о предмете
     * 
     * @return string возвращает или html-код таблицы или 
     * непосредственно вывод ее на экран
     * @param int $id - id предмет в таблице programmitems
     * @param bool $onlyhtml - сразу распечатать (=true) или 
     * вернуть строку (по умолчанию)
     */
    public function show_programmitem($id, $onlyhtml=false)
    {
        return $this->dof->im('programmitems')->
               print_short_info_table($id, $onlyhtml);
    }
    
    /** Получить список действий, которые доступны для данного предмета
     * 
     * @return string - html-код списка действий с иконками
     * @param int $id - id педмета в таблице programmitems
     */
    private function get_programmitem_actions($id)
    {
        // объявляем переменную для вывода результата
        $actions = '';
        //создаем html-код для изображения редактирования
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'"
            alt="'.$this->dof->modlib('ig')->igs('view').'" title="'.
            $this->dof->modlib('ig')->igs('view').'">';
        // составляем ссылку для редактирования
        $actions .= '<a href="'.$this->dof->url_im('programmitems', '/view.php?pitemid='.$id,$addvars).'">'.$imgview.'</a>';
        return $actions;
    }
    
    /** Отрисовать таблицу с назначениями на должности
     * 
     * @param array $list - выборка записей из таблицы appointments
     * @param array $addvars - дополнилеотные параметры для формирования ссылок
     * @return bool|string - html-код или false в случае ошибки
     */
    public function show_list_appointeagreements($list, $addvars, $options=null)
    {
        if ( ! is_array($list) OR empty($list) )
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
    	foreach ($list as $obj)
    	{   
            // создаем массив, в который мы занесем все значения объекта из базы
            $appointment   = array();
            // получаем из базы полную информацию о назначении на должность
            $appobj = $this->dof->storage('appointments')->get($obj->id);
            // Получаем информацию о договоре с сотрудником
            $eagreement = $this->dof->storage('eagreements')->get($appobj->eagreementid);
            // получаем назначение на должность
            $schposition = $this->dof->storage('schpositions')->get($appobj->schpositionid);
            // добавим в раздел "действия" список возможных действий с иконками
            $actions = $this->get_appointeagreement_actions_for_list($obj->id, $addvars);
            // перед выводом в таблицу получаем отображение статуса русскими буквами
            $status = $this->dof->workflow('appointments')->get_name($obj->status);
            $check = '';
            if ( is_array($options) )
            {// добавляем галочки
                $check = '<input id="id_transfer_object_'.$obj->id.'" type="checkbox" name="'.$options['prefix'].'_'.
                $options['listname'].'['.$obj->id.']" value="'.$obj->id.'"/>';
            }             
            
            // добавляем в итоговый результат весь список свойств
            $appointment[] = $check;
            $appointment[] = $actions;
            
            // ФИО сотрудника
            if ( $this->dof->storage('persons')->is_access('view', $eagreement->personid) )
            {
                $appointment[] = '<a href="'.$this->dof->url_im('persons', '/view.php',
                                 array('id' => $eagreement->personid)+$addvars).
                                 '">'.$obj->name.'</a>';
            }else
            {
                $appointment[] = $obj->name;
            }
            
            // Должность
            if ( $this->dof->storage('positions')->is_access('view', $schposition->positionid) )
            {
                $appointment[] = '<a href="'.$this->dof->url_im('employees', '/view_position.php',
                                 array('id' => $schposition->positionid)+$addvars).
                                 '">'.$obj->posname.'['.$obj->code.']'.'</a>';
            }else
            {
                $appointment[] = $obj->posname.'['.$obj->code.']';
            }
            
            
            // Номер договора с сотрудником
            if ( $this->dof->storage('eagreements')->is_access('view', $appobj->eagreementid) )
            {// со ссылкой
                $appointment[] = '<a href="'.$this->dof->url_im('employees', '/view_eagreement.php',
                                 array('id' => $appobj->eagreementid)+$addvars).
                                 '">'.$obj->num.'</a>';
            }else
            {// и без ссылки :)
                $appointment[] = $obj->num;
            }
            
            // Табельный номер
            if ( $this->dof->storage('appointments')->is_access('view', $obj->id) )
            {
                $appointment[] = '<a href="'.$this->dof->url_im('employees', '/view_appointment.php',
                                  array('id' => $obj->id)+$addvars).'">'.
                                  $obj->enumber.'</a>';
            }else
            {
                $appointment[] = $obj->enumber;
            }
            $combination = $this->dof->modlib('ig')->igs('no');
            if ( $appobj->combination )
            {
                $combination = $this->dof->modlib('ig')->igs('yes');
            }
            $appointment[] = $combination;
            // Ставка
            $appointment[] = round($obj->worktime, 2);
            $appointment[] = $status;
            // Добавим в результирующий массив
            $appointments[$obj->id]= $appointment;
        }
        // Рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = array ("center","center","center","center","center","center","center","center");
        // Шапка
        $table->head =  $this->get_fields_description('appointment_list',$addvars);
        // Заносим данные
        $table->data = $appointments;
        
        // Возвращаем html код таблицы 
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Отобразить список должностей
     * 
     * @param array $list - массив записей из таблицы positions
     * @return string|bool - html-код таблицы со списком должностей, или false если данные не найдены 
     */
    public function show_list_positions($list,$addvars)
    {
        if ( ! is_array($list) OR empty($list) )
        {// переданны данные неверного формата или их просто нет
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
        foreach ($list as $obj)
    	{
    	    if ( $department = $this->dof->storage('departments')->get($obj->departmentid) )
            {
                if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
                {// ссылка на подразделение (если есть права)
                    $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
                }else
                {
                    $department = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                              $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
                }
            }else
            {// попытаемся получить подразделение - либо сразу сообшим что его нет
                $department = $this->dof->modlib('ig')->igs('no_specify');
            }
            // создаем массив, в который мы занесем все значения объекта из базы
            $position   = array();
            // добавляем действия
            $position[] = $this->get_position_actions($obj->id,$addvars);
            // название должности
            if ( $this->dof->storage('positions')->is_access('view', $obj->id) )
            {
                $position[] = '<a href="'.$this->dof->url_im('employees', '/view_position.php',
                                array('id' => $obj->id)+$addvars).'">'.
                                $obj->name.'</a>';
            }else
            {
                $position[] = $obj->name;
            }
            
            // добавляем код должности
            $position[] = $obj->code;
            // добавляем название подразделение
            $position[] = $department;
            // добавляем статус
            $position[] = $this->dof->workflow('positions')->get_name($obj->status);
            // добавляем все данные в общий массив
            $positions[$obj->id] = $position;
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign  = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = array("center","center","center","center","center");
        // шапка таблицы
        $table->head = $this->get_fields_description('position_list');
        // заносим данные в таблицу
        $table->data = $positions;
        // возвращаем код таблицы
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    /** Отобразить список вакансий
     * 
     * @param array $list - массив записей из таблицы positions
     * @return string|bool - html-код таблицы со списком должностей, или false если данные не найдены 
     */
    public function show_list_schpositions($list,$addvars)
    {
        if ( ! is_array($list) OR empty($list) )
        {// переданны данные неверного формата или данных нет
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
        foreach ($list as $obj)
    	{
    	    if ( $department = $this->dof->storage('departments')->get($obj->departmentid) )
            {// получим название и код подразделения
                if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
                {// ссылка на подразделение (если есть права)
                    $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
                }else
                {
                    $department = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                              $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
                }
            }else
            {// попытаемся получить подразделение - либо сразу сообшим что его нет
                $department = $this->dof->modlib('ig')->igs('no_specify');
            }
            if ( $position = $this->dof->storage('positions')->get($obj->positionid) ) 
            {// получим название и код подразделения
                if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
                {
                    $position = '<a href="'.$this->dof->url_im('employees', '/view_position.php',
                                array('id' => $position->id)+$addvars).'">'.
                                $position->name.' ['.$position->code.']</a>';
                }else
                {
                    $position = $position->name.' ['.$position->code.']';
                }
            }else
            {// попытаемся получить подразделение - либо сразу сообшим что его нет
                $position = $this->dof->modlib('ig')->igs('no_specify');
            }
            // создаем массив, в который мы занесем все значения объекта из базы
            $schposition   = array();
            // добавляем действия
            $schposition[] = $this->get_schposition_actions($obj->id,$addvars);
            // добавляем название подразделения
            $schposition[] = $department;
            // добавляем название должности
            $schposition[] = $position;
            // добавляем ставку в часах
            $schposition[] = round($obj->worktime, 2);
            // добавляем статус
            $schposition[] = $this->dof->workflow('schpositions')->get_name($obj->status);
            // добавляем все данные в общий массив
            $schpositions[$obj->id] = $schposition;
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign  = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = array("center","center","center","center","center");
        // шапка таблицы
        $table->head = $this->get_fields_description('schposition_list');
        // заносим данные в таблицу
        $table->data = $schpositions;
        // возвращаем код таблицы
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    /** Отобразить список сотрудников
     * 
     * @param array $list - массив записей из таблицы eagreements
     * @return string|bool - html-код таблицы со списком должностей, или false если данные не найдены 
     */
    public function show_list_employees($list,$addvars,$options=null)
    {
        if ( ! is_array($list) OR empty($list) )
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
        foreach ($list as $obj)
    	{
            if ( $obj->begindate )
            {// выведем дату начала работы в удобном формате
                $begindate = dof_userdate($obj->begindate,'%d-%B-%Y');
            }else
            {// дата начала работы не указана
                // @todo добавить эту ошибку в лог когда будут возможности для этого
                $begindate = $this->dof->modlib('ig')->igs('no_specify');
            }
            // получаем номер договора, и делаем его ссылкой
            $eanum = '<a href="'.$this->dof->url_im('employees', '/view_eagreement.php?id='.$obj->id,$addvars).
                    '">'.$obj->num.'</a>';
            // получаем ФИО и делаем его ссылкой
            $fullname = $this->dof->im('persons')->get_fullname($obj->personid,true,$obj); 
            // создаем массив, в который мы занесем все значения объекта из базы
            $employee   = array();
            // добавляем ФИО
            $check = '';
            if ( is_array($options) )
            {// добавляем галочки
                $check = '<input type="checkbox" name="'.$options['prefix'].'_'.
                 $options['listname'].'['.$obj->id.']" value="'.$obj->id.'"/>';
            }
            $employee[] = $check;
            // добавляем действия
            $employee[] = $this->get_employee_actions($obj->id,$addvars);
            $employee[] = $fullname;
            // добавляем номер договора
            $employee[] = $eanum;
            // добавляем дату начала работы
            $employee[] = $begindate;
            // добавляем статус
            $employee[] = $this->dof->workflow('eagreements')->get_name($obj->status);
            // добавляем все данные в общий массив
            $employees[$obj->id] = $employee;
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign  = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = array("center","left","center","center","center","center");
        // шапка таблицы
        $table->head = $this->get_fields_description('employee_list',$addvars);
        // заносим данные в таблицу
        $table->data = $employees;
        // возвращаем код таблицы
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** 
     * Получить список действий, которые доступны для даннго договора с сотрудником
     * 
     * @param int $id - ID назначения на должность (табельного номера в таблице appointments)
     * @param array $addvars - массив дополнительных параметров для формирования ссылки
     * 
     * @return string - html-код списка действий с иконками
     */
    private function get_appointeagreement_actions_for_list($id, $addvars )
    {
        // Объявляем переменную для вывода результата
        $actions = '';
        
        // Создаем html-код для изображения редактирования назначения
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'"
            alt="'.$this->dof->get_string('edit_appointment',$this->code()).'" title="'.
            $this->dof->get_string('edit_appointment',$this->code()).'">';
        // Составляем ссылку для редактирования
        $actions .= '<a id="edit_appointment_'.$id.'" href="'.$this->dof->url_im($this->code(), 
            '/edit_appointment.php?id='.$id,$addvars).'">'.$imgedit.'</a>';
        
        // Создаем html-код для изображения просмотра назначения
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'" 
            alt="'.$this->dof->get_string('view_appointment',$this->code()).'" title="'.
            $this->dof->get_string('view_appointment',$this->code()).'">';
        // Добавляем ссылку
        $actions .= ' <a id="view_appointment_'.$id.'" href="'.$this->dof->url_im($this->code(), 
            '/view_appointment.php?id='.$id,$addvars).'">'.$imgview.'</a>';
        
        // Разделяем 2 группы иконок на 2 части
        $actions .= '<br/>';
        
        // Создаем html-код для изображения редактирования договора
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit-eagreement.png').'"
            alt="'.$this->dof->get_string('edit_eagreement',$this->code()).'" title="'.
            $this->dof->get_string('edit_eagreement',$this->code()).'">';
        // Составляем ссылку для редактирования договора
        $eagreementid = $this->dof->storage('appointments')->get_field($id, 'eagreementid');
        $actions .= ' <a id="edit_eagreement_'.$eagreementid.'" href="'.$this->dof->url_im($this->code(), 
            '/edit_eagreement_one.php?id='.$eagreementid,$addvars).'">'.$imgedit.'</a>';
        
        // Создаем html-код для изображения просмотра договора
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view-eagreement.png').'" 
            alt="'.$this->dof->get_string('view_eagreement',$this->code()).'" title="'.
            $this->dof->get_string('view_eagreement',$this->code()).'">';
        // Добавляем ссылку
        $actions .= ' <a id="view_eagreement_'.$eagreementid.'" href="'.$this->dof->url_im($this->code(), 
            '/view_eagreement.php?id='.$eagreementid,$addvars).'">'.
                $imgview.'</a>';
        
        $personid = $this->dof->storage('eagreements')->get_field($eagreementid,'personid');
        if ( $this->dof->im('journal')->is_access('view:salfactors') OR 
             $this->dof->im('journal')->is_access('view:salfactors/own',$personid) ) 
        {
            $date = dof_userdate(time(), '%Y_%m');   
            $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/report_user.png').'" 
                alt="'.$this->dof->get_string('view_teacher_salfactors',$this->code()).'" title="'.
                $this->dof->get_string('view_teacher_salfactors',$this->code()).'">';
            // Добавляем ссылку
            $urlparams = $addvars + array('personid'=>$personid,'date'=>$date);
            if ( empty($urlparams['personid']) )
            {
                $urlparams['personid'] = $personid;
            }
            $actions .= ' <a href="'.$this->dof->url_im('journal','/load_personal/loadpersonal.php',
                    $urlparams).'">'.
                    $imgview.'</a>';
        }
        
        // Кнопка удаления работника
        if ( $this->dof->im('employees')->is_access('deleteperson', $id) )
        {
            // Получить статус
            $status = $this->dof->storage('persons')->get_field($personid, 'status');
            if ( $status != 'deleted' )
            {// Если персона не удалена
                // Массив параметров для ссылки на удаление персоны
                $somevars = array();
                $somevars['departmentid'] = $addvars['departmentid'];
                $somevars['id'] = $id;
            
                // Создаем html-код для изображения удаления персоны по назначению
                $imgdel = '<img src="'.$this->dof->url_im($this->code(), '/icons/delete.png').'" 
                             alt="'.$this->dof->get_string('delete_person', $this->code()).'" 
                             title="'.$this->dof->get_string('delete_person', $this->code()).'">';
                // Добавляем ссылку
                $actions .= '<a href="'.$this->dof->url_im($this->code(), 
                            '/delete_person.php', $somevars).'">'.
                            $imgdel.
                        '</a>';
            }
        }
        
        return $actions;
    }
    
    /** Получить список действий, которые доступны для даннго сотрудника
     * 
     * @return string - html-код списка действий с иконками
     * @param int $id - id договора в таблице eagreements
     */
    public function get_employee_actions($id,$addvars)
    {
        // объявляем переменную для вывода результата
        $actions = '';
        // добавляем иконку просмотра договора с сотрудником
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'" 
            alt="'.$this->dof->get_string('view_eagreement',$this->code()).'" title="'.
            $this->dof->get_string('view_eagreement',$this->code()).'">';
        // добавляем ссылку
        $actions .= ' <a id="view_eagreement_'.$id.'" href="'.$this->dof->url_im($this->code(), 
            '/view_eagreement.php?id='.$id,$addvars).'">'.
                $imgview.'</a>';
        // составляем ссылку для редактирования
            // Добавляем ссылку
        $personid = $this->dof->storage('eagreements')->get_field($id,'personid');
        if ( $this->dof->im('journal')->is_access('view:salfactors') OR 
             $this->dof->im('journal')->is_access('view:salfactors/own',$personid)) 
        {
            $date = dof_userdate(time(), '%Y_%m');   
            $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/report_user.png').'" 
                alt="'.$this->dof->get_string('view_teacher_salfactors',$this->code()).'" title="'.
                $this->dof->get_string('view_teacher_salfactors',$this->code()).'">';
            // добавляем ссылку
            $urlparams = $addvars + array('personid'=>$personid,'date'=>$date);
            if ( empty($urlparams['personid']) )
            {
                $urlparams['personid'] = $personid;
            }
            $actions .= ' <a href="'.$this->dof->url_im('journal','/load_personal/loadpersonal.php',
                    $urlparams).'">'.
                    $imgview.'</a>';
        }
        return $actions;
    }
    /** Отобразить список табельных номеров для договора
     * 
     * @param int $eagreementid - id договора из таблицы eagreements
     * @return string|bool - html-код таблицы со списком должностей, или false если данные не найдены 
     */
    public function show_piteam_for_eagreement($appointmentid,$addvars)
    {
        $statuses = $this->dof->workflow('teachers')->get_list();
        $select = 'appointmentid='.$appointmentid." AND status != 'canceled'";
        if ( isset($addvars['departmentid']) AND $addvars['departmentid'] )
        {// есть подразделение, добавим в поиск
             //$select .= " AND departmentid = ".$addvars['departmentid'];
        }
        if ( ! $teachers = $this->dof->storage('teachers')->get_records_select($select) )
        {// нет предметов, которые преподает сотрудник
            return '';
        }
        $data = array();
        foreach ( $teachers as $teacher )
        {// предметы есть, выведем их
            $programmitem = array();
            $programmid = $this->dof->storage('programmitems')->get_field($teacher->programmitemid,'programmid');
            $programmitem[] = '<a href="'.$this->dof->url_im('programms', 
                                      '/view.php?programmid='.$programmid,$addvars).'">'.
                              $this->dof->storage('programms')->
                                      get_field($programmid,'name').' ['.
                              $this->dof->storage('programms')->
                                      get_field($programmid,'code').']</a>';  
            $programmitem[] = '<a href="'.$this->dof->url_im('programmitems', 
                                      '/view.php?pitemid='.$teacher->programmitemid,$addvars).'">'.
                              $this->dof->storage('programmitems')->
                                      get_field($teacher->programmitemid,'name').' ['.
                              $this->dof->storage('programmitems')->
                                      get_field($teacher->programmitemid,'code').']</a>';
            $programmitem[] = $statuses[$teacher->status];
             // @todo иконку;
            $programmitem[] = '<a href="'.$this->dof->url_im('employees', '/view_appointment.php?id='.$appointmentid,$addvars).'">'.
                              $this->dof->get_string('edit_programmitem',$this->code()).'</a>';
                              // добавляем все данные в общий массив
            $data[$teacher->id] = $programmitem;
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign  = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = array("center","left","left","center","center");
        // шапка таблицы
        $table->head = $this->get_fields_description('enumber_for_eagreement');
        // заносим данные в таблицу
        $table->data = $data;
        // возвращаем код таблицы
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    /** Отобразить список табельных номеров для договора
     * 
     * @param int $eagreementid - id договора из таблицы eagreements
     * @return string|bool - html-код таблицы со списком должностей, или false если данные не найдены 
     */
    public function show_enumber_for_eagreement($eagreementid,$addvars)
    {
        if ( ! $appointments = $this->dof->storage('appointments')->get_records(array('eagreementid'=>$eagreementid)))
        {// переданны данные неверного формата
            return '';
        }
        $data = array();
        $bigtable = '';
    	// заносим данные в таблицу
        foreach ($appointments as $appointment)
    	{
    	    // рисуем таблицу
            $table = new stdClass();
            $table->tablealign  = "center";
            $table->cellpadding = 5;
            $table->cellspacing = 5;
            $table->align = array("center");
            // шапка таблицы
    	    if ( $positionid = $this->dof->storage('schpositions')->
    	                           get_field($appointment->schpositionid,'positionid') )
            {// найдем должность
                $posname = $this->dof->storage('positions')->get_field($positionid,'name').' ['.
                           $this->dof->storage('positions')->get_field($positionid,'code').']';
            }else
            {// нету ее
                 $posname = '';
            }
            $table->head = array($this->dof->get_string('enumber',$this->code()).
    	                         ' <a href="'.$this->dof->url_im('employees', 
        	                         '/view_appointment.php?id='.$appointment->id,$addvars).'">№'
                                     .$appointment->enumber.'</a><br>'.
        	                     $this->dof->get_string('position',$this->code()).
        	                     ': <a href="'.$this->dof->url_im('employees', 
        	                         '/view_position.php?id='.$positionid,$addvars).'">'.
        	                         $posname.'</a>');
            // возвращаем код таблицы
            $bigtable .= $this->dof->modlib('widgets')->print_table($table,true).
                         $this->show_piteam_for_eagreement($appointment->id,$addvars).'<br>';
    	}
    	return $bigtable;
    }
    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * 
     * @return string
     * @param object $data объект с отображаемыми значениями
     * @param string $type - тип элемента, для которого будет составляться таблица
     */
    private function print_single_table($data, $type)
    {
        $table = new stdClass();
        if ( ! $data )
        {
            return '';
        }
        // получаем подписи с пояснениями
        $descriptions = $this->get_fields_description($type);
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.current(each($descriptions)).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить список полей таблицы
     * для отображения одного объекта 
     * 
     * @todo использовать плагин ig для всех стандартных строк
     * @param string $type - тип отображаемого объекта
     * @return array
     */
    private function get_fields_description($type,$addvars=null)
    {
        // получим предшествующий способ сортировки
        $addvars['orderby'] = optional_param('orderby', 'ASC', PARAM_TEXT);
        // поменяем сортировку на противоположную  
        if ( $addvars['orderby'] == 'ASC' )
        {
            $oderby = 'DESC';
        }else
        {
            $oderby = 'ASC';
        }
        // перемним наш массив с параметрами
        $addvars['orderby'] = $oderby;
        switch ($type)
        {
            // строки таблицы для отображения одной должности
            case 'position': return array($this->dof->modlib('ig')->igs('actions'),
                                          $this->dof->get_string('name',$this->code()),
                                          $this->dof->get_string('code',$this->code()),
                                          $this->dof->get_string('department',$this->code()),
                                          $this->dof->modlib('ig')->igs('status'));
            break;
            // столбцы таблицы для отображения списка должностей
            case 'position_list': return array($this->dof->modlib('ig')->igs('actions'),
                                               $this->dof->get_string('name',$this->code()),
                                               $this->dof->get_string('code',$this->code()),
                                               $this->dof->get_string('department',$this->code()),
                                               $this->dof->modlib('ig')->igs('status'));
            break;
            // строки таблицы для отображения 
            // договоров с сотрудниками
            case 'eagreement': return array($this->dof->modlib('ig')->igs('actions'),
                                            $this->dof->get_string('name_employee',$this->code()),
                                            $this->dof->get_string('num',$this->code()),
                                            $this->dof->get_string('date',$this->code()),
                                            $this->dof->get_string('begindate',$this->code()),
                                            $this->dof->get_string('enddate',$this->code()),
                                            $this->dof->get_string('schpositions',$this->code()),
                                            $this->dof->get_string('department',$this->code()),
                                            $this->dof->modlib('ig')->igs('status'));
            break;
            // строки таблицы для отображения вакансий
            case 'schposition': return array($this->dof->modlib('ig')->igs('actions'),
                                             $this->dof->get_string('position',$this->code()),
                                             $this->dof->get_string('worktime',$this->code()),
                                             $this->dof->get_string('department',$this->code()),
                                             $this->dof->modlib('ig')->igs('status'));
            
            break;
            // строки таблицы для отображения вакансий
            case 'schposition_list': return array($this->dof->modlib('ig')->igs('actions'), 
                                             $this->dof->get_string('department',$this->code()),
                                             $this->dof->get_string('position',$this->code()),
                                             $this->dof->get_string('worktime',$this->code()),
                                       		 $this->dof->modlib('ig')->igs('status'));
            
            break;
            // строки таблицы для отображения одного назначения на должность
            case 'appointment': return array($this->dof->modlib('ig')->igs('actions'),
                                             $this->dof->get_string('eagreement',$this->code()),
                                             $this->dof->get_string('position',$this->code()),
                                             $this->dof->get_string('enumber',$this->code()),
                                             $this->dof->get_string('combination',$this->code()),
                                             $this->dof->get_string('schposition',$this->code()),
                                             $this->dof->get_string('date',$this->code()),
                                             $this->dof->get_string('begindate',$this->code()),
                                             $this->dof->get_string('enddate',$this->code()),
                                             $this->dof->get_string('department',$this->code()),
                                             $this->dof->modlib('ig')->igs('status'));
                                             //'&nbsp;',
                                             //$this->dof->get_string('status_eagreement',$this->code()),
                                             //$this->dof->get_string('status_position',$this->code()),
                                             //$this->dof->get_string('status_schposition',$this->code()));
            // Столбцы таблицы для отображения списка назначений на должность
            case 'appointment_list': return array('', $this->dof->modlib('ig')->igs('actions'),
                                            '<a href="'.$this->dof->url_im('employees','/list_appointeagreements.php?sort=sortname',$addvars).'">'.
                                                $this->dof->modlib('ig')->igs('fio').'</a>',
                                             '<a href="'.$this->dof->url_im('employees','/list_appointeagreements.php?sort=name',$addvars).'">'.   
                                                 $this->dof->get_string('position',$this->code()).'</a>',
                                             $this->dof->get_string('eagreement',$this->code()),
                                             $this->dof->get_string('enumber',$this->code()),
                                             $this->dof->get_string('combination',$this->code()),
                                             $this->dof->get_string('worktime',$this->code()),
                                             $this->dof->modlib('ig')->igs('status'));
            break;
            // строки таблицы для отображения сотрудника
            // @todo удалить если не пригодится
            case 'employee': return array(
                                             $this->dof->modlib('ig')->igs('actions'),
                                             $this->dof->modlib('ig')->igs('fio'),
                                             $this->dof->get_string('department',$this->code()),
                                             $this->dof->get_string('positions',$this->code()),
                                             $this->dof->get_string('startdate',$this->code()),
                                             $this->dof->modlib('ig')->igs('status'));
            
            break;
            // Столбцы таблицы для отображения списка сотрудников
            case 'employee_list': return array('', $this->dof->modlib('ig')->igs('actions'),
                            				'<a href="'.$this->dof->url_im('employees','/list.php',$addvars).'">'.$this->dof->modlib('ig')->igs('fio').'</a>',
                                             $this->dof->get_string('eagreement',$this->code()),
                                             $this->dof->get_string('startdate1',$this->code(), '<br/>'),
                                             $this->dof->modlib('ig')->igs('status'));
            
            break;
             // Столбцы таблицы для отображения списка сотрудников
            case 'enumber_for_eagreement': return array(
                                             $this->dof->get_string('programm',$this->code()),
                                             $this->dof->get_string('pitems_available',$this->code()),
                                             $this->dof->modlib('ig')->igs('status'),
                                             $this->dof->modlib('ig')->igs('actions'));
            
            break;
            // Столбцы для отображения информации по предмету
            case 'programmitem': return array(
                                             $this->dof->get_string('name',$this->code()),
                                             $this->dof->get_string('code',$this->code()),
                                             $this->dof->get_string('programm',$this->code()),
                                             $this->dof->get_string('agenum',$this->code()),
                                             $this->dof->get_string('hoursweek',$this->code()),
                                             //$this->dof->get_string('freedomhours',$this->code()),
                                             $this->dof->modlib('ig')->igs('status'),
                                             $this->dof->modlib('ig')->igs('actions'));
            
            break;
            default: return array();
        }
    }
    
	
	/** Проверить данные из массива формы добавления/удаления предметов
	 * 
	 * @param array $data
	 * @return array - массив с проверенными безопасными данными
	 */
	public function check_add_remove_array($data)
	{
		$result = array();
		if ( ! is_array($data) )
		{// переданы неверные данные
			return false;
		}
		foreach ($data as $item)
		{// перебираем весь список идентификаторов, и приводим его к нормальному виду
			if ( ! is_numeric($item) )
			{// если значение не числовое - пропустим его и не внесем в итоговый массив
				continue;
			}
			// если значение числовое - запишем его в итоговый массив
			$result[] = intval($item);
		}
		
		return $result;
	}
    
    /** Получить таблицу со списком учителей для переданного предмета.
     * Поля таблицы:
     * - порядковый номер,
     * - ФИО (ссылка) .
 	 * - табельный номер (ссылка).
     * - номер договора (ссылка).
     * 
     * @return bool|string 
     * @param object $pitemid - id педмета в таблицы programmitems
     * @parem bool $return[optional] - если true, то только вернуть html-код таблицы, не распечатывая ее
     */
    public function get_teachers_table_for_pitem($pitemid,$addvars, $return=false)
    {
        if ( ! $teachers = $this->dof->storage('teachers')->get_teachers_for_pitem($pitemid) )
        {// не получили учителей - этот предмет пока никто не преподает, так и скажем об этом
            $message = '<p align="center">'.
                        $this->dof->get_string('this_programmitem_has_no_teachers', $this->code()).'</p>';
            if ( $return )
            {// вернем сообщение
                return $message;
            }else
            {// распечатаем сообщение
                print($message);
                return null;
            }
        }
        // создаем объект таблицы, и задаем ее заголовок
        $table = new stdClass();
        $table->head = array('№', 
                             $this->dof->get_string('fio', $this->code()),
                             $this->dof->get_string('eagreement', $this->code()),
                             $this->dof->get_string('enumber', $this->code()));
        $table->align = array('center', 'center', 'center', 'center');
        $tabledata = array();
        foreach ( $teachers as $teacher )
        {// перебираем всех учителей и собираем для каждого нужные данные
            // @todo сортировать их каким-нибудь более  адекватным образом чем по индексу массива
            if ( ! $appointment = $this->dof->storage('appointments')->get($teacher->appointmentid) )
            {// не найдено назначение на должность
                // @todo это означает ошибку целостности базы данных - в будущем надо будет записать в лог  
                continue;
            }else
            {// делаем ссылку на запись
                $appointmentlink = '<a href="'.$this->dof->url_im($this->code(), 
                '/view_appointment.php?id='.$appointment->id,$addvars).'">'.
                $appointment->enumber.'</a>';
            }
            if ( ! $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
            {// договор не найден
                // @todo это означает ошибку целостности базы данных - в будущем надо будет записать в лог
                continue;
            }else
            {// делаем ссылку на запись
                $eagreementlink = '<a href="'.$this->dof->url_im($this->code(), 
                '/view_eagreement.php?id='.$eagreement->id,$addvars).'">'.
                $eagreement->num.'</a>';
            }
            if ( ! $person = $this->dof->storage('persons')->get($eagreement->personid) )
            {// пользователь с таким id не найден
                // @todo это означает ошибку целостности базы данных - в будущем надо будет записать в лог
                continue;
            }else
            {// делаем ссылку на запись
                $personlink = '<a href="'.$this->dof->url_im('persons', '/view.php?id='.$person->id,$addvars).'">'.
                $person->sortname.'</a>';
            }
            // записываем в массив все данные об учителе 
            $tabledata[$person->sortname.$appointment->id] = array($personlink, $eagreementlink, $appointmentlink);
        }
        // сортируем варварским способом всех немногочисленных найденных учителей
        ksort($tabledata);
        
        $recordnum = 1;
        foreach ( $tabledata as $datarecord )
        {// записываем  учителей в таблицу, присвоив им порядковые номера
            $table->data[] = array_merge(array($recordnum), $datarecord);
            $recordnum++;
        }
        // распечатываем таблицу, либо возвращаем ее html-код
        return $this->dof->modlib('widgets')->print_table($table, $return);
    }
    
    /**
     * Возвращает список персон которые могут преподавать 
     * указанный предмет или уже преподают его
     * @param int $pitemid - id предмета
     * @param bool $already - указатель кого надо вернуть - 
     * того кто уже преподает (true) или не преподает, но может преподавать (false)
     * @param bool $enum - добавлять табельный номер (true) или нет (false)
     * @return array - массив пустой или объектоы с полями 
     * enumber - табельный номер
     * status - статус teacher, если есть
     * appointmentid - id назначения на должность
     * fullname - ФИО
     */
    public function get_pitem_teachers($pitemid, $already, $enum=true)
    {
        if ( $already )
        {//получим преподов, которые преподают указанный предмет
            $appteachers = $this->dof->storage('teachers')->get_teachers_for_pitem($pitemid);
            if ( ! $appteachers )
            {//не получили
                return array();
            }
            //Получим записи персон преподов
            $teachers = $this->dof->storage('teachers')->get_persons_with_appid($appteachers, $enum);
        }else
        {//получим преподов, которые могут преподавать этот предмет
            $appointments = $this->dof->storage('teachers')->get_teachers_no_pitem($pitemid);
            if ( ! $appointments )
            {
                return array();
            }            
            //получим персон на этих номерах
            $teachers = $this->dof->storage('appointments')->
                       get_persons_by_appointments($appointments);
        }
        
        if ( ! $teachers )
        {//не получили
            return array();
        }
        //формируем список для меню
        $rez = array();
        foreach ( $teachers as $one )
        {
            $teacher = new stdClass();
            $teacher->appointmentid = $one->appointmentid;
            $teacher->enumber = $one->enumber;
            $teacher->fullname = $this->dof->storage('persons')->get_fullname($one);
            if ( isset($one->teacherstatus) )
            {
                $teacher->status = $one->teacherstatus;
            }else
            {
                $teacher->status = '';
            }
            if ( isset($one->worktime) )
            {
                $teacher->worktime = round($one->worktime, 2).'/'.
                round($this->dof->storage('appointments')->get_field($one->appointmentid,'worktime'),2);
            }else
            {
                $teacher->worktime = '0/'.
                round($this->dof->storage('appointments')->get_field($one->appointmentid,'worktime'),2);
            }
            $rez[$one->appointmentid] = $teacher; 
                
        }
        return $rez;
    }    
    

    
//    /**
//     * Возвращает объект приказа
//     *
//     * @param string $code
//     * @param integer  $id
//     * @return dof_storage_orders_baseorder
//     */
//    public function order($code, $id = NULL)
//    {
//        require_once($this->dof->plugin_path('im','ages','/order/change_status.php'));
//        switch ($code)
//        {
//            case 'change_status':
//                $order = new dof_im_ages_order_change_status($this->dof);
//                if ( ! is_null($id))
//                {// нам передали id, загрузим приказ
//                    if ( ! $order->load($id))
//                    {// Не найден
//                        return false;
//                    }
//                }
//                // Возвращаем объект
//                return $order;
//            break;
//        }
//    }

    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function report($code, $id = NULL)
    {
        return $this->dof->storage('reports')->report('sync', 'mreports', $code, $id);
    }

   /**
    * Возвращает вкладки на сотрудники/список должностей/список вакансий/ список должостных назначений
    * @param string $id -идентификатор,определяет какая вкладка активна в данный момент
    * @param arrrya $addvars - массив параметров GET(подразделение)  
    * @param bool   $subtab - флаг поключения вкладки 2-ого уровня
    * @return смешанную строку 
    */
    public function print_tab($addvars, $id, $subtab=false)
    {
        // соберем данные для вкаладок
        $tabs = array();
        // сотрудники
        if ( $this->dof->storage('eagreements')->is_access('view') )
        {
            $link = $this->dof->url_im($this->code(),'/list.php',$addvars);
            $text = $this->dof->get_string('eagreements', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('eagreements', $link, $text, NULL, true);
        }
        
        // Список должностей
        if ( $this->dof->storage('positions')->is_access('view') )
        {
            $link = $this->dof->url_im($this->code(),'/list_positions.php',$addvars);
            $text = $this->dof->get_string('positions', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('positions', $link, $text, NULL, true);
        }
        
        // Список вакансий
        if ( $this->dof->storage('schpositions')->is_access('view') )
        {
            $link = $this->dof->url_im($this->code(),'/list_schpositions.php',$addvars);
            $text = $this->dof->get_string('schpositions', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('schpositions', $link, $text, NULL, true);
        }
        // должностных назначений
        if ( $this->dof->storage('appointments')->is_access('view') )
        {
            $link = $this->dof->url_im($this->code(),'/list_appointeagreements.php',$addvars);
            $text = $this->dof->get_string('appointeagreement', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('appointments', $link, $text, NULL, true);
        }
        if ( $subtab )
        {
            $output  = $this->dof->modlib('widgets')->print_tabs($tabs, $id, NULL, NULL, true);
            $output .= "<div>".$this->print_subtab($addvars, $id)."</div>";
            return $output;
        }
        return $this->dof->modlib('widgets')->print_tabs($tabs, $id, NULL, NULL, true);
    }
    
    /** 
     * Получить таблицу с должностями
     * @param int $intvar - id персоны
     * @param int $depid - id департамента
     * @return string - html-код таблицы или пустая строка
     * @access public
     */
    public function get_table_eagreements($intvar, $depid = 0) 
    {

        $result = '';
        
        $conditions = array('personid' => $intvar,
                                'status'   => array('plan', 'active'));
        if ( ! $eagreements = $this->dof->storage('eagreements')->get_records($conditions) )
        {// нет договоров - ничего не отображаем
            return '';
        }
        $result .= $this->show_list_employees($eagreements, array('departmentid' => $depid));
            
        return $result;
    }
    
    /** 
     * Получить таблицу с назначенями на должности
     * @param int $intvar - id персоны
     * @param int $depid - id департамента
     * @return string - html-код таблицы или пустая строка
     * @access public
     */
    public function get_table_appointments($intvar, $depid = 0) 
    {
        $result = '';
        // $intvar пустой - берем текущего пользователя 
        if (!$intvar)
        {
            $intvar = $this->dof->storage('persons')->get_bu();
        }
        
        $conditions = array('personid' => $intvar,
                'status' => 'active');

        // получаем массив договоров пользователя
        $appointments = $this->dof->storage('appointments')->get_listing(
                    $conditions,null,null,false,true);

        if (empty($appointments))
        {// нет назначений - ничего не отображаем
            return '';
        }
        // Массив параметров для ссылок
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $result .= $this->show_list_appointeagreements($appointments, $addvars);
        
        return $result;
    }
    
    //////////////////////////////////////////////////////////////
    // Методы получения URL для совершения действий с объектами //
    //////////////////////////////////////////////////////////////
    
    /** Получить url для совершения действия с объектом хранилища appointments
     * (Должностные назначения)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function appointments_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            $urlparams = array_merge($urlparams, array('id' => $id));
            return $this->url('/view_appointment.php', $urlparams);
        }
    }
    /** Получить url для совершения действия с объектом хранилища eagreements
     * (договора с сотрудниками)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function eagreements_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            $urlparams = array_merge($urlparams, array('id' => $id));
            return $this->url('/view_eagreement.php', $urlparams);
        }
    }
    /** Получить url для совершения действия с объектом хранилища positions
     * (должности)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function positions_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            $urlparams = array_merge($urlparams, array('id' => $id));
            return $this->url('/view_position.php', $urlparams);
        }
    }
    /** Получить url для совершения действия с объектом хранилища schpositions
     * (вакансии)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function schpositions_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            $urlparams = array_merge($urlparams, array('id' => $id));
            return $this->url('/view_schposition.php', $urlparams);
        }
    }
    
    /** Вывод вкладки 2-ого уровня
     * @param string $id -идентификатор,определяет какая вкладка активна в данный момент
     * @param arrrya $addvars - массив параметров GET(подразделение)
     * @return смешанную строку
     */
    protected function print_subtab($addvars, $id)
    {// соберем данные для вкаладок
        $tabs = array();
        $obj = new stdClass(); 
        
        if ( !$obj = $this->dof->storage($id)->get_record(array( 'id' => $addvars['id'])) )
        {// тип верхней вкладки неверен или запись отсутствует - вернем пустую строку
            return '';
        }
        
        switch ($id)
        {// формируем данные для текущей вкладки
            case "eagreements":
                $listlink = '/list.php';
                $link_r = $this->dof->url_im($this->code(), '/view_eagreement.php', $addvars); 
                $text_r = $this->dof->get_string('eagreement_str', $this->code()).':&nbsp;'.$obj->num;
                break;
            
            case "appointments":
                $listlink = '/list_appointeagreements.php';
                $link_r = $this->dof->url_im($this->code(), '/view_appointment.php', $addvars);
                $text_r = $this->dof->get_string('enumber', $this->code()).':&nbsp;'.$obj->enumber;
                break;
                    
            case "positions":
                $listlink = '/list_positions.php';
                $link_r = $this->dof->url_im($this->code(), '/view_position.php', $addvars);
                $text_r = $this->dof->get_string('position', $this->code()).':&nbsp;'.$obj->name.'['.$obj->code.']';
                break;
                        
            case "schpositions":
                $record = $this->dof->storage('schpositions')->get_record( array(
                        'id'=>$addvars['id']));
                
                $position = $this->dof->storage('positions')->get_record(array(
                        'id'=>$record->positionid), 'name,code');
                
                $listlink = '/list_schpositions.php';
                $link_r = $this->dof->url_im($this->code(), '/view_schposition.php', $addvars);
                $text_r = $this->dof->get_string('schposition', $this->code()).':&nbsp;'
                        .$position->name.'['.$position->code.']('.$record->worktime.')';
                break;

            default: return '';
        }
        
        // создаем вкладку возврата "все"
        unset($addvars['id']);
        $link_w = $this->dof->url_im($this->code(), $listlink, $addvars);
        $text_w = $this->dof->get_string('whole_list', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('whole', $link_w, $text_w, NULL, true);
        // создаем вкладку с текущим объектом
        $tabs[] = $this->dof->modlib('widgets')->create_tab('record', $link_r, $text_r, NULL, true);
        
        // готовим для вывода
        $massiv = array();
        $massiv = $tabs;
        return $this->dof->modlib('widgets')->print_tabs($massiv, 'record', NULL, NULL, true);
    }
}