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
 * Журнал
 */
class dof_im_journal implements dof_plugin_im
{
    /**
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
    * 
    * @return boolean
    * Может надо возвращать массив с названиями таблиц и результатами их создания?
    * чтобы потом можно было распечатать сообщения о результатах обновления
    * @access public
    */
    public function install()
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /**
     * Метод, реализующий обновление плагина в системе
     * Создает или модифицирует существующие таблицы в БД
     * 
     * @param string $oldversion - версия установленного в системе плагина
     * @return boolean
     * Может надо возвращать массив с названиями таблиц и результатами их создания/изменения?
     * чтобы потом можно было распечатать сообщения о результатах обновления
     * @access public
     */
    public function upgrade($oldversion)
    {
        return $this->dof->storage('acl')->save_roles($this->type(),$this->code(),$this->acldefault());
    }
    /**
     * Возвращает версию установленного плагина
     * 
     * @return string
     * @access public
     */
    public function version()
    {
        return 2016011400;
    }
    /**
     * Возвращает версии интерфейса Деканата,
     * с которыми этот плагин может работать
     * 
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
     * 
     * @return string
     * @access public
     */
    public function compat()
    {
        return 'angelfish';
    }

    /**
     * Возвращает тип плагина
     * 
     * @return string
     * @access public
     */
    public function type()
    {
        return 'im';
    }
    /**
     * Возвращает короткое имя плагина
     * Оно должно быть уникально среди плагинов этого типа
     * 
     * @return string
     * @access public
     */
    public function code()
    {
        return 'journal';
    }
    /**
     * Возвращает список плагинов,
     * без которых этот плагин работать не может
     * 
     * @return array
     * @access public
     */
    public function need_plugins()
    {
        return [
            'im' => [
                'cstreams'      => 2016011400,
                'programmitems' => 2015122900,
            ],
            'modlib' => [
                'nvg'           => 2008060300,
                'ama'           => 2009042900,
                'templater'     => 2009031600,
                'widgets'       => 2009050800
            ],
            'storage'=> [
                'persons'       => 2009060400,
                'plans'         => 2009060900,
                'cpgrades'      => 2009060900,
                'schpresences'  => 2009060800,
                'schevents'     => 2009060800,
                'cstreams'      => 2009060800,
                'cpassed'       => 2016011400,
                'orders'        => 2009052500,
                'departments'   => 2009040800,
                'programms'     => 2009040800,
                'programmitems' => 2009060800,
                'acl'           => 2011040504
            ]
        ];
    }
    /** 
     * Определить, возможна ли установка плагина в текущий момент
     * Эта функция одинакова абсолютно для всех плагинов и не содержит в себе каких-либо зависимостей
     * 
     * @TODO УДАЛИТЬ эту функцию при рефакторинге. Вместо нее использовать наследование
     * от класса dof_modlib_base_plugin 
     * @see dof_modlib_base_plugin::is_setup_possible()
     * @param int $oldversion [optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин устанавливать нельзя
     */
    public function is_setup_possible($oldversion=0)
    {
        return dof_is_plugin_setup_possible($this, $oldversion);
    }
    /** 
     * Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку или обновление невозможно
     * 
     * @param int $oldversion[optional] - старая версия плагина в базе (если плагин обновляется)
     *                                    или 0 если плагин устанавливается
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDD00));
     */
    public function is_setup_possible_list($oldversion=0)
    {
        return array('storage'=>array('acl'=>2011040504));
    }
    /**
     * Список обрабатываемых плагином событий
     * 
     * @return array - array(array('plugintype'=>..,'plugincode'=>..,'eventcode'=>..),...)
     * @access public
     */
    public function list_catch_events()
    {
        return array(
                array('plugintype' => 'im',
                        'plugincode' => 'journal',
                        'eventcode'  => 'info'),
             
                array('plugintype' => 'im',
                        'plugincode' => 'my',
                        'eventcode'  => 'info'),
                
                array('plugintype' => 'im',
                        'plugincode' => 'persons',
                        'eventcode'  => 'persondata'));
    }
    /**
     * Требуется ли запуск cron в плагине
     * 
     * @return bool
     * @access public
     */
    public function is_cron()
    {
        // Период запуска 30 минут
        return 1800;
    }

