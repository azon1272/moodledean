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

$aclwarrantid = optional_param('aclwarrantid', 0, PARAM_INT);

$DOF->im('acl')->require_access('acl:create');

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('new_acl', 'acl'), 
                     $DOF->url_im('acl','/editacl.php'),$addvars);

// загружаем форму
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->aclwarrantid = $aclwarrantid;
$form = new dof_im_acl_edit_acl_form($DOF->url_im('acl','/editacl.php'),$customdata);
// путь возврата
$error = '';
// обработка формы
$form->process();

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