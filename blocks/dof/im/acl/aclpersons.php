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

// Подключаем библиотеки
require_once('lib.php');
// славливаем id персоны
$aclid = required_param('id', PARAM_INT);

$DOF->im('acl')->require_access('aclwarrantagents:view');
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('list_persons', 'acl'), 
                     $DOF->url_im('acl','/aclpersons.php?id='.$aclid,$addvars));
// проверка сущуствования объекта
if ( ! $DOF->storage('acl')->is_exists($aclid) )
{// если подписка на курс не найдена, выведем ошибку
    $errorlink = $DOF->url_im('acl');
    $DOF->print_error('not_found_acl', $errorlink, '', 'im', 'acl');
}
$list = $DOF->storage('acl')->get_persons_acl($aclid,$addvars['departmentid']);
//var_dump($list);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
$aclobj = $DOF->storage('acl')->get($aclid);
print '<br>';
print '<div style="text-align:center;"><b>'.$aclobj->plugintype.'/'.$aclobj->plugincode.'/'.$aclobj->code.'</b><br>
		('.$DOF->get_string($aclobj->plugintype.'_'.$aclobj->plugincode.'_'.$aclobj->code,'acl').')</div>';
if ( ! $list )
{// списка нет
    print '<div style="text-align:center;">'.$DOF->get_string('not_found_list_persons', 'acl').'</div>';
}else
{// печатаем таблицу
    print '<br>';
    print $DOF->im('acl')->get_table_persons_acl($list);
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>