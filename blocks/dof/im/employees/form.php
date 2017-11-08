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
global $DOF;
$DOF->modlib('widgets')->webform();
if ( $DOF->plugin_exists('im', 'persons') )
{
    require_once($DOF->plugin_path('im', 'persons', '/form.php'));
}
/**
 * Класс редактирования статуса должности
 */
class dof_im_employees_positions_status_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'employees';
    }
    
    protected function workflow_code()
    {
        return 'positions';
    }
}

/**
 * Класс редактирования статуса вакансии
 */
class dof_im_employees_schpositions_status_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'employees';
    }
    
    protected function workflow_code()
    {
        return 'schpositions';
    }
}

/**
 * Класс редактирования статуса назначения на должность
 */
class dof_im_employees_appointments_status_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'employees';
    }
    
    protected function workflow_code()
    {
        return 'appointments';
    }
}
/**
 * Класс редактирования статуса назначения на должность
 */
class dof_im_employees_eagreements_status_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'employees';
    }
    
    protected function workflow_code()
    {
        return 'eagreements';
    }
}
/**
 * Класс редактирования должности
 */
class dof_im_employees_position_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control 
     */
    protected $dof;
    
    /** Возвращает название storage, из которого будут браться статусы
     * 
     * @return string - название плагина workflow
     */
    protected function storage_code()
    {
        return 'positions';
    }
    
    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    /** Объявление класса формы
     */
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        
        // добавляем скрытый параметр - id записи
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        // запоминаем ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // название должности
        $mform->addElement('text', 'name', $this->dof->get_string('name',$this->im_code()).':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        // это поле обязательно - добавим проверку (на клиенте и на сервере)
        $mform->addRule('name', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'client');
        $mform->addRule('name', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'server');
        // код должности
        $mform->addElement('text', 'code', $this->dof->get_string('code',$this->im_code()).':', 'size="20"');
        $mform->setType('code', PARAM_TEXT);
        // это поле обязательно - добавим проверку (на клиенте и на сервере)
        $mform->addRule('code', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'client');
        $mform->addRule('code', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'server');
        // подразделение
        // получаем список всех подразделений из базы
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        
        // добавляем элемент "подразделение"
        $mform->addElement('select', 'departmentid', 
                $this->dof->get_string('department', $this->im_code()).':', $departments);
        $mform->setType('departmentid', PARAM_INT);      
                
        $mform->setType('departmentid', PARAM_INT);
        $mform->addRule('departmentid', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'client');
        
        // кнопка смены статуса - показывается только если его можно поменять
        $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Дополнительное определение класса. Используется для динамических форм.
     */
    function definition_after_data()
    {
        $mform = $this->_form;
        if ( $id = $mform->getElementValue('id') )
        {// если элемент редактируется
            // добавим заголовок о редактировании
            $header =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('edit_position', $this->im_code()));
        }else
        {// если элемент создается
            // добавить заголовок о редактировании
            $header =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('new_position', $this->im_code()));
        }
        // добавляем заголовок в начало формы
        $mform->insertElementBefore($header, 'id');
        // добавляем галочку "автоматически создать вакансии"
        $confirmbox    =& $mform->createElement('checkbox', 'create_schpositions_confirm', null, 
                $this->dof->get_string('auto_create_positions', $this->im_code()));
        // добавим поле для количества автоматически создаваемых вакансий
        $schpositions  =& $mform->createElement('text', 'number_schpositions', 
                $this->dof->get_string('how_many_schpositions', $this->im_code()).':', 'size="2"');
        // добавим поле "количество часов"
        $numberofhours =& $mform->createElement('text', 'number_of_hours', 
                $this->dof->get_string('number_of_hours', $this->im_code()).':', 'size="2"');
        // добавляем поле количества вакансий перед кнопкой "сохранить"
        $mform->insertElementBefore($schpositions, 'save');
        // добавляем галочку подтверждения создания вакансий перед 
        // полем количества вакансий
        $mform->insertElementBefore($confirmbox, 'number_schpositions');
        // добавляем поле для количества часов для каждой вакансии
        $mform->insertElementBefore($numberofhours, 'save');
        
        // установим зависимость полей от галочки "создать вакансии"
        $mform->disabledIf('number_schpositions', 'create_schpositions_confirm', 'notchecked');
        $mform->disabledIf('number_of_hours', 'create_schpositions_confirm', 'notchecked');
        // установливаем типы данных добавленным элементам
        $mform->setType('number_schpositions', PARAM_INT);
        $mform->setType('number_of_hours', PARAM_INT);
        $mform->setType('create_schpositions_confirm', PARAM_BOOL);
        // По умолчанию устанавливаем 36 часов для каждой вакансии и как минимум 1 вакансию в начале
        $mform->setDefault('number_schpositions', 1);
        $mform->setDefault('number_of_hours', 36);
        // разрешим только положительные числа для количества часов у вакансии
        // проверка на стороне клиента
        $mform->addRule('number_of_hours', $this->dof->modlib('ig')->igs('form_err_numeric'), 
                'numeric', null, 'client');
        // проверка и на стороне сервера
        $mform->addRule('number_of_hours', $this->dof->modlib('ig')->igs('form_err_numeric'), 
                'numeric', null, 'server');
    }
    
    /** Проверки данных формы
     */
    function validation($data, $files)
    {
        $mform = $this->_form;
        $errors = array();
        if ( ! isset($data['departmentid']) OR ! $data['departmentid'] )
        {// не указано подразделение
            $errors['departmentid'] = $this->dof->get_string('form_err_not_set_department', $this->im_code());
        }elseif( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
        {// указанное подразделение не существует
            $errors['departmentid'] = $this->dof->get_string('form_err_unknown_department', $this->im_code());
        }
        if ( $positions = $this->dof->storage('positions')->get_records(array('code'=>$data['code'])) )
        {// код должности должен быть уникальным
            unset($positions[$data['id']]);
            if ( ! empty($positions) )
            {// код не уникален
                $errors['code'] = $this->dof->get_string('form_err_ununique_position_code', $this->im_code());
            }
        }
        if ( isset($data['create_schpositions_confirm']) AND $data['create_schpositions_confirm'] AND ( ! isset($data['number_schpositions']) OR ! $data['number_schpositions'] ) )
        {// поставлена галочка для автоматического создания вакансий, но не указано количество
            $errors['number_schpositions'] = $this->dof->get_string('form_err_not_set_number_schpositions', $this->im_code());
        }elseif ( $data['number_schpositions'] != (int)$data['number_schpositions'] )
        {// число вакансий не целое или вообще числом не является
            $errors['number_schpositions'] = $this->dof->get_string('form_err_wrong_number_schpositions_format', $this->im_code());
        }
        $hours = intval($data['number_of_hours']);
        if ( $hours > 40 OR $hours < 1 )
        {// количество часов в вакансии указано неверно
            // @todo вынести этот параметр в глобальные настройки FDO
            $errors['number_of_hours'] = $this->dof->get_string('form_err_incorrect_schposition_hours', $this->im_code());
        }
        // проверка на лимит
        if ( ! $data['id']  )
        {
            if ( ! $this->dof->storage('config')->get_limitobject('positions',$data['departmentid'] ) )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','employees');
            }
        }else 
        {
            $depid = $this->dof->storage('positions')->get_field($data['id'],'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('positions',$data['departmentid'] ) AND $depid != $data['departmentid'] )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','employees');
            }           
            
        }
        // обрезаем концевые пробелы у всех значений
        $mform->applyFilter('__ALL__', 'trim');
        // возвращаем ошибки, если они есть
        return $errors;
    }
}

/**
 * Класс формы редактирования вакансий
 */
