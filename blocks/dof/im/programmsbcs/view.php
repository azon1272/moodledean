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
 * отображает одну запись по ее id 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once($DOF->plugin_path('im','departments','/lib.php'));
// Подключаем формы
require_once($DOF->plugin_path('im', 'programmsbcs', '/form.php'));
$programmsbcid = required_param('programmsbcid', PARAM_INT);
//проверяем доступ
$DOF->storage('programmsbcs')->require_access('view', $programmsbcid);

// переменная для текстовых сообщений, выводимых на экран
$message = '';


$options = array();
$change_department = new dof_im_departments_change_department($DOF,'programmsbcs',$options);
//print_object($_POST);
$errors = $change_department->execute_form();
if ( $errors != 1 )
{// сработал обработчик
    if ( empty($errors) )
    {// выводим сообщение, что все хорошо
        $message = '<p style=" color:green; "><b>'.$DOF->get_string('departments_change_success', 'programmsbcs').'</b></p>';
    }else
    {// все плохо...
        $message = '<p style=" color:red; "><b>'.implode('<br>',$errors).'</b></p>';
    }
}


$programm = $DOF->storage('programms')->get($DOF->storage('programmsbcs')->get_field($programmsbcid,'programmid'));
// создаем объект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
// объявляем форму
$statusform = new dof_im_programmsbcs_changestatus_form($DOF->url_im('programmsbcs', 
                '/view.php?programmsbcid='.$programmsbcid,$addvars), $customdata);
$statusform->process();
$agenumform = new dof_im_programmsbcs_changeagenum_form($DOF->url_im('programmsbcs', 
                '/view.php?programmsbcid='.$programmsbcid,$addvars), $customdata);
// подключаем обработчик формы
require_once($DOF->plugin_path('im', 'programmsbcs', '/process_agenum_form.php'));

// добавляем данные периода
$dataobj     = new stdClass();
$dataobj->id = $programmsbcid;
// устанавливаем значения по умолчанию
$statusform->set_data($dataobj);
// устанавливаем значения по умолчанию
$agenumform->set_data($dataobj);
//вывод на экран
//добавление уровня навигации
if ( $programm )
{
    $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']',$DOF->url_im('programms','/view.php?programmid='.$programm->id,$addvars));
    $DOF->modlib('nvg')->add_level($DOF->get_string('programmsbcs', 'programmsbcs'),$DOF->url_im('programmsbcs','/view.php?programmsbcid='.$programmsbcid,$addvars));
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'),$DOF->url_im('programmsbcs'));
}
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

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

if ( ! $programmsbc = $DOF->im('programmsbcs')->show_id($programmsbcid,$addvars) )
{// если подписка на программу не найдена, выведем ошибку
    $DOF->print_error($DOF->get_string('notfound','programmsbcs', $programmsbcid));
}
//покажем ссылку на создание новой подписки
if ( $DOF->storage('programmsbcs')->is_access('create') )
{// если есть право на создание подписки
    if ( $DOF->storage('config')->get_limitobject('programmsbcs',$addvars['departmentid']) )
    {
        $link = '<a href='.$DOF->url_im('programmsbcs','/edit.php',$addvars).'>'.
            $DOF->get_string('newprogrammsbcs', 'programmsbcs').'</a>';
    }else 
    {    
        $link =  '<span style="color:silver;">'.$DOF->get_string('newprogrammsbcs', 'programmsbcs').
                 ' <br>('.$DOF->get_string('limit_message','programmsbcs').')</span>';        
    }
    echo '<br>'.$link.'<br>';
}
if ( $DOF->storage('contracts')->is_access('view') )
{// если есть право на просмотр контрактов
    $contractid = $DOF->storage('programmsbcs')->get_field($programmsbcid, 'contractid');
    $link = '<a href='.$DOF->url_im('sel','/contracts/view.php?id='.$contractid,$addvars).'>'.
            $DOF->get_string('view_contract_on_this_sbc', 'programmsbcs').'</a>';
    echo $link;
}
$link = '<a href='.$DOF->url_im('recordbook','/program.php?programmsbcid='.$programmsbcid,$addvars).'>'.
        $DOF->get_string('view_recordbook', 'programmsbcs').'</a>';
echo '<br>'.$link.'<br>';

$link = '<a href='.$DOF->url_im('cpassed','/list.php?programmsbcid='.$programmsbcid,$addvars).'>'.
        $DOF->get_string('view_cpassed', 'programmsbcs').'</a>';
echo $link.'<br>';
$link = '<a href='.$DOF->url_im('cpassed','/register.php?programmsbcid='.$programmsbcid,$addvars).'>'.
        $DOF->get_string('register_reoffset', 'cpassed').'</a>';
echo $link.'<br>';
//выводим подписку
echo '<br>'.$programmsbc;
// выводим сообщение о результате смены статуса, если оно есть
print('<div align="center">'.$message.'</div>');
echo '<form action="'.$DOF->url_im('programmsbcs',"/view.php?programmsbcid={$programmsbcid}",$addvars).'" method=POST name="change_department">';
echo '<input type="hidden" name="'.$change_department->options['prefix'].'_'.
     $change_department->options['listname'].'['.$programmsbcid.']" value="'.$programmsbcid.'"/>';
echo $change_department->get_form();
echo '</form>';

$agroupid = $DOF->storage('programmsbcs')->get_field($programmsbcid, 'agroupid');
if ( $DOF->is_access('datamanage') AND ( $DOF->storage('agroups')->get_field($agroupid, 'status') == 'plan' 
                    OR $DOF->storage('programmsbcs')->get_field($programmsbcid, 'edutype') == 'individual') )
{// если есть специальные полномочия и группа находится в статусе формируется
    // выведем форму смены параллели
    $agenumform->display();
}
if ( $DOF->workflow('programmsbcs')->is_access('changestatus') )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // показываем форму
    $statusform->display();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>