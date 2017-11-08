<?php
////////////////////////////////////////////////////////////////////////////
//                                                                        //
// NOTICE OF COPYRIGHT                                                    //
//                                                                        //
// Dean`s Office for Moodle                                               //
// Электронный деканат                                                    //
// <http://sourceforge.net/projects/freedeansoffice/>                     //
//                                                                        //
// Copyright (C) 2008-2999                                                //
// Ilia Smirnov (Илья Смирнов)                                            //
// Evgenij Tsygantsov (Евгений Цыганцов)                                  //
// Alex Djachenko (Алексей Дьяченко)  alex-pub@my-site.ru                 //
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
// $DOF->modlib('nvg')->add_level($DOF->get_string('itog_grades', 'journal'), $DOF->url_im('journal','/itog_grades/edit.php?id='.$cstreamid),$addvars);
$depid = optional_param('departmentid', 0, PARAM_INT);
$addvars = array();
$addvars['departmentid'] = $depid;

/** 
 * Класс формирования объекта для вставки в шаблон ведомости оценок
 */
class block_dof_im_journal_templater_itoggrades
{
    /**
     * @var dof_control
     */
    protected $dof;
    
    /**
     * инициализирует экземпляр класса
     * 
     * @param dof_control $dof - методы ядра fdo 
     * @param object $order - объект приказа
     * @return void
     */
    public function __construct(dof_control $dof)
    {
    	$this->dof = $dof;
    }

    /**
     * Возвращает данные для вставки в шаблон
     * 
     * @return object - данные в подготовленные 
     * для вставки в шаблон документа
     */
    public function get_data($orderid)
    {
    	if ( ! $order = $this->get_order($orderid) )
        {// не получили объект приказа
            return false;
        }
        // возвращает объект для вставки в шаблон
        return $this->get_info($order);
    }
    
    /**
     * Формирует данные для вставки в шаблон ведомости
     * 
     * @param int $orderid - id приказа
     * @return mixed object - объект, подготовленный для вставки в шаблон или
     * bool false 
     */
    public function get_info($order)
    {
    	$table = new stdClass();
		// ссылка на содержимое приказа
		if( ! $orderdata = $order->data )
		{// нет содержимого приказа
			return false;
		};
		// получим информацию о потоке
		if ( !empty($orderdata->cstreamdata) )
		{
		    $cstream_info = $orderdata->cstreamdata;
		}elseif( ! $cstream_info = $this->get_cstream_info($orderdata->cstreamid) )
		{
			return false;
		};
		// ФИО того кто подготовил приказ
		if( ! $table->ordercreator = $this->get_fullname($order->ownerid) ) 
		{
			return false;
		};
		// ФИО того кто подписал приказ
		if( ! $table->ordersigner = $this->get_fullname($order->signerid) )
		{
			return false;
		};
		// дата подписания приказа
        $table->date             = $this->get_date($order->signdate);
        // название подразделения
        $table->departmentname   = $cstream_info->departmentname; 
        //название программы
        $table->programmname     = $cstream_info->programmname;
        //название дисциплины
        $table->programmitemname = $cstream_info->programmitemname; 
        // ФИО преподавателя
        $table->teachername      = $cstream_info->teachername;
        //название учебного периода
        $table->agename          = $cstream_info->agename;
        // название группы если есть
        $table->groupname        = $cstream_info->groupname;
        // итоговые оценки студентов
        if( ! $table->table = $this->get_students_grades($order->data->itoggrades) )
		{
			return false;
		}
        return $table;
    }
    
    /**
     * Возвращает информацию о потоке для вставки
     * в шаблон (название дисциплины, ФИО преподавателя, период, и т.п.)
     * 
     * @param int $cstreamid - id потока
     * @return mixed bool false или
     * объект с нужными значениями 
     */
    protected function get_cstream_info($cstreamid)
    {
    	if ( ! $cstream = $this->dof->storage('cstreams')->get($cstreamid) )
    	{
    		return false;
    	}
    	$cstream_info = new stdClass();
    	// id потока
    	$cstream_info->id = $cstreamid;
    	// название подразделения
    	if ( ! $cstream_info->departmentname = $this->get_departmentname($cstream->departmentid) )
    	{
    		$cstream_info->departmentname = '';
    	}
    	// название периода
    	if ( ! $cstream_info->agename = $this->get_agename($cstream->ageid) )
    	{
    		$cstream_info->agename = '';
    	}
    	// название дисциплины
    	if ( ! $cstream_info->programmitemname = $this->get_programmitemname($cstream->programmitemid) )
    	{
    		$cstream_info->programmitemname = '';
    	}
    	// название программы
    	if ( ! $cstream_info->programmname = $this->get_programmname($cstream->programmitemid) )
    	{
    		$cstream_info->programmname = '';
    	}
    	// ФИО преподавателя
    	if ( ! $cstream_info->teachername = $this->get_fullname($cstream->teacherid) )
    	{
    		$cstream_info->teachername = '';
    	}
    	// название группы если есть
    	$cstream_info->groupname = $this->get_groupname($cstream->mdlgroup);
    	return $cstream_info;
    }
    
