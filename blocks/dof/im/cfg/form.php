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
 *
 * Подключается из init.php.
 */

// Подключаем библиотеки
require_once ('lib.php');
// подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

/**
 * Класс формы для создания/редактирования настроек
 */
class dof_im_cfg_form extends dof_modlib_widgets_form
{

    protected $dof;

    protected $depid;

    protected $departmentid;

    protected $plugintype;

    protected $plugincode;

    function definition()
    {
        // данные для работы
        $this->depid = $this->_customdata->id;
        $this->dof = $this->_customdata->dof;
        $this->departmentid = optional_param('departmentid', 0, PARAM_INT);
        $this->plugintype = optional_param('plugintype', '', PARAM_TEXT);
        $this->plugincode = optional_param('plugincode', '', PARAM_TEXT);
        // создаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        // получим ВСЕ настройки этого подразделения(и выше, которые действуют и на его)
        $conds = new stdClass();
        $conds->departmentid = $this->departmentid;
        $conds->plugintype = $this->plugintype;
        $conds->plugincode = $this->plugincode;
        $configs = $this->dof->storage('config')->get_listing($conds);
        $con = new stdClass();
        $con->plugintype = '';
        $con->plugincode = '';
        foreach ( $configs as $config )
        { // перебираем все настройки
            if ( $con->plugintype != $config->plugintype )
            { // тип не совпал - новый заголово
                $mform->addElement('header', $config->plugintype, $config->plugintype);
            }
            if ( $con->plugincode != $config->plugincode )
            {
                $mform->addElement('html', '<a name="' . $config->plugincode . '"></a>');
                // вверх/вниз
                $mform->addElement('html', 
                    '<a href=#top style="font-size:14px;color:green;"><&#8593;' .
                         $this->dof->get_string('top', 'cfg') .
                         '></a>
                						    <a href=#down style="font-size:14px;color:green;"><' .
                         $this->dof->get_string('down', 'cfg') . '<b>&#8595;</b>></a>');
                // добавление именя КОДА елемента
                $mform->addElement('html', '<b>');
                $mform->addElement('static', $config->plugincode, $config->plugincode);
                $mform->addElement('html', '</b>');
            }
            if ( $con->plugintype == $config->plugintype && $con->plugincode == $config->plugincode )
            {
                $mform->addElement('html', '<br>');
            }
            // определим, какая же настройка активная(чтобы выделить её)
            $config_active = $this->dof->storage('config')->get_config($config->code, 
                $config->plugintype, $config->plugincode, $this->departmentid);
            if ( isset($config_active->id) and $config_active->id == $config->id )
            { // показать, что настройка активна
                $this->get_type_form($config->type, $config, true);
            } else
            { // эта настройка не активна
                $this->get_type_form($config->type, $config);
            }
            
            // переопределяем 
            $con = $config;
        }
        
        // кнопка создания
        $mform->addElement('submit', 'save', $this->dof->modlib('ig')->igs('save'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }

    /**
     * От типа настройки возвращает соотвественный код для формы
     *
     * @param string $type
     *            - тип настройки (char,text,select...)
     * @param object $config
     *            - настройка
     * @param bolena $flag
     *            - активна(true) иил нет ЭТА настройка
     */
    private function get_type_form( $type, $config, $flag = false )
    {
        $mform = & $this->_form;
        
        // Создаем элементы формы
        // ТУТ всегда стандартные значение
        // да/нет
        $extend = array(
            '0' => $this->dof->modlib('ig')->igs('yes'),
            '1' => $this->dof->modlib('ig')->igs('no')
        );
        $a = array(
            '0',
            '1'
        );
        // настройки для выбора cpassed итоговые оценки фильтрация
        $finalgrade = array(
            '0' => $this->dof->get_string('no_cfg', 'cfg'),
            '1' => $this->dof->get_string('by_programm', 'cfg'),
            '2' => $this->dof->get_string('by_programm_cstream', 'cfg')
        );
        // настройки для дней недели                
        $dayvar = $this->dof->modlib('refbook')->get_day_vars();
        
        if ( $flag )
        { // активна натсройка - поставим РАДИО, чтобы можно было ЭТУ активную переопределить или изменить
            $mform->addElement('html', '<fieldset>');
            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                $this->dof->get_string('config_active', 'cfg'), 'active');
            // удалени настройки (только в текущем подразделении)
            if ( $this->depid and $this->depid == $config->departmentid )
            { // чекбокс для удаления настройки
                $mform->addElement('checkbox', 'delete[' . $config->id . ']', '', 
                    $this->dof->get_string('delete', 'cfg'));
            }
        }
        // поставим якорь
        $mform->addElement('html', '<a name="' . $config->id . '"></a>');
        // имя подразделения в виде ссылки
        $depname = "<a href =" . $this->dof->url_im('cfg', 
            '/edit.php?departmentid=' . $config->departmentid . '#' . $config->id) . ">" .
             $this->depname($config->departmentid) . "</a>";
        $mform->addElement('static', 'dep1' . $config->id, 
            $this->dof->get_string('department', 'cfg'), $depname);
        $mform->addElement('static', 'type1' . $config->id, $this->dof->get_string('type', 'cfg'), 
            $config->type);
        $mform->addElement('static', 'code1' . $config->id, $this->dof->get_string('code', 'cfg'), 
            $config->code);
        if ( $type == 'select' )
        {
            $mform->addElement('static', 'value1' . $config->id, 
                $this->dof->get_string('value', 'cfg'), ${$config->code}[$config->value]);
        } else
        {
            $mform->addElement('static', 'value1' . $config->id, 
                $this->dof->get_string('value', 'cfg'), $config->value);
        }
        
        // настройки по умолчанию редактировать нельзя(depid=0)
        if ( ! empty($this->depid) )
        {
            switch ( $type )
            { // типы настройки  
                case 'checkbox':
                    // характерные для каждого типа ПОЛЯ
                    //$mform->addElement('static', 'noextend'.$config->id,$this->dof->get_string('noextend', 'cfg'), $extend[$config->noextend]);
                    $mform->addElement('html', '<br>');
                    if ( $flag )
                    { // есть флаг - добавим поля дле создания новой настройки
                        if ( $config->departmentid == $this->depid )
                        { // редактирование
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('edit_config', 'cfg'), 'edit');
                        } else
                        { // создание
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('new_config', 'cfg'), 'new');
                        }
                        $mform->addElement('static', 'dep' . $config->id, 
                            $this->dof->get_string('department', 'cfg'), 
                            $this->depname($this->depid));
                        $mform->addElement('static', 'type' . $config->id, 
                            $this->dof->get_string('type', 'cfg'), $config->type);
                        $mform->addElement('static', 'code' . $config->id, 
                            $this->dof->get_string('code', 'cfg'), $config->code);
                        $mform->addElement('select', 'value[' . $config->id . ']', 
                            $this->dof->get_string('value', 'cfg'), $a);
                        //$mform->addElement('select', 'noextend2'.$config->id,$this->dof->get_string('noextend', 'cfg'), $extend);
                        // по умолчанию
                        $mform->setDefault('radio[' . $config->id . ']', 'active');
                        //$mform->setDefault('noextend2'.$config->id, $config->noextend);
                        $mform->setDefault('value[' . $config->id . ']', $config->value);
                        // элементк - который визуально отделяет каждую настройку в БЛОК    
                        $mform->addElement('html', '</fieldset>');
                    }
                    break;
                
                case 'text':
                    // характерные для каждого типа ПОЛЯ ТЕКСТ
                    

                    //$mform->addElement('static', 'noextend'.$config->id,$this->dof->get_string('noextend', 'cfg'), $extend[$config->noextend]);
                    $mform->addElement('html', '<br>');
                    if ( $flag )
                    { // есть флаг - добавим поля дле создания новой настройки
                        if ( $config->departmentid == $this->depid )
                        { // редактирование
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('edit_config', 'cfg'), 'edit');
                        } else
                        { // создание
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('new_config', 'cfg'), 'new');
                        }
                        $mform->addElement('static', 'dep' . $config->id, 
                            $this->dof->get_string('department', 'cfg'), 
                            $this->depname($this->depid));
                        $mform->addElement('static', 'type' . $config->id, 
                            $this->dof->get_string('type', 'cfg'), $config->type);
                        $mform->addElement('static', 'code' . $config->id, 
                            $this->dof->get_string('code', 'cfg'), $config->code);
                        $mform->addElement('text', 'value[' . $config->id . ']', 
                            $this->dof->get_string('value', 'cfg'), 'size=20');
                        //$mform->addElement('select', 'noextend2'.$config->id,$this->dof->get_string('noextend', 'cfg'), $extend);
                    }
                    break;
                case 'textarea':
                    $mform->addElement('html', '<br>');
                    if ( $flag )
                    { // есть флаг - добавим поля дле создания новой настройки
                        if ( $config->departmentid == $this->depid )
                        { // редактирование
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('edit_config', 'cfg'), 'edit');
                        } else
                        { // создание
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('new_config', 'cfg'), 'new');
                        }
                        $mform->addElement('static', 'dep' . $config->id, 
                            $this->dof->get_string('department', 'cfg'), 
                            $this->depname($this->depid));
                        $mform->addElement('static', 'type' . $config->id, 
                            $this->dof->get_string('type', 'cfg'), $config->type);
                        $mform->addElement('static', 'code' . $config->id, 
                            $this->dof->get_string('code', 'cfg'), $config->code);
                        $mform->addElement('textarea', 'value[' . $config->id . ']', 
                            $this->dof->get_string('value', 'cfg'), 
                            [
                                'cols' => 50,
                                'rows' => 7
                            ]);
                        //$mform->addElement('select', 'noextend2'.$config->id,$this->dof->get_string('noextend', 'cfg'), $extend);
                    }
                    break;
                
                case 'select':
                    ;
                    // характерные для каждого типа ПОЛЯ SELECT
                    $mform->addElement('html', '<br>');
                    if ( $flag )
                    { // есть флаг - добавим поля дле создания новой настройки
                        if ( $config->departmentid == $this->depid )
                        { // редактирование
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('edit_config', 'cfg'), 'edit');
                        } else
                        { // создание
                            $mform->addElement('radio', 'radio[' . $config->id . ']', null, 
                                $this->dof->get_string('new_config', 'cfg'), 'new');
                        }
                        $mform->addElement('static', 'dep' . $config->id, 
                            $this->dof->get_string('department', 'cfg'), 
                            $this->depname($this->depid));
                        $mform->addElement('static', 'type' . $config->id, 
                            $this->dof->get_string('type', 'cfg'), $config->type);
                        $mform->addElement('static', 'code' . $config->id, 
                            $this->dof->get_string('code', 'cfg'), $config->code);
                        $mform->addElement('select', 'value[' . $config->id . ']', 
                            $this->dof->get_string('value', 'cfg'), ${$config->code});
                        //$mform->addElement('select', 'noextend2'.$config->id,$this->dof->get_string('noextend', 'cfg'), $extend);
                    }
                
                 case 'password': 
                 case 'passwordunmask': 
                 case 'textarea': 
                 case 'date_selector': 
                 case 'date_time_selector': 
                 case 'selectyesno': 
                 case 'advcheckbox': 
                 case 'file': 
                 case 'radio': 
                 case 'htmleditor': 
                 default:
                    break;
            }
        }
        // по умолчанию
        $mform->setDefault('radio[' . $config->id . ']', 'active');
        //$mform->setDefault('noextend2'.$config->id, $config->noextend);
        $mform->setDefault('value[' . $config->id . ']', $config->value);
        $mform->setType('value[' . $config->id . ']', PARAM_RAW);
        // элемент - который визуально отделяет каждую настройку в БЛОК    
        $mform->addElement('html', '</fieldset>');
        
        if ( $flag )
        { // здесь записаны блокирующие поля - неактивные
            $mform->disabledIf('value[' . $config->id . ']', 'radio[' . $config->id . ']', 'eq', 
                'active');
            //$mform->disabledIf('noextend2'.$config->id,'radio'.$config->id, 'eq','1');
            $mform->disabledIf('radio[' . $config->id . ']', 'delete[' . $config->id . ']', 
                'checked');
            //$mform->disabledIf('noextend2'.$config->id,'delete'.$config->id, 'checked'); 
            $mform->disabledIf('value[' . $config->id . ']', 'delete[' . $config->id . ']', 
                'checked');
        }
        return true;
    }

    /*
     * Возвращает имя подразделения[код] или ВСЕ подразделения(если 0)
     * @param integer $id - id подразделения
     * return string - имя подразделения
     */
    private function depname( $depid )
    {
        if ( $obj = $this->dof->storage('departments')->get($depid) )
        { // получили id подразделения - выведем название и код
            $depname = $obj->name . '[' . $obj->code . ']';
        } else
        { // нету - значит выводим для всех
            $depname = $this->dof->get_string('all_departments', 'cfg');
        }
        return $depname;
    }

    /**
     * Обработать пришедшие из формы данные, сменить статус,
     * создать и выполнить приказ и вывести сообщение
     * 
     * @return bool
     */
    public function process()
    {
        //die('fd');
        $mform = & $this->_form;
        $error = array();
        if ( $this->is_submitted() and $formdata = $this->get_data() )
        { // данные отправлены в форму, и не возникло ошибок
            //print_object($formdata); 
            // соберем данные
            

            // ДЕЛАЕМ ХУК для переопределения данных(или ещё что нам там понадобиться в дочерних классах)
            $formdata = $this->get_config_objects($formdata);
            
            $radio = $formdata->radio;
            $value = $formdata->value;
            if ( isset($formdata->delete) )
            { // есть удавление - запомним
                $delete = $formdata->delete;
            }
            foreach ( $radio as $id => $text )
            {
                // создать новую
                if ( $text == 'new' )
                {
                    // готовим объект
                    $obj = $this->dof->storage('config')->get($id);
                    // можеи поменять только значение и подразделение
                    $obj->value = $value[$id];
                    $obj->departmentid = $this->depid;
                    // вставим новый объект
                    if ( ! $this->dof->storage('config')->insert($obj) )
                    { // запишем ошибку
                        $error[$id] = 'new';
                    }
                    // дальше незачем идти
                    continue;
                }
                // редактировать
                if ( $text == 'edit' )
                {
                    $obj = new stdClass();
                    // можеи поменять только значение и подразделение
                    $obj->value = $value[$id];
                    // вставим новый объект
                    if ( ! $this->dof->storage('config')->update($obj, $id) )
                    { // запишем ошибку
                        $error[$id] = 'edit';
                    }
                }
            }
            // удаление
            if ( isset($delete) and ! empty($delete) )
            {
                foreach ( $delete as $id => $value )
                {
                    if ( ! $this->dof->storage('config')->delete($id) )
                    { // запишем ошибку
                        $error[$id] = 'delete';
                    }
                }
            }
            
            // проверка на ошибки 
            if ( ! empty($error) )
            {
                $message = '';
                foreach ( $error as $id => $value )
                {
                    if ( $value == 'delete' )
                    { // ошибка удаления
                        $message .= '<div style="color:red;">' .
                             $this->dof->get_string('delete_error', 'cfg', $id) . '</div>';
                    }
                    if ( $value == 'edit' )
                    { // ошибка редактирования
                        $message .= '<div style="color:red;">' .
                             $this->dof->get_string('edit_error', 'cfg', $id) . '</div>';
                    }
                    if ( $value == 'new' )
                    { // ошибка создания новой
                        $message .= '<div style="color:red;">' .
                             $this->dof->get_string('new_error', 'cfg', $id) . '</div>';
                    }
                }
                return $message;
            } else
            { // ВСЁ ХОРОШО !!!
                $adds = array(
                    'departmentid' => $this->departmentid,
                    'plugincode' => $this->plugincode,
                    'plugintype' => $this->plugintype
                );
                redirect($this->dof->url_im('cfg', '/edit.php', $adds), '', 0);
            }
        }
        return '';
    }

    /**
     * Дополнительные проверки/ действия для работы с конфигурацией натроек
     * (переопределяется в дочерних классах, если необходимо)
     * 
     * @param object $formdata
     *            - данные пришедние из формы
     * @return object $formdata -
     */
    protected function get_config_objects( $formdata )
    {
        return $formdata;
    }
}

?>