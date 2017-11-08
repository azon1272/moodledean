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
global $DOF;
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_cpassed_edit_form extends dof_modlib_widgets_form
{
    private $cpassed;
    /**
     * @var dof_control 
     */
    protected $dof;
    
    /** Объявление формы
     * 
     * @return null
     */
    function definition()
    {// делаем глобальные переменные видимыми

        $this->cpassed = $this->_customdata->cpassed;
        $this->dof     = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','cpassedid', $this->cpassed->id);
        $mform->setType('cpassedid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // устанавливаем значения как hidden-поля, чтобы потом забрать из них
        // значения при помощи definition_after_data
        $mform->addElement('hidden','studentid', 0);
        $mform->setType('studentid', PARAM_INT);
        $mform->addElement('hidden','programmsbcid', 0);
        $mform->setType('programmsbcid', PARAM_INT);
        $mform->addElement('hidden','programmitemid', 0);
        $mform->setType('programmitemid', PARAM_INT);
        $mform->addElement('hidden','cstreamid', 0);
        $mform->setType('cstreamid', PARAM_INT);
        $mform->addElement('hidden','ageid', 0);
        $mform->setType('ageid', PARAM_INT);
        $mform->addElement('hidden','agroupid', 0);
        $mform->setType('agroupid', PARAM_INT);
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->cpassed->id));
        // получаем список всех элементов для hierselect
        $students = $this->get_list_students();
        foreach ( $agelist as $ageid=>$age )
        {// составляем список разрешенных периодов
            // создаем иерархию пятого уровня
            $type[$contrid][$progsbcid][$pitemid][$csid][$ageid] = $this->get_type_cpassed($progsbcid);
        }
        $options = $this->get_select_options();
        // выравниваем строки по высоте
        $mform->addElement('html', '<div style=" line-height: 1.9; ">');
        // добавляем новый элемент выбора зависимых вариантов форму
        $myselect =& $mform->addElement('hierselect', 'cpdata', 
                                        $this->dof->get_string('student',      'cpassed').':<br/>'.
                                        $this->dof->get_string('programm',     'cpassed').':<br/>'.
                                        $this->dof->get_string('subject',      'cpassed').':<br/>'.
                                        $this->dof->get_string('cstream',      'cpassed').':<br/>'.
                                        $this->dof->get_string('age',          'cpassed').':<br/>'.
                                        $this->dof->get_string('type_cpassed', 'cpassed').':<br/>',
                                        null,'<br/>');
        // закрываем тег выравнивания строк
        $mform->addElement('html', '</div>');
        // устанавливаем для него варианты ответа
        // (значения по умолчанию устанавливаются в методе definition_after_data)
        $myselect->setOptions(array($options->students, $options->programms, $options->subjects,
        $options->cstreams, $options->ages,$options->type));
        if ( $this->cpassed->id )
        {// выведем поле "статус" если форма редактируется
            $mform->addElement('static', 'status', $this->dof->get_string('status','cpassed').':', 
                                    $this->get_status_name($this->cpassed->status));
        }
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');        
        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','cpassed'));
    }
    
    /** Установка значений по умолчанию для сложных элементов
     * 
     * @return 
     */
    function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // получаем id ученика
        $studentid      = $mform->getElementValue('studentid');
        // получаем id подписки на программу
        $programmsbcid  = $mform->getElementValue('programmsbcid');
        // получаем предмет
        $programmitemid = $mform->getElementValue('programmitemid');
        // получаем id потока 
        $cstreamid      = $mform->getElementValue('cstreamid');
        // получаем период
        $ageid          = $mform->getElementValue('ageid');
        // получаем запись контракта
        if ( $progsbc = $this->dof->storage('programmsbcs')->get($programmsbcid) )
        {// если подписка найдена - извлечем из нее номер контракта
            $contractid = $progsbc->contractid;
        }else
        {// если не нашли подписку, то ничего не покажем
            $contractid = 0;
        }
        $agroupid       = $mform->getElementValue('agroupid');
        // устанавливаем значения по умолчанию для всех полей элемента hierselect
        $mform->setDefault('cpdata', array($contractid, $programmsbcid, $programmitemid, $cstreamid, $ageid,$agroupid));
    }
    
    /** Проверка данных на стороне сервера
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        
        // проверим существование учителя
        if ( ! isset($data['cpdata'][0]) OR ! $contract = $this->dof->storage('contracts')->get($data['cpdata'][0]) )
        {// такой контракт не зарегестрировван
            $errors['cpdata'] = $this->dof->get_string('err_required_multi','cpassed');
        }elseif( ! $this->dof->storage('persons')->is_exists($contract->studentid) )
        {// контракт существует, проверим существует ли ученик
            $errors['cpdata'] = $this->dof->get_string('err_student_notexists','cpassed');
        }
        
        // проверим существование периода
        if ( ! isset($data['cpdata'][1]) OR ! $progsbc = $this->dof->storage('programmsbcs')->get($data['cpdata'][1]) )
        {// подписка на программу не существует
            $errors['cpdata'] = $this->dof->get_string('err_required_multi','cpassed');
        }elseif( $progsbc->contractid != $data['cpdata'][0] )
        {// если подписка существует, проверим соответствие контракта подписке
            $errors['cpdata'] = $this->dof->get_string('err_required_multi','cpassed');
        }
        
        // проверим существование предмета
        if ( ! isset($data['cpdata'][2]) OR ! $subject = $this->dof->storage('programmitems')->get($data['cpdata'][2]) )
        {// предмет не существует
            $errors['cpdata'] = $this->dof->get_string('err_required_multi','cpassed');
        }elseif ( $subject->programmid != $progsbc->programmid )
        {// если предмет существует, то проверим соответствие его с программой
            $errors['cpdata'] = $this->dof->get_string('err_required_multi','cpassed');
        }
        
        // проверим существование потока, если он указан
        if ( $data['cpdata'][3] AND ! $cstream = $this->dof->storage('cstreams')->get($data['cpdata'][3]) )
        {// поток не существует, сообщим об этом
            $errors['cpdata'] = $this->dof->get_string('cstream_not_exists','cpassed');
        }elseif ( $data['cpdata'][3] )
        {// если поток существует - проверим правильность его привязки к программе
            if ( ! $pitem = $this->dof->storage('programmitems')->get($cstream->programmitemid) )
            {// если не найден элемент учебной программы - это ошибка
                $errors['cpdata'] = $this->dof->get_string('wrong_cstream_and_programm','cpassed');
            }elseif ( $pitem->programmid != $progsbc->programmid )
            {// если элемент программы найден, про принадлежит к другому потоку - это ошибка
                $errors['cpdata'] = $this->dof->get_string('wrong_cstream_and_programm','cpassed');
            }
        }
        
        // проверим существование периода
        if ( ! isset($data['cpdata'][4]) OR ! $this->dof->storage('ages')->is_exists($data['cpdata'][4]) )
        {// периода не существует
            $errors['cpdata'] = $this->dof->get_string('err_required_multi','cpassed');
        }elseif( $data['cpdata'][3] )
        {// если поток выбран и существует, то проверим соответствие id потока с id периода
            if ( $cstream->ageid != $data['cpdata'][4] )
            {// если период не соответствеет выбранному потоку, то не даем сохранить данные
                // и сообщаем об ошибке
                $errors['cpdata'] = $this->dof->get_string('wrong_cstream_and_age','cpassed');
            }
        }
        // если ошибки есть - то пользователь вернется на страницу редактирования и увидит их
        return $errors;
    }
    
    /** Получить весь список опций для элемента hierselect
     * @todo переделать эту функцию в рекурсивную процедуру, чтобы сократить объем кода
     * @return stdClass object объект, содержащий данные для элемента hierselect
     */
    function get_select_options()
    {
        $result = new stdClass();
        // получаем список всех учеников
       
        // создаем массив для учебных программ
        $programms = array();
        $cstreams  = array();
        foreach ( $students as $contrid=>$student )
        {// для каждого ученика определяем список программ на которые он подписан
            $plist = $this->get_student_programs($contrid);
            // создадим иерархию второго уровня
            $programms[$contrid] = $plist;
            foreach ( $plist as $progsbcid=>$programm )
            {// составляем список разрешенных предметов
                $subjlist = $this->get_list_subjects($progsbcid);
                // создаем иерархию третьего уровня
                $subjects[$contrid][$progsbcid] = $subjlist;
                foreach ( $subjlist as $pitemid=>$subject )
                {// составляем список разрешенных учебных потоков
                    $cstreamlist = $this->get_list_cstreams($pitemid);
                    // создаем иерархию четвертого уровня
                    $cstreams[$contrid][$progsbcid][$pitemid] = $cstreamlist;
                    foreach ( $cstreamlist as $csid=>$cstream )
                    {// составляем список разрешенных периодов
                        $agelist = $this->get_list_ages($progsbcid, $csid);
                        // создаем иерархию пятого уровня
                        $ages[$contrid][$progsbcid][$pitemid][$csid] = $agelist;
                        
                    }
                }
            }
        }
        // записываем в результурующий объект все что мы получили
        $result->students  = $students;
        $result->programms = $programms;
        $result->subjects  = $subjects;
        $result->cstreams  = $cstreams;
        $result->ages      = $ages;
        $result->type      = $type;
        //print_object($result);
        // возвращаем все составленные массивы в упорядоченном виде
        return $result;
    }
    
    /** Получить список программ, на которые подписан ученик
     * @todo добавить в зависимости плагин контрактов, программ, и подписок на программы
     * @return array массив вариантов для элемента hierselect
     * @param int $contractid - id контракта ученика для которого получается список программ. Таблица contracts.
     */
    private function get_student_programs($contractid)
    {
        $result = array();
        // получаем id ученика
        $studentid = $this->dof->storage('contracts')->get_field($contractid, 'studentid');
        if ( ! $studentid )
        {// для нулевого элемента покажем простто пункт "выбрать"
            return $result;
        }
        // извлекаем все контракты ученика
        if ( $contracts = $this->dof->storage('contracts')->
                get_records(array('studentid'=>$studentid), '', 'id, num') )
        {// удалось извлечь контракты
            foreach ( $contracts as $cntrid=>$contract )
            {// перебираем все контракты и извлекаем для каждого учебную программу
                // на которую подписан ученик
                // @todo контракт выбираем только пока 1
                if ( $programmsbcs = $this->dof->storage('programmsbcs')->
                        get_records(array('contractid'=>$contractid), '', 'id, programmid') )
                {// получили id программ - теперь получим их названия
                    foreach ( $programmsbcs as $psid=>$programmsbc )
                    {// и запишем из в результирующий массив
                        if ( $progname = $this->dof->storage('programms')->
                                 get_field($programmsbc->programmid, 'name') )
                        {// если название программы корректно извлеклось - вставим его
                         // в результат
                            $result[$psid] = $progname.' ['.$this->dof->storage('programms')->
                                 get_field($programmsbc->programmid, 'code').']';
                        }
                    }
                }
            }
        }
        // возвращаем то, что набрали
        return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---') + $result;
    }
    
    /** Получить список учебных потоков, разрешенных данной учебной программой
     * @return array массив допустимых учебных потоков
     * @param object $progitemid - id изучаемого предмета в таблице programmitems
     */
    private function get_list_cstreams($progitemid)
    {
        $result = array();
        // получаем все учебные потоки для текущего периода и подписки
        if ( $cstreams = $this->dof->storage('cstreams')->get_records(array('programmitemid'=>$progitemid)) )
        {// если получили потоки, то приведем их к виду, нужному в форме
            foreach ( $cstreams as $id=>$cstream )
            {
                // формируем пункт меню
                $result[$id] = $cstream->name;
            }
        }
        return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---') + $result;
    }
    
    /** Получить список периодов для элемента hierselect
     * 
     * @return array массив пригодный для составления html-элемента select
     * @param int $progsbcid - id подписки на учебную программу в таблице programmsbcs
     * @param int $cstreamid - id потока в таблице cstreams
     */
    private function get_list_ages($progsbcid, $cstreamid=0)
    {
        // объявляем итоговый массив
        $result = array();
        // получаем все данные по подписке
        if ( ! $progsbc = $this->dof->storage('programmsbcs')->get($progsbcid) )
        {// не получили подписку на программу  - не имеет смысла выполнять
            // действия дальше, вернем пустой массив
            $result[0] = '--- '.$this->dof->get_string('to_select','cpassed').' ---';
            return $result;
        }
        if ( ! $programm = $this->dof->storage('programms')->get($progsbc->programmid) )
        {// не получили учебную программу  - не имеет смысла выполнять
            // действия дальше, вернем пустой массив
            $result[0] = '--- '.$this->dof->get_string('to_select','cpassed').' ---';
            return $result;
        }
        if ( ! $cstreamid )
        {// если поток не указан - то можно выбрать период
            // добавляем все допустимые варианты периодов:
            // для этого выясним минимальный и максимальный ageid
            $history = $this->dof->storage('learninghistory')->get_first_learning_data($progsbc->id);
            $maxageid = $this->dof->storage('ages')->get_next_ageid($history->ageid, $programm->agenums);
            // после того как выяснили - извлечем из таблицы все подходящие по критериям записи
            if ( $ageslist = $this->dof->storage('ages')->get_ages_by_idrange($minageid, $maxageid) )
            {// если мы получили список периодов - сформируем из него массив
                // добавляем первый вариант со словом "выбрать"
                $result[0] = '--- '.$this->dof->get_string('to_select','cpassed').' ---';
                foreach ( $ageslist as $age )
                {// перебираем все периоды и составляем массив для select'а
                    $result[$age->id] = $age->name;
                }
            }else
            {// если мы не получили ни одного периода - сообщим об этом
                $result[0] = '('.$this->dof->get_string('not_found', 'cpassed').')';
            }
        }else
        {// если поток указан - то период указывается единственным образом (берется из потока)
            if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
            {// поток не найден - нет смысла выполнять дальнейшие действия. Сообщим об ошибке.
                $result[0] = '('.$this->dof->get_string('not_found', 'cpassed').')';
                return $result;
            }
            if ( $age = $this->dof->storage('ages')->get($cstream->ageid) )
            {// если период есть - у нас должен быть единственный вариант выбора
                $result[$age->id] = $age->name;
            }else
            {// если периода нет - сообщим об этом
                $result[0] = '('.$this->dof->get_string('not_found', 'cpassed').')';
            }
        }
        return $result;
    }
    
    /** Получить список предметов для элемента hierselect
     * 
     * @return array
     * @param int $progsbcid - id подписки ученика на поток
     */
    private function get_list_subjects($progsbcid)
    {
        // объявляем итоговый массив
        $result = array();
        // получаем запись подписки на программу по ее id
        if ( $progsbc = $this->dof->storage('programmsbcs')->get($progsbcid) )
        {// если получили программу - получаем список доступных предметов
            if ( $subjlist = $this->dof->storage('programmitems')->get_records(array('programmid'=>$progsbc->programmid)) )
            {// если получили список - то составляем массив  
                foreach ( $subjlist as $id=>$subject )
                {
                    $result[$id] = $subject->name.' ['.$subject->code.']';
                }
            }
        }
        // возвращаем массив, пригодный для формирования html-элемента select
        return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---') + $result;
    }

    
    /** Получить список учеников для добавления элемента select в форму
     * @todo определить, при помощи какой функции можно выяснить кто является учеником, а кто нет
     * @return array
     */
    private function get_list_students()
    {
        // добавляем первый вариант со словом "выбрать"
        $students = array();
        // извлекаем из базы все контракты
        $select = $this->dof->storage('contracts')->get_records(array('status'=>array('wesign', 'work', 'frozen')), 
                    'id, num, studentid, clientid');
        
        // заводим отдельный массив для учеников, чтобы потом сортировать его
        if ( $select )
        {// данные удалось извлечь
            foreach ($select as $id=>$record)
            {// составляем массив вида id=>ФИО
                // для этого извлекаем всех учеников из базы
                if ( ! empty($record->studentid) AND $this->dof->storage('programmsbcs')->count_list(array('contractid'=>$id)) )
                {// если они присутствуют в контракте и у них есть подписки на программу
                    $students[$id] = 
                        $this->dof->storage('persons')->get_fullname($record->studentid).' ('.$record->num.')';
                }
            }
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'contracts', 'code'=>'use'));
            $students    = $this->dof_get_acl_filtered_list($students, $permissions);
        }
        
        // сортируем массив по фамилиям учеников, чтобы было удобнее искать
        asort($students);
        // возвращаем результат вместе с надписью "выбрать"
        return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---') + $students;
    }
    
    private function get_type_cpassed($progsbcid)
    {
        // добавляем первый вариант со словом "индивидуальная"
        $result = array(0 => $this->dof->get_string('individual','cpassed'));
        if ( $progsbc = $this->dof->storage('programmsbcs')->get($progsbcid) )
        {// если подписка на программу указана
            if ( isset($progsbc->agroupid) AND ! empty($progsbc->agroupid) )
            {// и у ученика имеется группа - добавим ее в список
                if ( ! $agroupname = $this->dof->storage('agroups')->get_field($progsbc->agroupid,'name') )
                {
                    $agroupname = '-';
                }
                if ( ! $agroupcode = $this->dof->storage('agroups')->get_field($progsbc->agroupid,'code') )
                {
                    $agroupcode = '-';
                }
                $result[$progsbc->agroupid] = $agroupname.' ['.$agroupcode.']';
            }
        }
        // возвращаем результат вместе с надписью "индивидуальная"
        return $result;
    }   
    
    
    /**
     * Возвращает строку заголовка формы
     * @param int $cpassedid
     * @return string
     */
    private function get_form_title($cpassedid)
    {
        if ( ! $cpassedid )
        {//заголовок создания формы
            return $this->dof->get_string('newcpassed','cpassed');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editcpassed','cpassed');
        }
    }
    
    /**
     * Возврашает название статуса
     * @return unknown_type
     */
    private function get_status_name($status)
    {
        return $this->dof->workflow('cpassed')->get_name($status);
    }
}

