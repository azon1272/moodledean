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
$planid = required_param('sectionid', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);

// проверки
// проверка на существование элемента планирования
if ( ! $planidobj = $DOF->storage('plansections')->get($planid) )
{
    $DOF->print_error($DOF->get_string('notfoundthemeplan','plans', $planid));
}
// проверка, что на данный темраздел нет активных темпланов
if ( $DOF->storage('plans')->get_records(array('plansectionsid'=>$planid,'status'=>'active')) )
{
    $DOF->print_error($DOF->get_string('notdelete','plans'));
}
// все проверки пройдены
$linktype = $planidobj->linktype;
$linkid   = $planidobj->linkid;
//проверяем доступ
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid);    
}
//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)
$DOF->im('plans')->nvg($linktype, $linkid,$addvars);     

// ссылки на подтверждение и непотдверждение сохранения приказа
$linkyes ='/deletesection.php?sectionid='.$planid.'&delete=1';
$linkno ='/themeplan/editthemeplan.php?linktype='.$linktype.'&linkid='.$linkid;
if ( $delete )
{// если сказали удалить - сменим статус
    $obj = new stdClass();
    $obj->status = 'canceled';
    $DOF->storage('plansections')->update($obj,$planid);
    redirect($DOF->url_im('plans',$linkno,$addvars));
}else
{
    //вывод на экран
    //печать шапки страницы
    $DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
                                   $DOF->url_im('plans','/list.php',$addvars));
    $DOF->modlib('nvg')->add_level($DOF->get_string('delete_plan', 'plans'),
                                   $DOF->url_im('plans','/deletesection.php?sectionid='.$planid,$addvars));
    $DOF->modlib('nvg')->print_header(NVG_MODE_PAGE);
    // вывод названия удаляемого элемента
    echo '<div align="center" style="color:red;font-size:25px;">' . $planidobj->name . '</div><br>';
    // спросим об удалении
    $DOF->modlib('widgets')->notice_yesno($DOF->get_string('confirmation_delete_plan','plans'), $DOF->url_im('plans',$linkyes,$addvars),
                                                                     $DOF->url_im('plans',$linkno,$addvars));
    //печать подвала
    $DOF->modlib('nvg')->print_footer(NVG_MODE_PAGE);
}

?>