    /**
     * Возвращает ФИО студентов, 
     * которые получили оценки этим 
     * приказом и сами оценки в виде, 
     * пригодном для вставки в шаблон ведомости
     * 
     * @return array 
     */
    private function get_students_grades($itoggrades)
    {
    	$grades_new = array();//массив итоговых оценок
    	$num = 1;
    	foreach ( $itoggrades as $grades)
        {// Перебираем все строки оценок
            // создаем объект для ряда таблицы
            $row = new stdClass();
            $row->number  = $num;
            $row->student = $grades['fullname'];
            $row->grades  = $grades['grade'];
            // помещаем объект ряда таблицы в массив
			$grades_new[] = $row;
            $num++;
        }
       	return $grades_new;
    }

    /**
     * Возвращает полное имя пользователя из fdo 
     * 
     * @param int $personid - id персоны
     * @return mixed string - имя пользователя или bool false
     */
    private function get_fullname($personid)
    {
		// получаем полное имя участника
		if ( ! $fullname = $this->dof->storage('persons')->get_fullname($personid) )
    	{
    		return false;
    	}
    	return $fullname;
    }
    
    /**
     * Возвращает название учебного периода
     * 
     * @return mixed string - название учебного периода
     * или bool false
     */
    private function get_agename($ageid)
    {
    	if ( ! $age = $this->dof->storage('ages')->get($ageid) )
    	{
    		return false;
    	}
    	return $age->name;
    }
    
    /**
     * Возвращяет название дисциплины
     * 
     * @return mixed string или bool false
     */
    private function get_programmitemname($programmitemid)
    {
		if ( ! $programmitem = $this->dof->storage('programmitems')->get($programmitemid))
		{
			return false;
		}
        return $programmitem->name;
    }
    
    /**
     * Возвращает название программы
     * 
     * @param int programmitemid - id дисциплины,
     *  для которой надо получить имя программы
     * @return mixed string или bool false
     */
    private function get_programmname($programmitemid)
    {
		if ( ! $programmitem=$this->dof->storage('programmitems')->get($programmitemid))
		{
			return false;
		}
		if ( ! $programm = $this->dof->storage('programms')->get($programmitem->programmid))
		{
			return false;
		}
		
		return $programm->name;
    }
    
    /**
     * Возвращает название подразделения
     * 
     * @param int departmentid - id подразделения,
     * @return mixed string или bool false
     */
    private function get_departmentname($departmentid)
    {
		if ( ! $department = $this->dof->storage('departments')->get($departmentid) )
		{
			return false;
		}
		return $department->name;
    }
    
    /**
     * Возвращает название группы если есть
     * 
     * @param int groupid - id группы,
     * @return mixed string или bool false
     */
    private function get_groupname($groupid)
    {
        // группы пока не реализованы
    	return '';
    }

    /**
     * Возвращает данные приказа из таблицы
     * 
     * @param int $orderid - id приказа, данные которого надо получить
     * @return mixed - object - информация приказа или bool false
     */
    private function get_order($orderid)
    {
    	//подключаем методы работы с приказом
        $order = $this->dof->im('journal')->order('set_itog_grade');
        //Загружаем приказ
        if ( ! $orderobj = $order->load($orderid) )
        {
        	return false;        	
        }
        return $orderobj;     	
    }
    
    /**
     * Возвращает дату как строку,
     * получив метку времени 
     * 
     * @param int $date - метка времени
     * @return string
     */
    private function get_date($date)
    {
    	return dof_userdate($date,'%d.%m.%Y');
    }
}


/**
 * Класс для формирования приказов сохранения и удаления итоговых оценок
 */
class dof_im_journal_order_itog_grades extends block_dof_im_journal_templater_itoggrades
{
    /**
     * @var dof_control
     */
    protected $dof;
    protected $gradedata;
    
    function __construct($dof, $gradedata)
    {
    	$this->dof = $dof;
        $this->gradedata = $gradedata;
    }

