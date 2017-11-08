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
$status = optional_param('status', '', PARAM_TEXT);
if (!$obj = $DOF->storage('contracts')->get(required_param('id', PARAM_INT)))
{
	$DOF->print_error("Object not found");
}
// Проверяем полномочия
$DOF->im('sel')->require_access('setstatus:'.$status, $obj->id);


// Проверяем, имеет ли право пользователь устанавливать этот статус
if ($status==='wesign' OR $status==='archives')
{	// Можно менеджерам счетов
	$DOF->im('sel')->require_access('manageaccount');
}
if ($status==='frozen' OR $status==='work')
{	// Можно операторам, вводящим списки оплаты
	$DOF->im('sel')->require_access('payaccount');
}
// Опции смены статуса
$opt = array();
if (optional_param('muserkeep', false, PARAM_BOOL))
{
	$opt['muserkeep'] = true;
}

if ($DOF->workflow('contracts')->change($obj->id,$status,$opt))
{
	redirect($DOF->url_im('sel',"/contracts/view.php?id={$obj->id}",$addvars), '', 0);
}else
{
	error('Error status change');
}
?>