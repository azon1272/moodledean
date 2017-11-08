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



/** Подразделения
 * 
 */
class dof_im_departments implements dof_plugin_im
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
        return true;
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
        return true;
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
        return 2016080500;
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
        return 'departments';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'          => 2008060300,
                                     'widgets'      => 2009050800),
                                     
                     'storage'=>array('persons'     => 2016080500,
                                      'departments' => 2011091900,
                                      'ages'        => 2009050600,
                                      'acl'         => 2011041800));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array(
                array('plugintype' => 'im',
                      'plugincode' => 'obj',
                      'eventcode'  => 'get_object_url'));
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
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
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
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "departments/{$do} (block/dof/im/departments: {$do})";
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
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'departments' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('id' => $intvar);
                    if ( isset($mixedvar['urlparams']) AND is_array($mixedvar['urlparams']) )
                    {
                        $params = array_merge($params, $mixedvar['urlparams']);
                    }
                    return $this->url('/view.php', $params);
                }
            }
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
    /** Возвращает текст для отображения в блоке на странице dof
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string - html-код содержимого блока
     */
    function get_block($name, $id = 1)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $rez = '';
        // адрес текущей станицы
        $url = $this->dof->modlib('nvg')->get_url();

        switch ($name)
        {
            case 'main':
                // список всех подразделений
                $dep  = $this->dof->storage($this->code())->departments_list_subordinated(null,'0', null,true,'',true);
                if ( $dep )
                {// есть права
                    foreach ( $dep as $id=>$objdep )
                    {
                        // получим полное название подразделения для всплывающей подсказки
                        $deptname = $this->dof->storage($this->code())->get_field($id, 'name');
                        if ( strstr($url, 'departmentid=') )
                        {// есть подразделение заменим на соответствующий id
                            $path = str_replace('departmentid='.$depid, 'departmentid='.$id, $url);
                        }else 
                        {// установим свои подразделения
                            if (  strstr($url, '?') )
                            {
                                $path= $url.'&departmentid='.$id;
                            }else 
                            {    
                                $path= $url.'?departmentid='.$id;
                            }
                        }    
                        if ( $id==0 )
                        {// есть право смотреть все объекты - покажем это
                            if ( $depid == 0)
                            {
                                $rez = "<a href='{$path}' style='color:green; font-size:17px;'>".$this->dof->get_string('see_allobj', 'departments') ."</a><br>".$rez;
                            }else 
                            {
                                $rez = "<a href='{$path}'>". $this->dof->get_string('see_allobj', 'departments') ."</a><br>".$rez;
                            }     
                        }elseif ( $depid == $id)
                        {// ссылка, на которой мы сейчас находимся
                            $rez .= "<a href='{$path}' title='{$deptname}' style='color:green; font-size:17px;'>{$objdep}</a><br>";
                        }else 
                        {// все остальные ссылки
                            $rez .= "<a href='{$path}' title='{$deptname}'>{$objdep}</a><br>";
                        }  
                    }
                }  
        }
        if ( $rez )
        {     
            return $rez;
        }    
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    function get_section($name, $id = 0)
    {
        $rez = '';
        switch ($name)
        {
            case 'information' :
                $rez .= $this->get_information_section(NULL);
                break;
        }
        return $rez;
    }
     /** Возвращает текст, отображаемый в блоке на странице курса MOODLE 
      * @return string  - html-код для отображения
      */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('departments','/index.php')}'>"
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
    protected function get_access_parametrs($action, $objectid, $userid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
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
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
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
     * Возвращает html-код отображения 
     * информации о подразделении
     * @param stdClass $obj - запись из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show($obj)
    {
        if (! is_object($obj))
        {// переданны данные неверного формата
        	return false;
        }
        if ( empty($obj->addressid) )
        {
            $obj->addressid = 0;
        }
        $customdata = new stdClass;
        $customdata->obj = $obj;
        $customdata->dof = $this->dof;
        $form = new dof_im_departments_card(null,$customdata);
        unset($obj->zone);
        $form->set_data($obj); 
    	// выводим таблицу на экран
        return $form;
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о подразделении
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('departments')->get($id) )
    	{// подразделение не найден
    		return false;
    	} 
    	return $this->show($obj);
    }
    
    /**
     * Возвращает HTML-код таблицы подразделений
     * 
     * @param array $list - Массив подразделений
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Массив дополнительных параметров отображения
     * 
     * @return string - HTML-код таблицы
     */
    public function showlist($list, $addvars, $options = [])
    {
        $html = '';
        
        // Нормализация
        if ( ! is_array($list) )
        {
        	$list = [];
        }
          
        // Таблица
        $table = new stdClass();
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = ['100px', '150px', '150px', '200px', '150px', '100px'];
        $table->align = ["center", "center", "center", "center", "center", "center"];
        // Шапка
        $table->head = [
            $this->dof->get_string('actions','departments'),
            $this->dof->get_string('name','departments'),
            $this->dof->get_string('code','departments'),
            $this->dof->get_string('manager','departments'),
            $this->dof->get_string('leaddep','departments'),
            $this->dof->get_string('status','departments')
        ];
        $table->data = [];
        
        if ( ! empty($list) )
        {
            // Заполнение таблицы
            $listvars = $addvars;
        	foreach ( $list as $obj )
        	{   
        	    $listvars['id'] = $obj->id;
        	    
        	    // Имя подразделения
                if ( $this->dof->storage('departments')->is_access('view', NULL, NULL, $obj->id) )
                {// Ссылка на просмотр
                    $name = html_writer::link(
                        $this->dof->url_im('departments', '/view.php', $listvars),
                        $obj->name
                    );
                } else
                {// Название подразделения без ссылки
                    $name = $obj->name;
                }
                
                // Код подразделения
        		$code = $obj->code;
        		
       		    // Руководитель подразделения
       	    	$manager = $this->dof->storage('persons')->get_fullname($obj->managerid);
       	    	
       	    	// Вышестоящее подразделение
       	    	if ( $obj->leaddepid > 0 )
       	    	{// Вышестоящее подразделение указано
       		        $leaddep = $this->dof->storage('departments')->get_field($obj->leaddepid,'name').
       	   		       '<br>['.$this->dof->storage('departments')->get_field($obj->leaddepid,'code').']';
       	    	} else
       	    	{// Вышестоящее подразделение не указано
       	    	    $leaddep = '';
       	    	}
       	    	
       	    	// Действия над подразделением
                $actions = '';
        	    if ( $this->dof->storage('departments')->is_access('edit', NULL, NULL, $obj->id) )
                {// Редактирование подразделения доступно
                    $attroptions['title'] = $this->dof->get_string('edit', 'departments');
                    $actions .= $this->dof->modlib('ig')->icon(
                        'edit',
                        $this->dof->url_im('departments', '/edit.php', $listvars),
                        $attroptions
                    );
                }
                if ( $this->dof->storage('departments')->is_access('view', NULL, NULL, $obj->id) )
                {// Просмотр подразделения доступен
                    $attroptions['title'] = $this->dof->get_string('view', 'departments');
                    $actions .= $this->dof->modlib('ig')->icon(
                        'view',
                        $this->dof->url_im('departments', '/view.php', $listvars),
                        $attroptions
                    );
                }
                
                $actualstatuses = $this->dof->workflow('departments')->get_meta_list('actual');
                $realstatuses = $this->dof->workflow('departments')->get_meta_list('real');
        	    if ( $this->is_access('datamanage') && isset($realstatuses[$obj->status]) )
                {// Удаление доступно  
                    $listvars['delete'] = 1;
                    $attroptions['title'] = $this->dof->get_string('deletedepartment', 'departments');
                    $actions .= $this->dof->modlib('ig')->icon(
                        'delete',
                        $this->dof->url_im('departments', '/change.php', $listvars),
                        $attroptions
                    );
                    unset($listvars['delete']);
                }
        	    if ( $this->is_access('datamanage') && ! isset($actualstatuses[$obj->status]) )
                {// Активация доступна
                    $listvars['active'] = 1;
                    $attroptions['title'] = $this->dof->get_string('activatedepartment', 'departments');
                    $actions .= $this->dof->modlib('ig')->icon(
                        'add',
                        $this->dof->url_im('departments', '/change.php', $listvars),
                        $attroptions
                    );
                    unset($listvars['active']);
                }
                
                // Статус подразделения
                $status = $this->dof->workflow('departments')->get_name($obj->status);
                
                // Добавление строки в таблицу
                $table->data[] = [$actions, $name, $code, $manager, $leaddep, $status];
        	}
        }
    	
        // Формирование таблицы
    	return $this->dof->modlib('widgets')->print_table($table, true);
    }

    /** Получить фрагмент списка подразделений для вывода таблицы 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param string $select - SQL-код с дополнительными условиями, если потребуется 
     * @param string $sort - по какому полю и в каком порядке сортировать записи 
     * (sql-параметр ORDER BY)
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список подразделений не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        $countselect = $this->get_select_listing($conds);
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        //определяем порядок сортировки
        $sort = 'leaddepid ASC, name ASC , code ASC, managerid ASC, status ASC';
        // возвращаем ту часть массива записей таблицы, которую нужно
        
        return $this->dof->storage('departments')->get_records_select($select, null, $sort, '*', $limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param int $departmentid - id подразделения
     * @param string $status - название статуса
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( ! is_null($field) AND ! empty($field))
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->dof->storage('departments')->query_part_select('id',$field);
            }
        }
        $selects[] = "(status <> 'deleted' OR status IS NULL)";
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

    /** Получить название подразделения в виде ссылки
     * @param int id - id подразделения в таблице departments
     * @param bool $withcode - добавлять или не добавлять код в конце
     * 
     * @return string html-строка со ссылкой на подразделение или пустая строка в случае ошибки
     */
    public function get_html_link($id, $withcode=false)
    {
        if ( ! $name = $this->dof->storage('departments')->get_field($id, 'name') )
        {
            return '';
        }
        if ( $withcode )
        {
            $code = $this->dof->storage('departments')->get_field($id, 'code');
            $name = $name.' ['.$code.']';
        }
        return '<a href="'.$this->dof->url_im($this->code(), 
                    '/view.php', array('departmentid' => $id)).'">'.$name.'</a>';
    }   
    
    /**
     * Сформировать секцию информации по подразделению
     * 
     * @param int $departmentid - ID подразделения ,по которому формируется информация. 
     *                            Автоопределение, если не указано 
     * @param array $options - Дополнительные опции отображения
     * 
     * @return string - HTML-код секции
     */
    public function get_information_section($departmentid = null, $options = [])
    {
        $html = '';
        
        // Нормализация входных данных
        if ( is_null($departmentid) )
        {// Не передано требуемое подразделение
            $departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        
        if ( $departmentid > 0 )
        {// Проверка наличия подразделения
            $department = $this->dof->storage('departments')->get($departmentid);
        } else
        {// Коренное подразделение
            $department = null;
        }
        if ( ! $this->dof->plugin_exists('storage', 'persons') )
        {
            return $html;
        }
        
        // Формирование блока информации о подразделении
        $html .= dof_html_writer::start_div('dof_departmens_infosection_wrapper');
        
        $header = '';
        $content = $this->block_department_information_content($departmentid);
        $footer = '';
        // Проверка наличия подразделения
        if ( ! empty($department) || $department === null )
        {// Подразделение определено
            
            // Механизм спойлера
            $label = dof_html_writer::div(
                $this->dof->get_string('spoil', 'departments'), 
                'dof_departmens_infosection_spoiler_checkbox_spoil'
            );
            $label .= dof_html_writer::div(
                $this->dof->get_string('despoil', 'departments'), 
                'dof_departmens_infosection_spoiler_checkbox_despoil'
            );
            
            $header .= dof_html_writer::checkbox('dof_departmens_infosection_spoiler','', true, $label);
            $header .= dof_html_writer::start_div('dof_departmens_inf_content');
            $header .= $this->block_department_information_header($department);
            $footer .= $this->block_department_information_footer($departmentid);
            $footer .= dof_html_writer::end_div();
            $footer .= dof_html_writer::div('', 'dof_clearboth');
        }
        $html .= $header.$content.$footer;
        $html .= dof_html_writer::end_div();
        
        return $html;
    }
    
    /**
     * Блок отображения шапки подразделения
     *
     * @param stdClass|null $department - Подразделение, для которого требуется сформирвать блок
     * @param array $options - Дополнительные опции отображения
     */
    private function block_department_information_header($department, $options = [])
    {
        // Подготовка данных для формирования блока
        $html = '';
        
        if ( empty($department) )
        {// Подразделение не передано
            return $html;
        }
        
        // Получение текущего URL
        $currenturl = $this->dof->modlib('nvg')->get_url();
        
        // Получение данных вышестоящего подразделения
        if ( (int)$department->leaddepid > 0 )
        {// Вышестоящее подразделение указано
            $name = $this->dof->storage('departments')->get_field($department->leaddepid, 'name');
            $code = $this->dof->storage('departments')->get_field($department->leaddepid, 'code');
        } elseif ( (int)$department->leaddepid === 0 )
        {// Коренное подразделение
            $name = $this->dof->get_string('root_department', 'departments');
            $code = '';
        } else 
        {// Подразделение не найдено
            $name = $this->dof->get_string('notfound', 'departments', $department->leaddepid);
            $code = '';
        }
        
        $html .= dof_html_writer::start_div('dof_departmens_inf_header');
        $html .= dof_html_writer::start_div('dof_departmens_inf_h_parentdep_wrapper');
        if ( $this->dof->storage('departments')->is_access('view', NULL, NULL, $department->leaddepid) ||
             $this->dof->storage('departments')->is_access('view/mydep', NULL, NULL, $department->leaddepid) )
        {// Есть право на просмотр родительского подразделения
            // URL для перехода к вышестоящему подразделению
            $url = preg_replace('/departmentid=\d*/', 'departmentid='.$department->leaddepid, $currenturl);
            
            if ( ! empty($code) )
            {
                // Получение блока кода подразделения
                $code = $this->block_department_codeblock($code);
                $code = dof_html_writer::div($code, 'dof_departmens_inf_h_p_code');
            }
            if ( ! empty($name) )
            {
                $name = dof_html_writer::div($name, 'dof_departmens_inf_h_p_name');
            }
            $infoblock = dof_html_writer::div('<div></div>', 'dof_departmens_inf_h_p_icon').$code.$name;
            $html .= dof_html_writer::link($url, $infoblock, ['class' => 'dof_departmens_inf_h_parentdep_link']);
        }
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        
        return $html;
    }
    
    /**
     * Блок отображения информации подразделения
     *
     * @param int $departmentid - Подразделение, для которого требуется сформирвать блок
     * @param array $options - Дополнительные опции отображения
     */
    private function block_department_information_content($departmentid = 0, $options = [])
    {
        // Подготовка данных для формирования блока
        $html = '';
        $currentperson = $this->dof->storage('persons')->get_bu();
        // Получение текущего URL
        $currenturl = $this->dof->modlib('nvg')->get_url();
        $currentdepid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = ['departmentid' => $currentdepid];
        
        if ( empty($departmentid) )
        {// Подразделение не указано
            $departmentid = $currentdepid;
        }
        
        // Проверка наличия подразделения
        $department = $this->dof->storage('departments')->get($departmentid);
        if ( empty($department) && $departmentid != 0 )
        {// Подразделение не найдено
            $name = $this->dof->get_string('notfound', 'departments', $departmentid);
            $code = '';
        } elseif ( $departmentid === 0 )
        {// Коренное подразделение
            $name = $this->dof->get_string('root_department', 'departments');
            $code = '';
        } else
        {// Подразделение найдено
            $name = $department->name;
            $code = $department->code;
        }
    
        $html .= dof_html_writer::start_div('dof_departmens_inf_main');
        
        $html .= $this->block_department_time($departmentid);
        $html .= dof_html_writer::start_div('dof_departmens_depinfo_wrapper');
        if ( ! empty($code) )
        {
            // Получение блока кода подразделения
            $code = $this->block_department_codeblock($code);
            $html .= dof_html_writer::div($code, 'dof_departmens_inf_c_code');
        }
        if ( ! empty($name) )
        {
            $html .= dof_html_writer::div($name, 'dof_departmens_inf_c_name');
        }
        $html .= dof_html_writer::end_div();
        $html .= $this->block_department_additional($departmentid);
        
        $html .= dof_html_writer::end_div();
        return $html;
    }
    
    /**
     * Блок отображения времени в подразделении
     *
     * @param number $departmentid - ID подразделения
     * @param array $options - Дополнительные опции отображения
     */
    private function block_department_time($departmentid = 0, $options = [])
    {
        // Подготовка данных для формирования блока
        $html = '';
        // Часовая зона подразделения
        $deptimezone = $this->dof->storage('departments')->get_timezone($departmentid);
        if ( $deptimezone == 99 )
        {// Установка часового пояса сервера
            $deptimezone =  date('Z')/3600;
        }
    
        // Формирование строкового представления часовой зоны
        $deptimezonestr = (string)$deptimezone;
        if ( $deptimezone > 0 )
        {// Добавление +, если часовая зона положительная
            $deptimezonestr = '+'.$deptimezonestr;
        }
    
        // Формирование блока
        $html .= dof_html_writer::start_div('dof_departmens_dateblock_wrapper');
        $html .= dof_html_writer::start_div('dof_departmens_dateblock');
    
        // БАЗОВЫЕ ДАННЫЕ ПО ВРЕМЕНИ В ПОДРАЗДЕЛЕНИИ
        // Время в подразделении
        $current_time = dof_html_writer::div(
            dof_userdate(time(), '%H:%M', $deptimezone),
            '',
            ['id' => 'dof_departmens_dateblock_currenttime']
            );
        // Часовой пояс в подразделении
        $current_timezone = dof_html_writer::div(
            '('.$this->dof->get_string('gmtinfo', 'departments', $deptimezonestr).')',
            '',
            ['id' => 'dof_departmens_dateblock_currenttimezone']
            );
        $html .= dof_html_writer::span(
            $current_time.$current_timezone,
            'dof_departmens_dateblock_time',
            ['id' => 'dof_departmens_dateblock_time']
            );
    
        // ДОПОЛНИТЕЛЬНЫЕ ДАННЫЕ ПО ВРЕМЕНИ В ПОДРАЗДЕЛЕНИИ
        $html .= dof_html_writer::start_div('dof_departmens_dateblock_additional');
        $html .= dof_html_writer::div('', 'dof_departmens_dateblock_additional_arrow');
        $html .= dof_html_writer::start_div('dof_departmens_dateblock_additional_content');
        // День недели
        $html .= dof_html_writer::div(
            dof_userdate(time(), '%A', $deptimezone),
            'dof_departmens_dateblock_dayweek',
            ['id' => 'dof_departmens_dateblock_dayweek']
            );
        // Дата
        $html .= dof_html_writer::div(
            dof_userdate(time(), '%d.%m.%Y', $deptimezone, false),
            'dof_departmens_dateblock_date',
            ['id' => 'dof_departmens_dateblock_date']
            );
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
    
        $html .= dof_html_writer::end_div();
    
        // Смещение для js-часов
        $jstimestampoffset = $deptimezone * 3600 * 1000;
        // Добавление js-скрипта для поддержки
        $html .= '<script type="text/javascript">
                     var timeblock = document.getElementById("dof_departmens_dateblock_currenttime");
                     var weekblock = document.getElementById("dof_departmens_dateblock_dayweek");
                     var dateblock = document.getElementById("dof_departmens_dateblock_date");
    
                     var days = [
                     "'.get_string('sunday', 'calendar').'",
                     "'.get_string('monday', 'calendar').'",
                     "'.get_string('tuesday', 'calendar').'",
                     "'.get_string('wednesday', 'calendar').'",
                     "'.get_string('thursday', 'calendar').'",
                     "'.get_string('friday', 'calendar').'",
                     "'.get_string('saturday', 'calendar').'"
                     ];
    
                     function dof_departments_showTime() {
                     var now = Date.now() + '.$jstimestampoffset.';
                     var time = new Date(now);
                     if ( timeblock != null ) {
                     timeblock.innerHTML = (time.getUTCHours()<10?"0":"") + time.getUTCHours() + ":" +
                     (time.getUTCMinutes()<10?"0":"") + time.getUTCMinutes();
                     }
                     if ( weekblock != null ) {
                     weekblock.innerHTML = days[time.getUTCDay()];
                     }
                     if ( dateblock != null ) {
                     dateblock.innerHTML = (time.getUTCDate()<10?"0":"") + time.getUTCDate() + "." +
                     (time.getUTCMonth()<9?"0":"") + (Number(time.getUTCMonth()) + Number(1)) + "." +
                     time.getUTCFullYear();
                     }
                     setTimeout("dof_departments_showTime()", 10000)
                     }
                     window.onload=function(){
                     setTimeout(dof_departments_showTime(), 10000);
                     }
                </script>';
        $html .= dof_html_writer::end_div();
        return $html;
    }
    
    /**
     * Блок отображения дополнительной информации подразделения
     *
     * @param number $departmentid - ID подразделения
     * @param array $options - Дополнительные опции отображения
     */
    private function block_department_additional($departmentid = 0, $options = [])
    {
        // Подготовка данных для формирования блока
        $html = '';
        $currentperson = $this->dof->storage('persons')->get_bu();
        // Получение текущего URL
        $currenturl = $this->dof->modlib('nvg')->get_url();
        $currentdepid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = ['departmentid' => $currentdepid];
    
        if ( empty($departmentid) )
        {// Подразделение не указано
            $departmentid = $currentdepid;
        }
    
        // Проверка наличия подразделения
        $department = $this->dof->storage('departments')->get($departmentid);
        if ( empty($department) )
        {// Подразделение не найдено
            return $html;
        }
    
        $html .= dof_html_writer::start_div('dof_departmens_depaddinfo_wrapper');

        // Блок руководителя подразделения
        $html .= dof_html_writer::start_div('dof_departmens_infoblock_dmanager');
        $html .= $this->dof->get_string('manager', 'departments').' ';
        $html .= dof_html_writer::start_tag('span', ['class' => 'dof_departmens_infoblock_dmanager_name']);
        if ( $department->managerid > 0 )
        {// Указан руководитель
            // Проверка доступа на просмотр персоны
            $access = $this->dof->storage('persons')->is_access('view', $department->managerid);
            $html .= $this->dof->im('persons')->get_fullname($department->managerid, $access);
        } else
        {// Руководитель не указан
            $html .= $this->dof->get_string('none', 'departments');
        }
        $html .= dof_html_writer::end_tag('span');
        $html .= dof_html_writer::end_div();
    
        // Блок действий над подразделением
        $html .= dof_html_writer::start_div('dof_departmens_infoblock_dactions');
        // Ссылка на настройки подразделения
        $access = $this->dof->is_access('admin');
        if ( $access )
        {// Доступ есть
            $attroptions['title'] = $this->dof->get_string('cfg', 'admin');
            $attroptions['class'] = 'dof_departmens_infoblock_da_settings';
            $html .= $this->dof->modlib('ig')->icon(
                'h_settings',
                $this->dof->url_im('cfg', '/index.php', $addvars),
                $attroptions
            );
        }
        // Ссылка на просмотр прав
        $access = $this->dof->im('acl')->is_access('aclwarrantagents:view');
        if ( $access && isset($currentperson->id) )
        {// Доступ есть
            $attroptions['title'] = $this->dof->get_string('view_acl_person', 'acl');
            $attroptions['class'] = 'dof_departmens_infoblock_da_myacl';
            $addvars['id'] = $currentperson->id;
            $html .= $this->dof->modlib('ig')->icon(
                'h_user',
                $this->dof->url_im('acl', '/personacl.php', $addvars),
                $attroptions
            );
            unset($addvars['id']);
        }
        // Cсылка на просмотр подразделения
        $access = ( $this->dof->storage('departments')->is_access('view/mydep', $departmentid) ||
                    $this->dof->storage('departments')->is_access('view', $departmentid) );
        if ( $access )
        {// Доступ есть
            $attroptions['title'] = $this->dof->get_string('view', 'departments');
            $attroptions['class'] = 'dof_departmens_infoblock_da_view';
            $addvars['id'] = $departmentid;
            $html .= $this->dof->modlib('ig')->icon(
                'h_search',
                $this->url('/view.php', $addvars),
                $attroptions
            );
            unset($addvars['id']);
        }
        // Cсылка на редактирование подразделения
        $access = ( $this->dof->storage('departments')->is_access('edit/mydep', $departmentid) ||
                    $this->dof->storage('departments')->is_access('edit', $departmentid) );
        if ( $access )
        {
            $attroptions['title'] = $this->dof->get_string('edit', 'departments');
            $attroptions['class'] = 'dof_departmens_infoblock_da_edit';
            $addvars['id'] = $departmentid;
            $html .= $this->dof->modlib('ig')->icon(
                'h_edit',
                $this->url('/edit.php', $addvars),
                $attroptions
                );
            unset($addvars['id']);
        }
        $html .= dof_html_writer::end_div();
        
        $html .= dof_html_writer::end_div();
        return $html;
    }
    
    /**
     * Блок отображения дочерних подразделений
     *
     * @param number $departmentid - ID подразделения
     * @param array $options - Дополнительные опции отображения
     * 
     * @return string - HTML-код блока дочерних подразделений
     */
    private function block_department_information_footer($departmentid = 0, $options = [])
    {
        // Подготовка данных для формирования блока
        $html = '';
    
        // Получение текущего URL
        $currenturl = $this->dof->modlib('nvg')->get_url();
    
        // Получение прямых потомков текущего подразделения
        $statuses = $this->dof->workflow('departments')->get_meta_list('actual');
        $statuses = array_keys($statuses);
        $subdepartments = (array)$this->dof->storage('departments')->
            get_records(['status' => $statuses, 'leaddepid' => (int)$departmentid]);
        
        $html .= dof_html_writer::start_div('dof_departmens_inf_footer');
        // Блок доступа к дочерним подразделениям
        $html .= dof_html_writer::start_div('dof_departmens_inf_subdep_wrapper');
        foreach ( $subdepartments as $department )
        {
            if ( $this->dof->storage('departments')->is_access('view', NULL, NULL, $department->id) ||
                 $this->dof->storage('departments')->is_access('view/mydep', NULL, NULL, $department->id) )
            {// Есть право на просмотр подразделения
                $html .= dof_html_writer::start_div('dof_departmens_inf_subdep');
                // URL для перехода к подразделению
                $url = preg_replace('/departmentid=\d*/', 'departmentid='.$department->id, $currenturl);
                if ( ! empty($department->code) )
                {
                    // Получение блока кода подразделения
                    $code = $this->block_department_codeblock($department->code);
                    $code = dof_html_writer::div($code, 'dof_departmens_inf_f_p_code');
                }
                if ( ! empty($department->name) )
                {
                    $name = dof_html_writer::div($department->name, 'dof_departmens_inf_f_p_name');
                }
                $infoblock = dof_html_writer::div('<div></div>', 'dof_departmens_inf_f_p_icon').$code.$name;
                $html .= dof_html_writer::link($url, $infoblock, ['class' => 'dof_departmens_inf_f_subdep_link']);
                $html .= dof_html_writer::end_div();
            }
        }
        $html .= dof_html_writer::end_div();
        
        // Блок быстрого перехода
        $html .= dof_html_writer::start_div('dof_departmens_inf_fastlink_wrapper');
        // Получение подразделений для списка
        $availabledeps = $this->get_departments_select_options();
        if ( ! empty($availabledeps) )
        {// Есть подразделения для смены
            // Опции формирования списка
            $addoptions = [];
            $addoptions['disabled_options'] = [];
            // Формирование списка отключенных опций
            foreach ( $availabledeps as $key => $dep )
            {
                if ( is_string($key) )
                {// Отключенная опция списка
                    $key = (int)$key;
                    $addoptions['disabled_options'][$key] = $key;
                }
            }
            $addoptions['label'] = $this->dof->get_string('fast_change_department', 'departments').': ';
            $html .= $this->dof->modlib('widgets')->
                single_select($currenturl, 'departmentid', $availabledeps, $departmentid, $addoptions);
        }
        $html .= dof_html_writer::end_div();
        $html .= dof_html_writer::end_div();
        return $html;
    }
    
    /**
     * Получить HTML-блок кода подразделения
     * 
     * Блок позволяет ограничить длиные названия кодов
     *
     * @param string $code - Код подразделения
     * @param array $options - Дополнительные опции отображения блока
     * 
     * @return string - HTML-код блока
     */
    private function block_department_codeblock($code = '', $options = [])
    {
        // Нормализация входных данных
        $code = (string)$code;
        $options = (array)$options;
        
        $html = '';
        
        $html .= dof_html_writer::start_div('dof_departmens_depcode');
        if ( strlen($code) > 20 )
        {// Свернутое отображение кода
            $html .= dof_html_writer::checkbox('trigger', '', false, $this->dof->get_string('show_code', 'departments'));
        }
        $html .= dof_html_writer::span($code);
        $html .= dof_html_writer::end_div();
        
        return $html;
    }
    
    
    /**
     * Получить массив доступных подразделений
     * 
     * Подразделения, которые не доступны для пользоваетля, 
     * но среди своих дочерних имеют доступные, возвращаются со строковым ключем
     * 
     * @param int $parentdep - Подразделение, от которого начинается сбор массива
     * @param array $options - Массив опций сбора данных
     *              ['delimiter'] - Разделитель для уровней иерархии. По - умолчанию '-' 
     *              ['statuses'] -  Массив статусов в виде ['status1', 'status2']. По-умолчанию - actual метастатус
     * 
     * @return array - Массив для добавления в select список
     */
    public function get_departments_select_options($parentdep = 0, $options = [])
    {
        $available = [];
        
        // Нормализация
        if ( ! isset($options['statuses']) )
        {// Статусы не переданы
            // Добавление свойства
            $actualstatuses = $this->dof->workflow('departments')->get_meta_list('actual');
            $options['statuses'] = array_keys($actualstatuses);
        }
        if ( ! isset($options['delimiter']) )
        {// Разделитель не определен
            // Стандартный разделитель
            $options['delimiter'] = '–';
        }
        if ( ! isset($options['_level']) )
        {// Системная опция уровня вложенности
            // Стандартный разделитель
            $options['_level'] = 0;
        }
        
        
        $subdeps = [];
        // Подразделения текущего уровня
        $departments = $this->dof->storage('departments')->
            get_records(['leaddepid' => $parentdep, 'status' => $options['statuses']], 'code');
        $delimiter = str_repeat($options['delimiter'], $options['_level']);
        $options['_level']++;
        // Добавление дочерних подразделений
        foreach ( $departments as $department )
        {
            $subdeps = $subdeps + $this->get_departments_select_options($department->id, $options);
        }
        
        $access = ( $this->dof->storage('departments')->is_access('view', NULL, NULL, $parentdep) ||
               $this->dof->storage('departments')->is_access('view/mydep', NULL, NULL, $parentdep) );
        if ( ! empty($subdeps) || $access)
        {// Право на просмотр подразделения есть, либо есть дочерние доступные подразделения
            // Формирование ключа
            $key = (integer)$parentdep;
            if ( ! $access )
            {// Доступа нет, ключ - строка
                // Для сохранения строкового типа ключа требуется добавить в начале 0
                $key = '0'.(string)$key;
            }
            // Формирование значения
            $keyval = $this->dof->get_string('notfound', 'departments', $parentdep);
            if ( $parentdep == 0 )
            {// Все объекты
                $keyval = $this->dof->get_string('see_allobj', 'departments');
            } else
            {// Получение кода подразделения
                $department = $this->dof->storage('departments')->get($parentdep);
                if ( ! empty($department) )
                {
                    $keyval = $department->code;
                }
            }
            $available = $available + [$key => $delimiter.$keyval] + $subdeps;
        }
        
        return $available;
    }
}