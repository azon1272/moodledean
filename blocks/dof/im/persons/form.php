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

// Подключаем библиотеки
require_once('lib.php');

global $DOF;
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** 
 * Форма массового редактирования временной зоны персон подразделения
 */
class dof_im_persons_edit_timezone extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $depid - ID выбранного подразделения
     */
    protected $depid = 0;
    
    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->dof  = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        
        // Заголовок формы
        $mform->addElement('header','headername', '');
        
        // Примечение
        $mform->addElement(
            'static', 
            'staticname', 
            '', 
            $this->dof->get_string('notice_ans','persons')
        );
        
        // Выбор подразделения
        $departments = $this->dof->storage('departments')->
            departments_list_subordinated(null, 0, null, true);
        $mform->addElement(
            'select',
            'depart',
            $this->dof->get_string('select_depart','persons'),
            $departments
        );
        $mform->setDefault('depart', $this->depid);
        
        // Выбор временной зоны
        $timezones = dof_get_list_of_timezones();
        $mform->addElement(
            'select',
            'timezone',
            $this->dof->get_string('select_time_zone','persons'),
            $timezones
        );
        $mform->setDefault('timezone','99');
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'save',
            $this->dof->modlib('ig')->igs('save')
        );
        $mform->addGroup($group, 'buttonar', '', '', false);
    }    
    
    /**
     * Валидация данных формы
     */
    function validation($data, $files)
    {
        $error = [];
        if ( empty($data['depart']) )
        {
            $error['depart'] = $this->dof->get_string('select_depart', 'persons');
        }
        return $error;
    }   
    
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @param array $addvars - Массив с доп данными(подразделение)
     */
    function process($addvars)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Сохранение данных
		    dof_hugeprocess();
            $limitfrom = 0;
            $flag = true;
            while ( 
                $list = $this->dof->storage('persons')->get_records(
                    ['departmentid' => $formdata->depart, 'sync2moodle' => '1'],
                    '',
                    'id, mdluser', 
                    $limitfrom, 
                    100
                ) 
            )
            {
                $limitfrom += 100;
                
                // Смена часового пояса
                foreach ( $list as $obj )
                {
                    $objmdluser = new stdClass();
                    $obj->id = $obj->mdluser;
                    $obj->timezone = $formdata->timezone;
                    if ( ! $this->dof->modlib('ama')->user(false)->is_exists($obj->mdluser) )
                    {// Пользователь не найден
                        continue;
                    }
                    $flag = ( $flag & (bool)$this->dof->modlib('ama')->user($obj->mdluser)->update($obj) );
                } 
            }
            
    		if ( $flag )
            {
                // Добавление сообщения
                $this->dof->messages->add($this->dof->modlib('ig')->igs('data_save_success'), 'message');
            } else 
            {// Ошибка во время обработки
                // Добавление сообщения
                $this->dof->messages->add($this->dof->modlib('ig')->igs('data_save_failure'), 'error');
            } 
		} 
		return;
    }
    
}

/*
 * Класс формы для сохранения данных персоны
 * 
 * Для загрузки персон в форму необходимо заполнить массив $customdata->persons
 * в формате ['произвольный код персоны' => 'ID персоны']. Для создания новой персоны
 * необходимо вместо ID персоны установить 0
 * 
 * Результатом обработки будет либо ошибка, в таком случае пользователь останется 
 * на странице обработки формы, либо редирект на $customdata->returnurl с дополнительными
 * GET-параметрами в виде 'Код персоны'='ID персоны'.
 * 
 * Блокировка полей производится путем заполнения $customdata->freezepersons.
 * Ключами массива являются коды персон, а значением - массив блокируемых полей персоны
 * (Если массив пуст - юлокируются все поля)
 * 
 * Изменение заголовков форм сохранения персон производится путем заполнения
 * массива $customdata->personlabels. Формат масива ['Код персоны' => 'Заголовок формы']
 */
