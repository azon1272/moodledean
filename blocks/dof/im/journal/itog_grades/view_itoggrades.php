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
$cstreamid = required_param('id', PARAM_INT);
// проверяем права доступа
if ( ! $DOF->storage('cpassed')->is_access('edit:grade/own',$cstreamid) AND 
     ! $DOF->storage('cpassed')->is_access('edit:grade/auto',$cstreamid)) 
{// нет прав на автоматическое выставление - проверим на ручное
    $DOF->storage('cpassed')->require_access('edit:grade',$cstreamid);
}

if ( empty($orderid) )
{// нету приказа - непонятно как вообще тогда мы сюда попали
	print_error($DOF->get_string('access_denied','journal'));
}
$order = $DOF->im('journal')->order('set_itog_grade');
$orderobj = $order->load($orderid);
//распечатываем ведомость в том виде, в котором она пойдет в приказ
// выводим заголовок
print '<div align="center">';
print '<b>Ведомость</b>';
// выводим таблицу с соценками
print '</div><br>
<table align="center" frame="box" rules="all" cellpadding="5">';
$num=1;
print '<tr>
	        <th>№</th>
		    <th>ФИО</th>
		    <th>Оценка</th>
	   </tr>';
foreach ( $orderobj->data->itoggrades as $grade)
{
	
    print	
       '<tr>
			<td align="right">'.$num.'</td>
			<td>'.$grade['fullname'].'</td>
			<td align="center">'.$grade['grade'].'</td>
		</tr>';
	$num++;
}
print '</table><br>';

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes='/itog_grades/edit.php?id='.$orderobj->data->cstreamid.'&saveorderid='.$orderid;
$linkno = $linkyes.'&delete=1';
if ( isset($cstream_complite) AND $cstream_complite )
{// если сказали завершить поток - завершаем
	$linkyes .= '&complete=1';
}

$DOF->modlib('widgets')->notice_yesno($DOF->get_string('save_itog_grades','journal'), $DOF->url_im('journal',$linkyes,$addvars),
                                                             $DOF->url_im('journal',$linkno,$addvars));

?>