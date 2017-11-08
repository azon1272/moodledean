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

// подключение интерфейса
//require_once($DOF->plugin_path('storage','acl','/interface_acl.php'));
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Приемная комиссия
 * 
 */
class dof_im_sel implements dof_plugin_im,  dof_storage_config_interface 
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
	/** Метод, реализующий удаление плагина в системе  
	 * @return bool
	 */
	public function uninstall()
	{
		return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),array());
	}
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016060900;
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
        return 'sel';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'   => array('nvg'=>2008102300),
					 'storage'  => array('contracts'=>2008101600,
                                         'config' => 2011040500,
                                         'persons'=>2008110100,
                                         'acl'=>2011040504,
                                         'organizations' => 2012102500,
                                         'workplaces' => 2012102500,
                                         'metacontracts' => 2012102500
                                         ),
        			 'workflow' => array('contracts'=>2008102200),
        		     'im'       => array('persons'=>2008110100)
                    );
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
        return array('storage'=>array('acl'    => 2011040504,
                                      'config' => 2011080900));
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
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
		// Этому плагину не нужен крон
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
        // проверяем права, используя систему полномочий acl
        if ( $this->acl_access_check($do, $objid, $userid) )
        {// права есть - все нормально
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
        if (!$this->is_access($do, $objid, $userid))
        {
            $link = "{$this->type()}/{$this->code()}:{$do}";
            $notice = '';
            if ($objid){$notice.="#{$objid}";}
            $this->dof->print_error('nopermissions',$link,$notice); 
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
        $result = '';
        require_once($this->dof->plugin_path('im', 'sel', '/lib.php') );
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {// отобразить все подписки персоны
            if ( $table = $this->get_table_contracts($intvar) )
            {// у нас есть хотя бы один договор - выводим заголовок
                $heading = $this->dof->get_string('title', $this->code());
                $result .= $this->dof->modlib('widgets')->print_heading($heading, '', 2, 'main', true);
                $result .= $table;
            }
            
            return $result;
        }
        
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'contracts' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('id' => $intvar);
                    if ( isset($mixedvar['urlparams']) AND is_array($mixedvar['urlparams']) )
                    {
                        $params = array_merge($params, $mixedvar['urlparams']);
                    }
                    return $this->url('/contracts/view.php', $params);
                }
            }
        }
            
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info' )
        {
            $sections = array();
            if ( $this->get_section('my_contracts') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>$this->code(),'name'=>'my_contracts','id'=>1, 'title'=>$this->dof->get_string('title', $this->code()));
            }
            if ( $this->get_section('my_contracts_client') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>$this->code(),'name'=>'my_contracts_client','id'=>1, 'title'=>$this->dof->get_string('ward_contracts', $this->code()));
            }
            return $sections;

        }
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
    /** Возвращает содержимое блока
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код названия блока
     */
    function get_block($name, $id = 1)
    {
        /*
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
		switch ($name)
		{
       		case 'main':
				$str = '';
				/*
				if ($this->dof->im('sel')->is_access('view'))
				{
       		 		$str = $str.'<a href="'.$this->dof->url_im('sel').'">'
							.$this->dof->get_string('title', 'sel').'</a>';
				}
				*/
				/*
				if ($this->dof->im('sel')->is_access('openaccount'))
				{
       		 		$str = $str.'<a href="'.$this->dof->url_im('sel/contracts/edit_first.php','',$addvars).'">'
							.$this->dof->get_string('newcontract', 'sel').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?byseller=1','',$addvars).'">'
							.$this->dof->get_string('mycontracts', 'sel').'</a>';
				}
				if ($this->dof->im('sel')->is_access('viewaccount'))
				{
					$str = $str."<br />{$this->dof->get_string('contractlist', 'sel')}:";
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php','',$addvars).'">'
							.$this->dof->get_string('contractall', 'sel').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=tmp','',$addvars).'">'
							.$this->dof->get_string('status:tmp','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=new','',$addvars).'">'
							.$this->dof->get_string('status:new','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=studreg','',$addvars).'">'
							.$this->dof->get_string('status:studreg','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=wesign','',$addvars).'">'
							.$this->dof->get_string('status:wesign','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=work','',$addvars).'">'
							.$this->dof->get_string('status:work','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=frozen','',$addvars).'">'
							.$this->dof->get_string('status:frozen','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=cancel','',$addvars).'">'
							.$this->dof->get_string('status:cancel','contracts',NULL,'workflow').'</a>';
       		 		$str = $str.'<br /><a href="'.$this->dof->url_im('sel/contracts/list.php?status=archives','',$addvars).'">'
							.$this->dof->get_string('status:archives','contracts',NULL,'workflow').'</a>';
				}
				return $str;
			break;	
		}
        */
    }
    /** Возвращает содержимое секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код названия секции
     */
    function get_section($name, $id = 1)
    {
        global $USER;
        switch ($name)
        {
            case "my_contracts":
                $personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                return $this->get_table_contracts($personid);
            
            case "my_contracts_client";
                $personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                return $this->get_table_contracts_client($personid);
        }
    	
        return '';
    }
     /** Возвращает текст для отображения в блоке dof
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
		return "";
    }

    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     * 				 'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        // при обновлении - создаються
        $a = array();
        // право смотреть свои договоры для законного представителя и куратора
        $a['view/seller'] = array('roles'=>array('manager'));
        $a['view/parent'] = array('roles'=>array('parent'));
        // право создавать новый договор
        $a['openaccount'] = array('roles'=>array('manager'));
        // @todo в изначальной версии для этого права был комментарий "можно только бухалтеру"
        // нужно уточнить что за роль "бухалтер" и как ее интерпретировать в нашей системе
        $a['payaccount']   = array('roles'=>array('manager'));
        $a['changestatus'] = array('roles'=>array('manager'));
        
        return $a;
    }

    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        // Включить/отключить плагин
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Регион в форме регистрации пользователя
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'defaultregion';
        $obj->value = 'RU-MOW';
        $config[$obj->code] = $obj;  
        // Обязательность заполнения удостоверения личности для ЗП
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredclpasstype';
        $obj->value = '0';
        $config[$obj->code] = $obj;   
        // Обязательность заполнения email для ЗП
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredclientemail';
        $obj->value = '0';
        $config[$obj->code] = $obj;   
        // Обязательность заполнения удостоверения личности для студента
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredstpasstype';
        $obj->value = '0';
        $config[$obj->code] = $obj;   
        // Обязательность заполнения отчества для ЗП
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredclmiddlename';
        $obj->value = '0';
        $config[$obj->code] = $obj;  
        // Обязательность заполнения отчества для студента
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'requiredstmiddlename';
        $obj->value = '0';
        $config[$obj->code] = $obj;       
        return $config;
    }
    
    /** Получить список параметров для фунции has_hight()
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
            $result->departmentid = $this->dof->storage('contracts')->get_field($objectid, 'departmentid');
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
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
    }
    
    /** Проверить права через систему полномочий acl
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     */
    protected function acl_access_check($do, $objectid, $userid)
    {
        if ( ! $userid )
        {// получаем id пользователя в persons
            $userid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        }
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objectid, $userid);
        //    echo '<br><br>moodlecode:'.$do;
        switch ( $acldata->code )
        {// определяем дополнительные параметры в зависимости от запрашиваемого права
            // для некоторых прав название полномочия заменим на стандартное, для совместимости
            
            // @todo здесь в будущем нужно будет обращаться к правам 
            // из storage-плагинов persons и contracts
            case 'editcontract'  : 
            case 'manageaccount' : $acldata->code = 'edit'; 
                                   $acldata->plugintype = 'storage';
                                   $acldata->plugincode = 'contracts'; break;
            case 'viewcontract'  : $acldata->code = 'view'; 
                                   $acldata->plugintype = 'storage';
                                   $acldata->plugincode = 'contracts'; break;
        }
        
        if ( dof_strbeginfrom($do, 'setstatus') !== false )
        {// для всех остальных случаев просто проверяем право менять статус 
            $acldata->code = 'changestatus';
        }
        
        //      echo '<br>userid:'.$acldata->userid;
        //      echo '<br>code:'.$acldata->code;
       
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// право есть заканчиваем обработку
            return true;
        }
        
        // если права не оказалось - то проверим дополнительные случаи:
       
        if ( $acldata->code == 'view' )
        {// если нет права view - то проверим права view/seller и view/parent
            if ( $acldata->objectid )
            {// если запрашивается право на просмотр конкретного договора - 
                // то проверим - является ли пользователь законным представителем или куратором 
                
                // если указан - то получим контракт (с другими типами объектов мы в этом плагине не работаем)
                $object = $this->dof->storage('contracts')->get($objectid);
            
                if ( $userid == $object->clientid )
                {// пользователь является законным представителем 
                    $acldata->code = 'view/parent';
                    if ( $this->acl_check_access_paramenrs($acldata) )
                    {// законным представителям разрешено просматривать договоры
                        return true;
                    }
                }
                if ( $userid == $object->sellerid )
                {// пользователь является куратором
                    $acldata->code = 'view/seller';
                    if ( $this->acl_check_access_paramenrs($acldata) )
                    {// законным представителям разрешено просматривать договоры
                        return true;
                    }
                }
            }
        }
        //      echo "  !!!!NET";
        //      echo '<br>code2:'.$acldata->code;
        // никаких дополнительных прав тоже нет
        return false;
    }
    
    /** Проверить права через старую систему полномочий (пока оставлено для совместимости)
     * 
     * @todo избавится от этой функции после полного перехода на новую систему полномочий
     * @deprecated Эта функция существует здесь для совместимости со старой системой прав
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя в Moodle, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     */
    protected function old_access_check($do, $objid, $userid)
    {
        // Обрабатываем собственные полномочия плагина
        // Редактирование контракта
        if ($do === 'editcontract')
        {
            // Проверяем, является ли запросивший - куратором или менеджером
            if (($this->dof->storage('contracts')->is_seller($objid,$this->dof->storage('persons')->get_by_moodleid_id($userid))) OR ($this->is_access('manageaccount',null,$userid)))
            {
            	// Можно только при неподтвержденном статусе
            	if (($this->dof->storage('contracts')->get($objid)->status == 'tmp') )
            	{
                    $subdo = 'openaccount';
            	} else
            	{
            		return false;
            	}
            }else 
            {// не манагер и не куратор (seller) - чтоб не было notice
                return false;
            }
            // Проверяем полномочия по переопределенному действию
            return $this->is_access($subdo,$objid,$userid);  
        }
        // Просмотр контракта
        if ($do === 'viewcontract')
        {
            // Кому можно сразу
            if ($this->is_access('manageaccount',null,$userid))
            {   // Менеджеру по клиентам можно - дальше не проверяем
                return true;
            }
            // Проверяем, является ли запросивший - куратором
            if ($this->dof->storage('contracts')->is_seller($objid,$this->dof->storage('persons')->get_by_moodleid_id($userid)))
            {
                $subdo = 'openaccount';
            }else
            {
            	return false;
            }
            
            // Проверяем полномочия по переопределенному действию
            return $this->is_access($subdo,$objid,$userid);  
        }
        
        // Установка статуса
        if (dof_strbeginfrom($do,'setstatus')!==false)
        {
            $subdo = 'manageaccount';
            if ($do === 'setstatus' OR $do === 'setstatus:' OR $do==='setstatus:wesign' OR $do==='setstatus:archives')
            {
                // Можно только менеджеру клиентов
                $subdo = 'manageaccount';    
            }elseif ($do === 'setstatus:frozen' OR $do === 'setstatus:work')
            {
                // Можно только бухгалтеру
                $subdo = 'payaccount';
            }else
            {
                if ($this->is_access('manageaccount',null,$userid))
                {    // Менеджеру по клиентам можно - дальше не проверяем
                    return true;
                }
                // Проверяем, является ли запросивший - куратором
                if ($this->dof->storage('contracts')->is_seller($objid,$this->dof->storage('persons')->get_by_moodleid_id($userid)))
                {
                    $subdo = 'openaccount';
                }
            }
            // Проверяем полномочия по переопределенному действию
            return $this->is_access($subdo,$objid,$userid);
        }
        return false;
    }
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
    
    /** Получить таблицу с контрактами
     * @param int $intvar - id персоны
     * @return string - html-код таблицы или пустая строка
     * @access public
     */
    public function get_table_contracts($intvar)
    {
        require_once($this->dof->plugin_path('im', 'sel', '/lib.php') );
        $result = '';
        $conditions = array('studentid' => $intvar,
                'status'    => array('work', 'frozen'));
        
        if ( ! $contracts = $this->dof->storage('contracts')->get_records($conditions) )
        {// нет договоров - нечего отображать
            return '';
        }
        
        $result .= imseq_show_contracts($contracts, array(), null, true);
        
        return $result;
    }
    
    /** Получить таблицу с контрактами клиента
     * @param int $intvar - id персоны
     * @return string - html-код таблицы или пустая строка
     * @access public
     */
    public function get_table_contracts_client($intvar)
    {
        require_once($this->dof->plugin_path('im', 'sel', '/lib.php') );
        $result = '';
        $conditions = array('clientid' => $intvar,
                'status'    => array('work', 'frozen'));
    
        if ( ! $contracts = $this->dof->storage('contracts')->get_records($conditions) )
        {// нет договоров - нечего отображать
            return '';
        }
        
        $result .= imseq_show_contracts($contracts, array(), null, true);
        
        return $result;
    }
}


?>