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
 * Страница просмотра отчета
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

// Проверка прав доступа
$DOF->im('partners')->require_access('report');

// Получение GET параметров
$depid = optional_param('departmentid', 0, PARAM_INT);
$type = optional_param('type', '', PARAM_TEXT);

// Формируем объект формы
$url = $DOF->url_im('partners','/reports/index.php', $addvars);
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->depid = $depid;
$customdata->type = $type;
$customdata->addvars = $addvars;

// Форма добавления отчета
switch ( $type )
{
    case 'admins' : 
    case 'teachers' :
    case 'students' :
        // Форма добавления отчета
        $form = new dof_im_partners_add_report_form($url, $customdata);
        
        // Обработчик формы
        $form->process();
        // Печать шапки страницы
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        // Отображение формы
        $form->display();
        break;
    default :
        // Обнуляем тип
        $type = NULL;
        // Печать шапки страницы
        $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
        break;
}

// Отобразить таблицу отчетов
$plugintype = 'im';
$plugincode = 'partners';
$options['addvars'] = $addvars;
$options['limitfrom'] = 0;
$options['limitnum'] = 0;
$options['csvexport'] = true;
$table = $DOF->im('reports')->table_reports($plugintype, $plugincode, $type, $options);

print($table);

// Печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>