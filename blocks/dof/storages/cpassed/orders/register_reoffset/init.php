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
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
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

// Подключение библиотеки
global $DOF;
require_once($DOF->plugin_path('storage','orders','/baseorder.php'));

/**
 * Класс для создания приказов "Ведомость перезачета оценок".
 */
class dof_storage_cpassed_order_register_reoffset extends dof_storage_orders_baseorder
{
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'cpassed';
    }
    
    public function code()
    {
        return 'register_reoffset';
    }
    /** Исполнить действия, сопутствующие исполнению приказа
     * Добавляет оценки в режиме "перезачета"; в случае если была активная подписка, она закрывается
     * 
     * @param object $order - объект из таблицы orders. Содержит следующие данные:
     *      ->data->grades == array( $pitemid => $grade, ... ) - оценки по перезачитываемым предметам
     *      ->data->personid - id из таблицы persons
     *      ->data->programmsbcid - id из таблицы programmsbcs
     * @return bool
     */
    protected function execute_actions($order)
    {
        if ( ! isset($order->data) OR ! $order->data )
        {// Данных вообще нет
            return false;
        }
        if ( ! isset($order->data->grades) OR empty($order->data->grades) )
        {// Не получили оценки из приказа
            return false;
        }
        
        $ok = true; // Всё хорошо
        foreach ( $order->data->grades as $pitemid => $grade )
        {
            if ( $grade == 'academicdebt' )
            { // Вставим академическую разницу
                $ok = $ok && $this->dof->storage('cpassed')->insert_academic_debt($order->data->programmsbcid,
                        $pitemid, $order->data->personid, $order->id, $order->ownerid, $order->date);
            } else
            { // Вставим перезачёт
                $ok = $ok && $this->dof->storage('cpassed')->insert_grade_register_reoffset($order->data->programmsbcid,
                                     $pitemid, $order->data->personid, $grade, $order->id, $order->ownerid, $order->date); // Приказ не исполнился верно
            }
        }
        return $ok;
    }
}
?>