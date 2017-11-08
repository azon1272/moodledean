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

global $DOF;
// подключаем интерфейс настроек, чтобы в плагине работали настройки
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Класс стандартных функций интерфейса
 * 
 */
class dof_modlib_refbook implements dof_plugin_modlib
{

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
        return 2014122502;
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
        return 'neon';
    }

    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'modlib';
    }

    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'refbook';
    }

    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array();
    }

    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array();
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
        // Используем функционал из $DOFFICE
        return $this->dof->is_access($do, NULL, $userid);
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
        return true;
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

    // **********************************************
    // Собственные методы
    // **********************************************
    /** Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }

    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code = null)
    {
        // стандартная настройка "плагин включен"
        $config = array();
        $obj = new stdClass();
        $obj->type  = 'checkbox';
        $obj->code  = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        
        // Какие js-файлы по умолчанию использовать: сжатые или нет
        $edurules = $this->get_edulevel_rules();
        foreach ( $edurules as $edulevel => $scopes )
        {
            foreach ( $scopes as $scope => $rules )
            {
                foreach ( $rules as $rulename => $value )
                {
                    $obj = new stdClass();
                    switch ( $rulename )
                    {// По названию правила определим, к какому типу оно принадлежит
                        case 'metaprogrammitemid':
                            $obj->type  = 'checkbox';
                            break;
                        default:
                            $obj->type  = 'text';
                            break;
                    }
                    $obj->code  = implode('_', array($edulevel, $scope, $rulename));
                    $obj->value = $value; // названия статусов по порядку
                    $config[$obj->code] = $obj;
                }
            }
        }
        return $config;
    }
    
    /**
     *  Получить список стран и регионы к ним
     * 
     * @return array($countries, $regions) - список стран и регионов в формате:
     * $countries = array('код_страны' => 'Название страны', ...)
     * $regions   = array('код_страны' => array('код_региона' => 'название региона', ...), ... )
     */
    public function get_country_regions()
    {
        // Страна и регион
        $countries = get_string_manager()->get_list_of_countries(false);
        $regions = array();
        foreach ( $countries as $key => $name )
        {
            $regions += $this->dof->modlib('refbook')->region($key);
        }
        return array($countries, $regions);
    }

    /**
     * Возвращает список кодов регионов по коду страны
     * @param string $countrycode - код страны
     * @param string $regcode - код региона
     * @return mixed массив кодов регионов по коду страны, если передан код региона, то строка с этим кодом
     * @access public
     */
    public function region($countrycode, $regcode = null)
    {
        if ( file_exists($file = $this->dof->plugin_path('modlib', 'refbook', '/standards/regions/' . $countrycode . '_regions.php')) )
        {// если файл с регионами существует 
            // подключаем его
            include ($file);
            if ( isset($regcode) )
            {// указан код региона
                // вернем только его имя
                if ( !isset($string[$regcode]) )
                {// если кода региона нет в списке
                    // вернем пустую строчку
                    return '';
                }
                return $string[$regcode];
            }
            // отсортируем массив
            uasort($string, 'strcoll');
            return array($countrycode => $string);
        } else
        {
            return array();
        }
    }

    /**
     * Возвращает список типов адресов
     * @param int $addrtype - код типа адреса
     * @return mixed массив типов адресов, если предан код типа, то строка с этим типом
     * @access public
     */
    public function addres_type($addrtype = null)
    {// подключаем файл
        include ($this->dof->plugin_path('modlib', 'refbook', '/standards/addres.php'));
        if ( isset($string) )
        {// если в нем есть массив
            if ( isset($addrtype) )
            {// и указан тип
                // вернем название типа
                return $string[$addrtype];
            }
            // не указан - вернем сам массив
            return $string;
        }
        return false;
    }

    /**
     * Возвращает список типов удостоверений личности 
     * @param int $pastype - код типа удостоверения личности
     * @return mixed массив типов удостоверений личности, если предан код типа, то строка с этим типом
     * @access public
     */
    public function pasport_type($pastype = null)
    {// подключаем файл
        include ($this->dof->plugin_path('modlib', 'refbook', '/standards/pasport.php'));
        if ( isset($string) )
        {// если в нем есть массив
            if ( isset($pastype) )
            {// и указан тип
                // вернем название типа
                return $string[$pastype];
            }
            // не указан - вернем сам массив
            // отсортированный по возрастанию по имени
            asort($string);
            return $string;
        }
        return false;
    }

    /**
     * Возваращает список типов улиц
     * @return array
     */
    public function get_street_types()
    {
        $path = $this->dof->plugin_path('modlib', 'refbook', '/cfg/array/street_types.php');
        if ( !file_exists($path) )
        {//если файла нет - сообщим об этом
            $this->dof->print_error('file_not_found', $path);
        }
        //файл есть - подключаем файл
        include($path);
        if ( isset($street) AND is_array($street) )
        {//данные на месте
            ksort($street);
            return $street;
        }
        //а данных там нет
        return $this->dof->print_error('data_not_found', $path);
    }

    /** Получить все значения для степени однородности дисциплин
     * Этот метод используется вместо запланированного на будущее плагина storage/coursecls
     * @return array
     */
    public function get_st_coursecls()
    {
        $path = $this->dof->plugin_path('modlib', 'refbook', '/cfg/storage/coursecls.php');
        if ( !file_exists($path) )
        {//если файла нет - сообщим об этом
            $this->dof->print_error('file_not_found', $path);
        }
        //файл есть - подключаем файл
        include($path);
        if ( isset($values) AND is_array($values) )
        {//данные на месте
            return $values;
        }
        //а данных там нет
        return $this->dof->print_error('data_not_found', $path);
    }

    /** Получить русское название категории подобия учебной дисциплины
     * 
     * @return string название категории
     * @param object $data
     */
    public function get_st_coursecls_name($data)
    {
        if ( $data )
        {
            $values = $this->get_st_coursecls();
            if ( isset($values[$data]) )
            {
                return $values[$data];
            }
            return $this->dof->get_string('dis_unknown_cathegory', 'refbook', null, 'modlib');
        }
        return '';
    }

    /** Получить все типы компонентов учебной программы
     * 
     * @return array
     */
    public function get_st_component_types()
    {
        $path = $this->dof->plugin_path('modlib', 'refbook', '/cfg/storage/components.php');
        if ( !file_exists($path) )
        {//если файла нет - сообщим об этом
            $this->dof->print_error('file_not_found', $path);
        }
        //файл есть - подключаем файл
        include($path);
        if ( isset($values) AND is_array($values) )
        {//данные на месте';
            return $values;
        }
        //а данных там нет';
        return $this->dof->print_error('data_not_found1', $path);
    }

    /** Получить название для типа компоненты по его id
     * 
     * @return 
     * @param int $data - id компоненты
     */
    public function get_st_component_type_name($data)
    {
        if ( $data )
        {
            $values = $this->get_st_component_types();
            if ( isset($values[$data]) )
            {
                return $values[$data];
            }
            return $this->dof->get_string('comp_unknown', 'refbook', null, 'modlib');
        }
        return '';
    }

    /** Получить типы темы - для тем
     * 
     * @return array
     */
    public function get_lesson_types()
    {
        $file = $this->dof->plugin_path(
            'modlib', 'refbook', '/standards/lesson_types.php');
        if ( file_exists($file) )
        {// если файл существует 
            // подключаем его
            include ($file);
            if ( isset($list) AND is_array($list) )
            {// есть типы уроков - возвращаем
                return $list;
            }
            return array();
        }
        //файл не найден
        return array();
    }

    /** Получить формы урока - для событий и шаблонов
     * 
     * @return array
     */
    public function get_event_form()
    {
        $file = $this->dof->plugin_path(
            'modlib', 'refbook', '/standards/event_form.php');
        if ( file_exists($file) )
        {// если файл существует 
            // подключаем его
            include ($file);
            if ( isset($list) AND is_array($list) )
            {// есть типы уроков - возвращаем
                return $list;
            }
            return array();
        }
        //файл не найден
        return array();
    }

    /** Получить все возможные типы учебных недель (ежедневно, четная, нечетная). 
     * Для шаблонов и учебных дней.
     * 
     * @return array
     */
    public function get_day_vars()
    {
        $file = $this->dof->plugin_path(
            'modlib', 'refbook', '/standards/day_vars.php');
        if ( file_exists($file) )
        {// если файл существует 
            // подключаем его
            include ($file);
            if ( isset($list) AND is_array($list) )
            {// есть типы уроков - возвращаем
                return $list;
            }
            return array();
        }
        //файл не найден
        return array();
    }

    /** Получить все возможные типы уроков. 
     * Для шаблонов и событий.
     * 
     * @return array
     */
    public function get_event_types()
    {
        $file = $this->dof->plugin_path(
            'modlib','refbook','/standards/event_types.php');
        if ( file_exists($file) )
        {// если файл существует 
            // подключаем его
            include ($file);
            if ( isset($list) AND is_array($list) )
            {// есть типы уроков - возвращаем
                return $list;
            }
            return array();
        }
        //файл не найден
        return array();
    }

    /** Получить пронумерованный список дней недели для шаблона
     * 
     * @return array
     */
    public function get_template_week_days()
    {
        return array(
            1 => $this->dof->modlib('ig')->igs('monday'),
            2 => $this->dof->modlib('ig')->igs('tuesday'),
            3 => $this->dof->modlib('ig')->igs('wednesday'),
            4 => $this->dof->modlib('ig')->igs('thursday'),
            5 => $this->dof->modlib('ig')->igs('friday'),
            6 => $this->dof->modlib('ig')->igs('satuday'),
            7 => $this->dof->modlib('ig')->igs('sunday')
        );
    }

    /** Получить все типы итогового контроля
     * 
     * @return array
     */
    public function get_st_total_control()
    {
        $path = $this->dof->plugin_path('modlib', 'refbook', '/cfg/storage/control_type.php');
        if ( !file_exists($path) )
        {//если файла нет - сообщим об этом
            $this->dof->print_error('file_not_found', $path);
        }
        //файл есть - подключаем файл
        include($path);
        if ( isset($values) AND is_array($values) )
        {//данные на месте';
            // элемент "Другое" всегда должно быть на первом месте, запомним его
            $type_control = array();
            $type_control[1] = $values[1];
            // удалим элемент "Другое" и отсортируем массив
            unset($values[1]);
            asort($values);
            // вернем отсортировынный массив с элементом "Другой" на первом месте
            return $type_control + $values;
        }
        //а данных там нет';
        return $this->dof->print_error('data_not_found1', $path);
    }

    /** Получить название для типа итогового контроля по его id
     * 
     * @return 
     * @param int $data - id контроля
     */
    public function get_st_total_control_name($data)
    {
        if ( $data )
        {
            $values = $this->get_st_total_control();
            if ( isset($values[$data]) )
            {
                return $values[$data];
            }
            return $this->dof->get_string('total_unknown', 'refbook', null, 'modlib');
        }
        return '';
    }

    /**
     * Возвращает список уровней образования
     * @param int $edulevel - код уровня образования
     * @return mixed массив типов адресов, если предан код типа, то строка с этим типом
     * @access public
     */
    public function get_edulevel($edulevel = null)
    {// подключаем файл
        include ($this->dof->plugin_path('modlib', 'refbook', '/standards/edulevel.php'));
        if ( isset($string) )
        {// если в нем есть массив
            if ( isset($edulevel) )
            {// и указан тип
                // вернем название типа
                return $string[$edulevel];
            }
            // не указан - вернем сам массив
            return $string;
        }
        return false;
    }

    /**
     * Возвратить объект с правилами для уровня образования
     * 
     * @param int $edulevel - код уровня образования
     * @access public
     * @return object содержит следующую структуру правил
     * Область действия правил ($scope):
     * Правила для дисциплины:
     * ->pitem
     * 
     * Правила для программы:
     * ->programm
     * 
     * Правила для учебного семестра (параллели)
     * ->agenum
     * 
     * Правила для учебного года
     * ->academicyear
     * 
     * Языковые строки
     * ->strings
     * по следующему принципу:
     * strings->{$scope + $rulename} = ->get_string('edr_' + $scope + $rulename, 'refbook', null, 'modlib');
     * Например:
     * strings->agenumtotalhours = 'Количество часов превышает допустимое в семестр'
     */
    public function get_edulevel_rules($edulevel = null, $departmentid = 0)
    {// подключаем файл
        unset($string);
        include ($this->dof->plugin_path('modlib', 'refbook', '/standards/edulevelrules.php'));
        $rules = new stdClass();
        if ( !isset($string) )
        {// если в нем нет массива
            return false;
        }
        if ( isset($edulevel) )
        {// и указан тип
            // Правила для программы
            $edurules = $string[$edulevel];
            $object = json_decode(json_encode($edurules));
            // Соберём строки:
            foreach ( $edurules as $scope => $rules )
            {
                foreach ( $rules as $rulename => $rule )
                {
                    
                    if ( !empty($departmentid) )
                    {// Если указано подразделение, попробуем получить настройку
                        $configcode = implode('_', array(
                            $edulevel, $scope, $rulename
                        ));
                        $configvalue = $this->dof->storage('config')
                                ->get_config_value($configcode, 'modlib',
                                             'refbook', $departmentid);
                        if ( is_int_string($configvalue) AND $configvalue != 0 )
                        {
                            $object->$scope->$rulename = $value = $configvalue;
                        } else
                        {
                            $value = $rule;
                        }
                    } else
                    {
                        $value = $rule;
                    }
                    // ok-строки:
                    $object->{'ok' . $scope . $rulename} = $this->dof
                            ->get_string('edr_ok' . $scope . $rulename,
                                        'refbook', $value, 'modlib');
                    // fail-строки:
                    $object->{$scope . $rulename} = $this->dof
                            ->get_string('edr_' . $scope . $rulename,
                                        'refbook', $value, 'modlib');
                }
            }
            return $object;
        }
//            return json_decode(json_encode($string));
        if ( !empty($departmentid) )
        {
            $object = new stdClass();
            foreach ( $string as $edulevel => $edurules )
            {
                $object->$edulevel = new stdClass();
                foreach ( $edurules as $scope => $rules )
                {
                    $object->$edulevel->$scope = new stdClass();
                    foreach ( $rules as $rulename => $rule )
                    {
                        if ( !empty($departmentid) )
                        {// Если указано подразделение, попробуем получить настройку
                            $configcode = implode('_', array(
                                $edulevel, $scope, $rulename
                            ));
                            $configvalue = $this->dof->storage('config')
                                    ->get_config_value($configcode, 'modlib',
                                                 'refbook', $departmentid);
                            if ( is_int_string($configvalue) AND $configvalue != 0 )
                            {
                                $object->$edulevel->$scope->$rulename = $value = $configvalue;
                            } else
                            {
                                $value = $rule;
                            }
                        } else
                        {
                            $object->$edulevel->$scope->$rulename = $value = $rule;
                        }
                        // ok-строки:
                        $object->{'ok' . $scope . $rulename} = $this->dof
                                ->get_string('edr_ok' . $scope . $rulename,
                                            'refbook', $value, 'modlib');
                        // fail-строки:
                        $object->{$scope . $rulename} = $this->dof
                                ->get_string('edr_' . $scope . $rulename,
                                            'refbook', $value, 'modlib');
                    }
                }
            }
            return $object;
        } else
        {// не указан - вернем сам массив (в виде объекта)
            return json_decode(json_encode($string));
        }
        // Пустой config-файл?
        return new stdClass();
    }

    /**
     * Возвращает список типов адресов
     * @param int $edydoctype - код типа адреса
     * @return mixed массив типов адресов, если предан код типа, то строка с этим типом
     * @access public
     */
    public function get_edydoctype($edydoctype = null)
    {// подключаем файл
        include ($this->dof->plugin_path('modlib', 'refbook', '/standards/edydoctype.php'));
        if ( isset($string) )
        {// если в нем есть массив
            if ( isset($edydoctype) )
            {// и указан тип
                // вернем название типа
                return $string[$edydoctype];
            }
            // не указан - вернем сам массив
            return $string;
        }
        return false;
    }
}
?>