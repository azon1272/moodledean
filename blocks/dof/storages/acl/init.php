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

/** Полномочия
 * 
 */
class dof_storage_acl extends dof_storage
{

    /**
     * @var dof_control
     */
    protected $dof;

    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $DB;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ( $oldversion < 2012031100 )
        {//удалим enum поля
            // для поля plugintype
            if ( $this->dof->moodle_version() <= 2011120511 )
            {
                $field = new xmldb_field('plugintype', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'id');
                $dbman->drop_enum_from_field($table, $field);
            }
        }
        return true; // уже установлена самая свежая версия
    }

    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
        return 2016071500;
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
        return 'paradusefish';
    }

    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }

    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'acl';
    }

    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage' => array('aclwarrants' => 2011040500,
                                        'aclwarrantagents' => 2011040500));
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
        return array('storage' => array('aclwarrants' => 2011040500,
                                        'aclwarrantagents' => 2011040500));
    }

    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype' => 'storage', 'plugincode' => 'acl', 'eventcode' => 'delete'),
                     array('plugintype' => 'storage', 'plugincode' => 'acl', 'eventcode' => 'insert'));
    }

    /** Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Просим запускать крон не чаще раза в 15 минут
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
        $flag = true;
        if ( $gentype === 'storage' AND $gencode === 'acl' AND $eventcode === 'delete' )
        {
            if ( $list = $this->get_child_acls($mixedvar['old']) )
            {
                foreach ( $list as $record )
                {
                    $flag = ( $flag AND $this->delete($record->id) );
                }
            }
        }
        if ( $gentype === 'storage' AND $gencode === 'acl' AND $eventcode === 'insert' )
        {
            if ( $list = $this->dof->storage('aclwarrants')->get_records(array('parentid' => $mixedvar['new']->aclwarrantid)) )
            {
                foreach ( $list as $record )
                {
                    $mixedvar['new']->aclwarrantid = $record->id;
                    $flag = ( $flag AND $this->insert($mixedvar['new']) );
                }
            }
        }
        return $flag;
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
        if ( $code === 'addusepitemsprogramm' )
        {
            // Сопоставим активные cpasseed с cstream
            return $this->add_use_pitems_programm();
        }
        return true;
    }

    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_acl';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    /** Определяет, предоставляет ли плагин список собственных полномочий
     * 
     * @return bool
     * @param string $plugintype
     * @param string $plugincode
     */
    public function save_roles($plugintype, $plugincode, $acldata)
    {
        if ( empty($acldata) )
        {
            return $this->uninstall_plugin_acl($plugintype, $plugincode);
        }

        return $this->upgrade_plugin_acl($plugintype, $plugincode, $acldata);
    }

    /** Инсталирует полномочия в плагин
     * @param string $plugintype - тип плагина, реализовавшего полномочия
     * @param string $plugincode - код плагина, реализовавшего полномочия
     * @param array $acldata - массив стандартных полномочий
     * @return bool true or false
     */
    private function install_plugin_acl($plugintype, $plugincode, $acldata)
    {
        if ( !is_array($acldata) )
        {// массив - не массив
            // ошибка
            return false;
        }
        if ( empty($acldata) )
        {// массив полномочий пустой - все хорошо, ничего устанавливать не надо
            return true;
        }
        $flag = true;
        foreach ( $acldata as $code => $aclelement )
        {// для каждого полномочия
            if ( !is_array($aclelement['roles']) )
            {// если роль строка - превращаем ее в массив
                $aclelement['roles'] = array($aclelement['roles']);
            }
            foreach ( $aclelement['roles'] as $role )
            {// создаем объект для вставки
                if ( !$warrant = $this->dof->storage('aclwarrants')->get_records_select
                        ('code=\'' . $role . '\' AND linktype=\'core\' 
                        AND linkid=0 AND status IN (\'draft\',\'active\')') )
                {// мандаты нет - устанавливать нельзя
                    $flag = false;
                    continue;
                }
                $acl = new stdClass();
                $acl->plugintype = $plugintype;
                $acl->plugincode = $plugincode;
                $acl->aclwarrantid = current($warrant)->id;
                $acl->code = $code;
                $acl->objectid = 0;
                $flag = ($flag AND (bool) $this->insert($acl));
            }
        }
        return $flag;
    }

    /** При удалении ЛЮБОГО плагина удаляет все
     * его записи из таблицы мандат(acl)
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @return bool
     */
    public function uninstall_plugin_acl($plugintype, $plugincode)
    {
        $flag = true;
        // ищем полномочия по типу и коду
        if ( $acls = $this->get_records(array('plugintype' => $plugintype, 'plugincode' => $plugincode)) )
        {// передираем по одному наши полномочия
            foreach ( $acls as $acl )
            {// ищем записи с таким полномочием в таблице выдачи доверенностей
                // удаляем и из acl
                $flag = $flag && $this->delete($acl->id, true);
            }
        }
        return $flag;
    }

    /** Обновляет настройки заданного плагина
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @param array $acldata - список настроек (формат задаётся функцией config_default)
     * @return bool - true, если всё получилось, false, если что-то пошло не так
     */
    public function upgrade_plugin_acl($plugintype, $plugincode, $acldata)
    {
        if ( !isset($acldata) OR ! is_array($acldata) )
        {
            return false;
        }
        // добавим везде root к каждой роле
        foreach ( $acldata as $action => $roles )
        {
            if ( !in_array('root', $roles['roles']) )
            {// нет такой роли - добавим
                $roles['roles'][] = 'root';
                $acldata[$action] = $roles;
            }
        }
        //Сюда будем записывать права, которые нужно удалить
        $delete = array();
        $acl = $this->prefix() . $this->tablename();
        $aclwarrants = $this->dof->storage('aclwarrants')->prefix() .
                       $this->dof->storage('aclwarrants')->tablename();
        //Для начала получим общий список базовых ролей и их id
        $sql = "SELECT code, id FROM " . $aclwarrants . " WHERE parentid = '0' AND status = 'active'";
        if ( !$warrants = $this->dof->storage('aclwarrants')->get_records_sql($sql) )
        {//Не нашли ни одной базовой роли - ошибка
            return false;
        }
        //Подготовим запрос, объединяющий данные трёх таблиц
        //$sql = "SELECT CONCAT(".$aclwarrants.".id, ".$acl.".id ) as id, ".$acl.".id as aclid, ";
        $sql = "SELECT $acl.id as aclid, " .
                $aclwarrants . ".code as role, " . $acl . ".code FROM " . $aclwarrants . ", " . $acl . " WHERE " .
                $acl . ".aclwarrantid = " . $aclwarrants . ".id AND " . $acl . ".plugintype = '" . $plugintype .
                "' AND " . $acl . ".plugincode = '" . $plugincode . "' AND " . $acl . ".objectid = '0' AND " .
                $aclwarrants . ".status = 'active' AND " . $aclwarrants . ".parentid = '0'";
        //print_object($sql);
        if ( ( $list = $this->get_records_sql($sql) ) AND is_array($list) )
        {// Нашли настройки, обработаем их
            $oldacldata = array();
            foreach ( $list as $record )
            {//Запишем права в нужном нам формате
                $oldacldata[$record->code][$record->role] = $record->aclid;
            }
            foreach ( $oldacldata as $code => $properties )
            {//проверим соответствие старых и новых данных
                if ( isset($acldata[$code]) )
                {//Если старое право есть в новом списке
                    foreach ( $properties as $role => $aclid )
                    {
                        if ( !is_array($acldata[$code]['roles']) )
                        {// если роль строка - превращаем ее в массив
                            $acldata[$code]['roles'] = array($acldata[$code]['roles']);
                        }
                        $key = array_search($role, $acldata[$code]['roles']);
                        if ( $key !== false )
                        {//Если связь права с ролью из нового списка уже есть в старом
                            //Просто забиваем на него
                            unset($acldata[$code]['roles'][$key]);
                        } else
                        {
                            $delete[$code][$role] = $aclid;
                        }
                    }
                    if ( !$acldata[$code]['roles'] )
                    {//Если все связи права с ролями совпали и список пуст
                        //забиваем на право
                        unset($acldata[$code]);
                    }
                } else
                {//Если старого права в новом списке нет
                    //Его следует удалить
                    $delete[$code] = $properties;
                }
            }
        }
        $flag = true;
        if ( $acldata )
        {// Все права из списка новых прав, которых не было среди старых, запишем в справочник acl
            foreach ( $acldata as $capability => $properties )
            {
                if ( !is_array($properties['roles']) )
                {// если роль строка - превращаем ее в массив
                    $properties['roles'] = array($properties['roles']);
                }
                $obj = new stdClass();
                $obj->plugintype = $plugintype;
                $obj->plugincode = $plugincode;
                $obj->objectid = 0;
                $obj->code = $capability;
                foreach ( $properties['roles'] as $role )
                {
                    if ( isset($warrants[$role]) )
                    {
                        $obj->aclwarrantid = $warrants[$role]->id;
                        $flag = $flag && (bool) $this->insert($obj);
                    }
                }
            }
        }
        if ( $delete )
        {// Удалим устаревшие права
            foreach ( $delete as $ids )
            {
                foreach ( $ids as $id )
                {
                    $flag = $flag && (bool) $this->delete($id);
                }
            }
        }

        return $flag;
    }

    /** Обертка для функции has_right
     * Проверяет права конкретного пользователя для конкретного плагина
     * Функция создана для того чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     * 
     * @return bool
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     *              формат объекта:
     *              $acldata->plugintype - тип плагина
     *              $acldata->plugincode - код плагина
     *              $acldata->code - код права
     *              $acldata->personid[optional] - id персоны в таблице persons, для которого проверяются права
     *              $acldata->departmentid[optional] - id подразделения в таблице departments
     *              $acldata->objectid[optional] - id объекта, право на действия над которым проверяется
     */
    public function acl_check_access_paramenrs($acldata)
    {
        $obj = new stdClass();
        $obj->plugintype = null;
        $obj->plugincode = null;
        $obj->code = null;
        $obj->personid = 0;
        $obj->departmentid = 0;
        $obj->objectid = 0;

        foreach ( $acldata as $field => $value )
        {// Избегаем проблем с необъявленными переменными
            $obj->$field = $value;
        }
        return $this->dof->storage('acl')->
                        has_right($obj->plugintype, $obj->plugincode, $obj->code, $obj->personid, $obj->departmentid, $obj->objectid);
    }

    /** Проверяет права конкретного пользователя для конкретного плагина
     * 
     * @param string $plugintype - тип плагина
     * @param string $plugincode - код плагина
     * @param string $code - код права
     * @param int $userid - id персоны в таблице persons, для которого проверяются права
     * @param int $departmentid - id подразделения в таблице departments
     * @param int $objectid - id объекта, право на действия над которым проверяется
     * @return bool : false - нет;
     *                true - есть.
     */
    public function has_right($plugintype, $plugincode, $code, $userid = 0, $departmentid = 0, $objectid = 0)
    {
        global $DB;
        $params = array();

        //Запишем названия таблиц, из которых будем доставать данные
        $acl = $this->prefix() . $this->tablename();
        $aclwarrants = $this->dof->storage('aclwarrants')->prefix() .
                $this->dof->storage('aclwarrants')->tablename();
        $aclwarrantagents = $this->dof->storage('aclwarrantagents')->prefix() .
                $this->dof->storage('aclwarrantagents')->tablename();

        $warrantscodessql = "SELECT COUNT(a.id) FROM " . $aclwarrantagents . " as awa, " . $acl . " as a";
        // Если нам передали несколько departmentid, то заменим слеши на запятые
        $deps = $this->dof->storage('departments')->change_path_department($departmentid);
        if ( $deps )
        {
            $departments = $this->dof->storage('departments')->prefix() .
                    $this->dof->storage('departments')->tablename();
            $warrantscodessql .= ", " . $departments . " as dep";
            $deps = "( awa.departmentid = dep.id AND dep.id IN (" . $deps . ") ) OR ";
        }
        //Запрос для получения id требуемого права
        $obj = "";
        if ( $objectid )
        {// есть объект - переопределим objectid
            $obj = "OR (a.objectid = :objectid )";
            $params['objectid'] = $objectid;
        }
        // создаем условие для того чтобы выбрать только те права, которые действительны
        // в переданном подразделении (или родительских подразделениях)
        $depart = "awa.departmentid = 0 ) AND (a.objectid = 0 )) " . $obj . ")";

        $warrantscodessql .= " WHERE awa.aclwarrantid = a.aclwarrantid AND awa.personid = :personid
                              AND ( ( awa.begindate + awa.duration ) > :time
                              OR awa.duration = 0 ) AND awa.status = :status AND a.plugintype = :plugintype
                              AND awa.basepcode != :awaplugintype
                              AND a.plugincode = :plugincode AND a.code = :code AND ((( " . $deps . $depart;

        $params['personid'] = $userid;
        $params['time'] = time();
        $params['status'] = 'active';
        $params['plugintype'] = $plugintype;
        $params['awaplugintype'] = 'departments'; //@todo удалить
        $params['plugincode'] = $plugincode;
        $params['code'] = $code;

        //Вернём ответ на вопрос, есть ли в БД запись, соответствующая входным данным
        return (bool) $DB->count_records_sql($warrantscodessql, $params);
    }

    /** Возвращает список прав, с теми же параметрами, что и переданное,
     * привязанных к мандатам, являющихся дочерними для мандата данного права
     * @param object $aclsample - пример права, подобные которому  нужно найти
     * @return 
     */
    public function get_child_acls($aclsample)
    {
        global $DB;
        $acl = $this->prefix() . $this->tablename();
        $aclwarrants = $this->dof->storage('aclwarrants')->prefix() .
                $this->dof->storage('aclwarrants')->tablename();
        // задаем параметры sql-запроса (для того чтобы избежать ошибок с фильтрацией параметров)
        $params = array();
        $params['code'] = $aclsample->code;
        $params['plugintype'] = $aclsample->plugintype;
        $params['plugincode'] = $aclsample->plugincode;
        $params['objectid'] = $aclsample->objectid;
        $params['parentid'] = $aclsample->aclwarrantid;
        $sql = "SELECT " . $acl . ".id
                FROM " . $acl . ", " . $aclwarrants . "
                WHERE " . $acl . ".code = ?
                AND " . $acl . ".plugintype = ?
                AND " . $acl . ".plugincode = ?
                AND " . $acl . ".objectid = ?
                AND " . $acl . ".aclwarrantid = " . $aclwarrants . ".id
                AND " . $aclwarrants . ".parentid = ?";
        return $DB->get_records_sql($sql, $params);
    }

    /** Возвращает массив стандартных ролей, которыми обладает пользователь
     * в данном подразделении
     * @param integer $userid - id пользователя
     * @param integer $depid - id подразделения, для которого ищуться права(0 по умолчанию)
     * @return array $roles - массив прав
     */
    public function show_standroles($userid, $depid = 0)
    {
        $dep = '';
        $roles = array();
        if ( $depid )
        {
            $dep = 'departmentid';
        }
        // соберем все назначения
        $aclwarrants = $this->dof->storage('aclwarrantagents')->
                get_records(array('personid' => $userid, $dep => $depid), 'aclwarrantid', 'id,aclwarrantid');
        // запишем в массив(повторяющие уберем)
        $masid = array();
        if ( $aclwarrants )
        {
            foreach ( $aclwarrants as $obj )
            {
                if ( !in_array($obj->aclwarrantid, $masid) )
                {
                    $masid[] = $obj->aclwarrantid;
                }
            }
        }
        //перебираем 
        foreach ( $masid as $id )
        {// если не мандата - продолжаем(у мандаты родителя нет(=0))
            do
            {
                if ( $aclwar = $this->dof->storage('aclwarrants')->get($id) )
                {// нашли полномочие
                    if ( $aclwar->parentid )
                    {
                        $id = $aclwar->parentid;
                    } elseif ( !in_array($aclwar->code, $roles) )
                    {// добавим в массив
                        $roles[] = $aclwar->code;
                    }
                } else
                {// чтоб ошибки не было дальше
                    $aclwar->parentid = 0;
                }
            } while ( $aclwar->parentid );
        }
        return $roles;
    }

    /** Возвращает права конкретного пользователя 
     * 
     * @param int $personid - id пользователя
     * @param int $departmentid - id подразделения
     * @return array|false
     */
    public function get_right_person($personid = 0, $departmentid = 0)
    {
        //Запишем названия таблиц, из которых будем доставать данные
        $acl = $this->prefix() . $this->tablename();
        $aclwarrants = $this->dof->storage('aclwarrants')->prefix() .
                $this->dof->storage('aclwarrants')->tablename();
        $aclwarrantagents = $this->dof->storage('aclwarrantagents')->prefix() .
                $this->dof->storage('aclwarrantagents')->tablename();
        //Запрос для получения id требуемого права
        $warrantscodessql = "SELECT CONCAT(a.id, CONCAT('-', awa.id)) as aid_awaid, a.*,awa.departmentid FROM " . $acl . " as a " .
                "INNER JOIN " . $aclwarrantagents . " as awa ON awa.aclwarrantid = a.aclwarrantid";
        $deps = "";
        if ( $departmentid )
        {
            $deps = $this->dof->storage('departments')->change_path_department($departmentid);
            if ( $deps )
            {
                $departments = $this->dof->storage('departments')->prefix() .
                        $this->dof->storage('departments')->tablename();
                $warrantscodessql .= ", " . $departments . " as dep";
                $deps = "( awa.departmentid = dep.id AND dep.id IN (" . $deps . ") ) OR ";
            }
            $deps = " AND ( " . $deps . "awa.departmentid = '0' ) ";
        }
        $warrantscodessql .= " WHERE awa.personid = '" .
                $personid . "' AND ( ( awa.begindate + awa.duration ) > '" . time() .
                "' OR awa.duration = '0' ) AND awa.status = 'active'" . $deps .
                            " GROUP BY a.id, awa.departmentid, awa.id " .
                            " ORDER BY a.plugintype,a.plugincode";
        //Вернём ответ на вопрос, есть ли в БД запись, соответствующая входным данным
        //print $warrantscodessql;
        return $this->get_records_sql($warrantscodessql);
    }

    /** Возвращает список пользователей имеющих данное право
     * 
     * @param int $aclid - id права
     * @param int $departmentid - id подразделения
     * @return array|false
     */
    public function get_persons_acl($aclid = 0, $departmentid = 0)
    {
        //Запишем названия таблиц, из которых будем доставать данные
        $acl = $this->prefix() . $this->tablename();
        $aclwarrants = $this->dof->storage('aclwarrants')->prefix() .
                $this->dof->storage('aclwarrants')->tablename();
        $aclwarrantagents = $this->dof->storage('aclwarrantagents')->prefix() .
                $this->dof->storage('aclwarrantagents')->tablename();
        $persons = $this->dof->storage('persons')->prefix() .
                $this->dof->storage('persons')->tablename();
        //Запрос для получения id требуемого права
        $warrantscodessql = "SELECT DISTINCT pr.id,awa.departmentid FROM " . $aclwarrantagents . " as awa, " .
                $acl . " as a , " . $persons . " as pr";
        $deps = "";
        if ( $departmentid )
        {
            $deps = $this->dof->storage('departments')->change_path_department($departmentid);
            if ( $deps )
            {
                $departments = $this->dof->storage('departments')->prefix() .
                        $this->dof->storage('departments')->tablename();
                $warrantscodessql .= ", " . $departments . " as dep";
                $deps = "( awa.departmentid = dep.id AND dep.id IN (" . $deps . ") ) OR ";
            }
            $deps = " AND ( " . $deps . "awa.departmentid = '0' )";
        }
        $aclobj = $this->get($aclid);
        $warrantscodessql .= " WHERE a.code = ? AND a.plugintype = ?
                            AND a.plugincode = ? AND awa.aclwarrantid = a.aclwarrantid AND
                            pr.id = awa.personid AND ( ( awa.begindate + awa.duration ) > '" . time() .
                "' OR awa.duration = '0' ) AND awa.status = 'active'" . $deps . ' ORDER BY pr.sortname';
        // ORDER BY ".$departments.".depth DESC";
        $params = array();
        $params['code'] = $aclobj->code;
        $params['plugintype'] = $aclobj->plugintype;
        $params['plugincode'] = $aclobj->plugincode;

        //Вернём ответ на вопрос, есть ли в БД запись, соответствующая входным данным
        return $this->get_records_sql($warrantscodessql, $params);
    }

    /** Возвращает можно ли указанному пользователю 
     * устанавливать выбранные статусы
     * @param int $plugintype - тип плагина
     * @param int $plugincode - код плагина
     * @param array $statuslist - статусы, которые надо проверить
     * @param int $departmentid - id подразделения
     * @param int $personid - id пользователя
     * @param int $objectid - id объекта(записи)
     * @return array|false
     */
    public function get_usable_statuses($plugintype, $plugincode, $statuslist, $departmentid = 0, $personid = 0, $objectid = 0)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('admin')
                OR $this->dof->is_access('manage') )
        {// манагеру можно все
            return $statuslist;
        }
        if ( $acl = $this->dof->plugin($plugintype, $plugincode)->need_plugins() AND isset($acl['storage']['acl']) )
        {// поддреживает ACL
            if ( $this->has_right($plugintype, $plugincode, 'changestatus', $personid, $departmentid, $objectid) )
            {// есть права на все статусы - и вернем все статусы
                return $statuslist;
            }
            // нет права на ВСЕ статусы - перебираем тогда их все по оджному 
            $statuts = array();
            if ( !is_array($statuslist) )
            {// неправильного формата данные - вернем пустой массив
                return $statuts;
            }
            foreach ( $statuslist as $status )
            {
                if ( $this->has_right($plugintype, $plugincode, 'changestatus:to:' . $status, $personid, $departmentid, $objectid) )
                {// есть право - добавим статус
                    $statuts[] = $status;
                }
            }
            return $statuts;
        }
        // вернем стаусы, т.к. плагин не поддреживает ACL
        return $statuslist;
    }

    /** Получить список статусов, разрешенных workflow и acl - вариант для select-элементов форм
     * @param int $plugintype - тип плагина
     * @param int $plugincode - код плагина
     * @param array $statuslist - статусы, которые надо проверить
     * @param int $departmentid - id подразделения
     * @param int $personid - id пользователя
     * @param int $objectid - id объекта(записи)
     * @return array|false
     */
    public function get_usable_statuses_select($plugintype, $plugincode, $statuslist, $departmentid = 0, $personid = 0, $objectid = 0)
    {
        // оставляем только те статусы, которые пользователь имеет право изменить
        $available = $this->
                get_usable_statuses($plugintype, $plugincode, $statuslist, $departmentid, $personid, $objectid);

        $result = array();
        foreach ( $available as $code )
        {// перебираем все разрешенные статусы оставляем в результате только их
            $result[$code] = $this->dof->$plugintype($plugincode)->get_name($code);
        }

        return $result;
    }

    /** Отфильтровать список объектов, убрав те, на которые пользователь не имеет права
     * 
     * @param array $values - массив значений, ключами которого являются id записей в каком-либо хранилище (storage)
     * @param array $permissions - массив прав, которые нужно проверить у каждого элемента
     *                             Формат массива сответствует формату функции has_right() в плагине acl
     *                             Пример:
     *                             array(
     *                                 array('plugintype'=>'storage', 
     *                                       'plugincode'=>'persons', 
     *                                       'code'=>'use', 
     *                                       'departmentid' => 2,
     *                                       'userid'=> 55),
     *                                 array('plugintype'=>'workflow', 
     *                                       'plugincode'=>'persons', 
     *                                       'code'=>'changestatus'),
     *                                 ...
     *                             )
     * @param string $mode [optional] - режим проверки
     *                                AND - в итоговый массив будут включены все записи, обладающие
     *                                      ВСЕМ списком прав, указанных в массиве $permissions
     *                                OR -  в итоговый массив будут включены все записи, обладающие
     *                                      ХОТЯ БЫ ОДНИМ правом из массива permissions
     */
    public function get_acl_filtered_list($values, $permissions, $mode = 'AND')
    {
        $result = array();

        if ( !is_array($values) OR empty($values) )
        {// список объектов не задан - вернем пусмтой массив
            return array();
        }
        if ( !$this->_dof_acl_permissions_is_correct($permissions) )
        {// список прав пуст - просто вернем весь исходный массив
            return $values;
        }

        foreach ( $values as $id => $value )
        {// проверяем права для каждого элемента
            if ( $this->_dof_object_id_is_allowed($permissions, $id, $mode) )
            {// права проверены, и мы определили, что пользователь обладает
                // нужным набором прав. Включаем объект в итоговый массив
                $result[$id] = $value;
            }
        }

        return $result;
    }

    /** Проверить правильность структуры массива списка прав для функции get_acl_filtered_list
     * 
     * @param array $permissions - массив прав, которые нужно будет проверить у каждого элемента
     * @return bool
     */
    private function _dof_acl_permissions_is_correct($permissions)
    {
        if ( !is_array($permissions) OR empty($permissions) )
        {
            return false;
        }

        foreach ( $permissions as $permission )
        {// проверяем каждое полномочие на наличие и правильность всех полей
            if ( !is_array($permission) )
            {
                return false;
            }

            if ( !isset($permission['plugintype']) OR ! isset($permission['plugincode']) OR ! isset($permission['code']) )
            {// отсутствуют необходимые поля
                return false;
            }

            if ( !$this->dof->plugin_exists($permission['plugintype'], $permission['plugincode']) )
            {// плагин указан неправильно
                return false;
            }
        }

        return true;
    }

    /** Проверить список прав для одного объекта БД. Эта функция решает, попадет ли объект
     * с переданными id в итоговый массив или нет
     * 
     * @param array  $permissions - массив прав, которые нужно проверить
     * @param int    $id - id объекта, для которого проверяются права
     * @param string $mode - режим проверки
     *                         AND - в итоговый массив будут включены все записи, обладающие
     *                               ВСЕМ списком прав, указанных в массиве $permissions
     *                         OR -  в итоговый массив будут включены все записи, обладающие
     *                               ХОТЯ БЫ ОДНИМ правом из массива permissions
     * 
     * @return bool
     *              true - если объект должен попасть в итоговый список
     *              false - если объект не должен попасть в итоговый список
     */
    private function _dof_object_id_is_allowed($permissions, $id, $mode)
    {
        if ( $id <= 0 )
        {// служебные элементы select-списка пропускаем
            return true;
        }
        foreach ( $permissions as $permission )
        {// для каждого объекта проверяем полномочие
            $userid = null;
            $departmentid = null;
            if ( isset($permission['userid']) AND $permission['userid'] )
            {// если нужно проверить права для конкретного пользователя
                $userid = $permission['userid'];
            }
            if ( isset($permission['departmentid']) )
            {// нужно проверить права для конкретного подразделения
                // (проверка "AND $permission['departmentid']" не включена здесь на случай
                // если нам передали departmentid=0, это нормальная ситуация)
                $departmentid = $permission['departmentid'];
            }

            $plugintype = $permission['plugintype'];
            $plugincode = $permission['plugincode'];
            if ( $this->dof->$plugintype($plugincode)->
                            is_access($permission['code'], $id, $userid, $departmentid) )
            {// пользователь обладает указанным правом из списка
                if ( $mode == 'OR' )
                {// для режима OR - этого достаточно для добавления в итоговый массив 
                    // нет смысла проверять остальные права
                    return true;
                }
            } else
            {// пользователь не обладает одним правом из списка
                if ( $mode == 'AND' )
                {// для режима AND этого достаточно для того чтобы исключить элемент из списка
                    // нет смысла проверять остальные права
                    return false;
                }
            }
        }
        // если дошли до сюда - то все полномочия проверены. Смотрим, чего от нас хотели.
        if ( $mode == 'AND' )
        {// все полномочия проверены - и мы не встретили ни одного отказа - 
            // добавляем элемент в итоговый массив
            return true;
        } elseif ( $mode == 'OR' )
        {// все полномочия проверены - и мы не встретили ни одного подтверждения -
            // исключаем элемент из итогового массива 
            return false;
        }
    }
    /* Сохраняем права по доверенности
     * @param int $aclwarrantid - id доверенности, к которой привязываются права
     * @param array $acls  - массив объектов с правами
     * @return bool true - если все успешно, false - если хоть какое-то право не добавилось.
     */

    public function add_warrant_acls($aclwarrantid, $acls)
    {
        $rez = true;
        if ( empty($aclwarrantid) OR ! is_int_string($aclwarrantid) )
        {// данные неверного формата
            return false;
        }
        foreach ( $acls as $acl )
        {// для каждого создаем новое право
            $obj = new stdClass();
            $obj->plugintype = $acl->plugintype;
            $obj->plugincode = $acl->plugincode;
            $obj->code = $acl->code;
            $obj->objectid = $acl->objectid;
            $obj->aclwarrantid = $aclwarrantid;
            if ( $acl = $this->get_record((array) $obj) )
            {// запись должна быть уникальна - если такая есть - вернем ее id
                return $acl->id;
            }
            $rez = $rez && $this->insert($obj);
        }
        // возвращеам результат
        return $rez;
    }
    /* Обновляем права по доверенности
     * @param int $aclwarrantid - id доверенности, к которой привязываются права
     * @param array $acls  - массив объектов с правами
     * @return bool
     */

    public function update_warrant_acls($aclwarrantid, $acls)
    {
        $rez = true;
        if ( empty($aclwarrantid) OR ! is_int_string($aclwarrantid) )
        {// данные неверного формата
            return false;
        }
        // сначала удаляем все права для данной доверенности
        if ( $oldacls = $this->get_records(array('aclwarrantid' => $aclwarrantid)) )
        {// если они есть
            foreach ( $oldacls as $acl )
            {// удалим по одному
                $rez = $rez && $this->delete($acl->id);
            }
        }
        // сохраняем новые
        $rez = $rez && $this->add_warrant_acls($aclwarrantid, $acls);
        return $rez;
    }

    /** Добавляет использование предметов программы, если есть право использования программы
     * @return bool
     */
    public function add_use_pitems_programm()
    {
        $result = true;
        // ищем полномочия по использованию программы
        if ( $acls = $this->get_records(array('plugintype' => 'storage', 'plugincode' => 'programms', 'code' => 'use')) )
        {
            foreach ( $acls as $acl )
            {
                if ( $acl->objectid == 0 )
                {// объекта нет - создавать нечего
                    continue;
                }
                if ( !$pitems = $this->dof->storage('programmitems')->get_records(array('programmid' => $acl->objectid)) )
                {// предметов нет - создавать нечего
                    continue;
                }
                foreach ( $pitems as $pitem )
                {
                    if ( !$this->count_records_select("plugintype='storage' AND plugincode='programmitems'
                                             AND code = 'use' AND objectid='{$pitem->id}' 
                                             AND aclwarrantid = {$acl->aclwarrantid}") )
                    {// если такого права еще нет - добавим
                        $acl2 = new stdClass();
                        $acl2->plugintype = 'storage';
                        $acl2->plugincode = 'programmitems';
                        $acl2->aclwarrantid = $acl->aclwarrantid;
                        $acl2->code = 'use';
                        $acl2->objectid = $pitem->id;
                        $result = ($result AND (bool) $this->insert($acl2));
                    }
                }
            }
        }
        return $result;
    }

    /** Возвращает список прав сгруппированных по plugintype и plugincode
     * @return bool
     */
    public function get_acls_group_plugintype_plugincode($conditions = array(), $sort = '', $fields = '*', $limitfrom = 0, $limitnum = 0)
    {
        $result = array();
        // ищем полномочия по использованию программы
        if ( $acls = $this->get_records($conditions, $sort, $fields, $limitfrom, $limitnum) )
        {
            foreach ( $acls as $acl )
            {
                $result[$acl->plugintype . '_' . $acl->plugincode][$acl->id] = $acl;
            }
        }
        return $result;
    }
}
?>