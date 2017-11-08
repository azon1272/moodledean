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
 * отображает одну запись по ее id 
 */

// Подключаем библиотеки
require_once('lib.php');
// Подключаем формы
require_once($DOF->plugin_path('im', 'programms', '/form.php'));
$programmid = required_param('programmid', PARAM_INT);

//проверяем доступ
$DOF->storage('programms')->require_access('view', $programmid);

$programm = $DOF->storage('programms')->get($programmid);
// переменная для текстовых сообщений, выводимых на экран
$message = '';
// создаем объект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
// объявляем форму
$statusform = new dof_im_programms_changestatus_form($DOF->url_im('programms', 
                '/view.php?programmid='.$programmid,$addvars), $customdata);
$statusform->process();

// добавляем данные периода
$dataobj     = new stdClass();
$dataobj->id = $programmid;
// устанавливаем значения по умолчанию
$statusform->set_data($dataobj);

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programms'), 
      $DOF->url_im('programms', '/list.php', $addvars));
if ( $programm )
{
    $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']',
          $DOF->url_im('programms','/view.php?programmid='.$programmid,$addvars));
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'), $DOF->url_im('programms'));
}

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $programm = $DOF->im('programms')->show_id($programmid,$addvars) )
{// если период не найден, выведем ошибку
	print_error($DOF->get_string('notfoundprogramm','programms'));
}
if ( $DOF->storage('programms')->is_access('create') )
{// выводим ссылку на создание учебной программы
    if ( $DOF->storage('config')->get_limitobject('programms',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('programms','/edit.php',$addvars).'>'.
            $DOF->get_string('newprogramm', 'programms').'</a>';
    }else 
    {    
        $link =  '<span style="color:silver;">'.$DOF->get_string('newprogramm', 'programms').
        	' <br>('.$DOF->get_string('limit_message','programms').')</span>';        
    }
    echo '<p align="left">'.$link.'</p>';
}
// выводим информацию о программе
echo '<br>'.$programm;

if ( $DOF->workflow('programms')->is_access('changestatus') )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // показываем форму
    $statusform->display();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>