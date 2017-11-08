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
 * отображает одну запись по ее id 
 */

// Подключаем библиотеки
require_once('lib.php');
$cpassedid = required_param('cpassedid', PARAM_INT);
//проверяем доступ
$DOF->storage('cpassed')->require_access('view', $cpassedid);
// для сообщений
$message = '';
// Подключаем формы
require_once($DOF->plugin_path('im', 'cpassed', '/form.php'));
// создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
// объявляем форму
$statusform = new dof_im_cpassed_changestatus_form($DOF->url_im('cpassed', 
                '/view.php?cpassedid='.$cpassedid,$addvars), $customdata);
// подключаем обработчик формы
$statusform->process();

//вывод на экран
//добавление уровня навигации
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cpassed'), 
                     $DOF->url_im('cpassed','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('cpassed', 'cpassed'),
                     $DOF->url_im('cpassed','/view.php?cpassedid='.$cpassedid,$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( ! $cpassed = $DOF->im('cpassed')->show_id($cpassedid,$addvars) )
{// если подписка на курс не найдена, выведем ошибку
	print_error($DOF->get_string('notfound','cpassed', $cpassedid));
}
//покажем ссылку на создание подписки на курс
if ( $DOF->storage('cpassed')->is_access('create') )
{// если есть право создавать подписки
// @todo к этой странице пока не обращаемся... запрящино
    //$link = '<a href='.$DOF->url_im('cpassed','/edit.php').'>'.$DOF->get_string('newcpassed', 'cpassed').'</a>';
    //echo '<br>'.$link.'<br>';
}
// выводим информацию по подписке
echo '<br>'.$cpassed;
print('<div align="center">'.$message.'</div>');
if ( $DOF->workflow('cpassed')->is_access('changestatus') )
{// если у пользователя есть полномочия вручную изменять статус - то покажем ему форму для этого
    // добавляем данные периода
    $dataobj = new stdClass();
    $dataobj->id = $cpassedid;
    // устанавливаем значения по умолчанию
    $statusform->set_data($dataobj);
    // показываем форму
    $statusform->display();
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>