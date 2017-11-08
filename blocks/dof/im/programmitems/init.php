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

/** Учебные предметы
 * 
 */
class dof_im_programmitems implements dof_plugin_im
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
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
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
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
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
        return 'programmitems';
    }

    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib'   => array('nvg'           => 2008060300,
                                         'widgets'       => 2009050800),
                     'storage'  => array('persons'       => 2009060400,
                                         'programmitems' => 2011032900,
                                         'pridepends'    => 2011032500,
                                         'acl'           => 2011041800),
                     'sync'     => array('minfo'         => 2011061700));
        //'im' => array('employees' => 2010040500)
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
    public function is_setup_possible($oldversion = 0)
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
    public function is_setup_possible_list($oldversion = 0)
    {
        return array('storage' => array('acl'   => 2011040504,
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
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( !$this->is_access($do, $objid, $userid) )
        {
            $notice = "programmitems/{$do} (block/dof/im/programmitems: {$do})";
            if ( $objid )
            {
                $notice.=" id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
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
        if ( $gentype == 'im' AND $gencode == 'obj' AND $eventcode == 'get_object_url' )
        {
            if ( $mixedvar['storage'] == 'programmitems' )
            {
                if ( isset($mixedvar['action']) AND $mixedvar['action'] == 'view' )
                {// Получение ссылки на просмотр объекта
                    $params = array('pitemid' => $intvar);
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
    public function cron($loan, $messages)
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
    public function todo($code, $intvar, $mixedvar)
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
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        switch ( $name )
        {
            case 'main':
                $path = $this->dof->url_im('programmitems', '/index.php', $addvars);
//                $rez .= "<a href=\"{$path}\">".$this->dof->get_string('title', 'programmitems').'</a>';
//                $rez .= "<br />";
                if ( $this->dof->storage('programmitems')->is_access('view') )
                {//может видеть все предметы
                    $path = $this->dof->url_im('programmitems', '/list.php', $addvars);
                }
                //ссылка на список подразделений
                $rez .= "<a href=\"{$path}\">" . $this->dof->get_string('list', 'programmitems') . '</a>';
                if ( $this->dof->storage('programmitems')->is_access('create') )
                {//может создавать предмет - покажем ссылку
                    $rez .= "<br />";
                    $path = $this->dof->url_im('programmitems', '/edit.php', $addvars);
                    $rez .= "<a href=\"{$path}\">" . $this->dof->get_string('new', 'programmitems') . '</a>';
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
        switch ( $name )
        {
            
        }
        return $rez;
    }

    /** 
     * Возвращает текст, отображаемый в блоке на странице курса MOODLE
     * 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format = 'other')
    {
        return "<a href='{$this->dof->url_im('programmitems', '/index.php')}'>"
                . $this->dof->get_string('page_main_name') . "</a>";
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
        $result->objectid = $objectid;
        if ( !$objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage('programmitems')->get_field($objectid, 'departmentid');
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

    public function acldefault()
    {
        $a = array();

        return $a;
    }

    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code = null)
    {
        // Параллелей в метадисциплинах
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'max_age_meta_pitems';
        $obj->value = 15;
        $config[$obj->code] = $obj;
        return $config;
    }
    // **********************************************
    //              Собственные методы
    // **********************************************

    /** Получить URL к собственным файлам плагина
     * @param string $adds [optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars [optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds = '', $vars = array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }

    /**
     * Возвращает html-код отображения 
     * информации об учебном предмете
     * @param stdClass $obj - запись из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show($obj, $conds)
    {
        if ( !is_object($obj) )
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        $data = $this->get_string_table($obj, $conds);
        // выводим таблицу на экран
        return $this->print_single_table($data);
    }

    /**
     * Возвращает html-код отображения 
     * информации об учебном предмете
     * @param int $id - id записи из таблицы
     * @return mixed string html-код или false в случае ошибки
     */
    public function show_id($id, $conds)
    {
        if ( !is_int_string($id) )
        {//входные данные неверного формата 
            return false;
        }
        if ( !$obj = $this->dof->storage('programmitems')->get($id) )
        {// предмет не найден
            return false;
        }
        return $this->show($obj, $conds);
    }

    /**
     * Возвращает html-код отображения 
     * информации о нескольких предметах
     * @param массив $list - массив записей 
     * предметов, которые надо отобразить 
     * @return mixed string в string html-код или false в случае ошибки
     */
    public function showlist($list, $conds)
    {
        if ( !is_array($list) )
        {// переданны данные неверного формата
            return false;
        }
        $data = array();
        // заносим данные в таблицу
        foreach ( $list as $obj )
        {
            $data[] = $this->get_string_table($obj, $conds);
        }

        // выводим таблицу на экран
        return $this->print_table($data);
    }

    /**
     * Возвращает форму создания/редактирования с начальными данными
     * @param int $id - id записи, значения 
     * которой устанавливаются в поля формы по умолчанию
     * @param array $options - дополнительные параметры
     * @return moodle quickform object
     */
    public function form($id = NULL, $options = null)
    {
        global $USER;
        // устанавливаем начальные данные
        if ( isset($id) AND ( $id <> 0) )
        {// id передано
            $pitem = $this->dof->storage('programmitems')->get($id);
        } elseif ( is_array($options) AND ! empty($options) )
        {// id не передано, но переданы дополнительные параметры
            $pitem = new stdClass();
            foreach ( $options as $name => $value )
            {// составляем список предустановленных значений для формы
                $pitem->$name = $value;
            }
        } else
        {// создать чистую форму
            $pitem = $this->form_new_data();
        }
        if ( isset($USER->sesskey) )
        {//сохраним идентификатор сессии
            $pitem->sesskey = $USER->sesskey;
        } else
        {//идентификатор сессии не найден
            $pitem->sesskey = 0;
        }
        $customdata = new stdClass;
        $customdata->pitem = $pitem;
        $customdata->dof = $this->dof;
        // подключаем методы вывода формы
        $form = new dof_im_programmitems_edit_form(null, $customdata);

        if ( isset($pitem->status) )
        {// очистим статус, чтобы не отображался латинскими буквами как в БД
            unset($pitem->status);
        }
        // заносим значения по умолчению
        $form->set_data($pitem);
        // возвращаем форму
        return $form;
    }

    /**
     * Возвращает заготовку для формы создания предмета
     * @return stdclassObject
     */
    private function form_new_data()
    {
        $pitem = new stdClass();
        $pitem->id = 0;
        $pitem->gradelevel = 'discipline';
        $pitem->controltypeid = 17;
        return $pitem;
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
        $table->cellpadding = 2;
        $table->cellspacing = 2;
        // @todo пока что оставим надежду вместить эту таблицу в ширину экрана...
        //$table->size = array ('100px','150px','150px','200px','150px','100px');
        $table->align = array();
        for ( $i = 0; $i <= 20; $i++ )
        {// все по центру выравниваем, короче
            $table->align[] = "center";
        }
        // шапка таблицы
        $table->head = $this->get_fields_description();
        // заносим данные в таблицу     
        $table->data = $date;
        return $this->dof->modlib('widgets')->print_table($table, true);
    }

    /** Распечатать вертикальную таблицу для удобного отображения информации по элементу
     * 
     * @return null
     * @param object $data объект с отображаемыми значениями
     */
    private function print_single_table($data)
    {
        $table = new stdClass();
        if ( !$data )
        {
            return '';
        }
        // получаем подписи с пояснениями
        $descriptions = $this->get_fields_description();
        $i = 0;
        foreach ( $data as $elm )
        {
            $table->data[] = array('<b>' . current(each($descriptions)) . '</b>', $elm);
        }
        return $this->dof->modlib('widgets')->print_table($table, true);
    }

    /** Получить заголовок для списка таблицы, или список полей
     * для отображения одного объекта 
     * @return array
     */
    private function get_fields_description()
    {
        return array($this->dof->get_string('actions', 'programmitems'),
            $this->dof->get_string('name', 'programmitems'),
            $this->dof->get_string('sname', 'programmitems'),
            $this->dof->get_string('code', 'programmitems'),
            $this->dof->get_string('scode', 'programmitems'),
            $this->dof->get_string('program', 'programmitems'),
            $this->dof->get_string('department', 'programmitems'),
            $this->dof->get_string('status', 'programmitems'),
            $this->dof->get_string('type', 'programmitems'),
            $this->dof->get_string('required', 'programmitems'),
            $this->dof->get_string('maxcredit', 'programmitems'),
            $this->dof->get_string('eduweeks', 'programmitems'),
            $this->dof->get_string('maxduration', 'programmitems'),
            $this->dof->get_string('hours_all', 'programmitems'),
            $this->dof->get_string('hours_theory', 'programmitems'),
            $this->dof->get_string('hours_practice', 'programmitems'),
            $this->dof->get_string('hours_lab', 'programmitems'),
            $this->dof->get_string('hours_ind', 'programmitems'),
            $this->dof->get_string('hours_control', 'programmitems'),
            $this->dof->get_string('hours_classroom', 'programmitems'),
            //$this->dof->get_string('hours_laboratoryworks_thead','programmitems'),
            //$this->dof->get_string('hours_selfstudywithteacher_thead','programmitems'),
            $this->dof->get_string('level', 'programmitems'),
            $this->dof->get_string('about', 'programmitems'),
            $this->dof->get_string('notice', 'programmitems'),
            $this->dof->get_string('controltype_thead', 'programmitems'),
            $this->dof->get_string('scale', 'programmitems'),
            $this->dof->get_string('mingrade', 'programmitems', '<br>'),
            $this->dof->get_string('gradesyncenabled', 'programmitems'),
            $this->dof->get_string('incjournwithoutgrade', 'programmitems'),
            $this->dof->get_string('incjournwithunsatisfgrade', 'programmitems'),
            $this->dof->get_string('altgradeitem', 'programmitems'),
            $this->dof->get_string('mcourse', 'programmitems'),
            $this->dof->get_string('billingtext', 'programmitems'),
            $this->dof->get_string('salfactor', 'programmitems'));
    }

    /** Возвращает массив для вставки в таблицу
     * @param object $obj
     * @param string $conds
     * @param string $show
     * @return array
     */
    private function get_string_table($obj, $conds)
    {
        // для ссылок вне плагина
        $conds = (array) $conds;
        if ( $obj->programmid === '0' )
        {
            $conds['meta'] = 1;
        } else
        {
            $conds['meta'] = 0;
        }
        $outconds = array();
        $outconds['departmentid'] = $conds['departmentid'];
        $outconds['meta'] = $conds['meta'];
        if ( $this->dof->storage('departments')->is_access('view', $obj->departmentid) )
        {// ссылка на подразделение (если есть права)
            $department = $this->dof->im('departments')->get_html_link($obj->departmentid, true);
        } else
        {
            $department = $this->dof->storage('departments')->get_field($obj->departmentid, 'name') . ' <br>[' .
                    $this->dof->storage('departments')->get_field($obj->departmentid, 'code') . ']';
        }
        if ( $this->dof->storage('programms')->is_access('view', $obj->programmid) )
        {// ссылка на просмотр программы (при наличии прав)
            $progname = $this->dof->im('programms')->get_html_link($obj->programmid, true);
        } else
        {
            $progname = $this->dof->storage('programms')->get_field($obj->programmid, 'name') . ' <br>[' .
                    $this->dof->storage('programms')->get_field($obj->programmid, 'code') . ']';
        }
        //получаем ссылки на картинки
        $imgedit = '<img src="' . $this->dof->url_im('programmitems', '/icons/edit.png') . '"
            alt="' . $this->dof->get_string('edit', 'programmitems') . '" title="' . $this->dof->get_string('edit', 'programmitems') . '">';
        $imgview = '<img src="' . $this->dof->url_im('programmitems', '/icons/view.png') . '" 
            alt="' . $this->dof->get_string('view', 'programmitems') . '" title="' . $this->dof->get_string('view', 'programmitems') . '">';
        // панель инструментов
        $actions = '';
        if ( $this->dof->storage('programmitems')->is_access('edit', $obj->id) )
        {//покажем ссылку на страницу редактирования
            $actions .= '<a href=' . $this->dof->url_im('programmitems', '/edit.php?pitemid=' .
                            $obj->id, $conds) . '>' . $imgedit . '</a>&nbsp;';
        }
        if ( $this->dof->storage('programmitems')->is_access('view', $obj->id) )
        {//покажем ссылку на страницу просмотра
            $actions .= '<a href=' . $this->dof->url_im('programmitems', '/view.php?pitemid=' .
                            $obj->id, $conds) . '>' . $imgview . '</a>&nbsp;';
        }
        if ( $conds['meta'] !== 1 )
        {
            // планирование
            if ( $this->dof->im('plans')->is_access('viewthemeplan', $obj->id, null, 'programmitems') )
            {// если есть право на просмотр планирования
                $actions.= '<a href="' . $this->dof->url_im('plans', '/themeplan/viewthemeplan.php?linktype=programmitems&linkid=' . $obj->id, $outconds) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/plan.png') . '"
                alt=  "' . $this->dof->get_string('view_plancstream', 'programmitems') . '" 
                title="' . $this->dof->get_string('view_plancstream', 'programmitems') . '" /></a>&nbsp;';
            }
            // подписки
            if ( $this->dof->storage('cpassed')->is_access('view') )
            {// если есть право на просмотр списка подписок
                $actions .= '<a href="' . $this->dof->url_im('cpassed', '/list.php?programmitemid=' . $obj->id, $outconds) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/cpassed.png') . '"
            alt=  "' . $this->dof->get_string('view_cpassed', 'programmitems') . '" 
            title="' . $this->dof->get_string('view_cpassed', 'programmitems') . '" /></a>&nbsp;';
            }
            // создание предмето-потоков
            if ( $this->dof->storage('cstreams')->is_access('create') )
            {// если есть право на просмотр списка подписок
                $actions .= '<a href="' . $this->dof->url_im('cstreams', '/edit.php?programmitemid=' . $obj->id
                                . '&programmid=' . $obj->programmid, $outconds) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/create_cstreams.png') . '"
            alt=  "' . $this->dof->get_string('create_cstream_for_programmiteam', 'programmitems') . '" 
            title="' . $this->dof->get_string('create_cstream_for_programmiteam', 'programmitems') . '" /></a>&nbsp;';
            }
            // подписка учителе
            if ( $this->dof->storage('teachers')->is_access('create') )
            {// если есть право на просмотр списка подписок
                $actions .= '<a href="' . $this->dof->url_im('employees', '/view_programmitem.php?id=' . $obj->id, $outconds) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/assign_teachers.png') . '"
            alt=  "' . $this->dof->get_string('assign_teachers_for_programmiteam', 'programmitems') . '" 
            title="' . $this->dof->get_string('assign_teachers_for_programmiteam', 'programmitems') . '" /></a>&nbsp;';
            }
            // подписка учителе
            if ( $this->dof->im('cstreams')->is_access('viewcurriculum') )
            {// если есть право на просмотр списка подписок
                $actions .= '<a href="' . $this->dof->url_im('cstreams', '/by_groups.php?programmid=' . $obj->programmid . '&agenum=' . $obj->agenum, $outconds) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/view_edu_process.png') . '"
            alt=  "' . $this->dof->get_string('participants_cstreams', 'programmitems') . '" 
            title="' . $this->dof->get_string('participants_cstreams', 'programmitems') . '" /></a>&nbsp;';
            }
            // добавление зависимостей для дисциплин
            if ( $this->is_access('edit', $obj->id) )
            {// если есть право на редавктирование дисциплин
                $actions .= '<a href="' . $this->dof->url_im('programmitems', '/pridepends.php?id=' . $obj->id, $conds) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/depends.png') . '"
            alt=  "' . $this->dof->get_string('pridepends', 'programmitems') . '" 
            title="' . $this->dof->get_string('pridepends', 'programmitems') . '" /></a>&nbsp;';
            }
        }
        // статус русскими буквами
        $status = $this->dof->workflow('programmitems')->get_name($obj->status);
        // тип предмета
        $type = $this->dof->storage('programmitems')->get_type_name($obj->type);
        if ( $obj->required )
        {// обязательное поле - выведем "да"
            $required = '<div style="color: green;">' . $this->dof->get_string('yes', 'programmitems') . '</div>';
        } else
        {// необязательное поле - выведем "нет"
            $required = '<div style="color: gray;">' . $this->dof->get_string('no', 'programmitems') . '</div>';
        }

        if ( $obj->gradesyncenabled )
        {// синхронизация оценок разрешена - выведем "да"
            $gradesyncenabled = '<div style="color: green;">' . $this->dof->get_string('yes', 'programmitems') . '</div>';
        } else
        {// синхронизация оценок запрещена - выведем "нет"
            $gradesyncenabled = '<div style="color: gray;">' . $this->dof->get_string('no', 'programmitems') . '</div>';
        }

        if ( $obj->incjournwithoutgrade )
        {// включать в ведомость пользователей без оценки или не подписанных на курс - выведем "да"
            $withoutgrade = '<div style="color: green;">' . $this->dof->get_string('yes', 'programmitems') . '</div>';
        } else
        {// не включать в ведомость пользователей без оценки или не подписанных на курс - выведем "нет"
            $withoutgrade = '<div style="color: gray;">' . $this->dof->get_string('no', 'programmitems') . '</div>';
        }

        if ( $obj->incjournwithunsatisfgrade )
        {// включать в ведомость пользователей с неудовлетворительной оценкой - выведем "да"
            $badgrade = '<div style="color: green;">' . $this->dof->get_string('yes', 'programmitems') . '</div>';
        } else
        {// не включать в ведомость пользователей с неудовлетворительной оценкой - выведем "нет"
            $badgrade = '<div style="color: gray;">' . $this->dof->get_string('no', 'programmitems') . '</div>';
        }

        // максимальная продолжительность в днях
        $maxduration = ceil($obj->maxduration / (3600 * 24)) . ' ' . $this->dof->get_string('days', 'programmitems');
        // уровень компоненты
        $instrlevel = $this->dof->modlib('refbook')->get_st_component_type_name($obj->instrlevelid);
        $controltype = $this->dof->modlib('refbook')->get_st_total_control_name($obj->controltypeid);
        // курс в moodle
        $mcoursename = '';
        if ( $mcourse = $this->dof->sync('minfo')->get_course($obj->mdlcourse) )
        {
            $mcourselink = $this->dof->sync('minfo')->get_course_link($obj->mdlcourse);
            $mcoursename = '<a href="' . $mcourselink . '">' . $mcourse->fullname . '</a>';
        }
        $name = $this->get_html_link($obj->id, false, $outconds);
        $data = array($actions,            $name,                $obj->sname,
                      $obj->code,          $obj->scode,          $progname,
                      $department,         $status,              $type,
                      $required,           $obj->maxcredit,      $obj->eduweeks,
                      $maxduration,        $obj->hours,          $obj->hourstheory,
                      $obj->hourspractice, $obj->hourslab,       $obj->hoursind,
                      $obj->hourscontrol,  $obj->hoursclassroom, $instrlevel,
                      $obj->about,         $obj->notice,         $controltype,
                      $obj->scale,         $obj->mingrade,       $gradesyncenabled,
                      $withoutgrade,       $badgrade,            $obj->altgradeitem,
                      $mcoursename,        $obj->billingtext,   $obj->salfactor);
        return $data;

    }

    /** 
     * Вывести на экран список предметов, которые входят в программу, в виде таблицы
     * Добавленный функционал-вывести на экран список метадисциплин(для этого переданный параметр
     * $conds->metaprogrammitemid должен быть равен 0).Реализован вывод таблицы метадисциплин для данной параллели
     * с ссылками "создать" и "создать и редактировать", для этого должен быть передан параметр $key=1
     * 
     * @param int $programmid - id программы для которой запрашивается список предметов
     * @param string $conds - массив условий выборки
     * @param int $key - ключ управления логикой
     * @return null|false
     */
    public function print_list_agenums($programmid, $conds = null, $key = 0)
    {
        //Если метод вызван для вывода метадисциплин
        $programm = new stdClass();
        $programm->id = 0;
        $programm->agenums = $this->dof->storage('config')->get_config
                        ('max_age_meta_pitems', 'im', 'programmitems', $conds->departmentid)->value;

        if ( $conds === null )
        {
            $conds->metaprogrammitemid = null;
        }

        if ( $key == 1 )
        {
            if ( $conds->agenum != 0 )
            {// Выводим список метадисциплин для конкретной параллели
                $this->print_agenum_table($programmid, $conds->agenum, $this->dof->get_string('metapitem_table_title', 'programmitems') . $conds->agenum, $conds, $key);
            }
            // Нулевую параллель отображаем в любом случае
            $this->print_agenum_table($programmid, 0, $this->dof->get_string('optional_pitems', 'programmitems'), $conds, $key);
            return true;
        }

        if ( $conds->metaprogrammitemid === 0 )//отрисовываем таблицы метадисциплин
        {
            //вывобим дисциплины для всех параллелей
            $this->print_agenum_table(0, 0, $this->dof->get_string('optional_pitems', 'programmitems'), $conds);

            // печатаем таблицы по параллелям
            $this->print_ages_tables($programm, $conds);
            return true;
        } else
        {
            // извлекаем программу
            if ( !$programm = $this->dof->storage('programms')->get($programmid) )
            {// учебная программа не найдена
                $this->dof->print_error($this->dof->get_string('program_not_found', 'programmitems'));
            } elseif ( !$this->dof->storage('programmitems')->is_exists(array('programmid' => $programmid)) )
            {// в программе пока нет ни одного предмета
                // выведем сообщение, и переадресуем пользователя обратно
                print($this->get_programm_title($programm->name . ' [' . $programm->code . ']'));
                $this->dof->modlib('widgets')->print_box('<div align="center">' . $this->dof->get_string('no_items_in_program', 'programmitems') . '</div>' .
                        // выводим ссылку на создание нового предмета
                        '<div align="center">(<a href="' .
                        $this->dof->url_im('programmitems', '/edit.php?programmid=' . $programmid, $conds) .
                        '" id="add_new_pirem_to_programm">' .
                        $this->dof->get_string('addpitem', 'programmitems') . '</a>)</div>');
                // рисуем таблицу с параллелями
                $this->print_ages_tables($programm, $conds);
            } else
            {// составляем таблицу
                print($this->get_programm_title($programm->name . ' [' . $programm->code . ']'));
                // печатаем таблицы по параллелям
                $this->print_ages_tables($programm, $conds);
                // после того, как вывели всю таблицу по периодам -
                // выведем предметы по выбору
                $this->print_agenum_table($programm->id, 0, $this->dof->get_string('optional_pitems', 'programmitems'), $conds);
            }
        }
    }

    /** Вывести список всех учебных параллелей
     * 
     * @return null
     * @param object $programm - учебная программа объект в таблице programms
     */
    private function print_ages_tables($programm, $conds = null, $key = 0)
    {
        for ( $i = 1; $i <= $programm->agenums; $i++ )
        {// для каждого периода программы извлекаем из базы список предметом
            $this->print_agenum_table($programm->id, $i, $this->dof->get_string('parallel', 'programmitems') . ' ' . $i, $conds, $key);
        }
    }

    /** Распечатать заголовок для страницы просмотра списка предметов программы
     * @param string $title - название учебной программы
     * @return string отформатированный заголовок со всеми html-тегами
     */
    private function get_programm_title($title)
    {
        return '<h2 align="center">' .
                $this->dof->get_string('pitems_list_for_program', 'programmitems') . ' &quot;' .
                $title . '&quot;</h2>';
    }

    /** Распечатать таблицу со списком предметов по одному периоду
     * добавленные функции "Просмотр списка су3ществующих метадисциплин"-для этого должен быть задан
     * $conds->metaprogrammitemid=0
     * Так же распечатывает таблицу метадисциплин для заданной параллели и отображает иконки "создать" и
     * "создать и редактировать",для этого должен быть передан параметр $key=1
     *
     * @return null
     * @param int $programmid - id программы, для которой рисуется таблицы (таблица programms)
     * @param int $agenum - относительный номер периода внутри программы
     * @param int $agenum - массив условий выборки
     * @param int $key - ключ управления логикой функции
     * @param string $title - заголовок таблицы
     */
    private function print_agenum_table($programmid, $agenum, $title, $conds = null, $key = 0)
    {
        $meta = false; //ключ,если=ИСТИНА,то отображает таблицу с метадисциплинами(различие в отображаемых иконках и ссылках)
        if ( isset($conds->metaprogrammitemid) )
        {
            if ( $conds->metaprogrammitemid == 0 )
            {
                $meta = true;
            }
        }
        // создадим объект таблицы
        $table = new stdClass();

        // cоздаем заголовок таблицы 
        $table->head       = array($title, $this->dof->get_string('actions', 'programmitems'));
        $table->size       = array(null, '100px');
        $table->align      = array('left', 'center');
        $table->width      = '60%';
        $table->tablealign = 'center';
        // извлекаем все изучаемые предметы для каждой параллели
        if ( isset($conds) AND ! is_null($conds) )
        {// если есть параметры фильтрации - используем sql
            $sqlconds = fullclone($conds);
            $sqlconds->status = array('suspend', 'active');
            $sqlconds->agenum = $agenum;
            unset($sqlconds->departmentid);
            // @TODO заменить стандартным вызовом storage
            $pitems = $this->dof->storage('programmitems')->get_listing($sqlconds);
        } else
        {// обычная выборка
            $pitems = $this->dof->storage('programmitems')->
                    get_pitems_list($programmid, $agenum, array('active', 'suspend'));
        }
        // для ссылок вне плагина
        $conds = (array) $conds;
        $outconds = array();
        $outconds['departmentid'] = 0;
        if ( isset($conds['departmentid']) )
        {
            $outconds['departmentid'] = $conds['departmentid'];
        }
        // убираем статус из запроса, потому что это массив, и его нельзя передать в GET 
        unset($conds['status']);
        if ( $pitems )
        {// если есть предметы - то составляем таблицу
            foreach ( $pitems as $pitem )
            {// заполняем ячейки по одной
                $rowclass = '';
                $dep_match = true;

                $deptname = $this->dof->storage('departments')->get_field($pitem->departmentid, 'name');
                if ( $meta == false )
                {
                    $pitemdata = '<a href="' . $this->dof->url_im('programmitems', '/view.php?pitemid=' . $pitem->id, $outconds) .
                            '">' . $pitem->name . '</a> [' . $deptname . ']';
                } else
                {
                    $pitemdata = '<a href="' . $this->dof->url_im('programmitems', '/view.php?meta=1&pitemid=' . $pitem->id, $outconds) .
                            '">' . $pitem->name . '</a> [' . $deptname . ']';
                }

                if ( $conds['departmentid'] != $pitem->departmentid AND $conds['departmentid'] != 0 )
                {// предметы из другого подразделения неактивны
                    $pitemdata = $pitem->name . ' [' . $deptname . ']';
                    $rowclass = 'pitem_inactive';
                    $dep_match = false;
                }

                // создаем панель инструментов из иконок
                $actions = '';

                if ( $dep_match )
                {// предмет и программа из одного подразделения
                    if ( $key == 1 )//Если управляющий ключ=1 -нарисовать таблицу метадисциплин с ссылками "Создать" и "Создать и редактировать"
                    {
                        if ( $this->dof->storage('programmitems')->is_access('edit', $pitem->id) )//Есть ли право на редактирование
                            if ( $this->dof->storage('programmitems')->check_limit_metapitems($outconds['departmentid']) )//Не достигнут ли лимит
                            {
                                $actions.= '<a style="color:black;" href="' . $this->dof->url_im('programmitems', '/choosemeta.php?departmentid=' .
                                                $outconds['departmentid'] . '&programmid=' .
                                                $programmid . '&meta=1&agenum=' . $conds['agenum'] . '&metaprogrammitemid=' . $pitem->id) . '">';

                                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/create.png') . '"
                            alt=  "' . $this->dof->get_string('create', 'programmitems') . '"
                            title="' . $this->dof->get_string('create', 'programmitems') . '" /></a>&nbsp;';

                                $actions.= '<a style="color:black;" href="' . $this->dof->url_im('programmitems', '/choosemeta.php?departmentid=' .
                                                required_param('departmentid', PARAM_TEXT) . '&programmid=' .
                                                $programmid . '&meta=1&redirectedit=1&agenum=' . $conds['agenum'] . '&metaprogrammitemid=' . $pitem->id) . '">';

                                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/create_and_edit.png') . '"
                            alt=  "' . $this->dof->get_string('create_and_edit', 'programmitems') . '"
                            title="' . $this->dof->get_string('create_and_edit', 'programmitems') . '" /></a>&nbsp;';

                                $table->rowclasses[] = $rowclass;
                                $table->data[] = array($pitemdata, $actions);
                                continue;
                            }
                    }

                    // просмотр
                    if ( $this->dof->storage('programmitems')->is_access('view', $pitem->id) )
                    {// если есть право на просмотр
                        if ( $meta == false )
                        {
                            $actions .= '<a href="' . $this->dof->url_im('programmitems', '/view.php?pitemid=' . $pitem->id, $outconds) .
                                    '" id="view_pitem_' . $pitem->id . '">';
                        } else
                        {
                            $actions .= '<a href="' . $this->dof->url_im('programmitems', '/view.php?meta=1&pitemid=' . $pitem->id, $outconds) .
                                    '" id="view_pitem_' . $pitem->id . '">';
                        }
                        $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/view.png') . '"
                        alt=  "' . $this->dof->get_string('view', 'programmitems') . '"
                        title="' . $this->dof->get_string('view', 'programmitems') . '" /></a>&nbsp;';
                    }
                    // редактирование
                    if ( $this->dof->storage('programmitems')->is_access('edit', $pitem->id) )
                    {// если есть право на редактирование
                        if ( $meta == false )
                        {
                            $actions .= '<a href="' . $this->dof->url_im('programmitems', '/edit.php?&pitemid=' . $pitem->id, $outconds) .
                                    '" id="edit_pitem_' . $pitem->id . '">';
                        } else
                        {
                            $actions .= '<a href="' . $this->dof->url_im('programmitems', '/edit.php?meta=1&pitemid=' . $pitem->id, $outconds) .
                                    '" id="edit_pitem_' . $pitem->id . '">';
                        }
                        $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/edit.png') . '"
                        alt=  "' . $this->dof->get_string('edit', 'programmitems') . '"
                        title="' . $this->dof->get_string('edit', 'programmitems') . '" /></a>&nbsp;';
                    }
                    // планирование
                    if ( $meta == false )
                    {
                        if ( $this->dof->im('plans')->is_access('viewthemeplan', $pitem->id, null, 'programmitems') )
                        {// если есть право на просмотр планирования
                            $actions.= '<a href="' . $this->dof->url_im('plans', '/themeplan/viewthemeplan.php?linktype=programmitems&linkid=' . $pitem->id, $outconds) .
                                    '" id="view_plan_on_pitem_' . $pitem->id . '">';
                            $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/plan.png') . '"
                        alt=  "' . $this->dof->get_string('view_plancstream', 'programmitems') . '"
                        title="' . $this->dof->get_string('view_plancstream', 'programmitems') . '" /></a>&nbsp;';
                        }

                        // подписки
                        if ( $this->dof->storage('cpassed')->is_access('view') )
                        {// проверка прав
                            $actions .= '<a href="' . $this->dof->url_im('cpassed', '/list.php?programmitemid=' . $pitem->id, $outconds) .
                                    '" id="view_cpassed_for_pitem_' . $pitem->id . '">';
                            $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/cpassed.png') . '"
                        alt=  "' . $this->dof->get_string('view_cpassed', 'programmitems') . '"
                        title="' . $this->dof->get_string('view_cpassed', 'programmitems') . '" /></a>&nbsp;';
                        }
                        // создание потока

                        if ( $this->dof->storage('cstreams')->is_access('create') )
                        {// проверка прав
                            $actions .= '<a href="' . $this->dof->url_im('cstreams', '/edit.php?programmitemid=' . $pitem->id
                                            . '&programmid=' . $pitem->programmid, $outconds) . '" id="create_cstreams_for_pitem_' . $pitem->id . '">';
                            $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/create_cstreams.png') . '"
                        alt=  "' . $this->dof->get_string('create_cstream_for_programmiteam', 'programmitems') . '"
                        title="' . $this->dof->get_string('create_cstream_for_programmiteam', 'programmitems') . '" /></a>&nbsp;';
                        }
                        // подписка учителей

                        if ( $this->dof->storage('teachers')->is_access('create') )
                        {// проверка прав
                            $actions .= '<a href="' . $this->dof->url_im('employees', '/view_programmitem.php?id=' . $pitem->id, $outconds) .
                                    '" id="assign_teachers_on_pitem_' . $pitem->id . '">';
                            $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/assign_teachers.png') . '"
                        alt=  "' . $this->dof->get_string('assign_teachers_for_programmiteam', 'programmitems') . '"
                        title="' . $this->dof->get_string('assign_teachers_for_programmiteam', 'programmitems') . '" /></a>&nbsp;';
                        }
                    }
                }

                // экспорт
                /*
                 * @todo разобраться с правильной подстановкой параметров
                  if ( $this->dof->im('cpassed')->is_access('viewall') )
                  {// @todo разобраться с правами на экспорт ведомости
                  $actions .= '<a href="'.$this->dof->url_im('cpassed', '/list.php?export=odf">');
                  $actions .= '<img src="'.$this->dof->url_im('programmitems', '/icons/vedomost.png').'"
                  alt=  "'.$this->dof->get_string('get_exam_roll', 'programmitems').'"
                  title="'.$this->dof->get_string('get_exam_roll', 'programmitems').'" /></a>&nbsp;';
                  } */

                $table->rowclasses[] = $rowclass;
                $table->data[] = array($pitemdata, $actions);
            }
        }
        if ( $key == 1 )//Если выводим список метадисциплин для создания дисциплин
        {
            $this->dof->modlib('widgets')->print_table($table);
            print('<br/>');

            return true;
        }
        // выводим ссылку на создание нового предмета
        if ( $meta == false )
        {
            if ( $this->dof->storage('config')->get_limitobject('programmitems', $outconds['departmentid']) )// лимит
            {
                $createlink = '<div align="center"><a href="' .
                        $this->dof->url_im('programmitems', '/edit.php?agenum=' . $agenum . '&programmid=' . $programmid, $outconds) .
                        '" id="add_pitem_for_agenum_' . $agenum . '">' .
                        $this->dof->get_string('addpitem', 'programmitems') . '</a>';
            } else
            {

                $createlink = '<div align="center"><span style="color:silver;">' . $this->dof->get_string('addpitem', 'programmitems') .
                        ' <br>(' . $this->dof->get_string('limit_message', 'programmitems') . ')</span>';
            }
        } else
        {
            if ( $this->dof->storage('programmitems')->check_limit_metapitems($outconds['departmentid']) )
            {
                $createlink = '<div align="center"><a href="' .
                        $this->dof->url_im('programmitems', '/edit.php?meta=1&agenum=' . $agenum . '&programmid=' . $programmid, $outconds) .
                        '" id="add_pitem_for_agenum_' . $agenum . '">' .
                        $this->dof->get_string('metapitem_add', 'programmitems') . '</a>';
            } else
            {
                $createlink = '<div align="center"><span style="color:silver;">' . $this->dof->get_string('addpitem', 'programmitems') .
                        ' <br>(' . $this->dof->get_string('limit_message_metapitems', 'programmitems') . ')</span>';
            }
        }

        if ( $this->dof->storage('config')->get_limitobject('programmitems', $outconds['departmentid']) )
        {
            if ( $meta == false )
            {
                $createlink .= '<div align="center"><a href="' .
                        $this->dof->url_im('programmitems', '/choosemeta.php?agenum=' . $agenum . '&programmid=' . $programmid, $outconds) .
                        '" id="add_pitem_for_agenum_' . $agenum . ' ">' .
                        $this->dof->get_string('metapitem_add_from', 'programmitems') . '</a>';
            }
        } else
        {
            $createlink = '<div align="center"><span style="color:silver;">' . $this->dof->get_string('addpitem', 'programmitems') .
                    ' <br>(' . $this->dof->get_string('limit_message', 'programmitems') . ')</span>';
        }

        if ( $meta == false )
        {
            if ( $pitems AND $agenum )
            {// если есть предметы - то для них можно создавать учебные процессы
                // лимит
                if ( $this->dof->storage('config')->get_limitobject('cstreams', $outconds['departmentid']) )
                {
                    $createlink .= '<br/><a href="' .
                            $this->dof->url_im('cstreams', '/create_cstreams_forprogramm.php?agenum=' .
                                    $agenum . '&programmid=' . $programmid, $outconds) .
                            '" id="create_cstreams_for_agenum_' . $agenum . '">' .
                            $this->dof->get_string('create_cstreams_for_this', 'programmitems') . '</a>';
                } else
                {
                    $createlink .= '<br><span style="color:silver;">' . $this->dof->get_string('create_cstreams_for_this', 'programmitems') .
                            ' <br>(' . $this->dof->get_string('limit_message', 'programmitems') . ')</span>';
                }
            }

            if ( $this->dof->im('cstreams')->is_access('viewcurriculum') AND $agenum )
            {// если есть право на просмотр учебного плана
                $createlink .= '<br/><a href="' . $this->dof->url_im('cstreams', '/by_groups.php?programmid=' . $programmid . '&agenum=' . $agenum, $outconds) .
                        '" id="view_curriculum_pr' . $programmid . '_age' . $agenum . '">' .
                        $this->dof->get_string('participants_cstreams', 'programmitems') . '</a></div>';
            }
        }
        $table->data[] = array($createlink, '');
        // выводим на экран таблицу со всем содержимым
        $this->dof->modlib('widgets')->print_table($table);
        print('<br/>');
        @ob_flush();
    }

    /**
     * Возвращает объект приказа
     *
     * @param string $code - код приказа
     * @param integer  $id - id предмета
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        require_once($this->dof->plugin_path('im', $this->code(), '/orders/change_status/init.php'));
        switch ( $code )
        {
            case 'change_status':
                $order = new dof_im_programmitems_order_change_status($this->dof);
                if ( !is_null($id) )
                {// нам передали id, загрузим приказ
                    if ( !$order->load($id) )
                    {// Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
                break;
        }
    }

    /** Распечатать таблицу с краткой информацией о дисциплине: 
     * Подразделение (ссылка), программа (ссылка), дисциплина (ссылка), количество часов.
     * 
     * @return null|string
     * @param int $programmitemid - id предмета по которому выводится информация
     * @param bool $onlyhtml[optional] - только получить html-код таблицы
     */
    public function print_short_info_table($programmitemid, $onlyhtml = false)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( $programmitem = $this->dof->storage($this->code())->get($programmitemid) )
        {// создадим ссылку для просмотра
            $pitemname = '<a href="' . $this->dof->url_im('programmitems', '/view.php?pitemid=' . $programmitemid, $addvars) .
                    '">' . $programmitem->name . '</a>';
            if ( !$department = $this->dof->storage('departments')->get($programmitem->departmentid) )
            {// подразделение не указано
                $department = '';
            } else
            {// создадим ссылку для просмотра
                $department = '<a href="' . $this->dof->url_im('departments', '/view.php?departmentid=' . $department->id) .
                        '">' . $department->name . '</a>';
            }
            if ( !$programm = $this->dof->storage('programms')->get($programmitem->programmid) )
            {// Программа не указана
                $programm = '';
            } else
            {// создадим ссылку для просмотра
                $programm = '<a href="' . $this->dof->url_im('programms', '/view.php?programmid=' . $programm->id, $addvars) .
                        '">' . $programm->name . '</a>';
            }
        } else
        {// не найден предмет для которого собрались выводить таблицу
            $department = '';
            $programm = '';
            $pitemname = '';
            $programmitem = new stdClass();
            $programmitem->hours = '';
            $programmitem->hourstheory = '';
            $programmitem->hourspractice = '';
            $programmitem->status = '';
        }
        $table = new stdClass();
        $table->data = array();
        $table->data[] = array('<b>' . $this->dof->get_string('department', $this->code()) . '</b>', $department);
        $table->data[] = array('<b>' . $this->dof->get_string('program', $this->code()) . '</b>', $programm);
        $table->data[] = array('<b>' . $this->dof->get_string('pitem', $this->code()) . '</b>', $pitemname);
        // выводим количество часов
        $table->data[] = array('<b>' .
            $this->dof->get_string('hours_all', $this->code()) . ' = ' .
            $this->dof->get_string('hours_theory', $this->code()) . ' + ' .
            $this->dof->get_string('hours_practice', $this->code()),
            $programmitem->hours . ' = ' . $programmitem->hourstheory . ' + ' .
            $programmitem->hourspractice . '</b>');

        $table->data[] = array('<b>' . $this->dof->get_string('status', $this->code()) . '</b>',
            $this->dof->get_string('status:' . $programmitem->status, 'programmitems', null, 'workflow'));

        // распечатываем или отображаем таблицу
        return $this->dof->modlib('widgets')->print_table($table, $onlyhtml);
    }

    /** Распечатать краткую таблицу со списком предметов при поиске 
     * предметов на странице просмотра предметов по параллелям
     * 
     * @return null - эта функция не возвращает значений
     * @param array $programmitems - массив записей из таблицы programmitems
     */
    public function print_search_agenum_table($programmitems)
    {
        if ( !is_array($programmitems) OR empty($programmitems) )
        {// неправильный формат исходных данных
            return null;
        }
        $table = new stdClass();
        // задаем заголовок таблицы
        $table->head = array($this->dof->get_string('subject', $this->code()),
            $this->dof->modlib('ig')->igs('actions'));
        // создаем данные для таблицы
        foreach ( $programmitems as $pitem )
        {
            // информация о подразделении
            $deptname = $this->dof->storage('departments')->get_field($pitem->departmentid, 'name');
            $pitemdata = '<a href="' . $this->dof->url_im('programmitems', '/view.php?pitemid=' . $pitem->id) .
                    '">' . $pitem->name . '</a> [' . $deptname . ']';
            // @todo вынести составление списка действий в отдельную функцию
            // создаем панель инструментов из иконок
            $actions = '';
            // просмотр
            if ( $this->dof->storage('programmitems')->is_access('view', $pitem->id) )
            {// если есть право на просмотр
                $actions .= '<a href="' . $this->dof->url_im('programmitems', '/view.php?pitemid=' . $pitem->id) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/view.png') . '" 
                alt=  "' . $this->dof->get_string('view', 'programmitems') . '" 
                title="' . $this->dof->get_string('view', 'programmitems') . '" /></a>&nbsp;';
            }
            // редактирование
            if ( $this->dof->storage('programmitems')->is_access('edit', $pitem->id) )
            {// если есть право на редактирование
                $actions .= '<a href="' . $this->dof->url_im('programmitems', '/edit.php?pitemid=' . $pitem->id) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/edit.png') . '"
                alt=  "' . $this->dof->get_string('edit', 'programmitems') . '" 
                title="' . $this->dof->get_string('edit', 'programmitems') . '" /></a>&nbsp;';
            }
            // планирование
            if ( $this->dof->im('plans')->is_access('viewthemeplan', $obj->id, null, 'programmitems') )
            {// если есть право на просмотр планирования
                $actions.= '<a href="' . $this->dof->url_im('plans', '/themeplan/viewthemeplan.php?linktype=programmitems&linkid=' . $obj->id) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/plan.png') . '"
                    alt=  "' . $this->dof->get_string('view_plancstream', 'programmitems') . '" 
                    title="' . $this->dof->get_string('view_plancstream', 'programmitems') . '" /></a>&nbsp;';
            }
            // подписки
            if ( $this->dof->storage('cpassed')->is_access('view') )
            {// если есть право на просмотр списка подписок
                $actions .= '<a href="' . $this->dof->url_im('cpassed', '/list.php?programmitemid=' . $pitem->id) . '">';
                $actions .= '<img src="' . $this->dof->url_im('programmitems', '/icons/cpassed.png') . '"
                alt=  "' . $this->dof->get_string('view_cpassed', 'programmitems') . '" 
                title="' . $this->dof->get_string('view_cpassed', 'programmitems') . '" /></a>&nbsp;';
            }
            $table->data[] = array($pitemdata, $actions);
        }
        $this->dof->modlib('widgets')->print_table($table);
        print('<br/>');
    }

    /** Получить html-ссылку на просмотр дисциплины
     * @param int id - id дисциплины в таблице programmitems
     * @param bool $withcode - добавлять или не добавлять код в конце
     * 
     * @return string html-строка со ссылкой на дисциплину или пустая строка в случае ошибки
     */
    public function get_html_link($id, $withcode = false, $addvars = null)
    {
        if ( !$addvars )
        {
            $addvars = array();
        }
        if ( !$name = $this->dof->storage('programmitems')->get_field($id, 'name') )
        {
            return '';
        }
        if ( $withcode )
        {
            $code = $this->dof->storage('programmitems')->get_field($id, 'code');
            $name = $name . ' [' . $code . ']';
        }
        
        $is_access = $this->dof->storage('programmitems')->is_access('view', $id);
        if ( $is_access )
        {// Есть доступ к просмотру дисциплины
            return '<a href="'.$this->dof->url_im($this->code(),
                '/view.php', array_merge($addvars, array('pitemid' => $id))).'">'.$name.'</a>';
        } else 
        {// Доступа нет
            return '<span>'.$name.'</span>';
        }
        
    }
}