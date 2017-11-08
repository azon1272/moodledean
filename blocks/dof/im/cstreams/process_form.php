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
if ( $cstreamid )
{//проверка права редактировать поток
    if ( ! $DOF->storage('cstreams')->is_access('edit/plan', $cstreamid) ) 
    {// нельзя редактировать черновик - проверим, можно ли вообще редактировать
        $DOF->storage('cstreams')->require_access('edit', $cstreamid);
    }
}else
{//проверка права создавать поток
    $DOF->storage('cstreams')->require_access('create');
}
// создаем путь на возврат
$path = $DOF->url_im('cstreams','/list.php',$addvars);
if ( $form->is_cancelled() )
{//ввод данных отменен - возвращаем на страницу просмотра класса
    redirect($path);
} else if ( $form->is_submitted() AND confirm_sesskey() AND $formdata = $form->get_data() )
{//даные переданы в текущей сессии - получаем
    // создаем объект для сохранения в БД
    $cstream = new stdClass();
    if ( isset($formdata->ageeduweeks['checkeduweeks']) AND $formdata->ageeduweeks['checkeduweeks'] )
    {//если количество недель сказано брать из периода
        $cstream->eduweeks = $DOF->storage('ages')->get_field($formdata->ageid,'eduweeks');
        if ( $number = $DOF->storage('programmitems')->get_field($formdata->programmitemid,'eduweeks') )
        {// или из предмета, если указано там
            $cstream->eduweeks = $number;
        } 
    }else
    {//если нет - берем из формы
        $cstream->eduweeks = intval($formdata->ageeduweeks['eduweeks']);
    }
    if ( isset($formdata->pitemhours['checkhours']) AND $formdata->pitemhours['checkhours'] )
    {//если количество часов всего указано - возьмем из предмета
        $cstream->hours = $DOF->storage('programmitems')->get_field($formdata->programmitemid,'hours');  
    }else
    {//если нет - берем из формы
        $cstream->hours = intval($formdata->pitemhours['hours']);
    }
    if ( isset($formdata->pitemhoursweek['checkhoursweek']) AND $formdata->pitemhoursweek['checkhoursweek'] )
    {//если количество часов в неделю указано - возьмем из предмета
        $cstream->hoursweek = $DOF->storage('programmitems')->get_field($formdata->programmitemid,'hoursweek');  
    }else
    {//если нет - берем из формы
        $cstream->hoursweek = intval($formdata->pitemhoursweek['hoursweek']);
    }
    if ( ! isset($formdata->depid) )
    {// подразделение не указано - возьмем из предмета
        $cstream->departmentid = $DOF->storage('programmitems')->get_field($formdata->programmitemid, 'departmentid');
    }else
    {
        $cstream->departmentid = $formdata->depid;
    }
    // принимаем данные из формы
    $cstream->cstreamid      = $formdata->cstreamid;
    $cstream->ageid          = $formdata->ageid;
    if ( $formdata->cstreamid AND ! $DOF->is_access('datamanage') )
    {
        $cstream->appointmentid  = $formdata->appointmentid;
    }else
    {
        $cstream->programmitemid = $formdata->programmitemid;
        $cstream->appointmentid  = $formdata->appointmentid;
    }
    $cstream->teacherid = 0;
    if ( $cstream->appointmentid )
    {// если есть назначение - найдем учителя
        $cstream->teacherid = $DOF->storage('appointments')->
                               get_person_by_appointment($cstream->appointmentid)->id;
    }
    if ( isset($formdata->agedates) AND $formdata->agedates )
    {// в форме было сказано взять данные из периода
        $cstream->begindate  = $DOF->storage('ages')->get_field($formdata->ageid,'begindate');
        $cstream->enddate    = $DOF->storage('ages')->get_field($formdata->ageid,'enddate');
    }else
    {// в форме указаны собственные даты начала и окончания обучения
        
    }
    if ( isset($formdata->agedates) AND $formdata->agedates )
    {// в форме было сказано взять данные из периода
        $formdata->begindate  = $DOF->storage('ages')->get_field($formdata->ageid,'begindate');
    } 
    if ( isset($formdata->chooseend) AND $formdata->chooseend == 'age' )
    {// в форме было сказано взять данные из периода
        $formdata->enddate  = $DOF->storage('ages')->get_field($formdata->ageid,'enddate');
    } 
    if ( isset($formdata->chooseend) AND $formdata->chooseend == 'pitem' )
    {// в форме было сказано взять из предмета
        // это сделает сам метод
        $formdata->enddate  = $formdata->begindate + $DOF->storage('programmitems')->
                              get_field($formdata->programmitemid, 'maxduration');
    } 
    $cstream->begindate  = $formdata->begindate;
    $cstream->enddate    = $formdata->enddate;
    if ( $formdata->cstreamid AND ! $DOF->is_access('datamanage') )
    {
        $default->programmid = $formdata->programmid;
        $default->programmitemid = $formdata->programmitemid;
        $default->appointmentid = $formdata->appointmentid;
    }else
    {
        $default->appointmentid = $formdata->appointmentid;
    }
    // часов в неделю дистанционно
    $cstream->hoursweekdistance = $formdata->hoursweekdistance;    

    // часов в неделю очно    
    $cstream->hoursweekinternally = $formdata->hoursweekinternally;  
    // зарплатные коэффициенты     
    if ( $formdata->factor == 'sal' )   
    {// указан поправочный
        $cstream->salfactor = $formdata->salfactor; 
        $cstream->substsalfactor = 0; 
    }elseif ( $formdata->factor == 'substsal' )   
    {// указан замещающий
        $cstream->salfactor = 0; 
        $cstream->substsalfactor = $formdata->substsalfactor; 
    }
    if (isset($formdata->cstreamid) AND $formdata->cstreamid )
    {// класс редактировался - обновим запись в БД
        // подразделение менять нельзя
        unset($formdata->depid);
        if ( $DOF->storage('cstreams')->update($cstream, $formdata->cstreamid) )
        {
            redirect($DOF->url_im('cstreams','/view.php?cstreamid='.$formdata->cstreamid,$default));
        }else
        {
            $error .= '<br>'.$DOF->get_string('errorsavecstream','cstreams').'<br>';
        }
    }else
    {// класс создавался
        // сохраняем запись в БД
        if( $id = $DOF->storage('cstreams')->insert($cstream) )
        {// все в порядке - сохраняем статус и возвращаем на страниу просмотра класса
            $DOF->workflow('cstreams')->init($id);
            redirect($DOF->url_im('cstreams','/view.php?cstreamid='.$id,$default));
        }else
        {// класс выбран неверно - сообщаем об ошибке
            $error .=  '<br>'.$DOF->get_string('errorsavecstream','cstreams').'<br>';
        }
    }
}
?>