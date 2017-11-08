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

class dof_im_agroups_edit_form extends dof_modlib_widgets_form
{
    private $agroup;
    protected $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми

        $this->agroup = $this->_customdata->agroup;
        $this->dof    = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
       
        $mform->addElement('hidden','agroupid', $this->agroup->id);
        $mform->setType('agroupid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // поле "agenum" для установки значения по умолчанию
        $mform->addElement('hidden','agenum');
        $mform->setType('agenum', PARAM_INT);
        // поле "programmid" для установки значения по умолчанию через set_data
        $mform->addElement('hidden','programmid');
        $mform->setType('programmid', PARAM_INT);
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $mform->addElement('hidden','departmentid', $depid);
        $mform->setType('departmentid', PARAM_INT);
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->agroup->id));
        // имя класса
        $mform->addElement('text', 'name', $this->dof->get_string('name','agroups').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name',$this->dof->get_string('err_name_required', 'agroups'), 'required',null,'client');
        // код класса
        $mform->addElement('text', 'code', $this->dof->get_string('code','agroups').':', 'size="20"');
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code',$this->dof->get_string('err_code_required', 'agroups'), 'required',null,'client');
        // получим все возможные подразделения для поля "select"
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        if ( ! empty($this->agroup->departmentid))
        {
            $departments[$this->agroup->departmentid] = 
                $this->dof->storage('departments')->get_field($this->agroup->departmentid,'name').'['.
                $this->dof->storage('departments')->get_field($this->agroup->departmentid,'code').']';
        }
        // поле "подразделение"
        if ( $this->dof->storage('agroups')->is_access('edit:departmentid') )
        {// если подразделение можно редактировать - редактируем
            $mform->addElement('select', 'department', $this->dof->get_string('department','agroups').':', $departments);
        } else
        {// если нельзя - то сделаем поле выключенным
            $mform->addElement('select', 'department', $this->dof->get_string('department','agroups').':', $departments, 'disabled');
        }
        // устанавливаем целочисленный тип, поскольку будем передавать только id
        $mform->setType('department', PARAM_INT);
        
        // получим список программ для select-элемента "учебная программа"
        $programms = $this->get_list_programms();
        // добавим элементы "учебная программа" и "номер периода"
        // (см. справку по moodleform)
        $agenums = array();
        if ( $this->agroup->id <> 0 )
        {// если форма редактируется, то нельзя менять номер периода
            if ( ! $this->dof->storage('agroups')->is_access('edit:programmid') AND $this->agroup->status <> 'plan' )
            {// если нет права обходного редактирования и группа не в статусе формируется
                // закрываем редактирование
                $agenumdisabled = 'disabled';
            } else
            {// разрешаем редактировать
                $agenumdisabled = '';
            }
            $hiselect = &$mform->addElement('hierselect', 'progages', $this->dof->get_string('programm','agroups').':<br/>'.
                                        $this->dof->get_string('agenum','agroups').':', null, '<br>');
        } else
        {// форма создается - номер периода менять можно
            // поле "учебная программа"
            $hiselect = &$mform->addElement('hierselect', 'progages', $this->dof->get_string('programm','agroups').':<br/>'.
                                        $this->dof->get_string('agenum','agroups').':', null, '<br>');
            foreach ( $programms as $progid=>$programm )
            {
                $totalagenums = $this->dof->storage('programms')->get_field($progid, 'agenums');
                if ( ! $totalagenums )
                {// если периодов нет - то выведем 0. Это необходимо, поскольку связяно с багом hierselect
                    if ( $progid == 0 )
                    {// не указан период
                        $agenums[$progid][0] = $this->dof->get_string('no_select_periods','agroups');
                    } else 
                    {// без периодов(0 - параллелей)
                        $agenums[$progid][0] = $this->dof->get_string('no_periods','agroups');
                    } 
                } else
                {// если периоды есть
                    for ( $agenum=1; $agenum<=$totalagenums; $agenum++ )
                    {// выдадим полный список всех периодов для каждой учебной программы
                        $agenums[$progid][$agenum] = $agenum.' ';
                    }
                }
            }
            // поле "количество периодов"
            $hiselect->setOptions(array($programms, $agenums));
        }
        // Добавляем период
        $actualstatuses = $this->dof->workflow('ages')->get_meta_list('actual');
        $conds = array('status' => array_keys($actualstatuses));
        $ages = $this->dof->storage('ages')->get_records($conds, 'id ASC', 'id,name');
        $result = array();
        foreach ( $ages as $id => $age )
        {
            $result[$id] = $age->name;
        }
        $mform->addElement('select', 'ageid', $this->dof->get_string('age', 'programmsbcs').':', $result);
        // Установим номер текущего периода
        if ( $this->agroup->id <> 0 )
        {
            if ( $actual = $this->dof->storage('agrouphistory')->get_actual_learning_data($this->agroup->id) )
            {
                $mform->setDefault('ageid', $actual->ageid);
            }
        }
        
        // поправочный зарплатный коэффициент
        $mform->addElement('text', 'salfactor', $this->dof->get_string('salfactor','agroups').':', 'size="10"');
        $mform->setType('salfactor', PARAM_TEXT);
        $mform->setDefault('salfactor', '0.00');
        //метаконтракт
        $ajaxparams = $this->autocomplete_params('metacontracts','client',0);
        $mform->addElement('dof_autocomplete', 'metacontract', $this->dof->get_string('metacontract','sel'), null, 
                $ajaxparams);
        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','agroups'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');    
    }

    function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->agroup->id <> 0 )
        {// если форма редактируется,
            // уcтановим грамотные значение по умолчанию для учебной программы
            
            // узнаем id учебной программы
//            $progid = $mform->getElementValue('programmid');
            // получим поле "номер периода"
            $hierselect = &$mform->getElement('progages');
            $programms = $this->get_list_programms();
            // заполним его значениями для нужной учебной программы
            $agenums = array();
            foreach ( $programms as $id => $programm )
            {
                $totalagenums = $this->dof->storage('programms')->get_field($id, 'agenums');
                if ( ! $totalagenums )
                {
                    $agenums[$id][0] = 0 . ' ';
                }
                for ( $agenum = 1; $agenum <= $totalagenums; $agenum++ )
                {// выдадим полный список всех периодов для каждой учебной программы
                    $agenums[$id][$agenum] = $agenum.' ';
                }
            }
            $hierselect->setOptions(array($programms, $agenums));
            // устанавливаем значение по умолчанию для количества периодов
            $mform->setDefault('progages', array($this->agroup->programmid, $this->agroup->agenum));
        }
    }
    
    /** Проверка данных на стороне сервера
     * 
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        if ( ! trim($data['name']) )
        {// не указано название класса 
            $errors['name'] = $this->dof->get_string('err_name_required','agroups');
        }
        if ( ! trim($data['code']) )
        {// не указан код
            $errors['code'] = $this->dof->get_string('err_code_required','agroups');
        } else
        {// если код указан, то он должен быть уникальным
            if ( ! $this->agroup->id )
            {// при создании
                if ( $this->dof->storage('agroups')->is_exists(array('code'=>trim($data['code']))) )
                {// код не уникален - выведем ошибку
                    $errors['code'] = $this->dof->get_string('err_code_unique','agroups');
                }
                if ( ! $this->dof->storage('config')->get_limitobject('agroups',$data['department']) )
                {
                    $errors['department'] = $this->dof->get_string('limit_message','agroups');
                }
            } else
            {// при редактировании
                $oldcode = $this->dof->storage('agroups')->get_field($this->agroup->id, 'code');
                if ( trim($data['code']) != $oldcode )
                {// если код изменен, то проверим новый код на уникальность
                    if ( $this->dof->storage('agroups')->is_exists(array('code'=>trim($data['code']))) )
                    {// код не уникален - выведем ошибку
                        $errors['code'] = $this->dof->get_string('err_code_unique','agroups');
                    }
                }
                $depid = $this->dof->storage('agroups')->get_field($this->agroup->id,'departmentid');
                if ( ! $this->dof->storage('config')->get_limitobject('agroups',$data['department']) AND $depid != $data['department'] )
                {
                    $errors['department'] = $this->dof->get_string('limit_message','agroups');
                }                
                
            }
        }
        // проверим существование программы и подразделения
        if ( ! $this->dof->storage('departments')->is_exists($data['department']) )
        {// учебное подразделение не существует
            $errors['department'] = $this->dof->get_string('err_dept_notexists','programmitems');
        }
        if ( ! $this->dof->storage('programms')->is_exists($data['progages'][0]) )
        {// учебная программа не существует
            if ( $data['agroupid'] != 0 )
            {// id группы нету - одна программы
                $errors['progages[0]'] = $this->dof->get_string('err_prog_notexists','programmitems');
            } else
            {// есть - использовался hierselect
                $errors['progages'] = $this->dof->get_string('err_prog_notexists','programmitems');
            }
        }
        
        //проверка по метаконтрактам
        if ( isset($data['metacontract']) )
        {
            $value = $this->dof->modlib('widgets')->get_extvalues_autocomplete('metacontract',$data['metacontract']);
            switch ($value['do'])
            {
                case "create"://запись создается
                    if ( $this->dof->storage('metacontracts')->is_exists(array('num'=>$value['name'])) )
                    {// такой метаконтракт уже существует и его нельзя использовать
                        $errors['metacontract'] = $this->dof->get_string('error_use_exists_metacontract','sel');
                    }
                break;
                case "rename":// запись переименовывается
                    if ( $this->dof->storage('metacontracts')->is_exists(array('num'=>$value['name'])) )
                    {// переименовать в уже существующий с таким названием тоже нельзя
                        $errors['metacontract'] = $this->dof->get_string('error_use_exists_metacontract','sel');
                    }
                case "choose":// запись выбрана
                    if ( !$this->dof->storage('metacontracts')->is_exists($value['id']) )
                    {// метаконтракта нет - пичалька
                        $errors['metacontract'] = $this->dof->get_string('metacontract_no_exist','sel',$value['id']);
                    } else if ( !$this->dof->storage('metacontracts')->is_access('use',$value['id']) )
                    {// прав нет - пичалька
                        $errors['metacontract'] = $this->dof->get_string('error_use_metacontract','sel',$value['id']);
                    }
               break;
            }
        }

        return $errors;
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
    protected function autocomplete_params($type, $side, $contractid=0)
    {
        $options = array();
        $options['plugintype']   = "storage";
     
        $options['sesskey']      = sesskey();
        $options['type']         = 'autocomplete';
        
        if ( $type == 'metacontracts' )
        {
            $options['plugincode']   = "metacontracts";
            $options['querytype']  = "metacontracts_list";
            //если форма редактируется-ставим значение по умолчанию
            $options['extoptions'] = new stdClass;
            $options['extoptions']->create = true;
            if ($this->agroup->id <> 0)
            {
                $options['extoptions']->empty = true;
                $agroup = $this->dof->storage('agroups')->get($this->agroup->id, 'metacontractid');
                $metacontractid = $agroup->metacontractid;
                if (!empty($metacontractid))
                {
                    $metacontract = $this->dof->storage('metacontracts')->get($metacontractid,'id,num');
                    $options['default'] = array($metacontractid => $metacontract->num.' ['.$metacontractid.']');
                    //$options['extoptions']->rename = $metacontract->id;
                }
                
            }    
        }
        return $options;
    }

    /**
     * Возвращает строку заголовка формы
     * @param int $agroupid
     * @return string
     */
    private function get_form_title($agroupid)
    {
        if ( ! $agroupid )
        {//заголовок создания формы
            return $this->dof->get_string('newagroup','agroups');
        } else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editagroup','agroups');
        }
    }
    
    /** Получить список учебных программ, для элемента select
     * @return array массив учебных программ в формате 'id' => 'Название учебной программы'
     */
    private function get_list_programms()
    {
        // добавляем нулевой элемент со словом "выбрать"
        $rez = array();
        // извлекаем все программы, сортируя их в алфавитном порядке
    	$programms = $this->dof->storage('programms')->get_records(array('status'=>'available'));
    	if ( ! is_array($programms) )
        {//получили не массив - это ошибка';
            return array(0 => '--- '.$this->dof->get_string('to_select','agroups').' ---');
        }
        foreach ( $programms as $id=>$programm )
        {// забиваем массив данными
            $rez[$programm->id]  = $programm->name.' ['.$programm->code.']';
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        asort($rez);
        return array(0 => '--- '.$this->dof->get_string('to_select','agroups').' ---') + $rez;
    }
}

