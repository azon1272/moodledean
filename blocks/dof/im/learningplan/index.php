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

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
// Подключаем стили
$DOF->modlib('nvg')->add_css('im', 'learningplan', '/styles.css');

$type = required_param('type', PARAM_TEXT);
$typeid = 0;

// Определим, какой тип учебного плана нужно загружать
if ( $type == 'programmsbc' )
{
    $typeid = required_param('programmsbcid', PARAM_INT);
} else if ( $type == 'agroup' )
{
    $typeid = required_param('agroupid', PARAM_INT);
} else
{// Выведем ошибку - некорректный тип учебного плана
    $errorlink = $DOF->url_im('programmsbcs','/list.php',$addvars);
    $DOF->print_error('error_type', $errorlink, $type, 'im', 'learningplan');
}

// Проверяем полномочия на просмотр и редактирование информации
$DOF->storage('learningplan')->require_access('view');
$DOF->storage('learningplan')->require_access('edit');

$params = array('type' => $type, "{$type}id" => $typeid);

// Проверим существования группы или подписки
if ( ! $DOF->storage($type . 's')->is_exists($typeid) )
{
    $errorlink = $DOF->url_im($type . 's','/list.php',$addvars);
    $DOF->print_error('error_typeid', $errorlink, $type, 'im', 'learningplan');
}

// Подключаем формы
require_once($DOF->plugin_path('im', 'learningplan', '/form.php'));
// Создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->type = $type;
$customdata->typeid = $typeid;
if ( isset($addvars['departmentid']) )
{
    $customdata->departmentid = $addvars['departmentid'];
} else
{
    $customdata->departmentid = 0;
}
// Объявляем форму
$learningplan = new dof_im_learningplan_edit_form($DOF->url_im('learningplan',
                '/index.php', $addvars + $params), $customdata, 'post', '', array('id'=>'learnplan'));
$learningplan->process();
// Если мы отправили что-то с формы, обновим её
if ( $learningplan->is_submitted() AND $learningplan->is_validated() )
{
    $learningplan = new dof_im_learningplan_edit_form($DOF->url_im('learningplan',
                    '/index.php', $addvars + $params), $customdata, 'post', '', array('id'=>'learnplan'));
}

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title','learningplan'), $DOF->url_im('learningplan','/index.php', array_merge($addvars,$params)));
// Выводим шапку
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');

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

// id элемента, содержащего форму
$formcontainer = 'form-container';
// id элементов, содержащих параметры
$submitparams  = $learningplan->get_submitparams();
// Часы для пересчёта времени дисциплин
$pitemsparams  = $learningplan->get_pitemsparams();
// Параметры индивидуального учебного плана
$learnplanparams = $DOF->storage('learningplan')->get_learningplan_info($type, $typeid);
$DOF->modlib('yui')->yui_module('moodle-block_dof-ajax', 'M.block_dof.init_ajax',
        array(array('container'       => $formcontainer,
                    'ajaxurl'         => '/blocks/dof/im/learningplan/ajax.php',
                    'type'            => $type,
                    'typeid'          => $typeid,
                    'submitparams'    => $submitparams,
                    'learnplanparams' => $learnplanparams,
                    'pitemsparams'    => $pitemsparams)));
//$DOF->modlib('yui')->yui_module('moodle-block_dof-dragdrop', 'M.block_dof.init_learningplan_dragdrop',
//    array(array(
//        'container'    => $formcontainer,
//        'courseid'     => 1,
//        'ajaxurl'      => $DOF->url_im('learningplan','/ajax.php',$addvars),
//        'listselector' => '.dragdiscipline',
////        'config' => $config,
//    )), null, true);
//$DOF->modlib('nvg')->strings_for_js(array(
//        'movecoursemodule',
//    ), 'moodle');

// Отображаем индивидуальный учебный план
ob_start();
$learningplan->display();
$lplan = ob_get_clean();
$DOF->modlib('widgets')->html_writer();
echo dof_html_writer::div($lplan, null, array('id'=>$formcontainer));
// подвал
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');
?>