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

//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Тип отчета
$reporttype = optional_param('type', 'loadteachers', PARAM_TEXT);

switch ($reporttype)
{
    case 'loadteachers':   $pagetitle = $DOF->get_string('report_actual_load', 'journal'); break;
    case 'replacedevents': $pagetitle = $DOF->get_string('report_replacedevents', 'journal'); break;
}
$DOF->modlib('nvg')->add_level($pagetitle, $DOF->url_im('journal','/reports/index.php', $addvars+array('type'=>$reporttype)));

/** 
 * Метод, который реализует импорт данных ф формате xls,
 * используя API moodle
 * 
 * @param array $data - сложный массив в массиве
 */
function otech_doffice_xls_table($data)
{
    global $CFG;
    require_once("$CFG->libdir/excellib.class.php");
    $workbook = new MoodleExcelWorkbook("-");
    foreach ($data as $tablename=>$tabledata)
    {
            $table = $workbook->add_worksheet($tablename);
            $num_row = 0;
            $num_col = 0;
            foreach ($tabledata as $row)
            {
                    foreach ($row as $cell)
                    {
                            $table->write_string($num_row,$num_col,$cell);
                            ++$num_col;
                    }
                    $num_col = 0;
                    ++$num_row;
            }
    }
    return $workbook;
}

?>