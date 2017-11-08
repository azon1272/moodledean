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
require_once(dirname(realpath(__FILE__)).'/lib.php');
// Подключаем скрипты для сворачивающихся блоков
$DOF->modlib('widgets')->js_init('show_hide');

$id = required_param('id', PARAM_INT);
// Защищаем персональные сведения от случайного доступа
$DOF->storage('persons')->require_access('view',$id);

$DOF->modlib('nvg')->add_level($DOF->get_string('persons', 'persons'), 
      $DOF->url_im('persons', '/list.php', $addvars));
$DOF->modlib('nvg')->add_level($DOF->storage('persons')->get_fullname($id),
      $DOF->url_im('persons','/view.php?id='.$id,$addvars));

// Выводим шапку в режиме "портала
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
if ( ! $DOF->storage('persons')->is_exists($id) )
{
    $errorlink = $DOF->url_im('persons','',$addvars);
    $DOF->print_error('nopersons',$errorlink,null,'im','persons');   
}

// Выводим информацию о персоне
$DOF->im('persons')->show_person($id,$addvars);
echo "<br><p align=center><a href='{$DOF->url_im('persons',"/edit.php?id={$id}",$addvars)}'>{$DOF->get_string('edit', 'persons')}</a></p>";
// широковещательным запросом получаем информацио о персоне со всех плагинов
// Дополнительные данные для широковещательного запроса

if ( $DOF->plugin_exists('im', 'comments') )
{// Получить форму комментариев
    $DOF->im('comments')->commentsform('storage', 'persons', $id, $addvars);
}
$result = $DOF->send_event('im', 'persons', 'persondata', $id);
foreach ( $result as $data )
{// Отображаем 
    echo $data;
}

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');
