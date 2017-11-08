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
 * Ведомость оценок по подписке персоны. Классы форм.
 * 
 * @package    im
 * @subpackage journal
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('lib.php');
// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

/**
 * Класс формы выбора критериев отображения ведомости
 */
class dof_im_journal_pbcgl_sourceselect extends dof_modlib_widgets_form
{  
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];
    
    public function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
            'header',
            'form_header',
            $this->dof->get_string('pbcgl_form_sourceselect_header', 'journal')
        );
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );
        
        // Временной интервал
        $options = [];
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $currenttime = time();
        $options['timezone'] = $usertimezone;
        $options['calendartype'] = 'two_calendar';
        $timestart = $currenttime;
        $timeend = $currenttime;
        if ( isset($this->addvars['timestart']) && is_int($this->addvars['timestart']) && $this->addvars['timestart'] >= 0 )
        {// Указан начальный интервал времени
            $timestart = $this->addvars['timestart'];
        }
        if ( isset($this->addvars['timeend']) && is_int($this->addvars['timeend']) )
        {// Указан конечный интервал времени
            if ( $timestart > $this->addvars['timeend'] )
            {// Ошибка интервала
                $this->dof->messages->add($this->dof->get_string('pbcgl_form_sourceselect_notice_timeinterval', 'journal'), 'notice');
                $timestart = $currenttime;
                $timeend = $currenttime;
            } else
            {// Конечный интревал передан верно
                $timeend = $this->addvars['timeend'];
            }
        }
        
        $options['date_from'] = $timestart;
        $options['date_to'] = $timeend;
        
        $mform->addElement(
            'dof_calendar',
            'sourceselect_timeinterval',
            $this->dof->get_string('pbcgl_form_sourceselect_timeinterval', 'journal'),
            $options
        );
        
        // Академическая группа
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'agroups';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = $this->addvars['departmentid'];
        $options['querytype']  = 'agroups_list';
        $mform->addElement(
            'dof_autocomplete',
            'sourceselect_agroupid',
            $this->dof->get_string('pbcgl_form_sourceselect_agroupid', 'journal'),
            [],
            $options
        );
        
        // Подписка на программу
        $options = [];
        $options['plugintype'] = 'storage';
        $options['plugincode'] = 'programmsbcs';
        $options['sesskey']    = sesskey();
        $options['type']       = 'autocomplete';
        $options['departmentid'] = $this->addvars['departmentid'];
        $options['querytype']  = 'programmbcs_list';
        $mform->addElement(
            'dof_autocomplete',
            'sourceselect_programmbcid',
            $this->dof->get_string('pbcgl_form_sourceselect_programmbcid', 'journal'),
            [],
            $options
        );
        
        // Опции отображения ведомости
        $group = [];
        $group[] = $mform->createElement(
            'static',
            'sourceselect_static_viewoptions',
            '',
            $this->dof->get_string('sourceselect_static_viewoptions', 'journal') 
        );
        $select = [
            0 => $this->dof->get_string('pbcgl_form_sourceselect_grouping_cpassed_cpassed', 'journal'),
            1 => $this->dof->get_string('pbcgl_form_sourceselect_grouping_cpassed_cstream', 'journal'),
            2 => $this->dof->get_string('pbcgl_form_sourceselect_grouping_cpassed_programmitem', 'journal')
        ];
        $group[] = $mform->createElement(
            'select',
            'sourceselect_grouping_cpassed',
            $this->dof->get_string('pbcgl_form_sourceselect_grouping_cpassed', 'journal'),
            $select
        );
        $select = [
            0 => $this->dof->get_string('pbcgl_form_sourceselect_grouping_time_time', 'journal'),
            1 => $this->dof->get_string('pbcgl_form_sourceselect_grouping_time_day', 'journal')
        ];
        $group[] = $mform->createElement(
            'select',
            'sourceselect_grouping_time',
            $this->dof->get_string('pbcgl_form_sourceselect_grouping_time', 'journal'),
            $select
        );
        
        $mform->addGroup($group, 'grouping', '', '', false);
        
        
        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'submit',
            $this->dof->get_string('pbcgl_form_sourceselect_submit', 'journal')
        );
        $group[] = $mform->createElement(
            'submit',
            'export_csv',
            $this->dof->get_string('pbcgl_form_sourceselect_export_csv', 'journal')
            );
        $group[] = $mform->createElement(
            'cancel',
            'cancel',
            $this->dof->get_string('pbcgl_form_sourceselect_clear', 'journal')
        );
        $mform->addGroup($group, 'buttons', '', '', false);
       
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Задаем проверку корректности введенных значений
     */
    public function validation($data, $files)
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
                
        // Массив ошибок
        $errors = [];

        // Проверки подразделения
        if ( $data['departmentid'] != $this->addvars['departmentid'] )
        {// Смена подразделения во время получения данных
            $errors['hidden'] = 
                $this->dof->get_string('error_pbcgl_form_sourceselect_department_changed', 'journal');
        }
        
        // Проверки группы
        $exists = $this->dof->storage('agroups')->is_exists((int)$data['sourceselect_agroupid']['id']);
        if ( empty($exists) && $data['sourceselect_agroupid']['id'] != 0 )
        {// Идентификатор группы указан неверно
            $errors['sourceselect_agroupid'] = $this->dof->get_string(
                'error_pbcgl_form_sourceselect_agroupid_not_found', 'journal');
        } else 
        {
            if ( $data['sourceselect_agroupid']['id'] != 0 )
            {// Группа найдена
                $access = $this->dof->storage('agroups')->is_access(
                    'use',
                    (int)$data['sourceselect_agroupid']['id'],
                    NULL,
                    $this->addvars['departmentid']
                    );
                if ( empty($access) )
                {// Нет доступа к группе
                    $errors['sourceselect_agroupid'] = $this->dof->get_string(
                        'error_pbcgl_form_sourceselect_agroupid_access', 'journal');
                }
            }
        }
        
        // Проверки подписки
        $exists = $this->dof->storage('programmsbcs')->is_exists((int)$data['sourceselect_programmbcid']['id']);
        if ( empty($exists) && $data['sourceselect_programmbcid']['id'] != 0 )
        {// Идентификатор подписки на программу указан неверно
            $errors['sourceselect_programmbcid'] =
                $this->dof->get_string('error_pbcgl_form_sourceselect_programmbc_not_found', 'journal');
        } else
        {
            if ( $data['sourceselect_programmbcid']['id'] != 0 )
            {// Подписка найдена
                $access = $this->dof->storage('programmsbcs')->is_access(
                    'use',
                    (int)$data['sourceselect_programmbcid']['id'],
                    NULL,
                    $this->addvars['departmentid']
                );
                if ( empty($access) )
                {// Нет доступа к группе
                    $errors['sourceselect_programmbcid'] =
                        $this->dof->get_string('error_pbcgl_form_sourceselect_programmbc_access', 'journal');
                }
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
    public function definition_after_data()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        // Заполнение занчения поля выбора группы
        if ( isset($this->addvars['agroupid']) )
        {// Указана группа
            $agroup = $this->dof->storage('agroups')->get($this->addvars['agroupid']);
            if ( empty($agroup) )
            {// Идентификатор группы указан неверно
                $mform->setDefault('sourceselect_agroupid[id]', 0);
                $mform->setDefault('sourceselect_agroupid[id_autocomplete]', 0);
            } else
            {// Группа найдена
                $access = $this->dof->storage('agroups')->is_access(
                    'use',
                    (int)$this->addvars['agroupid'],
                    NULL,
                    $this->addvars['departmentid']
                );
                if ( empty($access) )
                {// Нет доступа к группе
                    $mform->setDefault('sourceselect_agroupid[id]', 0);
                    $mform->setDefault('sourceselect_agroupid[id_autocomplete]', 0);
                } else 
                {// Есть доступ к группе
                    $mform->setDefault('sourceselect_agroupid[id]', $this->addvars['agroupid']);
                    $mform->setDefault('sourceselect_agroupid[id_autocomplete]', $this->addvars['agroupid']);
                    $mform->setDefault(
                        'sourceselect_agroupid[sourceselect_agroupid]', $agroup->name. ' ['.$agroup->code. ']' );
                }
            }
        }
        
        // Заполнение занчения поля выбора подписки на программу
        if ( isset($this->addvars['personbcid']) )
        {// Указана программа
            $programmbc = $this->dof->storage('programmsbcs')->get($this->addvars['personbcid']);
            if ( empty($programmbc) )
            {// Идентификатор подписки на программу указан неверно
                $mform->setDefault('sourceselect_programmbcid[id]', 0);
                $mform->setDefault('sourceselect_programmbcid[id_autocomplete]', 0);
            } else
            {// Подписка найдена
                $access = $this->dof->storage('programmsbcs')->is_access(
                    'use',
                    (int)$this->addvars['personbcid'],
                    NULL,
                    $this->addvars['departmentid']
                );
                if ( empty($access) )
                {// Нет доступа к подписке
                    $mform->setDefault('sourceselect_programmbcid[id]', 0);
                    $mform->setDefault('sourceselect_programmbcid[id_autocomplete]', 0);
                } else
                {// Есть доступ к подписке
                    $mform->setDefault('sourceselect_programmbcid[id]', $this->addvars['personbcid']);
                    $mform->setDefault('sourceselect_programmbcid[id_autocomplete]', $this->addvars['personbcid']);
                    $personname = ' - ';
                    $contract = $this->dof->storage('contracts')->get($programmbc->contractid);
                    if ( ! empty($contract) )
                    {
                        $personname = $this->dof->storage('persons')->get_fullname($contract->studentid);
                    }
                    // Программа
                    $programmname = ' - ';
                    $programm = $this->dof->storage('programms')->get($programmbc->programmid);
                    if ( ! empty($programm) )
                    {
                        $programmname = $programm->name;
                    }
                    $mform->setDefault(
                        'sourceselect_programmbcid[sourceselect_programmbcid]', $personname.' '.$programmname.' ['.$programmbc->id.']' );
                }
            }
        }
        
        // Заполнение значения отображения
        if ( isset($this->addvars['viewtype']) )
        {
            // Получение типа отображения
            $viewtype = (string)$this->addvars['viewtype'];
            if ( isset($viewtype{0}) )
            {// Группировка по подпискам
                $mform->setDefault('sourceselect_grouping_cpassed', (int)$viewtype{0});
            }
            if ( isset($viewtype{1}) )
            {// Группировка по времени
                $mform->setDefault('sourceselect_grouping_time', (int)$viewtype{1});
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
        // Cоздаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        
        if ( $this->is_cancelled() )
        {// Форма отменена
            unset($this->addvars['timestart']);
            unset($this->addvars['timeend']);
            unset($this->addvars['agroupid']);
            unset($this->addvars['personbcid']);
            unset($this->addvars['viewtype']);
            // Формирование URL
            $url = $this->dof->url_im(
                'journal',
                '/personsbc_gradeslist/index.php',
                $this->addvars
            );
            redirect($url);
        }
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Формирование данных для редиректа
            if ( isset($formdata->sourceselect_timeinterval['date_from']) )
            {// Начальный интервал указан
                $this->addvars['timestart'] = (int)$formdata->sourceselect_timeinterval['date_from'];
            }
            if ( isset($formdata->sourceselect_timeinterval['date_to']) )
            {// Кончный интервал указан
                $this->addvars['timeend'] = (int)$formdata->sourceselect_timeinterval['date_to'];
            }
            if ( isset($formdata->sourceselect_programmbcid['id']) && $formdata->sourceselect_programmbcid['id'] > 0 )
            {// Указан идентификатор подписки
                $this->addvars['personbcid'] = (int)$formdata->sourceselect_programmbcid['id'];
            }
            if ( isset($formdata->sourceselect_agroupid['id']) && $formdata->sourceselect_agroupid['id'] > 0 )
            {// Указан идентификатор подписки
                 $this->addvars['agroupid'] = $formdata->sourceselect_agroupid['id'];
            }
            
            // Формироваине типа отображения
            $viewtype = '00';
            if ( isset($formdata->sourceselect_grouping_cpassed) )
            {
                $viewtype{0} = (string)$formdata->sourceselect_grouping_cpassed;
            }
            if ( isset($formdata->sourceselect_grouping_cpassed) )
            {
                $viewtype{1} = (string)$formdata->sourceselect_grouping_time;
            }
            $this->addvars['viewtype'] = $viewtype;
            
            // Формирование URL
            if ( isset($formdata->export_csv) )
            {// Запрос на экспорт данных
                $url = $this->dof->url_im(
                    'journal',
                    '/personsbc_gradeslist/export.php',
                    $this->addvars
                );
            } else
            {// Отображение таблицы
                $url = $this->dof->url_im(
                    'journal',
                    '/personsbc_gradeslist/index.php',
                    $this->addvars
                );
            }
            
            redirect($url);
        }
    }
}
?>