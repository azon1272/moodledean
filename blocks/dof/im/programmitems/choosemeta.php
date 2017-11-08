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
 * предназначен для отображения списка метадисциплин и ссылок для создания дисциплин-наследников
 * Получает данные из формы, данные из метадисциплины, и заносит в базу модифицированную копию этой метадисциплины
 */

// Подключаем библиотеки
require_once('lib.php');

$programmid = required_param('programmid', PARAM_INT);
$agenum = required_param('agenum', PARAM_INT);
$metaprogrammitemid = optional_param('metaprogrammitemid', null, PARAM_TEXT);
$redirectedit = optional_param('redirectedit','',PARAM_TEXT);
$result = '';

$addvars['agenum'] = $agenum;

if ( ! $programm = $DOF->im('programms')->show_id($programmid,$addvars) )
{// если период не найден, выведем ошибку
    print_error($DOF->get_string('notfoundprogramm','programms'));
}

//проверяем доступ
$DOF->storage('programmitems')->require_access('use/meta',$metaprogrammitemid);

$programm = $DOF->storage('programms')->get($programmid);
// навигация - название программы
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programmitems'), 
      $DOF->url_im('programmitems', '/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']', 
      $DOF->url_im('programms','/view.php?programmid='.$programmid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('metaprogrammitems_list','programmitems'), 
      $DOF->url_im('programmitems','/choosemeta.php?programmid='.$programmid,$addvars));

if ($meta == 1)
{
    //получаем данные метадисциплины
    $metaprogrammitem = $DOF->storage('programmitems')->get($metaprogrammitemid);

    //модифицируем полученные данные метадисциплины для вставки в таблицу
    $metaprogrammitem->agenum = $agenum;
    $metaprogrammitem->status = 'active';

    $metaprogrammitem->notice = '';
    $metaprogrammitem->metaprogrammitemid = $metaprogrammitemid;
    $metaprogrammitem->metasyncon = 1;
    $metaprogrammitem->programmid = $programmid;//id метадисциплины
    unset($metaprogrammitem->code);// код создастся сам
    //$metaprogrammitem->scode = $metaprogrammitem->code;
    
    if ( $id = $DOF->storage('programmitems')->insert($metaprogrammitem) )
    {// дисциплина успешно создана
        $result = '<span style="color:green;"><b>'.$DOF->get_string('pitem_create_success','programmitems').'</b></span>';
    }else 
    {// успешно не создана
        $result = '<span style="color:red;"><b>'.$DOF->get_string('pitem_create_failure','programmitems').'</b></span>';
    }

    //если былла ссылка "перейти и редактировать" редирект на страницу редактирования
    if ( $redirectedit == '1' AND $id )
    {
        redirect($DOF->url_im('programmitems', '/edit.php?pitemid='.$id,$addvars));
    }
}

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// вернуться на состав
unset($addvars['meta']);
$link = '<a href='.$DOF->url_im('programmitems','/list_agenum.php?programmid='.$programmid.'&meta=0',$addvars).'>'.
        $DOF->get_string('return_on_list_programm', 'programmitems').'</a>';
echo '<br>'.$link.'<br>';
echo '<br>'.$result.'<br>';

$conds->metaprogrammitemid = 0;
$conds->departmentid = $depid;
$conds->agenum = $agenum;

$DOF->im('programmitems')->print_list_agenums($programmid, $conds, 1);

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>