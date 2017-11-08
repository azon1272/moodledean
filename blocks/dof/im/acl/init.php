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

/** Класс стандартных функций интерфейса
 * 
 */
class dof_im_acl implements dof_plugin_im
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
        return 2016011400;
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
        return 'acl';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'=>2008060300),
                     'storage'=>array('acl'=>2011041800,
                                      'aclwarrantagents'=>2011040500,
                                      'aclwarrants'=>2011040501) );
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
        return array('storage'=>array('acl'=>2012042500));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array(
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
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('manage') 
             OR $this->dof->is_access('admin') )
        {//если глобальное право есть - пропускаем';
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);    
        switch ( $do )
        {// определяем дополнительные параметры в зависимости от запрашиваемого права
            // право на просмотр своих мандат   
    
            case 'aclwarrants:view/owner':
                if ( ! $this->dof->storage('aclwarrants')->is_exists(array('ownerid'=>$personid)) 
                            AND ! $this->dof->storage('aclwarrantagents')->is_exists(
                            array('aclwarrantid'=>$objid,'personid'=>$personid)) ) 
                {// персона не владелец и не назначен на мандату ';  
                    return false;
                }
            break;
            // право на редактирование своих мандат
            case 'aclwarrants:edit/owner':
            // право на смену статуса своих мандат
            case 'aclwarrants:changestatus/owner':
                if ( ! $this->dof->storage('aclwarrants')->is_exists(array('ownerid'=>$personid)) ) 
                {// персона не владелец    
                    return false;
                }
            break;
            
        }
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
            $notice = "acl/{$do} (block/dof/im/acl: {$do})";
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
        $sections = array();
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info' ) 
        {
            if ( $this->get_section('my_warrants') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>$this->code(),'name'=>'my_warrants','id'=>1, 'title'=>$this->dof->get_string('title', $this->code()));
            }
            return $sections;
        }
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
        global $USER;
        $rez = '';
        switch ($name)
        {
            case "my_warrants":
                $personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                $depid = optional_param('departmentid', 0, PARAM_INT);
                return $this->get_warrants($personid,$depid);
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
        $acl = explode(':',$action);
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }elseif ( $acl[0] != 'acl'  )
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage($acl[0])->get_field($objectid, 'departmentid');
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
    
    /** Задаем права доступа для объектов этого интерфейса
     *  
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        $a['acl:edit']   = array('roles'=>array());
        $a['acl:create'] = array('roles'=>array());
        $a['acl:delete'] = array('roles'=>array());
        $a['aclwarrants:create']             = array('roles'=>array());
        $a['aclwarrants:edit']               = array('roles'=>array());
        $a['aclwarrants:edit/owner']         = array('roles'=>array('manager','methodist'));
        $a['aclwarrants:view']               = array('roles'=>array());
        $a['aclwarrants:view/owner']         = array('roles'=>array('manager','methodist'));
        $a['aclwarrants:changestatus']       = array('roles'=>array('manager'));
        $a['aclwarrants:changestatus/owner'] = array('roles'=>array('manager','methodist'));
        $a['aclwarrants:delegate']           = array('roles'=>array('manager','methodist'));
        //$a['aclwarrantagents:create']             = array('roles'=>array('manager','methodist'));
        //$a['aclwarrantagents:edit']               = array('roles'=>array());
        //$a['aclwarrantagents:edit/owner']         = array('roles'=>array('manager','methodist','teacher'));
        $a['aclwarrantagents:view']               = array('roles'=>array());
        $a['aclwarrantagents:view/owner']         = array('roles'=>array('manager','methodist'));
        //$a['aclwarrantagents:changestatus']       = array('roles'=>array('manager'));
        //$a['aclwarrantagents:changestatus/owner'] = array('roles'=>array('manager','methodist','teacher'));


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
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * @return array
     */
    private function get_fields_description($type)
    {
        switch ( $type )
		{
			case 'person':
            return array($this->dof->get_string('plugintype', 'acl'),
                         $this->dof->get_string('plugincode', 'acl'),
                         $this->dof->get_string('code', 'acl'),
                         $this->dof->get_string('legend', 'acl'),
                         $this->dof->get_string('objectid', 'acl'),
                         $this->dof->get_string('department', 'acl')); 
            case 'warrant':
            return array($this->dof->modlib('ig')->igs('actions'),
            			 $this->dof->get_string('plugintype', 'acl'),
                         $this->dof->get_string('plugincode', 'acl'),
                         $this->dof->get_string('code', 'acl'),
                         $this->dof->get_string('legend', 'acl'),
                         $this->dof->get_string('objectid', 'acl'));
            case 'acl':
            return array($this->dof->modlib('ig')->igs('actions'),
            			 $this->dof->modlib('ig')->igs('fio')); 
		}
    }

   /** Возвращает html-код таблицы
     * @param array $date - данные в таблицу
     * @return string - html-код или пустая строка
     */
    private function print_table($date,$type)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->width = "100%";
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        $table->size = array ('90px','130px','150px','200px','70px','100px');
        $table->align = array ("center","center","center","center","center","center");
        // шапка таблицы
        $table->head =  $this->get_fields_description($type);
        // заносим данные в таблицу     
        $table->data = $date;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких группах
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function get_table_right_person($list)
    {
        if ( ! is_array($list))
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
    	foreach ($list as $obj)
    	{   
    	    $department = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
    	                  $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
    	    $data[] = array($obj->plugintype,$obj->plugincode,$obj->code,
    	                    $this->dof->get_string($obj->plugintype.'_'.$obj->plugincode.'_'.$obj->code,'acl','<br>'),
    	                    $obj->objectid,$department);
    	}
    	
    	// выводим таблицу на экран
        return $this->print_table($data,'person');
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких группах
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function get_table_right_warrant($list)
    {
        $addvars = array();
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        if ( ! is_array($list))
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
    	foreach ($list as $obj)
    	{
    	    $actions = '';
    	    if ( $this->is_access('aclwarrantagents:view') )   
            {
        	    $actions .= '<a href='.$this->dof->url_im('acl','/aclpersons.php?id='.
                $obj->id,$addvars).'><img src="'.$this->dof->url_im('acl', '/icons/persons_acl.png').'" 
                    alt="'.$this->dof->get_string('view_acl_person', 'acl').'" 
                    title="'.$this->dof->get_string('view_acl_person', 'acl').'"></a>';
            }
            if ( $this->is_access('acl:delete') ) 
            {
                $actions .= '<a href='.$this->dof->url_im('acl','/delete.php?id='.
                $obj->id,$addvars).'><img src="'.$this->dof->url_im('acl', '/icons/delete.png').'" 
                    alt="'.$this->dof->get_string('delete_acl', 'acl').'" 
                    title="'.$this->dof->get_string('delete_acl', 'acl').'"></a>';
            }
    	    $data[] = array($actions, $obj->plugintype,$obj->plugincode,$obj->code,
    	                    $this->dof->get_string($obj->plugintype.'_'.$obj->plugincode.'_'.$obj->code,'acl','<br>'),
    	                    $obj->objectid);
    	}
    	
    	// выводим таблицу на экран
        return $this->print_table($data,'warrant');
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких группах
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function get_table_persons_acl($list)
    {
        $addvars = array();
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        if ( ! is_array($list))
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
    	foreach ($list as $obj)
    	{   
    	    $personname = '<a href='.$this->dof->url_im('persons','/view.php?id='.
            $obj->id,$addvars).'>'.$this->dof->storage('persons')->get_fullname($obj->id).'</a>';
    	    //покажем ссылку на страницу просмотра
            $actions = '<a href='.$this->dof->url_im('acl','/personacl.php?id='.
            $obj->id,$addvars).'><img src="'.$this->dof->url_im('acl', '/icons/list_acl.png').'" 
                alt="'.$this->dof->get_string('view_acl_person', 'acl').'" 
                title="'.$this->dof->get_string('view_acl_person', 'acl').'"></a>';
    	    $data[] = array($actions, $personname);
    	}
    	
    	// выводим таблицу на экран
        return $this->print_table($data,'acl');
    }
    
    /** 
     * Получить список с доверенностями пользователя
     * @param int $intvar - id персоны
     * @param int $depid - id департамента
     * @return string - html-код таблицы или пустая строка
     * @access public
     */
    public function get_warrants($intvar, $depid = 0) 
    {
        $result = '';
        // $intvar пустой - берем текущего пользователя 
        if (!$intvar)
        {
            $intvar = $this->dof->storage('persons')->get_bu();
        }
        $conditions = array('personid' => $intvar,
                            'status' => array('draft', 'active'));

        // получаем массив доверенностей пользователя
        $warrants = $this->dof->storage('aclwarrants')->get_listing(
                    $conditions);
        if (empty($warrants))
        {// нет доверенностей - ничего не отображаем
            return '';
        }

        foreach ( $warrants as $warrant )
        {
            $action = '';
            //сюда же потом добавить передачу подразделений и проверку прав
            if ($this->is_access('aclwarrants:delegate'))
            {
                $action .= '<a href="'.$this->dof->url_im('acl','/givewarrant.php?id=0&aclwarrantid='.$warrant->id.'&ownerid='.$intvar.
                           '&subdepartmentid='.$depid.'&departmentid='.$depid).'">';
                $action .= '<img src="'.$this->dof->url_im('acl', '/icons/sub_warrant.png').
                           '"alt=  "'.$this->dof->get_string('give_warrant', 'acl').
                           '"title="'.$this->dof->get_string('give_warrant', 'acl').'" /></a>&nbsp;';
                
            }
            // Надпись делается ссылкой на просмотр прав доверенности
            $link = $this->dof->url_im('acl','/warrantview.php?aclwarrantid='.$warrant->id.'&departmentid='.$depid);
            $result .= '<a href="'.$link.'">'.$warrant->name.'['.$warrant->code.']</a> '.$action.'<br>';
            $result .= $this->show_list_warrants($warrant->id, $intvar, $depid, '&nbsp;&nbsp;&nbsp;&nbsp;');
        }
        
        return $result;
    }
    
    /** Получает список субдоверенностей доверенности
     * @param int $intvar - id персоны
     * @param int $depid - id департамента
     * @return string - html-код таблицы или пустая строка
     * @access public
     */
    public function show_list_warrants($warrantid, $personid, $depid, $spaces = '') 
    {
        $result = '';
        $conditions = array('parentid' => $warrantid,
                            'ownerid' => $personid,
                            'status' => array('draft', 'active'));
        // получаем массив доверенностей пользователя
        $subwarrants = $this->dof->storage('aclwarrants')->get_records($conditions);
        if (empty($subwarrants))
        {// нет доверенностей - ничего не отображаем
            return '';
        }
        foreach ( $subwarrants as $subwarrant )
        {
            // TODO добавить ссылки на редактирование и удаление субдоверенности
            $action = '';
            if ( $this->is_access('aclwarrants:delegate',$subwarrant->id) )
            {
                $action .= $this->dof->modlib('ig')->icon('edit',
                    $this->dof->url_im('acl','/givewarrant.php?id='.$subwarrant->id.
                       '&aclwarrantid='.$subwarrant->parentid.'&departmentid='.$depid));
            }
            if ( $this->is_access('aclwarrants:changestatus/owner',$subwarrant->id) OR 
                 $this->is_access('aclwarrants:changestatus',$subwarrant->id) )
            {
                if ( $subwarrant->status == 'draft' )
                {
                    $action .= $this->dof->modlib('ig')->icon('state',
                        $this->dof->url_im('acl','/activewarrant.php?aclwarrantid='.$subwarrant->id.'&departmentid='.$depid));
                }
                $action .= $this->dof->modlib('ig')->icon('delete',
                    $this->dof->url_im('acl','/archivewarrant.php?aclwarrantid='.$subwarrant->id.'&departmentid='.$depid));

            }

        }
        // Надпись делается ссылкой на просмотр прав доверенности
        $link = $this->dof->url_im('acl','/warrantview.php?aclwarrantid='.$subwarrant->id.'&departmentid='.$depid);
        $result .= $spaces.'<a href="'.$link.'">'.$subwarrant->name.'['.$subwarrant->code.']</a> '.$action.'<br>';
        return $result;
    }
    
    public function process_addremove_aclwarrantagents($type, $list, $aclwarrantid, $departmentid)
    {
        switch ( $type )
		{
			case 'add':// добавление назначений
			    $res = true;
			    foreach ( $list as $id )
			    {// добавляем для каждой персонки
                    $res = $res && $this->add_warrantagent($aclwarrantid, $departmentid, $id);
                }
                return $res;
            case 'remove':// удаление назначений
                $res = true;
			    foreach ( $list as $id )
			    {// архивируем для каждой персонки
			        if ( !$warrantagents = $this->dof->storage('aclwarrantagents')->get_records(
			                         array('personid'=>$id, 'status'=>array('draft','active'))) )
			        {// назначений нет - переходим к следующей персонке
			            continue;
			        }
			        foreach ( $warrantagents as $warrantagent )
			        {// архивируем каждое назначение
			            $res = $res && $this->dof->workflow('aclwarrantagents')->change($warrantagent->id,'archive');
			        }
                }
                return $res;
                

		}
		return true;
    }
    
    /** Получить сообщение о результате создания/удаления применений доверенностей, размеченное html-тегами
     * 
     * @param string $action - add - применения были созданы 
     *                         remove - применения были удалены 
     * @param bool $result - результат выполненной операции
     */
    public function get_addremove_aclwarrantagents_result_message($action, $result)
    {
        // определяем, какими цветами будем раскрашивать успешное и неуспешное сообщение
        $successcss = 'color:green;';
        $failurecss = 'color:red;';
        $basecss    = 'text-align:center;font-weight:bold;margin-left:auto;margin-right:auto;';
        if ( $action == 'add' )
        {// применения создавались
            if ( $result )
            {// удалось создать
                $css      = $successcss;
                $stringid = 'add_aclwarrantagents_success';
            }else
            {// не удалось создать
                $css      = $failurecss;
                $stringid = 'add_aclwarrantagents_failure';
            }
        }elseif ( $action == 'remove' )
        {// применения удалялись
            if ( $result )
            {// удалось архивировать
                $css      = $successcss;
                $stringid = 'remove_aclwarrantagents_success';
            }else
            {// не удалось архивировать
                $css      = $failurecss;
                $stringid = 'remove_aclwarrantagents_failure';
            }
        }
        // получаем текст сообщения
        $text = $this->dof->get_string($stringid, $this->code());
        // оформляем сообшение css-стилями
        return '<p style="'.$basecss.$css.'">'.$text.'</p>';
    }
    
    /**
     * Сохранение доверенного лица
     * @param object $warrantagent - объект с данными для таблицы warrantagents
     * @return bool
     */
    public function add_warrantagent($aclwarrantid, $departmentid, $personid) 
    {
    	$rez = true;
    	
        $record = new stdClass();
        $record->baseptype     = 'storage';
        $record->basepcode     = 'persons';
        $record->basetype      = 'record';
        $record->aclwarrantid  = $aclwarrantid;
        $record->departmentid  = $departmentid;
        $record->begindate	   = time();
        $record->datecreate	   = time();
        // брать из настройки
        $record->duration 	   = 0;
        $record->isdelegatable = 0;
        $record->personid	   = $personid;
		$record->baseid		   = $personid;
        if ( !$pid = $this->dof->storage('aclwarrantagents')->add($record))
        {
        	return false;
        }
        // добавляем ту же запись, но с измененными basepcode и baseid
        $record->basepcode = 'departments';
        $record->baseid = $record->departmentid;
        if ( !$did = $this->dof->storage('aclwarrantagents')->add($record))
        {
            return false;
        }
        // получаем статусы пользователя и доверенности';
        $perstat = $this->dof->storage('persons')->get_field(array('id' => $personid), 'status');
        $warstat = $this->dof->storage('aclwarrants')->get_field(array('id' => $aclwarrantid), 'status');
        if ( $perstat == 'normal' AND $warstat == 'active' )
        {// если персона и доверенность активны - активируем доверенное лицо
            $rez = $rez && $this->dof->workflow('aclwarrantagents')->change($did,'active');
            $rez = $rez && $this->dof->workflow('aclwarrantagents')->change($pid,'active');
        }
    	return $rez;
    }
    
    /**
     * Обновление субдоверенности для переданных пользователей
     * @param object $warrantagent - объект с данными для таблицы warrantagents
     * @param object $persolist - массив с id персон
     * @return bool
     */
    
    public function apply_to_update_warrantagents($data, $personlist) {
        
        $res = true;
        
        if ( ! empty($personlist))
        {
            // массив доверенных лиц, использующих данную доверенность 
            if ( ! $warrantagents = $this->dof->storage('aclwarrantagents')->get_records(
                    array('aclwarrantid' => $data->aclwarrantid, 'status' => 'active')))
            {
                return false;
            }
            
            foreach ($personlist as $personid)
            {
                $match = false;
               
                foreach ($warrantagents as $key => $warrantagent)
                {
                    if ($warrantagent->personid == $personid)
                    {// обновляем дынные доверенного лица
                        
                        $obj = new stdClass();
                        $obj->id = $warrantagent->id;
                        $obj->begindate = $data->begindate;
                        $obj->duration = $data->duration;
                        $obj->isdelegatable = $data->isdelegatable;
                        
                        $res = $this->update_warrantagent($obj) AND $res;
                       
                        unset($warrantagents[$key]);
                        $match = true;
                    }   
                }
                
                // не найдено - значит новый warrantagent
                if (!$match)
                {
                    $data->personid = $personid;
                    $data->baseid = $personid;
                    
                    $res = $this->add_warrantagent($data) AND $res;
                }
            }
            
            // все необновленные записи в warrantagents отправляем в архив
            if (!empty($warrantagents))
            {
                foreach ($warrantagents as $warrantagent)
                {
                    $obj = new stdClass();
                    $obj->id = $warrantagent->id;
                    $obj->status = 'archive';
                    $res = $this->dof->storage('aclwarrantagents')->update($obj) AND $res;
                }
            }
             
            return $res;
        }
    
        return false;
    }
    
    /**
     * Обновление доверенного лица
     * @param object $warrantagent - объект с данными для таблицы warrantagents
     * @return bool
     */
    public function update_warrantagent($obj)
    {
        // основные проверки
        if (!is_object($obj) OR empty($obj->id) OR !is_int_string($obj->id))
        {
            return false;
        }
        
        if ( ! $this->dof->storage('aclwarrantagents')->update($obj))
        {
            return false;
        }
        
        return true;
    }
    
   /**
    * Возвращает вкладки на сотрудники/список должностей/список вакансий/ список должостных назначений
    * @param string $id -идентификатор,определяет какая вкладка активна в данный момент
    * @param arrrya $addvars - массив параметров GET(подразделение) 
    * @return смешанную строку 
    */
    public function print_tab($addvars, $id)
    {
        // соберем данные для вкаладок
        $tabs = array();
        // мандаты ядра
        if ( $this->is_access('admin') )
        {//просмотр только для админа
            $link = $this->dof->url_im($this->code(),'/warrantslist.php?type=core',$addvars);
            $text = $this->dof->get_string('core', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('core', $link, $text, NULL, true);
        }
        
        // мандаты выданные по должности
        //if ( $this->dof->storage('schpositions')->is_access('view') )
        {
            $link = $this->dof->url_im($this->code(),'/warrantslist.php?type=ext',$addvars);
            $text = $this->dof->get_string('ext', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('ext', $link, $text, NULL, true);
        }
        // субдоверенности пользователей
        //if ( $this->dof->storage('appointments')->is_access('view') )
        {
            $link = $this->dof->url_im($this->code(),'/warrantslist.php?type=sub',$addvars);
            $text = $this->dof->get_string('sub', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('sub', $link, $text, NULL, true);
        }
        return $this->dof->modlib('widgets')->print_tabs($tabs, $id, NULL, NULL, true);
    }
    
    /**
     * Возвращает таблицу с доверенностью
     * @param string $aclwarrantdid - id доверенности
     * @param arrrya $addvars - массив параметров GET(подразделение)
     * @return string
     */
    public function show_one_warrant($addvars, $aclwarrantid)
    {
        $result = '';
        if ( ! intval($aclwarrantid) > 0)
        {// проверим входной id
            return false;
        }
        if ( ! $warrant = $this->dof->storage('aclwarrants')->get_record(array('id' => $aclwarrantid)))
        {
            return false;
        }
        // получили доверенность - заносим ее в таблицу
        $table = new stdClass();
        //$table->tablealign = 'center';
        $table->cellpadding = '5';
        $table->cellspacing = '5';
       // $table->size = array('20%', '10%', '20%', '5%', '5%', '10%', '15%', '15%');
        //$table->width = '70%';
        //$table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
         
        $head = array($this->dof->get_string('warrants_table_name', 'acl'),
                $this->dof->get_string('warrants_table_code', 'acl'),
                $this->dof->get_string('warrants_table_description', 'acl'),
                $this->dof->get_string('warrants_table_status', 'acl'),
                $this->dof->get_string('warrants_table_parenttype', 'acl'),
                $this->dof->get_string('warrants_table_parent', 'acl'),
                $this->dof->get_string('warrants_table_ownerid', 'acl'),
                $this->dof->get_string('warrants_table_departmentid', 'acl'),
                $this->dof->get_string('warrants_table_actions', 'acl'));
         
        if (!empty($warrant))
        {// маcсив не пустой - заносим данные в таблицу
            $obj = new stdClass();
            $obj->name 			= $warrant->name;
            $obj->code 			= $warrant->code;
            $obj->description 	= $warrant->description;
            $obj->status		= $warrant->status;
            $obj->parenttype	= $warrant->parenttype;
            $parentlink = '';
            if (intval($warrant->parentid) > 0)
            {// parentid > 0 - получаем имя родительской доверенности
                if ( $parent = $this->dof->storage('aclwarrants')->get_record(array(
                        'id' => $warrant->parentid)) )
                {// есть запись - формируем ссылку на доверенность
                    $link = $this->dof->url_im('acl', '/warrantview.php', array(
                            'aclwarrantid' => $warrant->parentid,'departmentid'=>$addvars['departmentid']));
                    $string = $parent->name."[".$parent->code."]";
                     
                    $parentlink = "<a href='".$link."'>".$string."</a>";
                }
            }
            $obj->parent		= $parentlink;
            $personfio = '';
            if ( intval($warrant->ownerid) > 0)
            {// ownerid больше нуля - получаем полное имя владельца
                $personfio = $this->dof->storage('persons')->get_fullname($warrant->ownerid);
            }
            $obj->ownerid		= $personfio;
            $departmentlink = '';
            if (intval($warrant->departmentid) > 0)
            {// parentid > 0 - получаем имя родительской доверенности
                if ( $parent = $this->dof->storage('departments')->get_record(array(
                        'id' => $warrant->departmentid)) )
                {// есть запись - формируем ссылку на доверенность
                    $link = $this->dof->url_im('acl', '/warrantview.php', array(
                            'aclwarrantid' => $warrant->parentid,'departmentid'=>$addvars['departmentid']));
                    $string = $parent->name."[".$parent->code."]";
                    //$departmentlink = "<a href='".$link."'>".$string."</a>";
                    $departmentlink = $string;
                }
            }
            $obj->departmentid = $departmentlink;
            $obj->action = '';
            if ( $this->is_access('aclwarrants:delegate',$warrant->id) AND $warrant->parenttype == 'sub' )
            {
                $obj->action .= $this->dof->modlib('ig')->icon('edit',
                    $this->dof->url_im('acl','/givewarrant.php?id='.$warrant->id.
                       '&aclwarrantid='.$warrant->parentid.'&departmentid='.$addvars['departmentid']));
            }
            if ( ($this->is_access('aclwarrants:changestatus/owner',$warrant->id) OR 
                 $this->is_access('aclwarrants:changestatus',$warrant->id)) AND $warrant->parenttype == 'sub' )
            {
                if ( $warrant->status == 'draft' )
                {
                    $obj->action .= $this->dof->modlib('ig')->icon('state',
                        $this->dof->url_im('acl','/activewarrant.php?aclwarrantid='.$warrant->id.'&departmentid='.$addvars['departmentid']));
                }
                $obj->action .= $this->dof->modlib('ig')->icon('delete',
                    $this->dof->url_im('acl','/archivewarrant.php?aclwarrantid='.$warrant->id.'&departmentid='.$addvars['departmentid']));

            }
            foreach ( $obj as $elm )
            {
                $table->data[] = array('<b>'.current(each($head)).'</b>', $elm);
            } 
            $result .= '<br/>'.$this->dof->modlib('widgets')->print_table($table,true);
        }


        return $result;
    }
    

}