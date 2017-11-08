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
// id контролькой точки, если она редактируктся
$sectionid  = optional_param('id', 0, PARAM_INT);

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
        $DOF->url_im('plans','/list.php',$addvars));

if ( $sectionid )
{// редактируем существующую КТ
    if  ( ! $section = $DOF->storage('plansections')->get($sectionid) )
    {// нет такого объекта в базе
        $DOF->print_error($DOF->get_string('notfound', 'plans', $sectionid));
    }
    $linktype = $section->linktype;
    $linkid   = $section->linkid;
    $DOF->im('plans')->nvg($linktype, $linkid,$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('editthemeplan', 'plans'),
        $DOF->url_im('plans','/editsection.php', array('linktype'=> $linktype, 'linkid' => $linkid) + $addvars));
    // установим тип связи и id привязки - они нам понадобятся для ссылок
}else
{// создаем новую КТ
    // тип связи, если мы создаем КТ с заданными параметрами 
    $linktype = required_param('linktype', PARAM_ALPHA);
    // id элемента с которым связывается КТ
    $linkid   = required_param('linkid', PARAM_INT);
    // @todo добавить проверку на корректность связи
    $DOF->im('plans')->nvg($linktype, $linkid,$addvars);
    $DOF->modlib('nvg')->add_level($DOF->get_string('newthemeplan', 'plans'),
        $DOF->url_im('plans','/editsection.php', array('linktype'=> $linktype, 'linkid' => $linkid) + $addvars));
    $section = new stdClass();
    $section->id = $sectionid;
    $section->linktype = $linktype;
    $section->linkid = $linkid;
}
//проверяем доступ
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid);    
}

 


//вывод на экран
//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)

if ( isset($USER->sesskey) )
{//сохраним идентификатор сессии
    $section->sesskey = $USER->sesskey;
}else
{//идентификатор сессии не найден
    $section->sesskey = 0;
}
$customdata = new stdClass;
$customdata->section = $section;
$customdata->dof   = $DOF;
if ( isset($section->status) AND $section->status == 'deleted' )
{// если контрольная точка удалена - запретим ее редактировать
    $form = new dof_im_plans_edit_themeplan_form($DOF->url_im('plans',
            '/editsection.php', array('linktype'=>$linktype, 'linkid'=>$linkid) + $addvars),$customdata, 'post', '', null, false);
}else
{// в остальных случаях - разрешим
    $form = new dof_im_plans_edit_themeplan_form($DOF->url_im('plans',
            '/editsection.php', array('linktype'=> $linktype, 'linkid' => $linkid) + $addvars),$customdata);
}

// заносим значения по умолчению
$form->set_data($section); 


$error = '';
//подключаем обработчик формы
include($DOF->plugin_path('im','plans','/process_form_section.php'));
//вывод на экран

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

echo $error;

// печать формы
$form->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>
