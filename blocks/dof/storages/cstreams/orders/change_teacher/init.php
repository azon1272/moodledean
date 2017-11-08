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
 * Класс для создания приказов 
 * о передаче нагрузки преподавателя
 */
class dof_storage_cstreams_order_change_teacher extends dof_storage_orders_baseorder
{
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'cstreams';
    }
    
    public function code()
    {
        return 'change_teacher';
    }
    /**
     * Исполнить действия, сопутствующие исполнению приказа 
     *
     * @param object $order - объект из таблицы orders
     * @return bool
     */
    protected function execute_actions($order)
    {
        if ( ! isset($order->data) OR ! $order->data )
        { // Не получили данные из приказа
            return false;
        }
        $ok = true;
        // В зависимости от направления выполним передачу нагрузки
        switch ( $order->data->direction )
        {
            case 'fromteacher':
                $ok = $this->transfer_from_teacher($order->data);
                break;

            case 'toteacher':
                $ok = $this->transfer_to_teacher($order->data);
                break;

            case 'returnhours':
                $ok = $this->return_to_teacher($order->data);
                break;
            default:
                return false;
        }
        // Сохраняем статус
        if ( !empty($order->data->changestatus) )
        {
            $statuses = array(
                'sickleave' => 'patient',
                'vacation'  => 'vacation',
                'recovery'  => 'active',
                'vacationreturn' => 'active',
            );
            if ( array_key_exists($order->data->reason, $statuses) )
            {
                if ( false === $this->dof->workflow('appointments')->
                        change($order->data->appointmentid, $statuses[$order->data->reason]) )
                {
                    $ok = false;
                    dof_debugging('Не удалось перевести в указанное состояние объект: '
                                                    .$order->data->appointmentid, DEBUG_DEVELOPER);
                }
            } else {
                $ok = false;
                dof_debugging('Такой причины не существует: '
                                                .$order->data->reason, DEBUG_DEVELOPER);
            }
        }
        return $ok;
    }
    
    
    private function transfer_from_teacher($orderdata)
    { // Входные: id потоков, где надо поменять id учителя, id учителей, привязанных к ним
        // Делаем обновление в storage('cstreams'): Меняем teacherid и appointmentid
        $ok = true;
        foreach ( $orderdata->cstreams as $object )
        {
            foreach ( $object->cstreams as $id => $cstream )
            {
                if ( ! $current = $this->dof->storage('cstreams')->get($cstream->id) )
                {
                    $ok = false;
                    dof_debugging('Не удалось получить запись по потоку: ' . $cstream->id, DEBUG_DEVELOPER);
                }
                // Передаём нагрузку преподавателям
                $current->appointmentid = $cstream->appointmentid;
                $current->teacherid = $cstream->teacherid;
                if ( ! $this->dof->storage('cstreams')->update($current) )
                {
                    $ok = false;
                }
            }
        }
        return $ok;
    }
    
    private function transfer_to_teacher($orderdata)
    { // Входные: id потоков, где надо поменять id учителя, id учителя, привязанного к ним
        // Делаем обновление в storage('cstreams'): Меняем teacherid и appointmentid
        $ok = true;
        foreach ( $orderdata->cstreams as $object )
        {
            foreach ( $object->cstreams as $id => $cstream )
            {
                if ( ! $current = $this->dof->storage('cstreams')->get($cstream->id) )
                {
                    $ok = false;
                    dof_debugging('Не удалось получить запись по потоку: ' . $cstream->id, DEBUG_DEVELOPER);
                }
                // Передаём нагрузку преподавателю
                $current->appointmentid = $orderdata->appointmentid;
                $current->teacherid = $orderdata->teacherid;
                if ( ! $this->dof->storage('cstreams')->update($current) )
                {
                    $ok = false;
                }
            }
        }
        return $ok;
    }
    
    private function return_to_teacher($orderdata)
    { // Входные: id потоков, где надо поменять id учителя, id учителей, привязанных к ним
        $ok = true;
        foreach ( $orderdata->cstreams as $object )
        {
            foreach ( $object->cstreams as $id => $cstream )
            {
                if ( ! $current = $this->dof->storage('cstreams')->get($cstream->id) )
                {
                    $ok = false;
                    dof_debugging('Не удалось получить запись по потоку: ' . $cstream->id, DEBUG_DEVELOPER);
                }
                // Возвращаем нагрузку
                $current->appointmentid = $orderdata->appointmentid;
                $current->teacherid = $orderdata->teacherid;
                if ( ! $this->dof->storage('cstreams')->update($current) )
                {
                    $ok = false;
                }
            }
        }
        return $ok;
    }
    
    
}
?>