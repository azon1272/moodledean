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
 * Класс формирования комплектов
 */
class dof_storage_invitems_order_set_invsets extends dof_storage_orders_baseorder
{
    
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'invsets';
    }
    
    public function code()
    {
        return 'forming_set';
    }

	/* Метод, предусмотренный для расширения логики сохранения
     * тут происходит сохранение данных в справочник orderdata в скалярном виде

     */
    protected  function save_data( $order, $data )
    {
        // номер переменной
        $num = 1;
        $obj = new stdClass();
        $obj->orderid = $order->id;
        $obj->scalar = 1;
        // всатвим кол оборудования в комплекте
        $obj->firstlvlname = 'count';
        $obj->varnum = $num;
        $obj->data = $data->count;
        $obj->ind = $data->count; 
        $this->dof->storage('orderdata')->insert($obj);
        // кол комплектов
        $num++;
        $obj->firstlvlname = 'quantity';
        $obj->varnum = $num;
        $obj->data = $data->quantity;
        $obj->ind = $data->quantity; 
        $this->dof->storage('orderdata')->insert($obj); 
        // категория
        $num++;
        $obj->firstlvlname = 'categoryid';
        $obj->varnum = $num;
        $obj->data = $data->categoryid;
        $obj->ind = $data->categoryid; 
        $this->dof->storage('orderdata')->insert($obj);   
        
        // вставим теперь то, что нам передали уже для 1 комплекта
        $i = 1;
        // соберем выбранные категории в массив
        $catid_items = array();
        for ( $i=1; $i <= $data->count; $i++ )
        {
            $cat = 'cat_'.$i;
            $catid_items[$i] = $data->$cat;  
        }
        // для 1 комплекта всегда проставлены iditem, смело их и берем
        if ( $data->quantity == 1 )
        {
            // подставляем тогда переданные id оборудования
            // соберем выбранные id в массив
            $items = array();
            for ( $i=1; $i <= $data->count; $i++ )
            {
                $item = 'item_'.$i;
                $items[$i] = $data->$item;
            }            
            // переменная-флаг, отвечающюю за запись(true - запись записана)
            $flag = false;
            foreach ( $catid_items as $key=>$id)
            {
                // проверка, стоит ли id или 0
                if ( empty($items[$key]) )
                {// придется самим выбрать из ЭТОЙ категории и дочерних
                    // и чтоб не совпла уже с сущест
                    // ПРИ этом, сначало берем из ТЕКУЩЕЙ категории и лишь потом рассматриваем дочек, если есть
                    if ( $itemparent = $this->dof->storage('invitems')->get_records_select("departmentid=$order->departmentid AND status='active' 
                    			AND invsetid=0 AND invcategoryid=$id") )
                    {
                        foreach ( $itemparent as $itemid=>$itemobj )
                        {
                            if ( ! in_array($itemid, $items) AND $this->dof->storage('invitems')->is_access('use',$itemid) )
                            {// нет в массиве - запишем и идем дальше
                                $items[$key] = $itemid;
                                $flag = true;
                                break;
                            }
                        }
                    }
                    // не нашлось оборудования в родителе - берем из дочек
                    if ( ! $flag )
                    {
                        $itemdaughter = $this->dof->storage('invitems')->
                            get_category_subordinated_items($data->categoryid, array('status'=>'active'),$order->departmentid,'use');
                        foreach ( $itemdaughter as $itemid=>$itemobj )
                        {
                            if ( ! in_array($itemid, $items) )
                            {// нет в массиве - запишем и идем дальше
                                $items[$key] = $itemid;
                                break;
                            }
                        }                            
                            
                    }
                        
                }
                $num++;
                $obj->firstlvlname = 'comp1_cat'.$key;
                $obj->varnum = $num;
                $obj->data = $items[$key];
                $obj->ind = $items[$key]; 
                $this->dof->storage('orderdata')->insert($obj); 
                $i++;
            }
        }else 
        {// все подставляется автоматом
            // отсортируем категории начиная с дочек
            usort($catid_items, array('dof_storage_invitems_order_set_invsets', 'sortapp_by_depth')); 
            // выберим нужное кол ообрудования для каждого комплекта
            // сюда будем собирать все категори, который уже просмотрели
            $cats = array();
            // номер категории
            $i = 1;
            $items = array();
            foreach ( $catid_items as $catid )
            {
                $cats = $this->dof->storage('invcategories')->category_list_subordinated($catid,null,null,true,'',$order->departmentid);
                // дозапишем отца
                $cats[$catid] = $catid;
                // выберим ключи(именно в них содержаться id категории)
                $cats = array_keys($cats); 
                // вычислим число-количество вхождение категорий
                $n = count(array_intersect($catid_items, $cats));
                // сделаем лимит для экономии
                $limit = (int)$n*(int)$data->quantity;
                // получаем массив результатов
                if ( ! $value = $this->dof->storage('invitems')->get_category_subordinated_items($catid, array('status'=>'active'), $order->departmentid,'use' ) )
                {// по неизвестным никому причинам это случилось
                    return false;
                }
                // номер комплекта
                $j = 1;
                // заносим это в ордердата во все комплекты
                foreach ( $value as $key=>$item )
                {
                    if ( ! in_array($item->id, $items) )
                    {
                        $num++;
                        $obj->firstlvlname = "comp".$j."_cat".$i;
                        $obj->varnum = $num;
                        $obj->data = $item->id;
                        $obj->ind = $item->id; 
                        $this->dof->storage('orderdata')->insert($obj); 
                        // запомним, которые уже записали
                        $items[] = $item->id;
                        $j++; 
                        // записали нуженое - выход
                        if ( $j > $data->quantity )
                        {
                            break;
                        }
                    }    
                }
                $i++;
            }    
        }
        // считаем , что все данные занесены в orderdata, значит роль save выполнена
        return $order;
    } 

    /*
     * Расширенный метод исполнения приказа
     */
    protected function execute_actions($order)
    {
        // ошибка
        if ( ! $data = $order->data )
        {
            return  false;
        }
        // перебираем кол комплектов и записываем
        for ( $i=1; $i<=$data->quantity; $i++ )
        {
            $comp = "comp".$i."_cat1";
            if ( isset($data->$comp) )
            {// есть комплект - создадим его
                $obj = new stdClass();
                $obj->invcategoryid = $data->categoryid;
                $obj->departmentid  = $order->departmentid ;
                $obj->status  = 'active' ;
                // TODO узнать про возвращаемость
                //$obj->type  = 'active' ;
                
                if ( ! $id = $this->dof->storage('invsets')->insert($obj) )
                {// по непонятным причинас пропускаем этот комплект
                    continue;
                }
                // категория
                $obj = new stdClass();
                $obj->code = "id".$id;
                // обновим
                $this->dof->storage('invsets')->update($obj,$id);
            
                // перебираем число елементов в комплекте
                for ( $j=1; $j<=$data->count; $j++ )
                {
                    // комплект создали, петерь укажем в оборудовании, что но в комплекте
                    $field = "comp".$i."_cat".$j;
                    if ( ! empty($data->$field) )
                    {
                        $obj = new stdClass();
                        $obj->invsetid = $id;
                        $this->dof->storage('invitems')->update($obj, $data->$field);
                    }
                    
                }
            }
        }
        
        return true;
    }
    
    /**
     * Функция сравнения двух объектов 
     * из таблицы invcategories по полю depth
     * сортирует от дочек к родителям
     * @param integer $cat1 - id из таблицы invcategories
     * @param integer $cat2 - id из таблицы invcategories
     * @return -1, 0, 1 в зависимости от результата сравнения
     */
    protected function sortapp_by_depth($cat1,$cat2)
    {
        if ( (int)$this->dof->storage('invcategories')->get_field($cat1, 'depth') < 
             (int)$this->dof->storage('invcategories')->get_field($cat2, 'depth') )
        {
            return 1;
        }elseif( (int)$this->dof->storage('invcategories')->get_field($cat1, 'depth') > 
                 (int)$this->dof->storage('invcategories')->get_field($cat2, 'depth') )
        {
            return -1;
        }
        // равны
        return 0;     
    }     
}

/**
 * Класс расформирования комплектов
 */
class dof_storage_invitems_order_set_no_invsets extends dof_storage_orders_baseorder
{
    
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'invsets';
    }
    
    public function code()
    {
        return 'dissolution_set';
    }

	/* Метод, предусмотренный для расширения логики сохранения
     * тут происходит сохранение данных в справочник orderdata в скалярном виде

     */
    protected  function save_data( $order, $data )
    {
        // номер переменной
        $num = 1;

        $obj = new stdClass();
        $obj->orderid = $order->id;
        $obj->scalar = 1;
        // всатвим кол оборудования в комплекте
        $obj->firstlvlname = 'count';
        $obj->varnum = $num;
        $obj->data = $data->count;
        $obj->ind = $data->count; 
        $this->dof->storage('orderdata')->insert($obj);
        // id комплекта
        $num++;
        $obj->firstlvlname = 'setid';
        $obj->varnum = $num;
        $obj->data = $data->setid;
        $obj->ind = $data->setid; 
        $this->dof->storage('orderdata')->insert($obj); 
        // соберем всё оборудование находящееся в комплекте
        $items = $this->dof->storage('invitems')->get_records(array('invsetid' => $data->setid));
        // запишем его в orderdata
        $i = 1;
        foreach ( $items as $itemobj )
        {
            $num++;
            $obj->firstlvlname = 'item_'.$i;
            $obj->varnum = $num;
            $obj->data = $itemobj->id;
            $obj->ind = $itemobj->id; 
            $this->dof->storage('orderdata')->insert($obj);   
            $i++;          
        }
        // всё сохраниили - возвращаемся
        return $order;
    } 

    /*
     * Расширенный метод исполнения приказа
     */
    protected function execute_actions($order)
    {
        // ошибка
        if ( ! $data = $order->data )
        {
            return  false;
        }
                
        // перебираем кол комплектов и записываем
        for ( $i=1; $i<=$data->count; $i++ )
        {
            $item = "item_".$i;
            if ( isset($data->$item) )
            {// есть информация об оборудовании - обработаем его
                $obj = new stdClass();
                $obj->invsetid = 0;
                $this->dof->storage('invitems')->update($obj,$data->$item);
            }
            // поменяем статус и у самого комплекта
            $this->dof->workflow('invsets')->change($data->setid, 'deleted');
        }
        
        return true;
    }
    
   
}