/** Класс формы для поиска подписки на курс
 * 
 */
class dof_im_cpassed_search_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /** Объявление всех элементов формы
     * 
     */
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('search','cpassed'));
        
        // поле "период"
        $ages = $this->get_list_ages();
        $mform->addElement('select', 'ageid', $this->dof->get_string('age','cpassed').':', $ages);
        $mform->setType('ageid', PARAM_INT);
        // поле "предмет"
        $pitems = $this->get_list_pitems();
        $mform->addElement('select', 'programmitemid', $this->dof->get_string('subject','cpassed').':', $pitems);
        $mform->setType('programmitemid', PARAM_INT);
        // поле "учитель"
       /* $teachers = $this->get_list_teachers();
        $mform->addElement('select', 'teacherid', $this->dof->get_string('teacher','cpassed').':', $teachers);
        $mform->setType('teacherid', PARAM_INT);*/
        // поле "ученик"
        //$students = $this->get_list_students();
        //$mform->addElement('select', 'studentid', $this->dof->get_string('student','cpassed').':', $students);
        //$mform->setType('studentid', PARAM_INT);
        // поле "статус"
        $statuses = $this->get_list_statuses();
        $mform->addElement('select', 'status', $this->dof->get_string('status','cpassed').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','cpassed'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');        
    }
    
    /** Получить список всех учебных программ в нужном для формы поиска формате 
     * 
     * @return array массив для создания select-элемента
     */
    private function get_list_pitems()
    {
        // извлекаем все предметы
        $result = $this->dof->storage('programmitems')->
                    get_records(array('status'=>array('active')), 'name ASC', 'id, name, code');
        // преобразуем для использования в select
        $result = $this->dof_get_select_values($result);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'code'=>'use'));
        $result      = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список всех периодов в нужном для формы поиска формате
     * 
     * @return array массив 
     */
    private function get_list_ages()
    {
        // извлекаем периоды
        $result = $this->dof->storage('ages')->
            get_records(array('status'=>array('plan','createstreams','createsbc','createschedule','active','completed')),
                            'name ASC', 'id, name');
        // преобразуем для использования в select
        $result = $this->dof_get_select_values($result);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $result      = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    
    /** Получить список учителей для добавления элемента select в форму
     * @todo определить, при помощи какой функции можно выяснить кто является учителем, а кто нет
     * @deprecated пока что не используется
     * @return array
     */
    private function get_list_teachers()
    {
        
    }
    
    /** Получить список учеников для добавления элемента select в форму
     * @todo определить, при помощи какой функции можно выяснить кто является учеником, а кто нет
     * @return array
     */
    private function get_list_students()
    {
        // добавляем первый вариант со словом "выбрать"
        $students = array();
        // извлекаем из базы все контракты
        $select = $this->dof->storage('contracts')->get_records(array('status'=>array('wesign', 'work', 'frozen')), 
                    'id, num, studentid, clientid');
        
        // заводим отдельный массив для учеников, чтобы потом сортировать его
        if ( $select )
        {// данные удалось извлечь
            foreach ($select as $id=>$record)
            {// составляем массив вида id=>ФИО
                // для этого извлекаем всех учеников из базы
                if ( ! empty($record->studentid) AND $this->dof->storage('programmsbcs')->count_list(array('contractid'=>$id)) )
                {// если они присутствуют в контракте и у них есть подписки на программу
                    $students[$id] = 
                        $this->dof->storage('persons')->get_fullname($record->studentid).' ('.$record->num.')';
                }
            }
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'contracts', 'code'=>'use'));
            $students    = $this->dof_get_acl_filtered_list($students, $permissions);
        }
        
        // сортируем массив по фамилиям учеников, чтобы было удобнее искать
        asort($students);
        // возвращаем результат вместе с надписью "выбрать"
        return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---') + $students;
    }
    
    /** Получить список всех доступных статусов учебного потока
     * 
     * @return array
     */
    private function get_list_statuses()
    {
        $statuses    = array();
        // добавляем значение, на случай, если по статусу искать не нужно
        $statuses[0] = '--- '.$this->dof->get_string('any_mr','cpassed').' ---';
        // получаем весь список статусов через workflow
        $statuses    = array_merge($statuses, $this->dof->workflow('cpassed')->get_list());
        // возвращаем список всех статусов
        return $statuses;
    }
}


