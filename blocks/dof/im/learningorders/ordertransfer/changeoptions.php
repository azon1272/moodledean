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
$orderid    = required_param('orderid', PARAM_INT);
$agenum     = required_param('agenum', PARAM_INT);
$sbcid      = optional_param('sbcid',0 , PARAM_INT);
$groupid    = optional_param('groupid',0 , PARAM_INT);
$newageid   = required_param('newageid', PARAM_INT);
// тип перевода по умолчанию
$type       = required_param('type', PARAM_TEXT );
// id программы - передается в том случае когда редактируется список учеников без группы
$programmid = optional_param('programm', 0, PARAM_INT);

$addvars1 = array( 'orderid'  => $orderid ,
                  'agenum'      => $agenum,
                  'sbcid'       => $sbcid,
                  'groupid'     => $groupid,
                  'newageid'    => $newageid,
                  'type'        => $type,
                  'programmid'  => $programmid,
                  'departmentid' => $addvars['departmentid'] );
// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'learningorders'), $DOF->url_im('learningorders','/list.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('order', 'learningorders'),  $DOF->url_im('learningorders','/ordertransfer/formationorder.php?id='.$orderid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('edit', 'learningorders'), 
    $DOF->url_im('learningorders',"/ordertransfer/changeoptions.php?",$addvars1)) ;

// права
$DOF->im('learningorders')->require_access('order');

// Запишем выбранные
$customdata = new stdClass();
$customdata->dof        = $DOF;
$customdata->orderid    = $orderid;
$customdata->agenum     = $agenum;
$customdata->sbcid      = $sbcid;
$customdata->groupid    = $groupid;
$customdata->newageid   = $newageid;
$customdata->type       = $type;
$customdata->programmid = $programmid;

// форма редактирования студента, группы
$edit_student = new dof_im_learningorders_ordertransfer_group_and_student
    ($DOF->url_im('learningorders','/ordertransfer/changeoptions.php?orderid='.$orderid.
     '&agenum='.$agenum.'&newageid='.$newageid.'&type='.$type.'&groupid='.$groupid.'&sbcid='.$sbcid,$addvars), $customdata);

if ( $edit_student->is_cancelled()  )
{// отмена - возврат
   redirect($DOF->url_im('learningorders','/ordertransfer/formationorder.php?id='.$orderid,$addvars)); 
}    

if ( $edit_student->is_submitted() AND confirm_sesskey() AND $formdata = $edit_student->get_data() )
{

//    print_object($formdata); die();
    $order = new dof_im_learningorders_ordertransfer($DOF,$orderid);
    //print_object($order->get_order_data()->data);die();
    if ( $sbcid )
    {// если был студент - редактируем только студента(в греппе или без)
        $order->save_options_student($formdata->type,$formdata->sbcid,$formdata);
    }elseif( $formdata->agroupid != 0 )
    {// менялась вся группа и/или ученики
        $order->save_options_agroup($formdata->type,$formdata);
    }else 
    {// ученики без группы(много)
        $order->save_options_students_nogroup($formdata->type, $formdata);
    }
    redirect($DOF->url_im('learningorders','/ordertransfer/formationorder.php?id='.$orderid,$addvars));
}
    
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$edit_student->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>