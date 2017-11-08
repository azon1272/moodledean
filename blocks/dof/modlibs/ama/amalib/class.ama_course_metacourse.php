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
//Подключаем класс для работы с курсом
require_once('class.ama_course.php');

/** Класс для работы с метакурсом
 * @access public
 */
class ama_course_metacourse extends ama_base
{

//    protected $mcid = 0;//id курса
    /** конструктор класса
     * @param int $id - id метакурса
     */
    public function __construct($id)
    {
        $this->set_id($id);
    }

    /** Возвращает список курсов, входящих в этот метакурс
     * @access public
     * @return array массив курсов или false
     */
    public function list_courses()
    {
        return get_courses_in_metacourse($this->get_id());
    }

    /** Добавляет курс в метакурс
     * @access public
     * @param int $courseid - id курса, который надо вставить в метакурс
     * @return bool true - курс успешно добавлен, false - в иных случаях 
     */
    public function add_course($courseid)
    {
        return add_to_metacourse($this->get_id(), $courseid);
    }

    /** Удаляет курс из метакурса
     * @access public
     * @param int $courseid - id курса, который надо удалить
     * @return bool true - курс успешно удален, false - в иных случаях
     */
    public function remove_course($courseid)
    {
        return remove_from_metacourse($this->get_id(), $courseid);
    }
    
    /*** ОПРЕДЕЛЯЕМ АБСТРАКТНЫЕ КЛАССЫ *****/
    public function is_exists($id=null)
    {
        return true;
    }

    /** Создает объект и возвращает его id
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию 
     * @return mixed
     */
    public function create($obj = null)
    {
        return true;
    }

    /** Возвращает шаблон нового объекта
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию 
     * @return object
     */
    public function template($obj = null)
    {
        return true;
    }

    /** Возвращает информацию об объекте из БД
     * @access public
     * @return object объект типа параметр=>значение
     */
    public function get()
    {
        return true;
    }

    /** Обновляет информацию об объекте в БД
     * @access public
     * @param object $obj - объект с информацией 
     * @param bool $replace - false - надо обновить запись курса
     * true - записать новую информацию в курс
     * @return mixed id объекта или false
     */
    public function update($obj, $replace = false)
    {
        return true;
    }

    /** Удаляет объект из БД
     * @access public
     * @return bool true - удаление прошло успешно 
     * false в противном случае
     */
    public function delete()
    {
        return true;
    }

}

?>