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
$todoid = optional_param('todoid', 0, PARAM_INT);

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('todo', 'admin'), $DOF->url_im('admin','/todo/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('newtodo', 'admin'), $DOF->url_im('admin','/todo/edit.php'),$addvars);

// загружаем форму
$customdata = new stdClass();
$customdata->dof = $DOF;
$form = new dof_im_admin_edit($DOF->url_im('admin','/todo/edit.php?'),$customdata);
// путь возврата
$path = $DOF->url_im('admin','/todo/list.php');
$error = '';
// обработка формы
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра периода
    redirect($path);
}elseif( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data()  )
{
    $plugintype = $formdata->testname[0];
    $plugincode = $formdata->testname[1];
    $todocode = $formdata->todocode;
    $intvar = (int)$formdata->dopparam;
    $loan = $formdata->readysys;
    $time = $formdata->time;
    if ( $DOF->add_todo($plugintype,$plugincode,$todocode,$intvar,null,$loan,$time) )
    {// успешно добавлена записб
        redirect($path);    
    }else 
    {// произошла ошибка
        $error = $DOF->get_string('error', 'admin');
    }
}

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$form->display();
if ( $error )
{
    print('<div align="center" style="color:red;">'.$error.'</div>');
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>