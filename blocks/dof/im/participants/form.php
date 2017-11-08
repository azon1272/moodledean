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
 * Интерфейс управления подразделениями. Библиотека форм.
 * 
 * @package    im
 * @subpackage participants
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('lib.php');
global $DOF;
// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();
require_once($DOF->plugin_path('im', 'persons', '/form.php'));

/**
 * Фильтр слушателей в панели управления слушателями
 * 
 */
class dof_im_participants_students_filter extends dof_modlib_widgets_form
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
     * URL для возврата
     * 
     * @var string
     */
    protected $returnurl = NULL;
    
    /**
     * Обьявление полей формы
     * 
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        if ( isset($this->_customdata->returnurl) && ! empty($this->_customdata->returnurl) )
        {// Передан url возврата
            $this->returnurl = $this->_customdata->returnurl;
        } else 
        {// Установка url возврата на страницу обработчика
            $this->returnurl = $mform->getAttribute('action');
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        // Данные для установки значений полей
        $data = $this->addvars_to_filter();
        // Группа фильтров по персоне
        $filter_usergroup = [];
        
        // Фамилия пользователя
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'persons';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'autocomplete_lastname';
        if ( isset($data->autocomplete_lastname) )
        {
            $options['default'] = (array)$data->autocomplete_lastname;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_lastname_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_lastname_label', 'participants')
        ];
        $filter_usergroup[] = $mform->createElement(
            'dof_autocomplete', 
            'autocomplete_lastname',
            $this->dof->get_string('students_filter_lastname_label', 'participants'), 
            $attributes, 
            $options
        );
        // Имя пользователя
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'persons';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'autocomplete_firstname';
        if ( isset($data->autocomplete_firstname) )
        {
            $options['default'] = (array)$data->autocomplete_firstname;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_firstname_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_firstname_label', 'participants')
        ];
        $filter_usergroup[] = $mform->createElement(
            'dof_autocomplete',
            'autocomplete_firstname',
            $this->dof->get_string('students_filter_firstname_label', 'participants'),
            $attributes,
            $options
        );
        // Отчество пользователя
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'persons';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'autocomplete_middlename';
        if ( isset($data->autocomplete_middlename) )
        {
            $options['default'] = (array)$data->autocomplete_middlename;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_middlename_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_middlename_label', 'participants')
        ];
        $filter_usergroup[] = $mform->createElement(
            'dof_autocomplete',
            'autocomplete_middlename',
            $this->dof->get_string('students_filter_middlename_label', 'participants'),
            $attributes,
            $options
        );
        // Email пользователя
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'persons';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'autocomplete_email';
        if ( isset($data->autocomplete_email) )
        {
            $options['default'] = (array)$data->autocomplete_email;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_email_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_email_label', 'participants')
        ];
        $filter_usergroup[] = $mform->createElement(
            'dof_autocomplete',
            'autocomplete_email',
            $this->dof->get_string('students_filter_email_label', 'participants'),
            $attributes,
            $options
        );
        // Телефон пользователя
        $options = [];
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_telephone_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_telephone_label', 'participants')
        ];
        $filter_usergroup[] = $mform->createElement(
            'dof_telephone',
            'autocomplete_phone',
            $this->dof->get_string('students_filter_telephone_label', 'participants'),
            $attributes,
            $options
        );
        if ( isset($data->autocomplete_phone) )
        {
            $mform->setDefault('autocomplete_phone', $data->autocomplete_phone);
        }
        $mform->addGroup($filter_usergroup, 'filter_usergroup', '', '', false);
        
        // Группа фильтров по программе
        $filter_programmgroup = [];
        // Программа
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'programms';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'find_programm_with_params';
        $options['additional_data'] = ['agenum' => 'autocomplete_agenum[id]'];
        if ( isset($data->autocomplete_programm) )
        {
            $options['default'] = (array)$data->autocomplete_programm;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_programm_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_programm_label', 'participants')
        ];
        $filter_programmgroup[] = $mform->createElement(
            'dof_autocomplete',
            'autocomplete_programm',
            $this->dof->get_string('students_filter_programm_label', 'participants'),
            $attributes,
            $options
        );
        // Параллель
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'programms';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'select_programm_agenum_with_params';
        $options['additional_data']  = ['programmid' => 'autocomplete_programm[id]'];
        $options['js_options'] = ['delay' => 200, 'string_minlength' => 0];
        $options['default'] = ['' => ''];
        if ( isset($data->autocomplete_agenum) )
        {
            $options['default'] = (array)$data->autocomplete_agenum;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_programmagenum_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_programmagenum_label', 'participants')
        ];
        $filter_programmgroup[] = $mform->createElement(
            'dof_autocomplete',
            'autocomplete_agenum',
            $this->dof->get_string('students_filter_programmagenum_label', 'participants'),
            $attributes,
            $options
        );
        // Группа
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'agroups';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']  = 'autocomplete_name';
        $options['additional_data']  = ['programmid' => 'autocomplete_programm[id]', 'agenum' => 'autocomplete_agenum[id]'];
        $options['js_options'] = ['string_minlength' => 0];
        if ( isset($data->autocomplete_agroup) )
        {
            $options['default'] = (array)$data->autocomplete_agroup;
        }
        $attributes = [
            'placeholder' => $this->dof->get_string('students_filter_agroup_placeholder', 'participants'),
            'title' => $this->dof->get_string('students_filter_agroup_label', 'participants')
        ];
        $filter_programmgroup[] = $mform->createElement(
            'dof_autocomplete',
            'autocomplete_agroup',
            $this->dof->get_string('students_filter_agroup_label', 'participants'),
            $attributes,
            $options
        );
        $mform->addGroup($filter_programmgroup, 'filter_programmgroup', '', '', false);
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit', 
            'submit', 
            $this->dof->get_string('students_filter_submit', 'participants')
        );
        $mform->addGroup($group, 'buttons', '', '', false);
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Вернуть ошибки валидации элементов
        return $errors;
    }
    
    /**
     * Заполнение полей формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;
        
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            if ( empty($this->errors) )
            {// Сформировать массив GET-параметров для редиректа
                // Получение URL перехода
                $url = $this->returnurl;
                // Получение установленных GET-параметров перехода
                $parseurl = parse_url($url);
                $returnurl_addvars = explode('&', $parseurl['query']);
                // Получение GET-параметров, добавляемых формой
                $filter_addvars = (array)$this->filter_to_addvars();

                // Объединение GET-параметров
                foreach ( $returnurl_addvars as $addvar )
                {// Получение данных по параметру
                    $parameter = explode('=', $addvar);
                    if ( isset($parameter[0]) && isset($parameter[1]) && ! isset($filter_addvars[$parameter[0]]) )
                    {// Параметр валиден
                        $filter_addvars[$parameter[0]] = $parameter[1];
                    }
                    
                }
                // Сформировать итоговый URL перехода с объединенными GET-параметрами
                foreach ( $filter_addvars as $key => &$val )
                {
                    $val = $key.'='.urlencode($val);
                }
                $query = implode('&', $filter_addvars);
                $url = $parseurl['path'].'?'.$query;
                redirect($url);
            }
        }
        return $this->get_filter();
    }
    
    /**
     * Получить текущее состояние фильтра
     */
    public function get_filter()
    {
        $filter = [];
        $formdata = $this->addvars_to_filter();
        
        // Фильтры пользователя
        $user = new stdClass();
        if ( isset($formdata->autocomplete_lastname) && is_array($formdata->autocomplete_lastname) )
        {
            $user->lastname = current($formdata->autocomplete_lastname);
        }
        if ( isset($formdata->autocomplete_firstname) && is_array($formdata->autocomplete_firstname) )
        {
            $user->firstname = current($formdata->autocomplete_firstname);
        }
        if ( isset($formdata->autocomplete_middlename) && is_array($formdata->autocomplete_middlename) )
        {
            $user->middlename = current($formdata->autocomplete_middlename);
        }
        if ( isset($formdata->autocomplete_email) && is_array($formdata->autocomplete_email) )
        {
            $user->email = current($formdata->autocomplete_email);
        }
        if ( isset($formdata->autocomplete_phone) && ! empty($formdata->autocomplete_phone) )
        {
            $user->phone = str_replace(' ', '', $formdata->autocomplete_phone);
        }
        $array = (array)$user;
        if ( ! empty($array) )
        {// Фильтры пользователя определены
            $filter['user'] = $user;
        }
        
        // Фильтры программы
        $programm = new stdClass();
        if ( isset($formdata->autocomplete_programm) && ! empty($formdata->autocomplete_programm) )
        {
            $programm->name = current($formdata->autocomplete_programm);
        }
        $array = (array)$programm;
        if ( ! empty($array) )
        {// Фильтры программы определены
            $filter['programm'] = $programm;
        }
        
        // Фильтры академической группы
        $agroup = new stdClass();
        if ( isset($formdata->autocomplete_agenum) && ! empty($formdata->autocomplete_agenum) )
        {
            $agroup->agenum = current($formdata->autocomplete_agenum);
        }
        if ( isset($formdata->autocomplete_agroup) && ! empty($formdata->autocomplete_agroup) )
        {
            $agroup->name = current($formdata->autocomplete_agroup);
        }
        $array = (array)$agroup;
        if ( ! empty($array) )
        {// Фильтры группы определены
            $filter['agroup'] = $agroup;
        }
        
        return $filter;
    }
    
    /**
     * Получить массив GET параметров, с которыми работает фильтр
     */
    public function get_addvars()
    {
        $addvars = [];
        // Получение фамилии пользователя
        $lastname = optional_param('lastname', NULL, PARAM_RAW_TRIMMED);
        if ( $lastname )
        {
            $addvars['lastname'] = $lastname;
        }
        // Получение имени пользователя
        $firstname = optional_param('firstname', NULL, PARAM_RAW_TRIMMED);
        if ( $firstname )
        {
             $addvars['firstname'] = $firstname;
        }
        // Получение отчества пользователя
        $middlename = optional_param('middlename', NULL, PARAM_RAW_TRIMMED);
        if ( $middlename )
        {
            $addvars['middlename'] = $middlename;
        }
        // Получение email пользователя
        $email = optional_param('email', NULL, PARAM_RAW_TRIMMED);
        if ( $email )
        {
            $addvars['email'] = $email;
        }
        // Получение телефона пользователя
        $phone = optional_param('phone', NULL, PARAM_RAW_TRIMMED);
        if ( $phone )
        {
            $addvars['phone'] = $phone;
        }
        
        // Получение имени программы
        $programmname = optional_param('programm', NULL, PARAM_RAW_TRIMMED);
        if ( $programmname )
        {
            $addvars['programm'] = $programmname;
        }
        // Получение номера параллели программы
        $agenum = optional_param('agenum', NULL, PARAM_INT);
        if ( is_int($agenum) )
        {
            $addvars['agenum'] = $agenum;
        }
        // Получение номера параллели программы
        $agroup = optional_param('agroup', NULL, PARAM_RAW_TRIMMED);
        if ( $agroup )
        {
            $addvars['agroup'] = $agroup;
        }
        
        return $addvars;
    }
    
    /**
     * Получить массив GET-параметров на основе данных формы
     */
    public function filter_to_addvars()
    {
        $addvars = $this->addvars;
        $formdata = $this->get_data();
        
        // Получение фамилии пользователя
        if ( trim($formdata->autocomplete_lastname['autocomplete_lastname']) != '' )
        {// Фильтр определен
            $addvars['lastname'] = trim($formdata->autocomplete_lastname['autocomplete_lastname']);
        }
        // Получение имени пользователя
        if ( trim($formdata->autocomplete_firstname['autocomplete_firstname']) != '' )
        {// Фильтр определен
            $addvars['firstname'] = trim($formdata->autocomplete_firstname['autocomplete_firstname']);
        }
        // Получение отчетва пользователя
        if ( trim($formdata->autocomplete_middlename['autocomplete_middlename']) != '' )
        {// Фильтр определен
            $addvars['middlename'] = trim($formdata->autocomplete_middlename['autocomplete_middlename']);
        }
        // Получение email пользователя
        if ( trim($formdata->autocomplete_email['autocomplete_email']) != '' )
        {// Фильтр определен
            $addvars['email'] = trim($formdata->autocomplete_email['autocomplete_email']);
        }
        // Получение телефона пользователя
        if ( trim($formdata->autocomplete_phone) != '' )
        {// Фильтр определен
            $addvars['phone'] = trim($formdata->autocomplete_phone);
        }
        // Получение имени программы
        if ( trim($formdata->autocomplete_programm['autocomplete_programm']) != '' )
        {// Фильтр определен
            $addvars['programm'] = trim($formdata->autocomplete_programm['autocomplete_programm']);
        }
        // Получение номера параллели программы
        if ( trim($formdata->autocomplete_agenum['autocomplete_agenum']) != '' )
        {// Фильтр определен
            $addvars['agenum'] = trim($formdata->autocomplete_agenum['autocomplete_agenum']);
        }
        // Получение имени группы
        if ( trim($formdata->autocomplete_agroup['autocomplete_agroup']) != '' )
        {// Фильтр определен
            $addvars['agroup'] = trim($formdata->autocomplete_agroup['autocomplete_agroup']);
        }
        
        return $addvars;
    }
    
    /**
     * Получить данные для формы на основе GET-параметров
     */
    protected function addvars_to_filter()
    {
        $formdata = new stdClass();
        
        // Получение фамилии пользователя
        $lastname = optional_param('lastname', NULL, PARAM_RAW_TRIMMED);
        if ( $lastname )
        {
            $formdata->autocomplete_lastname = [0 => $lastname];
        }
        // Получение имени пользователя
        $firstname = optional_param('firstname', NULL, PARAM_RAW_TRIMMED);
        if ( $firstname )
        {
            $formdata->autocomplete_firstname = [0 => $firstname];
        }
        // Получение отчества пользователя
        $middlename = optional_param('middlename', NULL, PARAM_RAW_TRIMMED);
        if ( $middlename )
        {
            $formdata->autocomplete_middlename = [0 => $middlename];
        }
        // Получение email пользователя
        $email = optional_param('email', NULL, PARAM_RAW_TRIMMED);
        if ( $email )
        {
            $formdata->autocomplete_email = [0 => $email];
        }
        // Получение телефона пользователя
        $phone = optional_param('phone', NULL, PARAM_RAW_TRIMMED);
        if ( $phone )
        {
            $formdata->autocomplete_phone = $phone;
        }
        
        // Получение имени программы
        $programmname = optional_param('programm', NULL, PARAM_RAW_TRIMMED);
        if ( $programmname )
        {
            $formdata->autocomplete_programm = [0 => $programmname];
        }
        // Получение номера параллели программы
        $agenum = optional_param('agenum', NULL, PARAM_INT);
        if ( is_int($agenum) )
        {
            $formdata->autocomplete_agenum = [(int)$agenum => $agenum];
        }
        // Получение номера параллели программы
        $agroup = optional_param('agroup', NULL, PARAM_RAW_TRIMMED);
        if ( $agroup )
        {
            $formdata->autocomplete_agroup = [0 => $agroup];
        }
    
        return $formdata;
    }
}

