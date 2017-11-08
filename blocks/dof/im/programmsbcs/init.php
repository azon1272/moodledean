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

/** Подписки на программы
 * 
 */
class dof_im_programmsbcs implements dof_plugin_im
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
        return 'programmsbcs';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'=>array('nvg'      => 2008060300,
                                     'widgets'  => 2009050800),
                                     
                     'storage'=>array('persons'       => 2009060400,
                                      'ages'          => 2009050600,
                                      'programms'     => 2009040800,
                                      'programmsbcs'  => 2009052900,
                                      'agroups'       => 2009011601,
                                      'contracts'     => 2009101200,
                                      'acl' => 2011041800));
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
                  'plugincode' => 'persons',
                  'eventcode'  => 'persondata'),
                  
            array('plugintype' => 'im',
                  'plugincode' => 'obj',
                  'eventcode'  => 'get_object_url'),
            
            array('plugintype' => 'im',
                  'plugincode' => 'my',
                  'eventcode'  => 'info'));
    }
    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
       return 3600 * 24;
    }
    
    /** Проверяет полномочия на совершение действий
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                      по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     *              false - доступ запрещен
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
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "programmsbcs/{$do} (block/dof/im/programmsbcs: {$do})";
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
     * @return mixed 
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        $result = '';
        $depid = optional_param('departmentid', 0, PARAM_INT);
    
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {// отобразить все подписки персоны
            if ( $table = $this->get_table_programmsbcs($intvar, $depid) )
            {// у нас есть хотя бы одна подписка - выводим заголовок
                $heading = $this->dof->get_string('title', $this->code());
                $result .= $this->dof->modlib('widgets')->print_heading($heading, '', 2, 'main', true);
                $result .= $table;
            }
            
            return $result;
        }
        
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'programmsbcs' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр программы
                    $params = array('id' => $intvar, 'departmentid' => $depid);
                    
                    if ( isset($mixedvar['urlparams']) AND is_array($mixedvar['urlparams']) )
                    {
                        $params = array_merge($params, $mixedvar['urlparams']);
                    }
                    return $this->url('/view.php', $params);
                }
            }
        }
        
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info')
        {
            $sections = array();
            if ( $this->get_section('my_programmsbcs') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>$this->code(),'name'=>'my_programmsbcs','id'=>1, 'title'=>$this->dof->get_string('title', $this->code()));
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
        $result = true;
        
        if ( $loan == 3 )
        {// генерация отчетов запускаем только в режиме самых ресурсоемких операций
            // @todo уточнить plugintype и plugincode для отчетов
            $result = $result && $this->dof->storage('reports')->generate_reports('sync', 'mreports');
        }
        
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
     * @param dof_control $dof 
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
     * @todo разобраться с правами доступа
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
            case 'main':
                $path = $this->dof->url_im('programmsbcs','/index.php',$addvars);
//                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('title', 'ages').'</a>';
//                $rez .= "<br />";
                if ( $this->dof->storage('programmsbcs')->is_access('view') )
                {//может видеть все подписки
                    $path = $this->dof->url_im('programmsbcs','/list.php',$addvars);
                }
                //ссылка на список подписок на программу
                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('list', 'programmsbcs').'</a>';
                if ( $this->dof->storage('programmsbcs')->is_access('create') )
                {//может создавать подписку на программу - покажем ссылку
                    $rez .= "<br />";
                    $path = $this->dof->url_im('programmsbcs','/edit.php',$addvars);
                    $rez .= "<a href=\"{$path}\">".$this->dof->get_string('new', 'programmsbcs').'</a>';
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
    public function get_section($name, $id = 1)
    {
        global $USER;
        $rez = '';
        switch ($name)
        {
            case "my_programmsbcs";
                $personid = $this->dof->storage('persons')->get_by_moodleid_id($USER->id);
                return $this->get_table_programmsbcs($personid);
        }
        return $rez;
    }
     /** Возвращает текст, отображаемый в блоке на странице курса MOODLE 
      * @return string  - html-код для отображения
      */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('programmsbcs','/index.php')}'>"
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
            $result->departmentid = $this->dof->storage('eagreements')->get_field($objectid, 'departmentid');
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
     * информации о подписке на программу
     * @param stdClass $obj - запись из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show($obj,$conds, $fields = array())
    {
        if (! is_object($obj))
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        $data = $this->get_string_single_table($obj,$conds);
        // выводим таблицу на экран
        return $this->print_single_table($data, $fields);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о подписке на программу
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id,$conds,$fields = array())
    {
        if ( ! is_int_string($id) OR !is_array($fields) )
        {//входные данные неверного формата 
            return false;
        }
        if ( ! $obj = $this->dof->storage('programmsbcs')->get($id) )
        {// подписка не найдена
            return false;
        } 
        return $this->show($obj,$conds,$fields);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких подписках на программу
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function showlist($list,$conds,$options = null)
    {
        if ( ! is_array($list) )
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        foreach ( $list as $obj )
        {   
            $data[] = $this->get_string_table($obj,$conds,$options);
        }

        // выводим таблицу на экран
        return $this->print_table($data,$conds);
    }
    
    /**
     * Возвращает html-код отображения 
     * информации о нескольких подписках на программу
     * @param массив $list - массив записей 
     * периодов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function showlist_persons($list, $conds)
    {
        if ( ! is_array($list) )
        {// переданны данные неверного формата
            return false;
        }
        // для ссылок вне плагина
        $conds = (array) $conds;
        $outconds = array();
        $outconds['departmentid'] = $conds['departmentid'];

        $data = array();
        // заносим данные в таблицу
        foreach ( $list as $obj )
        {   
            if ( ! $contract = $this->dof->storage('contracts')->get($obj->contractid) )
            {//номера контракта нет - выведем пустую строчку
                $contractnum = '&nbsp;';
                $studentname = '&nbsp;';
            }else
            {//выведем номер контракта и имя ученика
                if ( $this->dof->storage('contracts')->is_access('view', $obj->contractid) )
                {// если есть право просматривать контракт ученика
                    $contractnum = '<a href='.$this->dof->url_im('sel','/contracts/view.php?id='.
                            $obj->contractid,$outconds).'>'.$contract->num.'</a>&nbsp;';
                }else
                {
                    $contractnum = $contract->num;
                }
                if ( ! $studentname = $this->dof->storage('persons')->get_fullname($contract->studentid) )
                {// имени нет - выведем пустую строчку
                    $studentname = '&nbsp;';
                }else
                {// есть имя - создадим ссылку
                    if ( $this->dof->storage('persons')->is_access('view',$contract->studentid) )
                    {// если есть право просматривать персону
                        $studentname = '<a href='.$this->dof->url_im('persons','/view.php?id='.
                            $contract->studentid,$outconds).'>'.$studentname.'</a>&nbsp;';
                    }
                }
            }
            // подразделение
            if ( ! $departmentname = $this->dof->storage('departments')->get_field($obj->departmentid, 'name') )
            {//подразделение не указано - выведем пустую строчку
                $departmentname = '&nbsp;';
            }else
            {// подразделение указано - создадим ссылку
                if ( $this->dof->storage('departments')->is_access('view',$obj->departmentid) )
                {// если есть право просматривать подразделение
                    $departmentname = '<a href='.$this->dof->url_im('departments','/view.php?departmentid='.
                            $obj->departmentid).'>'.$departmentname.' ['.
                            $this->dof->storage('departments')->get_field($obj->departmentid, 'code').']</a>&nbsp;';
                }
            }
            if ( isset($obj->agenum) )
            {//паралель указана - создадим ссылку (пока нет)
                $agenum = $obj->agenum.$this->dof->get_string('-ya', 'programmsbcs');
            }else
            {//паралель не указана - выведем пустую строчку
                $agenum = '&nbsp;';
            }
            //получаем ссылки на картинки
            $imgview = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/view.png').'" 
                alt="'.$this->dof->get_string('view', 'programmsbcs').'" title="'.$this->dof->get_string('view', 'programmsbcs').'">';
            // добавляем ссылку
            $actions = '';
            if ( $this->dof->storage('programmsbcs')->is_access('view', $obj->id) )
            {//покажем ссылку на страницу просмотра
                $actions .= '<a href='.$this->dof->url_im('programmsbcs','/view.php?programmsbcid='.
                            $obj->id,$conds).'>'.$imgview.'</a>&nbsp;';
            }
            $data[] = array($actions, $studentname, $contractnum, $departmentname, $agenum);
        }
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->wrap = array ("","","","","","nowrap");
        $table->align = array ('center','center','center','center','center');
        // шапка таблицы
        // @todo занести сюда графу "задание в moodle" когда будет реализована синхронизация
        $table->head = array($this->dof->get_string('actions','programmsbcs'),
                             $this->dof->get_string('student','programmsbcs'),
                             $this->dof->get_string('contract','programmsbcs'),
                             $this->dof->get_string('department','programmsbcs'),
                             $this->dof->get_string('agenum','programmsbcs'));
        // заносим данные в таблицу     
        $table->data = $data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    
    /**
     * Возвращает форму создания/редактирования с начальными данными
     * @param int $id - id записи, значения 
     * которой устанавливаются в поля формы по умолчанию
     * @return moodle quickform object
     */
    public function form($id = NULL, $contractid = NULL)
    {
        global $USER;
        // устанавливаем начальные данные
        if ( isset($id) AND ($id <> 0) )
        {// id передано
            $programmsbcs = $this->dof->storage('programmsbcs')->get($id);
        }else
        {// id не передано
            $programmsbcs = $this->form_new_data($contractid);
        }
        if ( isset($USER->sesskey) )
        {//сохраним идентификатор сессии
            $programmsbcs->sesskey = $USER->sesskey;
        }else
        {//идентификатор сессии не найден
            $programmsbcs->sesskey = 0;
        }
        $customdata = new stdClass();
        $customdata->programmsbcs = $programmsbcs;
        $customdata->contractid = $contractid;
        $customdata->dof    = $this->dof;
        // подключаем методы вывода формы
        $form = new dof_im_programmsbcs_edit_form(null,$customdata);
        // очистим статус, чтобы он не отображался
        // английскими буквами как в БД
        unset($programmsbcs->status);
        // заносим значения по умолчению
        $form->set_data($programmsbcs); 
        // возвращаем форму
        return $form;
    }
    
    /**
     * Возвращает исходные данные для формы создания подписки
     * @return stdclassObject
     */
    private function form_new_data($contractid = NULL)
    {
        $formdata = new stdClass();
        $formdata->id             = 0;
        $formdata->contractid     = $contractid;
        $formdata->programmid     = 0;
        $formdata->agroupid       = 0;
        $formdata->freeattendance = 0;
        $formdata->departmentid   = optional_param('departmentid', 0, PARAM_INT);
        return $formdata;
    }
    
   /** Возвращает html-код таблицы
     * @param array $date - данные в таблицу
     * @param array $addvars - доп данные для ссылок
     * @return string - html-код или пустая строка
     */
    private function print_table($data, $addvars = array() )
    {
        // рисуем таблицу
        $addvars = (array) $addvars;
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->wrap = array ("","","","","","","","","","nowrap");
        $table->align = array ('center','center','center','center','center',
                               'center','center','center','center','center',
                               'center','center','center','center','center');
        // шапка таблицы
        // @todo занести сюда графу "задание в moodle" когда будет реализована синхронизация
        $table->head = $this->get_fields_description($addvars);
        // заносим данные в таблицу     
        $table->data = $data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * 
     * @return null
     * @param object $data объект с отображаемыми значениями
     */
    private function print_single_table($data, $fields = array())
    {
        $table = new stdClass();
        if ( ! $data )
        {
            return '';
        }
        // получаем подписи с пояснениями
        $descriptions = $this->get_single_fields_description();
        $i = 0;
        foreach ( $data as $elm )
        {
            $each = each($descriptions);
            $tablerow = array('<b>'.current($each).'</b>', $elm);
            if ( empty($elm) )
            {
                continue;
            }
            if ( !empty($fields) AND is_array($fields) )
            {
                if ( array_search($each['key'], $fields) !== false )
                {
                    $table->data[] = $tablerow;
                }
            } else
            {
                $table->data[] = $tablerow;                
            }
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }
    
    /** Получить заголовок для списка таблицы
     * @param array $addvars - доп данные для ссылок
     * @return array
     */
    private function get_fields_description($addvars)
    {
        return array('', $this->dof->get_string('actions','programmsbcs'),
                     //$this->dof->get_string('contract','programmsbcs'),
                     "<a href='".$this->dof->url_im('programmsbcs','/list.php',array_merge($addvars, array('sort'=>'sortname')))."'>".$this->dof->get_string('sortname','programmsbcs')."</a>",
                     "<a href='".$this->dof->url_im('programmsbcs','/list.php',array_merge($addvars, array('sort'=>'programm')))."'>".$this->dof->get_string('programm','programmsbcs')."</a>",
                     "<a href='".$this->dof->url_im('programmsbcs','/list.php',array_merge($addvars, array('sort'=>'agenumprogramm')))."' title = '".
                     $this->dof->get_string('agenum_agroup_help','programmsbcs')."'> + </a>",
                     //$this->dof->get_string('edutype','programmsbcs','<br />'),
                     //$this->dof->get_string('eduform','programmsbcs','<br />'),
                     //$this->dof->get_string('freeattendance','programmsbcs','<br />'),
                     "<a href='".$this->dof->url_im('programmsbcs','/list.php',array_merge($addvars, array('sort'=>'agenum')))."'>".$this->dof->get_string('agenum','programmsbcs')."</a>",
                     "<a href='".$this->dof->url_im('programmsbcs','/list.php',array_merge($addvars, array('sort'=>'agroup')))."'>".$this->dof->get_string('agroup','programmsbcs')."</a>",
                     //$this->dof->get_string('department','programmsbcs'),
                     //$this->dof->get_string('agestart','programmsbcs','<br />'),
                     //$this->dof->get_string('datestart','programmsbcs','<br />'),
                     //$this->dof->get_string('dateadd','programmsbcs','<br />'),
                     //$this->dof->get_string('dateend','programmsbcs','<br />'),
                     //$this->dof->get_string('certificatenum','programmsbcs','<br />'),
                     //$this->dof->get_string('certificatedate','programmsbcs','<br />'),
                     $this->dof->get_string('salfactor','programmsbcs','<br>'),
                     "<a href='".$this->dof->url_im('programmsbcs','/list.php',array_merge($addvars, array('sort'=>'status')))."'>".$this->dof->get_string('status','programmsbcs')."</a>");
    }
    
    /** Получить список полей для отображения одного объекта 
     * @param array $addvars - доп данные для ссылок
     * @return array
     */
    private function get_single_fields_description()
    {
        return array('' => '',
                     'contract'        => $this->dof->get_string('contract', 'programmsbcs'),
                     'program'         => $this->dof->get_string('programm', 'programmsbcs'),
                     'edutype'         => $this->dof->get_string('edutype', 'programmsbcs', '<br />'),
                     'eduform'         => $this->dof->get_string('eduform', 'programmsbcs', '<br />'),
                     'freeattendance'  => $this->dof->get_string('freeattendance', 'programmsbcs', '<br />'),
                     'agroup'          => $this->dof->get_string('agroup', 'programmsbcs'),
                     'department'      => $this->dof->get_string('department', 'programmsbcs'),
                     'datestart'       => $this->dof->get_string('datestart', 'programmsbcs', '<br />'),
                     'dateadd'         => $this->dof->get_string('dateadd', 'programmsbcs', '<br />'),
                     'dateend'         => $this->dof->get_string('dateend', 'programmsbcs', '<br />'),
                     'certificatenum'  => $this->dof->get_string('certificatenum', 'programmsbcs', '<br />'),
                     'certificatedate' => $this->dof->get_string('certificatedate', 'programmsbcs', '<br />'),
                     'agenum'          => $this->dof->get_string('agenum', 'programmsbcs'),
                     'salfactor'       => $this->dof->get_string('salfactor', 'programmsbcs'),
                     'status'          => $this->dof->get_string('status', 'programmsbcs'),
                     'actions'         => $this->dof->get_string('actions', 'programmsbcs'));
    }
    
    /** Возвращает массив для вставки в таблицу
     * @param object $obj
     * @return array
     */
    private function get_string_table($obj,$conds,$options = null)
    {
        $departmentid = optional_param('departmentid',0, PARAM_INT);
        //print_object($obj);die;
        $check = '';
        if ( is_array($options) )
        {// добавляем галочки
            $check = '<input type="checkbox" name="'.$options['prefix'].'_'.
             $options['listname'].'['.$obj->id.']" value="'.$obj->id.'"/>';
        }
        //ФИО ученика
        if ( ! $sortname = $obj->sortname )
        {//Имени ученика нет - выведем пустую строчку
            $sortname = '&nbsp;';
        }else
        {
            $sortname = "<a href='".$this->dof->url_im('persons','/view.php',array('id'=>$obj->studentid,
                'departmentid'=>$departmentid))."'>".$sortname."</a>";
        }
        
        /* Это поле не используется
        if ( ! $contract = $this->dof->storage('contracts')->get($obj->contractid) )
        {//номера контракта нет - выведем пустую строчку
            $contractnum = '&nbsp;';
        }elseif ( ! $studentname = $this->dof->storage('persons')->get_fullname($contract->studentid) )
        {//ученик не указан - выведем просто номер контракта
            $contractnum = $contract->num;
        }else
        {// выведем номер контракта с именем ученика
            $contractnum = $contract->num.' ['.$studentname.']';
        }*/
        
        // программа
        if ( ! $programm = $obj->programm )
        {//программа не указана - выведем пустую строчку
            $programm = '&nbsp;';
        }else 
        {
            $programm = "<a href='".$this->dof->url_im('programms','/view.php',array('programmid'=>$obj->programmid,
                'departmentid'=>$departmentid))."'>".$programm."</a>";
        }
        
        /* Это поле не используется
        if ( ! $programmcode = $this->dof->storage('programms')->get_field($obj->programmid, 'code') )
        {//код программы не указан - выведем пустую строчку
            $programmcode = '&nbsp;';
        }
        if ( ($programmname <> '&nbsp;') OR ($programmcode <> '&nbsp;') )
        {// если код группы или имя были найдены - выведем их вместе 
            $programm = $programmname.' ['.$programmcode.']';
        }else
        {// не найдены - пустую строчку
            $programm = '&nbsp;';
        }
        
        // тип обучения
        $edutype = $this->dof->get_string('edutype:'.$obj->edutype, 'programmsbcs');
        // форма обучения
        $eduform = $this->dof->storage('programmsbcs')->get_eduform_name($obj->eduform);
        if ( isset($obj->freeattendance) AND ($obj->freeattendance == '1') )
        {// свободное посещение указано - выведем да
            $freeattendance = $this->dof->get_string('yes', 'programmsbcs');
        }else
        {// нет - значит нет
            $freeattendance = $this->dof->get_string('no', 'programmsbcs');
        }*/
        
        // группа
        if ( ! $agroupname = $this->dof->storage('agroups')->get_field($obj->agroupid, 'name') )
        {//группа не указана - выведем пустую строчку
            $agroupname = '&nbsp;';
        }
        if ( ! $agroupcode = $this->dof->storage('agroups')->get_field($obj->agroupid, 'code') )
        {//код группы не указан - выведем пустую строчку
            $agroupcode = '&nbsp;';
        }
        if ( ($agroupname <> '&nbsp;') OR ($agroupcode <> '&nbsp;') )
        {// если код группы или имя были найдены - выведем их вместе 
            $agroup = "<a href='".$this->dof->url_im('agroups','/view.php',array('agroupid'=>$obj->agroupid,
                'departmentid'=>$departmentid))."'>".$agroupname.' ['.$agroupcode.']'."</a>";
        }else
        {// не найдены - пустую строчку
            $agroup = '&nbsp;';
        }
        
        /* Это поле не используется
        $departmentname = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                          $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
        if ( ! $agename = $this->dof->storage('ages')->get_field($obj->agestartid, 'name') )
        {//начальный период не указан - выведем пустую строчку
            $agename = '&nbsp;';
        }
        // дата начала действия
        if ( isset($obj->datestart) )
        {// дата есть - преобразуем ее
            $datestart = date('d.m.Y',$obj->datestart);
        }else
        {// нет - выведем что не указано
            $datestart = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        // дата создания
        if ( isset($obj->dateadd) )
        {// дата есть - преобразуем ее
            $dateadd = date('d.m.Y',$obj->dateadd);
        }else
        {// нет - выведем что не указано
            $dateadd = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        // дата завершения
        if ( isset($obj->dateend) )
        {// дата есть - преобразуем ее
            $dateend = date('d.m.Y',$obj->dateend);
        }else
        {// нет - выведем что не указано
            $dateend = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        // дата выдачи сертификата
        if ( isset($obj->certificatedate) )
        {// дата выдачи сертификата
            $certificatedate = date('d.m.Y',$obj->certificatedate);
        }else
        {// нет - выведем что не указано
            $certificatedate = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        if ( isset($obj->certificatenum) )
        {// есть сертификат - выведем его номер
            $certificatenum = $obj->certificatenum;
        }else
        {// нет - значит еще не выдан
            $certificatenum = $this->dof->get_string('no_give_out', 'programmsbcs');
        }*/
        
        //получим название статуса
        if ( ! $statusname = $this->dof->workflow('programmsbcs')->get_name($obj->status) )
        {//статуса нет - выведем пустую строчку
            $statusname = '&nbsp;';
        }
        //получаем ссылки на картинки
        $imgedit = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/edit.png').'"
            alt="'.$this->dof->get_string('edit', 'programmsbcs').'" title="'.$this->dof->get_string('edit', 'programmsbcs').'">';
        $imgview = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/view.png').'" 
            alt="'.$this->dof->get_string('view', 'programmsbcs').'" title="'.$this->dof->get_string('view', 'programmsbcs').'">';
        $imgcpas = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/viewcpass.png').'" 
            alt="'.$this->dof->get_string('view_cpassed', 'programmsbcs').'" title="'.$this->dof->get_string('view_cpassed', 'programmsbcs').'">';        
        // добавляем ссылку
        $actions = '';
        if ( $this->dof->storage('programmsbcs')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $actions .= '<a href="'.$this->dof->url_im('programmsbcs','/edit.php?programmsbcid='.
            $obj->id,$conds).'">'.$imgedit.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmsbcs')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $actions .= '<a href="'.$this->dof->url_im('programmsbcs','/view.php?programmsbcid='.
            $obj->id,$conds).'">'.$imgview.'</a>&nbsp;';
        }
        if ( $this->dof->storage('cpassed')->is_access('view') )
        {
            $actions .= '<a href="'.$this->dof->url_im('cpassed','/list.php?programmsbcid='.
            $obj->id,$conds).'">'.$imgcpas.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmsbcs')->is_access('view') )
        {//покажем ссылку на страницу просмотра истории обучения группы
            $img = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/history.png').'" 
            alt="'.$this->dof->get_string('history_programmsbc', 'programmsbcs').'" title="'.
                   $this->dof->get_string('history_programmsbc', 'programmsbcs').'">';
            $actions .= ' <a href='.$this->dof->url_im('programmsbcs','/history.php?sbcid='.
            $obj->id,$conds).'>'.$img.'</a>&nbsp;';
        }
        if ( $this->dof->storage('learningplan')->is_access('edit') )   
        {
            $img = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/editplan.png').'" 
            alt="'.$this->dof->get_string('edit_learningplan', 'programmsbcs').'" title="'.
                   $this->dof->get_string('edit_learningplan', 'programmsbcs').'">';
            $actions .= ' <a href='.$this->dof->url_im('learningplan','/index.php?type=programmsbc&programmsbcid='.
            $obj->id,$conds).'>'.$img.'</a>&nbsp;';
        }
        
        
        // выводим поля в таблицу в нужном порядке и формате
        return array($check,
                     $actions,
                     $sortname,
                     //$contractnum, 
                     $programm,'',// пустая строчка для пустого столбца сортировки
                     $obj->agenum,
                     //$edutype,
                     //$eduform,
                     //$freeattendance, 
                     $agroup,
                     //$departmentname,
                     //$agename,
                     //$datestart,
                     //$dateadd, 
                     //$dateend,
                     //$certificatenum,
                     //$certificatedate,
                     $obj->salfactor,
                     $statusname);
    }
    
    /** Возвращает массив для вставки в таблицу с полной информацией об учащемся
     * @param object $obj
     * @return array
     */
    private function get_string_single_table($obj,$conds,$options = null)
    {
        global $addvars;
        $this->dof->modlib('widgets')->html_writer();
        //print_object($obj);die;
        $check = '';
        if ( is_array($options) AND $this->is_access('datamanage') )
        {// добавляем галочки
            $check = '<input type="checkbox" name="'.$options['prefix'].'_'.
             $options['listname'].'['.$obj->id.']" value="'.$obj->id.'"/>';
        }
        
        if ( ! $contract = $this->dof->storage('contracts')->get($obj->contractid) )
        {//номера контракта нет - выведем пустую строчку
            $contractnum = '&nbsp;';
        } else
        {
            $contractnum = dof_html_writer::tag('a', $contract->num,
                array('href'=>$this->dof->url_im('sel','/contracts/view.php',
                        $addvars + array('id' => $contract->id))));
        }
        if ( $studentname = $this->dof->storage('persons')->get_fullname($contract->studentid) )
        {// ученик указан - выведем номер контракта с именем ученика
            $studentlink = $this->dof->im('obj')->get_object_url_current('persons', $contract->studentid, 'view', $addvars, $studentname);
            if ( empty($studentlink) )
            {
                $contractnum .= " [$studentname]";
            } else
            {
                $contractnum .= " [$studentlink]";
            }
        }
        
        // программа
        if ( ! $programmname = $this->dof->storage('programms')->get_field($obj->programmid, 'name') )
        {//программа не указана - выведем пустую строчку
            $programmname = '&nbsp;';
        }
        
        if ( ! $programmcode = $this->dof->storage('programms')->get_field($obj->programmid, 'code') )
        {//код программы не указан - выведем пустую строчку
            $programmcode = '&nbsp;';
        }
        
        
        if ( ($programmname <> '&nbsp;') OR ($programmcode <> '&nbsp;') )
        {// если код программы или имя были найдены - выведем их вместе 
            $programmnamecode = $programmname.' ['.$programmcode.']';
            $programm = $this->dof->im('obj')->get_object_url_current('programms',
                    $obj->programmid, 'view', $addvars, $programmnamecode);
        }else
        {// не найдены - пустую строчку
            $programm = '&nbsp;';
        }
        
        // тип обучения
        $edutype = $this->dof->get_string('edutype:'.$obj->edutype, 'programmsbcs');
        // форма обучения
        $eduform = $this->dof->storage('programmsbcs')->get_eduform_name($obj->eduform);
        if ( isset($obj->freeattendance) AND ($obj->freeattendance == '1') )
        {// свободное посещение указано - выведем да
            $freeattendance = $this->dof->get_string('yes', 'programmsbcs');
        }else
        {// нет - значит нет
            $freeattendance = $this->dof->get_string('no', 'programmsbcs');
        }
        
        // группа
        if ( ! $agroupname = $this->dof->storage('agroups')->get_field($obj->agroupid, 'name') )
        {//группа не указана - выведем пустую строчку
            $agroupname = '&nbsp;';
        }
        if ( ! $agroupcode = $this->dof->storage('agroups')->get_field($obj->agroupid, 'code') )
        {//код группы не указан - выведем пустую строчку
            $agroupcode = '&nbsp;';
        }
        if ( ($agroupname <> '&nbsp;') OR ($agroupcode <> '&nbsp;') )
        {// если код группы или имя были найдены - выведем их вместе 
            $agroup = $agroupname.' ['.$agroupcode.']';
            $agroup = '<a href='.$this->dof->url_im('agroups','/view.php?agroupid='.
                                        $obj->agroupid,$conds).'>'.$agroup.'</a>';

        }else
        {// не найдены - пустую строчку
            $agroup = '&nbsp;';
        }
        
        $departmentname = $this->dof->storage('departments')->get_field($obj->departmentid,'name').' <br>['.
                          $this->dof->storage('departments')->get_field($obj->departmentid,'code').']';
        // дата начала действия
        if ( isset($obj->datestart) )
        {// дата есть - преобразуем ее
            $datestart = dof_userdate($obj->datestart,'%d.%m.%Y');
        }else
        {// нет - выведем что не указано
            $datestart = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        // дата создания
        if ( isset($obj->dateadd) )
        {// дата есть - преобразуем ее
            $dateadd = dof_userdate($obj->dateadd,'%d.%m.%Y');
        }else
        {// нет - выведем что не указано
            $dateadd = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        // дата завершения
        if ( isset($obj->dateend) )
        {// дата есть - преобразуем ее
            $dateend = dof_userdate($obj->dateend,'%d.%m.%Y');
        }else
        {// нет - выведем что не указано
            $dateend = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        // дата выдачи сертификата
        if ( isset($obj->certificatedate) )
        {// дата выдачи сертификата
            $certificatedate = dof_userdate($obj->certificatedate,'%d.%m.%Y');
        }else
        {// нет - выведем что не указано
            $certificatedate = $this->dof->get_string('no_specify', 'programmsbcs');
        }
        if ( isset($obj->certificatenum) )
        {// есть сертификат - выведем его номер
            $certificatenum = $obj->certificatenum;
        }else
        {// нет - значит еще не выдан
            $certificatenum = $this->dof->get_string('no_give_out', 'programmsbcs');
        }
        
        //получим название статуса
        if ( ! $statusname = $this->dof->workflow('programmsbcs')->get_name($obj->status) )
        {//статуса нет - выведем пустую строчку
            $statusname = '&nbsp;';
        }
        //получаем ссылки на картинки
        $imgedit = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/edit.png').'"
            alt="'.$this->dof->get_string('edit', 'programmsbcs').'" title="'.$this->dof->get_string('edit', 'programmsbcs').'">';
        $imgview = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/view.png').'" 
            alt="'.$this->dof->get_string('view', 'programmsbcs').'" title="'.$this->dof->get_string('view', 'programmsbcs').'">';
        // добавляем ссылку
        $actions = '';
        if ( $this->dof->storage('programmsbcs')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $actions .= '<a href="'.$this->dof->url_im('programmsbcs','/edit.php?programmsbcid='.
            $obj->id,$conds).'">'.$imgedit.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmsbcs')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $actions .= '<a href="'.$this->dof->url_im('programmsbcs','/view.php?programmsbcid='.
            $obj->id,$conds).'">'.$imgview.'</a>&nbsp;';
        }
        if ( $this->dof->storage('programmsbcs')->is_access('view') )
        {//покажем ссылку на страницу просмотра истории обучения группы
            $img = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/history.png').'" 
            alt="'.$this->dof->get_string('history_programmsbc', 'programmsbcs').'" title="'.
                   $this->dof->get_string('history_programmsbc', 'programmsbcs').'">';
            $actions .= ' <a href='.$this->dof->url_im('programmsbcs','/history.php?sbcid='.
            $obj->id,$conds).'>'.$img.'</a>&nbsp;';
        }
        if ( $this->dof->storage('learningplan')->is_access('edit') )   
        {
            $img = '<img src="'.$this->dof->url_im('programmsbcs', '/icons/editplan.png').'" 
            alt="'.$this->dof->get_string('edit_learningplan', 'programmsbcs').'" title="'.
                   $this->dof->get_string('edit_learningplan', 'programmsbcs').'">';
            $actions .= ' <a href='.$this->dof->url_im('learningplan','/index.php?type=programmsbc&programmsbcid='.
            $obj->id,$conds).'>'.$img.'</a>&nbsp;';
        }
        
        // выводим поля в таблицу в нужном порядке и формате
        return array($check,
                     $contractnum, 
                     $programm,
                     $edutype,
                     $eduform,
                     $freeattendance, 
                     $agroup,
                     $departmentname,
                     $datestart,
                     $dateadd, 
                     $dateend,
                     $certificatenum,
                     $certificatedate,
                     $obj->agenum,
                     $obj->salfactor,
                     $statusname,
                     $actions);
    }
    
    /**
     * Возвращает объект приказа
     *
     * @param string $code - код приказа
     * @param integer  $id - id подписки на программу
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        require_once($this->dof->plugin_path('im','programmsbcs','/orders/change_status/init.php'));
        switch ($code)
        {
            case 'change_status':
                $order = new dof_im_programmsbcs_order_change_status($this->dof);
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
    
    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_reports_basereport
     */
    public function report($code, $id = NULL)
    {
        return $this->dof->storage('reports')->report('sync', 'mreports', $code, $id);
    }
    
    /** Возвращает html-код таблицы для истории группы
     * @param int agroupid - id группы 
     * @return string - html-код или пустая строка
     */
    public function print_table_history($sbcid)
    {
        // рисуем таблицу
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array ("center","center");
        // шапка таблицы
        $table->head =  array($this->dof->get_string('age', 'programmsbcs'),
                              $this->dof->get_string('agenum', 'programmsbcs'));
        // заносим данные в таблицу   
        if ( ! $history = $this->dof->storage('learninghistory')->get_records(array('programmsbcid'=>$sbcid)) )
        {
            return '<div align=\'center\'>'.$this->dof->get_string('no_history_programmsbc', 'programmsbcs').'</div>';
        }else
        {
            $data = array();
            foreach ( $history as $lhistory )
            {
                $agename = $this->dof->storage('ages')->get_field($lhistory->ageid, 'name');
                $data[] = array($agename, $lhistory->agenum );
            }
        }
        $table->data = $data;
        return $this->dof->modlib('widgets')->print_table($table,true);
    }
    
    /* Получаем таблицу с подписками персоны
     * @param int $intvar - id персоны
     * @param int $depid - id департамента
     * @return string $result - html-код таблицы или пустую строку 
     */
    public function get_table_programmsbcs($intvar, $depid = 0) 
    {
        $result = '';
        $conditions = array('studentid' => $intvar,
                'status'    => array('work', 'frozen'));
        
        if ( ! $contracts = $this->dof->storage('contracts')->get_records($conditions) )
        {// нет договоров - значит нет и подписок
            return '';
        }
        
        unset($conditions);
        
        $list = array();
        
        foreach ( $contracts as $contract )
        {// для каждого договора извлекаем подписку
            $conditions = array();
            $conditions['contractid'] = $contract->id;
            $conditions['status']     = array('application', 'plan', 'active', 'condactive', 'suspend');
            
            if ( ! $programmsbcs = $this->dof->storage('programmsbcs')->get_records($conditions) )
            {// для контракта нет подписок
                continue;
            }
             
            foreach ( $programmsbcs as $psbc )
            {
                $psbc->programm  = $this->dof->storage('programms')->get_field($psbc->programmid, 'name');
                $personid        = $this->dof->storage('contracts')->get_field($psbc->contractid, 'studentid');
                $psbc->sortname  = $this->dof->im('persons')->get_fullname($personid);
                $psbc->studentid = $personid;
                $list[$psbc->id] = $psbc;
            }
        }
        
        // @todo проставить нормальный departmentid, передав его в $mixedvar
        $result .= $this->showlist($list, array('departmentid' => $depid));
        
        return $result;
    }
}