/** Класс формы для создания подписки
 *
 */
class dof_im_cpassed_addpass_form extends dof_modlib_widgets_form
{
    private $agroupid;
    private $cstreamid;
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {
        $this->agroupid = $this->_customdata->agroupid;
        $this->cstreamid = $this->_customdata->cstreamid;
        $this->dof     = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // скрытые поля
        $mform->addElement('hidden','cstreamid', $this->cstreamid);
        $mform->setType('cstreamid', PARAM_INT);
        $mform->addElement('hidden','agroupid', $this->agroupid);
        $mform->setType('agroupid', PARAM_INT);
        // находим список студентов группы не имеющие подписки
        if ( $contracts = $this->dof->storage('programmsbcs')->get_contracts_without_cpassed($this->agroupid, $this->cstreamid) )
        {// ученики найдены
        
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'contracts', 'code'=>'use'));
            $contracts = $this->dof_get_acl_filtered_list($contracts, $permissions);
            
            foreach ($contracts as $contract)
            {// создадим для каждого поле
                $studentname = $this->dof->storage('persons')->get_field($contract->studentid,'sortname');
                $mform->addElement('checkbox','addpass['.$contract->id.']', $studentname , $this->dof->get_string('to_sing','cpassed'));
            }
        }
        // кнопка создания
        $mform->addElement('submit', 'save', $this->dof->get_string('save_cpassed','cpassed'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}

/** Класс формы для создания подписки
 *
 */
class dof_im_cpassed_register_reoffset_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
/** Внутренняя функция. Получить параметры для autocomplete-элемента 
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       organizations - поиск по организациям
     *                       workplaces - поиск по рабочим местам
     * @param string $side[optional] - сторона, подписывающая договор
     *                       client - законный представитель
     *                       student - ученик
     * @param int $contractid[optional] - id договора в таблицах: organizations (если договор редактируется)
     * 
     * @return array
     */
    protected function autocomplete_params($type, $side=null, $id=null)
    {
        $options = array();
        $options['plugintype'] = "storage";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';
        
        //тип данных для автопоиска
        switch ($type)
        {
            //организация
            case 'organizations':
                $options['plugincode'] = "organizations";
                $options['querytype'] = "organizations_list";
                
                $fullname = $this->dof->storage('organizations')
                        ->get_field(array('id' => $id),'fullname');
                
                if (!empty($fullname))
                {
                    //значение по умолчанию
                    $options['default'] = array($id => $fullname);
                }
                
                break;
                
            //должность
            case 'workplaces':
                $options['plugincode'] = "workplaces";
                $options['querytype'] = "workplaces_list";
                
                $workplaceid = $this->dof->storage('workplaces')
                        ->get_field(array('personid' => $personid, 'statuswork' => 'active'),'id');
                
                if (!empty($workplaceid))
                {
                    $workplace = $this->dof->storage('workplaces')->get($workplaceid, 'post');
                    //значение по умолчанию
                    $options['default'] = array($workplaceid => $workplace->post);
                }
                
                break;
        }
 
        return $options;
    }

    function definition()
    {
        global $addvars;
        $this->next = (isset($this->_customdata->save_next)) ? $this->_customdata->save_next : null;
        $this->dof  = $this->_customdata->dof;
        $this->psbcid = $this->_customdata->programmsbcid;
        $this->addvars = $addvars;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Информация о приказе сверху
        $mform->addElement('header','orderheader', $this->dof->get_string('order_header', 'cpassed'));
        $personid = $this->dof->storage('programmsbcs')->get_studentid_by_programmsbc($this->psbcid);
        $pname = $this->dof->storage('persons')->get_fullname($personid);
        $fullnamelink = '<a href="'.$this->dof->url_im('persons','/view.php?id='.$personid, $addvars).'">'
                                   .$pname . '</a>';
        $mform->addElement('static', 's_fullname', $this->dof->get_string('student','cpassed').':', $fullnamelink);
        
        if ( !is_null($this->next) )
        {
            $this->show_second_screen($this->_customdata);
        } else 
        {
            $this->show_first_screen();
        }
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    private function show_first_screen()
    {
        $mform =& $this->_form;
        // Название учебного заведения
        // получаем параметры для ajax-запроса и персону по умолчанию (если есть)
        
        $ajaxparams = $this->autocomplete_params('organizations', null, null);
        $mform->addElement('dof_autocomplete', 'institution',
                $this->dof->get_string('institution', 'cpassed').':', array(), $ajaxparams);

//        $mform->addElement('text', 'institution', $this->dof->get_string('institution', 'cpassed'), $this->dof->get_string('institution', 'cpassed'));
//        $mform->setType('institution', PARAM_TEXT);
        $mform->addElement('header','stheader', $this->dof->get_string('document', 'cpassed'));
        // Дата предъявленного документа
        // выставим дату до 1930 года
        $options = array();
        $options['startyear'] = 1930;
        $options['stopyear']  = dof_userdate(time(),'%Y');
        $options['optional']  = false;
        $mform->addElement('date_selector', 'date', $this->dof->get_string('date', 'cpassed').':', $options);
        // Номер предъявленного документа
        $mform->addElement('text', 'number', $this->dof->get_string('number', 'cpassed').':', $this->dof->get_string('number', 'cpassed'));
        $mform->setType('number', PARAM_TEXT);
        // Специальность или квалификация по диплому
        $mform->addElement('text', 'degree', $this->dof->get_string('degree', 'cpassed').':', $this->dof->get_string('degree', 'cpassed'));
        $mform->setType('degree', PARAM_TEXT);
        // Флажок "разрешить перезачет поверх изучаемых или пройденных дисциплин"
        $mform->addElement('checkbox', 'reoffset_passed', '', $this->dof->get_string('reoffset_passed', 'cpassed'));
        // Переход на следующую страницу
        $mform->addElement('submit', 'save_next', $this->dof->get_string('next','cpassed'));
    }
    
    private function show_second_screen($data)
    {
        /**
         * Form 2:
         * Список всех дисциплины, входящих в учебную программу, на которую подписан студент
         *  (если флажок не установлен - отображаются только те, по которым студент еще не имеет активных или
         *  успешно-завершенных cpassed). Напротив каждой из них выпадающее меню с оценкой.
         */
        $mform =& $this->_form;
        $fields = array('institution', 'institutionid', 'dateint', 'number', 'degree');
        foreach ($fields as $field)
        {
            if ( !isset($data->$field) )
            {
                $data->$field = null;
            }
        }
//        $mform->addElement('header', 'formtitle', $this->dof->get_string('document_summary','cpassed'));
        $mform->addElement('static', 's_institution', $this->dof->get_string('institution','cpassed').':', $data->institution);
        $mform->addElement('static', 's_dateint', $this->dof->get_string('date', 'cpassed').':', userdate($data->dateint,'%d.%m.%Y'));
        $mform->addElement('static', 's_number', $this->dof->get_string('number', 'cpassed').':', $data->number);
        $mform->addElement('static', 's_degree', $this->dof->get_string('degree', 'cpassed').':', $data->degree);

        $mform->addElement('hidden', 'orgid', $data->institutionid);
        $mform->setType('orgid', PARAM_INT);
        $mform->addElement('hidden', 'fullname', $data->institution);
        $mform->setType('institution', PARAM_TEXT);
        $mform->addElement('hidden', 'dateint', $data->dateint);
        $mform->setType('dateint', PARAM_INT);
        $mform->addElement('hidden', 'number', $data->number);
        $mform->setType('number', PARAM_TEXT);
        $mform->addElement('hidden', 'degree', $data->degree);
        $mform->setType('degree', PARAM_TEXT);
        $mform->addElement('hidden', 'programmsbcid', $data->programmsbcid);
        $mform->setType('programmsbcid', PARAM_INT);
        $passed = ( isset($data->reoffset_passed) ) ? $data->reoffset_passed : false;
        if ( $passed )
        {
            $mform->addElement('hidden', 'reoffsetpassed', $passed);
            $mform->setType('reoffsetpassed', PARAM_INT);
        }
        // Отобразим дисциплины для перезачёта
        $this->show_disciplines($data->programmsbcid, $passed);
        // Отобразим дисциплины для академической разницы
//        $this->show_academicdebts($data->programmsbcid);
        // Создание
        $mform->addElement('submit', 'save', $this->dof->get_string('next','cpassed'));
    }
        
    /** Отобразить дисциплины для перезачёта
     * 
     * @param int $programmsbcid - id из таблицы programmsbcs
     * @param bool $passed - отображать активные или успешно-завершенные cpassed по дисциплинам
     * @return void
     */
    private function show_disciplines($programmsbcid, $passed = true)
    {
        $mform =& $this->_form;
        $mform->addElement('header', 'disctitle', $this->dof->get_string('disciplines_reoffset','cpassed'));

        // Сформируем список дисциплин, по которым уже есть академическая разница
        $debts = $this->dof->storage('cpassed')->get_academic_debts($programmsbcid);
        $actualdebts = $this->dof->storage('cpassed')->get_actual_academicdebts_cpassed($debts);
        unset($debts);
        $pitemsdebts = array();
        foreach ( $actualdebts as $cpassed )
        {
            if ( !isset($pitemsdebts[$cpassed->programmitemid]) )
            {
                $pitemsdebts[$cpassed->programmitemid] = $cpassed;
            }
        }
        // Выберем необходимые поля 
        $pbcs = $this->dof->storage('programmsbcs')->get($programmsbcid, 'contractid, programmid');
        $pitems = $this->dof->storage('programmitems')->get_pitems_list($pbcs->programmid);
        $contract = $this->dof->storage('contracts')->get($pbcs->contractid, 'studentid');
        $mform->addElement('hidden', 'personid', $contract->studentid);
        $mform->setType('personid', PARAM_INT);
        if ( empty($pitems) )
        {
            $mform->addElement('static', 'nopitems', $this->dof->get_string('nodisciplines','cpassed'));
            return;
        }
        foreach ( $pitems as $pitem )
        {
            // Ссылка на дисциплину
            $vars = array('pitemid' => $pitem->id);
            $pitemurl = $this->dof->url_im('programmitems', '/view.php', $this->addvars + $vars);
            $pitemlink = "<a href=\"{$pitemurl}\">{$pitem->name}</a>";
            if ( !$passed ) // Проверим, есть ли активные или успешно-завершенные cpassed по этой дисциплине
            { // Если флажок установлен, отображаются все 
                // cpassed
                $actualcpassed = false;
                $cpasseds = $this->dof->storage('cpassed')->get_cpassed_on_studentid_programmsbcid_active_complete($programmsbcid, $pitem->id);
                if ( $cpasseds === false )
                { // пока не знаю, что делать, если вообще ничего не нашёл
                    continue;
                } else 
                {
                    // Достаём последний по дате cpassed для отображения статуса
                    $actualcpassed = reset($cpasseds);
                }
                
                if ( !empty($cpasseds) OR isset($pitemsdebts[$pitem->id]) )
                { // Если нашли активные или успешно-завершенные cpassed по этой дисциплине, или
                    // академическую разницу
                    if ( empty($actualcpassed) )
                    { // Если не нашли последний cpassed из get_cpassed_on_studentid_programmsbcid_active_complete,
                        // значит он в академ. разнице
                        $actualcpassed = $pitemsdebts[$pitem->id];
                    }
                    // Ссылка на подписку
                    $vars = array('cpassedid' => $actualcpassed->id);
                    $actualcpassed->link = $this->dof->url_im('cpassed', '/view.php', $this->addvars + $vars);
                    $actualcpassed->status = $this->dof->workflow('cpassed')->get_name($actualcpassed->status);
                    $gradeelements = array();
                    $gradeelements[] = $mform->createElement('static', 'message', '', $this->dof->get_string('hascpassed', 'cpassed', $actualcpassed));
                    $grp =& $mform->addElement('group', 'group['.$pitem->id.']', $pitemlink, $gradeelements, null, false);
                    unset($gradeelements);
                    continue;
                }
            }
            $gradeelements = array();
            $gradeelements[] = $mform->createElement('select', 'grades['.$pitem->id.']', $pitem->name.':', 
                                      $this->get_itog_grades_scale(null, $pitem->scale), '');
            $grp =& $mform->addElement('group', 'group['.$pitem->id.']', $pitemlink, $gradeelements, null, false);
            // уничтожаем массив, чтобы избавиться от отработанных элементов
            unset($gradeelements);
        }
    }
    
    /** Обработать пришедшие из формы данные, затем создаются cpassed-ы не привязанные к периоду.
     * Если у студента уже был cpassed по этой дисциплине, перезачет ссылается на него
     *  полем repeatid (если несколько - то на самый первый, если старая подписка была активна
     *  - закрывается с неуспешным статусом).
     * создать и выполнить приказ и вывести сообщение
     * @return bool 
     */
    public function process($departmentid = 0)
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// данные отправлены в форму, и не возникло ошибок

            // Добавим организацию в список organizations, если не нашли таковой
            if ( empty($formdata->orgid) )
            {
                if ( !empty($formdata->fullname) )
                {// Организации с пустым именем не существует
                    $organization = new stdClass();
                    $organization->fullname = $formdata->fullname;
                    if ( !$this->dof->storage('organizations')->is_exists(array('fullname' => $formdata->fullname)) )
                    {
                        $this->dof->storage('organizations')->insert($organization);
                    }
                }
            }
            // Создаем экземпляр приказа через плагин
            $order = $this->dof->im('cpassed')->order('register_reoffset');
            // Вводим данные (id, отдел, ответственный, время в приказе)
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
            {// Если id персоны не найден
                return false;
            }
            $orderobj = new stdClass();
            $orderobj->date = time();
            $orderobj->ownerid = $personid;
            $orderobj->data = new stdClass();
            $orderobj->data->departmentid  = $departmentid;
            $orderobj->data->institution   = $formdata->fullname;
            $orderobj->data->dateint       = $formdata->dateint;
            $orderobj->data->number        = $formdata->number;
            $orderobj->data->degree        = $formdata->degree;
            $orderobj->data->personid      = $formdata->personid;
            $orderobj->data->programmsbcid = $formdata->programmsbcid;
            $orderobj->data->grades        = array();
            foreach ( $formdata->grades as $id => $grade )
            {
                if ( !empty($grade) )
                {
                    $orderobj->data->grades[$id] = $grade;
                }
            }
            $orderobj->date = time();
            // Сохраняем приказ в БД и привязываем экземпляр приказа к id
            $orderobj->departmentid = $departmentid;
            $order->save($orderobj);
            // Подписываем приказ
            $order->sign($personid);
            if ( ! $order->is_signed() )
            {// Приказ не подписан
                return false;
            }
            if ( ! $order->execute() )
            {// Не удалось исполнить приказ
                return false;
            }
        }
        return true;
    }
    
    /** Возвращает возможные итоговые оценки предмета и вариант "Академическая разница"
     * @param string $grade - старая оценка, если есть
     * @return array список возможных итоговых оценок
     */
    private function get_itog_grades_scale($grade = null, $scale = null)
    {
        $itog_grades = array();
        if ( is_null($grade) )
        {// если старой оценки нет - выведем надпись "Без оценки"
            $itog_grades[''] = $this->dof->get_string('without_grade','journal');
        } else
        {// если указана - выведем ее
            $itog_grades[''] = $this->dof->get_string('old_grade','journal',$grade);
        }
        $itog_grades['academicdebt'] = $this->dof->get_string('academicdebts','cpassed');
        if ( empty($scale) )
        {
            return $itog_grades;
        }
        return $itog_grades + $this->dof->storage('plans')->get_grades_scale($scale);
    }
}
/** Класс, отвечающий за форму смену статуса подписки на дисциплину вручную
 * 
 */
