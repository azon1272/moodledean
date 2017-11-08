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

/** Форма редактирования учебного плана
 * 
 */
class dof_im_learningplan_edit_form extends dof_modlib_widgets_form
{
    /**
     * @var addvars - массив GET-параметров для генерации ссылок
     */
    protected $addvars;

    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var object - объект индивидуального учебного плана
     */
    protected $lp;
    
    /**
     * @var int -id подразделения в таблице departments, в котором происходит работа
     */
    protected $departmentid = 0;
    
    /**
     * @var string - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup' 
     */
    protected $type;
    
    /**
     * @var int - id из таблицы programmsbcs или agroups
     */
    protected $typeid;
    
    /**
     * @var object - объект, содержащий id элементов формы, классифицированные по назначению
     *             ->addtoplan = array()
     *             ->excludefromplan = array()
     *             ->addtoagenum = array()
     *             ->transfertoagenum = array()
     *             ->planrequired = array()
     *             ->planrequiredall = array()
     *             ->autosubscribeid = string
     */
    protected $submitparams;

    protected function im_code()
    {
        return 'learningplan';
    }
    
    protected function storage_code()
    {
        return 'learningplan';
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
        $this->dof          = $this->_customdata->dof;
        $this->type         = $this->_customdata->type;
        $this->typeid       = $this->_customdata->typeid;
        $this->departmentid = $this->_customdata->departmentid;
        $this->submitparams = new stdClass();
        $this->submitparams->addtoplan = array();
        $this->submitparams->excludefromplan = array();
        // Массив для передачи GET-параметров
        global $addvars;
        if ( is_array($addvars) )
        {
            $this->addvars = $addvars;
        } else
        {
            $this->addvars = array();
        }
        $this->addvars['departmentid'] = $this->departmentid;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $this->lp = $this->dof->storage('learningplan')->create_learningplan($this->type, $this->typeid);
        // устанавливаем все скрытые поля 
        $mform->addElement('hidden','departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        // Отображаем основную информацию
        $this->show_general_info();
        // Если группа или подписка уже в статусе "завершено", отобразим уведомление и кнопку "Назад"
        if ( $status = $this->dof->storage($this->type. 's')->get_field($this->typeid, 'status') )
        {
            if ( $status == 'completed' )
            {
                if ( $this->type == 'agroup')
                {
                    $mform->addElement('static', 'notice', '', $this->dof->get_string('statuscompletedagroup',$this->im_code()));
                } else if ( $this->type == 'programmsbc' )
                {
                    $mform->addElement('static', 'notice', '', $this->dof->get_string('statuscompletedpsbc',$this->im_code()));
                }
                $buttonarray=array();
                $buttonarray[] = &$mform->createElement('submit', 'cancel', $this->dof->modlib('ig')->igs('back'));
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('notice');
                return;
            }
        }
        // Отображаем информацию о перезачётах
        $this->show_reoffsets();
        // Для вывода кнопки "запланировать все обязательные дисциплины"
        $this->countrequired = 0;
        // Отображаем информацию об академической разнице
//        $this->countrequired += $this->show_academic_debts();
        $this->show_academic_debts();
        // Количество параллелей в учебном году
        $edulevelagenums = $this->get_edulevel_agenums($this->lp->programm->edulevel);
        // Отображаем информацию по семестрам
        if ( $this->lp->agenums > 0 )
        {
            // Переменная для вывода учебной нагрузки (часы и ЗЕТ) по учебным годам
            $agenums = array();
            // Счётчик для отображения часов по учебным годам
            $currentagenums = -1;
            for ( $agenum = 0; $agenum <= $this->lp->agenums; $agenum++)
            {
//                $this->countrequired += $this->show_age($agenum);
                // Какие семестры выводим в нагрузке за год:
                if ( $currentagenums >= 0 )
                {
                    $agenums[] = $agenum;
                }
                $currentagenums++;
                // Отобразим семестр
                $this->show_age($agenum);
                // Если текущий семестр является концом учебного года, после него
                // показываем сводную статистику по нагрузке (часам и ЗЕТ)
                if ( $currentagenums >= $edulevelagenums )
                {
                    $mform->addElement('header', "edulevel_agenum_" . $agenum, $this->dof->get_string('eduyearhours', 'learningplan'));
                    $mform->setExpanded("edulevel_agenum_" . $agenum);
                    $mform->setType("edulevel_agenum_" . $agenum, PARAM_INT);
                    $mform->closeHeaderBefore("edulevel_agenum_" . $agenum);
                    $this->show_hours($agenums, true);
                    $agenums = array();
                    $currentagenums = 0;
                }
            }
            // В случае, если количество семестров не кратно "учебному году"
            if ( $currentagenums > 0 )
            {// Выведем "остаток"
                $mform->addElement('header', "edulevel_agenum_" . $this->lp->agenums, $this->dof->get_string('eduyearhours', 'learningplan'));
                $mform->setExpanded("edulevel_agenum_" . $this->lp->agenums);
                $mform->setType("edulevel_agenum_" . $this->lp->agenums, PARAM_INT);
                $mform->closeHeaderBefore("edulevel_agenum_" . $this->lp->agenums);
                $this->show_hours($agenums, true);
            }
        }
        if ( $this->countrequired > 0 )
        {
            $mform->closeHeaderBefore('planrequired');
            // submitid для передачи AJAX-скрипту
            $this->submitparams->planrequiredall = 'planrequiredall';
            $mform->addElement('submit', "planrequired", $this->dof->get_string('planrequiredall',$this->im_code()), 'id="planrequiredall"');
        } else
        {
            $mform->closeHeaderBefore('buttonar');
        }
        // кнопки "сохранить" и "отмена"
        $buttonarray=array();
        $subscribedescription = $this->dof->get_string('subscribedisciplines',$this->im_code());
        if ( !isset($this->lp->learningplan[$this->lp->agenum]) OR
                count($this->lp->learningplan[$this->lp->agenum]->planned) < 1 )
        {
            $subscribedescription = $this->dof->get_string('autosubscribenoelems',$this->im_code());
            $mform->disabledIf('buttonar[subscribe]', 'autosubscribedisabled', 'eq', 1);
        }
        $buttonarray[] = &$mform->createElement('submit', 'subscribe', $subscribedescription);
        $buttonarray[] = &$mform->createElement('submit', 'cancel', $this->dof->modlib('ig')->igs('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', 'buttonar', array(' '), true);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     *  Добавление дополнительльных полей формы и установка значений по умолчанию
     * после загрузки данных в форму (если происходит редактирование)
     * 
     * @return void
     */
    public function definition_after_data()
    {
    }
    
    /** Проверка данных формы
     * @param array $data - данные, пришедшие из формы
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data,$files)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $errors = array();
        // Ссылка для перехода после ошибок, связанных с изменением дисциплин
        $params = array('type' => $this->type, "{$this->type}id" => $this->typeid);
        $url = $this->dof->url_im('learningplan','/index.php', $this->addvars + $params);
        $errorlink = $this->dof->get_string('error_link', $this->im_code(), $url);

        // Обработка добавления запланированной дисциплины
        if ( !empty($data['addtoplan']) AND is_array($data['addtoplan']) )
        {
            // Форма отсылает только один элемент
            $pitemid = current(array_keys($data['addtoplan']));
            $agenum  = current($data['addtoplan']);
            // Проверяем, что дисциплина существует, активная и семестр лежит в
            //  пределах допустимого диапазона
            $programmid = $this->lp->programm->id;
            $conds = array('id' => $pitemid, 'programmid' => $programmid, 'status' => 'active');
            if ( $agenum < 0 OR $agenum > $this->lp->agenums )
            {
                $errors['error'] = $this->dof->get_string('error_agenum', $this->im_code()) . '. ' . $errorlink;
            }
            if ( ! $this->dof->storage('programmitems')->is_exists($conds) )
            {
                $errors['error'] = $this->dof->get_string('error_discipline', $this->im_code()) . '. ' . $errorlink;
            }
        }

        // Обработка исключения запланированной дисциплины
        if ( !empty($data['excludefromplan']) AND is_array($data['excludefromplan']) )
        {
            // Форма отсылает только один элемент
            $pitemid = current(array_keys($data['excludefromplan']));
            // Проверяем, что дисциплина находится в плане
            $conds = array('type' => $this->type,
                           "{$this->type}id" => $this->typeid,
                           'programmitemid' => $pitemid);
            // Проверим, есть ли такая запланированная дисциплина
            if ( ! $this->dof->storage('learningplan')->is_exists($conds) )
            {
                $errors['error'] = $this->dof->get_string('error_notplanned', $this->im_code()) . '. ' . $errorlink;
            }
        }
        
        // Обработка добавления запланированой дисциплины в другую параллель
        if ( !empty($data['transfertoagenum']) AND is_array($data['transfertoagenum']) )
        {
            // Форма отсылает только один элемент
            $pitemid = current(array_keys($data['transfertoagenum']));
            if ( !empty($data['transfer']) AND isset($data['transfer'][$pitemid]) )
            {
                $agenum = $data['transfer'][$pitemid];
            }
            // Проверяем, что дисциплина существует, активная и семестр лежит в
            //  пределах допустимого диапазона
            $programmid = $this->lp->programm->id;
            $conds = array('id' => $pitemid, 'programmid' => $programmid, 'status' => 'active');
            if ( $agenum < 0 OR $agenum > $this->lp->agenums )
            {
                $errors['error'] = $this->dof->get_string('error_agenum', $this->im_code()) . '. ' . $errorlink;
            }
            if ( ! $this->dof->storage('programmitems')->is_exists($conds) )
            {
                $errors['error'] = $this->dof->get_string('error_discipline', $this->im_code()) . '. ' . $errorlink;
            }
        }

        // Обработка добавления незапланированной дисциплины в другую параллель
        if ( !empty($data['addtoplan']) AND is_array($data['addtoplan']) )
        {
            // Форма отсылает только один элемент
            $pitemid = current(array_keys($data['addtoplan']));
            if ( !empty($data['transfer']) AND isset($data['transfer'][$pitemid]) )
            {
                $agenum = $data['transfer'][$pitemid];
            }
            // Проверяем, что дисциплина существует, активная и семестр лежит в
            //  пределах допустимого диапазона
            $programmid = $this->lp->programm->id;
            $conds = array('id' => $pitemid, 'programmid' => $programmid, 'status' => 'active');
            if ( $agenum < 0 OR $agenum > $this->lp->agenums )
            {
                $errors['error'] = $this->dof->get_string('error_agenum', $this->im_code()) . '. ' . $errorlink;
            }
            if ( ! $this->dof->storage('programmitems')->is_exists($conds) )
            {
                $errors['error'] = $this->dof->get_string('error_discipline', $this->im_code()) . '. ' . $errorlink;
            }
        }
        
        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        // Проблема с выводом ошибок: создаём элемент, к которому будем привязывать сообщения об ошибках
        if ( ! empty($errors) )
        {
            $mform->addElement('static','error');
        }
        
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Обработать пришедшие из формы данные и сделать redirect в случае отмены
     *
     * @return bool - результат операции
     */
    public function process($isajax = false)
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $this->is_validated() AND $formdata = $this->get_data() )
        {// Получили данные формы';
            if ( isset($formdata->cancel) OR isset($formdata->buttonar['cancel']) )
            { // Ввод данных отменен - возвращаем на страницу назад
                // Странная штука - если вручную сделать 'cancel'-кнопку (в группе),
                // то is_cancelled() не работает, из-за того, что она в группе, и
                // из-за этого optional_param() не видит её...
                redirect($this->dof->url_im($this->type.'s','/list.php',$this->addvars));
            }
            
            if ( isset($formdata->subscribe) OR isset($formdata->buttonar['subscribe']) )
            { // Перенаправляем пользователя на дополнительные настройки
                $adds = array('type'             => $this->type,
                               "{$this->type}id" => $this->typeid);
                redirect($this->dof->url_im('learningplan','/create.php',$this->addvars + $adds));
            }
            
            if ( isset($formdata->autosubscribe) )
            { // Автоматически подписываем студента/группу на потоки и создаём подписки на дисциплины
                $adds = array('type'             => $this->type,
                               "{$this->type}id" => $this->typeid);
                $pitems = $this->dof->storage('learningplan')->get_subscribe_current_agenum_pitems($this->type, $this->typeid);
                if ( !empty($pitems) )
                {
                    $this->dof->storage('cpassed')->sign_pitems_current_agenum($this->type, $this->typeid, $pitems);
                }
                // Когда закончили переправляяем сюда же
                if ( !$isajax )
                {
                    redirect($this->dof->url_im('learningplan','/index.php',$this->addvars + $adds));
                }
            }

            // Обработка добавления запланированной дисциплины
            if ( !empty($formdata->addtoplan) )
            {
                // Форма отсылает только один элемент
                $pitemid = current(array_keys($formdata->addtoplan));
                $agenum  = current($formdata->addtoplan);
                $this->dof->storage('learningplan')->add_to_planned($this->type, $this->typeid, $agenum, $pitemid);
            }
            
            // Обработка исключения запланированной дисциплины
            if ( !empty($formdata->excludefromplan) )
            {
                // Форма отсылает только один элемент
                $pitemid = current(array_keys($formdata->excludefromplan));
                $this->dof->storage('learningplan')->remove_from_planned($this->type, $this->typeid, $pitemid);
            }
            
            // Обработка добавления запланированой дисциплины в другую параллель
            if ( !empty($formdata->transfertoagenum) )
            {
                // Форма отсылает только один элемент
                $pitemid = current(array_keys($formdata->transfertoagenum));
                if ( !empty($formdata->transfer) AND isset($formdata->transfer[$pitemid]) )
                {
                    $agenum = $formdata->transfer[$pitemid];
                }
                $this->dof->storage('learningplan')->change_planned_agenum($this->type, $this->typeid, $agenum, $pitemid);
            }

            // Обработка добавления незапланированной дисциплины в другую параллель
            if ( !empty($formdata->addtoagenum) )
            {
                // Форма отсылает только один элемент
                $pitemid = current(array_keys($formdata->addtoagenum));
                if ( !empty($formdata->transfer) AND isset($formdata->transfer[$pitemid]) )
                {
                    $agenum = $formdata->transfer[$pitemid];
                }
                $this->dof->storage('learningplan')->add_to_planned($this->type, $this->typeid, $agenum, $pitemid);
            }
            
            if ( !empty($formdata->planrequired) )
            {
                if ( is_array($formdata->planrequired) )
                {
                    $agenum = current(array_keys($formdata->planrequired));
                    $this->dof->storage('learningplan')->add_to_planned_required_agenum($this->type, $this->typeid, $agenum);
                } else if ( is_string ($formdata->planrequired) )
                {
                    $this->dof->storage('learningplan')->add_to_planned_required_agenum($this->type, $this->typeid);
                }
            }
            return true;
        }
        return false;
    }
    
    /** Получить описания столбцов к записям
     */
    private function get_head_descriptions()
    {
        return array(
            $this->dof->get_string('discipline', $this->im_code()),
            $this->dof->get_string('mark', $this->im_code()),
            $this->dof->get_string('agenum', $this->im_code()),
            $this->dof->get_string('controltype', $this->im_code()),
            $this->dof->get_string('actions', $this->im_code()),
        );
    }
    
    /**
     * Получить параметры всех дисциплин планируемой программы
     * @return array массив объектов с полями
     * ->maxcredit
     * ->hours
     * ->hourstheory
     * ->hourspractice
     * ->hoursweek
     * ->hourslab
     * ->hoursind
     * ->hourscontrol
     * ->hoursclassroom
     * ->agenum
     * ->agenums
     */
    public function get_pitemsparams()
    {
        $realstatuses = array_keys($this->dof->workflow('programmitems')->get_meta_list('real'));
        $pitemshours  = array();
        if ( $pitems = $this->dof->storage('programmitems')
                ->get_pitems_list($this->lp->programm->id, false, $realstatuses) )
        {
            foreach ( $pitems as $pitemid => $pitem )
            {
                $pitemshours[$pitemid] = $this->dof->storage('programmitems')->get_hours_sum(array($pitemid));
                $pitemshours[$pitemid] = dof_object_merge($pitem, $pitemshours[$pitemid]);
            }
        }
        // Доступные для планирования семестры
        for ( $num = 0; $num <= $this->lp->agenums; $num++ )
        {
            $sorted[$num] = array();
            if ( !empty($this->lp->learningplan[$num]->learned) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->learned as $pid => $pitem )
                {
                    $pitemshours[$pid]->agenums = $this->get_transfer_agenums($num, $pitem->agenum);
                }
            }
            // Запланированные
            if ( !empty($this->lp->learningplan[$num]->planned) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->planned as $pid => $pitem )
                {
                    $pitemshours[$pid]->agenums = $this->get_transfer_agenums($num, $pitem->agenum);
                }
            }
            // Планируемые
            if ( !empty($this->lp->learningplan[$num]->planning) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->planning as $pid => $pitem )
                {
                    $pitemshours[$pid]->agenums = $this->get_transfer_agenums($num, $pitem->agenum);
                }
            }
        }
        return $pitemshours;
    }
    
    /**
     * Возвратить объект с параметрами для запроса (для AJAX-скрипта)
     * 
     * ->sesskey
     * ->formname
     * В массивах и строках передаются id элементов формы
     * ->addtoplan = array()
     * ->excludefromplan = array()
     * ->tranfertoagenum = array()
     * ->addtoagenum = array()
     * ->planrequired = array()
     * ->planrequiredall = string
     * ->autosubscribeid = string
     * @return object
     */
    public function get_submitparams()
    {
        $this->submitparams->sesskey = sesskey();
        $this->submitparams->formname = $this->get_form_identifier();
        return $this->submitparams;
    }
    
    /**
     * Получить список параллелелей для select-элемента переноса дисциплины в другую параллель
     * 
     * @param int $agenum - номер параллели, из которой производится перенос дисциплины
     * @param int $pitemagenum - номер указанной в программе параллели дисциплины
     */
    public function get_transfer_agenums($agenum, $pitemagenum)
    {
        // Если в программе запрещены плавающие периоды, и дисциплина привязана к параллели
        if ( $this->lp->programm->flowagenums == 0 AND $pitemagenum != 0 )
        {
            return array();
        }
        
        $agenums = array(0 => 0);
        for ( $agen = 1; $agen <= $this->lp->agenums; $agen++ )
        { // Создаём список со всеми параллелями
            if ( $agen >= $this->lp->agenum )
            { // В пройденные параллели переносить дисциплины нельзя
                $agenums[$agen] = $agen;
            }
        }
        // Переносить в тот же семестр дисциплину нельзя
        unset($agenums[$agenum]);
        // Переносить в нулевую параллель нельзя
        unset($agenums[0]);
        return $agenums;
    }

    /**
     * Получить список доступных для переноса параллелей для всех дисциплин
     *
     * @return array
     */
    public function get_transfer_agenums_all(){
        $realstatuses = array_keys($this->dof->workflow('programmitems')->get_meta_list('real'));
        $pitemsagenums  = array();
        if ( $pitems = $this->dof->storage('programmitems')
            ->get_pitems_list($this->lp->programm->id, false, $realstatuses) )
        {
            for ( $num = 0; $num <= $this->lp->agenums; $num++ )
            {
                // Запланированные
                if ( !empty($this->lp->learningplan[$num]->planned) )
                { // Дисциплины имеются
                    foreach ( $this->lp->learningplan[$num]->planned as $pid => $pitem )
                    {
                        $pitemsagenums[$pid] = $this->get_transfer_agenums($num, $pitem->agenum);
                    }
                }
                // Планируемые
                if ( !empty($this->lp->learningplan[$num]->planning) )
                { // Дисциплины имеются
                    foreach ( $this->lp->learningplan[$num]->planning as $pid => $pitem )
                    {
                        $pitemsagenums[$pid] = $this->get_transfer_agenums($num, $pitem->agenum);
                    }
                }
            }
        }
        return $pitemsagenums;

    }
        
    /**
     * Отобразить учебный семестр, в котором находятся запланированные, пройденные и
     *  доступные для планирования дисциплины. Возвращает количество обязательных дисциплин,
     *  доступных для планирования по этому семестру
     * 
     * @param int $num - номер семестра 
     * @return int - количество обязательных программ, доступных для планирования по этому семестру
     */
    private function show_age($num)
    {
        $mform =& $this->_form;
        // Сначала определим текущую параллель и выставим необходимые флаги для отображения
        $learned = false;
        $count = 0; // Количество элементов для отображения
        $data  = array(); // Данные элементов
        if ( $num == 0 )
        {
            $header = $this->dof->get_string('agenumall',$this->im_code());
        } else
        {
            $header = $this->dof->get_string('agenum',$this->im_code(), $num);
        }
        if ( $this->lp->agenum > $num AND $num != 0 )
        { // Семестр уже пройден, для него нельзя планировать (только показать пройденные)
            $learned = false; // Пока убрали эту функцию (r5901)
            $header .= ' (' . strtolower($this->dof->get_string('agenumfinished',$this->im_code())) . ')';
        }
        if ( $this->lp->agenum == $num AND $num != 0)
        {
            $header .= ' (' . strtolower($this->dof->get_string('agenumcurrent',$this->im_code())) . ')';
        }
        $mform->addElement('header', 'ages'.$num, $header);
        $mform->setExpanded('ages'.$num);
        if ( !isset($this->lp->learningplan[$num]) )
        { // Не получили семестр с дисциплинами?
            $string = '<div id="no_elements_' . $num . '"class="noelements">' . $this->dof->get_string('noelements', $this->im_code()) . "</div>";
            $mform->addElement('static', 'no_elements', $string);
            return;
        }
        // Отобразим пройденные дисциплины
        if ( !empty($this->lp->learningplan[$num]->learned) )
        { // Дисциплины имеются
            foreach ( $this->lp->learningplan[$num]->learned as $pid => $pitem )
            {
                $count++;
                $data[] = $this->get_learned_string($pitem);
            }
        }
        // Для вывода кнопки "Запланировать все обязательные дисциплины"
        $countrequired = $this->countrequired;
        // Для вывода кнопки "Автоматическая подписка по учебному плану"
        $countplanned = 0;
        // Отобразим дисциплины, которые запланированы и можно запланировать
        if ( !$learned )
        {
            // Запланированные
            if ( !empty($this->lp->learningplan[$num]->planned) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->planned as $pitem )
                {
                    $countplanned++;
                    $count++;
                    $data[] = $this->get_planned_string($pitem, $num);
                }
            }
            // Планируемые
            if ( !empty($this->lp->learningplan[$num]->planning) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->planning as $pitem )
                {
                    $count++;
                    $data[] = $this->get_planning_string($pitem, $num);
                }
            }
        }
        
        $haveelements = 'haveelements';
        $tableclass   = '';
        if ( $count > 0 )
        {
            $visible = true;
        } else
        {
            $visible = false;
            $haveelements = 'noelements';
            $tableclass   = 'generaltable havenoelements';
        }
        $table = new html_table();
        $table->id = 'pitems_' . $num;
        $table->tablealign = "left";
        $table->attributes['class']= $tableclass;
