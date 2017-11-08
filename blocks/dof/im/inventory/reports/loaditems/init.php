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
class dof_im_inventory_report_loaditems extends dof_storage_reports_basereport
{
    // Параметры для работы с шаблоном
    protected $templatertype = 'im';
    protected $templatercode = 'inventory';
    protected $templatertemplatename = 'loaditems';
    /* Код плагина, объявившего тип приказа
    */
    public function code()
    {
        return 'loaditems';
    }
    
    /* Имя плагина, объявившего тип приказа
    */ 
    public function name()
    {
        return $this->dof->get_string('report_items', 'inventory');
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
        
        $report->name = $this->dof->get_string('report_items', 'inventory');
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
        // если дочерние, то берем и дочерние категории
        $categoryids= array();
        $flag = false;
        if ( $data->child == '1' )
        {
            $flag = true;
        }
        if ( $data->child == '1' OR  empty($data->categoryid) )
        {
            $catids = $this->dof->storage('invcategories')->category_list_subordinated
                            ($data->categoryid, null, null, true, '', $report->departmentid );
             
            // не забываем и про текущую категорию 
            if ( ! empty($data->categoryid) )
            {
                $catids[$data->categoryid] = $data->categoryid;    
            }               
            
            // перепимшем к подходящему виду
            foreach ( $catids as $catid=>$catobj )
            {
                $categoryids[] = $catid;
            }      
        }else 
        {// без дочерних
            $categoryids[] = $data->categoryid;
        }

        // если в одной категории лежит оборудование из разных подразделений - ЭТО сисетмная ошибка
        // собрали все категории, теперь выбираем оборудование их этих категорий
        $status = $this->dof->workflow('invitems')->get_list_param('real');
//        var_dump($categoryids);
//        var_dump($status);
        if ( $report->departmentid )
        {
            $items = $this->dof->storage('invitems')->get_records(array('invcategoryid' => $categoryids,
                                                                        'status'        => $status,
                                                                        'departmentid'  => $report->departmentid),
                                                                        'name');    
        }else 
        {
            $items = $this->dof->storage('invitems')->get_records(array('invcategoryid' => $categoryids,
                                                                        'status'        => $status),
                                                                        'name');
        }
        
        if ( $items )
        {
            // перебираем оборудование и создаем шаблон для вывода
            // для того чтобы отобразить сколько ообрудования осталось обработать - посчитаем их количество
            $totalcount   = count($items);
            $currentcount = 0;   
            foreach ( $items as $item )
            {
                // Выводим сообщение о том какое оборудование обрабатываеися и сколько осталось ещё
                // (информация отображается при запуске cron.php)
                ++$currentcount;
                $mtracestring = 'Prosessing itemid: '.$item->id.' ('.$currentcount.'/'.$totalcount.')';
                $this->dof->mtrace(2, $mtracestring); 
                // запишем объект с данными                
                $iteminfo[] = $this->get_string_load($item,$currentcount,$report->departmentid);
            }         
            
            // дозапишем наш объект 
            $report->data->column_items = $iteminfo;
            // метка времени сбора отчета(используется в названии, для наглядности)
            $report->data->column_time = dof_userdate($report->crondate,'%d.%m.%Y');
            // инвентарный номер
            $report->data->column_code = $this->dof->get_string('invnum','inventory');
            // название оборудования
            $report->data->column_name = $this->dof->modlib('ig')->igs('name');
            // серийный номер
            $report->data->column_serial = $this->dof->get_string('serialnum','inventory');
            // категория
            $report->data->column_category = $this->dof->get_string('catname','inventory');
            // статус
            $report->data->column_status = $this->dof->modlib('ig')->igs('status');
            // персона - у кого это оборудование
            $report->data->column_person = $this->dof->modlib('ig')->igs('fio');        
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
            $report->data->category_n = $this->dof->get_string('catname', 'inventory');
            $report->data->child = $this->dof->get_string('including_child', 'inventory');
            if ( ! empty($data->categoryid) )
            {
                $report->data->category_name = $this->dof->storage('invcategories')->get_field($data->categoryid, 'name');
                //включая дочерние
                if ( $flag )
                {// да
                    $report->data->child_name = $this->dof->modlib('ig')->igs('yes');
                }else 
                {// нет    
                    $report->data->child_name = $this->dof->modlib('ig')->igs('no');
                }
            }else 
            {
                $report->data->category_name = $this->dof->get_string('all_cats', 'inventory');
            }    
        }
        
        return $report;

    }
    
    /**
     * Строка для вывода одного события
     * @param object $item - объект из таблицы invitems
     * @param integer $depid - объект из таблицы departments
     * @param integer $i - нумерация (1,2,3...)	
     */                
   public function get_string_load($item, $i, $depid=0)
   {

        $templater = new stdClass();
        // имя со ссылкой
        $templater->person = '';
        if ( ! empty($item->invsetid) AND $perosnid = $this->dof->storage('invsets')->get_field($item->invsetid,'personid')  )
        {
            $url_persons = $this->dof->url_im('persons','/view.php?id='.$perosnid,array('departmentid'=>$depid));
            $templater->person = '<a href="'.$url_persons.'">'.$this->dof->storage('persons')->get_fullname($perosnid).'</a>';
        }
        // название
        $templater->name = $item->name;
        // инвентарный номер
        $url_item = $this->dof->url_im('inventory','/items/view.php?id='.$item->id,array('departmentid'=>$item->departmentid, 'invcategoryid'=>$item->invcategoryid));
        $templater->code =  '<a href="'.$url_item.'">'.$item->code.'</a>';
        // серийный номер
        $templater->serialnum =  $item->serialnum;
        // категория
        $url_cat = $this->dof->url_im('inventory','/category/list.php',array('departmentid'=>$item->departmentid, 'invcategoryid'=>$item->invcategoryid));
        $templater->category =  '<a href="'.$url_cat.'">'.$this->dof->storage('invcategories')->get_field($item->invcategoryid,'name').'</a>';
        // статус
        $templater->status =  $this->dof->get_string('status:'.$item->status,'invitems','','workflow');
        // номер
        $templater->i =  $i;
        
        return $templater;
    }    
    
}
