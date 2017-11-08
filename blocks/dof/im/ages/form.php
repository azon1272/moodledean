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

class dof_im_ages_edit_age_form extends dof_modlib_widgets_form
{
    protected $dof;
    
    function definition()
    {
        // Делаем глобальные переменные видимыми
        $this->dof = $this->_customdata->dof;
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Получаем ID учебного периода(редактирование)
        $ageid = $this->_customdata->ageid;
        // Получаем текущее подразделение
        $depid = optional_param('departmentid', 0, PARAM_INT);
        
        // Скрытые поля 
        $mform->addElement('hidden','ageid', $ageid);
        $mform->setType('ageid', PARAM_INT);
        $mform->addElement('hidden','departmentid', $depid);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // [ Имя периода ]
        $mform->addElement('text', 'name', $this->dof->get_string('name','ages').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name',$this->dof->get_string('agename_required', 'ages'), 'required',null,'client');
        
        // [ Даты ]
        // Сформируем массивы опций
        $beginopt = array();
        $endopt = array();
        if ( ! empty($ageid) )
        {// Если редактируем период
            // Получим подразделение учебного периода
            $agedepid = $this->dof->storage('ages')->get_field($ageid, 'departmentid');
            if ( ! empty($agedepid) )
            {
                // Получаем часовой пояс подразделения, к которому относится период
                $timezone = $this->dof->storage('departments')->get_field($agedepid, 'zone');
                // Добавляем зону к опциям для корректного отображения даты 
                $beginopt['timezone'] = $timezone;
                $endopt['timezone'] = $timezone;
            }
        }
        // Добавим установку корректного времени для конца учебного периода
        $endopt['hours'] = 23;
        $endopt['minutes'] = 55;
        
        // Сформируем поля выбора даты
        $mform->addElement('dof_date_selector', 'begindate', $this->dof->get_string('begindate','ages').':', $beginopt);
        $mform->addElement('dof_date_selector', 'enddate', $this->dof->get_string('enddate','ages').':', $endopt);
        
        // количество недель
        $mform->addElement('text', 'eduweeks', $this->dof->get_string('eduweeks','ages').':', 'size="4"');
        $mform->addRule('eduweeks','Error', 'numeric',null,'client');
        $mform->setType('eduweeks', PARAM_INT);
        // подразделение и предыдущий период
        $mform->addElement('hierselect', 'departprevious', $this->dof->get_string('departprevious','ages').':',null,'<br>');
        
        // Заголовок блока - Настройка учебной недели
        $mform->addElement('html', html_writer::tag('h2', $this->dof->get_string('form_block_learningweek','ages')));
        // кол-во дней в неделе
        $mform->addElement('text', 'schdays', $this->dof->get_string('schdays','ages').':', 'size="4"');
        $mform->setType('schdays', PARAM_INT);
        
        // список дней в неделе
        $mform->addElement('text', 'schedudays', $this->dof->get_string('schedudays','ages').':', 'size="20"');
        $mform->setType('schedudays', PARAM_TEXT);
        // номер первого дня периода
        $mform->addElement('text', 'schstartdaynum', $this->dof->get_string('schstartdaynum','ages').':', 'size="4"');
        $mform->setType('schstartdaynum', PARAM_INT);
        
        // Заголовок блока - Настройка дней учебной недели
        $mform->addElement('html', html_writer::tag('h2', $this->dof->get_string('form_block_learningdays','ages')));
        // Использовать календарные названия дней
        $mform->addElement('checkbox', 'useweekdaynames', $this->dof->get_string('standartweek', 'ages').':');
        $mform->setType('useweekdaynames', PARAM_INT);
        $mform->setDefault('useweekdaynames', 0);
        
        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, $this->dof->get_string('to_save','ages'));
    }
    
