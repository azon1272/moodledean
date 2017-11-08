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
 * Классы форм
 * 
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');
// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** 
 * Форма создания/редактирования партнера
 */
class dof_im_partners_edit_partner extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $depid - ID подразделения партнера
     */
    protected $depid = 0;
    
    /**
     * @var $depid - ID персоны партнера
     */
    protected $personid = 0;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];
    
    /**
     * @var $errors - Массив ошибок
     */
    protected $errors = [];
    
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->personid = $this->_customdata->personid;
        $this->addvars = $this->_customdata->addvars;
        $this->code = $this->_customdata->code;
        $this->errors = [];
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'depid', $this->depid);
        $mform->setType('depid', PARAM_INT);
        $mform->addElement('hidden', 'personid', $this->personid);
        $mform->setType('personid', PARAM_INT);
        
        // Заголовок формы - Персона
        $mform->addElement(
                'header',
                'form_partners_add_person_title',
                $this->dof->get_string('form_partners_add_person_title', 'partners')
        );
        
        // Имя персоны
        $mform->addElement(
                'text',
                'form_partners_add_person_firstname',
                $this->dof->get_string('form_partners_add_person_firstname', 'partners')
        );
        $mform->setType(
                'form_partners_add_person_firstname',
                PARAM_TEXT
        );
        $mform->addRule(
                'form_partners_add_person_firstname',
                $this->dof->get_string('error_form_partners_add_person_firstname_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Фамилия персоны
        $mform->addElement(
                'text',
                'form_partners_add_person_lastname',
                $this->dof->get_string('form_partners_add_person_lastname', 'partners')
        );
        $mform->setType(
                'form_partners_add_person_lastname',
                PARAM_TEXT
        );
        $mform->addRule(
                'form_partners_add_person_lastname',
                $this->dof->get_string('error_form_partners_add_person_lastname_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Отчество персоны
        $mform->addElement(
                'text',
                'form_partners_add_person_midname',
                $this->dof->get_string('form_partners_add_person_midname', 'partners')
        );
        $mform->setType(
                'form_partners_add_person_midname',
                PARAM_TEXT
        );
        $mform->addRule(
                'form_partners_add_person_midname',
                $this->dof->get_string('error_form_partners_add_person_midname_required', 'partners'),
                'required',
                null,
                'client'
        );
       
        // Email персоны
        $mform->addElement(
                'text',
                'form_partners_add_person_email',
                $this->dof->get_string('form_partners_add_person_email', 'partners')
        );
        $mform->setType(
                'form_partners_add_person_email',
                PARAM_EMAIL
        );
        $mform->addRule(
                'form_partners_add_person_email',
                $this->dof->get_string('error_form_partners_add_person_email_required', 'partners'),
                'required',
                null,
                'client'
        );
        $mform->addRule(
                'form_partners_add_person_email',
                $this->dof->get_string('error_form_partners_add_person_email_nonvalid', 'partners'),
                'email',
                null,
                'client'
        );
        
        // Дата рождения
        $opts = [];
        $opts['startyear'] = 1950;
        $opts['stopyear']  = dof_userdate(time()-5*365*24*3600,'%Y');
        $opts['optional']  = false;
        $mform->addElement(
                'date_selector', 
                'form_partners_add_person_birth', 
                $this->dof->get_string('form_partners_add_person_birth', 'partners')
                
        );
        
        // Мобильный телефон персоны
        $mform->addElement(
                'text',
                'form_partners_add_person_mobilephone',
                $this->dof->get_string('form_partners_add_person_mobilephone', 'partners')
        );
        $mform->setType(
                'form_partners_add_person_mobilephone',
                PARAM_TEXT
        );
        
        // Рабочий телефон персоны
        $mform->addElement(
                'text',
                'form_partners_add_person_workphone',
                $this->dof->get_string('form_partners_add_person_workphone', 'partners')
        );
        $mform->setType(
                'form_partners_add_person_workphone',
                PARAM_TEXT
        );
        
        // Заголовок формы - Подразделение
        $mform->addElement(
                'header', 
                'form_partners_add_title', 
                $this->dof->get_string('form_partners_add_dep_title', 'partners')
        );
        
        // Имя подразделения
        $mform->addElement(
                'text',
                'form_partners_add_dep_name',
                $this->dof->get_string('form_partners_add_dep_name', 'partners')
        );
        $mform->setType(
                'form_partners_add_dep_name', 
                PARAM_TEXT
        );
        $mform->addRule(
                'form_partners_add_dep_name', 
                $this->dof->get_string('error_form_partners_add_dep_name_required', 'partners'), 
                'required',
                null,
                'client'
        );
        
        // Тип подразделения
        $types = $this->dof->im('partners')->get_list_dep_types();
        $mform->addElement(
                'select',
                'form_partners_add_dep_type',
                $this->dof->get_string('form_partners_add_dep_type', 'partners'),
                $types
        );
        $mform->addRule(
                'form_partners_add_dep_type',
                $this->dof->get_string('error_form_partners_add_dep_type_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Рабочий телефон
        $mform->addElement(
                'text',
                'form_partners_add_dep_telephone',
                $this->dof->get_string('form_partners_add_dep_telephone', 'partners')
        );
        $mform->setType(
                'form_partners_add_dep_telephone', 
                PARAM_TEXT
        );
        $mform->addRule(
                'form_partners_add_dep_telephone',
                $this->dof->get_string('error_form_partners_add_dep_telephone_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // E-mail 
        $mform->addElement(
                'text',
                'form_partners_add_dep_email',
                $this->dof->get_string('form_partners_add_dep_email', 'partners')
        );
        $mform->setType(
                'form_partners_add_dep_email', 
                PARAM_EMAIL
        );
        $mform->addRule(
                'form_partners_add_dep_email',
                $this->dof->get_string('error_form_partners_add_dep_email_required', 'partners'),
                'required',
                null,
                'client'
        );
        $mform->addRule(
                'form_partners_add_dep_email',
                $this->dof->get_string('error_form_partners_add_dep_email_nonvalid', 'partners'),
                'email',
                null,
                'client'
        );
        
        // Плановое число учеников
        $mform->addElement(
                'text',
                'form_partners_add_dep_studentsnum',
                $this->dof->get_string('form_partners_add_dep_studentsnum', 'partners')
        );
        $mform->setType(
                'form_partners_add_dep_studentsnum', 
                PARAM_INT
        );
        $mform->addRule(
                'form_partners_add_dep_studentsnum',
                $this->dof->get_string('error_form_partners_add_dep_studentsnum_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Плановое число преподавателей
        $mform->addElement(
                'text',
                'form_partners_add_dep_teachersnum',
                $this->dof->get_string('form_partners_add_dep_teachersnum', 'partners')
        );
        $mform->setType('form_partners_add_dep_teachersnum', PARAM_INT);
        $mform->addRule(
                'form_partners_add_dep_teachersnum',
                $this->dof->get_string('error_form_partners_add_dep_teachersnum_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Плановое число руководителей
        $mform->addElement(
                'text',
                'form_partners_add_dep_managersnum',
                $this->dof->get_string('form_partners_add_dep_managersnum', 'partners')
        );
        $mform->setType('form_partners_add_dep_managersnum', PARAM_INT);
        $mform->addRule(
                'form_partners_add_dep_managersnum',
                $this->dof->get_string('error_form_partners_add_dep_managersnum_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // ФИО директора
        $mform->addElement(
                'text',
                'form_partners_add_dep_fio_director',
                $this->dof->get_string('form_partners_add_dep_fio_director', 'partners')
        );
        $mform->setType(
                'form_partners_add_dep_fio_director',
                PARAM_TEXT
        );
        $mform->addRule(
                'form_partners_add_dep_fio_director',
                $this->dof->get_string('error_form_partners_add_dep_fio_director_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Адрес
        $choices = get_string_manager()->get_list_of_countries(false);
        $sel = $mform->addElement(
                'hierselect', 
                'form_partners_add_dep_county', 
                $this->dof->get_string('form_partners_add_dep_county', 'partners').':',
                null,
                '<br>'
        );
        $sel->setMainOptions($choices);
        $sel->setSecOptions($this->get_list_regions($choices));
        $mform->addRule(
                'form_partners_add_dep_type',
                $this->dof->get_string('error_form_partners_add_dep_county_required', 'partners'),
                'required',
                null,
                'client'
        );
        $mform->setDefault('form_partners_add_dep_county', ['RU', 'RU-MOW']);
        
        // Округ
        $mform->addElement(
                'text', 
                'form_partners_add_dep_district', 
                $this->dof->get_string('form_partners_add_dep_district', 'partners')
        );
        $mform->setType('form_partners_add_dep_district', PARAM_TEXT);
        $mform->addRule(
                'form_partners_add_dep_district',
                $this->dof->get_string('error_form_partners_add_dep_district_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Населенный пункт
        $mform->addElement(
                'text', 
                'form_partners_add_dep_city', 
                $this->dof->get_string('form_partners_add_dep_city', 'partners')
        );
        $mform->setType('form_partners_add_dep_city', PARAM_TEXT);
        $mform->addRule(
                'form_partners_add_dep_city',
                $this->dof->get_string('error_form_partners_add_dep_city_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Улица
        $mform->addElement(
                'text', 
                'form_partners_add_dep_street', 
                $this->dof->get_string('form_partners_add_dep_street', 'partners')
        );
        $mform->setType('form_partners_add_dep_street', PARAM_TEXT);
        $mform->addRule(
                'form_partners_add_dep_street',
                $this->dof->get_string('error_form_partners_add_dep_street_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Тип улицы
        $mform->addElement(
                'select', 
                'form_partners_add_dep_streettype', 
                $this->dof->get_string('form_partners_add_dep_streettype', 'partners'), 
                $this->dof->modlib('refbook')->get_street_types()
        );
        $mform->setType('form_partners_add_dep_streettype', PARAM_TEXT);
        $mform->addRule(
                'form_partners_add_dep_streettype',
                $this->dof->get_string('error_form_partners_add_dep_streettype_required', 'partners'),
                'required',
                null,
                'client'
        );
        
        // Номер дома
        $mform->addElement(
                'text', 
                'form_partners_add_dep_housenum', 
                $this->dof->get_string('form_partners_add_dep_housenum', 'partners')
        );
        $mform->setType('form_partners_add_dep_housenum', PARAM_TEXT);
        $mform->addRule(
                'form_partners_add_dep_housenum',
                $this->dof->get_string('error_form_partners_add_dep_housenum_required', 'partners'),
                'required',
                null,
                'client'
        );
       
        // Кнопки подтверждения
        $mform->addElement(
                'submit', 
                'form_partners_add_dep_housenum_submit', 
                $this->dof->get_string('form_partners_add_dep_housenum_submit', 'partners')
        );
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /** 
     * Проверка данных формы
     * 
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = array();
        
        $str = trim($data['form_partners_add_person_firstname']);
        if ( empty($str) )
        {// Имя персоны
            $errors['form_partners_add_person_firstname'] = $this->dof->get_string('error_form_partners_add_person_firstname_required', 'partners');
        }
        $str = trim($data['form_partners_add_person_firstname']);
        if ( empty($str) )
        {// Фамилия персоны
            $errors['form_partners_add_person_lastname'] = $this->dof->get_string('error_form_partners_add_person_lastname_required', 'partners');
        }
        $str = trim($data['form_partners_add_person_firstname']);
        if ( empty($str) )
        {// Отчество персоны
            $errors['form_partners_add_person_midname'] = $this->dof->get_string('error_form_partners_add_person_midname_required', 'partners');
        }
        $str = trim($data['form_partners_add_person_email']);
        if ( empty($str) )
        {// Email персоны
            $errors['form_partners_add_person_email'] = $this->dof->get_string('error_form_partners_add_person_email_required', 'partners');
        }
        if ( ! empty($this->personid) )
        {// Указан ID персоны
            $person = $this->dof->storage('persons')->get($this->personid);
            if ( ! empty($person) )
            {// Персона имеется в системе
                $str = trim($data['form_partners_add_person_email']);
                if ( $person->email != $str )
                {// Переданный и текущий Email персоны не совпадает
                    $errors['form_partners_add_person_email'] = $this->dof->get_string('error_form_partners_add_person_email_no_compare', 'partners');
                }
            }
        }
        $str = trim($data['form_partners_add_person_mobilephone']);
        if ( empty($str) )
        {// Мобильный телефон персоны
            $errors['form_partners_add_person_mobilephone'] = $this->dof->get_string('error_form_partners_add_person_mobilephone_required', 'partners');
        }
        $str = trim($data['form_partners_add_person_workphone']);
        if ( empty($str) )
        {// Рабочий телефон персоны
            $errors['form_partners_add_person_workphone'] = $this->dof->get_string('error_form_partners_add_person_workphone_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_name']);
        if ( empty($str) )
        {// Имя подразделения
            $errors['form_partners_add_dep_name'] = $this->dof->get_string('error_form_partners_add_dep_name_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_telephone']);
        if ( empty($str) )
        {// Рабочий телефон подразделения
            $errors['form_partners_add_dep_telephone'] = $this->dof->get_string('error_form_partners_add_dep_telephone_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_email']);
        if ( empty($str) )
        {// E-mail подразделения
            $errors['form_partners_add_dep_email'] = $this->dof->get_string('error_form_partners_add_dep_email_required', 'partners');
        }
        $int = intval($data['form_partners_add_dep_studentsnum']);
        if ( $int < 0 )
        {// Плановое число учеников
            $errors['form_partners_add_dep_studentsnum'] = $this->dof->get_string('error_form_partners_add_dep_studentsnum_required', 'partners');
        }
        $int = intval($data['form_partners_add_dep_teachersnum']);
        if ( $int < 0 )
        {// Плановое число учеников
            $errors['form_partners_add_dep_teachersnum'] = $this->dof->get_string('error_form_partners_add_dep_teachersnum_required', 'partners');
        }
        $int = intval($data['form_partners_add_dep_managersnum']);
        if ( $int < 0 )
        {// Плановое число учеников
            $errors['form_partners_add_dep_managersnum'] = $this->dof->get_string('error_form_partners_add_dep_managersnum_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_district']);
        if ( empty($str) )
        {// Округ
            $errors['form_partners_add_dep_district'] = $this->dof->get_string('error_form_partners_add_dep_district_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_city']);
        if ( empty($str) )
        {// Населенный пункт
            $errors['form_partners_add_dep_city'] = $this->dof->get_string('error_form_partners_add_dep_city_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_street']);
        if ( empty($str) )
        {// Улица
            $errors['form_partners_add_dep_street'] = $this->dof->get_string('error_form_partners_add_dep_street_required', 'partners');
        }
        $str = trim($data['form_partners_add_dep_housenum']);
        if ( empty($str) )
        {// Номер дома
            $errors['form_partners_add_dep_housenum'] = $this->dof->get_string('error_form_partners_add_dep_housenum_required', 'partners');
        }
        
        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Заполнение формы данными
     */
    function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( ! empty($this->personid) )
        {// Заполнение значениями
            $person = $this->dof->storage('persons')->get($this->personid);
            if ( ! empty($person) )
            {// Персона имеется в системе
                $mform->setDefault('form_partners_add_person_firstname', $person->firstname);
                $mform->setDefault('form_partners_add_person_lastname', $person->lastname);
                $mform->setDefault('form_partners_add_person_midname', $person->middlename);
                $mform->setDefault('form_partners_add_person_email', $person->email);
                $mform->setDefault('form_partners_add_person_mobilephone', $person->phonecell);
                $mform->setDefault('form_partners_add_person_workphone', $person->phonework);
            }
        }
        if ( ! empty($this->depid) )
        {// Заполнение значениями
            $dep = $this->dof->storage('departments')->get($this->depid);
            if ( ! empty($dep) )
            {// Персона имеется в системе
                $mform->setDefault('form_partners_add_dep_name', $dep->name);
                $address = $this->dof->storage('addresses')->get($dep->addressid);
                if ( ! empty($address) )
                {
                    $mform->setDefault('form_partners_add_dep_county', [
                                    $address->country,
                                    $address->region
                    ]);
                    $mform->setDefault('form_partners_add_dep_district', $address->county);
                    $mform->setDefault('form_partners_add_dep_city', $address->city);
                    $mform->setDefault('form_partners_add_dep_street', $address->streetname);
                    $mform->setDefault('form_partners_add_dep_streettype', $address->streettype);
                    $mform->setDefault('form_partners_add_dep_housenum', $address->number);
                }
                
                $type = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'type');
                if ( ! empty($type) )
                {
                    $mform->setDefault('form_partners_add_dep_type', $type);
                }
                $telephone = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'telephone');
                if ( ! empty($telephone) )
                {
                    $mform->setDefault('form_partners_add_dep_telephone', $telephone);
                }
                $email = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'email');
                if ( ! empty($email) )
                {
                    $mform->setDefault('form_partners_add_dep_email', $email);
                }
                $num = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'studentsnum');
                if ( ! empty($num) )
                {
                    $mform->setDefault('form_partners_add_dep_studentsnum', $num);
                }
                $num = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'teachersnum');
                if ( ! empty($num) )
                {
                    $mform->setDefault('form_partners_add_dep_teachersnum', $num);
                }
                $num = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'managersnum');
                if ( ! empty($num) )
                {
                    $mform->setDefault('form_partners_add_dep_managersnum', $num);
                }
                $num = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'directorfio');
                if ( ! empty($num) )
                {
                    $mform->setDefault('form_partners_add_dep_fio_director', $num);
                }
            }
        }
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
            // Создаем подразделение или обновляем его, если такое уже имеется
            $linkuser = false;
            if ( empty($formdata->depid) )
            {// Подразделение не указано - создаем новое
                
                // Подготовим данные для добавления адреса подразделения
                $addres = new stdClass();
                // Страна и район
                $addres->country = $formdata->form_partners_add_dep_county[0];
                if ( isset($formdata->form_partners_add_dep_county[1]) )
                {// Район указан
                    $addres->region = $formdata->form_partners_add_dep_county[1];
                } else
                {// Район не указан
                    $addres->region = null;
                }
                // Округ
                $addres->county = $formdata->form_partners_add_dep_district;
                // Город
                $addres->city = $formdata->form_partners_add_dep_city;
                // Улица
                $addres->streetname = $formdata->form_partners_add_dep_street;
                // Тип улицы
                $addres->streettype = $formdata->form_partners_add_dep_streettype;
                // Номер дома
                $addres->number = $formdata->form_partners_add_dep_housenum;
                // Тип адреса
                $addres->type = '7';
                
                // Подготовим данные для добавления подразделения
                $department = new stdClass();
                // Имя подразделения
                $department->name = $formdata->form_partners_add_dep_name;
                // Генерация кода на основе имени
                $code = $this->generatecode($department->name);
                if ( empty($code) )
                {// Ошибка во время генерации кода
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_generate_code', 'partners');
                    return false;
                }
                // Код подразделения
                $department->code = $code;
                // ID управляющего
                $department->managerid = 0;
                // Адрес  
                $department->addressid = 0;
                // Часовая зона
                $department->zone = 0.0;
                // Получим id подразделения - родителя партнерской сети
                $parendepartment = $this->dof->storage('config')->
                    get_config_value('parent_departmentid', 'im', 'partners', $this->addvars['departmentid']);
                // ID родительского подразделения
                $department->leaddepid = $parendepartment;
                
                // СОЗДАНИЕ ПОДРАЗДЕЛЕНИЯ
                $departmentid = $this->dof->storage('departments')->insert($department);
                if ( empty($departmentid) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_create', 'partners');
                    return false;
                }
                    
                // СОХРАНЕНИЕ ДОПОЛНИТЕЛЬНЫХ ОПЦИЙ ПОДРАЗДЕЛЕНИЯ
                $resultcov = true;
                // Сохраняем доп настройки подразделения
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'timecreate', time() ));
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'directorfio', $formdata->form_partners_add_dep_fio_director) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'type', $formdata->form_partners_add_dep_type) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'telephone', $formdata->form_partners_add_dep_telephone) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'email', $formdata->form_partners_add_dep_email) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'studentsnum', $formdata->form_partners_add_dep_studentsnum) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'teachersnum', $formdata->form_partners_add_dep_teachersnum) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                            save_option('storage', 'departments', $departmentid, 'managersnum', $formdata->form_partners_add_dep_managersnum) );
                if ( empty($resultcov) )
                {// Во время сохранения данных по подразделению произошли ошибки
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_save_cov', 'partners');
                }
                
                // СОХРАНЕНИЕ АДРЕСА
                $addrid = $this->dof->storage('addresses')->insert($addres);
                if ( empty($addrid) )
                {// Во время сохранения данных об адресе подразделения произошла ошибка
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_save_address', 'partners');
                    $addrid = 0;
                }
                
                // СВЯЗЫВАНИЕ ПОДРАЗДЕЛЕНИЯ С ЗАПИСЬЮ АДРЕСА
                $updatedep = new stdClass();
                $updatedep->addressid = $addrid;
                $result = $this->dof->storage('departments')->update($updatedep, $departmentid);
                if ( empty($result) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_update_addr', 'partners');
                }
                // Связать пользователя с подразделением
                $linkuser = true;
            } else 
            {// Обновляем подразделение
                // Получим подразделение
                $department = $this->dof->storage('departments')->get($formdata->depid);
                $departmentid = $department->id;
                if ( empty($department) )
                {// Подразделение не найдено
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_not_found', 'partners');
                    return false;
                }
                
                // Родительское подразделение
                $parendepartment = $this->dof->storage('config')->
                    get_config_value('parent_departmentid', 'im', 'partners', $this->addvars['departmentid']);
                if ( $department->leaddepid != $parendepartment )
                {
                    // Подразделение не входит в сеть
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_not_in_web', 'partners');
                    return false;
                }
                
                // Подготовим данные для добавления адреса подразделения
                $addres = new stdClass();
                // Страна и район
                $addres->country = $formdata->form_partners_add_dep_county[0];
                if ( isset($formdata->form_partners_add_dep_county[1]) )
                {// Район указан
                    $addres->region = $formdata->form_partners_add_dep_county[1];
                } else
                {// Район не указан
                    $addres->region = null;
                }
                // Округ
                $addres->county = $formdata->form_partners_add_dep_district;
                // Город
                $addres->city = $formdata->form_partners_add_dep_city;
                // Улица
                $addres->streetname = $formdata->form_partners_add_dep_street;
                // Тип улицы
                $addres->streettype = $formdata->form_partners_add_dep_streettype;
                // Номер дома
                $addres->number = $formdata->form_partners_add_dep_housenum;
                // Тип адреса
                $addres->type = '7';
                if ( ! empty($department->addressid) ) 
                {// Подразделение уже имеет адрес
                    $addrid = $department->addressid;
                    // Обновляем адрес подразделения
                    $result = $this->dof->storage('addresses')->update($addres, $addrid);
                    if ( empty($addrid) )
                    {// Во время сохранения данных об адресе подразделения произошла ошибка
                        // Записать ошибку
                        $this->errors[] = $this->dof->get_string('error_form_partners_dep_save_address', 'partners');
                    }
                } else 
                {
                    // Добавляем адрес
                    $addrid = $this->dof->storage('addresses')->insert($addres);
                    if ( empty($addrid) )
                    {// Во время сохранения данных об адресе подразделения произошла ошибка
                        // Записать ошибку
                        $this->errors[] = $this->dof->get_string('error_form_partners_dep_save_address', 'partners');
                        $addrid = 0;
                    }
                }
                
                // Результат сохраниения
                $resultcov = true;
                // Сохраняем доп настройки
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $departmentid, 'directorfio', $formdata->form_partners_add_dep_fio_director) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $department->id, 'type', $formdata->form_partners_add_dep_type) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $department->id, 'telephone', $formdata->form_partners_add_dep_telephone) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $department->id, 'email', $formdata->form_partners_add_dep_email) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $department->id, 'studentsnum', $formdata->form_partners_add_dep_studentsnum) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $department->id, 'teachersnum', $formdata->form_partners_add_dep_teachersnum) );
                $resultcov = ( $resultcov && $this->dof->storage('cov')->
                        save_option('storage', 'departments', $department->id, 'managersnum', $formdata->form_partners_add_dep_managersnum) );
                
                if ( empty($resultcov) )
                {// Во время сохранения данных по подразделению произошли ошибки
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_save_cov', 'partners');
                }
                
                // Имя подразделения
                $department->name = $formdata->form_partners_add_dep_name;
                // Адрес
                $department->addressid = $addrid;
                // Получим id подразделения - родителя партнерской сети
                $parendepartment = $this->dof->storage('config')->
                    get_config_value('parent_departmentid', 'im', 'partners', $this->addvars['departmentid']);
                // ID родительского подразделения
                $department->leaddepid = $parendepartment;
                
                // Обновим подразделение
                $result = $this->dof->storage('departments')->update($department);
                if ( empty($result) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_update', 'partners');
                    return false;
                }
            }
            
            if ( empty($this->personid) )
            {// Создадим персону
                $person = new stdClass();
                $person->firstname = $formdata->form_partners_add_person_firstname;
                $person->lastname = $formdata->form_partners_add_person_lastname;
                $person->middlename = $formdata->form_partners_add_person_midname;
                // Поиск email в системе
                $mdluser = $this->dof->modlib('ama')->user(false)->get_list(['email' => $formdata->form_partners_add_person_email]);
                if ( ! empty($mdluser) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_person_notunique_email', 'partners');
                    return false;
                }
                $person->email = $formdata->form_partners_add_person_email;
                $person->phonecell = $formdata->form_partners_add_person_mobilephone;
                $person->phonework = $formdata->form_partners_add_person_workphone;
                $person->dateofbirth  = $formdata->form_partners_add_person_birth;
                $person->sync2moodle = 1;
                $person->mdluser = 0;
                $person->departmentid = $departmentid;
                $person->addressid = 0;

                $personid = $this->dof->storage('persons')->insert($person);
                if ( empty($personid) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_person_create', 'partners');
                    return false;
                }
                $linkuser = true;
            } else  
            {// Указан ID персоны
                $person = $this->dof->storage('persons')->get($this->personid);
                $personid = $person->id;
                if ( empty($person) )
                {// Персона не найдена
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_person_not_found', 'partners');
                    return false;
                }
                
                $person->firstname = $formdata->form_partners_add_person_firstname;
                $person->lastname = $formdata->form_partners_add_person_lastname;
                $person->middlename = $formdata->form_partners_add_person_midname;
                $str = trim($formdata->form_partners_add_person_email);
                if ( $person->email != $str )
                {// Переданный и текущий Email персоны не совпадает
                    // Поиск email в системе
                    $mdluser = $this->dof->modlib('ama')->user(false)->get_list(['email' => $str]);
                    if ( ! empty($mdluser) )
                    {
                        // Записать ошибку
                        $this->errors[] = $this->dof->get_string('error_form_partners_person_notunique_email', 'partners');
                        return false;
                    }   
                }
                $person->email = $formdata->form_partners_add_person_email;
                $person->phonecell = $formdata->form_partners_add_person_mobilephone;
                $person->phonework = $formdata->form_partners_add_person_workphone;
                $person->dateofbirth  = $formdata->form_partners_add_person_birth;
                $person->departmentid = $departmentid;
                
                $personid = $this->dof->storage('persons')->update($person, $personid);
                if ( empty($personid) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_person_update', 'partners');
                    return false;
                }
            }
            
            if ( $linkuser )
            {
                // Свяжем пользователя с подразделением
                $department = new stdClass();
                $department->managerid = $personid;
                $department->id = $departmentid;
                // Обновим подразделение
                $result = $this->dof->storage('departments')->update($department);
                if ( empty($result) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_partners_dep_person', 'partners');
                    return false;
                }
                $this->dof->im('partners')->generate_codes($departmentid);
            }
            
            if ( empty($this->errors) )
            {// Генерация партнера завершена без ошибок
                if ( ! empty($this->code) )
                {// Генерация партнера по коду
                    
                    // Вход под пользователем
                    $person = $this->dof->storage('persons')->get($personid);
                    if ( ! empty($person->mdluser) )
                    {
                        $user = $this->dof->sync('personstom')->get_mdluser($person->mdluser);
                        // Очистка текущей сессии
                        \core\session\manager::terminate_current();
                        
                        complete_user_login($user);
                        
                        $somevars = [];
                        $somevars['departmentid'] = $this->addvars['departmentid'];
                        redirect($this->dof->url_im('my', '/index.php', $somevars));
                    }
                    redirect('/');
                }
                redirect($this->dof->url_im('partners', '/admin_panel.php', $this->addvars));
            }
        }
    }
    
    
    /**
     * Сгенерировать код подразделения
     *
     * @return array - Массив типов
     */
    private function generatecode($name)
    {
        // Добавлять постфикс к коду
        $add = false;
        // Лимит попыток
        $limit = 100;
        do 
        {
            // Очищвем имя
            $name = trim($name);
            // Получаем длину имени
            $length = strlen($name);
            // Транслитерация имени
            $translit = $this->translit($name);
            if ( $length > 10 )
            {// Обрезали до 10 знаков
                $translit = substr($translit, 0, 9);
            }
            if ( $add )
            {
                $translit .= mt_rand (1000, 99999);
            }
            // Поиск подразделения с таким кодом
            $exist = $this->dof->storage('departments')->get_record(['code' => $translit]);
            if ( $exist )
            {
                $add = true;
            }
            $limit--;
            
        } while ( $exist && $limit );
        
        if ( empty($limit) )
        {// Превышен интервал попыток сгенерировать код подразделения
            return false;
        }
        
        return $translit;
    }
    
    /**
     * Транслитерация
     *
     * @return string - Массив типов
     */
    private function translit($string) 
    {
        $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($string, $converter);
    }
    
    /** 
     * Возвращает список регионов приписанных к стране
     * @param array $choices - список стран
     * @return array список регионов
     */
    private function get_list_regions($choices)
    {
        $return = [];
        if ( ! is_array($choices) )
        {// Получили не массив - это ошибка
            return $return;
        }
        // К каждой стране припишем ее регионы
        foreach ( $choices as $key => $value )
        {
            $regions = $this->dof->modlib('refbook')->region($key);
            if ( ! empty($regions) )
            {
                $return[$key] = $regions[$key];
            } else 
            {
                $return[$key] = [];
            }
        }
        
        return $return;
    
    }
    
    /**
     * Вывести стек ошибок
     */
    public function errors()
    {
        if ( ! empty($this->errors) )
        {
            foreach ( $this->errors as $error )
            {
                echo $this->dof->modlib('widgets')->error_message($error);
            }
        }
    }
    
    
}

