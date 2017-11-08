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

/**
 * библиотека, для вызова из веб-страниц, подключает DOF.
 */ 

//загрузка библиотек верхнего уровня
require_once(dirname(realpath(__FILE__))."/../lib.php");

// Подключаем стили
$DOF->modlib('nvg')->add_css('im', 'employees', '/styles.css');

/** Класс для назначения учителям предметов
 * 
 */
class dof_im_employees_programmitems_assigment
{
    /**
     * @var dof_control
     */
    protected $dof;
    public    $extradata;
    
    /** Конструктор
     * @param dof_control $dof - объект с методами ядра деканата
     * @access public
     */
    public function __construct($dof)
    {
        // Сохраняем ссылку на DOF, чтоб вызывать его через $this->dof
        $this->dof = $dof;
        $this->extradata = new stdClass;
    }
    
    /** Получить список возможных предметов для преподавания для переданного назначения на должность
     * 
     * @return array - массив записей из таблице programmitems, исключая те предметы, которые 
     *                      уже были привязаны к этой должности в таблице teachers
     * @param int $appointmentid - id назначения на должность в таблице в табице appointments
     * @todo исключать из выборки только те курсы, которые пользователь ведет в 
     * рамках указанного назначения на должность, или вообще все, которые он
     * ведет и в рамках других должностей?
     */
    public function get_available_pitems_for_appointment($appointmentid)
    {
        // найдем все актуальные предметы, которые есть в системе
        $progpitems = $this->dof->storage('programmitems')->get_records(array('status'=>array('active','suspend')), 'name ASC');
        // оставим в списке только те объекты, на использование которых есть право
        $permissions  = array(array('plugintype'=>'storage', 'plugincode'=>'programmitems', 'code'=>'use'));
        $progpitems = $this->dof->storage('acl')->get_acl_filtered_list($progpitems, $permissions);
        if ( empty($progpitems) )
        {// предметов нет - возвращаем пустой массив
            return array();
        }
        // найдеим предметы, которые учитель уже преподает
        $availablepitems = $this->get_appointment_pitems($appointmentid);
        // исключим их из общего массива
        $progpitems = array_diff_key($progpitems, $availablepitems);
        if ( empty($progpitems) )
        {// предметов не осталось - возвращаем пустой массив
            return array();
        }
        $pitems = array();
        foreach ( $progpitems as $progpitem )
        {// перебираем все предметы программы
            if ( empty($pitems[$progpitem->programmid]) )
            {
                $pitems[$progpitem->programmid] = new stdClass;
            }
            if ( empty($pitems[$progpitem->programmid]->name) )
            {// если имени программы еще нет - добавим в массив
                $pitems[$progpitem->programmid]->name = 
                        $this->dof->storage('programms')->get_field($progpitem->programmid, 'name').'['.
                        $this->dof->storage('programms')->get_field($progpitem->programmid, 'code').']'; 
            }
            // добавляем сам предмет
            $pitems[$progpitem->programmid]->options[$progpitem->id] = $progpitem->name.'['.$progpitem->code.']';
        }
        return array_values($pitems);
    }
    
    /** Получить список курсов, которые учитель уже может вести в рамках указанного назначения на должность
     * 
     * @return array - массив курсов из таблицы pitems 
     * @param int $appointmentid - id назначения на должность в таблице в табице appointments
     */
    public function get_appointment_pitems($appointmentid)
    {
        // из таблицы teachers получим те предметы, которые учитель на 
        // указанной должности уже преподает
        if ( ! $teachingitems = $this->dof->storage('teachers')->get_records(array('appointmentid'=>$appointmentid,
                    'status'=>array('active', 'plan'))) )
        {// учитель пока не преподает ни одного предмета
            return array();
        }
        
        $pitems = array();
        foreach ( $teachingitems as $id=>$item )
        {// перебираем все записи о курсах, и оставляем только уникальные
            $pitem = current($this->dof->storage('programmitems')->
                    get_records(array('id'=>$item->programmitemid,
                    'status'=>array('active', 'suspend'))));
            // добавляем к объекту предмета статус назначения учителя, который его ведет
            $pitem->teacherstatus = $item->status;
            // записываем комбинацию в итоговый массив
            $pitems[$item->programmitemid] = $pitem->name.'['.$pitem->code.']';
            // определим статус учителя для этого предмета: если учитель пока не 
            // преподает предмет, а только собирается его преподавать - то выделим предмет серым
            $color = 'color:black';
            if ( $item->status == 'plan' )
            {// учитель только планирует преподавать этот предмет
                $color = 'color:gray';
            }
            $this->extradata->elements[$item->programmitemid] = new stdClass;
            $this->extradata->elements[$item->programmitemid]->style = $color;
        }
        // возвращаем итоговый результат
        return $pitems;
    }
    

