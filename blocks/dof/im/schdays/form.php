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
class dof_im_schdays_edit_schday_form extends dof_modlib_widgets_form
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
        return 'schdays';
    }
    
    protected function storage_code()
    {
        return 'schdays';
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
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // устанавливаем все скрытые поля 
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', $this->_customdata->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        $this->departmentid =  $this->_customdata->departmentid;
        $mform->addElement('hidden','ageid', $this->_customdata->ageid);
        $mform->setType('ageid', PARAM_INT);
        $this->age = $this->dof->storage('ages')->get($this->_customdata->ageid);
        // учебный период
        $mform->addElement('static', 'agename', $this->dof->get_string('age', $this->im_code()));
        $mform->setType('ageid', PARAM_INT);
        $mform->setDefault('agename', $this->age->name);
        // Подразделение
        $mform->addElement('static', 'department', $this->dof->get_string('department', $this->im_code()));
        $mform->setDefault('department', 
                           $this->dof->storage('departments')->get_field($this->departmentid,'name').' ['.
                           $this->dof->storage('departments')->get_field($this->departmentid,'code').']');
        // выбор даты
        $dateoptions = array();// объявляем массив для установки значений по умолчанию
        if ( $this->_customdata->edit_date )
        {
            
            $dateoptions['startyear'] = $this->dof->storage('persons')->get_userdate(time(),"%Y")-12; // устанавливаем год, с которого начинать вывод списка
            $dateoptions['stopyear']  = $this->dof->storage('persons')->get_userdate(time(),"%Y")+12; // устанавливаем год, которым заканчивается список
            $dateoptions['optional']  = false; // убираем галочку, делающую возможным отключение этого поля
            $mform->addElement('date_selector', 'date', $this->dof->get_string('date', $this->im_code()).':', $dateoptions);        
            $mform->setDefault('date', time());
        }else
        {
            $mform->addElement('hidden','date');
            $mform->setType('date', PARAM_INT);
            $mform->addElement('date_selector', 'date_text', $this->dof->get_string('date', $this->im_code()).':', $dateoptions, 'disabled');        
        }
        
        // день недели
        // Включены алиасы номеров дней
        $usealiases = $this->dof->storage('ages')->get_custom_option(
                'useweekdaynames',
                $this->_customdata->ageid
        );
        $mform->addElement('select', 'daynum', $this->dof->get_string('daynum', $this->im_code()), 
                           $this->dof->storage('ages')->get_list_daynums($this->age, $usealiases));
        $mform->setType('daynum', PARAM_INT);
        // учебная неделя
        $mform->addElement('select', 'dayvar', $this->dof->get_string('dayvar', $this->im_code()), 
                array('1' => $this->dof->get_string('odd',$this->im_code()) ,
                      '2' => $this->dof->get_string('event',$this->im_code())));   
        $mform->setType('dayvar', PARAM_TEXT);
        $mform->setDefault('dayvar', 1);
        
        // тип урока
        $lessontypes = $this->get_types_day();
        $mform->addElement('select', 'type', $this->dof->get_string('type', $this->im_code()),
                           $lessontypes);
        $mform->setType('type', PARAM_ALPHANUM);
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
        if ( $id == 0 OR $this->dof->storage('schdays')->get_field($id,'status') == 'plan' )
        {
            // создать расписание
		    $mform->addElement('checkbox', 'create', '', $this->dof->get_string('create_events',$this->im_code()));
		    $mform->setType('create', PARAM_BOOL);
        }
        if ( $id )
        {// шаблон редактируется
            $date = $this->dof->storage('schdays')->get_field($id,'date');
            $mform->setDefault('date', $date);
            $mform->setDefault('date_text', $date);
        }elseif ( ! $this->_customdata->edit_date )
        {
            $mform->setDefault('date', optional_param('date', 0, PARAM_INT));
            $mform->setDefault('date_text', optional_param('date', 0, PARAM_INT));
        }else
        {
            $mform->setDefault('date', time());
        }

        // кнопки "сохранить" и "отмена"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
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
        if ( ! empty($data['departmentid']) )
        {
            if ( ! $department = $this->dof->storage('departments')->get($data['departmentid']) )
            {// подразделение не существует
                $errors['departmentid'] = $this->dof->get_string('error:department_not_exists', $this->im_code());
            }
        }else
        {// подразделение не указано
            $errors['departmentid'] = $this->dof->get_string('error:department_not_set', $this->im_code());
        }
        
        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
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
        $message =& $mform->createElement('static', 'text', '', $message);
        // добавляем элемент в форму
        $mform->insertElementBefore($message, $elementname);
    }
    
    /** Получить список типов дня для select-списка
     * 
     * @return array
     */
    protected function get_types_day()
    {
        return array(
            'working'  => $this->dof->get_string('working', $this->im_code()),
            'holiday'  => $this->dof->get_string('holiday', $this->im_code()),
            'vacation' => $this->dof->get_string('vacation', $this->im_code()),
            'dayoff'   => $this->dof->get_string('dayoff', $this->im_code()),
            );
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
            return $this->dof->get_string('new_day',  $this->im_code());
        }
        //заголовок редактирования формы
        return $this->dof->get_string('edit_day', $this->im_code());
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
        // переменная, хранящая результат операции сохранения
        $result = true;
        // создаем объект для вставки в базу (или обновления записи)
        $dbobj  = new stdClass();
        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу просмотра шаблонов
            redirect($this->dof->url_im('schdays','/calendar.php',$add));
        }
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            
            $dbobj->date         = $formdata->date; // время начала урока (уже в секундах)
            if ( isset($formdata->date_edit) )
            {
                $dbobj->date     = $formdata->date_edit;
            }
            $dbobj->daynum       = $formdata->daynum; // день недели
            $dbobj->dayvar       = $formdata->dayvar; // тип недели
            $dbobj->type         = $formdata->type; // тип урока
            $dbobj->ageid        = $formdata->ageid; // id периода
            $dbobj->departmentid = $formdata->departmentid; // id подразделения 
            if ( $formdata->id )
            {// шаблон редактируется - обновляем запись
                $id = $dbobj->id = $formdata->id;
                $result = $result AND (bool)$this->dof->storage('schdays')->update($dbobj);
            }else
            {// шаблон добавляется - обновляем запись
                $id = $this->dof->storage('schdays')->save_day($dbobj);
                $result = $result AND (bool)$id;
            }
            if ( $result AND ! empty($formdata->create) AND 
                 $this->dof->storage('schdays')->get_field($id,'status') == 'plan' )
            {
            	redirect($this->dof->url_im('schdays','/process_events.php?type=create&id='.$id,$add));
            }
            if ( $result )
            {// если все успешно - делаем редирект
                redirect($this->dof->url_im('schdays','/view.php?id='.$id,$add));
            }
            // все отработано без ошибок, расписание создано
            $message = $this->dof->get_string('schedule_created', $this->im_code());
            return $this->style_result_message($message, true);

        }
    }
}


