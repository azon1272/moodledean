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
 * Страница обработчика формы добавлния/удалния учеников
 */
// проверяем права
if ($DOF->storage('cpassed')->is_access('view', $cstreamid))
{
    if ( $addstudents )
    {// если была нажата кнопка которая добавляет учеников в поток
        if ( isset($_POST['addselect']) )
        {// убедимся, что пользователь выделил хотя бы одного ученика
            $programmsbcids = $addremove->check_add_remove_array($_POST['addselect']);
            // записываем всех ученико на поток
            $result = $DOF->storage('cstreams')->enrol_students_on_cstream($cstreamobj, $programmsbcids);
        }
    }elseif( $removestudents )
    {// если была нажада кнопка которая удаляет учеников из потока
        if ( isset($_POST['removeselect']) )
        {// убедимся, что пользователь выделил хотя бы одного ученика
            $programmsbcids = $addremove->check_add_remove_array($_POST['removeselect']);
            // отписываем всех учеников с потока
            $result = $DOF->storage('cstreams')->unenrol_students_from_cstream($cstreamobj, $programmsbcids);
        }
    }
}

?>