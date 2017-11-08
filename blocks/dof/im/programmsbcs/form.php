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

/** Форма создания или редактирования подписки
 * @todo возможно стоит сделать форму неактивной при наступлении какого-ниюудь окончательного статуса
 */
class dof_im_programmsbcs_edit_form extends dof_modlib_widgets_form
{
    private $programmsbcs;
    private $contractid;
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

        $this->programmsbcs = $this->_customdata->programmsbcs;
        $this->dof          = $this->_customdata->dof;
        $this->contractid   = $this->_customdata->contractid;
        
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
       
        $mform->addElement('hidden','programmsbcid', $this->programmsbcs->id);
        $mform->setType('programmsbcid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // устанавливаем значения как hidden-поля, чтобы потом забрать из них
        // значения при помощи definition_after_data

        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title($this->programmsbcs->id));

        // получаем список всех подразделений и составляем для них массив для select-элемента
        $departments = $this->get_list_departments($this->programmsbcs->id);

        $mform->addElement('select', 'departmentid', $this->dof->get_string('department', 'programmsbcs'), $departments);
        // создаем меню выбора ученика
        if ( $this->programmsbcs->id AND $this->programmsbcs->status <> 'application' AND ! $this->dof->is_access('datamanage') )
        {// если форма редактируется  - запрещаем менять ученика
            $contarct = $this->dof->storage('contracts')->get($this->programmsbcs->contractid);
            $students[$this->programmsbcs->contractid] = $this->dof->storage('persons')->get_fullname($contarct->studentid).'['.$contarct->num.']';            
            $mform->addElement('select', 'contractid', $this->dof->get_string('student', 'programmsbcs'), $students);
        }elseif ( $this->contractid  )
        {// если создаем подписку с контракта - ученика менять нельзя
            $mform->addElement('hidden','contractid', $this->contractid);
            $mform->setType('contractid', PARAM_INT);
            $contract = $this->dof->storage('contracts')->get($this->contractid);
            $students[$this->contractid] = $this->dof->storage('persons')->get_fullname($contract->studentid).'['.$contract->num.']';            
            $mform->addElement('select', 'student', $this->dof->get_string('student', 'programmsbcs'), $students);
            $mform->setDefault('student', $this->contractid);
        }else
        {// если создаем новую подписку - то ученика выбрать можно
            // получаем всех учеников, на которых есть контракты
            $students    = $this->get_list_students();
            $mform->addElement('select', 'contractid', $this->dof->get_string('student', 'programmsbcs'), $students);
        }
        
