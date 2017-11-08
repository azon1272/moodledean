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
 * Форма редактирования базового приказа
 *
 * @package storage
 * @subpackage orders
 * @author Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright 2015
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$this->dof->modlib('widgets')->webform();

class dof_storage_orders_baseorder_edit extends dof_modlib_widgets_form
{

    private $obj;

    /**
     *
     * @var dof_control
     */
    protected $dof;

    /**
     *
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     *
     * @var $defaultvalues - массив дефолтных значений, переданных через GET
     */
    protected $defaultvalues = [];

    function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->defaultvalues = $this->_customdata->defaultvalues;
        // создаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        $mform->addElement('hidden', 'sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement('static', 'hidden', '');
        
        $mform->addElement('hidden', 'id', $this->defaultvalues->id);
        $mform->setType('id', PARAM_INT);
        
        //создаем заголовок формы
        $mform->addElement('header', 'formtitle', 
            $this->dof->get_string('form_edit_title', 'orders', null, 'storage'));
        
        //заметки
        $mform->addElement('textarea', 'notes', 
            $this->dof->get_string('form_edit_element_notes', 'orders', null, 'storage'));
        
        //По умолчанию поле с номером приказа доступно
        $availability = '';
        //Номер приказа
        $actualstatuses = $this->dof->workflow('orders')->get_meta_list('actual');
        $order = $this->dof->storage('orders')->get($this->defaultvalues->id);
        if ( ! in_array($order->status, array_keys($actualstatuses)) )
        {
            //Приказ в неподходящем для изменения номера статусе
            $availability = 'disabled';
        }
        
        $mform->addElement('text', 'num', 
            $this->dof->get_string('form_edit_element_num', 'orders', null, 'storage'), 
            $availability);
        $mform->setType('num', PARAM_TEXT);
        
        // кнопоки сохранить и отмена
        $this->add_action_buttons(true, 
            $this->dof->get_string('form_edit_element_save', 'orders', null, 'storage'));
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Обработка формы
     *
     * @return string|boolean html с результатом обработки, либо false при ошибке
     */
    function process()
    {
        if ( ! empty($this->addvars['backurl']) )
        { //переданный параметр соответствует разрешенным страницам возврата
            $backurl = $this->addvars['backurl'];
        } else
        { //попытка перехода на неразрешенную страницу
            //переведем на страницу по умолчанию
            $backurl = $this->dof->url_im('orders','/view.php');
        }
        
        if ( $this->is_submitted() and confirm_sesskey() and $this->is_validated() and
             $formdata = $this->get_data() )
        {
            $order = $this->dof->storage('orders')->get_record(
                [
                    'id' => $formdata->id
                ]);
            
            $orderinstance = $this->dof->storage('orders')->order($order->plugintype, 
                $order->plugincode, $order->code, $formdata->id);
            
            if ( $order->notes != $formdata->notes )
            {
                //сохраним заметки (не участвует в подписи и менять их можно всегда)
                $resultsavenotes = $orderinstance->notes($formdata->notes);
            } else
            {
                $resultsavenotes = true;
            }
            
            //Получим актуальные статусы, находясь в них можно менять номер приказа
            $actualstatuses = $this->dof->workflow('orders')->get_meta_list('actual');
            //false, если возникли ошибки при сохранении номера приказа
            $resultsavenum = true;
            if ( isset($formdata->num) )
            {
                if ( $order->num != $formdata->num and
                     in_array($order->status, array_keys($actualstatuses)) )
                {
                    
                    //приказ в статусе, позволяющем менять данные
                    //сохраниим номер
                    $resultsavenum = $orderinstance->save_num($formdata->num);
                    if( ! $resultsavenum )
                    {//возникла ошибка при сохранении номера
                        $this->dof->messages->add(
                            $this->dof->get_string('error_order_num_not_unique', 'orders', null, 'storage'), 
                            'error');
                    }
                } elseif ( $order->num != $formdata->num )
                {
                    $resultsavenum = false;
                    $this->dof->messages->add(
                        $this->dof->get_string('error_status_not_suit', 'orders', null, 'storage'), 
                        'error');
                }
            }
            
            if ( $resultsavenum && $resultsavenotes )
            {
                //сохранение всех данных прошло успешно
                redirect($backurl);
            }
        }
        if ( $this->is_cancelled() )
        {
            redirect($backurl);
        }
        return false;
    }