    /** Добавление дополнительльных полей формы и установка значений по умолчанию
     * после загрузки данных в форму (если происходит редактирование)
     * 
     * @return null
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // узнаем id периода (если он есть)
        $id = $mform->getElementValue('ageid');
        // добавляем заголовок формы
        $header =& $mform->createElement('header','form_title', $this->get_form_title($id));
        $mform->insertElementBefore($header, 'ageid');
        // установим подразделение и предыдущий период
        $departments = $this->dof->storage('departments')->departments_list_subordinated(null,'0', null,true);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'departments', 'code'=>'use'));
        $departments = $this->dof_get_acl_filtered_list($departments, $permissions);
        $previous = $mform->getElement('departprevious');
        $previous->setMainOptions($departments);
        $previous->setSecOptions($this->get_list_previous($departments,$id,
            $this->dof->storage('ages')->get_field($id, 'previousid')));
        // установим значения
        if ( empty($id) )
        {// для создания - текущее время
            $beginyear = $endyear = $this->get_year(time());
            $mform->setDefault('schdays', '7');
            $mform->setDefault('schedudays', '1,2,3,4,5');
            $mform->setDefault('schstartdaynum', '');
        }else
        {// при редактировании поставим значения из объекта
            $beginyear = $this->dof->storage('ages')->get_field($id,'begindate');
            $endyear   = $this->dof->storage('ages')->get_field($id,'enddate');
            $mform->setDefault('departprevious',array(
                    $this->dof->storage('ages')->get_field($id,'departmentid'), 
                    $this->dof->storage('ages')->get_field($id,'previousid')));
        }
        $mform->setDefault('begindate', $beginyear);
        $mform->setDefault('enddate', $endyear);
        
        // Получим опцию названий дней недели
        $useweekdaynames = $this->dof->storage('ages')->get_custom_option(
                'useweekdaynames',
                $id
        );
        $mform->setDefault('useweekdaynames', $useweekdaynames);
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** 
     * Проверка на стороне сервера
     * 
     * @param array data - данные из формы
     * @param array files - файлы из формы
     * 
     * @return array - массив ошибок
     */
    function validation($data,$files)
    {
        $errors = array();
        
        // Лимиты 
        if ( $data['ageid'] == 0 AND !$this->dof->storage('config')->get_limitobject('ages',$data['departprevious'][0]) )
        {// достигнут лимит создания
            $errors['departprevious'] = $this->dof->get_string('limit_message','ages');
        }
        if ( $data['ageid'] )
        {// редактирование
            $depid = $this->dof->storage('ages')->get_field($data['ageid'], 'departmentid');
            if ( ! $this->dof->storage('config')->get_limitobject('ages',$data['departprevious'][0] ) AND $depid != $data['departprevious'][0]  )
            {// в отредактированном подразделении достигнут лимит
                $errors['departprevious'] = $this->dof->get_string('limit_message','ages');
            }
        }
                 
        if ( $data['begindate']['timestamp'] > $data['enddate']['timestamp'] )
        {// дата начала больше даты конца - выведем сообщение
            $errors['begindate'] = $this->dof->get_string('errorbeginenddate','ages');
        } 
        
        if ( isset($data['useweekdaynames'])  && $data['schdays'] != 7 )
        {
            $errors['useweekdaynames'] = $this->dof->get_string('error_invalid_schdays', 'ages');
        }
        
        // Возвращаем ошибки, если они возникли
        return $errors;
    }
    
    /** Возвращает двумерный массив подразделений и периодов
     * @param array $department - список подразделений
     * @param int $ageid - текущий период
     * @param int $previousid - текущий предыдущий период
     * @return array список периодов, массив(id подразделения=>id периода=>название периода)
     */
    private function get_list_previous($departments,$ageid,$previousid)
    {
        $previous = array();
        if ( ! is_array($departments) )
        {//получили не массив - это значит что в базен нет ни одного подразделения
            return $previous;
        }
        foreach ($departments as $key => $value)
        {// забиваем массив данными    
            $previous[$key] = $this->get_list_ages($key,$ageid,$previousid);
        }
        return $previous;
    }
    
