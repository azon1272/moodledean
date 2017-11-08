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
 * Партнерская сеть
 * 
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class dof_im_partners implements dof_plugin_im
{
    /**
     * Объект деканата для доступа к общим методам
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
        // Обновим права доступа
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());  
    }
    
    /**
     * Возвращает версию установленного плагина
     * 
     * @return int - Версия плагина
     */
    public function version()
    {
		return 2015111000;
    }
    
    /** 
     * Возвращает версии интерфейса Деканата, с которыми этот плагин может работать
     * 
     * @return string
     */
    public function compat_dof()
    {
        return 'aquarium';
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
        return 'partners';
    }
    
    /** 
     * Возвращает список плагинов, без которых этот плагин работать не может
     * 
     * @return array
     */
    public function need_plugins()
    {
        return [
                'modlib' => [
                                'nvg'         => 2008060300,
                                'widgets'     => 2009050800
                ],
                'im' => [
                                'reports'     => 2012080100
                ],
                'storage' => [
                                'config'      => 2011080900,
                                'departments' => 2015110500,
                                'persons'     => 2015111000,
                                'acl'         => 2011040504,
                                'reports'     => 2013021100
                ],
                'workflow' => [
                                'reports' => 2015090000
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
                'modlib' => [
                                'nvg'         => 2008060300,
                                'widgets'     => 2009050800
                ],
                'im' => [
                                'reports'     => 2012080100
                ],
                'storage' => [
                                'config'      => 2011080900,
                                'departments' => 2015110500,
                                'persons'     => 2015111000,
                                'acl'         => 2011040504,
                                'reports'     => 2013021100
                ],
                'workflow' => [
                                'reports' => 2015090000
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
                        [
                                        'plugintype' => 'im',
                                        'plugincode' => 'my',
                                        'eventcode'  => 'info'
                        ]
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
    public function is_access($do, $objid = NULL, $userid = NULL)
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
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "partners/{$do} (block/dof/im/partners: {$do})";
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
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info')
        {
            $sections = [];
            if ( $this->get_section('my_partner_codes') )
            {// Вывести таблицу кодов
                $sections[] = [
                                'im' => $this->code(),
                                'name' => 'my_partner_codes',
                                'id' => 1, 
                                'title' => $this->dof->get_string('title', $this->code())
                ];
            }
            return $sections;
        }
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
    
    /** Функция получения настроек для плагина
     *
     */
    public function config_default($code = NULL)
    {
        // Плагин включен и используется
        $config = [];
        
        // Подключение регистрации
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'registration_enabled';
        $obj->value = '0';
        $config[$obj->code] = $obj;
        
        // Родительское подразделение партнерской сети
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'parent_departmentid';
        $obj->value = NULL;
        $config[$obj->code] = $obj;
        
        // URL редиректа после успешной регистрации
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'registration_success_url';
        $obj->value = NULL;
        $config[$obj->code] = $obj;
        
        // ID модуля курса MOODLE для сбора статистики по оценкам
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'grademodule_id';
        $obj->value = NULL;
        $config[$obj->code] = $obj;
        
        // ID модуля курса MOODLE для сбора информации по сертификатам
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'sertificatemodule_id';
        $obj->value = NULL;
        $config[$obj->code] = $obj;
        
        // ID группы, в которую будет происходить подписка зарегистрировавшихся студентов
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'agroup_id';
        $obj->value = NULL;
        $config[$obj->code] = $obj;
        
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
            case 'my_partner_codes';
                $html = '';
                $user = $this->dof->storage('persons')->get_bu();
                if ( isset($user->id) )
                {// Персона есть в деканате
                    // Получим подразделения , в которых персона является руководителем
                    $departments = $this->dof->storage('departments')->get_records(['managerid' => $user->id]);
                    
                    if ( ! empty($departments) )
                    {// Подразделения найдены
                        foreach ( $departments as $department )
                        {
                            $html .= $this->get_codestable($department->id);
                        }
                    }
                }
                
                return $html;
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
        return "<a href='{$this->dof->url_im('partners','/index.php')}'>"
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
     * Задаем права доступа для объектов
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = [];
        /* Базовые права */

        /* Права партнерской сети */
        $a['admnistration'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['report'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['view_report_admins'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['view_report_teachers'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['view_report_students'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['export_report_admins'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['export_report_teachers'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['export_report_students'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['delete_report_admins'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['delete_report_teachers'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['delete_report_students'] = [
                        'roles' => [
                                        'manager'
                        ]
        ];
        $a['codes/view'] = [// Просмотр кодов
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
     * Сформировать код регистрации партнера
     *
     * @param $departmentid - ID текущего подразделения
     *
     * @return string -  HTML-КОД блока с кодом регистрации
     */
    public function get_partnerregistrationcode($departmentid = 0)
    {
        // Родительское подразделение партнерской сети
        $parendepartment = $this->dof->storage('config')->
            get_config_value('parent_departmentid', 'im', 'partners', $departmentid);
    
        $html = '';
        if ( ! empty($parendepartment) )
        {// Родительское подразделение партнерской сети указано
            
            // Получение кода регистрации партнерских подразделений
            $code = $this->dof->storage('cov')->get_option('storage', 'departments', $parendepartment, 'partnercode');
            if ( empty($code) )
            {// Код не найден
                // Создание нового кода регистрации
                $code = $this->generate_code();
                if ( ! empty($code) )
                {// Сохранение кода регистрации партнеров для указанного подразделения
                    $this->dof->storage('cov')->save_option('storage', 'departments', $parendepartment, 'partnercode', $code );
                }
            }
        
            // Отображение кода
            $html .= dof_html_writer::start_div();
            $html .= dof_html_writer::tag('h4', $this->dof->get_string('partner_registration_code_header', 'partners'));
            if ( ! empty($code) )
            {// Отображение кода
                $html .= dof_html_writer::tag('h5', $code);
            } else
            {
                $html .= dof_html_writer::div('-');
            }
            $html .= dof_html_writer::end_div();
            
        } else 
        {// Родительское подразделение не указано
            $html .= dof_html_writer::tag('h4', $this->dof->get_string('parent_department_not_found', 'partners'));
        }
        
        return $html;
    }
    
    /**
     * Получение таблицы партнеров
     * 
     * @param array $options - массив параметров для переопределения значений
     *          ['addvars'] - Массив GET-параметров
     * 
     * @return string - HTML-код таблицы
     */
    public function get_partnerstable($options)
    {
        // Базовые параметры
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $html = '';
        
        // Формирование массива GET параметров
        if ( isset($options['addvars']) )
        {// Массив передан в опциях
            $addvars = $options['addvars'];
        } else
        {// Самостоятельное формирование массива
            $addvars = [];
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {// Добавление подразделения
            // ID подразделения
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        
        // Родительское подразделение
        $parendepartment = $this->dof->storage('config')->
            get_config_value('parent_departmentid', 'im', 'partners', $addvars['departmentid']);
        if ( empty($parendepartment) )
        {// Главное подразделение не указано
            return $html;
        }
        
        // Получим подразделения партнерской сети
        $departments = $this->dof->storage('departments')->departments_list_subordinated($parendepartment);

        // Ссылка на добавление партнера
        $addlink = dof_html_writer::link(
                $this->dof->url_im('partners', '/edit_partner.php', $addvars),
                $this->dof->get_string('table_partners_add', 'partners')
        );
        $html .= dof_html_writer::tag('h3', $addlink);
        
        // Формируем таблицу
        $table = new stdClass;
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = ["left", "left", "center", "center", "center", "center"];
        $table->size = ["10%", "20%", "20%", "20%", "15%", "15%"];
        $table->wrap = [true, false, true, false, false, false];
        
        // Шапка таблицы
        $table->head = [
                        $this->dof->get_string('table_partners_actions', 'partners'),
                        $this->dof->get_string('table_partners_name', 'partners'),
                        $this->dof->get_string('table_partners_code', 'partners'),
                        $this->dof->get_string('table_partners_adminperson', 'partners'),
                        $this->dof->get_string('table_partners_parentdepartment', 'partners'),
                        $this->dof->get_string('table_partners_status', 'partners')
        ];
        
        // Заносим данные
        $table->data = [];
        // Параметры для редактирования
        $somevars = $addvars;
        foreach ( $departments as $item )
        {// Формирование таблицы партнерских подразделений
        
            // Формируем строку
            $data = [];
            
            // Название подразделения
            if ( $this->dof->storage('departments')->is_access('view', $item->id) )
            {// Имя - ссылка на просмотр
                $somevars['id'] = $item->id;
                $name = dof_html_writer::link(
                        $this->dof->url_im('departments', '/view.php', $somevars), 
                        $item->name
                );
            } else
            {// Имя без ссылки
                $name = $item->name;
            }
            
            // Руководитель подразделения
            $adminpersonname = $this->dof->storage('persons')->get_fullname($item->managerid);
            if ( $this->dof->storage('persons')->is_access('view', $item->managerid) )
            {// Имя - ссылка на просмотр
                $somevars['id'] = $item->managerid;
                // Руководитель
                $adminperson = dof_html_writer::link(
                    $this->dof->url_im('persons', '/view.php', $somevars),
                    $adminpersonname
                );
            } else
            {// Нет доступа
                $adminperson = $this->dof->get_string('access_error_person_view', 'persons', NULL, 'storage');
            }
            
            // Родительское подразделение
            if ( ! empty($item->leaddepid) )
            {// Родительское подразделение определено
                $parent = $this->dof->storage('departments')->get($item->leaddepid);
                if ( ! empty($parent) )
                {
                    $parentdep = $parent->name;
                } else
                {
                    $parentdep = '';
                }
            } else
            {// Родителя нет
                $parentdep = '';
            }
            
            // Действия
            $actions = '';
            if ( $this->dof->im('partners')->is_access('codes/view', $item->id) )
            {// Имеем право на просмотр кодов регистрации
                $somevars = $addvars;
                $somevars['departmentid'] = $item->id;
                // Ссылка на добавление партнера
                $opts = [];
                $opts['title'] = $this->dof->get_string('table_partners_viewcodes', 'partners');
                $actions .= $this->dof->modlib('ig')->icon(
                        'view',
                        $this->dof->url_im('partners', '/codes.php', $somevars),
                        $opts
                );
            }
            if ( $this->dof->im('partners')->is_access('admnistration') )
            {// Имеем право на просмотр кодов регистрации
                $somevars['depid'] = $item->id;
                $somevars['personid'] = $item->managerid;
                // Ссылка на добавление партнера
                $opts = [];
                $opts['title'] = $this->dof->get_string('table_partners_edit', 'partners');
                $actions .= $this->dof->modlib('ig')->icon(
                    'edit',
                    $this->dof->url_im('partners', '/edit_partner.php', $somevars),
                    $opts
                );
            }
            
            $data[] = $actions;
            $data[] = $name;
            $data[] = $item->code;
            $data[] = $adminperson;
            $data[] = $parentdep;
            $data[] = $this->dof->workflow('departments')->get_name($item->status);
            
            $table->data[] = $data;
        }
        
        if ( ! empty($table->data) )
        {// Есть доступные для отображения строки
            
            $html .= dof_html_writer::tag('h4', $this->dof->get_string('table_partners_title', 'partners') );
        
            $html .= $this->dof->modlib('widgets')->print_table($table, true);
        }
        
        return $html;
    }
    
    /**
     * Создать таблицу кодов подазделения
     * 
     * @param int $departmentid - ID подразделения, коды которого необходимо отобразить
     * @param array $options - Массив дополнитеьных опций отображения
     *      [addvars] - массив GET-параметров для ссылки
     *      
     * @return string - HTML-код таблицы
     */
    public function get_codestable($departmentid = 0, $options = [])
    {
        // Переменная для хранения HTML кода
        $html = '';
        
        // Объявление опций обработки
        if ( ! isset($options['addvars']) )
        {// Массив GET- параметров не указан - сформируем свой
            $addvars = [];
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
            $options['addvars'] = $addvars;
        }
        
        // Получение целевого подразделения
        $department = $this->dof->storage('departments')->get($departmentid);
        if ( empty($department) )
        {
            return $html;
        }
        // Получение кодов
        $codestudent = $this->dof->storage('cov')->
            get_option('storage', 'departments', $departmentid, 'studentcode');
        $codeteacher = $this->dof->storage('cov')->
            get_option('storage', 'departments', $departmentid, 'teachercode');
        
        // Формируем таблицу
        $table = new stdClass;
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->align = ["center", "center"];
        $table->size = ["50%", "50%"];
        $table->wrap = [true, true];
        $table->data = [];
        $table->head = [
                        $this->dof->get_string('table_codes_student', 'partners'),
                        $this->dof->get_string('table_codes_teacher', 'partners')
        ];
        
        $data = [];
        if ( ! empty($codestudent) )
        {// Код для студентов найден
            $data[] = $codestudent;
        } else 
        {
            $data[] = $this->dof->get_string('error_table_codes_code_not_found', 'partners');
        }
        if ( ! empty($codeteacher) )
        {// Код для преподавателей найден
            $data[] = $codeteacher;
        } else 
        {
            $data[] = $this->dof->get_string('error_table_codes_code_not_found', 'partners');
        }
        $table->data[] = $data;
        
        $html .= dof_html_writer::tag('h5', $this->dof->get_string('table_codes_title', 'partners', $department) );
        
        if ( $this->dof->im('partners')->is_access('admnistration', $departmentid) )
        {// Есть право управлять партнерской сетью
            $url = $this->dof->url_im('partners', '/refresh_codes.php', $options['addvars']);
            $html .= dof_html_writer::link($url, $this->dof->get_string('table_codes_refresh_codes', 'partners') );
        }
        $html .= $this->dof->modlib('widgets')->print_table($table, true);
        
        return $html;
    }
    
    /**
     * Сгенерировать уникальный код регистрации
     *
     * @return string|bool - Код регистрации или false в случае ошибки
     */
    public function generate_code()
    {
        // Лимит попыток
        $limit = 100;
        do
        {// Попытка сгенерировать код
            // Получить код
            $code = mt_rand(10000000 ,99999999);
    
            $params = [ 'storage' => 'storage', 'departmants' => 'departments', 'value' => (string)$code ];
            // Проверка на содержание аналогичного кода
            $exist = $this->dof->
                storage('cov')->get_records_select(' plugintype = :storage AND plugincode = :departmants AND value LIKE :value ', $params);
    
            // Попытка завершена
            $limit--;
        } while ( $exist && $limit );
    
        if ( ! empty($limit) )
        {
            return $code;
        }
        return false;
    }
    
    /**
     * Сгенерировать коды для регистрации пользователей
     *
     * @return bool - Результат генерации
     */
    public function generate_codes($depid)
    {
        // Получим подразделение, для которого происходит генерация кодов
        $department = $this->dof->storage('departments')->get($depid);
        if ( empty($department) )
        {// Подразделение не найдено
            return false;
        }
        /* Генерация кода для преподавателей */
        // Лимит попыток
        $limit = 100;
        do
        {// Попытка сгенерировать код
            // Получить код
            $code = mt_rand(10000000 ,99999999);
    
            $params = [ 'storage' => 'storage', 'departmants' => 'departments', 'value' => (string)$code ];
            // Проверка на содержание аналогичного кода
            $exist = $this->dof->
                storage('cov')->get_records_select(' plugintype = :storage AND plugincode = :departmants AND value LIKE :value ', $params);
    
            // Попытка завершена
            $limit--;
        } while ( $exist && $limit );
    
        if ( ! empty($limit) )
        {
            $this->dof->storage('cov')->
                save_option('storage', 'departments', $department->id, 'teachercode', $code);
        }
    
        /* Генерация кода для учеников */
        // Лимит попыток
        $limit = 100;
        do
        {// Попытка сгенерировать код
            // Получить код
            $code = mt_rand(10000000 ,99999999);
    
            $params = [ 'storage' => 'storage', 'departmants' => 'departments', 'value' => (string)$code ];
            // Проверка на содержание аналогичного кода
            $exist = $this->dof->
                storage('cov')->get_records_select(' plugintype = :storage AND plugincode = :departmants AND value LIKE :value ', $params);
    
            // Попытка завершена
            $limit--;
        } while ( $exist && $limit );
    
        if ( ! empty($limit) )
        {
            $this->dof->storage('cov')->
                save_option('storage', 'departments', $department->id, 'studentcode', $code);
        }
        return true;
    }
    
    /**
     * Получить информацию по коду регистрации
     * 
     * @param string $code - Код регистрации
     */
    public function get_code_info($code)
    {
        $select = ' plugintype = :plugintype AND plugincode = :plugincode AND value LIKE :value ';
        $param = [
                        'plugintype' => 'storage',
                        'plugincode' => 'departments',
                        'value' => $code
        ];
        $records = $this->dof->storage('cov')->get_records_select($select, $param);
        
        // Массив информации 
        $info = [];
        if ( ! empty($records) )
        {// Записи найдены
            foreach ( $records as $record )
            {// Обработаем каждую запись
                $codeinfo = [];
                $codeinfo['department'] = $this->dof->storage('departments')->get($record->objectid);
                $codeinfo['type'] = $record->code;
                $info[] = $codeinfo;
            }
        }
        
        return $info;
    }
    
    /**
     * Получить список типов подразделения
     *
     * @return array - Массив типов
     */
    public function get_list_dep_types()
    {
        $result = [];
        $result[0] = $this->dof->get_string('form_partners_add_dep_type_school', 'partners');
        $result[1] = $this->dof->get_string('form_partners_add_dep_type_college', 'partners');
    
        return $result;
    }
    
    /**
     * Получить HTML-код таблицы отчета руководителей
     * 
     * @param array $options - Массив дополнительных опций отображения
     *      [addvars] - массив GET-параметров для ссылки
     *      
     * @return string - HTML-код таблицы
     */
    public function get_reporttable_admins($options = [])
    {
        // Переменная для хранения HTML кода
        $html = '';
        
        // Объявление опций обработки
        if ( ! isset($options['addvars']) )
        {// Массив GET- параметров не указан - сформируем свой
            $addvars = [];
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        } else 
        {// Массив GET параметров передан
            $addvars = $options['addvars'];
        }
        
        // Получение родительского подразделения партнерской сети
        $parentdepartment = $this->dof->storage('config')->
            get_config_value('parent_departmentid', 'im', 'partners', $addvars['departmentid']);
        
        // Получение всех подразделений партнерской сети
        $options = [];
        $statuses = $this->dof->workflow('departments')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $options['statuses'] = $statuses;
        $departments = $this->dof->storage('departments')->get_departments($parentdepartment, $options); 
        
        
        
        // Формируем таблицу
        $table = new html_table();
        $table->tablealign = "center";
        $table->cellpadding = 0;
        $table->cellspacing = 0;
        $table->size = ["50px","200px","200px","100px","150px","200px","150px","150px","150px","100px","100px","150px","200px","150px","50px","50px","50px","50px","50px","50px"];
        $table->wrap = [true, true];
        
        // Заголовок таблицы
        $table->head = [];
        // Номер строки
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_admins_num', 'partners'));
        
        // ФИО
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_admins_fio', 'partners'));
        
        // Подтаблица Об Образовательной организации
        $tablecell = new html_table();
        $tablecell->size = ["200px","100px","150px","200px","150px","150px"];
        $tablecelldata = [];
        $tablecelldatacell = new html_table_cell($this->dof->get_string('table_report_admins_about_lo', 'partners'));
        $tablecelldatacell->colspan = 6;
        $tablecelldata[] = [$tablecelldatacell];
        $tablecelldata[] = [
                        $this->dof->get_string('table_report_admins_lo', 'partners'),
                        $this->dof->get_string('table_report_admins_lo_type', 'partners'),
                        $this->dof->get_string('table_report_admins_lo_district', 'partners'),
                        $this->dof->get_string('table_report_admins_lo_director_fio', 'partners'),
                        $this->dof->get_string('table_report_admins_lo_telephone', 'partners'),
                        $this->dof->get_string('table_report_admins_lo_email', 'partners')
        ];
        $tablecell->data = $tablecelldata;
        $cell = new html_table_cell($this->dof->modlib('widgets')->print_table($tablecell, true));
        $cell->colspan = 6;
        $table->head[] = $cell;
        
        // Подтаблица О руководителе
        $tablecell = new html_table();
        $tablecell->size = ["150px","100px","100px","150px","200px"];
        $tablecelldata = [];
        $tablecelldatacell = new html_table_cell($this->dof->get_string('table_report_admins_about_director', 'partners'));
        $tablecelldatacell->colspan = 5;
        $tablecelldata[] = [$tablecelldatacell];
        $tablecelldata[] = [
                        $this->dof->get_string('table_report_admins_director_position', 'partners'),
                        $this->dof->get_string('table_report_admins_director_birth', 'partners'),
                        $this->dof->get_string('table_report_admins_director_gender', 'partners'),
                        $this->dof->get_string('table_report_admins_director_email', 'partners'),
                        $this->dof->get_string('table_report_admins_director_mobile', 'partners')
        ];
        $tablecell->data = $tablecelldata;
        $cell = new html_table_cell($this->dof->modlib('widgets')->print_table($tablecell, true));
        $cell->colspan = 5;
        $table->head[] = $cell;
        
        // Дата регистрации
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_admins_registration_date', 'partners'));
        
        // Подтаблица количество учащихся
        $tablecell = new html_table();
        $tablecell->size = ["50px","50px"];
        $tablecelldata = [];
        $tablecelldatacell = new html_table_cell($this->dof->get_string('table_report_admins_student_count', 'partners'));
        $tablecelldatacell->colspan = 2;
        $tablecelldata[] = [$tablecelldatacell];
        $tablecelldata[] = [
                        $this->dof->get_string('table_report_admins_student_count_plan', 'partners'),
                        $this->dof->get_string('table_report_admins_student_count_fact', 'partners')
        ];
        $tablecell->data = $tablecelldata;
        $cell = new html_table_cell($this->dof->modlib('widgets')->print_table($tablecell, true));
        $cell->colspan = 2;
        $table->head[] = $cell;
        
        // Подтаблица количество преподавателей
        $tablecell = new html_table();
        $tablecell->size = ["50px","50px"];
        $tablecelldata = [];
        $tablecelldatacell = new html_table_cell($this->dof->get_string('table_report_admins_teacher_count', 'partners'));
        $tablecelldatacell->colspan = 2;
        $tablecelldata[] = [$tablecelldatacell];
        $tablecelldata[] = [
                        $this->dof->get_string('table_report_admins_teacher_count_plan', 'partners'),
                        $this->dof->get_string('table_report_admins_teacher_count_fact', 'partners')
        ];
        $tablecell->data = $tablecelldata;
        $cell = new html_table_cell($this->dof->modlib('widgets')->print_table($tablecell, true));
        $cell->colspan = 2;
        $table->head[] = $cell;
        
        // Подтаблица количество руководителей
        $tablecell = new html_table();
        $tablecell->size = ["50px","50px"];
        $tablecelldata = [];
        $tablecelldatacell = new html_table_cell($this->dof->get_string('table_report_admins_admins_count', 'partners'));
        $tablecelldatacell->colspan = 2;
        $tablecelldata[] = [$tablecelldatacell];
        $tablecelldata[] = [
                        $this->dof->get_string('table_report_admins_student_admins_plan', 'partners'),
                        $this->dof->get_string('table_report_admins_student_admins_fact', 'partners')
        ];
        $tablecell->data = $tablecelldata;
        $cell = new html_table_cell($this->dof->modlib('widgets')->print_table($tablecell, true));
        $cell->colspan = 2;
        $table->head[] = $cell;

        $table->data = [];
        
        if ( ! empty($departments) )
        {// Подразделения партнерской сети есть
            
            // Кэш регионов
            $addresscache = [];
            
            // Счетчик
            $num = 0;
            
            // Типы подразделений
            $deptypes = $this->get_list_dep_types();
            
            foreach ( $departments as $dep )
            {// Обработаем данные по каждому подразделению
                
                if ( ! empty($dep->addressid) )
                {// Указан адрес подразделения
                    $address = $this->dof->storage('addresses')->get($dep->addressid);
                    if ( ! isset($addresscache[$address->country]) )
                    {// В кеш не загружена страна подразделения
                        $country = $this->dof->modlib('refbook')->region($address->country);
                        $addresscache[$address->country] = $country[$address->country];
                    }
                }
                // Получение персоны-администратора подразделения
                $person = $this->dof->storage('persons')->get($dep->managerid);
                // Массив персон подразделения
                $persons = $this->dof->storage('persons')->get_records(['departmentid' => $dep->id], '', 'id');
                $personsids = array_keys($persons);
                $personsids = implode(',', $personsids);
                
                // Сбор данных
                $data = [];
                
                // Номер строки
                $data[] = ++$num;
                // ФИО администратора
                $data[] = $this->dof->storage('persons')->get_fullname($dep->managerid);
                // Имя подразделения
                $data[] = $dep->name;
                // Тип подразделения
                $typeid = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'type');
                if ( isset($deptypes[$typeid]) )
                {
                    $data[] = $deptypes[$typeid];
                } else 
                {
                    $data[] = '';
                }
                // Округ
                if ( isset($addresscache[$address->country][$address->region]) )
                {
                    $data[] = $addresscache[$address->country][$address->region];
                } else
                {
                    $data[] = '';
                }
                // ФИО директора
                $directorfio = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'directorfio');
                if ( ! empty($directorfio) )
                {
                    $data[] = $directorfio;
                } else
                {
                    $data[] = '';
                }
                // Рабочий телефон школы
                $telephone = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'telephone');
                if ( ! empty($telephone) )
                {
                    $data[] = $telephone;
                } else
                {
                    $data[] = '';
                }
                // E-mail школы
                $email = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'email');
                if ( ! empty($email) )
                {
                    $data[] = $email;
                } else
                {
                    $data[] = '';
                }
                
                // Должность
                $data[] = '';
                
                // Число лет
                if ( isset($person->dateofbirth) )
                {
                    $datea = new DateTime();
                    $datea->setTimestamp($person->dateofbirth);
                    $dateb = new DateTime();
                    $interval = $dateb->diff($datea);
                    $data[] = $interval->format("%Y");
                } else 
                {
                    $data[] = '';
                }
                
                // Пол
                if ( isset($person->gender) )
                {
                    $data[] = $this->dof->get_string('form_registrtion_person_gender_'.$person->gender, 'partners');
                } else
                {
                    $data[] = '';
                }
                // Email
                if ( isset($person->email) )
                {
                    $data[] = $person->email;
                } else
                {
                    $data[] = '';
                }
                // Мобильный телефон
                if ( isset($person->phonecell) )
                {
                    $data[] = $person->phonecell;
                } else
                {
                    $data[] = '';
                }
                // Дата регистрации
                $timecreate = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'timecreate');
                if ( ! empty($timecreate) )
                {
                    $timezone = $this->dof->storage('persons')->get_usertimezone_as_number($person->id);
                    $data[] = dof_userdate($timecreate, "%d-%m-%Y", $timezone);
                } else
                {
                    $data[] = '';
                }
                // Плановое число Учащихся
                $studentsnum = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'studentsnum');
                if ( ! empty($studentsnum) )
                {
                    $data[] = $studentsnum;
                } else
                {
                    $data[] = 0;
                }
                // Фактическое число Учащихся
                $select = ' plugintype = :plugintype AND plugincode = :plugincode AND code = :code AND value = :value AND objectid IN (' . $personsids . ')';
                $param = [
                    'plugintype' => 'storage',
                    'plugincode' => 'persons',
                    'code' => 'type',
                    'value' => 'student'
                ];
                $records = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id');
                $data[] = count($records);
                // Плановое число Преподавателей
                $teachersnum = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'teachersnum');
                if ( ! empty($teachersnum) )
                {
                    $data[] = $teachersnum;
                } else
                {
                    $data[] = 0;
                }
                // Фактическое число Преподавателей
                $select = ' plugintype = :plugintype AND plugincode = :plugincode AND code = :code AND value = :value AND objectid IN (' . $personsids . ')';
                $param = [
                    'plugintype' => 'storage',
                    'plugincode' => 'persons',
                    'code' => 'type',
                    'value' => 'teacher'
                ];
                $records = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id');
                $data[] = count($records);
                // Плановое число Руководителей
                $adminsnum = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'managersnum');
                if ( ! empty($teachersnum) )
                {
                    $data[] = $adminsnum;
                } else
                {
                    $data[] = 0;
                }
                // Фактическое число Руководителей
                $select = ' plugintype = :plugintype AND plugincode = :plugincode AND code = :code AND value = :value AND objectid IN (' . $personsids . ')';
                $param = [
                    'plugintype' => 'storage',
                    'plugincode' => 'persons',
                    'code' => 'type',
                    'value' => 'manager'
                ];
                $records = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id');
                $data[] = count($records);
                
                // Заполнение строки
                $table->data[] = $data;
            }
        }
        
        $html .= $this->dof->modlib('widgets')->print_table($table, true);
        
        return $html; 
    }
    
    /**
     * Получить HTML-код таблицы отчета преподавателей
     *
     * @param array $options - Массив дополнительных опций отображения
     *      [addvars] - массив GET-параметров для ссылки
     *
     * @return string - HTML-код таблицы
     */
    public function get_reporttable_teachers($options = [])
    {
        // Переменная для хранения HTML кода
        $html = '';
        
        // Объявление опций обработки
        if ( ! isset($options['addvars']) )
        {// Массив GET- параметров не указан - сформируем свой
            $addvars = [];
            $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        } else
        {// Массив GET параметров передан
            $addvars = $options['addvars'];
        }
        
        // Получение родительского подразделения партнерской сети
        $parentdepartment = $this->dof->storage('config')->
            get_config_value('parent_departmentid', 'im', 'partners', $addvars['departmentid']);
        
        // Получение всех подразделений партнерской сети
        $options = [];
        $statuses = $this->dof->workflow('departments')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $options['statuses'] = $statuses;
        $departments = $this->dof->storage('departments')->get_departments($parentdepartment, $options);
        
        // Формируем таблицу
        $table = new html_table();
        $table->tablealign = "center";
        $table->cellpadding = 0;
        $table->cellspacing = 0;
        $table->size = ["50px","200px","200px","100px","200px","50px","100px","150px","150px","100px","100px","150px","100px"];
        $table->wrap = [true, true];
        
        // Заголовок таблицы
        $table->head = [];
        // Номер строки
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_num', 'partners'));
        // ФИО
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_fio', 'partners'));
        // Образовательное учреждение
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_lo', 'partners'));
        // Тип образовательного учреждения
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_lo_type', 'partners'));
        // Округ
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_lo_district', 'partners'));
        // Возраст
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_birth', 'partners'));
        // Пол
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_gender', 'partners'));
        // E-mail
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_email', 'partners'));
        // Мобильный телефон
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_mobile', 'partners'));
        // Сертификат
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_sertificate', 'partners'));
        // Тип
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_type', 'partners'));
        // Начало тестирования
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_teststart', 'partners'));
        // Итог за тест (оценка)
        $table->head[] = dof_html_writer::span($this->dof->get_string('table_report_teachers_testgrade', 'partners'));
        
        $table->data = [];
        
        if ( ! empty($departments) )
        {// Подразделения партнерской сети есть
        
            /* Кэши */
            // Кэш адресов подразделений
            $addresscache = [];
            // Кэш персон
            $personscache = [];
        
            /* Вспомогательные переменные */
            // Счетчик
            $num = 0;
            // Типы подразделений
            $deptypes = $this->get_list_dep_types();
        
            
            // Формирование данных по всем подразделениям
            foreach ( $departments as $dep )
            {// Обработаем данные по каждому подразделению
            
                // Конфигурация
                $sertificatemoduleid = $this->dof->storage('config')->
                    get_config_value('sertificatemodule_id', 'im', 'partners', $dep->id);
                $grademoduleid = $this->dof->storage('config')->
                    get_config_value('grademodule_id', 'im', 'partners', $dep->id);
                
                // Класс работы с модулями сертификации
                $helpersertificate = $this->dof->modlib('ama')->course(false)->instance($sertificatemoduleid)->get_manager();
                
                if ( ! empty($dep->addressid) )
                {// Указан адрес подразделения
                    $address = $this->dof->storage('addresses')->get($dep->addressid);
                    if ( ! isset($addresscache[$address->country]) )
                    {// В кеш не загружена страна подразделения
                        $country = $this->dof->modlib('refbook')->region($address->country);
                        $addresscache[$address->country] = $country[$address->country];
                    }
                }
                
                // Массив персон подразделения
                $deppersons = $this->dof->storage('persons')->get_records(['departmentid' => $dep->id], '', 'id, gender, dateofbirth, email, phonecell');
                if ( empty($deppersons) )
                {// В подразделении нет персон
                    continue;
                }
                $deppersonsids = array_keys($deppersons);
                $deppersonsids = implode(',', $deppersonsids);
                // Получение всех преподавателей и руководителей подразделения
                $select = ' 
                        plugintype = :plugintype AND 
                        plugincode = :plugincode AND 
                        code = :code AND 
                        value IN ("teacher", "manager") AND 
                        objectid IN (' . $deppersonsids . ')';
                $param = [
                                'plugintype' => 'storage',
                                'plugincode' => 'persons',
                                'code' => 'type'
                ];
                $persons = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id, objectid, value');
                
                // Тип подразделения
                $typeid = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'type');
                if ( isset($deptypes[$typeid]) )
                {
                    $dtype = $deptypes[$typeid];
                } else
                {
                    $dtype = '';
                }
                // Округ
                if ( isset($addresscache[$address->country][$address->region]) )
                {
                    $ddist = $addresscache[$address->country][$address->region];
                } else
                {
                    $ddist = '';
                }
                
                $grades = $this->dof->modlib('ama')->course(false)->instance($grademoduleid)->grades();

                if ( ! empty($persons) )
                {// Персоны в подразделении найдены
                    foreach ( $persons as $person )
                    {
                        // Сбор данных
                        $data = [];
                        
                        // Номер строки
                        $data[] = ++$num;
                        
                        // ФИО администратора
                        $data[] = $this->dof->storage('persons')->get_fullname($person->objectid);
                        
                        // Имя подразделения
                        $data[] = $dep->name;
                        
                        // Тип подразделения
                        $data[] = $dtype;
                        
                        // Округ подразделения
                        $data[] = $ddist;
                        
                        // Число лет
                        if ( isset($deppersons[$person->objectid]->dateofbirth) )
                        {
                            $datea = new DateTime();
                            $datea->setTimestamp($deppersons[$person->objectid]->dateofbirth);
                            $dateb = new DateTime();
                            $interval = $dateb->diff($datea);
                            $data[] = $interval->format("%Y");
                        } else
                        {
                            $data[] = '';
                        }
                        
                        // Пол
                        if ( isset($deppersons[$person->objectid]->gender) )
                        {
                            $data[] = $this->dof->get_string('form_registrtion_person_gender_'.$deppersons[$person->objectid]->gender, 'partners');
                        } else
                        {
                            $data[] = '';
                        }
                        // Email
                        if ( isset($deppersons[$person->objectid]->email) )
                        {
                            $data[] = $deppersons[$person->objectid]->email;
                        } else
                        {
                            $data[] = '';
                        }
                        // Мобильный телефон
                        if ( isset($deppersons[$person->objectid]->phonecell) )
                        {
                            $data[] = $deppersons[$person->objectid]->phonecell;
                        } else
                        {
                            $data[] = '';
                        }
                        
                        // Сертификат
                        $personobject = $this->dof->storage('persons')->get($person->objectid);
                        
                        $data[] = $helpersertificate->get_user_sertificate_link($personobject->mdluser);
                        
                        // Тип персоны
                        $data[] = $this->dof->get_string('form_registrtion_person_type_'.$person->value, 'partners');
                        
                        // Получение оценки
                        $grade = $grades->get_grades($personobject->mdluser);
                        
                        // Начало тестирования
                        if ( isset($grade->items[0]->grades[$personobject->mdluser]->dategraded) )
                        {
                            $timezone = $this->dof->storage('persons')->get_usertimezone_as_number($person->objectid);
                            $time = dof_userdate($grade->items[0]->grades[$personobject->mdluser]->dategraded, "%d-%m-%Y", $timezone);
                            $data[] = $time;
                        } else
                        {
                            $data[] = '';
                        }
                        
                        // Оценка
                        if ( isset($grade->items[0]->grades[$personobject->mdluser]->str_grade) )
                        {
                            $data[] = $grade->items[0]->grades[$personobject->mdluser]->str_grade;
                        } else 
                        {
                            $data[] = '';
                        }

                        // Заполнение строки
                        $table->data[] = $data;
                    }
                }
            }
        }
        
        $html .= $this->dof->modlib('widgets')->print_table($table, true);
        
        return $html;
    }
}