/**
 * Форма быстрого создания подписки
 * 
 * Позволяет выбрать пользователя, программу и группу
 * 
 */
class dof_im_participants_students_fastcreate extends dof_modlib_widgets_form
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
     * Данные для создвния подписок
     *
     * @var string
     */
    protected $createdata = [];

    /**
     * Данные для отображения выпадающих списков в форме
     * 
     * @var array
     */
    private $persons = [];
    private $contracts = [];
    private $programms = [];
    private $agroups = null;
    
    /**
     * Возможность отправки формы
     * 
     * @var bool
     */
    public $canconfirm = true;
    
    /**
     * Наличие сообщений для пользователя
     * 
     * @var bool
     */
    public $hasmessages = false;
    
    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->createdata = $this->_customdata->createdata;
        // Добавить данные для создания подписки
        $this->init_data($this->createdata);
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);

        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // ВЫБОР ДОГОВОРА ДЛЯ ПОДПИСКИ
        $mform->addElement(
            'html', 
            dof_html_writer::tag(
                'h5', 
                $this->dof->get_string('students_fastcreate_contract_title', 'participants')
            )
        );
        $sbc_contract = $mform->addElement(
            'hierselect', 
            'sbc_contract',
            null,
            null,
            '<br/>'
        );
        // Инициализация опций иерархического списка
        $this->sbc_contract_hierselect_init($sbc_contract);
        
        // Выбор программы, группы и периода для подписки
        $mform->addElement(
            'html', 
            dof_html_writer::tag(
                'h5', 
                $this->dof->get_string('students_fastcreate_programm_title', 'participants')
            )
        );
        $sbc_programm = $mform->addElement(
            'hierselect', 
            'sbc_programm',
            null,
            null,
            '<br/>'
        );
        // Инициализация опций иерархического списка
        $this->sbc_programm_hierselect_init($sbc_programm);
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('students_fastcreate_submit', 'participants')
        );
        $mform->addGroup($group, 'buttons', '', '', false);

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Проверки для договора на обучение
        if ( ! isset($data['sbc_contract'][0]) || ! isset($data['sbc_contract'][1]) )
        {// Данные о договоре не переданы
            $errors['hidden'] = $this->dof->get_string('students_fastcreate_error_data_not_found', 'participants');
        } else 
        {// Данные о договоре переданы
            $personid = $data['sbc_contract'][0];
            if ( $personid == 0 )
            {// Требуется создание пользователя
                if ( ! isset($this->_customdata->createdata['user']->email) )
                {// Не указан email для создания персоны
                    $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_person_empty_email_accessdenied', 'participants');
                } else
                {
                    if ( ! $this->dof->storage('persons')->is_access('create') )
                    {// Пользователь не имеет доступа к созданию персоны
                        $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_personcreate_accessdenied', 'participants');
                    } else 
                    {// Проверка уникальности email
                        if ( ! $this->dof->storage('persons')->is_email_unique($this->_customdata->createdata['user']->email) )
                        {
                            $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_personcreate_emailnotunique', 'participants');
                        }
                    }
                }
            }
            $contractid = $data['sbc_contract'][1];
            if ( $contractid == 0 )
            {// Требуется создание договора
                if ( ! $this->dof->storage('contracts')->is_access('create') )
                {// Пользователь не имеет доступа к созданию договоров
                    $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_contractcreate_accessdenied', 'participants');
                }
            } else
            {// Использование договора для создания подписки
                if ( ! $this->dof->storage('contracts')->is_access('use', $contractid) )
                {// Пользователь не имеет доступа к созданию договоров
                    $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_contractuse_accessdenied', 'participants');
                }
            }
        }
        
        // Проверки для программы
        if ( ! isset($data['sbc_programm'][0]) || ! isset($data['sbc_programm'][1]) || ! isset($data['sbc_programm'][2]) )
        {
            $errors['hidden'] = $this->dof->get_string('students_fastcreate_error_data_not_found', 'participants');
        } else 
        {
            $programmid = $data['sbc_programm'][0];
            if ( $programmid == 0 )
            {// Программа не указана
                $errors['sbc_programm'] = $this->dof->get_string('students_fastcreate_error_programm_not_set', 'participants');
            } else
            {// Проверка доступа
                if ( ! $this->dof->storage('programms')->is_access('use', $programmid) )
                {// Пользователь не имеет доступа к указанной программе
                    $errors['sbc_programm'] = $this->dof->get_string('students_fastcreate_error_programmuse_accessdenied', 'participants');
                }
            }
            $agropid = $data['sbc_programm'][1];
            if ( $agropid > 0 )
            {
                // Проверка доступа на использование целевой группы
                if ( ! $this->dof->storage('agroups')->is_access('use', $agropid) )
                {// Пользователь не имеет доступа к указанной группе
                    $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_agroupuse_accessdenied', 'participants');
                } else
                {
                    // Проверка на совпадение группы и программы
                    if ( $programmid != $this->dof->storage('agroups')->get_field($agropid, 'programmid') )
                    {// Указанная программа не совпадает с программой выбранной группы
                        $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_agroup_compare_programm', 'participants');
                    }
                }
            }
            $agenum = $data['sbc_programm'][2];
            if ( $agenum > $this->dof->storage('programms')->get_field($programmid, 'agenums') )
            {// Недопустимый номер периода
                $errors['sbc_contract'] = $this->dof->get_string('students_fastcreate_error_agenum_overlimit', 'participants');
            }
        }
        
        // Проверка доступа к созданию подписки на программу
        if ( ! $this->dof->storage('programmsbcs')->is_access('create') )
        {
            $errors['hidden'] = $this->dof->get_string('students_fastcreate_error_personcreate_accessdenied', 'participants');
        }
        
        if ( (bool)$errors )
        {
            $this->hasmessages = true;
        }
        
        // Вернуть ошибки валидации элементов
        return $errors;
    }

    /**
     * Заполнение полей формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
            $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            // Определение данных
            $departmentid = $formdata->departmentid;
            $personid = $formdata->sbc_contract[0];
            $contractid = $formdata->sbc_contract[1];
            $programmid = $formdata->sbc_programm[0];
            $agroupid = $formdata->sbc_programm[1];
            $agenum = $formdata->sbc_programm[2];
            
            $currentperson = $this->dof->storage('persons')->get_bu();
            
            // Обработка переданной персоны
            if ( $personid <= 0 )
            {// Создание новой персоны
                if ( isset($this->createdata['user']) )
                {// Данные для создания пользователя определены
                    
                    // Создание персоны
                    try {
                        // Формирование данных для создания персоны
                        $data = clone $this->createdata['user'];
                        $data->departmentid = $departmentid;
                        if ( isset($data->phone) )
                        {
                            $data->phonecell = $data->phone;
                            unset($data->phone);
                        }
                        // Сохранение персоны
                        $personid = (int)$this->dof->storage('persons')->save($data);
                    } catch ( dof_exception_dml $e )
                    {// Ошибка во время создания персоны
                        $this->hasmessages = true;
                        $this->errors[] = $this->dof->get_string($e->errorcode, 'persons', null, 'storage');
                        return false;
                    }
                } else 
                {// Данные для создания пользователя не определены
                    $this->hasmessages = true;
                    $this->errors[] = $this->dof->get_string('students_fastcreate_error_data_not_found', 'participants');
                    return false;
                }
            }
            
            // Обработка переданного договора
            if ( $contractid <= 0 )
            {
                // Создание нового договора
                try {
                    $data = new stdClass();
                    $data->sellerid = $currentperson->id;
                    $data->studentid = $personid;
                    $data->clientid = $personid;
                    $data->departmentid = $departmentid;
                    $contractid = (int)$this->dof->storage('contracts')->save($data);
                    // Перевод договора из шаблона в статус "Новый"
                    $this->dof->workflow('contracts')->change($contractid, 'new');
                } catch ( dof_exception_dml $e )
                {// Ошибка во время создания персоны
                    $this->hasmessages = true;
                    $this->errors[] = $this->dof->get_string($e->errorcode, 'contracts', null, 'storage');
                    return false;
                }
            }
            
            // Обработка переданной программы
            if ( ! $this->dof->storage('programms')->is_exists((int)$programmid) )
            {// Указанная программа не найдена
                $this->hasmessages = true;
                $this->errors[] = $this->dof->get_string('students_fastcreate_error_programm_not_found', 'participants');
                return false;
            }
            
            // Поиск дублей подписки
            $params = [];
            $params['status'] = array_keys((array)$this->dof->workflow('programmsbcs')->get_meta_list('real'));
            $params['programmid'] = $programmid;
            $params['contractid'] = $contractid;
            if ( $this->dof->storage('programmsbcs')->get_records($params, '', 'id') )
            {// Подписка уже существует
                $this->hasmessages = true;
                $this->errors[] = $this->dof->get_string('students_fastcreate_error_programmbc_exist', 'participants');
                return false;
            }
            
            // Создание подписки
            try {
                $programmbc = new stdClass();
                $programmbc->contractid = (int)$contractid;
                $programmbc->programmid = (int)$programmid;
                if ( $agroupid > 0 )
                {
                    $programmbc->agroupid = (int)$agroupid;
                } else 
                {
                    $programmbc->agroupid = 0;
                }
                $programmbc->agenum = (int)$agenum;
                $programmbc->departmentid   = $departmentid;
                
                $this->dof->storage('programmsbcs')->save($programmbc);
                return true;
            } catch ( dof_exception_dml $e )
            {// Ошибка во время создания подписки
                $this->hasmessages = true;
                $this->errors[] = $this->dof->get_string($e->errorcode, 'programmsbcs', null, 'storage');
                return false;
            }
        }
    }
    
    /**
     * Инициализация данных для создания подписки
     * 
     * Запорняет массивы пользователей, договоров, групп и программ данными с проверкой прав по ним
     * 
     * @param void
     */
    private function init_data($data)
    {
        // Определение доступных договоров на обучение и персон
        if ( isset($data['user']) )
        {
            // Получение списка пользователей, подходящих по фильтру
            $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
            // Получение персон на основе фильтра. Статусы не поддерживаются методом.
            $this->persons = (array)$this->dof->storage('persons')->get_list_extendedsearch($data['user']);
            foreach ( $this->persons as $id => &$person )
            {
                // Проверка доступа к использованию персоны для создания подписки и контракта
                if ( $this->dof->storage('persons')->is_access('use', $id) )
                {// Доступ есть
                    $person = $this->dof->storage('persons')->get_record([
                        'id' => $id, 
                        'status' => array_keys($statuses)
                    ]);
                    if ( empty($person) )
                    {// Персона не в актуальном статусе
                        unset($this->persons[$id]);
                    }
                } else 
                {// Доступа к персоне нет
                    unset($this->persons[$id]);
                }
            }
            // Получение списка договоров, которые можно использовать для создания подписки
            $contractstatuses = (array)$this->dof->workflow('contracts')->get_meta_list('actual');
            $this->contracts = (array)$this->dof->storage('contracts')->get_records([
                    'studentid' => array_keys($this->persons),
                    'status' => array_keys($contractstatuses)
                ],'', 'id, num, studentid, date'
            );
            foreach ( $this->contracts as $id => &$contract )
            {
                // Проверка доступа к использованию персоны для создания подписки и контракта
                if ( ! $this->dof->storage('contracts')->is_access('use', $id) )
                {// Доступа к договору нет
                    unset($this->contracts[$id]);
                }
            }
        }
        
        // Определение доступных программ
        if ( isset($data['programm']) )
        {// Фильтр программы определен
            // Добавить фильтрацию по статусам программ
            $data['programm']->status = (array)$this->dof->workflow('programms')->get_meta_list('real');
            // Получение списка программ на основе данных
            $this->programms = (array)$this->dof->storage('programms')->
                get_programms_by_filter($data['programm'], ['returnids' => false]);
        } else
        {// Получение полного списка программ
            // Добавить фильтрацию по статусам программ
            $status = (array)$this->dof->workflow('programms')->get_meta_list('real');
            // Получение полного списка программ
            $this->programms = (array)$this->dof->storage('programms')->
                get_records(['status' => array_keys($status)]);
        }
        foreach ( $this->programms as $id => &$programm )
        {
            // Проверка доступа к использованию персоны для создания подписки и контракта
            if ( ! $this->dof->storage('programms')->is_access('use', $id) )
            {// Доступа к договору нет
                unset($this->programms[$id]);
            }
        }
        
        // Определение групп для фильтрации
        if ( isset($data['agroup']) )
        {// Фильтр групп определен
            // Получение списка групп
            $data['agroup']->status = (array)$this->dof->workflow('agroups')->get_meta_list('actual');
            $this->agroups = (array)$this->dof->storage('agroups')->
                get_agroups_by_filter($data['agroup'], ['returnids' => false]);
            foreach ( $this->agroups as $id => &$agroup )
            {
                // Проверка доступа к использованию
                if ( ! $this->dof->storage('programms')->is_access('use', $id) )
                {// Доступа к договору нет
                    unset($this->programms[$id]);
                }
            }
        }
    }
    
    /**
     * Инициализация выпадающего списка с выбором договора на обучение
     * 
     * @param object $sbc_contract - Объект поля hierselect
     * 
     * @return void
     */
    private function sbc_contract_hierselect_init($sbc_contract)
    {
        // Получение ссылки на объект формы
        $mform =& $this->_form;
        
        // Получение пользовательской временной зоны
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        // Инициализация массивов опций для иерархического списка
        $contracts = [];
        $persons = [];
        
        // Определение прав доступа
        $access_createperson = ( $this->dof->storage('persons')->is_access('create') && 
                                 isset($this->_customdata->createdata['user']->email) );
        $access_createcontract = $this->dof->storage('contracts')->is_access('create');
        
        // Распределение списка договоров по персонам
        foreach ( $this->contracts as $id => $contract )
        {
            $stringvars = new stdClass();
            $stringvars->date = dof_userdate($contract->date, '%d.%m.%Y', $usertimezone, false);
            $stringvars->num = $contract->num;
            $contracts[$contract->studentid][$id] = $this->dof->
                get_string('students_fastcreate_contract_contract_name', 'participants', $stringvars);
        }
        // Добавление возможности создать новый договор
        if ( $access_createcontract )
        {
            // Добавление опции создания нового договора для всех найденных персон
            foreach ( $this->persons as $id => $person )
            {
                // Сортировка договоров персоны
                if ( isset($contracts[$id]) )
                {// У персоны есть договоры для выбора
                    asort($contracts[$id]);
                } else 
                {
                    $contracts[$id] = [];
                }
                
                // Добавление опции создания договора
                $contracts[$id] = [0 => $this->dof->get_string('students_fastcreate_contract_new_contract', 'participants')] + $contracts[$id];
            }
            // Добавление опции создания нового договора для новой персоны
            if ( $access_createperson )
            {
                $contracts[0] = [ 0 => $this->dof->get_string('students_fastcreate_contract_new_contract', 'participants') ];
            }
        }
        
        // Добавление в список только тех персон, у кого есть хотя бы одна опция выбора договора
        foreach ( $contracts as $personid => $personcontracts )
        {
            if ( $personid == 0 && $access_createperson )
            {// Опция создания новой персоны
                $persons[$personid] = $this->dof->get_string('students_fastcreate_contract_new_person', 'participants');
                continue;
            }
            if ( $personid > 0 && isset($this->persons[$personid]) )
            {// Добавление персоны для выбора ее договоров
                $persons[$personid] = $this->dof->storage('persons')->get_fullname($this->persons[$personid]);
            }
        }
        
        // Сортировка списка персон
        asort($persons);
        
        // Добавление опций в список
        $sbc_contract->setOptions([$persons, $contracts]);
        
        // Проверка на возможность выбора хотя бы одного договора 
        if ( empty($persons) )
        {// Список персон пуст, невозможно выбрать договор для подписки
            $this->canconfirm = false;
        } else
        {// Проверка наличия договоров
            $this->canconfirm = false;
            foreach ( $persons as $personid => $person )
            {
                if ( isset($contracts[$personid]) && ! empty($contracts[$personid]) )
                {// Варианты выбора договора найдены
                    $this->canconfirm = true;
                    break;
                }
            }
        }

        // Установка персоны по-умолчанию
        reset($this->persons);
        if ( key($persons) == 0 )
        {// Нужно найти значение по-умолчанию с персоной
            next($persons);
        }
        $defaultperson = (int)key($persons);

        // Установка договора персоны по-умолчанию
        if ( isset($contracts[$defaultperson]) && ! empty($contracts[$defaultperson]) )
        {
            reset($contracts[$defaultperson]);
            if ( key($contracts[$defaultperson]) == 0 )
            {// Нужно найти значение по-умолчанию с договором, а не с добавлением нового
                next($contracts[$defaultperson]);
            }
            $defaultcontract = (int)key($contracts[$defaultperson]);
        } else 
        {
            $defaultcontract = 0;
        }
        
        // Установка значений по-умолчанию
        $mform->setDefault('sbc_contract', [$defaultperson, [$defaultperson => $defaultcontract]]);
    }
    
    /**
     * Инициализация выпадающего списка с выбором программы, группы и параллели
     *
     * @param object $sbc_programm - Объект поля hierselect
     *
     * @return void
     */
    private function sbc_programm_hierselect_init($sbc_programm)
    {
        // Получение ссылки на объект формы
        $mform =& $this->_form;
    
        // Инициализация массивов опций для иерархического списка
        $programms = [];
        $programmsagroups = [];
        $programmsagroupagenum = [];
        
        // Массив с информацией о принадлежности групп к параллели
        $buferagroupagenum = [];
        
        // Заполнение первого уровня иерархического выпадающего списка - Программы
        foreach ( $this->programms as $id => $programm )
        {
            $programms[$id] = $programm->name;
            
            // Добавление общих пунктов выбора группы для каждой программы
            $programmsagroups[$id][-1] = $this->dof->get_string('students_fastcreate_programm_no_agroup', 'participants');
        }
        // Сортировка программ
        asort($programms);
        
        // Заполнение второго уровня иерархического выпадающего списка - Группы
        if ( $this->agroups === null )
        {// Нет фильтрации по группам - вывод полного списка групп по программе
            
            // Получение статусов групп
            $status = (array)$this->dof->workflow('agroups')->get_meta_list('real');
            // Добавление для каждой программы списка групп
            foreach ( $this->programms as $id => $programm )
            {
                $agroups = (array)$this->dof->storage('agroups')->get_records([
                    'programmid' => $id,
                    'status' => array_keys($status)
                ]);
                // Добавление каждой найденной группы
                foreach ( $agroups as $agroup )
                {
                    if ( $this->dof->storage('agroups')->is_access('use', $id) )
                    {// Проверка прав на использование групп
                        // Добавление информации о параллели группы
                        $buferagroupinfo[$agroup->id] = $agroup->agenum;
                        // Добавление в список групп для программы
                        $programmsagroups[$id][$agroup->id] = $agroup->name.' ['.$agroup->code.']';
                    }
                }
            }
        } else
        {// Добавление отфильтрованных групп в списки выбора для программ
            foreach ( $this->agroups as $id => $agroup )
            {
                $buferagroupinfo[$id] = $agroup->agenum;
                $programmsagroups[$agroup->programmid][$id] = $agroup->name.' ['.$agroup->code.']';
            }
        }
        
        // Сортировка второго уровня иерархического списка по имени группы
        foreach ( $programmsagroups as &$agroupselect )
        {
            asort($agroupselect);
            if ( isset($agroupselect[-1]) )
            {// Перемещение опции для выбора без группы в начало списка
                $option = $agroupselect[-1];
                unset($agroupselect[-1]);
                $agroupselect = [-1 => $option] + $agroupselect;
            }
        }
        
        // Заполнение третьего уровня иерархического выпадающего списка - Параллели
        foreach ( $programmsagroups as $programmid => $agroupsselect )
        {
            // Формирование списка для выбора параллелей программы
            $pfullagenums = [];
            if ( isset($this->programms[$programmid]) )
            {// Заполнение параллелями
                for ( $i = 1; $i <= $this->programms[$programmid]->agenums; $i++ )
                {
                    $pfullagenums[$i] = (string)$i;
                }
            }
            
            // Обработка каждой опции выбора группы
            foreach ( $agroupsselect as $agroupid => $agroup )
            {
                // Индивидуальная подписка без группы
                if ( $agroupid < 1 )
                {// Подписка на любую параллель программы
                    $programmsagroupagenum[$programmid][$agroupid] = [
                        0 => $this->dof->get_string('students_fastcreate_programm_no_agenum', 'participants')
                    ] + $pfullagenums;
                    continue;
                }
                
                // Единственная параллель для выбора - параллель группы
                $programmsagroupagenum[$programmid][$agroupid] = [
                    (int)$buferagroupinfo[$agroupid] => (string)$buferagroupinfo[$agroupid]
                ];
            }
        }
        
        // Добавление опций в список
        $sbc_programm->setOptions([$programms, $programmsagroups, $programmsagroupagenum]);
        
        if ( empty($programms) )
        {// Не найдено ни одной программы для выбора
            $this->canconfirm = false;
        } else 
        {// Установка значений по-умолчанию
            reset($programms);
            $defaultprogramm = (int)key($programms);
        }
    }
}

