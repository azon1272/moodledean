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
// Получаем mdluser id
$mdluser = required_param('mdluser',PARAM_INT);
// Доступно только менеджерам по продажам или кому можно видеть все
$DOF->require_access('datamanage');
$DOF->modlib('nvg')->add_level($DOF->get_string('listpersons', 'persons'),
      $DOF->url_im('persons','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('createpersonemails', 'persons'), 
      $DOF->url_im('persons','/util_mdluser.php'),$addvars);

// Пробуем найти персону среди уже зарегистрированных
if (!$personid = $DOF->storage('persons')->get_by_moodleid_id($mdluser))
{
	// Пробуем найти пользователя Moodle
	if ($objmdluser = $DOF->sync('personstom')->get_mdluser($mdluser))
	{
		// Регистрируем пользователя, как персону
		if (!$personid = $DOF->storage('persons')->reg_moodleuser($objmdluser))
		{
			$DOF->print_error("Registred user isn't founded");
		}
	}else
	{
		$DOF->print_error("Account is not registered");
	}
}

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// Отображаем форму
$DOF->im('persons')->show_person($personid);

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>