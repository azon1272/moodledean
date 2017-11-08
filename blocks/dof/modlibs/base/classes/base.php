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

/** Класс-родитель для всех базовых классов плагинов
 * В нем содержатся функции, которые обязательно будут присутствовать абсолютно во всех плагинах
 * 
 */
abstract class dof_modlib_base_plugin implements dof_plugin
{
    /** Определить, возможна ли установка плагина в текущий момент
     * Проверяет, установлены ли в системе все необходимые плагины
     * (то есть у них должна быть выполнена функция install, и они должны быть успешно записаны в базу).
     * Вызывается при установке плагина.
     * @access public
     * 
     * @return bool 
     *              true - если плагин можно устанавливать
     *              false - если плагин пока что устанавливать нельзя
     */
    public function is_setup_possible()
    {
        if ( ! method_exists($this, 'is_setup_possible_list') )
        {// нет функции со списком плагинов -  этом случае считаем что все  
            return true;
        }
        
        // получаем все плагины, которые необходимо установить
        $pluginlist = $this->is_setup_possible_list();
        
        if ( empty($pluginlist) )
        {// список необходимых плагинов пуст можем начинать установку
            return true;
        }
        
        if ( ! is_array($pluginlist) )
        {// список плагинов получен, но в неправильном формате
            $this->dof->print_error('Wrong plugin list format in is_setup_possible_list(), class '.get_class($this));
        }
        
        foreach ( $pluginlist as $plugintype => $plugins )
        {
            foreach ( $plugins as $plugincode => $version )
            {
                if ( ! $this->dof->plugin_exists($plugintype, $plugincode) )
                {// в системе не установлен нужный плагин
                    return false;
                }
                if ( $this->dof->$plugintype($plugincode)->version() < $version )
                {// версия требуемого плагина слишком старая
                    return false;
                }
            }
        }
        
        // все нужные плагины есть на диске
        return true;
    }
    
    /** Получить список плагинов, которые уже должны быть установлены в системе,
     * и без которых начать установку невозможно
     * По умолчанию возвращает пустой массив - то есть зависимостей нет
     * НЕ РЕДАКТИРУЙТЕ ЭТУ ФУНКЦИЮ, иначе добавленная зависимость появится во всех плагинах 
     * 
     * @return array массив плагинов, необходимых для установки
     *      Формат: array('plugintype'=>array('plugincode' => YYYYMMDDNN));
     *                                                (NN - число от 00-99)
     */
    public function is_setup_possible_list()
    {
        return array();
    }
}
?>