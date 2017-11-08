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
// id контролькой точки, если она редактируктся
$pointid  = optional_param('pointid', 0, PARAM_INT);
if ( $pointid )
{// редактируем существующую КТ
    if  ( ! $pointobj = $DOF->storage('plans')->get($pointid) )
    {// нет такого объекта в базе
        $DOF->print_error($DOF->get_string('notfound', 'plans', $pointid));
    }
    // установим тип связи и id привязки - они нам понадобятся для ссылок
    $linktype = $pointobj->linktype;
    $linkid   = $pointobj->linkid;
}else
{// создаем новую КТ
    // тип связи, если мы создаем КТ с заданными параметрами 
    $linktype = required_param('linktype', PARAM_ALPHA);
    // id элемента с которым связывается КТ
    $linkid   = required_param('linkid', PARAM_INT);
}
//проверяем доступ
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{// нету права редактировать свое планирование - проверим, есть ли право редактировать его вообще
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid, null, $linktype);
}

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
        $DOF->url_im('plans','/list.php',$addvars));

//вывод на экран
//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)
$DOF->im('plans')->nvg($linktype, $linkid,$addvars);

$var = array();
$var['linktype'] = $linktype;
$var['linkid'] = $linkid; 
$var['departmentid'] = $addvars['departmentid'];
if ( $pointid === 0 )
{// КТ создается
    // создаем значения по умолчанию, если нужно
    $defaults = new stdClass();
    if ( $linktype )
    {// тип связи по умолчанию
        $defaults->linktype = $linktype;
    }
    if ( $linkid )
    {// id привязки по умолчанию
        $defaults->linkid = $linkid;
    }
    $DOF->modlib('nvg')->add_level($DOF->get_string('newpoint', 'plans'), $DOF->url_im('plans','/edit.php',$var));
    // загружаем форму создания со значениями по умолчанию
    $form = $DOF->im('plans')->form($pointid, $defaults);
}else
{// КТ редактируется
    $DOF->modlib('nvg')->add_level($DOF->get_string('editpoint', 'plans'), $DOF->url_im('plans','/edit.php',$var));
    // загружаем форму создания со значениями по умолчанию
    $form = $DOF->im('plans')->form($pointid);
}  

$error = '';
//подключаем обработчик формы
include($DOF->plugin_path('im','plans','/process_form.php'));
//вывод на экран

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $error;

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>
