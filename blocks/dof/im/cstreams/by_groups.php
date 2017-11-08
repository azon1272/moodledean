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
 * страница для отображения подробной информации о потоках
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
require_once('process_form_by_groups.php');


$default = new stdClass;
$programmid = optional_param('programmid', 0, PARAM_INT);
$default->ageid = optional_param('ageid', 0, PARAM_INT);
$agenum = optional_param('agenum', 1, PARAM_INT);
$default->sbcstatus = optional_param('sbcstatus', 'real', PARAM_TEXT);
$default->progdata = array($programmid, $agenum);

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('participants_cstreams', 'cstreams'), 
                     $DOF->url_im('cstreams','/by_groups.php?programmid='.$programmid.
                     '&ageid='.$default->ageid.'&agenum='.$agenum.
                     '&sbcstatus='.$default->sbcstatus),$addvars);
                     
//проверяем права доступа
$DOF->im('cstreams')->require_access('viewcurriculum');
//print_object($_POST);
$customdata = new stdClass();
$customdata->dof = $DOF;

// объявляем форму
$searchform = new dof_im_cstreams_search_form_by_groups($DOF->url_im('cstreams', 
                '/by_groups.php',$addvars), $customdata);
// устанавливаем переданные данные
$searchform->set_data($default);
if ( $searchform->is_cancelled() )
{//ввод данных отменен - возвращаем на эту же страницу
    redirect($DOF->url_im('cstreams', '/by_groups.php',$addvars));
}elseif ( $searchform->is_submitted() AND confirm_sesskey() 
                   AND $formdata = $searchform->get_data() )
{
    //print_object($formdata);
    $addvars['programmid'] = $formdata->progdata[0];
    $addvars['ageid'] = $formdata->ageid;
    $addvars['agenum'] = $formdata->progdata[1];
    $addvars['sbcstatus'] = $formdata->sbcstatus;
    // подключаем обработчик формы
    redirect($DOF->url_im('cstreams', '/by_groups.php',$addvars));
}
// подключаем обработчик формы
$processform = new dof_im_cstreams_process_form_by_groups($DOF,
                                                          $programmid,
                                                          $default->ageid,
                                                          $agenum,
                                                          $addvars['departmentid'],
                                                          $default->sbcstatus);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// выводим форму
$searchform->display();
if ( ! $programmid )
{// если id программы не передан - ничего не отображаем
    // но скажем что надо что-то выбрать 
    print '<div align=\'center\'><b>'.$DOF->get_string('choose_programm', 'cstreams').'</b></div>';
}
if ( isset($processform) AND $programmid )
{// если подключен обработчик и указана программа
    // выведем таблицы
    // с предметами 
    print $processform->get_programitems();
    // с группами
    print '<br>'.$processform->get_agroups();
    // с индивидуальными подписками
    print '<br>'.$processform->get_programmsbcs();
    // с пустыми потоками
    print '<br>'.$processform->get_cstreams();

}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>