    /**
     * Задаем проверку корректности введенных значений
     */
    function validation( $data, $files )
    {
        $errors = [];
        return $errors;
    }
}

/**
 * Форма фильтрации приказов
 *
 * @package storage
 * @subpackage orders
 * @author Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright 2015
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dof_storage_orders_baseorder_filter extends dof_modlib_widgets_form
{

    private $obj;

    /**
     *
     * @var dof_control
     */
    protected $dof;

    /**
     *
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     *
     * @var $defaultvalues - массив дефолтных значений, переданных через GET
     */
    protected $defaultvalues = [];

    /**
     *
     * @var $count - количество результатов при полной выборке
     */
    protected $count = 0;

    /**
     *
     * @var $html - код с результатом
     */
    protected $html = '';

    function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->defaultvalues = $this->_customdata->defaultvalues;
        // создаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        $mform->addElement('hidden', 'sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // тип плагина
        $mform->addElement('hidden', 'ptype', null);
        $mform->setType('ptype', PARAM_ALPHANUM);
        $mform->addRule('ptype', 
            $this->dof->get_string('error_required_field', 'orders', null, 'storage'), 'required');
        
        // код плагина
        $mform->addElement('hidden', 'pcode', null);
        $mform->setType('pcode', PARAM_ALPHANUMEXT);
        $mform->addRule('pcode', 
            $this->dof->get_string('error_required_field', 'orders', null, 'storage'), 'required');
        
        // код приказа
        $mform->addElement('hidden', 'code', null);
        $mform->setType('code', PARAM_ALPHANUMEXT);
        $mform->addRule('code', 
            $this->dof->get_string('error_required_field', 'orders', null, 'storage'), 'required');
        
        //создаем заголовок формы
        $mform->addElement('header', 'formtitle', 
            $this->dof->get_string('form_filter_title', 'orders', null, 'storage'));
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement('static', 'hidden', '');
        
        // статус приказа
        $statuses = $this->dof->workflow('orders')->get_list();
        $statuseselements = [];
        foreach ( $statuses as $status => $statuslabel )
        { // Cобираем массив с чекбоксами
            $statuseselements[] = $mform->createElement('checkbox', $status, '', $statuslabel);
        }
        // Добавляем чекбоксы в одну группу
        $mform->addGroup($statuseselements, 'status', 
            $this->dof->get_string('form_filter_element_status', 'orders', null, 'storage'), 
            '&nbsp;');
        
        //         //Поиск по диапазону дат исполнения договора
        //         $mform->addElement('checkbox', 'use_exdate', $this->dof->get_string('use_exdate', 'orders'));
        //         $mform->addElement('dof_date_selector', 'exdate_begin', $this->dof->get_string('exdate_begin', 'orders'));
        //         $mform->addElement('dof_date_selector', 'exdate_end', $this->dof->get_string('exdate_end', 'orders'));
        

        //         $mform->disabledIf('exdate_begin', 'use_exdate');
        //         $mform->disabledIf('exdate_end', 'use_exdate');
        

        // кнопоки сохранить и отмена
        $this->add_action_buttons(false, 
            $this->dof->get_string('form_filter_element_filter', 'orders', null, 'storage'));
        
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Обработка формы
     *
     * @return boolean с результатом обработки
     */
    function process( $countonly = false )
    {
        //объявляем массив с результирующими данными
        $result = false;
        //Если отправили форму и все на месте, либо если параметры передали через GET и они проходят валидацию
        if ( ($this->is_submitted() and confirm_sesskey() and $this->is_validated() and
             $formdata = $this->get_data()) or (count($this->defaultvalues) > 0 and
             $formdata = $this->defaultvalues and
             count($this->validation(get_object_vars($this->defaultvalues), null)) == 0) )
        {
            $ptype = $formdata->ptype;
            $pcode = $formdata->pcode;
            $code = $formdata->code;
            $ownerid = isset($formdata->ownerid) ? $formdata->ownerid : NULL;
            $signerid = isset($formdata->signerid) ? $formdata->signerid : NULL;
            $statuses = isset($formdata->status) ? array_keys($formdata->status) : [
                NULL
            ];
            $this->addvars['o_status']=implode(',',$statuses);
            
            $backurladdvars = $this->addvars;
            unset($backurladdvars['backurl']);
            $this->addvars['backurl']=$this->dof->url_im('orders','/list.php', $backurladdvars);
            
            // Получение html-кода таблицы с подразделениями
            $orders = $this->dof->storage('orders')->get_list_by_code($ptype, $pcode, $code, 
                $this->addvars['departmentid'], $ownerid, $signerid, $statuses, 
                ($this->addvars['limitfrom'] - 1), $this->addvars['limitnum']);
            
            $rows = [];
            //если список приказов не пуст
            if ( count($orders) > 0 )
            {
                //для каждого приказа создаем экземпляр подходящего класса
                foreach ( $orders as $order )
                {
                    //$orderinstance = new $classname($this->dof, $order->id);
                    

                    $orderinstance = $this->dof->storage('orders')->order($ptype, $pcode, $code, 
                        $order->id);
                    
                    //добавляем строку приказа для формирования таблицы
                    $rows[] = $orderinstance->show_tablerow($this->addvars);
                }
                
                $this->count = $this->dof->storage('orders')->get_list_by_code($ptype, $pcode, 
                    $code, $this->addvars['departmentid'], $ownerid, $signerid, $statuses, '', '', 
                    null, 'id ASC', true);
                
                //возвращаем сформировнную таблицу из собранных строк
                if ( $this->html = $orderinstance->show_table($rows) )
                {
                    $result = true;
                }
            } else
            {
                //приказов не было
                //напишем сообщение
                $this->dof->messages->add(
                    $this->dof->get_string('message_no_orders', 'orders', null, 'storage'), 
                    'message');
                //вернем пустую строку, так как ошибки не было, но и приказов не было
                $this->html = '';
                $result = true;
            }
        }
        return $result;
    }

    public function get_process_result()
    {
        return $this->html;
    }

    public function get_pages_count()
    {
        return $this->count;
    }

    /**
     * Задаем проверку корректности введенных значений
     */
    function validation( $data, $files )
    {
        $errors = array();
        // Проверим выбрали ли тип плагина
        if ( ! isset($data['ptype']) or (string) $data['ptype'] == '0' )
        {
            $errors['ptype'] = $this->dof->get_string('error_required_field', 'orders', null, 
                'storage');
        } else
        {
            if ( ! in_array($data['ptype'], 
                array_keys($this->dof->storage('orders')->get_list_ptypes())) )
            {
                $errors['ptype'] = $this->dof->get_string('error_field_value', 'orders');
            }
        }
        // Проверим значение кода плагина
        if ( ! isset($data['pcode']) or (string) $data['pcode'] == '0' )
        {
            $errors['pcode'] = $this->dof->get_string('error_required_field', 'orders', null, 
                'storage');
        } else
        {
            if ( ! in_array($data['pcode'], 
                array_keys($this->dof->storage('orders')->get_list_pcodes($data['ptype']))) )
            {
                $errors['pcode'] = $this->dof->get_string('error_field_value', 'orders');
            }
        }
        // Проверим значение кода приказа
        if ( ! isset($data['code']) or (string) $data['code'] == '0' )
        {
            $errors['code'] = $this->dof->get_string('error_required_field', 'orders', null, 
                'storage');
        } else
        {
            if ( ! in_array($data['code'], 
                array_keys(
                    $this->dof->storage('orders')->get_list_codes($data['ptype'], $data['pcode']))) )
            {
                $errors['code'] = $this->dof->get_string('error_field_value', 'orders');
            }
        }
        
        return $errors;
    }
}

