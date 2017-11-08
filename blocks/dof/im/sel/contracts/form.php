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

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();
if ( $DOF->plugin_exists('im', 'persons') )
{
    require_once($DOF->plugin_path('im', 'persons', '/form.php'));
}

/*
 * Форма сохранения договора
 */
class im_sel_contract_save_form extends dof_modlib_widgets_form
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
     * URL для отмены формы
     *
     * @var string
     */
    protected $cancelurl = '';
    
    /**
     * Имя переменной, которая будет добавлена в returnurl
     * Содержит результат сохранения договора
     *
     * @var string
     */
    protected $idparam = 'contractid';
    
    /**
     * Возможность ручного указания номера договора
     *
     * @var bool
     */
    protected $createnumber = false;
    
    /**
     * Обьявление полей формы
     *
     * @see dof_modlib_widgets_form::definition()
     */
    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Заблокированные элементы
        $freezed = [];
        
        // Добавление свойств
        $this->dof =& $this->_customdata->dof;
        $this->returnurl = $this->_customdata->returnurl;
        $this->cancelurl = $this->_customdata->cancelurl;
        $this->addvars = $this->_customdata->addvars;
        $contractid = (int)$this->_customdata->contractid;
        if ( isset($this->_customdata->idparam) && ! empty($this->_customdata->idparam) )
        {// Имя параметра переопределено
            $this->idparam = $this->_customdata->idparam;
        }
        if ( isset($this->_customdata->createnumber) AND $this->_customdata->createnumber )
        {// Разрешено ручное указание номера договора
            $this->createnumber = true;
        }

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'contractid', $contractid);
        $mform->setType('contractid', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
            'header',
            'cldheader',
            $this->dof->get_string('cldheader', 'sel')
        );
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Поле создания номера договора
        $defaultnumber = $this->dof->storage('contracts')->get_default_contractnum($contractid);
        $mform->addElement('text', 'num', $this->dof->get_string('num', 'sel'));
        $mform->setType('num', PARAM_TEXT);
        $mform->setDefault('num', $defaultnumber);
        if ( ! $this->createnumber )
        {// Пользователю недоступно изменение номера договора
            $freezed[] = 'num';
        }
        
        // Дата заключения договора
        $mform->addElement(
            'date_selector', 
            'date', 
            $this->dof->get_string('date', 'sel')
        );
        $mform->setType('date', PARAM_INT);
        
        // Подразделение договора
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null, '0', null, true);
        $permissions = [['plugintype' => 'storage', 'plugincode' => 'departments', 'code' => 'use']];
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        $mform->addElement(
            'select', 
            'department', 
            $this->dof->get_string('department', 'sel').':', 
            $departments
        );
        $mform->setType('department', PARAM_TEXT);
        $mform->addRule('department', $this->dof->modlib('ig')->igs('form_err_required'), 'required', null, 'client');
        
        // Заметка
        $mform->addElement(
            'textarea', 
            'notes', 
            $this->dof->get_string('notes', 'sel'), 
            ['style' => 'width: 100%']
        );
        
        // Метаконтракт текущего договора
        $ajaxparams = $this->autocomplete_params('metacontracts','client', $contractid);
        $mform->addElement(
            'dof_autocomplete', 
            'metacontract',
            $this->dof->get_string('metacontract','sel'), 
            [],
            $ajaxparams
        );
        
        // Выбор учащегося по договору
        $mform->addElement(
            'header',
            'stheader', 
            $this->dof->get_string('student', 'sel')
        );
        // Cоздать новую персону
        $mform->addElement(
            'radio', 
            'student', 
            null, 
            $this->dof->get_string('new', 'sel'),
            'new'
        );
        // Использовать персону деканата
        $mform->addElement(
            'radio', 
            'student', 
            null, 
            $this->dof->get_string('personid', 'sel'),
            'personid'
        );
        // Выбор персоны Деканата
        $ajaxparams = $this->autocomplete_params('personid', 'student', $contractid);
        $mform->addElement(
            'dof_autocomplete', 
            'st_person_id',
            $this->dof->modlib('ig')->igs('search'), 
            [], 
            $ajaxparams
        );
        // Использовать пользователя Moodle
        if ( ! $contractid )
        {// Договор создается
            $mform->addElement(
                'radio', 
                'student', 
                null, 
                $this->dof->get_string('mdluser','sel'),
                'mdluser'
            );
            $ajaxparams = $this->autocomplete_params('mdluser', 'student', $contractid);
            $mform->addElement(
                'dof_autocomplete', 
                'st_mdluser_id',
                $this->dof->modlib('ig')->igs('search'), 
                [], 
                $ajaxparams
            );
        }
        // Блокировка при редактировании
        $change = new stdClass();
        $change->studentid = 'change';
        if ( ! $this->dof->workflow('contracts')->is_change($contractid, $change) )
        {// Запрет редактирования ученика
            $freezed[] = 'student';
            $freezed[] = 'st_person_id';
            if ( $mform->elementExists('st_mdluser_id') )
            {
                $freezed[] = 'st_mdluser_id';
            }
        }

        // Данные по законному представителю
        $mform->addElement(
            'header',
            'clheader', 
            $this->dof->get_string('specimen', 'sel')
        );
        // Создать новую персону
        $mform->addElement(
            'radio', 
            'client', 
            null, 
            $this->dof->get_string('new','sel'), 
            'new'
        );
        // Представитель совпадает с учеником
        $mform->addElement(
            'radio', 
            'client', 
            null, 
            $this->dof->get_string('сoincides_with_student','sel'), 
            'student'
        );
        // Выбор персоны Деканата
        $mform->addElement(
            'radio', 
            'client', 
            null, 
            $this->dof->get_string('personid', 'sel'), 
            'personid'
        );
        $ajaxparams = $this->autocomplete_params('personid', 'client', $contractid);
        $mform->addElement(
            'dof_autocomplete', 
            'cl_person_id',
            $this->dof->modlib('ig')->igs('search'), 
            null, 
            $ajaxparams
        );
        
        // Использовать пользователя Moodle
        if ( ! $contractid )
        {// Договор создается
            $mform->addElement(
                'radio', 
                'client', 
                null, 
                $this->dof->get_string('mdluser', 'sel'), 
                'mdluser'
            );
            $ajaxparams = $this->autocomplete_params('mdluser', 'client', $contractid);
            $mform->addElement(
                'dof_autocomplete', 
                'cl_mdluser_id',
                $this->dof->modlib('ig')->igs('search'), 
                null, 
                $ajaxparams
            );
        }
        
        // Данные по куратору
        $mform->addElement(
            'header',
            'clheader', 
            $this->dof->get_string('curator', 'sel')
        );
        
        // Не использовать куратора
        $mform->addElement(
            'checkbox', 
            'nocurator', 
            null, 
            $this->dof->get_string('nocurator', 'sel')
        );
        
        // Поиск персоны в Деканате
        $ajaxparams = $this->autocomplete_params('personid', 'curator', $contractid);
        $mform->addElement(
            'dof_autocomplete', 
            'cur_person_id',
            $this->dof->modlib('ig')->igs('search'), 
            null, 
            $ajaxparams
        );
        
        // Блокировка полей
        if ( $contractid )
        {// Редактирование
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'new');
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'student');
            $mform->disabledIf('st_person_id', 'student', 'eq', 'new');
        }else
        {// Создание договора
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'new');
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'student');
            $mform->disabledIf('cl_mdluser_id', 'client', 'eq', 'new');
            $mform->disabledIf('cl_mdluser_id', 'client', 'eq', 'student');
            $mform->disabledIf('cl_person_id', 'client', 'eq', 'mdluser');
            $mform->disabledIf('cl_mdluser_id', 'client', 'eq', 'personid');
            $mform->disabledIf('st_person_id', 'student', 'eq', 'new');
            $mform->disabledIf('st_mdluser_id', 'student', 'eq', 'new');
            $mform->disabledIf('st_person_id', 'student', 'eq', 'mdluser');
            $mform->disabledIf('st_mdluser_id', 'student', 'eq', 'personid');
        }
        $mform->disabledIf('cur_person_id', 'nocurator', 'checked');
        if ( ! empty($freezed) )
        {
            $mform->freeze($freezed);
        }
        
        $mform->setType('student', PARAM_ALPHANUM);
        $mform->setType('client', PARAM_ALPHANUM);
        
        // Кнопки действий
        $this->add_action_buttons(true, $this->dof->get_string('continue', 'sel'));
        
        // Фильтрация всех полей
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Проверка данных формы
     */
    function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        
        // Базовые проверки
        if ( ! isset($data['student']) )
        {// Не указан способ добавления ученика
            $errors['student'] = $this->dof->get_string('error_choice', 'sel');
        }
        if ( ! isset($data['client']) )
        {// Не указан способ выбора законного представителя
            $errors['client'] = $this->dof->get_string('error_choice', 'sel');
        }
        if ( ! isset($data['department']) || ! isset($data['contractid']))
        {// Не указаны базовые данные договора
            $errors['department'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        
        if ( ! empty($errors) )
        {// Вывод текущего уровня ошибок
            return $errors;
        }
        
        // Валидация договора
        if ( isset($data['contractid']) && ! empty($data['contractid']) )
        {// Редактирование договора
            
            // Валидация номера договора
            $contract = $this->dof->storage('contracts')->get($data['contractid']);
            if ( empty($contract) )
            {// Договор не найден
                $errors['hidden'] = $this->dof->get_string('contract_save_error_contract_not_found', 'sel');
            }
            
            if ( $this->dof->im('sel')->is_access('editcontract', $data['contractid'], null, $data['departmentid']) )
            {// Право на изменение есть
                
                // Валидация персон по договору
                $errors = $this->validation_update($data, $files);
                
                if ( isset($data['num']) && ( $data['num'] != $contract->num ) && 
                     $this->dof->storage('contracts')->get_records(['num' => $data['num']]) )
                {// Номер договора не уникален
                    $errors['num'] = $this->dof->get_string('err_num_nounique', 'sel');
                }
                // Ограничение на число договоров в подразделении
                if ( $data['department'] != $contract->departmentid && 
                     $this->dof->storage('config')->get_limitobject('contracts', $data['department']) )
                {// Превышен лимит договоров в подразделении
                    $errors['department'] = $this->dof->get_string('limit_message', 'sel');
                }
            } else 
            {// Права на изменение нет
                $errors['hidden'] = $this->dof->get_string('contract_save_error_edit_access_denied', 'sel');
            }  
        } else 
        {// Создание договора
            if ( $this->dof->storage('contracts')->is_access('create', null, null, $data['departmentid']) )
            {// Право на создание есть
                
                // Валидация персон по договору
                $errors = $this->validation_create($data, $files);
                
                if ( isset($data['num']) && $this->dof->storage('contracts')->
                    get_records(['num'=>$data['num']]) )
                {// Номер договора не уникален
                    $errors['num'] = $this->dof->get_string('err_num_nounique', 'sel');
                }
                // Ограничение на число договоров в подразделении
                if ( ! $this->dof->storage('config')->get_limitobject('contracts', $data['department']) )
                {// Превышен лимит договоров в подразделении
                    $errors['department'] = $this->dof->get_string('limit_message','sel');
                }
            } else
            {// Права на создание нет
                $errors['hidden'] = $this->dof->get_string('contract_save_error_edit_create_denied', 'sel');
            }
        }
        
        // Валидация метаконтракта
        if ( isset($data['metacontract']) )
        {
            // Подучение значения метаконтракта
            $value = $this->dof->modlib('widgets')->get_extvalues_autocomplete('metacontract', $data['metacontract']);
            
            // Проверка в зависимости от действия
            switch ($value['do'])
            {
                // Создание нового метаконтракта
                case 'create' :
                    if ( $this->dof->storage('metacontracts')->is_exists(['num' => $value['name']]) )
                    {// Метаконтракт существует
                        $errors['metacontract'] = $this->dof->get_string('error_use_exists_metacontract', 'sel');
                    }
                    break;
                // Переименовывание метаконтракта
                case 'rename' :
                    if ( $this->dof->storage('metacontracts')->is_exists(['num' => $value['name']]) )
                    {// Имя уже используется
                        $errors['metacontract'] = $this->dof->get_string('error_use_exists_metacontract', 'sel');
                    }
                // Выбор метаконтракта
                case 'choose' :
                    if ( ! $this->dof->storage('metacontracts')->is_exists($value['id']) )
                    {// Метаконтракт не найден
                        $errors['metacontract'] = $this->dof->get_string('metacontract_no_exist','sel', $value['id']);
                    } elseif ( ! $this->dof->storage('metacontracts')->is_access('use', $value['id'], null, $data['departmentid']) )
                    {// Нет прав на использование метаконтракта
                        $errors['metacontract'] = $this->dof->get_string('error_use_metacontract', 'sel', $value['id']);
                    }
                    break;
            }
        }
        
        return $errors;
    }
    
    /**
     * Проверка данных персон при создании договора
     */
    protected function validation_create($data, $files)
    {
        $errors = [];
        
        // Выбор ученика
        if ( $data['student'] != 'personid' && $data['student'] != 'mdluser' )
        {// Создание новой персоны
            if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
            {// Доступ к созданию персоны закрыт
                $errors['student'] = $this->dof->
                    get_string('contract_save_error_student_create_access_denied', 'sel');
            }
        }
        if ( isset($data['st_person_id']['id']) && $data['student'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['st_person_id']['id'] < 1 )
            {// Студент не указан
                $errors['st_person_id'] = $this->dof->
                    get_string('contract_save_error_student_not_set', 'sel');
            } else
            {// Поиск выбранной персоны
                $student = $this->dof->storage('persons')->get($data['st_person_id']['id']);
                if ( empty($student) )
                {// Выбранная персона не найдена
                    $errors['st_person_id'] = $this->dof->
                        get_string('contract_save_error_student_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$student->status]) )
                    {// Персона не в актуальном статусе
                        $errors['st_person_id'] = $this->dof->
                            get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['st_person_id'] = $this->dof->
                            get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                }
            }
        }
        if ( isset($data['st_mdluser_id']['id']) && $data['student'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['st_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['st_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_student_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $studentid = (int)$this->dof->storage('persons')->get_by_moodleid_id($data['st_mdluser_id']['id']);
                $student = $this->dof->storage('persons')->get($studentid);
                if ( empty($student) )
                {// Выбранная персона не найдена
                    $errors['st_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_student_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$student->status]) )
                    {// Персона не в актуальном статусе
                        $errors['st_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['st_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_student_use_access_denied', 'sel');
                    }
                }
            }
        }
    
        // Проверки выбора законного представителя
        if ( $data['client'] != 'personid' && $data['client'] != 'mdluser' && $data['client'] != 'student' )
        {// Проверка прав на создание новой персоны законного представителя
            if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
            {// Доступ к созданию персоны закрыт
                $errors['client'] = $this->dof->
                    get_string('contract_save_error_client_create_access_denied', 'sel');
            }
        }
        if ( isset($data['cl_person_id']['id']) && $data['client'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['cl_person_id']['id'] < 1 )
            {// Законный представитель не указан
                $errors['cl_person_id'] = $this->dof->
                    get_string('contract_save_error_client_not_set', 'sel');
            } else
            {// Поиск выбранной персоны
                $client = $this->dof->storage('persons')->get($data['cl_person_id']['id']);
                if ( empty($client) )
                {// Выбранная персона не найдена
                    $errors['cl_person_id'] = $this->dof->
                        get_string('contract_save_error_client_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$client->status]) )
                    {// Персона не в актуальном статусе
                        $errors['cl_person_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['cl_person_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                }
            }
        }
        if ( isset($data['cl_mdluser_id']['id']) && $data['client'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['cl_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['cl_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_client_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $clientid = (int)$this->dof->storage('persons')->get_by_moodleid_id($data['cl_mdluser_id']['id']);
                $client = $this->dof->storage('persons')->get($clientid);
                if ( empty($client) )
                {// Выбранная персона не найдена
                    $errors['cl_mdluser_id'] = $this->dof->
                        get_string('contract_save_error_client_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$client->status]) )
                    {// Персона не в актуальном статусе
                        $errors['cl_mdluser_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['cl_mdluser_id'] = $this->dof->
                            get_string('contract_save_error_client_use_access_denied', 'sel');
                    }
                }
            }
        }
        // Проверки выбора куратора
        if ( ! isset($data['nocurator']) && isset($data['cur_person_id']['id']) )
        {// Выбрана персона из деканата
            if ( $data['cur_person_id']['id'] < 1 )
            {// Куратор не указан
                $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_set', 'sel');
            } else
            {// Поиск выбранной персоны
                $curator = $this->dof->storage('persons')->get($data['cur_person_id']['id']);
                if ( empty($curator) )
                {// Выбранная персона не найдена
                    $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_found', 'sel');
                } else
                {// Проверка доступа к персоне
                    $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                    if ( ! isset($statuses[$curator->status]) )
                    {// Персона не в актуальном статусе
                        $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                    }
                    if ( ! $this->dof->storage('persons')->is_access('use', $curator->id, null, $data['departmentid']) )
                    {// Доступ к использованию персоны закрыт
                        $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Проверка данных формы
     */
    protected function validation_update($data, $files)
    {
        $errors = [];

        // Получение текущего договора
        $contract = $this->dof->storage('contracts')->get($data['contractid']);
    
        // ПРОВЕРКИ ВЫБРАННОГО УЧЕНИКА
        // Возможность изменять ученика по договору
        $params = new stdClass();
        $params->studentid = 'change';
        $canchangestudent = (bool)$this->dof->workflow('contracts')->is_change($data['contractid'], $params);
        
        // Выбор ученика
        if ( $data['student'] != 'personid' && $data['student'] != 'mdluser' )
        {// Создание новой персоны
            if ( $canchangestudent )
            {// Можно изменять студента по договору
                if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
                {// Доступ к созданию персоны закрыт
                    $errors['student'] = $this->dof->
                        get_string('contract_save_error_student_create_access_denied', 'sel');
                }
            } else 
            {// Нельзя изменять студента по договору
                $errors['hidden'] = $this->dof->
                    get_string('contract_save_error_student_change_denied', 'sel');
            }
        }
        if ( isset($data['st_person_id']['id']) && $data['student'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['st_person_id']['id'] != $contract->studentid )
            {// Студент изменен
                if ( $canchangestudent )
                {// Можно изменять студента по договору
                    if ( $data['st_person_id']['id'] < 1 )
                    {// Студент не указан
                        $errors['st_person_id'] = $this->dof->
                            get_string('contract_save_error_student_not_set', 'sel');
                    } else
                    {// Поиск выбранной персоны
                        $student = $this->dof->storage('persons')->get($data['st_person_id']['id']);
                        if ( empty($student) )
                        {// Выбранная персона не найдена
                            $errors['st_person_id'] = $this->dof->
                                get_string('contract_save_error_student_not_found', 'sel');
                        } else
                        {// Проверка доступа к персоне
                            $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                            if ( ! isset($statuses[$student->status]) )
                            {// Персона не в актуальном статусе
                                $errors['st_person_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                            if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                            {// Доступ к использованию персоны закрыт
                                $errors['st_person_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                        }
                    }
                } else
                {// Нельзя изменять студента по договору
                    $errors['hidden'] = $this->dof->
                        get_string('contract_save_error_student_change_denied', 'sel');
                }
            }
        }
        if ( isset($data['st_mdluser_id']['id']) && $data['student'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['st_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['st_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_student_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $studentid = (int)$this->dof->storage('persons')->get_by_moodleid_id($data['st_mdluser_id']['id']);
                if ( $studentid != $contract->studentid )
                {// Изменение студента
                    if ( $canchangestudent )
                    {
                        $student = $this->dof->storage('persons')->get($studentid);
                        if ( empty($student) )
                        {// Выбранная персона не найдена
                            $errors['st_mdluser_id'] = $this->dof->
                                get_string('contract_save_error_student_not_found', 'sel');
                        } else
                        {// Проверка доступа к персоне
                            $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                            if ( ! isset($statuses[$student->status]) )
                            {// Персона не в актуальном статусе
                                $errors['st_mdluser_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                            if ( ! $this->dof->storage('persons')->is_access('use', $student->id, null, $data['departmentid']) )
                            {// Доступ к использованию персоны закрыт
                                $errors['st_mdluser_id'] = $this->dof->
                                    get_string('contract_save_error_student_use_access_denied', 'sel');
                            }
                        }
                    } else
                    {// Нельзя изменять студента по договору
                        $errors['hidden'] = $this->dof->
                            get_string('contract_save_error_student_change_denied', 'sel');
                    }
                }
            }
        }
    
        // Проверки выбора законного представителя
        if ( $data['client'] != 'personid' && $data['client'] != 'mdluser' && $data['client'] != 'student' )
        {// Проверка прав на создание новой персоны законного представителя
            if ( ! $this->dof->storage('persons')->is_access('create', null, null, $data['departmentid']) )
            {// Доступ к созданию персоны закрыт
                $errors['client'] = $this->dof->
                    get_string('contract_save_error_student_create_access_denied', 'sel');
            }
        }
        if ( isset($data['cl_person_id']['id']) && $data['client'] == 'personid' )
        {// Выбрана персона из деканата
            if ( $data['cl_person_id']['id'] != $contract->clientid )
            {// Представитель изменен
                if ( $data['cl_person_id']['id'] < 1 )
                {// Законный представитель не указан
                    $errors['cl_person_id'] = $this->dof->
                        get_string('contract_save_error_client_not_set', 'sel');
                } else
                {// Поиск выбранной персоны
                    $client = $this->dof->storage('persons')->get($data['cl_person_id']['id']);
                    if ( empty($client) )
                    {// Выбранная персона не найдена
                        $errors['cl_person_id'] = $this->dof->
                            get_string('contract_save_error_client_not_found', 'sel');
                    } else
                    {// Проверка доступа к персоне
                        $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                        if ( ! isset($statuses[$client->status]) )
                        {// Персона не в актуальном статусе
                            $errors['cl_person_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                        if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                        {// Доступ к использованию персоны закрыт
                            $errors['cl_person_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                    }
                }
            }
        }
        if ( isset($data['cl_mdluser_id']['id']) && $data['client'] == 'mdluser' )
        {// Выбран пользователь Moodle
            if ( $data['cl_mdluser_id']['id'] < 1 )
            {// Студент не указан
                $errors['cl_mdluser_id'] = $this->dof->
                    get_string('contract_save_error_client_not_set', 'sel');
            } else
            {// Поиск персоны по выбранному ID пользователя
                $clientid = (int)$this->dof->storage('persons')->get_by_moodleid_id($data['cl_mdluser_id']['id']);
                if ( $clientid != $contract->clientid )
                {// Представитель изменен
                    $client = $this->dof->storage('persons')->get($clientid);
                    if ( empty($client) )
                    {// Выбранная персона не найдена
                        $errors['cl_mdluser_id'] = $this->dof->
                            get_string('contract_save_error_client_not_found', 'sel');
                    } else
                    {// Проверка доступа к персоне
                        $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                        if ( ! isset($statuses[$client->status]) )
                        {// Персона не в актуальном статусе
                            $errors['cl_mdluser_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                        if ( ! $this->dof->storage('persons')->is_access('use', $client->id, null, $data['departmentid']) )
                        {// Доступ к использованию персоны закрыт
                            $errors['cl_mdluser_id'] = $this->dof->
                                get_string('contract_save_error_client_use_access_denied', 'sel');
                        }
                    }
                }
            }
        }
        // Проверки выбора куратора
        if ( ! isset($data['nocurator']) && isset($data['cur_person_id']['id']) )
        {// Выбрана персона из деканата
            if ( $data['cur_person_id']['id'] != $contract->curatorid )
            {// Куратор изменен
                if ( $data['cur_person_id']['id'] < 1 )
                {// Куратор не указан
                    $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_set', 'sel');
                } else
                {// Поиск выбранной персоны
                    $curator = $this->dof->storage('persons')->get($data['cur_person_id']['id']);
                    if ( empty($curator) )
                    {// Выбранная персона не найдена
                        $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_not_found', 'sel');
                    } else
                    {// Проверка доступа к персоне
                        $statuses = $this->dof->workflow('persons')->get_meta_list('actual');
                        if ( ! isset($statuses[$curator->status]) )
                        {// Персона не в актуальном статусе
                            $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                        }
                        if ( ! $this->dof->storage('persons')->is_access('use', $curator->id, null, $data['departmentid']) )
                        {// Доступ к использованию персоны закрыт
                            $errors['cur_person_id'] = $this->dof->get_string('contract_save_error_curator_use_access_denied', 'sel');
                        }
                    }
                }
            }
            
        }
        
        return $errors;
    }
    
    /** 
     * Получить массив опций для autocomplete-элемента 
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       personid - поиск по персонам
     *                       mdluser - поиск по пользователям Moodle
     *                       metacontracts - метаконтракты
     * @param string $side - сторона, подписывающая договор
     *                       client - законный представитель
     *                       student - ученик
     * @param int $contractid[optional] - id договора в таблице contracts (если договор редактируется)
     * @return array - массив опций
     */
    protected function autocomplete_params($type, $side, $contractid)
    {
        $options = array();
        $options['plugintype']   = "storage";
        $options['plugincode']   = "persons";
        $options['sesskey']      = sesskey();
        $options['type']         = 'autocomplete';
        $options['departmentid'] = $this->addvars['departmentid'];
        
        //получаем контракт
        $contract = $this->dof->storage('contracts')->get($contractid);
        //тип данных для автопоиска
        switch ($type)
        {
            //id персоны
            case 'personid':
                $options['querytype'] = "persons_list";
                
                $personid = 0;
                if ( ! $contractid )
                {// договор создается - значение по умолчанию не устанавливае
                    return $options;
                }else
                {
                    $column = $side.'id';
                    $personid = $contract->$column;
                }
                // если договор редактируется - установим в autocomplete значение по умолчани
                if ( ! $contract = $this->dof->storage('contracts')->get($contractid) )
                {// не получили договор - не можем установить значение по умолчанию
                    // id есть, а договора нет - нестандартная ситуация, сообщим об этом разработчикам
                    dof_debugging('autocomplete_params() cannot find contract by $contractid',
                            DEBUG_DEVELOPER);
                    return $options;
                }
                
                // законный представитель совпадает с учеником
                if ( ($contract->studentid == $contract->clientid) AND ($side == 'client') )
                {
                    // не ставим значение по умолчанию
                    return $options;
                }
                
                // не получили персону по id
                if ( ! $person = $this->dof->storage('persons')->get($personid) AND ($side != 'curator'))
                { // ошибка, но поле с куратором допускается пустое
                    dof_debugging('autocomplete_params() cannot find person by $personid',
                            DEBUG_DEVELOPER);
                    // возвращаем опции, т.к. значение по умолчанию уже не сможем получить
                    return $options;
                }
                
                // нашли персону - установим ее как значение по умолчанию
                $default = array($personid => $this->dof->storage('persons')->get_fullname($person));
                $options['default'] = $default;
                
                break;
            //пользователь в moodle
            case 'mdluser':
                $options['querytype'] = "mdluser_list";
                
                break;
            //метаконтракты
            case 'metacontracts':
                $options['querytype'] = "metacontracts_list";
                $options['plugincode'] = "metacontracts";
                $options['extoptions'] = new stdClass;
                $options['extoptions']->create = true;
                //если не удалось получить контракт
                if ($contract === false)
                {
                    return $options;
                }
                // получили метаконтракт
                if (!empty($contract->metacontractid))
                {//подставляем по умолчанию
                    $options['extoptions']->empty = true;
                    $metacontract = $this->dof->storage('metacontracts')->get($contract->metacontractid,'id,num');
                    $options['default'] = array($contract->metacontractid => 
                            $metacontract->num.' ['.$metacontract->id.']');
                }
                
                break;
        }
        
        return $options;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        $mform =& $this->_form;
        
        // Отмена формы
        if ( $this->is_cancelled() )
        {
            // Редирект на страницу отмены
            redirect($this->cancelurl);
        }
        
        // Отправка формы
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            
            // Сохранение договора
            $contract = new stdClass();
            
            // Вызов обработчика метаконтракта
            $contract->metacontractid = $this->dof->storage('metacontracts')
                ->handle_metacontract($formdata->metacontract, $formdata->department);
            
            if ( isset($formdata->contractid) && ! empty($formdata->contractid) )
            {// Договор редактируется
                $contract->id = (int)$formdata->contractid;
            } else 
            {// Договор создается
                $seller = $this->dof->storage('persons')->get_bu(null, true);
                if ( empty($seller) )
                {
                    $contract->sellerid = null;
                } else 
                {
                    $contract->sellerid = $seller->id;
                }
            }
            
            // Получение ученика
            switch ($formdata->student)
            {
                // Персона выбрана
                case 'personid':
                    $contract->studentid = $formdata->st_person_id['id'];
                    break;
                // Персона выбрана через пользователя Moodle
                case 'mdluser':
                    $contract->studentid = $this->dof->storage('persons')->
                        get_by_moodleid_id($formdata->st_mdluser_id['id']);
                    break;
                // Новая персона
                case 'new':
                    $contract->studentid = 0;
                    break;
                // Нет ученика
                default :
                    $contract->studentid = 0;
                    break;
            }
            // Получение представителя
            switch ($formdata->client)
            {
                case 'new':
                    $contract->clientid = 0;
                    break;
                // Представитель является учеником
                case 'student':
                    if ( $contract->studentid )
                    {// Студент определен
                        $contract->clientid = $contract->studentid;
                    } else 
                    {// Представитель связан со студентом
                        $contract->clientid = null;
                    }
                    break;
                // Персона выбрана
                case 'personid':
                    $contract->clientid = $formdata->cl_person_id['id'];
                    break;
                // Персона выбрана через пользователя Moodle
                case 'mdluser':
                    $contract->clientid = $this->dof->storage('persons')->
                        get_by_moodleid_id($formdata->st_mdluser_id['id']);
                    break;
                // Нет законного представителя
                default :
                    $contract->clientid = null;
            }
            if( ! isset($formdata->nocurator) )
            {// Установка куратора
                $contract->curatorid = $formdata->cur_person_id['id'];
            } else
            {// Куратор не установлен
                $contract->curatorid = null;
            }
        
            $contract->departmentid = $formdata->department;
            $contract->notes        = $formdata->notes;
            $contract->date         = $formdata->date + 3600*12;
            if ( $this->createnumber && isset($formdata->num) AND ! empty($formdata->num) )
            {// Можно изменять номер договора
                $contract->num = $formdata->num;
            }
        
            try {
                $contractid = $this->dof->storage('contracts')->save($contract);
                
                $url = $this->get_returnurl($contractid);
                redirect($url);
            } catch ( dof_exception_dml $e )
            {// Ошибка сохранения
                $this->dof->messages->add(
                    $this->dof->get_string($e->errorcode, 'contracts', null, 'storage'), 
                    'error'
                );
            }
        }
    }
    
    /**
     * Получить URL для возврата после обработки
     */
    protected function get_returnurl($contractid)
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
        $query[$this->idparam] = $contractid;
    
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


/*
 * Класс формы для ввода данных договора (вторая страничка)
 */
class sel_contract_form_two_page extends dof_im_persons_edit_form
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
        $this->contract = $this->dof->storage('contracts')->get($this->contractid);
    
        // Скрытые поля
        $mform->addElement('hidden', 'contractid', $this->contractid);
        $mform->setType('contractid', PARAM_INT);
    }
    
    /**
     * Добавление дополнительных полей для персон
     * 
     * @see dof_im_persons_edit_form::add_person_fields()
     */
    protected function add_person_fields($personcode)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $personcode == 'clientid' )
        {// Добавление полей для законного представителя
            // Организация
            $ajaxparams = $this->autocomplete_params(
                'organizations', 
                'client', 
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete', 
                'clorganization', 
                $this->dof->get_string('organization','sel'), 
                null,
                $ajaxparams
            );
            // Должность
            $ajaxparams = $this->autocomplete_params(
                'workplaces', 
                'client', 
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete', 
                'clworkplace', 
                $this->dof->get_string('workplace','sel'), 
                null,
                $ajaxparams
            );
        }
            
        if ( $personcode == 'studentid' )
        {// Добавление полей для ученика
            // Организация
            $ajaxparams = $this->autocomplete_params(
                'organizations',
                'student',
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete',
                'storganization',
                $this->dof->get_string('organization','sel'),
                null,
                $ajaxparams
            );
            // Должность
            $ajaxparams = $this->autocomplete_params(
                'workplaces',
                'student',
                $this->contractid
            );
            $mform->addElement(
                'dof_autocomplete',
                'stworkplace',
                $this->dof->get_string('workplace','sel'),
                null,
                $ajaxparams
            );
        }
    }
    
    /**
     * Дополнительные поля формы
     * 
     * @see dof_im_persons_edit_form::add_fields_after_persons()
     */
    protected function add_fields_after_persons()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->_customdata->countsbc == false )
        {// если подписок нет или она одна
            $mform->addElement('hidden', 'programmsbcid', 0);
            $mform->setType('programmsbcid', PARAM_INT);
            //создаем или редактируем подписку на программу
            $mform->addElement('header','header', $this->dof->get_string('create_programmsbc', 'sel'));
            $mform->addElement('checkbox', 'programmsbc',null, $this->dof->get_string('create_programmsbc', 'sel'));
            $options = $this->get_select_options();
            // при помощи css делаем так, чтобы надписи в форме совпадали с элементами select
            $mform->addElement('html', '<div style=" line-height: 1.9; ">');
            // добавляем новый элемент выбора зависимых вариантов форму
            $myselect =& $mform->addElement('hierselect', 'prog_and_agroup', 
                                            $this->dof->get_string('programm', 'programmsbcs').':<br/>'.
                                            $this->dof->get_string('agenum',   'programmsbcs').':<br/>'.
                                            $this->dof->get_string('agroup',   'programmsbcs').':',
                                            null,'<br/>');
            // закрываем тег выравнивания строк
            $mform->addElement('html', '</div>');
            // устанавливаем для него варианты ответа
            // (значения по умолчанию устанавливаются в методе definition_after_data)
            $myselect->setOptions(array($options->programms, $options->agenums, $options->agroups ));
            $mform->disabledIf('prog_and_agroup', 'programmsbc');
            // получаем все возможные формы обучения
            $eduforms = $this->get_eduforms_list();
            // создаем меню выбора формы обучения
            $mform->addElement('select', 'eduform', $this->dof->get_string('eduform', 'sel'), $eduforms);
            $mform->disabledIf('eduform', 'programmsbc');
            $mform->setType('eduform', PARAM_TEXT);
            // получаем все возможные типы обучения
            $edutypes = $this->get_edutypes_list();
            // создаем меню выбора типа обучения
            $mform->addElement('select', 'edutype', $this->dof->get_string('edutype', 'sel'), $edutypes);
            $mform->disabledIf('edutype', 'programmsbc');
            $mform->setType('edutype', PARAM_TEXT);
            $mform->setDefault('edutype','group');
            // свободное посещение
            $mform->addElement('selectyesno', 'freeattendance', $this->dof->get_string('freeattendance', 'sel'));
            $mform->disabledIf('freeattendance', 'programmsbc');
            $mform->setType('freeattendance', PARAM_INT);
            $ages = $this->get_list_ages();
            $mform->addElement('select', 'agestart', $this->dof->get_string('agestart', 'sel'), $ages);
            $mform->disabledIf('agestart', 'programmsbc');
            $mform->setType('agestart', PARAM_INT);
            $options = array();
            $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
            $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
            $options['optional']  = false;
            $mform->addElement('date_selector', 'datestart', $this->dof->get_string('datestart', 'sel'), $options);
            $mform->disabledIf('datestart', 'programmsbc');
            //$mform->setType('datestart', PARAM_INT);
            // поправочный зарплатный коэффициент
            $mform->addElement('text', 'salfactor', $this->dof->get_string('salfactor','sel').':', 'size="10"');
            $mform->setType('salfactor', PARAM_TEXT);
            $mform->setDefault('salfactor', '0.00');
        }else
        {// если их много - создаем ссылки на подписки
            $mform->addElement('header','header', $this->dof->get_string('programmsbcs', 'sel'));
            $programmsbcs = (array)$this->dof->storage('programmsbcs')->get_records(['contractid' => $this->_customdata->contractid]);
            foreach ( $programmsbcs as $sbc )
            {
                $mform->addElement('html', '&nbsp;&nbsp;&nbsp;<a href='.
                       $this->dof->url_im('programmsbcs','/edit.php?programmsbcid='.$sbc->id).'>'.
                       $this->dof->get_string('view_programmsbcs', 'sel', $this->get_programm_name($sbc->programmid)).
                       '</a><br>');
            }
        }
    }
    
    /**
     * Валидация формы
     */
    public function validation($data, $files)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        $errors = parent::validation($data, $files);
        
        $reqfield = [];
        
        // Валидация подписки на программу
        if ( isset($data['programmsbc']) AND ($data['programmsbc'] == 1) )
        {// если создается подписка
            // проверим существование программы
            if ( ! isset($data['prog_and_agroup'][0]) OR
                ! $this->dof->storage('programms')->is_exists($data['prog_and_agroup'][0]) )
            {// такая программа не существует
                $errors['prog_and_agroup'] = $this->dof->get_string('err_required','sel');
            }elseif ( ! isset($data['prog_and_agroup'][2]) AND $data['prog_and_agroup'][2] )
            {// проверяем существование группы
                if ( ! $agroup = $this->dof->storage('agroups')->get($data['prog_and_agroup'][2]) )
                {// если она указана, но ее id не найден - то это ошибка
                    $errors['prog_and_agroup'] = $this->dof->get_string('err_required','sel');
                }elseif ( $agroup->programmid <> $data['prog_and_agroup'][0] )
                {
                    $errors['prog_and_agroup'] = $this->dof->get_string('error_conformity_agroup','sel');
                }elseif ( $agroup->agenum <> $data['prog_and_agroup'][1] AND $agroup->status <> 'plan' )
                {
                    $errors['prog_and_agroup'] = $this->dof->get_string('error_conformity_agenum','sel');
                }
            }
        }
        
        // Валидация дополнительных полей студента
        //проверим, существует ли в форме автокомплит для организаций и передано ли в поле число
        if ( isset($data['storganization']['storganization']) AND
            preg_match("/^[0-9]+$/", $data['storganization']['storganization']) )
        {
            $checkid = $data['storganization']['storganization'];
            //проверим, существует ли такая организация
            if ( !$this->dof->storage('organizations')->is_exists($checkid) )
            {// такой организации не существует
                $errors['storganization'] = $this->dof->get_string('org_no_exist','sel');
            }elseif ( ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// нельзя использовать данную организацию
                $errors['storganization'] = $this->dof->get_string('error_use_org','sel',$checkid);
            }
        } elseif ( isset($data['storganization']['storganization']) )
        {
            if ( $checkid = $this->dof->storage('organizations')->get_field($data['storganization']['storganization'],'id') AND
                ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// такая организация уже существует и ее нельзя использовать
                $errors['storganization'] = $this->dof->get_string('error_use_exists_org','sel',$checkid);
            }
        } elseif ( ! empty($data['storganization']['id']) )
        {// передано id - проверим на использование
            if ( ! $this->dof->storage('organizations')->is_access('use',$data['storganization']['id']) )
            {
                $errors['storganization'] = $this->dof->get_string('error_use_org','sel',$data['storganization']['id']);
            }
        }
    
        // Валидация дополнительных полей законного представителя
        //проверим, существует ли в форме автокомплит для организаций и передано ли в поле число
        if ( isset($data['clorganization']['clorganization']) AND
            preg_match("/^[0-9]+$/", $data['clorganization']['clorganization']) )
        {
            $checkid = $data['clorganization']['clorganization'];
            //проверим, существует ли такая организация
            if ( !$this->dof->storage('organizations')->is_exists($checkid) )
            {// такой организации не существует
                $errors['clorganization'] = $this->dof->get_string('org_no_exist','sel');
            }elseif ( ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// нельзя использовать данную организацию
                $errors['clorganization'] = $this->dof->get_string('error_use_org','sel',$checkid);
            }
        }elseif ( isset($data['clorganization']['clorganization']) )
        {
            if ( $checkid = $this->dof->storage('organizations')->get_field($data['clorganization']['clorganization'],'id') AND
                ! $this->dof->storage('organizations')->is_access('use',$checkid) )
            {// такая организация уже существует и ее нельзя использовать
                $errors['clorganization'] = $this->dof->get_string('error_use_exists_org','sel',$checkid);
            }
        }elseif ( ! empty($data['clorganization']['id']) )
        {// передано id - проверим на использование
            if ( ! $this->dof->storage('organizations')->is_access('use',$data['clorganization']['id']) )
            {
                $errors['clorganization'] = $this->dof->get_string('error_use_org','sel',$data['clorganization']['id']);
            }
        }
    
        return $errors;
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
        
        // Должность студента
        if ( empty($formdata->stworkplace['stworkplace']) )
        {// Должность студента не указана
            $formdata->stworkplace['stworkplace'] = $this->dof->get_string('empty_workplace', 'sel');
        }
        // Организация студента
        if ( ! empty($formdata->storganization['storganization']) )
        {
            // Добавление новой организации
            $orgid = $this->dof->storage('organizations')->
                handle_organization('storganization', $formdata->storganization);
            
            if ( ! empty($orgid) )
            {// Организация создана
                // Привязка метаконтракта к созданной организации
                $obj = new stdClass();
                $obj->organizationid = $orgid;
                $this->dof->storage('metacontracts')->update($obj, $this->contract->metacontractid);
                // Привязка должности студента к организации и метаконтракту
                $this->dof->storage('workplaces')->handle_workplace('stworkplace', $formdata->stworkplace, $studentid, $orgid);
            }
        } else
        {// Установка organizationid = 0 и должность "Не указана"
            $this->dof->storage('workplaces')->handle_workplace('stworkplace', $formdata->stworkplace, $studentid);
        }
    
        // Обработчик данных законного представителя
        if ( $studentid <> $clientid && $clientid > 0 )
        {
            // Должность законного представителя
            if ( empty($formdata->stworkplace['clworkplace']) )
            {// Должность студента не указана
                $formdata->stworkplace['clworkplace'] = $this->dof->get_string('empty_workplace', 'sel');
            }
            
            // Организация Законного представителя
            if ( ! empty($formdata->clorganization['clorganization']) )
            {
                // Добавление новой организации
                $orgid = $this->dof->storage('organizations')->
                    handle_organization('clorganization', $formdata->clorganization);
                
                if ( ! empty($orgid) )
                {// Организация создана
                    // Привязка метаконтракта к созданной организации
                    $obj = new stdClass();
                    $obj->organizationid = $orgid;
                    $this->dof->storage('metacontracts')->update($obj, $this->contract->metacontractid);
                    // Привязка должности студента к организации и метаконтракту
                    $this->dof->storage('workplaces')->handle_workplace('clworkplace', $formdata->stworkplace, $clientid, $orgid);
                }
            } else
            {// Установка organizationid = 0 и должность "Не указана"
                $this->dof->storage('workplaces')->handle_workplace('clworkplace', $formdata->stworkplace, $clientid);
            }
        }
            
        // Обновление договора
        $contract = new stdClass();
        $contract->id = $this->contractid;
        $contract->studentid = $studentid;
        if ( $clientid )
        {
            $contract->clientid = $clientid;
        } else 
        {
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
        
        // Обработка подписки на программу
        if ( isset( $formdata->programmsbc ) )
        {
            // Сохранение подписки
            $sbc = new stdClass();
            $sbc->id = $formdata->programmsbcid;
            $sbc->contractid = $this->contractid;
            $sbc->programmid = $formdata->prog_and_agroup[0]; // id программы
            $sbc->agenum = $formdata->prog_and_agroup[1]; //парралель
            
            if ( isset($formdata->prog_and_agroup[2]) AND ($formdata->prog_and_agroup[2] <> 0) )
            {// и если указана группа - сохраняем группу
                $sbc->agroupid = $formdata->prog_and_agroup[2]; // id группы
            } else
            {// иначе - индивидуальный
                $sbc->agroupid = null;
            }
            
            $sbc->edutype = $formdata->edutype; // тип обучения
            $sbc->eduform = $formdata->eduform; // форма обучения
            $sbc->freeattendance = $formdata->freeattendance; // свободное посещение
            $sbc->datestart = $formdata->datestart;
            $sbc->salfactor = $formdata->salfactor;
            
            // сохраним подписку
            if ( ! $sbc->departmentid = $this->dof->storage('contracts')->get_field($sbc->contractid, 'departmentid') )
            {// Не удалось получить ID подразделения
                $this->errors[] = $this->dof->get_string('errorsaveprogrammsbcs', 'sel');
            } elseif ( $this->dof->storage('programmsbcs')->
                        is_programmsbc( $sbc->contractid, $sbc->programmid, $sbc->agroupid, $sbc->datestart, $sbc->id) )
            {// если такая подписка уже существует - сохранять нельзя
                $this->errors[] = $this->dof->get_string('programmsbc_exists', 'sel');
            } else
            {//можно сохранять
                
                if ( !empty($sbc->id) )
                {// подписка на курс редактировалась - обновим запись в БД
                    if ( ! $this->dof->storage('programmsbcs')->update($sbc, $sbc->id) )
                    {// не удалось произвести редактирование - выводим ошибку
                        $this->errors[] = $this->dof->get_string('errorsaveprogrammsbcs','sel');
                    }
                    if ( $history = $this->dof->storage('learninghistory')->get_first_learning_data($sbc->id) )
                    {
                        $this->dof->storage('learninghistory')->delete($history->id);
                    }
                    if ( $formdata->agestart )
                    {
                        //имитируем cpassed
                        $cpassed = new stdClass();
                        $cpassed->programmsbcid = $sbc->id;
                        $cpassed->ageid         = $formdata->agestart;
                        $cpassed->status        = 'active';
                        $this->dof->storage('learninghistory')->add($cpassed);
                    }
                } else
                {// подписка на курс создавалась
                    // сохраняем запись в БД
                    $sbc->status = 'application';
                    if( $id = $this->dof->storage('programmsbcs')->sign($sbc) )
                    {// все в порядке - сохраняем статус и возвращаем на страниу просмотра подписки
                        $this->dof->workflow('programmsbcs')->init($id);
                        //имитируем cpassed
                        $cpassed = new stdClass();
                        $cpassed->programmsbcid = $id;
                        $cpassed->ageid         = $formdata->agestart;
                        $cpassed->status        = 'active';
                        $this->dof->storage('learninghistory')->add($cpassed);
                    }else
                    {// подписка на курс выбрана неверно - сообщаем об ошибке
                        $this->errors[] = $this->dof->get_string('errorsaveprogrammsbcs','sel');
                    }
                }
            }
        }
    }
    
    /** Получить весь список опций для элемента hierselect
     * @todo переделать эту функцию в рекурсивную процедуру, чтобы сократить объем кода
     * @return stdClass object объект, содержащий данные для элемента hierselect
     */
    private function get_select_options()
    {
        $result = new stdClass();
        // получаем список всех учеников
        $programms = $this->get_list_programms();
        // создаем массив для учебных программ
        $agroups  = array();
        // создаем массив для параллелей
        $agenums  = array();
        foreach ( $programms as $progid=>$programm )
        {// для каждой программы составим список возможных академических групп,
            // и тем самым создадим иерархию второго уровня
            $agenums[$progid] = $this->get_list_agenums($progid);
            foreach ($agenums[$progid] as $num=>$agenum)
            {
                $agroups[$progid][$num] = $this->get_list_agroups($progid, $num);
            }
        }
        // записываем в результурующий объект все что мы получили
        $result->programms = $programms;
        $result->agroups   = $agroups;
        $result->agenums   = $agenums;
        //print_object($result);
        // возвращаем все составленные массивы в упорядоченном виде
        return $result;
    }
    
    /** Внутренняя функция. Получить параметры для autocomplete-элемента
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       personid - поиск по персонам
     *                       mdluser - поиск по пользователям Moodle
     * @param string $side - сторона, подписывающая договор
     *                       client - законный представитель
     *                       student - ученик
     * @param int $contractid[optional] - id договора в таблице contracts (если договор редактируется)
     *
     * @return array
     */
    private function autocomplete_params($type, $side, $contractid)
    {
        $options = array();
        $options['plugintype'] = "storage";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';
    
        //получаем контракт
        $contract = $this->dof->storage('contracts')->get($contractid);
    
        // определяем, для какого поля получать значение (ученик или законный представитель)
        $personid = 0;
        if ($contract !== false)
        {
            $column = $side.'id';
            $personid = $contract->$column;
        }
    
        //тип данных для автопоиска
        switch ($type)
        {
            //организация
            case 'organizations':
                $options['plugincode'] = "organizations";
                $options['querytype'] = "organizations_list";
                $options['extoptions'] = new stdClass;
                $options['extoptions']->create = true;
                $organizationid = $this->dof->storage('workplaces')
                ->get_field(array('personid' => $personid,'statuswork' => 'active'),'organizationid');
    
                if (!empty($organizationid))
                {
                    $organization = $this->dof->storage('organizations')->get($organizationid,'id,shortname');
                    // получили организацию
                    if (!empty($organization->metacontractid))
                    {//подставляем по умолчанию
                        $options['extoptions']->empty = true;
                        $metacontract = $this->dof->storage('metacontracts')->get($contract->metacontractid,'id,num');
                        $options['default'] = array($organizationid => $organization->shortname);
                    }
    
                }
    
    
                break;
    
                //должность
            case 'workplaces':
                $options['plugincode'] = "workplaces";
                $options['querytype'] = "workplaces_list";
                $options['extoptions'] = new stdClass;
                $options['extoptions']->create = true;
                $workplaceid = $this->dof->storage('workplaces')
                ->get_field(array('personid' => $personid, 'statuswork' => 'active'),'id');
    
                if (!empty($workplaceid))
                {
                    $workplace = $this->dof->storage('workplaces')->get($workplaceid, 'post');
                    if (!empty($workplace->metacontractid))
                    {//подставляем по умолчанию
                        $options['extoptions']->empty = true;
                        $metacontract = $this->dof->storage('metacontracts')->get($contract->metacontractid,'id,num');
                        $options['default'] = array($workplaceid => $workplace->post);
                    }
    
                }
    
                break;
        }
    
        return $options;
    }
    
    /** Получить список всех возможных программ обучения
     * @return array массив вариантов для элемента hierselect
     */
    private function get_list_programms()
    {
        // извлекаем все учебные программы из базы
        $result = $this->dof->storage('programms')->
            get_records(array('status'=>array('available')),'name');
        $result = $this->dof_get_select_values($result, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список академических групп 
     * 
     * @return array
     */
    private function get_list_agroups($programmid, $agenum)
    {
        $result = array();
        // добавляем первый вариант со словом "Индивидуально"
        $result[0] = $this->dof->get_string('no','programmsbcs');
        // получаем все программы
        $agroups = $this->dof->storage('agroups')->get_records(array('programmid'=>$programmid));
        if ( $agroups )
        {// если группы извлеклись - то добавим их в массив
            foreach ( $agroups as $id=>$agroup )
            {// составляем массив нужной для select-элемента структуры
                if ( $agroup->agenum == $agenum OR $agroup->status == 'plan')
                {
                    $result[$id] = $agroup->name.' ['.$agroup->code.']';
                }
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'agroups', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список доступных учебных периодов для этой программы
     * 
     * @return array массив элементов для hierselect
     * @param int $programmid - id учебной программы из таблицы programms
     */
    private function get_list_agenums($programmid)
    {
        $result = array();
        // добавляем первый вариант со словом "Индивидуально"
        $result[0] = $this->dof->get_string('no','programmsbcs');
        if ( ! $programm = $this->dof->storage('programms')->get($programmid) )
        {// переданная учебная программа не найдена
            return $result;
        }
        // заполняем массив данными
        for ( $i=1; $i<=$programm->agenums; $i++ )
        {
            $result[$i] = $i.' '; // пустой пробел в конце обязателен
        } 
         
        return $result;
    }
    /** Возвращает массив периодов 
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_ages()
    {
        $rez = $this->dof->storage('ages')->get_records(array('status'=>array('plan',
                                                                            'createstreams',
                                                                            'createsbc',
                                                                            'createschelude',
                                                                            'active')));
        $rez = $this->dof_get_select_values($rez);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        return $rez;
    }
    /** Получить список всех возможных форм обучения для элемента select
     * 
     * @return array
     */
    private function get_eduforms_list()
    {
        return $this->dof->storage('programmsbcs')->get_eduforms_list();
    }
    
    /** Получить список всех возможных типов обучения для элемента select
     * 
     * @return array
     */
    private function get_edutypes_list()
    {
        return $this->dof->storage('programmsbcs')->get_edutypes_list();
    }
    
    /** Получить название программы
     * @param int $programmid - id программы
     * @return string
     */
    private function get_programm_name($programmid)
    {
        if ( ! $programmname = $this->dof->storage('programms')->get_field($programmid, 'name') )
        {//программа не указана - выведем пустую строчку
            $programmname = '&nbsp;';
        }
        if ( ! $programmcode = $this->dof->storage('programms')->get_field($programmid, 'code') )
        {//код программы не указан - выведем пустую строчку
            $programmcode = '&nbsp;';
        }
        if ( ($programmname <> '&nbsp;') OR ($programmcode <> '&nbsp;') )
        {// если код группы или имя были найдены - выведем их вместе 
            $programm = $programmname.' ['.$programmcode.']';
        }else
        {// не найдены - пустую строчку
            $programm = '&nbsp;';
        }
        return $programm;
    }
}


/** Класс формы для поиска контрактов
 *  по состояниям
 */
class sel_contract_form_search_status extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('search'));
        //поле подразделений
        $statuses = $this->dof->workflow('contracts')->get_list();
        foreach($statuses as $key => $status)
        {
            $statuses[$key] = $this->dof->get_string('status:'.$key,'contracts',NULL,'workflow');
        }
        $statuses = array('my_contracts' => $this->dof->get_string('my_contracts','sel'),
                          'all_statuses' => $this->dof->get_string('all_statuses','sel')) + $statuses;        
        $mform->addElement('select', 'status', $this->dof->get_string('status','sel').':', $statuses);        
        $mform->setdefault('status', $this->_customdata->search);
        $mform->addElement('submit', 'search', $this->dof->get_string('to_find','sel'));  
        $mform->closeHeaderBefore('formtitle');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}
?>