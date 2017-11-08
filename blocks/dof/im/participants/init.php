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
 * Интерфейс управления участниками учебного процесса. Класс плагина.
 *
 * @package    im
 * @subpackage participants
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_participants implements dof_plugin_im
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************
    
    /** 
     * Метод, реализующий инсталяцию плагина в систему
     * 
     * Создает или модифицирует существующие таблицы в БД
     * и заполняет их начальными значениями
     * 
     * @return boolean
     */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    
    /** 
     * Метод, реализующий обновление плагина в системе.
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $old_version - Версия установленного в системе плагина
     * 
     * @return boolean
     */
    public function upgrade($oldversion)
    {
        // Обновление прав доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2016062300;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium_bcd';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, которым этот плагин соответствует
     * 
     * @return string
     */
    public function compat()
    {
        return 'angelfish';
    }
    
    /** 
     * Возвращает тип плагина
     * 
     * @return string 
     */
    public function type()
    {
        return 'im';
    }
    
    /** 
     * Возвращает короткое имя плагина
     * 
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     */
    public function code()
    {
        return 'participants';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
                'im' => [
                                'persons'      => 2016053100
                ],
                'modlib' => [
                                'ig'           => 2016060900,
                                'nvg'          => 2016050400,
                                'widgets'      => 2016050500
                ],
                'storage' => [
                                'ages'            => 2014050502,
                                'learninghistory' => 2012042500,
                                'cpassed'         => 2016011400,
                                'learningplan'    => 2014090500,
                                'config'          => 2012042500,
                                'departments'     => 2016012100,
                                'persons'         => 2016060900,
                                'contracts'       => 2016060900,
                                'programms'       => 2016042800,
                                'agroups'         => 2016051200,
                                'acl'             => 2012042500,
                                'programmsbcs'    => 2016052600
                ],
                'workflow' => [
                                'contracts'      => 2015020200,
                                'programmsbcs'   => 2011082200,
                                'persons'        => 2015012000,
                                'agroups'        => 2011082200,
                                'ages'           => 2011082200,
                                'programms'      => 2016042600
                    
                ]
        ];
    }
    
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * 
     * @see dof_modlib_base_plugin::is_setup_possible()
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

    /**
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     *
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     *
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return [
                'im' => [
                                'persons'      => 2016053100
                ],
                'modlib' => [
                                'ig'           => 2016060900,
                                'nvg'          => 2016050400,
                                'widgets'      => 2016050500
                ],
                'storage' => [
                                'ages'            => 2014050502,
                                'learninghistory' => 2012042500,
                                'cpassed'         => 2016011400,
                                'learningplan'    => 2014090500,
                                'config'          => 2012042500,
                                'departments'     => 2016012100,
                                'persons'         => 2016060900,
                                'contracts'       => 2016060900,
                                'programms'       => 2016042800,
                                'agroups'         => 2016051200,
                                'acl'             => 2012042500,
                                'programmsbcs'    => 2016052600
                ],
                'workflow' => [
                                'contracts'      => 2015020200,
                                'programmsbcs'   => 2011082200,
                                'persons'        => 2015012000,
                                'agroups'        => 2011082200,
                                'ages'           => 2011082200,
                                'programms'      => 2016042600
                    
                ]
        ];
    }
    
    /** 
     * Список обрабатываемых плагином событий 
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     */
    public function list_catch_events()
    {
        return [
        ];
    }
    
    /** 
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
       // Запуск каждые пол часа
       return 1800;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function is_access($do, $objid = null, $userid = null)
    {
        if ( $this->dof->is_access('datamanage') OR 
             $this->dof->is_access('admin') OR 
             $this->dof->is_access('manage') 
           )
        {// Открыть доступ для менеджеров
            return true;
        } 
              
        // Получаем ID персоны, с которой связан данный пользователь 
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // Формируем параметры для проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid);

        switch ( $do )
        {// Определяем дополнительные параметры в зависимости от запрашиваемого права
            default:
                break;
        }
        // Производим проверку
        if ( $this->acl_check_access_paramenrs($acldata) )
        {// Право есть
            return true;
        } 
        return false;
    }
    
    /** 
	 * Требует наличия полномочия на совершение действий
	 * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта, 
     *                     по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя Moodle, полномочия которого проверяются
     * 
     * @return bool 
     *              true - можно выполнить указанное действие по 
     *                     отношению к выбранному объекту
     *              false - доступ запрещен
     */
    public function require_access($do, $objid = null, $userid = null)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "participants/{$do} (block/dof/im/participants: {$do})";
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
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function catch_event($gentype, $gencode, $eventcode, $intvar, $mixedvar)
    {
        return true;
    }
    
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (
     *              1 - только срочные, 
     *              2 - нормальный режим, 
     *              3 - ресурсоемкие операции
     *        )
     * @param int $messages - количество отображаемых сообщений (
     *              0 - не выводить,
     *              1 - статистика,
     *              2 - индикатор, 
     *              3 - детальная диагностика
     *        )
     *        
     * @return bool - true в случае выполнения без ошибок
     */
    public function cron($loan,$messages)
    {
        $result = true;
        if ( $loan == 3 )
        {// Генерация отчета должна запускаться только во время минимальной активности
            $result = ( $result && $this->dof->storage('reports')->generate_reports($this->type(), $this->code()));
        }
        return $result;
    }
    
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
     * @param string $code - код задания
     * @param int $intvar - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * 
     * @return bool - true в случае выполнения без ошибок
     */
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }
    
    /**
     * Получить настройки для плагина
     * 
     * @param unknown $code
     * 
     * @return array - Массив настроек плагина
     */
    public function config_default($code = null)
    {
        // Плагин включен и используется
        $config = [];
        
        return $config;
    }
    
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    
    /** 
     * Возвращает текст для отображения в блоке на странице dof
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string - html-код содержимого блока
     */
    public function get_block($name, $id = 1)
    {
        $rez = '';
        switch ($name)
        {
            default:  
                break;
        }
        return $rez;
    }
    
    /** 
     * Возвращает html-код, который отображается внутри секции
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * 
     * @return string  - html-код содержимого секции секции
     */
    public function get_section($name, $id = 0)
    {
        $rez = '';
        
        // Инициализируем генератор HTML
        $this->dof->modlib('widgets')->html_writer();
        
        switch ($name)
        {
            default:  
                break;  
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
        return "<a href='{$this->dof->url_im('participants','/index.php')}'>"
                    .$this->dof->get_string('page_main_name')."</a>";
    }

    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************    
    
    /** 
     * Получить список параметров для фунции has_hight()
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->personid     = $personid;
        $result->departmentid = $depid;
        $result->objectid     = $objectid;
        
        if ( is_null($depid) )
        {// Подразделение не задано - ищем в GET/POST
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        if ( ! $objectid )
        {// Если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** 
     * Проверить права через плагин acl.
     * 
     * Функция вынесена сюда, чтобы постоянно не писать 
     * длинный вызов и не перечислять все аргументы
     * 
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     * 
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right(
                            $acldata->plugintype, 
                            $acldata->plugincode, 
                            $acldata->code, 
                            $acldata->personid, 
                            $acldata->departmentid, 
                            $acldata->objectid
        );
    }    
      
    /** 
     * Сформировать права доступа для интерфейса
     * 
     * @return array - Массив с данными по правам доступа
     */
    public function acldefault()
    {
        $a = [];
        
        // Право доступа к плагину
        $a['interface_base'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // Право доступа к панели управления слушателями
        $a['interface_students'] = [
            'roles' => [
                'manager',
                'methodist'
            ]
        ];
        // Право доступа к панели управления сотрудниками
        $a['interface_eagreements'] = [
            'roles' => [
                'manager'
            ]
        ];
        // Право доступа к панели управления персонами
        $a['interface_persons'] = [
            'roles' => [
                'manager'
            ]
        ];
        // Право доступа к панели управления метаконтрактами
        $a['interface_metacontracts'] = [
            'roles' => [
                'manager'
            ]
        ];
        // Право доступа к панели управления импортом
        $a['interface_import'] = [
            'roles' => [
                'manager'
            ]
        ];
        
        return $a;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** 
     * Получить URL к собственным файлам плагина
     * 
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     *                                 начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * 
     * @return string - путь к папке с плагином 
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    
    /** 
     * Сформировать вкладки перехода между интрефейсами
     * 
     * @param string $tabname - Название вкладки
     * @param array $addvars - Массив GET-параметорв
     * 
     * @return string - HTML-код вкладок
     */
    public function render_tabs($tabname, $addvars = [])
    {
        // Вкладки
        $tabs = [];

        // Главная страница
        $link = $this->dof->url_im($this->code(), '/index.php', $addvars);
        $text = $this->dof->get_string('tab_main', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('main', $link, $text, null, false);

        if ( $this->is_access('interface_students') )
        {// Есть доступ к просмотру интерфейса управления слушателями
            $link = $this->dof->url_im($this->code(), '/students.php', $addvars);
            $text = $this->dof->get_string('tab_students', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('students', $link, $text, null, false);
        }
        /* Нереализованные интерфейсы
        if ( $this->is_access('interface_eagreements') )
        {// Есть доступ к просмотру интерфейса управления сотрудниками
            $addvars['tab'] = 'eagreements';
            $link = $this->dof->url_im($this->code(), '/eagreements.php', $addvars);
            $text = $this->dof->get_string('tab_eagreements', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('eagreements', $link, $text, null, false);
        }
        if ( $this->is_access('interface_persons') )
        {// Есть доступ к просмотру интерфейса управления персонами
            $addvars['tab'] = 'persons';
            $link = $this->dof->url_im($this->code(), '/persons.php', $addvars);
            $text = $this->dof->get_string('tab_persons', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('persons', $link, $text, null, false);
        }
        if ( $this->is_access('interface_metacontracts') )
        {// Есть доступ к просмотру интерфейса управления метаконтрактами
            $addvars['tab'] = 'metacontracts';
            $link = $this->dof->url_im($this->code(), '/metacontracts.php', $addvars);
            $text = $this->dof->get_string('tab_metacontracts', $this->code());
            $tabs[] = $this->dof->modlib('widgets')->create_tab('metacontracts', $link, $text, null, false);
        }*/
        
        // Формирование блока вкладок
        return $this->dof->modlib('widgets')->print_tabs($tabs, $tabname, null, null, true);
    }
    
    /**
     * Отображение уведомлений о результатах действий пользователей
     * 
     * Формирует стек уведомлений на основе имеющихся GET-параметров
     *
     * @return void
     */
    public function messages()
    {
        // ПОЛЬЗОВАТЕЛЬСКАЯ ЧАСТЬ
        // Сообщение о создании подписки на программу
        if ( optional_param('bccreatesussess', 0, PARAM_INT) )
        {
            $this->dof->messages->add(
                $this->dof->get_string('message_students_programmbs_create_success', $this->code()),
                'message'
            );
        }
    }
    
    /**
     * Сформировать общие GET параметры для плагина
     *
     * @param array &$addvars - Ссылка на массив GET-параметров
     *
     * @return void
     */
    public function get_plugin_addvars(&$addvars)
    {
        // Текущее подразделение
        if ( ! isset($addvars['departmentid']) )
        {// Не установлено текущее подразделение
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        // Лимит записей на странице
        $baselimitnum = (int)$this->dof->modlib('widgets')->get_limitnum_bydefault($addvars['departmentid']);
        $limitnum = optional_param('limitnum', $baselimitnum, PARAM_INT);
        if ( $limitnum < 1 )
        {// Нормализация
            $limitnum = $baselimitnum;
        }
        $addvars['limitnum'] = $limitnum;
    }
    
    /**
     * Получить массив идентиифкаторов подписок на программы с учетом фильтрации
     * 
     * @param array $filter - Фильтр, массив объектов фильтров по типам(user, programm, agroup)
     *                      ['user']->lastname
     *                      ['user']->firstname
     *                      ['agroup']->name
     *                      ...
     * @param int|array $departmentid - Подразделения, по которым требуется производить поиск                   
     * @param array $options - Опции формирования подписок
     *                      ['sort'] => Сортировка
     *                      ['dir'] => Направление сортировки
     * @return array - Массив подписок
     */
    public function get_programmsbcs_id_by_filter($filter, $departmentid = 0, $options = [])
    {
        $programmsbcsparams = [];
        
        if ( isset($filter['user']) )
        {// Фильтр пользователей определен
            // Получение списка пользователей на основе фильтра
            $users = (array)$this->dof->storage('persons')->
                get_list_extendedsearch($filter['user']);
            if ( empty($users) )
            {// Пользователи в соответствии с фильтром не найдены
                return [];
            }
            // Получение списка идентификаторов договоров на основе персон
            $contracts = $this->dof->storage('contracts')->
                get_records(['studentid' => array_keys($users)],'', 'id');
            if ( empty($contracts) )
            {// Договора в соответствии с фильтром не найдены
                return [];
            }
            $programmsbcsparams['contractid'] = array_keys($contracts);
        }
        
        if ( isset($filter['programm']) )
        {// Фильтр программы определен
            // Получение списка программ на основе данных
            $programms = (array)$this->dof->storage('programms')->
                get_programms_by_filter($filter['programm'], ['returnids' => true]);
            if ( empty($programms) )
            {// Программы в соответствии с фильтром не найдены
                return [];
            }
            $programmsbcsparams['programmid'] = array_keys($programms);
        }
        
        if ( isset($filter['agroup']) )
        {// Фильтр групп определен
            if ( isset($filter['agroup']->agenum) )
            {// Передан номер параллели
                $programmsbcsparams['agenum'] = (int)$filter['agroup']->agenum;
            }
            // Фильтрация группы
            $agroupsfilter = (array)$filter['agroup'];
            unset($agroupsfilter['agenum']);
            if ( $agroupsfilter )
            {// Указана фильтрация по группам
                $agroups = (array)$this->dof->storage('agroups')->
                    get_agroups_by_filter($filter['agroup'], ['returnids' => true]);
                if ( empty($agroups) )
                {// Группы в соответствии с фильтром не найдены
                    return [];
                }
                $programmsbcsparams['agroupid'] = array_keys($agroups);
            }
        }
        
        // Получить массив подписок
        $programmsbcsparams['status'] = array_keys((array)$this->dof->workflow('programmsbcs')->get_meta_list('real'));
        if ( ! empty($departmentid) )
        {// Указана фильтрация по подразделениям
            $programmsbcsparams['departmentid'] = $departmentid;
        }
        
        return array_keys($this->dof->storage('programmsbcs')->
            get_records($programmsbcsparams, '', 'id'));
    }
    
    /**
     * Получить HTML-код таблицы подписок
     *
     * @param array $programmsbcs - Массив идентификаторов подписок на программы
     * @param array $options - Опции формирования таблицы подписок
     *                      ['limitfrom'] => Смещение
     *                      ['limitnum'] => Число записей
     *                      
     * @return string - HTML-код таблицы подписок на программы
     */
    public function get_programmsbcs_table($programmsbcs, $options = [])
    {
        // ПОЛУЧЕНИЕ БАЗОВЫХ ПАРАМЕТРОВ
        $currentperson = $this->dof->storage('persons')->get_bu();
        $currenturl = $this->dof->modlib('nvg')->get_url()->out(false);
        $html = '';
        
        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        if ( ! isset($options['addvars']) )
        {
            $options['addvars'] = [];
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {
            $options['addvars']['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        // Пагинация
        if ( ! isset($options['limitnum']) || $options['limitnum'] < 1 )
        {
            $options['limitnum'] = (int)$this->dof->modlib('widgets')->
            get_limitnum_bydefault($options['addvars']['departmentid']);
        }
        if ( ! isset($options['limitfrom']) || $options['limitfrom'] < 0 )
        {
            $options['limitfrom'] = 1;
        }
        // Сортировка
        if ( ! isset($options['addvars']['sort']) )
        {
            $options['addvars']['sort'] = optional_param('sort', '', PARAM_TEXT);
        }
        if ( ! isset($options['addvars']['dir']) )
        {
            $options['addvars']['dir'] = optional_param('dir', 'ASC', PARAM_TEXT);
        }
        if ( $options['addvars']['dir'] != 'DESC' )
        {
            $options['addvars']['dir'] != 'ASC';
        }
        
        // ПОСТРОЕНИЕ ТАБЛИЦЫ
        // Получение данных для формирования таблицы
        $tabledata = $this->prepare_programmsbcs_table_data($programmsbcs, $options);
        if ( empty($tabledata) )
        {// Данные не определены
            return $html;
        }

        // Рендер таблицы
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->cellpadding = 0;
        $table->cellspacing = 0;
        $table->head = $this->get_programmsbcs_table_head($options);
        $table->data = [];
        // GET-параметры для ссылок таблицы
        $linkvars = ['departmentid' => $options['addvars']['departmentid']];
        // Формирование строк таблицы
        foreach ( $tabledata as $item )
        {
            $data = [];
            
            $data['actions'] = '';
            $access = $this->dof->storage('programmsbcs')->is_access('view', (int)$item->id);
            if ( $access )
            {// Добавление ссылки на просмотр
                $linkvars['programmsbcid'] = (int)$item->id;
                $linkvars['returnurl'] = $currenturl;
                $attroptions['title'] = $this->dof->get_string('table_programmsbcs_header_actions_view_programmbc', 'participants');
                $data['actions'] .= $this->dof->modlib('ig')->icon(
                    'view',
                    $this->dof->url_im('programmsbcs', '/view.php', $linkvars),
                    $attroptions
                );
                unset($linkvars['programmsbcid']);
                unset($linkvars['returnurl']);
            }
            $access = $this->dof->storage('programmsbcs')->is_access('edit', (int)$item->id);
            if ( $access )
            {// Добавление ссылки на редактирование
                $linkvars['programmsbcid'] = (int)$item->id;
                $linkvars['returnurl'] = $currenturl;
                $attroptions['title'] = $this->dof->get_string('table_programmsbcs_header_actions_edit_programmbc', 'participants');
                $data['actions'] .= $this->dof->modlib('ig')->icon(
                    'edit',
                    $this->dof->url_im('programmsbcs', '/edit.php', $linkvars),
                    $attroptions
                );
                unset($linkvars['programmsbcid']);
                unset($linkvars['returnurl']);
            }
            $access = $this->dof->storage('cpassed')->is_access('view');
            if ( $access )
            {// Добавление ссылки на список дисциплин по подписке
                $linkvars['programmsbcid'] = (int)$item->id;
                $linkvars['returnurl'] = $currenturl;
                $attroptions['title'] = $this->dof->get_string('table_programmsbcs_header_actions_programmitems', 'participants');
                $data['actions'] .= $this->dof->modlib('ig')->icon(
                    'cstreams',
                    $this->dof->url_im('cpassed', '/list.php', $linkvars),
                    $attroptions
                );
                unset($linkvars['programmsbcid']);
                unset($linkvars['returnurl']);
            }
            $access = $this->dof->storage('programmsbcs')->is_access('view');
            if ( $access )
            {// Добавление ссылки на историю обучения
                $linkvars['sbcid'] = (int)$item->id;
                $linkvars['returnurl'] = $currenturl;
                $attroptions['title'] = $this->dof->get_string('table_programmsbcs_header_actions_learninghistory', 'participants');
                $data['actions'] .= $this->dof->modlib('ig')->icon(
                    'history',
                    $this->dof->url_im('programmsbcs', '/history.php', $linkvars),
                    $attroptions
                );
                unset($linkvars['sbcid']);
                unset($linkvars['returnurl']);
            }
            $access = $this->dof->storage('learningplan')->is_access('edit');
            if ( $access )
            {// Добавление ссылки на индивидуальный учебный план по подписке
                $linkvars['type'] = 'programmsbc';
                $linkvars['programmsbcid'] = (int)$item->id;
                $linkvars['returnurl'] = $currenturl;
                $attroptions['title'] = $this->dof->get_string('table_programmsbcs_header_actions_learningplan', 'participants');
                $data['actions'] .= $this->dof->modlib('ig')->icon(
                    'plan',
                    $this->dof->url_im('learningplan', '/index.php', $linkvars),
                    $attroptions
                    );
                unset($linkvars['type']);
                unset($linkvars['programmsbcid']);
                unset($linkvars['returnurl']);
            }
            
            $data['lastname'] = '';
            $data['firstname'] = '';
            $data['middlename'] = '';
            $data['email'] = '';
            if ( isset($item->person) && ! empty($item->person) )
            {// Пользователь - владелец подписки определен
                $data['lastname'] = $item->person->lastname;
                $data['firstname'] = $item->person->firstname;
                $data['middlename'] = $item->person->middlename;
                $data['email'] = $item->person->email;
                // Проверка доступа
                $access = $this->dof->storage('persons')->is_access('view', (int)$item->person->id);
                $exist = $this->dof->plugin_exists('im', 'persons');
                if ( $access && $exist )
                {// Добавление ссылок на данные пользователя
                    $linkvars['id'] = (int)$item->person->id;
                    $link = $this->dof->url_im('persons', '/view.php', $linkvars);
                    $data['lastname'] = dof_html_writer::link($link, $item->person->lastname);
                    $data['firstname'] = dof_html_writer::link($link, $item->person->firstname);
                    $data['middlename'] = dof_html_writer::link($link, $item->person->middlename);
                    $data['email'] = dof_html_writer::link($link, $item->person->email);
                    unset($linkvars['id']);
                }
            }
            $data['department'] = '';
            if ( isset($item->department) && ! empty($item->department) )
            {// Подразделение определено
                $data['department'] = $item->department->name;
            }
            $data['programm'] = '';
            if ( isset($item->programm) && ! empty($item->programm) )
            {// Программа определена
                $data['programm'] = $item->programm->name;
                // Проверка доступа
                $access = $this->dof->storage('programms')->is_access('view', (int)$item->programm->id);
                $exist = $this->dof->plugin_exists('im', 'programms');
                if ( $access && $exist )
                {// Добавление ссылки на данные программы
                    $linkvars['programmid'] = (int)$item->programm->id;
                    $link = $this->dof->url_im('programms', '/view.php', $linkvars);
                    $data['programm'] = dof_html_writer::link($link, $item->programm->name);
                    unset($linkvars['programmid']);
                }
            }
            $data['agenum'] = (string)$item->agenum;
            $data['agroup'] = '';
            if ( isset($item->agroup) && ! empty($item->agroup) )
            {// Группа определена
                $data['agroup'] = $item->agroup->name;
                // Проверка доступа
                $access = $this->dof->storage('agroups')->is_access('view', (int)$item->agroup->id);
                $exist = $this->dof->plugin_exists('im', 'agroups');
                if ( $access && $exist )
                {// Добавление ссылки на данные группы
                    $linkvars['agroupid'] = (int)$item->agroup->id;
                    $link = $this->dof->url_im('agroups', '/view.php', $linkvars);
                    $data['agroup'] = dof_html_writer::link($link, $item->agroup->name);
                    unset($linkvars['agroupid']);
                }
            }
            $data['status'] = $this->dof->workflow('programmsbcs')->get_name($item->status);
            
            $table->data[] = $data;
        }
        $html .= $this->dof->modlib('widgets')->print_table($table, true);
        
        return $html;
    }
    
    /**
     * Сформировать результирующие данные для рендеринга таблицы подписок
     *
     * @param array $programmsbcs - Массив идентиифкаторов подписок
     * @param array $options - Опции формирования данных
     *
     * @return array - Массив подписок с данными о пользователе, группе и программе
     */
    private function prepare_programmsbcs_table_data($programmsbcs, $options)
    {
        if ( empty($programmsbcs) )
        {
            return [];
        }
        // Нормализация
        if ( $options['limitfrom'] < 1 )
        {
            $options['limitfrom'] = 1;
        }

        // ПОЛУЧЕНИЕ СРЕЗА ПОДПИСОК НА ПРОГРАММЫ C СОРТИРОВКОЙ
        switch ( $options['addvars']['sort'] )
        {// Получение среза в зависимости от типа сортировки
            case 'lastname' :
            case 'firstname' :
            case 'middlename' :
            case 'email' :
                // Сортировка по полю пользователя
                $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records_sort_person(
                    $programmsbcs, 
                    $options['addvars']['sort'], 
                    $options['addvars']['dir'], 
                    '*', 
                    $options['limitfrom'] - 1, 
                    $options['limitnum']
                );
                break;
            case 'programm' :
                $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records_sort_programm(
                    $programmsbcs,
                    'name',
                    $options['addvars']['dir'],
                    '*',
                    $options['limitfrom'] - 1,
                    $options['limitnum']
                );
                break;
            case 'agenum' :
            case 'status' :
                $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records(
                    ['id' => $programmsbcs],
                    $options['addvars']['sort'].' '.$options['addvars']['dir'],
                    '*',
                    $options['limitfrom'] - 1,
                    $options['limitnum']
                );
                break;
            case 'agroup' :
                $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records_sort_agroup(
                    $programmsbcs,
                    'name',
                    $options['addvars']['dir'],
                    '*',
                    $options['limitfrom'] - 1,
                    $options['limitnum']
                );
                break;
            default :
                $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records(
                    ['id' => $programmsbcs],
                    '',
                    '*',
                    $options['limitfrom'] - 1,
                    $options['limitnum']
                );
        }
        
        // Подготовкв данных
        $contracts = [];
        $programms = [];
        $agroups = [];
        $departments = [];
        
        foreach ( $programmsbcs as &$item )
        {
            // Заполнение буферного массива связи контракта и персоны
            if ( ! isset($contracts[$item->contractid]) )
            {// Пользователь по договору не найден
                $personid = (int)$this->dof->storage('contracts')->get_field($item->contractid, 'studentid');
                $person = $this->dof->storage('persons')->
                    get($personid, 'id, firstname, lastname, middlename, email');
                if ( ! empty($person) )
                {// Добавление пользователя в буферный массив
                    $contracts[$item->contractid] = $person;
                } else 
                {// Пользователь не найден
                    $contracts[$item->contractid] = null;
                }
            }
            // Заполнение буферного массива программ
            if ( ! isset($programms[$item->programmid]) )
            {// Программа не найдена в буферном массиве
               
                $programm = $this->dof->storage('programms')->get($item->programmid, 'id, name, code');
                if ( ! empty($programm) )
                {// Добавление программы в буферный массив
                    $programms[$item->programmid] = $programm;
                } else
                {// Программа не найдена
                    $programms[$item->programmid] = null;
                }
            }
            // Заполнение буферного массива групп
            if ( ! isset($agroups[$item->agroupid]) )
            {// Группа не найдена в буферном массиве
                
                $agroup = $this->dof->storage('agroups')->get($item->agroupid, 'id, name, code');
                if ( ! empty($agroup) )
                {// Добавление группы в буферный массив
                    $agroups[$item->agroupid] = $agroup;
                } else
                {// Пользователь не найден
                    $agroups[$item->agroupid] = null;
                }
            }
            // Заполнение буферного массива подразделений
            if ( ! isset($departments[$item->departmentid]) )
            {// Подразделение не найдено в буферном массиве
            
                $department = $this->dof->storage('departments')->get($item->departmentid, 'id, name, code');
                if ( ! empty($department) )
                {// Добавление подразделения в буферный массив
                    $departments[$item->departmentid] = $department;
                } else
                {// Подразделение не найдено
                    $departments[$item->departmentid] = null;
                }
            }
            // Добавление данных к подписке
            $item->person = $contracts[$item->contractid];
            $item->programm = $programms[$item->programmid];
            $item->agroup = $agroups[$item->agroupid];
            $item->department = $departments[$item->departmentid];
        }
        // Очистка данных кэша
        $contracts = null;
        $programms = null;
        $agroups = null;
        $departments = null;
        
        return $programmsbcs;
    }
    
    /**
     * Сформировать массив заголовков таблицы подписок с возможностью сортировки
     * 
     * @param $options - Массив данных для генерации шапки
     *                  bool['disablesort'] - Отключить функции сортировки в таблице
     * @return array - Массив заголовков таблицы с кнопками сортировки
     */
    private function get_programmsbcs_table_head($options)
    {
        // Базовая шапка
        $head = [
            'actions'    => $this->dof->get_string('table_programmsbcs_header_actions',    'participants'),
            'lastname'   => $this->dof->get_string('table_programmsbcs_header_lastname',   'participants'),
            'firstname'  => $this->dof->get_string('table_programmsbcs_header_firstname',  'participants'),
            'middlename' => $this->dof->get_string('table_programmsbcs_header_middlename', 'participants'),
            'email'      => $this->dof->get_string('table_programmsbcs_header_email',      'participants'),
            'department' => $this->dof->get_string('table_programmsbcs_header_department', 'participants'),
            'programm'   => $this->dof->get_string('table_programmsbcs_header_programm',   'participants'),
            'agenum'     => $this->dof->get_string('table_programmsbcs_header_agenum',     'participants'),
            'agroup'     => $this->dof->get_string('table_programmsbcs_header_agroup',     'participants'),
            'status'     => $this->dof->get_string('table_programmsbcs_header_status',     'participants')
        ];
        
        if ( isset($options['disablesort']) && $options['disablesort'] )
        {// Функции сортировки в таблице отключены
            return $head;
        }
        
        // Адрес текущей страницы
        $currenturl = $this->dof->modlib('nvg')->get_url();
        // Добавить функции сортировки
        foreach ( $head as $name => $item )
        {
            // Запрет сортировки по полям
            if ( $name == 'actions' || $name == 'department' )
            {
                continue;
            }
            if ( $options['addvars']['sort'] == $name )
            {// Сортировка по текущему полю включена
                // Ссылка на противоположную сортировку
                if ( $options['addvars']['dir'] == 'DESC' )
                {
                    $currenturl->param('dir', 'ASC');
                    $cell = $this->dof->modlib('ig')->icon(
                        'arrow_down'
                    );
                } else
                {
                    $currenturl->param('dir', 'DESC');
                    $cell = $this->dof->modlib('ig')->icon(
                        'arrow_up'
                    );
                }
                $currenturl->param('sort', $name);
                $head[$name] = dof_html_writer::link($currenturl, $cell.$head[$name]);
            } else 
            {// Сортировка по полю не ведется
                // Добавление параметров URL
                $currenturl->param('sort', $name);
                $currenturl->param('dir', $options['addvars']['dir']);
                $head[$name] = dof_html_writer::link($currenturl, $head[$name]);
                // Хак для сброса значения сортировки. Иначе параметр сохраняется в объекте текущего URL
                $currenturl->remove_params(['sort', 'dir']);
            }
        }

        return $head;
    }
}