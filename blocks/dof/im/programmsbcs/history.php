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
 * Отображение истории оучения группы
 */

// Подключаем библиотеки
require_once('lib.php');
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// выводятся классы с любым programmid, если ничего не передано
$sbcid = required_param('sbcid', PARAM_INT);

//проверяем доступ
$DOF->storage('agroups')->require_access('view');


//добавление уровня навигации
// TODO раньше тут чтояло $conds
$DOF->modlib('nvg')->add_level($DOF->get_string('history_programmsbc', 'programmsbcs'), 
                     $DOF->url_im('programmsbcs','/history.php?sbcid='.$sbcid,$addvars));
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Добавление ссылки назад
$returnurl = optional_param('returnurl', null, PARAM_URL);
if ( $returnurl )
{
    $link = dof_html_writer::link(
        $returnurl,
        $DOF->get_string('back', 'programmsbcs'),
        ['class' => 'btn btn-primary']
        );
    // Ссылка на создание подраделения
    echo html_writer::div($link);
}

$contractid = $DOF->storage('programmsbcs')->get_field($sbcid,'contractid');
if ( ! $contract = $DOF->storage('contracts')->get($contractid) )
{//номера контракта нет - выведем пустую строчку
    $contractnum = '&nbsp;';
}elseif ( ! $studentname = $DOF->storage('persons')->get_fullname($contract->studentid) )
{//ученик не указан - выведем просто номер контракта
    $contractnum = $contract->num;
}else
{// выведем номер контракта с именем ученика
    $contractnum = $contract->num.' ['.$studentname.']';
}

$programmid = $DOF->storage('programmsbcs')->get_field($sbcid,'programmid');
if ( ! $programmname = $DOF->storage('programms')->get_field($programmid, 'name') )
{//программа не указана - выведем пустую строчку
    $programmname = '&nbsp;';
}
$a = new stdClass();
$a->student = '&nbsp;'.$studentname;
$a->programm = '&nbsp;&quot;'.$programmname.'&quot;';

echo $DOF->modlib('widgets')->print_heading(
            $DOF->get_string('history_programmsbc_with_name', 'programmsbcs', $a),'center', 2, 'main', true);

// получаем html-код таблицы с группами
$agroups = $DOF->im('programmsbcs')->print_table_history($sbcid);
    

// выводим таблицу с учебными группами
echo '<br>'.$agroups;
    

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);


?>