    /** Возвращает массив периодов для текущего структурного подразделения
     * @param int $departmentid - id подразделения
     * @param int $ageid - текущий период
     * @param int $previousid - текущий предыдущий период
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_ages($departmentid,$ageid,$previousid)
    {
        $params = array();
        $params['departmentid'] = $departmentid;
        $params['noid'] = $ageid;
        $params['status'] = array_keys($this->dof->workflow('ages')->get_meta_list('real'));
        $select = $this->dof->storage('ages')->get_select_listing($params);
    	$ages = $this->dof->storage('ages')->get_records_select($select);
    	if ( ! is_array($ages) )
        {//получили не массив - периодов нет
            return array(0 => $this->dof->modlib('ig')->igs('no'));
        }
        $rez = array();
        foreach ( $ages as $age )
        {// забиваем массив данными
            if ( ! $this->dof->storage('ages')->get_next_ageid($age->id,2)  )
            {// занесем только те периоды, у которых нет последующих
                $rez[$age->id] = $age->name;
            }
        }
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        if ( isset($previousid) )
        {// если уже был предыдущий период, выставим его по умолчанию
            $rez[$previousid] = $this->dof->storage('ages')->get_field($previousid,'name');
        }
        // сортируем в алфавитном порядке
        asort($rez);
        $rez = array(0 => $this->dof->modlib('ig')->igs('no')) + $rez;
        return $rez;
    }
    
    /** Возвращает строку заголовка формы
     * @param int $ageid - id периода
     * @return string
     */
    private function get_form_title($ageid)
    {
        if ( ! $ageid )
        {//заголовок создания формы
            return $this->dof->get_string('newages','ages');
        }else 
        {//заголовок редактирования формы
            return $this->dof->get_string('editage','ages');
        }
        
    }
    
    /** Возвращает год для date_selector
     * @param int $date - дата
     * @return array
     */
    private function get_year($date)
    {
        $dateform = array();
        $dateform['startyear'] = dof_userdate($date-1*365*24*3600,'%Y');
        $dateform['stopyear']  = dof_userdate($date+10*365*24*3600,'%Y');
        $dateform['optional']  = false;
        return $dateform;
    }
    
    /** Обработчик формы
     * @return string - сообщение об ошибке
     */
    public function process()
    {
        $add = array();
        $add['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        // переменная, хранящая результат операции сохранения
        $reslut = true;
        // создаем путь на возврат
        $path = $this->dof->url_im('ages','/list.php',$add);
        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу просмотра периода
            redirect($path);
        }elseif ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {//даные переданы в текущей сессии - получаем
            // Создаем объект для сохранения в БД
            $age = new stdClass();
            
            if (isset($formdata->departprevious))
            {// Подразделение редактировалось
                $age->departmentid = $formdata->departprevious[0];
                if ( $formdata->departprevious[1] <> 0)
                {// У подразделения уже были предыдущие периоды
                    $age->previousid = $formdata->departprevious[1];
                }else
                {// Предыдущих периодов нет
                    $age->previousid = null;
                }
            } else
            {// подразделение не редактировалось
                $age->departmentid = $formdata->departmentid;
                if ( $formdata->previous <> 0)
                {// у подразделения уже были предыдущие периоды
                    $age->previousid = $formdata->previous;
                }else
                {// предыдущих периодов нет
                    $age->previousid = null;
                }
            }
            
            
            // Получаем часовой пояс подразделения, к которому относится период
            $timezone = $this->dof->storage('departments')->get_field($age->departmentid, 'zone');
            // Устанавливаем дату по подразделению, к которому принадлежит период
            $age->begindate = dof_make_timestamp(
                    $formdata->begindate['year'],
                    $formdata->begindate['month'],
                    $formdata->begindate['day'],
                    $formdata->begindate['hours'],
                    $formdata->begindate['minutes'],
                    $formdata->begindate['seconds'],
                    $timezone
                    );
            $age->enddate = dof_make_timestamp(
                    $formdata->enddate['year'],
                    $formdata->enddate['month'],
                    $formdata->enddate['day'],
                    $formdata->enddate['hours'],
                    $formdata->enddate['minutes'],
                    $formdata->enddate['seconds'],
                    $timezone
                    );
            $age->name = $formdata->name;
            $age->eduweeks = $formdata->eduweeks;
            $age->schdays = $formdata->schdays;
            $age->schedudays = $formdata->schedudays;
            $age->schstartdaynum = $formdata->schstartdaynum;
            
            if ( empty($formdata->schstartdaynum) )
            {
                $daynumber = dof_userdate($age->begindate,'%w');
                if ( $daynumber == '0' )
                {
                    $daynumber = 7;
                }
                $age->schstartdaynum = $daynumber;
            }
            
            if ( $formdata->ageid )
            {// шаблон редактируется - обновляем запись
                $id = $age->id = $formdata->ageid;
                $reslut = $reslut AND (bool)$this->dof->storage('ages')->update($age);
            }else
            {// шаблон добавляется - обновляем запись
                $id = $this->dof->storage('ages')->insert($age);
                $reslut = $reslut AND (bool)$id;
            }
            
            // Получим опцию useweekdaynames
            $option = $this->dof->storage('ages')->get_custom_option(
                    'useweekdaynames', $age->id
            ); 
                   
            
            if ( isset($formdata->useweekdaynames) )
            {// Сохраним значение
                $this->dof->storage('ages')->save_custom_option(
                        'useweekdaynames', 
                        $formdata->ageid, 
                        $formdata->useweekdaynames
                );
            } else 
            {
                // Удалим значение
                $this->dof->storage('ages')->save_custom_option(
                        'useweekdaynames',
                        $formdata->ageid,
                        NULL
                );
            }
            
            if ( $reslut )
            {// если все успешно - делаем редирект
                redirect($this->dof->url_im('ages','/view.php?ageid='.$id,$add));
            }
            return $reslut;
        }
    }
    
}


