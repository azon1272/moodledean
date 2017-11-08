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



// Подключаем библиотеки
require_once('lib.php');
//id записи о потоке
$cstreamid = required_param('cstreamid', PARAM_INT);
$confirmation = optional_param('confirmation', 0, PARAM_BOOL);

if ( ! $cstream = $DOF->storage('cstreams')->get($cstreamid) )
{// поток не найден
    $DOF->print_error('no_cstream_found', '', $cstreamid, 'im', 'plans');
}
$linktype = 'plan';
$linkid   = $cstreamid;
// @todo - какие права проверять?
//проверка прав доступа
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{// нет права редактировать свое планирование - проверим, можно ли редактировать вообще
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid, null, $linktype);
}

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
        $DOF->url_im('plans','/list.php',$addvars));
//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)
$DOF->im('plans')->nvg($linktype, $linkid,$addvars);     

// ссылки на подтверждение и непотдверждение наследования
$linkyes ='/successionplan.php?cstreamid='.$cstreamid.'&confirmation=1';
$linkno ='/themeplan/editthemeplan.php?linktype='.$linktype.'&linkid='.$linkid;
if ( $confirmation )
{// если сказали наследовать - наследуем
    if ( $DOF->storage('plans')->succession_pitem_plan($cstream) )
    {// все хорошо - возвращаем на УТП
        redirect($DOF->url_im('plans',$linkno,$addvars));
    }else
    {// нет - скажем что что-то не получилось и даем ссылку возвращения на УТП
        echo '<div align="center">'.$DOF->get_string('failed_succession_plan_pitem','plans').'</div>';
        echo '<div align="center"><a href='.$DOF->url_im('plans',$linkno,$addvars)."'>"
                        .$DOF->modlib('ig')->igs('back').'</a></div>';
    }
}else
{//вывод на экран 
    //найдем элементы планирования на предмет
    $pitemplans = $DOF->storage('plans')->get_records(array('linktype'=>'programmitems',
                  'linkid'=>$cstream->programmitemid,'status'=>'active'));
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('succession_plan_pitem', 'plans'),
                                   $DOF->url_im('plans','/successionplan.php?cstreamid='.$cstreamid,$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    if ( $pitemplans )
    {// если элементы есть
        // выводим их
        echo '<div>'.$DOF->get_string('element_succession_plan_pitem', 'plans', $cstream->name).':</div><br>';
        echo '<ul>';
        foreach ( $pitemplans as $pitemplan )
        {// для каждого в список
            echo '<li>'.$pitemplan->name.'</li>';
        }
        echo '</ul>';
        // спросим о наследовании
        $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_succession_plan','plans'), $DOF->url_im('plans',$linkyes,$addvars),
                                                                         $DOF->url_im('plans',$linkno,$addvars));
    }else
    {// элементов - нет, наследовать нечего
        // сообщим об этом и выведем ссылку на возврат
        echo '<div align="center">'.$DOF->get_string('notfoundpoint','plans').'</div><br>';
        echo '<div align="center"><a href='.$DOF->url_im('plans',$linkno,$addvars)."'>"
                        .$DOF->modlib('ig')->igs('back').'</a></div>';
    }
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>
