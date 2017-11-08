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


/** Класс стандартных функций интерфейса
 * 
 */
class dof_sync_schedule implements dof_sync
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
        return 2014031700;
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
        return 'ancistrus';
    }
    
    /** Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'sync';
    }
    /** Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'schedule';
    }
    /** Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('storage'=>array('schpresences'=>2014031400));

    }
    /** Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(array('plugintype'=>'storage','plugincode'=>'schpresences','eventcode'=>'insert'),
        			 array('plugintype'=>'storage','plugincode'=>'schpresences','eventcode'=>'delete'));
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
     * @param int $id - дополнительный параметр 
     * @param mixed $mixedvar - дополнительные параметры
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function catch_event($gentype,$gencode,$eventcode,$id,$mixedvar)
    {
		if ( $gentype === 'storage' OR $gencode === 'schpresences' )
        {//обрабатываем события от своего собственного справочника
            switch($eventcode)
            {
                case 'insert': return $this->create_event($mixedvar['new']);
                case 'delete': return $this->delete_event($mixedvar['old']);
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
    // **********************************************
    // Собственные методы
    // **********************************************
    /** Конструктор
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
        //$this->calendar = new calendar_event();
    }
    
    public function create_event($schpresence)
    {
        //print_object($schpresence);
        if ( ! is_object($schpresence) )
        {//если передан не курс, а его id
            if ( ! $schpresence = $this->dof->storage('schpresences')->get($schpresence) )
            {//не получили курс
                return false;
            }
        }
        if ( ! $dof_event = $this->dof->storage('schevents')->get($schpresence->eventid) )
        {
            return false;
        }
        if ( ! $person = $this->dof->storage('persons')->get($schpresence->personid) )
        {
            return false;
        }
        if ( empty($person->mdluser) )
        {
            return false;
        }
        $item = $this->dof->storage('programmitems')->get(
                $this->dof->storage('cstreams')->get_field($dof_event->cstreamid,'programmitemid'));
        $event = new stdClass;
        $event->name = $item->name;
        $event->description = $this->dof->storage('persons')->get_fullname_initials($person);
        $event->timestart = $dof_event->date;
        $event->timeduration = $dof_event->duration;
        $event->eventtype = 'user';
        $event->userid = $person->mdluser;
        $event->courseid = 0;
        if ( ! empty($item->mdlcourse) )
        {
            $event->eventtype = 'course';
            $event->courseid = $item->mdlcourse;
        }
        return true;
        // добавляем событие
        //$this->calendar->update($event);
        //print_object($this->calendar->properties());
        //$obj = new stdClass;
        //$obj->mdlevent = $this->calendar->properties()->id;
        //return $this->dof->storage('schpresences')->update($obj,$schpresence->id,true);
    }
    
    public function delete_event($schpresence)
    {
        if ( ! is_object($schpresence) )
        {//если передан не курс, а его id
            if ( ! $schpresence = $this->dof->storage('schpresences')->get($schpresence) )
            {//не получили курс
                return false;
            }
        }
        if ( empty($schpresence->mdlevent) )
        {
            return false;
        }
        $this->calendar->load($schpresence->mdlevent);
        $this->calendar->delete();
    }

}
?>