/** Класс, отвечающий за форму смену статуса учебного периода вручную
 * 
 */
class dof_im_ages_changestatus_form extends dof_modlib_widgets_changestatus_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    protected function im_code()
    {
        return 'ages';
    }
    
    protected function workflow_code()
    {
        return 'ages';
    }
    
     /** Дополнительные проверки и действия в форме смены статуса 
     * (переопределяется в дочерних классах, если необходимо)
     * @param object $formdata - данные пришедние из формы
     * 
     * @return bool
     */
    protected function dof_custom_changestatus_checks($formdata, $result=true)
    {
        // @todo - учитель перестал быть причиной невозможности смены статуса
        /*if ( ! $result )
        {// если не удалось сменить статус - то возможно это из-за учителей
            if ( $cstreams = $DOF->storage('cstreams')->get_records(array('ageid'=>$formdata->id)) )
            {// если у периода есть потоки
                foreach ( $cstreams as $cstream )
                {
                    if ( $cstream->teacherid == 0 )
                    {// не указан учитель потока
                        $message = '<div style="color:red;"><b>'.
                                    $DOF->get_string('no_teacher', $this->im_code()).'</b></div>';
                        $link = $this->dof->url_im('cstreams'. '/view.php', array('cstreamid' => $cstream->id));
                        $message .= '<a href="'.$link.'">'.$cstream->name.'</a>';
                        $mform->addElement('static', 'noteacher'.$cstream->id, '', $message);
                    }
                }
            }
            
            return false;
        }*/
        return true;
    }
    
}


/** Форма с кнопкой пересинхронизации всех cpassed за период
 * @todo в сообщении выводить когда было добавлено задание на пересинхронизацию
 * @todo выводить когда была последняя пересинхронизация
 * @todo добавить notice_yesno после нажатии на кнопку
 * @todo Переместить объявление кнопки в definition_after_data чтобы она всегда отражала актуальные изменения в базе
 */
class dof_im_ages_resync_form extends dof_modlib_widgets_form
{
    /**
     * @param int - id учебного периода в таблице ages
     */
    protected $id;
    protected $action;
    
    protected function im_code()
    {
        return 'ages';
    }

    public function definition()
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        if ( ! $this->id  = $this->_customdata->id )
        {// не можем отобразить форму без периода
            $this->dof->print_error('err_age_not_exists', $this->im_code());
        }
        
