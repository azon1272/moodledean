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
if ( ! $id )
{// права доступа
    $DOF->storage('appointments')->require_access('create');
}else
{// права доступа
    $DOF->storage('appointments')->require_access('edit',$id);
}

if ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    
    //print_object($formdata);die; 
    // создаем объект для сохранения в БД
    $obj = new stdClass();
    $obj->schpositionid = $formdata->schpositionid;
    $obj->enumber = $formdata->enumber;
    $obj->worktime = $formdata->worktime;
    if ( !empty($formdata->combination) )
    {
        $obj->combination = $formdata->combination;
    }else
    {
        $obj->combination = 0;
    }
    $obj->date = $formdata->date;
    $obj->departmentid = $formdata->departmentid;
    if ( is_array($formdata->eagreementid) )
    {// Данные отправляются через autocomplete
        $obj->eagreementid = $formdata->eagreementid['id_autocomplete'];
    }else
    {// Данные собираются отправляются через select - сотрудник уже выбран
        $obj->eagreementid = $formdata->eagreementid;
    }
    if ( $formdata->id )
    {// назначение редактировалось - обновим запись в БД
        if ( $DOF->storage('appointments')->update($obj,$formdata->id) )
        {
            redirect($DOF->url_im('employees','/view_appointment.php?id='.$formdata->id,$addvars));
        }else
        {
            $error .= '<p style=" color:red; " align="center"><b>'.
                      $DOF->modlib('ig')->igs('record_update_failure').'</b></p>';
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
            $error .= '<p style=" color:red; " align="center"><b>'.
                      $DOF->modlib('ig')->igs('record_insert_failure').'</b></p>';
        }
    }

}

?>