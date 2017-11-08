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
 * Библиотека работы с плагинами проверки на заимствования. Базовый класс работы с плагином плагиаризма.
 *
 * @package    modlib
 * @subpackage plagiarism
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотеки работы с плагинами плагиаризма
global $CFG;
require_once($CFG->libdir . '/plagiarismlib.php');

class dof_modlib_plagiarism_plagiarism
{
    /**
     * Объект деканата для доступа к общим методам
     * 
     * @var dof_control
     */
    protected $dof;
    
    /**
     * Код плагина плагиаризма
     *
     * @var string
     */
    protected $plugincode = false;

    /**
     * Проверка доступности плагина
     *
     * @param dof_control $dof - Объект ядра Деканата
     */
    static function is_enabled()
    {
        return false;
    }
    
    /** 
     * Конструктор
     * 
     * @param dof_control $dof - Объект ядра Деканата
     */
    public function __construct( dof_control $dof )
    {
        // Установка свойств объекта
        $this->dof = $dof;
        $this->plugincode = null;
    }
    
    /**
     * Получить код плагина
     * 
     * @return string - Код плагина
     */
    public function get_code()
    {
        return (string)$this->plugincode;
    }
    
    /**
     * Получить локализованное название плагина
     * 
     * @return string - Имя плагина
     */
    public function get_name()
    {
        return '';
    }
    
    /**
     * Добавление файла в очередь на загрузку в систему
     *
     * @param string $pathnamehash - Хэш пути файла
     * @param string $options - Дополнительные опции обработки
     * 
     * @return bool - Результат добавления файла
     */
    public function add_file($pathhash, $options = [])
    {
        return true;
    }

    /**
     * Удаление файла из очереди 
     *
     * @param string $pathnamehash - Хэш пути файла
     * @param string $options - Дополнительные опции обработки
     * 
     * @return bool - Результат удаления файла
     */
    public function delete_file($pathhash, $options = [])
    {
        return true;
    }
    
    /**
     * Получение информации о проверке файла
     *
     * @param string $pathnamehash - Хэш пути файла
     * @param string $options - Дополнительные опции обработки
     * 
     * @return stdClass
     */
    public function get_file_info($pathhash, $options = [])
    {
        return true;
    }
}
?>