/**
 * Форма детального создания подписки
 */
class dof_im_participants_students_create extends dof_modlib_widgets_form
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
     * ID договора на создание подписки
     *
     * @var string
     */
    protected $conractid = null;
    
    /**
     * URL для возврата после обработки формы
     *
     * @var string
     */
    protected $returnurl = null;
    
    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = (array)$this->_customdata->addvars;
        $this->contractid = $this->_customdata->contractid;
        $this->returnurl = $this->_customdata->returnurl;
        
        // Нормализация GET-параметров
        if ( ! isset($this->addvars['departmentid']) )
        {// Установка текущего подразделения
            $this->addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'returnurl', $this->returnurl);
        $mform->setType('returnurl', PARAM_URL);

        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Договор по подписке
        $contract = $this->dof->storage('contracts')->get($this->contractid);
        $students[$this->contractid] = $this->dof->storage('persons')->
            get_fullname($contract->studentid).'['.$contract->num.']';
        $mform->addElement(
            'select', 
            'sbc_contract', 
            $this->dof->get_string('students_create_contract_title', 'participants'), 
            $students
        );
        $mform->setDefault('sbc_contract', $this->contractid);
        $mform->freeze('sbc_contract');
        
        // Выбор программы, группы и периода для подписки
        $sbc_programm = $mform->addElement(
            'hierselect', 
            'sbc_programm',
            $this->dof->get_string('students_create_programm_title', 'participants'),
            null,
            '<br/>'
        );
        // Инициализация опций иерархического списка
        $this->sbc_programm_hierselect_init($sbc_programm);
        
        // Форма обучения
        $mform->addElement(
            'select', 
            'sbc_eduform', 
            $this->dof->get_string('students_create_eduform_title', 'participants'),
            $this->dof->storage('programmsbcs')->get_eduforms_list()
        );
        $mform->setType('sbc_eduform', PARAM_TEXT);
        
        // Тип обучения
        $mform->addElement(
            'select', 
            'sbc_edutype', 
            $this->dof->get_string('students_create_edutype_title', 'participants'),
            $this->dof->storage('programmsbcs')->get_edutypes_list()
        );
        $mform->setType('sbc_edutype', PARAM_TEXT);
        
        // Свободное посещение
        $mform->addElement(
            'selectyesno', 
            'sbc_freeattendance', 
            $this->dof->get_string('students_create_freeattendance_title', 'participants')
        );
        
        // Начало действия подписки
        $timezone = $this->dof->storage('departments')->get_field($this->addvars['departmentid'], 'zone');
        if ( $timezone === false )
        {
            $timezone = 99;
        }
        
        $options = [];
        $options['startyear'] = dof_userdate(time()-10*365*24*3600, '%Y');
        $options['stopyear']  = dof_userdate(time()+10*365*24*3600, '%Y');
        $options['optional']  = false;
        $options['timezone']  = (float)$timezone;
        $options['hours'] = 12;
        $options['minutes']  = 00;
        $mform->addElement(
            'dof_date_selector', 
            'sbc_startdate', 
            $this->dof->get_string('students_create_startdate_title', 'participants'),
            $options
        );
        $mform->setType('sbc_startdate', PARAM_INT);
        
        // Поправочный зарплатный коэфициент
        $mform->addElement(
            'text', 
            'sbc_salfactor', 
            $this->dof->get_string('students_create_salfactor_title', 'participants')
        );
        $mform->setType('sbc_salfactor', PARAM_TEXT);
        $mform->setDefault('sbc_salfactor', '0.00');
        
        // Начальный период обучения
        $status = array_keys((array)$this->dof->workflow('ages')->get_meta_list('actual'));
        $ages = $this->dof->storage('ages')->get_records(['status' => $status]);
        
        // Фильтрация списка с учетом права доступа
        $select = [0 => $this->dof->get_string('students_create_age_notselect', 'participants')];
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        foreach ( $ages as $id => $age )
        {
            if ( $this->dof->storage('ages')->is_access('use', $id, null, $this->addvars['departmentid']) )
            {
                $stringvars = new stdClass();
                $stringvars->name = $age->name;
                $stringvars->startdate = dof_userdate($age->name, '%d.%m.%Y', $usertimezone, false);
                $stringvars->enddate = dof_userdate($age->name, '%d.%m.%Y', $usertimezone, false);
                $select[$id] = $this->dof->get_string('students_create_age_select', 'participants', $stringvars);
            }
        }

        $mform->addElement(
            'select', 
            'sbc_agestart', 
            $this->dof->get_string('students_create_agestart_title', 'participants'), 
            $select
        );
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('students_create_submit', 'participants')
        );
        $mform->addGroup($group, 'buttons', '', '', false);

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = parent::validation($data, $files);
        
        // Валидация договора
        $contract = $this->dof->storage('contracts')->get($data['sbc_contract']);
        if ( empty($contract) )
        {// Договор не найден
            $errors['sbc_contract'] = $this->dof->get_string('students_create_error_contract_not_found', 'participants');
        } else
        {// Проверка доступа к договору
            if ( ! $this->dof->storage('contracts')->is_access('use', $contract->id, null, $data['departmentid']) )
            {// Пользователь не имеет доступа к использованию договоров
                $errors['sbc_contract'] = $this->dof->get_string('students_create_error_contract_access_denied', 'participants');
            }
        }
        
        // Валидация программы
        if ( ! isset($data['sbc_programm'][0]) || $data['sbc_programm'][0] < 1 )
        {// Программа не установлена
            $errors['sbc_programm'] = $this->dof->get_string('students_create_error_programm_not_set', 'participants');
        } else 
        {// Проверка программы
            $programm = $this->dof->storage('programms')->get($data['sbc_programm'][0]);
            if ( empty($programm) )
            {// Программа не найдена
                $errors['sbc_programm'] = $this->dof->get_string('students_create_error_programm_not_found', 'participants');
            } else
            {// Проверка доступа к договору
                if ( ! $this->dof->storage('programms')->is_access('use', $programm->id, null, $data['departmentid']) )
                {// Пользователь не имеет доступа к использованию программы
                    $errors['sbc_programm'] = $this->dof->get_string('students_create_error_programm_access_denied', 'participants');
                }
            }
        }
        
        if ( ! isset($errors['sbc_programm']) )
        {
            // Валидация группы
            if ( ! isset($data['sbc_programm'][1]) )
            {// Группа не установлена
                $errors['sbc_programm'] = $this->dof->get_string('students_create_error_agroup_not_set', 'participants');
            } else
            {// Проверка группы
                if ( $data['sbc_programm'][1] > 0 )
                {// Группа установлена
                    $agroup = $this->dof->storage('agroups')->get($data['sbc_programm'][1]);
            
                    if ( empty($agroup) )
                    {// Группа не найдена
                        $errors['sbc_programm'] = $this->dof->get_string('students_create_error_agroup_not_found', 'participants');
                    } else
                    {// Проверка доступа к группе
                        if ( ! $this->dof->storage('agroups')->is_access('use', $agroup->id, null, $data['departmentid']) )
                        {// Пользователь не имеет доступа к использованию группы
                            $errors['sbc_programm'] = $this->dof->get_string('students_create_error_programm_access_denied', 'participants');
                        }
                    }
                }
            }
        }
        
        if ( ! isset($errors['sbc_programm']) )
        {
            // Валидация номера параллели
            if ( ! isset($data['sbc_programm'][2]) )
            {// Параллель не установлена
                $errors['sbc_programm'] = $this->dof->get_string('students_create_error_agenum_not_set', 'participants');
            } else
            {// Проверка параллели
                if ( $data['sbc_programm'][2] > $this->dof->storage('programms')->get_field($programm->id, 'agenums') )
                {// Недопустимый номер параллели
                    $errors['sbc_programm'] = $this->dof->get_string('students_create_error_agenum_overlimit', 'participants');
                }
                if ( isset($agroup->agenum) && $agroup->agenum != $data['sbc_programm'][2] )
                {// Параллель не принадлежит группе
                    $errors['sbc_programm'] = $this->dof->get_string('students_create_error_agenum_overagroup', 'participants');
                }
            }
        }
        
        // Валидация учебного периода
        if ( isset($data['sbc_agestart']) && $data['sbc_agestart'] )
        {// Проверка периода
            $age = $this->dof->storage('ages')->get($data['sbc_agestart']);
            if ( empty($age) )
            {// Период не найден
                $errors['sbc_agestart'] = $this->dof->get_string('students_create_error_age_not_found', 'participants');
            } else
            {// Проверка доступа к периоду
                if ( ! $this->dof->storage('ages')->is_access('use', $age->id, null, $data['departmentid']) )
                {// Пользователь не имеет доступа к использованию периода
                    $errors['sbc_agestart'] = $this->dof->get_string('students_create_error_age_access_denied', 'participants');
                }
            }
        }
       
        // Лимит объектов
        if ( ! $this->dof->storage('config')->get_limitobject('programmsbcs', $data['departmentid'] ) )
        {
            $errors['hidden'] = $this->dof->get_string('students_create_error_sbc_overlimit', 'participants');
        }

        // Вернуть ошибки валидации элементов
        return $errors;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
            )
        {// Обработка данных формы
            
            // Данные для сохранения
            $sbc = new stdClass();
            $sbc->contractid = $formdata->sbc_contract;
            $sbc->programmid = $formdata->sbc_programm[0];
            $sbc->edutype = $formdata->sbc_edutype;
            $sbc->eduform = $formdata->sbc_eduform;
            $sbc->sbc_freeattendance = $formdata->sbc_freeattendance;
            $sbc->agroupid = $formdata->sbc_programm[1];
            $sbc->agenum = $formdata->sbc_programm[2];
            $sbc->departmentid = $formdata->departmentid;
            $sbc->datestart = $formdata->sbc_startdate['timestamp'];
            
            // Поиск дублей подписки
            $params = [];
            $params['status'] = array_keys((array)$this->dof->workflow('programmsbcs')->get_meta_list('real'));
            $params['programmid'] = $sbc->programmid;
            $params['contractid'] = $sbc->contractid;
            if ( $this->dof->storage('programmsbcs')->get_records($params, '', 'id') )
            {// Подписка уже существует
                $this->errors[] = $this->dof->get_string('students_create_error_programmbc_exist', 'participants');
                return false;
            }

            // Создание подписки
            try {
                $programmsbcsid = $this->dof->storage('programmsbcs')->save($sbc);
                
                // Работа с историей обучения
                // @todo Разобраться зачем нужен этот хак и вынести его в эвенты
                if ( $history = $this->dof->storage('learninghistory')->get_first_learning_data($programmsbcsid) )
                {
                    $this->dof->storage('learninghistory')->delete($history->id);
                }
                if ( isset($formdata->sbc_agestart) && $formdata->sbc_agestart )
                {// Начало истории обучения
                    $cpassed = new stdClass();
                    $cpassed->programmsbcid = $programmsbcsid;
                    $cpassed->ageid         = $formdata->sbc_agestart;
                    $cpassed->status        = 'active';
                    $this->dof->storage('learninghistory')->add($cpassed);
                }
                
                // Редирект
                redirect($this->returnurl);
                
            } catch ( dof_exception_dml $e )
            {// Ошибка во время создания подписки
                $this->errors[] = $this->dof->get_string($e->errorcode, 'programmsbcs', null, 'storage');
                return false;
            }
            
            
        }
    }

    /**
     * Инициализация выпадающего списка с выбором программы, группы и параллели
     *
     * @param object $sbc_programm - Объект поля hierselect
     *
     * @return void
     */
    private function sbc_programm_hierselect_init($sbc_programm)
    {
        // Получение ссылки на объект формы
        $mform =& $this->_form;

        // Инициализация массивов опций для иерархического списка
        $programms = [];
        $programmsagroups = [];
        $programmsagroupagenum = [];

        // Массив с информацией о принадлежности групп к параллели
        $buferagroupagenum = [];

        // Заполнение первого уровня иерархического выпадающего списка - Программы
        $status = array_keys((array)$this->dof->workflow('programms')->get_meta_list('active'));
        $allprogramms = $this->dof->storage('programms')->get_records(['status'=> $status], 'name ASC');
        // Преобразование в выпадающий список 
        $programms = $this->dof_get_select_values($allprogramms, true, 'id', ['name', 'code']);
        // Фильтрация с учетом прав доступа
        $permissions = [['plugintype' => 'storage', 'plugincode' => 'programms', 'code' => 'use']];
        $programms = $this->dof_get_acl_filtered_list($programms, $permissions);
        
        // Обработка программ
        foreach ( $programms as $id => $programm )
        {
            if ( ! empty($id) )
            {
                // Добавление общих пунктов выбора группы для каждой программы
                $programmsagroups[$id][0] = $this->dof->get_string('students_fastcreate_programm_no_agroup', 'participants');
            }
        }

        // Заполнение второго уровня иерархического выпадающего списка - Группы
        
        $status = (array)$this->dof->workflow('agroups')->get_meta_list('real');
        // Добавление для каждой программы списка групп
        foreach ( $programms as $id => $programm )
        {
            $agroups = (array)$this->dof->storage('agroups')->get_records(
                [
                    'programmid' => $id,
                    'status' => array_keys($status)
                ],
                'name ASC, code ASC'
            );
            
            // Добавление каждой найденной группы
            foreach ( $agroups as $agroup )
            {
                if ( $this->dof->storage('agroups')->is_access('use', $id) )
                {// Проверка прав на использование групп
                    // Добавление информации о параллели группы
                    $buferagroupinfo[$agroup->id] = $agroup->agenum;
                    // Добавление в список групп для программы
                    $programmsagroups[$id][$agroup->id] = $agroup->name.' ['.$agroup->code.']';
                }
            }
        }

        // Заполнение третьего уровня иерархического выпадающего списка - Параллели
        foreach ( $programmsagroups as $programmid => $agroupsselect )
        {
            // Формирование списка для выбора параллелей программы
            $pfullagenums = [];
            if ( isset($allprogramms[$programmid]) )
            {// Заполнение параллелями
                for ( $i = 1; $i <= $allprogramms[$programmid]->agenums; $i++ )
                {
                    $pfullagenums[$i] = (string)$i;
                }
            }
            

            // Обработка каждой опции выбора группы
            foreach ( $agroupsselect as $agroupid => $agroup )
            {
                // Индивидуальная подписка без группы
                if ( $agroupid < 1 )
                {// Подписка на любую параллель программы
                    $programmsagroupagenum[$programmid][0] = $pfullagenums;
                    continue;
                }

                // Единственная параллель для выбора - параллель группы
                $programmsagroupagenum[$programmid][$agroupid] = [
                    (int)$buferagroupinfo[$agroupid] => (string)$buferagroupinfo[$agroupid]
                ];
            }
        }

        // Добавление опций в список
        $sbc_programm->setOptions([$programms, $programmsagroups, $programmsagroupagenum]);
    }
}