class dof_im_persons_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * GET параметры для ссылки
     *
     * @var array
     */
    protected $addvars = [];

    /**
     * URL для возврата после обработки формы
     *
     * @var string
     */
    protected $returnurl = null;

    /**
     * ID текущей персоны
     *
     * @var string
     */
    protected $currentpersoncode = null;

    /**
     * Пул персон
     * 
     * @var array
     */
    protected $persons = [
        'pool' => [],
        'added' => []
    ];
    
    /**
     * Заголовки для зарегистрированных персон
     *
     * @var array
     */
    protected $personlabels = [];
    
    /**
     * Список заблокированных полей
     *
     * @var array
     */
    protected $freezepersons = [];
    
    /**
     * Поля пользователей при обработке
     * 
     * @var array
     */
    private $personfields = [];
    
    /**
     * Массив имен элементов формы
     * 
     * @var array
     */
    protected $elements = [];
    
    /**
     * Список типов адресов для отображения в форме
     * 
     * @var array
     */
    protected $addresstypes = [
        ''                => 'passportaddrid',
        'addressid_'      => 'addressid',
        'birthaddressid_' => 'birthaddressid'
    ];
    
    /**
     * Получить идентиифкатор для блокировки формы
     *
     * @see dof_modlib_widgets_form::get_freezeid()
     */
    protected function get_freezeid()
    {
        return __CLASS__ . "_freeze_{$this->currentpersoncode}";
    }
    
    /**
     * Инициализация базовых данных формы
     */
    protected function init()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        
        if ( isset($this->_customdata->addvars) )
        {// Установка текущих GET-параметров страницы
            $this->addvars = (array)$this->_customdata->addvars;
        }
        // Нормализация GET-параметров
        if ( ! isset($this->addvars['departmentid']) )
        {// Установка текущего подразделения
            $this->addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Установка URL возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }
        
        // Добавление персон в форму
        if ( isset($this->_customdata->persons) && ! empty($this->_customdata->persons) )
        {// Добавление персон в форму
            $persons = (array)$this->_customdata->persons;
            foreach ( $persons as $personcode => $personid )
            {
                if ( (int)$personid > 0 )
                {// Передана персона на редактирование
                    $this->persons['pool'][(string)$personcode] = (int)$personid;
                } else
                {// Передана персона для создания
                    $this->persons['pool'][(string)$personcode] = 0;
                }
            }
        }
        // Установка текущей сохраняемой персоны
        if ( isset($this->_customdata->personid) )
        {// Добавление персоны
            if ( (int)$this->_customdata->personid > 0 )
            {// Передана персона на редактирование
                $this->persons['pool'][(string)$this->_customdata->personid] = (int)$this->_customdata->personid;
            } else
            {// Передана персона для создания
                $this->persons['pool']['newperson'] = 0;
            }
        }
        
        // Добавление заголовков форм персон
        if ( isset($this->_customdata->personlabels) && ! empty($this->_customdata->personlabels) )
        {// Добавление персон в форму
            $this->personlabels = (array)$this->_customdata->personlabels;
        }
        
        // Блокировка полей персоны
        if ( isset($this->_customdata->freezepersons) && ! empty($this->_customdata->freezepersons) )
        {// Добавление списка заблокированных полей
            $this->freezepersons = (array)$this->_customdata->freezepersons;
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', __CLASS__.'departmentid', $this->addvars['departmentid']);
        $mform->setType(__CLASS__.'departmentid', PARAM_INT);
        $mform->addElement('hidden', __CLASS__.'returnurl', $this->returnurl);
        $mform->setType(__CLASS__.'returnurl', PARAM_URL);
    }

    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Базовая инициализация формы
        $this->init();
        
        // Добавление форм персон из пула
        foreach ( $this->persons['pool'] as $personcode => $personid )
        {
            // Отобразить форму сохранения для существующей персоны
            $this->show_person_form($personcode);
        }

        // Добавление полей после персон
        $this->add_fields_after_persons();
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'save', 
            $this->dof->modlib('ig')->igs('save')
        );
        $mform->addGroup($group, 'buttons', '', '', false);
        
        // Отделить кнопки действий от заголовка
        $mform->closeHeaderBefore('buttons');
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Добавление полей в форму после групп персон
     * 
     * @return void
     */
    protected function add_fields_after_persons()
    {
    }
    
    /**
     * Отобразить форму персоны
     *
     * @param string $code - Код персоны в пуле
     *
     * @return void
     */
    protected function show_person_form($code)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
    
        // Фиксация ID текущей выбранной персоны
        $this->set_personcode($code);
    
        if ( $this->currentpersoncode === null )
        {// Персона не установлена
            return;
        }
        
        // Получение ID текущей персоны
        $personid = $this->persons['pool'][$this->currentpersoncode];
        
        // Получаем часовой пояс подразделения, к которому относится период
        $timezone = $this->dof->storage('departments')->get_field($this->addvars['departmentid'], 'zone');
        if ( $timezone === false )
        {
            $timezone = 99;
        }
        // Заголовок формы персоны
        $personheader = $this->dof->get_string('person', 'persons');
        if ( isset($this->personlabels[$this->currentpersoncode]) )
        {// Найден дополнительный заголовок
            $personheader .= ' ['.$this->personlabels[$this->currentpersoncode].']';
        }
        $mform->addElement(
            'header',
            $this->get_elprefix('stheader'), 
            $personheader
        );
            
        // Добавление поля для связи код => id
        $mform->addElement('hidden', $this->get_elprefix('id'), $personid);
        $mform->setType($this->get_elprefix('id'), PARAM_INT);
            
        // Чекбокс с блокировкой полей отчества
        $mform->addElement(
            'checkbox',
            $this->get_elprefix('havemiddlename'),
            '',
            $this->dof->get_string('havemiddlename', 'persons')
        );
        $mform->setDefault($this->get_elprefix('havemiddlename'), false);
    
        // Сформировать группы полей имени персоны
        $this->show_name($this->dof->get_string('currentname', 'persons'), '', true);
        $this->show_name($this->dof->get_string('oldname', 'persons').' 1', 'n1_', false);
        $this->show_name($this->dof->get_string('oldname', 'persons').' 2', 'n2_', false);
    
        // Дата рождения
        $options = [];
        $options['startyear'] = 1930;
        $options['stopyear']  = dof_userdate(time(), '%Y', $timezone);
        $options['optional']  = false;
        $options['timezone'] = $timezone;
        $endopt['hours'] = 12;
        $endopt['minutes'] = 00;
        $mform->addElement('dof_date_selector', $this->get_elprefix('dateofbirth'), $this->dof->get_string('dateofbirth', 'sel'), $options);
    
        // Пол
        $displaylist = [];
        $displaylist['unknown'] = $this->dof->get_string('unknown','persons');
        $displaylist['male'] = $this->dof->get_string('male', 'sel');
        $displaylist['female'] = $this->dof->get_string('female', 'sel');
        $mform->addElement('select', $this->get_elprefix('gender'), $this->dof->get_string('gender', 'sel').':', $displaylist);
        $mform->setType($this->get_elprefix('gender'), PARAM_TEXT);
        $mform->addRule($this->get_elprefix('gender'),$this->dof->get_string('error_gender', 'persons'), 'required',null,'client');
        // Email
        $mform->addElement('text', $this->get_elprefix('email'), $this->dof->get_string('email','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('email'), PARAM_TEXT);
        $mform->addRule($this->get_elprefix('email'),$this->dof->get_string('error_emailreq', 'persons'), 'required',null,'client');
        // дополнительные email
        $mform->addElement('text', $this->get_elprefix('emailadd1'), $this->dof->get_string('emailadd','persons').':', 'size="20"');
        $mform->setType($this->get_elprefix('emailadd1'), PARAM_TEXT);
        $mform->addElement('text', $this->get_elprefix('emailadd2'), $this->dof->get_string('emailadd','persons').':', 'size="20"');
        $mform->setType($this->get_elprefix('emailadd2'), PARAM_TEXT);
        
        // Гражданство
        $mform->addElement('text', $this->get_elprefix('citizenship'), $this->dof->get_string('citizenship','persons').':', 'maxlength="5" size="20"');
        $mform->setType($this->get_elprefix('citizenship'), PARAM_TEXT);
        
        // Телефоны
        $mform->addElement('dof_telephone', $this->get_elprefix('phonehome'), $this->dof->get_string('phonehome','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('phonehome'), PARAM_TEXT);
        $mform->addElement('dof_telephone', $this->get_elprefix('phonework'), $this->dof->get_string('phonework','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('phonework'), PARAM_TEXT);
        $mform->addElement('dof_telephone', $this->get_elprefix('phonecell'), $this->dof->get_string('phonecell','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('phonecell'), PARAM_TEXT);
        
        // Дополнительные телефоны
        $mform->addElement('dof_telephone', $this->get_elprefix('phoneadd1'), $this->dof->get_string('phoneadd','persons').':', 'size="20"');
        $mform->setType($this->get_elprefix('phoneadd1'), PARAM_TEXT);
        $mform->addElement('dof_telephone', $this->get_elprefix('phoneadd2'), $this->dof->get_string('phoneadd','persons').':', 'size="20"');
        $mform->setType($this->get_elprefix('phoneadd2'), PARAM_TEXT);
        $mform->addElement('dof_telephone', $this->get_elprefix('phoneadd3'), $this->dof->get_string('phoneadd','persons').':', 'size="20"');
        $mform->setType($this->get_elprefix('phoneadd3'), PARAM_TEXT);
        
        // Характеристика
        $mform->addElement('textarea', $this->get_elprefix('about'), $this->dof->get_string('about','persons').':', 'wrap="virtual" rows="20" cols="50"');
        $mform->setType($this->get_elprefix('about'), PARAM_NOTAGS);
        
        // Skype
        $mform->addElement('text', $this->get_elprefix('skype'), $this->dof->get_string('skype','persons').':', 'size="20"');
        $mform->setType($this->get_elprefix('skype'), PARAM_TEXT);
    
        // Удостоверение личности
        $pass = $this->dof->modlib('refbook')->pasport_type();
        $pass['0'] = $this->dof->get_string('nonepasport', 'sel');
        ksort($pass);
        $mform->addElement('select', $this->get_elprefix('passtypeid'), $this->dof->get_string('passtypeid', 'sel').':', $pass);
        $mform->setType($this->get_elprefix('passtypeid'), PARAM_TEXT);
        $mform->addElement('text', $this->get_elprefix('passportserial'), $this->dof->get_string('passportserial','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('passportserial'), PARAM_TEXT);
        $mform->disabledIf($this->get_elprefix('passportserial'), $this->get_elprefix('passtypeid'),'eq','0');
        $mform->addElement('text', $this->get_elprefix('passportnum'), $this->dof->get_string('passportnum','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('passportnum'), PARAM_TEXT);
        $mform->disabledIf($this->get_elprefix('passportnum'), $this->get_elprefix('passtypeid'),'eq','0');
        
        $options = [];
        $options['startyear'] = 1930;
        $options['stopyear']  = dof_userdate(time(), '%Y', $timezone);
        $options['optional']  = false;
        $options['timezone'] = $timezone;
        $endopt['hours'] = 12;
        $endopt['minutes'] = 00;
        $mform->addElement('dof_date_selector', $this->get_elprefix('passportdate'), $this->dof->get_string('passportdate', 'sel').':', $options);
        $mform->disabledIf($this->get_elprefix('passportdate'), $this->get_elprefix('passtypeid'),'eq','0');
        $mform->addElement('text', $this->get_elprefix('passportem'),$this->dof->get_string('passportem','sel').':', 'size="20"');
        $mform->setType($this->get_elprefix('passportem'), PARAM_TEXT);
        $mform->disabledIf($this->get_elprefix('passportem'), $this->get_elprefix('passtypeid'),'eq','0');
        
        // Адреса регистрации, фактического проживания и рождения
        $this->show_address($this->dof->get_string('passportaddrid', 'persons'), '', true);
        $this->show_address($this->dof->get_string('addressid', 'persons'),      'addressid_');
        $this->show_address($this->dof->get_string('birthaddressid', 'persons'), 'birthaddressid_');
        
        // Заполнение информации о персоне закончено
        $mform->closeHeaderBefore($this->get_elprefix('departmentid')); 
        
        // Подразделение персоны
        $mform->addElement(
            'select', 
            $this->get_elprefix('departmentid'), 
            $this->dof->get_string('department','agroups').':', 
            $this->get_departments_list()
        );
        $mform->setType($this->get_elprefix('departmentid'), PARAM_INT);
        
        // Поля синхронизации с Moodle
        $sync = [];
        $sync[] = $mform->createElement(
            'radio', 
            $this->get_elprefix('sync2moodle'), 
            null, 
            $this->dof->modlib('ig')->igs('yes'), 
            1
        );
        $sync[] = $mform->createElement(
            'radio', 
            $this->get_elprefix('sync2moodle'), 
            null, 
            $this->dof->modlib('ig')->igs('no'),
            0
        );
        $mform->addGroup($sync, $this->get_elprefix('sync'), $this->dof->get_string('sync2moodle', 'sel'), "<br/>", false);
        $mform->addElement('text', $this->get_elprefix('mdluser'), $this->dof->get_string('moodleuser','sel').':');
        $mform->setType($this->get_elprefix('mdluser'), PARAM_INT);
        $mform->setDefault($this->get_elprefix('mdluser'), 0);

        // Возможность редактирования полей синхронизации
        $caneditsunc = $this->dof->storage('persons')->
            is_access('edit:sync2moodle', $personid, null, $this->addvars['departmentid']);
        if ( ! $caneditsunc )
        {// Пользователю запрещено редактирование полей синхронизации
            $mform->freeze([$this->get_elprefix('sync'), $this->get_elprefix('mdluser')]);
        }
        
        // Часовой пояс
        $canedittimezone = $this->dof->storage('persons')->
            is_access('edit_timezone', $personid, null, $this->addvars['departmentid']);
        if ( $canedittimezone && ! empty($personid) && $this->dof->storage('persons')->get_field($personid, 'sync2moodle') )
        {
            // Список часовых зон
            $UTC = dof_get_list_of_timezones();
            // Добавление поля выбора
            $mform->addElement(
                'select', 
                $this->get_elprefix('timezone'), 
                $this->dof->get_string('time_zone','persons').':',
                $UTC
            );
            $mform->disabledIf($this->get_elprefix('timezone'), $this->get_elprefix('sync2moodle'),'eq','0');
            
            // Установка значения поля
            if ( $this->dof->storage('persons')->get_field($personid, 'sync2moodle') )
            {// Персона синхронизировани с Moodle
                // Получение временной зоны персоны
                $person = $this->dof->storage('persons')->get($personid);
                if ( $this->dof->modlib('ama')->user(false)->is_exists($person->mdluser) )
                {
                    $mdluser = $this->dof->modlib('ama')->user($person->mdluser)->get();
                    $mform->setDefault($this->get_elprefix('timezone'), $mdluser->timezone);
                } else
                {// нет - по умолчанию на время на сервере
                    $mform->setDefault($this->get_elprefix('timezone'), '99');
                }
            } else
            {
                $mform->setDefault($this->get_elprefix('timezone'), '99');
            }
        }
        
        // Добавление дополниетльных полей для персон
        $this->add_person_fields($this->currentpersoncode);
        
        // Постобработка формы персоны
        $this->add_person($this->currentpersoncode);
    }
    
    /**
     * Добавление дополнительных полей для персон
     * 
     * @param string $personcode - Код персоны
     */
    protected function add_person_fields($personcode)
    {
    }

    /**
     * Формирование полей имени персоны
     *
     * @param string $header - Заголовок группы полей
     * @param string $prefix - Префикс полей
     *
     * @param bool $required требуется ли js-проверка заполнения полей lastname, firstname
     */
    protected function show_name($header = null, $prefix = '', $required = true)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
    
        if ( empty($header) )
        {// Заголовок по-умолчанию
            $header = $this->dof->get_string('currentname', 'persons');
        }
        $header = "<b>$header</b>";
        $mform->addElement('static', $this->get_elprefix('description'), '', $header);
    
        // ФИО
        $mform->addElement(
            'text',
            $this->get_elprefix($prefix.'lastname'),
            $this->dof->get_string('lastname','sel').':'
            );
        $mform->addElement(
            'text',
            $this->get_elprefix($prefix.'firstname'),
            $this->dof->get_string('firstname','sel').':'
            );
        $mform->addElement(
            'text',
            $this->get_elprefix($prefix.'middlename'),
            $this->dof->get_string('middlename','sel').':'
            );
        $mform->setType($this->get_elprefix($prefix.'lastname'), PARAM_TEXT);
        $mform->setType($this->get_elprefix($prefix.'firstname'), PARAM_TEXT);
        $mform->setType($this->get_elprefix($prefix.'middlename'), PARAM_TEXT);
    
        if ( $required )
        {// Требуется валидация полей
            $mform->addRule(
                $this->get_elprefix($prefix.'firstname'),
                $this->dof->get_string('error_firstnamereq', 'persons'),
                'required',
                null,
                'client'
            );
            $mform->addRule(
                $this->get_elprefix($prefix.'lastname'),
                $this->dof->get_string('error_lastnamereq', 'persons'),
                'required',
                null,
                'client'
            );
        }
        $mform->disabledIf(
            $this->get_elprefix("{$prefix}middlename"),
            $this->get_elprefix('havemiddlename'),
            'checked'
        );
    }
    
    /**
     * Отобразить группу полей с данными о проживании
     *
     * @param string $header - Заголовок группы полей
     * @param string $prefix - Префикс полей ( passportaddrid, addressid, birthaddressid )
     * @param bool $required - Обязательный блок
     */
    protected function show_address($header = null, $prefix = '', $required = false)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
    
        // Формирование заголовка блока
        if ( empty($header) )
        {// Заголовок по-умолчанию
            $header = $this->dof->get_string('passportaddrid', 'persons');
        }
        $mform->addElement('html', dof_html_writer::tag('h4', $header));
    
        if ( ! empty($prefix) )
        {// Установка возможности копирования информации из основного блока
    
            $mform->addElement(
                'checkbox', 
                $this->get_elprefix($prefix.'copy'), 
                '', 
                $this->dof->get_string('copyfromaddr', 'persons')
            );
            $mform->setDefault($this->get_elprefix($prefix.'copy'), false);
            
            // Блокировка полей, при включенном копировании
            $fields = [
                'country', 
                'postalcode', 
                'county',
                'city', 
                'streetname', 
                'streettype', 
                'number',
                'gate', 
                'floor', 
                'apartment', 
                'latitude', 
                'longitude'
            ];
            foreach ($fields as $field)
            {
                $mform->disabledIf($this->get_elprefix($prefix.$field), $this->get_elprefix($prefix.'copy'), 'checked');
            }
        }
        
        // Страна и регион
        list($choices, $regions) = $this->dof->modlib('refbook')->get_country_regions();
        $sel = $mform->addElement(
            'hierselect', 
            $this->get_elprefix($prefix.'country'), 
            $this->dof->get_string('addrcountryregion', 'sel').':'
        );
        $sel->setMainOptions($choices);
        $sel->setSecOptions($regions);
        if ( $required )
        {
            $mform->addRule($this->get_elprefix($prefix.'country'),$this->dof->get_string('error_country', 'persons'), 'required',null,'client');
        }
        $mform->setDefault($this->get_elprefix($prefix.'country'), ['RU', ['RU' => 'RU-MOS']]);
    
        // Индекс
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'postalcode'), 
            $this->dof->get_string('addrpostalcode','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'postalcode'), PARAM_TEXT);

        // Район
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'county'), 
            $this->dof->get_string('addrcounty','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'county'), PARAM_TEXT);
        
        // Населенный пункт
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'city'), 
            $this->dof->get_string('addrcity','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'city'), PARAM_TEXT);
        
        // Улица
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'streetname'), 
            $this->dof->get_string('addrstreetname','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'streetname'), PARAM_TEXT);
        
        // Тип улицы
        $street = (array)$this->dof->modlib('refbook')->get_street_types();
        $mform->addElement(
            'select', 
            $this->get_elprefix($prefix . 'streettype'), 
            $this->dof->get_string('addrstreettype','sel').':',
            $street
        );
        $mform->setType($this->get_elprefix($prefix . 'streettype'), PARAM_TEXT);
        
        // Номер дома
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'number'), 
            $this->dof->get_string('addrnumber','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'number'), PARAM_TEXT);
        
        // Подъезд
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'gate'), 
            $this->dof->get_string('addrgate','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'gate'), PARAM_TEXT);
        
        // Этаж
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'floor'), 
            $this->dof->get_string('addrfloor','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'floor'), PARAM_TEXT);
        
        // Квартира
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'apartment'), 
            $this->dof->get_string('addrapartment','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'apartment'), PARAM_TEXT);
        
        // Широта
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'latitude'), 
            $this->dof->get_string('addrlatitude','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'latitude'), PARAM_TEXT);
        
        // Долгота
        $mform->addElement(
            'text', 
            $this->get_elprefix($prefix . 'longitude'), 
            $this->dof->get_string('addrlongitude','sel').':'
        );
        $mform->setType($this->get_elprefix($prefix . 'longitude'), PARAM_TEXT);
    }
    
    /**
     * Сформировать список подразделений для выпадающего списка
     */
    protected function get_departments_list()
    {
        $rez = array();
    
        if ( $dep = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true) )
        {//получили список отделов
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
            $dep = $this->dof_get_acl_filtered_list($dep, $permissions);
            //сливаем массивы
            return $rez + $dep;
        }else
        {//отделов нет
            return $this->dof_get_select_values();
        }
    }
    
    /**
     * Получить имя элемента с префиксом
     *
     * @param string $element - Имя элемента формы
     * 
     * @return string - Имя элемента с префиксом
     */
    public function get_elprefix($elementname)
    {
        // Добавление элемента в список
        $this->elements[$elementname] = $elementname;
        
        // Формирование полного имени поля с префиксом
        $fullelementname = "{$this->currentpersoncode}__$elementname";
        
        return $fullelementname;
    }

    /**
     * Регистрация персоны в форме
     *
     * @param string $personcode - Код персоны
     */
    private function add_person($personcode)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
    
        $personcode = (string)$personcode;
    
        // Перевод персоны в пул обработанных
        $this->persons['added'][$personcode] = $this->persons['pool'][$personcode];
        unset($this->persons['pool'][$personcode]);
    
        // Добавить данные в форму
        if ( empty($this->persons['added'][$personcode]) )
        {// Была добавлена новая персона
            // Заполнение формы значениями по-умолчанию
            $this->set_default_data($personcode);
        } else
        {// Была добавлена существующая персона
            $this->set_person_data($personcode);
        }
        
        // Заблокировать поля, если необходимо
        $freezedfields = [];
        if ( isset($this->freezepersons[$personcode]) )
        {// Требуется заблокировать поля персоны
            $this->dof->modlib('nvg')->add_css('modlib', 'widgets', '/css/show_hide.css');
            if ( empty($this->freezepersons[$personcode]) )
            {// Заблокировать все поля
                foreach ( $mform->_elements as $element )
                {
                    // Получение имени поля
                    $name = (string)$element->getName();
                    if ( ! empty($name) )
                    {// Имя определено
                        $codeend = strpos($name, '__');
                        if ( $codeend )
                        {// Код найден
                            $code = substr($name, 0, $codeend);
                            if ( $code === $personcode )
                            {// Заблокировать поле
                                $mform->freeze($name);
                            }
                        }
    
                    }
                }
            } else
            {// Заблокировать только указанные поля
                foreach ( $this->freezepersons[$personcode] as $fieldname )
                {
                    // Получить полное имя поля
                    $name = $this->get_elprefix($fieldname);
    
                    if ( $mform->elementExists($name) )
                    {// Поле найдено
                        $mform->freeze($name);
                    }
                }
            }
        }
    }
    
    /**
     * Заполнить форму персоны значениями по-умолчанию
     * 
     * @param unknown $personcode - Код персоны
     * 
     * @return void
     */
    protected function set_default_data($personcode)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        $default = [];
        $default['dateofbirth'] = 0;
        $default['passportdate'] = 0;
        $default['country'] = ['RU'];
        $default['addressid_country'] = ['RU'];
        $default['birthaddressid_country'] = ['RU'];
        $default['departmentid'] = $this->addvars['departmentid'];
        
        // Заполнение значениями по-умолчанию
        foreach ( $default as $code => $value )
        {
            $elementName = $this->get_elprefix($code);
            if ( $mform->elementExists($elementName) )
            {// Поле найдено
                $mform->setDefault($elementName, $value);
            }
        }
    }
    
    /**
     * Заполнить форму персоны
     * 
     * @param unknown $personcode - Код персоны
     */
    protected function set_person_data($personcode)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Значения по-умолчанию
        $this->set_default_data($personcode);
        
        $personid = $this->persons['added'][$personcode];
        if ( empty($personid) )
        {// Заполнение значениями по-умолчанию
            return;
        }
        
        // Получить персону
        $person = $this->dof->storage('persons')->get($personid);
        if ( empty($person) )
        {// Персона не найдена
            return;
        }
        
        // Заполнение формы базовыми данными
        foreach ( $person as $name => $value )
        {
            $elementName = $this->get_elprefix($name);
            
            // Поле находится в составе группы
            if ( $name == 'sync2moodle' )
            {
                $mform->setDefault($elementName, $value);
            }
            if ( $name == 'middlename' && $value === null )
            {// Отчество не указано
                if ( $mform->elementExists($this->get_elprefix('havemiddlename')) )
                {// Поле найдено
                    $mform->setDefault($this->get_elprefix('havemiddlename'), 1);
                }
            }
            if ( $mform->elementExists($elementName) )
            {// Поле найдено
                $mform->setDefault($elementName, $value);
            }
        }
        
        // Заполнение дополнительных полей имени
        $prefixes = ['n1_', 'n2_'];
        // Заполнение с блоее новой записи
        $index = count($prefixes);
        foreach ( $prefixes as $prefix )
        {
            // Получение нового имени
            $oldname = $this->dof->storage('persons')->get_person_namechanges($person->id, 1, $index);
            if ( $oldname !== false )
            {// Получены данные о старом имени
                $oldname = current($oldname);
                
                // Добавление имени
                $elementName = $this->get_elprefix($prefix.'firstname');
                if ( $mform->elementExists($elementName) )
                {// Поле найдено
                    $mform->setDefault($elementName, $oldname->firstname);
                }
                
                // Добавление фамилии
                $elementName = $this->get_elprefix($prefix.'lastname');
                if ( $mform->elementExists($elementName) )
                {// Поле найдено
                    $mform->setDefault($elementName, $oldname->lastname);
                }
                
                // Добавление отчества
                if ( isset($oldname->middlename) )
                {
                    $elementName = $this->get_elprefix($prefix.'middlename');
                    if ( $mform->elementExists($elementName) )
                    {// Поле найдено
                        $mform->setDefault($elementName, $oldname->middlename);
                    }
                }
            }
            $index--;
        }
            
        // Заполнение полей адресов
        foreach ( $this->addresstypes as $prefix => $addresstype )
        {
            if ( $address = $this->dof->storage('addresses')->get($person->$addresstype) )
            {// Адрес получен
                $address->country = [$address->country, $address->region];
                foreach ( $address as $k => $v )
                {
                    $elementName = $this->get_elprefix($prefix.$k);
                    if ( $mform->elementExists($elementName) )
                    {// Поле найдено
                        $mform->setDefault($elementName, $v);
                    }   
                }
            }
        }
    }
    
    /**
     * Сгруппировать поля формы по персонам
     * 
     * @return void
     */
    protected function group_fields($data)
    {
        // Группировка пришедших полей по пользователям
        foreach ( $data as $key => $value )
        {
            $codeend = strpos($key, '__');
            if ( $codeend )
            {// Код найден
                $personcode = substr($key, 0, $codeend);
                $fieldname = substr($key, $codeend + 2);
                if ( isset($this->persons['added'][$personcode]) )
                {// Персона - владелец поля найдена
                    $this->personfields[$personcode][$fieldname] = $value;
                }
            }
        }
    }
    
    /**
     * Очистить поля персоны от заблокированных полей
     */
    protected function clear_freezedfields($personcode)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Базовые данные о сохраняемой персоне
        $savedperson = $this->personfields[$personcode];
        
        // Установка кода персоны
        $this->set_personcode($personcode);
        
        $filtered = [];
        
        // Группировка пришедших полей по пользователям
        foreach ( $savedperson as $field => $value )
        {
            $isfrozen = $mform->isElementFrozen($this->get_elprefix($field));
            if ( ! $isfrozen )
            {// Добавить элемент в отфильтрованный массив
                if ( is_string($value) )
                {
                    $filtered[$field] = trim($value);
                } else 
                {
                    $filtered[$field] = $value;
                }
            }
        }
        
        return $filtered;
    }
    
    /**
     * Валидация данных формы
     */
    function validation($data, $files)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Группировка полей по персонам
        $this->group_fields($data);
        
        // Получение массива ошибок
        $errors = parent::validation($data, $files);
        
        foreach ( $this->personfields as $personcode => $personfields )
        {// Валидация полей пользователя
            $personid = $this->persons['added'][$personcode];
            
            // Установка кода персоны
            $this->set_personcode($personcode);
            
            // Получение текущего пользователя
            $person = $this->dof->storage('persons')->get($personid);
            
            $personfields = (object)$personfields;
            
            // Нормализация
            if ( ! isset($personfields->havemiddlename) )
            {
                $personfields->havemiddlename = 0;
            }
            
            // Проверки ФИО
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('firstname'));
            if ( ( ! $isfrozen ) && ( ! trim($personfields->firstname) ) )
            {// Имя не указано
                $errors[$this->get_elprefix('firstname')] = $this->dof->get_string('person_edit_error_firstname_not_set', 'persons');
            }
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('lastname'));
            if ( ( ! $isfrozen ) && ( ! trim($personfields->lastname) ) )
            {// Фамилия не указана
                $errors[$this->get_elprefix('lastname')] = $this->dof->get_string('person_edit_error_lastname_not_set', 'persons');
            }
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('middlename'));
            $isfrozen = $isfrozen || $personfields->havemiddlename;
            if ( ( ! $isfrozen ) && ( ! trim($personfields->middlename) ) )
            {// Отчество не указано
                $errors[$this->get_elprefix('middlename')] = $this->dof->get_string('person_edit_error_middlename_not_set', 'persons');
            }
            
            // Проверка даты рождения
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('dateofbirth'));
            if ( ! $isfrozen && $personfields->dateofbirth['timestamp'] <= -1893421800 )
            {// Неверно указанная дата
                $errors[$this->get_elprefix('dateofbirth')] = $this->dof->get_string('err_date', 'persons');
            }
            
            // Проверка пола
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('gender'));
            if ( ! $isfrozen && $personfields->gender == 'unknown' )
            {// Пол не указан
                $errors[$this->get_elprefix('gender')] = $this->dof->get_string('error_sex', 'persons');
            }
            
            // Проверка Email и лимита подразделения
            if ( ! empty($personid) )
            {// Персона редактируется
                
                $isfrozen = $mform->isElementFrozen($this->get_elprefix('email'));
                if ( ! $isfrozen )
                {// Валидация email
                    if ( empty($personfields->email) )
                    {// Не указан email
                        $errors[$this->get_elprefix('email')] = $this->dof->get_string('person_edit_error_email_empty', 'persons');
                    }
                    if ( ($personfields->email != $person->email) && ! $this->dof->storage('persons')->is_email_unique($personfields->email) )
                    {// Email не уникален
                        $errors[$this->get_elprefix('email')] = $this->dof->get_string('person_edit_error_email_notunique', 'persons');
                    }
                    if ( ! $this->dof->modlib('ama')->user(false)->validate_email($personfields->email) )
                    {// Email не валиден
                        $errors[$this->get_elprefix('email')] = $this->dof->get_string('person_edit_error_email_invalid', 'persons');
                    }
                }
                
                // Проверка на лимит персон в подразделении при переносе
                $canchangedep = $this->dof->storage('config')->get_limitobject('persons', $personfields->departmentid);
                if ( ! $canchangedep && $person->departmentid != $personfields->departmentid )
                {// Запрет переноса персоны в соседнее подразделение
                    $errors[$this->get_elprefix('departmentid')] = $this->dof->get_string('limit_message','persons');
                }
            } else
            {// Персона создается
                
                $isfrozen = $mform->isElementFrozen($this->get_elprefix('email'));
                if ( ! $isfrozen )
                {// Валидация email
                    if ( empty($personfields->email) )
                    {// Не указан email
                        $errors[$this->get_elprefix('email')] = $this->dof->get_string('person_edit_error_email_empty', 'persons');
                    }
                    if ( ! $this->dof->storage('persons')->is_email_unique($personfields->email) )
                    {// Email не уникален
                        $errors[$this->get_elprefix('email')] = $this->dof->get_string('person_edit_error_email_notunique', 'persons');
                    }
                    if ( ! $this->dof->modlib('ama')->user(false)->validate_email($personfields->email) )
                    {// Email не валиден
                        $errors[$this->get_elprefix('email')] = $this->dof->get_string('person_edit_error_email_invalid', 'persons');
                    }
                }
                if ( ! $this->dof->storage('persons')->is_email_unique($personfields->email) )
                {// Email не уникален
                    $errors[$this->get_elprefix('email')] = $this->dof->get_string('error_email','persons');
                }
                
                // Проверка на лимит персон в подразделении при переносе
                $cancreatedep = $this->dof->storage('config')->get_limitobject('persons', $personfields->departmentid);
                if ( ! $cancreatedep )
                {// Запрет переноса персоны в соседнее подразделение
                    $errors[$this->get_elprefix('departmentid')] = $this->dof->get_string('limit_message','persons');
                }
            }
            
            // Проверка полей дополнительного email
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('emailadd1'));
            if ( ! $isfrozen && ! empty($personfields->emailadd1) )
            {// Требуется валидация email
                if ( ! $this->dof->modlib('ama')->user(false)->validate_email($personfields->emailadd1) )
                {// Email не валиден
                    $errors[$this->get_elprefix('emailadd1')] = $this->dof->get_string('person_edit_error_email_invalid', 'persons');
                }
            }
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('emailadd2'));
            if ( ! $isfrozen && ! empty($personfields->emailadd2) )
            {// Требуется валидация email
                if ( ! $this->dof->modlib('ama')->user(false)->validate_email($personfields->emailadd2) )
                {// Email не валиден
                    $errors[$this->get_elprefix('emailadd2')] = $this->dof->get_string('person_edit_error_email_invalid', 'persons');
                }
            }
            
            // Проверка skype
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('skype'));
            if ( ! $isfrozen && ! empty($personfields->skype) && 
                 ! preg_match('/^[a-z][a-z0-9\.,\-_]{5,31}$/i', $personfields->skype) )
            {
                $errors[$this->get_elprefix('skype')] = $this->dof->get_string('error_skype','persons');
            }
            
            // Проверка полей удостоверения личности
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('passportserial'));
            if ( ! $isfrozen && $personfields->passtypeid != '0' && empty($personfields->passportserial) )
            {
                $errors[$this->get_elprefix('passportserial')] = $this->dof->get_string('error_passser','persons');
            }
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('passportnum'));
            if ( ! $isfrozen && $personfields->passtypeid != '0' && empty($personfields->passportnum) )
            {
                $errors[$this->get_elprefix('passportnum')] = $this->dof->get_string('error_passnum','persons');
            }
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('passportem'));
            if ( ! $isfrozen && $personfields->passtypeid != '0' && empty($personfields->passportem) )
            {
                $errors[$this->get_elprefix('passportem')] = $this->dof->get_string('error_passtem','persons');
            }
            
            // Проверка полей адресов
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('streetname'));
            if ( ! $isfrozen && ! empty($personfields->streetname) && empty($personfields->streettype) )
            {
                $errors[$this->get_elprefix('streettype')] = $this->dof->get_string('error_streettype', 'persons');
            }
            
            // Проверка синхронизации
            $isfrozen = $mform->isElementFrozen($this->get_elprefix('mdluser'));
            if ( ! $isfrozen && isset($personfields->mdluser) && ! empty($personfields->mdluser) )
            {
                // Валидация указанного пользователя
                $users = $this->dof->storage('persons')->get_records(['mdluser' => $personfields->mdluser]);
                if ( count($users) > 1 )
                {// Если нашёл более одного пользователя с одинаковым mdluser
                    $errors[$this->get_elprefix('mdluser')] = $this->dof->get_string('error_mdlusercount', 'persons');
                } elseif ( ! empty($users) )
                {// Проверим, если мы обновляем пользователя, а mdluser такой же есть у другого
                    $user = current($users);
                    $userid = $user->id;
                    if ( ! empty($personid) )
                    {// Редактирование персоны
                        if ( $personid != $userid )
                        {// Mdluserid зарезервирован за другой персоной
                            $errors[$this->get_elprefix('mdluser')] = $this->dof->get_string('error_mdluser', 'persons');
                        }
                    } else
                    {// Создание персоны
                        // Mdluserid зарезервирован за другой персоной
                        $errors[$this->get_elprefix('mdluser')] = $this->dof->get_string('error_mdluser', 'persons');
                    }
                }
            }
        }
    
        return $errors;
    }
    
    /** 
     * Копирование полей из адреса регистрации при необходимости
     *
     * @param object $data отправленные с формы поля
     */
    protected function copy_address(&$data)
    {
        // Базовые данные для автозаполнения адресов
        $prefixes = ['addressid_', 'birthaddressid_'];
        $fields = [
            'country',
            'region',
            'postalcode', 
            'county',
            'city', 
            'streetname', 
            'streettype', 
            'number',
            'gate', 
            'floor', 
            'apartment', 
            'latitude', 
            'longitude'
        ];

        // Обработка дополнительных адресов
        foreach ( $prefixes as $prefix )
        {
            // Определение поля для копирования адреса
            $copyfield = $prefix.'copy';
            if ( isset($data->$copyfield) )
            {// Требуется скопировать поля из основного адреса
                foreach ( $fields as $field )
                {
                    // Копирование поля
                    if ( isset($data->$field) && ! empty($data->$field) )
                    {// Поле в базовом адресе заполнено
                        // Копирование поля в дополнительный адрес
                        $data->{$prefix.$field} = $data->$field;
                    }
                }
            }
        }
    }

    /**
     * Обработчик формы
     *
     * @return void
     */
    public function process()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Сохранение данных
            foreach ( $this->personfields as $personcode => $personfields )
            {// Сохранение пользователя
                $this->process_person($personcode);
            }
            
            // Дополнительный обработчик
            $this->process_after_persons($formdata);
            
            if ( empty($this->errors) )
            {// Ошибок не обнаружено
                
                $url = $this->get_returnurl();
                redirect($url);
            }
        }
    }
    
    /**
     * Дополнительный обработчик для использования в дочерних классах формы
     */
    protected function process_after_persons($formdata)
    {
    }

    /**
     * Обработчик персоны
     * 
     * @param string $personcode
     */
    protected function process_person($personcode)
    {
        // Базовые данные о сохраняемой персоне
        $savedperson = $this->personfields[$personcode];
        $personid = $this->persons['added'][$personcode];
        
        // Получить незаблокированные поля персоны
        $savedperson = (object)$this->clear_freezedfields($personcode);
        
        // Сохранять нечего
        if ( empty($savedperson) )
        {
            return true;
        }
        
        // Добавление ID в результирующий массив
        if ( ! empty($personid) )
        {
            $savedperson->id = $personid;
        }
        
        // Нормализация базовых данных персоны
        if ( isset($savedperson->dateofbirth) )
        {// Передано поле даты рождения персоны
            $savedperson->dateofbirth = 0;
            if ( isset($savedperson->dateofbirth['timestamp']) )
            {// Указана метка времени
                $savedperson->dateofbirth = $savedperson->dateofbirth['timestamp'];
            }
        }
        if ( isset($savedperson->passportdate) )
        {// Передано поле даты выдачи удостоверения личности персоны
            $savedperson->passportdate = 0;
            if ( isset($savedperson->passportdate['timestamp']) )
            {// Указана метка времени
                $savedperson->passportdate = $savedperson->passportdate['timestamp'];
            }
        }  
        
        if ( isset($savedperson->havemiddlename) )
        {// Удаление отчества
            $savedperson->middlename = null;
        }
        
        // Обработка персоны
        try {
            if ( ( ! isset($savedperson->id) ) || ( count((array)$savedperson) > 1 ) )
            {// Полей для сохранения достаточно
                // Сохранение персоны
                $personid = (int)$this->dof->storage('persons')->save($savedperson);
            }
            $this->persons['added'][$personcode] = $personid;
        } catch ( dof_exception_dml $e )
        {// Ошибка во время сохранения персоны
            $this->errors[] = $this->dof->get_string($e->errorcode, 'persons', null, 'storage');
            return false;
        }
        
        // Сохранение адресов
        $form_addrfields = [
            'postalcode', 
            'county',
            'city', 
            'streetname', 
            'streettype', 
            'number',
            'gate', 
            'floor', 
            'apartment', 
            'latitude', 
            'longitude'
        ];
        
        // Подготовка адресов для сохранения
        $addresses = [];
        
        // Копирование адресов, если требуется
        $this->copy_address($savedperson);
        
        // Обработка адресов
        foreach ( $this->addresstypes as $prefix => $addrtype )
        {
            $address = new stdClass();
            
            // Добавление типа адреса по SIF
            if ( $addrtype === 'passportaddrid' )
            {// Адрес по паспорту
                $address->type = 1;
            } elseif ( $addrtype === 'addressid' ) 
            {// Домашний адрес
                $address->type = 2;
            } else
            {// Другой адрес
                $address->type = 9;
            }
            // Добавление региона
            if ( isset($savedperson->{$prefix.'country'}[1]) )
            {// Установлен регион
                $address->region = $savedperson->{$prefix.'country'}[1];
            }
            
            // Добавление страны
            if ( isset($savedperson->{$prefix.'country'}[0]) )
            {// Установлен регион
                $address->country = $savedperson->{$prefix.'country'}[0];
            }

            // Добавление остальных полей
            foreach ( $form_addrfields as $field )
            {
                if ( isset($savedperson->{$prefix . $field}) )
                {// Установлен регион
                    $address->$field = trim($savedperson->{$prefix . $field});
                }
            }
            
            // Добавление данных в результирующий массив
            if ( ! empty($address) )
            {
                $addresses[$addrtype] = $address;
            }
        }
        
        if ( ! empty($personid) )
        {// Определен ID персоны
            // Получение персоны для привязки адресов
            $person = $this->dof->storage('persons')->get($personid);
            if ( ! empty($person) )
            {
                $savedata = new stdClass();
                $savedata->id = $person->id;
                
                // Сохранение данных об адресе
                foreach ( $addresses as $addrcode => $addrobj )
                {
                    if ( ! empty($person->$addrcode) )
                    {// Определен код адреса
                        // Обновление адреса
                        $this->dof->storage('addresses')->update($addrobj, $person->$addrcode);
                    } else
                    {// Код адреса не определен
                        $savedata->$addrcode = $this->dof->storage('addresses')->insert($addrobj);
                    }
                }
                
                // Добавление данных о синхронизации
                if ( $this->dof->storage('persons')->is_access('edit:sync2moodle') )
                {
                    if ( isset($savedperson->sync2moodle) )
                    {
                        $savedata->sync2moodle = $savedperson->sync2moodle;
                    }
                    if ( isset($savedperson->sync2moodle) )
                    {
                        $savedata->mdluser = trim($savedperson->mdluser);
                    }
                }
                
                // Редактирование временной зоны
                if ( $this->dof->storage('persons')->is_access('edit_timezone')
                     && $person->mdluser && $person->sync2moodle )
                {
                    if ( isset($savedperson->timezone) )
                    {
                        // Сохранение временной зоны
                        $obj = new stdClass();
                        if ( isset($savedperson->mdluser) )
                        {
                            $obj->id = $savedperson->mdluser;
                        } else
                        {
                            $obj->id = $person->mdluser;
                        }
                        $obj->timezone = $savedperson->timezone;
                        
                        if ( $this->dof->modlib('ama')->user(false)->is_exists($obj->id) )
                        {
                            $this->dof->modlib('ama')->user($obj->id)->update($obj);
                        }
                    }
                }
                
                // Сохранение устаревших ФИО
                $count = $this->dof->storage('persons')->count_name_changes($person->id);
                // Автозаполнение
                if ( $count < 2 )
                {
                    $count = 2 - $count;
                    for ( $index = 0; $index < $count; $index++ )
                    {
                        $this->dof->storage('persons')->create_old_name($person->id, false);
                    }
                }
                $prefixes = array('n1_', 'n2_');
                $index = 2;
                foreach ( $prefixes as $prefix )
                {
                    // Заполнение недостающих данных
                    if ( ! isset($savedperson->{$prefix.'firstname'}) )
                    {// Получение текущего значения
                        $savedperson->{$prefix.'firstname'} = (string)$this->dof->storage('cov')->get_option(
                            'storage',
                            'persons', 
                            $person->id, 
                            'oldfirstname'.$index
                        );
                    }
                    if ( ! isset($savedperson->{$prefix.'lastname'}) )
                    {// Получение текущего значения
                    $savedperson->{$prefix.'lastname'} = (string)$this->dof->storage('cov')->get_option(
                        'storage',
                        'persons',
                        $person->id,
                        'oldlastname'.$index
                        );
                    }
                    if ( ! isset($savedperson->{$prefix.'middlename'}) )
                    {// Получение текущего значения
                    $savedperson->{$prefix.'middlename'} = (string)$this->dof->storage('cov')->get_option(
                        'storage',
                        'persons',
                        $person->id,
                        'oldmiddlename'.$index
                        );
                    }
                    if ( isset($savedperson->havemiddlename) )
                    {// Без отчества
                        $savedperson->{$prefix.'middlename'} = '';
                    }
                    $this->dof->storage('persons')->edit_old_name(
                        $person->id, 
                        $index, 
                        $savedperson->{$prefix . 'firstname'},
                        $savedperson->{$prefix . 'lastname'},
                        $savedperson->{$prefix . 'middlename'}
                    );
                    $index--;
                }
                
                try {
                    if ( ( ! isset($savedata->id) ) || ( count((array)$savedata) > 1 ) )
                    {// Полей для сохранения достаточно
                        // Сохранение персоны
                        $personid = (int)$this->dof->storage('persons')->save($savedata);
                    }
                } catch ( dof_exception_dml $e )
                {// Ошибка во время сохранения персоны
                    $this->errors[] = $this->dof->get_string($e->errorcode, 'persons', null, 'storage');
                    return false;
                }
            }
        }
    }

    /**
     * Установка текущего кода сохраняемой персоны
     *
     * @param string $personcode - Код персоны, который требуется установить
     *
     * @return $code - Код персоны
     */
    protected function set_personcode($personcode = null)
    {
        $code = null;
        if ( $personcode === null )
        {// Код персоны не установлен
            // Установка первой персоны в пуле
            if ( count($this->persons['pool']) > 0 )
            {// Есть персоны в пуле
                // Установка первой персоны
                reset($this->persons['pool']);
                $code = (string)key($this->persons['pool']);
            }
        } else 
        {// Код персоны передан
            if ( isset($this->persons['pool'][$personcode]) )
            {// Персона найдена
                $code = (string)$personcode;
            } elseif ( isset($this->persons['added'][$personcode]) ) 
            {// Персона найдена
                $code = (string)$personcode;
            }
        }
        $this->currentpersoncode = $code;
        return $code;
    }
    
    /**
     * Получить URL для возврата после обработки
     */
    protected function get_returnurl()
    {
        // Формирование URL для редиректа
        $parsedurl = parse_url($this->returnurl);
        
        // Массив GET-параметров для URL возврата
        $query = [];
        if ( isset($parsedurl['query']) && ! empty($parsedurl['query']) )
        {// В URL возврата указаны GET-параметры
            $parsedquery = explode('&', $parsedurl['query']);
            foreach ( $parsedquery as $parameter )
            {// Формирование GET-массива
                $parameter = explode('=', $parameter);
                if ( isset($parameter[0]) && isset($parameter[1]) )
                {// Валидный параметр
                    // Очистка от возможного параметра-массива
                    $parameter[0] = str_replace('[]', '', $parameter[0]);
                    if ( ! isset($query[$parameter[0]]) )
                    {// Добавление значения
                        $query[$parameter[0]] = $parameter[1];
                    } else
                    {// Параметр уже найден среди имеющихся. Формирование массива значений
                        $query[$parameter[0]] = (array)$query[$parameter[0]];
                        $query[$parameter[0]][] = $parameter[1];
                    }
                }
            }
        }
        
        // Добавление результатов обработки формы
        foreach ( $this->persons['added'] as $personcode => $personid )
        {
            $query[$personcode] = $personid;
        }
        
        // Формирование результирующего URL
        $resultquery = [];
        foreach ( $query as $name => $value )
        {
            if ( is_array($value) )
            {
                foreach ( $value as $element )
                {
                    $resultquery[] = $name.'[]='.$element;
                }
            } else
            {
                $resultquery[] = $name.'='.$value;
            }
        }
        
        $query = implode('&', $resultquery);
        $url = $parsedurl['path'].'?'.$query;
        
        return $url;
    }
}



