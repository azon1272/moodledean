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

/**
 * Здесь происходит объявление класса формы, 
 * на основе класса формы из плагина modlib/widgets. 
 * Подключается из init.php. 
 */

// Подключаем библиотеки
require_once('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** Форма создания/редактирования шаблона урока
 * 
 */
class dof_im_schedule_edit_schetemplate_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var int - id потока в таблице cstreams на который создается расписание 
     * (если расписание создается по ссылке, для конкретного потока) 
     */
    protected $cstreamid = 0;
    
    /**
     * @var int -id подразделения в таблице departments, в котором происходит работа
     */
    protected $departmentid=0;
    /**
     * @var - время по умолчанию, для которого создается шаблон (если есть) 
     */
    protected $begintime = 0;

    protected function im_code()
    {
        return 'schedule';
    }
    
    protected function storage_code()
    {
        return 'schetemplates';
    }
    
    protected function workflow_code()
    {
        return $this->storage_code();
    }
    
    /**
     * @see parent::definition()
     */
    public function definition()
    {
        $this->dof       = $this->_customdata->dof;
        // id учебного потока (если расписание создается для потока)
        $this->cstreamid = (int)$this->_customdata->cstreamid;
        // время начала (если добавляется шаблон для конкретного времени)
        $this->begintime = (int)$this->_customdata->begintime;
        
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // устанавливаем все скрытые поля 
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // время и длительность начала урока - hidden использован для того чтобы позже установить 
        // в группу правильные значения по умолчанию, переконвертировав из unixtime
        $mform->addElement('hidden','begin', $this->begintime);
        $mform->setType('begin', PARAM_INT);
        $mform->addElement('hidden','duration');
        $mform->setType('duration', PARAM_INT);
        
        // день недели
        $mform->addElement('select', 'daynum', $this->dof->get_string('daynum', $this->im_code()), 
                           $this->get_week_days());
        $mform->setType('daynum', PARAM_INT);
        // учебная неделя
        $dayvars = $this->dof->modlib('refbook')->get_day_vars();
        $mform->addElement('select', 'dayvar', $this->dof->get_string('dayvar', $this->im_code()), $dayvars);
        $mform->setType('dayvar', PARAM_INT);
        
        // начало урока
        // @todo заменить расширенным элементом dof_duration когда он будет написан
        $objs = array();
        // Создаем элементы формы - часы и минуты
        $hours   = $this->dof->modlib('widgets')->get_hours_list_for_select();
        $objs[]  =& $mform->createElement('select', 'hours', null, $hours);
        $minutes = $this->dof->modlib('widgets')->get_minutes_list_for_select();
        $objs[]  =& $mform->createElement('select', 'minutes', null, $minutes);
        // добавляем элементы в форму, как группу
        $group =& $mform->addElement('group', 'begintime_group',  
                    $this->dof->get_string('begin_time', $this->im_code()), $objs, ' : ', true);
        // получаем и устанавливаем время начала урока по умолчанию (из настроек)
        $defaultbegin = $this->get_default_begintime();
        $mform->setDefault('begintime_group[hours]',   $defaultbegin['hours']);
        $mform->setDefault('begintime_group[minutes]', $defaultbegin['minutes']);
        
        // длительность урока
        $durationoptions = $this->get_duration_list();
        $mform->addElement('select', 'duration_time', $this->dof->get_string('duration', $this->im_code()), 
                           $durationoptions);
        $mform->setType('duration_time', PARAM_INT);
        $mform->setDefault('duration_time', $this->get_default_duration());
        // форма урока
        $lessonforms = $this->dof->modlib('refbook')->get_event_form();
        $mform->addElement('select', 'form', $this->dof->get_string('lesson_form', $this->im_code()),
                           $lessonforms);
        $mform->setType('form', PARAM_ALPHANUM);
        $mform->setDefault('form', $this->_customdata->formlesson);
        // тип урока
        $lessontypes = $this->dof->modlib('refbook')->get_event_types();
        $mform->addElement('select', 'type', $this->dof->get_string('lesson_type', $this->im_code()),
                           $lessontypes);
        $mform->setType('type', PARAM_ALPHANUM);
        // Предмето-класс
        $cstreamvalue = $this->get_cstream_value($this->cstreamid);
        if ( $this->cstreamid )
        {// если передан id потока - то не показываем select, а просто отображаем его название
            // чтобы форма загружалась быстрее
            $mform->addElement('static', 'cstream_text', $this->dof->get_string('cstream', $this->im_code()),
                           $cstreamvalue);
            // и добавим дополнительный hidden-элемент, чтобы запомнить, для какого потока создавать шаблон
            $mform->addElement('hidden','cstreamid', $this->cstreamid);
            $mform->setType('cstreamid', PARAM_INT);
        }else
        {// поток не передан - покажем все возможные потоки
            $mform->addElement('select', 'cstreamid', $this->dof->get_string('cstream', $this->im_code()),
                           $cstreamvalue);
            $mform->setType('cstreamid', PARAM_INT);
        }
        
        // Подразделение
        $departments = $this->get_list_departments();
        $mform->addElement('select', 'department', $this->dof->get_string('department', $this->im_code()),
                           $departments);
        $mform->setType('department', PARAM_INT);
        // по умолчанию - текущее подразделение
        $mform->setDefault('department', $this->_customdata->departmentid);
        
        // Кабинет
        $mform->addElement('text', 'place', $this->dof->get_string('place', $this->im_code()), 
                           array('style' => "width:100px;"));
        $mform->setType('place', PARAM_TEXT);
        // поправочный зарплатный коэффициент
        $mform->addElement('text', 'salfactor', $this->dof->get_string('salfactor',$this->im_code()).':', 'size="10"');
        $mform->setType('salfactor', PARAM_TEXT);
        $mform->setDefault('salfactor', '0.00');
        // кнопки "сохранить" и "отмена"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Добавление дополнительльных полей формы и установка значений по умолчанию
     * после загрузки данных в форму (если происходит редактирование)
     * 
     * @return null
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // узнаем id шаблона (если он есть)
        $id = $mform->getElementValue('id');
        // добавляем заголовок формы
        $header =& $mform->createElement('header','form_title', $this->get_form_title($id));
        $mform->insertElementBefore($header, 'id');
        
        // определяем: шаблон создается или редактируется
        if ( $id )
        {// шаблон редактируется
            
            // @todo предусмотреть округление +- 5 минут
            // переведем начало урока из секунд в часы минуты
            // получим время начала урока
            $begintime = $mform->getElementValue('begin');
            $hours     = floor($begintime / 3600);
            $minutes   = floor(($begintime - $hours * 3600) / 60);
            $mform->setDefault('begintime_group[hours]', dof_userdate(mktime($hours, $minutes),"%H"));
            $mform->setDefault('begintime_group[minutes]', dof_userdate(mktime($hours, $minutes),"%M"));
            
            // переведем длительность урока из секунд в минуты
            $duration = $mform->getElementValue('duration');
            $minutes  = floor($duration / 60);
            $mform->setDefault('duration_time', $minutes);
            $mform->setDefault('department', $this->dof->storage('schtemplates')->get_field($id, 'departmentid'));
        }
    }
    
    /** Проверка данных формы
     * @param array $data - данные, пришедшие из формы
     * 
     * @todo добавить проверку пересечения времени с другими уроками. Выводить
     * текст ошибки в поле begintime, вместе со ссылкой на другой шаблон
     * @todo добавить проверку прав создания объектов в подразделении
     * 
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data,$files)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        $errors = array();
        
        if ( $this->cstreamid )
        {// определим как называется элеменьт, содержащий данные о потоке
            $csname = 'cstream_text';
        }else
        {
            $csname = 'cstreamid';
        }

        if ( isset($data['cstreamid']) AND $data['cstreamid'] )
        {
            if ( ! $cstream = $this->dof->storage('cstreams')->get($data['cstreamid']) )
            {// поток не существует
                $errors[$csname] = $this->dof->get_string('error:cstream_not_exists', $this->im_code());
            }
            // установим список статусов потока, в которых можно создавать шаблон
            $cstreamstatuses = array('plan', 'active', 'suspend');
            if ( ! in_array($cstream->status, $cstreamstatuses) )
            {// нельзя создавать шаблон на поток с таким статусом
                $errors[$csname] = $this->dof->get_string('error:wrong_cstream_status', $this->im_code());
            }
        }else
        {// учебный поток не указан, ошибка
            $errors[$csname] = $this->dof->get_string('error:cstream_not_set', $this->im_code());
        }
        
        if ( isset($data['department']) AND $data['department'] )
        {
            if ( ! $department = $this->dof->storage('departments')->get($data['department']) )
            {// подразделение не существует
                $errors['department'] = $this->dof->get_string('error:department_not_exists', $this->im_code());
            }
        }else
        {// подразделение не указано
            $errors['department'] = $this->dof->get_string('error:department_not_set', $this->im_code());
        }
        
        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Обработать пришедшие из формы данные
     *
     * @return bool 
     */
    public function process()
    {
        $add = array();
        $add['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        $add['ageid']        = optional_param('ageid', 0, PARAM_INT);
        $add['display']      = optional_param('display', null, PARAM_ALPHANUM);
        $add['intervalid']   = optional_param('intervalid', null, PARAM_INT);
        $add['daynum']       = optional_param('daynum', null, PARAM_INT);
        $add['dayvar']       = optional_param('dayvar', null, PARAM_INT);
        // переменная, хранящая результат операции сохранения
        $reslut = true;
        // создаем объект для вставки в базу (или обновления записи)
        $dbobj  = new stdClass();
        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу просмотра шаблонов
            redirect($this->dof->url_im('schedule','/index.php',$add));
        }
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            // сначала преобразуем все данные из формы к виду пригодному для записи в базу
            
            // преобразуем время начала урока в секунды
            $hours = intval($formdata->begintime_group['hours']) * 2 -
                     intval(dof_userdate(mktime($formdata->begintime_group['hours'], $formdata->begintime_group['minutes']),"%H"));
            
            if ($hours < 0 )
            {
                $hours = 24 + $hours;
            }
            if ($hours > 24 )
            {
                $hours = $hours - 24;
            }               
            $begintime = ($hours) * 3600 + 
                         (intval($formdata->begintime_group['minutes']) * 2 -
                         intval(dof_userdate(mktime($formdata->begintime_group['hours'], $formdata->begintime_group['minutes']),"%M"))) * 60;
            if ($begintime < 0 )
            {
                $begintime = 24 * 3600 - $begintime;
            }
            if ($begintime > 24 * 3600 )
            {
                $begintime = $begintime - 24 * 3600;
            }
            // преобразуем длительность урока в секунды
            $duration  = intval($formdata->duration_time) * 60;
            
            // Записываем проверенные и преобразованные данные в базу
            
            // время начала урока (уже в секундах)
            $dbobj->begin        = $begintime;
            // длительность урока (уже в секундах)
            $dbobj->duration     = $duration;
            // день недели
            $dbobj->daynum       = $add['daynum'] = $formdata->daynum;
            // тип недели
            $dbobj->dayvar       = $add['dayvar'] = $formdata->dayvar;
            // тип урока
            $dbobj->type         = $formdata->type;
            // форма урока
            $dbobj->form         = $formdata->form;
            // id потока для которго создается шаблон
            $dbobj->cstreamid    = $formdata->cstreamid;
            // id подразделения 
            $dbobj->departmentid = $formdata->department;
            // место проведения (кабинет, аудитория)
            $dbobj->place        = $formdata->place;
            $dbobj->salfactor = $formdata->salfactor;
            if ( $formdata->id )
            {// шаблон редактируется - обновляем запись
                $id = $dbobj->id = $formdata->id;
                $reslut = $reslut AND (bool)$this->dof->storage('schtemplates')->update($dbobj);
            }else
            {// шаблон добавляется - обновляем запись
                $id = $this->dof->storage('schtemplates')->insert($dbobj);
                $reslut = $reslut AND (bool)$id;
            }
            if ( $reslut )
            {// если все успешно - делаем редирект
                redirect($this->dof->url_im('schedule','/view.php?id='.$id,$add));
            }
            return $reslut;
        }
    }
    
    /** Вызывается в случае сохранения формы. Добавляет в форму элемент с результатом сохранения данных.
     * 
     * @param string $elementname - уникальное имя quickform-элемента, перед которым будет добавляться
     * сообщение о результате сохранения данных
     * @param string $message - сообщение для отображения
     * 
     * @return null
     */
    protected function add_save_message($elementname, $message)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // создаем элемент с сообщением
        $message =& $mform->createElement('static', 'cstream_text', '', $message);
        // добавляем элемент в форму
        $mform->insertElementBefore($message, $elementname);
    }
    
    /** Получить список дней недели для select-списка
     * 
     * @return array
     */
    protected function get_week_days()
    {
        return $this->dof->storage('ages')->get_list_daynums($this->_customdata->ageid, true);
    }
    
    /** Получить значение по умолчанию для времени начала урока 
     * @todo брать эти данные из настроек
     * 
     * @return array - массив, для установки значений по умолчанию в группе
     */
    protected function get_default_begintime()
    {
        // пока что установим 10:00
        return array('hours' => 10, 'minutes' => 0);
    }
    
    /** Получить длительность урока по умолчанию. 
     * 
     * @return int
     */
    protected function get_default_duration()
    {
        $duration = $this->dof->storage('config')->get_config_value
                    ('duration', 'storage', 'schtemplates', optional_param('departmentid', 0, PARAM_INT));
        if ( $duration )
        {
            return floor($duration / 60);
        }
        // настройки нет
        return 45;
    }
    
    /** Получить список всех возможных вариантиов длительности урока
     * @todo брать этот параметр из настроек
     * 
     * @return array - массив для использования в select-элементе
     */
    protected function get_duration_list()
    {
        return 
            array(
                    15  => '15',
                    20  => '20',
                    25  => '25',
                    30  => '30',
                    35  => '35',
                    40  => '40',
                    45  => '45',
                    50  => '50',
                    55  => '55',
                    60  => '60',
                    65  => '65',
                    70  => '70',
                    75  => '75',
                    80  => '80',
                    85  => '85',
                    90  => '90',
                    100 => '100',
                    105 => '105',
                    110 => '110',
                    115 => '115',
                    120 => '120'
                 );
    }
    
    /** Получить подразделения которые можно использовать (для select-списка)
     * 
     * @return array
     */
    protected function get_list_departments()
    {
        // добавляем нулевой элемент для select-списка
        $result = $this->dof_get_select_values();
        // получаем структурированный список подразделений
        $departments = $this->dof->storage('departments')->
                            departments_list_subordinated(null,'0',null,true);
        if ( ! empty($this->obj->leaddepid) AND ! (array_key_exists($this->obj->leaddepid, $departments)) )
        {
            $result[$this->obj->leaddepid] = $this->dof->storage('departments')->
                                                    get($this->obj->leaddepid)->name;  
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        if ( ! is_array($departments) )
        {//получили не массив - это ошибка';
            return $result;
        }
        
        $result += $departments;
        
        return $result;
    }
    
    /** Получить значение для элемента формы "учебный поток".
     * (select или static, в зависимости от ситуации) 
     * 
     * @return array|string - массив значений для select-элемента или строка для static-поля
     *                        если шаблон расписания создается по ссылке
     */
    protected function get_cstream_value($cstreamid=null)
    {
        if ( $cstreamid AND $cstreamname = $this->dof->storage('cstreams')->get_field($cstreamid, 'name') )
        {// если шаблон создается для конкретного потока - то показываем только static-поле
            // для того чтобы не грузить длинный select-список
            return $cstreamname;
        }
        
        // учебный поток не указан либо не существует - получим список всех 
        // учебных потоков, на которые пользователь может создавать шаблон
        
        // получаем все потоки подразделений пользователя
        $counds = new stdClass();
        $counds->ageid = $this->_customdata->ageid;
        $counds->status = array('plan', 'active', 'suspend');
        if ( $this->_customdata->teacherid )
        {// передаем id учителя
            $counds->teacherid = $this->_customdata->teacherid;
        }elseif ( $this->_customdata->studentid )
        {// передаем id студента
            $counds->personid = $this->_customdata->studentid;
        }elseif ( $this->_customdata->agroupid )
        {// передаем id группы
            $counds->agroupid = $this->_customdata->agroupid;
        }
        $cstreams = $this->dof->storage('cstreams')->get_listing($counds);
        
        if ( empty($cstreams) )
        {// ни одного потока нет - прекращаем обработку
            return $this->dof_get_select_values();
        }
        
        $usedcstreams = array();
        // оставляем только те, на которые пользователь имеет права
        foreach ( $cstreams as $cstream )
        {
            if ( ! $this->dof->storage('cstreams')->is_access('use',$cstream->id) )
            {// пользователь не имеет права создавать расписание на этот поток - пропускаем его
                continue;
            }
            $usedcstreams[$cstream->id] = $cstream;
        }
        
        // возвращаем преобразованное значение для select-элемента
        return $this->dof_get_select_values($usedcstreams);
    }
    
    /** Получить id пользователя в таблице persons, который сейчас редактирует форму
     * 
     * @return int
     */
    protected function get_userid()
    {
        $person = $this->dof->storage('persons')->get_bu();
        if ( $person )
        {
            return $person->id;
        } 
        
        return false;
    }
    
    /** Получить заголовок формы
     * 
     * @param int $id - редактируемого объекта
     * @return string
     */
    protected function get_form_title($id)
    {
        if ( ! $id )
        {//заголовок создания формы
            return $this->dof->get_string('new_template',  $this->im_code());
        }
        //заголовок редактирования формы
        return $this->dof->get_string('edit_template', $this->im_code());
    }
}

