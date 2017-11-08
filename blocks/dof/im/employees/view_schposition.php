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
// получаем id вакансии, которую будем отображать
$id = required_param('id', PARAM_INT);
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars));
// добавляем уровень навигации - заголовок "просмотр вакансии"
$DOF->modlib('nvg')->add_level($DOF->get_string('list_schpositions', 'employees'),
    $DOF->url_im('employees','/list_schpositions.php',$addvars));
// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('view_schposition', 'employees'),
        $DOF->url_im('employees','/view_schposition.php?id='.$id,$addvars));
//проверяем доступ
$DOF->storage('schpositions')->require_access('view',$id);
if ( ! $schposition = $DOF->storage('schpositions')->get($id) )
{// в базе нет такой записи
    $DOF->print_error($DOF->get_string('schposition_not_found', 'employees', $id));
}

// создаем объект дополнительных данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id  = $id;
// создаем и показываем форму смены статуса
$statusform = new dof_im_employees_schpositions_status_form($DOF->url_im('employees','/view_schposition.php?id='.$id,$addvars), $customdata);
$statusform->process();
// устанавливаем в форму значения по умолчанию
$statusform->set_data($schposition);
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// распечатываем вкладки 1-ого и 2-ого уровня
echo $DOF->im('employees')->print_tab( array_merge($addvars, array(
        'id' => $id) ),'schpositions',true);

// ссылка на создание вакансии
if ( $DOF->storage('schpositions')->is_access('create') )
{// создание вакансии
    // проверка на лимит
    if ( $DOF->storage('config')->get_limitobject('schpositions',$addvars['departmentid']) )
    {
        echo '<br><a href="'.$DOF->url_im('employees', '/edit_schposition.php?id=0',$addvars).'">'.
            $DOF->get_string('new_schposition', 'employees').'</a><br>';
    }else 
    {
        $link =  '<br><span style="color:silver;">'.$DOF->get_string('new_schposition', 'employees').
        	' ('.$DOF->get_string('limit_message','employees').')</span><br>';
        echo $link; 
    } 

}
// добавляем ссылку на просмотр всех табельных номеров
$link = '<a href='.$DOF->url_im('employees','/list_appointeagreements.php?schpositionid='.$id,$addvars).'>'.
$DOF->get_string('appointmen_list_for_schposition', 'employees').'</a>';
echo $link.'<br/><br/>';

// отображаем информацию о вакансии в виде таблицы
$DOF->im('employees')->show_schposition($id,$addvars);

// отображаю форму
if ( $DOF->workflow('schpositions')->is_access('changestatus') )
{// Форма смены статуса отображается только в случая наличия прав
    $statusform->display();
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>