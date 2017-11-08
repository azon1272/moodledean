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
 * Отображение списка объектов. 
 * Если соответствующие поля есть в справочнике. 
 * страница принимает параметры departmentid, status, 
 * по необходимости, могут быть другие, 
 * специфичные для справочника. Если они переданы, 
 * то выводится не весь список, а только его часть, 
 * удовлетворяющая условию. 
 * Параметры limitfrom и limitnum предназначены для ограничения количества 
 * выводимых записей. Если limitnum задан, то внизу выводится указатель страниц. 
 * Сам указатель страниц должен быть реализован в виде метода в плагине 
 * modlib widgets, которому передаются значения $code (код плагина im для ссылки), 
 * $adds, $vars, $limitfrom, $limitnum и $count, 
 * на основании которых возвращается html-код указателя. 
 * Ссылки генерируются с помощью $DOF->url_im(). 
 * Если для этого типа записей предусмотрен поиск, 
 * то форма поиска отображается над списком. 
 * Для результатов поиска действуют те же фильтры, что и для вывода списка. 
 */

// Подключаем библиотеки
require_once('lib.php');
require_once($DOF->plugin_path('im','agroups','/form.php'));
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
// id учебного подразделения в таблице departmrnts
//выводятся классы с любым departmentid, если ничего не передано
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// выводятся классы с любым programmid, если ничего не передано
$conds->programmid   = optional_param('programmid', null, PARAM_INT);
// статус учебного периода. Выводятся периоды 
// с любым статусом, если ничего не передано
$conds->status       = optional_param('status', '', PARAM_ALPHA);
// название или код класса
$conds->nameorcode   = trim(optional_param('nameorcode', null, PARAM_TEXT));
// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);

//добавление уровня навигации
// TODO раньше тут чтояло $conds
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'agroups'), 
                               $DOF->url_im('agroups','/list.php'),$addvars);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

//проверяем доступ
$DOF->storage('agroups')->require_access('view');

// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('agroups',null,$limitnum, $limitfrom);
//получаем список групп
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.

$list = $DOF->storage('agroups')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());
                                                                          
// получаем html-код таблицы с группами
$agroups = $DOF->im('agroups')->showlist($list,$conds);
 
//покажем ссылку на создание новой группы
if ( $DOF->storage('agroups')->is_access('create') )
{// если есть право создавать группу
    if ( $DOF->storage('config')->get_limitobject('agroups',$conds->departmentid) )
    {
        $link = '<a href='.$DOF->url_im('agroups','/edit.php',$addvars).'>'.$DOF->get_string('newagroup', 'agroups').'</a>';
        echo '<br>'.$link.'<br>';
    }else 
    {
        $link =  '<span style="color:silver;">'.$DOF->get_string('newagroup', 'agroups').
        	' ('.$DOF->get_string('limit_message','agroups').')</span>';
        echo '<br>'.$link.'<br>'; 
    }  

}
    
if ( ! $agroups )
{// не найдено ни одной группы
    print('<p align="center">(<i>'.$DOF->
            get_string('no_agroups_found', 'agroups').'</i>)</p>');
}else
{//есть группы
    if ( $conds->programmid )
    {//если id программы указано - покажем ссылки
        // на программу
        if ( $DOF->storage('programms')->is_access('view', $conds->programmid) )
        {// если есть право просматривать программу
            $link_programm = '<a href='.$DOF->url_im('programms','/view.php?&programmid='.$conds->programmid,$addvars).'>'
                 .$DOF->get_string('view_programm', 'programmsbcs').' '
                 .$DOF->storage('programms')->get_field($conds->programmid, 'name')
                 .'['.$DOF->storage('programms')->get_field($conds->programmid, 'code').']</a>';
            echo $link_programm.'<br>';
        }
        // на все предметы
        if ( $DOF->storage('programmitems')->is_access('view') )
        {// если есть право просматривать все предметы одной программы
            $link_programmitems = '<a href='.$DOF->url_im('programmitems','/list_agenum.php?&programmid='.$conds->programmid,$addvars).'>'
                 .$DOF->get_string('view_all_programmitems', 'programmsbcs').'</a>';
            echo $link_programmitems.'<br>';
        }
    }
    // выводим таблицу с учебными группами
    echo '<br>'.$agroups;
    
    // помещаем в массив все параметры страницы, 
    //чтобы навигация по списку проходила корректно
    $vars = array('limitnum'     => $pages->get_current_limitnum(),
                  'limitfrom'    => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // составим запрос для извлечения количества записей
    $selectlisting = $DOF->storage('agroups')->get_select_listing($conds);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('agroups')->count_records_select($selectlisting);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    
    echo $pagesstring;
}
    $cdata = new stdClass();
    $cdata->dof = $DOF;
    $search = new dof_im_agroups_search_form(null, $cdata);
    $search->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>