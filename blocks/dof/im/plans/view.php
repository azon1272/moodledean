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
$pointid = required_param('pointid', PARAM_INT);
//проверяем доступ
$DOF->storage('plans')->require_access('view', $pointid);
if ( ! $point = $DOF->im('plans')->show_id($pointid,$addvars) )
{// если период не найден, выведем ошибку
	print_error($DOF->get_string('notfound','plans', $pointid));
}
$pointobj = $DOF->storage('plans')->get($pointid);
$linktype = $pointobj->linktype;
$linkid   = $pointobj->linkid;
//вывод на экран
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
        $DOF->url_im('plans','/list.php',$addvars));
//добавление уровня навигации для ВСЕХ КТ(программы, периоды, дисциплины)
$DOF->im('plans')->nvg($linktype, $linkid,$addvars);

$DOF->modlib('nvg')->add_level($DOF->get_string('point', 'plans'),
         $DOF->url_im('plans','view.php?pointid='.$pointid,$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//покажем ссылку на создание новой КТ
if ( $DOF->storage('plans')->is_access('create') )
{// если есть право на создание КТ
    $link = '<a href='.$DOF->url_im('plans','/edit.php?linkid='.$pointobj->linkid.'&linktype='.$pointobj->linktype,$addvars).'>'.
        $DOF->get_string('newpoint', 'plans').'</a>';
    echo '<br>'.$link.'<br>';
}
// Выводим информацию по контролькой точке
echo '<br>'.$point;

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>
