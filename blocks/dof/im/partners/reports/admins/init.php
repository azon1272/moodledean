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
 * Отчет по администраторам подразделений партнерской сети
 * 
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class dof_im_partners_report_admins extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'partners';
    protected $templatertemplatename = 'admins';
    
    /** 
     * Код отчета
     */
    public function code()
    {
        return 'admins';
    }
    
    /**
     * Имя отчета
     */ 
    public function name()
    {
        return $this->dof->get_string('report_admins_title', 'partners');
    }
    
    /**
     * Тип плагина
     */
    public function plugintype()
    {
        return 'im';
    }
    
    /**
     * Код плагина
     */
    public function plugincode()
    {
        return 'partners';
    }    
    
    
    /**
     * Дополнительные действия над отчетом перед сохранением данных
     * 
     * @param object $report - Объект данных отчета
     * 
     * @return object - Объект данных отчета
     */
    protected function save_data($report)
    {
        return $report;
    }     

    /** 
     * Формирование данных отчета
     * 
     * @param object $report - Объект данных отчета
     * 
     * @return object - Объект данных отчета
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// Ошибочный тип данных
            return false;
        }

        // Языковые данные
        $report->data->table_report_admins_num = $this->dof->get_string('table_report_admins_num', 'partners');
        $report->data->table_report_admins_fio = $this->dof->get_string('table_report_admins_fio', 'partners');
        $report->data->table_report_admins_about_lo = $this->dof->get_string('table_report_admins_about_lo', 'partners');
        $report->data->table_report_admins_lo = $this->dof->get_string('table_report_admins_lo', 'partners');
        $report->data->table_report_admins_lo_type = $this->dof->get_string('table_report_admins_lo_type', 'partners');
        $report->data->table_report_admins_lo_district = $this->dof->get_string('table_report_admins_lo_district', 'partners');
        $report->data->table_report_admins_lo_director_fio = $this->dof->get_string('table_report_admins_lo_director_fio', 'partners');
        $report->data->table_report_admins_lo_telephone = $this->dof->get_string('table_report_admins_lo_telephone', 'partners');
        $report->data->table_report_admins_lo_email = $this->dof->get_string('table_report_admins_lo_email', 'partners');
        $report->data->table_report_admins_about_director = $this->dof->get_string('table_report_admins_about_director', 'partners');
        $report->data->table_report_admins_director_position = $this->dof->get_string('table_report_admins_director_position', 'partners');
        $report->data->table_report_admins_director_birth = $this->dof->get_string('table_report_admins_director_birth', 'partners');
        $report->data->table_report_admins_director_gender = $this->dof->get_string('table_report_admins_director_gender', 'partners');
        $report->data->table_report_admins_director_email = $this->dof->get_string('table_report_admins_director_email', 'partners');
        $report->data->table_report_admins_director_mobile = $this->dof->get_string('table_report_admins_director_mobile', 'partners');
        $report->data->table_report_admins_registration_date = $this->dof->get_string('table_report_admins_registration_date', 'partners');
        $report->data->table_report_admins_student_count = $this->dof->get_string('table_report_admins_student_count', 'partners');
        $report->data->table_report_admins_student_count_plan = $this->dof->get_string('table_report_admins_student_count_plan', 'partners');
        $report->data->table_report_admins_student_count_fact = $this->dof->get_string('table_report_admins_student_count_fact', 'partners');
        $report->data->table_report_admins_teacher_count = $this->dof->get_string('table_report_admins_teacher_count', 'partners');
        $report->data->table_report_admins_teacher_count_plan = $this->dof->get_string('table_report_admins_teacher_count_plan', 'partners');
        $report->data->table_report_admins_teacher_count_fact = $this->dof->get_string('table_report_admins_teacher_count_fact', 'partners');
        $report->data->table_report_admins_admins_count = $this->dof->get_string('table_report_admins_admins_count', 'partners');
        $report->data->table_report_admins_student_admins_plan = $this->dof->get_string('table_report_admins_student_admins_plan', 'partners');
        $report->data->table_report_admins_student_admins_fact = $this->dof->get_string('table_report_admins_student_admins_fact', 'partners');

        // Данные отчета
        $reportdata = [];
        
        // Логируем процесс
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        
        // Получаем временную зону, относительно которой будут формироваться даты
        $timezone = $this->dof->storage('departments')->get_timezone($report->departmentid);
        
        // Получение всех подразделений 
        $options = [];
        $statuses = $this->dof->workflow('departments')->get_meta_list('real');
        $statuses = array_keys($statuses);
        $options['statuses'] = $statuses;
        $departments = $this->dof->storage('departments')->get_departments($report->departmentid, $options);
        $departments[$report->departmentid] = $this->dof->storage('departments')->get($report->departmentid);
        
        if ( ! empty($departments) )
        {// Подразделени найдены
            
            // Кэш регионов
            $addresscache = [];
            
            // Счетчик
            $num = 0;
            
            // Типы подразделений
            $deptypes = $this->dof->im('partners')->get_list_dep_types();
            
            foreach ( $departments as $dep )
            {// Обработаем данные по каждому подразделению
                
                if ( ! empty($dep->addressid) )
                {// Указан адрес подразделения
                    $address = $this->dof->storage('addresses')->get($dep->addressid);
                    if ( ! isset($addresscache[$address->country]) )
                    {// В кеш не загружена страна подразделения
                        $country = $this->dof->modlib('refbook')->region($address->country);
                        $addresscache[$address->country] = $country[$address->country];
                    }
                }
                // Получение персоны-администратора подразделения
                $person = $this->dof->storage('persons')->get($dep->managerid);
                // Массив персон подразделения
                $persons = $this->dof->storage('persons')->get_records(['departmentid' => $dep->id], '', 'id');
                $personsids = array_keys($persons);
                $personsids = implode(',', $personsids);
                
                // Сбор данных
                $data = new stdClass();
                
                // Номер строки
                $data->num = ++$num;
                // ФИО администратора
                $data->managerfio = $this->dof->storage('persons')->get_fullname($dep->managerid);
                // Имя подразделения
                $data->departmentname = $dep->name;
                // Тип подразделения
                $typeid = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'type');
                if ( isset($deptypes[$typeid]) )
                {
                    $data->departmenttype = $deptypes[$typeid];
                } else 
                {
                    $data->departmenttype = '';
                }
                // Округ
                if ( isset($addresscache[$address->country][$address->region]) )
                {
                    $data->departmentregion = $addresscache[$address->country][$address->region];
                } else
                {
                    $data->departmentregion = '';
                }
                // ФИО директора
                $directorfio = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'directorfio');
                if ( ! empty($directorfio) )
                {
                    $data->directorfio = $directorfio;
                } else
                {
                    $data->directorfio = '';
                }
                // Рабочий телефон школы
                $telephone = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'telephone');
                if ( ! empty($telephone) )
                {
                    $data->departmenttelephone = $telephone;
                } else
                {
                    $data->departmenttelephone = '';
                }
                // E-mail школы
                $email = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'email');
                if ( ! empty($email) )
                {
                    $data->departmentemail = $email;
                } else
                {
                    $data->departmentemail = '';
                }
                
                // Должность
                $data->managerposition = '';
                
                // Число лет
                if ( isset($person->dateofbirth) )
                {
                    $datea = new DateTime();
                    $datea->setTimestamp($person->dateofbirth);
                    $dateb = new DateTime();
                    $interval = $dateb->diff($datea);
                    $data->managerdateofbirth = $interval->format("%Y");
                } else 
                {
                    $data->managerdateofbirth = '';
                }
                
                // Пол
                if ( isset($person->gender) )
                {
                    $data->managergender = $this->dof->get_string('form_registrtion_person_gender_'.$person->gender, 'partners');
                } else
                {
                    $data->managergender = '';
                }
                // Email
                if ( isset($person->email) )
                {
                    $data->manageremail = $person->email;
                } else
                {
                    $data->manageremail = '';
                }
                // Мобильный телефон
                if ( isset($person->phonecell) )
                {
                    $data->managerphonecell = $person->phonecell;
                } else
                {
                    $data->managerphonecell = '';
                }
                // Дата регистрации
                $timecreate = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'timecreate');
                if ( ! empty($timecreate) )
                {
                    $timezone = $this->dof->storage('persons')->get_usertimezone_as_number($person->id);
                    $data->timecreate = dof_userdate($timecreate, "%d-%m-%Y", $timezone);
                } else
                {
                    $data->timecreate = '';
                }
                // Плановое число Учащихся
                $studentsnum = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'studentsnum');
                if ( ! empty($studentsnum) )
                {
                    $data->studentsnumplan = $studentsnum;
                } else
                {
                    $data->studentsnumplan = 0;
                }
                if ( ! empty($personsids) )
                {
                    // Фактическое число Учащихся
                    $select = ' plugintype = :plugintype AND plugincode = :plugincode AND code = :code AND value = :value AND objectid IN (' . $personsids . ')';
                    $param = [
                                    'plugintype' => 'storage',
                                    'plugincode' => 'persons',
                                    'code' => 'type',
                                    'value' => 'student'
                    ];
                    $records = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id');
                } else 
                {
                    $records = [];
                }
                
                $data->studentsnumfact = count($records);
                // Плановое число Преподавателей
                $teachersnum = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'teachersnum');
                if ( ! empty($teachersnum) )
                {
                    $data->teachersnumplan = $teachersnum;
                } else
                {
                    $data->teachersnumplan = 0;
                }
                
                if ( ! empty($personsids) )
                {
                    // Фактическое число Преподавателей
                    $select = ' plugintype = :plugintype AND plugincode = :plugincode AND code = :code AND value = :value AND objectid IN (' . $personsids . ')';
                    $param = [
                        'plugintype' => 'storage',
                        'plugincode' => 'persons',
                        'code' => 'type',
                        'value' => 'teacher'
                    ];
                    $records = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id');
                } else
                {
                    $records = [];
                }
                
                $data->teachersnumfact = count($records);
                // Плановое число Руководителей
                $adminsnum = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'managersnum');
                if ( ! empty($adminsnum) )
                {
                    $data->adminsnumplan = $adminsnum;
                } else
                {
                    $data->adminsnumplan = 0;
                }
                
                if ( ! empty($personsids) )
                {
                    // Фактическое число Руководителей
                    $select = ' plugintype = :plugintype AND plugincode = :plugincode AND code = :code AND value = :value AND objectid IN (' . $personsids . ')';
                    $param = [
                        'plugintype' => 'storage',
                        'plugincode' => 'persons',
                        'code' => 'type',
                        'value' => 'manager'
                    ];
                    $records = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id');
                } else
                {
                    $records = [];
                }
                $data->adminsnumfact = count($records);
                
                // Заполнение строки
                $reportdata[] = $data;
            }
        }
        
        $report->data->persons = $reportdata;
        return $report;
    }
    
    /** 
     * Отобразить отчет в формате HTML
     * 
     * @param array $addvars - Массив GET-параметров 
     */
    public function show_report_html($addvars = NULL)
    {
        /** Базовые переменные **/
        $error = '';
        $table = '';

        if ( ! $this->is_generate($this->load()) )
        {// Отчет еще не собран
            $error = $this->dof->get_string('error_report_admins_not_completed', 'partners');
        }else
        {// Отчет собран - загрузка шаблона
            
            // Получение данных из файла
            $template = $this->load_file();
            
            // Формирование структуры
            if ( isset($template->persons) )
            {
                // Заполнение шаблона данными
                $templater = $this->template(); 
                
                if ( empty($templater) )
                {// Заполнение данными прошло с ошибками
                    $error = $this->dof->get_string('error_report_admins_not_get_template', 'partners');
                } else
                {
                    if ( ! $table = $templater->get_file('html') )
                    {// Загрузка html таблицы завершилась не успешно
                        $error = $this->dof->get_string('error_report_admins_not_get_table', 'partners');
                    }
                }
            } else 
            {
                $error = $this->dof->get_string('message_report_admins_no_data', 'partners');
            }
        }

        if ( ! empty($error) )
        {// Вывод ошибок
            print '<p style=" color:red; text-align:center; "><b>'.$error.'</b></p>';
        } else 
        {// Вывод данных
            print($table);
        }
    } 

    protected function template_data($template)
    {
        return $template;
    }   
}

?>