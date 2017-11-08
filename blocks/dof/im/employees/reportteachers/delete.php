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

// принятые данные
$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

// проверки
// не найден отчет
if ( ! $report = $DOF->storage('reports')->get($id) )
{// вывод сообщения и ничего не делаем
    print_error($DOF->get_string('notfound_report','employees', $id));
}
// проверка прав
if ( ! $DOF->storage('reports')->is_access('delete',$id) AND $report->personid != $DOF->storage('persons')->get_by_moodleid_id() )
{
    print_error($DOF->get_string('no_access','employees',$report->name));
}
// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes = $DOF->url_im('employees', '/reportteachers/delete.php?id='.$id.'&delete=1', $addvars);
$linkno = $DOF->url_im('employees', '/reportteachers/index.php',$addvars);
if ( $delete )
{
    // Делаем физическое удаление записи
    $DOF->storage('reports')->delete_report($report);
    redirect($linkno);
}else
{
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $report->name.'</div><br>';
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('delete_yes','employees'), $linkyes, $linkno);
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
}

?>