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
/*
 * Этот файл используется для отображения списка добавления/удаления пользователей в поток
 * на странице im/cstreams/view.php
 */
//проверяем доступ, на случай если кто-то захочет обратиться сюда напрямую/
$DOF->storage('cstreams')->require_access('view', $cstreamid);

// $showtype = $viewmodeform->get_data();
// // устанавливаем в форму последнее запомненное значение в ворму режима просмотра учеников
// $viewmodeform->set_data($showtype);
// // добавляем элемент для записи учеников на поток
// $DOF->modlib('widgets')->addremove();
// //print_object($_POST);
// //@todo установить другой адрес обработчика формы, вывести обработчик в отдельный файл.
// // устанавливаем адрес обработчика формы
// $action        = $DOF->url_im('cstreams','/view.php', array_merge($addvars,array('cstreamid' => $cstreamid)));
// $addremove     = new dof_modlib_widgets_addremove($DOF, $action);
// // подключаем обработчик формы добавления/удаления
// require_once($DOF->plugin_path('im', 'cstreams', '/process_addremove_students.php'));
// // устанавливаем список тех, кто уже записан на поток
// $removelist = $DOF->im('cstreams')->get_remove_persons_list($cstreamid);
// // устанавливаем список тех, кто еще не записан на поток
// if ( is_object($showtype) )
// {// данные из формы пришли корректно
//     $addlist = $DOF->im('cstreams')->get_add_persons_list($cstreamid, $showtype->showtype);
// }else
// {// данные из формы пришли с ошибкой
//     $addlist = $DOF->im('cstreams')->get_add_persons_list($cstreamid);
// }
// // устанавливаем список тех, кто уже записан на поток
// $addremove->set_remove_list($removelist);
// if ( isset($showtype->showtype) AND $showtype->showtype == 'groups' )
// {// если данные нужно отобразить в режиме групп
//     $addremove->set_complex_add_list($addlist);
// }else
// {// нужно отобразить список в режиме просмотра отдельных пользователей
//     $addremove->set_add_list($addlist);
// }
// // лобавляем подписи к спискам добавления и удаления
// $descriptions = new stdClass();
// // выводим заголовок для элемента "добавить/удалить"
// $descriptions->title       = $DOF->get_string('enrol_students_to_cstream','cstreams');
// // заголовок для списка учеников которых можно подписать на поток
// $descriptions->addlabel    = $DOF->get_string('can_be_enroled_on_cstream','cstreams').':';
// // заголовок для учеников которые уже подписаны на поток
// $descriptions->removelabel = $DOF->get_string('already_enroled_on_cstream','cstreams').':';
// // добавляем строки в форму
// $addremove->set_default_strings($descriptions);
// $pathcpassed = $DOF->url_im('cpassed','/edit_pitem.php',array_merge($addvars,array('cstreamid'=>$cstreamid)));
// $link = "<a href=\"{$pathcpassed}\">"
//         .$DOF->get_string('manual_enrol_students_to_cstream','cstreams').'</a>';

if ( $cs = $DOF->storage('cstreams')->get($cstreamid) )
{
    $pathcpassedlist = $DOF->url_im(
        'cpassed',
        '/listeditor.php',
        array_merge($addvars, ['cstreamid' => $cs->id, 'ageid' => $cs->ageid])
    );
    $link = dof_html_writer::link(
        $pathcpassedlist,
        $DOF->get_string('view_cpassed_list_for_pitem', 'cstreams'),
        ['class' => 'btn button dof_button']
    );
    print($link);
}

// print $link;
// не выводим готовый элемент "добавить/удалить", так как заменяем его новой функцональностью
//$addremove->print_html();

// отображаем форму выбора вида отображения списка учеников
// @todo пока что используется только один режим просмотра: все ученики.
// нужно раскомментировать эту строку как  только мы разберемся с тем, как отображать группы 
//$viewmodeform->display();

?>