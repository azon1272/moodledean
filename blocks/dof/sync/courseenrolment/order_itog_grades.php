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
// Copyright (C) 2011-2999  Evgeniy Yaroslavtsev (Евгений Ярославцев)     //
// Copyright (C) 2011-2999  Evgeniy Gorelov (Евгений Горелов)             //  
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
 * Класс для формирования, сохранения и исполнения приказов (ведомостей)
 * 
 * @package    block
 * @subpackage dof
 * @copyright  2011 Evgeniy Yaroslavtsev
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Класс для формирования, сохранения и исполнения приказов (ведомостей)
 */
class dof_sync_courseenrolment_order_itog_grades 
{
	/**
     * @var dof_control
     */
    private $dof;
    public $gradedata;
    
    function __construct($dof, $gradedata)
    {
    	$this->dof = $dof;
        $this->gradedata = $gradedata;
    }

    /**
     * Сформировать приказ об изменении состояния учебного потока
     * 
     * @return true or false
     * @author Evgeniy Yaroslavtsev
     */
    public function generate_order_itog_grades()
    {
        if ( ! $orderobj = $this->order_set_itog_grade() )
        {
            //ошибка формирования приказа выставления итоговых оценок
            $this->log_get_str('error_gen_journal', $this->gradedata->id, true);
            return false;
        }
        if ( ! $orderid = $this->save_order_itog_grade($orderobj) )
        {
            //ошибка  при сохранении приказа выставления итоговых оценок
            $this->log_get_str('error_save_journal', $this->gradedata->id, true);
            return false;
        }
        return $this->sign_and_execute_order_itog_grade($orderid);    
    }
    
    /**
     * Формирует приказ - установить оценку
     * 
     * @return mixed object - данные приказа для сохранения
     * или bool false в случае неудачи
     * @author Evgeniy Yaroslavtsev
     */
    public function order_set_itog_grade()
    {
        global $USER;
        
        //создаем объект для записи
        $orderobj = new stdClass();
        
        // Можно так получить personid
        //if ( ! $personid = $this->get_cfg('teacher_personid') )
		//{
		//    // если id персоны не найден
		//    $this->log_get_str('not_found_cfg_param', 'teacher_personid', true); 
		//    return false;
		//}
		
        // Определяем, есть ли в $USER поле id не равное нулю 
        if ( isset($USER) AND isset($USER->id) AND $USER->id )
        {
            $person = $this->dof->storage('persons')->get_bu();
            if (!$person)
            {
                $this->log_get_str('cur_user_havent_person', $USER->id, true);
                return false;
            }
            $personid = $person->id;
        }
        else
        {
            // если текущего пользователя нет (через крон к примеру запущено)
            // то автором становится преподаватель из учебного потока
            $personid = $this->gradedata->teacherid;
            //находим этого пользователя
            if ( ! $person = $this->dof->storage('persons')->get($personid) )
            {
                // пользователя, выставляющего оценку нет в базе данных
                $this->log_get_str('error_get', "person (id = {$personid})", true);
                return false;
            }
        }
		
        //сохраняем автора приказа
        $orderobj->ownerid = $personid;
        
        // установим id подразделения преподавателя
        $orderobj->departmentid = $person->departmentid;
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
     * @author Evgeniy Yaroslavtsev
     */
    public function save_order_itog_grade($orderobj)
    {
    	//подключаем методы работы с приказом
        $order = $this->dof->im('journal')->order('set_itog_grade');
        if ( empty($orderobj->data->itoggrades) )
        {
            // если оценки не менялись, создавать приказ не надо
            $this->log_get_str('grades_not_changed', $this->gradedata->id);
        	return true;
        }
        // сохраняем приказ в БД и привязываем экземпляр приказа к id
        $order->save($orderobj);
        // вернем id приказа
        return $order->get_id();
    }
    
    /**
     * Подписывает и исполняет приказ
     * 
     * @param int $orderid - id приказа
     * @return bool true в случае успеха и false в случае неудачи
     * @author Evgeniy Yaroslavtsev 
     */
    public function sign_and_execute_order_itog_grade($orderid)
    {
        global $USER;
        
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('journal')->order('set_itog_grade',$orderid) )
    	{
    	    // приказа нет - это ошибка
    	    $this->log_get_str('error_get', "order (orderid = {$orderid})", true);
    		return false;
    	}    	
        
        // Определяем, есть ли в $USER поле id не равное нулю 
        if ( isset($USER) AND isset($USER->id) AND $USER->id )
        {
            $person = $this->dof->storage('persons')->get_bu();
            if (!$person)
            {
                $this->log_get_str('cur_user_havent_person', $USER->id, true);
                return false;
            }
            $personid = $person->id;
        }
        else
        {
            // если текущего пользователя нет (через крон к примеру запущено)
            // то автором становится преподаватель из учебного потока
            $personid = $this->gradedata->teacherid;
            //находим этого пользователя
            if ( ! $person = $this->dof->storage('persons')->get($personid) )
            {
                // пользователя, выставляющего оценку нет в базе данных
                $this->log_get_str('error_get', "person (id = {$personid})", true);
                return false;
            }
            
            // Проверяем, что с сотрудником заключен(ы) договор(а) и он(и) в активном статусе
            $eagreements = $this->dof->storage('eagreements')->get_records(array('personid'=>$personid, 'status'=>'active'));
            if (!$eagreements)
            {
                $this->log_get_str('person_havent_eagreement', $personid, true);
                return false;
            }
        }
		
        $order->sign($personid);
        
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {
            //приказ не подписан
            $this->log_get_str('error_sign_order', $orderid, true);
            return false;
        }
        
        //исполняем приказ;
        if ( ! $order->execute() )
        {
            //не удалось исполнить приказ
            $this->log_get_str('error_execute_order', $orderid, true);
            return false;
        }
        
        return true;
    }
    
