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
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
$programmsbcsid = optional_param('programmsbcid', 0, PARAM_INT);
$contractid     = optional_param('contractid', 0, PARAM_INT);

//проверяем доступ
if ( $programmsbcsid )
{//проверка права редактировать подписку на курс
    $DOF->storage('programmsbcs')->require_access('edit', $programmsbcsid);
}else
{//проверка права создавать подписку на курс
    $DOF->storage('programmsbcs')->require_access('create');
}

if ( $DOF->storage('programmsbcs')->is_exists($programmsbcsid) OR ($programmsbcsid === 0) )
{
    // загружаем форму
    $form = $DOF->im('programmsbcs')->form($programmsbcsid, $contractid);    
    $error = '';
    //подключаем обработчик формы
    include($DOF->plugin_path('im','programmsbcs','/process_form.php'));
}


if ( $programmsbcsid == 0 )
{//добавляем уровень навигации для создания  подписки на курс 
    $DOF->modlib('nvg')->add_level($DOF->get_string('newprogrammsbcs', 'programmsbcs'), $DOF->url_im('programmsbcs','/edit.php'),$addvars );
}elseif( $DOF->storage('programmsbcs')->is_exists($programmsbcsid) )
{//добавляем уровень навигации для редактирования  подписки на курс
    $programm = $DOF->storage('programms')->get($DOF->storage('programmsbcs')->get_field($programmsbcsid,'programmid'));
    $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']',$DOF->url_im('programms','/view.php?programmid='.$programm->id,$addvars));
    $DOF->modlib('nvg')->add_level($DOF->get_string('editprogrammsbcs', 'programmsbcs'), $DOF->url_im('programmsbcs','/edit.php?programmsbcsid='.$programmsbcsid,$addvars ));
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'),$DOF->url_im('programmsbcs'));
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $DOF->storage('programmsbcs')->is_exists($programmsbcsid) AND $programmsbcsid != 0)
{// если поток не найден, выведем ошибку
    print_error($DOF->get_string('notfoundprogrammsbcs','programmsbcs'));
}

//вывод сообщений об ошибках из обработчика
echo $error;

// Добавление ссылки назад
$returnurl = optional_param('returnurl', null, PARAM_URL);
if ( $returnurl )
{
    $link = dof_html_writer::link(
        $returnurl,
        $DOF->get_string('back', 'learningplan'),
        ['class' => 'btn btn-primary']
    );
    // Ссылка на создание подраделения
    echo html_writer::div($link);
}

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>