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
require_once(dirname(realpath(__FILE__)).'/lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/*
 * Класс формы для ввода данных приказа на передачу нагрузки преподавателя (первая страничка)
 */
class dof_im_cstreams_order_change_teacher_page_one extends dof_modlib_widgets_form
{

    protected $dof;
    
/** Внутренняя функция. Получить параметры для autocomplete-элемента 
     * @param string $type - тип autocomplete-элемента, для которого получается список параметров
     *                       appointments - поиск по табельным номерам
     *                       orders  - поиск по приказам
     * @return array
     */
    protected function autocomplete_params($type)
    {
        $options = array();
        $options['plugintype'] = "storage";
        $options['sesskey'] = sesskey();
        $options['type'] = 'autocomplete';
        
        //тип данных для автопоиска
        switch ($type)
        {
            // Должностные назначения
            case 'appointments':
                $options['plugincode'] = "appointments";
                $options['querytype']  = "appointments_fromname";
                break;
                
            // Приказы на смену преподавателя
            case 'orders':
                $options['plugincode'] = "orders";
                $options['querytype'] = "orders_change_teacher";
                break;
        }
 
        return $options;
    }
    
    protected function im_code()
    {
        return 'cstreams';
    }
    
    function definition()
    {
        $mform =& $this->_form;
        $this->dof = $this->_customdata->dof;
        $mform->addElement('header','cldheader', $this->dof->get_string('cldheader', $this->im_code()));

        $mform->addElement('hidden', 'stage', 1); // Номер этапа
        $mform->setType('stage', PARAM_INT);
        
        // Радиокнопка "Направление передачи нагрузки":
        //  "Передать часы от преподавателя", "Передать часы преподавателю", "Вернуть переданные часы".
        $mform->addElement('radio', 'direction', $this->dof->get_string('direction', $this->im_code()).':',
                $this->dof->get_string('fromteacher', $this->im_code()), 'fromteacher');
        $mform->addElement('radio', 'direction', '', 
                $this->dof->get_string('toteacher', $this->im_code()), 'toteacher');
        $mform->addElement('radio', 'direction', '', 
                $this->dof->get_string('returnhours', $this->im_code()), 'returnhours');
        $mform->setDefault('direction', 'fromteacher'); 

        // Поле с автопоиском ФИО (автопоиск по должностным назначениям). Поле с номером приказа при этом варианте игнорируется.
        $ajaxparams = $this->autocomplete_params('appointments', null, null);
        $mform->addElement('dof_autocomplete', 'fullname',
                $this->dof->get_string('fullname', $this->im_code()).':', array(), $ajaxparams);
        $mform->disabledIf('fullname', 'direction', 'eq', 'returnhours');
        //Поле с автопоиском "Приказ о передаче часов"
        // (по номеру приказа, в подсказке отображаем дату, направление и ФИО,
        //  отображаем только приказы на передачу нагрузки).
        // Активно только для варианта "Вернуть переданные часы". Находятся
        //  только приказы, передающие часы от преподавателя.
        $ajaxparams = $this->autocomplete_params('orders', null, null);
        $mform->addElement('dof_autocomplete', 'order',
                $this->dof->get_string('changehoursorder', $this->im_code()).':', array(), $ajaxparams);
        $mform->disabledIf('order', 'direction', 'noteq', 'returnhours');

        // Радиокнопка "Причина".
        $mform->addElement('header','cldheader', $this->dof->get_string('reason', $this->im_code()));
        $mform->addElement('radio', 'reason', $this->dof->get_string('activitysuspension', $this->im_code()).':',
                $this->dof->get_string('sickleave', $this->im_code()), 'sickleave');
        $mform->addElement('radio', 'reason', '',
                $this->dof->get_string('vacation', $this->im_code()), 'vacation');
        $mform->addElement('radio', 'reason', $this->dof->get_string('activityresumption', $this->im_code()).':',
                $this->dof->get_string('recovery', $this->im_code()), 'recovery');
        $mform->addElement('radio', 'reason', '', 
                $this->dof->get_string('vacationreturn', $this->im_code()), 'vacationreturn');
        $mform->addElement('radio', 'reason', $this->dof->get_string('changeload', $this->im_code()).':',
                $this->dof->get_string('loadreduction', $this->im_code()), 'loadreduction');
        $mform->addElement('radio', 'reason', '',
                $this->dof->get_string('loadincrease', $this->im_code()), 'loadincrease');
        $mform->setDefault('reason', 'sickleave'); 
        
        //Галочка "Изменить статус должностного назначения" (активна только для вариантов с больничным и отпуском)
        // - меняет статус должностного назначения при исполнении приказа.
        $mform->addElement('checkbox', 'changestatus', null, $this->dof->get_string('changeappointmentstatus',$this->im_code()));
        $mform->disabledIf('changestatus', 'reason', 'eq', 'loadreduction');
        $mform->disabledIf('changestatus', 'reason', 'eq', 'loadincrease');
        $mform->setType('changestatus', PARAM_BOOL);
        // Кнопка "продолжить и отмена"
        
        $sumbit = array();
         // Создаем элементы навигации
        $sumbit[] =& $mform->createElement('submit','save', $this->dof->modlib('ig')->igs('save'));
        $sumbit[] =& $mform->createElement('submit','next', $this->dof->modlib('ig')->igs('continue'));
        $sumbit[] =& $mform->createElement('cancel');
        $mform->closeHeaderBefore('buttonar'); // Закроем заголовок перед кнопками
        $mform->addGroup($sumbit, 'buttonar');
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        $errors = array();
        if ( isset($data['buttonar']['cancel']) )
        { // Проверок не делаем, просто выходим
            return $errors;
        }
        if ( empty($data['order']['order']) OR
                   $data['order']['id'] == 0 )
        {// Одно из полей выборки должно быть выбрано
            if ( $data['direction'] == 'returnhours')
            {
                $errors['order'] = $this->dof->get_string('error_choice',$this->im_code());
            }
        }
        if ( empty($data['fullname']['fullname']) OR
                   $data['fullname']['id'] == 0 )
        {// Одно из полей выборки должно быть выбрано
            if ( $data['direction'] != 'returnhours' )
            {
                $errors['fullname'] = $this->dof->get_string('error_choice',$this->im_code());
            }
        }
            
        // Найдём appointmentid из полученных данных с формы, с которым будем работать
        $appid = '';
        if ( $data['direction'] != 'returnhours' )
        {
            $appid = $data['fullname']['id'];
            if ( ! is_int_string($appid) OR ! $appointment = $this->dof->storage('appointments')->get($appid) )
            {
                $errors['fullname'] = $this->dof->get_string('error_choice',$this->im_code());
                return $errors; // Дальше нет смысла проверять на ошибки
            }
        } else
        {
            $orderid = $data['order']['id'];
            $order = $this->dof->storage('orders')->get($orderid);
            if ( ! is_int_string($orderid) OR ! $order = $this->dof->storage('orders')->get($orderid) )
            {
                $errors['order'] = $this->dof->get_string('error_choice',$this->im_code());
                return $errors; // Дальше нет смысла проверять на ошибки
            }
            $conds = array('orderid'=>$orderid, 'firstlvlname'=>'appointmentid');
            if ( ! $orderdata = $this->dof->storage('orderdata')->get_record($conds,'id,data') )
            {
                $errors['order'] = $this->dof->get_string('error_choice',$this->im_code());
                return $errors; // Дальше нет смысла проверять на ошибки
            }
            $appid = $orderdata->data;
        }

        if ( $data['direction'] == 'fromteacher' )
        {
            $cstreams = $this->dof->storage('cstreams')->get_appointment_cstreams($appid, 'active', 'id');
        } else if ( $data['direction'] == 'toteacher' )
        {
            $cstreams = $this->dof->storage('cstreams')->get_appointment_take_cstreams($appid, 'active', 'id');
        }
        if ( $data['direction'] != 'returnhours' )
        {
            if ( empty($cstreams) )
            {
                $errors['fullname'] = $this->dof->get_string('error_nocstreams',$this->im_code());
            }
        }
        // Сначала нужно удостовериться, что смена статуса возможна
        if ( ! $list = $this->dof->workflow('appointments')->get_available($appid) )
        {// Ошибка получения списка возможных статусов для объекта';
            $errors['changestatus'] = $this->dof->get_string('error_nostatuses',$this->im_code());
        }
        // возьмём текущий статус
        $status = $this->dof->storage('appointments')->get_field($appid, 'status');
        if ( isset($data['changestatus']) )
        { // Проверяем, можно ли перейти в указанный статус
            // Если это ФИО, проверяем по appointmentid
            switch ( $data['reason'] )
            {
                case 'sickleave':
                    $status = 'patient';
                    break;

                case 'vacation':
                    $status = 'vacation';
                    break;

                case 'recovery':
                case 'vacationreturn':
                    $status = 'active';
                    break;
                default:
                    $errors['reason'] = $this->dof->get_string('error_unknownreason',$this->im_code());
                    break;
            }
            if ( empty($status) OR ! isset($list[$status]) )
            {// Переход в данный статус из текущего невозможен';
                $errors['changestatus'] = $this->dof->get_string('error_statuschangenotavailable',$this->im_code());
            }
        }
        // Затем проверим, может ли преподаватель с этим статусом брать нагрузку
        // В $status хранится конечное состояние преподавателя (смена статуса или текущее)
        if ( $data['direction'] != 'fromteacher' )
        {// Если мы передаём нагрузку преподавателю, а не от него
            $appstatuses = $this->dof->workflow('appointments')->get_meta_list('active');
            if ( empty($status) OR ! array_key_exists($status, $appstatuses) )
            {// Преподаватель не может брать нагрузку, т.к. он не будет неактивен
                $errors['fullname'] = $this->dof->get_string('error_finalstatusnotactive', $this->im_code(),
                                                             $this->dof->workflow('appointments')->get_name($status));
            }
        }
        return $errors;
    }

    /** Обработчик формы
     */
    public function process()
    {
        $addvars = array();
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// Получили данные формы';
            if ( isset($formdata->buttonar['cancel']) )
            { // Ввод данных отменен - возвращаем на страницу назад
                // Странная штука - если вручную сделать 'cancel'-кнопку (в группе),
                // то is_cancelled() не работает, из-за того, что она в группе, и
                // из-за этого optional_param() не видит её...
                redirect($this->dof->url_im('cstreams','/orderctload/index.php',$addvars));
            }
            // Создадим новый приказ
            $order = new dof_im_cstreams_teacher($this->dof);
            // Создадим объект приказа из пришедшей формы
            if ( ! $order->process_form($formdata) )
            {
                return false;
            }
            // В зависимости от кнопки сделаем redirect
            switch ( current(array_keys($formdata->buttonar)) )
            { // Сохраняем и возвращаемся на список или проходим на вторую форму
                case 'next':
                    if ( $formdata->direction == 'returnhours' )
                    {
                        redirect($this->dof->url_im('cstreams','/orderctload/form_third.php?edit='.$order->get_id().'&',$addvars));
                    }
                    redirect($this->dof->url_im('cstreams','/orderctload/form_second.php?edit='.$order->get_id().'&',$addvars));
                    break;
                case 'save':
                    redirect($this->dof->url_im('cstreams','/orderctload/list.php',$addvars));
                    break;
            }
        }
    }

}

