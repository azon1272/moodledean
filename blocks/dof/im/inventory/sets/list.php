<?PHP
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

$conds = array();
// Получаем параметры выбирки и сортировки
$sort   = optional_param('sort', null, PARAM_ALPHA);
// статус
$conds['status']     = optional_param('status', null, PARAM_ALPHA);
// Категория 
$conds['invcategoryid'] = optional_param('invcategoryid', null, PARAM_INT);
// подразделение возьмем из get-параметра
$conds['departmentid'] = $addvars['departmentid'];
// тип отображения: только выданные, только не выданные
$conds['displaytype'] = optional_param('displaytype', null, PARAM_ALPHA);
// комплекты, выданные одному человеку
$conds['personid'] = optional_param('displaytype', null, PARAM_INT);

// ограничение на количество записей при отображении
$limitnum  = optional_param('limitnum', null, PARAM_INT);
$limitfrom = optional_param('limitfrom', null, PARAM_INT);

// помещаем в массив все параметры страницы, 
// чтобы навигация по списку проходила корректно
$sortmas = array('limitnum'  => $limitnum,
                  'limitfrom' => $limitfrom,
                  'sort'      => $sort);

$invcount = 0;
if ( ! empty($conds['invcategoryid'])  )
{
    // считаем общее количество записей, которые надо вывести
    $invcount = $DOF->storage('invsets')->get_listing($conds,null,null,'','*',true);
}
// создадим массив, который хранит кол-во элементов в той или иной вкладке
$count_tab = array();
// тип отображения
$display = array('all','granted','available');
foreach ( $display as $value )
{
    $conds1 = $conds;
    $conds1['displaytype'] = $value; 
    $count_tab[$value] = $DOF->storage('invsets')->get_listing($conds1,null,null,'','*',true);  
}

// подключаем класс для постраничного вывода данных
$pages = $DOF->modlib('widgets')->pages_navigation('inventory',$invcount,$limitnum,$limitfrom);

$list = '';
if ( ! empty($conds['invcategoryid'])  )
{
    // получаем список оборудования, в зависимости от условий
    $list = $DOF->storage('invsets')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                        $pages->get_current_limitnum(),$sort);
}

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав на просмотр
$DOF->storage('invsets')->require_access('view',NULL,NULL,$addvars['departmentid']);

// доп навигация по категориям
$param = $addvars+$conds;
echo $DOF->im('inventory')->additional_nvg('/sets/list.php',$param);

// Вкладки
echo $DOF->im('inventory')->print_tab($addvars,'sets'); 
// Второй уровень вкладок - фильтр оборудования по статусу
echo $DOF->im('inventory')->print_set_tabs($addvars + $conds, $conds['displaytype'], $count_tab);



// Выводим список оборудования
$DOF->im('inventory')->print_invsets_table($list, $addvars+$sortmas);

// постраничная навигация:
echo $pages->get_navpages_list('/sets/list.php', $addvars+$conds+$sortmas);

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>