/** Класс формы для поиска класса
 * 
 */
class dof_im_agroups_search_form extends dof_modlib_widgets_form
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
        $mform->addElement('header','formtitle', $this->dof->get_string('search','agroups'));
        // поле "название или код"
        $mform->addElement('text', 'nameorcode', $this->dof->get_string('nameorcode','agroups').':', 'size="20"');
        $mform->setType('nameorcode', PARAM_TEXT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // поле "название или код"
        // @todo удалить неиспользуемый код
        /* Это поле больше не нужно
        $mform->addElement('select', 'departmentid', $this->dof->get_string('department','agroups').':', $this->get_departments_list());
        $mform->setType('departmentid', PARAM_INT);
        */
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');        
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','agroups'));
    }
    
}

/** Класс, отвечающий за форму смену статуса группы
 * 
 */
class dof_im_agroups_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'agroups';
    }
    
    protected function workflow_code()
    {
        return 'agroups';
    }
}

/** 
 * Класс формы для массовой рассылки сообщений
 *
 */
class dof_im_agroups_groupmessage_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $agroupid;
    protected $depid;
    
    /**
     * Формируем поля формы
     */
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->agroupid = $this->_customdata->agroupid;
        $this->depid = $this->_customdata->depid;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        //создаем заголовок формы
        $displayoptions = array('rows'=>'4', 'cols'=>'', 'class'=>'messagesendbox');
        $mform->addElement('header','formtitle', $this->dof->get_string('group_message', 'agroups'));
        // поле "название или код"
        $mform->addElement('textarea', 'message', $this->dof->get_string('message_text','agroups').':', $displayoptions);
        $mform->setType('message', PARAM_TEXT);
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');        
        // Отправить сообщение
        $this->add_action_buttons(false, $this->dof->get_string('send_message','agroups'));
    }
    
    /**
     * Обработчик формы
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $this->is_validated() AND $formdata = $this->get_data() )
        {// Получили данные формы;
            // Получаем подписки студентов
            $sbc = $this->dof->im('agroups')->get_students_sbc($this->agroupid);
            // Результат выполнения
            $result = true;
            foreach ( $sbc as $item )
            {
                // Результат выполнения
                $result = ( 
                        $result && 
                        $this->dof->send_event(
                                'im', 
                                'agroups', 
                                'send_message', 
                                $item->studentid, 
                                array( 'message' => $formdata->message)
                        ) 
                );
            }
            // Обработаем результат
            $addvars = array(
                    'departmentid' => $this->depid,
                    'agroupid' => $this->agroupid
            );
            if ( $result )
            {// Успех
                $addvars['success'] = 1;
                redirect($this->dof->url_im('agroups', '/message.php', $addvars));
            } else 
            {
                $addvars['error'] = 1;
                redirect($this->dof->url_im('agroups', '/message.php', $addvars));
            }
        }
    }
}
?>