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
require_once($DOF->plugin_path('im','plans','/form.php'));
// создаем объект, который будет содержать будущие условия выборки
$conds = new stdClass();
/** из трех нижеперечисленных параметров (кроме status) 
 * передаваться одновременно в ссылке может 
 * только один из трех поэтому проверка такая извращенная
 */
if ( $cstreamid = optional_param('cstreamid', null, PARAM_INT) )
{// id предмето-потока
    //выводятся все КТ приписанные к этому потоку
    $conds->cstreamid = $cstreamid;
}elseif( $ageid = optional_param('ageid', null, PARAM_INT) )
{// id периода
    //выводятся все КТ приписанные к этому периоду
    $conds->ageid = $ageid;
}elseif( $programmitemid = optional_param('programmitemid', null, PARAM_INT) )
{// id предмета
    // выводятся все КТ приписанные к предмету
    $conds->programmitemid   = $programmitemid;
    // добавляем уровень навигации
    $addvars['pitemid'] = $programmitemid;
    $addvars['meta']    = '0';
    $DOF->modlib('nvg')->add_level($DOF->storage('programmitems')->get($programmitemid)->name, 
            $DOF->url_im('programmitems','/view.php',$addvars));
}else
{   // добавляем уровень навигации(list без параметров)
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'), 
            $DOF->url_im('plans','/list.php',$addvars));
}



// статус учебного периода.
$conds->status       = optional_param('status', '', PARAM_ALPHA);
// название или код класса
$conds->nameorcode   = trim(optional_param('nameorcode', null, PARAM_TEXT));
// ловим номер страницы, если его передали
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum     = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom    = optional_param('limitfrom', '1', PARAM_INT);
//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
//проверяем доступ
$DOF->storage('plans')->require_access('view');
// подключаем класс для вывода страниц
$pages = $DOF->modlib('widgets')->pages_navigation('plans',null,$limitnum, $limitfrom);
//получаем список групп
// массивы в PHP нумеруются с нуля, а наши страницы - с 1, 
//поэтому от стартового значения отнимем единицу.
$list = $DOF->im('plans')->get_listing($conds, $pages->get_current_limitfrom()-1, 
                                      $pages->get_current_limitnum());
                                                                           
// получаем html-код таблицы с КТ
$points = $DOF->im('plans')->showlist($list,$addvars);
//покажем ссылку на создание новой КТ
if ( $DOF->im('plans')->is_access('addpoint') )
{// если есть право на создание КТ
    $paramenters = array();
    if ( isset($conds->programmitemid) AND $conds->programmitemid )
    {// если указана программа - то добавим ссылку на создание КТ для программы
        $paramenters['linkid']   = $conds->programmitemid;
        $paramenters['linktype'] = 'programmitems';
    }elseif( isset($conds->cstreamid) AND $conds->cstreamid )
    {// если указан процесс - то добавим ссылку на создание КТ для процесса
        $paramenters['linkid']    = $conds->cstreamid;
        $paramenters['linktype']  = 'cstreams';
    }elseif( isset($conds->ageid) AND $conds->ageid )
    {// если указан период то добавим ссылку на создание КТ для периода
        $paramenters['linkid']    = $conds->ageid;
        $paramenters['linktype']  = 'ages';
    }
    $paramenters['departmentid'] = $addvars['departmentid'];
    if ( isset($paramenters['linktype']) AND $paramenters['linktype'] )
    {// выводим ссылку на создание - если есть для чего создавать элемент планирования
        $link = '<a href='.$DOF->url_im('plans','/edit.php', $paramenters).'>'.$DOF->get_string('newpoint', 'plans').'</a>';
        echo '<br>'.$link.'<br>';
    }
}
if ( ! $points )
{// не найдено ни одной КТ
    print('<p align="center">(<i>'.$DOF->
            get_string('no_points_found', 'plans').'</i>)</p>');
}else
{//есть группы
    
    // выводим таблицу с КТ
    echo '<br>'.$points;
    
    // помещаем в массив все параметры страницы, 
    //чтобы навигация по списку проходила корректно
    $vars = array('limitnum'       => $pages->get_current_limitnum(),
                  'limitfrom'      => $pages->get_current_limitfrom());
    // добавляем все необходимые условия фильтрации
    $vars = array_merge($vars, (array)$conds);
    // составим запрос для извлечения количества записей
    $selectlisting = $DOF->im('plans')->get_select_listing($conds);
    // посчитаем общее количество записей, которые нужно извлечь
    $pages->count = $DOF->storage('plans')->count_records_select($selectlisting);
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/list.php', $vars);
    echo $pagesstring;
}
$cdata      = new stdClass();
$cdata->dof = $DOF;
$search     = new dof_im_plans_search_form(null, $cdata);
$search->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>
