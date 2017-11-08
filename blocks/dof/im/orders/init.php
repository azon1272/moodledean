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
 * Интерфейс просмотра приказов. Класс плагина.
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class dof_im_orders implements dof_plugin_im
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
     * Метод, реализующий удаление плагина в системе.
     * 
     * @return boolean
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * Возвращает версию установленного плагина
     * 
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016042700;
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
        return 'orders';
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
                'nvg'          => 2008060300,
                'widgets'      => 2009050800
            ],
            'storage' => [
                'config'       => 2011080900,
                'departments'  => 2015110500,
                'orders'       => 2014013000,
                'acl'          => 2011040504
            ],
            'workflow' => [
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
                'nvg'          => 2008060300,
                'widgets'      => 2009050800
            ],
            'storage' => [
                'config'       => 2011080900,
                'departments'  => 2015110500,
                'orders'       => 2014013000,
                'acl'          => 2011040504
            ],
            'workflow' => [
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
        return [];
    }
    
    /**
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     */
    public function is_cron()
    {
        return false;
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
    public function is_access( $do, $objid = NULL, $userid = NULL )
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
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
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
    public function cron($loan, $messages)
    {
        return true;
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
     * Функция получения настроек для плагина
     */
    public function config_default($code = NULL)
    {
        // Плагин включен и используется
        $config = [];
    
        // Создание нового контекста уникальных номеров приказов
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'set_unique_ordersnum_context';
        $obj->value = '0';
        $config[$obj->code] = $obj;
    
        return $config;
    }
    
    // **********************************************
    // Методы, предусмотренные интерфейсом im
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
    
        return $a;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /**
     * Генерация формы для фильтрации первого шага
     *
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Дополнительные параметры создания формы
     *         
     * @return dof_im_orders_prefilter_form - Форма фильтрации приказов
     */
    public function form_prefilter($addvars, $options = [] )
    {
        // Формирование URL формы
        $url = $this->dof->url_im('orders', '/index.php', $addvars);
        
        // Формирование данных формы
        $customdata = new stdClass();
        $customdata->dof = $this->dof;
        $customdata->addvars = $addvars;
        
        // Применение значений по-умолчанию
        $defaultvalues = new stdClass();
        if ( isset($addvars['ptype']) )
        {// Тип плагина указан
            $defaultvalues->ptype = $addvars['ptype'];
        }
        if ( isset($addvars['pcode']) and isset($addvars['ptype']) )
        {// Код плагина указан
            $defaultvalues->pcode = $addvars['ptype'] . "_" . $addvars['pcode'];
        }
        if ( isset($addvars['code']) )
        {// Код приказа указан
            $defaultvalues->code = $addvars['code'];
        }
        
        // Создание формы
        $formfilter = new dof_im_orders_prefilter_form($url, $customdata);
        // Заполнение данными
        $formfilter->set_data($defaultvalues);
        // Вернуть форму
        return $formfilter;
    }

    /**
     * Генерация формы для фильтрации приказов
     *
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Дополнительные параметры создания формы
     *           
     * @return boolean|object форма в зависимости от переданных параметров или false в случае ошибки
     */
    public function form_filter($addvars, $options = [])
    {
        // Проверка обязательных параметров
        if ( ! isset($addvars['ptype']) || ! isset($addvars['pcode']) || ! isset($addvars['code']) )
        {
            return false;
        }
        
        // Создание экземпляра приказа
        $orderclass = $this->dof->storage('orders')->
            order($addvars['ptype'], $addvars['pcode'], $addvars['code']);
        if ( ! $orderclass )
        {// Экземпляр не создан
            return false;
        }
        
        //сформируем объект со значениями по умолчанию для возможности передачи GET параметров
        $defaultvalues = new stdClass();
        $defaultvalues->ptype = $addvars['ptype'];
        $defaultvalues->pcode = $addvars['pcode'];
        $defaultvalues->code = $addvars['code'];
        if ( isset($addvars['o_status']) )
        {// Статусы приказов указаны
            foreach(explode(',',$addvars['o_status']) as $defaultstatus)
            {
                $defaultvalues->status[$defaultstatus] = '1';
            }
        }
        
        // Создание внутренней формы фильтрации приказов целевого типа
        $formfilter = $orderclass->form_filter($addvars, $defaultvalues);
        
        return $formfilter;
    }

    /**
     * Генерация формы для редактирования приказа, подцепляет класс формы
     *
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Дополнительные параметры создания формы
     *           
     * @return boolean|object форма в зависимости от переданных параметров или false в случае ошибки
     */
    public function form_edit($addvars, $options = [])
    {
        // Проверка обязательных параметров
        if ( ! isset($addvars['id']) )
        {// Идентификатор не указан
            return false;
        }
        
        // Получение приказа по идентификатору
        if ( $order = $this->dof->storage('orders')->
                get_record(['id' => $addvars['id']]) )
        {// Получен приказ
            if ( $orderinstance = $this->dof->storage('orders')->order($order->plugintype, 
                $order->plugincode, $order->code, $order->id) )
            {// Создан экземпляр класса
                // Возвращаем форму
                return $orderinstance->form_edit($addvars, $order);
            } else
            {// Не удалось создать экземпляр класса
                return false;
            }
        } else
        {// Не удалось получить приказ
            return false;
        }
    }

    /**
     * Генерация формы для смены статуса приказа
     *
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Дополнительные параметры создания формы
     *       
     * @return boolean|object форма смены статуса или false в случае ошибки
     */
    public function form_change_status($addvars, $options = [])
    {
        // Проверка обязательных параметров
        if ( ! isset($addvars['id']) )
        {// Идентификатор не указан
            return false;
        }
        
        // Получение приказа по идентификатору
        if ( $order = $this->dof->storage('orders')->
            get_record(['id' => $addvars['id']]) )
        {// Получен приказ
            if ( $orderinstance = $this->dof->storage('orders')->order($order->plugintype,
                $order->plugincode, $order->code, $order->id) )
            {// Создан экземпляр класса
                // Возвращаем форму
                return $orderinstance->form_change_status($addvars, $order);
            } else
            {// Не удалось создать экземпляр класса
                return false;
            }
        } else
        {// Не удалось получить приказ
            return false;
        }
    }

    /**
     * Визуальное представление приказа
     *
     * @param array $addvars - Массив GET-параметров
     * @param array $options - Дополнительные параметры создания формы
     *          
     * @return boolean|string html-таблица с данными по приказу или false в случае ошибки
     */
    public function view_order( $addvars, $options = [] )
    {
        // Проверка обязательных параметров
        if ( ! isset($addvars['id']) )
        {// Идентификатор не указан
            return false;
        }
        
        // Получение приказа по идентификатору
        if ( $order = $this->dof->storage('orders')->
            get_record(['id' => $addvars['id']]) )
        {// Получен приказ
            if ( $orderinstance = $this->dof->storage('orders')->order($order->plugintype,
                $order->plugincode, $order->code, $order->id) )
            {// Создан экземпляр класса
                
                // Таблица данных по приказу
                $table = new stdClass();
                // Получение строк таблицы
                $orderrow = $orderinstance->show_tablerow($addvars);
                // Получение заголовков для таблицы
                $orderheader = $orderinstance->show_tableheader($addvars);
                // Заполнение таблицы
                foreach ( $orderrow as $orderfield )
                {
                    $table->data[] = [
                        '<b>' . current(each($orderheader)) . '</b>',
                        $orderfield
                    ];
                }
                // Возвращаем таблицу
                return $this->dof->modlib('widgets')->print_table($table, true);
            } else
            {// Не удалось создать экземпляр класса
                return false;
            }
        } else
        {// Не удалось получить приказ
            return false;
        }
    }

    /**
     * формирование списков для ajax select
     *
     * @param string $querytype - тип завпроса(по умолчанию стандарт)
     * @param string $data - строка
     * @param integer $depid - id подразделения
     *            
     * @return array|boolean - запись, если есть или false, если нет
     */
    public function widgets_field_variants_list($querytype, $depid, $data, $objectid )
    {
        $result = false;
        // в зависимости от типа, возвращаем те или иные данные
        switch ( $querytype )
        {
            case 'list_pcodes':
                //список кодов плагинов
                //устанавливаем значение типа "не выбрано"
                $result[0] = $this->dof->get_string('form_prefilter_element_option_choose_pcode', 
                    'orders');
                //получаем список кодов плагина
                $pcodes = $this->dof->storage('orders')->get_list_pcodes($data['parentvalue']);
                //для каждого кода плагина добавляем элемент массива
                foreach ( $pcodes as $k => $v )
                {
                    //для возможности передать дальше значения двух селектов, храним значение родителя
                    $result[$data['parentvalue'] . "_" . $k] = $v;
                }
                break;
            case 'list_codes':
                //список кодов приказов
                //ожидаем, что в коде плагина содержится и его тип - парсим
                $plugininfo = explode("_", $data['parentvalue']);
                
                $codes = [];
                if ( count($plugininfo) > 1 )
                {
                    //получаем значения типа и кода плагина
                    list ( $ptype, $pcode ) = $plugininfo;
                    //получаем возможные коды приказов
                    $codes = $this->dof->storage('orders')->get_list_codes($ptype, $pcode);
                }
                
                $result = array_merge(
                    [
                        0 => $this->dof->get_string('form_prefilter_element_option_choose_code', 
                            'orders')
                    ], $codes);
                break;
        }
        return $result;
    }
}