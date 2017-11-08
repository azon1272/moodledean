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

// Подключение библиотеки
global $DOF;
require_once($DOF->plugin_path('storage','orders','/baseorder.php'));


/**
 * Класс для создания приказов
 * о выставлении текущей посещаемости
 */
class dof_im_learningorders_order_transfer extends dof_storage_orders_baseorder
{

    public function plugintype()
    {
        return 'im';
    }

    public function plugincode()
    {
        return 'learningorders';
    }

    public function code()
    {
        return 'transfer';
    }

    protected function execute_actions( $order )
    { // приказ пока не исполняется
        

        // перед проверкой данных и исполнением приказа учеличим лимиты памяти
        // чтобы скрипт не закончился по таймауту на середине
        dof_hugeprocess();
        
        //получили оценки из приказа
        if ( ! $this->check_order_data($order) )
        { //не получили оценки из приказа
            $this->log_string(date('d.m.Y H:i:s', time()) . "\n");
            $this->log_string('Order data is not correct' . "\n\n");
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s', time()) . "\n");
        $order->data->orderid = $order->id;
        // удаляем оценки
        $rez = true;
        if ( isset($order->data->groups) )
        {
            foreach ( $order->data->groups as $id => $group )
            {
                if ( $group->oldagenum != $group->newagenum )
                { // если параллель не совпадает - переформировываем группу
                    if ( $this->dof->storage('agroups')->get_field($id, 'status') != 'plan' )
                    { // меняем статус группы на формирующуюся
                        $this->log_string(
                            'Change status group ' . $group->name . '(' . $id . ') on plan... ');
                        if ( $result = $this->dof->workflow('agroups')->change($id, 'plan') )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                    }
                }
                $agroup = new stdClass();
                $agroup->agenum = $group->newagenum;
                $this->log_string('Update group ' . $group->name . '(' . $id . ')... ');
                if ( $result = $this->dof->storage('agroups')->update($agroup, $id) )
                { // успешный результат
                    $this->log_string('successfully' . "\n");
                } else
                { // неуспешный
                    $this->log_string('fails' . "\n");
                }
                $rez = $rez and $result;
                $object = new stdClass();
                $object->agroupid = $id;
                $object->ageid = $group->newageid;
                $object->agenum = $group->newagenum;
                $object->changedate = time();
                if ( ! $this->dof->storage('agrouphistory')->is_exists(
                    array(
                        'agroupid' => $object->agroupid,
                        'agenum' => $object->agenum,
                        'ageid' => $object->ageid
                    )) )
                { // если такая история уже есть - все в порядке
                    $this->log_string('Add group history ' . $group->name . '(' . $id . ')... ');
                    if ( $result = $this->dof->storage('agrouphistory')->insert($object) )
                    { // успешный результат
                        $this->log_string('successfully' . "\n");
                    } else
                    { // неуспешный
                        $this->log_string('fails' . "\n");
                    }
                    $rez = $rez and $result;
                }
            }
        }
        $student = $order->data->student;
        if ( isset($student->transfer) )
        { // массив transfer
            foreach ( $student->transfer as $agenum => $groups )
            { // массив групп
                foreach ( $groups as $agroupid => $sbcids )
                { // массив подписок
                    foreach ( $sbcids as $id => $sbcdata )
                    { // подписка - объект
                        $obj = new stdClass();
                        $obj->agenum = $sbcdata->newagenum;
                        $obj->agroupid = $sbcdata->newagroupid;
                        $this->log_string('Update programmsbcs ' . $id . '... ');
                        if ( $result = $this->dof->storage('programmsbcs')->update($obj, $id) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                        if ( $sbcdata->oldstatus != 'active' )
                        { // меняем статус подписки, если она не было активной
                            $this->log_string(
                                'Change status programmsbcs ' . $id . ' on ' . $sbcdata->newstatus .
                                     '... ');
                            if ( $result = $this->dof->workflow('programmsbcs')->change($id, 
                                $sbcdata->newstatus) )
                            { // успешный результат
                                $this->log_string('successfully' . "\n");
                            } else
                            { // неуспешный
                                $this->log_string('fails' . "\n");
                            }
                            $rez = $rez and $result;
                        }
                        // имитируем cpassed
                        $cpassed = new stdClass();
                        $cpassed->programmsbcid = $id;
                        $cpassed->ageid = $sbcdata->newageid;
                        $cpassed->status = 'active';
                        $this->log_string('Add learning history ' . $id . '... ');
                        if ( $result = $this->dof->storage('learninghistory')->add($cpassed) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                    }
                }
            }
        }
        if ( isset($student->condtransfer) )
        { // массив condtransfer
            foreach ( $student->condtransfer as $agenum => $groups )
            { // массив групп
                foreach ( $groups as $agroupid => $sbcids )
                { // массив подписок
                    foreach ( $sbcids as $id => $sbcdata )
                    { // подписка - объект
                        $obj = new stdClass();
                        $obj->agenum = $sbcdata->newagenum;
                        $obj->agroupid = $sbcdata->newagroupid;
                        $this->log_string('Update programmsbcs ' . $id . '... ');
                        if ( $result = $this->dof->storage('programmsbcs')->update($obj, $id) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                        if ( $sbcdata->oldstatus != 'condactive' )
                        { // меняем статус подписки, если она не было условно активной
                            $this->log_string(
                                'Change status programmsbcs ' . $id . ' on ' . $sbcdata->newstatus .
                                     '... ');
                            if ( $result = $this->dof->workflow('programmsbcs')->change($id, 
                                $sbcdata->newstatus) )
                            { // успешный результат
                                $this->log_string('successfully' . "\n");
                            } else
                            { // неуспешный
                                $this->log_string('fails' . "\n");
                            }
                            $rez = $rez and $result;
                        }
                        // имитируем cpassed
                        $cpassed = new stdClass();
                        $cpassed->programmsbcid = $id;
                        $cpassed->ageid = $sbcdata->newageid;
                        $cpassed->status = 'active';
                        $this->log_string('Add learning history ' . $id . '... ');
                        if ( $result = $this->dof->storage('learninghistory')->add($cpassed) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                    }
                }
            }
        }
        if ( isset($student->notransfer) )
        { // массив notransfer
            foreach ( $student->notransfer as $agenum => $groups )
            { // массив групп
                foreach ( $groups as $agroupid => $sbcids )
                { // массив подписок
                    foreach ( $sbcids as $id => $sbcdata )
                    { // подписка - объект
                        $obj = new stdClass();
                        $obj->agroupid = $agroupid;
                        $this->log_string('Update programmsbcs ' . $id . '... ');
                        if ( $result = $this->dof->storage('programmsbcs')->update($obj, $id) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                        // имитируем cpassed
                        $cpassed = new stdClass();
                        $cpassed->programmsbcid = $id;
                        $cpassed->ageid = $sbcdata->newageid;
                        $cpassed->status = 'active';
                        $this->log_string('Add learning history ' . $id . '... ');
                        if ( $result = $this->dof->storage('learninghistory')->add($cpassed) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                    }
                }
            }
        }
        if ( isset($student->restore) )
        { // массив restore
            foreach ( $student->restore as $agenum => $groups )
            { // массив групп
                foreach ( $groups as $agroupid => $sbcids )
                { // массив подписок
                    foreach ( $sbcids as $id => $sbcdata )
                    { // подписка - объект
                        $obj = new stdClass();
                        $obj->agroupid = $agroupid;
                        $this->log_string('Update programmsbcs ' . $id . '... ');
                        if ( $result = $this->dof->storage('programmsbcs')->update($obj, $id) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                        // меняем статус
                        $this->log_string(
                            'Change status programmsbcs ' . $id . ' on ' . $sbcdata->newstatus .
                                 '... ');
                        if ( $result = $this->dof->workflow('programmsbcs')->change($id, 
                            $sbcdata->newstatus) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                    }
                }
            }
        }
        if ( isset($student->academ) )
        { // массив academ
            foreach ( $student->academ as $agenum => $groups )
            { // массив групп
                foreach ( $groups as $agroupid => $sbcids )
                { // массив подписок
                    foreach ( $sbcids as $id => $sbcdata )
                    { // подписка - объект
                        $obj = new stdClass();
                        $obj->agroupid = $sbcdata->newagroupid;
                        $this->log_string('Update programmsbcs ' . $id . '... ');
                        if ( $result = $this->dof->storage('programmsbcs')->update($obj, $id) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                        // меняем статус
                        $this->log_string(
                            'Change status programmsbcs ' . $id . ' on ' . $sbcdata->newstatus .
                                 '... ');
                        if ( $result = $this->dof->workflow('programmsbcs')->change($id, 
                            $sbcdata->newstatus) )
                        { // успешный результат
                            $this->log_string('successfully' . "\n");
                        } else
                        { // неуспешный
                            $this->log_string('fails' . "\n");
                        }
                        $rez = $rez and $result;
                    }
                }
            }
        }
        return $rez;
    }

    public function check_order_data( $order )
    {
        if ( empty($order->data) and empty($order->data->student) )
        { //приказа нет - исполнять нечего
            return false;
        }
        if ( isset($order->data->groups) )
        {
            foreach ( $order->data->groups as $agroupid => $group )
            {
                if ( $group->oldagenum != $group->newagenum )
                { // если периоды не совпадают - надо переформировать группу
                    if ( $this->dof->workflow('agroups')->has_active_or_suspend_cstreams($agroupid) )
                    { // если есть активные потоки группы - исполнять приказ нельзя
                        return false;
                    }
                    $status = $this->dof->storage('agroups')->get_field($agroupid, 'status');
                    if ( $status != 'plan' and $status != 'active' and $status != 'formed' )
                    { // если группа не в активном запланированном или сформерованном статусе - переводить нельзя
                        return false;
                    }
                }
            }
        }
        foreach ( $order->data->student as $type => $transfertype )
        { //перебераем по типам перевода
            foreach ( $transfertype as $agenum => $groups )
            { // массив групп
                foreach ( $groups as $agroupid => $sbcids )
                { // массив подписок
                    foreach ( $sbcids as $id => $sbcdata )
                    { // подписка - объект
                        switch ( $type )
                        {
                            case 'transfer':
                            case 'condtransfer':
                                if ( $sbcdata->oldstatus != 'active' and
                                     $sbcdata->oldstatus != 'condactive' )
                                { //ученик не в активном статусе - переводить нельзя
                                    return false;
                                }
                                break;
                            case 'notransfer':
                            case 'academ':
                                if ( $sbcdata->oldstatus != 'active' and
                                     $sbcdata->oldstatus != 'condactive' and
                                     $sbcdata->oldstatus != 'suspend' )
                                { //ученик не в активном или преостановленном статусе - переводить нельзя
                                    return false;
                                }
                                break;
                            case 'restore':
                                if ( $sbcdata->oldstatus != 'onleave' )
                                { //ученик не в статусе академического отпуска - переводить нельзя
                                    return false;
                                }
                                break;
                        }
                    }
                }
            }
        }
        
        return true;
    }

    public function show_tablerow( $addvars = [] )
    {
        //формируем ссылку для кастомного просмотра приказа 
        $viewurl = $this->dof->url_im('learningorders', '/ordertransfer/formationorder.php', 
            [
                'id' => $this->get_id()
            ]);
        //формируем массив для вставки в базовую строку
        $specialactions = [
            'specialactions' => $this->dof->modlib('ig')->icon('view', $viewurl, [])
        ];
        //получаем базовую строку
        $baserow = parent::show_tablerow($addvars);
        //вставляем в строку базового приказа спец.действия перед id
        $row = $this->insert_array_in_array($baserow, $specialactions, 'id');
        
        return $row;
    }

    protected function insert_array_in_array( $array, $addarray, $index )
    {
        //порядковый номер искомого по ключу элемента
        $beforeindex = array_search($index, array_keys($array));
        //левая часть массива от нужной позиции
        $leftarray = array_slice($array, 0, $beforeindex);
        //правая часть массива от нужной позиции
        $rightarray = array_slice($array, $beforeindex);
        //объединенный массив со вставкой на нужную позицию
        return array_merge($leftarray, $addarray, $rightarray);
    }

    public function show_tableheader()
    {
        //получаем базовую строку заголовков
        $headerrow = parent::show_tableheader();
        //вставляем на нужное место дополнительную колонку со спец.действиями
        array_splice($headerrow, 1, 0, 
            $this->dof->get_string('table_im_learningorders_transfer_specialactions', 'orders'));
        
        return $headerrow;
    }
}

// /**
//  * Данный закомментированный класс сохранен на случай, если встретится его использование
//  * Был создан класс с шаблонным названием dof_im_learningorders_order_transfer
//  */
// class dof_im_journal_order_transfer extends dof_im_learningorders_order_transfer
// {
// }
?>