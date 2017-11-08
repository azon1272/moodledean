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
 * Интерфейс для "Ведомости перезачета оценок"
 * Формируется в два экрана: 
 * на первом - название учебного заведение, дата и номер предъявленного документа об образовании,
 *  специальность или квалификация по диплому, флажок "разрешить перезачет поверх изучаемых или пройденных дисциплин".
 * Далее в списке отображаются все дисциплины, входящие в учебную программу, на которую подписан студент
 *  (если флажок не установлен - отображаются только те, по которым студент еще не имеет активных или
 *  успешно-завершенных cpassed). Напротив каждой из них выпадающее меню с оценкой.
 */
// Подключаем библиотеки
require_once('lib.php');
$programmsbcid = required_param('programmsbcid', PARAM_INT);
$departmentid  = optional_param('departmentid', 0, PARAM_INT);
$isdone        = optional_param('isdone', false, PARAM_BOOL);
$reoffset      = optional_param('reoffsetpassed', false, PARAM_BOOL);
//проверяем доступ
$DOF->storage('programmsbcs')->require_access('view', $programmsbcid);
// для сообщений
$message = '';
// Подключаем формы
require_once($DOF->plugin_path('im', 'cpassed', '/form.php'));
// создаем оъект данных для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->programmsbcid = $programmsbcid;
if ( $isdone )
{
    $customdata->save_next = true;
    $customdata->reoffset_passed = $reoffset;
}

// Хлебные крошки
$programm = $DOF->storage('programms')->get($DOF->storage('programmsbcs')->get_field($programmsbcid,'programmid'));
if ( $programm )
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programmsbcs'),$DOF->url_im('programms','/list.php',$addvars));
    $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']',$DOF->url_im('programms','/view.php?programmid='.$programm->id,$addvars));
    $DOF->modlib('nvg')->add_level($DOF->get_string('programmsbcs', 'programmsbcs'),$DOF->url_im('programmsbcs','/view.php?programmsbcid='.$programmsbcid,$addvars));
    $DOF->modlib('nvg')->add_level($DOF->get_string('order_header', 'cpassed'), 
                         $DOF->url_im('cpassed','/register.php?programmsbcid='.$programmsbcid),$addvars);
}else 
{
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('error'),$DOF->url_im('programmsbcs'));
}

// Проверяем, который раз отправляем форму (1-й или 2-й)
// объявляем форму
$registerform = new dof_im_cpassed_register_reoffset_form($DOF->url_im('cpassed', 
                '/register.php?programmsbcid='.$programmsbcid.'&departmentid='.$departmentid,$addvars), $customdata);
if ( $registerform->is_submitted() )
{
    if ( $formdata = $registerform->get_data() )
    {
        if ( isset($formdata->save) )
        { // Во второй раз сохраняем форму: вызываем обработчик и показываем, удалось ли обработать приказ
            if ( $registerform->process($departmentid) )
            {
                //печать шапки страницы
                $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

                // Спрашиваем, что делать дальше: вернуться на список подписок на программу (да) или сделать ещё один приказ (нет)
                $linkno = $DOF->url_im('cpassed','/register.php?programmsbcid='.$programmsbcid.'&departmentid='.$departmentid, $addvars);
                $pbcs = $DOF->storage('programmsbcs')->get($programmsbcid, 'programmid');
                $linkyes = $DOF->url_im('programmsbcs','/view.php?programmsbcid='.$programmsbcid,$addvars);
                echo $DOF->modlib('widgets')->notice_yesno($DOF->get_string('ordersuccess', 'cpassed'), $linkyes, $linkno);
                //печать подвала
                $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
                exit;
            } else
            {
                //печать шапки страницы
                $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

                // показываем ошибку
                $errorlink = $DOF->url_im('cpassed',"/register.php?programmsbcid={$programmsbcid}&departmentid={$departmentid}");
                $DOF->print_error('orderfailed', $errorlink, '', 'im', 'cpassed');

                //печать подвала
                $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
                exit;
            }
        } else if ( isset($formdata->save_next) )
        { // В первый раз сохраняем форму
            // Параметры пересохраняем в customdata и опять скармливаем форме:
            $institution               = ( isset($formdata->institution) ) ? $formdata->institution : null;
            $customdata->institution   = ( isset($institution['institution']) ) ? trim($institution['institution']) : null;
            $customdata->institutionid = ( isset($institution['id']) ) ? $institution['id'] : null;
            if ( empty($customdata->institutionid) )
            { // Проверим, на самом ли деле такой организации нет, или пользователь просто ввёл название полностью без AJAX'а
                if ( $DOF->storage('organizations')->is_exists(array('fullname' => $customdata->institution)) )
                { // Значит, всё-таки есть
                    if ( $DOF->storage('organizations')->count_list(array('fullname' => $customdata->institution)) > 1)
                    { // Если нашли больше одной записи
                        //печать шапки страницы
                        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

                        // показываем ошибку
                        $errorlink = $DOF->url_im('cpassed',"/register.php?programmsbcid={$programmsbcid}&departmentid={$departmentid}");
                        $DOF->print_error('failedmultipleorgs', $errorlink, '', 'im', 'cpassed');

                        //печать подвала
                        $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
                        exit;
                    } else
                    { // Всё нормально
                        $org = $DOF->storage('organizations')->get_record(array('fullname' => $customdata->institution), 'id');
                        $customdata->institutionid = $org->id;
                    }
                }
            }
            $customdata->dateint       = ( isset($formdata->date) ) ? $formdata->date : null;
            $customdata->number        = ( isset($formdata->number) ) ? trim($formdata->number) : null;
            $customdata->degree        = ( isset($formdata->degree) ) ? trim($formdata->degree) : null;
            $customdata->reoffset_passed = ( isset($formdata->reoffset_passed) ) ? $formdata->reoffset_passed : null;
            $customdata->save_next     = $formdata->save_next;
            $registerform = new dof_im_cpassed_register_reoffset_form($DOF->url_im('cpassed', 
                            '/register.php?isdone=1&programmsbcid='.$programmsbcid.'&departmentid='.$departmentid,$addvars), $customdata);
        }
    }
} 
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if ( $DOF->storage('cpassed')->is_access('create') )
{// если есть право создавать подписки
    $registerform->display();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>