/*
 * Класс формы для ввода данных приказа на передачу нагрузки преподавателя (вторая страничка)
 */
class dof_im_cstreams_order_change_teacher_page_three extends dof_modlib_widgets_form
{

    protected $dof;
    private $orderid;   // Номер приказа, над которым работаем
    private $order;     // Класс работы с приказом
    private $orderdata; // Данные объекта приказа
    
    protected function im_code()
    {
        return 'cstreams';
    }
    
    function definition()
    {
        $mform =& $this->_form;
        $this->dof     = $this->_customdata->dof;
        $this->orderid = $this->_customdata->orderid;
        // Инициализируем класс работы с приказом 
        $this->order   = new dof_im_cstreams_teacher($this->dof, $this->orderid);
        $this->orderdata = $this->order->get_order_data();
        $data     =& $this->orderdata->data;
        $direction = $this->orderdata->data->direction;
        $status    = $this->orderdata->data->status;
        $currentstatus = $this->dof->workflow('appointments')->get_name($status);
        // Выведем информацию о приказе
        $mform->addElement('hidden', 'direction', $direction);
        $mform->addElement('header','headerfrom', "$data->fullname [$data->enumber]" );
        $mform->addElement('static', 's_currentstatus', $this->dof->get_string('currentstatus', $this->im_code()). ':', $currentstatus);
        $mform->addElement('static', 's_direction', $this->dof->get_string('direction', $this->im_code()). ':', $this->dof->get_string($data->direction,$this->im_code()));
        $mform->addElement('static', 's_reason', $this->dof->get_string('reason', $this->im_code()). ':', $this->dof->get_string($data->reason,$this->im_code()));
        $changestatush = $this->dof->modlib('ig')->igs('change_status') . ':';
        if ( !empty($data->changestatus) )
        {
            $changestatus = $this->dof->modlib('ig')->igs('yes');
        } else
        {
            $changestatus = $this->dof->modlib('ig')->igs('no');
        }
        $mform->addElement('static', 's_changestatus', $changestatush, $changestatus);
        // В соответствии с типом приказа отобразим подходящую информацию (сгруппированную по-разному)
        switch ( $direction )
        {
            // Группировка по программам
            case 'fromteacher':
                $this->show_cstreams_group_program_transfer($this->orderdata->data->appointmentid);
                break;
            // Группировка по учителям
            case 'toteacher':
                $this->show_cstreams_group_program_take($this->orderdata->data->appointmentid);
                break;
            // Группировка по программам, нет возможности редактировать
            case 'returnhours':
                $this->show_cstreams_group_program_returnhours($this->orderdata->data->appointmentid);
                break;
            default:
                break;
        }

        $sumbit = array();
        // Создаем элементы навигации
        $sumbit[] =& $mform->createElement('submit','save', $this->dof->modlib('ig')->igs('save'));
        $sumbit[] =& $mform->createElement('submit','next', $this->dof->get_string('order_see',$this->im_code()));
        $sumbit[] =& $mform->createElement('cancel');
        $mform->closeHeaderBefore('buttonar'); // Закроем заголовок перед кнопками
        $mform->addGroup($sumbit, 'buttonar');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        $errors = array();
        $sum = 0;
        if ( isset($data['buttonar']['cancel']) )
        { // Проверок не делаем, просто выходим
            return $errors;
        }
        if ( $data['direction'] == 'returnhours')
        { // Тут редактировать нечего
            return $errors;
        }
        // Проверим, выбрал ли пользователь хоть один поток на передачу?
        $programs = array_values($data['cstreams']);
        foreach ($programs as $cstreams)
        {
            $sum += array_sum($cstreams);
        }
        
        if ( $sum == 0 )
        { // Ничего не выбрали..
            $firstprogram = current(array_keys($data['cstreams']));
            $firstcstream = current(array_keys($data['cstreams'][$firstprogram]));
            $errors['cstreamsg['.$firstprogram.']['.$firstcstream.']'] = $this->dof->get_string('error_nochoice',$this->im_code());
        }
        return $errors;
    }

