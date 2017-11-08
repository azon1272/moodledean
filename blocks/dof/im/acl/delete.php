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



// Подключаем библиотеки
require_once('lib.php');
//id записи о теме занятия
$aclid = required_param('id', PARAM_INT);

$DOF->im('acl')->require_access('acl:delete');

$delete = optional_param('delete', 0, PARAM_BOOL);
// проверки
// не найден элемент учебного плана
if ( ! $acl  = $DOF->storage('acl')->get($aclid) )
{// вывод сообщения и ничего не делаем
    $errorlink = $DOF->url_im('acl');
    $DOF->print_error('not_found_acl',$errorlink, '', 'im', 'acl');
}

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes ='/delete.php?id='.$aclid.'&delete=1';
$linkno ='/warrantacl.php?id='.$acl->aclwarrantid;
if ( $delete )
{// если сказали удалить - сменим статус
    $DOF->storage('acl')->delete($aclid);
    redirect($DOF->url_im('acl',$linkno,$addvars));
}else
{
    //вывод на экран    
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
    //$DOF->modlib('nvg')->add_level($DOF->get_string('delete_template', 'schedule'),
    //                               $DOF->url_im('schedule','/delete.php',$addvars));
    //печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // спросим об удалении
    
    
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_delete_acl','acl'), $DOF->url_im('acl',$linkyes,$addvars),
                                                                     $DOF->url_im('acl',$linkno,$addvars));
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>
