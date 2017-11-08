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

// Подключаем библиотеку
require_once(dirname(realpath(__FILE__)).'/lib.php');
// Приказ
$id = required_param('id', PARAM_INT);
// Подтверждение на вопрос "вы уверены"
$state = optional_param('state', 0, PARAM_INT);
// Права
$DOF->im('cstreams')->require_access('order');

$order = new dof_im_cstreams_teacher($DOF, $id);
if ( $order->order == false )
{
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('order_notfound', $errorlink, $id, 'im', 'cstreams');
}
if ( $order->is_signed() )
{
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('order_wrote', $errorlink, $id, 'im', 'cstreams');
}
$orderdata = $order->get_order_data();
$paramsyes = array('edit' => $id);
switch ( $orderdata->data->state )
{
    case 1:
        $link = $DOF->url_im('cstreams', '/orderctload/form_second.php', array_merge($addvars,$paramsyes));
        break;

    case 2:
    case 3:
        $link = $DOF->url_im('cstreams', '/orderctload/form_third.php', array_merge($addvars,$paramsyes));
        break;

    default:
        $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
        $DOF->print_error('error_state', $errorlink, $id, 'im', 'cstreams');
        break;
}
redirect($link);

?>