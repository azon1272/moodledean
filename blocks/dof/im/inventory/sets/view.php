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
// Copyright (C) 2008-2999  Dmitriy Baranov (Дмитрий Баранов)             //
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

require_once('lib.php');
require_once($DOF->plugin_path('im','inventory','/invorders/form.php'));

$id = required_param('id', PARAM_INT);
$message = optional_param('message','', PARAM_TEXT);


// проверяем, существует ли просматриваемое оборудование
if ( ! $set = $DOF->storage('invsets')->get($id) )
{
    $DOF->print_error('set_not_found','inventory', '', '', 'im', 'inventory');
}

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->modlib('ig')->igs('view').':'.$set->code, $DOF->url_im('inventory','/sets/view.php',$addvars));

// формируем данные для формы
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->depid = $addvars['departmentid'];
$customdata->id = $id;
$path = $DOF->url_im('inventory','/sets/view.php?id='.$id,$addvars);
// форма расформирования
$form = new dof_im_inventory_order_set_no_invset($path,$customdata);
// форма выдачи
$form_give = new dof_im_inventory_order_set_delivery($path,$customdata); 
// форма возврата
$form_return = new dof_im_inventory_order_set_return($path,$customdata);     

// расформирование
$form->process($addvars);
// выдача
$form_give->process($addvars);
// возврат
$form_return->process($addvars);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// ообщение и совершенном действии(выдача, расформирование, возврат)
if ( $message )
{
    $text = $DOF->get_string($message,'inventory');
    echo $DOF->modlib('widgets')->success_message($text);
}

// проверка прав на просмотр
$DOF->storage('invsets')->require_access('view',$id);
// Показываем таблицу с информацией о комплекте
$DOF->im('inventory')->display_set_info($id, $addvars);

// кнопка расформировать
// можем расформировать только активный и недоступный комплекты

if ( in_array($set->status, array('active','notavailable')) )
{
    $items = $DOF->storage('invitems')->get_records(array('invsetid'=>$id));
    $flag = true;
    foreach ( $items as $item )
    {// нет право использовать ресурс - нельзя расформировать комплект
        if ( ! $DOF->storage('invitems')->is_access('use',$item->id) )
        {
            $flag = false;
            break;
        }
    }
    if ( $flag )
    {
        $form->display();
    }    
}    
// Кнопка выдать комплект
// проверка по стаутсу и правам
if ( $set->status == 'active' AND empty($set->personid) AND $DOF->storage('invsets')->is_access('use',$id) )
{
    $form_give->display();   
}
// Кнопка вернуть комплект
// проверка по стаутсу и правам
if ( $set->status == 'granted' AND ! empty($set->personid) AND $DOF->storage('invsets')->is_access('use',$id) )
{
    $form_return->display();   
}

// отобразим оборудование комплекта
$DOF->im('inventory')->display_invitems_list($id, 'items_of_set', $addvars);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>