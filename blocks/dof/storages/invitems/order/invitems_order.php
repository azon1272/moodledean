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
 * Класс для прихода оборудования
 */
class dof_storage_invitems_order_new_items extends dof_storage_orders_baseorder
{
    // создадим переменную, хранящюю номер последней записи в orderdata
    public $lastorderdata = 0;
    
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'invitems';
    }
    
    public function code()
    {
        return 'new_items';
    }


    
    /* Метод, предусмотренный для расширения логики сохранения
     * тут происходит сохранение данных в справочник orderdata- сериализация данных
     * Вх данные array() [name]         => name
     *                   [invcategorid] => 18
     *                   [quantity]     => 4
     *                   [ data ] => array() [0] => array()	[itemid_1]     = itemid1
     *                   									[serialnum_1]  = serial1
     *                   									[invnum_1]     = code1
     *                   										
     *                   
     * 										 [1] => array() [itemid_2]     = itemid2
     *                   									[serialnum_2]  = serial2
     *                   									[invnum_2]     = code2	
     * @param object $order - сам ордер
     * @param object/array $data - данные масива для сериалицации(они тут разбираются)
     */
    protected function save_data( $order, $data )
    {

        // счетчик - сколько оборудования нам передали через форму
        // используется для отличия от переданног с количеством
        $i = 0;
        $num = 0;
        foreach ( $data as $key=>$value )
        {// перебираем сами данные
            
            if ( is_array($value) OR is_object($value) )
            {// не скаляр - разберем
                // кол пришедших элементов
                $count = $data->quantity;
                // категория 
                $categoryid = $data->categoryid;
                // Название оборудования
                $nameitem = $data->name;
                // значения с данными
                $mas = $data->mas;

                $errors = array();
                foreach ( $mas as $field=>$value )
                {// придется расчиплять тут поля на составные
                    // $value имеет вид array('serialnum'=> ...., 'invnum'=>...)   
                    // счетчик
                    $i++;
                    // заполняем orderdata serialnum, invnum, pitemid
                    $obj = new stdClass();
                    $obj->orderid = $order->id;
                    $obj->scalar = 1;
                    // встваим в orderdata serialnum (serialnum_1)
                    // увеличиваем номер переменной
                    $num++;
                    $obj->varnum = $num;
                    $obj->firstlvlname = 'serialnum_'.$i;
                    $obj->data = $value['serialnum'];
                    $obj->ind = $value['serialnum'];
                    $this->dof->storage('orderdata')->insert($obj);
                    // встваим в orderdata invnum (invnum_1)
                    // увеличиваем номер переменной
                    $num++;
                    $obj->varnum = $num;
                    $obj->firstlvlname = 'invnum_'.$i;
                    $obj->data = $value['invnum'];
                    $obj->ind = $value['invnum'];
                    $this->dof->storage('orderdata')->insert($obj);                    
                }
                // вставили ВСЁ, что было нам передано, А если пришло больше
                // то система при подписании сама создаст  
            }else 
            {// обычный скаляр
                $num++;
                $obj = new stdClass();
                $obj->orderid = $order->id;
                $obj->scalar = 1;
                // запоминаме имя
                $obj->firstlvlname = $key;
                // порядковый номер имени
                $obj->varnum = $num;
                
                $obj->data = $value;
                // запишем данные в поле индекс
                $obj->ind = $value; 
                // сохраняем запись
                if ( $element = $this->dof->storage('orderdata')->
                    get_records(array('orderid'      => $order->id,
                                      'firstlvlname' => $obj->firstlvlname,
                                      'varnum'       => $i) ) )
                {// запись существует
                    $this->dof->storage('orderdata')->update($obj, current($element)->id);
                }else 
                {// вставляем
                    $this->dof->storage('orderdata')->insert($obj);
                }                   
            }
        }
        
        return $order;
    }    

    /*
     *  Исполненеи приказа
     */    
    protected function execute_actions($order)
    {
        //print_object($order); die;
        // получим данные приказа
        $data = $order->data;
        // кол элементов
        $count = $data->quantity;
        // готовим объект для вставки в items
        $itemobj = new stdClass();
        $itemobj->name = $data->name;
        $itemobj->dateentry = time();
        $itemobj->type = 'unit';
        $itemobj->count = 1;
        $itemobj->invcategoryid = $data->categoryid;
        $itemobj->departmentid = $order->departmentid;
        $itemobj->invsetid = 0;
        $itemobj->status = 'active';
        $itemobj->setorderid = $order->id;
        // перебираем в цикле ВСЁ кол-во
        for ( $i=1; $i<=$data->quantity; $i++ )
        {
            $itemobj->serialnum = '';
            $itemobj->code = '';
            // обозначим переменные
            $serial = 'serialnum_'.$i;
            $code = 'invnum_'.$i;
              
            if ( ! empty($data->$serial) )
            {
                $itemobj->serialnum = $data->$serial;   
            }
            if ( ! empty($data->$code) )
            {
                $itemobj->code = $data->$code;   
            }
            // вставляем объект
            if ( $id = $this->dof->storage('invitems')->insert($itemobj) )
            {
                // если пустой код - создадим сами его из id
                if ( empty($data->$code) )
                {
                    $obj = new stdClass();
                    $obj->code = 'id'.$id;
                    $this->dof->storage('invitems')->update($obj, $id);
                } 
            }else 
            {// невставилась запись - плохо
                return false;
            }       
        }
        // всё хооршо
        return true;

    }    
}