        $mform->addElement('hidden', 'id', $this->id);
        $mform->addElement('hidden', 'action', optional_param('action', 'none', PARAM_TEXT));
        $mform->setType('id', PARAM_INT);
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('resync',$this->im_code()));
    }
    
    /** Объявление внешнего вида после установки данных по умолчанию  
     */
    public function definition_after_data()
    {
        // добавляем элементы для работы со сменой статуса
        $this->get_todo_submit();
    }
    
    /** Получить кнопки пересинхронизации
     * @return null
     */
    private function get_todo_submit()
    {
        GLOBAL $DB;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        if ( $mform->elementExists('save') )
        {// заменим поле
            $mform->removeElement('save');
        }
        if ( $mform->elementExists('resync_notice') )
        {// заменим поле
            $mform->removeElement('resync_notice');
        }
        if ( $mform->elementExists('sus_go') )
        {// заменим поле
            $mform->removeElement('sus_go');
        }
        if ( $mform->elementExists('active_notice') )
        {// заменим поле
            $mform->removeElement('active_notice');
        }
        if ( $mform->elementExists('act_stop') )
        {// заменим поле
            $mform->removeElement('act_stop');
        }
        if ( $mform->elementExists('stop_notice') )
        {// заменим поле
            $mform->removeElement('stop_notice');
        }
        
        // Кнопка задачи по активации учебных процессов
        // @todo на текущий момент в ядре нет нормального API для работы с таблицей todo
        // поэтому здесь используется прямое обращение к get_records_select
        $somevars = [
            'ageid' => optional_param('ageid', $this->id, PARAM_INT), 
            'departmentid' => optional_param('departmentid', 0, PARAM_INT)
        ];
        
        $activemeta = (array)$this->dof->workflow($this->im_code())->get_meta_list('active');
        if ( $this->dof->storage($this->im_code())->get_record(['id' => $this->id, 'status' => array_keys($activemeta)]) )
        {// Учебный период активен 
            if ( $cstreams = $this->dof->storage('cstreams')->get_records(['ageid' => $this->id, 'status' => 'plan']) )
            {// Есть запланированные учебные процессы
                
                if ( $todo = $this->dof->storage('cstreams')->get_todo('activate_cstreams', $this->id) )
                {// Задание на исполнение найдено
                    $link = dof_html_writer::link(
                        $this->dof->url_im('ages', '/activate_cstreams.php', $somevars), 
                        $this->dof->get_string('activate_cstreams', $this->im_code()), 
                        ['class' => 'btn button dof_button without_margin']
                    );
                    $mform->addElement('static', 'link_to_activate_cstreams', '', $link);
                } else 
                {// Задание не найдено
                    $link = dof_html_writer::link(
                        $this->dof->url_im('ages','/activate_cstreams.php', $somevars),
                        $this->dof->get_string('activate_cstreams', $this->im_code()),
                        ['class' => 'btn button dof_button without_margin']
                    );
                    $mform->addElement('static', 'link_to_activate_cstreams', '', $link);
                }
            } else 
            {// Запланированных учебных процессов не найдено
                $mform->addElement(
                    'static', 
                    'activate_cstreams_list_empty', 
                    '', 
                    $this->dof->get_string('activate_cstreams_list_empty', $this->im_code())
                );
            }
        }
        
        $mform->addElement('static','resync_notice','','<b>'.$this->dof->get_string('resync_notice',$this->im_code()).'</b>');
        if ( $DB->get_records('block_dof_todo',array('exdate'=>0,'plugintype'=>'storage', 
             'plugincode'=>'cpassed','todocode'=>'resync_age_cpassed','intvar'=>$this->id)) )
        {// в базе уже есть добавленное необработанное задание на пересинхронизацию всех потоков курса
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('static', 'save', '', 
                        $this->dof->get_string('resync_task_added',$this->im_code()));
        }else
        {// задание еще не добавлено - показываем кнопку
            $mform->addElement('submit', 'save', $this->dof->get_string('resync_cstreams',$this->im_code()));
        }
        
        // Кнопкa АКТИВАЦИИ ВСЕХ cpassed этого периода
        $mform->addElement('static','active_notice','','<b>'.$this->dof->get_string('active_notice',$this->im_code()).'</b>');
        if ( $DB->get_records('block_dof_todo',array('exdate'=>0,'plugintype'=>'storage', 
             'plugincode'=>'cpassed','todocode'=>'suspend_to_active_cpassed','intvar'=>$this->id)) )
        {// в базе уже есть добавленное необработанное задание на пересинхронизацию всех потоков курса
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('static', 'sus_go', '', 
                        $this->dof->get_string('active_suspend',$this->im_code(),$this->dof->get_string('suspend_go',$this->im_code())) );
        }else 
        {// задание еще не добавлено - показываем кнопку
            $mform->addElement('submit', 'sus_go', $this->dof->get_string('suspend_go',$this->im_code()));
        }
        // Кнопкa ПРИОСТАНОВКИ ВСЕХ cpassed этого периода  
        $mform->addElement('static','stop_notice','','<b>'.$this->dof->get_string('stop_notice',$this->im_code()).'</b>');      
        if ( $DB->get_records('block_dof_todo',array('exdate'=>0,'plugintype'=>'storage',
             'plugincode'=>'cpassed','todocode'=>'active_to_suspend_cpassed','intvar'=>$this->id)) )
        {// в базе уже есть добавленное задание 
            // не показывем кнопку, чтобы нельзя было добавить одно задание несколько раз, и перегрузить систему
            $mform->addElement('static', 'act_stop', '', 
                        $this->dof->get_string('active_suspend',$this->im_code(),$this->dof->get_string('active_stop',$this->im_code())) );
        }else 
        {// задание еще не добавлено - показываем кнопку
            $mform->addElement('submit', 'act_stop', $this->dof->get_string('active_stop',$this->im_code()));
        }
        
    }
    
    /** Обработчик формы
     * 
     */
    public function process()
    {
        $mform =& $this->_form;
        
        if ( $formdata = $this->get_data() AND $this->dof->is_access('manage') AND confirm_sesskey() )
        {// пришли данные из формы
             if ( isset($formdata->save) )
             {// пересинхронизация всех подписок
                 $mform->setConstant('action','save');
                 $this->dof->add_todo('storage', 'cpassed', 'resync_age_cpassed', $formdata->id, null, 2, time());
             }
             if ( isset($formdata->sus_go) )
             {// запуск всех приостановленных
                 $mform->setConstant('action','sus_go');
                 $this->dof->add_todo('storage', 'cpassed', 'suspend_to_active_cpassed', $formdata->id, null, 2, time());
             }
             if ( isset($formdata->act_stop) )
             {// остановка всех активных
                 $mform->setConstant('action','act_stop');
                 $this->dof->add_todo('storage', 'cpassed', 'active_to_suspend_cpassed', $formdata->id, null, 2, time());
             }
             // обновим данные поля
             $this->get_todo_submit();
        }
        return 0;
    }
    
}


