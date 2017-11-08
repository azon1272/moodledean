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

//проверяем доступ
if ( $programmsbcsid )
{//проверка права редактировать подписку на курс
    $DOF->storage('programmsbcs')->require_access('edit', $programmsbcsid);
}else
{//проверка права создавать подписку на курс
    $DOF->storage('programmsbcs')->require_access('create');
}
// создаем путь на возврат
$path = $DOF->url_im('programmsbcs','/list.php',$addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра подписки на курс
    redirect($path);
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    // создаем объект для сохранения в БД
    $programmsbcs = new stdClass();
    // заполняем поля объекта значениями из формы
    $programmsbcs->contractid = $formdata->contractid; // id контракта
    if ( $programmsbcsid AND $DOF->storage('programmsbcs')->get_field($programmsbcsid, 'status') <> 'application' 
                         AND ! $DOF->is_access('datamanage'))
    {// если подписка указана
        //запомним id программы - понадобится позже для проверки
        $programmid = $formdata->programmid;
        // сохраняем параллель
        $programmsbcs->agenum = $formdata->agroup[0];
        if ( isset($formdata->agroup[1]) AND ($formdata->agroup[1] <> 0) )
        {// и если указана группа - сохраняем группу
            $programmsbcs->agroupid = $formdata->agroup[1]; // id группы
        }else
        {// иначе группы нет
        	$programmsbcs->agroupid = null;
        }
    }else
    {// если подписка не указана
        // сохраняем программу
        $programmid = $programmsbcs->programmid = $formdata->prog_and_agroup[0]; // id программы
        // сохраняем параллель
        $programmsbcs->agenum = $formdata->prog_and_agroup[1];
        if ( isset($formdata->prog_and_agroup[2]) AND ($formdata->prog_and_agroup[2] <> 0) )
        {// и если указана группа - сохраняем группу
            $programmsbcs->agroupid = $formdata->prog_and_agroup[2]; // id группы
        }else
        {// иначе группы нет
        	$programmsbcs->agroupid = null;
        }
    }
    $programmsbcs->departmentid   = $formdata->departmentid;
    $programmsbcs->edutype        = $formdata->edutype; // тип обучения
    $programmsbcs->eduform        = $formdata->eduform; // форма обучения
    $programmsbcs->freeattendance = $formdata->freeattendance; // свободное посещение
    if ( $programmsbcsid == 0 OR 
         $DOF->storage('programmsbcs')->get_field($programmsbcsid, 'status') == 'application' )
    {// добавляем историю обучения
        if ( $history = $DOF->storage('learninghistory')->get_first_learning_data($programmsbcsid) )
        {
            $DOF->storage('learninghistory')->delete($history->id);
        }
        if ( $formdata->agestartid )
        {
            //имитируем cpassed 
            $cpassed = new stdClass();
            $cpassed->programmsbcid = $formdata->programmsbcid;
            $cpassed->ageid         = $formdata->agestartid;
            $cpassed->status        = 'active';
            $DOF->storage('learninghistory')->add($cpassed);
        }
    }
    if ( $programmsbcsid == 0 )
    {// дата добавления
        $programmsbcs->dateadd = time();
    }    
    $programmsbcs->datestart      = $formdata->datestart; // дата начала действия подписки в unixtime
    $programmsbcs->salfactor      = $formdata->salfactor;
    //print_object($formdata);
    // заносим данные в БД
    
    // TODO вынести эти проверки в validation() в форме
    if (isset($formdata->programmsbcid) AND $formdata->programmsbcid )
    {// подписка на курс редактировалась - обновим запись в БД';
        if ( $DOF->storage('programmsbcs')->is_programmsbc($programmsbcs->contractid,$programmid,
                            $programmsbcs->agroupid, null, $formdata->programmsbcid) )
        {// если такая подписка уже существует - обновлять нельзя
            $error .= '<br>'.$DOF->get_string('programmsbc_exists','programmsbcs').'<br>';
        }else
        {// такой подписки еще нет - можем обновлять
            if ( $DOF->storage('programmsbcs')->update($programmsbcs, $formdata->programmsbcid) )
            {// редактирование прошло успешно
                redirect($DOF->url_im('programmsbcs','/view.php?programmsbcid='.$formdata->programmsbcid,$addvars));
            }else
            {// не удалось произвести редактирование - выводим ошибку
                $error .= '<br>'.$DOF->get_string('errorsaveprogrammsbcs','programmsbcs').'<br>';
            }
        }
    }else
    {// подписка на курс создавалась
        if ( $id = $DOF->storage('programmsbcs')->is_programmsbc($programmsbcs->contractid,$programmid) )
        {// если такая подписка уже существует - сохранять нельзя
            $error .= '<br>'.$DOF->get_string('programmsbc_exists','programmsbcs').'<br>';
            $error .= '<a href='.$DOF->url_im('programmsbcs','/edit.php?programmsbcid='.$id,$addvars).
                      '>'.$DOF->get_string('go_edit_programmsbc','programmsbcs').'</a><br>';
        }else
        {// такой подписки еще нет - можем сохранять        
            // сохраняем запись в БД
            if( $id = $DOF->storage('programmsbcs')->sign($programmsbcs) )
            {// все в порядке - сохраняем статус и возвращаем на страниу просмотра подписки
                $DOF->workflow('programmsbcs')->init($id);
                redirect($DOF->url_im('programmsbcs','/view.php?programmsbcid='.$id,$addvars));
            }else
            {// подписка на курс выбрана неверно - сообщаем об ошибке
                $error .=  '<br>'.$DOF->get_string('errorsaveprogrammsbcs','programmsbcs').'<br>';
            }
        }
    }
}
?>