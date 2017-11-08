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

$aclwarrantid = required_param('aclwarrantid',PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

if ( ! $DOF->im('acl')->is_access('aclwarrants:changestatus/owner',$aclwarrantid) )
{
    $DOF->im('acl')->require_access('aclwarrants:changestatus',$aclwarrantid);
}

// проверка, что это субдоверенность
if ( ! $warrant = $DOF->storage('aclwarrants')->get($aclwarrantid) )
{// переводим доверенность в архивный статус
    $DOF->print_error('not_found_warrant', '', $aclwarrantid, 'im', 'acl');
}
if ( $warrant->parenttype != 'sub' )
{// переводим доверенность в архивный статус
    $DOF->print_error('error_archive_warrant', '', $aclwarrantid, 'im', 'acl');
}

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes ='/archivewarrant.php?aclwarrantid='.$aclwarrantid.'&delete=1';
$linkno ='/warrantacl.php?id='.$aclwarrantid;

if ( $delete )
{// если сказали удалить - сменим статус
    $obj = new stdClass();
    $DOF->workflow('aclwarrants')->change($aclwarrantid, 'archive');
    redirect($DOF->url_im('acl',$linkno,$addvars));
}else
{
    //вывод на экран
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('delete_warrant', 'acl'),
                                   $DOF->url_im('acl','/archivewarrant.php',$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $warrant->name . '</div><br>';
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_delete_warrant','acl'), $DOF->url_im('acl',$linkyes,$addvars),
                                                                     $DOF->url_im('acl',$linkno,$addvars));
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}
?>