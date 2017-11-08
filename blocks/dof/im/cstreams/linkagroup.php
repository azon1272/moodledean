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
// Copyright (C) 2008-2999  Evgenij Cigancov (Евгений Цыганцов)           //
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
require_once('form.php');
$cstreamid = required_param('cstreamid', PARAM_INT);
// проверка существования потока
if ( ! $DOF->storage('cstreams')->is_exists($cstreamid) )
{// если поток не найден, выведем ошибку
    print_error($DOF->get_string('notfoundcstream','cstreams'));
}
//проверка прав доступа
$DOF->storage('cstreams')->require_access('edit', $cstreamid);

// отловим вывод сообщений
$message = optional_param('message', '', PARAM_TEXT);
// формируем массив значений по умолчанию
$default = array();
if ( $groups = $DOF->storage('agroups')->get_group_cstream($cstreamid) )
{// если у потока уже есть группы
    foreach ($groups as $group)
    {// установим им тип связи
        $link = $DOF->storage('cstreamlinks')->get_link_cstreamlink($group->id, $cstreamid);
        $default['group'.$group->id]['agroupsync'] = $link->agroupsync;
    }
}
//print_object($default);
// заносим данные для формы
$data = new stdClass();
$data->dof = $DOF;
$data->cstreamid = $cstreamid;
// подключаем форму
$agroup = new dof_im_cstreams_linkagroup(null, $data);
// устанавливаем значение по умолчанию
$agroup->set_data($default);
include($DOF->plugin_path('im','cstreams','/linkagroup_process_form.php'));
//добавление уровней навигации
$programmitemid = $DOF->storage('cstreams')->get_field($cstreamid, 'programmitemid');
$progitem = $DOF->storage('programmitems')->get($programmitemid);
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($progitem->name.'['.$progitem->code.']', 
                     $DOF->url_im('programmitems','/view.php?pitemid='.$progitem->id,$addvars));
$DOF->modlib('nvg')->add_level($DOF->storage('cstreams')->get_field($cstreamid, 'name'), 
                     $DOF->url_im('cstreams','/view.php?cstreamid='.$cstreamid,$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('editagroupslink', 'cstreams'),
                     $DOF->url_im('cstreams','/linkagroup.php?cstreamid='.$cstreamid,$addvars));
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// выведем сообщения
 echo '<br />'.str_replace(',', '<br />', $message);
// вывод формы
$agroup->display();
//$agroup->get_form_agroups();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>