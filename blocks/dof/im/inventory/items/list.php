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
require_once('form.php');

$conds = array();
// Получаем параметры выбирки и сортировки
$sort   = optional_param('sort', 'name', PARAM_ALPHA);
// статус
$conds['status']     = optional_param('status', null, PARAM_ALPHA);
// Комплект
$conds['invsetid']   = optional_param('invsetid', null, PARAM_INT);
// Категория оборудования
$conds['invcategoryid'] = optional_param('invcategoryid', null, PARAM_INT);
// название или инвентарный номер
$conds['nameorcode'] = optional_param('nameorcode', null, PARAM_TEXT);
// подразделение возьмем из get-параметра
$conds['departmentid'] = $addvars['departmentid'];
// тип отображения 
$conds['displaytype'] = optional_param('displaytype', null, PARAM_TEXT);

// ограничение на количество записей при отображении
$limitnum  = optional_param('limitnum', $DOF->modlib('widgets')->get_limitnum_bydefault(), PARAM_INT);
$limitfrom = optional_param('limitfrom', 1, PARAM_INT);

// помещаем в массив все параметры страницы, 
// чтобы навигация по списку проходила корректно
$sortmas = array('limitnum'  => $limitnum,
                  'limitfrom' => $limitfrom,
                  'sort'      => $sort);

$invcount = 0;
if ( ! empty($conds['invcategoryid']) )
{
    // считаем общее количество записей, которые надо вывести
    $invcount = $DOF->storage('invitems')->get_listing($conds,null,null,'','*',true);
}
// создадим массив, который хранит кол-во элементов в той или иной вкладке
$count_tab = array();
// тип отображения
$display = array('all','free','in_set','n_a');
foreach ( $display as $value )
{
    $conds1 = $conds;
    $conds1['displaytype'] = $value; 
    $count_tab[$value] = $DOF->storage('invitems')->get_listing($conds1,null,null,'','*',true);    
}



// подключаем класс для постраничного вывода данных
$pages = $DOF->modlib('widgets')->pages_navigation('inventory',$invcount,$limitnum,$limitfrom);

$list = array();
if ( ! empty($conds['invcategoryid']) )
{
    // получаем список оборудования, в зависимости от условий
    $list = $DOF->storage('invitems')->get_listing($conds,$pages->get_current_limitfrom()-1, 
                        $pages->get_current_limitnum(),$sort);
    
}

// Создаем форру поиска
$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->departmentid = $conds['departmentid'];

$searchform = new block_dof_im_inventory_item_search_form(
    $DOF->url_im('inventory','/items/list.php',$addvars+$conds), $customdata);

$searchform->process($addvars+$conds);    

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// проверка прав на просмотр
$DOF->storage('invsets')->require_access('view',NULL,NULL,$addvars['departmentid']);

// доп навигация по категориям
$param = $addvars+$conds;
//удалим код поиска, если он есть
if ( isset($param['nameorcode']) )
{
    unset($param['nameorcode']);
}
echo $DOF->im('inventory')->additional_nvg('/items/list.php',$param);

// распечатеам вкладки
echo $DOF->im('inventory')->print_tab($addvars,'items');
// Второй уровень вкладок - фильтр оборудования по статусу
echo $DOF->im('inventory')->print_item_tabs($addvars+$conds, $conds['displaytype'], $count_tab);

// Выводим список оборудования
$DOF->im('inventory')->print_invitems_table($list, $addvars+$sortmas+$conds);

// постраничная навигация:
echo $pages->get_navpages_list('/items/list.php', $addvars+$conds+$sortmas);

if ( ! empty($conds['invcategoryid']) )
{// отображение формы поиска (искать можно только внутри конкретной категории)
    $searchform->set_data($conds);
    $searchform->display();
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>