class dof_im_persons_email_edit_form extends dof_modlib_widgets_form
{

    /**
     * @var dof_control
     */
    protected $dof;

    function definition()
    {
        global $DOF;
        $this->dof = $DOF;
        $mform =& $this->_form;
        // обьявляем заголовок формы
        $mform->addElement('header','stheader');
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // email
        $mform->addElement('textarea', 'emails', $this->dof->get_string('email','sel').':', array('cols'=>50, 'rows'=>20));
        // Кнопка "сохранить"
        $mform->addElement('submit', 'save');
        $mform->setDefault('save', $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');

    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        return true;
    }

}

class dof_im_person_search_form extends dof_modlib_widgets_form
{
    function definition()
    {
        global $DOF;
        $mform =& $this->_form;
        $this->dof = $DOF;
        // обьявляем заголовок формы
        $mform->addElement('header','stheader');
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        //По каким параметрам искать
        $mform->addElement('radio', 'option', '', $this->dof->get_string('bylastname', 'persons'), 'bylastname');
        $mform->addElement('radio', 'option', '', $this->dof->get_string('byquery', 'persons'), 'byquery');
        $mform->addElement('radio', 'option', '', $this->dof->get_string('byoldname', 'persons'), 'byoldname');
        $mform->setDefault('option', 'bylastname');
        //Искать ли в дочерних подразделениях
        $mform->addElement('checkbox', 'children', '', $this->dof->get_string('children', 'persons'));
        // Значение поля по которому будем искать
        $mform->addElement('text', 'searchstring', '', 'size="20"');
        $mform->setType('searchstring', PARAM_TEXT);
        // Кнопки "сохранить" и "отмена"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('search'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');

    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data, $files)
    {
        return true;
    }
}

/**
 * Форма расширенного поиска персон
 */
class dof_im_person_extendedsearch_form extends dof_modlib_widgets_form
{

