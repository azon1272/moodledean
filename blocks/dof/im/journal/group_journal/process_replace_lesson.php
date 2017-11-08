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
/*
 * Описание файла
 */
require_once('lib.php');
$msg = '';
//обработчик формы
if ($transferlesson->is_submitted() AND confirm_sesskey() AND $formdata = $transferlesson->get_data())
{
    //print_object($formdata);//die;
    if ( isset($formdata->postpone_lesson) )
    {// если стоит подтверждение отмены урока
        if ( $DOF->workflow('schevents')->change($formdata->eventid,'postponed'))
        {
            $rez .= '<p align="center" style=" color:green; "><b>'.$DOF->get_string('lesson_transfer_true','journal').'</b></p>';
            $flag = false;
        }else
        {
            $rez .= '<p align="center" style=" color:red; "><b>'.$DOF->get_string('data_dontsave','journal').'</b></p>';
            $flag = true;
        }
    }

    if ( isset($formdata->replace_lesson) )
    {// если стоит подтверждение отмены урока
        // чтоб не было ошибки
        if ( ! isset($formdata->teacher) )
        {
            $formdata->teacher = null;
        }
        $replace = new stdClass;
        $replace->date = $formdata->date;
        $replace->appointmentid = $formdata->teacher;
        if ( $DOF->storage('schevents')->replace_events($formdata->eventid, $replace) )
        {
            $rez .= '<p align="center" style=" color:green; "><b>'.$DOF->get_string('lesson_transfer_true','journal').'</b></p>';
            $flag = false;
        }else
        { 
            $error = '';
            $access = $DOF->im('journal')->is_access_replace($formdata->eventid);
            // проверим по времени
            $cstreamid = $DOF->storage('schevents')->get_field($formdata->eventid, 'cstreamid');
            $ageid = $DOF->storage('cstreams')->get_field($cstreamid, 'ageid');
            $age = $DOF->storage('ages')->get($ageid);
            if ( ($formdata->date < $age->begindate OR $formdata->date > $age->enddate) 
                     AND ! $DOF->is_access('datamanage') ) 
            {// даты начала и окончания события не должны вылезать за границы периода
                $error = $DOF->get_string('err_date','journal', 
                    dof_userdate(time(),'%Y/%m/%d').'-'.
                    dof_userdate($age->enddate,'%Y/%m/%d'));
            }
            if ( ! $access->ignorolddate )
            {// игнорировать новую дату урока нельзя
                if ( $formdata->date < time() )
                {// переносить можно только на еще не наступившее время
                    $error = $DOF->get_string('err_date_postfactum','journal');
                }
                // @todo если границы бутут определятся в конфиге сделаем потом через него
                
                // @todo сделать проверку, если у ученика или учителя уже есть на это время уроки
            }
            $rez .= '<p align="center" style=" color:red; "><b>'.$error.'</b></p>';
            $flag = true;
            
        }    
    }
}
?>