/** Класс формы для поиска периодов
 * 
 */
class dof_im_ages_search_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    private $addvars;
    function definition()
    {
        $this->dof = $this->_customdata->dof;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->modlib('ig')->igs('search'));
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        // поле "название"
        $mform->addElement('text', 'name', $this->dof->modlib('ig')->igs('name').':', 'size="20"');
        $mform->setType('name', PARAM_TEXT);
        // поле "статус"
        $statuses    = array();
        $statuses[0] = '--- '.$this->dof->modlib('ig')->igs('select').' ---';
        $statuses    = array_merge($statuses, $this->dof->workflow('ages')->get_list());
        $mform->addElement('select', 'status', $this->dof->modlib('ig')->igs('status').':', $statuses);
        $mform->setType('status', PARAM_TEXT);
        // кнопка "поиск"
        $this->add_action_buttons(false, $this->dof->modlib('ig')->igs('find'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}

/**
 * Класс формы активации/деактивации учебных процессов для активного периода
 * 
 * @author     Ivanov Dmitry <ivanov@opentechnology.ru>
 * @copyright  2016
 */
class dof_im_ages_activate_cstreams extends dof_modlib_widgets_form
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
     * ID учебного периода
     * 
     * @param int
     */
    protected $id;
    
    
    protected $action;
   
    /**
     * Получить текущий код плагина
     * 
     * @return string
     */
    protected function im_code()
    {
        return 'ages';
    }
    
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
        if ( ! $this->id = $this->_customdata->id ) 
        {// ID учебного периода не указан
            $this->dof->messages->add(
                get_string('err_age_not_exists', $this->im_code()), 
                'error'
            );
        }
        
        $checkboxes = [];
        $cstreamids = $old_cstreamids = null;
        
        // Получение учебного периода
        $age = $this->dof->storage('ages')->get($this->id);
        if ( isset($age->status) && $age->status == 'active' ) 
        {// Учебный период активен
            if ($cstreams = $this->dof->storage('cstreams')->get_join_cstreams($this->id, 'plan')) 
            { // если есть запланированные процессы
                // Поле для вывода сообщений об ошибках скрытых элементов
                $mform->addElement(
                    'static',
                    'hidden',
                    ''
                );
                $mform->addElement('static', 'notice_for_activate_cstreams', '', $this->dof->get_string('check_cstreams', $this->im_code()));
                $this->add_checkbox_controller(1, $this->dof->get_string('check_all', $this->im_code()), null, 1);
                if ( $todo = $this->dof->storage('cstreams')->get_todo('activate_cstreams', $this->id) ) 
                { // если есть не исполненное задание на активацию
                  // узнаем, есть ли запланированные процессы, которые не попали в задание на активацию
                    $res = false;
                    $todo->mixedvar = unserialize($todo->mixedvar);
                    if( $already_activated_cstreams = $this->dof->storage('cstreams')->get_join_cstreams_by_id($this->id, $todo->mixedvar->id) )
                    {
                        foreach($already_activated_cstreams as $k => $v)
                        {
                            $name = $v->name . ' (' . $this->dof->get_string('program', $this->im_code()) . ': ' . $v->pname . '; ' . $this->dof->get_string('discipline', $this->im_code()) . ': ' . $v->piname . ')';
                            $checkboxes[] = $mform->createElement('advcheckbox', $v->id, '', $name, ['group' => 2]);
                            $mform->setDefault('freezecstreams['.$v->id.']', 1);
                        }
                        $mform->addGroup($checkboxes, 'freezecstreams', $this->dof->get_string('active_cstreams', $this->im_code()), '<br>');
                        $mform->freeze('freezecstreams');
                    }
                    if ($todo->mixedvar->id) 
                    { // если есть какие-то запланированные процессы в задании
                        foreach ($cstreams as $cstream) 
                        {
                            if ($temp_res = in_array($cstream->id, $todo->mixedvar->id)) {
                                $res = $temp_res;
                                $old_cstreamids[] = [
                                    'id' => $cstream->id, 
                                    'name' => $cstream->name,
                                    'pname' => $cstream->pname ? $cstream->pname: $this->dof->get_string('empty_program', $this->im_code()),
                                    'piname' => $cstream->piname ? $cstream->piname: $this->dof->get_string('empty_discipline', $this->im_code())
                                ];
                            } else 
                            {
                                $res = true;
                                $cstreamids[] = [
                                    'id' => $cstream->id, 
                                    'name' => $cstream->name,
                                    'pname' => $cstream->pname ? $cstream->pname: $this->dof->get_string('empty_program', $this->im_code()),
                                    'piname' => $cstream->piname ? $cstream->piname: $this->dof->get_string('empty_discipline', $this->im_code())
                                ];
                            }
                        }
                    } else 
                    { // если нет запланированных процессов в задании, ничего не проверяем
                        $res = true;
                        foreach ($cstreams as $cstream)
                        {
                            $cstreamids[] = [
                                'id' => $cstream->id, 
                                'name' => $cstream->name,
                                'pname' => $cstream->pname ? $cstream->pname: $this->dof->get_string('empty_program', $this->im_code()),
                                'piname' => $cstream->piname ? $cstream->piname: $this->dof->get_string('empty_discipline', $this->im_code())
                            ];
                        }
                    }
                    if ($res) 
                    { // если есть запланированные процессы, которые не попали в задание на активацию
                        // сначала выведем те, что уже есть в задании и отметим их
                        $checkboxes = [];
                        if( $old_cstreamids )
                        {
                            foreach($old_cstreamids as $k => $v)
                            {
                                $name = $v['name'] . ' (' . $this->dof->get_string('program', $this->im_code()) . ': ' . $v['pname'] . '; ' . $this->dof->get_string('discipline', $this->im_code()) . ': ' . $v['piname'] . ')';
                                $checkboxes[] = $mform->createElement('advcheckbox', $v['id'], '', $name, ['group' => 1]);
                                $mform->setDefault('cstreams['.$v['id'].']', 1);
                            }
                        }
                        // потом выведем те, которых нет в задании и оставим их неотмеченными
                        if( $cstreamids )
                        {
                            foreach($cstreamids as $k => $v)
                            {
                                $name = $v['name'] . ' (' . $this->dof->get_string('program', $this->im_code()) . ': ' . $v['pname'] . '; ' . $this->dof->get_string('discipline', $this->im_code()) . ': ' . $v['piname'] . ')';
                                $checkboxes[] = $mform->createElement('advcheckbox', $v['id'], '', $name, ['group' => 1]);
                                $mform->setDefault('cstreams['.$v['id'].']', 0);
                            }
                        } 
                        $mform->addGroup($checkboxes, 'cstreams', $this->dof->get_string('cstreams', $this->im_code()), '<br>');
                    }
                } else 
                { // если нет задания на активацию
                    $checkboxes = [];
                    foreach($cstreams as $cstream)
                    {
                        $name = $cstream->name . ' (' . $this->dof->get_string('program', $this->im_code()) . ': ' . $cstream->pname . '; ' . $this->dof->get_string('discipline', $this->im_code()) . ': ' . $cstream->piname . ')';
                        $checkboxes[] = $mform->createElement('advcheckbox', $cstream->id, '', $name, ['group' => 1]);
                        $mform->setDefault('cstreams['.$cstream->id.']', 0);
                    }
                    $mform->addGroup($checkboxes, 'cstreams', $this->dof->get_string('cstreams', $this->im_code()), '<br>');
                }
                $mform->addElement('hidden', 'age_id', $this->id);
                $mform->setType('age_id', PARAM_INT);
                $mform->addElement('submit', 'add_task_activate_cstreams', $this->dof->get_string('save_activate_cstreams',$this->im_code()), ['class' => 'align_right']);
            } else 
            {// если нет запланированных процессов, напишем об этом
                $mform->addElement('static', 'activate_cstreams_list_empty', '', $this->dof->get_string('activate_cstreams_list_empty', $this->im_code()));
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
        $mform =& $this->_form;

        if ( $this->is_submitted() && confirm_sesskey() &&
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Обработка данных формы
            
            // Формирование задачи на активацию
            $this->dof->storage('cstreams')->add_todo_activate_cstreams($formdata->cstreams);
            
            // Редирект
            $url = $this->dof->url_im('ages', '/view.php', $this->addvars);
            redirect($url);
        }
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
        
        // Проверки учебного периода
        if ( ! $this->dof->storage($this->im_code())->get_record(['id' => $data['age_id']]) )
        {// Учебный период не найден
            $errors['hidden'] = $this->dof->get_string('err_age_not_exists', $this->im_code());
        }
        
        // Проверки доступа к изменению учебных процессов
        foreach($data['cstreams'] as $id => $checked )
        {
            if ( $checked )
            {// Активация предмето-класса
                if ( ! $this->dof->workflow('cstreams')->is_access('changestatus', $id, null, $this->addvars['departmentid']) )
                {// Доступ к изменению статуса закрыт
                    $errors['cstreams'] = $this->dof->get_string('error_cstream_activate_access_denied', $this->im_code());
                }
            }
        }
        
        return $errors;
    }
}
?>