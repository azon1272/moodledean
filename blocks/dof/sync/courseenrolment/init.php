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


/** 
 * Класс стандартных функций интерфейса
 */
class dof_sync_courseenrolment implements dof_sync
{
    /**
     * @var $dof - содержит методы ядра деканата
     * @var $logs - содержит переменные, нужные для ведения логов
     * @var $cenrolcfg - переменная с данными из конфигурационного файла
     */
    protected $dof;
    protected $logs;
    protected $cenrolcfg;
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
        return true;
    }
    /** 
     * Метод, реализующий обновление плагина в системе
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
    /** 
     * Возвращает версию установленного плагина
     * @return string
     * @access public
     */
    public function version()
    {
        return 2015011300;
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
        return 'ancistrus';
    }
    
    /** 
     * Возвращает тип плагина
     * @return string 
     * @access public
     */
    public function type()
    {
        return 'sync';
    }
    /** 
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * @return string
     * @access public
     */
    public function code()
    {
        return 'courseenrolment';
    }
    /** 
     * Возвращает список плагинов, 
     * без которых этот плагин работать не может
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return array('modlib' =>  array('ama' => 2009082500),
                     'storage' => array('plans' => 2011020800,
                                        'cstreams' => 2011032900,
                                        'appointments' => 2013110100,
                                        'cpassed' => 2010123000,
                                        'programmitems' => 2011041406,
                                        'persons' => 2011020800),
                     'workflow' => array('cpassed' => 2015011300,
                                         'cstreams' => 2015011300),
                     'im'   =>    array('journal' => 2011021500));

    }
    /** 
     * Список обрабатываемых плагином событий 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(
                // События смены статусов у cpassed и cstreams
                array('plugintype'=>'workflow', 'plugincode'=>'cpassed',  'eventcode'=>'cpassed_active'),
                array('plugintype'=>'workflow', 'plugincode'=>'cpassed',  'eventcode'=>'cpassed_not_active'),
                array('plugintype'=>'workflow', 'plugincode'=>'cstreams', 'eventcode'=>'cstreams_active'),
                array('plugintype'=>'workflow', 'plugincode'=>'cstreams', 'eventcode'=>'cstreams_passive'),
                array('plugintype'=>'workflow', 'plugincode'=>'cstreams', 'eventcode'=>'cstreams_not_active'),
                // События работы справочников
                array('plugintype'=>'storage',  'plugincode'=>'cpassed',  'eventcode'=>'insert'),
                array('plugintype'=>'storage',  'plugincode'=>'cpassed',  'eventcode'=>'delete'),
                array('plugintype'=>'storage',  'plugincode'=>'cstreams', 'eventcode'=>'insert'),
                array('plugintype'=>'storage',  'plugincode'=>'cstreams', 'eventcode'=>'update'),
                array('plugintype'=>'storage',  'plugincode'=>'cstreams', 'eventcode'=>'delete')
        );
    }
    /** 
     * Требуется ли запуск cron в плагине
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        $interval = $this->get_cfg('sync_interval');
        return $interval;
    }
    
    /** 
     * Проверяет полномочия на совершение действий
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
    /** 
     * Обработать событие
     * 
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
        if ( $gentype === 'workflow' AND $gencode === 'cpassed' )
        {
            switch($eventcode)
            {
                // Выполнение действий при активации cpassed'а
                case 'cpassed_active': return $this->event_enrol_student($id);
                // Выполнение действий при остановке cpassed'а
                case 'cpassed_not_active': return $this->event_unenrol_student($id);
            }
        }
        if ( $gentype === 'workflow' AND $gencode === 'cstreams' )
        {
            switch($eventcode)
            {
                // Выполнение действий при активации cstreams'а
                case 'cstreams_active': return $this->event_enrol_teacher($id);
                // Выполнение действий при переходе cstreams'а в неактивные статусы
                case 'cstreams_passive': return $this->mdl_delete_cstream_group($id);
                // Выполнение действий при остановке cstreams'а
                case 'cstreams_not_active': return $this->event_unenrol_teacher($id);
            }
        }
        if ( $gentype === 'storage' AND $gencode === 'cpassed' )
        {
            switch($eventcode)
            {
                // При добавлении ничего не происходит
                case 'insert': return true;
                // Отписать при удалении подписки
                case 'delete': return $this->event_unenrol_student($id, $mixedvar);
            }
        }
        if ( $gentype === 'storage' AND $gencode === 'cstreams' )
        {
            switch($eventcode)
            {
                // При добавлении ничего не происходит
                case 'insert': return true;
                // При добавлении ничего не происходит
                case 'update': return $this->event_update_cstream($id, $mixedvar);
                // Отписать при удалении подписки
                case 'delete': return $this->event_unenrol_teacher($id, $mixedvar);
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
    public function cron($loan,$messages)
    {
        // Если там указан false (null, 0 или переменная не существует)
        // то мы не делаем синхронизацию
        if ( !$this->get_cfg('sync_enabled') )
        {
            return true;
        }
        
        // $loan - уровень загрузки. 3 - пониженная нагрузка
        // если загрузка системы < 3, то не выполнять синхр-ю
        if ( 3 == $loan )
        {
            return $this->sync_grades();
        }
        else
        {   
            return true;
        }
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
    // **********************************************
    // Собственные методы
    // **********************************************
    /** 
     * Конструктор
     * @param dof_control $dof - это $DOF - методы ядра деканата
     */
    public function __construct($dof)
    {
        $this->dof = $dof;
    }
    
    /** 
     * Подписать пользователя на курс
     * 
     * @return (bool)
     *               - true если пользователя удалось подписать
     *               - false если произошла ошибка
     * @param int $mdlcourseid - id курса в Moodle на который подписывается пользователь
     * @param int $mdluserid - id пользователя в moodle
     * @param int $mdlgroupid[optional] - id группы, в которую будет записан пользователь или null, если пользователь
     *                                    не будет записан в группу
     * @param int $mdlroleid[optional] - id роли прльзователя в курсе (из таблицы moodle). Роль по умолчанию - ученик.
     * @param int $timeend[optional] - время окончания обучения на курсе в формете unixtime 
     *                                 (при наступлении этой даты пользователь булет отписан с курса)
     * @param bool $hidden[optional] - записать пользователя в скрытом режиме (он не будет отображаться в
     *                                 списке пользователей для учеников и учителей курса)
     * 
     * @todo добавить обработку исключений, когда появятся соответствующие классы
     */
    public function mdl_enrol_to_course($mdlcourseid, $mdluserid, $type = 'student', $mdlgroupid = false, 
                                        $mdlroleid = false, $timeend = 0, $hidden = false)
    {
        // подписываем ученика на курс Moodle, используя модуль ama
        if ( ! $this->dof->modlib('ama')->course($mdlcourseid)->role($mdlroleid,$type)->enrol($mdluserid, $timeend, $hidden) )
        {// не удалось подписать ученика на курс
            return false;
        }
        
        if ( $mdlgroupid )
        {// записываем ученика в группу, если это необходимо
            if ( ! $this->mdl_add_to_group($mdlcourseid,$mdlgroupid,$mdluserid) )
            {// подписать пользователя в группу не удалось
                // @todo должны ли мы возвращать false при этом?
                return false;
            }
        }
        // все действия произведены успешно
        return true;
    }
    
    /** Отписать пользователя из курса
     * 
     * @return 
     *        - true если пользователя удалось подписать
     *        - false если произошла ошибка
     * @param int $mdlcourseid - id курса в Moodle с которого отписывается пользователь
     * @param int $mdluserid - id пользователя в moodle
     * 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function mdl_unenrol_from_course($mdlcourseid, $mdluserid, $type = 'student')
    {
        // отписываем пользователя из курса, используя модуль ama
        return $this->dof->modlib('ama')->course($mdlcourseid)->role(false,$type)->unenrol($mdluserid);
    }
    
    /** Записать пользователя moodle в группу moodle
     * 
     * @return (bool)
     * @param int $mdlcourseid - id курса в Moodle в котором находится группа
     * @param int $mdlgroupid - id группы в курсе, куда будет записываться пользователь
     * @param int $mdluserid - id пользователя в moodle
     * 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function mdl_add_to_group($mdlcourseid,$mdlgroupid,$mdluserid)
    {
        if ( ! $this->dof->modlib('ama')->course($mdlcourseid)->group(false)->is_exists($mdlgroupid) )
        {// если группа была удалена, и подписать мы в нее никого не можем - то ничего не делаем
            //dof_mtrace(2, 'sync/courseenrolment: coud not find mdlgroupid='.$mdlgroupid);
            return true;
        }
        // подписываем пользователя в группу,  используя модуль ama
        return $this->dof->modlib('ama')->course($mdlcourseid)->group($mdlgroupid)->add_member($mdluserid);
    }
    
    /** Получить id курса в moodle по id курса FDO
     * 
     * @return int|bool
     * @param int $programmitemid - id курса в таблице programmitems
     */
    protected function get_mdl_course($programmitemid)
    {
        if ( ! is_numeric($programmitemid) )
        {// неправильный формат данных
            return false;
        }
        $mdlcourseid =  $this->dof->storage('programmitems')->get_field($programmitemid, 'mdlcourse');
        if ( ! trim($mdlcourseid) )
        {// id курса не найден
            return false;
        }
        // возвращаем id курса в moodle
        return $mdlcourseid;
    }
    
    /** Получить id группы в moodle по id учебного потока в FDO
     * 
     * @return int|bool
     * @param int $cstreamid - id академической группы в таблице agroups
     */
    protected function get_mdl_group($cstreamid)
    {
        if ( ! is_numeric($cstreamid) )
        {// неправильный формат данных
            return false;
        }
        $mdlgroupid = $this->dof->storage('cstreams')->get_field($cstreamid, 'mdlgroup');
        if ( ! trim($mdlgroupid) )
        {// id группы не найден
            return false;
        }
        // возвращаем id курса в moodle
        return $mdlgroupid;
    }
    
    /** 
     * Получить id пользователя moodle по id персоны деканата
     * 
     * @param int $personid - id пользователя в таблице persons
     * 
     * @return int|bool
     */
    protected function get_mdl_userid($personid)
    {
        if ( ! is_numeric($personid) )
        {// неправильный формат данных
            return false;
        }
        $mdluserid = $this->dof->storage('persons')->get_field($personid, 'mdluser');
        if ( ! trim($mdluserid) )
        {// id курса не найден
            return false;
        }
        // возвращаем id курса в moodle
        return $mdluserid;
    }
    
    /** Добавить пользователя в группу moodle
     * 
     * @return bool
     * @param int $programmitemid - id курса в хранилище programmitems
     * @param int $cstreamid - id учебного потока в таблице cstreams, привязанного к группе moodle 
     * @param int $personid - id персоны деканата в хранилище persons
     * 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function add_to_group($programmitemid,$cstreamid,$personid)
    {
        if ( ! $mdlcourseid = $this->get_mdl_course($programmitemid) )
        {// указанный курс FDO не синхронизирован с курсом Moodle
            return false;
        }
        if ( ! $mdluserid = $this->get_mdl_userid($personid) )
        {// указанный пользователь FDO не синхронизирован с Moodle
            return false;
        }
        if ( ! $mdlgroupid = $this->get_mdl_group($cstreamid) )
        {// поток не синхронизирован с группой moodle - это ошибка
            return false;
        }
        // все идентефикаторы есть - можем приступать к записи в группу
        return $this->mdl_add_to_group($mdlcourseid, $mdlgroupid, $mdluserid);
    }
    
    /** Создать новую группу для потока в указанном курсе Moodle.
     * Одновремено записывает id этой группы в поток
     * @todo создавать нормальное имя группы а не название потока
     * 
     * @param int $mdlcourseid - id курса в Moodle
     * @param int $cstreamid - id учебного потока, для которого создается группа
     * 
     * @return bool|int - id созданной в Moodle группы или false в случае ошибки
     */
    protected function mdl_create_cstream_group($mdlcourseid, $cstreamid)
    {
        if ( ! $this->dof->modlib('ama')->course(FALSE)->is_exists($mdlcourseid) )
        {// аккуратно обходим API модуля ama, НЕ ДАВАЯ ЕМУ СОЗДАТЬ КУРС при проверке его существования
            // если курс не существует - не продолжаем
            return false;
        }
        
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
        {// проверяем, существует ли поток, для которого создается группа
            return false;
        }
        
        // Формируем название группы: Учитель + период + название предмето-класса
        $groupname = '';
        if ( $teacherid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id) )
        {// если у потока есть учитель - то запомним его имя
            $groupname .= $this->dof->storage('persons')->get_fullname_initials($teacherid);
        }
        if ( $agename = $this->dof->storage('ages')->get_field($cstream->ageid, 'name') )
        {
            $groupname .= ' '.$agename;
        }
        $groupname .= ' '.$cstream->name;
        
        // курс точно существует, и НЕ БУДЕТ СОЗДАН ПРИ ПОПЫТКЕ К НЕМУ ОБРАТИТЬСЯ
        // Теперь попробуем создать в нем группу и назвать ее нужным именем
        $data = new stdClass();
        $data->name = $groupname;
        if ( ! $group = $this->dof->modlib('ama')->course($mdlcourseid)->group() )
        {
            return false;
        }
        if ( ! $group->update($data) )
        {
            return false;
        }
        // записываем id созданной группы в поток
        $cstreamobj = new stdClass();
        $cstreamobj->id       = $cstreamid;
        $cstreamobj->mdlgroup = $group->get_id();
        if ( ! $this->dof->storage('cstreams')->update($cstreamobj) )
        {
            $mdlgroupid = false;
        }
        return $group->get_id();
    }
    
    /** Удалить группу Moodle из курса при приостановке или завершении потока.
     * Удаляет группу и отписывает из нее всех учеников
     * 
     * @todo сделать удаление группы через ama, когда zтам появится возможность удалять группу не зная курс
     * Сейчас мы можем попытаться получить предмет, а из него курс moodle, и только потом удалять группу.
     * Однако, у нас нет гарантии что курс moodle проставлен везде, или что курс у дисциплины
     * не сменился. А id группы у нас есть, так что сейчас используем API Moodle и ждем переписывания ama
     * 
     * @param int $cstreamid - id потока, который 
     * 
     * @return bool
     */
    public function mdl_delete_cstream_group($cstreamid)
    {
        global $CFG;
        require_once($CFG->dirroot.'/group/lib.php');
        if ( ! $mdlgroupid = $this->dof->storage('cstreams')->get_field($cstreamid, 'mdlgroup') )
        {// нет группы Moodle - значит и удалять ничего не нужно
            return true;
        }
        
        if ( ! groups_delete_group($mdlgroupid) )
        {
            return false;
        }
        // удаляем группу из самого потока
        $cstreamobj = new stdClass();
        $cstreamobj->id = $cstreamid;
        $cstreamobj->mdlgroup = 0;
        return $this->dof->storage('cstreams')->update($cstreamobj);
    }
    
    /** Подписать пользователя на курс moodle
     * 
     * @return bool
     * @param int $programmitemid - id курса в хранилище programmitems
     * @param int $personid - id персоны деканата в хранилище persons
     * @param int $cstreamid[optional]  - id учебного потока в таблице cstreams, привязанного к группе moodle
     * @param int $mdlroleid[optional] - id роли прльзователя в курсе (из таблицы moodle). Роль по умолчанию - ученик.
     * @param int $timeend[optional] - время окончания обучения на курсе в формете unixtime 
     *                                 (при наступлении этой даты пользователь булет отписан с курса)
     * @param bool $hidden[optional] - записать пользователя в скрытом режиме (он не будет отображаться в
     *                                 списке пользователей для учеников и учителей курса)
     *                                 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function enrol_to_course($programmitemid, $personid, $cstreamid = null, $type = 'student', 
                                    $mdlroleid = false, $timeend = 0, $hidden = false)
    {
        if ( ! $mdlcourseid = $this->get_mdl_course($programmitemid) )
        {// указанный курс FDO не синхронизирован с курсом Moodle
            return false;
        }
        if ( ! $mdluserid = $this->get_mdl_userid($personid) )
        {// указанный пользователь FDO не синхронизирован с Moodle
            return false;
        }
        if ( $cstreamid )
        {// если указан id потока - то найдем его группу
            if ( ! $mdlgroupid = $this->get_mdl_group($cstreamid) )
            {// поток указан, но он не синхронизирован с группой moodle 
                // пробуем создать собственную группу Moodle, с названием потока
                if ( ! $mdlgroupid = $this->mdl_create_cstream_group($mdlcourseid, $cstreamid) )
                {// группу создать не удалось - не приписываем ученика к группе
                    $mdlgroupid = false;
                }
            }
        }else
        {// id потока не указан - просто создадим переменную с null-значением
            $mdlgroupid = false;
        }
        // все идентефикаторы есть - можем приступать к подписке на курс
        return $this->mdl_enrol_to_course($mdlcourseid, $mdluserid, $type, $mdlgroupid, $mdlroleid, $timeend, $hidden);
    }
    
    /** Отписать пользователя с курса modle
     * 
     * @return bool
     * @param int $programmitemid - id курса в хранилище programmitems
     * @param int $personid - id персоны деканата в хранилище persons
     * 
     * @todo добавить генерацию исключений когда появятся соответствующие классы
     */
    public function unenrol_from_course($programmitemid, $personid, $type = 'student')
    {
        if ( ! $mdlcourseid = $this->get_mdl_course($programmitemid) )
        {// указанный курс FDO не синхронизирован с курсом Moodle');
            return false;
        }
        if ( ! $mdluserid = $this->get_mdl_userid($personid) )
        {// указанный пользователь FDO не синхронизирован с Moodle');
            return false;
        }
        // все идентификаторы есть - можем запускать процедуру отписки');
        return $this->mdl_unenrol_from_course($mdlcourseid, $mdluserid, $type);
    }
    
	/**
     * Вернуть массив с настройками или одну переменную
     * 
     * @param string $key - название искомого параметра
     * @return mixed
     * @author Evgeniy Yaroslavtsev
     */
    public function get_cfg($key=null)
    {
        if (! isset($this->cenrolcfg) OR empty($this->cenrolcfg))
        {
            if ( file_exists($cfgfile = $this->dof->plugin_path($this->type(),$this->code(),'/cfg/cfg.php')) )
            {
                include ($cfgfile);
                $this->cenrolcfg = $cenrolcfg;
            }else
            {
                return null;
            }
        }
        
        if (empty($key))
        {
            return $this->cenrolcfg;
        }else
        {
            return (@$this->cenrolcfg[$key]);
        }
    }
    
    /**
     * Синхронизирует оценки заданного в конфиге количества cstream`ов
     * 
     * Тут на просроченность не смотрим
     * 
     * @return bool успешность
     * @author Evgeniy Yaroslavtsev
     */
    public function sync_grades()
    {
        $success = true;
        
        // в этом нет необходимости, но сразу выявим ошибки, если они есть
        $this->init_logs();
        // Удалим старые (а вот это нужно и только после инициализации можно)
        $this->delete_old_logs();
        
        // Пишем в лог
        $this->log_get_str('start_sync');
        
        // Получаем из конфига кол-во синхронизируемое за один вызов метода
        $limit = $this->get_cfg('sync_cstream_at_time');
        if (!$limit)
        {
            // Пишем в лог
            $this->log_get_str('not_found_cfg_param', 'sync_cstream_at_time', true);
            return false;
        }
        
        // Получаем cstream`ы которые давно синхронизировались
        $cstreamids = $this->dof->storage('cstreams')->get_old_sync_cstreams($limit);
        if (!$cstreamids)
        {
            // Выясняем: ошибка произошла или таблица пуста
            if ( !$this->dof->storage('cstreams')->count_records_select() )
            {
                // Пишем в лог
                $this->log_get_str('table_is_empty', 'cstream');
                return true;
            }
            else
            {
                // Пишем в лог
                $this->log_get_str('error_get_from_table', 'cstream', true);
                return false;
            }
        }
        
        // По всем 
        foreach ($cstreamids as $cstream)
        {
            // Там получаем cstream заного, потому что во время исполнения
            // цикла он мог быть уже закрыт

            $synccstream = $this->sync_cstream($cstream->id);
            
            $success = $success AND $synccstream;
        }
        
        // Пишем в лог
        if ($success)
        {
            $this->log_get_str('end_sync_success');
        }
        else
        {
            $this->log_get_str('end_sync_err', null, true);
        }
        
        return $success;
    }
    
    /**
     * Синхронизирует оценки по всем cpassed`ам cstream`а
     * 
     * @param int $cstreamid id cstream
     * @param bool $closing[optional] Если закрываем cstream 
     * @param bool $execute исполнять ли автоматически приказ или только сохранить
     * (т.е. закрываем не здесь, но значит все cpassed надо обязательно проставить)
     * @return bool
     * @author Evgeniy Yaroslavtsev
     */
    public function sync_cstream($cstreamid, $closing = false, $execute = true)
    {
        $success = true;
        
        // Пишем в лог
        $this->log_get_str('start_sync_cstream', $cstreamid);
        

        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        if (!$cstream)
        {
            // Пишем в лог
            $this->log_get_str('error_get', "cstream (id={$cstreamid})", true);
            $this->log_get_str('end_sync_cstream_err', $cstreamid, true);
            return false;
        }

        // Если он уже закрыт
        if ('active' != $cstream->status)
        {
            $this->log_get_str('cstream_is_closed', $cstream->id);
            $this->log_get_str('end_sync_cstream_success', $cstream->id);
            return true;
        }

        // Получаем programmitem
        $pitem = $this->dof->storage('programmitems')->get($cstream->programmitemid);
        if (!$pitem)
        {
            // Пишем в лог
            $this->log_get_str('error_get', "programmitem (id={$cstream->programmitemid})", true);
            $this->log_get_str('end_sync_cstream_err', $cstream->id, true);
            return false;
        }

        // смотрим, нужно ли синхронизировать
        if (!$pitem->gradesyncenabled)
        {
            // Пишем в лог
            $this->log_get_str('sync_disabled', $cstream->id);
            $this->log_get_str('end_sync_cstream_success', $cstream->id);
            return true;
        }

        $gradedata = new stdClass();
        $gradedata->id = $cstream->id;
        $gradedata->teacherid = $cstream->teacherid;
        $gradedata->ageid = $cstream->ageid;
        $gradedata->programmitemid = $cstream->programmitemid;
        $gradedata->scale = $pitem->scale;
        $gradedata->mingrade = $pitem->mingrade;
        $gradedata->grade = array();
        
        // Получаем programmitem и смотрим, нужно ли синхронизировать 
        //$cpasseds = $this->dof->storage('cpassed')->get_list('cstreamid', $cstream->id, 'status', 'active');
        $cpasseds = $this->dof->storage('cpassed')->get_records_select("status='active' AND cstreamid='{$cstream->id}'");
        if ($cpasseds)
        {
            foreach ($cpasseds as $cpassed)
            {
                $scalegrade = $this->get_scalegrade($cpassed, $pitem);
                
                // Если ошибка
                if ( false === $scalegrade )
                {
                    $this->log_get_str('error_get_scalegrade', $cpassed->id, true);
                    $success = false;
                    continue;
                }
                
                // Если в любом случае нужно закрывать
                if ($closing)
                {
                    // Пишем оценку в массив оценок
                    $gradedata->grade[$cpassed->id] = $scalegrade;
                    continue;
                }
                
                // Если нет пока оценки
                if ( null === $scalegrade )
                {
                    // Если включать в ведомость без оценки
                    if ($pitem->incjournwithoutgrade)
                    {
                        $this->log_get_str('not_passed_yet_but_included', $cpassed->id);
                    }
                    else
                    {
                        // Оценку не пишем
                        $this->log_get_str('not_passed_yet', $cpassed->id);
                        continue;
                    }
                }
                else
                {
                    // Если оценка неудовлетворительна
                    if ( !$this->dof->storage('programmitems')->is_positive_grade($pitem->id, $scalegrade) )
                    {
                        // И указано, что такие в ведомость не включать
                        if ( !$pitem->incjournwithunsatisfgrade )
                        {
                            $this->log_get_str('unsatisf_grade_not_included', $cpassed->id);
                            continue;
                        }
                    }
                }
                
                // Пишем оценку в массив оценок
                $gradedata->grade[$cpassed->id] = $scalegrade;
            }
        }
        else
        {
            $this->log_get_str('empty_cstream', $cstream->id);
        }
        
        // Ведомость будем делать, только если есть оценки, которые еще не записаны в cpassed
        if ( !empty($gradedata->grade) )
        {
            // Подключаем класс для создания ведомости
            $orderitogpath = $this->dof->plugin_path($this->type(),$this->code(),'/order_itog_grades.php');
            if ( !file_exists($orderitogpath) )
            {
                $this->log_get_str('error_open_file', $orderitogpath, true);
                $this->log_get_str('end_sync_cstream_err', $cstream->id, true);
                return false;
            }
            
            include_once($orderitogpath);
            // Создаем ведомость, там проставляется оценка и создается событие исполнения ведомости
            $orderitogobj = new dof_sync_courseenrolment_order_itog_grades($this->dof, $gradedata);
            if ( $execute )
            {// выполнить приказ
                if ( !$orderitogobj->generate_order_itog_grades() )
                {
                    $this->log_get_str('error_gen_journal', $cpassed->id, true);
                    $success = false;
                }
                else
                {
                    // После успешного исполнения приказа-ведомости отправляем событие о том,
                    // что cstream синхронизировался ведомость исполнена и можно проверить приказ
                    $this->dof->send_event($this->type(),$this->code(),'sync_cstream_completed', $cstream->id);
                }
            }else
            {// не выполнять
                if ( ! $orderid = $this->save_order_itog_grades($orderitogobj) )
                {
                    $this->log_get_str('error_gen_journal', $cpassed->id, true);
                    $success = false;
                }else
                {
                    return $orderid;
                }
            }
        }
        else
        {
            $this->log_get_str('nothing_sync_cstream', $cstream->id);
        }
        
        if ($success)
        {
            $this->log_get_str('end_sync_cstream_success', $cstream->id);
        }
        else
        {
            $this->log_get_str('end_sync_cstream_err', $cstream->id, true);
        }

        return $success;
    }
    
    public function save_order_itog_grades($orderitogobj)
    {
        if ( ! $orderobj = $orderitogobj->order_set_itog_grade() )
        {
            //ошибка формирования приказа выставления итоговых оценок
            $this->log_get_str('error_gen_journal', $orderitogobj->gradedata->id, true);
            return false;
        }
        if ( ! $orderid = $orderitogobj->save_order_itog_grade($orderobj) )
        {
            //ошибка  при сохранении приказа выставления итоговых оценок
            $this->log_get_str('error_save_journal', $orderitogobj->gradedata->id, true);
            return false;
        }
        return $orderid;
    }
    
    /**
     * Получает приведенную к шкале оценку
     * 
     * @param int|object $cpassed id cpassed`а или сам объект
     * @param int|object[optional] $pitem id programmitem`а или сам объект
     * @return bool|int Приведенная к шкале оценка, false в случае ошибки, null - если нет оценки пока
     * @author Evgeniy Yaroslavtsev
     */
    public function get_scalegrade($cpassed, $pitem = null)
    {
        // дальше проверяем и дополучаем параметры
        
        // ищем объект cpassed
        if (!is_object($cpassed))
        {
            $cpassedid = $cpassed;
            $cpassed = $this->dof->storage('cpassed')->get($cpassed);
            if (!$cpassed)
            {
                $this->log_get_str('error_get', "cpassed (id = {$cpassedid})", true);
                return false;
            }
        }
        
        // Если предположительно id передан programmitem`а
        if ($pitem AND !is_object($pitem))
        {
            $pitemid = $pitem;
            $pitem = $this->dof->storage('programmitems')->get($pitem);
        }
        
        // Если null или попытка выше не увенчалась успехом
        if (!$pitem)
        {
            $pitem = $this->dof->storage('programmitems')->get($cpassed->programmitemid);
            if (!$pitem)
            {
                $this->log_get_str('error_get', "programmitem (id = {$cpassed->programmitemid})", true);
                return false;
            }
        }

        
        // Теперь будем искать moodleuserid
        $person = $this->dof->storage('persons')->get($cpassed->studentid);
        if (!$person)
        {
            $this->log_get_str('error_get', "person (id = {$cpassed->studentid})", true);
            return false;
        }
        
        // Проверяем существование курса
        if ( !$this->dof->modlib('ama')->course(false)->is_exists($pitem->mdlcourse) )
        {
            $this->log_get_str('not_found_course', $pitem->mdlcourse, true);
            return false;
        }
        
        // Если альтернативный источник оценки не указан
        if (!$pitem->altgradeitem)
        {
            // Получаем оценку (не ранее указанной даты, т.е. отсекаем старые оценки)
            $grade = $this->dof->modlib('ama')->course($pitem->mdlcourse)->grade()->get_total_grade(
                    $person->mdluser, $cpassed->begindate, true);
                   
        }
        else
        {
            // Получаем оценку (не ранее указанной даты, т.е. отсекаем старые оценки)
            $grade = $this->dof->modlib('ama')->course($pitem->mdlcourse)->grade()->get_last_grade(
                    $person->mdluser, $pitem->altgradeitem, $cpassed->begindate, true);
        }
        
        // Параметры для сообщения в логи
        $a = new stdClass();
        $a->courseid = $pitem->mdlcourse;
        $a->userid = $person->mdluser;
        
        // Если ошибка
        if ( false === $grade)
        {
            $this->log_get_str('error_get_grade', $a, true);
            return false;
        }
        
        // Если оценки пока нет
        if ( null === $grade )
        {
            $this->log_get_str('not_rated', $a);
            return null;
        }
        
        // Если мы тут, значит нужно приводить оценку
        
        // получаем шкалу
        $scale = trim($pitem->scale);
        if ( !$scale )
        {
            // нет шкалы оценок - не можем выставлять оценки;
            $this->log_get_str('not_found_scale', $pitem->id, true); 
            return false;
        }

        // Преобразуем шкалу в массив
        $scale = $this->dof->storage('plans')->get_grades_scale_str($scale);
        // Приводим оценку к шкале
        $scalegrade = $this->bring_grade_to_scale($grade, $scale);
        
        if (false === $scalegrade)
        {
            $a = new stdClass();
            $a->grade = $grade;
            $a->pitemid = $pitem->id;
            $this->log_get_str('error_bring_to_scale', $a, true); 
            return false;
        }
        
        return $scalegrade;
    }
    
    /**
     * Приведение оценки (в процентах) к шкале из programmitem
     * 
     * @param float $grade Оценка в процентах
     * @param array $scale Массив элементов шкалы
     * @return string|bool Оценка сответствующая переданной шкале или false в случае неудачи
     * @author Evgeniy Yaroslavtsev
     */
    protected function bring_grade_to_scale($grade, $scale)
    {
        // Если шкала нас не устраивает
        if ( !$scale OR !is_array($scale) OR empty($scale) )
        {
            return false;
        }
        
        // немного преобразуем шкалу (нам не нравятся ключи той, которую нам передали)
        // там в ключах были значения элементов, а нам нужен просто порядковый номер начиная с 0
        $scale = array_values($scale);
        
        // Получаем шаг, кол-во процентов, соответствующих одному шагу шкалы
        // (сколько процентов приходится на один элемент шкалы)
        $step = 100/count($scale);
        
        // Получаем номер элемента в массиве шкалы, которому соответствует наша оценка
        $num = floor($grade/$step);
        
        // ... но с одной поправочкой - если наша оценка 100%, то мы получим $num,
        // который на 1 больше чем максимальный ключ массива
        if ($num == count($scale))
        {
            $num--;
        }
        
        // На всякий случай проверим
        $scalegrade = @$scale[$num];
        if (null === $scalegrade)
        {
            return false;
        }
        
        return $scalegrade;
    }
    
    /**
     * Метод для записывания студента на курс moodle
     * 
     * Производит проверку на возможность записать студента 
     * и записывает его на курс по cpassed
     * 
     * @param int $cpassedid - id записи в таблице cpassed
     *                         (Дисциплины, которые проходит или проходил ученик)
     * 
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_enrol_student($cpassedid)
    {
        // Получаем запись
        $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
        
        if ( ! is_object($cpassed) )
        {// Неправильный формат даных
            return false;
        }
        
        // подписываем пользователя на курс moodle
        return $this->enrol_to_course($cpassed->programmitemid, $cpassed->studentid,
                $cpassed->cstreamid);
    }
    
    /**
     * Метод для отписывания студента от курса moodle
     *
     * Проверяет возможность отписать студента и производит отписку, если это возможно
     *
     * @param int $cpassedid - id записи в таблице cpassed(Дисциплины, которые проходит или проходил ученик)
     * @param array $deletedobj - массив удаленных записей в таблице cpassed
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_unenrol_student($cpassedid = NULL, $deletedobj = NULL)
    {
        $result = true;
        
        if ( ! empty($deletedobj) )
        {// Записи были удалены из таблицы, обрабатываем их
            
            // Получаем массив статусов cpassed
            $statuses = array_keys($this->dof->workflow('cpassed')->get_meta_list('active'));
            
            foreach ( $deletedobj as $cpassed )
            {// Производим отписку для каждого из элементов
                
                // Получаем курс , на который ссылалась текущая подписка(cpassed)
                $mdlcourse = $this->dof->storage('programmitems')->get_field($cpassed->programmitemid, 'mdlcourse');
                // Получаем все активные cpassed персоны
                $cpasseds = $this->dof->storage('cpassed')->get_records(array(
                        'studentid' => $cpassed->studentid,
                        'status' => $statuses
                ));
                
                if ( ! empty($cpasseds) )
                {// Есть активные дисциплины
                    foreach ( $cpasseds as $cp )
                    {// Проверяем , ссылаются ли дисциплины на тот же курс,
                        // что и удаленная(дисциплина, от которой отписываем персону)
                        if ( $mdlcourse === $this->dof->storage('programmitems')->
                                get_field($cp->programmitemid, 'mdlcourse') )
                        {// Нашли такую - отписываться не требуется
                            continue;
                        }
                    }
                }
                
                // Проверка на оставшиеся подписки
                $params = array();
                $params['studentid'] = $cpassed->studentid;
                $params['programmitemid'] = $cpassed->programmitemid;
                $params['noid'] = $cpassed->id;
                $params['status'] = $statuses;
                $select = $this->dof->storage('cpassed')->get_select_listing($params);
                
                if ( ! $this->dof->storage('cpassed')->is_exists_select($select) )
                {// Если подписок на дисциплину больше нет - отписываем пользователя из курса moodle
                    $result = ( $result AND 
                            $this->unenrol_from_course($cpassed->programmitemid, $cpassed->studentid) );
                }
            }
        } else 
        {// Записи не были удалены - был осуществлен перевод в неактивный статус
            // Получаем запись
            $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
            if ( ! is_object($cpassed) )
            {// Неправильный формат даных
                return false;
            }
            
            // Получаем массив статусов cpassed
            $statuses = array_keys($this->dof->workflow('cpassed')->get_meta_list('active'));
            
            // Получаем курс , на который ссылается текущая подписка(cpassed)
            $mdlcourse = $this->dof->storage('programmitems')->get_field($cpassed->programmitemid, 'mdlcourse');
            // Получаем все активные cpassed персоны
            $cpasseds = $this->dof->storage('cpassed')->get_records(array(
                            'studentid' => $cpassed->studentid,
                            'status' => $statuses
            ));
            if ( ! empty($cpasseds) )
            {// Есть активные дисциплины
                unset($cpasseds[$cpassed->id]);
                foreach ( $cpasseds as $cp )
                {// Проверяем , ссылаются ли дисциплины на тот же курс, 
                 // что и исходная(дисциплина, от которой отписываем персону)
                    if ( $mdlcourse === $this->dof->storage('programmitems')->get_field($cp->programmitemid, 'mdlcourse') )
                    {// Нашли такую - отписываться не требуется
                        return $result;
                    }
                }
            }
            
            $result = ( $result AND 
                $this->unenrol_from_course($cpassed->programmitemid, $cpassed->studentid) );
        }
        return $result;
    }
    
    /**
     * Метод для записывания преподавателя на курс moodle
     *
     * Проверяет возможность записать преподавателя и производит запись, если это возможно
     *
     * @param int $cstreamid - id записи в таблице cstreams (Учебные процессы/Предмето-класс)
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_enrol_teacher($cstreamid)
    {
        // Получаем запись
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
        
        if ( ! is_object($cstream) )
        {// неправильный формат даных
            return false;
        }
        // Получаем ID пользователя
        $personid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id);
        
        // Подписываем преподавателя на курс moodle
        return $this->enrol_to_course($cstream->programmitemid, $personid, 
            null, 'teacher');
    }
    
   /**
     * Метод для отписывания преподавателя от курса moodle
     *
     * Проверяет возможность отписать преподавателя и производит отписку, если это возможно
     * Позволяет отписать преподавателя либо с существующего учебного процесса
     * по cstreamid, либо с удаленных учебных процессов, объекты которых переданы
     * в массиве $deletedobj
     *
     * @param int $cstreamid - id записи в таблице cstreams (Учебные процессы/Предмето-класс)
     * @param array $deletedobj - массив удаленных записей в таблице cstreams
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_unenrol_teacher($cstreamid = NULL, $deletedobj = NULL)
    {
        // Результат формируется комплексно
        $return = true;
        if ( ! empty($cstreamid) )
        {// Передали ID учебного процесса, пробуем отписать преподавателя

            /* 
             * Базовые проверки данных 
             */ 
            // Получаем запись учебного процесса
            $cstream = $this->dof->storage('cstreams')->get($cstreamid);
            if ( empty($cstream) )
            {// Не нашли учебный процесс
                return false;
            }
            // Получаем ID преподавателя
            $personid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id);
            if ( empty($personid) )
            {// Преподаватель не найден
                return false;
            }
        
            /* 
            * Получим все учебные процессы, ссылающиеся на один и тот же курс moodle,
            * учителем в которых является одна и та же персона.(Кроме переданного в $cstreamid)
            * Если такие процессы есть, то отписывать преподавателя от курса не нужно
            */
            // Получим ID курса Moodle по учебному процессу
            $mdlcourse = $this->dof->storage('programmitems')->
                get_field($cstream->programmitemid, 'mdlcourse');
            // Получим активные статусы дисциплин
            $statuses = array_keys($this->dof->workflow('programmitems')->get_meta_list('active'));
            // Получим все активные дисциплины, привязанные к курсу
            $programmitems = $this->dof->storage('programmitems')->get_records(array(
                'mdlcourse' => $mdlcourse,
                'status' => $statuses
            ));
            if ( empty($programmitems) )
            {// Нет дисциплин, привязанных к курсу moodle
                // @TODO Определить, что надо делать в таком случае. Скорее всего - удалять подписку 
                return false;
            }
            // Переформируем массив на ключи
            $programmitems = array_keys($programmitems);
            // Получим активные статусы договоров
            $statuses = array_keys($this->dof->workflow('eagreements')->get_meta_list('active'));
            // Получим все активные договора на работу у учителя
            $personeagerments = $this->dof->storage('eagreements')->get_records(array(
                'personid' => $personid,
                'status' => $statuses
            ));
            if ( empty($personeagerments) )
            {// Нет договоров на работу у преподавателя
                // @TODO Определить, что надо делать в таком случае. 
                return false;
            }
            // Переформируем массив на ключи
            $personeagerments = array_keys($personeagerments);
            // Получим активные статусы должостных назначений учителя
            $statuses = array_keys($this->dof->workflow('appointments')->get_meta_list('active'));
            //Получим все активные должостные назначения учителя
            $personappointments = $this->dof->storage('appointments')->get_records(array(
                'eagreementid' => $personeagerments,
                'status' => $statuses
            ));
            if ( empty($personappointments) )
            {// Нет назначений на должности у персоны, которая считается преподавателем
                // @TODO Определить, что надо делать в таком случае.
                return false;
            }
            // Переформируем массив на ключи
            $personappointments = array_keys($personappointments);
            // Получим активные статусы учебных процессов
            $statuses = array_keys($this->dof->workflow('cstreams')->get_meta_list('active'));
            // Получим все активные учебные процессы учителя, где он ведет тот же курс moodle
            $cstreams = $this->dof->storage('cstreams')->get_records(array(
                'appointmentid' => $personappointments,
                'programmitemid' => $programmitems,
                'status' => $statuses
            ));
            
            if ( ! empty($cstreams) )
            {// Есть активные процессы
                // Удалим из списка переданный процесс
                unset($cstreams[$cstreamid]);
            }
            if ( empty($cstreams) )
            {// Процессов преподавателя, связанных с курсом moodle ппереданного процесса
             // больше не осталось, можно удалять подписку преподавателя   
                $return = ( $return AND 
                        $this->unenrol_from_course($cstream->programmitemid, $personid, 'teacher')
                );
            }
        }
        if ( is_array($deletedobj) )
        {// Передали массив удаленных учебных процессов, обрабатываем каждый из них
            foreach ( $deletedobj as $cstream )
            {
                
                /*
                 * Базовые проверки данных
                 */
                if ( ! is_object($cstream) )
                {// Не передан объект
                    continue;
                }
                // Получаем ID преподавателя
                $personid = $this->dof->storage('cstreams')->get_cstream_teacherid($cstream->id);
                if ( empty($personid) )
                {// Преподаватель не найден
                    return false;
                }
                
                /*
                 * Получим все учебные процессы, ссылающиеся на один и тот же курс moodle,
                 * учителем в которых является одна и та же персона.
                 * Если такие процессы есть, то отписывать преподавателя от курса не нужно
                 */
                // Получим ID курса Moodle по учебному процессу
                $mdlcourse = $this->dof->storage('programmitems')->
                get_field($cstream->programmitemid, 'mdlcourse');
                // Получим активные статусы дисциплин
                $statuses = array_keys($this->dof->workflow('programmitems')->get_meta_list('active'));
                // Получим все активные дисциплины, привязанные к курсу
                $programmitems = $this->dof->storage('programmitems')->get_records(array(
                        'mdlcourse' => $mdlcourse,
                        'status' => $statuses
                ));
                if ( empty($programmitems) )
                {// Нет дисциплин, привязанных к курсу moodle
                 // @TODO Определить, что надо делать в таком случае. Скорее всего - удалять подписку
                    return false;
                }
                // Переформируем массив на ключи
                $programmitems = array_keys($programmitems);
                // Получим активные статусы договоров
                $statuses = array_keys($this->dof->workflow('eagreements')->get_meta_list('active'));
                // Получим все активные договора на работу у учителя
                $personeagerments = $this->dof->storage('eagreements')->get_records(array(
                        'personid' => $personid,
                        'status' => $statuses
                ));
                if ( empty($personeagerments) )
                {// Нет договоров на работу у преподавателя
                 // @TODO Определить, что надо делать в таком случае.
                    return false;
                }
                // Переформируем массив на ключи
                $personeagerments = array_keys($personeagerments);
                // Получим активные статусы должостных назначений учителя
                $statuses = array_keys($this->dof->workflow('appointments')->get_meta_list('active'));
                //Получим все активные должостные назначения учителя
                $personappointments = $this->dof->storage('appointments')->get_records(array(
                        'eagreementid' => $personeagerments,
                        'status' => $statuses
                ));
                if ( empty($personappointments) )
                {// Нет назначений на должности у персоны, которая считается преподавателем
                 // @TODO Определить, что надо делать в таком случае.
                    return false;
                }
                // Переформируем массив на ключи
                $personappointments = array_keys($personappointments);
                // Получим активные статусы учебных процессов
                $statuses = array_keys($this->dof->workflow('cstreams')->get_meta_list('active'));
                // Получим все активные учебные процессы учителя, где он ведет тот же курс moodle
                $cstreams = $this->dof->storage('cstreams')->get_records(array(
                        'appointmentid' => $personappointments,
                        'programmitemid' => $programmitems,
                        'status' => $statuses
                ));
                
                if ( empty($cstreams) )
                {// Процессов преподавателя, связанных с курсом moodle ппереданного процесса
                 // больше не осталось, можно удалять подписку преподавателя
                    
                    $return = ( $return AND
                        $this->unenrol_from_course($cstream->programmitemid, $personid, 'teacher')
                    );
                }
            }
        }
        return $return;
    }
    
    /**
     * Метод для отписывания/подписывания преподавателя на курс moodle рпи изменении учебного процесса
     *
     * @param int $cstreamid - id записи в таблице cstreams (Учебные процессы/Предмето-класс)
     * @param array $mixedvar - массив со старой и обновленной записью
     *
     * @return bool - false, если в ходе работы произошли ошибки
     *              - true, если работа завершена без ошибок
     * @access private
     */
    private function event_update_cstream($cstreamid, $mixedvar = NULL)
    {
        // Получаем запись
        $cstream = $this->dof->storage('cstreams')->get($cstreamid);
    
        if ( ! is_object($cstream) )
        {// неправильный формат даных
            return false;
        }
        
        if ( $mixedvar['new']->appointmentid != $mixedvar['old']->appointmentid AND 
             $mixedvar['old']->status != 'plan' )
        {// Преподаватель курса изменился
            
            if ( $mixedvar['old']->appointmentid != 0 )
            {// Отпишем старого преподавателя от курса
                
                // Получаем персону
                $person = $this->dof->storage('appointments')->
                                get_person_by_appointment($mixedvar['old']->appointmentid);
                if ( empty($person) )
                {// Персона не найдена
                    return false;
                }
                
                // Проверка
                $params = array();
                $params['teacherid'] = $person->id;
                $params['programmitemid'] = $mixedvar['old']->programmitemid;
                $params['noid'] = $mixedvar['old']->id;
                $params['status'] = array_keys($this->dof->workflow('cstreams')->get_meta_list('active'));
                $select = $this->dof->storage('cstreams')->get_select_listing($params);
                if ( ! $this->dof->storage('cstreams')->is_exists_select($select) )
                {// Если потоков у преподавателя больше нет, отписываем его с курса
                    if ( ! $this->unenrol_from_course($mixedvar['old']->programmitemid, $person->id, 'teacher') )
                    {// Ошибка при отписывании преподавателя
                        return false;
                    }
                }
            }
            
            if ( $mixedvar['new']->appointmentid != 0 )
            {// У нас есть новый преподаватель - запишем его на курс
                 // Получаем персону
                $person = $this->dof->storage('appointments')->
                                get_person_by_appointment($mixedvar['new']->appointmentid);
                
                if ( empty($person) )
                {// Персона не найдена
                    return false;
                }
                // Подписываем на курс
                if ( ! $this->enrol_to_course($mixedvar['new']->programmitemid, $person->id, null, 'teacher') )
                {// Ошибка при подписывании преподавателя
                    return false;
                }
            }
        }
        return true;
    }
    
    //*************************************************************************
    //
    // ДАЛЕЕ МЕТОДЫ ДЛЯ ВЕДЕНИЯ ЛОГОВ
    //
    // Почему эти методы? Есть ведь error_log!
    // - Этой функцией и пользуемся. А эти методы позволяют просто вызывать log_get_str
    // и больше ни о чем не думать.
    //
    // Особенности:
    // - Ничего не нужно инициализировать
    // - Ведет файл со всеми сообщениями и отдельно файл только для ошибок
    // - при вызове метода log_get_str метод сам определяет, писать ли в новые файлы
    //   (в названии файлов даты с точностью до секунды создания) или дописывать
    //   в те, которые найдет (подробности в методе find_just_writed_logs)
    // - Набор методов позволяет удалять старые логи (в конфиге задается срок хранения)
    //
    // Почему эти методы тут, а не в отдельном классе: они используют
    // $this->code(), $this->tyep(), $this->get_cfg(). Конечно все это не проблема,
    // но пока так. А вообще не дурно бы отдельным плагином сделать.
    //
    // Далее описано, что нужно сделать чтобы использовать эти методы:
    //
    // 1. Нужно где-то - хотя не обязательно - написать следующее:
    // $this->init_logs();
    // $this->delete_old_logs();
    //
    // 2. добавить в конфиг следующие параметры:
    // shelflife_logs (int) - срок хранения логов в днях
    // log (bool) - вести ли логи
    // just_writed_delay (int) - Какая пауза (в секундах) допустима при записи
    // логов, чтобы считать что конкретный файл логов сейчас используется для
    // записи. Используется при поиске файла логов, в который в данный момент
    // происходит запись
    // 
    // 3. в класс нужно добавить переменную logs
    //
    //*************************************************************************
    
    /**
     * Инициализация логов
     * 
     * Если не требуется инициализация или логи отключены, то ничего не происходит
     * 
     * @return nothing Если возникнут проблемы, то будет ошибка ввода вывода
     */
    public function init_logs()
    {
        global $CFG;
        
        // Если логи уже инициализированны или их не нужно вести, то возвращаем
        if ( isset($this->logs) OR !$this->get_cfg('log') )
        {
            return;
        }
        
        // Ну а если мы тут, то инициализируем
        
        $this->logs = new stdClass();
        
        // Задаем формат даты для названий файлов
        $this->logs->filedateformat = "%Y%m%d%H%M%S";
        
        // Устанавливаем директорию для логов 
        $this->logs->logpath = $this->dof->plugin_path($this->type(),$this->code(),'/dat/logs');
        // $this->logs->logpath = $CFG->dataroot."/cfg/dof/{$this->type()}/{$this->code()}";
        
        // Задаем базовые имена файлов с логами (перед ними будет размещаться дата в установленном формате)
        $this->logs->baselogname = 'log.txt';
        $this->logs->baseerrorlogname = 'errorlog.txt';
        
        $this->create_logs();
    }
    
	/**
     * Создание новых логов
     * 
     * Если будут ошибки чтения/записи, то ошибка исполнения будет
     *
     * @param bool $trytofind[optional] Пытаться ли искать лог, в который писать
     * (иначе создавать новый)
     * @author Evgeniy Yaroslavtsev
     */
    protected function create_logs($trytofind = true)
    {
        global $CFG;
        
        // !!! Если тут задать %Y%m%d, то на один день будет один файл лога,
        // он будет дописываться при нескольких вызовах за сутки данного метода
        // Если менять это значение, то стоит папку логов очистить - иначе старые
        // логи (со старым форматом названия) могут перестать удаляться или
        // наоборот при первом запуске все удалятся
        
        $needcreatelog = true;
        $needcreateerrorlog = true;
        
        // Если нужно пытаться искать
        if ($trytofind)
        {
            // то ищем
            $obj = $this->find_just_writed_logs();
            
            // исли что-то нашли
            if ($obj)
            {
                // если нашли файл для всех сообщений
                if ($obj->namel)
                {
                    // то его создавать не нужно
                    $needcreatelog = false;
                    $this->logs->logfilepath = $obj->namel;
                }
                
                // если нашли файл для ошибок
                if ($obj->namee)
                {
                    $needcreateerrorlog = false;
                    $this->logs->errorlogfilepath = $obj->namee;
                }
            }
        }
        
        // А дальше создадим, что не создали и заодно проверим возможность
        // открытия созданного для записи
        
        $filedate = strftime($this->logs->filedateformat);
        $path = $this->logs->logpath;
        
        if ($needcreatelog)
        {
            $this->logs->logfilepath = $path.'/'.$filedate.$this->logs->baselogname;
        }
        
        if ($needcreateerrorlog)
        {
            $this->logs->errorlogfilepath = $path.'/'.$filedate.$this->logs->baseerrorlogname;
        }
        
        // Создаем директорию для логов, если ее нет
        if ( !file_exists($path) )
        {
            mkdir($path, $CFG->directorypermissions, true);
        }
        
        // Создаем файл для логов, если нет
        $f = fopen($this->logs->logfilepath, 'a');
        fclose($f);

        // Создаем файл для лога ошибок, если нет
        $f = fopen($this->logs->errorlogfilepath, 'a');
        fclose($f);
    }
    
    /**
     * Удаление старых логов
     * 
     * Время создания определяет по названию файла (не по реальному времени
     * модификации файла)
     * Если будут ошибки чтения/записи, то будет ошибка исполнения
     * Если в папке с логами будут другие файлы, они скорее всего будут удалены
     *
     * Срок хранения указывается в конфиге
     * @author Evgeniy Yaroslavtsev
     */
    protected function delete_old_logs()
    {
        // Надо было инициализировать
        if (!isset($this->logs))
        {
            return;
        }
        
        // Получаем пороговую дату. Все файлы, созданные раньше нее удалим
        
        // Срок годности логов в секундах (параметр из конфига в днях)
        $shelflife = $this->get_cfg('shelflife_logs') * 24 * 60 * 60;
        $dateexpire = strftime($this->logs->filedateformat, time() - $shelflife);
        
        
        // Получаем список файлов директории логов (если она есть)
        
        // Смотрим есть ли директория логов
        if ( !file_exists($this->logs->logpath) )
        {
            // Если нет, то возвращаемся
            return true;
        }

        // Удаляем файлы, созданные раньше пороговой даты
        
        $dir = opendir($this->logs->logpath);
        
        while (false !== ($file = readdir($dir)))
        {
            $fullpath = $this->logs->logpath.'/'.$file;
            if ( is_file($fullpath) )
            {
                // Выделяем дату текущего файла
                $filedate = substr($file, 0, strlen($dateexpire));
                // Удаляем если дата файла меньше срока удаления
                if ( strcmp($filedate, $dateexpire) < 0 )
                {
                    unlink($fullpath);
                }
            }
        }
        
        closedir($dir);
    }
    
    /**
     * Ищет в директории логов два файла (один для всех сообщений, второй только
     * для ошибок), которые изменялись позже всех, при условии,
     * что прошло времени не более допустимого, указанного в конфиге
     * 
     * @return object|bool Полные пути к найденному файлу или false
     * Полные пути в следующем виде: объект с полями namel (полный путь к файлу
     * для всех сообщений) и namee (полный путь к файлу для ошибок) 
     * @author Evgeniy Yaroslavtsev
     */
    protected function find_just_writed_logs()
    {
        // На всякий случай проверяем
        if (!isset($this->logs->logpath))
        {
            return false;
        }
        
        // Для корректной работы функции получения времени последнего изменения файла
        clearstatcache();
        
        $path = $this->logs->logpath;
        // Длина даты в текущем формате
        $datelength = strlen(strftime($this->logs->filedateformat, time()));
        
        // Сюда запишем имя и время доступа к файлу для всех сообщений,
        // который изменялся последним
        $lastwritedl = new stdClass();
        $lastwritedl->name = '';
        $lastwritedl->time = 0;
        
        // Сюда запишем имя и время доступа к файлу для ошибок, который
        // изменялся последним
        $lastwritede = new stdClass();
        $lastwritede->name = '';
        $lastwritede->time = 0;
        
        // Смотрим, есть ли такая папка вообще
        if ( file_exists($path) )
        {
            $files = scandir($path);
            if (!$files)
            {
                // недолго думая
                return false;
            }
            else
            {
                foreach ($files as $file)
                {
                    $fullname = $path.'/'.$file;
                    if (is_file($fullname))
                    {
                        $changetime = filemtime($fullname);
                        
                        // получаем базовое имя
                        $basename = substr($file, $datelength);
                        
                        // Если это файл для всех сообщений
                        if ( $basename == $this->logs->baselogname )
                        {
                            // Если время его изменения больше (т.е. позже)
                            if ( $changetime > $lastwritedl->time )
                            {
                                $lastwritedl->name = $fullname;
                                $lastwritedl->time = $changetime; 
                            }
                        }
                        
                        // если это файл для сообщений об ошибках
                        if ( $basename == $this->logs->baseerrorlogname )
                        {
                            // Если время его изменения больше (т.е. позже)
                            if ( $changetime > $lastwritede->time )
                            {
                                $lastwritede->name = $fullname;
                                $lastwritede->time = $changetime; 
                            }
                        }

                    }
                }
            }
        }
        
        if ( !$this->get_cfg('just_writed_delay') )
        {
            return false;
        }

        // Задаем то, что будем возвращать
        $obj = new stdClass();
        $obj->namel = null;
        $obj->namee = null;
        
        // Теперь смотрим, что нашли
        // Если с момента изменения файла прошло времени не более допустимого, указанного в конфиге,
        // то это нам подходит
        if ( time() - $lastwritedl->time <= $this->get_cfg('just_writed_delay') )
        {
            $obj->namel = $lastwritedl->name;
        }
        
        if ( time() - $lastwritede->time <= $this->get_cfg('just_writed_delay') )
        {
            $obj->namee = $lastwritede->name;
        }

        return $obj;
    }
    
	/**
     * Ведение лога синхронизации, лога ошибок, вывод сообщений на экран
     *
     * @param string $message Сообщение об ошибке
     * @param bool[optional] $error Если это сообщение об ошибке
     * @author Evgeniy Yaroslavtsev
     */
    public function log($message, $error = false)
    {
        global $CFG;
        
        // Если логи отключены, то возвращаемся
        if (!$this->get_cfg('log'))
        {
            return;
        }
        
        // А тут мы сначала запускаем следующее
        // Таким образом инициализировать логи специально посути не нужно
        $this->init_logs();
        
        $logfilepath = $this->logs->logfilepath;
        $errorlogfilepath = $this->logs->errorlogfilepath;
        
        // На данный момент это не нужно
        //if ( $CFG->debug >= DEBUG_DEVELOPER OR $this->get_cfg('debug') )
        //{
        
            $timestamp = '['.date('d.m.Y H:i:s').']: ';
            
            $message = $timestamp . $message . "\n";
            
            error_log($message, 3, $logfilepath);
            
            if ($error)
            {
                error_log($message, 3, $errorlogfilepath);
            }
            
        // }
    }
    
    /**
     * Метод log, только вместо сообщения подается строка как в get_string,
     * т.е. ищет сообщение в файлах локализации
     * 
     * @param string $message Сообщение об ошибке
     * @param mixed $a Параметры для строки из файла локализации
     * @param bool[optional] $error Если это сообщение об ошибке
     * @author Evgeniy Yaroslavtsev
     */
    public function log_get_str($messagekey, $a = null, $error = false)
    {
        $message = $this->dof->get_string($messagekey, $this->code(), $a, $this->type());
        $this->log($message, $error);
    }
    
    
}
?>