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
/**
 * Обработчик формы создания/редактирования учебного потока
 */

//проверяем доступ
if ( $programmid == 0 )
{// если id нет - программа создается
    $DOF->storage('programms')->require_access('create');
}else
{// id передано - программа редактируется
    $DOF->storage('programms')->require_access('edit',$programmid);
}
if( $form->is_cancelled() )
{// если форма отменена, то отправим пользователя назад
    redirect($DOF->url_im('programms','/list.php',$addvars));
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    //print_object($formdata);
    // создаем путь на возврат
    $path = $DOF->url_im('programms','/list.php',$addvars);
    // создаем объект для сохранения в БД
    $programm = new stdClass();
    $programm->id           = $formdata->programmid;
    $programm->name         = trim($formdata->name);
    $programm->code         = trim(mb_strtolower($formdata->code,'utf-8'));
    $programm->departmentid = $formdata->department;
    $programm->about        = $formdata->about;
    $programm->notice       = $formdata->notice;
    $programm->agenums      = $formdata->agenums;
    $programm->duration     = ($formdata->timegroup['duration_days'])*3600*24;
    $programm->ahours       = (int)$formdata->timegroup['duration_academic_hours'];
    $programm->billingtext  = $formdata->billingtext;
    $programm->edulevel     = $formdata->edulevel;
    if ( isset($formdata->flowagenums) )
    {
        $programm->flowagenums  = $formdata->flowagenums;
    } else
    {
        $programm->flowagenums  = null;
    }
    
    if ( $programm->id AND ! $programm->code )
    {// если запись редактируется и код не указан - то заменим код на id
        $programm->code = 'id'.$programm->id;
    }

    if ( $formdata->programmid )
    {// программа редактируется - обновим запись в БД
        // права на редактирование проверены еще в самом начале
        if ( $DOF->storage('programms')->update($programm, $formdata->programmid) )
        {// учебная программа успешно обновлена
           redirect($DOF->url_im('programms','/view.php?programmid='.$formdata->programmid,$addvars));
        }else
        {// не удалось обновить учебную программу
            $error .= '<br>'.$DOF->get_string('errorsaveprogramm','programms').'<br>';
        }
    }else
    {// программа создается
        // сохраняем запись в БД
        if( $id = $DOF->storage('programms')->insert($programm) )
        {// все в порядке - сохраняем статус и возвращаем на страниу просмотра периода
            $DOF->workflow('programms')->init($id);
            redirect($DOF->url_im('programms','/view.php?programmid='.$id,$addvars));
        }else
        {// период выбран неверно - сообщаем об ошибке
            $error .=  '<br>'.$DOF->get_string('errorsaveprogramm','programms').'<br>';
        }
    }    
}
?>