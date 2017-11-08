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

// Подключаем библиотеки
//require_once('lib.php');
// то, от куда мы пришли, создавая подразделение
$path = $DOF->url_im('departments','/list.php',$addvars);

if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра всех подразделений
    if ( ! $addvars['departmentid'] )
    {
        $addvars['departmentid'] = $addvars['dep'];    
    }
    $path = $DOF->url_im('departments','/list.php',$addvars);
    redirect($path);
}elseif ( $form->is_submitted() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    //print_object($formdata);die;
    // создаем объект для сохранения в БД
    $department = new stdClass();
    $addres = new stdClass();
    $department->name = trim($formdata->name);
    $department->code = trim(mb_strtolower($formdata->code,'utf-8'));
    $department->managerid = $formdata->manager;
    $department->leaddepid = $formdata->leaddepid;
    $department->zone = $formdata->zone;
    $addres->postalcode = trim($formdata->postalcode);
    $addres->country = $formdata->country[0];
    if (isset($formdata->country[1]))
    {
        $addres->region = $formdata->country[1];
    } else
    {
        $addres->region = null;
    }
    $addres->county = trim($formdata->county);
    $addres->city = trim($formdata->city);
    $addres->streetname = trim($formdata->streetname);
    $addres->streettype = $formdata->streettype;
    $addres->number = trim($formdata->number);
    $addres->gate = trim($formdata->gate);
    $addres->floor = trim($formdata->floor);
    $addres->apartment = trim($formdata->apartment);
    $addres->latitude = trim($formdata->latitude);
    $addres->longitude = trim($formdata->longitude);
    if ( $formdata->id AND ! $department->code )
    {// если запись редактируется и код не указан - то заменим код на id
        $department->code = 'id'.$formdata->id;
    }
    // @todo какой тип адреса имеет структурное подразделение?
    $addres->type = "7";
    if (isset($formdata->edit))
    {// подразделение редактировалось - обновим запись в БД
        // запрашиваем права на редактирование подразделения
        $DOF->storage('departments')->require_access('edit', $formdata->id);
        if ( $DOF->storage('addresses')->is_exists($formdata->addressid) )
        {// если адрес у подразделения существует - обновим его
            $DOF->storage('addresses')->update($addres,$formdata->addressid);
        }else
        {// не существует - добавим            
            $addrid = $DOF->storage('addresses')->insert($addres);
            $department->addressid = $addrid;
        }
        // обновляем подразделение
        if( $DOF->storage('departments')->update($department,$formdata->id) )
        {    
            redirect($DOF->url_im('departments','/view.php?departmentid='.$formdata->id));
        }else
        {// не удалось - сообщаем об ошибке
            $error .=  '<br>'.$DOF->get_string('errorcreatedepartment','ages').'<br>';
        }
        
    }else
    {// подразделение создавалось
        $DOF->storage('departments')->require_access('create', null, null, $department->leaddepid);
        // сохраняем запись в БД
        if ( $addrid = $DOF->storage('addresses')->insert($addres) )
        {// если адрес eудалось создать - добавим к подразделению
            $department->addressid = $addrid;
        }else
        {// не удалось - сообщаем об ошибке           
            $error .=  '<br>'.$DOF->get_string('errorcreatedepartment','ages').'<br>';
        }
        if( $id = $DOF->storage('departments')->insert($department) )
        {// все в порядке - возвращаем на страниу просмотра подразделения
            redirect($DOF->url_im('departments','/view.php?departmentid='.$id));
        }else
        {// не удалось - сообщаем об ошибке
            $error .=  '<br>'.$DOF->get_string('errorcreatedepartment','ages').'<br>';
        }
    }
}
