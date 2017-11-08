<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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
/*
 * Формы для журнала
 */
require_once('lib.php');
// содключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** Класс формы редактирования или создания урока
 * 
 */
class dof_im_journal_formtopic_teacher extends dof_modlib_widgets_form
{
    protected $plan;
    protected $event;
    protected $cstream;
    protected $cstreams;
    protected $dof;
    protected $linktype;
    protected $linkid;
    protected $departmentid;
    
    protected function storage_code()
    {
        return 'plans';
    }
    
    protected function im_code()
    {
        return 'journal';
    }
    
    /** Определить, можно ли создавать событие через журнал
     * 
     * @return bool 
     */
    protected function can_create_event()
    {
        return $this->dof->storage('schevents')->is_access('create');

    }
    
    function definition()
    {
        if ( isset($this->_customdata->departmentid) )
        {
            $this->departmentid = $this->_customdata->departmentid;
        }
        $this->dof = $this->_customdata->dof;
        //сохраняем в свойство объекта массив потоков, если он передан
        if ( ! empty($this->_customdata->cstreams) )
        {
            $this->cstreams = $this->_customdata->cstreams;
        }
        
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // извлекаем из базы все нужные переменные и записываем их во внутренние поля объекта
        // для будущей обработки
        $this->setup_local_variables($this->_customdata->planid, 
                                     $this->_customdata->cstreamid, 
                                     $this->_customdata->eventid);
        // устанавливаем все hidden-поля формы
        $this->setup_hidden_fields();
        
        //если передан массив потоков
        if ( ! empty($this->cstreams) )
        {//Отображаем раздел формы, связанный с выбором потоков
            $this->show_cstreams();
        }
        
        // Отображаем раздел формы, связанных с событием
        $this->show_event();
        
        // Отображаем раздел формы, связанный с контрольной точкой
        $this->show_plan();
        
        // кнопоки сохранить и отмена
        // создаем массив
        $objs = array();
        // Создаем элементы формы
        $objs[] = $mform->createElement('dof_single_use_submit', 'save', $this->dof->modlib('ig')->igs('save'));
        $objs[] = $mform->createElement('cancel', 'cancel', $this->dof->modlib('ig')->igs('cancel'));
        // добавляем элементы в форму
        $grp =& $mform->addElement('group', 'groupname', '', $objs, '', false);
        
        //$this->add_action_buttons(true, $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Извлечь из всех таблиц все необходимые объекты для построения запроса
     * @todo разобраться с сообщениями об ошибках - внести их в языковой файл и протестировать вызовы
     * 
     * @return null
     * @param int $planid - id контрольной точки в таблице plans (или 0 если такой точки нет)
     * @param int $csid - id учебного потока в таблице cstreams
     * @param int $eventid - id учебного события в таблице schevents
     */
    protected function setup_local_variables($planid, $csid, $eventid)
    {
        if ( ! $this->cstream = $this->dof->storage('cstreams')->get($csid) )
        {// поток обязательно должен быть существующим
            $this->dof->print_error('cstream_not_found');
        }
        if ( $planid )
        {// если контрольная точка редактируется - то возьмем привязку из нее
            if ( ! $this->plan = $this->dof->storage('plans')->get($planid) )
            {// мы пытаемся редактировать элемент планирования, которого нет в базе - это ошибка
                $this->dof->print_error('plan_not_found');
            }
            //  свзязь и тип связи контрольной точки мы возьмем из базы в этом случае
            $this->linkid   = $this->plan->linkid;
            $this->linktype = $this->plan->linktype;
        }else
        {// если контрольная точка создается - то возьмем информацию о привязке из переданных параметров
            $this->linkid = $csid;
            // в форме создания урока через журнал - мы можем создавать 
            // или редактировать только события учебного потока (cstream)
            $this->linktype = 'cstreams';
            // если событие не создано - создадим объект-заглушку для избежания notice-сообщений
            $this->plan = new stdClass();
            $this->plan->id             = 0;
            $this->plan->homeworkhours  = 0;
            $this->plan->plansectionsid = 0;
            $this->plan->name           = '';
            $this->plan->type           = 'facetime';
            $this->plan->homework       = '';
            $this->plan->note           = '';
        }
        
        if ( $eventid )
        {// мы редактируем существующее событие
            if ( ! $this->event = $this->dof->storage('schevents')->get($eventid) )
            {// переданное событие не существует
                $this->dof->print_error('event_not_found');
            }
        }else
        {// мы создаем новое событие - поставим заглушку внутрь переменной чтобы не было notice
            $event = new stdClass();
            $event->id = 0;
            $this->event = $event;
        }
        // получаем дату начала периода или потока (для которого редактируется журнал)
        $this->begindate = $this->cstream->begindate;
    }
    
    /** Установить все служебные hidden-параметры. Вынесено в отдельную функцию
     * для более удобного чтения кода
     * 
     * @return null
     */
    protected function setup_hidden_fields()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // id контрольной точки
        $mform->addElement('hidden','planid', $this->plan->id);
        $mform->setType('planid', PARAM_INT);
        // id события
        $mform->addElement('hidden','eventid', $this->event->id);
        $mform->setType('eventid', PARAM_INT);
        // id потока
        $mform->addElement('hidden','csid', $this->cstream->id);
        $mform->setType('csid', PARAM_INT);
        
        //  ключ сессии
        $mform->addElement('hidden','sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        // количество часов на домашнюю работу (для будущего пересчета)
        $mform->addElement('hidden','homeworkhours', $this->plan->homeworkhours);
        $mform->setType('homeworkhours', PARAM_INT);
        
        // объект привязки - 2 hidden-поля
        // тип связи 
        $mform->addElement('hidden','linktype', $this->linktype);
        $mform->setType('linktype', PARAM_ALPHANUM);
        // id связи
        $mform->addElement('hidden','linkid', $this->linkid);
        $mform->setType('linkid', PARAM_INT);
        // дата начала периода или потока (если есть)
        $mform->addElement('hidden','begindate', $this->begindate);
        $mform->setType('begindate', PARAM_INT);
        
        // созданные из журнала уроки всегда отображаются по факту, поэтому directmap всегда будет в положении 1
        $mform->addElement('hidden', 'directmap', 1);
        $mform->setType('directmap', PARAM_INT);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
    }
    
    /** Отобразить форму создания тематического планирования или форму редактирования
     * тематического планирования
     * @todo если контрольную точку нельзя редактировать - то вывести сообщение о том, почему это нельзя 
     * сделать и ссылка на редактирование контрольной точки в тематическом планировании
     * если у пользователя есть соответствующие права
     * 
     * @return 
     */
    protected function show_plan()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->get_form_title());
        
