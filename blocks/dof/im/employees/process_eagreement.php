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
if ( ! $id )
{//проверяем доступ
    $DOF->storage('eagreements')->require_access('create');
}else
{//проверяем доступ
    $DOF->storage('eagreements')->require_access('edit',$id);
}

if ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    
    //print_object($formdata);die; 
    // определим какого типа пользователь
    $usertype = $formdata->userid[0];
    // узнаем id пользователя
    $userid   = $formdata->userid[1];
    if ( $usertype == 'fdo' )
    {// это пользователь из деканата - просто запишем его id 
        $personid = $userid;
    }elseif ( $usertype == 'moodle' )
    {// если пользователь из moodle - то сначала создадим пользователя FDO а потом получим его id
        if ( ! $mdluser = $DOF->modlib('ama')->user($userid)->get() )
        {// указан неправильный id мользователя в moodle
            $DOF->print_error($DOF->get_string('no_user_id_specified', 'employees'));
        }
        if ( ! $personid = $DOF->storage('persons')->reg_moodleuser($mdluser) )
        {// не удалось создать пользователя moodle - прекращаем обработку
            $DOF->print_error($DOF->get_string('err_moodleuser_creation_failed', 'employees'));
        }
    }else
    {// id пользователя не указан вообще - это ошибка
        $DOF->print_error($DOF->get_string('no_user_id_specified', 'employees'));
    }
    // создаем объект для сохранения в БД
    $obj = new stdClass();
    $obj->personid     = $personid;
    $obj->date         = $formdata->date;
    $obj->notice       = $formdata->notice;
    $obj->departmentid = $formdata->departmentid;
    if ( isset($formdata->num) AND trim($formdata->num) )
    {// если номер договора указан вручную - запишем его в базу
        $obj->num = $formdata->num;
    }
    if ( $formdata->eagreementid )
    {// назначение редактировалось - обновим запись в БД
        $eagreementid = $formdata->eagreementid;
        if ( ! $DOF->storage('eagreements')->update($obj,$formdata->eagreementid) )
        {
            $error .= '<p style=" color:red; " align="center"><b>'.
                      $DOF->modlib('ig')->igs('record_update_failure').'</b></p>';
        }
    }else
    {// назначение создавалось
        // сохраняем запись в БД
        if( $eagreementid = $DOF->storage('eagreements')->insert($obj) )
        {// все в порядке - сохраняем статус
            $DOF->workflow('eagreements')->init($eagreementid);
        }else
        {// не сохранилось - сообщаем об ошибке
            $error .= '<p style=" color:red; " align="center"><b>'.
                      $DOF->modlib('ig')->igs('record_insert_failure').'</b></p>';
        }
    }
    if ( $error == "" )
    {// если ошибок нет
        // удалим лишнее
        unset($obj->personid);
        unset($obj->notice);
        // создадим объект для назначения
        $obj->eagreementid  = $eagreementid;
        $obj->schpositionid = $formdata->schpositionid;
        $obj->enumber       = $formdata->enumber;
        $obj->worktime      = $formdata->worktime;
        
        if ( $formdata->id AND ! $error )
        {// назначение редактировалось - обновим запись в БД
            if ( $DOF->storage('appointments')->update($obj,$formdata->id) )
            {
                redirect($DOF->url_im('employees','/view_appointment.php?id='.$formdata->id,$addvars));
            }else
            {
                $error .= '<br>'.$DOF->get_string('errorsaveage','ages').'<br>';
            }
        }else
        {// назначение создавалось
            // сохраняем запись в БД
            if( $id = $DOF->storage('appointments')->insert($obj) )
            {// все в порядке - сохраняем статус и возвращаем на страниу просмотра
                $DOF->workflow('appointments')->init($id);
                redirect($DOF->url_im('employees','/view_appointment.php?id='.$id,$addvars));
            }else
            {// не сохранилось - сообщаем об ошибке
                $error .=  '<br>'.$DOF->get_string('errorsaveage','ages').'<br>';
            }
        }
    }

}

?>