        if ( $this->programmsbcs->id AND $this->programmsbcs->status <> 'application' AND ! $this->dof->is_access('datamanage') )
        {// если подписка редактируется - то группу менять можно, а программу нельзя 
            // поэтому создаем 2 разных элемента select вместо одного hierselect
            // изучаемая программа (отключено)
            $programms = $this->get_list_programms();
            $mform->addElement('select', 'programmid', $this->dof->get_string('programm', 'programmsbcs'), $programms,
            'disabled');
            $mform->setType('programmid', PARAM_INT);
            // получаем все варианты выбора для всех уровней hierselect
            $options = $this->get_select_options_no_programm($this->programmsbcs->programmid);
            // при помощи css делаем так, чтобы надписи в форме совпадали с элементами select
            $mform->addElement('html', '<div style=" line-height: 1.9; ">');
            // добавляем новый элемент выбора зависимых вариантов форму
            $myselect =& $mform->addElement('hierselect', 'agroup', 
                                           // $this->dof->get_string('programm', 'programmsbcs').':<br/>'.
                                            $this->dof->get_string('agenum',   'programmsbcs').':<br/>'.
                                            $this->dof->get_string('agroup',   'programmsbcs').':',
                                            null,'<br/>');
            // закрываем тег выравнивания строк
            $mform->addElement('html', '</div>');
            // устанавливаем для него варианты ответа
            // (значения по умолчанию устанавливаются в методе definition_after_data)
            $myselect->setOptions(array($options->agenums, $options->agroups ));
            $mform->setDefault('agroup',array($this->programmsbcs->agenum, 
                                        $this->programmsbcs->agroupid ));
            
        }else
        {// если подписка создается - то можно менять и группу и программу
            // получаем все варианты выбора для всех уровней hierselect
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
            if ( $this->programmsbcs->id )
            {
                $mform->setDefault('prog_and_agroup',array($this->programmsbcs->programmid,
                                                           $this->programmsbcs->agenum, 
                                                           $this->programmsbcs->agroupid ));
            }
        }
        
        
        // получаем все возможные формы обучения
        $eduforms = $this->get_eduforms_list();
        // создаем меню выбора формы обучения
        $mform->addElement('select', 'eduform', $this->dof->get_string('eduform', 'programmsbcs'), $eduforms);
        $mform->setType('eduform', PARAM_TEXT);
        // получаем все возможные типы обучения
        $edutypes = $this->get_edutypes_list();
        // создаем меню выбора типа обучения
        $mform->addElement('select', 'edutype', $this->dof->get_string('edutype', 'programmsbcs'), $edutypes);
        $mform->setType('edutype', PARAM_TEXT);
        $mform->setDefault('edutype','group');
        // свободное посещение
        $mform->addElement('selectyesno', 'freeattendance', $this->dof->get_string('freeattendance', 'programmsbcs'));
        $ages = $this->get_list_ages();
        
        if ( isset($this->programmsbcs->status) AND $this->programmsbcs->status <> 'application' )
        {
            unset($ages[0]);
            $mform->addElement('select', 'agestartid', $this->dof->get_string('agestart', 'sel'), $ages, 'disabled');
        }else
        {
            $mform->addElement('select', 'agestartid', $this->dof->get_string('agestart', 'sel'), $ages);
        }
        $mform->setType('agestartid', PARAM_INT);
        $options = array();
        $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
        $options['optional']  = false;
        $mform->addElement('date_selector', 'datestart', $this->dof->get_string('datestart', 'sel'), $options);
        //$mform->setType('datestart', PARAM_INT);
        // поправочный зарплатный коэффициент
        $mform->addElement('text', 'salfactor', $this->dof->get_string('salfactor','programmsbcs').':', 'size="10"');
        $mform->setType('salfactor', PARAM_TEXT);
        $mform->setDefault('salfactor', '0.00');
        