/**
 * Форма обновления кодов регистрации
 */
class dof_im_partners_refresh_codes extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $depid - ID подразделения
     */
    protected $depid = 0;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * @var $errors - Массив ошибок
     */
    protected $errors = [];


    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = & $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->addvars = $this->_customdata->addvars;
        $this->errors = [];

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'depid', $this->depid);
        $mform->setType('depid', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
                'header',
                'form_refresh_codes_title',
                $this->dof->get_string('form_refresh_codes_title', 'partners')
        );
           
        $department = $this->dof->storage('departments')->get($this->depid);
        if ( empty($department) )
        {// Подразделение не найдено
            // Записать ошибку
            $this->errors[] = $this->dof->get_string('error_form_refresh_codes_department_notfound', 'partners');
            return false;
        }
        
        // Описание
        $mform->addElement(
                'static',
                'form_refresh_codes_desc',
                '',
                $this->dof->get_string('form_refresh_codes_desc', 'partners', $department)
        );
        
        // Кнопки действий
        $this->add_action_buttons(true, $this->dof->get_string('form_refresh_codes_refresh', 'partners'));

        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
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
     * Заполнение формы данными
     */
    function definition_after_data()
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
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            if ( $formdata->depid != $this->addvars['departmentid'] )
            {// Попытка обновить код в не целевом подразделении
                $this->errors[] = $this->dof->get_string('error_form_refresh_codes_refresh_wrong_refresh', 'partners');
            }

            if ( empty($this->errors) )
            {// Создаем редирект на страницу кодов
                $this->dof->im('partners')->generate_codes($formdata->depid);
                    
                $addvars = $this->addvars;
                redirect($this->dof->url_im('partners', '/codes.php', $addvars));
            }
        }
        if ( $this->is_cancelled() )
        {
            if ( empty($this->errors) )
            {// Создаем редирект на страницу кодов
                $addvars = $this->addvars;
                redirect($this->dof->url_im('partners', '/codes.php', $addvars));
            }
        }
    }

    /**
     * Вывести стек ошибок
     */
    public function errors()
    {
        if ( ! empty($this->errors) )
        {
            foreach ( $this->errors as $error )
            {
                echo $this->dof->modlib('widgets')->error_message($error);
            }
        }
    }
}

