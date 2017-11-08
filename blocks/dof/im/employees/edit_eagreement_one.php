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
require_once('form.php');
// получаем id должности, которую будем редактировать
$id = required_param('id', PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_appointeagreement', 'employees'),
    $DOF->url_im('employees','/list_appointeagreements.php',$addvars));
    
$error = '';
$errordischarge='';
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->edit_person = true;
$customdata->personid = 0;
    
if ( ! $id )
{// создаем новую должность
    //проверяем доступ
    $DOF->storage('eagreements')->require_access('create');
    $eagreement = new stdClass();
    $eagreement->departmentid = $addvars['departmentid'];
    // добавляем уровень навигации - заголовок "создание должности"
    $DOF->modlib('nvg')->add_level($DOF->get_string('new_eagreement', 'employees'),
        $DOF->url_im('employees','/edit_eagreement_one.php?id='.$id,$addvars));
}else
{// редактируем существующую должность
    //проверяем доступ
    $DOF->storage('eagreements')->require_access('edit',$id);
    // добавляем уровень навигации - заголовок "редактирование должности"
    $DOF->modlib('nvg')->add_level($DOF->get_string('edit_eagreement', 'employees'),
        $DOF->url_im('employees','/edit_eagreement_one.php?id='.$id,$addvars));
    if( ! $eagreement = $DOF->storage('eagreements')->get($id) )
    {// в базе нет такой записи
        $DOF->print_error($DOF->get_string('appointment_not_found', 'employees', $id));
    }
}
$eagreement->person = 'new';
//@todo - включить в defenition_after_data
if ( $id )
{// если контракт редактируется
    if ( $DOF->storage('appointments')->is_exists(array('eagreementid'=>$id)) )
    {// если у персоны есть должностные назначения, сменить её уже нельзя
        $customdata->edit_person = false;
        $customdata->personid = $eagreement->personid;
    }
    if ( $eagreement->personid <> 0 )
    {// если id студента указанный в контракте не равен 0
        // установим что это пользователь деканата 
        $eagreement->personid = $eagreement->personid;
        $eagreement->person = 'personid';
    }
}

if ( $id AND $eagreement->status == 'canceled' )
{// удаленный договор нельзя редактировать
    $form = new dof_im_employees_eagreement_edit_form_one_page(
        $DOF->url_im('employees', '/edit_eagreement_one.php?id='.$id,$addvars), $customdata, 'post', null, null, false);
    // устанавливаем данные по умолчанию
    $form->set_data($eagreement);
}else
{// остальные договоры редактировать можно
    // создаем объект формы
    $form = new dof_im_employees_eagreement_edit_form_one_page(
        $DOF->url_im('employees', '/edit_eagreement_one.php?id='.$id,$addvars), $customdata);
    // устанавливаем данные по умолчанию
    $form->set_data($eagreement);
    $error = $form->process();
}




//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
print '<br>'.$error.'<br>';
// отображаем форму
$form->display();
if ( $id AND $DOF->storage('eagreements')->get_field($id, 'status') != 'canceled')
{// если сотрудник указан и еще не уволен - выведем форму увольнения
    print '<br>'.$errordischarge.'<br>';
    //$dischargeform->display();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>