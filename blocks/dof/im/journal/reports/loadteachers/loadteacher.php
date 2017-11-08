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
require_once('../lib.php');

$id = required_param('id', PARAM_INT);
$appointid = required_param('appointid', PARAM_INT);
$begindate = required_param('begindate', PARAM_INT);
$enddate = required_param('enddate', PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('load_teacher', 'journal'), 
        $DOF->url_im('journal','/loadteacher.php',$addvars + array('id' => $id, 
                'begindate' => $begindate, 'enddate' => $enddate)));

//проверяем полномочия на просмотр информации
if ( ! $DOF->storage('reports')->is_access('view_report_im_journal_loadteachers',$id) )
{
    $DOF->storage('reports')->require_access('view_report',$id);
}
// проверка на правильность данных
$report = $DOF->storage('reports')->get($id);
// загружаем метод работы с отчетом
$report = $DOF->im('journal')->report($report->code, $id);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// отображаем отчет
$report->dof_im_journal_get_loadteacher($appointid,$begindate,$enddate);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>