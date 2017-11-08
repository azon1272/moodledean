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
require_once(dirname(realpath(__FILE__)).'/lib.php');

$path = $DOF->url_im('persons','/list.php',$addvars);
redirect($path,'',0);

//проверка прав доступа
$DOF->storage('persons')->require_access('view');

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('persons', 'persons'), 
      $DOF->url_im('persons','/index.php',$addvars));

// Выводим шапку 
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
// Выводи стандартные секции
// $DOF->modlib('nvg')->print_sections();
echo "<ul>";
echo "<li><a href=\"{$DOF->url_im('persons','/edit.php',$addvars)}\">{$DOF->get_string('createperson', 'persons')}</a></li>";
echo "<li><a href=\"{$DOF->url_im('persons','/list.php',$addvars)}\">{$DOF->get_string('listpersons', 'persons')}</a></li>";
echo "<li><a href=\"{$DOF->url_im('persons','/search.php',$addvars)}\">{$DOF->get_string('searchperson', 'persons')}</a></li>";
if ( $DOF->is_access('datamanage') )
{
    echo "<li><a href=\"{$DOF->url_im('persons','/util_email.php',$addvars)}\">{$DOF->get_string('createpersonemails', 'persons')}</a></li>";
}
echo "</ul>";
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');


?>