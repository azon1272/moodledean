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
 * отображает одну запись по ее id 
 */




// Подключаем библиотеки
require_once('lib.php');
// Подключаем формы
require_once('form.php');
// получаем id'шники, для которых будем отображать учебную неделю
// получаем id потока
$cstreamid = optional_param('cstreamid',0,PARAM_INT);
// получаем id студента
$studentid = optional_param('studentid',0,PARAM_INT);
// получаем id учителя
$teacherid = optional_param('teacherid',null,PARAM_INT);
// получаем id группы
$agroupid  = optional_param('agroupid',0,PARAM_INT);

//проверяем доступ
$DOF->storage('schtemplates')->require_access('view');

//добавление уровня навигации
if ( isset($addvars['ageid']) AND $age = $DOF->storage('ages')->get($addvars['ageid']) )
{// на конкретный периож
    $DOF->modlib('nvg')->add_level($DOF->get_string('title_on', 'schedule', $age->name), 
    $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                               'ageid'=>$addvars['ageid'])) ); 
}else
{// без периода
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'schedule'), 
    $DOF->url_im('schedule','/index.php',array('departmentid'=>$addvars['departmentid'],
                                               'ageid'=>$addvars['ageid'])) ); 
} 
// @todo настоить навигацию в соответствии с поманными параметрами
if ( $cstreamid AND $DOF->storage('cstreams')->is_exists($cstreamid) )
{// отображаем недею для предмето-класса
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php?cstreamid='.$cstreamid,$addvars));
}elseif ( $teacherid AND $studentid AND $teacherid==$studentid 
          AND $DOF->storage('persons')->is_exists($teacherid) )
{// отображаем недею для персоны
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php?teacherid='.$teacherid.'&studentid='.$studentid,$addvars));
}elseif ( $teacherid AND $DOF->storage('persons')->is_exists($teacherid) )
{// отображаем недею для учителя
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php?teacherid='.$teacherid,$addvars));
}elseif ( $studentid AND $DOF->storage('persons')->is_exists($studentid) )
{// отображаем недею для студента
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php?studentid='.$studentid,$addvars));
}elseif ( $agroupid AND $DOF->storage('agroups')->is_exists($agroupid) )
{// отображаем недею для группы
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php?agroupid='.$agroupid,$addvars));
}elseif ( ! is_null($teacherid) AND $teacherid == 0 )
{// нам попался запрос на вакансию - ее тоже надо отобразить
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php?teacherid=0',$addvars));
}else
{// просто страница
    $DOF->modlib('nvg')->add_level($DOF->get_string('week_list_templates', 'schedule'),
    $DOF->url_im('schedule','/view_week.php',$addvars));
}

//вывод на экран

//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

$DOF->modlib('widgets')->print_heading($DOF->modlib('ig')->igs('you_from_timezone', dof_usertimezone()));

