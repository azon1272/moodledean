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
 * @todo добавить поле "длительность"
 */

// Подключаем библиотеки
require_once('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * Класс формы для редактирования учебных программ
 */
class dof_im_programms_edit_form extends dof_modlib_widgets_form
{
    private $programm;
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми

        $this->programm = $this->_customdata->programm;
        $this->dof      = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', null, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','programmid', $this->programm->id);
        $mform->setType('programmid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // создадим вспомогательгый элемент для хранения длительности обучения в секундах
        $mform->addElement('hidden', 'duration');
        $mform->setType('duration', PARAM_INT);
        // создадим вспомогательгый элемент для хранения длительности обучения в академических часах
        $mform->addElement('hidden', 'ahours');
        $mform->setType('ahours', PARAM_INT);
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->programm->id));
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // имя учебной программы
        $mform->addElement('text', 'name', $this->dof->get_string('name','programms').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name',$this->dof->get_string('programmname_required', 'programms'), 'required',null,'client');
        
        // код учебной программы
        $mform->addElement('text', 'code', $this->dof->get_string('code','programms').':', 'size="12"');
        $mform->setType('code', PARAM_TEXT);
        if ( isset($this->programm->id) AND $this->programm->id )
        {// если программа редактируется - то код считается обязательным
            $mform->addRule('code',$this->dof->get_string('code_required','programms'), 'required',null,'client');
            $mform->addRule('code',$this->dof->get_string('code_required','programms'), 'required',null,'server');
        }
        
        // структурное подразделение
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        $mform->addElement('select', 'department', 
                            $this->dof->get_string('department','programms').':', $departments);
        $mform->setType('department', PARAM_INT);
        $mform->setDefault('department', $this->programm->departmentid);
        
        // длительность учебного периода
        // создадим массив для группы элементов:
        $timegroup = array();
        // создаем массив для подписей "месяц", "год" и "день"
        $titles    = array();
        // @todo когда разберемся, как корректно конвертировать время 
        // из секунд и обратно в месяцы/года - сделать поля активными
        $titles[]  = $this->dof->get_string('academic_hours', 'programms').'<br/>';
        //$titles[]  = $this->dof->get_string('semesters', 'programms').'<br/><br/>';
        //$titles[]  = $this->dof->get_string('years', 'programms');
        //$titles[]  = $this->dof->get_string('mounts', 'programms');
        $titles[]   = $this->dof->get_string('days', 'programms');
        
        $timegroup[] = &$mform->createElement('text', 'duration_academic_hours', '', 'size="1"');
        //$timegroup[] = &$mform->createElement('text', 'duration_semesters', '', 'size="1" disabled');
        //$timegroup[] = &$mform->createElement('text', 'duration_years', '', 'size="1" disabled');
        //$timegroup[] = &$mform->createElement('text', 'duration_mounts', '', 'size="1" disabled');
        $timegroup[] = &$mform->createElement('text', 'duration_days', '', 'size="1"');
                
        // добавляем дополнительный static-элемент, чтобы вывести пояснения к окошкам
        $timegroup[] = &$mform->createElement('static', 'add_static');
        $mform->addElement('group', 'timegroup', $this->dof->get_string('duration_of_learning','programms').':',$timegroup, $titles);
        //$mform->setType('duration_years', PARAM_INT);
        //$mform->setType('duration_mounts', PARAM_INT);
        $mform->setType('timegroup[duration_days]', PARAM_INT);
        $mform->setType('timegroup[duration_academic_hours]', PARAM_INT);
        // количестово учебных периодов
        $mform->addElement('text', 'agenums', $this->dof->get_string('agenums','programms').':', 'size="2"');
        $mform->setType('agenums', PARAM_INT);
        // описание
        $mform->addElement('textarea', 'about', $this->dof->get_string('about','programms'), array('cols'=>60, 'rows'=>10));
        $mform->setType('about', PARAM_TEXT);
        // заметки для сотрудников
        $mform->addElement('textarea', 'notice', $this->dof->get_string('notes','programms'), array('cols'=>60, 'rows'=>10));
        $mform->setType('notice', PARAM_TEXT);
        
        // цена программы
        $mform->addElement('htmleditor', 'billingtext', $this->dof->get_string('billingtext', 'programms').": ", 
                array('width'=>'50%', 'height'=>'100px'));
        $mform->setType('billingtext', PARAM_RAW);
        if ($this->programm->id)
        {// редактируем - добавляем текущее значение
            $mform->setDefault('billingtext', trim($this->programm->billingtext));
        }
        // Плавающие учебные периоды
        $mform->addElement('checkbox', 'flowagenums', null, $this->dof->get_string('flowagenums', 'programms'));
        $mform->setType('flowagenums', PARAM_BOOL);
        
        // Уровень образования
        $edulevels = $this->dof->modlib('refbook')->get_edulevel();
        $mform->addElement('select', 'edulevel', $this->dof->get_string('edulevel','programms').':', $edulevels);
        
        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','programms'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $durationhours = $mform->getElementValue('ahours');
        $durationsecs  = $mform->getElementValue('duration');
        // вычисляем длительность в днях
        $durationdays = ceil($durationsecs/(3600*24));
        
        // устанавливаем длительность обучения в академических часах
        $mform->setDefault('timegroup[duration_academic_hours]', (int)$durationhours);
        
        // устанавливаем продолжительность обучения в днях
        $mform->setDefault('timegroup[duration_days]', (int)$durationdays);
        
    }
    
    function validation($data, $files)
    {
        $errors = [];
        if ( ! isset($data['department']) )
        {// Подразделение не выбрано, или указано значение вне доступных
            $errors['hidden'] = $this->dof->get_string('error_form_not_available_value_department', 'programms');
            $data['department'] = 0;
        }
        if ( trim($data['code']) AND 
             $this->dof->storage('programms')->is_exists(['code' => strtolower($data['code'])]) AND 
             ! trim($data['programmid']) 
           )
        {// Код новой учебной программы не уникален
            $errors['code'] = $this->dof->get_string('code_not_unique', 'programms');
        } elseif( trim($data['programmid']) )
        {// Проверка на уникальность кода редактируемой программы
            $programm = $this->dof->storage('programms')->get($data['programmid']);
            if ( ( trim($data['code']) <> $programm->code) AND 
                 $this->dof->storage('programms')->is_exists(['code'=>trim($data['code'])]) 
               )
            {// Измененный код не уникален
                $errors['code'] = $this->dof->get_string('code_not_unique', 'programms');
            }
        }

        if ( $data['agenums'] <= 0 )
        {// попытка установить отрицательное число или 0
        $errors['agenums'] = $this->dof->get_string('error_numages', 'programms');
        }
        
        if ( $data['programmid'] )
        {// если учебная программа редактируется,  
            $programm = $this->dof->storage('programms')->get($data['programmid']);
            if ( $programm->departmentid != $data['department'] )
            {// и меняется подразделение, то посмотрим
                // есть ли у пользователя такие права
                if ( ! $this->dof->storage('programms')->is_access('create', $programm->id) OR 
                     ! $this->dof->workflow('programms')->is_access('changestatus', $programm->departmentid) )
                {// нет прав создавать учебную программу в новом подразделении
                    // или удалять программу из старого
                    $errors['department'] = $this->dof->get_string('noright_remove', 'programms');
                }
            }
            
            $maxages = $this->dof->storage('programmitems')->get_maxagenum($data['programmid']);
            if ( $data['agenums'] < $maxages )
            {// попытка установить количество семестров для программы меньше, чем 
                // их описано в таблице дисциплин (programmitems)
                $errors['agenums'] = $this->dof->get_string('error_maxages', 'programms');
            }
            // лимит объектов
            $depid = $this->dof->storage('programms')->get_field($data['programmid'], 'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('programms',$data['department'] ) AND $depid != $data['department'] )
            {
                $errors['department'] = $this->dof->get_string('limit_message','programms');
            }             
        }else
        {// если программа создается, то проверим, можно ли создать ее в этом подразделении
            if ( ! $this->dof->storage('programms')->is_access('create', NULL, NULL, $data['department']) )
            {// прав нет - выведем ошибку
                $errors['department'] = $this->dof->get_string('noright_create', 'programms');
            }
            // лимит объектов
            if ( ! $this->dof->storage('config')->get_limitobject('programms',$data['department'] ) )
            {
                $errors['department'] = $this->dof->get_string('limit_message','programms');
            }            
        }
        
        // Вернуть массив ошибок
        return $errors;
    }
    
    
    /** Возвращает строку заголовка формы
     * @param int $programmid
     * @return string
     */
    private function get_form_title($programmid)
    {
        if ( ! $programmid )
        {//заголовок создания формы
            return $this->dof->get_string('newprogramm','programms');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editprogramm','programms');
        }
        
    }
    
    /**
     * Возвращает имя подразделения
     * @param $id
     * @return unknown_type
     */
    private function get_department_name($id)
    {
        return $this->dof->storage('departments')->get_field($id,'name');
    }
    
    /**
     * Возврашает название статуса
     * @return string
     */
    private function get_status_name($status)
    {
        return $this->dof->workflow('programms')->get_name($status);
    }
}

/** Класс формы для поиска учебной программы
 * 
 */
class dof_im_programms_search_form extends dof_modlib_widgets_form
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
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('search','programms'));
        // поле "название"
        $mform->addElement('text', 'name', $this->dof->get_string('name','programms').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        // поле "код"
        $mform->addElement('text', 'code', $this->dof->get_string('code','programms').':', 'size="20"');
        $mform->setType('code', PARAM_TEXT);
        // получаем список возможных статусов
        $statuses    = array();
        $statuses[0] = $this->dof->get_string('any', 'programms');
        $statuses    = array_merge($statuses, $this->dof->workflow('programms')->get_list());
        // поле "статус"
        $mform->addElement('select', 'status', $this->dof->get_string('status','programms').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','programms'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
    }
}

/** Класс, отвечающий за форму смену статуса учебной программы
 * 
 */
class dof_im_programms_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'programms';
    }
    
    protected function workflow_code()
    {
        return 'programms';
    }
}
?>