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
// Copyright (C) 2008-2999  Nikolay Konovalov (Николай Коновалов)         //
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
 * Класс стандартных функций интерфейса
 * 
 */
class dof_workflow_departments implements dof_workflow
{

    /**
     * Хранит методы ядра деканата
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
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function install()
    {
        // Обновить записи в таблице, сделав все подразделения активными
        $records = $this->dof->storage($this->get_storage())->get_records(array('status' => null));
        foreach ( $records as $record )
        {
            if ( $record->status != 'deleted' )
            {
                $record->status = 'active';
                $this->dof->storage($this->get_storage())->update($record, $record->id, true, true);
            }
        }
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }

    /**
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return $this->dof->storage('acl')->save_roles($this->type(), $this->code(), $this->acldefault());
    }

    /**
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2014092201;
    }

    /**
     * Возвращает версии интерфейса Деканата, 
     * с которыми этот плагин может работать
     * @return string
     * @access public
     */
    public function compat_dof()
    {
        return 'aquarium';
    }

    /**
     * Возвращает версии стандарта плагина этого типа, 
     * которым этот плагин соответствует
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'guppy_a';
    }

    /**
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'workflow';
    }

    /**
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'departments';
    }

    /**
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage' => array('departments' => 2014092200,
                                        'acl'         => 2012042500));
    }

    /** Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * 
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
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
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion = 0)
    {
        return array('storage' => array('acl' => 2012042500));
    }

    /**
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype' => 'storage', 'plugincode' => 'departments', 'eventcode' => 'insert'),
                     array('plugintype' => 'storage', 'plugincode' => 'departments', 'eventcode' => 'update'),
                     array('plugintype' => 'storage', 'plugincode' => 'departments', 'eventcode' => 'delete'),
        );
    }

    /**
     * Требуется ли запуск cron в плагине
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
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin')
                OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid);
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
    public function require_access($do, $objid = NULL, $userid = NULL, $depid = null)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( !$this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ( $objid )
            {
                $notice.=" id={$objid}";
            }
            $this->dof->print_error('nopermissions', '', $notice);
        }
    }

    /**
     * Обработать событие
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
        if ( $gentype === 'storage' AND $gencode === 'departments' )
        {//обрабатываем события от своего собственного справочника
            $storage = $this->dof->storage($this->get_storage());
            switch ( $eventcode )
            {
                case 'insert':
                    return $this->init($intvar);
                case 'update':
                    $obj = new stdClass();
                    $obj->path = $storage->get_path_for_department($mixedvar['new']->id);
                    $obj->depth = $storage->get_depth_for_department($mixedvar['new']->id);
                    $storage->update($obj, $mixedvar['new']->id, true);
                    if ( isset($mixedvar['old']->leaddepid) AND
                            $mixedvar['old']->leaddepid != $mixedvar['new']->leaddepid )
                    {// подразделение сменило родителя - обновим деточек
                        $storage->update_depth_path($mixedvar['old']->path);
                    }
                    if ( isset($mixedvar['new']->status) AND $mixedvar['new']->status == 'deleted' )
                    {// Если удаляем подразделение
                        $storage->change_subdepartment($mixedvar['new']->id, $mixedvar['new']->leaddepid);
                    }
                    break;
                case 'delete':

                    $storage->change_subdepartment($mixedvar['old']->id, $mixedvar['old']->leaddepid);
                    break;
            }
        }
        return true;
    }

    /**
     * Запустить обработку периодических процессов
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

    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
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
    // Методы, предусмотренные интерфейсом workflow
    // **********************************************
    /**
     * Возвращает код справочника, в котором хранятся отслеживаемые объекты
     * @return string
     * @access public
     */
    public function get_storage()
    {
        return 'departments';
    }

    /**
     * Возвращает массив всех состояний,   
     * в которых может находиться экземпляр объекта,
     * обрабатываемый этим плагином
     * @return array
     * @access public
     */
    public function get_list()
    {
        return array('draft' => $this->dof->get_string('status:draft', $this->get_storage(), NULL, 'workflow'),
                     'active' => $this->dof->get_string('status:active', $this->get_storage(), NULL, 'workflow'),
                     'deleted' => $this->dof->get_string('status:deleted', $this->get_storage(), NULL, 'workflow'));
    }

    /** Возвращает массив метастатусов
     * @param string $type - тип списка метастатусов
     *               'active' - активный 
     *               'actual' - актуальный
     *               'real' - реальный
     *               'junk' - мусорный
     * @return array
     */
    public function get_meta_list($type)
    {
        switch ( $type )
        {
            case 'active':
                return array('active' => $this->dof->get_string('status:active', $this->code(), NULL, 'workflow'));
            case 'actual':
                return array('active' => $this->dof->get_string('status:active', $this->code(), NULL, 'workflow'));
            case 'real':
                return array('draft'  => $this->dof->get_string('status:draft', $this->code(), NULL, 'workflow'),
                             'active' => $this->dof->get_string('status:active', $this->code(), NULL, 'workflow'));
            case 'junk':
                return array('deleted' => $this->dof->get_string('status:deleted', $this->code(), NULL, 'workflow'));
            default:
                dof_debugging('workflow/' . $this->code() . ' get_meta_list.This type of metastatus does not exist', DEBUG_DEVELOPER);
                return array();
        }
    }