    /** 
     * Сформировать приказ об изменении состояния учебного потока
     * 
     * @return true or false
     */
    public function generate_order_itog_grades()
    {
        if ( ! $orderobj = $this->order_set_itog_grade() )
        {//ошибка  формировании приказа выставления итоговых оценок
            return false;
        }
        if ( ! $orderid = $this->save_order_itog_grade($orderobj) )
        {//ошибка  при сохранении приказа выставления итоговых оценок
            return false;
        }
        return $this->sign_and_execute_order_itog_grade($orderid);    
    }
    
    /** 
     * Формирует приказ - установить оценку
     * 
     * @return mixed object - данные приказа для сохранения
     * или bool false в случае неудачи
     */
    public function order_set_itog_grade()
    {
        //создаем объект для записи
        $orderobj = new stdClass();
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
		{// если id персоны не найден 
			return false;
		}
        //сохраняем автора приказа
        $orderobj->ownerid = $personid;
        //подразделение, к которому он относится
        if ( ! $teacher = $this->dof->storage('persons')->get($orderobj->ownerid) )
        {// пользователя, выставляющего оценку нет в базе данных
            return false;
        }
        // установим id подразделения из сведений об учителе
        $orderobj->departmentid = $teacher->departmentid;
        //дата создания приказа
        $orderobj->date = time();
        //добавляем данные, о которых приказ
        $orderobj->data = $this->get_itog_grades_fororder();
        
        return $orderobj;
    }
    
    /** 
     * Сохраняет данные приказа
     * 
     * @param object $orderobj - данные приказа для сохранения
     * @return mixed int - id приказа
     * или bool true - если приказ не создавался
     */
    public function save_order_itog_grade($orderobj)
    {
    	//подключаем методы работы с приказом
        $order = $this->dof->im('journal')->order('set_itog_grade');
        if ( empty($orderobj->data->itoggrades) )
        {// если оценки не менялись, создавать приказ ненадо
        	return true;
        }
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // вернем id приказа
        return $order->get_id();
    }
    /** Подписывает и исполняет приказ
     * 
     * @param int $orderid - id приказа
     * @return bool true в случае успеха и false в случае неудачи 
     */
    public function sign_and_execute_order_itog_grade($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('journal')->order('set_itog_grade',$orderid) )
    	{// приказа нет - это ошибка
    		return false;
    	}    	
        // подписываем приказ
        if ( ! $personid = $this->dof->storage('persons')->get_by_moodleid_id() )
		{// если id персоны не найден 
			return false;
		}
        $order->sign($personid );
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {//приказ не подписан
            return false;
        }
        //исполняем приказ';
        if ( ! $order->execute() )
        {//не удалось исполнить приказ
            return false;
        }
        return true;
    }
    /** 
     * Проверяет подписан ли приказ
     * 
     * @param int $orderid - id приказа
     * @return bool true если уже подписан и false если нет
     */
    public function is_signed($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('journal')->order('set_itog_grade',$orderid) )
    	{// приказа уже нет - будем считать что все нормально
    		return true;
    	}
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {//приказ не подписан
            return false;
        }
        return true;
    }

    /** 
     * Формирует массив данных для приказа
     * 
     * @param $type  
     * @return unknown_type
     */
    private function get_itog_grades_fororder()
    {
    	//print_object($this->gradedata);//die;
    	//print_object($this->dof->storage('programmitems')->get($this->gradedata->programmitemid));
    	//Структура приказа:
        $order = new stdClass();
            //поля сохранения итоговых оценок
        $order->cstreamid = $this->gradedata->id;//id предмето-потока
        $order->cstreamdata = $this->get_cstream_info($this->gradedata->id);//данные потока
        $order->gradesdate = time();//дата выставления оценки
        $order->teacherid = $this->gradedata->teacherid;//тот кто поставил оценку
        $order->ageid = $this->gradedata->ageid;//тот кто поставил оценку
        $order->programmitemid = $this->gradedata->programmitemid;//id дисциплины
        $order->scale = $this->gradedata->scale;//шкала оценок
        $order->mingrade = $this->gradedata->mingrade;//минимальная положительная оценка
        //$order->previousorderid = $prevorderid;//id приказа, которым была выставлена предыдущая оценка
        $order->itoggrades = array();//массив итоговых оценок
        $grades = $this->gradedata->grade;
        foreach ( $grades as $cpassid=>$grade)
        {// для каждого студента запишем его оценку
            $cpass = $this->dof->storage('cpassed')->get($cpassid);
            if ( $grade != '')
            {// в приказ поступают только те оценки, которые менялись
                $order->itoggrades[$cpass->programmsbcid] = array('grade'=>$grade, 'fullname'=>$this->dof->storage('persons')->get_fullname($cpass->studentid));
            }
        }
        return $order;	
    }
}

?>