    /** Обработчик формы
     */
    public function process()
    {
        $addvars = array();
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// Получили данные формы';
            if ( isset($formdata->buttonar['cancel']) )
            { // Ввод данных отменен - возвращаем на страницу назад
                // Странная штука - если вручную сделать 'cancel'-кнопку (в группе),
                // то is_cancelled() не работает, из-за того, что она в группе, и
                // из-за этого optional_param() не видит её...
                redirect($this->dof->url_im('cstreams','/orderctload/list.php',$addvars));
            }
            
            // Обработаем данные, пришедшие с формы: обновим потоки
            $this->order->process_form_third($formdata);
            // В зависимости от кнопки сделаем redirect
            switch ( current(array_keys($formdata->buttonar)) )
            { // Сохраняем и возвращаемся на список или проходим на вторую форму
                case 'next':
                    $link = $this->dof->url_im('cstreams','/orderctload/view.php?id='.$this->order->get_id().'&',$addvars);
                    break;
                case 'save':
                    $link = $this->dof->url_im('cstreams','/orderctload/list.php',$addvars);
                    break;
            }
            redirect($link);
        }
    }

    /** Выбор и отображение списка потоков по выбранному преподавателю (группировка по программам)
     * 
     * @param int $appid - id из таблицы appointments
     */
    private function show_cstreams_group_program_transfer($appid)
    {
        $groups = $this->orderdata->data->cstreams;
        $appid = $this->orderdata->data->appointmentid;
        // Теперь вывод
        $mform =& $this->_form;
        foreach ( $groups as $programmid => $object )
        { // Формат такой:
            // $groups[$programmid] = new stdClass();
            // $groups[$programmid]->name = $programmname;
            // $groups[$programmid]->cstreams = array();
            $programmname = $object->name;
            $mform->addElement('header','headername'.$programmid, $programmname);
            $pitemsgroup = array(); // Сформируем группу по 
            foreach ( $object->cstreams as $cstream )
            { // Сгруппируем потоки по дисциплинам
                if ( !isset($pitemsgroup[$cstream->programmitemid]) )
                {
                    $pitemsgroup[$cstream->programmitemid] = array();
                }
                $pitemsgroup[$cstream->programmitemid][] = $cstream;
            }
            foreach ( $pitemsgroup as $pitemid => $cstreams )
            { // Теперь выводим сгруппированные по дисциплинам потоки
                $pitemname = $this->dof->storage('programmitems')->get_field($pitemid,'name');
                $cstreamsg = array();
                $cstreamsg[] = $mform->createElement('static', 'pitems['.$pitemid.']', '<b>'.$pitemname.'</b>');
                $grp =& $mform->addElement('group', 'pitemsg['.$pitemid.']', '<b>'.$pitemname.'</b>', $cstreamsg, null, false);
                foreach ( $cstreams as $cstream )
                {
                    $cstreamsg = array();
                    $cstreamsg[] = $mform->createElement('select', 'cstreams['.$programmid.']'.'['.$cstream->id.']', $cstream->name.':', 
                                              $this->get_possible_teachers($cstream->programmitemid, $appid), '');
                    // Ссылка на поток
                    $depid = optional_param('departmentid', 0, PARAM_INT);
                    $link = $this->dof->url_im('cstreams', '/view.php', array('cstreamid'=>$cstream->id, 'departmentid'=>$depid));
                    $cstreamlink = '<a href="'.$link.'">'.$cstream->name.'</a>';
                    $grp =& $mform->addElement('group', 'cstreamsg['.$programmid.']'.'['.$cstream->id.']', $cstreamlink, $cstreamsg, null, false);
                    $mform->setDefault('cstreams['.$programmid.']'.'['.$cstream->id.']', $cstream->appointmentid);
                }
            }
            
        }
    }
    
    /** Выбор и отображение списка потоков по выбранному преподавателю (группировка по программам)
     * Для отображения в случае, если часы возвращаются. Показывается, из каких потоков
     *  будет перенесена нагрузка (и кому передавалась ранее)
     */
    private function show_cstreams_group_program_returnhours()
    {
        $groups =& $this->orderdata->data->cstreams;
        $mform =& $this->_form;
        foreach ( $groups as $programmid => $object )
        { // Формат такой:
            // $groups[$programmid] = new stdClass();
            // $groups[$programmid]->name = $programmname;
            // $groups[$programmid]->cstreams = array();
            $programmname = $object->name;
            $mform->addElement('header','headername'.$programmid, $programmname);
            foreach ( $object->cstreams as $cstream )
            { // Сгруппируем потоки по дисциплинам
                if ( !isset($pitemsgroup[$cstream->programmitemid]) )
                {
                    $pitemsgroup[$cstream->programmitemid] = array();
                }
                $pitemsgroup[$cstream->programmitemid][] = $cstream;
            }
            foreach ( $pitemsgroup as $pitemid => $cstreams )
            { // Выведем сгруппированные по дисциплинам потоки
                $pitemname = $this->dof->storage('programmitems')->get_field($pitemid,'name');
                $cstreamsg = array();
                $cstreamsg[] = $mform->createElement('static', 'pitems['.$pitemid.']', '<b>'.$pitemname.'</b>');
                $grp =& $mform->addElement('group', 'pitemsg['.$pitemid.']', '<b>'.$pitemname.'</b>', $cstreamsg, null, false);
                foreach ( $cstreams as $cstream )
                { // Для каждого потока
                    $cstreamsg = array();
                    $currentappid = $this->dof->storage('cstreams')->get_field($cstream->id, 'appointmentid');
                    if ( $cstream->appointmentid != $currentappid )
                    { // Если вдруг поток уже передан другому преподавателю, покажем текущего
                        $person = $this->dof->storage('appointments')->get_person_by_appointment($currentappid, true);
                        $personname = ' (' . $this->dof->get_string('current', $this->im_code()) . ': '
                                           . $this->dof->storage('persons')->get_fullname($person)
                                           . " [$person->enumber])";
                    } else
                    {
                        $personname = '';
                    }
                    // Ссылка на поток
                    $depid = optional_param('departmentid', 0, PARAM_INT);
                    $link = $this->dof->url_im('cstreams', '/view.php', array('cstreamid'=>$cstream->id, 'departmentid'=>$depid));
                    $cstreamlink = '<a href="'.$link.'">'.$cstream->name.'</a>';
                    $cstreamsg[] = $mform->createElement('static', 's_cstreams['.$programmid.']'.'['.$cstream->id.']', $cstream->name);
                    $cstreamsg[] = $mform->createElement('static', 's_teachers['.$programmid.']'.'['.$cstream->id.']',
                            '',$cstream->fullname . " [$cstream->enumber]" . $personname);
                    $grp =& $mform->addElement('group', 'cstreamsg['.$programmid.']'.'['.$cstream->id.']', $cstreamlink, $cstreamsg, null, false);
                }
            }
        }
    }
    
    /** Выбор и отображение списка потоков по выбранному преподавателю (группировка по преподавателям)
     * Для отображения в случае, если часы передаются преподавателю. Показываются потоки преподавателей
     * , сгруппированные по дисциплинам
     * 
     * @param int $appid - id из таблицы appointments
     */
    private function show_cstreams_group_program_take($appid)
    {
        $groups = $this->orderdata->data->cstreams;
        $appid = $this->orderdata->data->appointmentid;
        $mform =& $this->_form;
        foreach ($groups as $appointmentid => $object)
        { // Формат такой:
            // $groups[$appointmentid] = new stdClass();
            // $groups[$appointmentid]->name = $programmname;
            // $groups[$appointmentid]->enumber = $enumber;
            // $groups[$appointmentid]->cstreams = array();
            $mform->addElement('header','pname'.$appointmentid, $object->name . " [$object->enumber]");
            $pitemsgroup = array(); // Сформируем группу по 
            foreach ( $object->cstreams as $cstream )
            { // Сгруппируем потоки по дисциплинам
                if ( !isset($pitemsgroup[$cstream->programmitemid]) )
                {
                    $pitemsgroup[$cstream->programmitemid] = array();
                }
                $pitemsgroup[$cstream->programmitemid][] = $cstream;
            }
            foreach ( $pitemsgroup as $pitemid => $cstreams )
            { // Выводим сгруппированные по дисциплинам потоки
                $pitemname = $this->dof->storage('programmitems')->get_field($pitemid,'name');
                $cstreamsg = array();
                $cstreamsg[] = $mform->createElement('static', 'pitems['.$pitemid.']', '<b>'.$pitemname.'</b>');
                $grp =& $mform->addElement('group', 'pitemsg['.$pitemid.']', '<b>'.$pitemname.'</b>', $cstreamsg, null, false);
                foreach ($cstreams as $cstream)
                {
                    $cstreamsg = array();
                    $cstreamsg[] = $mform->createElement('static', 's_cstreams['.$appointmentid.']'.'['.$cstream->id.']', $cstream->name);
                    $cstreamsg[] = $mform->createElement('advcheckbox', 'cstreams['.$appointmentid.']'.'['.$cstream->id.']',
                                                         null, $this->dof->get_string('transferload', 'cstreams'));
                    // Ссылка на поток
                    $depid = optional_param('departmentid', 0, PARAM_INT);
                    $link = $this->dof->url_im('cstreams', '/view.php', array('cstreamid'=>$cstream->id, 'departmentid'=>$depid));
                    $cstreamlink = '<a href="'.$link.'">'.$cstream->name.'</a>';
                    $grp =& $mform->addElement('group', 'cstreamsg['.$appointmentid.']'.'['.$cstream->id.']', $cstreamlink, $cstreamsg, null, false);
                    if ( isset($cstream->checked) AND ($cstream->checked != 0) )
                    {
                        $mform->setDefault('cstreams['.$appointmentid.']'.'['.$cstream->id.']', 1);
                    } else
                    {
                        $mform->setDefault('cstreams['.$appointmentid.']'.'['.$cstream->id.']', 0);
                    }
                }
            }
        }
    }
    
    /** Выдаёт для select-элемента формы список доступных учителей в потоке
     * 
     * @param int $pitemid - programmitemid из таблицы cstreams
     * @param int $exclude - номер appointmentid, который нужно убрать из списка 
     *                     (для того, чтобы нагрузку нельзя было передать самому себе)
     * @return array - список должностных назначений, которые могут вести поток
     */
    private function get_possible_teachers($pitemid, $exclude=null)
    {
        $persons = $this->order->get_possible_teachers($pitemid);
        if ( !empty($persons) )
        {
            // Преобразовываем список к пригодному для элемента select виду
            $firstelement = array(0 => $this->dof->get_string('donttransfer', 'cstreams'));
            $rez = $this->dof_get_select_values($persons, false,
                        'appointmentid', array('sortname','enumber'));
            asort($rez);
            $rez = $firstelement + $rez;
        }
        
        // Оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $filtered = $this->dof->storage('acl')->get_acl_filtered_list($rez, $permissions);
        if ( is_int_string($exclude) )
        { // Если передали id того, кого не нужно видеть в списке.
            unset($filtered[$exclude]);
        }
        return $filtered;
    }
}

