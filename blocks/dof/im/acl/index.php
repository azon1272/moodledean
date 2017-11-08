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

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'acl'), 
                     $DOF->url_im('acl','/index.php'),$addvars);
// какое количество строк таблицы выводить на экран
$limitnum = $DOF->modlib('widgets')->get_limitnum_bydefault();
$limitnum = optional_param('limitnum', $limitnum, PARAM_INT);
// начиная с какого номера записи показывать ее
$limitfrom = optional_param('limitfrom', 0, PARAM_INT);

// тип вкладок 
$type = optional_param('type', null, PARAM_INT);
$addvars['type'] = $type;
$typelist = optional_param('typelist', null, PARAM_INT);
$addvars['typelist'] = $typelist;
$aclwarrantid = optional_param('aclwarrantid', null, PARAM_TEXT);
$addvars['aclwarrantid'] = $aclwarrantid;
// сортировка
//$orderby = optional_param('orderby', null, PARAM_TEXT);
//$addvars['orderby'] = $orderby;
$ordercol = optional_param('ordercol', null, PARAM_TEXT);
$addvars['ordercol'] = $ordercol;

// класс отображения вкладок
$tabs = new dof_im_aclwarrants_display($DOF, $addvars['departmentid'], $addvars);
$tabs->get_nvg($type,$typelist);

//вывод на экран
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

if (isset($addvars['typelist']))
{// дошли до таблиц - подключаем навигацию
    echo $tabs->get_main_page_tabs($addvars['type'], $addvars['typelist']);
    
    echo $tabs->get_tablelist_data($type,$typelist,$limitnum,$limitfrom,$aclwarrantid); 
    // получаем количество записей для определенной вкладки 
    $count = $tabs->get_tablelist_data($type,$typelist,$limitnum,$limitfrom,$aclwarrantid,true); 
    // подключаем класс для вывода страниц
    $pages = $DOF->modlib('widgets')->pages_navigation('acl', $count, $limitnum, $limitfrom);
    
    $vars = array('limitnum'  => $pages->get_current_limitnum(),
            'limitfrom' => $pages->get_current_limitfrom());
    
    // выводим строку со списком страниц
    $pagesstring = $pages->get_navpages_list('/index.php', array_merge($vars,$addvars));
    echo $pagesstring;
    
}else 
{
    echo $tabs->get_main_page_tabs($addvars['type'], $addvars['typelist']);
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>