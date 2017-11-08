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
$warrantid = required_param('id', PARAM_INT);
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('list_warrant_acl', 'acl'), 
                     $DOF->url_im('acl','/warrantacl.php?id='.$warrantid,$addvars));
                     
if ( ! $DOF->im('acl')->is_access('aclwarrants:view/owner',$warrantid) )
{
    $DOF->im('acl')->require_access('aclwarrants:view',$warrantid);
}
// проверка сущуствования объекта
if ( ! $warrant = $DOF->storage('aclwarrants')->get($warrantid) )
{// если подписка на курс не найдена, выведем ошибку
    $errorlink = $DOF->url_im('acl');
    $DOF->print_error('not_found_warrant', $errorlink, '', 'im', 'acl');
}
$list = $DOF->storage('acl')->get_records(array('aclwarrantid' =>$warrantid),'plugintype ASC, plugincode ASC, code ASC, objectid ASC' );
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
$link = '';
if ( $warrant->status != 'archive' )
{
    if ( $warrant->parenttype == 'ext' AND $DOF->im('acl')->is_access('acl:create') )
    {//расширять можно только синхронизируемые доверенности
        $link = '<a href='.$DOF->url_im('acl','/editacl.php?aclwarrantid='.$warrant->id,$addvars).'>'.
        $DOF->get_string('new_acl', 'acl').'</a>';
    }
    if ( $warrant->parenttype == 'sub' AND $DOF->im('acl')->is_access('aclwarrants:delegate') )
    {// редактировать можно только наследуемые доверенности
        $link .= '<br><a href='.$DOF->url_im('acl','/givewarrant.php?id='.$warrant->id.'&aclwarrantid='.$warrant->parentid,$addvars).'>'.
        $DOF->get_string('edit_subvaraant', 'acl').'</a>';
    }
}
echo '<br>'.$link.'<br>';
print '<br>';
print '<div style="text-align:center;"><b>'.$DOF->storage('aclwarrants')->get_field($warrantid,'name').
       '['.$DOF->storage('aclwarrants')->get_field($warrantid,'code').']</b></div>';

if ( ! $list )
{// списка нет
    print '<div style="text-align:center;">'.$DOF->get_string('not_found_list_acl', 'acl').'</div>';
}else
{// печатаем таблицу
    print '<br>';
    print $DOF->im('acl')->get_table_right_warrant($list);
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>