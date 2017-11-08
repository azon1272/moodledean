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
/*
 * Хранилище для описания истории подписок в учебных периодах
 */
class dof_storage_agrouphistory extends dof_storage
{
    /**
     * @var dof_control
     */
    protected $dof;
    // **********************************************
    // Методы, предусмотренные интерфейсом plugin
    // **********************************************

    /** Устанавливает плагин в fdo
	 * @return bool
	 */
    public function install()
	{
        parent::install();
        $result = true;
        if ( $this->dof->plugin_exists('storage','cstreamlinks') )
        {// число зарисей в таблице cstreamlinks
            $num = 0;
            while ( $list = $this->dof->storage('cstreamlinks')->get_records_select('',null,'', '*', $num, 100) )
            {// выуживаем по 100 записей
                $num +=100;
                foreach ( $list as $cslink )
                {// добавляем запись
                    $result = $result && $this->add($cslink);
                }
            }
        }
        return $result;
	}

    /** Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * @param string $old_version - версия установленного в системе плагина
     * @return boolean
     * @access public
     */
    public function upgrade($oldversion)
    {
        global $CFG,$DOF;
        require_once($CFG->libdir.'/ddllib.php');//методы для установки таблиц из xml
        $result = true;
        
        return $result;
    }
    /** Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
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
        return 'agrouphistory';
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
        return array('storage'=>array('cstreamlinks'=>0));
    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
       return array(
       //слушаем участие академических групп в учебном процессе
                     array('plugintype'=>'storage', 'plugincode'=>'cstreamlinks', 'eventcode'=>'insert'),
                     array('plugintype'=>'storage', 'plugincode'=>'cstreamlinks', 'eventcode'=>'update'),
                     array('plugintype'=>'storage', 'plugincode'=>'cstreams',     'eventcode'=>'update'),
                     array('plugintype'=>'storage', 'plugincode'=>'agroups',      'eventcode'=>'update')
                     );
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
    public function catch_event($gentype,$gencode,$eventcode,$intvar,$mixedvar)
    {
        if ( $gentype === 'storage' AND $gencode === 'cstreamlinks' )
        {
            switch ($eventcode)
            {
                case 'insert': return $this->add($intvar);
                case 'update': return $this->add($intvar);
            }
        }
        if ( $gentype === 'storage' AND $gencode === 'cstreams' )
        {
            switch ($eventcode)
            {
                case 'update': 
                    if ( $cslinks = $this->dof->storage('cstreamlinks')->get_records(array('cstreamid'=>$intvar)) )
                    {// если есть связи с группой на поток
                        foreach ( $cslinks as $cslink )
                        {// ддля каждой добавим связку
                            $this->add($cslink);
                        }
                    }
                return true;
            }
        }
        if ( $gentype === 'storage' AND $gencode === 'agroups' )
        {
            switch ($eventcode)
            {
                case 'update':
                // при активации академической группы пробуем активировать все ее связи с потоками
                    if ( ! $cstreamlinks = $this->dof->storage('cstreamlinks')->get_records(array('agroupid'=>$intvar)) )
                    {// связей нет - все ок
                        return true;
                    }
                    foreach ( $cstreamlinks as $cslid=>$link )
                    {// для каждой связи запускаем активацию
                        $this->add($cslid);
                    }
                    return true;
                break;
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
        return 'block_dof_s_agrouphistory';
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************

    /**Добавляет запись в таблицу
     * 
     * @param int $cslinkid - id из таблицы cstreamlinks
     * @return bool
     */
    public function add($cslink)
    {// если передали не объект, а id
        if ( ! is_object($cslink) )
        {//если передан не курс, а его id
            $cslink = $this->dof->storage('cstreamlinks')->get($cslink);
            if ( ! $cslink )
            {//не получили курс
                return false;
            }
        }
        // группы нет как таковой - ничего не делаем и просто пропускаем
        if ( is_null($cslink->agroupid) )
        {
            return true;
        }
        //формируем объект для вставки
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cslink->cstreamid) OR 
                       ! $group = $this->dof->storage('agroups')->get($cslink->agroupid) )
        {// нет одной из записи
            return false;
        }
        if ( $cstream->status != 'active' OR $group->status != 'active' )
        {// если поток и группа не активны - создавать agrouphistory нельзя
            // вернем что все в порядке 
            return true;
        }
        // формируем объект для вставки
        $object = new stdClass();
        $object->agroupid = $cslink->agroupid;
        $object->ageid = $cstream->ageid;
        $object->agenum = $group->agenum;
        $object->changedate = time();
        if ( $this->is_exists(array('agroupid'=>$cslink->agroupid, 
                                     'agenum'=>$group->agenum, 'ageid'=>$cstream->ageid)) )
        {// если такая история уже есть - все в порядке
            return true;
        }
        return $this->insert($object);
    }
    
    /**Добавляет запись в таблицу
     * 
     * @param int $insert - объект для вставки - agroupid, agenum, ageid
     * @return bool
     */
    public function add_history($insert)
    {// если передали не объект, а id
        if ( ! is_object($insert) )
        {
            return false;
        }
        // группы нет как таковой - ничего не делаем и просто пропускаем
        if ( is_null($insert->agroupid) OR !$this->get($insert->agroupid) )
        {
            return false;
        }
        //формируем объект для вставки
        $forinsert = new stdClass();
        $forinsert->agroupid = $insert->agroupid;
        $forinsert->agenum = $insert->agenum;
        $forinsert->ageid = $insert->ageid;
        $forinsert->changedate = time();
        if ( $this->is_exists(array('agroupid'=>$forinsert->agroupid, 
                                     'agenum'=>$forinsert->agenum, 'ageid'=>$forinsert->ageid)) )
        {// Если такая история уже есть, ошибка
            return false;
        }
        return $this->insert($forinsert);
    }
    
    /** Получить текущую информацию об учебных подписках и периодах
     * 
     * @param int $agroupid - id группы (таблица agroups), 
     * по которой запрашивается история
     * @return object - объект из таблицы agrouphistory, или false
     */
    public function get_actual_learning_data($agroupid)
    {
        $list = $this->get_records(array('agroupid'=>$agroupid), 'changedate DESC, id DESC', '*', 0, 1);
        if ( is_array($list) )
        {// если получили массив - значит вернем его единственный элемент
            return current($list);
        }else
        {// если нет - значит ничего не нашлось
            return false;
        }
    }
}    
?>