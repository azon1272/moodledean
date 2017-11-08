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


//проверяем доступ
if ( ! $DOF->im('plans')->is_access('editthemeplan:'.$linktype.'/my', $linkid) )
{// нет права редактировать свое планирование - проверим, можно ли редактировать вообще
    $DOF->im('plans')->require_access('editthemeplan:'.$linktype, $linkid, null, $linktype);
}

// создаем путь на возврат
$path = $DOF->url_im('plans','/themeplan/editthemeplan.php', 
    array('linktype'=> $linktype, 'linkid' => $linkid) + $addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра класса
    redirect($path);
}elseif ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    // print_object($formdata);//die;
    // print_object($formdata->datetype_group);die;
    // создаем объект для сохранения в БД
    $point = new stdClass();
    $point->name = trim($formdata->name);
    // @todo когда появится возможность задавать неограниченное количество родительских тем - 
    // изменить алгоритм сохранения
    $parentids   = array();
    if ( $formdata->parentid1 OR $formdata->parentid2 OR $formdata->parentid3 )
    {// если указана одна или несколько родительских тем
        $pointnames = array();
        if ( $formdata->parentid1 )
        {
            if ( ! $point->name )
            {// если название темы не было указано - то составим его из родительских тем
                $pointnames[] = $DOF->storage('plans')->get_field($formdata->parentid1, 'name');
            }
            $parentids[]   = $formdata->parentid1;
        }
        if ( $formdata->parentid2 )
        {
            if ( ! $point->name )
            {// если название темы не было указано - то составим его из родительских тем
                $pointnames[] = $DOF->storage('plans')->get_field($formdata->parentid2, 'name');
            }
            $parentids[]   = $formdata->parentid2;
        }
        if ( $formdata->parentid3 )
        {
            if ( ! $point->name )
            {// если название темы не было указано - то составим его из родительских тем
                $pointnames[] = $DOF->storage('plans')->get_field($formdata->parentid3, 'name');
            }
            $parentids[]   = $formdata->parentid3;
        }
        if ( ! $point->name )
        {
            $point->name = implode($pointnames, '. ');
        }
    }
    if ( isset($formdata->scale) AND ! empty($formdata->scale) )
    {// есть шкала - записываем, иначе NULL
        $point->scale        = $formdata->scale;
    }
    $point->type         = $formdata->type;
    $point->directmap    = $formdata->directmap;
    // определим, какую дату сохранять: абсолютную или относительную
    if ( $formdata->datetype_group['datetype'] == 'absolute' )
    {// дата проведения - абсолютная. Ее нужно пересчитать в относительную
        // вычитаем из даты проведения дату начала периода или потока
        $point->reldate = $formdata->pinpoint_date - $formdata->begindate;
    }else
    {// дата относительная
        // переводим время проведения из недель и дней  в секунды
        $point->reldate = $formdata->reldate_group['reldate_weeks'] * 7 * 24 * 3600 +
                          $formdata->reldate_group['reldate_days']  * 24 * 3600 +
                          $formdata->reldate_group['reldate_hours'] * 3600;
    }
    
    // сохраняем крайнюю дату сдачи
    $point->reldldate = $formdata->relddate_group['relddate_weeks'] * 7 * 24 * 3600 +
                        $formdata->relddate_group['relddate_days']  * 24 * 3600 +
                        $formdata->relddate_group['relddate_hours'] * 3600;
    // проверяем на всякий случай правильность привязок еще раз
    if ( ! isset($formdata->linktype) OR ! isset($formdata->linkid) )
    {//такого быть не должно - сообщим об этом
        $DOF->print_error($DOF->get_string('nolink','plans'));
    }
    //запомнили тип и id привязки
    $point->linktype = $formdata->linktype;
    $point->linkid   = $formdata->linkid;
    // заполняем данные по домашнему заданию
    $point->homework = $formdata->homework;
    // переводим часы и минуты в секунды
    $homeworkhours = 0;
    
    $hoursname   = 'homeworkhoursgroup[hours]';
    $minutesname = 'homeworkhoursgroup[minutes]';
    if ( isset($formdata->$hoursname) )
    {// собираем часы
        $homeworkhours += $formdata->$hoursname;
    }
    if ( isset($formdata->$minutesname) )
    {// собираем минуты
        $homeworkhours += $formdata->$minutesname;
    }
    $point->homeworkhours  = $homeworkhours;
    $point->note           = $formdata->note;
    // сохраняем раздел тематического планирования (если он указан)
    $point->plansectionsid = $formdata->plansectionsid;
    /*** @todo пока не используется
    $point->typesync = 
    $point->mdlinstance = 
    ***/ 
    if (isset($formdata->pointid) AND $formdata->pointid )
    {// класс редактировался - обновим запись в БД
        if ( $DOF->storage('plans')->update($point, $formdata->pointid) )
        {//обновление прошло успешно
            if ( $DOF->storage('planinh')->upgrade_point_links($formdata->pointid, $parentids) )
            {// обновление информации о наследовании контрольных точек прошло успешно
                redirect($DOF->url_im('plans','/themeplan/editthemeplan.php',
                    array('linktype' => $formdata->linktype,'linkid' => $point->linkid) + $addvars));
            }else
            {// не удалось обновить информацию о наследовании контрольных точек
                $error .= '<br/>'.$DOF->get_string('errorsave','plans').'<br/>';
            }
        }else
        {//сообщим об ошибке обновления
            $error .= '<br/>'.$DOF->get_string('errorsave','plans').'<br/>';
        }
    }else
    {// класс создавался
        // сохраняем запись в БД
        if( $id = $DOF->storage('plans')->insert($point) )
        {// все в порядке - сохраняем статус и возвращаем на страниу просмотра КТ
            $DOF->workflow('plans')->init($id);
            if ( $DOF->storage('planinh')->create_point_links($id, $parentids) )
            {// удалось создать связи наследования контрольных точек
                redirect($DOF->url_im('plans','/themeplan/editthemeplan.php',
                    array('linktype' => $formdata->linktype,'linkid' => $point->linkid) + $addvars));
            }else
            {// не удалось создать информацию о наследовании контрольных точек
                $error .=  '<br/>'.$DOF->get_string('errorsave','plans').'<br/>';
            }
        }else
        {//  КТ не сохранена - сообщаем об ошибке
            $error .=  '<br/>'.$DOF->get_string('errorsave','plans').'<br/>';
        }
    }
}
?>