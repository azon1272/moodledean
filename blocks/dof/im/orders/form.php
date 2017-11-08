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
 * Форма фильтрации приказов
 *
 * @package     im
 * @subpackage  orders
 * @author      Dmitrii Shtolin <d.shtolin@gmail.com>
 * @copyright   2016
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('lib.php');
global $DOF;
// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

class dof_im_orders_prefilter_form extends dof_modlib_widgets_form
{
    /**
     * 
     * @var unknown
     */
    private $obj;

    /**
     * @var dof_control
     */
    protected $dof;

    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];

    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Получение данных
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', 0);
        $mform->setType('sesskey', PARAM_ALPHANUM);
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement('static', 'hidden', '');
        
        // Заголовок формы
        $mform->addElement('header', 'formtitle', 
            $this->dof->get_string('form_prefilter_title', 'orders'));
        
        // Тип плагина
        $ptypes = array_merge(
            [ 0 => $this->dof->get_string('form_prefilter_element_option_choose_ptype', 'orders') ], 
            $this->dof->storage('orders')->get_list_ptypes()
        );
        $mform->addElement(
            'select', 
            'ptype', 
            $this->dof->get_string('form_prefilter_element_ptype', 'orders') . ':', $ptypes
        );
        $mform->addRule(
            'ptype', 
            $this->dof->get_string('error_required_field', 'orders'), 
            'required', 
            'client'
        );
        
        // Код плагина
        $ajaxoptions = [
            'plugintype' => 'im',
            'plugincode' => 'orders',
            'querytype' => 'list_pcodes',
            'parentid' => 'id_ptype'
        ];
        $mform->addElement(
            'dof_ajaxselect', 
            'pcode', 
            $this->dof->get_string('form_prefilter_element_pcode', 'orders') . ':', 
            NULL, 
            $ajaxoptions
        );
        $mform->addRule(
            'pcode', 
            $this->dof->get_string('error_required_field', 'orders'), 
            'required', 
            'client'
        );
        
        // Код приказа
        $ajaxoptions = [
            'plugintype' => 'im',
            'plugincode' => 'orders',
            'querytype' => 'list_codes',
            'parentid' => 'id_pcode'
        ];
        $mform->addElement(
            'dof_ajaxselect', 
            'code', 
            $this->dof->get_string('form_prefilter_element_code', 'orders') . ':', 
            NULL, 
            $ajaxoptions
        );
        $mform->addRule(
            'code', 
            $this->dof->get_string('error_required_field', 'orders'), 
            'required', 
            'client'
        );
        
        // Кнопока отправки формы
        $this->add_action_buttons(
            FALSE, 
            $this->dof->get_string('form_prefilter_element_choose', 'orders')
        );
        
        // Очистка пограничных пробелов у всех элементов
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * Задаем проверку корректности введенных значений
     */
    function validation($data, $files)
    {
        $errors = [];
        
        // Проверим выбрали ли тип плагина
        if ( ! isset($data['ptype']) || (string) $data['ptype'] == '0' )
        {// Тип плагина не выбран
            $errors['ptype'] = $this->dof->
                get_string('error_required_field', 'orders', NULL, 'storage');
        } else
        {// Тип плагина выбран
            if ( ! in_array($data['ptype'], 
                array_keys($this->dof->storage('orders')->get_list_ptypes())) )
            {// Тип плагина не валиден
                $errors['ptype'] = $this->dof->get_string('error_field_value', 'orders');
            }
        }
        
        // Проверим значение кода плагина
        if ( ! isset($data['pcode']) || (string) $data['pcode'] == '0' )
        {// Код плагина не указан
            $errors['pcode'] = $this->dof->
                get_string('error_required_field', 'orders', NULL, 'storage');
        } else
        {// Код плагина выбран
            // Код
            $plugininfo = explode('_', $data['pcode']);
            if ( isset($plugininfo[1]) )
            {// Код определен
                $data['pcode'] = $plugininfo[1];
            }
            if ( ! in_array($data['pcode'], 
                array_keys($this->dof->storage('orders')->get_list_pcodes($data['ptype']))) )
            {// Значение кода не валидно
                $errors['pcode'] = $this->dof->get_string('error_field_value', 'orders');
            }
        }
        
        // Проверим значение кода приказа
        if ( ! isset($data['code']) || (string) $data['code'] == '0' )
        {// Код приказа указан
            $errors['code'] = $this->dof->
                get_string('error_required_field', 'orders', NULL, 'storage');
        } else
        {// Код приказа выбран
            if ( ! in_array($data['code'], 
                array_keys($this->dof->storage('orders')->get_list_codes($data['ptype'], $data['pcode']))) )
            {// Значение кода не валидно
                $errors['code'] = $this->dof->get_string('error_field_value', 'orders');
            }
        }
        
        // Вернуть ошибки валидации
        return $errors;
    }

    /**
     * Обработка формы
     *
     * @return void
     */
    function process()
    {
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data() )
        { // Форма подтверждена и данные получены

            // Редирект разрешен
            $canredirect = true;
            
            // Сбор данных для редиректа
            $this->addvars['ptype'] = (string)$formdata->ptype;
            $plugininfo = explode("_", $formdata->pcode);
            if ( isset($plugininfo[1]) )
            {// Код плагина получен
                $this->addvars['pcode'] = $plugininfo[1];
            } else
            {// Код плагина не получен
                $canredirect = false;
                // Уведомление об ошибке
                $this->dof->messages->add(
                    $this->dof->get_string('error_required_field', 'orders', NULL, 'storage'), 
                    'error');
            }
            // Код приказа
            $this->addvars['code'] = (string)$formdata->code;
            
            if ( $canredirect )
            {// Редирект разрешен
                redirect($this->dof->url_im('orders', '/list.php', $this->addvars));
            }
        }
    }
}