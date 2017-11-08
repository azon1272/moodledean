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


/** Связь учебных процессов (предмето-классов) с академическими группами
 * 
 */
class dof_storage_cstreamlinks extends dof_storage
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
        global $CFG;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        // Модификация базы данных через XMLDB
        // if ($result && $oldversion < 2008121000) 
        // {
        
        // }
        return true;// уже установлена самая свежая версия
     }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        // Версия плагина (используется при определении обновления)
		return 2012042500;
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
        return 'cstreamlinks';
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
        return 'block_dof_s_cstreamlinks';
    }

    // **********************************************
    //              Собственные методы
    // **********************************************
    /** Получить список связей по типу связи
     * @param string $typesync - статус потока
     * @return mixed array список связей или bool false если связи не найдены
     */
    public function get_typesync_cstreamlink($typesync)
    {
        if ( ! is_string($typesync) )
        {// данные неверного формата
            return false;
        }
        return $this->get_records(array('agroupsync'=>$typesync));
    }
    /** Получить список связей по академической группе
     * @param int $id - id академической группы
     * @return mixed array список связей или bool false если связи не найдены
     */
    public function get_agroup_cstreamlink($id)
    {
    	if ( ! is_int_string($id) )
    	{//входные данные неверного формата 
    		return false;
    	}
        return $this->get_records(array('agroupid'=>$id));
    }
    /** Получить список связей по учебному процессу 
     * @param int $id - id учебного процесса
     * @return mixed array список связей или bool false если связи не найдены
     */
    public function get_cstream_cstreamlink($id)
    {
    	if ( ! is_int_string($id) )
    	{//входные данные неверного формата 
    		return false;
    	}
        return $this->get_records(array('cstreamid'=>$id));
    }
    /** Получить информацию о связи академической группы с учебным процессом или false 
     * @param int $agid - id академической группы
     * @param int $csid - id учебного процесса
     * @return mixed object - запись со связью или bool false если запись не найдена 
     */
    public function get_link_cstreamlink($agid, $csid)
    {
    	if ( ! is_int_string($csid) OR ! is_int_string($agid) )
    	{//входные данные неверного формата 
    		return false;
    	}
        $params = array();
        $params['cstreamid'] = $csid;
        $params['agroupid'] = $agid;
        return $this->get_record($params);  

    }
    /** Получить тип связи связи академической группы с учебным процессом или false 
     * @param int $agid - id академической группы
     * @param int $csid - id учебного процесса
     * @return mixed string - запись со связью или bool false если запись не найдена 
     */
    public function get_type_cstreamlink($agid, $csid)
    {
    	if ( ! $cstreamlink = $this->get_link_cstreamlink($agid, $csid) )
    	{//входные данные неверного формата 
    		return false;
    	}
        $link = $this->get_list_agroupsync();
        if ( isset($link[$cstreamlink->agroupsync]) )
        {
            return $link[$cstreamlink->agroupsync];
        }
        return false;
    }
    /** Возвращает массив возможных типов связок
     * @return array
     */
    public function get_list_agroupsync()
    {
    	$link = array();
    	$link['full'] = $this->dof->get_string('full','cstreamlinks',null,'storage');
    	$link['norequired'] = $this->dof->get_string('norequired','cstreamlinks',null,'storage');
    	$link['nolink'] = $this->dof->get_string('nolink','cstreamlinks',null,'storage');
    	return $link;
    }
    /** Подписать одну учебную группу на один поток
     * @return int|bool - id новой записи в таблице cstreams
     *                    true - если такая запись уже существует
     *                    false - если произошла ошибка
     * @param int $agroupid - id группы в таблице agroups
     * @param int $cstreamid - id учебного потока в таблице cstreams
     * @param string $agroupsync[optional] -  тип связи с академической группой: 
     *                                        полная, 
     *                                        не обязательный курс, 
     *                                        нет связи 
     */
    public function enrol_agroup_on_cstream($agroupid, $cstreamid, $agroupsync='full')
    {
        if ( ! $agroupid OR ! $cstreamid )
        {// не переданы необходимые параметры
            return false;
        }
        // получаем все доступные виды связей ак. групп с потоками
        $syncs = array_keys($this->get_list_agroupsync());
        // проверяем, является ли переданный нам вариант допустимым
        if ( ! in_array($agroupsync, $syncs) )
        {// недопустимый вариант связи
            return false;
        }
        $obj = new stdClass();
        // заполняем объект переданными данными
        $obj->cstreamid  = $cstreamid;
        $obj->agroupid   = $agroupid;
        $obj->agroupsync = $agroupsync;
        // вставляем запись в базу и возвращаем результат
        return $this->insert($obj);
    }
    
    /** Находит общий поправочный зарплатный коэффициент для групп потока
     * @param int cstreamid - id потока
     * @return int 
     */
    public function get_salfactor_agroups($cstreamid,$full=false)
    {
        $salfactors = array();
        if ( ! $cstreamlinks = $this->dof->storage('cstreamlinks')->get_records(array('cstreamid'=>$cstreamid)) )
        {// групп нет - коэффициент равен 0
            if ( $full )
            {
                $salfactors['all'] = 0;
                return $salfactors;
            } 
            return 0;
        }
        $salfactor = 0;
        foreach ( $cstreamlinks as $link )
        {// пооожим коэффициенты каждого
            $agroupsalfactor = $this->dof->storage('agroups')->get_field($link->agroupid, 'salfactor');
            $salfactor += $agroupsalfactor;
            if ( $full )
            {
                $salfactors[$link->agroupid] = $agroupsalfactor;
            }
        }
        if ( $full )
        {
            $salfactors['all'] = $salfactor;
            return $salfactors;
        }
        return $salfactor;
    }
} 
?>