/**
 * Класс для списании оборудования
 */
class dof_storage_invitems_order_delete_items extends dof_storage_orders_baseorder
{
    
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'invitems';
    }
    
    public function code()
    {
        return 'delete_items';
    }

	/* Метод, предусмотренный для расширения логики сохранения
     * тут происходит сохранение данных в справочник orderdata в скалярном виде
     * Вх данные array() [ data ] => array()   [0] => array()	[invnum_1]  = code1
     *                   						    			[itemid_1]  = itemid1
     *                   
     * 										    [1] => array()	[invnum_2]  = code2
     *                   						    			[itemid_2]  = itemid2	
     * @param object $order - сам ордер
     * @param object/array $data - данные масива для сериалицации(они тут разбираются)
     */
    protected  function save_data( $order, $data )
    {
        // счетчик - сколько оборудования нам передали через форму
        // используется для отличия от переданног с количеством
        $i = 0;
        // номер переменной(1,2,3...)
        $num = 1;
        //получаме массив значений кодов
        $data = $data->mas;
        // формируем объект и общие поля
        $orderdata = new stdClass();
        $orderdata->orderid = $order->id;
        $orderdata->scalar = 1;
        
        // вставим в приказ кол элементов
        $count = count($data); 
        $orderdata->firstlvlname = 'quantity';
        $orderdata->varnum = $num;
        $orderdata->data = $count;
        $orderdata->ind = $count;
        $this->dof->storage('orderdata')->insert($orderdata);
        
        // получаем реальные статусы
        $status = $this->dof->workflow('invitems')->get_list_param('actual');
    
        foreach ( $data as $value )
        {// перебираем сами данные
            // передали код - сохраняем + и id
            $i++;
            $num++;
            // инвент номер
            $orderdata->firstlvlname = 'invnum_'.$i;
            $orderdata->varnum = $num;
            $orderdata->data = $value;
            $orderdata->ind = $value;
            $this->dof->storage('orderdata')->insert($orderdata);
            // выявим id и вставим и его(для целостности данных)
            $num++;
            // проверка на сущ записи сделана в validation
            $item = $this->dof->storage('invitems')->get_records(array('code'=>$value,'status'=>$status));        
               
           
            // item = массив объектов
            $orderdata->firstlvlname = 'itemid_'.$i;
            $orderdata->varnum = $num;
            $orderdata->data = key($item);
            $orderdata->ind = key($item);
            $this->dof->storage('orderdata')->insert($orderdata);             
        }
        
        return $order;
    } 

    
    protected function execute_actions($order)
    {
        // ошибка
        if ( ! $data = $order->data )
        {
            return  false;
        }
        $flag = true;
        // ВСЁ хорошо, спишем оборудование тогда
        $obj = new stdClass();
        $obj->datewriteoff = time();
        $obj->outorderid = $order->id;         
        foreach ( $data as $field=>$id )
        {
            // берем поля с данными приказа. Если это поле "itemid", 
            // значит берем его значение и меняем статус
            // очень опасная функция(прочитать мануал по ней), сторого такое равенство,
            // т.к. значение 0-означает, что запись найдена, но это и false, потому и (bool) 
            if ( strpos($field, 'itemid') !== (bool)false )
            {
                $flag = ($flag AND $this->dof->workflow('invitems')->change($id,'scrapped'));
                // укажем время списания
                $this->dof->storage('invitems')->update($obj,$id);
            }
        }
        // вернем результат 
        return $flag;
        
    }
    
   
}


?>