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
// Copyright (C) 2008-2999  Dmitry Baranov (Дмитрий Баранов)              //
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

/** Страница удаления категории
 * 
 */

require_once('lib.php');

$id      = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

// конструируем навигацию
$DOF->modlib('nvg')->add_level($DOF->get_string('category_delete_title','inventory'), $DOF->url_im('inventory','/category/delete.php'), $addvars);
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// перед совершением всех операций проверяем права
$DOF->storage('invcategories')->require_access('delete', $id);

// создаем ссылку для возвращения обратно
$returnlink = $DOF->url_im('inventory', '/category/list.php', $addvars);
if ( ! $category = $DOF->storage('invcategories')->get($id) )
{// удаляемой категории не существует
    $message = $DOF->modlib('ig')->igs('form_err_is_exist_element');
    $message = $DOF->modlib('widgets')->error_message($message);
    redirect($returnlink, $message, 1);
}
if ( $category->status == 'deleted' )
{// категория уже удалена
    $message = $DOF->get_string('category_delete_success', 'inventory');
    $message = $DOF->modlib('widgets')->success_message($message);
    redirect($returnlink, $message, 1);
}

if ( ! $confirm )
{// спрашиваем - точно ли пользователь хочет удалить запись
    $message = $DOF->get_string('category_delete_confirmation', 'inventory', $category->name);
    $linkyes = $DOF->url_im('inventory', '/category/delete.php', $addvars + array('confirm' => 1, 'id' => $id));
    $DOF->modlib('widgets')->notice_yesno($message, $linkyes, $returnlink);
}else
{// удаление уже подтверждено - приступаем
    // удаляем саму категорию
    $obj = new stdClass();
    $obj->id = $id; 
    $obj->status = 'deleted';
    if ( $DOF->storage('invcategories')->update($obj) )
    {// удалить категорию удалось
        $message = $DOF->get_string('category_delete_success', 'inventory');
        $message = $DOF->modlib('widgets')->success_message($message);
    }else
    {// не удалось удалить категорию
        $message = $DOF->get_string('category_delete_failure', 'inventory');
        $message = $DOF->modlib('widgets')->error_message($message);
    }
    redirect($returnlink, $message, 1);
}


$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>