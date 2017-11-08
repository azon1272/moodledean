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

// права
$DOF->im('learningorders')->require_access('order');

// входные параметры
$orderid    = required_param('orderid', PARAM_INT);
$agenum     = required_param('agenum', PARAM_INT);
$groupid    = optional_param('groupid',0 , PARAM_INT);
$newageid   = required_param('newageid', PARAM_INT);
// тип перевода по умолчанию
$type       = required_param('type', PARAM_TEXT );
// id программы - передается в том случае когда редактируется список учеников без группы
$programmid = optional_param('programm', 0, PARAM_INT);

// собираем объект, который для отправки понадобиться
$obj = new stdClass();

$obj->agroupid =  $groupid;
$obj->newtype  =  'exclude';
$obj->newageid =  $newageid;
$obj->agenum   =  $agenum;
$obj->prog    =   $programmid;


// объявим ордер
$order = new dof_im_learningorders_ordertransfer($DOF, $orderid);
if( $groupid )
{// менялась вся группа
    $order->save_options_agroup($type, $obj);
}else 
{// ученики без группы(много)
    $order->save_options_students_nogroup($type, $obj, true);
} 

// перейдем на глв страницу
redirect($DOF->url_im('learningorders','/ordertransfer/formationorder.php?id='.$orderid,$addvars));

?>