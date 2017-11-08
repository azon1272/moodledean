<?PHP
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

/** Здесь перечисляется минимальный набор методов, 
 * которые должен иметь любой плагин типа storage
 */
abstract class dof_storage implements dof_plugin_storage
{
    /** Устанавливает плагин в fdo
     * @return bool
     */
    public function install()
    {
        //получим путь к файлу описания таблиц плагина
        $xml = $this->dof->plugin_path('storage', $this->code(), '/db/install.xml');
        if ( file_exists($xml) )
        {//начинаем установку таблиц
            global $DB;
            try
            {// пробуем установить таблицы плагина
               $DB->get_manager()->install_from_xmldb_file($xml);
            }catch ( ddl_exception $e )
            {// @todo обработать все возможные варианты исключений и сделать
                // вывод сообщений об ошибках более подробным
                dof_mtrace(2, 'unable install table from xmldb file:'.$xml);
                return false;
            }
        }
        //таблицы нормально установились или их просто нет
        dof_mtrace(2, 'storage'.$this->code().' successfully installed');
        return true;
    }
    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $oldversion - версия установленного в системе плагина
     * @return bool
     * @access public
     */
    public function upgrade($oldversion)
    {
        return true;
    }
    /** Удаляем плагин из fdo
     * @return bool
     */
    public function uninstall()
    {
        //удаляем все таблицы плагина
        $xml = $this->dof->plugin_path('storage', $this->code(), '/db/install.xml');
        if ( file_exists($xml) )
        {// таблицы для удаления есть - удаляем
            global $DB;
            try
            {// пробуем удалить таблицы плагина
                $DB->get_manager()->delete_tables_from_xmldb_file($xml);
            }catch ( ddl_exception $e )
            {// таблицы удалить не удалось - указываем в каком именно плагине произошла ошибка
                // @todo обработать все возможные варианты исключений и сделать
                // вывод сообщений об ошибках более подробным
                print_error("Can't to delete plugin's table! (".$this->type().':'.$this->code().')');
            }
        }
        return true;
    }
    
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin_storage
    // **********************************************
    
    /** Вставляет запись в таблицу(ы) плагина 
     * @param object dataobject 
     * @param bool quiet - не генерировать событий
     * @param bool $bulk - true если операций последует много
     *                     false если обновлено будет всего несколько записей (для производительности)
     * 
     * @return mixed bool false если операция не удалась или id вставленной записи
     * @access public
     * 
     * @throws dml_exception в случае ошибки
     */
    public function insert($dataobject,$quiet=false,$bulk=false)
    {
        global $DB;
        if ( $id = $DB->insert_record($this->tablename(), $dataobject, true, $bulk) )
        {//запись вставлена
            if ( ! $quiet )
            {//надо создать событие о вставке в базу - генерим его
                if ( is_numeric($id) )
                {
                    $dataobject->id = $id;
                }
                $this->dof->send_event($this->type(),$this->code(),
                            'insert',$id,array('new'=>$dataobject));
            }
            //возвращаем id вставленой записи
            return $id;
        }else
        {//а вставить-то не удалось
            dof_debugging('Unable to insert record', DEBUG_DEVELOPER);
            return false;
        }
    }
    
    /** Удаляет запись с указанным id
     * @param int id - id записи в таблице 
     * @param bool quiet - не генерировать событий
     * @param bool $bulk - true если операций последует много
     *                     false если обновлено будет всего несколько записей (для производительности)
     * 
     * @return boolean true если запись удалена или ее нет;
     *                 false в остальных случаях
     * @access public
     * 
     * @throws dml_exception в случае ошибки
     */
    public function delete($id,$quiet=false)
    {
        global $DB;
        
        $id = intval($id);
        $conditions = array($this->idname() => $id);
        
        if ( $dataobject = $this->get($id)
             AND $DB->delete_records($this->tablename(), $conditions) )
        {
            if ( ! $quiet )
            {
                $this->dof->send_event($this->type(),$this->code(),'delete',$id,array('old'=>$dataobject));
            }
            return true;
        }else
        {
            return false;
        }
    }
    
