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
$conds = new stdClass;
$conds->departmentid = $addvars['departmentid'];
$conds->plugintype = $addvars['plugintype'];
$conds->plugincode = $addvars['plugincode'];
// готовим объект для вставки в форму
$customdata = new stdClass();
$customdata->id = $addvars['departmentid'];
$customdata->dof = $DOF;
// подключаем форму
$form = new dof_im_cfg_form($DOF->url_im('cfg','/edit.php',$addvars), $customdata);

/*
// принимаем id настройки (таблица config)
$id = optional_param('id', 0, PARAM_INT);
// проверка на существование записи в БД
if ( $id AND ! $DOF->storage('config')->is_exists($id) )
{
    print_error($DOF->get_string('notfound','cfg',$id));
}

// навигация
if ( $id )
{
     $DOF->modlib('nvg')->add_level($DOF->get_string('edit_cfg','cfg'), $DOF->url_im('cfg','/edit.php?id='.$id,$addvars));
}else
{
    $DOF->modlib('nvg')->add_level($DOF->get_string('new','cfg'), $DOF->url_im('cfg','/edit.php?id='.$id,$addvars));
}

// TODO права открыть
//проверяем доступ
/*
if ( $id )
{//проверка права редактировать подписку на курс
    $DOF->im('cfg')->require_access('edit', $id);
}else
{//проверка права создавать подписку на курс
    $DOF->im('cfg')->require_access('new');
}*/

$DOF->modlib('nvg')->add_level($DOF->get_string('edit_cfg','cfg'), $DOF->url_im('cfg','/edit.php',$addvars));
// есть ошибка-запомним, нет-то в обработчике сработает redirect 
$message = $form->process();
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// будут ошибки - тут отобразятся они

echo  $message;

//Выведем название выбранного подразделения
if ( $addvars['departmentid'] )
{// получили id подразделения - выведем название и код
    $depname = $DOF->storage('departments')->get_field($addvars['departmentid'],'name').' ['.
               $DOF->storage('departments')->get_field($addvars['departmentid'],'code').']';
}else
{// нету - значит выводим для всех
    $depname = $DOF->get_string('all_departments', 'cfg');
}

// список настроек
$configs = $DOF->storage('config')->get_listing($conds);
// вспомогательный обект для содержания
$con = new stdClass();
$con->plugintype = '';
$con->plugincode = '';

// Тут оглавление 
echo "<br><div><b>".$DOF->get_string('content','cfg')."</b></div>";
// делаем содержание
echo "<ol style='list-style-type:none;'><li><ol>";
// якорь вверх
echo "<a name = top></a>";
foreach ($configs as $config)
{
    if ( $con->plugintype != $config->plugintype )
    {// другой плагин - отобразми
        echo "</ol></li>";
        echo "<li><a href = #".$config->plugintype.">".$config->plugintype."</a>";
        echo "<ol style='list-style-type:none;'>";
    }
    if ( $con->plugincode != $config->plugincode )
    {// другой плагин - отобразми
        echo "<li><a href = #".$config->plugincode.">".$config->plugincode."</a></li>";
    } 
    
    $con = $config;
}

echo "</ol>";

print('<p align="center"><font size="5">'.$depname.'</font>');

// печать формы
$form->display();
// якорь вниз
echo "<a name = down></a>";

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>