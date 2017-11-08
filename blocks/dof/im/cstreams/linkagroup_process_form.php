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
require_once('form.php');
$cstreamid = required_param('cstreamid', PARAM_INT);

//проверка прав доступа
$DOF->storage('cstreams')->require_access('edit', $cstreamid);

if ( $agroup->is_submitted() AND $formdata = $agroup->get_data() )
{// если кнопки нажаты и данные из формы получены
    //print_object($formdata);die;
    if ( isset($formdata->save_no_link) )
    {// была нажата кнопка сохранения группы без привязки
        // создаем к ней привязку
        $link = new stdClass();
        $link->cstreamid  = $formdata->cstreamid;
        $link->agroupid   = $formdata->groupid;
        $link->agroupsync = $formdata->agroupsync;
        // добавляем в БД
        if ( $DOF->storage('cstreamlinks')->insert($link) )
        {// успешно - сообщим об этом
            $message = $DOF->get_string('successfulinsert','cstreams');
        }else
        {// не успешно - тоже сообщим
            $message = $DOF->get_string('nosuccessfulinsert','cstreams');
        }
    }
    if ( isset($formdata->save_link) )
    {// была нажата кнопка сохранения групп с привязкой
        foreach ($groups as $group)
        {// для каждой группы
            // выудим массив с данными
            $groupid = 'group'.$group->id;
            $groupdata = $formdata->$groupid;
            if ( isset($groupdata['del']) )
            {// если была поставлена галочка "удалить" - удаляем
                if ( $DOF->storage('cstreamlinks')->delete($groupdata['linkid']) )
                {// успешно - сообщим об этом
                    $message .= $DOF->get_string('successfuldelete','cstreams',$group->code).',';
                }else
                {// не успешно - тоже сообщим
                    $message .= $DOF->get_string('nosuccessfuldelete','cstreams',$group->code).',';
                }
            }elseif ( isset($formdata->$groupid) ) 
            {// если нет - обновляем
                // создаем привязку с новыми данными
                $link = new stdClass();
                $link->cstreamid  = $formdata->cstreamid;
                $link->agroupsync = $groupdata['agroupsync'];
                $olddata = $DOF->storage('cstreamlinks')->get($groupdata['linkid']);
                if ( $olddata->agroupsync != $groupdata['agroupsync'] )
                {// если данные изменились, то обновляем запись
                    if ( $DOF->storage('cstreamlinks')->update($link,$groupdata['linkid']) )
                    {// успешно - сообщим об этом
                        $message .= $DOF->get_string('successfulupdate','cstreams',$group->code).',';
                    }else
                    {// не успешно - тоже сообщим
                        $message .= $DOF->get_string('nosuccessfulupdate','cstreams',$group->code).',';
                    }
                }
            }
        }
    }
    // сделаем редирект для обновление формы
   redirect($DOF->url_im('cstreams','/linkagroup.php?cstreamid='.$cstreamid,array_merge($addvars,array('message'=>$message))));
}
?>