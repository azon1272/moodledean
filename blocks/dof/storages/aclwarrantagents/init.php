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
// подключение интерфейса настроек
require_once($DOF->plugin_path('storage','config','/config_default.php'));

/** Выданные доверенности
 * (здесь хранится то какие доверенности кому выданы)
 */
class dof_storage_aclwarrantagents extends dof_storage implements dof_storage_config_interface
{
    /**
     * @var dof_control
     */
    protected $dof;

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
        global $CFG, $DB;
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2012030600) 
        {//удалим enum поля
            // для поля noextend
            if ( $this->dof->moodle_version() <= 2011120511 )
            {
                $field = new xmldb_field('noextend', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'status');
                $dbman->drop_enum_from_field($table, $field);
            }
        }
        if ( $oldversion < 2012112000 )
        {
            // после удаления enum поля слетели настройки - исправим их
            $num = 0;
            while ( $warrantagents = $this->get_records(array('noextend'=>1),'','*',$num,100) )
            {// если такая найдена
                $num += 100;
                foreach ( $warrantagents as $warrantagent )
                {// для каждой стандартной роли
                    // меняем наследование
                    $warrantagent->noextend = 0;
                    $this->update($warrantagent);
                }
            }
            //меняем имя поля
            $index = new xmldb_index('inoextend', XMLDB_INDEX_NOTUNIQUE, array('noextend'));
            if ($dbman->index_exists($table, $index)) 
            {// дропаем сначала индекс
                $dbman->drop_index($table, $index);
            }
            $field = new xmldb_field('noextend', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null, 'parenttype');
            $dbman->rename_field($table, $field, 'isdelegatable');
            $index = new xmldb_index('iisdelegatable', XMLDB_INDEX_NOTUNIQUE, array('isdelegatable'));
            if ( !$dbman->index_exists($table, $index) ) 
            {// добавляем новый индекс
                $dbman->add_index($table, $index);
            }
        }
        return true;// уже установлена самая свежая версия
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2013021400;
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
        return 'aclwarrantagents';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('aclwarrants'=>2011040500));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array();
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        // Ничего не делаем, но отчитаемся об "успехе"
        return true;
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
        return true;
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
        return 'block_dof_s_aclwarrantagents';
    }
    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************  
    
    
    /** Функция получения настроек для плагина
     *
     */
    public function config_default($code=null)
    {
        // плагин включен и используется
        $config = array();
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'duration';
        // в качестве значения - продолжительность года в секундах
        $obj->value = 3600 * 24 * 365;
        $config[$obj->code] = $obj;
  
        return $config;
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    
    /** Возвращает список мандат по заданным критериям 
     * 
     * @return array массив записей из базы, или false в случае ошибки
     * @param int $limitfrom - начиная с какой записи просматривается фрагмент списка записей
     * @param int $limitnum - сколько записей нужно извлечь из базы
     * @param object $conds[optional] - объект со списком свойств, по которым будет происходить поиск
     * @param bool $countonly[optional] - только вернуть количество записей по указанным условиям
     * @param string $orderby - критерии сортировки в sql
     */
    public function get_listing($conds=null, $limitfrom = null, $limitnum = null, $sort='', $fields='*', $countonly=false)
    {
        if ( ! $conds )
        {// если список потоков не передан - то создадим объект, чтобы не было ошибок
            $conds = new stdClass();
        }
        $conds = (object)$conds;
        if ( ! is_null($limitnum) AND $limitnum <= 0 )
        {// количество записей на странице может быть 
            //только положительным числом
            $limitnum = $this->dof->modlib('widgets')->get_limitnum_bydefault();
        }
        if ( ! is_null($limitfrom) AND $limitfrom < 0 )
        {//отрицательные значения номера просматриваемой записи недопустимы
            $limitfrom = 0;
        }
        $dopselect = '';
        if ( isset($conds->ownerid) )
        {// передали переменную из таблицы aclwarrantsagents
            $dopselect = 'w.ownerid = '.$conds->ownerid.' AND';
            unset($conds->ownerid);
        }
        $select = $this->get_select_listing($conds);
        $tblaclwarrant = $this->dof->storage('aclwarrants')->prefix().$this->dof->storage('aclwarrants')->tablename();
        $tblaclwa = $this->prefix().$this->tablename();
        if (strlen($select)>0)
        {
            $select = 'wa.'.preg_replace('/ AND /',' AND wa.',$select.' ').' AND ';
            $select = preg_replace('/ OR /',' OR wa.',$select);
            $select = str_replace('wa. (','(wa.',$select);
            $select = str_replace('wa.(','(wa.',$select);

        }
        if (!empty($sort))
        {
            $sort = "ORDER BY wa.".$sort;
        }
        $sql = "FROM {$tblaclwarrant} as w, {$tblaclwa} as wa
                WHERE {$select} {$dopselect} wa.aclwarrantid=w.id AND wa.basepcode != 'departments'";
        if ( $countonly )
        {// посчитаем общее количество записей, которые нужно извлечь
            return $this->count_records_sql("SELECT COUNT(*) {$sql}");
        }
        $sql = "SELECT {$fields} {$sql} {$sort}";
        return $this->get_records_sql($sql, null,$limitfrom, $limitnum);
    }
    
    /**
     * Возвращает фрагмент sql-запроса после слова WHERE
     * @param object $inputconds - список полей с условиями запроса в формате "поле_БД->значение" 
     * @return string
     */
    public function get_select_listing($inputconds)
    {
        // создадим массив для фрагментов sql-запроса
        $selects = array();
        $conds = fullclone($inputconds);
        if ( ! empty($conds) )
        {// теперь создадим все остальные условия
            foreach ( $conds as $name=>$field )
            {
                if ( $field )
                {// если условие не пустое, то для каждого поля получим фрагмент запроса
                    $selects[] = $this->query_part_select($name,$field);
                }
            } 
        }
        //формируем запрос
        if ( empty($selects) )
        {// если условий нет - то вернем пустую строку
            return '';
        }elseif ( count($selects) == 1 )
        {// если в запросе только одно поле - вернем его
            return current($selects);
        }else
        {// у нас несколько полей - составим запрос с ними, включив их всех
            return implode($selects, ' AND ');
        }
    }
    
    /**Добавляет запись в таблицу
     * 
     * @param $cpassed - изучаемый или пройденный курс
     * @return 
     */
    public function add($record)
    {
        $conds = clone $record;
        unset($conds->id);
        unset($conds->datecreate);
        unset($conds->begindate);
        unset($conds->duration);
        unset($conds->isdelegatable);
        $conds->status = array('draft','active');
        if ( $warrantagent = $this->get_record((array)$conds) )
        {// если такая история уже есть - все в порядке
            return $warrantagent->id;
        }
        return $this->insert($record);
    }
    
    /**Получаем список персон, которым назначена субдоверенности
     *
     * @param int $aclwarrantid - id доверенности
     * @return array
     */
    public function get_subwarrant_personlist($aclwarrantid)
    {
        $user = $this->dof->storage('persons')->get_bu();
        $res = array();
    	if (empty($aclwarrantid) OR ! is_int_string($aclwarrantid) OR intval($aclwarrantid) < 0)
    	{// id пустой или не число или меньше нуля - вернем пустой массив
    		return $res;
    	}
    	// ищем массив назначений по данной доверенности
    	if ( ! $persons = $this->get_records(array('aclwarrantid' => $aclwarrantid, 
    	                  'status' => array('draft','active')), null, 'id,personid') )
    	{// назначений нет - пустой массив
    	    return $res;
    	}
    	// если массив не пустой - обращаемся к storages/persons за именами
		foreach ($persons as $person)
		{// формуруем каждого в виде id=>ФИО
			if ( $user->id != $person->personid )
			{// выводим всех, кроме себя самого
				$res[$person->personid] = $this->dof->storage('persons')->get_fullname($person->personid);
			}
		}		
		// сортируем
		asort($res);
    	return $res;
    }
    
    /**Получаем список персон - претендентов на субдоверенность
     *
     * @param int $aclwarrantid - id доверенности
     * @param array - список тех, кого следует удалить из списка
     * @return array
     */
    public function get_subwarrant_applicantlist($aclwarrantid)
    {
        $persons = array();
        $user = $this->dof->storage('persons')->get_bu();
        
    	// id пустой или не число или меньше нуля - вернем пустой массив
        if (empty($aclwarrantid) OR ! is_int_string($aclwarrantid) OR intval($aclwarrantid) < 0)
    	{
    		return $persons;
    	}
    	// проверим, существует ли данная доверенность
    	if ( ! $warrant = $this->dof->storage('aclwarrants')->get_record(
    	                array('id' => $aclwarrantid, 'isdelegatable' => 0, 'status' => array('draft','active'))) )
    	{// если нельзя передоверять или статус не активный - вернем пустой массив
    	    return $persons;
    	}
    						
		// список персон - возможные претенденты на доверенность
		if ( ! $list = $this->dof->storage('persons')->get_records(array('status' => 'normal'), null,'id') )
		{// претендентов нет - выводить некого
		    return $persons;
		}
		foreach ($list as $value)
		{// исключаем из сиска самого пользователя
			if ($value->id != $user->id AND $this->dof->storage('persons')->is_access('use',$value->id) )
			{
			    $persons[$value->id] = $this->dof->storage('persons')->get_fullname($value->id);
			}
		}  
		// сортируем  
		asort($persons);
		//найдем тех, кто уже назначен
		$removelist = $this->get_subwarrant_personlist($aclwarrantid);
		// исключаем данных персон из общего списка	
		return array_diff($persons,$removelist);
    }
}
?>