/** 
 * Календарь с возможностью массового редактирования дней
 *
 */
class dof_im_schdays_calendar_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var id подразделения 
     */
    protected $departmentid;

    /**
     * @var id подразделения
     */
    protected $age;
    
    /**
     * @var GET параметры
     */
    protected $addvars;
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->departmentid = $this->_customdata->departmentid;
        $this->age = $this->_customdata->age;
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden','sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','ageid', $this->age->id);
        $mform->setType('ageid', PARAM_INT);
        
        // Заголовок формы
        $mform->createElement('header','form_title', 'form_title');
        
        // Добавим легенду
        $mform->addElement('html', $this->get_legend());
        
        // Формируем календарь
        $this->get_calendar();
        
        
        // Массовая обработка
        $action = $mform->addElement('hierselect', 'action', $this->dof->get_string('form_days_action', 'schdays'),
                null, '<br />');
        $mainaction = array(
                '0' => $this->dof->get_string('form_days_action_select_action', 'schdays'),
                '1' => $this->dof->get_string('form_days_action_change_type', 'schdays'),
                '2' => $this->dof->get_string('form_days_action_fix_day', 'schdays')
        );
        $change_type = $this->dof->storage('schdays')->get_types();
        $subaction = array(
                '0' => array(),
                '1' => $change_type,
                '2' => array(
                        'fix' => $this->dof->get_string('form_days_action_fix_day_fix_days', 'schdays')
                )
        );
        $action->setOptions(array($mainaction, $subaction));
        $mform->setType('action', PARAM_ALPHANUM);
        
        // Кнопка подтверждения действий
        $this->add_action_buttons(false, $this->dof->get_string('form_days_action_submit', 'schdays'));
    }

    /** 
     * Проверка данных формы
     * 
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data,$files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Массив ошибок
        $errors = array();
        
        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /** 
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            // Дублируем пришедшие данные
            $ids = clone $formdata;
            // Очищаем от ненужных элементов
            unset ($ids->departmentid);
            unset ($ids->ageid);
            unset ($ids->action);
            unset ($ids->submitbutton);
            // Массив ошибок
            $errors = array();
            // Производим обработку
            switch ($formdata->action[0])
            {
                case '1':
                    // Смена типа дней
                    foreach ( $ids as $id => $val )
                    {
                        // Получим день
                        $day = $this->dof->storage('schdays')->get($id);
                        if ( empty($day) )
                        {// День не найден
                            $errors[$id] = $this->dof->get_string('error_day_not_found', 'schdays');
                            continue;
                        }
                        if ( ! $this->dof->storage('schdays')->is_access('edit', $id) )
                        {// Доступ запрещен
                            $errors[$id] = $this->dof->get_string('error_day_access_denied', 'schdays');
                            continue;
                        }
                        if ( $day->status != 'plan' )
                        {// День с таким статусом нельзя менять
                            $errors[$id] = $this->dof->get_string('error_day_status_not_supported', 'schdays');
                            continue;
                        }
                        $update = new stdClass();
                        $update->id = $id;
                        $update->type = $formdata->action[1];
                        if ( ! $this->dof->storage('schdays')->update($update) )
                        {
                            $errors[$id] = $this->dof->get_string('error_day_update_error', 'schdays');
                            continue;
                        }
                    }
                    break;
                case '2':
                        // Блокировка\разблокировка дней
                        foreach ( $ids as $id => $val )
                        {
                            // Получим день
                            $day = $this->dof->storage('schdays')->get($id);
                            if ( empty($day) )
                            {// День не найден
                                $errors[$id] = $this->dof->get_string('error_day_not_found', 'schdays');
                                continue;
                            }
                            if ( ! $this->dof->storage('schdays')->is_access('edit', $id) )
                            {// Доступ запрещен
                                $errors[$id] = $this->dof->get_string('error_day_access_denied', 'schdays');
                                continue;
                            }
                            
                            switch ( $formdata->action[1] )
                            {
                                case 'fix' : 
                                    $result = $this->dof->workflow('schdays')->change($id, 'fixed');
                                    if ( empty($result) )
                                    {// День нельзя заблокировать
                                        $errors[$id] = $this->dof->get_string('error_day_not_fixed', 'schdays');
                                    }
                                    break;
                                default :
                                    break;
                            }
                        }
                        break;
                default :
                    break; 
            }
            if ( empty($errors) )
            {// Ошибок нет
                $this->addvars['complete'] = 1;
                redirect( $this->dof->url_im('schdays', '/calendar.php', $this->addvars) );
            } else 
            {// Вернем ошибки
                return $errors;
            }
        }
    }
    
    private function get_calendar()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        $res = new stdClass();
        
        // Получим часовой пояс подразделения
        $timezone = $this->dof->storage('departments')->
            get_timezone($this->age->departmentid);
        // Получим интервалы периода
        $agestart = dof_usergetdate($this->age->begindate, $timezone);
        $ageend = dof_usergetdate($this->age->enddate, $timezone);
        
        // Начнем подсчет количества месяцев
        if ( $agestart['year'] === $ageend['year'] )
        {// Месяцы находятся в рамках одного года
            $countmonth = $ageend['mon'] - $agestart['mon'] + 1;
        } else
        {// Месяцы в разных годах
            $countmonth = 0;
            for ( $year = $agestart['year']; $year <= $ageend['year']; $year++ )
            {
                if ( $year == $agestart['year'] )
                {// Число месяцев в начальном году
                    $countmonth += 13 - $agestart['mon'];
                    continue;
                }
                if ( $year == $ageend['year'] )
                {// Число месяцев в начальном году
                    $countmonth += $ageend['mon'];
                    continue;
                }
                // Год целиком входит в интервал
                $countmonth += 12;
            }
        }
        
        // Установим смещение первого дня периода(номер дня)
        if ( isset($this->age->schstartdaynum) )
        {// Смещение передано в периоде
            $schstartdaynum = $this->age->schstartdaynum;
        } else
        {// Смещение по - умолчанию
            $schstartdaynum = 1;
        }
        
        // Чекбоксы для регистрации
        $checkboxes = [];
        
        // Распечатаем каждый месяц отдельно
        for ( $month = 0; $month < $countmonth; $month++ )
        {
            // Сформируем данные для получения месяца
            $currentmonth = ($agestart['mon'] + $month)%12;
            if ($currentmonth == 0)
            {// Текущий месяц - декабрь
                $currentmonth = 12;
            }
        
            // Флаги первого и последнего месяцев
            $firstmonth = false;
            $endmonth = false;
            // Сформируем начальную и конечную даты
            if ( $month == 0 )
            {// Текущий месяц - первый
                $firstmonth = true;
                $monthstartday = mktime(12, 0, 0, $agestart['mon'] + $month, $agestart['mday'], $agestart['year']);
            } else
            {
                $monthstartday = mktime(12, 0, 0, $agestart['mon'] + $month, 1, $agestart['year']);
            }
            if ( $month == $countmonth - 1 )
            {// Текущий месяц - последний
                $endmonth = true;
                $monthendday = mktime(12, 0, 0, $agestart['mon'] + $month, $ageend['mday'], $agestart['year']);
            } else
            {
                $monthendday = mktime(12, 0, 0, $agestart['mon'] + $month + 1, 0, $agestart['year']);
            }
        
            // Получим данные по месяцу
            list( $data, $schstartdaynum ) = $this->get_data_month(
                    $currentmonth,
                    $monthstartday,
                    $monthendday,
                    $schstartdaynum,
                    $firstmonth,
                    $endmonth
            );
            
            $mform->addElement('html',  "<h1 align='center'>$data->month</h1>");
            $mform->addElement('html',  "<table cellpadding = '5' cellspacing='1' border='5' width='100%' class='calendartable generaltable boxaligncenter fixedtable'>");
            $mform->addElement('html',  "<tr>");
            // Получим алиасы дней
            $aliases = $this->dof->storage('ages')->get_daynums_aliases($this->age->id);
            // Пустая первая ячейка в заголовке
            $mform->addElement('html',  "<th></th>");
            // Формируем заголовок
            for ( $th = 1; $th <= $this->age->schdays; $th++ )
            {
                $mform->addElement('html',  "<th>$aliases[$th]</th>");
            }
            $mform->addElement('html',  "</tr>");
            
            // Формируем строки календаря
            foreach ( $data->row as $row )
            {// Строка
                $mform->addElement('html',  "<tr>");
                if (isset($row->cell))
                {// Есть элементы в строке
                    // Первая ячейка с действиями над неделей
                    $mform->addElement('html',  "<td align='center'><div class='weekinfo'>");
                    $mform->addElement('html',  "<span style='font-size: large;'>$row->num</span>");
                    if ( isset($row->addweek) )
                    {// Есть кнопка добавления расписания на неделю
                        $mform->addElement('html',  "<div class='addweek'>$row->addweek</div>");
                    }
                    $mform->addElement('html',  "</div></td>");
                    foreach ( $row->cell as $cell )
                    {// Ячейка
                        $link = "<div class='daylink'>$cell->link</div>";
                        if ( $cell->daynum.$cell->dayvar == '' )
                        {
                            $dayinfo = '';
                        } else 
                        {
                            $dayinfo = "<div class='dayinfo'>$cell->daynum/$cell->dayvar</div>";
                        }
                        
                        $mform->addElement('html',  "<td align='center' class='$cell->class'>");
                        $mform->addElement('html',  "<div class='day'>$link$dayinfo</div>");
                        $mform->addElement('html',  "<div class='dayicon'>$cell->icon</div>");
                        if ( ! empty($cell->dayid) )
                        {// День есть
                            $mform->addElement('html',  "
                                <span class='emptycheckbox'>
                                    <input name='$cell->dayid' type='checkbox' value='1' id='id_$cell->dayid'>
                                    <label for='id_$cell->dayid'></label>
                                </span>");
                            $checkboxes[$cell->dayid] = $cell->dayid;
                        }
                        $mform->addElement('html',  "</td>");
                    }
                }
                $mform->addElement('html',  "</tr>");
            }
            
            $mform->addElement('html',  "</table>");
        }
        
        /*
         * Хак - Добавление чекбоксов для массовой обработки
         * 
         * Рендер формы ломает html, если внутрь таблицы календаря поместить 
         * чекбокс стандартными методами $mform->addElement, поэтому чекбоксы там 
         * генерируются вручную. 
         * Для получения данных по этим чекбоксам требуется их регистрация в форме
         * Ниже происхлдит регистрация всех чекбоксов формы массовой обработки
         */
        if ( ! empty($checkboxes) )
        {
            $group = [];
            foreach ( $checkboxes as $cellid )
            {
                $group[] = $mform->createElement(
                    'checkbox',
                    $cellid,
                    NULL,
                    '',
                    ['style' => 'display:none;']
                );
            }
            
            $mform->addGroup($group, 'hidden', '', '', FALSE);
        }
    }
    
    /**
     * Получить месяц
     * 
     * @param int $month - номер месяца
     * @param int $monthstartday - timestamp начала месяца
     * @param int $monthendday - timestamp конца месяца
     * @param int $schstartdaynum - смещение первого дня месяца
     * @param bool $firstmonth - первый месяц
     * @param bool $endmonth - последний месяц
     * 
     * @return list(array, int) - данные для шаблона и смещение
     */
    private function get_data_month($month, $monthstartday, $monthendday, $schstartdaynum, $firstmonth, $endmonth )
    {
        // Готовим данные
        $res = new stdClass();
        $res->row = array();
        $startday = dof_userdate($monthstartday,'%d');
        $endday = dof_userdate($monthendday,'%d');
        $flag = 1;
        $num = 1;

        // Первая строка месяца
        if ( $schstartdaynum <= $this->age->schdays )
        {// Указано валидное смещение первого дня, сформируем пустые ячейки
            for ( $j = -$schstartdaynum + 2; $j <= 0; $j++ )
            {
                $res->weeknum[$flag] = new stdClass;
                $res->weeknum[$flag]->num = $flag;
                $flag++;
                // Сформируем пустой день
                $res->row[$num]->cell[$j] = $this->get_data_empty_day();
            }
        } else 
        {// Смещение невалидно, исправляем
            $schstartdaynum = $schstartdaynum % $this->age->schdays;
            if ( $schstartdaynum == 0 )
            {// Нулевой день - это последний день недели
                $schstartdaynum = $this->age->schdays;
            }
        }

        // Первая учебная неделя
        if ( ! isset($res->row[$num]) OR ! is_object($res->row[$num]) )
        {
            $res->row[$num] = new stdClass();
        }
        
        $day = $monthstartday;
        // Конечная дата учебной недели
        $enddate = $day + DAYSECS * ( $this->age->schdays - $schstartdaynum );
        
        if ( $firstmonth || ( $schstartdaynum == 1 ) )
        {
            // Массив GET параметров для ссылки
            $add = array(
                    'ageid'     => $this->age->id,
                    'begindate' => $day,
                    'enddate'   => $enddate
            );
            $res->row[$num]->addweek = $this->dof->modlib('ig')->icon_plugin(
                    'create_events',
                    'im',
                    'schdays',
                    $this->dof->url_im('schdays','/autocreate_events.php?type=createweek',$add + $this->addvars),
                    array('title' => $this->dof->get_string('createweek_events', 'schdays'), 'width'=> '24px')
            );
        }
        
        
        
        // Вторая и далее строка
        for ( $i = $startday; $i <= $endday; $i++ )
        {
            if ( $flag > $this->age->schdays )
            {
                $flag = 1;
                $num++;
            }
            
            $day = mktime(12, 0, 0, $month, $i, dof_userdate($monthstartday,'%Y'));
            
            // Первая ячейка?
            if ( ! isset($res->row[$num]) OR !is_object($res->row[$num]) )
            {
                // Добавим ссылку на создание расписания на каждый учебный цикл
                // (неделю) в клетке с номером учебной недели (первый уже есть)
                $res->row[$num] = new stdClass();
                // Если последний цикл короче 7 рабочих дней
                if ( $day + DAYSECS * ($this->age->schdays - 1) >= $this->age->enddate )
                {
                    $timezone = $this->dof->storage('departments')->
                        get_timezone($this->age->departmentid);
                    $ageend = dof_usergetdate($this->age->enddate, $timezone);
                    $enddate = mktime(12, 0, 0, $ageend['mon'], $ageend['mday'], $ageend['year']);
                } else
                {
                    $enddate = $day + DAYSECS * ($this->age->schdays - 1);
                }
                
                $add = array('ageid'     => $this->age->id,
                             'begindate' => $day,
                             'enddate'   => $enddate);
                
                $res->row[$num]->addweek = $this->dof->modlib('ig')->icon_plugin('create_events','im','schdays',
                        $this->dof->url_im('schdays','/autocreate_events.php?type=createweek',$add + $this->addvars),
                        array('title'=>$this->dof->get_string('createweek_events', 'schdays'),
                              'width'=>'24px'));
            }
            $schday = $this->dof->storage('schdays')->get_day($this->age->id,$day,$this->departmentid);
            $res->row[$num]->cell[$i] = $this->get_data_full_day($day,$schday,$flag);
            $res->row[$num]->num = $num;
            $res->weeknum[$flag] = new stdClass;
            $res->weeknum[$flag]->num = $flag;
            $flag++;
        }
        $schstartdaynum = $flag;
        // Ячейки после конца месяца до заполнения таблицы
        for ( $j=$flag; $j<=$this->age->schdays; $j++)
        {
            $res->weeknum[$flag] = new stdClass;
            $res->weeknum[$flag]->num = $flag;
            $flag++;
            $res->row[$num]->afternullcell[$j] = $this->get_data_empty_day();
        }
        $res->month = dof_userdate($monthstartday,'%B');
        return array($res,$schstartdaynum);
    }
    
    /**
     * Сформировать объект пустого дня
     *
     * @return stdClass
     */
    private function get_data_empty_day()
    {
        $res = new stdClass();
        $res->link = '';
        $res->numday = '';
        $res->nameweekday = '';
        $res->daynum = '';
        $res->dayid = '';
        $res->dayvar = '';
        $res->icon = '';
        $res->class = 'nullday';
        return $res;
    }
    
    /**
     * Сформировать объект дня
     *
     * @param unknown $day
     * @param unknown $schday
     * @param unknown $i
     * @return stdClass
     */
    private function get_data_full_day($day, $schday, $i)
    {
        $res = new stdClass();
        $res->numday = dof_userdate($day,'%d');
        $res->nameweekday = dof_userdate($day,'%a');
        $res->daynum = $i;
        $res->dayid = 0;
        $res->icon = '';
        $res->link = $res->numday.'/'.$res->nameweekday;
        if ( $res->daynum == 0 )
        {
            $res->daynum = 7;
        }
        $res->dayvar = 0;
        $res->class = 'notcreated ';
        if ( ! empty($schday) )
        {
            $res->link = '<a href="'.$this->dof->url_im('schdays','/view.php?id='.
                    $schday->id,$this->addvars).'">'.$res->link.'</a>';
            $res->dayid = $schday->id;
            $res->icon = '';
            $res->class = $schday->type;
            if ( $schday->daynum != $i )
            {
                $res->class .= ' edunomatch';
            }
            if ( $schday->status == 'active' )
            {
                $res->icon = $this->dof->modlib('ig')->icon_plugin('schedule_create','im','schdays',false,
                        array('title'=>$this->dof->get_string('schedule_create', 'schdays'),
                                'width'=>'24px'));
                $res->class .= ' haveschedule';
            } else if ( $schday->status == 'draft' )
            {
                $res->icon = $this->dof->modlib('ig')->icon_plugin('schedule_draft','im','schdays',false,
                        array('title'=>$this->dof->get_string('schedule_draft', 'schdays'),
                                'width'=>'24px'));
                $res->class .= ' haveschedule';
            } else if ( $schday->status == 'completed' )
            {
                $res->icon = $this->dof->modlib('ig')->icon_plugin('schedule_completed','im','schdays',false,
                        array('title'=>$this->dof->get_string('schedule_completed', 'schdays'),
                                'width'=>'24px'));
                $res->class .= ' haveschedule';
            } if ( $schday->status == 'fixed' )
            {
                $res->icon = $this->dof->modlib('ig')->icon_plugin('schedule_fixed','im','schdays',false,
                        array('title'=>$this->dof->get_string('schedule_fixed', 'schdays'),
                                'width'=>'24px'));
                $res->class .= ' haveschedule';
            }
            $res->daynum = $schday->daynum;
            $res->dayvar = $schday->dayvar;
        } else
        {
            $this->addvars['date'] = $day;
            $res->link = '<a href="'.$this->dof->url_im('schdays','/edit.php?id=0',
                    $this->addvars).'">'.$res->link.'</a>';
        }
        if ( $day < time() )
        { // В 12 часов день считается наступившим
            $res->class .= ' occured';
        } else
        {
            $res->class .= ' notoccured';
        }
        if ( empty($this->departmentid) )
        {
            $res->link = $res->numday.'/'.$res->nameweekday;
        }
        return $res;
    }
    
    /**
     * Получить html-код легенды календаря
     * 
     * @return string - код легенды
     */
    private function get_legend()
    {
        // Заголовок
        $header = html_writer::div(
                $this->dof->get_string('calendar_legend_heading', 'schdays'), 
                'calendar_legend_heading'
        );
        
        // Объявляем таблицу
        $table = new stdClass();
        // Данные
        $table->data = array();
        
        // День не создан
        $table->data[] = array(
                html_writer::div('', 'notcreated'),
                $this->dof->get_string('calendar_legend_day_notcreated', 'schdays')
        );
        // Рабочий день
        $table->data[] = array(
                html_writer::div('', 'working'),
                $this->dof->get_string('calendar_legend_day_edumatch', 'schdays')
        );
        // Праздничный или каникулярный день
        $table->data[] = array(
                html_writer::div('', 'vacation'),
                $this->dof->get_string('calendar_legend_day_eduvacation', 'schdays')
        );
        // Выходной день
        $table->data[] = array(
                html_writer::div('', 'dayoff'),
                $this->dof->get_string('calendar_legend_day_edudayoff', 'schdays')
        );
        // Перенесенный день
        $table->data[] = array(
                html_writer::div('', 'edunomatch'),
                $this->dof->get_string('calendar_legend_day_edunomatch', 'schdays')
        );
        $return = $header.$this->dof->modlib('widgets')->print_table($table, true);
        
        return html_writer::div($return, 'calendar_legend');
    }
}