    /**
     * Возвращает имя статуса
     * @param string status - название состояния
     * @return string
     * @access public
     */
    public function get_name($status)
    {
        $list = $this->get_list();
        if ( isset($list[$status]) )
        {
            return $list[$status];
        }
        return '';
    }

    /**
     * Возвращает массив состояний,
     * в которые может переходить объект 
     * из текущего состояния  
     * @param int id - id объекта
     * @return mixed array - массив возможных состояний или false
     * @access public
     */
    public function get_available($id)
    {
        // Получаем объект из ages
        if ( !$obj = $this->dof->storage($this->get_storage())->get($id) )
        {
            // Объект не найден
            return false;
        }
        // Определяем возможные состояния в зависимости от текущего статуса
        switch ( $obj->status )
        {
            case 'active':   // переход из статуса "идет"
                $statuses = array('deleted' => $this->get_name('deleted'));
                break;
            case 'deleted':  // переход из статуса "отменен"
                $statuses = array();
                break;
            case 'draft':       // переход из статуса "запланирован"
                $statuses = array('active' => $this->get_name('active'),
                    'deleted' => $this->get_name('deleted'));
                break;
            default: $statuses = array('draft' => $this->get_name('draft'));
        }

        return $statuses;
    }

    /**
     * Переводит экземпляр объекта с указанным id в переданное состояние
     * @param int id - id экземпляра объекта
     * @param string status - название состояния
     * @return boolean true - удалось перевести в указанное состояние, 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function change($id, $status, $opt = null)
    {
        if ( !$department = $this->dof->storage($this->get_storage())->get($id) )
        {// Назначение не найдено
            return false;
        }

        if ( !$list = $this->get_available($id) )
        {// Ошибка получения статуса для объекта';
            return false;
        }
        if ( !isset($list[$status]) )
        {// Переход в данный статус из текущего невозможен';
            return false;
        }

        // Меняем статус';
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = $status;

        $flag = $this->dof->storage($this->get_storage())->update($obj);
        $storage = $this->dof->storage($this->get_storage());
        if ( $flag )
        {
            switch ( $status )
            {
                case 'active':
                    break;
                case 'deleted':
                    //Ищем все подразделения, под удаляемым
                    if ( $deps = $storage->get_records(array('leaddepid' => $id)) )
                    {// если есть дочерние перекинем их на родителя
                        foreach ( $deps as $dep )
                        {
                            $obj = new stdClass();
                            $obj->id = $dep->id;
                            $obj->leaddepid = $department->leaddepid;
                            $flag = ($flag AND $storage->update($obj));
                        }
                    }
                    $obj = new stdClass();
                    $obj->id = $id;
                    $obj->status = 'deleted';
                    $flag = ($flag AND $storage->update($obj));
                    break;
                default:
            }
        }
        if ( !$flag )
        {// какому-то подразделению не удалось изменить статус - вернем исходное состояние
            $storage->update($department);
            // сообщим о неудачной операции
            return false;
        }
        $this->dof->storage('statushistory')->change_status($this->get_storage(), intval($id), $status, $department->status, $opt);
        return $flag;
    }

    /**
     * Инициализируем состояние объекта
     * @param int id - id экземпляра
     * @return boolean true - удалось инициализировать состояние объекта 
     * false - не удалось перевести в указанное состояние
     * @access public
     */
    public function init($id)
    {
        // Получаем объект из справочника
        if ( !$obj = $this->dof->storage($this->get_storage())->get($id) )
        {// Объект не найден
            return false;
        }
        // Меняем статус
        $obj = new stdClass();
        $obj->id = intval($id);
        $obj->status = 'draft';
        return $this->dof->storage($this->get_storage())->update($obj);
    }
    // **********************************************
    //       Методы для работы с полномочиями
    // **********************************************  

    /** Получить список параметров для фунции has_right()
     * 
     * @return object - список параметров для фунции has_right()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $personid
     */
    protected function get_access_parametrs($action, $objectid, $personid, $depid = null)
    {
        $result = new stdClass();
        $result->plugintype = $this->type();
        $result->plugincode = $this->code();
        $result->code = $action;
        $result->personid = $personid;
        $result->departmentid = $depid;
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid = $objectid;
        if ( !$objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        } else
        {// если указан - то установим подразделение
            $result->departmentid = $this->dof->storage($this->code())->get_field($objectid, 'departmentid');
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
                        $acldata->personid, $acldata->departmentid, $acldata->objectid);
    }

    /** Возвращает стандартные полномочия доступа в плагине
     * @return array
     *  a[] = array( 'code'  => 'код полномочия',
     *               'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        $a['changestatus'] = array('roles' => array('manager'));

        return $a;
    }

    // **********************************************
    // Собственные методы
    // **********************************************
    /**
     * Конструктор
     * @param dof_control $dof - это $DOF
     * объект с методами ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
}
?>