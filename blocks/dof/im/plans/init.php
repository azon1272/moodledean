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

/** Тематическое планирование
 * 
 */
class dof_im_plans implements dof_plugin_im
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
        return 'plans';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'         => 2008060300,
                                     'widgets'     => 2009050800),
                     'storage'=>array('departments'=> 2009040800,
                                      'plans'      => 2009011601,
                                      'acl'        => 2011062100));
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
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('manage') 
             OR $this->dof->is_access('admin') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);  
        switch ($do)
        {
            case 'viewthemeplan/my' :
            case 'editthemeplan:cstreams/my' :
            case 'editthemeplan:plan/my' :
                if ( $personid != $this->dof->storage('cstreams')->get_field($objid,'teacherid') )
                {// редактировать свое планирование может только учитель потока
                    return false;                  
                }             
            break;             
        }
 
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
            $notice = "plans/{$do} (block/dof/im/plans: {$do})";
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
            if ( $mixedvar['storage'] == 'plans' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр периода
                    $params = array('pointid' => $intvar);
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
    public function get_block($name, $id = 1)
    {
        $rez = '';
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        switch ($name)
        {
            
        }
        return $rez;
    }
    /** Возвращает html-код, который отображается внутри секции
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 1)
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
        return "<a href='{$this->dof->url_im('plans','/index.php')}'>"
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
    
    /** Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        $a['viewthemeplan'] = array('roles'=>array('manager','methodist'));
        $a['viewthemeplan/my'] = array('roles'=>array('manager','teacher'));
        $a['editthemeplan'] = array('roles'=>array('manager'));
        $a['editthemeplan:ages'] = array('roles'=>array('manager'));
        $a['editthemeplan:programmitems'] = array('roles'=>array('manager','methodist'));
        $a['editthemeplan:cstreams'] = array('roles'=>array('manager'));
        $a['editthemeplan:plan'] = array('roles'=>array('manager'));
        $a['editthemeplan:cstreams/my'] = array('roles'=>array('manager','teacher'));
        $a['editthemeplan:plan/my'] = array('roles'=>array('manager','teacher'));
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
     * Возвращает html-код отображения 
     * информации об учебной группе
     * @param stdClass $obj - запись из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show($obj,$conds)
    {
        if (! is_object($obj))
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
   		$data = $this->get_string_table($obj,$conds,'single');
    	// выводим таблицу на экран
        return $this->print_single_table($data);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации об учебной группе
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id,$conds)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('plans')->get($id) )
    	{// период не найден
    		return false;
    	} 
    	return $this->show($obj,$conds);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких группах
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function showlist($list,$conds)
    {
        if ( ! is_array($list))
        {// переданны данные неверного формата
        	return false;
        }
        $data = array();
    	// заносим данные в таблицу
    	foreach ($list as $obj)
    	{   
    	    $data[] = $this->get_string_table($obj,$conds);
    	}
    	
    	// выводим таблицу на экран
        return $this->print_table($data);
    }
    
    /**
     * Возвращает форму создания/редактирования с начальными данными
     * @param int $id - id записи, значения 
     * которой устанавливаются в поля формы по умолчанию
     * @param object - объект с данными по умолчанию, которые должны стоять в форме
     *             эти данные передаются для создания новой записи
     * @return moodle quickform object
     */
    public function form($id = NULL, $defaults=null)
    {
        global $USER;
        // устанавливаем начальные данные
        if (isset($id) AND ($id <> 0) )
        {// id передано
            $point = $this->dof->storage('plans')->get($id); 
        }else
        {// id не передано
            $point = $this->form_new_data($defaults);
        }
        if ( isset($USER->sesskey) )
        {//сохраним идентификатор сессии
            $point->sesskey = $USER->sesskey;
        }else
        {//идентификатор сессии не найден
            $point->sesskey = 0;
        }
        $customdata = new stdClass;
        $customdata->point    = $point;
        $customdata->dof      = $this->dof;
        $customdata->linktype = '';
        if ( isset($defaults->linktype) )
        {
            $customdata->linktype = $defaults->linktype;
        }
        $customdata->linkid = '';
        if ( isset($defaults->linkid) )
        {
            $customdata->linkid = $defaults->linkid;
        }
        
        if ( isset($point->status) AND $point->status == 'deleted' )
        {// если контрольная точка удалена - запретим ее редактировать
            $form = new dof_im_plans_edit_form(null,$customdata, 'post', '', null, false);
        }else
        {// в остальных случаях - разрешим
            $form = new dof_im_plans_edit_form(null,$customdata);
        }
        // подключаем методы вывода формы
        
        // очистим статус, чтобы не отображался как в БД
        unset($point->status);
        // заносим значения по умолчению
        $form->set_data($point); 
        // возвращаем форму
        return $form;
    }
    
    /**
     * Возвращает заготовку для формы создания группы
     * @return stdclassObject
     */
    private function form_new_data($defaults=null)
    {
        $point = new stdClass();
        $point->id = 0;
        $point->parentid = 0;
        $point->linktype = 0;
        $point->linkid = 0;
        if ( isset($defaults->linktype) )
        {// если тип связи указан - установим его в форме по умолчанию
            $point->linktype = $defaults->linktype;
        }
        if ( isset($defaults->linkid) )
        {// если id привязки указан - установим его в форме по умолчанию
            $point->linkid = $defaults->linkid;
        }
        
        return $point;
    }
    
   /** Возвращает html-код таблицы
     * @param array $date - данные в таблицу
     * @return string - html-код или пустая строка
     */
    private function print_table($date)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head = $this->get_fields_description();
        // заносим данные в таблицу     
        $table->data = $date;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * 
     * @return null
     * @param object $data объект с отображаемыми значениями
     */
    private function print_single_table($data)
    {
        $table = new stdClass();
        if ( ! $data )
        {
            return '';
        }
        // получаем подписи с пояснениями
        $descriptions = $this->get_fields_description('single');
        $i = 0;
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.current(each($descriptions)).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для списка отображения одного объекта 
     * @return array
     * @param string $type - Тип отображения: 
     *                             - list - список всех контрольных точек
     *                             - single - одна контрольная точка
     * 
     */
    private function get_fields_description($type='list')
    {
        if ( $type=='list' )
        {
            return  array($this->dof->get_string('actions',$this->code()),
                     $this->dof->get_string('name',$this->code()),
                     $this->dof->get_string('type',$this->code()),
                     $this->dof->get_string('linktype',$this->code()),
                     $this->dof->get_string('linkid',$this->code()),
                     $this->dof->get_string('parenttheme',$this->code()),
                     $this->dof->get_string('reldate',$this->code(), '<br/>'),
                     $this->dof->get_string('reldldate',$this->code(), '<br/>'),
                     $this->dof->get_string('scale',$this->code()),
                     $this->dof->get_string('typesync',$this->code()),
                     $this->dof->get_string('directmap',$this->code(), '<br/>'),
                     $this->dof->get_string('mdlinstance',$this->code()),
                     $this->dof->get_string('status',$this->code()),
                     $this->dof->get_string('homework',$this->code(), '<br/>'),
                     $this->dof->get_string('homeworkhours',$this->code(), '<br/>'));
        }elseif ( $type=='single' )
        {
            return  array($this->dof->get_string('name',$this->code()),
                     $this->dof->get_string('type',$this->code()),
                     $this->dof->get_string('linktype',$this->code()),
                     $this->dof->get_string('linkid',$this->code()),
                     $this->dof->get_string('parenttheme',$this->code()),
                     $this->dof->get_string('reldate',$this->code()),
                     $this->dof->get_string('reldldate',$this->code()),
                     $this->dof->get_string('scale',$this->code()),
                     $this->dof->get_string('typesync',$this->code()),
                     $this->dof->get_string('directmap',$this->code()),
                     $this->dof->get_string('mdlinstance',$this->code()),
                     $this->dof->get_string('status',$this->code()),
                     $this->dof->get_string('homework',$this->code()),
                     $this->dof->get_string('homeworkhours',$this->code()),
                     $this->dof->get_string('actions',$this->code()));
        }
         
    }
    
    /** Возвращает массив для вставки в таблицу
     * @param object $obj
     * @param string $conds
     * @param string $show
     * @return array
     */
    private function get_string_table($obj,$conds,$show='list')
    {
        // получим типы темы
   		$typearray = $this->dof->modlib('refbook')->get_lesson_types();
   		if ( empty($obj->type) OR empty($typearray[$obj->type]) OR ! ($type = $typearray[$obj->type]) )
   		{// если типа у темы нет - выведем пустую строчку
   		    $type = '';
   		}
   		// выведем тип привязки
   		$linktype = $this->dof->get_string(substr($obj->linktype, 0, -1), $this->code());
   		// имя радотельской точки
   		if ( ! $parentname = $this->dof->storage($this->code())->get_field($obj->parentid,'name') )
        {//кода статуса нет - выведем пустую строчку
            $parentname = $this->dof->get_string('none', $this->code());
        }
        $linkid = $this->dof->storage($obj->linktype)->get($obj->linkid);
        if ( $obj->linktype == 'ages' OR $obj->linktype == 'cstreams' )
        {// для периода выведем только имя
            $linkname = $linkid->name;
        }elseif ( $obj->linktype == 'programmitems' )
        {// для предмета имя с кодом
            $linkname = $linkid->name.' ['.$linkid->code.']';
        }
   		//получаем ссылки на картинки
        $imgedit = '<img src="'.$this->dof->url_im($this->code(), '/icons/edit.png').'"
            alt="'.$this->dof->modlib('ig')->igs('edit').'" title="'.$this->dof->modlib('ig')->igs('edit').'">';
        $imgview = '<img src="'.$this->dof->url_im($this->code(), '/icons/view.png').'" 
            alt="'.$this->dof->modlib('ig')->igs('view').'" title="'.$this->dof->modlib('ig')->igs('view').'">';
        $imgdel = '<img src="'.$this->dof->url_im($this->code(), '/icons/delete.png').'" 
            alt="'.$this->dof->modlib('ig')->igs('delete').'" title="'.$this->dof->modlib('ig')->igs('delete').'">';
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('plans')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $link .= '<a id="edit_plan_'.$obj->id.'" href='.$this->dof->url_im($this->code(),'/edit.php?pointid='.
            $obj->id,$conds).'>'.$imgedit.'</a>&nbsp;';
        }
        if ( $this->dof->storage('plans')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $link .= '<a id="view_plan_'.$obj->id.'" href='.$this->dof->url_im($this->code(),'/view.php?pointid='.
            $obj->id,$conds).'>'.$imgview.'</a>&nbsp;';
        }
        if ( $this->dof->storage('plans')->is_access('edit', $obj->id) AND
             $obj->status != 'deleted' )
        {// если у пользователя есть право удалять элемент, и элемент еще не удален
            $link .= '<a id="delete_plan_'.$obj->id.'" href='.$this->dof->url_im($this->code(),'/delete.php?planid='.
            $obj->id,$conds).'>'.$imgdel.'</a>&nbsp;';
        }
        if ( $obj->status )
        {// выводим обозначение статуса
            $statusname = $this->dof->get_string('status:'.$obj->status,$this->code());
        }else
        {// не выводим статус если его нет
            $statusname = '';
        }
        
        if ( round($obj->reldate / ( 3600 * 24)) )
        {// устанавливаем дату начала и крайний срок сдачи в днях
            $reldate  = round($obj->reldate / ( 3600 * 24) ).
                $this->dof->get_string('-ii', 'plans').' '.$this->dof->get_string('days_small', 'plans');
        }else
        {// дата проведения не задана
            $reldate = $this->dof->get_string('not_set', 'plans');
        }
        
        if ( round($obj->reldldate / ( 3600 * 24)) )
        {// если указана крайняя дата сдачи
            $reldldate = round($obj->reldldate / ( 3600 * 24) ).
                $this->dof->get_string('-ii', 'plans').' '.$this->dof->get_string('days_small', 'plans');
        }else
        {// крайний срок сдачи не указан
            $reldldate = $this->dof->get_string('not_defined', 'plans');
        }
        $homeworkhours = '';
        if ( $obj->homeworkhours )
        {// часы на домашнее задание - приводим к нормальному виду
            $hours   = floor($obj->homeworkhours / 3600);
            $minutes = floor(($obj->homeworkhours - $hours * 3600) / 60);
              
            $homeworkhours .= $hours.' '.$this->dof->modlib('ig')->igs('hours').' ';
            $homeworkhours .= $minutes.' '.$this->dof->modlib('ig')->igs('minutes');
        }
        // определяем - отображается ли в планировании эта КТ
        $directmap = $this->dof->modlib('ig')->igs('yes');
        if ( ! $obj->directmap )
        {
            $directmap = $this->dof->modlib('ig')->igs('no');
        }
        $data = array($obj->name, $type, $linktype, $linkname,
                $parentname, $reldate,$reldldate, 
   	            $obj->scale, $obj->typesync, $directmap, $obj->mdlinstance, $statusname, 
                $obj->homework, $homeworkhours);
        
        if ( $show == 'single' )
        {
            return array_merge($data, array($link));
        }
   	    return array_merge(array($link), $data);
    }

    /** Возвращает список записей по заданным критериям 
     * 
     * @deprecated
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds - список параметров для выборки периодов 
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список периодов не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        if ( $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault(); 
        }
        if ( $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        $countselect = $this->get_select_listing($conds);
        // посчитаем общее количество записей, которые нужно извлечь
        $recordscount = $this->dof->storage('plans')->count_records_select($countselect);
        if ( $recordscount < $limitfrom )
        {// если количество записей в базе меньше, 
            //чем порядковый номер записи, которую надо показать  
            //покажем последнюю страницу
            $limitfrom = $recordscount;
        }
        //формируем строку запроса
        $select = $this->get_select_listing($conds);
        //определяем порядок сортировки
        $sort = 'reldate ASC, name ASC, status ASC';
        // возвращаем ту часть массива записей таблицы, которую нужно
        return $this->dof->storage('plans')->get_records_select($select,null,$sort, '*', $limitfrom, $limitnum);
    }
    
    /**
     * @deprecated
     * 
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( isset($conds->nameorcode) AND strlen(trim($conds->nameorcode)) )
        {// для имени используем шаблон LIKE
            $selects[] = " name LIKE '%".$conds->nameorcode."%'";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->nameorcode);
        }
        if ( isset($conds->ageid) AND strlen(trim($conds->ageid)) )
        {// сформируем запрос для периода
            $selects[] = " linktype = 'ages' AND linkid ='".$conds->ageid."'";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->ageid);
        }elseif ( isset($conds->cstreamid) AND strlen(trim($conds->cstreamid)) )
        {// сформируем запрос для потока
            $selects[] = " linktype = 'cstreams' AND linkid ='".$conds->cstreamid."'";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->cstreamid);
        }elseif ( isset($conds->programmitemid) AND strlen(trim($conds->programmitemid)) )
        {// сформируем запрос для предмета
            $selects[] = " linktype = 'programmitems' AND linkid ='".$conds->programmitemid."'";
            // убираем имя из запроса для того чтобы не создать 2 условия для одного поля
            unset($conds->programmitemid);
        }
        // теперь создадим все остальные условия
        foreach ( $conds as $name=>$field )
        {
            if ( $field )
            {// если условие не пустое, то для каждого поля получим фрагмент запроса
                $selects[] = $this->dof->storage('plans')->query_part_select($name,$field);
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

    /** Навинация в КТ
     *  @linktype str - имя плагина 
     *  @linkid int - id или предмета, или периода, или учебный процесса)
     *  @return null
     */
    public function nvg($linktype, $linkid,$addvars)
    {   
        $DOF=$this->dof;
        $go = '/themeplan/viewthemeplan.php?linktype='.$linktype.'&linkid='.$linkid; 
        switch ( $linktype )
        {   // если элемент планирования связан с предметом - то в обратной ссылке покажем 
            // список всех элементов планирования этого предмета 
            case 'programmitems': $DOF->modlib('nvg')->add_level($DOF->get_string('title', $linktype), $DOF->url_im($linktype).'/list.php',$addvars);
	    $DOF->modlib('nvg')->add_level($DOF->storage($linktype)->get($linkid)->name,
              $DOF->url_im($linktype,'/view.php?pitemid='.$linkid,$addvars));
	    $DOF->modlib('nvg')->add_level($DOF->get_string('list', 'plans'),
              $DOF->url_im('plans',$go,$addvars)); 
            return;
            break;
            // Если элемент связан с потоком - то покажем список всех КТ потока
            case 'cstreams': $DOF->modlib('nvg')->add_level($DOF->get_string('title', $linktype), $DOF->url_im($linktype).'/list.php',$addvars);
            $DOF->modlib('nvg')->add_level($DOF->storage($linktype)->get($linkid)->name,
              $DOF->url_im($linktype,'/view.php?cstreamid='.$linkid,$addvars));
            $DOF->modlib('nvg')->add_level($DOF->get_string('list', 'plans'),
              $DOF->url_im('plans',$go,$addvars)); 
            return;
            break;
            // plan
            case 'plan': $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), $DOF->url_im('cstreams').'/list.php',$addvars);
            $DOF->modlib('nvg')->add_level($DOF->storage('cstreams')->get($linkid)->name,
              $DOF->url_im('cstreams','/view.php?cstreamid='.$linkid,$addvars));
            $DOF->modlib('nvg')->add_level($DOF->get_string('list', 'plans'),
              $DOF->url_im('plans',$go,$addvars)); 
            return;
            break;
            case 'ages': $DOF->modlib('nvg')->add_level($DOF->get_string('title', $linktype), $DOF->url_im($linktype).'/list.php',$addvars);
            $DOF->modlib('nvg')->add_level($DOF->storage($linktype)->get($linkid)->name,
              $DOF->url_im($linktype,'/view.php?ageid='.$linkid,$addvars));
	    $DOF->modlib('nvg')->add_level($DOF->get_string('list', 'plans'),
              $DOF->url_im('plans',$go,$addvars));
            return;
            break;
            // во всех остальных случаях просто покажем ссылку на список всех тем
            default: $DOF->modlib('nvg')->add_level($DOF->get_string('list', 'plans'), $DOF->url_im('plans','/list.php',$addvars)); 
            return;
        }
    }

    /**
     * Вернуть массив с настройками или одну переменную
     * @param $key - переменная
     * @return mixed
     */
    public function get_cfg($key=null)
    {
    	// Возвращает параметры конфигурации
    	include ($this->dof->plugin_path($this->type(),$this->code(),'/cfg/cfg.php'));
    	if (empty($key))
    	{
    		return $im_plans;
    	}else
    	{
    		return @$im_plans[$key];
    	}
    } 

}