        if ( $this->plan->id )
        {// Контрольная точка редактируется - покажем форму редактирования
            if ( $this->linktype != 'cstreams' )
            {// можем редактировать только контрольные точки относящиеся к потоку
                // поэтому мы отключаем форму редактирования для контрольных точек 
                // не относящихся к потоку (cstream)
                
                // хак с display:none использован для того, чтобы выключить
                // форму редактирования контрольной точки.
                // По непонятным причинам в quickform правило disabledif
                // нельзя использовать для hidden-элементов
                $mform->addElement('radio', 'plan_disabled', '', '', 'true', 
                                array('disabled' => 'disabled', 'style' => 'display:none;'));
                $this->disable_standart_plan_form('plan_disabled', 'true');
            }
            $this->show_plan_edit();
        }else
        {// Контрольную точку надо создать - покажем форму создания
            $this->show_plan_create();
        }
    }
    
    /** Показать фрагмент формы, который отвечает за редактирование контрольной точки
     * 
     * @return 
     */
    protected function show_plan_edit()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // выводим форму редактирования контрольной точки
        $this->get_standart_plan_edit_form();
    }
    
    /** Показать фрагмент формы, который отвечает за создание контрольной точки
     * 
     * @return 
     */
    protected function show_plan_create()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // даем пользователю выбрать варианты создания точки тематического планирования:
        
        // не создавать точку вообще (только создать событие)
        $mform->addElement('radio', 'plan_creation_type', '', 
            $this->dof->get_string('do_not_create_point', $this->im_code()), 'none');
        $mform->setType('plan_creation_type', PARAM_ALPHA);
        // устанавливаем правила
        // отключаем поле "список тем"
        $mform->disabledIf('existing_point', 'plan_creation_type', 'eq', 'none');
        // отключаем стандартную форму создания контрольной точки
        $this->disable_standart_plan_form('plan_creation_type', 'none');
        
        // выбрать тему из списка контрольных точек (для того чтобы привязать точку к событию)
        $mform->addElement('radio', 'plan_creation_type', '', 
            $this->dof->get_string('select_existing_point', $this->im_code()), 'select');
        $mform->setType('plan_creation_type', PARAM_ALPHA);
        // список тем
        // получим список тем этого потока и добавляем выпадающее меню с ними
        $mform->addElement('select', 'existing_point', '',
                           $this->get_list_point($this->plan->id, $this->linktype, $this->linkid,1), 
                           ' style="max-width:400px;width:100%;" ');
        // устанавливаем правила
        // отключаем стандартную форму создания контрольной точки
        $this->disable_standart_plan_form('plan_creation_type', 'select');
        
        // создать контрольную точку самостоятельно (стандартная форма)
        $mform->addElement('radio', 'plan_creation_type', '', 
            $this->dof->get_string('create_new_point', $this->im_code()), 'create');
        $mform->setType('plan_creation_type', PARAM_ALPHA);
        // устанавливаем правила
        // отключаем поле "список тем"
        $mform->disabledIf('existing_point', 'plan_creation_type', 'eq', 'create');
        
        // устанавливаем по умолчанию переключател в положение "не создавать контрольную точку"
        $mform->setDefault('plan_creation_type', 'create');
        
        // подключаем стандартную форму создания контрольной точки
        $this->get_standart_plan_edit_form();
    }
    
    /** Показать стандартную часть формы создания/редактирования тематического планирования
     * 
     * @return 
     */
    protected function get_standart_plan_edit_form()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // получаем список всех возможных тематических разделов для этой контрольной точки
        $plansections = $this->dof_get_select_values(
                                                    $this->get_plan_sections_list($this->linktype, 
                                                                                  $this->linkid),
                                                    array(0 => '--- '.$this->dof->modlib('ig')->igs('absent').' ---'));
        // тематический раздел
        $mform->addElement('select', 'plansectionsid', $this->dof->get_string('plansection',$this->im_code()).':',
                           $plansections, ' style="max-width:400px;width:100%;" ');
        $mform->setType('plansectionsid', PARAM_INT);
        $mform->setDefault('plansectionsid', $this->plan->plansectionsid);
        
        // получаем список возможных родительских тем для тематиеского планирования
        $themes = $this->get_list_point($this->plan->id, $this->linktype, $this->linkid, null, true);
        // родительская тема 1
        $mform->addElement('select', 'parentid1', $this->dof->get_string('parenttheme',$this->im_code()).'&nbsp;1:',
                           $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid1', PARAM_INT);
        // родительская тема 2
        $mform->addElement('select', 'parentid2', $this->dof->get_string('parenttheme',$this->im_code()).'&nbsp;2:',
                           $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid2', PARAM_INT);
        // родительская тема 3
        $mform->addElement('select', 'parentid3', $this->dof->get_string('parenttheme',$this->im_code()).'&nbsp;3:',
                           $themes, ' style="max-width:400px;width:100%;" ');
        $mform->setType('parentid3', PARAM_INT);
        
        // @todo переделать этот алгоритм тогда когда появится возможность задавать более 3-х родительских тем.
        // Сейчас он работает КРИВО и через ЗАДНИЦУ. Да простит меня Алан Тьюринг. Аминь.
        if ( $this->plan->id )
        {// устанавливаем по умолчанию значения в 3 родительские темы
            if ( $parentpoints = $this->dof->storage('planinh')->get_records(array('inhplanid'=>$this->plan->id)) )
            {
                $i = 1;
                foreach ( $parentpoints as $parentpoint )
                {
                    if ( $mform->elementExists('parentid'.$i) )
                    {
                        $mform->setDefault('parentid'.$i, $parentpoint->planid);
                    }
                    ++$i;
                }
            }
        }
        
        // название темы
        $mform->addElement('textarea', 'name', 
                $this->dof->get_string('what_passed_on_lesson',$this->im_code()).':', 
                array('style' => 'width:100%;max-width:400px;height:150px;'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', $this->plan->name);
        $mform->addRule('name',$this->dof->modlib('ig')->igs('form_err_maxlength'), 'maxlength', 512,'client');
        $mform->addRule('name',$this->dof->modlib('ig')->igs('form_err_maxlength'), 'maxlength', 512,'server');
        
        // тип темы
        $mform->addElement('select', 'type', $this->dof->get_string('typetheme',$this->im_code()).':', 
                           $this->dof->modlib('refbook')->get_lesson_types());
        $mform->setType('type', PARAM_ALPHANUM);
        $mform->setDefault('type', $this->plan->type);
        // Номер темы в плане
        // @todo включить опцию установки номера в планировании когда это станет возможным
        //$mform->addElement('text', 'number', $this->dof->modlib('ig')->igs('number').':', 'size="2"');
        //$mform->setType('number', PARAM_INT);
        //$mform->addRule('number',$this->dof->modlib('ig')->igs('form_err_numeric'), 'numeric',null,'client');
        
        // домашнее задание
        // @todo отключить домашнее задание для итоговой аттестации
        // @todo сделать richtext-редактор для поля "домашнее задание"
        $mform->addElement('textarea', 'homework', $this->dof->get_string('homework',$this->im_code()).' :<br>'.
                            $this->dof->get_string('homework_size',$this->im_code()), 
                            array('style' => 'width:100%;max-width:400px;height:150px;'));
        $mform->setType('homework', PARAM_TEXT);
        $mform->setDefault('homework', $this->plan->homework);
        // часов на домашнее задание (создаем группу элементов)
        $homeworkgroup = array();
        // настройки для select-элемента "часы"
        // @todo сейчас отключено, и домашнее задание указывается только в минутах
        //       если решим что так и должно быть - то удалить этот элемент совсем
        //$hoursoptions    = array();
        //$hoursoptions['availableunits']   = array(3600 => $this->dof->modlib('ig')->igs('hours'));
        //$homeworkgroup[] = &$mform->createElement('dof_duration', 'hours', null, $hoursoptions);
        // настройки для select-элемента "минуты"
        $minutesoptions  = array();
        $minutesoptions['availableunits'] = array(60 => $this->dof->modlib('ig')->igs('minutes'));
        $homeworkgroup[] = &$mform->createElement('dof_duration', 'minutes', null, $minutesoptions);
        // добавляем группу элементов "время на домашнее задание"
        $mform->addGroup($homeworkgroup, 'homeworkhoursgroup', $this->dof->get_string('homeworkhours', $this->im_code()).':', '&nbsp;');
        // поле "примечания"
        // @todo сделать ricktext-редактор для поля "примечания"
        $mform->addElement('textarea', 'note',  $this->dof->get_string('notes',$this->im_code()).':',
                            array('style' => 'width:100%;max-width:400px;height:150px;'));
        $mform->setType('note', PARAM_TEXT);
        $mform->setDefault('note', $this->plan->note);
        
        // создаем поле, отвечающее за дату начала урока (абсолютную)
        if(! isset($this->event) OR ! $this->event->id)
        {
            $this->get_pinpoint_dateselector();
        }
    }
    
    /** Отобразить информацию о событии или форму редактирования события
     * 
     * @return 
     */
    protected function show_event()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( ! $this->event->id AND $this->can_create_event() )
        {// событие не существует и его разрешено создать через журнал - отобразим форму создания события
            // создаем заголовок формы
            $mform->addElement('header','formtitle', $this->dof->get_string('event', $this->im_code()));
            // показываем форму создания
            $this->show_event_create();
        }elseif( $this->event->id )
        {// событие есть, но его нельзя редактировать - отобразим njkmrj информацию о событии
            // создаем заголовок формы
            $mform->addElement('header','formtitle', $this->dof->get_string('event', $this->im_code()));
            // отображаем информацию
            $this->show_event_info();
        }
        // если события нет и создавать его не нужно - просто ничего не отображаем
        // ...
    }
    
    /** Отобразить информацию о событии
     * 
     * @return 
     */
    protected function show_event_info()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // получаем все типы событий
        $lessonforms = $this->dof->modlib('refbook')->get_event_form();
        // выводим тот, которым обладает событие
        $mform->addElement('static','event_lesson', $this->dof->get_string('form_lesson',$this->im_code()).':',
                            $lessonforms[$this->event->form]);
        // выводим дату начала события
        $mform->addElement('static','event_date', $this->dof->get_string('event_date',$this->im_code()).':',
                            dof_userdate($this->event->date,'%Y-%m-%d %H:%M'));
        // выводим дату начала события
        if ( $this->dof->storage('schevents')->is_access('edit:ahours',$this->event->id) )
        {// есть право - разрешаем редактировать
            $mform->addElement('text', 'event_ahours', $this->dof->get_string('ahours', $this->im_code()).':', 'size="4"');
            $mform->setType('event_ahours', PARAM_INT);
        }else
        {// просто выводим на экран
            $mform->addElement('hidden','event_ahours', $this->event->ahours);
            $mform->setType('event_ahours', PARAM_INT);
            $mform->addElement('static','event_ahours_info', $this->dof->get_string('ahours',$this->im_code()).':',
                           $this->event->ahours);
        }
        $mform->setDefault('event_ahours', $this->event->ahours);
        // выводим дату начала события
        $mform->addElement('static','event_salfactor', $this->dof->get_string('salfactor',$this->im_code()).':',
                           $this->dof->storage('cstreams')->calculation_salfactor($this->event->cstreamid));
        // выводим дату начала события
        $mform->addElement('static','event_rhours', $this->dof->get_string('rhours',$this->im_code()).':',
                           $this->event->rhours);
        if ( ! empty($this->event->replaceid) )
        {// это замена - выведем ссылку на источник
            $replace = $this->dof->storage('schevents')->get($this->event->replaceid);
            $mform->addElement('static','event_replace', $this->dof->get_string('replace_from',$this->im_code()).':',
                '<a href ='.$this->dof->url_im('journal','/group_journal/topic.php?csid='.$replace->cstreamid.
                '&planid='.$replace->planid.'&eventid='.$replace->id.'&departmentid='.$this->departmentid).
                '>'.dof_userdate($replace->date,'%Y-%m-%d %H:%M').'</a>');
        }
        if ( $replaces = $this->dof->storage('schevents')->get_records(array('replaceid'=>$this->event->id),'date DESC') )
        {// у урока есть замена - дадим ссылку на нее
            $replace = current($replaces); //выберем последнюю
            $mform->addElement('static','event_replaced', $this->dof->get_string('replaced_on',$this->im_code()).':',
                '<a href ='.$this->dof->url_im('journal','/group_journal/topic.php?csid='.$replace->cstreamid.
                '&planid='.$replace->planid.'&eventid='.$replace->id.'&departmentid='.$this->departmentid).
                '>'.dof_userdate($replace->date,'%Y-%m-%d %H:%M').'</a>');
            
        }
    }
    
    /** Показать фрагмент формы, который отвечает за создание события
     * 
     * @return 
     */
    protected function show_event_create()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // галочка "создать событие" - делает активными поля созданя события
        $mform->addElement('checkbox', 'create_event', '', $this->dof->modlib('ig')->igs('create').' '.
                            $this->dof->get_string('event', $this->im_code()));
        // отключаем поля создания события если галочка не поставлена
        $mform->disabledIf('event_form', 'create_event', 'notchecked');
        $mform->disabledIf('event_date', 'create_event', 'notchecked');
        $mform->disabledIf('event_ahours', 'create_event', 'notchecked');
        // тип события
        $mform->addElement('select', 'event_form', $this->dof->get_string('form_lesson',$this->im_code()).':', 
                           $this->dof->modlib('refbook')->get_event_form());
        // получаем дату начала и окончания по умолчанию из текущего периода
        $options = $this->get_dateselector_defaults();
        if ( $this->plan->id )
        {// есть контрольная точка - то берем дату из нее
            $date = $this->plan->reldate + $this->begindate;
        }else
        {// если нет контрольной точки - то подставляем текущую дату
            $date = time();
        }
        // планируемая дата события (по умолчанию совпадает с датой контрольной точки)
        $mform->addElement('date_time_selector', 'event_date', 
                    $this->dof->get_string('event_date', $this->im_code()).':', $options);
        $mform->setDefault('event_date', $date);
        $mform->addElement('text', 'event_ahours', $this->dof->get_string('ahours', $this->im_code()).':', 'size="4"');
        $mform->setType('event_ahours', PARAM_INT);
        $mform->setDefault('event_ahours', 1);
        // устанавливаем тип события - пол умолчанию  "normal"
        $mform->addElement('hidden','event_type', 'normal');
        $mform->setType('event_type', PARAM_ALPHANUM);
        
        // берем id учителя для потока из события
        $mform->addElement('hidden','event_teacherid', $this->cstream->teacherid);
        $mform->setType('event_teacherid', PARAM_INT);
        
        // устанавливаем по умолчанию поле "длительность"
        // @todo сделать здесь элемент dof_duration
        $mform->addElement('hidden','event_duration', 2700);
        $mform->setType('event_duration', PARAM_INT);
        $mform->setType('event_teacherid', PARAM_INT);
        
        // берем id назначения на должность из потока
        $mform->addElement('hidden','event_appointmentid', $this->cstream->appointmentid);
        $mform->setType('event_appointmentid', PARAM_INT);
    }
    
    /** Отобразить список потоков, для которых будет создано событие/план
     * Используется только если перешли по ссылке "добавление события для нескольких учебных
     * процессов" из журнала. Идет проверка на право создания события
     * 
     */
    protected function show_cstreams()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        // создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('cstreams', $this->im_code()));
        
        foreach($this->cstreams as $cstream)
        {//есть ли право создать событие для потока
            if ( $this->dof->im($this->im_code())->is_access('create_schevent', $cstream->id) )
            {//есть - отображаем
                $mform->addElement('checkbox', 'cstreams['.$cstream->id.']', '', $cstream->name);
            }
        }
    }
    
    /** Отключить все элементы формы создания элемента тематического планирования
     * Используется для того чтобы задать правила disabledif для всей формы
     * @todo найти способ выключить поле "время на домашнее задание"
     * 
     * @return null
     * @param string $element - название элемента от которого зависит, будет выключена форма создания
     *                          контрольной точки или нет
     * @param string $value - значение, при котором будет выключена форма создания контрольной точки
     */
    protected function disable_standart_plan_form($element, $value)
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // создаем массив полей формы создания тематического планирования, которые нужно отключить
        $fields = array('plansectionsid', 'parentid1', 'parentid2', 'parentid3', 'name', 'type',
                        'homework', 'homeworkhoursgroup', 'note', 'pinpoint_date');
        
        foreach ( $fields as $field )
        {// перебираем все поля формы и для каждого устанавливаем правило disabledif
            $mform->disabledIf($field, $element, 'eq', $value);
        }
    }
    
    /** Получить элемент dateselector для выбора даты внутри периода
     * (Для контрольных точек относящихся к cpassed и ages)
     * @return null
     */
    protected function get_pinpoint_dateselector()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // получаем дату начала и окончания по умолчанию из текущего периода
        $options = $this->get_dateselector_defaults();
        
        // определяем дату точки тематического планирования по умолчанию
        // показываем дату события, если есть
        if ( $this->event->id )
        {
            $default = $this->event->date;
        }elseif ( $this->plan->id )
        {// если события нет - то берем дату из контрольной точки
            $default = $this->plan->reldate + $this->begindate;
        }else
        {// если нет ни события ни контрольной точки - то подставляем текущую дату
            $default = time();
        } 
        
        $mform->addElement('date_time_selector', 'pinpoint_date', 
                    $this->dof->get_string('pinpoint_date', $this->im_code()).':', $options);
        $mform->setDefault('pinpoint_date', $default);
        $mform->disabledIf('pinpoint_date', 'create_event', 'checked');
    }
    
    /** Получить дату начала и дату окончания потока в виде массива настроек 
     * для quickform-элемента date_selector или date_time_selector
     * 
     * @return array - массив настроек
     */
    protected function get_dateselector_defaults()
    {
        if ( $age = $this->get_current_age($this->linktype, $this->linkid) )
        {// если мы можем точно определить период в котором работаем
            $startyear = dof_userdate($age->begindate,'%Y');
            $stopyear  = dof_userdate($age->enddate,'%Y');
        }else
        {// в остальных случаях - ставим только текущий год
            $startyear = dof_userdate(time(),'%Y');
            $stopyear  = dof_userdate(time(),'%Y');
        }
        // объявляем массив для установки значений по умолчанию
        $options = array();
        // устанавливаем год, с которого начинать вывод списка
        $options['startyear'] = $startyear;
        // устанавливаем год, которым заканчивается список
        $options['stopyear']  = $stopyear;
        // убираем галочку, делающую возможным отключение этого поля
        $options['optional']  = false;
        
        return $options;
    }
    
    
    /** Определить, какой тип даты выбирать - относительная или абсолютная
     * 
     * @param string $linktype - тип связи контрольной точки с объектом
     * @return bool
     *             true - использовать относительную дату (от начала программы или предмета)
     *             false - использовать абсолютную дату (для периода или подписки на предмет)
     */
    protected function is_relative_dataselector($linktype)
    {
        switch ( $linktype )
        {
            case 'ages'          : return false;
            case 'cstreams'      : return false;
            case 'programmitems' : return true;
            case 'programms'     : return true;
            case 'plan'          : return false;
            // по умолчанию возвращаем относительную дату
            default : return true;
        }
    }
    
    /** Получить список разделов тематического планирования
     * 
     * @return array
     * @param string $linktype
     * @param int $linkid
     */
    protected function get_plan_sections_list($linktype, $linkid)
    {
        // получаем список разделов тематического планирования для выбранного 
        // предмета, программы, потока или периода
        $sections = $this->dof->storage('plansections')->get_theme_plan($linktype, $linkid, array('active'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'plansections', 'code'=>'use'));
        $sections = $this->dof_get_acl_filtered_list($sections, $permissions);
        
        return $sections;
    }
    
    /** Подстановка данных по умолчанию
     * 
     * @return null
     */
    public function definition_after_data()
    {
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
                
        // устанавливаем значение часов/минут на домашнее задание по умолчанию
        $hwhours = $mform->getElementValue('homeworkhours');
        $minutes = floor($hwhours / 60);
        $mform->setDefault('homeworkhoursgroup', 
                            array('minutes' => $minutes));
    }
    
    /**
     * Проверка даты
     * @param int $event_date - дата события
     * @param int $cstream_begindate - начало потока
     * @param int $cstream_enddate - конец потока
     * @return string - Сообщение об ошибке или null в случае, если всё правильно
     */
    protected function date_check($event_date)
    {
        if( $event_date < $this->cstream->begindate )
        {//дата события меньше начальной даты потока
            $cstreambegin = dof_userdate($this->cstream->begindate,'%Y-%m-%d');
            return $this->dof->get_string('error_earlier_event_date', $this->im_code(), $cstreambegin);
        }
        if( $event_date > $this->cstream->enddate )
        {//дата события больше конечной даты потока
            $cstreamend = dof_userdate($this->cstream->enddate,'%Y-%m-%d');
            return $this->dof->get_string('error_later_event_date', $this->im_code(), $cstreamend);
        }
        return null;
    }
    
    /**
     * Проверяет соответствие КТ потоку и, при несоответствии, сразу выводит ошибку пользователю,
     * останевив всю работу
     * @param int $data_linkid - id потока, переданое через форму
     * @param string $linktype - тип связи, взятый из таблицы (должен быть 'cstreams')
     * @param int $linkid - id потока, взятое из таблицы
     */
    protected function plan_check($data_linkid, $linktype, $linkid)
    {
        if( ($linktype != 'cstreams') OR ($linkid != $data_linkid) )
        {// КТ не соответствует КТ потока
            $this->dof->print_error('plan_not_correspond_cstream', '', $data_linkid, 'im', 'journal');
        }
    }
    
    /**
     * Проверяет данные контрольной точки
     * @param mixed array $data - данные
     * @return string array - список сообщений об ошибках
     */
    protected function plan_data_check($data)
    {
        $errors = array();
        // проверка назнания темы
        if ( ! $data['parentid1'] AND ! $data['parentid2'] AND ! $data['parentid3'] )
        {// не указана ни одна из родительских тем
            if ( ! trim($data['name']) )
            {// не указано название темы, и не указана ни одна из родительских тем - это ошибка 
                $errors['name'] = $this->dof->modlib('ig')->igs('form_err_required');
            }
        }
        if ( ($data['parentid1'] == $data['parentid2'] OR $data['parentid1'] == $data['parentid3']) 
                AND $data['parentid1'] != '0' ) 
        {// проверка на совпадение родительских тем 
            $errors['parentid1'] = $this->dof->get_string('field_has', $this->im_code());    
        }elseif ( ($data['parentid2'] == $data['parentid3']) AND $data['parentid2'] != '0' )
        {
            $errors['parentid2'] = $this->dof->get_string('field_has', $this->im_code());             
        }
        if( isset($data['pinpoint_date']) AND ! isset($data['create_event']) )
        {
            // проверка правильности даты проведения
            if ( $age = $this->get_current_age($data['linktype'], $data['linkid']) )
            {// мы можем точно определить период, в котором работаем
                if ( $data['pinpoint_date'] < $age->begindate )
                {// абсолютная дата начала меньше даты начала периода
                    $agebegin = dof_userdate($age->begindate,'%Y-%m-%d');
                    $errors['pinpoint_date'] = 
                        $this->dof->get_string('err_too_small_absdate', $this->im_code(), $agebegin);
                }
                if ( $data['pinpoint_date'] > $age->enddate )
                {// абсолютная дата окончания больше даты окончания периода
                    $ageend = dof_userdate($age->enddate,'%Y-%m-%d');
                    $errors['pinpoint_date'] = 
                        $this->dof->get_string('err_too_large_absdate', $this->im_code(), $ageend);
                }
            }
        }
        
        // проверка поля "домашнее задание"
        if ( mb_strlen(trim($data['homework']),'utf-8') > 700 )
        {// слишком длинное домашнее задание (512 символов используется потому что данные передаются
            // из формы в двухбайтовой кодировке)
            $errors['homework'] = $this->dof->get_string('err_too_long_homework','plans');
        }
                
        if ( $this->dof->im($this->im_code())->get_cfg('deny_homework_without_hours') )
        {// если в конфиге запрещено задание домашних заданий без указания часов - проверим, 
            // указано ли время на выполнение домашнего задания
            if ( ! trim($data['homeworkhours']) OR ! floatval($data['homeworkhours']) )
            {// не указаны часы на домашнее задание
                $errors['homeworkhours'] = $this->dof->get_string('err_no_homework_hours','plans');
            }
        }
        
        return $errors;
    }
    
    /**Проверка прав доступа
     * 
     * @param $access - право доступа, которое нужно проверить
     * @param $objid - id потока, события или плана (по умолчанию равен null)
     * @return 
     */
    protected function access_check($access, $objid = null)
    {
        if( !$this->dof->im('journal')->is_access($access, $objid) )
        {
            $this->dof->print_error('access_denied', '', null, 'im', 'journal');
        }
    }
    
    /** Проверка данных на стороне сервера
     * 
     * @return array
     * @param array $data[optional] - массив с данными из формы
     * @param array $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $returnurl = $this->dof->url_im('journal','/group_journal/index.php?departmentid='
                    .$this->departmentid.'&csid='.$this->cstream->id);
        if ( ! isset($data['linktype']) OR ! isset($data['linkid']) )
        {//не переданы обязательные данные
            $this->dof->print_error('error_data_null', '', null, 'im', 'journal');
        }
        if( $data['linktype'] != 'cstreams' AND $data['linktype'] != 'programmitems' )
        {//редактирование и создание КТ и события может быть связано только с потоком или предметом
            $this->dof->print_error('error_data_linktype', '', null, 'im', 'journal');
        }

        if( $this->cstream->id != $data['linkid'])
        {// id потока передано неверно
            $this->dof->print_error('cstream_not_found', '', $data['linkid'], 'im', 'journal');
        }

        $errors = array();
        
        // определяем, какой тип проверки использовать
        if ( ! $this->event->id AND $this->can_create_event() AND isset($data['create_event']) AND $data['create_event'] )
        {// событие можно и нужно было создать - проверка создания события
            if ( ! $this->dof->storage('schevents')->is_access('create') AND 
                 ! $this->dof->storage('schevents')->is_access('create/in_own_journal',$this->cstream->id) )
            {
                $this->dof->print_error('access_denied', '', null, 'im', 'journal');
            }
            if( ($data['event_form'] != 'distantly') AND ($data['event_form'] != 'internal') )
            {//форма урока неверно задана
                $errors['event_form'] = $this->dof->get_string('error_event_form', 'journal');
            }
            if( $error = $this->date_check($data['event_date']) )
            {
                $errors['event_date'] = $error; 
            }
        }elseif( isset($data['eventid']) AND $data['eventid'] )
        {// событие уже существует
            // проверка правильности привязки события к потоку
            if( ! isset( $this->event->cstreamid ) OR $this->event->cstreamid != $data['linkid'])
            {//Событие не соответствует событию потока
                $this->dof->print_error('event_not_correspond_cstream', '', $data['linkid'], 'im', 'journal');
            }
        }

        if ( ! isset($data['planid']) OR ! $data['planid'] )
        {// если КТ создается 
            if ( ! $this->dof->storage('plans')->is_access('create') AND 
                 ! $this->dof->storage('plans')->is_access('create/in_own_journal',$this->cstream->id) AND
                 ! $this->dof->im('journal')->is_access('give_theme_event',$data['eventid']) AND 
                 ! $this->dof->im('journal')->is_access('give_theme_event/own_event',$data['eventid']) )
            {
                $this->dof->print_error('access_denied', '', null, 'im', 'journal');
            }
            if ( $data['plan_creation_type'] == 'create' )
            {// проверка создания новой контрольной точки
                $errors = array_merge($errors, $this->plan_data_check($data));
            }elseif ( $data['plan_creation_type'] == 'select' )
            {// проверка выбранной из списка контрольной точки
                if ( isset($data['eventid']) AND $data['eventid'] )
                {
                    if ( ! $this->dof->im('journal')->is_access('give_theme_event',$data['eventid']) AND 
                         ! $this->dof->im('journal')->is_access('give_theme_event/own_event',$data['eventid']) )
                    {
                        $this->dof->print_error('access_denied', '', null, 'im', 'journal');
                    }
                }
                if ( ! $plan = $this->dof->storage('plans')->get($data['existing_point']) )
                {// выбранная точка планирования не существует
                    $errors['existing_point'] = $this->dof->get_string('err_selected_point_not_exists',
                        $this->im_code());
                }else
                {// выбранная точка планирования существует
                    // проверка правильности привязки контрольной точки к потоку
                    $this->plan_check($data['linkid'], $plan->linktype, $plan->linkid);
                    if( ($plan->status != 'active') AND ($plan->status != 'draft') )
                    {
                        $this->dof->print_error('error_plan_status', '', null, 'im', 'journal');
                    }
                }

            }
        }else
        {// КТ редактируется

            if ( ! $this->dof->storage('plans')->is_access('edit',$data['planid']) AND 
                 ! $this->dof->storage('plans')->is_access('edit/in_own_journal',$data['planid']) )
            {
                $this->dof->print_error('access_denied', '', null, 'im', 'journal');
            }
            // проверка правильности привязки контрольной точки к потоку
            $this->plan_check($data['linkid'], $this->plan->linktype, $this->plan->linkid);
            // проверка обновления данных контрольной точки
            $errors = array_merge($errors, $this->plan_data_check($data));
        }
        
        if ( $this->event->id )
        {// событие редактируется
            $appoint = $this->dof->storage('appointments')->get($this->event->appointmentid );
            if ( ! empty($appoint) )
            {// Назначение на должность для урока есть
                if ( $appoint->status == 'patient' )
                {// учитель на больничном не может отмечать уроки
                    $this->dof->print_error('err_patient_teacher', $returnurl, null, 'im', 'journal');
                } else if ( $appoint->status == 'vacation' )
                {// учитель в отпуске не может отмечать уроки
                    $this->dof->print_error('err_vacation_teacher', $returnurl, null, 'im', 'journal');
                }
            }
        }
        
        return $errors;
    }
    
    /** Возвращает список контрольных точек для select-элементов "родительская тема"
     * 
     * @param int $pointid - id контрольной точки которую надо
     * @param string $linktype - тип связи контрольной точки с объектом
     * @param int $linkid - id объекта с которым связана контрольная точка
     * @return array
     */
    private function get_list_point($pointid, $linktype, $linkid, $direcrmap = 1, $noremoveitself = false)
    {
        $points = array();
        $points['0'] = $this->dof->get_string('none','plans');
        
        // получим список всех элементов тематического планирования
        $plans = $this->dof->storage('plans')->
            get_theme_plan($linktype, $linkid, 
                        array('active', 'fixed', 'checked'), true, $direcrmap, $noremoveitself);
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'plans', 'code'=>'use'));
        
        $plans = $this->dof_get_acl_filtered_list($plans, $permissions);
        
        if ( ! $plans )
        {// нет ни одного элемента - возвращаем массив с единственным элементом "выбрать"
           return $points; 
        }
        // для каждого плана сформируем массив id плана=>имя плана
        foreach ($plans as $plan)
        {
            if ( ! $noremoveitself AND $this->dof->storage('schevents')->
                       get_records(array('planid'=>$plan->id,'status'=>
                       array('plan','completed','postponed','replaced'))) )
            {// если стоит флаг показать самого себя, то активных событий быть не должно
                continue;
            }
            if ( $plan->linktype != 'cstreams' AND $plan->linktype != 'plan' )
            {// только темы потока
                continue;
            }
            if ( $plan->id <> $pointid )
            {// забиваем все, кроме той, которой не надо
                $points[$plan->id] = $plan->name;
            }
        }
        return $points;
    }
    /** Возвращает строку заголовка формы
     * 
     * @param int $id[optional] - id редактируемой в данной момент записи
     * @return string
     */
    private function get_form_title($id=null)
    {
        return $this->dof->get_string('form_topic_title', $this->im_code());
    }
    
    /** Получить текущий учебный период, или false если определить период
     * не представляется возможным
     * @todo в этой форме брать даты начала и окончания только из текущего потока
     * 
     * @return object|bool
     * @param string $linktype - тип связи контрольной точки с объектом
     * @param int $linkid - id объекта с которым связана контрольная точка
     */
    protected function get_current_age($linktype, $linkid)
    {
        if ( $this->is_relative_dataselector($linktype) )
        {// это предмет либо программа - невозможно установить точную дату начала периода
            return false;
        }
        // в этой форме дату начала и окончания периода всегда берем из потока 
        return $this->cstream;
    }
    
    /** Обработчик формы добавления события для нескольких потоков
     *  @param $departmentid - id подразделения
     */
    public function process_save_events()
    {
        // для того, что в библиотеке прописана навигация не ругалась
        GLOBAL $DOF;
        $addvars = '';
        // Подключаем библиотек
        include_once($this->dof->plugin_path('im','journal','/group_journal/lib.php'));

        //создадим путь на журнал заняти
        $path = $this->dof->url_im('journal','/show_events/index.php?departmentid='.$this->departmentid);

        if ( $this->is_cancelled() )
        {//ввод данных отменен - возвращаем на страницу журнала
            redirect($path,'',0);
        }
        //обработчик формы
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {//даные переданы в текущей сессии - получаем
            if ( ! empty($formdata->cstreams) )
            {
                foreach($formdata->cstreams as $csid=>$value)
                {
                    $planid = 0; 
                    $eventid = 0;
                    //подключаем методы редактирования формы
                    $edittopic = new dof_im_journal_edittopic($this->dof, $planid, $csid, $eventid);

                    $formdata->csid = $csid;
                    $formdata->linkid = $csid;
                    $formdata->event_teacherid = $this->dof->storage('cstreams')->get_field($csid,'teacherid');
                    $formdata->event_appointmentid = $this->dof->storage('cstreams')->get_field($csid,'appointmentid');
                    // сохраняем данные из формы
                    $edittopic->save_complete_lesson_form($formdata,false);
                }
                echo '<div align=\'center\'><b style="color:#0b8000;">'
                        .$this->dof->get_string('add_event_success','journal').'</b></div>';
            }
            else
            {
                echo '<div align=\'center\'><b style="color:#f00;">'
                        .$this->dof->get_string('no_cstreams_choosed','journal').'</b></div>';
            }
        }
    }
}

