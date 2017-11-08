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
//id записи о теме занятия
//получаем linktype 
$linktype = required_param('linktype', PARAM_TEXT);
// получаем linkid
$linkid = required_param('linkid', PARAM_INT);
$delete = optional_param('fix', 0, PARAM_BOOL);
if ( $linktype === 'plan' )
{// переопределяем linktype=plan
    $linktypecs = 'cstreams';
}else
{// оставляем как есть
    $linktypecs = $linktype;
}


if ( ! $DOF->storage($linktypecs)->get($linkid) )
{// не найден элемент планирования
    $DOF->print_error($DOF->get_string('notfound','plans', $linkid));
}

//проверка прав доступа
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{// нет права редактировать свое планирование - проверим, можно ли редактировать вообще
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid, null, $linktype);
}
if ( ! $DOF->workflow('plans')->is_access('changestatus') )
{// нет прав или статус не тот
    print_error($DOF->get_string('edit_fix_active','plans'));
}

$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
        $DOF->url_im('plans','/list.php',$addvars));

//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)
$DOF->im('plans')->nvg($linktype, $linkid,$addvars);     

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes ='/fixall.php?linktype='.$linktype.'&linkid='.$linkid.'&fix=1';
$linkno ='/themeplan/editthemeplan.php?linktype='.$linktype.'&linkid='.$linkid;
if ( $delete )
{// если сказали удалить - сменим статус
    if ( $plans = $DOF->storage('plans')->get_records(array('linktype'=>$linktype,'linkid'=>$linkid,
                  'status'=>'active')) )
    {//если есть темпланы, которые можно подтвердить - подтверждаем их
        foreach ( $plans as $plan )
        {
            $DOF->workflow('plans')->change($plan->id,'fixed');
        }
    }
    redirect($DOF->url_im('plans',$linkno,$addvars));
}else
{
    //вывод на экран
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('fix_plan', 'plans'),
                                   $DOF->url_im('plans','/fixall.php?linktype='.$linktype.'&linkid='.$linkid,$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);

    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_fix_plan_all','plans'), $DOF->url_im('plans',$linkyes,$addvars),
                                                                     $DOF->url_im('plans',$linkno,$addvars));
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>
