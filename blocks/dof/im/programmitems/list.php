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
// подключаем формы
require_once('form.php');

//добавление уровня навигации
$DOF->modlib('nvg')->add_level(
    $DOF->get_string('title', 'programmitems'), 
    $DOF->url_im('programmitems'), '/list.php', 
    $addvars
);

// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
$conds->departmentid = optional_param('departmentid', null, PARAM_INT);
// Статус предмета
$conds->status       = optional_param('status', null, PARAM_INT);
// id программы
$conds->programmid   = optional_param('programmid', null, PARAM_INT);
// название предмета
$conds->name         = trim(optional_param('name', null, PARAM_TEXT));
// код предмета
$conds->code         = trim(optional_param('code', null, PARAM_TEXT));
// название или код класса
$conds->nameorcode   = trim(optional_param('nameorcode', null, PARAM_TEXT));
// ловим номер страницы, если его передали
// какое количество предметов выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);
// создадим форму поиска

$customdata = new stdClass();
$customdata->dof = $DOF;
$searchform = new dof_im_programmitems_search_form(null, $customdata);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//проверяем доступ
$DOF->storage('programmitems')->require_access('view');
// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('programmitems',null,$limitnum, $limitfrom);
//получаем список предметов
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
$list = $DOF->storage('programmitems')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());
                                                                           
// получаем html-код таблицы с предметами
$pitems = $DOF->im('programmitems')->showlist($list,$conds);
// покажем ссылку на создание предмета
if ( $DOF->storage('programmitems')->is_access('create') )
{// если есть право на создание предмета
    $urloptions = array();
    if ( $conds->programmid )
    {// если указан id программы - подставим его по умолчанию в форму создания
        $urloptions['programmid'] = $conds->programmid;
    }
    // лимит
    if ( $DOF->storage('config')->get_limitobject('programmitems',$conds->departmentid) )
    {
        $link = '<a href='.$DOF->url_im('programmitems','/edit.php', array_merge($urloptions,(array)$conds)).'>'.
                $DOF->get_string('newpitem', 'programmitems').'</a>';
    }else 
    {    
        $link =  '<span style="color:silver;">'.$DOF->get_string('newpitem', 'programmitems').
        	' <br>('.$DOF->get_string('limit_message','programmitems').')</span>';        
    }        
    // распечатаем ссылку на создание предмета
    echo '<br>'.$link.'<br>';
}
if ( ! $pitems )
{// не найдено ни одного учебного предмета
    print('<p align="center">(<i>'.$DOF->get_string('no_pitems_found', 'programmitems').'</i>)</p>');
	//print_error($DOF->get_string('notfoundage','programmitems'));
}else
{
    // выводим таблицу с учебными предметами
    echo '<br>'.$pitems;
    
    // помещаем в массив все параметры страницы, чтобы навигация по списку проходила корректно
    $vars = array('limitnum'       => $pages->get_current_limitnum(),
                  'limitfrom'      => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // составим запрос для извлечения количества записей
    $selectlisting = $DOF->storage('programmitems')->get_select_listing($conds);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('programmitems')->count_records_select($selectlisting);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', (array) $conds + $vars);
    echo $pagesstring;
}
// покажем форму поиска
$searchform->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>