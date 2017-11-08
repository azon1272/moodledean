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
// Copyright (C) 2011-2999  Evgeniy Gorelov (Евгений Горелов)             //
// Copyright (C) 2011-2999  Evgeniy Yaroslavtsev (Евгений Ярославцев)     //
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

/*
 * +id целевой дисц-ы
 * +вывести программу, назв дисц-ы
 * +вывести зависимости, которые есть в БД для этой дисц-ы
 * напротив каждой сделать кнопу удаления
 * +сделать селектор с набором оставшихся (еще не выведенных на экран и 
 * + не добавленных в зависимости) дисц-н и кн.сохранения, 
 * + после сохранения вернуться на эту же страницу
 * +вывести подробный заголовок для возможности уйти со страницы
 * пока не надо: сделать селектор с набором кодов функций из конфига, но 
 *  не понятно как задавать дисциплины для этих функций.
 */
// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

// Получаем id целевой дисциплины
$id = required_param('id', PARAM_INT);
// id удаляемой дисциплины-зависимости
$did = optional_param('del', null, PARAM_INT);

//*****************************************************************************
// Проверим полномочие
$DOF->storage('programmitems')->require_access('edit', $id);

//*****************************************************************************
// Получаем различные данные

$redirecturl = $DOF->url_im('programmitems','/pridepends.php', array_merge(array('id' => $id),$addvars) );
$err = array();

if ( $did )
{
    if (! $DOF->storage('pridepends')->delete($did) )
    {
        $err[] = $DOF->get_string('not_delete_dep', 'pridepends', $did, 'storage'); 
    }
}

// Получаем дисциплину
if (! $disc = $DOF->storage('programmitems')->get($id) )
{   // дисциплина не найдена
    $DOF->print_error('notfoundpitem',null,null,'storage','programmitems');
}

// Получаем программу
 if (! $pr = $DOF->storage('programms')->get($disc->programmid) )
 {   // Программа не найдена
     $DOF->print_error($DOF->get_string('program_not_found','programmitems'));
 }
 
 // Получаем список зависимостей для переданной дисциплины
 if (! $depends = $DOF->storage('pridepends')->get_records(array('programmitemid'=>$id)))
 {
     // Список зависимостей пуст
     $depends = array();
 }

//*****************************************************************************
// Готовим форму добавления зависимости и обрабатываем ее, если форма была подтверждена

// выборка всех доступных для добавления в зависимости дисциплин для данной дисциплины
$avalist = $DOF->storage('pridepends')->get_list_depends_select($id);
if (!$avalist)
{
    $avalist[0] = $DOF->get_string('err_get_discs_list', 'programmitems');
}

$customdata = new stdClass();
$customdata->dof = $DOF;
$customdata->id = $id;
$customdata->avalist = $avalist;

$depform = new dof_im_programmitems_pridepends_form($redirecturl, $customdata);

if ( $depform->is_submitted() AND $depform->is_validated() AND $customdata = $depform->get_data() )
{
    $dataobject = new stdClass();
    $dataobject->programmitemid = $id;
    // Пока задается напрямую, а потом будем через форму получать
    $dataobject->type = 'requirepritem';
    $dataobject->value = $customdata->pridepend;
    if (! $DOF->storage('pridepends')->insert($dataobject) )
    {
        $err[] = $DOF->get_string('not_insert_dep', 'pridepends', null,'storage');
    }
    else
    {
        redirect($redirecturl, '', 0);
    }
}
 
//*****************************************************************************
// Готовим таблицу с существующими зависимостями
$table = new stdClass();
$table->head = array(
        $DOF->get_string('dependtype', 'programmitems'),
        $DOF->get_string('pitem', 'programmitems'),
        $DOF->get_string('actions', 'programmitems')
);

foreach($depends as $depend)
{
    // если понадобится подавать в зависимость в поле value строку 
    // с несколькими id дисциплины, то для разбиения подходит ниже ниаписанный 
    // функционал, нужно только убрать break и как следует сохранить id в массив
    // (расшифровка названия)function depend disciplines
    $fdds = preg_split("/[\s-:]/", $depend->value);
    foreach($fdds as $fdd)
    {
        if (! empty($fdd))
        {
            $fdd = intval($fdd);
            // убрать break,если надо обработать массив
            // пока исп-ся только одна, поэтому берем первую 
            break;  
        }
    }
    
    // получили дисциплину-предусловие
    if (! $fdd = $DOF->storage('programmitems')->get($fdd))
    {   // дисциплина не найден
        $DOF->print_error($DOF->get_string('notfoundpitem','programmitems'));
    }
    
    $link = '<a href='.$DOF->url_im('programmitems','/pridepends.php',array_merge(array('id' => $id, 'del'=>$depend->id),$addvars)).'>
                <img src="'.$DOF->url_im('persons', '/icons/delete.png').'" 
                alt="'.$DOF->modlib('ig')->igs('delete').'" 
                title="'.$DOF->modlib('ig')->igs('delete').'">
            </a>&nbsp;';
    
    $dependtype = $DOF->get_string('dependtype:'.$depend->type, 'programmitems');
    
    $table->data[] = array($dependtype, $fdd->name, $link);
}

//*****************************************************************************
// Теперь пошел вывод всего подготовленного

//добавление уровня навигации
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'programmitems'), 
      $DOF->url_im('programmitems', '/list.php'),$addvars);
$DOF->modlib('nvg')->add_level($pr->name.'['.$pr->code.']', 
      $DOF->url_im('programms','/view.php?programmid='.$pr->id,$addvars)); 
$DOF->modlib('nvg')->add_level($DOF->get_string('pridepends', 'programmitems', $disc->name), 
      $DOF->url_im('programmitems','/pridepends.php?id='.$id,$addvars));
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// Выводим ошибки, какие у нас скопились за время обработки данных
if (!empty($err))
{
    foreach ($err as $errstr)
    {
        echo $errstr . '<br />';
    }
}

$strheading1 = $DOF->get_string('program', 'programmitems').' "'.$pr->name.'" ';
$strheading2 = $DOF->get_string('pitem', 'programmitems').' "'.$disc->name.'"';
echo '<h3 main>'.$strheading1.'</h3>'; // замена print_heading
echo '<h1 main>'.$strheading2.'</h1>'; // замена print_heading

// Вывод таблички с зависимостями
$DOF->modlib('widgets')->print_table($table);

// Вывод формы добавления зависимости
$depform->display();

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>