        // @todo переделать вывод полей со специальными значениями через функции
        if ( $this->programmsbcs->id )
        {//если форма редактируется
            // создаем дополнительный заголовок - "дополнительная информация"
            $mform->addElement('header','formtitle2', $this->dof->get_string('additional_info', 'programmsbcs'));
            // добавляем статические поля с информацией. Она редактируются автоматически.
            
            // структурное подразделение
            $mform->addElement('static', 'department_static', $this->dof->get_string('department','programmsbcs').':',
                                $this->get_department_name($this->programmsbcs->departmentid));
            // год обучения - приходит к нам автоматически через setData
            $mform->addElement('static', 'agenum_static', $this->dof->get_string('agenum','programmsbcs').':');
            // статус
            $mform->addElement('static', 'status', $this->dof->get_string('status','programmsbcs').':',
                                $this->get_status_name($this->programmsbcs->status));
            
            if ( $this->programmsbcs->datestart )
            {// дата начала обучения 
                $mform->addElement('static', 'datestart_static', 
                                $this->dof->get_string('datestart','programmsbcs').':',
                                dof_userdate($this->programmsbcs->datestart,"%d-%m-%Y") );
            }
            if ( $this->programmsbcs->dateend )
            {// дата окончания обучения 
                $mform->addElement('static', 'dateend_static', 
                               $this->dof->get_string('dateend','programmsbcs').':',
                                dof_userdate($this->programmsbcs->dateend,"%d-%m-%Y"));
            }
            if ( $this->programmsbcs->dateadd )
            {// если дата создания подписки указана - выведем ее
                $mform->addElement('static', 'dateadd_static', $this->dof->get_string('dateadd','programmsbcs').':',
                                dof_userdate($this->programmsbcs->dateadd,"%d-%m-%Y"));
            }
            
            if ( $this->programmsbcs->certificatenum  OR 
                 $this->programmsbcs->certificateform OR
                 $this->programmsbcs->certificateorderid )
            {// если выдано свидетельство об окончании обучения - покажем дополнительную
                // информационную секцию
                // создаем заголовок - "Свидетельство об окончании обучения"
                $mform->addElement('header','formtitle3', $this->dof->get_string('certificate_info', 'programmsbcs'));
                if ( $this->programmsbcs->certificatenum )
                {// если номер сертификата есть - выведем его
                    $mform->addElement('static', 'certificatenum_static', 
                                   $this->dof->get_string('certificatenum','programmsbcs').':',
                                   $this->programmsbcs->certificatenum);
                }
                if ( $this->programmsbcs->certificateform )
                {// если указан код формы сертификата - выведем его
                    $mform->addElement('static', 'certificateform_static', 
                                   $this->dof->get_string('certificateform','programmsbcs').':',
                                   $this->programmsbcs->certificateform);
                }
                if ( $this->programmsbcs->certificatedate )
                {// Дата выдачи свидетельства
                    $mform->addElement('static', 'certificatedate_static', 
                                    $this->dof->get_string('certificatedate','programmsbcs').':',
                                    dof_userdate($this->programmsbcs->certificatedate,"%d-%m-%Y"));
                }
                if ( $this->programmsbcs->certificateorderid )
                {// id приказа о выдаче свидетельства
                    // @todo выяснить, не нужно ли здесь лазить в таблицу orders
                    $mform->addElement('static', 'certificateorder_static', 
                                    $this->dof->get_string('certificateorder','programmsbcs').':',
                                    $this->programmsbcs->certificateorderid);
                }
            }
        }
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        // кнопки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','programmsbcs'));
    }
    
    /** Установка значений по умолчанию для сложных элементов
     * 
     * @return 
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        $id = $mform->getElementValue('programmsbcid');
        
        if ( $id AND ($history = $this->dof->storage('learninghistory')->get_first_learning_data($id)) )
        {
            $mform->setDefault('agestartid', $history->ageid);
        }  
    }
    
    /** Проверка данных на стороне сервера
     * @todo добавить строки в языковой файл для более детального вывода ошибок
     * @todo добавить проверку указания подразделения и периода
     * @return array 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        if ( $data['programmsbcid'] AND ! $this->dof->is_access('datamanage') )
        {// если форма редактируется
            // проверим существование группы
            if ( isset($data['agroup'][1]) AND $data['agroup'][1] )
            {// проверяем существование группы
                if ( ! $agroup = $this->dof->storage('agroups')->get($data['prog_and_agroup'][1]) )
                {// если она указана, но ее id не найден - то это ошибка
                    $errors['agroup'] = $this->dof->get_string('err_required_multi','programmsbcs');
                }elseif ( $agroup->programmid <> $data['programmid'] )
                {
                    $errors['agroup'] = $this->dof->get_string('error_conformity_agroup','programmsbcs');
                }elseif ( $agroup->agenum <> $data['agroup'][0] AND $agroup->status <> 'plan' )
                {
                    $errors['agroup'] = $this->dof->get_string('error_conformity_agenum','programmsbcs');
                }
            }
            // лимит объектов
            $depid = $this->dof->storage('programmsbcs')->get_field($data['programmsbcid'], 'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('programmsbcs',$data['departmentid'] ) AND $data['departmentid'] != $depid )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','programmsbcs');
            } 
        }else
        {// если создается новая подписка
            // проверим существование контракта
            if ( ! isset($data['contractid']) OR 
                 ! $contract = $this->dof->storage('contracts')->get($data['contractid']) )
            {// такой контракт не зарегестрировван
                $errors['contractid'] = $this->dof->get_string('err_required_multi','programmsbcs');
                $errors['student'] = $this->dof->get_string('err_required_multi','programmsbcs');
            }elseif( ! $this->dof->storage('persons')->is_exists($contract->studentid) )
            {// контракт существует, проверим существует ли ученик
                $errors['contractid'] = $this->dof->get_string('err_student_notexists','programmsbcs');
                $errors['student'] = $this->dof->get_string('err_student_notexists','programmsbcs');
            }
            
            // проверим существование программы
            if ( ! isset($data['prog_and_agroup'][0]) OR 
                 ! $this->dof->storage('programms')->is_exists($data['prog_and_agroup'][0]) )
            {// такая программа не существует
                $errors['prog_and_agroup'] = $this->dof->get_string('err_required_multi','programmsbcs');
            }elseif ( ! isset($data['prog_and_agroup'][2]) AND $data['prog_and_agroup'][2] )
            {// проверяем существование группы
                if ( ! $agroup = $this->dof->storage('agroups')->get($data['prog_and_agroup'][2]) )
                {// если она указана, но ее id не найден - то это ошибка
                    $errors['prog_and_agroup'] = $this->dof->get_string('err_required_multi','programmsbcs');
                }elseif ( $agroup->programmid <> $data['prog_and_agroup'][0] )
                {
                    $errors['prog_and_agroup'] = $this->dof->get_string('error_conformity_agroup','programmsbcs');
                }elseif ( $agroup->agenum <> $data['prog_and_agroup'][1] AND $agroup->status <> 'plan' )
                {
                    $errors['prog_and_agroup'] = $this->dof->get_string('error_conformity_agenum','programmsbcs');
                }
            }
            // лимит объектов
            if ( ! $this->dof->storage('config')->get_limitobject('programmsbcs',$data['departmentid'] ) )
            {
                $errors['departmentid'] = $this->dof->get_string('limit_message','programmsbcs');
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
    function get_select_options_no_programm($progid)
    {
        $result = new stdClass();
        $agroups  = array();
        // создаем массив для параллелей
        $agenums = $this->get_list_agenums($progid);
        foreach ($agenums as $num=>$agenum)
        {
            $agroups[$num] = $this->get_list_agroups($progid, $num);
        }
        // записываем в результурующий объект все что мы получили
        $result->agroups   = $agroups;
        $result->agenums   = $agenums;
        //print_object($result);
        // возвращаем все составленные массивы в упорядоченном виде
        return $result;
    }
    /** Получить список всех возможных программ обучения
     * @return array массив вариантов для элемента hierselect
     */
    private function get_list_programms()
    {
        // получаем список программ, отсортированных по алфавиту
        $programms = $this->dof->storage('programms')->get_records(array('status'=>'available'), 'name ASC');
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($programms, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        return $rez;
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
    
    /** Получить список учеников для добавления элемента select в форму
     * 
     * @return array
     */
    private function get_list_students()
    {
        // добавляем первый вариант со словом "выбрать"
        $students = array(0 => '--- '.$this->dof->modlib('ig')->igs('select').' ---');
        // извлекаем из базы все контракты
        $contracts = $this->dof->storage('contracts')->
                get_records(array('status'=>array('clientsign','wesign','work','frozen','new','tmp')));
        
        // заводим отдельный массив для учеников, чтобы потом сортировать его
        if ( $contracts )
        {// данные удалось извлечь
            foreach ($contracts as $contractid=>$record)
            {// составляем массив вида id=>ФИО
                // для этого извлекаем всех учеников из базы
                if ( ! empty($record->studentid) )
                {// если они присутствуют в контракте
                    $students[$contractid] = 
                        $this->dof->storage('persons')->get_fullname($record->studentid).'['.$record->num.']';
                }
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'contracts', 'code'=>'use'));
        $students = $this->dof_get_acl_filtered_list($students, $permissions);
        
        // сортируем массив по фамилиям учеников, чтобы было удобнее искать
        asort($students);
        // возвращаем результат вместе с надписью "выбрать"
        return $students;
    }
    
    /** Получить список подразделений для select-списка
     * @param int $id - id подписки на программу в таблице programsbcs
     * 
     * @return array
     */
    protected function get_list_departments($id)
    {
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        // добавим подразделение, которое уже присутствует в подписке, на случай если 
        // функция проверки прав случайно убрала его
        
        if ( $id )
        {// подписка редактируется
            // получаем текущий departmentid
            if ( ! $currentdeptid = $this->dof->storage('programmsbcs')->get_field($id, 'departmentid') )
            {
                return array_merge($this->dof_get_select_values(), $departments);
            }
            if ( ! $currentdept = $this->dof->storage('departments')->get($currentdeptid) )
            {
                return array_merge($this->dof_get_select_values(), $departments);
            }
            if ( ! array_key_exists($currentdept->id, $departments) )
            {
                // добавляем новый вариант в select
                $departments[$currentdept->id] = $currentdept->name.' ['.$currentdept->code.']';
            }
        }
        
        return $departments;
    }
    
    /**
     * Возвращает строку заголовка формы
     * @param int $programmsbcsid
     * @return string
     */
    private function get_form_title($programmsbcsid)
    {
        if ( ! $programmsbcsid )
        {//заголовок создания формы
            return $this->dof->get_string('newprogrammsbcs','programmsbcs');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editprogrammsbcs','programmsbcs');
        }
    }
    
    /**
     * Возврашает название статуса
     * @return unknown_type
     */
    private function get_status_name($status)
    {
        return $this->dof->workflow('programmsbcs')->get_name($status);
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
    
    /** Получить название подразделения 
     * 
     * @return string
     * @param object $departmentid
     */
    private function get_department_name($departmentid)
    {
        if ( $name = $this->dof->storage('departments')->get_field($departmentid, 'name') )
        {// возвращаем название подразделения
            return $name;
        }
        // если не удалось получить название подразделения - укажем  
        return '<i>('.$this->dof->get_string('no_specify', 'programmsbcs').')</i>';
    }
    
    /** Получить название периода обучения
     * 
     * @return string - название периода или строка "не указан" - если период не указан
     * @param object $ageid
     */
    private function get_age_name($ageid)
    {
        if ( $name = $this->dof->storage('ages')->get_field($ageid, 'name') )
        {// если название периода указано - возвращаем его
            return $name;
        }
        // если не указано - тоже скажем об этом
        return '<i>('.$this->dof->get_string('no_specify_mr', 'programmsbcs').')</i>';
    }

    /** Возвращает массив периодов 
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_ages()
    {
        if ( isset($this->programmsbcs->status) AND $this->programmsbcs->status <> 'application' )
        {// если есть id выведем все периоды
            $ages = $this->dof->storage('ages')->get_records(array());
            // 
        }else
        {
            $ages = $this->dof->storage('ages')->get_records(array('status'=>array('plan',
                                                                            'createstreams',
                                                                            'createsbc',
                                                                            'createschedule',
                                                                            'active')));
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $ages = $this->dof_get_acl_filtered_list($ages, $permissions);
        
        if ( $this->programmsbcs->id )
        {// подписка редактируется - добавляем текущий период, если вдруг функция проверки прав его выбросила
            if ( ! $history = $this->dof->storage('learninghistory')->get_first_learning_data($this->programmsbcs->id) )
            {
                return $this->dof_get_select_values($ages);
            }
            if ( ! $currentage = $this->dof->storage('ages')->get($history->ageid) )
            {
                return $this->dof_get_select_values($ages);
            }
            if ( ! array_key_exists($currentage->id, $ages) )
            {// добавляем новый вариант в select
                $ages[$currentage->id] = $currentage;
            }
        }
        
        if ( ! is_array($ages) )
        {//получили не массив - это ошибка';
            return array(0 => $this->dof->get_string('none', 'sel'));
        }
        
        return $this->dof_get_select_values($ages);
    }
}

/** Класс, отвечающий за форму смену статуса подписки на учебную программу
 * 
 */
class dof_im_programmsbcs_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'programmsbcs';
    }
    
    protected function workflow_code()
    {
        return 'programmsbcs';
    }
    
    /** Дополнительные проверки и действия в форме смены статуса 
     * (переопределяется в дочерних классах, если необходимо)
     * @param object $formdata - данные пришедние из формы
     * @param bool $result - результат прошлой операции
     * 
     * @return bool
     */
    protected function dof_custom_changestatus_checks($formdata, $result=true)
    {
        return true;
    }
}
/** Класс, отвечающий за форму смену статуса подписки на учебную программу
 * 
 */
class dof_im_programmsbcs_changeagenum_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    public $dof;
    
    /** Объявление класса формы
     * 
     */
    function definition()
    {
        $mform     = $this->_form;
        $this->dof = $this->_customdata->dof;
        // устанавливаем id периода
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('change_agenum_manual', 'programmsbcs'));
    }
    
    /** Объявление внешнего вида после установки данных по умолчанию  
     * 
     * @return null
     */
    function definition_after_data()
    {
        $mform = $this->_form;
        // получаем элемент
        $id = $mform->getElementValue('id');
        // получаем запись из базы по переданному id
        $programmid = $this->dof->storage('programmsbcs')->get_field($id,'programmid');
        $agroupid = $this->dof->storage('programmsbcs')->get_field($id,'agroupid');
        $result = array();
        // добавляем первый вариант со словом "Индивидуально"
//        $result[0] = $this->dof->get_string('no','programmsbcs');
        if ( ! $programm = $this->dof->storage('programms')->get($programmid) )
        {// переданная учебная программа не найдена
            return $result;
        }
        // заполняем массив данными
        for ( $i=1; $i<=$programm->agenums; $i++ )
        {
            $result[$i] = $i.' '; // пустой пробел в конце обязателен
        }
        $mform->addElement('select', 'agenum', $this->dof->get_string('agenum', 'programmsbcs').':', $result);
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
        $mform->addElement('submit', 'save', $this->dof->get_string('change_agenum', 'programmsbcs'));
        if ( isset($agroupid) )
        {
            $lh = $this->dof->storage('agrouphistory')->get_actual_learning_data($agroupid);
            if ( ! empty($lh) )
            {
                $mform->setDefault('agenum', $lh->agenum);
                $mform->setDefault('ageid', $lh->ageid);
            }
        } else
        {
            $lh = $this->dof->storage('learninghistory')->get_actual_learning_data($id);
            $mform->setDefault('agenum', $lh->agenum);
            $mform->setDefault('ageid', $lh->ageid);
        }
    }
    
    /** Проверки данных формы
     * 
     */
    function validation($data, $files)
    {
        $errors = array();
        
        if ( ! isset($data['id']) OR ! $data['id'] )
        {// не найдена запись - не можем изменить ее данные
            $errors['status'] = $this->dof->get_string('error', 'programmsbcs');
        }
        $agroupid = $this->dof->storage('programmsbcs')->get_field($data['id'],'agroupid');
        if ( $agroupid AND $this->dof->storage('agroups')->get_field($agroupid,'status') != 'plan' )
        {// статус группы не тот, не разрешаем менять
            $errors['agenum'] = $this->dof->get_string('invalid_agenum', 'programmsbcs');
        }
        // возвращаем все возникшие ошибки, если они есть
        return $errors;
    }
}

