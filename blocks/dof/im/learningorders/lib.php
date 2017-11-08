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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

final class dof_im_learningorders_orders_table 
{
    private $dof;
    private $orders;
    private $transfer;
    private $addvars; 
    
    /** Конструктор
     * 
     */
    public function __construct($dof,$orders,$transfer,$addvars=array())
    {
        $this->dof       = $dof;
        $this->orders    = $orders;
        $this->transfer  = $transfer;
        $this->addvars   = $addvars;        
    }
    
    /** Создание таблицы приказов
     * 
     */
    public function show_table()
    {
        $rez = '<h2 align="center">'.$this->dof->get_string('list_orders', 'learningorders').'</h2>';
        $table = new stdClass();
        $table->tablealign = "center";
        $table->cellpadding = 5;
        $table->cellspacing = 5;
        $table->head = $this->get_head_description();
        $table->align = array('center','center','center','center','center','center','center');
        
        if ( ! empty($this->orders) )
        {// есть приказы - строим таблицу
            foreach ( $this->orders as $order )
            {
                $table->data[] = $this->get_string_order($order);
            }
            
            $rez .= $this->dof->modlib('widgets')->print_table($table,true);
        }else 
        {// список пустой - скажем об этом
            $rez .= ' <br><h3 align="center">'.$this->dof->get_string('empty_order', 'learningorders').'</h3>';   
        }
          
        return $rez;
    }

    protected function get_string_order($order)
    {
        //Не зависимо от статуса приказа - покажем иконку редактирования (заметки, номер)
        $actions = "";
        if ( ! empty($order->signdate) )
        {// подписан - смотреть и исполнить
            // TODO сделать ссылку для подписан
            $actions .= '
            <a href="'.$this->dof->url_im('learningorders','/ordertransfer/formationorder.php?id='.$order->id,$this->addvars)
            .'" title="'. $this->dof->get_string('order_see','learningorders')
            .'"> <img src="'.$this->dof->url_im('learningorders', '/icons/view.png').'" ></a>';
            if (  empty($order->exdate) )
            {// приказ не исполнен - покажем ссылку
                $actions .= '
                <a href="'.$this->dof->url_im('learningorders', '/ordertransfer/readytransfer.php?orderid='.$order->id,$this->addvars)
                .'" title="'. $this->dof->get_string('order_ready','learningorders')
                .'"> <img src="'.$this->dof->url_im('learningorders', '/icons/ready.png').'"></a>';
            }
        }else
        {// не подписан - продолжить работу, сформировать заново, подписать
            // TODO сделать ссылку для готово
            $actions .= '
            <a href="'.$this->dof->url_im('learningorders', '/ordertransfer/ageschoice.php?id='.$order->id,$this->addvars)
            .'" title="'. $this->dof->get_string('order_new','learningorders')
            .'"> <img src="'.$this->dof->url_im('learningorders', '/icons/new.png').'" > </a>';
           
            $a = $this->transfer->load($order->id);
            
            if ( ! empty($a->data->student) )
            {// на случай чтоб не подписать и не просматривать ПУСТОЙ приказ
                $actions .= '
                <a href="'.$this->dof->url_im('learningorders', '/ordertransfer/formationorder.php?id='.$order->id,$this->addvars)
                .'" title="'. $this->dof->get_string('order_edit','learningorders')
                .'"> <img src="'.$this->dof->url_im('learningorders', '/icons/edit.png').'"> </a>
                <a href="'.$this->dof->url_im('learningorders', '/ordertransfer/subtransfer.php?orderid='.$order->id,$this->addvars)
                .'" title="'. $this->dof->get_string('order_write','learningorders')
                .'"> <img src="'.$this->dof->url_im('learningorders', '/icons/write.png').'">  </a>';
            }
        }
        
        $date = dof_userdate($order->date, '%d/%m/%Y');
        $owner = $this->dof->get_string('not_defined', 'learningorders');
        if ( $order->ownerid )
        {// ссылка на персону, создавшую приказ
            $owner = '<a href="'.$this->dof->url_im('persons','/view.php',array(
                    'id' => $order->ownerid, 'departmentid' => $this->addvars['departmentid']))
                    .'">'.$this->dof->storage('persons')->get_fullname($order->ownerid).'</a>';
        }
        $signer = $this->dof->get_string('not_defined', 'learningorders');
        if ( $order->signerid )
        {// ссылка на персону, подписавшую приказ
            $signer = '<a href="'.$this->dof->url_im('persons','/view.php',array(
                    'id' => $order->signerid, 'departmentid' => $this->addvars['departmentid']))
                    .'">'.$this->dof->storage('persons')->get_fullname($order->signerid).'</a>';
        }
        $signdate = $this->dof->get_string('not_defined', 'learningorders');
        if ( $order->signdate )
        {// дата подписания известна
            $signdate = dof_userdate($order->signdate, '%d/%m/%Y');    
        }
        $exdate = $this->dof->get_string('not_defined', 'learningorders');
        if ( $order->exdate )
        {// дата исполнения известна
            $exdate = dof_userdate($order->exdate, '%d/%m/%Y');
        }
        return array($actions, $order->id, $date, $owner, $signdate, $signer, $exdate);
    }
    
    /** Получение заголовков таблицы
     * @return array
     */
    protected function get_head_description()
    {
        return array($this->dof->get_string('action', 'learningorders'),
                     $this->dof->get_string('tbl_id', 'learningorders'),
                     $this->dof->get_string('tbl_createdate', 'learningorders'),
                     $this->dof->get_string('tbl_owner', 'learningorders'),
                     $this->dof->get_string('tbl_signdate', 'learningorders'),
                     $this->dof->get_string('tbl_signer', 'learningorders'),
                     $this->dof->get_string('tbl_exdate', 'learningorders'));
    }
}

?>
