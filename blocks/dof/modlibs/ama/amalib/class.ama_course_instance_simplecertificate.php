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
 * API работы с модулями Simplesertificate
 *
 * @package    modlib
 * @subpackage ama
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение дополнительных библиотек
require_once('class.ama_course_instance.php');

class ama_course_instance_simplecertificate extends ama_course_instance
                          
{
    /**
     * @var array - Дополнительные опции
     */
    protected $options;
    
    /**
     * @var array - Объект хелпера (/mod/simplecertificate/locallib.php)
     */
    protected $helper;
    
    /**
     * @var int - Экземпляр модуля
     */
    protected $cm;
    
    /**
     * Конструктор класса
     *
     * @param int|bool $courseid - ID курса
     * @param array $options - Дополнительные опции
     */
    public function __construct($cm, $options = [])
    {
        $this->cm = $cm;
        $this->options = $options;
        $this->helper = NULL;
    }
    
    /**
     * Загрузить хелпер модуля
     *
     * @param int $cm - Экземпляр модуля, если не предан, 
     *      передается экземпляр модуля, сохраненный в объекте класса
     */
    public function get_helper($cm = NULL)
    {
        if ( ! empty($this->helper) )
        {// Хелпер уже загружен
            return $this->helper;
        } else 
        {// Загрузим хелпер
            // Определение глобальных переменных
            global $CFG, $DB;
            // Подключение класса
            require_once ($CFG->dirroot . '/mod/simplecertificate/locallib.php');
            if ( empty($cm) )
            {// Экземпляр не найден
                // Получим экземпляр модуля, записанный в объекте класса
                $cm = $this->cm;
            }
        }
        if ( empty($cm) )
        {// Экземпляр не найден
            return false;
        } else 
        {// Экземпляр модуля курса определен
            $context = context_module::instance($cm->id);
            $simplecertificate = new simplecertificate($context, $cm);
            return $simplecertificate;
        }
    }
    
    /**
     * Получить ссылку на сертификат пользователя
     *
     * @param int $userid - ID пользователя
     *      
     * @param int $cm - Экземпляр модуля, если не предан, 
     *      передается экземпляр модуля, сохраненный в объекте класса
     */
    public function get_user_sertificate_link($userid, $cm = NULL)
    {
         $helper = $this->get_helper($cm);
         
         if ( ! empty($helper) )
         {// Хелпер получен
             $sertificate = $helper->get_issue($userid);
             
             if ( isset($sertificate->pathnamehash) )
             {// Указан хэш
                 try {
                     // Получение хранилища
                     $fs = get_file_storage();
                     if ( ! $fs->file_exists_by_hash($sertificate->pathnamehash))
                     {// Файл не найден
                         throw new Exception();
                     }
                     $file = $fs->get_file_by_hash($sertificate->pathnamehash);
                      
                     $url = new moodle_url('/mod/simplecertificate/wmsendfile.php');
                     $url->param('id', $sertificate->id);
                     $url->param('sk', sesskey());
                      
                     return dof_html_writer::link($url, $sertificate->certificatename);
                 } catch (Exception $e)
                 {
                     return get_string('filenotfound', 'simplecertificate', '');
                 }
             }
         }
         return '';
    }
}

?>