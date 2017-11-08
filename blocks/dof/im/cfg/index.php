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
$sort = optional_param('sort', 'plugintype DESC, plugincode DESC', PARAM_TEXT);

//Выведем название выбранного подразделения
$depname = '';
if ( $addvars['departmentid'] )
{// получили id подразделения - выведем название и код
    $depname = $DOF->storage('departments')->get_field($addvars['departmentid'],'name').' ['.
               $DOF->storage('departments')->get_field($addvars['departmentid'],'code').']';
}else
{// нету - значит выводим для всех
    $depname = $DOF->get_string('all_departments', 'cfg');
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

print('<p align="center"><font size="5">'.$depname.'</font>
<a href ='.$DOF->url_im('cfg','/edit.php',$addvars).'>
            	<img src="'.$DOF->url_im('cfg', '/icons/edit.png').'" 
            	 alt="'.$DOF->get_string('edit_cfg', 'cfg').'" title="'.$DOF->get_string('edit_cfg', 'cfg').'"></a>

</p>');

//Выведем таблицу настроек
$DOF->im('cfg')->show_list($DOF->storage('config')->get_config_list_by_department($depid, $sort), $addvars);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);