//        $table->cellspacing = 5;
        $table->width = '100%';
        $table->size = array('', '125px', '75px', '150px', '100px');

        $table->head = $this->get_head_descriptions();
        $table->colclasses = array('discipline', 'mark', 'agenum', 'controltype', 'actions');
        $table->align = array('center','center','center','center','center');
        $table->data = $data;
        // Добавляем элементы в форму
        $mform->addElement('html', $this->dof->modlib('widgets')->print_table($table, true));
        // Количество часов и ЗЕТ
        if ( $num > 0 )
        {
            $this->show_hours($num, $visible);
        }
        // Добавляется кнопка "запланировать обязательные дисциплины", но 
        //  показывается только в случае, если в этой параллели есть обязательная
        //  незапланированная дисциплина 
        $planrequiredclass = 'havenoelements';
        if ( ($this->countrequired - $countrequired) > 0 )
        {
            // submitid для передачи AJAX-скрипту
            $planrequiredclass = '';
        }
        $submitid = 'aid_planrequired_' . $num;
        $this->submitparams->planrequired[] = $submitid;
        $mform->addElement('submit', "planrequired[{$num}]", $this->dof->get_string('planrequired',$this->im_code()), 'id="' . $submitid . '" class="' . $planrequiredclass . '"');
        if ( $num == $this->lp->agenum AND $num != 0 )
        {
            
            if ( $countplanned < 1 )
            {
                // Автоподписка 
                $mform->addElement('submit', "autosubscribe", $this->dof->get_string('autosubscribenoelems', $this->im_code()));
                $mform->addElement('hidden', "autosubscribedisabled", '1');
                $mform->setType('autosubscribedisabled', PARAM_INT);
                $mform->disabledIf('autosubscribe', 'autosubscribedisabled', 'eq', 1);
            } else
            {
                // submitid для передачи AJAX-скрипту
                $this->submitparams->autosubscribeid = 'id_autosubscribe';
                $mform->addElement('submit', "autosubscribe", $this->dof->get_string('autosubscribe',$this->im_code()), 'id="id_autosubscribe"');
            }
        }
        $string = '<div id="no_elements_' . $num . '"class="' . $haveelements . '">' . $this->dof->get_string('noelements', $this->im_code()) . "</div>";
        $mform->addElement('static', 'no_elements', $string);
        return $this->countrequired;
    }
    
    private function show_config($configcode)
    {
        if ( $config = $this->dof->storage('config')->get_config($configcode,
                'storage', 'learningplan', $this->departmentid) )
        {
            $mform =& $this->_form;
            $mform->addElement($config->type, $configcode, '', $this->dof->get_string('config:'.$configcode,$this->im_code()));
            $mform->setDefault($configcode, $config->value);
            $mform->disabledIf($configcode, 'disabled');
        }
    }
    

    /** Показать общую информацию о типе учебного плана, программе обучения и ссылки
     *  на студента / группу
     */
    private function show_general_info()
    {
        $mform =& $this->_form;
        $mform->addElement('header', 'generalinfo', $this->dof->get_string('generalinfo',$this->im_code()));
        $mform->setExpanded('generalinfo');
        $typeinfo = '';
        $typelink = '';
        // Отображение студента или группы в зависимости от типа учебного плана
        if ( $this->type == 'programmsbc' )
        {
            $typeinfo = 'student';
            $typeurl = $this->dof->url_im('programmsbcs', '/view.php', $this->addvars + array('programmsbcid' => $this->typeid));
            $typelink = '<a href="' . $typeurl . '">' . $this->lp->name . '</a>';
        } else if ( $this->type == 'agroup' )
        {
            $typeinfo = 'group';
            $typeurl = $this->dof->url_im('agroups', '/view.php', $this->addvars + array('agroupid' => $this->typeid));
            $typelink = '<a href="' . $typeurl . '">' . $this->lp->name . '</a>';
        }
        
        $mform->addElement('static', 'typeinfo', $this->dof->get_string($typeinfo, $this->im_code()).':', $typelink);
        // Отображение программы
        $programmurl = $this->dof->url_im('programms', '/view.php', $this->addvars + array('programmid' => $this->lp->programm->id));
        $programmlink = '<a href="' . $programmurl .'">'
                . "{$this->lp->programm->name} [{$this->lp->programm->code}]</a>";
        $mform->addElement('static', 'programmname', $this->dof->get_string('programm',$this->im_code()).':', $programmlink);
        $agenumtext = $this->lp->agenum;
        if ( empty($agenumtext) )
        {
            $agenumtext = $this->dof->get_string('edunotstarted',$this->im_code());
        }
        $mform->addElement('static', 'agenum', $this->dof->get_string('currentagenum',$this->im_code()).':', $agenumtext);
        $mform->addElement('static', 'agenums', $this->dof->get_string('agenums',$this->im_code()).':', $this->lp->agenums);
        if ( !empty($this->lp->age) )
        {
            $ageurl = $this->dof->url_im('ages', '/view.php', $this->addvars + array('ageid' => $this->lp->age->id));
            $agestatus = $this->dof->workflow('ages')->get_name($this->dof->storage('ages')->get_field($this->lp->age->id, 'status'));
            $agelink = '<a href="' . $ageurl . '">' . $this->lp->age->name . " [{$agestatus}]</a>";
            $mform->addElement('static', 'ageid', $this->dof->get_string('ageid',$this->im_code()).':', $agelink);
        } else
        {
            $mform->addElement('static', 'ageid', $this->dof->get_string('ageid',$this->im_code()).':', 
                    $this->dof->get_string('noageid',$this->im_code()));
        }
        // Блок с настройками
        $adds = array('departmentid' => $this->departmentid, 'plugintype' => 'storage', 'plugincode' => 'learningplan');
        $configurl  = $this->dof->url_im('cfg','/edit.php', $adds);
        $configlink = $this->dof->get_string('config',$this->im_code()) .
                '<a href="' . $configurl . '"> (' . $this->dof->get_string('configset',$this->im_code()) . ')</a>';
        $mform->addElement('header', 'config', $configlink);

        // Элемент для отключения checkbox'ов
        $mform->addElement('hidden', 'disabled', 0);
        $mform->setType('disabled', PARAM_INT);
        // Выбирать преподавателя автоматически
        $this->show_config('autochooseteacher');
        // Использовать поле "рекомендованный преподаватель"
        $this->show_config('recommendedteacher');
        // Отображать все планируемые дисциплины в меню, если в программе
        // задана опция "Плавающие учебные планы" - flowagenums
        $this->show_config('showallpitems');
        
//        // Тип обучения
//        $edutype = 'individual';
//        $mform->addElement('static', 'edutype', $this->dof->get_string('edutype',$this->im_code()).':', $edutype); 
    }
    
    /** Отобразить секцию с перезачётами
     */
    private function show_reoffsets()
    {
        $mform =& $this->_form;
        $header = $this->dof->get_string('reoffsets',$this->im_code());
        // Ссылка на "Ведомость перезачёта оценок", если тип плана - подписка
        if ( $this->type == 'programmsbc' )
        {
            $link = ' <a href="'. $this->dof->url_im('cpassed', '/register.php', $this->addvars + array('programmsbcid' => $this->typeid))
                    . '">(' . $this->dof->get_string('reoffsetslink',$this->im_code()) . ')</a>';
            $header .= $link;
        }
        $mform->addElement('header', 'reoffsets', $header);
        $mform->setExpanded('reoffsets');
        
        $reoffsets = $this->dof->storage('learningplan')->get_reoffset_pitems($this->type, $this->typeid, false, array('active'));
        $count = 0; // Количество элементов для отображения
        
        if ( !empty($reoffsets) )
        {
            $data = array();
            foreach ( $reoffsets as $pitemid => $pitem )
            {
                // Элементы таблицы
                // Сначала дисциплина и ссылка на неё
                $count++;
                $row = array();
                $cell = new html_table_cell();
                $title = '';
                $pitemname = $pitem->name . " [$pitem->code]";
                // Для обязательной дисциплины добавим звёздочку в название
                if ( $pitem->required )
                {
                    $pitemname .= ' *';
                    $title = $this->dof->get_string('discipline_required',$this->im_code());
                }
                $pitemurl = $this->dof->url_im('programmitems', '/view.php',
                        array('pitemid' => $pitemid, 'departmentid' => $this->departmentid));
                $pitemlink = '<a href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>';
                $cell->text = $mform->createElement('static', "reoffset[$pitemid]", '', $pitemlink)->toHtml();
//                $cell->attributes = array('align'=>'center');
                $row[] = $cell;
                $cell = new html_table_cell();
                $cell->text = $mform->createElement('static', "reoffsetm[$pitemid]", '', $pitem->grade)->toHtml();
//                $cell->attributes = array('align'=>'center');
                $row[] = $cell;
                // Семестр в оригинальной программе
                $cell = new html_table_cell();
                $cell->text = $mform->createElement('static', "agenum[$pitemid]", '', $pitem->agenum)->toHtml();
                $row[] = $cell;
                // Тип итогового контроля
                $controltypeid = $this->dof->storage('programmitems')->get_field($pitemid, 'controltypeid');
                $controltype = $this->dof->modlib('refbook')->get_st_total_control_name($controltypeid);
                $cell = new html_table_cell();
                $cell->text = $mform->createElement('static', "controltype[$pitemid]", '', $controltype)->toHtml();
                $row[] = $cell;
                $data[] = $row;
            }
            // Делаем таблицу с элементами формы
            $table = new html_table();
            $table->tablealign = "left";
            $table->cellspacing = 5;
            $head = $this->get_head_descriptions();
            unset($head[4]);
            $table->head  = $head;
            $table->align = array('center','center','center','center');
            $table->data  = $data;
            // добавляем элемент в форму
        }
        if ( $count > 0 )
        {
            $mform->addElement('html', $this->dof->modlib('widgets')->print_table($table, true));
        } else
        {
            $mform->addElement('static', 'no_elements', $this->dof->get_string('noelements', $this->im_code()));
        }
        
    }
    
    /** Отобразить академическую разницу
     */
    private function show_academic_debts()
    {
        $mform =& $this->_form;
        $header = $this->dof->get_string('academicdebts',$this->im_code());
        // Ссылка на "Ведомость перезачёта оценок", если тип плана - подписка
//        if ( $this->type == 'programmsbc' )
//        {
//            $link = ' <a href="'. $this->dof->url_im('cpassed', '/register.php', $this->addvars + array('programmsbcid' => $this->typeid))
//                    . '">(' . $this->dof->get_string('reoffsetslink',$this->im_code()) . ')</a>';
//            $header .= $link;
//        }
        $mform->addElement('header', 'academicdebts', $header);
        $mform->setExpanded('academicdebts');
        
        $academicdebts = array();
        // Для группы академической разницы не отображаем
        if ( $this->type == 'programmsbc' )
        {
            $debts = $this->dof->storage('cpassed')->get_academic_debts($this->typeid);
            $academicdebts = $this->dof->storage('cpassed')->get_actual_academicdebts_cpassed($debts);
            // Добавим к cpassed'ам информацию о дисциплинах
            foreach ( $academicdebts as $id => $cpassed )
            {
                if ( $pitem = $this->dof->storage('programmitems')->get($cpassed->programmitemid, 'id,code,name,required,agenum') )
                {
                    $academicdebts[$id]->code = $pitem->code;
                    $academicdebts[$id]->name = $pitem->name;
                    $academicdebts[$id]->required = $pitem->required;
                    $academicdebts[$id]->agenum = $pitem->agenum;
                }
            }
        }
        $count = 0; // Количество элементов для отображения
        
        if ( !empty($academicdebts) )
        {
            $data = array();
            foreach ( $academicdebts as $id => $cpassed )
            {
                // Элементы таблицы
                // Сначала дисциплина и ссылка на неё
                $count++;
                $row = array();
                $cell = new html_table_cell();
                $title = '';
                $pitemname = $cpassed->name . " [$cpassed->code]";
                // Для обязательной дисциплины добавим звёздочку в название
                if ( $cpassed->required )
                {
                    $pitemname .= ' *';
                    $title = $this->dof->get_string('discipline_required',$this->im_code());
                }
                $pitemurl = $this->dof->url_im('programmitems', '/view.php',
                        array('pitemid' => $cpassed->programmitemid, 'departmentid' => $this->departmentid));
                $pitemlink = '<a href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>';
                // Название дисциплины
                $cell->text = $mform->createElement('static', "academic[$cpassed->programmitemid]", '', $pitemlink)->toHtml();
//                $cell->attributes = array('align'=>'center');
                $row[] = $cell;
                
                // Оценка за дисциплину
                $cell = new html_table_cell();
                // Сделаем ссылку на историю cpassed'а
                $add = array();
                $add['programmsbcid'] = $this->typeid;
                $add['departmentid'] = $this->departmentid;
                $add['cpassed'] = $cpassed->id;
                if ( empty($cpassed->grade) )
                {
                    $cpassed->grade = '-';
                }
                $grade = '<a href="'.$this->dof->url_im('recordbook','/program.php',$add).'#history" 
                                                                            title="'.$this->dof->get_string('hystori_view', 'recordbook').'">'.$cpassed->grade.'</a>';
                $cell->text = $mform->createElement('static', "academicm[$cpassed->programmitemid]", '', $grade)->toHtml();
//                $cell->attributes = array('align'=>'center');
                $row[] = $cell;
                
                // Семестр в оригинальной программе
                $cell = new html_table_cell();
                $cell->text = $mform->createElement('static', "academicagenum[$cpassed->programmitemid]", '', $cpassed->agenum)->toHtml();
                $row[] = $cell;
                
                // Тип итогового контроля
                $controltypeid = $this->dof->storage('programmitems')->get_field($cpassed->programmitemid, 'controltypeid');
                $controltype = $this->dof->modlib('refbook')->get_st_total_control_name($controltypeid);
                $cell = new html_table_cell();
                $cell->text = $mform->createElement('static', "academiccontroltype[$cpassed->programmitemid]", '', $controltype)->toHtml();
                $row[] = $cell;
                $data[] = $row;
            }
            // Делаем таблицу с элементами формы
            $table = new html_table();
            $table->tablealign = "left";
            $table->cellspacing = 5;
            $head = $this->get_head_descriptions();
            unset($head[4]);
            $table->head  = $head;
            $table->align = array('center','center','center','center');
            $table->data  = $data;
            // добавляем элемент в форму        
        }
        if ( $count > 0 )
        {
            $mform->addElement('html', $this->dof->modlib('widgets')->print_table($table, true));
        } else
        {
            $string = '<div class="noelements">' . $this->dof->get_string('noelements', $this->im_code()) . "</div>";
            $mform->addElement('static', 'no_elements', $string);
        }
    }
    
    private function get_learned_string($pitem)
    {
        $mform =& $this->_form;
        $columns = array();
        $classes = '';
        $pitemurl = $this->dof->url_im('programmitems', '/view.php',
                array('pitemid' => $pitem->id, 'departmentid' => $this->departmentid));
        $title = '';
        $pitemname = $pitem->name . " [$pitem->code]";
        // Для обязательной дисциплины добавим звёздочку в название
        if ( $pitem->required )
        {
            $pitemname .= ' *';
            $classes .= 'required';
            $title = $this->dof->get_string('discipline_required',$this->im_code());
        }
        // Дисциплина
        $columns[] = $mform->createElement('static', "learned[$pitem->id]", '',
                '<a href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>')->toHtml();
        // Оценка
        $add = array();
        $add['programmsbcid'] = $this->typeid;
        $add['departmentid'] = $this->departmentid;
        $add['cpassedid'] = $pitem->cpassedid;
        $grade = '';
        if ( !empty($pitem->grade) )
        {
            $grade = $pitem->grade . ' ';
        }
        $grade .= '[' . $this->dof->workflow('cpassed')->get_name($pitem->cpassedstatus) . ']';
        $columns[] = $mform->createElement('static', "glearned[$pitem->id]", '', 
                '<a href="'.$this->dof->url_im('cpassed','/view.php',$add).'"'
                .'title="'.$this->dof->get_string('cpassedview', $this->im_code()).'">'.$grade.'</a>')->toHtml();
        // Семестр в оригинальной программе
        $columns[] = $mform->createElement('static', "agenum[$pitem->id]", '',
                '<a>' . $pitem->agenum . '</a>')->toHtml();
        // Тип итогового контроля
        $controltypeid = $this->dof->storage('programmitems')->get_field($pitem->id, 'controltypeid');
        $controltype = $this->dof->modlib('refbook')->get_st_total_control_name($controltypeid);
        $columns[] = $mform->createElement('static', "controltype[$pitem->id]", '',
                '<a>' . $controltype . '</a>')->toHtml();
        $columns[] = '&nbsp;';
        $tablerow = new html_table_row($columns);
        $classes .= ' learned';
        $tablerow->attributes['class'] = $classes;
        $tablerow->id = 'pitem_' . $pitem->id;
        return $tablerow;
    }

    private function get_planned_string($pitem, $num)
    {
        $mform =& $this->_form;
        $columns = array();
        $classes = '';
        $pitemurl = $this->dof->url_im('programmitems', '/view.php',
                array('pitemid' => $pitem->id, 'departmentid' => $this->departmentid));
        $title = '';
        $pitemname = $pitem->name . " [$pitem->code]";
        // Для обязательной дисциплины добавим звёздочку в название
        if ( $pitem->required )
        {
            $pitemname .= ' *';
            $classes .= 'required';
            $title = $this->dof->get_string('discipline_required',$this->im_code());
        }
        // Дисциплина
        $cell = new html_table_cell($mform->createElement('static', "planned[$pitem->id]", '',
                '<a class="planned" href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>')->toHtml());
        $datatitle = '';
        if ( $pitem->metaprogrammitemid !== null AND $pitem->metaprogrammitemid !== 0 )
        {// Значит дисциплина создана через метадисциплину
            $metapitem = $this->dof->storage('programmitems')->get($pitem->metaprogrammitemid);
            $datatitle = $this->dof->get_string('edr_okpitemmetaprogrammitemid', 'refbook', $metapitem->name . " [$metapitem->code]", 'modlib');
            $cell->attributes['data-title'] = $datatitle;
        }
        $columns[] = $cell;
        // Оценка
        $columns[] = '<a class="planned">-</a>';
        // Семестр в оригинальной программе
        $columns[] = $mform->createElement('static', "agenum[$pitem->id]", '',
                '<a class="planned">' . $pitem->agenum . '</a>')->toHtml();
        // Тип итогового контроля
        $controltypeid = $this->dof->storage('programmitems')->get_field($pitem->id, 'controltypeid');
        $controltype = $this->dof->modlib('refbook')->get_st_total_control_name($controltypeid);
        $columns[] = $mform->createElement('static', "controltype[$pitem->id]", '',
                '<a class="planned">' . $controltype . '</a>',
                '" class="planned"')->toHtml();
        // Действия
        $submitid = 'aid_excludefromplan_'.$pitem->id;
        $this->submitparams->excludefromplan[] = $submitid;
        $submit = $mform->createElement('submit', "excludefromplan[$pitem->id]", $num,
                'title="'.$this->dof->get_string('excludefromplan',$this->im_code()).
                '" class="icon excludefromplan" id="'.$submitid.'"')->toHtml();
        // Добавим невидимые элементы, без них форма не отправляет данные
        $mform->addElement('html', '<div id="pitemhide_' . $pitem->id . '" class="pitemhide">');
        $mform->addElement('submit', "excludefromplan[$pitem->id]", $num,
                'title="'.$this->dof->get_string('excludefromplan',$this->im_code()).
                '" class="icon excludefromplan"');
        // Проверяем, можно ли дисциплину переносить
        $transferagenums = $this->get_transfer_agenums($num, $pitem->agenum);
        if ( !empty($transferagenums) )
        {
            // Можно перетаскивать
            $classes .= ' draggable';
            // Добавление id для элементов submit и select в submitparams->transfertoagenum:
            $submitidkey = 'aid_transfertoagenum_'.$pitem->id;
            $submitidval = 'aid_transfer_'.$pitem->id;
            $this->submitparams->transfertoagenum[$submitidkey] = $submitidval;
            // Куда переместить дисциплину
            $transfer  = $mform->createElement('select', "transfer[$pitem->id]", '',
                    $transferagenums, 'id="' . $submitidval . '"')->toHtml();
            $columns[] = $submit . $mform->createElement('submit', "transfertoagenum[$pitem->id]", $num,
                    'title="'.$this->dof->get_string('transfertoagenum',$this->im_code()).
                    '" class="icon changeagenum" id="' . $submitidkey . '"')->toHtml() . ' '. $transfer;
            // Добавим невидимые элементы, без них форма не отправляет данные
            $mform->addElement('select', "transfer[$pitem->id]", '',
                    $transferagenums, '');
            $mform->addElement('submit', "transfertoagenum[$pitem->id]", $num,
                    'title="'.$this->dof->get_string('transfertoagenum',$this->im_code()).
                    '" class="icon changeagenum"');
        } else
        {
            $columns[] = $submit;
        }
        $mform->addElement('html', '</div>');
        $tablerow = new html_table_row($columns);
        $classes .= ' planned';
        $tablerow->attributes['class'] = $classes;
        $tablerow->id = 'pitem_' . $pitem->id;
        return $tablerow;
    }

    private function get_planning_string($pitem, $num)
    {
        $mform =& $this->_form;
        $columns = array();
        $classes = '';
        $pitemurl = $this->dof->url_im('programmitems', '/view.php',
                array('pitemid' => $pitem->id, 'departmentid' => $this->departmentid));
        $title = '';
        $pitemname = $pitem->name . " [$pitem->code]";
        // Для обязательной дисциплины добавим звёздочку в название и класс
        $requiredclass = '';
        if ( $pitem->required AND $pitem->agenum == $num )
        {
            $this->countrequired++;
            $classes .= ' required';
            $requiredclass = ' requiredpitem';
            $pitemname .= ' *';
            $title = $this->dof->get_string('discipline_required',$this->im_code());
        }
        $cell = new html_table_cell($mform->createElement('static', "planning[$pitem->id]", '',
                '<a class="planning" href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>')->toHtml());
        $datatitle = '';
        if ( $pitem->metaprogrammitemid !== null AND $pitem->metaprogrammitemid !== 0 )
        {// Значит дисциплина создана через метадисциплину
            $metapitem = $this->dof->storage('programmitems')->get($pitem->metaprogrammitemid);
            $datatitle = $this->dof->get_string('edr_okpitemmetaprogrammitemid', 'refbook', $metapitem->name . " [$metapitem->code]", 'modlib');
            $cell->attributes['data-title'] = $datatitle;
        }
        $columns[] = $cell;
        // Оценка
        $columns[] = '<a class="planning">-</a>';
        // Семестр в оригинальной программе
        $columns[] = $mform->createElement('static', "agenum[$pitem->id]", '',
                '<a class="planning">' . $pitem->agenum . '</a>')->toHtml();
        // Тип итогового контроля
        $controltypeid = $this->dof->storage('programmitems')->get_field($pitem->id, 'controltypeid');
        $controltype = $this->dof->modlib('refbook')->get_st_total_control_name($controltypeid);
        $columns[] = $mform->createElement('static', "controltype[$pitem->id]", '',
                '<a class="planning">' . $controltype . '</a>',
                '" class="planning"')->toHtml();
        // Действия
        $submitid = 'aid_addtoplan_'.$pitem->id;
        $this->submitparams->addtoplan[] = $submitid;
        $class = ' icon addtoplan' . $requiredclass;
        $submit = $mform->createElement('submit', "addtoplan[$pitem->id]", $num,
                'title="'.$this->dof->get_string('addtoplan',$this->im_code()).
                '" class="' . $class . '" id="'.'aid_addtoplan_'.$pitem->id.'"')->toHtml();
        // Добавим невидимые элементы, без них форма не отправляет данные
        $mform->addElement('html', '<div id="pitemhide_' . $pitem->id . '" class="pitemhide">');
        $mform->addElement('submit', "addtoplan[$pitem->id]", $num,
                'title="'.$this->dof->get_string('addtoplan',$this->im_code()).
                '" class=" icon addtoplan"');

        $transferagenums = $this->get_transfer_agenums($num, $pitem->agenum);
        if ( !empty($transferagenums) )
        {
            // Можно перетаскивать
           $classes .= ' draggable';
            // Добавление id для элементов submit и select в submitparams->transfertoagenum:
            $submitidkey = 'aid_addtoagenum_'.$pitem->id;
            $submitidval = 'aid_transfer_'.$pitem->id;
            $this->submitparams->addtoagenum[$submitidkey] = $submitidval;
            // Куда переместить дисциплину
            $transfer  = $mform->createElement('select', "transfer[$pitem->id]", '',
                    $transferagenums, 'id="' . $submitidval . '"')->toHtml();
            $columns[] = $submit . $mform->createElement('submit', "addtoagenum[$pitem->id]", $num,
                    'title="'.$this->dof->get_string('addtoagenum',$this->im_code()).
                    '" class="icon changeagenum" id="' . $submitidkey . '"')->toHtml() . ' '. $transfer;
            // Добавим невидимые элементы, без них форма не отправляет данные
            $mform->addElement('select', "transfer[$pitem->id]", '',
                    $transferagenums, '');
            $mform->addElement('submit', "addtoagenum[$pitem->id]", '',
                    'title="'.$this->dof->get_string('addtoagenum',$this->im_code()).
                    '" class="icon changeagenum"');
        } else
        {
            $columns[] = $submit;
        }
        $mform->addElement('html', '</div>');
        $tablerow = new html_table_row($columns);
        $classes .= ' planning';
        $tablerow->attributes['class'] = $classes;
        $tablerow->id = 'pitem_' . $pitem->id;
        return $tablerow;
    }
    
    /**
     * Получить общее количество запланированных и изученных часов в параллели/-ях
     * 
     * @param mixed|int|array $agenums - номер одной параллели или нескольких
     * @return object часы в формате:
        $hours->hours,
        $hours->hourstheory,
        $hours->hourspractice,
        $hours->hoursweek,
        $hours->hourslab,
        $hours->hoursind,
        $hours->hourscontrol,
        $hours->hoursclassroom,
        $hours->maxcredit,
     * 
     */
    public function get_total_hours($agenums)
    {
        // Считаем общее количество часов запланированных и изученных дисциплин
        if ( !is_array($agenums) )
        {
            $anums = array($agenums);
        } else
        {
            $anums = $agenums;
        }
        $ids = array();
        foreach ( $anums as $agenum )
        {
            if ( !empty($this->lp->learningplan[$agenum]->learned) )
            {
                $ids = array_merge($ids, array_keys($this->lp->learningplan[$agenum]->learned));
            }
            if ( !empty($this->lp->learningplan[$agenum]->planned) )
            {
                $ids = array_merge($ids, array_keys($this->lp->learningplan[$agenum]->planned));
            }
        }
        $hours = $this->dof->storage('programmitems')->get_hours_sum($ids);
        return $hours;
    }
    
    /**
     * Добавить таблицу с суммой запланированных и пройденных часов за семестр /
     * учебный год
     * 
     * @param type $agenums
     */
    public function show_hours($agenums, $visible)
    {
        // Показываем часы за учебный год?
        $academicyear = false;
        if ( !is_array($agenums) )
        {
            $tableid = 'hours_agenum_' . $agenums;
        } else
        {
            if (count($agenums) > 1)
            {
                $tableid = 'hours_agenums_' . implode("_", $agenums);
            } else
            {
                $tableid = 'hours_agenums_' . $agenums[0];
            }
            $academicyear = true;
        }
        $mform =& $this->_form;
        $table = new html_table();
        $table->id = $tableid;
        if ( !$visible )
        {
            $table->attributes['class'] = 'generaltable havenoelements';
        }
        $table->tablealign = "left";
        $table->width = '100%';
        $table->head       = array('Часов всего',   'Часов лекций',       'Часов практики',
                                   'Часов в неделю','Часов лабораторных', 'СРС',
                                   'Контроль',      'Часов аудиторных',   'ЗЕТ');
        $colclasses = array('hours',         'hourstheory',        'hourspractice', 
                            'hoursweek',     'hourslab',           'hoursind',
                            'hourscontrol',  'hoursclassroom',     'maxcredit');
        $table->head = array();
        $table->colclasses = $colclasses;
        $table->align = array('center','center','center',
                              'center','center','center',
                              'center','center','center');
        // Количество часов по семестрам/учебным годам
        $hours = $this->get_total_hours($agenums);
        
        // Проверим, работают ли правила для уровня образования
        // Если их нет, то пропускаем этот этап, иначе назначаем классы
        $checkedrules = $this->check_rules_hours($hours, $academicyear);
        $row = array();
        foreach ( $colclasses as $class )
        {
            $table->head[] = $this->dof->get_string($class, $this->im_code());
            $row[] = $checkedrules[$class];
        }

        $table->data = array($row);
        $mform->addElement('html', $this->dof->modlib('widgets')->print_table($table, true));
    }
    
    /**
     * Получить количество параллелей в учебном году согласно уровню образования
     * (коды уровней образования описаниы в modlib/refbook/standards/edulevel.php)
     * 
     * @param int $edulevel - код уровня образования (1-10)
     * @return int
     */
    public function get_edulevel_agenums($edulevel)
    {
        $default = 2;
        // 'modlib/refbook/standards/edulevel.php'
        switch ( $edulevel )
        {
            case 7:
                return 2;
            default:
                break;
        }
        return $default;
    }
    
    /**
     * Получить список (id) дисциплин, отсортированных по порядку для указанной
     * параллели или для всех
     * 
     * @param int $agenum
     * @return array - структура:
     * array(
     *   'параллель' => array(
     *                    // learned
     *                    $pitemid1,
     *                    $pitemid2,
     *                    // planned
     *                    $pitemid3,
     *                    // planning
     *                    $pitemid4, ...
     *                  ),
     * )
     */
    public function get_sorted_pitems($agenum = null)
    {
        $sorted = array();
        for ( $num = 0; $num <= $this->lp->agenums; $num++ )
        {
            $sorted[$num] = array();
            if ( !empty($this->lp->learningplan[$num]->learned) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->learned as $pid => $pitem )
                {
                    $sorted[$num][] = $pid;
                }
            }
            // Запланированные
            if ( !empty($this->lp->learningplan[$num]->planned) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->planned as $pid => $pitem )
                {
                    $sorted[$num][] = $pid;
                }
            }
            // Планируемые
            if ( !empty($this->lp->learningplan[$num]->planning) )
            { // Дисциплины имеются
                foreach ( $this->lp->learningplan[$num]->planning as $pid => $pitem )
                {
                    $sorted[$num][] = $pid;
                }
            }
        }
        // Нам передали параллель
        if ( $agenum != null AND is_int_string($agenum) )
        {
            if ( $agenum > 0 AND $agenum <= $this->lp->agenums )
            {
                return $sorted[$agenum];
            } else
            {
                return false;
            }
        }
        return $sorted;
    }
    
    /**
     * Проверить правила для часов и ЗЕТ 
     * 
     * @param object $hours - часы и ЗЕТ в формате:
        $hours->hours,
        $hours->hourstheory,
        $hours->hourspractice,
        $hours->hoursweek,
        $hours->hourslab,
        $hours->hoursind,
        $hours->hourscontrol,
        $hours->hoursclassroom,
        $hours->maxcredit,
     * @param bool $academicyear - проверять правила для учебного года? (по-умолчанию для параллели)
     * @param bool $returncheckedonly - возвращать только проверенные поля
     * @return array - содержит название столбца и ячейку с классами/текстом/подсказкой:
     * 'hours' => html_table_cell(), ...
     *  "colclasses" таблицы часов и ЗЕТ
     */
    public function check_rules_hours($hours, $academicyear = false, $returncheckedonly = false)
    {
        
        // Названия CSS-классов для визуализации правильности выполнения правил
        $ruleok = ' ruleok';
        $rulefail = ' rulefail';
        $scope = 'agenum';
        if ( $academicyear )
        {
            $scope = 'academicyear';
        }
        $rules = $this->dof->modlib('refbook')->get_edulevel_rules($this->lp->programm->edulevel, $this->departmentid);
        // В этом массиве хранится результат
        $checkedrules = array();
        if ( empty($rules) )
        {
            foreach ( $hours as $fieldname => $value )
            {
                $checkedrules[$fieldname] = $value;
            }
            return $checkedrules;
        }
        $period = array();
        if ( $academicyear )
        {
            $period = $rules->academicyear;
        } else
        {
            $period = $rules->agenum;
        }
        foreach ( $hours as $fieldname => $value )
        {
            // Ячейка с часами
            $td = new html_table_cell($value);
            if ( isset($period->{$fieldname}) AND !empty($period->{$fieldname}) )
            {// Если такое правило есть
                if ( $value <= $period->{$fieldname} )
                {
                    $td->attributes['class'] = $ruleok;
                    $td->attributes['data-title'] = $rules->{'ok' . $scope . $fieldname};
                    $checkedrules[$fieldname] = $td;
                } else
                {
                    $td->attributes['class'] = $rulefail;
                    $td->attributes['data-title'] = $rules->{$scope . $fieldname};
                    $checkedrules[$fieldname] = $td;
                }
            } else
            {// Если правила нет
                if ( !$returncheckedonly )
                {// Возвращаем все поля
                    $checkedrules[$fieldname] = $td;
                }
            }
        }
        
        return $checkedrules;
        
    }

}

