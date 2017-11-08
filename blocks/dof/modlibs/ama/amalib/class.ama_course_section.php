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
    die('This file was generated for PHP 5'); //если ниже php5, то кончаем работу
}

//Подключаем класс для работы с экзепляром ресурса курса
require_once('class.ama_course_instance.php');

/** Класс для работы с секцией курса
 * @access public
 */
class ama_course_section implements Iterator
{

    //id секции, с которой будем работать
    public $sectionid = 0;

    /** Удалить из секции все ресурсы
     * @access public
     * @return bool true - секция очищена, 
     * false - в иных случаях 
     */
    public function clean()
    {
        $returnvalue = (bool) false;

        return (bool) $returnvalue;
    }

    /** Возвращает название секции в том виде 
     * как она отображается (может содержать HTML-код)
     * @access public
     * @return string строка названия секции 
     * из таблицы _course_sections или 
     * false в иных случаях 
     */
    public function get_summary()
    {
        $returnvalue = (string) '';

        return (string) $returnvalue;
    }

    /** Назначить название секции
     * @access public
     * @param string $summary - название секции так, 
     * как оно будет отображаться (может содержать HTML-код) 
     * @return bool true - сохранили название в БД, 
     * false - в иных случаях
     */
    public function set_summary($summary)
    {
        
    }

    /** Возвращает атрибут видимости секции
     * @access public
     * @return int 1 - видима, 0 - невидима,
     * false - в иных случаях
     */
    public function get_visible()
    {
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /** Устанавливает атрибут видимости секции в определенное значение
     * @access public
     * @param int $visible - 1 секция видима, 0 - невидима
     * @return bool true - удалось записать атрибут 
     * false - в иных случаях
     */
    public function set_visible($visible)
    {
        
    }

    /** Возвращает id курса, в котором эта секция располагается
     * @access public
     * @return int id курса или false
     */
    public function get_courseid()
    {
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /** Возвращает массив id всех экземпляров модулей секции
     * @access public
     * @return array или false
     */
    public function get_inst_ids()
    {
        $returnvalue = array();

        return (array) $returnvalue;
    }

    /** Возвращает количество экземпляров модулей в секции
     * @access public
     * @return int число ресурсов или false
     */
    public function count_inst()
    {
        $returnvalue = (int) 0;

        return (int) $returnvalue;
    }

    /** Возвращает объект класса для работы с 
     * экземпляром модуля по его id
     * @access public
     * @param int $id - id экземпляра модуля
     * @return object экземпляр класса ama_course_instance
     */
    public function get_byid($id)
    {
        $returnvalue = NULL;

        return $returnvalue;
    }

    /** Возвращает экземпляр модуля 
     * по его порядковому номеру среди 
     * всех экземпляров модулей секции
     * @access public
     * @param int $num - порядковый номер экземпляра модуля в секции
     * @return object экземпляр класса ama_course_instance
     */
    public function get_bynum($num)
    {
        $returnvalue = NULL;

        return $returnvalue;
    }

    /** Возвращает объект для работы с экземпляром модуля 
     * @access public
     * @param string $mod - тип модуля
     * @param string $name - имя модуля
     * @param int $num - номер модуля в секции
     * @return экземпляр от ama_course_instance
     */
    public function add_inst($mod, $name, $num = NULL)
    {
        $returnvalue = NULL;

        return $returnvalue;
    }

    /** Регистрирует экземпляр модуля в секции 
     * @access public
     * @param int $instanceid - id экземпляра модуля
     * @param int $moduleid - id модуля
     * @param int $num - порядковый номер экземпляра модуля 
     * среди всех экземпляров модулей секции
     * @return bool true - модуль успешно добавлен в курс
     * false - в иных случаях
     */
    public function register_inst($instanceid, $moduleid, $num = NULL)
    {
        $returnvalue = (bool) false;

        return (bool) $returnvalue;
    }

    /** Удаляет экземпляр модуля из БД
     * @access public
     * @param int $num - порядковый номер экземпляра модуля 
     * среди всех экземпляров модулей секции 
     * @return bool true - модуль успешно удален
     * false - в иных случаях
     */
    public function delete_inst($num)
    {
        $returnvalue = (bool) false;

        return (bool) $returnvalue;
    }

    /** Перемещает экзеипляр модуля внутри секции
     * @access public
     * @param int $numfrom - текущий порядковый номер экземпляра модуля в секции
     * @param int $numto - новый порядковый номер экзеипляра модуля в секции
     * @return bool true - модуль перемещен, false - в иных случаях
     */
    public function move_inst($numfrom, $numto)
    {
        $returnvalue = (bool) false;

        return (bool) $returnvalue;
    }

    /** конструктор класса - создает объект от текущего класса
     * @access public
     * @param int $sectionid - id секции, с которой собираются работать
     * @return void
     */
    public function __construct($sectionid)
    {
        
    }

    /************* МЕТОДЫ ДЛЯ РЕАЛИЗАЦИИ ИТЕРАТОРА *********/
    //Итератор получает массив id элементов курса одной секции и перебирает их
    //каждый раз возвращает экземпляр класса ama_course_instance для работы с секцией

    /** Возвращает указатель итератора на первый элемент курса в секции
     * return void
     */
    public function rewind()
    {
        
    }

    /** Возвращает экземпляр от класса ama_course_instance
     * для работы с секцией
     * @return object - экземпляр от ama_course_instance
     */
    public function current()
    {
        
    }

    /** Возвращает порядковый номер элемента курса в секции
     * return int - номер текущей секции
     */
    public function key()
    {
        
    }

    /** Переводит указатель итератора на следующий, за текущим, 
     * элемент курса в секции 
     * return void
     */
    public function next()
    {
        
    }

    /** Проверяет достижение конца массива элементов курса секции
     * return bool - true если не все элементы курса в секции перебраны  
     * false - в иных случаях   
     */
    public function valid()
    {
        
    }
    /************* КОНЕЦ МЕТОДОВ ДЛЯ РЕАЛИЗАЦИИ ИТЕРАТОРА ********/
}

?>