    function definition()
    {
        $mform = & $this->_form;
        $this->dof = $this->_customdata->dof;
        $this->dof->modlib('nvg')->add_css('im', 'persons', '/styles.css');

        $mform->addElement('hidden', 'departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);

        // ФИО
        $mform->addElement('text', 'lastname', $this->dof->get_string('lastname', 'persons'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addElement('text', 'firstname', $this->dof->get_string('firstname', 'persons'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addElement('text', 'middlename', $this->dof->get_string('middlename', 'persons'));
        $mform->setType('middlename', PARAM_TEXT);

        // Номер договора
        $mform->addElement('text', 'contractnum', $this->dof->get_string('num', 'sel'));
        $mform->setType('contractnum', PARAM_TEXT);

        // Один из номеров телефона
        $mform->addElement('text', 'phone', $this->dof->get_string('one_of_phones', 'persons'));
        $mform->setType('phone', PARAM_TEXT);

        // Один из почтовых адресов
        $mform->addElement('text', 'email', $this->dof->get_string('one_of_emails', 'persons'));
        $mform->setType('email', PARAM_TEXT);

        // Образовательная программа
        $programms = $this->dof->storage('programms')->get_menu_programms_list();
        $programms = array(0 => $this->dof->get_string('any_programm', 'programms')) + $programms;
        $mform->addElement('select', 'programmid', $this->dof->get_string('learningprogramm', 'programms'), $programms);
        $mform->setType('programmid', PARAM_INT);

        // Текущий период
        $ages = $this->dof->storage('ages')->get_records(array(), 'name');
        $ages = $this->dof_get_select_values($ages, array(0 => $this->dof->get_string('any_age', 'ages')));
        $mform->addElement('select', 'currentageid', $this->dof->get_string('current_age', 'ages'), $ages);
        $mform->setType('currentageid', PARAM_INT);

        // Начальный период
        $mform->addElement('select', 'startageid', $this->dof->get_string('start_age', 'ages'), $ages);
        $mform->setType('startageid', PARAM_INT);

        // Текущий семестр
        // Получаем максимальный семестр всех подписок
        $maxagenum = $this->dof->storage('programmsbcs')->get_max_agenum();
        // Семестры от 1 до максимального используемого
        $agenumsarr = array();
        for ( $i = 1; $i <= $maxagenum; $i++ )
        {
            $agenumsarr[$i] = $i;
        }
        $agenumsarr = array(0 => $this->dof->get_string('any_agenum', 'programmsbcs')) + $agenumsarr;
        // Добавляем элемент на форму
        $mform->addElement('select', 'currentagenum', $this->dof->get_string('current_agenum', 'programmsbcs'), $agenumsarr);
        $mform->setType('currentagenum', PARAM_INT);

        // Куратор
        // Получаем id кураторов
        $curatorids = $this->dof->storage('contracts')->get_curator_ids();
        // По их id получаем сами персоны
        $curators = $this->dof->storage('persons')->get_records(array("id" => $curatorids), "sortname ASC", "id,sortname as name");
        // Формируем массив с персонами для выпадающего списка
        $curatorsarr = array();
        foreach ( $curators as $curator )
        {
            $curatorsarr[$curator->id] = $curator->name;
        }
        $curatorsarr = array(0 => $this->dof->get_string('any_person', 'persons')) + $curatorsarr;
        // Добавляем элемент на форму
        $mform->addElement('select', 'curatorid', $this->dof->get_string('curator', 'sel'), $curatorsarr);
        $mform->setType('curatorid', PARAM_INT);

        // Cтатус подписки
        // Получаем список всех статусов подписок
        $pbcsstatuses = array(0 => $this->dof->get_string('withoutpsbc', 'persons')) + $this->dof->workflow('programmsbcs')->get_list();
        $pbcsststuseselements = array();
        foreach ( $pbcsstatuses as $pbcsstatus => $pbcsstatuslabel )
        {// Cобираем массив с чекбоксами
            $pbcsststuseselements[] = $mform->createElement('checkbox', $pbcsstatus, '', $pbcsstatuslabel);
        }
        // Добавляем чекбоксы в одну группу
        $mform->addGroup($pbcsststuseselements, 'pbcsstatus', $this->dof->get_string('pbcs_status', 'programmsbcs'), '&nbsp;');

        // Статус договора
        // Получаем список всех статусов договоров
        $contractstatuses = array(0 => $this->dof->get_string('withoutcontract', 'persons')) + $this->dof->workflow('contracts')->get_list();
        $contractstatuseselements = array();
        foreach ( $contractstatuses as $contractstatus => $contractstatuslabel )
        {//собираем массив с чекбоксами
            $contractstatuseselements[] = $mform->createElement('checkbox', $contractstatus, '', $contractstatuslabel);
        }
        //добавляем чекбоксы в одну группу
        $mform->addGroup($contractstatuseselements, 'contractstatus', $this->dof->get_string('contract_status', 'sel'), '&nbsp;');

        //Искать ли в дочерних подразделениях
        $mform->addElement('checkbox', 'children', '', $this->dof->get_string('children', 'persons'));
        $mform->setDefault('children', 'checked');

        // Кнопки "сохранить" и "отмена"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('search'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data, $files)
    {
        return true;
    }

    function process()
    {
        global $addvars;
        $conds = new stdClass();
        if ( $formdata = $this->get_data() )
        {
            if ( isset($formdata->pbcsstatus) )
            {
                $conds->pbcsstatus     = implode(',', array_keys($formdata->pbcsstatus));
                unset($formdata->pbcsstatus);
            }
            if ( isset($formdata->contractstatus) )
            {
                $conds->contractstatus = implode(',', array_keys($formdata->contractstatus));
                unset($formdata->contractstatus);
            }
            $fields = array('lastname',   'firstname',     'middlename', 'contractnum',
                'phone',      'email',         'programmid', 'currentageid',
                'startageid', 'currentagenum', 'curatorid',  'children');
            foreach ( $fields as $field )
            {
                if ( isset($formdata->$field) )
                {
                    $conds->$field = $formdata->$field;
                }
            }
            // Отправлена форма - отображаем первую страницу
            $addvars['limitfrom'] = 0;
            //            $this->set_data_person($conds);
            $url = $this->dof->url_im('persons', '/extendedsearch.php', (array)$conds + $addvars);
            redirect($url);
        } else if ( $this->is_cancelled() )
        {
            $url = $this->dof->url_im('persons', '/extendedsearch.php', $addvars);
            redirect($url);
        }
    }
}

?>