// TODO в будущем вынести ЭТО в стили
// тут мы выделяем другим цветом всю строку таблицы, отвечеющей нашим условиям
echo "
  <style type='text/css'>
    #mismatch_department { color: #009900; }   
    .mismatch_department .cell { color: #009900; }  
    #mismatch_age { color: #FF8C00; } 
	.mismatch_age .cell { color: #FF8C00; }  
	#mismatch_age_department { color: #800000; } 
	.mismatch_age_department .cell { color: #800000; } 
	#mismatch_timezone { color: #FF0033; } 
    .mismatch_timezone .cell { color: #FF0033; } 
  </style> ";

//ссылка на возвращение к рассписанию
if ( $DOF->storage('schtemplates')->is_access('view') )
{// если есть право создавать шаблон
    $link = '<a href='.$DOF->url_im('schedule','/index.php',$addvars).'>'.
    $DOF->get_string('return_on_schedule', 'schedule').'</a>';
    echo '<br>'.$link;
}
// ссылка на создание шаблона

if ( $DOF->storage('schtemplates')->is_access('create') AND $addvars['ageid'] )
{// если есть право создавать шаблон
    //добываем (золото, минералы, древесину) id периода
    $link = '<a href='.$DOF->url_im('schedule','/edit.php',$addvars).'>'.
    $DOF->get_string('new_template', 'schedule').'</a>';
    echo '<br>'.$link;
}
$load = new dof_im_schedule_master_make($DOF, $addvars['departmentid'], null, $addvars);
// вывод список шаблонов для переданного объекта
if ( $cstreamid AND $DOF->storage('cstreams')->is_exists($cstreamid) )
{// отображаем недею для предмето-класса
    $addvars['cstreamid'] = $cstreamid;
    // ссылка на создание шаблона
    if ( $DOF->storage('schtemplates')->is_access('create')  )
    {// если есть право создавать шаблон
        $link = '<a href='.$DOF->url_im('schedule','/edit.php',$addvars).'>'.
        $DOF->get_string('new_template_on_cstream', 'schedule').'</a>';
        echo '<br>'.$link;
    }
    $show = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);
    // отобразим недогруз
    echo '<br><br>'.$load->get_underload_cstream($cstreamid);
    echo '<br>'.$show->get_help_value_color();
    $cs_name =  $DOF->storage('cstreams')->get_field($cstreamid,'name');
    echo "<h2 align='center'><a href = ".$DOF->url_im('cstreams','/view.php?cstreamid='.$cstreamid,$addvars).">".$cs_name."</a></h2>";
    // получим массив дней недели
    $daynum = $DOF->modlib('refbook')->get_template_week_days();
    for( $i=1; $i<=7; $i++)
    {
        echo '<div align="center"><b>'.$daynum[$i].'</b></div>'; // день недели 
        if ( $table = $show->get_table_cstream($cstreamid,$i) )
        {// есть таблица - выведем ее
            echo '<br>'.$table.'<br>';
        }else
        {// скажем что на этот день шадлонов нет
            echo '<br><div align="center">'.$DOF->get_string('no_week_list_templates', 'schedule').'</div><br>';
        }
    }
}elseif ( $teacherid AND $studentid AND $teacherid==$studentid 
          AND $DOF->storage('persons')->is_exists($teacherid) )
{// отображаем недею для персоны
    // получим массив дней недели
    $daynum = $DOF->modlib('refbook')->get_template_week_days();
    for( $i=1; $i<=7; $i++)
    {
        echo '<div align="center"><b>'.$daynum[$i].'</b></div>'; // день недели 
        // @todo в перспективе будет сразу отображение для персоны как учителя и как ученика
        // но пока оно не нужно
        //echo '<br>'.$show->get_table_cstream($cstreamid,$i);
    }
}elseif ( $teacherid AND $DOF->storage('persons')->is_exists($teacherid) )
{// отображаем недею для учителя
    $addvars['teacherid'] = $teacherid;
    // ссылка на создание шаблона
    if ( $DOF->storage('schtemplates')->is_access('create') AND $addvars['ageid'] )
    {// если есть право создавать шаблон
        $link = '<a href='.$DOF->url_im('schedule','/edit.php',$addvars).'>'.
        $DOF->get_string('new_template_on_teacher', 'schedule').'</a>';
        echo '<br>'.$link.'<br>';
    }
    $show = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);
    echo '<br>'.$show->get_help_value_color();
    echo '<h2 align="center">'.$DOF->storage('persons')->get_fullname($teacherid).'</h2>';
    // получим массив дней недели
    $daynum = $DOF->modlib('refbook')->get_template_week_days();
    for( $i=1; $i<=7; $i++)
    {
        echo '<div align="center"><b>'.$daynum[$i].'</b></div>'; // день недели 
        if ( $table = $show->get_table_teacher($teacherid,$i) )
        {// есть таблица - выведем ее
            echo '<br>'.$table.'<br>';
        }else
        {// скажем что на этот день шадлонов нет
            echo '<br><div align="center">'.$DOF->get_string('no_week_list_templates', 'schedule').'</div><br>';
        }
    }
}elseif ( $studentid AND $DOF->storage('persons')->is_exists($studentid) AND $addvars['ageid'] )
{// отображаем недею для студента
    $addvars['studentid'] = $studentid;
    // ссылка на создание шаблона
    if ( $DOF->storage('schtemplates')->is_access('create') )
    {// если есть право создавать шаблон
        $link = '<a href='.$DOF->url_im('schedule','/edit.php',$addvars).'>'.
        $DOF->get_string('new_template_on_student', 'schedule').'</a>';
        echo '<br>'.$link.'<br>';
    }
    $show = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);
    echo '<br>'.$show->get_help_value_color();
    echo '<h2 align="center">'.$DOF->storage('persons')->get_fullname($studentid).'</h2>';
    // получим массив дней недели
    $daynum = $DOF->modlib('refbook')->get_template_week_days();
    for( $i=1; $i<=7; $i++)
    {
        echo '<div align="center"><b>'.$daynum[$i].'</b></div>'; // день недели 
        if ( $table = $show->get_table_student($studentid ,$i) )
        {// есть таблица - выведем ее
            echo '<br>'.$table.'<br>';
        }else
        {// скажем что на этот день шадлонов нет
            echo '<br><div align="center">'.$DOF->get_string('no_week_list_templates', 'schedule').'</div><br>';
        }
    }
}elseif ( $agroupid AND $DOF->storage('agroups')->is_exists($agroupid) )
{// отображаем недею для группы
    $addvars['agroupid'] = $agroupid;
    // ссылка на создание шаблона
    if ( $DOF->storage('schtemplates')->is_access('create') )
    {// если есть право создавать шаблон
        $link = '<a href='.$DOF->url_im('schedule','/edit.php',$addvars).'>'.
        $DOF->get_string('new_template_on_group', 'schedule').'</a>';
        echo '<br>'.$link.'<br>';
    }
    $show = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);
    echo '<br>'.$show->get_help_value_color();
    echo '<h2 align="center">'.$DOF->storage('agroups')->get_field($agroupid,'name').'['.
         $DOF->storage('agroups')->get_field($agroupid,'code').']'.'</h2>';
    // получим массив дней недели
    $daynum = $DOF->modlib('refbook')->get_template_week_days();
    for( $i=1; $i<=7; $i++)
    {
        echo '<div align="center"><b>'.$daynum[$i].'</b></div>'; // день недели 
        if ( $table = $show->get_table_agroup($agroupid ,$i) )
        {// есть таблица - выведем ее
            echo '<br>'.$table.'<br>';
        }else
        {// скажем что на этот день шадлонов нет
            echo '<br><div align="center">'.$DOF->get_string('no_week_list_templates', 'schedule').'</div><br>';
        }
    }
}elseif ( ! is_null($teacherid) AND $teacherid == 0 )
{// нам попался запрос на вакансию - ее тоже надо отобразить
    $addvars['teacherid'] = 0;
    $show = new dof_im_schedule_display($DOF, $addvars['departmentid'],$addvars);
    echo '<br>'.$show->get_help_value_color();
    echo '<h2 align="center">'.$DOF->get_string('cstreams_no_teacher', 'schedule').'</h2>';
    // получим массив дней недели
    $daynum = $DOF->modlib('refbook')->get_template_week_days();
    for( $i=1; $i<=7; $i++)
    {
        echo '<div align="center"><b>'.$daynum[$i].'</b></div>'; // день недели 
        if ( $table = $show->get_table_teacher(0,$i) )
        {// есть таблица - выведем ее
            echo '<br>'.$table.'<br>';
        }else
        {// скажем что на этот день шадлонов нет
            echo '<br><div align="center">'.$DOF->get_string('no_week_list_templates', 'schedule').'</div><br>';
        }
    }
}

//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);
?>