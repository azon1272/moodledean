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

// подключаем библиотеки верхнего уровня
require_once('lib.php');

//получаем linktype 
$linktype = required_param('linktype', PARAM_TEXT);
// получаем linkid
$linkid = required_param('linkid', PARAM_INT);

if ( $linktype === 'plan' )
{// переопределяем linktype=plan
    $linktypecs = 'cstreams';
}else
{// оставляем как есть
    $linktypecs = $linktype;
}

// подключаем библеиотеки и стили
$DOF->modlib('widgets')->js_init('show_hide');

if ( ! $DOF->storage($linktypecs)->get($linkid) )
{// не найден элемент планирования
    print_error($DOF->get_string('notfound','plans', $linkid));
}
// определим права доступа
if ( ! $DOF->im('plans')->is_access('viewthemeplan/my', $linkid) )
{// если нет права видеть свои планы - проверим есть ли право их видеть вообще
   $DOF->im('plans')->require_access('viewthemeplan', $linkid, null, $linktype);
}
// наследуем класс
$name = new dof_im_plans_themeplan_view($DOF,$linktype,$linkid);

//вывод на экран
//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)
$DOF->im('plans')->nvg($linktype, $linkid, $addvars);
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);

// указываем в каком планировании мы сейчас находимся
echo '<br><div align="center" style="font-size:25px;">'.$DOF->get_string('viewpoint_'.$linktype,'plans').'</div>';
                     
if ( $linktypecs != 'cstreams' )
{
    if ( $DOF->im('plans')->is_access('editthemeplan:'.$linktype, $linkid) OR 
         $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
    {// ссылка на страницу редактирования
        echo "<br> <a href='".$DOF->url_im('plans',"/themeplan/editthemeplan.php?linktype={$linktype}&linkid={$linkid}",$addvars)."'>"
                        .$DOF->get_string('editpoint','plans')."</a> ";
    }
    
}else 
{
    if ( $DOF->im('plans')->is_access('editthemeplan:'.$linktype, $linkid) OR 
         $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
    {// ссылка на страницу редактирования
        echo "<br> <a href='".$DOF->url_im('plans',"/themeplan/editthemeplan.php?linktype={$linktype}&linkid={$linkid}",$addvars)."'>"
                        .$DOF->get_string('editpoint','plans')."</a> ";        
    }
    // проверка прав доступа'
    if ( $DOF->im('journal')->is_access('view_journal/own', $linkid) OR 
         $DOF->im('journal')->is_access('view_journal', $linkid) )
    {// если предмето поток - покажем ссылку на журнал
         echo '<br><a href="'.$DOF->url_im('journal', '/group_journal/index.php?csid='.$linkid,$addvars).'">'
                        .$DOF->get_string('journal','plans')."</a><br> ";       
    }

}

// таблица для показа пояснит записки
if ( $linktype != 'ages' )
{// пояснительная записка только для потока и предмета
    echo '<table align="center" cellpadding="4">
    <tr>
    <td><span class="hideBtn"> &nbsp;</span></td>
    <td><a href="" onClick="return dof_modlib_widgets_js_hide_show(\'hideCont\',\'hideBtn\');">'
        .$DOF->get_string('planatory','plans').'</a></td>
    </tr>
    </table>';
    
    // вывод пояснительной записки
    echo '<div class="hideCont" >';
    //echo '<div id="zapiska" style="display: none;">';
    print($name->get_table_note($addvars));
    echo "</div>";
}
echo "<br>";
// вывод темплана
print($name->get_table_themeplan($addvars));
echo "<br>";
// вывод темразделов
print($name->get_table_plansections($addvars));
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>