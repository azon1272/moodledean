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


// создаем путь на возврат
$path = $DOF->url_im('plans','/themeplan/editthemeplan.php',
    array('linktype'=> $linktype, 'linkid' => $linkid) + $addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра класса
    redirect($path);
}elseif ( $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    //print_object($formdata);die;    
    // создаем объект для сохранения в БД
    $section = new stdClass();
    $section->name     = trim($formdata->name);
    if ( empty($section->name) )
    {// если имя пустое - не сохраняем
        redirect($path);
    }
    $section->linktype = $linktype;
    $section->linkid   = $linkid;
    $section->status   = 'active';
    if (isset($formdata->id) AND $formdata->id )
    {// класс редактировался - обновим запись в БД
        if ( $DOF->storage('plansections')->update($section, $formdata->id) )
        {//обновление прошло успешно
            redirect($path);
        }else
        {//сообщим об ошибке обновления
            $error .= '<br>'.$DOF->get_string('errorsave','plans').'<br>';
        }
    }else
    {// класс создавался
        // сохраняем запись в БД
        if( $id = $DOF->storage('plansections')->insert($section) )
        {// все в порядке - сохраняем статус и возвращаем на страниу просмотра КТ
            //$DOF->workflow('plans')->init($id);
            redirect($path);
        }else
        {//  КТ не сохранена - сообщаем об ошибке
            $error .=  '<br>'.$DOF->get_string('errorsave','plans').'<br>';
        }
    }
}
?>