/**
 * Форма выбора договора для детального создания подписки
 *
 */
class dof_im_participants_students_create_select_contract extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * GET-параметры текущей страницы
     *
     * @var array
     */
    protected $addvars = [];
    
    /**
     * URL для возврата после обработки формы
     *
     * @var string
     */
    protected $returnurl = '';
    
    /**
     * Имя переменной, которая будет добавлена в returnurl
     * Содержит результат выбора договора
     *
     * @var string
     */
    protected $cidparam = 'contractid';
    
    /**
     * Имя переменной, которая будет добавлена в returnurl
     * Содержит результат выбора договора
     *
     * @var string
     */
    protected $pidparam = 'personid';

    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->returnurl = $this->_customdata->returnurl;
        $this->addvars = $this->_customdata->addvars;
        if ( isset($this->_customdata->cidparam) && ! empty($this->_customdata->cidparam) )
        {// Имя параметра переопределено
            $this->cidparam = $this->_customdata->cidparam;
        }
        if ( isset($this->_customdata->pidparam) && ! empty($this->_customdata->pidparam) )
        {// Имя параметра переопределено
            $this->pidparam = $this->_customdata->pidparam;
        }
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
            'header',
            'form_header',
            $this->dof->get_string('students_create_select_contract_header', 'participants')
        );
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        $access_contractcreate = $this->dof->storage('contracts')->
            is_access('create', null, null, (int)$this->addvars['departmentid']);
        $hascontractplugin = $this->dof->plugin_exists('im', 'sel');
        if ( $access_contractcreate && $hascontractplugin )
        {// Доступ к созданию нового договора открыт
            
            // Создать новый договор
            $mform->addElement(
                'radio',
                'contractselect',
                null,
                $this->dof->get_string('students_create_select_contract_contractselect_new', 'participants'),
                0
            );
            
            $access_personcreate = $this->dof->storage('persons')->
                is_access('create', null, null, (int)$this->addvars['departmentid']);
            $haspersonplugin = $this->dof->plugin_exists('im', 'persons');
            if ( $access_personcreate && $haspersonplugin )
            {// Доступ к созданию новой персоны открыт
                // Создать новую персону
                $mform->addElement(
                    'radio',
                    'personselect',
                    null,
                    $this->dof->get_string('students_create_select_contract_personselect_new', 'participants'),
                    0
                );
            }
            
            // Выбрать имеющуюся персону
            $mform->addElement(
                'radio',
                'personselect',
                null,
                $this->dof->get_string('students_create_select_contract_personselect_select', 'participants'),
                1
            );
            // Поиск существующей персоны
            $options = [];
            $options['plugintype']   = 'storage';
            $options['plugincode']   = 'persons';
            $options['sesskey']      = sesskey();
            $options['type']         = 'autocomplete';
            $options['departmentid'] = $this->addvars['departmentid'];
            $options['querytype']    = 'persons_list';
            $attributes = [
                'placeholder' => $this->dof->get_string('students_create_select_contract_personfind_placeholder', 'participants')
            ];
            $mform->addElement(
                'dof_autocomplete',
                'personfind',
                $this->dof->get_string('students_create_select_contract_personfind_label', 'participants'),
                $attributes,
                $options
            );
            
            // Правила отключения полей
            $mform->disabledIf('personselect', 'contractselect', 'neq', 0);
            $mform->disabledIf('personfind', 'contractselect', 'neq', 0);
            $mform->disabledIf('personfind', 'personselect', 'neq', 1);
            
            // Значения по-умолчанию
            $mform->setDefault('personselect', 1);
        }
        
        // Выбрать договор
        $mform->addElement(
            'radio',
            'contractselect',
            null,
            $this->dof->get_string('students_create_select_contract_contractselect_select', 'participants'),
            1
        );
        
        // Поиск существующего договора
        $options = [];
        $options['plugintype']   = 'storage';
        $options['plugincode']   = 'contracts';
        $options['sesskey']      = sesskey();
        $options['type']         = 'autocomplete';
        $options['departmentid'] = 0;
        $options['querytype']    = 'contracts_list';
        $attributes = [
            'placeholder' => $this->dof->get_string('students_create_select_contract_contractfind_placeholder', 'participants')
        ];
        $mform->addElement(
            'dof_autocomplete',
            'contractfind',
            $this->dof->get_string('students_create_select_contract_contractfind_label', 'participants'),
            $attributes,
            $options
        );
        
        // Правила отключения полей
        $mform->disabledIf('contractfind', 'contractselect', 'neq', 1);
        
        // Значения по-умолчанию
        $mform->setDefault('contractselect', 1);
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('students_create_select_contract_continue', 'participants')
        );
        $mform->addGroup($group, 'buttons', '', '', false);

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = parent::validation($data, $files);

        if ( ! isset($data['contractselect']) || ! isset($data['personselect']) )
        {// Не указано действие
            $errors['hidden'] = $this->dof->get_string(
                'students_create_select_contract_error_action_not_set', 
                'participants'
            );
            return $errors;
        }
        
        if ( (bool)$data['contractselect'] )
        {// Процесс выбора существующего договора
            if ( ! isset($data['contractfind']['id']) || empty($data['contractfind']['id']) )
            {// Договор не выбран
                $errors['contractfind'] = $this->dof->get_string(
                    'students_create_select_contract_error_contractfind_not_set', 
                    'participants'
                );
            } else 
            {// Договор указан
                // Проверка доступа к договору
                $access = $this->dof->storage('contracts')->
                    is_access('use', $data['contractfind']['id'], null, (int)$data['departmentid']);
                if ( ! $access )
                {// Нет доступа к использованию текущего договора
                    $errors['contractfind'] = $this->dof->get_string(
                        'students_create_select_contract_error_contractfind_access_use', 
                        'participants'
                    );
                }
            }
        } else
        {// Процесс создания нового договора
            // Проверка доступа к созданию договора
            $access = $this->dof->storage('contracts')->
                is_access('create', null, null, (int)$data['departmentid']);
            if ( ! $access )
            {// Нет доступа к созданию нового договора
                $errors['hidden'] = $this->dof->get_string(
                    'students_create_select_contract_error_contractfind_access_create', 
                    'participants'
                );
            } else 
            {// Доступ есть
                // Указание персоны, для которой будет создан договор
                if ( (bool)$data['personselect'] )
                {// Создание договора для выбранной персоны
                    if ( ! isset($data['personfind']['id']) || empty($data['personfind']['id']) )
                    {// Персона не выбрана 
                        $errors['personfind'] = $this->dof->get_string(
                            'students_create_select_contract_error_personfind_not_set', 
                            'participants'
                        );
                    } else
                    {// Персона указана
                        // Проверка доступа к персоне
                        $access = $this->dof->storage('persons')->
                            is_access('use', $data['personfind']['id'], null, (int)$data['departmentid']);
                        if ( ! $access )
                        {// Нет доступа к использованию текущего договора
                            $errors['personfind'] = $this->dof->get_string(
                                'students_create_select_contract_error_personfind_access_use', 
                                'participants'
                            );
                        }
                    }
                } else
                {// Процесс создания новой персоны и нового договора
                    $access = $this->dof->storage('persons')->
                        is_access('create', null, null, (int)$data['departmentid']);
                    if ( ! $access )
                    {// Нет доступа к созданию нового пользователя
                        $errors['hidden'] = $this->dof->get_string(
                            'students_create_select_contract_error_personfind_access_create', 
                            'participants'
                        );
                    }
                }
            }
        }

        // Вернуть ошибки валидации элементов
        return $errors;
    }

    /**
     * Заполнение полей формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    }

    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
            $this->is_validated() && $formdata = $this->get_data()
            )
        {// Обработка данных формы
            // Формирование URL для редиректа
            $parsedurl = parse_url($this->returnurl);
            $query = explode('&', $parsedurl['query']);
            if ( (bool)$formdata->contractselect )
            {// Выбор договора
                $query[] = $this->cidparam.'='.$formdata->contractfind['id'];
            } else
            {// Создание договора
                $query[] = $this->cidparam.'=0';
                if ( (bool)$formdata->personfind )
                {// Выбор персоны
                    $query[] = $this->pidparam.'='.$formdata->personfind['id'];
                } else
                {// Создание персоны
                    $query[] = $this->pidparam.'=0';
                }
            }
            $query = implode('&', $query);
            $url = $parsedurl['path'].'?'.$query;
            
            redirect($url);
        }
    }
}

/**
 * Форма просмотра и дозаполнения персон, прилинкованных к договору
 */
