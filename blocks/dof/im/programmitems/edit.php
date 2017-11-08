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

if($meta == 1)
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('metaprogrammitems_list', 'programmitems'),
            $DOF->url_im('programmitems','/list_agenum.php?meta=1'),$addvars);
}
else 
{    
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programmitems'), 
            $DOF->url_im('programmitems', '/list.php'),$addvars);   
}
// Если запись редактируетс - то укажем ее id
$pitemid   = optional_param('pitemid', 0, PARAM_INT);
// если нужно создать предмет с установленной учебной программой
$programmid = optional_param('programmid', 0, PARAM_INT);
// если нужно создать предмет с установленным учебным периодом
$agenum     = optional_param('agenum', 0, PARAM_INT);

$pitem = $DOF->storage('programmitems')->get($pitemid);

$options = array();
$options['meta'] = $meta;
$options['id'] = 0;

if ( ($programmid AND $meta !== 1) OR $agenum )
{// если указаны предустановленные параметры - то создадим объект для вставки их в форму
    if ( $programmid )
    {// добавляем предустановленный id программы
        if ( $programm = $DOF->storage('programms')->get($programmid) )
        {// если переданная программа существует - возьмем из нее id и подразделение
            $options['departmentid'] = $programm->departmentid;
            $options['programmid']   = $programm->id;
        }
    }
    if ( $agenum )
    {// добавляем предустановленный номер периода
        $options['agenum'] = $agenum;
    }
}
if ( $pitemid == 0 )
{
    if ($meta == 1)
    {
        //проверяем доступ
        $DOF->storage('programmitems')->require_access('create/meta');
        $DOF->modlib('nvg')->add_level($DOF->get_string('newmetapitem', 'programmitems'), 
              $DOF->url_im('programmitems','/edit.php?meta=1',$addvars));
    }
    else
    {
        //проверяем доступ
        $DOF->storage('programmitems')->require_access('create');
        $DOF->modlib('nvg')->add_level($DOF->get_string('newpitem', 'programmitems'), 
              $DOF->url_im('programmitems','/edit.php',$addvars));
    }
}else
{ 
    if ( $pitem )
    {
        if ( ( ! isset($programm) OR ! $programm ) AND $meta !== 1 )
        {// нет программы  -укажем её
            $programm = $DOF->storage('programms')->get($DOF->storage('programmitems')->get_field($pitemid,'programmid'));
        }
        $DOF->modlib('nvg')->add_level($pitem->name.'['.$pitem->code.']', 
              $DOF->url_im('programmitems','/view.php?pitemid='.$pitemid,$addvars));
    }

    if ($meta == 1)
    {
        //проверяем доступ
        $DOF->storage('programmitems')->require_access('edit/meta',$pitemid);
        $DOF->modlib('nvg')->add_level($DOF->get_string('editmetapitem', 'programmitems'), 
              $DOF->url_im('programmitems','/edit.php?pitemid='.$pitemid,$addvars));
    }
    else
    {
        //проверяем доступ
        $DOF->storage('programmitems')->require_access('edit',$pitemid);
        $DOF->modlib('nvg')->add_level($DOF->get_string('editpitem', 'programmitems'), 
              $DOF->url_im('programmitems','/edit.php?pitemid='.$pitemid,$addvars));
    }
}

if ( $pitem OR $pitemid === 0 OR $meta == 1)
{
    // загружаем форму
    $form = $DOF->im('programmitems')->form($pitemid, $options);
    $error = '';
    //подключаем обработчик формы
    include($DOF->plugin_path('im','programmitems','/process_form.php'));
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $DOF->storage('programmitems')->is_exists($pitemid) AND $pitemid != 0 )
{// если предмет не ненайден, выведем ошибку
    print_error($DOF->get_string('notfoundpitem','programmitems'));
}


echo $error;
if ( $pitem )
{// (для существующих) вернуться на состав программы
    echo '<br/><a href='.$DOF->url_im('programmitems','/list_agenum.php?programmid='.$pitem->programmid,$addvars).'>'.
        $DOF->get_string('return_on_list_programm', 'programmitems').'</a>';
}elseif ( $programmid )
{// (для создающихся под конкретную программу дисциплин) вернуться на состав программы
    echo '<br/><a href='.$DOF->url_im('programmitems','/list_agenum.php?programmid='.$programmid,$addvars).'>'.
        $DOF->get_string('return_on_list_programm', 'programmitems').'</a>';
}

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>