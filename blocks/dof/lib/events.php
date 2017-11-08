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

/** Класс для хранения результата одного широковещательного запроса
 * @todo дописать режим отладки
 * 
 */
class dof_broadcast_result implements Iterator
{
    /**
     * @var int текущий элемент - ответ на запрос от одного плагина
     */
    private $position = 0;
    /**
     * @var array - массив, содержащий ответы всех плагинов (структурированный)
     *              формат массива:
     *              $result[plugintype][plugincode] = $value
     *              
     *              plugintype - тип ответившего плагина
     *              plugincode - код ответившего плагина
     *              $value - данные, которые плагин послал в ответ на запрос
     */
    private $result = array();
    /**
     * @var array - массив содержащий ответы всех плагинов (как список объектов)
     *              Формат массива:
     *              $result[] = $value;
     *              
     *              $value->plugintype - тип ответившего плагина
     *              $value->plugincode - код ответившего плагина
     *              $value->answer     - данные, которые вернул плагин
     */
    private $rawresult = array();
    /**
     * @var array - служебный массив, в котором хранится список плагинов, ответивших на запрос
     *              Каждый элемент содержит объект со струкурой:
     *              ->plugintype
     *              ->plugincode
     *              ->time
     */
    private $plugins;
    /**
     * @var bool использовать ли режим отладки при ответе на запрос
     */
    private $debug = false;
    /**
     * @var dof_control
     */
    private $dof;
    
    /**
     * @param dof_control $dof
     * @param bool $debug
     * 
     */
    public function __construct($dof, $debug=false)
    {
        $this->position = 0;
        $this->debug    = $debug;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        $object = $this->rawresult[$this->position];
        return $object->result;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->rawresult[$this->position]);
    }
    /** Добавить новый ответ плагина в массив
     * @param string $plugintype
     * @param string $plugincode
     * @param mixed  $answer
     * 
     * @return null
     */
    public function add($plugintype, $plugincode, $answer)
    {
        if ( ! $answer )
        {// Если нет результата то считаем что плагин не ответил на запрос
            return;
        }
        // Дополняем структурированный массив
        $this->result[$plugintype][$plugincode] = $answer;
        // Дополняем массив объектов
        $result = new stdClass;
        $result->plugintype = $plugintype;
        $result->plugincode = $plugincode;
        $result->result     = $answer;
        
        $this->rawresult[]  = $result;
    }
    /** Получить результат широковещательного запроса в неструктурированном виде
     * (как массив объектов)
     * 
     * @return array - массив ответов на широковещательный запрос
     *                 Формат одного объекта из массива:
     *                 ->plugintype - тип ответившего плагина
     *                 ->plugincode - код ответтившего плагина
     *                 ->result - данные, которые ыернул плагин
     *                 Вернет пустой массив если ни один плагин не ответил на запрос
     */
    public function raw()
    {
        return $this->rawresult;
    }
    
    public function strict()
    {
        return $this->result;
    }
}
