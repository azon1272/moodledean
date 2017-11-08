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
require_once(dirname(realpath(__FILE__)).'/lib.php');
require_once(dirname(realpath(__FILE__)).'/form.php');
// входные параметры
$orderid = required_param('id', PARAM_INT);

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), $DOF->url_im('cstreams','/orderctload/index.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'cstreams'), $DOF->url_im('cstreams','/orderctload/list.php',$addvars));

$order = new dof_im_cstreams_teacher($DOF, $orderid);
if ( ! empty($order->order) )
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('order_see', 'cstreams'), $DOF->url_im('cstreams','/orderctload/view.php?id='.$orderid,$addvars));
}else 
{// Не удалось найти приказ
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('order_notfound', $errorlink, $orderid, 'im', 'cstreams');
}

// права
$DOF->im('cstreams')->require_access('order');

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// выводим приказ
$orderdata = $order->get_order_data();
if ( empty($orderdata->data->cstreams) )
{// данных нет - выведем сообщение
    echo '<p align="center" ><b>'.$DOF->get_string('order_nodata','cstreams').'</b></p>';
}else 
{
    echo "<br><b>".$DOF->get_string('content','cstreams').'</b>';
}

// печать ордера
$order->print_texttable();

if( ! $order->is_executed() )
{// Если приказ не исполнен, то
    if( ! $order->is_signed() )
    {// Дадим возможность пользователю переформировать приказ и подписать его
        echo '<br><a href="'.$DOF->url_im('cstreams','/orderctload/edit.php?id='.$orderid,$addvars).'">'.$DOF->get_string('order_edit','cstreams').'</a>';
        echo '<br><a href="'.$DOF->url_im('cstreams','/orderctload/sign.php?id='.$orderid,$addvars).'">'.$DOF->get_string('order_write','cstreams').'</a>';
        echo '<br><a href="'.$DOF->url_im('cstreams','/orderctload/sign_execute.php?id='.$orderid,$addvars).'">'.$DOF->get_string('order_writeready','cstreams').'</a>';
    }
    if( $order->is_signed() )
    {// Если приказ подписан, то можно только исполнить приказ
        echo '<br><a href="'.$DOF->url_im('cstreams','/orderctload/execute.php?id='.$orderid,$addvars).'">'.$DOF->get_string('order_ready','cstreams').'</a>';
    }
}
// Выводим только ссылку возврата назад
echo '<br><a href="'.$DOF->url_im('cstreams','/orderctload/list.php',$addvars).'">'.$DOF->get_string('list_orders','learningorders').'</a>';

//$paramsyes  = array('id' => $id, 'confirm' => 1);
//$linkyes    = $DOF->url_im('cstreams', '/orderctload/execute.php', array_merge($addvars,$paramsyes));
//$linkno     = $DOF->url_im('cstreams', '/orderctload/list.php',$addvars);
//$messageyes = $DOF->get_string('order_execute');
//$messageno  = $DOF->url_im('cstreams', '/orderctload/list.php',$addvars);
//$confirmmessage = $DOF->get_string('order_writesure', 'cstreams');

// сообщение с просьбой подтвердить выбор
//$DOF->modlib('widgets')->notice_custom($confirmmessage, $linkyes, $linkno, $messageyes, $messageno);
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>