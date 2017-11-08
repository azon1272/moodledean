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
// подключаем библиотеки верхнего уровня
require_once('lib.php');
//проверяем доступ
$DOF->storage('eagreements')->require_access('edit');

if ( $dischargeform->is_submitted() AND confirm_sesskey() AND $formdata = $dischargeform->get_data() )
{//даные переданы в текущей сессии - получаем
    
    //print_object($formdata);//die; 
    // создаем объект для сохранения в БД
    if ( isset($formdata->discharge) AND isset($formdata->group) )
    {// сказано освободить сотрудника с должностей
        $appointments = $formdata->group;
        $discharge = true;
        foreach ( $appointments as $id=>$discharge )
        {
            if ( isset($discharge['discharge']) AND $discharge['discharge'] )
            {// освобождаем сотрудника с должности
                $discharge = & $DOF->workflow('appointments')->change($id, 'canceled');
            } 
        }
        if ( $discharge )
        {// освободили со всех должностей
            $errordischarge .= '<p style=" color:green; " align="center"><b>'.
                      $DOF->get_string('discharge_success','employees').'</b></p>';
        }else
        {// где-то глюкнуло
            $errordischarge .= '<p style=" color:red; " align="center"><b>'.
                      $DOF->get_string('discharge_failure','employees').'</b></p>';
        }
    }
    if ( isset($formdata->dismiss) AND isset($formdata->confirm_dismiss) AND $formdata->confirm_dismiss )
    {// нам сказали уволить сотрудника
        if ( $DOF->workflow('eagreements')->change($formdata->eagreementid, 'canceled') )
        {// успешно уволили
            $errordischarge .= '<p style=" color:green; " align="center"><b>'.
                      $DOF->get_string('dismiss_success','employees').'</b></p>';
        }else
        {// не получилось
            $errordischarge .= '<p style=" color:red; " align="center"><b>'.
                      $DOF->get_string('dismiss_failure','employees').'</b></p>';
        }
    }

}
?>