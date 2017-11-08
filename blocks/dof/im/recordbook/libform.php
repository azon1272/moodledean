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
 * Интерфейс зачетной книжки студента. Классы форм.
 *
 * @package    im
 * @subpackage recordbook
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение базовых функций плагина
require_once('lib.php');
// Подключение библиотеки форм
$DOF->modlib('widgets')->webform();

/**
 * Класс формы лоя выбора недели в дневнике
 */
class dof_im_recordbook_weekselect_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * @var $addvars - GET параметры для ссылки
     */
    protected $addvars = [];
    
    /**
     * @var $addvars - Текущая выбранная дата
     */
    protected $date;

    function definition()
    {
        // Создание ссылки на HTML_QuickForm
        $mform =& $this->_form;
        
        // Добавление свойств
        $this->dof = $this->_customdata->dof;
        $this->addvars = $this->_customdata->addvars;
        if ( ! empty($this->_customdata->date) && $this->_customdata->date > 0 )
        {// Дата определена
            $this->date = $this->_customdata->date;
        } else
        {// Дата не указана
            $this->date = time();
        }

        // Скрытые поля
        $mform->addElement('hidden', 'sesskey', sesskey());
        $mform->setType('sesskey', PARAM_ALPHANUM);
        $mform->addElement('hidden', 'departmentid', $this->addvars['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        
        // Заголовок формы
        $mform->addElement(
            'header',
            'form_header',
            $this->dof->get_string('rb_form_weekselect_header', 'recordbook')
        );
        
        // Поле для вывода сообщений об ошибках скрытых элементов
        $mform->addElement(
            'static',
            'hidden',
            ''
        );

        // Календарь с выбором учебной недели
        $options = [];
        $usertimezone = $this->dof->storage('persons')->get_usertimezone_as_number();
        $options['timezone'] = $usertimezone;
        $options['calendartype'] = 'one_calendar';
        
        // Текущая выбранная дата
        $options['date_from'] = $this->date;
        // Получить даты, на которые найдены события по подписке, для подсветки этих дней
        $options['hightlight_dates'] = $this->get_event_dates();
        
        $mform->addElement(
            'dof_calendar',
            'selected_date',
            $this->dof->get_string('rb_form_weekselect_select_week', 'recordbook'),
            $options
        );

        // Кнопки действий
        $group = [];
        $group[] = $mform->createElement(
            'submit',
            'select_week',
            $this->dof->get_string('rb_form_weekselect_select', 'recordbook')
        );
        $mform->addGroup($group, 'buttons', '', '', false);
       
        // Применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
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
        
        if ( $this->is_submitted() && confirm_sesskey() && 
             $this->is_validated() && $formdata = $this->get_data()
           )
        {// Формирование данных для редиректа
            
            if ( isset($formdata->selected_date['date_from']) )
            {// Добавление даты
                $this->addvars['date'] = $formdata->selected_date['date_from'];
            }
            
            // Формирование URL
            $url = $this->dof->url_im('recordbook', '/recordbook.php', $this->addvars);
            redirect($url);
        }
    }
    
    /**
     * Сформировать массив меток времени для выделения в календаре
     *
     * @return array - Массив меток TIMESTAMP
     */
    private function get_event_dates()
    {
        // Получение текущей подписки 
        $programmbc = $this->dof->storage('programmsbcs')->get($this->addvars['programmsbcid']);
        if ( empty($programmbc) ) 
        {// Подписка не найдена
            return [];
        } 
        
        // Получение подписок на учебные процессы
        $params = [];
        $params['status'] = array_keys($this->dof->workflow('cpassed')->get_meta_list('real'));
        $params['programmsbcid'] = $programmbc->id;
        $cpasseds = $this->dof->storage('cpassed')->get_records($params);
        if ( empty($cpasseds) )
        {// Подписки на учебные процессы не найдены
            return [];
        }
        // Получение событий по подпискам на предмето-классы
        $events = [];
        foreach ( $cpasseds as $cpassed )
        {
            $options = [];
            $options['status'] = ['plan' => 'plan', 'completed' => 'completed'];
            $cp_events = $this->dof->storage('schevents')->get_events_by_cpassed($cpassed, $options);
            // Добавление событий
            $events = $events + $cp_events;
        }
        
        $dates = [];
        foreach ( $events as $event )
        {
            $dates[$event->date] = (int)$event->date;
        }   
        return $dates;
    }
}
?>