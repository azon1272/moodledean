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
// права
$DOF->storage('schtemplates')->require_access('view');
// используется для отображение кнопки ОБНОВИТЬ
$id = optional_param('id', 0, PARAM_INT);
// создаем дополнительные данные для формы
// addvars описан в библиотеке
$customdata = new stdClass();
$customdata->dof          = $DOF;
$customdata->departmentid = $addvars['departmentid'];
$customdata->ageid        = $addvars['ageid'];

$age = $DOF->storage('ages')->get($addvars['ageid']);
//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name), 
    $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                               'ageid'=>$addvars['ageid'])) ); 
$DOF->modlib('nvg')->add_level($DOF->get_string('report', 'schedule'), 
      $DOF->url_im('schedule','/report_template.php', $addvars));

$error = '';
// Создаем объект формы
$form = new dof_im_schedule_report_template($DOF->url_im('schedule','/report_template.php',$addvars), $customdata);

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//ссылка на возвращение к рассписанию
if ( $DOF->storage('schtemplates')->is_access('view') )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('schedule','/index.php',$addvars).'>'.
    $DOF->get_string('return_on_schedule', 'schedule').'</a>';
    echo '<br>'.$link;
}

$formdata = $form->process();

// вывод формы на экран
$form->display();

if ( $formdata )
{
    $mas = array();
    $mas[] = $formdata->department;
    if ( ! $formdata->load  )
    {// показываем ПЕРЕГРЕЗ/НЕДОГРУЗ
        $a = new dof_im_schedule_master_make($DOF, $addvars['departmentid'], $mas, $addvars);
        // отобразим недогруженные/перегруженные
        echo $a->get_table_load();
    }else
    {// отображаем ПЕРЕСЕЧЕНИЕ
        $a = new dof_im_schedule_master_make($DOF, $addvars['departmentid'], $mas, $addvars);
        if ( ! $a->show_cross_templates() )
        {// нет пересечений - скажем об этом
            echo '<div align="middle" style="color:green"><b>'.$DOF->get_string('no_cross_template', 'schedule').'</b></div>';
        }
    }    

}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>