class dof_im_cpassed_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'cpassed';
    }
    
    protected function workflow_code()
    {
        return 'cpassed';
    }
}



class dof_im_cpassed_edit_pitem_form extends dof_modlib_widgets_form
{
    private $cpassed;
    /**
     * @var dof_control 
     */
    public $dof;
    
    /** Объявление формы
     * 
     * @return null
     */
    function definition()
    {// делаем глобальные переменные видимыми

        $this->cpassed = $this->_customdata->cpassed;
        $this->dof     = $this->_customdata->dof;

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','cpassedid', $this->cpassed->id);
        $mform->setType('cpassedid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // устанавливаем значения как hidden-поля, чтобы потом забрать из них
        // значения при помощи definition_after_data
        $mform->addElement('hidden','studentid', 0);
        $mform->setType('studentid', PARAM_INT);
        $mform->addElement('hidden','programmsbcid', 0);
        $mform->setType('programmsbcid', PARAM_INT);
        $mform->addElement('hidden','programmitemid', 0);
        $mform->setType('programmitemid', PARAM_INT);
        $mform->addElement('hidden','cstreamid', 0);
        $mform->setType('cstreamid', PARAM_INT);
        $mform->addElement('hidden','ageid', 0);
        $mform->setType('ageid', PARAM_INT);
        $mform->addElement('hidden','agroupid', 0);
        $mform->setType('agroupid', PARAM_INT);
        
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->cpassed->id));
        // получаем список всех элементов для hierselect
        $options = $this->get_select_options();
        // выравниваем строки по высоте
        $mform->addElement('html', '<div style=" line-height: 1.9; ">');
        // добавляем новый элемент выбора зависимых вариантов форму
        $mform->addElement('static', 'programm',  $this->dof->get_string('programm', 'cpassed').':');
        $mform->addElement('static', 'subject', $this->dof->get_string('subject', 'cpassed').':');
        $mform->addElement('static', 'cstream', $this->dof->get_string('cstream', 'cpassed').':');
        $mform->addElement('static', 'age', $this->dof->get_string('age', 'cpassed').':');
        // устанавливаем для него варианты ответа
        $myselectst =& $mform->addElement('hierselect', 'pidata', 
                                        $this->dof->get_string('student',      'cpassed').':<br/>'.
                                        $this->dof->get_string('type_cpassed', 'cpassed').':<br/>',
                                        null,'<br/>');
        $programmid = $this->dof->storage('programmitems')->get_field($this->cpassed->programmitemid,'programmid');
        $students = $this->get_list_students($programmid,$this->cpassed->programmitemid,$this->cpassed->ageid);
        foreach ( $students as $sbcid=>$student )
        {
            $type[$sbcid] = $this->get_type_cpassed($sbcid);
        }
        if ( isset($this->cpassed->status) )
        {    
            if ( $this->cpassed->status != 'plan' AND ! $this->dof->storage('cpassed')->is_access('edit:studentid') )
            {// если статус не "plan" то нельзя редактировать студента
                // найдем контракт
                $sbcs = $this->dof->storage('programmsbcs')->get($this->cpassed->programmsbcid);
                $contrac = $this->dof->storage('contracts')->get($sbcs->contractid);
                // полное имя + контракт 
                $students = array();
                $students[$sbcs->id] = $this->dof->storage('persons')->get_fullname($this->cpassed->studentid).'('.$contrac->num.')';
                $type[$sbcs->id] = $this->get_type_cpassed($sbcid);              
            }
        }
        $myselectst->setOptions(array($students,$type));
        // закрываем тег выравнивания строк
        $mform->addElement('html', '</div>');
        if ( $this->cpassed->id )
        {// выведем поле "статус" если форма редактируется
            $mform->addElement('static', 'status', $this->dof->get_string('status','cpassed').':', 
                                    $this->get_status_name($this->cpassed->status));
        }
        
        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','cpassed'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Установка значений по умолчанию для сложных элементов
     * 
     * @return 
     */
    function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // получаем id ученика
        $studentid      = $mform->getElementValue('studentid');
        
