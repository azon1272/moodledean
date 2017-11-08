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
 * Класс для создания приказов о фиксации дня 
 */

class dof_im_journal_order_fix_day extends dof_storage_orders_baseorder
{
    public function plugintype()
    {
        return 'im';
    }
    
    public function plugincode()
    {
        return 'journal';
    }
    
    public function code()
    {
        return 'fix_day';
    }
    
    /**
     * Выполнения действий после подписания приказа
     * 
     * @see dof_storage_orders_baseorder::execute_actions()
     */
    protected function execute_actions($order)
    {
        // Получаем посещаемость из приказа
        if ( ! isset($order->data) )
        {// Данных не найдено
            return false;
        }
        if ( empty($order->data) )
        {// Данных нет
            return false;
        }
        // Получаем отчет
        $report = $this->dof->storage('reports')->get($order->data->reportid);

        // Подключаем класс, который будет генерировать отчет
        if ( ! $reportobj = $this->dof->storage('reports')->report($report->plugintype,$report->plugincode,$report->code,$report->id) )
        {// в плагине нет отчета с таким кодом - это ошибка 
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Report is not loaded'."\n\n");
            return false;
        }
        $this->log_string(date('d.m.Y H:i:s',time())."\n");
        $this->log_string('Generating report'."\n\n");
        
        // Генерируем отчет
        $result = $reportobj->generate();
        
        // Подготовим критерии по отбору дней для фиксации
        if ( ! isset($order->data->begindate) )
        {// Нет даты начала у приказа - ошибка
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Order startdate not defined'."\n\n");
            return false;
        }
        if ( ! isset($order->data->enddate) )
        {// Нет даты окончания у приказа - ошибка
            $this->log_string(date('d.m.Y H:i:s',time())."\n");
            $this->log_string('Order enddate not defined'."\n\n");
            return false;
        }
        if ( isset($order->data->prevorderid))
        {// Указан ID отчета за предыдущий период
            // Получим объект отчета за предыдущий период
            $report = $this->dof->storage('reports')->get($order->data->prevorderid);
            if ( ! empty($report) )
            {// Отчет найден
                $begindate = $report->enddate + 1;
            } else 
            {// Отчета нет
                $begindate = $order->data->begindate;
            }
        } else 
        {// Отчет за предыдущий период не указан
            $begindate = $order->data->begindate;
        }
        // Сформируем массив подразделений , которые затрагивает приказ
        $dep = $this->dof->storage('departments')->get($order->departmentid);
        $departmentids = array($order->departmentid);
        if ( $departments = $this->dof->storage('departments')->departments_list_subordinated($dep->id, $dep->depth,$dep->path,true) )
        {
        	$departmentids = array_merge(array_keys($departments),$departmentids);
        }
        $depid = implode(',',$departmentids);
        $statuses = implode(',', array('"plan"', '"active"', '"draft"'));
        $rez = true;
        
        // Получим дни
        $days = $this->dof->storage('schdays')->get_records_select
            ("date <= {$order->data->enddate} AND 
              date >= {$begindate} AND 
              departmentid IN ({$depid}) AND
              status IN ({$statuses}) 
             ");
        if ( ! empty($days) )
        {
            foreach($days as $day)
            {// Зафиксируем день
                $obj = new stdClass();
                $this->log_string(date('d.m.Y H:i:s',time())."\n");
                $this->log_string('Day fixining'."\n\n");
                $rez = $this->dof->workflow('schdays')->change($day->id,'fixed') && $rez;
            }
        }
        return $rez;
    }
}
?>