/**
 * Форма создания/редактирования партнера
 */
class dof_im_partners_registration extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $depid - ID подразделения
     */
    protected $depid = 0;

    /**
     * @var $code - Код партнера
     */
    protected $code = '';

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     * @var $errors - Массив ошибок
     */
    protected $errors;


    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = & $this->_form;

        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->code = $this->_customdata->code;
        $this->addvars = $this->_customdata->addvars;
        $this->errors = [];

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'depid', $this->depid);
        $mform->setType('depid', PARAM_INT);
        $mform->addElement('hidden', 'code', $this->code);
        $mform->setType('code', PARAM_INT);

        if ( empty($this->code) )
        {// Код не передан
            // Заголовок формы - Код партнера
            $mform->addElement(
                    'header',
                    'form_registrtion_code_title',
                    $this->dof->get_string('form_registrtion_code_title', 'partners')
            );
            
            // Код партнера
            $mform->addElement(
                    'text',
                    'form_registrtion_code',
                    $this->dof->get_string('form_registrtion_code', 'partners')
            );
            $mform->setType(
                    'form_registrtion_code',
                    PARAM_INT
            );
            $mform->addRule(
                    'form_registrtion_code',
                    $this->dof->get_string('error_form_registrtion_code_required', 'partners'),
                    'required',
                    null,
                    'client'
            );
            
            // Кнопка отправки
            $mform->addElement(
                    'submit',
                    'form_registrtion_code_submit',
                    $this->dof->get_string('form_registrtion_code_submit', 'partners')
            );
        } else 
        {// Код указан
            // Получаем информацию по коду
            $codeinfo = $this->dof->im('partners')->get_code_info($this->code);
            
            $registration_enabled = $this->dof->storage('config')->
                get_config_value('registration_enabled', 'im', 'partners', $this->depid);
            
            if ( ! $registration_enabled )
            {// Регистрация отключена
                $this->dof->print_error('error_registration_disabled', '', NULL, 'im', 'partners');
            }
            
            if ( empty($codeinfo) )
            {// Код не найден
                $this->errors[] = $this->dof->get_string('error_form_registrtion_code_valid', 'partners');
                return false;
            }
            if ( count($codeinfo) > 1 )
            {// Найдено несколько партнеров с данным кодом
                $this->errors[] = $this->dof->get_string('error_form_registrtion_code_multiple_partners', 'partners');
                return false;
            }
           
            $info = array_shift($codeinfo);
            if ( $info['type'] == 'partnercode' )
            {// Код регистрации партнера
                $addvars = $this->addvars;
                $addvars['code'] = $this->code;
                if ( isset($info['department']->id) )
                {
                    $addvars['departmentid'] = $info['department']->id;
                } else
                {
                    $addvars['departmentid'] = 0;
                }
                redirect($this->dof->url_im('partners', '/edit_partner.php', $addvars));
            }
            // Подразделение целевого кода
            $department = $info['department'];
            
            // Получаем настройку о доступности регистрации
            $registration_enabled = $this->dof->storage('config')->
                get_config_value('registration_enabled', 'im', 'partners', $department->id);

            if ( ! $registration_enabled )
            {// Регистрация отключена
                $this->dof->print_error('error_registration_disabled', '', NULL, 'im', 'partners');
            }
            
            // Тип подразделения
            $types = $this->dof->im('partners')->get_list_dep_types();
            $typeid = $this->dof->storage('cov')->get_option('storage', 'departments', $department->id, 'type');
            $departmenttype = $types[$typeid];
            
            /*
            // Тип Образовательного учреждения
            $mform->addElement(
                    'static',
                    'form_registrtion_person_deptype',
                    $this->dof->get_string('form_registrtion_person_deptype', 'partners'),
                    $departmenttype
                    );
            
            // Название Образовательного учреждения
            $mform->addElement(
                    'static',
                    'form_registrtion_person_depname',
                    $this->dof->get_string('form_registrtion_person_depname', 'partners'),
                    $department->name
                    );
            */
            // Имя персоны
            $mform->addElement(
                    'text',
                    'form_registrtion_person_firstname',
                    $this->dof->get_string('form_registrtion_person_firstname', 'partners')
            );
            $mform->setType(
                    'form_registrtion_person_firstname',
                    PARAM_TEXT
            );
            $mform->addRule(
                    'form_registrtion_person_firstname',
                    $this->dof->get_string('error_form_registrtion_person_firstname_required', 'partners'),
                    'required',
                    null,
                    'client'
            );
            
            // Фамилия персоны
            $mform->addElement(
                    'text',
                    'form_registrtion_person_lastname',
                    $this->dof->get_string('form_registrtion_person_lastname', 'partners')
            );
            $mform->setType(
                    'form_registrtion_person_lastname',
                    PARAM_TEXT
            );
            $mform->addRule(
                    'form_registrtion_person_lastname',
                    $this->dof->get_string('error_form_registrtion_person_lastname_required', 'partners'),
                    'required',
                    null,
                    'client'
            );
            
            // Отчество персоны
            $mform->addElement(
                    'text',
                    'form_registrtion_person_midname',
                    $this->dof->get_string('form_registrtion_person_midname', 'partners')
            );
            $mform->setType(
                    'form_registrtion_person_midname',
                    PARAM_TEXT
            );
            $mform->addRule(
                    'form_registrtion_person_midname',
                    $this->dof->get_string('error_form_registrtion_person_midname_required', 'partners'),
                    'required',
                    null,
                    'client'
            ); 
            
            // Email персоны
            $mform->addElement(
                    'text',
                    'form_registrtion_person_email',
                    $this->dof->get_string('form_registrtion_person_email', 'partners')
            );
            $mform->setType(
                    'form_registrtion_person_email',
                    PARAM_EMAIL
            );
            $mform->addRule(
                    'form_registrtion_person_email',
                    $this->dof->get_string('error_form_registrtion_person_email_required', 'partners'),
                    'required',
                    null,
                    'client'
            );
            $mform->addRule(
                    'form_registrtion_person_email',
                    $this->dof->get_string('error_form_registrtion_person_email_email_nonvalid', 'partners'),
                    'email',
                    null,
                    'client'
            );
            
            // Дата рождения
            $opts = [];
            $opts['startyear'] = 1950;
            $opts['stopyear']  = dof_userdate(time()-5*365*24*3600,'%Y');
            $opts['optional']  = false;
            $mform->addElement(
                    'date_selector',
                    'form_registrtion_person_birth',
                    $this->dof->get_string('form_registrtion_person_birth', 'partners')
            
            );
            
            // Пол
            $displaylist = array();
            $displaylist['unknown'] = $this->dof->get_string('form_registrtion_person_gender_unknown', 'partners');
            $displaylist['male'] = $this->dof->get_string('form_registrtion_person_gender_male', 'partners');
            $displaylist['female'] = $this->dof->get_string('form_registrtion_person_gender_female', 'partners');
            $mform->addElement(
                    'select',
                    'form_registrtion_person_gender',
                    $this->dof->get_string('form_registrtion_person_gender', 'partners'),
                    $displaylist
            );
            
            // Мобильный телефон персоны
            $mform->addElement(
                    'text',
                    'form_registrtion_person_mobilephone',
                    $this->dof->get_string('form_registrtion_person_mobilephone', 'partners')
            );
            $mform->setType(
                    'form_registrtion_person_mobilephone',
                    PARAM_TEXT
            );
            
            /*
            switch ($info['type'])
            {
                // Регистрация учителя
                case 'teachercode' :
                    // Тип пользователя
                    $types = [
                    'teacher' => $this->dof->get_string('form_registrtion_person_type_teacher', 'partners'),
                    'manager' => $this->dof->get_string('form_registrtion_person_type_manager', 'partners')
                    ];
                    $mform->addElement(
                            'select',
                            'form_registrtion_person_type',
                            $this->dof->get_string('form_registrtion_person_type', 'partners'),
                            $types
                            );
            
                    // Должность персоны
                    $mform->addElement(
                            'text',
                            'form_registrtion_person_position',
                            $this->dof->get_string('form_registrtion_person_position', 'partners')
                    );
                    $mform->setType(
                            'form_registrtion_person_position',
                            PARAM_TEXT
                    );
                    break;
                    // Регистрация ученика
                case 'studentcode' :
                    // Тип пользователя
                    $mform->addElement('hidden', 'form_registrtion_person_type', 'student');
                    $mform->setType('form_registrtion_person_type', PARAM_TEXT);
                    // Класс/Курс
                    $mform->addElement(
                            'text',
                            'form_registrtion_person_stream',
                            $this->dof->get_string('form_registrtion_person_stream', 'partners')
                            );
                    $mform->setType(
                            'form_registrtion_person_stream',
                            PARAM_TEXT
                    );
                    break;
                default :
                    $this->errors[] = $this->dof->get_string('error_form_registrtion_code_wrong_codetype', 'partners');
                    return false;
            }
            */
            // Кнопка регистрации
            $mform->addElement(
                    'submit',
                    'form_registrtion_code_submit',
                    $this->dof->get_string('form_registrtion_code_submit', 'partners')
            );
            
        }
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Проверка данных формы
     *
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = array();
        
        if ( ! empty($this->code) )
        {
            // Массив ошибок
            $errors = array();
            
            $str = trim($data['form_registrtion_person_firstname']);
            if ( empty($str) )
            {// Имя персоны не указано
                $errors['form_registrtion_person_firstname'] = $this->dof->get_string('error_form_registrtion_person_firstname_required', 'partners');
            }
            $str = trim($data['form_registrtion_person_lastname']);
            if ( empty($str) )
            {// Фамилия персоны не указана
                $errors['form_registrtion_person_lastname'] = $this->dof->get_string('error_form_registrtion_person_lastname_required', 'partners');
            }
            $str = trim($data['form_registrtion_person_midname']);
            if ( empty($str) )
            {// Отчество персоны не указано
                $errors['form_registrtion_person_midname'] = $this->dof->get_string('error_form_registrtion_person_midname_required', 'partners');
            }
            if ( isset($data['form_registrtion_person_email']) )
            {
                $str = trim($data['form_registrtion_person_email']);
                if ( empty($str) )
                {// Email не указан
                    $errors['form_registrtion_person_email'] = $this->dof->get_string('error_form_registrtion_person_email_required', 'partners');
                }
            }
        }
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
            if ( isset($formdata->form_registrtion_code) )
            {// Код регистрации
                $codeinfo = $this->dof->im('partners')->get_code_info($formdata->form_registrtion_code);
                
                if ( count($codeinfo) > 1 )
                {// Найдено несколько партнеров с данным кодом
                    $this->errors[] = $this->dof->get_string('error_form_registrtion_code_multiple_partners', 'partners');
                    return false;
                }
                
                if ( empty($codeinfo) )
                {// Найдено несколько партнеров с данным кодом
                    $this->errors[] = $this->dof->get_string('error_form_registrtion_code_valid', 'partners');
                    return false;
                }
                
                if ( empty($this->errors) )
                {// Создаем редирект на регистрацию пользователя
                    $info = array_shift($codeinfo);
                    if ( $info['type'] == 'partnercode' )
                    {// Код регистрации партнера
                        $addvars = $this->addvars;
                        $addvars['code'] = $formdata->form_registrtion_code;
                        $addvars['departmentid'] = $info['department']->id;
                        redirect($this->dof->url_im('partners', '/edit_partner.php', $addvars));
                    }
                    $addvars = $this->addvars;
                    $addvars['code'] = $formdata->form_registrtion_code;
                    $addvars['departmentid'] = $info['department']->id;
                    redirect($this->dof->url_im('partners', '/registration.php', $addvars));
                }
            }
            if ( ! empty($this->code) )
            {// Код указан и переданы регистрационные данные
                $person = new stdClass();
                $person->firstname = $formdata->form_registrtion_person_firstname;
                $person->lastname = $formdata->form_registrtion_person_lastname;
                $person->middlename = $formdata->form_registrtion_person_midname;
                
                // Поиск email в системе
                $mdluser = $this->dof->modlib('ama')->user(false)->get_list(['email' => $formdata->form_registrtion_person_email]);
                if ( ! empty($mdluser) )
                {
                    // Записать ошибку
                    $this->errors[] = $this->dof->get_string('error_form_registrtion_person_notunique_email', 'partners');
                    return false;
                }
                $person->email = $formdata->form_registrtion_person_email;
                $person->phonecell = $formdata->form_registrtion_person_mobilephone;
                $person->dateofbirth  = $formdata->form_registrtion_person_birth;
                $person->gender = $formdata->form_registrtion_person_gender;
                $person->sync2moodle = 1;
                $person->mdluser = 0;
                $person->departmentid = $this->depid;
                $person->addressid = 0;
                
                $personid = $this->dof->storage('persons')->insert($person);
                if ( empty($personid) )
                {
                    // Ошибка при записи
                    $this->errors[] = $this->dof->get_string('error_form_registrtion_person_create', 'partners');
                    return false;
                }
                // Группа, в которую требуется добавить персону
                $agroupid = $this->dof->storage('config')->
                    get_config_value('agroup_id', 'im', 'partners', $this->depid);
                if ( ! empty($agroupid) )
                {// Передана группа
                    // Добавить персону в группу
                    $options = ['switch_to_active' => true];
                    $result = $this->dof->storage('agroups')->autocomplete_add_person_to_group($agroupid, $personid, $options);
                    if ( ! isset($result->status) || $result->status != 'ok' )
                    {// Персона не добавлена в группу
                        $this->errors[] = $result->message;
                    }
                }
                
                // Запись доп данных
                if ( isset($formdata->form_registrtion_person_position) )
                {// Передана должность
                    $resultcov = $this->dof->storage('cov')->
                        save_option('storage', 'persons', $personid, 'position', $formdata->form_registrtion_person_position);
                    if ( empty($resultcov) )
                    {
                        $this->errors[] = $this->dof->get_string('error_form_registrtion_person_save_position', 'partners');
                    }
                }
                if ( isset($formdata->form_registrtion_person_stream) )
                {// Передан Класс/Курс
                    $resultcov = $this->dof->storage('cov')->
                        save_option('storage', 'persons', $personid, 'position', $formdata->form_registrtion_person_stream);
                    if ( empty($resultcov) )
                    {
                        $this->errors[] = $this->dof->get_string('error_form_registrtion_person_save_stream', 'partners');
                    }
                }
                if ( isset($formdata->form_registrtion_person_type) )
                {// Передан тип персоны
                    $resultcov = $this->dof->storage('cov')->
                        save_option('storage', 'persons', $personid, 'type', $formdata->form_registrtion_person_type);
                    if ( empty($resultcov) )
                    {
                        $this->errors[] = $this->dof->get_string('error_form_registrtion_person_save_type', 'partners');
                    }
                }
                
                
                if ( empty($errors) )
                {// Ошибок нет, производим вход в систему под этип пользователем
                    $person = $this->dof->storage('persons')->get($personid);
                    if ( ! empty($person->mdluser) )
                    {
                        $user = $this->dof->sync('personstom')->get_mdluser($person->mdluser);
                        complete_user_login($user);
                        
                        // Получаем настройку о доступности регистрации
                        $url = $this->dof->storage('config')->
                            get_config_value('registration_success_url', 'im', 'partners', $person->departmentid);
                        
                        if ( empty($url) )
                        {// Настройка не найдена
                            redirect('/');
                        }
                        // Редирект на страницу, указанную в настройках
                        redirect($url);
                    }
                }
            }
        }
    }

    /**
     * Вывести стек ошибок
     */
    public function errors()
    {
        if ( ! empty($this->errors) )
        {
            foreach ( $this->errors as $error )
            {
                echo $this->dof->modlib('widgets')->error_message($error);
            }
        }
    }
}
?>