class dof_im_participants_students_create_contract_persons_contract extends dof_im_persons_edit_form
{
    /**
     * Инициализация базовых данных формы
     */
    protected function init()
    {
        // Основной процесс инициализации
        parent::init();
        
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->contractid = $this->_customdata->contractid;
    
        // Скрытые поля
        $mform->addElement('hidden', 'contractid', $this->contractid);
        $mform->setType('contractid', PARAM_INT);
    }
    
    /**
     * Дополнительный обработчик формы
     * 
     * {@inheritDoc}
     * @see dof_im_persons_edit_form::process_after_persons()
     */
    protected function process_after_persons($formdata)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Базовые данные для обработчика
        $studentid = $this->persons['added']['studentid'];
        $clientid = 0;
        if ( isset($this->persons['added']['clientid']) )
        {
            $clientid = $this->persons['added']['clientid'];
        }
            
        // Обновление договора
        $contract = new stdClass();
        $contract->id = $this->contractid;
        $contract->studentid = $studentid;
        if ( $clientid )
        {// Законный представитель указан
            $contract->clientid = $clientid;
        } else 
        {// Законный представитель является студентом
            $contract->clientid = $studentid;
        }

        // Сохранение договора
        try {
            $this->dof->storage('contracts')->save($contract);
        } catch ( dof_exception_dml $e )
        {// Ошибка сохранения
            $this->errors[] = $this->dof->
                get_string('error_save', 'sel', $this->dof->get_string('m_contract', 'sel')
            );
            return;
        }
    }
}

