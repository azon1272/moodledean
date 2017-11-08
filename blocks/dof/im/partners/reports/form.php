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
 * Классы форм отчетов
 *
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');

// Подключаем библиотеку форм
$DOF->modlib('widgets')->webform();

// Класс заказа нового отчета 
class dof_im_partners_add_report_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * ID подразделения
     */
    protected $depid;
    
    /**
     * Тип отчета
     */
    protected $type;
    
    public function definition()
    {
        // Cоздаем ссылку на HTML_QuickForm
        $mform = & $this->_form;
        
        // Добавляем свойства
        $this->dof = $this->_customdata->dof;
        $this->depid = $this->_customdata->depid;
        $this->type = $this->_customdata->type;
        $this->addvars = $this->_customdata->addvars;
        
        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'form_partners_add_report_depid', $this->depid);
        $mform->setType('form_partners_add_report_depid', PARAM_INT);
        $mform->addElement('hidden', 'form_partners_add_report_type', $this->type);
        $mform->setType('form_partners_add_report_type', PARAM_TEXT);
        
        // Заголовок формы
        $mform->addElement(
                'header',
                'form_partners_add_report',
                $this->dof->get_string('form_partners_add_report_title', 'partners')
        );
        
        // Получение временной зоны персоны
        $timezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        // Опции для выбора даты
        $options = [];
        $options['startyear'] = dof_userdate(time(), '%Y', $timezone);
        $options['stopyear']  = (int)$options['startyear'] + 10;
        $options['optional']  = false;
        $options['timezone']  = $timezone;
        $options['onlytimestamp'] = true;
        
        // Дата формирования отчета
        $mform->addElement(
                'dof_date_selector', 
                'form_partners_add_report_crondate', 
                $this->dof->get_string('form_partners_add_report_crondate', 'partners'),
                $options
        );
        $mform->setType('form_partners_add_report_crondate', PARAM_INT);

        // Кнопки подтверждения
        $mform->addElement(
                'submit', 
                'form_partners_add_report_submit', 
                $this->dof->get_string('form_partners_add_report_submit', 'partners')
        );
        
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    /**
     * Заполнение формы данными
     */
    function definition_after_data()
    {
        $mform = $this->_form;

        // Получение временной зоны персоны
        $timezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        
        // Время формирования отчета
        $mform->setDefault('form_partners_add_report_crondate', time());
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
    
        // Убираем лишние пробелы со всех полей формы
        $mform->applyFilter('__ALL__', 'trim');
    
        // Возвращаем ошибки, если они есть
        return $errors;
    }
    
    /**
     * Обработать пришедшие из формы данные
     *
     * @return bool
     */
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {// Форма подтверждена
            
            if ( $formdata->form_partners_add_report_depid != $this->depid )
            {// Попытка заказать отчет не в текущем подразделении
                $this->errors[] = $this->dof->get_string('error_form_partners_add_report_wrong_depid', 'partners');
            }
            if ( ! empty($this->errors) )
            {// Имеются ошибки
                return false;   
            }
            
            // Формируем объект отчета
            $report = $this->dof->storage('reports')->report('im', 'partners', $this->type);
            if ( empty($report) )
            {// Объект не получен
                return false;   
            }
            
            // Формируем данные отчета
            $reportdata = new stdClass();
            $reportdata->data = new stdClass();
            $reportdata->crondate = $formdata->form_partners_add_report_crondate;
            $reportdata->personid = $this->dof->storage('persons')->get_by_moodleid_id();
            $reportdata->departmentid = $formdata->form_partners_add_report_depid;
            $reportdata->type = $formdata->form_partners_add_report_type;
            $reportdata->objectid = $formdata->form_partners_add_report_depid;
            $report->save($reportdata);
            
            return true;
        }
    }
}

?>