        // получаем предмет
        $programmitemid = $mform->getElementValue('programmitemid');
        $item = $this->dof->storage('programmitems')->get($programmitemid);
        $itemname = $item->name.' ['.$item->code.']';
        // получаем программу
        $programm = $this->dof->storage('programms')->get($item->programmid);
        $programmname = $programm->name.' ['.$programm->code.']';
        // получаем id потока 
        $cstreamid = $mform->getElementValue('cstreamid');
        $cstreamname = $this->dof->storage('cstreams')->get_field($cstreamid,'name');
        // получаем id подписки на программу
        $programmsbcid  = $mform->getElementValue('programmsbcid');
        // получаем период
        $ageid = $mform->getElementValue('ageid');
        $agename = $this->dof->storage('ages')->get_field($ageid,'name');
        $agroupid = $mform->getElementValue('agroupid');

        // устанавливаем значения по умолчанию для всех полей элемента hierselect
        $mform->setDefault('programm', $programmname);
        $mform->setDefault('subject', $itemname);
        $mform->setDefault('cstream', $cstreamname);
        $mform->setDefault('age', $agename);
        $mform->setDefault('pidata', array($programmsbcid,$agroupid));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        
    }
    
    /** Проверка данных на стороне сервера
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        //print_object($data);
        // проверим существование периода
        if ( empty($data['pidata'][0]) AND ! $progsbc = $this->dof->storage('programmsbcs')->get($data['pidata'][0]) )
        {// подписка на программу не существует
            $errors['pidata'] = $this->dof->get_string('err_required_multi','cpassed');
        }
        // проверим существование потока, если он указан
        if ( $data['cstreamid'] AND ! $cstream = $this->dof->storage('cstreams')->get($data['cstreamid']) )
        {// поток не существует, сообщим об этом
            $errors['pidata'] = $this->dof->get_string('cstream_not_exists','cpassed');
        }elseif ( $data['cstreamid'] )
        {// если поток существует - проверим правильность его привязки к программе
            if ( ! $pitem = $this->dof->storage('programmitems')->get($cstream->programmitemid) )
            {// если не найден элемент учебной программы - это ошибка
                $errors['pidata'] = $this->dof->get_string('wrong_cstream_and_programsbc','cpassed');
            }elseif ( isset($progsbc->programmid) AND $pitem->programmid != $progsbc->programmid )
            {// если элемент программы найден, про принадлежит к другому потоку - это ошибка
                $errors['pidata'] = $this->dof->get_string('wrong_cstream_and_programsbc','cpassed');
            }
        }
        // если ошибки есть - то пользователь вернется на страницу редактирования и увидит их
        return $errors;
    }
    
    /** Получить весь список опций для элемента hierselect
     * @todo переделать эту функцию в рекурсивную процедуру, чтобы сократить объем кода
     * @return stdClass object объект, содержащий данные для элемента hierselect
     */
    function get_select_options()
    {
        $result = new stdClass();
        // создаем массив для учебных программ
        $programms = array();
        $cstreams  = array();
        // для каждого ученика определяем список программ на которые он подписан
        $plist = $this->get_programms();
        // создадим иерархию второго уровня
        $programms = $plist;
        foreach ( $plist as $progid=>$programm )
        {// составляем список разрешенных предметов
            $subjlist = $this->get_list_subjects($progid);
            // создаем иерархию третьего уровня
            $subjects[$progid] = $subjlist;
            foreach ( $subjlist as $pitemid=>$subject )
            {// составляем список разрешенных учебных потоков
                $cstreamlist = $this->get_list_cstreams($pitemid);
                // создаем иерархию четвертого уровня
                $cstreams[$progid][$pitemid] = $cstreamlist;
                foreach ( $cstreamlist as $csid=>$cstream )
                {// составляем список разрешенных периодов
                    $agelist = $this->get_list_ages($csid);
                    // создаем иерархию пятого уровня
                    $ages[$progid][$pitemid][$csid] = $agelist;
                    
                }
            }
        }
        // записываем в результурующий объект все что мы получили
        $result->programms = $programms;
        $result->subjects  = $subjects;
        $result->cstreams  = $cstreams;
        $result->ages      = $ages;
        //print_object($result);
        // возвращаем все составленные массивы в упорядоченном виде
        return $result;
    }
    
    /** Получить список программ, на которые подписан ученик
     * @todo добавить в зависимости плагин контрактов, программ, и подписок на программы
     * @todo убрать из списка программы с неправильными статусами
     * @todo при редактировании всегда включать программу ученика, вне зависимости от статуса и прав
     * @return array массив вариантов для элемента hierselect
     * @param int $contractid - id контракта ученика для которого получается список программ. Таблица contracts.
     */
    private function get_programms()
    {
        $result = array();
        if ( $programms = $this->dof->storage('programms')->get_records(array()) )
        {// получили id программ - теперь получим их названия
            // оставим в списке только те объекты, на использование которых есть право
            $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
            $programms   = $this->dof_get_acl_filtered_list($programms, $permissions);
            
            $result = $this->dof_get_select_values($programms,true,null,array('name','code'));
        }
        // возвращаем то, что набрали
        return $result;
    }
    
    /** Получить список учебных потоков, разрешенных данной учебной программой
     * @return array массив допустимых учебных потоков
     * @todo убрать объекты с неправильным статусом
     * @param object $progitemid - id изучаемого предмета в таблице programmitems
     */
    private function get_list_cstreams($progitemid)
    {
        $result = array();
        // получаем все учебные потоки для предмета
        $result = $this->dof->storage('cstreams')->get_records(array('programmitemid'=>$progitemid));
        $result = $this->dof_get_select_values($result);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'cstreams', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список периодов для элемента hierselect
     * 
     * @return array массив пригодный для составления html-элемента select
     * @param int $progsbcid - id подписки на учебную программу в таблице programmsbcs
     * @param int $cstreamid - id потока в таблице cstreams
     */
    private function get_list_ages($cstreamid)
    {
        // объявляем итоговый массив
        $result = array();
        // период указывается единственным образом (берется из потока)
        if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
        {// поток не найден - нет смысла выполнять дальнейшие действия. Сообщим об ошибке.
            $result[0] = '('.$this->dof->get_string('not_found', 'cpassed').')';
            return $result;
        }
        if ( $age = $this->dof->storage('ages')->get($cstream->ageid) )
        {// если период есть - у нас должен быть единственный вариант выбора
            $result[$age->id] = $age->name.' ';
        }else
        {// если периода нет - сообщим об этом
            $result[0] = '('.$this->dof->get_string('not_found', 'cpassed').')';
        }
        return $result;
    }
    
    /** Получить список предметов для элемента hierselect
     * 
     * @return array
     * @todo убрать объекты с неправильным статусом
     * @param int $progsbcid - id подписки ученика на поток
     */
    private function get_list_subjects($progid)
    {
        // объявляем итоговый массив
        $result = array();
        // добавляем первый вариант со словом "выбрать"
        $result[0] = '--- '.$this->dof->get_string('to_select','cpassed').' ---';
        if ( $subjlist = $this->dof->storage('programmitems')->get_records(array('programmid'=>$progid)) )
        {// если получили список - то составляем массив  
            $result = $this->dof_get_select_values($subjlist,true,null,array('name','code'));
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        // возвращаем массив, пригодный для формирования html-элемента select
        return $result;
    }

    
    /** Получить список учеников для добавления элемента select в форму
     * @todo определить, при помощи какой функции можно выяснить кто является учеником, а кто нет
     * @return array
     */
    private function get_list_students($programmid,$pitemid,$ageid)
    {
        // добавляем первый вариант со словом "выбрать"
        $students = array();
        if ( $agenum = $this->dof->storage('programmitems')->get_field($pitemid,'agenum') AND $agenum != 0 )
        {// если предмет отнесен к конкретной параллели
            // подписки отображаем только этой параллели';
            $sbcs = $this->dof->storage('programmsbcs')->get_records(array('programmid'=>$programmid,'agenum'=>$agenum));
        }else
        {   // иначе найдем все для программы';
            $sbcs = $this->dof->storage('programmsbcs')->get_records(array('programmid'=>$programmid));
        }
        if ( ! $sbcs )
        {// данные не удалось извлечь
            return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---');
        }
        foreach ($sbcs as $id=>$record)
        {// составляем массив вида id=>ФИО
            //$sbcageid = $this->dof->storage('ages')->get_next_ageid($record->agestartid, $record->agenum);
            //if ( $sbcageid != $ageid )
            //{// период подписки не совпадает с периодом потока - пропускаем подписку
            //    continue;
            //}
            if ( isset($record->contractid) AND ! $contract = $this->dof->storage('contracts')->get($record->contractid) )
            {// если контракт не удалось извлечь - это ошибка
                continue;
            }
            if ( ! empty($contract->studentid) )
            {// если ученик присутствует в контракте
                $students[$id] =
                     $this->dof->storage('persons')->get_fullname($contract->studentid).' ('.$contract->num.')';
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'persons', 'code'=>'use'));
        $students = $this->dof_get_acl_filtered_list($students, $permissions);
        // сортируем массив по фамилиям учеников, чтобы было удобнее искать
        asort($students);
        // возвращаем результат вместе с надписью "выбрать"
        return array(0 => '--- '.$this->dof->get_string('to_select','cpassed').' ---') + $students;
    }

    /** Получить список учеников для добавления элемента select в форму
     * @todo определить, при помощи какой функции можно выяснить кто является учеником, а кто нет
     * @return array
     */
    private function get_type_cpassed($progsbcid)
    {
        // добавляем первый вариант со словом "индивидуальная"
        $result = array(0 => $this->dof->get_string('individual','cpassed'));
        if ( $progsbc = $this->dof->storage('programmsbcs')->get($progsbcid) )
        {// если подписка на программу указана
            if ( isset($progsbc->agroupid) AND ! empty($progsbc->agroupid) )
            {// и у ученика имеется группа - добавим ее в список
                $agroupname = $this->dof->storage('agroups')->get_field($progsbc->agroupid,'name');
                $agroupcode = $this->dof->storage('agroups')->get_field($progsbc->agroupid,'code');
                $result[$progsbc->agroupid] = $agroupname.' ['.$agroupcode.']';
            }
        }
        // возвращаем результат вместе с надписью "индивидуальная"
        return $result;
    }   
    
    
    /**
     * Возвращает строку заголовка формы
     * @param int $cpassedid
     * @return string
     */
    private function get_form_title($cpassedid)
    {
        if ( ! $cpassedid )
        {//заголовок создания формы
            return $this->dof->get_string('newcpassed','cpassed');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editcpassed','cpassed');
        }
    }
    
    /**
     * Возврашает название статуса
     * @return unknown_type
     */
    private function get_status_name($status)
    {
        return $this->dof->workflow('cpassed')->get_name($status);
    }
}

/**
 * Панель управления подписками на учебный процесс
 * 
 */
class dof_im_cpassed_listeditor_form extends dof_modlib_widgets_form
{
    private $conditions = [];

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
     * Обьявление полей формы
     * 
     * @see dof_modlib_widgets_form::definition()
     */
    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->addvars = $this->_customdata->addvars;
        $this->dof = $this->_customdata->dof;
        
        // Нормализация входных данных
        if ( ! isset($this->addvars['cstreamid']) )
        {
            $this->addvars['cstreamid'] = 0;
        }
        if ( ! isset($this->addvars['departmentid']) )
        {
            $this->addvars['departmentid'] = 0;
        }
        if ( ! isset($this->addvars['sort']) )
        {
            $this->addvars['sort'] = 'id';
        }
        if ( ! isset($this->addvars['dir']) )
        {
            $this->addvars['dir'] = 'ASC';
        }
        if ( $this->addvars['dir'] != 'ASC' )
        {
            $this->addvars['dir'] = 'DESC';
        }
        
        // Установка HTML-атрибутов формы
        $formattrs = $mform->getAttributes();
        $formattrs['class'] = $formattrs['class']." cpassed-listeditor";
        $formattrs['id'] = "form_cpassed_list_editor";
        $mform->setAttributes($formattrs);

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'cstreamid', $this->addvars['cstreamid']);
        $mform->setType('cstreamid', PARAM_INT);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Формирование таблицы подписок на учебный процесс
        $tabledata = $this->get_cpasseds_data();
        $this->add_list_editor($tabledata);

        // Функционал массовых действий над подписками
        $operationtypes = [
            'status_change' => $this->dof->get_string('form_listeditor_element_option_status_change', 'cpassed'),
            'cstream_change_'.$this->addvars['cstreamid'] => $this->dof->get_string('form_listeditor_element_option_cstream_change', 'cpassed')
        ];
        $mform->addElement(
            'select', 
            'operationtype',
            $this->dof->get_string('form_listeditor_element_operationtype', 'cpassed'), 
            $operationtypes
        );
        // Список статусов
        $ajaxoptions = [
            'plugintype' => 'im',
            'plugincode' => 'cpassed',
            'querytype' => 'list_operations',
            'parentid' => 'id_operationtype'
        ];
        $mform->addElement('dof_ajaxselect', 'operation',
            $this->dof->get_string('form_listeditor_element_operation', 'cpassed'), NULL, $ajaxoptions);

        // Кнопка подтверждения
        $mform->addElement('dof_confirm_submit', 'confirm_submit', 'confirm_submit',[
            'modalbuttonname' => $this->dof->get_string('form_listeditor_modalbuttonname', 'cpassed'),
            'modaltitle' => $this->dof->get_string('form_listeditor_modaltitle', 'cpassed'),
            'modalcontent' => $this->dof->get_string('form_listeditor_modalcontent', 'cpassed'),
            'submitbuttonname' => $this->dof->get_string('form_listeditor_submitbuttonname', 'cpassed'),
            'cancelbuttonname' => $this->dof->get_string('form_listeditor_cancelbuttonname', 'cpassed')
        ]);
    }

    /**
     * Получение данных по подпискам на дисцпилины
     *
     * @return array - массив, состоящий из строк (массивов с данными по каждой подписке на дисциплину)
     */
    private function get_cpasseds_data()
    {
        // Параметры поиска подписок
        $conditions = [
            'cstreamid' => $this->addvars['cstreamid'],
            'departmentid' => $this->addvars['departmentid']
        ];
         
        // Получение списка подписок на учебный процесс
        $cpasseds = (array)$this->dof->storage('cpassed')->get_listing(
            $conditions, 
            $this->addvars['limitfrom']-1, 
            $this->addvars['limitnum'], 
            '', 
            '*', 
            false
        );
        
        // Получение данных для формирования таблицы
        $options = [
            'sortview' => true,
            'urlpath' => '/listeditor.php'
        ];
        // Заголовок таблицы подписок на учебный процесс
        $cpassedsdata = ['0' => [
            $this->dof->get_string('actions','cpassed'),
            $this->dof->get_string('student','cpassed'),
            $this->dof->get_string('programmitem','cpassed'),
            $this->dof->get_string('agenum','cpassed'),
            $this->dof->get_string('agroup','cpassed'),
            $this->dof->get_string('age','cpassed'),
            $this->dof->get_string('status','cpassed')
        ]];
        // Строки таблицы подписок на учебный процесс
        foreach ( $cpasseds as $cpassed )
        {
            $cpassedsdata[(string)$cpassed->id] = $this->dof->im('cpassed')->
                get_string_table($cpassed, $this->addvars, $options);
        }
        return $cpassedsdata;
    }

    /**
     * Получение количества подписок на дисцпилины без использования LIMIT
     *
     *  @return int - количество подписок на дисцпилины
     */
    public function get_cpasseds_count()
    {
        $conditions = [
            'cstreamid' => $this->addvars['cstreamid'],
            'departmentid' => $this->addvars['departmentid']
        ];
        
        return $this->dof->storage('cpassed')->get_listing(
            $conditions, $this->addvars['limitfrom']-1, $this->addvars['limitnum'], $this->addvars['sort'], '*', true);
    }

    /**
     * Добавление в форму таблицы с чекбоксами
     *
     * @param string $elementname - название элементов, используется для формирования чекбоксов и заголовка
     * @param array $data - массив будущих строк таблицы, каждая строка должна содержать массив данных для ячеек. Первая строка - заголовки.
     * @param string $headertext - текст заголовка
     */
    private function add_list_editor($data)
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Заголовок таблицы
        $mform->addElement(
            'header', 
            'cpassedids-table-header', 
            $this->dof->get_string('form_listeditor_headertext', 'cpassed')
        );

        
        // Формирование заголовка таблицы
        $header = reset($data);
        $mform->addElement('html', '<table class="generaltable boxaligncenter"><tr><th width="0">');
        $this->add_checkbox_controller(1, $this->dof->get_string('form_listeditor_headercheckbox', 'cpassed'));
        $mform->addElement('html', '</th><th>');
        $mform->addElement('html', implode('</th><th>',$header).'</th></tr>');
        // Удаление данных о заголовке
        unset($data[key($data)]);
        foreach( $data as $id => $datarow )
        {
            $mform->addElement('html', '</td><td>');
            $mform->addElement('advcheckbox', 'cpassedids['.$id.']', '', '', ['group' => 1]);
            $mform->addElement('html','</td><td>'.implode('</td><td>',$datarow).'</td></tr>');
        }
        $mform->addElement('html','</table>');
    }

    public function validation($data, $files)
    {
        $errors = [];

        $statuschange = 'status_change';
        $cstreamchange = 'cstream_change_';
        $cstreamchangestrlen = strlen($cstreamchange);

        if( $data['operation'] == '0' )
        {
            $errors['operation'] = $this->dof->get_string("form_listeditor_message_operation_not_selected",'cpassed');
        }
        else
        {
            if( $data['operationtype'] == $statuschange )
            {//требуется перевод статуса
                if( ! $this->dof->workflow('cpassed')->is_access('changestatus') )
                {
                    $errors['operationtype'] = $this->dof->get_string("form_listeditor_message_cpassed_changestatus_denied",'cpassed');
                }
            }
            elseif( substr($data['operationtype'], 0, $cstreamchangestrlen) == $cstreamchange )
            {
                if( ! $this->dof->workflow('cpassed')->is_access('changestatus') )
                {
                    $errors['operationtype'] = $this->dof->get_string("form_listeditor_message_cpassed_changestatus_denied",'cpassed');
                }
                if( ! $this->dof->workflow('cpassed')->is_access('edit') )
                {
                    $errors['operationtype'] = $this->dof->get_string("form_listeditor_message_cpassed_edit_denied",'cpassed');
                }
            }
        }
        return $errors;
    }

    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data() )
        {// Форма подтверждена и данные получены
            
            $messages = [];
            $statuschange = 'status_change';
            $cstreamchange = 'cstream_change_';
            $cstreamchangestrlen = strlen($cstreamchange);
            $oldcstreamurl = $this->dof->url_im('cpassed', '/listeditor.php', $this->addvars);
            $checkedcount = 0;
            $changedcount = 0;
            
            if ( !empty($formdata->cpassedids))
            {//галочки поставили

                foreach( $formdata->cpassedids as $cpassedid=>$checked )
                {
                    if($checked)
                    {//для этой подписки на дисциплину требуется совершить действие
                        $checkedcount++;


                        if( $formdata->operationtype == $statuschange )
                        {//требуется перевод статуса
                            
                            //подписка на дисциплину
                            $cpassed = $this->dof->storage('cpassed')->get($cpassedid);
    
                            //объект для использования в языковых строках
                            $a = new stdClass();
                            $a->cpassedid = $cpassed->id;
                            $a->studentname = $this->dof->storage('persons')->get_fullname($cpassed->studentid);
    
                            //статусы, доступные для этой подписки на дисциплину
                            $availablestatuses = $this->dof->workflow('cpassed')->get_available($cpassed->id);
                            
                            //статус, выбранный для перевода
                            $newstatus = $formdata->operation;
                            if( in_array($newstatus, array_keys($availablestatuses)) )
                            {//в такой статус можно переводить
                                if( $this->dof->workflow('cpassed')->change($cpassed->id, $newstatus) )
                                {//статус переведен
                                    $changedcount ++;
                                }
                                else
                                {//перевод статуса не удался
                                    $messages[] = $this->dof->get_string("form_listeditor_message_status_not_changed",'cpassed',$a);
                                }
                            }
                            else
                            {//данную подписку нельзя перевести в выбранный статус
                                $messages[] = $this->dof->get_string("form_listeditor_message_status_not_available",'cpassed',$a);
                            }
                        }
                        else if ( substr($formdata->operationtype, 0, $cstreamchangestrlen) == $cstreamchange )
                        {//операция - перевод в другой процесс
                            //процесс, в который производится перевод
                            $destinationcstreamid = $formdata->operation;
                            //результат перевода
                            $moveresult = $this->dof->storage('cpassed')->move_cpassed($cpassedid, $destinationcstreamid);
                            if( $moveresult === true )
                            {
                                $changedcount ++;
                            }
                            else
                            {
                                foreach($moveresult as $message)
                                {
                                    $messages[] = $message;
                                }
                            }
                        }
                    }
                }
                
                if( $checkedcount == 0 )
                {//не было отмечено ни одной подписки на дисциплины
                    $messages[] = $this->dof->get_string("form_listeditor_message_cpasseds_not_selected",'cpassed');
                }
                
                if( $changedcount > 0 )
                {//были успешные операции

                    if( $formdata->operationtype == $statuschange )
                    {//требуется перевод статуса
                        $messages[] = $this->dof->get_string("form_listeditor_message_statuses_changed", 'cpassed', $changedcount);
                    }
                    elseif ( substr($formdata->operationtype, 0, $cstreamchangestrlen) == $cstreamchange )
                    {
                        //формирование get-параметров для ссылки на выбранный для перемещения учебный процесс
                        $destinationcstreamaddvars = $this->addvars;
                        $destinationcstreamaddvars['cstreamid'] = $destinationcstreamid;
                        $destinationcstreamaddvars['limitfrom'] = 1;
                        //ссылка на выбранный для перемещения учебный процесс
                        $destinationcstreamlink = dof_html_writer::link(
                            $this->dof->url_im('cpassed', '/listeditor.php', $destinationcstreamaddvars),
                            $this->dof->get_string("form_listeditor_message_goto_new_cstream", 'cpassed'));
                        
                        //результат выполнения операций по перемещению
                        $a = new stdClass();
                        $a->replaced = $changedcount;
                        $a->link = $destinationcstreamlink;
                        $messages[] = $this->dof->get_string("form_listeditor_message_replaced", 'cpassed', $a);
                    }
                }
            }
            else
            {//подписки на дисциплины не получены
                $messages[] = $this->dof->get_string("form_listeditor_message_cpasseds_not_selected",'cpassed');
            }
            //возвращаем пользователя на страницу массовых операций, отображая сообщения
            redirect($oldcstreamurl, implode('<br/>', $messages), 15);
        }
    }
}


