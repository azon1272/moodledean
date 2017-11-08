<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
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


/** Справочник учебных программ
 * 
 */
class dof_storage_schpresences extends dof_storage
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
        // Модификация базы данных через XMLDB
        $result = true;
        
        $dbman = $DB->get_manager();
        $table = new xmldb_table($this->tablename());
        if ($oldversion < 2014031400)
        {// добавим поле salfactor
            $field = new xmldb_field('mdlevent', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, 
                    null, null, null, 'orderid');
            if ( !$dbman->field_exists($table, $field) )
            {// поле еще не установлено
                $dbman->add_field($table, $field);
            }
            // добавляем индекс к полю
            $index = new xmldb_index('imdlevent', XMLDB_INDEX_NOTUNIQUE,
                    array('mdlevent'));
            if (!$dbman->index_exists($table, $index))
            {// если индекс еще не установлен
                $dbman->add_index($table, $index);
            }
        }
        return $result;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2014032600;
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
        return 'schpresences';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
		return array('storage'=>array('schevents'=>2009060800,
		                              'orders'=>2009052500,
		                              'persons'=>2009060400));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        // Пока событий не обрабатываем
        return array(// Обрабатываем подписку/отписку группы на поток
                     array('plugintype'=>'storage', 'plugincode'=>'schevents', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'schevents', 'eventcode'=>'update'),
                     );
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
        if ( $gentype === 'storage' AND $gencode === 'schevents' )
        {//обрабатываем события от справочника cstreamlink
            switch($eventcode)
            {
                //синхронизируем подписки группы
                case 'insert': 
                	if ( ! $cpassed = $this->dof->storage('cpassed')->get_records(array('cstreamid'=>$mixedvar['new']->cstreamid)) )
                	{
                		return true;
                	}
                	foreach ( $cpassed as $cpass )
                	{
                		$obj = new stdClass;
                		$obj->personid = $cpass->studentid;
                        $obj->eventid = $mixedvar['new']->id;
                        $this->save_present_student($obj);
                	}
                //удаляем подписки';
                case 'update': 
                    if ( empty($mixedvar['new']->status) )
                    {
                		return true;
                	}
                	if ( $mixedvar['new']->status == 'plan' OR $mixedvar['new']->status == 'completed' )
                    {
                		return true;
                	}
                	if ( ! $schpresences = $this->get_records(array('eventid'=>$mixedvar['new']->id)) )
                	{
                	    return true;
                	}
                	foreach ( $schpresences as $schpresence )
                	{
                	    return $this->delete($schpresence->id);
                	}
            }
        }
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
        return 'block_dof_s_schpresences';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    /** Сохранить статус присутствия/отсутствия ученика на занятии  
     * @param object $obj - запись в таблицу
     * @return mixed int id вставленной записи при вставке, bool true при обновлении
     * или false если операции не удались 
     */
    public function save_present_student($obj)
    {
        $params = array();
        $params['personid'] = $obj->personid;
        $params['eventid'] = $obj->eventid;
        if( $obj1 = $this->get_record($params) )
    	{ 
    		return $this->update($obj,$obj1->id);
    	}
    	return $this->insert($obj);
    }
    /** Сохранить список статусов присутствия/отсутствия учеников на занятии  
     * @param int $evid - id события
     * @param int $orid - id приказа
     * @param array $students - ключ - id персоны, значение - статус присутствия
     * @return bool true если все записи сохранились и false в остальных случаях
     */
    public function save_present_students($obj)
    {
    	$result = true;
    	//print_object($obj); 
    	foreach ($obj->presents as $cpid=>$presence)
        {
        	$obj->personid = $this->dof->storage('cpassed')->get_field($cpid, 'studentid');
        	$obj->present = $presence;
        	if ( ! $this->save_present_student($obj) )
        	{
        		$result = false;
        	}
        }
        return $result;
    }
    /** Получить статус присутствия ученика на занятии 
     * @param int $stid - id студента
     * @param int $evid - ученика
     * @return mixed int статус присутствия или bool false если событие не найдено
     */
    public function get_present_status($stid, $evid)
    {
        $params = array();
        $params['personid'] = $stid;
        $params['eventid'] = $evid;
    	if ( ! $obj = $this->get_record($params) )
    	{ 
    		return false;
    	}
    	return $obj->present;
    }
    /** Получить статусы присутствия учеников на занятии 
     * @param int $evid - id события
     * @return array ключ - id персоны, значение - статус присутствия 
     */
    public function get_present_students($evid)
    {
    	$mas = array();
    	if (  $presences = $this->get_records(array('eventid'=>$evid)) )
    	{
    	    foreach ( $presences as $student )
    	    {
    		    $mas[$student->personid] = $this->get_present_status($student->personid, $evid);
    	    }
    	}    	
    	return $mas;
    }


} 
?>