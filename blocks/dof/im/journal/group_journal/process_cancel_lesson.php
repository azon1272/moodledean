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
$planid = required_param('planid',PARAM_INT);
$msg = '';
//обработчик формы
if ($cancellesson->is_submitted() AND confirm_sesskey() AND $formdata = $cancellesson->get_data())
{
    //print_object($formdata);//die;
    if ( isset($formdata->yes_cancel) )
    {// если стоит подтверждение отмены урока
        if ( $DOF->storage('schevents')->cancel_event($formdata->eventid,true,true))
        {
            $rez .= '<p align="center" style=" color:green; "><b>'.$DOF->get_string('lesson_cancel_true','journal').'</b></p>';
            $flag = false;
        }else
        {
            $rez .= '<p align="center" style=" color:red; "><b>'.$DOF->get_string('data_dontsave','journal').'</b></p>';
            $flag = true;
        }
    }
}
?>