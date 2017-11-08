<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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


// Подключаем библиотеки
require_once('lib.php');
require_once ('form.php');
// входные параметры
$orderid = required_param('id', PARAM_INT);

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('page_main_name', 'learningorders'), $DOF->url_im('learningorders','/index.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'learningorders'), $DOF->url_im('learningorders','/list.php',$addvars));
if (  $order = $DOF->storage('orders')->get($orderid) )
{// не удалось найти приказ
    $DOF->modlib('nvg')->add_level($DOF->get_string('order', 'learningorders'), $DOF->url_im('learningorders','/ordertransfer/formationorder.php?id='.$orderid,$addvars));
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'), $DOF->url_im('learningorders','/index.php',$addvars));
}



// права
$DOF->im('learningorders')->require_access('order');

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $order = $DOF->storage('orders')->get($orderid) )
{// не удалось найти приказ
	print_error($DOF->get_string('not_found_order','learningorders', $orderid));
}elseif( ! is_object($order) OR $order->code != 'transfer' )
{// приказ не типа transfer
    print_error($DOF->get_string('not_transfer','learningorders', $orderid));
}


if( ! $order->signdate )
{//дадим возможность пользователю переформировать приказ
    echo '<a href="'.$DOF->url_im('learningorders','/ordertransfer/ageschoice.php?id='.$orderid,$addvars).'">'.$DOF->get_string('go_back_to_agechoice','learningorders').'</a><br>';
}
// выводим приказ
$order = new dof_im_learningorders_ordertransfer($DOF, $orderid);
$orderdata = $order->get_order_data();
if ( empty($orderdata->data->student) )
{// данных нет - выведем сообщение
    echo '<p align="center" ><b>'.$DOF->get_string('not_found_students','learningorders').'</b></p>';
}else 
{
    echo "<br><b>".$DOF->get_string('content','learningorders').'</b>';
}

// якорь начала
echo "<br><a name='begin'></a>";

// печать ордера
$order->print_texttable(); 

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>