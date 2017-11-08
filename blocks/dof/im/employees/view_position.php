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
require_once($DOF->plugin_path('im', 'employees', '/form.php'));
// получаем id должности, которую будем отображать
$id = required_param('id', PARAM_INT);
//проверяем доступ
$DOF->storage('positions')->require_access('view',$id);
if ( ! $position = $DOF->storage('positions')->get($id) )
{// в базе нет такой записи
    $DOF->print_error($DOF->get_string('position_not_found', 'employees', $id));
}
$message = '';
// добавляем уровень навигации - заголовок "список должностей"
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_positions', 'employees'),
    $DOF->url_im('employees','/list_positions.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('view_position', 'employees'),
    $DOF->url_im('employees','/view_position.php?id='.$id,$addvars));
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->wid = 0;
$customdata->departmentid = $addvars['departmentid'];
// ищем есть ли уже мандата на должность
$conds = new stdClass();
$conds->linkptype = 'storage';
$conds->linkpcode = 'positions';
$conds->linktype = 'record';
$conds->linkid = $id;
$conds->status = array('draft','active');
$warrant = new stdClass();
if ( $warrants = $DOF->storage('aclwarrants')->get_listing($conds) )
{// такая есть - запомним ее
    $warrant = current($warrants);
    $customdata->wid = $warrant->id;
}
// подключаем класс смены мандаты
$changerole = new dof_im_employees_change_role($DOF->url_im('employees','/view_position.php?id='.$id,$addvars), $customdata);
$changerole->set_data($warrant);
// обрабатываем форму
$message = $changerole->save_change_warrants();
if ( $message !='' )
{// если форма обработалась - обновим данные в форме
    $changerole = new dof_im_employees_change_role($DOF->url_im('employees','/view_position.php?id='.$id,$addvars), $customdata);
    $changerole->set_data($warrant);
}
// создаем и показываем форму смены статуса
$statusform = new dof_im_employees_positions_status_form($DOF->url_im('employees','/view_position.php?id='.$id,$addvars), $customdata);
$statusform->process();
$statusform->set_data($position);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатываем вкладки 1-ого и 2-ого уровня
echo $DOF->im('employees')->print_tab( array_merge($addvars, array(
        'id' => $id) ),'positions',true);

// ссылка на создание вакансии
if ( $DOF->storage('positions')->is_access('create') )
{// если есть право создавать должность или вакансию
    // покажем ссылку на создание должности
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('positions',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('employees','/edit_position.php?id=0',$addvars).'>'.
        $DOF->get_string('new_position', 'employees').'</a>';
        echo '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('new_position', 'employees').
        	' ('.$DOF->get_string('limit_message','employees').')</span>';
        echo '<br>'.$link.'<br>';
    }     

    if ( $position->status === 'active' AND  $DOF->storage('config')->get_limitobject('schpositions',$addvars['departmentid']) )
    {// если должность используется - то
        // покажем ссылку на создание вакансии
        $createschposlink = '<a href='.$DOF->url_im('employees',
                '/edit_schposition.php?id=0&positionid='.$position->id,$addvars).'>'.
        $DOF->get_string('new_schposition_on_position', 'employees').'</a>';
        echo $createschposlink;
    }
}
// покажем ссылку на просмотр вакансий по этой должности
$listlink = '<a href='.$DOF->url_im('employees','/list_schpositions.php?positionid='.$position->id,$addvars).'>'.
    $DOF->get_string('list_schpositions_for_position', 'employees').'</a>';
    echo '<br>'.$listlink.'<br><br>';

// выводим информацию по должности
$DOF->im('employees')->show_position($id,$addvars);

// отображаю форму
if ( $DOF->workflow('positions')->is_access('changestatus') )
{// Форма смены статуса отображается только в случая наличия прав
    $statusform->display();
}
if ( $DOF->storage('positions')->get_field($id,'status') != 'canceled' )
{// мы не можем привязывать мандаты и доверенности к удаленным должностям
    $changerole->display();
}


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>