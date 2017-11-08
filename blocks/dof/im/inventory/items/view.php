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
// Copyright (C) 2008-2999  Dmitriy Baranov (Дмитрий Баранов)             //
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

require_once('lib.php');

$id = required_param('id', PARAM_INT);

// проверяем, существует ли просматриваемое оборудование
if ( ! $item = $DOF->storage('invitems')->get($id) )
{
    $DOF->print_error('item_not_found', $DOF->url_im('inventory'), null, 'im', 'invitems');
}

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('view').':'.$item->name, $DOF->url_im('inventory','/items/view.php',$addvars));

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->storage('invitems')->require_access('view',$id);

// Показываем таблицу со всеми сведениями об оборудовании
$DOF->im('inventory')->display_item($id, $addvars);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>