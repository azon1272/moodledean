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

echo "
<style type='text/css'>
    #implied { color: #A52A2A; }
    .implied .cell { color: #A52A2A; }
</style> ";

$personid = required_param('personid', PARAM_INT);
$date = required_param('date', PARAM_TEXT);
$show = optional_param('show','less',PARAM_TEXT);

$DOF->modlib('nvg')->add_level($DOF->get_string('view_teacher_salfactors', 'journal'), 
        $DOF->url_im('journal','/load_personal/loadpersonal.php', array('personid' => $personid,
                'date' => $date),$addvars) );
// проверим права
// timestamp полученной даты
$tmp = explode('_', $date);
$now = dof_gmgetdate(time());
if ( mktime(0,0,0,$tmp[1],1,$tmp[0]) <= mktime(0,0,0,$now['mon']-2,1,$now['year']) )
{// просмотр более чем на месяц назад
    $DOF->im('journal')->require_access('view:salfactors_history');
}elseif ( $DOF->im('journal')->is_access('view:salfactors/own') )
{// проверяем права на просмотр отчетности
    $DOF->im('journal')->require_access('view:salfactors');
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// получаем данные
$salfactors = new dof_im_journal_teacher_salfactors($DOF, $personid, $date, $addvars['departmentid']);

// выводим нагрузку учителя
print $salfactors->get_table_salfactors($show);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>