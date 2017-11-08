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
 * Массовое создание учебных потоков
 */
// подключение библиотек верхнего уровня
require_once('lib.php');
// подключение форм
require_once('form.php');
//проверка прав доступа
$DOF->im('cstreams')->require_access('editcurriculum');
// получаем id группы
$agroupid = optional_param('agroupid', 0, PARAM_INT);
// получаем id группы
$sbcid = optional_param('sbcid', 0, PARAM_INT);
// получаем id группы
$pitemid = optional_param('pitemid', 0, PARAM_INT);
// получаем id периода
$ageid = optional_param('ageid', 0, PARAM_INT);

// для привязки всех обязательных дисциплин
// получим нажатие кнопки привязать все
$bindall = optional_param('bindall',0,PARAM_TEXT);
// программа
$programmid = optional_param('programmid', 0, PARAM_INT);
// параллеь
$agenum = optional_param('agenum', 0, PARAM_INT);

$pitems = $DOF->storage('programmitems')->get_pitems_list($programmid,$agenum);


if ( ! $agroupid AND ! $sbcid )
{
    $DOF->print_error($DOF->get_string('student_or_group_not_found', 'cstreams'));
}
// проверяем правильность переданных параметров
if ( ! $pitem = $DOF->storage('programmitems')->get($pitemid) AND ! $bindall )
{// не найдена переданная академическая группа
    $DOF->print_error($DOF->get_string('error_programmitem', 'cstreams'));
}
// проверяем правильность переданных параметров
if ( $agroupid AND ! $DOF->storage('agroups')->is_exists($agroupid) )
{// не найдена переданная академическая группа
    $DOF->print_error($DOF->get_string('agroup_not_found', 'cstreams'));
}
if ( $ageid AND ! $DOF->storage('ages')->is_exists($ageid) )
{// не найден переданный период
    $DOF->print_error($DOF->get_string('age_not_found', 'cstreams'));
}
if ( $sbcid AND ! $DOF->storage('programmsbcs')->is_exists($sbcid) )
{// не найден переданный период
    $DOF->print_error($DOF->get_string('sbc_not_found', 'cstreams',$sbcid));
}
// проверяем правильность статусов группы
if ( $agroupid AND ! in_array($DOF->storage('agroups')->get_field($agroupid,'status'), 
                              array('plan','active','formed')) )
{// не найдена переданная академическая группа
    $DOF->print_error($DOF->get_string('error_group_status', 'cstreams'));
}
// проверяем правильность статусов подписки
if ( $sbcid AND ! in_array($DOF->storage('programmsbcs')->get_field($sbcid,'status'), 
                           array('plan','active','application','condactive')) )
{// не найден переданный период
    $DOF->print_error($DOF->get_string('error_sbc_status', 'cstreams'));
}
// создаем объект дополнительных данных для формы
$customdata = new stdClass();
$customdata->cstream = new stdClass;

// помещаем туда соответствующие значения
$customdata->dof       = $DOF;
$customdata->cstream->agroupid  = $agroupid;
$customdata->cstream->sbcid = $sbcid;
$customdata->cstream->ageid   = $ageid;
$customdata->cstream->pitemid = $pitemid;
$customdata->cstream->programmid = $programmid;
$customdata->cstream->agenum = $agenum;
$customdata->bindall = $bindall;
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$a = new stdClass;
if ( ! $bindall )
{
    $a->item = $pitem->name; 
    $DOF->modlib('nvg')->add_level($pitem->name.'['.$pitem->code.']', $DOF->url_im('programmitems','/view.php?pitemid='.$pitemid,$addvars));
    if ( $sbcid )
    {
        $a->student = $DOF->storage('persons')->get_field($DOF->storage('contracts')->get_field(
            $DOF->storage('programmsbcs')->get_field($sbcid,'contractid'),'studentid'),'sortname');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_student', 'cstreams', $a), 
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars,(array)$customdata->cstream));
    }elseif ( $agroupid )
    {
        $a->student = $DOF->storage('agroups')->get_field($agroupid,'name');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_group', 'cstreams', $a), 
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars,(array)$customdata->cstream));
    }
}else 
{
  
    if ( $sbcid )
    {
        $a->student = $DOF->storage('persons')->get_field($DOF->storage('contracts')->get_field(
            $DOF->storage('programmsbcs')->get_field($sbcid,'contractid'),'studentid'),'sortname');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_studentall', 'cstreams', $a),
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars));
    }elseif ( $agroupid )
    {
        $a->student = $DOF->storage('agroups')->get_field($agroupid,'name');
        $DOF->modlib('nvg')->add_level($DOF->get_string('assign_groupall', 'cstreams', $a),
        $DOF->url_im('cstreams','/assign_cstream_student.php'),array_merge($addvars));
    }
}
    


// создаем объект формы
$form = new dof_im_cstreams_assign_student_form($DOF->url_im('cstreams', 
                    '/assign_cstream_student.php',array_merge($addvars,(array)$customdata->cstream)), $customdata);

// параметры передаются или вместе, или вообше не передаются
/*if ( ! $form->is_submitted() )
{// если это не просто перезагрузка формы после сообщения об ошибке
    if ( ( $agroupid AND ! $ageid ) OR ( ! $agroupid AND $ageid ) AND ! $form->is_validated() )
    {// в случае, если передан только один необходимый параметр - выводим сообщение об ошибке
        $DOF->print_error($DOF->get_string('only_one_param_specified', 'cstreams'));
    }
}*/



// подключение обработчика
$message = $form->execute_form();
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( $message != '' )
{// вывод ошибок
    print $message;
}
// вывод формы на экран
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>