    /**
     * Проверяет полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function is_access($do, $objid = NULL, $userid = NULL, $depid = NULL)
    {
        if ( $this->dof->is_access('datamanage') OR $this->dof->is_access('manage') 
             OR $this->dof->is_access('admin') )
        {//если глобальное право есть - пропускаем';
            return true;
        }
        // получаем id пользователя в persons
        $personid = $this->dof->storage('persons')->get_by_moodleid_id($userid);
        // получаем все нужные параметры для функции проверки прав
        $acldata = $this->get_access_parametrs($do, $objid, $personid, $depid); 
        
        // Дополнительные действия по проверке прав
        switch ( $do )
        {
            // Просмотр "своего" журнала          
            case 'view_journal/own':
                // Персона ведет занятия в предмето-классе
                $hasevents = $this->dof->storage('schevents')->is_exists(['teacherid' => $personid, 'cstreamid' => $objid]);
                // Получение учителя предмето-класса
                $teacherpersonid = $this->dof->storage('cstreams')->get_field($objid, 'teacherid');
                
                if ( ! $hasevents && $personid != $teacherpersonid ) 
                {// Персона - не учитель потока и не ведет ни один урок потока(по замене)      
                    return false;
                }
            // Право на просмотр журнала предмето-класса
            case 'view_journal' :
                // Просмотр относительно подразделения предмето-класса
                $acldata->departmentid = $this->dof->storage('cstreams')->get_field($objid, 'departmentid');
                break;
            // право на отметить проведение своего урока    
            case 'can_complete_lesson/own':        
                if ( ! $this->dof->storage('schevents')->is_exists(['teacherid' => $personid, 'id' => $objid]) )
                {// Персона не ведет данный урок
                    return false;
                }
            case 'can_complete_lesson': 
                if ( $this->dof->storage('schevents')->get_field($objid, 'status') != 'plan' )
                {// Завершать можно только заплпнированные уроки
                    return false;
                }
                $isfixed = $this->dof->storage('schevents')->is_fixed($objid);
                if ( is_null($isfixed) || $isfixed )
                {// Если при определении произошла ошибка, или урок находится в зафиксированном дне
                    return false;
                }
                break; 
            // Право выставить оценку в своем журнале
            case 'give_grade/in_own_journal':
                if ( $schevents = $this->dof->storage('schevents')->get_records(['planid' => $objid, 'status' => ['plan','completed']]) )
                {// Для темы найдено событие
                    if ( $personid != current($schevents)->teacherid )
                    {// Персоня не ведет урок
                        return false;
                    }    
                }else
                {//тема - промежуточная оценка
                    $statusplan = $this->dof->storage('plans')->get_field($objid,'status');
                    if ( ($statusplan != 'active' AND $statusplan != 'checked' AND $statusplan != 'completed') ) 
                    {// статус темы не позволяет редактировать оценки';
                        return false;
                    }
                }
                break;    
            // право на отметку посещаемости своего урока
            case 'give_attendance/own_event':
                if ( $personid != $this->dof->storage('schevents')->get_field($objid,'teacherid') )
                {// только учитель урока
                    return false;
                }               
            break;
            // право указать тему для своего события
            case 'give_theme_event/own_event':
                if ( ! $event = $this->dof->storage('schevents')->get($objid) )
                {// нет урока - нечего и менять
                    return false;
                }
                if ( ($event->status != "plan" AND $event->status != "postponed") 
                     OR ($this->dof->storage('cstreams')->get_field($event->cstreamid,'status') != 'active'
                     AND $this->dof->storage('cstreams')->get_field($event->cstreamid,'status') != 'suspend')
                     OR $personid != $event->teacherid )
                {// только для учителя урока, если статус не "запланирован" или "отложено" и предмето-класс активен
                    return false;
                }
            break;
            case 'replace_schevent':  
                if ( ! $event = $this->dof->storage('schevents')->get($objid) )
                {// нет урока - нечего и менять
                    return false;
                }
                if ( $event->status != 'plan' )
                {// проведен - запрет на редактирование
                    return false;
                }
                if ( $this->dof->storage('schevents')->is_exists(array('joinid'=>$objid,'status'=>'plan')) )
                {// на уроке есть привязка - нельзя менять этот урок
                    return false;
                }
                
            break;  
            // право заменять урок(учителя, дату, учителя потока)
            case 'replace_schevent:date_dis': 
                if ( ! $event = $this->dof->storage('schevents')->get($objid) )
                {// нет урока - нечего и менять
                    return false;
                }
                if ( $event->form != 'distantly' OR $event->status == 'completed' )
                {// проведен - запрет на редактирование
                    return false;
                } 
            break;
            case 'replace_schevent:date_int':  
                if ( ! $event = $this->dof->storage('schevents')->get($objid) )
                {// нет урока - нечего и менять
                    return false;
                }
                if ( $event->form != 'internal' OR $event->status == 'completed' )
                {// проведен - запрет на редактирование
                    return false;
                }
            break;
            case 'replace_schevent:teacher':  
                if ( ! $event = $this->dof->storage('schevents')->get($objid) )
                {// нет урока - нечего и менять
                    return false;
                }
                if ( $event->status == 'completed' )
                {// проведен - запрет на редактирование
                    return false;
                }
            break;  
            // право заменять свой дистанционный урок
            case 'replace_schevent:date_dis/own':   
                if ( ! $event = $this->dof->storage('schevents')->get($objid) )
                {// нет урока - нечего и менять
                   return false;
                }
                if ( $event->form != 'distantly' OR $event->status == 'completed' OR $personid != $event->teacherid )
                {// проведен - запрет на редактирование
                   return false;
                }
            break; 
            // право видеть свою нагрузку
            case 'view:salfactors/own':   
                if ( $personid != $objid )
                {// персона не та
                   return false;
                }
            case 'view:salfactors': 
            break;
            case 'view:salfactors_history':
                $acldata->departmentid = $this->dof->storage('cstreams')->get_field($objid,'departmentid');
            break;
            // права итоговой ведомости
            case 'complete_cstream_before_enddate':   
            case 'complete_cstream_after_enddate':  
            case 'close_journal_before_closing_cstream':  
            case 'close_journal_before_cstream_enddate': 
            case 'close_journal_after_active_cstream_enddate': 
                $acldata->departmentid = $this->dof->storage('cstreams')->get_field($objid,'departmentid');
            break; 
        }
        return $this->acl_check_access_paramenrs($acldata);
    }
    /**
     * Требует наличия полномочия на совершение действий
     * 
     * @param string $do - идентификатор действия, которое должно быть совершено
     * @param int $objid - идентификатор экземпляра объекта,
     * по отношению к которому это действие должно быть применено
     * @param int $userid - идентификатор пользователя, полномочия которого проверяются
     * @return bool true - можно выполнить указанное действие по отношению к выбранному объекту
     * false - доступ запрещен
     * @access public
     */
    public function require_access($do, $objid = NULL, $userid = NULL)
    {
        // Используем функционал из $DOFFICE
        //return $this->dof->require_access($do, NULL, $userid);
        if ( ! $this->is_access($do, $objid, $userid) )
        {
            $notice = "journal/{$do} (block/dof/im/journal: {$do})";
            if ($objid){$notice.=" id={$objid}";}
            $this->dof->print_error('nopermissions','',$notice);

        }
    }
    /**
     * Обработать событие
     * 
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
        $result = '';
        
        if ( $gentype == 'im' AND $gencode == 'journal' AND $eventcode == 'info' )
        {// распечатываем секции
            $path = $this->dof->plugin_path('im','journal','/cfg/main_events.php');
            $this->dof->modlib('nvg')->print_sections($path);
            return true;
        }
        
        if ( $gentype == 'im' AND $gencode == 'my' AND $eventcode == 'info' )
        {// отобразить секции, в которых информация из журнала
//            require_once('show_events/lib.php');
            $sections = array();
            if ( $this->get_section('my_events') )
            {// если в секции "мои занятия" есть данные - выведем секцию
                $sections[] = array('im'=>'journal','name'=>'my_events','id'=>1, 'title'=>$this->dof->get_string('view_today_events_teacher','journal'));
            }
            if ( $this->get_section('my_load') )
            {// если в секции "моя нагрузка" есть данные - выведем секцию
                $sections[] = array('im'=>'journal','name'=>'my_load','id'=>1, 'title'=>$this->dof->get_string('view_teacher_load','journal'));
            }
            if ( $this->get_section('my_salfactors') )
            {// если в секции "Фактическая персональная нагрузка за месяц" есть данные - выведем секцию
                $sections[] = array('im'=>'journal','name'=>'my_salfactors','id'=>1, 'title'=>$this->dof->get_string('view_teacher_salfactors','journal'));
            }
            return $sections;
        }
        if ( $gentype == 'im' AND $gencode == 'persons' AND $eventcode == 'persondata' )
        {// отобразить ссылку на нагрузку за месяц
            $depid = optional_param('departmentid', 0, PARAM_INT);
            if ( $this->dof->storage('schevents')->is_access('view:salfactors',null,null,$depid) )
            {// проверка прав
                if ( $this->dof->storage('appointments')->get_appointment_by_persons($intvar) )
                {// id учителя - вернем ссылку на нагрузку за месяц
                    return $this->show_my_salfactors($intvar, true);
                }
            }
            return '';
        }
        return true;
    }
    /**
     * Запустить обработку периодических процессов
     * 
     * @param int $loan - нагрузка (1 - только срочные, 2 - нормальный режим, 3 - ресурсоемкие операции)
     * @param int $messages - количество отображаемых сообщений (0 - не выводить,1 - статистика,
     *  2 - индикатор, 3 - детальная диагностика)
     * @return bool - true в случае выполнения без ошибок
     * @access public
     */
    public function cron($loan,$messages)
    {
        $result = true;
        if ( $loan == 3 )
        {// генерацию отчетов запускаем только в режиме
            mtrace("Executed orders started");
            $result = $result && $this->dof->storage('orders')->generate_orders($this->type(), $this->code());
            mtrace("Generated reports started");
            $result = $result && $this->dof->storage('reports')->generate_reports($this->type(), $this->code());
        }
        return $result;
    }
    /**
     * Обработать задание, отложенное ранее в связи с его длительностью
     * 
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
     * 
     * @param dof_control $dof - объект $DOF
     * @access public
     */
    public function __construct($dof)
    {
        $this->dof = $dof;

    }
    // **********************************************
    // Методы, предусмотренные интерфейсом im
    // **********************************************
    /**
    * Возвращает содержимое блока
     * 
    * @param string $name - название набора текстов для отображания
    * @param int $id - id текста в наборе
    * @return string - html-код названия блока
    */
    function get_block($name, $id = 1)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        switch ($name)
        {//выбираем нужнуое содержание по названию
            case 'main':
                return '<a href="'.$this->dof->url_im('journal','',$addvars).'">'
                .$this->dof->get_string('title', 'journal').'</a>';
            case 'test':
                return $this->dof->get_string('thisis_test_block', 'journal', $id);
            default:
                {//соответствия не нашлось выведем и имя и id
                    $a = new stdClass();
                    $a->name = $name;
                    $a->id = $id;
                    return $this->dof->get_string('thisis_block_number', 'journal', $a);
                }
        }
    }
    /** 
     * Возвращает содержимое секции
     * 
     * @param string $name - название набора текстов для отображания
     * @param int $id - id текста в наборе
     * @return string  - html-код названия секции
     */
    function get_section($name, $id = 1)
    {
        switch ($name)
        {//выбираем содержание
            case 'navigation': return $this->get_section_navigation($id);
            case 'my_events': return $this->show_my_events(); 
            case 'my_load': return $this->show_my_load(); 
            case 'unmarked_events': return $this->show_unmarked_events(); 
            case 'my_salfactors': return $this->show_my_salfactors(); 
            default:
                {//соответствия не нашлось выведем и имя и id
                    $a = new stdClass();
                    $a->name = $name;
                    $a->id = $id;
                    return $this->dof->get_string('thisis_section_number', 'journal', $a);
                }
        }
    }
    /** 
     * Возвращает текст для отображения в блоке dof
     * 
     * @return string  - html-код для отображения
     */
    public function get_blocknotes($format='other')
    {
        return "<a href='{$this->dof->url_im('journal','/')}'>"
        .$this->dof->get_string('title','journal')."</a>";
    }
    
    /**
     * Получить настройки для плагина
     * 
     * @param unknown $code
     * @return object[]
     */
    public function config_default($code = NULL)
    {
        $config = [];
        
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'enabled';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // Показывать отписанных учеников в журнале группы
        $obj = new stdClass();
        $obj->type = 'checkbox';
        $obj->code = 'showjunkstudents';
        $obj->value = '1';
        $config[$obj->code] = $obj;
        // День окончания зарплатноо периода
        $obj = new stdClass();
        $obj->type = 'text';
        $obj->code = 'enddate';
        $obj->value = '25';
        $config[$obj->code] = $obj;

        return $config;
    }

    
    // ***********************************************************
    //       Методы для работы с полномочиями и конфигурацией
    // ***********************************************************  
    
    /** 
     * Задаем права доступа для объектов этого хранилища
     * 
     * @return array
     */
    public function acldefault()
    {
        $a = array();
        // право видеть журнал
        $a['view_journal']                  = array('roles'=>array('manager','methodist'));
        // право видеть свой журнал
        $a['view_journal/own']              = array('roles'=>array('teacher'));
        // видеть список уроков
        $a['view_schevents']                = array('roles'=>array('manager','methodist','teacher'));
        // видеть информацию о персоне
        $a['view_person_info']              = array('roles'=>array('manager','methodist','teacher'));
        // завершать урок
        $a['can_complete_lesson']           = array('roles'=>array('manager'));
        // завершать свой урок
        $a['can_complete_lesson/own']       = array('roles'=>array('teacher'));
        // проверять как учителя ведут журнал (обычно право используется завучами)
        $a['control_journal']               = array('roles'=>array('manager'));
        // ставить оценку
        $a['give_grade']                    = array('roles'=>array('manager'));
        // ставить оценку в своем журнале
        $a['give_grade/in_own_journal']     = array('roles'=>array('teacher'));
        // отмечать посещаемость
        $a['give_attendance']               = array('roles'=>array('manager'));
        // отмечать посещаемость своего урока
        $a['give_attendance/own_event']     = array('roles'=>array('teacher'));
        // задавать тему для урока
        $a['give_theme_event']              = array('roles'=>array('manager'));
        // задавать тему для своего урока
        $a['give_theme_event/own_event']    = array('roles'=>array('teacher'));
        // заменять урок
        $a['replace_schevent']              = array('roles'=>array('manager'));
        // заменять дистанционный урок
        $a['replace_schevent:date_dis']     = array('roles'=>array('manager'));
        // заменять свой дистанционный урок
        $a['replace_schevent:date_dis/own'] = array('roles'=>array('teacher'));
        // заменять очный урок
        $a['replace_schevent:date_int']     = array('roles'=>array('manager'));
        // заменять урок, меняя при этом учителя
        $a['replace_schevent:teacher']      = array('roles'=>array('manager'));
        // снять галочку н/о
        $a['remove_not_studied']            = array('roles'=>array('manager'));
        // скачать список уроков
        $a['export_events']                 = array('roles'=>array('manager'));
        // завершать cstream до истечения срока cstream
        $a['complete_cstream_before_enddate'] = array('roles'=>array('manager'));
        // завершать cstream после истечения срока cstream (пересдача)
        $a['complete_cstream_after_enddate']  = array('roles'=>array('manager','teacher'));
        // Закрывать итоговую ведомость до завершения cstream 
        // (под завершением имеется в виду cstream в конечном статусе)
        $a['close_journal_before_closing_cstream'] = array('roles'=>array('manager'));
        // Закрывать итоговую ведомость до истечения даты cstream
        $a['close_journal_before_cstream_enddate'] = array('roles'=>array('manager'));
        // Закрывать итоговую ведомость после истечения даты cstream, но до завершения cstream
        $a['close_journal_after_active_cstream_enddate'] = array('roles'=>array('manager','teacher'));
        // просмотр фактической нагрузки
        $a['view:salfactors'] = array('roles' =>array('manager'));
        // просмотр персональной фактической нагрузки
        $a['view:salfactors/own'] = array('roles' =>array('teacher','methodist'));
        // просмотр персональной фактической нагрузки дальше чем на месяц назад
        $a['view:salfactors_history'] = array('roles' =>array('manager','methodist'));
        // Просмотр финансовой информации
        $a['view:financial'] = array('roles' => array('manager', 'methodist'));
        
        return $a;
    }

    /** 
     * Получить список параметров для фунции has_hight()
     * 
     * @return object - список параметров для фунции has_hight()
     * @param string $action - совершаемое действие
     * @param int $objectid - id объекта над которым совершается действие
     * @param int $userid
     */
    protected function get_access_parametrs($action, $objectid, $userid)
    {
        $result               = new stdClass();
        $result->plugintype   = $this->type();
        $result->plugincode   = $this->code();
        $result->code         = $action;
        $result->userid       = $userid;
        $result->departmentid = optional_param('departmentid', 0, PARAM_INT);    
        $result->objectid     = $objectid;
        if ( ! $objectid )
        {// если objectid не указан - установим туда 0 чтобы не было проблем с sql-запросами
            $result->objectid = 0;
        }
        
        return $result;
    }    

    /** 
     * Проверить права через плагин acl.
     * Функция вынесена сюда, чтобы постоянно не писать длинный вызов и не перечислять все аргументы
     * 
     * @param object $acldata - объект с данными для функции storage/acl->has_right() 
     * @return bool
     */
    protected function acl_check_access_paramenrs($acldata)
    {
        return $this->dof->storage('acl')->
                    has_right($acldata->plugintype, $acldata->plugincode, $acldata->code, 
                              $acldata->userid, $acldata->departmentid, $acldata->objectid);
    }
    
    // **********************************************
    //              Собственные методы
    // **********************************************

    /** 
     * Получить URL к собственным файлам плагина
     * 
     * @param string $adds[optional] - фрагмент пути внутри папки плагина
     * начинается с /. Например '/index.php'
     * @param array $vars[optional] - параметры, передаваемые вместе с url
     * @return string - путь к папке с плагином 
     * @access public
     */
    public function url($adds='', $vars=array())
    {
        return $this->dof->url_im($this->code(), $adds, $vars);
    }
    /**
     * Возвращает контейнер с краткой информацией о группо-потоке
     * 
     * @param int $csid - id потока (cstream)
     * @return string
     */
    public function get_cstream_info($csid, $options = array())
    {
        if ( !is_array($options) )
        {
            $options = array();
        }
        global $CFG, $DOF;
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        if ( ! $cstream = $this->dof->storage('cstreams')->get($csid) )
        {//не получили поток
            $progname = '';
            $coursename = '';
            $teacherfio = '';
        }else
        {
            //получаем имя преподавателя
            $teacherfio = $this->dof->storage('persons')->get_fullname($cstream->teacherid).
                       ' <a href="'.$this->dof->url_im('journal', '/show_events/show_events.php?personid='.$cstream->teacherid.
                       '&date_to='.time().'&date_from='.time(),$addvars).'">
                       <img src="'.$this->dof->url_im('journal', '/icons/events_student.png').'"
                       alt=  "'.$this->dof->get_string('view_events_teacher', 'journal').'" 
                       title="'.$this->dof->get_string('view_events_teacher', 'journal').'" /></a>';
                        $link = '';
            if ( $this->dof->storage('schtemplates')->is_access('view') )
            {// можно просматривать шаблон - добавим ссылку на просмотр шаблона на неделю
                $teacherfio .= '<a href="'.$this->dof->url_im('schedule', '/view_week.php?teacherid='.$cstream->teacherid.'&ageid='.$cstream->ageid,$addvars).
                        '"><img src="'.$this->dof->url_im('journal', '/icons/show_schedule_week.png').'"
                         alt=  "'.$this->dof->get_string('view_week_template_on_teacher', 'journal').'" 
                         title="'.$this->dof->get_string('view_week_template_on_teacher', 'journal').'" /></a>';
            }
            //получаем название предмета
            if ( ! $progitem = $DOF->storage('programmitems')->get($cstream->programmitemid) )
            {//не получили запись
                $coursename = '';
                $progname = '';
                $agenum = '';
            }else
            {//получаем имя курса и программы
                $coursename = $progitem->name.' ['.$progitem->code.']';
                if ( $this->dof->storage('programmitems')->is_access('view',$progitem->id) )
                {// ссылка на просмотр предмета
                    $coursename = '<a href='.$this->dof->url_im('programmitems','/view.php?pitemid='.$progitem->id,$addvars).'>'.
                                $coursename.'</a>';
                }
                //получаем название программы
                $progname = $DOF->storage('programms')->get_field($progitem->programmid, 'name').' ['.
                $DOF->storage('programms')->get_field($progitem->programmid, 'code').' ]';
                $agenum = $progitem->agenum;

            }
        }
        $begindate        = dof_userdate($cstream->begindate, '%d.%m.%Y');
        $enddate          = dof_userdate($cstream->enddate, '%d.%m.%Y');
        $csdate           = $begindate . ' - ' . $enddate;
        $statusname = $this->dof->workflow('cstreams')->get_name($cstream->status);
        
        // ссылка на предмето-класс
        $path = $DOF->url_im('cstreams','/view.php?cstreamid='.$cstream->id, $addvars);
        if ( $this->dof->storage('cstreams')->is_access('view',$cstream->id) )
        {// ссылка на просмотр предмето-класса
            $cstream->name = "<a href =$path>".$cstream->name."</a>";
        }
        
        // ссылка на курс в moodle
        $cname = '';
        if ( isset($progitem->mdlcourse) AND $this->dof->modlib('ama')->course(false)->is_course($progitem->mdlcourse) )
        {
            $course = $this->dof->modlib('ama')->course($progitem->mdlcourse)->get();
            $cname = "<a href = ".$CFG->wwwroot."/course/view.php?id=".$progitem->mdlcourse." >".$course->fullname."</a>";
        }
        
        $rez = new stdClass();
        $rez->tablealign = 'left';
        $rez->width = '100%';
        // Поля таблицы и переменные
        $opts = array(
            'programm' => $progname,
            'agenum' => $agenum,
            'course' => $coursename,
            'teacher' => $teacherfio,
            'name' => $cstream->name,
            'course_moodle' => $cname,
            'date' => $csdate,
            'status' => $statusname,
        );
        // Заполняем таблицу в зависимости от $options
        $rez->data = array();
        if ( !empty($options) )
        {
            foreach ( $options as $opt )
            {
                 if ( array_key_exists($opt, $opts) )
                 {
                    $rez->data[] = array($this->dof->get_string($opt,'journal'), $opts[$opt]);
                 }
            }
        } else
        {
            foreach ( $opts as $optname => $opt )
            {
                $rez->data[] = array($this->dof->get_string($optname,'journal'), $opt);
            }
        }
        
        return $this->dof->modlib('widgets')->print_table($rez, true);
    }

    /** 
     * Отображение секции навигации в зависимости от страницы
     * 
     * @param $code - код секции
     * @return string - html-код страницы
     */
    public function get_section_navigation($code)
    {
        $rez = '';
        $rez .= '<ul>';
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $viewform = optional_param('viewform', 0, PARAM_INT);
        switch ($code)
        {
            case 1:
                if ( $this->is_access('view_schevents') )
                {
                    $rez .= '<li><a href="'.$this->dof->url_im('journal','/show_events/show_events.php',$addvars).'">'.
                    $this->dof->get_string('show_events','journal').'</a></li>';
                    $rez .= '<li><a href="'.$this->dof->url_im('journal', '/personsbc_gradeslist/index.php',$addvars).'">'.
                    $this->dof->get_string('pbcgl_programmbc_header','journal').'</a></li>';
                    $personid = $this->dof->storage('persons')->get_by_moodleid_id();
                    
                    if ( $this->is_access('view:salfactors') OR 
                         $this->is_access('view:salfactors/own',$personid))
                    {// ссылка на отчет по фактической нагрузке
                        $date = dof_userdate(time(), '%Y_%m');
                        $rez .= '<li><a href="'.$this->dof->url_im('journal','/load_personal/loadpersonal.php',
                        $addvars+array('personid'=>$personid,'date'=>$date)).'">'.
                        $this->dof->get_string('view_teacher_salfactors','journal').'</a></li>';
                    }
                    if ( $this->dof->storage('orders')->is_access('') )
                    {// ссылка приказ о закрытии периода
                        $rez .= '<li><a href="'.$this->dof->url_im('journal','/orders/fix_day/list.php',
                        $addvars).'">'.
                        $this->dof->get_string('order_fix_day','journal').'</a></li>';
                    }
                    if ( $this->dof->storage('reports')->is_access('view_report_im_journal_loadteachers') )
                    {// ссылка на отчет по фактической нагрузке
                        $rez .= '<li><a href="'.$this->dof->url_im('reports','/list.php',
                        $addvars+array('plugintype'=>'im','plugincode'=>'journal','code'=>'loadteachers')).'">'.
                        $this->dof->get_string('report_actual_load','journal').'</a></li>';
                    }
                    if ( $this->dof->storage('reports')->is_access('view_report_im_journal_replacedevents') )
                    {// ссылка на отчет по заменам уроков
                        $rez .= '<li><a href="'.$this->dof->url_im('reports','/list.php',
                        $addvars+array('plugintype'=>'im','plugincode'=>'journal','code'=>'replacedevents')).'">'.
                        $this->dof->get_string('report_replacedevents','journal').'</a></li>';
                    }
                    //есть ли право добавлять события
                    if ( $this->dof->storage('schevents')->is_access('create') )
                    {   //ссылка на добавление события для нескольких учебных процессов.
                        $rez .= '<li><a href="'.$this->dof->url_im('journal','/mass_events/index.php',$addvars).'">'.
                        $this->dof->get_string('add_event_for_some_cstreams','journal').'</a></li>';
                    }
                }
                break;
            case 2:
                $rez .= '<li><a href="'.$this->dof->url_im('journal','/show_events/index.php',$addvars).'">'.
                $this->dof->modlib('ig')->igs('back').'</a></li>';
                if ( $viewform )
                {
                    $rez .= '<li><a href="'.$this->dof->url_im('journal','/show_events/show_events.php',$addvars).'">'.
                    $this->dof->get_string('search_events_back','journal').'</a></li>';
                }else 
                {
                    $rez .= '<li><a href="'.$this->dof->url_im('journal','/show_events/show_events.php?viewform=1',$addvars).'">'.
                    $this->dof->get_string('search_events','journal').'</a></li>';                    
                }    
                break;
        }
        $rez .= '</ul>';
        return $rez;
    }
    
    public function get_show_events()
    {
        if ( empty($this->show_events) )
        {
            global $DOF, $addvars;
            require_once('show_events/lib.php');
            $this->show_events = new dof_im_journal_show_events($this->dof);
        }
        return $this->show_events;
    }

    /** 
     * Отображение секции "Мои уроки за сегодня"
     * 
     * @return string - html-код страницы
     */
    public function show_my_events()
    {
        $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        $rez = '';
        if ( $this->dof->storage('eagreements')->is_exists(array('personid'=>$personid)) )
        {// считаем, что персона учитель
            //подключаем методы получения списка журналов
            $d = $this->get_show_events();
            //инициализируем начальную структуру
            $d->set_data(null, $personid);
            //получаем список журналов
            $rez = '<br>'.$d->get_table_events();
        }
        if ( $this->dof->storage('contracts')->is_exists(array('studentid'=>$personid)) )
        {// считаем, что персона студент
            //подключаем методы получения списка журналов
            $d = $this->get_show_events();
            //инициализируем начальную структуру
            $d->set_data(null, null, $personid);
            //получаем список журналов
            $rez = '<br>'.$d->get_table_events();
        }
        
        return $rez;
    }
    /** 
     * Отображение секции "Моя нагрузка"
     * 
     * @return string - html-код страницы
     */
    public function show_my_load()
    {
        $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        $rez = '';
        //подключаем методы получения списка журналов
        $d = $this->get_show_events();
        //инициализируем начальную структуру
        $d->set_data(null, $personid);
        //получаем список журналов
        return '<br>'.$d->get_table_teaching_load();
    }
    
    /** 
     * Отображение секции "Мои не отмеченные занятия"
     * 
     * @return string - html-код страницы
     */
    public function show_unmarked_events()
    {
        $personid = $this->dof->storage('persons')->get_by_moodleid_id();
        $rez = '';
        //подключаем методы получения списка журналов
        $d = $this->get_show_events();
        //инициализируем начальную структуру
        $d->set_data(null, $personid);
        //получаем список журналов
        return '<br>'.$d->get_table_unmarked_events();
    }
    
    /** 
     * Отображение секции "Фактическая персональная нагрузка за месяц"
     * 
     * @param int $personid
     * @param bool $linktitle
     * @return string - html-код секции
     */
    public function show_my_salfactors($personid=0, $linktitle=false)
    {
        // ссылка на отчет
        if ( ! $personid )
        {// пользователь не указан - берем текущего
            $personid = $this->dof->storage('persons')->get_by_moodleid_id();
            if ( ! $this->dof->storage('appointments')->get_appointment_by_persons($personid) )
            {// не учитель - выходим
                return '';
            }
        }
    
        // дата отчета
        $date = dof_userdate(time(), '%Y_%m');
        $params = array('personid' => $personid,
                'date' => $date,
                'departmentid' => optional_param('departmentid', 0, PARAM_INT));
    
        $title = $this->dof->get_string('view_teacher_salfactors_go_link', 'journal');
    
        if ( $linktitle )
        {// заголовок и будет ссылкой
            $title = $this->dof->get_string('view_teacher_salfactors', 'journal');
        }
    
        return "<div align='center'><br><a href='".$this->dof->url_im('journal',
                '/load_personal/loadpersonal.php', $params)."'>".$title."</a></div>";
    }
    

    /**
     * Возвращает объект приказа
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function order($code, $id = NULL)
    {
        global $DOF;
        require_once($this->dof->plugin_path('im','journal','/orders/set_grade/init.php'));
        require_once($this->dof->plugin_path('im','journal','/orders/delete_grade/init.php'));
        require_once($this->dof->plugin_path('im','journal','/orders/presence/init.php'));
        require_once($this->dof->plugin_path('im','journal','/orders/set_itog_grade/init.php'));
        require_once($this->dof->plugin_path('im','journal','/orders/fix_day/init.php'));
        switch ($code)
        {
            case 'presence':
                $order = new dof_im_journal_order_presence($this->dof);
                if (!is_null($id))
                {
                    if (!$order->load($id))
                    {
                        // Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
                break;
            case 'set_grade':
                $order = new dof_im_journal_order_set_grade($this->dof);
                if (!is_null($id))
                {
                    if (!$order->load($id))
                    {
                        // Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
                break;
            case 'delete_grade':
                $order = new dof_im_journal_order_delete_grade($this->dof);
                if (!is_null($id))
                {
                    if (!$order->load($id))
                    {
                        // Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
                break;
            case 'set_itog_grade':
                $order = new dof_im_journal_order_set_itog_grade($this->dof);
                if (!is_null($id))
                {
                    if (!$order->load($id))
                    {
                        // Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
                break;
            case 'fix_day':
                $order = new dof_im_journal_order_fix_day($this->dof);
                if (!is_null($id))
                {
                    if (!$order->load($id))
                    {
                        // Не найден
                        return false;
                    }
                }
                // Возвращаем объект
                return $order;
                break;  
            default:
                // Ошибка
                return false;
                break;
        }
    }

    /**
     * Возвращает объект отчета
     *
     * @param string $code
     * @param integer  $id
     * @return dof_storage_orders_baseorder
     */
    public function report($code, $id = NULL)
    {
        return $this->dof->storage('reports')->report($this->type(), $this->code(), $code, $id);
    }

    
   /**
    * Возвращает вкладки на просмотр по времени/учителям/ученикам
    * 
    * @param string $id -идентификатор,определяет какая вкладка активна в данный момент
    * @param array $addvars - массив параметров GET/POST 
    * @return смешанную строку 
    */
    public function print_tab($addvars, $id)
    {
        unset($addvars['display']);
        // соберем данные для вкладок
        $tabs = array();
        // операции
        $link = $this->dof->url_im($this->code(),'/show_events/show_events.php',$addvars);
        $text = $this->dof->get_string('display_mode:time', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('time', $link, $text, NULL, true); 
        // оборудование
        $link = $this->dof->url_im($this->code(),'/show_events/show_events.php?display=students',$addvars);
        $text = $this->dof->get_string('display_mode:students', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('students', $link, $text, NULL, true);
        // комплекты оборудования
        $link = $this->dof->url_im($this->code(),'/show_events/show_events.php?display=teachers',$addvars);
        $text = $this->dof->get_string('display_mode:teachers', $this->code());
        $tabs[] = $this->dof->modlib('widgets')->create_tab('teachers', $link, $text, NULL, true);        
        // готовим для вывода
        return $this->dof->modlib('widgets')->print_tabs($tabs, $id, NULL, NULL, true);
    }    
    
    /** 
     * Метод, который возаращает список для автозаполнения
     * 
     * @param string $querytype - тип завпроса(поу молчанию стандарт)
     * @param string $data - строка
     * @param integer $depid - id подразделения  
     * 
     * @return array or false - запись, если есть или false, если нет
     */
    public function widgets_field_variants_list($querytype, $depid, $data='')
    {
        // в зависимости от типа, проверяем те или иные права
        switch ($querytype)
        {
            case 'person_name' :        
            // есть права - то посылаем запрос
            if ( $this->is_access('view_schevents',NULL,NULL,$depid) )
            {
                return $this->dof->storage('persons')->result_of_autocomplete($querytype, $depid, $data);
            }
        }    
        
       // нет ничего
       return false;
        
    }

    /**
     * Отобразить Историю обучения по дисциплине для подписки на программу
     * 
     * @param int $programmsbcid - Подписка на программу
     * @param int $programmitemid - Целевая дисциплина
     * 
     * @return string HTML-код для отображения на странице
     */
    public function show_cphistory($programmsbcid, $programmitemid)
    {
        global $DOF, $addvars;
        require_once($this->dof->plugin_path('im', 'journal', '/group_journal/lib.php'));
        $errorlink = $this->dof->url_im('programmsbcs','/list.php', $addvars);
        if ( !$programmsbc = $this->dof->storage('programmsbcs')->get($programmsbcid) )
        {
            $this->dof->print_error('error_nopsbc', $errorlink, $programmsbcid, 'im', 'journal');
        }

        if ( !$pitem = $this->dof->storage('programmitems')->get($programmitemid) )
        {
            $this->dof->print_error('error_nopitem', $errorlink, $programmitemid, 'im', 'journal');
        }
        // Проверим, относится ли дисциплина к программе ученика.
        if ( !$programmsbc->programmid == $pitem->programmid )
        {
            // http://moodle.dev/blocks/dof/im/programmitems/list_agenum.php?programmid=1&departmentid=0
            $this->dof->print_error('error_nopiteminprogramm', $errorlink, $programmitemid, 'im', 'journal');
        }
        
        // Подключаем класс для вывода
        $this->dof->modlib('widgets')->html_writer();
        $cphistory = '';
        $conds = new stdClass();
        $fields = 'program,contract,agenum,status';
        // Информация о подписке
        $content = $this->dof->im('programmsbcs')->show_id($programmsbcid, $conds, explode(',',$fields));
        $divpsbcinfo = dof_html_writer::div($content, 'psbcinfo');
        // Заголовок "Информация о подписке"
        $header = $this->dof->modlib('widgets')->print_heading($this->dof->get_string('psbcinfo', 'journal'), '', 2, 'main', true);
        $pitemlink = $this->dof->im('obj')->get_object_url_current('programmitems', $programmitemid, 'view', $addvars, $pitem->name);
        $gradesheader = $this->dof->modlib('widgets')->print_heading($this->dof->get_string('cstreamgrades', 'journal', $pitemlink), '', 2, 'cstreamheading', true);
        // Заголовок, подписка, заголовок "Оценки"
        $cphistory .= $header . $this->dof->modlib('widgets')->print_box($divpsbcinfo, 'generalbox', '', true) . $gradesheader;
        // Получим список всех потоков, доступных пользователю
        $sortcstreams = array();
        $realstatuses = $this->dof->workflow('cstreams')->get_meta_list('real');
        $hasgrades = false;
        if ( $cstreams = $this->dof->storage('cstreams')->get_programmitem_cstream($programmitemid, false) )
        {
            foreach ( $cstreams as $csid => $cstream )
            {
                if  (!array_key_exists($cstream->status, $realstatuses) )
                {// Статус мусорный - пропускаем
                    continue;
                }
                if ( $this->dof->storage('cstreams')->is_access('view', $csid) )
                {
                    $sortcstreams[$csid] = $cstream->begindate;
                }
            }
            // Отсортируем по дате начала процессов
            asort($sortcstreams);
            foreach ( $sortcstreams as $csid => $begindate )
            {
                // Таблица с оценками
                $journal = new dof_im_journal_tablegrades($this->dof, $csid);
                $grades = $journal->get_grades_programmsbcid($programmsbcid, TRUE);
                if ( empty($grades) )
                {
                    continue;
                } else
                {
                    $hasgrades = true;
                }
                $options = 'agenum,teacher,name,date,status';
                // Таблица с информацией о потоке
                $csheader = $this->dof->im('journal')->get_cstream_info($csid, explode(',', $options));
                $divcstreamgrades = dof_html_writer::div($csheader . $grades, 'cstreamgrades');
                $cphistory .= $divcstreamgrades;
            }
        }
        
        if ( !$hasgrades )
        {// Сообщение о том, что истории оценок нет
            $cphistory .= $this->dof->modlib('widgets')->notice_message($this->dof->get_string('cphistoryempty', 'journal'));
        }
        return $cphistory;
        
    }
    
    /***************************************************/
    /************ МЕТОДЫ ПРОВЕРКИ    *******************/
    /************ ПРАВ ДОСТУПА.      *******************/
    /************ МОЖНО ИСПОЛЬЗОВАТЬ *******************/
    /************ ТОЛЬКО В МЕТОДЕ    *******************/
    /************ $this->is_access() *******************/
    /***************************************************/

    /**
     * Вернуть массив с настройками или одну переменную
     * 
     * @param $key - переменная
     * @return mixed
     */
    public function get_cfg($key=null)
    {
        // Возвращает параметры конфигурации
        include ($this->dof->plugin_path($this->type(),$this->code(),'/cfg/cfg.php'));
        if (empty($key))
        {
            return $im_journal;
        }else
        {
            return @$im_journal[$key];
        }
    }

    /** 
     * Проверяет полномочия на перенос уроков
     * 
     * @param int $objid - id переносимого события
     * @param int $personid - id персоны, запрашивающей перенос уроков
     * @return bool true - все в порядке, ограничений нет или false
     */
    public function is_access_replace($objid, $userid = null, $roles = array())
    {
        global $USER;
        if ( is_null($userid) )
        {
            $userid = $USER->id;
        }
        $access = new stdClass();
        $access->selectdate    = false; // право выбрать время урока
        $access->ignorform     = false; // игнорирование формы урока
        $access->ignorolddate  = false; // игнорирование старой даты урока
        $access->ignornewdate  = false; // игнорирование новой даты урока
        // TODO убрать тут все после OR после перехода на новые права поностью
        if ( in_array('manager', $roles) OR $this->dof->is_access('manage', $objid, $userid)  )
        {//особенным всегда можно
            $access->ignorform    = true;
            $access->selectdate   = true;
            $access->ignorolddate = true;
            $access->ignornewdate = true;
        }
        $event = $this->dof->storage('schevents')->get($objid);
        // TODO убрать послу OR как перейдем на новые права
        if ( $event->teacherid == $userid OR 
                $event->teacherid == $this->dof->storage('persons')->get_by_moodleid_id($userid) )
        {// указанная персона учитель
            if ( $event->form == 'distantly' )
            {// ему можно переносить только дистанционные уроки
                $access->selectdate = true;
            }
        }
        // @todo добавить проверки на замену урока
        // проверки не пройдены
        return $access;
    } 
    
    /**
     * Сформировать массив доступных подписок пользователей из подписок на программы
     * 
     * Проверки доступа кореектируются с помощью идентификаторов подразделения и 
     * персоны. Если не указаны - производится автозаполнение текущими значениями
     * Если указан интервал - фильтруются подписки, которые хотя бы частично не затрагивают временной интервал
     * 
     * @param array $programmbcs - Массив подписок на программы
     * @param array $options - Массив параметров для поиска подписок пользователей
     *          ['departmentid'] => integer - Текущее подразделение. Автовыбор, если не указано
     *          ['personid']     => integer - Текущий пользователь, для которого происодит сбор данных.
     *                                        Автовыбор, если не указан.
     *          ['timestart']    => integer - Необязательный параметр. Начальный интервал времени, 
     *                                        который должна затрагивать подписка
     *          ['timeend']      => integer - Необязательный параметр. Конечный интервал времени, 
     *                                        который должна затрагивать подписка
     *                                        
     * @return array|bool - Массив подписок на учебные процессы или false , если возникла ошибка
     */
    public function get_cpasseds_by_programmbcs($programmbcs = [], $options = [])
    {
        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        if ( empty($programmbcs) )
        {// Подписки не указаны
            return [];
        } else 
        {// Указан массив подписок
            $programmbcsclear = [];
            foreach ( $programmbcs as $programmbc )
            {
                if ( is_int($programmbc) || is_string($programmbc) )
                {// Указан идентификатор
                    $programmbcsclear[] = (int)$programmbc;
                }
                if ( is_object($programmbc) && isset($programmbc->id) )
                {// Указан объект подписки
                    $programmbcsclear[] = (int)$programmbc->id;
                }
            }
        }
        if ( ! isset($options['personid']) || (int)$options['personid'] < 1 )
        {// Сброс идентификатора персоны
            $options['personid'] = $this->dof->storage('persons')->get_bu();
        }
        if ( ! isset($options['departmentid']) || (int)$options['departmentid'] < 1 )
        {// Сброс идентификатора подразделения
            $options['departmentid'] = optional_param('departmentid', NULL, PARAM_INT);
        }
        if ( ! isset($options['timestart']) || (int)$options['timestart'] < 0 )
        {// Сброс начального интервала
            $options['timestart'] = NULL;
        }
        if ( ! isset($options['timeend']) || (int)$options['timeend'] < 0 )
        {// Сброс конечного интервала
            $options['timeend'] = NULL;
        }
        
        // Получение подписок на предмето-классы
        $params = [];
        // Массив идентификаторов
        $params['programmbcids'] = $programmbcsclear;
        // Установка фильтрации по частичному вхождению во временной интервал
        $params['time_entry'] = 'partial';
        if ( ! empty($options['timestart']) )
        {// Указано начало интервала
            $params['timestart'] = $options['timestart'];
        }
        if ( ! empty($options['timestart']) )
        {// Указан конец интервала
            $params['timeend'] = $options['timeend'];
        }
        
        // Соритровка
        $params['sort'] = ' programmsbcid ASC ';
        
        // Получение подписок на предмето-классы
        $cpasseds = $this->dof->storage('cpassed')->get_cpasseds_by_options($params);
        
        // Формирование результирующего массива подписок на дисциплины
        $availablecpasseds = [];
        // Проверки доступа для элементов
        foreach ( $cpasseds as $cpassed )
        {
            if ( $this->dof->storage('cpassed')->is_access('use', $cpassed->id, NULL, (int)$options['departmentid']) ||
                 $this->dof->storage('cpassed')->is_access('use/teacher', $cpassed->id, NULL, (int)$options['departmentid']) )
                {// Доступ есть
                    $availablecpasseds[$cpassed->id] = $cpassed;
                }
        }
        
        return $availablecpasseds;
    }
    
    /**
     * Сформировать массив доступных подписок на программы
     * 
     * @param array $options - Массив параметров для поиска подписок пользователей
     *          ['agroupid']     => integer - Идентификатор академической группы,
     *                                        откуда требуется взять подписки на программы
     *          ['personbcid']   => integer - Идентификатор подписки на программу.
     *
     * @return array|bool - Массив подписок на программы или false , если возникла ошибка
     */
    public function get_available_programmbcs($options = [])
    {
        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        if ( ! isset($options['agroupid']) || (int)$options['agroupid'] < 1 )
        {// Сброс идентификатора группы
            $options['agroupid'] = NULL;
        }
        if ( ! isset($options['personbcid']) || (int)$options['personbcid'] < 1 )
        {// Сброс идентификатора подписки
            $options['personbcid'] = NULL;
        }
    
        // Получение подписок на программы
        $params = [];
        if ( ! empty($options['personbcid']) )
        {// Указан идентификатор подписки
            $params['ids'] = $options['personbcid'];
        }
        if ( ! empty($options['agroupid']) )
        {// Указан идентификатор группы
            $params['agroupids'] = $options['agroupid'];
        }
        $bcs = $this->dof->storage('programmsbcs')->get_programmsbcs_by_options($params);
    
        // Фильтрация с проверкой прав
        $availablebcs = [];
        foreach ( $bcs as $bc )
        {
            if ( $this->dof->storage('programmsbcs')->is_access('use', $bc->id) ||
                 $this->dof->storage('programmsbcs')->is_access('use/my', $bc->id) )
            {// Добавление элемента в список
                $availablebcs[$bc->id] = $bc;
            }
        }
        
        return $availablebcs;
    }
    
    /**
     * Построить таблицы ведомостей
     * 
     * @param array $cpasseds - Подписки на предмето - классы персон
     * @param array $options - Массив дополнительных опций отображения
     *              ['addvars'] - Массив GET-параметров
     *              ['departmentid'] => integer - Текущее подразделение. Автовыбор, если не указано
     *              ['personid']     => integer - Текущий пользователь, для которого происодит сбор данных.
     *                                            Автовыбор, если не указан.
     *              ['timestart']    => integer - Ограничение выборки событий для подписки
     *              ['timeend']      => integer - Ограничение выборки событий для подписки
     *              ['export']       => string  - Формирование содержимого для экспорта. Доступные дорматы(CSV)
     *              ['view_type']    => string  - Код типа отображения (00, 01, 10, 11, 20, 21).
     *                                          Первая цифра отвечает за уровень группировки подписок(без группировки,по предмето-классу, по дисциплине )
     *                                          Вторая цифра - за группировку уроков по времени(без группировки, по дням)
     *              
     * @return string - HTML-код отображения
     */
    public function personbc_gradelist_table($cpasseds, $options)
    {
        $content = '';
        $exportdata = ['title' => [], 'programmbcs' => []];
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        if ( empty($cpasseds) || ! is_array($cpasseds) )
        {// Подписки не указаны
            $this->dof->messages->add(
                $this->dof->get_string('pbcgl_notice_cpasseds_not_found', 'journal'),
                'notice'
            );
            return $content;
        }
        
        if ( ! isset($options['personid']) || (int)$options['personid'] < 1 )
        {// Сброс идентификатора персоны
            $options['personid'] = $this->dof->storage('persons')->get_bu();
        }
        if ( ! isset($options['departmentid']) || (int)$options['departmentid'] < 1 )
        {// Сброс идентификатора подразделения
            $options['departmentid'] = optional_param('departmentid', NULL, PARAM_INT);
        }
        if ( ! isset($options['timestart']) || (int)$options['timestart'] < 0 )
        {// Сброс начального интервала
            $options['timestart'] = NULL;
        }
        if ( ! isset($options['timeend']) || (int)$options['timeend'] < 0 )
        {// Сброс конечного интервала
            $options['timeend'] = NULL;
        }
        if ( ! isset($options['addvars']) || ! is_array($options['addvars']) )
        {// Установка GET-параметров
            $options['addvars'] = [
                'departmentid' => $options['departmentid']
            ];
        }
        $view_type = '00';
        if ( isset($options['view_type']{0}) )
        {// Нормализация
            $fcode = (int)$options['view_type']{0};
            if ( $fcode > 2 || $fcode < 0 )
            {
                $fcode = 0;
            }
            $view_type{0} = (string)$fcode;
        }
        if ( isset($options['view_type']{1}) )
        {// Нормализация
            $scode = (int)$options['view_type']{1};
            if ( $scode > 1 || $scode < 0 )
            {
                $scode = 0;
            }
            $view_type{1} = (string)$scode;
        }
        $options['view_type'] = $view_type;
        
        // Результирующие данные
        $programmbcs = [];
        
        // Буферы данных
        $bufercstreams = [];
        $buferprorammitems = [];
        $bufercstreamevents = [];
        $programmsbceventdates = [];
        
        // Формирование данных по подпискам
        foreach ( $cpasseds as $cpassed )
        {
            // Подписка на программу
            if ( ! isset($programmbcs[$cpassed->programmsbcid]) )
            {// Добавление слота новой подписки
                $programmbcs[$cpassed->programmsbcid] = [];
                $programmsbceventdates[$cpassed->programmsbcid] = [];
            }
            
            // БУФЕРИЗАЦИЯ ДАННЫХ
            // Предмето-класс
            if ( ! isset($bufercstreams[$cpassed->cstreamid]) )
            {// Добавление предмето-класса в буфер
                $cstream = $this->dof->storage('cstreams')->get($cpassed->cstreamid);
                if ( empty($cstream) )
                {// Уведомление о том, что cstream не найден в системе
                    $this->dof->messages->add(
                        $this->dof->get_string('pbcgl_notice_cstream_not_found', 'journal', $cpassed->cstreamid),
                        'notice'
                    );
                } else 
                {// Добавление ссылки на предмето-класс
                    $cstream->cslink = $this->dof->im('cstreams')->
                        get_html_link($cpassed->cstreamid, false, $options['addvars']);
                }
                $bufercstreams[$cpassed->cstreamid] = $cstream;
            }
            if ( empty($bufercstreams[$cpassed->cstreamid]) )
            {// Предмето-класс не найден, переход к следующему cpassed
                continue;
            }
            
            // Дисциплина
            if ( ! isset($buferprorammitems[$cpassed->programmitemid]) )
            {// Добавление данных о дисциплине
                $buferprorammitems[$cpassed->programmitemid] = $this->dof->im('programmitems')->
                    get_html_link($cpassed->programmitemid, false, $options['addvars']);
                if ( empty($buferprorammitems[$cpassed->programmitemid]) )
                {// Дисциплина не найдена
                    $buferprorammitems[$cpassed->programmitemid] = 
                        $this->dof->get_string('notfoundpitem', 'programmitems');
                }
            }
            
            // События предмето-класса
            if ( ! isset($bufercstreamevents[$cpassed->cstreamid]) )
            {// Формирование буфера событий для предмето-класса
                $events = $this->dof->storage('schevents')->get_cstream_events(
                    $cpassed->cstreamid, 'completed', $options['timestart'], $options['timeend']);
                if ( empty($events) )
                {// Нормализация
                    $events = [];
                }
                // Добавление событий в буфер
                $bufercstreamevents[$cpassed->cstreamid] = $events;
            }
            
            // ФОРМИРОВАНИЕ МАССИВА ДАННЫХ НА ОСНОВЕ ПОДПИСКИ
            if ( ! isset($programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]) )
            {// Добавление предмето-класса
                $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid] = [];
            }
            if ( ! isset($programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds']) )
            {// Добавление списка подписок
                $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds'] = [];
            }
            // Добавление базовой информации по предмето-классу
            $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['programmitemid'] = $cpassed->programmitemid;
            $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['programmitemlink'] = $buferprorammitems[$cpassed->programmitemid];
            if ( ! isset($programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds'][$cpassed->id]) )
            {// Обьявление массива событий для предмето-класса
                $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds'][$cpassed->id] = ['events' => []];
            }
            if ( ! empty($bufercstreamevents[$cpassed->cstreamid]) )
            {// Обработка событий предмето-класса
                foreach ( $bufercstreamevents[$cpassed->cstreamid] as $event )
                {
                    // Формирование данных по событию для подписки
                    $eventdata = [];
                    $grade = $this->dof->storage('cpgrades')->get_grade_student_cpassed($cpassed->id, $event->planid);
                    
                    if ( isset($grade->grade) )
                    {// Оценка найдена
                        $eventdata['cpgrade'] = (string)$grade->grade;
                    } else 
                    {// Оценки нет
                        $eventdata['cpgrade'] = '';
                    }
                    $eventdata['date'] = (int)$event->date;
                    // Добавление в буфер столбцов данных по дате
                    $programmsbceventdates[$cpassed->programmsbcid][$eventdata['date']] = NULL;
                    $schpresence = $this->dof->storage('schpresences')->
                        get_present_status($cpassed->studentid, $event->id);
                    if ( ! is_bool($schpresence) )
                    {// Данные о присутствии найдены
                        $eventdata['schpresence'] = (int)$schpresence;
                    } else
                    {// Данных о присутствии нет
                        $eventdata['schpresence'] = NULL; 
                    }
                    // Добавление данных о событии
                    if ( ! isset($programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds'][$cpassed->id]['events'][$event->id]) )
                    {// Первые данные по событию
                        $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds'][$cpassed->id]['events'][$event->id] = $eventdata;
                    } else
                    {// Несколько оценок по событию
                        $programmbcs[$cpassed->programmsbcid][$cpassed->cstreamid]['cpasseds'][$cpassed->id]['events'][$event->id]['cpgrade'] .= $eventdata['cpgrade'];
                    }
                }
            }
        }
        
        // Формирование html-кода таблиц на основе собранных данных
        if ( ! empty($programmbcs) )
        {// Есть подписки 
            // Формирование таблицы ведомости по каждой подписке
            foreach ( $programmbcs as $programmbcid => $cstreams )
            {
                // Получение ФИО ученика
                $studentfio = '';
                // ID студента на обучение по подписке на программу
                $studentid = $this->dof->storage('programmsbcs')->get_studentid_by_programmsbc($programmbcid);
                if ( ! empty($studentid) )
                {// Персона найдена
                    $studentfio = $this->dof->storage('persons')->get_fullname($studentid);
                } else
                {// Персона не найденан
                    $studentfio = $this->dof->get_string('pbcgl_error_person_not_found', 'journal', $contractid);
                }
                    
                // Объявление таблицы ведомости по подписке
                $table = new stdClass();
                $table->size = [];
                $table->align = [];
                // Шапка таблицы
                $table->head = [];
                // Данные таблицы
                $table->data = [];
                $table->colclasses = ['pbcgl_left'];
                    
                // Формирование столбцов для таблицы ведомости
                if ( ! empty($programmsbceventdates[$programmbcid]) )
                {// Найдены столбцы в буфере
                    // Сортировка столбцов по дате события
                    ksort($programmsbceventdates[$programmbcid]);
                    $buffercolumns = $programmsbceventdates[$programmbcid];
                    // Добавление в начало столбца с описанием
                    $buffercolumns = ['name' => NULL] + $buffercolumns;
                } else 
                {// События не найдены
                    // Добавление в начало столбца с именем дисциплины
                    $buffercolumns = ['name' => NULL];
                }
                
                // Подготовка таблицы
                $columns = [];
                foreach ( $buffercolumns as $columnname => $data )
                {
                    // Код столбца устанавливается временем проведения события
                    $colcode = (string)$columnname;
                    // Столбец описания
                    if ( is_int($columnname) )
                    {// Столбец события
                        $table->size[$colcode] = '100px';
                        $table->align[$colcode] = 'center';
                        if ( $options['view_type']{1} == '0' )
                        {// Расширенный вид
                            $table->head[$colcode] = dof_userdate($columnname, '%d.%m.%Y %H:%M', $usertimezone, false);
                        } else 
                        {// Краткий вид
                            // Код столбца устанавливается датой
                            $colcode = (string)dof_userdate($columnname, '%d.%m.%Y', $usertimezone, false);
                            $table->head[$colcode] = $colcode;
                        }
                    } else 
                    {// Столбец описания
                        $table->size[$colcode] = '200px';
                        $table->align[$colcode] = 'left';
                        if ( $options['view_type']{0} == 2 )
                        {// Дисциплина
                            $table->head[$colcode] = $this->dof->get_string('pbcgl_table_programmitem', 'journal');
                        } else 
                        {// Предмето-класс
                            $table->head[$colcode] = $this->dof->get_string('pbcgl_table_cstream', 'journal');
                        }
                    }
                    // Формирование столбца с кодом
                    $columns[$colcode] = '';
                }
                
                // Заполнение таблицы
                if ( ! empty($cstreams) )
                {// Есть дисциплины
                    foreach ( $cstreams as $cstreamid => $cstreamdata )
                    {// Обработка предмето-класса
                        if ( ! empty($cstreamdata['cpasseds']) )
                        {// Найдены подписки на предмето-класс
                            foreach( $cstreamdata['cpasseds'] as $cpassedid => $cpassed )
                            {// Обработка каждой подписки
                                // Получение кода добавляемой в таблицу строки
                                switch ($options['view_type']{0})
                                {
                                    // Разбиение по подпискам на предмето-класс
                                    case '0' :
                                        $rowcode = $cpassedid;
                                        break;
                                    // Разбиение по предмето-классам
                                    case '1' :
                                        $rowcode = $cstreamid;
                                        break;
                                    // Разбиение по дисциплинам
                                    case '2' :
                                        $rowcode = $cstreamdata['programmitemid'];
                                        break;
                                }
                                // Проверка наличия строки в таблице
                                if ( ! isset($table->data[$rowcode]) )
                                {// Строка еще не определена в таблице
                                    // Подготовка строки - разбиение на стоблцы
                                    $table->data[$rowcode] = $columns;
                                    
                                    // Добавление ссылки для ячейку с описанием
                                    if ( $options['view_type']{0} == '2' )
                                    {
                                        $table->data[$rowcode]['name'] = $cstreamdata['programmitemlink'];
                                    } else
                                    {
                                        $table->data[$rowcode]['name'] = $bufercstreams[$cstreamid]->cslink;
                                    }
                                }
                                if ( isset($cpassed['events']) && ! empty($cpassed['events']) )
                                {// События по подписке на предмето-класс
                                    foreach ( $cpassed['events'] as $eventid => $eventdata )
                                    {
                                        $columncode = (string)$eventdata['date'];
                                        if ( $options['view_type']{1} == '1' )
                                        {// Краткий вид
                                            $columncode = dof_userdate($columncode, '%d.%m.%Y', $usertimezone, false);
                                        }
                                    
                                        // Формирование данных по событию
                                        $cellcontent = $eventdata['cpgrade'];
                                        if ( $eventdata['schpresence'] === NULL )
                                        {// Без оценки
                                            if ( ! empty($cellcontent) )
                                            {
                                                $cellcontent .= '('.$this->dof->get_string('pbcgl_table_grade_no', 'journal').')';
                                            } else
                                            {
                                                $cellcontent .= $this->dof->get_string('pbcgl_table_grade_no', 'journal');
                                            }
                                        }
                                        if ( $eventdata['schpresence'] === 0 )
                                        {// Не присутствовала на занятии
                                            if ( ! empty($cellcontent) )
                                            {
                                                $cellcontent .= '('.$this->dof->get_string('pbcgl_table_presence_no', 'journal').')';
                                            } else
                                            {
                                                $cellcontent .= $this->dof->get_string('pbcgl_table_presence_no', 'journal');
                                            }
                                        }
                                        // Добавление всплывающей подсказки для краткого вида
                                        if ( $options['view_type']{1} == '1' && ! empty($cellcontent) )
                                        {
                                            // Добавление всплывающей подсказки
                                            $extended = dof_userdate($eventdata['date'], '%H:%M', $usertimezone, false);
                                            $cellcontent = dof_html_writer::span($cellcontent, 'dof_pbcgl_cell', ['data-extended' => $extended] );
                                        }
                                    
                                        if ( ! isset($table->data[$rowcode][$columncode]) )
                                        {// Столбец не найден
                                            $this->dof->messages->add(
                                                $this->dof->get_string('pbcgl_table_column_not_found', 'journal'),
                                                'error'
                                            );
                                        } else
                                        {// Добавление данных к столбцу
                                            if ( ! empty($table->data[$rowcode][$columncode]) && ! empty($cellcontent))
                                            {
                                                $table->data[$rowcode][$columncode] .= ' / ';
                                            }
                                            $table->data[$rowcode][$columncode] .= $cellcontent;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ( ! isset($options['export']) )
                {// Отображение таблицы
                    $content .= dof_html_writer::start_div('dof_pbcgl_table_wrapper');
                    $content .= dof_html_writer::tag('h5', $studentfio, ['class' => 'dof_pbcgl_fio']);
                    $content .= dof_html_writer::start_div('dof_pbcgl_table dof_fixed_table dof_table_allborders');
                    $content .= $this->dof->modlib('widgets')->print_table($table, true);
                    $content .= dof_html_writer::end_div();
                    $content .= dof_html_writer::end_div();
                } else 
                {// Экспорт
                    $tablecontent = $studentfio."\n";
                    
                    $tablecontent .= implode(';', $table->head)."\n";
                    if ( ! empty($table->data) )
                    {
                        foreach ( $table->data as $row )
                        {
                            $tablecontent .= implode(';', $row)."\n";
                        }
                    }
                    $content .= strip_tags($tablecontent)."\n";
                }
            }
        }
        
        if ( ! isset($options['export']) )
        {// Отображение таблицы
            $title = dof_html_writer::start_div('dof_pbcgl_table_wrapper_header');
            $title .= dof_html_writer::tag(
                'h3',
                $this->dof->get_string('pbcgl_table_header_title', 'journal'),
                    ['class' => 'dof_pbcgl_title']
                );
            if ( ! empty($options['timestart']) && ! empty($options['timeend']) )
            {// Указан интервал
                $timeintervalstart = dof_userdate($options['timestart'], '%d.%m.%Y', $usertimezone, false);
                $timeintervalend = dof_userdate($options['timeend'], '%d.%m.%Y', $usertimezone, false);
                $timeinterval = $timeintervalstart.' — '.$timeintervalend;
                $title .= dof_html_writer::tag(
                    'h4',
                    $this->dof->get_string('dof_pbcgl_table_wrapper_header_time', 'journal', $timeinterval),
                        ['class' => 'dof_pbcgl_title_time']
                    );
            } else
            {// Интервал не указан
            if ( ! empty($options['timestart']) )
            {// Указана только начальная дата
                $timeintervalstart = dof_userdate($options['timestart'], '%d.%m.%Y', $usertimezone, false);
                $title .= dof_html_writer::tag(
                    'h4',
                    $this->dof->get_string('dof_pbcgl_table_wrapper_header_time_start', 'journal', $timeintervalstart),
                        ['class' => 'dof_pbcgl_title_time']
                    );
            }
            if ( ! empty($options['timestart']) )
            {// Указана только конечная дата
                $timeintervalend = dof_userdate($options['timeend'], '%d.%m.%Y', $usertimezone, false);
                $title .= dof_html_writer::tag(
                    'h4',
                    $this->dof->get_string('dof_pbcgl_table_wrapper_header_time_end', 'journal', $timeintervalstart),
                        ['class' => 'dof_pbcgl_title_time']
                    );
                }
            }
            $title .= dof_html_writer::end_div();
        } else
        {// Экспорт
            $title = $this->dof->get_string('pbcgl_table_header_title', 'journal')."\n";
            if ( ! empty($options['timestart']) && ! empty($options['timeend']) )
            {// Указан интервал
                $timeintervalstart = dof_userdate($options['timestart'], '%d.%m.%Y', $usertimezone, false);
                $timeintervalend = dof_userdate($options['timeend'], '%d.%m.%Y', $usertimezone, false);
                $timeinterval = $timeintervalstart.' — '.$timeintervalend;
                $title .= $this->dof->get_string('dof_pbcgl_table_wrapper_header_time', 'journal', $timeinterval)."\n";
            } else
            {// Интервал не указан
               if ( ! empty($options['timestart']) )
               {// Указана только начальная дата
                   $timeintervalstart = dof_userdate($options['timestart'], '%d.%m.%Y', $usertimezone, false);
                   $title .= $this->dof->get_string('dof_pbcgl_table_wrapper_header_time_start', 'journal', $timeintervalstart);
               }
               if ( ! empty($options['timestart']) )
               {// Указана только конечная дата
                   $timeintervalend = dof_userdate($options['timeend'], '%d.%m.%Y', $usertimezone, false);
                   $title .= $this->dof->get_string('dof_pbcgl_table_wrapper_header_time_end', 'journal', $timeintervalstart)."\n";
               }
            }
        }
        
        return $title.$content;
    }
}