/** Форма просмотра подписок на дисциплины учебного плана
 * 
 */
class dof_im_learningplan_subscribe_form extends dof_modlib_widgets_form
{
    /**
     * @var addvars - массив GET-параметров для генерации ссылок
     */
    protected $addvars;

    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var object - объект индивидуального учебного плана
     */
    protected $lp;
    
    /**
     * @var int -id подразделения в таблице departments, в котором происходит работа
     */
    protected $departmentid = 0;
    
    /**
     * @var string - тип индивидуального учебного плана (ИП), может быть 'programmsbc' или 'agroup' 
     */
    protected $type;
    
    /**
     * @var int - id из таблицы programmsbcs или agroups
     */
    protected $typeid;

    protected function im_code()
    {
        return 'learningplan';
    }
    
    protected function storage_code()
    {
        return 'learningplan';
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
        $this->dof          = $this->_customdata->dof;
        $this->type         = $this->_customdata->type;
        $this->typeid       = $this->_customdata->typeid;
        $this->departmentid = $this->_customdata->departmentid;
        // Массив для передачи GET-параметров
        global $addvars;
        if ( is_array($addvars) )
        {
            $this->addvars = $addvars;
        } else
        {
            $this->addvars = array();
        }
        $this->addvars['departmentid'] = $this->departmentid;

        $mform =& $this->_form;
        $this->lp = $this->dof->storage('learningplan')->create_learningplan($this->type, $this->typeid);
        // устанавливаем все скрытые поля 
        $mform->addElement('hidden','departmentid', $this->departmentid);
        $mform->setType('departmentid', PARAM_INT);
        // Отображаем основную информацию
        $this->show_general_info();
        // Если группа или подписка уже в статусе "завершено", отобразим уведомление и кнопку "Назад"
        if ( $status = $this->dof->storage($this->type. 's')->get_field($this->typeid, 'status') )
        {
            if ( $status == 'completed' )
            {
                if ( $this->type == 'agroup' )
                {
                    $mform->addElement('static', 'notice', '', $this->dof->get_string('statuscompletedagroup',$this->im_code()));
                } else if ( $this->type == 'programmsbc')
                {
                    $mform->addElement('static', 'notice', '', $this->dof->get_string('statuscompletedpsbc',$this->im_code()));
                }
                $buttonarray=array();
                $buttonarray[] = &$mform->createElement('submit', 'cancel', $this->dof->modlib('ig')->igs('back'));
                $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
                $mform->closeHeaderBefore('buttonar');
                return;
            }
        }
        // Отображаем информацию по семестрам
        $this->show_unsigned_pitems($this->lp->agenum);
        $mform->closeHeaderBefore('buttonar');
        // кнопки "сохранить" и "отмена"
        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'subscribe', $this->dof->get_string('subscribedisciplines',$this->im_code()));
        $buttonarray[] = &$mform->createElement('submit', 'cancel', $this->dof->modlib('ig')->igs('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', 'buttonar', array(' '), true);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Добавление дополнительльных полей формы и установка значений по умолчанию
     * после загрузки данных в форму (если происходит редактирование)
     * 
     * @return void
     */
    public function definition_after_data()
    {
    }
    
    /** Проверка данных формы
     * @param array $data - данные, пришедшие из формы
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data,$files)
    {
        $mform =& $this->_form;
        $errors = array();

        // убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /** Обработать пришедшие из формы данные и сделать redirect в случае отмены
     *
     * @return bool - результат операции
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $this->is_validated() AND $formdata = $this->get_data() )
        {// Получили данные формы';
            if ( isset($formdata->cancel) OR isset($formdata->buttonar['cancel']) )
            { // Ввод данных отменен - возвращаем на страницу назад
                // Странная штука - если вручную сделать 'cancel'-кнопку (в группе),
                // то is_cancelled() не работает, из-за того, что она в группе, и
                // из-за этого optional_param() не видит её...
                redirect($this->dof->url_im($this->type.'s','/list.php',$this->addvars));
            }
            
            if ( isset($formdata->subscribe) OR isset($formdata->buttonar['subscribe']) )
            { // Подписываем студента/группу на потоки (создаём подписки на дисциплины)
                $options = array();
                $optvalues = array('appointmentid', 'cstreamid');
                $unsigned = $this->dof->storage('learningplan')->get_unsigned_pitems($this->type, $this->typeid, $this->lp->agenum);
                foreach ( $unsigned as $pid => $pitem )
                {
                    $options[$pid] = new stdClass();
                    foreach ( $optvalues as $value )
                    {
                        if ( !empty($formdata->$value) )
                        {
                            if ( isset($formdata->$value[$pid]) )
                            {
                                $options[$pid]->$value = $formdata->$value[$pid];
                            }
                        }
                    }
                }
                $pitems = $this->dof->storage('learningplan')->get_subscribe_current_agenum_pitems($this->type, $this->typeid, $options);
                if ( !empty($pitems) )
                {
                    $this->dof->storage('cpassed')->sign_pitems_current_agenum($this->type, $this->typeid, $pitems);
                }
                // Перенаправим обратно
                $adds = array('type'             => $this->type,
                               "{$this->type}id" => $this->typeid);
                redirect($this->dof->url_im('learningplan','/index.php',$this->addvars + $adds));
            }


            return true;
        }
        return false;
    }
    
    /** Получить описания столбцов к записям
     */
    private function get_head_descriptions()
    {
        return array(
            $this->dof->get_string('discipline', $this->im_code()),
            $this->dof->get_string('mark', $this->im_code()),
            $this->dof->get_string('agenum', $this->im_code()),
            $this->dof->get_string('controltype', $this->im_code()),
            $this->dof->get_string('actions', $this->im_code()),
        );
    }
        
    /** Отобразить дисциплины из плана, на которые студент не подписан в указанном семестре.
     * 
     * @param int $num - номер семестра 
     */
    private function show_unsigned_pitems($num)
    {
        $mform =& $this->_form;
        // Сначала определим текущую параллель и выставим необходимые флаги для отображения
        $learned = false;
        $count = 0; // Количество элементов для отображения
        $data  = array(); // Данные элементов
        $header = $this->dof->get_string('subscribes',$this->im_code(), $num);

        $unsigned = $this->dof->storage('learningplan')->get_unsigned_pitems($this->type, $this->typeid, $num);
        if ( empty($unsigned) )
        {
            $string = '<div class="noelements">' . $this->dof->get_string('noelements', $this->im_code()) . "</div>";
            $mform->addElement('static', 'no_elements', $string);
            return;
        }

        $mform->addElement('header', 'unsigned', $header);
        // Отображаем неподписанные дисциплины
        foreach ( $unsigned as $pid => $pitem )
        {
            $count++;
//            $row = array();
            $pitemurl = $this->dof->url_im('programmitems', '/view.php',
                    array('pitemid' => $pid, 'departmentid' => $this->departmentid));
            $title = '';
            $pitemname = $pitem->name . " [$pitem->code]";
            // Для обязательной дисциплины добавим звёздочку в название
            if ( $pitem->required )
            {
                $pitemname .= ' *';
                $title = $this->dof->get_string('discipline_required',$this->im_code());
            }
            $group = array();
            // Дисциплина
            $group[] = $mform->createElement('static', "unsigned[$pid]", '',
                    '<a class="planned" href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>');
            $teachers = $this->get_possible_teachers_select($pid);
//            $group[] = $mform->createElement('select', "transfer[$pid]", '123',
//                            $teachers, '');
//            $mform->addGroup($group, null, '', array(' '), true);
            $pitemdescription = '<a class="planned" href="' . $pitemurl . "\" title=\"{$title}\">" . $pitemname . '</a>';
            $mform->addElement('static', "pitem[$pid]", '', $pitemdescription);
            
            $mform->addElement('select', "appointmentid[$pid]", $this->dof->get_string('teacher',$this->im_code()),
                            $teachers, '');
            
            $mform->addElement('select', "cstreamid[$pid]", $this->dof->get_string('cstream',$this->im_code()),
                            $this->get_possible_cstreams_select($pid), '');
            
        }
    }
    
    /** Выводит список доступных учителей для дисциплины в формате:
     *  array (appointmentid => "teachername [enumber]", ... )
     * 
     * @param int $pitemid - programmitemid из таблицы cstreams
     * @return array - список доступных учителей
     */
    private function get_possible_teachers($pitemid)
    {
        // Получаем всех учителей
        // с проверкой доступа к чтению дисциплины, активных табельных номеров
        // Найдём pitemid через cstreamid
        $teachers = $this->dof->storage('teachers')->get_teachers_for_pitem($pitemid);
        $appointmentids = array();
        foreach ( $teachers as $teacher )
        {
            $appointmentids[] = $teacher->appointmentid;
        }
        $persons = array();
        if ( !empty($appointmentids) )
        {// получаем список пользователей по списку учителей
            foreach ( $appointmentids as $appid )
            {
                $persons[] = $this->dof->storage('appointments')->get_person_by_appointment($appid, true);
            }
            
            // Смотрим, есть ли у них активные табельные номера
            $statuses = $this->dof->workflow('appointments')->get_meta_list('active');
            foreach ($persons as $id => $person)
            { // Перебираем статусы табельных номеров
                $appstatus = $this->dof->storage('appointments')->get_field(
                        array('enumber'=>$person->enumber),'status');
                if ( !array_key_exists($appstatus, $statuses) )
                { // Если табельный номер не активен
                    unset($persons[$id]);
                }
            }
        }
        return $persons;
    }
    
    private function get_possible_teachers_select($pitemid)
    {
        $persons = $this->get_possible_teachers($pitemid);
        $firstelement = array( 0 => '--- '.$this->dof->get_string('notspecified',$this->im_code()).' ---' );
        if ( !empty($persons) )
        {
            // Преобразовываем список к пригодному для элемента select виду
            $rez = $this->dof_get_select_values($persons, false,
                        'appointmentid', array('sortname','enumber'));
            asort($rez);
            $rez = $firstelement + $rez;
        } else
        {
            return $firstelement;
        }
        
        // Оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $filtered = $this->dof->storage('acl')->get_acl_filtered_list($rez, $permissions);

        return $filtered;
    }

    /** Получить список доступных потоков для дисциплины
     * 
     * @param int $pitemid
     * @return string
     */
    private function get_possible_cstreams_select($pitemid)
    {
        // Поток считается подходящим, если ageid, agenum и programmitemid совпадают, а metastatus = 'actual'
        $actualstatus = $this->dof->workflow('cstreams')->get_meta_list('actual');
        // Достаём ageid из актуальной истории обучения
        if ( $this->type == 'programmsbc' )
        {
            $actual = $this->dof->storage('learninghistory')->get_actual_learning_data($this->typeid);
        } else
        {
            $actual = $this->dof->storage('agrouphistory')->get_actual_learning_data($this->typeid);
        }
        $ageid  = 0;
        if ( !empty($actual) )
        {
            $ageid = $actual->ageid;
        }
        $conds = array('ageid' => $ageid,
                       'programmitemid' => $pitemid,
                       'status' => array_keys($actualstatus));
        $cstreams = $this->dof->storage('cstreams')->get_records($conds);
        $firstelement = array( 0 => '--- '.$this->dof->get_string('createnew',$this->im_code()).' ---' );
        if ( !empty($cstreams) )
        {
            // Преобразовываем список к пригодному для элемента select виду
            $rez = $this->dof_get_select_values($cstreams, false,
                        'id', array('name'));
            asort($rez);
            $rez = $firstelement + $rez;
        } else
        {
            return $firstelement;
        }
        
        // Оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'cstreams', 'code'=>'use'));
        $filtered = $this->dof->storage('acl')->get_acl_filtered_list($rez, $permissions);

        return $filtered;
    }

    /** Показать общую информацию о типе учебного плана, программе обучения и ссылки
     *  на студента / группу
     */
    private function show_general_info()
    {
        $mform =& $this->_form;
        $mform->addElement('header', 'generalinfo', $this->dof->get_string('generalinfo',$this->im_code()));
        $typeinfo = '';
        $typelink = '';
        // Отображение студента или группы в зависимости от типа учебного плана
        if ( $this->type == 'programmsbc' )
        {
            $typeinfo = 'student';
            $typeurl = $this->dof->url_im('programmsbcs', '/view.php', $this->addvars + array('programmsbcid' => $this->typeid));
            $typelink = '<a href="' . $typeurl . '">' . $this->lp->name . '</a>';
        } else if ( $this->type == 'agroup' )
        {
            $typeinfo = 'group';
            $typeurl = $this->dof->url_im('agroups', '/view.php', $this->addvars + array('agroupid' => $this->typeid));
            $typelink = '<a href="' . $typeurl . '">' . $this->lp->name . '</a>';
        }
        
        $mform->addElement('static', 'typeinfo', $this->dof->get_string($typeinfo, $this->im_code()).':', $typelink);
        // Отображение программы
        $programmurl = $this->dof->url_im('programms', '/view.php', $this->addvars + array('programmid' => $this->lp->programm->id));
        $programmlink = '<a href="' . $programmurl .'">'
                . "{$this->lp->programm->name} [{$this->lp->programm->code}]</a>";
        $mform->addElement('static', 'programmname', $this->dof->get_string('programm',$this->im_code()).':', $programmlink);
        $agenumtext = $this->lp->agenum;
        if ( empty($agenumtext) )
        {
            $agenumtext = $this->dof->get_string('edunotstarted',$this->im_code());
        }
        $mform->addElement('static', 'agenum', $this->dof->get_string('currentagenum',$this->im_code()).':', $agenumtext);
        $mform->addElement('static', 'agenums', $this->dof->get_string('agenums',$this->im_code()).':', $this->lp->agenums);
        if ( !empty($this->lp->age) )
        {
            $mform->addElement('static', 'ageid', $this->dof->get_string('ageid',$this->im_code()).':', $this->lp->age->name);
        } else
        {
            $mform->addElement('static', 'ageid', $this->dof->get_string('ageid',$this->im_code()).':', 
                    $this->dof->get_string('noageid',$this->im_code()));
        }
//        // Тип обучения
//        $edutype = 'individual';
//        $mform->addElement('static', 'edutype', $this->dof->get_string('edutype',$this->im_code()).':', $edutype); 
    }
    
}
?>