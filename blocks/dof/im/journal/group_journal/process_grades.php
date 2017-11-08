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
 * Журнал предмето-класса. Обработчик данных об оценках.
 * 
 * @package    im
 * @subpackage journal
 * @author     
 * @copyright  
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключение библиотеки сабинтерфейса
require_once('libform.php');

// Получение идентификатора контрольной точки
$planid = optional_param('planid', 0, PARAM_INT);
if ( empty($planid) )
{// Тематический план не указан
    $DOF->messages->add(
        $DOF->get_string('error_grpjournal_form_savegrades_themeplan_not_set', 'journal'), 
        'error'
    );
} else 
{// Проверка на наличие указанного тематического плана в системе
    $isexist = $DOF->storage('plans')->is_exists(['id' => $planid]);
    if ( ! $isexist )
    {// Тематический план не найден
        $DOF->messages->add(
            $DOF->get_string('error_grpjournal_form_savegrades_themeplan_not_found', 'journal'),
            'error'
        );
    }
}

// Буферизация вывода @todo - требуется переписывание формы оценок
ob_start();
// Обработка данных
if ( data_submitted() AND confirm_sesskey() )
{// Данные проверены
    $checkdata = new dof_im_journal_process_gradesform($DOF, $_POST);
    if ( ! $checkdata->process_form() )
    {// Если данные не удалось сохранить по какой-либо причине
        $link = $DOF->url_im(
            'journal', 
            '/group_journal/index.php',
            array_merge(['csid' => $checkdata->csid], $addvars)
        );
        $DOF->print_error(
            'error_data_not_saved', 
            $link,
            NULL,
            'im',
            'journal'
        );
    }
}
$html = ob_get_clean();

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
print($html);
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>