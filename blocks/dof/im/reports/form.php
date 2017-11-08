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

class dof_im_journal_report_form extends dof_modlib_widgets_form
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    function definition()
    {    
        // делаем глобальные переменные видимыми
        $this->dof    = $this->_customdata->dof;
        $departmentid = $this->_customdata->departmentid;
        $reporttype   = $this->_customdata->type;
        // создаем ссылку на HTML_QuickForm
        $mform =& $this->_form;
        $mform->addElement('hidden','departmentid', $departmentid);
        $mform->setType('departmentid', PARAM_INT);
        $mform->addElement('hidden','type', $reporttype);
        $mform->setType('type', PARAM_TEXT);
        //создаем заголовок формы
        $mform->addElement('header','formtitle', $this->dof->get_string('report_on_interval', 'journal'));
        
        // Опции для выбора дат
        $options = array();
        $options['startyear'] = dof_userdate(time()-10*365*24*3600,'%Y');
        $options['stopyear']  = dof_userdate(time()+10*365*24*3600,'%Y');
        $options['optional']  = false;
        $options['timezone']  = $this->dof->storage('departments')->get_timezone($departmentid);
        $options['onlytimestamp'] = true;
        
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
        
        $mform->addElement('date_time_selector', 'crondate', 
            $this->dof->get_string('crondate','journal').':',$options);
        
        
        switch ( $reporttype )
        {// добавочные поля для разных типов отчетов
            case 'im_journal_loadteachers': 
                $mform->addElement('checkbox', 'forecast', '', $this->dof->get_string('forecast','journal'));
                $mform->setType('forecast', PARAM_BOOL);
                $mform->disabledIf('forecast', 'begindate', 'eq', 'new');
                //
                $mform->addElement('select', 'reportid', $this->dof->get_string('age','cstreams').':', $this->get_list_reports());
                $mform->setType('reportid', PARAM_INT);
            break;
        }    
        $mform->addElement('submit', 'buttonview', $this->dof->get_string('button_order','journal'));
        // применение проверки ко всем элементам
        $mform->applyFilter('__ALL__', 'trim');
    }
    
    
    function definition_after_data()
    {
        $mform = $this->_form;
        // делаем значения по умолчанию
        $date = time();
        // формируем даты начала и конца месяца 
        $dateday = dof_usergetdate($date);
        $begintime = mktime(12,0,0,$dateday['mon'],1,$dateday['year']);
        $endtime = mktime(12,0,0,$dateday['mon'],30,$dateday['year']);
        $mform->setDefault('begindate', $begintime);
        $mform->setDefault('enddate', $endtime);
    }
    
    function validation($data, $files) 
    {
        $errors = array();

        $arrtmp = explode('_', $data['type']);
        $addvars = array('plugintype' => $arrtmp[0], 'plugincode' => $arrtmp[1], 'code' => $arrtmp[2]);
        // попытка получить отчеты по параметрам из формы
        $reports = $this->dof->storage('reports')->get_records(array('departmentid' => $data['departmentid'],
                'plugintype' => $addvars['plugintype'], 
                'code' => $addvars['code'],
                'status' => 'requested',
                'crondate' => $data['crondate'],
                'begindate' => $data['begindate'],
                'enddate' => $data['enddate']
                ));
        switch ( $data['type'] )
        {// добавочные поля для разных типов отчетов
            case 'im_journal_loadteachers': 
                if ( ! empty($data['forecast']) )
                {
                    $begintime = dof_usergetdate($data['begindate']);
                    $endtime = dof_usergetdate($data['enddate']);
                    if ( $begintime['mday'] != 1 OR
                         $begintime['mon'] != $endtime['mon'] OR 
                         $begintime['year'] != $endtime['year'])
                    {
                        $errors['forecast'] = $this->dof->get_string('report_loadteachers_forecast', 'reports');
                    }
                }
            break;
        }       
        if ( !empty($reports) )
        {// ошибка, если отчет с такими парамерами уже лежит в базе
            $errors['begindate'] = $this->dof->get_string('report_already_exists', 'reports');
        }
        
        // возвращаем ошибки, если они возникли
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
    

    
}

?>