/** Класс формы для поиска подписки на программу
 * 
 */
class dof_im_programmsbcs_search_form extends dof_modlib_widgets_form
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
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('search','programmsbcs'));
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);        
        // поле "период"
        // @todo пока н7е используется. Будет заменено на "параллель"
        //$ages = $this->get_list_ages();
        //$mform->addElement('select', 'ageid', $this->dof->get_string('age','programmsbcs').':', $ages);
        //$mform->setType('ageid', PARAM_INT);
        // поле "программа"
        $pitems = $this->get_list_programms();
        $mform->addElement('select', 'programmid', $this->dof->get_string('programm','programmsbcs').':', $pitems);
        $mform->setType('programmid', PARAM_INT);
        // поле "ученик"
        
        /* TODO расскоментировать, когда будет работать фо AJAX форме
        $agroups = $this->get_list_agroups();
        $mform->addElement('select', 'agroupid', $this->dof->get_string('agroup','programmsbcs').':', $agroups);
        $mform->setType('agroupid', PARAM_INT);
        // поле "ученик"
        $students = $this->get_list_contracts();
        $mform->addElement('select', 'contractid', $this->dof->get_string('contract','programmsbcs').':', $students);
        $mform->setType('studentid', PARAM_INT);
        */
        
        // поле "статус"
        $statuses = $this->get_list_statuses();
        $mform->addElement('select', 'status', $this->dof->get_string('status','programmsbcs').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->get_string('to_find','programmsbcs'));
    }
    
    /** Получить список всех учебных программ в нужном для формы поиска формате 
     * 
     * @return array массив для создания select-элемента
     */
    private function get_list_programms()
    {
        // получаем список программ, отсортированных по алфавиту
        $programms = $this->dof->storage('programms')->get_records(array('status'=>'available'), 'name ASC');
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($programms, true, 'id', array('name', 'code'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'programms', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        return $rez;
    }
    
    /** Получить список всех структурных отделов в нужном для формы поиска формате
     * 
     * @return array массив 
     */
    private function get_list_ages()
    {
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('ages')->get_records(array(
                'status'=>array('plan',
                                'createstreams',
                                'createsbc',
                                'createschedule',
                                'active')));
        // преобразуем список записей в нужный для select-элемента формат  
        $rez = $this->dof_get_select_values($rez, true, 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        return $rez;
    }
    
    
    /** Получить список учеников для добавления элемента select в форму
     * @todo определить, контракты с каким статусом отображать
     * @return array
     */
    private function get_list_contracts()
    {
        // извлекаем из базы все контракты
        $select = $this->dof->storage('contracts')->get_records(array());
        
        // заводим отдельный массив для учеников, чтобы потом сортировать его
        if ( $select )
        {// данные удалось извлечь
            foreach ($select as $id=>$record)
            {// составляем массив вида id=>ФИО
                // для этого извлекаем всех учеников из базы
                if ( ! empty($record->studentid) )
                {// если они присутствуют в контракте
                    $students[$id] = 
                        $this->dof->storage('persons')->get_fullname($record->studentid).'['.$record->num.']';
                }
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'contracts', 'code'=>'use'));
        $students = $this->dof_get_acl_filtered_list($students, $permissions);
        
        // сортируем массив по фамилиям учеников, чтобы было удобнее искать
        asort($students);
        // возвращаем результат вместе с надписью "выбрать"
        return $students;
    }
    /** 
     * 
     * @return array
     */
    private function get_list_agroups()
    {
        $result = array();
        // получаем все программы
        $agroups = $this->dof->storage('agroups')->get_records(array('programmid'=>$programmid));
        $this->dof_get_select_values($agroups);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'agroups', 'code'=>'use'));
        $result = $this->dof_get_acl_filtered_list($result, $permissions);
        
        return $result;
    }
    
    /** Получить список всех доступных статусов учебного потока
     * 
     * @return array
     */
    private function get_list_statuses()
    {
        $statuses    = array();
        // добавляем значение, на случай, если по статусу искать не нужно
        $statuses[0] = '--- '.$this->dof->get_string('any_mr','programmsbcs').' ---';
        // получаем весь список статусов через workflow
        $statuses    = array_merge($statuses, $this->dof->workflow('programmsbcs')->get_list());
        // возвращаем список всех статусов
        return $statuses;
    }
}
?>