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
 * Классы форм
 * 
 * @package    im
 * @subpackage skills
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем базовые функции плагина
require_once('lib.php');
// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/** 
 * Форма создания/редактирования компетенций
 */
class dof_im_skills_edit_form extends dof_modlib_widgets_form
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
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->id = optional_param('id', 0, PARAM_INT);
        $this->addvars = $this->_customdata->addvars;

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'id', $this->id);
        $mform->setType('id', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
                'header', 
                'form_skills_edit_title', 
                $this->dof->get_string('form_skills_edit_title', 'skills')
        );
        
        // Название компетенции
        $mform->addElement(
                'text', 
                'name', 
                $this->dof->get_string('form_skills_edit_name', 'skills')
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '');
        
        // Сложность
        $mform->addElement(
                'text',
                'complexity',
                $this->dof->get_string('form_skills_edit_complexity', 'skills')
        );
        $mform->setType('complexity', PARAM_FLOAT);
        $mform->addRule(
                'complexity',
                $this->dof->get_string(
                    'error_form_skills_edit_complexity_not_valid',
                    'skills'
                ), 
                'numeric',
                null,
                'client');
        $mform->setDefault('complexity', '0.0');

        // Родитель
        $list = $this->get_parent_list();
        $list[0] = $this->dof->get_string('form_skills_edit_parentid_top', 'skills');
        $mform->addElement(
                'select', 
                'parentid', 
                $this->dof->get_string('form_skills_edit_parentid', 'skills'), 
                $list
        );
        $mform->setDefault('parentid', $this->addvars['parentskill']);
        
        $mform->addElement('submit', 'submit', $this->dof->get_string('form_skills_edit_submit', 'skills'));
        
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
        
        if ( ! trim($data['name']) )
        {// Если пустое имя компетенции
            $errors['name'] = $this->dof->get_string('error_form_skills_edit_empty_name', 'skills');
        }
        
        if ( $data['complexity'] > 1 || $data['complexity'] < 0 || ! is_float($data['complexity'] ) )
        {// Уровень сожности не валиден
            $errors['complexity'] = $this->dof->get_string(
                    'error_form_skills_edit_complexity_not_valid', 
                    'skills'
            );
        }
        // Проверим существование родителя
        if ( isset($data['parentid']) && $data['parentid'] > 0 )
        {
            $parent = $this->dof->storage('skills')->is_exists($data['parentid']);
            if ( empty($parent) )
            {// Родитель не найден
                $errors['parentid'] = $this->dof->get_string(
                    'error_form_skills_edit_parent_not_found',
                    'skills'
                );
            }
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
            $skill = $this->dof->storage('skills')->get($this->id);
            $mform->setDefault('name', $skill->name);
            $mform->setDefault('complexity', $skill->complexity);
            $mform->setDefault('parentid', $skill->parentid);
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
            // Сохраним компетенцию
            $id = $this->dof->storage('skills')->save($formdata);
            if ( $id )
            {// Успешное сохранение
                $this->addvars['success'] = 1;
                $this->addvars['id'] = $id;
                redirect($this->dof->url_im('skills', '/edit.php', $this->addvars));
            } else 
            {// Ошибки
                $this->addvars['success'] = 0;
                redirect($this->dof->url_im('skills', '/edit.php', $this->addvars));
            }
        }
    }
    
    /**
     * Получить список родителей для поля
     * 
     * @param $id - ID - элемента-родителя
     * @param $level - Уровень вложенности
     * 
     * @return array - Массив компетенций
     */
    private function get_parent_list($id = 0, $level = 0)
    {
        $result = array();
        $parents = $this->dof->storage('skills')->get_records(array('parentid' => $id), '', 'id, name');
        if ( ! empty($parents) )
        {// Сформируем массив
            // Получим отступ
            $shift = str_pad('', $level, '-');
            foreach ( $parents as $skill )
            {
                if ( $skill->id == $this->id )
                {// Удалим текущий редактируемый элемент
                    continue;
                }
                // Сформируем элемент
                $result[$skill->id] = $shift.$skill->name;
                // Получим массид дочерних
                $childrens = $this->get_parent_list($skill->id, $level + 1);
                // Добавим к исходному
                $result = $result + $childrens;
            }
        }
        
        return $result; 
    }
}
?>