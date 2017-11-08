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
// получаем id договора, которого будем отображать
$id = required_param('id', PARAM_INT);
//проверяем доступ
$DOF->storage('eagreements')->require_access('view',$id);

if ( ! $eagreement = $DOF->storage('eagreements')->get($id) )
{// в базе нет такой записи
    $DOF->print_error('eagreement_not_found', null, $id, 'im' ,'employees');
}
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_appointeagreement', 'employees'),
    $DOF->url_im('employees','/list_appointeagreements.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('view_eagreement', 'employees'),
    $DOF->url_im('employees','/view_eagreement.php?id='.$id,$addvars));
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id  = $id;
// создаем и показываем форму смены статуса
$statusform = new dof_im_employees_eagreements_status_form($DOF->url_im('employees', '/view_eagreement.php',
    array('id' => $id) + $addvars), $customdata);
$statusform->process();
// устанавливаем в форму значения по умолчанию
$statusform->set_data($eagreement);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатываем вкладки 1-ого и 2-ого уровня
echo $DOF->im('employees')->print_tab( array_merge($addvars, array(
        'id' => $id) ),'eagreements',true);

if ( $DOF->storage('eagreements')->is_access('create') )
{
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('eagreements',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_eagreement_one.php?id=0',$addvars).'>'.
            $DOF->get_string('new_eagreement', 'employees').'</a>';
        echo '<br>'.$link;
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_eagreement', 'employees').
        	' ('.$DOF->get_string('limit_message','employees').')</span><br>';
        echo '<br>'.$link; 
    } 
}    

if ( $DOF->storage('appointments')->is_access('create') )
{
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('eagreements',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_appointment.php?id=0&eaid='.$eagreement->id,$addvars).'>'.
            $DOF->get_string('create_enumber', 'employees').'</a>';
        echo '<br>'.$link.'<br><br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('create_enumber', 'employees').
        	' ('.$DOF->get_string('limit_message','employees').')</span><br>';
        echo '<br>'.$link.'<br>'; 
    } 
} 


// выводим информацию по договору
$DOF->im('employees')->show_eagreement($id,$addvars);

if ( $DOF->workflow('eagreements')->is_access('changestatus') )
{// Форма смены статуса отображается только в случая наличия прав
    $statusform->display();
}


print $DOF->im('employees')->show_enumber_for_eagreement($id,$addvars);


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>