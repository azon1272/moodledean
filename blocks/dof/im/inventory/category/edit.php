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

// параметры
$id = optional_param('id', 0, PARAM_INT);
$error = optional_param('error', 0, PARAM_INT);
$depid = $addvars['departmentid'];


// добавлякем уровень навигации
if ( $id == 0 )
{
    // права, передаем подразделение, чтоб не использовать в коде optional_param
    $DOF->storage('invcategories')->require_access('create', NULL, NULL, $depid);
    // навигация
    $DOF->modlib('nvg')->add_level($DOF->get_string('cat_create','inventory'), $DOF->url_im('inventory','/category/edit.php'), $addvars);
}else
{
    // права, передаем подразделение, чтоб не использовать в коде optional_param
    $DOF->storage('invcategories')->require_access('edit', $id, NULL, $depid); 
    // навигация
    $DOF->modlib('nvg')->add_level($DOF->get_string('cat_edit','inventory'), $DOF->url_im('inventory','/category/edit.php'), $addvars);
}


if ( $id AND ! $obj = $DOF->storage('invcategories')->get($id) )
{// если подразделение не ненайдено, выведем ошибку
    print_error($DOF->modlib('ig')->igs('form_err_is_exist_element'));
}

// формируем объект для формы
$customdata = new stdClass();
$customdata->id = $id;
$customdata->dof = $DOF;
$customdata->depid = $depid;
// объявим форму
$form = new dof_im_inventory_category_edit(null, $customdata);
// обработчик
$form->process($addvars);
// внесем значения по умолчанию
if ( $id )
{
    $form->set_data($obj);
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// права
if ( $id )
{
    $DOF->storage('invcategories')->require_access('edit',$id);
}else 
{
    $DOF->storage('invcategories')->require_access('create',NULL,NULL,$addvars['departmentid']);
}
    
// обработка ошибки - вывод
if ( $error )
{
    echo '</br>';
    print($DOF->modlib('widgets')->error_message($DOF->get_string('error','inventory')));
}

// печать формы
$form->display();


//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>