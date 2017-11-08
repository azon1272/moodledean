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

// Загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

/**
 * Отобразить информацию по персоне
 */
function imseq_show_contracts($list, $conds, $options = null, $return=false)
{
    
	global $DOF;
	// Собираем данные
	$data = array();
	if (!is_array($list))
	{
		return false;
	}
	$depid = optional_param('departmentid', 0, PARAM_INT);
    $addvars = array();
    $addvars['departmentid'] = $depid;
	foreach ($list as $obj)
	{
		$studname    = '';
        $link = ' <a href="'.$DOF->url_im('sel',"/contracts/view.php?id=".$obj->id,$addvars).'">'.
            '<img src="'.$DOF->url_im('sel', '/icons/view.png').'" 
            alt="'.$DOF->modlib('ig')->igs('view').
            '" title="'.$DOF->modlib('ig')->igs('view').'"></a>'; 
        $link .= ' <a href="'.$DOF->url_im('programmsbcs',"/list.php?contractid=".$obj->id,$addvars).'">'.
            '<img src="'.$DOF->url_im('sel', '/icons/programmsbcs.png').'" 
            alt="'.$DOF->get_string('view_programmsbcs', 'sel').
            '" title="'.$DOF->get_string('view_programmsbcs', 'sel').'"></a>'; 
		if ($student = $DOF->storage('persons')->get($obj->studentid))
		{
			$studname    = "{$student->sortname}";
            $link .= ' <a href="'.$DOF->url_im('recordbook', '/index.php?clientid='.$obj->studentid,$addvars).'">'.
            '<img src="'.$DOF->url_im('sel', '/icons/recordbook.png').'" 
            alt="'.$DOF->get_string('recordbook', 'sel').
            '" title="'.$DOF->get_string('recordbook', 'sel').'"></a>'; 
		}
	    $check = '';
        if ( is_array($options) )
        {// добавляем галочки
            $check = '<input type="checkbox" name="'.$options['prefix'].'_'.
             $options['listname'].'['.$obj->id.']" value="'.$obj->id.'"/>';
        }
		$data[] = array($check,$link,"<a href='".$DOF->url_im('sel',"/contracts/view.php?id={$obj->id}",$conds)."'>{$obj->num}</a>",
                     $DOF->storage('persons')->get_object_action($obj->studentid, 'view', $addvars),
                     dof_userdate($obj->date,'%d-%m-%Y'), 
                     $DOF->workflow('contracts')->get_name($obj->status));
	}
	// Рисуем таблицу
	$table = new stdClass();
	unset($conds['sort']);
    $table->head = array('', $DOF->modlib('ig')->igs('actions'),
        "<a href='".$DOF->url_im('sel',"/contracts/list.php?sort=num",$conds)."'>№</a>",
        "<a href='".$DOF->url_im('sel',"/contracts/list.php?sort=sortname",$conds)."'>{$DOF->get_string('fullname', 'sel')}</a>",
        "<a href='".$DOF->url_im('sel',"/contracts/list.php?sort=date",$conds)."'>{$DOF->get_string('date', 'sel')}</a>",
        //"<a href='".$DOF->url_im('sel',"/contracts/list.php?sort=departmentid",$conds)."'>{$DOF->get_string('date', 'sel')}</a>",
        "<a href='".$DOF->url_im('sel',"/contracts/list.php?sort=status",$conds)."'>{$DOF->modlib('ig')->igs('status')}</a>");


   
    $table->tablealign = "center";
	// $table->align = array ("center","center","center", "center", "center");
	// $table->wrap = array ("nowrap","","","");
	$table->cellpadding = 5;
	$table->cellspacing = 0;
	$table->width = '600';
	// $table->head = array('id', 'code');
	$table->data = $data;
	//передали данные в таблицу
	return $DOF->modlib('widgets')->print_table($table, $return);
}
?>
