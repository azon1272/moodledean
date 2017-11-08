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

//получаем id подписки на программу для которой получается итоговая ведомость
$programmsbcid = required_param('programmsbcid', PARAM_INT);
// получаем период, за который нужно получить ведомость
$ageid         = required_param('ageid', PARAM_INT);
$cpassed        = optional_param('cpassed',0, PARAM_INT);

// @todo эти 4 проверки частично дублируются в классе dof_im_recordbook_programm_age
// @todo разобраться с проверками прав
// нужно придумать способ избежать дублирования
if ( ! $age = $DOF->storage('ages')->get($ageid) )
{// не найдена подписка на программу
    $DOF->print_error('no_age_in_base', $DOF->url_im('recordbook','',$addvars), $ageid, 'im', 'programmsbcs');
}
if ( ! $programsbc = $DOF->storage('programmsbcs')->get($programmsbcid) )
{// такой подписки на учебную программу нет в базе
    $DOF->print_error('no_program_subscribe', '', $DOF->url_im('recordbook','',$addvars), 'im', 'recordbook');
}
if ( ! $contract = $DOF->storage('contracts')->get($programsbc->contractid) )
{// такой контракт не зарегистрирован
    $DOF->print_error('contract_not_found', '', $DOF->url_im('recordbook','',$addvars), 'im', 'recordbook');
}
$fullname = '';
if ( $DOF->storage('persons')->is_exists($contract->studentid) )
{// Узнаем ФИО ученика чтобы вывести информацию по нему
    $fullname = $DOF->storage('persons')->get_fullname($contract->studentid);
}

$vars = array(  'programmsbcid' => $programmsbcid,
                'departmentid'  => $addvars['departmentid'],
                'ageid'         => $ageid );

//добавление уровня навигации, ведущего на главную страницу дневника
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'recordbook'), 
    $DOF->url_im('recordbook', '/index.php?clientid='.$contract->studentid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('finalgrades', 'recordbook'),
    $DOF->url_im('recordbook', '/finalgrades.php',$vars));

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
$DOF->modlib('widgets')->print_heading($fullname);
// содержимое самой страницы
$grade = new dof_im_recordbook_programm_age($DOF,$programmsbcid,$ageid);
print($grade->get_programm_age_table());
// покажем историю по оценке
if ( $cpassed AND $contract->studentid == $DOF->storage('cpassed')->get_field($cpassed, 'studentid'))
{
    echo '<br><br>'.$grade->show_history_cpass($cpassed, $addvars['departmentid']);    
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>