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

// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'employees'),
    $DOF->url_im('employees','/list.php', $addvars)
);

// Получение договора
$eagreementid = required_param('id', PARAM_INT);
$eagreement = $DOF->storage('eagreements')->get($eagreementid);
if ( ! $eagreement )
{// Договор не найден
    $errorlink = $DOF->url_im('employees','/list.php',$addvars);
    $DOF->print_error('eagreement_not_found', $errorlink, $eagreementid, 'im', 'employees');
}

// Проверка прав доступа
$DOF->storage('eagreements')->require_access('edit', $eagreementid);

// Формирование URL текущей страницы
$addvars['id'] = $eagreementid;
$pagelink = $DOF->url_im('employees','/edit_eagreement_two.php', $addvars);
// Добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('edit_eagreement', 'employees'),
    $pagelink
);

// Сформировать дополнительные данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->eagreementid = $eagreementid;
$customdata->addvars = $addvars;
$customdata->persons = ['personid' => $eagreement->personid];
$customdata->returnurl = $DOF->url_im('employees', '/view_eagreement.php', $addvars);
$customdata->freezepersons = [];
$customdata->personlabels = ['personid' => $DOF->get_string('employee', 'employees')];

if ( $eagreement->personid > 0 )
{// Персона указана в договоре
    if ( $DOF->storage('eagreements')->is_personel($eagreement->personid, $eagreementid, 'fdo') )
    {// Персона имет административные права или указана в других договорах
        // Запрет редактирования персоны
        $customdata->freezepersons['personid'] = [];
    }
}

// Регион для должности
$defaultdepartment = $DOF->storage('contracts')->get_field($eagreementid, 'departmentid');
$defaultregion = $DOF->storage('config')->get_config('defaultregion', 'im', 'sel', $defaultdepartment);
if ( isset($defaultregion->value) )
{
    $defaultregion = $defaultregion->value;
}else
{
    $defaultregion = 0;
}

// Формирование значений по-умолчанию для блока создания назначения на должности
$default = [];
if ( $DOF->storage('appointments')->count_list(['eagreementid' => $eagreementid]) > 1 )
{// Множество назначений на должность
    $customdata->countappoint = true;
}else
{// Назначение одно или нет
   $customdata->countappoint = false;
   $customdata->appointment = new stdClass();
   $customdata->appointment->id = 0;
   if ( $appointment = $DOF->storage('appointments')->get_record(['eagreementid' => $eagreementid]) )
   {// Формирование значений по-умолчанию
       $default['appoint'] = 1;
       $default['schpositionid'] = $appointment->schpositionid;
       $default['enumber'] = $appointment->enumber;
       $default['worktime'] = $appointment->worktime;
       $default['date'] = $appointment->date;
       $customdata->appointment = $appointment;
   }
}

// Форма доступна для редактирования
$editable = true;
if ( $eagreementid AND $eagreement->status == 'canceled' )
{// Запрет редактирования формы
    $editable = false;
}

// Создание формы с редактированием персоны по договору
$form = new dof_im_employees_eagreement_edit_form_two_page(
    $pagelink,
    $customdata,
    'post',
    '',
    ['class' => 'dof_im_employees_eagreement_edit_form_two_page'],
    $editable
);

// Заполнение значений по-умолчанию для должности
$form->set_data($default);

if ( $editable )
{// Форма доступна для редактирования
    $form->process();
}

// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Отображаем форму
$form->display();

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>