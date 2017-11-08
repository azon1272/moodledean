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
// подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Ресурсы
 * 
 */
class dof_im_inventory implements dof_plugin_im, dof_storage_config_interface
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
        return 2015122900;
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
        return 'inventory';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('invitems'      => 2011112400,
                                      'invsets'       => 2011112400,
                                      'invcategories' => 2011112400,
                                      'acl'           => 2011040504) );
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
       return 1800;
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "inventory/{$do} (block/dof/im/inventory: {$do})";
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
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' AND
             isset($mixedvar['storage']) AND isset($mixedvar['action']) )
        {
            $action = $mixedvar['action'];
            $params = $mixedvar['urlparams'];
            switch ( $mixedvar['storage'] )
            {
                case 'invcategories': return $this->invcategories_action_url($intvar, $action, $params);
                case 'invitems':      return $this->invitems_action_url($intvar, $action, $params);
                case 'invsets':       return $this->invsets_action_url($intvar, $action, $params);
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
        $result = true;
        if ( $loan == 3 )
        {// генерацию отчетов запускаем только в режиме
            $result = $result && $this->dof->storage('reports')->generate_reports($this->type(), $this->code());
        }
        return $result;
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
        $depid = optional_param('depid', 0, PARAM_INT);
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

    /***************************************************/
    /************ МЕТОДЫ ПРОВЕРКИ    *******************/ 
    /************ ПРАВ ДОСТУПА.      *******************/
    /************ МОЖНО ИСПОЛЬЗОВАТЬ *******************/
    /************   !!! ТОЛЬКО !!!   *******************/
    /************ В МЕТОДЕ           *******************/
    /************ $this->is_access() *******************/ 
    /***************************************************/ 


    /** Получить список параметров для фунции has_hight()
     * @todo завести дополнительные права в плагине storage/persons и storage/contracts 
     * и при редактировании контракта или персоны обращаться к ним
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid=0, $personid)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $result->objectid     = $objectid;
        
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
        $a['view'] = array('roles'=>array('manager'));
        return $a;
    }    
    
    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        // плагин включен и используется
        // При помощи этой настройки возможно отключение плагина в различных подразделениях
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
            
        return $config;
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
     * Возвращает вкладки на категории/оборудование/комплекты оборудования
     * @param string $id -идентификатор,определяет какая вкладка активна в данный момент
     * @param arrrya $addvars - массив параметров GET(подразделение) 
     * @return смешанную строку 
     */
    public function print_tab($addvars, $id)
    {
        // соберем данные для вкладок
        $tabs = array();
        // операции
        $link = $this->dof->url_im($this->code(),'/index.php',$addvars);
        $text = $this->dof->get_string('operation', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('operation', $link, $text, NULL, true); 
        // оборудование
        $link = $this->dof->url_im($this->code(),'/items/index.php',$addvars);
        $text = $this->dof->get_string('items', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('items', $link, $text, NULL, true);
        // комплекты оборудования
        $link = $this->dof->url_im($this->code(),'/sets/index.php',$addvars);
        $text = $this->dof->get_string('sets', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('sets', $link, $text, NULL, true);        
        // готовим для вывода
        return $this->dof->modlib('widgets')->print_tabs($tabs, $id, NULL, NULL, true);
    }
    
    /** Отрисовать второй уровень вкладок на странице со списокм оборудования
     * @param string $activetab -идентификатор,определяет какая вкладка активна в данный момент
     * @param array $addvars - массив параметров GET(подразделение)
     * @param array $count_tab - массив с колич элементов в той или иной вкладке
     * @return string 
     */
    public function print_item_tabs($addvars, $activetab, $count_tab)
    {
        $tabs = array();
        
        if ( ! $activetab )
        {// по умолчанию показываем все оборудование
            $activetab = 'all';
        }
        // Операции
        $link = $this->dof->url_im('inventory','/items/index.php',$addvars);
        $text = $this->dof->get_string('operation', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('operation_items', $link, $text, NULL, true);
        if ( ! empty($addvars['invcategoryid']) AND 
               ( $addvars['departmentid'] == $this->dof->storage('invcategories')->get_field($addvars['invcategoryid'], 'departmentid')
                OR empty($addvars['departmentid']) ) )
        {
            // все
            unset($addvars['displaytype']);
            $link = $this->dof->url_im($this->code(),'/items/list.php',$addvars);
            $text = $this->dof->modlib('ig')->igs('all')."(".$count_tab['all'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('all', $link, $text, NULL, true);
            // свободное
            $addvars['displaytype'] = 'free';
            $link = $this->dof->url_im($this->code(),'/items/list.php',$addvars);
            $text = $this->dof->get_string('show_free_items', $this->code())."(".$count_tab['free'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('free', $link, $text, NULL, true);
            // В комплекте
            $addvars['displaytype'] = 'in_set';
            $link = $this->dof->url_im($this->code(),'/items/list.php',$addvars);
            $text = $this->dof->get_string('show_in_set_items', $this->code())."(".$count_tab['in_set'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('in_set', $link, $text, NULL, true);
            // Недоступно
            $addvars['displaytype'] = 'n_a';
            $link = $this->dof->url_im($this->code(),'/items/list.php',$addvars);
            $text = $this->dof->get_string('show_n_a_items', $this->code())."(".$count_tab['n_a'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('n_a', $link, $text, NULL, true);
        }
        
        return '<div>'.
            $this->dof->modlib('widgets')->print_tabs($tabs, $activetab, NULL, NULL, true).'</div>';
    }

    /** Отрисовать второй уровень вкладок на странице со списокм оборудования
     * @param string $activetab -идентификатор,определяет какая вкладка активна в данный момент
     * @param array $addvars - массив параметров GET(подразделение)
     * @param array $count_tab - массив с колич элементов в той или иной вкладке
     * 
     * @return string 
     */
    public function print_set_tabs($addvars, $activetab, $count_tab)
    {
        $tabs = array();
        
        if ( ! $activetab )
        {// по умолчанию показываем все оборудование
            $activetab = 'all';
        }
        // операции
        $link = $this->dof->url_im('inventory','/sets/index.php', $addvars);
        $text = $this->dof->get_string('operation',$this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('operation_sets', $link, $text, NULL, true);
        if ( ! empty($addvars['invcategoryid']) AND 
               ( $addvars['departmentid'] == $this->dof->storage('invcategories')->get_field($addvars['invcategoryid'], 'departmentid')
                OR empty($addvars['departmentid']) ) )
        {
            // все
            unset($addvars['displaytype']);
            $link = $this->dof->url_im($this->code(),'/sets/list.php',$addvars);
            $text = $this->dof->modlib('ig')->igs('all')."(".$count_tab['all'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('all', $link, $text, NULL, true);
            // Выданные
            $addvars['displaytype'] = 'granted';
            $link = $this->dof->url_im($this->code(),'/sets/list.php',$addvars);
            $text = $this->dof->get_string('show_granted_sets', $this->code())."(".$count_tab['granted'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('granted', $link, $text, NULL, true);
            // Доступные
            $addvars['displaytype'] = 'available';
            $link = $this->dof->url_im($this->code(),'/sets/list.php',$addvars);
            $text = $this->dof->get_string('show_available_sets', $this->code())."(".$count_tab['available'].")";
            $tabs[] = $this->dof->modlib('widgets')->create_tab('available', $link, $text, NULL, true);
        }
        
        return '<div>'.
            $this->dof->modlib('widgets')->print_tabs($tabs, $activetab, NULL, NULL, true).'</div>';
    }
    
    /** Отобразить таблицу со списком комплектов оборудования
     * @param array $list - массив записей из таблицы invsets
     * @param array $addvars[optional] - массив с параметрами для генерации ссылок сортировки
     * 
     * @return null
     */
    public function print_invsets_table($sets, $addvars=null)
    {
        if ( ! is_array($sets) OR empty($sets) )
        {
            echo $this->dof->modlib('widgets')->notice_message($this->dof->modlib('ig')->igs('empty'));
            return;
        }
        
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->align = array('center','center','center','center','center','center');
        $table->head = $this->get_invsets_table_head($addvars);
        $table->data = array();
        // счётчик
        if ( empty($addvars['limitfrom']) )
        {
            $i = 1;
        }else
        {
            $i = $addvars['limitfrom'];
            // удалим, чтою он не передавался дальше
            unset($addvars['limitfrom']);
        }        
        
        foreach ( $sets as $set )
        {// формируем строки таблицы
            $row = array();
            $row[] = $i;
            $row[] = $set->code;
            if ( ! isset($addvars['departmentid']) OR ! $addvars['departmentid'] )
            {// Подразделение (только при просмотре с departmentid=0)
                $row[] = $this->dof->im('departments')->get_html_link($set->departmentid, true);
            }
            
            $row[] = $this->get_person_link($set->personid, $addvars);
            // категория
            $row[] = $this->dof->storage('invcategories')->get_field($set->invcategoryid, 'name');
            // ссылка на просмотр одного комплекта
            $viewlink = $this->dof->url_im($this->code(),'/sets/view.php', $addvars + array('id' => $set->id));
            $viewtitle = $this->dof->get_string('view-give', $this->code());
            $row[] = $this->dof->modlib('ig')->icon('view', $viewlink);
            
            $table->data[] = $row;
            $i++;
        }
        
        $this->dof->modlib('widgets')->print_table($table);
    }

    /** Получить заголовки таблицы для списка комплектов оборудования
     * @param array $addvars - дополнительные параметры для формирования ссылок сортировки
     * 
     * @return array массив строк для заголовка таблицы
     */
    protected function get_invsets_table_head($addvars=null)
    {
        $labels = array();
        // id (из базы)
        $labels['number'] = '№';
        // код
        $labels['code'] = $this->dof->modlib('ig')->igs('code');
        if ( ! isset($addvars['departmentid']) OR ! $addvars['departmentid'] )
        {// Подразделение (только при просмотре с departmentid=0)
            $labels['departmentid'] = $this->dof->get_string('department', $this->code());
        }
        
        // Кому выдан (отображается персона (ссылкой). Если просто сделать - то можно отобразить дату выдачи)
        $labels['personid'] = $this->dof->get_string('set_owner', $this->code());
        // категория оборудования        
        $labels['invcategoryid'] = $this->dof->get_string('catname', 'inventory', '<br>');
        // Действия 
        $labels['actions'] = $this->dof->modlib('ig')->igs('actions');
        
        return $labels;
    }
    
    /** Отобразить таблицу со списком категорий, в виде таблицы, в которой записи можно сортировать
     * @param array $addvars - массив get-параметров для формирования ссылки 
     * @param string $sort - поле, по которому будут сортироваться записи в таблице
     * 
     * @return null
     */
    public function print_category_table($addvars, $sort='name')
    {
        $departmentid = 0;
        if ( isset($addvars['departmentid']) )
        {
            $departmentid = $addvars['departmentid'];
        }
        
        if ( $departmentid )
        {
            $departments = array(0, $departmentid);
        }else
        {
            $departments = $departmentid;
        }
        
        if ( ! $departments )
        {// нужен список всех категорий, вне зависимости от подразделения
            $categories = $this->dof->storage('invcategories')->
                get_records(array('status'=>'active'), $sort.' ASC');
        }else
        {// нужен список категорий по подразделениям + вне подразделений
            $categories = $this->dof->storage('invcategories')->
                get_records(array('departmentid'=>$departments,'status'=>'active'), $sort.' ASC');
        }
        // получаем список категорий одного подразделения
        if ( ! $categories )
        {// не показываем категории - их нет
            echo $this->no_categories_message();
            return;
        }
        // оставляем только те подразделения, которые пользователь имеет право видеть
        $permissions = 
            array(
                array(
                    'plugintype' => 'storage', 
                    'plugincode' => 'invcategories', 
                    'code'       => 'view'
                    )
                 );
        if ( ! $categories = $this->dof->storage('acl')->get_acl_filtered_list($categories, $permissions) )
        {
            echo $this->no_categories_message();
            return;
        }
        
        $categories = $categories;
        
        // создаем таблицу и настраиваем заголовки
        $table = new stdClass();
        $table->tablealign = "center";
        $table->align = array("center","center","center","center","center");
        
        $table->head = array(
            $this->dof->modlib('ig')->igs('actions'),
            '<a id="im_inventory_cats_sortbyname" href="'.$this->dof->url_im('inventory', '/category/list.php', 
            $addvars + array('sort' => 'name')).'">'.
            $this->dof->get_string('catname', $this->code(), '<br>').'</a>',
            '<a id="im_inventory_cats_sortbycode" href="'.$this->dof->url_im('inventory', '/category/list.php', 
            $addvars + array('sort' => 'code')).'">'.
            $this->dof->modlib('ig')->igs('code').'</a>',
            $this->dof->get_string('parentcategory', $this->code(), '<br>'),
            $this->dof->get_string('department', $this->code())
            );
        
        foreach ( $categories as $id=>$category )
        {// из каждой категории формируем строку таблицы
            if ( ! $parent = $this->dof->storage('invcategories')->
                    get_field($category->parentid, 'name') )
            {// получаем название родительской категории
                $parent = '';
            }
            if ( ! $department = $this->dof->storage('departments')->
                    get_field($category->departmentid, 'name') )
            {// получаем название подразделения, в которой находится категория
                $department = '';
            }
            // добавляем иконки просмотра и редактирования
            $actions = '';
            
            if ( $this->dof->storage('invcategories')->is_access('edit', $id) )
            {// показываем иконку редактирования
                $editurl = $this->dof->url_im('inventory', '/category/edit.php', 
                $addvars + array('id' => $id));
                $editoptions = array('id' => 'im_inventory_editcat_'.$id);
                $actions .= $this->dof->modlib('ig')->icon('edit', $editurl, $editoptions);
            }
            
            if ( $this->dof->storage('invcategories')->is_access('delete', $id) )
            {// показываем иконку редактирования
                $editurl = $this->dof->url_im('inventory', '/category/delete.php', 
                $addvars + array('id' => $id));
                $editoptions = array('id' => 'im_inventory_deletecat_'.$id);
                $actions .= '&nbsp;'.$this->dof->modlib('ig')->icon('delete', $editurl, $editoptions);
            }
            
            $string = array($actions, $category->name, $category->code, $parent, $department);
            $table->data[] = $string;
        }
        
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    /** Вывести таблицу со списком оборудования
     * @param array $items - массив объектов из таблицы invitems
     * @param array $addvars[optional] - массив с параметрами для генерации ссылок сортировки
     * 
     * @return null
     */
    public function print_invitems_table($items, $addvars=null)
    {
        if ( ! is_array($items) OR ! $items )
        {
            echo $this->dof->modlib('widgets')->notice_message($this->dof->modlib('ig')->igs('empty'));
            return;
        }
        
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->align = array('center','center','center','center','center','center','center','center','center');
        $table->head = $this->get_invitems_table_head($addvars);
        $table->data = array();
        // счётчик
        if ( empty($addvars['limitfrom']) )
        {
            $i = 1;
        }else
        {
            $i = $addvars['limitfrom'];
            // удалим, чтою он не передавался дальше
            unset($addvars['limitfrom']);
        }
        
        foreach ( $items as $item )
        {// из каждой записи об оборудовании создадим строку таблицы
            $row = array();
            $item = $item;
            // номер
            $row[] = $i;
            // название
            // ссылка тут ни к чему нам
            $row[] = $this->dof->storage('invitems')->get_field($item->id, 'name');
                    //$this->get_invitem_link($item->id, false, $addvars);
            // инвентарный номер
            $row[] = $item->code;
            // серийный номер
            $row[] = $item->serialnum;
            // дата поступления/списания
            if ( $item->status == 'scrapped' )
            {// если оборудование списано - то покажем дату списания
                $row[] = $this->get_time($item->dateentry).' <br>('.
                    $this->dof->get_string('scrapped', 'inventory').': '.
                    $this->get_time($item->datewriteoff).')';
            }else
            {// в остальных случаях покажем только дату поступления
                $row[] = $this->get_time($item->dateentry);
            }
            // категория
            $row[] = $this->dof->storage('invcategories')->get_field($item->invcategoryid, 'name');
            // комплект (со ссылкой на просмотр)
            $row[] = $this->get_invset_link($item->invsetid, $addvars);
            // статус
            $row[] = $this->dof->workflow('invitems')->get_name($item->status);
            // ссылка на просмотр
            $viewlink = $this->dof->url_im($this->code(),'/items/view.php', $addvars + array('id' => $item->id));
            $row[] = $this->dof->modlib('ig')->icon('view', $viewlink);
            
            $table->data[] = $row;
            $i++;
        }
        
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    /** Получить заголовки для таблицы с отображением списка оборудования
     * 
     */
    public function get_invitems_table_head($addvars=null)
    {
        // параметры для сортировки
        unset($addvars['sort']);
        $addvars['limitfrom'] = 1;
        $url = $this->dof->url_im('inventory','/items/list.php',$addvars);
        $labels = array();
        // наоммер
        $labels['number']    = '№';
        // название
        $labels['name']      = $this->dof->modlib('ig')->igs('name');
        // инвентарный номер
        $labels['code'] = '<a href="'.$url.'&sort=code" title="'.$this->dof->get_string('sort_by_code', 'inventory').'">'.
                                                $this->dof->get_string('invnum', 'inventory', '<br>').'</a>';
        // серийный номер
        $labels['serialnum'] = $this->dof->get_string('serialnum', 'inventory', '<br>');
        // @todo тип оборудования пока не используется
        // $labels['type']
        // @todo счетное оборудование пока не используется
        // $labels['count']
        // @todo срок полезного использования пока не используется
        // $labels['termofuse']
        // дата поступления (дата списание рисуется здесь же, если оборудование списано)
         $labels['dateentry'] = '<a href="'.$url.'&sort=dateentry" title="'.$this->dof->get_string('sort_by_data', 'inventory').'">'.
                                         $this->dof->get_string('dateentry_header', 'inventory', '<br>').'</a>';
        
        
        // категория оборудования        
        $labels['invcategoryid'] = $this->dof->get_string('catname', 'inventory');
        // @todo Отображается только если просматриваются все подразделения
        // $labels['departmentid']
        // комплект
        $labels['invsetid'] = $this->dof->get_string('set', 'inventory');
        // статус
        $labels['status']   = $this->dof->modlib('ig')->igs('status');
        // действия
        $labels['actions']   = $this->dof->modlib('ig')->igs('actions');
        
        return $labels;
    }
    
    /** Получить html-код сообщения о том, что категорий не нашлось
     * 
     * @return string
     */
    protected function no_categories_message()
    {
        return $this->dof->modlib('widgets')->
            notice_message($this->dof->get_string('no_categories', $this->code()));
    }
    
    /** Отобразить информацию об одном приказе по оборудованию (приход или списание)
     * Отображает всю информацию по приказу и список оборудования
     * 
     * @param int $id - id приказа в таблице orders 
     * @param array $addvars - дополнит параметры, для перехода по ссылкам(чаще id подразделения) 
     * 
     * @return table
     */
    public function display_inventory_order($id, $addvars=array())
    {
        // подключаем приказы
        require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));
       // require_once($this->dof->plugin_path('storage','invitems','/order/delete_order.php'));
        
        // определим тим приказа 
        $type = $this->dof->storage('orders')->get_field($id, 'code'); 
        if ( $type == 'new_items' )
        {
            $order = new dof_storage_invitems_order_new_items($this->dof,$id);
        }elseif ( $type == 'delete_items' )
        {
            $order = new dof_storage_invitems_order_delete_items($this->dof,$id);
        }else
        {
            return $this->dof->modlib('widgets')->
                error_message($this->dof->get_string('order_notfound', $this->code()));
        }
        
        if ( ! $data = $order->load($id) )
        {// нет приказа, или не удалось загрузить данные
            return $this->dof->modlib('widgets')->
                error_message($this->dof->get_string('order_notfound', $this->code()));
        }
        
        if ( ! $order->is_executed() )
        {// Информация о неисполненных приказах не отображается
            return $this->dof->modlib('widgets')->
                error_message($this->dof->get_string('order_is_not_executed', $this->code()));
        } 
               
        switch ( $type )
        {// определим тип приказа
            // приход
            case 'new_items':   return $this->display_new_resource_order($type, $data, $addvars);
            // списание
            case 'delete_items': return $this->display_scrap_resource_order($type, $data, $addvars);
            // приказ не является приказом о приходе либо списании оборудования
            // а значит мы не можем его отобразить
            default: return $this->dof->modlib('widgets')->
                error_message($this->dof->get_string('wrong_order_type', $this->code()));
        }
    }
    
    /** Отобразить информацию о приказе по приходу оборудования
     * 
     * @param string $type - тип приказ из таблицы
     * @param object $data  - данные из приказа
     * @param array $addvars - дополнит данные для перехода по ссылкам(чаще id подразделения)
     * 
     * @return null
     */
    protected function display_new_resource_order($type, $data, $addvars)
    {

        // Составляем таблицу для самого приказа
        // получаем данные для таблицы
        $orderinfo = $this->get_invorder_info($data, $type, $addvars);
        $labels    = $this->get_invorder_info_labels($type);
        // рисуем саму таблицу приказа
        $this->display_invorder_table($orderinfo, $labels);
        // выводим заголовок "Поступившее оборудование"
        print "<br><h3 align='center'>".$this->dof->get_string($type.'_items', 'inventory')."</h3>";
        // Таблица самого оборудования
        $this->display_invitems_list($data->id, $type, $addvars);
    }
    
    /** Отобразить информацию о приказе по приходу оборудования
     * 
     * @param string $type - тип приказ из таблицы
     * @param object $data  - данные из приказа
     * 
     * @return null
     */
    protected function display_scrap_resource_order($type, $data, $addvars=array())
    {
        // Составляем таблицу для самого приказа
        // получаем данные для таблицы
        $orderinfo = $this->get_invorder_info($data, $type, $addvars);
        $labels    = $this->get_invorder_info_labels($type);
        // рисуем саму таблицу приказа
        $this->display_invorder_table($orderinfo, $labels);
        // выводим заголовок "Поступившее оборудование"
        $this->dof->modlib('widgets')->print_heading($this->dof->get_string($type.'_items', 'inventory'), 'center');
        // Таблица самого оборудования
        $this->display_invitems_list($data->id, $type, $addvars);
    }
    
    /** Получить данные для отображения информационной таблицы по приказу об оборудовании
     * 
     * @param string $type - тип приказа, для которого собираются данные
     *                       new_items - приход оборудования
     *                       scrap_resource - списание оборудования
     * @param dof_storage_invitems_order_new_resource $order - приказ из таблицы orders
     * @param object $data  - данные из приказа
     * @param array $addvars - дополнит данные для перехода по ссылкам(чаще id подразделения)
     * 
     * @return array
     */
    protected function get_invorder_info($data, $type=null, $addvars=array())
    {
        $orderinfo = array();
        // Подразделение
        $orderinfo['department'] = $this->dof->im('departments')->get_html_link($data->departmentid, true);
        // Тип
        $orderinfo['type']   = $this->dof->get_string($type, 'inventory');
        // ответственный
        $orderinfo['owner']  = $this->get_person_link($data->ownerid, $addvars);
        // ответственный
        $orderinfo['signer'] = $this->get_person_link($data->signerid, $addvars);
        // Дата исполнения
        $orderinfo['exdate'] = $this->get_time($data->exdate);
        // Количество единиц оборудования
        $orderinfo['quantity'] = $data->data->quantity;
        
        if ( $type == 'new_recourse' )
        {
            // Категория, куда было зачислено оборудование (только при поступлении)
            $orderinfo['category'] = $this->dof->storage('invcategories')->get_field($data->data->categoryid,'name');
        }
        
        return $orderinfo;
    }
    
    /** Получить текстовые пояснения для таблицы с данными приказа
     * 
     */
    protected function get_invorder_info_labels($type=null)
    {
        $labels = array();
        // Подразделение
        $labels['department'] = $this->dof->get_string('department', 'inventory');
        // Тип
        $labels['type']   = $this->dof->modlib('ig')->igs('type');
        // ответственный
        $labels['owner']  = $this->dof->get_string('owner', 'inventory');
        // исполнитель
        $labels['signer'] = $this->dof->get_string('signer', 'inventory');
        // Дата исполнения
        $labels['exdate'] = $this->dof->get_string('exdate', 'inventory');
        // Количество единиц оборудования
        $labels['quantity'] = $this->dof->get_string('quantity', 'inventory');
        if ( $type == 'new_recourse' )
        {
            // Категория, куда было зачислено оборудование
            $labels['category'] = $this->dof->get_string('catname', 'inventory');
        }
        return $labels;
    }
    
    /** Отобразить таблицу с данными о приказе по оборудованию
     * Функция подходит как для прихода так и для списания
     * 
     * @param array $info - информация о приказе по оборудованию
     * @param labels $info - информация о приказе по оборудованию
     * @param string $type - тип приказа, для которого собираются данные
     *                       new_resource - приход оборудования
     *                       scrap_resource - списание оборудования
     * @return null
     */
    protected function display_invorder_table($info, $labels)
    {
        $table = new stdClass();
        $table->width = '75%';
        $table->data = array();
        
        // Составляем таблицу, компануя между собой пояснение и данные
        foreach ( $labels as $key=>$label )
        {
            $table->data[] = array('<b>'.$label.'</b>', $info[$key]);
        }
        
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    /** Вывести список всего поступившего (или списанного) оборудования
     * 
     * @param integer $id  - id приказа из таб orders 
     * @param string $type - тип приказа(удаление, приход)
     * @param array $addvars - массив с доп параметрами для перехода по ссылкам                           
     * @return null
     */
    public function display_invitems_list($id, $type=null, $addvars=array())
    {
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->head = $this->get_invitems_labels();
        // полный список оборудования
        if ( $type == 'new_items' )
        {// пришедшего
            $table->data = $this->get_new_invorder_items($id, $addvars);
        }elseif( $type == 'delete_items')
        {// списанного
            $table->data = $this->get_deleted_invorder_items($id, $addvars);
        }elseif ( $type == 'items_of_set' )
        {// обрудование в комплекта
            $table->data = $this->get_items_of_set($id, $addvars);
            // вывод оборудование в комплекте
            print "<br><h3 align='center'>".$this->dof->get_string('items_of_set','inventory')."</h3>";
        }
            
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    /** Получить список оборудования для составления таблицы
     * @todo добавить отображение серийных номеров в двух вариантах: сейчас и на момент исполнения приказа
     * @todo добавить срок использования, тип и количество - когда мы начнем их использовать
     * @param integer $id - id приказа из таблтцы order
     * 
     * @return array - массив оборудования для отображения в таблице
     */
    protected function get_new_invorder_items($id, $addvars = array())
    {
        $itemmas = array();
        // список оборудования, которое часилено эти приказом
        $items = $this->dof->storage('invitems')->get_records(array('setorderid'=>$id)); 
        $i = 1;
        foreach ( $items as $itemobj)
        {
            $item = array();
            $item[] = $i;
            // название
            $item[] = $this->get_invitem_link($itemobj->id, false, $addvars);
            // инвентарный номер
            $item[] = $itemobj->code;
            // серийный номер
            $item[] = $itemobj->serialnum;
            $i++;
            $itemmas[] = $item;
        }
        
        return $itemmas;
    }
    
    /** Получить список оборудования для 1 комплекта
     * 
     * @param int $id - запись из таблицы invitems
     * @param array $addvars - дополнит параметры для перехода по ссылкам
     * 
     * @return array - массив оборудования для отображения в таблице
     */
    protected function get_items_of_set($id, $addvars)
    {
        $items = array();
        if ( ! $itemlist = $this->dof->storage('invitems')->get_records(array('invsetid'=>$id), 'code') )
        {// нет данных - вернем пустой массив
            return $items;
        }
        // счётчик
        $j = 0;
        foreach ( $itemlist as $itemobj )
        {// Смотрим сколько оборудования поступило, и для каждого создаем массив для таблицы
            $j++;
            $item = array();
            // порядковый номер
            $item[] = $j;
            // название
            $item[] = $this->get_invitem_link($itemobj->id,false, $addvars);
            // инвентарный номер
            $item[] = $itemobj->code;
            // серийный номер
            $item[] = $itemobj->serialnum;
            
            $items[] = $item;
        }
        
        return $items;
    }    
    
    /** Получить список списанного оборудования для таблицы
     * Сортировки здесь не будет - просматриваем оборудование в том же порядке в котором списываем, чтобы
     * не путаться
     * @todo добавить срок использования, тип и количество - когда мы начнем их использовать
     * 
     * @param integer $id - id записи из таблица orders
     * @param array $addvars - массив с доп данными для перехода по ссылкам
     * 
     * @return array
     */
    protected function get_deleted_invorder_items($id, $addvars=array())
    {
        $items = array();
        if ( ! $itemlist = $this->dof->storage('invitems')->get_records(array('outorderid'=>$id), 'code') )
        {// нет данных - вернем пустой массив
            return $items;
        }
        // счётчик
        $j = 0;
        foreach ( $itemlist as $itemobj )
        {// Смотрим сколько оборудования поступило, и для каждого создаем массив для таблицы
            $j++;
            $item = array();
            // порядковый номер
            $item[] = $j;
            // название
            $item[] = $this->get_invitem_link($itemobj->id,false, $addvars);
            // инвентарный номер
            $item[] = $itemobj->code;
            // серийный номер
            $item[] = $itemobj->serialnum;
            
            $items[] = $item;
        }
        
        return $items;
    }
    
    /** Получить массив заголовков для таблицы со списком оборудования
     * @todo убрать параметр type если обнаружится что приказы о приходе и списании не различаются
     * @todo добавить срок использования, тип и количество - когда мы начнем их использовать
     * 
     * @return array
     */
    protected function get_invitems_labels($type=null)
    {
        $labels = array();
        // номер
        $labels['number']    = '№';
        // название
        $labels['name']      = $this->dof->modlib('ig')->igs('name');
        // Инвентарный номер
        $labels['code']      = $this->dof->get_string('invnum', 'inventory');
        // серийный номер
        $labels['serialnum'] = $this->dof->get_string('serialnum', 'inventory');
        
        return $labels;
    }
    
    /** Получить html-ссылку на просмотр одной единицы оборудования
     * @param int $id - id записи в таблице invitems
     * @param bool $widhcode - включать ли в имя инвентарный номер
     * @param array $addvars - доплнит данные для перехода по ссылке(id подразделения)
     * 
     * @return string
     */
    public function get_invitem_link($id, $withcode=false, $addvars=array())
    {
        if ( ! $name = $this->dof->storage('invitems')->get_field($id, 'name') )
        {
            return '';
        }
        if ( $withcode )
        {
            $code = $this->dof->storage('invitems')->get_field($id, 'code');
            $name = $name.' ['.$code.']';
        }
        
        return '<a href="'.$this->dof->url_im($this->code(), 
                    '/items/view.php?id='.$id,$addvars).'">'.$name.'</a>';
    }
    
    /** Получить html-ссылку на просмотр одного комплекта
     * @param int $id - id записи в таблице invsets
     * @param array $addvars - дополнит данные для перехода по ссылкам(чаще id подразделения)
     * @return string 
     */
    public function get_invset_link($id, $addvars=array())
    {
        if ( ! $name = $this->dof->storage('invsets')->get_field($id, 'code') )
        {
            return $this->dof->get_string('no_set','inventory');
        }
        
        return '<a href="'.$this->dof->url_im($this->code(), 
                    '/sets/view.php?id='.$id,$addvars).'">'.$name.'</a>';
    }

    /** Получить html-ссылку на просмотр персоны
     * @param int $id - id записи в таблице persons
     * @param array $addvars - дополнит данные для перехода по ссылкам(чаще id подразделения)
     * @return string 
     */
    public function get_person_link($id, $addvars)
    {
        if ( ! $name = $this->dof->storage('persons')->get_fullname($id) )
        {
            return '';
        }
        
        return '<a href="'.$this->dof->url_im('persons','/view.php?id='.$id, $addvars).'">'.$name.'</a>';
    }
    
    
    /** Отобразить данный об одной единице оборудования
     * @param int $id - id записи в таблице invitems
     * @param array $addvars - массив доп параметров для перехода по ссылкам
     * 							(id подразделения)
     * 
     */
    public function display_item($id, $addvars=array())
    {
        if ( ! $item = $this->dof->storage('invitems')->get($id) )
        {// нет записи - невозможно отобразить данные
            return;
        }
        
        $table = new stdClass();
        $table->tablealign = 'center';
        //$table->align = array('right', 'left');
        $table->data  = array();
        
        // получаем поясняюцие надписи
        $labels = $this->get_display_item_labels();
        // получаем данные для таблицы
        $data   = $this->get_display_item_data($item,$addvars);
        
        // Составляем таблицу из двух столбцов
        foreach ( $labels as $code=>$label )
        {
            $table->data[] = array('<b>'.$label.'</b>', $data[$code]);
        }
        // отображаем всю собранную таблицу
        $this->dof->modlib('widgets')->print_table($table);
    }
    
    /** Отобразить информацию о комплекте
     * @param int $id - id записи в таблице invsets
     * @param array $addvars - массив с дополнительными данными для перехода по ссылкам
     * 							(чаще это просто id подразделения)
     * return table
     */
    public function display_set_info($id, $addvars)
    {
        if ( ! $set = $this->dof->storage('invsets')->get($id) )
        {// нет записи - невозможно отобразить данные
            return;
        }
        
        // получаем поясняюцие надписи
        $labels = $this->get_display_set_labels();
        // получаем данные для таблицы
        $data   = $this->get_display_set_data($set, $addvars);
        $table = new stdClass();
        $table->tablealign = 'center';
        //$table->align = array('right', 'left');
        $table->data  = array();
        // Составляем таблицу из двух столбцов
        foreach ( $labels as $code=>$label )
        {
            $table->data[] = array('<b>'.$label.'</b>', $data[$code]);
        }
        // отображаем всю собранную таблицу
        $this->dof->modlib('widgets')->print_table($table); 
        
    }

    
    /** Получить описание для отображения одного комплекта
     * 
     * @return array
     */
    protected function get_display_set_labels()
    {
        $labels = array();
        // подразделение
        $labels['departmentid']  = $this->dof->get_string('department', 'inventory');
        // категория оборудования, к которой приписан комплект        
        $labels['invcategoryid'] = $this->dof->get_string('catname', 'inventory');
        // название
        $labels['code']          = $this->dof->modlib('ig')->igs('code');
        // тип комплекта
        $labels['type']          = $this->dof->modlib('ig')->igs('type');
        // персона, отвественная за комплект
        $labels['responsibility_person'] = $this->dof->get_string('responsibility_person', 'inventory');
        // статус
        $labels['status']        = $this->dof->modlib('ig')->igs('status');
        // примечание
        $labels['notice']        = $this->dof->get_string('notice', 'inventory');      
        return $labels;
    }    
    
    /** Получить данные для отображения таблицы с информацией об одной единице оборудования
     *
     * @param object $set - объект одного оборудования
     * @param array $addvars - массив с дополнительными данными для перехода по ссылкам
     * 							(чаще это просто id подразделения)
     * 
     * @return array
     */
    protected function get_display_set_data($set, $addvars)
    {
        $result = array();
        // код
        $result['code'] = $set->code;
        // тип комплекта
        // TODO проставить
        $result['type'] = '';
        // категория
        $result['invcategoryid'] = $this->dof->storage('invcategories')->get_field($set->invcategoryid, 'name');
        // подразделение
        $result['departmentid'] = $this->dof->im('departments')->get_html_link($set->departmentid);
        // отвественная персона
        $result['responsibility_person'] = $this->get_person_link($set->personid, $addvars);
        // статус
        $result['status'] = $this->dof->workflow('invsets')->get_name($set->status);
        // примечание
        $result['notice'] = $set->note;
        return $result;
    }    
    
    
    
    /** Получить описание для отображения одной единицы оборудования
     * 
     * @return array
     */
    protected function get_display_item_labels()
    {
        $labels = array();
        // подразделение
        $labels['departmentid']  = $this->dof->get_string('department', 'inventory');
        // категория оборудования        
        $labels['invcategoryid'] = $this->dof->get_string('catname', 'inventory');
        // название
        $labels['name']          = $this->dof->modlib('ig')->igs('name');
        // инвентарный номер
        $labels['code']          = $this->dof->get_string('invnum', 'inventory');
        // серийный номер
        $labels['serialnum']     = $this->dof->get_string('serialnum', 'inventory');
        // @todo тип оборудования пока не используется
        // $labels['type']
        // @todo счетное оборудование пока не используется
        // $labels['count']
        // @todo срок полезного использования пока не используется
        // $labels['termofuse']
        // дата поступления 
        $labels['dateentry']     = $this->dof->get_string('dateentry_header1', 'inventory');
        // дата списания
        $labels['datewriteoff']  = $this->dof->get_string('datewriteoff_header', 'inventory');
        // комплект
        $labels['invsetid']      = $this->dof->get_string('set', 'inventory');
        // статус
        $labels['status']        = $this->dof->modlib('ig')->igs('status');
        
        return $labels;
    }

    /** Получить данные для отображения таблицы с информацией об одной единице оборудования
     * @todo объединить с функцией получения данных для отрисовки таблицы с оборудованием.
     *       Получать все данные, а отрисовывать только нужные
     * @todo отображать статус комплекта в котором находится оборудование
     * @param object $item - объект из таблицы invitems
     * 
     * @return array
     */
    protected function get_display_item_data($item,$addvars=array())
    {
        $result = array();
        // название
        $result['name']      = $item->name;
        // инвентарный номер
        $result['code']      = $item->code;
        // серийный номер
        $result['serialnum'] = $item->serialnum;
        // дата поступления
        $result['dateentry'] = $this->get_time($item->dateentry);
        // дата списания
        if ( $item->status == 'scrapped' )
        {
            $result['datewriteoff'] = $this->get_time($item->datewriteoff);
        }else
        {
            $result['datewriteoff'] = $this->dof->modlib('ig')->igs('no');
        }
        
        // категория
        $result['invcategoryid'] = $this->dof->storage('invcategories')->get_field($item->invcategoryid, 'name');
        // подразделение
        $result['departmentid'] = $this->dof->im('departments')->get_html_link($item->departmentid);
        // комплект (со ссылкой на просмотр)
        $result['invsetid'] = $this->get_invset_link($item->invsetid,$addvars);
        // статус
        $result['status'] = $this->dof->workflow('invitems')->get_name($item->status);
        
        return $result;
    }
    
    /*
     * Вывод времени в род падеже
     */
    public function get_time($time)
    {
        $data = strtolower(date('F', $time).'_r');
        $data = dof_userdate($time,'%d').' '.$this->dof->modlib('ig')->igs($data).' '.dof_userdate($time,'%Y');
        return $data;
    }
    

    /** Вывод на эран списка приказов по параметрам
     * 
     * @param array   $type - тип приказа, в зависимости от типа отобразить приказы
     * @param inteher $depid - id подразделения, если 0, то отобразить все
     * @param array() $time - массив, за промежуток времени(begindate, enddate)
     * @param integer $userid - id персоны, который исполнил приказ
     * 
     * 
     * @return string, готовый код html в виде списка приказов
     */
    public function print_inventory_orders_list($code, $depid=null, $time=null, $signerid=null, $addvars=array())
    {
        if ( ! is_array($code) OR empty($code) )
        {// неверный формат типа приказа или пустой 
            return false;
        }
        // подключаем приказ
        require_once($this->dof->plugin_path('storage','invitems','/order/invitems_order.php'));
        $text = '';
        // перебираем коды
        foreach ( $code as $value )
        {
            // работа c якорями, установим их
            if ( $value == 'new_items' )
            {
                $anchor = '<a name="new_items"></a>';
                $link = "<a style='color:green;font-size:13px;' href='#delete_items'>[".$this->dof->get_string('delete_items','inventory')."&darr;]</a>";
            }
            if ( $value == 'delete_items' )
            {
                $anchor = '<a name="delete_items"></a>';
                $link = "<a style='color:green;font-size:13px;' href='#new_items'>[".$this->dof->get_string('new_items','inventory')."&uarr;]</a>";
            }            
            
            // берем приказы
            if ( $orders = $this->dof->storage('orders')->get_list_by_code('storage','invitems',$value,$depid,null,$signerid,$status=null,'','',$time,'id DESC') )
            {// есть приказ
                // тип приказа (поступление оборудования или списание оборудования)
                $type = $this->dof->get_string($value,'inventory');
                $text .="<br>".$type." ".$anchor.$link."<ul>";
                foreach ( $orders as $id=>$obj )
                {
                    // сформируем строку для вывода
                    $classname = "dof_storage_invitems_order_".$value;
                    $order = new $classname($this->dof);
                    $a = $order->load($id);
                    $data = $a->data;
                    //print_object($data);die;
                    
                    if ( $order->is_executed() )
                    {// выводим толко исполненные приказы
                        $text .= '<li>';
                        $time = $this->dof->storage('orders')->get_field($id,'exdate');
                        // приказы отдичаься и мы их будем тут отличать
                        if ( $value == 'new_items' )
                        {
                            $category = $this->dof->storage('invcategories')->get_field($data->categoryid,'name');
                            $name = '№'.$id.' ['.dof_userdate($time,'%d.%m.%Y').
                                    '] ['.$category .'] '.$data->name;
                        }else 
                        {
                             $name = '№'.$id.' ['.dof_userdate($time,'%d.%m.%Y').']';
                        } 
                        // делаем сылку на приказ
                        $name = '<a id="im_inventory_vieworder_'.$id.'" href='.
                            $this->dof->url_im('inventory','/invorders/view.php?id='.$id,$addvars).'>'.$name.'</a>';   
                        $text .= $name;
                        $text .= '</li>';
                    }
                    
                    
                }                  
                
                $text .='</ul>';      
            }
        }
        return $text;
        
    }    

    /* Метод дополнит навигации для раздела РЕСУРСЫ
     * 
     * @param string $url - адрес страницы, куда ведет ссылка, 
     * 		со всеми доп параметрами(departmentid), НО КРОМЕ категории
     * @param integer $catid - id записи из табл invcategories
     * @param integer $addvars - массив с доп записями перехода по ссылкам
     * 
     * @return string $text - html код с навигацие/либо пустой\та
     */
    public function additional_nvg($path, $addvars)
    {
        // параметры без категории
        $addvars_nocat = $addvars;
        unset($addvars_nocat['invcategoryid']);
        $depid = $addvars['departmentid'];
        $url = $this->dof->url_im('inventory',$path, $addvars_nocat);
        $catid = $addvars['invcategoryid'];
        $cat_nvg = array();
        if ( $depid )
        {
            $cats = $this->dof->storage('invcategories')->get_records(array('departmentid'=>$depid,'parentid'=>0,'status'=>'active'));
        }else 
        {
            $cats = $this->dof->storage('invcategories')->get_records(array('parentid'=>0,'status'=>'active'));
        }
        if ( $cats )
        {// нет категорий - нечего показывать
            foreach ( $cats as $id=>$catobj )
            {
                $cat_nvg[] = $id; 
            }
        }
        $text = '';
        // создание категориии
        $path_new = $this->dof->url_im('inventory','/category/edit.php', $addvars);
        $create_cat  = '<a href="'.$path_new.'">'.$this->dof->get_string('cat_create','inventory').'</a> ';     
        $create_cat .= $this->dof->modlib('ig')->icon('add', $path_new,array('title' => $this->dof->get_string('cat_create','inventory') ));
        $create_cat .= ' <-> ';
        // просмотр категорий
        $path_view = $this->dof->url_im('inventory','/category/list.php', $addvars);
        $see_cat  = '<a href="'.$path_view.'">'.$this->dof->get_string('view_category','inventory').'</a> ';   
        $see_cat .= $this->dof->modlib('ig')->icon('view', $path_view,array('title' => $this->dof->get_string('view_category','inventory') ));
        $conds = $addvars;
        unset($addvars['invcategoryid']);
        if ( empty($catid) )
        {// выведем родительские
            $text .= '<div style="color:green;">'.$this->dof->get_string('all_cats','inventory').'</div>';
            foreach ( $cat_nvg as $id )
            {
                $name = $this->dof->storage('invcategories')->get_field($id, 'name');
                $text .= "<a href='$url&invcategoryid=$id'>$name</a> | ";
            }
            $text .= '<br>';
        }elseif( $cats ) 
        {// передали категорию
            $text .= "<a href='$url'>".$this->dof->get_string('all_cats','inventory')."</a>-&gt;";  
            // берем путь(родителей)
            $path = $this->dof->storage('invcategories')->get_field($catid, 'path');
            // или это главный или вложенный
            $catids = explode('/',$path);
            // узнаем кол элементов
            $count = count($catids);
            // счсётчик
            $i = 1;
            foreach ( $catids as $id )
            {
                 if ( $count == $i )
                 {
                    $text .= "<span style='color:green'>".$this->dof->storage('invcategories')->get_field($id, 'name')."</span>   ";
                    // редактировакние
                    $path_edit = $this->dof->url_im('inventory','/category/edit.php?id='.$id, $conds);
                    $text .= $this->dof->modlib('ig')->icon('edit', $path_edit,array('title' => $this->dof->get_string('cat_edit','inventory') ));                    
                    $text .= '<br>';                     
                 }else
                 {
                    $name = $this->dof->storage('invcategories')->get_field($id, 'name');
                    $text .= "<a href='$url&invcategoryid=$id'>$name</a>->";                    
                 }
                 $i++;
            }
            // установим указатель на последний элемент
            end($catids);
            // запомним этот элемент
            $id = current($catids);
            if ( $addvars['departmentid'] )
            {
                $cats = $this->dof->storage('invcategories')->get_records(array('departmentid'=>$depid,'parentid'=>$id,'status'=>'active'));
            }else 
            {
                $cats = $this->dof->storage('invcategories')->get_records(array('parentid'=>$id,'status'=>'active'));
            }
            if ( $cats  )
            {
                foreach ( $cats as $obj )
                {
                    $name = $this->dof->storage('invcategories')->get_field($obj->id, 'name');
                    $text .= "<a href='$url&invcategoryid=$obj->id'>$name</a> | ";                     
                }
                $text .= '<br>';
                
            }
            
                    
            
        }
        if ( $text AND $text !== '<br>' )
        {
            $text .= '<br>';
        }
        return $text.$create_cat.$see_cat;    
    }
    
    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function report($code, $id = NULL)
    {
        return $this->dof->storage('reports')->report($this->type(), $this->code(), $code, $id);
    }
    
    /** Показать список выдаваемого оборудования для подтверждения выдачи
     * @param object $formdata - данные пришедшие из формы выдачи произвольнгого оборудования
     * @param integer $depid - id записи из таблицы departments
     * 
     * @return null
     */
    public function display_delivery_set_confirmation($formdata, $depid)
    {
        // отображаем информацию о комплекте
        print_heading($this->dof->get_string('set', $this->code()));
        if ( $formdata->setid == 0 )
        {// выдаём произвольный комплект - тогда надо выбрать
            // соберем категории 
            $categories = array_keys($this->dof->storage('invcategories')->
                category_list_subordinated($formdata->categoryid,null,null,true,' ',$depid)); 
            // добавим текущюю
            $categories[$formdata->categoryid] = $formdata->categoryid;              
            // выбираем свободный комплекты и 1, больше не надо
            $sets = $this->dof->storage('invsets')->get_records(array('status'=>'active', 'invcategoryid'=>$categories,'','*','',1));
            // берем этто комплект
            $formdata->setid = key($sets);        
            
        }
        $this->display_set_info($formdata->setid, array());
        // Отображаем информацию о персоне
        $persondata = $this->dof->im('persons')->get_fullname($formdata->search['id_autocomplete'],true);
        print_heading($this->dof->get_string('person', 'persons').': '.$persondata);
    }
    
    /** Определить, включен ли плагин для указанного подразделения
     * @param int $departmantid - id подразделения (в таблице departments), 
     *                            для которого проверяется доступность
     * @param int $personid[optional] - id пользователя в таблице persons для которого проверяется
     *                        доступность плагина
     * 
     * @return bool
     */
    public function is_enabled($departmentid, $personid=0)
    {
        if ( $this->is_access('datamanage') )
        {// считаем что для администратора включены все плагины во всех подразделениях
            return true;
        }
        if ( ! $personid )
        {
            $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        }
        
        // в остальных случаях возвращаем настройку, пользуясь стандартной логикой get_config
        return $this->dof->storage('config')->
            get_config_value('enabled', 'im', 'inventory', $departmentid, $personid);
    }

    /** Метод, который возаращает список для автозаполнения
     * 
     * @param string $querytype - тип завпроса(поу молчанию стандарт)
     * @param string $data - строка
     * @param integer $depid - id подразделения  
     * 
     * @return array or false - запись, если есть или false, если нет
     */
    public function widgets_field_variants_list($querytype, $depid, $data, $objectid)
    {
        // в зависимости от типа, проверяем те или иные права
        switch ($querytype)
        {
            // выдать конкретной персоне косплект
            case 'person_give_set' :        
                // есть права - то посылаем запрос
                if ( $this->is_access('view_all_journals',$objectid,NULL,$depid) )
                {
                    return $this->dof->storage('persons')->result_of_autocomplete($querytype, $depid, $data);
                }
                break;
            // сформировать 1 комплект    
            case 'im_inventory_newinvset_form':
            	//
            	if ( $this->dof->storage('invsets')->is_access('create', NULL, NULL, $depid ) )
            	{
            	     return $this->dof->storage('invitems')->widgets_field_ajax_select($querytype, $depid, $data);
            	} 
            	break;
            // выдать 1 комплект
            case 'im_inventory_delivery':
            	//
            	if ( $this->is_access('view') )
            	{
            	     return $this->dof->storage('invsets')->widgets_field_ajax_select($querytype, $depid, $data);
            	} 
            	break; 	    
        }    
        
       // нет ничего
       return false;
        
    }     
    
    //////////////////////////////////////////////////////////////
    // Методы получения URL для совершения действий с объектами //
    //////////////////////////////////////////////////////////////
    
    /** Получить url для совершения действия с объектом хранилища invcategories
     * (категории оборудования)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function invcategories_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            // у нас нет страницы просмотра одной категории
            return '';
        }
    }
    /** Получить url для совершения действия с объектом хранилища invsets
     * (комплекты оборудования)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function invsets_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            $urlparams = array_merge($urlparams, array('id' => $id));
            return $this->url('/sets/view.php', $urlparams);
        }
    }
    /** Получить url для совершения действия с объектом хранилища invitems
     * (учетные единицы оборудования)
     * @param int    $id - id объекта в хранилище
     * @param string $action - тип действия (view, edit, delete...)
     * @param array  $urlparams - дополнительные параметры для ссылки
     * 
     * @return string - url для совершения действия
     */
    protected function invitems_action_url($id, $action, array $urlparams=array())
    {
        if ( $action == 'view' )
        {// Получение ссылки на просмотр объекта
            $urlparams = array_merge($urlparams, array('id' => $id));
            return $this->url('/items/view.php', $urlparams);
        }
    }
}


