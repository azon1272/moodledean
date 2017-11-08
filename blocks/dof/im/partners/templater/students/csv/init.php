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
        $header->table_report_students_num =         $this->data->table_report_students_header_num;
        $header->table_report_students_fio =         $this->data->table_report_students_header_fio;
        $header->table_report_students_lo =          $this->data->table_report_students_header_lo;
        $header->table_report_students_lo_type =     $this->data->table_report_students_header_lo_type;
        $header->table_report_students_lo_district = $this->data->table_report_students_header_lo_district;
        $header->table_report_students_birth =       $this->data->table_report_students_header_birth;
        $header->table_report_students_gender =      $this->data->table_report_students_header_gender;
        $header->table_report_students_email =       $this->data->table_report_students_header_email;
        $header->table_report_students_mobile =      $this->data->table_report_students_header_mobile;
        $header->table_report_students_sertificate = $this->data->table_report_students_header_sertificate;
        $header->table_report_students_stream =      $this->data->table_report_students_header_stream;
        $header->table_report_students_teststart =   $this->data->table_report_students_header_teststart;
        $header->table_report_students_testgrade =   $this->data->table_report_students_header_testgrade;

        return $header;
    }

   
    /**
     * Создает из массива данных строку csv-файла 
     * @param array $head - массив строки заголовка
     * названия индексов - названия полей 
     * @param array $obj - массив данных для вставки в строку
     * названия индексов - названия полей, значения - данные
     * @return string - одну строку с данными
     */
    protected function create_data_string($head, $obj)
    {
        //формируем строку результата
        $rez = '';
        foreach ( $head as $key => $value )
        {//перебираем элементы строки заголовка
            if ( array_key_exists($key, $obj) )
            {//одноименное поле есть в строке данных
                if ( $key == 'table_report_students_sertificate' )
                {// Возможна ссылка на сертификат
                    $link = $this->clear_link($obj[$key]);
                    //заносим его значение в строку
                    $rez .= $this->prepare_string($link);
                } else 
                {
                    //заносим его значение в строку
                    $rez .= $this->prepare_string($obj[$key]);
                }
            }
            $rez .= ',';
        }
        //отрезали последнюю запятую
        $rez = substr($rez, 0, -1);
        //переходим на новую строку
        $rez .= "\n";
        return $rez;
    }
    
    /**
     * Получение URL из html-кода
     * 
     * @param string $str - html-код
     * 
     * @return - string - URL или входные данные, если URL не найден
     */
    protected function clear_link($str)
    {
        preg_match('/<a href="(.+)">/', $str, $match);
        if ( isset($match[1]) )
        {// URL найден
            $str = htmlspecialchars_decode($match[1]);
        }
        return $str;
    }
}
?>