    /** Назначает учителю преподаваемые дисциплины
     * @param int $appointment - запись назначения на должность из таблицы appointments
     * @param array $addlist - массив назначаемых дисциплин
     * @param int $worktime - время на преподавание дисциплины
     */
    public function add_programmitem_from_appointment($appointment,$addlist,$worktime=0)
    {
        $result = true;
		foreach ( $addlist as $pitemid )
		{// обработаем добавление каждого предмета
			$teacherdata = new stdClass;
			$teacherdata->appointmentid  = $appointment->id;
			$teacherdata->programmitemid = $pitemid;
			$teacherdata->departmentid   = $appointment->departmentid;
			$teacherdata->worktime 		 = $worktime;
			$worktime = 0;
			// найдем тичеров на это назначение
			if ( $aviteachers = $this->dof->storage('teachers')->
			                          get_records(array('appointmentid'=>$appointment->id,
                                                      'status'=>array('plan', 'active'))) )
			{// тичеры есть
                foreach ( $aviteachers as $aviteacher )
                {// узнаем сколько он уже преподает
                    $worktime += $aviteacher->worktime;
                }
            }
		    // добавим введеное кол-во часов 
		    $freeworktime = $appointment->worktime - $worktime;
            $worktime += $teacherdata->worktime;
            if ( $appointment->worktime < $worktime)
            {// если лимит указанное время превышает ставку
                // выведем
                $eagreement = $this->dof->storage('eagreements')->get($appointment->eagreementid);
                $fullname = $this->dof->storage('persons')->get_fullname($eagreement->personid);
                // ставка переполнена - сообщим об этом
                return 'limit_excess_worktime';
            }
            // @todo - объеденить в один запрос - если это возможно
			if ( ! $this->dof->storage('teachers')->
			       is_exists(array('appointmentid'=>$appointment->id,
			       'programmitemid'=>$pitemid, 'status'=>'active')) AND 
		         ! $this->dof->storage('teachers')->
                   is_exists(array('appointmentid'=>$appointment->id,
                   'programmitemid'=>$pitemid, 'status'=>'plan')) )
			{//такой подписки нет - 
			    // производим добавление
                $newteacherid = $this->dof->storage('teachers')->add_teacher($teacherdata);
                // запоминаем результат добавления в базу
				$result = $result AND $newteacherid;
                if ( $newteacherid AND $appointment->status == 'active' )
                {// запись о преподавании нужно сразу же активировать - и вставка успешно удалась
                    $result = $result AND $this->dof->workflow('teachers')->change($newteacherid, 'active');
                }
			}
		}
		return $result;
    }
    
    /** Отписывает преподаваемые дисциплины от учителя
     * @param int $appointment - запись назначения на должность из таблицы appointments
     * @param array $removelist - массив дисциплин, которые следует отписать
     */
    public function remove_programmitem_from_appointment($appointment,$removelist)
    {
        $result = true;
		foreach ( $removelist as $pitemid )
		{// обработаем удаление каждого предмета
			$result = $result AND $this->dof->storage('teachers')->
				remove_programmitem_from_appointment($appointment->id, 
				$pitemid);
		}
		return $result;
    }
    
    /** Получить сообщение о результате подписки/отписки учеников в группу, размеченное html-тегами
     * 
     * @param string $action - add - ученики были добавлены в группу
     *                         remove ученики быле удалены из группы
     * @param bool $result - результат выполненной операции
     */
    public function get_addremove_result_message($action, $result)
    {
        // определяем, какими цветами будем раскрашивать успешное и неуспешное сообщение
        $successcss = 'color:green;';
        $failurecss = 'color:red;';
        $basecss    = 'text-align:center;font-weight:bold;margin-left:auto;margin-right:auto;';
        if ( is_string($result) )
        {// скорее всего пришло сообщение об ошибке
            $css      = $failurecss;
            $stringid = $result;
        }elseif ( $action == 'add' )
        {// предметы назначались учителю
            if ( $result )
            {// успешно
                $css      = $successcss;
                $stringid = 'add_pitems_to_appointment_success';
            }else
            {// не успешно
                $css      = $failurecss;
                $stringid = 'add_pitems_to_appointment_failure';
            }
        }elseif ( $action == 'remove' )
        {// предметы отписывались от учителя
            if ( $result )
            {// успешно
                $css      = $successcss;
                $stringid = 'remove_pitems_from_appointment_success';
            }else
            {// не успешно
                $css      = $failurecss;
                $stringid = 'remove_pitems_from_appointment_failure';
            }
        }
        // получаем текст сообщения
        $text = $this->dof->get_string($stringid, 'employees');
        // оформляем сообшение css-стилями
        return '<p style="'.$basecss.$css.'">'.$text.'</p>';
    }
    
    public function get_worktime_form()
    {
        $template = new stdClass;
        $template->underaddlist = '<p align="left">
               <label for="worktime">'.$this->dof->get_string('worktimi_for_teaching', 'employees').'</label>
               <br />
               <input type="text" name="worktime" id="worktime" size="5">
               </p>';
        return $template;
    }

}
?>