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
if ( $cpassedid )
{//проверка права редактировать подписку на курс
    $DOF->storage('cpassed')->require_access('edit', $cpassedid);
}else
{//проверка права создавать подписку на курс
    $DOF->storage('cpassed')->require_access('create');
}
// создаем путь на возврат
$path = $DOF->url_im('cpassed','/list.php',$addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра подписки на курс
    redirect($path);
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    //print_object($formdata);die;    
    // создаем объект для сохранения в БД
    $cpassed = new stdClass();
    // заполняем поля объекта значениями из формы
    if ( isset($formdata->cpdata) )
    {// если данные пришли с первой формы
        $contractid              = (int)$formdata->cpdata[0]; // id контракта (чтобы получить потом id ученика)
        $cpassed->programmsbcid  = (int)$formdata->cpdata[1]; // id подписки на программу 
        $cpassed->programmitemid = (int)$formdata->cpdata[2]; // id программы
        $cpassed->cstreamid      = (int)$formdata->cpdata[3]; // id потока (необязательно)
        $cpassed->ageid          = (int)$formdata->cpdata[4]; // id периода 
        if ( (int)$formdata->cpdata[5] != 0 )
        {// если группа указана - добавим id группы
            $cpassed->agroupid = (int)$formdata->cpdata[5];   // id группы
        }else
        {// если нет - то null
            $cpassed->agroupid = null;
        }
    }elseif ( isset($formdata->pidata) )
    {// если данные пришли со второй формы
        // id контракта (чтобы получить потом id ученика)
        $contractid = $DOF->storage('programmsbcs')->get_field($formdata->pidata[0],'contractid'); 
        $cpassed->programmsbcid  = (int)$formdata->pidata[0]; // id подписки на программу 
        $cpassed->programmitemid = (int)$formdata->programmitemid; // id программы
        $cpassed->cstreamid      = (int)$formdata->cstreamid; // id потока (необязательно)
        $cpassed->ageid          = (int)$formdata->ageid; // id периода 
        if ( (int)$formdata->pidata[1] != 0 )
        {// если группа указана - добавим id группы
            $cpassed->agroupid = (int)$formdata->pidata[1];   // id группы
        }else
        {// если нет - то null
            $cpassed->agroupid = null;
        }
    }
    // узнаем id ученика
    $cpassed->studentid      = $DOF->storage('contracts')->get_field($contractid, 'studentid');
    
    if (isset($formdata->cpassedid) AND $formdata->cpassedid )
    {// подписка на курс редактировалась - обновим запись в БД
        if ( $DOF->storage('cpassed')->update($cpassed, $formdata->cpassedid) )
        {// редактирование прошло успешно
            redirect($DOF->url_im('cpassed','/view.php?cpassedid='.$formdata->cpassedid,$addvars));
        }else
        {// не удалось произвести редактирование - выводим ошибку
            $error .= '<br>'.$DOF->get_string('errorsavecpassed','cpassed').'<br>';
        }
    }else
    {// подписка на курс создавалась
        // устанавливаем статус "запланирован"
        $cpassed->status = 'plan';
        if ( $cpassed->cstreamid AND 
             ( $cpid = $DOF->storage('cpassed')->
               is_already_enroled($cpassed->studentid, $cpassed->cstreamid,array('canceled')) ) )
        {// если ученик уже подписан на такой поток - покажем пользователю ссылку  
            // на страницу просмотра этого потока
            if ( isset($formdata->cpdata) )
            {
                $error .=  '<br/>'.$DOF->get_string('student_already_enroled','cpassed').' '.
                '<a href="'.
                $DOF->url_im('cpassed','/edit.php?cpassedid='.$cpid,$addvars).'">'.
                $DOF->get_string('edit', 'cpassed').
                '</a><br/>';
            }elseif ( isset($formdata->pidata) )
            {
                $error .=  '<br/>'.$DOF->get_string('student_already_enroled','cpassed').' '.
                '<a href="'.
                $DOF->url_im('cpassed','/edit_pitem.php?cpassedid='.$cpid,$addvars).'">'.
                $DOF->get_string('edit', 'cpassed').
                '</a><br/>';
            }
        }else
        {// сохраняем запись в БД
            if( $id = $DOF->storage('cpassed')->insert($cpassed) )
            {// все в порядке - сохраняем статус и возвращаем на страниу просмотра подписки
                $DOF->workflow('cpassed')->init($id);
                redirect($DOF->url_im('cpassed','/view.php?cpassedid='.$id,$addvars));
            }else
            {// подписка на курс выбрана неверно - сообщаем об ошибке
                $error .=  '<br>'.$DOF->get_string('errorsavecpassed','cpassed').'<br>';
            }
        }
    }
}
?>