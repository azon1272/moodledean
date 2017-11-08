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
// Подключаем формы
require_once($DOF->plugin_path('im', 'programmitems', '/form.php'));
$pitemid = required_param('pitemid', PARAM_INT);
$sesskey = optional_param('sesskey', sesskey(), PARAM_ALPHANUM);


// навигация - список дисциплин или метадисциплин
if ($meta !== 1)
{
    //проверяем доступ
    $DOF->storage('programmitems')->require_access('view', $pitemid);
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programmitems'), 
          $DOF->url_im('programmitems', '/list.php'),$addvars);
}
else
{
    //проверяем доступ
    $DOF->storage('programmitems')->require_access('view/meta', $pitemid);
    $DOF->modlib('nvg')->add_level($DOF->get_string('metaprogrammitems_list', 'programmitems'),  
          $DOF->url_im('programmitems','/list_agenum.php'),$addvars);
}

$pitem = $DOF->storage('programmitems')->get($pitemid); 

if ( $pitem )
{
    // имя программы
    $progname = $DOF->storage('programms')->get_field($pitem->programmid, 'name');
    // код программы
    $progcode = $DOF->storage('programms')->get_field($pitem->programmid, 'code');
    // навигация - название программы
    if ($meta !== 1)
    {
        $DOF->modlib('nvg')->add_level($progname.'['.$progcode.']', 
              $DOF->url_im('programms','/view.php?programmid='.$pitem->programmid,$addvars));
    }
    // навигация - название дисциплины
    $DOF->modlib('nvg')->add_level($pitem->name.'['.$pitem->code.']',
          $DOF->url_im('programmitems','/view.php?pitemid='.$pitemid,$addvars));
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'), $DOF->url_im('programmitems'));
}

// переменная для текстовых сообщений, выводимых на экран
$message = '';
// создаем объект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id  = $pitemid;
// объявляем форму смены статуса
$statusform = new dof_im_programmitems_changestatus_form($DOF->url_im('programmitems', 
                '/view.php?pitemid='.$pitemid,$addvars), $customdata);

$statusform->process();

if ($meta !== 1)
{
    // формы смены курса moodle для данной дисциплины
    $changecourse = new dof_im_programmitems_change_course_form($DOF->url_im('programmitems',
            '/view.php?pitemid='.$pitemid, $addvars), $customdata);
    $changecourse->process();
    $changecourse = new dof_im_programmitems_change_course_form($DOF->url_im('programmitems',
            '/view.php?pitemid='.$pitemid, $addvars), $customdata);
}


// добавляем данные периода
$dataobj     = new stdClass();
$dataobj->id = $pitemid;
// устанавливаем значения по умолчанию
$statusform->set_data($dataobj);

//вывод на экран

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);


if ( ! $pitemhtml = $DOF->im('programmitems')->show_id($pitemid,$addvars) )
{// если предмет не найден, выведем ошибку
	print_error($DOF->get_string('notfoundpitem','programmitems'));
}
//Если отображаем не метадисциплину
if ($meta !== 1)
{
    // покажем ссылку на создание предмета
    if ( $DOF->storage('programmitems')->is_access('create') )
    {// если есть право на создание предмета
        // лимит
        if ( $DOF->storage('config')->get_limitobject('programmitems',$addvars['departmentid']) )
        {
            $link = '<a href='.$DOF->url_im('programmitems','/edit.php',$addvars).'>'.
                $DOF->get_string('newpitem', 'programmitems').'</a>';
        }else
        {
            $link =  '<span style="color:silver;">'.$DOF->get_string('newpitem', 'programmitems').
                ' <br>('.$DOF->get_string('limit_message','programmitems').')</span>';
        }

        echo '<br>'.$link.'<br>';
    }

    // вернуться на состав
    $link = '<a href='.$DOF->url_im('programmitems','/list_agenum.php?programmid='.$pitem->programmid,$addvars).'>'.
            $DOF->get_string('return_on_list_programm', 'programmitems').'</a>';
    echo '<br>'.$link.'<br>';
    // просмотреть список преподавателей
    $link = '<a href='.$DOF->url_im('employees','/list_teachers.php?id='.$pitem->id,$addvars).'>'.
            $DOF->get_string('teachers_list_for_pitem', 'programmitems').'</a>';
}
else
{
    if ( $DOF->storage('programmitems')->check_limit_metapitems($addvars['departmentid']))
    {
        $link = '<a href='.$DOF->url_im('programmitems','/edit.php',$addvars).'>'.
            $DOF->get_string('newmetapitem', 'programmitems').'</a>';
    }else
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('newpitem', 'programmitems').
            ' <br>('.$DOF->get_string('limit_message_metapitems','programmitems').')</span>';
    }

}
echo '<br>'.$link.'<br>';
echo '<br>'.$pitemhtml;

// выводим сообщение о результате смены статуса, если оно есть
print('<div align="center">'.$message.'</div>');
if ( $DOF->workflow('programmitems')->is_access('changestatus') )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // показываем форму
    $statusform->display();
}
if ($DOF->storage('programmitems')->is_access('edit:mdlcourse') AND $meta !== 1 )
{// если пользователь имеет полномочия, показываем форму смены курса
    $changecourse->display();
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>
