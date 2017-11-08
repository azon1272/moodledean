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
require_once($DOF->plugin_path('im','programmsbcs','/form.php'));
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id группы 
$conds->agroupid   = optional_param('agroupid', null, PARAM_INT);
//id программы
$conds->programmid = optional_param('programmid',null, PARAM_INT);
$conds->departmentid = $addvars['departmentid'];
if ( $conds->programmid  )
{
    $programm = $DOF->storage('programms')->get($conds->programmid );
    $DOF->modlib('nvg')->add_level($programm->name.'['.$programm->code.']', $DOF->url_im('programms','/view.php',$conds)); 
    $DOF->modlib('nvg')->add_level($DOF->get_string('programmsbcsprogramm','programmsbcs'), $DOF->url_im('programmsbcs','/list_persons.php',$conds));
}

// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);

//проверяем доступ
$DOF->storage('programmsbcs')->require_access('view');

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('programmsbcs',null,$limitnum, $limitfrom);
//получаем список подписок
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.    
$list = $DOF->storage('programmsbcs')->get_listing($conds, $pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());                                     
                          
// получаем html-код таблицы с подписками
$programmsbcs = $DOF->im('programmsbcs')->showlist_persons($list,$conds);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
if ( ! $programmsbcs )
{// не найдено ни одной подписки
    print('<p align="center">(<i>'.$DOF->
            get_string('no_programmsbcs_found', 'programmsbcs').'</i>)</p>');
}else
{//есть подписки
    //покажем ссылки
    if ( $conds->programmid )
    {// если передан id программы
        // на программу
        if ( $DOF->storage('programms')->is_access('view', $conds->programmid) )
        {// если есть право просматривать программу
            $link_programm = '<a href='.$DOF->url_im('programms','/view.php',$conds).'>'
                 .$DOF->get_string('view_programm', 'programmsbcs').' '
                 .$DOF->storage('programms')->get_field($conds->programmid, 'name')
                 .'['.$DOF->storage('programms')->get_field($conds->programmid, 'code').']</a>';
            echo '<br>'.$link_programm.'<br>';
        }
        // на все предметы
        if ( $DOF->storage('programmitems')->is_access('view') )
        {// если есть право просматривать все предметы одной программы
            $link_programmitems = '<a href='.$DOF->url_im('programmitems','/list_agenum.php',$conds).'>'
                 .$DOF->get_string('view_all_programmitems', 'programmsbcs').'</a>';
            echo $link_programmitems.'<br>';
        }
        if ( $conds->agroupid )
        {// если id группы передано - покажем ссылку на просмотр группы
            if ( $DOF->storage('agroups')->is_access('view', $conds->agroupid) )
            {// если есть право просматривать информацию о группе
                $link_agroup = '<a href='.$DOF->url_im('agroups','/view.php',$conds).'>'
                     .$DOF->get_string('view_agroup', 'programmsbcs').' '
                 .$DOF->storage('agroups')->get_field($conds->agroupid, 'name')
                 .'['.$DOF->storage('agroups')->get_field($conds->agroupid, 'code').']</a>';
                echo $link_agroup.'<br>';
            }
        }
        // на список групп
        if ( $DOF->storage('agroups')->is_access('view') )
        {// если есть прово просматривать все группы
            $link_agroups = '<a href='.$DOF->url_im('agroups','/list.php',$conds).'>'
                                   .$DOF->get_string('list_agroups', 'programmsbcs').'</a>';
            echo $link_agroups.'<br>';
        }
    }
    // выводим таблицу с подписками
    echo '<br>'.$programmsbcs;
    // помещаем в массив все параметры страницы, 
    //чтобы навигация по списку проходила корректно
    $vars = array('limitnum'       => $pages->get_current_limitnum(),
                  'limitfrom'      => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('programmsbcs')->get_listing($conds,$pages->get_current_limitfrom(), 
                        $pages->get_current_limitnum(),'','*',true);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list_persons.php', $vars);
    echo $pagesstring;
}
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>