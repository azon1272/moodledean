<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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
$cstreamid = required_param('id', PARAM_INT);
// проверяем права доступа
if ( ! $DOF->storage('cpassed')->is_access('edit:grade/own',$cstreamid) AND 
     ! $DOF->storage('cpassed')->is_access('edit:grade/auto',$cstreamid)) 
{// нет прав на автоматическое выставление - проверим на ручное
    $DOF->storage('cpassed')->require_access('edit:grade',$cstreamid);
}

$saveorderid = optional_param('saveorderid', 0, PARAM_INT);
$complete = optional_param('complete', 0, PARAM_BOOL);
$delete = optional_param('delete', 0, PARAM_BOOL);
$reoffset = optional_param('reoffset', 0, PARAM_BOOL);
$error = '';
if ( ! $cstream = $DOF->storage('cstreams')->get($cstreamid) )
{// не удалось найти поток
	print_error($DOF->get_string('not_found_cstream','journal', $cstreamid));
}
if ( $cstream->status != 'active' AND $cstream->status != 'completed' )
{// выводить ведомость можно только для активного и завершенного потока
	print_error($DOF->get_string('incorrect_status_cstraem','journal'));
}

$DOF->modlib('nvg')->add_level($DOF->get_string('itog_grades', 'journal'), $DOF->url_im('journal','/itog_grades/edit.php?id='.$cstreamid,$addvars));
if ( $saveorderid )
{// передан id приказа - надо что-то с ним делать
	$itog_grades = new dof_im_journal_order_itog_grades ($DOF,null);
	if ( $delete )
	{// сказано что приказ надо удалить
		if ( ! $itog_grades->is_signed($saveorderid) )
		{// если его еще не подписали - удаляем
			$DOF->storage('orders')->delete($saveorderid);
		}
	}else
	{// надо сохранить оценки
	    if ( ! $itog_grades->sign_and_execute_order_itog_grade($saveorderid) )
	    {// не смогли исполнить приказ - это очень плохо
	    	$error .= '<br><b style=" color:red; ">'.$DOF->get_string('error_order_execute','journal').'</b><br>';
	    }else
	    {// все прошло успешно - запомним это
	    	$save = true;
	    	$error .= '<br><b style=" color:green; ">'.$DOF->get_string('grades_successful_saved','journal').'</b><br>';
	    }
	}
}
if ( $complete ) 
{// необходимо завершить поток
	$complete_cstream=true;
	if ( $saveorderid AND ! $delete AND empty($save) )
	{// приказ был, но по каким-то причинам не исполнился
		// нельзя завершать поток
	    $complete_cstream=false;
	}
    if ( $complete_cstream AND $DOF->storage('cstreams')->set_status_complete($cstreamid))
	{// если завершать можно и мы завершили - все хорошо
		$error .=  '<br><b style=" color:green; ">'.$DOF->get_string('cstream_complete','journal').'</b><br>';
	}else
	{// не получилось - сообщим об этом
		$error .=  '<br><b style=" color:red; ">'.$DOF->get_string('error_save_cstream','journal').'</b><br>';
	}
}
// извлечем еще раз уже измененный поток
$cstream = $DOF->storage('cstreams')->get($cstreamid);
$customdata = new stdClass();
$customdata->reoffset = $reoffset;
$customdata->cstreamid = $cstreamid;
$customdata->cstreamstatus = $cstream->status;
// запоминаем данные для приказа
$customdata->teacherid = $cstream->teacherid;
$customdata->ageid = $cstream->ageid;
if ( isset($cstream->programmitemid) AND 
     $item = $DOF->storage('programmitems')->get($cstream->programmitemid) )
{// если у потока есть дисциплина, запоминаем ее
    $customdata->programmitemid = $cstream->programmitemid;
    $customdata->scale = $item->scale;//шкала оценок
    $customdata->mingrade = $item->mingrade;//минимальная положительная оценка
}else
{// нет дисцыплины - выставим пустые значения
    $customdata->programmitemid = 0;
    $customdata->scale = "";
    $customdata->mingrade = "";
}
$customdata->dof    = $DOF;
// подключаем методы вывода формы
$form = new dof_im_journal_edit_form($DOF->url_im('journal', '/itog_grades/edit.php?id='.$cstreamid,$addvars),$customdata);
//подключаем обработчик формы
include($DOF->plugin_path('im','journal','/itog_grades/process_form.php')); 

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
                        
if ( isset($report) AND $report )
{// надо распечатать ведомость
	include($DOF->plugin_path('im','journal','/itog_grades/view_itoggrades.php'));
}else
{// печатаем форму
    echo '<br><a href="'.$DOF->url_im('journal','/group_journal/index.php?csid='.$cstreamid,$addvars).'">'.
                         $DOF->get_string('return_on_cstream', 'journal').' '.
                         $cstream->name.'</a>';// печатаем форму
    //вывод сообщений об ошибках из обработчика
    echo $error;
    $form->display();
}
//print_object($form->get_data());
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>