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
 * Класс шаблонизатора csv для отчета по студентам
 *
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
global $DOF;
require_once($DOF->plugin_path('modlib','templater','/formats/csv/init.php'));

class dof_im_partners_format_csv extends dof_modlib_templater_format_csv
{   
    /**
     * Возвращает объект, содержащий заголовок таблицы.
     * Возвращает объект, свойства и значения которого - это  
     * имена полей первой записи.
     * @param array $data - массив объектов данных
     * @return object - объект имен полей
     */
    protected function get_title($data)
    {
        // Формируем первый элемент
        $header = new stdClass();
        $header->num =         $this->data->table_report_admins_num;
        $header->managerfio =         $this->data->table_report_admins_fio;
        $header->departmentname =          $this->data->table_report_admins_lo;
        $header->departmenttype =     $this->data->table_report_admins_lo_type;
        $header->departmentregion = $this->data->table_report_admins_lo_district;
        $header->directorfio =       $this->data->table_report_admins_lo_director_fio;
        $header->departmenttelephone =      $this->data->table_report_admins_lo_telephone;
        $header->departmentemail =       $this->data->table_report_admins_lo_email;
        $header->managerposition =      $this->data->table_report_admins_director_position;
        $header->managerdateofbirth = $this->data->table_report_admins_director_birth;
        $header->managergender =      $this->data->table_report_admins_director_gender;
        $header->manageremail =   $this->data->table_report_admins_director_email;
        $header->managerphonecell =   $this->data->table_report_admins_director_mobile;
        $header->timecreate =      $this->data->table_report_admins_registration_date;
        $header->studentsnumplan =       $this->data->table_report_admins_student_count_plan;
        $header->studentsnumfact =      $this->data->table_report_admins_student_count_fact;
        $header->teachersnumplan = $this->data->table_report_admins_teacher_count_plan;
        $header->teachersnumfact =      $this->data->table_report_admins_teacher_count_fact;
        $header->adminsnumplan =   $this->data->table_report_admins_student_admins_plan;
        $header->adminsnumfact =   $this->data->table_report_admins_student_admins_fact;
        
        return $header;
    }
}
?>