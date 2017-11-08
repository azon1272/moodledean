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

/** Класс для редактирования учебного предмета
 * @todo разбить элементы на группы
 * @todo сделать убирающиеся необязательные поля  
 */
class dof_im_programmitems_edit_form extends dof_modlib_widgets_form
{
    private $pitem;
    private $meta;
    /**
     * @var dof_control
     */
    public $dof;
    
    function definition()
    {// делаем глобальные переменные видимыми
        $this->pitem = $this->_customdata->pitem;
        $this->dof = $this->_customdata->dof;
        
        if ( isset($this->pitem->metaprogrammitemid) )
        {
            if ( $this->pitem->metaprogrammitemid == '0' )
            {
                $this->meta = 1;
            }
            else
            {
                $this->meta = 0;
            }   
        }
        else 
        {       
            if ( isset($this->pitem->meta) ) 
            {
                $this->meta = $this->pitem->meta;
            }
        }
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;    
        
        $mform->addElement('hidden','meta', $this->meta);
        $mform->setType('meta', PARAM_INT);
        $mform->addElement('hidden','pitemid', $this->pitem->id);
        $mform->setType('pitemid', PARAM_INT);
        $mform->addElement('hidden','sesskey', $this->_customdata->pitem->sesskey);
        $mform->setType('sesskey', PARAM_TEXT);
        // id программы. Используется как промежуточное поле для установки значений по умолчанию
        $mform->addElement('hidden','programmid', 0);
        $mform->setType('programmid', PARAM_INT);
        // номер периода. Используется как промежуточное поле для установки значений по умолчанию
        $mform->addElement('hidden','agenum', 0);
        $mform->setType('agenum', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->pitem->id));
        // название предмета
        $mform->addElement('text', 'name', $this->dof->get_string('name','programmitems').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name',$this->dof->get_string('err_name_required', 'programmitems'), 'required',null,'client');
        
        // название предмета в стандарте
        $mform->addElement('text', 'sname', $this->dof->get_string('sname','programmitems').':', 'size="20"');
        $mform->setType('sname', PARAM_TEXT);
        //$mform->addRule('sname',$this->dof->get_string('err_required', 'programmitems'), 'required',null,'client');
        
        // код предмета
        $mform->addElement('text', 'code', $this->dof->get_string('code','programmitems').':', 'size="20"');
        $mform->setType('code', PARAM_TEXT);
        if ( isset($this->pitem->id) AND $this->pitem->id )
        {// если предмет редактируется - то код считается обязательным
            $mform->addRule('code',$this->dof->get_string('err_required', 'programmitems'), 'required',null,'client');
            $mform->addRule('code',$this->dof->get_string('err_required', 'programmitems'), 'required',null,'server');
        }
        
        
        // код предмета в стандарте
        $mform->addElement('text', 'scode', $this->dof->get_string('scode','programmitems').':', 'size="20"');
        $mform->setType('scode', PARAM_TEXT);
        
        // сначала получим список всех подразделений
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        
        // и все учебные программы
        $programms   = $this->get_list_programms();
        $agenums     = array();
        
        if ( $this->pitem->id <> 0 AND ! $this->dof->is_access('datamanage') )
        {// предмет редактируется, подразделение и программу нельзя менять
            // поле "подразделение" (заблокировано)
            $mform->addElement('select', 'departmentid', $this->dof->get_string('department','programmitems').':', $departments/*, 'disabled'*/);
            // поле "программа" (заблокировано)
            // (значение по умолчанию устанавливаются в definition_after_data)
            $mform->addElement('select', 'progages[0]', $this->dof->get_string('program','programmitems').':', $programms, 'disabled');
            // поле "количество периодов" доступно
            // (варианты значений и значение по умолчанию устанавливаются в definition_after_data)
            $mform->addElement('select', 'progages[1]', $this->dof->get_string('agenums','programmitems').':', $agenums);
        }else
        {// предмет создается
            $mform->addElement('select', 'departmentid', $this->dof->get_string('department','programmitems'), $departments);
            $mform->setDefault('departmentid', optional_param('departmentid',0 , PARAM_INT));
            // поле "учебная программа"
            if ( $this->meta != 1 )
            {
                $hiselect = &$mform->addElement('hierselect', 'progages', $this->dof->get_string('program','programmitems').':<br/><br/>'.
                                                                $this->dof->get_string('agenums','programmitems').':', null, '<br>');
            }
            foreach ( $programms as $progid=>$programm )
            {
                $totalagenums = $this->dof->storage('programms')->get_field($progid, 'agenums');
                if ( ! $totalagenums )
                {// если периодов нет - то выведем 0. Это необходимо, поскольку связянно с багом hierselect
                    $agenums[$progid][0] = $this->dof->get_string('no_periods','programmitems');
                }else
                {// если периоды есть
                    for ( $agenum=0; $agenum<=$totalagenums; $agenum++ )
                    {// выдадим полный список всех периодов для каждой учебной программы
                        $agenums[$progid][$agenum] = $agenum.' ';
                    }
                }
            }
            // поле "количество периодов"
            if ( $this->meta != 1 )
            {
                $hiselect->setOptions(array($programms, $agenums));
            }
        }
        
        // тип предмета
        $types = $this->dof->storage('programmitems')->get_types();
        $mform->addElement('select', 'type', $this->dof->get_string('type','programmitems').':', $types);
        $mform->setType('type', PARAM_ALPHANUM);
        
        // обязателен ли предмет
        $mform->addElement('selectyesno', 'required', $this->dof->get_string('required','programmitems'));
        // установить значение по умолчанию "да"
        $mform->setDefault('required', 1);
        $mform->setType('required', PARAM_INT);
        
        // максимальное количество кредитов (очков) за дисциплину
        $mform->addElement('text', 'maxcredit', $this->dof->get_string('maxcredit','programmitems').':', 'size="4"');
        $mform->addRule('maxcredit',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('maxcredit', PARAM_INT);
        
        // количество недель
        $mform->addElement('text', 'eduweeks', $this->dof->get_string('eduweeks','programmitems').':', 'size="4"');
        $mform->addRule('eduweeks',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('eduweeks', PARAM_INT);
        
        // максимальная длительность обучения
        $mform->addElement('text', 'maxduration', $this->dof->get_string('maxduration','programmitems').' '.
                            '('.$this->dof->get_string('in_days','programmitems').'):', 'size="4"');
        $mform->addRule('maxduration',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('maxduration', PARAM_INT);
        
        // количество часов на дисциплину - всего
        $mform->addElement('text', 'hours', $this->dof->get_string('hours_all','programmitems').':', 'size="4"');
        $mform->addRule('hours',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hours', PARAM_INT);

        // Автоматически расчитывать количество "Часов всего"
        $mform->addElement('checkbox', 'autohours', '', $this->dof->get_string('autohours', 'programmitems'));
        $mform->disabledIf('hours', 'autohours', 'checked');
        
        // количество часов теории
        $mform->addElement('text', 'hourstheory', $this->dof->get_string('hours_theory','programmitems').':', 'size="4"');
        $mform->addRule('hourstheory',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hourstheory', PARAM_INT);
        
        // количество часов практики
        $mform->addElement('text', 'hourspractice', $this->dof->get_string('hours_practice','programmitems').':', 'size="4"');
        $mform->addRule('hourspractice',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hourspractice', PARAM_INT);
        
        // количество часов в неделю
        $mform->addElement('text', 'hoursweek', $this->dof->get_string('hours_week','programmitems').':', 'size="4"');
        $mform->addRule('hoursweek',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hoursweek', PARAM_INT);
        
        // количество часов лабораторных (Лаб)
        $mform->addElement('text', 'hourslab', $this->dof->get_string('hours_lab','programmitems').':', 'size="4"');
        $mform->addRule('hourslab',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hourslab', PARAM_INT);
        
        // количество часов самостоятельной работы слушателя (СРС)
        $mform->addElement('text', 'hoursind', $this->dof->get_string('hours_ind','programmitems').':', 'size="4"');
        $mform->addRule('hoursind',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hoursind', PARAM_INT);
        
        // количество часов контроля (Контроль)
        $mform->addElement('text', 'hourscontrol', $this->dof->get_string('hours_control','programmitems').':', 'size="4"');
        $mform->addRule('hourscontrol',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('hourscontrol', PARAM_INT);        

        // уровень компоненты
        $complevels = $this->dof->modlib('refbook')->get_st_component_types();
        $mform->addElement('select', 'instrlevelid', $this->dof->get_string('level','programmitems').':', $complevels);
        $mform->setType('instrlevelid', PARAM_INT);
        
        // описание
        $mform->addElement('textarea', 'about', $this->dof->get_string('about','programmitems'), array('cols'=>60, 'rows'=>10));
        $mform->setType('about', PARAM_TEXT);
        // заметки для сотрудников
        $mform->addElement('textarea', 'notice', $this->dof->get_string('notice','programmitems'), array('cols'=>60, 'rows'=>10));
        $mform->setType('notice', PARAM_TEXT);
        
        // вид итогового контроля
        $mform->addElement('select', 'controltypeid', $this->dof->get_string('controltype','programmitems').':', 
                           $this->dof->modlib('refbook')->get_st_total_control());
        //$mform->setType('controltypeid', PARAM_TEXT);
        
        // шкала оценок
        $mform->addElement('text', 'scale', $this->dof->get_string('scale','programmitems').':', 'size="60"');
        $mform->setType('scale', PARAM_TEXT);
        $mform->addRule('scale',$this->dof->get_string('err_scale', 'programmitems'), 'required',null,'client');
        
        // минимальная оценка для окончания курса
        $mform->addElement('text', 'mingrade', $this->dof->get_string('mingrade','programmitems').':', 'size="4"');
        //$mform->addRule('mingrade','err_numeric', 'numeric',null,'client');
        $mform->setType('mingrade', PARAM_TEXT);
        $mform->addRule('mingrade',$this->dof->get_string('err_mingrade_is_not_valid', 'programmitems'), 'required',null,'client');
        
        // степень однородности
        // @todo приказано было убрать
        //$coursecls = $this->dof->modlib('refbook')->get_st_coursecls();
        //$mform->addElement('select', 'courseclsid', $this->dof->get_string('coursecls','programmitems').':', $coursecls);
        //$mform->setType('controltypeid', PARAM_INT);
        
        // курс в moodle
        // @todo не показывать select, если есть активные предмето-классы.
        // Вместо него показывать статическое поле с текущим курсом и список потоков которые нужно завершить
        // лишний раз не будем запрашивать весь список курсов, и освободим ресурсы сервера
        if ( $this->dof->storage('programmitems')->is_access('edit:mdlcourse', $this->pitem->id) )
        {// если пользователь имеет право редактировать id курса Moodle - покажем поле
            if ( isset($this->pitem->status) AND $this->pitem->status != 'suspend' )
            {// есть статус и он не приостановленный - запрещаем редактирование
                $mform->addElement('select', 'mdlcourse', $this->dof->get_string('mdlcourse','programmitems').':', 
                                                      $this->get_list_mdlcourse(),'disabled');
            }else
            {// можно отредактировать
                $mform->addElement('select', 'mdlcourse', $this->dof->get_string('mdlcourse','programmitems').':', 
                                                      $this->get_list_mdlcourse());
            }
            $mform->setType('mdlcourse', PARAM_INT);
        }
        
        // уровень оценки
        $gradelevels = $this->dof->storage('programmitems')->get_gradelevels();
        $mform->addElement('select', 'gradelevel', $this->dof->get_string('gradelevel','programmitems').':', $gradelevels);
        $mform->setType('gradelevel', PARAM_ALPHANUM);
        
        // разрешена ли синхронизация оценок
        $mform->addElement('selectyesno', 'gradesyncenabled', $this->dof->get_string('gradesyncenabled','programmitems'));
        // установить значение по умолчанию "да"
        $mform->setDefault('gradesyncenabled', 0);
        $mform->setType('gradesyncenabled', PARAM_INT);
        
        // включать в ведомость пользователей без оценки или не подписанных на курс
        $mform->addElement('selectyesno', 'incjournwithoutgrade', $this->dof->get_string('incjournwithoutgrade','programmitems'));
        // установить значение по умолчанию "нет"
        $mform->setDefault('incjournwithoutgrade', 0);
        $mform->setType('incjournwithoutgrade', PARAM_INT);
        
        // включать в ведомость пользователей с неудовлетворительной оценкой
        $mform->addElement('selectyesno', 'incjournwithunsatisfgrade', $this->dof->get_string('incjournwithunsatisfgrade','programmitems'));
        // установить значение по умолчанию "да"
        $mform->setDefault('incjournwithunsatisfgrade', 1);
        $mform->setType('incjournwithunsatisfgrade', PARAM_INT);
        
        // использовать другой grade_items
        $mform->addElement('text', 'altgradeitem', $this->dof->get_string('altgradeitem','programmitems').':', 'size="4"');
        $mform->addRule('altgradeitem',$this->dof->get_string('err_numeric', 'programmitems'), 'numeric',null,'client');
        $mform->setType('altgradeitem', PARAM_INT);
        
        if ( ! empty($this->pitem->metaprogrammitemid) )
        {// только для дисциплины привязанной к метадисциплине
            $mform->addElement('radio', 'metasyncon', $this->dof->get_string('sync_with_metapitems','programmitems').':', $this->dof->get_string('sync_on','programmitems'), 1);
            $mform->addElement('radio', 'metasyncon', null, $this->dof->get_string('sync_off','programmitems'), 0);
            $mform->disabledIf('altgradeitem', 'metasyncon','eq','1');
            $mform->disabledIf('incjournwithunsatisfgrade', 'metasyncon','eq','1');
            $mform->disabledIf('incjournwithoutgrade', 'metasyncon','eq','1');
            $mform->disabledIf('gradesyncenabled', 'metasyncon','eq','1');
            $mform->disabledIf('gradelevel', 'metasyncon','eq','1');
            if ( $this->dof->storage('programmitems')->is_access('edit:mdlcourse', $this->pitem->id) )
            {// если пользователь имеет право редактировать id курса Moodle - покажем поле
                $mform->disabledIf('mdlcourse', 'metasyncon','eq','1');
            }
            $mform->disabledIf('controltypeid', 'metasyncon','eq','1');
            $mform->disabledIf('instrlevelid', 'metasyncon','eq','1');
            $mform->disabledIf('hoursweek', 'metasyncon','eq','1');
            $mform->disabledIf('hourspractice', 'metasyncon','eq','1');
            $mform->disabledIf('hourstheory', 'metasyncon','eq','1');
            $mform->disabledIf('hours', 'metasyncon','eq','1');
            $mform->disabledIf('maxduration', 'metasyncon','eq','1');
            $mform->disabledIf('eduweeks', 'metasyncon','eq','1');
            $mform->disabledIf('maxcredit', 'metasyncon','eq','1');
            $mform->disabledIf('required', 'metasyncon','eq','1');
            $mform->disabledIf('type', 'metasyncon','eq','1');
            $mform->disabledIf('scode', 'metasyncon','eq','1');
            $mform->disabledIf('sname', 'metasyncon','eq','1');
        }else
        {
            $mform->addElement('hidden','metasyncon', 0);
        }
        
        $mform->setType('metasyncon', PARAM_BOOL);
        // поправочный зарплатный коэффициент
        $mform->addElement('text', 'salfactor', $this->dof->get_string('salfactor','programmitems').':', 'size="10"');
        $mform->setType('salfactor', PARAM_TEXT);
        $mform->setDefault('salfactor', '0.00');
        // цена дисциплины
        $mform->addElement('htmleditor', 'billingtext', $this->dof->get_string('billingtext', 'programmitems').": ",
                array('width'=>'50%', 'height'=>'100px'));
        $mform->setType('billingtext', PARAM_RAW);
        if ($this->pitem->id)
        {
            $mform->setDefault('billingtext', trim($this->pitem->billingtext));
        }
        
        // $mform->setDefault('required', 1);
        $mform->setType('required', PARAM_INT);

        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','programmitems'));
        // убираем концевые пробелы для всех введенных полей
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // получим id программы, которой принадлежит редактируемый или создаваемый предмет
        $progid = $mform->getElementValue('programmid');
        // получим номер периода, пришедший из базы или из параметров GET
        $baseagenum = $mform->getElementValue('agenum');
        if ($this->pitem->id <> 0 AND ! $this->dof->is_access('datamanage') )
        {// если предмет редактируется, или создается с предустановленными параметрами
            // сначала уcтановим грамотные значения по умолчанию
            $mform->setDefault('progages[0]', $progid);
            // получим поле "количество элементов"
            $agenums = &$mform->getElement('progages[1]');
            // заполним его значениями для нужной учебной программы
            $totalagenums = $this->dof->storage('programms')->get_field($progid, 'agenums');
            if ( ! $totalagenums )
            {// если периодов нет - то выведем соответствующее сообщение
                $agenums->addOption(0, $this->dof->get_string('no_periods','programmitems'));
            }else
            {// если периоды есть
                for ( $agenum=0; $agenum<=$totalagenums; $agenum++ )
                {// выдадим полный список всех периодов для каждой учебной программы
                    $agenums->addOption($agenum, $agenum);
                }
            }
            // устанавливаем значение по умолчанию для количества периодов
            $mform->setDefault('progages[1]', $baseagenum);
        }elseif ( isset($this->pitem) )
        {// если предмет создается с предустановленными параметрами
            $mform->setDefault('progages', array($progid, $baseagenum));
        }
        // устанавливваем количество дней, переводя продолжительность из секунд
        $maxdurationsec = $mform->getElementValue('maxduration');
        $mform->setDefault('maxduration', ceil($maxdurationsec / (3600 * 24) ));

    }
    
    /** Проверки формы на стороне сервера
     * 
     * @return 
     * @param array $data - данные из формы
     */
    function validation($data,$files)
    {
        $errors = array();
        if ( ! trim($data['name']) )
        {// если не указано название программы
            $errors['name'] = $this->dof->get_string('err_required','programmitems');
        }
        if ( trim($data['code']) )
        {// если код указан, то он должен быть уникальным
            if ( ! $this->pitem->id )
            {// при создании
                if ( $this->dof->storage('programmitems')->is_exists(array('code'=>trim($data['code']))) )
                {// код не уникален - выведем ошибку
                    $errors['code'] = $this->dof->get_string('err_unique','programmitems');
                }
            }else
            {// при редактировании
                $oldcode = $this->dof->storage('programmitems')->get_field($this->pitem->id, 'code');
                if ( trim($data['code']) != $oldcode )
                {// если код изменен, то проверим новый код на уникальность
                    if ( $this->dof->storage('programmitems')->is_exists(array('code'=>trim($data['code']))) )
                    {// код не уникален - выведем ошибку
                        $errors['code'] = $this->dof->get_string('err_unique','programmitems');
                    }
                }
            }
        }
        if ( ! $this->dof->storage('departments')->is_exists($data['departmentid']) )
        {// учебное подразделение не существует
            $errors['departmentid'] = $this->dof->get_string('err_dept_notexists','programmitems');
        }

        if($this->meta !== 1)
        {
            if ( ! $this->dof->storage('programms')->is_exists($data['progages'][0]) )
            {// учебная программа не существует
                if ( $data['pitemid'] <> 0 )
                {// id группы нету - одна программы
                    $errors['progages[0]'] = $this->dof->get_string('err_prog_notexists','programmitems');
                }else
                {// есть - использовался hierselect
                    $errors['progages'] = $this->dof->get_string('err_prog_notexists','programmitems');
                }
            }else
            {// если учебная программа существует, - то введенный номер периода должен быть правильным
                $totalagenums = $this->dof->storage('programms')->get_field($data['progages'][0], 'agenums');
                if ( $data['progages'][1] > $totalagenums OR $data['progages'][1] < 0 )
                {// номер периода указан неправильно - сообщим об этом
    	            if ( $data['pitemid'] <> 0 )
    	            {// id группы нету - одна программы
    	                $errors['progages[1]'] = $this->dof->get_string('err_incorrect_agenum','programmitems');;
    	            }else
    	            {// есть - использовался hierselect
    	                $errors['progages'] = $this->dof->get_string('err_prog_notexists','programmitems');
    	            } 
                }
            }
        }
        if ( ! empty($data['mdlcourse']) AND (!$this->dof->modlib('ama')->course(false)->is_course($data['mdlcourse'])))
    	{// если такой курс не существует или это главная страница
    		$errors['mdlcourse'] =  $this->dof->get_string('err_course_Moodle','programmitems');
    	}elseif ( $activecstreams = $this->get_active_cstreams($data['pitemid']) )
        {// курс можно менять только в том случае, когда нет активных учебных процессов 
            // для этого предмета - дадим ссылки на потоки которые надо завершить
            
            // но сначала убедимся в том, что курс действительно изменился
            $oldmdlcourse = (int)$this->dof->storage('programmitems')->get_field($this->pitem->id, 'mdlcourse');
            if ( $oldmdlcourse AND $oldmdlcourse != $data['mdlcourse'] )
            {
                $errors['mdlcourse'] = $this->dof->get_string('err_active_cstreams_exist','programmitems');
                $errors['mdlcourse'] .= '<br/>'.$activecstreams;
            }
        }
        if ( ! isset($data['scale']) )
	    {// если шкала не указана
	    	$errors['scale'] =  $this->dof->get_string('err_scale','programmitems');
	    }else
        {// шкала указана, проверим ее
            $result = $this->scale_is_valid($data['scale']);
            if ( ! empty($result) )
            {// если шкала указано неверно - то запишем возникшие ошибки в общий массив
                $errors = $errors + $result;
            }
            if ( isset($data['mingrade']) AND trim($data['mingrade']) )
            {// если у нас есть минимальная оценка, то проверим, приенадлежит ли она шкале
                if ( ! $this->dof->storage('programmitems')->is_grade_valid(null, $data['mingrade'], $data['scale']) )
                {
                    $errors['mingrade'] = $this->dof->get_string('err_mingrade_is_not_valid','programmitems');
                }
            }
        }
        // проверим, что все значения, которые должны быть положительными, 
        // действительно являются таковыми
        // поскольку проверка однотипная - запишем названия полей в массив
        // и проверим их в цикле
        $checkfields = array('maxcredit', 'eduweeks', 'maxduration', 'hours',
                             'hourstheory', 'hourspractice', 'hourslab', 'hoursind',
                             'hourscontrol');
        foreach ( $checkfields as $checkfield )
        {// проверяем все поля
            if ( !empty($data[$checkfield]) AND $data[$checkfield] < 0 )
            {// если введено отрицательное значение - выведем ошибку
                $errors[$checkfield] = $this->dof->get_string('err_only_positive','programmitems');
            }
        }
        // лимит объектов
        if ( ! $data['pitemid'] )
        {
            if ( ! $this->dof->storage('config')->get_limitobject('programmitems',$data['departmentid'] ) )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','programmitems');
            }
        }else 
        {// редактирование - нельзя перенсти в переполненые
            $depid = $this->dof->storage('programmitems')->get_field($data['pitemid'], 'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('programmitems',$data['departmentid']) AND $depid != $data['departmentid'] )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','programmitems');
            }           
        }
                     
        return $errors;
    }
    
    /************************
     ** Собственные методы **
     ************************/
    
    /** проверяет правильность задания шкалы оценок
     * 
     * @return array массив ошибок, для функции validation()
     * @param string $scale - шкала оценок
     * 
     * @todo предусмотреть случай с отрицательными числами
     * @todo добавить проверку того, действительно ли по возрастанию расположены числовые оценки
     */
    protected function scale_is_valid($scale)
    {
        if ( ! trim($scale) )
        {// шкала не задана - это ошибка
            return array('scale' => $this->dof->get_string('err_scale', 'programmitems'));
        }
        // разбиваем шкалу на отдельные части
        $scale = explode(',',  trim($scale));
        
        foreach ( $scale as $element )
        {// начинаем проверять переданную шкалу
            if ( ! trim($element) AND trim($element) != '0')
            {// пустые элементы в шкале неодпустимы
                return array('scale' => $this->dof->get_string('err_scale_null_element', 'programmitems'));
            }
            if ( preg_match('/-/', $element) )
            {// это диапазон
                $boundaries = explode('-', $element);
                if ( count($boundaries) != 2 )
                {// диапазон задан неправильно
                    return array('scale' => $this->dof->get_string('err_scale', 'programmitems'));
                }
                // определим границы максимальных и минимальных значений
                $min = $boundaries[0];
                $max = $boundaries[1];
                if ( ($min == '' AND ! is_numeric($max)) OR (! is_numeric($min) AND $max == '') OR 
                       ($min != '' AND $max != '' AND (! is_numeric($max) OR ! is_numeric($min))) ) 
                {// диапазоны могут быть только числовыми
                    return array('scale' => $this->dof->get_string('err_scale_not_number_diapason', 'programmitems'));
                }
                if ( $min == $max )
                {// максимальная оценка в диапазоне равна минимальной: 
                    // диапазон задан неверно
                    return array('scale' => $this->dof->get_string('err_scale_max_min_must_be_different', 'programmitems'));
                }
            }
        }
        // если ошибки есть - то возвращаем массив, в котором указано, что именно произошло
        // если нет - то просто пустой массив
        return array();
    }
    
    /** Получить активные учебные процессы для предмета - чтобы определить можно ли менять курс moodle
     * @param int $pitemid - id предмета для которого ищутся учебные процессы
     * 
     * @return string|bool - массив ссылок на учебные процессы, которые нужно завершить или
     *                       false если таких процессов нет
     */
    protected function get_active_cstreams($pitemid)
    {
        if ( ! $cstreams = $this->dof->storage('cstreams')->
                        get_records(array('programmitemid'=>$pitemid, 'status'=>'active')) )
        {// активных учебных процессов нет - можно менять курс moodle
            return false;
        }
        $result = '';
        // создаем ссылки на каждый учебный процесс
        foreach ( $cstreams as $cstream )
        {
            $result .= '<a href=""'.$this->dof->url_im('cstreams', '/view.php', array('id' => $cstream->id)).'">';
            $result .= $cstream->name.'</a><br/>'."\n";
        }
        
        return $result;
    }
    
    /** Возвращает строку заголовка формы
     * @param int $pitemid
     * @return string
     */
    private function get_form_title($pitemid)
    {
        if ( ! $pitemid )
        {//заголовок создания формы
            if ($this->meta !== 1)
            {
                return $this->dof->get_string('newpitem','programmitems');
            }
            else
            {
                return $this->dof->get_string('newmetapitem','programmitems');
            }    
        }else 
        {    //заголовок редактирования формы
            if ($this->meta !== 1)
            {
                return $this->dof->get_string('editpitem','programmitems');
            }
            else
            {
                return $this->dof->get_string('editmetapitem','programmitems');
            }
            
        }
    }
    /** Получает список курсов moodle
     * 
     * @return array - массив пользователей для элемента select array( 'moodleid' => 'ФИО')
     * @todo исключить из массива пользователей fdo
     */
    private function get_list_mdlcourse()
    {
         dof_hugeprocess();
         // получаем список всех не удаленных пользователей из moodle 
         $courses = $this->dof->modlib('ama')->course(false)->get_list(null, 'fullname ASC');
         if ( ! $courses OR empty($courses) )
         {// данные не получены, вернем только "выбрать"
             return $this->dof_get_select_values();
         }
         // убираем из списка главную страницу
         unset($courses[SITEID]);
         // добавляем пункт "выбрать"
         $options = $this->dof_get_select_values();
         foreach ( $courses as $course )
         {// составляем комбинацию ФИО для каждого пользователя moodle
             $options[$course->id] = $course->fullname.' ('.$course->id.')';
         }
         // преобразовываем список к виду, пригодному для использования в элемента Select
         return $options;
    }
    
    /** Получить список учебных программ, в которые можно добавить предмет
     * 
     * @return array массив учебных программ в формате 'id' => 'Название учебной программы'
     */
    private function get_list_programms()
    {
        // получаем список программ, отсортированных по алфавиту
        $programms = $this->dof->storage('programms')->get_records(array('status'=>array('available','draft')), 'name ASC');
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($programms, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        return $rez;
    }
    
    /** Возврашает название статуса
     * @return string
     */
    private function get_status_name($status)
    {
        $this->dof->workflow('programmitems')->get_name($status);
    }
    
    /** Возвращает список доступных статусов
     * @todo переделать обращение к workflow, когда он будет готов
     * 
     * @param $id - id учебной программы в таблице programmitems
     * @return array список статусов в формате 'значение_в_базе' => 'текст_на_русском'
     */
    private function get_available_statuses($id)
    {
        return array();//$this->dof->workflow('programmitems')->get_available($id);
    }
}

/** Класс формы для поиска предмета
 * 
 */
class dof_im_programmitems_search_form extends dof_modlib_widgets_form
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
        $mform->addElement('header','formtitle', $this->dof->get_string('search','programmitems'));
        // поле "название или код"
        $mform->addElement('text', 'nameorcode', $this->dof->get_string('nameorcode','programmitems').':', 'size="20"');
        $mform->setType('nameorcode', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','programmitems'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
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

/**
 * Класс редактирования статуса предмета
 */
class dof_im_programmitems_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'programmitems';
    }
    
    protected function workflow_code()
    {
        return 'programmitems';
    }
}

/** Класс для формы поиска на странице списка предметов по параллелям
 * 
 */
class dof_im_programmitems_agenum_search_form extends dof_modlib_widgets_form
{
    
    protected function im_code()
    {
        return 'programmitems';
    }
    
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);        
        // поле с id программы для переадресации
        $mform->addElement('hidden','programmid', $this->_customdata->programmid);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('search'));
        // поле "название"
        $mform->addElement('text', 'name', $this->dof->modlib('ig')->igs('name').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        // поле "код"
        $mform->addElement('text', 'code', $this->dof->modlib('ig')->igs('code').':', 'size="20"');
        $mform->setType('code', PARAM_TEXT);
        // получаем список возможных статусов
        $statuses    = array();
        $statuses[0] = $this->dof->get_string('any', $this->im_code());
        $statuses    = array_merge($statuses, $this->dof->workflow('programms')->get_list());
        // поле "статус"
        $mform->addElement('select', 'status', $this->dof->gmodlib('ig')->igs('status').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->modlib('ig')->igs('find'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}

/** 
 * Класс для задания зависимостей для дисциплин
 */
class dof_im_programmitems_pridepends_form extends dof_modlib_widgets_form
{
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $avalist = $this->_customdata->avalist;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        //--------------------------------------------------------------------------------
        
        // id целевой дисциплины, нудно для доп.проверок
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('select', 'pridepend', $this->dof->get_string('adddepend', 'programmitems'), $avalist);
        
        //--------------------------------------------------------------------------------
        
        // добавили кнопку "добавить"
        $this->add_action_buttons(false, $this->dof->get_string('adddepend', 'programmitems'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
     }
     
     function validation($data, $files)
     {
         $errors = array();
         
         if (!array_key_exists('pridepend', $data) || !$data['pridepend'])
         {
             $errors['pridepend'] = $this->dof->get_string('candidatedepempty','programmitems');
             return $errors;
         }
         
         $listdepends = $this->dof->storage('pridepends')->get_list_by_id($data['id']);
         foreach($listdepends as $depend)
         {
             if ($depend->value == $data['pridepend'])
             {
                $errors['pridepend'] = $this->dof->get_string('alreadyexist','programmitems');
             }
         }

         // возвращаем все возникшие ошибки, если они есть
         return $errors;
     }        
     
}

/** Форма с кнопкой пересинхронизации всех потоков периода
 * @todo в сообщении выводить когда было добавлено задание на пересинхронизацию
 * @todo выводить когда была последняя пересинхронизация
 * @todo добавить notice_yesno после нажатии на кнопку
 * @todo Переместить объявление кнопки в definition_after_data чтобы она всегда отражала актуальные изменения в базе
 */
class dof_im_programmitems_resync_form extends dof_modlib_widgets_form
{
    /**
     * @param int - id дисциплины в таблице programmitems
     */
    protected $id;
    
    protected function im_code()
    {
        return 'programmitems';
    }

    public function definition()
    {
        GLOBAL $DB;
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        if ( ! $this->id  = $this->_customdata->id )
        {// не можем отобразить форму без дисциплины
            $this->dof->print_error('err_pitem_not_exists', $this->im_code());
        }
        // id дисциплины
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('resync',$this->im_code()));
        
        //  Получаем список неисполненных заданий
        // @todo на текущий момент в ядре нет нормального API для работы с таблицей todo
        // поэтому здесь используется прямое обращение к get_records_select
        // пояснение
        $mform->addElement('static', 'resync_notice', '',$this->dof->get_string('resync_notice','programmitems'));
        if ( $DB->get_records_select('block_dof_todo'," exdate=0  AND plugintype='storage' AND 
                 plugincode='cstreams' AND todocode='resync_programmitem_cstreams' AND intvar=".$this->id) )
        {// в базе уже есть добавленное необработанное задание на пересинхронизацию всех потоков курса
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('static', 'resync', '', 
                        $this->dof->get_string('resync_task_added',$this->im_code()));
        }else
        {// задание еще не добавлено - показываем кнопку
            $mform->addElement('submit', 'save', $this->dof->get_string('resync_cstreams',$this->im_code()));
        }
        // Кнопкa АКТИВАЦИИ ВСЕХ cpassed этого периода
        // пояснение
        $mform->addElement('static', 'active_notice', '',$this->dof->get_string('active_notice','programmitems') ); 
        if ( ! $DB->get_records_select('block_dof_todo'," exdate=0  AND plugintype='storage' AND 
                 plugincode='cstreams' AND todocode='programmitem_cpass_to_active' AND intvar=".$this->id) )
        {// в базе уже есть добавленное необработанное задание на пересинхронизацию всех потоков курса
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('submit', 'sus_go', $this->dof->get_string('suspend_go',$this->im_code()));
        }else 
        {// в базе уже есть добавленное необработанное задание на пересинхронизацию всех потоков курса
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('static', 'actie', '', 
                        $this->dof->get_string('active_suspend',$this->im_code(),$this->dof->get_string('suspend_go',$this->im_code())) );
        }
        // Кнопкa ПРИОСТАНОВКИ ВСЕХ cpassed этого периода  
        // пояснение
        $mform->addElement('static', 'stop_notice', '',$this->dof->get_string('stop_notice','programmitems') );       
        if ( ! $DB->get_records_select('block_dof_todo'," exdate=0  AND plugintype='storage' AND 
                 plugincode='cstreams' AND todocode='programmitem_cpass_to_suspend' AND intvar=".$this->id) )
        {// в базе уже есть добавленное задание 
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('submit', 'act_stop', $this->dof->get_string('active_stop',$this->im_code()));
        }else 
        {// в базе уже есть добавленное задание
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('static', 'stop', '', 
                        $this->dof->get_string('active_suspend',$this->im_code(),$this->dof->get_string('active_stop',$this->im_code())) );
        }        
        
        
    }
    
    /** Обработчик формы
     * 
     */
    public function process()
    {
        $mform =& $this->_form;
        
        if ( $formdata = $this->get_data() AND $this->dof->is_access('manage') AND confirm_sesskey() )
        {
            //print_object($formdata); die;
            if ( isset($formdata->save) )
            {
                return $this->dof->add_todo('storage', 'cstreams', 'resync_programmitem_cstreams',$formdata->id,null,2,time());
            }
            // запуск всех приостановленных
            if ( isset($formdata->sus_go) )
            {
                return $this->dof->add_todo('storage', 'cstreams', 'programmitem_cpass_to_active',$formdata->id,null,2,time());
            }
            // остановка всех активных
            if ( isset($formdata->act_stop) )
            {
                return $this->dof->add_todo('storage', 'cstreams', 'programmitem_cpass_to_suspend',$formdata->id,null,2,time());
            }   
        }
        
        return true;
    }
}

/** Форма смены курса для текущей дисцилины
 * @todo в сообщении выводить когда было добавлено задание на пересинхронизацию
 * @todo выводить когда была последняя пересинхронизация
 * @todo добавить notice_yesno после нажатии на кнопку
 * @todo Переместить объявление кнопки в definition_after_data чтобы она всегда отражала актуальные изменения в базе
 */
class dof_im_programmitems_change_course_form extends dof_modlib_widgets_form
{
    /**
     * @param int - id дисциплины в таблице programmitems
     */
    protected $id;

    protected function im_code()
    {
        return 'programmitems';
    }

    public function definition()
    {
        GLOBAL $DB;
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        if ( ! $this->id  = $this->_customdata->id )
        {// не можем отобразить форму без дисциплины
            $this->dof->print_error('err_pitem_not_exists', $this->im_code());
        }
        // id дисциплины
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        


        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('change_course_header',$this->im_code()));
        // не показываем кнопку смены курса, если такое задание уже стоит в очереди (таблица todo)
        $select = " plugintype = 'storage' AND plugincode = 'programmitems' 
                AND todocode = 'change_mcourse_in_programmitem' 
                AND intvar = '$this->id'
                AND exdate = 0";
        
        if ( !$DB->get_records_select('block_dof_todo', $select) )
        {
            // собираем данные для селекта с курсами
            $courses = $this->get_list_mdlcourse();
            $mform->addElement('checkbox', 'confirmcourse', '', $this->dof->get_string('change_course_confirm', $this->im_code()));
            $mform->addElement('select', 'courseid', $this->dof->get_string('change_course_select', $this->im_code()), $courses);
            $mform->addElement('submit', 'submitcourse', $this->dof->get_string('change_course_submit', $this->im_code()), array());
            $mform->setDefault('courseid',$this->dof->storage('programmitems')->get_field($this->id,'mdlcourse'));
        }else
        {
            $mform->addElement('static', 'stop', '', 
                        '<b style="color:green;">'.$this->dof->get_string('change_course_message',$this->im_code()).'</b>');
        }
    }

    /** Обработчик формы
     *
     */
    public function process()
    {
        GLOBAL $DB;
        if ( $data = $this->get_data() AND $this->dof->storage('programmitems')->is_access('edit:mdlcourse') AND confirm_sesskey() )
        {// форма подтверждена
            if ( !$data->confirmcourse )
            {// если не стоит подтверждение смены курса - ничего не делаем
                return false;
            }
            // добавляем дополнительные данные
            $params = new stdClass();
            $params->mdlcourse = $data->courseid; 
            $select = " plugintype = 'storage' AND plugincode = 'programmitems' 
                AND todocode = 'change_mcourse_in_programmitem' 
                AND intvar = '$this->id'
                AND exdate = 0";
            if ( !$DB->get_records_select('block_dof_todo', $select) )
            {// кидаем todo, если его еще нет
                return $this->dof->add_todo('storage', 'programmitems', 'change_mcourse_in_programmitem', $this->id, $params, 2, time());
            }
        }
    }
    
    public function validation($data,$files) 
    {
        
        $errors = array();
        if ( !isset($data['confirmcourse']) )
        {// подтверждения не стоит
            $errors['confirmcourse'] = $this->dof->get_string('change_course_not_confirmed', $this->im_code());
        }
        return $errors;
        
    }
    
    /** Получает список курсов moodle
     * 
     * @return array - массив пользователей для элемента select array( 'moodleid' => 'ФИО')
     * @todo исключить из массива пользователей fdo
     */
    private function get_list_mdlcourse()
    {
         dof_hugeprocess();
         // получаем список всех не удаленных пользователей из moodle 
         $courses = $this->dof->modlib('ama')->course(false)->get_list(null, 'fullname ASC');
         if ( ! $courses OR empty($courses) )
         {// данные не получены, вернем только "выбрать"
             return $this->dof_get_select_values();
         }
         // убираем из списка главную страницу
         unset($courses[SITEID]);
         // добавляем пункт "выбрать"
         $options = $this->dof_get_select_values();
         foreach ( $courses as $course )
         {// составляем комбинацию ФИО для каждого пользователя moodle
             $options[$course->id] = $course->fullname.' ('.$course->id.')';
         }
         // преобразовываем список к виду, пригодному для использования в элемента Select
         return $options;
    }
}

?>
