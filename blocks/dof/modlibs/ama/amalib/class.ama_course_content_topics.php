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
//Все в этом файле написано на php5.
//Проверяем совместимость с ПО сервера
if ( 0 > version_compare(PHP_VERSION, '5') )
{
    die('This file was generated for PHP 5');
}
// Iterator
//подключаем библиотеку для работы с форматами курса
require_once('class.ama_course_content.php');

//подключаем библиотеку для работы с секцией курса
require_once('class.ama_course_section.php');

/** Класс для работы с курсом формата структура
 * @access public
 */
class ama_course_content_topics implements ama_course_content, Iterator
{

    /** Возвращает объект для работы с секцией 
     * Секция выбирается по ее порядковому номеру в курсе
     * @access public
     * @param int $num - порядковый номер секции в курсе
     * @return экземпляр от класса ama_course_section
     */
    public function get_section($num)
    {
        $returnvalue = NULL;

        return $returnvalue;
    }

    /** Подсчитывает количество секций в курсе 
     * @access public
     * @return int общее число секций в курсе
     */
    public function count_sections()
    {
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /************* МЕТОДЫ ДЛЯ РЕАЛИЗАЦИИ ИТЕРАТОРА *********/
    //Итератор получает массив секций курса и перебирает их
    //каждый раз возвращает экземпляр класса ama_course_section для работы с секцией

    /** Возвращает указатель итератора на первую секцию курса
     * return void
     */
    public function rewind()
    {
        
    }

    /** Возвращает экземпляр от класса ama_course_section
     * для работы с секцией
     * @return object - экземпляр от ama_course_section
     */
    public function current()
    {
        
    }

    /** Возвращает порядковый номер секции в курсе
     * return int - номер текущей секции
     */
    public function key()
    {
        
    }

    /** Переводит указатель итератора на следующую, за текущей, секцию
     * return void
     */
    public function next()
    {
        
    }

    /** Проверяет - продолжать перебор или нет
     * return bool - true если перебрали не все секции 
     * false - в иных случаях
     */
    public function valid()
    {
        
    }
    /************* КОНЕЦ МЕТОДОВ ДЛЯ РЕАЛИЗАЦИИ ИТЕРАТОРА ********/
}

?>