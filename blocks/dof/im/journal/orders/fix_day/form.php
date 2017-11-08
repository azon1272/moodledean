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



// Подключаем библиотеки
require_once('lib.php');

// содключаем библиотеку форм
$DOF->modlib('widgets')->webform();

class dof_im_journal_order_fix_day_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {    
        // Делаем глобальные переменные видимыми
        $this->dof    = $this->_customdata->dof;
        $departmentid = $this->_customdata->departmentid;
        
        // Создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', $departmentid);
        $mform->setType('departmentid', PARAM_INT);
        
        // Cоздаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('order_fix_day', 'journal'));
        
        // Опции для выбора дат
        $options = array();
        $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+10*365*24*3600,'%Y');
        $options['optional']  = false;
        $options['timezone']  = $this->_customdata->timezone;
        
        // Начальная дата
        $mform->addElement('dof_date_selector', 'begindate', 
            $this->dof->get_string('date_fix_day_from', 'journal').':',$options);
        $mform->setType('begindate', PARAM_INT);
        
        // Конечная дата
        $options['hours'] = 23;
        $options['minutes']  = 55;
        $mform->addElement('dof_date_selector', 'enddate', 
            $this->dof->get_string('date_fix_day_to', 'journal').':',$options);
        $mform->setType('enddate', PARAM_INT);
        
        // Дата выполнения
        $mform->addElement('date_time_selector', 'crondate', 
            $this->dof->get_string('date_execute_order','journal').':',$options);
        
        // Отчет
        $mform->addElement('select', 'reportid', $this->dof->get_string('age','cstreams').':', $this->get_list_reports());
        $mform->setType('reportid', PARAM_INT);
           
        $mform->addElement('submit', 'buttonview', $this->dof->get_string('button_order','journal'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    
    /**
     * Изменяем поля формы после заполнения данными по-умолчанию
     */
    function definition_after_data()
    {
        $mform = $this->_form;
        // Получаем текущее время
        $date = time();
        // Получаем данные о дате - день,месяц,год 
        $dateday = dof_usergetdate($date, $this->_customdata->timezone);
        // Получаем настройку для дня окончания зарплатного периода(по-умолчанию - 25)
        $endday = $this->dof->storage('config')->get_config_value(
                'enddate',
                'im', 
                'journal', 
                 optional_param('departmentid', 0, PARAM_INT)
        );
        // Меняем тип
        $endday = intval($endday);
        
        // Начало периода 00:00, первое число
        $begintime = dof_make_timestamp(
                $dateday['year'], 
                $dateday['mon'], 
                1, 
                0, 
                0, 
                0, 
                $this->_customdata->timezone 
            );
        
        // Окончание месяца - исходя из настройки
        $endtime = dof_make_timestamp(
                $dateday['year'], 
                $dateday['mon'], 
                $endday, 
                23, 
                55, 
                0, 
                $this->_customdata->timezone 
            );
        
        // Проверка на смещение месяца при несуществующей дате ( 31 февраля = 3 марта )
        $enddateday = dof_usergetdate($endtime);
        if ( $dateday['mon'] != $enddateday['mon'] )
        {// Месяц перескочил - установим последний день
            $endtime = dof_make_timestamp(
                $dateday['year'], 
                $dateday['mon'] + 1, 
                0, 
                23, 
                55, 
                0, 
                $this->_customdata->timezone 
            );
        }
        // Устанавливаем умолчания
        $mform->setDefault('begindate', $begintime);
        $mform->setDefault('enddate', $endtime);
    
    }
    
    /** 
     * Проверка данных формы на валидность
     * 
     * Проверяет на следующие условия:
     *  - Приказ должен находиться в рамках одного месяца
     *  - Родительский отчет должен быть за предыдущий месяц
     *
     * @param object $data[optional] - массив с данными из формы
     * @param object $files[optional] - массив отправленнных в форму файлов (если они есть)
     * @return array $errors - массив ошибок
     */
    public function validation($data, $files)
    {
        $errors = array();
        // Проверим на нахождение в одном месяце интервала
        
        
        if ( isset($data['begindate']) AND isset($data['enddate']) )
        {// Интервал установлен
            if ( $data['begindate']['timestamp'] > $data['enddate']['timestamp'] )
            {// Интервал установлен неверно
                $errors['begindate'] = $this->dof->get_string('form_fixday_interval_error', 'journal');
            }
            
            if ( $data['begindate']['month'] != $data['enddate']['month'] )
            {// Интервал вышел за рамки одного месяца
                $errors['begindate'] = $this->dof->get_string('form_fixday_interval_mon_error', 'journal');
            }
            
            // Получаем настройку для дня окончания зарплатного периода(по-умолчанию - 25)
            $endday = $this->dof->storage('config')->get_config_value(
                    'enddate',
                    'im',
                    'journal',
                    optional_param('departmentid', 0, PARAM_INT)
            );
            // Меняем тип
            $endday = intval($endday);
            if ( $data['enddate']['day'] > $endday )
            {
                $errors['enddate'] = $this->dof->get_string('form_fixday_enddate_after_limit', 'journal');
            }
        } else 
        {// Какая-то из дат установлено неверно
            $errors['begindate'] = $this->dof->get_string('form_fixday_interval_error', 'journal');
        }
        
        
        if ( ! empty($data['reportid']) )
        {
            // Получим предыдущий отчет
            $report = $this->dof->storage('reports')->get($data['reportid']);
            if ( empty($report) )
            {// Отчет не найден
                $errors['reportid'] = $this->dof->get_string('form_fixday_report_not_found', 'journal');
            }
            // Получаем информацию о дате окончания предыдущего отчета
            $rependday = dof_usergetdate($report->enddate);
            if (
                    $report->enddate > $data['begindate']['timestamp']
            )
            {// Дата предыдущего отчета больше, чем начало текущего
                $errors['reportid'] = $this->dof->get_string('form_fixday_report_date_error', 'journal');
            }
            if ( 
                    $rependday['mon'] == $data['begindate']['month'] AND 
                    $rependday['year'] == $data['begindate']['year'] 
               )
            {// Отчет в том же месяце
                $errors['reportid'] = $this->dof->get_string('form_fixday_report_date_error', 'journal');
            }
        }
        
        return $errors;
    }
    
    /** Возвращает массив периодов 
     * @return array список периодов, массив(id периода=>название)
     */
    private function get_list_reports()
    {
    	$params = new stdClass;
    	$params->departmentid = optional_param('departmentid', null, PARAM_INT);
        $params->plugintype = 'im';
        $params->plugincode = 'journal';
        $params->code       = 'loadteachers';
        $params->status     = 'completed';
        // получаем список доступных учебных периодов
    	$select = $this->dof->storage('reports')->get_select_listing($params);
    	$rez = $this->dof->storage('reports')->get_records_select($select);
        // преобразуем список записей в нужный для select-элемента формат  
    	$rez = $this->dof_get_select_values($rez, array(0=>$this->dof->get_string('out_correction', 'journal')), 'id', array('name'));
        // оставим в списке только те объекты, на использование которых есть право
        $permissions = array(array('plugintype'=>'storage', 'plugincode'=>'reports', 'code'=>'im_journal_loadteachers'));
        $rez = $this->dof_get_acl_filtered_list($rez, $permissions);
        return $rez;
    }
    
    public function process()
    {
        if ( $this->is_submitted() AND confirm_sesskey() AND $formdata = $this->get_data() )
        {
            // Получим пустой приказ на фиксацию дней
            $order = $this->dof->im('journal')->order('fix_day');
            
            // Формируем данные для заполнения приказа
            $orderobj = new stdClass();
            
            if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
    		{// ID персоны, которая заказывает приказ, не найден 
    			return false;
    		}
    		
    		// Данные о персоне, зказавшей приказ
            $orderobj->ownerid = $personid;
            // Дата необходимости исполнения приказа
            $orderobj->crondate = $formdata->crondate;
            // Установим ID подразделения
            if ( isset($formdata->departmentid) AND $formdata->departmentid )
            {// Берем из формы
                $orderobj->departmentid = $formdata->departmentid;
            } else
            {// Берем подразделение персоны
                $orderobj->departmentid = $this->dof->storage('persons')->get_field($personid,'departmentid');
            }
            // Дата создания приказа
            $orderobj->date = time();
            // Формируем дополнительную информацию по приказу
            $orderobj->data = new stdClass();
            // Начальная граница периода блокировки дней
            $orderobj->data->begindate = $formdata->begindate['timestamp'];
            // Конечная граница периода блокировки дней
            $orderobj->data->enddate = $formdata->enddate['timestamp'];
            // ID отчета за предыдущий период
            $orderobj->data->prevorderid = $formdata->reportid;
            
            // Формируем данные для сбора отчета о фактической нагрузке за текущий период
            $reportdata = new stdClass();
            // Начальная граница сбора отчета
            $reportdata->begindate    = $orderobj->data->begindate;
            // Конечная граница сбора отчета
            $reportdata->enddate      = $orderobj->data->enddate;
            // Дата необходимости сбора отчета
            $reportdata->crondate     = $formdata->crondate;
            // Персона, заказавшая отчет
            $reportdata->personid     = $personid;
            // Подразделение, для которого собирается отчет
            $reportdata->departmentid = $formdata->departmentid;
            $reportdata->objectid     = $formdata->departmentid;
            // Дополнительная информация по отчету
            $reportdata->data = new stdClass();
            // Провести прогноз
            $reportdata->data->forecast = 'true';
            // ID отчета о фактической нагрузке для рассчета поправки
            $reportdata->data->reportid = $formdata->reportid;  
            
            // Получим объект отчета
            $report = $this->dof->storage('reports')->report('im','journal', 'loadteachers');
            // Сформируем отчет
            $report->save($reportdata);
            
            // Привяжем заказанный отчет к приказу
            $orderobj->data->reportid = $report->get_id();
            // Сформируем приказ
            $order->save($orderobj);
            // Подпишем приказ
            $order->sign($personid);
        }
    }
}

?>