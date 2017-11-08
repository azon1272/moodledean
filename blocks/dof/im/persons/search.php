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
require_once(dirname(realpath(__FILE__)).'/lib.php');
require_once('form.php');
// Защищаем списки пользователей от случайного доступа
$DOF->storage('persons')->require_access('view');

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('persons', 'persons'), 
      $DOF->url_im('persons','/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('searchperson', 'persons'), 
      $DOF->url_im('persons').'/search.php',$addvars);

$searchform = new dof_im_person_search_form($DOF->url_im('persons','/search.php',$addvars));
//$formdata[''] = ;
//$searchform->is_submitted();


// Выводим шапку в режиме "портала
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL, 'left');
// Формируем запрос

$searchform->display();
if ( $searchform->is_submitted() AND $formdata = $searchform->get_data() )
{
    if ( isset($formdata->children) )
    {
        $children = true;
    }else
    {
        $children = false;
    }
    switch($formdata->option)
    {
        case 'bylastname':
        	$DOF->im('persons')->show_list($DOF->storage('persons')->get_list_search_lastname
                ($formdata->searchstring,$addvars['departmentid'], $children),$addvars);
        break;
        case 'byquery':
            $DOF->im('persons')->show_list($DOF->storage('persons')->get_list_search
	            ($formdata->searchstring,$addvars['departmentid'], $children),$addvars);
        break;
    }
}

$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL,'right');

?>