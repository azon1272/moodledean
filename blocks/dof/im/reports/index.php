<?PHP
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

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//проверяем полномочия на просмотр информации
$DOF->storage('reports')->require_access('view_report');

// подключаем класс dof_html_writer
$DOF->modlib('widgets')->html_writer();

echo dof_html_writer::link($DOF->url_im('reports', '/list.php', array(
        'plugintype' => 'im', 
        'plugincode' => 'journal', 
        'code' => 'loadteachers')), 
            $DOF->get_string('im_journal_loadteachers', 'reports'))."<br/>";

echo dof_html_writer::link($DOF->url_im('reports', '/list.php', array(
        'plugintype' => 'im', 
        'plugincode' => 'journal', 
        'code' => 'replacedevents')), 
            $DOF->get_string('im_journal_replacedevents', 'reports'))."<br/>";

echo dof_html_writer::link($DOF->url_im('reports', '/list.php', array(
        'plugintype' => 'sync', 
        'plugincode' => 'mreports', 
        'code' => 'teachershort')), 
            $DOF->get_string('sync_mreports_teachershort', 'reports'))."<br/>";

echo dof_html_writer::link($DOF->url_im('reports', '/list.php', array(
        'plugintype' => 'sync', 
        'plugincode' => 'mreports', 
        'code' => 'teacherfull')), 
            $DOF->get_string('sync_mreports_teacherfull', 'reports'))."<br/>";

echo dof_html_writer::link($DOF->url_im('reports', '/list.php', array(
        'plugintype' => 'sync', 
        'plugincode' => 'mreports', 
        'code' => 'studentshort')), 
            $DOF->get_string('sync_mreports_studentshort', 'reports'))."<br/>";

echo dof_html_writer::link($DOF->url_im('reports', '/list.php', array(
        'plugintype' => 'sync', 
        'plugincode' => 'mreports', 
        'code' => 'studentfull')), 
            $DOF->get_string('sync_mreports_studentfull', 'reports'))."<br/>";

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>