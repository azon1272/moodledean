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

/** Параметры фильтрации */
$ageid = optional_param('ageid', 0, PARAM_INT);

/** Навигация и Доступ */
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'ages'), 
                               $DOF->url_im('ages','/list.php'),$addvars);
if ( $ageid == 0 )
{//период создается
    $DOF->storage('ages')->require_access('create');
    $message = 'newages';
}else
{//период редактируется
    $DOF->storage('ages')->require_access('edit', $ageid);
    $message = 'editage';
}
$url = $DOF->url_im('ages','/edit.php?ageid='.$ageid,$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string($message, 'ages'), $url);
if ( ! $age = $DOF->storage('ages')->get($ageid) AND $ageid != 0 )
{// если период не найден, выведем ошибку
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    $errorlink = $DOF->url_im('ages');
    $DOF->print_error('notfoundage', $errorlink, $ageid, 'im', 'ages');
}

/** Формы */
$customdata = new stdClass;
$customdata->ageid = $ageid;
$customdata->dof = $DOF;
// подключаем методы вывода формы
$form = new dof_im_ages_edit_age_form($url,$customdata);
// обрабатываем пришедшие данные (если нужно)
$form->process();
// Устанавливаем данные по умолчанию
$form->set_data($age);

/** Отображение */
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $ageid == 0 AND ! $DOF->storage('config')->get_limitobject('ages',$addvars['departmentid']) )
{// достигнут лимит - больше нельзя ничего создавать
    $link =  '<span style="color:red;">'.$DOF->get_string('limit_message','ages').'</span>';
    echo '<br>'.$link;     
}else 
{// печать формы
    $form->display();
}    

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>