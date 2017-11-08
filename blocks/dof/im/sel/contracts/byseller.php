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
// Доступно только менеджерам по продажам или кому можно видеть все
$DOF->im('sel')->require_access('openaccount');
if (!$seller = $DOF->storage('persons')->get_bu())
{
	$DOF->print_error("You account is not registered");
}

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('mycontracts', 'sel'), $DOF->url_im('sel','/contracts/byseller.php'),$addvars);

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
echo '<br /><br />';
$list = $obj = $DOF->storage('contracts')->get_list_by_seller();
imseq_show_contracts($list);

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>