/** Класс, отвечающий за форму смену статуса 
 * 
 */
class dof_im_schedule_changestatus_schetemplate_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'schedule';
    }
    
    protected function workflow_code()
    {
        return 'schtemplates';
    }
}

/** Форма выбора режима отображения расписания
 * 
 */
class dof_im_schedule_display_mode_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var int - id подразделения в таблице departments, для которого будет просматриваться расписание
     */
    protected $departmentid;
    
    protected function im_code()
    {
        return 'schedule';
    }
    
    public function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        // заголовок
        $mform->addElement('header', 'header', $this->dof->get_string('display_mode', $this->im_code()));
        // учебный период
        $mform->addElement('html','<table width="100%" border="0"><tr><td width="40%">');
        $mform->addElement('select', 'ageid', $this->dof->get_string('age', $this->im_code()), $this->get_ages());
        $mform->setType('ageid', PARAM_INT);
        // тип отображения
        $mform->addElement('select', 'display', $this->dof->get_string('display_mode', $this->im_code()), $this->get_display_modes());
        $mform->setType('id', PARAM_ALPHANUM);
        $mform->addElement('html','</td><td width="60%">');
        $mform->addElement('radio', 'form', null, $this->dof->modlib('ig')->igs('all'),'all','align="left">');
        $mform->addElement('radio', 'form', null, $this->dof->get_string('only_distantly',$this->im_code()),'distantly');
        $mform->addElement('radio', 'form', null, $this->dof->get_string('only_intermal',$this->im_code()),'internal');
        $mform->disabledIf('form', 'display','noeq','time');
        $mform->setDefault('form', 'all');
        $mform->addElement('html','</td></table>');
        // кнопка "показать"
        $mform->addElement('submit', 'go', $this->dof->modlib('ig')->igs('show'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить список доступных учебных периодов
     * @todo добавить проверку права использовать период, когда будет преведена в порядок
     * функция is_access() в плагине ages. Пока что просто выводятся все доступные периоды
     * от plan до active
     * @todo Учитывать переданное подразделение
     * 
     * @return array массив учебных периодов для select-элемента
     */
    protected function get_ages()
    {
        $ages = $this->dof->storage('ages')->
                    get_records(array('status'=>array('plan', 'createstreams', 'createsbc', 'createschedule', 'active')));
        $useages = array();
        // оставляем только те, на которые пользователь имеет права
        foreach ( $ages as $age )
        {
            if ( ! $this->dof->storage('ages')->is_access('view',$age->id) )
            {// пользователь не имеет права создавать расписание на этот поток - пропускаем его
                continue;
            }
            $useages[$age->id] = $age;
        }
        
        return $this->dof_get_select_values($useages);
    }
    
    /** Получить возможные режимы отображения расписания (по времени/по ученикам/по учителям)
     * 
     * @return array
     */
    protected function get_display_modes()
    {
        return array(
                'time'     => $this->dof->get_string('display_mode:time',     $this->im_code()),
                'students' => $this->dof->get_string('display_mode:students', $this->im_code()),
                'teachers' => $this->dof->get_string('display_mode:teachers', $this->im_code()));
    }
    
    /** Получить id пользователя в таблице persons, который сейчас редактирует форму
     * 
     * @return int
     */
    protected function get_userid()
    {
        $person = $this->dof->storage('persons')->get_bu();
        if ( $person )
        {
            return $person->id;
        } 
        
        return false;
    }
    
    /** Обработчик формы
     * 
     * @param array $urloptions - массив дополнительных параметров для ссылки при редиректе
     */
    public function process($urloptions)
    {
        if ( $formdata = $this->get_data() AND confirm_sesskey() )
        {
            // добавляем к странице новые параметры
            $urloptions['ageid']   = $formdata->ageid;
            $urloptions['display'] = $formdata->display;
            // создаем ссылку
            $url = $this->dof->url_im('schedule', '/index.php', $urloptions);
            // перезагружаем страницу
            redirect($url, '', 0);
        }
    }
}


/** Форма создания расписания
 * 
 */
class dof_im_schedule_create_event_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var int - id подразделения в таблице departments, для которого будет просматриваться расписание
     */
    protected $departmentid;
    
    protected function im_code()
    {
        return 'schedule';
    }
    
    public function definition()
    {
        // $mform     = $this->_form;
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        $mform->addElement('hidden','departmentid', $this->_customdata->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        $this->departmentid =  $this->_customdata->departmentid;
        // заголовок
        $mform->addElement('header', 'header', $this->dof->get_string('create_event', $this->im_code()));
       
        // выбор даты
        $dateoptions = array();// объявляем массив для установки значений по умолчанию
        $dateoptions['startyear'] = $this->dof->storage('persons')->get_userdate(time(),"%Y")-12; // устанавливаем год, с которого начинать вывод списка
        $dateoptions['stopyear']  = $this->dof->storage('persons')->get_userdate(time(),"%Y")+12; // устанавливаем год, которым заканчивается список
        $dateoptions['optional']  = false; // убираем галочку, делающую возможным отключение этого поля
        $mform->addElement('date_selector', 'date', $this->dof->get_string('select_date', $this->im_code()).':', $dateoptions);        
        $mform->setDefault('date', time());
        // тип отображения
        $mform->addElement('select', 'dayvar', $this->dof->get_string('dayvar', $this->im_code()), array('1' => $this->dof->get_string('odd',$this->im_code()) ,
                                                                                                              '2' => $this->dof->get_string('event',$this->im_code())));   
        $mform->setType('dayvar', PARAM_TEXT);
        $mform->setDefault('dayvar', 1);
        
        // Массив доступных учебных периодов
        $ages = $this->get_ages();
        // Готовим массив номеров учебных дней
        $daynum = array();
        foreach ( $ages as $id => $agename )
        {
            $daynum[$id] = $this->get_week_days($id, true);
        }
        // Добавим зависимость выбора дня недели от периода
        $complex = $mform->addElement('hierselect', 'complex',
                $this->dof->get_string('age', $this->im_code()).':<br/>'.
                $this->dof->get_string('daynum', $this->im_code()).':', null, '<br/>');
        $complex->setOptions(array($ages, $daynum));
        
        // учебный период
        /*$mform->addElement('select', 'ageid', $this->dof->get_string('age', $this->im_code()), $this->get_ages());
        $mform->setType('ageid', PARAM_INT);
        $mform->setDefault('ageid', $this->_customdata->ageid);
        
        // день недели
        $mform->addElement('select', 'daynum', $this->dof->get_string('daynum', $this->im_code()), 
                           $this->get_week_days(1));
        $mform->setType('daynum', PARAM_INT);*/
        
        // галочка обновить распиание
        $mform->addElement('checkbox', 'update_sch', '', $this->dof->get_string('update_schedule',$this->im_code()));
        
        // checkbox для мнимого урока
        $mform->addElement('checkbox', 'implied_event', '', $this->dof->get_string('implied_event',$this->im_code()));
        $mform->setType('implied_event', PARAM_BOOL);
        
        // делаем с помошью HTML для ВИЗУАЛА
        // TODO в будущем это вынести в стили
        $table_html = '<table align="left" width=80%><tr><td>';
        $mform->addElement('html', $table_html);
        $mform->addElement('submit', 'button1', $this->dof->get_string("create_week",$this->im_code()));
        $mform->addElement('html', '</td><td>');        
        $mform->addElement('submit', 'button2', $this->dof->get_string("create_day",$this->im_code()));
        $mform->addElement('html', '</td></tr><tr><td>');
        $mform->addElement('static', 'testname1', '', $this->dof->get_string("begin_this_date",$this->im_code()));
        $mform->addElement('html', '</td><td>');
        $mform->addElement('static', 'testname2', '', $this->dof->get_string("for_this_date",$this->im_code())); 
        $mform->addElement('html', '</td></tr></table>');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');

    }
    
    /** Получить список доступных учебных периодов
     * @todo добавить проверку права использовать период, когда будет преведена в порядок
     * функция is_access() в плагине ages. Пока что просто выводятся все доступные периоды
     * от plan до active
     * @todo Учитывать переданное подразделение
     * 
     * @return array массив учебных периодов для select-элемента
     */
    protected function get_ages()
    {
        $ages = $this->dof->storage('ages')->
                    get_records(array('status'=>array('plan', 'createstreams', 'createsbc', 'createschedule', 'active')));
        $useages = array();
        // оставляем только те, на которые пользователь имеет права
        foreach ( $ages as $age )
        {
            if ( ! $this->dof->storage('ages')->is_access('use',$age->id) )
            {// пользователь не имеет права создавать расписание на этот поток - пропускаем его
                continue;
            }
            $useages[$age->id] = $age;
        }
        
        return $this->dof_get_select_values($useages);
    }
    
    
    /** 
     * Получить список дней недели для select-списка
     * 
     * @param $ageid - ID учебного периода, 
     *                 для которого получаем число дней в учебной неделе
     * 
     * @return array - массив алиасов для номеров дней учебной недели
     */
    protected function get_week_days($ageid)
    {
        // Вернем массив дней для учебного периода
        return $this->dof->storage('ages')->get_list_daynums($ageid, true);
    }    
    

    /** Проверка данных формы
     * @param array $data - данные, пришедшие из формы
     * 
     * @todo добавить проверку пересечения времени с другими уроками. Выводить
     * текст ошибки в поле begintime, вместе со ссылкой на другой шаблон
     * @todo добавить проверку прав создания объектов в подразделении
     * 
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data,$files)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        $data['ageid'] = $data['complex'][0];
        
        $errors = array();
        if ( isset($data['ageid']) AND $data['ageid'] )
        {
            if ( ! $age = $this->dof->storage('ages')->get($data['ageid']) )
            {// подразделение не существует
                $errors['ageid'] = $this->dof->get_string('error:ageid_not_exists', $this->im_code());
            }
        }else
        {// подразделение не указано
            $errors['ageid'] = $this->dof->get_string('error:ageid_not_set', $this->im_code());
        }
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Проверяет, можно ли удалить день 
     * @param array $formdata - данные, пришедшие из формы
     * @param integer $date - дата времени дня
     * @return bool
     */
    protected function acl_can_delete_day($formdata, $date)
    {
        // получим день, который собираемся удалить
        if ( ! $day = $this->dof->storage('schdays')->
                get_day($formdata->ageid,$date,$formdata->departmentid) )
        {// дня нет - значит и удалять нечего, все хорошо
            return true;
        }
        
        if ( time() > ($day->date - 12*3600)  )
        {//наступивший день нельзя удалять
            return false;
        }
        
        if ( $this->dof->storage('schdays')->
                is_access('changestatus:to:deleted', $day->id, null, $day->departmentid) )
        {// у пользователя есть право менять статус на "удаленный"
            return true;
        }
        
        return false;
    }
    
    /** Обработать пришедшие из формы данные
     * @todo переписать эту функцию, вынеся в отдельный метод обработку одного дня
     * @todo вынести в отдельный метод обработку не созданных событий
     * @todo - при успешном ссоздании расписания выводить полный список того что создалось
     *
     * @return bool 
     */
    public function process()
    {
        if ( ! $this->is_submitted() OR ! confirm_sesskey() OR ! $formdata = $this->get_data() )
        {// данные не пришли из формы, или не проверены
            return '';
        }
        if ( ! $this->departmentid )
        {// подразделение не выбрано - создавать расписание нельзя
            $message = $this->dof->get_string('error:no_select_department', $this->im_code());
            return $this->style_result_message($message, false);
        }
        
        $formdata->ageid = $formdata->complex[0];
        $formdata->daynum = $formdata->complex[1];
        
        $implied = false;
        if ( isset($formdata->implied_event) )
        {// мнимый урок
            $implied = true;
        }
        $message = '';
        if ( isset($formdata->button2) )
        {// создаем один день
            if ( empty($formdata->update_sch) AND 
                 $this->dof->storage('schdays')->is_exists_day
                 ($formdata->ageid,$formdata->date,$this->departmentid) )
            {// если день существует - создавать новые нельзя
                $message = $this->dof->get_string('error:day_already_exists', $this->im_code());
                return $this->style_result_message($message, false);
            }else
            {// день не существует, или нам нужно обновить расписание
                if ( isset($formdata->update_sch) )
                {//надо удалить старый день - но у пользователя нет такого права
                    if ( ! $this->delete_entire_day($formdata) )
                    {// не удалось удалить старый день перед созданием расписания
                        $message = $this->dof->get_string('error:cannot_delete_day', $this->im_code());
                        return $this->style_result_message($message, false);
                    }
                }
                
                if ( ! $dayid = $this->dof->storage('schdays')->save_day($formdata) )
                {// не удалось создать день
                    $message = $this->dof->get_string('error:cannot_create_day', $this->im_code());
                    return $this->style_result_message($message, false);
                }
                
                if ( $implied )
                {// установим мнимый статус
                    $this->dof->storage('schdays')->update_holiday($dayid);
                }
                $templateids = $this->create_events_day(
                     $formdata->ageid,$formdata->daynum,$formdata->dayvar,$dayid,$formdata->departmentid,$implied);
                if ( ! empty($templateids) )
                {// при сохранении шаблонов возникли ошибки
                    $this->dof->workflow('schdays')->change($dayid, 'draft');
                    $errordays[] = $this->get_templates_errors($templateids);
                } else 
                {
                    $this->dof->workflow('schdays')->change($dayid, 'active');
                }
            }
        }
        if ( isset($formdata->button1) )
        {// создаем расписание на неделю
            // получим дни недели
            $days = $this->get_week_days($formdata->ageid);
            // для каждого дня недели создаем расписание
            $createdays = array();
            // создаем массив для ошибок, которые могут возникнуть при сохранении каждго дня
            $errordays  = array();
            // если не удалось создать расписание на 1 день то не создаем на вСЮ неделю
            // потому повторяем перебор по дням, дабы исключить 
            // создание распиписания для недели хоть для одного дня
            foreach ( $days as $num => $day )
            {
                $message = '';
                // расчитываем дату недели
                $date = $formdata->date + (($num-1)*24*3600);
                $mdate = dof_usergetdate($date);
                $date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
                
                if ( empty($formdata->update_sch) AND 
                     $this->dof->storage('schdays')->is_exists_day
                     ($formdata->ageid,$date,$this->departmentid) )
                {// создаем новый день. Если день существует -  - НЕЛЬЗЯ создавать и на ВСЮ неделю
                    $message = $day.': '.$this->dof->get_string('error:day_already_exists', $this->im_code());
                    return $this->style_result_message($message, false);
                } else 
                {// нужно удалить старый день
                    if ( isset($formdata->update_sch) )
                    {// надо удалить день
                        if ( ! $this->delete_entire_day($formdata, $num) )
                        {// не удалось удалить старый день перед созданием расписания - НЕЛЬЗЯ создавать и на ВСЮ неделю
                            $message = $day.': '.$this->dof->get_string('error:cannot_delete_day', $this->im_code());
                            return $this->style_result_message($message, false);
                        }
                    }
                }
                
            }
            
            // ошибок НЕТ - СОЗДАЁМ
            foreach ( $days as $num => $day )
            {
                $message = '';
                
                $dayobj = clone $formdata;
                // расчитываем дату недели
                $date = $formdata->date + (($num-1)*24*3600);
                $mdate = dof_usergetdate($date);
                $date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
                $dayobj->date = $date;
                $dayobj->daynum = $num;
                
                if ( ! $dayid = $this->dof->storage('schdays')->save_day($dayobj) )
                {// не удалось сохранить день
                    $message = $day.': '.$this->dof->get_string('error:cannot_create_day', $this->im_code());
                    $errordays[] = $this->style_result_message($message, false);
                    // с этим днем не получилось - переходим к следующему
                    continue;
                }
                
                if ( $implied )
                {// установим мнимый статус
                    $this->dof->storage('schdays')->update_holiday($dayid);
                }
                
                $templateids = $this->create_events_day(
                     $dayobj->ageid,$num,$dayobj->dayvar,$dayid,$dayobj->departmentid,$implied);
                $createdays[$day] = implode(',',array_keys($templateids));
                if ( ! empty($templateids) )
                {// при сохранении шаблонов возникли ошибки
                    $this->dof->workflow('schdays')->change($dayid, 'draft');
                    $message = $this->style_result_message($day.': <br/>', false);
                    $errordays[] = $message.$this->get_templates_errors($templateids);
                } else 
                {
                    $this->dof->workflow('schdays')->change($dayid, 'active');
                }
            }    
        }
        if ( ! empty($errordays) )
        {// при создании расписания на некоторые дни возникли ошибки - отобразим их
            $message = implode(' ', $errordays);
            return $message;
        }
        
        // все отработано без ошибок, расписание создано
        $message = $this->dof->get_string('schedule_created', $this->im_code());
        return $this->style_result_message($message, true);
    }
    
    /** 
     * Удалить день вместе с событиями
     * @param array $formdata - данные, пришедшие из формы
     * 
     * @return bool
     */
    protected function delete_entire_day($formdata, $num = null)
    {
        
        if ( is_null($num) )
        {// если нам не передали номер дня - то берем его из формы
            $num  = $formdata->daynum;
            $date = $formdata->date;
        } else 
        {// расчитываем дату недели
            $date = $formdata->date + (($num-1)*24*3600);
        }    
        if ( ! $this->acl_can_delete_day($formdata, $date) )
        {// у пользователя нет права удалять день
            return false;
        }

        $mdate = dof_usergetdate($date);
        $date = mktime(12,0,0,$mdate['mon'],$mdate['mday'],$mdate['year']);
        
        // если по ошибке на одно и то же время создано несколько дней в одном подразделении - то 
        // удалим их всех, для избежания ошибок
        $schdays = $this->dof->storage('schdays')->get_records_select(
            "ageid={$formdata->ageid} AND date={$date} AND 
             departmentid={$formdata->departmentid} AND status IN ('active','holiday','fixed', 'plan')");

        if ( ! $schdays )
        {// ничего не нужно удалять - таких дней нет
            return true;
        }
        
        $result = true;
        foreach ( $schdays as $schday )
        {
            $deleteresult = $this->dof->storage('schdays')->delete_entire_day($schday->id);
            $result = $result && $deleteresult; 
        }
        // день успешно удален
        return $result;
    }

    /** Создает расписание на день
     * @todo добавить ссылки на процессы и событий (cstreams, schevents)
     * 
     * @param int $ageid - id периода
     * @param int $daynum -день недели
     * @param int $dayvar - вариант недели
     * @param int $dayid - id созданного дня
     * @param int $depid - id подразделения
     * @param int $implied - является ли урок мнимым
     * @return array - массив шаблонов, на которые не создались события
     */
    protected function create_events_day($ageid,$daynum,$dayvar,$dayid,$depid,$implied=false)
    {
        // найдем все интересующие нас шаблоны 
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->daynum = $daynum;
        $conds->dayvar = $dayvar;
        $conds->ageid  = $ageid;
        $conds->status = array('active');
        if ( ! $templates = $this->dof->storage('schtemplates')->get_objects_list($conds))
        {// не нашли шаблоны - не надо создавать события';
            return array();
        }
        $templateids = array();
        foreach ( $templates as $template )
        {// для каждого шаблона создадим событие
            if ( ! $cstream = $this->dof->storage('cstreams')->get($template->cstreamid) )
            {// поток не найден
                $template->error = 'error:cstream_not_found';
                $template->errortype = 'schtemplate';
                $templateids[$template->id] = $template;
                continue;
            }
            if ( $cstream->status != 'active' )
            {// поток не активный - создать урок нельзя
                $template->error = 'error:cstream_is_not_active';
                $template->errortype = 'cstream';
                $templateids[$template->id] = $template;
                continue;
            }
            // расчитываем дату
            $date = $this->dof->storage('schdays')->get_field($dayid,'date');
            // отматываем дату дня на начало дня и добавляем время урока по шаблону
            $date_begin = $date - (12*3600) + $template->begin;
            if ( ($cstream->begindate > $date_begin) OR ($cstream->enddate < $date_begin) )
            {// если дата урока не входит в промежуток времени потока
                // событие создавать нельзя
                $template->error = 'error:begindate_and_cstream_not_compatible';
                $template->errortype = 'cstream';
                $templateids[$template->id] = $template;
                continue;
            }
            $event = new stdClass();
            $event->templateid     = $template->id; // id шаблона
            $event->dayid          = $dayid;// id дня
            $event->type           = $template->type; //тип урока
            $event->cstreamid      = $template->cstreamid; // id потока
            $event->teacherid      = $cstream->teacherid; //id учителя @todo может и не надо
            $event->appointmentid  = $cstream->appointmentid; // id должности учителя, который ведет урок

            if ( isset($cstream->appointmentid) AND $cstream->appointmentid )
            {// проверим статус табельного номера
                $status = $this->dof->storage('appointments')->get_field($cstream->appointmentid, 'status');
                if ( $status == 'patient' OR $status == 'vacation' )
                {// учитель на больничном или в отпуске не может быть назначен событию
                    $event->teacherid      = 0;
                    $event->appointmentid  = 0;
                }
            }
            $event->date           = $date_begin; // дата урока
            $event->duration       = $template->duration; // длительность
            $event->place          = $template->place; // аудитория
            $event->form           = $template->form; // форма занятия
            $event->ahours         = 1; // предполагаемое кол-во академических часов
            if ( ! $scheventid = $this->dof->storage('schevents')->insert($event) )
            {// не удалось сохранить событие
                $template->error = 'error:schevent_not_saved';
                $template->errortype = 'schevent';
                $templateids[$template->id] = $template;
                continue;
            }
            if ( $implied )
            {// установим мнимый статус
                $this->dof->workflow('schevents')->change($scheventid, 'implied');
            }
        }
        // вернем шаблоны, где возникли ошибки
        return $templateids;
    }
    
    /** Раскрасить сообщение в зависимости от того, успешно или неуспешно прошла операция
     * 
     * @param string $message
     * @param bool $success - результат операции. true - успешно, false- неуспешно
     */
    protected function style_result_message($message, $success)
    {
       $color = 'red';
       if ( $success )
       {
           $color = 'green';
       }
       return '<p align="center" style="color:'.$color.';"><b>'.$message.'</b></p>'; 
    }
    
    /** Обработать массив не созданных уроков, и вывести сообщение
     * @todo документировать остальные параметры
     * 
     * @param array - массив записей из таблицы schtemplates с дополнительным полем error,
     *                которое содержит всебе идентификатор строки перевода из языкового файла
     * 
     * @return string - сообщение о том какие шаблоны не удалось создать и почему
     */
    protected function get_templates_errors($templates)
    {
        $result = '';
        if ( empty($templates) )
        {// ошибок нет - выводить нечего
            return $result;
        }
        
        $message = $this->dof->get_string('error:schedule_not_created', $this->im_code());
        $message = $this->style_result_message($message, false);
        
        // создаем объект для таблицы
        $table = new stdClass();
        $table->align = array("center","center","center","center","center");
        // создаем заголовки для таблицы
        // @todo всесто конкретного типа ошибки указывать тот, который пришел извне
        $table->head = $this->get_error_table_header('cstream');
        
        foreach ( $templates as $id=>$template )
        {// перебираем каждый созданный с ошибкой шаблон и устанавливаем причину ошибки
            $row = $this->get_error_table_row($template);
            // получаем таблицу с данными о шаблоне
            $table->data[] = $row;
        }
        
        $table = $this->dof->modlib('widgets')->print_table($table, true);
        
        $result .= $message.$table;
        
        return $result;
    }
    
    /** Получить строку с данными для таблицы  с ошибками
     * @todo дописать варианты для ошибок шаблона и события
     * 
     * @return array
     */
    protected function get_error_table_row($template)
    {
        switch ( $template->errortype )
        {
            // таблица с ошибками шаблона
            case 'schtemplate': 
                return $this->get_error_table_row_schtemplate($template);
            break;
            // таблица с ошибками события
            case 'schevent': 
                return $this->get_error_table_row_schevent($template);
            break;
            // таблица с ошибками потока
            case 'cstream':
                return $this->get_error_table_row_cstream($template);
            break;
        }
    }
    
    /** Получить строку таблицы для отображения информации об ошибке потока
     * (предполагается, что при передачи данных в эту функцию поток существует)
     * 
     */
    protected function get_error_table_row_cstream($template)
    {
        $row = array();
        $emptyrow = array('','','',$this->dof->get_string($template->error, $this->im_code()),'');
        
        if ( ! $cstream = $this->dof->storage('cstreams')->get($template->cstreamid) )
        {
            return $emptyrow;
        }
        if ( $this->dof->storage('cstreams')->is_access('view', $cstream->id) )
        {// у пользователя есть право на просмотр предмето-класса - покажем ссылку
            $cstreamname = '<a href='.$this->dof->url_im('cstreams','/view.php?cstreamid='.$cstream->id,
                           array('departmentid' => $this->departmentid)).' target="_blank" >'.
                           $cstream->name.'</a>';
        }else
        {// у пользователдя нет права на просмотр предмето-класса - покажем только название
            $cstreamname = $cstream->name;
        }
        if ( ! $pitem   = $this->dof->storage('programmitems')->get($cstream->programmitemid) )
        {
            return $emptyrow;
        }
        if ( ! $appointment = $this->dof->storage('appointments')->get($cstream->appointmentid) )
        {
            return $emptyrow;
        }
        if ( ! $eagreement  = $this->dof->storage('eagreements')->get($appointment->eagreementid) )
        {
            return $emptyrow;
        }
        if ( ! $teachername = $this->dof->storage('persons')->get_fullname($eagreement->personid) )
        {
            return $emptyrow;
        }
        
        $row[] = $cstreamname;
        $row[] = $pitem->name; 
        $row[] = $teachername;
        $row[] = $this->dof->get_string($template->error, $this->im_code());
        // ссылки на просмотр и редактирование шаблона
        $link  = $this->get_link('view_template', $template);
        $link .= $this->get_link('edit_template', $template);
        $row[] = $link;
        
        return $row;
    }
    
    /** Получить строку таблицы для отображения информации об ошибке шаблона
     * @todo изменить порядок элементов в массиве, когда будут отображаться 3 разные таблицы
     * 
     */
    protected function get_error_table_row_schtemplate($template)
    {
        $row = array();
        switch ($template->error)
        {
            // предмето-класс не найден
            case 'error:cstream_not_found': 
                // нет потока
                $row[] = $this->dof->modlib('ig')->igs('no');
                // нет учителя
                $row[] = $this->dof->modlib('ig')->igs('no');
                // нет предмета
                $row[] = $this->dof->modlib('ig')->igs('no');
                // описание ошибки
                $row[] = $this->dof->get_string($template->error, $this->im_code());
                // ссылки на просмотр и редактирование шаблона
                $link  = $this->get_link('view_template', $template);
                $link .= $this->get_link('edit_template', $template);
                $row[] = $link;
            break;
        }
        
        return $row;
    }
    
    /** Получить строку таблицы для отображения информации об ошибке события
     * (предполагается, что при передачи данных в эту функцию поток существует)
     * @todo изменить порядок элементов в массиве, когда будут отображаться 3 разные таблицы
     * 
     */
    protected function get_error_table_row_schevent($template)
    {
        $row = array();
        
        switch ($template->error)
        {
            
        }
        
        return $row;
    }
    
    /** Получить ссылку с иконкой, для выполнения действия, с проверкой прав
     * @param string $action - совершаемое действие
     * @param int $id - id объекта, на который генерируется ссылка
     * 
     */
    protected function get_link($action, $template)
    {
        $link = '';
        // дополнительные параметры ссылки
        $add = array('departmentid' => $this->departmentid);
        switch ( $action )
        {
            case 'view_template': 
                $id = $template->id;
                if ( $this->dof->storage('schtemplates')->is_access('view',$id) )
                {// пользователь может просматривать шаблон
                    $link .= ' <a href='.$this->dof->url_im($this->im_code(),'/view.php?id='.$id,$add).' target="_blank" >'.
                            '<img src="'.$this->dof->url_im($this->im_code(), '/icons/view.png').
                            '"alt="'.$this->dof->get_string('view_template', $this->im_code()).
                            '" title="'.$this->dof->get_string('view_template', $this->im_code()).'">'.'</a>';
                }
            break;
            case 'edit_template':
                $id = $template->id;
                if ( $this->dof->storage('schtemplates')->is_access('edit',$id) )
                {// пользователь может редактировать шаблон
                    $link .= ' <a href='.$this->dof->url_im($this->im_code(),'/edit.php?id='.$id,$add).' target="_blank" >'.
                            '<img src="'.$this->dof->url_im($this->im_code(), '/icons/edit.png').
                            '"alt="'.$this->dof->get_string('edit_template', $this->im_code()).
                            '" title="'.$this->dof->get_string('edit_template', $this->im_code()).'">'.'</a>';
                }
            break;
        }
        
        return $link;
    }
    
    /** Получить массив строк, которые будут являться заголовками для таблицы ошибок,
     * возникших при создании расписания
     * @todo дописать варианты для шаблона и события
     * 
     * @param string $type - тип таблицы с ошибками. Возможные варианты:
     *                       schtemplate - ошибка в шаблоне
     *                       cstream - ошибка в потоке
     *                       event - ошибка в событии
     * 
     * @return array
     */
    protected function get_error_table_header($type)
    {
        switch ( $type )
        {
            // таблица с ошибками шаблона
            case 'schtemplate': 
            // таблица с ошибками события
            case 'event': 
            // таблица с ошибками потока
            case 'cstream':
            return array($this->dof->get_string('cstream_name', $this->im_code()),
                         $this->dof->get_string('item', $this->im_code()),
                         $this->dof->get_string('teacher', $this->im_code()),
                         $this->dof->modlib('ig')->igs('error'),
                         $this->dof->modlib('ig')->igs('actions'));
            break;
        }
        return array();
    }
}


/** Форма выбора данных для отчета по шаблонам(нагрузки, перегрузки)
 * 
 */
class dof_im_schedule_report_template extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'schedule';
    }
    
    public function definition()
    {
        // $mform     = $this->_form;
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        
        // устанавливаем все скрытые поля 
        $mform->addElement('hidden','id');
        $mform->setType('id', PARAM_INT);
       // $mform->setDefault('id',0);
        // заголовок
        $mform->addElement('header', 'header', $this->dof->get_string('report_dan', $this->im_code()));
        // учебный период
        $mform->addElement('select', 'ageid', $this->dof->get_string('age', $this->im_code()), $this->get_ages());
        $mform->setType('ageid', PARAM_INT);
        $mform->setDefault('ageid', $this->_customdata->ageid);
        
        // выбор задачи - радио кнопки
        $mform->addElement('radio', 'load', '', $this->dof->get_string('cstreams_load', $this->im_code()), '0');
        // пересечение
        $mform->addElement('radio', 'load', '', $this->dof->get_string('templater_intersection', $this->im_code()) , '1');   
        // по умолчанию - нагрузка шаблонов    
        $mform->Setdefault('load', '0');
        
        // Подразделение
        $departments = $this->get_list_departments();
        $mform->addElement('select', 'department', $this->dof->get_string('department_cstreams', $this->im_code()),
                           $departments);
        $mform->setType('department', PARAM_INT);
        $mform->setDefault('department', $this->_customdata->departmentid);
        // кнопка "показать"
        // $mform->addElement('submit', 'go', $this->dof->modlib('ig')->igs('show'));
        $mform->addElement('dof_single_use_submit', 'go', $this->dof->modlib('ig')->igs('show'));
        $mform->disabledIf('department', 'load', 'eq', '1');
        // кнопка обновить
       /* $mform->addElement('submit', 'update', $this->dof->get_string('update', $this->im_code()));
        $mform->closeHeaderBefore('update');    */  
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim'); 
    }
    
    public function definition_after_data()
    {
        // Добавляем кнопку оьбновить
        $mform =& $this->_form;
        $id = $mform->getElementValue('id');
        // кнопка обновить
        if ( isset($id) )
        {
            $mform->addElement('dof_single_use_submit', 'update', $this->dof->get_string('update', $this->im_code()));
            $mform->closeHeaderBefore('update');
        }            
    }   
    
    
    /** Получить список доступных учебных периодов
     * @todo добавить проверку права использовать период, когда будет преведена в порядок
     * функция is_access() в плагине ages. Пока что просто выводятся все доступные периоды
     * от plan до active
     * @todo Учитывать переданное подразделение
     * 
     * @return array массив учебных периодов для select-элемента
     */
    protected function get_ages()
    {
        $ages = $this->dof->storage('ages')->
                    get_records(array('status'=>array('plan', 'createstreams', 'createsbc', 'createschedule', 'active')));
        $useages = array();
        // оставляем только те, на которые пользователь имеет права
        foreach ( $ages as $age )
        {
            if ( ! $this->dof->storage('ages')->is_access('view',$age->id) )
            {// пользователь не имеет права создавать расписание на этот поток - пропускаем его
                continue;
            }
            $useages[$age->id] = $age;
        }
        // убираем нулевой элемент (выбрать)
        return $this->dof_get_select_values($useages,false);
    }
  
    /** Проверка данных формы
     * @param array $data - данные, пришедшие из формы
     * 
     * @todo добавить проверку пересечения времени с другими уроками. Выводить
     * текст ошибки в поле begintime, вместе со ссылкой на другой шаблон
     * @todo добавить проверку прав создания объектов в подразделении
     * 
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data,$files)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $errors = array();
        if ( isset($data['ageid']) AND $data['ageid'] )
        {
            if ( ! $age = $this->dof->storage('ages')->get($data['ageid']) )
            {// подразделение не существует
                $errors['ageid'] = $this->dof->get_string('error:ageid_not_exists', $this->im_code());
            }
        }else
        {// подразделение не указано
            $errors['ageid'] = $this->dof->get_string('error:ageid_not_set', $this->im_code());
        }
        
        if ( isset($data['department']) AND $data['department'] )
        {
            if ( ! $department = $this->dof->storage('departments')->get($data['department']) )
            {// подразделение не существует
                $errors['department'] = $this->dof->get_string('error:department_not_exists', $this->im_code());
            }
        }elseif( ! $data['load'] )
        {// подразделение не указано
            $errors['department'] = $this->dof->get_string('error:department_not_set', $this->im_code());
        }
        
        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');        
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Обработать пришедшие из формы данные
     *
     * @return bool 
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            return $formdata;
        }
        return '';
    }

    /** Получить подразделения которые можно использовать (для select-списка)
     * 
     * @return array
     */
    protected function get_list_departments()
    {
        // добавляем нулевой элемент для select-списка
        $result = $this->dof_get_select_values();
        // получаем структурированный список подразделений
        $departments = $this->dof->storage('departments')->
                            departments_list_subordinated(null,'0',null,true);
        if ( ! empty($this->obj->leaddepid) AND ! (array_key_exists($this->obj->leaddepid, $departments)) )
        {
            $result[$this->obj->leaddepid] = $this->dof->storage('departments')->
                                                    get($this->obj->leaddepid)->name;  
        }
        if ( ! is_array($departments) )
        {//получили не массив - это ошибка';
            return $result;
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        $result += $departments;
        
        return $result;
    }    
    
}


?>