class dof_im_employees_schposition_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control 
     */
    protected $dof;
    /** Возвращает название storage, из которого будут браться статусы
     * 
     * @return string - название плагина workflow
     */
    protected function storage_code()
    {
        return 'schpositions';
    }
    
    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    /** Объявление класса формы
     */
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        
        // добавляем скрытый параметр - id записи
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        // запоминаем ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // подразделение
        // получаем список всех подразделений из базы
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        
        // добавляем элемент "подразделение"
        $mform->addElement('select', 'departmentid', 
                $this->dof->get_string('department', $this->im_code()).':', $departments);
        $mform->setType('departmentid', PARAM_INT);
        // получаем список всех должностей из базы
        $positions = $this->get_positions();
        // добавляем элемент "должность"
        $mform->addElement('select', 'positionid', 
                $this->dof->get_string('position', $this->im_code()).':', $positions);
        $mform->setType('positionid', PARAM_INT);
        // ставка
        $mform->addElement('text', 'worktime', $this->dof->get_string('worktime',$this->im_code()).':', 'size="2"');
        $mform->setType('worktime', PARAM_NUMBER);
        // это поле обязательно - добавим проверку (на клиенте и на сервере)
        $mform->addRule('worktime', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'client');
        $mform->addRule('worktime', $this->dof->modlib('ig')->igs('form_err_required'), 'required',null,'server');
        $mform->addRule('worktime', $this->dof->get_string('number_only','employees'), 'numeric', null, 'client');
        $mform->addRule('worktime', $this->dof->get_string('number_only','employees'), 'numeric', null, 'server');
        // кнопка смены статуса - показывается только если его можно поменять
        $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Дополнительное определение класса. Используется для динамических форм.
     */
    function definition_after_data()
    {
        $mform     = $this->_form;
        
        if ( $id = $mform->getElementValue('id') )
        {// если элемент редактируется
            // создаем элемент
            $element =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('edit_schposition', $this->im_code()));
            // если мы редактируем существующую выкансию - то установим
            // значения по умолчанию для элементов "подразделение" и "должность"
            $mform->setDefault('departmentid', $mform->getElementValue('departmentid'));
            $mform->setDefault('positionid', $mform->getElementValue('positionid'));
        }else
        {// если элемент создается
            $element =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('new_schposition', $this->im_code()));
        }
        // приведем количество часов в ставке к нормальному виду отображения
        $worktime = $mform->getElementValue('worktime');
        $worktime = round($worktime, 2);
        $mform->setDefault('worktime', $worktime);
        // добавляем заголовок в начало формы
        $mform->insertElementBefore($element, 'id');
    }
    
    /** Проверки данных формы
     */
    function validation($data, $files)
    {
        $mform = $this->_form;
        $errors = array();
        if ( ! isset($data['departmentid']) OR ! $data['departmentid'] )
        {// не указано подразделение
            $errors['departmentid'] = $this->dof->get_string('form_err_not_set_department', $this->im_code());
        }elseif( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
        {// указанное подразделение не существует
            $errors['departmentid'] = $this->dof->get_string('form_err_unknown_department', $this->im_code());
        }
        if ( ! isset($data['positionid']) OR ! $data['positionid'] )
        {// не указана должность
            $errors['positionid'] = $this->dof->get_string('form_err_not_set_position', $this->im_code());
        }elseif( ! $this->dof->storage('positions')->is_exists($data['positionid']) )
        {// указанная должность не существует
            $errors['positionid'] = $this->dof->get_string('form_err_unknown_position', $this->im_code());
        }
        // проверка на лимит
        if ( ! $data['id']  )
        {
            if ( ! $this->dof->storage('config')->get_limitobject('schpositions',$data['departmentid'] ) )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','employees');
            }  
        }else 
        {// редактирование - переносить нельзя в переполненые
            $depid = $this->dof->storage('schpositions')->get_field($data['id'],'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('schpositions',$data['departmentid'] ) AND $depid != $data['departmentid'] )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','employees');
            }           
        }
        if ( empty($data['worktime']) OR  $data['worktime'] <= 0  )
        {
            $errors['worktime'] = $this->dof->get_string('number_only','employees');
        }
        // обрезаем концевые пробелы у всех значений
        $mform->applyFilter('__ALL__', 'trim');
        // возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Получить список доступных должностей
     * @return array массив должностей для select-элемента
     */
    protected function get_positions()
    {
        $positions = $this->dof->storage('positions')->
                get_records(array('status'=>array('plan','active')), 'name');
        $usepositions = array();
        // оставляем только те, на которые пользователь имеет права
        foreach ( $positions as $position )
        {
            if ( ! $this->dof->storage('positions')->is_access('use',$position->id) )
            {// пользователь не имеет права создавать расписание на этот поток - пропускаем его
                continue;
            }
            $usepositions[$position->id] = $position;
        }
        
        return $this->dof_get_select_values($usepositions);
    }
}


/**
 * Класс редактирования назначения на должность
 */
