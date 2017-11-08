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

/** Класс хранилища, реализующего свясь должностей
 * с преподаваемыми дисциплинами
 */
class dof_storage_teachers extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control - объект с методами ядра деканата
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    public function install()
    {
        if ( ! parent::install() )
        {
            return false;
        }
        // после установки плагина устанавливаем права
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }  
    
    /** 
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        //подключаем конфиг мудла
        global $CFG;
        //методы для установки таблиц из xml
        require_once($CFG->libdir.'/ddllib.php');
        $result = true;
        
        return $result && $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2012042500;
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
        return 'paradusefish';
    }
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'storage';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'teachers';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array('storage'=>array('appointments'  => 2010040200,
                                      'departments'   => 2010022700,
                                      'programmitems' => 2010012100,
                                      'config'        => 2011080900)
                                       );
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
        return array('storage'=>array('acl'=>2011040504,
                                      'config'=> 2011080900));
    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
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
        if ( ! $this->is_access($do, $objid, $userid, $depid) )
        {
            $notice = "{$this->code()}/{$do} (block/dof/{$this->type()}/{$this->code()}: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
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
    public function cron($loan,$messages)
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
    public function todo($code,$intvar,$mixedvar)
    {
        return true;
    }
    /** 
     * Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
    }

    /** 
     * Возвращает название таблицы без префикса (mdl_)
     * с которой работает examplest
     * @return text
     * @access public
     */
    public function tablename()
    {
        // Имя таблицы, с которой работаем
        return 'block_dof_s_teachers';
    }

    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************    
    
    /** Получить список параметров для фунции has_hight()
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
        if ( is_null($depid) )
        {// подразделение не задано - берем текущее
            $result->departmentid = optional_param('departmentid', 0, PARAM_INT);
        }
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
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
     * 				 'roles' => array('student' ,'...');
     */
    public function acldefault()
    {
        $a = array();
        
        $a['view']     = array('roles'=>array('manager','methodist'));
        $a['edit']     = array('roles'=>array('manager','methodist'));
        $a['create']   = array('roles'=>array('manager','methodist'));
        $a['delete']   = array('roles'=>array());
        $a['use']      = array('roles'=>array('manager','methodist'));
        
        return $a;
    }
    
    /** Функция получения настроек для плагина
     *  
     */
    public function config_default($code=null)
    {
        // плагин включен и используется
        $config = array();
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Максимально разрешенное количество объектов этого типа в базе
        // (указывается индивидуально для каждого подразделения)
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'objectlimit';
        $obj->value = '-1';
        $config[$obj->code] = $obj;        
        return $config;
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Освободить учителя от преподавания упредмета в указанной должности
     * 
     * @param int $appointmentid - id назначения на должность в таблице appointments
     * @param int $programmitemid - id предмета в таблице programmitems
     * @param int $departmentid [optional] - id подразделения в таблице departments
     * @return bool
     * @todo предусмотреть вариант, когда в базе может оказаться 2 активные зпписи о преподавании должности
     */
    public function remove_programmitem_from_appointment($appointmentid, $programmitemid, $departmentid=null)
	{
		if ( ! is_numeric($appointmentid) OR ! is_numeric($programmitemid) 
				OR ! $appointmentid OR ! $programmitemid )
		{// переданы неверные данные
			return false;
		}
		if ( ! is_numeric($departmentid) OR ! $departmentid )
		{
			$departmentid = null;
		}else
		{// приведем к нормальному виду id подразделения если оно есть
			$departmentid = intval($departmentid);
		}
		
		// получаем запись для обновления
		if ( $departmentid )
		{// если указано подразделение - то ищем еще и по нему
			$select = ' departmentid = '.$departmentid.' AND programmitemid = '.
			$programmitemid.' AND appointmentid = '.$appointmentid;
		}else
		{// подразделение не указано - будем искать без него
			$select = 'programmitemid = '.
			$programmitemid.' AND appointmentid = '.$appointmentid;
		}
		$select .= ' AND (status = "active" OR status = "plan")';
		//получаем список учителей
		$teachers = $this->get_records_select($select);
		if ( is_array($teachers) )
		{//все есть кого отчислить
		    $teacherdata = current($teachers);
            return $this->remove_teacher($teacherdata->id);
		}
		//отчислять некого
		return true;
	}
	
	/** Освободить учителя от преподаваемого предмета
	 * 
	 * @param int $id - id записи о преподаваемом предмете в этой таблице
	 * @return 
	 */
	public function remove_teacher($id)
	{
		// устанавливаем статус записи через workflow
		$this->dof->workflow($this->code())->change($id, 'canceled');
	}
	
	/** Добавить новую запись о преподавании учителем курса, сразу же переводя ее в статус "активна"
	 * 
	 * @param object $teacher - объект для вставки в таблицу $teachers
	 * @return bool
	 */
	public function add_teacher($teacher)
	{
		if ( $id = $this->safe_insert($teacher) )
		{// если вставка в таблицу удалась - то сразу же переведем ее в статус "активна"
			return $id;
		}
	}
	
	/** Безопасная вставка в таблицу. Использование этой функции вместо традиционного insert
	 * гарантирует, что все ваши данные будут проверены, идентификаторы на момент вставки будут
	 * точно указывать на существующие записи, а также что логика работы системы не будет нарушена
	 * 
	 * @param object $dataobject - объект для вставки в таблицу
	 * @return int|bool 
	 * 				- id новой записи если операция вставки прошла успешно
	 * 				- false усли вставка в таблицу не удалась
     * @param object $quiet[optional] - не генерировать событий
	 * 
	 * @todo сделать вывод ошибок через исключения, когда появится механизм работы с ними
	 */
	public function safe_insert($dataobject, $quiet=false)
	{
		if ( $this->insert_possible($dataobject) )
		{// данные проверены, их можно записывать
			return $this->insert($dataobject, $quiet);
		}
		// вставка данных не удалась
		return false;
	}
    
    /** Безопасное обновление записи в таблице. Использование этой функции вместо традиционного update
	 * гарантирует, что все ваши данные будут проверены, идентификаторы на момент вставки будут
	 * точно указывать на существующие записи, а также что логика работы системы не будет нарушена
     * 
     * @return 
     * @param object $dataobject - объект для обновления таблицы
     * @param int $id - id обновляемой записи
     * @param object $quiet[optional] - не генерировать событий
     * 
     * @todo сделать вывод ошибок через исключения, когда появится механизм работы с ними
     */
    public function safe_update($dataobject, $id, $quiet=false)
    {
        if ( $this->update_possible($dataobject) )
        {// проверки перед обновлением прошли успешно
            return $this->update($dataobject, $id, $quiet);
        }
        // проверки не прошли
        return false;
    }
	
	/** Определяет, возможно ли вставить запись в таблицу
	 * 
	 * @param object $dataobject - объект для вставки в таблицу
	 * @return bool
	 * 		true - если вставка возможна
	 * 		false - если вставка невозможна
	 */
	public function insert_possible($dataobject)
	{
		if ( ! $this->data_valid($dataobject, 'insert') )
		{// данные не прошли проверку на корректность данных
			return false;
		}
        if ( ! $this->extra_validation($dataobject, 'insert') )
        {// данные не прошли дополнительные проверки
            return false;
        }
        // проверка всех данных прошла успешно
		return true;
	}
	
	/** Определяет, возможно ли обновить запись в таблице
	 * 
	 * @param object $dataobject - объект для обновления таблицы
	 * @param int $id - id обновляемой записи
	 * @return bool
	 * 		true - если вставка возможна
	 * 		false - если вставка невозможна
	 */
	public function update_possible($dataobject, $id=null)
	{
		if ( ! $this->data_valid($dataobject, 'update') )
		{// данные не прошли проверку на корректность данных
			return false;
		}
        if ( ! $this->extra_validation($dataobject, 'update') )
        {// данные не прошли дополнительные проверки
            return false;
        }
        
		return true;
	}
	
	/** Выполняет проверки данных, необходимые как при вставке новой записи в таблицу, 
	 * так и при обновлении старой
	 * 
	 * @param object $dataobject - объект для добавления в базу, либо для обновления существующей записи
	 * @param string $checktype - тип совершаемого действия: добавление, обновление, или удаление
	 * @return bool
	 */
	private function data_valid($dataobject, $checktype)
	{
            if ( ! is_object($dataobject) )
            {// неверных формат данных
                    return false;
            }
		
            switch ( $checktype )
            {// вызываем нужные функции проверки в зависимости от типа
                case 'insert': return $this->insert_data_validation($dataobject); break;
                case 'update': return $this->update_data_validation($dataobject); break; 
            }
            // если дошли до сюда - значит получили неизвестный тип совершаемого действия
            // @todo записать эту ошибку в лог, когда будет возможность
            return false;
	}
	
    /** Проверка данных перед вставкой их в базу
     * 
     * @return bool
     * @param object $dataobject - объект для добавления в базу, либо для обновления существующей записи
     */
	private function insert_data_validation($dataobject)
    {
        // получим список обязательных полей
		$requiredfields = $this->get_required_fields();
		
        if ( ! empty($requiredfields) )
        {// если есть обязательные поля
            foreach ($requiredfields as $requiredfield)
    		{// проверим в цикле наличие всех обязательных полей
    			if ( ! isset($dataobject->$requiredfield) OR ! $dataobject->$requiredfield  )
    			{// обязательное поле, или значение в нем - отсутствует, это ошибка
    				return false;
    			}
    		}
        }
		
		// получим список полей с идентефикаторами
		$idfields = array_keys($this->get_dependencies());
        if ( ! empty($idfields) )
        {// если есть поля, содержащие id из других таблиц
            foreach ( $idfields as $idfield )
    		{// перебираем все поля объекта и применяем к каждому необходимые проверки
    			if ( ! $this->idfield_valid($dataobject, $idfield) )
    			{// в поле отсутствует необходимый id, либо оно ссылается на несуществующую
    				// запись в базе
    				return false;
    			}
    		}
        }
        // все проверки перед вставкой прошли успешно
        return true;
    }
    
    /** Проверка данных перед обновлением базы
     * 
     * @return bool
     * @param object $dataobject - объект для обновления существующей записи
     * 
     * @todo дописать проверки для обновления
     */
    private function update_data_validation($dataobject)
    {
        return true;
    }
	
	/** Проверяет, содержит ли указанное поле допустимое для этой таблицы значение
	 * 
	 * @param object $dataobject - объект с данными для проверки
	 * @param string $field - поле, которое проверяется
	 * @return bool
	 * 
	 * @todo более подробно описать проверку на нулевое значение в поле
	 */
	private function idfield_valid($dataobject, $field)
	{
		if ( ! isset($dataobject->$field) OR ! $dataobject->$field  )
		{// проверяем, есть ли значение в указанном поле
			if ( $this->required_field($field) )
			{// если не указано обязательное поле - это ошибка
				return false;
			}else
			{// если не указано необязательноеполе - то ничего страшного, идем дальше
				return true;
			}
		}
		
		// получаем список полей, которые содержат id других таблиц
		$dependencies = $this->get_dependencies();
		if ( isset($dependencies[$field]) )
		{// если переданное поле составляет содержит id из другой таблицы - то проверим
			// указывает ли этот id на реально существующую запись
			if ( ! $this->dof->storage($dependencies[$field])->is_exists($dataobject->$field) )
			{// поле объекта содержит id которого не существует - это ошибка
				return false;
			}
		}
		
		// все проверки пройдены успешно
		return true;
	}
	
	/** Возвращает массив, определяющий, какие поля этого справочника связывают его с другими справочниками
	 * 
	 * @return array массив связей в формате ('название_поля_в_БД' => 'Название_плаина_storage')
	 */
	private function get_dependencies()
	{
		return array('departmentid'   => 'departments',
					 'appointmentid'  => 'appointments',
				     'programmitemid' => 'programmitems'
					 );
	}
	
	/** Проверяет, является ли указанное поле объекта обязательным
	 * 
	 * @param string $name - название поля в таблице
	 * @return bool
	 * 			- true если поле обязательное
	 * 			- false если поле необязательное
	 */
	private function required_field($name)
	{
		if ( in_array($name, $this->get_required_fields()) )
		{// поле обязательное 
			return true;
		}
		return false;
	}
	
	/** Определяет, какие поля явдяются обязательными для этого справочника
	 * (без этих незаполненных полей нельзя будет вставить объект в таблицу)
	 * @return array массив обязетельных для этого справочника полей
	 */
	private function get_required_fields()
	{
		// @todo сделать worktime обязательным, когда разберемся, откуда ебрать при массовом создании вакансий
		return array('appointmentid', 'programmitemid', 'departmentid'/*, 'worktime'*/);
	}
    
    /** Получить список возможных предметов для преподавания для переданного назначения на должность
     * 
     * @return array|bool - массив записей из таблице programmitems, исключая те предметы, которые 
     *                      уже были привязаны к этой должности в таблице teachers
     * @param int $appointmentid - id назначения на должность в таблице в табице appointments
     * @todo исключать из выборки только те курсы, которые пользователь ведет в 
     * рамках указанного назначения на должность, или вообще все, которые он
     * ведет и в рамках других должностей?
     */
    public function get_available_pitems_for_appointment($appointmentid)
    {
        if ( ! $appointment = $this->dof->storage('appointments')->get($appointmentid) )
        {// не удалось получить назначение на должность -  не можем продолжать работу
            return false;
        }
        
        // получим все программы подразделения
        $programms = $this->dof->storage('programms')->get_records(array('status'=> 
            array('available', 'draft', 'notavailable')), ' name ASC ');
        
        if ( ! $programms )
        {// указанное подразделение не реализует ни одной учебной программы
            // значит и предметов нет
            return false;
        }
        
        // получим те курсы, которые преподаватель уже ведет в этой должности
        $availablepitems = $this->get_appointment_pitems($appointmentid);
        
        if ( is_array($availablepitems) )
        {// курсы в программе есть
            $availablepitems = array_keys($availablepitems);
        }else
        {// курсов в программе нет
            $availablepitems = array();
        }
        
        $pitems = array();
        // @todo оптимизировать алгоритм выборки
        foreach ( $programms as $progid=>$programm )
        {// для каждой учебной программы получаем список ее предметов
            // перед началом каждой серии списка предметов обязательно вставляем название программы
            $progpitems = $this->dof->storage('programmitems')->get_records(array('programmid'=>$progid,
                    'status'=>array('plan', 'active')), 'name ASC');
            // оставим в списке только те объекты, на использование которых есть право
            $permissions  = array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'code'=>'use'));
            $progpitems = $this->dof->storage('acl')->get_acl_filtered_list($progpitems, $permissions);
            if ( is_array($progpitems) )
            {// если в программе есть предметы
                foreach ( $progpitems as $progpitem )
                {// перебираем все предметы программы
                    if ( ! in_array($progpitem->id, $availablepitems) )
                    {// если предмет не в списке уже преподаваемых - добавим его в список доступных
                        // для преподавания
                        $pitems[$progid][$progpitem->id] = $progpitem;
                    }
                }
            }
        }
        
        // возвращаем итоговый массив
        return $pitems;
    }
    
    /** Получить список курсов, которые учитель уже может вести в рамках указанного назначения на должность
     * 
     * @return array|bool - массив курсов из таблицы pitems или false если учителю не назначено пока ни
     *                      одного курса
     * @param int $appointmentid - id назначения на должность в таблице в табице appointments
     * @todo делать ли проверку на уникальность? (сейчас пишутся только уникальные записи)
     */
    public function get_appointment_pitems($appointmentid)
    {
        // из таблицы teachers получим те предметы, которые учитель на 
        // указанной должности уже преподает
        if ( ! $teachingitems = $this->get_records(array('appointmentid'=>$appointmentid,
                    'status'=>array('active', 'plan'))) )
        {// учитель пока не преподает ни одного предмета
            return false;
        }
        
        $pitems = array();
        foreach ( $teachingitems as $id=>$item )
        {// перебираем все записи о курсах, и оставляем только уникальные
            $pitem = current($this->dof->storage('programmitems')->
                    get_records(array('id'=>$item->programmitemid,
                    'status'=>array('plan', 'active', 'suspend'))));
            // добавляем к объекту предмета статус назначения учителя, который его ведет
            $pitem->teacherstatus = $item->status;
            // записываем комбинацию в итоговый массив
            $pitems[$item->programmitemid] = $pitem;
        }
        // возвращаем итоговый результат
        return $pitems;
    }

	
    /** Получить всех учителей, которые ведут указанный предмет
     * 
     * @return array|bool массив записей из таблицы persons или false в случае ошибки
     * @param int $pitemid - id предмета в таблице programmitems
     * @param bool $count[optional] - получить только количество учителей
     * 
     * @todo добавить обработку поиска по маске
     */
    public function get_pitem_teachers($pitemid, $count=false, $mask='')
    {
        //получаем список преподавателей курса
        $teachers = $this->get_records(array('programmitemid'=>$pitemid));
        if ( empty($teachers) )
        {
            return false;
        }
        //получаем список назначений этих учителей на вакансии
        $apps = $this->dof->storage('appointments')->
            get_list_by_list($teachers,'appointmentid');
        if ( empty($apps) )
        {
            return false;
        }
        //получаем список договоров с этими учителями
        $eagree = $this->dof->storage('eagreements')->
            get_list_by_list($apps, 'eagreementid');
        if ( empty($eagree) )
        {
            return false;
        }
        //получаем персональные данные этих учителей
        $persons = $this->dof->storage('persons')->
            get_list_by_list($eagree,'personid');
        if ( empty($persons) )    
        {
            return false;
        }
        if ( $count )
        {// нужно только подсчитать количество записей
            return count($persons);
        }
        return $persons;
    }
    
    /** Получить всех учителей, которые не ведут указанный предмет
     * 
     * @return array|int|bool - список учителей, количество учителей, или false если ничего не найдено
     * @param int $pitemid - id предмета в таблице programmitems
     * @param bool $count[optional] - получить только количество учителей
     * @param string $mask[optional] - поиск по маске (если он производится)
     * 
     * @todo добавить обработку поиска
     * @todo протестировать эту функцию
     */
    public function get_pitem_no_teachers($pitemid, $count=false, $mask='')
    {
        if ( ! $pitem = $this->dof->storage('programmitems')->get($pitemid) )
        {// указанного предмета нет в базе
            return false;
        }
        
        $teachers = $this->get_records(array('programmitemid'=>$pitem->id, 'status'=>array('plan', 'active')));
        if ( ! $teachers OR empty($teachers) )
        {// не нейдено ни одного преподавателя, ведущего этот предмет
            if ( $count )
            {// Если нужно было подсчитать количество - то выведем 0
                return 0;
            }
            return false;
        }
        // соберем пользователей
        $persons = $this->get_persons($teachers);
        
        // вычтем из всех учителей тех, которые не ведут указанный предмет
        $pitemteachers = $this->get_pitem_teachers($pitemid, $count, $mask);
        
        if ( $count )
        {// возвращаем количество записей
            return count($persons) - count($pitemteachers);
        }
        if ( $pitemteachers AND ! empty($pitemteachers) )
        {// если есть учителя уже записанные в курс - исключим их из списка возможных
            $personids = array_keys($pitemteachers);
            foreach ( $personids as $personid )
            {// в цикле удаляем существующих учителей
                unset($persons[$personid]);
            }
        }
        // возвращаем записи
        return $persons;
    }
    
    /** Получить полный список учителей, которые ведут какие-либо курсы, пользуясь таблицей teachers
     * 
     * @return array
     * @param bool $count[optional]
     * @param string $mask[optional]
     * 
     * @deprecated эта функция используется только для совместимости с FDO v1. В новых версиях 
     * используйте функции которые получают учителей для конкретных курсов
     */
    public function get_full_teacherlist($count=false, $mask='')
    {
        //получаем список преподавателей курса
        $teachers = $this->get_records(array('status'=>array('plan', 'active')));
        if ( empty($teachers) )
        {// нет ни одного учителя
            return false;
        }
        // соберем пользователей
        $persons = $this->get_persons($teachers);
        
        if ( $count )
        {// возвращаем количество записей
            return count($persons);
        }
        // возвращаем записи
        return $persons;
    }
    
    /** Функция для дополнительных проверок вводимых данных перед вставкой в базу
     * 
     * @return bool 
     *             - true проверки пройдены
     *             - false проверки не пройдены
     * @param object $data - данные для вставки в таблицу
     * @param string $type - тип совершаемого действия: добавление, обновление, или удаление
     */
    private function extra_validation($data, $type)
    {
        return true;
    }
    
    /** Получить список записей из таблицы persons, по списку из таблицы teachers
     * 
     * @return array|bool - массив записей из таблицы persons или false если ничего не нашлось
     * @param array $teachers - массив записей из таблицы teachers
     * 
     * @todo добавить возможность возвращать неуникальные записи
     */
    public function get_persons($teachers, $enum = false)
    {
        if ( ! is_array($teachers) OR empty($teachers) )
        {// неправильный формат данных
            return false;
        }
        $persons = array();
        foreach ( $teachers as $teacher )
        {// перебираем все записи и ищем назначения на должность
            if ( $person = $this->get_person_by_teacher($teacher->id, $enum) )
            {// если пользователь найден - добавим его в список
                $persons[$person->id] = $person;
            }
        }
        // возвращаем все найденные записи из таблицы persons
        return $persons;
    }
    
    /** Получить список записей из таблицы persons, 
     * по списку записей из таблицы teachers
     * @return array|bool - массив записей из таблицы persons 
     * или false если ничего не нашлось
     * в качестве ключей массива используются 
     * id записей из таблицы appointments
     * к каждой записи добавляется поле appointmentid и, 
     * если указать, enumber - табельный номер
     * @param array $teachers - массив записей из таблицы teachers
     * @param bool $enum - добавлять табельный номер в результат или нет
     */
    public function get_persons_with_appid($teachers, $enum = false)
    {
        if ( ! is_array($teachers) OR empty($teachers) )
        {// неправильный формат данных
            return false;
        }
        $persons = array();
        foreach ( $teachers as $teacher )
        {// перебираем все записи и ищем назначения на должность
            if ( $person = $this->get_person_by_teacher($teacher->id, $enum) )
            {// если пользователь найден - добавим его в список
                $person->appointmentid = $teacher->appointmentid;
                $person->worktime = $teacher->worktime;
                $persons[$teacher->appointmentid] = $person;
            }
        }
        //print_object($persons);
        //отсортировали по ФИО
        uasort($persons,'sort_by_sortname');
        //print_object($persons);
        // возвращаем все найденные записи из таблицы persons
        return $persons;
    }
    
    /** Получить объект из таблицы persons по id в таблице teachers
     * 
     * @return object|bool - объект из таблицы persons или false если ничего не нашлось
     * @param object|int $teacher - объект из таблицы teachers или id объекта из таблицы teachers 
     */
    public function get_person_by_teacher($teacher, $enum = false)
    {
        if ( ! is_object($teacher) )
        {// если переменная - не объект, значит нам передали id
            if ( ! $teacher = $this->get($teacher) )
            {// неправильный формат данных или такой записи не существуэ
                return false;
            }
        }
        if ( ! $appointment = $this->dof->storage('appointments')->get($teacher->appointmentid) )
        {// не найдено назначение на должность
            // @todo это означает ошибку целостности базы данных - в будущем надо будет записать в лог  
            return false;
        }
        if ( ! $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
        {// договор не найден
            // @todo это означает ошибку целостности базы данных - в будущем надо будет записать в лог
            return false;
        }
        if ( ! $person = $this->dof->storage('persons')->get($eagreement->personid) )
        {// пользователь с таким id не найден
            // @todo это означает ошибку целостности базы данных - в будущем надо будет записать в лог
            return false;
        }
        $person->teacherstatus = $teacher->status;
        if ( $enum )
        {// сказано, что нужно вернуть и табельный номер
            $person->enumber = $appointment->enumber;
        }
        // возвращаем найденную запись
        return $person;
    }
    
    /** Получить список учителей, преподающих указанный предмет
     * 
     * @return array|bool массив записей из таблицы teachers или false если ничего не нашлось
     * @param int $pitemid - id предмета из таблицы programmitems
     */
    public function get_teachers_for_pitem($pitemid)
    {
        if ( ! $pitemid OR ! is_numeric($pitemid) )
        {// неверный формат данных
            return false;
        }
        // получаем список учителей
        return $this->get_records(array('programmitemid'=>$pitemid, 'status'=>array('plan', 'active')));
    }
    
    /** Получить список должностных назначений, преподающих указанный предмет и 
     * имеющих активный табельный номер
     * 
     * @return array|bool массив записей из таблицы teachers, appointments, persons
     *  или false если ничего не нашлось
     * @param int $pitemid - id предмета из таблицы programmitems
     */
    public function get_appointments_active_for_pitem($pitemid)
    {
        if ( ! $pitemid OR ! is_numeric($pitemid) )
        {// неверный формат данных
            return false;
        }
        // получаем список учителей
        $teachers = $this->get_records(array('programmitemid'=>$pitemid, 'status'=>array('plan', 'active')));
        if ( empty($teachers) )
        {
            return array();
        }
        $teachers = $this->get_persons_with_appid($teachers, true);
        // Проверим статусы
        $active = $this->dof->workflow('appointments')->get_meta_list('active');
        foreach ( $teachers as $appid => $teacher )
        {
            if ( ! array_key_exists($this->dof->storage('appointments')
                    ->get_field($appid, 'status'), $active) )
            { // Табельный номер не активен
                unset($teachers[$appid]);
            }
        }
        return $teachers;
    }
    
    /** Получить список учителей, могущих преподавать указанный предмет
     * 
     * @return array|bool массив записей из таблицы appointments или false если ничего не нашлось
     * @param int $pitemid - id предмета из таблицы programmitems
     * @param bool $withworktime - вернуть всех кто не преподает этот предмет (false) или
     * исключить преподавателей, у которых нет часов для этого (true)
     */
    public function get_teachers_no_pitem($pitemid, $withworktime = true)
    {
        //получим табельные номера
        $appointments = $this->dof->storage('appointments')->
             get_records(array('status'=>array('plan', 'active')));
        if ( ! $appointments )
        {// сотрудников нет - вернем пустой массив
            return array();
        }
        //сотрудники есть - проверим кто из них может преподавать
        foreach ( $appointments as $appointment )
        {// найдем тичеров по данному табельному номеру
            if ( ! $appteachers = $this->dof->storage('teachers')->get_records(array('appointmentid'=>$appointment->id,
                                               'status'=>array('plan', 'active'))) )
            {// таких нет, переходим к следующему
                continue;
            }
            //тичеры есть - проверим есть ли у них свободные часы
            if ( $withworktime )
            {
                $worktime = 0;
                foreach ( $appteachers as $teacher )
                {// узнаем сколько он уже преподает
                    $worktime += $teacher->worktime;
                }
                if ( $appointment->worktime <= $worktime)
                {// если у него свободного времени нет, удалим его из списка
                    unset($appointments[$appointment->id]);
                }
            }
        }
        if ( $oldteachers = $this->dof->storage('teachers')->get_teachers_for_pitem($pitemid) )
        {// удалим из списка уже преподающих персон - если они есть
            foreach ( $oldteachers as $oldteacher)
            {
                unset($appointments[$oldteacher->appointmentid]);
            }
        }
        return $appointments;
    }
    
    /** Получить список записей из таблицы, отсортированных по 
     * @param array $conditions - список условий, по которым извлекаются записи в формате 
     *                            'название поля' => 'значение'
     * @param string $order - порядок сортировки
     * @param int $limitfrom
     * @param int $limitnum
     */
    public function get_objects_sorted_by_pitem($conditions, $order='ASC', $limitfrom='', $limitnum='')
    {
        // получаем полные имена таблиц по которым будем делать запрос
        $pitemsfillname   = $this->dof->storage('programmitems')->prefix().
                            $this->dof->storage('programmitems')->tablename();
        $teachersfullname = $this->prefix().$this->tablename();
        
        $wherestring = '';
        if ( ! empty($conditions) )
        {// составляем sql-запрос с условием where
            $queries = array();
            foreach ($conditions as $field=>$value)
            {
                $field = $teachersfullname.'.'.$field;
                $queries[] = $this->query_part_select($field, $value);
            }
            $wherestring = ' WHERE '.implode(' AND ', $queries);
        }
        
        // При помощи LEFT OUTER JOIN извлекаем все записи, в том числе и те, 
        // у которых не указан programmitemid
        $sql = 'SELECT * FROM  '.$teachersfullname.' LEFT OUTER JOIN '.$pitemsfillname.
               ' ON ('.$teachersfullname.'.programmitemid = '.$pitemsfillname.'.id) '.$wherestring.
               ' ORDER BY name '.$order;
        
        return $this->get_records_sql($sql, null, $limitfrom, $limitnum);
    }
}

/**
 * Функция сравнения двух объектов 
 * из таблицы persons по полю sortname
 * @param object $person1 - запись из таблицы persons
 * @param object $person2 - другая запись из таблицы persons
 * @return -1, 0, 1 в зависимости от результата сравнения
 * используется в методе get_persons_with_appid
 * для сортировки по алфавиту
 */
function sort_by_sortname($person1,$person2)
{
    return strnatcmp($person1->sortname, $person2->sortname);
}


?>