class dof_im_cpassed_import_to_cstream_form extends dof_modlib_widgets_form
{
    private $conditions=[];
    private $addvars=[];

    /**
     * @var dof_control
     */
    protected $dof;


    /** Объявление формы
     *
     * @return null
     */
    function definition()
    {
        // делаем глобальные переменные видимыми
        $this->addvars = $this->_customdata->addvars;
        $this->dof = $this->_customdata->dof;
        $cstreamid = $this->addvars['cstreamid'];

        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        //устанавливаем свой идентификатор и добавляем класс для формы
        $formattrs = $mform->getAttributes();
        $formattrs['class'] = $formattrs['class']." cpassed-import_to_cstream";
        $formattrs['id'] = "form_cpassed_import_to_cstream";
        $mform->setAttributes($formattrs);
        
        //доступные для импорта из других процессов подписки на дисциплины 
        $cpassedoptions = $this->get_cpasseds_selectgroups_options($cstreamid);
        $mform->addElement('selectgroups', 'cpasseds', $this->dof->get_string('form_import_to_cstream_cpassed_available_to_import', 'cpassed'), $cpassedoptions, [
            'multiple' => 'multiple'
        ], false);
        
        //подписки на программы, доступные для записи в этот процесс
        $programmsbcstoenrol = $this->dof->im('cstreams')->get_add_persons_list($cstreamid);
        //текущий учебный процесс
        $currentcstream = $this->dof->storage('cstreams')->get($cstreamid);
        //список актуальных статусов (которые как правило нет смысла дублировать)
        $actualcpassedsstatuses = array_keys($this->dof->workflow('cpassed')->get_meta_list('actual'));
        //подписки на программы, не задействованные в других процессах (не имеющие подписок на дисциплины)
        $freeprogrammsbcs = [];
        //подписки на программы, уже задействованные в других процессах
        //могут пригодиться, чтобы подписывать на процесс, а не переносить из других процессов
        $busyprogrammsbcs = [];
        foreach($programmsbcstoenrol as $psbcid=>$name)
        {
            //пробуем получить подписки на дисциплины (проверяем имеются ли у нашей подписки на программу)
            $cpasseds = $this->dof->storage('cpassed')->get_cpassed_on_studentid_programmsbcid_active_complete($psbcid,
                $currentcstream->programmitemid, $actualcpassedsstatuses);
            if( empty($cpasseds) )
            {//подписка на программу не участвует в подписках на дисциплины
                $freeprogrammsbcs[$psbcid] = $name;
            }
            else
            {//подписка на программу задействована в подписках на дисциплины
                $busyprogrammsbcs[$psbcid] = $name;
            }
        }

        $mform->addElement('select', 'freeprogrammsbcs', $this->dof->get_string('form_import_to_cstream_free_programmsbcs', 'cpassed'), $freeprogrammsbcs, [
            'multiple' => 'multiple'
        ], false);
        
//         $mform->addElement('select', 'busyprogrammsbcs', $this->dof->get_string('programmsbcs_create_busy', 'cpassed'), $busyprogrammsbcs, [
//             'multiple' => 'multiple'
//         ], false);


        if ( empty($cpassedoptions) AND empty($freeprogrammsbcs) )
        {//в обоих полях нет данных - выведем нотис для надежности
            $this->dof->messages->add($this->dof->get_string('form_import_to_cstream_message_no_suitable', 'cpassed'), 'notice');
        }

        //идентификатор процесса, в который выполняем импорт
        $mform->addElement('hidden','cstreamid', $cstreamid);
        $mform->setType('cstreamid', PARAM_INT);
        
        $mform->addElement('dof_confirm_submit', 'confirm_submit', 'confirm_submit',[
            'modalbuttonname' => $this->dof->get_string('form_import_to_cstream_modalbuttonname', 'cpassed'),
            'modaltitle' => $this->dof->get_string('form_import_to_cstream_modaltitle', 'cpassed'),
            'modalcontent' => $this->dof->get_string('form_import_to_cstream_modalcontent', 'cpassed'),
            'submitbuttonname' => $this->dof->get_string('form_import_to_cstream_submitbuttonname', 'cpassed'),
            'cancelbuttonname' => $this->dof->get_string('form_import_to_cstream_cancelbuttonname', 'cpassed')
        ]);
    }
    
    
    private function get_cpasseds_selectgroups_options($cstreamid)
    {
        $options = [];
        $currentcstream = $this->dof->storage('cstreams')->get($cstreamid);
        
        //список актуальных статусов (которые можно импортировать)
        $actualcpassedsstatuses = array_keys($this->dof->workflow('cpassed')->get_meta_list('actual'));
        
        $studentids = [];
        //список студентов, которые уже есть в процессе в актуальном статусе
        $currentcpasseds = $this->dof->storage('cpassed')->get_records([
            'cstreamid' => $currentcstream->id,
            'status' => $actualcpassedsstatuses
        ]);
        foreach($currentcpasseds as $currentcpassed)
        {
            $studentids[] = $currentcpassed->studentid;
        }
        
        //подписки на дисцпилины, подходящие для импорта
        $cpasseds = $this->dof->storage('cpassed')->get_records([
            'programmitemid' => $currentcstream->programmitemid,
            //'ageid' => $cstream->ageid,
            'status' => $actualcpassedsstatuses
        ], 'cstreamid');
        
        $lastcstreamid=null;
        foreach($cpasseds as $cpassed)
        {
            if( $lastcstreamid != $cpassed->cstreamid )
            {//начались подписки на дисциплины нового процесса
                if( $lastcstreamid != null AND !empty($cstreamcpasseds))
                {//есть что добавить и это не первая запись - добавляем
                    $cstream = $this->dof->storage('cstreams')->get($lastcstreamid);
                    $options[$cstream->name.' ['.$cstream->id.']']=$cstreamcpasseds;
                }
                //очищаем массив для сбора подписок по следующему процессу
                $cstreamcpasseds=[];
            }
            if( $cpassed->cstreamid != $currentcstream->id 
                AND !in_array($cpassed->studentid, $studentids) )
            {//нет смысла импортировать подписки внутри одного процесса
                //и нельзя импортировать студента, если он уже есть в текущем процессе в актуальном статусе

                //получение наименования как было в других интерфейсах
                
                //получаем подписку на программу
                if ( ! $programmsbc = $this->dof->storage('programmsbcs')->get($cpassed->programmsbcid) )
                {
                    continue;
                }
                // по каждой подписке на программу получаем контракт
                if ( ! $contract = $this->dof->storage('contracts')->get($programmsbc->contractid) )
                {// такой контракт не найден
                    continue;
                }
                // по контракту получаем ученика
                if ( ! $person = $this->dof->storage('persons')->get($contract->studentid) )
                {// ученик не зарегестрирован
                    continue;
                }
                // составляем массив для элемента select
                $cstreamcpasseds[$cpassed->id] = $person->sortname.' ['.$contract->num.']';
                //добавим код группы если есть
                if ( $agroupcode = $this->dof->storage('agroups')->get_field($programmsbc->agroupid,'code') )
                {
                    $cstreamcpasseds[$cpassed->id] .= '['.$agroupcode.']';
                }
            }
            $lastcstreamid = $cpassed->cstreamid;
        }
        //последний процесс с его подписками
        if( !empty($cstreamcpasseds) )
        {//подписки есть - добавляем
            $cstream = $this->dof->storage('cstreams')->get($lastcstreamid);
            $options[$cstream->name.' ['.$cstream->id.']'] = $cstreamcpasseds;
        }
        return $options;
    }
    
