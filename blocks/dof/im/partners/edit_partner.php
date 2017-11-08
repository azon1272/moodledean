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
 * Точка входа в плагин
 *
 * @package    im
 * @subpackage partners
 * @author     Polikarpov Alexander <polikarpovst@gmail.com>
 * @copyright  2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');


// Получение GET параметров
$depid = optional_param('depid', 0, PARAM_INT);
$personid = optional_param('personid', 0, PARAM_INT);
$code = optional_param('code', '', PARAM_TEXT);

$info = $DOF->im('partners')->get_code_info($code);
if ( empty($info) || ! empty($depid) || ! empty($personid) )
{
    // Проверка прав доступа
    $DOF->im('partners')->require_access('admnistration');
} else 
{// Получение информации по коду
    $codeinfo = array_shift($info);
    if ( $codeinfo['type'] == 'partnercode' )
    {// Код регистрации партнера
        $addvars['code'] = $code;
        $addvars['departmentid'] = $codeinfo['department']->id;
    } else
    {
        // Проверка прав доступа
        $DOF->im('partners')->require_access('admnistration');
    }
}

// Формируем объект формы
$url = $DOF->url_im('partners','/edit_partner.php', $addvars);
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->depid = $depid;
$customdata->personid = $personid;
$customdata->code = $code;
$customdata->addvars = $addvars;
$form = new dof_im_partners_edit_partner($url, $customdata);

// Обработчик формы
$form->process();
                                                        
// Печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);  

$form->errors();
$form->display();

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>