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



//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");
$DOF->modlib('nvg')->add_level($DOF->get_string('list_journals', 'journal'), $DOF->url_im(
        'journal', '/list_journals/list_journals.php', $addvars));

class dof_im_journal_listjournals
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * массив структуры:
     * = array(
     *   [departmentid] => obj  -> departmentname = 'department_name'
     *                     obj  -> programms = array(
     *   [programmid]   => obj1 -> programmname = 'programm_name'
     *                     obj1 -> ages = array(
     *   [agenum]       => obj2 -> agename = 'age_name'
     *                     obj2 -> items = array(
     *   [itemid]       => obj3 -> itemname = 'item_name'
     *                     obj3 -> cstreams = array(
     *   [cstreamid]    => obj4 -> cstreamname = 'cstream_name'
     *                                              )
     *                                            )
     *                                           )
     *                                               )
     *         )
     * содержит данные для вставку в темплатер после добавления еще одного уровня:
     * $fortemplater->departments = $this->departments;
     * @var array
     */
    private $departments;
    private $teacherid;
    private $mycstrems;
    private $completecstrems;
    public function __construct(dof_control $dof)
    {
        $this->dof = $dof;
    }  

    /**
     * Заполняет $this->data начальной информацией
     * 
     * @param int $departmentid - id подразделения
     * @return bool true, если все нормально или 
     * false в ином случае
     */
    public function set_data($departmentid = 0, $teacherid = 0, $mycstrems = false, $completecstrems = false)
    {
        if ( $departmentid )
        {//получаем журналы одного подразделения
            $dep = $this->dof->storage('departments')->get($departmentid);
            if ( ! $dep )
            {//не получили подразделение
                return false;
            }
            $obj = new stdClass();
            $obj->departmentname = $dep->name;
            $this->departments = array($dep->id => $obj);
        }else
        {//получаем все подразделения
            $all = $this->dof->storage('departments')->departments_list();
            if ( ! $all )
            {//не получили
                return false;
            }
            //создаем заготовку структуры
            $this->departments = array();
            foreach ( $all as $depid => $depname )
            {//заполняем ее подразделениями
                $dep = new stdClass();
                $dep->departmentname = $depname;
                $this->departments[$depid] = $dep; 
            }
        }
        // запомним остальные параметры
        $this->teacherid = $teacherid;
        $this->mycstrems = $mycstrems;
        $this->completecstrems = $completecstrems;
        //раз до сюда дошли - значит все в порядке
        return true;
    }

    /**
     * возвращает собранные данные
     * @return mixed array или null
     */
    public function get_data()
    {
        return $this->departments;
    }
    /**
     * получаем все журналы занесенных подразделений
     * @return bool - результат операции
     */
    public function get_journals()
    {
        if ( ! is_array($this->departments) )
        {//базовая структура не создана';
            return false;
        }
        foreach ( $this->departments as $depid => $one )
        {//перебираем подразделения';
            if ( ! $journals = $this->get_journals_department($depid) )
            {//не получили журналы подразделения
                continue;
            }
            //заносим их журналы
            $this->departments[$depid]->programms = $journals;
        }
        return true;
    }
    
    /**
     * Возвращает журналы одного подразделения
     * @param int $departmentid id - подразделения
     * @return mixed array - набор журналов или bool - false
     */
    private function get_journals_department($departmentid)
    {
        //получаем все программы, за которые отвечает подразделение
        $allprog = $this->dof->storage('programms')->get_records(array('departmentid'=>$departmentid), 'name');
        if ( ! $allprog )
        {//не получили
            return false;
        }
        $programms = array();
        foreach ( $allprog as $one )
        {//для каждой программы получаем все ее журналы
            $programm = new stdClass();
            $programm->programmname = $one->name.' ['.$one->code.']';
            $programm->ages = $this->get_journal_programm($one->id, $one->agenums);
            $programms[$one->id] = $programm;
        }
        return $programms;
    }
    
    /**
     * Возвращает ссылки на журналы предметов программы
     * @param int $programmid - id программы
     * @param int $agenums - количество периодов, в течение которых 
     * идет преподавание программы
     * @return mixed array - массив периодов или 
     * bool false
     */
    private function get_journal_programm($programmid, $agenums)
    {
        $ages = array();
        for ( $i = 0; $i <= $agenums; $i++ )
        {//перебираем номера учебных периодов
            //получаем журналы для каждого из них
            $age = new stdClass();
            $age->agename = $this->dof->get_string('agename', 'journal', $i);
            if ( $i == 0 )
            {
                $age->agename = $this->dof->get_string('agename0', 'journal');
            }
            $age->items = $this->get_journal_age($programmid, $i);
            if ( ! $age )
            {//не получили данные периода
                continue;
            }
            $ages[$i] = $age;
        }
        return $ages;
    }
    
    /**
     * Возвращает ссылки на журналы предметов программы, 
     * которые идут в периоде с указанным порядковым номером
     * @param int $programmid - id программы
     * @param int $agenum - порядковый номер периода, 
     * в котором идет эта программа
     * @return mixed array - массив предметов или 
     * bool false
     */
    private function get_journal_age($programmid, $agenum)
    {
        //получаем все предметы этой программы, которые идут в указанном периоде
        $itemage = $this->dof->storage('programmitems')->
                    get_records_select("programmid={$programmid} AND agenum={$agenum}",null,'name ASC');
        if ( ! $itemage )
        {//не получили предметы
            return false;
        }
        $items = array();
        foreach ( $itemage as $one )
        {//перебираем предметы и к каждому добавляем список потоков
            $item = new stdClass();
            $item->itemname = $one->name.' ['.$one->code.']';
            $item->cstreams = $this->get_journal_cstreams($one->id);
            $items[$one->id] = $item;
        }
        return $items;
    }
    
    /**
     * Возвращает ссылки на журналы потоков одной дисциплины
     * @param int $programmitemid - id дисциплины
     * @return mixed array - массив потоков или 
     * bool false если потоки не получены
     */
    private function get_journal_cstreams($programmitemid)
    {
        //получаем все потоки предмета
        $cstreamitems = $this->dof->storage('cstreams')->
                get_cstreams_on_parametres($programmitemid, $this->teacherid, 
                $this->mycstrems, $this->completecstrems);
        if ( ! $cstreamitems )
        {//не получили';
            return false;
        }
        $cstreams = array();
        foreach ( $cstreamitems as $one )
        {//пербираем потоки и приводим к нужному виду
            $cstream = new stdClass();
            $name = $this->get_cstreamname($one);
            if ($one->status == 'completed')
            {//если поток завершен - сделаем ссылку серой
                $cstream->cstreamname = '<div id="menu">'.$this->get_journal_link($name, $one->id).'</div>';
            }
            else
            {// оставим так
                $cstream->cstreamname = $this->get_journal_link($name, $one->id);
            }
            $cstreams[$one->id] = $cstream;
        }
        return $cstreams;
    }
    
    /**
     * Возвращает название потока
     * @param object $cstream - запись потока из БД
     * @return string - название потока
     */
    private function get_cstreamname($cstream)
    {
        //$links = $this->dof->storage('cstreamlinks')->get_list('cstreamid',$cstream->id);
        $name = $this->dof->get_string('cstream', 'journal').' '.$cstream->name;
        //        strftime('%d.%m.%y', $cstream->begindate).' - '.
        //        strftime('%d.%m.%y', $cstream->enddate);
        //if ( $links )
        //{// если есть еще группы подписаные на поток
        //    foreach ( $links as $link)
        //    {
        //        $name .= ' ('.
        //            $this->dof->storage('agroups')->get_field($link->agroupid,'name').'['.
        //            $this->dof->storage('agroups')->get_field($link->agroupid,'code').'])';
        //    }
        //}
        return $name;
    }
    
    /** Получить ссылку на страницу журнала
     * 
     * @return string html-код ссылки
     * @param string $name - текст на ссылке
     * @param int $cstreamid - id учебного потока в таблице cstreams
     */
    private function get_journal_link($name, $cstreamid)
    {
        $depid = optional_param('departmentid', 0, PARAM_INT);
        $addvars = array();
        $addvars['departmentid'] = $depid;
        $path = $this->dof->url_im('journal',
        '/group_journal/index.php?csid='.$cstreamid,$addvars);
        return "<a href=\"{$path}\">".$name.'</a>';
    }
}
?>