/*
 * Класс формы для ввода данных приказа на передачу нагрузки преподавателя (вторая страничка)
 */
class dof_im_cstreams_order_change_teacher_page_two extends dof_modlib_widgets_form
{

    protected $dof;
    private $orderid;   // Номер приказа, над которым работаем
    private $order;     // Класс работы с приказом
    private $orderdata; // Данные объекта приказа
    
    protected function im_code()
    {
        return 'cstreams';
    }
    
    function definition()
    {
        $mform =& $this->_form;
        $this->dof     = $this->_customdata->dof;
        $this->orderid = $this->_customdata->orderid;
        $this->order   = new dof_im_cstreams_teacher($this->dof, $this->orderid); // Класс работы с приказом
        $this->orderdata = $this->order->get_order_data();
        // Покажем сначала информацию о приказе
        $data     =& $this->orderdata->data;
        $direction = $this->orderdata->data->direction;
        $status    = $this->orderdata->data->status;
        $currentstatus = $this->dof->workflow('appointments')->get_name($status);
        $mform->addElement('hidden', 'direction', $direction);
        $mform->addElement('header', 'headerfrom', "$data->fullname [$data->enumber]" );
        $mform->addElement('static', 's_currentstatus', $this->dof->get_string('currentstatus', $this->im_code()). ':', $currentstatus);
        $mform->addElement('static', 's_direction', $this->dof->get_string('direction', $this->im_code()). ':', $this->dof->get_string($data->direction,$this->im_code()));
        $mform->addElement('static', 's_reason', $this->dof->get_string('reason', $this->im_code()). ':', $this->dof->get_string($data->reason,$this->im_code()));
        $changestatush = $this->dof->modlib('ig')->igs('change_status') . ':';
        if ( !empty($data->changestatus) )
        {
            $changestatus = $this->dof->modlib('ig')->igs('yes');
        } else
        {
            $changestatus = $this->dof->modlib('ig')->igs('no');
        }
        $mform->addElement('static', 's_changestatus', $changestatush, $changestatus);
        $mform->closeHeaderBefore('ageid'); // Закроем заголовок перед

        $mform->addElement('select', 'ageid', $this->dof->get_string('age',$this->im_code()).':', 
                                  $this->get_list_ages(), '');
        $mform->addElement('select', 'agenum', $this->dof->get_string('agenum',$this->im_code()).':', 
                                  $this->get_list_agenum(), '');
        // Отобразим возможные дисциплины, по которым можно отфильтровать приказ
        $this->show_programmitems_group_program($this->orderdata->data->appointmentid);
        
        $sumbit = array();
        // Создаем элементы навигации
        $sumbit[] =& $mform->createElement('submit','save', $this->dof->modlib('ig')->igs('save'));
        $sumbit[] =& $mform->createElement('submit','next', $this->dof->modlib('ig')->igs('next'));
        $sumbit[] =& $mform->createElement('cancel');
        $mform->closeHeaderBefore('buttonar'); // Закроем заголовок перед кнопками
        $mform->addGroup($sumbit, 'buttonar');
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /** Получить список периодов из базы данных для элемента select
     * @return array массив в формате id_периода=>имя_периода
     */
    private function get_list_ages($departmentid = null)
    {
        $params = array();
        if ( !empty($departmentid) )
        {
            $params['departmentid'] = $departmentid;
        }
        $statuses = $this->dof->workflow('ages')->get_meta_list('actual');
        $params['status'] = array_keys($statuses);
        // получаем список доступных учебных периодов
        $rez = $this->dof->storage('ages')->get_records($params);
        // преобразуем список записей в нужный для select-элемента формат
        $rez = $this->dof_get_select_values($rez, array( 0 => '--- '.$this->dof->modlib('ig')->igs('any_mr').' ---' ), 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'ages', 'code'=>'use'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        
        if ( isset($this->ageid) AND $this->ageid )
        {// если у нас уже указан период добавим его в список
            $rez[$this->ageid] = 
                 $this->dof->storage('ages')->get_field($this->ageid,'name');
        }
        
        return $rez;
    }
    
    /** Выполняет фильтрацию списка дисциплин, которые показываются на странице
     * для того, чтобы не выбирать фильтры тех дисциплин, которые преподаватель
     * не ведёт
     * 
     * @param array $pitems - массив дисциплин с ключом $pitemid
     * @return array - массив отфильтрованных дисциплин
     */
    private function filter_pitems_cstreams($pitems)
    {
        $programms =& $this->orderdata->data->cstreams;
        $cstreampitemids = array();
        // Просматриваем программы
        foreach ( $programms as $object )
        {
            foreach ( $object->cstreams as $cstream )
            {
                // Для всех потоков в программе ищем номер дисциплины в переданном массиве
                // а затем сохраняем в $cstreampitemids с ключом programmitemid
                if ( array_key_exists($cstream->programmitemid, $pitems) )
                {
                    $cstreampitemids[$cstream->programmitemid] = true;
                }

            }
        }
        // Исключаем те дисциплины, которых нет в наших потоках
        foreach ( $pitems as $pitemid => $pitem )
        {
            if ( ! array_key_exists($pitemid, $cstreampitemids) )
            {
                unset($pitems[$pitemid]);
            }
        }
        return $pitems;
    }
    
    /** Получить список семестров (максимальный по таблице programms) элемента select
     * 
     * @param int $departmentid - id подразделения, в котором ищем максимальный семестр
     * @return array массив в формате ( 1 => 1, ..., n => n ), n = MAX(agenums)
     */
    private function get_list_agenum($departmentid = null)
    {
        // Найдем по таблице programms максимальный agenums
        $maxagenum = $this->dof->storage('programms')->get_max_agenums($departmentid);
        $agenums = array( 0 => '--- '.$this->dof->modlib('ig')->igs('any_jr').' ---' );
        for ( $i = 1; $i <= $maxagenum; $i++ )
        {
            $agenums[] = $i;
        }
        return $agenums;
    }
    
    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data,$files)
    {
        $errors = array();
        if ( isset($data['buttonar']['cancel']) )
        { // Проверок не делаем, просто выходим
            return $errors;
        }
        return $errors;
    }

    /** Обработчик формы
     */
    public function process()
    {
        $addvars = array();
        $addvars['departmentid'] = optional_param('departmentid', 0, PARAM_INT);
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// Получили данные формы';
            if ( isset($formdata->buttonar['cancel']) )
            { // Ввод данных отменен - возвращаем на страницу назад
                // Странная штука - если вручную сделать 'cancel'-кнопку (в группе),
                // то is_cancelled() не работает, из-за того, что она в группе, и
                // из-за этого optional_param() не видит её...
                redirect($this->dof->url_im('cstreams','/orderctload/list.php',$addvars));
            }
            // Обработаем данные, пришедшие с формы: обновим потоки
            $this->order->process_form_second($formdata);
            // В зависимости от кнопки сделаем redirect
            switch ( current(array_keys($formdata->buttonar)) )
            { // Сохраняем и возвращаемся на список или проходим на вторую форму
                case 'next':
                    $link = $this->dof->url_im('cstreams','/orderctload/form_third.php?edit='.$this->order->get_id().'&',$addvars);
                    break;
                case 'save':
                    $link = $this->dof->url_im('cstreams','/orderctload/list.php',$addvars);
                    break;
            }
            redirect($link);
        }
    }