/** 
 * Форма создания расписания
 * 
 * @deprecated - Форма не используется. 
 */
class dof_im_schdays_create_event_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    /**
     * @var int - id подразделения в таблице departments, для которого будет просматриваться расписание
     */
    protected $departmentid;
    protected $age;
    
    protected function im_code()
    {
        return 'schdays';
    }
    
    public function definition()
    {
        // $mform     = $this->_form;
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        $mform->addElement('hidden','departmentid', $this->_customdata->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        $this->departmentid =  $this->_customdata->departmentid;
        $mform->addElement('hidden','ageid', $this->_customdata->ageid);
        $mform->setType('ageid', PARAM_INT);
        $this->age = $this->dof->storage('ages')->get($this->_customdata->ageid);
        // заголовок
        $mform->addElement('header', 'header', $this->dof->get_string('create_event', $this->im_code()));
        // учебный период
        $mform->addElement('static', 'agename', $this->dof->get_string('age', $this->im_code()));
        $mform->setType('ageid', PARAM_INT);
        $mform->setDefault('agename', $this->age->name);
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
        // день недели
        $mform->addElement('select', 'daynum', $this->dof->get_string('daynum', $this->im_code()), 
                           $this->dof->storage('ages')->get_list_daynums($this->age));
        $mform->setType('daynum', PARAM_INT);
        // галочка обновить распиание
        $mform->addElement('checkbox', 'update_sch', '', $this->dof->get_string('update_schedule',$this->im_code()));
        
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
        
        if ( time() > ($day->date - DAYSECS / 2)  )
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
                $dbobj = new stdClass;
                $dbobj->date         = $formdata->date; // время начала урока (уже в секундах)
                $dbobj->daynum       = $formdata->daynum; // день недели
                $dbobj->dayvar       = $formdata->dayvar; // тип недели
                $dbobj->type         = $formdata->type; // тип урока
                $dbobj->ageid        = $formdata->ageid; // id периода
                $dbobj->departmentid = $formdata->departmentid; // id подразделения 
                if ( ! $dayid = $this->dof->storage('schdays')->save_day($dbobj) )
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
                    $errordays[] = $this->get_templates_errors($templateids);
                }
            }
        }
        if ( isset($formdata->button1) )
        {// создаем расписание на неделю
            // получим дни недели
            $days = explode(',',$this->age->schedudays);
            // для каждого дня недели создаем расписание
            $createdays = array();
            // создаем массив для ошибок, которые могут возникнуть при сохранении каждго дня
            $errordays  = array();
            // если не удалось создать расписание на 1 день то не создаем на вСЮ неделю
            // потому повторяем перебор по дням, дабы исключить 
            // создание распиписания для недели хоть для одного дня
            foreach ( $days as $num=>$day )
            {
                $message = '';
                // расчитываем дату недели
                $date = $formdata->date + (($num - 1) * DAYSECS);
                if ( empty($formdata->update_sch) AND 
                     $this->dof->storage('schdays')->is_exists_day
                     ($formdata->ageid,$date,$this->departmentid) )
                {// создаем новый день. Если день существует -  - НЕЛЬЗЯ создавать и на ВСЮ неделю
                    $message = $day.': '.$this->dof->get_string('error:day_already_exists', $this->im_code());
                    return $this->style_result_message($message, false);
                }else 
                {// нужно удалить старый день
                    if ( isset($formdata->update_sch) )
                    {//надо удалить день
                        if ( ! $this->delete_entire_day($formdata, $num) )
                        {// не удалось удалить старый день перед созданием расписания - НЕЛЬЗЯ создавать и на ВСЮ неделю
                            $message = $day.': '.$this->dof->get_string('error:cannot_delete_day', $this->im_code());
                            return $this->style_result_message($message, false);
                        }
                    }
                    
                }
                
            }
            // ошибок НЕТ - СОЗДАЁМ
            foreach ( $days as $num=>$day )
            {
                $message = '';
                // расчитываем дату недели
                $date = $formdata->date + (($num - 1) * DAYSECS);
                $dbobj = new stdClass;
                $dbobj->date         = $date; // время начала урока (уже в секундах)
                $dbobj->daynum       = $num; // день недели
                $dbobj->dayvar       = $formdata->dayvar; // тип недели
                $dbobj->type         = $formdata->type; // тип урока
                $dbobj->ageid        = $formdata->ageid; // id периода
                $dbobj->departmentid = $formdata->departmentid; // id подразделения 
                if ( ! $dayid = $this->dof->storage('schdays')->save_day($dbobj) )
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
                     $formdata->ageid,$num,$formdata->dayvar,$dayid,$formdata->departmentid,$implied);
                $createdays[$day] = implode(',',array_keys($templateids));
                if ( ! empty($templateids) )
                {// при сохранении шаблонов возникли ошибки
                    $message = $this->style_result_message($day.': <br/>', false);
                    $errordays[] = $message.$this->get_templates_errors($templateids);
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
    
    /** Удалить день вместе с событиями
     * @param array $formdata - данные, пришедшие из формы
     * 
     * @return bool
     */
    protected function delete_entire_day($formdata, $num=null)
    {
        if ( is_null($num) )
        {// если нам не передали номер дня - то берем его из формы
            $num  = $formdata->daynum;
            $date = $formdata->date;
        }else 
        {// расчитываем дату недели
            $date = $formdata->date + (($num - 1) * DAYSECS);
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
             departmentid={$formdata->departmentid} AND status IN ('active','holiday','fixed')");
                    
        if ( ! $schdays )
        {// ничего не нужно удалять - таких дней нет
            return true;
        }
        //print_object($schdays);die;
        // @todo содержимое этого foreach нужно заменить на вызов функции
        // delete_entire_day в storage/schdays
        foreach ( $schdays as $schday )
        {// перебираем все одинаковые дни
            $delevents = true;
            $conds = new stdClass();
            $conds->dayid = $schday->id;
            $sql = $this->dof->storage('schevents')->get_select_listing($conds);
            if ( $events = $this->dof->storage('schevents')->get_records_select($sql) )
            {
                foreach($events as $event)
                {// для каждой КТ удалим ее вместе с событием
                    if ( ! $this->dof->storage('schevents')->cancel_event($event->id, true) ) 
                    {// не удалось удалить событие
                        // нельзя будет удалить и ДЕНЬ
                        $delevents = false;
                    }
                }
            }
                        
            // если все прошло успешно - удалить сам день
            if ( $delevents )
            {// все события удалены
                if ( ! $this->dof->storage('schdays')->delete_day($schday->id) )
                {// попытались удалить день, но не получилось
                    return false;
                }
            }else 
            {// не смогли удалить ВСЕ дня с этой датой
                return false;    
            }    
        }
        
        // день успешно удален
        return true;
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
            $date_begin = $date - (DAYSECS / 2) + $template->begin;
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


/** Форма создания расписания
 * 
 */
class dof_im_schdays_auto_create_days_form extends dof_modlib_widgets_form
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
        return 'schdays';
    }
    
    protected function storage_code()
    {
        return 'schdays';
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
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // устанавливаем все скрытые поля 
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', $this->_customdata->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        $this->departmentid =  $this->_customdata->departmentid;
        $mform->addElement('hidden','ageid', $this->_customdata->ageid);
        $mform->setType('ageid', PARAM_INT);
        $this->age = $this->dof->storage('ages')->get($this->_customdata->ageid);
        // учебный период
        $mform->addElement('static', 'agename', $this->dof->get_string('age', $this->im_code()));
        $mform->setType('ageid', PARAM_INT);
        $mform->setDefault('agename', $this->age->name);
        // Подразделение
        $mform->addElement('static', 'department', $this->dof->get_string('department', $this->im_code()));
        $mform->setDefault('department', 
                           $this->dof->storage('departments')->get_field($this->departmentid,'name').' ['.
                           $this->dof->storage('departments')->get_field($this->departmentid,'code').']');
        // выбор даты
        // объявляем массив для установки значений по умолчанию
        $dateoptions = array();
        
        // устанавливаем год, с которого начинать вывод списка
        $dateoptions['startyear'] = $this->dof->storage('persons')->get_userdate(time(),"%Y")-12;
        // устанавливаем год, которым заканчивается список
        $dateoptions['stopyear']  = $this->dof->storage('persons')->get_userdate(time(),"%Y")+12;
        // убираем галочку, делающую возможным отключение этого поля
        $dateoptions['optional']  = false;
        $mform->addElement('date_selector', 'date', $this->dof->get_string('date', $this->im_code()).':', $dateoptions);        
        $mform->setDefault('date', $this->age->begindate);

        // день недели
        $mform->addElement('select', 'daynum', $this->dof->get_string('daynum', $this->im_code()), 
                           $this->dof->storage('ages')->get_list_daynums($this->age));
        $mform->setType('daynum', PARAM_INT);
        // учебная неделя
        $mform->addElement('select', 'dayvar', $this->dof->get_string('dayvar', $this->im_code()), 
                array('1' => $this->dof->get_string('odd',$this->im_code()) ,
                      '2' => $this->dof->get_string('event',$this->im_code())));   
        $mform->setType('dayvar', PARAM_TEXT);
        $mform->setDefault('dayvar', 1);
        
        // тип урока
        $lessontypes = $this->get_types_day();
        $mform->addElement('select', 'type', $this->dof->get_string('type', $this->im_code()),
                           $lessontypes);
        $mform->setType('type', PARAM_ALPHANUM);
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

        // кнопки "сохранить" и "отмена"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
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
        if ( ! empty($data['departmentid']) )
        {
            if ( ! $department = $this->dof->storage('departments')->get($data['departmentid']) )
            {// подразделение не существует
                $errors['departmentid'] = $this->dof->get_string('error:department_not_exists', $this->im_code());
            }
        }else
        {// подразделение не указано
            $errors['departmentid'] = $this->dof->get_string('error:department_not_set', $this->im_code());
        }
        
        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        
        // Возвращаем ошибки, если они есть
        return $errors;
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
        $message =& $mform->createElement('static', 'text', '', $message);
        // добавляем элемент в форму
        $mform->insertElementBefore($message, $elementname);
    }
    
    /** Получить список типов дня для select-списка
     * 
     * @return array
     */
    protected function get_types_day()
    {
        return array(
            'working'  => $this->dof->get_string('working', $this->im_code()),
            'holiday'  => $this->dof->get_string('holiday', $this->im_code()),
            'vacation' => $this->dof->get_string('vacation', $this->im_code()),
            'dayoff'   => $this->dof->get_string('dayoff', $this->im_code()),
            );
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
            return $this->dof->get_string('new_day',  $this->im_code());
        }
        //заголовок редактирования формы
        return $this->dof->get_string('edit_day', $this->im_code());
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
        // переменная, хранящая результат операции сохранения
        $result = true;
        // создаем объект для вставки в базу (или обновления записи)
        $dbobj  = new stdClass();
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            if ( $this->is_cancelled() OR isset($formdata->buttonar['cancel']) )
            {//ввод данных отменен - возвращаем на страницу просмотра шаблонов
                redirect($this->dof->url_im('schdays','/calendar.php',$add));
            }
            $result = $this->dof->im('schdays')->auto_create_days($formdata->ageid,
                    $formdata->departmentid, $formdata->date, $formdata->daynum, $formdata->dayvar, $formdata->type);
            if ( $result )
            {// если все успешно - делаем редирект
                redirect($this->dof->url_im('schdays','/calendar.php',$add));
            }
            // все отработано без ошибок, расписание создано
            $message = $this->dof->get_string('schedule_created', $this->im_code());
            return $this->style_result_message($message, true);

        }
    }
}

?>