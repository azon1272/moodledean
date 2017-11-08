<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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
require_once ('form.php');
// входные параметры
$orderid = required_param('id', PARAM_INT);
// добавляем уровень навигации

$DOF->modlib('nvg')->add_level($DOF->get_string('page_main_name', 'learningorders'), $DOF->url_im('learningorders','',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('list_orders', 'learningorders'), $DOF->url_im('learningorders','/list.php',$addvars));
$DOF->modlib('nvg')->add_level($DOF->get_string('choice_periods', 'learningorders'), $DOF->url_im('learningorders','/ordertransfer/ageschoice.php?id='.$orderid,$addvars));

// права
$DOF->im('learningorders')->require_access('order');



// проверим нахождение объекта в БД
if ( $orderid === 0 )
{// если id = 0, формируем новый приказ
    $order = new dof_im_learningorders_ordertransfer($DOF);
    $orderid = $order->order->get_id();
    redirect($DOF->url_im('learningorders','/ordertransfer/ageschoice.php?id='.$orderid,$addvars),'',0);
}

if ( ! $order = $DOF->storage('orders')->get($orderid) )
{// не удалось найти приказ
	print_error($DOF->get_string('not_found_order','learningorders', $orderid));
}elseif( ! is_object($order) OR $order->code != 'transfer' )
{// приказ не типа transfer
    print_error($DOF->get_string('not_transfer','learningorders', $orderid));
}elseif ( $order->signdate )
{// приказ уже подписан и его нельзя менять
    print_error($DOF->get_string('order_already_signed','learningorders', $orderid));
}


// наследуем класс
$order = new dof_im_learningorders_ordertransfer($DOF,$orderid);

if( isset($_POST['1']) )
{// верхняя форма
    if( isset($_POST['add']) )
    {
        $order->add_ages_in_order($_POST['addselect']);
    }elseif( isset($_POST['remove']) )
    {
        $order->delete_ages($_POST['removeselect']);
    }
}elseif( isset($_POST['2']) )
{// нижняя форма
    if( isset($_POST['add']) )
    {
        $order->add_ages_in_order(null, $_POST['addselect']);
    }elseif( isset($_POST['remove']) )
    {
        $order->delete_ages(null, $_POST['removeselect']);
    }
}elseif( isset($_POST['3']) )
{// форма подразделений
    if( isset($_POST['add']) )
    {
        $order->add_ages_in_order(null, null, $_POST['addselect']);
    }elseif( isset($_POST['remove']) )
    {
        $order->delete_ages(null, null, $_POST['removeselect']);
    }
}


if ( ! $orderages = $order->set_period() )
{// не удалось загрузить приказ
    //ошибочка небольшая такая
	print_error($DOF->get_string('not_found_order','learningorders', $orderid));
}


// Получаем объект, который реализует стандартную форму "добавить/удалить"
$DOF->modlib('widgets')->addremove();

// создаем обект для передачи данных
$customdata = new stdClass();
$customdata->dof = $DOF;

// форма ПОДРАЗДЕЛЕНИЙ
$addremove_dep = new dof_modlib_widgets_addremove($DOF,'','3');
$departments = $DOF->storage('departments')->get_list_no_deleted();
// формируем список доступных подразделений
$orderdeparts = $order->set_depart($departments);
$left = array();
foreach ($departments as $department)
{   
    if ( array_search($department->id,$orderdeparts) === false ) 
    {// если такой элемент уже есть в списке выбранных не наследуем его
        $left[$department->id] = $department->name;
    }
}
// формируем список выбранных подразделений
$right = array();
foreach ($orderdeparts as $departmentid)
{    
    $right[$departmentid] =  $DOF->storage('departments')->get_field($departmentid,'name');
}
$options = new stdClass();
$values = new stdClass();
$values->title       = $DOF->get_string('departments','learningorders');
$values->addlabel    = $DOF->get_string('available','learningorders');
$values->removelabel = $DOF->get_string('selected','learningorders');
$values->addarrow    = $DOF->modlib('ig')->igs('add');
$values->removearrow = $DOF->modlib('ig')->igs('remove');
// галочка "Включая дочерние"
$values->element = '<p  style="font-size: 11pt;"><input type="checkbox" disabled="disabled" name="semidepartments">'.
    $DOF->get_string('semidepartments','learningorders').'</p>';
$addremove_dep->set_default_options($options);
$addremove_dep->set_default_strings($values);
$addremove_dep->set_add_list($left);
$addremove_dep->set_remove_list($right);

// наследуем форму ОТКУДА
$addremove_f = new dof_modlib_widgets_addremove($DOF,'','1');
$ages = $DOF->storage('ages')->get_records(array('status'=>array('active', 'completed')));
// формируем список доступных периодов
$left = array();
foreach ($ages as $age)
{   
    if ( array_search($age->id,$orderages['from']) === false ) 
    {// если такой элемент уже есть в списке выбранных не наследуем его
        $left[$age->id] = $age->name;
    }
}
// формируем список выбранных периодов
$right = array();
foreach ($orderages['from'] as $ageid)
{    
    $right[$ageid] =  $DOF->storage('ages')->get_field($ageid,'name');
}
$options = new stdClass();
$values = new stdClass();
$values->title       = $DOF->get_string('from','learningorders');
$values->addlabel    = $DOF->get_string('available','learningorders');
$values->removelabel = $DOF->get_string('selected','learningorders');
$values->addarrow    = $DOF->modlib('ig')->igs('add');
$values->removearrow = $DOF->modlib('ig')->igs('remove');
$addremove_f->set_default_options($options);
$addremove_f->set_default_strings($values);
$addremove_f->set_add_list($left);
$addremove_f->set_remove_list($right);


// Запишем выбранные
$customdata->ages = new stdClass();
$customdata->ages->from = $right;


// наследуем форму КУДА
$array = array('plan', 'createstreams', 'createsbc', 'createschedule', 'active', 'completed');
$addremove_t = new dof_modlib_widgets_addremove($DOF,'','2');
$ages = $DOF->storage('ages')->get_records(array('status'=>$array));
// формируем список доступных периодов
$left = array();
foreach ($ages as $age)
{   
    if ( array_search($age->id,$orderages['where']) === false ) 
    {// если такой элемент уже есть в списке выбранных не наследуем его
        $left[$age->id] = $age->name;
    }
}
// формируем список выбранных периодов
$right = array();
foreach ($orderages['where'] as $ageid)
{    
    $right[$ageid] =  $DOF->storage('ages')->get_field($ageid,'name');
}
$options = new stdClass();
$values = new stdClass();
$values->title       = $DOF->get_string('to','learningorders');
$values->addlabel    = $DOF->get_string('available','learningorders');
$values->removelabel = $DOF->get_string('selected','learningorders');
$values->addarrow    = $DOF->modlib('ig')->igs('add');
$values->removearrow = $DOF->modlib('ig')->igs('remove');
$addremove_t->set_default_options($options);
$addremove_t->set_default_strings($values);
$addremove_t->set_add_list($left);
$addremove_t->set_remove_list($right);

// Запишем выбранные
$customdata->ages->to = $right;
// базовый массив id
$customdata->base = $orderages['base'];

// кнопка ДАЛЕЕ и select
$base_select = new dof_im_learningorders_ordertransfe_base
              ($DOF->url_im('learningorders','/ordertransfer/ageschoice.php?id='.$orderid,$addvars), $customdata);

$error = '';
// обработчик формы ДАЛЕЕ
if ($base_select->is_submitted() AND confirm_sesskey() AND $formdata = $base_select->get_data())
{// даные переданы в текущей сессии - получаем
    if ( $order->chage_base($formdata->base) )
    {// МОНСТР в работе
        $order->formating_transfer_order();
        redirect($DOF->url_im('learningorders','/ordertransfer/formationorder.php?id='.$orderid,$addvars),'',0);
    }else 
    {// ошибка
        $error = $DOF->get_string('no_save','learningorders');
    }
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// вывод подразделений
$addremove_dep->print_html();

// вывод откуда
$addremove_f->print_html();
// вывод куда
$addremove_t->print_html();
// отображение формы
$base_select->display();
// вывод ошибки
if ( $error != '' )
{
    echo '<div align="center" style="color:red;font-size:20px;">' . $error . '</div>';
}

//print_object($order->get_order_data()); 
//$order->print_texttable();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);


?>