    /** Выбор и отображение списка дисциплин по выбранному преподавателю (группировка по программам)
     * 
     * @param int $appid - id из таблицы appointments
     */
    private function show_programmitems_group_program($appid)
    {
        $appid = $this->orderdata->data->appointmentid;
        $mform =& $this->_form;
        // Если мы формируем фильтры для передачи от преподавателя, то сначала
        // Найдём всего его дисциплины, сформируем по программам и выведем
        // в виде списка (галочка на дисциплине и select возможных преподавателей
        if ( $this->orderdata->data->direction == 'fromteacher' )
        { // Получим список всех дисциплин преподавателя
            $pitemsall = $this->dof->storage('teachers')->get_appointment_pitems($appid);
            // Отфильтруем те, которые он в данный момент не ведёт
            $pitems = $this->filter_pitems_cstreams($pitemsall);
            unset($pitemsall);
        } else
        {
            $pitems = $this->dof->storage('teachers')->get_appointment_pitems($appid);
        }
        $programms = array();
        // Сгруппируем по программам
        foreach ( $pitems as $id => $pitem )
        {
            if ( !isset($programms[$pitem->programmid]) )
            {
                $programms[$pitem->programmid] = array();
            }
            $programms[$pitem->programmid][$id] = $pitem;
        }
        
        foreach ( $programms as $programmid => $pitems )
        { // Формат такой:
            // $groups[$programmid] = new stdClass();
            // $groups[$programmid]->name = $programmname;
            // $groups[$programmid]->cstreams = array();
            // Выведем сгруппированные по программам дисциплины
            $programmname = $this->dof->storage('programms')->get_field($programmid,'name');
            $mform->addElement('header','headername'.$programmid, $programmname);
            foreach ( $pitems as $pitem )
            { // Для каждой дисциплины
                $cstreamsg = array();
                $cstreamsg[] = $mform->createElement('checkbox', 'programmitems['.$pitem->id.']'."[1]",
                                                     null, '');
                $cstreamsg[] = $mform->createElement('select', 'programmitems['.$pitem->id.']', '', 
                                          $this->get_possible_teachers($pitem->id, $appid), '');
                $grp =& $mform->addElement('group', 'programmitemsg['.$pitem->id.']', $pitem->name, $cstreamsg, null, false);
                $mform->disabledIf('programmitems['.$pitem->id.']', 'programmitems['.$pitem->id.'][1]');
            }
            
        }
    }
    
    /** Выдаёт для select-элемента формы список доступных учителей в потоке
     * 
     * @param int $pitemid - id из таблицы programmitems
     * @param int $exclude - номер appointmentid, который нужно убрать из списка
     *                     (для того, чтобы нагрузку нельзя было передать самому себе)
     * @return array список должностных назначений, которые могут вести поток
     */
    private function get_possible_teachers($pitemid, $exclude=null)
    {
        $persons = $this->order->get_possible_teachers($pitemid);
        if ( !empty($persons) )
        {
            // Преобразовываем список к пригодному для элемента select виду
            $firstelement = array( 0 => '--- '.$this->dof->get_string('by_default',$this->im_code()).' ---' );
            $rez = $this->dof_get_select_values($persons, false,
                        'appointmentid', array('sortname','enumber'));
            asort($rez);
            $rez = $firstelement + $rez;
        }
        
        // Оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'appointments', 'code'=>'use'));
        $filtered = $this->dof->storage('acl')->get_acl_filtered_list($rez, $permissions);
        if ( is_int_string($exclude) )
        { // Если передали id того, кого не нужно видеть в списке.
            unset($filtered[$exclude]);
        }
        return $filtered;
    }

}
?>