    /**
     * Проверяет подписан ли приказ
     * 
     * @param int $orderid - id приказа
     * @return bool true если уже подписан и false если нет
     * @author Evgeniy Yaroslavtsev
     */
    protected function is_signed($orderid)
    {
    	//подключаем методы работы с приказом
    	if ( ! $order = $this->dof->im('journal')->order('set_itog_grade',$orderid) )
    	{
    	    // приказа уже нет - будем считать что все нормально
    		return true;
    	}
        //проверяем подписан ли приказ
        if ( ! $order->is_signed() )
        {
            //приказ не подписан
            return false;
        }
        return true;
    }

    /** 
     * Формирует массив данных для приказа
     * 
     * @param $type  
     * @return unknown_type
     * @author Evgeniy Yaroslavtsev
     */
    private function get_itog_grades_fororder()
    {
    	//Структура приказа:
        $order = new stdClass();
		//поля сохранения итоговых оценок
		$order->cstreamid = $this->gradedata->id;//id предмето-потока
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
        {
            // для каждого студента запишем его оценку
        	$cpass = $this->dof->storage('cpassed')->get($cpassid);
        	// в приказ поступает все, что передали
        	// TODO Тут получать studentid через подписку, а не напрямую
        	$order->itoggrades[$cpass->programmsbcid] = array('grade'=>$grade, 'fullname'=>$this->dof->storage('persons')->get_fullname($cpass->studentid));
        }
        //print_object($order);//die;
        return $order;
		
    }
    
    /**
     * Вернуть массив с настройками или одну переменную
     * 
     * получает параметры из sync/courseenrolment
     * 
     * @param string $key - название искомого параметра
     * @return mixed
     * @author Evgeniy Yaroslavtsev
     */
    protected function get_cfg($key=null)
    {
        return $this->dof->sync('courseenrolment')->get_cfg($key);
    }
    
    /**
     * Метод log, только вместо сообщения подается строка как в get_string,
     * т.е. ищет сообщение в файлах локализации
     *
     * пользуется методом из sync/courseenrolment
     *
     * @param string $message Сообщение об ошибке
     * @param mixed $a Параметры для строки из файла локализации
     * @param bool[optional] $error Если это сообщение об ошибке
     * @author Evgeniy Yaroslavtsev
     */
    protected function log_get_str($messagekey, $a = null, $error = false)
    {
        $this->dof->sync('courseenrolment')->log_get_str($messagekey, $a, $error);
    }
}
    
?>