/**
 * Класс выдачи 1 комплекта
 */
class dof_storage_invitems_order_set_delivery extends dof_storage_orders_baseorder
{
    
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'invsets';
    }
    
    public function code()
    {
        return 'delivery_set';
    }


    /*
     * Расширенный метод исполнения приказа
     */
    protected function execute_actions($order)
    {
        // ошибка
        if ( ! $data = $order->data )
        {
            return  false;
        }
        // выдали комплект-отметим это
        $set = new stdClass();
        $set->personid = $data->personid;
        if ( ! $this->dof->storage('invsets')->update($set, $data->setid) )
        {// по непонятным причинам не удалось исполнить
            return false;
        }
        // поменяем статус и у самого комплекта
        if ( ! $this->dof->workflow('invsets')->change($data->setid, 'granted') )
        {// по непонятным причинам не удалось исполнить
            return false;
        }
        // всё хорошо
        return true;
    }
    
   
}

/**
 * Класс возврата комплекта
 */
class dof_storage_invitems_order_set_return extends dof_storage_orders_baseorder
{
    
    public function plugintype()
    {
        return 'storage';
    }
    
    public function plugincode()
    {
        return 'invsets';
    }
    
    public function code()
    {
        return 'return_set';
    }


    /*
     * Расширенный метод исполнения приказа
     */
    protected function execute_actions($order)
    {
        // ошибка
        if ( ! $data = $order->data )
        {
            return  false;
        }
        // выдали комплект-отметим это
        $set = new stdClass();
        $set->personid = '';
        if ( ! $this->dof->storage('invsets')->update($set, $data->setid) )
        {// по непонятным причинам не удалось исполнить
            return false;
        }
        // поменяем статус и у самого комплекта
        if ( ! $this->dof->workflow('invsets')->change($data->setid, 'active') )
        {// по непонятным причинам не удалось исполнить
            return false;
        }
        // всё хорошо
        return true;
    }
    
   
}
?>