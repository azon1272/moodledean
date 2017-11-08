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
$id = required_param('id', PARAM_INT);
// добавляем уровень навигации - заголовок "список должностей"
$DOF->modlib('nvg')->add_level($DOF->get_string('list_positions', 'employees'),
    $DOF->url_im('employees','/list_positions.php',$addvars));
if ( ! $id )
{//проверяем доступ
    $DOF->storage('positions')->require_access('create');
}else
{//проверяем доступ
    $DOF->storage('positions')->require_access('edit',$id);
}
// @todo добавить изменение статусов через приказы
if ( $form->is_submitted() AND $form->is_validated() AND $formdata = $form->get_data() )
{// если данные формы отправлены и прошли все проверки
    $defaultmessage = '<p align="center"><b style=" color:green; ">'.
            $DOF->modlib('ig')->igs('data_save_success').'</b></p>';
    // создаем объект 
    $data = new stdClass();
    // записываем в него данные из формы
    $data->id           = $formdata->id;
    $data->name         = $formdata->name;
    $data->code         = $formdata->code;
    $data->departmentid = $formdata->departmentid;
    
    if ( $data->id )
    {// редактировалась существующая запись - обновляем таблицу
        if ( ! $DOF->storage('positions')->update($data) )
        {// не удалось обновить запись - выведем ошибку
            $DOF->print_error($DOF->modlib('ig')->igs('record_update_failure'));
        }
        $viewid = $data->id;
    }else
    {// создавалась новая запись - запишем новую строку в таблицу
        if ( ! $viewid = $DOF->storage('positions')->insert($data) )
        {// не удалось создать новую запись - выведем ошибку
            $DOF->print_error($DOF->modlib('ig')->igs('record_insert_failure'));
        }
        // после того как создали запись - запускаем workflow
        // @todo проверить результат работы этой функции когда появится механизм исключений
        $DOF->workflow('positions')->init($viewid);
    }
    
    
    if ( isset($formdata->create_schpositions_confirm) AND $formdata->create_schpositions_confirm )
    {// стоит галочка подтверждения создания вакансий
        // создадим столько вакансий, сколько указано
        $position = $DOF->storage('positions')->get($viewid);
        // с количеством часов, которое указано
        $schposition = new stdClass();
        $schposition->worktime = $formdata->number_of_hours;
        if ( ! $DOF->storage('schpositions')->
                create_schpositions($formdata->number_schpositions, $position, $schposition) )
        {// не удалось создать вакансии
            notice($defaultmessage.$DOF->get_string('schpositions_autocreation_failed', 'employees'), 
                $DOF->url_im('employees','/view_position.php?id='.$viewid,$addvars));
        }else
        {// вакансии созданы успешно, добавим сообщение об этом.
            $defaultmessage .= '<p align="center"><b style=" color:green; ">'.
                    $DOF->get_string('schpositions_created', 'employees').'</b></p>';
        }
    }
    // все прошло нормально - перенаправляем пользователя на страницу просмотра информации
    redirect($DOF->url_im('employees','/view_position.php?id='.$viewid,$addvars), $defaultmessage, 0);
}
?>