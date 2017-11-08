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
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'departments'), $DOF->url_im('departments','/index.php'),$addvars);

//id подразделения
$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
// проверки
// не найдена персона
if ( ! $config  = $DOF->storage('config')->get($id) )
{// вывод сообщения и ничего не делаем
    $errorlink = $DOF->url_im('cfg');
    $DOF->print_error('notfound', $errorlink, $id, 'im', 'cfg');
}

// TODO узнать - нужны/нет тут права
/*
//проверка прав доступа
$DOF->im('cfg')->require_access('delete');
*/

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('cfg', '/delete.php?id='.$id.'&delete=1', $addvars);
$linkno = $DOF->url_im('cfg', '/index.php',$addvars);
if ( $delete )
{
    // Делаем физическое удаление записи
    $DOF->storage('config')->delete($id);
    redirect($linkno);
}else
{
    //вывод на экран
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('delete'),$DOF->url_im('cfg','/delete.php?id='.$id,$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $config->code.'(' .$config->value. ')</div><br>';
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('delete_yes','cfg'), $linkyes, $linkno);
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>