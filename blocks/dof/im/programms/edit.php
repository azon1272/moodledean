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
$programmid = optional_param('programmid', 0, PARAM_INT);

//проверяем доступ
if ( $programmid == 0 )
{// если id нет - программа создается
    $DOF->storage('programms')->require_access('create');
}else
{// id передано - программа редактируется
    $DOF->storage('programms')->require_access('edit',$programmid);
}


// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programms'), 
      $DOF->url_im('programms', '/list.php'),$addvars);
if ( $programmid == 0 )
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('newprogramm', 'programms'), 
          $DOF->url_im('programms','/edit.php'),$addvars);
}else
{
    $programm = $DOF->storage('programms')->get($programmid);
    if ( $programm )
    {
        $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']', 
              $DOF->url_im('programms','/view.php?programmid='.$programmid,$addvars));
    }
    $DOF->modlib('nvg')->add_level($DOF->get_string('editprogramm', 'programms'), 
          $DOF->url_im('programms','/edit.php?programmid='.$programmid,$addvars));
}
    


if ( $DOF->storage('programms')->is_exists($programmid) OR ($programmid === 0) )
{// если программа есть или id программы не передано
    // загружаем форму
    $form = $DOF->im('programms')->form($programmid);
    $error = '';
    //подключаем обработчик формы
    include($DOF->plugin_path('im','programms','/process_form.php'));
}    

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $DOF->storage('programms')->is_exists($programmid) AND $programmid != 0 )
{// если программа не ненайдена, выведем ошибку
    print_error($DOF->get_string('notfoundprogramm','programms'));
}

echo $error;
// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>