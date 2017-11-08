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
 * API работы с модулями курса
 *
 * @package    modlib
 * @subpackage ama
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение дополнительных библиотек
require_once('class.ama_course_section.php');
require_once('class.ama_course_instance_simplecertificate.php');
require_once('class.ama_course_instance_assignment.php');
require_once('class.ama_course_instance_resource.php');

class ama_course_instance
{

    /**
     * @var int - ID курса
     */
    protected $courseid; 
    
    /**
     * @var int - ID модуля
     */
    protected $cmid;
    
    /**
     * @var array - Дополнительные опции
     */
    protected $options;
    
    /**
     * @var object Объект экземпляра
     */
    protected $cm = NULL;

    /**
     * Конструктор класса
     * 
     * @param int|bool $courseid - ID курса
     * @param int|bool $cmid - ID модуля курса
     * @param array $options - Дополнительные опции
     */
    public function __construct($courseid = false, $cmid = false, $options = [])
    {
        $this->courseid = $courseid;
        $this->cmid = $cmid;
        $this->options = $options;
        
        $cm = NULL;
        if ( ! empty($cmid) )
        {// Получение модуля курса
            $cm = get_coursemodule_from_id( '', $cmid);
            if ( empty($cm) )
            {
                $cm = NULL;
            }
        }
        $this->cm = $cm;
    }

    /**
     * Установить экземпляр модуля
     *
     * @param $cm - Экземпляр модуля
     */
    public function set_cm($cm)
    {
        $this->cm = $cm;
        if ( isset($cm->id) )
        {
            $this->id = $cm->id;
        } else
        {
            $this->id = false;
        }
    }
    
    /**
     * Вернуть экземпляр модуля
     */
    public function get_cm()
    {
        return $this->cm;
    }
    
    /** Удаляет экземпляр модуля из системы 
     * @access public
     * @return bool true - удаление прошло успешно
     * false - в иных случаях
     */
    public function delete()
    {
        $returnvalue = (bool) false;

        return (bool) $returnvalue;
    }

    /** Сохраняет экземпляр модуля в БД
     * @access public
     * @param string $name - название экземпляра модуля
     * @param array $options - информация, наполняющая экземпляр модуля
     * @return int id модуля в БД или false
     */
    public function save($name, $options = NULL)
    {
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /** Возвращает информацию "по умолчанию" для наполнения модуля
     * @access public
     * @param array $options - если параметры, заменяющие значения по умолчанию
     * @return array информация, наполняющая экземпляр модуля
     */
    public function template($obj = null)
    {
        $returnvalue = array();

        return (array) $returnvalue;
    }

    /**
     * Получить объект менеджера по ID экземпляра модуля
     *
     * @param array $options - Дополнительные опции
     */
    public function get_manager($options = [])
    {
        if ( isset($this->cm->modname) )
        {
            $class = 'ama_course_instance_'.$this->cm->modname;
            if ( ! class_exists($class) )
            {// Класс работы с модулем не найден
                return NULL;
            }
            
            $modulemanager = new $class($this->cm);
        } else
        {
            $modulemanager = NULL;
        }
    
        return $modulemanager;
    }
    
    /**
     * Возвращает объект оценки по модулю
     * 
     * @return ama_grade
     */
    public function grades()
    {
        require_once(dirname(realpath(__FILE__)).'/class.ama_grade.php');
        
        // Возвращаем экземпляр класса
        return new ama_grade($this->cm->course, $this->cm);
    }
}

?>