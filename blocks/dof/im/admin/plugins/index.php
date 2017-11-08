<?PHP
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
//проверка прав доступа сделана в lib.php

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
$type = optional_param('type', 'all', PARAM_ALPHA);
//вывод модулей таблицей
/*switch ($type)
{
	case 'storage': print print_plugins('storage');break;//печатаем плагины типы storage
	case 'im': print print_plugins('im');break;//печатаем плагины типа im
	case 'workflow': print print_plugins('workflow');break;//печатаем плагины типа workflow
	case 'sync': print print_plugins('sync');break;//печатаем плагины типа sync
	case 'modlib': print print_plugins('modlib');break;//печатаем плагины типа modlib
	default:
	{
		print print_plugins('storage');//печатаем плагины типы storage
		print print_plugins('im');//печатаем плагины типа im
		print print_plugins('workflow');//печатаем плагины типа workflow
		print print_plugins('sync');//печатаем плагины типа sync
		print print_plugins('modlib');//печатаем плагины типа modlib
	}
}
*/

//вывод плагинов в секции
$sections = array();
switch ($type)
{
	case 'storage': $sections[] = array('im'=>'admin','name'=>'plugins','id'=>1,'title'=>$DOF->get_string('storages', 'admin', null));break;
	case 'im': $sections[] = array('im'=>'admin','name'=>'plugins','id'=>2, 'title'=>$DOF->get_string('ims', 'admin', null));break;
	case 'workflow': $sections[] = array('im'=>'admin','name'=>'plugins','id'=>3, 'title'=>$DOF->get_string('workflows', 'admin', null));break;
	case 'sync': $sections[] = array('im'=>'admin','name'=>'plugins','id'=>4, 'title'=>$DOF->get_string('syncs', 'admin', null));break;
	case 'modlib': $sections[] = array('im'=>'admin','name'=>'plugins','id'=>5, 'title'=>$DOF->get_string('modlibs', 'admin', null));break;
	default:
	{
	    // Выводим секции по умолчанию
		$sections[] = array('im'=>'admin','name'=>'plugins','id'=>1,'title'=>$DOF->get_string('storages', 'admin', null));
	}
}
$DOF->modlib('nvg')->print_sections($sections);


$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');
function print_plugins($type)
{
	global $DOF;
	$plugins = $DOF->plugin_list_dir($type);//получили список всех плагинов
	if ( ! empty($plugins) )
	{
		return plugin_table($plugins, false);
	}
	return false;
}
?>