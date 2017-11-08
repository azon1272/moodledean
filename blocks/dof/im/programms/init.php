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
/*
 * Плагин "учебные программы"
 * @todo полностью переработать методы, оставшиеся от ages
 */
class dof_im_programms implements dof_plugin_im
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
        return 'programms';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     * @todo добавить зависимость от workflow/programms когда этот плагин будет создан
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'=>2008060300,
                                     'widgets'=>2009050800),
                     'storage'=>array('persons'=>2009060400,
                                      'departments'=>2009040800,
                                      'programms'=>2009040800,
                                      'acl' => 2011041800));
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
            $notice = "programms/{$do} (block/dof/im/programms: {$do})";
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
            if ( $mixedvar['storage'] == 'programms' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр периода
                    $params = array('programmid' => $intvar);
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
    function get_block($name, $id = null)
    {
        $rez = '';
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        switch ($name)
        {
            case 'main':
                $path = $this->dof->url_im('programms','/index.php',$addvars);
//                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('title', 'programms').'</a>';
//                $rez .= "<br />";
                if ( $this->dof->storage('programms')->is_access('view') )
                {//может видеть все учебные программы
                    $path = $this->dof->url_im('programms','/list.php',$addvars);
                }
                //ссылка на список подразделений
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('list', 'programms').'</a>';
                if ( $this->dof->storage('programms')->is_access('create') )
                {//может создавать период - покажем ссылку
                    $rez .= "<br />";
                    $path = $this->dof->url_im('programms','/edit.php',$addvars);
                    $rez .= "<a href=\"{$path}\">".$this->dof->get_string('new', 'programms').'</a>';
                }
            break;
        }
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
        return "<a href='{$this->dof->url_im('programms','/index.php')}'>"
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
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);;
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('programms')->get_field($objectid, 'departmentid');
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
     * информации об учебной программе
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
    
    /** Возвращает html-код отображения 
     * информации об учебной программе
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id,$conds)
    {
        if ( ! is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
    	if ( ! $obj = $this->dof->storage('programms')->get($id) )
    	{// учебная программа не найдена
    		return false;
    	} 
    	return $this->show($obj,$conds);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких учебных программах
     * @param массив $list - массив записей 
     * учебных программ, которые надо отобразить 
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
     * @return moodle quickform object
     */
    public function form($id = NULL)
    {
        global $USER;
        // устанавливаем начальные данные
        if (isset($id) AND ($id <> 0) )
        {// id передано
            $programm = $this->dof->storage('programms')->get($id);
            $programm->department = $programm->departmentid; 
        }else
        {// id не передано
            $programm = $this->form_new_data();
        }
        if ( isset($USER->sesskey) )
        {//сохраним идентификатор сессии
            $programm->sesskey = $USER->sesskey;
        }else
        {//идентификатор сессии не найден
            $programm->sesskey = 0;
        }
        $customdata = new stdClass;
        $customdata->programm = $programm;
        $customdata->dof      = $this->dof;
        // подключаем методы вывода формы
        $form = new dof_im_programms_edit_form(null,$customdata);
        // очистим статус, чтобы не отображался как в БД
        unset($programm->status);
        // заносим значения по умолчению
        $form->set_data($programm); 
        // возвращаем форму
        return $form;
    }
    
    /**
     * Возвращает заготовку для формы создания периода
     * @return stdclassObject
     */
    private function form_new_data()
    {
        $programm = new stdClass();
        $programm->id = 0;
        $programm->enddate = $programm->begindate = time();
        $programm->departmentid = optional_param('departmentid', 0, PARAM_INT);
        return $programm;
    }
    
   /** Возвращает html-код таблицы
     * @param array $date - данные в таблицу
     * @return string - html-код или пустая строка
     */
    private function print_table($strings)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center","center","center","center","center","center","center");
        // шапка таблицы
        $table->head = $this->get_fields_description('<br>');
        // заносим данные в таблицу     
        $table->data = $strings;
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
        $descriptions = $this->get_fields_description();
        $i = 0;
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>'.current(each($descriptions)).'</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить заголовок для списка таблицы, или список полей
     * для отображения одного объекта 
     * @param string $a - используется для разделителя строки, по умолчанию пусто
     * @return array
     */
    private function get_fields_description($a='')
    {
        return array($this->dof->get_string('actions','programms'),
                     $this->dof->get_string('name','programms'),
                     $this->dof->get_string('code','programms'),
                     $this->dof->get_string('about','programms'),
                     $this->dof->get_string('department','programms'),
                     $this->dof->get_string('notes','programms'),
                     $this->dof->get_string('duration','programms'),
                     $this->dof->get_string('agenums','programms',$a),
                     $this->dof->get_string('status','programms'),
                     $this->dof->get_string('billingtext','programms'),
                     $this->dof->get_string('flowagenums','programms'),
                     $this->dof->get_string('edulevel','programms'));
    }
    
    /** Возвращает массив для вставки в таблицу
     * @param object $obj
     * @param string $conds
     * @param string $show
     * @return array
     */
    private function get_string_table($obj,$conds,$show='list')
    {
        // для ссылок вне плагина
        $conds = (array) $conds;
        $outconds = array();
        $outconds['departmentid'] = $conds['departmentid'];
        $data = array();
        // заносим данные в таблицу
        if ( $this->dof->storage('departments')->is_access('view', $obj->departmentid) )
        {// ссылка на подразделение (если есть права)
            $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
        }else
        {
            $department = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                          $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
        }
        
        // устанавливаем время в днях
        $duration   = $obj->duration/(3600 * 24).' '.$this->dof->get_string('days', 'programms');
        
        //получаем картинки
        //картинка на редактирование программы
        $imgedit = '<img src="'.$this->dof->url_im('programms', '/icons/edit.png').'"
        alt="'.$this->dof->get_string('edit', 'programms').'" title="'.$this->dof->get_string('edit', 'programms').'">';
        //картинка на просмотр программы
        $imgview = '<img src="'.$this->dof->url_im('programms', '/icons/view.png').'" 
        alt="'.$this->dof->get_string('view', 'programms').'" title="'.$this->dof->get_string('view', 'programms').'">';
        //картинка на просмотр подписок на программу
        $imgprogrammsbcs = '<img src="'.$this->dof->url_im('programms', '/icons/programmsbcs.png').'" 
        alt="'.$this->dof->get_string('programmsbcs_list', 'programms').'" title="'.$this->dof->get_string('programmsbcs_list', 'programms').'">';
        //картинка на просмотр групп, обучающихся по программе
        $imggroups = '<img src="'.$this->dof->url_im('programms', '/icons/group.gif').'" 
        alt="'.$this->dof->get_string('groups_list', 'programms').'" title="'.$this->dof->get_string('groups_list', 'programms').'">';
        //картинка на просмотр предметов программы
        $imgprogrammitems = '<img src="'.$this->dof->url_im('programms', '/icons/programmitems.png').'" 
        alt="'.$this->dof->get_string('programmitems_list', 'programms').'" title="'.$this->dof->get_string('programmitems_list', 'programms').'">';
        // картинка создания учебных потокаов для программы
        $imgallcstreams = '<img src="'.$this->dof->url_im('programms', '/icons/create_cstreams.png').'" 
        alt="'.$this->dof->get_string('create_cstream_for_programm', 'programms').'" title="'.
        $this->dof->get_string('create_cstream_for_programm', 'programms').'">';
        // картинка на просмотр учебного процесса по программе
        $imgcstreams = '<img src="'.$this->dof->url_im('programms', '/icons/view_edu_process.png').'" 
        alt="'.$this->dof->get_string('participants_cstreams', 'programms').'" title="'.
        $this->dof->get_string('participants_cstreams', 'programms').'">';
        // добавляем ссылку
        $link = '';
        if ( $this->dof->storage('programms')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования программы
            $link .= '<a href="'.$this->dof->url_im('programms','/edit.php?programmid='.
            $obj->id,$conds).'" id="edit_programm_'.$obj->id.'">'.$imgedit.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programms')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра программы
            $link .= '<a href='.$this->dof->url_im('programms','/view.php?programmid='.
            $obj->id,$conds).' id="view_programm_'.$obj->id.'">'.$imgview.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmsbcs')->is_access('view') )
        {//покажем ссылку на список подписок на изучения программы
            $link .= '<a href='.$this->dof->url_im('programmsbcs','/list_persons.php?programmid='.
            $obj->id,$outconds).' id="view_cstreams_for_programm_'.$obj->id.'">'.$imgprogrammsbcs.'</a>&nbsp;';
        }
        if ( $this->dof->storage('agroups')->is_access('view') )
        {// список групп, обучающихся по программе
            $link .= '<a href='.$this->dof->url_im('agroups','/list.php?programmid='.
            $obj->id,$outconds).' id="view_agroups_for_programm_'.$obj->id.'">'.$imggroups.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmitems')->is_access('view') )
        {// просмотр состава учебной программы
            $link .= '<a href='.$this->dof->url_im('programmitems','/list_agenum.php?programmid='.
            $obj->id,$outconds).' id="view_programmitems_for_programm_'.$obj->id.'">'.$imgprogrammitems.'</a>&nbsp;';
        }
        if ( $this->dof->storage('cstreams')->is_access('create') )
        {//покажем ссылку на страницу массового создания потоков
            if ( $ages = $this->dof->storage('ages')->get_records(array('status' => 'plan')) )
            {// если есть периоды на которые еще не создано потоков
                // @todo более надежным способом получать все периоды, у которых нет потоков.
                // Возможно надо добавить фильтр по подразделению
                //добавляем ссылку
	            $link .= '<a href='.$this->dof->url_im('cstreams','/create_cstreams_forprogramm.php?programmid='.
	            $obj->id,$outconds).' id="create_cstreams_for_programm_'.$obj->id.'">'.$imgallcstreams.'</a>&nbsp;';
            }
        }
        if ( $this->dof->im('cstreams')->is_access('viewcurriculum') )
        {//покажем ссылку на страницу просмотра
            $link .= '<a href='.$this->dof->url_im('cstreams','/by_groups.php?programmid='.
            $obj->id,$outconds).' id="view_curriculum_for_programm_'.$obj->id.'">'.$imgcstreams.'</a>&nbsp;';
        }
        $status = $this->dof->workflow('programms')->get_name($obj->status);
        if ( !empty($obj->flowagenums) )
        {
            $flowagenums = $this->dof->modlib('ig')->igs('yes');
        } else
        {
            $flowagenums = $this->dof->modlib('ig')->igs('no');
        }
        $edulevelstring = $this->dof->modlib('refbook')->get_edulevel($obj->edulevel);
        $data = array($obj->name,$obj->code,$obj->about,$department,$obj->notice, $duration, $obj->agenums, $status, $obj->billingtext, $flowagenums, $edulevelstring); 
        array_unshift($data, $link);
        return $data;
    }

    /**
     * Возваращает html-код таблицы, выводящей 
     * список программ, упорядоченных по подразделениям
     * если нет программ или подразделений - 
     * выводится соответствующее сообщение
     * @return string
     */
    public function get_programms_by_departments()
    {
        //получаем все подразделения
        $alldepartments = $this->dof->storage('departments')->departments_list();
        $rez = '';
        if ( ! $alldepartments )
        {//нет подразделений
            $rez .= '<p align="center"><i>'.$this->dof->get_string('departments_not_found', $this->code()).'</i></p>';
        }
        //подразделения есть - формируем таблицу
        
        //перебираем их
        foreach ( $alldepartments as $id => $name )
        {
            $one = new stdClass();
            $one->id = $id;
            $one->name = $name;
            $rez .= '<br />'.$this->get_department_programms($one);
        }
        return $rez;
    }
    
    /**
     * Возвращет html-код таблицы,
     * заголовком которой является название подразделения, 
     * а строками - название программы.
     * Если программы не найдены, выводится сообщение
     * @param object $department - запись из таблицы departments 
     * @return string
     */
    public function get_department_programms($department)
    {
        //Сделаем название подразделения ссылкой
        if ( $this->dof->storage('departments')->is_access('view', $department->id) )
        {// если есть право просматривать подразделение
            // сделаем его ссылкой
            $path = $this->dof->url_im('departments','/view.php?departmentid='.$department->id);
            $linkdepartment = "<a href=\"{$path}\">{$department->name}</a>"; 
        }else
        {// просто выведем имя
            $linkdepartment = $department->name;
        }
        //создадим таблицу для программ одного подразделения
        $table = new stdClass();
        //заголовок таблицы
        $table->head = array($linkdepartment);
        //данные тоблицы
        $table->data = array();
        //получим все программы подразделения
        $programms = $this->dof->storage('programms')->get_programms_list($department->id);
        if ( ! $programms )
        {//не получили - сообщим об этом
            $table->data[] = array('<i>'.
            $this->dof->get_string('programms_not_found',$this->code()).'</i>');
        }else
        {//получили - заносим в таблицу
            foreach ( $programms as $one )
            {
                // имя программы
                $name = $one->name.' [<i>'.$one->code.'</i>]';
                if ( $this->dof->storage('programms')->is_access('view', $one->id) )
                {// если есть право просматривать программу
                    //Сделаем название программы ссылкой
                    $path = $this->dof->url_im('programms','/view.php?programmid='.$one->id.'&departmentid='.$department->id);
                    $name = "<a href=\"{$path}\" id='view_programm_{$one->id}'>{$name}</a>";
                }
                //добавляем в таблицу
                $table->data[] = array($name);
            }
        }
        //возвращаем таблицу
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    /**
     * Возвращает объект приказа
     *
     * @param string $code - код приказа
     * @param integer  $id - id программы
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        require_once($this->dof->plugin_path('im','programms','/orders/change_status/init.php'));
        switch ($code)
        {
            case 'change_status':
                $order = new dof_im_programms_order_change_status($this->dof);
                if ( ! is_null($id))
                {// нам передали id, загрузим приказ
                    if ( ! $order->load($id))
                    {// Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
            break;
        }
    }
    
    /** Получить название программы в виде ссылки
     * @param int id - id программы в таблице programms
     * @param bool $withcode - добавлять или не добавлять код в конце
     * 
     * @return string html-строка со ссылкой на подразделение или пустая строка в случае ошибки
     */
    public function get_html_link($id, $withcode=false)
    {
        if ( ! $name = $this->dof->storage('programms')->get_field($id, 'name') )
        {
            return '';
        }
        if ( $withcode )
        {
            $code = $this->dof->storage('programms')->get_field($id, 'code');
            $name = $name.' ['.$code.']';
        }
        return '<a href="'.$this->dof->url_im($this->code(), 
                    '/view.php', array('programmid' => $id)).'" id="_link_view_programm_'.$id.'">'.$name.'</a>';
    }

}
