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

/**
 * Отображает форму добавления и редактирования. 
 * Если передан параметр id, 
 * то отображается редактирование, 
 * если не передан - добавление. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');
$agroupid = optional_param('agroupid', 0, PARAM_INT);

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'agroups'), 
                               $DOF->url_im('agroups','/list.php'),$addvars);
if ( $agroupid == 0 )
{//проверяем доступ
    $DOF->storage('agroups')->require_access('create');
    $DOF->modlib('nvg')->add_level($DOF->get_string('newagroup', 'agroups'), 
                                   $DOF->url_im('agroups','/edit.php?agroupid='.$agroupid,$addvars));
}else
{//проверяем доступ
    $DOF->storage('agroups')->require_access('edit', $agroupid);
    if ( $agroup = $DOF->storage('agroups')->get($agroupid) )
    {// получили группу - добавим ее в навигацию
        $DOF->modlib('nvg')->add_level($agroup->name.'['.$agroup->code.']',
                             $DOF->url_im('agroups','/view.php?agroupid='.$agroupid,$addvars));
    }   
    $DOF->modlib('nvg')->add_level($DOF->get_string('editagroup', 'agroups'), 
                         $DOF->url_im('agroups','/edit.php?agroupid='.$agroupid,$addvars));
}

$error = '';
if ( $DOF->storage('agroups')->is_exists($agroupid) OR $agroupid == 0 )
{
    // загружаем форму
    $form = $DOF->im('agroups')->form($agroupid); 
    //подключаем обработчик формы
    include($DOF->plugin_path('im','agroups','/process_form.php'));
    
} 


//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( ! $DOF->storage('agroups')->is_exists($agroupid) AND $agroupid != 0 )
{// если период не найден, выведем ошибку
    print_error($DOF->get_string('notfoundargoup','agroups'));
} 

 
echo $error;

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>