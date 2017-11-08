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

// подключаем библиотеки верхнего уровня и формы
require_once('lib.php');
require_once('form.php');

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
if ( $linktype == 'ages' )
{// записка для периода не допускается
    print_error($DOF->get_string('err_forbide_ages', 'plans'));
}
if ( ! $rec = $DOF->storage($linktypecs)->get($linkid) )
{// не найден элемент учебного плана';
    print_error($DOF->get_string('notfound',$linktypecs, $linkid));
}
// определим права доступа
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{// нет права редактировать свое планирование - проверим, можно ли редактировать вообще
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid, null, $linktype);
}
// данные для формы
$customdata = new stdClass();
if ( isset($USER->sesskey) )
{//сохраним идентификатор сессии
    $customdata->sesskey = $USER->sesskey;
}else
{//идентификатор сессии не найден
    $customdata->sesskey = 0;
}
$customdata->dof      = $DOF;
$customdata->linkid   = $linkid;
$customdata->linktype = $linktypecs;
// путь на себя
$action = $DOF->url_im('plans',"/editexplanatory.php?linktype=".$linktype."&linkid=".$linkid,$addvars);
// путь на возврат
$action1 = $DOF->url_im('plans',"/themeplan/editthemeplan.php?linktype=".$linktype."&linkid=".$linkid,$addvars);
$error = '';

// определим форму
$form = new dof_im_plans_editplanatory_form($action,$customdata);
// возврат делаем, откуда и пришли 
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу редактирования
    redirect($action1);
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
   // обновляем  запись в БД
   $newrec->explanatory = $formdata->textname;
   if ( ! $DOF->storage($linktypecs)->update($newrec, $linkid) )
   {// запись не обновилась - ошибка
       $error =  '<br>'.$DOF->get_string('errorsave','plans').'<br>';
   }
   if ( empty($error))
   {
        redirect($action1);  
   }
}

//вывод на экран
//добавление уровня навигации для ВСЕХ КТ(пронраммы, периоды, дисциплины)
$DOF->modlib('nvg')->add_level($DOF->get_string('title', 'plans'),
        $DOF->url_im('plans','/list.php',$addvars));
$DOF->im('plans')->nvg($linktype, $linkid,$addvars);
$DOF->modlib('nvg')->add_level($DOF->get_string('editplanatory', 'plans'), 
    $DOF->url_im('plans','/editexplanatory.php?linktype='.$linktype.'&linkid='.$linkid,$addvars));
//печать шапки страницы
$DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
// вывод ошибок
echo $error;
$form->display();
//печать подвала
$DOF->modlib('nvg')->print_footer(NVG_MODE_PORTAL);

?>