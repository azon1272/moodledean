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
 * о выставлении текущих оценок
 */
class dof_im_journal_order_set_itog_grade extends dof_storage_orders_baseorder
{
    public function plugintype()
    {
        return 'im';
    }
    
    public function plugincode()
    {
        return 'journal';
    }
    
    public function code()
    {
        return 'set_itog_grade';
    }
    
    protected function execute_actions($order)
    {
        //получили оценки из приказа
        if ( ! isset($order->data) OR ! $order->data )
        {//не получили оценки из приказа
            return false;
        }
        //print_object($order->data);//die;
        //сохраняем оценки
        $rez = true;
        foreach ( $order->data->itoggrades as $sbcid=>$grade)
    	{// сохраняем оценки
            $select = 'programmsbcid = '.$sbcid.
                      ' AND cstreamid = '.$order->data->cstreamid.
                      " AND repeatid IS NULL AND status != 'canceled' ";
            $cpass = $this->dof->storage('cpassed')->get_records_select($select);
            if ( $cpass AND is_array($cpass) )
            {// если нашли запись - то она единственная
                $cpass = current($cpass);
            }else
    		{// подписка не найдена - это неправильно
    			$rez = false;
    			continue;
    		}
    		// найдем наследника
    		$successorid = $this->dof->storage('cpassed')->get_last_successor($cpass->id);
    		$rez = ( $rez AND  $this->dof->storage('cpassed')->set_final_grade($successorid, $grade['grade'], $order->id) );
    	}
        return $rez;
    }
}

?>