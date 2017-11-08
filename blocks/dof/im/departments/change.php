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
$departmentid = required_param('departmentid', PARAM_INT);
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$delete = optional_param('delete', 0, PARAM_BOOL);
$active = optional_param('active', 0, PARAM_BOOL);

// Ссылка для возврата при ошибке
$errorlink = $DOF->url_im('departments','',$addvars);
// проверки
// Либо удаляем, либо делаем активным!
if ( $active AND $delete )
{
    $DOF->print_error('err_multiplechangestatus',$errorlink,$id,'im','departments');
}
// не найдена персона
if ( ! $department  = $DOF->storage('departments')->get($id) )
{// вывод сообщения и ничего не делаем
    $DOF->print_error('notfound',$errorlink,$id,'im','departments');
}

// ссылка на возврат
$linkno = $DOF->url_im('departments', '/list.php',$addvars);
// Проверка возможность перехода в статус
$available = $DOF->workflow('departments')->get_available($id);
if ( ( $delete AND !isset($available['deleted'])) OR
     ( $active AND !isset($available['active']) ) )
{
    $DOF->print_error('err_changestatus',$errorlink,$id,'im','departments');
}

//проверка прав доступа
if ( $delete )
{
    $DOF->storage('departments')->require_access('delete');
} else if ( $active )
{
    $DOF->storage('departments')->require_access('create');
}

if ( $confirm )
{
    if ( $delete )
    {// Меняем статус подразделения
        $status = 'deleted';
    } else if ( $active )
    {
        $status = 'active';
    }    
    $DOF->workflow('departments')->change($id, $status);
    redirect($linkno);
} else
{
    //вывод на экран
    // спросим об удалении или активации
    if ( $delete )
    {
        $confirmation = $DOF->get_string('confirmation_delete_department','departments');
        $linkyes = $DOF->url_im('departments', '/change.php?confirm=1&departmentid='.$departmentid.'&delete=1&id='.$id);
    } else if ( $active )
    {
        $confirmation = $DOF->get_string('confirmation_active_department','departments');
        $linkyes = $DOF->url_im('departments', '/change.php?confirm=1&departmentid='.$departmentid.'&active=1&id='.$id);
    } else
    {
        $DOF->print_error('err_confirmation',$errorlink,$id,'im','departments');
    }
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('page_status_change_title', 'departments'),
                                   $DOF->url_im('departments','/change.php'));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $department->name . '</div><br>';
    $DOF->modlib('widgets')->notice_yesno($confirmation, $linkyes, $linkno);
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>