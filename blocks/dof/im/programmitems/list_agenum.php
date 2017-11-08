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
/*
 * Состав учебной программы или список метадисциплин, в зависимости от параметра meta
 */
// Подключаем библиотеки
require_once('lib.php');
// подключаем формы
require_once('form.php');

// подключение стилей
$DOF->modlib('nvg')->add_css('im', 'programmitems', '/styles.css');

$programmid = optional_param('programmid', 0, PARAM_INT);
if ($meta != 1)
{
    if ( ! $programm = $DOF->im('programms')->show_id($programmid,$addvars) )
    {// если период не найден, выведем ошибку
        print_error($DOF->get_string('notfoundprogramm','programms'));
    }
    $programm = $DOF->storage('programms')->get($programmid);
    //проверяем доступ
    $DOF->storage('programmitems')->require_access('view');
}else
{
    //проверяем доступ
    $DOF->storage('programmitems')->require_access('view/meta');
}



// ловим список условий поиска
$conds = new stdClass();
// название предмета
$conds->nameorcode         = trim(optional_param('nameorcode', null, PARAM_TEXT));
// программа
$conds->programmid = $programmid;
$conds->departmentid = $addvars['departmentid'];


$customdata = new stdClass();
// передаем объект $DOF по ссылке для быстродействия
$customdata->dof = $DOF;
$customdata->programmid = $programmid;

//метадисциплина
if ($meta == 1)
{// создаем форму

    $searchform = new dof_im_programmitems_search_form
             ($DOF->url_im('programmitems', '/list_agenum.php?programmid='.$programmid.'&meta='.$meta,$conds), $customdata);
}else
{// создаем форму
    $customdata->programmid = $programmid;

    $searchform = new dof_im_programmitems_search_form
            ($DOF->url_im('programmitems', '/list_agenum.php?programmid='.$programmid,$conds), $customdata);
}

// добавляем уровень навигации
if ($meta == 1)
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('metaprogrammitems_list', 'programmitems'),  
          $DOF->url_im('programmitems','/list_agenum.php?meta=1',$conds));
    
}else
{
    // навигация - название программы
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programmitems'), 
          $DOF->url_im('programmitems', '/list.php'),$addvars);
    $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']', 
          $DOF->url_im('programms','/view.php?programmid='.$programmid,$addvars));
    $DOF->modlib('nvg')->add_level($DOF->get_string('program_structure', 'programmitems'), 
          $DOF->url_im('programmitems','/list_agenum.php',$conds));
}

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// выводим весь список предметов в виде таблиц
if ($meta == 1)
{
    $conds->metaprogrammitemid = 0;
    $DOF->im('programmitems')->print_list_agenums($programmid,$conds);

}else
{
    $conds->metaprogrammitemid = null;
    $DOF->im('programmitems')->print_list_agenums($programmid, $conds);
}

// выводим форму на экран
$searchform->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>
