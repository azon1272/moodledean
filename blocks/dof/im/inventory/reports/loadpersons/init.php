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

/** Отчёт о персонах с обрудованием
 *
 */
class dof_im_inventory_report_loadpersons extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'inventory';
    protected $templatertemplatename = 'loadpersons';
    /* Код плагина, объявившего тип приказа
    */
    public function code()
    {
        return 'loadpersons';
    }
    
    /* Имя плагина, объявившего тип приказа
    */ 
    public function name()
    {
        return $this->dof->get_string('report_persons', 'inventory');
    }
    
    /*
     * Тип плагина
     */
    public function plugintype()
    {
        return 'im';
    }
    
    /*
     * Код плагина
     */
    public function plugincode()
    {
        return 'inventory';
    }    
    
    
    /**
     * Метод, предусмотренный для расширения логики сохранения
     */
    protected function save_data($report)
    {
        
        $report->name = $this->dof->get_string('report_persons', 'inventory');
        return $report;
    }     
    
 

    /** Метод записывает в отчет все данные по студентам и
     * возвращает уже полный отчет
     * @param object $report - отчет, по который доформировываем )
     * @return object $report - объект 
     */
    public function generate_data($report)
    {
        if ( ! is_object($report) )
        {// не того типа передали даные
            return false;
        }
        // получим данные из отчета
        $data = $report->data;
        $persons = array();
        // учтем подразделения
        // соберем всех персон этого подразделения
        if ( $report->departmentid )
        {
            $persons = $this->dof->storage('persons')->get_records(array('departmentid'=>$report->departmentid),'sortname');
        }else 
        {
            $persons = $this->dof->storage('persons')->get_records(array('status' => 'normal'),'sortname');
        }
        //есть - передираем
        if ( $persons )
        {
            $i = 0;
            $people = array();
            // для того чтобы отобразить сколько ообрудования осталось обработать - посчитаем их количество
            $totalcount   = count($persons);
            $currentcount = 0;
            foreach ( $persons as $person )
            {// отчет о нагрузке учителей
                // Выводим сообщение о том какой контракт проверяется сейчас, и сколько контрактов осталось
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing personid: '.$person->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->dof->mtrace(2, $mtracestring); 
                        
                $i++;
                // есть оборудование - допишем, нет - не допишем
                $persontemp = $this->get_string_load($person->id, $report->departmentid, $i);

                $people[$person->id] = $persontemp;
            }

            // дозапишем наш объект 
            $data = $report->crondate;
            $report->data->column_persons = $people;
            $report->data->column_time = dof_userdate($data,'%d.%m.%Y');
            $report->data->column_person = $this->dof->modlib('ig')->igs('fio');
            $report->data->column_item = $this->dof->get_string('items','inventory');
            // дополнительная инфа
            $report->data->info = $this->dof->get_string('info','inventory');
            $report->data->depart = $this->dof->get_string('department','inventory');
            if ( $report->departmentid )
            {// отчет по подразделению
                $dep = $this->dof->storage('departments')->get($report->departmentid);
                $report->data->depart_name = $dep->name.'['.$dep->code.']';    
            }else 
            {// все отчеты
                $report->data->depart_name = $this->dof->get_string('all_departs', 'inventory');
            }     
            $report->data->data_complete = $this->dof->get_string('data_complete', 'inventory');
            $report->data->data_begin_name = $this->dof->get_string('data_begin', 'mreports','','sync');
            $report->data->data_begin = dof_userdate($report->crondate,'%d.%m.%Y %H:%M');
            $report->data->request_name = $this->dof->get_string('request_name', 'inventory');
            $report->data->requestdate = dof_userdate($report->requestdate,'%d.%m.%Y %H:%M');
            
        }
        return $report;
    }
    
    /**
     * Строка для вывода одного события
     * @param integer $id - id объектa из таблицы persons
     * @param integer $depid - id объектa из таблицы departments
     * @param integer $i - номер записи(1,2,3,4...)
     * 											 -	
     */                
   public function get_string_load($id, $depid, $i)
   {
        $templater = new stdClass();
        // счетчик
        $templater->i = $i;
        // имя
        $url_persons = $this->dof->url_im('persons','/view.php?id='.$id,array('departmentid'=>$depid));
        $templater->person = '<a href="'.$url_persons.'">'.$this->dof->storage('persons')->get_fullname($id).'</a>';
        // оборудование
        $templater->item = '';
        // соберем все комплекты этой персоны
        if ( $sets = $this->dof->storage('invsets')->get_records(array('personid'=>$id, 'departmentid'=>$depid)) )
        {
            // перепишем к подходящему виду
            $setarray = array();
            foreach ( $sets as $id=>$obj )
            {
                $setarray[] = $id;
            }
            // соберем все оборудование ро этим комплектам
            if ( $items = $this->dof->storage('invitems')->get_records(array('invsetid'=>$setarray)) )
            {
                $text = '';
                foreach ( $items as $item )
                {// перебираем комплект
                    $item_str = $item->name.'['.$item->code.']';
                    if ( ! empty($item->serialnum ) )
                    {// добавим сериынй номер
                        $item_str .= '['.$item->serialnum.']';
                    }
                    // отобразим в виде ссылки
                    $url_item = $this->dof->url_im('inventory','/items/view.php?id='.$item->id,array('departmentid'=>$item->departmentid, 'invcategoryid'=>$item->invcategoryid));
                    $item_str =  '<a href="'.$url_item.'">'.$item_str.'</a>';
                    // каждое оборудование - с новой строки
                    $text .= $item_str.'<br>';
                }
                $templater->item = $text;
            }    
        }

        return $templater;
    }    
    
}
?>