class dof_im_employees_appointment_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control 
     */
    protected $dof;
    
    /** Возвращает название storage, из которого будут браться статусы
     * 
     * @return string - название плагина workflow
     */
    protected function storage_code()
    {
        return 'appointments';
    }
    
    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    /** Объявление класса формы
     */
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        $this->appointment  = $this->dof->storage('appointments')->get($this->_customdata->id);
        $this->eagreement   = $this->dof->storage('eagreements')->get($this->_customdata->eagreementid);
        
        // добавляем скрытый параметр - id записи
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','eaid', $this->_customdata->eagreementid);
        $mform->setType('eaid', PARAM_INT);
        // запоминаем ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // договор
        if ( $this->eagreement )
        {// табельный номер создается для конкретного сотрудника, 
            // или табельный номер редактируется. Запрещаем изменять договор, 
            // ускоряем загрузку страницы
            $options = array();
            $options[$this->eagreement->id] = $this->dof->storage('persons')->
                        get_fullname($this->eagreement->personid).' ['.$this->eagreement->num.']';
            // добавляем элемент "договоры"
            $mform->addElement('select', 'eagreementid', 
                    $this->dof->get_string('eagreement', $this->im_code()).':', $options);
            $mform->setType('eagreementid', PARAM_INT);
        }else
        {// Табельный номер создается "с чистого листа"
            // Указываем из какого плагина запрашивать данные
            $options = array();
            $options['plugintype'] =   "storage";
            $options['plugincode'] =   "eagreements";
            $options['querytype']  =   "list_eagreements";
            $options['sesskey']    =   sesskey();
            $options['type']       =   'autocomplete';
            // установим значение по умолчанию
            //$mas = array( 19 => 'primer' );
            //$options['option'] = $mas; 
            
            // используем ajax-autocomplete для ускорения загрузки страницы
            $mform->addElement('dof_autocomplete', 'eagreementid',
                    $this->dof->get_string('eagreement', $this->im_code()).':',
                    array('style' => 'width:100%'), $options);
        }
        
        // вакансия
        // создаем массив нужной структуры для элемента select
        $options     = $this->get_list_schpositions();
        // добавляем элемент "вакансии"
        $mform->addElement('select', 'schpositionid', 
                $this->dof->get_string('position', $this->im_code()).':', $options);
        $mform->setType('schpositionid', PARAM_INT);
        // табельный номер
        $mform->addElement('text', 'enumber', $this->dof->get_string('enumber',$this->im_code()).':', 'size="20"');
        $mform->setType('enumber', PARAM_TEXT);
        // совместитель
        $mform->addElement('checkbox', 'combination', null, $this->dof->get_string('combination', $this->im_code()));
        // ставка
        $mform->addElement('text', 'worktime', $this->dof->get_string('worktime',$this->im_code()).':', 'size="20"');
        $mform->setType('worktime', PARAM_NUMBER);
        // дата назначения
        $mform->addElement('date_selector', 'date', $this->dof->get_string('date',$this->im_code()).':');
        // подразделение
        // получаем список всех подразделений из базы
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        
        // добавляем элемент "подразделение"
        $mform->addElement('select', 'departmentid', 
                $this->dof->get_string('department', $this->im_code()).':', $departments);
        $mform->setType('departmentid', PARAM_INT);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        // кнопка смены статуса - показывается только если его можно поменять
        $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('save'));
    }
    
    /** Дополнительное определение класса. Используется для динамических форм.
     */
    function definition_after_data()
    {
        $mform     = $this->_form;
        
        if ( $id = $mform->getElementValue('id') )
        {// если элемент редактируется
            // создаем заголовок формы
            $header =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('edit_appointment', $this->im_code()));
        }else
        {// если элемент создается
            // создаем заголовок формы
            $header =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('new_appointment', $this->im_code()));
        }
        // добавляем заголовок в начало формы
        $mform->insertElementBefore($header, 'id');
        // проверки
        $mform->addRule('eagreementid', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('schpositionid', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('departmentid', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('enumber', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('worktime', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('worktime', $this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric');
    }
    
    /** Проверки данных формы
     */
    function validation($data, $files)
    {
        $error = array();
        // проверим существование договора
        if ( is_array($data['eagreementid']) )
        {// данные отправлялись через autocomplete
            $eagreementid = $data['eagreementid']['id_autocomplete'];
        }else
        {// данные отправлялись через select
            $eagreementid = $data['eagreementid'];
        }
        if ( ! $this->dof->storage('eagreements')->is_exists($eagreementid) )
        {// договор не существует, сообщим об этом
            $error['eagreementid'] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
        }
        
        // проверим существование вакансии
        if ( ! $this->dof->storage('schpositions')->is_exists($data['schpositionid']) )
        {// вакансия не существует, сообщим об этом
            $error['schpositionid'] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
        }
        // проверим существование подразделения
        if ( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
        {// подразделение не существует, сообщим об этом
            $error['departmentid'] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
        }
        // проверим табельного номера
        if ( ! $data['enumber'] )
        {// номер не введен
            $error['enumber'] = $this->dof->modlib('ig')->igs('form_err_required');
        }elseif ( ((isset($this->appointment->enumber) AND $this->appointment->enumber <> $data['enumber']) 
                  OR empty($this->appointment->enumber)) AND
                  ! $this->dof->storage('appointments')->is_enumber_unique($data['enumber']) )
        {// номер введен, но не уникальный
            $error['enumber'] = $this->dof->get_string('error_unique_enumber','employees');
        }
        // проверим правильность введения ставки
        if ( ! $data['worktime'] )
        {// ставка не указана
            $error['worktime'] = $this->dof->modlib('ig')->igs('form_err_required');
        }elseif ( isset($this->appointment->worktime) )
        {// объект редактируется
            if  ( $this->appointment->worktime <> $data['worktime'] AND 
                  $data['worktime'] > $this->appointment->worktime + 
                  $this->dof->storage('appointments')->get_free_worktime($data['schpositionid']) )
            {// отредактировали ставку - проверим чтобы она не превышала допустимое значение
                $error['worktime'] = $this->dof->get_string('error_free_worktime','employees',
                                     $this->appointment->worktime + $this->dof->storage('appointments')->
                                     get_free_worktime($data['schpositionid']));
            }
        }elseif( $data['worktime'] > $this->dof->storage('appointments')->get_free_worktime($data['schpositionid']) )
        {// ставка указана, но больше допустимой
            $error['worktime'] = $this->dof->get_string('error_free_worktime','employees',
                                 $this->dof->storage('appointments')->
                                 get_free_worktime($data['schpositionid']));
        }
        
        // проверка на лимит
        if ( ! $data['id']  )
        {
            if ( ! $this->dof->storage('config')->get_limitobject('appointments',$data['departmentid'] ) )
            {
                $error['departmentid'] = $this->dof->get_string('limit_message','employees');
            } 
        }else 
        {// редактирование - переносить нельзя в переполненые
            $depid = $this->dof->storage('appointments')->get_field($data['id'],'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('appointments',$data['departmentid'] ) AND $depid != $data['departmentid'] )
            {
                $error['departmentid'] = $this->dof->get_string('limit_message','employees');
            }           
        }
        
        return $error;
    }
    /** Возвращает список вакансий
     * @return array
     */
    function get_list_schpositions()
    {
        $options = array( 0 => '--- '.$this->dof->modlib('ig')->igs('choose').' ---' );
        // получаем список всех вакансий из базы
        if ( $schpositions = $this->dof->storage('schpositions')->
                             get_records(array('status'=>array('plan','active'))) )
        {// если что-то нашли
            foreach ( $schpositions as $schposition )
            { 
                // найдем свободное время ставки
                $time = $this->dof->storage('appointments')->get_free_worktime($schposition->id);
                if ( $time == 0 )
                {// времени нет
                    if ( isset($this->appointment->schpositionid) 
                         AND $this->appointment->schpositionid <> $schposition->id 
                         OR empty($this->appointment->schpositionid) )
                    {// и вакансия не редактируется
                        continue;
                    }
                }
                // строка меню с указанием свободной ставки
                $position = $this->dof->storage('positions')->get_field($schposition->positionid,'name').
                            ' ('.$this->dof->storage('departments')->get_field($schposition->departmentid,'code').') ['.
                                $this->dof->storage('positions')->get_field($schposition->positionid,'code').
                            '] '.$this->dof->get_string('worktime_lt','employees').' '.$time.
                            ' '.$this->dof->get_string('from','employees').' '.round($schposition->worktime,2);
                $options[$schposition->id] = $position; 
            }
        }
        asort($options);
        return $options;
    }
}

/**
 * Класс редактирования договоров
 */
class dof_im_employees_eagreement_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control 
     */
    public $dof;
    public $appointment;
    
    /** Возвращает название storage, из которого будут браться статусы
     * 
     * @return string - название плагина workflow
     */
    protected function storage_code()
    {
        return 'eagreements';
    }
    
    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    /** Объявление класса формы
     */
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        $this->appointment = $this->dof->storage('appointments')->get($this->_customdata->id);
        
        // добавляем скрытый параметр - id записи
        $mform->addElement('hidden','id', 0);
        $mform->setType('id', PARAM_INT);
        // запоминаем ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // договора
         
        // Добавляем элемент позволяющий выбрать: откуда брать пользователя: из moodle или из fdo
        // получаем типы пользователей, которых мы можем выбрать
        $usertypes   = $this->get_user_types();
        // создаем массив для второго уровня hierselect
        $userlist[0]    = $this->dof_get_select_values();
        // получаем список пользователей из таблицы persons и добавляем к ним нулевой элемент
        $userlist['fdo']    = $this->dof_get_select_values() + $this->get_list_persons();
        // получаем список пользователей из moodle и добавляем к ним нулевой элемент
        $userlist['moodle'] = $this->dof_get_select_values() + $this->get_list_mdlusers();
        
        // добавляем элемент hierselect для указания типа  пользователя и самого пользователя
        $userselect = $mform->addElement('hierselect', 'userid', 
                $this->dof->get_string('user_type', $this->im_code()).':<br/>'.
                $this->dof->get_string('name_employee', $this->im_code()).':', 'null', '<br/>');
        $userselect->setOptions(array($usertypes, $userlist));
        $mform->disabledIf('userid', 'id', 'noteq', 0);
        // дата назначения
        $mform->addElement('date_selector', 'date', $this->dof->get_string('date',$this->im_code()).':');
        // Заметки
        $mform->addElement('textarea', 'notice', $this->dof->get_string('notice',$this->im_code()).':', 'cols="60" rows="10"');
        $mform->setType('notice', PARAM_TEXT);
         // вакансии
        // создаем массив нужной структуры для элемента select
        $options     = $this->get_list_schpositions();
        // добавляем элемент "вакансии"
        $mform->addElement('select', 'schpositionid', 
                $this->dof->get_string('schposition', $this->im_code()).':', $options);
        $mform->setType('schpositionid', PARAM_INT);
        // табельный номер
        $mform->addElement('text', 'enumber', $this->dof->get_string('enumber',$this->im_code()).':', 'size="50%"');
        $mform->setType('enumber', PARAM_TEXT);
        
        // Номер договора (можно редактировать только администратору и завучу)
        // @todo перенести создание этого элемента в definition_after_data
        // и запрашивать право ручного указания номера договора и право редактировать номер договора отдельно
        // @todo добавить сзади элемента галочку "присвоить автоматически"
        if ( $this->dof->storage('eagreements')->is_access('edit:num') )
        {
            $mform->addElement('text', 'num', $this->dof->get_string('full_num',$this->im_code()).':', 'size="20"');
            $mform->setType('enumber', PARAM_TEXT);
        }else
        {
            $mform->addElement('static', 'num', $this->dof->get_string('full_num',$this->im_code()).':', 'size="20"');
        }
        
        
        // ставка
        $mform->addElement('text', 'worktime', $this->dof->get_string('worktime',$this->im_code()).':', 'size="20"');
        $mform->setType('worktime', PARAM_NUMBER);
        // подразделение
        // получаем список всех подразделений из базы
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        
        // добавляем элемент "подразделение"
        $mform->addElement('select', 'departmentid', 
                $this->dof->get_string('department', $this->im_code()).':', $departments);
        $mform->setType('departmentid', PARAM_INT);
        // кнопка смены статуса - показывается только если его можно поменять
        $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Дополнительное определение класса. Используется для динамических форм.
     */
    function definition_after_data()
    {
        $mform = $this->_form;
        
        if ( $id = $mform->getElementValue('id') )
        {// если элемент редактируется
            // создаем заголовок
            $element =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('edit_eagreement', $this->im_code()));
            // устанавливаем значения по умолчанию для элемента "пользователь"
            // если договор редактируется - значит сотрудник точно из FDO
            $personid = $mform->getElementValue('personid');
            $mform->setDefault('userid', array('fdo', $personid));
        }else
        {// если элемент создается
            $element =& $mform->createElement('header','formtitle', 
                    $this->dof->get_string('new_eagreement', $this->im_code()));
        }
        // добавляем заголовок в начало формы
        $mform->insertElementBefore($element, 'id');
        $mform->addElement('hidden','eagreementid', 
            $this->dof->storage('appointments')->get_field('eagreementid',$id));
        $mform->setType('eagreementid', PARAM_INT);
        
        // проверки
        $mform->addRule('schpositionid', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('departmentid', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('enumber', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('worktime', $this->dof->modlib('ig')->igs('form_err_required'), 'required');
        $mform->addRule('worktime', $this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric');
    }
    
    /** Проверки данных формы
     * 
     */
    function validation($data, $files)
    {
        $error = array();
        // проверим, правильноли указан тип пользователя и сам пользователь 
        if ( ! $data['userid'][0] )
        {// не указано откуда брать пользователя
            $error['userid'] = $this->dof->get_string('error_no_type_selected',$this->im_code());
        }elseif ( ! $data['userid'][1] )
        {// не указан id пользователя
            $error['userid'] = $this->dof->get_string('error_no_user_selected',$this->im_code());
        }
        // проверим существование вакансии
        if ( ! $this->dof->storage('schpositions')->is_exists($data['schpositionid']) )
        {// вакансия не существует, сообщим об этом
            $error['schpositionid'] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
        }
        // проверим существование подразделения
        if ( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
        {// подразделение не существует, сообщим об этом
            $error['departmentid'] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
        }
        // проверим табельный номер
        if ( ! $data['enumber'] )
        {// номер не введен
            $error['enumber'] = $this->dof->modlib('ig')->igs('form_err_required');
        }elseif ( ((isset($this->appointment->enumber) AND $this->appointment->enumber <> $data['enumber']) 
                  OR empty($this->appointment->enumber)) AND
                  ! $this->dof->storage('appointments')->is_enumber_unique($data['enumber']) )
        {// номер введен, но не уникальный
            $error['enumber'] = $this->dof->get_string('error_unique_enumber',$this->im_code());
        }
        // проверяем правильность указания полного номера договора
        if ( isset($data['num']) AND trim($data['num']) )
        {
            if ( $data['eagreementid'] )
            {// номер договора указывается при редактировании
                if ( ! $this->dof->storage('eagreements')->is_access('edit:num', $data['eagreementid']) )
                {// нет нужных прав
                    // @todo убрать обращение к праву manage после полного перехода на новую систему полномочий 
                    $error['num'] = $this->dof->modlib('ig')->igs('no_access_for_this_action');
                }
                if ( $duplicates = $this->dof->storage('eagreements')->
                   get_records(array('num'=>trim($data['num']), 'status'=>array('plan', 'active'))) )
                {
                    unset($duplicates[$data['eagreementid']]);
                    //print_object($data);
                    //var_dump($data['id']);
                    if ( ! empty($duplicates) )
                    {
                        $error['num'] = $this->dof->get_string('full_num_is_not_unique',$this->im_code());
                    }
                }
            }else
            {// номер договора указывается при создании
                if ( ! $this->dof->storage('eagreements')->is_access('edit:num') )
                {// нет нужных прав
                    $error['num'] = $this->dof->modlib('ig')->igs('no_access_for_this_action');
                }
            }
            
        }
        // проверим правильность введения ставки
        if ( ! $data['worktime'] )
        {// ставка не указана
            $error['worktime'] = $this->dof->modlib('ig')->igs('form_err_required');
        }elseif ( isset($this->appointment->worktime) )
        {// объект редактируется
            if  ( $this->appointment->worktime <> $data['worktime'] AND 
                  $data['worktime'] > $this->appointment->worktime + 
                  $this->dof->storage('appointments')->get_free_worktime($data['schpositionid']) )
            {// отредактировали ставку - проверим чтобы она не превышала допустимое значение
                $error['worktime'] = $this->dof->get_string('error_free_worktime','employees', 
                                     $this->appointment->worktime + $this->dof->storage('appointments')->
                                     get_free_worktime($data['schpositionid']));
            }
        }elseif( $data['worktime'] > $this->dof->storage('appointments')->get_free_worktime($data['schpositionid']) )
        {// ставка указана, но больше допустимой
            $error['worktime'] = $this->dof->get_string('error_free_worktime',$this->im_code(),
                                 $this->dof->storage('appointments')->
                                 get_free_worktime($data['schpositionid']));
        }
        // проверка на лимит
        if ( ! $data['id']  )
        {
            if ( ! $this->dof->storage('config')->get_limitobject('eagreements',$data['departmentid'] ) )
            {
                $error['departmentid'] = $this->dof->get_string('limit_message','employees');
            }  
        }else 
        {// редактирование - переносить нельзя в переполненые
            $depid = $this->dof->storage('eagreements')->get_field($data['id'],'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('eagreements',$data['departmentid'] ) AND $depid != $data['departmentid'] )
            {
                $error['departmentid'] = $this->dof->get_string('limit_message','employees');
            }           
        }
        
        return $error;
    }
    
    /** Получает список типов пользователей
     * 
     * @return array массив для элемента select в формате array('тип' => 'название типа')
     */
    private function get_user_types()
    {
        // получим первый стандартный элемент
        $options = $this->dof_get_select_values();
        $options['fdo']    = $this->dof->get_string('fdo_user',    $this->im_code());
        $options['moodle'] = $this->dof->get_string('moodle_user', $this->im_code());
        
        return $options;
    }
    
    /** Получить список пользователей из хранилища persons
     * 
     * @return массив для элемента select в формате array('id' => 'ФИО')
     */
    function get_list_persons()
    {
        $options = array( 0 => '--- '.$this->dof->modlib('ig')->igs('choose').' ---' );
        // получаем список всех подразделений из базы
        if ( $persons = $this->dof->storage('persons')->gget_records(array()) )
        {
            foreach ( $persons as $person )
            {
                $name = $this->dof->storage('persons')->get_fullname($person->id);
                $options[$person->id] = $name; 
            }
        }
        asort($options);
        return $options;
    }
    
    /** Получает список пользователей moodle
     * 
     * @return array - массив пользователей для элемента select array( 'moodleid' => 'ФИО')
     * @todo исключить из массива пользователей fdo
     */
    private function get_list_mdlusers()
    {
         // получаем список всех не удаленных пользователей из moodle 
         $users = $this->dof->modlib('ama')->user(false)->get_list(array('deleted' => '0'), 'lastname ASC');
         if ( ! $users OR empty($users) )
         {// данные не получены, вернем только "выбрать"
             return $this->dof_get_select_values();
         }
         // убираем из списка пользователей гостя и администратора
         //unset($users[1]);
         //unset($users[2]);
         // добавляем пункт "выбрать"
         $options = $this->dof_get_select_values();
         foreach ( $users as $user )
         {// составляем комбинацию ФИО для каждого пользователя moodle
             $options[$user->id] = $user->lastname.' '.$user->firstname;
         }
         // преобразовываем список к виду, пригодному для использования в элемента Select
         return $options;
    }
    
    /** Возвращает список вакансий
     * @return array
     */
    function get_list_schpositions()
    {
        $options = array( 0 => '--- '.$this->dof->modlib('ig')->igs('choose').' ---' );
        // получаем список всех вакансий из базы
        if ( $schpositions = $this->dof->storage('schpositions')->
                             get_records(array('status'=>array('plan','active'))) )
        {// если что-то нашли
            foreach ( $schpositions as $schposition )
            { 
                // найдем свободное время ставки
                $time = $this->dof->storage('appointments')->get_free_worktime($schposition->id);
                if ( $time == 0 )
                {// времени нет
                    if ( isset($this->appointment->schpositionid) 
                         AND $this->appointment->schpositionid <> $schposition->id 
                         OR empty($this->appointment->schpositionid) )
                    {// и вакансия не редактируется
                        continue;
                    }
                }
                // строка меню с указанием свободной ставки
                $position = $this->dof->storage('positions')->get_field($schposition->positionid,'name').
                            ' ('.$this->dof->storage('departments')->get_field($schposition->departmentid,'code').') ['.
                                $this->dof->storage('positions')->get_field($schposition->positionid,'code').
                            '] '.$this->dof->get_string('worktime_lt','employees').' '.$time.
                            ' '.$this->dof->get_string('from','employees').' '.round($schposition->worktime,2);
                $options[$schposition->id] = $position; 
            }
        }
        asort($options);
        return $options;
    }
}
/*
 * Класс формы для ввода данных договора (первая страничка)
 */
class dof_im_employees_eagreement_edit_form_one_page extends dof_modlib_widgets_form
{

    protected $dof;
    /** Возвращает название storage, из которого будут браться статусы
     * 
     * @return string - название плагина workflow
     */
    protected function storage_code()
    {
        return 'eagreements';
    }
    
    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    function definition()
    {
        global $DOF;
        $mform =& $this->_form;
        $this->dof = $DOF;
        // дата заключения договора - заголовок
        // Если контракт редактируется передаем id контракта
        $mform->addElement('hidden', 'id',$this->_customdata->id);
        $mform->setType('id', PARAM_INT);
        //$mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        //$mform->setType('departmentid', PARAM_INT);
        // обьявляем заголовок формы
        $mform->addElement('header','cldheader', $this->dof->get_string('cldheader', $this->im_code()));

        // дата заключения договора
        $mform->addElement('date_selector', 'date', $this->dof->get_string('date', $this->im_code()));
        $mform->setType('date', PARAM_INT);
        $depart =$this->dof->storage('departments')->departments_list_subordinated(null,'0',null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $depart = $this->dof_get_acl_filtered_list($depart, $permissions);
        
        $mform->addElement('select', 'departmentid', $this->dof->get_string('department', $this->im_code()).':', $depart);
        $mform->setType('departmentid', PARAM_TEXT);
        $mform->addRule('departmentid',$this->dof->get_string('err_required', $this->im_code()), 'required', null, 'client');
        $mform->addElement('textarea', 'notice', $this->dof->get_string('notice', $this->im_code()), 'cols="60" rows="10"');
        if ( $this->_customdata->id AND $this->dof->storage('eagreements')->is_access('edit:num') )
        {
            $mform->addElement('text', 'num', $this->dof->get_string('full_num',$this->im_code()).':', 'size="20"');
            $mform->setType('num', PARAM_TEXT);
        }
        // ученик
        $stoptions = '';
        if ( $this->_customdata->edit_person == false )
        {// нельзя редактировать студента
            // закроем поля
            $stoptions = 'disabled';
        }
        $mform->addElement('header','empheader', $this->dof->get_string('employee', $this->im_code()));
        $mform->addElement('text', 'personid', $this->dof->get_string('userid',$this->im_code()), $stoptions);
        $mform->setType('personid', PARAM_INT);
        $mform->addElement('radio', 'person', null, $this->dof->modlib('ig')->igs('new_mr'),'new', $stoptions);
        $mform->addElement('radio', 'person', null, $this->dof->get_string('personid',$this->im_code()),'personid', $stoptions);
        $mform->addElement('radio', 'person', null, $this->dof->get_string('mdluser',$this->im_code()),'mdluser', $stoptions);
        $mform->setType('person', PARAM_TEXT);
        if ( $stoptions <> 'disabled' )
        {// студент редактируется, поставим disebled по умолчанию
            $mform->disabledIf('personid', 'person','eq','new');
        }

        // Кнопка "продолжить и отмена"
        $this->add_action_buttons(true, $this->dof->modlib('ig')->igs('continue'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        $errors = array();
        if ( ! isset($data['person']) )
        {// одно из полей выборки должно быть выброно
            $errors['person'] = $this->dof->get_string('error_choice',$this->im_code());
        }
        if ( ! isset($data['departmentid']) )
        {// подразделение нужно указать
            $errors['departmentid'] = $this->dof->get_string('err_required',$this->im_code());
        }
        if ( $this->_customdata->edit_person = false AND empty($data['personid']) AND
               (($data['person'] == 'new') OR ($data['person'] == 'mdluser') OR
                   ($data['personid'] <> $this->_customdata->personid)) )
        {// нельзя менять id студента
            $errors['personid'] = $this->dof->get_string('error_persons',$this->im_code(),$data['personid']);
        }
        // проверим персону на использование
        if ( $data['person'] == 'personid' )
        {//персона наша - запомним
            $personid = $data['personid'];
        }elseif ( $data['person'] == 'mdluser' )
        {//пользователь moodle - найдем нашу персону
            $personid = $this->dof->storage('persons')->get_by_moodleid_id($data['personid']);
        }
        if ( ( empty($this->_customdata->personid) AND (! empty($personid)) AND
             (! $this->dof->storage('persons')->is_access('use',$personid)) ) OR
             ( (! empty($this->_customdata->personid)) AND isset($personid) AND 
             ($personid<>$this->_customdata->personid) AND
             (! $this->dof->storage('persons')->is_access('use',$personid))) )
        {// нельзя менять id студента
            $errors['personid'] = $this->dof->modlib('ig')->igs('form_err_no_use_object');
        }
        // 
        if ( $eagreements = $this->dof->storage('eagreements')->get($data['id']) )
        {// если указан клиент и его адрес менялся
            if ( isset($data['num']) AND ($data['num'] <> $eagreements->num) AND 
                 $this->dof-> storage('eagreements')->is_exists(array('num'=>$data['num']))
                 AND ! $this->dof->storage('eagreements')->is_access('edit:num') )
            {// емайл должен быть уникальным
                $errors['num'] = $this->dof->get_string('err_num_nounique', $this->im_code());
            }
            // лимит объектов
            $depid = $eagreements->departmentid;
            if ( ! $this->dof->storage('config')->get_limitobject('eagreements',$data['departmentid'] ) AND $data['departmentid'] != $depid )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message',$this->im_code());
            }             
        } else
        {// если клиента нет
            if ( isset($data['num']) AND $this->dof->storage('eagreements')->
                     get_records(array('num'=>$data['num'])) )
                     //,'status',array('tmp','new','clientsign','wesign','work','frozen','archives')) )
            {// емайл должен быть уникальным
                $errors['num'] = $this->dof->get_string('err_num_nounique', $this->im_code());
            }
            // лимит объектов
            if ( ! $this->dof->storage('config')->get_limitobject('eagreements',$data['departmentid'] ) )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message',$this->im_code());
            }             
        }
        return $errors;
    }

    /** Обработчик формы
     * 
     * @param array $urloptions - массив дополнительных параметров для ссылки при редиректе
     */
    public function process()
    {
        $addvars = array();
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу просмотра договоров
            redirect($this->dof->url_im($this->im_code(),'/list.php?',$addvars));
        }
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// Получили данные формы';
            //print_object($formdata);//die;
            // $formdata = (array) $formdata;
            // Обновляем/создаем контракт
            $eagreement = new stdClass();
            switch ($formdata->person)
            {   
                case 'new':
                    // если указано что ученик создается с нуля - занесем в контракт 0
                    $eagreement->personid = 0;
                break;
                case 'personid':
                    
                    // если ученик это пользователь деканата 
                    if ( $this->dof->storage('persons')->is_exists($formdata->personid) )
                    {// если пользователь найден - запишем его как ученика контракта
                        $eagreement->personid = $formdata->personid;
                    }else
                    {// не найден - сообщение об ошибке
                        return $this->dof->get_string('error_persons', $this->im_code(), $formdata->personid).'<br>';
                    }
                break;
                case 'mdluser':
                    
                    // если ученик указан как пользователь Moodle
                    if ( ! empty($formdata->personid) AND ($formdata->personid <> 1) AND 
                         $this->dof->modlib('ama')->user(false)->is_exists($formdata->personid) AND
                                 $user = $this->dof->modlib('ama')->user($formdata->personid)->get()   )
                    {// если пользователь Moodle найден и его id не равно 1
                        
                        if ( $personid = $this->dof->storage('persons')->get_by_moodleid_id($formdata->personid) )
                        {// персона уже зарегестрирована - записываем как ученика контракта
                            $eagreement->personid = $personid;
                        }elseif ( $personid = $this->dof->storage('persons')->reg_moodleuser($user) )
                        {// регестрируем персону и записываем как ученика контракта
                            $eagreement->personid = $personid;
                        }else
                        {// не удалось зарегестрировать - сообщим об ошибке
                            return $this->dof->get_string('error_save_persons', $this->im_code(), $formdata->personid).'<br>';
                        }
                    }else
                    {// пользователь не найден - сообщим об этом
                        return $this->dof->get_string('error_mdluser', $this->im_code(), $formdata->personid).'<br>';
                    }
                break;
                default:
                    // ничего не выбрано - это ошибка    
                    return $this->dof->get_string('error_choice', $this->im_code()).'<br>';
                break;
            }
        
            // print_object($contract);
            // сохраняем контракт
            if ( isset($eagreement->personid) )
            {// если id студента и клиента введены верно';
                $eagreement->departmentid = $formdata->departmentid;
                $eagreement->notice = $formdata->notice;
                $eagreement->date = $formdata->date + 3600*12;
                if ( $this->_customdata->id AND $this->dof->storage('eagreements')->is_access('edit:num') )
                {// имеем право эдить номер договора
                    $eagreement->num = $formdata->num;
                }
                if ( $this->_customdata->id )
                {// id контракта указано - редактируем договор
                    if ( $this->dof->storage('eagreements')->update($eagreement,$this->_customdata->id) )
                    {// все в порядке - переходим ко второй странице
                         redirect($this->dof->url_im($this->im_code(),"/edit_eagreement_two.php?id={$this->_customdata->id}",$addvars), '', 0);
                    }else
                    {// ошибка сохранения
                        return $this->dof->get_string('error_save', $this->im_code(), $this->dof->get_string('m_contract', $this->im_code())).'<br>';
                    }
                }else
                {// добавляем договор
                    if ( $eagreement_id = $this->dof->storage('eagreements')->insert($eagreement) )
                    {// все в порядке - переходим ко второй странице
                        redirect($this->dof->url_im($this->im_code(),"/edit_eagreement_two.php?id={$eagreement_id}",$addvars), '', 0);
                    }else
                    {// ошибка сохранения
                        return $this->dof->get_string('error_save', $this->im_code(), $this->dof->get_string('m_contract', $this->im_code())).'<br>';
                    }
                }
            }
        }
    }


}


/*
 * Класс формы для ввода данных договора (вторая страничка)
 */
class dof_im_employees_eagreement_edit_form_two_page extends dof_im_persons_edit_form
{
    protected function storage_code()
    {
        return 'eagreements';
    }
    protected function im_code()
    {
        return 'employees';
    }
    
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
        $this->eagreementid = $this->_customdata->eagreementid;
        $this->eagreement = $this->dof->storage('eagreements')->get($this->eagreementid);
    
        // Скрытые поля
        $mform->addElement('hidden', 'eagreementid', $this->eagreementid);
        $mform->setType('eagreementid', PARAM_INT);
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
        
        // Форма назначения на должность
        if ( $this->_customdata->countappoint == false )
        {// Отобразить форму назначения на должность
            $mform->addElement('hidden', 'appointmentid', $this->_customdata->appointment->id);
            $mform->setType('appointmentid', PARAM_INT);
            //создаем или редактируем подписку на программу
            $mform->addElement('header','header', $this->dof->get_string('create_appoint', $this->im_code()));
            $mform->addElement('checkbox', 'appoint',null, $this->dof->get_string('create_appoint', $this->im_code()));
            // вакансии
            // создаем массив нужной структуры для элемента select
            $options = $this->get_list_schpositions();
            // добавляем элемент "вакансии"
            $mform->addElement(
                'select', 
                'schpositionid', 
                $this->dof->get_string('position', $this->im_code()).':', 
                $options
            );
            $mform->disabledIf('schpositionid', 'appoint');
            $mform->setType('schpositionid', PARAM_INT);
            // табельный номер
            $mform->addElement('text', 'enumber', $this->dof->get_string('enumber',$this->im_code()).':');
            $mform->setType('enumber', PARAM_TEXT);
            $mform->disabledIf('enumber', 'appoint');
            // ставка
            $mform->addElement('text', 'worktime', $this->dof->get_string('worktime',$this->im_code()).':');
            $mform->setType('worktime', PARAM_NUMBER);
            $mform->disabledIf('worktime', 'appoint');
            // дата назначения
            $mform->addElement('date_selector', 'date', $this->dof->get_string('date',$this->im_code()).':');
            $mform->disabledIf('date', 'appoint');

        } else
        {// Создание ссылок на назначения
            $mform->addElement(
                'header',
                'header', 
                $this->dof->get_string('programmsbcs', $this->im_code())
            );
            $appointments = $this->dof->storage('appointments')->get_records(['eagreementid' => $this->_customdata->id]);
            foreach ( $appointments as $appointment )
            {
                $link = dof_html_writer::link(
                    $this->dof->url_im('employees','/edit_appointment.php?id='.$appointment->id), 
                    $this->dof->get_string('view_programmsbcs', $this->im_code(), $appointment->enumber)
                );
                $mform->addElement(
                    'html', 
                    dof_html_writer::div($link)
                );
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
        
        // Валидация назначения на должность
        if ( isset($data['appoint']) && ($data['appoint'] == 1) )
        {// Назначение сохраняется
            
            // проверим существование вакансии
            if ( ! $this->dof->storage('schpositions')->is_exists($data['schpositionid']) )
            {// вакансия не существует, сообщим об этом
                $errors['schpositionid'] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
            }
            
            // проверим табельного номера
            if ( ! $data['enumber'] )
            {// номер не введен
                $errors['enumber'] = $this->dof->modlib('ig')->igs('form_err_required');
            } elseif ( ((isset($this->appointment->enumber) AND $this->appointment->enumber <> $data['enumber']) 
                      OR empty($this->appointment->enumber)) AND
                      ! $this->dof->storage('appointments')->is_enumber_unique($data['enumber']) )
            {// номер введен, но не уникальный
                $errors['enumber'] = $this->dof->get_string('error_unique_enumber','employees');
            }
            
            // проверим правильность введения ставки
            if ( ! $data['worktime'] )
            {// ставка не указана
                $errors['worktime'] = $this->dof->modlib('ig')->igs('form_err_required');
            } elseif ( isset($this->appointment->worktime) )
            {// объект редактируется
                if  ( $this->appointment->worktime <> $data['worktime'] AND 
                      $data['worktime'] > $this->appointment->worktime + 
                      $this->dof->storage('appointments')->get_free_worktime($data['schpositionid']) )
                {// отредактировали ставку - проверим чтобы она не превышала допустимое значение
                    $errors['worktime'] = $this->dof->get_string('error_free_worktime','employees',
                                         $this->appointment->worktime + $this->dof->storage('appointments')->
                                         get_free_worktime($data['schpositionid']));
                }
            } elseif( $data['worktime'] > $this->dof->storage('appointments')->get_free_worktime($data['schpositionid']) )
            {// ставка указана, но больше допустимой
                $errors['worktime'] = $this->dof->get_string('error_free_worktime','employees',
                                     $this->dof->storage('appointments')->
                                     get_free_worktime($data['schpositionid']));
            }
            
            // проверка на лимит
            if ( ! $data['eagreementid']  )
            {
                if ( ! $this->dof->storage('config')->get_limitobject('eagreements', $data['departmentid'] ) )
                {
                    $errors['departmentid'] = $this->dof->get_string('limit_message','employees');
                } 
            } else 
            {// редактирование - переносить нельзя в переполненые
                $depid = $this->dof->storage('eagreements')->get_field($data['eagreementid'],'departmentid');
                if ( ! $this->dof->storage('config')->get_limitobject('eagreements',$data['departmentid'] ) AND $depid != $data['departmentid'] )
                {
                    $errors['departmentid'] = $this->dof->get_string('limit_message','employees');
                }           
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
        // Базовые данные для обработчика
        $personid = $this->persons['added']['personid'];
        
        // Формирование данных по договору
        $eagreement = new stdClass();
        if ( ! isset($this->eagreement->personid) || $this->eagreement->personid != $personid )
        {// Обновление персоны по договору
            $eagreement->personid = $personid;
            if ( ! $this->dof->storage('eagreements')->update($eagreement, $this->eagreementid) )
            {// Ошибка сохранения
                $this->errors[] = $this->dof->get_string(
                    'error_save',
                    $this->im_code(),
                    $this->dof->get_string('m_contract', $this->im_code())
                );
            }
        }
        
        // Назначение на должность
        if ( isset($formdata->appoint) )
        {// Сохранение назначения на должность
            $appoint = new stdClass();
            $appoint->eagreementid = $formdata->eagreementid;
            $appoint->schpositionid = $formdata->schpositionid; 
            $appoint->enumber = $formdata->enumber; 
            $appoint->worktime = $formdata->worktime; 
            $appoint->date = $formdata->date;
            
            // Установка подразделения
            if ( ! $appoint->departmentid = $this->dof->storage('eagreements')->get_field($appoint->eagreementid, 'departmentid') )
            {// Подразделение не получено
                    $this->errors[] = $this->dof->get_string('save_appointment_failure', $this->im_code());
            } else
            {// Сохранение назначения на должность
                if ( ! empty($this->_customdata->appointment->id) )
                {// Обновление назначения
                    if ( ! $this->dof->storage('appointments')->update($appoint, $this->_customdata->appointment->id) )
                    {// Ошибка сохранения
                        $this->errors[] = $this->dof->get_string('save_appointment_failure', $this->im_code());
                    }
                } else
                {// Сохранение назначения   
                    if ( ! $id = $this->dof->storage('appointments')->insert($appoint) )
                    {// Ошибка сохранения
                        $this->errors[] = $this->dof->get_string('save_appointment_failure',$this->im_code());
                    }
                }
            }
        }
    }
    
 

    /** 
     * Возвращает список вакансий для формы создания назначения на должность
     * 
     * @return array
     */
    private function get_list_schpositions()
    {
        $options = [0 => '--- '.$this->dof->modlib('ig')->igs('choose').' ---'];
        // получаем список всех вакансий из базы
        if ( $schpositions = $this->dof->storage('schpositions')->
                             get_records(array('status'=>array('plan','active'))) )
        {// если что-то нашли
            foreach ( $schpositions as $schposition )
            { 
                // найдем свободное время ставки
                $time = $this->dof->storage('appointments')->get_free_worktime($schposition->id);
                if ( $time == 0 )
                {// времени нет
                    if ( isset($this->appointment->schpositionid) 
                         AND $this->appointment->schpositionid <> $schposition->id 
                         OR empty($this->appointment->schpositionid) )
                    {// и вакансия не редактируется
                        continue;
                    }
                }
                // строка меню с указанием свободной ставки
                $position = $this->dof->storage('positions')->get_field($schposition->positionid,'name').
                            ' ('.$this->dof->storage('departments')->get_field($schposition->departmentid,'code').') ['.
                                $this->dof->storage('positions')->get_field($schposition->positionid,'code').
                            '] '.$this->dof->get_string('worktime_lt','employees').' '.$time.
                            ' '.$this->dof->get_string('from','employees').' '.round($schposition->worktime,2);
                $options[$schposition->id] = $position; 
            }
        }
        asort($options);
        return $options;
    }
    
    /** 
     * Получить список всех возможных форм обучения для элемента select
     * 
     * @return array
     */
    private function get_eduforms_list()
    {
        return $this->dof->storage('programmsbcs')->get_eduforms_list();
    }
    
    /** 
     * Получить список всех возможных типов обучения для элемента select
     * 
     * @return array
     */
    private function get_edutypes_list()
    {
        return $this->dof->storage('programmsbcs')->get_edutypes_list();
    }
}


/**
 * Класс увольнения сотрудников
 */
class dof_im_employees_appointment_discharge_form extends dof_modlib_widgets_form
{
/** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    /** Объявление класса формы
     */
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        $this->appointment = $this->dof->storage('appointments')->get($this->_customdata->id);
        $mform->addElement('header','formtitle', 
                    $this->dof->get_string('discharge_employees', $this->im_code()));
        // добавляем скрытый параметр - id записи
        $mform->addElement('hidden','id', $this->appointment->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // запоминаем ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // договора
        if ( $appointments = $this->dof->storage('appointments')->
                        get_records(array('eagreementid'=>$this->appointment->eagreementid,
                                        'status'=>array('plan','active'))) )
        {
            foreach ( $appointments as $appointment )
            {
                // создаем массив
                $group = array();
                
                // получим должность
                $positionid = $this->dof->storage('schpositions')->
                              get_field($this->appointment->schpositionid, 'positionid');
                $position = $this->dof->storage('positions')->get_field($positionid, 'name').' ['.
                                $this->dof->storage('positions')->get_field($positionid, 'code').']';
                $group[] =& $mform->createElement('static', 'schposition_'.$appointment->schpositionid, 
                            null, $position);
                //укажем ставку
                $group[] =& $mform->createElement('static', 'worktime_'.$appointment->worktime, null, 
                            $this->dof->get_string('worktime_lt', $this->im_code()).' '.$appointment->worktime);
                $group[] =& $mform->createElement('checkbox','discharge', null, $this->dof->get_string('release', $this->im_code()));              
                $grp     =& $mform->addGroup($group, 'group['.$appointment->id.']', 
                            $this->dof->get_string('position', $this->im_code()).':'); 
            }
            // кнопка освобождения - показывается только если его можно поменять
            $mform->addElement('submit', 'discharge', $this->dof->get_string('release_on_position', $this->im_code()));
        }
        if ( $this->dof->storage('eagreements')->get_field($this->appointment->eagreementid, 'status') != 'canceled' )
        {// если сотрудник еще не уволен
            // добавляем скрытый параметр - id записи
            $mform->addElement('hidden','eagreementid', $this->appointment->eagreementid);
            $mform->setType('eagreementid', PARAM_INT);
            $mform->addElement('checkbox','confirm_dismiss', null, $this->dof->get_string('confirm_dismiss', $this->im_code()));
            // кнопка увольнения - показывается только если его можно поменять
            $mform->addElement('submit', 'dismiss', $this->dof->get_string('dismiss', $this->im_code()));
        }
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Дополнительное определение класса. Используется для динамических форм.
     */
    function definition_after_data()
    {
        
    }
    
    /** Проверки данных формы
     */
    function validation($data, $files)
    {
        $error = array();
        if ( isset($data['group']) )
        {
            foreach ( $data['group'] as $id=>$value )
            {
                if ( $this->dof->storage('appointments')->
                     get_field($id,'eagreementid') != $data['eagreementid'] )
                {
                    $error['schposition_'.$id] = $this->dof->modlib('ig')->igs('form_err_is_exist_element');
                }
            }
        }
        return $error;
    }
    
}

/**
 * Класс назначения мандаты
 */
class dof_im_employees_change_role extends dof_modlib_widgets_form
{
    public $wid;

    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    /** Объявление класса формы
     */
    function definition()
    {
        $mform     = &$this->_form;
        $this->dof = $this->_customdata->dof;
        $this->wid = $this->_customdata->wid;
        $this->departmentid = $this->_customdata->departmentid;
        if ( $this->wid )
        {// мандата редактируется
            $formtitle = $this->dof->get_string('change_warrant_on_position',$this->im_code());
            $somevars = ['departmentid' => $this->departmentid, 'id' => $this->wid];
            $link_to_warrant = dof_html_writer::link(
                $this->dof->url_im('acl','/warrantacl.php', $somevars),
                $this->dof->get_string('edit_warrant', $this->im_code()),
                ['class' => 'btn button dof_button without_margin']
            );
        }else
        {// мандата создается
            $formtitle = $this->dof->get_string('give_warrant_on_position',$this->im_code());
            $link_to_warrant = '';
        }
        $mform->addElement('header','formtitle', $formtitle);
        if ( $this->wid )
        {// доверенность уже прикреплена к должности - то покажем ее статус
            $mform->addElement('static', 'status_text', $this->dof->modlib('ig')->igs('status').':');
            $mform->setType('status_text', PARAM_TEXT);
            if($this->dof->im('acl')->is_access('aclwarrants:view')) {
                $mform->addElement('static', 'link_to_warrant', '', $link_to_warrant);
                $mform->setType('link_to_warrant', PARAM_TEXT);
            }
        }
        
        // добавляем скрытый параметр - id записи
        $mform->addElement('hidden','wid', $this->wid);
        $mform->setType('wid', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // запоминаем ключ сессии
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // имя
        $mform->addElement('text', 'name', $this->dof->get_string('name',$this->im_code()).':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        // код
        $mform->addElement('text', 'code', $this->dof->get_string('code',$this->im_code()).':', 'size="20"');
        $mform->setType('code', PARAM_TEXT);
        // не должен быть русским
        $mform->addRule('code',  $this->dof->modlib('ig')->igs('form_err_alphanumeric'),'alphanumeric',null,'client');
        // пояснение
        $mform->addElement('textarea', 'description', $this->dof->get_string('description',$this->im_code()).':', 
                            array('style' => 'width:100%;max-width:200px;height:100px;'));
        $mform->setType('description', PARAM_TEXT);
        // родительская мандата
        $mform->addElement('select', 'parentid', 
                $this->dof->get_string('parrent_warrant', $this->im_code()).':', $this->get_list_aclwarrants());
        $mform->setType('parentid', PARAM_INT);
        
        $choices=array();
        $choices[0] = $this->dof->modlib('ig')->igs('forbid');
        $choices[1] = $this->dof->modlib('ig')->igs('resolve');
        $mform->addElement('select', 'isdelegatable', 
                $this->dof->get_string('noextend', $this->im_code()).':', $choices);
        $mform->setType('isdelegatable', PARAM_INT);
        // кнопка 
        if ( $this->wid )
        {// если мандата редактируется - добавляем кнопки изменить и удалить
            $group[] =& $mform->createElement('submit', 'change', $this->dof->modlib('ig')->igs('change'));
            $group[] =& $mform->createElement('submit', 'delete', $this->dof->modlib('ig')->igs('delete'));
            $grp     =& $mform->addGroup($group, 'group');
            
        }else
        {// добавляется - кнопка добавить
            $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('add'));
        }
        // уберем пробелы в форме
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить список родительских мандат
     */
    function get_list_aclwarrants()
    {
        // найдем неархивные мандаты, которые можно наследовать
        $real = $this->dof->workflow('aclwarrants')->get_meta_list('real');
        if ( ! $aclwarrants = $this->dof->storage('aclwarrants')
                ->get_records(array('status'=> array_keys($real))) )
        {// таких нет - вернем пустой элемент
            return $this->dof_get_select_values();
        }
        
        $rez = array();
        foreach ( $aclwarrants as $aclwarrant )
        {
            if ( $this->wid != $aclwarrant->id )
            {// если мандата не текущая - добавим еее в список
                // отыщем хозяина мандаты
                if ( ! empty($aclwarrant->linkptype) AND ! empty($aclwarrant->linkpcode) )
                {// хозяин плагин - выведем его имя
                    $note = $this->dof->get_string('title', $aclwarrant->linkpcode, null, $aclwarrant->linkptype);
                }else
                {// мандата ядра
                    $note = $this->dof->get_string('core',$this->im_code());
                }
                $rez[$aclwarrant->id] = $aclwarrant->name.'['.$aclwarrant->code.']('.$note.')';
            }
        }
        /*
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'aclwarrants', 'code'=>'use'));
        // для мандаты нет прав
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        */
        // сортируем и возвращаем
        asort($rez);
        return $this->dof_get_select_values() + $rez;
        
        
    }
    
    /** Дополнительное определение класса. Используется для динамических форм.
     */
    function definition_after_data()
    {
        $mform = &$this->_form;
        if ( $this->wid )
        {
            $wstatus = $this->dof->storage('aclwarrants')->get_field($this->wid, 'status');
            $mform->setDefault('status_text', $this->dof->workflow('aclwarrants')->get_name($wstatus));
        }
    }
    
    /** Проверки данных формы
     */
    function validation($data, $files)
    {
        
        $error = array();
        if ( empty($data['name']) )
        {// не указано имя
            $error['name'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        if ( empty($data['code']) )
        {// не указан код
            $error['code'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        if ( empty($data['parentid']) )
        {// не указан родитель
            $error['parentid'] = $this->dof->modlib('ig')->igs('form_err_required');
        }
        return $error;
    }

    /** Обработчик формы
     * @return unknown_type
     */
    function save_change_warrants()
    {
        if ( $this->is_submitted() AND $this->is_validated() AND $data = $this->get_data() )
        {// была нажата кнопка - получим данные
            
            if ( ! $warrant = $this->dof->storage('aclwarrants')->get($data->parentid) )
            {// родителя нет - вернем ошибку
                return '<p style=" color:red; " align="center"><b>'.
                       $this->dof->get_string('no_parent_warrant',$this->im_code()).'</b></p>';
            }
            
            // переназначим поля родителя
            unset($warrant->description);
            $warrant->code = $data->code;
            $warrant->name = $data->name;
            $warrant->parentid = $data->parentid;
            $warrant->linkptype = 'storage';
            $warrant->linkpcode = 'positions';
            $warrant->linktype = 'record';
            $warrant->linkid = $this->_customdata->id;
            $warrant->description = $data->description;
            $warrant->isdelegatable = $data->isdelegatable;
            $warrant->parenttype = 'ext';
            
            if ( $this->wid AND (! empty($data->group['delete']) OR ! empty($data->group['change'])) )
            {// есть запись - обновляем
                if ( $this->dof->storage('aclwarrants')->update($warrant,$this->wid) )
                {// апдейт прошел успешно
                    if ( isset($data->group['delete']) )
                    {// если удаляли - делаем редирект чтобы обнулилась форма
                        $opt = array();
                        $opt['changestatuswa'] = true; // надо сменить статус применениям
                        if ( $this->dof->workflow('aclwarrants')->change($warrant->id,'archive',$opt) )
                        {
                            redirect($this->dof->url_im('employees','/view_position.php?id='.
                                $this->_customdata->id.'&departmentid='.$data->departmentid));
                        }
                    }
                    $this->dof->storage('appointments')->add_warrentagents($this->_customdata->id,$warrant);
                    // вернем сообщение об успехе
                    return '<p style=" color:green; " align="center"><b>'.
                           $this->dof->modlib('ig')->igs('record_update_success').'</b></p>';
                }else
                {// ошибка
                    return '<p style=" color:red; " align="center"><b>'.
                           $this->dof->modlib('ig')->igs('record_update_failure',$this->wid).'</b></p>';
                }
                
            }elseif ( ! empty($data->save) );
            {// добавляем запись
                // СТАТУСЫ
                // ставим статус такой же как и должности 
                // но если вдруг должность активная, а выбран parentid неактивный(draft) 
                // то сообщить об этом и статус draft
                if ( $warrantid = $this->dof->storage('aclwarrants')->insert($warrant) )
                {// Если получилось, добавим применение мандат
                    $sms = '';
                    $parent = $this->dof->storage('aclwarrants')->get($data->parentid);
                    // тут у нас уже создалась должность в статусе драфт
                    // должность актив и parentid==актив -> переводим и созданнюу должность в актив
                    if (  $this->dof->storage('positions')->get_field($this->_customdata->id,'status') == 'active' 
                            AND  $parent->status == 'active' )
                    {// ставим статус active
                        $this->dof->workflow('aclwarrants')->change($warrantid,'active');               
                    }elseif(  $this->dof->storage('positions')->get_field($this->_customdata->id,'status') == 'active' 
                            AND  $parent->status == 'draft' )  
                    {// должность актив но parentid=draft - сообщим об этом
                        $sms = $this->dof->get_string('no_active_status', $this->im_code(), $parent->name.'['.$parent->code.']');
                    }
                                  
                    $warrant->id = $warrantid;
                  //  var_dump($warrant); echo "<br>";echo $this->_customdata->id."<br>";
                    $this->dof->storage('appointments')->add_warrentagents($this->_customdata->id,$warrant);
 
                    //и сообщим об удачном добавлении записи
                    $this->_customdata->wid = $warrantid;
                    return '<p style=" color:green; " align="center"><b>'.
                           $this->dof->modlib('ig')->igs('record_insert_success').'</b><br>'.$sms.'</p>';
                }else
                {// неуспешно - тоже сообщим
                    return '<p style=" color:red; " align="center"><b>'.
                           $this->dof->modlib('ig')->igs('record_insert_failure').'</b></p>';
                }
                
            }
            return ''; 
        }
        
    }
    
    /** Сделать всю форму неактивной
     * 
     * @return null
     */
    function disable_form()
    {
        $this->_form->hardFreeze();
    }
    
}

/** Класс формы для поиска периодов
 * 
 */
class dof_im_appointments_search_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $addvars;
    /** Получить код im-плагина с которым будет работать форма (откуда брать языковые
     * сироки и т. п.)
     * 
     * @return string - название im-плагина для работы
     */
    protected function im_code()
    {
        return 'employees';
    }
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('search'));
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // поле "табельный номер"
        $mform->addElement('text', 'enumber', $this->dof->get_string('enumber',$this->im_code()), 'size="20"');
        $mform->setType('enumber', PARAM_TEXT);
        // совместитель
        $select = array();
        $select['-1'] = '--- '.$this->dof->modlib('ig')->igs('no_specify').' ---';
        $select['0'] = $this->dof->modlib('ig')->igs('no');
        $select['1'] = $this->dof->modlib('ig')->igs('yes');
        $mform->addElement('select', 'combination', $this->dof->get_string('combination',$this->im_code()),$select);
        $mform->setType('combination', PARAM_INT);
        // поле "статус"
        $statuses    = array();
        $statuses[0] = '--- '.$this->dof->modlib('ig')->igs('select').' ---';
        $statuses    = array_merge($statuses, $this->dof->workflow('appointments')->get_list());
        $mform->addElement('select', 'status', $this->dof->modlib('ig')->igs('status').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->modlib('ig')->igs('find'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}
?>