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
 * Абстрактный класс, задающий типовой интерфейс для некоторых классов ama
 */
abstract class ama_base
{

    /**
     * id объекта
     */
    protected $id = false;    //id курса

    /** Конструктор
     * @param int $id - id объекта, с которым предстоит работать 
     * если id=NULL - создание нового объекта
     * если id === false - только операции без id
     * @access public
     */

    public function __construct($id = NULL)
    {
        global $DOF;
        require_once($DOF->plugin_path('modlib', 'ama', '/amalib/utils.php'));
        $this->set_id($id);
        if ( !is_integer($this->get_id()) AND $this->get_id() !== false )
        {
            dof_debugging("Error with setting object id", DEBUG_DEVELOPER);
        }
    }

    /**
     * Возвращает true, если сопаставлен реальному объекту в БД
     * @return bool
     * @access public
     */
    public function is_real()
    {
        if ( is_integer($this->get_id()) )
        {
            return true;
        }
        return false;
    }

    /**
     * Выдает ошибку, если не сопоставлен реальному объекту в БД
     * @access protected
     */
    protected function require_real()
    {
        if ( !$this->is_real() )
        {
            dof_debugging("Object must be real for this operation", DEBUG_DEVELOPER);
        }
    }

    /**
     * Возвращает id курса
     * @return int id курса
     * @access public
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Устанавливает id объекта, проверяя его правильность
     * @return mixed id объекта
     * @access public
     */
    protected function set_id($id)
    {
        $this->id = null;
        if ( is_null($id) )
        {
            if ( $id = $this->create() )
            {
                //устанавливаем текущий id
                $this->id = intval($id);
            } else
            {
                dof_debugging("Error object create: db", DEBUG_DEVELOPER);
            }
        } elseif ( ama_utils_is_intstring($id) )
        {
            if ( $this->is_exists($id) )
            {
                $this->id = (integer) $id;
            } else
            {
                dof_debugging("Error object opened: object not exists", DEBUG_DEVELOPER);
            }
        } elseif ( false === $id )
        {
            // Без id
            $this->id = false;
        } else
        {
            dof_debugging("Object id must be integer, NULL or FALSE! Not", DEBUG_DEVELOPER);
        }
        return $this->id;
    }

    /** Проверяет существование объекта
     * Проверяет существование в таблице записи с указанным id 
     * и возвращает true или false
     * @return bool
     */
    abstract function is_exists($id = null);

    /** Создает объект и возвращает его id
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию 
     * @return mixed
     */
    abstract function create($obj = null);

    /** Возвращает шаблон нового объекта
     * @param mixed $obj - параметры объекта или null для параметров по умолчанию 
     * @return object
     */
    abstract function template($obj = null);

    /** Возвращает информацию об объекте из БД
     * @access public
     * @return object объект типа параметр=>значение
     */
    abstract function get();

    /** Обновляет информацию об объекте в БД
     * @access public
     * @param object $obj - объект с информацией 
     * @param bool $replace - false - надо обновить запись курса
     * true - записать новую информацию в курс
     * @return mixed id объекта или false
     */
    abstract function update($obj, $replace = false);

    /** Удаляет объект из БД
     * @access public
     * @return bool true - удаление прошло успешно 
     * false в противном случае
     */
    abstract function delete();
}

?>