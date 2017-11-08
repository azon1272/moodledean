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
$confirm = optional_param('confirm', 0, PARAM_INT);
// Права
$DOF->im('cstreams')->require_access('order');
if ( ! $personid = $DOF->storage('persons')->get_by_moodleid_id() )
{// Если id персоны не найден
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('error_person', $errorlink, null, 'im', 'cstreams');
}

// Добавление уровней навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('order_change_teacher', 'university'), $DOF->url_im('cstreams','/orderctload/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'cstreams'), $DOF->url_im('cstreams','/orderctload/list.php',$addvars));
$order = new dof_im_cstreams_teacher($DOF, $id);

if ( $order->order == false )
{// Приказ не существует
    $errorlink = $DOF->url_im('cstreams','/orderctload/list.php',$addvars);
    $DOF->print_error('order_notfound', $errorlink, $id, 'im', 'cstreams');
}

if ( ! $confirm )
{// Формируем предупреждение "вы уверены что хотите исполнить приказ?"
    $paramsyes = array('id' => $id, 'confirm' => 1);
    $linkyes   = $DOF->url_im('cstreams', '/orderctload/execute.php',array_merge($addvars,$paramsyes));
    $linkno    = $DOF->url_im('cstreams', '/orderctload/list.php',$addvars);
    $confirmmessage = $DOF->get_string('order_readysure', 'cstreams');
    
    // Печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // Сообщение с просьбой подтвердить выбор
    $DOF->modlib('widgets')->notice_yesno($confirmmessage, $linkyes, $linkno);
    // Печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}else
{
    $backurl = '<a href="'.$DOF->url_im('cstreams','/orderctload/list.php',$addvars).'">'.$DOF->modlib('ig')->igs('back').'</a>';
    //    
    if ( ! $order->check_order_data() )
    {// Устаревший приказ
        // Печать шапки страницы
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // Сообщение с просьбой подтвердить выбор
        echo '<p style=" color:red; text-align:center"><b>'.$DOF->get_string('order_old', 'cstreams').'</b></p>';
        echo '<p style=" text-align:center">'.$backurl.'</p>';
        // Печать подвала
        $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    }elseif ( ! $order->is_signed() )
    {// Не подписан
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // Сообщение с просьбой подтвердить выбор
        echo '<p style=" color:red; text-align:center"><b>'.$DOF->get_string('order_notsigned', 'cstreams').'</b></p>';
        echo '<p style=" text-align:center">'.$backurl.'</p>';
        // Печать подвала
        $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    }elseif ( $order->execute() )
    {// Исполнен успешно
        redirect($DOF->url_im('cstreams','/orderctload/list.php',$addvars));
    }else
    {// Не исполнен
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // Сообщение с просьбой подтвердить выбор
        echo '<p style=" color:red; text-align:center"><b>'.$DOF->get_string('order_nowready', 'cstreams').'</b></p>';
        echo '<p style=" text-align:center">'.$backurl.'</p>';
        // Печать подвала
        $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
    }
}

?>