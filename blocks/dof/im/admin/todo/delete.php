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
//id персоны
$todoid = required_param('todoid', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
// проверки
// не найдена персона
if ( ! $todorec = $DOF->get_todo($todoid) )
{// вывод сообщения и ничего не делаем
    print_error($DOF->get_string('notfound','admin', $todoid));
}

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('admin', '/todo/delete.php?todoid='.$todoid.'&delete=1',$addvars);
$linkno = $DOF->url_im('admin', '/todo/list.php',$addvars);
if ( $delete )
{// если сказали удалить
    $DOF->delete_todo($todoid);
    redirect($linkno);
}else
{
    //вывод на экран
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('todo', 'admin'), $DOF->url_im('admin','/todo/list.php'),$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('deletetodo', 'admin'), $DOF->url_im('admin','/todo/delete.php'),$addvars);
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $todorec->todocode . '</div><br>';
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('deletetodorec','admin'), $linkyes, $linkno);
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>