/**
 * кнопка отмены урока
 *
 */
class dof_im_journal_form_cancel_lesson extends dof_modlib_widgets_form
{
    protected $dof;
    function definition() 
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        // выводим заголовок
        $mform->addElement('header', 'cancelname', $this->dof->get_string('lesson_cancel_title','journal'));
        // выводим скрытые поля, необходимые для обновления и переадресации
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'eventid');
        $mform->setType('eventid', PARAM_INT);
        $mform->addElement('checkbox', 'yes_cancel',null, $this->dof->get_string('сonfirmation_cancel_lesson','journal'));
        $mform->setDefault('yes_cancel', 0);
        // Кнопка "отменить"
        $mform->addElement('submit', 'lesson_cancel', $this->dof->get_string('lesson_cancel','journal'));
        $mform->disabledIf('lesson_cancel', 'yes_cancel');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
}
/**
 * кнопка проведения урока 
 *
 */
class dof_im_journal_form_complete_lesson extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    function definition() 
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        // выводим заголовок
        $mform->addElement('header','lessoncompletename', 
                 $this->dof->get_string('lesson_complete_title','journal'));
        // выводим скрытые поля, необходимые для обновления и переадресации
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'eventid');
        $mform->setType('eventid', PARAM_INT);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
        // Кнопка "применить"
        $mform->addElement('submit', 'lesson_complete', $this->dof->get_string('lesson_complete','journal'));
    }
}