    /** Обновляет запись данными из объекта.
     * Отсутствующие в объекте записи не изменяются.
     * Если id передан, то обновляется запись с переданным id.
     * Если id не передан обновляется запись с id, который передан в объекте
     * @param object $dataobject - данные, которыми надо заменить запись в таблице 
     * @param int  $id - id обновляемой записи
     * @param bool $quiet - не генерировать событий если true
     * @param bool $bulk - true если операций последует много
     *                     false если обновлено будет всего несколько записей (для производительности)
     * 
     * @return boolean true если обновление прошло успешно и false во всех остальных случаях
     * @access public
     * 
     * @throws dml_exception в случае ошибки
     */
    public function update($dataobject,$id = NULL,$quiet=false, $bulk=false)
    {
        global $DB;
        // Меняем id вручную
        if ( is_numeric($id) )
        {
            $dataobject->id = $id;
        }
        
        if ( ! $dataobject_old = $this->get($dataobject->id) )
        {//нет записи, которую надо обновить';
            //она нужна для генерации события
            dof_debugging('No record to update!', DEBUG_DEVELOPER);
            return false;
        }
        
        if ( $DB->update_record($this->tablename(), $dataobject, $bulk) )
        {
            if ( ! $quiet ) 
            {
                $this->dof->send_event($this->type(),$this->code(),'update',$dataobject->id,
                    array('old'=>$dataobject_old,'new'=>dof_object_merge($dataobject_old,$dataobject)));
            }
            return true;
        }else
        {
            return false;
        }
    }
    
