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
 * Интерфейс комментариев к объектам Деканата. Классы форм
 * 
 * @package    im
 * @subpackage comments
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');
global $DOF;
// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** 
 * Форма создания/редактирования компетенций
 */
class dof_im_comments_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $id - ID компетенции
     */
    protected $id;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars;
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Идентификатор формы
        $formid = $mform->getAttribute('id');
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = $this->_customdata->id;
        $this->plugintype = $this->_customdata->plugintype;
        $this->plugincode = $this->_customdata->plugincode;
        $this->objectid = $this->_customdata->objectid;
        $this->code = $this->_customdata->code;
        $this->task = $this->_customdata->task;
        $this->returnurl = $this->_customdata->returnurl;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'plugintype', $this->plugintype);
        $mform->setType('plugintype', PARAM_TEXT);
        $mform->addElement('hidden', 'plugincode', $this->plugincode);
        $mform->setType('plugincode', PARAM_TEXT);
        $mform->addElement('hidden', 'objectid', $this->objectid);
        $mform->setType('objectid', PARAM_INT);
        $mform->addElement('hidden', 'code', $this->code);
        $mform->setType('code', PARAM_TEXT);
        $mform->addElement('hidden', 'task', $this->task);
        $mform->setType('task', PARAM_TEXT);
        $mform->addElement('hidden', 'returnurl', $this->returnurl);
        $mform->setType('returnurl', PARAM_URL);
        
        // Отображение формы в зависимости от задачи
        switch ( $this->task )
        {
            case 'edit' :
                // Заголовок формы
                $mform->addElement(
                        'header',
                        'form_comments_edit_title',
                        $this->dof->get_string('form_comments_edit_title', 'comments')
                        );
                
                // Редактирование комментария
                $mform->addElement(
                        'textarea',
                        'text',
                        $this->dof->get_string('form_comments_field_message', 'comments'),
                        ['form' => $formid, 'rows' => '5', 'cols' => '70']
                );
                // Кнопки подтверждения/отмены
                $mform->addElement(
                        'submit', 
                        'submit', 
                        $this->dof->get_string('form_comments_edit_submit', 'comments'),
                        ['form' => $formid]
                );
                $mform->addElement(
                        'submit', 
                        'cancel', 
                        $this->dof->get_string('form_comments_edit_cancel', 'comments'),
                        ['form' => $formid]
                );
                break;
            case 'delete' :
                $mform->addElement(
                        'header',
                        'form_comments_delete_title',
                        $this->dof->get_string('form_comments_delete_message', 'comments')
                );
                // Кнопки подтверждения/отмены
                $mform->addElement(
                        'submit', 
                        'submit', 
                        $this->dof->get_string('form_comments_delete_submit', 'comments'),
                        ['form' => $formid]
                );
                $mform->addElement(
                        'submit', 
                        'cancel', 
                        $this->dof->get_string('form_comments_delete_cancel', 'comments'),
                        ['form' => $formid]
                );
                break;
            case 'create' :
                // Заголовок формы
                $mform->addElement(
                        'header',
                        'form_comments_edit_title',
                        $this->dof->get_string('form_comments_create_title', 'comments')
                );
                
                // Добавление комментария
                $mform->addElement(
                        'textarea',
                        'text',
                        $this->dof->get_string('form_comments_field_message', 'comments'),
                        ['form' => $formid, 'rows' => '5', 'cols' => '70']
                );
                // Кнопка подтверждения
                $mform->addElement(
                        'submit', 
                        'submit', 
                        $this->dof->get_string('form_comments_create_submit', 'comments'),
                        ['form' => $formid]
                );
                break;
            default :
                break;
        }  
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /** 
     * Проверка данных формы
     * 
     * @param array $data - данные, пришедшие из формы
     *
     * @return array - массив ошибок, или пустой массив, если ошибок нет
     */
    function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;

        // Массив ошибок
        $errors = array();
        
        switch ( $data['task'] )
        {
            case 'edit' :
                if ( ! trim($data['text']) )
                {// Если пустой текст комментария
                    $errors['text'] = $this->dof->get_string('error_form_comments_edit_message_empty', 'comments');
                }
                break;
            case 'delete' :
                break;
            case 'create' :
                if ( ! trim($data['text']) )
                {// Если пустой текст комментария
                    $errors['text'] = $this->dof->get_string('error_form_comments_edit_message_empty', 'comments');
                }
                break;
        }  
        
        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');

        // Возвращаем ошибки, если они есть
        return $errors;
    }

    /**
     * Заполнение формы данными
     */
    function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( ! empty($this->id) )
        {// Заполнение значениями
            $comment = $this->dof->storage('comments')->get_record(array('id' => $this->id));
            
            if ( ! empty($comment) )
            {
                $mform->setDefault('text', $comment->text);
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
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            $returnurl = preg_replace('/&?commenttask=[^&]*/', '', $formdata->returnurl);
            $returnurl = preg_replace('/&?commentid=[^&]*/', '', $returnurl);
            if ( isset($formdata->cancel) )
            {
                redirect($returnurl);
            }
            if ( isset($formdata->submit) )
            {
                switch ( $formdata->task )
                {
                    case 'edit' :
                        if ( $this->dof->storage('comments')->is_access('edit/owner', $formdata->id) )
                        {
                            $comment = new stdClass();
                            $comment->id = $formdata->id;
                            $comment->plugintype = $formdata->plugintype;
                            $comment->plugincode = $formdata->plugincode;
                            $comment->objectid = $formdata->objectid;
                            $comment->text = $formdata->text;
                            $comment->code = $formdata->code;
                            // Сохраним комментарий
                            $id = $this->dof->storage('comments')->save($comment);
                        }
                        redirect($returnurl);
                        break;
                    case 'delete' :
                        // Проверим комментарий на существование
                        if ( ! empty($formdata->id) )
                        {
                            $exist = $this->dof->storage('comments')->is_exists(array('id' => $formdata->id));
                            if ( ! empty($exist) )
                            {
                                if ( $this->dof->storage('comments')->is_access('delete/owner', $formdata->id) )
                                {
                                    $id = $this->dof->workflow('comments')->change($formdata->id, 'deleted');
                                }
                                redirect($returnurl);
                            }
                        }
                        break;
                    case 'create' :
                        if ( $this->dof->storage('comments')->is_access('create') )
                        {
                            $comment = new stdClass();
                            $comment->plugintype = $formdata->plugintype;
                            $comment->plugincode = $formdata->plugincode;
                            $comment->objectid = $formdata->objectid;
                            $comment->text = $formdata->text;
                            $comment->code = $formdata->code;
                            // Сохраним комментарий
                            $id = $this->dof->storage('comments')->save($comment);
                        }
                        redirect($returnurl);
                        break;
                    default:
                        break;
                }
            }
           
        }
    }
}
?>