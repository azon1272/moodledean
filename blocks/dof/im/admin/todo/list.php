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

// ловим номер страницы, если его передали
// какое количество записей выводить на экран
$limitnum  = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum  = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom = optional_param('limitfrom', '0', PARAM_INT);

$sort = optional_param('sort', 'id', PARAM_TEXT);
// @todo переданную по get/post сортировку обрабатывать непосредственно в методе справочника
if ( $sort != 'id' AND $sort !='plugincode' AND $sort !='plugintype' 
     AND $sort !='todocode' AND $sort !='tododate' )
{// передана нехорошая сортировка - пропишем стандартную
    $sort = 'plugintype ASC, plugintype ASC';
}

// добавляем уровень навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('todo', 'admin'), $DOF->url_im('admin','/todo/list.php'),$addvars);

$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('admin',null,$limitnum,$limitfrom);

echo "<br><a href='".$DOF->url_im('admin','/todo/edit.php')."'>".$DOF->get_string('dotodo', 'admin')."</a><br><br>";

//Выведем таблицу todo
$list = $DOF->get_todo(0,$sort,$limitnum,$limitfrom-1);
$todo = $DOF->im('admin')->show_list($list, $addvars, $load);
if ( $todo )
{
    // выводим таблицу с должностями
    echo $todo;
    // помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
    $vars = array('limitnum'     => $pages->get_current_limitnum(),
                  'limitfrom'    => $pages->get_current_limitfrom(),
                    'sort' => $sort);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count  = count($DOF->get_todo(0,$sort));
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/todo/list.php', $vars);
    echo $pagesstring;
}

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');
?>