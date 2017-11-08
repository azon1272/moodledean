<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999  Alex Djachenko (Алексей Дьяченко)             //
// alex-pub@my-site.ru                                                    //
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

//Методы для вывода имени предмето-потока

class block_dof_storage_cstreams_namecstream
{
    /** Возвращает коды групп подписанных на прeдмето-поток
     * @param int $cstreamid - id предмето-потока
     * @return string коды групп или bool false если такие не найдены
     */
    private function get_string_agroups_codes($cstreamid)
    {
        global $DOF;
        // найдем все привязки к группам для этого потока
        if ( ! $links = $DOF->storage('cstreamlinks')->get_cstream_cstreamlink($cstreamid) )
        {// связей нет
            return false;
        }
        $agroupscode = array();
        foreach ( $links as $link )
        {// для каждой группы из связки найдем ее код
            $code = $DOF->storage('agroups')->get_field($link->agroupid,'code');
            if ( ! $code )
            {
                return false;
            }
            $agroupscode[] = $code;
        }
        // все полученные коды групп отсортируем по алфавиту
        asort($agroupscode);
        // и вернем строкой через запятую
        return implode(',', $agroupscode);
    }
    /** Получает логины учащихся предмето-потока не связанных с потоком через группы
     * @param int $cstreamid - id предмето-потока
     * @return string логины учащихся или bool false если такие не найдены
     */
    private function get_string_students_usernames($cstreamid)
    {
        global $DOF;
        // найдем все подписки на дисциплину для потока, у которых нет группы
        if ( ! $cpassed = $DOF->storage('cpassed')->
                          get_records(array('cstreamid'=>$cstreamid,'agroupid'=>array('0',null),
                          'status'=>array('plan','active','suspend','completed','failed','reoffset'))) )
        {// таких подписок нет
            return false;
        }
        $loginsstudent = array();
        foreach ( $cpassed as $cpass )
        {// для каждой привязки найдем логин ученика
            if ( ! $userid = $DOF->storage('persons')->get_field($cpass->studentid,'mdluser') )
            {// не получили mdluser - логина нет
                continue;
            }
            if ( ! $user = $DOF->sync('personstom')->get_mdluser($userid) )
            {// не получили пользователя Moodle - логина нет
                continue;
            }
            // запишем логин
            $loginsstudent[] = $user->username;
        }
        $loginsstudent = array_unique($loginsstudent);
        // все полученные логины отсортируем по алфавиту
        asort($loginsstudent);
        // и вернем строкой через запятую
        return implode(',', $loginsstudent);
    }
    /** Возвращает имя предмето потока
     * @param int $cstreamid - id предмето-поток
     * @return string строку имени предмето-потока
     */
    private function get_cstream_name($cstreamid)
    {
        global $DOF;
        $cstreamname= array();
        //найдем id предмета
        if ( ! $piteamid = $DOF->storage('cstreams')->get_field($cstreamid,'programmitemid') )
        {// не нашли
            return 'error cstream name';
        }
        // найдем id программы
        if ( ! $programmid = $DOF->storage('programmitems')->get_field($piteamid,'programmid') )
        {// не нашли
            return 'error cstream name';
        }
        // найдем код программы и занесем его в массив формирования имени потока
        if ( $programmname = $DOF->storage('programms')->get_field($programmid,'code') )
        {// нашли
            $cstreamname[] = $programmname;
        }
        // найдем код предмета и занесем его в массив формирования имени потока
        if ( $pitemname = $DOF->storage('programmitems')->get_field($piteamid,'code') )
        {// нашли
            $cstreamname[] = $pitemname;
        }
        if ( $agroupsname = $this->get_string_agroups_codes($cstreamid) )
        {// нашли
            $cstreamname[] = $agroupsname;
        }
        if ( $loginsstudent = $this->get_string_students_usernames($cstreamid) )
        {// нашли
            $cstreamname[] = $loginsstudent; 
        }
        if ( ! empty($cstreamname) )
        {// если массив имени не пустой выведим все полученные элементы через -
            return substr(implode('-', $cstreamname),0,254);
        }else
        {// ничего не получили - скажем об этом
            return 'error cstream name';
        }
    }
    /** Сохраняет имя предмето-потока в БД
     * @param int $cstreamid - id предмето-поток
     * @return bool true - если запись прошла успешно или false
     */
    public function save_cstream_name($cstreamid)
    {
        global $DOF;
        $obj = new stdClass();
        $obj->name = $this->get_cstream_name($cstreamid);
        return $DOF->storage('cstreams')->update($obj,$cstreamid,true);
    }
}
?>