    public function validation($data, $files)
    {
        $errors = [];
        if( empty($data['cstreamid']) )
        {
            $error['cstreamid'] = $this->dof->get_string("form_import_to_cstream_message_empty_cstreamid",'cpassed');
        }
        elseif( ! $cstream = $this->dof->storage('cstreams')->get($data['cstreamid']) )
        {
            $error['cstreamid'] = $this->dof->get_string("form_import_to_cstream_message_cstream_not_exist",'cpassed');
        }
    }

    public function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() &&
            $this->is_validated() && $formdata = $this->get_data() )
        { // Форма подтверждена и данные получены
            
            $messages = [];
            $backurl = $this->dof->url_im('cpassed', '/import_to_cstream.php', $this->addvars);
            $checkedcount = 0;
            $changedcount = 0;
            //процесс, в который производится подписка
            $destinationcstreamid = $formdata->cstreamid;

            if ( !empty($formdata->cpasseds))
            {//галочки поставили
                foreach( $formdata->cpasseds as $cpassedid )
                {//эту подписку на дисциплину требуется перенести
                    $checkedcount++;
                    
                    //результат перевода
                    $moveresult = $this->dof->storage('cpassed')->move_cpassed($cpassedid, $destinationcstreamid);
                    if( $moveresult === true )
                    {//успешно перенесли процесс
                        $changedcount ++;
                    }
                    else
                    {//возникли ошибки при переносе процесса
                        foreach($moveresult as $message)
                        {
                            $messages[] = $message;
                        }
                    }
                }
            }
            if ( !empty($formdata->freeprogrammsbcs))
            {//галочки поставили
                foreach( $formdata->freeprogrammsbcs as $psbcid )
                {
                    $checkedcount++;
                    $destinationcstream = $this->dof->storage('cstreams')->get($destinationcstreamid);
                    if ( $this->dof->storage('cstreams')->enrol_student_on_cstream($destinationcstream, $psbcid) )
                    {//подписали студента на процесс
                        $changedcount ++;
                    }
                    else
                    {//не удалось подписать на процесс
                        $messages[] = $this->dof->get_string("form_import_to_cstream_message_not_enrolled",'cpassed');
                    }
                }
            }
            
            if( $checkedcount == 0 )
            {//не было отмечено ни одной подписки на дисциплины
                $messages[] = $this->dof->get_string("form_import_to_cstream_message_nothing_was_selected",'cpassed');
            }
            if($changedcount > 0)
            {//были произведены операции по подписке на процесс
                //ссылка на выбранный для перемещения учебный процесс
                $destinationcstreamlink = dof_html_writer::link(
                    $this->dof->url_im('cpassed', '/listeditor.php', $this->addvars),
                    $this->dof->get_string("form_import_to_cstream_message_goto_listeditor_cstream", 'cpassed'));

                $a = new stdClass();
                $a->replaced = $changedcount;
                $a->link = $destinationcstreamlink;
                $messages[] = $this->dof->get_string("form_import_to_cstream_message_imported", 'cpassed', $a);
            }
            //возвращаем пользователя на страницу массовых операций, отображая сообщения
            redirect($backurl, implode('<br/>', $messages), 15);
        }
    }
}
?>