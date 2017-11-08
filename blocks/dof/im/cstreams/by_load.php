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
 * страница для отображения подробной информации о потоках
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
require_once('process_form_by_load.php');
// 
$default = new stdClass();
$default->search = optional_param('search', 0, PARAM_INT);
$departmentid = optional_param('departmentid', 0, PARAM_INT);

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'cstreams'), 
                     $DOF->url_im('cstreams','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('load_teachers', 'cstreams'), 
                     $DOF->url_im('cstreams','/by_load.php',array('departmentid'=>$departmentid)));

//проверяем права доступа
$DOF->storage('cstreams')->require_access('view');
//print_object($_POST);
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->departmentid = $departmentid;

// объявляем форму
$searchform = new dof_im_cstreams_search_form_by_load($DOF->url_im('cstreams', 
                '/by_load.php',array('departmentid'=>$departmentid)), $customdata);
// устанавливаем переданные данные
$searchform->set_data($default);
if ( $searchform->is_cancelled() )
{//ввод данных отменен - возвращаем на эту же страницу
    redirect($DOF->url_im('cstreams', '/by_load.php',array('departmentid'=>$departmentid)));
}else
{
    if ( $searchform->is_submitted() AND confirm_sesskey() 
                       AND $formdata = $searchform->get_data() )
    {
        // подключаем обработчик формы с параметрами из формы
        switch($formdata->search)
        {
            case 0:
                $apdepid = 0;
                $eadepid = 0;
                $cstreamdepid = 0;
                $personid = null;// изначально персона пуста
                if ( ! empty($formdata->person['id']) )
                {// персона выбрана - отразим только ее
                    $personid = $formdata->person['id'];
                }
            break;
            case 1:
                $apdepid = $departmentid;
                $eadepid = 0;
                $cstreamdepid = 0;
                $personid = 0;
            break;
            case 2:
                $apdepid = 0;
                $eadepid = $departmentid;
                $cstreamdepid = 0;
                $personid = 0;
            break;
            case 3:
                $apdepid = 0;
                $eadepid = 0;
                $cstreamdepid = $departmentid;
                $personid = 0;
            break;
        }
    }else
    {
        // подключаем обработчик формы
        $apdepid = $departmentid;
        $eadepid = 0;
        $cstreamdepid = 0;
        $personid = null;
    }
    $processform = new dof_im_cstreams_process_form_by_load($DOF,
                                                            $eadepid,
                                                            $apdepid,
                                                            $cstreamdepid,
                                                            $personid);
}
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// выводим форму
$searchform->display();
if ( isset($processform) )
{// если подключен обработчик
    //выведем справку
    print $processform->get_help();
    // выведем таблицы
    print $processform->get_teachers_load();
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>