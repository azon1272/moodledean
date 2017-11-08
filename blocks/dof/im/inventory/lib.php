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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Проверка прав доступа
$DOF->im('inventory')->require_access('view');

$catid = optional_param('invcategoryid', 0, PARAM_INT);
$addvars['invcategoryid'] = $catid;

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'inventory'), 
    $DOF->url_im('inventory','/index.php',$addvars)
);

// Проверка активности плагина
if ( ! $DOF->im('inventory')->is_enabled($depid) )
{// Плагин отключен
    $DOF->print_error('plugin_has_been_disabled_in_this_department','',NULL,'im','university');
}

?>