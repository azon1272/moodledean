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


//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5'); //если ниже php5, то кончаем работу
}

// Подключаем Утилиты
require_once(dirname(realpath(__FILE__)) . '/utils.php');
//Подключаем класс для работы с курсами
require_once(dirname(realpath(__FILE__)) . '/class.ama_course.php');
//Подключаем класс для работы с пользователями
require_once(dirname(realpath(__FILE__)) . '/class.ama_user.php');

/** 
 * Основной класс, для работы с библиотекой AMA(Alternative Moodle Api)
 */
class ama
{

    /** 
     * Конструктор класса
     *
     * @access public
     */
    public function __construct()
    {
        
    }

    /** 
     * Возвращает объект для работы с курсом
     *
     * @param int $id - id курса либо NULL (пустой курс)
     * @return ama_course объект для работы с курсом
     * @access public
     */
    public function course($id = NULL)
    {
        return new ama_course($id);
    }

    /** Получить объект для работы с курсом
     * 
     * @access public
     * @return ama_user
     * @param  int $id [optional] - id пользователя
     */
    public function user($id = NULL)
    {
        return new ama_user($id);
    }
}
?>