class dof_storage_orders_baseorder_change_status extends dof_modlib_widgets_form
{

    private $obj;

    /**
     *
     * @var dof_control
     */
    protected $dof;

    /**
     *
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    /**
     *
     * @var $defaultvalues - массив дефолтных значений, переданных через GET
     */
    protected $defaultvalues = [];

    /**
     *
     * @var $html - код с результатом
     */
    protected $html = '';

    function definition()
    {
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        $this->defaultvalues = $this->_customdata->defaultvalues;
        // создаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        $mform->addElement('hidden', 'sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement('static', 'hidden', '');
        
        $mform->addElement('hidden', 'id', $this->defaultvalues->id);
        $mform->setType('id', PARAM_INT);
        
        //создаем заголовок формы
        $mform->addElement('header', 'formtitle', 
            $this->dof->get_string('form_change_status_title', 'orders', null, 'storage'));
        
        //приказ
        $order = $this->dof->storage('orders')->get_record(
            [
                'id' => $this->defaultvalues->id
            ]);
        //доступны для переключения статусы
        $statuses = $this->dof->workflow('orders')->get_available_to_user($this->defaultvalues->id);
        //название текущего статуса
        $currentstatusname = $this->dof->workflow('orders')->get_name($order->status);
        //подсказка о том, что это текущий статус
        $currentstatushint = " (" . $this->dof->get_string(
            'form_change_status_element_status_current', 'orders', null, 'storage') . ")";
        //текущий статус со сформированным названием
        $currentstatus = [
            $order->status => $currentstatusname . $currentstatushint
        ];
        //список статусов для селекта
        $selectstatuses = $currentstatus + $statuses;
        //заметки
        $mform->addElement('select', 'newstatus', 
            $this->dof->get_string('form_change_status_element_status', 'orders', null, 'storage'), 
            $selectstatuses);
        
        // кнопоки сохранить и отмена
        $this->add_action_buttons(false, 
            $this->dof->get_string('form_change_status_element_change_status', 'orders', null, 
                'storage'));
        $mform->disabledIf('submitbutton', 'newstatus', 'eq', $order->status);
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Обработка формы
     *
     * @return boolean с результатом обработки
     */
    function process()
    {
        // Ссылка на возврат
        $backlink = $this->dof->url_im('orders', '/view.php', $this->addvars);
        
        //изменяемый приказ
        $orderid = null;
        if ( isset($this->addvars['id']) )
        {
            $orderid = $this->addvars['id'];
        }
        
        //устанавливаемый статус
        $newstatus = null;
        if ( isset($this->addvars['newstatus']) )
        {
            $newstatus = $this->addvars['newstatus'];
        }
        
        if ( $this->is_submitted() and confirm_sesskey() and $this->is_validated() and
             $formdata = $this->get_data() )
        { //если есть данные из формы - переопределяем полученные ранее
            $orderid = $formdata->id;
            $newstatus = $formdata->newstatus;
        }
        
        if ( $orderid == null or $newstatus == null )
        { //не достаточно исходных данных
            return false;
        }
        
        //проверим существование приказа
        $order = $this->dof->storage('orders')->get_record([
            'id' => $orderid
        ]);
        if ( empty($order) )
        { //приказ не существует
            $this->dof->messages->add(
                $this->dof->get_string('error_element_id', 'orders') . ": " .
                     $this->dof->get_string('error_field_value', 'orders'), 'error');
            return false;
        }
        
        //проверим возможность перехода в статус при помощи ручного переключения
        $availablestatusestouser = $this->dof->workflow('orders')->get_available_to_user($order->id);
        if ( ! in_array($newstatus, array_keys($availablestatusestouser)) )
        { //нашего статуса нет в доступных для перехода вручную
            $backbtn = $this->dof->modlib('widgets')->button(
                $this->dof->get_string('error_status_not_available', 'orders'), $backlink, 'return');
            $this->dof->messages->add($backbtn, 'error');
            return false;
        }
        
        if ( $this->addvars['confirmed'] )
        { //смена статуса подтверждена
            if ( $this->dof->workflow('orders')->change($order->id, $newstatus) )
            { //статус сменился успешно
                redirect(
                    $this->dof->url_im('orders', '/view.php', 
                        [
                            'id' => $this->addvars['id'],
                            'departmentid' => $this->addvars['departmentid']
                        ]));
            } else
            { //возникла ошибка при смене статуса
                $backbtn = $this->dof->modlib('widgets')->button(
                    $this->dof->get_string('error_status_not_available', 'orders'), $backlink, 
                    'return');
                $this->dof->messages->add($backbtn, 'error');
                return false;
            }
        } else
        { //смена статуса не подтверждена - выведем форму подтверждения
            $this->addvars['id'] = $order->id;
            $this->addvars['newstatus'] = $newstatus;
            // Ссылка на страницу обработки смены статуса (действие подтверждено)
            $linkyes = $this->dof->url_im('orders', '/change_status.php', 
                array_merge($this->addvars, 
                    [
                        'confirmed' => 1
                    ]));
            
            //Языковая строка подтверждения
            $confirmation = $this->dof->get_string('confirmation_change_status', 'orders');
            // Форма подтверждения удаления
            ob_clean();
            $this->dof->modlib('widgets')->notice_yesno($confirmation, $linkyes, $backlink);
            $this->html .= ob_get_clean();
            
            return true;
        }
    }

    /**
     * Задаем проверку корректности введенных значений
     */
    function validation( $data, $files )
    {
        $errors = [];
        return $errors;
    }

    public function get_process_result()
    {
        return $this->html;
    }
}