    /** Возвращает по id 
     * @param int $conditions - id записи в таблице
     * @param int $strictness - как обрабатывать возникающие ошибки
     *            IGNORE_MISSING - если запись не найдена - то функция возвращает false
     *                             и выводит отладочное сообщение
     *            IGNORE_MULTIPLE - вернуть первое значение и игнорировать остальные,
     *                              если нашлось больше одной записи (не рекомендуется)
     *            MUST_EXIST - если запись не найдена, или найдено несколько записей
     *                         вместо одной, то будет вызвано исключение
     * @return object - запись из таблицы или false в остальных случаях
     * @access public
     * 
     * @throws dml_exception в случае ошибки
     */
    public function get($conditions, $fields='*', $strictness=IGNORE_MISSING)
    {
        if ( ! is_array($conditions) AND ! is_object($conditions) )
        {// извлечь запись только по id
            $conditions = array($this->idname() => intval($conditions));
        }else 
        {// для выбора по критериям есть метод get_record
            $conditions = (array)$conditions;
            dof_debugging('Do not use multiplie conditions in dof_storage::get().
                            Use dof_storage::get_record() instread!', DEBUG_DEVELOPER);
        }
        return $this->get_record($conditions, $fields, $strictness);
    }
    
    /** Возвращает содержимое поля по id записи и имени поля
     * @param int|array $conditions - id записи в таблице или массив
     *      условий в формате ключ/значение для извлечения 
     *      записи по нескольким критериям
     * @param string $return имя поля
     * @param int $strictness - как обрабатывать возникающие ошибки
     *              IGNORE_MISSING - если запись не найдена - то функция возвращает false
     *                               и выводит отладочное сообщение
     *              IGNORE_MULTIPLE - вернуть первое значение и игнорировать остальные,
     *                                если нашлось больше одной записи (не рекомендуется)
     *              MUST_EXIST - если запись не найдена, или найдено несколько записей
     *                           вместо одной, то будет вызвано исключение
     * @return mixed
     * 
     * @throws dml_exception в случае ошибки
     */
    public function get_field($conditions,$return, $strictness=IGNORE_MISSING)
    {
        global $DB;
        
        if ( ! is_array($conditions) )
        {// извлечь запись только по id 
            $conditions = array($this->idname() => intval($conditions));
        }
        
        return $DB->get_field($this->tablename(), $return, $conditions, $strictness);
    }
    
    /** Возвращает объект из базы по списку критериев
     * @param array $conditions - массив условий в формате ключ/значение
     *     для извлечения записи по нескольким критериям 
     * @param int $strictness - как обрабатывать возникающие ошибки
     *            IGNORE_MISSING - если запись не найдена - то функция возвращает false
     *                             и выводит отладочное сообщение
     *            IGNORE_MULTIPLE - вернуть первое значение и игнорировать остальные,
     *                              если нашлось больше одной записи (не рекомендуется)
     *            MUST_EXIST - если запись не найдена, или найдено несколько записей
     *                         вместо одной, то будет вызвано исключение
     * @return object - запись из таблицы или false в остальных случаях
     * 
     * @throws dml_exception в случае ошибки
     */
    public function get_record($conditions, $fields='*', $strictness=IGNORE_MISSING)
    {
        global $DB;
        // Пользуемся собственным where_clause() который понимает массивы в значениях
        list($select, $params) = $this->where_clause($conditions);
        
        return $DB->get_record_select($this->tablename(), $select, $params, $fields, $strictness);
    }
    
    /** 
     * Возвращает массив объектов из базы по списку критериев
     * 
     * @param array $conditions - условия в формате поле-значение. Объединяются через AND.
     *                            Значения могут быть массивами.
     * @param string $sort - по какому полю сортировать результат
     * @param string $fields - список полей, которые надо извлечь (по умолчанию извлекаются все поля)
     * @param int $limitfrom - начиная с какой записи в выборке возвращать результат
     * @param int $limitnum - сколько записей вернуть
     * 
     * @return array массив записей из таблицы.
     *         В качестве ключей массива будут использованы id записей
     * @since 2.4.0
     * @todo в версии 2.2 Moodle не поддерживает массивы в качестве значений, поэтому приходится
     *       конструировать и выполнять SQL. Нужно изменить эту ситуацию когда они допилят get_records()
     * 
     * @throws dml_exception в случае ошибки
     */
    public function get_records($conditions=array(), $sort='', $fields='*', $limitfrom=0, $limitnum=0)
    {
        global $DB;
        // Пользуемся собственным where_clause() который понимает массивы в значениях
        list($select, $params) = $this->where_clause($conditions);
        
        return $DB->get_records_select($this->tablename(), $select, $params, $sort, $fields, $limitfrom, $limitnum);
    }
    
    /** Возвращает массив объектов, выбранных по фрагменту sql-запросу после условия WHERE.
     * @param string $select - фрагмент sql-запроса
     * @param array $params - массив sql-параметров
     * @param string $sort - в каком направлении и по каким полям производится сортировка
     * @param string $fields - поля, которые надо возвратить
     * @param int $limitfrom - id, начиная с которого надо искать
     * @param int $limitnum - максимальное количество записей, которое надо вернуть
     * @return array массив записей из таблицы.
     *         В качестве ключей массива будут использованы id записей
     * @throws dml_exception в случае ошибки
     * @since 2.4.0
     */
    public function get_records_select($select, array $params=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) 
    {
        global $DB;
        
        return $DB->get_records_select($this->tablename(), $select, $params, $sort, $fields, $limitfrom, $limitnum);
    }

    /** Возвращает массив объектов, выбранных по sql-запросу.
     *
     * @param string $sql - Полноценный SQL-запрос. Первое поле будет ключом возвращаемого массива,
     * так что оно должно иметь только уникальные значения
     * @param array $params - массив sql-параметров
     * @param int $limitfrom - id, начиная с которого надо искать
     * @param int $limitnum - максимальное количество записей, которое надо вернуть
     * @return array массив записей из таблицы.
     *         В качестве ключей массива будут использованы id записей
     * @throws dml_exception в случае ошибки
     * @since 2.4.0
     */
    public function get_records_sql($sql, array $params=null, $limitfrom=0, $limitnum=0)
    {
        global $DB;
        
        return $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
    }

    /** Подсчитывает количество записей, найденных по критериям
     * @param array $conditions - массив условий для подсчета в формате ключ/значение 
     * @param string $countitem - поля, по которым производится подсчет
     * 
     * @return int 
     * @access public
     * @since 2.4.0
     * 
     * @throws dml_exception в случае ошибки
     */
    public function count_list($conditions=array(), $countitem='COUNT(*)')
    {
        global $DB;
        
        list($select, $params) = $this->where_clause($conditions);
        
        return $DB->count_records_select($this->tablename(), $select, $params, $countitem); 
    }
    
    /** Подсчитывает количество записей, выбранных по фрагменту sql-запросу после условия WHERE.
     * @param string $select - фрагмент sql-запроса
     * @param array $params - массив sql-параметров
     * @param string $countitem - поля, по которым производится подсчет
     * @return int
     * @throws dml_exception в случае ошибки
     * @since 2.4.0
     */
    public function count_records_select($select, array $params=null, $countitem="COUNT('x')")
    {
        global $DB;
        
        return $DB->count_records_select($this->tablename(), $select, $params, $countitem);
    }
    
    /** Подсчитывает количество записей, sql SELECT COUNT(...) запроса.
     * @param string $sql - Полноценный SQL-запрос для подсчета записей. 
     * @param array $params - массив sql-параметров
     * @return int 
     * @throws dml_exception в случае ошибки
     * @since 2.4.0
     */
    public function count_records_sql($sql, array $params=null)
    {
        global $DB;
        
    return $DB->count_records_sql($sql, $params);
    }
    
    /** Проверяет наличие записи в таблице
     * @param int|array $conditions - id проверяемой записи или массив условий в формате ключ/значение
     *                       для проверки по нескольким критериям
     * @return boolean true - запись найдена, false - запись не найдена
     * @access public
     * 
     * @throws dml_exception в случае ошибки
     */
    public function is_exists($conditions=array())
    {
        global $DB;
        
        if ( ! is_array($conditions) )
        {// извлечь запись только по id 
            $conditions = array($this->idname() => intval($conditions));
        }
        
        return $DB->record_exists($this->tablename(), $conditions);
    }
    
    /** Проверяет наличие записи в таблице, выбранных по фрагменту sql-запросу после условия WHERE.
     * @param string $select - фрагмент sql-запроса
     * @param array $params - массив sql-параметров
     * @return boolean true - запись найдена, false - запись не найдена
     * @throws dml_exception в случае ошибки
     * @since 2.4.0
     */
    public function is_exists_select($select, array $params=null) 
    {
        global $DB;
        
        return $DB->record_exists_select($this->tablename(), $select, $params);
    }

    /** Проверяет наличие записи в таблице, выбранных по sql-запросу.
     * @param string $sql - Полноценный SQL-запрос для подсчета записей. 
     * @param array $params - массив sql-параметров
     * @return boolean true - запись найдена, false - запись не найдена
     * @throws dml_exception в случае ошибки
     * @since 2.4.0
     */
    public function is_exists_sql($sql, array $params=null)
    {
        global $DB;
        
        return $DB->record_exists_sql($sql, $params);
    }
    
    // **********************************************
    // Собственные методы
    // **********************************************
    
    /** Возвращает название таблицы без префикса (mdl_)
     * @return text
     * @access public
     */
    abstract function tablename();
    
    /** Возвращает первичный ключ таблицы
     * @return text
     * @access public
     */
    public function idname()
    {
        return 'id';
    }
    
    /** Возвращает префикс таблицы
     * @return text
     * @access public
     */
    public function prefix()
    {
        global $CFG;
        return $CFG->prefix;
    }
    
    /** Получить массив объектов из данного справочника, по массиву объектов, содержащих ключи по данному справочнику
     * @param array $list1 список объектов
     * @param string $idfield имя поля, содержащего ключ
     * @param bool $byorder сохранить порядок объектов, если соответствие не будет найдено (добавит пустые элементы)?
     * @param bool $combine добавить новые объекты в качестве вложенного поля к старым?
     * @param string $combfield имя поля для вложенного объекта
     * @return object
     * 
     * @throws dml_exception в случае ошибки
     */
    public function get_list_by_list($list1, $idfield, $byorder=false, $combine=false,$combfield='')
    {
        $list2 = array();
        // Требуется создать комбинированный список?
        if ($combine)
        {
            $list2 = fullclone($list1);
        }
        // Комбинируем список
        foreach ($list1 as $key1=>$obj1)
        {
            // Получаем объект
            if (!isset($obj1->$idfield) OR (!$obj2 = $this->get($obj1->$idfield) AND !$byorder))
            {
                // Нет объекта и не требуется вернуть их в том же порядке, что и были
                continue; 
            }
            // Требуется создать комбинированный список?
            if ($combine)
            {
                // Собираем в список клоны изначальных объектов и добавляем поля
                $objc = clone $obj1;
                $objc->$combfield = $obj2;
                $list2[$key1] = $objc;
            }else
            {
                // Просто собираем в список полученные объекты
                $list2[] = $obj2;
            }
        }
        // Возвращаем новый список
        return $list2;
    }
    
    /**
     * @todo это копия метода Moodle, в которую внесены наши правки, позволяющие работать с параметрами
     *       вида ('field' => array('value1', 'value2', 'value3'))
     * 
     * Returns SQL WHERE conditions.
     * @param array conditions - must not contain numeric indexes
     * @return array sql part and params
     */
    protected function where_clause(array $conditions=null)
    {
        global $DB;
        
        $table = $this->tablename();
        // We accept nulls in conditions
        $conditions = is_null($conditions) ? array() : $conditions;
        // Some checks performed under debugging only
        if ( debugging() )
        {
            $columns = $DB->get_columns($table);
            if (empty($columns))
            {
                // no supported columns means most probably table does not exist
                throw new dof_exception_dml('ddltablenotexist', $table);
            }
            foreach ($conditions as $key=>$value)
            {
                if (!isset($columns[$key]))
                {
                    $a = new stdClass();
                    $a->fieldname = $key;
                    $a->tablename = $table;
                    throw new dof_exception_dml('ddlfieldnotexist', $a);
                }
                $column = $columns[$key];
                if ($column->meta_type == 'X')
                {
                    //ok so the column is a text column. sorry no text columns in the where clause conditions
                    throw new dof_exception_dml('textconditionsnotallowed', $conditions);
                }
            }
        }

        $allowed_types = SQL_PARAMS_QM;
        if (empty($conditions))
        {
            return array('', array());
        }
        $where = array();
        $params = array();

        foreach ($conditions as $key=>$value)
        {
            if (is_int($key))
            {
                throw new dof_exception_dml('invalidnumkey');
            }
            if (is_null($value))
            {
                $where[] = "$key IS NULL";
            }else
            {
                if ($allowed_types & SQL_PARAMS_NAMED)
                {
                    // Need to verify key names because they can contain, originally,
                    // spaces and other forbidden chars when using sql_xxx() functions and friends.
                    $normkey = trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $key), '-_');
                    if ($normkey !== $key)
                    {
                        debugging('Invalid key found in the conditions array.');
                    }
                    $where[] = "$key = :$normkey";
                    $params[$normkey] = $value;
                }elseif( is_array($value) )
                {// FIXME Правка FDO - добавляем возможность работать со списком значений для одного поля
                    // оригинальный код Moodle так не умеет
                    // @todo послать патч в Moodle, и после того как они его примут - 
                    // удалить эту функцию отсюда и пользоваться стандартной мудловской
                    list($listwhere, $listparams) = $this->where_clause_list($key, $value);
                    if ( $listwhere )
                    {
                        $where[] = '('.$listwhere.')';
                        $params = array_merge($params, $listparams);
                    }
                }else
                {
                    $where[]  = "$key = ?";
                    $params[] = $value;
                }
            }
        }
        $where = implode(" AND ", $where);
        return array($where, $params);
    }
    
    /** Копия метода Moodle 2.2 для составления запроса по одному полю и нескольким значениям
     * Returns SQL WHERE conditions for the ..._list methods.
     *
     * @param string $field the name of a field.
     * @param array $values the values field might take.
     * @return array sql part and params
     */
    protected function where_clause_list($field, array $values)
    {
        $params = array();
        $select = array();
        $values = (array)$values;
        foreach ($values as $value) {
            if (is_bool($value)) {
                $value = (int)$value;
            }
            if (is_null($value)) {
                $select[] = "$field IS NULL";
            } else {
                $select[] = "$field = ?";
                $params[] = $value;
            }
        }
        $select = implode(" OR ", $select);
        return array($select, $params);
    }
    /** Получить ссылку на совершение действия с объектом этого хранилища
     * В функции также проверяются права. Если прав на совершение действия нет - то
     * отображается только название объекта
     * @todo убрать зависимость от im/obj 
     * @todo непонятно что при проверке прав делать с параметром $departmentid: сейчас его не указываем
     * 
     * @param int|object $id - id объета из хранилища или сам объект
     * @param string $action - действие которое нужно совершить с объектом.
     *                         Название действия должно совпадать с названием права на действие в storage/acl
     * @param array $params  - дополнительные параметры для ссылки на объект
     * @param array $name  - название объекта, или иконка на него. Тот текст, который будет отображаться по ссылке.
     *                       Если ничего не указано - отобразится название объекта
     * @return string - html-ссылка на совершение действия (если есть права)
     *                  или просто название объекта (если прав нет)
     */
    public function get_object_action($id, $action='view', array $params=array(), $name=null)
    {
        $result = '';
        if ( is_object($id) )
        {
            $obj = $id;
            $id  = $obj->id;
        }elseif ( ! $obj = $this->get($id) )
        {
            dof_debugging(get_class($this).'::get_object_action() object not found!', DEBUG_DEVELOPER);
            return '[[object_not_found!]]';
        }
        // Получаем название объекта для составления ссылки
        $name = $this->get_object_name($obj);
        // проверяем право на совершение действия
        if ( $this->is_access($action, $id) )
        {// право есть - покажем ссылку на совершение действия
            if ( ! $urls = $this->dof->im('obj')->get_object_url($this->code(), $id, $action, $params) )
            {// ни один плагин не предоставляет ссылок на этот объект - отобразим хотя бы название
                return $name;
            }
            
            // Предполагаем, что ссылка в ответ может придти не одна - поэтому
            // составим тег для каждой
            foreach ( $urls as $url )
            {
                $result .= '<a href="'.$url.'">'.$name.'</a>';
            }            
        }else
        {// права на совершение действия нет - покажем просто название объекта 
            return $name;
        }
        
        return $result;
    }
    /** Получить название объекта из хранилища для отображения или составления ссылки
     * Этот метод переопределяется для тех хранилищ, объекты в которых не имеют поля name
     * @todo дописать алгоритм работы с дополнительными полями
     * 
     * @param int|object - id объекта или сам объект
     * @param array $fields[optional] - список дополнительных полей, которые будут выведены после названия
     * 
     * @return string название объекта
     */
    public function get_object_name($id, array $fields=array())
    {
        if ( is_object($id) )
        {
            $obj = $id;
        }elseif ( is_int_string($id) )
        {
            if ( ! $obj = $this->get($id) )
            {
                dof_debugging(get_class($this).'::get_object_name() object not found!', DEBUG_DEVELOPER);
                return '[[object_not_found!]]';
            }
        }else
        {
            dof_debugging(get_class($this).'::get_object_name() wrong parameter type!', DEBUG_DEVELOPER);
            return '';
        }
        
        return $obj->name;
    }
    /*****************************************************/
    /** Устаревшие методы. Оставлены для совместимости, **/ 
    /**       не используйте их в новых плагинах        **/
    /*****************************************************/
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE,
     * который определяет параметры выборки  
     * @param string $field - название поля
     * @param mixed $value - null, string или array 
     * @return mixed string - фрагмент sql-запроса
     *          если $value - null, то пустая строка
     *          если $value - строка, то "поле = значение"
     *          если $value - массив, то "поле IN(знач1, знач2, ... значN)" 
     *          если массив пуст или это не массив и не строка и не null,
     *          то вернется bool false 
     * 
     * @deprecated в связи с новым API доступа к базе в Moodle 2.2 запросы теперь составляютя по-другому
     */
    public function query_part_select($field, $value = null)
    {
        //dof_debugging('Using deprecated method query_part_select(). Please use where_clause() instread', DEBUG_DEVELOPER);
        if ( ! is_scalar($field) OR is_bool($field) )
        {//название поля неправильного типа';
            return false;
        }
        if ( is_null($value) OR ! $field )
        {//значение поля не передано';
            return '';
        }
        if ( is_scalar($value) AND ! is_bool($value) )
        {//значение только одно';
            return "{$field} = '{$value}'";
        }
        if ( is_array($value) AND ! empty($value) )
        {//значений несколько';
            $isnull = '';
            foreach ( $value as $k => $v )
            {//разберемся, что передано в массиве, 
                if (is_null($v) )
                {//передан элемент null
                    //сформируем фрагмент запроса IS NULL
                    $isnull = $field.' IS NULL ';
                    //уберем null из массива во избежание ошибок
                    unset ($value[$k]);
                }elseif( is_scalar($v) )
                {//передано что надо - превращаем в строку
                    $value[$k] = '\''.$v.'\'';
                }else
                {//передано то, что не надо было передавать
                    return false;
                }
            }
            if ( empty($value) )
            {//в массиве были только элементы null
                return $isnull;
            }
            //если в массиве еще что-то осталось
            $str = implode(',',$value);
            if ( $isnull )
            {// Нужно сравнивать с null-значением
                return "({$field} IN({$str}) OR {$isnull})";
            }else
            {// не нужно сравнивать с null-значением
                return "({$field} IN({$str}))";
            }
        }else
        {//не массив или пустой массив';
            return false;
        }
        //на всякий случай, если передали нечто неизвестное';
        return false;
    }
    
    /** Подсчитывает количество записей, найденных по критериям
     * @param string select - условие выборки
     * @param string countitem - код счетчика
     * @return mixed
     * @access public
     * 
     * @throws dml_exception в случае ошибки
     * @deprecated 
     */
    public function count_select($select='', $countitem="COUNT('x')")   
    {
        global $DB;
        dof_debugging('Using deprecated method count_select(). Please use count_records_select() instread', DEBUG_DEVELOPER);
        return $DB->count_records_select($this->tablename(),$select, null, $countitem);
    }
    
    /** Получить количество записей по SQL-запросу.
     * 
     * @param string $sql - Полноценный SQL-запрос с использованием COUNT
     * @param int $limitfrom - порядковый номер записи, начиная с которой нужно вернуть массив, по умолчанию $limitfrom=''.
     * @param int $limitnum - количество записей в возвращаемом массиве, по умолчанию $limitnum=''.
     * @return mixed - массив объектов или false, если не было найдено записей или произошла ошибка.
     * 
     * @throws dml_exception в случае ошибки
     * @deprecated
     */
    public function count_list_sql($sql)
    {
        global $DB;
        dof_debugging('Using deprecated method count_list_sql(). Please use count_records_sql() instread', DEBUG_DEVELOPER);
        return $DB->count_records_sql($sql);
    }
    
    /** Возвращает массив объектов, выбранных по sql-запросу
     * @param string select - фрагмент where sql-запроса
     * @param string sort - в каком направлении и по каким полям производится сортировка
     * @param string fields поля, которые надо возвратить
     * @param int limitfrom - id, начиная с которого надо искать
     * @param int limitnum максимальное количество записей, которое надо вернуть
     * @return mixed массив объектов если что-то нашлось или false
     * @access public
     */
    public function get_list_select($select='', $sort='', $fields='*', $limitfrom=0, $limitnum=0)
    {
        global $DB;
        dof_debugging('Using deprecated method get_list_select(). Please use get_records_select() instread', DEBUG_DEVELOPER);
        return $DB->get_records_select($this->tablename(), $select, null, $sort, 
            $fields, $limitfrom, $limitnum);
    }
    
    /** Возвращает объект, которые удовлетворяют заданным критериям
     * @deprecated эта функция оставлена только для совместимости со старой версией
     *             плагинов
     * 
     * @param string field1 - название первого поля поиска 
     * @param mixed value1 - значение, которое ищется в первом поле
     * @param string field2 - название второго поля поиска
     * @param mixed value2 - значение, которое ищется во втором поле
     * @param string field3 - название третьего поля поиска
     * @param mixed value3 - значение, которое ищется в третьем поле 
     * любая переменная $valueX может содержать как одно значение 
     * так и массив значений, которые ищутся в соответствующем  поле.
     * @param string fields - поля, которые должны быть возвращены, разделенные запятыми
     * @return mixed object - объект с указанными полями, если нашлась запись,
     *  удовлетворяюшая всем трем критериям поиска или false 
     * @access public
     */
    public function get_filter($field1 = '', $value1 = '', $field2 = '', $value2 = '', 
                               $field3 = '', $value3 = '', $fields = '*')
    {
        dof_debugging('Using deprecated method get_filter(). Please use get_records() instread', DEBUG_DEVELOPER);
        global $DB;
        
        $conditions = array();
        if ( ! empty($field1) )
        {
            $conditions[$field1] = $value1;
        }
        if ( ! empty($field2) )
        {
            $conditions[$field2] = $value2;
        }
        if ( ! empty($field3) )
        {
            $conditions[$field3] = $value3;
        }
        return $this->get_record($conditions,$fields);
    }
    /** Возвращает массив объектов, удовлетворяющих нескольким значениям одного поля
     * @deprecated это устаревшая функция, не используйте ее в новых плагинах
     * 
     * @param string field - название поля для поиска
     * @param mixed value - может содержать как одно значение, 
     * так и массив значений, которые ищутся в указанном поле
     * @param string sort - в каком направлении и по каким полям производится сортировка
     * @param string fields поля, которые надо возвратить
     * @param int limitfrom - id, начиная с которого надо искать
     * @param int limitnum максимальное количество записей, которое надо вернуть
     * @return mixed массив объектов если что-то нашлось или false
     * @access public
     */
    public function get_list($field='', $value='', $sort='', 
                             $fields='*', $limitfrom=0, $limitnum=0)
    {
        dof_debugging('Using deprecated method get_list(). Use get_records() instread', DEBUG_DEVELOPER);
        global $DB;
        $select = $this->query_part_select($field, $value);
        return $DB->get_records_select($this->tablename(), $select,null, $sort, $fields, $limitfrom, $limitnum);
        
    }
    /** Проверяет наличие в таблице записи, которая ищется по критериям
     * @deprecated эта функция устарела, не используйте ее в новых плагинах
     * 
     * @param string field1 - название первого поля поиска 
     * @param mixed value1 - значение, которое ищется в первом поле
     * @param string field2 - название второго поля поиска
     * @param mixed value2 - значение, которое ищется во втором поле
     * @param string field3 - название третьего поля поиска
     * @param mixed value3 - значение, которое ищется в третьем поле
     * любая переменная $valueX может содержать как одно значение 
     * так и массив значений, которые ищутся в соответствующем  поле.
     * @return boolean true - запись найдена, false - запись не найдена
     * @access public
     */
    public function is_exists_filter($field1 = '', $value1 = '', $field2 = '', 
                                     $value2 = '', $field3 = '', $value3 = '')
    {
        dof_debugging('Using deprecated method is_exists_filter(). Use is_exists() instread', DEBUG_DEVELOPER);
        global $DB;
        $conditions = array();
        if ( ! empty($field1) )
        {
            $conditions[$field1] = $value1;
        }
        if ( ! empty($field2) )
        {
            $conditions[$field2] = $value2;
        }
        if ( ! empty($field3) )
        {
            $conditions[$field3] = $value3;
        }
        return $this->is_exists($conditions);
    }
    /** Подсчитывает количество записей, найденных по критериям
     * @deprecated эта функция устарела, не используйте ее в новых плагинах
     * 
     * @param string field1 - название первого поля поиска 
     * @param mixed value1 - значение, которое ищется в первом поле
     * @param string field2 - название второго поля поиска
     * @param mixed value2 - значение, которое ищется во втором поле
     * @param string field3 - название третьего поля поиска
     * @param mixed value3 - значение, которое ищется в третьем поле
     * любая переменная $valueX может содержать как одно значение 
     * так и массив значений, которые ищутся в соответствующем  поле.
     * @return mixed
     * @access public
     */
    public function count($field1 = '', $value1 = '', $field2 = '', 
                          $value2 = '', $field3 = '', $value3 = '')   
    {
        dof_debugging('Using deprecated method count(). Use count_list() instread', DEBUG_DEVELOPER);
        global $DB;
        
        $conditions = array();
        if ( $field1 )
        {// для каждого указанного поля составляем условие
            $conditions[$field1] = $value1;
        }
        if ( $field2 )
        {// для каждого указанного поля составляем условие
            $conditions[$field2] = $value2;
        }
        if ( $field3 )
        {// для каждого указанного поля составляем условие
            $conditions[$field3] = $value3;
        }        
        
        return $this->count_list($conditions); 
        
    }
    
    /**
     * Возвращает детальную информацию о полях в таблице. Информация кешируется
     * 
     * @param bool $usecache - флаг для использования внутреннего кеширования. По-умолчанию включено
     * @return array объекты database_column_info проиндексированные по названию полей
     */
    public function get_columns($usecache = true)
    {
        global $DB;
        return $DB->get_columns($this->tablename(), $usecache);
    }
    
    /**
     * Исполняет SQL-код. Использовать только в случае, если не найдено другого подходящего
     * метода.
     * Не использовать метод для манипуляции со структурой бд!
     * 
     * @param string $sql
     * @param array $params
     * @return bool
     * @throws dof_exception
     */
    public function execute($sql, array $params = null)
    {
        global $DB;
        try
        {
            return $DB->execute($sql, $params);
        } catch ( dof_exception_dml $e )
        {
            throw new dof_exception('errorsqlexecute', null, $e->getMessage());
        }
    }
    
    /**
     * Сформировать sql, содержащий LIKE-часть кода
     * 
     * @param string $fieldname - Имя поля
     * @param string $param - Плейсхолдер или значение(?, :named)
     * @param bool $casesensitive - Регистрозависимый поиск
     * @param bool $accentsensitive - Диакритический поиск
     * @param bool $notlike - Инверсия поиска
     * @param string $escapechar - Экранирующий символ
     * 
     * @return string - Фрагмент SQL кода
     */
    public function get_sql_like($fieldname, $param, $casesensitive = false, $accentsensitive = false, $notlike = false, $escapechar = '\\')
    {
        global $DB;
        
        return $DB->sql_like($fieldname, $param, $casesensitive, $accentsensitive, $notlike, $escapechar);
    }
}
