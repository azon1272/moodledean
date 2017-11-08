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

/**
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
$cpassedid = optional_param('cpassedid', 0, PARAM_INT);

//проверяем доступ
if ( $cpassedid )
{//проверка права редактировать подписку на курс
    $DOF->storage('cpassed')->require_access('edit', $cpassedid);
}else
{//проверка права создавать подписку на курс
    $DOF->storage('cpassed')->require_access('create');
}

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cpassed'), 
                     $DOF->url_im('cpassed','/list.php'),$addvars);

if ( $DOF->storage('cpassed')->is_exists($cpassedid) OR ($cpassedid === 0) )
{// если  подписка на курс есть или id периода не передано
    // загружаем форму
    $form = $DOF->im('cpassed')->form($cpassedid);
    // добавляем уровень навигации
    $DOF->modlib('nvg')->add_level($DOF->get_string('list', 'cpassed'),
                         $DOF->url_im('cpassed','/list.php'),$addvars);
    if ( $cpassedid == 0 )
    {//добавляем уровень навигации для создания  подписки на курс 
        $DOF->modlib('nvg')->add_level($DOF->get_string('newcpassed', 'cpassed'), 
                             $DOF->url_im('cpassed','/edit.php?cpassedid='.$cpassedid),$addvars);
    }else
    {//добавляем уровень навигации для редактирования  подписки на курс
        $DOF->modlib('nvg')->add_level($DOF->get_string('editcpassed', 'cpassed'), 
                             $DOF->url_im('cpassed','/edit.php?cpassedid='.$cpassedid),$addvars);
    }
}else
{// если поток не найден, выведем ошибку
    print_error($DOF->get_string('notfoundcpassed','cpassed'));
}
$error = '';
//подключаем обработчик формы
include($DOF->plugin_path('im','cpassed','/process_form.php'));
//вывод на экран

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//вывод сообщений об ошибках из обработчика
echo $error;

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>