/**
 * Форма быстрого создания подписки
 *
 * Позволяет выбрать пользователя, программу и группу
 *
 */
class dof_im_participants_students_import extends dof_modlib_widgets_form
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
    protected $returnurl = '';
    
    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;

        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->returnurl = $this->_customdata->returnurl;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'filename', '');
        $mform->setType('filename', PARAM_RAW_TRIMMED);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        $mform->addElement(
            'header', 
            'import_header', 
            $this->dof->get_string('form_students_import_header', 'participants')
        );

        // Файл импорта
        $options = [
            'accepted_types' => '.csv'
        ];
        $mform->addElement(
            'filepicker', 
            'import_file', 
            $this->dof->get_string('form_students_import_file_label', 'participants'),
            null,
            $options
        );
        $mform->setType('import_file', PARAM_FILE);
        $mform->addRule('import_file', null, 'required');
        
        // Разделитель
        $delimiters = $this->get_delimiter_select();
        $mform->addElement(
            'select', 
            'import_delimiter', 
            $this->dof->get_string('form_students_import_delimiter_label', 'participants'), 
            $delimiters
        );
        $mform->setDefault('import_delimiter', 'semicolon');
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'check',
            $this->dof->get_string('form_students_import_check', 'participants')
        );
        $group[] = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('form_students_import_submit', 'participants')
        );
        $mform->addGroup($group, 'buttons', '', '', false);

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
    }

    /**
     * Проверки введенных значений в форме
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = parent::validation($data, $files);

        // Вернуть ошибки валидации элементов
        return $errors;
    }

    /**
     * Заполнение полей формы данными
     */
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
    }

    /**
     * Получить разделители для выпадающего списка
     */
    private function get_delimiter_select()
    {
        $delimiters = [
            'comma' => ',',
            'semicolon' => ';',
            'colon' => ':'
        ];
        return $delimiters;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return null|array
     */
    public function process()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            
            $currenttime = dof_userdate(time(), '%Y_%m_%d_%I:%M:%S', 99, false);
            if ( empty($formdata->filename) )
            {// Загрузка файла импорта в систему
                // Сохранение файла импорта
                $filename = $this->get_new_filename('import_file');
                if ( $filename )
                {// Имя получено
                    $savedname = $currenttime.'_'.$filename;
                    $logname = $savedname;
                } else
                {// Имя будет сгенерировано
                    $savedname = '';
                    $logname = '';
                }
                $content = $this->get_file_content('import_file');
                $filename = $this->dof->sync('import')->save_importfile($content, (string)$savedname);
                unset($content);
                $field = $mform->getElement('filename')->setValue($filename);
            } else 
            {// Файл уже был загружен в систему
                $filename = $formdata->filename;
                $logname = $currenttime.'_'.$this->get_new_filename('import_file');
            }
            
            // Получение адреса файла лога по процессу импорта данных из файла
            if ( empty($logname) )
            {// Сформировать имя лога на основе сгенерированного имени файла
                $logname = $currenttime.'_'.$filename;
            }
            $logfilepath = $this->dof->plugin_path('sync', 'import', '/dat/'.$logname);
            
            // Получить путь до файла импорта
            $importfilepath = $this->dof->sync('import')->get_importfile_path($filename);
            
            // Попытка проведения импорта
            try {
                // Опции импорта
                $importoptions = [];
                $delimiters = $this->get_delimiter_select();
                $delimiter = $delimiters[$formdata->import_delimiter];
                $importoptions['delimiter'] = $delimiter;
                if ( isset($formdata->check) )
                {// Процесс симуляции импорта для определения ошибок в парсинге
                    $importoptions['simulation'] = true;
                } else 
                {// Стандартный процесс
                    $importoptions['simulation'] = false;
                }
                
                // Процесс импорта
                $resultdata = $this->dof->sync('import')->import(
                    'programmsbcs', 
                    $importfilepath, 
                    $logfilepath, 
                    $importoptions
                );
                
                // Результат обработки
                return $resultdata;
                
            } catch ( dof_exception_file $e )
            {// Ошибка файла импорта
                $this->dof->messages($e->errorcode, 'error');
            } catch ( dof_exception $e )
            {// Критическая ошибка импорта
                $this->dof->messages($e->errorcode, 'error');
            }
        }
        return null;
    }
    
    /**
     * Сформировать таблицу с результатами импорта подписок на программы
     * 
     * @param array $importstatistic - Массив с результатами импорта
     * 
     * @return string - HTML-код с данными импорта
     */
    public function table_importstatistic($importstatistic)
    {
        // ПОЛУЧЕНИЕ БАЗОВЫХ ПАРАМЕТРОВ
        $currentperson = $this->dof->storage('persons')->get_bu();
        $currenturl = $this->dof->modlib('nvg')->get_url()->out(false);
        $html = '';
        
        // НОРМАЛИЗАЦИЯ ЗНАЧЕНИЙ
        if ( ! isset($options['addvars']) )
        {
            $options['addvars'] = [];
        }
        if ( ! isset($options['addvars']['departmentid']) )
        {
            $options['addvars']['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        }
        
        // ПОСТРОЕНИЕ ТАБЛИЦЫ
        // Рендер таблицы
        $table = new stdClass();
        $table->tablealign = 'center';
        $table->cellpadding = 0;
        $table->cellspacing = 0;
        $table->head = [
            'row' => $this->dof->get_string('table_import_students_header_row', 'participants'),
            'status' => $this->dof->get_string('table_import_students_header_status', 'participants'),
            'actions' => $this->dof->get_string('table_import_students_header_actions', 'participants')
        ];
        $table->data = [];
        // Формирование строк таблицы
        $rownumber = 0;
        $counterrors = 0;
        foreach ( $importstatistic as $row )
        {
            $data = [];
            // Номер строки
            $data['row'] = ++$rownumber;
            
            // Отчет по импорту строки
            $programmsbcreport = $row->report['programmsbc'];
            
            // Статус импорта
            if ( $programmsbcreport['error'] )
            {// Ошибка импорта подписки на программу
                $message = $this->dof->
                    get_string('table_import_row_programmsbc_error', 'participants');
                $data['status'] = dof_html_writer::div(
                    $message,
                    'table_import_error'
                );
            } else 
            {// Успешное выполнение
                $message = $this->dof->
                    get_string('table_import_row_programmsbc_'.$programmsbcreport['action'], 'participants');
                $data['status'] = dof_html_writer::div(
                    $message,
                    'table_import_message'
                );
            }
            // Описание импорта
            $data['actions'] = $this->table_trace($programmsbcreport, 'programmsbc', 'programmsbcs', $counterrors);
        
            $table->data[] = $data;
        }
        if ( $counterrors )
        {// Ошибки во время импорта
            $text = $this->dof->get_string('table_import_errors', 'participants', $counterrors);
            $this->dof->messages->add($text, 'error');
        }
        $html .= $this->dof->modlib('widgets')->print_table($table, true);
        
        return $html;
    }
    
    /**
     * Сформировать блок с описанием действий, произведенных над данными
     * 
     * @return string - HTML-код с описанием работы
     */
    protected function table_trace($report, $name, $plugincode, &$counterrors)
    {
        $html = '';
        
        // Заголовок блока
        $html .= dof_html_writer::tag(
            'h5', 
            $this->dof->get_string(
                'table_import_row_'.$name, 
                'participants'
            )
        );
        
        if ( $report['error'] )
        {// Ошибка работы
            $html .= dof_html_writer::div(
                $this->dof->get_string($report['error'], $plugincode, null, 'storage'),
                'table_import_error'
            );
            $counterrors++;
        } else 
        {// Исполненное действие
            $message = 'table_import_row_'.$name.'_'.$report['action'];
            $html .= dof_html_writer::div(
                $this->dof->get_string($message, 'participants'),
                'table_import_message'
            );
        }
        
        foreach ( $report['subreports'] as $name => $report )
        {
            $plugincode = null;
            switch ( $name )
            {
                case 'contractid' :
                    $plugincode = 'contracts';
                    break;
                case 'programmid' :
                    $plugincode = 'programms';
                    break;
                case 'agroupid' :
                    $plugincode = 'agroups';
                    break;
                case 'studentid' :
                case 'sellerid' :
                case 'clientid' :
                case 'curatorid' :
                    $plugincode = 'persons';
                    break;
            }
            $html .= $this->table_trace($report, $name, $plugincode, $counterrors);
        }
        return $html;
    }
}
?>