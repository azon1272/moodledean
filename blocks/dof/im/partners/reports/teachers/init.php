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
 * Отчет по преподавателям подразделений партнерской сети
 * 
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class dof_im_partners_report_teachers extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'partners';
    protected $templatertemplatename = 'teachers';
    
    /** 
     * Код отчета
     */
    public function code()
    {
        return 'teachers';
    }
    
    /**
     * Имя отчета
     */ 
    public function name()
    {
        return $this->dof->get_string('report_teachers_title', 'partners');
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
        $report->data->table_report_teachers_header_num = $this->dof->get_string('table_report_teachers_num', 'partners');
        $report->data->table_report_teachers_header_fio = $this->dof->get_string('table_report_teachers_fio', 'partners');
        $report->data->table_report_teachers_header_lo = $this->dof->get_string('table_report_teachers_lo', 'partners');
        $report->data->table_report_teachers_header_lo_type = $this->dof->get_string('table_report_teachers_lo_type', 'partners');
        $report->data->table_report_teachers_header_lo_district = $this->dof->get_string('table_report_teachers_lo_district', 'partners');
        $report->data->table_report_teachers_header_birth = $this->dof->get_string('table_report_teachers_birth', 'partners');
        $report->data->table_report_teachers_header_gender = $this->dof->get_string('table_report_teachers_gender', 'partners');
        $report->data->table_report_teachers_header_email = $this->dof->get_string('table_report_teachers_email', 'partners');
        $report->data->table_report_teachers_header_mobile = $this->dof->get_string('table_report_teachers_mobile', 'partners');
        $report->data->table_report_teachers_header_sertificate = $this->dof->get_string('table_report_teachers_sertificate', 'partners');
        $report->data->table_report_teachers_header_type = $this->dof->get_string('table_report_teachers_type', 'partners');
        $report->data->table_report_teachers_header_teststart = $this->dof->get_string('table_report_teachers_teststart', 'partners');
        $report->data->table_report_teachers_header_testgrade = $this->dof->get_string('table_report_teachers_testgrade', 'partners');
        
        // Данные отчета
        $reportdata = [];
        
        // Логируем процесс
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        
        
        
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
        {// Подразделения партнерской сети есть
        
            // Кэш регионов
            $addresscache = [];
            
            // Счетчик
            $num = 0;
            
            // Типы подразделений
            $deptypes = $this->dof->im('partners')->get_list_dep_types();
        
            // Формирование данных по всем подразделениям
            foreach ( $departments as $dep )
            {// Обработаем данные по каждому подразделению
            
                // Конфигурация
                $sertificatemoduleid = $this->dof->storage('config')->
                    get_config_value('sertificatemodule_id', 'im', 'partners', $dep->id);
                $grademoduleid = $this->dof->storage('config')->
                    get_config_value('grademodule_id', 'im', 'partners', $dep->id);
                
                if ( ! empty($sertificatemoduleid) )
                {// Класс работы с модулями сертификации
                    $helpersertificate = $this->dof->modlib('ama')->
                        course(false)->instance($sertificatemoduleid)->get_manager();
                }
                
                if ( ! empty($dep->addressid) )
                {// Указан адрес подразделения
                    $address = $this->dof->storage('addresses')->get($dep->addressid);
                    if ( ! isset($addresscache[$address->country]) )
                    {// В кеш не загружена страна подразделения
                        $country = $this->dof->modlib('refbook')->region($address->country);
                        $addresscache[$address->country] = $country[$address->country];
                    }
                }
                
                // Массив персон подразделения
                $deppersons = $this->dof->storage('persons')->get_records(['departmentid' => $dep->id], '', 'id, gender, dateofbirth, email, phonecell');
                if ( empty($deppersons) )
                {// В подразделении нет персон
                    continue;
                }
                $deppersonsids = array_keys($deppersons);
                $deppersonsids = implode(',', $deppersonsids);
                // Получение всех преподавателей и руководителей подразделения
                $select = ' 
                        plugintype = :plugintype AND 
                        plugincode = :plugincode AND 
                        code = :code AND 
                        value IN ("teacher", "manager") AND 
                        objectid IN (' . $deppersonsids . ')';
                $param = [
                                'plugintype' => 'storage',
                                'plugincode' => 'persons',
                                'code' => 'type'
                ];
                $persons = $this->dof->storage('cov')->get_records_select($select, $param, '', 'id, objectid, value');
                
                // Тип подразделения
                $typeid = $this->dof->storage('cov')->get_option('storage', 'departments', $dep->id, 'type');
                if ( isset($deptypes[$typeid]) )
                {
                    $dtype = $deptypes[$typeid];
                } else
                {
                    $dtype = '';
                }
                // Округ
                if ( isset($addresscache[$address->country][$address->region]) )
                {
                    $ddist = $addresscache[$address->country][$address->region];
                } else
                {
                    $ddist = '';
                }
                
                if ( ! empty($grademoduleid) )
                {
                    $grades = $this->dof->modlib('ama')->course(false)->instance($grademoduleid)->grades();
                }
               

                if ( ! empty($persons) )
                {// Персоны в подразделении найдены
                    foreach ( $persons as $person )
                    {
                        // Сбор данных
                        $data = new stdClass();
                        
                        // Номер строки
                        $data->table_report_teachers_num = ++$num;
                        
                        // ФИО администратора
                        $data->table_report_teachers_fio = $this->dof->storage('persons')->get_fullname($person->objectid);
                        
                        // Имя подразделения
                        $data->table_report_teachers_lo = $dep->name;
                        
                        // Тип подразделения
                        $data->table_report_teachers_lo_type = $dtype;
                        
                        // Округ подразделения
                        $data->table_report_teachers_lo_district = $ddist;
                        
                        // Число лет
                        if ( isset($deppersons[$person->objectid]->dateofbirth) )
                        {
                            $datea = new DateTime();
                            $datea->setTimestamp($deppersons[$person->objectid]->dateofbirth);
                            $dateb = new DateTime();
                            $interval = $dateb->diff($datea);
                            $data->table_report_teachers_birth = $interval->format("%Y");
                        } else
                        {
                            $data->table_report_teachers_birth = '';
                        }
                        
                        // Пол
                        if ( isset($deppersons[$person->objectid]->gender) )
                        {
                            $data->table_report_teachers_gender = $this->dof->get_string('form_registrtion_person_gender_'.$deppersons[$person->objectid]->gender, 'partners');
                        } else
                        {
                            $data->table_report_teachers_gender = '';
                        }
                        // Email
                        if ( isset($deppersons[$person->objectid]->email) )
                        {
                            $data->table_report_teachers_email = $deppersons[$person->objectid]->email;
                        } else
                        {
                            $data->table_report_teachers_email = '';
                        }
                        // Мобильный телефон
                        if ( isset($deppersons[$person->objectid]->phonecell) )
                        {
                            $data->table_report_teachers_mobile = $deppersons[$person->objectid]->phonecell;
                        } else
                        {
                            $data->table_report_teachers_mobile = '';
                        }
                        
                        // Сертификат
                        $personobject = $this->dof->storage('persons')->get($person->objectid);
                        
                        if ( ! empty($sertificatemoduleid) )
                        {
                            $data->table_report_teachers_sertificate = 
                                $helpersertificate->get_user_sertificate_link($personobject->mdluser);
                        }
                        
                        // Тип персоны
                        $data->table_report_teachers_type = $this->dof->get_string('form_registrtion_person_type_'.$person->value, 'partners');
                        
                        // Получение оценки
                        if ( ! empty($grademoduleid) )
                        {
                            $grade = $grades->get_grades($personobject->mdluser);
                        } else 
                        {
                            $grade = NULL;
                        }
                        
                        // Начало тестирования
                        if ( isset($grade->items[0]->grades[$personobject->mdluser]->dategraded) )
                        {
                            $timezone = $this->dof->storage('persons')->get_usertimezone_as_number($person->objectid);
                            $time = dof_userdate($grade->items[0]->grades[$personobject->mdluser]->dategraded, "%d-%m-%Y", $timezone);
                            $data->table_report_teachers_teststart = $time;
                        } else
                        {
                            $data->table_report_teachers_teststart = '';
                        }
                        
                        // Оценка
                        if ( isset($grade->items[0]->grades[$personobject->mdluser]->str_grade) )
                        {
                            $data->table_report_teachers_testgrade = $grade->items[0]->grades[$personobject->mdluser]->str_grade;
                        } else 
                        {
                            $data->table_report_teachers_testgrade = '';
                        }

                        // Заполнение строки
                        $reportdata[] = $data;
                    }
                }
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
            $error = $this->dof->get_string('error_report_teachers_not_completed', 'partners');
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
                    $error = $this->dof->get_string('error_report_teachers_not_get_template', 'partners');
                } else
                {
                    if ( ! $table = $templater->get_file('html') )
                    {// Загрузка html таблицы завершилась не успешно
                        $error = $this->dof->get_string('error_report_teachers_not_get_table', 'partners');
                    }
                }
            } else 
            {
                $error = $this->dof->get_string('message_report_teachers_no_data', 'partners');
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