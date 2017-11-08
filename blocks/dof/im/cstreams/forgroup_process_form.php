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
/*
 * Обработчик формы создания потоков по группе и периоду
 */
// Подключаем библиотеки
require_once('lib.php');
require_once('form.php');

//проверка прав доступа
$DOF->storage('cstreams')->require_access('create');

if ( $form->is_submitted() AND $formdata = $form->get_data() AND $form->is_validated() )
{// если кнопки нажаты и данные из формы получены
    //print_object($formdata);die;
    $cstreamerrors = '';
    if ( isset($formdata->agedates) AND $formdata->agedates )
    {// в форме было сказано взять данные из периода
        $formdata->datebegin  = $DOF->storage('ages')->get_field($formdata->ageid,'begindate');
    }
    if ( isset($formdata->agedates) AND $formdata->agedates )
    {// в форме было сказано взять данные из периода
        $formdata->datebegin  = $DOF->storage('ages')->get_field($formdata->ageid,'begindate');
    } 
    if ( isset($formdata->chooseend) AND $formdata->chooseend == 'age' )
    {// в форме было сказано взять данные из периода
        $formdata->dateend  = $DOF->storage('ages')->get_field($formdata->ageid,'enddate');
    } 
    if ( isset($formdata->chooseend) AND $formdata->chooseend == 'pitem' )
    {// в форме было сказано взять из предмета
        // это сделает сам метод
        $formdata->dateend  = null;
    } 
    if ( isset($formdata->depcheck) AND $formdata->depcheck )
    {// в форме было сказано взять данные из периода
        $formdata->departmentid = 0;
    }
    // создаем потоки для группы
    // @todo оптимизировать алгоритм, передавая в функцию объект а не все его поля
    if ( ! $DOF->storage('cstreams')->create_cstreams_for_agroup($formdata->agroupid, 
                                        $formdata->ageid, $formdata->departmentid, 
                                        $formdata->datebegin,$formdata->dateend) )
    {// если в процессе создания потоков произошла ошибка - сообщим об этом
        $cstreamerrors .= $DOF->get_string('cstreams_are_not_created', 'cstreams').'<br/>';
    }
    
    if ( $cstreamerrors )
    {// если не удалось создать потоки, или подписать группы на потоки - сообщим об этом
        $DOF->print_error($cstreamerrors);
    }
    if ( ! $formdata->departmentid )
    {
        $formdata->departmentid = $addvars['departmentid'];
    }
    // определим, как получены данные: по ссылке или выставлены пользователем
    $urloptions = array('agroupid'     => $formdata->agroupid,
                        'ageid'        => $formdata->ageid,
                        'departmentid' => $formdata->departmentid);
    // раз дошли до сюда - значит потоки уже успешно создались - сообщим об этом
    $message = '<div align="center" style=" color:green; ">'.
        $DOF->get_string('creation_cstreams_for_agroup_success', 'cstreams').'</div>';
    //печать шапки страницы
    $DOF->modlib('nvg')->print_header(NVG_MODE_PORTAL);
    // перенаправляем пользователя на страницу списка учебных потоков, которые только что были созданы
    notice($message, $DOF->url_im('cstreams','/list.php', $urloptions));
}

?>