/** Перенос уроков
 * 
 */
class dof_im_journal_form_transfer_lesson extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    function definition() 
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        // выводим заголовок
        $mform->addElement('header','transfer_lesson', 
                 $this->dof->get_string('lesson_transfer_title','journal'));
        // выводим скрытые поля, необходимые для обновления и переадресации
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'eventid');
        $mform->setType('eventid', PARAM_INT);
                // настройки для элемента datetimeselector
        $options = array();
        $options['startyear'] = dof_userdate(time()-5*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
        $options['optional']  = true;
        //покажем меню выбора даты
        if  ( $this->dof->im('journal')->is_access('replace_schevent:date_dis',$this->_customdata->eventid) OR 
              $this->dof->im('journal')->is_access('replace_schevent:date_dis/own',$this->_customdata->eventid) OR 
              $this->dof->im('journal')->is_access('replace_schevent:date_int',$this->_customdata->eventid))
        {
            $mform->addElement('date_time_selector', 'date', $this->dof->get_string('new_lesson_date','journal').':',$options);
        }
        // замена учителя
        if ( $this->dof->im('journal')->is_access('replace_schevent:teacher',$this->_customdata->eventid) )
        {
            $teachers = $this->get_list_teachers($this->dof->storage('cstreams')->get_field($this->_customdata->cstreamid, 'programmitemid'));
            $mform->addElement('select', 'teacher', $this->dof->get_string('new_teacher','journal'),$teachers);    
            $appointmentid = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'appointmentid');
            if ( ! $appointmentid )
            {// у события нет учителя - поставим учителья потока
                $appointmentid = $this->dof->storage('cstreams')->get_field($this->_customdata->cstreamid, 'appointmentid');
            }        
            $mform->setDefault('teacher', $appointmentid); 
            
        }
        // Кнопка "применить"
        $mform->addElement('submit', 'replace_lesson', $this->dof->get_string('postpone','journal'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Добавляет необходимые поля
     * @return void
     */
    function definition_after_data()
    {
        $mform =& $this->_form;
        if ( $eventid = $mform->getElementValue('eventid') )
        {// если передан id события
            // извлечем его статус
            $status = $this->dof->storage('schevents')->get_field($eventid,'status');
            if ( $status == 'plan' )
            {// добавим кнопку отложения урока на неопределенный срок
                $mform->addElement('submit', 'postpone_lesson', $this->dof->get_string('postpone_indefinitely','journal'));
            }
        }
    }
    /** Проверка данных на стороне сервера
     * @return 
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     */
    public function validation($data,$files)
    {
        $errors = array();
        if ( isset($data['postpone_lesson']) )
        {// если переносим урок
            return $errors;
        }
        // @todo ошибки закоментированы, т.к definition_after_data работает через жопу
        $access = $this->dof->im('journal')->is_access_replace($data['eventid']);
        // проверим по времени
        $cstreamid = $this->dof->storage('schevents')->get_field($data['eventid'], 'cstreamid');
        $ageid = $this->dof->storage('cstreams')->get_field($cstreamid, 'ageid');
        $age = $this->dof->storage('ages')->get($ageid);
        if ( ($data['date'] < $age->begindate OR $data['date'] > $age->enddate) 
                 AND ! $this->dof->is_access('datamanage') ) 
        {// даты начала и окончания события не должны вылезать за границы периода
            //$errors['date'] = $this->dof->get_string('err_date','journal', 
            //    date('Y/m/d', time()).'-'.date('Y/m/d', $age->enddate));
        }
        if ( ! $access->ignorolddate )
        {// игнорировать новую дату урока нельзя
            if ( $data['date'] < time() )
            {// переносить можно только на еще не наступившее время
                //$errors['date'] = $this->dof->get_string('err_date_postfactum','journal');
            }
            // @todo если границы бутут определятся в конфиге сделаем потом через него
            
            // @todo сделать проверку, если у ученика или учителя уже есть на это время уроки
        }
        // если ошибки есть - то пользователь вернется на страницу редактирования и увидит их
        return $errors;
    }

    /** Возвращает массив персон
     * 
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_teachers($pitemid=null)
    {
        $rez = $this->dof_get_select_values();
        // получаем список всех кто может преподавать
        if ( is_int_string($pitemid) )
        {// если передан id предмета, выведем только учителей предмета
            $teachers = $this->dof->storage('teachers')->get_records(array
                                    ('programmitemid'=>$pitemid,'status'=>array('plan', 'active')));
        }else
        {// иначе выведем всех
            $teachers = $this->dof->storage('teachers')->get_records(array('status'=>array('plan', 'active')));
        }
        if ( $teachers AND isset($teachers) )
        {// получаем список пользователей по списку учителей
            $persons = $this->dof->storage('teachers')->get_persons_with_appid($teachers,true);
            // преобразовываем список к пригодному для элемента select виду
            $rez = $this->dof_get_select_values($persons, true, 'appointmentid', array('sortname','enumber'));
            asort($rez);
        }
        
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        return $rez;
    }    
    
}
/** Перенос уроков
 * 
 */
class dof_im_journal_form_replace_event extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    function definition() 
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        // выводим заголовок
        $mform->addElement('header','transfer_lesson', 
                 $this->dof->get_string('lesson_transfer_title','journal'));
        // выводим скрытые поля, необходимые для обновления и переадресации
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden','departmentid', optional_param('departmentid', 0, PARAM_INT));
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden', 'eventid',$this->_customdata->eventid);
        $mform->setType('eventid', PARAM_INT);
        $mform->addElement('hidden', 'event', 0);
        //$mform->setConstant('event',0);
        $mform->addElement('hidden', 'joinid', 0);
        // настройки для элемента datetimeselector
        $options = array();
        $options['startyear'] = dof_userdate(time()-5*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+5*365*24*3600,'%Y');
        $options['optional']  = true;
        $options['enabled']  = true;
        $options['defaulttime']  = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'date');
        $event_types = $this->dof->modlib('refbook')->get_event_types();
        $mform->addElement('radio', 'type', null, $event_types['normal'],'normal');
        $mform->addElement('radio', 'type', null, $event_types['combination'],'combination');
        $mform->addElement('radio', 'type', null, $event_types['free'],'free');
        $mform->setDefault('type','normal');
        //покажем меню выбора даты
        if  ( $this->dof->im('journal')->is_access('replace_schevent:date_dis',$this->_customdata->eventid) OR 
              $this->dof->im('journal')->is_access('replace_schevent:date_dis/own',$this->_customdata->eventid) OR 
              $this->dof->im('journal')->is_access('replace_schevent:date_int',$this->_customdata->eventid))
        {
            $mform->addElement('date_time_selector', 'date', $this->dof->get_string('new_lesson_date','journal').':',$options);
        }
        // замена учителя
        if ( $this->dof->im('journal')->is_access('replace_schevent:teacher',$this->_customdata->eventid) )
        {
            $cstreamid = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'cstreamid');
            $teachers = $this->get_list_teachers($this->dof->storage('cstreams')->get_field($cstreamid, 'programmitemid'));
            $mform->addElement('select', 'teacher', $this->dof->get_string('new_teacher','journal'),$teachers);    
            $appointmentid = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'appointmentid');
            if ( ! $appointmentid )
            {// у события нет учителя - поставим учителья потока
                $appointmentid = $this->dof->storage('cstreams')->get_field($cstreamid, 'appointmentid');
            }        
            $mform->setDefault('teacher', $appointmentid); 
            
        }
        // Кнопка "применить"
        $mform->addElement('submit', 'replace_lesson', $this->dof->modlib('ig')->igs('next').
                                                   '/'.$this->dof->modlib('ig')->igs('replace'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Добавляет необходимые поля
     * @return void
     */
    function definition_after_data()
    {
        $mform =& $this->_form;
    }
    
    function choice_event($formdata) 
    {
        $mform =& $this->_form;
        if ( $mform->elementExists('replace_lesson') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('replace_lesson');
        }
        if ( $mform->elementExists('joinid') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('joinid');
        }
        $mform->setConstant('event',1);
        if ( $mform->elementExists('type') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('type');
            $mform->removeElement('type');
            $mform->removeElement('type');
        }
        $mform->addElement('hidden', 'type', $formdata->type);
        $event_types = $this->dof->modlib('refbook')->get_event_types();
        $mform->addElement('static', 'htype', null, $event_types[$formdata->type]);
        if ( $mform->elementExists('date') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('date');
        }
        $mform->addElement('hidden', 'date', $formdata->date);
        $date = dof_userdate($formdata->date,'%d-%m-%Y %H:%M');
        if ( $formdata->date == 0 )
        {
            $date = $this->dof->modlib('ig')->igs('no_specify_jr');
        }
        $mform->addElement('static', 'hdate', $this->dof->get_string('new_lesson_date','journal'), $date);
        if ( $mform->elementExists('teacher') )
        {// если раньше на этом месте стоял select - уберем его вместе с кнопкой "сохранить"
            $mform->removeElement('teacher');
        }
        $mform->addElement('hidden', 'teacher', $formdata->teacher);
        $person = $this->dof->storage('appointments')->get_person_by_appointment($formdata->teacher);
        $fio = $this->dof->storage('persons')->get_fullname($person);
        if ( $formdata->teacher == 0 )
        {
            $fio = $this->dof->modlib('ig')->igs('no_specify_mr');
        }
        $mform->addElement('static', 'hteacher', $this->dof->get_string('new_teacher','journal'), $fio);
        //$mform->addElement('hidden', 'date', $formdata->date);
        //$mform->addElement('hidden', 'teacher', $formdata->teacher);
        //$mform->addElement('hidden', 'type', $formdata->type);
        $events = $this->get_list_events($formdata->date,$formdata->teacher);
        $mform->addElement('select', 'joinid', $this->dof->get_string('new_event','journal'),$events);  
        // Кнопка "применить"
        $mform->addElement('submit', 'replace_lesson', $this->dof->modlib('ig')->igs('replace'));
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
        //print_object($data);die;
        $errors = array();
        if ( isset($data['postpone_lesson']) )
        {// если переносим урок
            //return $errors;
        }
        $access = $this->dof->im('journal')->is_access_replace($data['eventid']);
        // проверим по времени
        $cstreamid = $this->dof->storage('schevents')->get_field($data['eventid'], 'cstreamid');
        $ageid = $this->dof->storage('cstreams')->get_field($cstreamid, 'ageid');
        $age = $this->dof->storage('ages')->get($ageid);
        if ( ($data['date'] < $age->begindate OR $data['date'] > $age->enddate) 
                 AND ! $this->dof->is_access('datamanage') ) 
        {// даты начала и окончания события не должны вылезать за границы периода
            //$errors['date'] = $this->dof->get_string('err_date','journal', 
            //    date('Y/m/d', time()).'-'.date('Y/m/d', $age->enddate));
        }
        if ( ! $access->ignorolddate )
        {// игнорировать новую дату урока нельзя
            if ( $data['date'] < time() )
            {// переносить можно только на еще не наступившее время
                //$errors['date'] = $this->dof->get_string('err_date_postfactum','journal');
            }
            // @todo если границы бутут определятся в конфиге сделаем потом через него
            
            // @todo сделать проверку, если у ученика или учителя уже есть на это время уроки
        }
        // если ошибки есть - то пользователь вернется на страницу редактирования и увидит их
        return $errors;
    }

    /** Возвращает массив персон
     * 
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_teachers($pitemid=null)
    {
        $rez = $this->dof_get_select_values();
        // получаем список всех кто может преподавать
        if ( is_int_string($pitemid) )
        {// если передан id предмета, выведем только учителей предмета
            $teachers = $this->dof->storage('teachers')->get_records(array
                                ('programmitemid'=>$pitemid,'status'=>array('plan', 'active')));
        }else
        {// иначе выведем всех
            $teachers = $this->dof->storage('teachers')->get_records(array('status'=>array('plan', 'active')));
        }
        if ( $teachers AND isset($teachers) )
        {// получаем список пользователей по списку учителей
            $persons = $this->dof->storage('teachers')->get_persons_with_appid($teachers,true);
            // преобразовываем список к пригодному для элемента select виду
            foreach ( $persons as $id=>$person )
            {// составляем название комплекта: категория + код
                $positionid = $this->dof->storage('schpositions')->
                                    get_field($this->dof->storage('appointments')->
                                    get_field($id,'schpositionid'), 'positionid');  
                $departmentid = $this->dof->storage('appointments')->
                                    get_field($id,'departmentid');
                $fullname = $this->dof->storage('persons')->get_fullname($person);
                $position = $this->dof->storage('positions')->get_field($positionid,'name');
                $depcode  = $this->dof->storage('departments')->get_field($departmentid,'code');
                $rez[$id] = "$fullname [$position / $person->enumber / $depcode]";
            }
            asort($rez);
        }
        
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        return $rez;
    }
    
    /** Возвращает массив персон
     * 
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    protected function get_list_events($date = 0,$appointid=null)
    {
        $rez = array();
        // получаем список всех кто может преподавать
        $params = new stdClass;
        $params->status = array('plan');
        if ( !empty($date) )
        {// если передан id учителя - ищем по нему
            $params->date = $date;
            $params->status[] = 'completed';
        }else
        {
            $params->date_from = time()-7*24*60*60;
            $params->date_to = time()+365*24*60*60;
        }
        if ( !empty($appointid) )
        {// если передан id учителя - ищем по нему
            $params->appointmentid = $appointid;
            $params->status[] = 'completed';
        }
        $select = $this->dof->storage('schevents')->get_select_listing($params);
        $events = $this->dof->storage('schevents')->get_records_select($select,null,'date');
        if ( empty($events) )
        {
            return $this->dof_get_select_values();
        }
        foreach ( $events as $event )
        {
            //print_object($event);
            if ( $event->id == $this->_customdata->eventid )
            {// себя самого исключаем
                continue;
            }
            $pitemid = $this->dof->storage('cstreams')->get_field($event->cstreamid,'programmitemid');
            $pitem = $this->dof->storage('programmitems')->get($pitemid);
            $person = $this->dof->storage('appointments')->get_person_by_appointment($event->appointmentid);
            $fullname = $this->dof->storage('persons')->get_fullname($person);
            $eventdate = dof_userdate($event->date,'%d-%m-%Y %H:%M');
            $rez[$event->id] = "$pitem->name [$pitem->code] $fullname $eventdate";
        }
        return $rez;
    }

    /** Возвращает массив персон
     * 
     * @return array список персон, массив(id предмета=>название)
     * @param object $cstream[optional] - объект из таблицы cstreams, если поток редактируется
     */
    public function process()
    {
        $addvars = '';
        //обработчик формы
        $formdata = $this->get_data();
        if ( isset($formdata->joinid) AND $formdata->type != 'normal' AND 
             $formdata->joinid == 0 AND $formdata->event == 1 )
        {
            return $this->dof->get_string('err_joinid','journal');
        }
        if ( $this->is_submitted() AND confirm_sesskey() )
        {//даные переданы в текущей сессии - получаем
            $addvars['departmentid'] = $formdata->departmentid;
            if ( $formdata->type != 'normal' AND $formdata->event == 0 )
            {
                $this->choice_event($formdata);
            }else
            {
                $replace = new stdClass;
                $replace->date = $formdata->date;
                $replace->appointmentid = $formdata->teacher;
                $replace->type = $formdata->type;
                $replace->joinid = $formdata->joinid;
                if ( $formdata->joinid AND empty($formdata->date) )
                {// время не передано - вставим из совместного урока
                    $replace->date = $this->dof->storage('schevents')->get_field($formdata->joinid,'date');
                }
                if ( $formdata->joinid AND empty($formdata->teacher) )
                {// учитель не задан - вставим из совместного урока
                    $replace->appointmentid = $this->dof->storage('schevents')->get_field($formdata->joinid,'appointmentid');
                }
                $expression = $this->dof->storage('schevents')->replace_events($formdata->eventid, $replace);
                //var_dump($expression);die;
                if ( empty($formdata->date) )
                {
                    $replace->date = $this->dof->storage('schevents')->get_field($this->_customdata->eventid, 'date');
                }
                $addvars['date_from'] = $replace->date;
                $addvars['date_to'] = $replace->date;
                $person = $this->dof->storage('appointments')->get_person_by_appointment($replace->appointmentid);
                $addvars['personid'] = $person->id;
                $path = $this->dof->url_im('journal','/show_events/show_events.php',$addvars);
                redirect($path,'',0);
            }
        }
    }    
    
}
?>