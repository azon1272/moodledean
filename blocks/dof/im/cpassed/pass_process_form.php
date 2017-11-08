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

if ( isset($pass) AND $formdata = $pass->get_data() )
{// если есть форма и данные получены - это добавление подписок
    if ( isset($formdata->addpass) )
    {// есть подписки на добавление - добавляем каждую
        foreach ($formdata->addpass as $contractid=>$addpass)
        {
            // формируем объект для вставки в БД
            $cpass = new stdClass();
            $cpass->cstreamid = $formdata->cstreamid;
            // ученик
            if  ( $contract = $DOF->storage('contracts')->get($contractid) AND
                                          $student = $DOF->storage('persons')->get($contract->studentid) )
            {// если ученик существует и у него имеется контракт - сохраняем
                $cpass->studentid = $contract->studentid;
            }
            // предмет
            if ( $cstream = $DOF->storage('cstreams')->get($formdata->cstreamid) AND 
                        $programmitem = $DOF->storage('programmitems')->get($cstream->programmitemid) )
            {// если существует поток и предмет потока - сохраняем
                $cpass->programmitemid = $cstream->programmitemid;
            }
            // подписка на программу
            if ( isset($programmitem) AND $DOF->storage('programms')->is_exists($programmitem->programmid) 
                                 AND $sbc = $DOF->storage('programmsbcs')->get_filter('contractid',$contractid,
                                               'agroupid',$formdata->agroupid,'programmid',$programmitem->programmid) )
            {// если существует программа и ученик подписан на данную программу - сохраняем
                $cpass->programmsbcid = $sbc->id;
            }
            // сообщения об ошибках
            if ( ! isset($cpass->programmitemid) )
            {// у потока нет предмета
                $message .= $DOF->get_string('programmitem_absent', 'cpassed', $student->sortname).','; 
            }
            if ($student->sortname)
            {// студент есть
                if ( ! isset($cpass->studentid) )
                {// но контракта нет
                    $message .= $DOF->get_string('contract_absent', 'cpassed', $student->sortname).','; 
                }
                if ( ! isset($cpass->programmsbcid) )
                {// и подпски на программу
                    $message .= $DOF->get_string('programmcbs_absent', 'cpassed', $student->sortname).','; 
                }
            }else
            {// нет студента - очень плохо
                $message .= $DOF->get_string('students_absent', 'cpassed', $student->sortname).',';
            }
            // сохраняем запись в БД
            if ( $DOF->storage('cpassed')->is_access('create') AND isset($cpass->studentid) 
                    AND isset($cpass->programmitemid) AND isset($cpass->programmsbcid) 
                          AND $id = $DOF->storage('cpassed')->insert($cpass) )
            {// все в порядке - сохраняем статус и выводим сообщение
                $DOF->workflow('cpassed')->init($id);
                $message .= $DOF->get_string('save_cpassed_true','cpassed').',';       
            }else
            {// подписка на курс выбрана неверно - сообщаем об ошибке
                $message .= $DOF->get_string('errorsavecpassed','cpassed').',';
            }
        }
    }
    // обновим форму и выведем сообщения
    redirect($DOF->url_im('cpassed','/list.php?cstreamid='.$formdata->cstreamid.'&departmentid='.$formdata->departmentid.
                                  '&agroupid='.$formdata->agroupid.'&message='.$message));
    
}else
{// формы нет - вылавливаем пост
    $formdata = $_POST;
    // добввлячем подразделение
    $formdata['departmentid'] = optional_param('departmentid',0,PARAM_INT);
    if ( ! empty($list) AND isset($formdata['delete']) AND isset($formdata['delpass']) AND is_array($formdata['delpass']))
    {// если есть список и нажата кнопка удаления - это галочки
        // запомним их
        $delpass = $formdata['delpass'];
        foreach($list as $cpass)
        {// переберем каждую
            if ( isset($cpass->id) AND is_int_string($cpass->id) AND isset($delpass[$cpass->id]) )
            {// галка указана
                if ( $DOF->workflow('cpassed')->is_access('changestatus', $cpass->id) AND 
                                        $DOF->workflow('cpassed')->change($cpass->id,'canceled') )
                {// если есть право менять статус - сменим ей статус и сообщим об этом
                    $message .= $DOF->get_string('cpassed_cancel', 'cpassed').',';
                }else
                {// не получилось - тоже сообщим
                    $message .= $DOF->get_string('cpassed_no_cancel', 'cpassed').',';
                }
            } 
        }
        
        // обновим форму и выведем сообщения
        redirect($DOF->url_im('cpassed','/list.php?cstreamid='.$formdata['cstreamid'].'&departmentid='.$formdata['departmentid'].'&message='.$message));
    }
    
}



?>